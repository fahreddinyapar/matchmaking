<?php

require_once 'PEAR.php';
require_once 'DB.php';

/**
* Translation class
*
* Class allows to store and retrieve all the strings on multilingual site in the 
* database.
* Class connects to any database using PHP PEAR extension - so it needs 
* PEAR to be installed to work correctly.
* The object should be created for every page. While creation all the strings 
* connected with specific page and the strings connected with all the pages 
* on the site are loaded into variable, so access to them is quite fast and 
* does not overload database server connection.
* Reducing the DB connections by possibility to use the existing ones - the 
* constructor right now needs the PEAR DB DSN to make the connection do 
* the DB. The new version will allow user to use the existing DB connection 
* (PEAR DB connection) instead of making the new one. To do so he just needs 
* to pass to the constructor the handle for this connection instead of DSN.
* 
* @author Wojciech Zieli�ski <voyteck@caffe.com.pl>
* @version 1.2.3
* @access public
* @package Translation
*/
class Translation extends PEAR {

/**
* The translations are retrieved from this class object
* @see Translation(), gstr(), getLangName(), getOtherLangs(), getMetaTags
*/

/**
* Strings of the given page array
*
* This one is for fast access to cached strings for specified page
*
* @var array string $Strings
*/
	var $Strings = array();

/**
* Page identifier
*
* @var string $PageName
*/
	var $PageName = "";

/**
* Language identifier
*
* @var string $LanguageID
*/
	var $LanguageID = "";

/**
* Connection to database
*
* @var object DB $db
*/
	var $db;
	
/**
* Is true if the connection was reused, not made from the class
*
* @var int $ConnectionReused
*/
	var $ConnectionReused;

/**
* The string that will be displayed, if no string has been found in the DB for specified string_id
*
* @var string $ErrorText
*/
	var $ErrorText;
	
/**
* The array with table and column names
*
* The table may have following items:
*
* 'langsavail' - table, in which all the information of the possible languages 
* is kept. This array item may be the string - then the structure of the table 
* remains as original, but the name is specified here; or the array with the 
* following items:
* 'name' - the name of the table - default is 'tr_langsavail'
* 'lang_id' - the column that stores the language identifier - default 
* is 'lang_id'
* 'lang_name' - the column that stores the language name - default 
* is 'name'
* 'metatags' - the column that stores meta tags for the pages in specified 
* language - default is 'metatags'
* 'errortext' - the column that stores the text that will be displayed in case 
* if some text will not be found in the DB - default is 'errortext'
*
* 'strings_XX' - table, in which the strings of language "XX" (the 
* corresponding lang_id) are kept. This array item may be the string - then 
* the structure of the table remains as original, but the name is specified 
* here; or the array with the following items:
* 'name' - the name of the table - default is 'tr_strings_XX'
* 'page_id' - the page identifier - default is 'page_id'.
* 'string_id' - the string indetifier - default is 'string_id'.
* 'string' - the string itself - default is 'string'.
*
* This parameter in fact has impact only if the DB is used as the strings 
* repository. The defaults are set in the way that the method is compatible 
* with lower versions.
*
* @var array $CustomTables
*/
	var $TableDefinitions;
	
/**
* Class constructor
*
* @param string $PageName					the page identifier. It identifies 
* strings connected with specific page on the site
* @param string $LanguageID 				language id. All the languages 
* are stored on the database on specific ID's.
* @since version 1.2.1
* @param string $pear_DSN					This might be 3 types: the PEAR DSN 
* string form making the connection; the PEAR DB connection handle; the string 
* in the format: 
* gettext://LOCALE:LANG:BINDTXTDOMAIN:TXTDOMAINFILE:TXTDOMAIN:CFGFILE 
* for using the native PHP gettext support.
* @param array $CustomTables				This is the array of the names of the tables and 
* optionally the names of columns. It contains the following elements:
*/
	function Translation($PageName, $LanguageID, $pear_DSN, $CustomTables = 0) {
		$this->PageName = $PageName;
		$this->LanguageID = $LanguageID;
		if (! DB::isConnection($pear_DSN)) {
			$this->db = DB::connect($pear_DSN);
			$this->ConnectionReused = 0;
		}
		else {
			$this->db = $pear_DSN;
			$this->ConnectionReused = 1;
		}
		if (DB::isError($this->db))
			die ($this->db->getMessage());
			
	    $this->TableDefinitions = array('langsavail'=>array('name'=>'tr_langsavail', 'lang_id'=>'lang_id', 'lang_name'=>'name', 'metatags'=>'metatags', 'errortext'=>'errortext'));
		$result = $this->db->query("SELECT " . $this->TableDefinitions['langsavail']['lang_id'] . " FROM " . $this->TableDefinitions['langsavail']['name']);
		if (DB::isError($result))
			die ($result->getMessage());
		while($row = $result->fetchRow()) {
			$this->TableDefinitions['strings_' . $row[0]] = array('name'=>('tr_strings_' . $row[0]), 'page_id'=>'page_id', 'string_id'=>'string_id', 'string'=>'string');
			if (is_array($CustomTables['strings_' . $row[0]])) {
			    ((($str = $CustomTables['strings_' . $row[0]]['name']) != "") ? ($this->TableDefinitions['strings_' . $row[0]]['name'] = $str) : $str = $str);
				((($str = $CustomTables['strings_' . $row[0]]['page_id']) != "") ? ($this->TableDefinitions['strings_' . $row[0]]['page_id'] = $str) : $str = $str);
				((($str = $CustomTables['strings_' . $row[0]]['string_id']) != "") ? ($this->TableDefinitions['strings_' . $row[0]]['string_id'] = $str) : $str = $str);
				((($str = $CustomTables['strings_' . $row[0]]['string']) != "") ? ($this->TableDefinitions['strings_' . $row[0]]['string'] = $str) : $str = $str);
			}
			elseif ($CustomTables['strings_' . $row[0]] != "")
				$this->TableDefinitions['strings_' . $row[0]]['name'] = $CustomTables['strings_' . $row[0]];
		}
		if (is_array($CustomTables['langsavail'])) {
			((($str = $CustomTables['langsavail']['name']) != "") ? $this->TableDefinitions['langsavail']['name'] = $str : $str = $str);
			((($str = $CustomTables['langsavail']['lang_id']) != "") ? $this->TableDefinitions['langsavail']['lang_id'] = $str : $str = $str);
			((($str = $CustomTables['langsavail']['lang_name']) != "") ? $this->TableDefinitions['langsavail']['lang_name'] = $str : $str = $str);
			((($str = $CustomTables['langsavail']['metatags']) != "") ? $this->TableDefinitions['langsavail']['metatags'] = $str : $str = $str);
			((($str = $CustomTables['langsavail']['errortext']) != "") ? $this->TableDefinitions['langsavail']['errortext'] = $str : $str = $str);
		}
		elseif ($CustomTables['langsavail'] != "")
			$this->TableDefinitions['langsavail']['name'] = $CustomTables['langsavail'];
		
		$result = $this->db->query("SELECT " . $this->TableDefinitions['strings_' . $LanguageID]['string_id'] . ", " . $this->TableDefinitions['strings_' . $LanguageID]['string'] . " FROM " . $this->TableDefinitions['strings_' . $LanguageID]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $LanguageID]['page_id'] . " = '$PageName' OR " . $this->TableDefinitions['strings_' . $LanguageID]['page_id'] . " = ''");
		if (DB::isError($result)) {
			die ($result->getMessage());
		}
		while ($row = $result->fetchRow())
			$this->Strings[$row[0]] = $row[1];
		$this->ErrorText = $this->db->getOne("SELECT " . $this->TableDefinitions['langsavail']['errortext'] . " FROM " . $this->TableDefinitions['langsavail']['name'] . " WHERE " . $this->TableDefinitions['langsavail']['lang_id'] . " = '$LanguageID'");
	}
	
