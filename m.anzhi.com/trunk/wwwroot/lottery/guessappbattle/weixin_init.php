<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

$actsid = get_key("guessappbattle:{$aid}");
$tplObj->out['actsid'] = $actsid;

// 分享
$tplObj->out['share_url'] = SHARE_PROMOTION_HOST . '/lottery/guessappbattle/weixin_index.php';
$tplObj->out['share_title'] = '史上最难的“我画你猜”，参与送iPhone6S';
$tplObj->out['share_icon'] = $configs['new_static_url'] . '/activity/guessappbattle/images/share.jpg';

// 获得微信授权
include(dirname(realpath(__FILE__)).'/../public/WeixinShareAuth.class.php');
$wx_share_auth = new WeixinShareAuth();
$wx_share_config = $wx_share_auth->get_config();
$tplObj->out['wx_share_config'] = json_encode($wx_share_config);