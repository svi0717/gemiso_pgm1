<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$product_type = trim($_REQUEST['product_type']);
$company_nm = trim($_REQUEST['company_nm']);
$product_nm = trim($_REQUEST['product_nm']);
$use_yn = trim($_REQUEST['use_yn']);

$sort = trim($_REQUEST['sort']);
$dir = trim($_REQUEST['dir']);
$sort_q = 'ORDER BY CREATE_DATE DESC';

if(!empty($sort)){
	$sort_q = 'ORDER BY '.$sort.' '.$dir;
}

try
{
	$where = array();
	$where[] = "DEL_YN = 'N'";
	if(!empty($product_type) && $product_type != 'all'){
		$where[] = "PRODUCT_TYPE = '".$product_type."'";
	}
	if(!empty($company_nm) && $company_nm != 'all'){
		$where[] = "upper(COMPANY_NM) like upper('%".$company_nm."%')";
	}
	if(!empty($product_nm) && $product_nm != 'all'){
		$where[] = "upper(PRODUCT_NM) like upper('%".$product_nm."%')";
	}
	if(!empty($use_yn) && $use_yn != 'all'){
		$where[] = "use_yn = '".$use_yn."'";
	}
	
	if(!empty($where)) $where_str = "WHERE ".join(' AND ', $where);

	$q = "
		SELECT	PRODUCT_ID
				, PRODUCT_NM
				, PRODUCT_TYPE
				, (SELECT C.CODE_NM FROM BC_CODE C, BC_CODE_TYPE CT WHERE CT.ID = C.CODE_TYPE_ID AND CT.CODE = 'PRODUCT_TYPE' AND C.CODE = P.PRODUCT_TYPE) PRODUCT_TYPE_NM
				, COMPANY_NM
				, GARANTIA_TERM
				, (SELECT C.CODE_NM FROM BC_CODE C, BC_CODE_TYPE CT WHERE CT.ID = C.CODE_TYPE_ID AND CT.CODE = 'USE_TERM' AND C.CODE = P.GARANTIA_TERM) GARANTIA_TERM_NM
				, ( SELECT LASTNAME||FIRSTNAME FROM BC_USER WHERE LOGIN_ID = P.CREATE_USER) AS CREATE_USER
				, CREATE_DATE
				, DESCRIPTION
				, USE_YN
		FROM	BC_PRODUCT P
		".$where_str."
		".$sort_q."
	";
	@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $q:::'.$q."\n", FILE_APPEND);

	$total = $db->queryOne("
		SELECT	COUNT(PRODUCT_ID)
		FROM	BC_PRODUCT
		".$where_str."
	");
	$rows = $db->queryAll($q);

	echo json_encode(array(
		'success' => true,
		'total' => $total,
		'data' => $rows,
		'query' => $q
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
