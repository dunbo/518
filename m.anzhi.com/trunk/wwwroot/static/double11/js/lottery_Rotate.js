
function randomnum(smin, smax) {// 获取2个值之间的随机数
	var Range = smax - smin;
	var Rand = Math.random();
	return (smin + Math.round(Rand * Range));
}
var myreturn = new Object;
function runzp(award) {
	var index = award - 1;
	var angle = 0;
	var angle0 = [ 331, 360];
	var angle1 = [ 31, 60 ];
	var angle2 = [ 211, 240];
	var angle3 = [ 151, 180];
	var angle4 = [ 271, 300];
	var angle5 = [ 61, 90 ];
	var angle6 = [ 121, 150];
	var angle7 = [ 301, 330];
	var angle8 = [ 1, 30];
	var angle9 = [ 181, 210];
	var angle10 = [ 241, 270];
	var angle11 = [ 91, 120 ];
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
	case 6:// 六等奖
		var r6 = randomnum(angle6[0], angle6[1]);
		angle = r6;
		break;
	case 7:// 六等奖
		var r7 = randomnum(angle7[0], angle7[1]);
		angle = r7;
		break;
	case 8:// 六等奖
		var r8 = randomnum(angle8[0], angle8[1]);
		angle = r8;
		break;
	case 9:// 六等奖
		var r9 = randomnum(angle9[0], angle9[1]);
		angle = r9;
		break;
	case 10:// 六等奖
		var r10 = randomnum(angle10[0], angle10[1]);
		angle = r10;
		break;
	case 11:// 六等奖
		var r11 = randomnum(angle11[0], angle11[1]);
		angle = r11;
		break;
	}
	myreturn.angle = angle;
	return myreturn;
}