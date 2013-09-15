<?php
class Request {
	private $_action;
	private $_subAction;
	private $_delimiter = '/';
	private $_params;
	private $_otherParams;
	
	//private $_logger;
		
    function Request($pRequest, $pAction = false, $pSubAction = false) {
    	//$this->_logger = & $GLOBALS['logger'];
    	foreach($pRequest as $k=>$v) {
    	    if(strpos($k, '___') !== false) {
    		$pRequest[str_replace('___','.',$k)] = $v;
    		unset($pRequest[$k]);
    	    }
    	}
    	$this->_processRequest($pRequest);
    	if($pAction) {
    		$this->_action = $pAction;
    	}
    	if($pSubAction) {
    		$this->_subAction = $pSubAction;
    	}
    }

	/**
	 * Processes the request array.Sets required private variables
	 * 
	 */
	private function _processRequest($pRequest) {
		$pos = strpos($_SERVER['QUERY_STRING'], '&');
		if($pos) {
			$req = substr($_SERVER['QUERY_STRING'], 0, strpos($_SERVER['QUERY_STRING'], '&'));
		} else {
			$req = $_SERVER['QUERY_STRING'];
		}
		$exp = explode($this->_delimiter, $req);
		$this->_action    = $exp[0]?$exp[0]:"index";
		$this->_subAction = $exp[1];
		$this->_params = @array_slice($exp, 2);
		$this->_otherParams = @array_slice( $pRequest, 1);
	}
	
    /**
     * Returns action string
     * 
     * @return string user action
     */
    public function getAction() {
    	return $this->_action;
    }

    /**
     * Returns subAction string
     * 
     * @return string user subAction
     */
    public function getSubAction() {
    	return $this->_subAction;
    }
    
    /**
     * Returns ordered parameter
     * 
     * @param int parameter index
     * @return mixed user ordered param
     */
    public function getParamByOrder($pOrder) {
    	return $this->_params[$pOrder];
    }
    
    public function getParamArray() {
	return $this->_params;
    }
    
    /**
     * Returns named parameter
     * 
     * @param string parameter name
     * @return mixed parameter value
     */
     public function getParamByName($pName) {
     	return $this->_otherParams[$pName];
     }
     
     public function getNamedParamsArray() {
     	return $this->_otherParams;
     }
     
}
