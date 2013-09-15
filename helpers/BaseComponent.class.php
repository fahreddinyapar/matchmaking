<?php
/**
 * Base component for all business lojic classes
 * 
 * @package sahibinden
 */
abstract class BaseComponent {

	public $db;
	public $memcached;
	public $mySession;
	public $config;
	public $languages;
	public $currentLanguage;
	public $misc;
	public $logger;
	
	/*
	 * Gets pointers to common objects
	 * It is important to do that, because we need these for unit tests 
	 * 
	 */
	// TODO: check language realy works
	public function BaseComponent()
	{
		$this->db        = & $GLOBALS['db'];
		$this->memcached = & $GLOBALS['memcached'];
		$this->mySession = & $GLOBALS['my_session'];
		$this->languages = & $GLOBALS['languages'];
		$this->config    = & $GLOBALS['config'];
		$this->misc      = & $GLOBALS['misc'];
		$this->logger    = & $GLOBALS['logger'];

		if (!$this->mySession->language) {
			$this->currentLanguage = $this->config["default_language"];
		} else {
			$this->currentLanguage = $this->mySession->language;
		}
		
	}
	
	/*
	 * Simply changes the db object
	 */
	public function setDb($pDb) {
		$this->db = & $pDb;
	}
	/*
	 * Simply changes the memcached object
	 */
	public function setMemcached($pMemcached) {
		$this->memcached = & $pMemcached;
	}
	/*
	 * Simply changes the mySession object
	 */
	public function setMySession($pMySession) {
		$this->mySession = & $pMySession;
	}
	/*
	 * Simply changes the misc object
	 */
	public function setMisc($pMisc) {
		$this->misc = & $pMisc;
	}

}
?>