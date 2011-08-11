<?php
require_once 'Abstract.php';
/**
 * Validate that value is number
 */
class NumberValidate extends AbstractValidate {
    const REGEX = '[0-9,\.]+';
    public function validate($value,$data) {
        if (!$value) return true; //No value is allowed - use required to require
        return preg_match(sprintf('/^%s$/i',self::REGEX),$value);
    }
    public function getError() {
        return T('Invalid number');
    }
}