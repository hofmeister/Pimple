<?php
class View {
    private $template,$taglibs;
	public $data;

    public function  __construct($template,$data) {
        $this->template = $template;
        $this->data = $data;
        $this->taglibs = Pimple::instance()->getTagLibs();
    }
    public function render() {
        $dirname = dirname(substr($this->template,strlen(BASEDIR)));
        $cachename = Dir::normalize(CACHEDIR).Dir::normalize($dirname).basename($this->template);
		umask(0002);
        if (!is_file($cachename)) {
			ob_start();
			include $this->template;
			$phtml = ob_get_clean();
			
            $phtml = addslashes($phtml);
			
            $phtml = preg_replace('/\{(.+)\}/','<?=$this->_eval(\'$1;\');?>',$phtml);
			
            //Matches single elements
            $phtml = preg_replace('/<([\w]+):([\w]+) ?([^>]*)\/>/is','<'.'?$this->lib("$1")->tag$2($this->attr("$3"),null,$this);?'.'>',$phtml);
			
            while(true) {
                $newPhtml = preg_replace('/<(([\w]+):([\w]+)) ?([^>]*)>(.*?)<\/\\1>/is','<?ob_start();?>$5<?$this->lib("$2")->tag$3($this->attr("$4"),ob_get_clean(),$this);?>',$phtml);
                if ($newPhtml != $phtml)
                    $phtml = $newPhtml;
                else
                    break;
            }
			
            Dir::ensure(Dir::concat(CACHEDIR,$dirname));
			
			file_put_contents($cachename,$phtml);

        }
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
        extract($this->data);
        require_once $file;
    }
    private function _eval($expr) {
        extract($this->data);
        return eval("return ".stripslashes($expr));
    }
    private function lib($ns) {
        if (!$this->taglibs[$ns])
            throw new Exception(T('Unknown tag lib: %s',$ns));
        return $this->taglibs[$ns];
    }
    private function attr($string) {
		$attrs = new stdClass();
		preg_match_all('/(\w+)=("|\')([^\2]*?)\2/is',$string,$matches);
        
		foreach($matches[1] as $i=>$name) {
			$value = $matches[3][$i];
			$attrs->$name = $value;
		}
        
        return $attrs;
    }
}