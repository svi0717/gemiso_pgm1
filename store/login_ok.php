<?php
session_start();
header("Content-type: application/json; charset=UTF-8");


require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/functions.php');

$user_id = trim($_REQUEST['userName']);
$password = trim($_REQUEST['password']);
//$password = crypt(trim($_REQUEST['password']));

//익스포터 플러그인용 로그인 페이지 구분 2013-01-31 이성용
$flag = trim($_REQUEST['flag']);
$direct = trim($_REQUEST['direct']);
$code = trim($_REQUEST['code']);
$address = trim($_REQUEST['address']);
$url_params = array(
	'flag'		=> $_REQUEST['flag'],
	'address'	=> $_REQUEST['address'],
	'direct'	=> $_REQUEST['direct'],
	'code'		=> $_REQUEST['code']
);
try
{
	if(empty($user_id) || empty($password)) throw new Exception('아이디와 비밀번호를 입력해주세요.');

	$q = "
		SELECT *
		FROM BC_USER
		WHERE LOGIN_ID = '".$user_id."'
	";
	$user = $db->queryRow($q);

	if(strtoupper($user['use_yn']) == 'N')  throw new Exception("사용 중지된 사용자입니다.");
	if(empty($user)) throw new Exception('아이디가 맞지 않습니다.');
	if($user['login_pwd'] != $password) throw new Exception('아이디 또는 비밀번호가 맞지 않습니다.');

	$_SESSION['user'] = array(
		'user_id' => $user['login_id'],
		'member_id' => $user['id'],
		'lang' => $user['lang_cd'],
		'EN_NM' => $user['firstname']." ".$user['lastname'],
		'KOR_NM' => $user['lastname']." ".$user['firstname']
	);

	$target_page = 'browse.php';
	echo json_encode(array(
		'success' => true,
		'redirection' => $target_page,
		'user' => $user,
		'login_pwd' => hash('sha512', $user['login_pwd'])
	));

	//$db->close();
	$db = null;
}
catch (Exception $e)
{
	echo json_encode(array(
		'success' => false,
		'msg' => $e->getMessage(),
		'login_pwd' => hash('sha512', $user['login_pwd'])
	));
}

?>
