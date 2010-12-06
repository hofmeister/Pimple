<?php
class Http {

    public static function post($url,$data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw new Exception(curl_error($ch),curl_errno($ch));
        }
        return $result;
    }
    public static function put($url,$data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw new Exception(curl_error($ch),curl_errno($ch));
        }
        return $result;
    }
    public static function get($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw new Exception(curl_error($ch),curl_errno($ch));
        }
        return $result;
    }
}