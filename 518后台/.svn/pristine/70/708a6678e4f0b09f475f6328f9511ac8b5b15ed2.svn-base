<?php
class Clientlist_pAction extends CommonAction{

	function client_list(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$models = new Model();
		
		$admin_id = $_SESSION['admin']['admin_id'];
		$admin_filter_result = $models -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		foreach($admin_filter_result as $key => $val){
			$admin_cid[] = $val['target_value'];
		}
		$all_client = $model -> table('co_client_list') -> order('id') -> select();
	
		if($admin_cid){
			foreach($all_client as $key => $val){
				$client_channel_result = $model -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> select();
				$my_cid_power = array();
				foreach($client_channel_result as $k => $v){
					$my_cid_power[] = $v['cid'];
				}
				if(!array_diff($my_cid_power,$admin_cid)){
					$my_client_id_str .= $val['id'].',';
				}
			}
		}else{
			$my_client_id = '';
		}
		$my_client_id = substr($my_client_id_str,0,-1);
		$count_power_result = $models -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
	
		if($count_power_result['filter_type'] == 2){
			$where_go .= " and id in({$my_client_id})";
		}
		$search_type = $_GET['search_type'];
		$search_need = trim($_GET['search_need']);
		if($search_type == 1 && $search_need){
			$where_go .= " and client_name like '%{$search_need}%'";
		}elseif($search_type == 2 && $search_need){
			$login_where = "is_parent = 1 and login_name like '%{$search_need}%'";
			$login_result = $model -> table('co_account') -> where($login_where) -> select();
			foreach($login_result as $key => $val){
				$client_str_go .= $val['client_id'].',';
			}
			$client_str = substr($client_str_go,0,-1);
			$where_go .= " and id in ({$client_str})";
		}
		
		$charge_id = $_GET['charge_id'];
		if($charge_id){
			$where_go .= " and charge_id = {$charge_id}";
		}
		$where['_string'] = "status !=0".$where_go; 
		$count = $model -> table('co_client_list') -> where($where) -> order('status,create_tm DESC') -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 50, $param);
		$result = $model -> table('co_client_list') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('status,create_tm DESC') -> select();
        $User = M("config");
        $huilv_no = $User->table('pu_config')->where(array('config_type' => 'savepersonal_no'))->select();//个人不扣税
		$huilv = $User->table('pu_config')->where(array('config_type' => 'savepersonal'))->select();//个人扣税
		$business_no = $User->table('pu_config')->where(array('config_type' => 'savebusiness_no'))->select();//企业不扣税
		$business = $User->table('pu_config')->where(array('config_type' => 'savebusiness'))->select();//企业扣税
		$company = $User->table('pu_config')->where(array('config_type' => 'savecompany'))->select();//公司税率
        foreach($result as $key => $val){
			$channel_count = $model -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> count();
			$login_result = $model -> table('co_account') -> where(array('is_parent' => 1,'client_id' => $val['id'])) -> select();
			$val['login_name'] = $login_result[0]['login_name'];
			$val['create_time'] = date('Y-m-d H:i:s',$val['create_tm']);
			$val['channel_num'] = $channel_count;
			$charge_name = $model -> table('co_charge') -> where(array('id' => $val['charge_id'])) -> select();
			$val['charge_name'] = $charge_name[0]['charge_name'];
			$result[$key] = $val;
                        if($val['account_attr']==1){
                            $result[$key]['account_attr_name']='企业(不扣税)'.$business_no[0]['configcontent'].'%';
                        }else if($val['account_attr']==2){
                            $result[$key]['account_attr_name']='个人(扣税)'.$huilv[0]['configcontent'].'%';
                        }else if($val['account_attr']==3){
                            $result[$key]['account_attr_name']='个人(不扣税)'.$huilv_no[0]['configcontent'].'%';
                        }else if($val['account_attr']==5){
                            $result[$key]['account_attr_name']='企业(扣税)'.$business[0]['configcontent'].'%';
                        }else if($val['account_attr']==6){
                            $result[$key]['account_attr_name']='公司税率'.$company[0]['configcontent'].'%';
                        }else if($val['account_attr']==4){
                            $result[$key]['account_attr_name']='论坛';
                        }
		}
			
