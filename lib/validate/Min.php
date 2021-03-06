<?php
require_once 'Abstract.php';
/**
 * Validate that value is no smaller than min
 */
class MinValidate extends AbstractValidate {
    public function validate($value,$data) {
        if (!$value) return true; //No value is allowed - use required to require
        $min = (int)$this->args[0];
        $type = $this->args[1];
        if ($min == 0) throw new Exception (T('min validator requires an int argument of 1 or higher'));
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
        $min = (int)$this->args[0];
        $type = $this->args[1];
        switch ($type) {
            case 'int':
            case 'float':
            case 'double':
                return T('Value must be higher than or equal to %s',$min);
            default:
                return T('Value must be atleast %s characters long',$min);
        }
    }
}