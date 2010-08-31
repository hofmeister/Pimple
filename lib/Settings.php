<?php
class Settings {
	const ENCODING = 'g_encoding';
    private static $settings = array();
    public static function set($name,$value) {
        self::$settings[$name] = $value;
    }
    public static function get($name,$default = null) {
        if (self::$settings[$name])
			return self::$settings[$name];
		else if ($default !== null)
			return $default;
		throw new Exception(sprintf('Setting: %s was not set',$name));
    }
}
Settings::set(Settings::ENCODING,'UTF-8');