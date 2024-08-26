<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$license_id = trim($_REQUEST['license_id']);

try
{

	$total_q = "
		SELECT	COUNT(ID)
		FROM	BC_LICENSE_MAC_ADDR
		WHERE	LICENSE_ID = ".$license_id."
	";
	$total = $db->queryOne($total_q);


	$q = "
		SELECT	ID
				, MAC_ADDR
				, ADAPTER_NAME
				, CREATE_DATE
		FROM	BC_LICENSE_MAC_ADDR
		WHERE	LICENSE_ID = ".$license_id."
	";

	$rows = $db->queryAll($q);


	echo json_encode(array(
		'success' => true,
		'total' => $total,
		'data' => $rows,
		'query' => $q
	));
}
catch (Exception $e)
{
	echo json_encode(array(
		'success' => false,
		'msg' => $e->getMessage()
	));
}

?>