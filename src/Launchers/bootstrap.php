<?php
$baseName = basename($_SERVER['SCRIPT_FILENAME']);
$mvno = substr($baseName, 0, strpos($baseName, '_') ?: strlen($baseName));

require_once '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Core\ConfigurationManager;
use Illuminate\Database\Capsule\Manager;

ConfigurationManager::init($mvno);

$config = ConfigurationManager::getConfig('sql_storage');
$manager = new Manager();
$manager->addConnection([
    'driver' => $config['driver'],
    'host' => $config['host'],
    'database' => $config['database'],
    'username' => $config['username'],
    'password' => $config['password'],
    'charset' => $config['charset'] ?? 'utf8',
    'collation' => $config['collation'] ?? 'utf8_unicode_ci',
    'prefix' => $config['prefix'] ?? '',
    'options' => $config['options'] ?? null
]);
$manager->setAsGlobal();
$manager->bootEloquent();
