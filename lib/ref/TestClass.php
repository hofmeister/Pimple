<?php
/**
 * Some fancy class
 * @deprecated
 * @namespace something
 */
abstract class SomeTest 
        extends TestExtends
        implements SomeInterface, AnotherInterface {
    
    const aConst = "TEST";
    
    public $varname = "value";
    /**
     * the constructor 
     */
    function __construct($varname) {
        $this->varname = $varname;
    }
    /**
     * Fancy pants
     * @param type $more
     * @param type $args
     * @param type $allowed 
     * @return return type
     */
    public function test($more,$args,
                $allowed) {
        
    }
    /**
     * Something usefull
     * @deprecated
     */
    abstract protected function privTest($some , $var = 'asd',$test = 'ojab');
    
    public static function forever() {
        
    }
}

/**
 * Some fancy class
 * @namespace other thing
 */
abstract class SomeOtherTest 
        extends TestExtends
        implements OddFace, ThirdInterface,
                    SomeInterface, AnotherInterface{
    
    const aConst = "TEST";
    
    public $varname = "value";
    
    function __construct($varname) {
        $this->varname = $varname;
    }
    public function test($more,$args,
                $allowed) {
        
    }
    
    abstract protected function privTest($some , $var = 'asd');
    
    public static function forever() {
        
    }
}