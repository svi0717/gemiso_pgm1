<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nusoap/lib/nusoap.php'); 
require_once($_SERVER['DOCUMENT_ROOT'].'/nusoap/lib/nusoapmime.php'); 
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/functions.php');

//서버 객체 생성
$NAMESPACE = 'LicenseIFService';
$server    = new soap_server();
$server->soap_defencoding = 'UTF-8';
$server->decode_utf8 = false;
// Initialize WSDL support

$server->configureWSDL('LicenseIFService', 'urn:LicenseIFService'); 
$server_ip = $_SERVER['REMOTE_ADDR'];

$server->register(
	'putWriteLicense'
	,array(
		'requestXml'   => 'xsd:string'
	)
	,array('return' => 'xsd:string')
	,$NAMESPACE
	,$NAMESPACE.'#putWriteLicense'
	,'rpc'
	,'encoded'
	,'putWriteLicense'
);

function putWriteLicense($requestXml)
{
	global $db;

	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/logs/putWriteLicense'.date('Y-m-d').'.log',"\r\n[".date('Y-m-d H:i:s')."]\r\n". $requestXml."\r\n\r\n", FILE_APPEND);

	try{
		if(@trim($requestXml))
		{
			//echo "있다";
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string(trim($requestXml));

			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/logs/putWriteLicense'.date('Y-m-d').'.log',print_r($xml, true)."\r\n\r\n", FILE_APPEND);

			if(!$xml)
			{
				$code = '500';
				return return_error_code($code,'xml오류'); 
			}
			else 
			{
				$parser_xml = $xml;
				/*
				<?xml version="1.0"?>
				<request>
				  <data>
					<system>
					  <cpu_id>BFEBFBFF000206A7</cpu_id>
					  <os Ver="6.1.7601">Microsoft Windows 7 Ultimate K </os>
					  <memory>6078 MB</memory>
					  <user_name>Administrator</user_name>
					  <pc_name>MSDN-SPECIAL</pc_name>
					  <cpu core="4">       Intel(R) Core(TM) i5-2450M CPU @ 2.50GHz</cpu>
					  <mac_address>
						<Item AdapterName="Qualcomm Atheros AR9285 802.11b/g/n WiFi Adapter">9C-B7-0D-64-FB-2B</Item>
						<Item AdapterName="Realtek PCIe GBE Family Controller">E4-11-5B-48-E9-0D</Item>
					  </mac_address>
					</system>
					<license>B2FB0-0DWFQ-CEEBA-GDFWJ-BGAEH</license>
					<project_id>P00033</project_id>
					<program_id>G0001</program_id>
					<license_day>60</license_day>
					<start_date Date="16-01-15 17:06:09">20160115170609</start_date>
					<end_date Date="16-03-15 17:06:09">20160315170609</end_date>
				  </data>
				</request>
				*/

				if($parser_xml)
				{					
					$license		= (string)$parser_xml->data->license;
					$project_id		= (string)$parser_xml->data->project_id;
					$start_date		= (string)$parser_xml->data->start_date;
					$system			= $parser_xml->data->system;
					$cpu_id			= (string)$system->cpu_id;
					$mac_address	= $system->mac_address;
					$os				= (string)$system->os;
					$memory			= (string)$system->memory;
					$server_nm		= (string)$system->pc_name;
					$cpu_info		= (string)$system->cpu;

					/*
					$where = array();

					if(!empty($license)) $where[] = "LICENSE_KEY = '".$license."'";
					if(!empty($project_id)) $where[] = "PROJ_ID = '".$project_id."'";

					if(!empty($where)) $where_str = 'WHERE '.join(' AND ', $where);
					*/
					if(!empty($license)) $license_where = "WHERE L.LICENSE_KEY = '".$license."'";
					if(!empty($project_id)) $proj_where = "AND P.PROJ_ID= '".$project_id."'";

					$total_q = "
						SELECT	COUNT(L.ID)
						FROM	BC_LICENSE L
						LEFT OUTER JOIN BC_PROJ_MST P ON (P.PROJ_ID = L.PROJ_ID ".$proj_where.")
						".$license_where."
					";
					
					$total = $db->queryOne($total_q);

					if($total != 1){
						return send_xml($row, false, '잘못된 라이센스 정보입니다.', '');
					}
					
					$q = "
						SELECT	L.ID
								, L.PROJ_ID
								, L.REG_TYPE
								, L.LICENSE_TYPE
								, L.PRODUCT_ID
								, L.USE_TERM
								, L.REG_STATUS
								, L.OS
								, L.CPU_ID
								, L.LICENSE_KEY
								, L.DEL_YN
								, L.CREATE_DATE
								, L.MEMORY
								, L.SERVER_NM
								, L.CPU_INFO
								, P.DEL_YN AS PROJ_DEL_YN
								, L.CH_CNT AS CHANNEL
						FROM	BC_LICENSE L
						LEFT OUTER JOIN BC_PROJ_MST P ON (P.PROJ_ID = L.PROJ_ID ".$proj_where.")								
						".$license_where."
					";
					@file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/'.basename(__FILE__).'_'.date('Ymd').'.log', '['.date('Y-m-d H:i:s').'] '. '$q:::'.print_r($q,true)."\r\n\r\n", FILE_APPEND);
					$row = $db->queryRow($q);

					if($row['proj_del_yn'] == 'Y')//프로젝트 삭제
					{
						return send_xml($row, false, '삭제된 프로젝트의 라이센스입니다.', '');
					}

					if($row['reg_status'] == "R99") //등록완료
					{
						return send_xml($row, false, '이미 등록된 라이센스입니다.', '');
					}
					else if($row['reg_status'] == "R03")//취소
					{
						return send_xml($row, false, '취소된 라이센스입니다.', '');
					}
					else if($row['reg_status'] == "R06")//보류
					{
						return send_xml($row, false, '보류된 라이센스입니다.', '');
					}
					else if($row['reg_status'] == "R04" || $row['del_yn'] == 'Y')//삭제
					{
						return send_xml($row, false, '삭제된 라이센스입니다.', '');
					}
					else if($row['reg_status'] == "R02")//진행
					{

						$update_q = "
							UPDATE	BC_LICENSE
							SET		REG_STATUS = 'R99'
									, CPU_ID = '".$cpu_id."'
									, OS = '".$os."'
									, MEMORY = '".$memory."'
									, SERVER_NM = '".$server_nm."'
									, CPU_INFO = '".$cpu_info."'
									, COMPLETE_DATE = '".$start_date."'
							WHERE	ID = '".$row['id']."'
						";

						$create_date = date('YmdHis');						
						$mac_id = $db->queryOne("SELECT MAX(ID) FROM BC_LICENSE_MAC_ADDR");
						foreach($mac_address->children() as $mac_item)
						{
							/*
							*2016.02.29 mjsong
							*AdapterName을 저장하기 위해서는 어트리뷰트가 아닌 요소로 XML 형식이 변경되어야 함. 성민효 문의 결과 불필요한 데이터로 판단되어 NULL처리
							*/
							//$adapter_nm = (string)$mac_item['AdapterName'];
							$mac_addr = (string)$mac_item;
							$mac_id = $mac_id+1;
							$insert_mac_q = "
								INSERT INTO BC_LICENSE_MAC_ADDR
								(ID, PROJ_ID, LICENSE_ID, ADAPTER_NAME, MAC_ADDR, CREATE_DATE, CREATE_USER)
								VALUES
								('".$mac_id."', '".$project_id."', '".$row['id']."', '', '".$mac_addr."', '".$create_date."', '')
							";
							$r = $db->exec($insert_mac_q);
						}

						$r = $db->exec($update_q);

						$row = $db->queryRow($q);
					}
					else{
						return send_xml($row, false, '상태가 불분명한 라이센스입니다.', '');
					}

					$rerutnXml = send_xml($row, true, 'ok', $requestXml);

					// $db->close();
					$db = null;

					return $rerutnXml;
				}
			}
		}
	}
	catch(Exception $e)
	{
		$code = '500';
		return return_error_code($code,$e->getMessage()); 
	}


//hash('sha512', $user['login_pwd'])

	
}

