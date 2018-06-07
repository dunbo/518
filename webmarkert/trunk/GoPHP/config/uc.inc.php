<?php
$config = array();

$config['ucenter']['www'] = array(
	'uri' => 'http://dev.i.anzhi.com/',
	'imguri' => 'http://image.anzhi.com/header',
	'serviceId' => '005',
	'serviceVersion' => '1.0',
	'privatekey' => '5St7rezj27NYSUhBfcG629iv',
	'serviceType' => 1,
);

$config['ucenter']['weixin'] = array(
	'uri' => 'http://dev.i.anzhi.com/',
	'imguri' => 'http://image.anzhi.com/header',
	'serviceId' => '029',
	'serviceVersion' => '1.0',
	'privatekey' => 'Ir85s2I360UBE0OjzPhwwGxn',
	'serviceType' => 1,
);


$config['ucenter']['dev'] = array(
	'uri' => 'http://dev.i.anzhi.com/',
	'imguri' => 'http://image.anzhi.com/header',
	'serviceId' => '006',
	'serviceVersion' => '1.0',
	'privatekey' => 'tV4JK7eRLNSjlmLo49Hax95N',
);

$config['ucenter']['gomarket'] = array(
	'uri' => 'http://dev.i.anzhi.com/',
	'task_uri' => 'http://dev.task.anzhi.com/',
	'task_version' => 'v1',
	'imguri' => 'http://image.anzhi.com/header',
	'serviceId' => '007',
	'client_serviceId' => '014',
	'serviceVersion' => 'V5.3',
	'privatekey' => 'SWbqAzj182isUcUm4CF9h10k',
	'client_privatekey' => 'c01qUofs1y71L6Uzl61xVPj7',
);
$config['ucenter']['market'] = $config['ucenter']['gomarket'];

$config['ucenter']['devupload'] = array(
	'uri' => 'http://dev.i.anzhi.com/',
	'imguri' => 'http://image.anzhi.com/header',
	'serviceId' => '009',
	'serviceVersion' => '1.0',
	'privatekey' => '6me099BnlNuXub06XyPW7uvv',
);

$config['ucenter']['game'] = array(
	'uri' => 'http://dev.i.anzhi.com/',
	'imguri' => 'http://image.anzhi.com/header',
	'serviceId' => '010',
	'serviceVersion' => 'V1.2.1',
	'privatekey' => 'S5Rrp3A5n49NhkPklSz19iQ4',
);

$config['ucenter']['pad'] = array(
	'uri' => 'http://dev.i.anzhi.com/',
	'imguri' => 'http://image.anzhi.com/header',
	'serviceId' => '011',
	'serviceVersion' => '1.0',
	'privatekey' => '9c2RQ3AO3EJMawY71S2Uux4J',
);

$config['ucenter']['portal'] = array(
    'uri' => 'http://dev.i.anzhi.com/',
    'imguri' => 'http://image.anzhi.com/header',
    'serviceId' => '015',
    'serviceVersion' => '1.0',
    'uc_api' => 'http://dev.i.anzhi.com/api/account/cookie',
    'privatekey' => '7wIbLD1d1f4ec9QWgWoSkEKc',
	'serviceType' => 1,
);

$config['ucenter']['activity'] = array(
    'uri' => 'http://dev.i.anzhi.com/',
    'imguri' => 'http://image.anzhi.com/header',
    'serviceId' => '034',
    'serviceVersion' => '1.0',
    'uc_api' => 'http://dev.i.anzhi.com/api/account/cookie',
    'privatekey' => '4YlKFH8jAc8H8rj94k0CyVAt',
	'serviceType' => 1,
);

return $config;


