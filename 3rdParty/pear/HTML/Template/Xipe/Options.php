<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001, 2002, 2003 The PHP Group |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Wolfram Kriesing <wolfram@kriesing.de>                      |
// +----------------------------------------------------------------------+
//  $Id: Options.php,v 1.1.1.1 2003/07/14 12:57:13 ozan Exp $
//

/**
*   this class only defines commonly used methods, etc.
*   it is worthless without being extended
*
*   @package  HTML_Template_Xipe
*   @access   public
*   @author   Wolfram Kriesing <wolfram@kriesing.de>
*
*/
class HTML_Template_Xipe_Options
{
    /**
    *   @var    array   $options    you need to overwrite this array and give the keys, that are allowed
    */
    var $options = array();

    var $_forceSetOption = false;

    /**
    *   this constructor sets the options, since i normally need this and
    *   in case the constructor doesnt need to do anymore i already have it done :-)
    *
    *   @version    02/01/08
    *   @access     public
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @param      array       the key-value pairs of the options that shall be set
    *   @param      boolean     if set to true options are also set
    *                           even if no key(s) was/were found in the options property
    */
    function HTML_Template_Xipe_Options( $options=array() , $force=false )
    {
        $this->_forceSetOption = $force;

        if( is_array($options) && sizeof($options) )
            foreach( $options as $key=>$value )
                $this->setOption( $key , $value );
    }

    /**
    *
    *   @access     public
    *   @author     Stig S. Baaken
    *   @param
    *   @param
    *   @param      boolean     if set to true options are also set
    *                           even if no key(s) was/were found in the options property
    */
    function setOption( $option , $value , $force=false )
    {
        if( is_array($value) )                      // if the value is an array extract the keys and apply only each value that is set
        {                                           // so we dont override existing options inside an array, if an option is an array
            foreach( $value as $key=>$aValue )
                $this->setOption( array($option , $key) , $aValue );
            return true;
        }

        if( is_array($option) )
        {
            $mainOption = $option[0];
            $options = "['".implode("']['",$option)."']";
            $evalCode = "\$this->options".$options." = \$value;";
        }
        else
        {
            $evalCode = "\$this->options[\$option] = \$value;";
            $mainOption = $option;
        }

        if( $this->_forceSetOption==true || $force==true || isset($this->options[$mainOption]) )
        {
            eval($evalCode);
            return true;
        }
        return false;
    }

    /**
    *   set a number of options which are simply given in an array
    *
    *   @access     public
    *   @author
    *   @param
    *   @param      boolean     if set to true options are also set
    *                           even if no key(s) was/were found in the options property
    */
    function setOptions( $options , $force=false )
    {
        if( is_array($options) && sizeof($options) )
        {
            foreach( $options as $key=>$value )
            {
                $this->setOption( $key , $value , $force );
            }
        }
    }

    /**
    *
    *   @access     public
    *   @author     copied from PEAR: DB/commmon.php
    *   @param      boolean true on success
    */
    function getOption($option)
    {
        if( func_num_args() > 1 &&
            is_array($this->options[$option]))
        {
            $args = func_get_args();
            $evalCode = "\$ret = \$this->options['".implode( "']['" , $args )."'];";
            eval( $evalCode );
            return $ret;
        }

        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
//        return $this->raiseError("unknown option $option");
        return false;
    }

    /**
    *   returns all the options
    *
    *   @version    02/05/20
    *   @access     public
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @return     string      all options as an array
    */
    function getOptions()
    {
        return $this->options;
    }
} // end of class
?>
