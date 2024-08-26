<?php
session_start();
//extract($_REQUEST);

//ini_set('display_errors', 0);
header('Content-Type: text/html; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/lang.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/lib/lib.php');

//로그아웃시 창이 나오지 않고 바로 홈으로 갈수 있도록 수정
//2010 12 20 조훈휘
if (!$_SESSION['user']){
	echo "<script>alert(_text('MSG01001'))</script>";
	echo "<script>location.href='/index.php'</script>";
	exit;
}

$user_data = $db->queryRow("
	select	firstname
			, lastname
			, user_level
			, job_grade
			, phone
			, email
			, use_yn
			, lang_cd
	from	bc_user 
	where	login_id = '".$_SESSION['user']['user_id']."'
");
?>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Gemini Soft - License Management</title>

	<script src="javascript/zeroclipboard-2.2.0/dist/ZeroClipboard.js"></script>
	<script src="javascript/lang.js"></script>
	<script type="text/javascript">
		var lang_cd = "<?=$user_data['lang_cd']?>";
		LANG = lang_cd.trim().toLowerCase();
	</script>

	<link rel="SHORTCUT ICON" href="/Ariel.ico"/>
    <link rel="stylesheet" type="text/css" href="/ext/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="/css/xtheme-gemini.css" />
	<link rel="stylesheet" type="text/css" href="/css/xtheme-gray.css" />
    <link rel="stylesheet" type="text/css" href="/ext/examples/ux/css/Portal.css" />
    <link rel="stylesheet" type="text/css" href="/ext/examples/ux/css/MultiSelect.css" />
	<link rel="stylesheet" type="text/css" href="/ext/examples/ux/css/ProgressColumn.css" />

	<link rel="stylesheet" type="text/css" href="/javascript/timepicker/Ext.ux.Spinner/resources/css/Spinner.css" />
	<link rel="stylesheet" type="text/css" href="/javascript/timepicker/Ext.ux.TimePicker/resources/css/TimePicker.css" />

	<link rel="stylesheet" type="text/css" href="/ext/resources/css/main_top.css" />

	<link rel="stylesheet" type="text/css" href="/ext/resources/css/font-awesome-4.4.0/css/font-awesome.css" />

	<script type="text/javascript" src="/lib/CryptoJS/rollups/sha512.js"></script>
</head>

<body>
	<div id="loading-mask"></div>
	<div id="loading">
		<div class="loading-indicator"><img src="/images/loadinfo.net.gif" width="32" height="32" style="margin-right:8px;float:left;vertical-align:top;"/>Proxima Engine 2.0 - <span id="loading-msg">Loading styles and images...</span></div>
	</div>
 
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Loading Core API...';</script>
    <script type="text/javascript" src="/ext/adapter/ext/ext-base.js"></script>

    <script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Loading UI Components...';</script>
    <script type="text/javascript" src="/ext/ext-all.js"></script>

    <script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Initializing...';</script>

	
	<script type="text/javascript">
		function MM_swapImgRestore() { //v3.0
			var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
		}
		function MM_preloadImages() { //v3.0
			var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
			var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
			if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
		}

		function MM_findObj(n, d) { //v4.01
			var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
			d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
			if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
			for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
			if(!x && d.getElementById) x=d.getElementById(n); return x;
		}

		function MM_swapImage() { //v3.0
			var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
			if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
		}
    </script>
	<style type="text/css">

		/* progress */
		.x-grid3-td-progress-cell .x-grid3-cell-inner {
			font-weight: bold;
		}

		.x-grid3-td-progress-cell .high {
			background: transparent url(/ext/examples/ux/images/progress-bg-green.gif) 0 -33px;
		}

		.x-grid3-td-progress-cell .medium {
			/*background: transparent url(/ext/examples/ux/images/progress-bg-orange.gif) 0 -33px;*/
			background: transparent url(/ext/examples/ux/images/progress-bg-middle.gif) 0 -33px;
		}

		.x-grid3-td-progress-cell .low {
			/*background: transparent url(/ext/examples/ux/images/progress-bg-green.gif) 0 -33px;*/
			background: transparent url(/ext/examples/ux/images/progress-bg-low.gif) 0 -33px;
		}

		.x-grid3-td-progress-cell .ux-progress-cell-foreground {
			color: #fff;
		}
		/*progress끝*/

		.app-msg .x-box-bl, .app-msg .x-box-br, .app-msg .x-box-tl, .app-msg .x-box-tr {
			background-image: url(/images/box-round-images/corners.gif);
		}

		.app-msg .x-box-bc, .app-msg .x-box-mc, .app-msg .x-box-tc {
			background-image: url(/images/box-round-images/tb.gif);
		}

		.app-msg .x-box-mc {
			color: darkorange;
			background-color: #c3daf9;
		}
		.app-msg .x-box-mc h3 {
			color: red;
		}

		.app-msg .x-box-ml {
			background-image: url(/images/box-round-images/l.gif);
		}

		.app-msg .x-box-mr {
			background-image: url(/images/box-round-images/r.gif);
		}

		.custom-nav-tab {
			background-color: #BDBDBD;
			padding: 0 0 0 4;
		}

		.tab-over-cls {
			background-color:red;
		}


		/* I'm not happy to have to include this hack but since we're using floating elements */
		/* this is needed, if anyone have a better solution, please keep me posted! */
		/*
		.x-grid3-body:after { content: "."; display: block; height: 0; font-size: 0; clear: both; visibility: hidden; }
		.x-grid3-body { display: inline-block; }
		*/
		/* Hides from IE Mac \*/
		/*
		* html .x-grid3-body { height: 1%; }
		.x-grid3-body { display: block; }
		*/
		/* End Hack */
		/*!
		 * Ext JS Library 3.0.0
		 * Copyright(c) 2006-2009 Ext JS, LLC
		 * licensing@extjs.com
		 * http://www.extjs.com/license
		 */
		 .x-grid3-col-title{
			text-align: left;
		 }
		.x-grid3-td-title b {
			font-family:tahoma, verdana;
			display:block;
		}
		.x-grid3-td-title b i {
			font-weight:normal;
			font-style: normal;
			color:#000;
		}
		.x-grid3-td-title .x-grid3-cell-inner {
			white-space:normal;
		}
		.x-grid3-td-title a {
			color: #385F95;
			text-decoration:none;
		}
		.x-grid3-td-title a:hover {
			text-decoration:underline;
		}
		.details .x-btn-text {
			background-image: url(details.gif);
		}
		.x-resizable-pinned .x-resizable-handle-south{
			background:url(/ext/resources/images/default/sizer/s-handle-dark.gif);
			background-position: top;
		}
		.x-grid3-row-body p {
			margin:5px 5px 10px 5px !important;
		}
		.x-grid3-col-fileinfo{
			text-align: right;
		}

		.inner-body {
			margin: 0;
			padding: 0;
			background: #1b1b1b url(/images/web_bg_blue.jpg) top left repeat-x;
			font-family: Arial;
			font-size: 0.8em;
			width:100%;
		}

		.icon-506 {
			background-image:url(/led-icons/film.png) !important;
		}

		.icon-515 {
			background-image:url(/led-icons/music.png) !important;
		}

		.icon-57057 {
			background-image:url(/led-icons/book.png) !important;
		}

		.icon-518 {
			background-image:url(/led-icons/image_1.png) !important;
		}

		/* I'm not happy to have to include this hack but since we're using floating elements */
		/* this is needed, if anyone have a better solution, please keep me posted! */
		.x-grid3-body:after { content: "."; display: block; height: 0; font-size: 0; clear: both; visibility: hidden; }
		.x-grid3-body { display: inline-block; }
		/* Hides from IE Mac \*/
		* html .x-grid3-body { height: 1%; }
		.x-grid3-body { display: block; }
		/* End Hack */

		/*섬네일+리스트보기*/
		.ux-explorerview-detailed-icon-row { width: <?=$list_box_width?>; height: <?=$list_box_height?>; float: left; margin: <?=$list_box_margin?>; border: none; border: 1px solid #DCDCDC; }
		.ux-explorerview-detailed-icon-row .x-grid3-row-table { width: 100%; height: 65px; }
		.ux-explorerview-detailed-icon-row .x-grid3-row-table td.ux-explorerview-icon { width: 80px;  border: 0px solid blue; }
		.ux-explorerview-detailed-icon-row .x-grid3-row-table td.ux-explorerview-icon img { border: 0px solid yellow; margin: auto 0 auto 0; background-color:black; }
		.ux-explorerview-detailed-icon-row .x-grid3-row-table .x-grid3-col x-grid3-cell .x-grid3-cell-inner {  }

		/*섬네일보기*/
		.ux-explorerview-large-icon-row { width: <?=$thumb_box_width?>; height: <?=$thumb_box_height?>; float: left; margin: <?=$thumb_box_margin?>; border: 1px solid #DCDCDC; }
		.ux-explorerview-large-icon-row .x-grid3-row-table { width: 100%; }
		.ux-explorerview-large-icon-row .x-grid3-row-table td { text-align: center; }
		.x-grid3-row ux-explorerview-large-icon-row .x-grid3-row-table .x-grid3-col x-grid3-cell ux-explorerview-icon {}

		/*.x-grid3-row .ux-explorerview-large-icon-row x-grid3-row-first x-grid3-row-selected*/
		/*.x-grid3-row .ux-explorerview-large-icon-row .x-grid3-row-selected {*/

		.x-grid3-row-selected{
			border: 1px solid #00CCFF !important;
			background-color: #99CCFF !important;
		}

		/*
		수정일 : 2010.12.20
		작성자 : 김형기
		내용 : 콘텐츠 리스트에 마우스 오버 시 색상 설정
		*/
		.x-grid3-row-over {
			border: 1px solid #00CCFF !important;
			background-image: none !important;
		}

		.x-tool-detail {background-image: url(/led-icons/application_view_detail.png) !important;}
		.x-tool-list {background-image: url(/led-icons/application_view_list.png) !important;}
		.x-tool-tile {background-image: url(/led-icons/application_view_tile.png) !important;}

		.gridBodyNotifyOver {
			border-color: #00cc33 !important;
		}
		.gridRowInsertBottomLine {
			border-bottom:1px dashed #00cc33;
		}
		.gridRowInsertTopLine {
			border-top:1px dashed #00cc33;
		}

		#loading-mask{
			position:absolute;
			left:0;
			top:0;
			width:100%;
			height:100%;
			z-index:20000;
			background-color:white;
		}

		#loading{
			border: 1px solid black;
			position:absolute;
			left:45%;
			top:40%;
			padding:2px;
			z-index:20001;
			height:auto;
		}
		#loading a {
			color:#225588;
		}
		#loading .loading-indicator{
			background:white;
			color:#444;
			font:bold 13px tahoma,arial,helvetica;
			padding:10px;
			margin:0;
			height:auto;
		}
		#loading-msg {
			font: normal 10px arial,tahoma,sans-serif;
		}

		#images-view .x-panel-body{
			background: white;
			font: 11px Arial, Helvetica, sans-serif;
		}
		#images-view .thumb{
			background: #dddddd;
			padding: 3px;
		}
		#images-view .thumb img{
			height: 83px;
			width: 150px;
		}
		#images-view .thumb-wrap{
			float: left;
			margin: 4px;
			margin-right: 0;
			padding: 5px;
		}
		.comments{
			background-color: blue;
		}
		#images-view .thumb-wrap span{
			display: block;
			overflow: hidden;
			text-align: center;
		}

		#images-view .x-view-over{
			border:1px solid #dddddd;
			background: #efefef url(../../resources/images/default/grid/row-over.gif) repeat-x left top;
			padding: 4px;
		}

		#images-view .x-view-selected{
			background: #eff5fb url(images/selected.gif) no-repeat right bottom;
			border:1px solid #99bbe8;
			padding: 4px;
		}
		#images-view .x-view-selected .thumb{
			background:transparent;
		}

		#images-view .loading-indicator {
			font-size:11px;
			background-image:url('../../resources/images/default/grid/loading.gif');
			background-repeat: no-repeat;
			background-position: left;
			padding-left:20px;
			margin:10px;
		}

		.mainnav-date {
			background-image: url(/led-icons/calendar_2.png) !important;
		}
		.mainnav-category {
			background-image: url(/led-icons/text_padding_bottom.png) !important;
		}

		.subnav-favorite {
			background-image: url(/led-icons/zicon.gif) !important;
		}
		.subnav-workflow {
			background-image: url(/led-icons/workicon.gif) !important;
		}

		.is-hidden-content {
			background-color: #DDA0DD;
		}

		.review-ready {
			background-color: red;
		}

		/*
		.content-status-reg-ready {
			background-color: #;
		}
		*/
		.content-status-reg-request {
			background-color: #FFD700;
		}
		/*
		.content-status-reg-complete {
			background-color: #;
		}
		*/
		.content-status-review-ready {
			background-color: #A9A9A9;
		}
		.content-status-review-complete {
			background-color: #778899;
		}
		.content-status-review-return {
			background-color: #E9967A;
		}
		.content-status-review-half {
			background-color: #DCDCDC;
		}

		.ct-override {
			background-color: red;
		}
		.wait_list_modified {
			background-color: #FFFFBB;
		}

		.x-list-body-inner dl {
		   border-bottom: 1px solid #DDDDDD;
		   border-right: 1px solid #DDDDDD;
		}

		/* progress */
		.x-grid3-td-progress-cell .x-grid3-cell-inner {
			font-weight: bold;
		}

		.x-grid3-td-progress-cell .high {
			background: transparent url(/ext/examples/ux/images/progress-bg-green.gif) 0 -33px;
		}

		.x-grid3-td-progress-cell .medium {
			/*background: transparent url(/ext/examples/ux/images/progress-bg-orange.gif) 0 -33px;*/
			background: transparent url(/ext/examples/ux/images/progress-bg-middle.gif) 0 -33px;
		}

		.x-grid3-td-progress-cell .low {
			/*background: transparent url(/ext/examples/ux/images/progress-bg-green.gif) 0 -33px;*/
			background: transparent url(/ext/examples/ux/images/progress-bg-low.gif) 0 -33px;
		}

		.x-grid3-td-progress-cell .ux-progress-cell-foreground {
			color: #fff;
		}
		
		ul.x-tab-strip-top {
				  background-color: #eaeaea;
		  border-bottom-color: #d0d0d0;
		  background-image: url("");
		  margin: 5px 3px -1px;
		  padding:0px;
			}
			ul.x-tab-strip li {
		  float: left;
		  margin-left: 2px;
		}
		.x-panel-body-noheader, .x-panel-mc .x-panel-body {
			
		}
		.x-tab-panel-header {
			
			padding-bottom: 0px;
		
		}

		.x-tab-strip.x-tab-strip-top.strip-hidden
		{
			display: none;
		} 

		.x-tab-strip.x-tab-strip-top.strip-show
		{
			 display: block;
		}

		/* 메뉴 기존 아이콘 삭제 */
		 .tree_menu .x-tree-node-icon{ display:none;}

		 /* 메뉴 빈공백에 대한 아이콘 삭제 */
		 .tree_menu .x-tree-ec-icon { float:right;position:relative;top:13px;}

		 /*
		  하위 메뉴가 있을 경우 배경 삭제
		  - 아이콘을 before 를 이용하여 대체
		 */
		 .tree_menu .x-tree-no-lines .x-tree-elbow-minus {
			background-image: url("");
			content: " ";
		 }

		 .tree_menu .x-tree-ec-icon.x-tree-elbow-minus::before {
			  content: "\f077";
			  display: inline-block;
			  font: normal normal normal 14px/1 FontAwesome;
			  font-size: inherit;
			  text-rendering: auto;
			  -webkit-font-smoothing: antialiased;
			  font-size:12px;
			  position: absolute;
			  left: -20px;
			  top: -3px;
		 }

		 /*
		  하위 메뉴가 있을 경우 배경 삭제
		  + 아이콘을 before 를 이용하여 대체
		 */
		 .tree_menu .x-tree-no-lines .x-tree-elbow-plus{
			background-image: url("");
			content: " ";
		 }

		 .tree_menu .x-tree-ec-icon.x-tree-elbow-plus:before {
			content: "\f078";
			display: inline-block;
			font: normal normal normal 14px/1 FontAwesome;
			font-size: inherit;
			text-rendering: auto;
			-webkit-font-smoothing: antialiased;
			font-size:12px;
			position: absolute;
			left: -20px;
			top: -3px;
		 }

		 /*
		   하나의 메뉴에 대한 CSS 
		 */
		 .tree_menu .x-tree-node-el {
			width: 100%;
			line-height: 35px;
			cursor: pointer;
			text-indent:15px;
			border-bottom:1px solid #f1f1f1;
			-webkit-transition: all 0.1s ease-in-out;
			-moz-transition: all 0.1s ease-in-out;
			-ms-transition: all 0.1s ease-in-out;
			-o-transition: all 0.1s ease-in-out;
			transition: all 0.1s ease-in-out;
		 } 

		 /* 하위 메뉴가 존재하는 메뉴의 배경색을 설정할 수 있다. expand collapse 경우*/

		 .tree_menu .x-tree-node-expanded , .tree_menu .x-tree-node-collapsed {
			background-color:#f1f1f1;
		 }

		 /* 메뉴가 선택되었을 경우*/

		.tree_menu .x-tree-selected {
			/* fallback */
			background-color: #0D9AC8;
			/* Safari 4-5, Chrome 1-9 */
			//background: -webkit-gradient(linear, left top, right top, from(#0D9AC8), to(#fff));
			/* Safari 5.1, Chrome 10+ */
			// background: -webkit-linear-gradient(left, #0D9AC8, #fff);
			/* Firefox 3.6+ */
			//background: -moz-linear-gradient(left, #0D9AC8, #fff);
			/* IE 10 */
			//background: -ms-linear-gradient(left, #0D9AC8, #fff);
			/* Opera 11.10+ */
			//background: -o-linear-gradient(left, #0D9AC8, #fff);
		}

		 /*  선택되었을 경우 글자 색상 및 크기 변경*/

		 .tree_menu .x-tree-node .x-tree-selected  a span {
			color: #fff;
			font-weight:800;
		 }

		.licenseSearch {
			width: 62px;
			height: 15px;
			font-size: 9px;
			background: #3B97DF;
			color: white;
			border-radius: 12px;
			font-family: 나눔고딕;
		}
	</style>

	<script type="text/javascript">
	Ext.onReady(function(){

		Ext.QuickTips.init();
		Ext.apply(Ext.QuickTips.getQuickTip(), {
			showDelay: 50,
		    dismissDelay: 15000
		});

		var view = new Ext.Viewport({
            layout: 'border',
            items:[{
				region: 'north',
				height: 80,
				baseCls: 'bg_main_top_gif',
				contentEl:'alltotal'
			},{
				id: 'west-menu',
				region: 'west',
				xtype: 'treepanel',
				border: false,
				split: false,
				plain: false,
				width: 195,
				autoScroll: true,
				rootVisible :false,
				cls:'tree_menu',
				lines:false,
				root: {
					id:'admin',
					icon:'/led-icons/folder.gif',
					text: 'root',
					expanded: true,
					children: [
					<?php if($user_data['user_level'] == 'L09'){ ?>
						{
							text: '프로젝트 관리',
							name: 'license',
							url: '/pages/project.php',
							cls: 'fa fa-key',
							icon:'/led-icons/folder.gif',
							leaf: true
						},
					<?php } ?>
					{
						text: '라이센스 관리',
						name: 'license',
						url: '/pages/license.php',
						cls: 'fa fa-key',
						icon:'/led-icons/folder.gif',
						leaf: true
					}
					<?php if($user_data['user_level'] == 'L09'){ ?>
						,{
							text: '코드 관리',
							name: 'code_management',
							url: '/pages/codeManagement.php',
							cls: 'fa fa-code',
							icon:'/led-icons/folder.gif',
							leaf: true
						},{
							text: '제품 관리',
							name: 'product',
							url: '/pages/product.php',
							cls: 'fa fa-cube',
							icon:'/led-icons/folder.gif',
							leaf: true
						}
					<?php } ?>
					,{
						text: '자산 관리',
						name: 'equipment',
						url: '/pages/equipment.php',
						cls: 'fa fa-desktop',
						icon:'/led-icons/folder.gif',
						leaf: true
					}
					<?php if($user_data['user_level'] == 'L09'){ ?>
						,{
							text: '사용자 관리',
							name: 'user',
							url: '/pages/user.php',
							cls: 'fa fa-users',
							icon:'/led-icons/folder.gif',
							leaf: true
						}
					<?php }	?>
				]
				},
				listeners: {
					afterrender: function(self){
						self.getSelectionModel().select(self.getRootNode().childNodes[0]);
						self.fireEvent('click', self.getRootNode().childNodes[0]);
					},
					click: function(node, e){
						var url = node.attributes.url;

						if(!url) return;

						Ext.Ajax.request({
							url: url,
							timeout: 0,
							callback: function(opts, success, response){
								try
								{
									
									Ext.getCmp('centerPanel').removeAll(true);
									Ext.getCmp('centerPanel').add(Ext.decode(response.responseText));

									Ext.getCmp('centerPanel').doLayout();
								}
								catch (e)
								{
									Ext.Msg.alert(e['name'], opts.url+'<br />'+e['message']);
								}
							}
						});

						var name = node.attributes.name;
						//eval(name).call();
					}
				}
			},{
				region: 'center',
				id: 'centerPanel',
				layout: 'fit',
				border: false
			}]
		});

		var hideMask = function () {
	        Ext.get('loading').remove();
	        Ext.fly('loading-mask').fadeOut({
	            remove:true
	        });


			<?php
			if ( empty($_SESSION['user']) || $_SESSION['user']['user_id'] == 'temp' )
			{
			?>
			function loginDo(id, pw)
			{
				if (Ext.isEmpty(id))
				{
					Ext.Msg.show({
						//>>title: '확인',
						title: '<?=_text('MN00024')?>',
						//>>msg: '사원번호을 입력하세요',
						icon: Ext.Msg.INFO,
						buttons: Ext.Msg.OK,
						fn: function(btnId, text, opts){
							Ext.get('login-id').focus(250);
						}
					});
					return;
				}

				if (Ext.isEmpty(pw))
				{
					Ext.Msg.show({
						//>>title: '확인',
						title: '<?=_text('MN00024')?>',
						//>>msg: '비밀번호을 입력하세요',
						icon: Ext.Msg.INFO,
						buttons: Ext.Msg.OK,
						fn: function(btnId, text, opts){
							Ext.get('login-pw').focus(250);
						}
					});
					return;
				}else if(pw.length < 9){
					Ext.Msg.show({
						//>>title: '확인',
						title: '<?=_text('MSG00015')?>',
						//>>msg: '비밀번호을 8자리 이상 입력하세요.',
						icon: Ext.Msg.INFO,
						buttons: Ext.Msg.OK,
						fn: function(btnId, text, opts){
							Ext.get('login-pw').focus(250);
						}
					});
					return;
				}

				Ext.Ajax.request({
					url: '/login_ok.php',
					params: {
						userName: id,
						password: pw
					},
					callback: function(opts, success, response){
						if (success)
						{
							try
							{
								var r = Ext.decode(response.responseText);
								// 2010-12-14 최초 로그인한 사용자 비밀번호 세팅하도록 마이페이지로 이동 by CONOZ
								if (r.success)
								{
									//Ext.get('login-mask').remove();
									//Ext.get('login-form').remove();
									if(r.passchk){
										Ext.Msg.show({
											//>>title: '확인',
											title: '<?=_text('MN00024')?>',
											//>>msg: '비밀번호가 설정되어 있지 않습니다.<br/> 비밀번호를 변경하여주세요. 마이페이지로 이동합니다.',
											msg: '<?=_text('MSG00008')?>',
											icon: Ext.Msg.INFO,
											buttons: Ext.Msg.OK,
											fn: function(btnId){

												// 비밀번호가 없을시 무조건 마이페이지로 이동
												window.location = '/pages/mypage/index.php';
											}
										});
										//Ext.Msg.alert('확인', '메뉴가 설정되어 있지 않습니다. \n 마이페이지로 이동합니다.');
										//window.location = '/store/mypage/mypage.php?type=setPassword';
									}else{
										window.location = '/browse.php';
									}
									//window.location = '/browse.php';
								}
								else
								{
									Ext.Msg.show({
										//>> title: '확인',
										title: '<?=_text('MN00024')?>',
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
							//>>Ext.Msg.alert('서버 오류', response.statusText);
							Ext.Msg.alert('<?=_text('MN00022')?>', response.statusText);
						}
					}
				});
			}

			var showMask = function () {
				Ext.get('login-mask').show();
				Ext.get('login-form').show({
					duration: 1,
					easing: 'easeIn'
				});
			}
			showMask();

			Ext.get('login-id').focus();
			Ext.get('login-id').on('keydown', function(e, t, o){
				if (e.getKey() == e.ENTER)
				{
					e.stopEvent();
					loginDo(Ext.get('login-id').getValue(), Ext.get('login-pw').getValue());
				}
			});
			Ext.get('login-pw').on('keydown', function(e, t, o){
				if (e.getKey() == e.ENTER)
				{
					e.stopEvent();
					loginDo(Ext.get('login-id').getValue(), Ext.get('login-pw').getValue());
				}
			});
			Ext.get('login-submit').on('click', function(e, t, o){
				loginDo(Ext.get('login-id').getValue(), Ext.get('login-pw').getValue());
			});
			<?php
			}
			?>
	    }
	    hideMask.defer(250);
    });
    </script>

	<div id="alltotal">
		<div id="main_top">

			<div id="top_left">
				<h1>
					<img src="/ext/resources/gemini_logo.png" style="width: 120px; height : 65px; margin : 8 0 0 25;">
				</h1>

				<ul id="gnb"><!--메인 탑 메뉴 : 2012-3-9 by 이성용 추후 권한 적용을 위해 함수로 변경 -->
				
				<?=createTopMenu($_SESSION)?>

				</ul>

			</div>

			<div id="top_rightall" >
				<div id="top_right" >
					<div>

					<tr>
						<?php
						if ( $_SESSION['user']['user_id'] != 'temp' )
						{
						?>
						<td>
							<TABLE  height="10" border="0" id="login_msg"  border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td colspan=4>
									<input id="searchInput" type="text" width="250px" onKeyUp="onKeyUpLicenseSearch();"/>
									<button type="button" class="licenseSearch" onClick="onLicenseSearch()">검색</button>
								</td>
							</tr>
							<TR>
								<TD width="89%" align="right">
									<a href="#" style="color: rgba(0, 0, 0, 0.5);text-decoration: none;" href="#" >
									<?=$_SESSION['user']['KOR_NM'].'('.$_SESSION['user']['user_id'].')';?>&nbsp
									</a>
								</TD>
								<TD width="4%">
									<a href="#" onclick="personalInfo()">
										<img src='/images/edit_personal_bt.png'   value='정보변경' style="_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader" border="0">
									</a>
								</TD>
								<td width="1%"></td>
								<TD width="4%">
									<a href="#" onclick="logout()">
										<img src='/images/logout_bt.png'   value='로그아웃' style="_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader" border="0">
									</a>
								</TD>
								<td width="1%"></td>
								
							</TR>
							</TABLE>

						</td>

						<?
						}
						else
						{
							echo '<td>&nbsp;</td>';
						}
						?>
					</tr>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		function goHome(){
			window.location = '/browse.php';

		}

		function license(){
			Ext.getCmp('centerTab').setActiveTab(0);
		}

		function code_management(){
			Ext.getCmp('centerTab').setActiveTab(1);
		}

		function product(){
			Ext.getCmp('centerTab').setActiveTab(2);
		}

		function logout(){
			Ext.Msg.show({
				icon: Ext.Msg.QUESTION,
				//>>title: '로그아웃',
				title: '로그아웃',
				//>> msg: ' 님 로그아웃 하시겠습니까?',
				msg: '로그아웃 하시겠습니까?',
				buttons: Ext.Msg.OKCANCEL,
				fn: function(btnId, text, opts){
					if(btnId == 'cancel') return;

					Ext.Ajax.request({
						url: '/store/logout.php',
						callback: function(opts, success, response){
							if(success){
								try{
									var r = Ext.decode(response.responseText);
									if(r.success){
										window.location = '/';
									}else{
										//>>Ext.Msg.alert('오류', r.msg);
										Ext.Msg.alert('오류', r.msg);
									}
								}
								catch(e){
									//>>Ext.Msg.alert('오류', e+'<br />'+response.responseText);
									Ext.Msg.alert('오류', e+'<br />'+response.responseText);
								}
							}else{
								//>>Ext.Msg.alert('오류', response.statusText);
								Ext.Msg.alert('오류', response.statusText);
							}
						}
					})
				}
			});
		}

		function personalInfo(){
			var user_nm = '<?=$_SESSION['user']['KOR_NM']?>';
			var personalInfo_win = new Ext.Window({
				title: '사용자 정보 변경',
				width: 280,
				height: 320,
				layout: 'fit',
				modal: true,
				items: [{
					xtype: 'form',
					frame: true,
					autoScroll: true,
					labelWidth: 80,
					id: 'personalInfo_form',
					defaults: {
						width: 150
					},
					items: [{
						xtype: 'textfield',
						allowBlank: false,
						fieldLabel: '성',
						name: 'lastname',
						value: '<?=$user_data['lastname']?>'
					},{
						xtype: 'textfield',
						allowBlank: false,
						fieldLabel: '이름',
						name: 'firstname',
						value: '<?=$user_data['firstname']?>'
					},{
						xtype: 'textfield',
						inputType: 'password',
						fieldLabel: '기존 비밀번호',
						name: 'password'
					},{
						xtype: 'textfield',
						inputType: 'password',
						fieldLabel: '새 비밀번호',
						name: 'new_password'
					},{
						xtype: 'textfield',
						inputType: 'password',
						fieldLabel: '비밀번호 확인',
						name: 'new_password_confirm'
					},{
						<?
							if($user_data['user_level'] != 'L09' && $user_data['user_level'] != 'L04'){
								echo 'disabled: true,';
							}
						?>
						xtype: 'combo',
						allowBlank: false,
						fieldLabel: '직급',
						name: 'job_grade',
						value: '<?=$user_data['job_grade']?>',
						typeAhead: true,
						triggerAction: 'all',
						editable: false,
						displayField: 'd',
						valueField: 'v',
						mode: 'local',
						store: new Ext.data.ArrayStore({
							fields: [
								'v', 'd'
							],
							data: [
								<?php
								unset($t);
								$job_grade_list = $db->queryAll("
									SELECT	C.CODE, C.CODE_NM
									FROM	BC_CODE C
										, BC_CODE_TYPE CT
									WHERE	CT.ID = C.CODE_TYPE_ID
									AND	CT.CODE = 'JOB_GRADE'
									AND C.CODE != '0'
									ORDER BY C.SORT ASC
								");
								foreach($job_grade_list as $row){
									$t[] = "['".$row['code']."', '".$row['code_nm']."']";
								}
								echo implode(', ', $t);
								?>
							]
						})
						/*
						store: new Ext.data.JsonStore({
							url: '/store/code/getCode.php',
							remoteSort: true,
							idProperty: 'id',
							root: 'data',
							fields: [
								'id',
								'code',
								'code_nm'
							],
							baseParams: {
								action: 'getCode',
								code_type_id: '6'
							}
						})
						*/
					},{
						<?
							if($user_data['user_level'] != 'L09'){
								echo 'disabled: true,';
							}
						?>
						xtype: 'combo',
						allowBlank: false,
						fieldLabel: '사용자 등급',
						name: 'user_level',
						value: '<?=$user_data['user_level']?>',
						typeAhead: true,
						triggerAction: 'all',
						editable: false,
						displayField: 'd',
						valueField: 'v',
						mode: 'local',
						store: new Ext.data.ArrayStore({
							fields: [
								'v', 'd'
							],
							data: [
								<?php
								unset($t);
								$job_grade_list = $db->queryAll("
									SELECT	C.CODE, C.CODE_NM
									FROM	BC_CODE C
										, BC_CODE_TYPE CT
									WHERE	CT.ID = C.CODE_TYPE_ID
									AND	CT.CODE = 'USER_LEVEL'
									AND C.CODE != '0'
									ORDER BY C.SORT ASC
								");
								foreach($job_grade_list as $row){
									$t[] = "['".$row['code']."', '".$row['code_nm']."']";
								}
								echo implode(', ', $t);
								?>
							]
						})
					},{
						xtype: 'textfield',
						fieldLabel: 'E-Mail',
						name: 'email',
						value: '<?=$user_data['email']?>'
					},{
						xtype: 'textfield',
						fieldLabel: '연락처',
						name: 'phone',
						value: '<?=$user_data['phone']?>'
					}]
				}],
				buttons: [{
					text: '저장',
					handler: function(btn){
						var personalInfo_form = Ext.getCmp("personalInfo_form").getForm();
						if(!personalInfo_form.isValid()){
							Ext.Msg.alert('알림', '필수 입력 값들을 모두 입력해주세요.');
							return;
						}

						var personalInfo_inputs = personalInfo_form.getFieldValues();

						if(!Ext.isEmpty(personalInfo_inputs.password) && Ext.isEmpty(personalInfo_inputs.new_password)){
							Ext.Msg.alert('알림', '변경될 새 비밀번호를 입력해주세요.');
							return;
						}
						else if(Ext.isEmpty(personalInfo_inputs.password) && (!Ext.isEmpty(personalInfo_inputs.new_password) || !Ext.isEmpty(personalInfo_inputs.new_password_confirm))){
							Ext.Msg.alert('알림', '기존 비밀번호를 입력해주세요.');
							return;
						}
						else if((!Ext.isEmpty(personalInfo_inputs.password) && !Ext.isEmpty(personalInfo_inputs.new_password)) && Ext.isEmpty(personalInfo_inputs.new_password_confirm)){
							Ext.Msg.alert('알림', '비밀번호 확인을 입력해주세요.');
							return;
						}
						else if(personalInfo_inputs.new_password != personalInfo_inputs.new_password_confirm){
							Ext.Msg.alert('알림', '변경될 새 비밀번호와 동일하게 비밀번호 확인을 입력해주세요.');
							return;
						}
						else if(!Ext.isEmpty(personalInfo_inputs.password) && !Ext.isEmpty(personalInfo_inputs.new_password) && (personalInfo_inputs.password == personalInfo_inputs.new_password)){
							Ext.Msg.alert('알림', '기존의 비밀번호와 다른 비밀번호를 입력해주세요.');
							return;
						}

						Ext.Ajax.request({
						url: '/store/user/setUser.php',
						params: {
							action:					'update',
							id:						'<?=$_SESSION['user']['user_id']?>',
							lastname:				personalInfo_inputs.lastname,
							firstname:				personalInfo_inputs.firstname,
							password:				CryptoJS.SHA512(personalInfo_inputs.password.trim()),
							new_password:			CryptoJS.SHA512(personalInfo_inputs.new_password.trim()),
							new_password_confirm:	CryptoJS.SHA512(personalInfo_inputs.new_password_confirm.trim()),
							job_grade:				personalInfo_inputs.job_grade,
							user_level:				personalInfo_inputs.user_level,
							email:					personalInfo_inputs.email,
							phone:					personalInfo_inputs.phone
						},
						callback: function(opts, success, response){
							if(success){
								try{
									var r = Ext.decode(response.responseText);
									if(r.success){
										Ext.Msg.show({
											title: '알림', 
											msg: '정보가 변경되었습니다. 페이지를 새로고침하시겠습니까?',
											buttons: Ext.Msg.OKCANCEL,
											fn: function(btn){
												if(btn == 'ok'){
													location.reload()
												}
											}
										});
										btn.ownerCt.ownerCt.close();
									}
									else{
										Ext.Msg.alert('오류', r.msg);
										btn.ownerCt.ownerCt.close();
									}
								}
								catch(e){
									Ext.Msg.alert('오류', e+'<br />'+response.responseText);
									btn.ownerCt.ownerCt.close();
								}
							}
							else{
								Ext.Msg.alert('오류', response.statusText);
								btn.ownerCt.ownerCt.close();
							}
						}
					});

						//btn.ownerCt.ownerCt.close();
					}
				},{
					text: '닫기',
					handler: function(btn){
						btn.ownerCt.ownerCt.close();
					}
				}]
			});

			personalInfo_win.show();
		}

		function onKeyUpLicenseSearch(){
			if(event.keyCode == 13){
				onLicenseSearch();
			}
		}

		function onLicenseSearch(){
			var searchV = Ext.get('searchInput').getValue();
			if(Ext.isEmpty(searchV)) return;

			var LicenseSearchWin = new Ext.Window({
				width: 500,
				height: 300,
				title: '라이센스 검색',
				layout: 'fit',
				modal: true,
				border: false,
				items: [{
					xtype: 'panel',
					layout: 'fit',
					border: false,
					tbar: [{
						xtype: 'textfield',
						name: 'searchInputInWin',
						value: searchV,
						enableKeyEvents: true,
						listeners: {
							keypress: function(self, e){
								if(e.keyCode == 13){
									var searchV = self.getValue();
									Ext.getCmp('gridInLicenseSearchWin').getStore().load({
										params: {
											searchkey: searchV
										}
									});
								}
							}
						}
					},{
						icon: '/led-icons/magnifier.png',
						text: '조회',
						handler: function(handler){
							var searchV = handler.ownerCt.get(0).getValue();
							Ext.getCmp('gridInLicenseSearchWin').getStore().load({
								params: {
									searchkey: searchV
								}
							});
						}
					}],
					items: [{
						xtype: 'grid',
						id: 'gridInLicenseSearchWin',
						loadMask: true,
						border: false,
						store: new Ext.data.JsonStore({
							autoLoad: true,
							url: '/store/license/getLicenseList.php',
							remoteSort: true,
							sortInfo: {
								field: 'create_date',
								direction: 'DESC'
							},
							idProperty: 'id',
							root: 'data',
							fields: [
								'id',
								'proj_id',
								'proj_nm',
								'cust_nm',
								'license_type',
								'license_type_nm',
								'product_id',
								'product_nm',
								'use_term',
								'use_term_nm',
								'reg_type',
								'reg_type_nm',
								'reg_status',
								'reg_status_nm',
								'os',
								'phone',
								'ch_cnt',
								'cpu_id',
								'cpu_info',
								'memory',
								'server_nm',
								'license_key',
								'create_user',
								{name: 'create_date', type: 'date', dateFormat:'YmdHis'},
								{name: 'complete_date', type: 'date', dateFormat:'YmdHis'},
								{name: 'expire_date', type: 'date', dateFormat:'YmdHis'},
								'description',
								'provided'
							],
							baseParams: {
								searchkey: searchV
							}
						}),
						selModel: new Ext.grid.RowSelectionModel({
							singleSelect: false
						}),
						colModel: new Ext.grid.ColumnModel({
							columns: [
								new Ext.grid.RowNumberer(),
								{header: '프로젝트ID', dataIndex: 'proj_id', width: 80},
								{header: '고객사', dataIndex: 'cust_nm', width: 120},
								{header: '프로젝트', dataIndex: 'proj_nm', width: 120},
								{header: '발급여부', dataIndex: 'provided', width: 60, align: "center", renderer: function(v){ if(v == 'Y') return '<img src="/led-icons/accept.png" style="height:13px;" />'; return ''; }},
								{header: '구분', dataIndex: 'license_type_nm', width: 100},
								{header: '제품명', dataIndex: 'product_nm', width: 100},
								{header: '채널 수', dataIndex: 'ch_cnt', width: 50, renderer: function(v){ if(Ext.isEmpty(v)) return '1'; return v; }},
								{header: '등록방법', dataIndex: 'reg_type_nm', width: 80},
								{header: '사용기간', dataIndex: 'use_term_nm', width: 60},
								{header: '등록상태', dataIndex: 'reg_status', width: 80, renderer: function(value, metaData, record){
									if(value == 'R02'){
										return '';
									}
									else if(value == 'R99'){
										var use_term = record.get('use_term');

										if(use_term == 91){
											return '완료';
										}
										else if(!Ext.isEmpty(record.get('complete_date'))){
											var today = new Date();
											var expire_date = record.get('expire_date').format('Ymd');

											if(expire_date < today.format('Ymd')){
												return '만료';
											}
											else {
												return '완료';
											}
										}
									}
								}},
								{header: 'License Key', dataIndex: 'license_key', width: 250, editor: new Ext.form.TextField({readOnly: true})},
								{header: '등록일', dataIndex: 'create_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
								{header: '서버 설치일', dataIndex: 'complete_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
								{header: '사용 만료일', dataIndex: 'expire_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
								{header: '서버 명', dataIndex: 'server_nm', width: 200},
								{header: '비고', dataIndex: 'description', width: 300}
							]
						}),
						viewConfig: {
							forseFit: true
						}
					}],
					buttons: [{
						text: '닫기',
						handler: function(handler){
							handler.ownerCt.ownerCt.ownerCt.close();
						}
					}]
				}]
			});

			LicenseSearchWin.show();

			//
		}

	</script>

</body>
</html>



