<?php
class Settings {
    private static $settings = array();
    public static function set($name,$value) {
        self::$settings[$name] = $value;
    }
    public static function get($name,$default = null) {
        return self::$settings[$name] ? self::$settings[$name] : $default;
    }
}