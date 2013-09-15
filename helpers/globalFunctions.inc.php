<?php

function _pre($pMixed) {
	echo "<pre>";
	print_r($pMixed);
}

function __($pMsgid) {
	$lang = apc_fetch('langlang');
	if (!$lang) {
	    $settings = Doctrine::getTable('Settings')->findOneById(1);
	    $lang = $settings->lang;
	    apc_store('langlang',$lang, 600);
	}
	$text = apc_fetch($pMsgid.$lang.$lang);
	if ($text){
	    return $text;
	}else{
	    return getMsg($pMsgid);
	}
}

function insertMsg($pMsgid,$lang) {
    if($lang == '' || $lang == 'en'){
	$lang = new Lang();
	$lang->lang = 'en';
	$lang->pkey = $pMsgid;
	$lang->pvalue = $pMsgid."*";
	$lang->save();
    }else{
	$lang = new Lang();
	$lang->lang = 'tr';
	$lang->pkey = $pMsgid;
	$lang->pvalue = $pMsgid."*";
	$lang->save();
    }
}

function getMsg($pMsgid) {
    $lang = apc_fetch('langlang');
	if (!$lang){
	    $settings = Doctrine::getTable('Settings')->findOneById(1);
	    $lang = $settings->lang;
	    apc_store('langlang',$lang, 600);
	}
	$ln = Doctrine_Query::create()
	    ->from('Lang')
	    ->where('pkey=? AND lang=?',array($pMsgid,$lang));
	$ds = $ln->Execute();
	$arr = $ds->toArray();

	$msg = $arr[0]['pvalue'];
	if(count($arr) != 0){
	    apc_store($pMsgid.$lang.$lang, $msg, 600);
	    return $msg;
	}else {
	    insertMsg($pMsgid,$lang);
	    apc_store($pMsgid.$lang.$lang, $pMsgid.'*', 600);
	    return $pMsgid.'*';
	}
}
