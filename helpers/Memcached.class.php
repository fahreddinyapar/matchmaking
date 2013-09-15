<?php
/**
 * Memcache Manager class
 * All memcache operation must use this class
 * 
 * @todo implement for non persistent connections.This always use pconnect
 * @author Ozan Turksever <ozant@sahibinden.com>
 */

define('MEMCACHED_PROTOCOL_TCP',	1);
define('MEMCACHED_PROTOCOL_UDP',	2);

define('MEMCACHED_DEFAULT_FLAG',	0);
define('MEMCACHED_DEFAULT_EXPIRE',	900);
define('MEMCACHED_DEFAULT_PORTTCP',	11211);
define('MEMCACHED_DEFAULT_PORTUDP',	11212);
define('MEMCACHED_DEFAULT_TIMEOUT',	2);

define('MEMCACHED_MODE_READWRITE',	0);
define('MEMCACHED_MODE_READONLY',	1);
define('MEMCACHED_MODE_DUMMY',		2);

require_once 'MemcachedDriver.class.php';

class Memcached {

	private $_servers;
	private $_defaultProtocol = MEMCACHED_PROTOCOL_TCP;
	private $_mode;

	/**
	 * This array has connected memcache objects
	 */
	private $_clients;

	/**
	 * Points wrapper class for memcache functions.
	 * Usefull for unit tests
	 */
	private $_driver;
	
	/**
	 * Cache hash
	 */
	private $_cache;
	
    function Memcached($pServers, $pMode = MEMCACHED_MODE_READWRITE ) {
    	$this->_servers = $pServers;
    	$this->_mode = $pMode;
    	$this->_driver = new MemcachedDriver();
    }
    
    public function setDriver($pDriver) {
    	$this->_driver = & $pDriver;
    }
    
    public function setMode($pMode) {
    	$this->_mode = $pMode;
    }
    
    /**
     * Connects to spesific server
     * 
     * @param int $pServerId
     * @return bool
     */
    public function connect($pServerId) {
		$this->_clients[$pServerId] = & $this->_driver->connect($this->_servers[$pServerId], MEMCACHED_DEFAULT_PORTTCP, MEMCACHED_DEFAULT_TIMEOUT);
		if($this->_clients[$pServerId]) {
			return true;
		} else {
			return false;
		}
    }

    /**
     * Returns serverId for the spesific key.
     * ServerId is persistent for the key.If server list changes this persistens may change
     * 
     * @param String $pKey
     * @return int serverId
     */
    public function getServerId($pKey) {
		$hash = hash('md5', $pKey);
		for ($i=0; $i < strlen($hash); $i++) {
			$hash_total += ord($hash[$i]);
		}
		$serverId = $hash_total % count($this->_servers);    	
		return $serverId;
    }
    
    /**
     * Returns $pKey's value
     * 
     * @param String $pKey
     * @param bool $pUseCache enable/disable get cache
     * @return mixed Value
     */
    public function get($pKey, $pUseCache=true) {
    	if ($this->_mode == MEMCACHED_MODE_DUMMY ) {
    		return false;
    	}
    	
    	if($pUseCache) { 
	    	if($this->_cache[$pKey]) {
	    		return $this->_cache[$pKey];
	    	}
    	}
    	
    	$serverId = $this->getServerId($pKey);
    	if( ! $this->_refreshConnection($serverId) ) {
    		return false;
    	}
    	$value = $this->_driver->get($this->_clients[$serverId], $pKey);
    	if($pUseCache) {
    		// TODO: pointing may give some memory (&$value?)
    		$this->_cache[$pKey] = $value;
    	}
    	return $value;
    }

    /**
     * Returns memcache object for that server
     * 
     * @param int serverId
     * @return Memcache Memcache object for that server
     */
    public function getServerClient($pServerId) {
    	return $this->_clients[$pServerId];
    }

    /**
     * Saves $pValue to memcache
     * 
     * @param string $pKey 
     * @param mixed $pValue
     * @param int $pFlag Compression flag defaults to MEMCACHED_DEFAULT_FLAG
     * @param int $pExpire Expire time(sec) defaults to MEMCACHED_DEFAULT_EXPIRE
     * @return bool true if success
     */
    public function set($pKey, $pValue, $pFlag = MEMCACHED_DEFAULT_FLAG, $pExpire = MEMCACHED_DEFAULT_EXPIRE) {
    	if ($this->_mode == MEMCACHED_MODE_READONLY ) {
    		return false;
    	} else if ($this->_mode == MEMCACHED_MODE_DUMMY ) {
    		return true;
    	}
    	
    	$serverId = $this->getServerId($pKey);
    	if( ! $this->_refreshConnection($serverId) ) {
    		return false;
    	}
    	return $this->_driver->set($this->getServerClient($serverId), $pKey, $pValue, $pFlag, $pExpire);    	
    }
    
    /**
     * Checks if server is connected
     * 
     * @param int serverId
     * @return bool
     */
    private function _isConnected($pServerId) {
    	return $this->getServerClient($pServerId) ? true : false ;
    }

    /**
     * Refresh the server connection.Connect if not connected
     * 
     * @param int serverId
     * @return bool
     */
	private function _refreshConnection($pServerId) {
		if(!$this->_isConnected($pServerId)) {
			return $this->connect($pServerId);
		}
		return true;
	}    
}
