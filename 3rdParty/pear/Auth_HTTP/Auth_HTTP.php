<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Martin Jansen <mj@php.net>                                  |
// +----------------------------------------------------------------------+
//
// $Id: Auth_HTTP.php,v 1.1.1.1 2003/07/14 12:56:06 ozan Exp $
//

require_once "Auth/Auth.php";

/**
 * PEAR::Auth_HTTP
 *
 * The PEAR::Auth_HTTP class provides methods for creating an
 * HTTP authentication system using PHP.
 *
 * Instead of generating an HTML driven form like PEAR::Auth
 * does, this class sends header commands to the clients which
 * cause them to present a login box like they are e.g. used
 * in Apache's .htaccess mechanism.
 *
 * This class requires the PEAR::Auth package.
 *
 * @author  Martin Jansen <mj@php.net>
 * @package Auth_HTTP
 * @extends Auth
 * @version $Revision: 1.1.1.1 $
 */
class Auth_HTTP extends Auth
{

    // {{{ properties

    /**
     * Name of the realm
     *
     * @access public
     * @var    string
     * @see    drawLogin()
     */
    var $realm = "protected area";

    /**
     * Text to send if user hits cancel button
     *
     * @access public
     * @var    string
     * @see    drawLogin()
     */
    var $CancelText = "Error 401 - Access denied";

    // }}}
    // {{{ assignData()

    /**
     * Assign values from $PHP_AUTH_USER and $PHP_AUTH_PW
     * to internal variables
     *
     * @global $PHP_AUTH_USER, $PHP_AUTH_PW
     */
    function assignData()
    {
        global $PHP_AUTH_USER,$PHP_AUTH_PW;

        if ($PHP_AUTH_USER != "") {
            $this->username = $PHP_AUTH_USER;
        }

        if ($PHP_AUTH_PW != "") {
            $this->password = $PHP_AUTH_PW;
        }
    }

    // }}}
    // {{{ drawLogin()

    /**
     * Launch the login box
     *
     * @param string $username  Username
     * @param string $password  Password
     */
    function drawLogin($username = "")
    {
        /**
         * Send the header commands
         */
        header("WWW-Authenticate: Basic realm=\"".$this->realm."\"");
        header("HTTP/1.0 401 Unauthorized");

        /**
         * This code is only executed if the user hits the cancel
         * button or if he enters wrong data 3 times.
         */
        echo $this->CancelText;
        exit;
    }

    // }}}
    // {{{ setRealm()

    /**
     * Set name of the current realm
     *
     * @access public
     * @param  string $name  Name of the realm
     */
    function setRealm($name)
    {
        $this->realm = $name;
    }

    // }}}
    // {{{ setCancelText()

    /**
     * Set the text to send if user hits the cancel button
     *
     * @access public
     * @param  string $text  Text to send
     */
    function setCancelText($text)
    {
        $this->CancelText = $text;
    }

    // }}}
}
?>
