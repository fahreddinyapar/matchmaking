<?php
/**
 * Memcache wrapper class.
 * This class is needed for unit tests
 * 
 * @author Ozan Turksever <ozant@sahibinden.com>
 */
class MemcachedDriver
{
	public function connect($pHost, $pPort, $pTimeout) {
		$m = new Memcache();
		if(  $m->pconnect($pHost, $pPort, $pTimeout) ) {
			return $m;
		}
		return false;
	}
	
	public function get($pMemcacheObj, $pKey) {
		return $pMemcacheObj->get($pKey);
	}
	
	public function set($pMemcacheObj, $pKey, $pValue, $pFlag, $pExprire) {
		return $pMemcacheObj->set($pKey, $pValue, $pFlag, $pExprire);
	}
}