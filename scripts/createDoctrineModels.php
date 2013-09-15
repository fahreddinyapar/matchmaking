#!/opt/bin/php5 -q
<?php

define('OTHER_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
define('nol', true);
require_once OTHER_PATH.'/../bootstrap.php';
Doctrine::generateModelsFromDb(APP_ROOT.'models', array($GLOBALS['config']['appName']), array('generateTableClasses' => true));