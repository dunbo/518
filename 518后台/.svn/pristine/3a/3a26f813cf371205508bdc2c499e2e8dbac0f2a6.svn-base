<?php
class InterfaceAction extends Action {
	//初始化函数 限制ip
	public function _initialize() {
		//return true;
		$ip = $_SERVER['REMOTE_ADDR'];
		$allow_ip = array(
			"192.168.0.*",
			"192.168.1.*",
			"192.168.3.*",
			//"10.0.3.*",
			"10.0.3.2",
			"42.62.4.169",
			"42.62.4.171",
			"218.241.82.226",
			"127.0.0.1",
			"42.62.70.157",
			"103.103.12.82",			
		);	

		foreach($allow_ip as $key=>$val) {
			if(strpos($val,'*')===FALSE) {
				if($val==$ip) {
					return TRUE;
				}
			} else {
				$val = str_replace('*','[0-9]{1,3}',$val);
				$val = str_replace('.','\.',$val);
				if(preg_match("/{$val}/",$ip)) {
					return TRUE;
				}
			}
		}
		exit(json_encode(array('code'=>-1,'msg'=>'该ip无权限','ip'=>$ip)));
	}

	//定时上架软件推送
	public function time_shelves_soft_push(){
		if(isset($_POST['softid'])){
			$model = new Model();
			$where = array(
				"softid"=>$_POST['softid']
			);
			$softinfo  = $model->table('sj_soft')->where($where)->field("package,softname,version,version_code,update_content")->find();
			$where['package_status'] = 1;
			$file_info  = $model->table('sj_soft_file')->where($where)->field("filesize,iconurl_125")->order("id desc")->find();
			$data = array(
				"softid"=>$_POST['softid'],
				"package"=>$softinfo['package'],
				"softname"=>$softinfo['softname'],
				"version" => $softinfo['version'],
				"version_code" =>$softinfo['version_code'],
				"filesize" => $file_info['filesize'],
				"iconurl" => 'http://img3.anzhi.com'.$file_info['iconurl_125'],
				"review_time" => time(),
				"update_content" => $softinfo['update_content'],
				"start_time" => time(),
				"end_time"  => time()+(86400*3)
			);
			push_soft_update_msg($data);
			//日志
			$log_path = '/data/att/permanent_log/'.$_SERVER['SERVER_NAME'].'/'.date('Y-m-d').'/';
			if(!is_dir($log_path))  mkdir($log_path,0777,true);
			$log = date("Y-m-d H:i:s")."\n\n软件推送软件id为".var_export($_POST,true)."\n\n推送内容为".var_export($data,true);
			file_put_contents($log_path.'time_shelves_soft_push.log',$log,FILE_APPEND);
		}else{
			exit(json_encode(array('code'=>-1,'msg'=>'缺少softid')));
		}

	}
	//获取软件信息
	public function get_soft_list(){
		$model = new Model();
		$where = array(
			'hide' =>1,
			'status' => 1
		);
		//type为1时走接口数据否则直接查库
		if($_POST['type'] == 1){
			$softname = trim($_POST['softname']);
			if(!$softname) {
				exit(json_encode(array('code'=>-1,'msg'=>'请填写名称！')));
			}	
			$res = $this -> get_client_pkg($softname);
			if(!$res){
				exit(json_encode(array('code'=>0,'msg'=>'无数据')));
			}else{
				$where['package'] = array('in',$res);	
			}
		}else{
			$softname = mysql_escape_string(trim($_POST['softname']));
			//$package = trim($_POST['package'])?trim($_POST['package']):trim($_GET['package']);
			$package = trim($_POST['package']);
			//$package = 'com.praseodymium.anzhi';
			if($softname){
				$where['softname'] =  array('like',"%{$softname}%");
			}	
			if($package) $where['package'] =  $package;	
			if(!$softname && !$package) {
				exit(json_encode(array('code'=>-1,'msg'=>'包名和名称必须有一个！')));
			}	
		}
		$field = "softid,package,softname,version,version_code";
		$order = "total_downloaded desc,softid desc";
		$ret = $model->table('sj_soft')->where($where)->field($field)->group('package')->order($order)->limit(20)->select();
		//echo $model->getlastsql();exit;
		if($ret){
			$where = array('softid'=>$ret[0]['softid'],'package_status'=>1);
			$softinfo  = $model->table('sj_soft_file')->where($where)->field("iconurl,sign")->find();
			if($softinfo){
				$ret[0]['iconurl'] = IMGATT_HOST.$softinfo['iconurl'];
				$ret[0]['sign'] = $softinfo['sign'];
			}	
			exit(json_encode(array('code'=>1,'msg'=>'成功','ret'=>$ret)));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'无数据')));
		}
	}
	//搜索接口返回
	public function get_client_pkg($query){
		include_once SERVER_ROOT. 'GoPHP/helper/search.helper.php';
		$client = new SearchKeyClient();
		$client->SetServer('103.17.40.101',1112);
		$client->SetConnectTimeout(4);
		$res = $client->query($query);
		$pkg = array();
		foreach($res as $v){
			if($v['package']) $pkg[] = $v['package'];
		}
		unset($res);
		return $pkg;
	}

	public function notify(){
		$time = time();	
		$type = intval($_POST['type']);
		// $allow_types = array(1,2,3,4);//type:1任务2奖品3精选软件4抽奖相关
		if(!$type) exit(json_encode(array('code'=>-1,'msg'=>'type值必须填写')));
		// if(!in_array($type,$allow_types)) exit(json_encode(array('code'=>-1,'msg'=>'type值不存在')));
		$model = new Model();
		$data = array(
			'fromip' => $_SERVER['REMOTE_ADDR'],
			'actionexp'=>$type,
			'log_time' => $time,
			'update_time' => $time,
		);
		$log_id = $model->table('sj_task_client_log')->add($data);
		$task_client = get_task_client();
		$task_val = array(
			"type"=>$type,
			"atime"=>$time,
			"file"=>'cache/cache_task.php '. $type,
			"log_id" =>$log_id,
		);
		$handle = $task_client->doBackground("ucenter_callback", json_encode($task_val));
		$status = $task_client->jobStatus($handle);
		if ($status[0] === true){
			if($status[1] === false){
				exit(json_encode(array('code'=>1,'msg'=>'成功延时处理')));
				//	@todo:	work进程不存在?报警
			}
			exit(json_encode(array('code'=>1,'msg'=>'成功实时处理')));
		}
		exit(json_encode(array('code'=>0,'msg'=>'接口请求失败')));
	}
	public function sync_gift_status(){
		$status = $_POST['status'];//1成功0失败
		if($status){
			$activeid = $_POST['id'];
			$active_model = D('sendNum.sendNum');
			$where = array('id'=>$activeid);
			$data = array('sync' => 1);
			$res = $active_model->table('sendnum_active')->where($where)->save($data);
			if(!$res){
				//日志
				$log_path = '/data/att/permanent_log/'.$_SERVER['SERVER_NAME'].'/'.date('Y-m-d').'/';
				if(!is_dir($log_path))  mkdir($log_path,0777,true);
				$log = date('Y-m-d H:i:s').':'.$active_model -> getlastsql()."\n";
				file_put_contents($log_path.'sync_gift_status.log',$log,FILE_APPEND);
				exit(json_encode(array('code'=>0,'msg'=>'接口请求失败')));
			}
		}	
		exit(json_encode(array('code'=>1,'msg'=>'成功实时处理')));
	}
	//CP通讯录数据接口
	public function get_contacts(){
		$model = new Model();
		// $_POST['appName'] = '植物大战僵尸无尽';
		// $_POST['pageIndex'] = 1;
		// $_POST['resultSize'] = 10;
		// $_POST['appKey'] = '1378375366Az26xatNyDOD5EM6D2ys';

		$softname = trim(urldecode($_POST['appName']));
		$dev_name = trim(urldecode($_POST['devName']));
		$app_key = trim($_POST['appKey']);
		$p = trim($_POST['pageIndex']);
		$limit = trim($_POST['resultSize']);
		if(!$p) exit(json_encode(array('code'=>-1,'msg'=>'请填写页码')));	
		if(!$limit) exit(json_encode(array('code'=>-1,'msg'=>'请填写每页显示结果数据')));	
		$where = array(
			'status' => 1
		);
		if($softname){
			$where['softname'] = array('like',"%{$softname}%"); 
		}
		if($dev_name){
			$where['dev_name'] = array('like',"%{$dev_name}%"); 
		}
		if($app_key){
			$where_k = array(
				'app_id' => $app_key,
				'app_status' => 1
			);
			$subQuery = $model->table('sj_sdk_info')->field('package')->where($where_k)->buildSql(); 
			$where['package'] = array('in',"{$subQuery}"); 
		}
		if(!$p) $p = 1;
		$firstRow = ($p-1) * $limit;
		$filed = "softname,package,dev_name"; 
		$total = $model -> table('sj_soft_whitelist')->where($where)->count();
		$list = $model -> table('sj_soft_whitelist')->where($where)->limit($firstRow .','.$limit)->field($field)->select();
		// var_dump($_POST);
		 // var_dump($list);
		 // echo $model->getlastsql();exit;
		if(!$list) exit(json_encode(array('code'=>0,'msg'=>'无数据')));	
		$pkg = array();
		foreach($list as $k => $v){
			$pkg[] = $v['package'];
		}
		$where = array(
			'package' => array('in',$pkg),
			'del' => 0,
		);
		$product  = get_table_data($where,"yx_product","package","package,customer_qq,customer_tel,contacts,com_tj_tel");
		$where = array(
			'package' => array('in',$pkg),
			'app_status' => 1,
		);
		$sdk_info = get_table_data($where,"sj_sdk_info","package","package,app_id");
		//整理数据
		$return_list = array();
		foreach($list as $k => $v){
			$return_list[$k]['appName'] = $v['softname'];
			$return_list[$k]['packageName'] = $v['package'];
			$return_list[$k]['devName'] = $v['dev_name'];
			$return_list[$k]['qq'] = $product[$v['package']]['customer_qq'];
			$return_list[$k]['telephone'] = $product[$v['package']]['customer_tel'];
			$return_list[$k]['contact'] = $product[$v['package']]['contacts'];
			$return_list[$k]['contactPhone'] = $product[$v['package']]['com_tj_tel'];
			$return_list[$k]['appKey'] = $sdk_info[$v['package']]['app_id'];
		}		
		exit(json_encode(array('code'=>1,'msg'=>'成功','list'=>$return_list,'totalNum'=>$total)));
	}
	//活动的列表数据
	function get_activity_list(){
		$model = new Model();
		$time = time();
		$where = array(	
			'status' => 1,
			'activity_type'=>array(
				//过滤 5、九宫格，6,天降红包雨，7，红包翻翻乐，8，红包叠叠乐
				"not in","5,6,7,8"
			),
			'start_tm' => array('elt',$time),
			'end_tm' => array('egt',$time),
		);	
		$field = "id,name,start_tm,end_tm,sdk_type,vip_jump";
		$list = $model->table('sj_activity')->where($where)->field($field)->order('end_tm desc')->limit(100)->select();	
		//echo $model->getlastsql();	
		if($list){
			foreach($list as $k => $v){
				$list[$k]['acrivity_url'] = ACTIVITY_URL ."/lottery/activity_hop.php?aid=".$v['id'];
			}
			exit(json_encode(array('code'=>1,'msg'=>'成功','list'=>$list)));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'无数据')));
		}
	}

	//获取sdk版本列表
	function sdk_list(){
		$model = new Model();
		$where = array('status'=>1);
		$list = $model->table('sj_sdk')->where($where)->field('version_code,version_name,sdk_type')->group('version_code')->select();		
		if($list){
			$list_w = array();
			$list_s = array();
			$sdk_type = 0;
			foreach($list as $val){
				$sdk_type = $val['sdk_type'];
				unset($val['sdk_type']);
				if($sdk_type == 1){
					$list_w[] = $val;
				}else if($sdk_type == 2){
					$list_s[] = $val;
				}
				
			}
			exit(json_encode(array('code'=>1,'msg'=>'成功','list'=>$list_w,'list2'=>$list_s)));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'无数据')));
		}
	}
	//获取推广产品
	function extend_list(){
		$model = new Model();
		$where = array('e.status'=>1,'e.extend_sta'=>2);
		
		$p = trim($_POST['page'])?trim($_POST['page']):1;
		$limit = trim($_POST['size'])?trim($_POST['size']):20;
		
		if(isset($_POST['softname'])&&!empty($_POST['softname'])){
			$softname = urldecode($_POST['softname']);
			$softname = trim($softname);
			$where['e.softname'] = array('like',"%{$softname}%"); 
		}
		if(isset($_POST['package'])&&!empty($_POST['package'])){
			$package = urldecode($_POST['package']);
			$package = trim($package);
			$where['e.package'] = $package;
		}
		$where['s.status'] = 1;
		$where['s.hide'] = 1;		
		$total = $model -> table('sdk_channel_extend as e left join sj_soft s on e.package=s.package')->where($where)->field('e.id')->group('s.package')->select();
		$total = count($total);
		
		$field = 'e.softname,e.package,MAX(s.version_code) as version_code';		
		$firstRow = ($p-1) * $limit;
		$list = $model -> table('sdk_channel_extend as e left join sj_soft s on e.package=s.package')->where($where)->limit($firstRow .','.$limit)->field($field)->group('s.package')->select();
		if($list){
			exit(json_encode(array('code'=>1,'msg'=>'成功','total'=>$total,'list'=>$list)));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'无数据')));
		}
	}
}
