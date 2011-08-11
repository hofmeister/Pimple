<?php
require_once 'Abstract.php';
/**
 * Validate that value is not empty
 */
class NotEmptyValidate extends AbstractValidate {
    public function validate($value,$data) {
        return $value != null;
    }
    public function getError() {
        return T('Cannot be blank');
    }
}