<?php
/**
 * Class representing binary image - provides several methods for manipulating images
 */
class Image {
	protected static $sizes = array();
	protected $originalBinary;
	protected $filename;
    protected $width;
    protected $height;
    protected $editMode;
    protected $imgRes;
    protected $font;
    protected $transparencySet = false;

	public function __construct($filename = null) {
		$this->filename = $filename;
        if ($this->filename != null && File::Exists($this->filename)) {
            list($this->width,$this->height) = @getimagesize($this->filename);
            $this->setOriginalBinary(@File::ReadBinary($this->filename));
        }

	}
	public function setOriginalBinary($originalBinary) {
		$this->originalBinary = $originalBinary;
	}
    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }
	public function getResizedExact($dstWidth,$dstHeight,$align = 'center',$valign = 'middle') {
		$src = imagecreatefromstring($this->originalBinary);
		if (!$src)
			throw new Exception(T("Could not read file: %s",File::getExtension($this->filename)));
		$srcWidth  	= imagesx($src);
		$srcHeight 	= imagesy($src);
		$widthRatio = $srcWidth  / $dstWidth;
		$heightRatio = $srcHeight / $dstHeight;
		
		if ($widthRatio < $heightRatio) {
			$tmp = ($dstHeight * $widthRatio);
			switch(strtolower($valign)) {
                case 'top':
                    //Center
                    $srcY = 0;
                    $srcHeight = $tmp;
                    $srcX = 0;
                    break;
                case 'middle':
                    //Center
                    $srcY = ($srcHeight - $tmp) / 2;
                    $srcHeight = $tmp;
                    $srcX = 0;
                    break;
                case 'bottom':
                    //Center
                    $srcY = ($srcHeight - $tmp);
                    $srcHeight = $tmp;
                    $srcX = 0;
                    break;
            }
		} else {
			$tmp = ($dstWidth * $heightRatio);
            switch(strtolower($align)) {
                case 'left':
                    //Center
                    $srcX = 0;
                    $srcWidth = $tmp;
                    $srcY = 0;
                    break;
                case 'center':
                    //Center
                    $srcX = ($srcWidth - $tmp) / 2;
                    $srcWidth = $tmp;
                    $srcY = 0;
                    break;
                case 'right':
                    //Center
                    $srcX = ($srcWidth - $tmp);
                    $srcWidth = $tmp;
                    $srcY = 0;
                    break;
            }
			
		}
		
		$dst = imagecreatetruecolor($dstWidth,$dstHeight);
		//imagesavealpha($dst,TRUE);
		
		imagecopyresampled($dst,$src,0,0,$srcX,$srcY,$dstWidth,$dstHeight,$srcWidth,$srcHeight);
		
		return $this->save2string($dst);
	}
	public function getResizedMaxSize($dstWidth,$dstHeight) {
		$src = @imagecreatefromstring($this->originalBinary);
		if (!$src)
			return false;
		$srcWidth  	= imagesx($src);
		$srcHeight 	= imagesy($src);
		$widthRatio = $srcWidth  / $dstWidth;
		$heightRatio = $srcHeight / $dstHeight;
		if ($widthRatio < $heightRatio) {
			//src height is higher compared to destation - make height fit and adjust width accordingly
			$height = $dstHeight;
			$width 	= $srcWidth / $heightRatio;			
		} else {
			$width 	= $dstWidth;
			$height	= $srcHeight / $widthRatio;
		}
		
		$dst = imagecreatetruecolor($width,$height);
		//imagesavealpha($dst,TRUE);
		imagecopyresampled($dst,$src,0,0,0,0,$width,$height,$srcWidth,$srcHeight);
		
		return $this->save2string($dst);
	}
	
	/**
	 * @return unknown
	 */
	public function getFilename() {
		return $this->filename;
	}
	public function getMimeType() {
        switch (File::getExtension($this->filename)) {
			case 'jpeg':
			case 'jpg':
				return image_type_to_mime_type(IMAGETYPE_JPEG);
				break;
			case 'gif':
				return image_type_to_mime_type(IMAGETYPE_GIF);
				break;
			case 'bmp':
				return image_type_to_mime_type(IMAGETYPE_BMP);
				break;	
			case 'png':
				return image_type_to_mime_type(IMAGETYPE_PNG);
				break;
			default:
				throw new Exception(T('Unknown filetype: %s',$this->filename));
		}
	}
	
	/**
	 * @return unknown
	 */
	public function getOriginalBinary() {
		return $this->originalBinary;
	}
	
	
	/**
	 * @param unknown_type $filename
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}
	
	/**
	 * @param unknown_type $sizes
	 */
	public function setSize($width,$height) {
		$this->width = $width;
        $this->height = $height;
	}
    /* EDIT METHODS */

    public function startEditMode() {
        if ($this->editMode)
            return false;
        $this->editMode = true;
        if (is_file($this->filename)) {
            $this->imgRes = $this->getImageRessource($this->filename);
            list($this->width,$this->height) = @getimagesize($this->filename);
        } elseif ($this->width && $this->height) {
            $this->imgRes = imagecreatetruecolor($this->width,$this->height);
            $this->fillImage("0000000");
        }
        
        return true;
    }
    public function endEditMode() {
        $this->editMode = false;
        $this->imgRes = null;
    }
    public function save2string($dst = null,$filename = null) {
        ob_start();
		$this->save($dst,$filename);
		$fileContents = ob_get_contents();
		ob_end_clean();
        return $fileContents;
    }
    public function save($dst = null,$filename = null) {
        if (!$dst)
            $dst = $this->imgRes;

        switch (File::getExtension($this->filename)) {
			case 'jpeg':
			case 'jpg':
				imagejpeg($dst,$filename);
				break;
			case 'gif':
                if (!$this->transparencySet)
                    imagecolortransparent($dst,imagecolorat($dst,0,0));
				imagegif($dst,$filename);
				break;
			case 'bmp':
				imagewbmp($dst,$filename);
				break;
			case 'png':
                imagealphablending($dst, true);
                imagesavealpha($dst, true);
				imagepng($dst,$filename);
				break;
			default:
				throw new Exception(T("Unknown filetype: %s",File::getExtension($this->filename)));
		}
        imagedestroy($dst);
    }
    public function addImageAtPoint($x,$y,$image,$align = 'left',$valign = 'top') {
        $this->startEditMode();
        $newImg = $this->getImageRessource($image);
        switch($align) {
            case 'center':
                $x -= round(imagesx($newImg) / 2);
                break;
            case 'right':
                $x -= imagesx($newImg);
                break;
        }
        switch($valign) {
            case 'middle':
                $y -= round(imagesy($newImg) / 2);
                break;
            case 'bottom':
                $y -= imagesy($newImg);
                break;
        }
        imagecopy($this->imgRes,$newImg, $x,$y,0,0,imagesx($newImg), imagesy($newImg));
    }
    public function setTransparentColor($color) {
        $this->startEditMode();
        $this->transparencySet = true;
        imagecolortransparent($this->imgRes,$this->getColor($color));
    }
    public function fillImage($color,$x = 0,$y = 0) {
        $this->startEditMode();
        imagefill($this->imgRes,$x,$y,$this->getColor($color));
    }
    public function drawRectangle($color,$x,$y,$width,$height) {
        $this->startEditMode();
        imagerectangle($this->imgRes, $x, $y, $x + $width, $y + $height, $this->getColor($color));
    }
    public function drawLine($startX,$startY,$endX,$endY,$color = '000000') {
        $this->startEditMode();
        imageline($this->imgRes, $startX, $startY, $endX,$endY, $this->getColor($color));
    }
    public function drawEllipse($color,$x,$y,$width,$height) {
        $this->startEditMode();
        imageellipse($this->imgRes, $x, $y,$width,$height,$this->getColor($color));
    }
    public function setFont($fontFile,$fontSize,$angle = 0) {
        $this->font = array(Fonts::getInstance()->getFont($fontFile),$fontSize,$angle);
    }
    public function writeText($text,$x,$y,$color = '000000') {
        if (!$this->font)
            throw new Exception('No font was set');
        imagettftext($this->imgRes, $this->font[1], $this->font[2], $x, $y, $this->getColor($color), $this->font[0], $text);
    }
    public function getColor($color,$alpha = 1) {
        $this->startEditMode();
        $color = trim($color,'#');
        $parts = array(
            hexdec(substr($color,0,2)),
            hexdec(substr($color,2,2)),
            hexdec(substr($color,4,2))
        );
        if (strlen($color) > 6)
            $alpha = floatval(substr($color,6));
        
        $alpha = 127 - round($alpha * 127);
    
        if ($alpha)
            return imagecolorallocatealpha($this->imgRes, $parts[0],$parts[1],$parts[2],$alpha);
        else
            return imagecolorallocate($this->imgRes, $parts[0],$parts[1],$parts[2]);
    }

    private function getImageRessource($image) {
        if(is_resource($image)) {
            return $image;
        } elseif (is_file($image)) {
            switch (File::getExtension($image)) {
                case 'jpeg':
                case 'jpg':
                    return imagecreatefromjpeg($image);
                    break;
                case 'gif':
                    return imagecreatefromgif($image);
                    break;
                case 'bmp':
                    return imagecreatefromwbmp($image);
                    break;
                case 'png':
                    $img = imagecreatefrompng($image);
                    imagesavealpha($img, true);
                    imagealphablending($img, true);
                    return $img;
                    break;
                default:
                    throw new Exception(T("Ukendt filtype: %s",File::getExtension($image)));
            }
        } elseif(is_string($image)) {
            return imagecreatefromstring($data);
        }
    }
	
}

