<?php

class Zend {
	public static function _use($zendClass) {
		require_once Pimple::instance()->getRessource('lib/'.str_replace('_','/',$zendClass).'.php');
	}
}