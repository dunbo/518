function getArgs() {
	var args = {};
	var query = location.search.substring(1);
	var pairs = query.split("&");

	for(var i = 0; i < pairs.length; i++) {
		var pos = pairs[i].indexOf('=');
		if (pos == -1) continue;
		var argname = pairs[i].substring(0,pos);
		var value = pairs[i].substring(pos+1);
		value = decodeURIComponent(value);
		args[argname] = value;
	}
	return args;
}

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



function getCookie(name)
{
 var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
 if(arr=document.cookie.match(reg))
  return unescape(arr[2]);
 else
  return null;
} 


function my_notice(){
	var notice_no = new Array();
	notice_no = [['运气跑偏了，下次一定要中！'],['大奖擦肩而过，快抓住它~'],['奖品很淘气，又溜掉了~'],['说好的大奖呢，再来一次~'],['大奖就在附近，加油哦~'],['勤劳的孩子有糖吃，继续努力~'],['菩萨保佑，下次就中~'],['我就不信了，还能总不中？'],['上帝刚睡醒了，再给一次机会~'],['手指上有灰，吹吹再抽吧~'],['念段咒语，不信下次不中~'],['没中奖哦，换个姿势吧~']];
	var i = Math.floor(1+Math.random()*11);
	var the_notice = notice_no[i];
	$('#active-tx').html(the_notice);
}

function getArrayItems(arr, num) {
    var temp_array = new Array();
    for (var index in arr) {
        temp_array.push(arr[index]);
    }
    var return_array = new Array();
    for (var i = 0; i<num; i++) {
        if (temp_array.length>0) {
            var arrIndex = Math.floor(Math.random()*temp_array.length);
            return_array[i] = temp_array[arrIndex];
            temp_array.splice(arrIndex, 1);
        } else {
            break;
        }
    }
    return return_array;
}

