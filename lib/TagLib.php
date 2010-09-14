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
        $view = $args[2];
        if (!$view) {
            $view = View::current();
        }
        $attrObj = new stdClass();
        if ($attrs) {
            if (is_array($attrs) || is_object($attrs)) {
                //var_dump($args[0]);
                foreach($attrs as $key=>$value) {
                    $attrObj->$key = $value;
                }

            } else {
                throw new InvalidArgumentException('Tags accept only arrays and objects as attributes');
            }
        }

        return $this->$method($attrObj,$args[1],$view);
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
