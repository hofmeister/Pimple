<?php
/**
 * @author New Dawn Technologies
 * @version 1.0
 * @license BSD
 * @package Basic
 */
class IncludePath {
	/**
	 * Singleton instance
	 *
	 * @var IncludePath
	 */
	private static $instance;

    protected $paths = array();
	public function __construct() {
		$this->paths = $this->getPaths();
		array_unique($this->paths);
	}
    
    public function getPaths() {
        return explode(PATH_SEPARATOR,get_include_path());
    }

   
	public function addPath($path) {
		$path = rtrim($path,'\\/');
		if (!in_array($path,$this->paths))
			array_push($this->paths,$path);
		$this->setIncludePath();
	}
	public function setIncludePath() {
		$path = implode(PATH_SEPARATOR,$this->paths);
        $path = preg_replace('/[\/\\\\]/','/',trim($path,PATH_SEPARATOR));
		set_include_path($path);
	}
	
	
	/**
	 * @return IncludePath
	 */
	public static function instance() {
		if (!self::$instance)
			self::$instance = new IncludePath();
		return self::$instance;
	}

}

