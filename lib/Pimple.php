<?php
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


    private $controller,$action,$parms = array();
    private $controllerInstance = null;
    private $body = '';
    private $siteName;
    private $tagLibs = array();
    public function init() {
        $this->getPath();
        list($this->controller,$this->action) = explode('/',trim($this->getPath(),'/'));
        $this->parms = $_GET;
		if (!Settings::get(self::URL,false))
			Settings::set(self::URL,'http://pimple.kweative.dk/');
    }
    public function hasParm($name) {
        return array_key_exists($name,$this->parms);
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
    public function execute() {
        if (isset($_GET['__clearcache'])) {
            //Clear cache
            Dir::emptyDir(CACHEDIR,true);
        }
        
        if (!$this->controller)
            $this->controller = 'index';
        if (!$this->action)
            $this->action = 'index';
        try {
            if (!String::isAlphaNum($this->controller)) {
                header("HTTP/1.0 404 Invalid url");
                throw new Exception(T('Invalid controller: %s',$this->controller));
            }
            if (!String::isAlphaNum($this->action)) {
                header("HTTP/1.1 404 Invalid url");
                throw new Exception(T('Invalid action: %s',$this->action));
            }

            

            $ctrlClass = ucfirst($this->controller).'Controller';
            $appViewFile = 'application';
            $viewFile = '/'.$this->controller.'/'.$this->action;
            if (!class_exists($ctrlClass)) {
                $ctrlFile = Dir::normalize(BASEDIR).'controller/'.$ctrlClass.'.php';
                if (!File::exists($ctrlFile)) {
                    header("HTTP/1.1 404 Controller not found");
                    throw new Exception(T('Controller not found: %s',$ctrlFile));
                }
                require_once $ctrlFile;
            }

            if (!class_exists($ctrlClass)) {
                header("HTTP/1.1 404 Controller not Found");
                throw new Exception(T('Controller not found: %s',$ctrlClass));
            }

            $ctrl = new $ctrlClass();
            $this->controllerInstance = $ctrl;
            if (!method_exists($ctrl,$this->action)) {
                header("HTTP/1.1 404 Action not Found");
                throw new Exception(T('Action not found: %s::%s',$ctrlClass,$this->action));
            }
            $action = $this->action;

            if (!$ctrl->getSkipView()) {
				try {
					$view = new View($viewFile);
				} catch(Exception $e) {
					//Ignore for now
				}
			}
            try {
                $data = $ctrl->$action();
                
            } catch(ValidationException $e) {
                //Do nothing...
            } catch(Interrupt $e) {
                //Do nothing...
            } catch(ErrorException $e) {
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
                        throw new Exception(T('View not found: %s',$viewFile));
                    }
                }
            }

        } catch(Exception $e ) {
            header("HTTP/1.1 500 Internal error");
            if (Request::isAjax()) {
                $this->body = json_encode(array('msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()));
            } else {
                if (Settings::get(Settings::DEBUG,false)) {
                    $body = $e->__toString();
                    if (!stristr($body,'<')) {
                        $body = '<pre>'.$body.'</pre>';
                    }
                    $this->body = $body;
                } else {
                    Url::redirect('error','internal');
                }
            }
        }
        $this->view = new View($appViewFile);

    }
    public function render() {
        if (Request::isAjax()
                || ($this->controllerInstance && $this->controllerInstance->getSkipView())
                || ($this->controllerInstance && $this->controllerInstance->getSkipLayout())) {
            echo $this->body;
        } else {
            echo $this->view->render(array('body'=>$this->body));

        }

        
    }
    public static function getPath() {
        $uri = $_SERVER['REQUEST_URI'];
        $baseOffset = strlen(BASEURL)-1;
        $path = current(explode('?',substr($uri,$baseOffset),2));
        if (!$path)
            $path = '/';
        return $path;
    }
    public function getControllerInstance() {
        return $this->controllerInstance;
    }

    public function getBody() {
        return $this->body;
    }
    
    public function registerTagLib($namespace,$instance) {
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
    public function getBaseDir() {
        
        return Dir::normalize(realpath(dirname(__FILE__).'/../'));
    }
    public function getRessource($path) {
        return $this->getBaseDir().'ressource/'.$path;

    }
    public function loadZendClass($class) {
        if (class_exists($class)) return;
        $path = str_replace('_','/',$class).'.php';
        require_once $this->getRessource('lib/'.$path);

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
}