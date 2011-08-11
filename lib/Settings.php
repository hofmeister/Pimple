<?php
/**
 * Settings (name / value) implementation
 */
class Settings {
	const ENCODING = 'g_encoding';
    const DEBUG = 'g_debug';
    private static $settings = array();
    public static function set($name,$value) {
        self::$settings[$name] = $value;
    }
    public static function has($name) {
        return isset(self::$settings[$name]);
    }
    public static function get($name,$default = 'noDefaultValue') {
        if (isset(self::$settings[$name]))
			return self::$settings[$name];
		else if ($default !== 'noDefaultValue')
			return $default;
		throw new Exception(sprintf('Setting: %s was not set',$name));
    }
}
Settings::set(Settings::ENCODING,'UTF-8');