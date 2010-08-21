<?php
class Controller {
    protected $name;
    public function __construct() {
        $this->name = get_class($this);
    }
}