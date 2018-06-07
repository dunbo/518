<?php
class AnzhiremindAction extends CommonAction {
	//安智提醒列表
	function remind_list(){
		$model = M('anzhi_remind');
		$where['status'] = 1;
		//搜索
		if($_GET){
			if(isset($_GET['content'])){
				$this->assign('content',$_GET['content']);
				$where['remind_content'] = array("like","%{$_GET['content']}%");
			}		
			if(isset($_GET['send_obj'])){
				$this->assign('send_obj',$_GET['send_obj']);
				$where['send_obj'] = array("eq","{$_GET['send_obj']}");
			}	
			if(isset($_GET['devid'])){
				if(!isset($_GET['send_obj'])){
					$where['send_obj'] = 4;
				}
				$this->assign('devid',$_GET['devid']);
				$where['send_id'] = array("like","%{$_GET['devid']};%");
			}	
			if(isset($_GET['mail_notification'])){
				$this->assign('mail_notification',$_GET['mail_notification']);
				$where['mail_notification'] = array("eq","{$_GET['mail_notification']}");
			}
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$endtime = strtotime($_GET['endtime']);
				$this->assign('begintime',$_GET['begintime']);
				$this->assign('endtime',$_GET['endtime']);
				$where['create_tm'] = array(array("egt",$begintime),array("elt",$endtime));
			}
		}
		//分页
		$total = $model->where($where)->count();
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$param = http_build_query($_GET);
		import('@.ORG.Page2');
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		$list = $model -> where($where)->limit($Page->firstRow.','.$Page->listRows)->order('create_tm desc')->select();
		//展示数据
		foreach($list as $k => $v){
			$list[$k]['create_tm'] = $v['create_tm'] ? date('Y-m-d H:i:s',$v['create_tm']) : '';
			$devidarr = explode(";",$v['send_id']);
			$send_id_5 = array_slice($devidarr,0,5);
			$send_id = '';
			foreach($send_id_5 as $val){
				$send_id .= $val.";";
			}
			$list[$k]['send_id_5'] = substr($send_id,0,-1);	
		}
		$this->assign('list',$list);
		$this -> assign('total', $total);
		$this -> assign('page', $Page->show());
		$this -> display();
	}
	//安智提醒列表__发送提醒
	function send_remind_add(){
		$model = new Model();
		$emailmodel = D("Dev.Sendemail");
		$dev_id = $_POST['dev_id'];
		$msgs = str_replace(' ','',trim($_POST['content_P']));
		$mail = $_POST['mail'];
		$sendobj = $_POST['sendobj'];
		$dev_prompt = !empty($_POST['dev_prompt']) ? $_POST['dev_prompt'] : 0;
		if(empty($_FILES['csv']['tmp_name']) && empty($dev_id) && $sendobj == ''){
			//exit ("<script>alert('id不为空');history.go(-1);</script>");	
			$this -> error('id不为空');	
		}
		if(empty($msgs) && $msgs == ''){
			$this -> error('内容不为空');	
		}
		if(empty($sendobj) && $sendobj == ''){
			if(!empty($_FILES) || isset($dev_id)){
				$id_arr = explode(';',$dev_id);
				if (!empty($_FILES['csv']['tmp_name'])) {
					$array = array('csv');
					$ytypes = $_FILES['csv']['name'];
					$info = pathinfo($ytypes);
					$type =  $info['extension'];//获取文件件扩展名
					if(!in_array($type,$array)){
						$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
						$this->error('上传格式错误');
					}
					
					if (!empty($_FILES['csv']['tmp_name'])){		
						$fp = fopen($_FILES['csv']['tmp_name'], 'r');
						$id = array();
						while(!feof($fp)){
							$id_f = fgetcsv($fp);
							if (empty($id_f)) continue;
							$id_f = trim($id_f[0]);
							$id[] = $id_f;
						}
						fclose($fp);
					}
				}
				if(!$id){
					//这个ID不存在时给重制下数据
					$id = array();
				}	
				
				if($id_arr){	
					foreach($id_arr as $val){
						if(in_array($val,$id)){
							continue;
						}else{	
							//如果没有这个值把这个值追加到id数组后面
							array_push($id,$val);
						}
					}
				}

				$id_str = '';
				$error_id = '';
				$rid_arr = array();
				foreach($id as $v){
					//检测邮箱是否有效
					$dever = $model-> table('pu_developer')->where("dev_id={$v} and status=0 and email_verified =1")-> field('dev_id,email,dev_name') ->find();
					if(!$dever){
						$error_id .= $v.","; 
					}else{
						$id_str .= $v.";"; 
						$rid_arr[] = $v;
					}
					if($mail == 1){
						//发送邮件
						$subject = $_POST['title_email'];
						$send = $emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$msgs);
						
					}
				}
			}
		}
		if(isset($sendobj) && $sendobj != ''){
			$data['send_obj'] = $sendobj;
			//异步发送
			if($mail == 1){
				$task_client = get_task_client();
				if($sendobj == 3){
					$sendobj = array('exp','>=0');
				}
				$subject = $_POST['title_email'];
				$task_client->doBackground("send_mail_log", json_encode(array("send_obj" =>$sendobj,"msgs"=>$msgs,"subject"=>$subject,"atime"=>time())));			
			}
		}elseif(empty($sendobj)){
			$data['send_obj'] = 4;
			//$id_str = substr($id_str,0,-1);
			$data['send_id'] = $id_str ;
		}
		//邮件发送不成功的ID
		$error_id = substr($error_id,0,-1);
		if(!empty($error_id)){
			echo "<script>alert('信息发送失败，失败id为{$error_id}'); location.href='/index.php/Dev/Anzhiremind/remind_list';</script>";
		}
		$tm = time();
		$data['remind_content'] = $msgs;
		$data['mail_notification'] = $mail ? $mail : 0;
		$data['create_tm'] = $tm;
		$data['update_tm'] = $tm;
		$data['status'] = 1;
		$data['dev_prompt'] = $dev_prompt;
		$add_remind = $model -> table('sj_anzhi_remind')-> data($data)->add();
		if($add_remind){
			foreach($rid_arr as $v){
				//发送提醒
				$emailmodel -> dev_remind_add($v,$msgs,$add_remind);
			}			
			$this->writelog("发送了提醒id为{$add_remind}提醒内容为{$msgs}的提醒。", 'sj_anzhi_remind', $add_remind,__ACTION__ ,"","add");
			$this -> success('提交成功');
		}
	}
	//安智提醒信息列表__删除操作
	function remind_del(){
		$model =  M('anzhi_remind');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$id_str = implode(',',$id_arr);
		$list = $model -> where(array('id'=>array('in',$id_arr))) -> field('id,status') -> select();
		foreach($list as $k => $v){
	 		if($v['status'] == 1){	
				$del = $model -> where("id={$v['id']}") -> save(array('status'=>0));
				if(!$del){
					exit(json_encode(array('code'=>'0','msg'=>"ID为{$v['id']}操作不成功")));
				} else {
					$this->writelog("删除了{$v['id']}的提醒", 'sj_anzhi_remind', $v['id'],__ACTION__ ,"","del");
				}
			}else{
				exit(json_encode(array('code'=>'0','msg'=>"ID为{$v['id']}已经被删除过了")));
			}
			$idarr[] = $v['id'];
		}
		exit(json_encode(array('code'=>1,'msg'=>$idarr)));
	}
	//安智公告_列表
	function bulletin_list(){
		$model = M('anzhi_bulletin');
		$where['status'] = 1;
		//搜索
		$time = time();
		if(isset($_GET['type'])){
			$this->assign('type',$_GET['type']);
			$where['type'] = $_GET['type'];
		}else{
			$this->assign('type',0);
			$where['type'] =0;
		}
		if($_GET){
			if(isset($_GET['content'])){
				$this->assign('content',$_GET['content']);
				$where['content'] = array("like","%{$_GET['content']}%");
			}			
			if(!empty($_GET['zh_type'])){
				if($_GET['zh_type'] == 1){
					$where['start_tm'] = array('elt',$time);
					$where['end_tm'] = array( 'egt',$time);
				}else if($_GET['zh_type'] == 2){
					$where['end_tm'] = array('lt',$time);						
				}else if($_GET['zh_type'] == 3){
					$where['start_tm'] = array ('gt',$time);
				}
				$this->assign("zh_type",$_GET['zh_type']);
			}
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$endtime = strtotime($_GET['endtime']);
				$this->assign('begintime',$_GET['begintime']);
				$this->assign('endtime',$_GET['endtime']);
				$where['create_tm'] = array(array("egt",$begintime),array("elt",$endtime));
			}
		}
		//分页
		$total = $model->where($where)->count();
		$pos_max = $model->where($where)->field('pos')->order('pos desc')->find();
		$pos_max =$pos_max['pos'];
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$param = http_build_query($_GET);
		import('@.ORG.Page2');
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		$list = $model -> where($where)->limit($Page->firstRow.','.$Page->listRows)->order("pos asc")->select();
		//展示数据
		foreach($list as $k => $v){
			$list[$k]['start_tm'] = $v['start_tm'] ? date('Y-m-d H:i:s',$v['start_tm']) : '';
			$list[$k]['end_tm'] = $v['end_tm'] ? date('Y-m-d H:i:s',$v['end_tm']) : '';
			$list[$k]['create_tm'] = $v['create_tm'] ? date('Y-m-d H:i:s',$v['create_tm']) : '';
			$list[$k]['update_tm'] = $v['update_tm'] ? date('Y-m-d H:i:s',$v['update_tm']) : '';
			$list[$k]['content'] = $v['content'] ? str_replace('IMG_HOST',IMGATT_HOST,$v['content']) : '';
			if(($v['start_tm'] <= time())&&($v['end_tm'] >= time())){
				$list[$k]['type'] = 1; //当前显示
			}elseif($v['end_tm'] < time()){
				$list[$k]['type'] = 2;//过期
			}elseif($v['start_tm'] > time()){
				$list[$k]['type'] = 3;//未开始
			}else{
				$list[$k]['type'] = 0;
			}
			//排序
			$tmp = "<select rel='{$v['id']}' name='rank' class='extent_rank'>";
			for($i=1;$i<=$pos_max;$i++) {
				$select = $i==$v['pos'] ? ' selected' : '';
				$tmp .= "<option value='{$i}'{$select}>{$i}</option>";
			}
			$tmp .= "</select>";
			$list[$k]['pos_str'] = $tmp;
		}
		// header('content-type:text/html;charset=utf-8');
		// echo "<pre>";var_dump($list);die;
		$this->assign('list',$list);
		$this -> assign('total', $total);
		$this -> assign('page', $Page->show());
		$this -> display();
	}
	//删除安智公告
	function bulletin_del(){
		$model = M('anzhi_bulletin');
		$id = $_GET['id'];
		$reason_list['status'] = 0;
		$reason_list['update_tm'] = time();		
		$pos_list = $model-> where(array("status" =>1,"id" => $id))->field('pos')-> find();
		$where['pos'] = array('gt',$pos_list['pos']);
		$where['status'] = 1;
		$list = $model->where($where)-> select();
		foreach($list as $v){
			$sql = "update sj_anzhi_bulletin set pos = pos-1 where id={$v['id']}";
			$model -> query($sql);
		}	
		$affect = $model-> where(array("status" =>1,"id" => $id))->save($reason_list);
		if($affect){
			$this->writelog("删除了id为{$id}的安智公告","sj_anzhi_bulletin",$id,__ACTION__ ,"","del");
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	//编辑和添加公告面板
	function bulletin_edit_add(){
		if(is_numeric($_GET['id'])) {
			$model = new Model();
			$detail = $model->Table('sj_anzhi_bulletin')->where("id={$_GET['id']}")->find();
			$detail['content'] = str_replace('IMG_HOST',IMGATT_HOST,$detail['content']);
			$begintime = date("Y-m-d H:i:s",$detail['start_tm']);
			$endtime = date("Y-m-d H:i:s",$detail['end_tm']);
			$this->assign("editor_id", $_GET['id']);
			$this->assign("detail", $detail);
			$this->assign("type", $detail['type']);
		} else {
			unset($_GET['id']);
			$begintime = date("Y-m-d 00:00:00",time());
			$endtime = "2023-1-1 23:59:59";
		}
		if(isset($_GET['type'])){
			$type=$_GET['type']?$_GET['type']:0;
			$this->assign('type',$type);
		}
		$this->assign("begintime", $begintime);
		$this->assign("endtime", $endtime);
		$this->display();
	}
	//编辑和添加公告__提交
	function bulletin_add(){
		if(empty($_POST['editor_preurl'])) $_POST['editor_preurl'] = '/index.php/Dev/Anzhiremind/bulletin_list/';
		//对内容是否为<p>&nbsp;</p>的检查
		$tmp = trim(strip_tags($_POST['editor_content']));
		if(!$_POST['editor_content']) {
			$this->assign('jumpUrl',$_POST['editor_preurl']);
			$this->error('请填写公告内容！');
		}
		$begintime = strtotime($_POST['begintime']);
		$end_tm = strtotime($_POST['endtime']);
		if($begintime > $end_tm){
			$this -> error('开始时间不能大于结束时间');
		}
		//文章中图片处理,开始
		preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$_POST['editor_content'],$matches);
		if($matches[1]) {	//有需要上传的新图片
			$pre_path = $_SERVER['DOCUMENT_ROOT'];		//web根目录
			//图片宽度不超过600px检查
			foreach($matches[1] as $key => $val) {
				unset($width,$height,$type,$attr);
				list($width,$height,$type,$attr) = getimagesize($pre_path.$val);
				if($width>600) {
					$this->assign('jumpUrl',$_POST['editor_preurl']);
					$this->error('有图片宽度超过限定的600px，请检查！');
					exit;
				}
			}

			//上传图片
			$files = array();
			$files_name = array();
			foreach($matches[1] as $key => $val) {
				$upload_model = D("Dev.Uploadfile");
				$files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
			}
			foreach($matches[1] as $key => $val) {
				$files[$files_name[$key]] = '@'.$pre_path.$val;
			}
			$arr = AnzhiremindAction::dev_upload($files);
			if($arr['info']['http_code']!=200) {
				$this->assign('jumpUrl',$_POST['editor_preurl']);
				$this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
			}
			//删除public下图片
			foreach($matches[1] as $key => $val) {
				unlink($pre_path.$val);
			}
			$new_arr = array();
			if($arr['ret']) {
				foreach($arr['ret'] as $key=>$val) {
					unset($k,$new_k);
					$k = array_search($key,$files_name);
					$new_k = $matches[1][$k];
					$new_arr[$new_k] = 'IMG_HOST'.$val;
				}
				//文章内容中图片路径替换
				$_POST['editor_content'] = strtr($_POST['editor_content'],$new_arr);
			}
		}
		//文章中图片处理,结束
		//插入数据库
		$model = new Model();
		//统计有效数据
		$platform_type=$_POST['type'];
		$count = $model->table("sj_anzhi_bulletin")->where("status=1 and type=$platform_type")->count();
		$data = array();
		$editor_id = intval($_POST['editor_id']);
		$do = '';
		if($editor_id) {
			$data['dev_prompt'] = $_POST['dev_prompt']?$_POST['dev_prompt']:0;
			$data['content'] = $_POST['editor_content'];
			$data['update_tm'] = time();
			$data['start_tm'] =	$begintime;
			$data['end_tm'] = $end_tm;
			$res = $model->table('sj_anzhi_bulletin')->where("id='{$editor_id}'")->save($data);
			$do = '编辑';
			$log = "编辑了id为{$editor_id}的公告信息！";
		} else {
			$data['start_tm'] = $begintime;
			$data['end_tm'] = $end_tm;
			$data['content'] = $_POST['editor_content'];
			if($_POST['dev_prompt']) $data['dev_prompt'] = $_POST['dev_prompt'];
			$data['status'] = 1;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['type'] = $platform_type;
			$data['pos'] = $count+1;
			$res = $model->table('sj_anzhi_bulletin')->add($data);
			$do = '添加';
			$log = "添加了id为{$res}的公告信息！";
		}
		if($res) {
			if($editor_id){
				$type = 'edit';
				$id = $editor_id;
			}else{
				$type = 'add';
				$id = $res;
			}
			$this->writelog($log, 'sj_anzhi_bulletin', $id,__ACTION__ ,"",$type);
			$this->assign('jumpUrl',$_POST['editor_preurl']."type/".$_POST['type']);
			$this->success("{$do}公告成功！");
		} else {
			$this->assign('jumpUrl',$_POST['editor_preurl']);
			$this->error("数据{$do}失败，请重试！");
		}
	}
	//动态信息添加_图片处理,代码来源:/dev.goapk.com/common.php
	public static function dev_upload($files) {
		$vals = array(
			'do' => 'save',
			'static_data' => '/data/att/m.goapk.com',
		);
		$upload_model = D("Dev.Uploadfile");
		return $upload_model -> _http_post(array_merge($vals,$files));
	}

	//排序
	function reason_sequence() {
	    if(isset($_GET)){
			$table       = 'sj_anzhi_bulletin';
			$field       = 'pos';
			$extent_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['pos'];
			$type = (int)$_GET['type'];
			$where_rank = array(
				'status' => 1,
				'type' =>$type,
			);
			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
			exit(json_encode($param));
		}
	}
	//安智已发送开发者邮件列表
	function email_list(){
		$model = M('developer_mail_notice');
		$where['status'] = 1;
		$this->assign('soft_two_category2',-1);
		$this->assign('soft_parent_category2',-1);
		//搜索
		if($_GET){
			if(isset($_GET['subject'])){
				$this->assign('subject',$_GET['subject']);
				$where['subject'] = array("like","%{$_GET['subject']}%");
			}		
			if(isset($_GET['send_obj'])){
				$this->assign('send_obj',$_GET['send_obj']);
				$where['send_obj'] = array("eq","{$_GET['send_obj']}");
			}
			if(isset($_GET['whitelist_type2']) && $_GET['whitelist_type2']!=0){
				$this->assign('whitelist_type2',$_GET['whitelist_type2']);
				$where['whitelist_type'] = array("eq","{$_GET['whitelist_type2']}");
			}else if(isset($_GET['whitelist_type2'])){
				$this->assign('whitelist_type2',$_GET['whitelist_type2']);
			}	
			if(isset($_GET['soft_parent_category2']) && $_GET['soft_parent_category2']!=0){
				$this->assign('soft_parent_category2',$_GET['soft_parent_category2']);
				$where['soft_parent_category'] = array("eq","{$_GET['soft_parent_category2']}");
			}else if(isset($_GET['soft_parent_category2'])){
				$this->assign('soft_parent_category2',$_GET['soft_parent_category2']);
				
			}	
			if($_GET['soft_parent_category2']==0){
				$this->assign('soft_parent_category2',-1);
			}
			if(isset($_GET['soft_two_category2']) && $_GET['soft_two_category2']!=0){
				$this->assign('soft_two_category2',$_GET['soft_two_category2']);
				$where['soft_two_category'] = array("eq","{$_GET['soft_two_category2']}");
			}else if(isset($_GET['soft_two_category2'])){
				$this->assign('soft_two_category2',$_GET['soft_two_category2']);
			}
			if($_GET['soft_two_category2']==0){
				$this->assign('soft_two_category2',-1);
			}
			if(isset($_GET['devid'])){
				if(!isset($_GET['send_obj'])){
					$where['send_obj'] = 4;
				}
				$this->assign('devid',$_GET['devid']);
				$where['send_id'] = array("like","%{$_GET['devid']};%");
			}	
			if(isset($_GET['mail_notification'])){
				$this->assign('mail_notification',$_GET['mail_notification']);
				$where['mail_notification'] = array("eq","{$_GET['mail_notification']}");
			}
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$endtime = strtotime($_GET['endtime']);
				$this->assign('begintime',$_GET['begintime']);
				$this->assign('endtime',$_GET['endtime']);
				$where['create_tm'] = array(array("egt",$begintime),array("elt",$endtime));
			}
		}
		//分页
		$total = $model->where($where)->count();
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$param = http_build_query($_GET);
		import('@.ORG.Page2');
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		$list = $model -> where($where)->limit($Page->firstRow.','.$Page->listRows)->order('create_tm desc')->select();
		//展示数据
		foreach($list as $k => $v){
			$list[$k]['create_tm'] = $v['create_tm'] ? date('Y-m-d H:i:s',$v['create_tm']) : '';
			$devidarr = explode(";",$v['send_id']);
			$send_id_5 = array_slice($devidarr,0,5);
			$send_id = '';
			foreach($send_id_5 as $val){
				$send_id .= $val.";";
			}
			$list[$k]['send_id_5'] = substr($send_id,0,-1);
			$admin_data = $model ->table('sj_admin_users')-> where(array('admin_user_id'=>$v['admin_id']))->find();
			$list[$k]['admin_name'] = $admin_data['admin_user_name'];
			$soft_parent_category = $model ->table('sj_category')-> where(array('category_id'=>$v['soft_parent_category']))->field('category_id,name')->find();
			if($v['soft_parent_category']!=''){
				$list[$k]['soft_parent_category_name'] = ($v['soft_parent_category']==0)?'全部':$soft_parent_category['name'];
			}else{
				$list[$k]['soft_parent_category_name']='';
			}
			$soft_two_category = $model ->table('sj_category')-> where(array('category_id'=>$v['soft_two_category']))->field('category_id,name')->find();
			if($v['soft_two_category']!=''){
				$list[$k]['soft_two_category_name'] = ($v['soft_two_category']==0)?'全部':$soft_two_category['name'];
			}else{
				$list[$k]['soft_two_category_name']='';
			}
			$list[$k]['whitelist_type'] = ($v['whitelist_type']=='')?3:$v['whitelist_type'];
		}
		//获取软件分类数据
		// SELECT * FROM `sj_category` WHERE parentid=0
		$category_parent = $model ->table('sj_category')-> where(array('parentid'=>0))->field('category_id,name,parentid')->select();
		$parentid=array();
		foreach($category_parent as $k=>$v){
			$parentid[]=$v['category_id'];
		}
		$category_two = $model ->table('sj_category')-> where(array('parentid'=>array('in',$parentid)))->field('category_id,name,parentid')->select();
		$this->assign('category_parent',$category_parent);
		$this->assign('category_two',$category_two);

		$this->assign('list',$list);
		$this -> assign('total', $total);
		$this -> assign('page', $Page->show());
		$this -> display();
	}
	//安智提醒列表__发送邮件
	function send_email_add(){
		if($_POST['width']){
			$title_email=$_POST['title_email2'];
			$preview_content=$_POST['preview_content2'];
			$this -> assign('title_email', $title_email);
			$this -> assign('preview_content', $preview_content);
			$this -> assign('time', date('Y-m-d',time()));
			$this -> display('preview_content');
			die;
		}
		// echo "<pre>";var_dump($_POST);die;
		$model = new Model();
		$emailmodel = D("Dev.Sendemail");
		$dev_id = $_POST['dev_id'];
		$is_type = $_POST['is_type'];
		// var_dump($is_type);die;
		$time=date('Y-m-d',time());
		//文章中图片处理,开始
		preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$_POST['content_P'],$matches);
		if($matches[1]) {	//有需要上传的新图片
			$pre_path = $_SERVER['DOCUMENT_ROOT'];		//web根目录
			//图片宽度不超过600px检查
			foreach($matches[1] as $key => $val) {
				unset($width,$height,$type,$attr);
				list($width,$height,$type,$attr) = getimagesize($pre_path.$val);
				// if($width>600) {
				// 	exit( "<script language='javascript'> alert('有图片宽度超过限定的600px，请检查！');  window.history.back(-1);</script>");	
				// }
			}

			//上传图片
			$files = array();
			$files_name = array();
			foreach($matches[1] as $key => $val) {						
				$upload_model = D("Dev.Uploadfile");
				$files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
			}
			foreach($matches[1] as $key => $val) {
				$files[$files_name[$key]] = '@'.$pre_path.$val;
			}
			$arr = AnzhiremindAction::dev_upload($files);
			if($arr['info']['http_code']!=200) {
				// $this->assign('jumpUrl',$_POST['editor_preurl']);
				$this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
			}
			//删除public下图片
			foreach($matches[1] as $key => $val) {
				unlink($pre_path.$val);
			}
			$new_arr = array();
			if($arr['ret']) {
				foreach($arr['ret'] as $key=>$val) {
					unset($k,$new_k);
					$k = array_search($key,$files_name);
					$new_k = $matches[1][$k];

					$new_arr[$new_k] = IMGATT_HOST_CDN.$val;
				}
				//文章内容中图片路径替换
				$_POST['content_P'] = strtr($_POST['content_P'],$new_arr);
			}
		}
		$msgs = trim($_POST['content_P'])."<br/><br/><br/><br/><br/>如有疑问，请与安智客服联系（http://dev.anzhi.com/contact_us.php）<br/>安智开发者联盟敬上<br/>http://dev.anzhi.com <br/>日期：{$time} <br/>(系统邮件,请勿回复)<br/>";
		// $mail = $_POST['mail'];
		$sendobj = $_POST['sendobj'];
		// if($is_type==1){
		// 	$sendobj='';
		// }
		// $dev_prompt = !empty($_POST['dev_prompt']) ? $_POST['dev_prompt'] : 0;
		if(empty($_FILES['csv']['tmp_name']) && empty($dev_id) && $is_type == 1){
			//exit ("<script>alert('id不为空');history.go(-1);</script>");	
			$this -> error('id不为空');	
		}
		if(empty($msgs) && $msgs == ''){
			$this -> error('内容不为空');	
		}
		
		if($is_type==1){
			if(!empty($_FILES) || isset($dev_id)){
				$id_arr = explode(';',$dev_id);
				if (!empty($_FILES['csv']['tmp_name'])) {
					$array = array('csv');
					$ytypes = $_FILES['csv']['name'];
					$info = pathinfo($ytypes);
					$type =  $info['extension'];//获取文件件扩展名
					if(!in_array($type,$array)){
						$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
						$this->error('上传格式错误');
					}
					
					if (!empty($_FILES['csv']['tmp_name'])){		
						$fp = fopen($_FILES['csv']['tmp_name'], 'r');
						$id = array();
						while(!feof($fp)){
							$id_f = fgetcsv($fp);
							if (empty($id_f)) continue;
							$id_f = trim($id_f[0]);
							$id[] = $id_f;
						}
						fclose($fp);
					}
				}
				if(!$id){
					//这个ID不存在时给重制下数据
					$id = array();
				}	
				
				if($id_arr){	
					foreach($id_arr as $val){
						if(in_array($val,$id)){
							continue;
						}else{	
							//如果没有这个值把这个值追加到id数组后面
							array_push($id,$val);
						}
					}
				}

				$id_str = '';
				$error_id = '';
				$rid_arr = array();
				foreach($id as $v){
					//检测邮箱是否有效
					$dever = $model-> table('pu_developer')->where("dev_id={$v} and status=0 and email_verified =1")-> field('dev_id,email,dev_name') ->find();
					if(!$dever){
						$error_id .= $v.","; 
					}else{
						$id_str .= $v.";"; 
						$rid_arr[] = $v;
					}
					// if($mail == 1){
						//发送邮件
						$subject = $_POST['title_email'];
						$send = $emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$msgs);
						
					// }
				}
			}
		}
		if($is_type==2){
			$data['send_obj'] = $sendobj;
			$data['whitelist_type'] = $_POST['whitelist_type'];
			$data['soft_parent_category'] = $_POST['soft_parent_category'];
			if($_POST['soft_parent_category']!=''){
				$data['soft_two_category'] = $_POST['soft_two_category'];
			}else{
				$data['soft_two_category'] = '';
			}
			
			//发送对象数组
			$send_type=array();
			//异步发送
			$task_client = get_task_client();
			if($sendobj == 3){
				$sendobj = array('exp','>=0');
			}
			if($sendobj != ''){
				$send_type['send_obj2']=$sendobj;
			}
			if($_POST['whitelist_type']!=''){
				$send_type['whitelist_type']=$_POST['whitelist_type'];
			}
			if($_POST['soft_parent_category']!=''){
				$send_type['soft_parent_category']=$_POST['soft_parent_category'];
				if($_POST['soft_parent_category']!=0){
					$send_type['soft_two_category']=$_POST['soft_two_category'];
				}
				
			}
			$subject = $_POST['title_email'];
			$task_client->doBackground("developer_notice_send_mail", json_encode(array("send_obj" =>$send_type,"msgs"=>$msgs,"subject"=>$subject,"atime"=>time())));
			// $task_client->doBackground("send_mail_log", json_encode(array("send_obj" =>$sendobj,"msgs"=>$msgs,"subject"=>$subject,"atime"=>time())));
		}elseif($is_type==1){
			$data['send_obj'] = 4;
			$data['send_id'] = $id_str ;
		}
		$data['type'] = $is_type;
		$error_id = substr($error_id,0,-1);
		if(!empty($error_id) && empty($id_str)){
			echo "<script>alert('信息发送失败，失败id为{$error_id}'); location.href='/index.php/Dev/Anzhiremind/email_list';</script>";
			die;
		}
		$tm = time();
		$data['content'] = $msgs;
		$data['subject'] =$subject;
		$data['admin_id'] =$_SESSION['admin']['admin_id'];
		// $data['mail_notification'] = $mail ? $mail : 0;
		$data['create_tm'] = $tm;
		// $data['create_time'] = $tm;
		$data['status'] = 1;
		// $data['dev_prompt'] = $dev_prompt;
		$add_remind = $model -> table('sj_developer_mail_notice')-> data($data)->add();
		// var_dump($model->getlastsql());die;
		if($add_remind){			
			$this->writelog("发送了邮件id为{$add_remind},邮件内容为{$msgs}的邮件。", 'sj_developer_mail_notice', $add_remind,__ACTION__ ,"","add");
			//邮件发送不成功的ID
			if(!empty($error_id)){
				echo "<script>alert('信息发送失败，失败id为{$error_id}'); location.href='/index.php/Dev/Anzhiremind/email_list';</script>";
				die;
			}
			$this -> success('提交成功');
		}
	}
}
?>
