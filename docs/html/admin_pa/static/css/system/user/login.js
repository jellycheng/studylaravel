require.async(['common:jquery','common:formValid','common:placeholder'],function($,FormValid,PlaceHolder){
	$('input[name="sAccount"]').focus();

	new PlaceHolder({
		dom:$('#J_s_account'),
		text:'UM帐号'
	});

	new PlaceHolder({
		dom:$('#J_s_passwork'),
		text:'密码'
	});

	var loginFormValidateRule = {

		'sAccount':{
			//支持6到30个字，包括数字、字母、中文
			rule: function(str){
				var str = $.trim(str);
				var  l = str.length;
				var reg = /^[a-z]+[0-9]{0,4}$/i;
				var ruleFlag = reg.test(str);

				return (l >= 30 || l < 6 || !ruleFlag) ? false : true;
			},
			errorText: '6~30位字符包含数字和字母'
		},

		'sPassword':{
			//支持6到30个字，包括数字、字母、中文
			rule: function(str){
				var str = $.trim(str);
				var  l = str.length;
				return (l >= 30 || l < 6 ) ? false : true;
			},
			errorText: '密码：6~30位字符'
		}

	}

	var loginForm = $('#J_loginForm');

	var loginFormValidate = new FormValid({
		dom:loginForm,
		rules:loginFormValidateRule
	});

	$('#J_login_button').click(function(){
		$('#J_server_login_error').remove();
		if(loginFormValidate.check()){
			loginForm.submit();
		}
	});

	$('#J_loginForm input').keydown(function(event){
		if(event.keyCode == 13){
			if($(this).attr('name') == 'sAccount'){
				$('input[name="sPassword"]').focus();
			}else{
				$('#J_login_button').click();
			}
		}
	});

	function setLoginFormPosition(){
		var wheight =  $(window).height();
		
		var blackHeight = wheight - 186;
		var remainh = blackHeight - 330;
		var top = 0;
		if(remainh > 0 ){
			top = remainh/2;
			$('#J_login_footer').show();
		}else{
			top = 2;
			$('#J_login_footer').hide();
		}
		/*$('#J_login_content').css('top',top+'px');*/
	};

	$(window).resize(function(event){
		setLoginFormPosition();
	});
	setLoginFormPosition();

});