<?php
class TagLib {
    private static $uidCount = 0;
    private $preprocess = false;
    private $bodies = array();

    function __construct($preprocess = false) {
        $this->preprocess = $preprocess;
    }
    public function isPreprocess() {
        return $this->preprocess;
    }

	protected function uid() {
		return 'guid-'.md5(microtime(true) + rand(1, 99999));
	}
    protected function toObject($value) {
        if (is_string($value)) {
            return json_decode(str_replace('\'','"',stripslashes($value)));
        } else {
            return $value;
        }
    }


    public function __call($name,$args) {
        return $this->callTag($name,$args[0],$args[1],$args[2]);
    }

    public function callTag($name,$attrs,$body = null,$view = null) {
        $method = 'tag'.ucfirst($name);
        if (!method_exists($this,$method)) {
            throw new Exception(T('Unknown tag: %s::%s',get_class($this),$name),E_ERROR);
        }
        array_push($this->bodies,$body);
        
        if (!$view) {
            $view = View::current();
        }
        $attrObj = new stdClass();
        if ($attrs) {
            if (is_array($attrs) || is_object($attrs)) {
                foreach($attrs as $key=>$value) {
                    $attrObj->$key = $value;
                }

            } else {
                throw new InvalidArgumentException('Tags accept only arrays and objects as attributes');
            }
        }

        $result = $this->$method($attrObj,$view);
        array_pop($this->bodies);
        return $result;
    }
    protected function body($body = null) {
        if ($body) {
            $this->bodies[count($this->bodies)-1] = $body;
        }
        $body = $this->bodies[count($this->bodies)-1];
        if (is_object($body)) {
            return $body->__toString();
        }
        return $body;
    }
    protected function toAttrString($arrayOrObject) {
        $array = ArrayUtil::fromObject($arrayOrObject);
        $out = '';
        foreach($array as $key=>$value) {
            $out .= $key.'="'.htmlentities($value,ENT_COMPAT,'UTF-8').'" ';
        }
        return trim($out);
    }
}
