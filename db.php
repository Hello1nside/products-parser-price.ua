<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class DB {
	public static $sql;

	public static function Connect() {
		self::$sql = new mysqli('localhost', 'wp_parser');
		self::$sql->set_charset('utf8');
	}

	public static function RS($query) {
		$rs = self::$sql->query($query);
		self::check_error($query);
		if($rs && $rs->num_rows != 0 ) {
			return $rs;
		} 
		return null;
	}

	public static function Execute($query) {
		self::$sql->query($query);
		self::check_error($query);
	}

	public static function DQ($value) {
		return self::$sql->real_escape_string($value);
	}

	private static function check_error($query) {
		if(self::$sql->errno > 0) {
			trigger_error("MySQL error " . self::$sql->error . "\r\n" . $query . "\r\n");
		}
	}
}

DB::Connect();