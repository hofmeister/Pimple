<?php
class Dir {
    /**
     * Dir concatenate
     */
    public static function concat($dir1,$dir2) {
        return self::normalize(rtrim($dir1,'/ ').'/'.ltrim($dir2,'/ '));
    }
    public static function normalize($dir) {
        $dir= trim($dir,'/');
        if (strlen($dir) > 0)
            return '/'.$dir.'/';
        else {
            return '/';
        }
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
    public static function emptyDir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir")
                        self::remove($dir."/".$object,true);
                    else
                        unlink($dir."/".$object);
                }
            }
        }
    }
    public static function remove($dir,$recursive = false) {
        if (is_dir($dir)) {
            if ($recursive) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (filetype($dir."/".$object) == "dir")
                            self::remove($dir."/".$object,$recursive);
                        else
                            unlink($dir."/".$object);
                    }
                }
                reset($objects);
            }
            rmdir($dir);
        }
    }
}