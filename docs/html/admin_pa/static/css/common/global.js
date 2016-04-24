require.async(['common:jquery','common:dialog2','common:tips'],function($,Dialog,Tips){
	var modifiyPasswordDialog = new Dialog({
		mask: false,					//蒙版
		autoOpen: false,
		title:'修改密码',
		dom:$('#J_password_modify_window')
	});

	var login = {
		init:function(){
			this.bindEvent();
		},
		confirmButton : $('#J_btn_confirm_passoord'),//确定按钮

		bindEvent:function(){
			$('#J_modify_password').click(function(){
				modifiyPasswordDialog.open();
				$('#J_logined_user_name').text(($('#J_login_user_name').text()));
				$('#J_old_password').focus();
			});

			this.confirmButton.click(function(){
				var data = $('#J_form_modify_password').serialize();

				Tips.loading('请求中请稍候...');
				$.ajax({
			        url:pageConfig.passwordModifyUrl,
			        type: "POST",
			        dataType: 'JSON',
			        data:data,
			        success: function(data) {
			        	Tips.destroy();
			        	if(data.code == 0){
			               var sreturUrl = data.data.sReturnURL;
			               Tips.success('密码修改成功! 请重新登录');
			               setTimeout(function(){
			               		window.location.href = sreturUrl;
			               },1200);
			            }else{
			              alert(data.msg);
			            }
			        },
			        error: function(){
			        		Tips.error('操作失败');
			        }
		        });
			});


		    $('#J_confirm_password').keydown(function(event){
		    	if(event.keyCode == 13){
		    		login.confirmButton.click();
		    	}
		    });

		}

	}

	login.init();
});