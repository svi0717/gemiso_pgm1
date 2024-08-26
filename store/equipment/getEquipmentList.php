<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$equ_type = trim($_REQUEST['equ_type']);
$equ_nm = trim($_REQUEST['equ_nm']);
$login_id = trim($_REQUEST['login_id']);


$sort = trim($_REQUEST['sort']);
$dir = trim($_REQUEST['dir']);
$sort_q = 'ORDER BY CREATE_DATE DESC';

try
{
	$where = array();
	if(!empty($equ_type) && $equ_type != 'all'){
		$where[] = "E.EQU_TYPE = '".$equ_type."'";
	}
	if(!empty($equ_nm)){
		$where[] = "UPPER(E.EQU_NM) LIKE UPPER('%".$equ_nm."%')";
	}	
	if(!empty($login_id)){
		$where[] = "E.LOGIN_ID LIKE '%$login_id%'";
	}
	$where[] = "DEL_YN = 'N'";
	
	if(!empty($where)) $where_str = "WHERE ".join(' AND ', $where);

	$total = $db->queryOne("
		select count(equ_id) from bc_equipment E ".$where_str."
	");

	if(!empty($sort)){
		$sort_q = 'ORDER BY '.$sort.' '.$dir;
	}

	if($total < 1){
		echo json_encode(array(
			'success' => true,
			'total' => $total,
			'data' => array(),
			'q' => "select count(equ_id) from bc_equipment ".$where_str.""
		));

		return;
	}


	$q = "
		SELECT 	E.EQU_ID
				, E.EQU_NM
				, E.EQU_TYPE
				, (SELECT C.CODE_NM FROM BC_CODE C , BC_CODE_TYPE CT WHERE C.CODE_TYPE_ID = CT.ID AND CT.CODE = 'EQU_TYPE' AND C.CODE = E.EQU_TYPE) AS EQU_TYPE_NM
				, E.COMP_NM
				, E.CPU, OS
				, E.MEMORY
				, E.GRAPHICS
				, E.HDD1
				, E.HDD1_SDD_YN
				, E.HDD2
				, E.HDD2_SDD_YN
				, E.PURCHASE_YMD
				, E.MAKE_YMD
				, E.USE_COMMENT
				, E.LOGIN_ID
				, E.DESCRIPTION
				, E.USE_YN
				, E.CREATE_DATE
				, E.DEL_YN
				, E.UPDATE_DATE
				, E.UPDATE_USER
				, U.LASTNAME||U.FIRSTNAME AS UPDATE_USER_NM
		FROM 	BC_EQUIPMENT E
		LEFT OUTER JOIN BC_USER U ON (E.UPDATE_USER = U.LOGIN_ID)
		".$where_str."
		".$sort_q."
	";
	@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $q:::'.$q."\n", FILE_APPEND);
	
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
