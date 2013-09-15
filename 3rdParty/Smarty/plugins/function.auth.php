<?php
//
// $Id: function.auth.php,v 1.1.1.1 2003/07/14 12:55:46 ozan Exp $
//

function smarty_function_auth($params, &$smarty)
{
    extract($params);
    switch($function) {
	case "loginbox":
	    if(!$GLOBALS['bauth']->getAuth()) return $GLOBALS['bauth']->drawLoginBox();
	    else $GLOBALS['bauth']->redirectHome();
	    break;
	default:
	    return;
	    break;
    }
}

