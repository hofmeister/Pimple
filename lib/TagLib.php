<?php
class TagLib {
    public function __call($name,$args) {
        $method = 'tag'.ucfirst($name);
        if (!method_exists($this,$method)) {
            throw new Exception(T('Unknown tag: %s::%s',get_class($this),$name),E_ERROR);
        }
        $attrs = $args[0];
        if ($attrs) {
            //var_dump($args[0]);
            foreach($attrs as $key=>$value) {
                $attrs->$key = $this->evalAttr($value,$args[2]);
            }

        }

        return $this->$method($attrs,$args[1],$args[2]);
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
}
