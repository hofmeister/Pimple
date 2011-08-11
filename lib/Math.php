<?php
/**
 * Provides some math methods
 */

class Math {

    public static function DMStoDECfromString($str) {
        $str = utf8_decode($str);
        $str = trim($str,' \"\'“');
        $str = preg_replace('/[^0-9A-Z\s\.\+\-]*/is','',$str);
        $str = str_replace('.',' ',$str);

        var_dump($str);
        var_dump(explode(' ',$str));
        list($degrees,$min,$sec,$dir) = explode(' ',$str);
        if (!($degrees !== null && $min !== null && $sec !== null))
            return false;
        $dir = strtoupper($dir);
        //Convert from strings
        $degrees = floatval($degrees);
        $min = floatval($min);
        $sec = floatval($sec);

        if ($degrees < 0) {
            $negative = true;
            $degrees = abs($degrees);
        }

        //Calculate
        $sec=($sec/60);
        $min=($min+$sec);
        $min=($min/60);
        $decimal = ($degrees+$min);
        
        if ($negative || ($dir=="S") || ($dir=="W")) {
            $decimal = $decimal*-1;
        }
        return $decimal;
    }
}
