<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);

$user_id = $_SESSION['user']['user_id'];
$login_id = trim($_REQUEST['id']);

$lastname = trim($_REQUEST['lastname']);
$firstname = trim($_REQUEST['firstname']);
$password = trim($_REQUEST['password']);
$new_password = trim($_REQUEST['new_password']);
$new_password_confirm = trim($_REQUEST['new_password_confirm']);
$job_grade = trim($_REQUEST['job_grade']);
$user_level = trim($_REQUEST['user_level']);
$login_pwd = trim($_REQUEST['login_pwd']);
$phone = trim($_REQUEST['phone']);
$email = trim($_REQUEST['email']);
@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $_REQUEST:::'.print_r($_REQUEST,true)."\n", FILE_APPEND);
$update_date = date('YmdHis');

$reset_password = '9ce62ad7193de3dc3a6c24bb8eba4f4ff3f160fd340a9689bc1a229a70641ba738e79399fd55584edf88db505496758690983c524f248e31a2ae29d06ac4be1f'; //gemiso1!

$original_user = $db->queryRow("SELECT * FROM BC_USER WHERE LOGIN_ID = '$login_id'");

try
{
	if(empty($action)) throw new Exception('잘못된 동작입니다.');

	if($action == 'regist'){
		//regist
		$login_id = trim($_REQUEST['login_id']);
		
		$q = "
			INSERT INTO BC_USER
			(LOGIN_ID, FIRSTNAME, LASTNAME, LOGIN_PWD, PHONE, EMAIL, JOB_GRADE, USER_LEVEL, CREATE_DATE, CREATE_USER, USE_YN, LANG_CD)
			VALUES
			('$login_id', '$firstname', '$lastname', '$password', '$phone', '$email', '$job_grade', '$user_level', '$update_date', '$user_id', 'Y', 'ko')
		";

		$r = $db->exec($q);

	}
	else if($action == 'update_in_userManagement'){
		$login_id = trim($_REQUEST['login_id']);

		$q = "
			UPDATE	BC_USER
			SET		FIRSTNAME = '$firstname'
					, LASTNAME = '$lastname'
					, PHONE = '$phone'
					, EMAIL = '$email'
					, JOB_GRADE = '$job_grade'
					, USER_LEVEL = '$user_level'
					, UPDATE_DATE = '$update_date'
					, UPDATE_USER = '$user_id'
			WHERE	LOGIN_ID = '$login_id'
		";
		@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $q:::'.$q."\n", FILE_APPEND);
		$r = $db->exec($q);
	}
	else if($action == 'update'){
		//update
		$login_pwd = $db->queryOne("SELECT login_pwd FROM BC_USER WHERE LOGIN_ID = '$login_id'");
		$update_pwd = "";

		if(!empty($password)){
			if($login_pwd != $password){
				throw new Exception('기존 비밀번호가 틀렸습니다.');
			}
			else if(trim($new_password) != trim($new_password_confirm)){
				throw new Exception('새 비밀번호를 다시 확인해주세요.');
			}
			$update_pwd = ", LOGIN_PWD = '$new_password'";
		}

		if($original_user['user_level'] == 'L04' || $original_user['user_level'] == 'L09'){
			$update_jobgrade = ", JOB_GRADE = '$job_grade'";
			$update_user_level = ", USER_LEVEL = '$user_level'";
		}
		$q = "
			UPDATE	BC_USER
			SET		FIRSTNAME = '$firstname'
					, LASTNAME = '$lastname'
					, PHONE = '$phone'
					, EMAIL = '$email'
					".$update_pwd."
					".$update_jobgrade."
					".$update_user_level."
					, UPDATE_DATE = '$update_date'
					, UPDATE_USER = '$user_id'
			WHERE	LOGIN_ID = '$login_id'
		";

		$r = $db->exec($q);

		$user = $db->queryRow("SELECT login_id, lastname, firstname FROM BC_USER WHERE LOGIN_ID = '$login_id'");

		$_SESSION['user'] = array(
			'user_id' => $user['login_id'],
			'EN_NM' => $user['firstname']." ".$user['lastname'],
			'KOR_NM' => $user['lastname']." ".$user['firstname']
		);
	}
	else if($action == 'remove'){
		$login_id = trim($_REQUEST['login_id']);

		$q = "
			UPDATE	BC_USER
			SET		USE_YN = 'N'
			WHERE	LOGIN_ID = '$login_id'
		";

		$r = $db->exec($q);
	}else if($action == 'reset_password'){
		$login_id = trim($_REQUEST['login_id']);

		$q = "
			UPDATE	BC_USER
			SET		LOGIN_PWD = '$reset_password'
			WHERE	LOGIN_ID = '$login_id'
		";

		$r = $db->exec($q);
	}

	echo json_encode(array(
		'success' => true,
		'q' => $q
	));

	// $db->close();
	$db = null;
}
catch (Exception $e)
{
	echo json_encode(array(
		'success' => false,
		'msg' => $e->getMessage(),
		'q' => $q
	));
}

?>