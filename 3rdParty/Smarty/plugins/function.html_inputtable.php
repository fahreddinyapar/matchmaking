<?php

function smarty_function_html_inputtable($params, &$smarty)
{
	extract($params);

    if (!isset($table)) {
        $smarty->trigger_error("html_inputtable: missing 'table' parameter");
        return;
	}
	while(list($key,$value) = each($form)) {
	    $ex = explode("_",$key);
	    if($ex[0]=="rows" && $ex[1]==$table) {
		$rows[] = $key;
	    }
	}

	$output = "<table cellpadding=0 cellspacing=0 width=100%>\n";
	if(Misc::getForm('printer')!="true") {
	    if(Misc::getForm('tbl')==$table) $crows = Misc::getForm('rows')+1;
	    else $crows = 1;
	    $url = Misc::addParameterToUri(Misc::getCurrentUri(array('rows','tbl')),"rows=".$crows."&tbl=$table");
//	    $url = Misc::addParameterToUri($url,"tid=".$table);
	    $output .= "<tr><td colspan=".count($tables[$table])." align=right><a onClick='return confirm(\"Formu kayit etmeden devam ederseniz, girdiginiz bilgiler silinecektir.\")' href='".$url."'>Satir Ekle</a></td></tr>";
	}

	if(!empty($tables[$table]['title'])) $output .= "<tr><td class=tablo colspan='".(count($tables[$table])-1)."'>".$tables[$table]['title']."</td></tr>";
	$output .= "<tr>\n";
	$toplam = count($tables[$table])-1;
	for($i=0;$i<$toplam;$i++) {
	    $cols = $tables[$table][$i]['colspan'];
	    if($cols>1) {
		$output .="
		<td class=tablo colspan=".$cols.">
		<table border=0 width=100%><tr>
		    <td colspan=$cols class=tablo2 align=center>
		    <strong>" . $tables[$table][$i]['title']. "</strong>
		    </td>
		    </tr>
		    <tr>
		    ";
		for($k=0;$k<$cols;$k++) {
		$output .="
		    <td class=tablo2 align=center><strong>".$tables[$table][$i+$k]['spec']."</strong></td>
		    ";
		}
		$output .="
		    </tr>
		</table>
		</td>";
		$i=$i+($cols-1);
	    } else {
		$output .="<td class=tablo align=center><strong>" . $tables[$table][$i]['spec']. "</strong></td>";
	    }
	}
	$output .= "</tr>";
//	if($table == Misc::getForm('tid')) {
	for($i=0;$i<count($rows);$i++) {
	    if(!empty($form[$rows[$i]]['error'])) $output .= "<tr><td colspan=".count($tables[$table])." class=tablo align=middle><font color=red>".$form[$rows[$i]]['error']."</font></td></tr>";
	    $output .="<tr><td valign=top align=middle class=tablo>". $form[$rows[$i]]['html']. "</td></tr>";
	}
/*
	} else {
	for($i=0;$i<count($rows)-Misc::getForm('rows');$i++) {
	    if(!empty($form[$rows[$i]]['error'])) $output .= "<tr><td colspan=".count($tables[$table])." class=tablo align=middle><font color=red>".$form[$rows[$i]]['error']."</font></td></tr>";
	    $output .="<tr><td valign=top align=middle class=tablo>". $form[$rows[$i]]['html']. "</td></tr>";
	}	
	}		
*/
	$output .= "</table>\n";
	
	return $output;
}


?>
