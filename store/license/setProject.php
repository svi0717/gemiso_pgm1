<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);

$id = trim($_REQUEST['id']);
$cust_nm = trim($_REQUEST['cust_nm']);
$proj_nm = trim($_REQUEST['proj_nm']);
$cust_user_nm = trim($_REQUEST['cust_user_nm']);
$phone = trim($_REQUEST['phone']);
$email = trim($_REQUEST['email']);
$description = trim($_REQUEST['description']);
$create_date = date('YmdHis');
$member_id = $_SESSION['user']['user_id'];

try
{
	if(empty($action)) throw new Exception('잘못된 동작입니다.');

	if($action == 'regist'){
		//regist
		if(empty($cust_nm)) throw new Exception('고객사를 입력해주세요.');

		$last_id = $db->queryOne("SELECT MAX(PROJ_ID) FROM BC_PROJ_MST");
		$id = (int)substr($last_id, 1);
		$id = 'P'.str_pad(++$id, 5, "0", STR_PAD_LEFT);

		$q = "
			INSERT INTO BC_PROJ_MST
			(PROJ_ID, CUST_NM, PROJ_NM, CUST_USER_NM, PHONE, EMAIL, DESCRIPTION, CREATE_DATE, CREATE_USER, DEL_YN, USE_YN)
			VALUES
			('".$id."', '".$cust_nm."', '".$proj_nm."', '".$cust_user_nm."', '".$phone."', '".$email."', '".$description."', '".$create_date."', '".$member_id."', 'N', 'Y')
		";

		$r = $db->exec($q);
	}
	else if($action == 'update'){
		//update
		if(empty($cust_nm)) throw new Exception('고객사를 입력해주세요.');
		$q = "
			UPDATE	BC_PROJ_MST
			SET		CUST_NM = '".$cust_nm."'
					, PROJ_NM = '".$proj_nm."'
					, CUST_USER_NM = '".$cust_user_nm."'
					, PHONE = '".$phone."'
					, EMAIL = '".$email."'
					, DESCRIPTION = '".$description."'
					, UPDATE_DATE = '".$create_date."'
					, UPDATE_USER = '".$member_id."'
			WHERE	PROJ_ID = '".$id."'
		";

		$r = $db->exec($q);
	}
	else if($action == 'remove'){
		$id = trim($_REQUEST['id']);

		$q = "
			UPDATE	BC_PROJ_MST
			SET		DEL_YN = 'Y'
					, DELETE_DATE = '".$create_date."'
					, DELETE_USER = '".$member_id."'
			WHERE	PROJ_ID = '".$id."'
		";

		$r = $db->exec($q);
	}
	else if($action == 'use'){
		$id = trim($_REQUEST['id']);

		$q = "
			UPDATE	BC_PROJ_MST
			SET		USE_YN = 'Y'
			WHERE	PROJ_ID = '".$id."'
		";

		$r = $db->exec($q);
	}
	else if($action == 'unuse'){
		$id = trim($_REQUEST['id']);

		$q = "
			UPDATE	BC_PROJ_MST
			SET		USE_YN = 'N'
			WHERE	PROJ_ID = '".$id."'
		";

		$r = $db->exec($q);
	}

	echo json_encode(array(
		'success' => true,
		'q' => $q,
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