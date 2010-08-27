<?php
class Url {
    private $url;
	private $protocol;
	private $host;
	private $path;
        private $port;
        private $query;

	/**
	 * Create new Url
	 *
	 * @param string $url
	 * @return Url
	 */
	public function __construct($url) {
		$this->setUrl($url);
	}

	private function parseUrl() {
		$temp = parse_url($this->url);
		$this->url = $this->url;
		$this->protocol = $temp['scheme'];
		$this->host = $temp['host'];
		$this->path = $temp['path'];
        $this->query = $temp['query'];
        $this->port = ($temp['port']) ? $temp['port'] : 80;
        $this->compile();
	}
	public function setUrl($url) {
        if ($url instanceof Url) {
            return $this->setUrl($url->getUrl());
        }
		$this->url = $url;
		$this->parseUrl();
    }

    public function setProtocol($protocol) {
        $this->protocol = $protocol;
        $this->compile();
    }

    public function setHost($host) {
        $this->host = $host;
        $this->compile();
    }

    public function setPath($path) {
        $this->path = $path;
        $this->compile();
    }

    public function setPort($port) {
        $this->port = $port;
        $this->compile();
    }

    public function setQuery($query) {
        $this->query = $query;
        $this->compile();
    }


	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getProtocol() {
		return $this->protocol;
	}

