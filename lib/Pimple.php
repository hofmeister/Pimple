<?php

/**
 * Main class in pimple - provides methods for handling requests and other very general stuff
 */
class Pimple {
    const URL = 'PIMPLE_URL';

    private static $instance;

    /**
     *
     * @return Pimple
     */
    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function save() {
        try {
            MessageHandler::instance()->save();
            SessionHandler::instance()->save();
        } catch (Exception $e) {
            //Do nothing...
        }
    }

    public static function end($msg = null) {
        self::save();
        if ($msg) {
            echo $msg;
        }
        exit();
    }

    private $controller, $action, $parms = array();
    private $controllerInstance = null;
    private $body = '';
    private $siteName;
    private $tagLibs = array();
    private $router;

    public function __construct() {
        $this->router = new UrlRouter();
    }

    public function init() {
        $path = $this->getPath();
        list($this->controller, $this->action) = $this->router->getMethod($path);
        $this->parms = $_GET;
    }

    /**
     * Get current UrlRouter on this pimple instance
     * @return UrlRouter
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * Sets the url router of this pimple instance. Is used for setting alternative urls for controller/method paths
     * @param UrlRouter $router
     */
    public function setRouter($router) {
        $this->router = $router;
    }

    public function hasParm($name) {
        return array_key_exists($name, $this->parms);
    }

    public function getParm($name) {
        if ($this->hasParm($name))
            return $this->parms[$name];
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }

    public function getViewFile() {
        return $this->controller . '/' . $this->action;
    }

