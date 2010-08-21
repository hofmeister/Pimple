<?php
class Locale {
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