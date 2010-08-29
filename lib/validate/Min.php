<?php
require_once 'Abstract.php';

class MinValidate extends AbstractValidate {
    public function validate($value,$data) {
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