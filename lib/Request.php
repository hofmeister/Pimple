<?php
class Request {
	private static $post;
	private static $get;
    /**
     * Get POST parms
     *
     * @param string $key
     * @param mixed $default
     * @return Request_Parms
     */
	public static function post($key = null,$default = null) {
        if ($key) {
            return self::post()->$key ? self::post()->$key : $default;
        }
        if (count($_POST) == 0) return false;
		if (!self::$post) {
			self::$post = new Request_Parms($_POST);
		}
		return self::$post;
	}

    /**
     * Get GET parms
     *
     * @param string $key
     * @param mixed $default
     * @return Request_Parms
     */
	public static function get($key = null,$default = null) {
        if ($key) {
            return self::get()->$key ? self::get()->$key : $default;
        }
		if (!self::$get) {
            self::$get = new Request_Parms($_GET);
		}
		return self::$get;
	}
    public static function isAjax() {
        return array_key_exists('__ajax',$_REQUEST) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
}
class Request_Parms {
    private $_data = array();

    public function __construct($array) {
        foreach($array as $key=>$value) {
            $this->_data[$key] = stripslashes($value);
        }
    }
    public function  __get($name) {
        return $this->_data[$name];
    }
    public function  __set($name, $value) {
        $this->_data[$name] = $value;
    }
    public function get() {
        $args = func_get_args();
        if (count($args) == 1) {
            return $this->$args[0];
        }
        $result = new stdClass();
        foreach($args as $key) {
            $result->$key = $this->$key;
        }
        return $result;
    }
	public function clear() {
		foreach($this as $key=>$value) {
			unset($this->$key);
		}
	}
}
