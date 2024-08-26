<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);
$create_date = date('YmdHis');
$member_id = $_SESSION['user']['user_id'];

try
{
	if(empty($action)) throw new Exception('잘못된 동작입니다.');

	if($action == 'regist'){
		$last_id = $db->queryOne("SELECT MAX(ID) FROM BC_LICENSE");
		$id = ++$last_id;
		
		$proj_id		= trim($_REQUEST['proj_id']);
		$reg_type		= trim($_REQUEST['reg_type']);
		$product		= trim($_REQUEST['product']);
		$channel_cnt		= trim($_REQUEST['channel_cnt']);
		//$license_type	= trim($_REQUEST['license_type']);
		$reg_status		= trim($_REQUEST['reg_status']);
		$use_term		= trim($_REQUEST['use_term']);
		if($use_term == '91'){
			$license_type = 'LICENSE';
		}
		else{
			$license_type = 'TRIAL';
		}
		$license_cnt	= trim($_REQUEST['license_cnt']);
		$description	= trim($_REQUEST['description']);
		
		//오프라인 라이센스 등록
		$os				= trim($_REQUEST['os']);
		$cpu_id			= trim($_REQUEST['cpu_id']);
		$cpu_info		= trim($_REQUEST['cpu_info']);
		$memory			= trim($_REQUEST['memory']);
		$mac_address_list	= $_REQUEST['mac_address'];
		$adapter_nm		= trim($_REQUEST['adapter_nm']);
		$server_nm		= trim($_REQUEST['server_nm']);
		$complete_date	= '';

		@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $_REQUEST:::'.$_REQUEST."\n", FILE_APPEND);

		if(!empty($mac_address_list)){
			$license_cnt = 1;
			$complete_date = $create_date;
		}

		$license_index = $db->queryOne("select count(id) from bc_license where proj_id = '{$proj_id}' and product_id = '{$product}'");
		
		$cmd_str = 'd:'; // C 드라이브 -> D드라이브 || D 드라이브 -> C드라이브 이동 시 cd c:\가 아니라 c:로 사용해야된다.
		$cmd_str = $cmd_str.' & cd D:\dev_app\Gemiso_Licence';
		$cmd_str = $cmd_str.' & MakeGMLicense.exe';
		$cmd_str = $cmd_str.' -p:'.$proj_id;
		$cmd_str = $cmd_str.' -g:'.$product;
		$cmd_str = $cmd_str.' -d:'.$use_term;
		$cmd_str = $cmd_str.' -c:'.$license_cnt;
		$cmd_str = $cmd_str.' -l:'.++$license_index;
		
		exec($cmd_str, $output, $return);
		@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $cmd_str:::'.$cmd_str."\n", FILE_APPEND);
		@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $output:::'.print_r($output,true)."\n", FILE_APPEND);
		@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $return:::'.$return."\n", FILE_APPEND);

		for($i=0; $i<$license_cnt; $i++){
			$license_key = $output[$i];
			@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] $license_key:::'.$license_key."\n", FILE_APPEND);
			if(empty($license_key) || strlen($license_key) > 29) throw new Exception('라이센스 키 생성 오류.');

			$exist = $db->queryOne("select count(id) from bc_license where LICENSE_KEY = '{$license_key}'");
			if($exist) throw new Exception('이미 존재하는 라이센스 키입니다.('.$license_key.')');

			$q = "
				INSERT INTO BC_LICENSE
				(ID, PROJ_ID, REG_TYPE, LICENSE_TYPE, PRODUCT_ID, CH_CNT, USE_TERM, REG_STATUS, OS, CPU_ID, LICENSE_KEY, DEL_YN, DESCRIPTION, CREATE_DATE, CREATE_USER, MEMORY, SERVER_NM, CPU_INFO, COMPLETE_DATE)
				VALUES
				('$id', '$proj_id', '$reg_type', '$license_type', '$product', '$channel_cnt', '$use_term', '$reg_status', '$os', '$cpu_id', '$license_key', 'N', '$description', '$create_date', '$member_id', '$memory', '$server_nm', '$cpu_info', '$complete_date')
			";

			$r = $db->exec($q);

			//오프라인 라이센스 등록인 경우 mac_address를 직접 입력받는다.
			if(!empty($mac_address_list)) {
				//foreach($mac_address_list as $mac_address){
					$mac_id = $db->queryOne("SELECT MAX(ID) FROM BC_LICENSE_MAC_ADDR");
					$mac_id = $mac_id+1;

					$insert_mac_q = "
						INSERT INTO BC_LICENSE_MAC_ADDR
						(ID, PROJ_ID, LICENSE_ID, ADAPTER_NAME, MAC_ADDR, CREATE_DATE, CREATE_USER)
						VALUES
						('".$mac_id."', '".$proj_id."', '".$id."', '".$adapter_nm."', '".$mac_address_list."', '".$create_date."', '".$member_id."')
					";
					$r = $db->exec($insert_mac_q);
		//		}
			}

			$id = ++$last_id;
		}
	}
	else if($action == 'remove'){
		$id = trim($_REQUEST['id']);

		$q = "
			UPDATE	BC_LICENSE
			SET		DEL_YN = 'Y'
					, REG_STATUS = 'R04'
					, DELETE_DATE = '".$create_date."'
					, DELETE_USER = '".$member_id."'
			WHERE	ID = '".$id."'
		";

		$r = $db->exec($q);

		$q = "
			DELETE 
			FROM BC_LICENSE_MAC_ADDR 
			WHERE LICENSE_ID = ".$id."
		";

		$r = $db->exec($q);
	}
	else if($action == 'reset'){
		$id = trim($_REQUEST['id']);

		$q = "
			UPDATE	BC_LICENSE
			SET		DEL_YN = 'N'
					, REG_STATUS = 'R02'
					, OS = null
					, CPU_ID = null
					, MEMORY = null
					, SERVER_NM = null
					, CPU_INFO = null
					, COMPLETE_DATE = null
			WHERE	ID = '".$id."'
		";

		$r = $db->exec($q);

		$q = "
			DELETE 
			FROM BC_LICENSE_MAC_ADDR 
			WHERE LICENSE_ID = ".$id."
		";

		$r = $db->exec($q);
	}
	else if($action == 'provide'){
		$ids = trim($_REQUEST['license_ids']);
		$provide = trim($_REQUEST['provide']);

		$ids_arr = explode(',', $ids);
		$id_q = array();
		for($i=0; $i<count($ids_arr); $i++){
			$id_q[] = "ID = '".$ids_arr[$i]."'";
		}

		$q = "
			UPDATE	BC_LICENSE
			SET		provided = '$provide'
			WHERE	".join(" OR ", $id_q)."
		";

		$r = $db->exec($q);
	}

	echo json_encode(array(
		'success' => true,
		'license_key' => $cmd_str,
		'id' => --$id,
		'output' => json_encode($output),
		'query' => $q
	));

	// $db->close();
	$db = null;
}
catch (Exception $e)
{
	echo json_encode(array(
		'success' => false,
		'cmd_str' => $cmd_str,
		'license_key' => $license_key,
		'msg' => $e->getMessage()
	));
}

?>