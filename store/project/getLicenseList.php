<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$license_type = trim($_REQUEST['license_type']);
$proj_id = trim($_REQUEST['proj_id']);
$product_id = trim($_REQUEST['product_id']);
$license_type = trim($_REQUEST['license_type']);
$license_status = trim($_REQUEST['license_status']);
$expired_status = trim($_REQUEST['expired_status']);
$searchkey = trim($_REQUEST['searchkey']);

try
{
	//코드 매핑 Array
	$code_q = "
		SELECT	C.CODE_TYPE_ID
				, CT.CODE AS CODE_TYPE
				, C.ID
				, C.CODE
				, C.CODE_NM
				, C.SORT
		FROM	BC_CODE C
				, BC_CODE_TYPE CT
		WHERE 	C.CODE_TYPE_ID = CT.ID
		AND		C.USE_YN = 'Y'
		ORDER BY C.CODE_TYPE_ID, C.ID
	";
	$code_data = $db->queryAll($code_q);
	$code_arr = array();
	foreach($code_data as $row)
	{
		if(empty($code_arr[$row['code_type']]))					$code_arr[$row['code_type']] = array();
		if(empty($code_arr[$row['code_type']][$row['code']])) 	$code_arr[$row['code_type']][$row['code']] = '';
		$code_arr[$row['code_type']][$row['code']] = $row['code_nm'];
	}
	//@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date("Y-m-d H:i:s").'] 마지막:::'.print_r($code_arr,true)."\n", FILE_APPEND);

	//라이센스 list
	$where = array();
	if(!empty($proj_id)){
		$where[] = "L.PROJ_ID = '".$proj_id."'";
	}
	if(!empty($product_id) && $product_id != 'all'){
		$where[] = "L.PRODUCT_ID = '".$product_id."'";
	}
	if(!empty($license_type) && $license_type != 'all'){
		$where[] = "L.LICENSE_TYPE = '".$license_type."'";
	}
	if(!empty($license_status) && $license_status != 'all'){
		if($license_status == 'non_complete'){
			$where[] = "L.REG_STATUS != 'R99'";
		}
		else if($license_status == 'complete'){
			$where[] = "L.REG_STATUS = 'R99'";
		}
		
	}
	if(!empty($searchkey)){
		$where_searchkey = array();
		$where_searchkey[] = "upper(L.LICENSE_KEY) LIKE upper('%".$searchkey."%')";
		$where_searchkey[] = "upper(P.PRODUCT_NM) LIKE upper('%".$searchkey."%')";
		$where_searchkey[] = "upper(L.OS) LIKE upper('%".$searchkey."%')";
		$where_searchkey[] = "upper(L.SERVER_NM) LIKE upper('%".$searchkey."%')";
		$where_searchkey[] = "upper(L.DESCRIPTION) LIKE upper('%".$searchkey."%')";

		$where[] = "(".join(' OR ', $where_searchkey).")";
	}

	if($expired_status == 'true'){
		$expire_where = "T.EXPIRE_DATE < TO_CHAR(CURRENT_TIMESTAMP, 'YYYYMMDD') || '000000'";
	}
	else{
		$expire_where = "T.EXPIRE_DATE IS NULL OR T.EXPIRE_DATE >= TO_CHAR(CURRENT_TIMESTAMP, 'YYYYMMDD') || '240000'";
	}
	if(!empty($where)) $where_str = ' AND '.join(' AND ', $where);

	$q = "
		SELECT	T.*
		FROM	(
				SELECT	CASE 	
							WHEN	L.COMPLETE_DATE != ''
									AND L.USE_TERM != '91'
									THEN TO_CHAR(TO_TIMESTAMP(L.COMPLETE_DATE, 'YYYYMMDDHH24MISS') + (L.USE_TERM::INTEGER * '24 hour'::INTERVAL), 'YYYYMMDDHH24MISS')
							ELSE NULL
						END AS EXPIRE_DATE
						,L.ID
						, L.PROJ_ID
						, PROJ.PROJ_NM
						, PROJ.CUST_NM
						, L.LICENSE_TYPE
						, L.PRODUCT_ID
						, P.PRODUCT_NM
						, L.USE_TERM
						, L.REG_TYPE
						, L.REG_STATUS
						, L.OS
						, L.CH_CNT
						, L.CPU_ID
						, L.CPU_INFO
						, L.MEMORY
						, L.SERVER_NM
						, L.LICENSE_KEY
						, L.CREATE_DATE
						, L.COMPLETE_DATE
						, ( SELECT LASTNAME||FIRSTNAME FROM BC_USER WHERE LOGIN_ID = L.CREATE_USER) AS CREATE_USER
						, L.DESCRIPTION
						, L.PROVIDED
				FROM	BC_LICENSE L
						LEFT OUTER JOIN BC_PROJ_MST PROJ ON (L.PROJ_ID = PROJ.PROJ_ID)
						, BC_PRODUCT P
				WHERE	L.DEL_YN = 'N'
				".$where_str."
				AND L.PRODUCT_ID = P.PRODUCT_ID
				AND P.USE_YN = 'Y'
				ORDER BY L.CREATE_DATE DESC, L.ID DESC
				) T
		WHERE	".$expire_where."
		
	";
	@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $q:::'.$q."\n", FILE_APPEND);
	$total = $db->queryOne("
		SELECT	COUNT(L.ID)
		FROM	BC_LICENSE L
				, BC_PRODUCT P
		WHERE	L.DEL_YN = 'N'
		".$where_str."
		AND L.PRODUCT_ID = P.PRODUCT_ID
		AND P.USE_YN = 'Y'
	");
	$rows = $db->queryAll($q);

	for($i= 0 ; $i<count($rows); $i++)
	{
		$row = $rows[$i];
		foreach($row as $field => $value)
		{
			if(!empty($code_arr[strtoupper($field)])) $rows[$i][$field.'_nm'] = $code_arr[strtoupper($field)][$value];
		}

	}

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
