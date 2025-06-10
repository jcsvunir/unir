<?php

namespace Billing;

use Core\ConfigurationManager;
use Exceptions\ObjectAlreadyExistsException;
use Exceptions\ObjectNotExistsException;
use Fpdf\Fpdf;
use Google\Cloud\Storage\StorageClient;

abstract class AbstractPdfInvoice extends Fpdf
{
    protected $invoiceTemplateConfig = null;
    protected $storage = null;
    protected $bucket = null;
    protected $cloudCustomerNamespace = null;

    abstract protected function getIdCustomer():string;
    abstract protected function getSanitizedInvoiceNumber():string;
    abstract protected function printOthers():void;
    abstract protected function printInvoiceData():void;
    abstract protected function printDetails():void;


    public function __construct($idInvoice, $configPath)
    {

        if (!file_exists($configPath)){
            throw new ObjectNotExistsException($configPath);
        }

        ConfigurationManager::init($configPath);
        $this->invoiceTemplateConfig = ConfigurationManager::getConfig('invoice_template_pdf');

        parent::__construct($this->invoiceTemplateConfig['global_orientation'], $this->invoiceTemplateConfig['global_unit'], $this->invoiceTemplateConfig['global_size']);

        if (!isset($idInvoice)) {
            throw new \InvalidArgumentException("id_invoice can not be null");
        }


        $googleCloudStorageConfig = ConfigurationManager::getConfig('google_cloud');
        $this->storage = new StorageClient([ConfigurationManager::getParameter('google_cloud', 'project_id')]);
        $this->bucket = $this->storage->bucket($googleCloudStorageConfig['bucket_name']);
        $this->cloudCustomerNamespace = $googleCloudStorageConfig['bill_path_template'] . '/' .  $this->getIdCustomer();
    }


    public function getInvoiceFileName()
    {

        return $this->cloudCustomerNamespace . DIRECTORY_SEPARATOR . $this->getSanitizedInvoiceNumber() . '.pdf';
    }

    public function existsPdfOnCloud()
    {

        return $this->bucket->object($this->getInvoiceFileName())->exists();
    }

    public function savePdfOnCloud($force = false)
    {

        if ($this->existsPdfOnCloud() && $force === false) {
            throw new ObjectAlreadyExistsException($this->getInvoiceFileName());
        }
        $tmpFile = tempnam('/tmp', 'lantia_iot_tmp_bill_');
        $this->exportPdf2Disk($tmpFile);
        $storageObject = $this->bucket->upload(fopen($tmpFile, 'r'), ['name' => $this->getInvoiceFileName()]);
        return $storageObject->exists();
    }

    public function render():void{
        //imprimir los campos de la factura
        $this->printInvoiceData();
        $this->printDetails();

        $this->printOthers();

    }
    protected function isEndOfPage()
    {
        if ($this->GetY() >= $this->invoiceTemplateConfig['global_end_of_page']) {

            return true;

        }
        return false;
    }

    public function exportPdf2Browser($fileName):string
    {
        return $this->Output($fileName, 'D');
    }

    /**
     * @param $fileName
     * @return string
     */
    public function exportPdf2BrowserPlugin($fileName):string
    {
        return $this->Output($fileName, 'I');
    }

    /**
     * @param $fileName
     * @return string
     */
    public function exportPdf2Disk($fileName):string
    {
        return $this->Output($fileName, 'F');
    }

    /**
     * @param $destination
     * @return string
     * @throws ObjectNotExistsException
     */
    public function downloadPdfFromCloud($destination = "browser"):string
    {
        if (!in_array($destination,['browser','disk','browser_plugin'])){
            throw new \InvalidArgumentException("Unknown destination.");
        }
        if (!$this->existsPdfOnCloud() ) {
            throw new ObjectNotExistsException($this->getInvoiceFileName());
        }
        $downloadedFile = tempnam('/tmp', 'invoice_') . '.pdf';

        $storageObject = $this->bucket->object($this->getInvoiceFileName())->downloadToFile($downloadedFile);


        $method = "exportPdf2". lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $destination))));
        if ($destination === "disk"){
            return $downloadedFile;
        }else{
            return $this->{$method}($downloadedFile);
        }

    }

}