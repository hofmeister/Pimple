<?php
class DataUtil {

    public static function set(&$ds,$key,$value) {
        if (is_array($ds)){
            $ds[$key] = $value;
        } else if (is_object($ds)) {
            $ds->$key = $value;
        }
    }
    public static function get($ds,$key) {
        if (is_array($ds)){
            return $ds[$key];
        } else if (is_object($ds)) {
            return $ds->__get($key);
        }
    }

    public static function has($ds,$key) {
        if (is_array($ds) && array_key_exists($key,$ds)){
            return true;
        } else if (is_object($ds) && property_exists ($ds,$key)) {
            return true;
        }
        return false;
    }
    public static function isValid($ds) {
        return is_array($ds) || is_object($ds);
    }
    public static function merge(&$ds1,&$ds2) {
        
        if (!self::isValid($ds1)) {
            return $ds2;
        }
        if (!self::isValid($ds2)) {
            return $ds1;
        }
        foreach($ds2 as $key=>$value) {
            self::set($ds1,$key,$value);
        }
        return $ds1;
    }
}