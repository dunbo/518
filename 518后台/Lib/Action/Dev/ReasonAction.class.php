<?php
class ReasonAction extends CommonAction {
    /**
		* 内置原因展示
	*/
	function reason_list($reason_type,$tpl){
		if($reason_type){
			$reason = $reason_type;
		}else{
			$reason = $_GET['reason_type'] ? $_GET['reason_type']:1;
			$tpl = "reason_list";
		}
		if(isset($_GET['p'])){
			$p =  $_GET['p'];
		}else{
			$p = 1;
		}
		if(isset($_GET['lr'])){
			$lr =  $_GET['lr'];
		}else{
			$lr = 10;
		}
		$model = new Model();
		import("@.ORG.Page2");
		$where  = array(
			"status" => 1,
			"reason_type" => $reason 
		);
		$this->check_where($where, 'pid');
		$this->check_where($where, 'content', 'isset', 'like');
		$this->check_where($where, 'content2', 'isset', 'like');
		$this->check_range_where($where, 'begintime', 'endtime', 'update_tm', true);
		$count = $model -> table("dev_reason") -> where($where)->count();
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$list = $model -> table("dev_reason") -> where($where)->order("pos asc,id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
		foreach($list as $k => $v){
			$list[$k]['create_date'] = date("Y-m-d H:i",$v['create_tm']);
			$list[$k]['update_tm'] = date("Y-m-d H:i",$v['update_tm']);
			$list[$k]['content2'] = str_replace('<br />', ';', $list[$k]['content2']);
			//排序
			$tmp = "<select rel='{$v['id']}' name='rank' class='extent_rank'>";
			for($i=1;$i<=$count;$i++) {
				$select = $i==$v['pos'] ? ' selected' : '';
				$tmp .= "<option value='{$i}'{$select}>{$i}</option>";
			}
			$tmp .= "</select>";
			$list[$k]['pos_str'] = $tmp;
		} 
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign("reason", $reason);
		if($reason == 8){
			$util = D("Sj.Util");
			$product = $util -> getProducts(array('pid'=>$_GET['pid']));
			$this->assign('product', $product);		
		}
		$this->assign("p", $p);
		$this->assign("lr", $lr);
		$this -> assign("list",$list);
		$this -> display($tpl);
	}
	//快捷回复管理
	function reply_content(){
		$reason_type = $_GET['reason_type'] ? $_GET['reason_type']:8;
		$this->reason_list($reason_type,"reply_content");
	}
	//采集忽略原因
	function cj_reason(){
		$reason_type = $_GET['reason_type'] ? $_GET['reason_type']:12;
		$this->reason_list($reason_type,'cj_reason');
	}
	/**
		内置原因添加
	*/
	function reason_add(){
		$model = new Model();
		$reason = $_GET['reason'] ? $_GET['reason'] : 1;
		if($_POST){
			$reason_add = $_POST['reason'];
			if($reason_add==1){
				$reason_info ="驳回";
			}elseif($reason_add==2){
				$reason_info ="屏蔽";
			}elseif($reason_add==3){
				$reason_info ="认领软件驳回";
			}elseif($reason_add==4){
				$reason_info ="软件驳回原因";
			}elseif($reason_add==5){
				$reason_info ="下架原因";
			}elseif($reason_add==6){
				$reason_info ="用户屏蔽原因";
			}elseif($reason_add==7){
				$reason_info ="评论删除原因";
			}elseif($reason_add==8){
				$reason_info ="软件反馈回复";
			}elseif($reason_add==9){
				$reason_info ="开发者反馈回复";
			}elseif($reason_add==10){
				$reason_info ="软件评论回复";
			}
			$pid = $_POST['pid'] ? $_POST['pid'] : 1 ;
			$count = $model->table("dev_reason")->where(array('reason_type'=>$reason_add,'status'=>1,'pid'=>$pid))->count();
			$reason_list['content'] = $_POST['content'];
			$reason_list['status'] = 1;
			$reason_list['pid'] = $pid;
			 
			$content2 = str_replace("\n",'',$_POST['content2']);
			$_POST['content2'] = strlen(trim($content2))?str_replace("\n","<br />",$_POST['content2']):'';
			$reason_list['content2'] = $_POST['content2'];
			$reason_list['pos'] = $count+1;
			$reason_list['reason_type'] = $reason_add;
			$reason_list['create_tm'] = time();
			$reason_list['update_tm'] = time();
			$result = $model -> table("dev_reason") -> add($reason_list);
			if(empty($result)){
				$this->error('添加内置原因失败');
			}else{
				$this->writelog("添加开发者{$reason_info}原因成功","dev_reason",$result,__ACTION__ ,"","add");
				$this->success('添加内置原因成功');
			}
		}
		if($reason == 8 ){
			$util = D("Sj.Util");
			$product = $util -> getProducts(array('pid'=>$_GET['pid']));
			$this->assign('product', $product);					
		}
		$this -> assign("reason",$reason);
		$this -> display();
	}

	//排序
	function reason_sequence() {
	    if(isset($_GET)){
			$table       = 'dev_reason';
			$field       = 'pos';
			$where       = "reason_type='{$_GET['reason_type']}' AND status=1";
			$extent_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['pos'];

			$where_rank = array(
				'reason_type' => $_GET['reason_type'],
				'status' => 1,
			);

			//更新排序
			$log_result = $this->logcheck(array('id'=>$extent_id),'dev_reason',array('pos'=>$target_rank),$model);
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
		    $this->writelog("{$table}编辑id为{$extent_id}的排序。{$log_result }",$table,$extent_id,__ACTION__ ,"","edit");
			exit(json_encode($param));
		}
	}

	/**
		* 内置原因编辑
	*/
	function reason_edit(){
		$model = new Model();
		if($_POST){
			$reason_add = $_POST['reason']?$_POST['reason']:1;
			if($reason_add==1){
				$reason_info ="驳回";
			}elseif($reason_add==2){
				$reason_info ="屏蔽";
			}elseif($reason_add==3){
				$reason_info ="认领软件驳回";
			}
			$reason_list['content'] = $_POST['content'];
			$content2 = str_replace("\n",'',$_POST['content2']);
			$_POST['content2'] = strlen(trim($content2))?str_replace("\n","<br />",$_POST['content2']):'';
			$reason_list['content2'] = $_POST['content2'];
			$reason_list['update_tm'] = time(); 
			$where = array(
				'id' => $_POST['id'],
				'status' => 1
			);
			$result = $model -> table("dev_reason") -> where($where) -> save($reason_list);
			if(empty($result)){
				$this->error('编辑内置原因失败');
			}else{
				$this->writelog("编辑开发者{$reason_info}原因成功","dev_reason",$_POST['id'],__ACTION__ ,"","edit");
				$this->assign("jumpUrl", "/index.php/" . GROUP_NAME . "/Reason/reason_list/reason_type/{$reason_add}/");
				//刷新父窗口
				echo "<script>self.parent.window.location.reload();</script> ";
				$this->success('编辑内置原因成功');
			}
		}else{
			$reason = $_GET['reason'] ? $_GET['reason'] : 1;
			if(isset($_GET['p'])){
			$p =  $_GET['p'];
			}else{
				$p = 1;
			}
			if(isset($_GET['lr'])){
				$lr =  $_GET['lr'];
			}else{
				$lr = 10;
			}
			$id = $_GET['id'];
			$reason_info = $model -> table("dev_reason") -> where(array("status" => 1,"id" => $id))->find();
			$reason_info['content2'] = str_replace("<br />",'',$reason_info['content2']);
			$this -> assign("reason_info",$reason_info);
			if($reason == 8 ){
				$util = D("Sj.Util");
				$product = $util -> getProducts(array('pid'=>$reason_info['pid']));
				$this->assign('product', $product);					
			}			
			$this -> assign("reason",$reason);
			$this -> assign("id",$id);
			$this -> display();
		}
	}
	/**
		* 内置原因删除
	*/
	function reason_del(){
		$model = new Model();
		$reason = $_GET['reason']?$_GET['reason']:1;
		$p = $_GET['p']?$_GET['p']:1;
		$lr = $_GET['lr']?$_GET['lr']:10;
		if($reason==1){
			$reason_info ="驳回";
		}elseif($reason==2){
			$reason_info ="屏蔽";
		}elseif($reason==3){
			$reason_info ="认领软件驳回";
		}elseif($reason==4){
			$reason_info ="软件驳回原因";
		}elseif($reason==5){
			$reason_info ="下架原因";
		}elseif($reason==6){
			$reason_info ="评论屏蔽原因";
		}
		$id = $_GET['id'];
		$reason_list['status'] = 0;
		$reason_list['update_tm'] = time();
		$pos_list = $model-> table("dev_reason")-> where(array("status" =>1,"id" => $id,'reason_type'=>$reason))->field('pos,pid')-> find();
		$where['pos'] = array('gt',$pos_list['pos']);
		$where['status'] = 1;
		$where['reason_type'] = $reason;
		$where['pid'] = $pos_list['pid'];
		$list = $model->table("dev_reason")->where($where)-> select();
		foreach($list as $v){
			$sql = "update dev_reason set pos = pos-1 where id={$v['id']}";
			$model -> query($sql);
		}	
		$affect = $model -> table("dev_reason")-> where(array("status" =>1,"id" => $id))->save($reason_list);
		if($affect){
			$this->writelog("删除了开发者{$reason_info}原因成功","dev_reason",$id,__ACTION__ ,"","del");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Reason/reason_list/reason_type/{$reason}/p/{$p}/lr/{$lr}");
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
		
	}
	//版本迭代记录列表
	function version_record_list(){
		$model = M('version_record');
		$where = "status=1";
		if(isset($_GET['content'])){
			$this->assign("content", $_GET['content']);
			$where .= " and content like '%{$_GET['content']}%' or title like '%{$_GET['content']}%'";
		}
		//起止日期
		if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
			$begintime = strtotime($_GET['begintime']);
			$endtime = strtotime($_GET['endtime']);
			$this->assign('begintime',$_GET['begintime']);
			$this->assign('endtime',$_GET['endtime']);
			if($endtime<$begintime){   
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error("时间无效,请选择正常时间");
			}
			$where .= " and (add_tm >={$begintime} and add_tm <= {$endtime})";
		}
		//分页		
		import('@.ORG.Page2');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;	
		$total = $model->where($where)->count();
		$Page = new Page($total,$limit);		
		$list = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('pos')->select();
		foreach($list as $key=>$val){
			$list[$key]['content'] = str_replace('IMG_HOST',IMGATT_HOST,$val['content']);
			//排序
			$tmp = "<select rel='{$val['id']}' name='rank' class='extent_rank'>";
			for($i=1;$i<=$total;$i++) {
				$select = $i==$val['pos'] ? ' selected' : '';
				$tmp .= "<option value='{$i}'{$select}>{$i}</option>";
			}
			$tmp .= "</select>";
			$list[$key]['pos'] = $tmp;			
		}
		$this->assign('list',$list);
		$this->assign('total',$total);
		$this -> assign('page', $Page->show());		
		$this->display();
	}
	//版本迭代记录列表__编辑__添加列表
	function version_add_edit(){
		$version_record_db = M('version_record');
		$editor_id = intval($_GET['editor_id']);
		$this -> assign('editor_id',$editor_id);
		if($editor_id){
			$editor_list = $version_record_db->where("status=1 and id={$editor_id}")->find();
			$editor_list['content'] = str_replace('IMG_HOST',IMGATT_HOST,$editor_list['content']);
			$this -> assign('editor_list',$editor_list);
		}
		$this->display();
	}
	//版本迭代记录列表__编辑__添加提交
	function version_add_edit_do(){
		if(empty($_POST['editor_preurl'])) $_POST['editor_preurl'] = '/index.php/Dev/Reason/version_add_edit';
		$editor_title = trim($_POST['editor_title']);
		if($editor_title == "40个字以内" || empty($editor_title)) {
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error('请填写标题！');
			exit( "<script language='javascript'> alert('请填写标题！'); window.history.back(-1);</script>");
		}	
		if(mb_strlen($editor_title) >40){
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error('填写标题已经超过40个字！');
			exit( "<script language='javascript'> alert('填写标题已经超过40个字！'); window.history.back(-1);</script>");
		}	
		if(!$_POST['project_leader']) {
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error('请填写负责人！');
			exit( "<script language='javascript'> alert('请填写负责人！'); window.history.back(-1);</script>");
		}
		// $editor_content = $_POST['editor_content'];
		// if(!isset($editor_content) ||  $editor_content == '') {
			// // $this->assign('jumpUrl',$_POST['editor_preurl']);
			// // $this->error('请填写内容！');
			// exit( "<script language='javascript'> alert('请填写内容！'); window.history.back(-1);</script>");
		// }
		//文章中图片处理,开始
		preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$_POST['editor_content'],$matches);
		if($matches[1]) {	//有需要上传的新图片
			$pre_path = $_SERVER['DOCUMENT_ROOT'];		//web根目录
			//图片宽度不超过600px检查
			foreach($matches[1] as $key => $val) {
				unset($width,$height,$type,$attr);
				list($width,$height,$type,$attr) = getimagesize($pre_path.$val);
				if($width>600) {
					exit( "<script language='javascript'> alert('有图片宽度超过限定的600px，请检查！'); window.history.back(-1);</script>");
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
			$arr = ReasonAction::dev_upload($files);
			if($arr['info']['http_code']!=200) {
				exit( "<script language='javascript'> alert('和图片服务器通讯失败，请重试！【{$arr['errno']}:{$arr['error']}】'); window.history.back(-1);</script>");
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
		$editor_id = intval($_POST['editor_id']);
		$do = '';
		$data = array();
		$data['project_leader'] = $_POST['project_leader'];
		$data['title'] = $_POST['editor_title'];
		$data['content'] = $_POST['editor_content'];		
		$data['update_tm'] = time();			
		if($editor_id) {
			$list = $model->table('sj_version_record')->where("id='{$editor_id}' and status=1")->find();
			if(!$list){
				// $this->assign('jumpUrl',$_POST['editor_preurl']);
				// $this->error('该条记录已经失效！');
				exit( "<script language='javascript'> alert('该条记录已经失效！'); window.history.back(-1);</script>");
			}
			$log_result = $this->logcheck(array('id'=>$editor_id),'sj_version_record',$data,$model);
			$res = $model->table('sj_version_record')->where("id='{$editor_id}'")->save($data);
			$do = '编辑';
			$log = "编辑了id为{$editor_id}的迭代记录！{$log_result}";
		} else {
			$data['status'] = 1;
			$data['add_tm'] = time();
			$count = $model->table('sj_version_record')-> where("status=1")->count(); 
			$data['pos'] = $count + 1;
			$res = $model->table('sj_version_record')->add($data);
			$do = '添加';
			$log = "添加了id为{$res}的迭代记录！";
		}
		if($res) {
			$str=$editor_id?'edit':'add';
			$editor_id=$editor_id?$editor_id:$res;
			$this->writelog($log,"sj_version_record",$editor_id,__ACTION__ ,"",$str);
			$this->assign('jumpUrl',"/index.php/Dev/Reason/version_record_list");
			$this->success("{$do}文章成功！");
		} else {
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error("数据{$do}失败，请重试！");
			exit( "<script language='javascript'> alert('数据{$do}失败，请重试！'); window.history.back(-1);</script>");
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
	//版本迭代记录删除
	function version_record_del(){
		$model = new Model();
		$id = $_GET['id'];
		$list = $model->table('sj_version_record')->where("id='{$id}' and status=1")->find();
		if($list){
			$res = $model->table('sj_version_record')->where("id='{$id}'")->save(array('status'=>0));
			if($res){
				//添加成功后排序
				$table       = 'sj_version_record';
				$field       = 'pos';
				$where       = "status=1";
				$extent_id   = $res;
				$target_rank = 1;

				$where_rank = array(
					'status' => 1,
				);

				//更新排序
				$log_result = $this->logcheck(array('id'=>$extent_id),'sj_version_record',array('pos'=>$target_rank),$model);
				$param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
				$this->writelog("{$table}编辑id为{$extent_id}的排序。{$log_result}","sj_version_record",$extent_id,__ACTION__ ,"","edit");
				$this->writelog("删除了id为{$id}的迭代记录","sj_version_record",$id,__ACTION__ ,"","del");
				$this->assign('jumpUrl',"/index.php/Dev/Reason/version_record_list");
				$this->success("删除成功！");
			}
		}else{
			$this->error("该记录已经失效，请不要重复删除！");
		}
	}
	//排序
	public function pub_version_sequence() {
	    if(isset($_GET)){
			$table       = 'sj_version_record';
			$field       = 'pos';
			$where       = "status=1";
			$extent_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['pos'];

			$where_rank = array(
				'status' => 1,
			);

			//更新排序
			$log_result = $this->logcheck(array('id'=>$extent_id),'sj_version_record',array('pos'=>$target_rank),$model);
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
		    $this->writelog("{$table}编辑id为{$extent_id}的排序。{$log_result }","sj_version_record",$extent_id,__ACTION__ ,"","edit");
			exit(json_encode($param));
		}
	}	
}
?>