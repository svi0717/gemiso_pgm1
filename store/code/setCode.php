<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);
$code = trim($_REQUEST['code']);
$code_nm = trim($_REQUEST['code_nm']);
$code_type_id = trim($_REQUEST['code_type_id']);
$create_date = date('YmdHis');
$user_id = $_SESSION['user']['user_id'];

try
{
	if(empty($action)) throw new Exception('잘못된 동작입니다.');

	//code type
	if($action == 'codeType_regist'){
		$id = $db->queryOne("SELECT MAX(ID) FROM BC_CODE_TYPE");
		++$id;


		$q = "
			INSERT INTO BC_CODE_TYPE
			(ID, CODE, CODE_NM, USE_YN, CREATE_DATE, CREATE_USER)
			VALUES
			('".$id."', upper('".$code."'), '".$code_nm."', 'Y', '".$create_date."', '".$user_id."')
		";

		$r = $db->exec($q);
	}
	else if($action == 'codeType_update'){
		$id = trim($_REQUEST['id']);
		$q = "
			UPDATE	BC_CODE_TYPE
			SET		CODE = '".$code."'
					, CODE_NM = '".$code_nm."'
					, UPDATE_DATE = '".$create_date."'
					, UPDATE_USER = '".$user_id."'
			WHERE	ID = '".$id."'
		";

		$r = $db->exec($q);
	}
	else if($action == 'codeType_remove'){
		$id = trim($_REQUEST['id']);

		$q = "
			DELETE
			FROM	BC_CODE_TYPE
			WHERE	ID = '".$id."'
		";

		$r = $db->exec($q);
	}
	//code
	else if($action == 'code_regist'){
		$id = $db->queryOne("SELECT MAX(ID) FROM BC_CODE");
		++$id;

		$sort = $db->queryOne("SELECT MAX(SORT) FROM BC_CODE WHERE CODE_TYPE_ID = ".$code_type_id);
		++$sort;

		$q = "
			INSERT INTO BC_CODE
			(ID, CODE, CODE_NM, CODE_TYPE_ID, USE_YN, SORT, CREATE_DATE, CREATE_USER)
			VALUES
			('".$id."', upper('".$code."'), '".$code_nm."', '".$code_type_id."', 'Y', '".$sort."', '".$create_date."', '".$user_id."')
		";

		$r = $db->exec($q);
	}
	else if($action == 'code_update'){
		$id = trim($_REQUEST['id']);
		$q = "
			UPDATE	BC_CODE
			SET		CODE = '".$code."'
					, CODE_NM = '".$code_nm."'
					, UPDATE_DATE = '".$create_date."'
					, UPDATE_USER = '".$user_id."'
			WHERE	ID = '".$id."'
		";

		$r = $db->exec($q);
	}
	else if($action == 'code_remove'){
		$id = trim($_REQUEST['id']);

		$q = "
			DELETE
			FROM	BC_CODE
			WHERE	ID = '".$id."'
		";

		$r = $db->exec($q);
	}

	echo json_encode(array(
		'success' => true,
		'id' => $id
	));

	// $db->close();
	$db = null;
}
catch (Exception $e)
{
	echo json_encode(array(
		'success' => false,
		'msg' => $e->getMessage()
	));
}

?>