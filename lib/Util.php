<?php
/**
 * General util methods
 */
class Util {
    private static $counters = array();
    public static function count($name) {
        if (!self::$counters[$name]) {
            self::$counters[$name] = 0;
        }
        return ++self::$counters[$name];
    }

    public static function first() {
        $args = func_get_args();
        foreach($args as $arg) {
            if ($arg) return $arg;
        }
        return null;
    }
}