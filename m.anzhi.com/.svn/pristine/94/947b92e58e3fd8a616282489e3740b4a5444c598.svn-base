function randomnum(smin, smax) {// 获取2个值之间的随机数
	var Range = smax - smin;
	var Rand = Math.random();
	return (smin + Math.round(Rand * Range));
}
var myreturn = new Object;
function runzp(award) {
	var index = award - 1;
	var angle = 0;
	var angle0 = [10,40];
	var angle1 = [55,80];
	var angle2 = [100,130];
	var angle3 = [145,170];
	var angle4 = [190,215];
	var angle5 = [235,260];
	var angle6 = [280,305];
	var angle7 = [335,350];
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
	case 6:// 七等奖
		var r6 = randomnum(angle6[0], angle6[1]);
		angle = r6;
		break;
	case 7:// 八等奖
		var r7 = randomnum(angle7[0], angle7[1]);
		angle = r7;
		break;
	}
	myreturn.angle = angle;
	return myreturn;
}