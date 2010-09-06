<?php
require_once 'Abstract.php';

class MaxValidate extends AbstractValidate {
    public function validate($value,$data) {
        if (!$value) return true; //No value is allowed - use required to require
        $min = (int)$this->args[0];
        $type = $this->args[1];
        if ($min == 0) throw new Exception (T('max validator requires an int argument of 1 or higher'));
        switch ($type) {
            case 'int':
                return $min <= (int) $value;
            case 'float':
                return $min <= (float) $value;
            case 'double':
                return $min <= (double) $value;
            default:
                return $min <= strlen($value);
        }
    }
    public function getError() {
        $max = (int)$this->args[0];
        $type = $this->args[1];
        switch ($type) {
            case 'int':
            case 'float':
            case 'double':
                return T('Value must be lower than or equal to %s',$max);
            default:
                return T('Value must be at most %s characters long',$max);
        }
    }
}