<?php
define('LOGGER_MODE_ENABLED',	0);
define('LOGGER_MODE_DISABLED',	1);

define('LOGGER_LEVEL_WARNING',	'warning');
define('LOGGER_LEVEL_NOTICE',	'notice');
define('LOGGER_LEVEL_ERROR',	'error');
define('LOGGER_LEVEL_FATAL',	'fatal');

/**
 * Misc logging class
 * 
 */
class Logger {
	private $_mode;
	private $_logFile;
	private $_logFd;
	private $_logFormat = "#@date@# #@level@# #@uri@# #@message@# #@count@#\n";
	
	function Logger($pMode, $pLogFile = '/opt/var/log/app.log') {
		$this->_logFile = $pLogFile;
		$this->_mode = $pMode;
	}
	
	/**
	 * Writes $pMessage to log file
	 * 
	 * @param string $pLevel WARNING|NOTICE|ERROR|FATAL
	 * @param string $pMessage Mesage to log
	 * @return bool 
	 */
	public function log($pLevel, $pMessage) {
		if($this->_mode == LOGGER_MODE_DISABLED ) {
			return false;
		}
		$status = $this->_openLog();
		if(!$status) {
			return false;
		}
		
		$msg = str_replace('#@date@#', date('Y/m/d H:i:s'), $this->_logFormat);
		$msg = str_replace('#@level@#', $pLevel, $msg);
		$msg = str_replace('#@uri@#', $_SERVER['REQUEST_URI'], $msg);
		$msg = str_replace('#@message@#', $pMessage, $msg);
		$msg = str_replace('#@count@#', count($_POST)."/".count($_GET), $msg);

		fputs($this->_logFd, $msg);
	}
	
	public function getMode() {
		return $this->_mode;
	}
	
	/**
	 * Opens logFile for append
	 * 
	 */
	private function _openLog() {
		if($this->_logFd) {
			return true;
		}
		$this->_logFd = @fopen($this->_logFile, 'a+');
		return $this->_logFd;
	}
}
