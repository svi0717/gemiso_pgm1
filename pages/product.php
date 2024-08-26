<?
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');
?>
(function(){
	var productPanel = {
		xtype: 'panel',
		layout: 'fit',
		items: [{
			xtype: 'grid',
			id: 'product_list',
			tbar: ['구분 : ',{
				width: 150,
				xtype: 'combo',
				id: 'search_f_product_type',
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
							FROM 	BC_CODE C, BC_CODE_TYPE CT
							WHERE	C.CODE_TYPE_ID = CT.ID
							AND		CT.CODE = 'PRODUCT_TYPE'
							ORDER BY SORT ASC

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
					}
				}
			},'회사명 : ',{
				width: 200,
				xtype: 'textfield',
				id: 'search_f_company_nm'
			},'제품명 : ',{
				width: 200,
				xtype: 'textfield',
				id: 'search_f_product_nm'
			},{
				text: '검색',
				icon: '/led-icons/magnifier.png',
				listeners: {
					click: function(self){
						Ext.getCmp('product_list').getStore().load();
					}
				}
			},'-',{
				text: '등록',
				icon: '/led-icons/application_add.png',
				handler: function(btn, e){
					registProductForm('regist', '');
				}
			},'-',{
				text: '수정',
				icon: '/led-icons/application_edit.png',
				handler: function(btn, e){
					var select_prod = Ext.getCmp('product_list').getSelectionModel().getSelected();
					if(Ext.isEmpty(select_prod)){
						Ext.Msg.alert('알림', '수정할 프로젝트를 선택해주세요.');
						return;
					}
					
					registProductForm('update', select_prod.get('product_id'));
				}
			}
			
			,'-',{
				text: '삭제',
				icon: '/led-icons/application_delete.png',
				handler: function(btn, e){
					var select_proj = Ext.getCmp('product_list').getSelectionModel().getSelected();
					if(Ext.isEmpty(select_proj)){
						Ext.Msg.alert('알림', '삭제할 프로젝트를 선택해주세요.');
						return;
					}
					Ext.Msg.show({
						title: '알림',
						msg: select_proj.get('product_nm')+' 제품을 삭제하시겠습니까?',
						buttons: Ext.Msg.OKCANCEL,
						fn: function(btn){
							if(btn == 'ok'){
								request('remove', select_proj.data);
							}
						}								
					});	

				}
			}
			
			],
			listeners: {
				afterrender: function(self){
					self.store.load();
				},
				rowdblclick: function(grid, rowIndex, e){
					if(Ext.isEmpty(rowIndex)) return;
					var select_row = grid.getStore().getAt(rowIndex);

					registProductForm('update', select_row.get('product_id'));
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
						items: [
							{
								text : '수정',
								icon: '/led-icons/application_edit.png',
								handler: function(b, e){
									var sm = self.getSelectionModel();
									if(sm.hasSelection()){
										registProductForm('update', sm.getSelected().get('product_id'));
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
							}
							/*,{
								text: '삭제',
								icon: '/led-icons/application_delete.png',
								handler: function(b, e){
									var sm = self.getSelectionModel();
									if(sm.hasSelection()){
										Ext.Msg.show({
											title: '알림',
											msg: sm.getSelected().get('product_nm')+' 제품을 삭제하시겠습니까?',
											buttons: Ext.Msg.OKCANCEL,
											fn: function(btn){
												if(btn == 'ok'){
													request('remove', sm.getSelected().data);
												}
											}								
										});	
									}else{
										Ext.Msg.alert('알림', '삭제할 프로젝트를 선택해주세요.');
									}
								}
							}*/
						]
					});
					menu.showAt(e.getXY());
				}
			},
			loadMask: true,
			store: new Ext.data.JsonStore({
				url: '/store/product/getProductList.php',
				remoteSort: true,
				/*sortInfo: {
					field: 'product_nm',
					direction: 'ASC'
				},*/
				idProperty: 'product_id',
				root: 'data',
				fields: [
					'product_id',
					'product_nm',
					'product_type',
					'product_type_nm',
					'company_nm',
					'create_user',
					{name: 'create_date', type: 'date', dateFormat:'YmdHis'},
					'garantia_term',
					'garantia_term_nm',
					'description',
					'use_yn'
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
						var product_type = Ext.getCmp('search_f_product_type').getValue();
						var company_nm = Ext.getCmp('search_f_company_nm').getValue();
						var product_nm = Ext.getCmp('search_f_product_nm').getValue();

						self.baseParams = {
							product_type: product_type,
							company_nm: company_nm,
							product_nm: product_nm
						}
					},
					load: function(self, records, opts){
						if(records.length > 0) Ext.getCmp('product_list').getSelectionModel().selectFirstRow();
					}
				}
			}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true
				},
				columns: [
					new Ext.grid.RowNumberer(),
					{header: '회사명', dataIndex: 'company_nm', width: 120},
					{header: '제품ID', dataIndex: 'product_id', width: 70, hidden:true},
					{header: '제품명', dataIndex: 'product_nm', width: 120},
					{header: '제품구분', dataIndex: 'product_type_nm', width: 120},
					{header: '보증기한', dataIndex: 'garantia_term_nm', width: 70},
					{header: '제품설명', dataIndex: 'description', width: 200},
					{header: '사용여부', dataIndex: 'use_yn', width: 60, renderer: function(value){ if(value == 'Y') return '사용'; else return '미사용'; }},
					{header: '등록자', dataIndex: 'create_user', width: 120},
					{header: '등록일', dataIndex: 'create_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
				]
			}),
			viewConfig: {
				forseFit: true
			}
		}]
	};

	function registProductForm(flag, product_id){
		var title = '';

		if(flag == 'regist'){
			title = '등록';
		}
		else if(flag == 'update'){
			title = '수정';
		}
		var registProjForm = new Ext.Window({
			title: '제품 '+title,
			width: 300,
			height: 240,
			layout: 'fit',
			modal: true,
			items: [{
				xtype: 'form',
				id: 'registProductForm',
				frame: true,
				labelWidth: 60,
				defaults: {
					xtype: 'textfield',
					width: 120
				},
				items: [{
					fieldLabel: '제품명',
					name: 'product_nm',
					allowBlank: false
				},{
					xtype: 'textfield',
					name: 'company_nm',
					fieldLabel: '회사명',
					allowBlank: false
				},{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '보증기한',
					allowBlank: false,
					name: 'garantia_term',
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
							foreach($product_list as $row){
								$t[] = "['".$row['code']."', '".$row['code_nm']."']";
							}
							echo implode(', ', $t);
							?>
						]
					})
				},{
					xtype: 'combo',
					triggerAction: 'all',
					editable: false,
					mode: 'local',
					displayField: 'd',
					valueField: 'v',
					fieldLabel: '제품구분',
					allowBlank: false,
					name: 'product_type',
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
								AND	CT.CODE = 'PRODUCT_TYPE'
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
				},{
					name: 'product_id',
					hidden: true
				}],
				listeners: {
					afterrender: function(self){
						if(!Ext.isEmpty(product_id)){
							var product_data = Ext.getCmp('product_list').getStore().getById(product_id);
							
							self.getForm().setValues(product_data.data);
						}
					}
				}
			}],
			buttons: [{
				text: title,
				handler: function(btn){
					var registProductForm = Ext.getCmp('registProductForm').getForm();
					
					if(!registProductForm.isValid()){
						Ext.Msg.alert('알림', '필수 입력 항목을 입력해주세요.');
						return;
					}
					
					var registProductForm_data = registProductForm.getFieldValues();

					request(flag, registProductForm_data);
				}
			},{
				text: '닫기',
				handler: function(btn){
					btn.ownerCt.ownerCt.close();
				}
			}]
		});

		registProjForm.show();
	}

	function request(action, data){
		var action_p;
		if(action == 'change_useyn'){
			action_p = 'update';
			if(data.use_yn == 'Y') data.use_yn = 'N';
			else if(data.use_yn == 'N') data.use_yn = 'Y';
		}
		else{
			action_p = action;
		}

		Ext.Ajax.request({
			url: '/store/product/setProduct.php',
			params: {
				action:			action_p,
				product_id:		data.product_id,
				product_nm:		data.product_nm,
				company_nm:		data.company_nm,
				garantia_term:	data.garantia_term,
				product_type:	data.product_type,
				use_yn:			data.use_yn,
				description:	data.description
			},
			callback: function(opts, success, response){
				if(success){
					try{
						var r = Ext.decode(response.responseText);
						if(r.success){
							Ext.getCmp('product_list').getStore().load();
							if(action == 'regist' || action == 'update'){
								Ext.getCmp('registProductForm').ownerCt.close();
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