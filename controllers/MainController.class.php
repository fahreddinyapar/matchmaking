<?php
require_once("Main.class.php");

class MainController extends PageController
{
    public function &run()
    {
        $subAction = $this->request->getSubAction();
        $action = $this->request->getAction();
        $params = $this->request->getNamedParamsArray();
        if (!$subAction) $subAction = 'list';
        switch ($subAction) {
            case "list":
                $view = new View();
                //$main = new Main();
                //$view->assign('users', $main->getCategories());
                return $view->getHtml('main/list.tpl', 'main.tpl');
                break;
        }
    }
}
