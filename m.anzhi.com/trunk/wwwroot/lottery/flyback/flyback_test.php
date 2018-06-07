<?php
//奖品缓存
include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once 'flyback_fun.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}


//奖品个数
for($i=1;$i<8;$i++){
	var_dump($redis -> get('flyback_prize_num_'.$i));
	echo '<br>';
	$redis -> delete('flyback_prize_num_'.$i);
}
$redis -> set('flyback_prize_num_1', 1);
$redis -> set('flyback_prize_num_2', 1);
$redis -> set('flyback_prize_num_3', 4);
$redis -> set('flyback_prize_num_4', 20);
$redis -> set('flyback_prize_num_5', 30);
$redis -> set('flyback_prize_num_6', 50);
$redis -> set('flyback_prize_num_7', 80);


//奖品名称
$redis -> set('flyback_prize_name_1', '800元京东卡');
$redis -> set('flyback_prize_name_2', '500元京东卡');
$redis -> set('flyback_prize_name_3', '300京东卡');
$redis -> set('flyback_prize_name_4', '100元充值卡');
$redis -> set('flyback_prize_name_5', '100安智币');
$redis -> set('flyback_prize_name_6', '50安智币');
$redis -> set('flyback_prize_name_7', '30安智币');

//礼包个数
$redis -> set('flyback_gift_num', '1000');
for($i=1;$i<8;$i++){
	var_dump($redis -> get('flyback_prize_num_'.$i));
	echo '<br>';
}

