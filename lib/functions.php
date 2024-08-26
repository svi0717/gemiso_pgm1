<?php

function createTopMenu(){
	global $db;

	$bar = '<li>&nbsp&nbsp&nbsp<img src="/ext/resources/images/default/etc.das/m_sun.gif">&nbsp&nbsp&nbsp</li>';

	$menu_array = array();

	//Home TopMenu
	$home_menu = '
	<li style="width:55">
		<div align="center">
			<a href="javascript:;" onClick="goHome();" onfocus="blur();" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'Image1\',\'\',\'/ext/resources/images/default/etc.das/menu-on_01.jpg\',1)">
				<img src="/ext/resources/images/default/etc.das/menu_01.jpg" name="Image1" width="28" height="32" border="0" id="Image1" />
				<center>홈으로</center>
			</a>
		</div>
	</li>';

	$license_menu = '
	<li >
		<div align="center">
			<a href="javascript:;" onClick="license();" onfocus="blur();" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'Image1\',\'\',\'/ext/resources/images/default/etc.das/menu-on_01.jpg\',1)">
				라이센스관리
			</a>
		</div>
	</li>';

	$code_menu = '
	<li>
		<div align="center">
			<a href="javascript:;" onClick="code_management();" onfocus="blur();" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'Image1\',\'\',\'/ext/resources/images/default/etc.das/menu-on_01.jpg\',1)">
				코드관리
			</a>
		</div>
	</li>';
	//기본 메뉴
	//array_push($menu_array, $home_menu);

	//array_push($menu_array, $license_menu);
	//array_push($menu_array, $code_menu);

	//$value = join($bar, $menu_array);
 	$value = join(' ', $menu_array);

	return $value;
}

