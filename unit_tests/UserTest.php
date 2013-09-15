<?php
    $base = dirname(__FILE__);
    require_once($base."/../controllers/User.class.php");

    class UserTest extends PHPUnit_Framework_TestCase{
	public $user;
	
	public function setUp(){
	    $this->user = new User();
	} 
	public function testAuthenticateUser(){
	    $result = $this->user->authenticateUser(array('username'=>'fahri','password'=>'fah911'));
	    $this->AssertTrue($result);
	}
    }
?>