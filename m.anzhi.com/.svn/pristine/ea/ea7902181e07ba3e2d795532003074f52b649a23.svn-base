<?php
	include_once (dirname(realpath(__FILE__)).'/init.php');

	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
		session_id($_GET['sid']);
	}
	session_start();
	include_once (dirname(realpath(__FILE__)).'/init.php');
	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();
	//if($_SESSION['USER_IMSI']){
		$imsi = $_SESSION['USER_IMSI'];
	//}else{
	//	$imsi = 10;
	//}
	
	$option = array(
		'where' => array(
			'A.imsi' => $imsi
		),
		'table' => 'cup_guess AS A',
		'join' => array(
			'cup_match AS B' => array(
				'on' => array('A.match_id', 'B.id'),
			)
		),
		'order' => 'B.begintime DESC'
	);
	$result = $model -> findAll($option,'world_cup');
        foreach($result as $key=>$v)
        {
            $result[$key]['new_time'] = date('Y-m-d',$v['begintime']);
        }
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['result'] = $result;
	$tplObj -> display('guess_log.html');
