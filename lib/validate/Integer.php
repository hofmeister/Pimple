<?php
require_once 'Abstract.php';

class IntegerValidate extends AbstractValidate {
    const REGEX = '[0-9]+';
    public function validate($value,$data) {
        if (!$value) return true; //No value is allowed - use required to require
        return preg_match(sprintf('/^%s$/i',self::REGEX),$value);
    }
    public function getError() {
        return T('Invalid integer');
    }
}