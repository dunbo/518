<?php
class HelpManagementAction extends CommonAction {
	//已发布列表
	public function release_list(){
		$help_management_db = M("help_management");
		$where = "status=1";
		if(isset($_GET['title'])){
			$this->assign("title", $_GET['title']);
			$where .= " and title like '%{$_GET['title']}%' ";
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
		if(isset($_GET['title']) || (isset($_GET['begintime']) && isset($_GET['begintime']))){				
			$total = $help_management_db->where($where)->count();
		}else{
			$total = $help_management_db->where("status=1 and pid=0")->count();
		}
		//分页		
		import('@.ORG.Page2');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;		
		$Page = new Page($total,$limit);	
		if(isset($_GET['title']) || (isset($_GET['begintime']) && isset($_GET['begintime']))){	
			$help_management_list = $help_management_db->where($where)->order('one_pos asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$one_dir = $help_management_db->where("status=1 and pid=0")->limit($Page->firstRow.','.$Page->listRows)->select();
		}else{
			$help_management_list = $help_management_db->where("status=1 and pid=0")->order('one_pos asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		}
		//echo  $help_management_db->getlastsql();
		$list = array();
		$lists = array();
		foreach($one_dir as $key=>$val){
			$lists[$val['id']] = $val;
		}
		foreach($help_management_list as $k => $v){
			if($v['pid'] ==0){
				$list[$k]['id'] = $v['id'];
				//一级排序
				$tmp = "<select rel='{$v['id']}' name='rank' class='extent_rank'>";
				for($i=1;$i<=$total;$i++) {
					$select = $i==$v['one_pos'] ? ' selected' : '';
					$tmp .= "<option value='{$i}'{$select}>{$i}</option>";
				}
				$tmp .= "</select>";
				$list[$k]['one_pos'] = $tmp;	
				
				$list[$k]['directory'] = $v['directory'];
				$list[$k]['title'] = $v['title'];
				$list[$k]['add_tm'] = $v['add_tm'];
				$list[$k]['two_dir'] = $this->pub_getpid($v['id']);	
			}
			if(isset($_GET['title']) || (isset($_GET['begintime']) && isset($_GET['begintime']))){	
				$list[$k]['id'] = $v['id'];
				$list[$k]['pid'] = $v['pid'];
				//一级排序
				$list[$k]['one_pos'] = $v['one_pos'];					
				$list[$k]['two_pos'] = $v['two_pos'];
				$list[$k]['directory'] = $v['directory'];
				$list[$k]['title'] = $v['title'];
				$list[$k]['add_tm'] = $v['add_tm'];
			}
		}
		$this -> assign('page', $Page->show());			
		$this -> assign('list',$list);
		$this -> assign('total',$total);
		$this -> assign('lists',$lists);
		$this->display();
	}	
	public function pub_getpid($pid){
		$help_management_db = M("help_management");
		$count = $help_management_db->where("status=1 and pid={$pid}")->count();
		$p_list = $help_management_db->where("status=1 and pid={$pid}")->order('two_pos asc')->select();
		foreach($p_list as $k =>$v){
			//二级排序
			$tmp = "<select rel='{$v['id']}' pid='{$pid}' name='rank' class='extent_rank2'>";
			for($i=1;$i<=$count;$i++) {
				$select = $i==$v['two_pos'] ? ' selected' : '';
				$tmp .= "<option value='{$i}'{$select}>{$i}</option>";
			}
			$tmp .= "</select>";
			$p_list[$k]['two_pos'] = $tmp;	
		}
		//echo $help_management_db->getlastsql();
		return $p_list;
	}	
	public function help_content_edit(){
		$help_management_db = M("help_management");
		$one_dir = $help_management_db->where("status=1 and pid=0")->field('id,directory')->select();
		$this -> assign('one_dir',$one_dir);
		$editor_id = intval($_GET['editor_id']);
		$this -> assign('editor_id',$editor_id);
		if($editor_id){
			$editor_list = $help_management_db->where("status=1 and id={$editor_id}")->find();
			$editor_list['content'] = str_replace('IMG_HOST',IMGATT_HOST,$editor_list['content']);
			$this -> assign('editor_list',$editor_list);
		}
		$this->display();
	}
	//编辑和添加文章__提交
	function help_content_add(){
		if(empty($_POST['editor_preurl'])) $_POST['editor_preurl'] = '/index.php/Dev/HelpManagement/help_content_edit';
		$editor_title = trim($_POST['editor_title']);
		if($editor_title == "40个字以内" || empty($editor_title)) {
			//$this->assign('jumpUrl',$_POST['editor_preurl']);
			//$this->error('请填写标题！');
			exit( "<script language='javascript'> alert('请填写标题！'); window.history.back(-1);</script>");
		}	
		if(mb_strlen($editor_title) >40){
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error('填写标题已经超过40个字！');
			exit( "<script language='javascript'> alert('填写标题已经超过40个字！');  window.history.back(-1);</script>");
		}	
		if(!$_POST['dir_name']) {
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error('请填写目录名称！');
			exit( "<script language='javascript'> alert('请填写目录名称！');  window.history.back(-1);</script>");
		}
		// $editor_content = trim($_POST['editor_content']) ;
		// if(!$editor_content) {
			// // $this->assign('jumpUrl',$_POST['editor_preurl']);
			// // $this->error('请填写文章内容！');
			// exit( "<script language='javascript'> alert('请填写文章内容！');  window.history.back(-1);</script>");			
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
					// $this->assign('jumpUrl',$_POST['editor_preurl']);
					// $this->error('有图片宽度超过限定的600px，请检查！');
					exit( "<script language='javascript'> alert('有图片宽度超过限定的600px，请检查！');  window.history.back(-1);</script>");	
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
			$arr = HelpManagementAction::dev_upload($files);
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
		$editor_id = intval($_POST['editor_id']);
		$do = '';
		$data = array();
		$data['directory'] = $_POST['dir_name'];
		$data['title'] = $_POST['editor_title'];
		if($_POST['content_type']==1){
			if (!preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',trim($_POST['editor_content2']))) {
			    exit( "<script language='javascript'> alert('链接地址格式错误！');  window.history.back(-1);</script>");
			} 
			$data['content'] = trim($_POST['editor_content2']);	
		}else{
			$data['content'] = $_POST['editor_content'];	
		}
		$data['content_type'] = $_POST['content_type'];	
		$data['update_tm'] = time();
		if($_POST['dir'] == 2 && !$_POST['one_dir']){
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error('请选择一级目录！');
			exit( "<script language='javascript'> alert('请选择一级目录！');  window.history.back(-1);</script>");	
		}
		$list = $model->table('sj_help_management')->where("id='{$editor_id}'")->find();		
		//var_dump($_POST['dir']);exit;		
		if($_POST['dir'] == 1 && $list['pid'] !=0){
			//统计有效数据
			$count = $model->table("sj_help_management")->where('status=1 and pid=0')->count();			
			$data['pid'] = 0;
			$data['one_pos'] = $count+1;
		}else if($_POST['dir'] == 2 && $list['pid'] ==0){
			$data['pid'] = $_POST['one_dir'];
			$two_count = $model->table("sj_help_management")->where("status=1 and pid={$_POST['one_dir']}")->count();	
			$data['two_pos'] = $two_count+1;	
		}else if($_POST['dir'] == 1 && empty($editor_id)){
			$count = $model->table("sj_help_management")->where('status=1 and pid=0')->count();			
			$data['pid'] = 0;
			$data['one_pos'] = $count+1;
		}else if($_POST['dir'] == 2 && ($_POST['one_dir'] != $list['pid'] )){
			$data['pid'] = $_POST['one_dir'];
			$two_count = $model->table("sj_help_management")->where("status=1 and pid={$_POST['one_dir']}")->count();	
			$data['two_pos'] = $two_count+1;
		}		
		if($editor_id) {
			if(!$list){
				$this->assign('jumpUrl',$_POST['editor_preurl']);
				$this->error('该条记录已经失效！');
			}
			//排序开始
			if($_POST['dir'] == 1 && $list['pid'] !=0){
				$two_pos = $model->table('sj_help_management')->where("pid='{$list['pid']}' and two_pos > {$list['two_pos']}")->field('two_pos,id')->select();
				foreach($two_pos as $v){					
					$model->query("UPDATE sj_help_management SET two_pos = two_pos-1 WHERE id={$v['id']}");
				}
			}elseif($_POST['dir'] == 2 && $list['pid']==0){
				$one_pos = $model->table('sj_help_management')->where("pid='0' and one_pos > {$list['one_pos']}")->field('one_pos,id')->select();
				foreach($one_pos as $v){					
					$model->query("UPDATE sj_help_management SET one_pos = one_pos-1 WHERE id={$v['id']}");
				}
			}else if($_POST['dir'] == 2 && ($_POST['one_dir'] != $list['pid'] )){
				$two_pos = $model->table('sj_help_management')->where("pid='{$list['pid']}' and two_pos > {$list['two_pos']}")->field('two_pos,id')->select();
				foreach($two_pos as $v){					
					$model->query("UPDATE sj_help_management SET two_pos = two_pos-1 WHERE id={$v['id']}");
				}
			}
			$res = $model->table('sj_help_management')->where("id='{$editor_id}'")->save($data);
			$do = '编辑';
			$log = "编辑了id为{$editor_id}的帮助文章！";
		} else {
			$data['status'] = 1;
			$data['add_tm'] = time();
			$res = $model->table('sj_help_management')->add($data);
			$do = '添加';
			$log = "添加了id为{$res}的帮助文章！";
		}
		if($res) {
			$do_type=$editor_id?'edit':'add';
			$this->writelog($log,"sj_help_management",$editor_id?$editor_id:$res,__ACTION__ ,"",$do_type);
			$this->assign('jumpUrl',"/index.php/Dev/HelpManagement/release_list");
			$this->success("{$do}文章成功！");
		} else {
			// $this->assign('jumpUrl',$_POST['editor_preurl']);
			// $this->error("数据{$do}失败，请重试！");
			exit( "<script language='javascript'> alert('数据{$do}失败，请重试！');  window.history.back(-1);</script>");	
		}
	}
	//上传图片
	public static function pub_rand_code($num) {
		$str = '';
		for($i=0;$i<$num;$i++) {
			$str .= mt_rand(0,9);
		}

		return $str;
	}
	//动态信息添加_图片处理,代码来源:/dev.goapk.com/common.php
	public function dev_upload($files) {
		$vals = array(
			'do' => 'save',
			'static_data' => '/data/att/m.goapk.com',
		);
		$upload_model = D("Dev.Uploadfile");
		return $upload_model -> _http_post(array_merge($vals,$files));
	}
		
	//排序
	public function pub_help_sequence() {
	    if(isset($_GET)){
			$table       = 'sj_help_management';
			$pid = $_GET['pid'];
			if(!$pid){
				$field       = 'one_pos';
				$target_rank = (int)$_GET['one_pos'];
				$where_rank = array(
					'status' => 1,
					'pid'=>0
				);
			}else{
				$field       = 'two_pos';
				$target_rank = (int)$_GET['two_pos'];
				$where_rank = array(
					'status' => 1,
					'pid'=>$pid
				);
			}
			$extent_id   = (int)$_GET['id'];
			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
			exit(json_encode($param));
		}
	}
	//删除
	public function help_content_del(){
		$model = new Model();
		$id = $_GET['id'];
		$pid = $_GET['pid'];
		//排序
		$list = $model->table('sj_help_management')->where("id='{$id}'")->find();
		if(!$list){
			$this->assign('jumpUrl',"/index.php/Dev/HelpManagement/release_list");
			$this->error('该条记录已经失效，请不要重复删除！');
		}		
		if($pid){
			$two_pos = $model->table('sj_help_management')->where("pid='{$pid}' and two_pos > {$list['two_pos']}")->field('two_pos,id')->select();
			foreach($two_pos as $v){					
				$model->query("UPDATE sj_help_management SET two_pos = two_pos-1 WHERE id={$v['id']}");
			}			
		}else{
			$one_pos = $model->table('sj_help_management')->where("pid='0' and one_pos > {$list['one_pos']}")->field('one_pos,id')->select();
			foreach($one_pos as $v){					
				$model->query("UPDATE sj_help_management SET one_pos = one_pos-1 WHERE id={$v['id']}");
			}			
		}
		$res = $model->table('sj_help_management')->where("id='{$id}'")->save(array('status' =>0,'update_tm'=>time()));
		if($res){
			$this->writelog("删除了ID为{$id}的帮助文章","sj_help_management",$id,__ACTION__ ,"","del");
			$this->assign('jumpUrl',"/index.php/Dev/HelpManagement/release_list");
			$this->success("删除成功");
		}
	}	
}
?>