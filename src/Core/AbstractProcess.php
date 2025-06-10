<?php

namespace Core;

use Exceptions\ObjectNotExistsException;
use Monolog\Logger;

abstract class AbstractProcess
{

    private $options;
    private $configFilePath = null;
    private $processConfiguration = null;
    private $configuration = null;
    private $logger = null;
    private $logEnabled = true;



    abstract protected function initialize():bool;
    abstract protected function cleanup():bool;
    abstract protected function processItem(&$item):bool;
    abstract protected function getItemId(&$item):string;
    abstract protected function getProcessCursor();

    /**
     * @param string $configPath
     * * Supported options:
     *  process_name: (required) process name to locate process configuration on config file.
     *  log_enabled: (optional) default true. If you want to disable logging log_enabled must be false.
     *  log_file: (optional) file name for log process file. If this option has been set, then override the log_name set on config file.
     *
     * @param array $options
     * @throws ObjectNotExistsException
     */
    public function __construct(string $configPath = "", $options = []){


        if (Tools::notNullNotEmpty($configPath)){
            // Load configuration File
            ConfigurationManager::init($configPath);

        }
        if (!is_array($options)){
            throw new \InvalidArgumentException("options argument must be an array.");
        }

        $this->options = $options;


        if (isset($this->options['process_name']) && Tools::notNullNotEmpty($this->options['process_name'])){
            $this->processConfiguration = ConfigurationManager::getParameter('processes', $this->options['process_name']);
        }else{
            throw new \InvalidArgumentException("process_name parameter value not set.");
        }
        $this->configFilePath = ConfigurationManager::getFilePath();
        $fileName = null;

        if (isset($options['log_enabled'])){
            if (is_bool($options['log_enabled'])) {
                if ($options['log_enabled'] == false) {
                    $this->logEnabled = false;
                } else {
                    // log_enabled -> true
                    if (isset($options['log_file'])){
                        // then configure log path with argument used in log_path
                        $fileName = $this->renderTemplate($options['log_file']);

                    }else{
                        // then configure log path using config file
                        $fileName = $this->renderTemplate($this->processConfiguration['log_name']);
                    }
                }
            }else{
                throw new \InvalidArgumentException("log_enabled must be true or false.");
            }
        }else{
                // then configure log path using config file
            $fileName = $this->renderTemplate($this->processConfiguration['log_name']);
        }


        if (isset($fileName)){
            $logFilePath = AbstractProcess . phpConfigurationManager::getParameter('processes', 'global_log_path') . $fileName;
            $this->logger = new Logger($this->options['process_name']);
            $this->logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $formater = new \Monolog\Formatter\LineFormatter(null, null, true, true);
            $handler = new \Monolog\Handler\StreamHandler($logFilePath, Logger::DEBUG);
            $handler->setFormatter($formater);
            $this->logger->pushHandler($handler);

        }
    }


    protected function checkOption($option, $optionArray, $typeOption):bool{

            $function = 'is_' . ${$typeOption} . '($optionArray[$option])';
            return array_key_exists($option, $optionArray) && $function;
    }

    public function run():void
    {
        $this->logInfo("Starting process" . $this->options['process_name']);
        $this->logInfo("Starting process initialization...");
        $this->initialize();
        $this->logInfo("Process initialization finished");
        $this->logInfo("Getting process iterator cursor...");
        $cursor = $this->getProcessCursor();
        $this->logInfo("Process cursor iterator stored successfully.");
        $this->logInfo("Starting process iteration...");
        foreach($cursor as $item){
            $this->logInfo("Starting item processing. ItemID#".$this->getItemId($item));
            if ($this->processItem($item) === false){
                $this->logError("Error found processing ItemID#".$this->getItemId($item));
            }else{
                $this->logInfo("ItemID#".$this->getItemId($item) . " processed successfully.");
            }
        }
        $this->logInfo("Finished process iteration.");
        $this->logInfo("Starting process cleanup....");
        $this->cleanup();
        $this->logInfo("Process cleanup finished.");
        $this->logInfo("Process " . $this->options['process_name'] . " finished.");

    }

    public function getConfigFilePath():string{
        return $this->configFilePath;
    }
    public function getProcessConfiguration():array
    {
        return $this->processConfiguration;
    }

    private function renderTemplate($template):string
    {

        $template = str_replace('{year}', date('Y'), $template);


        $template = str_replace('{month}', date('m'), $template);

        $template = str_replace('{day}', date('d'), $template);

        $template = str_replace('{time}', date('His'), $template);
        $template = str_replace('{date}', date('Ymd'), $template);
        $template = str_replace('{datetime}', date('YmdHis'), $template);

        return $template;

    }


    private function logEntry($message, $level){
        if (isset($this->logger)){
            $this->logger->{$level}($message);
        }
    }

    protected function logDebug($message){
        $this->logEntry($message, "debug");
    }

    protected function logWarning($message){
        $this->logEntry($message, "warning");
    }

    protected function logError($message){
        $this->logEntry($message, "error");
    }

    protected function logCritical($message){
        $this->logEntry($message, "critical");
    }

    protected function logInfo($message){
        $this->logEntry($message, "info");
    }


    public function getConfiguration($configName)
    {
        $this->configuration = ConfigurationManager::getConfig($configName);
        return $this->configuration;
    }


}