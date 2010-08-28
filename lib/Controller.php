<?php
class Controller {
    protected $name;
    public function __construct() {
        $this->name = get_class($this);
    }
	public function redirect($controller,$action,$parms = array()) {
		$url = Url::makeLink($controller, $action, $parms);
		header('Location: '.$url);
		die();
	}
}