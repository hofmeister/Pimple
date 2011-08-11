<?php
/**
 * Provides methods for including and using the included Zend Framework
 */
class Zend {
	public static function _use($zendClass) {
		require_once Pimple::instance()->getRessource('lib/'.str_replace('_','/',$zendClass).'.php');
	}
}