<?php
class Request {
	private static $post;
	private static $get;
	public static function post($key = null,$default = null) {
        if ($key) {
            return self::post()->$key ? self::post()->$key : $default;
        }
        if (count($_POST) == 0) return false;
		if (!self::$post) {
			self::$post = new stdClass();
			foreach($_POST as $key=>$value) {
				self::$post->$key = $value;
			}
		}
		return self::$post;
	}
	public static function get($key = null,$default = null) {
        if ($key) {
            return self::get()->$key ? self::get()->$key : $default;
        }
		if (!self::$get) {
			self::$get = new stdClass();
			foreach($_GET as $key=>$value) {
				self::$get->$key = $value;
			}
		}
		return self::$get;
	}
}
