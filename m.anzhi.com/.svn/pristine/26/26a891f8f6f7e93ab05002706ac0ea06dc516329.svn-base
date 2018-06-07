function login(login_url,version_code){
	if(version_code >= 5700 ){
		window.AnzhiActivitys.login();
		javascript:window.history.forward(1); 
		//logout();
	}else{
		location.href=login_url;
	}
}

//弹窗
function pop_tips(title,notice,box_id,is_center){
	$("#title"+box_id).html(title);	
	$("#notice"+box_id).html(notice);
	showOpenBox('#tip-box'+box_id,is_center);					
	return false;	
}

var flags={canMove:true};
function showOpenBox(obj,is_center){
	$('input').blur();
	setTimeout(function(){
		if(is_center == 1){
			var bg_h=$(window).height() + $(document).scrollTop(),
				top_h= $(obj).height()/ 2 - $(document).scrollTop();
			$(obj).css("margin-top",-top_h+"px").show();;
			$('#body-bg').css("height",bg_h+"px").show();
			flags.canMove=false;
			window.onresize = function(){
				var bg_h=$(window).height() + $(document).scrollTop(),
					top_h= $(obj).height()/ 2 - $(document).scrollTop();
				$('#body-bg').css("height",bg_h+"px");
				$(obj).css("margin-top",-top_h+"px");	
			}		
		}else{
			var bg_h=$(window).height()+$(document).scrollTop(),
				top_h= $(document).scrollTop();
			$(obj).css("top",top_h+"px").show();
			$('#body-bg').css("height",bg_h+"px").show();
			flags.canMove=false;
			window.onresize = function(){
			var bg_h=$(window).height()+$(document).scrollTop();
				$('#body-bg').css("height",bg_h+"px");
			}
		}
		
	},200)
	
}
function cloBox(obj,is_reload){
	$(obj).hide();
	 $('#body-bg').hide();
	flags.canMove=true;
	if(is_reload == 1){
		//location.assign(location) ;
		window.location.reload();//加载页面
	}
}

//优化返回按钮
$(function(){
    $("body").bind('touchmove', function (e) {
        if(!flags.canMove){
            e.preventDefault();
        }
    });
})

//保存手机号 (只有一个手机号表单的情况下使用)
function set_telephone(aid,sid){
		var telephone = $.trim($("#telephone_input").val());
		if (telephone == '') {
			$('#info').html('请输入手机号');
			return false;
		}
		var reg = /^1[34578][0-9]{9}$/
		if (!reg.test(telephone)) {
			$('#info').html('请输入正确的手机号');
			return false;
		}
		$('#info').html('');
		$.ajax({
			url:'/lottery/commentreply/set_telephone.php?sid='+sid+'&aid='+aid,
			data:'telephone='+telephone,
			type:'post',
			dataType:'json',
			success:function(data) {
				var status = data.status;
				var msg = data.msg;
				$('#info').html(msg);
			},
			error:function(){
				$('#info').html('提交失败');
			}
		});
}

function randomnum(smin, smax) {// 获取2个值之间的随机数
	var Range = smax - smin;
	var Rand = Math.random();
	return (smin + Math.round(Rand * Range));
}


//大转盘
function runzp_new(num,prize_num){
    var per_angle = 360/prize_num;
        var angle_num_start=(num-1)*per_angle+3;
        var angle_num_end=(num)*per_angle-3;
        var angle_num = randomnum(angle_num_start,angle_num_end);
        $(".rotate-pointer").rotate({//针转
            duration:2000,//转动时间
            angle: 0,//初始角度
            animateTo:(1800+angle_num),//结束角度 1440=360*4(圈)
            easing: $.easing.easeOutSine, //定义运动的效果，需要引用jquery.easing.min.js的文件
            callback: function(){//回调函数
            }
        });
}

//大转盘
var port_arr = [12345, 23456, 34567, 45678, 56789, 612345];
var listen_failed_max = port_arr.length;
var listen_failed_count = 0;
var market_installed = false;

function share_download() {
        market_installed = false;
        listen_failed_count = 0;
        document.getElementById("az_spirit").innerHTML = "";
        if (!port_arr) {
                return;
        }
        var arr_len = port_arr.length;
        for (var i = 0; i < arr_len; i++) {
                add_async_download_listen_script(port_arr[i]);
        }
        return;
}

