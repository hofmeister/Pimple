<?php
include_once 'RefBase.php';

class RefClass extends RefBase {
    public $isAbstract = false;
    public $constants = array();
    public $methods = array();
    public $properties = array();
    public $interfaces = array();
    public $extends;
}
class RefMethod extends RefBase {
    public $parms = array();
    public $isAbstract = false;
    public $isStatic = false;
    public $access = 'public';
}
class RefProperty extends RefBase {
    public $isStatic = false;
    public $access = 'public';
    public $default;
}
class RefParm {
    public $name;
    public $default;
}