<?php
class Math {

    public static function DMStoDECfromString($str) {
        $str = trim($str);
        $lastLetter = strtoupper(substr($str,-1));
        $parts = preg_split('/[^0-9]/',preg_replace('/[^0-9\.\s]/','',$str));
        //var_dump($parts);
        return self::DMStoDEC(intval($parts[0]),intval($parts[1]),intval($parts[2])) * ($lastLetter == 'W' ? -1 : 1) ;
    }
    public static function DMStoDEC($deg,$min,$sec) {
        return $deg+((($min*60)+($sec))/3600);
    }
}