var soft_lists_go = [];
function my_soft() {
	if (soft_lists_go.length ==0) {
		var json_data = window.AnzhiActivitys.getAllAppList(parseInt(aid));
		var cmd = 'var soft_list=' + json_data;
		eval(cmd);
		var soft_lists = soft_list.DATA;

		for(j=0;j<soft_lists.length;j++){
			var json_data_to = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(soft_lists[j][0]));
			var cmd = 'var soft_status_to=' + json_data_to;
			eval(cmd);
			
			if(soft_status_to == -1){
				if(window.AnzhiActivitys.isInstalledApp(soft_lists[j][7],parseInt(soft_lists[j][13])) != 0 && window.AnzhiActivitys.isInstalledApp(soft_lists[j][7],parseInt(soft_lists[j][13])) != 1 && window.AnzhiActivitys.isInstalledApp(soft_lists[j][7],parseInt(soft_lists[j][13])) != -1){
					soft_lists_go.push(soft_lists[j]);
				}
			}
		}
	}
	
	var data = getArrayItems(soft_lists_go,8);
	var str = '';
	for(i = 0; i < data.length; i++) {
		if(data[i][2].length > 4){
			var softname = data[i][2].substring(0,3)+'...';
		}else{
			var softname = data[i][2];
		}
		window.AnzhiActivitys.registerDownloadObserver(parseInt(data[i][0]));
		window.AnzhiActivitys.registerInstallObserver(data[i][7]);
		var json_datas = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(data[i][0]));
		var cmd = 'var soft_status=' + json_datas;
		eval(cmd);
		if(soft_status == 1){
			var my_soft = '<a id="'+data[i][0]+'" onclick="download_apk('+aid+','+data[i][0]+',\''+data[i][7]+'\',\''+data[i][2]+'\','+data[i][13]+','+data[i][27]+',0,0,'+data[i][27]+');">下载中</a>';
		}else if(soft_status == 2){
			var my_soft = '<a id="'+data[i][0]+'" onclick="download_apk('+aid+','+data[i][0]+',\''+data[i][7]+'\',\''+data[i][2]+'\','+data[i][13]+','+data[i][27]+',0,0,'+data[i][27]+');" >继续</a>';
		}else if(soft_status == 3){
			var my_soft = '<a id="'+data[i][0]+'" onclick="download_apk('+aid+','+data[i][0]+',\''+data[i][7]+'\',\''+data[i][2]+'\','+data[i][13]+','+data[i][27]+',0,0,'+data[i][27]+');" >继续</a>';
		}else if(soft_status == 4){
			var my_soft = '<a id="'+data[i][0]+'" onclick="download_apk(parseInt('+aid+'),'+data[i][0]+',"'+data[i][7]+'","'+data[i][2]+'",parseInt('+data[i][13]+'),"'+data[i][27]+'",0,0,'+data[i][27]+');" >下载</a>';
		}else if(soft_status == 5){
			var my_soft = '<a id="'+data[i][0]+'" onclick="installApp(parseInt('+data[i][0]+'));">安装</a>';
		}else if(soft_status == 6){
			var my_soft = '<a id="'+data[i][0]+'" >已下载</a>';
		}else if(soft_status == 8){
			var my_soft = '<a id="'+data[i][0]+'" onclick="download_apk('+aid+','+data[i][0]+',\''+data[i][7]+'\',\''+data[i][2]+'\','+data[i][13]+','+data[i][27]+',0,0,'+data[i][27]+');" >继续</a>';
		}else if(soft_status == 9){
			var my_soft = '<a value="校验中" id="'+data[i][0]+'" >校验中</a>';
		}else if(soft_status == 10){
			var my_soft = '<a id="'+data[i][0]+'">已下载</a>';
		}else if(soft_status == -1){
			var soft_other_status = window.AnzhiActivitys.isInstalledApp(data[i][7],parseInt(data[i][13]));
			if(soft_other_status == -2){
				var my_soft = '<a id="'+data[i][0]+'" onclick="download_apk('+aid+','+data[i][0]+',\''+data[i][7]+'\',\''+data[i][2]+'\','+data[i][13]+','+data[i][27]+',0,0,'+data[i][27]+');">下载</a>';
			}else if(soft_other_status == -1){
				var my_soft = '<a id="'+data[i][0]+'" onclick="download_apk('+aid+','+data[i][0]+',\''+data[i][7]+'\',\''+data[i][2]+'\','+data[i][13]+','+data[i][27]+',0,0,'+data[i][27]+');">更新</a>';
			}
		}
		str += '<li><dl><dd><img src="'+data[i][1]+'" width="43" height="43" border="0"></dd><dd><p class="name">'+softname+'</p><p class="size">'+data[i][9]+'</p><p class="download_bg">'+my_soft+'</p></dd></dl></li>';
	}
	$('#my_softs').html(str);
}

function installApp(softid){
	window.AnzhiActivitys.installAppForActivity(softid);
}

$(document).ready(function () {
	setTimeout(function(){
		my_soft();
	}, 100);
	$('#gameBtn').click(function(){
		click_lottery();
	});
});

function Star(my_notice) {
	var gameTable = document.getElementById('gameTable');
	gameTable.rows[arr[index][0]].cells[arr[index][1]].style.border = "";
	gameTable.rows[arr[index][0]].cells[arr[index][1]].style.background = "url('/images/cj_light.png') no-repeat scroll 3px 0px transparent";
	gameTable.rows[arr[index][0]].cells[arr[index][1]].style.color = "yellow";	

	if (index > 0) {
		prevIndex = index - 1;
	}
	else {
		prevIndex = arr.length - 1;
	}
	gameTable.rows[arr[prevIndex][0]].cells[arr[prevIndex][1]].style.border = "";
	gameTable.rows[arr[index][0]].cells[arr[index][1]].style.color = "";
	gameTable.rows[arr[prevIndex][0]].cells[arr[prevIndex][1]].style.background = "";		
	index++;
	quick++;

	if (index >= arr.length) {
		index = 0;
		cycle++;
	}

	if (flag == false) {
		if (quick == 5) {
			clearInterval(Time);
			Speed = 50;
			Time = setInterval(function (){Star(my_notice)}, Speed);
		}

		if (cycle == EndCycle + 3 && index == EndIndex) {
			clearInterval(Time);
			Speed = 300;
			flag = true;  
			Time = setInterval(function (){Star(my_notice)}, Speed);
		}
	}
	var stopNums = getCookie('stopNum');
	if (flag == true && index == stopNums - 1) {
		quick = 0;
		clearInterval(Time);
		if(my_notice == 300){
			setTimeout(have_num_one,500);
		}else if(my_notice == 400){
			setTimeout(have_num,500);
		}
		$("#gameContent p").removeClass("waitGame");
		$("#gameBtn").show();
	}
}

