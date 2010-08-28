<?php
class Request {
	private static $post;
	private static $get;
	public static function post() {
		if (!self::$post) {
			self::$post = new stdClass();
			foreach($_POST as $key=>$value) {
				self::$post->$key = $value;
			}
		}
		return self::$post;
	}
	public static function get() {
		if (!self::$get) {
			self::$get = new stdClass();
			foreach($_GET as $key=>$value) {
				self::$get->$key = $value;
			}
		}
		return self::$get;
	}
}
