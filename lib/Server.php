<?php
class Server {
	const PLATFORM_WINDOWS = 'win';
	const PLATFORM_MAC = 'mac';
	const PLATFORM_LINUX = 'linux';
	const PLATFORM_UNIX = 'unix';
	
	public static function IsPlatform($Platform) {
		return (strstr(strtolower(PHP_OS), $Platform));
	}
}