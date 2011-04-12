<?php
class PimpleController extends Controller {

    public function captcha() {
        $width = Request::get('w',210);
        $height = Request::get('h',40);
        $characters = Request::get('c',6);
        $font = Pimple::instance()->getRessource('monofont.ttf');
        
        $possible = '23456789bcdfghjkmnpqrstvwxyz';
        $code = '';
        $i = 0;
        while ($i < $characters) {
         $code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
         $i++;
        }
        /* font size will be 75% of the image height */
        $font_size = $height * 0.75;
        $image = imagecreate($width, $height) or die('Cannot initialize new GD image stream');
        /* set the colours */
        $background_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 20, 40, 100);
        $noise_color = imagecolorallocate($image, 100, 120, 180);
        /* generate random dots in background */
        for( $i=0; $i<($width*$height)/3; $i++ ) {
         imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
        }
        /* generate random lines in background */
        for( $i=0; $i<($width*$height)/150; $i++ ) {
         imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
        }
        /* create textbox and add text */
        $textbox = imagettfbbox($font_size, 0, $font, $code) or die('Error in imagettfbbox function');
        $x = ($width - $textbox[4])/2;
        $y = ($height - $textbox[5])/2;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font , $code) or die('Error in imagettftext function');
        /* output captcha image to browser */
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);
        SessionHandler::set('CAPTCHA',$code);
        Pimple::end();
    }
    public function mailpreview() {
        $data = Request::get();
        $view = $data->view;

        $mail = Mail::preview($view,$data->toArray(),$data->container,$data->textonly);
        if ($data->textonly) {
            $this->asText(trim($mail));
        } else {
            echo $mail;
        }
        Pimple::end();
    }
    public function javascript() {
        $this->setContentType('text/javascript; charset=utf-8;');
        $this->setCache(Date::SPAN_MONTH);
        //set_time_limit(0);
        require_once Pimple::instance()->getBaseDir().'lib/Javascript.php';
        $cacheDir = Pimple::instance()->getSiteDir().'cache/js/';
        Dir::ensure($cacheDir);
        $templates = array();
        if (!Request::get('skipLayout',false)) {
            $templates[] = 'application';
        }
        $view = Request::get('view',false);
        if ($view) {
            $templates[] = $view;
        }
        $used = array();
        $isDebug = Settings::get(Settings::DEBUG,false);
        foreach($templates as $template) {
            $cacheFile = $cacheDir.$template.'.js';
            echo "// $template\n";
            if (!$isDebug)
                Dir::ensure(dirname($cacheFile));
            if ($isDebug) {
                $view = new View($template);
                $files = $view->getInternalJsFiles();
                
                //echo("/*FILES:\n\t".implode("\n\t",$files).'*/'.chr(10));
                
                foreach($files as $file) {
                    if(in_array($file,$used) || String::StartsWith($file,"http://") || String::StartsWith($file,"https://")) {
                    	continue;        	
					}
					YUICompressor::Instance()->addFile(YUICompressor::TYPE_JAVASCRIPT, $file);
                    /*$used[] = $file;
                    echo("/*FILE:".basename($file).'*//*'.chr(10).String::normalize(@file_get_contents($file),false));
                    echo(chr(10));*/
                }
                set_time_limit(99999);
                try {
                	YUICompressor::Instance()->setJarFile(Pimple::instance()->getRessource('java' . DIRECTORY_SEPARATOR . 'yuicompressor-2.4.2.jar'));
                	$results = YUICompressor::Instance()->minify();
                }catch(Exception $e) {
                	die($e);
                }
                /* @var $result YUICompressor_Item */
                foreach($results as $result) {
                	echo ' /* COMPRESSED FILE: ' . $result->filename . ' */' . chr(10) . $result->minified . chr(10) . chr(10);
                }
            } else {
                if (!is_file($cacheFile)) {
                    File::truncate($cacheFile);
                    $view = new View($template);
                    $files = $view->getInternalJsFiles();
                    File::append($cacheFile,"/*FILES:\n\t".implode("\n\t",$files).'*/'.chr(10));
                    foreach($files as $file) {
                        if (in_array($file,$used)
                            || String::StartsWith($file,"http://")
                            || String::StartsWith($file,"https://")) continue;
                        $used[] = $file;
                        File::append($cacheFile,"/*FILE:".basename($file).'*/'.chr(10).String::normalize(@file_get_contents($file),false));
                        File::append($cacheFile,chr(10));
                    }
                }
                echo file_get_contents($cacheFile);
            }
        }

        Pimple::end();
    }
    public function css() {
        $this->setContentType('text/css; charset=utf-8;');
        require_once Pimple::instance()->getBaseDir().'lib/Stylesheet.php';
        $cacheDir = Pimple::instance()->getSiteDir().'cache/css/';
        Dir::ensure($cacheDir);
        $templates = array('application');
        $view = Request::get('view',false);
        if ($view) {
            $templates[] = $view;
        }
        $used = array();
        $isDebug = Settings::get(Settings::DEBUG,false);
        foreach($templates as $template) {
            $cacheFile = $cacheDir.$template.'.css';
            echo "/* $template */\n";
            if ($isDebug) {
                $view = new View($template);
                $files = $view->getInternalCssFiles();
                echo("/*FILES:\n\t".implode("\n\t",$files).'*/'.chr(10));
                foreach($files as $file) {
                    if (in_array($file,$used)
                            || String::StartsWith($file,"http://")
                            || String::StartsWith($file,"https://")) continue;
                    $used[] = $file;
                    echo("/*FILE:".basename($file).'*/'.chr(10).Stylesheet::minify($file).chr(10));
                }
            } else {
                Dir::ensure(dirname($cacheFile));
                if (!is_file($cacheFile)) {
                    File::truncate($cacheFile);
                    $view = new View($template);
                    $files = $view->getInternalCssFiles();
                    File::append($cacheFile,"/*FILES:\n\t".implode("\n\t",$files).'*/'.chr(10));
                    foreach($files as $file) {
                        if (in_array($file,$used)
                            || String::StartsWith($file,"http://")
                            || String::StartsWith($file,"https://")) continue;
                        $used[] = $file;
                        File::append($cacheFile,"/*FILE:".basename($file).'*/'.chr(10).Stylesheet::minify($file).chr(10));
                    }
                }
                echo file_get_contents($cacheFile);
            }
        }

        Pimple::end();
    }
}