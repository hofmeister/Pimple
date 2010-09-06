<?php
/**
 * @author New Dawn Technologies
 * @version 1.0
 * @license BSD
 * @package Basic
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
    public static function isMap($array) {
        $i = 0;
        foreach($array as $key=>$value) {
            if (!is_int($key)) return true;
            if ($key !== $i) return true;
            $i++;
        }
        return false;
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
}

