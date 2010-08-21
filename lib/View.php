<?php
class View {
    private $template,$data,$taglibs;

    public function  __construct($template,$data) {
        $this->template = $template;
        $this->data = $data;
        $this->taglibs = Pimple::instance()->getTagLibs();
    }
    public function render() {
        $dirname = dirname(substr($this->template,strlen(BASEDIR)));
        $cachename = Dir::normalize(CACHEDIR).Dir::normalize($dirname).basename($this->template);
        if (!is_file($cachename)) {
            $phtml = addslashes(file_get_contents($this->template));
            $phtml = preg_replace('/\{(.+)\}/','<?=$this->_eval(\'$1;\');?>',$phtml);
            //Matches single elements
            $phtml = preg_replace('/<([\w]+):([\w]+) ?([^>]*)/>/is','<?$this->lib("$1")->tag$2($this->attr("$3"),$this);?>',$phtml);
            while(true) {
                $newPhtml = preg_replace('/<(([\w]+):([\w]+)) ?([^>]*)>(.*?)<\/\\1>/is','<?ob_start();?>$5<?$this->lib("$2")->tag$3(ob_get_clean(),$this->attr("$4"),$this);?>',$phtml);
                if ($newPhtml != $phtml)
                    $phtml = $newPhtml;
                else
                    break;
            }
            
            
            Dir::ensure(Dir::concat(CACHEDIR,$dirname));

            file_put_contents($cachename,$phtml);
        }
        ob_start();
        $this->_include($cachename);
        //unlink($cachename);
        
        return ob_get_clean();
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
        return array();
    }
}