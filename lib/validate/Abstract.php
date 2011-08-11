<?php
/**
 * Abstract base class for all validators
 */
abstract class AbstractValidate {
    protected $args;
    abstract public function validate($value,$data);
    abstract public function getError();
    public function getArgs() {
        return $this->args;
    }

    public function setArgs($args) {
        $this->args = $args;
    }
}