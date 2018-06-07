<?php
function getExtraConfig() {
	$data = array();
	$cache_data = array();
	$model = new GoModel();
	$memcached = GoCache::getCacheAdapter('memcached');
	$memcached_data = $memcached->gets(array('extent_list_length'));
	$cache_data = array_change_key_case($memcached_data,CASE_UPPER);
	$option = array(
		'table' => 'sj_soft',
		'where' => array(
			'package' => 'cn.goapk.market',
			'status' => 1,
			'hide' => 1,
		),
		'order' => 'softid desc',
		'field' => 'softid',
	);
	$market = $model->findOne($option);
	if (!empty($market)) {
		$option = array(
			'table' => 'sj_icon',
			'where' => array(
				'softid' => $market['softid'],
				'status' => 1,
			),
		);
		$icon = $model->findOne($option);
		if (!empty($icon)) {
			$market_icon = $icon['iconurl_125'];
		} 
	}
	if (empty($market_icon)) {
		$option = array(
			'table' => 'sj_soft_file',
			'where' => array(
				'softid' => $market['softid'],
				'package_status' => 1,
			),
		);
		$icon = $model->findOne($option);
		if (!empty($icon)) {
			$market_icon = $icon['iconurl_125'];
		} 
	}
	$data['MARKET_ICON'] = $market_icon;
	$option = array(
		'table' => 'sj_soft',
		'where' => array(
			'status' => 1,
			'package' => 'com.anzhi.fasttransfer',
		),
		'field' => 'softid, last_refresh',
		'order' => 'softid desc',
	);
	$transfer = $model->findOne($option);
	$data['TRANSFER_PLUGIN'] = $transfer['softid'];
	$data['GET_TRANSFER_PLUGIN_TIMESTAMP'] = $transfer['last_refresh'];
	$now = time();
	
	$data['GET_BACK_AD_TIMESTAMP'] = getRangeMaxUpdateTime('sj_return_back_ad', 'start_at', 'end_at', 'update_at', array('status' => 1));
	
	$data['GET_POP_BANNER_TIMESTAMP'] = getRangeMaxUpdateTime('sj_animation_ad', 'start_at', 'end_at', 'update_at', array('status' => 1));

	$option = array(
		'table' => 'sj_custom_push',
                'where' => array(
		    'status' => 1,
		),
		'field' => 'max(update_at) as update_at',
	);
	$custom_push = $model->findOne($option);
	$data['CUSTOM_PUSH_TIMESTAMP'] = $custom_push['update_at'];

	$option = array(
		'table' => 'sj_extent_v1',
		'where' => array(
			'status' => 1,
			'location' => array('exp', '!=""'),
		),
		'field' => 'push_area',
	);
	$extent = $model->findAll($option);
	$filter_location = array();
	foreach ($extent as $v) {
		$areas = explode(';', $v['push_area']);
		foreach($areas as $area) {
			$location = explode(',', $area);
			if (!empty($location[0])) {
				$filter_location[$location[0]][$location[1]] = 1;
			}
		}
	}
	$data['FILTER_LOCATION'] = $filter_location;

    $data['DESK_ICON_TIMESTAMP'] = getRangeMaxUpdateTime('sj_desk_icon', 'start_time', 'end_time', 'up_time', array('status' => 1));

    $option = array(
    	'table' => 'sj_market_skin',
    	'field' => 'max(last_refresh) t'
    );
    $res = $model->findOne($option);
    $data['SKIN_TIMESTAMP'] = intval($res['t']);

	if(!empty($cache_data)){
		$data = array_merge($data,$cache_data);
	}

	$option = array(
    	'table' => 'coop_site',
    	'where' => array(
    		'id' => array('exp', '>1'),
    	),
    	'field' => 'id, is_chain_down'
    );
    $res = $model->findAll($option);
    $data['COOP_SITE_CONFIG'] = array();   
    foreach ($res as $row) {
    	$data['COOP_SITE_CONFIG'][] = array($row['id'], $row['is_chain_down']);
    }

	return $data;
}

function getRangeMaxUpdateTime($table, $start_key, $end_key, $update_key, $where = array())
{
	$now = time();
	$model = new GoModel();
	$where[$now] = array('exp', "between {$start_key} and {$end_key}");
	$option = array(
		'table' => $table,
		'where' => $where,
		'field' => "max({$start_key}) as start",
	);
	$res = $model->findOne($option);
	$max_update = 0;
	if ($res) {
		$max_update = intval($res['start']);
	}

    $option = array(
    	'table' => $table,
    	'field' => "max({$update_key}) t"
    );
    $res = $model->findOne($option);
    return max($res['t'], $max_update);	
}
