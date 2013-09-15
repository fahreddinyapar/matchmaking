<?php
define('CURL_MODE_GET',		0);
define('CURL_MODE_POST',	1);

class Curl {
	private $_url;
	private $_ch;
	private $_params;
	private $_info;
	private $_method;
	//private $_logger;
		
    function Curl($pUrl, $pParams = array(), $pMethod = CURL_MODE_GET) {
		$this->_ch = curl_init();
		$this->_url = $pUrl;
		$this->_params = $pParams;
		$this->_method = $pMethod;
		$this->_setOps();

    	//$this->_logger = & $GLOBALS['logger'];
    }
    
	public function &run() {
		if($this->_method == CURL_MODE_POST) {
			curl_setopt($this->_ch, CURLOPT_POST, true);
			curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->_convertParams());
		}
		$out = curl_exec($this->_ch);
		$this->_error = curl_error($this->_ch);
		$this->_info  = curl_getinfo($this->_ch);
		curl_close($this->_ch);
		/*
    	if($this->_logger->getMode() == LOGGER_MODE_ENABLED ) {
			$msg = "CURL: url=".$this->_url." error:".$this->_error." method:".$this->_method." code:".$this->_info['http_code']." params:".$this->_convertParams()." out: ".$out;
			//$this->_logger->log(LOGGER_LEVEL_NOTICE, $msg);
    	}
    	*/
		return $out;
	}
	
	private function _convertParams() {
		foreach($this->_params as $key => $value) {
			$params[] = urlencode($key).'='.urlencode($value);
		}
		return implode('&', $params);
	}
	
    private function _setOps() {
		curl_setopt($this->_ch, CURLOPT_URL, $this->_url);
		curl_setopt($this->_ch, CURLOPT_HEADER, 0);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);                                                                                                                                                                                 
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 0);    	
    }
}
