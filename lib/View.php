<?php
if (!defined('VIEWDIR')) {
    define('VIEWDIR',Dir::concat(BASEDIR,'view'));
}
class View {
    const CACHE = 'VIEW_CACHE';
    private static $_current = array();

    private $jsFiles = array();
    private $cssFiles = array();



    public static function current() {
        return end(self::$_current);
    }
    public static function top() {
        return self::$_current[0];
    }
    public static function addCurrent($view) {
        array_push(self::$_current,$view);
    }
    public static function removeCurrent() {
        array_pop(self::$_current);
    }
    private $template;
	public $taglibs= array();
    public $data = array();
    
    public function  __construct($template) {
        $this->template = ltrim($template,'/');
        if (!is_file($this->getTemplateFile())) {
            throw new Exception(T('View not found: %s',$this->template));
        }
        // $this->parent = $parent; // Parent is never set?
        $this->taglibs = Pimple::instance()->getTagLibs();
    }
    public function getTemplate() {
        return $this->template;
    }
    protected function getTemplateFile() {
        return VIEWDIR.$this->template.'.php';
    }
    public function getCacheName() {
        return Pimple::getCacheFile($this->getTemplateFile());
    }
    private function parseTemplate() {
        $cachename = $this->getCacheName();
		umask(0002);
        $cache = Settings::get(View::CACHE,true);
        if (!$cache || !is_file($cachename)) {

            $phtml = file_get_contents($this->getTemplateFile());
            $parser = new Phtml();
            $parsed = $parser->read($phtml);

            Dir::ensure(dirname($cachename));
            //die($parsed->toPhp());
            //die(get_class($parsed));
            $parsed->toPhp($cachename);
        }
    }
    public function render($data = array()) {
        self::addCurrent($this);
        $cachename = $this->getCacheName();
        if ($data instanceof Model)
            $data = $data->toArray();
        
        $this->data = DataUtil::merge($this->data,$data);
        $this->parseTemplate();
		ob_start();
		try {

            $this->_include($cachename);
		} catch(Exception $e) {
			//TODO: Handle errors
			echo $e;
		}
		//unlink($cachename);
        $result = ob_get_clean();
        self::removeCurrent();
		return $result;
    }
    protected function _include($file) {
        $data = $this->data;
        
        if (is_array($this->data)) {
            extract($this->data);
        }
        extract($this->taglibs);
        $view = $this;
        include $file;
    }
    protected function _var($varname) {
        if (is_array($this->data))
            return $this->data[$varname];
        else
            return $this->data->$varname;
    }
    private function lib($ns) {
        if (!$this->taglibs[$ns])
            throw new Exception(T('Unknown tag lib: %s',$ns));
        return $this->taglibs[$ns];
    }
    public function addJsFile($file) {
        if ($this != self::top())
            self::top()->addJsFile($file);
        else
            $this->jsFiles[] = $file;
    }
    public function addCssFile($file) {
        if ($this != self::top())
            self::top()->addCssFile($file);
        else
            $this->cssFiles[] = $file;
    }
    public function getJsFiles() {
        return $this->jsFiles;
    }

    public function getCssFiles() {
        return $this->cssFiles;
    }
}