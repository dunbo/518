<?php
/**
 * 首发软件管理
 * date:2013-11-27
 * ----------------------------------------------------------------------------
*/
class ApplyDebutAction extends CommonAction {
	//首发审核中
	public function debut_check(){
		$status = 1 ;
		$tpl  = "Dev:ApplyDebut:debut_check";
		$tab_status = 1;
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		$this->get_debut_list($status,$tpl,$tab_status,$order_field,$order_type);
	}
	//首发通过列表
	public function debut_through(){
		$status = 2 ;
		$tpl  = "Dev:ApplyDebut:debut_through";
		$tab_status = 2;
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		$this->get_debut_list($status,$tpl,$tab_status,$order_field,$order_type);
	}
	//首发驳回列表
	public function debut_reject(){
		$status = 3;
		$tpl  = "Dev:ApplyDebut:debut_reject";
		$tab_status = 3;
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		$this->get_debut_list($status,$tpl,$tab_status,$order_field,$order_type);
	}
	//首发取消列表
	public function debut_cancel(){
		$status =  4 ;
		$tpl  = "Dev:ApplyDebut:debut_cancel";
		$tab_status =4;
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		$this->get_debut_list($status,$tpl,$tab_status,$order_field,$order_type);
	}
	//查看资源
	public function show_image(){
		$id = trim($_GET['id']);
		$model = new model();
		$list = $model->table('sj_soft_debut')->where("id={$id}")->limit(1)->select();
		//if($list[0]['image'] && $list[0]['image_desc']){
			$image_arr = array();
			$desc_arr = array();
			$loacl_desc =  array();
			foreach (explode(',', $list[0]['image']) as $value) {
				$image_arr[] = IMGATT_HOST.$value;
			}
			foreach (explode(',', $list[0]['image_desc']) as $value) {
				$desc_arr[] = $value;
			}
			foreach ($desc_arr as $key => $value) {
				$loacl_desc[] = array('desc'=>$value,'image'=>$image_arr[$key]);
			}
			$this -> assign('list',$loacl_desc);
			$this -> display("Dev:ApplyDebut:debut_image");

			//$result = array ('success' => true,'rows'=>$loacl_desc);
			//echo json_encode ($result);
			//exit();
	    //}else{
	    	//$result = array ('success' => false,'rows'=>'获取数据失败！');
			//echo json_encode ($result);
			//exit();
	    //}

	}
	//获取首发软件列表
	public function get_debut_list($status,$tpl,$tab_status,$order_field,$order_type){
		$model = new model();
		import('@.ORG.Page');
		$param = http_build_query($_REQUEST);
		$limit = 50;
		$where = "status ={$status}";
		$seach = $order_dubut = $order_download = '';
		if($order_field=='debut_time' && isset($order_type)){
			$order_dubut = "$order_field   $order_type";
		}elseif ($order_field=='download' && isset($order_type)) {
			$order_download = $order_type;
		}else{
			if($tab_status=='1'){
				$order_dubut = 'apply_time  asc';
			}elseif ($tab_status=='2') {
				$order_dubut = 'through_time DESC';
			}elseif ($tab_status=='3') {
				$order_dubut = 'reject_time  DESC';
			}else{
				$order_dubut = 'cancel_time  DESC';
			}
		}
		$this->assign('url_field',$order_field);
		$this->assign('url_type',$order_type);
		if(isset($_GET['seach_id']) || isset($_GET['seach_softname'])){
			$softid = trim($_GET['seach_id']);
			$softname = trim($_GET['seach_softname']);
			$where_s = array(
				'status' => 1,
				'hide' => 1,
				'claim_status' => 2,
			);
			if(isset($_GET['seach_id'])){
				$where_s['softid']	= $softid;
				$this -> assign('seach_id',$softid);
			}	
			if(isset($_GET['seach_softname'])){
				$where_s['softname'] = array('like',"%{$softname}%");
				$this -> assign('seach_softname',$_GET['seach_softname']);
			}	
			$subQuery = $model->table('sj_soft')->where($where_s)->field('package')->buildSql(); 	
			$seach.= " and package in ({$subQuery})";
		}
		if(isset($_GET['seach_package'])){
			$seach.= " and package = '{$_GET['seach_package']}'";
			$this -> assign('seach_package',$_GET['seach_package']);
		}
		if(isset($_GET['seach_dev']) || isset($_GET['seach_email'])){
			$dev_name = trim($_GET['seach_dev']);
			$dever_email = trim($_GET['seach_email']);
			$where_p = array();
			if($dev_name){
				$where_p['dev_name'] = array('like',"%{$dev_name}%");
				$this -> assign('seach_dev',$dev_name);
			}	
			if($dever_email){
				$where_p['email'] = $dever_email;
				$this -> assign('seach_email',$_GET['seach_email']);
			}	
			$seach_dev_id = $model->table('pu_developer')->where($where_p)->field('dev_id')->buildSql(); 
			$seach.= " and dev_id in ({$seach_dev_id})";
		}
		//首发时间
		if(isset($_GET['start_time']) && isset($_GET['end_time'])){
			$start_time = strtotime($_GET['start_time']);
			$end_time = strtotime($_GET['end_time']);
			$seach.= " and debut_time >= '{$start_time}' and debut_time<='{$end_time}'";
			$this -> assign('start_time',$_GET['start_time']);
			$this -> assign('end_time',$_GET['end_time']);
		}
		//通过时间
		if(isset($_GET['pass_start_time']) && isset($_GET['pass_end_time'])){
			$start_time = strtotime($_GET['pass_start_time']);
			$end_time = strtotime($_GET['pass_end_time']);
			$seach.= " and through_time >= '{$start_time}' and through_time<='{$end_time}'";
			$this -> assign('start_time',$_GET['start_time']);
			$this -> assign('end_time',$_GET['end_time']);
		}
		//驳回时间
		if(isset($_GET['reject_start_time']) && isset($_GET['reject_end_time'])){
			$start_time = strtotime($_GET['reject_start_time']);
			$end_time = strtotime($_GET['reject_end_time']);
			$seach.= " and reject_time >= '{$start_time}' and reject_time<='{$end_time}'";
			$this -> assign('start_time',$_GET['start_time']);
			$this -> assign('end_time',$_GET['end_time']);
		}
		//取消时间
		if(isset($_GET['cancel_start_time']) && isset($_GET['cancel_end_time'])){
			$start_time = strtotime($_GET['cancel_start_time']);
			$end_time = strtotime($_GET['cancel_end_time']);
			$seach.= " and cancel_time >= '{$start_time}' and cancel_time<='{$end_time}'";
			$this -> assign('start_time',$_GET['start_time']);
			$this -> assign('end_time',$_GET['end_time']);
		}
		//状态
		$time = time();
		if(isset($_GET['debut_type']) && $_GET['debut_type']!='' && $status ==2){
			$debut_types = $_GET['debut_type']==6?0:$_GET['debut_type'];
			$seach.= " and  debut_type={$debut_types}";  
			$this -> assign('debut_type',$_GET['debut_type']);
		}
		
		$count_total = $model->table('sj_soft_debut')->where($where.$seach) -> count();
		$page = new Page($count_total, $limit, $param);
		$list = $model->table('sj_soft_debut')->order($order_dubut)->limit($page->firstRow.','.$page->listRows)->where($where.$seach)->select();
		//echo $model->getLastSql().'<br>';
		$icon_package = '';
		$dev_id =  array();
		$package =  array();
		foreach ($list as $key => $value) {
			$icon_package.= "'".$value['package']."',";
			$dev_id[] = $value['dev_id'];
			$package[] = $value['package'];
		}
		$where = array(
			'apk_name' => array('in',$package),
			'package_status' => 1
		);
		$iconurl_arr = get_table_data($where,"sj_soft_file","apk_name","apk_name,iconurl");
		//开发者信息
		$where = array(
			'dev_id' => array('in',$dev_id),
		);
		$dev_all = get_table_data($where,"pu_developer","dev_id","dev_id,dev_name,type,email,status");
		foreach ($list as $key => $value) {
			$iconurl =  $iconurl_arr[$value['package']]['iconurl'];
			$list[$key]['iconurl']= $iconurl;
			//type 0公司 1个人 2团队
			$list[$key]['dev_type'] = $dev_all[$value['dev_id']]['type'];
			$list[$key]['dever_email'] = $dev_all[$value['dev_id']]['email'];
			$list[$key]['dev_name'] = $dev_all[$value['dev_id']]['dev_name'];
			//软件信息
			$soft_info = $model->table('sj_soft')->field('softid,softname,category_id,version,total_downloaded +total_downloaded_add  - total_downloaded_detain as download, last_refresh,safe,version_code')->where("package ='{$value['package']}' and status =1 and hide=1 and dev_id = '{$value['dev_id']}'")->order("version_code desc")->limit(1)->select();
			$category_id = str_replace ( ',', '', $soft_info[0]['category_id']);
			$category = $model->table('sj_category')->field('name')->where("category_id=$category_id")->limit(1)->select(); 
			$list[$key]['category_name'] = $category[0]['name'];
			$list[$key]['download'] = $soft_info[0]['download'];
			$list[$key]['softname'] = $soft_info[0]['softname']?$soft_info[0]['softname']:'软件已下架';
			$list[$key]['softid'] = $soft_info[0]['softid'];
			$list[$key]['through_time'] = date('Y-m-d',$value['through_time']).'<br />'.date('H:i:s',$value['through_time']);
			$list[$key]['apply_time'] = date('Y-m-d',$value['apply_time']).'<br />'.date('H:i:s',$value['apply_time']); 	
			$list[$key]['reject_time'] = date('Y-m-d',$value['reject_time']).'<br />'.date('H:i:s',$value['reject_time']); 	
			$list[$key]['cancel_time'] = date('Y-m-d',$value['cancel_time']).'<br />'.date('H:i:s',$value['cancel_time']); 
			//判断首发状态
			$list[$key]['soft_version'] = $soft_info[0]['version'];
			$list[$key]['safe'] = $soft_info[0]['safe'];
			$list[$key]['expire'] = false;
			if ($soft_info[0]['version_code']>$value['version_code'] && $soft_info[0]['last_refresh']>intval($value['debut_time']+(17*60*60))) {
				$list[$key]['expire'] = true;
			}

			if($status==2){
				if($value['is_apk'] ==0) $debut_status = 5;
				if ($list[$key]['safe'] >= 2 && $value['status'] != 4 ) {
					$model->table('sj_soft_debut')->where("id = {$value['id']}")->save(array('status'=>4,'is_apk'=>0,'is_apk_uptime'=>0,'cancel_time'=>$time,'cancel_reason'=>'软件不安全，系统自动取消。'));
					update_soft_status(array('debut_status'=>4),$value['package']);
					unset($list[$key]);
					continue;
				}
				if ($time >= intval($value['debut_time']+(17*60*60)) && $soft_info[0]['version_code'] ==$value['version_code'] && $value['status'] != 4) {
					$model->table('sj_soft_debut')->where("id = {$value['id']}")->save(array('status'=>4,'is_apk'=>0,'is_apk_uptime'=>0,'cancel_time'=>$time,'cancel_reason'=>'首发超时，系统自动取消。'));
					update_soft_status(array('debut_status'=>4),$value['package']);
					unset($list[$key]);
					continue;							
				}
				$soft_tmp = $model->table('sj_soft_tmp')->where(array('package'=>$value['package'],'record_type'=>3,'version_code'=>array('gt',$value['version_code'])))->field('record_type,id as tmp_id,status,deny_msg,last_refresh,version_code')->limit(1)->order('id desc')->select();
				if(intval($value['debut_time']+($value['debut_length']*60*60))>$time){ //在首发期内的
					$list[$key]['debut_type'] = 1; //待提交软件
					if($soft_info[0]['version_code']>$value['version_code']  && $value['is_apk']=='1' && $soft_info[0]['last_refresh']>=$value['is_apk_uptime'] ){ //软件待首发
						$list[$key]['debut_type'] = 3;
						$debut_status = 7;
						if($soft_info[0]['version']!=$value['debut_version']){
							$list[$key]['soft_version'] = '<span style="color:#E53333;">'.$soft_info[0]['version'].'</span>';
						}
					}
					if($soft_tmp[0]['version_code']>=$soft_info[0]['version_code'] && $soft_tmp[0]['last_refresh']<=$value['through_time'] && $value['is_apk']=='1'){
						$list[$key]['is_check_soft'] = 1; //此软件有新版本正在审核中，请在审核通过后，再提交首发软件。 
					}
					if($soft_tmp[0]['status']=='2' && $soft_tmp[0]['version_code']>$soft_info[0]['version_code'] && $value['is_apk']=='1' ){ //软件属于升级审核中
						$list[$key]['debut_type'] = 2;
						$debut_status = 6;
					}
					if($soft_tmp[0]['status']=='3' && $soft_tmp[0]['version_code']>$soft[0]['version_code'] && $value['is_apk']=='1' && $soft_info[0]['last_refresh']<=$value['is_apk_uptime']){ //软件审核驳回
						$list[$key]['debut_type'] = 5;
						$debut_status = 9;
					}
					if($soft_info[0]['version_code']>$value['version_code'] && intval($value['debut_time'])<intval($time) && $value['debut_length']*60*60 + intval($value['debut_time']) >intval($time) && $value['is_apk']=='1' && $soft_info[0]['last_refresh']>=$value['is_apk_uptime']){//首发中的软件 
						$list[$key]['debut_type'] = 4;
						$debut_status = 8;
						if($soft_info[0]['version']!=$value['debut_version']){
							$list[$key]['soft_version'] = '<span style="color:#E53333;">'.$soft_info[0]['version'].'</span>';
						}
					}
					
				 }else{
					  if($soft_info[0]['version_code']>=$value['version_code'] && $value['is_apk']=='1'){
						$list[$key]['debut_type'] = 0; //首发结束 
						$debut_status = 11;
					  }
				 }
			}
			if($list[$key]['debut_type']=='0'  && $value['debut_time']+$value['debut_length']*60*60<$time && $value['status'] != 4){					
				$model->table('sj_soft_debut')->where("id = {$value['id']}")->save(array('status'=>4,'is_apk'=>0,'is_apk_uptime'=>0,'cancel_time'=>$time,'cancel_reason'=>'软件过期，系统自动取消。'));
				$debut_status = 4;
			}else{
				$model->table('sj_soft_debut')->where("id = {$value['id']}")->save(array('debut_type'=>$list[$key]['debut_type']));				
			}
			if($debut_status){
				update_soft_status(array('debut_status'=>$debut_status),$value['package']);
			}
			//var_dump($soft_info);
			//echo $model->getLastSql().'<br>';
		}
		foreach ($list as $key => $value) {
			$download[$key] = $value['download'];
		}
		if($order_download == 'desc'){
			array_multisort($download,SORT_DESC,$list);
		}
		if($order_download == 'asc'){
			array_multisort($download,SORT_ASC,$list);
		}

		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('page', $page->show());
		$this -> assign('tab_status',$tab_status);
		$this -> assign('count',$count_total);
		$this -> assign('list',$list);
		$this -> display($tpl);
	}
	//管理员备注信息
	public function admin_remark(){
		 $id = trim($_POST['id']);
		 $remark = trim($_POST['remark']);
		 $model = new model();
		 $is_val = $model->table('sj_soft_debut')->where("id='{$id}'")->select();
		 if($is_val[0]['admin_remark']==$remark){
		 	$result=array('success'=>false,'msg'=>'请修改后再提交！');
			echo json_encode($result);
			exit();
		 }
		 $res = $model->table('sj_soft_debut')->where("id = $id")->save(array('admin_remark'=>$remark));
		if($res){	
			    $this -> writelog('备注了ID为'.$id.'申请首发记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");
				$result = array ('success' => true,'msg'=>'操作成功！');
				echo json_encode ($result);
				exit();
		}else{
				$result = array ('success' => false,'msg'=>'操作失败！');
				echo json_encode ($result);
				exit();
		}
	}
	public function debut_oper(){
		$id = trim($_POST['id']);
		$type = trim($_POST['type']);//2 通过 3 驳回 4取消
		$model = new model();
		if(isset($id)){
			$debut_status = 0;
			if($type=='2'){ //通过
				$debut_status = 5;
				$debut_length =  trim($_POST['debut_length']);
				$debut_resource =  trim($_POST['debut_resource']);
				if(!$debut_resource && $_POST['pass_type'] ==1){
					exit(json_encode(array('success' => false,'msg'=>'请填写首发资源！')));
				}
				$map = array(
					'status'=>2,
					'debut_length'=>$debut_length,
					'through_time'=>time(),
				);
				if($debut_resource){
					$map['debut_resource'] = $debut_resource;
				}	
				$reject_reason = $debut_resource;
				$res = $model->table('sj_soft_debut')->where("id = $id")->save($map);
				$this -> writelog('通过了ID为'.$id.'申请首发记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");
			}
			if($type=='3'){//驳回
				$debut_status = 3;
				$reject_reason =  trim($_POST['reject_reason']);
				$res = $model->table('sj_soft_debut')->where("id in ($id)")->save(array('status'=>3,'reject_reason'=>$reject_reason,'reject_time'=>time()));
				$this -> writelog('驳回了ID为'.$id.'申请首发记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");
			}
			if($type=='4'){// 取消
				$debut_status = 4;
				$reject_reason =  trim($_POST['cancel_reason']);
				$res = $model->table('sj_soft_debut')->where("id in ($id)")->save(array('status'=>4,'is_apk'=>0,'is_apk_uptime'=>0,'cancel_reason'=>$reject_reason,'cancel_time'=>time()));
				$this -> writelog('取消了ID为'.$id.'申请首发记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");
				
			}
			if($res){	
				$debut_list = $model->table('sj_soft_debut')->where("id = $id")->field('package')->find();
				update_soft_status(array('debut_status'=>$debut_status),$debut_list['package']);
				$result = array('success' => true,'msg'=>'操作成功！','id'=>array('0'=>$id));
				echo json_encode ($result);
				if($_POST['pass_type'] ==2){
					exit;
				}
				$this->debut_sendmail($type,$id,$reject_reason,$debut_length);
				exit();
			}else{
				$result = array('success' => false,'msg'=>'操作失败！');
				echo json_encode ($result);
				exit();
			}
		}else{
			$result = array('success' => false,'msg'=>'请选择要操作的数据！');
			echo json_encode ($result);
			exit();
		}
	}
	//发送提醒
	public function debut_sendmail($type,$id,$reject_reason,$debut_length){
		$model = new Model();
		$emailmodel = D("Dev.Sendemail");
		$debutlist = $model->table('sj_soft_debut')->where("id in ($id)")->field('dev_id,package')->select();	
		$tm = date("Y-m-d",time());
		$config_txt = C('_config_txt_');	
		foreach($debutlist as $v){	
			$softlist = $model ->table('sj_soft')->where("package='{$v['package']}' and status=1")->field('package,softname')->find();
			if($v['dev_id'] != 0 && $softlist){				
				if($type ==2){
					$search   = array("softname","tm","msg","hours");
					$replace  = array($softlist['softname'],$tm,$reject_reason,$debut_length);
					$msg = str_replace($search,$replace,$config_txt['debut_pass']);
				}else if($type ==3){
					$search   = array("softname","tm","msg");
					$replace  = array($softlist['softname'],$tm,$reject_reason);	
					$msg = str_replace($search,$replace,$config_txt['debut_reject']);
				}else if($type ==4){
					$search   = array("softname","tm","msg");
					$replace  = array($softlist['softname'],$tm,$reject_reason);	
					$msg = str_replace($search,$replace,$config_txt['debut_cancel']);
				}
				$emailmodel -> dev_remind_add($v['dev_id'],$msg);
				//发送邮件提醒
				$dever = $model-> table('pu_developer')->where("dev_id={$v['dev_id']}")-> field('dev_id,email,dev_name') ->find();
				if($type == 4){
					$subject = $config_txt['debut_cancel_subject'];		
				}else{	
					$subject = $config_txt['debut_subject'];		
				}			
				if($type ==2){
					$search3   = array("devname","softname","tm","msg","hours","pkg");
					$replace3  = array($dever['dev_name'],$softlist['softname'],$tm,$reject_reason,$debut_length,$softlist['package']);				
					$email_cont = str_replace($search3,$replace3,$config_txt['debut_pass_txt']);
				}else if($type == 3){
					$search3   = array("devname","softname","tm","msg","pkg");
					$replace3  = array($dever['dev_name'],$softlist['softname'],$tm,$reject_reason,$softlist['package']);				
					$email_cont = str_replace($search3,$replace3,$config_txt['debut_reject_txt']);
				}else if($type == 4){
					$search3   = array("devname","softname","tm","msg","pkg");
					$replace3  = array($dever['dev_name'],$softlist['softname'],$tm,$reject_reason,$softlist['package']);				
					$email_cont = str_replace($search3,$replace3,$config_txt['debut_cancel_txt']);
				}
				$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
			}	
		}		
	}
	//修改首发日期
	function save_debut_time(){
		$model = new Model();
		$id = $_POST['id'];
		$debut_tm = $_POST['update_debut_tm'];
		$debut_time = strtotime($debut_tm);
		if($debut_time <= time()){
			exit(json_encode(array('code'=>0,'msg'=>'首发日期不能小于当前日期')));
		}
		$where = array(
			'id'=>$id
		);
		$debut_list = $model->table('sj_soft_debut')->where($where)->field('debut_time,package')->find();
		if($debut_list){
			$map = array(
				'debut_time'=>$debut_time
			);
			$ret = $model->table('sj_soft_debut')->where($where)->save($map);
			if($ret){
				$content = "修改了id为{$id}包名为{$debut_list['package']}首发日期，原日期".date('Y-m-d',$debut_list['debut_time'])."改成了".$debut_tm;
				$this->writelog($content,'sj_soft_debut',$id,__ACTION__ ,"","edit");
				exit(json_encode(array('code'=>1,'msg'=>array('0' =>$id))));
			}
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'该数据不在通过状态！')));
		}
	}
}
?>