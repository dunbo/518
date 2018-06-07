
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
	
	var angle0 = [1,60];
	var angle1 = [61,120];
	var angle2 = [121,180];
	var angle3 = [181,240];
	var angle4 = [241,300];
	var angle5 = [301,360];
	
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
	default:// 没有中奖
		var r5 = randomnum(angle5[0], angle5[1]);
		angle = r5;
		break;
	}
	myreturn.angle = angle;
	return myreturn;
}