<?php
//渠道游戏入库
require_once(dirname(__FILE__).'/../init.php');

$ip = getServerIp();
//只在18使用
 if ($ip != '192.168.1.18' && $ip != '192.168.0.99') {
     exit;
 }

ini_set('display_errors', true);
error_reporting(E_ALL);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("channel_game", "save_channel_game");
while ($worker->work());

/*
*$jobs 需传入包名，渠道id
*/
function save_channel_game($jobs){
	$model = new GoModel();
	$http_game = TRUE;  //同步数据开关
	$jobs = $jobs->workload();
	$game = json_decode($jobs,true);
	$package = $game['package'];
	$channel_id = $game['channel_id']; //渠道id
	$need_test = $game['need_test']; //是否需要测试
	$type = $record_type = '';
	$soft_id = $soft_id_pack = $has_soft  = array();
	//判断是否符合入库条件
	$where = array('package'=>$package,'hide'=>1,'status'=>1);
	//var_dump($where);
	$online_soft = $model->findOne(
		array(
			'table'=>'sj_soft',
			'where'=>$where,
			'field'=>'softid,softname,package,version,version_code,update_content',
			'order'=>'softid desc'
		)
	);
	if(!$package){
		writelog('sdkchannel_error.log',$package.'不是上线状态\n');
		return false;
	}else{
		$fen_lei = $model->findOne(array(
			'table'	=> 'sj_soft_whitelist',
			'where'	=> array('package'=>$package,'status'=>1),
			'field'	=> 'fen_lei'
		));
		if(!$fen_lei||$fen_lei['fen_lei']==''){
			$fen_lei = $model->findOne(array(
				'table'	=> 'yx_product',
				'where'	=> array('package'=>$package,'del'=>0),
				'field'	=> 'p_fenlei'
			));	
			if(!$fen_lei||$fen_lei['p_fenlei']==''||$fen_lei['p_fenlei']!='网游'){
				writelog('sdkchannel_error.log',$package.'不是网游\n');
				return false;
			}
		}else if($fen_lei['fen_lei']!='网游'){
			writelog('sdkchannel_error.log',$package.'不是网游\n');
			return false;
		}
		//$sdk_version = get_table_data(array('softid'=>$online_soft_id),'sj_soft_tmp','package','softid,package,sdk_version');
		$sdk_version = $model->findOne(array(
			'table'	=> 'sj_soft_tmp',
			'where'	=> array('softid'=>$online_soft['softid']),
			'field'	=> 'softid,package,sdk_version',
			'order' => 'id desc'
		));
		if(!$sdk_version||empty($sdk_version['sdk_version'])){
			writelog('sdkchannel_error.log',$package.'未扫描出sdk版本\n');
			return false;
		}else{
			//	判断sdk版本是否为3.2以上
			$ver = explode('.',$sdk_version['sdk_version']);
			if($ver[0]< 3||($ver[0]==3&&$ver[1] < 2)){
				writelog('sdkchannel_error.log',$package.'sdk版本不是3.2以上\n');
				return false;	
			}
		}
	}
	
	//准备要用数据
	$soft = array(
		'0'=>array(
			'softid'=> $online_soft['softid'],
			'softname'=> $online_soft['softname'],
			'package'=> $online_soft['package'],
			'version_code'=> $online_soft['version_code'],
			'version'=> $online_soft['version'],
			'update_content'=> $online_soft['update_content'],
			'sdk_version'=>	$sdk_version['sdk_version']
		)
	);
	
	
	if(count($soft)>0){
		foreach($soft as $k=>$v){
			$data = array(
				'softid'=>$v['softid'],
				'name'=>$v['softname'],				
				'channel_softname'=>$v['softname'],
				'package'=>$v['package'],
				'version_code_num'=>$v['version_code'],
				'version_code'=>$v['version'],
				'channel_id'=>$channel_id,
				'add_tm'=>time(),
				'update_tm'=>time()
			);
			//$res = $model->table('sdk_channel_game')->add($data);
			$data['__user_table'] = 'sdk_channel_game';
			$res = $model->insert($data);
			$sql = $model->getSql();
			if(!$res){
				$log = "step1-关联渠道游戏到sdk_channel_game表时失败\n" . $sql ."\n\n";
				writelog('sdkchannel.log',$log);
			}else{
				if($http_game){
					$channelId = $model->findOne(array('table'=>'sdk_channel','where'=>array('id'=>$channel_id),'field'=>'channel_name,channel_code'));
					
					$app_key = $model->findOne(array('table'=>'sj_sdk_info','where'=>array('package'=>$v['package']),'field'=>'app_id'));
					

					$http_data = array(
						'status'=>1,
						'id'=>$res,
						'channelId'=>$channelId['channel_code'],
						'version'=>$v['version'],
						'appName'=>$v['softname'],
						//'channelName'=>$channelId['channelName'], 新版增加渠道名称 2015.7.23
						//'APPKEY'=>$app_key['app_id'], 新版新增appkey
						'appNameChannel' => $v['softname'],
						'pkgName'=>$v['package']
					);
					$http_info = update_date_by_import($http_data);	
					if(!$http_info){							
						$data = array('__user_table'=>'sdk_channel_game','http_sta'=>2);
						
					}else{
						$data = array('__user_table'=>'sdk_channel_game','http_sta'=>1);					
					}
					$model->update(array('id'=>$res), $data);
				}
			}

			
			
			//判断同包名同版本是否已存在与合作渠道审核表，如已在不做处理，不再添加
			//$channel_id = array_unique($channel_id);

			$option = array(
				'table' => 'sdk_channel_game_sdk',
				'where' => array(
					'package'=>$v['package'],
					'version_code'=>$v['version_code'],
					'status'=>1,
					'channel_id'=>$channel_id
				),
				'field'	=> 'softid,sdk_status,channel_id'
			);
			$sdk_soft = $model->findAll($option);
			writelog('sdkchannel.log','step2--'.$model->getSql());
			
			if($sdk_soft){
				writelog('sdkchannel.log',$v['package'].'同包名同版本已存在合作渠道审核表/n');
				return false;
			}
		}
		
		$soft_apk = $model->findOne(array('table'=>'sj_soft_file','where'=>array('softid'=>$v['softid'],'package_status'=>1),'field'=>'apk_name,url'));
		writelog('sdkchannel.log','step2--'.print_r($soft_apk,true));

		$p_fenlei = $model->findOne(array('table'=>'sj_soft_whitelist','where'=>array('package'=>$v['package'],'status'=>1),'field'=>'softname,package,fen_lei as p_fenlei'));
		writelog('sdkchannel.log','step3--'.print_r($p_fenlei,true));	
		
		if(!$p_fenlei||empty($p_fenlei['p_fenlei'])){
			$yx_feilei = $model->findOne(
				array('table'=>'yx_product','where'=>array('package'=>$v['package'],'del'=>0),'field'=>'p_fenlei')
			);
			$p_fenlei['p_fenlei'] = $yx_feilei['p_fenlei'];
		}
		$rank = $model->findOne(array('table'=>'sdk_channel_game_sdk','field'=>'max(rank) as rank'));
		$insert_data = array(
			'softid'=>$v['softid'],
			'softname'=>$v['softname'],
			'package'=>$v['package'],
			'sdk_version'=>$v['sdk_version'],
			'game_type'=>$p_fenlei['p_fenlei'],
			'version'=>$v['version'],
			'version_code'=>$v['version_code'],
			'url'=>$soft_apk['url'],
			'sdk_status'=>4,
			'channel_id' => $channel_id,
			'need_test'=>$need_test,
			'rank'=>$rank['rank']+1,
			'update_tm'=>time(),
			'create_tm'=>time()
		);
		
		if(isset($record_type)&&!empty($record_type)){
			$insert_data['record_type'] = $record_type;
		}else{
			$insert_data['record_type'] = 1;
		}
		$insert_data['__user_table'] = 'sdk_channel_game_sdk';
		//$res = $model->table('sdk_channel_game_sdk')->add($insert_data);
		$res = $model -> insert($insert_data);
		writelog('sdkchannel.log','step4--'.$model->getSql());
		if(!$res){
			$sql = $model->getSql();
			$log = "step5--关联渠道游戏到testin审核表时失败\n" . $sql ."\n\n";
			writelog('sdkchannel.log',$log);
		}
	}	
}


	function writelog($filename,$msg){
		$now = time();
		$path = "/data/att/permanent_log/admin_data_log/".date("Y-m-d", $now);
		if(!file_exists($path)){
			mkdir($path, 0755, true);
		}	
		$path_log = $path."/".$filename;
		$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
		file_put_contents($path_log, $msg, FILE_APPEND);
	}
	
	function update_date_by_import($data){		
        //$host = 'http://192.168.3.91:9511';
	    $host = 'http://192.168.3.136:8089';
		$url = '/game/sdkChannel/CfgChannelGame/addRelation';
		$privatekey = 'eeUu5p6XElQbYGM26iCIOmo2';
		$des = new GoDes($privatekey);
		$data['pid'] = 1; 
		//var_dump($data);
		$temp_data = $des->encrypt(json_encode($data));
        $i_data = base64_encode($temp_data);
		$vals['data'] = $i_data;
		$vals['appKey'] = '142605894293bjc9VR9P3Xqv7jFTgh';
		//var_dump($host.$url);
		//var_dump($vals);
		// return false;
		$res = httpGetInfo($host.$url, $vals,'sdk_channel_http.log'); 
		$last = json_decode($res,true);
		//var_dump($last);
		if($last['statusCode']!='200'){
			return false;
		}else{
			return true;
		}
	}
	
	function httpGetInfo($url, $vals) {
        $res = curl_init();
        curl_setopt($res, CURLOPT_URL, $url);
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
        $result = curl_exec($res);
        $http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);
        writelog('sdkchannel_http.log',"{$http_code}|{$errno}|{$error}\n" .$url. print_r($vals, true) . "\n" . print_r($result, true) . "\n\n");
        return $result;
    }
	


