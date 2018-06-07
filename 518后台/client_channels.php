<?php

$pdo1 = new PDO("mysql:host=192.168.0.99;dbname=channel_cooperation", 'root', 'southpark');
$pdo2 = new PDO("mysql:host=192.168.0.99;dbname=newgomarket", 'root', 'southpark');
$sql1 = 'select id,client_name from `co_client_list` where status != 0 order by id desc';
$client_results = $pdo1->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
foreach($client_results as $key => $val){
	$sql2 = "select cid from `co_client_channel` where client_id={$val['id']}";
	$channel_ids = $pdo1->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
	$channels = array();
	if($channel_ids){
		foreach($channel_ids as $k => $v){
			$sql3 = "select chname,price,price_type,settle_attr from `sj_channel` where cid={$v['cid']}";
			$channel_result = $pdo2->query($sql3)->fetch(PDO::FETCH_ASSOC);
			if($channel_result['price_type'] == '1'){
				$sql4 = "select name from `co_scheme` where id={$channel_result['price']}";
				$scheme_result = $pdo1->query($sql4)->fetch(PDO::FETCH_ASSOC);
				$channel_result['price'] = $scheme_result['name'];
			}
			$channels[] = $channel_result;
		}
	}
	$val['channels'] = $channels;
	$client_results[$key] = $val;
}

include('./client_channels.html');