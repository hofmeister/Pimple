<?php

class Date {
	
	public static function getFirstDateOfWeek($week,$year = NULL) {
		if (!$year) $year = date('Y');
		$firstDayTime = mktime(0,0,0,01,01,$year);
		$lastMonday = strtotime('last Monday',$firstDayTime);
		$lastMonday = $lastMonday + (($week) * TIMESPAN_WEEK);
        return mktime(0,0,0,date('m',$lastMonday),date('d',$lastMonday),date('Y',$lastMonday));
	}
	public static function getLastDateOfWeek($week,$year = NULL) {
		if (!$year) $year = date('Y');
		$firstDayTime = mktime(23,59,59,01,01,$year);
		$nextSunday = strtotime('next Sunday',$firstDayTime);
		$nextSunday += (($week) * TIMESPAN_WEEK);
		return mktime(23,59,59,date('m',$nextSunday),date('d',$nextSunday),date('Y',$nextSunday));
	}
    
    
    private $timestamp;
    public function __construct($string = null) {
        if (Util::isInt($string)) {
            $this->timestamp = $string;
        } else {
            $this->timestamp = strtotime($string);
        }
    }
    public function toString($format = 'Y-m-d') {
        return date($format,$this->timestamp);
    }
    public function ToDbDate() {
        return $this->toString('Y-m-d');
    }
    public function ToDbDateTime() {
        return $this->toString('Y-m-d H:i:s');
    }
    public function  __toString() {
        return $this->toString();
    }
}
