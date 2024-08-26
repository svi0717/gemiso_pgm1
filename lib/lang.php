<?php
session_start();
if( empty( $_SESSION['user']['lang'] ) ){
	if( empty($_REQUEST['lang']) ) {
		$defined_lang = 'en';
	} else {
		$defined_lang = $_REQUEST['lang'];
	}
}else{
	$defined_lang = $_SESSION['user']['lang'];
}
define('LANG', $defined_lang);

function _text($str_code)
{
	if (strstr($str_code, 'MN'))
	{
		$doc = simplexml_load_file(ROOT.'/../lib/lang/lang_cmd.xml');
		if (LANG == 'en')
		{
			$lang = $doc->xpath("/root/menu[mcode='$str_code']/emenu");
		}
		else if (LANG == 'ko')
		{
			$lang = $doc->xpath("/root/menu[mcode='$str_code']/hmenu");
		}
		else if(LANG == 'enko')
		{
			$ko_lang = $doc->xpath("/root/menu[mcode='$str_code']/hmenu");
			$en_lang = $doc->xpath("/root/menu[mcode='$str_code']/emenu");

			$lang = $ko_lang.'('.$en_lang.')';
		}
		else
		{
			throw new Exception('국가가 정의 되어있지 않습니다.');
		}
	}
	else if (strstr($str_code, 'MSG'))
	{
		$doc = simplexml_load_file(ROOT.'/../lib/lang/lang_msg.xml');
		if (LANG == 'en')
		{
			$lang = $doc->xpath("/root/items[msgcode='$str_code']/msgeng");
		}
		else if (LANG == 'ko')
		{
			$lang = $doc->xpath("/root/items[msgcode='$str_code']/msgkor");
		}
		else if(LANG == 'enko')
		{
			$ko_lang = $doc->xpath("/root/items[msgcode='$str_code']/msgkor");
			$en_lang = $doc->xpath("/root/items[msgcode='$str_code']/msgeng");

			$lang = $ko_lang.'('.$en_lang.')';
		}
		else
		{
			throw new Exception('국가가 정의 되어있지 않습니다.');
		}
	}
	else if (strstr($str_code, 'IMG'))
	{
		$doc = simplexml_load_file(ROOT.'/../lib/lang/image_path.xml');
		if (LANG == 'en')
		{
			$lang = $doc->xpath("/root/items[imgcode='$str_code']/imgeng");
		}
		else if (LANG == 'ko')
		{
			$lang = $doc->xpath("/root/items[imgcode='$str_code']/imgkor");
		}
		else
		{
			throw new Exception('국가가 정의 되어있지 않습니다.');
		}
	}
	else
	{
		throw new Exception('존재하지 않는 언어코드입니다.');
	}

	$lang = $lang[0];
	if (empty($lang))
	{
		throw new Exception('Undefined text');
	}

	return str_replace("'", "\\'", $lang);
}
?>