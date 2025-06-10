<?php


namespace Core;


use InvalidArgumentException;

class ConfigurationManager
{
    public static $conf = NULL;

    private static $filePath = null;

    public static function init($mvnoName = null)
    {
        if (!isset(self::$conf)){

            $config = is_null($mvnoName) ? 'config.yml' :  $mvnoName . ".yml";
            self::$filePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $config;
            //echo "\n config file: " . self::$filePath . "\n";
            self::$conf = \Noodlehaus\Config::load(self::$filePath);
        }
    }

    /**
     * Devuelve un tipo de configuracion
     * @param $configuration
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function getConfig($configuration){
        self::init();

        return self::$conf[$configuration];
    }

    public static function getFilePath(){
        return self::$filePath;
    }
    public static function getParameter($configuration, $parameter){
        self::init();

        return self::getConfig($configuration)[$parameter];
    }
}