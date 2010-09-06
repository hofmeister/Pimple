<?php
class Util {
    private static $counters = array();
    public static function count($name) {
        if (!self::$counters[$name]) {
            self::$counters[$name] = 0;
        }
        return ++self::$counters[$name];
    }
}