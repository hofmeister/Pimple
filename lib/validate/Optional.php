<?php
require_once 'Abstract.php';

class OptionalValidate extends AbstractValidate {
    public function validate($value,$data) {
        return true;
    }
    public function getError() {
        return '';
    }
}