<?php
require_once("DB.php");

/**
 * A simple preference manager, takes userid, preference name pairs and returns the value
 * of that preference.
 *  
 * CREATE TABLE `preferences` (
 * `user_id` varchar( 255 ) NOT NULL default '',
 * `pref_id` varchar( 32 ) NOT NULL default '',
 * `pref_value` longtext NOT NULL ,
 * 	PRIMARY KEY ( `user_id` , `pref_id` )
 * }
 * 
 * @author Jon Wood <jon@jellybob.co.uk>
 */ 
class Auth_PrefManager
{
    /**
     * The database object.
     * @var object
     * @access private
     */
    var $_db;

    /**
     * The user name to get preferences from if the user specified doesn't
     * have that preference set.
     * @var string
     * @access private
     */
    var $_defaultUser = "__default__";

    /**
     * Should we search for default values, or just fail when we find out that
     * the specified user didn't have it set.
     * 
     * @var bool
     * @access private
     */
    var $_returnDefaults = true;

    /**
     * The table containing the preferences.
     * @var string
     * @access private
     */
    var $_table = "preferences";

    /**
     * The column containing user ids.
     * @var string
     * @access private
     */
    var $_userColumn = "user_id";

    /**
     * The column containing preference names.
     * @var string
     * @access private
     */
    var $_nameColumn = "pref_id";

    /**
     * The column containing preference values.
     * @var string
     * @access private
     */
    var $_valueColumn = "pref_value";

    /**
     * The session variable that the cache array is stored in.
     * @var string
     * @access private
     */
     var $_cacheName = "prefCache";

    /**
     * The last error given.
     * @var string
     * @access private
     */
    var $_lastError;

	/**
	 * Defines whether the cache should be used or not.
	 * @var bool
	 * @access private
	 */
	var $_useCache = true;
	
    /**
     * Constructor
     * 
     * Options:
     *  table: The table to get prefs from. [preferences]
     *  userColumn: The field name to search for userid's [user_id]
     *  nameColumn: The field name to search for preference names [pref_name]
     *  valueColumn: The field name to search for preference values [pref_value]
     *  defaultUser: The userid assigned to default values [__default__]
     *  cacheName: The name of cache in the session variable ($_SESSION[cacheName]) [prefsCache]
	 *  useCache: Whether or not values should be cached.
     * 
     * @param string $dsn The DSN of the database connection to make.
     * @param array $properties An array of properties to set.
     * @param string $defaultUser The default user to manage for.
     * @return bool Success or failure.
     * @access public
     */
    function Auth_PrefManager($dsn, $properties = NULL)
    {
        // Connect to the database.
        if ($dsn && ($dsn != "")) {
            $this->_db = DB::Connect($dsn);
            if (DB::isError($this->_db)) {
                $this->_lastError = "DB Error: ".$this->_db->getMessage();
            }
        } else {
            $this->_lastError = "No DSN specified.";
            return false;
        }

        if (is_array($properties)) {
            if ($properties["table"]) { $this->_table = $properties["table"]; }
            if ($properties["userColumn"]) { $this->_userColumn = $properties["userColumn"]; }
            if ($properties["nameColumn"]) { $this->_nameColumn = $properties["nameColumn"]; }
            if ($properties["valueColumn"]) { $this->_valueColumn = $properties["valueColumn"]; }
            if ($properties["defaultUser"]) { $this->_defaultUser = $properties["defaultUser"]; }
            if ($properties["cacheName"]) { $this->_cacheName = $properties["cacheName"]; }
			if ($properties["useCache"]) { $this->_useCache = $properties["useCache"]; }
        }

        if ($defaultUser && ($defaultUser != "")) {
            $this->_defaultUser = $defaultUser;
        }

        return true;
    }

    function setReturnDefaults($returnDefaults = true)
    {
        if (is_bool($returnDefaults)) {
            $this->_returnDefaults = $returnDefaults;
        }
    }

	/**
	 * Sets whether the cache should be used.
	 * 
	 * @param bool $use Should the cache be used.
	 * @access public
	 */
	function useCache($use = true)
	{
		$this->_useCache = $use;
	}
	
    /**
     * Cleans out the cache.
     * 
     * @access public
     */
    function clearCache()
    {
        unset($_SESSION[$this->_cacheName]);
    }

