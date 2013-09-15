<?php
require_once("Settings.class.php");

class SettingsController extends PageController
{
    public function &run()
    {
        $subAction = $this->request->getSubAction();
        $params = $this->request->getNamedParamsArray();
        if (!$subAction) $subAction = 'list';
        switch ($subAction) {
            case "list":
                $view = new View();
                //$main = new Settings();
                //$view->assign('settings', $settings->getSettings());
                return $view->getHtml('admin/list.tpl', 'main.tpl');
                break;
        }
    }
}