	public function getUrl() {
		return $this->url;
	}
	public function getContents() {
		$hd = curl_init();
		curl_setopt($hd, CURLOPT_URL, $this->getUrl());
		curl_setopt($hd, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($hd, CURLOPT_NOSIGNAL,1);
		curl_setopt($hd, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($hd, CURLOPT_CONNECTTIMEOUT_MS, 3000);
		curl_setopt($hd, CURLOPT_TIMEOUT_MS, 10000);
		$result = curl_exec($hd);
		$code = curl_getinfo($hd,CURLINFO_HTTP_CODE);
		if ($code > 399 || $code < 100) {
            throw new Exception('Request returned an error code',$code);
		}
		return $result;
	}

    public function getPort() {
        return $this->port;
    }

    public function getQuery() {
        return $this->query;
    }
    public function getQueryAsArray() {
        $result = array();
        $nameValArray = self::parseQueryString($this->getQuery());
        foreach($nameValArray as $name=>$value) {

            if (stristr($name,'[')) {
                $parts = explode('[',$name);
                $tmp =& $result;
                $multiParm = false;
                foreach($parts as $part) {
                    $part = trim($part,'[]');
                    if ($part != '') {
                        if (!is_array($tmp[$part]))
                            $tmp[$part] = array();
                        $tmp =& $tmp[$part];
                    } else
                        $multiParm = true;
                }
                $tmp = $value;

            } else {
                $result[$name] = $value;
            }
        }
        return $result;
    }
    /**
     * Resolve given relative Url using this url
     * @param Url $Url
     * @return Url
     */
    public function resolve($Url) {

        $new = clone $this;
        if (!$Url->getHost()) {
            $new->setHost($this->getHost());
            $new->setProtocol($this->getProtocol());
            $new->setPort($this->getPort());
        }
        $new->setQuery($Url->getQuery());
        /* @var $new Url */
        if (String::StartsWith($Url->getPath(),'/')) {
            $new->setPath($Url->getPath());
            return $new;
        } else {
            $file = basename($Url->getPath());
            $path = trim(trim(dirname($this->getPath()),'/').'/'.trim(dirname($Url->getPath()),'/'),'/');
            $parts = explode('/',$path);
            $resolved = array();
            foreach($parts as $part) {
                switch($part) {
                    case '.':
                    case '':
                        //Do nothing...
                        break;
                    case '..':
                        array_pop($resolved);
                        break;
                    default:
                        array_push($resolved,$part);
                        break;
                }
            }
            if (count($resolved) > 0)
                $new->setPath('/'.implode('/',$resolved).'/'.$file);
            else
                $new->setPath('/'.$file);
            return $new;
        }
    }
    private function compile() {
        if ($this->host) {
            if (!trim($this->protocol))
                $this->protocol = 'http';
            $this->url = $this->protocol.'://'.$this->host;
            if (trim($this->port) != '' && trim($this->protocol) != '') {
                if ($this->protocol == 'http' && $this->port != 80)
                    $this->url .=':'.$this->port;
                elseif ($this->protocol == 'https' && $this->port != 443)
                    $this->url .=':'.$this->port;
                elseif ($this->protocol == 'ftp' && $this->port != 21)
                    $this->url .=':'.$this->port;
            }
            if ($this->path) {
                if ($this->path[0] != '/')
                    $this->path = '/'.$this->path;
                $this->url .= $this->path;
            } else {
                $this->url .'/';
            }
            if ($this->query) {
                $this->query = trim($this->query,'?& ');
                $this->url .= '?'.$this->query;
            }
        }
    }
    public function __toString() {
        $this->compile();
        return $this->url;
    }
    /* UTIL METHODS BELOW HERE */


	/**
	 * Convert an array to a HTTP Request string
	 *
	 * @param array $array
	 * @return string
	 */
	public static function Array2GetParms($array) {
		$parms = array();
		foreach ($array as $key=>$value) {
			if(is_int($key)) continue;
			$parms[] = self::flattenUrlParms($key,$value);
		}
		return implode('&',$parms);
	}
    public static function toNameValue($array) {
        $result = array();
        foreach($array as $name=>$value) {
            $result[] = self::flattenUrlParms($name, $value);
        }
        return implode('&',$result);
    }
	public static function flattenUrlParms($key,$value) {
		if (is_array($value)) {
			$parms = array();
			foreach($value as $innerKey=>$innerValue) {
				if(is_array($innerValue))
					$parms[] = self::flattenUrlParms($key."[$innerKey]",$innerValue);
				elseif(is_int($innerKey))
					$parms[] = $key."[]=".$innerValue;
                else
                    $parms[] = $key."[$innerKey]=".$innerValue;
			}
			$key = implode('&',$parms);
		} else
			$key .= '='.String::UrlEncode($value);
		return $key;
	}
	/**
	 * Parse query (&name=value) string into array
	 *
	 * @param string $queryString
	 * @return array
	 */
	public static function parseQueryString($queryString) {
		$queryString = trim($queryString,'?&');
		$parts = explode('&',$queryString);
		$result = array();
        require_once 'String.php';
		foreach($parts as $part) {
			$nameValue = explode('=',$part,2);
            $name = String::UrlDecode($nameValue[0]);
            $value = String::UrlDecode($nameValue[1]);
            if (substr($name,-2) != '[]') {
                if (!$result[$name])
                    $result[$name] = $value;
            } else {
                if (!is_array($result[$name]))
                    $result[$name] = array();
                $result[$name][] = $value;
            }
		}
		return $result;
	}
    public static function isAbsolute($Url) {
        $temp = parse_url($Url);
        return array_key_exists('host', $temp);
    }
    public static function isValid($Url) {
        $temp = parse_url($Url);
        return array_key_exists('host', $temp) && array_key_exists('scheme', $temp);
    }
    public static function makeAbsolute($Url) {
        if (self::isAbsolute($Url)) return $Url;
        $temp = parse_url($Url);
        $parts = explode('/',$temp['path']);
        return Router::getAbsoluteRoot($parts[0], $parts[1]).$Url;
    }
    public static function makeLink($controller,$action,$parms = array()) {
        $url = Dir::concat(BASEURL,String::UrlEncode($controller));
        if ($action)
            $url .= String::UrlEncode($action).'/';

        if (count($parms) > 0) {
            $url .= '?'.self::Array2GetParms($parms);
        }
        return $url;
    }
}