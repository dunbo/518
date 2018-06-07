<?php

class AdAgreementAction extends CommonAction{

	function agreement_list(){
		$model = new Model();
		
		if(isset($_GET['agree_num'])){
			$where_go .= " and agree_num like '%{$_GET['agree_num']}%'";
		}
		if(isset($_GET['client_name'])){
			$client_where['_string'] = "client_name like '%{$_GET['client_name']}%' and status = 1";
			$client_result = $model -> table('ad_client') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_str_go .= $val['id'].',';
			}
			$client_id_str = substr($client_id_str_go,0,-1);
			$where_go .= " and client_id in ({$client_id_str})";
		}
		if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
			$start_tm = strtotime($_GET['start_tm']);
			$end_tm = strtotime($_GET['end_tm']);
			if($start_tm > $end_tm){
				$this -> error("搜索合作日期开始时间不能大于结束时间");
			}
			$where_go .= " and start_tm <= {$start_tm} and end_tm <= {$end_tm}";
		}
		if(isset($_GET['charge_name'])){
			$admin_where['_string'] = "admin_user_name like '%{$_GET['charge_name']}%' and admin_state = 1";
			$admin_result = $model -> table('sj_admin_users') -> where($admin_where) -> select();
			
			foreach($admin_result as $key => $val){
				$charge_id_str_go .= $val['admin_user_id'].',';
			}
			
			$charge_id_str = substr($charge_id_str_go,0,-1);
			$where_go .= " and charge_id in ({$charge_id_str})";
		}
		if(isset($_GET['ad_pos'])){
			$where_go .= " and ad_pos like '%,{$_GET['ad_pos']},%'";
		}
		if(isset($_GET['sign_start']) && isset($_GET['sign_end'])){
			$sign_start = strtotime($_GET['sign_start']);
			$sign_end = strtotime($_GET['sign_end']);
			if($sign_start > $sign_end){
				$this -> error("搜索签订日期开始时间不能大于结束时间");
			}
			$where_go .= " and sign_tm >= {$sign_start} && sign_tm <= {$sign_end}";
		}
		
		$where['_string'] = "status = 1".$where_go;
		$count = $model -> table('ad_frame_agreement') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 50, $param);
		$result = $model -> table('ad_frame_agreement') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('create_tm DESC') -> select();

		//广告位
		$ad_results = $model -> table('pu_config') -> where(array('config_type' => 'AD_CHANNEL','status' => 1)) -> select();
		$ad_result = json_decode($ad_results[0]['configcontent']);
		
		//已收保证金
		//已开发票
		//合同数
		foreach($result as $key => $val){
			$contract_count = $model -> table('ad_contract') -> where(array('framework_id' => $val['id'])) -> count();
			$val['contract_count'] = $contract_count;
			$client_name_result = $model -> table('ad_client') -> where(array('id' => $val['client_id'],'status' => 1)) -> select();
			$val['client_name'] = $client_name_result[0]['client_name'];
			$ad_pos_arr = explode(',',$val['ad_pos']);
			$ad_pos_name = '';
			foreach($ad_result as $k => $v){
				if(in_array($k,$ad_pos_arr)){
					$ad_pos_name .= $v.',';
				}
			}
			$val['ad_pos_name'] = substr($ad_pos_name,0,-1);
			$charge_name_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['charge_id'],'admin_state' => 1)) -> select();
			$val['charge_name'] = $charge_name_result[0]['admin_user_name'];
			$result[$key] = $val;
		}
		
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 50;
		}
		
		$this -> assign('ad_result',$ad_result);
		$this -> assign('agree_num',$_GET['agree_num']);
		$this -> assign('client_name',$_GET['client_name']);
		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('charge_name',$_GET['charge_name']);
		$this -> assign('ad_pos',$_GET['ad_pos']);
		$this -> assign('sign_start',$_GET['sign_start']);
		$this -> assign('sign_end',$_GET['sign_end']);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$Page -> setConfig('header', '篇记录');
		$Page -> setConfig('first', '<<');
		$Page -> setConfig('last', '>>');
		$show = $Page->show();
		$this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> display();
	}

	function add_agreement_show(){
		$model = new Model();
		$ad_channel_result = $model -> table('pu_config') -> where(array('config_type' => 'AD_CHANNEL','status' => 1)) -> select();
		$ad_channel = json_decode($ad_channel_result[0]['configcontent']);
		$client_result = $model -> table('ad_client') -> where(array('status' => 1)) -> select();
		$admin_id = $_SESSION['admin']['admin_id'];
		$my_time = microtime();
		$my_hash = md5($admin_id . $my_time);
		
		$this -> assign('my_hash',$my_hash);
		$this -> assign('ad_channel',$ad_channel);
		$this -> assign('client_result',$client_result);
		$this -> display();
	}
	
	function ajax_add_pos(){
		$model = new Model();
		$my_select = $_GET['my_select'];
		$ad_channel_result = $model -> table('pu_config') -> where(array('config_type' => 'AD_CHANNEL','status' => 1)) -> select();
		$ad_channel = json_decode($ad_channel_result[0]['configcontent']);
		$my_select_arr = explode(',',$my_select);
		foreach($ad_channel as $key => $val){
			if(!in_array($key,$my_select_arr)){
				$vals['ad_name'] = $val;
				$vals['ad_id'] = $key;
				$my_ad_channel[] = $vals;
			}
			
		}
	
		echo json_encode($my_ad_channel);
		return json_encode($my_ad_channel);
	}
	
	function add_agreement_do(){
		$model = new Model();
		$client_id = $_POST['client_id'];
		$hash = $_POST['my_hash'];
		if(!$client_id){
			$this -> error("请选择客户");
		}
		$agree_num = trim($_POST['agree_num']);
		if(!$agree_num){
			$this -> error("请填写协议编号");
		}
		$have_agree = $model -> table('ad_frame_agreement') -> where(array('agree_num' => $agree_num,'status' => 1)) -> select();
		if($have_agree){
			$this -> error("已存在此协议编号");
		}
		$sign_tm = strtotime($_POST['sign_tm']);
		if(!$_POST['sign_tm']){
			$this -> error("请选择签订日期");
		}
		$co_account = trim($_POST['co_account']);
		if(!$co_account){
			$this -> error("请输入合作金额");
		}
		$ad_pos = array_unique($_POST['ad_pos']);
		if(!$ad_pos){
			$this -> error("请选择购买频道");
		}
		foreach($ad_pos as $key => $val){
			$ad_post_str_go .= $val.',';
		}
		$ad_post_str = ','.$ad_post_str_go;
		$start_tm = strtotime($_POST['start_tm']);
		$end_tm = strtotime($_POST['end_tm']);
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请选择开始时间和结束时间");
		}
		if($start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$charge_name = $_POST['charge_name'];
		if(!$charge_name){
			$this -> error("请填写负责人");
		}
		$charge_result = $model -> table('sj_admin_users') -> where(array('admin_user_name' => $charge_name,'admin_state' => 1)) -> select();
		if(!$charge_result){
			$this -> error("'{$charge_name}'此负责人不存在");
		}else{
			$charge_id = $charge_result[0]['admin_user_id'];
		}
		$plan_margin = trim($_POST['plan_margin']);
		if(!$plan_margin){
			$this -> error("请输入预计保证金");
		}
		$comment = $_POST['comment'];
		$data = array(
			'client_id' => $client_id,
			'agree_num' => $agree_num,
			'sign_tm' => $sign_tm,
			'co_account' => $co_account,
			'ad_pos' => $ad_post_str,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'charge_id' => $charge_id,
			'plan_margin' => $plan_margin,
			'comment' => $comment,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1
		);
		
		$result = $model -> table('ad_frame_agreement') -> add($data);
		
		if($result){
			$my_go = C('AD_FILE');
			$my_tmp_go = C('AD_TMP_FILE');
			$tmp_affix = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
			if($tmp_affix){
				$tmp_affix_type_arr = array_reverse(explode('.',$tmp_affix[0]['affix_name']));
				$tmp_affix_type = $tmp_affix_type_arr[0];
				$my_time = date('Ym/d');
				$tmp_dir = $my_go .'/'.$my_time;
				if(!is_dir($tmp_dir)){
					@mkdir($tmp_dir,0777,true);
				}
				if(copy($my_tmp_go .'/'.$tmp_affix[0]['file_name'],$tmp_dir.'/'.time().'.'.$tmp_affix_type)){
					$affix_data = array(
						'affix_name' => $tmp_affix[0]['affix_name'],
						'affix_url' => $tmp_dir.'/'.time().'.'.$tmp_affix_type,
					);
					$affix_result = $model -> table('ad_frame_agreement') -> where(array('id' => $result)) -> save($affix_data);
					$affix_result = $model -> table('ad_affix_tmp') -> where(array('affix_hash'=> $hash,'status' => 1)) -> save(array('agreement_id' => $result));
				}else{
					$this -> error("上传文件失败");
				}
			}
		}
		
		if($result){
			$this -> writelog("已添加框架协议，协议编号为{$agree_num}");
			$this -> assign("jumpUrl",'/index.php/Sendnum/AdAgreement/agreement_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}

	function ajax_upload_file(){
		$model = new Model();
		$my_tmp_go = C('AD_TMP_FILE');
		$affix = $_FILES['affix'];
		$id = $_GET['id'];
		$affix_type_arr = array_reverse(explode('.',$affix['name']));
		$affix_type = $affix_type_arr[0];
		$hash = $_GET['hash'];
		$allow_type = array('word','excel','pdf','jpg','png','zip','rar');
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
		$have_been = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
		
		if (file_exists($tmp_dir . $tmp_filename) || $have_been){
			$ret = array('code' => 4,'msg' => '上传文件失败,文件已存在');
			echo json_encode($ret);
			return json_encode($ret);
		}else{
		  if(move_uploaded_file($affix["tmp_name"], $tmp_dir . $the_tmp_filename)){
			$affix_data = array(
				'affix_hash' => $hash,
				'affix_name' => $tmp_filename,
				'file_name' => $the_tmp_filename,
				'create_tm' => time(),
				'status' => 1
			);
			$tmp_affix_result = $model -> table('ad_affix_tmp') ->add($affix_data);
		
			if($tmp_affix_result){
				$tmp_result = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
		
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
	
	function upload_file(){
		$model = new Model();
		$id = $_GET['id'];
		$from = $_GET['from'];
		if(!$from){
			$from = 1;
		}
		//下载临时文件
		if($from == 1){
			$result = $model -> table('ad_affix_tmp') -> where(array('id' => $id)) -> select();
			$tmp_file = C('AD_TMP_FILE');
			$file = $tmp_file.'/'.$result[0]['file_name'];
			$file_name = $result[0]['affix_name'];
		}elseif($from == 2){
			$result = $model -> table('ad_frame_agreement') -> where(array('id' => $id)) -> select();
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
	
	function del_file(){
		$model = new Model();
		$id = $_GET['id'];
		$from = $_GET['from'];
		$hash = $_GET['hash'];
		$data = array(
			'status' => 0
		);
		$client_id = $_GET['client_id'];
	
		if($from == 1){
			$where['id'] = $id;
			$where['affix_hash'] = $hash;
			$update_result = $model -> table('ad_affix_tmp') -> where($where) -> save($data);
		}elseif($from == 2){
			$where_edit['agreement_id'] = $id;
			$where_edit['status'] = 1;
			$update_result = $model -> table('ad_affix_tmp') -> where($where_edit) -> save($data);
		}
		if($update_result){
			$tmp_result = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
			echo json_encode($result);
			return json_encode($result);
		}
	}

	function edit_agreement_show(){
		$model = new Model();
		$agree_num = $_GET['agree_num'];
		$client_name = $_GET['client_name'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$charge_name = $_GET['charge_name'];
		$ad_pos = $_GET['ad_pos'];
		$sign_start = $_GET['sign_start'];
		$sign_end = $_GET['sign_end'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$id = $_GET['id'];
		$result = $model -> table('ad_frame_agreement') -> where(array('id' => $id,'status' => 1)) -> select();
		$ad_pos_str = $result[0]['ad_pos'];
		$ad_pos_arr = explode(',',$ad_pos_str);
		$ad_channel_result = $model -> table('pu_config') -> where(array('config_type' => 'AD_CHANNEL','status' => 1)) -> select();
		$ad_channel = json_decode($ad_channel_result[0]['configcontent']);
		$client_result = $model -> table('ad_client') -> where(array('status' => 1)) -> select();
		$admin_id = $_SESSION['admin']['admin_id'];
		$my_time = microtime();
		$my_hash = md5($admin_id . $my_time);
		$charge_name_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $result[0]['charge_id'],'admin_state'=>1)) -> select();
		
		$result[0]['charge_name'] = $charge_name_result[0]['admin_user_name'];
	
		$this -> assign('my_hash',$my_hash);
		$this -> assign('ad_channel',$ad_channel);
		$this -> assign('client_result',$client_result);
		$this -> assign('ad_pos_arr',$ad_pos_arr);
		$this -> assign('result',$result);
		$this -> assign('agree_num',$agree_num);
		$this -> assign('client_name',$client_name);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('charge_name',$charge_name);
		$this -> assign('ad_pos',$ad_pos);
		$this -> assign('sign_start',$sign_start);
		$this -> assign('sign_end',$sign_end);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> display();
	}
	
	function edit_agreement_do(){
		$model = new Model();
		if($_POST['agree_nums']){
			$where_go .= "/agree_num/{$_POST['agree_nums']}";
		}
		if($_POST['client_names']){
			$where_go .= "/client_name/{$_POST['client_names']}";
		}
		if($_POST['start_tms']){
			$where_go .= "/start_tm/{$_POST['start_tms']}";
		}
		if($_POST['end_tms']){
			$where_go .= "/end_tm/{$_POST['end_tms']}";
		}
		if($_POST['charge_names']){
			$where_go .= "/charge_name/{$_POST['charge_names']}";
		}
		if($_POST['ad_poss']){
			$where_go .= "/ad_pos/{$_POST['ad_poss']}";
		}
		if($_POST['sign_starts']){
			$where_go .= "/sign_start/{$_POST['sign_starts']}";
		}
		if($_POST['sign_ends']){
			$where_go .= "/sign_end/{$_POST['ends']}";
		}
		if($_POST['p']){
			$where_go .= "/p/{$p}";
		}
		if($_POST['lr']){
			$where_go .= "/lr/{$lr}";
		}
		$id = $_POST['id'];
		$client_id = $_POST['client_id'];
		if(!$client_id){
			$this -> error("请选择客户");
		}
		$agree_num = trim($_POST['agree_num']);
		if(!$agree_num){
			$this -> error("请填写协议编号");
		}
		$have_agree_where['_string'] = "agree_num = {$agree_num} and status = 1 and id != {$id}";
		$have_agree = $model -> table('ad_frame_agreement') -> where($have_agree_where) -> select();
		if($have_agree){
			$this -> error("已存在此协议编号");
		}
		$sign_tm = strtotime($_POST['sign_tm']);
		if(!$_POST['sign_tm']){
			$this -> error("请选择签订日期");
		}
		$co_account = trim($_POST['co_account']);
		if(!$co_account){
			$this -> error("请输入合作金额");
		}
		$ad_pos = array_unique($_POST['ad_pos']);
		if(!$ad_pos){
			$this -> error("请选择购买频道");
		}
		foreach($ad_pos as $key => $val){
			$ad_post_str .= $val.',';
		}
		$ad_post_str = ','.$ad_post_str;
		$start_tm = strtotime($_POST['start_tm']);
		$end_tm = strtotime($_POST['end_tm']);
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请选择开始时间和结束时间");
		}
		if($start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$charge_name = $_POST['charge_name'];
		if(!$charge_name){
			$this -> error("请填写负责人");
		}
		$charge_result = $model -> table('sj_admin_users') -> where(array('admin_user_name' => $charge_name,'admin_state' => 1)) -> select();
		if(!$charge_result){
			$this -> error("'{$charge_name}'此负责人不存在");
		}else{
			$charge_id = $charge_result[0]['admin_user_id'];
		}
		$plan_margin = trim($_POST['plan_margin']);
		if(!$plan_margin){
			$this -> error("请输入预计保证金");
		}
		$comment = $_POST['comment'];
	
		$data = array(
			'client_id' => $client_id,
			'agree_num' => $agree_num,
			'sign_tm' => $sign_tm,
			'co_account' => $co_account,
			'ad_pos' => $ad_post_str,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'charge_id' => $charge_id,
			'plan_margin' => $plan_margin,
			'comment' => $comment,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1
		);
		
		$log_result = $this -> logcheck(array('id' => $id),'ad_frame_agreement',$data,$model);
		$result = $model -> table('ad_frame_agreement') -> where(array('id' => $id,'status' => 1)) -> save($data);
		$hash = $_POST['my_hash'];
		if($result){
			$my_go = C('AD_FILE');
			$my_tmp_go = C('AD_TMP_FILE');
			$change_affix = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash)) -> select();
			if($change_affix){
				$tmp_affix = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
				if($tmp_affix){
					$tmp_affix_type_arr = array_reverse(explode('.',$tmp_affix[0]['affix_name']));
					$tmp_affix_type = $tmp_affix_type_arr[0];
					$my_time = date('Ym/d');
					$tmp_dir = $my_go .'/'.$my_time;
					if(!is_dir($tmp_dir)){
						@mkdir($tmp_dir,0777,true);
					}
					if(copy($my_tmp_go .'/'.$tmp_affix[0]['file_name'],$tmp_dir.'/'.time().'.'.$tmp_affix_type)){
						$affix_data = array(
							'affix_name' => $tmp_affix[0]['affix_name'],
							'affix_url' => $tmp_dir.'/'.time().'.'.$tmp_affix_type,
						);
						$affix_result = $model -> table('ad_frame_agreement') -> where(array('id' => $id)) -> save($affix_data);
						$affix_result = $model -> table('ad_affix_tmp') -> where(array('affix_hash'=> $hash,'status' => 1)) -> save(array('agreement_id' => $id));
					}else{
						$this -> error("上传文件失败");
					}
				}
			}
		}
	
		if($result){
			$this -> writelog("已编辑框架协议，协议编号为{$agree_num}".$log_result);
			$this -> assign("jumpUrl",'/index.php/Sendnum/AdAgreement/agreement_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	


}
