<?php
//
// $Id: function.db.php,v 1.1.1.1 2003/07/14 12:55:46 ozan Exp $
//

function smarty_function_db($params, &$smarty)
{
    extract($params);
    switch($function) {
	case "fetch":
	    return content::fetch($key);
	    break;
	default:
	    return;
	    break;
    }
}

