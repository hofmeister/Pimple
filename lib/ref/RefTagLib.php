<?php
class RefTagLib extends RefClass {
    public function getNamespace() {
        return $this->doc['namespace'][0];
    }
    public function getTags() {
        $tags = array();
        foreach($this->methods as $method) {
            /* @var $method RefMethod */
            if (substr($method->name,0,3) == 'tag')
                $tags[] = $method;
        }
        return $tags;
    }
}
class RefTagLibMethod extends RefMethod {
    public function getTagName() {
        return substr(strtolower($this->name),3);
    }
    public function isContainer() {
        return count($this->doc['container']) > 0;
    }
}