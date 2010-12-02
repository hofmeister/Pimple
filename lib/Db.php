<?php

class DB {
    const HOST = 'db_host';
    const NAME = 'db_name';
    const USER = 'db_user';
    const PASS = 'db_pass';

	private static $debug = false;
    private static $lastResult = null;
    private static $link = null;

	public static function connect($host = null, $user = null, $pass = null, $dbName = null) {
        if (!$host) {
            $host = Settings::get(self::HOST,'localhost');
        }
        if (!$user) {
            $user = Settings::get(self::USER,'root');
        }
        if (!$pass) {
            $pass = Settings::get(self::PASS,'');
        }
        if (!$dbName) {
            $dbName = Settings::get(self::NAME);
        }
		self::$link = mysqli_connect($host, $user, $pass) or die('Host:'.$host);;
		mysqli_select_db(self::$link,$dbName) or die(mysqli_error(self::$link));
        self::q('SET NAMES utf8');
	}
    public static function close() {
        mysqli_close(self::$link);
    }

	private static function processArgs($args) {

		foreach ($args as $i => $arg) {
			$args[$i] = self::value($arg);
		}
		return $args;
	}

	public static function prepareList($array) {
		return implode(',', self::processArgs($array));
	}

	public static function value($arg) {
        if (is_string($arg))
			return '"' . self::escape($arg) . '"';
		else if ($arg === null)
			return 'NULL';
		else if (is_array($arg)) {
            return self::prepareList($arg);
        } elseif (is_int($arg)) {
            return $arg;
        } else {
			return $arg;
        }
	}
    public static function escape($arg) {
        return preg_replace('/([^\%])\%([^\%])/is','$1%%$2',mysqli_real_escape_string(self::$link,$arg));
    }

	private static function _query($sql, $args) {
		$compiled = vsprintf($sql, self::processArgs($args));
		if (self::$debug)
			echo nl2br("\nRUNNING:\n$compiled");
        //echo "$compiled\n<br/>";
        self::freeResult();
		$r = mysqli_query(self::$link,$compiled);
        self::$lastResult = $r;
        if (!$r)
			throw new Exception("SQL:$compiled\nERR:" . mysqli_error(self::$link), 3);
		return $r;
	}
    public static function freeResult() {
        if (self::$lastResult && !is_bool(self::$lastResult)) {
            mysqli_free_result(self::$lastResult);
            while (mysqli_more_results(self::$link)) {
                self::$lastResult = mysqli_use_result(self::$link);
                if (self::$lastResult)
                    mysqli_free_result(self::$lastResult);
                else
                    break;
            }
            self::$lastResult = null;
        }
    }

	public static function compile($sql) {
		$args = func_get_args();
		array_shift($args);
		return vsprintf($sql, self::processArgs($args));
	}

	public static function q($sql) {
		$args = func_get_args();
		array_shift($args);
		$r = self::_query($sql, $args);
		return $r;
	}

	public static function fetchVal($sql) {
		$args = func_get_args();
		array_shift($args);
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }
        $r = self::_query(self::ensureOneRow($sql), $args);
		$row = mysqli_fetch_row($r);
        self::freeResult();
		return $row[0];
	}

	public static function fetchOne($sql) {
		$args = func_get_args();
		array_shift($args);
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }
        $r = self::_query(self::ensureOneRow($sql), $args);
		$row = mysqli_fetch_object($r);
        self::freeResult();
		return $row;
	}
    public static function exists($sql) {
		$args = func_get_args();
		array_shift($args);
		$r = self::_query(self::ensureOneRow($sql), $args);
		$result = mysqli_num_rows($r) > 0;
        self::freeResult();
        return $result;
	}
    private function ensureOneRow($sql) {
        if (!preg_match('/[^A-Z]LIMIT[^A-Z]/i', $sql)) {
            $sql .= ' LIMIT 1';
        }
        return $sql;
    }

	/**
	 * Fetch a single-dimension array of single values
	 *
	 * @param string $sql
	 * @return <type>
	 */
	public static function fetchValues($sql) {
		$args = func_get_args();
		array_shift($args);

		$r = self::_query($sql, $args);
		$result = array();
		while ($row = mysqli_fetch_row($r)) {
			$result[] = $row[0];
		}
        self::freeResult();
		return $result;
	}

	public static function fetchAll($sql) {
		$args = func_get_args();
		array_shift($args);

		$r = self::_query($sql, $args);
		$result = array();
        do {
            while ($row = mysqli_fetch_object($r)) {
                $result[] = $row;
            }
        } while(mysqli_next_result(self::$link));
        self::freeResult();
		return $result;
	}
    /**
     *
     * @param int $rowsPrPage
     * @param string $sql
     * @return DbPageResult
     */
    public static function fetchPage($sqlArr,$rowsPrPage = 30,$page = null) {
        if ($page === null) {
            $page = intval(Request::get('page'));
        }
        $offset = $page*$rowsPrPage;
        $limit = $rowsPrPage;
		$sql = array_shift($sqlArr);
        $sqlCount = array_shift($sqlArr);
        $totalRows = self::fetchVal($sqlCount,$sqlArr);
        $sql .= " LIMIT $offset,$limit";
        
		$r = self::_query($sql,$sqlArr);
        $result = new DbPageResult();
        $result->totalPages = ceil($totalRows / $rowsPrPage);
        $result->page = $page;
        do {
            while ($row = mysqli_fetch_object($r)) {
                $result->rows[] = $row;
            }
        } while(mysqli_next_result(self::$link));
        self::freeResult();
		return $result;
	}
    private static function _call($method,$args) {
        $args = self::processArgs($args);
        $sql = "CALL $method(".implode(',',$args).");";

        self::freeResult();
        $result = array();
		if (mysqli_real_query(self::$link,$sql)) {
            do {
                $r = mysqli_use_result(self::$link);
                if ($r && !is_bool($r)) {
                    while ($row = mysqli_fetch_object($r)) {
                        $result[] = $row;
                    }
                    mysqli_free_result($r);
                }
            } while(mysqli_next_result(self::$link));
        }

		return $result;
    }
    public static function call($method) {
		$args = func_get_args();
		array_shift($args);
        return self::_call($method,$args);
	}
    public static function callFirst($method) {
        $args = func_get_args();
		array_shift($args);
        $result = self::_call($method,$args);
        return current($result);
    }

	public static function prepareQuery($query) {
		$words = explode(' ', $query);
		$query = '';
		foreach ($words as $word) {
			$word = trim($word);
			if (strlen($word) < 3)
				continue;
			$query .= ' +' . preg_replace('/([\.\-\/])/i', '\\\$1', mysqli_real_escape_string(self::$link,$word));
		}
		return new DbVal('\'' . trim($query) . '*\'');
	}

	public static function lastId() {
		return mysqli_insert_id(self::$link);
	}

	public static function rowCount($result) {
		return mysqli_num_rows($result);
	}

}

class DbVal {

	private $val;

	public function __construct($val) {
		$this->val = $val;
	}

	public function __toString() {
		return $this->val;
	}

}
class DbPageResult {
    public $page = 0;
    public $totalPages = 0;
    public $rows = array();
}