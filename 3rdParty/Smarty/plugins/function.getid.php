<?php
//
// $Id: function.blocks.php,v 1.1.1.1 2003/07/14 12:55:49 ozan Exp $
//

function smarty_function_getid($params, &$smarty)
{
    extract($params);
    global $smartyids;
    return "r".$smartyids++;
}

