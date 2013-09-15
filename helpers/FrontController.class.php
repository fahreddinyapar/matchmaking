<?php
class FrontController
{
    private $_routes;
    private $_controllersPath;
    private $_notFoundControllerClass;

    public function FrontController(&$pRoutes, $pControllersPath, $pNotFoundControllerClass = 'MainController')
    {
        $this->_routes = $pRoutes;
        $this->_controllersPath = $pControllersPath;
        $this->_notFoundControllerClass = $pNotFoundControllerClass;
    }

    public function setRoutes(& $pRoutes)
    {
        $this->_routes = $pRoutes;
    }

    public function setControllersPath($pControllersPath)
    {
        $this->_controllersPath = $pControllersPath;
    }

    /**
     * Trys to detect the PageController.If can not detect then uses NotFoundController
     *
     * @param Request $pRequest
     * @return PageController
     */
    public function &getPageController(&$pRequest)
    {
        $action = $pRequest->getAction();
        if ($action == '') $action = 'index';
        $controllerClass = $this->_routes[$action];

        if (empty($controllerClass)) $controllerClass = $this->_notFoundControllerClass;
        require_once APP_ROOT . $this->_controllersPath . $controllerClass . '.class.php';
        return new $controllerClass($pRequest);
    }

    /**
     * Runs PageController and streams returned content.
     * This is a good place to inject some debug info at the and of all pages
     *
     * @param Request $pRequest
     */
    public function runPageController(&$pRequest)
    {
        $pageController = $this->getPageController($pRequest);
        $html = $pageController->run();
        echo $html;
    }
}
