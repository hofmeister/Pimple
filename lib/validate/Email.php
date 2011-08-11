<?php
require_once 'Abstract.php';
/**
 * Validate email value
 */
class EmailValidate extends AbstractValidate {
    const REGEX = '[A-Å0-9\.\_\-\+]+@[A-Å0-9\._\-]+\.[A-Å0-9\._\-]{2,}';
    public function validate($value,$data) {
        if (!$value) return true; //No value is allowed - use required to require
        return preg_match(sprintf('/^%s$/i',self::REGEX),$value);
    }
    public function getError() {
        return T('Invalid email');
    }
}