function click_lottery(){
	$.ajax({
		url: '/lottery/get_award.php',
		data:'sid='+sid+'&aid='+aid,
		type: 'get',
		success:function(data){
			var data = eval(''+data+'');
			if(data[0] == 1000){
				showOpenNew('view_deta_no');
				$('#have_no').css('display','');
				$('#have_some').css('display','none');
				return false;
			}else if(data[0] == 300){
				$('#my_button').css('display','none');
				$('#have_no').css('display','');
				$('#have_some').css('display','none');
				var my_award = data[1];
				var my_num = data[2];
				$('#my_num').html(my_num);
				var my_notice = 300;
				cirle_go(my_notice,my_award,'','');
			}else if(data[0] == 900){
				$('#my_button').css('display','none');
				$('#have_no').css('display','');
				$('#have_some').css('display','none');
				var my_award_level = data[1];
				var my_prize = data[2];
				var my_award = data[3];
				var my_num = data[4];
				var my_notice = 900;
				$('#my_num').html(my_num);
				cirle_go(my_notice,my_award,my_award_level,my_prize);		
				setTimeout(_have_num_yes(my_award_level,my_prize),6000);				
			}else if(data[0] == 400){
				$('#my_button').css('display','none');
				$('#have_no').css('display','none');
				$('#have_some').css('display','');
				var my_award = data[1];
				var my_num = data[2];
				$('#my_num').html(my_num);
				var my_notice = 400;
				cirle_go(my_notice,my_award,'','');
			}else if(data[0] == 800){
				$('#my_button').css('display','none');
				$('#have_no').css('display','none');
				$('#have_some').css('display','');
				var my_award_level = data[1];
				var my_prize = data[2];
				var my_award = data[3];
				var my_num = data[4];
				var my_notice = 800;
				$('#my_num').html(my_num);
				cirle_go(my_notice,my_award,my_award_level,my_prize);		
				setTimeout(_have_num_yes(my_award_level,my_prize),6000);				
			}
		}
	});
}

function cirle_go(my_notice,my_award,my_award_level,my_prize){
	var stopNum = my_award+1;//点击产生随机数，最后将停在次数上
	document.cookie="stopNum="+stopNum;
	$(this).hide(); //开始后隐藏开始按钮
	$(this).parent().addClass("waitGame");
	cycle = 0;
	flag = false;
	EndIndex = Math.floor(Math.random() * 8);
	EndCycle = 1;
	Time = setInterval(function (){Star(my_notice)}, Speed);
}

function have_num_one(){
	showOpenNew('view_deta_one');
	$('#my_button').css('display','');
}

function have_num(){
	my_notice();
	showOpenNew('view_deta');
	$('#my_button').css('display','');
}

function have_num_yes(my_award_level,my_prize){
	my_notice();
	$('#gameContent').css('display','none');
	$('#act_part3_award').css('display','none');
	$('#act_part3').css('display','');
	$('#award_level').html(my_award_level);
	$('#prize').html(my_prize);
	$('#my_button').css('display','');
}

function _have_num_yes(my_award_level,my_prize){
	return function (){
		have_num_yes(my_award_level,my_prize);
	}
}

