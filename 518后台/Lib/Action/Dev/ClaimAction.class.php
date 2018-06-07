<?php

//软件认领审核:待审核列表,通过列表,驳回列表

class ClaimAction extends CommonAction {
	//通过列表
	public function claim_pass(){
		$status =1;
		$this -> claim_list($status);
	}	
	//待审核列表
	public function claim_audit(){
		$status =2;
		$this -> claim_list($status);
	}
	//驳回列表
	public function claim_reject(){
		$status =3;
		$this -> claim_list($status);
	}

	public function claim_list($status) {
		$p = isset($_GET['p']) && is_numeric($_GET['p']) ? $_GET['p'] : 1;
		$lr = isset($_GET['lr']) && is_numeric($_GET['lr']) ? $_GET['lr'] : 10;

		//$status = isset($_GET['status']) && is_numeric($_GET['status']) ? $_GET['status'] : 2;

		$where = "c.status='{$status}'";
		if(isset($_GET['id']) && is_numeric($_GET['id'])) {
			$where .= " AND c.softid='{$_GET['id']}'";
		}
		if(!empty($_GET['softname'])) {
			$where .= " AND c.softname LIKE '%{$_GET['softname']}%'";
		}
		if(!empty($_GET['package'])) {
			$where .= " AND c.package LIKE '%{$_GET['package']}%'";
		}
		if(!empty($_GET['dev_name'])) {
			$where .= " AND (d.dev_name LIKE '%{$_GET['dev_name']}%' OR dv.dev_name LIKE '%{$_GET['dev_name']}%')";
		}
		if(!empty($_GET['email'])) {
			$where .= " AND (d.email LIKE '%{$_GET['email']}%' OR dv.email LIKE '%{$_GET['email']}%')";
		}
		if(!empty($_GET['begintime'])) {
			$begintime = strtotime($_GET['begintime']);
			$where .= " AND c.update_tm>='{$begintime}'";
		}
		if(!empty($_GET['endtime'])) {
			$endtime = strtotime($_GET['endtime']);
			$where .= " AND c.update_tm<='{$endtime}'";
		}
		if(isset($_GET['dev_type'])){
			$this->assign('dev_type',$_GET['dev_type']);
			$where .= " AND d.type ='{$_GET['dev_type']}'";
		}
		import("@.ORG.Page2");
		$model = new Model();
		if($status==1) {		//通过列表
			$count = $model->table('dev_claim c LEFT JOIN pu_developer d ON c.dev_id=d.dev_id LEFT JOIN dev_claim_ori co ON c.id=co.cid AND c.softid=co.softid AND co.status=1 LEFT JOIN pu_developer dv ON co.dev_id=dv.dev_id')->where($where)->count();
			$Page=new Page($count,10);
			$list = $model->table('dev_claim c LEFT JOIN pu_developer d ON c.dev_id=d.dev_id LEFT JOIN dev_claim_ori co ON c.id=co.cid AND c.softid=co.softid AND co.status=1 LEFT JOIN pu_developer dv ON co.dev_id=dv.dev_id')->where($where)->field('c.*,d.dev_name,d.email,d.type AS dev_type,co.dev_id AS claim_devid,co.update_type,co.claim_status,dv.email AS old_email,dv.dev_name AS old_dev_name')->order('c.pass_tm desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		} else {	//待审核,驳回列表
			$count = $model->table('dev_claim c LEFT JOIN pu_developer d ON c.dev_id=d.dev_id LEFT JOIN sj_soft s ON c.softid=s.softid LEFT JOIN pu_developer dv ON s.dev_id=dv.dev_id')->where($where)->count();
			$Page=new Page($count,10);
			if($status == 3){
				$list = $model->table('dev_claim c LEFT JOIN pu_developer d ON c.dev_id=d.dev_id LEFT JOIN sj_soft s ON c.softid=s.softid LEFT JOIN pu_developer dv ON s.dev_id=dv.dev_id')->where($where)->field('c.*,d.dev_name,d.email,d.type AS dev_type,s.dev_id AS claim_devid,s.update_type,s.claim_status,s.category_id,s.version_code,s.version,s.language,dv.email AS old_email,dv.dev_name AS old_dev_name')->order('c.last_tm desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			} else{
				$list = $model->table('dev_claim c LEFT JOIN pu_developer d ON c.dev_id=d.dev_id LEFT JOIN sj_soft s ON c.softid=s.softid LEFT JOIN pu_developer dv ON s.dev_id=dv.dev_id')->where($where)->field('c.*,d.dev_name,d.email,d.type AS dev_type,s.dev_id AS claim_devid,s.update_type,s.claim_status,s.category_id,s.version_code,s.version,s.language,dv.email AS old_email,dv.dev_name AS old_dev_name')->order('c.update_tm asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			}
		}
		$pic_id = array();
		$package = array();
		if($list) {
			foreach($list as $key=>$val) {
				if(1 || $status==1) {  //通过列表
					
					if($val['softid'] == 0){
					    $soft = $model->table('sj_soft_tmp')->where(array('package'=>$val['package']))->field('dev_id,softname,package,version_code,version,category_id,language,claim_status')->order('version_code desc')->find();
					    $list[$key]['softname'] = $soft['softname'];
					    $list[$key]['version_code'] = $soft['version_code'];
					    $list[$key]['version'] = $soft['version'];
					    $list[$key]['language'] = $soft['language'];

					}else{
						$soft = $model->table('sj_soft')->where(array('package'=>$val['package'],'dev_id'=>array('exp','>0')))->field('dev_id,softname,package,version_code,version,category_id,language,claim_status')->order('version_code desc')->find();
						$list[$key]['softname'] = $soft['softname'];
						$list[$key]['version_code'] = $soft['version_code'];
						$list[$key]['version'] = $soft['version'];
						$list[$key]['language'] = $soft['language'];
						//$categoryids .= substr("{$soft['category_id']}",1);
					}
				}else{
					//$categoryids .= substr("{$val['category_id']}",1);
				}

				//$val['claim_devid'] && $val['claim_status']==2
				if($soft['claim_status']==2 ) {	//已认领
// 				    if($val['softid'] ==0 && !$val['claim_devid'] ) {
// 				        $soft['claim_devid'] = $soft['dev_id'];
// 				    }else{
// 				        $soft['claim_devid'] = $val['claim_devid'];
// 				    }
					if($val['claim_devid'] != $val['dev_id'] ){
					    $soft['claim_devid'] = $val['claim_devid'];
					}else{
					    $soft['claim_devid'] = 0 ;
					}
				   
					$list[$key]['claim_str'] = '';
					unset($old_dev);
					if(!$soft['claim_devid']){
					    $tmp_soft = $model->table('sj_soft_tmp')->where("package='{$val['package']}' and status !=0 ")->order('id desc')->find();
					    $soft['claim_devid'] = $tmp_soft['dev_id'];
					}
					$old_dev = $model->table('pu_developer')->where("dev_id='{$soft['claim_devid']}'")->find();	//原认领者信息
					
					$list[$key]['claim_devname'] = "<a href='/index.php/Dev/User/developer_list/dev_id/{$old_dev['dev_id']}' target='_blank'><span style='color:blue;'>{$old_dev['dev_name']}</span></a>";
					if($old_dev['type']==0) {
						$list[$key]['claim_type_str'] = '公司';
					} else if($old_dev['type']==1) {
						$list[$key]['claim_type_str'] = '个人';
					} else if($old_dev['type']==2) {
						$list[$key]['claim_type_str'] = '团队';
					}
					$list[$key]['claim_email'] = $old_dev['email'];
				} else {			//未认领
					$list[$key]['claim_str'] = '[未认领]';
					if($val['update_type']==0) {
						$list[$key]['claim_devname'] = '(未知)';
					} else if($val['update_type']==1) {
						$list[$key]['claim_devname'] = '(后台)';
					} else if($val['update_type']==2) {
						$list[$key]['claim_devname'] = '(开发者)';
					} else if($val['update_type']==3) {
						$list[$key]['claim_devname'] = '(采集)';
					} else if($val['update_type']==4) {
						$list[$key]['claim_devname'] = '(批量上传)';
					}
				}
                
				if($val['dev_type']==0) {
					$list[$key]['type_str'] = '公司';
				} else if($val['dev_type']==1) {
					$list[$key]['type_str'] = '个人';
				} else if($val['dev_type']==2) {
					$list[$key]['type_str'] = '团队';
				}
				 
				if($val['att2']) $list[$key]['more_pic'] = "<span style='color:blue;'>...</span>";
				$list[$key]['dev_name'] = "<a href='/index.php/Dev/User/developer_list/dev_id/{$val['dev_id']}' target='_blank'><span style='color:blue;'>{$val['dev_name']}</span></a>";
				$list[$key]['update_tm_str'] = date('Y-m-d H:i:s',$val['update_tm']);

				for($i=1;$i<=5;$i++) {
					if($val['att'.$i]) {
						$list[$key]['att'.$i] = $val['att'.$i];
						$pic_id[] = "{$val['id']}_att{$i}";
					}
				}
				$str = str_replace(array("；",";"),"  ",$val['descrip']);
				$descrip = preg_replace('/[a-z]+:\/\/\S+/i', '<a target="_blank"  href="\0">\0</a>', $str);
				$list[$key]['descrip1'] = $descrip;
				//查询最后一次的驳回原因和时间
				unset($reject);
				$reject = $model->table('dev_claim_reject')->where("cid='{$val['id']}'")->order('id DESC')->find();	//原认领者信息
				if($reject) {
					$list[$key]['reject_reason'] = $reject['reject_reason'];
					$list[$key]['reject_tm'] = $reject['reject_tm'];
					$list[$key]['reject_tm_str'] = date('Y-m-d H:i:s',$reject['reject_tm']);
				}

				if($status) {	//通过列表,通过时间
					$list[$key]['pass_tm_str'] = date("Y-m-d",$val['pass_tm'])."<br />".date('H:i:s',$val['pass_tm']);
				}
				if($val['package']) $package[] = $val['package'];
			}
			// $softmodel = D('Dev.Softlist');
			// $iconlist = $softmodel -> new_icon_list('',$package);
			foreach($list as $key=>$val) {
				//图标icon
				if($val['softid'] != 0){
					// if($iconlist[1][$v['package']]['iconurl']){
						// $file_icon['iconurl'] = $iconlist[1][$v['package']]['iconurl'];
					// }else{
						$file_icon = $model ->table('sj_soft_file')->where("softid={$val['softid']} and package_status > 0")->field('iconurl')->find();		
					//}					
				}else{
				    $file_icon = $model ->table('sj_soft_file_tmp')->where("apk_name='{$val['package']}' and package_status > 0")->field('iconurl')->find();
				}				
				$list[$key]['iconurl'] = $file_icon['iconurl'];
			}
			//类别名称
// 			$categoryid['status'] =1;
// 			$categoryid['category_id'] =array('in',substr($categoryids,0,-1));		
// 			$category = $model ->table('sj_category')->where($categoryid)->field('category_id,name,status')->select();
// 			$category_all = array();
// 			foreach($category as $val){
// 				$category_all[$val['category_id']] = $val['name'];
// 			}
// 			foreach($list as $key=>$val) {
// 				if($status==1) {
// 					$soft = $model->table('sj_soft')->where("softid='{$val['softid']}'")->field('version_code,version,category_id')->find();
// 					$categoryid = substr("{$soft['category_id']}",1,-1);
// 				}else{
// 					$categoryid = substr("{$val['category_id']}",1,-1);
// 				}
// 				$list[$key]['category_name'] = $category_all[$categoryid];				
// 			}
		}
		$pic_id_str = "'".implode("','",$pic_id)."'";

		//驳回原因
		$reason_list = $model -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 3 ))->order('pos asc,id desc')->select();
		foreach($reason_list as $key => $val){
		    if($val['content2']){
		        $reason_list[$key]['content2'] = explode('<br />', $val['content2']);
		    }
		}
// 		foreach ($list as $key=>$val){
// 		    if($val['softid'] == 0){
// 		        $soft = $model->table('sj_soft_tmp')->where(array('package'=>$val['package']))->field('softname,version_code,version,category_id,language')->order('version_code desc')->find();
// 		        $list[$key]['softname'] = $soft['softname'];
// 		        $list[$key]['version_code'] = $soft['version_code'];
// 		        $list[$key]['version'] = $soft['version'];
// 		        $list[$key]['language'] = $soft['language'];
// 				//incon
// 				$file_icon = $model ->table('sj_soft_file_tmp')->where("apk_name='{$val['package']}' and package_status > 0")->field('iconurl')->find();	
// 				//echo $model->getlastsql();
// 				$list[$key]['iconurl'] = $file_icon['iconurl'];
// 		    }
// 		}
		//echo '<pre>'; var_dump($list); exit;
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("count", $count );
		$this->assign('p', $p);
		$this->assign('lr', $lr);
		$this->assign('img_host',IMGATT_HOST);
		$this->assign('list', $list);
		$this->assign('pic_id_str', $pic_id_str);
		$this->assign('status', $status);
		$this->assign("reason_list", $reason_list);

		if($status==2) {//待审核
			$this->display('claim_audit');
			$this ->display('Public:check_reject');
		}elseif($status==1){//通过
			$this->display('claim_pass');
		}elseif($status==3){//驳回
			$this->display('claim_reject');
		}
	}

	//详情查看
	public function detail() {
		$id = intval($_GET['id']);

		$model = new Model();
		$detail = $model->table('dev_claim')->where("id='{$id}'")->select();
		$reject = array();
		if($detail) {
			$reject = $model->table('dev_claim_reject')->where("cid='{$id}'")->select();
			if($reject) {
				foreach($reject as $key=>$val) {
					$reject[$key]['reject_tm_str'] = date('Y-m-d H:i:s',$val['reject_tm']);
				}
			}

		} else {
			$detail = array();
		}
		//用正则给有带网址的加链接
		$str = str_replace(array("；",";")," ",$detail[0]['descrip']);
		$descrip = preg_replace('/[a-z]+:\/\/\S+/i', '<a target="_blank"  href="\0">\0</a>', $str);
		$this->assign('detail', $detail[0]);
		$this->assign('descrip', $descrip);
		$this->assign('reject', $reject);
		$this->assign('img_host', IMGATT_HOST);
		$this->display();
	}

	//通过操作
	public function do_pass() {
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));

		$id_str = implode(',',$id_arr);
 
		//开始通过
		$model = new Model();
		$emailmodel = D("Dev.Sendemail");
		$config_txt = C('_config_txt_');
		$arr = $model->table('dev_claim')->where("id IN ({$id_str}) AND status!=0")->select();
		$soft = array();
		$package_arr = array();
		if($arr) {
			//用id作为$arr键名
			foreach($arr as $key=>$val) {
				if(!isset($package_arr[$val['package']])) {
					$package_arr[$val['package']] = $val;
				} else {	//软件包不能重复:不同认领者认领同一个软件包
					exit(json_encode(array('code'=>'0','msg'=>"多个认领者同时认领同一个软件包，无法通过！")));
				}
			}
			$tm = date("Y-m-d",time());
			//对sj_soft表中的dev_id,claim_status进行备份
			foreach($package_arr as $key=>$val) {
				unset($soft,$softid_arr,$softid_str);
				$soft = $model->table('sj_soft')->where("package='{$key}' AND status=1")->select();
				if(!$soft){
				    $soft = $model->table('sj_soft_tmp')->where("package='{$key}' AND status  != 0")->select();
				}
				if($soft) {
					foreach($soft as $k=>$v) {  
						$softname = $v['softname'];
						if(($v['channel_id']!="0" && $v['channel_id']!="") || $v['hide'] >=1024) {	//渠道软件除外
							continue;
						}
						$dev_name = $model->table('pu_developer')-> where("dev_id={$val['dev_id']}")->field('dev_id,email,dev_name')->find();
						//更新sj_soft表，备份插入dev_claim_ori
						$time = time();
						$model->query("INSERT INTO dev_claim_ori (cid,softid,dev_id,claim_status,status,create_tm,update_type,last_tm) VALUES ('{$val['id']}','{$v['softid']}','{$v['dev_id']}','{$v['claim_status']}','1','{$time}','{$v['update_type']}','{$time}')");
						
						$data = array(
							'dev_id' => $val['dev_id'],
							'claim_status' => 2,
							'dev_name' => $dev_name['dev_name'],
						);	
						if($val['del_soft'] == 1){
							$data['status'] = 0;
							$model->table('sj_soft_status')->where("package='{$key}'")->delete(); 
						}
						
						//更新sj_soft 、sj_soft_tmp表
						$model->table('sj_soft')->where("package='{$key}'")->save($data);
						$model->table('sj_soft_tmp')->where("package='{$key}'")->save($data);
						$model->query("UPDATE sj_soft_status SET dev_id='{$val['dev_id']}' WHERE package='{$key}' ");
						
						$model->query("UPDATE sj_soft_whitelist SET dev_id='{$val['dev_id']}',dev_name='{$dev_name['dev_name']}' WHERE package='{$key}' ");
						$model->query("UPDATE sj_sdk_info SET dev_id='{$val['dev_id']}' WHERE package='{$key}' ");
						$model->query("UPDATE sj_soft_debut SET dev_id='{$val['dev_id']}' WHERE package='{$key}' ");
						$model->query("UPDATE sj_soft_screen SET dev_id='{$val['dev_id']}' WHERE package='{$key}' ");
						$model->query("UPDATE sj_new_server SET dev_id='{$val['dev_id']}' WHERE pack_name='{$key}' ");
						$model->query("UPDATE yx_product SET dev_id='{$val['dev_id']}' WHERE package='{$key}' ");
						$model_active  = D('sendNum.sendNum');
						$model_active->query("UPDATE sendnum_active SET dev_id='{$val['dev_id']}' WHERE  id IN (SELECT active_id FROM `olgame_active` WHERE apply_pkg ='{$key}')");
						$model_active->query("UPDATE sendnum_tmp SET dev_id='{$val['dev_id']}' WHERE apply_pkg='{$key}' ");
						$model->query("UPDATE sj_piracy_soft SET dev_id='{$val['dev_id']}',dev_name='{$dev_name['dev_name']}' WHERE package='{$key}' ");
						
						$softid_arr[] = $v['softid'];												
					}
					//修改sj_soft_status表
					getSoftStatusByPackage($key);
					//发送提醒信息
					if($val['dev_id'] != 0){
					    $search   = array("softname", "tm","msg");
					    if($val['del_soft'] == 1){
					        $msg_rep = "该软件已删除，马上进入软件管理提交软件吧~；";
					        $msg_rep2 = "该软件已删除，马上登录开发者平台提交软件吧~；";
					    }else{
					        $msg_rep = 	"马上进入软件管理-已上架列表查看详情吧~；";
					        $msg_rep2 = "马上登录开发者平台查看详情吧~；";
					    }
					    $replace    = array($softname,$tm,$msg_rep);
					    $msg = str_replace($search,$replace,$config_txt['claim_pass']);
					    //发送邮件提醒
					    $subject = $config_txt['claim_subject'];
					    $pass_txt  = $config_txt['claim_pass_txt'];
					    $emailmodel->dev_remind_add($val['dev_id'],$msg);
					    $search2   = array("devname", "softname", "tm","msg","pkg");
					    $replace2  = array($dev_name['dev_name'],$softname, $tm ,$msg_rep2,$key);
					    $email_cont = str_replace($search2,$replace2,$pass_txt);
					    $emailmodel -> realsend($dev_name['email'],$dev_name['dev_name'],$subject,$email_cont);
						//更新pu_developer字段statistics_on
						$softaudit_model = D("Dev.Softaudit");
						$softaudit_model -> update_developer($val['dev_id']);
					}
				}
				//联运游戏认领后开发者id同步到用户中心
				if(isSdkGame($val['package'])){
					$appkey = getAppKey($val['package']);
					if($appkey){
						$vals = array('appKey' => $appkey, 'pid' => $val['dev_id']);
						$res = json_decode(modifyAppPid($vals), true);						
					}
				}
				//写日志
				$softid_str = implode(',',$softid_arr);
				if($val['del_soft'] == 1){
				    $this->writelog("通过了开发者id:{$val['dev_id']}对软件包{$val['package']}的认领,并删除id为:{$_GET['id']} 的软件",'sj_soft',$_GET['id'],__ACTION__,'','edit');
				}else{
				    $this->writelog("通过了开发者id:{$val['dev_id']}对软件包{$val['package']}的认领,软件id为:{$_GET['id']}",'sj_soft',$_GET['id'],__ACTION__,'','edit');
				}
				
			}

			//对dev_claim表更新
			$time = time();
			$model->query("UPDATE dev_claim SET status=1,pass_tm='{$time}',last_tm='{$time}' WHERE id IN ({$id_str}) AND status!=0");

			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		} else {
			exit(json_encode(array('code'=>'0','msg'=>'没有查找到要通过的对象！')));
		}
	}

