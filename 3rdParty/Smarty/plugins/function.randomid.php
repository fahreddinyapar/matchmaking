<?php
//
// $Id: function.blocks.php,v 1.1.1.1 2003/07/14 12:55:49 ozan Exp $
//

function smarty_function_randomid($params, &$smarty)
{
    extract($params);
    return "r".substr(uniqid(rand()),1,21);
}

