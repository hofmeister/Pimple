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
        $serializedName = $cachename.'.serialized';
		umask(0002);
        $cache = Settings::get(View::CACHE,true);
        $parsed = null;
        if (!$cache || !is_file($cachename) || !is_file($serializedName)) {

            $phtml = file_get_contents($this->getTemplateFile());
            $parser = new Phtml();
            $parsed = $parser->read($phtml,$this->getTemplate());

            Dir::ensure(dirname($cachename));
            file_put_contents($serializedName,serialize($parsed));
            $parsed->toPhp($cachename);
        }
        return $parsed;
    }
    /**
     * @return PhtmlNode
     */
    public function getNodeTree() {
        $cachename = $this->getCacheName();
        $serializedName = $cachename.'.serialized';
        $tree = $this->parseTemplate();
        if (!$tree)
            $tree = unserialize (file_get_contents ($serializedName));
        return $tree->resolve();
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
    public function addJsFile($file,$lvl = 0) {
        
        if ($this != self::top())
            self::top()->addJsFile($file,count(self::$_current));
        elseif(!is_array($this->jsFiles[$lvl]) || !in_array($file,$this->jsFiles[$lvl])) {
            if (!$this->jsFiles[$lvl])
                $this->jsFiles[$lvl] = array();
            $this->jsFiles[$lvl][] = $file;
        }
    }
    public function addCssFile($file,$lvl = 0) {
        if ($this != self::top())
            self::top()->addCssFile($file,count(self::$_current));
        elseif(!is_array($this->cssFiles[$lvl]) || !in_array($file,$this->cssFiles[$lvl])) {
            if (!$this->cssFiles[$lvl])
                $this->cssFiles[$lvl] = array();
            $this->cssFiles[$lvl][] = $file;
        }
    }
    public function getJsFiles() {
        krsort($this->jsFiles);
        $files = array();
        foreach ($this->jsFiles as $jsfiles) {
            ArrayUtil::append($files,$jsfiles);
        }
        return array_unique($files);
    }

    public function getCssFiles() {
        krsort($this->cssFiles);
        $files = array();
        foreach ($this->cssFiles as $jsfiles) {
            ArrayUtil::append($files,$jsfiles);
        }
        return array_unique($files);
    }
    public function getInternalJsFiles() {
        $nodes = $this->getNodeTree()->getElementsByTagNameNS('js','include');
        $files = array();
		
		foreach($nodes as $node) {
			
            if ($node->getAttribute('local') == 'false')
                $base = Pimple::instance()->getBaseDir().'www/';
            else
                $base = Pimple::instance()->getSiteDir();
            $path = $node->getAttribute('path');
            if (String::StartsWith($path,"http://") || String::StartsWith($path,"https://"))
                $base = '';

            $files[] = $base.$path;
        }
        return $files;
    }
    public function getInternalCssFiles() {
        $nodes = $this->getNodeTree()->getElementsByTagNameNS('p','stylesheet');
        $files = array();
        foreach($nodes as $node) {
            if ($node->getAttribute('local') == 'false')
                $base = Pimple::instance()->getBaseDir().'www/';
            else
                $base = Pimple::instance()->getSiteDir();
            $path = $node->getAttribute('path');
            if (String::StartsWith($path,"http://") || String::StartsWith($path,"https://"))
                $base = '';
            $files[] = $base.$path;
        }
        return $files;
    }
}