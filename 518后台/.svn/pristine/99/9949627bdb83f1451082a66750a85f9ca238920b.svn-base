<?php
class AdsdkModel extends Model{
	public function get_ad_exit_list($where){
		import('@.ORG.Page2');
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;	
		$param = http_build_query($_GET);
		$time = time();
		if($_GET['type'] == 1){
			$where['ad_start_tm'] = array('gt',$time);
		}else if($_GET['type'] == 2){
			$where['ad_start_tm'] = array('lt',$time);
			$where['ad_end_tm'] = array('gt',$time);
		}else if($_GET['type'] == 3){
			$where['ad_end_tm'] = array('lt',$time);
		}
		$total = $this->table('sj_exit_ad')->where($where)->count();
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','条记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		
		$result = $this->table('sj_exit_ad')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('priority asc,ad_start_tm asc')->select();
		foreach($result as $k => $v){
			$result[$k]['ad_start_tm'] = $v['ad_start_tm'] ? date('Y-m-d H:i', $v['ad_start_tm']) : '';
			$result[$k]['ad_end_tm'] = $v['ad_end_tm'] ? date('Y-m-d H:i', $v['ad_end_tm']) : '';
			
		}
		return array($result,$total,$Page);
	}
	//用户名
	public function get_user_code(){
		$array = array('csv');
		$ytypes = $_FILES['user_file']['name'];
		$info = pathinfo($ytypes);
		$type =  $info['extension'];//获取文件件扩展名
		$error = '';
		if(!in_array($type,$array)){
			$error .= "上传格式错误\n";
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{		
			$tmp_name = $_FILES['user_file']['tmp_name'];
			$data_arr = $this -> get_file_data($tmp_name);
			$list_arr = array();
			foreach($data_arr as $v){
				if($list_arr[$v]){
					$error .= $v.";有重复数据\n"; 
					continue;
				}	
				$list_arr[$v] = 1;
			}
			if($error !=''){
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}
			list($msec,$sec) = explode(' ',microtime());
			$msec = substr($msec,2);
			$file_str = "/tmp/$msec.csv";
			if(!move_uploaded_file($tmp_name,$file_str)){
				$error .= "上传出错\n";
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}else{
				exit(json_encode(array('code'=>1,'msg'=>$file_str)));
			}
		}
	}
	//sdk游戏名称
	public function get_sdk_pkg_code(){
		$array = array('csv');
		$ytypes = $_FILES['sdk_game_file']['name'];
		$info = pathinfo($ytypes);
		$type =  $info['extension'];//获取文件件扩展名
		$error = '';
		if(!in_array($type,$array)){
			$error .= "上传格式错误\n";
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{	
			$tmp_name = $_FILES['sdk_game_file']['tmp_name'];
			$data_arr = $this -> get_file_data($tmp_name);
			//获取sdk接入的数据
			$where = array(
				'sdk_test_status' => 1,
				'package' => array('in',$data_arr)
			);
			$list  = get_table_data($where,"sj_sdk_info","package","package");
			//软件名称
			$where = array(
				'status' => 1,
				//'hide' => 1,
				'package' => array('in',$data_arr)
			);
			$soft_list  = get_table_data($where,"sj_soft","package","package,softname,hide");
			$err = '';
			$softname = '';
			$softname_err = '';
			foreach($data_arr as $v){
				if(empty($v)) continue;
				if(!$list[$v]) $err .= $v."\n";
				if($soft_list[$v]['hide'] == 1){
					if($soft_list[$v]) $softname .= $soft_list[$v]['softname'].";";
				}else{
					if($soft_list[$v]) $softname_err .= $soft_list[$v]['package'].";";
				}
			}
			if($err !=''){
				exit(json_encode(array('code'=>0,'msg'=>$err."没接入sdk")));
			}
			list($msec,$sec) = explode(' ',microtime());
			$msec = substr($msec,2);
			$file_str = "/tmp/$msec.csv";
			if(!move_uploaded_file($tmp_name,$file_str)){
				$error .= "上传出错\n";
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}else{
				exit(json_encode(array('code'=>1,'file_path'=>$file_str,'softname'=>$softname,'softname_err'=>$softname_err)));
			}
		}
	}
	//广告图片
	public function get_ad_picture_code(){
		$tmp_name = $_FILES['ad_picture']['tmp_name'];
		$array = array('jpg','jpeg','png','gif');
		$ytypes = $_FILES['ad_picture']['name'];
		$info = pathinfo($ytypes);
		$type =  $info['extension'];//获取文件件扩展名
		$error = '';
		if(!in_array($type,$array)){
			exit(json_encode(array('code'=>0,'msg'=> "上传格式错误")));
		}		
		$image_file = getimagesize($tmp_name);
		if($image_file[0] != 384 || $image_file[1] != 146){
			exit(json_encode(array('code'=>0,'msg'=>"图片尺寸不正确")));
		}
		list($msec,$sec) = explode(' ',microtime());
		$msec = substr($msec,2);
		$file_str = "/tmp/$msec.".$type;
		if(!move_uploaded_file($tmp_name,$file_str)){
			$error .= "上传出错\n";
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{
			exit(json_encode(array('code'=>1,'file_path'=>$file_str)));
		}		
	}

	//软件包名
	public function get_pkg_code(){
		$array = array('csv');
		$ytypes = $_FILES['pkg_path']['name'];
		$info = pathinfo($ytypes);
		$type =  $info['extension'];//获取文件件扩展名
		$error = '';
		if(!in_array($type,$array)){
			$error .= "上传格式错误\n";
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{	
			$tmp_name = $_FILES['pkg_path']['tmp_name'];
			$data_arr = $this -> get_file_data($tmp_name);
			//软件名称
			$where = array(
				'status' => 1,
				'hide' => 1,
				'package' => array('in',$data_arr)
			);
			$list  = get_table_data($where,"sj_soft","package","package,softname");
			$err = '';
			$err2 = '';
			$softname = '';
			$pkg_num = 0;
			$list_arr = array();
			foreach($data_arr as $v){
				if(empty($v)) continue;
				if(!$list[$v]){
					$err .= $v."\n";
					continue;
				}
				if($list_arr[$v]){
					$err2 .= $v."\n"; 
					continue;
				}	
				$list_arr[$v] = 1;
				$pkg_num++;
			}
			if($err !=''){
				exit(json_encode(array('code'=>0,'msg'=>$err."不在上架列表")));
			}
			if($err2 !=''){
				exit(json_encode(array('code'=>0,'msg'=>$err2."有重复数据")));
			}
			if($pkg_num < 4) exit(json_encode(array('code'=>0,'msg'=>"最少上传4个有效包名以上")));
			list($msec,$sec) = explode(' ',microtime());
			$msec = substr($msec,2);
			$file_str = "/tmp/$msec.csv";
			if(!move_uploaded_file($tmp_name,$file_str)){
				$error .= "上传出错\n";
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}else{
				exit(json_encode(array('code'=>1,'file_path'=>$file_str,'pkg_num'=>$pkg_num)));
			}
		}
	}	
	//提交添加退出广告
	public function post_add_exit(){
		$data = array(
			'ad_start_tm' => strtotime($_POST['begintime']),
			'ad_end_tm' => strtotime($_POST['endtime']),
			'ad_name' => $_POST['ad_name'],
			'priority' => $_POST['priority'],
		);
		//用户名
		$tmp_user = explode('/',$_POST['user_file_path']);
		$path= "/".date('Ym/d').'/';
		$dir = C("MARKET_PUSH_CSV").$path;
		if($tmp_user[1] == 'tmp'){		
			$tmp_dir = $_POST['user_file_path'];
			//$dir = UPLOAD_PATH .'/sdk/sdk_user/'.date('Ym/d').'/';
			$newfile = $this -> cp_sdk_ad_file($dir,$tmp_dir,'.csv','user_name_path');
			$data['user_name_path'] = str_replace(UPLOAD_PATH,'',$newfile);
		}
		//游戏名称上传
		$tmp_sdk_pkg = explode('/',$_POST['sdk_game_file_path']);
		if($tmp_sdk_pkg[1] == 'tmp'){
			$tmp_dir = $_POST['sdk_game_file_path'];
			//$dir = UPLOAD_PATH .'/files/'.date('Ym/d').'/';
			$newfile_sdk = $this -> cp_sdk_ad_file($dir,$tmp_dir,'.csv','game_name_path');
			$data['game_name_path'] = $newfile_sdk;
		}
		if($_POST['ad_type'] == 1){
			$data['ad_picture'] = $_POST['ad_picture_file_path'];
			$data['default_open'] = $_POST['default_open'] ? $_POST['default_open'] : 1;
			
			
			$tmp_ad_picture = explode('/',$_POST['ad_picture_file_path']);
			if($tmp_ad_picture[1] == 'tmp'){
				$tmp_dir = $_POST['ad_picture_file_path'];
				$dir = UPLOAD_PATH .'/image/'.date('Ym/d').'/';
				$newfile = $this -> cp_sdk_ad_file($dir,$tmp_dir,'.jpg','ad_picture');
				$data['ad_picture'] = $newfile;
			}
			$data['suggests'] = $_POST['suggests'] ? $_POST['suggests'] : 1;
			
			//SDK版本 非必填 2017-1-12 
			$data['game_sdk_version_rule'] = $_POST['game_sdk_version_rule'];
			if($_POST['game_sdk_version_rule']=='>=')
			{
				$data['game_sdk_version_code'] = $_POST['game_sdk_version1'];
			}
			if($_POST['game_sdk_version_rule']=='<=')
			{
				$data['game_sdk_version_code'] = $_POST['game_sdk_version2'];
			}
			if($_POST['game_sdk_version_rule']=='==')
			{
				$data['game_sdk_version_code'] = trim($_POST['force_update_version'],',');
			}
		
			//$data['game_sdk_version_code'] 		= intval($_POST['game_sdk_version']);
			//$data['game_sdk_version_rule'] 	= in_array($_POST['game_sdk_version_rule'], array('==','>=','<=')) ? $_POST['game_sdk_version_rule'] : '==';
				
			if($_POST['suggests'] == 4) {
				$data['display_method'] 		= intval($_POST['display_method']);
			} else if($_POST['suggests'] == 7){
				$data['package'] = $_POST['package_new']; 
	        	
	        	$data['uninstall_setting'] = $_POST['uninstall_setting']; 
	        	
	        	$data['download_way'] = $_POST['download_way'];
	        	if($data['download_way']==1){
	        		$data['install_setting'] = $_POST['install_setting']; 
	        		$data['start_to_page'] = $_POST['start_to_page'];
	        	}else if($data['download_way']==2){
	        		$data['install_setting'] = ''; 
	        		$data['start_to_page'] = '';
	        	}
			}
			
			
			if($_POST['suggests'] ==2){//活动就写活动id
				$data['activity_id'] = $_POST['activity_id'];
			}else{//网页就填链接
				$data['ad_picture_link'] = $_POST['ad_picture_link'];
			}
		}else{
			$data['suggests'] = 3;
			$data['description'] = $_POST['description'];
			$data['pkg_path'] = $_POST['pkg_file_path'];
			$data['pkg_num'] = $_POST['pkg_num'];
			$tmp_pkg_file_path = explode('/',$_POST['pkg_file_path']);
			if($tmp_pkg_file_path[1] == 'tmp'){
				$tmp_dir = $_POST['pkg_file_path'];
				//$dir = UPLOAD_PATH .'/files/'.date('Ym/d').'/';
				$newfile_pkg = $this -> cp_sdk_ad_file($dir,$tmp_dir,'.csv','pkg_path');
				$data['pkg_path'] = $newfile_pkg;
			}
		}
		$data['within_3_user'] = $_POST['within_3_user'] ? $_POST['within_3_user'] : 0;
		if($_POST['within_3_user'] == 2){
			$data['select_user_type_ext'] = implode(',',$_POST['user_type_old']);
		}else if($_POST['within_3_user'] == 3){
			$data['select_user_type_ext'] = implode(',',$_POST['user_type_vip']);
		}
		if($_POST['within_3_user'] != 4){
			//如果不是上传用户的这个字段置空
			$data['user_name_path'] = '';
		}
		$data['game_name_type'] = $_POST['game_name_type'];
		if($_POST['game_name_type'] >=3){
			$data['ignore_package_list'] = $_POST['all_ignore'];
		}
		$data['cps_only'] = intval($_POST['cps_only']);
		
		if($_POST['id']){
			$ad_id = $_POST['id'];
			$data['update_tm'] = time();
			$ret = $this -> table('sj_exit_ad')->where("id={$_POST['id']}")->save($data);
		}else{
			$data['ad_type'] = $_POST['ad_type'];
			$data['min_firmware'] = $_POST['min_firmware'];
			$data['memory'] = $_POST['memory'];
			$data['resolution'] = $_POST['resolution'];
			$data['game_name'] = $_POST['sdk_game_name'];
			$data['create_tm'] = time();
			$ret = $this -> table('sj_exit_ad')->add($data);
			$ad_id = $ret;
		}
		file_put_contents('/tmp/sql.log',$this->getlastsql());			
		if($newfile_sdk){
			$this->ad_exit_pkg($newfile_sdk,1,$ad_id);
		}	
		if($newfile_pkg){
			$this->ad_exit_pkg($newfile_pkg,2,$ad_id);
		}		
		return $ret;
	}
	public function cp_sdk_ad_file($dir,$tmp_dir,$extension,$field){
		if(!is_dir($dir)) {
			if(!mkdir($dir,0777,true)) {
				//创建thumb_ori目录{$dir_thumb_ori}失败
				permanentlog($field.".log",date('Y-m-d H:i:s')."---".$dir."目录创建失败");
			}
		}
		list($msec,$sec) = explode(' ',microtime());
		$msec = substr($msec,2);
		$newfile = $dir.$msec.$extension;
		if (!copy($tmp_dir, $newfile)) {
			permanentlog($field.".log",date('Y-m-d H:i:s')."---".$tmp_dir."=>".$newfile."copy失败");
		}
		if($field == 'ad_picture'){
			$newfile = str_replace(UPLOAD_PATH,'',$newfile);	
		}else{
			$newfile = str_replace(C("MARKET_PUSH_CSV"),'',$newfile);	
		}
		return $newfile;
	}
	public function get_softname($type = ''){
		$pkg = trim($_GET['package']);
		$where = array(
			'status' => 1,
			'hide' => 1,
			'package' => $pkg,
		);
		$list = $this -> table('sj_soft') -> where($where) -> field('softname,package')-> find();
		if($type == 1) return $list;
		if($list){
			$res = $this -> table('sj_sdk_info') -> where("package='{$list['package']}' and sdk_test_status=1") -> field('package')-> find();
			if($res){
				exit(json_encode(array('code'=>1,'msg'=>$list['softname'])));
			}else{
				exit(json_encode(array('code'=>1,'msg'=>$pkg)));
			}
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$pkg)));
		}
	}
	//固件版本
	public function get_firmware(){
		$where = array(
			'status' => 1,
			'config_type' => 'firmware',
		);
		$firmware = get_table_data($where,"pu_config","configname","configname,configcontent");
		ksort($firmware);//对数组按照键名排序
		return $firmware;
	}
	//内存配置
	public function get_memory(){
		$memory = array(
			'1' => '128',
			'2' => '256',
			'3' => '288',
			'4'  => '384',
			'5'  => '512',
			'6'  => '768',
			'7'  => '1024',
			'8'  => '1536',
			'9'  => '2048',
			'10' => '3072',
		);
		return $memory;
	}
	//sdk退出广告包名
	public function ad_exit_pkg($file,$type,$ad_id){
		$path = C("MARKET_PUSH_CSV") . $file;
		if(file_exists($path)){
			$this -> get_file_data($path);
			$sql_str = '';
			foreach($data_arr as $v){
				if(empty($v)) continue;
				$sql_str .= ",({$ad_id},'{$v}','{$type}','1')";
			}
			$sql_str =  substr($sql_str,1);
			$sql = "INSERT INTO sj_exit_ad_pkg (`ad_id`,`package`,`type`,`status`) VALUES ".$sql_str;
			$res = $this -> query($sql);
			if($res && $type ==2){
				$where = array(
					'ad_id' => $ad_id,
					'status' => 1,
					'type' => $type
				);
				$map = array('status'=>0);
				$this -> table('sj_exit_ad_pkg')->where($where)->save($map);
			}
		}else{
			permanentlog("ad_exit_pkg.log",date('Y-m-d H:i:s').$path."文件不存在");
		}
	}
	//获取文件数据
	public function get_file_data($path){
		$data = file_get_contents($path);
		//判断是否是utf-8编辑
		if(mb_check_encoding($data,"utf-8") != true){
			$data = iconv("gbk","utf-8", $data);
		}
		$data = str_replace("\r\n","\n",$data);	
		$data_arr = explode("\n", $data);
		return $data_arr;
	}
	//文件获取软件名称
	public function file_get_softname($file){
		$path = C("MARKET_PUSH_CSV") . $file;
		$data = $this -> get_file_data($path);
		$where = array(
			'package'=>array('in',$data),
			'hide' => 1,
			'status'=>1
		);
		$ret = $this -> table('sj_soft') -> where($where) -> field('softname')-> select();
		$softname = '';
		foreach($ret as $v){
			$softname .= $v['softname']."；" ;
		}
		return $softname;
	}
	//验证同一时间段优先级
	public function get_priority_num(){
		$start = strtotime($_GET['begintime']);
		$end = strtotime($_GET['endtime']);
		$priority = $_GET['priority'];
		$id = $_GET['id'];
		$where = array(
			'ad_end_tm' => array('egt',$start),
			'ad_start_tm' => array('elt',$end),
			'priority' => $priority,
			'status' => 1,
			'id' => array('exp',"!={$id}")
		);
		$ret = $this->table('sj_exit_ad')->where($where)->find(); 
		if($ret){
			exit(json_encode(array('code'=>0)));
		}else{
			exit(json_encode(array('code'=>1)));
		}
	}
}