function send_xml($licenseData, $result, $msg, $requestXml=null)
{
	global $db;

	$date = date('Y-m-d');
	$datetime = date('Y-m-d H:i:s');
	$file_path='/logs/LicenseIFService_'.$date.'.log';

    @file_put_contents($_SERVER['DOCUMENT_ROOT'].$file_path,"\r\n[".$datetime."]\r\n ". print_r($licenseData, true) ."\r\n\r\n", FILE_APPEND);
	@file_put_contents($_SERVER['DOCUMENT_ROOT'].$file_path,"\r\n[".$datetime."]\r\n requestXml:\r\n". $requestXml ."\r\n\r\n", FILE_APPEND);

	$success = 'false';
	if($result){
		$success = 'true';
		$cpu_id = hash('sha512', $licenseData['cpu_id']);
		if(!empty($requestXml)){
			$requestXml_parse = simplexml_load_string(trim($requestXml));
		}
	}

	$response = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?> \n<response />");	
	$reulst = $response->addChild('result');
	$reulst->addAttribute('success', $success);
	$reulst->addAttribute('msg', $msg);

	if($result){
		$data = $response->addChild('data');
		$system = $data->addChild('system');
		$system->addChild('cpu_id', $licenseData['cpu_id']);
		$system->addChild('os', $licenseData['os']);
		$system->addChild('memory', $licenseData['memery']);
		//2016-02-26 불필요하다 판단하여 제외
		//$system->addChild('user_name', (string)$requestXml_parse->data->user_name);
		$system->addChild('pc_name', $licenseData['server_nm']);
		$system->addChild('cpu', $licenseData['cpu_info']);

		$mac_addr = $system->addChild('mac_address');
		$mac_datas = $db->queryAll("
			SELECT	ID
					,	ADAPTER_NAME
					,	MAC_ADDR
			FROM	BC_LICENSE_MAC_ADDR
			WHERE	PROJ_ID = '".$licenseData['proj_id']."'
			AND		LICENSE_ID = ".$licenseData['id']."
		");
		if(!empty($mac_datas)){
			foreach($mac_datas as $mac_row){
				$mac_addr->addChild('item', $mac_row['mac_addr']);
					//->addAttribute('AdapterName', $mac_row['adapter_name']);
			}
		}

		if($licenseData['license_type'] == 'LICENSE') {
			$license_type = 1;
		}
		else{
			$license_type = 0;
		}
		$data->addChild('license_type', $license_type);
			//->addAttribute('desc', '1은 라이센스, 0은 트라이얼 버전');

		$data->addChild('project_id', $licenseData['proj_id']);
			//->addAttribute('maxlength', '6');
		$product_id = $data->addChild('program_id', $licenseData['product_id']);
		//$product_id->addAttribute('maxlength', '5');
		//$product_id->addAttribute('desc', '제품id');

		$data->addChild('license', $licenseData['license_key']);
		$data->addChild('license_day', $licenseData['use_term']);
				//->addAttribute('desc', '라이센스 기간');

		if($licenseData['use_term'] == '91') { // 영구사용
			$data->addChild('start_date');
			$data->addChild('end_date');
		}
		else {
			//$end_date = date('YmdHis', strtotime($licenseData['create_date']) + ((int)$licenseData['use_term'] * 24 * 60 * 60));
			$end_date = date('YmdHis', strtotime(date('YmdHis')) + (((int)$licenseData['use_term'] + 1) * 24 * 60 * 60));

			//$data->addChild('start_date', $licenseData['create_date']);
			$data->addChild('start_date', (string)$requestXml_parse->data->start_date);
			$data->addChild('end_date', $end_date);
		}

		if(empty($licenseData['channel'])){
			$licenseData['channel'] = '1';
		}

		$date = date('c');
		$date_arr = explode('+', $date);
		$program_code = $date_arr[0].'.'.generateRenNum(2).trim($licenseData['channel']).'+'.$date_arr[1];

		$data->addChild('program_code', $program_code);

	}

	/*
	//요청 xml의 data를 그대로 return
	if($result&& !empty($requestXml_parse)){
		$data = $response->addChild('data', $requestXml_parse->data->asXml());
	}
	*/
	/*
	if($result&& !empty($requestXml_parse)){
		
		foreach($requestXml_parse as $obj) {
		    foreach($obj as $key => $value){
		    	$data->addChild($key, $value);
		    }
		}

	}
	*/

	$xml = $response->asXml();
    @file_put_contents($_SERVER['DOCUMENT_ROOT'].$file_path,"\r\n[".$datetime."]\r\n ". print_r($xml, true) ."\r\n\r\n", FILE_APPEND);

	return $xml;
}

function generateRenNum($length) { 
    $characters  = "123456789";        
    $rendom_str = ""; 
    $loopNum = $length; 
    while ($loopNum--) { 
        $rendom_str .= $characters[mt_rand(0, strlen($characters)-1)]; 
    } 
    return (string)$rendom_str; 
}

function return_error_code($code,$msg)
{
	$response = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?> \n<server_result />");	

	$response->addChild('status',$code);
	$response->addChild('message',$msg);

	$xml = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>","",$response->asXml());

	return $xml;
}


// $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : ''; 
// php 7부터는 $HTTP_RAW_POST_DATA를 지원하지 않는다. file_get_contents("php://input")를 사용해야 된다.
$server->service(file_get_contents("php://input"));
//$server->service(file_get_contents("php://input"));


?>