<?php
/**
 * 合作方账号管理
 */
class CoAccountAction extends CommonAction{
	// 账号列表
	function AccountList(){
		$D = D('Cooperative.CoAccount');
		$admin_id = $_SESSION['admin']['admin_id'];
		$userid_arr = $this -> cooperative_manager($admin_id);
		// 账号名称、负责人
		$limit = $_GET['lr'] ? $_GET['lr'] : 10;
		
		import("@.ORG.Page");
		if ($_GET['type'] && trim($_GET['names']) != ''){
			$search = trim($_GET['names']);
			if ($_GET['type'] == 'username'){
				$where['user_name']  = array('like', "%$search%");
			}
			if ($_GET['type'] == 'loginname'){
				$where['login_name']  = array('like', "%$search%");
			}
			$userid_str = substr($userid_arr,0,strlen($userid_arr) - 1);
			$where['uid'] = array('in',$userid_arr);
			$n = $D -> table("t_user") -> where($where) -> count();
			$Page = new Page($n, $limit);
			$list = $D -> table("t_user") -> join('t_charge ON t_user.charge_person = t_charge.id') -> where($where) -> field('t_user.*,t_charge.charge_name') -> order('status,create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
			$this->assign('type',$_GET['type']);
	
			$this->assign('names',$search); 
		} else {
			if($_GET['username']){
				$where['user_name'] = $_GET['username'];
			}
			if($_GET['charge']){
				$charge_result = $D -> table('t_charge') -> where(array('charge_name' => $_GET['charge'],'status' => 1)) -> select();
				$user_result = $D -> table('t_user') -> where(array('charge_person' => $charge_result[0]['id'])) -> select();
				foreach($user_result as $key => $val){
					$user_charge[] = $val['uid'];
				}
				if($user_charge){
					$userid_str_go = array_intersect($user_charge,$userid_arr);
				}else{
					$userid_str_go = $userid_arr;
				}
				foreach($userid_str_go as $key => $val){
					$userid_str .= $val.',';
				}
				$userid_str = substr($userid_str,0,strlen($userid_str) - 1);
				$where['uid'] = array('in',$userid_str);
			}else{
				$userid_str_go = $userid_arr;
				foreach($userid_str_go as $key => $val){
					$userid_str .= $val.',';
				}
				$userid_str = substr($userid_str,0,strlen($userid_str) - 1);
				$where['uid'] = array('in',$userid_str);
			}
			$n = $D -> table("t_user") -> where($where) -> count();
			$Page = new Page($n,10,$limit);
			$list = $D -> table("t_user") -> join('t_charge ON t_user.charge_person = t_charge.id') -> where($where) -> field('t_user.*,t_charge.charge_name') -> order('status,create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
			
		}
		foreach($list as $k => $v){
			
			$list[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
			switch ($v['status']){
				case 1:
					$list[$k]['stat'] = '正常';
					break;
				case 2:
					$list[$k]['stat'] = '暂停';
					break;
				default:
					break;
			}
		}
		
		foreach($list as $key => $val){
			$my_channel = $D -> table('t_user_channel') -> where(array('uid' => $val['uid'])) -> count();
			
			if($my_channel > 0){
				$val['ch_count'] = 1;
			}else{
				$val['ch_count'] = 0;
			}
			$list[$key] = $val;
		}

		//3个月内的日期
		$today = date("Y-m-d");
		$month_ago = date("Y-m-d",(time()-86400*90));
		
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');	
		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign('lr', $limit);
		$this -> assign("list",$list);
		$this->assign('today',$today);
		$this->assign('month_ago',$month_ago);
		$this -> display();
	}
	// 账号信息
	function accountInfo(){
		$D = D('Cooperative.CoAccount');
		
		if ($_GET['uid']){
			$uid = $_GET['uid'];
			$info = $D->accountInfo($uid);
			$re_result = $D -> table('t_user') -> where(array('uid' => $info['uid'])) -> select();
			$info['user_status'] = $re_result[0]['status'];
			$info['password'] = $this -> rc4_decode($info['password']);
			$charge = $D->table('t_charge')->where(array('id' => $info['charge_person']))->field('charge_name')->find();
			$channel_all = $D -> table('t_user_channel') -> where(array('uid' => $uid)) -> count();
			$channel_normal = $D -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 1)) -> count();
			$channel_stop = $D -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 3)) -> count();
			$channel_no = $D -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 0)) -> count();
			
			$channel_stop_user = $D -> table('t_user_channel') -> where(array('uid' => $uid,'status' => 4)) -> count();
			$yesterday = date('Ymd',(time() - 3600*24));
			$no_check = $D -> table('t_all_income') -> where(array('userid' => $uid,'theday' => $yesterday)) -> field('sum(new_income)') -> select();
	
			$been_check = $D -> table('t_all_income') -> where(array('userid' => $uid)) -> field('sum(new_income)') -> select();
			$option_income = $D -> table('t_settle_account') -> where(array('user_id' => $uid,'status' => 1)) -> field('sum(after_tax)') -> select();

			$finance_income = $D -> table('t_settle_account') -> where(array('user_id' => $uid,'status' => 2)) -> field('sum(after_tax)') -> select();
			$obliation_income = $D -> table('t_settle_account') -> where(array('user_id' => $uid,'status' => 3)) -> field('sum(after_tax)') -> select();
			$paid_income = $D -> table('t_settle_account') -> where(array('user_id' => $uid,'status' => 4)) -> field('sum(after_tax)') -> select();
			$freeze_income = $D -> table('t_settle_account') -> where(array('user_id' => $uid,'status' => 0)) -> field('sum(after_tax)') -> select();
			
			$this -> assign('uid',$uid);
			$this -> assign('channel_all',$channel_all);
			$this -> assign('channel_normal',$channel_normal);
			$this -> assign('channel_stop',$channel_stop);
			$this -> assign('channel_stop_user',$channel_stop_user);
			$this -> assign('channel_no',$channel_no);
			$this -> assign('no_check',$no_check);
			$this -> assign('been_check',$been_check);
			$this -> assign('option_income',$option_income);
			$this -> assign('finance_income',$finance_income);
			$this -> assign('obliation_income',$obliation_income);
			$this -> assign('paid_income',$paid_income);
			$this -> assign('freeze_income',$freeze_income);
			$this->assign('charge',$charge['charge_name']);
			$this->assign('info',$info);
			$this->display();
		}
	}
	// 结算配置
	function settlementConf(){
		
	}
	// 添加账号
	function addAccount(){
		if ($_POST){
			foreach ($_POST as $k=>$v){
				$_POST[$k] = trim($v);
			}
			
			$reception = (int) $_POST['r_value'];
			$income = (int) $_POST['i_value'];

			// 判断必填项
			if ($_POST['user_name'] == '' || $_POST['login_name'] == '' || $_POST['accountPassword'] == '' ||empty($_POST['accountCharge'])){
				$this->error('账号信息,不能为空！');			
			}
			if ($this->checkUser($_POST['user_name'])){
				$this->error('账号重名！');				
			}
			if ($this->checkLogin($_POST['login_name'])){
				$this->error('登录名重名！');			
			}
			if ($_POST['accountPassword'] != $_POST['re_accountPassword']){	// 判断两次密码是否一致
				$this->error('两次密码不一致！');				
			}
			//结算模式与展示类型配置
			if (empty($_POST['settlement_value']) || $reception <= 0|| $income <= 0) {
				$this->error('结算模式不能为空！');
			}
			// 结算信息
			if (empty($_POST['accountProperty'])||empty($_POST['accountName'])||empty($_POST['bankName'])||empty($_POST['bankAddr'])||empty($_POST['bankLocation'])||empty($_POST['bankAccount'])){
				$this->error('结算信息,不能为空！');			
			}

			if ($_FILES['bankFile']['size']){
				// 图片格式、图片大小
				if ($_FILES['bankFile']['size']>1048576 || !in_array($_FILES['bankFile']['type'], array('image/jpeg','image/jpg','image/png'))){
					$this->error('请按图片规则上传图片！');
				}
				
				$path = date('Ymd',time());
				$config = array(
				'multi_config' => array(
					'bankFile' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec'
						),
					),
				);
				$list = $this -> _uploadapk(0, $config);
				$news_url = $list['image'][0]['url'];
				
			}
			
			$data = array(
				'user_name'=>$_POST['user_name'],
				'login_name'=>$_POST['login_name'],
				'password'=>$this -> rc4_encode($_POST['accountPassword']),
				'charge_person'=>$_POST['accountCharge'],
				'create_person'=>$_SESSION['admin']['admin_id'],
			);
			$data['update_time'] = $data['create_time'] = time();
			$data['status'] = 1;
			$D = D('Cooperative.CoAccount');
			$uid = $D->addUser($data);

			if ($uid){
				$a_data = array(
					'bank_file' => $news_url,
					'uid'=>$uid,
					'account_name'=>$_POST['accountName'],
					'account_property'=>$_POST['accountProperty'],
//					'account_type'=>$_POST['accountType'],
					'bank_name'=>$_POST['bankName'],
					'bank_account'=>$_POST['bankAccount'],
					'bank_addr'=>$_POST['bankAddr'],
					'bank_location'=>$_POST['bankLocation'],
					'min_balance'=>$_POST['minBalance'],
					'settlement'=>$_POST['settlement_value'],
					'reception_reveal'=>$reception,
					'income_reveal'=>$income,
				);
				$a_data['create_time'] = $a_data['update_time'] = time();
				$a_data['status'] = 1;
				if ($result=$D->addAccount($a_data)){
			
				    //同时付权限给有负责人权限的用户
					$re_t = $D->table('t_manager_purview')->where("charge_type=1 and charge_value={$_POST['accountCharge']}")->select();
	
					foreach ($re_t as $purview) {
					    $data = array(
					        'aid' => $purview['aid'],
					        'charge_type' => 2,
					        'charge_value' => $uid,
					        'create_time' => time() 
					    );
					    $D->table('t_manager_purview')->add($data);
					}
					
					$this->writelog("添加了新账号为".$_POST['user_name']."，账号id为$uid"); 
					// 成功
					$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/CoAccount/AccountList');
					$this->success("添加成功！");	
				} else {
					$D->delUser($uid);
					// 添加失败
				}
			} else {
				// 添加失败
			}
			
		} else {
			$D = D('Cooperative.CoAccount');
			$charge = $D->allCharge();
			
			$this->assign('charge',$charge);
			$this->display();
		}
	}
	
	function checkUser($user){
		$D = D('Cooperative.CoAccount');
		if ($D->table('t_user')->where("user_name='{$user}'")->count()){
			return true;
		} else {
			return false;
		}
	}
	function checkLogin($login){
		$D = D('Cooperative.CoAccount');
		if ($D->table('t_user')->where("login_name='{$login}'")->count()){
			return 1;
		} else {
			return 0;
		}
	}
	// 账号列表－编辑
	function editAccount(){
		$D = D('Cooperative.CoAccount');
		if ($_GET['id']){
			$uid = $_GET['id'];
			$edit = $D->table('t_user')->where("uid={$uid}")->select();
			$chid = $edit[0]['charge_person'];
			$charge = $D->table('t_charge')->where("id={$chid}")->field('charge_name')->find();
			$edit[0]['charge_person'] = $charge['charge_name'];
			$edit[0]['password'] = $this -> rc4_decode($edit[0]['password']);
			$type = $_GET['type'];
			$names = $_GET['names'];
			$this -> assign('type',$type);
			$this -> assign('names',$names);
			$this->assign('edit',$edit[0]);
			$this->display();
		} elseif ($_POST['uid']){
			$have_user_result = $D -> table('t_user') -> where("user_name = '{$_POST['user_name']}' and uid != {$_POST['uid']}") -> select();
			
			$have_login_result = $D -> table('t_user') -> where("login_name = '{$_POST['login_name']}' and uid != {$_POST['uid']}") -> select();
			if($have_user_result){
				$this -> error("账号名称已存在");
			}
			if($have_login_result){
				$this -> error("登录名已存在");
			}
			if($_POST['user_name'] == ''){
				$this -> error("请填写账号名称");
			}
			if($_POST['login_name'] == ''){
				$this -> error("请填写登录名");
			}
			if($_POST['password'] == ''){
				$this -> error("请填写密码");
			}
		
			if($_POST['re_password'] == ''){
				$this -> error("请填写确认密码");
			}
			if($_POST['password'] && $_POST['re_password'] && $_POST['password'] != $_POST['re_password']){
				$this -> error("两次输入密码不同");
			}
			
			$data = array(
				'user_name'=>$_POST['user_name'],
				'login_name'=>$_POST['login_name'],
				'password'=> trim($this -> rc4_encode($_POST['password'])),
				'contact_name'=>$_POST['contact_name'],
				'contact_position'=>$_POST['contact_position'],
				'contact_mobile'=>$_POST['contact_mobile'],
				'contact_phone'=>$_POST['contact_phone'],
				'contact_other'=>$_POST['contact_other'],
				'contact_email'=>$_POST['contact_email'],
				'contact_id'=>$_POST['contact_id'],
			);
			if($_FILES['contact_id_file']['size']){
				if($_FILES['contact_id_file']['size'] > 1024*1024){
					$this -> error("上传扫描件尺寸大于1M");
				}
				
				if($_FILES['contact_id_file']['type'] != 'image/jpg' && $_FILES['contact_id_file']['type'] != 'image/jpeg' && $_FILES['contact_id_file']['type'] != 'image/png'){
					$this -> error("上传扫描件格式错误");
				}
				$path = date('Ymd',time());
				$config = array(
				'multi_config' => array(
					'contact_id_file' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec'
						),
					),
				);
				$list = $this -> _uploadapk(0, $config);
				$news_url = $list['image'][0]['url'];
				$data['contact_id_file'] = $news_url;
			}
			$data['update_time'] = time();
			$D = D('Cooperative.CoAccount');
			$where['uid'] = $_POST['uid'];
			$log_model = D('Cooperative.SysManager');
			
			$log_all_need = $log_model -> logcheck(array('uid' => $_POST['uid']),'t_user',array('user_name'=> $_POST['user_name'],'login_name' => $_POST['login_name'],'password' => $this -> rc4_encode($_POST['password']),'contact_name' => $_POST['contact_name'],'contact_position' => $_POST['contact_position'],'contact_mobile' => $_POST['contact_mobile'],'contactPhone' => $_POST['contactPhone'],'contact_other' => $_POST['contact_other'],'contact_email' => $_POST['contact_email'],'contact_id' => $_POST['contact_id'],'contact_id_file' => $_POST['contact_id_file']));
			foreach($log_all_need as $key => $val){
				if($key != 'contact_id_file'){
					$msg .= "{$val[0]}(编辑前:{$val[1]};编辑后{$val[2]}),";
				}
			}
			$have_result = $log_model -> table('t_user') -> where(array('uid' => $_POST['uid'])) -> select();
			if($_FILES['contact_id_file']['size']){
				$msg_pic = ",身份证扫描件(编辑前:{$have_result[0]['contact_id_file']},编辑后:{$news_url})";
			}
			$result = $D -> editAccount($where,$data);
			$type = $_POST['type'];
			$names = $_POST['names'];
			if ($result){
				$this -> writelog("编辑账号id:{$_POST['uid']}的信息,".$msg.$msg_pic);
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/CoAccount/AccountList/type/{$type}/names/{$names}");
				$this->success("编辑成功！");
			}else{
				$this -> error("编辑失败");
			}
		}
	}
	// 账号列表－结算配置
	function editSettlement(){
		if ($_GET['id']){
			$uid = $_GET['id'];
			$D = D('Cooperative.CoAccount');
			$account = $D->table('t_account')->where(array('uid' => $uid,'status' => 1))->find();
			$username = $D->table('t_user')->where(array('uid' => $uid))->find();
			$account['user_name'] = $username['user_name'];
			$account['charge_person'] = $username['charge_person'];
			$account['contact_id_file'] = $username['contact_id_file'];
			$charge = $D -> table('t_charge') -> where(array('status' => 1)) -> select();
			$type = $_GET['type'];
			$names = $_GET['names'];
			$this -> assign('type',$type);
			$this -> assign('names',$names);
			$this -> assign('charge',$charge);
			$this->assign('account',$account);
			$this->display();
		} elseif ($_POST['uid']){
			if(isset($_POST['settlement_patterns']) && !empty($_POST['settlement_patterns'])){
				$data['settlement_patterns'] = $_POST['settlement_patterns'];
			}else if(isset($_POST['settlement_patterns_1']) && !empty($_POST['settlement_patterns_1'])){
				$data['settlement_patterns'] = $_POST['settlement_patterns_1'];
			}else{
				$this -> error("请选择结算模式");
			}
			
			if(empty($_POST['reception_reveal'])){
				$this -> error("请选择前端展示类型");
			}
			if(empty($_POST['income_reveal'])){
				$this -> error("请选择收入值展示类型");
			}
			
			if(empty($_POST['bank_name']) || strlen($_POST['bank_name']) > 50 || strlen($_POST['bank_name']) <2){
				$this -> error("请输入2-50个字符的中文、英文大小写及数字内的所属银行");
			}
			if(empty($_POST['bank_addr']) || strlen($_POST['bank_addr']) > 50 || strlen($_POST['bank_addr']) <2){
				$this -> error("请输入2-50个字符的中文、英文大小写及数字内的开户行");
			}
			if(empty($_POST['bank_location']) || strlen($_POST['bank_location']) > 100 || strlen($_POST['bank_location']) <2){
				$this -> error("请输入2-100个字符的中文、英文大小写及数字内的开户行所在地");
			}
			if(empty($_POST['bank_account'])){
				$this -> error("请输入数字格式的银行账号");
			}

			if(($_POST['reception_reveal']&$_POST['income_reveal']) != $_POST['income_reveal']){
				$this -> error("前台展示类型范围需大于或等于收入值展示类型");
			}
			
			$bankFilePath = '';

			if ($_FILES['bankFile']['size']){
				// 图片格式、图片大小
				if ($_FILES['bankFile']['size']>1024*1024){
					$this->error('请上传小于1M的银行卡扫描件');
				}
				if(!in_array($_FILES['bankFile']['type'], array('image/jpeg','image/jpg','image/png'))){
					$this->error('上传银行卡扫描件格式错误');
				}
				
				$path = date('Ymd',time());
				$config = array(
				'multi_config' => array(
					'bankFile' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec'
						),
					),
				);
				$list = $this -> _uploadapk(0, $config);
				$news_url = $list['image'][0]['url'];
				$bankFilePath = $news_url;
			}	

			$settlement_patterns = $data['settlement_patterns'];
			$reception_reveal = $_POST['reception_reveal'];
			$income_reveal = $_POST['income_reveal'];
			$uid = $_POST['uid'];
			$where['uid'] = $uid;
			$data = array(
				'settlement'=>$data['settlement_patterns'],
				'reception_reveal' => $reception_reveal,
				'income_reveal' => $income_reveal,
				'min_balance' => $_POST['min_pay'],
				'account_property'=>$_POST['account_property'],
				'account_name'=>$_POST['account_name'],
				'bank_name'=>$_POST['bank_name'],
				'bank_location'=>$_POST['bank_location'],
				'bank_account'=>$_POST['bank_account'],
				'bank_addr' => $_POST['bank_addr']
			);
			
			if($_FILES['bankFile']['size']){
				$data['bank_file'] = $bankFilePath;
			}

			$log_model = D('Cooperative.SysManager');
			$log_all_need = $log_model -> logcheck(array('uid' => $uid),'t_account',array('settlement'=> $_POST['settlement_patterns'],'reception_reveal' => $reception_reveal,'income_reveal' => $income_reveal,'account_property' => $_POST['account_property'],'account_name' => $_POST['account_name'],'bank_name' => $_POST['bank_name'],'bank_location' => $_POST['bank_location'],'bank_account' => $_POST['bank_account'],'bank_file' => $_POST['bank_file'],'min_balance' => $_POST['min_pay']));
			foreach($log_all_need as $key => $val){
				if($key != 'settlement' && $key != 'reception_reveal' && $key != 'income_reveal' && $key != 'bank_file'){
					$msg .= "{$val[0]}(编辑前:{$val[1]};编辑后{$val[2]}),";
				}
			}
			
			//结算信息
			$settlement_arr = array(1=>'A',2=>'B',3=>'C',4=>'A+B',5=>'A+C',6=>'B+C',7=>'A+B+C',8=>'A与B',9=>'A与C',10=>'B与C',11=>'A与B+C',12=>'B与A+C',13=>'C与A+B');
			//前台展示类型
			$pre_show = array(1=>'安智市场激活收入',2=>'广告分成收入',3=>'安智市场激活收入与广告分成收入',4=>'游戏分成收入',5=>'安智市场激活收入与游戏分成收入',6=>'广告分成收入与游戏分成收入',7=>'安智市场激活收入,广告分成收入与游戏分成收入');
			
			$username_result = $log_model -> table('t_user') -> where(array('uid'=> $uid)) -> select();
			$account_result = $log_model -> table('t_account') -> where(array('uid' => $uid)) -> select();
			
			
			if($account_result[0]['settlement'] != $settlement_patterns){
				$msg .= ",结算模式(编辑前:{$settlement_arr[$account_result[0]['settlement']]},编辑后:{$settlement_arr[$settlement_patterns]})";
			}
			if($account_result[0]['reception_reveal'] != $reception_reveal){
				$msg .= ",前台展示类型(编辑前:{$pre_show[$account_result[0]['reception_reveal']]},编辑后:{$pre_show[$reception_reveal]})";
			}
			if($_FILES['bankFile']['size']){
				$msg .= ",银行卡扫描件(编辑前:{$account_result[0]['bank_file']},编辑后:{$bankFilePath})";
			}
			
			if($account_result[0]['income_reveal'] != $income_reveal){
				$msg .= ",收入值展示类型(编辑前:{$pre_show[$account_result[0]['income_reveal']]},编辑后:{$pre_show[$income_reveal]})";
			}
			
			$D = D('Cooperative.CoAccount');
			$result = $D -> editSettlement($where,$data);

			$data_user['charge_person'] = $_POST['charge'];
			//给该负责人所属的管理员权限
			$admin_result = $D -> table('t_manager_purview') -> where(array('charge_type' => 1,'charge_value' => $_POST['charge'])) -> select();
			$admin_model = D('Cooperative.SysManager');
			foreach($admin_result as $key => $val){
				$admin_data['aid'] = $val['aid'];
				$admin_data['charge_type'] = 2;
				$admin_data['charge_value'] = $_POST['uid'];
				$admin_add_result = $admin_model -> diffAccount($admin_data);
			}
	
			if($username_result[0]['charge_person'] != $_POST['charge']){
				$charge_before = $D -> table('t_charge') -> where(array('id' => $username_result[0]['charge_person'])) -> select();
				$charge_update = $D -> table('t_charge') -> where(array('id' => $_POST['charge'])) -> select();
				$msg_charge = "负责人(编辑前:{$charge_before[0]['charge_name']},编辑后:{$charge_update[0]['charge_name']})";
			}
		
			$data_user['update_time'] = time();
			$where_user['uid'] = $uid;
			$result_user = $D -> editAccount($where_user,$data_user);
			$type = $_POST['type'];
			$names = $_POST['names'];
			if ($result || $result_user){
				if($msg){
					$this -> writelog("编辑结算信息(账号:{$username_result[0]['user_name']})".$msg.$msg_charge);
				}
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/CoAccount/AccountList/type/{$type}/names/{$names}");
				$this->success("编辑成功！");			
			}else{
				$this -> error("编辑失败！");
			}
		}
	}
	
	//修改结算模式 改变展示
	function ajax_settle(){

			if($_GET['settlement_patterns'] == 1){
				$settlement_arr = array(1);
			}elseif($_GET['settlement_patterns'] == 2){
				$settlement_arr = array(2);
			}elseif($_GET['settlement_patterns'] == 3){
				$settlement_arr = array(4);
			}elseif($_GET['settlement_patterns'] == 4){
				$settlement_arr = array(1,2);
			}elseif($_GET['settlement_patterns'] == 5){
				$settlement_arr = array(1,4);
			}elseif($_GET['settlement_patterns'] == 6){
				$settlement_arr = array(2,4);
			}elseif($_GET['settlement_patterns'] == 7){
				$settlement_arr = array(1,2,4);
			}elseif($_GET['settlement_patterns'] == 8){
				$settlement_arr = array(1,2);
			}elseif($_GET['settlement_patterns'] == 9){
				$settlement_arr = array(1,4);
			}elseif($_GET['settlement_patterns'] == 10){
				$settlement_arr = array(2,4);
			}elseif($_GET['settlement_patterns'] == 11){
				$settlement_arr = array(1,2,4);
			}elseif($_GET['settlement_patterns'] == 12){
				$settlement_arr = array(1,2,4);
			}elseif($_GET['settlement_patterns'] == 13){
				$settlement_arr = array(1,2,4);
			}
			$uid = $_GET['uid'];
			$co_model = D('Cooperative.CoAccount');
			$have_result = $co_model -> table('t_account') -> where(array('uid' => $uid)) -> select();
			$data = array('settlement_arr_data' => $settlement_arr);
			echo json_encode($data);
			return json_encode($data);
	}
	
	
	// 停用账号
	function stopAccount(){
		$co_model = D('Cooperative.CoAccount');
		$uid = $_GET['uid'];
		$status = $_GET['status'];
		$user_name = $co_model -> table('t_user') -> where(array('uid' => $uid)) -> select();
		$type = $_GET['type'];
		$names = $_GET['names'];
		if($status == 2){
			$where_user['uid'] = $uid;
			$where_user['status'] = 1;
			$data_user['status'] = 2;
			$data_user['stop_tm'] = time();
			$channel_been_result = $co_model -> table('t_user_channel') -> where(array('uid' => $uid)) -> select();
			foreach($channel_been_result as $key => $val){
				$where_channel['uid'] = $val['uid'];
				$where_channel['cid'] = $val['cid'];
				$data_channel['status'] = 4;
				$data_channel['pre_status'] = $val['status'];
				$data_channel['update_time'] = time();
				$channel_result = $co_model -> editChannel($where_channel,$data_channel);
				$where_config['cid'] = $val['cid'];
				$data_config['status'] = 0;
				$config_result = $co_model -> editChannel_config($where_config,$data_config);
			}
			$user_result = $co_model -> stopAccount($where_user,$data_user);
			$msg = "停用";
		}elseif($status == 1){
			$where_user['uid'] = $uid;
			$where_user['status'] = 2;
			$data_user['status'] = 1;
			$channel_been_result = $co_model -> table('t_user_channel') -> where(array('uid' => $uid)) -> select();
			foreach($channel_been_result as $key => $val){
				$where_channel['uid'] = $val['uid'];
				$where_channel['cid'] = $val['cid'];
				$data_channel['status'] = $val['pre_status'];
				$data_channel['pre_status'] = 0;
				$data_channel['update_time'] = time();
				$channel_result = $co_model -> editChannel($where_channel,$data_channel);
				$where_config['cid'] = $val['cid'];
				$data_config['status'] = 1;
				$config_result = $co_model -> editChannel_config($where_config,$data_config);
			}
			$user_result = $co_model -> stopAccount($where_user,$data_user);
			$msg = "正常";
		}
		if($user_result){
			$this -> writelog("已编辑账号:{$user_name[0]['user_name']}的状态为{$msg}");
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/CoAccount/AccountList/type/{$type}/names/{$names}");
			$this->success("编辑成功！");
		}else{
			$this->error("编辑失败！");
		}
	}
	
	// 恢复账号
	function restoreAccount(){
		if ($_GET['uid']){
			$this->assign('uid',$_GET['uid']);
			$this->display();
		} else {
			$uid = $_POST['uid'];
			$data['status'] = 1;
			$data['update_time'] = time();
			$D = D('Cooperative.CoAccount');
			if ($D->table('t_user')->where("uid=$uid")->save($data)){
				if ($D->table('t_user_channel')->where("uid=$uid AND status=1")->save(array('status'=>1))){
					echo json_encode(1);
				} else {
					echo json_encode(0);
				}
			}
		}
	}
	// 渠道列表　查看账号渠道
	function channelList(){
		$D = D('Cooperative.CoAccount');
	    ini_set("display_errors", 1);error_reporting(E_ERROR);
		$limit = $_GET['lr'] ? $_GET['lr'] : 10;
		$where = array();
		$admin_id = $_SESSION['admin']['admin_id'];
		$where_admin['aid'] = $admin_id;
		$where_admin['charge_type'] = 2;
		$user_result = $D -> table('t_manager_purview') -> where($where_admin) -> select();
	
		$admin_result = $D -> table('t_manager') -> where(array('aid' => $admin_id,'status' => 1)) -> select();
		if($admin_result){
			foreach($user_result as $key => $val){
				$userid_arr_go .= $val['charge_value'].',';
			}
			$userid_arr = substr($userid_arr_go,0,strlen($userid_arr_go) - 1);
		}else{
			$userid_arr = '';
		}
		
		
		if ($_GET['charge']) {
		    $charge_where .= " and charge_person = {$_GET['charge']}";
		    $this->assign('charge_person',$_GET['charge']);
		}
	
		if ($user = trim($_GET['user'])) {
		    $charge_where .= " and user_name like '%{$user}%'";
			$user_need = $D -> table('t_user') -> where(array('user_name' => $_GET['user'])) -> select();
			$this -> assign('suid',$user_need[0]['uid']);
		    $this->assign('user_name',$user);
		}

		if ($charge_where && !$_GET['uid']) {
		    $where['uid'] = 0;
			$userid = $D->table('t_user')->where("status != 0 and uid in ({$userid_arr})".$charge_where)->field('uid')->select();
			
			foreach ($userid as $row ){
				$user_id[] = $row['uid'];
			}
			$uids = implode(",", $user_id);
			if ($uids){
				$where['uid'] = array('in',$uids);
			}
		}elseif(empty($charge_where) && !$_GET['uid']){
			$where['uid'] = array('in',$userid_arr);
		}elseif(empty($charge_where) && $_GET['uid']){
			$where['uid'] = $_GET['uid'];
			$user_result = $D -> table('t_user') -> where(array('uid' => $_GET['uid'])) -> select();
			$this -> assign('user_name',$user_result[0]['user_name']);
		}elseif($charge_where && $_GET['uid']){
			$where['uid'] = $_GET['uid'];
			$user_result = $D -> table('t_user') -> where(array('uid' => $_GET['uid'])) -> select();
			$this -> assign('user_name',$user_result[0]['user_name']);
		}
		if ($channels = trim($_GET['channel'])){
		    $where['cid'] = 0;
			$m = M('channel');
			$chid = $m->where("chname like '%{$channels}%'")->field('cid')->select();
		
			foreach($chid as $key => $val){
				$chid_go .= $val['cid'].',';
			}
			$chid_str = substr($chid_go,0,strlen($chid_go) - 1);
		
			if ($chid){
				$where['cid'] = array('in',$chid_str);
			}
			$this->assign('chname',$channels);
		}
		
		if($_GET['status']){
			$where['status'] = $_GET['status'];
		}
				
		import("@.ORG.Page");
		if ($where){
			$n = $D -> table("t_user_channel")->where($where)->count();
			$Page = new Page($n, $limit);
			$channel = $D->table("t_user_channel")->where($where)->order('status,create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
			
		} else {
			$n = $D -> table("t_user_channel") -> count();
			$Page = new Page($n, $limit);
			$channel = $D->table("t_user_channel")->order('status,create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
			
		}
		
		foreach ($channel as $k=>$v){
			$uid[] = $v['uid'];
			$cid[] = $v['cid'];
			$channel[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
			switch ($v['status']){
				case 0:
					$channel[$k]['stat'] = '未启用';
					break;
				case 1:
					$channel[$k]['stat'] = '正常';
					break;
				case 2:
					$channel[$k]['stat'] = '未添加';
					break;
				case 3:
					$channel[$k]['stat'] = '暂停';
					break;
				case 4:
					$channel[$k]['stat'] = '账号暂停';
					break;
				default :
					break;
			}
		}
		$uid = array_unique($uid);
		$cid = array_unique($cid);
		$uid = implode(",", $uid);
		$cid = implode(",", $cid);
		$userCharge = $D->table('t_user tu')->join('t_charge tc ON tc.id=tu.charge_person')->where("tu.uid IN ({$uid})")->field('tu.uid,tu.user_name,tu.charge_person,tc.charge_name')->select();
		$ch = M('channel ch');
		$cc = $ch->join('sj_channel_category scc ON scc.category_id=ch.category_id')->where("ch.cid IN ({$cid})")->field('ch.cid,ch.chname,scc.name')->select();	// 渠道和分类
		foreach ($channel as $key=>$val){
			foreach ($userCharge as $row){
				if ($val['uid']==$row['uid']){
					$channel[$key]['user'] = $row['user_name'];
					$channel[$key]['charge'] = $row['charge_name'];
					$channel[$key]['chargeid'] = $row['charge_person'];
				}
			}
			foreach ($cc as $r){
				if ($r['cid']==$val['cid']){
					$channel[$key]['chname'] = $r['chname'];
					$channel[$key]['ch_category'] = $r['name'];
				}
			}
		}
		
		//3个月内的日期
		$today = date("Y-m-d");
		$month_ago = date("Y-m-d",(time()-86400*90));
	
		$charge = $D->table('t_charge')->where('status=1')->field('id,charge_name')->select();
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');	
		$show = $Page->show();
		$this -> assign('uid',$_GET['uid']);
		$this->assign("page", $show);
		$this->assign('lr', $limit);
		$this->assign('charge',$charge);
		$this->assign('month_ago', $month_ago);
		$this->assign('today',$today);
		$this -> assign("channel",$channel);
		$this -> display();
	}
	// 添加渠道显示
	function addChannel(){
		$search = '';
		$category = M('channel_category');
		$cy = $category->where('status=1')->field('category_id,name')->select();
		
		$channel = M('channel');
		$ch = $channel->where('status=1')->field('cid,chname,category_id,activation_type,platform')->select();
		$co_model = D('Cooperative.CoAccount');
		$have_result = $co_model -> table('t_user_channel') -> select();
		foreach ($ch as $key=>$value){
			switch ($value['platform']){
				case 1:
					$ch[$key]['platform'] = '手机客户端';
					break;
				case 4:
					$ch[$key]['platform'] = '平板';
					break;
				case 5:
					$ch[$key]['platform'] = '游戏客户端';
					break;
				default:
					break;
			}
			switch ($value['activation_type']){
				case 1:
					$ch[$key]['activation_type'] = '普通';
					break;
				case 2:
					$ch[$key]['activation_type'] = '严格';
					break;
				case 4:
					$ch[$key]['activation_type'] = '非山寨';
					break;
				case 8:
					$ch[$key]['activation_type'] = '山寨';
					break;
				default:
					break;
			}
			if ($search){		// 搜索渠道
				if ($search == $value['chname']){
					$ch[$key]['search'] = 1;
				}
			}
		}
		
		foreach ($cy as $k=>$v){
			$nval = array();
			$cval = array();
			foreach ($ch as $val){
				if ($val['category_id']==$v['category_id']){
					$nval['channel'][] = $val;
				}else if($val['category_id'] == 0){
					$cval['channel'][] = $val;
				}
			}
			$cys[0]['category_id'] = 0;
			$cys[0]['name'] = '未分类';
			$cys[0]['channel'] = $cval['channel'];
			$cys[$v['category_id']] = $v;
			$cys[$v['category_id']]['channel'] = $nval['channel'];
		}
	
		foreach($cys as $k => $v){
			$v['count'] = count($v['channel']);
			$cys[$k] = $v;
		}
		
		//渠道状态
		foreach($cys as $key => $val){
			foreach($val['channel'] as $k => $v){
				$have_result = $co_model -> table('t_user_channel') -> where(array('cid' => $v['cid'])) -> select();
				if($have_result){
					$v['user_status'] = $have_result[0]['status'];
					if($have_result[0]['status'] == 1){
						$v['stat'] = '正常';
					}elseif($have_result[0]['status'] == 2){
						$v['stat'] = '未添加';
					}elseif($have_result[0]['status'] == 0){
						$v['stat'] = '未启用';
					}elseif($have_result[0]['status'] == 3){
						$v['stat'] = '暂停';
					}elseif($have_result[0]['status'] == 4){
						$v['stat'] = '账号暂停';
					}
					
					$user_result = $co_model -> table('t_user') -> where(array('uid' => $have_result[0]['uid'])) -> select();
					$v['user_name'] = $user_result[0]['user_name'];
					$v['create_tm'] = $have_result[0]['create_time'];
					$charge_result = $co_model -> table('t_charge') -> where(array('id' => $user_result[0]['charge_person'])) -> select();
					$v['charge_name'] = $charge_result[0]['charge_name'];
				}else{
					$v['user_status'] = 2;
					$v['stat'] = '未添加';
				}
				$val['channel'][$k] = $v;
			}
			$cys[$key] = $val;
		}
		$my_uid = $_GET['uid'];
		$my_result = $co_model -> table('t_user') -> where(array('uid' => $my_uid,'status' => 1)) -> select();
		$er_cid = $_GET['er_cid'];
		$this -> assign('er_cid',$er_cid);
		$this -> assign('username',$my_result[0]['user_name']);
		$this->assign('categoryChannel',$cys);
		$this->display();
	}
	
	//添加渠道提交
	function addChannel_do(){
			$model = New Model();
			$D = D('Cooperative.CoAccount');
			if($_GET['username'] != ''){
				$user_result = $D -> table('t_user') -> where(array('user_name' => $_GET['username'])) -> select();
				
				if($user_result){
					echo 1;
					return json_encode(1);
				}else{
					echo 2;
					return json_encode(2);
				}
			}
	
			
			if($_POST['username'] == ''){
				$this -> error('请填写账号名称');
			}
			if(empty($_POST['cid'])){
				$this -> error('请选择渠道');
			}
			if ($_POST['cid'] && $_POST['username'] != ''){
			
			$cid = $_POST['cid'];
			$username = trim($_POST['username']);
			$r = $D->table('t_user')->where("user_name='{$username}'")->select();

			$config = $D -> table('t_init_config') -> select();
			if ($r[0]['uid']){
				foreach($cid as $key => $val){
					$channel_result = $model -> table('sj_channel') -> where(array('cid' => $val)) -> select();
					$data['uid'] = $r[0]['uid'];
					$data['cid'] = $val;
					$data['create_time'] = time();
					$data['update_time'] = time();
					if($r[0]['status'] == 1){
						$data['status'] = 0;
					}elseif($r[0]['status'] == 2){
						$data['status'] = 4;
					}
					$data_config['cid'] = $val;
					$data_config['ad_price'] = $config[0]['ad_price'];
					$data_config['ad_max_down'] = $config[0]['ad_max_down'];
					$data_config['ad_pre'] = $config[0]['ad_pre'];
					$data_config['game_pre'] = $config[0]['game_pre'];
					$data_config['create_time'] = time();
					$data_config['update_time'] = time();
					$data_config['status'] = 1;
					$config_result = $D -> addChannel_config($data_config);
					$result = $D -> addChannel($data);
					if($result){
						$results[] = $result;
						$msg .= "添加渠道(账号:{$_POST['username']}),渠道名:{$channel_result[0]['chname']}";	
					}else{
						$error_cid[] = $val;
						continue;
					}
				}
			}
			
			if($error_cid){
				foreach($error_cid as $key => $val){
					$ch_name = $model -> table('sj_channel') -> where(array('cid' => $val)) -> select();
					
					$error_chname .= $ch_name[0]['chname'].',';
				}
				$this -> writelog($msg);
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/CoAccount/addChannel');
				$this -> error("渠道{$error_chname}已被添加");
				
			}
			if($results){
				$this -> writelog($msg);
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/CoAccount/channelList');
				$this->success("添加成功！");
			}else{
				$this -> error("添加失败");
			}
		}
	
	}
	
	// 编辑用户渠道配置
	function editChannel(){
		if ($_GET['id']){
			$uid = $_GET['uid'];
			$id = $_GET['id'];
			$D = D('Cooperative.CoAccount');
			$ch = $D->table("t_user_channel")->where("id={$id}")->field('id,uid,cid')->select();
			$username = $D->table('t_user')->where("uid={$ch[0]['uid']}")->field('user_name')->select();
			$mod = M('channel');
			$chname = $mod->where("cid={$ch[0]['cid']}")->field('chname')->select();
			$userConf = $D->table('t_channel_config')->where("cid={$ch[0]['cid']} AND status=1")->select();
			$charge = $_GET['charge'];
			$user = $_GET['user'];
			$chnames = $_GET['chname'];
			$this->assign('charge',$charge);
			$this->assign('users',$user);
			$this->assign('chnames',$chnames);
			$this->assign('cid',$ch[0]['cid']);
			$this->assign('uid',$uid);
			$this->assign('conf',$userConf[0]);
			$this->assign('user',$username[0]['user_name']);
			$this->assign('chname',$chname[0]['chname']);
			$this->display();
		}
	}	
		
	// 编辑用户渠道配置
	function editChannel_do(){
		$D = D('Cooperative.CoAccount');
		$where['cid'] = $_POST['cid'];
		$cid = $_POST['cid'];
		$uid = $_POST['uid'];

		$data = array(
			'active_price'=>$_POST['active_price'],
			'ad_price'=>$_POST['ad_price'],
			'ad_max_down'=>$_POST['ad_max_down'],
			'ad_pre'=>$_POST['ad_pre'],
			'game_pre'=>$_POST['game_pre'],
		);
		
		if($_POST['active_price'] == '' || $this -> check_point(floatval(trim($_POST['active_price'])),2,10) || !is_numeric($_POST['active_price'])){
			$this -> error("安智市场激活单价格式错误");
		}
		if(empty($_POST['ad_price']) || $this -> check_point(floatval(trim($_POST['ad_price'])),2,10) || !is_numeric($_POST['ad_price'])){
			$this -> error("单个软件下载单价格式错误");
		}
		if(empty($_POST['ad_max_down']) || $this -> check_point(trim($_POST['ad_max_down']),0,100) || !is_numeric($_POST['ad_max_down'])){
			$this -> error("防刷量值格式错误");
		}
		if(empty($_POST['ad_pre']) || $this -> check_point(trim($_POST['ad_pre']),2,100) || !is_numeric($_POST['ad_pre'])){
			$this -> error("广告分成扣量比例");
		}
		if(empty($_POST['game_pre']) || $this -> check_point(trim($_POST['game_pre']),2,100) || !is_numeric($_POST['game_pre'])){
			$this -> error("游戏分成扣量比例");
		}
		$data['update_time'] = time();
		if($_POST['active_price'] != '' && $_POST['ad_price'] != '' && $_POST['ad_max_down'] != '' && $_POST['ad_pre'] != '' && $_POST['game_pre'] != ''){
			//查询是否已为正常渠道,只更新一次
			$channel_result_been = $D -> table('t_channel_config') -> where(array('cid' => $cid,'status' => 1)) -> select();
			if($channel_result_been[0]['active_price'] == 0){
				$where_cid['cid'] = $cid;
				$data_cid['status'] = 1;
				$data_cid['pre_status'] = 0;
				$data_cid['update_time'] = time();
				$channel_result = $D -> editChannel($where_cid,$data_cid);
			}
		}else{
			$this -> error("渠道配置系数不能为空");
		}
		$log_model = D('Cooperative.SysManager');
		$bef_result = $D -> table('t_channel_config') -> where(array('cid' => $cid,'status' => 1)) -> select();
		$log_all_need = $log_model -> logcheck(array('id' => $bef_result[0]['id']),'t_channel_config',array('active_price'=> $_POST['active_price'],'ad_price' => $_POST['ad_price'],'ad_max_down' => $_POST['ad_max_down'],'ad_pre' => $_POST['ad_pre'],'game_pre' => $_POST['game_pre']));
		if($log_all_need){
			foreach($log_all_need as $key => $val){
				$msg .= "{$val[0]}(编辑前:{$val[1]};编辑后{$val[2]}),";
			}
		}
		$model = new Model();
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		$result = $D-> editChannel_config($where,$data);
		$charge = $_POST['charge'];
		$user = $_POST['user'];
		$chname = $_POST['chname'];
	
		if($result){
			if($log_all_need){
				$this -> writelog("编辑渠道配置(渠道:{$channel_result[0]['chname']})".$msg);
			}
			if($charge){
				$go .= "charge/{$charge}/";
			}
			if($user){
				$go .= "user/{$user}/";
			}
		
			if($chname){
				$go .= "channel/{$chname}/";
			}
		
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/CoAccount/channelList/".$go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	// 渠道配置删除
	function delChannel(){
		$co_model = D('Cooperative.CoAccount');
		$model = new Model();
		$id = $_GET['id'];
		$uid = $_GET['uid'];
		$channel_result = $co_model -> table('t_user_channel') -> where(array('id' => $id)) -> select();
		$where['cid'] = $channel_result[0]['cid'];
		$config_result = $co_model -> table('t_channel_config') -> where($where) -> delete();
		$channel_name = $model -> table('sj_channel') -> where(array('cid' => $channel_result[0]['cid'])) -> select();
		$result = $co_model -> table('t_user_channel') -> where(array('id' => $id)) -> delete();
		
		if($result){
			$this -> writelog("已删除渠道(渠道名称:{$channel_name[0]['chname']})");
			$this -> assign('jumpUrl', "/index.php/" . GROUP_NAME . "/CoAccount/channelList/uid/{$uid}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	// 渠道配置停用
	function stopChannel(){
		$co_model = D('Cooperative.CoAccount');
		$model = new Model();
		$uid = $_GET['uid'];
		$id = $_GET['id'];
		$charge = $_GET['charge'];
		$user = $_GET['user'];
		$chname = $_GET['chname'];
		$channel_result = $co_model -> table('t_user_channel') -> where(array('id' => $id)) -> select();
		$channel_name = $model -> table('sj_channel') -> where(array('cid' => $channel_result[0]['cid'])) -> select();
		if($_GET['status'] == 3){
			$data['status'] = 3;
			$data['pre_status'] = 1;
			$data_config['status'] = 0;
		}elseif($_GET['status'] == 1){
			$data['status'] = 1;
			$data['pre_status'] = 3;
			$data_config['status'] = 1;
		}
		$where['id'] = $id;
		$data['update_time'] = time();
		$where_config['cid'] = $channel_result[0]['cid'];
		$data_config['update_time'] = time();
		$result = $co_model -> editChannel($where,$data);	
		$result_config = $co_model -> editChannel_config($where_config,$data_config);
		if($result && $result_config){
			$this -> writelog("已停用渠道{$channel_name[0]['chname']}");
			if($charge){
				$go .= "/charge/{$charge}";
			}
			if($user){
				$go .= "/user/{$user}";
			}
			if($chname){
				$go .= "/channel/{$chname}";
			}
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/CoAccount/channelList/uid/{$uid}".$go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	// 渠道配置　点击名称　显示配置信息
	function showChannel(){
		if ($_GET['id']){
			$id = $_GET['id'];
			$D = D('Cooperative.CoAccount');
			$ch = $D->table("t_user_channel")->where("id={$id}")->field('id,uid,cid')->select();
			$username = $D->table('t_user')->where("uid={$ch[0]['uid']}")->field('user_name')->select();
			$mod = M('channel');
			$chname = $mod->where("cid={$ch[0]['cid']}")->field('chname')->select();
			$userConf = $D->table('t_channel_config')->where("cid={$ch[0]['cid']}")->select();
	
			$this->assign('cid',$ch[0]['cid']);
			$this->assign('conf',$userConf[0]);
			$this->assign('user',$username[0]['user_name']);
			$this->assign('chname',$chname[0]['chname']);
			$this->display();			
		}
	}
	function checkRule(){
	    ini_set("display_errors", 1);error_reporting(E_ALL);
		$error = array();
		$D = D('Cooperative.CoAccount');
		if ($_POST['user_name'] == ''){
			$error['user_name'] = 1;
		}
	    if ($_POST['login_name'] == ''){
			$error['login_name'] = 1;
		}
		if (!empty($error)) {
		    echo json_encode($error);
		    return ;
		}
		
		if ($_POST['user_name'] && $_POST['uid']){
			$n = $D->table('t_user')->where("user_name='{$_POST['user_name']}' AND uid<>{$_POST['uid']} AND status=1")->count();
			if ($n){
				$error['user_name'] = 1;
			}
		} elseif($_POST['user_name']) {
			$n = $D->table('t_user')->where("user_name='{$_POST['user_name']}' AND status=1")->count();
			if ($n){
				$error['user_name'] = 1;
			}
		}
		if ($_POST['login_name'] && $_POST['uid']){
			$n = $D->table('t_user')->where("login_name='{$_POST['login_name']}' AND uid<>{$_POST['uid']} AND status=1")->count();
			if ($n){
				$error['login_name'] = 1;
			}
		} elseif($_POST['login_name']) {
			$n = $D->table('t_user')->where("login_name='{$_POST['login_name']}' AND status=1")->count();
			if ($n){
				$error['login_name'] = 1;
			}
		}
		
	    if (!empty($error)) {
		    echo json_encode($error);
		    return ;
		} else {
		    echo json_encode(0);
		    return ;
		}
	}
	
	function check_have(){
		$D = D('Cooperative.CoAccount');
		if($_POST['username']){
			$n = $D->table('t_user')->where("user_name='{$_POST['username']}'")->count();
		
			if ($n){
				$error['username'] = 1;
			}
		}
		if($_POST['loginname']){
			$n = $D->table('t_user')->where("login_name='{$_POST['loginname']}'")->count();
			if ($n){
				$error['loginname'] = 1;
			}
		}
		
		if (!empty($error)) {
		    echo json_encode($error);
		    return ;
		} else {
		    echo json_encode(0);
		    return ;
		}
	
	}
	
	/*
	 * 加密，可逆
	 * 可接受任何字符
	 * 安全度非常高
	 */
function rc4($pass, $data)
{
	$key[] ='';
	$box[] ='';
	$cipher='';
	$pass_length = strlen($pass);
	$data_length = strlen($data);
	for ($i = 0; $i < 256; $i++) {
		$key[$i] = ord($pass[$i % $pass_length]);
		$box[$i] = $i;
	}
	for ($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $key[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for ($a = $j = $i = 0; $i < $data_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$k = $box[(($box[$a] + $box[$j]) % 256)];
		$cipher.= chr(ord($data[$i]) ^ $k);
	}
	return $cipher;
}

function rc4_encode($data, $pass = 'cooperative')
{
	if ($pass == '') $pass = '$(^7hgAn';
	$data_str = json_encode($data);
	return base64_encode($this -> rc4($pass, $data_str));
}

function rc4_decode($data, $pass = 'cooperative')
{
	if ($pass == '') $pass = '$(^7hgAn';
	$data_str = base64_decode($data);
	return json_decode($this -> rc4($pass, $data_str), true);
}
	
	//检查是否保留指定小数点，范围是否正确
	protected function check_point($val,$point,$max){
		$str = explode('.',$val);
		$length = strlen($str[1]);
		if($length > $point || $val > $max || $val < 0){
			return true;
		}else{
			return false;
		}
	
	}
	
}