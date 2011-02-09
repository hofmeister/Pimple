<?php
/**
 * @author New Dawn Technologies
 * @version 1.0
 * @license BSD
 * @package Basic
 */

class String {
	public static function htmlEncode($string) {
        if (!self::isValidString($string))
            throw new InvalidArgumentException(sprintf('String::htmlEncode() requires argument 1 to be a string. %s given',gettype($string)),E_USER_ERROR);
		return self::normalize(str_replace('"','&quot;',(htmlentities(str_replace('&amp;','&',$string),null))));
	}

	public static function Strip( $string ) {
		return preg_replace('/[^a-z0-9]/', '',self::normalize($string));
	}
    public static function StripTags($string,$allowedTags = '') {
        $tagsAr = explode(',',str_ireplace(array('<','>'),'',$allowedTags));
        preg_match_all('/<([\!A-Z0-9\-\_]*)[^>]*>/is',$string,$matches);
        $tagsProcessed = array();
        foreach($matches[1] as $tag) {
            if (!in_array($tag,$tagsAr) && !in_array($tag,$tagsProcessed)) {
                $tagsProcessed[] = $tag;

                $string = preg_replace('/<'.$tag.'[^>]*>[^<]*<\/'.$tag.'>/is',' ', $string);
                $string = preg_replace('/<'.$tag.'[^>]*>[^<]*</is',' <', $string);
                $string = preg_replace('/>[^>]*<\/'.$tag.'>/is','> ', $string);
                $string = preg_replace('/<'.$tag.'[^>]*\/>/is',' ', $string);
                $string = preg_replace('/<'.$tag.'[^>]*>/is',' ', $string);
                $string = preg_replace('/<\/'.$tag.'>/is',' ', $string);
                //'/<html[^\/>]*>[^(<[A-Z0-9\-\_]*)]*/is'
                $string = preg_replace('/[ \t]{2,}/is','', $string);
                $string = str_replace("\r","", $string);
                $string = str_replace("\n\n","\n", $string);

            }
        }
        return self::normalize($string);
    }
	public static function JsEncode($string,$quote = '"') {
        return $quote.str_replace('script>','scri" + "pt>',

                        str_replace("\n",$quote.chr(10).'+ '.$quote.'\n',
                            addslashes(
                                preg_replace('/(\r|\t)/','',trim($string))))).$quote;
    }
    public static function truncate($string, $maxLength = 75, $includeDots = false){
    	$output = $string;
    	$strLength = strlen($output);
    	if($strLength > 0 && $strLength > $maxLength) {
    		$output = substr($output, 0, $maxLength);
    		if ($includeDots)
    			$output .= '...';
    	}
    	return $output;
    }
	public static function SubWord( $string, $maxWords = 10, $includeDots = false ) {
		if(!empty($string)) {
			$words = explode(' ', $string);
			$output = '';

			foreach($words as $i=>$word) {
				$output .= $word;

				// If it's the maxword, then stop.
				if($i == ($maxWords-1))
					break;

				// We don't want any space in the last word.
				if($i < count($words))
					$output .= ' ';
			}

			// We only want dots, if its not the last word in the sentence.
			if($includeDots && count($words) > $maxWords)
				$output .= '...';

			return $output;
		}
	}
    public static function SubSentence($string,$maxSentences = 1) {
        $sentences = explode('. ',$string);
        $result = '';
        for($i= 0;$i < $maxSentences;$i++)
            $result .= $sentences[$i].'. ';
        return $result;
    }
	public static function EndsWith($string,$ending) {
        return (substr($string,strlen($ending) * -1) == $ending);
    }
    public static function StartsWith($string,$start) {
        return (substr($string,0,strlen($start)) == $start);
    }
	public static function UrlEncode($url) {
		$url = rawurlencode($url);
        return $url;
	}
	public static function UrlDecode($url) {
        $url = rawurldecode($url);
        return $url;
	}
	public static function toLower($string) {
                if (!self::isValidString($string))
                    throw new InvalidArgumentException(sprintf('String::toLower() requires argument 1 to be a string. %s given',gettype($string)),E_USER_ERROR);
		return mb_strtolower($string,Site::GetCharset());
	}
	public static function ucWords($string) {
                if (!self::isValidString($string))
                    throw new InvalidArgumentException(sprintf('String::toLower() requires argument 1 to be a string. %s given',gettype($string)),E_USER_ERROR);
		return mb_convert_case($string,MB_CASE_TITLE,Site::GetCharset());
	}
	public static function toUpper($string) {
                if (!self::isValidString($string))
                    throw new InvalidArgumentException(sprintf('String::toUpper() requires argument 1 to be a string. %s given',gettype($string)),E_USER_ERROR);
		return mb_strtoupper($string,Site::GetCharset());
	}
        public static function isValidString($string) {
            try {
                $test = (string) $string;
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
	public static function Equals($str1,$str2) {
		$length = strlen($str1);
		if ($length !== strlen($str2))
			return false;
		for($i = 0; $i < $length; $i++) {
			if (ord($str1[$i]) != ord($str2[$i]))
				return false;
		}
		return true;
	}
	/**
	 * Compare string and return a procentage of similarity
	 * 0 is the lowest - 1 is max.
	 *
	 * @param string $str1
	 * @param string $str2
	 * @return float
	 */
	public static function Compare($str1,$str2) {
		$length = strlen($str1);
		$str2length = strlen($str2);
		$directHit = 0;
		$misses = 0;
		$str2pos = 0;
		$str2limit = $str2length;
		$str2matches = array();

		for($str1pos = 0; $str1pos < $length; $str1pos++) {
			$didHit = false;

			for($str2pos; $str2pos < $str2limit; $str2pos++) {

				if ($str1[$str1pos] == $str2[$str2pos]) {
					$str2matches[] = $str2pos;
					$directHit++;
					$didHit = true;
					$str2pos++;
					break;
				} else {
					$misses++;
				}
			}
			if (!$didHit) {
				$str2pos = max(0,($str2pos - 1));
			}
		}
		return ($directHit / max($length,$str2length));
	}
	public static function NamesEqual($name1,$name2) {
		//Check if 2 names are equal... not case-sensitive and disregarding letter apostrophe...
		$disregard = array(
			'å' => 'aa',
			'æ' => 'ae',
			'ø' => 'o',
			'ó' => 'o',
			'ò' => 'o',
			'ô' => 'o',
			'ö' => 'o'
			,
			'ú' => 'u',
			'Ù' => 'u',
			'û' => 'u',
			'ü' => 'u',

			'é' => 'e',
			'è' => 'e',
			'ê' => 'e',
			'ë' => 'e',

			'µ' => 'u',
			'ñ' => 'n',
			'ã'	=> 'a',
			'á' => 'a',
			'à' => 'a',
			'â' => 'a',
			'ä' => 'a',

			'í' => 'i',
			'ì' => 'i',
			'î' => 'i',
			'ï' => 'i',

			'ç' => 'c',

			' ' => '',
			'	' => '',
			chr(10) => '',
			chr(13) => ''
		);
		$name1 = trim(self::toLower($name1));
		$name2 = trim(self::toLower($name2));
		foreach($disregard as $fC=>$tC) {
			$name1 = str_replace($fC,$tC,$name1);
			$name2 = str_replace($fC,$tC,$name2);
		}
		return ($name1 == $name2);
	}
	/**
	 * Make string max length (and append ... if cutting is performed)
	 *
	 * @param string $string
	 * @param int $maxlength
	 * @return string
	 */
	public static function Shorten($string,$maxlength) {
		if (strlen($string) > $maxlength) {
			return trim(mb_substr($string,0,$maxlength - 3)).'...';
		}
		return $string;
	}
	public static function UTF8Encode($string) {
		if ($string == NULL)
			return $string;
		if (!self::IsUTF8Encoded($string))
			$string = utf8_encode($string);
		return $string;
	}
    public static function IsUTF8Encoded($string) {
        $array = array('æ','ø','å','é','è','ê','ë','ú','ù','û','ü','ó','ò','ô','ö','´','`','^','¨');
        foreach($array as $chr) {
			if (strstr($string,$chr) || strstr($string,mb_strtoupper($chr,'UTF-8')))
				return true;
            if (strstr($string,utf8_decode($chr)) || strstr($string,utf8_decode(mb_strtoupper($chr,'UTF-8'))))
                return false;
		}
        return true;
    }
	public static function Diff2Pattern($str1,$str2) {
		$str1 = trim(self::toLower($str1));
		$str2 = trim(self::toLower($str2));
		$length = strlen($str1);
		$str2length = strlen($str2);
		$directHit = 0;
		$misses = 0;
		$str2pos = 0;
		$str2limit = $str2length;
		$str2matches = array();
		$pattern = '';
		$machtes = array();

		for($str1pos = 0; $str1pos < $length; $str1pos++) {
			$didHit = false;

			for($str2pos; $str2pos < $str2limit; $str2pos++) {

				if ($str1[$str1pos] == $str2[$str2pos]) {
					$str2matches[] = $str2pos;
					$pattern .= '*';
					$matched .= $str2[$str2pos];
					$str2pos++;
					break;
				} else {
					if (trim($matched))
						$machtes[] = $matched;
					$matched = "";
					$misses++;

                    $str2matches[] = $str2pos;
                    if (preg_match('/[A-Å0-9]/i',$str1[$str1pos]))
                        $pattern .= $str2[$str2pos];
                    else
                        $pattern .= 'X';

				}
			}

		}
		if (trim($matched))
			$machtes[] = $matched;

		while(stristr($pattern,'**'))
			$pattern = str_replace('**','*',$pattern);

		return $pattern;
	}
    public static function normalize($string,$html = true) {
        //Replace odd white spaces with normal white space...
        $str = str_replace(chr(194).chr(160),chr(32),$string);
        $str = str_replace("\t","    ",$str);
        if (strstr($str,chr(13))) {
            if (strstr($str,chr(10)))
                $str = str_replace (chr(13),'',$str);
            else
                $str = str_replace (chr(13),chr(10),$str);
        }
        if ($html)
            $str = str_replace(chr(10),'<br/>',$str);
        return $str;
    }
    public static function trim($str,$chars = null) {
        $str = self::normalize($str,false);
        if ($chars) {
            return trim($str,$chars);
        }
        return trim($str);
    }
    public static function alphanum($string) {
        return preg_replace('/[^A-Za-z0-9_]/','',$string);
    }
    public static function isAlphaNum($string) {
        return !preg_match('/[^A-Za-z0-9_]/','',$string);
    }
    public static function GUID() {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
        return $uuid;
    }
    public static function isHtml($string) {
        return preg_match('/<(img|p|pre|code|div|label|li|ul|ol|table|tbody|tr|td|th|thead|tfoot|font|span|em|strong|b|i|head|body|title|meta|style|script|html|\!DOCTYPE)[^>]*>/i',$html);
    }
}

