<?php
//安智市场合作运营平台
class ChanneluserAction extends CommonAction{
	
	//渠道账号列表
	Public function channel_list(){
		$model = D('Cooperate.Channeluser');
		$key_word = $_GET['key_word'];
		$create_person = $_SESSION['admin']['admin_id'];
		$where_user['_string'] = "username like '%{$key_word}%' and create_person = {$create_person}";
		$user_result = $model -> table('t_user') -> where($where_user) -> order("status DESC,create_tm DESC") ->  select();
		$admin_model = new Model();
		import("@.ORG.3des");
		$rep = new Crypt3Des();
		foreach($user_result as $key => $val){
			$node_result = $model -> table('t_user_node') -> where(array('uid' => $val['id'],'status' => 1)) -> field('node') -> select();
			$val['node'] = $node_result[0]['node'];
			$user_result = $admin_model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['create_person'])) -> field('admin_user_name') -> select();
			$val['create_user'] = $user_result[0]['admin_user_name'];
			$val['passwd'] = $rep->decrypt($val['passwd']);
			$user_results[$key] = $val;
		}
		
		$this -> assign('key_word',$key_word);
		$this -> assign("user_results",$user_results);
		$this -> display();
	}
	
	//账号停用/恢复
	public function alter_status(){
		$uid = $_GET['uid'];
		$model = D('Cooperate.Channeluser');
		$status = $_GET['status'];
		if($status == 1){
			$where['id'] = $uid;
			$where['status'] = 0;
			$data['status'] = 1;
			$data['update_tm'] = time();
			$alter = $model -> save_user($where,$data);
			$where_account['uid'] = $uid;
			$data_account['status'] = 1;
			$alter_account = $model -> save_account($where_account,$data_account);
			$where_channel['uid'] = $uid;
			$where_channel['status'] = 3;
			$data_channel['status'] = 1;
			$alter_channel = $model -> save_channel($where_channel,$data_channel);
		}elseif($status == 0){
			$where['id'] = $uid;
			$where['status'] = 1;
			$data['status'] = 0;
			$data['update_tm'] = time();
			$alter = $model -> save_user($where,$data);
			$where_account['uid'] = $uid;
			$data_account['status'] = 0;
			$alter_account = $model -> save_account($where_account,$data_account);
			$where_channel['uid'] = $uid;
			$where_channel['status'] = 1;
			$data_channel['status'] = 3;
			$alter_channel = $model -> save_channel($where_channel,$data_channel);
		}
		if($alter && $alter_account){
			$this -> writelog("已修改id为{$uid}的用户状态为{$status}");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channeluser/channel_list');
			$this -> success("修改成功");
			
		}
	}
	
	//重置密码显示
	public function show_alter_pwd(){
		$uid = $_GET['uid'];
		$this -> assign('uid',$uid);
		$this -> display();
	}
	
	
	//重置密码提交
	public function alter_pwd(){
		$model = D('Cooperate.Channeluser');
		$uid = $_POST['uid'];
		$passwd = $_POST['passwd'];
		$passwd1 = $_POST['passwd1'];
		if($passwd != $passwd1){
			$this -> error("两次输入密码不一致");
		}
		$where['id'] = $uid;
		$where['status'] = 1;
		import("@.ORG.3des");
		$rep = new Crypt3Des();
		$data['passwd'] = $rep->encrypt($passwd);
		$data['update_tm'] = time();
		$alter = $model -> save_user($where,$data);
		if($alter){
			$this -> writelog("已修改id为{$uid}的用户密码");
			$this -> success("修改成功");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channeluser/channel_list');
		}
	}
	
	//放弃编辑
	public function give_up_edit(){
		$model = D('Cooperate.Channeluser');
		$uid = $_GET['uid'];
		$where['uid'] = $uid;
		$where['status'] = 2;
		$eidt_result = $model -> table('t_user_channel') -> where($where) -> delete();
		$where_node['uid'] = $uid;
		$data_node['status'] = 0;
		$node_result = $model -> save_node($where_node,$data_node);
		$where_user['id'] = $uid;
		$data_user['status'] = 1;
		$data_user['update_tm'] = time();
		$user_result = $model -> save_user($where_user,$data_user);
		if($user_result){
			$this -> writelog("已修改id为{$uid}的用户状态为1");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channeluser/channel_list'); 
			$this -> success("操作成功");
		}
	}
	
	//编辑账号信息显示
	public function show_edit_user(){
		$uid = $_GET['uid'];
		$model = D('Cooperate.Channeluser');
		$account_result = $model -> table('t_account') -> where(array('uid' => $uid,'status' => 1)) -> select();
		$this -> assign('account_result',$account_result[0]);
		$this -> assign('uid',$uid);
		$this -> display();
	}
	
	//编辑账号信息提交
	public function edit_user(){
		$model = D('Cooperate.Channeluser');
		$uid = $_POST['uid'];
		$data['account_name']= $_POST['account_name'];
		$data['account_type'] = $_POST['account_type'];
		$data['bank_name'] = $_POST['bank_name'];
		$data['bank_account'] = $_POST['bank_account'];
		$data['bank_addr'] = $_POST['bank_addr'];
		$data['min_balance'] = $_POST['min_balance'];
		$data['update_tm'] = time();
		//$data_user['account_type'] = $_POST['account_type'];
		$data_user['update_tm'] = time();
		if($uid){
			$where['uid'] = $uid;
			$where['status'] = 1;
			$where_user['id'] = $uid;
			$result = $model -> save_account($where,$data);
			$result_user = $model -> save_user($where_user,$data_user);
			//echo $model -> getLastSql();exit;
			if($result){
				$this -> writelog("已修改id为{$uid}的用户账号信息");
				$this -> success("编辑成功");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channeluser/channel_list');
			}
		}
	}
	
	//渠道管理列表
	public function channel_manage_list(){
		$model = D('Cooperate.Channeluser');
		$uid = $_GET['uid'];
		$where['_string'] = "uid = {$uid} and status !=2";
		
		$result = $model -> table('t_user_channel') -> where($where) -> order("status DESC,create_tm DESC") -> select();
		
		$channel_model = new Model();
		foreach($result as $key => $val){
			$where_cid['cid'] = $val['cid'];
			$chname = $channel_model -> table('sj_channel') -> where($where_cid) -> field('chname') -> select();
			$config_result = $model -> table('t_channel_config') -> where(array('cid' => $val['cid'])) -> select();
			$create_user = $channel_model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['create_uid'])) -> field('admin_user_name') -> select();
			if($val['update_tm'] >= $config_result[0]['update_tm']){
				$val['update_tms'] = $val['update_tm'];
			}else{
				$val['update_tms'] = $config_result[0]['update_tm'];
			}
			$val['create_tms'] = $config_result[0]['create_tm'];
			$val['chname'] = $chname[0]['chname'];
			$val['create_user'] = $create_user[0]['admin_user_name'];
			$result[$key] = $val;
		}
		$this -> assign('uid',$uid);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	//编辑渠道分成模式显示
	public function show_channel_config(){
		$model = D('Cooperate.Channeluser');
		$cid = $_GET['cid'];
		$config_result = $model -> table('t_channel_config') -> where(array('cid' => $cid)) -> select();
		$channel_model = new Model();
		$channel_name = $channel_model -> table('sj_channel') -> where(array('cid' => $cid)) -> field('chname') -> select();
		$this -> assign('config_result',$config_result[0]);
		$this -> assign("channel_name",$channel_name[0]['chname']);
		$this -> display();
	}
	
	//编辑渠道分成模式提交
	public function edit_channel_config(){
		$model = D('Cooperate.Channeluser');
		$cid = $_GET['cid'];
		$data['active_price'] = floatval(trim($_GET['active_price'])) > 0 ? floatval(trim($_GET['active_price'])) : 0;
		if($this -> check_point(floatval(trim($_GET['active_price'])),4,10)){
			$this -> error("安智市场激活单价格式错误");
		}
		$data['active_switch'] = trim($_GET['active_switch']) == 1 ? 1 : 0;
		$data['ad_price'] = floatval(trim($_GET['ad_price'])) > 0 ? floatval(trim($_GET['ad_price'])) : 0;
		if($this -> check_point(floatval(trim($_GET['ad_price'])),4,10)){
			$this -> error("单个软件下载单价格式错误");
		}
		$data['ad_switch'] = trim($_GET['ad_switch']) == 1 ? 1 : 0;
		$data['max_down'] = trim($_GET['max_down']) > 0 ? trim($_GET['max_down']) : 0;
		if($this -> check_point(trim($_GET['max_down']),0,100)){
			$this -> error("防刷量值格式错误");
		}
		$data['ad_cut_pre'] = floatval(trim($_GET['ad_cut_pre'])) > 0 ? floatval(trim($_GET['ad_cut_pre'])) : 0;
		$data['game_cut_pre'] = floatval(trim($_GET['game_cut_pre'])) > 0 ? floatval(trim($_GET['game_cut_pre'])) : 0;
		$data['game_switch'] = trim($_GET['game_switch']) == 1 ? 1 : 0;
		$data['update_tm'] = time();
		if(!$_GET['active_switch'] && !$_GET['ad_switch'] && !$_GET['game_switch']){
			$this -> error("请至少选择一项分成模式");
		}
		$where['cid'] = $cid;
		$where['status'] = 1;
		$result = $model -> save_channel_config($where,$data);
		$uid_result = $model -> table('t_user_channel') -> where(array('cid' => $cid,'status' => 1)) -> field('uid') -> select();
		$where_channel['cid'] = $cid;
		$data_channel['update_tm'] = time();
		$result_tm = $model -> save_channel($where_channel,$data_channel);
		if($result){
			$this -> writelog("已修改渠道id为{$cid}的渠道配置");
			$this -> success("编辑成功");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channeluser/channel_manage_list/uid/'.$uid_result[0]['uid'].'');
		}
	}
	
	//移除渠道
	public function remove_channel(){
		$model = D('Cooperate.Channeluser');
		$cid = $_GET['cid'];
		$where['cid'] = $cid;
		$where['status'] = 1;
		$data['status'] = 0;
		$data['update_tm'] = time();
		$uid_result = $model -> table('t_user_channel') -> where(array('cid' => $cid,'status' => 1)) -> field('uid') -> select();
		$channel_result = $model -> save_channel($where,$data);
		if($channel_result){
			$this -> writelog("已移除id为{$cid}的渠道");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channeluser/channel_manage_list/uid/'.$uid_result[0]['uid'].'');
			$this -> success("移除成功");
		}
	}
	
	//创建渠道账号显示
	Public function create_channel_user(){
		$uid = $_GET['uid'];
		$been_uid = $_GET['been_uid'];
		if($uid){
			$model = D('Cooperate.Channeluser');
			$user_result = $model -> table('t_user') -> where(array('id' => $uid)) -> select();
			$account_result = $model -> table('t_account') -> where(array('uid' => $uid)) -> select();
			$this -> assign('uid',$uid);
			$this -> assign('user_result',$user_result[0]);
			$this -> assign('account_result',$account_result[0]);
		}elseif($been_uid){
			$user_result = array();
			$account_result = array();
			$this -> assign('user_result',$user_result);
			$this -> assign('account_result',$account_result);
		}
		$this -> display();
	}
