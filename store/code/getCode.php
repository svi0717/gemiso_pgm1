<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);

try
{
	if($action == 'getCodeType'){
		$search_f = trim($_REQUEST['search_f']);
		$search_v = trim($_REQUEST['search_v']);

		if(!empty($search_f) && (!empty($search_v) || $search_v == '0')){
			switch($search_f){
				case 'code_type':
					$where = "AND UPPER(CT.CODE) LIKE UPPER('%".$search_v."%') ";
					break;
				case 'code_type_nm':
					$where = "AND UPPER(CT.CODE_NM) LIKE UPPER('%".$search_v."%') ";
					break;
				case 'code':
					$where = "AND ID IN (SELECT CODE_TYPE_ID FROM BC_CODE WHERE UPPER(CODE) LIKE UPPER('%".$search_v."%'))";
					break;
				case 'code_nm':
					$where = "AND ID IN (SELECT CODE_TYPE_ID FROM BC_CODE WHERE UPPER(CODE_NM) LIKE UPPER('%".$search_v."%'))";
					break;
			}
		}

		$q = "
			SELECT 	ID
					, CODE
					, CODE_NM
					, USE_YN
					, CREATE_DATE
					, ( SELECT LASTNAME||FIRSTNAME FROM BC_USER WHERE LOGIN_ID = CT.CREATE_USER) AS CREATE_USER
			FROM 	BC_CODE_TYPE CT
			WHERE 	USE_YN = 'Y'
			".$where."
			ORDER BY ID ASC
		";

		$rows = $db->queryAll($q);
	}
	else if($action == 'getCode'){
		$code_type_id = trim($_REQUEST['code_type_id']);

		$q = "
			SELECT	ID
				, CODE
				, CODE_NM
				, SORT
				, CREATE_DATE
				, ( SELECT LASTNAME||FIRSTNAME FROM BC_USER WHERE LOGIN_ID = C.CREATE_USER) AS CREATE_USER
			FROM	BC_CODE C
			WHERE	CODE_TYPE_ID = ".$code_type_id."
			AND	USE_YN = 'Y'
			ORDER BY SORT ASC
		";

		$rows = $db->queryAll($q);
	}
	/*
		$q = "
			SELECT	CT.ID AS CODE_TYPE_ID
					, CT.CODE AS CODE_TYPE_CODE
					, CT.CODE_NM AS CODE_TYPE_NM
					, C.ID
					, C.CODE
					, C.CODE_NM
					, C.SORT
					, C.CREATE_DATE
			FROM	BC_CODE_TYPE CT
					, BC_CODE C
			WHERE	CT.ID = C.CODE_TYPE_ID
			AND		CT.USE_YN = 'Y'
			AND		C.USE_YN = 'Y'
			ORDER BY CT.ID ASC, C.SORT ASC
		";
		$rows = $db->queryAll($q);

		$total = $db->queryOne("
			SELECT	COUNT(C.ID)
			FROM	BC_CODE_TYPE CT
					, BC_CODE C
			WHERE	CT.ID = C.CODE_TYPE_ID
			AND		CT.USE_YN = 'Y'
			AND		C.USE_YN = 'Y'
		");
	*/
	echo json_encode(array(
		'success' => true,
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
