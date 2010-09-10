<?php
require_once 'validate/Abstract.php';
require_once 'validate/Exception.php';
require_once 'validate/Email.php';
require_once 'validate/Equals.php';
require_once 'validate/Max.php';
require_once 'validate/Min.php';
require_once 'validate/NotEmpty.php';
require_once 'validate/Captcha.php';
require_once 'validate/Optional.php';

class Validate {
    private static $validators = array();
    private static $errors = array();
    public static function getValidator($identifier) {
        list($id,$args) = explode('[',rtrim($identifier,']'));
        if (self::$validators[$id]) {
            self::$validators[$id]->setArgs(explode(',',$args));
            return self::$validators[$id];
        } else {
            throw new Exception(T('Unknown validator: %s',$id));
        }
    }
    public static function registerValidator($id,$object) {
        self::$validators[$id] = $object;
    }
    public static function setErrors($errors) {
        self::$errors = $errors;
    }
    public static function getFieldErrors($field) {
        return self::$errors[$field];
    }
    public static function addFieldError($field,$error) {
        if (!is_array(self::$errors[$field]))
            self::$errors[$field] = array();
        self::$errors[$field][] = $error;
    }
    public static function isFieldValid($field) {
        if (count(self::$errors) > 0) {
            return @count(self::$errors[$field]) == 0;
        }
    }
}
Validate::registerValidator('required',new NotEmptyValidate());
Validate::registerValidator('optional',new OptionalValidate());
Validate::registerValidator('email',new EmailValidate());
Validate::registerValidator('min',new MinValidate());
Validate::registerValidator('max',new MaxValidate());
Validate::registerValidator('equal',new EqualsValidate());
Validate::registerValidator('captcha',new CaptchaValidate());