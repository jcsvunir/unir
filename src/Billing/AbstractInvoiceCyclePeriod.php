<?php


namespace Billing;



use Core\ConfigurationManager;
use Core\Timer;
use Exceptions\InvoiceBillingProcessException;
use Exceptions\ObjectAlreadyExistsException;
use Exceptions\ObjectNotExistsException;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Traversable;


abstract class AbstractInvoiceCyclePeriod implements IBillable
{

    protected $idCustomer;
    protected $accountId;
    protected $year;
    protected $month;
    protected $period;
    private $noSqlClient;
    private $callsCollection;
    private $invoiceArray = array();

    protected $issuesArray = null;
    private $aggregation;

    private $aggregateMatchArray = array();

    private $aggregateGroupArray = array();

    private $aggregateProjectArray = array();
    private $aggregateSortArray = array();

    private $fileSystemAdapter;

    private string $mvnoName;

    private string $csvPath;

    public function __construct(array $options)
    {
        $required_options = ['idCustomer', 'accountId', 'year', 'month', 'mvnoName'];
        foreach ($required_options as $option) {
            if (!array_key_exists($option, $options)) {
                throw new \InvalidArgumentException("Missing required option: $option");
            }
        }
        // Configurar parámetros básicos de factura
        $this->idCustomer = $options['idCustomer'];
        $this->accountId = $options['accountId'];
        $this->year = $options['year'];
        $this->month = $options['month'];
        $this->mvnoName = $options['mvnoName'];
        $this->includeChildCustomers = $options['includeChildCustomers'] ?? false;
        $this->period = $this->year . "-" . $this->month;

        // Configurar cliente NoSQL
        ConfigurationManager::init($this->mvnoName);
        $uri = ConfigurationManager::getParameter('nosql_storage', 'uri');
        $database = ConfigurationManager::getParameter('nosql_storage', 'database');
        $cdrCollection = ConfigurationManager::getParameter('nosql_storage', 'cdr_collection');

        $this->noSqlClient = new \MongoDB\Client($uri, ['readPreference' => \MongoDB\Driver\ReadPreference::SECONDARY_PREFERRED]);

        $this->callsCollection = $this->noSqlClient->selectCollection($database, $cdrCollection);

        // Configurar parámetros de agregación
        $this->aggregateMatchArray = $this->mapValues(ConfigurationManager::getParameter('billing', 'aggregate_match'), $options);
        $this->aggregateGroupArray = ConfigurationManager::getParameter('billing', 'aggregate_group');
        $this->aggregateProjectArray = ConfigurationManager::getParameter('billing', 'aggregate_project');
        $this->aggregateSortArray = ConfigurationManager::getParameter('billing', 'aggregate_sort');

        // Obtener configuración de almacenamiento.
        $fileSystemConfig = ConfigurationManager::getConfig('filesystem');

        switch ($fileSystemConfig['type']) {
            case 'google-cloud-storage':
                $storageClient = new StorageClient(['projectId' => $fileSystemConfig['project_id']]);
                $bucket = $storageClient->bucket($fileSystemConfig['bucket_name']);
                $adapter = new GoogleCloudStorageAdapter($bucket, $fileSystemConfig['path_prefix']);
                break;
            case 'aws-s3':
                $adapter = new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
                    new \Aws\S3\S3Client([
                        'version' => 'latest',
                        'region' => $fileSystemConfig['region'],
                        'credentials' => [
                            'key' => $fileSystemConfig['key'],
                            'secret' => $fileSystemConfig['secret'],
                        ],
                    ]),
                    $fileSystemConfig['bucket_name'], $fileSystemConfig['path_prefix']
                );
                break;
            default:
                $adapter = new LocalFilesystemAdapter($fileSystemConfig['path-prefix']);

        }
        $this->fileSystemAdapter = new Filesystem($adapter);


        $this->issuesArray = new InvoiceIssueList();

