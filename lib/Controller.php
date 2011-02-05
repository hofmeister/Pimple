<?php
require_once 'Interrupt.php';

class Controller {
    protected $skipView = false;
    protected $skipLayout = false;

    protected $name;
    protected $validation = array();
    protected $data;
    protected $section = array();
    public function __construct() {
        $this->name = get_class($this);
    }
    public function getData() {
        return $this->data;
    }
    public function isSection($section) {
        return in_array($section,$this->section);
    }

    public function setSection($section) {
        array_push($this->section, $section);
    }
    public function getSection() {
        return $this->section;
    }

    public function setData($data) {
        $this->data = $data;
    }
    public function getSkipView() {
        return $this->skipView;
    }

    public function setSkipView($skipView) {
        $this->skipView = $skipView;
    }
    public function getSkipLayout() {
        return $this->skipLayout;
    }

    public function setSkipLayout($skipLayout) {
        $this->skipLayout = $skipLayout;
    }

    protected function redirect($controller = null,$action = null,$parms = array()) {
        $this->setSkipView(true);
        $url = Url::makeLink($controller, $action, $parms);
		Url::gotoUrl($url);
        echo T("Redirecting to %s",$url);
        throw new Interrupt(); //Not yet submitted...
	}
    protected function refresh() {
        $this->setSkipView(true);
        Url::refresh();
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
        if ($data === null)
            $data = Request::post();
        if (!$data) throw new Interrupt(); //Not yet submitted...

        $errors = array();
        foreach($fields as $field=>$validation) {
            if (String::EndsWith($field,'[]')) {
                $fieldName = str_replace('[]','',$field);
            } else {
                $fieldName = $field;
            }
            $value = $data->__get($fieldName);
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
                    
                    if (!$v->validate(trim($val),$data)) {
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
	protected function setStatus($code,$text) {
		header(sprintf('HTTP/1.1 %s %s',$code,$text),true);
	}
	protected function asJson($value) {
        $this->setSkipView(true);
        header('Content-type: application/json');
        echo json_encode($value);
        Pimple::instance()->end();
    }
	protected function asXml($value,$rootName = 'response') {
        $this->setSkipView(true);
        
		$xml = Xml::toXml($value,$rootName);
		header('Content-type: text/xml');
        echo $xml;
        Pimple::instance()->end();
    }
    protected function asText($value) {
        $this->setSkipView(true);
        header('Content-type: text/plain');
        echo $value;
        Pimple::instance()->end();
    }
    protected function setContentType($type) {
        header('Content-type: '.$type,true);
    }
    protected function asCSV($array,$firstRowHeader = true,$seperator = ';') {
        $this->setSkipView(true);
        header('Content-type: text/csv');
        if ($firstRowHeader) {
            $cols = array();
            foreach($array[0] as $colName=>$val) {
                $cols[] = $colName;
            }
            echo implode($seperator,$cols).chr(10);
        }
        for($i = 0; $i < count($array); $i++) {
            $vals = array();
            foreach($array[$i] as $val) {
                $vals[] = $val;
            }
            echo implode($seperator,$vals).chr(10);
        }

        echo $value;
        Pimple::instance()->end();
    }
    protected function asDownload($filename) {
        header('Content-Disposition: attachment; filename="'.$filename.'"');
    }
    protected function setCache($seconds) {

        header("Cache-Control: max-age=".$seconds,true);
        if ($seconds == 0) $seconds = -1*time();
        header("Expires: ".date(DATE_RFC1123,time()+$seconds),true);
    }
}