<?php
	// 2017.10.07 hkkim 에러 보이도록 설정
	$error_repoting = ~E_NOTICE;
	error_reporting($error_repoting);
	ini_set('display_errors', 'On');


	define('ROOT', dirname(__FILE__));
	define('BASEDIR', __DIR__.'/..');
	if(!defined('DS'))
		define('DS', DIRECTORY_SEPARATOR);

	require_once("config.SYSTEM.php");

	//require_once("MDB2.php");
	require_once(ROOT."/../lib/error_handler.php");

	if(DB_TYPE == 'oracle' ) 
	{		
		require_once(ROOT."/DBOracle.class.php");
		$mdb = new Database(DB_USER, DB_USER_PW, DB_HOST.':'.DB_PORT.'/'.DB_SID );
	} else {	
		require_once(ROOT."/DB.Class.php");
		$mdb = new CommonDatabase(DB_TYPE,DB_USER, DB_USER_PW, DB_SERVICE);
	}

	$GLOBALS['db'] = &$mdb;
	//$mdb_ktds = new Database('kt_news', 'ktpassword', '10.217.31.31/KTHOME');
	$GLOBALS['db_ktds'] = &$mdb_ktds;
	//$mdb_t = new Database('kt_news', 'ktpassword', '10.217.31.31/KTHOME');
	//$GLOBALS['dbt'] = &$mdb_t;

	//awesome 업로드 경로 설정 정의 추가..

	//define('AWESOME_ROOT', 'x:'); //drive letter
	define('AWESOME_ROOT', '//127.0.0.1/d$/storage'); //drive letter 끝에 / 빼기.
	define('AWESOME_DIR', 'attach'); //first path
	define('AWESOME_CHILD_DIR', 'attach_all'); //child path
	define('AWESOME_DOWNLOAD_PROXY_DIR', 'lowres');// for download proxyfile path

	//define('ORI_ROOT_PATH', '\\\\'.$_SERVER['HTTP_HOST'].'/Storage/highres/');// for Chromium browser Drag&Drop path.
	define('ORI_ROOT_PATH', 'Z:/highres/');// for Chromium browser Drag&Drop path.
	define('PROXY_ROOT_PATH', 'Z:/lowres/');// for Chromium browser Drag&Drop path.

	//define('SGL_ROOT', '\\\\192.168.1.54\\d$\\storage\\highres');
	define('SGL_ROOT', '//127.0.0.1/storage2/highres');
	define('SGL_PFR_ROOT', '//127.0.0.1/storage2/pfr');
	//define('SGL_PFR_ROOT', 'Z:/pfr');

	//define('SGL_ROOT', '\\\\sglapac10\\storage2\\highres');
	//define('SGL_ROOT', '\\\\192.168.1.125\\d$\\storage\\highres');
	define('FILER_ROOT', 'Z:/upload');
	define('EDIUS_ROOT', 'Z:/export');

	function getSequence($seq_name)
	{
		global $mdb;
		$seq_name = trim($seq_name);
		return $mdb->queryOne("select ".$seq_name.".nextval from dual");
	}

	function getNextSequence()
	{
		global $mdb;

		return $mdb->queryOne('select seq.nextval from dual');
	}



	function getNextTaskSequence()
	{
		global $db;

		return $db->queryOne('select task_seq.nextval from dual');
	}

	function getNpsNextSequence()
	{
		global $dbNps;

		return $dbNps->queryOne('select seq.nextval from dual');
	}
	function getNextNoticeSequence()
	{
		global $mdb;

		return $mdb->queryOne('select notice_seq.nextval from dual');
	}

	function getNextIngestSequence()
	{
		global $mdb;

		return $mdb->queryOne('select ingest_seq.nextval from dual');
	}

	function getNextCategorySequence()
	{
		global $mdb;

		return $mdb->queryOne('select category_seq.nextval from dual');
	}
	function getNextMetaMultiSequence()
	{
		global $mdb;

		return $mdb->queryOne('select meta_multi_seq.nextval from dual');
	}


	function getNextIngestMetaMultiSequence()
	{
		global $mdb;

		return $mdb->queryOne('select ingest_meta_multi_seq.nextval from dual');
	}

	function buildArchiveID($prefix, $seq_name)
	{
		global $db;

		$curr_date = date('Ymd');

		$result = $db->queryOne("select count(*) from log_archive_id where type='$prefix' and dt='$curr_date'");
		if ( $result == 0 )
		{
			$r = $db->exec("call p_reset_seq('$seq_name')");

			$db->exec("insert into log_archive_id values ('$prefix', '$curr_date')");
		}

		$seq = $db->queryOne('select archive_seq.nextval from dual');

		return "$prefix$curr_date-".sprintf('%05d', $seq);
	}

	ini_set('magic_quotes_runtime',     0);
	ini_set('magic_quotes_sybase',      0);

	## 카탈로그 옵션 값 설정 //add.php
	$trans_proxy_parameter = '"h.264" "AAC" "480" "270" "1000" "128"';
	$trans_download_parameter = '"WMV" "WMA" "640" "480" "1000" "128"';
	$catal_parameter = '"19" "2" "10"';
	$audio_parmeter = 'mp3';

	define('WEB_PATH', 'data');
	define('SERVER_IP',	'127.0.0.1');

	define('UPLOAD_ROOT',	'U:/');

	define('FRAMERATE',	30);

	define('HD_PROXY', '"h.264" "AAC" "480" "270" "1000" "128"');
	define('SD_PROXY', '"h.264" "AAC" "640" "480" "1000" "128"');
	define('DEFAULT_PROXY', '"h.264" "AAC" "480" "270" "1000" "128"');
	define('CJMALL_PROXY', '"WMV" "WMA" "640" "480" "1000" "128"');

	define('ROOT', 'C:/Archive');
	define('STREAM_FILE', 'proxy');
	define('ERROR_QUERY', 100);

	define('LOG_PATH', $_SERVER['DOCUMENT_ROOT'].'/log');

	//server IP DEFINE 2010/12.23. sungmin.
	//define('STREAMER_ADDR', 'rtmp://127.0.0.1/vod');
	$stream_ip = checkIP( $_SERVER['REMOTE_ADDR'] , 'stream' );
	$stream_ip = '127.0.0.1';
	//define('STREAMER_ADDR', 'rtmp://'.$stream_ip.'/vod');
	define('STREAMER_ADDR', 'http://'.$stream_ip.'/data');

	// 디버그
	define('DEBUG_ERROR',	5);

	// 섬네일 등록에 있는 디파인 이동 12/09 김성민
	DEFINE('FILE01', '//lowres/ifs/data/DAS');
	DEFINE('FILE02', '//lowres/ifs/data/DAS');

	define('WATCH_ROOT', 'Z:/watch/');

	// task agent
	define('TASK_TIMEOUT',		60);
	define('TASK_NOHAVEITEM',	20);
	define('TASK_ERROR',		10);


	//define('DASO_URL', 'http://192.168.41.152');
	define('FILESERVER', '\\\\127.0.0.1\\Archive\\');
	//backup스토리지 경로.
	//define('BACKUP_TARGET', 'Y:\\Archive');
	//define('GOM_PATH', 'C:\\\\Program Files\\\\GRETECH\\\\GomPlayer\\\\GOM.exe');

	define('ANONYMOUS_GROUP',	84018);
	define('CHANNEL_GROUP',		85443);
	define('NPS_GROUP',			85444);
	define('REVIEW_GROUP',		85445);
	define('PD_GROUP',			85548);
	define('MD_GROUP',			85549);
	define('CG_GROUP',			85550);
	define('NLE_GROUP',			85551);
	define('TECH_GROUP',		85553);
	define('PROUCT_GROUP',		85444);
	define('ADMIN_GROUP',		1);
	define('SANGROK_GROUP',		4251122);

	define('MOVIE', 506);
	define('SOUND', 515);
	define('IMAGE', 518);
	define('DOCUMENT', 57057);
	define('SEQUENCE', 4582237);

	//define('PAST_BROADCAST', 81768); //지난방송
	//define('BROADCAST_DATETIME', 81865); //방송일시

	define('ARIEL_CATALOG',					10);
	define('ARIEL_THUMBNAIL_CREATOR',		11);
	define('ARIEL_TRANSCODER',				20);
	define('ARIEL_TRANSCODER_hi',			21);
	define('ARIEL_IMAGE_TRANSCODER',		22);
	define('ARIEL_PATIAL_FILE_RESTORE',		30);
	define('ARIEL_REWARPPING',				31);
	define('ARIEL_MXF_VALIDATE',			34);
	define('ARIEL_AVID_TRANSCODER',			40);
	define('ARIEL_TRANSFER_FS',				60);
	define('ARIEL_TRANSFER_FS_TO_NEARLINE', 61);
	define('ARIEL_TRANSFER_FS_NPSTODAS',	62);
	define('ARIEL_TRANSFER_YTN',			63);
	define('ARIEL_TRANS_AUDIO',				70);
	define('ARIEL_TRANSFER_FTP',			80);
	define('ARIEL_FTP_DCART',				81);
	define('ARIEL_HIGH_TRANSCODER',			90);
	define('ARIEL_DELETE_JOB',				100);
	define('ALTO_ARCHIVE',					110);
	define('LTO5_ARCHIVE',					111);

	define('ARCHIVE',						110);
	define('ARCHIVE_DELETE',				150);
	define('RESTORE',						160);
	define('RESTORE_PFR',					140);
	define('ARCHIVE_LIST',					170);



	define('REG_COMPLETE', 0);
	define('REG_WATCH_FOLDER_QUEUE', 1);
	define('REG_WATCH_FOLDER_PROCESSING', 2);

	define('FTP_ADDRESS', '192.168.1.20');
	define('FTP_PORT', 21);
	define('FTP_LOGIN_ID', 'admin');
	define('FTP_LOGIN_PW', 1);


	define('PRE_PRODUCE',		81722);	//TV방송프로그램,DMC
	define('CLEAN',				81767);	//TV인서트, 소재영상
	define('PAST_BROADCAST',	81768);	//참조영상
	define('D_CART_TABLE_ID', 	81769);	//음원
	define('VOD_CLIP',			83413);	//분절영상--(EBS에선 사용하지 않는 테이블아이디)
	define('LIBRARY_CLIP',	  4023846); //R.라디오방송프로그램

	define('LIBRARY_IMG',			81771);		//라이브러리이미지
	define('FIRST_IMG',				81770);		//1차 이미지
	define('SECOND_IMG',			85561);		//2차 이미지
	define('DOCS',						81772);		//문서
	/* KBN KT 사내방송 */
	define('UD_BROD',			1);//등록영상
	define('UD_EDITING',		5);//편집영상
	define('UD_METERIAL',		28);//촬영영상
	define('UD_REC',			48);//녹화영상

	define('CATEGORY_WEB',		108);//웹등록 카테고리

	define('ARRAY_META_TITLE', '33,48,64,91,111,131');//제목에 해당하는 메타ID
	/* KBN KT 사내방송 */

	# Diva
	//define('DIVA_RESTORED',		0);
	//define('DIVA_RESTORE',		1);
	//define('DIVA_RESTORING',	2);
	//define('DIVA_ARCHIVED',		3);
	//define('DIVA_ARCHIVE',		4);
	//define('DIVA_ARCHIVING',	5);
	//define('DIVA_ERROR',		6);

	//define('HARRIS_MAIN',					1);
	//define('HARRIS_SUB',					2);

	//define('CODE_TANSFER_CJMALL',			1);
	//define('CODE_TRANSFER_HARRIS_MAIN',	2);
	//define('CODE_TRANSFER_HARRIS_SUB',	3);
	define('CODESET_CHANNEL_NAME',			1);

	// 콘텐츠 상태 ( status ) //2010.12.16 김성민. 2번까지 상태값 디파인.
	define('FILER_REG_STATUS',				-2);	// NPS to DAS전송시 작업완료전까지의 상태
	define('CONTENT_STATUS_REG_HIDDEN',		-2);	// NPS to DAS전송시 작업완료전까지의 상태
	define('INGEST_READY',					-3);//인제스트리스트 등록시
	define('ORACLE_MIGRATION_STATUS',		-4); //(2011/01/12 조훈휘 추가)에이전트에 등록을 위한 상태값
	define('INGEST_LIST_STATUS',			-3);//인제스트리스트 등록시
	define('SUB_CONTENT_STATUS',			-7);//인제스트리스트 등록시
	define('WATCH_FLODER_REGIST',			-1);//와치폴더등록시 상태값
	define('REGIST_TEST_HIDDEN',			-4);//민효 테스트용
	define('CONTENT_STATUS_REG_READY',		 2);	// 등록 대기 상태 (dmc전송완료일때 사용중,DAS에서 등록하는모든콘텐츠는 등록대기를거침,2011년 1월 10일 인제스트 작업을 위한 상수에 쓰임)
	define('CONTENT_STATUS_REFUSE',			-5); //반려 시 상태값
	define('CONTENT_STATUS_REACCEPT',		-6); //재승인 요청 상태값 2011-02-22 by 이성용
	define('INGEST_COMPLETE',				1);
	define('INGEST_STATUS',					1); // 인제스트등록시
	define('CONTENT_STATUS_COMPLETE',		2); // 등록완료 상태
	define('CONTENT_STATUS_REVIEW_READY',	3);
	define('CONTENT_STATUS_REVIEW_ACCEPT',	4);
	define('CONTENT_STATUS_REVIEW_RETURN',	5);
	define('CONTENT_STATUS_REVIEW_HALF',	6);
	define('d_cart_regist_complete',		7); // d_cart전송완료 상태

	//ex) 사용자 그룹 3번에 읽기, 쓰기, 중해상도 다운로드 3가지 권한을 준다면
	//		GRANT_READ + GRANT_WRITE + GRANT_MR_DOWNLOAD = 19가 된다.(BC_GRANT테이블의 group_grant 필드값.)
	define('GRANT_READ', 1);
	define('GRANT_WRITE', 2);
	define('GRANT_DELETE', 4);
	define('GRANT_DOWNLOAD', 8);
	define('GRANT_REWRAPPER', 16);
	define('GRANT_ARCHIVE', 32);
	define('GRANT_RESTORE', 64);
	define('GRANT_TRANS', 128);
	define('GRANT_PFR', 256);
	define('GRANT_NEWCONTENT', 512);
	define('GRANT_ACCEPT', 1024);
	define('GRANT_HI_TRANS', 2048);

	define('GRANT_ACCESS_CREATE', 1);
	define('GRANT_ACCESS_READ',   2);
	define('GRANT_ACCESS_UPDATE', 4);
	define('GRANT_ACCESS_DELETE', 8);

	define('CONFIG_THUMB_PREVIEW_LIMIT', 6); // 썸네일 수   위치 : /store/get_content_list/libs/functions.php
	define('CONFIG_THUMB_DIV_WIDTH', 403);    // 쎔네일 표시 div 가로길이 위치 : /store/get_content_list/libs/functions.php
	define('CONFIG_THUMB_DIV_HEIGHT', 240);   // 쎔네일 표시 div 세로길이 위치 : /store/get_content_list/libs/functions.php
	define('CONFIG_QTIP_WIDTH', 400);         // qtip 길이 위치 :  /pages/browse/content.php
	define('CONFIG_THUMB_IMG_WIDTH', 120);     // 썸네일 이미지  가로길이 위치 : /store/get_content_list/libs/functions.php

	define('LoginTimer_LifeTime',  43200);//로그인허용시간(초)
	define('LoginTimer_MsgTime',  30);//메시지박스허용시간(초)
	define('LoginTimer_CheckTime',  30);//주기적인 세션확인시간(초)
	define('LoginTimer_Dupl',  'true');// 중복로그인체크(true : 중복로그인 X)
	define('LoginTimer_IsUseMsg',  'true');//타이머가 0이 되었을때 띄울 메시지박스 사용여부 (true : 사용) );

	// 2011-12-23
	///상태값 3,4 를 추가  by 허광회
	//콘텐츠 상태값
	//define('CONTENT_STATUS_REG_READY',		0); //등록 대기
	//define('CONTENT_STATUS_REVIEW_READY',	1); //심의 대기
	//define('CONTENT_STATUS_REG_COMPLETE',	2); //등록 완료 : 기술 심의, 내용 심의 대상에 따라 둘 다 대상이면 둘 다 완료 되어야 등록 완료로 변경된다.
	define('CONTENT_STATUS_DELETE_REQEUST',	3); //삭제 요청 :  사용자 요청으로 상태값이 변경된다.
	define('CONTENT_STATUS_DELETE_EXPIRE',  4); //삭제 요청 :  기한만료가 되면 상태값이 변경된다.
	define('CONTENT_STATUS_DELETE_APPROVE', 5); //삭제 승인 : 콘텐츠의 기한만료나 사용자 요청으로 삭제 요청을 승락하면 이값으로 변경된다.
	define('CONTENT_STATUS_DELETE_COMPLETE',6); //삭제 완료 : 콘텐츠의 관리자의 승인으로 삭제 완료된 상태값


	//사용자 요구로 인한 처리를 관리자 or 자동 FLAG
	// true 면 관리자 승인없이 자동 처리 되도록함
	define('DELETE_USER_REQUEST_FLAG',true);

	//미디어 상태값
	define('del_complete_code','DC'); // 미디어파일 삭제상태
	define('del_error_code','DO'); // 미디어파일 삭제 에러상태
	define('del_admin_approve_code','DA'); //미디어파일 관리자 승인상태
	define('del_request_code','DR'); //미디어파일 사용자가 삭제 요청상태
	define('del_limit_code','DL'); //미디어파일 만료상태

	define('DEL_MEDIA_COMPLETE_FLAG','DMC'); //미디어 파일 삭제 완료 상태
	define('DEL_MEDIA_ERROR_FLAG','DME'); // 미디어 파일 에러 상태
	define('DEL_MEDIA_REQUEST_FLAG','DMR'); //미디어 파일 사용자 요청 상태
	define('DEL_MEDIA_DATE_EXPIRE_FLAG','DME'); //미디어 파일 만료 상태
	define('DEL_MEDIA_CONTENT_REQUEST_FLAG','DCR'); // 콘텐츠의 삭제 요청으로인한 미디어 삭제요청상태
	define('DEL_MEDIA_CONTENT_EXPIRE_FLAG','DCE'); // 콘텐츠의 기한만료로 인한 미디어 삭제요청상태

	//다음날 삭제 처리할 파일 FLAG형태
	define('DEL_MEDIA_AUTO_APPROVE_FLAG','DAA'); // 콘텐츠 자동승인 상태
	define('DEL_MEDIA_ADMIN_APPROVE_FLAG','DMA'); //미디어 파일 관라자 승인 상태


	#============================================
	# log aciton 정의

	# accept			//콘텐츠 승인
	# transferFS		//FS전송
	# edit				//콘텐츠 수정
	# regist			//콘텐츠 등록
	# rewarpping
	# transcodingM		//트랜스코딩
	# accept_request	//재승인요청
	# transcodingA		//트랜스코딩
	# refuse_encoding	//콘텐츠 반려 인코딩문제
	# catalog			//카달로그 등록
	# login				//로그인
	# dastonps			//DAS To NPS 전송
	# DMC				//DMC 등록
	# read				//읽기
	# transferFTP		/FTP 등록
	# delete			//콘텐츠 삭제 - content 테이블의 is_deleted => 1 업데이트
	# npstodas			//NPS To DAS 전송
	# refuse_meta		//콘텐츠 반려 메타데이터문제
	# download			//콘텐츠 다운로드
	#sub_content_regist	//가상클립 등록

	#============================================

	# content_type 정의
	# 영상물 = 506
	# 음성   = 515
	# 이미지 = 518
	# 문서   = 57057
	# 기타   = 643

	#Manager 작업 코드/////////////////////////////////////////////////
	#define ARIEL_CATALOG               10
	#define ARIEL_TRANSCODER            20
	#define ARIEL_PATIAL_FILE_RESTORE   30
	#define ARIEL_AVID_TRANSCODER       40
	#define ARIEL_TRANSFER_FS           60
	#define ARIEL_TRANS_AUDIO           70
	#define ARIEL_TRANSFER_FTP          80

	#<Medias><media Type=""/></Medias>에서 media의 Type 정의///////////
	#original	:원본 미디어
	#thumb		:이미지 미디어 일시의 섬네일
	#proxy		:영상 미디어의 프록시 영상 등록시
	#text		:문서 미디어의 내용데이터
	#동영상일 경우 <media>태그가 original 한줄뿐이지만 이미지 일경우 <media>태그가 두줄. <media Type"original" /><media Type\"thumbnail">

	//get_queue.php 에 시스템메타 넘버 정의 필요


	#등록대기 = 0 (default)
	#등록요청 = 1
	#심의비대상(등록완료) = 2
	#심의대기중 = 3
	#승인 = 4
	#반려 = 5
	#조건부승인 = 6

	#harris -
	#Y/P :사전제작
	#Y/C : 클린
	#Y/R : 지난방송
	#Y/V : VOD


	function checkIP( $remote_ip , $type = null )
	{
		$port = $_SERVER["SERVER_PORT"];

		if( $type == 'board' ) $port = '8090';

		if( empty($remote_ip) ) $remote_ip = $_SERVER["REMOTE_ADDR"];

		$original_mam_ip = $_SERVER['SERVER_ADDR'].':'.$port;

		$original_ftp1_ip = '10.10.10.161';
		$original_ftp2_ip = '10.10.10.162';
		$original_stream1_ip = $_SERVER['SERVER_ADDR'];
		$original_stream2_ip = '10.10.10.72';

		//사내망 매핑 아이피
		$inner_mam_ip = '172.22.0.41'.':'.$port;
		$inner_ftp1_ip = '172.22.0.42';
		$inner_ftp2_ip = '172.22.0.43';
		$inner_tempDB_ip = '172.22.0.47';
		$inner_stream1_ip = '172.22.0.81';
		$inner_stream2_ip = '172.22.0.82';

		//EDRB 및 다운로드 단말용
		$edrb_mam_ip = '192.168.200.2'.':'.$port;
		$edrb_stream1_ip = '192.168.200.3';
		$edrb_stream2_ip = '192.168.200.4';
		$edrb_ftp1_ip = '192.168.200.5';
		$edrb_ftp2_ip = '192.168.200.6';

		//뉴스
		$news_mam_ip = '10.10.200.2'.':'.$port;
		$news_stream1_ip = '10.10.200.3';
		$news_stream2_ip = '10.10.200.4';
		$news_ftp1_ip = '10.10.200.5';
		$news_ftp2_ip = '10.10.200.6';

		$project_mam_ip = '61.103.112.105'.':'.$port;

		//nps 웹
		$nps_mam_ip_1 = '10.11.10.171';

		//nps사내망
		$nps_inner_ip_1 = '172.24.0.42';

		if( !is_null($type) )
		{
			switch($type)
			{
				case 'ftp':
					$ip_array = explode('.', trim($remote_ip) );

					if( count($ip_array) > 0 )
					{
						if( $ip_array[0] == '172' && ( $ip_array[1] >= '21' ) &&  ( $ip_array[1] <= '31' ) )
						{//사내망
							return $inner_ftp1_ip;
						}
						else if( ( $ip_array[0] == '192' ) && ( $ip_array[1] == '168' )  && ( $ip_array[2] == '200' )  )
						{//EDRB다운로드
							return $edrb_ftp1_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '200' ) )
						{//뉴스
							return $news_ftp1_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '11' ) )
						{//NPS
							return $original_ftp1_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '10' ) )
						{//내부
							return $original_ftp1_ip;
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}

				break;

				case 'stream':
					$ip_array = explode('.', trim($remote_ip) );

					if( count($ip_array) > 0 )
					{
						return $original_stream1_ip;
					}
					else
					{
						return false;
					}

				break;

				case 'ftp_radio':
					$ip_array = explode('.', trim($remote_ip) );

					if( count($ip_array) > 0 )
					{
						if( $ip_array[0] == '172' && ( $ip_array[1] >= '21' ) &&  ( $ip_array[1] <= '31' ) )
						{//사내망
							return $inner_ftp1_ip;
						}
						else if( ( $ip_array[0] == '192' ) && ( $ip_array[1] == '168' )  && ( $ip_array[2] == '200' )  )
						{//EDRB다운로드
							return $edrb_ftp1_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '200' ) )
						{//뉴스
							return $news_ftp1_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '11' ) )
						{//NPS
							return $original_ftp1_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '10' ) )
						{//내부
							return $original_ftp1_ip;
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}

				break;

				case 'nps':
					$ip_array = explode('.', trim($remote_ip) );

					if( count($ip_array) > 0 )
					{
						if( $ip_array[0] == '172' && ( $ip_array[1] >= '21' ) &&  ( $ip_array[1] <= '31' ) )
						{//사내망
							return $nps_inner_ip_1;
						}
						else if( ( $ip_array[0] == '192' ) && ( $ip_array[1] == '168' )  && ( $ip_array[2] == '200' )  )
						{//EDRB다운로드
							return $nps_mam_ip_1 ;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '200' ) )
						{//뉴스
							return $nps_mam_ip_1 ;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '11' ) )
						{//NPS
							return $nps_mam_ip_1 ;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '10' ) )
						{//내부
							return $nps_mam_ip_1 ;
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}

				break;

				case 'board':
					$ip_array = explode('.', trim($remote_ip) );

					if(count($ip_array) > 0 )
					{
						if( $ip_array[0] == '172' && ( $ip_array[1] >= '21' ) &&  ( $ip_array[1] <= '31' ) )
						{//사내망
							return $inner_mam_ip;
						}
						else if( ( $ip_array[0] == '192' ) && ( $ip_array[1] == '168' )  && ( $ip_array[2] == '200' )  )
						{//EDRB다운로드
							return $edrb_mam_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '200' ) )
						{//뉴스
							return $news_mam_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '11' ) )
						{//NPS
							return $original_mam_ip;
						}
						else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '10' ) )
						{//내부
							return $original_mam_ip;
						}
						else if( ( $ip_array[0] == '61' ) && ( $ip_array[1] == '39' )  && ( $ip_array[2] == '4' ) )
						{
							//프로젝트룸
							return $project_mam_ip;
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}
				break;

				default:
					return false;
				break;
			}
		}
		else
		{
			$ip_array = explode('.', trim($remote_ip) );

			if(count($ip_array) > 0 )
			{
				if( $ip_array[0] == '172' && ( $ip_array[1] >= '21' ) &&  ( $ip_array[1] <= '31' ) )
				{//사내망
					return $inner_mam_ip;
				}
				else if( ( $ip_array[0] == '192' ) && ( $ip_array[1] == '168' )  && ( $ip_array[2] == '200' )  )
				{//EDRB다운로드
					return $edrb_mam_ip;
				}
				else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '200' ) )
				{//뉴스
					return $news_mam_ip;
				}
				else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '11' ) )
				{//NPS
					return $original_mam_ip;
				}
				else if( ( $ip_array[0] == '10' ) && ( $ip_array[1] == '10' ) && ( $ip_array[2] == '10' ) )
				{//내부
					return $original_mam_ip;
				}
				else if( ( $ip_array[0] == '61' ) && ( $ip_array[1] == '39' )  && ( $ip_array[2] == '4' ) )
				{
					//프로젝트룸
					return $project_mam_ip;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}
?>
