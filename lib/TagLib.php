<?php
class TagLib {
    private $preprocess = false;
    function __construct($preprocess = false) {
        $this->preprocess = $preprocess;
    }
    public function isPreprocess() {
        return $this->preprocess;
    }


    public function __call($name,$args) {
        $method = 'tag'.ucfirst($name);
        if (!method_exists($this,$method)) {
            throw new Exception(T('Unknown tag: %s::%s',get_class($this),$name),E_ERROR);
        }
        $attrs = $args[0];
        if ($attrs) {
            //var_dump($args[0]);
            $attrObj = new stdClass();
            foreach($attrs as $key=>$value) {
                $attrObj->$key = $value;
            }

        }

        return $this->$method($attrObj,$args[1],$args[2]);
    }
    private function evalAttr($value,$view) {
        
        $regexp = '/\<\?\=(.+)\?>\}/';
        if (!preg_match($regexp,$value)) return $value;
        $expr = '?'.'>'.preg_replace($regexp,'<?=$view->_var(\'$1\');?>',$value);
        ob_start();
        eval($expr);
        $result =  ob_get_clean();
        return $result;
    }
    protected function toAttrString($arrayOrObject) {
        $array = ArrayUtil::fromObject($arrayOrObject);
        $out = '';
        foreach($array as $key=>$value) {
            $out .= $key.'="'.htmlentities($value,ENT_COMPAT,'UTF-8').'" ';
        }
        return trim($out);
    }
}
