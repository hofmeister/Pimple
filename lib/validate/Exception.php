<?php
/**
 * Validation exception - used to jump out of flows when validation errors occur
 */
class ValidationException extends Exception {
    private $errors;
    public function __construct($errors) {
        $this->errors = $errors;
    }
    public function getErrors() {
        return $this->errors;
    }
}