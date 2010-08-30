<?php
class View {
    private $template,$taglibs;
	public $data;

    public function  __construct($template) {
        $this->template = $template;
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

            $phtml = addslashes($phtml);

            $phtml = preg_replace('/\$eval\{(.+)\}/','<?=$this->_eval(\'$1;\');?>',$phtml);

            //Matches single elements
            $phtml = preg_replace('/<([\w]+):([\w]+) ?([^>]*)\/>/is','<'.'?=$$1->$2($this->attr(\'$3\'),null,$this).chr(10);?'.'>',$phtml);

            while(true) {
                $newPhtml = preg_replace('/<(([\w]+):([\w]+)) ?([^>]*)>(.*?)<\/\\1>/is','<?ob_start();?>$5<?=$$2->$3($this->attr(\'$4\'),ob_get_clean(),$this).chr(10);?>',$phtml);
                if ($newPhtml != $phtml)
                    $phtml = $newPhtml;
                else
                    break;
            }
            $phtml = preg_replace('/\{(.+)\}/','<?=$this->_var("$1");?>',$phtml);

            Dir::ensure(dirname($cachename));

			file_put_contents($cachename,$phtml);

        }
    }
    public function render($data) {
        $cachename = $this->getCacheName();
        $this->data = $data;
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
        
		return stripslashes($result);
    }
    private function _include($file) {
        $data = $this->data;
        if (is_array($this->data))
            extract($this->data);
        extract($this->taglibs);
        
        require $file;
    }
    private function _eval($expr) {
        $data = $this->data;
        if (is_array($this->data))
            return $this->data;
        extract($this->taglibs);
        
        return eval("return ".stripslashes($expr));
    }

    public function _var($varname) {
        
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