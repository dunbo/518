<?php

class ContentcooperationModel extends Model{

	//频道管理列表
	public function get_channel_list($where){
		$table = "coop_channel";
		import('@.ORG.Page2');
		$total = $this->table($table)->where($where)->count();
		$Page = new Page($total,100);	
		$order_str = "status desc,rank asc,id asc";
		$list = $this->table($table)->where($where)->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select(); 
		//计算卡片关联数据
		$id_arr = array();
		foreach($list as $v){
			$id_arr[] = $v['id'];
		}
		$where = array(
			'channle_id'=>array('in',$id_arr),
			'status'=> 1,
			'del' =>0
		);
		$count_list = $this->table("coop_card")->where($where)->group("channle_id")->field("count(channle_id) as counts,channle_id")->select();	
		$count_arr = array();
		foreach($count_list as $val){
			$count_arr[$val['channle_id']] = $val['counts'];
		}
		unset($count_list);		
		return array($list,$total,$Page,$count_arr);
	}
	//添加、更新频道
	public function save_channel(&$act){
		$table = "coop_channel";
		//渠道
		$time = time();
		$channel_id_array=$_POST['cid'];
		$cids = array_unique($channel_id_array);
		if (count($cids) > 0) {
			$s = implode(',', $cids);
			$s = ",{$s},";
		}
		$map = array(
			'cid' => $s ? $s : '' ,
			'site_id' => $_POST['site_id'],
			'rank' => $_POST['rank'],
			'channel_name' => $_POST['channel_name'],
			'status' => $_POST['status'],
			'common_jump_id'=>$_POST['common_jump_id']
		);		
		$id = $_POST['id'];
		$site_id_type=$this -> table('coop_site')->where(array('id'=>$_POST['site_id']))->find();
		if($site_id_type['type']==0 || $site_id_type['type']==3 || $site_id_type['type']==4 || $site_id_type['type']==5)
		{
			$map['type'] = $_POST['type'];
		}
		if($id){
			$where = array(
				'id' => $id,
				'del' => 0
			);
			$map['update_time'] = $time;
			$log = $act->logcheck($where, $table, $map, $this);	
			$affect = $this->table($table) -> where($where) -> save($map);
		}else{
			$map['create_time'] = $time;
			$map['update_time'] = $time;
			$affect = $this->table($table)->add($map);
		}
		return array($affect,$log);
	}
	//添加、更新合作闪屏管理
	public function save_coop_splash_screen(&$act){
		$table = "coop_splash_screen";
		//渠道
		$time = time();
		$channel_id_array=$_POST['cid'];
		$cids = array_unique($channel_id_array);
		if (count($cids) > 0) {
			$s = implode(',', $cids);
			$s = ",{$s},";
		}
		$map = array(
			'cid' => $s ? $s : '' ,
			'show_probability' => $_POST['show_probability'] ? $_POST['show_probability'] : '' ,
			'filter_area' => $_POST['filter_area'] ? $_POST['filter_area'] : '' ,
			'area' => $_POST['area_value'] ? $_POST['area_value'] : '' ,
		);	
		$id = $_POST['id'];
		if($id){
			$where = array(
				'id' => $id,
				'del' => 0
			);
			$map['update_time'] = $time;
			$log = $act->logcheck($where, $table, $map, $this);	
			$affect = $this->table($table) -> where($where) -> save($map);
		}
		return array($affect,$log);
	}
	//更改菜单配置
	public function save_meun_name(&$act){
		$where = array(
			'config_type' => 'coop_table_name',
			'status' => 1,
		);
		$config = $this -> table('pu_config') -> where($where)->field('configcontent')->find();
		if($config){
			$save = array(
				'configcontent' => $_GET['configcontent'],
				'uptime' => time(),
			);
			$log = $act->logcheck($where, 'pu_config', $save, $this);	
			$affect = $this -> table('pu_config')-> where($where) -> save($save);
		}else{
			$map = array(
				'config_type' => 'coop_table_name',
				'status' => 1,
				'configcontent' => $_GET['configcontent'],
			);
			$affect = $this-> table('pu_config') -> add($map);
		}
		return 	array($affect,$log);
	}
	//获取单条数据
	public function get_channel_find($id){
		$table = "coop_channel";
		$where = array(
			'id' => $id,
			'del' => 0
		);
		$ret = $this -> table($table) -> where($where)->find();
		return $ret;
	}
	//渠道数据
	public function get_cid_arr($cids){
		$cid_arr = explode(',', $cids);
		$table = "sj_channel";
		$field = "cid,chname";
		$where = array(
			'cid' => array('in',$cid_arr)
		);
		$chl = $this->table($table)->field($field)->where($where)->select();
		if (in_array("0",$cid_arr)){
		  $tong = array("cid"=> "0" ,"chname"=> "通用");
		  array_unshift($chl, $tong);
		}
		return 	$chl;
	}
	//卡片数据
	public function get_card_list($where){
		$table = "coop_card";
		import('@.ORG.Page2');
		$total = $this->table($table)->where($where)->count();
		$Page = new Page($total,100);	
		$order_str = "status desc,rank asc,id asc";
		$list = $this->table($table)->where($where)->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select(); 
		return array($list,$total,$Page);		
	}
	//频道数据
	public function get_channel_arr(){
		$where = array(
			'del' => 0,
			'type' => array('in',array('1','2','4')),
		);
		$channel_list = get_table_data($where,"coop_channel","id","id,channel_name,status");	
		return 	$channel_list;
	}
	//卡片类型数据
	public function get_card_type_conf(){
		$array = array(
			1 => "资讯_站点+标签",
			2 => "资讯_标签",
			3 => "视频_站点+标签",
			4 => "视频_标签",
		);
		return $array;
	}
	public function get_card_find($id){
		$table = "coop_card";
		$where = array(
			'id' => $id,
			'del' => 0
		);
		$ret = $this -> table($table) -> where($where)->find();
		return $ret;		
	}
	//添加、更新卡片
	public function save_card(&$act){
		$table = "coop_card";
		$contact_type = $_POST['contact_type'];
		$contact_id = $_POST['contact_id'];
		//渠道
		$_POST['content_tags']=is_array($_POST['content_tags'])?$_POST['content_tags']:array($_POST['content_tags']);
		$time = time();
		$map = array(
			'card_name' => $_POST['card_name'],
			'channle_id' => $_POST['channle_id'],
			'site_id' => $_POST['site_id'],
			'card_type' => $_POST['card_type'],
			'is_mark' => $_POST['card_type'] ==3 || $_POST['card_type'] ==4 ? $_POST['is_mark'] :0,
			'content_num' => $_POST['content_num'],
			'is_more' => $_POST['is_more'],
			'contact_site_id' => $_POST['contact_site_id'],
			'content_site_id' => $_POST['content_site_id'],
			'contact_type' => $_POST['contact_type'],
			'rank' => $_POST['rank'],
			'content_rank' => $_POST['content_rank'],
			'contact_id' => $contact_id,
			'content_tags' => ",".implode(",",$_POST['content_tags']).",",
		);		
		$id = $_POST['id'];
		if($id){
			$where = array(
				'id' => $id,
				'del' => 0
			);
			$map['update_tm'] = $time;
			$log = $act->logcheck($where, $table, $map, $this);	
			$affect = $this->table($table) -> where($where) -> save($map);
		}else{
			$map['create_tm'] = $time;
			$map['update_tm'] = $time;
			$affect = $this->table($table)->add($map);
		}
		return array($affect,$log);
	}
	//合作站点数据
	public function get_site_list(){
		$table = "coop_site";
		import('@.ORG.Page2');
		$total = $this->table($table)->count();
		$Page = new Page($total,100);	
		$order_str = "id asc";
		$list = $this->table($table)->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select(); 
		//计算数据
		$id_arr = array();
		$pkg = array();
		foreach($list as $v){
			$id_arr[] = $v['id'];
			if($v['package']) $pkg[] = $v['package'];
		}
		$where = array(
			'site_id'=>array('in',$id_arr),
			'status'=> 1,
		);
		$count_list = $this->table("coop_site_tag")->where($where)->group("site_id")->field("count(site_id) as counts,site_id")->select();	
		$count_arr = array();
		foreach($count_list as $val){
			$count_arr[$val['site_id']] = $val['counts'];
		}
		unset($count_list);	
		$config = $this -> return_config('coop_chl_id');
		$where = array(
			'status' => 1,
			'hide' => array('in',array(1,1024)),
			'package' => array('in',$pkg)
		);
		$soft_list = $this->table("sj_soft")->where($where)->order("version_code asc")->field("package,softid,softname,version,version_code,hide,channel_id")->select();	
		$where = array(
			'package_status' => 1,
			'apk_name' => array('in',$pkg)
		);		
		$file = get_table_data($where,"sj_soft_file","softid","softid,url");
		$soft_arr =  array();
		$chl_soft_arr =  array();
		foreach($soft_list as $key => $val){
			$channel_id = array_filter(explode(",",$val['channel_id']));
			if($val['hide'] == 1024 && in_array($config['configcontent'],$channel_id)){
				$chl_soft_arr[$val['package']] = $val;
				$chl_soft_arr[$val['package']]['url'] = $file[$val['softid']]['url'];
			}else if($val['hide'] == 1){
				$soft_arr[$val['package']] = $val;				
				$soft_arr[$val['package']]['url'] = $file[$val['softid']]['url'];
			}
		}	
		return array($list,$total,$Page,$count_arr,$soft_arr,$chl_soft_arr);		
	}	
	public function get_site_find($id){
		$table = "coop_site";
		$where = array(	'id' => $id	);
		$ret = $this -> table($table) -> where($where)->find();
		return $ret;		
	}	
	//编辑合作站点
	public function save_site(&$act){
		$table = "coop_site";
		//渠道
		$time = time();
		$brush_page_arr = $_POST['brush_page'];
		foreach ($brush_page_arr as $brush_page) 
		{
            $map_show_place |= $brush_page;
        }
		$map = array(
			'anzhi_name' => $_POST['anzhi_name'],
			'down_tip' => $_POST['down_tip'],
			'minimum_version' => $_POST['minimum_version'],
			'is_chain_down' => empty($_POST['is_chain_down'])?0:$_POST['is_chain_down'],
			'show_frequency' => $_POST['show_frequency'],
			'brush_config' => $_POST['brush_config'],
			'brush_page' => $map_show_place,
			'brush_percent' => $_POST['brush_percent'],
			'daily_brush_max' => $_POST['daily_brush_max'],
		);		
		$id = $_POST['id'];
		$where = array(
			'id' => $id,
		);
		$map['update_time'] = $time;
		$log = $act->logcheck($where, $table, $map, $this);	
		$affect = $this->table($table) -> where($where) -> save($map);
		if($id == 1){
			$where = array(
				'config_type' => 'coop_table_name',
				'status' => 1,
			);				
			$save = array(
				'configcontent' => $_POST['anzhi_name'],
				'uptime' => time(),
			);	
			$affect = $this -> table('pu_config')-> where($where) -> save($save);				
		}		
		return array($affect,$log);
	}	
	//合作站点标签数据
	public function get_site_tag_list($where){
		$table = "coop_site_tag";
		import('@.ORG.Page2');
		$total = $this->table($table)->where($where)->count();
		$Page = new Page($total,100);	
		$order_str = "status desc,rank asc,id asc";
		$list = $this->table($table)->where($where)->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select(); 
		//计算数据
		$id_arr = array();
		foreach($list as $v){
			$id_arr[] = $v['id'];
		}		
		$where = array(
			'tag_id'=>array('in',$id_arr),
		);
		$count_list = $this->table("coop_content")->where($where)->group("tag_id")->field("count(tag_id) as counts,tag_id")->select();	
		$count_arr = array();
		foreach($count_list as $val){
			$count_arr[$val['tag_id']] = $val['counts'];
		}		
		$video_count = $this->table("coop_video")->where($where)->group("tag_id")->field("count(tag_id) as counts,tag_id")->select();	
		$video_count_arr = array();
		foreach($video_count as $val){
			$video_count_arr[$val['tag_id']] = $val['counts'];
		}
		unset($count_list);		
		unset($video_count);		
		return array($list,$total,$Page,$count_arr,$video_count_arr);
	}	
	//编辑合作标签
	public function save_site_tag(&$act){
		$table = "coop_site_tag";
		//渠道
		$time = time();
		$map = array(
			'tag_anzhi_name' => $_POST['tag_anzhi_name'],
			'rank' => $_POST['rank'],
			'is_mark' => $_POST['is_mark'],
		);		
		$id = $_POST['id'];
		$where = array(
			'id' => $id,
		);
		$map['update_time'] = $time;
		$log = $act->logcheck($where, $table, $map, $this);	
		$affect = $this->table($table) -> where($where) -> save($map);
		return array($affect,$log);
	}
	//添加和编辑智友合作标签
	public function add_save_site_tag(&$act){
		$table = "coop_site_tag";
		//渠道
		$time = time();
		$map = array(
			'tag_anzhi_name' => $_POST['tag_anzhi_name'],
			'tag_name' => $_POST['tag_name'],
			'site_id' => $_POST['site_id'],
			'filter_id' => $_POST['filter_id'],
			'site_tag_id' => $_POST['site_tag_id'],

			// 'rank' => $_POST['rank'],
			// 'is_mark' => $_POST['is_mark'],
		);		
		$id = $_POST['id'];
		if($id){
			$where = array(
				'id' => $id,
			);
			$map['update_time'] = $time;
			$log = $act->logcheck($where, $table, $map, $this);	
			$affect = $this->table($table) -> where($where) -> save($map);
		}else{
			$map['create_time'] = $time;
			$affect = $this->table($table) -> where($where) -> add($map);
			$log=$affect;
		}
		
		return array($affect,$log);
	}	
	public function get_site_tag_find($id){
		$table = "coop_site_tag";
		$where = array(	'id' => $id	);
		$ret = $this -> table($table) -> where($where)->find();
		return $ret;		
	}
	//包名信息
	public function get_pkg_info(){
		$apkmodel = D("Dev.Apk");	
		if(!$_POST['softid'] || !is_numeric($_POST['softid'])||empty($_FILES['apk'])) {
			$ret['msg'] = '版本升级缺少软件id参数或apk文件';
			return $ret;
		}		
		$mark = $_POST['mark'];
		//获取apk信息
		$vals = array(
			'do' => 'apk',
			'apk' => '@'.$_FILES['apk']['tmp_name'],
			'name' => $_FILES['apk']['name'],
			'id' => $_SESSION['admin']['admin_id'],
		);	
		$arr = $apkmodel -> _http_post($vals);
		$arr = json_decode($arr, true);
		if($arr) {
			if($arr['code'] < 1) {
				$ret['msg'] = $arr['msg'];
				return $ret;
			}
		} else {
			$ret['msg'] = '上传失败。上传apk返回异常，请重试';
			return $ret;
		}	
				
		//返回的apk信息
		$_SESSION['apk_info'.$mark] = $arr['ret'];
		$_SESSION['apk_info'.$mark]['_iconurl'] = IMG_HOST.$_SESSION['apk_info'.$mark]['_iconurl'];		//icons
		$_SESSION['apk_info'.$mark]['iconurl'] = $arr['ret']['_iconurl'];
		file_put_contents('/tmp/lzf.log',var_export($_SESSION,true),FILE_APPEND);
		//返回显示包信息
		$ret['ret'] = $_SESSION['apk_info'.$mark];
		
		//包名规范性检查	
		$pname_chk = $this -> packagename_chk ($arr['ret'][packagename]);
		if($pname_chk != 'ok'){
			$ret['msg'] = $pname_chk;
			return $ret;
		}
		$extension_arr = C('extension_arr');
		foreach($extension_arr as $val){
			if(strpos($arr['ret']['packagename'],$val)){
				$ret['pack_extension'] = "此软件疑似竞品，包名包含{$val}";
				continue;
			}
		}
		$_SESSION['apk_update'.$mark] = '';
		$config = $this -> return_config('coop_chl_id');
		$where = array(
			'softid' => $_POST['softid'],
			'status'=>1,
			//'hide' => 1024,
			//'channel_id' => array("like","%,".$config['configcontent'].",%")
		);
		$soft_list = $this->table("sj_soft")->where($where)->find();	
		
		if(!$soft_list) {
			$ret['msg'] = "没找到该软件id对应的上架软件，无法升级";
			return $ret;
		} else {	//package一致,version_code高就可以更新
			$_SESSION['apk_update'.$mark] = $soft_list;	//升级软件信息
			$_SESSION['apk_info'.$mark]['record_type'] = 3;
			$_SESSION['apk_info'.$mark]['get_type'] = 'update';	
			$_SESSION['apk_info'.$mark]['channel_id'] = $_POST['cid'] ? $_POST['cid'] : $config['configcontent'];	
			$_SESSION['apk_info'.$mark]['user_ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['apk_info'.$mark]['update_from'] = $soft_list['softid'];
			$_SESSION['apk_info'.$mark]['category_id'] = substr($soft_list['category_id'],1,-1);
			if($_SESSION['apk_update'.$mark]['package']!=$_SESSION['apk_info'.$mark]['packagename']) {
				$ret['msg'] = "包名({$_SESSION['apk_update'.$mark]['package']})与当前包名({$_SESSION['apk_info'.$mark]['packagename']})不同。";
				return $ret;
			} else if($_SESSION['apk_update'.$mark]['version_code'] > $_SESSION['apk_info'.$mark]['versionCode']) {
				$ret['msg'] = "请上传版本号（version code）大于当版本的软件。当前版本号（{$_SESSION['apk_update'.$mark]['version_code']}），上传的版本号（{$_SESSION['apk_info'.$mark]['versionCode']}）";
				return $ret;
			} else {
				$_SESSION['apk_info'.$mark]['ok'] = md5(microtime());	//可以版本升级
				$ret['code'] = '1';
				$ret['msg'] = '软件上传成功';
				return $ret;
			}
		}
		
	}	
	function cp_soft_thumb($old_softid,$softid){
		$time = time();
		$where = array(
			'status' => 1,
			'softid' => $old_softid,
		);
		$thumb = $this->table("sj_soft_thumb")->where($where)->select(); 	
		if(!$thumb){
			return false;
		}
		foreach($thumb as  $v){
			$map = array();
			foreach($v as $k=>$val){
				if($k == 'id') continue;
				$map[$k] = $val;
				$map['softid'] = $softid;
				$map['status'] = 1;
				$map['upload_time'] = $time;
				$map['last_refresh'] = $time;
			}
			$this->table("sj_soft_thumb")->add($map);
		}				
	}	
	function cp_soft_icon($old_softid,$softid){
		$time = time();
		$where = array(
			'status' => 1,
			'softid' => $old_softid,
		);
		$icon = $this->table("sj_icon")->where($where)->find(); 	
		if(!$icon) return false;
		$map = array();
		foreach($icon as $k=>$val){
			if($k == 'id') continue;
			$map[$k] = $val;
			$map['softid'] = $softid;
			$map['status'] = 1;
			$map['update_time'] = $time;
			$map['add_time'] = $time;
		}				
		return $this->table("sj_icon")->add($map);
	}
	//包名检查
	function packagename_chk ($val){
		$model = new Model();
		if (empty($GLOBALS['soft_shieldpackagename'])) {
			$shield_result = $model->table('pu_config')->where("status='1' AND config_type='soft_shieldpackagename'")->find();

			$shield = $shield_result['configcontent'];
			$shield_str = substr($shield, 0, - 1);
			$GLOBALS['soft_shieldpackagename'] = explode(';', $shield_str);
		}

		if (in_array($val, $GLOBALS['soft_shieldpackagename'])) {
			return '软件包名不规范，未通过检查';
		}

		static $key_words = array ('abstract', 'assert', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'continue', 'default', 'do', 'double', 'else', 'enum', 'extends', 'final', 'finally', 'float', 'for', 'if', 'implements', 'import', 'instanceof', 'int', 'interface', 'long', 'native', 'new', 'package', 'private', 'protected', 'public', 'return', 'strictfp', 'short', 'static', 'super', 'switch', 'synchronized', 'this', 'throw', 'throws', 'transient', 'try', 'void', 'volatile', 'while', 'abstract', 'assert', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'continue', 'default', 'do', 'double', 'else', 'enum', 'extends', 'final', 'finally', 'float', 'for', 'if', 'implements', 'import', 'instanceof', 'int', 'interface', 'long', 'native', 'new', 'package', 'private', 'protected', 'public', 'return', 'strictfp', 'short', 'static', 'super', 'switch', 'synchronized', 'this', 'throw', 'throws', 'transient', 'try', 'void', 'volatile', 'while' );

		static $reg = '/^([a-z_\$][a-z_\$0-9]*\.)*[a-z_\$][a-z_\$0-9]*$/i';
		$reg_key_words_prefix = '/^(' . implode('|', $key_words) . ')\./';
		$reg_key_words_infix = '/\.(' . implode('|', $key_words) . ')\./';
		$reg_key_words_surfix = '/\.(' . implode('|', $key_words) . ')$/';

		if (! preg_match($reg, $val)) {
			return '软件包名不规范，未通过检查(1)';
		}
		if (preg_match($reg_key_words_prefix, $val)) {
			return '软件包名不规范，未通过检查(2)';
		}
		if (preg_match($reg_key_words_infix, $val)) {
			return '软件包名不规范，未通过检查(3)';
		}
		if (preg_match($reg_key_words_surfix, $val)) {
			return '软件包名不规范，未通过检查(4)';
		}

		return 'ok';
	}
	public function return_config($config_type){
		if(!S($config_type)){
			$where = array(
				'config_type' => $config_type,
				'status' =>1
			);
			$config = $this -> table('pu_config') -> where($where)->field('configcontent')->find();	
			S($config_type,$config,300);
		}else{
			$config = S($config_type);
		}
		return $config;
	}

	public function get_content($where){
		$content_list = $this->table('coop_content')->where($where)->select();
		return $content_list;
	}
}