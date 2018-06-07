<?php
class OlgamekeywordsAction extends CommonAction{
	
	//安卓游戏搜索关键字列表
	function search_key_list(){
		$sk_db = M("search_key");
		$key = escape_string($_GET['srh_key']);
		if(mb_strlen($key,'UTF-8') > 50){
		  $this -> error('您要搜索的关键字不存在！！');
		}
		$key_where = $key ? " and srh_key like '%".$key."%' and pid = 5" : ' and pid = 5';
        $where = 'status = 1'.$key_where;
		$search_key_list = $sk_db -> where("status = 1".$key_where) -> select();
		$count = count($search_key_list);
        import("@.ORG.Page");
		$p = new Page ($count, 25);
		$search_key_list = $sk_db -> where("status = 1".$key_where)->limit($p->firstRow.','.$p->listRows) ->order("update_tm desc") -> select();

        $page = $p->show();
		$this -> assign("page",$page);
		$this -> assign("key",$key);
		$this -> assign("stop_tm",date("m/d/Y",time()+24*3600*7));
		$this -> assign("start_tm",date("m/d/Y",time()));
		$this -> assign('key_list',$search_key_list);
		$this -> display('search_key_list');
	}


	//安卓游戏搜索关键字列表_添加关键字
	function search_key_add(){
		$sk_db = M("search_key");
		$srh_key = trim($_POST['srh_key']);
		$data['srh_key'] = $srh_key;
		$data['pid'] = 5;
		if(preg_match("/\s/", $srh_key)){
			$this -> error('关键字中不可含有空格');
		}
		if(trim($srh_key) == ''){
			$this -> error('关键字不能为空');
		}
		if($this -> strlen_az($data['srh_key']) >8){
			$this -> error('添加的关键字不要超过4个字');
		}
		$data['create_tm'] = time();
		$data['update_tm'] = $data['create_tm'];
		$start_tm = $this -> format_date($_POST['start_tm']);
		$stop_tm  = $this -> format_date($_POST['stop_tm']);
		if($start_tm > $stop_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$data['start_tm'] = strtotime($start_tm);
		$data['stop_tm'] = strtotime($stop_tm.' 23:59:59');
		$data['status'] = 1;
		$result = $sk_db -> where("srh_key = '".escape_string($srh_key)."' and stop_tm > ".time()." and pid = 5 and status = 1") -> select();
		$count = count($result);
		if($count == 0){
		  $affect = $sk_db -> add($data);
		  if($affect){
		     $this->writelog("搜索结果运营：添加安卓游戏关键字{$srh_key}，id为:".$affect,"sj_search_key",$affect,__ACTION__ ,'','add');
		     $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/search_key_list');
		    $this -> success("关键字添加成功");
		  }else{
		  $this -> error("关键字添加失败");
		  }
		}else{
		  $this -> error("有效关键字已存在！！");
		}
	}
	
	//安卓游戏搜索关键字列表_添加包显示
	function search_key_package_add(){
		$sk_db = M("search_key");
		$soft_db = M("soft");
	    $sk_pkg_db = M("search_key_package");
		$id = escape_string($_GET['id']);
		$package = escape_string($_GET['package']);
		$search_key = $_GET['package'] ? " and package like '%".$package."%'" : '';
		if(strlen($id) > 12 || strlen($_GET['package']) > 100){
			$this -> error('您的操作有误');
		}
		$sk_info = $sk_db -> where("pid = 5 and id = ".$id) -> select();
		$current_tm = time();
		//$date_where = " and start_tm <= ".$current_tm." and  stop_tm >".$current_tm;
		$count = $sk_pkg_db -> where("kid =".$id." and pid = 5 and status = 1".$search_key) -> count();
		import("@.ORG.Page");
		$p = new Page($count, 25);
		$sk_pkg_list = $sk_pkg_db -> where("kid =".$id." and pid = 5 and status = 1".$search_key)->limit($p->firstRow.','.$p->listRows) ->order("pos asc,stop_tm asc") -> select();
		  $page = $p->show ();
		  $this -> assign("page",$page);
		 foreach($sk_pkg_list as $key => $info){
			$srh_key = $sk_db -> where("pid = 5 and id = ".$info['kid']) -> getField("srh_key");
			$softname = $soft_db -> where("package = '".$info['package']."'") -> getField("softname");
			$sk_pkg_list[$key]['key_name'] = $srh_key;
			$sk_pkg_list[$key]['softname'] = $softname;
		 }
		$this -> assign("page",$page);
		$this -> assign("stop_tm",date("m/d/Y",time()+24*3600*7));
		$this -> assign("start_tm",date("m/d/Y",time()));
		$this -> assign("sk_info",$sk_info[0]);
		$this -> assign("search_key",$_GET['package']);
		$this -> assign("sk_pkg_list",$sk_pkg_list);
		$this -> assign("srh_key",$sk_info[0]['srh_key']);
		$this -> display("search_key_package_add");
	}

	//安卓游戏搜索关键字列表_添加包提交
	function search_key_package_add_do(){
	    $sk_pkg_db = M("search_key_package");
		$soft_db = M("soft");
		$data['package'] = $_POST['package'];
		$data['kid'] = $_POST['kid'];
		$data['status'] = 1;
		$data['pid'] = 5;
		if(strlen($data['package']) >= 100 || strlen($data['kid']) >= 10){
			$this -> error('您的操作有误');
		}
		$been_result = $sk_pkg_db -> where(array('package' => $data['package'],'status' => 1,'pid' => 5)) -> select();
		if($been_result){
			$this -> error("该软件已存在");
		}
		$soft = $soft_db -> where("package = '".escape_string($_POST['package'])."' and hide = 1 and status = 1") -> select();
		if(!$soft){
			$this -> error("该软件没有正式上线！！");
		}
		$now_time = time();
		$data['update_tm'] = time();
		$data['start_tm'] = strtotime($this -> format_date($_POST['start_tm']));
		$data['stop_tm'] = strtotime($this -> format_date($_POST['stop_tm']).' 23:59:59');
		if($data['start_tm'] >= $data['stop_tm']){
			$this -> error("开始时间必须小于结束时间");
		}
		$data['weight'] = $_POST['weight'];
		//相同时间内软件排序不可重复
		$data['pos'] = $_POST['pos'];
	    $where['_string'] = 'kid = '.$data['kid'].' and pid = 5 and pos ='.$data['pos'].' and ( stop_tm >='.$data['start_tm'].' and start_tm <='.$data['stop_tm'].' and status = 1)';
		$result_time = $sk_pkg_db -> where( $where ) -> select();
		if(count($result_time) > 0){
			$this -> error("在此期间内该位置已经有软件占用！");
		}
		
		$data['create_tm'] = time();
		$affect = $sk_pkg_db -> add($data);
		if($affect){
			$this->writelog("搜索结果运营：添加关键字id为{$affect} 软件包名为".$data['package'],"sj_search_key_package",$affect,__ACTION__ ,'','add');
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/search_key_package_add/id/'.$_POST['kid']);
		    $this -> success("软件添加成功");
		}else{
			$this -> error("添加失败！！");
		}
	}
	
	//安卓游戏搜索关键字列表_删除
	function search_key_delete(){
		$id = escape_string($_GET['id']);
		$sk_db = M("search_key");
		$sk_pkg_db = M("search_key_package");
		if(strlen($id) > 12){
			$this -> error('您的操作有误');
		}
		$data['status'] = 0;
		$data['update_tm'] = time();
		$affect = $sk_db -> where("pid = 5 and id = ".$id) -> save($data);
		if($affect){
			$count = $sk_pkg_db -> where('pid = 5 and kid = '.$id) -> count();
			if($count > 0){
				$affected = $sk_pkg_db -> where("kid =".$id) -> save($data);
				if($affected){
				$this->writelog('搜索结果运营：删除关键字id为'.$id,"sj_search_key",$id,__ACTION__ ,'','del');
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/search_key_list');
				$this -> success("关键字及包删除成功");
				}else{
				$data['status'] = 1;
				$data['update_tm'] = time();
				$affect = $sk_db -> where("id =".$id) -> save($data);
				$this -> error("关键字删除失败1");
				}
			}
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/search_key_list');
			$this -> success("关键字删除成功");
		}else{
			$this -> error("关键字删除失败2");
		}
	}
	
	function search_key_update(){
		$id = escape_string($_GET['id']);
		if(strlen($id) > 12){
			$this -> error('你的操作失误 请确认你的操作');
		}
		$sk_db = M("search_key");
		$info = $sk_db -> where("pid = 5 and id =".$id)-> select();
		$this -> assign("key_info",$info[0]);
		$this -> assign("id",$id);
		$this -> display("search_key_update");
	}
	
	 function search_key_update_do(){
		$id = escape_string($_POST['id']);
		$srh_key = $_POST['srh_key'];
		if($this -> strlen_az($srh_key) > 8){
			$this -> error('您的操作有误');
		}
		$sk_db = M("search_key");
		$where['status']=1;
		$where['id']=$id;
		$where['pid'] = 5;
		$old_sk = $sk_db->where($where)->find();
        $data['srh_key'] = $srh_key;
		$data['start_tm'] = strtotime($this -> format_date($_POST['start_tm']));
		$data['stop_tm'] = strtotime($this -> format_date($_POST['stop_tm']).' 23:59:59');
		$data['update_tm'] = time();
		$count = $sk_db -> where("srh_key = '".escape_string($srh_key)."' and id <> ".$id." and status = 1 and pid = 5") -> count('id');
		if($count > 0){
		  $this -> error("关键词已存在");
		}
		$log = $this->logcheck(array('id'=>$id),'sj_search_key',$data,$sk_db);
		$affect = $sk_db -> where("pid = 5 and id = ".$id) -> save($data);
		if($data['start_tm'] >= $data['stop_tm']){
			$this -> error("开始时间必须小于结束时间");
		}
		if($affect){
			$configModel = D('Sj.Config');
        	$column_desc = $configModel->getSearchColumnDesc();
        	$msg = "编辑了id为[{$id}],关键词为[{$old_sk['srh_key']}]\n";
        	foreach ($data as $key => $val) {
        		if (isset($column_desc[$key]) && $data[$key] != $old_sk[$key]) {
        			$desc = $column_desc[$key];
					$msg .= "将{$desc} 从'{$old_sk[$key]}'修改成 '{$data[$key]}'\n";	
        		}
        	}
		    //$this->writelog($msg,"sj_search_key");
		    $this->writelog("安卓游戏搜索关键字列表_编辑了id为$id".$log,'sj_search_key',$id,__ACTION__ ,'','edit');
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/search_key_list');
		    $this -> success("安卓游戏搜索关键字修改成功");
		}else{
		    $this -> error("安卓游戏搜索关键字修改失败");
		}
	}
	
	//a安卓游戏搜索热词管理
	function kwmanage(){
		$key_model = M("olgame_keywords");
		$keyword_time = $_GET['select_time'];
		if($keyword_time == 1 || !$keyword_time){
			$where['status'] = 1;
		}elseif($keyword_time == 2){
			$where['_string'] = "status = 1 and start_tm < ".time()." and end_tm > ".time()."";
		}elseif($keyword_time == 3){	
			$where['_string'] = "status = 1 and start_tm > ".time()."";
		}elseif($keyword_time == 4){
			$where['_string'] = "status = 1 and end_tm < ".time()."";
		}
		$keylist = $key_model -> where($where) -> select();
		$this -> assign('time',$keyword_time);
		$this -> assign("keywords",$keylist);
		$this -> display("kwmanage");
	
	}

	
	//安卓游戏搜索热词快照
	function kwrecord(){
		$key_model = M("olgame_keywords");
		$key_record = $key_model -> select();
		$this -> assign("key_record",$key_record);
		$this -> display("kwrecord");
	}

	//安卓游戏搜索热词——添加热词
	function addkeywords(){
		$key_model = M("olgame_keywords");
		$date['keywords'] = escape_string($_GET["keywords"]);
		if($this -> strlen_az($date['keywords']) >8){
			$this -> error('添加的关键字不要超过4个字');
		}
		$date['upload_time'] = time();
		$date['status'] = 1;
		$date['start_tm'] = strtotime(date('Y-m-d 00:00:00',strtotime($_GET['start_tm'])));
		if(!$_GET['start_tm']){
			$this -> error("请添加开始时间");
		}
		$date['end_tm'] = strtotime(date('Y-m-d 23:59:59',strtotime($_GET['end_tm'])));
		if(!$_GET['end_tm']){
			$this -> error("请添加结束时间");
		}
		if($date['start_tm'] > $date['end_tm']){
			$this -> error("开始时间不能大于结束时间");
		}
		$been_result = $key_model -> where("start_tm <= {$date['end_tm']} and end_tm >= {$date['start_tm']} and status = 1 and keywords = {$date['keywords']}") -> select();
		if($been_result){
			$this -> error("对不起，当前输入的时间范围内有相同的搜索热词！");
		}
		
		if(empty($date['keywords'])){
			$this -> error("请填写热词！");
		}
		
		if($this -> strlen_az($date['keywords'],'utf-8') > 8){
			$this -> error("请填写小于4个字(8个字符以内)的热词！");
		}
		$common = $key_model -> where(" keywords = '".$date['keywords']."' and status = 1") -> select();
		
		//echo $date['upload_time'];
		if(count($common) > 0){
			$this -> error("对不起，热词已存在！");
		}
		
		$affect = $key_model -> add($date); 
		
		if($affect){
		$this->writelog('安卓游戏搜索关键词管理_搜索热词管理_添加热词'.$date['keywords'],"sj_olgame_soft_keywords",$affect,__ACTION__ ,'','add');
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/kwmanage');
		$this -> success("热词添加成功");
		
		}
	
	}

	function format_date($strtime){
		$date = explode("/",$strtime);
		$time = $date[2].'-'.$date[0].'-'.$date[1];
		return $time;
	}

	function search_key_package_delete(){
		 $id = escape_string($_GET['id']);
		 $sk_pkg_db = M("search_key_package");
		 if(strlen($id) > 10){
			$this -> error('您的操作有误');
		 }
		 $data['status'] = 0;
		 $data['update_tm'] = time();
		 $result=$sk_pkg_db -> where(array("id"=>$id,"status"=>1,'pid' => 5)) -> find();
		 $affect = $sk_pkg_db -> where("pid =5 and id = ".$id) -> save($data);
		
		 if($affect){
				$this->writelog('搜索结果运营：删除关键字软件 id为'.$id.'包名为'.$result['package'],"sj_search_key_package",$id,__ACTION__ ,'','del');
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/search_key_package_add/id/'.$_GET['kid']);
				$this -> success("软件已经删除");
		 }else{
		   $this -> error("软件删除失败");
		 }
	}
	
	
	function search_key_package_update(){
	  $kid = escape_string($_GET['kid']);
	  $id_str = $_GET['id'];
	  $arr = explode("&",$id_str);
	  $id = escape_string($arr[0]);
	  $sk_pkg_db = M("search_key_package");
	  $sk_db = M("search_key");
	  $srh_key = $sk_db -> where("id =".$kid) -> getField("srh_key");
	  $info = $sk_pkg_db -> where("kid =".$kid." and id=".$id) -> select();
	  $this -> assign("srh_key",$srh_key);
	  $this -> assign("pkginfo",$info[0]);
	  $this -> display("search_key_package_update");
	}
	
	
	function search_key_package_update_do(){
		  $sk_pkg_db = M("search_key_package");
		  $sk_db = M("search_key");
		  $kid = escape_string($_POST['kid']);
		  $id = escape_string($_POST['id']);
		  $zh_where['status']=1;
		  $zh_where['id']=$id;
		  $old_skp = $sk_pkg_db->where($zh_where)->find();
		  $data['start_tm'] = strtotime($this -> format_date($_POST['start_tm']));
		  $data['stop_tm'] = strtotime($this -> format_date($_POST['stop_tm']).' 23:59:59');
		  $data['package'] =  $_POST['package'];
		  $data['weight'] = $_POST['weight'];
		  $data['pos'] = $_POST['pos'];
		  $data['update_tm'] = time();
		  if($data['start_tm'] >= $data['stop_tm']){
			$this -> error("开始时间必须小于结束时间");
		  }
		  if($data['pos'] <= "0"){
			$this -> error("排序必须大于0");
		  }
		  $soft_db = M("soft");
		  $soft = $soft_db -> where("package = '".$_POST['package']."' and hide = 1 and status = 1") -> select();
		  if(!$soft){
			$this -> error("该软件没有正式上线！！");
		  }
		  $have = $sk_pkg_db -> where("package = {$_POST['package']} and id != {$id} and status = 1 and pid = 5") -> select();
		  if($have){
			$this -> error("该软件已存在");
		  }
		  //相同时间内软件排序不可重复
		  $where['_string'] = 'id <>'.$id.' and pid = 5 and kid = '.$kid.' and pos ='.$data['pos']. ' and start_tm<='.$data['stop_tm'].' and stop_tm >='.$data['start_tm'].' and status = 1';
		  $result_time = $sk_pkg_db -> where( $where ) -> select();

			if(count($result_time) > 0){
				$this -> error("在此期间内该位置已经有软件占用！");
			}
		  $affect = $sk_pkg_db -> where("kid =".$kid." and pid = 5 and id=".$id) -> save($data);
		  if($affect){
			$configModel = D('Sj.Config');
			$column_desc = $configModel->getSearchPackageColumnDesc();
			$msg = "搜索结果运营：编辑了id为[{$id}],包名为[{$old_skp['package']}]\n";
			foreach ($data as $key => $val) {
				if (isset($column_desc[$key]) && $data[$key] != $old_skp[$key]) {
					$desc = $column_desc[$key];
					$msg .= "将{$desc} 从'{$old_skp[$key]}'修改成 '{$data[$key]}'\n";	
				}
			}
			$this->writelog($msg,"sj_search_key_package",$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/search_key_package_add/id/'.$kid);
			$this -> success("软件已经修改");
		  }else{
			$this -> error("软件修改失败");
		  }

	}
	
	//安卓游戏搜索热词——删除热词
	function deletekeywords(){

		$keywords_id = escape_string($_GET["id"]);
		$keywords = escape_string($_GET["keywords"]);
		$key_model = M("olgame_keywords");
		$nowtime = time();
		$all_id = $key_model -> field("id") -> where("status = 1") -> order("id desc") -> select();

		$upload_time = $key_model -> field("upload_time") -> where("id = ".$keywords_id) -> select();
		
		$affect = $key_model -> where(array('id' => $keywords_id)) -> save(array('status' => 0));
		
		if($affect){
			
			$this->writelog('安卓游戏搜索关键词管理_搜索热词管理_删除热词id:'.$keywords_id.'名为:'.$keywords,"sj_olgame_keywords",$keywords_id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/kwmanage');
			$this -> success("热词已删除");
		}
		
	}


	//V4.0 搜索热词管理_默认搜索关键字列表
	function listdefaultkeywords(){
		$hotwords_model = M("olgame_defaultkeywords");
		//$map['start_time'] = array('egt',strtotime(date("Y-m-d",time())));
		//显示热词时间分类
		$time = $_GET['select_time'];
		if($time == 1 || $time == ""){
			$map['_string'] = " status = 1";
		}elseif($time == 2){
			$map['_string'] = " start_time <= ".time()." and end_time >= ".time()." and status = 1";
		}elseif($time == 3){
			$map['_string'] = " start_time > ".time()." and status = 1";
		}elseif($time == 4){
			$map['_string'] = " end_time < ".time()." and status = 1";
		}
		$hotwords_total = $hotwords_model -> where($map) ->order("weight DESC") -> select();
		import("@.ORG.Page");
		$Page = new Page(count($hotwords_total), 10);
		$hotwords_list = $hotwords_model -> where($map) ->order("weight DESC") -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$this -> assign("hotwordslist",$hotwords_list);
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
		$this -> assign("time",$time);
        $this->assign("page", $show);
		$this -> display("list_defaultkeywords");
	}
	
	//V4.0 默认搜索关键字管理_添加默认搜索关键字
	function adddefaultkeywords(){
		import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['key_words']  = $Input->REQUEST('key_words');
		$data['weight']     = $Input->REQUEST('weight');
		$data['start_time'] = strtotime($Input->REQUEST('start_time').' 00:00:00 ');
		$data['end_time']   = strtotime($Input->REQUEST('end_time').' 23:59:59 ');
		$data['add_time']   = time();
		$data['status']     = 1;
		if(empty($data['key_words'])){
		   $this -> error("请输入默认搜索关键字！");
		}
		if(empty($data['start_time'])){
		   $this -> error("请选择开始时间！");
		}
		if(empty($data['end_time'])){
		   $this -> error("请输入结束时间！");
		}
		$defaultkeywords_model = M("olgame_defaultkeywords");
		/*
		$key_words_count = $defaultkeywords_model -> where(" key_words = '".$data['key_words']."' and status = 1") -> select();
		if(count($key_words_count) > 0){
		   $this -> error("对不起，输入的默认搜索关键字已存在！");
		}
		*/
		if($data['start_time'] > $data['end_time']){
		   $this -> error("对不起，您输入的结束日期小于开始日期！");
		}
		$keywords_time_range = $defaultkeywords_model -> where("start_time >= '".$data['start_time']."' AND end_time <= '".$data['end_time']."' AND status = 1 AND  key_words = '".$data['key_words']."'") -> select();
		//echo $defaultkeywords_model->getLastSql();
		if(count($keywords_time_range) > 0){
		    $this -> error("对不起，当前输入的时间范围内有相同的默认搜索关键字！");
		}
		/*
		else{
			$weight_count = $defaultkeywords_model -> where(" weight = '".$data['weight']."' and status = 1") -> select();
			if(count($weight_count) > 0){
			   $this -> error("对不起，输入的权重已存在！");
			}
		}
		*/
		$result = $defaultkeywords_model -> add($data); 
		if($result){
			$this->writelog('默认搜索关键字列表_添加默认搜索关键字'.$data['key_words'],"sj_olgame_defaultkeywords",$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/listdefaultkeywords');
			$this -> success("默认搜索关键字添加成功");	
		}
	}


	//V4.0 搜索热词管理_删除默认搜索关键字
	function deldefaultkeywords(){
	    import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['key_id']    = $Input->REQUEST('key_id');
		$data['key_words'] = $Input->REQUEST('key_words');
		$data['status']    = 0;
		$data['add_time']  = time();
	    $hotwords = M("olgame_defaultkeywords");
		$result = $hotwords->save($data);
		if($result){
		    $this -> writelog('默认搜索关键字列表_删除默认搜索关键字id:'.$data['key_id'].'名为:'.$data['key_words'],"sj_olgame_defaultkeywords",$data['key_id'],__ACTION__ ,'','del');
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Olgamekeywords/listdefaultkeywords');
		    $this -> success("默认搜索关键字已经删除");
		}
	}


	function search_list(){
		ini_set('memory_limit','1024M');
        set_time_limit(0);
		$search = D("Sj.OlgameSearch");
		$year=date("Y",time());
		$mon=date("m",time());
		import("@.ORG.Page");
		$start_time = strtotime($_GET['start_tm']);
		$end_time = strtotime($_GET['stop_tm']);
		$zh_search=$_GET['key'];
		if($start_time && $end_time ||(!empty($zh_search))){
			if(empty($start_time)||empty($end_time)&&(!empty($zh_search))){
				$zh_week=$search->table('search')->where(array("search_key"=>$zh_search));
				$count=count($zh_week);
				$Page=new Page($count,10);
				$zh_week=$search->table('search')->field('search_key,search_count as zong')->where(array("search_key"=>$zh_search))->limit($Page->firstRow.','.$Page->listRows) ->select();
			}else{
				if(($end_time-$start_time)>3600*24*184){
					$this -> error('选择区间超过六个月！！');
				}
					$start_tm = $_GET['start_tm'];
					$stop_tm = $_GET['stop_tm'];
					$biao=$this->back_biao($start_tm,$stop_tm);
					if(!empty($zh_search)){
						$where="where search_key = '".$zh_search."' and search_time >='".$start_time."' and search_time <='".$end_time."'";
					}else{
						$where="where search_time >='".$start_time."' and search_time <='".$end_time."'";
					}
					$zh_table=$this->back_sql($biao,$where);
					$sql="select count(distinct search_key) as num from (".$zh_table.") as zhang";
					$zh_week=$search->query($sql);
					$count=$zh_week[0]['num'];
					if($count<1000){
					$count=$count;
					}else{
						$count=1000;
					}
					$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
					$Page=new Page($count,$getpage);
					$limit = " limit ".$Page->firstRow.','.$Page->listRows;
					$sql="select search_key,sum(search_count) as zong,search_time from (".$zh_table.") as zhang group by search_key order by zong desc".$limit;
					$zh_week=$search->query($sql);
			}
		}else{
			$start_time = $this->start_time();
			$end_time = time()-86400;
			$biao=$this->biao($year,$mon);
			$where="where search_time >='".$start_time."' and search_time <='".$end_time."'";
			$zh_table=$this->back_bsql($biao,$where);
				$sql="select count(distinct search_key) as num  from (".$zh_table.") as zhang";
				$zh_week=$search->query($sql);
				$count=$zh_week[0]['num'];
				if($count<1000){
					$count=$count;
				}else{
					$count=1000;
				}
				$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
				$Page=new Page($count,$getpage);
				$limit = " limit ".$Page->firstRow.','.$Page->listRows;
				$sql="select search_key,sum(search_count) as zong,search_time from (".$zh_table.") as zhang group by search_key order by zong desc".$limit;
				$zh_week=$search->query($sql);
				$stop_tm=date("Y-m-d",$end_time);
				$start_tm=date("Y-m-d",$start_time);
		}
		$page = $Page->show();
		$this -> assign("page",$page);
		$this -> assign("getpage",$getpage);
		$this -> assign("stop_tm",$stop_tm);
		$this -> assign("start_tm",$start_tm);
		$this -> assign('key',$zh_search);
		$this -> assign('key_list',$zh_week);
		$this -> display('search_list');
	}


	//搜索单个字在某段时间内的搜索情况
	function  search_key(){
		ini_set('memory_limit','1024M');
        set_time_limit(0);
		$search = D("Sj.OlgameSearch");
		$year=date("Y",time());
		$mon=date("m",time());
		import("@.ORG.Page");
		$start_tm=$_GET['start_tm'];
		$stop_tm=$_GET['stop_tm'];
		$start_time = strtotime($_GET['start_tm']);
		$end_time = strtotime($_GET['stop_tm']);
		$zh_search=$_GET['key'];
		$search_key=$_GET['search_key'];
		//echo $serch_key;
		if(empty($start_tm)||empty($stop_tm)){
			$start_time = $this->start_time();
			$end_time = time()-86400;
			$biao=$this->biao($year,$mon);
			$where="where search_key='".$search_key."' and search_time >='".$start_time."' and search_time <='".$end_time."'";
			$zh_table=$this->back_bsql($biao,$where);
			
			$sql="select count(search_key) as num from (".$zh_table.") as zhang";
			$zh_week=$search->query($sql);
			$count=$zh_week[0]['num'];
			$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
			$Page=new Page($count,$getpage);
			$limit = " limit ".$Page->firstRow.','.$Page->listRows;
			$sql="select search_key,search_count as zong,search_time from (".$zh_table.") as zhang order by search_time desc".$limit;
			$zh_week=$search->query($sql);
		}else{
			$biao=$this->back_biao($start_tm,$stop_tm);
			$where="where search_key='".$search_key."' and search_time >='".$start_time."' and search_time <='".$end_time."'";
			$zh_table=$this->back_sql($biao,$where);
			$sql="select count(search_key) as num from (".$zh_table.") as zhang";
			$zh_week=$search->query($sql);
			$count=$zh_week[0]['num'];
			$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
			$Page=new Page($count,$getpage);
			$limit = " limit ".$Page->firstRow.','.$Page->listRows;
			$sql="select search_key,search_count as zong,search_time from (".$zh_table.") as zhang order by search_time desc".$limit;
			$zh_week=$search->query($sql);
		}
		$page = $Page->show();
		//print_r($page);exit;
		$this -> assign("page",$page);
		$this -> assign("getpage",$getpage);
		$this -> assign("stop_tm",$stop_tm);
		$this -> assign("start_tm",$start_tm);
		$this -> assign('key_list',$zh_week);
		$this -> assign('zh_key',1);
		$this -> assign('key',$zh_search);
		$this -> display('search_list');
	
	}
	
	function  start_time(){
		//$n=date("N",time());
		$one_time=time()-(7*86400);
		$One_date=date("Y-m-d",$one_time);
		$start_time=strtotime($One_date);
		return $start_time;
		
	}
	
	function biao($year,$mon){
		if((int)$mon==1){
			$biao="serch".($year-1)."12,serch".$year.$mon;
		}else{
			if((int)$mon<10){
				if((int)$mon==4&&(int)$year==2012){
						$biao="serch".$year.$mon;
					}else{
						$biao="serch".$year."0".((int)$mon-1).",serch".$year.$mon;
						}
			}else{
				$biao="serch".$year.((int)$mon-1).",serch".$year.$mon;
			}
		}
		return $biao;
	}

	function back_sql($biao,$where){
		$biao=explode(",",$biao);
		$count=count($biao);
		for($i=0;$i<$count;$i++){
			$zh_table.="select serch_key,serch_count,serch_time from {$biao[$i]} ".$where." union ";
		}
		$zh_table = substr($zh_table,0,strlen($zh_table)-6);
		return $zh_table;
	}
	
	function back_bsql($biao,$where){
		$biao=explode(",",$biao);
		$count=count($biao);
		for($i=0;$i<$count;$i++){
			$zh_table.="select serch_key,serch_count,serch_time from {$biao[$i]} ".$where." union ";
		}
		$zh_table = substr($zh_table,0,strlen($zh_table)-6);
		return $zh_table;
	}
	
	function back_biao($start_time,$end_time){
		$start_date = explode("-",$start_time);
		$end_date=explode("-",$end_time);
		if($start_date[0]!=$end_date[0]){
			for($i=(int)$start_date[1];$i<=12;$i++)
			{
				if($i<10){
					$i="0".$i;
				}
				$biao1.="serch".$start_date[0].$i.",";
			}
			for($i=(int)$end_date[1];$i>=1;$i--)
			{
				if($i<10){
					$i="0".$i;
				}
				$biao2.="serch".$end_date[0].$i.",";
			}
			$biao=$biao1.$biao2;
		}else{
			for($i=(int)$start_date[1];$i<=$end_date[1];$i++){
				if($i<10){
					$i="0".$i;
				}
				$biao.="serch".$start_date[0].$i.",";
			}
		}
		//$time = $date[2].'-'.$date[0].'-'.$date[1];
		$biao = substr($biao,0,strlen($biao)-1);
		return $biao;
	}
	
	
	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}
	
}