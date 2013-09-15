<?php

require_once 'BaseComponent.class.php';
abstract class PageController extends BaseComponent{
	public $request;
	
	function PageController(& $pRequest) {
		parent::__construct();
		$this->request = & $pRequest;
	}
	
	public function &run() {
		header("HTTP/1.0 404 Not Found");
		return 'Action Not Found<br><br>' . get_class($this);
	}
	
}

