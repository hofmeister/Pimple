<?php

class DB {

	private static $debug = false;

	public static function connect($host, $user, $pass, $dbName) {
		mysql_connect($host, $user, $pass) or die(mysql_error());
		mysql_select_db($dbName) or die(mysql_error());
        self::q('SET NAMES utf8');
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
			return '"' . mysql_real_escape_string(trim($arg)) . '"';
		else if ($arg === null)
			return 'NULL';
		else
			return $arg;
	}

	private static function _query($sql, $args) {
		$compiled = vsprintf($sql, self::processArgs($args));
		if (self::$debug)
			echo nl2br("\nRUNNING:\n$compiled");
		$r = mysql_query($compiled);
		if (!$r)
			throw new Exception("SQL:$compiled\nERR:" . mysql_error(), 3);
		return $r;
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
        $r = self::_query(self::ensureOneRow($sql), $args);
		$row = mysql_fetch_row($r);
		return $row[0];
	}

	public static function fetchOne($sql) {
		$args = func_get_args();
		array_shift($args);
        $r = self::_query(self::ensureOneRow($sql), $args);
		$row = mysql_fetch_object($r);
		return $row;
	}
    public static function exists($sql) {
		$args = func_get_args();
		array_shift($args);
		$r = self::_query(self::ensureOneRow($sql), $args);
		return mysql_num_rows($r) > 0;
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
		while ($row = mysql_fetch_row($r)) {
			$result[] = $row[0];
		}
		return $result;
	}

	public static function fetchAll($sql) {
		$args = func_get_args();
		array_shift($args);

		$r = self::_query($sql, $args);
		$result = array();
		while ($row = mysql_fetch_object($r)) {
			$result[] = $row;
		}
		return $result;
	}

	public static function prepareQuery($query) {
		$words = explode(' ', $query);
		$query = '';
		foreach ($words as $word) {
			$word = trim($word);
			if (strlen($word) < 3)
				continue;
			$query .= ' +' . preg_replace('/([\.\-\/])/i', '\\\$1', mysql_real_escape_string($word));
		}
		return new DbVal('\'' . trim($query) . '*\'');
	}

	public static function lastId() {
		return mysql_insert_id();
	}

	public static function rowCount($result) {
		return mysql_num_rows($result);
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