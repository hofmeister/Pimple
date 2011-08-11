<?php
require_once 'Abstract.php';
/**
 * Validate that 2 values are equeal
 */
class EqualsValidate extends AbstractValidate {
    public function validate($value,$data) {
        $equals = $this->args[0];
        if (!$equals) throw new Exception(T('Missing argument for equals validation'));
        $value2 = $data->$equals;
        return $value == $value2;
    }
    public function getError() {
        return T('Values do not match');
    }
}