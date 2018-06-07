<?php
/**
 * 闪屏软件管理
 * date:2013-11-27
 * ----------------------------------------------------------------------------
*/
class ApplyScreenAction extends CommonAction {
	//闪屏审核中
	public function screen_check(){
		$status = 1 ;
		$tpl  = "Dev:ApplyScreen:screen_check";
		$tab_status = 1;
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		$this->get_debut_list($status,$tpl,$tab_status,$order_field,$order_type);
	}
	//闪屏通过列表
	public function screen_through(){
		$status = 2 ;
		$tpl  = "Dev:ApplyScreen:screen_through";
		$tab_status = 2;
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		$this->get_debut_list($status,$tpl,$tab_status,$order_field,$order_type);
	}
	//闪屏驳回列表
	public function screen_reject(){
		$status = 3;
		$tpl  = "Dev:ApplyScreen:screen_reject";
		$tab_status = 3;
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		$this->get_debut_list($status,$tpl,$tab_status,$order_field,$order_type);
	}
	//闪屏取消列表
	public function screen_cancel(){
		$status =  4 ;
		$tpl  = "Dev:ApplyScreen:screen_cancel";
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
		if($list[0]['image'] && $list[0]['image_desc']){
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
			$result = array ('success' => true,'rows'=>$loacl_desc);
			echo json_encode ($result);
			exit();
	    }else{
	    	$result = array ('success' => false,'rows'=>'获取数据失败！');
			echo json_encode ($result);
			exit();
	    }

	}
	//获取首发软件列表
	public function get_debut_list($status,$tpl,$tab_status,$order_field,$order_type){
		        $model = new model();
		        import('@.ORG.Page');
				$param = http_build_query($_REQUEST);
				$limit = 50;
				$where = "status ={$status} and del_status=1";
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
				if(isset($_GET['seach_id'])){
					$softid = trim($_GET['seach_id']);
					$seach_soft = $model->table('sj_soft')->field('package')->where("softid ='{$softid}' and status =1 and hide=1 and claim_status=2")->limit(1)->select();
					$seach.= " and package ='{$seach_soft[0]['package']}'";
					$this -> assign('seach_id',$_GET['seach_id']);
				}
				if(isset($_GET['seach_softname'])){
					$softname = trim($_GET['seach_softname']);
					$seach_soft = $model->table('sj_soft')->field('package')->where("softname like '%{$softname}%' and status =1 and hide=1 and claim_status=2")->select();
					//echo $model->getLastSql();
					//var_dump($seach_soft);
					$seach_package = '';
					foreach ($seach_soft as $key => $value) {
						$seach_package.= "'".$value['package']."',";
					}
					$seach_package = substr ( $seach_package, 0, - 1 ); 
					//var_dump($seach_package);
					$seach.= " and package in ({$seach_package})";
					$this -> assign('seach_softname',$_GET['seach_softname']);
				}
				if(isset($_GET['seach_package'])){
					$seach.= " and package = '{$_GET['seach_package']}'";
					$this -> assign('seach_package',$_GET['seach_package']);
				}
				if(isset($_GET['seach_dev'])){
					$dev_name = trim($_GET['seach_dev']);
					$seach_dev = $model->table('pu_developer')->where("dev_name like '%{$dev_name}%'")->field('dev_id')->select();
					//echo $model->getLastSql();
					//var_dump($seach_dev);
					$seach_dev_id = '';
					foreach ($seach_dev as $key => $value) {
						$seach_dev_id.= "'".$value['dev_id']."',";
					}
					$seach_dev_id = substr ( $seach_dev_id, 0, - 1 ); 
					//var_dump($seach_dev_id);
					$seach.= " and dev_id in ({$seach_dev_id})";
					$this -> assign('seach_dev',$_GET['seach_dev']);
				}
				if(isset($_GET['seach_email'])){
					$dever_email = trim($_GET['seach_email']);
					$seach_email = $model->table('pu_developer')->where("email = '{$dever_email}'")->field('dev_id')->select();
					
					//echo $model->getLastSql();
					//var_dump($seach_dev);
					$seach_dev_email = '';
					foreach ($seach_email as $key => $value) {
						$seach_dev_email.= "'".$value['dev_id']."',";
					}
					$seach_dev_email = substr ( $seach_dev_email, 0, - 1 ); 
					//var_dump($seach_dev_email);
					$seach.= " and dev_id in ({$seach_dev_email})";
					$this -> assign('seach_email',$_GET['seach_email']);
				}
				//提交时间
				if(isset($_GET['start_time']) && isset($_GET['end_time'])){
					$start_time = strtotime($_GET['start_time']);
					$end_time = strtotime($_GET['end_time']);
					$seach.= " and apply_time >= '{$start_time}' and apply_time<='{$end_time}'";
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
				if(isset($_GET['screen_type']) && $_GET['screen_type']!='' && $status ==2){
					
					$seach.= " and  screen_type={$_GET['screen_type']}"; 
					$this -> assign('debut_type',$_GET['screen_type']); 
				}

		        $count_total = $model->table('sj_soft_screen')->where($where.$seach) -> count();
				$page = new Page($count_total, $limit, $param);
				$list = $model->table('sj_soft_screen')->order($order_dubut)->limit($page->firstRow.','.$page->listRows)->where($where.$seach)->select();
				//echo $model->getLastSql().'<br>';
				$icon_package = '';
				$dev_id =  array();
				foreach ($list as $key => $value) {
					$icon_package.= "'".$value['package']."',";
					$dev_id[] = $value['dev_id'];
				}
				$icon_package = substr ( $icon_package, 0, - 1 ); 
				$iconurl_arr = array();
				$icon_file = $model->table("sj_soft_file")->field("apk_name,iconurl")->where("apk_name in ($icon_package)  and package_status = 1")->order("id DESC")->select();
				//echo $model->getLastSql().'<br>';
				foreach ($icon_file as $key => $value) {
					$iconurl_arr[$value['apk_name']][] = $value['iconurl'];
				}
				//开发者信息
				$dev_name = $model->table('pu_developer')->where(array('dev_id'=>array('in',$dev_id)))->field('dev_id,dev_name,type,email,status')->select();
				$dev_all = array();
				foreach($dev_name as $m){
					$dev_all[$m['dev_id']] = $m;
				}
				$time = time();
				//var_dump($list);
				//软件图标
				// $softmodel = D('Dev.Softlist');
				// $iconlist = $softmodel -> new_icon_list('',$icon_package);	
				foreach ($list as $key => $value) {
					// if($iconlist[1][$value['package']]['iconurl']){
						// $iconurl = $iconlist[1][$value['package']]['iconurl'];
					// }else{
						$iconurl =  $iconurl_arr[$value['package']][0];
					//}
					$list[$key]['iconurl']= $iconurl;				
					//type 0公司 1个人 2团队
					$list[$key]['dev_type'] = $dev_all[$value['dev_id']]['type'];
					$list[$key]['dever_email'] = $dev_all[$value['dev_id']]['email'];
					$list[$key]['dev_name'] = $dev_all[$value['dev_id']]['dev_name'];
					//软件信息
					$soft_info = $model->table('sj_soft')->field('softid,softname,category_id,version,total_downloaded +total_downloaded_add  - total_downloaded_detain as download')->where("package ='{$value['package']}' and status =1  and dev_id = '{$value['dev_id']}'")->limit(1)->order("version_code desc")->select();
					$category_id = str_replace ( ',', '', $soft_info[0]['category_id']);
					$category = $model->table('sj_category')->field('name')->where("category_id=$category_id")->limit(1)->select(); 
					$list[$key]['category_name'] = $category[0]['name'];
					$list[$key]['soft_version'] = $soft_info[0]['version'];
					$list[$key]['download'] = $soft_info[0]['download'];
					$list[$key]['softname'] = $soft_info[0]['softname'];
					$list[$key]['softid'] = $soft_info[0]['softid'];
					$list[$key]['through_time'] = date('Y-m-d',$value['through_time']).'<br />'.date('H:i:s',$value['through_time']);
					$list[$key]['apply_time'] = date('Y-m-d',$value['apply_time']).'<br />'.date('H:i:s',$value['apply_time']); 	
					$list[$key]['reject_time'] = date('Y-m-d',$value['reject_time']).'<br />'.date('H:i:s',$value['reject_time']); 	
					$list[$key]['cancel_time'] = date('Y-m-d',$value['cancel_time']).'<br />'.date('H:i:s',$value['cancel_time']); 
					if($status==2){
						$screen_status = 5;
						$soft_tmp = $model->table('sj_soft_tmp')->where(array('package'=>$value['package'],'record_type'=>3,'version_code'=>array('gt',$value['version_code']),'status'=>2))->field('record_type,id as tmp_id,status,deny_msg,last_refresh,version_code')->order('id desc')->limit(1)->select();
						
						//echo $model->getLastSql().'<br>';
						//var_dump($soft_tmp);
						$soft = $model->table('sj_soft')->where(array('status'=>1,'hide'=>1,'claim_status'=>2,'package'=>$value['package']))->field('version_code,version,last_refresh')->order('softid desc')->limit(1)->select();
						    $list[$key]['debut_type'] = 1;
							if(($soft[0]['version_code'] == $value['version_code']) && !$soft_tmp){
								$list[$key]['debut_type'] = 1; //闪屏申请通过后，开发者还未升级软件的状态
								$screen_status = 5;
							}
							if($soft_tmp && $soft[0]['version_code'] == $value['version_code']){
								$list[$key]['debut_type'] = 2; //闪屏申请通过，开发者首次上传APK后，CP还未审核的状态
								$screen_status = 6;
							}
							if(($soft[0]['version_code'] > $value['version_code']) && ($soft[0]['last_refresh']> $value['through_time'])){
								$list[$key]['debut_type'] = 3; //闪屏申请通过，开发者上传APK后，CP首次通过审核的状态
								$screen_status = 7;
							}
							if($soft_tmp[0]['status']=='3' &&  ($soft_tmp[0]['last_refresh']> $value['through_time']) && ($soft[0]['version_code'] == $value['version_code'])){
								$list[$key]['debut_type'] = 4; //闪屏申请通过，开发者首次上传APK后，CP未通过审核的状态
								$screen_status = 8;
							}
							$model->table('sj_soft_screen')->where("id = {$value['id']}")->save(array('screen_type'=>$list[$key]['debut_type']));
							$model->table('sj_soft_status')->where("package = '{$value['package']}'")->save(array('screen_status'=>$screen_status));
					}
					//var_dump($res);
					//echo $model->getLastSql().'<br>';

				}
				//var_dump($list);
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
		 $is_val = $model->table('sj_soft_screen')->where("id='{$id}'")->select();
		 if($is_val[0]['admin_remark']==$remark){
		 	$result=array('success'=>false,'msg'=>'请修改后再提交！');
			echo json_encode($result);
			exit();
		 }
		 $res = $model->table('sj_soft_screen')->where("id='{$id}'")->save(array('admin_remark'=>$remark));
		 //echo $model->getLastSql();
		if($res){	
			    $this -> writelog('备注了ID为'.$id.'申请闪屏记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");
				$result=array('success'=>true,'msg'=>'操作成功！');
				echo json_encode($result);
				exit();
		}else{
				$result=array('success'=>false,'msg'=>'操作失败！');
				echo json_encode($result);
				exit();
		}
	}
	public function screen_oper(){
		$id = trim($_POST['id']);
		$type = trim($_POST['type']);//2 通过 3 驳回 4取消
		$model = new model();
		if(isset($id)){
			if($type=='2'){ //通过
				$screen_status = 5;
				//$debut_length =  trim($_POST['debut_length']);
				$res = $model->table('sj_soft_screen')->where("id = $id")->save(array('status'=>2,'through_time'=>time()));
				$this -> writelog('通过了ID为'.$id.'申请闪屏记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");
			}
			if($type=='3'){//驳回
				$screen_status = 3;
				$reject_reason =  trim($_POST['reject_reason']);
				$res = $model->table('sj_soft_screen')->where("id in ($id)")->save(array('status'=>3,'reject_reason'=>$reject_reason,'reject_time'=>time()));	
				$this -> writelog('驳回了ID为'.$id.'申请闪屏记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");			
			}
			if($type=='4'){// 取消
				$screen_status = 4;
				$reject_reason =  trim($_POST['cancel_reason']);
				$res = $model->table('sj_soft_screen')->where("id in ($id)")->save(array('status'=>4,'cancel_reason'=>$reject_reason,'cancel_time'=>time()));
				$this -> writelog('取消了ID为'.$id.'申请闪屏记录', 'sj_brush_adapter', $id,__ACTION__ ,"","edit");
			}	
			if($res){
				$screen_list = $model->table('sj_soft_screen')->where("id = $id")->field('package')->find();
				update_soft_status(array('screen_status'=>$screen_status),$screen_list['package']);
				$result=array('success'=>true,'msg'=>'操作成功！','id'=>array('0'=>$id));
				echo json_encode($result);
				$this->screen_sendmail($type,$id,$reject_reason);
				exit();
			}else{
				$result=array('success'=>false,'msg'=>'操作失败！');
				echo json_encode($result);
				exit();
			}
		}else{
			$result=array('success'=>false,'msg'=>'请选择要操作的数据！');
			echo json_encode($result);
			exit();
		}
	}
	//发送提醒
	public function screen_sendmail($type,$id,$reject_reason){
		$model = new Model();
		$emailmodel = D("Dev.Sendemail");
		$screenlist = $model->table('sj_soft_screen')->where("id in ($id)")->field('dev_id,package')->select();	
		$tm = date("Y-m-d",time());
		$config_txt = C('_config_txt_');	
		foreach($screenlist as $v){	
			$softlist = $model ->table('sj_soft')->where("package='{$v['package']}' and status=1 and hide=1")->field('package,softname')->find();
			if($v['dev_id'] != 0 && $softlist){
				$search   = array("softname","tm","msg");
				$replace  = array($softlist['softname'],$tm,$reject_reason);	
				if($type ==2){
					$msg = str_replace($search,$replace,$config_txt['screen_pass']);
				}else if($type ==3){
					$msg = str_replace($search,$replace,$config_txt['screen_reject']);
				}else if($type ==4){
					$msg = str_replace($search,$replace,$config_txt['screen_cancel']);
				}
				$emailmodel -> dev_remind_add($v['dev_id'],$msg);
				//发送邮件提醒
				$dever = $model-> table('pu_developer')->where("dev_id={$v['dev_id']}")-> field('dev_id,email,dev_name') ->find();
				if($type == 4){
					$subject = $config_txt['screen_cancel_subject'];		
				}else {
					$subject = $config_txt['screen_subject'];
				}
				$search3   = array("devname","softname","tm","msg");
				$replace3  = array($dever['dev_name'],$softlist['softname'],$tm,$reject_reason);				
				if($type ==2){				
					$email_cont = str_replace($search3,$replace3,$config_txt['screen_pass_txt']);
				}else if($type == 3){
					$email_cont = str_replace($search3,$replace3,$config_txt['screen_reject_txt']);
				}else if($type == 4){
					$email_cont = str_replace($search3,$replace3,$config_txt['screen_cancel_txt']);
				}
				$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
			}	
		}		
	}
	//批量添加
	function Batch_add_screen(){
		if (!empty($_FILES)) {
			$array = array('csv');
			$ytypes = $_FILES['csv']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			if(!in_array($type,$array)){
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error('上传格式错误');
			}

			if (!empty($_FILES['csv']['tmp_name'])){		
				$data = file_get_contents($_FILES['csv']['tmp_name']);
				//判断是否是utf-8编辑
				if(mb_check_encoding($data,"utf-8") != true){
					$data = iconv("gbk","utf-8", $data);
				}
				$data = str_replace("\r\n","\n",$data);	
				$data_arr = explode("\n", $data);
				$uniqid = uniqid();
				$file = '/tmp/'. session_id(). '_'.$uniqid. ".csv";
				$i = 0;
				$ii = 0;
				foreach($data_arr as $k => $v){
					if($k == 0) continue;
					if(empty($v)) continue;
					$datas = explode(',',$v);
					$ret = $this -> pub_Batch_add_screen_do($datas);
					if($ret){
						$i ++;
						//操作日志
						// $this->writelog("批量添加了包名为{$datas[0]}的闪屏");
					}else{
						$ii ++;
						$str = '';
						if($ii == '1'){
							$str = chr(0xEF).chr(0xBB).chr(0xBF);
							$str .= "包名(必填),关键词,联系人,邮箱,QQ,备注\n";
						}
						file_put_contents($file,$str.$v."\n",FILE_APPEND);
					}
				}
				$error_file = '/index.php/Dev/ApplyScreen/pub_download_err_data/uid/'.$uniqid;
				exit(json_encode(array('success_num'=>$i,'error_num'=>$ii,'error_file'=>$error_file)));
			}
		}
	}
	public function pub_Batch_add_screen_do($data){
		$model = new Model();
		$time = time();
		$pkg = trim($data[0]);
		$where = array(
			'package' => $pkg,
			'del_status' => 1
		);
		$ret = $model -> table('sj_soft_screen') -> where($where)->field('id')->find();
		$where = array(
			'package'=>$pkg,
			'status' => 1,
			'hide'=>1,
		);
		$soft = $model->table('sj_soft') ->where($where)->field('softname,package,version_code,dev_id')->find();
		if(!$ret){
			$data = array(
				'package' =>$pkg,
				'keyword'=>$data[1],
				'contact'=>$data[2],
				'email'=>$data[3],
				'qq'=>$data[4],
				'admin_remark'=>$data[5],
				'status'=>2,
				'apply_time'=>$time,
				'through_time'=>$time,
				'version_code'=>$soft['version_code'],
				'dev_id'=>$soft['dev_id'],
			);
			$res = $model -> table('sj_soft_screen')->add($data);
			$this->writelog("闪屏新增了id为{$res}的数据",'sj_soft_screen',$res,__ACTION__ ,"","add");
			if(!$res){
				return 0;
			}else{
				return 1;
			}
		}else{
			return 0;
		}
	}
	//批量添加__下载失败数量
	function pub_download_err_data(){
		$uid = $_GET['uid'];
		$file = '/tmp/'. session_id(). '_'.$uid. ".csv";
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="disabled.csv"');
		header('Cache-Control: max-age=0');	
		echo file_get_contents($file);
		exit;
	}	
}
?>
