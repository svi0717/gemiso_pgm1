// GIT 연동 테스트
<?php
	$cmd_str = 'c:';
	$cmd_str = $cmd_str.' & cd C:\Gemiso_Licence\resources';
	$cmd_str = $cmd_str.' & MakeGMLicense.exe';
	$cmd_str = $cmd_str.' -p:P00003';
	$cmd_str = $cmd_str.' -g:G0004';
	$cmd_str = $cmd_str.' -d:91';
	$cmd_str = $cmd_str.' -c:10';
	$cmd_str = $cmd_str.' -l:1';
		
	exec($cmd_str, $output, $return);
	echo '$output : ';
	print_r($output);
	print_r($return);
	echo '<br>';
	
?>