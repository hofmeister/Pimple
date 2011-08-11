<?php
/**
 * Provides several methods for manipulating arrays
 */
class ArrayUtil {
	/**
	 * Merge this array with another array object or instance
	 *
	 * @param ArrayUtil|array $array
	 * @return ArrayUtil
	 */
	public static function merge($array1,$array2) {
		$keyI = 0;
		foreach($array2 as $key=>$value) {
			if ($key == $keyI)
				$array1[] = $value;
			else
				$array1[$key] = $value;
			$keyI++;
		}
		return $this;
	}
    public static function append(&$array1,$array2) {
        for($i = 0;$i < count($array2);$i++) {
            array_push($array1,$array2[$i]);
        }
        return $array1;
    }
    public static function insert(&$array,$value,$index) {
        $slice1 = array_values(array_slice($array,0,$index));
        $slice2 = array_values(array_slice($array,$index));
        if (!is_array($value)) {
            $value = array($value);
        } else {
            $value = array_values($value);
        }
        $array = array_merge($slice1,$value,$slice2);
        return $array;
    }
	public static function doRecursive(&$array,$method,$byReference = false) {
		if (!is_array($array)) return;
		foreach ($array as &$value) {
			if (is_array($value))
				self::doRecursive($value,$method);
			else {
				if ($byReference)
					$method($value);
				else
					$value = $method($value);
			}
		}
	}
    public static function equals($array1,$array2) {
        if (count($array1) != count($array2)) return false;
        foreach($array1 as $key=>$val) {
            if ($array2[$key] != $val) return false;
        }
        return true;
    }
	/**
	 * Trim empty elements of array
	 *
	 * @param array $array
	 * @return array
	 */
	public static function Trim(&$array) {
		foreach ($array as $key=>$value) {
			if (trim($value) == '') {
				unset($array[$key]);
			}
		}
		return $array;
	}
    /**
	 * Trim values
	 *
	 * @param array $array
	 * @return array
	 */
	public static function trimValues(&$array,$filter = null) {
		foreach ($array as $key=>$value) {
            if ($filter)
                $array[$key] = trim($value,$filter);
            else
                $array[$key] = trim($value);
		}
		return $array;
	}
    public static function isMap($array) {
         return ($array != array_values($array));
    }
    public static function isList($array) {
        return !self::isMap($array);
    }
	public static function fromObject($obj) {
		return get_object_vars($obj);
	}
	public static function toObject($array) {
		$obj = new stdClass();
		foreach($array as $key=>$value) {
			$obj->$key = $value;
		}
		return $obj;
	}
	public static function stripValues($arr){
		if (is_array($arr)){
			foreach ($arr as $key => $val){
				if (is_array($val)){
					$arr[$key] = self::stripValues($val);
				} else {
	    	    	$arr[$key] = strip_tags($val);
	    	    	$arr[$key] = preg_replace('/<[A-Za-z]+/is', '', $val);
	    	    }
		   	}
		}
		return $arr;
	}
	public static function EscapeRegex(array $arr) {
		if(count($arr) > 0) {
			foreach($arr as $k=>$str) {
				$arr[$k] = preg_quote($str);
			}
		}
		return $arr;
	}
}

