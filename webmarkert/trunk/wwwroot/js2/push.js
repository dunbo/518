var oDivCnt = '<div class="openbox2"><div class="open_title"><h6 class="fontcolorgreen">推送'+SOFT_NAME+'到手机</h6><span onclick="closeWin()"></span></div><div class="report_cnt"><div class="open_title2">请先登录<a href="/detail.php?id=996309">手机安智市场</a>，并启用云推送功能</div><div class="cloudpush_img"><img src="images/cloudpush_01.jpg"/></div><div class="open_btn2"><a href="#" class="submit_btn1" id="check_push">3、开启后，点击重试</a></div></div></div>';
	var oDivCnt7 = '<div class="openbox2"><div class="open_title"><h6 class="fontcolorgreen">推送'+SOFT_NAME+'到手机</h6><span onclick="closeWin()"></span></div><div class="report_cnt"><div class="open_title2 font24">推送失败！<br/>请检查网络是否正确链接。</div><div class="open_btn2"><a href="#" class="submit_btn1" onclick="closeWin()">确定(5秒后自动关闭)</a></div></div></div>';
	var oDivCnt1 = '<div class="title"><h2>登录注册</h3><span class="closebtn_login" onclick="closeWin()"></span></div><div class="openbox_white"><form method="post" action=""><div class="register_list"><label for="">用户名</label><input type="text" class="inputtext" id="detail_username" name="user_name"/><span></span></div><div class="register_list"><label for="">密码</label><input type="password" class="inputtext checkinput" id="detail_userpassword" name="user_password"/></div><div class="login_check" id="error_msg"><span></span></div><div class="login_cookies"><input type="checkbox"  id="rmbUser"/><label for="">记住用户名</label></div><div class="login_cookies"><input type="checkbox" id="autoLogin" /><label for="">下次自动登录</label></div><div class="register_btn" id="login_btn" style="border-bottom:1px dashed #ddd; padding-bottom:15px;"><div class="register1" id="login_in">登　录</div><div class="clear"></div></div><p>没有账号？立刻加入安智！</p><div class="register_btn" id="register_btn"><a class="register1" href="javascript:void(0);" onclick="show_register(\'register_dialog\')">注　册</a><div class="clear"></div></div></div>';
	var oDivCnt2 = '<div class="win_title"><b>推送'+SOFT_NAME+'到手机</b></div><div class="win_cnt"><div class="win_warn"><p><img src="'+ICON+'" alt="'+SOFT_NAME+'" /></p> <p style="color:#535353;font-size:16px;">'+SOFT_NAME+'</p><p style="font-size:14px;">该软件较大，请尽量在wifi环境下完成推送<label style=" padding-left:5px;" for="Push_PopState"></label></p><div id="device_select"><h6>请选择机型</h6></div></div><div class="warn_btn"><input type="button" class="btn_ok" id="btn_ok" /><input type="button" class="btn_cancel" id="btn_cancel" onclick="closeWin()" /></div></div><div class="win_close" id="win_close1" onclick="closeWin()"></div>';
	var oDivCnt4 = '<div class="openbox2"><div class="open_title"><h6 class="fontcolorgreen">推送'+SOFT_NAME+'到手机</h6><span onclick="closeWin()"></span></div><div class="report_cnt"><div class="open_title2 font24">您有多台设备同时登陆，请选择要推送的设备</div><ul class="openul1" id="device_select"></ul><div class="open_btn"><a href="#" class="submit_btn1" id="btn_ok">继 续</a><a class="cancel" onclick="closeWin()">取 消</a><div class="clear"></div></div></div></div>';
	var oDivCnt6 = '<div class="openbox2"><div class="open_title"><h6 class="fontcolorgreen">推送'+SOFT_NAME+'到手机</h6><span onclick="closeWin()"></span></div><div class="report_cnt"><div class="open_title2 font24">推送完成！<br/>请到手机版安智市场“管理”---“下载”中查看下载情况。</div><div class="open_btn2"><a href="#" class="submit_btn1" onclick="closeWin()">确定(5秒后自动关闭)</a></div></div></div>';
	var oDivCnt5 = '<div class="openbox2"><div class="open_title"><h6 class="fontcolorgreen">推送'+SOFT_NAME+'到手机</h6><span onclick="closeWin()"></span></div><div class="report_cnt"><div class="open_title2 font24">推送完成！<br/>请到手机版安智市场“管理”---“下载”中查看下载情况。</div><ul class="openul1" id="push_info"></ul><div class="open_btn2"><a href="#" class="submit_btn1" onclick="closeWin()">确定(5秒后自动关闭)</a></div></div></div>';
	var oDivCnt3 = '<div class="openbox2"><div class="open_title"><h6 class="fontcolorgreen">推送'+SOFT_NAME+'到手机</h6><span onclick="closeWin()"></span></div><div class="report_cnt"><div class="open_title2 font24">大小为'+SOFT_SIZE1+'MB<br/>该应用较大，为避免浪费流量，建议在wifi环境下完成推送。</div><div class="tishi_box"><input id="tishi" type="checkbox"/><label for="tishi">不再提示</label></div><div class="open_btn"><a href="#" class="submit_btn1" id="soft_size_select">继 续</a><a class="cancel" onclick="closeWin()">取 消</a><div class="clear">&nbsp;</div></div></div></div>';
	var oDivCnt9 = '<div class="openbox2"><div class="open_title"><h6 class="fontcolorgreen">推送'+SOFT_NAME+'>到手机</h6><span onclick="closeWin()"></span></div><div class="report_cnt"><div class="open_title2 font24">推送失败 ! <br/>该应用与您>的手机不匹配，无法推送安装。</div><div class="open_btn"><a href="#" class="submit_btn1" onclick="closeWin()">确定(5秒关闭)</a><div class="clear">&nbsp;</div></div></div></div>';
