<?php
	include_once (dirname(realpath(__FILE__)).'/init.php');
	
	if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
		session_id($_GET['sid']);
	}
	session_start();

	$redis = new GoRedisCacheAdapter();
	$model = new GoModel();

        if($_POST)
        {
            $id = $_POST['gid'];
            $data = array(
                    'is_gua' => '1',
                    'gua_time' => time(),
                    'update_tm' => time(),
                    '__user_table' => 'cup_guess'
            );
            $where = array(
                    'id' => $id
            );
            echo $model->update($where, $data,'world_cup');
            exit(0);
        }
	//if($_SESSION['USER_IMSI']){
		$imsi = $_SESSION['USER_IMSI'];
	//}else{
	//	$imsi = 1;
	//}
	$option = array(
		'where' => array(
			'A.imsi' => $imsi,
			'A.guess_result' => 1,
			'A.is_gua' => 1
		),
		'table' => 'cup_guess AS A',
		'join' => array(
			'cup_match AS B' => array(
				'on' => array('A.match_id', 'B.id')
			),
		),
                'field' => 'A.id as g_id,A.*,B.*',
		'order' => 'B.begintime DESC'
	);
	$result = $model -> findAll($option,'world_cup');
	$level_option = array(
		'where' => array(
			'config_type' => 'WORLD_CUP_LEVEL',
			'status' => 1
		),
		'table' => 'pu_config'
	);
	$level_result = $model -> findOne($level_option);
	$the_level = json_decode($level_result['configcontent'],true);
	
	foreach($result as $key => $val){
		$val['level_money'] = $the_level[$val['award_level']];
		$result[$key] = $val;
                $result[$key]['begintime'] = date('Y-m-d',$val['begintime']);
	}


	$option = array(
		'where' => array(
			'A.imsi' => $imsi,
			'A.guess_result' => 1,
			'A.is_gua' => 0,
			'A.award_status' => array('exp','!=0'),
		),
		'table' => 'cup_guess AS A',
		'join' => array(
			'cup_match AS B' => array(
				'on' => array('A.match_id', 'B.id')
			),
		),
                'field' => 'A.id as g_id,A.*,B.*',
		'order' => 'B.begintime DESC'
	);
	$result_nogua = $model -> findAll($option,'world_cup');
        foreach($result_nogua as $key => $val){
                $result_nogua[$key]['begintime'] = date('Y-m-d',$val['begintime']);
	}
	
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['result_nogua'] = $result_nogua[0];
	$tplObj -> out['count'] = count($result_nogua);
	$tplObj -> out['result'] = $result;
	$tplObj -> display('award_log.html');
