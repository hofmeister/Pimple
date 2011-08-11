<?php
require_once 'Abstract.php';
/**
 * Allow value to be optional - when multiple validators are specified for a single field
 */
class OptionalValidate extends AbstractValidate {
    public function validate($value,$data) {
        return true;
    }
    public function getError() {
        return '';
    }
}