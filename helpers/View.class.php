<?php
require_once APP_ROOT . "3rdParty/Smarty/Smarty.class.php";

class View extends Smarty
{
    function View($plugin_name = '', $plugin_type = '')
    {
        parent::__construct();
        $this->template_dir = APP_ROOT . 'templates/';
        $this->compile_dir = APP_ROOT . 'cache/';
        $this->config_dir = APP_ROOT . 'config/';
        $this->cache_dir = APP_ROOT . 'cache/';
        $this->cache_lifetime = $GLOBALS['config']['smartyCacheLifetime'] ? $GLOBALS['config']['smartyCacheLifetime'] : 3600;
        $this->left_delimiter = "<smarty:";
        $this->right_delimiter = ">";
        $this->assign('APP_ROOT', APP_ROOT);

        $requestUriForJs = str_replace("\"", "&quot;", str_replace("'", "&#39;", $_SERVER['REQUEST_URI']));
        $this->assign('requestUri', $requestUriForJs);
        $this->assign('REQUEST_URI', $_SERVER['REQUEST_URI']);
        $this->assign('session', $_SESSION);
        $this->assign('GET', $_GET);
        if ($GLOBALS['request']) {
            $this->assign('paramArray', $GLOBALS['request']->getParamArray());
            $namedParamsArray = $this->replaceParams($GLOBALS['request']->getNamedParamsArray());
            $this->assign('namedParamsArray', $namedParamsArray);
        }
    }

    public function replaceParams($data)
    {
        foreach ($data as $key => $val) {
            $data[$key] = str_replace("\"", "&quot;", str_replace("'", "&#39;", $val));
        }
        return $data;
    }

    public function getHtml($pChildTemplate, $pParentTemplate = null)
    {
        if ($pParentTemplate != null) {
            $parentSmarty = new View();
            $parentSmarty->assign('session', $_SESSION);
            $parentSmarty->assign('content', $this->fetch($pChildTemplate));
            return $parentSmarty->fetch($pParentTemplate);
        }
        return $this->fetch($pChildTemplate);
    }

    public function assignVars($params)
    {
        foreach ($params as $k => $v) {
            $this->assign($k, $v);
        }
    }

    public function setWhere($params, $sperator = ' > ')
    {
        foreach ($params as $k => $field) {
            if ($field[0] == '#') {
                if ($GLOBALS['request']) {
                    $val = $GLOBALS['request']->getParamByName(substr($field, 1));
                    if ($val) {
                        $vals[] = $val;
                    }
                }
            } else {
                $vals[] = $field;
            }
        }
        $this->assign('where', implode($sperator, $vals));
    }
}