<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002, Richard Heyes                                     |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.| 
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// +-----------------------------------------------------------------------+
//
// $Id: Net_POP3_example.php,v 1.1.1.1 2003/07/14 12:57:54 ozan Exp $
?>
<html>
<body>
<?php

include('Net_POP3.php');

/*
* Create the class
*/
$pop3 =& new Net_POP3();

/*
* Connect to localhost on usual port
* If not given, defaults are localhost:110
*/
$pop3->connect('localhost', 110);

/*
* Login using username/password. APOP will
* be tried first if supported, then basic.
*/
$pop3->login('richard', 'Alien3');

/*
* Get the raw headers of message 1
*/
echo '<h2>getRawHeaders()</h2>';
echo '<pre>' . htmlspecialchars($pop3->getRawHeaders(1)) . '</pre>';

/*
* Get structured headers of message 1
*/
echo '<h2>getParsedHeaders()</h2> <pre>';
print_r($pop3->getParsedHeaders(1));
echo '</pre>';

/*
* Get body of message 1
*/
echo '<h2>getBody()</h2>';
echo '<pre>' . htmlspecialchars($pop3->getBody(1)) . '</pre>';

/*
* Get number of messages in maildrop
*/
echo '<h2>getNumMsg</h2>';
echo '<pre>' . $pop3->numMsg() . '</pre>';

/*
* Get entire message
*/
echo '<h2>getMsg()</h2>';
echo '<pre>' . htmlspecialchars($pop3->getMsg(1)) . '</pre>';

/*
* Get listing details of the maildrop
*/
echo '<h2>getListing()</h2>';
echo '<pre>';
print_r($pop3->getListing());
echo '</pre>';

/*
* Get size of maildrop
*/
echo '<h2>getSize()</h2>';
echo '<pre>' . $pop3->getSize() . '</pre>';

/*
* Disconnect
*/
$pop3->disconnect();
?>