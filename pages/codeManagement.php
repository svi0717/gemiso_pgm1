<?
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/config.php');
?>
(function(){
	var getCodeManagementPanel = {
		xtype: 'panel',
		layout: 'border',
		defaults: {
			width: '100%',
			split: true
		},
		items: [{
			region: 'center',
			title: '코드유형',
			xtype: 'grid',
			id: 'codeTypeList',
			loadMask: true,
			tbar: [{
				width: 90,
				xtype: 'combo',
				id: 'search_f_c',
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
						['code_type', '코드 유형'],
						['code_type_nm', '코드 유형 명'],
						['code', '코드'],
						['code_nm', '코드 명']
					]
				}),
				listeners: {
					render: function(self){
						self.setValue(self.getStore().getAt(0).get('v'));
					}
				}
			},{
				xtype: 'textfield',
				width: 250,
				id: 'search_v_c'
			},{
				text: '검색',
				icon: '/led-icons/magnifier.png',
				handler: function(btn){
					Ext.getCmp('codeTypeList').getStore().load();
				}
			},'-',{
				text: '등록',
				icon: '/led-icons/application_add.png',
				handler: function(btn){
					registCodeType('regist', '');
				}
			},'-',{
				text: '수정',
				icon: '/led-icons/application_edit.png',
				handler: function(btn){
					var selected_codeType = Ext.getCmp('codeTypeList').getSelectionModel().getSelected();
					if(Ext.isEmpty(selected_codeType)){
						Ext.Msg.alert('알림', '수정할 코드유형을 선택해주세요.');
						return;
					}

					registCodeType('update', selected_codeType.get('id'));
				}
			},'-',{
				text: '삭제',
				icon: '/led-icons/application_delete.png',
				handler: function(btn){
					var selected_codeType = Ext.getCmp('codeTypeList').getSelectionModel().getSelected();
					if(Ext.isEmpty(selected_codeType)){
						Ext.Msg.alert('알림', '삭제할 코드유형을 선택해주세요.');
						return;
					}

					Ext.Msg.show({
						title: '알림',
						msg: selected_codeType.get('code_nm')+' 코드유형을 삭제하시겠습니까?',
						buttons: Ext.Msg.OKCANCEL,
						fn: function(btn){
							if(btn == 'ok'){
								requestCodeType('remove', selected_codeType.data);
							}
						}
					});

				}
			}],
			listeners: {
				afterrender: function(self){
					self.getStore().load();
				},
				rowdblclick: function(grid, rowIndex, e){
					if(Ext.isEmpty(rowIndex)) return;
					var select_row = grid.getStore().getAt(rowIndex);

					registCodeType('update', select_row.get('id'));
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

									registCodeType('update', sm.getSelected().get('id'));
								}else{
									Ext.Msg.alert('알림', '수정할 코드타입을 선택해주세요.');
								}
							}
						},{
							text : '삭제',
							icon: '/led-icons/application_delete.png',
							handler: function(b, e){
								var sm = self.getSelectionModel();
								if(sm.hasSelection()){
									requestCodeType('remove', sm.getSelected().data);
								}else{
									Ext.Msg.alert('알림', '삭제할 코드타입을 선택해주세요.');
								}
							}
						}]
					});
					menu.showAt(e.getXY());
				}
			},
			store: new Ext.data.JsonStore({
				url: '/store/code/getCode.php',
				remoteSort: true,
				idProperty: 'id',
				root: 'data',
				fields: [
					'id',
					'code',
					'code_nm',
					'sort',
					{name: 'create_date', type: 'date', dateFormat:'YmdHis'},
					'create_user'
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
						var search_f = Ext.getCmp('search_f_c').getValue();
						var search_v = Ext.getCmp('search_v_c').getValue();

						self.baseParams = {
							action: 'getCodeType',
							search_f: search_f,
							search_v: search_v
						};
					},
					load: function(self, records){
						if(records.length > 0){
							Ext.getCmp('codeTypeList').getSelectionModel().selectFirstRow();
						}
					}
				}
			}),
			selModel: new Ext.grid.RowSelectionModel({
				listeners: {
					selectionchange: function(selModel){
						if(Ext.isEmpty(selModel.getSelected())) return;

						Ext.getCmp('codeList').getStore().load({
							params: {
								action: 'getCode',
								code_type_id: selModel.getSelected().get('id')
							}
						});
					}
				}
			}),
			colModel: new Ext.grid.ColumnModel({
				columns: [
					new Ext.grid.RowNumberer(),
					{header: 'ID', dataIndex: 'id', width: 50, hidden: true},
					{header: '코드 유형', dataIndex: 'code', width: 150},
					{header: '코드 유형 명', dataIndex: 'code_nm', width: 150},
					{header: '등록자', dataIndex: 'create_user', width: 80},
					{header: '등록일', dataIndex: 'create_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
				]
			}),
			viewConfig: {
				forseFit: true
			}
		},{
			region: 'south',
			height: 450,
			title: '코드',
			xtype: 'grid',
			loadMask: true,
			id: 'codeList',
			tbar: [{
				icon: '/led-icons/arrow_refresh.png',
				text: '새로고침',
				handler: function(btn){
					Ext.getCmp('codeList').getStore().reload();
				}
			},{
				text: '등록',
				icon: '/led-icons/application_add.png',
				handler: function(){
					var selected_codeType = Ext.getCmp('codeTypeList').getSelectionModel().getSelected();
					if(Ext.isEmpty(selected_codeType)){
						Ext.Msg.alert('알림', '코드가 등록될 코드유형을 선택해주세요.');
						return;
					}
					registCode('regist', '', selected_codeType.get('id'));
				}
			},'-',{
				text: '수정',
				icon: '/led-icons/application_edit.png',
				handler: function(btn){
					var selected_codeType = Ext.getCmp('codeTypeList').getSelectionModel().getSelected();
					if(Ext.isEmpty(selected_codeType)){
						Ext.Msg.alert('알림', '코드가 등록될 코드유형을 선택해주세요.');
						return;
					}
					var selected_code = Ext.getCmp('codeList').getSelectionModel().getSelected();
					if(Ext.isEmpty(selected_code)){
						Ext.Msg.alert('알림', '수정할 코드를 선택해주세요.');
						return;
					}

					registCode('update', selected_code.get('id'), selected_codeType.get('id'));
				}
			},'-',{
				text: '삭제',
				icon: '/led-icons/application_delete.png',
				handler: function(btn){
					var selected_code = Ext.getCmp('codeList').getSelectionModel().getSelected();
					if(Ext.isEmpty(selected_code)){
						Ext.Msg.alert('알림', '삭제할 코드를 선택해주세요.');
						return;
					}

					Ext.Msg.show({
						title: '알림',
						msg: selected_code.get('code_nm')+' 코드를 삭제하시겠습니까?',
						buttons: Ext.Msg.OKCANCEL,
						fn: function(btn){
							if(btn == 'ok'){
								requestCode('remove', selected_code.data, '');
							}
						}
					});

				}
			}],
			listeners: {
				rowdblclick: function(grid, rowIndex, e){
					if(Ext.isEmpty(rowIndex)) return;
					var selected_codeType = Ext.getCmp('codeTypeList').getSelectionModel().getSelected();
					if(Ext.isEmpty(selected_codeType)){
						Ext.Msg.alert('알림', '수정할 코드의 코드유형을 선택해주세요.');
						return;
					}
					var select_row = grid.getStore().getAt(rowIndex);

					registCode('update', select_row.get('id'), selected_codeType.get('id'));
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
									var selected_codeType = Ext.getCmp('codeTypeList').getSelectionModel().getSelected();
									if(Ext.isEmpty(selected_codeType)){
										Ext.Msg.alert('알림', '수정할 코드의 코드유형을 선택해주세요.');
										return;
									}

									registCode('update', sm.getSelected().get('id'), selected_codeType.get('id'));
								}else{
									Ext.Msg.alert('알림', '수정할 코드를 선택해주세요.');
								}
							}
						},{
							text : '삭제',
							icon: '/led-icons/application_delete.png',
							handler: function(b, e){
								var sm = self.getSelectionModel();
								if(sm.hasSelection()){
									requestCode('remove', sm.getSelected().data, '');
								}else{
									Ext.Msg.alert('알림', '삭제할 코드타입을 선택해주세요.');
								}
							}
						}]
					});
					menu.showAt(e.getXY());
				}
			},
			store: new Ext.data.JsonStore({
				url: '/store/code/getCode.php',
				remoteSort: true,
				idProperty: 'id',
				root: 'data',
				fields: [
					'id',
					'code',
					'code_nm',
					'sort',
					{name: 'create_date', type: 'date', dateFormat:'YmdHis'},
					'create_user'
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
			colModel: new Ext.grid.ColumnModel({
				columns: [
					new Ext.grid.RowNumberer(),
					{header: 'ID', dataIndex: 'id', width: 50, hidden: true},
					{header: '코드', dataIndex: 'code', width: 100},
					{header: '코드 명', dataIndex: 'code_nm', width: 150},
					{header: '등록자', dataIndex: 'create_user', width: 80},
					{header: '등록일', dataIndex: 'create_date', width: 150, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
				]
			}),
			viewConfig: {
				forseFit: true
			}
		}]
	};

	function registCodeType(action, id){
		var title = '';
		var buttnText = '';
		var codeType_data = '';

		if(action == 'regist'){
			title = '코드유형 등록';
			buttnText = '추가';
		}
		else if(action == 'update'){
			codeType_data = Ext.getCmp('codeTypeList').getStore().getById(id);
			title = '코드유형 수정';
			buttnText = '수정';
		}

		var registCodeType_form = new Ext.Window({
			title: title,
			width: 300,
			height: 150,
			modal: true,
			layout: 'fit',
			items: {
				id: 'code_type_regist_form',
				xtype: 'form',
				frame: true,
				items:[{
					xtype: 'textfield',
					hidden: true,
					name: 'id',
				},{
					xtype: 'textfield',
					width: 162,
					name: 'code',
					fieldLabel: '코드유형',
					allowBlank: false
				},{
					xtype: 'textfield',
					width: 162,
					name: 'code_nm',
					fieldLabel: '코드유형 명',
					allowBlank: false
				}],
				listeners: {
					afterrender: function(self){
						if(!Ext.isEmpty(codeType_data)) self.getForm().setValues(codeType_data.data);
					}
				}
			},
			buttons: [{
				text: buttnText,
				handler: function(btn){
					var code_type_regist_form = Ext.getCmp('code_type_regist_form').getForm();
					
					if(!code_type_regist_form.isValid()){
						Ext.Msg.alert('알림', '필수 입력 항목을 입력해주세요.');
						return;
					}
					
					requestCodeType(action, code_type_regist_form.getValues());
				}
			},{
				text: '취소',
				handler: function(btn){
					btn.ownerCt.ownerCt.close();
				}
			}]
		}).show();
	}

	function requestCodeType(flag, data){

		Ext.Ajax.request({
			url: '/store/code/setCode.php',
			params: {
				action: 'codeType_'+flag,
				id: data.id,
				code: data.code,
				code_nm: data.code_nm
			},
			callback: function(opts, success, response){
				if(success){
					try{
						var r = Ext.decode(response.responseText);
						if(r.success){							
							if(flag == 'regist' || flag == 'update'){
								Ext.getCmp('codeTypeList').getStore().load();
								Ext.getCmp('code_type_regist_form').ownerCt.close();
							}
							else if(flag == 'remove'){
								Ext.getCmp('codeTypeList').getStore().load();
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

	function registCode(action, id, code_type_id){
		var title = '';
		var buttnText = '';
		var code_data = '';

		if(action == 'regist'){
			title = '코드 등록';
			buttnText = '추가';
		}
		else if(action == 'update'){
			code_data = Ext.getCmp('codeList').getStore().getById(id);
			title = '코드 수정';
			buttnText = '수정';
		}

		var registCode_form = new Ext.Window({
			id: 'code_add_win',
			title: title,
			width: 300,
			height: 155,
			modal: true,
			layout: 'fit',
			items: {
				id: 'registCode_form',
				xtype: 'form',
				frame: true,
				items:[{
					xtype: 'textfield',
					hidden: true,
					name: 'id',
				},{
					xtype: 'textfield',
					width: 162,
					name: 'code',
					fieldLabel: '코드',
					allowBlank: false
				},{
					xtype: 'textfield',
					width: 162,
					name: 'code_nm',
					fieldLabel: '코드 명',
					allowBlank: false
				}],
				listeners: {
					afterrender: function(self){
						if(!Ext.isEmpty(code_data)) self.getForm().setValues(code_data.data);
					}
				}
			},
			buttons: [{
				text: buttnText,
				handler: function(btn){				
					var code_regist_form = Ext.getCmp('registCode_form').getForm();
					
					if(!code_regist_form.isValid()){
						Ext.Msg.alert('알림', '필수 입력 항목을 입력해주세요.');
						return;
					}

					requestCode(action, code_regist_form.getValues(), code_type_id);
				}
			},{
				text: '취소',
				handler: function(btn){
					btn.ownerCt.ownerCt.close();
				}
			}]
		}).show();
	}

	function requestCode(flag, data, code_type_id){

		Ext.Ajax.request({
			url: '/store/code/setCode.php',
			params: {
				action: 'code_'+flag,
				id: data.id,
				code: data.code,
				code_nm: data.code_nm,
				code_type_id: code_type_id
			},
			callback: function(opts, success, response){
				if(success){
					try{
						var r = Ext.decode(response.responseText);
						if(r.success){
							
							Ext.getCmp('codeList').getStore().reload();

							if(flag == 'regist' || flag == 'update'){
								Ext.getCmp('registCode_form').ownerCt.close();
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

	return getCodeManagementPanel;
})()