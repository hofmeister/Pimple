<?php
class Request {
	private static $post;
	private static $get;
	private static $file;
    /**
     * Get POST parms
     *
     * @param string $key
     * @param mixed $default
     * @return Request_Parms
     */
	public static function post($key = null,$default = null) {
        if ($key) {
            return (self::post() && self::post()->__isset($key)) ? self::post()->__get($key) : $default;
        }
        if (count($_POST) == 0) return false;
		if (!self::$post) {
			self::$post = new Request_Parms($_POST);
		}
		return self::$post;
	}

    /**
     * Get F parms
     *
     * @param string $key
     * @param mixed $default
     * @return Request_Parms
     */
	public static function file($key = null,$default = null) {
        if ($key) {
            return self::file()->__isset($key) ? self::file()->__get($key) : $default;
        }
		if (!self::$file) {
            self::$file = new Request_Multipart($_FILES);
		}
		return self::$file;
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
            return self::get()->__isset($key) ? self::get()->__get($key) : $default;
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

class Request_Parms implements POPOWrapper {
    private $_data = array();

    public function __construct($array) {
        foreach($array as $key=>$value) {
            $this->__set($key,is_string($value) ? stripslashes($value) : $value);
        }
    }
    public function  __get($name) {
        if ($this->__isset($name))
            return $this->_data[$name];
        return "";
    }
    public function  __set($name, $value) {
        $this->_data[$name] = $value;
    }
    public function  __isset($name) {
        return array_key_exists($name,$this->_data);
    }
    public function get() {
        $args = func_get_args();
        if (count($args) == 1) {
            return $this->__get($args[0]);
        }
        $result = new stdClass();
        foreach($args as $key) {
            $result->$key = $this->__get($key);
        }
        return $result;
    }
	public function clear() {
		$this->_data = array();
	}
    public function toPOPO() {
        return ArrayUtil::toObject($this->_data);
    }
    public function toArray() {
        return $this->_data;
    }
}

class Request_Multipart extends Request_Parms {
    
    public function __set($name,$value) {
        $msg = '';
        switch($value['error']) {
            case UPLOAD_ERR_CANT_WRITE:
                $msg = T('We are experiencing internal errors (%s). Please try again',1);
                break;
            case UPLOAD_ERR_EXTENSION:
                $msg = T('Extension is not allowed');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $msg = T('File size was to big (%s)',1);
                break;
            case UPLOAD_ERR_INI_SIZE:
                $msg = T('File size was to big (%s)',2);
                break;
            case UPLOAD_ERR_NO_FILE:
                $msg = T('Please select a file to upload');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg = T('We are experiencing internal errors (%s). Please try again',2);
                break;
            case UPLOAD_ERR_PARTIAL:
                $msg = T('File upload failed - please try again');
                break;
            case UPLOAD_ERR_OK:
            default:
                //Do nothing
                break;
                
        }
        if ($msg) {
            MessageHandler::instance()->flash($msg,true);
        }

        parent::__set($name, $value);
    }
    public function read($name) {
        $data = $this->__get($name);
        if ($data['tmp_name']) {
            return file_get_contents($data['tmp_name']);
        }
    }
    public function moveto($name,$dest) {
        $data = $this->__get($name);
        if ($data['tmp_name']) {
            move_uploaded_file($data['tmp_name'],$dest);
        }
    }
}