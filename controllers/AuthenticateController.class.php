<?php
require_once("User.class.php");

class AuthenticateController extends PageController{
    public function &run() {
	$subAction = $this->request->getSubAction();
	$params = $this->request->getNamedParamsArray();
	if(!$subAction) $subAction = 'login';
	switch($subAction) {
	    case "login":
		$view = new View();
		return $view->getHtml('authenticate/login.tpl', 'main_login.tpl');
		break;
	    case "authenticate":
		$user = new User();
		$response['success'] = $user->authenticateUser($params);
		return json_encode($response);
		break;
	}
    }
}
