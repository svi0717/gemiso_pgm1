<?php
//일부 define 정보를 xml로 변경

$doc = simplexml_load_file(ROOT.'/config.SYSTEM.xml');
$ip = $_SERVER['REMOTE_ADDR'];
$host = $_SERVER['REQUEST_SCHEME'];

$items = $doc->xpath("/root/items");
if(!empty($items)) {
	foreach($items as $item) {
		foreach($item as $key => $val) {
			if($key == 'UPLOAD_URL'){
				//내부/외부망 업로드 주소 아이피 뱔 분배 --사이트에 맞게 필요시 주석풀고 사용
				/*				
				if(strpos( $ip, '10.1') !== false){ //내부망일때
					define($key, (string)$val );
				}else{
					define($key, 'http://203.241.55.66:8080/upload' ); //외부망일시
				}
				*/
				define($key, (string)$val );
			}
			else if($key == 'STREAM_SERVER_IP'){
				//내부/외부망 업로드 주소 아이피 뱔 분배--사이트에 맞게 필요시 주석풀고 사용
				/*				
				if(strpos( $ip, '10.1') !== false){ //내부망일때
					define($key, (string)$val );
				}else{
					define($key, '203.241.55.66' ); //외부망일시
				}
				*/
				define($key, (string)$val );
			}			
			else{
				define($key, (string)$val );
			}
		}
	}
}