/**
* Class destructor
*
* @since version 1.2.1
*/
	function _Translation() {
		if (! $this->ConnectionReused) {
			$this->db->disconnect();
		}
	}
	
/**
* Translated string retrieval
*
* Retrieves the string basing on string identifier (string_id) or by 
* page_id.string_id. It means the strings_id may be specified in 2 ways:
* - as normal string_id - then the string is retrieved from the cached version
* of strings in the current page_id
* - as [page_id].[string_id] - then teh strings is retrieved from the DB by getting
* it from the given page_id. The additional optimization mechanism checks
* if the page_id is not the same, as the current - if it is - the string will still be
* retrieved from the cached version.
* Also in the Params array there can be the item 'lang_id'. If not 'action' has 
* been given then the string from specified lang_id (in specified language) 
* will be retrieved. This solution is not recommended for common usage 
* (e.g. for the whole pages) as this will not use the cached in the constructor 
* strings, but will make another query to the DB for the specified query.
* Another item that can be this array is 'action'. This specified special 
* actions that should be performed. The special actions can be the following:
* 'normal' - this is default setting. It means that method will act as if this 
* item was not given.
* 'translate' - this will couse, that the $StringName will not be the 
* string_id, but the string itself. This must be set only together with 
* 'lang_id' item. Method will try to find the specified string in the foreign 
* (specified in lang_id) language and will retrieve the corresponding string in 
* the current language. This solution is not recommended for common usage 
* (e.g. for the whole pages) as this will not use the cached in the constructor 
* strings, but will make another query to the DB for the specified query.
* Item 'optimization' is used to specify the optimization of the queries - 
* if the main load should be performed by the PHP server (by doing more, but 
* less complicated queries) or DB server (PHP is sending only one, but 
* complicated query). This one is used only together with specified 'lang_id' 
* and 'action'=>'translate' - as this performs more operations, then other 
* queries. It can have following values:
* 'php' - the default setting, cousing PHP to make 2 (or 3 if the string will 
* be found in some other page_id then the current) uncomplicated queries. This 
* is recommened if the DB server is the same machine as the PHP server.
* 'db' - this couses PHP to make only 1, but comlicated query. This is 
* recommended if the DB server is separate machine, then PHP server. WARNING: 
* This will not work with MySQL DB server and Db servers, that does not 
* supports nested SELECTs (SELECT ... FROM ... WHERE sth = (SELECT...))
*
* @param string $StringName		string identifier - unique for the page as well as for the strings, that are available on all the pages.
* @param array string $Params	string may be parametrized - and the paraters, that will be "inserted" into string may be typen into this array. It means, that &&1&& string will be replaced by 1st array element; string &&2&& will be replaced by 2nd array element; string &&3&& will be replaced by 3rd array element etc.
* @return string retrieved string
*/
	function gstr($StringName, $Params = array()) {
		if (($returnstring = $this->Strings[$StringName]) == "")
		    $returnstring = $this->ErrorText;
		if (($eregged = ereg("([_a-zA-Z]*)\.([_a-zA-Z]*)", $StringName, $regs)) && $Params['action'] != 'translate') {
			if ($reg[2] == $this->PageName) {
				if (($returnstring = $this->Strings[$StringName]) == "")
					$returnstring = $this->ErrorText;
			}
			else {
				$returnstring = $this->db->getOne("SELECT " . $this->TableDefinitions['strings_' . $this->LanguageID]['string'] . " FROM " . $this->TableDefinitions['strings_' . $this->LanguageID]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $this->LanguageID]['page_id'] . " = '" . $regs[1] . "' AND " . $this->TableDefinitions['strings_' . $this->LanguageID]['string_id'] . " = '" . $regs[2] . "'");
				if (DB::isError($returnstring))
					$returnstring = $this->ErrorText;
			}
		}
		$i = 0;
		if (count($Params) > 0 && ! $eregged) {
			if ($Params['lang_id'] != "" && ($Params['action'] == 'normal' || $Params['action'] == '')) {
				$returnstring = $this->db->getOne("SELECT " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string'] . " FROM " . $this->TableDefinitions['strings_' . $Params['lang_id']]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $Params['lang_id']]['page_id'] . " = '" . $this->PageName . "' AND " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string_id'] . " = '" . $StringName . "'");
				if (DB::isError($returnstring))
				    $returnstring = $this->ErrorText;
			}
			if ($Params['lang_id'] != "" && $Params['action'] == 'translate' && ! $eregged) {
				if ($Params['optimization'] == 'php' || $Params['optimization'] == '') {
				    $PageID = $this->db->getOne("SELECT " . $this->TableDefinitions['strings_' . $Params['lang_id']]['page_id'] . " FROM " . $this->TableDefinitions['strings_' . $Params['lang_id']]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string'] . " = '" . $StringName . "'");
					$StringID = $this->db->getOne("SELECT " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string_id'] . " FROM " . $this->TableDefinitions['strings_' . $Params['lang_id']]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string'] . " = '" . $StringName . "'");
					if ($PageID == "" || $PageID == $this->PageName) {
					    if (($returnstring = $this->Strings[$StringID]) == "")
		    				$returnstring = $this->ErrorText;
					}
					else {
						$returnstring = $this->db->getOne("SELECT " . $this->TableDefinitions['strings_' . $this->LanguageID]['string'] . " FROM " . $this->TableDefinitions['strings_' . $this->LanguageID]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $this->LanguageID]['page_id'] . " = '" . $PageID . "' AND " . $this->TableDefinitions['strings_' . $this->LanguageID]['string_id'] . " = '" . $StringID . "'");
						if (DB::isError($returnstring))
				    		$returnstring = $this->ErrorText;
					}
				}
				elseif ($Params['optimization'] == 'db') {
					$returnstring = $this->db->getOne("SELECT " . $this->TableDefinitions['strings_' . $this->LanguageID]['string'] . " FROM " . $this->TableDefinitions['strings_' . $this->LanguageID]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $this->LanguageID]['page_id'] . " = (" . "SELECT " . $this->TableDefinitions['strings_' . $Params['lang_id']]['page_id'] . " FROM " . $this->TableDefinitions['strings_' . $Params['lang_id']]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string'] . " = '" . $StringName . "'" . ") AND " . $this->TableDefinitions['strings_' . $this->LanguageID]['string_id'] . " = (" . "SELECT " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string_id'] . " FROM " . $this->TableDefinitions['strings_' . $Params['lang_id']]['name'] . " WHERE " . $this->TableDefinitions['strings_' . $Params['lang_id']]['string'] . " = '" . $StringName . "'" . ")");
					if (DB::isError($returnstring))
			    		$returnstring = $this->ErrorText;
				}
			}
			while($Params[$i] != "") {
				$returnstring = str_replace("&&" . ++$i . "&&", $Params[$i-1], $returnstring);
			}
		}
		return $returnstring;
    }
	
