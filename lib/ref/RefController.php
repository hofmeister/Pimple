<?php
class RefController extends RefClass {
    public function getUrl() {
        $url = strtolower(str_ireplace('controller','',$this->name));
        return $url;
    }
}
class RefControllerMethod extends RefMethod {
    public function getUrl() {
        return strtolower($this->name);
    }
    public function getOutput() {
        return @implode(',',$this->doc['output']);
    }
}