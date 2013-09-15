<?php
// Bootstrab the framework, This will give you lite start
$base = dirname(__FILE__);
require_once 'bootstrap.php';
$GLOBALS['routes']['Authenticate']      = 'AuthenticateController';
$GLOBALS['routes']['Main']           = 'MainController';
$GLOBALS['routes']['Settings']           = 'SettingsController';
$GLOBALS['routes']['admin']           = 'SettingsController';
$frontController = new FrontController(& $GLOBALS['routes'], $GLOBALS['config']['controllersPath']);
$request = new Request($_REQUEST);
$frontController->runPageController($request);
