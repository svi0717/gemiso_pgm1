<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

if (empty($_SESSION['user'])) {
	$_SESSION['user'] = array(
		'user_id' => 'temp',
		'is_admin' => 'N',
		'groups' => array(
			//ADMIN_GROUP,
			//CHANNEL_GROUP
		)
	);
}
//어디서 페이지를 호출했는지에 대한 구분 2013-01-31 이성용
$flag = $_REQUEST['flag'];
$user_id = $_REQUEST['user_id'];
$direct = $_REQUEST['direct'];

if ($_SESSION['user']['user_id'] != 'temp' && $flag == '')
{
	echo "<script type=\"text/javascript\">window.location=\"browse.php\"</script>";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Gemini Soft - License Management</title>
<link rel="SHORTCUT ICON" href="Ariel.ico"/>

<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="ext/resources/css/login.css" />
    <script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="ext/ext-all.js"></script>
    <script type="text/javascript" src="/lib/CryptoJS/rollups/sha512.js"></script>

	<script type="text/javascript">

	function checkLogin(){
		var id = Ext.get('login-id').getValue();
		var pw = CryptoJS.SHA512(Ext.get('login-pw').getValue());

		Ext.Ajax.request({
			url: '/store/login_ok.php',
		params: {
			userName: id,
			password: pw,
			flag: '<?=$flag?>'
		},
		callback: function(opts, success, response){
			if (success)
			{
				try
				{
					var r = Ext.decode(response.responseText);

					if (r.success)
					{
						if(r.passchk){
							Ext.Msg.show({
								title: '확인',
								msg: '비밀번호가 설정되어 있지 않습니다.<br />비밀번호를 변경하여주세요.<br />마이페이지로 이동합니다.',
								icon: Ext.Msg.INFO,
								buttons: Ext.Msg.OK,
								fn: function(btnId){
									window.location = 'pages/mypage/index.php?mode=passchg';
								}
							});
						}else{
							window.location = '/'+r.redirection;
						}
					}
					else
					{
						Ext.Msg.show({
							title: '확인',
							msg: r.msg,
							icon: Ext.Msg.INFO,
							buttons: Ext.Msg.OK,
							fn: function(btnId, text, opts){
								Ext.get('login-id').focus(250);
							}
						});
					}
				}
				catch (e)
				{
					Ext.Msg.alert(e['title'], e['message']);
				}

			}
			else
			{
				//MN01098 '서버 오류'
				Ext.Msg.alert('서버 오류', response.statusText);
			}
		}
		});

		return false;
	}

	Ext.onReady(function(){
		Ext.get('login-id').focus();
		Ext.get('login-id').on('keydown', function(e, t, o){
			if (e.getKey() == e.ENTER)
			{
				e.stopEvent();
				checkLogin(Ext.get('login-id').getValue(), Ext.get('login-pw').getValue());
			}
		});
		Ext.get('login-pw').on('keydown', function(e, t, o){
			if (e.getKey() == e.ENTER)
			{
				e.stopEvent();
				checkLogin(Ext.get('login-id').getValue(), Ext.get('login-pw').getValue());
			}
		});
		Ext.get('login-submit').on('click', function(e, t, o){
				checkLogin(Ext.get('login-id').getValue(), Ext.get('login-pw').getValue());
			});
	});
	</script>

</head>

<body>

<BLOCKQUOTE style="MARGIN-TOP:13%;MARGIN-BOTTOM:13%;MARGIN-RIGHT:13%;MARGIN-LEFT:13%"></BLOCKQUOTE>

<div width="100%" style="background:#F5F6F7;">

	<form id="login-form" method="post">
		<fieldset class="login_form">
			<legend class="blind">loginForm</legend>
			<p>
				<img alt="" src="/ext/resources/gemini_logo_b.png" style="width: 200px; margin-bottom : 20px;">
			</p>
			<p>
				<input name="textfield" type="text" id="login-id" placeholder="아이디" />
			</p>
			<p>
				<input name="textfield2" type="PASSWORD" id="login-pw" placeholder="패스워드"/>
			</p>
			<p>
				<button type="submit" id="login-submit" class="login_btn">
					<span style="font-weight:bold; font-size: 14px; padding: 2px 0 0 0; color: #3f4a58;">로그인</span>
				</button>
			</p>		
			
		</fieldset>
	</form>
<p align="center">&nbsp;</p>
</div>
</body>
</html>