    public function execute() {
        if (Settings::get(Settings::DEBUG, false)) {
            if (isset($_GET['__clearcache'])) {
                //Clear cache
                Dir::emptyDir(CACHEDIR, true);
            }
            if (isset($_GET['__clearview'])) {
                //Clear cache
                Dir::emptyDir(Dir::concat(CACHEDIR, 'view'), true);
            }
            if (isset($_GET['__clearjs'])) {
                //Clear cache
                Dir::emptyDir(Dir::concat(CACHEDIR, 'js'), true);
            }
            if (isset($_GET['__clearcss'])) {
                //Clear cache
                Dir::emptyDir(Dir::concat(CACHEDIR, 'css'), true);
            }
        }

        try {
            if (!String::isAlphaNum($this->controller)) {
                header("HTTP/1.0 404 Invalid url");
                throw new HttpNotFoundException(T('Invalid controller: %s', $this->controller));
            }
            if (!String::isAlphaNum($this->action)) {
                header("HTTP/1.1 404 Invalid url");
                throw new HttpNotFoundException(T('Invalid action: %s', $this->action));
            }



            $ctrlClass = ucfirst($this->controller) . 'Controller';
            $appViewFile = 'application';
            $viewFile = $this->getViewFile();
            if (!class_exists($ctrlClass)) {
                $ctrlFile = Dir::normalize(BASEDIR) . 'controller/' . $ctrlClass . '.php';
                if (!File::exists($ctrlFile)) {
                    header("HTTP/1.1 404 Controller not found");
                    throw new HttpNotFoundException(T('Controller not found: %s', $ctrlFile));
                }
                require_once $ctrlFile;
            }

            if (!class_exists($ctrlClass)) {
                header("HTTP/1.1 404 Controller not Found");
                throw new HttpNotFoundException(T('Controller not found: %s', $ctrlClass));
            }

            $ctrl = new $ctrlClass();
            $this->controllerInstance = $ctrl;
            if (!method_exists($ctrl, $this->action)) {
                header("HTTP/1.1 404 Action not Found");
                throw new HttpNotFoundException(T('Action not found: %s::%s', $ctrlClass, $this->action));
            }
            $action = $this->action;

            if (!$ctrl->getSkipView()) {
                try {
                    $view = new View($viewFile);
                } catch (Exception $e) {
                    //Ignore for now
                }
            }
            try {
                $data = $ctrl->$action();
            } catch (ValidationException $e) {
                //Do nothing...
            } catch (Interrupt $e) {
                //Do nothing...
            } catch (ErrorException $e) {
                MessageHandler::instance()->addError($e->getMessage());
            }


            if (!$data)
                $data = $ctrl->getData();

            if (!$ctrl->getSkipView()) {
                if ($view) {
                    $this->body = $view->render($data);
                } else {
                    if (!Request::isAjax()) {
                        header("HTTP/1.1 500 View not Found");
                        throw new Exception(T('View not found: %s', $viewFile));
                    }
                }
            }
        } catch (HttpNotFoundException $e) {
            trigger_error(sprintf("Path not found %s",self::getPath()),E_USER_ERROR);
            if (!Request::isAjax())
                Url::redirect('error', 'notfound');
            Pimple::end();
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal error");
            if (Request::isAjax()) {
                $this->body = json_encode(array('msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()));
            } else {
                if (Settings::get(Settings::DEBUG, false)) {
                    $body = $e->__toString();
                    if (!stristr($body, '<')) {
                        $body = '<pre>' . $body . '</pre>';
                    }
                    $this->body = $body;
                } else {
                    trigger_error(sprintf("Unexpected exception thrown in %s:\n\t%s",self::getPath(),$e->__toString()),E_USER_ERROR);
                    Url::redirect('error', 'internal');
                }
            }
        }
        $this->view = new View($appViewFile);
    }

    public function render() {
        if (Settings::get(Settings::DEBUG, false) && isset($_GET['__viewdebug'])) {
            $this->view->render(array('body' => $this->body));
            return;
        }

        if (Request::isAjax()
                || ($this->controllerInstance && $this->controllerInstance->getSkipView())
                || ($this->controllerInstance && $this->controllerInstance->getSkipLayout())) {
            echo $this->body;
        } else {
            echo $this->view->render(array('body' => $this->body));
        }
    }

    /**
     * Get current path of url (without baseurl)
     * @return string
     */
    public static function getPath() {
        $uri = $_SERVER['REQUEST_URI'];
        $baseOffset = strlen(BASEURL) - 1;
        $path = current(explode('?', substr($uri, $baseOffset), 2));
        if (!$path)
            $path = '/';
        return $path;
    }

    /**
     *
     * @return Controller
     */
    public function getControllerInstance() {
        return $this->controllerInstance;
    }

    public function getBody() {
        return $this->body;
    }

    public function registerTagLib($namespace, $instance) {
        $this->tagLibs[$namespace] = $instance;
    }

    public function getTagLib($id) {
        return $this->tagLibs[$id];
    }

    public function getTagLibs() {
        return $this->tagLibs;
    }

    public function getSiteName() {
        return $this->siteName;
    }

    public function setSiteName($siteName) {
        $this->siteName = $siteName;
    }

    /**
     * Get pimple base dir
     * @return string
     */
    public function getBaseDir() {

        return Dir::normalize(realpath(dirname(__FILE__) . '/../'));
    }

    public function getRessource($path) {
        return $this->getBaseDir() . 'ressource/' . $path;
    }

    public function loadZendClass($class) {
        if (class_exists($class))
            return;
        $path = str_replace('_', '/', $class) . '.php';
        require_once $this->getRessource('lib/' . $path);
    }

    /**
     * Convenience method for executing all normally needed methods
     */
    public function run() {
        $this->init();
        $this->execute();
        $this->render();
        $this->save();
    }

    /**
     * Get current environment type
     */
    public static function getEnvironment($default = null) {
        return ($_SERVER['PIMPLE_ENV']) ? $_SERVER['PIMPLE_ENV'] : $default;
    }

    public static function getCacheFile($filename) {
        $dirname = dirname(substr($filename, strlen(BASEDIR)));
        return Dir::concat(CACHEDIR, $dirname) . basename($filename);
    }

    public static function getSiteDir() {
        return Dir::normalize(BASEDIR);
    }

}

class UrlRouter {

    private $methodToUrl = array();
    private $urlToMethod = array();
    private $methods = array();
    private $urlToAlias = array();

    private function getKey($controller, $method) {
        if (!$controller)
            $controller = 'index';
        if (!$method)
            $method = 'index';
        return strtolower(trim("$controller|$method"));
    }

    private function normalize($url) {
        return trim($url, '/ ');
    }

    public function setUrl($url, $controller, $method = null) {
        if (!$method)
            $method = 'index';

        $url = $this->normalize($url);
        if ($this->hasUrl($controller, $method))
            throw new Exception("URL already exists for $controller::$method() :" . $this->getUrl($controller, $method));
        $key = $this->getKey($controller, $method);
        $this->methods[$key] = array($controller, $method);
        $this->methodToUrl[$key] = $url;
        $this->urlToMethod[$url] = $key;
    }

    public function getUrl($controller, $action) {
        $key = $this->getKey($controller, $action);
        $url = array_key_exists($key,$this->urlToMethod) ? $this->methodToUrl[$key] : null;
        if (!$url) {
            $url = '';
            if ($controller) {
                $url .= String::UrlEncode($controller) .'/';
                if ($action) {
                    $url .= String::UrlEncode($action).'/';
                }
            }
        }
        return $this->normalize($url);
    }

    public function hasUrl($controller, $method) {
        $key = $this->getKey($controller, $method);
        return isset($this->methodToUrl[$key]);
    }


    /**
     * Returns array($controller,$method)
     * @param string $url
     * @return array array($controller,$method)
     */
    public function getMethod($url) {
        $base = BASEURL;
        if ($base == '//')
            $base = '/';

        $url = $this->normalize($url);
        $key = array_key_exists($url,$this->urlToMethod) ? $this->urlToMethod[$url] : null;
        if ($key) {
            $out = $this->methods[$key];
        } else {
            $out = explode('/', $url);
            if (empty($out[0]))
                $out = array();
            $alias = $this->getUrl($out[0], $out[1]);
            if ($alias && $alias != $url) {
                Url::gotoUrl($base.$alias);
            }
        }
        switch (count($out)) {
            case 0:
                $out = array('index', 'index');
            case 1:
                $out = array($out[0], 'index');
        }

        //die("$url -> $out[0]::$out[1]()");

        return $out;
    }
}

class HttpNotFoundException extends Exception {

}