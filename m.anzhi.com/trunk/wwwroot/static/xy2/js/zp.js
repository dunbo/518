
function randomnum(smin, smax) {// 获取2个值之间的随机数
	var Range = smax - smin;
	var Rand = Math.random();
	return (smin + Math.round(Rand * Range));
}

function runzp() {
    // 奖项json; prize：奖项；v设置概率
    var data = '[{"id":1,"prize":"50积分1","v":10.0},{"id":2,"prize":"50积分2","v":10.0},{"id":3,"prize":"100元充值卡","v":20.0},{"id":4,"prize":"50积分3","v":10.0},{"id":5,"prize":"2000积分","v":10.0},{"id":5,"prize":"去哪代金券","v":10.0}]';
    var obj = eval('(' + data + ')');
	var result = randomnum(1, 100);
	var line = 0;
	var temp = 0;
	var returnobj = "0";
	var index = 0;

	//alert("随机数"+result);
	for ( var i = 0; i < obj.length; i++) {
		var obj2 = obj[i];
		var c = parseFloat(obj2.v);
		temp = temp + c;
		line = 100 - temp;
		if (c != 0) {
			if (result > line && result <= (line + c)) {
				index = i;
				// alert(i+"中奖"+line+"<result"+"<="+(line + c));
				returnobj = obj2;
				break;
			}
		}
	}
	var angle = 0;
	var message = "";
	var myreturn = new Object;
	if (returnobj != "0") {// 有奖
		message = "恭喜中奖了";
		var angle0 = [ 1, 29 ];//
		var angle1 = [ 31, 89 ];
		var angle2 = [ 121, 179];
        var angle3 = [ 181, 209 ];
        var angle4 = [ 211, 269];
        var angle5 = [ 301, 359];
		switch (index) {
		case 0:// 一等奖
			var r0 = randomnum(angle0[0], angle0[1]);
			angle = r0;
			break;
		case 1:// 二等奖
			var r1 = randomnum(angle1[0], angle1[1]);
			angle = r1;
			break;
		case 2:// 三等奖
			var r2 = randomnum(angle2[0], angle2[1]);
			angle = r2;
			break;
        case 3:// 四等奖
            var r3 = randomnum(angle3[0], angle3[1]);
            angle = r3;
            break;
        case 4:// 五等奖
            var r4 = randomnum(angle4[0], angle4[1]);
            angle = r4;
            break;
        case 5:// 六等奖
            var r5 = randomnum(angle5[0], angle5[1]);
            angle = r5;
            break;
		}
		myreturn.prize = returnobj.prize;
	} else {// 没有
		message = "再接再厉";
		var angle7 = [ 90, 120 ];
		var angle8 = [ 270, 300 ];
		var r = randomnum(7, 8);
		var angle;
		switch (r) {
		case 7:
			var r7 = randomnum(angle7[0], angle7[1]);
			angle = r7;
			break;
		case 8:
			var r8 = randomnum(angle8[0], angle8[1]);
			angle = r8;
			break;
		}
		myreturn.prize = "继续努力!";
	}
	myreturn.angle = angle;
	myreturn.message = message;
	return myreturn;
}