function push(softid) {
	var username1 = $.cookie("__gosession");
	var username2 = $.cookie("_AZ_COOKIE_");
	if (username1 == null && username2 == null) {
		<!--{if $out.uc.isnew eq 1}-->
		location.href = "http://i.anzhi.com/web/account/login?serviceId=005&serviceVersion=1.0&serviceType=1&redirecturi=http://www.anzhi.com/soft_'+softid+'.html";
		<!--{else}-->
		//弹登录框
		_showOpen(oDivCnt1);
		<!--{/if}-->
		
		if ($.cookie("rmbUser") == "true") {
			$("#rmbUser").attr("checked", true);
			$("#detail_username").val($.cookie("userName"));
		}
		$("#login_in").click(function() {
			$("#error_msg").empty();
			var login_name = $("#detail_username").val();
			var login_pass = $("#detail_userpassword").val();
			if (login_name == '' || login_pass == '') {
				$("#error_msg").html("请输入正确的用户信息!");
			}
			$.post(
				"login.php", 
				{user_name: login_name,user_password: login_pass,type: 'ajax'},
				function(data) {
					if (data == '{"status":200}') {
						$("#report_register").hide();

						saveUserInfo();
						t();
					} else {
						$("#error_msg").html("登录失败，请确认登录信息是否正确!");
					}
				}
			);
		})
	} else {
		t();
	}
}

function _showOpen(html)
{
	closeWin();
	var oMark = document.createElement('div');
	oMark.id = 'mark2';
	document.body.appendChild(oMark);
	oMark.style.width = viewWidth() + 'px';
	oMark.style.height = documentHeight() + 'px';
	var oDiv = document.createElement('div');
	oDiv.className = 'openbox_wrap';
	oDiv.id = 'openbox';
	oDiv.style.display = 'block';
	document.body.appendChild(oDiv);

	oDiv.innerHTML = html;
	oDiv.style.top = (viewHeight() - oDiv.offsetHeight) / 2 + scrollY() + 'px';
	oDiv.style.left = (viewWidth() - oDiv.offsetWidth) / 2 + 'px';
	window.onresize = window.onscroll = function() {
		oDiv.style.top = (viewHeight() - oDiv.offsetHeight) / 2 + scrollY() + 'px';
		oDiv.style.left = (viewWidth() - oDiv.offsetWidth) / 2 + 'px';
		oMark.style.width = viewWidth() + 'px';
		oMark.style.height = documentHeight() + 'px';
	}
}

function closeWin() {
	$('#openbox').remove();
	$('#mark2').remove();
}
function viewWidth() {
	return document.documentElement.clientWidth;
}
function viewHeight() {
	return document.documentElement.clientHeight;
}
function scrollY() {
	return document.documentElement.scrollTop || document.body.scrollTop;
}
function documentHeight() {
	return Math.max(document.documentElement.scrollHeight || document.body.scrollHeight, document.documentElement.clientHeight);
}

