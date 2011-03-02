<?php
class Server {
	public static $PLATFORM_WINDOWS = 'Windows';
	public static $PLATFORM_MAC = 'Mac';
	public static $PLATFORM_LINUX = 'Linux';
	public static $PLATFORM_UNIX = 'Unix';
	
	public static function IsPlatform($Platform) {
		return (strstr($_SERVER['HTTP_USER_AGENT'], $Platform));
	}
}