		$charge_result = $model -> table('co_charge') -> where(array('status' => 1)) -> select();
		
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('charge_result',$charge_result);
		$this -> assign('result',$result);
		$this -> assign('search_need',$search_need);
		$this -> assign('charge_id',$charge_id);
		$this -> assign('search_type',$search_type);
		$this -> display();
	}
	
	function add_client_show(){
        $User = M("config");
        $huilv_no = $User->table('pu_config')->where(array('config_type' => 'savepersonal_no'))->select();//个人不扣税
		$huilv = $User->table('pu_config')->where(array('config_type' => 'savepersonal'))->select();//个人扣税
		$business_no = $User->table('pu_config')->where(array('config_type' => 'savebusiness_no'))->select();//企业不扣税
		$business = $User->table('pu_config')->where(array('config_type' => 'savebusiness'))->select();//企业扣税
		$company = $User->table('pu_config')->where(array('config_type' => 'savecompany'))->select();//企业扣税
        $this->assign('company', $company[0]['configcontent']);
        $this->assign('huilv_no', $huilv_no[0]['configcontent']);
		$this->assign('huilv', $huilv[0]['configcontent']);
		$this->assign('business_no', $business_no[0]['configcontent']);
		$this->assign('business', $business[0]['configcontent']);
                
            
		$model = D('Channel_cooperation.channel_cooperation_p');
		$charge_result = $model -> table('co_charge') -> where(array('status' => 1)) -> select();
		$admin_id = $_SESSION['admin']['admin_id'];
		$my_time = microtime();
		$my_hash = md5($admin_id . $my_time);
		
		$this -> assign('charge_result',$charge_result);
		$this -> assign('my_hash',$my_hash);
		$this -> display();
	}

	
	function add_client_do(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$client_name = $_POST['client_name'];
		if(!$client_name){
			$this -> error("请填写客户名称");
		}
		$have_client_where['_string'] = "client_name = '{$client_name}' and status != 0";
		$have_client_result = $model -> table('co_client_list') -> where($have_client_where) -> select();
		if($have_client_result){
			$this -> error("该用户名已存在");
		}
		$login_name = $_POST['login_name'];

		if(!$login_name || strlen($login_name) > 20 || strlen($login_name) < 3 || !preg_match("/^[_0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u",$login_name)){
			$this -> error("请填写符合规则的登录名");
		}
		$have_login_where['_string'] = "login_name = '{$login_name}' and status != 0";
		$have_login_result = $model -> table('co_account') -> where($have_login_where) -> select();
		$hash = $_POST['my_hash'];
		if($have_login_result){
			$this -> error("该登录名已存在");
		}
		$password = md5($_POST['password']);

		if(!$password || strlen($_POST['password']) > 32 || strlen($_POST['password']) < 8){
			$this -> error("请填写符合规则的登录密码");
		}
		if(!preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z\#\!@\$\%\^\(\)]{8,32}$/",$_POST['password'])){
			$this -> error('密码必须是大小字符和数字的混合，长度在8-16之间，请重新设置');
		}
		$rpassword = $_POST['rpassword'];
		if(!$rpassword){
			$this -> error("请填写重复密码");
		}
		if($_POST['password'] != $_POST['rpassword']){
			$this -> error("两次输入密码不一致");
		}
		$charge_id = $_POST['charge_id'];
		if(!$_POST['charge_id']){
			$this -> error("请选择负责人");
		}
		
		$account_attr = $_POST['account_attr'];
	
		if(!$account_attr){
			$this -> error("请选择账号属性");
		}
		
		$account_gathering = $_POST['account_gathering'];
		$opening_bank = $_POST['opening_bank'];
		$bank_account = $_POST['bank_account'];
		$contract_num = $_POST['contract_num'];
		$company_name = $_POST['company_name'];
		$company_addr = $_POST['company_addr'];
		$linkman = $_POST['linkman'];
		$linkman_pos = $_POST['linkman_pos'];
		$linkman_phone = $_POST['linkman_phone'];
		if($linkman_phone){
			if(!preg_match("/1[3458]{1}\d{9}$/",$linkman_phone) && !preg_match("/^(0(10|21|22|23|[1-9][0-9]{2})(-|))?[0-9]{7,8}$/",$linkman_phone)){
				$this -> error("电话号码格式错误");
			}
		}
		$linkman_email = $_POST['linkman_email'];
		$comment = $_POST['comment'];
		$data = array(
			'client_name' => $client_name,
			'charge_id' => $charge_id,
			'account_attr' => $account_attr,
			'account_gathering' => $account_gathering,
			'opening_bank' => $opening_bank,
			'bank_account' => $bank_account,
			'contract_num' => $contract_num,
			'company_name' => $company_name,
			'company_addr' => $company_addr,
			'linkman' => $linkman,
			'linkman_pos' => $linkman_pos,
			'linkman_phone' => $linkman_phone,
			'linkman_email' => $linkman_email,
			'comment' => $comment,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1
		);
		
		$result = $model -> add_client($data);
		if($result){
			$account_data = array(
				'client_id' => $result,
				'login_name' => $login_name,
				'create_tm' => time(),
				'update_tm' => time(),
				'is_parent' => 1,
				'password' => $password,
				'spassword' => $_POST['password'],
				'status' => 1
			);
			$account_data = $model -> table('co_account') -> add($account_data);
		}
		if($result){
			$my_go = C('COOPERATION_FILE');
			$my_tmp_go = C('COOPERATION_TIMP_FILE');
			$tmp_affix = $model -> table('co_tmp_affix') -> where(array('hash' => $hash,'status' => 1)) -> select();
			foreach($tmp_affix as $key => $val){
				$tmp_affix_type_arr = array_reverse(explode('.',$val['affix_name']));
				$tmp_affix_type = $tmp_affix_type_arr[0];
				$my_time = date('Ym/d');
				$tmp_dir = $my_go .'/'.$my_time;
				if(!is_dir($tmp_dir)){
					@mkdir($tmp_dir,0777,true);
				}
				if(copy($my_tmp_go .'/'.$val['file_name'],$tmp_dir.'/'.time().'.'.$tmp_affix_type)){
					$affix_data = array(
						'client_id' => $result,
						'affix_name' => $val['affix_name'],
						'affix_url' => $tmp_dir.'/'.time().'.'.$tmp_affix_type,
						'status' => 1
					);
					$affix_result = $model -> add_affix($affix_data);

				}else{
					echo "上传文件失败";exit;
				}
			}
		}
		$go = $_POST['go'];
		if($result){
			$this -> writelog(" 客户列表：已添加客户ID为{$result}，名称为{$client_name}", 'co_client_list', $result,__ACTION__ ,'','add');
			if($go == 1){
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/client_list');
			}else{
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/add_channel_show/id/'.$result);
			}
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
		
	}

	function add_power_show(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
		$result = $model -> table('co_client_list') -> where(array('id' => $id)) -> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_power_do(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
		$where['id'] = $_GET['id'];
		$data = array(
			'feedback_status' => $_GET['feedback_status'],
			'download_status' => $_GET['download_status'],
			'update_tm' => time()
		);
		$log_result = $this -> logcheck(array('id' => $id),'co_client_list',$data,$model);
		$result = $model -> edit_client($where,$data);

		if($result){
			$this -> writelog(" 客户列表：已编辑id为{$id}的渠道合作客户权限".$log_result, 'co_client_list', $id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/client_list');
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	function ajax_upload_file(){
		$my_tmp_go = C('COOPERATION_TIMP_FILE');
		
		$model = D('Channel_cooperation.channel_cooperation_p');
		$affix = $_FILES['affix'];
		$id = $_GET['id'];
		$affix_type_arr = array_reverse(explode('.',$affix['name']));
		$affix_type = $affix_type_arr[0];
		$hash = $_GET['hash'];
		$allow_type = array('docx','doc','xlsx','xls','pdf','jpg','png','zip','rar');
		if(!in_array($affix_type,$allow_type)){
			$ret = array('code' => 2,'msg' => '上传文件失败,文件格式错误');
			echo json_encode($ret);
			return json_encode($ret);
		}
		$tmp_filename = $affix['name'];
		$the_tmp_filename = time().'.'.$affix_type;
		$tmp_dir = $my_tmp_go .'/';
		if(!is_dir($tmp_dir)){
			@mkdir($tmp_dir,0777,true);
		}
			
		if (file_exists($tmp_dir . $tmp_filename)){
			$ret = array('code' => 4,'msg' => '上传文件失败,文件已存在');
			echo json_encode($ret);
			return json_encode($ret);
		}else{
		  if(move_uploaded_file($affix["tmp_name"], $tmp_dir . $the_tmp_filename)){
			$affix_data = array(
				'hash' => $hash,
				'affix_name' => $tmp_filename,
				'file_name' => $the_tmp_filename,
				'create_tm' => time(),
				'status' => 1
			);
			$tmp_affix_result = $model -> add_tmp_affix($affix_data);
		
			if($tmp_affix_result){
				$tmp_result = $model -> table('co_tmp_affix') -> where(array('hash' => $hash,'status' => 1)) -> select();
				if($id){
					$have_result = $model -> table('co_affix') -> where(array('client_id' => $id,'status' => 1)) -> select();
					if($have_result){
						$result = array_merge($have_result,$tmp_result);
					}else{
						$result = $tmp_result;
					}
				}else{
					$result = $tmp_result;
				}
				
				$ret = array('code' => 1,'msg' => $result);
				echo json_encode($ret);
				return json_encode($ret);
			}
		  }else{
				$ret = array('code' => 3,'msg' => '上传文件失败');
				echo json_encode($ret);
				return json_encode($ret);
		  }
		}
	}
	
	function del_file(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
		$from = $_GET['from'];
		
		$hash = $_GET['hash'];
		
		$data = array(
			'status' => 0
		);
		$client_id = $_GET['client_id'];
	
		if($from == 1){
			$where['id'] = $id;
			$where['hash'] = $hash;
			$update_result = $model -> edit_tmp_affix($where,$data);
			
		}elseif($from == 2){
			$where['id'] = $id;
			$log_result = $this -> logcheck(array('id' => $id),'co_affix',$data,$model);
			$update_result = $model -> edit_affix($where,$data);
			if($update_result){
				$this -> writelog("客户列表：已编辑id为{$id}的附件".$log_result, 'co_affix', $id,__ACTION__ ,'','edit');
			}
		}
		if($update_result){
			$tmp_result = $model -> table('co_tmp_affix') -> where(array('hash' => $hash,'status' => 1)) -> select();
			if($client_id){
				$have_result = $model -> table('co_affix') -> where(array('client_id' => $client_id,'status' => 1)) -> select();
			}
			if($tmp_result && $have_result){
				$result = array_merge($have_result,$tmp_result);
			}elseif(!$tmp_result && $have_result){
				$result = $have_result;
			}elseif($tmp_result && !$have_result){
				$result = $tmp_result;
			}
			echo json_encode($result);
			return json_encode($result);
		}
	}
	
	function edit_client_show(){
        $User = M("config");
        $huilv_no = $User->table('pu_config')->where(array('config_type' => 'savepersonal_no'))->select();//个人不扣税
		$huilv = $User->table('pu_config')->where(array('config_type' => 'savepersonal'))->select();//个人扣税
		$business_no = $User->table('pu_config')->where(array('config_type' => 'savebusiness_no'))->select();//企业不扣税
		$business = $User->table('pu_config')->where(array('config_type' => 'savebusiness'))->select();//企业扣税
        $company = $User->table('pu_config')->where(array('config_type' => 'savecompany'))->select();//公司税率
        $this->assign('company', $company[0]['configcontent']);        
        $this->assign('huilv_no', $huilv_no[0]['configcontent']);
		$this->assign('huilv', $huilv[0]['configcontent']);
		$this->assign('business', $business[0]['configcontent']);
		$this->assign('business_no', $business_no[0]['configcontent']);
            
		$model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
		$result = $model -> table('co_client_list') -> where(array('id' => $id)) -> select();
		foreach($result as $key => $val){
			$login_result = $model -> table('co_account') -> where(array('is_parent' => 1,'client_id' => $val['id'])) -> select();
			$val['login_name'] = $login_result[0]['login_name'];
			$val['spassword'] = $login_result[0]['spassword'];
			$result[$key] = $val;
		}
		$charge_result = $model -> table('co_charge') -> where(array('id' => $result[0]['charge_id'])) -> select();
		$affix_result = $model -> table('co_affix') -> where(array('client_id' => $id,'status' => 1)) -> select();
		$admin_id = $_SESSION['admin']['admin_id'];
		$my_time = microtime();
		$my_hash = md5($admin_id . $my_time);
	
		$this -> assign('my_hash',$my_hash);
		$this -> assign('charge_result',$charge_result);
		$this -> assign('result',$result);
		$this -> assign('affix_result',$affix_result);
		$this -> display();
	
	}
	
	
	function edit_client_do(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$client_name = $_POST['client_name'];
		$id = $_POST['id'];
		$have_client_where['_string'] = "client_name = '{$client_name}' and status != 0 and id != {$id}";
		$have_client_result = $model -> table('co_client_list') -> where($have_client_where) -> select();
		if($have_client_result){
			$this -> error("该用户名已存在");
		}
		$login_name = $_POST['login_name'];
		if($login_name){
			if(strlen($login_name) > 20 || strlen($login_name) < 3 || !preg_match("/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u",$login_name)){
				$this -> error("请填写符合规则的登录名");
			}
		}
		$have_login_where['_string'] = "login_name = '{$login_name}' and status != 0 and id != {$id}";
		$have_login_result = $model -> table('co_client_list') -> where($have_login_where) -> select();
		$hash = $_POST['my_hash'];
		
		if($_POST['passwords']){
			if(!$_POST['passwords'] || strlen($_POST['passwords']) > 32 || strlen($_POST['passwords']) < 8){
				$this -> error("请填写符合规则的登录密码");
			}
			if(!preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z\#\!@\$\%\^\(\)]{8,32}$/",$_POST['passwords'])){
				$this -> error('密码必须是大小字符和数字的混合，长度在8-16之间，请重新设置');
			}
		}
		
		$rpassword = $_POST['rpassword'];
	
		if($_POST['passwords']){
			if(!$_POST['rpassword']){
				$this -> error("请填写重复密码");
			}
		}
		if($_POST['passwords'] != $_POST['rpassword']){
			$this -> error("两次输入密码不一致");
		}
		$been_result = $model -> table('co_account') -> where(array('client_id' => $id,'is_parent' => 1)) -> select();
		if($_POST['passwords']){
			$password = md5($_POST['passwords']);
			$spassword = $_POST['passwords'];
		}else{
			$password = $been_result[0]['password'];
			$spassword = $been_result[0]['spassword'];
		}
		$charge_id = $_POST['charge_id'];
		if(!$_POST['charge_id']){
			$this -> error("请选择负责人");
		}
		$account_attr = $_POST['account_attr'];
		if(!$account_attr){
			$this -> error("请选择账号属性");
		}
		$account_gathering = $_POST['account_gathering'];
		$opening_bank = $_POST['opening_bank'];
		$bank_account = $_POST['bank_account'];
		$contract_num = $_POST['contract_num'];
		$company_name = $_POST['company_name'];
		$company_addr = $_POST['company_addr'];
		$linkman = $_POST['linkman'];
		$linkman_pos = $_POST['linkman_pos'];
		$linkman_phone = $_POST['linkman_phone'];
		if($linkman_phone){
			if(!preg_match("/1[3458]{1}\d{9}$/",$linkman_phone) && !preg_match("/^(0(10|21|22|23|[1-9][0-9]{2})(-|))?[0-9]{7,8}$/",$linkman_phone)){
				$this -> error("电话号码格式错误");
			}
		}
		
		$linkman_email = $_POST['linkman_email'];
		$comment = $_POST['comment'];
		$data = array(
			'account_gathering' => $account_gathering,
			'opening_bank' => $opening_bank,
			'account_attr' => $account_attr,
			'bank_account' => $bank_account,
			'contract_num' => $contract_num,
			'company_name' => $company_name,
			'company_addr' => $company_addr,
			'linkman' => $linkman,
			'linkman_pos' => $linkman_pos,
			'linkman_phone' => $linkman_phone,
			'linkman_email' => $linkman_email,
			'comment' => $comment,
			'update_tm' => time(),
		);
		$where['id'] = $id;
		$log_result = $this -> logcheck(array('id' => $id),'co_client_list',$data,$model);
		
		$result = $model -> edit_client($where,$data);
	
		if($result){
			$account_data = array(
				'password' => $password,
				'spassword' => $spassword,
				'update_tm' => time()
			);
			$account_log = $this -> logcheck(array('client_id' => $id,'is_parent' => 1),'co_account',$account_data,$model);
			$account_result = $model -> table('co_account') -> where(array('client_id' => $id,'is_parent' => 1)) -> save($account_data);
			if($account_result){
				$this -> writelog("客户列表：已编辑id为{$id}的客户".$account_log, 'co_account', $id,__ACTION__ ,'','edit');
			}
		}
	
		if($result){
			$my_go = C('COOPERATION_FILE');
			$my_tmp_go = C('COOPERATION_TIMP_FILE');
			$tmp_affix = $model -> table('co_tmp_affix') -> where(array('hash' => $hash,'status' => 1)) -> select();
			$my_time = date('Ym/d');
			$tmp_dir = $my_go .'/'.$my_time;
			if(!is_dir($tmp_dir)){
				@mkdir($tmp_dir,0777,true);
			}
		
			foreach($tmp_affix as $key => $val){
				$tmp_affix_type_arr = array_reverse(explode('.',$val['affix_name']));
				$tmp_affix_type = $tmp_affix_type_arr[0];
				
				if(copy($my_tmp_go.'/'.$val['file_name'],$tmp_dir.'/'.time().'.'.$tmp_affix_type)){
					$affix_data = array(
						'client_id' => $id,
						'affix_name' => $val['affix_name'],
						'affix_url' => $tmp_dir.'/'.time().'.'.$tmp_affix_type,
						'status' => 1
					);
					$affix_result = $model -> add_affix($affix_data);

				}else{
					echo "上传文件失败";exit;
				}
			}
		}
		$go = $_POST['go'];
		if($result){

			$this -> writelog("客户列表：已编辑id为{$id}的渠道合作客户".$log_result, 'co_account', $id,__ACTION__ ,'','edit');
			if($go == 1){
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/client_list');
			}else{
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/add_channel_show/id/'.$id);
			}
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	}
	
	
	
	function upload_file(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
		$from = $_GET['from'];
		if(!$from){
			$from = 1;
		}
		//下载临时文件
		if($from == 1){
			$result = $model -> table('co_tmp_affix') -> where(array('id' => $id)) -> select();
			$file = "/tmp/channel_cooperation/{$result[0]['file_name']}";
			$file_dir = "/tmp/channel_cooperation/";
			$file_name = $result[0]['affix_name'];
		}elseif($from == 2){
			$result = $model -> table('co_affix') -> where(array('id' => $id)) -> select();
		
			$file = $result[0]['affix_url'];
			$file_name = $result[0]['affix_name'];
		}
		if($file_name){
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){  
				$file_names = urlencode($file_name); 
			}else{
				$file_names = $file_name;
			}
		}
		if(!file_exists($file)){
			$this -> error("文件不存在");
		}else{
			$open_file = fopen($file,"r");
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: ".filesize($file));
			Header("Content-Disposition: attachment; filename=" . $file_names);
			// 输出文件内容
			echo fread($open_file,filesize($file));
			fclose($file);
			exit();
		}
		
	}
	
	function change_status(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
		$where['id'] = $id;
		$channel_num = $model -> table('co_client_channel') -> where(array('client_id' => $id)) -> count();
		if(!$_GET['status']){
			if($channel_num){
				$this -> error("该客户存在关联渠道，不可删除!");
			}
		}
		if($_GET['status'] == 2 || $_GET['status'] == 0){
		    $co_account_where = array('client_id' => $id,'status' => 1);
		} else {
		    $co_account_where = array('client_id' => $id,'status' => 2);
		}
	
		$account_data = array(
			'status' => $_GET['status'],
			'update_tm' => time()
		);
		$account_result = $model -> table('co_account') -> where($co_account_where) -> save($account_data);
		$data = array('status' => $_GET['status'],'update_tm' => time());
		$log_result = $this -> logcheck(array('id' => $id),'co_client_list',$data,$model);
		$result = $model -> edit_client($where,$data);
		if($result){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/client_list');
			if($_GET['status']==0){
				$this -> writelog("客户列表：已删除id为{$id}的渠道合作用户".$log_result, 'co_client_list', $id,__ACTION__ ,'','del');
				$this -> success("删除成功");
			}else{
				$this -> writelog("客户列表：已编辑id为{$id}的渠道合作用户".$log_result, 'co_client_list', $id,__ACTION__ ,'','edit');
				$this -> success("编辑成功");
			}


		}else{
			$this -> error("编辑失败");
		}
	}
	
	function client_detail(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$id = $_GET['id'];
		$result = $model -> table('co_client_list') -> where(array('id' => $id)) -> select();
		$charge_result = $model -> table('co_charge') -> where(array('id' => $result[0]['charge_id'])) -> select();
		foreach($result as $key => $val){
			$login_result = $model -> table('co_account') -> where(array('client_id' => $id,'is_parent' => 1)) -> select();
			$val['login_name'] = $login_result[0]['login_name'];
			$val['spassword'] = $login_result[0]['spassword'];
			$result[$key] = $val;
		}
		$affix_result = $model -> table('co_affix') -> where(array('client_id' => $id,'status' => 1)) -> select();
		$this -> assign('charge_result',$charge_result);
		$this -> assign('result',$result);
		$this -> assign('affix_result',$affix_result);
		$this -> display();
	
	}
	
	
	function add_channel_show(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$id = $_GET['id'];
		if($_GET['channel_name']){
			$where_go .= " and chname like '%{$_GET['channel_name']}%'";
		}
		$client_result = $client_model -> table('co_client_list') -> where(array('id' => $id)) -> select();
		$admin_id = $client_model -> table('co_charge') -> where(array('id' => $client_result[0]['charge_id'])) -> select();
		$where['_string'] = "status = 1 and co_status_p=3 ".$where_go;
		//屏蔽用户开关新增
		$channel_id=$client_model->check_general_switch_cid($admin_id[0]['admin_id']);
		if($channel_id){
			$where['_string'] .= "and cid in ({$channel_id})";
		}
		//屏蔽用户开关新增
		$channel_count = $model -> table('sj_channel') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($channel_count, 10, $param);
		$channel_result = $model -> table('sj_channel') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('submit_tm DESC') -> select();
		// echo $model ->getlastsql();
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('id',$id);
		$this -> assign('channel_name',$_GET['channel_name']);
		$this -> assign('client_result',$client_result);
		$this -> assign('channel_result',$channel_result);
		$this -> display();
	}
	
	
	function add_channel(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$cid_arr = $_POST['cid'];
		$settle_attr = $_POST['settle_attr'];
		if(!$settle_attr)
		{
			$this -> error("请选择渠道的结算类型");
		}
				
		if(!$_POST['cid']){
			$this -> error("请选择渠道");
		}
		$id = $_POST['id'];
		$client_result = $client_model -> table('co_client_list') -> where(array('id' => $id)) -> select();
		$charge_result = $client_model -> table('co_charge') -> where(array('id' => $client_result[0]['charge_id'])) -> select();
		foreach($cid_arr as $key => $val){
			$admin_where['_string'] = "source_type = 1 and source_value = {$charge_result[0]['admin_id']} and target_type = 2 and target_value = {$val} and filter_type = 2";
			$admin_result = $model -> table('sj_admin_filter') -> where($admin_where) -> select();
			if(!$admin_result){
				$my_channel_result = $model -> table('sj_channel') -> where(array('cid' => $val)) -> select();
				$this -> error("该用户所属负责人没有此渠道查看权限,渠道名称:{$my_channel_result[0]['chname']},渠道id:{$val}");
			}
			$data = array(
				'client_id' => $id,
				'cid' => $val,
				'create_tm' => time(),
				'update_tm' => time(),
				'status' => 1
			);
			$result = $client_model -> add_channel($data);
			if($result){
				$channel_where['cid'] = $val;
				$channel_data = array(
					'co_status_p' => 1,
					'settle_attr' =>$settle_attr,
				);
				$channel_result = $model -> table('sj_channel') -> where($channel_where) -> save($channel_data);
			}
			
			if($result && $channel_result){
				$this -> writelog("客户列表：已添加渠道合作的渠道，渠道id为{$val},客户id为{$id}", 'co_client_channel', $val,__ACTION__ ,'','add');
			}
			$results[] = $result;
		}
		if($results){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/client_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	
	}
	
	
	function channel_list(){
		
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$admin_id = $_SESSION['admin']['admin_id'];
		$filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		foreach($filter_result as $key => $val){
			$admin_cid_str .= $val['target_value'].',';
		}
		$admin_cid = substr($admin_cid_str,0,-1);
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
		if($count_power_result['filter_type'] == 2){
			$where_go .= " and cid in ({$admin_cid})";
		}
		
		$category_results = $model -> table('sj_channel_category') -> where(array('status' => 1)) -> select();
		$chname = trim($_GET['chname']);
		$client_name = trim($_GET['client_name']);
		$charge_id = $_GET['charge_id'];
		$my_price = $_GET['my_price'];
		$my_qualit = $_GET['my_qualit'];
		$my_status = $_GET['my_status'];
		$my_attribute = $_GET['my_attribute'];
		$co_group = $_GET['co_group'];
		$category_id = $_GET['category_id'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$category_id_len = strlen($_GET['category_id']);
		if(substr($_GET['category_id'],$category_id_len-1,$category_id_len) == ','){
			$category_id = substr($_GET['category_id'],0,-1);
		}
		if($co_group != ''){
			$where_go .= " and co_group = {$co_group}";
			$this->assign("co_group", $co_group);
		}
		if($my_price){
			if($my_price == 'yes'){
				$where_go .= " and price_type = 2";
			}elseif($my_price == 'no'){
				$where_go .= " and price_type = 0";
			}else{
				$where_go .= " and price_type = 1 and price = {$my_price}";
			}
		}
		if($my_attribute){
			$where_go .= " and attribute = {$my_attribute}";
		}
		if($category_id != ''){
			$this->assign('category_id', $category_id.',');
			$where_go .= " and category_id in ({$category_id})";
		}
		
		if($my_qualit){
			$where_go .= " and qualit = {$my_qualit}";
		}
		if($my_status){
			$where_go .= " and co_status_p = {$my_status}";
		}
		if($chname){
			$where_go .= " and chname like '%{$chname}%'";
		}
		if($client_name && !$charge_id){
			$client_where['_string'] = "client_name like '%{$client_name}%' and status != 0";
			$client_str = $client_model->table('co_client_list')->where($client_where)->field('id')->buildSql(); 	
			$channel_need_where['_string'] = "client_id in ({$client_str})";
			$channel_need_result = $client_model -> table('co_client_channel') -> where($channel_need_where) -> select();
			
			foreach($channel_need_result as $key => $val){
				$channel_str .= $val['cid'].',';
			}
			$cid_str = substr($channel_str,0,-1);
			$where_go .= " and cid in ({$cid_str})";
		}elseif($charge_id && !$client_name){
			$client_where['_string'] = "charge_id = {$charge_id} and status != 0";
			$client_str = $client_model->table('co_client_list')->where($client_where)->field('id')->buildSql(); 	
			$channel_need_where['_string'] = "client_id in ({$client_str})";
			$channel_need_result = $client_model -> table('co_client_channel') -> where($channel_need_where) -> select();
			foreach($channel_need_result as $key => $val){
				$channel_str .= $val['cid'].',';
			}
			$cid_str = substr($channel_str,0,-1);
			$where_go .= " and cid in ({$cid_str})";
		}elseif($charge_id && $client_name){
			$client_where['_string'] = "charge_id = {$charge_id} and status != 0 and client_name like '%{$client_name}%'";
			$client_str = $client_model->table('co_client_list')->where($client_where)->field('id')->buildSql(); 	
			$channel_need_where['_string'] = "client_id in ({$client_str})";
			$channel_need_result = $client_model -> table('co_client_channel') -> where($channel_need_where) -> select();
			foreach($channel_need_result as $key => $val){
				$channel_str .= $val['cid'].',';
			}
			$cid_str = substr($channel_str,0,-1);
			
			$where_go .= " and cid in ({$cid_str})";
			
		}

		if($start_tm && $end_tm){
			$where_go .= " and submit_tm >= " . strtotime($start_tm) . " and submit_tm <= " . strtotime($end_tm) . "";
		}
		
		$filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> field('filter_type') -> select();
		
		if($filter_result[0]['filter_type'] == 2){
			if(!$_GET['client_id']){
				$where['_string'] = "status =1 and cid in ({$admin_cid})".$where_go;
			}else{
				$my_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $_GET['client_id'])) -> select();
				$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $_GET['client_id'])) -> select();
				$client_name = $client_name_result[0]['client_name'];
			
				foreach($my_channel_result as $key => $val){
					$my_cid_str .= $val['cid'].',';
				}
				$my_cid = substr($my_cid_str,0,-1);
				if($my_cid){
					$where['_string'] = "status = 1 and cid in ({$my_cid}) and cid in ({$admin_cid})".$where_go;
				}
			}
		}else{
			if($_GET['client_id']){
				$my_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $_GET['client_id'])) -> select();
				foreach($my_channel_result as $key => $val){
					$my_cid_str .= $val['cid'].',';
				}
				$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $_GET['client_id'])) -> select();
				$client_name = $client_name_result[0]['client_name'];
				$my_cid = substr($my_cid_str,0,-1);
				$where['_string'] = "status = 1 and cid in ({$my_cid})".$where_go;
			}else{
				$where['_string'] = "status = 1".$where_go;
			}
		}
		if(isset($_GET['billing'])){
			$this -> assign('billing',$_GET['billing']);
			$where['billing'] = $_GET['billing'];
		}
		if(isset($_GET['settle_attr'])){
			$this -> assign('settle_attr',$_GET['settle_attr']);
			$where['settle_attr'] = $_GET['settle_attr'];
		}
		$channel_count = $model -> table('sj_channel') -> where($where) -> count();
                
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($channel_count, 50, $param);
		$channel_result = $model -> table('sj_channel') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('submit_tm desc') -> select();
		foreach($channel_result as $key => $val){
			
			
			$val['co_status'] = $val['co_status_p'];
			$channel_result[$key] = $val['co_status_p'];

			$client_channel_result = $client_model -> table('co_client_channel') -> where(array('cid' => $val['cid'],'status' => 1)) -> select();
			if($client_channel_result){
				$client_result = $client_model -> table('co_client_list') -> where(array('id' => $client_channel_result[0]['client_id'])) -> select();
			
				$charge_name_result = $client_model -> table('co_charge') -> where(array('id' => $client_result[0]['charge_id'])) -> select();
				$val['charge_name'] = $charge_name_result[0]['charge_name'];
				$val['client_name'] = $client_result[0]['client_name'];
				$val['client_id'] = $client_result[0]['id'];
			}else{
				$val['client_name'] = '';
				$val['charge_name'] = '';
			}
			if($val['price_type'] == 1){
				$my_preice_name = $client_model -> table('co_scheme') -> where(array('id' => $val['price'])) -> select();
				$val['price_name'] = $my_preice_name[0]['name'];
				$my_price_result = $client_model -> table('co_detail') -> where(array('pid' => $val['price'],'status' => 1)) -> select();
				$val['the_price'] = $my_price_result;
			}
			
			$category_result = $model -> table('sj_channel_category') -> where(array('category_id' => $val['category_id'],'status' => 1)) -> find();
			$val['category_name'] = $category_result['name'];
			$channel_result[$key] = $val;
		}
	
		$charge_result = $client_model -> table('co_charge') -> where(array('status' => 1)) -> select();
		$price_result = $client_model -> table('co_scheme') -> where(array('status' => 1)) -> select();
		if(!$_GET['p']){
			$p = 1;
		}else{
			$p = $_GET['p'];
		}
		if(!$_GET['lr']){
			$lr = 50;
		}else{
			$lr = $_GET['lr'];
		}
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('price',$my_price);
		$this -> assign('attribute',$my_attribute);
		$this -> assign('qualit',$my_qualit);
		$this -> assign('co_status',$my_status);
		$this -> assign('price_result',$price_result);
		$this -> assign('charge_result',$charge_result);
		$this -> assign('channel_result',$channel_result);
		$this -> assign('chname',$chname);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('client_name',$client_name);
		$this -> assign('charge_id',$charge_id);
		$this -> assign('category_result',$category_results);
		$this->assign("co_group_arr", C('co_group'));
		$this -> display();
	}
	
	
	function change_channel(){
		$where_go = $this->getSearchParm();
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$cid = $_GET['cid'];
		$status = $_GET['status'];
		$where['cid'] = $cid;
		if($status == 2){
			$coefficient_result = $model -> table('sj_channel_coefficient_p') -> where(array('cid' => $cid,'status' => 1)) -> save(array('status' => 0,'last_refresh' => time()));
			$coefficient_data = array(
				'cid' => $cid,
				'coefficient' => 0,
				'status' => 1,
				'create_time' => time(),
				'last_refresh' => time()
			);
			$coefficient_change_result = $model -> table('sj_channel_coefficient_p') -> add($coefficient_data);
			$admin_id = $_SESSION['admin']['admin_id'];
			$admin_ip = $this -> getIP();
			$log_data = array(
				'cid' => $cid,
				'coefficient' => 0,
				'create_tm' => time(),
				'admin_id' => $admin_id,
				'admin_ip' => $admin_ip
			);
			$coefficient_log_result = $client_model -> table('co_channel_coefficient') -> add($log_data);
		}elseif($status == 1){
			$coefficient_result = $model -> table('sj_channel_coefficient_p') -> where(array('cid' => $cid,'status' => 1)) -> save(array('status' => 0,'last_refresh' => time()));
			$coefficient_data = array(
				'cid' => $cid,
				'coefficient' => 100,
				'status' => 1,
				'create_time' => time(),
				'last_refresh' => time()
			);
			$coefficient_change_result = $model -> table('sj_channel_coefficient_p') -> add($coefficient_data);
			$admin_id = $_SESSION['admin']['admin_id'];
			$admin_ip = $this -> getIP();
			$log_data = array(
				'cid' => $cid,
				'coefficient' => 100,
				'create_tm' => time(),
				'admin_id' => $admin_id,
				'admin_ip' => $admin_ip
			);
			$coefficient_log_result = $client_model -> table('co_channel_coefficient') -> add($log_data);
		}
		$data = array(
			'co_status_p' => $_GET['status']
		);
		$log_result = $this -> logcheck(array('cid' => $cid),'sj_channel',$data,$model);
		$result = $model -> table('sj_channel') -> where($where) -> save($data);
		//var_dump($model->getLastSql());exit;
		if($result){
			$this -> writelog("客户列表：已编辑id为{$cid}的合作渠道".$log_result, 'sj_channel', $cid,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/channel_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function edit_qualit_show(){
		$this->putSearchParm();
		$model = new Model();
		$cid = $_GET['cid'];
		$cid_str = substr($cid,0,-1);
		$cid_arr = explode(',',$cid_str);
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		$this -> assign('from',$_GET['from']);
		$this -> assign('cid_count',count($cid_arr));
		$this -> assign('cid',$cid);
		$this -> assign('channel_result',$channel_result);
		$this -> display();
	}
	
	function edit_qualit(){
		$where_go = $this->getSearchParm();
		$model = new Model();
		$cid = $_GET['cid'];
		$cid_arr = explode(',',$cid);
		$qualit = $_GET['qualit'];
		if(count($cid_arr) > 1){
			foreach($cid_arr as $key => $val){
				if($val){
					$where['cid'] = $val;
					$data = array(
						'qualit' => $qualit,
						'last_refresh' => time()
					);
					$log_result = $this -> logcheck(array('cid' => $val),'sj_channel',$data,$model);
					$result = $model -> table('sj_channel') -> where($where) -> save($data);
				
					if($result){
						$this -> writelog("渠道列表：已编辑cid为{$val}的渠道质量".$log_result, 'sj_channel', $val,__ACTION__ ,'','edit');
					}
					$results[] = $result;
				}
			}
			if($results){
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/channel_list'.	$where_go);
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}else{
			$where['cid'] = $cid;
			$data = array(
				'qualit' => $qualit,
				'last_refresh' => time()
			);
			$log_result = $this -> logcheck(array('cid' => $cid),'sj_channel',$data,$model);
			$result = $model -> table('sj_channel') -> where($where) -> save($data);
			if($result){
				$this -> writelog("渠道列表：已编辑cid为{$cid}的渠道质量".$log_result, 'sj_channel', $cid,__ACTION__ ,'','edit');
				$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/channel_list'.	$where_go);
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
	}
	
	function select_client_show(){
		$this->putSearchParm();	
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$cid = $_GET['cid'];
		//该渠道下的管理员和超管
		$admin_where['_string'] = "source_type = 1 and ((target_type =2 and target_value = {$cid} and filter_type = 2) or (target_type =9 and filter_type = 1))";
		$admin_result = $model -> table('sj_admin_filter') -> where($admin_where) -> field('source_value') -> select();
		
		foreach($admin_result as $key => $val){
			$my_charge_result = $client_model -> table('co_charge') -> where(array('admin_id' => $val['source_value'],'status' => 1)) -> select();
			if($my_charge_result[0]['id']){
				$my_charge_id .= $my_charge_result[0]['id'].',';
			}
		}

		$my_charge_str = substr($my_charge_id,0,-1);
		$my_client_where['_string'] = "charge_id in ({$my_charge_str})";
		$my_client_result = $client_model -> table('co_client_list') -> where($my_client_where) -> select();
		foreach($my_client_result as $key => $val){
			$client_id_str .= $val['id'].',';
		}
		$client_id = substr($client_id_str,0,-1);
		$my_client = $_GET['my_client'];
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		if($my_client){
			$client_where['_string'] = "status != 0 and client_name like '%{$my_client}%' and id in ({$client_id})";
		}else{
			$client_where['_string'] = "status != 0 and id in ({$client_id})";
		}
		$client_count = $client_model -> table('co_client_list') -> where($client_where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($client_count, 10, $param);
		$client_result = $client_model -> table('co_client_list') -> where($client_where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('create_tm DESC') -> select();

		foreach($client_result as $key => $val){
			$charge_result = $client_model -> table('co_charge') -> where(array('id' => $val['charge_id'])) -> select();
			$val['charge_name'] = $charge_result[0]['charge_name'];
			$login_result = $client_model -> table('co_account') -> where(array('client_id' => $val['id'],'is_parent' => 1)) -> select();
			$val['login_name'] = $login_result[0]['login_name'];
			
			$client_result[$key] = $val;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);	
		$this -> assign('channel_result',$channel_result);
		$this -> assign('client_result',$client_result);
		$this -> assign('my_client',$my_client);
		$this -> assign('cid',$cid);
		$this -> display();
	}
	
	
	function select_client(){
		$where_go = $this->getSearchParm();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$cid = $_GET['cid'];
		$client_id = $_GET['client_id'];
		if(!$client_id){
			$this -> error("请选择要关联的客户");
		}
		$settle_attr = $_GET['settle_attr'];
		if(!$settle_attr)
		{
			$this -> error("请选择渠道的结算类型");
		}
		$channel_where['cid'] = $cid;
		$channel_data = array(
			'co_status_p' => 1,
			'settle_attr' =>$settle_attr,
		);
		$channel_result = $model -> table('sj_channel') -> where($channel_where) -> save($channel_data);

		$client_data = array(
			'cid' => $cid,
			'client_id' => $client_id,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1
		);
		$client_result = $client_model -> add_channel($client_data);
	
		if($channel_result && $client_result){
			$this -> writelog("渠道列表：已关联id为{$client_id}的渠道合作用户,关联渠道id为{$cid}", 'co_client_channel', $client_id,__ACTION__ ,'','add');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/channel_list'.$where_go);
			$this -> success("关联成功");
		}else{
			$this -> error('关联失败');
		}
	}
	
	
	function edit_price_show(){
		$this->putSearchParm();
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$cid = $_GET['cid'];
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		$price_result = $client_model -> table('co_scheme') -> where(array('status' => 1)) -> select();
		$my_price_result = $client_model -> table('co_channel_price') -> where(array('cid' => $cid,'status' => 1)) -> order('create_tm DESC') -> select();
		if($channel_result[0]['price_type'] == 1){
			$price_detail = $client_model -> table('co_detail') -> where(array('pid' => $channel_result[0]['price'])) -> select();
			$price_name_result = $client_model -> table('co_scheme') -> where(array('id' => $channel_result[0]['price'])) -> select();
			$price_detail[0]['price_name'] = $price_name_result[0]['name'];
			$this -> assign('the_price',$price_detail);
		}
		foreach($my_price_result as $key => $val){
			if($val['price_type'] == 1){
				$the_price_result = $client_model -> table('co_price_history') -> where(array('did' => $val['id'])) -> select();
				$val['the_price'] = json_decode($the_price_result[0]['price'],true);
				$val['price_name'] = $the_price_result[0]['price_name'];
			}else{
				$val['the_price'] = array();
			}
			
			$admin_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['admin_id'])) -> select();
			$val['admin_name'] = $admin_result[0]['admin_user_name'];
			$val['the_month'] = date('Y-m',strtotime($val['month'].'01'));
			$my_price_result[$key] = $val;
		}

		$this -> assign('cid',$cid);
		$this -> assign('my_price_result',$my_price_result);
		$this -> assign('channel_result',$channel_result);
		$this -> assign('price_result',$price_result);
		$this -> display();
	}
	
	function edit_price(){
		$where_go = $this->getSearchParm();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$model = new Model();
		$cid = $_GET['cid'];
		$cid_arr = explode(',',$cid);
		if(!$_GET['price_type']){
			$this -> error("请选择单价");
		}

                $new_cid_arr = array();
                //todo查找CID对应的客户是否是按客户结算，如果是 则该客户下所有渠道单价都要修改
                foreach($cid_arr as $key => $val){
                    if(empty($val)){
                        continue;
                    }
                        $res = $client_model -> table('co_client_channel') -> where(array('cid' => $val)) -> find();
                        $temp_clientid = $res['client_id'];
                        $res = $client_model -> table('co_client_list') -> where(array('id' => $temp_clientid)) -> find();
                        $settle_entity =  $res['settle_entity'];
                        if($settle_entity==2){//按客户
                            $cid_tmp  = $client_model -> table('co_client_channel') -> where(array('client_id' => $temp_clientid)) -> select();
                            //print_r($cid_tmp);
                            foreach ($cid_tmp as $value) {
                                $new_cid_arr[]=$value['cid'];
                            }
                            //print_r($new_cid_arr);
                        }else{
                            $new_cid_arr[]=$val;                            
                        }
                }
                $cid_arr = array_unique($new_cid_arr);
                
		foreach($cid_arr as $key => $val){
			if($_GET['price_type'] == 'yes'){
				$price_type = 2;
			
				
				if(!is_numeric($_GET['price'])){
					$this -> error("单价格式错误");
				}
				$price = sprintf('%.2f',$_GET['price']);
			}else{
				$price_type = 1;
				$price = $_GET['price_type'];
			}
			$price_time = $_GET['price_time'];
			if($price_time == 1){
				$month = date('Ym',time());
			}else{
				$month = date('Ym',strtotime('+1 month'));
			}
			
			$data = array(
				'cid' => $val,
				'month' => $month,
				'admin_id' => $_SESSION['admin']['admin_id'],
				'admin_ip' => $this -> getIp(),
				'create_tm' => time(),
				'update_tm' => time(),
				'price' => $price,
				'price_type' => $price_type
			);

			$result = $client_model -> table('co_channel_price') -> add($data);
		
			if($result){
				$sj_data = array(
					'price' => $price,
					'price_type' => $price_type,
					'begin_month' => $price_time
				);
				$log_result = $this -> logcheck(array('cid' => $val),'sj_channel',$sj_data,$model);
				$sj_result = $model -> table('sj_channel') -> where(array('cid' => $val)) -> save($sj_data);
			
				if($price_type == 1){
					$my_price_project = $client_model -> table('co_scheme') -> where(array('id' => $price)) -> select();
					$my_price_detail = $client_model -> table('co_detail') -> where(array('pid' => 	$price,'status' => 1)) -> order('star_activations') -> select();
					
					$other_data = array(
						'did' => $result,
						'cid' => $val,
						'month' => $month,
						'price' => json_encode($my_price_detail),
						'price_name' => $my_price_project[0]['name']
					);
					$other_result = $client_model -> table('co_price_history') -> add($other_data);
			
				}
			}
			if($result){
				$this -> writelog("渠道列表：已修改ID为{$result}渠道id为{$val}的单价".$log_result, 'co_channel_price', $result,__ACTION__ ,'','edit');
			}
			$results[] = $result;
		}
		if($results){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/channel_list'.$where_go);
			$this -> success('编辑成功');
		}else{
			$this -> error('编辑失败');
		}
		
		
	}
	
	
	function my_price_show(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$price_id = $_GET['price_id'];
		$price_result = $client_model -> table('co_detail') -> where(array('pid'=> $price_id,'status' => 1)) -> order('star_activations') -> select();
		$price_name = $client_model -> table('co_scheme') -> where(array('id' => $price_id)) -> select();
		foreach($price_result as $key => $val){
			$val['price_name'] = $price_name[0]['name'];
			$price_result[$key] = $val;
		}
		echo json_encode($price_result);
		return json_encode($price_result);
	
	}
	
	function getIP(){
		global $ip;
		if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
		else $ip = "Unknow";
		return $ip;
	}
	
	function change_price_show(){
		$this->putSearchParm();
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$cid = $_GET['cid'];
		$cid_str = substr($cid,0,-1);
		$cid_arr = explode(',',$cid_str);
		$cid_count = count($cid_arr);
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		$price_result = $client_model -> table('co_scheme') -> where(array('status' => 1)) -> select();
		$my_price_result = $client_model -> table('co_channel_price') -> where(array('cid' => $cid)) -> order('create_tm DESC') -> select();
		foreach($my_price_result as $key => $val){
			$admin_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['admin_id'])) -> select();
			$val['admin_name'] = $admin_result[0]['admin_user_name'];
			if($val['price_type'] == 1){
				$the_price_result = $client_model -> table('co_scheme') -> where(array('id' => $val['price'])) -> select();
				$val['price_name'] = $the_price_result[0]['name'];
			}
			$val['the_month'] = date('Y-m',strtotime($val['month'].'01'));
			$my_price_result[$key] = $val;
		}
		$this -> assign('cid',$cid);
		$this -> assign('my_price_result',$my_price_result);
		$this -> assign('channel_result',$channel_result);
		$this -> assign('price_result',$price_result);
		$this -> assign('cid_count',$cid_count);
		$this -> display();
	}
	
	function check_client(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$client_name = $_POST['client_name'];
		$where['_string'] = "status != 0 and client_name = '{$client_name}'";
		$result = $client_model -> table('co_client_list') -> where($where) -> select();
	
		if($result){
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	
	function check_login(){
		$client_model = D('Channel_cooperation.channel_cooperation_p');
		$login_name = $_GET['login_name'];
		$where['_string'] = "status != 0 and login_name = '{$login_name}'";
		$result = $client_model -> table('co_account') -> where($where) -> select();
	
		if($result){
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function edit_attribute_show(){
		$this->putSearchParm();
		$model = new Model();
		$cid = $_GET['cid'];
		$cid_arr = explode(',',$cid);
		if(count($cid_arr) > 1){
			$result = $model -> table('sj_channel') -> where(array('cid' => $cid_arr[0])) -> find();
		}else{
			$result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> find();
		}
	
		$this -> assign('attribute',$result['attribute']);
		$this -> assign('cid',$cid);
		$this -> display();
	}
	
	function edit_attribute_do(){
		$where_go = $this->getSearchParm();
		$model = new Model();
		$cid = $_GET['cid'];
		$attribute = $_GET['channel_attribute'];
		$cid_arr = explode(',',$cid);
		if(count($cid_arr) > 1){
			foreach($cid_arr as $key => $val){
				$log_result = $this -> logcheck(array('cid' => $val),'sj_channel',array('attribute' => $attribute),$model);
				$result = $model -> table('sj_channel') -> where(array('cid' => $val)) -> save(array('attribute' => $attribute,'last_refresh' => time()));
				if($result){
					$this -> writelog("渠道列表：已编辑cid为{$val}的渠道".$log_result, 'sj_channel', $val,__ACTION__ ,'','edit');
				}
			}
		}else{
			$log_result = $this -> logcheck(array('cid' => $cid),'sj_channel',array('attribute' => $attribute),$model);
			$result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> save(array('attribute' => $attribute,'last_refresh' => time()));
	
			if($result){
				$this -> writelog("渠道列表：已编辑cid为{$cid}的渠道".$log_result, 'sj_channel', $cid,__ACTION__ ,'','edit');
			}
		}
		
		if($result){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/channel_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
		
	}
	function save_billing(){
		$model = new Model();
		if($_POST){
			if(!is_numeric($_POST['cid'])){
				$cid = substr($_POST['cid'],0,-1);
			}else{
				$cid = $_POST['cid'];
			}
			$where = array(
				'cid' =>  array('in',$cid)
			);
			$map = array(
				'billing' => $_POST['billing']
			);
			$ret = $model -> table('sj_channel') -> where($where)->save($map);
			
			$this -> assign('jumpUrl',$_SERVER['referer']);
			if($ret){
				$log = $_POST['billing'] == 1 ? '激活' : '预装';
				$this -> writelog("渠道列表：编辑cid为{$cid}的结算方式：【{$log}】", 'sj_channel', $cid,__ACTION__ ,'','edit');
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}else{
			if(is_numeric($_GET['cid'])){
				$result = $model -> table('sj_channel') -> where(array('cid' => $_GET['cid'])) -> field('billing')->find();
				$this -> assign('billing',$result['billing']);
			}
			$this -> assign('cid',$_GET['cid']);
			$this->display();
		}
	}
	
	function save_settle_attr()
	{
		$model = new Model();
		if($_POST){
			if(!is_numeric($_POST['cid'])){
				$cid = substr($_POST['cid'],0,-1);
			}else{
				$cid = $_POST['cid'];
			}
			$where = array(
				'cid' =>  array('in',$cid)
			);
			$map = array(
				'settle_attr' => $_POST['settle_attr']
			);
			$ret = $model -> table('sj_channel') -> where($where)->save($map);
			
			$this -> assign('jumpUrl',$_SERVER['referer']);
			if($ret){
				$attr_arr = array('','付款','换量','其他');
				$log = $attr_arr[$_POST['settle_attr']];
				$this -> writelog("渠道列表：编辑cid为{$cid}的结算类型：【{$log}】", 'sj_channel', $cid,__ACTION__ ,'','edit');
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}else{
			if(is_numeric($_GET['cid'])){
				$result = $model -> table('sj_channel') -> where(array('cid' => $_GET['cid'])) -> field('settle_attr')->find();
				$this -> assign('settle_attr',$result['settle_attr']);
			}
			$this -> assign('cid',$_GET['cid']);
			$this->display();
		}
	}

	function getSearchParm(){
		$my_price = $_GET['my_price'];
		$my_qualit = $_GET['my_qualit'];
		$my_status = $_GET['my_status'];
		$my_attribute = $_GET['my_attribute'];
		$category_id = $_GET['category_id'];
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$charge_id = $_GET['charge_id'];
		$billing = $_GET['billing'];
		$co_group = $_GET['co_group'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		if($my_price){
			$where_go .= "/my_price/{$my_price}";
		}
		if($my_attribute){
			$where_go .= "/my_attribute/{$my_attribute}";
		}
		if($category_id !=''){
			$where_go .= "/category_id/{$category_id}";
		}
		if($my_qualit){
			$where_go .= "/my_qualit/{$my_qualit}";
		}
		if($my_status){
			$where_go .= "/my_status/{$my_status}";
		}
		if($chname){
			$where_go .= "/chname/{$chname}";
		}
		if($client_name){
			$where_go .= "/client_name/{$client_name}";
		}
		if($charge_id!=''){
			$where_go .= "/charge_id/{$charge_id}";
		}
		if($billing){
		    $where_go .= "/billing/{$billing}";
		}
		if($co_group !=''){
		    $where_go .= "/co_group/{$co_group}";
		}
		if($p){
			$where_go .= "/p/{$p}";
		}
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		return $where_go;
	}
	function putSearchParm(){
		$my_price = $_GET['my_price'];
		$my_qualit = $_GET['my_qualit'];
		$my_status = $_GET['status'];
		$my_attribute = $_GET['my_attribute'];
		$category_id = $_GET['category_id'];
		$chname = $_GET['chname'];
		$client_name = $_GET['client_name'];
		$charge_id = $_GET['charge_id'];
		$billing = $_GET['billing'];
		$co_group = $_GET['co_group'];		
		$p = $_GET['p'];
		$lr = $_GET['lr'];

		$this -> assign('my_price',$my_price);
		$this -> assign('my_qualit',$my_qualit);
		$this -> assign('my_status',$my_status);
		$this -> assign('category_id',$category_id);
		$this -> assign('my_attribute',$my_attribute);
		$this -> assign('billing',$billing);
		$this -> assign('chname',$chname);
		$this -> assign('cid',$cid);
		$this -> assign('client_name',$client_name);
		$this -> assign('charge_id',$charge_id);
		$this -> assign('co_group',$co_group);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
        }

	function change_zhuti_show(){
		$model = D('Channel_cooperation.channel_cooperation_p');
		$cid = $_GET['cid'];
		$this -> assign('cid',$cid);

                if($_POST){
                    $zhuti = $_POST['zhuti'];
                    $cid_str = $_POST['cid'];
                    $cid_str = substr($cid_str,0,-1);
                    $cid_str = '('.$cid_str.')';
                    $sql = 'update co_client_list set settle_entity='.$zhuti.' where id in '.$cid_str;
                    $res = $model->execute($sql);
                    //var_dump($res,$model->getlastsql());
                    if($zhuti ==1){
                        $zhutiname='按渠道';
                    }else if($zhuti ==2){
                        $zhutiname='按客户';
                    }
		    $this -> writelog("渠道合作->合作方账号管理->客户列表,修改了id为{$cid_str}的结算主体为".$zhutiname);
                    $this -> assign('jumpUrl','/index.php/Channel_cooperation/Clientlist_p/client_list');
                    $this -> success("修改成功");
                }

		$this -> display();
	}

}
