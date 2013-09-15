<?php

require_once 'config/config.inc.php';
if (!$GLOBALS['config']['documentRoot']) die('Not configured!');
define('APP_ROOT', $GLOBALS['config']['documentRoot']);
foreach (glob(APP_ROOT . "helpers/*.php") as $filename) require_once($filename);
ini_set('include_path', '.:' . APP_ROOT . 'src/3rdParty/pear/:' . APP_ROOT . 'src/3rdParty/');
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

// init logger
if ($GLOBALS['config']['debug']) $GLOBALS['logger'] = new Logger(LOGGER_MODE_ENABLED);
else $GLOBALS['logger'] = new Logger(LOGGER_MODE_DISABLED);

// botstrap doctrine
require_once(APP_ROOT . '/3rdParty/Doctrine/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));
$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);
$manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);

$GLOBALS['db'] = Doctrine_Manager::connection($GLOBALS['config']['dsn'], $GLOBALS['config']['appName']);
Doctrine::loadModels(APP_ROOT . 'models/');

$GLOBALS['db']->setCharset('utf8');
session_start();
