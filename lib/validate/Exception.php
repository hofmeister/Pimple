<?php
class ValidationException extends Exception {
    private $errors;
    public function __construct($errors) {
        $this->errors = $errors;
    }
    public function getErrors() {
        return $this->errors;
    }
}