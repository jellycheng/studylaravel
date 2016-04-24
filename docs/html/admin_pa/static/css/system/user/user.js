require.async(['common:jquery','common:tips','common:placeholder','common:template','common:dialog','common:formValid','common:page2'],function($,Tips,Placeholder,Template,Dialog,FormValid,Page2){
	
	var page = {
		config : {
			queryUrl : '/base/user/query',
			createUrl : '/base/user/create',
			editUrl : '/base/user/edit',
			checkUmUrl : '/base/user/exist',
			checkTaskUrl : '/base/user/checkTask',
			resetPswordUrl : '/base/user/restPwd',
			userTable : __inline('./user_table.tpl'),
			rules : {
				'sMobile' : {
					rule: /\d{11}/, 
					errorText: "请输入正确的手机号码"
				},
				// 'sEmail' : {
				// 	rule: /^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/, 
				// 	errorText: "请输入正确的邮箱"
				// },
				'iOrgID' : {
					rule: function(value){
						return !!value;
					}, 
					errorText: "请选择组织机构"
				},
				'aRoleID' : {
					rule: function(value){
						return !!value;
					}, 
					errorText: "请选择角色"
				}
			}
		},
		init : function(){
			var _this = this;
			_this.bindEvent();
			_this.initDialog();
			_this.initFormVlid();
			_this.query();
		},
		bindEvent : function(){
			var _this = this;
			$('#J_user_query').click(function(event) {
				_this.query();
			});
			$('#J_user_create').click(function(event) {
				_this.userDialog.setTitle('创建用户');
				$('#UserTemplateWrap').attr('data',0);
				_this.resetDialog();
				_this.userDialog.open();
				$('.sUmAccount').removeAttr("readonly").removeAttr("disabled");
				$('.sName').removeAttr("readonly").removeAttr("disabled");
			});
			$('#userWrap').delegate('.J_user_edit','click',function(event) {
				var editItem = $(this);

				_this.userDialog.setTitle('编辑用户');
				$('#UserTemplateWrap').attr('data',1);
				_this.resetDialog();
				_this.userDialog.open();
				
				$('.iAutoID').val(editItem.attr('data-iAutoID'));
				$('.sUmAccount').val(editItem.attr('data-sUmAccount')).attr("readonly","readonly").attr("disabled","disabled");
				$('.sName').val(editItem.attr('data-sName')).attr("readonly","readonly").attr("disabled","disabled");
				$('.sMobile').val(editItem.attr('data-sMobile'));
				// $('.sEmail').val(editItem.attr('data-sEmail'));
				$('input[name="iOrgID"][value="'+editItem.attr('data-iOrgID')+'"]').prop("checked",true);
				$('.iOrgID').val(editItem.attr('data-sOrgName'));
				$('.orgdata').val(editItem.attr('data-iOrgID'));
				$.each(editItem.attr('data-sRoleIDList').split(","), function(key, value){
					$('input[name=aRoleID][value="'+value+'"]').prop("checked",true);
				});
				$('.a_aRoleID').val(editItem.attr('data-sRoleList'));
				$('.roledata').val(editItem.attr('data-sRoleIDList'));
				$('input[name="iUserStatus"][value="'+editItem.attr('data-iUserStatus')+'"]').prop("checked",true);
				
			});
			$('.a_aRoleID').focus(function() {
				$('.J_org_list').addClass('close');
				$('.J_role_list').removeClass('close');
			});
			$('.iOrgID').focus(function() {
				$('.J_role_list').addClass('close');
				$('.J_org_list').removeClass('close');
			});
			$('.J_role_list').delegate('.J_role_check', 'change', function(event) {
				var $this = $(this),
					aName = [],
					aAutoID = [];

				$('.J_role_list').find(':checked').each(function(index, el) {
					var $el = $(el);
					aName.push($el.attr('data'));
					aAutoID.push($el.val());
				});
				$('.a_aRoleID').val(aName.join(','));
				$('.roledata').val(aAutoID);
			});
			$('.J_org_list').delegate('.J_org_check', 'click', function(event) {
				var $this = $(this);
				if( $this.prop('checked') == true){
					$('.iOrgID').val($this.attr('data'));
					$('.orgdata').val($this.val());
				}
			});
			$('#userWrap').delegate('.J_psword_reset', 'click', function(event) {
				var iUserID = $(this).attr('data-iAutoID');
				_this.resetPsword(iUserID);
			});
		},
		initDialog: function(){
			var _this = this;
			_this.userDialog = new Dialog({
				title : '编辑用户',
				width: 550,
				autoOpen : false,
				dom : $('#UserTemplate,.J_role_list,.J_org_list'),
				buttons : {
					'保存' : function(){
						var status = $('#UserTemplateWrap').attr('data');
						if( status == 0){
							if( _this.fv2.check() ){
								_this.edit(page.config.createUrl, _this.formatData($('.UserTemplate')), '新增成功');
							}
						}else if( status == 1){
							if( _this.fv.check() ){
								_this.edit(page.config.editUrl ,_this.formatData($('.UserTemplate')) , '保存成功');
							}
						}
					},
					'关闭' : {
						'events' : {
							'click' : function(){
								this.close();
							}
						},
						'className' : 'default'
					}
				},
				open : function(){
					var status = $('#UserTemplateWrap').attr('data');

					if( status == 1 ){
						$('.passwd_line').hide();
					}else{
						$('.passwd_line').show();
					}

					$('.sUmAccount').blur(function(event){
						if( status == 0){
							_this.checkUM($(this).val());
						}else{
							return false;
						}
					});

					// $('.stopUse').click(function(event){
					// 	if( status == 1){
					// 		_this.checkTask();
					// 	}
					// });
					
				}
			});
		},
		resetDialog : function(){
			this.fv.reset();
			$('.iAutoID').val('');
			$('.orgdata').val('');
			$('.roledata').val('');
			$('.J_role_list').addClass('close')
				.find(':checked').removeAttr('checked').end()
				.find('input:text').val('');
			$('.J_org_list').addClass('close')
				.find(':checked').removeAttr('checked').end()
				.find('input:text').val('');
			$('#UserTemplate').find('input:text').val('').end()
				.find('input:password').val('').end()
				.find('input:radio')[0].checked = true;
		},
		formatData : function(el){
			return el.serialize();
		},
		initFormVlid : function(){
			var _this = this;
			_this.fv = new FormValid({
				dom : $('.UserTemplate'),
				showSuccessStatus : true,
				rules : page.config.rules/*编辑*/
			});

			_this.config.rules2 = $.extend({
				'sUmAccount' : {
					rule: /[a-zA-Z0-9]{2,20}/, 
					errorText: '请输入正确的UM账号'
				},
				'sName' : {
					rule: /[\u4e00-\u9fa5]{2,8}/, 
					errorText: "请输入正确的姓名"
				}
				// ,
				// 'sPasswd' :{
				// 	rule: function(value){
				// 		return !!value;
				// 	}, 
				// 	errorText: "请输入正确的密码"
				// }
			}, _this.config.rules);

			_this.fv2 = new FormValid({
				dom : $('.UserTemplate'),
				showSuccessStatus : true,
				rules : page.config.rules2/*开通*/
			});
		},
		edit : function(url,opt,msg){
			var _this = this;
			$.post(url,opt, function(data) {
				_this.userDialog.close();
				if(data.code == 0){
					Tips.success(msg);
					window.location.reload();
				}else{
					Tips.error(data.msg);
				}
			});
		},
		query : function(){
			var _this = this;

			var queryData = {
				'sName' : $('#sName').val(),
				'sUmAccount' : $('#sUmAccount').val(),
				'iOrgID' :  $('#iOrgID option:selected').val() ? $('#iOrgID option:selected').val(): 
							($('#pre_iOrgID option:selected').val() ? $('#pre_iOrgID option:selected').val() : 
								($('#pre_pre_iOrgID option:selected').val() ? $('#pre_pre_iOrgID option:selected').val() : 
									$('#pre_pre_pre_iOrgID option:selected').val() 
								)
							),
				'iRoleID' : $('#iRoleID option:selected').val()
			};

			var param = $.extend({
                iPage:1,
                iPageSize:10
            }, queryData);

			var $list = $('#userWrap'),
                $page = $('#popupPaging');
            Tips.loading('数据加载中...',60000)
			$.post(page.config.queryUrl, param , function(data) {
				Tips.destroy();
				if(data.code == 0){
					var resData = data.data;
					
					$('#userWrap').html(Template.parse(_this.config.userTable,{list:resData.list}));
					$page.empty();
	                var pageTotal =  Math.ceil(resData.iTotal / (resData.perPage||10));

	                if( pageTotal > 1 ){
	                    new Page2({
	                        dom: '#popupPaging',
	                        pageTotal: pageTotal,
	                        currentPage: param.iPage || 1,
	                        callback: function (page) {
	                            param.iPage = page;
	                            _this.showAll(param);
	                        }
	                    });
	                }
				}else{
					$('#userWrap').html();
					Tips.error(data.msg);
				}
			});
		},
		showAll:function(param){
			var _this = this;
			Tips.loading('数据加载中...',60000)
			$.post(page.config.queryUrl, param , function(data) {
				Tips.destroy();
				if(data.code == 0){
					$('#userWrap').html(Template.parse(_this.config.userTable,{list:data.data.list}));
				}else{
					$('#userWrap').html();
					Tips.error(data.msg);
				}
			});
        },
		checkUM : function(opt){
			var _this = this;

			if( !opt ){
				_this.fv.error('sUmAccount','请输入UM账号');
				return false;
			}

			$.post(page.config.checkUmUrl,{'sUmAccount':opt}, function(data) {
				if(data.code == 0){
					// $('.sName').val(data.data);
				}else{
					_this.fv.error('sUmAccount',data.msg);
				}
			});
		},
		checkTask : function(){
			var _this = this;
			$.post(page.config.checkTaskUrl,'', function(data) {
				if(data.code == 0){
					_this.editfv.error('iUserStatus','用户下存在待处理事项，请处理完后停用');
				}
			});
		},
		resetPsword : function(opt){
			var _this = this;
			$.post(page.config.resetPswordUrl,'iUserID='+opt, function(data) {
				if(data.code == 0){
					Tips.success('重置密码成功');
				}else{
					Tips.error(data.msg);
				}
			});
		}
	};
	page.init();

});