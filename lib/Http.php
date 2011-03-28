<?php
class Http {

    public static function post($url,$data,$headers = array(),$nonBlock) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw new Exception(curl_error($ch),curl_errno($ch));
        }
        return $result;
    }
    public static function put($url,$data,$headers = array()) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PUT, true);
        $fp = tmpfile();
        fwrite($fp,$data);
        fseek($fp, 0);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLOPT_INFILE,$fp);
        curl_setopt($ch,CURLOPT_INFILESIZE,strlen($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw new Exception(curl_error($ch),curl_errno($ch));
        }
        return $result;
    }
    public static function get($url,$headers = array()) {
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw new Exception(curl_error($ch),curl_errno($ch));
        }
        return $result;
    }
}