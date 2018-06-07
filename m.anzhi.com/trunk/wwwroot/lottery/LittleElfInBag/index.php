<?php

require_once(dirname(realpath(__FILE__)) . '/init.php');

$lottery_num = get_lottery_num();
$grasp_num = get_grasp_num();

$tplObj->out['lottery_num'] = $lottery_num;
$tplObj->out['grasp_num'] = $grasp_num;

//分享地址
if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
{
	$share_url = 'http://m.test.anzhi.com/a_'.$aid.'.html';
}
elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
{
	$share_url = 'http://9.m.anzhi.com/a_'.$aid.'.html';
}
else 
{
	$share_url = 'http://fx.anzhi.com/a_'.$aid.'.html';
}
$tplObj -> out['share_url'] = $share_url;
$tplObj-> out['share_text'] ="报告！书包里发现小精灵，据说抓到三只就可以抽来@安智市场 抽大奖，快和我一起去抓精灵啦！";

// if ($now > $activity_end_time) {
// 	$tplObj->display('lottery/LittleElfInBag/end.html');
// }
// else
// {
	$tplObj->display('lottery/LittleElfInBag/index.html');
//}
exit;