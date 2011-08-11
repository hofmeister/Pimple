<?php
/**
 * Provides several methods for getting information of the user agent
 */
class Browser {
	const CODE_FIREFOX 	= 'ff';
	const CODE_IE 		= 'ie';
	const CODE_OPERA 	= 'opera';
	const CODE_CHROME	= 'chrome';
	const CODE_CONSOLE  = 'cli';
	
	const BROWSER_FIREFOX 	= 1;
	const BROWSER_IE 		= 2;
	const BROWSER_OPERA 	= 3;
	const BROWSER_CHROME	= 4;
	const BROWSER_OTHER		= 5;
	
	const OS_WINDOWS		= 1;
	const OS_MAC			= 2;
	const OS_LINUX			= 3;
	const OS_SUN 			= 4;
	const OS_OTHER			= 5;
	private static $browser;
	
	public static function getBrowser() {
		
		if (!self::$browser) {
			if (!self::isConsole() && isset($_SERVER['HTTP_USER_AGENT'])) {
				self::$browser = @get_browser();
            }
		}
		return self::$browser;
	}
    public static function isBrowser($browser) {

    }
	public static function isCrawler() {
		if (self::isConsole())
			return false;
		return (self::getBrowser()->crawler) ? TRUE : FALSE;
	}
	public static function getVersion($includeMinor = false) {
		if (self::isConsole())
			return false;
		if ($includeMinor)
			return floatval(self::getBrowser()->version);
		else
			return intval(self::getBrowser()->majorver);
	}
	/**
	 * Check if browser meets specific requirements 
	 *
	 * @param array $reqs An array of the acceptable minimum for browsers
	 * @return boolean TRUE if browser meets reqs
	 */
	public static function checkRequirements($reqs) {
		if (self::isConsole())
			return false;
		$result = get_browser(0,true);
		foreach ($reqs as $name=>$value) {
			if (is_int($value) && intval($result[$name]) < $value)
				return false;
			if (is_float($value) && floatval($result[$name]) < $value)
				return false;
			if (is_string($value) && $result[$name] != $value)
				return false;
			if (is_array($value) && !in_array($result[$name],$value))
				return false;
		}
		return true;
	}
	public static function isMobile() {
		if (self::isConsole())
			return false;
		return (self::getBrowser()->ismobiledevice) ? TRUE : FALSE;
	}
	public static function getOS() {
		if (self::isConsole())
			return self::OS_OTHER;
			
		$platform = strtolower(self::getBrowser()->platform);
		switch(substr($platform,0,3)) {
			case 'win':
				return self::OS_WINDOWS;
			case 'max':
				return self::OS_MAC;
		}
				
		switch($platform) {
			case 'linux':
				return self::OS_LINUX;
			case 'sunos':
				return self::OS_SUN;
		}
		return self::OS_OTHER;
	}
	
	public static function getUserAgentCode($includeVersion = FALSE) {
		if (self::isConsole())
			return self::CODE_CONSOLE;
			
		$output = null;
		switch(strtolower(self::getBrowser()->browser)) {
			case 'firefox':
				$output = self::CODE_FIREFOX;
				break;
			case 'opera':
				$output = self::CODE_OPERA;
				break;
			case 'chrome':
				$output = self::CODE_CHROME;
				break;
			case 'ie':
				$output = self::CODE_IE;
				break;
		}
		if ($includeVersion && $output !== NULL) {
			if (self::getVersion() > 0)
				$output .= self::getVersion();
			elseif (self::getBrowser()->beta)
				$output .= '_beta';
		}
		return $output;
	}
	
	public static function getUserAgent() {
		if (self::isConsole())
			return self::CODE_CONSOLE;
			
		switch(strtolower(self::getBrowser()->browser)) {
			case 'firefox':
				return self::BROWSER_FIREFOX;
			case 'opera':
				return self::BROWSER_OPERA;
			case 'chrome':
				return self::BROWSER_CHROME;
			case 'ie':
				return self::BROWSER_IE;
			default:
				return self::BROWSER_OTHER;
		}
	}
	
	public static function isConsole() {
		return isset($_SERVER['SHELL']);
	}
    /**
     * Get IP adress (regardless if were behind proxy...)
     *
     * @return string
     */
    public static function getRemoteAddr() {
        return (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ?
                $_SERVER['HTTP_X_FORWARDED_FOR'] :
                    $_SERVER['REMOTE_ADDR'];
    }
}

