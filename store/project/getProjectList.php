<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$cust_nm = trim($_REQUEST['cust_nm']);
$product = trim($_REQUEST['product']);
$proj_nm = trim($_REQUEST['proj_nm']);

$sort = trim($_REQUEST['sort']);
$dir = trim($_REQUEST['dir']);
$sort_q = 'ORDER BY P.CREATE_DATE DESC';

if(!empty($sort)){
	$sort_q = 'ORDER BY '.$sort.' '.$dir;
}

try
{
	if(!empty($cust_nm)){
		$where[] = "UPPER(P.CUST_NM) LIKE UPPER('%".$cust_nm."%')";
	}
	if(!empty($product) && $product != 'all'){
		$where[] = "P.PROJ_ID IN (SELECT L2.PROJ_ID FROM BC_LICENSE L2 WHERE L2.PRODUCT_ID = '".$product."')";
	}
	if(!empty($proj_nm)){
		$where[] = "UPPER(P.PROJ_NM) LIKE UPPER('%".$proj_nm."%')";
	}

	if(!empty($where)) $where_str = ' AND '.join(' AND ', $where);

	$q = "
		SELECT	P.PROJ_ID
				, P.CUST_NM
				, P.PROJ_NM
				, P.CUST_USER_NM
				, P.PHONE
				, P.EMAIL
				, P.DESCRIPTION
				, P.CREATE_DATE
				, ( SELECT LASTNAME||FIRSTNAME FROM BC_USER WHERE LOGIN_ID = P.CREATE_USER) AS CREATE_USER
				, P.USE_YN
				, COUNT(L.LICENSE_KEY) AS LICENSE_CNT
		FROM	BC_PROJ_MST P
		LEFT OUTER JOIN BC_LICENSE L ON (P.PROJ_ID = L.PROJ_ID AND L.DEL_YN = 'N')
		WHERE	P.DEL_YN = 'N'
		".$where_str."
		GROUP BY P.PROJ_ID
				, P.CUST_NM
				, P.PROJ_NM
				, P.CUST_USER_NM
				, P.PHONE
				, P.EMAIL
				, P.DESCRIPTION
				, P.CREATE_DATE
				, P.USE_YN
		".$sort_q."
	";
	@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $q:::'.$q."\n", FILE_APPEND);

	$total = $db->queryOne("
		SELECT	COUNT(PROJ_ID)
		FROM	BC_PROJ_MST P
		WHERE	P.DEL_YN = 'N'
		".$where_str."
	");
	$rows = $db->queryAll($q);

	echo json_encode(array(
		'success' => true,
		'total' => $total,
		'data' => $rows,
		'query' => $q
	));
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
