<?php
require_once 'Interrupt.php';
require_once 'Validate.php';

class Controller {
    protected $name;
    protected $validation = array();
    public function __construct() {
        $this->name = get_class($this);
    }
	public function redirect($controller,$action,$parms = array()) {
		$url = Url::makeLink($controller, $action, $parms);
		header('Location: '.$url);
		die();
	}
    public function validate($fields,$data = null) {
        $this->validation = $fields;
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