<?php
class View {
    private static $_current = array();
    public static function current() {
        return end(self::$_current);
    }
    public static function addCurrent($view) {
        array_push(self::$_current,$view);
    }
    public static function removeCurrent() {
        array_pop(self::$_current);
    }
    private $template,$taglibs;
	public $data = array();
    
    public function  __construct($template) {
        $this->template = $template;
        $this->parent = $parent;
        $this->taglibs = Pimple::instance()->getTagLibs();
    }
    public function getCacheName() {
        $dirname = dirname(substr($this->template,strlen(BASEDIR)));
        return Dir::concat(CACHEDIR,$dirname).basename($this->template);
    }
    private function parseTemplate() {
        $cachename = $this->getCacheName();
		umask(0002);
        if (!is_file($cachename)) {

			ob_start();
            $this->_include($this->template);
			$phtml = ob_get_clean();
            $parser = new Phtml();
            $parsed = $parser->read($phtml);
            
            Dir::ensure(dirname($cachename));
            //die($parsed->toPhp());
            $parsed->toPhp($cachename);
        }
    }
    public function render($data) {
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
		unlink($cachename);
        $result = ob_get_clean();
        self::removeCurrent();
		return stripslashes($result);
    }
    protected function _include($file) {
        $data = $this->data;
        if (is_array($this->data)) {
            extract($this->data);
        }
        //print_r($this->data);
        extract($this->taglibs);
        
        include $file;
    }
    protected function _eval($expr) {
        $data = $this->data;
        if (is_array($this->data))
            return $this->data;
        extract($this->taglibs);
        
        return eval("return ".stripslashes($expr));
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
    private function attr($string) {
        $attrs = new stdClass();
        ob_start();
        eval('?>'.$string);
        $string = stripslashes(ob_get_clean());
        
		preg_match_all('/(\w+)=("|\')([^\2]*?)\2/is',$string,$matches);
        
        
		foreach($matches[1] as $i=>$name) {
			$value = $matches[3][$i];
			$attrs->$name = $value;
		}
        //var_dump($attrs);
        return $attrs;
    }
}