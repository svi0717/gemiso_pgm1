<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);
$product_nm = trim($_REQUEST['product_nm']);
$product_type = trim($_REQUEST['product_type']);
$company_nm = trim($_REQUEST['company_nm']);
$garantia_term = trim($_REQUEST['garantia_term']);
$description = trim($_REQUEST['description']);
$use_yn = trim($_REQUEST['use_yn']);
$create_date = date('YmdHis');
$user_id = $_SESSION['user']['user_id'];

try
{
	if(empty($action)) throw new Exception('�߸��� �����Դϴ�.');

	//code type
	if($action == 'regist'){
		$last_id = $db->queryOne("SELECT MAX(PRODUCT_ID) FROM BC_PRODUCT");
		$id = (int)substr($last_id, 1);
		if($product_type == 'OURS') $id = 'G'.str_pad(++$id, 4, "0", STR_PAD_LEFT);
		else if($product_type == 'OTHERS') $id = 'E'.str_pad(++$id, 4, "0", STR_PAD_LEFT);

		$q = "
			INSERT INTO BC_PRODUCT
			(PRODUCT_ID, PRODUCT_NM, PRODUCT_TYPE, COMPANY_NM, GARANTIA_TERM, DESCRIPTION, USE_YN, CREATE_DATE, CREATE_USER, DEL_YN)
			VALUES
			('$id', '$product_nm', '$product_type', '$company_nm', '$garantia_term', '$description', '$use_yn', '$create_date', '$user_id', 'N')
		";

		$r = $db->exec($q);
	}
	else if($action == 'update'){
		$id = trim($_REQUEST['product_id']);
		$q = "
			UPDATE	BC_PRODUCT
			SET		PRODUCT_NM = '$product_nm'
					, PRODUCT_TYPE = '$product_type'
					, garantia_term = '$garantia_term'
					, COMPANY_NM = '$company_nm'
					, DESCRIPTION = '$description'
					, USE_YN = '$use_yn'
					, UPDATE_DATE = '$create_date'
					, UPDATE_USER = '$user_id'
			WHERE	PRODUCT_ID = '$id'
		";

		$r = $db->exec($q);
	}
	else if($action == 'remove'){
		$id = trim($_REQUEST['product_id']);

		$q = "
			UPDATE	BC_PRODUCT
			SET		DEL_YN = 'Y'
					,DELETE_DATE = '$create_date'
					,DELETE_USER = '$user_id'
			WHERE	PRODUCT_ID = '$id'
		";

		$r = $db->exec($q);
	}

	echo json_encode(array(
		'success' => true,
		'id' => $id,
		'q' => $q
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