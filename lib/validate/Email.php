<?php
require_once 'Abstract.php';

class EmailValidate extends AbstractValidate {
    public function validate($value,$data) {
        if (!$value) return true; //No value is allowed - use required to require
        return preg_match('/^[A-Å0-9\.\_\-\+]+@[A-Å0-9\._\-]+\.[A-Å0-9\._\-]{2,}$/i',$value);
    }
    public function getError() {
        return T('Invalid email');
    }
}