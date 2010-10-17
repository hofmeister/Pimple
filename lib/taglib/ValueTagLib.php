<?php

class ValueTagLib extends TagLib {

    public function  __construct($preprocess = false) {
        parent::__construct($preprocess);
    }

	protected function tagDate($attrs) {
        if (!$attrs->format)
            $attrs->format = Settings::get(Date::DATE_FORMAT,'Y-m-d');
        if (!$attrs->value) $attrs->value = trim($this->body());
        if (preg_match('/[^0-9]/',$attrs->value)) {
            //Not all numbers
            $attrs->value = strtotime($attrs->value);
        } else {
            $attrs->value = intval($attrs->value);
        }
        return date($attrs->format,$attrs->value);
	}
    protected function tagTime($attrs) {
        if (!$attrs->format)
            $attrs->format = Settings::get(Date::TIME_FORMAT,'H:i:s');
        if (!$attrs->value) $attrs->value = trim($this->body());
        return $this->tagDate($attrs);
	}
    protected function tagDateTime($attrs) {
        if (!$attrs->format)
            $attrs->format = Settings::get(Date::DATETIME_FORMAT,'Y-m-d H:i:s');
        if (!$attrs->value) $attrs->value = trim($this->body());
        return $this->tagDate($attrs);
	}
    protected function tagInt($attrs) {
        $int = intval($this->body());
        return number_format($int,0);
    }
    protected function tagNumber($attrs) {
        if (!$attrs->decimals) $attrs->decimals = 2;
        $number = doubleval($this->body());
        return number_format($number,$attrs->decimals);
    }
}