<?php
/**
 * Value formatting tags
 * @namespace val
 */
class ValueTagLib extends TagLib {

    public function  __construct($preprocess = false) {
        parent::__construct($preprocess);
    }

    /**
     * Format value as date
     * @param string format | date format - defaults to @Setting(Date::DATE_FORMAT)
     * @param string|int value | date string or unix timestamp (defaults to body)
     * @container both
     */
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
    /**
     * Shorten string if mor that max - and outputs span with original text in title
     * @param int max | max length
     * @container true
     */
    protected function tagShort($attrs) {
        $max = intval($attrs->max);
        $string = trim($this->body());
        $orig = $string;
        if (strlen($string) > $max) {
            $string = substr($string,0,$max-3).'...';
        }
        $span = new HtmlElement('span',array('title'=>$orig));
        $span->addChild(new HtmlText($string));
        return $span;
	}
    /**
     * Format value as time
     * @param string format | date format - defaults to @Setting(Date::DATE_FORMAT)
     * @param string|int value | date string or unix timestamp (defaults to body)
     * @container both
     */
    protected function tagTime($attrs) {
        if (!$attrs->format)
            $attrs->format = Settings::get(Date::TIME_FORMAT,'H:i:s');
        if (!$attrs->value) $attrs->value = trim($this->body());
        return $this->tagDate($attrs);
	}
    /**
     * Format value as dateTime
     * @param string format | date format - defaults to @Setting(Date::DATE_FORMAT)
     * @param string|int value | date string or unix timestamp (defaults to body)
     * @container both
     */
    protected function tagDateTime($attrs) {
        if (!$attrs->format)
            $attrs->format = Settings::get(Date::DATETIME_FORMAT,'Y-m-d H:i:s');
        if (!$attrs->value) $attrs->value = trim($this->body());
        return $this->tagDate($attrs);
	}
    /**
     * Format as integer (from body)
     */
    protected function tagInt($attrs) {
        $int = intval($this->body());
        return number_format($int,0);
    }
    /**
     * Format as number (from body)
     * @param int decimals | how many decimals to show - defaults to 2
     */
    protected function tagNumber($attrs) {
        if (!isset($attrs->decimals)) $attrs->decimals = 2;
        $number = doubleval($this->body());
        return number_format($number,$attrs->decimals);
    }
}