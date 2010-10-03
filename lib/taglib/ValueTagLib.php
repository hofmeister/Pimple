<?php

class ValueTagLib extends TagLib {
	protected function tagDate($attrs) {
        if (!$attrs->format)
            $attrs->format = 'Y-m-d';
        return date($attrs->format,$attrs->value);
	}
    protected function tagTime($attrs) {
        if (!$attrs->format)
            $attrs->format = 'H:i:s';
        return $this->tagDate($attrs);
	}
    protected function tagDateTime($attrs) {
        if (!$attrs->format)
            $attrs->format = 'Y-m-d H:i:s';
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