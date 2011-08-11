<?php
/**
 * Interface for classes that can be converted to Plain Old PHP Objects
 */
interface POPOWrapper {
    /**
     * @return stdClass
     */
    public function toPOPO();
}
/**
 * Interface for classes that can be converted to arrays
 */
interface ArrayWrapper {
    /**
     * @return array
     */
    public function toArray();
}