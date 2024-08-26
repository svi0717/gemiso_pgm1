<?php

/*
 DB CLASS
  - ORACLE
  - ODBC
  - PostSql
  - Mysql

  공통 데이터 베이스 클래스

  - 생각할것들 ERROR 발생시..
  - 코드로 줄것인가.. 쿼리로 직접 줄것인가 ??

*/

class CommonDatabase
{
	/*
		각종 DB 라이브러리 디렉토리 위치정보
	*/
	public static $lib_database_dir = "/Database/";

	/*
		DB Libery dir or filename
		디비 연결 라이브러리 파일 배열
		oracle Db 연결시 ->  위 lib_database_dir 디렉토리에 위치한 CLASS 를 호출한다.
	*/

	public static $db_mobule_dir = Array(
					"oracle" => "DBOracle.class.php",
					"odbc"   => "DBOdbc.class.php",
					"postgresql" => "DBPostgresql.class.php"
	);

	/*
		연결 DB Object;
	*/
	public static $db = null;

	public function __construct($db_case , $user, $password, $db_string, $charset = 'AL32UTF8')
	{
		if($db_case)
		{
			$db_case = strtolower($db_case);

			if(self::$db_mobule_dir[$db_case])
			{
				//echo self::$lib_database_dir.self::$db_mobule_dir[$db_case];
				require_once(dirname(__FILE__).self::$lib_database_dir.self::$db_mobule_dir[$db_case]);
				self::$db = new Database($user, $password , $db_string, $charset);				
			}
		}
	}

	function __destruct(){

	}

	function get_connect_db()
	{
		if(self::$db)
		{
			return true;
		}
		else return false;
	}



	function queryOne($query)
	{
		try{
			if(self::$db)
			{
				 return self::$db->queryOne($query);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}


	function queryRow($query)
	{
		try{
			if(self::$db)
			{
				 return self::$db->queryRow($query);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}


	function queryAll($query)
	{
		try{
			if(self::$db)
			{
				 return self::$db->queryAll($query);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}


	function escape($query)
	{
		try{
			if(self::$db)
			{
				 return self::$db->escape($query);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function exec($query)
	{
		try{
			if(self::$db)
			{
				 return self::$db->exec($query);
			}
		}catch(Exception $e) {
			$msg = $e->getMessage().'<br/>query : '.$query;
			throw new Exception($msg);
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}


	function setTransaction($query)
	{
		try{
			if(self::$db)
			{
				 return self::$db->setTransaction($query);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function setLoadNEWCLOB($bool = false)
	{
		try{
			if(self::$db)
			{
				 return self::$db->setLoadNEWCLOB($bool);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function clob_exec($query,$place_holder,$var,$length=null){
		try{
			if(self::$db)
			{
				 return self::$db->clob_exec($query,$place_holder,$var,$length=null);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}


	function setLimit($limit,$start)
	{
		try{
			if(self::$db)
			{
				 return self::$db->setLimit($limit,$start);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function UpdateQuery($table, $insert_field_array, $insert_value_array, $where)
	{
		try{
			if(self::$db)
			{
				 return self::$db->UpdateQuery($table, $insert_field_array, $insert_value_array, $where);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function InsertQuery($table ,$insert_field_array, $insert_value_array)
	{
		try{
			if(self::$db)
			{
				 return self::$db->InsertQuery($table ,$insert_field_array, $insert_value_array);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function insert($table ,$data, $exec='exec')
	{
		try{
			if(self::$db)
			{
				 return self::$db->insert($table ,$data, $exec);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function update($table ,$data, $where, $exec='exec')
	{
		try{
			if(self::$db)
			{
				 return self::$db->update($table ,$data, $where, $exec);
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}

	function affectedRows()
	{
		try{
			if(self::$db)
			{
				 return self::$db->affectedRows();
			}
		}catch(Exception $e) {
			throw new Exception($e->getMessage());
			//return 'ERROR Message: ' .$e->getMessage();
		}
	}
}

?>