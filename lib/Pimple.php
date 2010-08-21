<?php
class Pimple {
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


    private $controller,$action,$parms = array();
    private $body = '';
    private $tagLibs = array();
    public function init() {
        $this->getPath();
        list($this->controller,$this->action) = explode('/',trim($this->getPath(),'/'));
        $this->parms = $_GET;
        $this->execute();
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
        if (!$this->controller)
            $this->controller = 'index';
        if (!$this->action)
            $this->action = 'index';
        if (!String::isAlphaNum($this->controller)) {
            throw new Exception(T('Invalid controller: %s',$this->controller));
        }
        if (!String::isAlphaNum($this->action)) {
            throw new Exception(T('Invalid action: %s',$this->action));
        }

        
        $ctrlClass = ucfirst($this->controller).'Controller';
        $appViewFile = Dir::normalize(BASEDIR).'view/application.php';
        $viewFile = Dir::normalize(BASEDIR).'view/'.$this->controller.'/'.$this->action.'.php';
        if (!class_exists($ctrlClass)) {
            $ctrlFile = Dir::normalize(BASEDIR).'controller/'.$ctrlClass.'.php';
            if (!File::exists($ctrlFile))
                throw new Exception(T('Controller not found: %s',$ctrlFile));
            require_once $ctrlFile;
        }
        
        if (!class_exists($ctrlClass)) {
            throw new Exception(T('Controller not found: %s',$ctrlClass));
        }

        $ctrl = new $ctrlClass();

        if (!method_exists($ctrl,$this->action)) {
            throw new Exception(T('Action not found: %s::%s',$ctrlClass,$this->action));
        }
        
        $data = $ctrl->$this->action();

        
        if (is_file($viewFile)) {
            $view = new View($viewFile,$data);
            $this->body = $view->render();
        } else {
            throw new Exception(T('View not found: %s',$viewFile));
        }
        $this->view = new View($appViewFile,array('body'=>$this-body));

    }
    public function render() {
        return $this->view->render();
    }
    public static function getPath() {
        $uri = $_SERVER['REQUEST_URI'];
        $baseOffset = strlen(BASEURL)-1;
        $path = current(explode('?',substr($uri,$baseOffset),2));
        if (!$path)
            $path = '/';
        return $path;
    }
    public function getBody() {
        return $this->body;
    }
    
    public function registerTagLib($namespace,$instance) {
        $this->tagLibs[$namespace] = $instance;
    }
    public function getTagLibs() {
        return $this->tagLibs;
    }
}
Pimple::instance()->registerTagLib('p',new CoreTagLib());