<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty numberformat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     numberformat<br>
 * Purpose:  Replace all repeated spaces, newlines, tabs
 *           with a single space or supplied replacement string.<br>
 * Example:  {$var|numberformat} {$var|numberformat:"&nbsp;"}
 * Date:     September 25th, 2002
 * @link http://smarty.php.net/manual/en/language.modifier.numberformat.php
 *          numberformat (Smarty online manual)
 * @author   Monte Ohrt <monte@ispi.net>
 * @version  1.0
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_numberformat($text)
{
	return number_format($text);
}

/* vim: set expandtab: */

?>