// 사용자정의콘텐츠 별 콘텐츠 권한 함수 $ud_content_id
function checkAllowUdContentGrant( $user_id, $ud_content_id, $grant , $category_id = null )
{
	global $db;

	$grant_type = 'content_grant';

	if( is_null( $category_id ) )  $category_id = 0;

	$groups = $db->queryAll("select is_admin, member_group_id from bc_member m, bc_member_group_member mg
								where user_id='".$user_id."' and m.member_id = mg.member_id");
	if (empty($groups))
	{
		$group_id = $db->queryOne("select member_group_id from bc_member_group where is_default='Y'");
		$groups[] = array(
			'member_group_id' => $group_id
		);
	}

	foreach($groups as $group)
	{
		if( $group['is_admin'] == 'Y' ) return true; //그룹 중 관리자 권한이 있다면 true

		$group_grant = $db->queryOne("select group_grant from bc_grant " .
									"where ud_content_id=".$ud_content_id." ".
									" and member_group_id=".$group['member_group_id']." " .
									" and grant_type='$grant_type'".
									" and category_id='$category_id'");
		if( !empty($group_grant) )
		{
			if( $group_grant & $grant ) return true;
		}
	}

	return false;
}

// 사용자정의콘텐츠 별 콘텐츠 권한 함수 $ud_content_id
function hasGrant($user_id, $ud_content_id, $grant) {
	global $db;

	$groups = $db->queryAll("
		SELECT IS_ADMIN, MEMBER_GROUP_ID
   		  FROM BC_MEMBER M, BC_MEMBER_GROUP_MEMBER MG
		 WHERE USER_ID='".$user_id."'
		   AND M.MEMBER_ID = MG.MEMBER_ID
	");

	if (empty($groups)) {
		$group_id = $db->queryOne("select member_group_id from bc_member_group where is_default='Y'");
		$groups[] = array(
			'member_group_id' => $group_id
		);
	}

	// print_r($groups);

	foreach ($groups as $group) {

		// 그룹 중 관리자 권한이 있다면 true
		if ($group['is_admin'] == 'Y') {
			return true;
		}

		$group_grant = $db->queryOne("
			SELECT GROUP_GRANT
			  FROM BC_GRANT_ACCESS
			 WHERE UD_CONTENT_ID=".$ud_content_id."
			   AND MEMBER_GROUP_ID=".$group['member_group_id']."
			   AND GRANT_TYPE='grant_access'
	    ");

	    // echo '$group_grant: ' . $group_grant . ', $group[\'member_group_id\']: ' . $group['member_group_id']."\n";

		if ( ! empty($group_grant)) {
			if ($group_grant & $grant) {
				return true;
			}
		}
	}

	return false;
}

function esc2($str){
	$str = str_replace("\n", "", $str);
	$str = str_replace("\r", "", $str);

	return $str;
}

function includeAll($path)
{
	$result = array();

	$d = dir($path);
	while ( ($file = $d->read()) !== false )
	{
		if ( $file == '.' || $file == '..' ) continue;

		if ( is_dir($d->path.'/'.$file) )
		{
			array_merge($result, includeAll($d->path.'/'.$file));
		}
		else
		{
			array_push($result, $d->path.'/'.$file);
		}
	}

	return $result;
}

function stripExtensionOfFilename($filename){
	return substr($filename, 0, strrpos($filename, '.'));
}

function changeExtensionOfFile($file, $ext){
	return substr($file, 0, strrpos($file, '.')).'.'.$ext;
}

function secToTimecode($sec){
	$h = floor($sec / 3600);
	$i = ( floor($sec / 60) - ($h * 60) );
	$s = $sec % 60;

	return sprintf("%02d:%02d:%02d" , $h, $i, $s);
}

function frameToTimecode($frame)
{
	$sec = $frame / FRAMERATE;
	return secToTimecode($sec);
}

function executeQuery($q)
{
	global $db;

	$r = $db->exec($q);
}

function checkXMLSyntax($receive_xml)
{
	libxml_use_internal_errors(true);
	$rtn = simplexml_load_string($receive_xml);
	if (!$rtn) {
		foreach(libxml_get_errors() as $error)
		{
			$err_msg .= $error->message . "\n";
		}
		throw new Exception('xml 파싱 에러: '.$err_msg);
	}

	return $rtn;
}

function checkLogin()
{
	if ( $_SESSION['user']['user_id']=='temp' || $_SESSION['user']['user_id']=='' )
	{
		?>
		<script type="text/javascript">
		alert("Please log in first.");
		//alert("로그인 해주세요.");
		window.location = '/';
		</script>
		<?php
	}
}

function _debug($filename, $msg)
{
	global $is_debug;

	if ($is_debug)
	{
		//날짜별로 log파일이 생성되게 변경.
		$log_file = LOG_PATH.'/'.substr($filename, 0, strrpos($filename, '.')).'_'.date('Ymd').'.html';
		$log_msg = $_SERVER['REMOTE_ADDR'].'['.date('Y-m-d H:i:s').'] '.$msg.chr(10);

		file_put_contents($log_file, $log_msg, FILE_APPEND);
	}
}

function convertTime( $value )  /// 00:00:00 형식을 sec 로 변환
{
	if( strlen($value) == 8 )
	{
		$h = substr($value,0,2);
		$i = substr($value,3,2);
		$s = substr($value,6,2);
		$time = ($h*3600) + ($i*60) + $s;
	}
	else
	{
		return false;
	}
	return $time;
}

function calculationLength($start, $end) // 길이 계산, 리턴값 00:00:00
{

	$length_time = $end - $start;

	$h = (int)($length_time / 3600);
	$i = (int)(($length_time % 3600) / 60) ;
	$s = (int)(($length_time % 3600) % 60) ;

	$h = str_pad($h,2,'0', STR_PAD_LEFT);
	$i = str_pad($i,2,'0', STR_PAD_LEFT);
	$s = str_pad($s,2,'0', STR_PAD_LEFT);

	return $h.':'.$i.':'.$s;

}

function insertLog($action, $user_id, $content_id, $description)
{
	global $db;

	if(!empty($description))
	{
		$description = $db->escape($description);
	}

	$cur_datetime = date('YmdHis');
	$con_info = $db->queryRow("select bs_content_id, ud_content_id from bc_content where content_id = '$content_id'");

	$result = $db->exec("insert into bc_log (action, user_id, bs_content_id, ud_content_id, content_id, created_date, description) values ('$action', '$user_id','{$con_info['bs_content_id']}', '{$con_info['ud_content_id']}', '$content_id', '$cur_datetime', '$description')");
}
?>