function pageScroll(){
    window.scrollBy(0,-1000);
    scrolldelay = setTimeout('pageScroll()',1000);
    var sTop=document.documentElement.scrollTop+document.body.scrollTop;
    if(sTop==0) clearTimeout(scrolldelay);
}

function change_soft(){
	$('#my_softs').html('');
	my_soft();
}

//下载软件
function download_apk(activity_id,softid,pkgname,softname,versionCode,size,flag,noflux,firmware,status){

	if(status != 1){
		window.AnzhiActivitys.registerDownloadObserver(parseInt(softid));
	}
	if(skinvc >= 5400){
		window.AnzhiActivitys.downloadForActivity(parseInt(aid),parseInt(softid),pkgname,softname,parseInt(versionCode),size,flag,0,firmware);
	}else{
		window.AnzhiActivitys.downloadForActivity(parseInt(aid),parseInt(softid),pkgname,softname,parseInt(versionCode),size,flag);
	}
}

//更新软件状态（正在下载）
function onDownloadCreated(softid){
	$('#'+softid+'').html("下载中");
}

function onDownloadStateChanged(softid,newState){
	if(newState == 1){
		$('#'+softid+'').html("下载中");
	}else if(newState == 2){
		$('#'+softid+'').html("继续");
	}else if(newState == 3){
		$('#'+softid+'').html("继续");
	}else if(newState == 4){
		$('#'+softid+'').html("重试");
	}else if(newState == 5){
		$('#'+softid+'').html("已下载");
		$.ajax({
			url: '/lottery/download.php',
			data: 'softid='+softid+'&sid='+sid,
			type: 'get',
			success: function(data){
				if(data){
					data = eval(''+data+'');
					$('#have_no').css('display','none');
					$('#have_some').css('display','');
					$('#my_num').html(data[0]);
					$('#my_soft').html(data[1]);
					$('#more_num').html(data[2]);
					if(data[1] < 20){
						$('#more_num').html('再下载'+data[2]+'个软件，多奖励'+data[3]+'次抽奖机会');
					}else{
						$('#more_num').html('');
					}
					if(data[1] >= 20){
						$('#more_two').html(2);
					}
				}
			}
		});
	}else if(newState == 6){
		$('#'+softid+'').html("打开");
	}else if(newState == 8){
		$('#'+softid+'').html("继续");
	}else if(newState == 9){
		$('#'+softid+'').html("检查中");
	}
}

function lottery_again(obj){
	closeBtn(obj);
	click_lottery();
}

function get_telphone(){
	var telphone = $('#my_telphone').val();
	if(!telphone || telphone == '手机号'){
		$('#my_error').css('display','');
		$('#the_telphone').html('请输入手机号');
		return false;
	}else{
		$('#my_error').css('display','none');
	}
	$.ajax({
		url: '/lottery/get_telphone.php',
		data: 'telphone='+telphone+'&sid='+sid+'&aid='+aid,
		type: 'get',
		success: function(data){
			if(data){
				data = eval(''+data+'');
				if(data == 500){
					$('#my_error').css('display','');
					$('#the_telphone').html('请输入正确的手机号');
				}else if(data[0] == 200){
					$('#act_part3').css('display','none');
					$('#gameContent').css('display','none');
					$('#act_part3_award').css('display','');
					$('#level_award').html(data[2]);
					$('#telphone_award').html(data[1]);
					$('#prize_award').html(data[3]);
				}
			}
		}
	});
}

function goon_award(){
	$('#my_telphone').val('');
	$('#act_part3_award').css('display','none');
	$('#gameContent').css('display','');
}

function go_down(obj){
	closeBtn(obj);
	window.scrollTo(0,524);
}

function viewWidth(){
	return document.documentElement.clientWidth;
	}
function viewHeight(){ return document.documentElement.clientHeight || document.body.clientHeight;}

function scrollY(){ return document.documentElement.scrollTop || document.body.scrollTop; } 
function scrollHeight(){
	return Math.max(document.documentElement.scrollHeight,document.body.scrollHeight, document.documentElement.clientHeight); 
}