/* 	//重置创建账号
	public function reset_user(){
		
		$uid = $_GET['uid'];
		$model = D('Cooperate.Channeluser');
		$where_user['id'] = $uid;
		$where_account['uid'] = $uid;
		$data_user['username'] = "";
		$data_user['passwd'] = "";
		$data_user['create_person'] = "";
		$data_account['account_name'] = "";
		$data_account['account_type'] = "";
		$data_account['bank_name'] = "";
		$data_account['bank_account'] = "";
		$data_account['bank_addr'] = "";
		$data_account['min_balance'] = "";
		$user_result = $model -> save_user($where_user,$data_user);

		$account_result = $model -> save_account($where_account,$data_account);
		Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/create_channel_user/uid/{$uid}");
	}	 */
	
	//查验用户名是否已存在
	public function check_username(){
		$username = $_GET['username'];
		$uid = $_GET['uid'];
		$model = D('Cooperate.Channeluser');
		if($uid){
			$where['_string'] = "id != {$uid} and username = {$username}";
		}else{
			$where['_string'] = " username = '{$username}'";
		}
		$result = $model -> table('t_user') -> where($where) -> select();
		
		if($result){
			$repeat = 1;
		}else{
			$repeat = 2;
		}
		echo json_encode($repeat);
		if($username){
			return json_encode($repeat);
		}
	}
		
	//创建渠道账号提交
	Public function submit_channel_user(){
		$username = trim($_POST['username']);
		import("@.ORG.3des");
		$rep=new Crypt3Des();
		$passwd = $rep->encrypt($_POST['passwd']);
		$account_type = trim($_POST['account_type']);
		$account_name = trim($_POST['account_name']);
		$bank_name = trim($_POST['bank_name']);
		$bank_addr = trim($_POST['bank_addr']);
		$min_balance = trim($_POST['min_balance']);
		$bank_account = trim($_POST['bank_account']);
		$model = D('Cooperate.Channeluser');
		$int_uid = intval($_POST['uid']);
		$int_gid = intval($_GET['uid']);

		if($int_uid){
			$where_user1['id'] = trim($_POST['uid']);
			$where_account1['uid'] = trim($_POST['uid']);
			$data_user1['username'] = $username;
			$data_user1['passwd'] = $passwd;
			$data_user1['create_person'] = $_SESSION['admin']['admin_id'];
			$data_user1['create_tm'] = time();
			$data_user1['update_tm'] = time();
			$data_account1['account_name'] = $account_name;
			$data_account1['account_type'] = $account_type;
			$data_account1['bank_name'] = $bank_name;
			$data_account1['bank_addr'] = $bank_addr;
			$data_account1['min_balance'] = $min_balance;
			$data_account1['bank_account'] = $bank_account;
			$data_account1['update_tm'] = time();
			$where['username'] = $username;
			$where['_string'] = "status != 0 and id != {$int_uid} ";

			$check_result = $model -> table('t_user') -> where($where) -> select();
			if($check_result){
				$this -> error("该用户名已存在");
			}
			$user_result1 = $model -> save_user($where_user1,$data_user1);
			$account_result1 = $model -> save_account($where_account1,$data_account1);
			if($user_result1 && $account_result1){
				$this -> writelog("修改了id为{$_POST['uid']}的渠道账号");
				Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/".$_POST['uid'].""); 
			}
		}else if($int_gid){
			$where_user1['id'] = trim($_GET['uid']);
			$where_account1['uid'] = trim($_GET['uid']);
			$data_user1['username'] = $username;
			$data_user1['passwd'] = $passwd;
			$data_user1['create_person'] = $_SESSION['admin']['admin_id'];
			$data_user1['create_tm'] = time();
			$data_user1['update_tm'] = time();
			$data_account1['account_name'] = $account_name;
			$data_account1['account_type'] = $account_type;
			$data_account1['bank_name'] = $bank_name;
			$data_account1['bank_addr'] = $bank_addr;
			$data_account1['min_balance'] = $min_balance;
			$data_account1['bank_account'] = $bank_account;
			$data_account1['update_tm'] = time();
			$where['username'] = $username;
			$where['_string'] = "status != 0";

			$check_result = $model -> table('t_user') -> where($where) -> select();
			if($check_result){
				$repeat = 1;
				return $repeat;
				exit;
			}
			$user_result1 = $model -> save_user($where_user1,$data_user1);
			$account_result1 = $model -> save_account($where_account1,$data_account1);
			if($user_result1 && $account_result1){
				$this -> writelog("修改了id为{$_POST['uid']}的渠道账号");
				Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/".$_POST['uid'].""); 
			}
		
		
		
		}else{
			$data_user['username'] = $username;
			$data_user['passwd'] = $passwd;
			$data_user['create_person'] = $_SESSION['admin']['admin_id'];
			$data_user['create_tm'] = time();
			$data_user['update_tm'] = time();
			$data_user['status'] = 2;
			$data_account['account_name'] = $account_name;
			$data_account['account_type'] = $account_type;
			$data_account['bank_name'] = $bank_name;
			$data_account['bank_addr'] = $bank_addr;
			$data_account['min_balance'] = $min_balance;
			$data_account['bank_account'] = $bank_account;
			$data_account['create_tm'] = time();
			$data_account['update_tm'] = time();
			$data_account['status'] = 1;
			$where['username'] = $username;
			$where['_string'] = "status != 0 and id != {$int_uid} ";

			$check_result = $model -> table('t_user') -> where($where) -> select();
			if($check_result){
				$this -> error("该用户名已存在");
			}
			$user_result = $model -> add_user($data_user);
			$data_account['uid'] = $user_result;
			
			if($user_result > 0){
				$account_result = $model -> add_account($data_account);
			}else{
				$this -> error("添加失败");
			}
			if($user_result && $account_result){
				$data_node['uid'] = $user_result;
				$data_node['node'] = 1;
				$data_node['create_tm'] = time();
				$data_node['update_tm'] = time();
				$data_node['status'] = 1;
				$node_result = $model -> add_node($data_node);
				$this -> writelog("添加了用户名为{$username}的渠道账号，id为{$user_result}");
				Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/".$user_result.""); 
			}
		}
 	}
	
	
	//选择渠道显示
	public function select_channel(){
		$model = new Model();
		$key_word = trim($_GET['key_word']);
		$uid = $_GET['uid'];
		$from = $_GET['from'];
		$check_model = D('Cooperate.Channeluser');
		if($from){
			$data_node['uid'] = $uid;
			$data_node['status'] = 1;
			$data_node['node'] = 1;
			$data_node['create_tm'] = time();
			$data_node['update_tm'] = time();
			$node_result = $check_model -> add_node($data);
		}
	
		
		if($key_word){//检索渠道
			$check_channel = $check_model -> table('t_user_channel') -> field('cid') -> select();
			foreach($check_channel as $key => $val){
				$check_cids .= $val['cid'].',';
			}
			$check_cid = substr($check_cids,0,strlen($check_cids) -1);
			if($_SESSION['co_channel']['cid'] && $check_channel){
				$where['_string'] = "status=1 and chname like '%{$key_word}%' and cid not in ({$_SESSION['co_channel']['cid']}) and cid not in ({$check_cid})";
			}elseif($_SESSION['co_channel']['cid'] && !$check_channel){
				$where['_string'] = "status=1 and chname like '%{$key_word}%' and cid not in ({$_SESSION['co_channel']['cid']})";
			}elseif(!$_SESSION['co_channel']['cid'] && $check_channel){
				$where['_string'] = "status=1 and chname like '%{$key_word}%' and cid not in ({$check_cid})";
			}elseif(!$_SESSION['co_channel']['cid'] && !$check_channel){
				$where['_string'] = "status=1 and chname like '%{$key_word}%'";
			}
	
			$channel_results = $model -> table('sj_channel') -> where($where) -> order("submit_tm") -> select();		
			import("@.ORG.Page");
			
			$count = count($channel_results);
			$param = http_build_query($_GET);
			$Page = new Page($count, 10, $param);
			$need_channel = $model -> table('sj_channel') -> where($where) -> order('submit_tm DESC') -> limit($Page->firstRow . ',' . $Page->listRows)-> select();

			foreach($need_channel as $key => $val){
				$channel_category = $model -> table('sj_channel_category') -> where(array('category_id' => $val['category_id'])) -> field('name') -> select();
				$val['category_name'] = $channel_category['name'];
				
				switch ($val['activation_type']){
					case 5:  $val['activation_type_name'] = '普通非山寨';break;
					case 6:  $val['activation_type_name'] = '严格非山寨';break;
					case 9:  $val['activation_type_name'] = '普通山寨';break;
					case 10:  $val['activation_type_name'] = '严格山寨';break;
				}
				$need_channel[$key] = $val;
			}
			$Page->setConfig('header', '篇记录');
			$Page->setConfig('first', '<<');
			$Page->setConfig('last', '>>');
			$show = $Page->show();
			$this->assign("page", $show);
		}else{
			unset($_SESSION['co_channel']);
			$need_channel = array();
		}
		
		//已选择渠道
		$username_result = $check_model -> table('t_user') -> where(array("id" => $uid)) -> field("username,status") -> select();
		//$_SESSION['co_channel']['cid'] = array_flip($_SESSION['co_channel']['cid']);
		$my_channels = $check_model -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 2)) -> field('cid') -> select();
		if($_SESSION['co_channel']['cid']){
			$cid = explode(',',$_SESSION['co_channel']['cid']);	
			$cids = array_flip($cid);
			foreach($cids as $key => $val){
				$cid_str .= $key.',';
			}
			$cid_strs = substr($cid_str,0,strlen($cid_str) - 1);
			$my_channels_session = explode(',',$cid_strs);
			$channel_result = $check_model -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 2)) -> field('cid') -> select();
			foreach($channel_result as $key => $val){
				$my_channels_go[] = $val['cid'];
			}
			if($my_channels_go){
				$my_channels_have = array_merge($my_channels_session,$my_channels_go);
			}else{
				$my_channels_have = $my_channels_session;
			}
		}else{
			$channel_result = $check_model -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 2)) -> field('cid') -> select();
			foreach($channel_result as $key => $val){
				$my_channels_have[] = $val['cid'];
			}
			//var_dump($my_channels);
		}

		foreach($my_channels_have as $key => $val){
			$my_channel = $model -> table('sj_channel') -> where(array("cid" => $val)) -> select();
			$my_channel_go[] = $my_channel[0];
		}

		foreach($my_channel_go as $key => $val){
			$channel_category = $model -> table('sj_channel_category') -> where(array('category_id' => $val['category_id'])) -> field('name') -> select();
			$val['category_name'] = $channel_category['name'];
			
			switch ($val['activation_type']){
				case 5:  $val['activation_type_name'] = '普通非山寨';break;
				case 6:  $val['activation_type_name'] = '严格非山寨';break;
				case 9:  $val['activation_type_name'] = '普通山寨';break;
				case 10:  $val['activation_type_name'] = '严格山寨';break;
			}
			$ineed_channel[$key] = $val;
		}
		
		$this -> assign("uid",$uid);
		$this -> assign("from",$from);
		$this -> assign("username",$username_result[0]['username']);
		$this -> assign('status',$username_result[0]['status']);
		if($key_word){
			$this -> assign("key_word",$key_word);
		}
		$this -> assign("need_channel",$need_channel);
		$this -> assign("ineed_channel",$ineed_channel);
		$this -> display('select_channel');
	}
	
	//伪渠道添加
	public function fake_channel_add(){
		$key_word = $_GET['key_word'];
		$from = $_GET['from'];
		$cid = $_GET['cid'];
		$uid = $_GET['uid'];
		if($_SESSION['co_channel']['cid']){
			$_SESSION['co_channel']['cid'] = $_SESSION['co_channel']['cid'].','.$cid;
		}else{
			$_SESSION['co_channel']['cid'] = $cid;
		}
 		/* $data['uid'] = $_GET['uid'];
		$data['cid'] = $_GET['cid'];
		$data['status'] = 2;
		$data['create_uid'] = $_SESSION['admin']['admin_id'];
		$data['update_tm'] = time();
		$data['create_tm'] = time();
		$model = D('Cooperate.Channeluser');
		$username_result = $model -> table('t_user') -> where(array("uid" => $uid,"status" => 1)) -> field('username') -> select();
		$result = $model -> add_channel($data);
		if($result){
			$this -> writelog("添加了用户{$uid}的伪渠道id为{$cid}");

		} */
		if($from){
			Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/{$_GET['uid']}/key_word/{$key_word}/from/{$from}"); 
		}else{
			Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/{$_GET['uid']}/key_word/{$key_word}/");
		}
	}
	
	//伪渠道删除
	public function fake_channel_del(){
		$key_word = $_GET['key_word'];
		$cid = $_GET['cid'];
		$from = $_GET['from'];
		$uid = $_GET['uid'];
		$cids = $_SESSION['co_channel']['cid'];
		$cid_arr = explode(',',$cids);
		foreach($cid_arr as $key => $val){
			if($val != $cid){
				$cids_go .= $val.',';
			}
		}
		unset($_SESSION['co_channel']['cid']);
		$_SESSION['co_channel']['cid'] = substr($cids_go,0,strlen($cids_go) - 1);
 		$model = D('Cooperate.Channeluser');
		$result = $model -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 2,'cid' => $cid)) -> delete();
		if($from){
			if($key_word){
				Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/{$_GET['uid']}/key_word/{$key_word}/from/{$from}");
			}else{
				Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/{$_GET['uid']}/from/{$from}");
			}	
		}else{
			Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/{$_GET['uid']}/key_word/{$key_word}/"); 
		}
	}
	
	//添加渠道提交
	public function add_channel(){
		$uid = $_GET['uid'];
		$user_model = D('Cooperate.Channeluser');
		$from = $_GET['from'];
		$cids = $_SESSION['co_channel']['cid'];
		$cid_arr = explode(',',$cids);
		$hcid = $user_model -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 2)) -> select();
		foreach($hcid as $key => $val){
			$have_cid[] = $val['cid'];
		}
		if(!$cids && !$hcid){
			$this -> error("请选择渠道！");
		}
		foreach($cid_arr as $key => $val){
			if(!in_array($val,$hcid) && $val>0){
				$data_cid['cid'] = $val;
				$data_cid['uid'] = $uid;
				$data_cid['create_tm'] = time();
				$data_cid['update_tm'] = time();
				$data_cid['status'] = 2;
				$data_cid['create_uid'] = $_SESSION['admin']['admin_id'];
				mysql_query('START TRANSACTION'); //事务开始
				$result = $user_model -> add_channel($data_cid);
				if(!$result){
					mysql_query('ROLLBACK '); //若有一条不能执行 则回滚
				}
			}
		}
		mysql_query('COMMIT'); //执行事务
		$username_result = $user_model -> table('t_user') -> field('username') -> select();
		$status_result = $user_model -> table('t_user') -> where(array('id' => $uid)) -> field('status') -> select();
		if($status_result[0]['status'] == 1){
			$where_user['id'] = $uid;
			$where_user['status'] = 1;
			$data_user['status'] = 3;
			$user_result = $user_model -> save_user($where_user,$data_user);
			$data_node['node'] = 2;
			$data_node['update_tm'] = time();
			$data_node['create_tm'] = time();
			$data_node['uid'] = $uid;
			$data_node['status'] = 1;
			$node_result = $user_model -> add_node($data_node);
		}else{
			$where_node['uid'] = $uid;
			$where_node['status'] = 1;
			$data_node['node'] = 2;
			$data_node['update_tm'] = time();
			$node_result = $user_model -> save_node($where_node,$data_node);
		}
		if($node_result){
			unset($_SESSION['co_channel']['cid']);
			Header("Location: /index.php/" . GROUP_NAME . "/Channeluser/channel_config/uid/{$uid}/from/{$from}/"); 
		}else{
			$this -> error("添加错误");
		}
	}
	
	//重置渠道
	public function reset_channel(){
		$from = $_GET['from'];
		$model = D('Cooperate.Channeluser');
		$uid = $_GET['uid'];
		$have_channel = $model -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 2)) -> select();
	
		foreach($have_channel as $key => $val){
			$where['id'] = $val['id'];
			$result = $model -> table('t_user_channel') -> where($where) -> delete();
		}
		unset($_SESSION['co_channel']['cid']);
		Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/{$uid}/from/{$from}/");
	}
	
	//渠道配置列表
	public function channel_config(){
		$from = $_GET['from'];
		$ad_price = floatval(trim($_GET['ad_price'])) > 0 ? floatval(trim($_GET['ad_price'])) : 0;
		$max_down = trim($_GET['max_down']) > 0 ? trim($_GET['max_down']) : 0;
		$ad_cut_pre = floatval(trim($_GET['ad_cut_pre'])) > 0 ? floatval(trim($_GET['ad_cut_pre'])) : 0;
		$ad_cid = trim($_GET['ad_cid']);
		$game_cid = trim($_GET['game_cid']);
		$game_cut_pre = floatval(trim($_GET['game_cut_pre'])) > 0 ? floatval(trim($_GET['game_cut_pre'])) : 0;
		$uid = trim($_GET['uid']);
		$channel_model = D('Cooperate.Channeluser');
		$where['id'] = $uid;
		$username_result = $channel_model -> table('t_user') -> where($where) -> field('username,status') -> select();
		$where_channel['_string'] = "uid = {$uid} and status = 2";
		$cid_result = $channel_model -> table('t_user_channel') -> where($where_channel) -> field('cid') -> order ("create_tm DESC") -> select();
		$model = new Model();
		if(!$cid_result){
			Header("Location:/index.php/" . GROUP_NAME . "/Channeluser/select_channel/uid/{$uid}/from/{$from}/");
		}
		foreach($cid_result as $key => $val){
			$where_go['cid'] = $val['cid'];
			$time_result = $model -> table('sj_channel') -> where($where_go) -> field('submit_tm') -> select();
			$val['submit_tm'] = $time_result[0]['submit_tm'];
			$cid_results[$val['cid']] = $val['submit_tm'];
		}
		krsort($cid_results);
		foreach($cid_results as $key => $val){
			$cid_result_arr[]['cid'] = $key;
		}

		foreach($cid_result_arr as $key => $val){
			$model = new Model();
			$where_chname['cid'] = $val['cid'];
			$result_chname = $model -> table('sj_channel') -> where($where_chname) -> field('chname') -> select();
			$channel_arr[$key]['cid'] = $val['cid'];
			$channel_arr[$key]['chname'] = $result_chname[0]['chname'];
			if($ad_cid == $val['cid']){
				$channel_arr[$key]['ad_price'] = $ad_price;
				$channel_arr[$key]['max_down'] = $max_down;
				$channel_arr[$key]['ad_cut_pre'] = $ad_cut_pre;
			}
			if($game_cid == $val['cid']){
				$channel_arr[$key]['game_cut_pre'] = $game_cut_pre;
			}
		}
		
		$config = $channel_model -> table('t_init_config') -> where(array("status" => 1)) -> select();
		foreach($config as $key => $val){
			$configs[$val['code']] = $val;
		}
		$this -> assign("uid",$uid);
		$this -> assign('from',$from);
		$this -> assign("configs",$configs);
		$this -> assign("username",$username_result[0]['username']);
		$this -> assign("status",$username_result[0]['status']);
		$this -> assign("channel_arr",$channel_arr);
		$this -> display();
	}
	
	//渠道配置添加
	public function add_channel_config(){
		$ad_price = $_POST['ad_price'];
		$max_down = $_POST['max_down'];
		$ad_cut_pre = $_POST['ad_cut_pre'];
		$ad_cid = $_POST['ad_cid'];
		$game_cid = $_POST['game_cid'];
		$active_price = $_POST['active_price'];
		$active_switch = $_POST['active_switch'];
		$ad_switch = $_POST['ad_switch'];
		$game_switch = $_POST['game_switch'];
		$game_cut_pre = $_POST['game_cut_pre'];
		$uid = trim($_POST['uid']);
		$cid = $_POST['cid'];

		foreach($active_switch as $key => $val){
			$active_switch[$val] = $val;
		}
		foreach($cid as $key => $val){
			if(!in_array($val,$active_switch)){
				$active_switchs[$val] = 0;
			}else{
				$active_switchs[$val] = 1;
			}
			if(!in_array($val,$ad_switch)){
				$ad_switchs[$val] = 0;
			}else{
				$ad_switchs[$val] = 1;
			}
			if(!in_array($val,$game_switch)){
				$game_switchs[$val] = 0;
			}else{
				$game_switchs[$val] = 1;
			}
		}

		foreach($active_switchs as $key => $val){
			$active_switchsc[] = $val;
		}
		foreach($ad_switchs as $key => $val){
			$ad_switchsc[] = $val;
		}
		foreach($game_switchs as $key => $val){
			$game_switchsc[] = $val;
		}

		$user_model = D('Cooperate.Channeluser');
		$count = count($cid);
		$model = D('Cooperate.Channeluser');
		for($i=0;$i<$count;$i++){
			$data['cid'] = trim($cid[$i]);

			$data['active_price'] = floatval(trim($active_price[$i])) > 0 ? floatval(trim($active_price[$i])) : 0;
			if($this -> check_point(floatval(trim($active_price[$i])),4,10)){
				$this -> error("安智市场激活单价格式错误");
			}
			$data['active_switch'] = trim($active_switchsc[$i]) == 1 ? 1 : 0;
			if($data['active_switch'] == 1){
				if($data['active_price'] == ''){
					$this -> error("安智市场激活单价不能为空");
				}
			}
			$data['ad_price'] = floatval(trim($ad_price[$i])) > 0 ? floatval(trim($ad_price[$i])) : 0;
			
			if($this -> check_point(floatval(trim($ad_price[$i])),4,10)){
				$this -> error("单个软件下载单价格式错误");
			}
			$data['ad_switch'] = trim($ad_switchsc[$i]) == 1 ? 1 : 0;
			
			$data['max_down'] = trim($max_down[$i]) >= 0 ? trim($max_down[$i]) : 0;
			
			if($this -> check_point(trim($max_down[$i]),0,100)){
				$this -> error("防刷量值格式错误");
			}
			$data['ad_cut_pre'] = floatval(trim($ad_cut_pre[$i])) > 0 ? floatval(trim($ad_cut_pre[$i])) : 0;
			if($data['ad_switch'] == 1){
				if($data['ad_price'] == ''){
					$this -> error("单个软件下载单价不能为空");
				}
				if($data['max_down'] == ''){
					$this -> error("防刷量值不能为空");
				}
				
				if($_POST['ad_cut_pre'] == ''){
					$this -> error("广告分成扣量比例不能为空");
				}
			}

			$data['game_cut_pre'] = floatval(trim($game_cut_pre[$i])) > 0 ? floatval(trim($game_cut_pre[$i])) : 0;
			
			$data['game_switch'] = trim($game_switchsc[$i]) == 1 ? 1 : 0;
			if($data['game_switch'] == 1){
				if($_POST['game_cut_pre'] == ''){
					$this -> error("游戏分成扣量比例不能为空");
				}
			}
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['status'] = 1;

			if($data['active_switch'] != 1 && $data['ad_switch'] != 1 && $data['game_switch'] != 1){
				$this -> error("每种渠道至少选择一种分成方式");
			}
			
			
			if($active_switch[$i] == 1 && !$active_price[$i]){
				$this -> error("请填写相应安智市场激活单价");
			}
			if($data['cid']){
				$result = $model -> add_channel_config($data);
			}
			
			if($result){
				$this -> writelog("已添加了id为{$cid[$i]}的渠道配置");
			}
			$results[$i] = $result;
		}

		if($results){
			$where_node['uid'] = $uid;
			$data_node['status'] = 0;
			$node_result = $model -> save_node($where_node,$data_node);
			$data_user['status'] = 1;
			$where_user['id'] = $uid;
			$user_result = $model -> save_user($where_user,$data_user);
			//改变渠道状态
			$data_channel['status'] = 1;
			$where_channel['uid'] = $uid;
			$where_channel['status'] = 2;
			$channel_result = $model -> save_channel($where_channel,$data_channel);
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME. '/Channeluser/channel_list');
			$this -> success("添加成功");	
		}
	}

	//放弃创建
	function give_up(){
		$uid = $_GET['uid'];
		$model = D('Cooperate.Channeluser');
		$user_result = $model -> table('t_user') -> where(array("id" => $uid)) -> delete();
		$account_result = $model -> table('t_account') -> where(array("uid" => $uid)) -> delete();
		$channel_result = $model -> table('t_user_channel') -> where(array("uid" => $uid)) -> delete();
		$config_result = $model -> table('t_channel_config') -> where(array("uid" => $uid)) -> delete();
		$where_node['uid'] = $uid;
		$data_node['status'] = 0;
		$node_result = $model -> save_node($where_node,$data_node);
		if($user_result && $account_result){
			$this -> writelog("放弃了id为{$uid}的用户创建");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Channeluser/channel_list");
			$this -> success("操作成功");
		}
	}
	
	//检查是否保留指定小数点，范围是否正确
	protected function check_point($val,$point,$max){
		$str = explode(',',$val);
		$length = strlen($str[1]);
		if($length > $point || $val > $max){
			return true;
		}else{
			return false;
		}
	
	}
	
	
}