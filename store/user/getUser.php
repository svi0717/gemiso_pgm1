<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);

$sort = trim($_REQUEST['sort']);
$dir = trim($_REQUEST['dir']);
$sort_q = 'ORDER BY JOB_GRADE DESC';

if($sort == 'job_grade_nm'){
	$sort = 'JOB_GRADE';
}

if(!empty($sort)){
	$sort_q = 'ORDER BY '.$sort.' '.$dir;
}

try
{
	if($action == 'allUsers'){
		$q = "
			SELECT	LOGIN_ID
					, LOGIN_PWD
					, FIRSTNAME
					, LASTNAME
					, USER_LEVEL
					, JOB_GRADE
					, PHONE
					, EMAIL
					, CREATE_DATE
					, UPDATE_DATE
			FROM	BC_USER
			WHERE	USE_YN = 'Y'
		";

		$rows = $db->queryAll($q);

		echo json_encode(array(
			'success' => true,
			'data' => $rows,
			'query' => $q
		));
	}
	else if($action == 'getUserList'){
		$searchkey = $_REQUEST['searchkey'];
		$codeId_jobGrade = $db->queryOne("SELECT ID FROM BC_CODE_TYPE WHERE CODE = 'JOB_GRADE'");
		$codeId_userLevel = $db->queryOne("SELECT ID FROM BC_CODE_TYPE WHERE CODE = 'USER_LEVEL'");

		$q = "
			SELECT	U.LOGIN_ID
					, U.LOGIN_PWD
					, U.FIRSTNAME
					, U.LASTNAME
					, (U.LASTNAME||U.FIRSTNAME) USER_NM
					, U.USER_LEVEL
					, (SELECT CODE_NM FROM BC_CODE WHERE CODE_TYPE_ID = ".$codeId_userLevel." AND CODE = U.USER_LEVEL) USER_LEVEL_NM
					, U.JOB_GRADE
					, (SELECT CODE_NM FROM BC_CODE WHERE CODE_TYPE_ID = ".$codeId_jobGrade." AND CODE = U.JOB_GRADE) JOB_GRADE_NM
					, U.PHONE
					, U.EMAIL
					, U.CREATE_DATE
					, U.CREATE_USER
					, (SELECT (LASTNAME||FIRSTNAME) FROM BC_USER WHERE LOGIN_ID = U.CREATE_USER) CREATE_USER_NM
					, U.UPDATE_DATE
					, U.UPDATE_USER
					, (SELECT (LASTNAME||FIRSTNAME) FROM BC_USER WHERE LOGIN_ID = U.UPDATE_USER) UPDATE_USER_NM
			FROM	BC_USER U
			WHERE	U.USE_YN = 'Y'
			AND     (LASTNAME||FIRSTNAME) LIKE '%$searchkey%'
			".$sort_q."
		";
		
		$rows = $db->queryAll($q);

		echo json_encode(array(
			'success' => true,
			'data' => $rows,
			'query' => $q
		));
	}
	else if($action == 'get_user_level'){
		$login_id = trim($_REQUEST['login_id']);

		$q = "
			SELECT	USER_LEVEL
			FROM	BC_USER
			WHERE	LOGIN_ID = '{$login_id}'
		";

		$user_level = $db->queryOne($q);

		echo json_encode(array(
			'success' => true,
			'user_level' => $user_level,
			'query' => $q
		));
	}
	else if($action == 'confirm_pwd'){
		$login_id = trim($_REQUEST['login_id']);
		$pwd = $_REQUEST['pwd'];

		$q = "
			SELECT	COUNT(LOGIN_ID)
			FROM	BC_USER
			WHERE	LOGIN_ID = '{$login_id}'
			AND		LOGIN_PWD = '{$pwd}'
		";

		$r = $db->queryOne($q);

		if($r > 0){
			echo json_encode(array(
				'success' => true,
				'result' => $r
			));
		}
		else{
			echo json_encode(array(
				'success' => false,
				'result' => $r
			));
		}
	}

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
