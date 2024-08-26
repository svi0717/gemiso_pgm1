<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);
$equ_id = trim($_REQUEST['equ_id']);
$comp_nm = trim($_REQUEST['comp_nm']);
$equ_nm = trim($_REQUEST['equ_nm']);
$equ_type = trim($_REQUEST['equ_type']);
$login_id = trim($_REQUEST['login_id']);
$purchase_ymd = trim($_REQUEST['purchase_ymd']);
$use_comment = trim($_REQUEST['use_comment']);
$use_yn = trim($_REQUEST['use_yn']);
$cpu = trim($_REQUEST['cpu']);
$os = trim($_REQUEST['os']);
$graphics = trim($_REQUEST['graphics']);
$memory = trim($_REQUEST['memory']);
$hdd1 = trim($_REQUEST['hdd1']);
$hdd2 = trim($_REQUEST['hdd2']);
$description = trim($_REQUEST['description']);
$create_date = date('YmdHis');
$user_id = $_SESSION['user']['user_id'];

try
{
	if(empty($action)) throw new Exception('�߸��� �����Դϴ�.');
	
	if($action == 'regist'){
		$_type = substr($equ_type, 0, 1);
		$last_id = $db->queryOne("SELECT MAX(EQU_ID) FROM BC_EQUIPMENT WHERE EQU_ID LIKE '$_type%'");
		$equ_id = (int)substr($last_id, 1);	
		$equ_id = $_type.str_pad(++$equ_id, 4, "0", STR_PAD_LEFT);

		$q = "
			INSERT INTO BC_EQUIPMENT
			(EQU_ID, COMP_NM, EQU_NM, EQU_TYPE, LOGIN_ID, PURCHASE_YMD, USE_COMMENT, USE_YN, CPU, OS, GRAPHICS, MEMORY, DEL_YN, HDD1, HDD2, DESCRIPTION, CREATE_DATE, CREATE_USER)
			VALUES
			('$equ_id', '$comp_nm', '$equ_nm', '$equ_type', '$login_id', '$purchase_ymd', '$use_comment', '$use_yn', '$cpu', '$os', '$graphics', '$memory', 'N', '$hdd1', '$hdd2', '$description', '$create_date', '$user_id')
		";
		@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $q:::'.$q."\n\n", FILE_APPEND);

		$r = $db->exec($q);
	}
	else if($action == 'update'){
		$equ_id = trim($_REQUEST['equ_id']);
		$q = "
			UPDATE	BC_EQUIPMENT
			SET		COMP_NM = '".$comp_nm."'
					, EQU_NM = '".$equ_nm."'
					, EQU_TYPE = '".$equ_type."'
					, LOGIN_ID = '".$login_id."'
					, PURCHASE_YMD = '".$purchase_ymd."'
					, USE_COMMENT = '".$use_comment."'
					, USE_YN = '".$use_yn."'
					, CPU = '".$cpu."'
					, OS = '".$os."'
					, GRAPHICS = '".$graphics."'
					, MEMORY = '".$memory."'
					, HDD1 = '".$hdd1."'
					, HDD2 = '".$hdd2."'
					, DESCRIPTION = '".$description."'
					, UPDATE_DATE = '".$create_date."'
					, UPDATE_USER = '".$user_id."'
			WHERE	EQU_ID = '".$equ_id."'
		";

		$r = $db->exec($q);
	}
	else if($action == 'remove'){
		$equ_id = trim($_REQUEST['equ_id']);

		$q = "
			UPDATE	BC_EQUIPMENT
			SET		DEL_YN = 'Y'
			WHERE	EQU_ID = '".$equ_id."'
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