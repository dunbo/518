<?php
class sendNumModel extends AdvModel{
	protected $connect_id = 15;
	protected $tablePrefix = '';
	protected $table = 'sendnum_active';
	protected $fields = '';
	public function __construct()
	{
		//parent::__construct();
		$myConnect1 = C('DB_CO_SENDNUM');
		$this -> addConnect($myConnect1, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}

	//添加活动
	public function add_active($data){
		$this -> trueTableName = $this -> table;
		$this -> fields = array('id','active_name','active_type','conf_cnt','market_conf_cnt','game_conf_cnt','sdk_conf_cnt','weixin_conf_cnt','bbs_conf_cnt','game_id','used_cnt',
		'num_cnt','start_tm','end_tm','creater','creater_id','status','active_from','gift_type');
		$affectid = $this -> add($data);
		return $affectid;
	}
	public function add_gift($data){
		$this -> trueTableName = 'sendnum_tmp';
		$this -> fields = array('id','apply_pkg','active_name','gift_type','start_tm','end_tm','be_limit_num','num_cnt','used_cnt','detail','exchange_start','exchange_end','usable','usage','rank','status','active_from',
		'is_exclusive','up_file_path','market_conf_cnt','game_conf_cnt','weixin_conf_cnt','bbs_conf_cnt','sdk_conf_cnt','add_tm','creater','creater_id','intro');
		$affectid = $this -> add($data);
		return $affectid;
	}	
        //新版添加礼包
        
	public function add_active_content($data){
		$this -> trueTableName = 'olgame_active';
		$this -> fields = array('id','active_id','apply_pkg','usable','module_content','rank','status','gift_url','create_tm','update_tm','rerelease_tm','be_active_name','be_apply_pkg','be_usable','be_cut_tm','be_limit_num','intro','selection',"exchange_start","exchange_end","usage","detail","active_type");
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	public function update_active_content($where,$data){
		$this -> trueTableName = 'olgame_active';
		$this -> fields = array('id','active_id','apply_pkg','usable','module_content','rank','status','gift_url','create_tm','update_tm','rerelease_tm','be_active_name','be_apply_pkg','be_usable','be_cut_tm','be_limit_num','intro','selection',"exchange_start","exchange_end","usage","detail",'active_type');
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	//添加号码
	// public function add_active_num($nums,$tabid = ''){
		// $this -> trueTableName = 'sendnum_number_'.$tabid;
	public function add_active_num($nums){
		$this -> trueTableName = 'sendnum_number';
		$this -> fields = array('id','active_num','ip','uid','active_id','active_type','take_tm','status','from');
		$affect = $this -> add($nums);
		return $affect;
	}
	public function create_sendnum_number($id){
		$sql = "CREATE TABLE `sendnum_number_$id` (
					`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
					`active_num` varchar(50) DEFAULT NULL COMMENT '活动号码',
					`ip` varchar(16) DEFAULT NULL COMMENT '获取号码的用户ip',
					`user_type` tinyint(2) NOT NULL COMMENT '用户类型，1:已登录用户 2:未登录用户',
					`user_id` varchar(255) NOT NULL COMMENT '获取活动的用户表示，为uid或device_id',
					`active_id` int(11) DEFAULT NULL COMMENT '参与的活动id',
					`take_tm` int(11) DEFAULT NULL COMMENT '获取号码的时间',
					`status` int(11) DEFAULT NULL COMMENT '号码的状态1为已获得 2为失效 0未获得',
					`device_id` varchar(100) NOT NULL default '' COMMENT '设备标示',
					`order_tm` int(11) DEFAULT NULL,
					`from` tinyint(1) DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `activenum` (`active_num`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		$creat = $this -> query($sql);
		//echo $this->getlastsql();exit;
	}
	public function create_newserver_activecode($id){
		$sql = "CREATE TABLE `activecode_$id` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`uid` varchar(100) NOT NULL DEFAULT '' COMMENT '用户ID',
			`number` varchar(100) NOT NULL DEFAULT '' COMMENT '激活码',
			`ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登陆ip',
			`create_tm` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
			`take_tm` int(11) NOT NULL DEFAULT 0 COMMENT '领取时间',
			`from` int(11) NOT NULL DEFAULT 0 COMMENT '渠道',
			`imei` varchar(100) NOT NULL DEFAULT '' COMMENT '设备imei',
			`status` int(11) NOT NULL DEFAULT 0 COMMENT '号码的状态1为已获得 2为失效 0未获得3已提取',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$creat = $this -> query($sql);
		//echo $this->getlastsql();exit;
	}

	public function add_market_num($sql_str,$id){
		$sql = "INSERT INTO sendnum_number_{$id} (`active_id`,`active_num`,`status`) VALUES ".$sql_str;
		$affect = $this -> query($sql);
		return $affect;
	}
	public function add_new_server_num($sql_str,$id){
		$sql = "INSERT INTO activecode_{$id} (`create_tm`,`number`,`status`) VALUES ".$sql_str;
		$affect = $this -> query($sql);
		return $affect;
	}

	public function active_save($where,$save){
		$this -> trueTableName = $this -> table;
		$this -> fields = array('id','active_name','active_type','conf_cnt','game_id','used_cnt',
		'num_cnt','start_tm','end_tm','creater','creater_id','status','market_conf_cnt','game_conf_cnt','sdk_conf_cnt','gift_type','active_from');
		
		$affect = $this -> where($where) -> save($save);
		return $affect;
	}

	
	function closeconnect(){
		$this -> closeConnect($this -> connectid);
	}
	//追加礼包
	function get_gift_code(){
		//上传文件获取隐藏域中的内容
		if (!empty($_FILES['gift_num'])) {
			$array = array('csv');
			$ytypes = $_FILES['gift_num']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			$error = '';
			if(!in_array($type,$array)){
				$error .= "上传格式错误\n";
			}		

			//把文件中的数据取出转字符编码					
			$data = file_get_contents($_FILES['gift_num']['tmp_name']);
			$data = str_replace("\r\n","\n",$data);	
			$data_arr = explode("\n", $data);
			//判断是否是utf-8
			if(mb_check_encoding($data,"utf-8") != true){
				$error .= "上传格式错误\n";
			}
			
			if($error == ''){
				$arr = array();
				$sendnum = $this->table('sendnum_number_'.$_GET['active_id'])->field('active_num')->select();
				$i = 0;
				foreach($sendnum as $k=>$v){
					if($v) $i++;
					$key = trim($v['active_num']);
					$arr[$key] = 1;
				}
				$ii=0;
				foreach($data_arr as $k=>$v){
					if($v) $ii++;
					if(strlen($v) > 25){
						$error .= "激活码长度不能大于25位\n";
					}
					if(preg_match("/[\x80-\xff]./", $v)){
						$error .= "激活码文件中不可有中文\n";
					}
					$kk = trim($v);
					if($arr[$kk] == 1){
						$error .= "激活码文件中含有重复数据:{$v}\n";
					}else{
						$arr[$kk] = 1;
					}
				}	
				$all_num = $i+$ii;				
				if(($ii+$_GET['residue_num']) >9999){
					$error .= "激活码有效总数不能超过9999个\n";
				}
	
			}
			if($error == ''){
				// ECHO $model->getsql();
				list($msec,$sec) = explode(' ',microtime());
				$msec = substr($msec,2);
				$file = "/tmp/$msec.csv";
				foreach($arr as $k =>$v){
					file_put_contents($file,$k."\n",FILE_APPEND);
				}
				$file_add = "/tmp/{$msec}_add.csv";
				move_uploaded_file($_FILES['gift_num']['tmp_name'],$file_add);
			}
			$return = array(
				'error' => $error,
				'all_num'=>$all_num,
				'num' => $ii,
				'file' => $file,
				'file_add' => $file_add,
			);
			return $return;
		}
	}
	//追加新服激活码
	function get_new_server_code(){
		//上传文件获取隐藏域中的内容
		if (!empty($_FILES['activation_code'])) {
			$array = array('csv');
			$ytypes = $_FILES['activation_code']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			$error = '';
			if(!in_array($type,$array)){
				$error .= "上传格式错误\n";
			}		

			//把文件中的数据取出转字符编码					
			$data = file_get_contents($_FILES['activation_code']['tmp_name']);
			$data = str_replace("\r\n","\n",$data);	
			$data_arr = explode("\n", $data);
			//判断是否是utf-8
			if(mb_check_encoding($data,"utf-8") != true){
				$error .= "上传格式错误\n";
			}
			
			if($error == ''){
				$arr = array();
				$sendnum = $this->table('activecode_'.$_GET['active_id'])->field('number')->select();
				$i = 0;
				foreach($sendnum as $k=>$v){
					if($v) $i++;
					$key = trim($v['number']);
					$arr[$key] = 1;
				}
				$ii=0;
				foreach($data_arr as $k=>$v){
					if($v) $ii++;
					if(strlen($v) > 25){
						$error .= "激活码长度不能大于25位\n";
					}
					if(preg_match("/[\x80-\xff]./", $v)){
						$error .= "激活码文件中不可有中文\n";
					}
					$kk = trim($v);
					if($arr[$kk] == 1){
						$error .= "激活码文件中含有重复数据:{$v}\n";
					}else{
						$arr[$kk] = 1;
					}
				}	
				$all_num = $i+$ii;				
				if(($ii+$_GET['residue_num']) >9999||($ii+$_GET['residue_num'])< 10)
				{
					$error .= "激活码有效总数不能超过10~9999个\n";
				}
	
			}
			if($error == ''){
				// ECHO $model->getsql();
				list($msec,$sec) = explode(' ',microtime());
				$msec = substr($msec,2);
				$file = "/tmp/$msec.csv";
				foreach($arr as $k =>$v){
					file_put_contents($file,$k."\n",FILE_APPEND);
				}
				$file_add = "/tmp/{$msec}_add.csv";
				move_uploaded_file($_FILES['activation_code']['tmp_name'],$file_add);
			}
			$return = array(
				'error' => $error,
				'all_num'=>$all_num,
				'num' => $ii,
				'file' => $file,
				'file_add' => $file_add,
			);
			return $return;
		}
	}
	function post_gift_reseed(){
		$dir_img = UPLOAD_PATH . '/gift/'.date('Ym/d').'/';
		$error = '';
		if(!is_dir($dir_img)) {
			if(!mkdir($dir_img,0777,true)) {
				//创建gift目录{$dir_img}失败
				$error .=  "创建gift目录{$dir_img}失败";
			}
		}
		if(empty($_POST['file_path'])){
			$error .=  "请上传激活码";
		}
		list($msec,$sec) = explode(' ',microtime());
		$types = 'csv';
		$msec = substr($msec,2);
		$dst = $dir_img.'gift_num'.'_'.$msec.'.'.$types;
		$src = $_POST['file_path'];
		if (!copy($src, $dst)) {
			$error .=  "上传失败";
		}
		
		$dst_add = $dir_img.'gift_num'.'_'.$msec.'_add.'.$types;
		if (!copy($_POST['file_add'], $dst_add)) {
			$error .=  "上传失败";
		}
		
		if($error == ''){

			$path = str_replace(UPLOAD_PATH,'',$dst);
			$path_add = str_replace(UPLOAD_PATH,'',$dst_add);
				
			$data = file_get_contents($_POST['file_add']);
			$data = str_replace("\r\n","\n",$data);	
			$data_arr = explode("\n", $data);
			$data_arr = array_chunk($data_arr,500);
			//激活码入口
			$n = 0;
			foreach ($data_arr as $key => $val) {
				if ($val) {
					$sql_str = '';
					foreach($val as $v){
						if(empty($v)) continue;
						$sql_str .= ",({$_POST['active_id']},'{$v}',0)";
						$n++;
					}
					$sql_str =  substr($sql_str,1);
					$sql = "INSERT INTO sendnum_number_{$_POST['active_id']} (`active_id`,`active_num`,`status`) VALUES ".$sql_str;
					$affect = $this -> query($sql);
				}
			}
			$where = array(
				'sendnumactive_id' => $_POST['active_id'],
			);
			$sendnum_list = $this -> table('sendnum_tmp')->where($where)->field('old_up_file_path,num_cnt')->find();
			if($sendnum_list){
				$map = array(
					'num_cnt' => $_POST['all_num'],
					'market_conf_cnt' => $_POST['market_conf_cnt'],
					'game_conf_cnt' => $_POST['game_conf_cnt'],
					'sdk_conf_cnt' => $_POST['sdk_conf_cnt'],
					'bbs_conf_cnt' => $_POST['bbs_conf_cnt'],
					'weixin_conf_cnt' => $_POST['weixin_conf_cnt'],
					'up_file_path' => $path,
					'old_up_file_path' => $sendnum_list['old_up_file_path'] .','. $path_add,
				);
				$this -> table('sendnum_tmp')->where($where)->save($map);
			}
			$map = array(
				'num_cnt' => $_POST['all_num'],
				'market_conf_cnt' => $_POST['market_conf_cnt'],
				'game_conf_cnt' => $_POST['game_conf_cnt'],
				'sdk_conf_cnt' => $_POST['sdk_conf_cnt'],
				'bbs_conf_cnt' => $_POST['bbs_conf_cnt'],
				'weixin_conf_cnt' => $_POST['weixin_conf_cnt'],
				'gift_reseed_tm' => time(),
			);
			$where = array(
				'id' => $_POST['active_id'],
			);
			$this -> table('sendnum_active')->where($where)->save($map);	
		}	
		if($error){
			return array('code'=>0,'error'=>$error);
		}else{	
			return array('code'=>1);
		}
	}
	function update_platform_num(){
		$where = array(
			'sendnumactive_id' => $_POST['active_id'],
		);
		$map = array(
			'num_cnt' => $_POST['all_num'],
			'market_conf_cnt' => $_POST['market_conf_cnt'],
			'game_conf_cnt' => $_POST['game_conf_cnt'],
			'sdk_conf_cnt' => $_POST['sdk_conf_cnt'],
			'bbs_conf_cnt' => $_POST['bbs_conf_cnt'],
			'weixin_conf_cnt' => $_POST['weixin_conf_cnt'],
		);
		$tmp_list = $this -> table('sendnum_tmp')->where($where)->find();
		$ret = $this -> table('sendnum_tmp')->where($where)->save($map);
		$map = array(
			'num_cnt' => $_POST['all_num'],
			'market_conf_cnt' => $_POST['market_conf_cnt'],
			'game_conf_cnt' => $_POST['game_conf_cnt'],
			'sdk_conf_cnt' => $_POST['sdk_conf_cnt'],
			'bbs_conf_cnt' => $_POST['bbs_conf_cnt'],
			'weixin_conf_cnt' => $_POST['weixin_conf_cnt'],
			'gift_reseed_tm' => time(),
		);
		$where = array(
			'id' => $_POST['active_id'],
		);
		$ret2 =  $this -> table('sendnum_active')->where($where)->save($map);			
		if(!$ret || !$ret2 ){
			return array('code'=>0,'error'=>'修改平台分发数失败');
		}else{	
			//
			$fileicon  = get_table_data(array('apk_name' => $tmp_list['apply_pkg']),"sj_soft_file","apk_name","apk_name,iconurl");
			$soft_arr  = get_table_data(array('package' => $tmp_list['apply_pkg'],'status'=>1,'hide'=>1),"sj_soft","package","package,softname");
			$send_data = array(
				'serviceId' => $tmp_list['active_from'],       //业务线id  ,礼包的使用业务线
				'giftName' => $tmp_list['active_name'],//"奖品名称"
				'giftType' => '2',//奖品类型：1:积分  2：礼包  3：话费
				'giftSoftName' => $soft_arr[$tmp_list['apply_pkg']]['softname'],//软件名称
				'giftSoftPname' => $tmp_list['apply_pkg'],//软件包名
				'giftTotal' => $_POST['all_num'],//礼包总数
				'giftEvSum' => $tmp_list['be_limit_num'],//明日发放限制数量
				'prImage' => 'http://img3.anzhi.com'.$fileicon[$tmp_list['apply_pkg']]['iconurl'],//奖品图片
				'prShelvesDate' => $tmp_list['start_tm'],   //有效期--开始时间--使用时间
				'prUnderDate' =>  $tmp_list['end_tm'],   //有效期--结束时间
				'prSdate' => $tmp_list['exchange_start'] ,  //上架时间
				'prEdate' =>  $tmp_list['exchange_end'], //下架时间
				'prSortno'  => $tmp_list['rank'],//排序号
				'remark' => $tmp_list['detail'], //奖品说明
				'giftUse' => '2', //奖品用途   0:抽奖  1:兑换   2:领取
				'useRange' => $tmp_list['usable'],//使用范围
				'useWay' => $tmp_list['usage'] ,//使用方法
				'createTime' => $tmp_list['add_tm'],
				'updateTime' => $tmp_list['update_tm'],
				'delStatus' => 0 ,//0:整除  1:删除。
				'ref_id' => $_POST['active_id'],
				'fromServiceId' => '007',//数据来源业务线
				'oper_type' => 1,//0:新增、1：修改、2：删除
			);
			$this -> send_gift_work($send_data);
			return array('code'=>1);
		}
	}
	function send_gift_work($send_data){
		$task_client = get_task_client("gift");
		$json =  json_encode($send_data);
		$task_client->doHighBackground("gift",$json);
		//日志
		$log_path = '/data/att/permanent_log/'.$_SERVER['SERVER_NAME'].'/'.date('Y-m-d').'/';
		if(!is_dir($log_path))  mkdir($log_path,0777,true);
		$log = date('Y-m-d H:i:s').':'.$json."\n";
		file_put_contents($log_path.'gift_work.log',$log,FILE_APPEND);
	}
	//礼包剩余量
	function get_gift_surplus($active_id){
		//剩于量
		$cli = "/usr/local/bin/redis-cli -h ";
		$ip = C('GIFT_REDIS_HOST');
		$port = C('GIFT_REDIS_PORT');	
		//echo "{$cli} {$ip} -p {$port}  llen 'NUMBER_LIST:{$active_id}'";
		$surplus_num = shell_exec("{$cli} {$ip} -p {$port}  llen 'NUMBER_LIST:{$active_id}'");
		return 	$surplus_num;
	}
	function get_active_data($active_id){
		// $redis = new redis();
		// $redis->connect(C('GIFT_REDIS_HOST'),C('GIFT_REDIS_PORT'));		
		// $key = "ACTIVE:{$active_id}:FROM:4:SENDED_NUMBER";
		// $redis->hSet($key, 'zhuang', 'val0');
		$where = array(
			'A.active_id' => $active_id,
			'B.id' => $active_id,
			'A.status' => 1,
			'B.status' => 1
		);
		$field = 'B.active_name,B.gift_type,B.bbs_conf_cnt,B.game_conf_cnt,B.market_conf_cnt,B.sdk_conf_cnt,B.weixin_conf_cnt,B.cnt1,B.cnt2,B.cnt4,B.cnt8,B.cnt16,A.detail,A.exchange_start,A.exchange_end,A.usable,A.usage';
		$list = $this->table('olgame_active A')->join("sendnum_active B ON B.id = A.active_id")->where($where)->field($field)->find();	
		$where = array('id'=>$list['gift_type']);
		$gift_type = $this->table('sendnum_gift_type')->where($where)->find();
		$list['gift_type'] = $gift_type['gift_type'];
		return $list;
	}
	//导出激活码
	function export_gift_file($active_id,$data){
		$redis = new redis();
		$redis->connect(C('GIFT_REDIS_HOST'),C('GIFT_REDIS_PORT'));
		$time = time();
		$list = $this -> get_active_data($active_id);
		$header = "礼包名称,".$list['active_name']."\n";
		$header .= "礼包类型,".$list['gift_type']."\n";
		$header .= "礼包详情,".$list['detail']."\n";
		$header .= "使用时间,".date("Y-m-d H:i:s",$list['exchange_start'])."-".date("Y-m-d H:i:s",$list['exchange_end'])."\n";
		$header .= "使用范围,".$list['usable']."\n";
		$header .= "使用方法,".$list['usage']."\n";
		$header .= "提取时间,".date("Y-m-d H:i:s")."\n";
		//@
		$total = 0;
		$str = '';
		foreach($data as $key => $val){
			if($val['num']>0){
				$total = $total+$val['num'];
				$str .= $val['msg'].",\n";
				$az_arr = array();
				for($i=1;$i<=$val['num'];$i++){
					$gift_num = json_decode($redis -> rpop("NUMBER_LIST:{$active_id}"));
					$redis -> hset("ACTIVE:{$active_id}:FROM:".$key.":SENDED_NUMBER",$gift_num,1);
					$az_arr[] = $gift_num;
					$str .= ','.$gift_num."\n";
				}
				$data_arr = array_chunk($az_arr,500);
				foreach($data_arr as $v){
					$where = array('active_num'=>array('in',$v));
					$map = array(
						'status'=>3,
						'from'=>$key,
						'take_tm' => $time
					);
					$result = $this->table("sendnum_number_".$active_id)->where($where)->save($map);
				}
			}
		}
		$date = date("Ym/d/");
		$dir_img = C('ACTIVITY_CSV') . '/gift/'.$date;
		if(!is_dir($dir_img)) mkdir($dir_img,0777,true);
		$dst = $dir_img.'sendnum_number_export_'.$active_id.'_'.$time.'.csv';
		$header .= "提取个数,".$total."\n";
		$header .= "邀请码,\n";
		file_put_contents($dst,mb_convert_encoding($header.$str,"gbk","utf-8"));
		$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
		$map = array(
			'cnt1' => array("exp","`cnt1`+{$data[1]['num']}"),
			'cnt2' => array("exp","`cnt2`+{$data[2]['num']}"),
			'cnt4' => array("exp","`cnt4`+{$data[4]['num']}"),
			'cnt8' => array("exp","`cnt8`+{$data[8]['num']}"),
			'cnt16' => array("exp","`cnt16`+{$data[16]['num']}"),
			'used_cnt' => array("exp","`used_cnt`+{$total}"),
			'update_tm' => $time
		);
		$where = array(
			'id' => $active_id
		);
		$this ->table('sendnum_active')->where($where)->save($map);
		//echo $this->getlastsql();
		//历史数据入库
		$json = json_encode($data);
		$map = array(
			'active_id' => $active_id,
			'num' => $total,
			'file_path' => $path,
			'add_tm' => $time,
			'from' => $json,
			'admin_id' => $_SESSION['admin']['admin_id']
		);
		$res = $this ->table('sendnum_number_export')->add($map);
		$ret_arr = array(
			'id' => $res ,
			'file_path' => base64_encode($path),
		);
		return $ret_arr;
	}
}