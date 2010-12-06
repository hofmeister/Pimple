<?php
require_once 'Abstract.php';

class NotEmptyValidate extends AbstractValidate {
    public function validate($value,$data) {
        return $value != null;
    }
    public function getError() {
        return T('Cannot be blank');
    }
}