<?
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/lang.php');

?>
(function(){
	var productPanel = {
		xtype: 'panel',
		layout: 'fit',
		items: [{
			xtype: 'grid',
			id: 'user_list',
			tbar: [
			/*
			'사용자 : ',{
				width: 200,
				xtype: 'textfield',
				id: 'search_key'
			},{
				text: '검색',
				icon: '/led-icons/magnifier.png',
				listeners: {
					click: function(self){
						Ext.getCmp('user_list').getStore().load();
					}
				}
			},'-',
			*/
			{
				//text: _text('MN00030'),
				text: '<?= _text('MN00030')?>',
				icon: '/led-icons/application_add.png',
				handler: function(btn, e){
					registUser('regist', '');
				}
			},'-',{
				//text: _text('MN00035'),
				text: '<?= _text('MN00035')?>',
				icon: '/led-icons/application_edit.png',
				handler: function(btn, e){
					var selectRow = Ext.getCmp('user_list').getSelectionModel().getSelected();
					if(Ext.isEmpty(selectRow)){
						Ext.Msg.alert('알림', '수정할 사용자를 선택해주세요.');
						return;
					}
					
					registUser('update_in_userManagement', selectRow.data);
				}
			}
			,'-',{
				//text: _text('MN00031'),
				text: '<?= _text('MN00031')?>',
				icon: '/led-icons/application_delete.png',
				handler: function(btn, e){
					var selectRow = Ext.getCmp('user_list').getSelectionModel().getSelected();
					if(Ext.isEmpty(selectRow)){
						Ext.Msg.alert('알림', '삭제할 프로젝트를 선택해주세요.');
						return;
					}

					request('remove', selectRow.data);
				}
			},'-',{
				icon: '/led-icons/arrow_refresh.png',
				//text: _text('MN00029'),
				text: '<?= _text('MN00029')?>',
				handler: function(btn){
					Ext.getCmp('user_list').getStore().reload();
				}
			},'-',{
				icon: '/led-icons/arrow_refresh.png',
				//text: _text('MN00036'),
				text: '<?= _text('MN00036')?>',
				handler: function(btn){
					var selectRow = Ext.getCmp('user_list').getSelectionModel().getSelected();
					var name = selectRow.data.lastname + selectRow.data.firstname;
					if(Ext.isEmpty(selectRow)){
						Ext.Msg.alert('알림', '삭제할 프로젝트를 선택해주세요.');
						return;
					}
					Ext.Msg.show({
						title: '알림',
						msg: name+' 님의 비밀번호를 gemiso1!로 초기화 하시겠습니까??',
						buttons: Ext.Msg.OKCANCEL,
						fn: function(btn){
							if(btn == 'ok'){
								var return1 = request('reset_password', selectRow.data);
								console.log('return1:::', return1);
								Ext.Msg.alert('알림', name+ '님의 비밀번호가 gemiso1!로 변경되었습니다.');
							}
						}
					});
				}
			}],
			listeners: {
				afterrender: function(self){
					self.store.load();
				},
				rowdblclick: function(grid, rowIndex, e){
					if(Ext.isEmpty(rowIndex)) return;
					var select_row = grid.getStore().getAt(rowIndex);

					registUser('update_in_userManagement', select_row.data);
				}
			},
			loadMask: true,
			store: new Ext.data.JsonStore({
				url: '/store/user/getUser.php',
				remoteSort: true,
				/*sortInfo: {
					field: 'user_nm',
					direction: 'ASC'
				},*/
				idProperty: 'equ_id',
				root: 'data',
				fields: [
					'login_id',
					'firstname',
					'lastname',
					'user_nm',
					'login_pwd',
					'user_level',
					'user_level_nm',
					'job_grade',
					'job_grade_nm',
					'phone',
					'email',
					'use_yn',
					{name: 'create_date', type: 'date', dateFormat: 'YmdHis'},
					'create_user',
					'create_user_nm',
					{name: 'update_date', type: 'date', dateFormat: 'YmdHis'},
					'update_user',
					'update_user_nm',
					'lang_cd'
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
						//var searchkey = Ext.getCmp('search_key').getValue();

						self.baseParams = {
							action: 'getUserList'
							//,searchkey: searchkey
						}
					},
					load: function(self, records, opts){
						if(records.length > 0) Ext.getCmp('user_list').getSelectionModel().selectFirstRow();
					}
				}
			}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true
				},
				columns: [
					new Ext.grid.RowNumberer(),
					{header: '사용자 ID', dataIndex: 'login_id', width: 120},
					{header: '사용자명', dataIndex: 'user_nm', width: 120},
					{header: '직급', dataIndex: 'job_grade_nm', width: 70},
					{header: '사용자 등급', dataIndex: 'user_level_nm', width: 100},
					{header: '연락처', dataIndex: 'phone', width: 120},
					{header: 'E-Mail', dataIndex: 'email', width: 150},
					{header: '등록자', dataIndex: 'create_user_nm', width: 120},
					{header: '등록일', dataIndex: 'create_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
					{header: '수정자', dataIndex: 'update_user_nm', width: 100},
					{header: '수정일', dataIndex: 'update_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
					
				]
			}),
			viewConfig: {
				forseFit: true
			}
		}]
	};

	function registUser(action, userData){
		var title = '';

		if(action == 'regist'){
			title = '등록';
		}
		else if(action == 'update_in_userManagement'){
			title = '수정';
		}

		var registUserForm = new Ext.Window({
			title: '사용자 '+title,
			width: 280,
			id: 'registUserForm',
			height: 315,
			layout: 'fit',
			modal: true,
			items: [{
				xtype: 'form',
				frame: true,
				autoScroll: true,
				labelWidth: 80,
				defaults: {
					width: 150
				},
				items: getUserInputForm(action, userData)
			}],
			buttons: [{
				text: title,
				handler: function(btn){
					var form = btn.ownerCt.ownerCt.get(0).getForm();
					console.log(btn.ownerCt.ownerCt.get(0));
					if(!form.isValid()){
						Ext.Msg.alert('알림', '필수 입력 항목을 입력해주세요.');
						return;
					}

					var values = form.getFieldValues();
					if(action == 'regist'){
						if(Ext.isEmpty(values.password)){
							Ext.Msg.alert('알림', '비밀번호를 입력해주세요.');
							return;
						}
						else if(Ext.isEmpty(values.password_confirm)){
							Ext.Msg.alert('알림', '비밀번호 확인을 입력해주세요.');
							return;
						}
						else if(values.password != values.password_confirm){
							Ext.Msg.alert('알림', '비밀번호와 비밀번호 확인이 일치하지 않습니다.');
							return;
						}
					}
					request(action, values);
				}
			},{
				text: '취소',
				handler: function(btn){
					btn.ownerCt.ownerCt.close();
				}
			}]
		});

		registUserForm.show();
	}

	function request(action, data){

		Ext.Ajax.request({
			url: '/store/user/setUser.php',
			params: {
				action:			action,
				login_id:		data.login_id,
				firstname:		data.firstname,
				lastname:		data.lastname,
				password:		CryptoJS.SHA512(data.password),
				phone:			data.phone,
				email:			data.email,
				job_grade:		data.job_grade,
				user_level:		data.user_level
			},
			callback: function(opts, success, response){
				if(success){
					try{
						var r = Ext.decode(response.responseText);
						if(r.success){
							Ext.getCmp('user_list').getStore().load();
							if(action == 'regist' || action == 'update_in_userManagement'){
								Ext.getCmp('registUserForm').close();
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

	function getUserInputForm(action, userData){
		console.log('userData:::',userData);
		if(action == 'regist'){
			var formItems = [{
				xtype: 'textfield',
				allowBlank: false,
				fieldLabel: '사용자 ID',
				name: 'login_id'
			},{
				xtype: 'textfield',
				allowBlank: false,
				fieldLabel: '성',
				name: 'lastname'
			},{
				xtype: 'textfield',
				allowBlank: false,
				fieldLabel: '이름',
				name: 'firstname'
			},{
				xtype: 'textfield',
				allowBlank: false,
				inputType: 'password',
				fieldLabel: '비밀번호',
				name: 'password'
			},{
				xtype: 'textfield',
				allowBlank: false,
				inputType: 'password',
				fieldLabel: '비밀번호 확인',
				name: 'password_confirm'
			},{
				xtype: 'combo',
				allowBlank: false,
				fieldLabel: '직급',
				name: 'job_grade',
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
			},{
				xtype: 'combo',
				allowBlank: false,
				fieldLabel: '사용자 등급',
				name: 'user_level',
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
						$user_level_list = $db->queryAll("
							SELECT	C.CODE, C.CODE_NM
							FROM	BC_CODE C
								, BC_CODE_TYPE CT
							WHERE	CT.ID = C.CODE_TYPE_ID
							AND	CT.CODE = 'USER_LEVEL'
							AND C.CODE != '0'
							ORDER BY C.SORT ASC
						");
						foreach($user_level_list as $row){
							$t[] = "['".$row['code']."', '".$row['code_nm']."']";
						}
						echo implode(', ', $t);
						?>
					]
				})
			},{
				xtype: 'textfield',
				fieldLabel: 'E-Mail',
				name: 'email'
			},{
				xtype: 'textfield',
				fieldLabel: '연락처',
				name: 'phone'
			}];
		}
		else{
			var formItems = [{
				xtype: 'textfield',
				allowBlank: false,
				fieldLabel: '사용자 ID',
				name: 'login_id',
				value: userData.login_id,
				disabled: true
			},{
				// 위에 사용자 ID 필드가 있지만 disabled: true를 하면
				// 파라메터가 안넘어가서 hidden으로 하나 더 추가해둔것
				xtype: 'textfield',
				allowBlank: false,
				fieldLabel: '사용자 ID',
				name: 'login_id',
				value: userData.login_id,
				hidden: true
			},{
				xtype: 'textfield',
				allowBlank: false,
				fieldLabel: '성',
				value: userData.lastname,
				name: 'lastname'
			},{
				xtype: 'textfield',
				allowBlank: false,
				fieldLabel: '이름',
				value: userData.firstname,
				name: 'firstname'
			},{
				xtype: 'combo',
				allowBlank: false,
				fieldLabel: '직급',
				value: userData.job_grade,
				name: 'job_grade',
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
			},{
				xtype: 'combo',
				allowBlank: false,
				fieldLabel: '사용자 등급',
				value: userData.user_level,
				name: 'user_level',
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
						$user_level_list = $db->queryAll("
							SELECT	C.CODE, C.CODE_NM
							FROM	BC_CODE C
								, BC_CODE_TYPE CT
							WHERE	CT.ID = C.CODE_TYPE_ID
							AND	CT.CODE = 'USER_LEVEL'
							AND C.CODE != '0'
							ORDER BY C.SORT ASC
						");
						foreach($user_level_list as $row){
							$t[] = "['".$row['code']."', '".$row['code_nm']."']";
						}
						echo implode(', ', $t);
						?>
					]
				})
			},{
				xtype: 'textfield',
				fieldLabel: 'E-Mail',
				value: userData.email,
				name: 'email'
			},{
				xtype: 'textfield',
				fieldLabel: '연락처',
				value: userData.phone,
				name: 'phone'
			}];
		}

		return formItems;
	}

	return productPanel;
})()