<?php
require_once 'Interrupt.php';

class Controller {
    protected $name;
    protected $validation = array();
    protected $data;
    public function __construct() {
        $this->name = get_class($this);
    }
    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
    }

    protected function redirect($controller = null,$action = null,$parms = array()) {
        $url = Url::makeLink($controller, $action, $parms);
		Url::gotoUrl($url);
        echo T("Redirecting to %s",$url);
        throw new Interrupt(); //Not yet submitted...
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
            if (String::EndsWith($field,'[]')) {
                $fieldName = str_replace('[]','',$field);
            } else {
                $fieldName = $field;
            }
            $value = $data->$fieldName;
            if (!is_array($value)) {
                $valArr = array($value);
            } else {
                $valArr = $value;
            }
            $validations = explode(',',$validation);
            foreach($validations as $validator) {
                $v = Validate::getValidator($validator);
                foreach($valArr as $i=>$val) {
                    if (String::EndsWith($field,'[]')) {
                        $validationName = str_replace('[]',"[$i]",$field);
                    } else {
                        $validationName = $field;
                    }
                    
                    if (!$v->validate($val,$data)) {
                        if (!is_array($errors[$validationName]))
                            $errors[$validationName] = array();
                        $errors[$validationName][] = $v->getError();
                    }
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
            return explode(',',preg_replace('/[\)\}]/',']',preg_replace('/[\(\{]/','[',$validators)));
        return null;
    }
    public function get($id) {
        $id = get_class($this).'_'.$id;
        return SessionHandler::get($id);
    }
    public function set($id,$value) {
        $id = get_class($this).'_'.$id;
        SessionHandler::set($id,$value);
    }
    public function clear($id) {
        $id = get_class($this).'_'.$id;
        SessionHandler::set($id,null);
    }
    protected function asJson($value) {
        header('Content-type: application/json');
        echo json_encode($value);
        Pimple::instance()->end();
    }
    protected function asText($value) {
        header('Content-type: text/plain');
        echo $value;
        Pimple::instance()->end();
    }
}