<?php
//
// $Id: MySmarty.class.php,v 1.1.1.1 2003/07/14 12:55:40 ozan Exp $
//
require_once APP_BASE . 'lib/Smarty/Smarty.class.php';

class MySmarty extends Smarty 
{
    function MySmarty($applib = false) {
	//$settings = Doctrine::getTable('Settings')->findOneById(1);
	//$data = $settings->toArray();
	//$this->assign('mydata',$data);
	
	if($applib) $this->template_dir    = APPLIB_TEMPLATES;
	else $this->template_dir    = APP_TEMPLATES;
	$this->compile_dir     = APP_BASE . APP_CACHE.'Smarty/';
	$this->config_dir      = APP_BASE . 'config/';
	$this->cache_dir       = APP_BASE . APP_CACHE.'Smarty/web/';
	$this->cache_lifetime  = $GLOBALS['config']['cache_lifetime'];
	$this->left_delimiter  = "<smarty:";
	$this->right_delimiter = ">";
	if($GLOBALS['onProduction']) {
	    $this->compile_check  = false;
	    $this->caching	  = 1;
	} else {
	    $this->compile_check = true;
	    $this->caching	 = 0;
	}
	$this->debuging = true;
	$this->register_resource('db',array('_smarty_get_template',
					    '_smarty_get_timestamp',
					    '_smarty_get_secure',
					    '_smarty_get_trusted'));

	$this->register_resource('string',array('_smarty_get_string_template',
					    '_smarty_get_string_timestamp',
					    '_smarty_get_string_secure',
					    '_smarty_get_string_trusted'));

	$this->assign('appbase',APP_BASE);
	$this->assign('appurl',APP_URL);
	$this->assign('liburl',$GLOBALS['config']['libPublicUrl']);
	$this->assign('fullurl',$GLOBALS['config']['fullurl']);
	$this->assign('imgurl',APP_URL."graphics/");
	$this->assign('config',$GLOBALS['config']);
	$this->assign('auth',$GLOBALS['bauth']->getAuth());
	$this->assign('root',$GLOBALS['bauth']->root);
	$this->assign('sess',$GLOBALS['session']->sid);
	$this->assign('def',$GLOBALS['session']->get('def'));
    }
}

function _smarty_get_template($tpl_name,&$tpl_source,&$smarty_obj) {
    $ex = explode("/",$tpl_name);
    $type = $ex[0];
    $name = $ex[1];
    $sub  = $ex[2];

    $scope = $GLOBALS['session']->get('scope') ? $GLOBALS['session']->get('scope') : 'main';

    $site = Modules::get('sites');
    $designinfo = $site->get_designs($scope);
    $db = DObject::get('templates');
    $db->siteuid   = $scope;
    $db->designuid = $designinfo['0']['uid'];
    $db->name      = $type."/".$name;
    $num = $db->find();
    $db->fetch();
    $packet = wddx_deserialize($db->template);
    if($sub) {
	$tpl_source = stripslashes($packet['templates'][$sub]);
    } else {
	$tpl_source = stripslashes($packet['templates']['current']);
    }
    return true;
}

function _smarty_get_timestamp($tpl_name,&$tpl_timestamp,&$smarty_obj) {
    $ex = explode("/",$tpl_name);
    $type = $ex[0];
    $name = $ex[1];
    $sub  = $ex[2];
    
    $scope = $GLOBALS['session']->get('scope') ? $GLOBALS['session']->get('scope') : 'main';
    $site = Modules::get('sites');
    $designinfo = $site->get_designs($scope);

    if($GLOBALS['config']['template2db']) {
	if(file_exists(APP_BASE . "/templates/temp/".$name.".tpl")) {
	    echo "Template GEN ON";
	    $fp = fopen(APP_BASE . "/templates/temp/".$name.".tpl","r");
	    $s="";
	    while(!feof($fp)) {
		$s.=fgets($fp,1024);
	    }
	    fclose($fp);
	    $db = DObject::get('templates');
	    $db->siteuid   = $scope;
	    $db->designuid = $designinfo['0']['uid'];
	    $db->name      = $type."/".$name;
	    $num = $db->find();
	    $templates['object_template'] = $s;
	    $id = wddx_packet_start();
	    wddx_add_vars($id,'templates');
	    $packet = wddx_packet_end($id);
	    if($db->fetch()) {
		$db->stamp    = date('YmdHis');
		$db->template = $packet;
		$db->update();
	    } else {
		$db = DObject::get('templates');
		$db->siteuid   = $scope;
		$db->designuid = $designinfo['0']['uid'];
		$db->name      = $type."/".$name;
		$db->stamp    = date('YmdHis');
		$db->template = $packet;
		$db->insert();
	    }
	    
	    //$time = Misc::mkDate(date('YmdHis'));
	    $tpl_timestamp = mktime($time['hour'],$time['minute'],$time['second'],$time['month'],$time['day'],$time['year']);
	    return true;
	}
    }

    $db = DObject::get('templates');
    $db->siteuid   = $scope;
    $db->designuid = $designinfo['0']['uid'];
    $db->name      = $type."/".$name;
    $num = $db->find();
    if($db->fetch()) {
	$time = Misc::mkDate($db->stamp);
	$time = Misc::mkDate(date('YmdHis'));
	$tpl_timestamp = mktime($time['hour'],$time['minute'],$time['second'],$time['month'],$time['day'],$time['year']);
	return true;
    } 
    return false;
}

function _smarty_get_secure($tpl_name,&$smarty_obj) {
    return true;
}

function _smarty_get_trusted($tpl_name,&$smarty_obj) {
}

function _smarty_get_string_template($tpl_name,&$tpl_source,&$smarty_obj) {
    $ex = explode("/",$tpl_name);
    $type = $ex[0];
    $name = $ex[1];
    $sub  = $ex[2];

    $tpl_source = $GLOBALS[$name];
    return true;
}

function _smarty_get_string_timestamp($tpl_name,&$tpl_timestamp,&$smarty_obj) {
    $ex = explode("/",$tpl_name);
    $type = $ex[0];
    $name = $ex[1];
    $sub  = $ex[2];
    
    $time = Misc::mkDate(date('YmdHis'));
    $tpl_timestamp = mktime($time['hour'],$time['minute'],$time['second'],$time['month'],$time['day'],$time['year']);
    return true;
}

function _smarty_get_string_secure($tpl_name,&$smarty_obj) {
    return true;
}

function _smarty_get_string_trusted($tpl_name,&$smarty_obj) {
}

function get_menuTemplateFromDB($file) {
    $scope = $GLOBALS['session']->get('scope') ? $GLOBALS['session']->get('scope') : 'main';
    $site = Modules::get('sites');
    $designinfo = $site->get_designs($scope);

    $basename = basename($file);
    $ex = explode('.',$basename);
    $name = $ex[0];
    $sub  = $ex[1];
    $db = DObject::get('menus');
    $db->siteuid   = $scope;
    $db->name      = $name;
    $num = $db->find();
    if($db->fetch()) {
	$packet = wddx_deserialize($db->template);
	if($sub!='sub') $s = $packet['template']['menu_template'];
	else {
	    $s = $packet['template']['submenu_template'];
	}
	$s = stripslashes($s);
	return $s;
    }
    return " ";
}