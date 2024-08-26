<?php
session_start();
 function TripleDesEncrypt($buffer,$key,$iv) {
 
	$cipher = mcrypt_module_open(MCRYPT_3DES, '', 'cbc', '');
	 $buffer.='___EOT';
	 // get the amount of bytes to pad
	 $extra = 8 - (strlen($buffer) % 8);
	 // add the zero padding
	 if($extra > 0) {
	 for($i = 0; $i < $extra; $i++) {
	 $buffer .= '_';
	 }
	 }
	 mcrypt_generic_init($cipher, $key, $iv);
	 $result = mcrypt_generic($cipher, $buffer);
	 mcrypt_generic_deinit($cipher);
	 return base64_encode($result);
 }

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$action = trim($_REQUEST['action']);
$create_date = date('YmdHis');

try{
	if($action == 'offline_license_download'){
		$license_id = trim($_REQUEST['id']);

		$license_cnt_q = "
			SELECT  (ID)
			FROM	BC_LICENSE
			WHERE	ID = ".$license_id."
		";
		$license_cnt = $db->queryOne($license_cnt_q);

		$response = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?> \n<response />");
		$reulst = $response->addChild('result');

		if($license_cnt > 0){
			$q = "
				SELECT	L.ID
						, L.PROJ_ID
						, L.LICENSE_TYPE
						, L.PRODUCT_ID
						, (SELECT P.PRODUCT_NM FROM BC_PRODUCT P WHERE P.PRODUCT_ID = L.PRODUCT_ID) AS PRODUCT_NM
						, L.LICENSE_KEY
						, L.USE_TERM
						, L.COMPLETE_DATE
						, L.CH_CNT AS CHANNEL
				FROM	BC_LICENSE L
				WHERE	L.ID = ".$license_id."
			";
			$license_data = $db->queryRow($q);

			$mac_cnt_q = "
				SELECT	COUNT(ID)
				FROM	BC_LICENSE_MAC_ADDR
				WHERE	LICENSE_ID = ".$license_id."
			";
			$mac_cnt = $db->queryOne($mac_cnt_q);
			
			$reulst->addAttribute('success', 'true');
			$reulst->addAttribute('msg', 'ok');

			$data = $response->addChild('data');
			$system = $data->addChild('system');
			$mac_address = $system->addChild('mac_address');
			if($mac_cnt > 0){			
				$mac_q = "
					SELECT	M.MAC_ADDR
					FROM	BC_LICENSE_MAC_ADDR M
					WHERE	LICENSE_ID = ".$license_id."
				";
				$mac_data = $db->queryAll($mac_q);
				foreach($mac_data as $mac_item){
					$mac_address->addChild('Item', $mac_item['mac_addr']);
				}
			}
			
			$license_type = 1;
			if($license_data['license_type'] == 'TRIAL'){
				$license_type = 0;
			}
			$data->addChild('license_type', $license_type);
			$data->addChild('project_id', $license_data['proj_id']);
			$data->addChild('program_id', $license_data['product_id']);
			$data->addChild('license', $license_data['license_key']);
			$data->addChild('license_day', $license_data['use_term']);
			$data->addChild('start_date', '');
			$data->addChild('end_date', '');

			$channel = (string)$license_data['channel'];
			if(empty($channel)) {
				$channel = '1';
			}

			$date = date('c');
			$date_arr = explode('+', $date);
			$rand_num = generateRenNum(2);
			$data->addChild('program_code', $date_arr[0].'.'.$rand_num.trim((string)$channel).'+'.$date_arr[1]);

			//제품명_사용기간_다운로드일시.xml
			$filename = $license_data['product_nm']."_".$license_data['use_term']."_".$create_date;
		}
		else{
			$reulst->addAttribute('success', 'false');
			$reulst->addAttribute('msg', '존재하지 않는 라이센스입니다.');

			$filename = $create_date;
		}


		$source = $response->asXml();

		$key="passwordDR0wSS@P6660juht";
		$iv="password";
		if (extension_loaded('mcrypt') === true) {
			//if (is_file($source) === true) {
				//$source = file_get_contents($source);
				//$encryptedSource=TripleDesEncrypt($source,$key,$iv);
				$encryptedSource=$source;
		}
		else {
			return false;
		}

		$filename = iconv('utf-8','euc-kr', $filename);

		Header("Content-type: text/xml charset=UTF-8"); 
		header("Content-Disposition: attachment; filename=".$filename.".xml"); 
		header( "Content-Description: PHP4 Generated Data" ); 
		header("Content-Transfer-Encoding: binary"); 

		echo $encryptedSource;

	}
	else if($action == 'current_license_state'){
		$proj_id = trim($_REQUEST['id']);

		$proj_q = "
		select	proj_id
				, proj_nm
				, cust_nm
		from	bc_proj_mst
		where	proj_id = '$proj_id'
		";
		$proj_data = $db->queryRow($proj_q);

		$q = "
		select	l.id
				, use_term_t.code_nm as use_term
				, l.use_term as use_term_code
				, reg_status_t.code_nm as reg_status
				, l.reg_status as reg_status_code
				, l.license_key
		from	bc_license l
				left outer join (
						select	c.code
								, c.code_nm
								, c.sort
						from	bc_code c
								, bc_code_type ct
						where	ct.id = c.code_type_id
						and		ct.code = 'USE_TERM'
				) use_term_t on (use_term_t.code = l.use_term)
				left outer join (
						select	c.code
								, c.code_nm
								, c.sort 
						from	bc_code c
								, bc_code_type ct 
						where	ct.id = c.code_type_id 
						and		ct.code = 'REG_STATUS'
				) reg_status_t on (reg_status_t.code = l.reg_status)
		where	l.proj_id = '$proj_id'
		and		l.del_yn = 'N'
		order	by use_term_t.sort asc
		";

		$license_datas = $db->queryAll($q);
		$total_cnt = 0;
		$rtn_array = array();

		foreach($license_datas as $license_row){
			if(empty($rtn_array[$license_row['use_term_code']])){
				$rtn_array[$license_row['use_term_code']] = array();
				$rtn_array[$license_row['use_term_code']]['cnt'] = 0;
				$rtn_array[$license_row['use_term_code']]['use_term_name'] = $license_row['use_term'];
				$rtn_array[$license_row['use_term_code']]['license'] = array();
			}

			$rtn_array[$license_row['use_term_code']]['cnt'] += 1;
			array_push($rtn_array[$license_row['use_term_code']]['license'], $license_row);
			$total_cnt += 1;
		}
		
		$filename = iconv('utf-8','euc-kr', 'test');

		Header("Content-type: text/xml charset=UTF-8"); 
		header("Content-Disposition: attachment; filename=".$filename.".txt"); 
		header( "Content-Description: PHP4 Generated Data" ); 
		header("Content-Transfer-Encoding: binary"); 

		echo "프로젝트 : ".$proj_data['proj_nm']."(".$proj_data['proj_id'].")";
		echo "\r\n";
		echo "고객사 : ".$proj_data['cust_nm']."건";
		echo "\r\n";
		echo "\r\n";

		echo "총 : ".$total_cnt."건";
		echo "\r\n";

		foreach($rtn_array as $use_term){
			echo $use_term['use_term_name']."(".$use_term['cnt'].")";
			echo "\r\n";
			foreach($use_term['license'] as $license_row){
				if($license_row['reg_status_code'] == 'R99' ){
					echo "(완료)";
				}
				echo $license_row['license_key'];
				echo "\r\n";
			}
			echo "\r\n";
		}
	}

}
catch(Exception $e)
{
	echo print_r("catch!!!!");
	echo $e->getMessage();
	//return return_error_code($e->getMessage()); 
}

// $db->close();
$db = null;


function generateRenNum($length) { 
    $characters  = "123456789";        
    $rendom_str = ""; 
    $loopNum = $length; 
    while ($loopNum--) { 
        $rendom_str .= $characters[mt_rand(0, strlen($characters)-1)]; 
    } 
    return (string)$rendom_str; 
}
?>