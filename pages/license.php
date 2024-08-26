<?
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/lang.php');

	$q = "
		SELECT	USER_LEVEL
		FROM	BC_USER
		WHERE	LOGIN_ID = '".$_SESSION['user']['user_id']."'
	";

	$user_level = $db->queryOne($q);
?>
(function(){
	var copyText = '';
	var clipBoard_client1 = '';
	var clipBoard_client2 = '';
	var selectProjRow = '';
	var selectLicenseRow = '';

	var licensePanel = {
		xtype: 'panel',
		layout: 'fit',
		border: false,
		items: [{
			xtype: 'panel',
			layout: 'border',
			defaults: {
				width: '100%',
				split: true
			},
			items: [{
				region: 'center',
				//title: _text('MN00026')+' '+_text('MN00027'),
				title: '<?= _text('MN00026').' '._text('MN00027')?>',
				xtype: 'grid',
				id: 'proj_list',
				listeners: {
					afterrender: function(self){
						self.store.load();
					},
					rowdblclick: function(grid, rowIndex, e){
						if(Ext.isEmpty(rowIndex)) return;
						var select_row = grid.getStore().getAt(rowIndex);

						registProjForm('update', select_row.get('proj_id'));
					},
					rowcontextmenu: function(self, rowIndex, e){
						e.stopEvent();
						var sm = self.getSelectionModel();
						if (!sm.isSelected(rowIndex)) {
							sm.selectRow(rowIndex);
						}

						var menu = new Ext.menu.Menu({
							items: [{
								text : '수정',
								icon: '/led-icons/application_edit.png',
								handler: function(b, e){
									var sm = self.getSelectionModel();
									if(sm.hasSelection()){
										
										registProjForm('update', sm.getSelected().get('proj_id'));
									}else{
										//Ext.Msg.alert(_text('MN00023'), _text('MSG00008'));
										Ext.Msg.alert('<?= _text('MN00023')?>','<?=_text('MSG00008')?>');
									}
								}
							},{
								text : '삭제',
								icon: '/led-icons/application_delete.png',
								handler: function(b, e){
									var sm = self.getSelectionModel();
									if(sm.hasSelection()){

										requestProj('remove', sm.getSelected().data);
									}else{
										//Ext.Msg.alert(_text('MN00023'), _text('MSG00009'));
										Ext.Msg.alert('<?= _text('MN00023')?>','<?=_text('MSG00009')?>');
									}
								}
							}]
						});
						menu.showAt(e.getXY());
					}
				},
				tbar: ['고객사 : ',{
					xtype: 'textfield',
					fieldLabel: '고객사',
					id: 'cust_nm',
					width: 120
				},'-','프로젝트 : ',{
					xtype: 'textfield',
					fieldLabel: '프로젝트',
					id: 'proj_nm',
					width: 120
				},'-','품명 : ',{
					width: 120,
					xtype: 'combo',
					fieldLabel: '품명',
					id: 'product',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							<?php
							unset($t);
							$product_list = $db->queryAll("
								SELECT	PRODUCT_ID, PRODUCT_NM
								FROM BC_PRODUCT
								WHERE USE_YN = 'Y'
								ORDER BY CREATE_DATE DESC
							");
							foreach($product_list as $row){
								$t[] = "['".$row['product_id']."', '".$row['product_nm']."']";
							}
							echo "['all', '전체'], ".implode(', ', $t);
							?>
						]
					}),
					listeners: {
						render: function(self){
							self.setValue(self.getStore().getAt(0).get('v'));
						}
					}
				},{
					text: '검색',
					icon: '/led-icons/magnifier.png',
					listeners: {
						click: function(self){
							Ext.getCmp('proj_list').getStore().load();
							Ext.getCmp('license_list').getStore().removeAll();
						}
					}
				}/*,'-',{
					text: '등록',
					icon: '/led-icons/application_add.png',
					handler: function(btn, e){
						registProjForm('regist', '');
					}
				}*/,'-',{
					text: '수정',
					icon: '/led-icons/application_edit.png',
					handler: function(btn, e){
						var select_proj = Ext.getCmp('proj_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(select_proj)){
							Ext.Msg.alert('알림', '수정할 프로젝트를 선택해주세요.');
							return;
						}
						
						registProjForm('update', select_proj.get('proj_id'));
					}
				}/*,'-',{
					text: '삭제',
					icon: '/led-icons/application_delete.png',
					handler: function(btn, e){
						var select_proj = Ext.getCmp('proj_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(select_proj)){
							Ext.Msg.alert('알림', '삭제할 프로젝트를 선택해주세요.');
							return;
						}

						Ext.Msg.show({
							title: '알림',
							msg: '선택한 '+select_proj.get('proj_nm')+'프로젝트를 삭제하시겠습니까?',
							buttons: Ext.Msg.OKCANCEL,
							fn: function(btnId, text, opts){
								if(btnId == 'ok'){
									requestProj('remove', select_proj.data);
								}
							}
						});
					}
				},'-',{
					text: '사용',
					hidden: <?if($user_level == 'L09' || $user_level == 'L04'){ echo 'false'; } else { echo 'true'; } ?>,
					//icon: '/led-icons/application_delete.png',
					handler: function(btn, e){
						var select_proj = Ext.getCmp('proj_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(select_proj)){
							Ext.Msg.alert('알림', '프로젝트를 선택해주세요.');
							return;
						}

						requestProj('use', select_proj.data);
					}
				},{
					text: '사용 안함',
					hidden: <?if($user_level == 'L09' || $user_level == 'L04'){ echo 'false'; } else { echo 'true'; } ?>,
					//icon: '/led-icons/application_delete.png',
					handler: function(btn, e){
						var select_proj = Ext.getCmp('proj_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(select_proj)){
							Ext.Msg.alert('알림', '프로젝트를 선택해주세요.');
							return;
						}

						requestProj('unuse', select_proj.data);
					}
				}*/],
				loadMask: true,
				store: new Ext.data.JsonStore({
					url: '/store/license/getProjectList.php',
					remoteSort: true,
					idProperty: 'proj_id',
					root: 'data',
					fields: [
						'proj_id',
						'cust_nm',
						'proj_nm',
						'cust_user_nm',
						'phone',
						'email',
						'description',
						{name: 'create_date', type: 'date', dateFormat:'YmdHis'},
						'create_user',
						'use_yn',
						'license_cnt'
					],
					listeners: {
						exception: function(self, type, action, opts, response, args){
							try {
								var r = Ext.decode(response.responseText);
								if(!r.success) {
									Ext.Msg.alert('정보', r.msg);
								}
							}
							catch(e) {
								Ext.Msg.alert('디코드 오류', e);
							}
						},
						beforeload: function(self, opts){
							var cust_nm = Ext.getCmp('cust_nm').getValue();
							var product = Ext.getCmp('product').getValue();
							var proj_nm = Ext.getCmp('proj_nm').getValue();

							self.baseParams = {
								cust_nm: cust_nm,
								product: product,
								proj_nm: proj_nm
							}
						},
						load: function(self, records, opts){
							if(records.length > 0){
								if(Ext.isEmpty(selectProjRow)) {
									Ext.getCmp('proj_list').getSelectionModel().selectFirstRow();
								}
								else {
									var row = Ext.getCmp('proj_list').getStore().find('proj_id', selectProjRow);
									if(!Ext.isEmpty(row)) Ext.getCmp('proj_list').getSelectionModel().selectRow(row);
									selectProjRow = '';
								}
							}
						}
					}
				}),
				colModel: new Ext.grid.ColumnModel({
					defaults: {
							sortable: true
					},
					columns: [
						new Ext.grid.RowNumberer(),
						{header: '프로젝트ID', dataIndex: 'proj_id', width: 80, align:'center'},
						{header: '고객사', dataIndex: 'cust_nm', width: 120},
						{header: '프로젝트', dataIndex: 'proj_nm', width: 120},
						{header: '담당자', dataIndex: 'cust_user_nm', width: 70, align:'center'},
						{header: '연락처', dataIndex: 'phone', width: 100},
						{header: '이메일', dataIndex: 'email', width: 150},
						{header: '비고', dataIndex: 'description', width: 200},
						{header: '등록자', dataIndex: 'create_user', width: 70, align:'center'},
						{header: '등록일', dataIndex: 'create_date', width: 150, align:'center', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
						{header: '사용 여부', dataIndex: 'use_yn', width: 70, align:'center', hidden : true, renderer: function(v){ if(v == 'Y') { return '사용'; } else { return '사용안함'; } } },
						{header: '발급한 라이센스', dataIndex: 'license_cnt', width: 100, align:'center'}
					]
				}),
				selModel: new Ext.grid.RowSelectionModel({
					listeners: {
						selectionchange: function(selModel){
							if(Ext.isEmpty(selModel.getSelected())) return;

							Ext.getCmp('license_list').getStore().load();

							var select_row = selModel.getSelected();
							var use = Ext.getCmp('proj_list').topToolbar.items.items[16];
							var unuse = Ext.getCmp('proj_list').topToolbar.items.items[17];
							/*
							if(select_row.get('use_yn') == 'Y'){
								<?
								if($user_level == 'L09' || $user_level == 'L04'){
								?>
								use.hide();
								unuse.show();
								<?
								}
								?>
								Ext.getCmp('license_list').topToolbar.items.items[2].setDisabled(false);
								Ext.getCmp('license_list').topToolbar.items.items[4].setDisabled(false);
							}
							else {
								<?
								if($user_level == 'L09' || $user_level == 'L04'){
								?>
								use.show();
								unuse.hide();
								<?
								}
								?>
								Ext.getCmp('license_list').topToolbar.items.items[2].setDisabled(true);
								Ext.getCmp('license_list').topToolbar.items.items[4].setDisabled(true);
							}
							*/
						}
					}
				}),
				viewConfig: {
					forseFit: true
				}
			},{
				region: 'south',
				height: 450,
				//title: _text('MN00028')+' '+_text('MN00027'),
				title: '<?= _text('MN00028').' '._text('MN00027')?>',
				xtype: 'editorgrid',
				id: 'license_list',
				loadMask: true,
				tbar: [{
					icon: '/led-icons/arrow_refresh.png',
					//text: _text('MN00029'),
					text: '<?= _text('MN00029')?>',
					handler: function(btn){
						Ext.getCmp('license_list').getStore().reload();
					}
				},'-',{
				/*
					icon: '/led-icons/page_copy.png',
					id: 'all_licensekey_copy_btn',
					text: '전체 라이센스 복사'
				},'-',{
					icon: '/led-icons/page_copy.png',
					id: 'selected_licensekey_copy_btn',
					text: '선택한 라이센스 복사',
					handler: function(btn){
						var license_rows = Ext.getCmp('license_list').getSelectionModel().getSelections();
						if(Ext.isEmpty(license_rows)) {
							Ext.Msg.alert('알림', '복사할 라이센스를 선택해주세요.');
							return;
						}
					}
				},'-',{
				*/
					// text: 추가,
					text: '<?= _text('MN00030')?>',
					icon: '/led-icons/application_add.png',
					handler: function(btn){
						var proj_rows = Ext.getCmp('proj_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(proj_rows)) {
							Ext.Msg.alert('알림', '라이센스를 추가할 프로젝트를 선택해주세요.');
							return;
						}

						registLicenseForm(proj_rows.get('proj_id'), 'license_list');
					}
				},'-',{
					icon: '/led-icons/application_delete.png',
					//text: 삭제,
					text: '<?= _text('MN00031')?>',
					handler: function(btn){
						requestLicense('delete', 'license_list');
					}
				},'-',{
					icon: '/led-icons/arrow_undo.png',
					//text: _text('MN00032'),
					text: '<?= _text('MN00032')?>',
					handler: function(btn){
						var license_rows = Ext.getCmp('license_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(license_rows)){
							Ext.Msg.alert('알림', '초기화 할 라이센스를 선택해주세요.');
							return;
						}

						Ext.Msg.show({
							title: '알림',
							msg: '선택하신 라이센스를 초기화하시겠습니까?',
							buttons: Ext.Msg.OKCANCEL,
							fn: function(btn){
								if(btn == 'ok'){
									Ext.Ajax.request({
										url: '/store/license/setLicense.php',
										params: {
											action:	'reset',
											id:		license_rows.get('id')
										},
										callback: function(opts, success, response){
											if(success){
												try{
													var r = Ext.decode(response.responseText);
													if(r.success){
														Ext.getCmp('license_list').getStore().reload();
													}
													else{
														Ext.Msg.alert('오류', r.msg);
													}
												}
												catch(e){
													Ext.Msg.alert('오류', e+'<br />'+response.responseText);
												}
											}
											else{
												Ext.Msg.alert('오류', response.statusText);
											}
										}
									});
								}
							}
						});
						
						
					}
				},'-',{
					//text: _text('MN00033'),
					text: '<?= _text('MN00033')?>',
					handler: function(btn){
						var license_row = Ext.getCmp('license_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(license_row)){
							Ext.Msg.alert('알림', '다운로드할 라이센스를 선택해주세요.');
							return;
						}

						var excel_window_id = 'license_excel';				
						if ( document.getElementById(excel_window_id)  != null ) {
							document.body.removeChild(document.getElementById(excel_window_id));
						}
						var aIframe = document.createElement("iframe");

						aIframe.setAttribute("id",excel_window_id);
						aIframe.style.display = "none";
						aIframe.src = 
						"/store/license/downloadLicense.php?id="+license_row.get('id')+"&action=offline_license_download";

						document.body.appendChild(aIframe);
					}
				},'-',{
					//text: _text('MN00034'),
					text: '<?= _text('MN00034')?>',
					handler: function(btn){
						var license_row = Ext.getCmp('license_list').getSelectionModel().getSelected();
						if(Ext.isEmpty(license_row)){
							Ext.Msg.alert('알림', '다운로드할 라이센스를 선택해주세요.');
							return;
						}

						var excel_window_id = 'license_state';				
						if ( document.getElementById(excel_window_id)  != null ) {
							document.body.removeChild(document.getElementById(excel_window_id));
						}
						var aIframe = document.createElement("iframe");

						aIframe.setAttribute("id",excel_window_id);
						aIframe.style.display = "none";
						aIframe.src = 
						"/store/license/downloadLicense.php?id="+license_row.get('proj_id')+"&action=current_license_state";

						document.body.appendChild(aIframe);
					}
				},'-','구분 : ',{
					xtype: 'combo',
					width: 120,
					id: 'search_licensetype',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							<?php
							unset($t);
							$product_list = $db->queryAll("
								SELECT	C.CODE, C.CODE_NM
								FROM	BC_CODE C
									, BC_CODE_TYPE CT
								WHERE	CT.ID = C.CODE_TYPE_ID
								AND	CT.CODE = 'LICENSE_TYPE'
								AND C.CODE != '0'
								ORDER BY C.SORT ASC
							");
							foreach($product_list as $row){
								$t[] = "['".$row['code']."', '".$row['code_nm']."']";
							}
							echo "['all', '전체'], ".implode(', ', $t);
							?>
						]
					}),
					listeners: {
						render: function(self){
							self.setValue(self.getStore().getAt(0).get('v'));
						},
						select: function(self, record, index){
							Ext.getCmp('license_list').getStore().load();
						}
					}
				},'-','상태 : ',{
					xtype: 'combo',
					width: 120,
					id: 'search_regStatus',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							['all', '전체'],
							['non_complete', '미완료'],
							['complete', '완료']
						]
					}),
					listeners: {
						render: function(self){
							self.setValue(self.getStore().getAt(0).get('v'));
						},
						select: function(self, record, index){
							Ext.getCmp('license_list').getStore().load();
						}
					}
				},'-', {
					xtype: 'checkbox',
					id: 'search_expired',
					boxLabel: '만료된 라이센스',
					listeners: {
						check: function(self, checked){
							Ext.getCmp('license_list').getStore().load();
						}
					}
				}],
				store: new Ext.data.JsonStore({					
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
					listeners: {
						beforeload: function(self, opts){

							var proj_id = Ext.getCmp('proj_list').getSelectionModel().getSelected().get('proj_id');
							var product = Ext.getCmp('product').getValue();
							var licenseType = Ext.getCmp('search_licensetype').getValue();
							var licenseStatus = Ext.getCmp('search_regStatus').getValue();
							var expiredStatus = Ext.getCmp('search_expired').getValue();

							self.baseParams = {
								proj_id: proj_id,
								product_id: product,
								license_type: licenseType,
								license_status: licenseStatus,
								expired_status: expiredStatus
							};
						},
						load: function(self, records, opts){
							if(records.length > 0){
								if(Ext.isEmpty(selectLicenseRow)) {
									Ext.getCmp('license_list').getSelectionModel().selectFirstRow();
								}
								else {
									var row = Ext.getCmp('license_list').getStore().find('proj_id', selectLicenseRow);
									if(!Ext.isEmpty(row)) Ext.getCmp('license_list').getSelectionModel().selectRow(row);
									selectLicenseRow = '';
								}
							}
						},
						exception: function(self, type, action, opts, response, args){
							try {
								var r = Ext.decode(response.responseText);
								if(!r.success) {
									Ext.Msg.alert('정보', r.msg);
								}
							}
							catch(e) {
								Ext.Msg.alert('디코드 오류', e);
							}
						}
					}
				}),
				selModel: new Ext.grid.RowSelectionModel({
					singleSelect: false
				}),
				colModel: new Ext.grid.ColumnModel({
					columns: [
						new Ext.grid.RowNumberer(),
						{header: '발급여부', dataIndex: 'provided', width: 60, align: "center", renderer: function(v){ if(v == 'Y') return '<img src="/led-icons/accept.png" style="height:13px;" />'; return ''; }},
						{header: '구분', dataIndex: 'license_type_nm', width: 100, align:'center'},
						{header: '품명', dataIndex: 'product_nm', width: 100},
						{header: '채널 수', dataIndex: 'ch_cnt', width: 50, align:'center', renderer: function(v){ if(Ext.isEmpty(v)) return '1'; return v; }},
						{header: '등록방법', dataIndex: 'reg_type_nm', width: 80, align:'center'},
						{header: '사용기한', dataIndex: 'use_term_nm', width: 60, align:'center'},
						{header: '등록상태', dataIndex: 'reg_status', width: 80, align:'center', renderer: function(value, metaData, record){
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
						{header: 'License Key', dataIndex: 'license_key', width: 250, align:'center', editor: new Ext.form.TextField({readOnly: true})},
						{header: '등록일', dataIndex: 'create_date', width: 150, align:'center', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
						{header: '서버 설치일', dataIndex: 'complete_date', width: 150, align:'center', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
						{header: '사용 만료일', dataIndex: 'expire_date', width: 150, align:'center', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
						{header: '서버 명', dataIndex: 'server_nm', width: 200},
						{header: '비고', dataIndex: 'description', width: 300}
					]
				}),
				viewConfig: {
					forseFit: true,
					/*
					* 2016-04-12 mjsong
					* #404 사용기간이 만료된 경우 조회되지 않도록 수정
					getRowClass: function(record, index, rowParams, ds){
						var today = new Date();

						rowParams.tstyle = "color:black;";
						if(!Ext.isEmpty(record.get('expire_date'))) {
							if(record.get('expire_date').format('Ymd')< today.format('Ymd')) {
								rowParams.tstyle = "color:gray;";
							}
						}
					}
					*/
				},
				listeners: {
					afterrender: function(self){
						/*
						clipBoard_client1 = new ZeroClipboard( document.getElementById('all_licensekey_copy_btn') );
						clipBoard_client2 = new ZeroClipboard( document.getElementById('selected_licensekey_copy_btn') );
					
						Ext.getCmp('all_licensekey_copy_btn').el.on('mousedown', function(event){
							var license_store = Ext.getCmp('license_list').getStore();
							license_store.each(function(record, index){
								if(index == 0){
									copyText = 'Project ID : '+record.get('proj_id')+'\r\n';
									copyText += 'License : \r\n';
								}
	
								copyText += record.get('license_key')+'\r\n';
							});
							
							clipBoard_client1.clearData();
							clipBoard_client1.setText(copyText);
						});
						Ext.getCmp('selected_licensekey_copy_btn').el.on('mousedown', function(event){
							var license_rows = Ext.getCmp('license_list').getSelectionModel().getSelections();
							if(Ext.isEmpty(license_rows)) return;							
							
							Ext.each(license_rows, function(record, index){
								if(index == 0){
									copyText = 'Project ID : '+record.get('proj_id')+'\r\n';
									copyText += 'License : \r\n';
								}
	
								copyText += record.get('license_key')+'\r\n';
							});
	
							clipBoard_client2.clearData();
							clipBoard_client2.setText(copyText);
						});
						*/	
					},
					celldblclick: function(self, rowIndex, colIndex, e){
						e.stopEvent();
						if(Ext.isEmpty(rowIndex)) return;
						if(colIndex == 6) return;

						viewLicenseInformation(rowIndex);
					},
					rowcontextmenu: function(self, rowIndex, e){
						e.stopEvent();

						var selectedRow = self.getStore().getAt(rowIndex);

						var selectedRows = self.getSelectionModel().getSelections();
						var license_ids = [];
						for(var i=0; i< selectedRows.length; i++){
							license_ids.push(selectedRows[i].get('id'));
						}

						if(license_ids.indexOf(selectedRow.get('id')) < 0){
							self.getSelectionModel().selectRow(rowIndex);
						}
						
						var xy = e.getXY();
						licenseContextmenu.showAt(xy);
					}
				}
			}]
		}]
	};

	var licenseContextmenu = new Ext.menu.Menu({
		items : [{
			text : '발급',
			handler: function(){
				var selectedRows = Ext.getCmp('license_list').getSelectionModel().getSelections();
				
				if(Ext.isEmpty(selectedRows)) return;

				var license_ids = [];
				for(var i=0; i< selectedRows.length; i++){
					license_ids.push(selectedRows[i].get('id'));
				}

				setProvide('Y', license_ids.join(','));
			}
		}, {
			text : '발급취소',
			handler: function(){
				var selectedRows = Ext.getCmp('license_list').getSelectionModel().getSelections();
				
				if(Ext.isEmpty(selectedRows)) return;

				var license_ids = [];
				for(var i=0; i< selectedRows.length; i++){
					license_ids.push(selectedRows[i].get('id'));
				}

				setProvide('N', license_ids.join(','));
			}
		}]
	});

	function setProvide(flag, license_ids){
		Ext.Ajax.request({
			url: '/store/license/setLicense.php',
			params: {
				action:			'provide',
				license_ids:	license_ids,
				provide:		flag
			},
			callback: function(opts, success, response){
				if(success){
					try{
						var r = Ext.decode(response.responseText);
						if(r.success){
							Ext.getCmp('license_list').getStore().reload();
						}										
						else{
							Ext.Msg.alert('오류', r.msg);
						}
					}
					catch(e){
						Ext.Msg.alert('오류', e+'<br />'+response.responseText);
					}
				}
				else{
					Ext.Msg.alert('오류', response.statusText);
				}
			}
		});
	}

	function registProjForm(flag, id){
		var title = '';
		var proj_data = '';
		var is_disable = false;

		if(flag == 'regist'){
			title = '프로젝트 등록';
		}
		else if(flag == 'update' && !Ext.isEmpty(id)){
			proj_data = Ext.getCmp('proj_list').getStore().getById(id);
			if(proj_data.get('use_yn') != 'Y'){
				is_disable = true;
			}

			title = '프로젝트 수정';
		}
		else return false;

		var registProjForm = new Ext.Window({
			title: title,
			width: 1100,
			height: 400,
			layout: 'border',
			modal: true,
			items: [{
				width: 300,
				region: 'west',
				xtype: 'form',
				title: '프로젝트 정보',
				id: 'registProjForm_proj',
				tbar: ['->',{
					icon: '/led-icons/application_edit.png',				
					text: '저장',
					handler: function(btn){
						if(Ext.getCmp('registProjForm_proj').getForm().isValid()) {
							var registProjForm_proj_data = Ext.getCmp('registProjForm_proj').getForm().getValues();

							if(!Ext.isEmpty(registProjForm_proj_data.proj_id)) flag = 'update';
							
							requestProj(flag, registProjForm_proj_data);
						}
						else{
							Ext.Msg.alert('알림', '필수입력 항목을 입력해주세요.');
							return;
						}
					}
				}],
				defaults: {
					xtype: 'textfield',
					width: 150
				},
				padding: 5,
				labelWidth: 80,
				items: [{
					hidden: true,
					name: 'proj_id'
				},{
					fieldLabel: '<font color=red style="font-weight:bold;">*</font>고객사',
					name: 'cust_nm',
					allowBlank: false,
					maxLength: 100
				},{
					fieldLabel: '<font color=red style="font-weight:bold;">*</font>프로젝트',
					name: 'proj_nm',
					allowBlank: false,
					maxLength: 50
				},{
					fieldLabel: '담당자(고객)',
					name: 'cust_user_nm',
					maxLength: 100
				},{
					fieldLabel: '전화번호',
					name: 'phone'
				},{
					fieldLabel: '이메일',
					name: 'email'
				},{
					xtype: 'textarea',
					fieldLabel: '비고',
					name: 'description',
					maxLength: 2000
				}],
				listeners: {
					afterrender: function(self){
						if(!Ext.isEmpty(proj_data)) self.getForm().setValues(proj_data.data);
					}
				}
			},{
				region: 'center',
				xtype: 'grid',
				title: 'License 정보',
				id: 'registProjForm_license',
				tbar: ['->',{
					icon: '/led-icons/application_add.png',
					disabled: is_disable,
					text: '추가',
					handler: function(btn){
						var registProjForm_proj = Ext.getCmp('registProjForm_proj').getForm().getValues();
						
						if(Ext.isEmpty(registProjForm_proj.proj_id)){
							Ext.Msg.alert('알림', '프로젝트 정보를 저장해주세요.');
							return;
						}

						registLicenseForm(registProjForm_proj.proj_id, 'registProjForm_license');
					}
				},'-',{
					icon: '/led-icons/application_delete.png',
					disabled: is_disable,
					text: '삭제',
					handler: function(btn){
						requestLicense('delete', 'registProjForm_license');
					}
				}],
				listeners: {
					render: function(self){
						if(flag == 'update'){
							self.store.load();
						}
					}
				},
				loadMask: true,
				
				store: new Ext.data.JsonStore({					
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
						'cpu_id',
						'cpu_info',
						'memory',
						'server_nm',
						'license_key',
						'create_user',
						{name: 'create_date', type: 'date', dateFormat:'YmdHis'},
						{name: 'complete_date', type: 'date', dateFormat:'YmdHis'},
						{name: 'expire_date', type: 'date', dateFormat:'YmdHis'},
						'description'
					],
					listeners: {
						exception: function(self, type, action, opts, response, args){
							try {
								var r = Ext.decode(response.responseText);
								if(!r.success) {
									Ext.Msg.alert('정보', r.msg);
								}
							}
							catch(e) {
								Ext.Msg.alert('디코드 오류', e);
							}
						},
						beforeload: function(self, opts){
							var proj_id = Ext.getCmp('registProjForm_proj').getForm().findField('proj_id').getValue();
							self.baseParams = {
								proj_id: proj_id
							}
						}
					}
				}),
				colModel: new Ext.grid.ColumnModel({
					columns: [
						new Ext.grid.RowNumberer(),
						{header: '구분', dataIndex: 'license_type_nm', width: 100, align:'center'},
						{header: '품명', dataIndex: 'product_nm', width: 100},
						{header: '등록방법', dataIndex: 'reg_type_nm', width: 80},
						{header: '사용기한', dataIndex: 'use_term_nm', width: 60, align:'center'},
						{header: '등록상태', dataIndex: 'reg_status', width: 80, align:'center', renderer: function(value, metaData, record){
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
						{header: '등록일', dataIndex: 'create_date', width: 150, align:'center', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
						{header: '서버 설치일', dataIndex: 'complete_date', width: 150, align:'center', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
						{header: '사용 만료일', dataIndex: 'expire_date', width: 150, align:'center', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
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
				handler: function(btn){
					Ext.getCmp('proj_list').getStore().load();
					Ext.getCmp('license_list').getStore().removeAll();
					btn.ownerCt.ownerCt.close();
				}
			}]
		});

		registProjForm.show();
		return true;
	}

	function requestProj(flag, registProjForm_proj_data){
		Ext.Ajax.request({
			url: '/store/license/setProject.php',
			params: {
				action:			flag,
				id:				registProjForm_proj_data.proj_id,
				cust_nm:		registProjForm_proj_data.cust_nm,
				proj_nm:		registProjForm_proj_data.proj_nm,
				cust_user_nm:	registProjForm_proj_data.cust_user_nm,
				phone:			registProjForm_proj_data.phone,
				email:			registProjForm_proj_data.email,
				description:	registProjForm_proj_data.description
			},
			callback: function(opts, success, response){
				if(success){
					try{
						var r = Ext.decode(response.responseText);
						if(r.success){
							if(flag == 'regist' || flag == 'update'){
								selectProjRow = r.id;
								Ext.getCmp('registProjForm_proj').getForm().findField('proj_id').setValue(r.id);
							}
							else{
								selectProjRow = '';
								Ext.getCmp('proj_list').getStore().load();
							}
						}											
						else{
							Ext.Msg.alert('오류', r.msg);
						}
					}
					catch(e){
						Ext.Msg.alert('오류', e+'<br />'+response.responseText);
					}
				}
				else{
					Ext.Msg.alert('오류', response.statusText);
				}
			}
		});
	}

	function registLicenseForm(proj_id, list_id){
		if(Ext.isEmpty(proj_id)) return;

		var registLicenseForm = new Ext.Window({
			title: 'License 등록',
			id: 'registLicenseForm',
			width: 320,
			height: 330,
			xtype: 'panel',
			layout: 'fit',
			modal: true,
			items: [{
				xtype: 'form',
				frame: true,
				labelWidth: 80,
				defaults: {
					width: 205
				},
				autoScroll: true,
				items: [{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '등록방법',
					name: 'reg_type',
					allowBlank: false,
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							<?php
							unset($t);
							$product_list = $db->queryAll("
								SELECT	C.CODE, C.CODE_NM
								FROM	BC_CODE C
									, BC_CODE_TYPE CT
								WHERE	CT.ID = C.CODE_TYPE_ID
								AND	CT.CODE = 'REG_TYPE'
								AND C.CODE != '0'
								ORDER BY C.SORT ASC
							");
							foreach($product_list as $row){
								$t[] = "['".$row['code']."', '".$row['code_nm']."']";
							}
							echo implode(', ', $t);
							?>
						]
					}),
					listeners: {
						beforeselect: function(self,r,idx) {
							var value = r.get('v');
							if(value == 'ETC') value = 'OTHERS';
							else value = 'OURS';

							var registLicenseForm = Ext.getCmp('registLicenseForm').get(0);
							
							var registLicenseBasicForm = registLicenseForm.getForm();

							var product_field = registLicenseBasicForm.findField('product');
							product_field.getStore().load({
								params: { 
									product_type: value
								},
								callback: function(records) {
									product_field.reset();
								}
							});
							
							if(r.get('v') == 'OFFLINE'){
								/*
								* 2016-02-22 송민정
								* 오프라인 라이센스 등록 시 등록 완료로 고정되도록 수정
								* 신기철 팀장님 요청
								*/
								registLicenseBasicForm.findField('reg_status').setValue('R99');
								registLicenseBasicForm.findField('reg_status').setDisabled(true);

								var off_license_fields = registLicenseForm.find('licenseType', 'off');
								if(Ext.isEmpty(off_license_fields)){

									registLicenseForm.add({
										xtype: 'textfield',
										fieldLabel: 'MAC주소',
										name: 'mac_address',
										allowblank: false
										//,emptyText: '-를 제외하고 입력해주세요.'
									/*
										xtype: 'container',
										fieldLabel: 'MAC주소',	
										name: 'mac_address',							
										licenseType: 'off',
										layout: 'hbox',
										defaults: {
											xtype: 'textfield',
											allowBlank: false,
											width: 27,
											maxLength: 2,
											enableKeyEvents: true,
											style: {
												'text-transform': 'uppercase'
											},
											listeners: {
												keyup: function(self, e){
													var reg = /^[A-Z0-9]{2}$/;
													var text = self.getValue().toUpperCase();

													if (text.length > 2){
														self.setValue(text.substring(0, 2));
													}
													if(reg.test(self.getValue()) && self.name != 'mac6'){
														var num = self.name.substr(3,1);
														self.ownerCt.items.items[(num)*2].focus();
													}
												}
											}
										},
										items: [{
											name: 'mac1',
											allowblank: false
										},{
											xtype: 'label',
											text: '-',
											width: 8,
											style: {
												'line-height': '22px',
												'text-align': 'center'
											}
										},{
											name: 'mac2',
											allowblank: false
										},{
											xtype: 'label',
											text: '-',
											width: 8,
											style: {
												'line-height': '22px',
												'text-align': 'center'
											}
										},{
											name: 'mac3',
											allowblank: false
										},{
											xtype: 'label',
											text: '-',
											width: 8,
											style: {
												'line-height': '22px',
												'text-align': 'center'
											}
										},{
											name: 'mac4',
											allowblank: false
										},{
											xtype: 'label',
											text: '-',
											width: 8,
											style: {
												'line-height': '22px',
												'text-align': 'center'
											}
										},{
											name: 'mac5',
											allowblank: false
										},{
											xtype: 'label',
											text: '-',
											width: 8,
											style: {
												'line-height': '22px',
												'text-align': 'center'
											}
										},{
											name: 'mac6',
											allowblank: false
										}]
									*/
									});

									registLicenseForm.doLayout();
								};

								registLicenseBasicForm.findField('create_license_cnt').setValue('');
								registLicenseBasicForm.findField('create_license_cnt').setDisabled(true);
							}
							else{
								//registLicenseBasicForm.findField('reg_status').reset();
								registLicenseBasicForm.findField('reg_status').setValue('R02');
								registLicenseBasicForm.findField('reg_status').setDisabled(false);
								
								var off_license_fields = registLicenseForm.find('licenseType', 'off');
								if(!Ext.isEmpty(off_license_fields)){
									Ext.each(off_license_fields, function(off_field){
										registLicenseForm.remove(off_field);
									});
								}

								registLicenseBasicForm.findField('create_license_cnt').setDisabled(false);
							}
						}
					}
				},{
					xtype: 'combo',
					typeAhead: true,
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'product_nm',
					valueField: 'product_id',
					fieldLabel: '품명',
					name: 'product',
					allowBlank: false,
					store: new Ext.data.JsonStore({
						autoLoad: true,
						url: '/store/product/getProductList.php',
						root: 'data',
						fields: [
							'product_id', 'product_nm'
						],
						baseParams: {
							use_yn: 'Y'
						}
					})
				},{
					xtype: 'numberfield',
					name: 'channel_cnt',
					fieldLabel: '채널 수',
					value: 1
				/*
				},{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '라이센스 구분',
					name: 'license_type',
					allowBlank: false,
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							<?php
							unset($t);
							$product_list = $db->queryAll("
								SELECT	C.CODE, C.CODE_NM
								FROM	BC_CODE C
									, BC_CODE_TYPE CT
								WHERE	CT.ID = C.CODE_TYPE_ID
								AND	CT.CODE = 'LICENSE_TYPE'
								AND C.CODE != '0'
								ORDER BY C.SORT ASC
							");
							foreach($product_list as $row){
								$t[] = "['".$row['code']."', '".$row['code_nm']."']";
							}
							echo implode(', ', $t);
							?>
						]
					})
				*/
				},{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '등록상태',
					id: 'reg_status',
					allowBlank: false,
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							<?php
							unset($t);
							$product_list = $db->queryAll("
								SELECT	C.CODE, C.CODE_NM
								FROM	BC_CODE C
									, BC_CODE_TYPE CT
								WHERE	CT.ID = C.CODE_TYPE_ID
								AND	CT.CODE = 'REG_STATUS'
								AND C.CODE != '0'
								ORDER BY C.SORT ASC
							");
							
							foreach($product_list as $row){
								$t[] = "['".$row['code']."', '".$row['code_nm']."']";
							}
							echo implode(', ', $t);
							?>
						]
					}),
					value: 'R02'
				},{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '사용기한',
					name: 'use_term',
					allowBlank: false,
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							<?php
							unset($t);
							$product_list = $db->queryAll("
								SELECT	C.CODE, C.CODE_NM
								FROM	BC_CODE C
									, BC_CODE_TYPE CT
								WHERE	CT.ID = C.CODE_TYPE_ID
								AND	CT.CODE = 'USE_TERM'
								ORDER BY C.SORT ASC
							");
							
							$i = 0;
							foreach($product_list as $row){
								$t[] = "['".$row['code']."', '".$row['code_nm']."']";
								if($i == 0) $t[] = "['br', '---------------------']";
								
								$i++;
							}
							echo implode(', ', $t);
							?>
						]
					})
				},{
					xtype: 'container',
					layout: 'hbox',
					fieldLabel: '생성 수량',
					items: [{
						xtype: 'textfield',
						width: 20,
						name: 'create_license_cnt',
						regex: /^([1-9]{1}$)|(10$)/,
						allowBlank: false
					},{
						xtype: 'label',
						text: '개',
						margins: '3 0 0 0'
					},{
						xtype: 'label',
						flex: 1,
						text: ' (최대 수량은 10개입니다.)',
						margins: '3 0 0 0',
						style: 'color:red'
					}]
				},{
					xtype: 'textarea',
					name: 'description',
					fieldLabel: '비고',
					maxLength: 2000
				}]
			}],
			buttons: [{
				text: 'License Key 추가',
				handler: function(btn){
					var registLicenseForm = Ext.getCmp('registLicenseForm').items.get(0).getForm();
					if(!registLicenseForm.isValid()){
						Ext.Msg.alert('알림', '필수 입력 항목을 입력해주세요.');
						return;
					}
					if(registLicenseForm.findField('use_term').getValue() == 'br'){
						Ext.Msg.alert('알림', '사용기한 값을 올바르게 입력해주세요.');
						return;
					}
					

					var registLicenseForm_data = registLicenseForm.getFieldValues();

					var mac_reg1 = /^[A-Za-z0-9]{12}$/;
					var mac_reg2 = /^[A-Za-z0-9]{2}-[A-Za-z0-9]{2}-[A-Za-z0-9]{2}-[A-Za-z0-9]{2}-[A-Za-z0-9]{2}-[A-Za-z0-9]{2}$/;
					
					if(registLicenseForm_data.reg_type.toUpperCase() == 'OFFLINE'){
						if(mac_reg1.test(registLicenseForm_data.mac_address)){
							registLicenseForm_data.mac_address = registLicenseForm_data.mac_address.substr(0,2)+'-'+registLicenseForm_data.mac_address.substr(2,2)+'-'+registLicenseForm_data.mac_address.substr(4,2)+'-'+registLicenseForm_data.mac_address.substr(6,2)+'-'+registLicenseForm_data.mac_address.substr(8,2)+'-'+registLicenseForm_data.mac_address.substr(10,2);

							registLicenseForm_data.mac_address = registLicenseForm_data.mac_address.toUpperCase();
						}
						else if(mac_reg2.test(registLicenseForm_data.mac_address)){
							registLicenseForm_data.mac_address = registLicenseForm_data.mac_address.toUpperCase();
						}
						else{
							Ext.Msg.alert('알림', 'MAC 주소 값을 올바르게 입력해주세요.');
							return;
						}
					}
					/*
					registLicenseForm_data.mac_address = '';
					if(!Ext.isEmpty(registLicenseForm_data.mac1) && registLicenseForm_data.mac1 != 'undefined' 
					&& !Ext.isEmpty(registLicenseForm_data.mac2) && registLicenseForm_data.mac2 != 'undefined' 
					&& !Ext.isEmpty(registLicenseForm_data.mac3) && registLicenseForm_data.mac3 != 'undefined' 
					&& !Ext.isEmpty(registLicenseForm_data.mac4) && registLicenseForm_data.mac4 != 'undefined' 
					&& !Ext.isEmpty(registLicenseForm_data.mac5) && registLicenseForm_data.mac5 != 'undefined' 
					&& !Ext.isEmpty(registLicenseForm_data.mac6) && registLicenseForm_data.mac6 != 'undefined' ) {
						registLicenseForm_data.mac_address = registLicenseForm_data.mac1 + '-' + registLicenseForm_data.mac2 + '-' + registLicenseForm_data.mac3 + '-' + registLicenseForm_data.mac4 + '-' + registLicenseForm_data.mac5 + '-' + registLicenseForm_data.mac6;
					}
					*/
					
					Ext.Ajax.request({
						url: '/store/license/setLicense.php',
						params: {
							action:			'regist',
							proj_id:		proj_id,
							reg_type:		registLicenseForm_data.reg_type,
							license_type:	registLicenseForm_data.license_type,
							product:		registLicenseForm_data.product,
							channel_cnt:	registLicenseForm_data.channel_cnt,
							use_term:		registLicenseForm_data.use_term,
							reg_status:		registLicenseForm_data.reg_status,
							license_cnt:	registLicenseForm_data.create_license_cnt,
							os:				registLicenseForm_data.os,
							cpu_id:			registLicenseForm_data.cpu_id,
							cpu_info:		registLicenseForm_data.cpu_info,
							memory:			registLicenseForm_data.memory,
							mac_address:	registLicenseForm_data.mac_address,
							adapter_nm:		registLicenseForm_data.adapter_nm,
							server_nm:		registLicenseForm_data.server_nm,
							description:	registLicenseForm_data.description
						},
						callback: function(opts, success, response){
							if(success){
								try{
									var r = Ext.decode(response.responseText);
									if(r.success){
										selectLicenseRow = r.id;
										Ext.getCmp(list_id).getStore().reload();
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
				}
			},{
				text: '닫기',
				handler: function(btn){
					Ext.getCmp(list_id).getStore().reload();
					btn.ownerCt.ownerCt.close();
				}
			}]
		});

		registLicenseForm.show();
		return true;
	}

	function requestLicense(action, list_id){
		if(action == 'delete'){
			var login_id = '<?=$_SESSION['user']['user_id']?>';
			if(Ext.isEmpty(login_id)){
				Ext.Msg.alert('알림', '세션이 만료되었습니다. 다시 로그인해주세요.');
				return;				
			}

			var selectedLicense = Ext.getCmp(list_id).getSelectionModel().getSelected();
			if(Ext.isEmpty(selectedLicense)){
				Ext.Msg.alert('알림', '삭제할 License를 선택해주세요.');
				return;
			}

			Ext.Ajax.request({
				url: '/store/user/getUser.php',
				params: {
					action:		'get_user_level',
					login_id:	login_id
				},
				callback: function(opts, success, response){
					if(success){
						try{
							var r = Ext.decode(response.responseText);
							if(r.success){
								if(r.user_level == 'L09' || r.user_level == 'L04'){
									var confirm_pwd = new Ext.Window({
										title: '비밀번호 확인',
										id: 'license_del_confirm_pwd',
										width: 270,
										height: 120,
										xtype: 'panel',
										layout: 'fit',
										modal: true,
										items: [{
											xtype: 'form',
											layout: 'vbox',
											frame: true,
											border: true,
											id: 'license_del_confirm_pwd_form',
											labelWidth: 1,
											items: [{
												flex: 1,
												xtype: 'label',
												text: '사용자 확인을 위해 비밀번호를 입력해주세요.',
												style: 'margin-bottom:3px'
											},{
												flex: 1,
												xtype: 'textfield',
												inputType: 'password',
												name: 'pwd'
											}]
										}],
										buttons: [{
											text: '확인',
											handler: function(btn){
												var pwd = Ext.getCmp('license_del_confirm_pwd_form').getForm().findField('pwd').getValue();
												if(Ext.isEmpty(pwd)){
													Ext.Msg.alert('알림', '비밀번호를 입력해주세요.');
													return;
												}

												Ext.Ajax.request({
													url: '/store/user/getUser.php',
													params: {
														action:		'confirm_pwd',
														login_id:	'<?=$_SESSION['user']['user_id']?>',
														pwd:		CryptoJS.SHA512(pwd.trim())
													},
													callback: function(opts, success, response){
														if(success){
															try{
																var r = Ext.decode(response.responseText);
																if(r.success){
																	//라이센스 삭제
																	Ext.Ajax.request({
																		url: '/store/license/setLicense.php',
																		params: {
																			action:	'remove',
																			id:		selectedLicense.get('id')
																		},
																		callback: function(opts, success, response){
																			if(success){
																				try{
																					var r = Ext.decode(response.responseText);
																					if(r.success){
																						selectLicenseRow = '';
																						Ext.getCmp(list_id).getStore().reload();
																					}
																					else{
																						Ext.Msg.alert('오류', r.msg);
																					}
																				}
																				catch(e){
																					Ext.Msg.alert('오류', e+'<br />'+response.responseText);
																				}
																			}
																			else{
																				Ext.Msg.alert('오류', response.statusText);
																			}
																		}
																	});
																}
																else{
																	Ext.Msg.alert('알림', "비밀번호가 틀렸습니다.<br>라이센스를 삭제할 수 없습니다.");
																	return;
																}
															}
															catch(e){
																Ext.Msg.alert('오류', e+'<br />'+response.responseText);
															}
														}
														else{
															Ext.Msg.alert('오류', response.statusText);
														}
													}
												});
												btn.ownerCt.ownerCt.close();
											}
											 
										},{
											text: '취소',
											handler: function(btn){
												btn.ownerCt.ownerCt.close();
											}
										}]
									});

									confirm_pwd.show();
								}
								else{
									Ext.Msg.alert('알림', "라이센스관리자 또는 시스템관리자 외에는 라이센스를 삭제할 수 없습니다.<br>관리자에게 문의해주세요.");
								}
							}
							else{
								Ext.Msg.alert('오류', r.msg);
							}
						}
						catch(e){
							Ext.Msg.alert('오류', e+'<br />'+response.responseText);
						}
					}
					else{
						Ext.Msg.alert('오류', response.statusText);
					}
				}
			});
		}
	}

	function viewLicenseInformation(licenseIndex){
		var LicenseData = Ext.getCmp('license_list').getStore().getAt(licenseIndex);
		var viewLicenseInformation = new Ext.Window({
			title: 'License 정보',
			id: 'viewLicenseInformation',
			width: 380,
			height: 350,
			xtype: 'panel',
			layout: 'vbox',
			modal: true,
			items: [{
				flex: 1,
				width: '100%',
				xtype: 'form',
				labelAlign: 'right',
				labelWidth: 100,
				items: [{
					xtype: 'label',
					fieldLabel: 'LICENSE KEY',
					text: LicenseData.get('license_key')
				},{
					xtype: 'label',
					fieldLabel: 'OS',
					text: LicenseData.get('os')
				},{
					xtype: 'label',
					fieldLabel: 'CPU ID',
					text: LicenseData.get('cpu_id')
				},{
					xtype: 'label',
					fieldLabel: 'CPU INFO',
					text: LicenseData.get('cpu_info')
				},{
					xtype: 'label',
					fieldLabel: 'MEMORY',
					text: LicenseData.get('memory')
				},{
					xtype: 'label',
					fieldLabel: 'SERVER NAME',
					text: LicenseData.get('server_nm')
				}]
			},{				
				flex: 1,
				width: '100%',
				xtype: 'editorgrid',
				//title: 'Mac Address',
				store: new Ext.data.JsonStore({					
					url: '/store/license/getLicenseMacAddress.php',
					remoteSort: true,
					baseParams: {
						license_id: LicenseData.get('id')
					},
					sortInfo: {
						field: 'create_date',
						direction: 'DESC'
					},
					idProperty: 'id',
					root: 'data',
					fields: [
						'id',
						'mac_addr',
						'adapter_name'
					],
					listeners: {
						exception: function(self, type, action, opts, response, args){
							try {
								var r = Ext.decode(response.responseText);
								if(!r.success) {
									Ext.Msg.alert('정보', r.msg);
								}
							}
							catch(e) {
								Ext.Msg.alert('디코드 오류', e);
							}
						}
					}
				}),
				selModel: new Ext.grid.RowSelectionModel({
					singleSelect: false
				}),
				colModel: new Ext.grid.ColumnModel({
					columns: [
						{header: 'id', dataIndex: 'id', hidden: true},
						{header: 'MAC Address', dataIndex: 'mac_addr', width: 250, editor: new Ext.form.TextField({readOnly: true})}
					]
				}),
				listeners: {
					afterrender: function(self){
						self.store.load();
					}
				}
			}],
			buttons: [{
				text: '닫기',
				handler: function(btn){
					btn.ownerCt.ownerCt.close();
				}
			}]
		});

		viewLicenseInformation.show();
		return true;
	}

	return licensePanel;
})()