        $this->csvPath = ConfigurationManager::getParameter('filesystem', 'file_basename_template');
        $this->csvPath = str_replace('{month}', $this->month, $this->csvPath);
        $this->csvPath = str_replace('{year}', $this->year, $this->csvPath);
        $this->csvPath = str_replace('{account_id}', $this->accountId, $this->csvPath);

    }

    /**
     * @param $performAggregation
     * @return void
     * @throws InvoiceBillingProcessException
     */
    private function mapValues(array $yamlStructure, array $arrayMap): array {
        $result= [];

        foreach ($yamlStructure as $clave => $valor) {
            if (is_array($valor)) {
                // Si es un array, lo procesamos de forma recursiva
                $result[$clave] = $this->mapValues($valor, $arrayMap);
            } elseif (is_string($valor)) {
                // Si es un string, buscamos si contiene un patrón {valor}
                $result[$clave] = preg_replace_callback('/\{([^\}]+)\}/', function ($coincidencia) use ($arrayMap) {
                    $claveMapeo = $coincidencia[1];
                    return $arrayMap[$claveMapeo] ?? $coincidencia[0]; // Si no lo encuentra, lo deja igual
                }, $valor);
            } else {
                // Si no es ni array ni string, lo dejamos igual
                $result[$clave] = $valor;
            }
        }

        return $result;
    }


    public function hasIssues(): bool
    {
        return $this->issuesArray->hasIssues();
    }

    public function getEndBillPeriod(): string
    {
        $timer = new Timer();
        return $timer->getLastDayOfDate($this->getBeginBillPeriod());
    }

    public function getBeginBillPeriod(): string
    {
        return $this->year . '-' . $this->month . '-01';
    }

    /**
     * Calcula una factura a partir de los parámetros de entrada.
     * @return void
     * @throws InvoiceBillingProcessException
     */
    public final function doInvoice(): void
    {

        $result = array();

        try {

            $cursor = $this->getAggregateConsumptionsCursor();

            foreach ($cursor as $document) {

                $result[] = $this->computeConsumption($document);
            }


        } catch (\Exception $exception) {
            throw new InvoiceBillingProcessException($exception->getMessage());
        }

        // Si el resultado está vacío, se añade una incidencia
        if (empty($result)){
            $this->issuesArray->add(new InvoiceIssue(IssueEnumerator::NO_CONSUMPTIONS_ON_PERIOD, SeverityEnumerator::INFO, "No consumptions found on period: " . $this->period, $this->idCustomer, $this->period));
        }

        // Almacena la factura en formato array
        $this->invoiceArray = $result;

    }

    /**
     * @return void
     * @throws FilesystemException
     * @throws ObjectNotExistsException
     */
    public final function downloadCSVFile(): void
    {

        if ($this->fileSystemAdapter->fileExists($this->$this->csvPath)) {

            // Output CSV-specific headers
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($this->csvPath));
            header('Content-Transfer-Encoding: binary');

            // Stream the CSV data
            $this->fileSystemAdapter->readStream($this->csvPath);
            //exit($bucket->object($this->getBillOutputPath())->downloadAsStream());

        } else {
            throw new ObjectNotExistsException($this->csvPath);
        }
    }

    public final function getInvoiceIssues(): InvoiceIssueList
    {
        return $this->issuesArray;
    }



    /**
     * Convierte la factura a formato CSV
     * @return string
     */
    public final function __toCSV(): string
    {

        $data = $this->getInvoiceArray();
        $csvConfig = ConfigurationManager::getConfig('billing');
        $csvDelimiterChar = $csvConfig['csv']['delimiter'];
        $csvEnclosureChar = $csvConfig['csv']['enclosure'];

        # Generate CSV data from array
        $fh = fopen('php://temp', 'rw'); # don't create a file, attempt

        # write out the headers
        fputcsv($fh, array_keys(current($data)), $csvDelimiterChar, $csvEnclosureChar);

        # write out the data
        foreach ($data as $row) {
            //print_r($row);
            fputcsv($fh, $row, $csvDelimiterChar, $csvEnclosureChar);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    /**
     * @return array
     */
    public final function getInvoiceArray(): array
    {
        return $this->invoiceArray;
    }

    /**
     * @param string $csvString
     * @param $overWrite
     * @return void
     * @throws FilesystemException
     * @throws ObjectAlreadyExistsException
     */
    public final function saveCSV(string $csvString, $overWrite = false): void
    {
        if ($this->existsCSV() && !$overWrite) {
            throw new ObjectAlreadyExistsException($this->csvPath);
        }
        $this->fileSystemAdapter->write($this->csvPath, $csvString);
    }

    /**
     * @return bool
     * @throws FilesystemException
     */
    public final function existsCSV(): bool
    {
        return $this->fileSystemAdapter->fileExists($this->csvPath);
    }

    /**
     * @return string
     * @throws FilesystemException
     * @throws ObjectNotExistsException
     */
    public final function getCSVString(): string
    {

        if ($this->existsCSV()) {

            return $this->fileSystemAdapter->read($this->csvPath);

        } else {
            throw new ObjectNotExistsException($this->csvPath);
        }
    }


    /**
     * @return \Traversable
     */
    private function getAggregateConsumptionsCursor(): Traversable
    {
        $match = array('$match' => $this->aggregateMatchArray);
        $group = array('$group' => $this->aggregateGroupArray);

        $aggregateArray = array($match, $group);
        if (!empty($this->aggregateProjectArray)) {
            $aggregateArray[] = array('$project' => $this->aggregateProjectArray);
        }

        if (!empty($this->aggregateSortArray)) {
            $aggregateArray[] = array('$sort' => $this->aggregateSortArray);
        }
        $result = $this->callsCollection->aggregate($aggregateArray);

        $this->storeAggregateConsumptions($result);
        return $result;
    }

    /**
     * Stores the aggregate consumptions.
     *
     * @param \Traversable $aggregation The aggregation data to be stored.
     * @return void
     */
    private function storeAggregateConsumptions(\Traversable $aggregation): void
    {
        $this->aggregation = $aggregation;
    }

}