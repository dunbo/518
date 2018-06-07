<?php

class CoActivityModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'gm_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	public function ranking_award_add_do(){
		//表单验证
		if(!$_POST['name']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品名称",
			);
			return $return;		
		}
		if(!$_POST['level']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品等级",
			);
			return $return;	
		}
		if(!$_POST['prize_num']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品数量",
			);
			return $return;				
		}		
		if(!$_POST['pid'] && empty($_FILES['pic_path']['tmp_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请上传奖品图片",
			);
			return $return;				
		}		

		//if(!$_POST['pid'] && $_POST['type'] == 4 && empty($_FILES['gift']['tmp_name'])){
		if(!$_POST['pid'] && $_POST['type'] == 2 && empty($_FILES['gift']['tmp_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请上传礼包",
			);
			return $return;				
		//}else if($_POST['pid'] && $_POST['type'] == 4 && empty($_FILES['gift']['tmp_name'])){
		}else if($_POST['pid'] && $_POST['type'] == 2 && empty($_FILES['gift']['tmp_name'])){
			$where = array('pid' => $_POST['pid']);
			$res = $this -> table('gm_lottery_prize') -> where($where)->field('gift_file') -> find();
			if(!$res['gift_file']){
				$return = array(
					'code' => 0,
					'msg' => "请上传礼包",
				);
				return $return;	
			}
		}	

		if($_POST['type'] == 4 && empty($_POST['couponid'])){
			$return = array(
					'code' => 0,
					'msg' => "请填写礼券ID",
			);
			return $return;
		}elseif( !$_POST['id'] && $_POST['type'] == 5 && empty($_POST['code']) ){
			$return = array(
					'code' => 0,	
					'msg' => "有未上传包名或文选择礼包",
			);
			return $return;
		}

		$where = array(
			'aid' => $_POST['aid'],
			'status' => 1,
			'level' => $_POST['level']
		);
		if($_POST['pid']){
			$where['pid'] = array('exp',"!={$_POST['pid']}");
		}		
		$have_level = $this -> table('gm_lottery_prize') -> where($where) -> find();
		//echo $this->getlastsql();
		if($have_level){
			$return = array(
				'code' => 0,
				'msg' => "该活动已包含此等级奖品",
			);
			return $return;				
		}	
		if($_POST['lottery_style'] == 1){
			$width = 80;
			$height = 128;
		}elseif($_POST['lottery_style'] == 2){
			$width = 100;
			$height = 100;
		}elseif($_POST['lottery_style'] == 3){
			$width = 60;
			$height = 78;
		}
		$date = date("Ym/d/");
		if($_FILES['pic_path']['tmp_name']){
			$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
					'code' => 0,
					'msg' => "分辨率图标大小不符合条件",
				);
				return $return;
                        }
			$config['multi_config']['pic_path'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $date,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		$desc = trim($_POST['desc']);	
		$data = array(
			'name' => trim($_POST['name']),
			'type' => $_POST['type'],
			'level' => $_POST['level'],
			'desc' => str_replace(array("\r\n","\n"),'',$desc),
			'aid' => $_POST['aid'],
		);		

		if($_FILES['gift']['tmp_name']){
			$array = array('csv');
			$ytypes = $_FILES['gift']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			if(!in_array($type,$array)){
				$return = array(
					'code' => 0,
					'msg' => "上传格式错误",
				);
				return $return;						
			}		
			//验证礼包重复数据
			$data_file = file_get_contents($_FILES['gift']['tmp_name']);
			//判断是否是utf-8编辑
			if(mb_check_encoding($data_file,"utf-8") != true){
				$data_file = iconv("gbk","utf-8", $data_file);
			}
			$data_file = str_replace("\r\n","\n",$data_file);	
			$data_arr = explode("\n", $data_file);
			$newarr = array();
			$str = '礼包重复数据：';
			$str2 = '';
			$gift_status = '';
			foreach($data_arr as $k=>$v){
				if($k == 0 || empty($v)){
					continue;
				}
				list($softname,$pkg,$num) = explode(',',$v);
				if(!$num){
					$gift_status .= $k."行\n";
					continue;
				}
				if($newarr[$num]){
					$str .= $num."\n";
				}else{
					$newarr[$num] = 1;
				}
				if(strpos($num,".")){
					$str2 .= "该礼包【有点】礼包码为：".$num;
				}					
			}	
			if($gift_status != ''){
				$return = array(
					'code' => 0,
					'msg' => '礼包码第'.$gift_status.'出错，请认真检查',
				);
				return $return;	
			}			
			if($str != '礼包重复数据：'){
				$return = array(
					'code' => 0,
					'msg' => $str,
				);
				return $return;					
			}	
			if($str2 != ''){
				$return = array(
					'code' => 0,
					'msg' => $str2,
				);
				return $return;					
			}				
			//上传礼包
			$dir_img = C('ACTIVITY_CSV') . '/gift/'.$date;
			if(!is_dir($dir_img)) {
				if(!mkdir($dir_img,0777,true)) {
					//创建gift目录{$dir_img}失败
					$return = array(
						'code' => 0,
						'msg' => "创建gift目录{$dir_img}失败",
					);
					return $return;				
				}
			}
			list($msec,$sec) = explode(' ',microtime());
			$types = 'csv';
			$msec = substr($msec,2);
			$dst = $dir_img.'gift'.$_POST['aid'].'_'.$_POST['pid'].'_'.$msec.'.'.$types;
			if(move_uploaded_file($_FILES['gift']['tmp_name'],$dst)) {
				$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
				$data['gift_file'] = $path;
			} 
		}
		unset($_FILES['gift']);

		if( $_POST['type'] == 4 && $_POST['couponid']){
			$data['gift_file'] = $_POST['couponid'];
		}
		
		if( $_POST['type'] == 5 && !empty($_POST['code']) ) {
			$gift_file = explode(',', $_POST['code']);
			foreach ($gift_file as $k => $v) {
				$gift_val[] = explode(':', $v);
			}
			$gift_data = array();
			foreach ($gift_val as $kk => $vv) {
				if( $kk == 0 ){
					$tmp = $vv[0];
					$gift_data[$vv[0]][] = $vv[1];
				}else {
					if( $vv[0] == $tmp ) {
						$gift_data[$vv[0]][] = $vv[1];
					}else {
						$tmp = $vv[0];
						$gift_data[$vv[0]][] = $vv[1];
					}
				}
			}
			$data['pkg_path'] = $_POST['pkg_path'];
			$data['gift_file'] = json_encode($gift_data);
		}

		$time = time();
		if($_POST['pid']){
			$data['create_tm'] = $time;
		}else{
			$data['create_tm'] = $time;
			$data['update_tm'] = $time;
		}		
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return; 		
	}
	//充值类活动---设置中奖概率
	function save_probability($data){
		$where = array(
			'pid' => $data['pid'],
			'aid' => $data['aid'],
		);
		$res = $this -> table('gm_probability') -> where($where) -> find();
		$time = time();
		if($res){
			$data['update_tm'] = $time;
			$ret = $this -> table('gm_probability') -> where($where) -> save($data);
		}else{
			$data['create_tm'] = $time;
			$data['update_tm'] = $time;
			$ret = $this -> table('gm_probability') -> add($data);
		}		
		return $ret;
	}
	//刷礼包缓存
	function brush_gift_cache($send_data){
		$task_client = get_task_client();	
		$task_client->doBackground("ranking_gift_work", json_encode($send_data));
	}	
	//订制活动添加、编辑
	public function custom_award_add_do(){
		//表单验证
		if(!$_POST['name']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品名称",
			);
			return $return;		
		}
		if(!$_POST['level']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品等级",
			);
			return $return;	
		}else if($_POST['level'] <= 0  || $_POST['level'] > 100 ){
			$return = array(
				'code' => 0,
				'msg' => "奖品等级不能小于1和大于100",
			);
			return $return;				
		}
		if($_POST['prize_num'] == ''){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品数量",
			);
			return $return;				
		}else if($_POST['prize_num'] < 0 || $_POST['prize_num'] > 1000000 ){
			$return = array(
				'code' => 0,
				'msg' => "奖品数量错误范围（0-1000000）",
			);
			return $return;					
		}				
		if(!$_POST['pid'] && $_POST['type'] == 2 && empty($_FILES['gift']['tmp_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请上传礼包",
			);
			return $return;				
		}else if($_POST['pid'] && $_POST['type'] == 2 && empty($_FILES['gift']['tmp_name'])){
			$where = array('id' => $_POST['pid']);
			$res = $this -> table('valentine_draw_prize') -> where($where)->field('gift_file') -> find();
			if(!$res['gift_file']){
				$return = array(
					'code' => 0,
					'msg' => "请上传礼包",
				);
				return $return;	
			}
		}else if(($_POST['type'] == 4 || $_POST['type'] == 5) && empty($_POST['couponid'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写礼券ID",
			);
			return $return;		
		} 	
		$where = array(
			'aid' => $_POST['aid'],
			'status' => 1,
			'level' => $_POST['level']
		);
		if($_POST['pid']){
			$where['id'] = array('exp',"!={$_POST['pid']}");
		}		
		$have_level = $this -> table('valentine_draw_prize') -> where($where) -> find();
		//echo $this->getlastsql();
		if($have_level){
			$return = array(
				'code' => 0,
				'msg' => "该活动已包含此等级奖品",
			);
			return $return;				
		}
		$desc = trim($_POST['desc']);	
		$data = array(
			'name' => trim($_POST['name']),
			'type' => $_POST['type'],
			'level' => $_POST['level'],
			'aid' => $_POST['aid'],
			'num' => $_POST['prize_num'],
			'prize_num' => $_POST['prize_num'],
			'desc' => str_replace(array("\r\n","\n"),'',$desc),
		);	
		if(($_POST['type'] == 4 || $_POST['type'] == 5) && !empty($_POST['couponid'])){
			$data['gift_file'] = $_POST['couponid'];
		}		
		if($_FILES['gift']['tmp_name']){
			$array = array('csv');
			$ytypes = $_FILES['gift']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			if(!in_array($type,$array)){
				$return = array(
					'code' => 0,
					'msg' => "上传格式错误",
				);
				return $return;						
			}		
			//验证礼包重复数据
			$data_file = file_get_contents($_FILES['gift']['tmp_name']);
			//判断是否是utf-8编辑
			if(mb_check_encoding($data_file,"utf-8") != true){
				$data_file = iconv("gbk","utf-8", $data_file);
			}
			$data_file = str_replace("\r\n","\n",$data_file);	
			$data_arr = explode("\n", $data_file);
			$newarr = array();
			$str = '礼包重复数据：';
			$str2 = '';
			$gift_status = '';
			foreach($data_arr as $k=>$v){
				if($k == 0 || empty($v)){
					continue;
				}
				list($softname,$pkg,$num) = explode(',',$v);
				if(!$num){
					$gift_status .= $k."行\n";
					continue;
				}
				if($newarr[$num]){
					$str .= $num."\n";
				}else{
					$newarr[$num] = 1;
				}
				if(strpos($num,".")){
					$str2 .= "该礼包【有点】礼包码为：".$num;
				}				
			}		
			if($gift_status != ''){
				$return = array(
					'code' => 0,
					'msg' => '礼包码第'.$gift_status.'出错，请认真检查',
				);
				return $return;	
			}			
			if($str != '礼包重复数据：'){
				$return = array(
					'code' => 0,
					'msg' => $str,
				);
				return $return;					
			}			
			if($str2 != ''){
				$return = array(
					'code' => 0,
					'msg' => $str2,
				);
				return $return;					
			}	
			//上传礼包
			$date = date("Ym/d/");
			$dir_img = C('ACTIVITY_CSV') . '/gift/'.$date;
			if(!is_dir($dir_img)) {
				if(!mkdir($dir_img,0777,true)) {
					//创建gift目录{$dir_img}失败
					$return = array(
						'code' => 0,
						'msg' => "创建gift目录{$dir_img}失败",
					);
					return $return;				
				}
			}
			list($msec,$sec) = explode(' ',microtime());
			$types = 'csv';
			$msec = substr($msec,2);
			$dst = $dir_img.'gift'.$_POST['aid'].'_'.$_POST['pid'].'_'.$msec.'.'.$types;
			if(move_uploaded_file($_FILES['gift']['tmp_name'],$dst)) {
				$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
				$data['gift_file'] = $path;
			} 
		}
		unset($_FILES['gift']);
		$time = time();
		if($_POST['pid']){
			$data['add_tm'] = $time;
		}else{
			$data['add_tm'] = $time;
			$data['update_tm'] = $time;
		}		
		$return = array(
			'code' => 1,
			'data' => $data,
		);
		return $return; 		
	}	
	//刷订制活动礼包缓存
	function brush_custom_gift_cache($send_data){
		$task_client = get_task_client();	
		$task_client->doBackground("custom_gift_work", json_encode($send_data));
	}	
	
	public function award_add_do($table){
		//表单验证
		if(!$_POST['name']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品名称",
			);
			return $return;		
		}
		if(!$_POST['level']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品等级",
			);
			return $return;	
		}
		if(!$_POST['prize_num']){
			$return = array(
				'code' => 0,
				'msg' => "请填写奖品数量",
			);
			return $return;				
		}		
		if(!$_POST['pid'] && empty($_FILES['pic_path']['tmp_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请上传奖品图片",
			);
			return $return;				
		}	
		if(!$_POST['pid'] && $_POST['type'] == 2 && empty($_FILES['gift']['tmp_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请上传礼包",
			);
			return $return;				
		}else if($_POST['pid'] && $_POST['type'] == 2 && empty($_FILES['gift']['tmp_name'])){
			$where = array('pid' => $_POST['pid']);
			$res = $this -> table($table) -> where($where)->field('gift_file') -> find();
			if(!$res['gift_file']){
				$return = array(
					'code' => 0,
					'msg' => "请上传礼包",
				);
				return $return;	
			}
		}else if($_POST['type'] == 4 && empty($_POST['couponid'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写礼券ID",
			);
			return $return;	
		}	
		$where = array(
			'aid' => $_POST['aid'],
			'status' => 1,
			'level' => $_POST['level']
		);
		if($_POST['pid']){
			$where['id'] = array('exp',"!={$_POST['pid']}");
		}		
		$have_level = $this -> table($table) -> where($where) -> find();
		//echo $this->getlastsql();
		if($have_level){
			$return = array(
				'code' => 0,
				'msg' => "该活动已包含此等级奖品",
			);
			return $return;				
		}	
		if($_POST['lottery_style'] == 1){
			$width = 80;
			$height = 128;
		}elseif($_POST['lottery_style'] == 2){
			$width = 100;
			$height = 100;
		}elseif($_POST['lottery_style'] == 3){
			$width = 60;
			$height = 78;
		}
		$date = date("Ym/d/");
		if($_FILES['pic_path']['tmp_name']){
			$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
					'code' => 0,
					'msg' => "分辨率图标大小不符合条件",
				);
				return $return;
			}
			$config['multi_config']['pic_path'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $date,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		$desc = trim($_POST['desc']);	
		$data = array(
			'name' => trim($_POST['name']),
			'type' => $_POST['type'],
			'level' => $_POST['level'],
			'desc' => str_replace(array("\r\n","\n"),'',$desc),
			'aid' => $_POST['aid'],
			'num' => $_POST['prize_num'],
			'prize_num' => $_POST['prize_num'],
		);		
		if($_FILES['gift']['tmp_name']){
			$array = array('csv');
			$ytypes = $_FILES['gift']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			if(!in_array($type,$array)){
				$return = array(
					'code' => 0,
					'msg' => "上传格式错误",
				);
				return $return;						
			}		
			//验证礼包重复数据
			$data_file = file_get_contents($_FILES['gift']['tmp_name']);
			//判断是否是utf-8编辑
			if(mb_check_encoding($data_file,"utf-8") != true){
				$data_file = iconv("gbk","utf-8", $data_file);
			}
			$data_file = str_replace("\r\n","\n",$data_file);	
			$data_arr = explode("\n", $data_file);
			$newarr = array();
			$str = '礼包重复数据：';
			$str2 = '';
			$gift_status = '';
			foreach($data_arr as $k=>$v){
				if($k == 0 || empty($v)){
					continue;
				}
				list($softname,$pkg,$num) = explode(',',$v);
				if(!$num){
					$gift_status .= $k."行\n";
					continue;
				}
				if($newarr[$num]){
					$str .= $num."\n";
				}else{
					$newarr[$num] = 1;
				}
				if(strpos($num,".")){
					$str2 .= "该礼包【有点】礼包码为：".$num;
				}					
			}	
			if($gift_status != ''){
				$return = array(
					'code' => 0,
					'msg' => '礼包码第'.$gift_status.'出错，请认真检查',
				);
				return $return;	
			}
			if($str != '礼包重复数据：'){
				$return = array(
					'code' => 0,
					'msg' => $str,
				);
				return $return;					
			}	
			if($str2 != ''){
				$return = array(
					'code' => 0,
					'msg' => $str2,
				);
				return $return;					
			}				
			//上传礼包
			$dir_img = C('ACTIVITY_CSV') . '/gift/'.$date;
			if(!is_dir($dir_img)) {
				if(!mkdir($dir_img,0777,true)) {
					//创建gift目录{$dir_img}失败
					$return = array(
						'code' => 0,
						'msg' => "创建gift目录{$dir_img}失败",
					);
					return $return;				
				}
			}
			list($msec,$sec) = explode(' ',microtime());
			$types = 'csv';
			$msec = substr($msec,2);
			$dst = $dir_img.'gift'.$_POST['aid'].'_'.$_POST['pid'].'_'.$msec.'.'.$types;
			if(move_uploaded_file($_FILES['gift']['tmp_name'],$dst)) {
				$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
				$data['gift_file'] = $path;
			} 
		}
		if($_POST['type'] == 4 && $_POST['couponid']){
			$data['gift_file'] = $_POST['couponid'];
		}
		unset($_FILES['gift']);
		$time = time();
		if($_POST['pid']){
			$data['add_tm'] = $time;
		}else{
			$data['add_tm'] = $time;
			$data['update_tm'] = $time;
		}		
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return; 		
	}

	//刷共用礼包缓存
	function brush_gift_redis($send_data){
		$task_client = get_task_client();	
		$task_client->doBackground("activity_gift_worker", json_encode($send_data));
	}
	
	//签到模板 奖品列表
	public function sign_award_add_do($table){
		//表单验证
		if(!$_POST['name']){
			$return = array(
					'code' => 0,
					'msg' => "请填写奖品名称",
			);
			return $return;
		}
		if(!$_POST['level']){
			$return = array(
					'code' => 0,
					'msg' => "请填写奖品等级",
			);
			return $return;
		}
		if(!$_POST['prize_num']){
			$return = array(
					'code' => 0,
					'msg' => "请填写奖品数量",
			);
			return $return;
		}
		if(!$_POST['id'] && empty($_FILES['pic_path']['tmp_name'])){
			$return = array(
					'code' => 0,
					'msg' => "请上传奖品图片",
			);
			return $return;
		}
		if($_POST['type'] == 4 && empty($_POST['couponid'])){
			$return = array(
					'code' => 0,
					'msg' => "请填写礼券ID",
			);
			return $return;
		}elseif( !$_POST['id'] && $_POST['type'] == 5 && empty($_POST['code']) ){
			$return = array(
					'code' => 0,	
					'msg' => "有未上传包名或文选择礼包",
			);
			return $return;
		}
		$where = array(
				'aid' => $_POST['aid'],
				'status' => 1,
				'level' => $_POST['level']
		);
		if($_POST['id']){
			$where['id'] = array('exp',"!={$_POST['id']}");
		}
		$have_level = $this -> table($table) -> where($where) -> find();
		if($have_level){
			$return = array(
					'code' => 0,
					'msg' => "该活动已包含此等级奖品",
			);
			return $return;
		}
		
		if($_POST['lottery_style'] == 1){
			$width = 80;
			$height = 128;
		}elseif($_POST['lottery_style'] == 2){
			$width = 100;
			$height = 100;
		}elseif($_POST['lottery_style'] == 3){
			$width = 60;
			$height = 78;
		}
		$date = date("Ym/d/");
		if($_FILES['pic_path']['tmp_name']){
			if($_POST['lottery_style'] == 1 || $_POST['lottery_style'] == 2){
				$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
				if($pic_path[0] != $width || $pic_path[1] != $height){
					$return = array(
							'code' => 0,
							'msg' => "分辨率图标大小不符合条件",
					);
					return $return;
				}
			}
			$config['multi_config']['pic_path'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $date,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
			);
		}
		$desc = trim($_POST['desc']);
		$data = array(
				'name'	=>	trim($_POST['name']),
				'type'	=>	$_POST['type'],
				'level'	=>	$_POST['level'],
				'desc'	=>	str_replace(array("\r\n","\n"),'',$desc),
				'aid'	=>	$_POST['aid'],
				'num'	=>	$_POST['prize_num'],
				'prize_num' => $_POST['prize_num'],
		);

		if( $_POST['type'] == 4 && $_POST['couponid']){
			$data['gift_file'] = $_POST['couponid'];
		}
		
		if( $_POST['type'] == 5 && !empty($_POST['code']) ) {
			$gift_file = explode(',', $_POST['code']);
			foreach ($gift_file as $k => $v) {
				$gift_val[] = explode(':', $v);
			}
			$gift_data = array();
			foreach ($gift_val as $kk => $vv) {
				if( $kk == 0 ){
					$tmp = $vv[0];
					$gift_data[$vv[0]][] = $vv[1];
				}else {
					if( $vv[0] == $tmp ) {
						$gift_data[$vv[0]][] = $vv[1];
					}else {
						$tmp = $vv[0];
						$gift_data[$vv[0]][] = $vv[1];
					}
				}
			}
			$data['pkg_path'] = $_POST['pkg_path'];
			$data['gift_file'] = json_encode($gift_data);
		}
		
		$time = time();
		if($_POST['id']){
			$data['add_tm'] = $time;
		}else{
			$data['add_tm'] = $time;
			$data['update_tm'] = $time;
		}
		
		$return = array(
				'code' => 1,
				'data' => $data,
				'config' => $config,
		);
		return $return;
	}
	
	//签到管理
	public function sign_template_add_do($table){
		//表单验证
		if( !$_POST['update_tm'] && empty($_FILES['icon_in']['tmp_name'])){
			$return = array(
					'code' => 0,
					'msg' => "请上传已签到icon",
			);
			return $return;
		}
		if(!$_POST['update_tm'] && empty($_FILES['icon_not_in']['tmp_name'])){
			$return = array(
					'code' => 0,
					'msg' => "请上传未签到icon",
			);
			return $return;
		}
		if( !$_POST['update_tm'] && empty($_FILES['icon_be_over']['tmp_name'])){
			$return = array(
					'code' => 0,
					'msg' => "请上传已过期icon",
			);
			return $return;
		}
		if( !$_POST['update_tm'] && empty($_FILES['icon_not_start']['tmp_name'])){
			$return = array(
					'code' => 0,
					'msg' => "请上传未过期icon",
			);
			return $return;
		}
		if($_POST['is_telephone'] == 1){
			$width = 68;
			$height = 68;
		}else{
			$width = 125;
			$height = 125;
		}
 		$date = date("Ym/d/");
 		$icon_name = array(
 				'icon_in'			=>	'已签到icon',
 				'icon_not_in'		=>	'未签到icon',
 				'icon_be_over'		=>	'已过期签到icon',
 				'icon_not_start'	=>	'未开始签到icon',
 		); 
 		$config = array();
 		if( !empty($_FILES) ) {
 			foreach ($_FILES as $k => $v)
	 			if($v['tmp_name']) {
	 				$pic_path = getimagesize($v['tmp_name']);
	 				if($pic_path[0] != $width || $pic_path[1] != $height){
	 					$return = array(
	 							'code' => 0,
	 							'msg' => "{$icon_name[$k]}分辨率图标大小不符合条件",
	 					);
	 					return $return;
	 				}
	 				$config['multi_config'][$k] = array(
	 						'savepath' => UPLOAD_PATH. '/img/'. $date,
	 						'saveRule' => 'getmsec',
	 						'img_p_size' => 1024 * 200,
	 				);
	 			}
 		}
		$data = array(
				'name'	=>	trim($_POST['name']),
				'type'	=>	$_POST['type'],
				'num'	=>	$_POST['prize_num'],
				'prize_num' => $_POST['prize_num'],
		);
		if( ($_POST['type'] == 4 || $_POST['type'] == 5) && $_POST['couponid']){
			$data['gift_file'] = $_POST['couponid'];
		}
		$time = time();
		if($_POST['update_tm']){
			$data['add_tm'] = $time;
		}else{
			$data['add_tm'] = $time;
			$data['update_tm'] = $time;
		}
		$return = array(
				'code' => 1,
				'data' => $data,
				'config' => $config,
		);
		return $return;
	}
	
	function httpGet($val)
	{
		if(C('is_test') == 1){
			$url = "http://dev.heifer.anzhi.com/giftBagManage/getCommonGift?params=";
		}else {
			$url = "http://heifer.anzhi.com/giftBagManage/getCommonGift?params=";
		}
		$param  = is_array($val) ? implode(',', $val) : $val;
		$newUrl = $url.$param;
		$result = httpGetInfo($newUrl, '', 'heifer.log');
		$result = json_decode($result, true);
		return $result;
	}
	//排行榜测试数据删除
	function ranking_test_data_del($aid){
		$cli = "/usr/local/bin/redis-cli -h";
		if(C('is_test') == 1){
			$ip = '192.168.1.242';
			$port = 6379;	
		}else{
			$ip = '192.168.1.151';
			$port = 6380; 					
		}		
		shell_exec("{$cli} {$ip} -p {$port}  keys 'ranking:{$aid}*' | xargs {$cli} {$ip}  -p {$port} DEL");
		shell_exec("{$cli} {$ip} -p {$port}  keys 'ranking_*{$aid}' | xargs {$cli} {$ip}  -p {$port} DEL");
		shell_exec("{$cli} {$ip} -p {$port}  keys 'ranking_draw_num:{$aid}*' | xargs {$cli} {$ip}  -p {$port} DEL");
		//shell_exec("{$cli} {$ip} -p {$port}  keys  'ranking_award_all_count{$aid}' | xargs {$cli} {$ip}  -p {$port} DEL");
		$where = array('aid' => $aid);
		$this -> table('ranking_userinfo') -> where($where) -> delete();
		$this -> table('gm_lottery_award') -> where($where) -> delete();
		$map = array('now_num'=>array('exp',"`upperlimit`"));
		$this -> table('gm_probability') -> where($where) -> save($map);
		$where['status'] = 1; 
		$virtual_prize =  $this -> table('gm_virtual_prize') -> where($where) ->group('pid')-> select();
		if($virtual_prize){	
			$this -> table('gm_virtual_prize') -> where($where) -> delete();
			$task_client = get_task_client();
			foreach($virtual_prize as $v){
				$send_data = array(
					'pid' => $v['pid'],
					'aid' => $aid,
				);
				$task_client->doBackground("ranking_gift_work", json_encode($send_data));
			}
		}		
	}
	//测试数据删除
	function test_data_del($aid,$prefix){
		$cli = "/usr/local/bin/redis-cli -h";
		if(C('is_test') == 1){
			$ip = '192.168.1.242';
			$port = 6379;	
		}else{
			$ip = '192.168.1.151';
			$port = 6380; 					
		}		
		shell_exec("{$cli} {$ip} -p {$port}  keys '{$prefix}:{$aid}*' | xargs {$cli} {$ip}  -p {$port} DEL");
		$where = array('aid' => $aid);
		if($prefix == 'sign'){
			$this -> table('sign_userinfo') -> where($where) -> delete();
			$this -> table('sign_award') -> where($where) -> delete();
			$this -> table('sign_user_data') -> where($where) -> delete();
			$map = array('num'=>array('exp',"`prize_num`"));
			$this -> table('sign_prize') -> where($where) -> save($map);	
			$this -> table('sign_prize_icon') -> where($where) -> save($map);	
		}else if($prefix = 'pre_down_operation'){
			$this -> table('pre_down_operation_userinfo') -> where($where) -> delete();
			$this -> table('pre_down_operation_award') -> where($where) -> delete();
			$map = array('num'=>array('exp',"`prize_num`"));
			$where['type'] = array("exp","!=2");
			$this -> table('pre_down_operation_prize') -> where($where) -> save($map);
			
		}		
	}
	//导出后台活动礼包
	function export_acrivity_gift($type,$aid,$pid){
		if($type == 1){
			$pkg_key = "custom:{$aid}:{$pid}_gift_pkg";
			$gift_key = "custom:{$aid}:{$pid}_gift:*";
			$field = "softname,package,gift_number";
		}else if($type == 5){
			$gift_key = 'ranking_gift_'.$aid.$pid;
			$field = "second_text as softname,third_text as package,first_text as gift_number";
		}else if(in_array($type,array(2,6))){
			$pkg_key = "general_lottery:package_{$pid}";
			$gift_key = "general_lottery:virtual_*_{$pid}";
			$field = "second_text as softname,third_text as package,first_text as gift_number";
		}else if(in_array($type,array(7,8))){
			$pkg_key = "activity_gift:{$aid}:{$pid}:pkg";
			$gift_key = "activity_gift:{$aid}:{$pid}:gift_num:*";
			$field = "softname,package,gift_number";
		}
		
		$cli = "/usr/local/bin/redis-cli -h";
		if(C('is_test') == 1){
			$ip = '192.168.1.242';
			$port = 6379;	
		}else{
			$ip = '192.168.1.151';
			$port = 6380; 					
		}
		if($pkg_key){	
			shell_exec("{$cli} {$ip} -p {$port}  keys {$pkg_key} | xargs {$cli} {$ip}  -p {$port} DEL");		
		}
		if($gift_key){
			shell_exec("{$cli} {$ip} -p {$port}  keys {$gift_key} | xargs {$cli} {$ip}  -p {$port} DEL");
		}
		$tab_con = array(
			1 => 'valentine_draw_gift', //定制
			2 => 'gm_virtual_prize',//通用
			5 => 'gm_virtual_prize',//充值消费类活动
			6 => 'gm_virtual_prize',//新通用预约活动
			7 => 'pre_down_operation_gift',//运营预下载活动
			8 => 'sign_gift',//签到模板
		);		
		$where = array(
			'aid' => $aid,
			'pid' => $pid,
			'status' => 0
		);
		$map = array('status' => 3);
		$this -> table($tab_con[$type]) -> where($where) -> save($map);
		$where['status'] = 3;
		$list = $this -> table($tab_con[$type]) -> where($where)->field($field) -> select();
		if($type == 2 || $type == 6){
			$str = "礼包码,游戏名称,包名 \r\n";
		}else{
			$str = "游戏名称,包名,礼包码 \n";
		}
		foreach($list as $key => $val){
			if($type == 2 || $type == 6){
				$str .= $val['gift_number'].",".$val['softname'].",".$val['package']."\r\n";
			}else{
				$str .= $val['softname'].",".$val['package'].",".$val['gift_number']."\n";
			}
		}
		$file_pach = "/tmp/export_gift_".$aid."_".$pid.".csv";
		file_put_contents($file_pach,mb_convert_encoding($str,"gbk","utf-8"));
		//下载
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' ."export_gift_".$aid."_".$pid. '.csv"');
		header('Cache-Control: max-age=0');
		readfile($file_pach);exit;		
	}
	function add_no_win($aid,$activity_type_bank){
		$data = array(
			'level' => 8,
			'name' => '谢谢参与',
			'aid' => $aid,
			'type' => 3,
			'pic_path' => '/img/201505/28/00960300.jpg',
		);
		$arr =  array(2,5,6);
		if(in_array($activity_type_bank,$arr)){
			$data['create_tm'] = time();
		}else{
			$data['add_tm'] = time();
			$data['prize_num'] = 10000;
			$data['num'] = 10000;
		}
		if(in_array($activity_type_bank,$arr)){
			//通用、充值消费类活动、新预约
			$this -> table('gm_lottery_prize') -> add($data);
		}else if($activity_type_bank == 7){
			//运营预下载
			$this -> table('pre_down_operation_prize') -> add($data);
		}else if($activity_type_bank == 8){
			//签到
			$this -> table('sign_prize') -> add($data);
		}
		
	}	
	//删除活动礼包缓存
	function del_acrivity_gift_redis($aid,$pid){
		if(C('is_test') == 1 ){
			$ip = '192.168.1.242';
			$port = 6379;	
		}else{
			$ip = '192.168.1.151';
			$port = 6380; 					
		}
		$prefix = "activity_gift";	
		shell_exec("/usr/local/bin/redis-cli -h {$ip} -p {$port}  keys  '{$prefix}:{$aid}:{$pid}*' | xargs /usr/local/bin/redis-cli -h {$ip}  -p {$port} DEL");		
	}
}
