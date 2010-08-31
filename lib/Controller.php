<?php
require_once 'Interrupt.php';
require_once 'Validate.php';

class Controller {
    protected $name;
    protected $validation = array();
    public function __construct() {
        $this->name = get_class($this);
    }
	protected function redirect($controller = null,$action = null,$parms = array()) {
		Url::redirect($controller, $action, $parms);
	}
    protected function setFields($fields) {
        $this->validation = $fields;
    }
    protected function validate($fields = null,$data = null) {
        if ($fields)
            $this->validation = $fields;
        else
            $fields = $this->validation;
        if (!$data)
            $data = Request::post();
        if (!$data) throw new Interrupt(); //Not yet submitted...

        $errors = array();
        foreach($fields as $field=>$validation) {
            $value = $data->$field;
            $validations = explode(',',$validation);
            foreach($validations as $validator) {
                $v = Validate::getValidator($validator);
                if (!$v->validate($value,$data)) {
                    if (!is_array($errors[$field]))
                        $errors[$field] = array();
                    $errors[$field][] = $v->getError();
                }
            }
        }
        Validate::setErrors($errors);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
        return true;
    }
    public function getFieldValidation($field) {
        $validators = $this->validation[$field];
        if ($validators)
            return explode(',',$validators);
        return null;
    }
}