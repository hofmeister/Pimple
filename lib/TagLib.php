<?php
class TagLib {
    public function __call($name,$args) {
        $method = 'tag'.ucfirst($name);
        if (!method_exists($this,$method)) {
            throw new Exception(T('Unknown tag: %s::%s',get_class($this),$name),E_ERROR);
        }
        return $this->$method($args[0],$args[1],$args[2]);
    }
}
