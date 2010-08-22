<?php
class Dir {
    /**
     * Dir concatenate
     */
    public static function concat($dir1,$dir2) {
        return self::normalize(rtrim($dir1,'/ ').'/'.ltrim($dir2,'/ '));
    }
    public static function normalize($dir) {
        return '/'.trim($dir,'/').'/';
    }
    public static function ensure($dirs) {
        if (is_array($dirs)) {
            foreach($dirs as $dir)
                self::ensure($dir);
        } else {
            if (!self::exist($dirs)) {
				mkdir($dirs,0775,true);
			}
        }
    }
    public static function exist($dir) {
        return is_dir($dir);
    }
}