/**
* Active language name retrieval
*
* Retrieves the name of active language
*
* @return string current active language name or PEAR_ERROR object in case of problems
*/
	function getLangName() {
		$result = $this->db->getRow("SELECT " . $this->TableDefinitions['langsavail']['lang_name'] . " FROM " . $this->TableDefinitions['langsavail']['name'] . " WHERE " . $this->TableDefinitions['langsavail']['langid'] . " = '" . $this->LanguageID . "'");
		if (DB::isError($result))
			return $result;
		return $result[0];
	}
	
/**
* Other languages retrieval
*
* Retrieves names of all other languages, not the active.
*
* @return array 2-dimensional array (0..)('id', 'name') for all the languages defined in DB but the current selected one. In case of DB error - returns PEAR_ERROR object.
*/
	function getOtherLangs() {
		$result = $this->db->query("SELECT " . $this->TableDefinitions['langsavail']['lang_id'] . ", " . $this->TableDefinitions['langsavail']['lang_name'] . " FROM " . $this->TableDefinitions['langsavail']['name'] . " WHERE " . $this->TableDefinitions['langsavail']['lang_id'] . " <> '" . $this->LanguageID . "'");
		if (DB::isError($result))
			return ($result->getMessage());
		$i = 0;
		while($row = $result->fetchRow()) {
			$returnarray[$i]["id"] = $row[0];
			$returnarray[$i++]["name"] = $row[1];
		}
		return $returnarray;
	}
	
/**
* META tags retrieval
*
* Retrievs the META tags that should be added on the top of translated page, so the translated characters will be correctly displayed on client's browser.
*
* @return string with configured in DB META tags for selected language. In case of DB error - returns PEAR_ERROR object.
*/
	function getMetaTags() {
		$result = $this->db->getRow("SELECT " . $this->TableDefinitions['langsavail']['metatags'] . " FROM " . $this->TableDefinitions['langsavail']['name'] . " WHERE " . $this->TableDefinitions['langsavail']['lang_id'] . " = '" . $this->LanguageID . "'");
		if (DB::isError($result))
			return ($result->getMessage());
		return $result[0];
	}
}


?>