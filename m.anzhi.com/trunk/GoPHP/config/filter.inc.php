<?php
$config = array();
$config['philips_channel'] = array(
	161 => 1,
	162 => 1,
	374 => 1,
	387 => 1,
	433 => 1,
	764 => 1,
	802 => 1,
	982 => 1,
	987 => 1,
	1588 => 1,
	1923 => 1,
	1932 => 1,
	1933 => 1,
	1979 => 1,
	2035 => 1,
	2053 => 1,
	2652 => 1,
	2673 => 1,
	2684 => 1,
);
//飞利浦渠道屏蔽电子书，奇虎软件
$config['philips_filter_key'] = array(
	'SOFTLIST_CATEGORY_SOFTID_F_3_NEW',
	'SOFTLIST_CATEGORY_SOFTID_F_55_NEW',
	'DEV_SOFT:214170',
	'DEV_SOFT:6589057',
	'DEV_SOFT:5264673',
);

$config['special_channel_filter'] = array(
	'2431' => array(
		'SOFTLIST_CATEGORY_SOFTID_F_3_NEW',
		'SOFTLIST_CATEGORY_SOFTID_F_55_NEW',
	)
);

return $config;