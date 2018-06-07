<?php

/*
** 初始页
*/
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = load_model('softlist');
$img_host = getImageHost();

// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];

// 分享相关
if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
	define('SHARE_PROMOTION_HOST', 'http://test.m.anzhi.com');
	define('SHARE_M_HOST', 'http://test.m.anzhi.com');
} else {
	define('SHARE_PROMOTION_HOST', 'http://fx.anzhi.com');
	define('SHARE_M_HOST', 'http://m.anzhi.com');
}
$tplObj->out['share_url'] = SHARE_M_HOST . '/lottery/defendwar/play.php';
$tplObj->out['share_title'] = '过年保卫战';
$tplObj->out['share_desc'] = '不提分数，咱们还能做亲戚！过年回家亲戚盘问太闹心，看我逆天应对！';

// 获得微信授权
include(dirname(realpath(__FILE__)).'/get_sign.php');

$js_str = 'var wx_share_config = '.json_encode($wx_share_config);
exit($js_str);