    /**
     * Get a preference for the specified user, or, if returning default values
     * is enabled, the default.
     * 
     * @param string $user_id The user to get the preference for.
     * @param string $pref_id The preference to get.
     * @param bool $showDefaults Should default values be searched (overrides the global setting).
     * @return mixed The value if it's found, or NULL if it isn't.
     * @access public
     */
    function getPref($user_id, $pref_id, $showDefaults = true)
    {
        if (isset($_SESSION[$this->_cacheName][$user_id][$pref_id]) && $this->_useCache) {
            // Value is cached for the specified user, so give them the cached copy.
            return $_SESSION[$this->_cacheName][$user_id][$pref_id];
        } else {
            // Not cached, search the database for this user's preference.
            $query = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s'", $this->_table,
			                                                               $this->_userColumn,
                                                                           addslashes($user_id),
                                                                           $this->_nameColumn,
                                                                           addslashes($pref_id));
            $result = $this->_db->query($query);
            if (DB::isError($result)) {
                // Ouch! The query failed!
                $this->_lastError = "DB Error: ".$result->getMessage();
                return NULL;
            } else if ($result->numRows()) {
                // The query found a value, so we can cache that, and then return it.
                $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
                $_SESSION[$this->_cacheName][$user_id][$pref_id] = $row[$this->_valueColumn];
                return $_SESSION[$this->_cacheName][$user_id][$pref_id];
            } else if ($this->_returnDefaults && $showDefaults) {
                // I was doing this with a call to getPref again, but it threw things into an
                // infinite loop if the default value didn't exist. If you can fix that, it would
                // be great ;)
                if (isset($_SESSION[$this->_cacheName][$this->_defaultUser][$pref_id]) && $this->_useCache) {
                    $_SESSION[$this->_cacheName][$user_id][$pref_id] = $_SESSION[$this->_cacheName][$this->_defaultUser][$pref_id];
                    return $_SESSION[$this->_cacheName][$this->_defaultUser][$pref_id];
                } else {
                    $query = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s'", $this->_table,
			                                                                       $this->_userColumn,
                                                                                   addslashes($this->_defaultUser),
                                                                                   $this->_nameColumn,
                                                                                   addslashes($pref_id));
                    $result = $this->_db->query($query);
                    if (DB::isError($result)) {
                        $this->_lastError = "DB Error: ".$result->getMessage();
                        return NULL;
                    } else {
                        if ($result->numRows()) {
                            $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
                            $_SESSION[$this->_cacheName][$this->_defaultUser][$pref_id] = $row[$this->_valueColumn];
                            $_SESSION[$this->_cacheName][$user_id][$pref_id] = $_SESSION[$this->_cacheName][$this->_defaultUser][$pref_id];
                            return $_SESSION[$this->_cacheName][$user_id][$pref_id];
                        } else {
                            return NULL;
                        }
                    }
                }
            } else {
                // We've used up all the resources we're allowed to search, so return a NULL.
                return NULL;
            }
        }
    }

    /**
     * Set a preference for the specified user.
     * 
     * @param string $user_id The user to set for.
     * @param string $pref_id The preference to set.
     * @param mixed $value The value it should be set to.
     * @return bool Sucess or failure.
     * @access public
     */
    function setPref($user_id, $pref_id, $value)
    {
        // Start off by checking if the preference is already set (if it is we need to do
        // an UPDATE, if not, it's an INSERT.
        if ($this->_exists($user_id, $pref_id, false)) {
            $query = sprintf("UPDATE %s SET %s='%s' WHERE %s='%s' AND %s='%s'", $this->_table,
                                                                                $this->_valueColumn,
                                                                                addslashes($value),
                                                                                $this->_userColumn,
                                                                                addslashes($user_id),
                                                                                $this->_nameColumn,
                                                                                addslashes($pref_id));
        } else {
            $query = sprintf("INSERT INTO %s (%s, %s, %s) VALUES('%s', '%s', '%s')", $this->_table,
                                                                                     $this->_userColumn,
                                                                                     $this->_nameColumn,
                                                                                     $this->_valueColumn,
                                                                                     addslashes($user_id),
                                                                                     addslashes($pref_id),
                                                                                     addslashes($value));
        }
        $result = $this->_db->query($query);
        if (DB::isError($result)) {
            $this->_lastError = "DB Error: ".$result->getMessage();
            return false;
        } else {
			if ($this->_useCache) {
			    $_SESSION[$this->_cacheName][$user_id][$pref_id] = $value;
			}
            return true;
        }
    }

    /**
    * A shortcut function for setPref($this->_defaultUser, $pref_id, $value)
    * 
    * @param string $pref_id The name of the preference to set.
    * @param mixed $value The value to set it to.
    * @return bool Sucess or failure.
    * @access public
    */
    function setDefaultPref($pref_id, $value)
    {
        return $this->setPref($this->_defaultUser, $pref_id, $value);
    }

    /**
    * Deletes a preference for the specified user.
    * 
    * @param string $user_id The userid of the user to delete from.
    * @param string $pref_id The preference to delete.
    * @return bool Success/Failure
    * @access public
    */
    function deletePref($user_id, $pref_id)
    {
        if ($this->getPref($user_id, $pref_id) == NULL) {
            // The user doesn't have this variable anyway ;)
            return true;
        } else {
            $query = sprintf("DELETE FROM %s WHERE %s='%s' AND %s='%s'", $this->_table,
                                                                         $this->_userColumn,
                                                                         addslashes($user_id),
                                                                         $this->_nameColumn,
                                                                         addslashes($pref_id));
            $result = $this->_db->query($query);
            if (DB::isError($result)) {
                $this->_lastError = "DB Error: ".$result->getMessage();
                return false;
            } else {
				if ($this->_useCache) {
				    unset($_SESSION[$this->_cacheName][$user_id][$pref_id]);
				}
                return true;
            }
        }
    }

    /**
    * Deletes a preference for the default user.
    * 
    * @param string $pref_id The preference to delete.
    * @return bool Success/Failure
    * @access public
    */
    function deleteDefaultPref($pref_id)
    {
        $this->deletePref($this->_defaultUser, $pref_id);
    }
	
	/**
	 * Checks if a preference exists in the database.  
	 *
	 * @param string $user_id The userid of the preference owner.
	 * @param string $pref_id The preference to check for.
	 * @return bool True if the preference exists.
	 * @access private
	 */
	function _exists($user_id, $pref_id)
	{
		$query = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s'", $this->_table,
                                                                       $this->_userColumn,
																	   addslashes($user_id),
																	   $this->_nameColumn,
																	   addslashes($pref_id));
	    $result = $this->_db->query($query);
		if (DB::isError($result)) {
            $this->_lastError = "DB Error: ".$result->getMessage();
            return false;
        } else {
            if ($result->numRows()) {
                return true;
            } else {
				return false;
			}
        }
	}
}
?>