	//驳回操作
	public function do_reject() {
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);

		$msg = $_REQUEST['msg'];
		if(!$msg) exit(json_encode(array('code'=>'0','msg'=>'请填写驳回原因！')));

		$model = new Model();
		$emailmodel = D("Dev.Sendemail");
		$config_txt = C('_config_txt_');		
		//修改dev_claim状态
		$time = time();
		$where = array(
			'id' => array('in',$id_arr)
		);
		$dev_claim_arr = get_table_data($where,"dev_claim","id","id,dev_id,softname,package");
		$model->query("UPDATE dev_claim SET status=3,last_tm='{$time}' WHERE id IN ({$id_str}) AND status!=0");
		//记录驳回原因
		$time = time();
		foreach($id_arr as $key=>$val) {
			$model->query("INSERT INTO dev_claim_reject (cid,reject_reason,reject_tm) VALUES ('{$val}','{$msg}','{$time}')");
			//日志
			$this->writelog("驳回了开发者id：{$dev_claim_arr[$val]['dev_id']}对软件包[{$dev_claim_arr[$val]['package']}]的认领,驳回原因:{$msg} [认领id：{$val}]",'dev_claim',$val,__ACTION__,'','edit');
		}
		$tm = date("Y-m-d",time());
		foreach($dev_claim_arr as $v){
			//发送提醒信息			
			if($v['dev_id'] != 0){
				$search   = array("softname", "tm",'msg');
				$replace    = array($v['softname'], $tm, $msg);
				$msgs = str_replace($search,$replace,$config_txt['claim_reject']);	
				$emailmodel -> dev_remind_add($v['dev_id'],$msgs);
				//发送邮件提醒
				$dever = $model-> table('pu_developer')->where("dev_id={$v['dev_id']}")-> field('dev_id,email,dev_name') ->find();
				$subject = $config_txt['claim_reject_subject'];		
				$search2   = array("devname", "softname", "tm", 'msg',"pkg");
				$replace2  = array($dever['dev_name'],$v['softname'], $tm, $msg,$v['package']);		
				$email_cont = str_replace($search2,$replace2,$config_txt['claim_reject_txt']);	
				$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
			}
		}		
		exit(json_encode(array('code'=>'1','msg'=>$id_arr)));
	}

	//撤销操作
	public function do_back() {
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);

		//开始撤销操作
		$model = new Model();
		foreach($id_arr as $key=>$val) {	//对每个撤销的开发者进行处理
			$time = time();

			//查找备份信息还原sj_soft
			unset($ori,$softid_arr,$softid_str);
			$softpackage = $model->table('dev_claim')->where("id={$val}")->find();
			
			$ori = $model->table('dev_claim_ori')->where("cid='{$val}' AND status=1")->select();
			
			if($ori) {
				foreach($ori as $k=>$v) {				    
				    $user_data = $model->table('pu_developer')->where("dev_id={$v['dev_id']}")->find();
				    //var_dump($user_data);var_dump($v);exit;
					$model->query("UPDATE sj_soft SET dev_name='{$user_data['dev_name']}', dev_id='{$v['dev_id']}',claim_status='{$v['claim_status']}'  WHERE package='{$softpackage['package']}'");
					
					$model->query("UPDATE sj_soft_whitelist SET dev_id='{$v['dev_id']}',dev_name='{$user_data['dev_name']}' WHERE package='{$softpackage['package']}' ");
					$model->query("UPDATE sj_sdk_info SET dev_id='{$v['dev_id']}' WHERE package='{$softpackage['package']}' ");
					$model->query("UPDATE sj_soft_debut SET dev_id='{$v['dev_id']}' WHERE package='{$softpackage['package']}' ");
					$model->query("UPDATE sj_soft_screen SET dev_id='{$v['dev_id']}' WHERE package='{$softpackage['package']}' ");
					$model->query("UPDATE sj_new_server SET dev_id='{$v['dev_id']}' WHERE pack_name='{$softpackage['package']}' ");
					$model->query("UPDATE yx_product SET dev_id='{$v['dev_id']}' WHERE package='{$softpackage['package']}' ");
					$model->query("UPDATE sj_soft_status SET dev_id='{$v['dev_id']}' WHERE package='{$softpackage['package']}' ");
					$model_active  = D('sendNum.sendNum');
					$model_active->query("UPDATE sendnum_active SET dev_id='{$v['dev_id']}' WHERE  id IN (SELECT active_id FROM `olgame_active` WHERE apply_pkg ='{$softpackage['package']}')");
					$model_active->query("UPDATE sendnum_tmp SET dev_id='{$v['dev_id']}' WHERE apply_pkg='{$softpackage['package']}' ");
					
					$model->query("UPDATE sj_piracy_soft SET dev_id='{$v['dev_id']}',dev_name='{$user_data['dev_name']}' WHERE package='{$softpackage['package']}' ");
					
					$model->query("UPDATE dev_claim_ori SET status=0,last_tm='{$time}' WHERE id='{$v['id']}'");
                    //更新sj_soft_tmp表
                    
					$model->query("UPDATE sj_soft_tmp SET dev_name='{$user_data['dev_name']}', dev_id='{$v['dev_id']}' ,claim_status='{$v['claim_status']}'  WHERE package='{$softpackage['package']}'");
						$softid_arr[] = $v['softid'];
					$softid_arr[] = $v['softid'];
					//联运游戏认领后开发者id同步到用户中心
					if(isSdkGame($softpackage['package'])){
						$appkey = getAppKey($softpackage['package']);
						if($appkey){
							$vals = array('appKey' => $appkey, 'pid' => $v['dev_id']);
							$res = json_decode(modifyAppPid($vals), true);						
						}
					}
				}

				$softid_str = implode(',',$softid_arr);
				$this->writelog("撤消了认领软件id：{$val}的软件认领，撤销的软件id为:{$softid_str}",'dev_claim',$val,__ACTION__,'','edit');
			}

			//dev_claim表标记为待审核
			$model->query("UPDATE dev_claim SET status=2,last_tm='{$time}' WHERE id='{$val}' AND status!=0");
		}

		exit(json_encode(array('code'=>'1','msg'=>$id_arr)));
	}

	//删除操作
	public function do_del() {
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);

		//开始删除
		$time = time();
		$model = new Model();
		$model->query("UPDATE dev_claim SET status=0,last_tm='{$time}' WHERE id IN ({$id_str})");
		foreach($id_arr as $val){
			$this->writelog("删除了认领软件id：{$val}的记录",'dev_claim',$val,__ACTION__,'','del');
		}
		exit(json_encode(array('code'=>'1','msg'=>$id_arr)));
	}
	//投诉举报 2013.9.16
	public function report_list(){
				//分页
				import('@.ORG.Page');
				$model = new Model();
				$where = '';
				if(isset($_GET['status_type'])){
					$where.="status = {$_GET['status_type']}";
					$this->assign('status_type', $_GET['status_type']);
				}else{
					$where.="status = 1";
					$this->assign('status_type', 1);
				}
				if(!empty($_GET['content'])){
					$content = trim($_GET['content']);
					$where.=" and content like '%{$content}%'";
				}
				if(!empty($_GET['reply'])){
					$reply = trim($_GET['reply']);
					$where.=" and reply like '%{$reply}%'";
				}
				if(!empty($_GET['dev_name'])){
					$dev_name = trim($_GET['dev_name']);
					$dev_name_res = $model->table('pu_developer')->where("status =0 and dev_name='{$dev_name}'")->field('dev_id,dev_name')->select();
					$dev_id = $dev_name_res[0]['dev_id'];
					$where.=" and dev_id = '{$dev_id}'";
				}
				if(!empty($_GET['read_type'])){
					if($_GET['read_type']=='1'){
						$where.=" and isread= '0'";
					}else{
						$where.=" and isread= '1'";
					}
					$this->assign('status_type', $_GET['read_type']);
				}

				if(!empty($_GET['email'])){
					$get_email = trim($_GET['email']);
					$email = $model->table('pu_developer')->where("status =0 and email='{$get_email}'")->field('dev_id,email')->select();
					$dev_id = $email[0]['dev_id'];
					$where.=" and dev_id = '{$dev_id}'";
				}
				
				if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
					$begintime = strtotime(trim($_GET['begintime']));
					$endtime = strtotime(trim($_GET['endtime']));
					$where.=" and add_time >={$begintime} and add_time<={$endtime}";
				}
				
				$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
				$total = $model->table('sj_claim_report')->where($where)->count();		
				$page = new Page($total, $limit);
				$res = $model->table('sj_claim_report')->where($where)->order('add_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();
				//echo $model->getlastsql();
				$dev_id_arr=array();
				foreach ($res as $key=>$val){
					$dev_id_arr[]=$val['dev_id'];
				}
				$where_two=array();
				$where_two['status']=0;
				$where_two['dev_id']=array('in',$dev_id_arr);
				$user = $model->table('pu_developer')->where($where_two)->field('dev_id,dev_name,type,email,status')->select();
				$dev_arr = array();
				foreach ($user as $key=>$val){
					$dev_arr[$val['dev_id']] = $val;
				}
				
			$list = array();
			foreach ($res as $key=>$val){
				$links = explode(',', $val['links']);
				$list[$key]['links'] = $links;
				$list[$key]['files'] = explode(',', $val['files']);
				$str = str_replace(array("；",";"),"  ",$val['content']);
				$descrip = preg_replace('/http+:\/\/\S+/i', '<a target="_blank"  href="\0">\0</a><br />', $str);
				$list[$key]['content'] = $descrip;
				$list[$key]['status'] = $val['status'];
				$list[$key]['reply_time'] = $val['reply_time'];
				$list[$key]['reply'] = $val['reply']?$val['reply']:'0';
				$list[$key]['isread'] = $val['isread'];
				$list[$key]['add_time'] = $val['add_time'];
				$list[$key]['update_time'] = $val['update_time'] ? date("Y-m-d H:i:s",$val['update_time']) : '';
				$list[$key]['id'] =     $val['id'];
				$list[$key]['dev_name'] =  $dev_arr[$val['dev_id']]['dev_name'];
				$list[$key]['dev_id'] =  $val['dev_id'];
				$dev_type = $dev_arr[$val['dev_id']]['type'];
				if($dev_type==1){
					$list[$key]['dev_type']='个人';
				}elseif($dev_type==2){
					$list[$key]['dev_type']='团队';
				}else{
					$list[$key]['dev_type']='公司';
				}
				$list[$key]['dev_email'] =  $dev_arr[$val['dev_id']]['email'];
				$softid = array();
				foreach($links as $val){
					$softid[]  = (int)(substr($val,strrpos($val,'_')+1,-5));
				}
				$where_i = array();
				$where_i['package_status'] = 1;
				$where_i['softid'] = array('in',$softid);
				$icon = $model->table('sj_soft_file')->where($where_i)->field('iconurl,softid,apk_name')->select();
				$icon_arr = array();
				foreach($icon as $k=>$v){
					$icon_arr[$v['softid']] = $v;
				}
				$list[$key]['icon'] = $icon_arr;
			}
			$page -> setConfig('header', '篇记录');
			$page -> setConfig('first', '<<');
			$page -> setConfig('last', '>>');
			$this->assign('page', $page->show());
			$this->assign('list', $list);
			$this->display('claim_report');
	}
	//删除投诉举报
	public function report_del(){
		$time = time();
		$flag = true;
		if (!isset($_GET['id'])){
			$this->error('ID不能为空');
		}
		$id = json_decode($_GET['id']);
		if (!$id){
			$this->error('ID格式错误');
		}
		$model = new Model();
		foreach ($id as $v){
			$res = $model->table('sj_claim_report')->where("id = $v")->field('status')->select();
			if ($res[0]['status'] != 0){
				$ret = $model->table('sj_claim_report')->where("id = $v")->save(array('status' => 0,'update_time'=>time()));
				if (!$ret)
					$flag = false;
				else
                   if($res[0]['status']!='1'){
                       $this->writelog('删除了ID为' . $v . '投诉举报','sj_claim_report',$v,__ACTION__,'processed','del');
                   }else{
                       $this->writelog('删除了ID为' . $v . '投诉举报','sj_claim_report',$v,__ACTION__,'','del');
                   }
					
			}
		}
		if ($flag == false){
			$this->error('删除失败');
		}
		else{
			$this->success('删除成功');
		}
	
	}
	//回复 
	public function report_reply(){
		$reply = trim($_POST['reply']);
		$id  = trim($_POST['id']);
		if($reply && $id){
			$model = new Model();
            $res = $model->table('sj_claim_report')->where("id = $id")->field('status')->find();
			$ret = $model->table('sj_claim_report')->where("id = $id")->save(array('reply' => $reply,'status'=>2,'reply_time'=>time()));
		     if($ret){
				if($res['status']!=1){
					$this->writelog("处理了举报申诉(数据id：{$_POST['id']})回复内容({$_POST['reply']})",'sj_claim_report',$_POST['id'],__ACTION__,'processed','edit');
				}else{
					$this->writelog("处理了举报申诉(数据id：{$_POST['id']})回复内容({$_POST['reply']})",'sj_claim_report',$_POST['id'],__ACTION__,'','edit');
				}
			    $result = array ('success' => true,'msg'=>'操作成功');			    
			    echo json_encode ($result);
				exit();
			}else{
				$result = array ('success' => false,'msg'=>'操作失败');
			    echo json_encode ($result);
				exit();
			}
			
		}else{
			$result = array ('success' => false,'msg'=>'非法操作');
			echo json_encode ($result);
			exit();
		}
	}
	//查看附件 
	public function show_file(){
		$id = trim($_POST['id']);
		$model = new Model();
		$ret = $model->table('sj_claim_report')->where("id = $id")->save(array('isread'=>1));
		$res = $model->table('sj_claim_report')->where("id='{$id}'")->select();		
		//echo $model->getlastsql();
		if($res){
		$result = array ('success' => true,'rows'=>array('files'=>explode(',', $res[0]['files']),'links'=>explode(',', $res[0]['links']),'content'=>$res[0]['content'],'reply'=>$res[0]['reply'],'status'=>$res[0]['status']));
		}else{
			$result = array ('success' => false,'rows'=>$res);
		}
		echo json_encode ($result);
		exit();
	}
}
