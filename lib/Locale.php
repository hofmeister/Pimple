<?php
class PimpleLocale {
    public static function T($format) {
        $args = func_get_args();
        array_shift($args);
        return vsprintf(gettext($format),$args);
    }
}

function T($format) {
    $args = func_get_args();
    array_shift($args);
    return vsprintf(gettext($format),$args);
}
if (!function_exists('gettext')) {
    //Dummmy method
    //@TODO: Remove this...
    function gettext($string) {
        return $string;
    }
}
