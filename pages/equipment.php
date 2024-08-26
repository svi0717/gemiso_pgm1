<?
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');

$product_list = $db->queryAll("
	SELECT	C.CODE, C.CODE_NM
	FROM 	BC_CODE C, BC_CODE_TYPE CT
	WHERE	C.CODE_TYPE_ID = CT.ID
	AND		CT.CODE = 'EQU_TYPE'
	ORDER BY SORT ASC

");
?>
(function(){
	var productPanel = {
		xtype: 'panel',
		layout: 'fit',
		items: [{
			xtype: 'grid',
			id: 'equipment_list',
			tbar: ['구분 : ',{
				width: 150,
				xtype: 'combo',
				id: 'search_f_equ_type',
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
						Ext.getCmp('equipment_list').getStore().load();
					}
				}
			}
			,'-' , '사용자명 : ',
			{
				width: 150,
				xtype: 'textfield',
				id: 'search_f_login_id'
			}
			,'-' , '장비명',
			{
				width: 150,
				xtype: 'textfield',
				id: 'search_f_equ_nm'
			},{
				text: '검색',
				icon: '/led-icons/magnifier.png',
				listeners: {
					click: function(self){
						Ext.getCmp('equipment_list').getStore().load();
					}
				}
			},'-',{
				text: '등록',
				icon: '/led-icons/application_add.png',
				handler: function(btn, e){
					registEquipmentForm('regist', '');
				}
			},'-',{
				text: '수정',
				icon: '/led-icons/application_edit.png',
				handler: function(btn, e){
					var select_prod = Ext.getCmp('equipment_list').getSelectionModel().getSelected();
					if(Ext.isEmpty(select_prod)){
						Ext.Msg.alert('알림', '수정할 프로젝트를 선택해주세요.');
						return;
					}
					
					registEquipmentForm('update', select_prod.get('equ_id'));
				}
			}
			,'-',{
				text: '삭제',
				icon: '/led-icons/application_delete.png',
				handler: function(btn, e){
					var select_proj = Ext.getCmp('equipment_list').getSelectionModel().getSelected();
					if(Ext.isEmpty(select_proj)){
						Ext.Msg.alert('알림', '삭제할 프로젝트를 선택해주세요.');
						return;
					}

					Ext.Msg.show({
						title: '알림',
						msg: '[ '+select_proj.get('equ_type_nm')+' ]<br>'+select_proj.get('equ_nm')+'<br> 장비를 삭제하시겠습니까?',
						buttons: Ext.Msg.OKCANCEL,
						fn: function(btn){
							if(btn == 'ok'){
								request('remove', select_proj.data);
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

					registEquipmentForm('update', select_row.get('equ_id'));
				},
				rowcontextmenu: function(self, rowIndex, e){
					e.stopEvent();
					var sm = self.getSelectionModel();
					if (!sm.isSelected(rowIndex)) {
						sm.selectRow(rowIndex);
					}

					var use_yn_text = '사용';
					if(sm.getSelected().get('use_yn') == 'Y'){
						use_yn_text = '미사용';
					}

					var menu = new Ext.menu.Menu({
						items: [{
							text : '수정',
							icon: '/led-icons/application_edit.png',
							handler: function(b, e){
								var sm = self.getSelectionModel();
								if(sm.hasSelection()){
									registEquipmentForm('update', sm.getSelected().get('equ_id'));
								}else{
									Ext.Msg.alert('알림', '수정할 프로젝트를 선택해주세요.');
								}
							}
						},{
							text : use_yn_text,
							icon: '/led-icons/application_delete.png',
							handler: function(b, e){
								var sm = self.getSelectionModel();
								if(sm.hasSelection()){
									//request('remove', sm.getSelected().data);
									request('change_useyn', sm.getSelected().data);
								}else{
									Ext.Msg.alert('알림', use_yn_text+'할 프로젝트를 선택해주세요.');
								}
							}
						}]
					});
					menu.showAt(e.getXY());
				}
			},
			loadMask: true,
			store: new Ext.data.JsonStore({
				url: '/store/equipment/getEquipmentList.php',
				remoteSort: true,
				/*sortInfo: {
					field: 'product_nm',
					direction: 'ASC'
				},*/
				idProperty: 'equ_id',
				root: 'data',
				fields: [
					'equ_id',
					'equ_nm',
					'equ_type',
					'equ_type_nm',
					'comp_nm',
					'cpu',
					'os',
					'memory',
					'graphics',
					'hdd1',
					'hdd1_sdd_yn',
					'hdd2',
					'hdd2_sdd_yn',
					{name: 'purchase_ymd', type: 'date', dateFormat:'Ymd'},
					{name: 'make_ymd', type: 'date', dateFormat:'Ymd'},
					'use_comment',
					'login_id',
					'description',
					'use_yn',
					{name: 'create_date', type: 'date', dateFormat:'YmdHis'},
					'create_user',
					{name: 'update_date', type: 'date', dateFormat:'YmdHis'},
					'update_user',
					'update_user_nm'
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
						var equ_type = Ext.getCmp('search_f_equ_type').getValue();
						var equ_nm = Ext.getCmp('search_f_equ_nm').getValue();
						var login_id = Ext.getCmp('search_f_login_id').getValue();
						self.baseParams = {
							equ_type: equ_type,
							equ_nm: equ_nm,
							login_id : login_id
						}
					},
					load: function(self, records, opts){
						if(records.length > 0) Ext.getCmp('equipment_list').getSelectionModel().selectFirstRow();
					}
				}
			}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
						sortable: true
				},
				columns: [
					new Ext.grid.RowNumberer(),
					{header: '장비명', dataIndex: 'equ_nm', width: 150},
					//{header: '장비ID', dataIndex: 'equ_id', width: 70},
					{header: '구분', dataIndex: 'equ_type_nm', width: 80},
					{header: '사용자', dataIndex: 'login_id', width: 70},
					{header: '제조사', dataIndex: 'comp_nm', width: 120},
					{header: 'CPU', dataIndex: 'cpu', width: 300},
					{header: '메모리', dataIndex: 'memory', width: 70},
					{header: '그래픽카드', dataIndex: 'graphics', width: 200},
					{header: 'HDD_1', dataIndex: 'hdd1', width: 80},
					{header: 'HDD_2', dataIndex: 'hdd2', width: 80},
					{header: '사용여부', dataIndex: 'use_yn', width: 50, renderer: function(value){ if(value == 'Y') return '사용'; else return '미사용'; }},
					{header: '구매일', dataIndex: 'purchase_ymd', width: 80, renderer: Ext.util.Format.dateRenderer('Y-m-d')},
					{header: '등록일', dataIndex: 'create_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
					{header: '수정자', dataIndex: 'update_user_nm', width: 70},
					{header: '수정일', dataIndex: 'update_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
					
				]
			}),
			viewConfig: {
				forseFit: true
			}
		}]
	};

	function registEquipmentForm(flag, equ_id){
		var title = '';

		if(flag == 'regist'){
			title = '등록';
		}
		else if(flag == 'update'){
			title = '수정';
		}
		var registEquForm = new Ext.Window({
			title: '장비 '+title,
			width: 350,
			height: 450,
			layout: 'fit',
			modal: true,
			items: [{
				xtype: 'form',
				id: 'registEquipmentForm',
				frame: true,
				labelWidth: 60,
				defaults: {
					xtype: 'textfield',
					width: 250
				},
				items: [{
					name: 'equ_id',
					hidden: true
				},{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '구분',
					allowBlank: false,
					name: 'equ_type',
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							<?php
							unset($t);
							foreach($product_list as $row){
								$t[] = "['".$row['code']."', '".$row['code_nm']."']";
							}
							echo implode(', ', $t);
							?>
						]
					})
				},{
					fieldLabel: '장비명',
					name: 'equ_nm',
					allowBlank: false
				},{
					xtype: 'textfield',
					name: 'comp_nm',
					fieldLabel: '제조사명',
					allowBlank: false
				},{
					xtype: 'textfield',
					name: 'login_id',
					fieldLabel: '사용자',
					allowBlank: false
				},{
					xtype: 'datefield',
					name: 'purchase_ymd',
					fieldLabel: '구매일자',
					format: 'Y-m-d'
				},{
					xtype: 'textfield',
					name: 'cpu',
					fieldLabel: 'cpu'
				},{
					xtype: 'textfield',
					name: 'os',
					fieldLabel: 'os'
				},{
					xtype: 'textfield',
					name: 'graphics',
					fieldLabel: '그래픽'
				},{
					xtype: 'textfield',
					name: 'memory',
					fieldLabel: '메모리'
				},{
					xtype: 'textfield',
					name: 'hdd1',
					fieldLabel: 'HDD_1'
				},{
					xtype: 'textfield',
					name: 'hdd2',
					fieldLabel: 'HDD_2'
				},{
					xtype: 'textfield',
					name: 'use_comment',
					fieldLabel: '용도'
				},{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '사용여부',
					allowBlank: false,
					name: 'use_yn',
					store: new Ext.data.ArrayStore({
						fields: [
							'v', 'd'
						],
						data: [
							['Y', '사용'],
							['N', '미사용']
						]
					})
				},{
					xtype: 'textfield',
					name: 'description',
					fieldLabel: '비고'
				}],
				listeners: {
					afterrender: function(self){
						if(!Ext.isEmpty(equ_id)){
							var product_data = Ext.getCmp('equipment_list').getStore().getById(equ_id);
							product_data.data.purchase_ymd = new Date(product_data.data.purchase_ymd);
							
							self.getForm().setValues(product_data.data);
						}
					}
				}
			}],
			buttons: [{
				text: title,
				handler: function(btn){
					var registEquipmentForm = Ext.getCmp('registEquipmentForm').getForm();
					
					if(!registEquipmentForm.isValid()){
						Ext.Msg.alert('알림', '필수 입력 항목을 입력해주세요.');
						return;
					}
					
					var registEquipmentForm_data = registEquipmentForm.getFieldValues();
					
					registEquipmentForm_data.purchase_ymd = new Date(registEquipmentForm_data.purchase_ymd).format('Ymd');

					request(flag, registEquipmentForm_data);
				}
			},{
				text: '닫기',
				handler: function(btn){
					btn.ownerCt.ownerCt.close();
				}
			}]
		});

		registEquForm.show();
	}

	function request(action, data){
		var action_p;
		if(action == 'change_useyn'){
			action_p = 'update';
			if(data.use_yn == 'Y') data.use_yn = 'N';
			else if(data.use_yn == 'N') data.use_yn = 'Y';
			
			data.purchase_ymd = new Date(data.purchase_ymd).format('Ymd');
		}
		else{
			action_p = action;
		}


		Ext.Ajax.request({
			url: '/store/equipment/setEquitment.php',
			params: {
				action:			action_p,
				comp_nm:		data.comp_nm,
				equ_nm:			data.equ_nm,
				equ_type:		data.equ_type,
				login_id:		data.login_id,
				equ_id:			data.equ_id,
				purchase_ymd:	data.purchase_ymd,
				use_comment:	data.use_comment,
				cpu:			data.cpu,
				os:				data.os,
				graphics:		data.graphics,
				memory:			data.memory,
				description:	data.description,
				use_yn:			data.use_yn,
				hdd1:			data.hdd1,
				hdd2:			data.hdd2
			},
			callback: function(opts, success, response){
				if(success){
					try{
						var r = Ext.decode(response.responseText);
						if(r.success){
							Ext.getCmp('equipment_list').getStore().load();
							if(action == 'regist' || action == 'update'){
								Ext.getCmp('registEquipmentForm').ownerCt.close();
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

	return productPanel;
})()