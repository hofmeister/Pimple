<?php
/**
 * JS util class
 */
class Javascript {

    public static function minify($filename) {
        require_once Pimple::instance()->getRessource('lib/minify/min/lib/JSMin.php');
        return JSMin::minify(String::normalize(file_get_contents($filename),false),$filename);
    }
    public static function verify($filename) {
        require_once Pimple::instance()->getRessource('lib/j4p5/js.php');
        return js::run(file_get_contents($filename));
    }
    public static function compileAll() {
        $jsDir = Dir::concat(Pimple::instance()->getSiteDir(),'www/js/');
    }
    public static function compileDir($dir,$destFile) {
        $dh = opendir($dir);
        if (!$dh)
            throw new Exception ('Unknown dir: '.$dir);
        
        while($file = readdir($dh)) {
            
            if ($file[0] == '.') continue;
            $absfile = Dir::normalize($dir).$file;
            if (is_file($absfile) && File::getExtension($file) == 'js') {
                File::append($destFile,"//FILE: $file".chr(10));
                if (filesize($absfile) > 200000)
                    File::append($destFile,file_get_contents($absfile));
                else
                    File::append($destFile,self::minify($absfile));
                File::append($destFile,chr(10).chr(10));
            } else if (is_dir($absfile)) {
                self::compileDir($absfile,$destFile);
            }
        }
        closedir($dh);
    }
}