<?php
/**
 * @author New Dawn Technologies
 * @version 1.0
 * @license BSD
 * @package Basic
 */
class File {
	protected $path;

	const TYPE_FILE 	= "FILE";
	const TYPE_IMAGE 	= "IMAGE";
	const TYPE_AUDIO 	= "AUDIO";
	const TYPE_VIDEO 	= "VIDEO";
	const TYPE_DOCUMENT	= "DOCUMENT";

	private static $mineTypes = array(
				'css' => 'text/css',
				'gif' => 'image/gif',
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png' => 'image/png',
				'bmp' => 'image/bmp',
				'js' => 'text/javascript'
				);
	public function __construct($path) {
		$this->path = $path;

	}
    /**
     * Returns file extension in lower case
     *
     * @param <type> $path
     * @return string
     */
	public static function getExtension($path) {
		return strtolower(end(explode('.',$path)));
	}
	public static function getMimeType($path) {
		return self::$mineTypes[self::getExtension($path)];
	}
	public static function getType($path) {
		$ext = self::getExtension($path);
		switch (true) {
			case in_array($ext,self::getAllowedAudioExtensions()):
				return self::TYPE_AUDIO;
				break;
			case in_array($ext,self::getAllowedDocumentExtensions()):
				return self::TYPE_DOCUMENT;
				break;
			case in_array($ext,self::getAllowedImageExtensions()):
				return self::TYPE_IMAGE;
				break;
			case in_array($ext,self::getAllowedMovieExtensions()):
				return self::TYPE_VIDEO;
				break;

		}
		return self::TYPE_FILE;
	}
	public function display($withHeader = false) {
		if ($withHeader) {
			header('Content-type: '.self::getMimeType($this->path),TRUE);
		}
		echo $this->getBinary($this->path);
		exit;
	}
	public function getPath() {
		return $this->path;
	}
	public function getBinary() {
		/*$fp = fopen($this->path,'rb');
		while(!feof($fp));
			$binary .= fread($fp,1024);
		fclose($fp);
		*/
		return file_get_contents($this->path);
	}
	public static function addBeforeExtension($file,$addon) {
		$ext = strtolower(end(explode('.',$file)));
		$fileWithoutExt = substr($file,0,strlen($file) - (strlen($ext) + 1));
		return $fileWithoutExt.$addon.'.'.$ext;
	}
	/**
	 * Create new local file
	 *
	 * @param string $fileName
	 * @param string $filepath
	 * @param int $FolderKey
	 * @return Model_Table_Row
	 */
	public static function createLocalFile($type, $fileName,$filepath,$FolderKey) {
		$File = new Basic_Model_File();
		$FileRow = $File->createRow();
		$FileRow->SiteID	= Site::ID();
		$FileRow->Filename 	= $fileName;
		$FileRow->Type 		= $type;
		$FileRow->Data 		= file_get_contents($filepath);
		$FileRow->DataType	= 'LOCAL';
		$FileRow->FolderKey = $FolderKey;
		$FileRow->FileKey 	= $FileRow->save();
		return $FileRow;
	}
	public static function getAllowedImageExtensions() {
		//'bmp' should maybe be supported - but imagecreatefromstring fails - need fix!
		return array('jpg','jpeg','gif','png');
	}
	public static function getAllowedDocumentExtensions() {
		return array('pdf','doc','docx','xsl','xslx','rtf','txt');
	}
	public static function getAllowedFileExtensions() {
		return array('zip','rar','gz','tar');
	}
	public static function getAllowedMovieExtensions() {
		return array('mov','avi','rmv','wmv');
	}
	public static function getAllowedAudioExtensions() {
		return array('mp3','wav','rma','wma');
	}
	public static function getAllowedExtensions() {
		return array_merge(
						self::getAllowedAudioExtensions(),
						self::getAllowedDocumentExtensions(),
						self::getAllowedFileExtensions(),
						self::getAllowedImageExtensions(),
						self::getAllowedMovieExtensions());
	}
	public static function isAllowedFile($path,$extensions = null) {
		$ext = strtolower(self::getExtension($path));
		if (!$extensions)
			$extensions = self::getAllowedExtensions();
		return in_array($ext,$extensions);
	}
	public static function exists($file) {
		$f = @fopen($file,'r',true);
		$exists = ($f) ? TRUE : FALSE;
		if ($exists)
			fclose($f);
		return $exists;
	}
	public static function read($file) {
		$f = @fopen($file,'r',true);
		if ($f) {
			$content = '';
			while(!feof($f))
				$content .= fread($f,1024);
			fclose($f);
		}

		return $content;
	}
    public static function readBinary($file) {
		$f = @fopen($file,'rb',true);
		if ($f) {
			$content = '';
			while(!feof($f))
				$content .= fread($f,1024);
			fclose($f);
		}

		return $content;
	}
    public static function truncate($file) {
        file_put_contents($file,'');
    }
    public static function append($file,$data) {
        $fp = fopen($file,'a');
        if ($fp) {
            fwrite($fp,$data);
            fclose($fp);
        } else {
            throw new Exception('Could not create or append to file: '.$file);
        }
    }

    public static function string2Filename($string) {
        return preg_replace('/[^0-9A-Z_\.\-]/is','', $string);
    }
}