function add_async_download_listen_script(port) {
        var m = document.createElement("script");
        m.type = 'text/javascript';
        var url = 'http://127.0.0.1:' + port + '/query?type=action&id='+aid+'&callback=change_install_status&r=' + Math.floor(Math.random() * ( 1000000000 + 1));
        m.src = url;
        m.async = "async";
        m.onload = function() {
                listen_failed_count++;
    if (listen_failed_count >= listen_failed_max) {
        window.location.href= 'http://m.anzhi.com/info_1979088.html';
    }
        };
        m.onerror = function() {
                listen_failed_count++;
                if (listen_failed_count >= listen_failed_max) {
                         window.location.href= 'http://m.anzhi.com/info_1979088.html';
                }
        };
        document.getElementById("az_spirit").appendChild(m);
}

function change_install_status() {
        market_installed = true;
}

//九宫格
var stopNum = '',index = 1,prevIndex = 0,Speed = 300,arr = GetSide(3, 3),EndIndex = 0,cycle = 0,EndCycle = 0,flag = false,quick = 0;


function GetSide(m, n) {
	var arr = [];
	for (var i = 0; i < m; i++) {
		arr.push([]);
		for (var j = 0; j < n; j++) {
			arr[i][j] = i * n + j;
		}
	}

	var resultArr = [];
	var tempX = 0,tempY = 0,direction = "Along",count = 0;
	while (tempX >= 0 && tempX < n && tempY >= 0 && tempY < m && count < m * n) {
		count++;
		resultArr.push([tempY, tempX]);
		if (direction == "Along") {
			if (tempX == n - 1)
				tempY++;
			else
				tempX++;
			if (tempX == n - 1 && tempY == m - 1)
				direction = "Inverse"
		}
		else {
			if (tempX == 0)
				tempY--;
			else
				tempX--;
			if (tempX == 0 && tempY == 0)
				break;
		}
	}
	return resultArr;
}

function cirle_go(my_notice,my_award,my_award_level,my_prize,gift_num,pkgname){
	var stopNum = my_award+1;//点击产生随机数，最后将停在次数上
	document.cookie="stopNum="+stopNum;
	cycle = 0;
	flag = false;
	EndIndex = Math.floor(Math.random() * 8);
	EndCycle = 1;
	Time = setInterval(function (){Star(my_notice,my_award,my_award_level,my_prize,gift_num,pkgname)}, Speed);
}

function Star(num) 
{
	gameTable.rows[arr[index][0]].cells[arr[index][1]].style.border = "";
	gameTable.rows[arr[index][0]].cells[arr[index][1]].getElementsByTagName('span')[0].style.background = "url("+cj_light_url+") no-repeat 0 0";
	gameTable.rows[arr[index][0]].cells[arr[index][1]].getElementsByTagName('span')[0].style.backgroundSize = "100% 100%"
	//1px solid pink
	//"pink";
	if (index > 0) {
		prevIndex = index - 1;
	}
	else {
		prevIndex = arr.length - 1;
	}
	gameTable.rows[arr[prevIndex][0]].cells[arr[prevIndex][1]].style.border = "";
	gameTable.rows[arr[prevIndex][0]].cells[arr[prevIndex][1]].getElementsByTagName('span')[0].style.background = "";		
	//1px solid #ffdd99
	//#fff9ed
	index++;
	quick++;

	if (index >= arr.length) {
		index = 0;
		cycle++;
	}

	//跑马灯变速
	if (flag == false) {
		//走五格开始加速
		if (quick == 5) {
			clearInterval(Time);
			Speed = 100;
			Time = setInterval(Star, Speed);
		}
		//跑N圈减速
		if (cycle == EndCycle + 3 && index == EndIndex) {
			clearInterval(Time);
			Speed = 300;
			flag = true;        //触发结束
			Time = setInterval(Star, Speed);
		}
	}
	var stopNums = getCookie('stopNum');
	if (flag == true && index == stopNums - 1) {
		
		quick = 0;
		clearInterval(Time);
		$("#gameContent p").removeClass("waitGame");
		$("#gameBtn").show(); //停止后显示开始按钮
	}

}

function getCookie(name){
	var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
	if(arr=document.cookie.match(reg))
		return unescape(arr[2]);
	else
		return null;
}
