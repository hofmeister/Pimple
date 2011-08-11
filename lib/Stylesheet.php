<?php
/**
 * Stylesheet util class
 */
class Stylesheet {
    public static function minify($filename) {
        require_once Pimple::instance()->getRessource('lib/minify/min/lib/Minify/CSS.php');
        require_once Pimple::instance()->getRessource('lib/minify/min/lib/Minify/CSS/Compressor.php');

        $isPimple = String::StartsWith($filename, Pimple::instance()->getBaseDir());
        $base = dirname($isPimple ? substr($filename,strlen(Pimple::instance()->getBaseDir().'www/')) : substr($filename,strlen(Pimple::instance()->getSiteDir()))).'/';
        $base = $isPimple ? Settings::get(Pimple::URL).$base : Url::basePath().$base;
        return Minify_CSS::minify(String::normalize(file_get_contents($filename),false),array(
            'prependRelativePath'=>$base
        ));
    }
}