<?php
$base = dirname(__FILE__);
require_once($base . "/../bootstrap.php");

class Main
{
    public function getCategories()
    {
        $cats = Doctrine::getTable('Category')->findAll();
        return $cats->toArray();
    }
}