function t()
{
	$.getJSON(
		"/yun_check.php", 
		{softid: softid},
		function(json) {
			if (json.status == 5) {
				//设备不支持
				_showOpen(oDivCnt9);
				return false;
			}

			if (json.status == 0) {
				//手机端未登录
				_showOpen(oDivCnt);
				$('#check_push').click(function(){
					t();
				});
				return;
			}

			soft_data = json.data;
			userName = json.userName;
			if (soft_size >= 5) {
				//大文件处理
				_showOpen(oDivCnt3);
				$('#soft_size_select').click(function(){
					$.cookie("no_show", "true", {
						expires: 7
					});
					push_to_device();
				});
			} else if (soft_size < 5 || $.cookie("no_show") == "true") {
				//小文件，或者关闭提示
				push_to_device();
			}

		}
	);
}
function push_to_device()
{
	if (soft_data) {
		var str = '';
		var i = 0;
		$.each(soft_data,function(k,s){
			str += '<li><h3><label for="openinput1" >'+s[1]+'</label></h3><span><input id="openinput1" value="'+s[0]+'" name="device_check" attr="'+s[1]+'" ats="'+s[2]+'"  type="checkbox" /></span></li>';
			i++;
		});
		var d =  new Date();
		var sec = d.getTime();
		if (i > 1) {
			//多台设备
			_showOpen(oDivCnt4);
			str += '<div class="clear"></div>';
			$("#device_select").append(str);
			$("#push_info").append('<div class="clear"></div>');

			$("#btn_ok").click(function() {
				var arrCheck = $("input[name='device_check']:checked");
				$.each(arrCheck, function() {
					var deviceid = this.value;
					var phone_model = $(this).attr('attr');
					var push_host = $(this).attr('ats');
					$.getJSON(
						push_host + '/push/ts/?callback=?', 
						{DID: deviceid,SID: softid,SEC: sec,PMOD: phone_model,Us: userName},
						function() {}
					);
					_showOpen(oDivCnt5);
					var setime = check_push(deviceid, softid, sec, phone_model);
					setTimeout(setime, 1000);
				});

				if (oDivCnt5) {
					setTimeout("closeWin()", 5000);
					$("#mark").hide();
					setTimeout("refresh()", 1000);
				}
			});
		} else {
			//单台设备
			$.each(soft_data,function(s, v) {
				var deviceid = s;
				var phone_model = v[1];
				var push_host = v[2];
				$.getJSON(
					push_host + '/push/ts/?callback=?', 
					{DID: deviceid, SID: softid, SEC: sec, PMOD: phone_model, Us: userName}
				);
				setTimeout(function(){
					//检测推送状态
					$.getJSON(
						'yun_check_push.php', 
						{DID: deviceid, SID: softid, SEC: sec},
						function(json) {
							if (json.status == 200) {
								_showOpen(oDivCnt6);
								setTimeout("closeWin()", 5000);
								$("#mark").hide();

							} else {
								_showOpen(oDivCnt7);
								setTimeout("closeWin()", 5000);
								$("#mark").hide();
							}
					});
				}, 1000);
			});
		}
	}
}
function refresh() {
	window.location.reload();
}
function check_push(deviceid, softid, sec, phone_model) {
	return function() {
		$.get('yun_check_push.php', {
			DID: deviceid,
			SID: softid,
			SEC: sec
		},
		function(json) {
			if (json == '{"status":200}') {
				$("#push_info").append('<li><h3>' + phone_model + '</h3><span class="cloudpush_suc">推送成功</span></li><div class="clear"></div>');
			} else {
				$("#push_info").append('<li><h3>' + phone_model + '</h3><span class="cloudpush_err">推送失败</span></li><div class="clear"></div>');
			}
		});
	};
}
    //初始化页面时验证是否记住了密码
    //保存用户信息
    function saveUserInfo() {
        if ($("#rmbUser").attr("checked") == true || $("#autoLogin").attr("checked") == true) {
            var userName = $("#detail_username").val();
            var password = $("#detail_userpassword").val();
            if ($("#autoLogin").attr("checked") == true) {
                $.cookie('autoLogin', "true", {
                    expires: 7
                });
            }
            $.cookie("rmbUser", "true", {
                expires: 7
            }); // 存储一个带7天期限的 cookie
            $.cookie("userName", userName, {
                expires: 7
            }); // 存储一个带7天期限的 cookie
        } else {
            if ($("#autoLogin").attr("checked") == false) {
                $.cookie('autoLogin', "false", {
                    expires: -1
                });
            }
            $.cookie("rmbUser", "false", {
                expires: -1
            });
            $.cookie("userName", '', {
                expires: -1
            });
        }
    }