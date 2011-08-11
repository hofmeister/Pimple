<?php
/**
 * Provides information about the local server
 */
class Server {
	const PLATFORM_WINDOWS = 'win';
	const PLATFORM_MAC = 'darwin';
	const PLATFORM_LINUX = 'linux';
	const PLATFORM_UNIX = 'unix';
	
	public static function IsPlatform($platform) {
        $os = strtolower(PHP_OS);
        $check = strtolower($platform);
		return (substr($os,0,strlen($platform)) == $check);
	}
}