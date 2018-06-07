<?php
class AssistantAction extends CommonAction{
	function search_engines(){
	  $ase_db = M("assistant_search_engines");
	  $count = $ase_db -> where("status = 1") -> count();
	  import("@.ORG.Page");
	  $page = new Page ($count, 25);
	  $ase_list = $ase_db -> where("status = 1") ->order("update_tm desc") -> limit($page -> firstRow.','.$page -> listRows) -> select();
	  
	  $this -> assign("page",$page -> show());
	  $this -> assign("ase_list", $ase_list);
	  $this -> display("search_engines");
	}
	function search_engines_add_do(){
	   $data['name'] = trim($_POST['name']);
	   $data['url'] = trim($_POST['url']);
	   $ms = preg_match("/^(http:\/\/)/i",$data['url'], $matches);
	   if(!$ms){
		$this -> error("请以http://开头");
	   }
	   $time = time();
	   $data['submit_tm'] = $time;
	   $data['update_tm'] = $time;
	   $data['status'] = 1;
	   $zh_data['name']=$data['name'];
	   $zh_data['status']=1;
	   $ase_db = M("assistant_search_engines");
	   $count = $ase_db -> where($zh_data) -> count();
		if($count > 0){
		 $this -> error("该搜索名已经存在！！");
		}
	   $count = $ase_db -> where($zh_data) -> count();
	   if($count > 0) $this -> error("搜索名已存在！！");
	   $affect = $ase_db -> add($data);
	   if($affect){
	    $this -> writelog("搜索关键字管理_安智助手站点管理_安智助手搜索engine 添加 id :".$affect,'sj_assistant_search_engines',$affect,__ACTION__ ,"","add");
        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/search_engines');	   
		$this -> success("安智助手搜索engine添加成功!!");
	   }else{
	    $this -> error("添加失败！！");
	   }
	}
	function search_engines_update(){
		$ase_db = M("assistant_search_engines");
		$id_arr = explode('&',$_GET['seid']);
		$seid = $id_arr[0];
		$where = "seid = ".$seid;
		$info = $ase_db -> where($where) -> select();
		$this -> assign("info",$info[0]);
		$this -> display("search_engines_update");
	}
	function search_engines_update_do(){
		$ase_db = M("assistant_search_engines");
		$data['name'] = trim($_POST['sname']);
		$data['url'] = trim($_POST['url']);
	   $ms = preg_match("/^(http:\/\/)/i",$data['url'], $matches);
	   if(!$ms){
		$this -> error("请以http://开头");
	   }	
	   $zh_where['_string']="seid <> ".$_POST['seid'];
	   $zh_where['name']=$data['name'];
	   $zh_where['status']=1;
	    $count = $ase_db -> where($zh_where) -> count();
		if($count > 0){
		 $this -> error("该搜索名已经存在！！");
		}
		$where = "seid = ".escape_string($_POST['seid']);
		$data['update_tm'] = time();
		$log = $this->logcheck(array('seid'=>$_POST['seid']),'sj_assistant_search_engines',$data,$ase_db);
		$affect = $ase_db -> where($where) -> save($data);
		if($affect){
			//$this -> writelog("安智助手搜索engine 修改 id :".$_POST['seid']);
			$this-> writelog("手机助手管理_助手搜索管理_安智助手搜索engine 修改 id :".$_POST['seid'].$log,'sj_assistant_search_engines',$_POST['seid'],__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/search_engines');	   
			$this -> success("安智助手搜索engine修改成功!!");			
		}else{
			$this -> error("修改失败");
		}
	}
	function search_engines_delete(){
		$seid = escape_string($_GET['seid']);
		$where = "seid = ".$seid;
		$ase_db = M("assistant_search_engines");
		$data['update_tm'] = time();
		$data['status'] = 0;
		
		$affect = $ase_db -> where($where) -> save($data);
		if($affect){
		$update['update_tm'] = time();
		$affect = $ase_db -> where("status = 1") -> save($update);
			$this -> writelog("手机助手管理_助手搜索管理_安智助手搜索engine 删除 id :".$seid,'sj_assistant_search_engines',$seid,__ACTION__ ,"","del");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/search_engines');	   
			$this -> success("安智助手搜索engine删除成功!!");			
		}else{
		  $this -> error("删除失败");
		}
	}
	function ass_site(){
		$asite_db = M("assistant_sites");
		import("@.ORG.Page");
		$count = $asite_db -> where("status = 1") -> count();
		$page = new Page($count,25);
		$asite_list = $asite_db -> where("status =1 ") -> order("update_tm desc") ->limit($page -> firstRow.','.$page -> listRows) -> select();
		$this -> assign("page",$page -> show);
		$this -> assign("asite_list",$asite_list);
		$this -> display("ass_site");
	}
	function ass_site_add_do(){
		$asite_db = M("assistant_sites");
		$data['name'] = $_POST['site_name'];
		$data['status'] = 1;
		$count = $asite_db -> where($data) -> count();
		if($count > 0){
			$this -> error("该站点已经存在!!");
		}
		$data['url'] = $_POST['url'];
/* 		import("@.ORG.UploadFile");
		$file_arr = explode('.',$_FILES['iconurl']['name']);
		$ext = $file_arr[count($file_arr)-1];
		$_FILES['iconurl']['name'] = md5($_FILES['iconurl']['name'].time()).".".$ext;
		
		$upload = new UploadFile(); // 实例化上传类
		$upload->maxSize  = 3145728 ; // 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$date = date("Ym/d/",time());
		$upload->savePath =  UPLOAD_PATH .'/icon/'.$date; // 设置附件上传目录
		if(!$upload->upload()){ // 上传错误提示错误信息
		$this->error($upload->getErrorMsg());
		}else{ // 上传成功获取上传文件信息
		$info =  $upload->getUploadFileInfo();
		} */
		$path = date('Ym/d/', time());
		$config = array(
			'multi_config' => array(
				'iconurl' => array(
					'savepath' => UPLOAD_PATH. '/icon/'. $path,
					'saveRule' => 'time'
				),
			),
			'img_p_size' =>  1024*5,
			'img_p_width' =>  48,
			'img_p_height' => 48,
		);
		$upload=$this->_uploadapk(0, $config);	
		$data['iconurl'] = $upload['image'][0]['url'];
		//$data['iconurl'] = '/icon/'.$date.$info[0]['savename']; //旧的方式
		$data['update_tm'] = time();
		$data['submit_tm'] = $data['update_tm'];
		$affect = $asite_db -> add($data);
		if($affect){
		$this -> writelog("搜索关键字管理_安智助手站点管理_添加该站点 id :".$affect,'sj_assistant_sites',$affect,__ACTION__ ,"","add");
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/ass_site');	   
		$this -> success("安智助手站点添加成功!!");				
		}
	}
	function ass_site_update(){
		$asite_db = M("assistant_sites");
		$id_arr = explode('&',$_GET['site_id']);
		$site_id = $id_arr[0];
		$info = $asite_db -> where("site_id = ".$site_id) -> select();
		$asite_info = $info[0];
		$this -> assign("asite_info",$asite_info);
		$this -> display("ass_site_update");
	}
	function ass_site_update_do(){
		$where = "site_id = ".escape_string($_POST['site_id']);
		$data['name'] = $_POST['site_name'];
		$data['url'] = $_POST['url'];
		if(!empty($_FILES['iconurl']['name'])){
/*			$file_arr = explode('.',$_FILES['iconurl']['name']);
			$ext = $file_arr[count($file_arr)-1];
			$_FILES['iconurl']['name'] = md5($_FILES['iconurl']['name'].time()).".".$ext;
 			import("@.ORG.UploadFile");
			$upload = new UploadFile(); // 实例化上传类
			$upload->maxSize  = 3145728 ; // 设置附件上传大小
			$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
			$date = date("Ym/d/",time());
			$upload->savePath =  UPLOAD_PATH .'/icon/'.$date; // 设置附件上传目录
			if(!$upload->upload()){ // 上传错误提示错误信息
			$this->error($upload->getErrorMsg());
			}else{ // 上传成功获取上传文件信息
			$info =  $upload->getUploadFileInfo();
			}
			$data['iconurl'] = '/icon/'.$date.$info[0]['savename']; */
			$path = date('Ym/d/', time());
			$config = array(
				'multi_config' => array(
					'iconurl' => array(
						'savepath' => UPLOAD_PATH. '/icon/'. $path,
						'saveRule' => 'time'
					),
				),
				'img_p_size' =>  1024*5,
				'img_p_width' =>  48,
				'img_p_height' => 48,
			);
			$upload=$this->_uploadapk(0, $config);	
			$data['iconurl'] = $upload['image'][0]['url'];
		}
		$data['update_tm'] = time();
		$asite_db = M("assistant_sites");
		$count = $asite_db -> where("site_id not in (".$_POST['site_id'].") and  name = '".$data['name']."' and status = 1") -> count();
		if($count > 0) $this -> error("站点名已存在！！");
		$log = $this->logcheck(array('site_id'=>$_POST['site_id']),'sj_assistant_sites',$data,$asite_db);
		$affect = $asite_db -> where($where) -> save($data);
		if($affect){
		//$this -> writelog("修改该站点 id :".$_POST['site_id']);
		$this->writelog("搜索关键字管理_安智助手站点管理_修改该站点id:'".$_POST['site_id']."'".$log,'sj_assistant_sites',$_POST['site_id'],__ACTION__ ,"","edit");
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/ass_site');	   
		$this -> success("安智助手站点修改成功!!");					
		}else{
		
		}
	}
	function ass_site_delete(){
		$site_id = $_GET['site_id'];
		$asite_db = M("assistant_sites");
		$data['status'] = 0;
		$data['update_tm'] = time();
		$affect = $asite_db -> where(array("site_id"=>$site_id)) -> save($data);
		if($affect){
			$update['update_tm'] = time();
			$affect = $asite_db -> where("status = 1") -> save($update);
			$this -> writelog("搜索关键字管理_安智助手站点管理_删除该站点 id :".$_GET['site_id'],'sj_assistant_sites',$_GET['site_id'],__ACTION__ ,"","del");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/ass_site');	   
			$this -> success("安智助手站点删除成功!!");					
		}else{
			$this -> error("删除失败");
		}
		
	}
	function assistant(){
		$ass_db = M("assistant");
		$count = $ass_db -> where("status = 1") -> count();
		import("@.ORG.Page");
		$page = new Page($count,25);
		$ass_list = $ass_db -> where("status = 1") -> order("update_tm desc") -> limit($page -> firstRow .','.$page -> listRows ) -> select();
		$this -> assign("page",$page -> show());
		$this -> assign("ass_list",$ass_list);
		$this -> display("assistant");
	}
	function assistant_upload(){
		$ass_db = M("assistant");
		$data['name'] = $_POST['as_name'];
		$data['status']=1;
		$count = $ass_db -> where($data) -> count();
		if($count > 0){
			$this -> error("该名称已存在！！");
		}
		$data['enforce'] = $_POST['enforce'];
		$data['submit_tm'] = time();
		$data['update_tm'] = $data['submit_tm'];
		$data['status'] = 1;
		if(mb_strlen($data['describe'],"uft8")>100){
			$this -> error("描述过长 不能超过100个字");
		}
		$data['describe'] = $_POST['describe'];
		import("@.ORG.UploadFile");
		$file_arr = explode('.',$_FILES['apk']['name']);
		$ext = $file_arr[count($file_arr)-1];
		$_FILES['apk']['name'] = md5($_FILES['apk']['name'].time()).".".$ext;
		$upload = new UploadFile(); // 实例化上传类
		$upload->maxSize  = 3145728 ; // 设置附件上传大小
		$upload->allowExts  = array('apk'); // 设置附件上传类型
		$date = date("Ym/d/",time());
		$upload->savePath =  UPLOAD_PATH .'/apk/'. $date; // 设置附件上传目录
		if(!$upload->upload()){ // 上传错误提示错误信息
		$this->error($upload->getErrorMsg());
		}else{ // 上传成功获取上传文件信息
		$info =  $upload->getUploadFileInfo();
		}
		$data['apkurl'] = '/apk/'. $date.$info[0]['savename'];
		include_once SERVER_ROOT. '/tools/functions.php';
		$apk_info = get_apk_info(UPLOAD_PATH.$data['apkurl']);
		$data['apksize'] = $_FILES['apk']['size'];
		$data['version_code'] = $apk_info['versionCode'];
		$data['firmware'] = $apk_info['min_sdk_ver']; 
		$affect = $ass_db -> add($data);
		if($affect){
			$this -> writelog("搜索关键字管理_手机安卓助手_添加安智助手 id:".$affect,'sj_assistant',$affect,__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/assistant');	   
			$this -> success("安智助手添加成功!!");		
		}else{
		 $this -> error("添加失败");
		}
	}
	function assistant_update(){
		$ass_id = $_GET['ass_id'];
		$ass_db = M("assistant");	
		$info = $ass_db -> where(array("ass_id" => $ass_id,"status" =>1))-> select();
		$assinfo = $info[0];
		$this -> assign("assinfo",$assinfo);
		$this -> display("assistant_update");
	}
	function assistant_delete(){
		$ass_id = $_GET['ass_id'];
		$ass_db = M("assistant");
		$data['status'] = 0;
		$data['update_tm'] = time();
		$affect = $ass_db -> where(array("ass_id" => $ass_id)) -> save($data);
		if($affect){
			$this -> writelog("搜索关键字管理_手机安卓助手_删除安智助手 id:".$ass_id,'sj_assistant',$ass_id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Assistant/assistant');	   
			$this -> success("安智助手删除成功!!");		
		}else{
			$this -> error("删除失败");
		}
	}
}
?>