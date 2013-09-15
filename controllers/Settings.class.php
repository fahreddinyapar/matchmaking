<?php
$base = dirname(__FILE__);
require_once($base . "/../bootstrap.php");

class Settings
{
    public function getSettings()
    {
        $setts = Doctrine::getTable('Settings')->findAll();
        return $setts->toArray();
    }
}
