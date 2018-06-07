<?php

class SearchkeywordsAction extends CommonAction{
	
	function kwmanage(){
			$key_model = M("soft_keywords");
			$keylist = $key_model -> where("status = 1") -> select();
			
			
			$this -> assign("keywords",$keylist);
			$this -> display("kwmanage");
			
	
	}
	
	function kwrecord(){
			$key_model = M("soft_keywords");
			$key_record = $key_model -> select();
			$this -> assign("key_record",$key_record);
			$this -> display("kwrecord");
	}
		
	
	function deletekeywords(){

			$keywords_id = escape_string($_GET["id"]);
			$keywords = escape_string($_GET["keywords"]);
			$key_model = M("soft_keywords");
			$nowtime = time();
			$all_id = $key_model -> field("id") -> where("status = 1") -> order("id desc") -> select();

			$upload_time = $key_model -> field("upload_time") -> where("id = ".$keywords_id) -> select();
			$log = $this->logcheck(array('id'=>$_GET["id"]),'sj_soft_keywords',array('status'=>0,'upload_time'=>$nowtime),$key_model);
			$affect = $key_model -> query("update __TABLE__ set status = 0,upload_time = ".$nowtime." where id = ".$keywords_id);

			$second = $all_id[1]['id'];
			$thirt = $all_id[0]['id'];
			
			$affect_second = $key_model -> query("update __TABLE__ set upload_time =".$nowtime." where id =".$thirt);
			if($keywords_id = $all_id[0]['id']){
				$affect_over = $key_model -> query("update __TABLE__ set upload_time = ".$nowtime." where id = ".$second);
				
			//$this->writelog('删除热词id:'.$keywords_id.'名为:'.$keywords,"sj_soft_keywords",$keywords_id);
			$this->writelog("搜索关键词管理_搜索热词管理_删除热词id:$keywords_id,名为:$keywords".$log,"sj_soft_keywords",$keywords_id,__ACTION__ ,"","del");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/kwmanage');
		    $this -> success("热词已删除");
			}
			
	}
	
	function addkeywords(){
			$key_model = M("soft_keywords");
			$date['keywords'] = escape_string($_GET["keywords"]);
			$date['upload_time'] = time();
			$date['status'] = 1;
			$date['admin_id'] = $_SESSION['admin']['admin_id'];
			if(empty($date['keywords'])){
				$this -> error("请填写热词！");
			}
			$common = $key_model -> where(" keywords = '".$date['keywords']."' and status = 1") -> select();
			
			//echo $date['upload_time'];
			if(count($common) > 0){
				$this -> error("对不起，热词已存在！");
			}
			
			$affect = $key_model -> add($date); 
			
			if($affect){
			$this->writelog('搜索关键词管理_搜索热词管理_添加热词'.$date['keywords'],"sj_soft_keywords",$affect,__ACTION__ ,"","add");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/kwmanage');
		    $this -> success("热词添加成功");
			
			}
	
	}
	
	//V4.0 搜索热词管理_添加搜索热词
	function addhotwords(){
        // 业务逻辑：trim一下需要用到的数据
        $useful_key = array('hot_words', 'key_type', 'location', 'start_time', 'end_time');
        foreach($useful_key as $key=>$value) {
            if (isset($_REQUEST[$value]))
                $_REQUEST[$value] = trim($_REQUEST[$value]);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $_REQUEST;
        $error_msg = $this->logic_check_hotwords($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->error($msg);
        }
        
		import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['hot_words']  = $Input->REQUEST('hot_words');
		$data['location']   = $Input->REQUEST('location');
		$data['key_type']   = $Input->REQUEST('key_type');
		$data['start_time'] = strtotime($Input->REQUEST('start_time'));
		$data['end_time']   = strtotime($Input->REQUEST('end_time'));
		$data['add_time']   = time();
		$data['status']     = 1;
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$hotwords_model = M("soft_hotwords");
        $result = $hotwords_model -> add($data);
        if($result){
            $this->writelog('搜索关键字管理_搜索热词管理V4_添加搜索热词'.$data['hot_words'],"sj_soft_hotwords",$result,__ACTION__ ,"","add");
            $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/listhotwords');
            $this -> success("搜索热词添加成功");	
        }
	
	}
	
	//V4.0 搜索热词管理_删除搜索热词
	function deletehotwords(){
	    import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['hot_id']    = $Input->REQUEST('hot_id');
		$data['hot_words'] = $Input->REQUEST('hot_words');
		$data['status']    = 0;
		$data['add_time']  = time();
	    $hotwords = M("soft_hotwords");
		$result = $hotwords->save($data);
		if($result){
		    $this -> writelog('搜索关键字管理_搜索热词管理V4_删除热词id:'.$data['hot_id'].'名为:'.$data['hot_words'],"sj_soft_hotwords",$data['hot_id'],__ACTION__ ,"","del");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/listhotwords');
		    $this -> success("搜索热词已经删除");
		}
	}
	
	//V4.0 搜索热词管理_搜索热词列表
	function listhotwords(){
		$hotwords_model = M("soft_hotwords");
		$qu_array=array(
			"1"=>"上升",
			"2"=>"下降",
			"3"=>"持平",
		);
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
		
		//$map['start_time'] = array('egt',strtotime(date("Y-m-d",time())));
		$hotwords_total = $hotwords_model -> where($map) -> select();
		import("@.ORG.Page");
		$Page = new Page(count($hotwords_total), 10);
		$hotwords_list = $hotwords_model -> where($map) -> order("location DESC") -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

		foreach($hotwords_list as $k=>$val){
			$hotwords_list[$k]['key_name']=$qu_array[$val['key_type']];
		}
		$this -> assign("hotwordslist",$hotwords_list);
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
		$this -> assign('time',$time);
        $this->assign("page", $show);
		$this -> display("list_hotwords");
	}
	
	//V4.0 搜索热词管理_搜索热词快照
	function recordhotwords(){
		$hotwords_model = M("soft_hotwords");
		$qu_array=array(
			"1"=>"上升",
			"2"=>"下降",
			"3"=>"持平",
		);
		$count = $hotwords_model -> count();
		import("@.ORG.Page");
		$Page = new Page($count, 10);
		$map['status'] = array('egt',0);
		$hotwords_list = $hotwords_model -> where($map) -> order("location DESC") -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$util = D("Sj.Util"); 
		foreach($hotwords_list as $k=>$val){
			$hotwords_list[$k]['key_name']=$qu_array[$val['key_type']];
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $key => $v){
				if($v[1] == true)
				{
					$hotwords_list[$k]['types'] = $v[0];
				}
			}
		}
		$this -> assign("hotwordslist",$hotwords_list);
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
		$this -> display("record_hotwords");
	}
	
	//V4.0 搜索热词管理_删除默认搜索关键字
	function deldefaultkeywords(){
	    import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['key_id']    = $Input->REQUEST('key_id');
		$data['key_words'] = $Input->REQUEST('key_words');
		$data['status']    = 0;
		$data['add_time']  = time();
	    $hotwords = M("soft_defaultkeywords");
		$result = $hotwords->save($data);
		if($result){
		    $this -> writelog('搜索关键词管理_默认关键字管理V4_删除默认搜索关键字id:'.$data['key_id'].'名为:'.$data['key_words'],"sj_soft_defaultkeywords",$data['key_id'],__ACTION__ ,"","del");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/listdefaultkeywords');
		    $this -> success("默认搜索关键字已经删除");
		}
	}
	
	//V4.0 搜索热词管理_默认搜索关键字列表
	function listdefaultkeywords(){
		$hotwords_model = M("soft_defaultkeywords");
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
        $selectpid = $_GET['product_id'] ? $_GET['product_id'] : 1;

        $map['pid'] = $selectpid;

		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);

		$hotwords_total = $hotwords_model -> where($map) ->order("weight DESC,start_time asc") -> select();
		import("@.ORG.Page");
		$Page = new Page(count($hotwords_total), 10);
		$hotwords_list = $hotwords_model -> where($map) ->order("weight DESC,start_time asc") -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		foreach($hotwords_list as $key => $val) {
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$hotwords_list[$key]['types'] = $v[0];
				}
			}
		}
		$content_id_arr=array();
		foreach($hotwords_list as $k=>$v){
			$content_id_arr[]=$v['content_id'];
		}
		// $common_jump_data=$hotwords_model->table('sj_common_jump')->where(array('id'=>array('in',$content_id_arr)))->select();
		// $common_jump_data_new=array();
		// foreach($common_jump_data as $k=>$v){
		// 	$content_id=$v['id'];
		// 	unset($v['id']);
		// 	unset($v['create_at']);
		// 	unset($v['update_at']);
		// 	unset($v['status']);

		// 	$common_jump_data_new[$content_id]=$v;
		// }
		$result = array();
		foreach($hotwords_list as $key=>$val) {
			if($val['content_id']){
				if($common_jump_data_new[$val['content_id']]){
					$result[] = array_merge($val,$common_jump_data_new[$val['content_id']]);
				}else{
					$result[] =$val;
				}
				
			}else{
				$result[] =$val;
			}
			
		}
		foreach($result as $k=>$val){
			// if($val['content_type'] == 5){
			// 	$result[$k]['content_type'] = "网页-".$val['website'];
			// }else if($val['content_type'] == 6){
			// 	$result[$k]['content_type'] = "礼包-".$val['gift_id'];
			// }else if($val['content_type'] == 7){
			// 	$result[$k]['content_type'] = "攻略-".$val['strategy_id'];
			// }else if($val['content_type'] == 9){
			// 	$used_info = json_decode($val['parameter_field'],true);
			// 	$result[$k]['content_type'] = "应用内览-".$used_info['softname']."-".$used_info['title'];
			// }
		}

        $product_model = M();
        $product_list = $product_model ->table('pu_product')->where('status = 1')->findAll();
        $this-> assign ("product_list", $product_list);
		$select_pid = isset($_GET['product_id']) ? $_GET['product_id'] : 1;
        $this -> assign("select_pid",$select_pid);

		$this -> assign("hotwordslist",$result);
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
		if($_GET['edit']){
			//合作形式
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			$this->edit_defaultkeywords();
			return;
		}else if(!$_POST){
			//合作形式
			$selectpid = $_GET['product_id'] ? $_GET['product_id'] : 1;	
			$this -> assign('select_pid',$selectpid);
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			$this -> display("add_defaultkeywords");
		}
        // 业务逻辑：trim一下需要用到的数据
        $useful_key = array('key_words','show_word', 'weight', 'start_time', 'end_time','type','product_id');
        $product_id = $_GET['product_id'] ? $_GET['product_id'] : 1;

        foreach($useful_key as $key=>$value) {
            if (isset($_REQUEST[$value]))
                $_REQUEST[$value] = trim($_REQUEST[$value]);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $_REQUEST;
        //var_dump($content_arr);exit;
        $error_msg = $this->logic_check_defaultkeywords($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->error($msg);
        }

        
		import("@.ORG.Input");
		$Input = Input::getInstance();
		$data['key_words']  = $Input->REQUEST('key_words');
		$data['show_word']  = $Input->REQUEST('show_word');
		$data['weight']     = $Input->REQUEST('weight');
		$data['start_time'] = strtotime($Input->REQUEST('start_time'));
		$data['end_time']   = strtotime($Input->REQUEST('end_time'));
		$data['add_time']   = time();
		$data['status']     = 1;
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$data['pid'] 		= $_POST['product_id'] ? $_POST['product_id'] : 1;
		$type=$Input->REQUEST('type');
		if(isset($type))
		{
			$data['type'] = $type;
			}else{
				$data['type'] = 0;
			}
        
		$defaultkeywords_model = M("soft_defaultkeywords");
		/*
		$key_words_count = $defaultkeywords_model -> where(" key_words = '".$data['key_words']."' and status = 1") -> select();
		if(count($key_words_count) > 0){
		   $this -> error("对不起，输入的默认搜索关键字已存在！");
		}
		*/
		/*
		else{
			$weight_count = $defaultkeywords_model -> where(" weight = '".$data['weight']."' and status = 1") -> select();
			if(count($weight_count) > 0){
			   $this -> error("对不起，输入的权重已存在！");
			}
		}
		*/
		// $conetnt_map_1 = array();
		// $rcontent_1 = ContentTypeModel::saveRecommendContent($_POST,$_POST['content_type'],$conetnt_map_1);
		// if($rcontent_1==true)
		// {
		// 	$conetnt_map_1['create_at'] = time();
		// 	$conetnt_map_1['update_at'] = time();
		// 	// echo "<pre>";var_dump($conetnt_map_1);die;
		// 	$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
		// 	$data['content_id'] = $conetnt_id_1;
		// }else {
		// 	$this -> error($rcontent_1);
		// }

		$result = $defaultkeywords_model -> add($data); 
		if($result){
			$this->writelog('搜索关键词管理_默认关键字管理V4_添加默认搜索关键字'.$data['key_words'],"sj_soft_defaultkeywords",$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/listdefaultkeywords/product_id/'.$data['pid']);
			$this -> success("默认搜索关键字添加成功");	
		}
	}
	function edit_defaultkeywords()
	{
		$key_id = $_REQUEST['key_id'];

		$where = array(
			'key_id' => $key_id
		);
		$model = M('soft_defaultkeywords');
		$keywordsdata = $model->where($where)->find();

		if (!empty($_POST)){
            
			$map = array();
			isset($_POST['key_id']) && $map['key_id'] = trim($_POST['key_id']);
			// $map['update_tm'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];

			isset($_POST['key_words']) && $map['key_words'] = trim($_POST['key_words']);
			
			isset($_POST['type']) && $map['type'] = trim($_POST['type']);
			isset($_POST['weight']) && $map['weight'] = trim($_POST['weight']);
			isset($_POST['show_word']) && $map['show_word'] = trim($_POST['show_word']);
			isset($_POST['start_time']) && $map['start_time'] = strtotime($_POST['start_time']);
			isset($_POST['end_time']) && $map['end_time'] = strtotime($_POST['end_time']);
			
			
			// $conetnt_map_1 = array();
			// $rcontent_1 = ContentTypeModel::saveRecommendContent($_POST,$_POST['content_type'],$conetnt_map_1);
			// if($rcontent_1==true)
			// {
			// 	$conetnt_map_1['create_at'] = time();
			// 	$conetnt_map_1['update_at'] = time();
			// 	$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
			// 	$map['content_id'] = $conetnt_id_1;
			// }else {
			// 	$this -> error($rcontent_1);
			// }
				
			$log_msg = $this->logcheck($where, 'sj_soft_defaultkeywords', $map, $model);
			// var_dump($map);
			if ($model->where($where)->save($map)) 
			{
				// echo $model->getlastsql();die;
				
				$this->writelog("市场搜索管理- 榜单推荐:编辑了id为{$id},{$log_msg}",'sj_soft_defaultkeywords',$key_id,__ACTION__ ,"","edit");
				
				// $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/listdefaultkeywords');
				$this -> success("默认搜索关键字编辑成功");	
			}
		} else {
			// $content_list = M('')->table('sj_common_jump')->where(array('id' => $keywordsdata['content_id']))->find();
			// $this->assign('content_list',$content_list);
			$this->assign('keywordsdata', $keywordsdata);
			$this->display('edit_defaultkeywords');
		}
	}
	// 搜索热词推荐软件管理
	function soft_hot_words(){
		$time = time();
		$where = 'stat=1';
		$_GET['product_id'] = isset($_GET['product_id']) ? $_GET['product_id'] : 1;
		// if ($softid = trim($_POST['softid'])){
		// 	$where .= " AND B.softid=$softid";
		// 	$this->assign('softid', $softid);
		// }
		if ($package = trim($_GET['packagename'])){
			$where .= " AND package='$package'";
			$this->assign('packagename', $package);
		}
		if ($associate = trim($_GET['associate'])){
			$where .= " AND associate like '%$associate%'";
			$this->assign('associate', $associate);
		}
		if($_GET['product_id']){
			$where .= " AND pid={$_GET['product_id']}";
			$this->assign('product_id',$_GET['product_id']);			
		}
		if ($_GET['stat']){
			$stat = trim($_GET['stat']);
			switch ($stat){
				case 1:
					$where .= " AND begin<=$time AND end>=$time";
					break;
				case 2:
					$where .= " AND end<$time";
					break;
				case 3:
					$where .= " AND begin>$time";
					break;
				default:
						break;
			}
			$this->assign('stat', $stat);
		}
		$saw = M('soft_associate_hot_word');
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $saw->where($where)->order('begin asc')->count();
		$Page = new Page($count, 15);
		$show = $Page->show();
		$softlist = $saw->where($where)->order('begin asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$util = D("Sj.Util"); 
		foreach($softlist as $key => $val){
			$val['associates'] = substr($val['associate'],1);
			$softlist[$key]['associate'] = substr($val['associates'],0,-1);
			$soft_result = $saw -> table('sj_soft') -> where(array('package' => $val['package'])) -> select();
			$val['softid'] = $soft_result[0]['softid'];
			$val['softname'] = $soft_result[0]['softname'];
			if($val['softid']){
				$softlist[$key] = $val;
			}
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$softlist[$key]['types'] = $v[0];
				}
			}
		}
		/*echo '<pre>';
		print_r($softlist);
		echo '</pre>';*/
		$this->assign('softlist', $softlist);
		$this->assign('page', $show);
		#产品列表
        $product_model = M();
        $product_list = $product_model ->table('pu_product')->where('status = 1')->findAll();
        $this-> assign ("product_list", $product_list);		
		$this->display();
	}
	function soft_hot_words_del(){
		$id = $_GET['id'];
		if (!$id){
			$this->ajaxReturn(0, '未知错误！', 0);
			return ;
		}
		$saw = M('soft_associate_hot_word');
		$data['stat'] = 0;
		$data['update_time'] = time();
		if ($saw->where("id=$id")->save($data)){
			$this->writelog('搜索阿拉丁管理_删除了 id为'.$id.'的记录',"sj_soft_associate_hot_word",$id,__ACTION__ ,"","del");
			$this->ajaxReturn(1, '删除成功！', 1);
		} else {
			$this->ajaxReturn(0, '删除 失败！', 0);
		}
		return ;
	}
	function soft_hot_words_add()
	{
		if ($_POST)
		{
			$saw = M('soft_associate_hot_word');
			$package = trim($_POST['package']);
			$recommend=trim($_POST['recommend']);
			$associate=trim($_POST['associate']);
			if($_POST['begintime']=="")
			{
			 $start_time=strtotime(date('Y-m-d H:i:s',time()));
			}
			else
			{
			 $start_time = strtotime($_POST['begintime']);
			}
			if($_POST['endtime']=="")
			{
			 $end_time=strtotime(date('Y-m-d H:i:s',time()));
			}
			else
			{
			$end_time = strtotime($_POST['endtime']);
			}
			if(isset($_POST['type'])){
				$type = $_POST['type'];
			}else{
				$type = 0;
			}
			
		    // tpl（网页）里的名称和数据库字段对应数组
            $column_convert_arr = array(
                'package' => 'package',
                'recommend' => 'recommend',
                'associate' => 'associate',
                'background' => 'background',
				'begintime' => 'begin',
                'endtime' => 'end',
				'publicimg' => 'publicimg',
				'type' =>'type',
				'pid' =>'pid',
            );
			 // $check_column_arr数组存放_POST/_GET对应数据库字段的值（因为logic_check里的变量名跟数据库字段名一样）
            $check_column_arr = array();
            foreach($column_convert_arr as $key=>$value) 
			{
                if (array_key_exists($key, $_POST)) 
				{
                    $check_column_arr[$value] = trim($_POST[$key]);
                }
            }
            // trim一下
            foreach($check_column_arr as $key=>$value) 
			{
                $check_column_arr[$key] = trim($value);
            }
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $check_column_arr;
            $error_msg = $this->logic_check_soft_hot_words($content_arr);
			
			 // 上传的图片
			$tmp_publicimg = $_FILES['publicimg']['tmp_name'];
			$tmp_background = $_FILES['background']['tmp_name'];
			
            $config = array('multi_config' => array());
            $path=date('Ym/d/', time());
            if ($tmp_background) {
                $config['multi_config']['background'] = array(
                    'savepath' => UPLOAD_PATH. '/img/'. $path,
                    'saveRule' => 'getmsec',
                    'enable_resize' => false,
                    /*'img_p_size' =>  1024*20,
                    'img_p_width' => 122,
                    'img_p_height' => 480,*/
                );
            }
            
            if ($tmp_publicimg) {
                $config['multi_config']['publicimg'] = array(
                    'savepath' => UPLOAD_PATH. '/img/'. $path,
                    'saveRule' => 'getmsec',
                    'enable_resize' => false,
                    /*'img_p_size' =>  1024*20,
                    'img_p_width' => 122,
                    'img_p_height' => 480,*/
                );
            }
            
            if (!empty($config['multi_config'])) 
			{
                $lists=$this->_uploadapk(0, $config);
                foreach($lists['image'] as $val) {
                    if ($val['post_name'] == 'background') 
					{
                        $data['background']= $val['url'];
                    }
					else if($val['post_name'] == 'publicimg')
					{
                        $data['publicimg']= $val['url'];
                    }
                }
            }
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) 
			{
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) 
			{
                $msg = $error_msg[0]['msg'];
                $this->error($msg);
            }
			$data['pid']=$_POST['pid'];
			$data['package']=trim($package);
			$data['recommend']=trim($recommend);
			$data['associate']=";".trim($associate).";";
			$data['begin']=$start_time;
			$data['end']=$end_time;
			$data['update_time']=time();
			$data['create_time']=time();
			$data['stat']=1;
			$data['type']=$type;
			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			if($data['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($data['package'],$data['begin'],$data['end'],1);
				if($shield_error){
				    $this -> error($shield_error);
				}
			}
			

			$result = $saw -> table('sj_soft_associate_hot_word') -> add($data);
			
			//同步到搜索结果中
			//搜索结果运营有关键字但是没包名，添加的软件是往该关键字中添加
			$key_arr=explode(";",trim($associate));
			if($content_arr[0]['no_add']) //关键字和软件包在搜索结果运营已经存在就不同步
			{
				$key_arr = array_diff(array_filter($key_arr),$content_arr[0]['no_add']);
			}
			if($content_arr[0]['key_ids_words'])
			{
				foreach($content_arr[0]['key_ids_words'] as $k =>$v)
				{
					$map_package=array(
						'kid'=>$k,
						'status' =>1,
						'package'=>$data['package'],
						'weight'=>'',
						'pos'=>1,
						'pid'=>'',
						'type'=>0,
						'create_tm' =>time(),
						'update_tm' =>time(),
						'start_tm' =>$data['begin'],
						'stop_tm' =>$data['end'],
						'pid' =>1,
						'co_type'=>$data['type'],
						'admin_id'=>$_SESSION['admin']['admin_id'],
					);
					$result_package=$saw -> table('sj_search_key_package')->add($map_package);
					if($result_package)
					{
						$this->writelog('搜索关键字管理_单个从搜索阿拉丁同步到搜索结果运营软件列表（有关键字）_同步关键字 软件'.$result_package.'包名为'.$data['package'],"sj_search_key_package",$result_package,__ACTION__ ,"","add");
					}
				}
				$key_diff = array_diff($key_arr,$content_arr[0]['key_ids_words']);
			}
			else
			{
				$key_diff = $key_arr;
			}
			//其余关键字 添加关键字列表 同时添加关键字包名
			foreach($key_diff as $k => $val)
			{
				$map=array(
					'srh_key'=>$val,
					'status' =>1,
					'create_tm' =>time(),
					'update_tm' =>time(),
					'start_tm' =>$data['begin'],
					'stop_tm' =>$data['end'],
					'pid' =>1,
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result_key=$saw -> table('sj_search_key')->add($map);
				$map_package=array(
					'kid'=>$result_key,
					'status' =>1,
					'package'=>$data['package'],
					'weight'=>'',
					'pos'=>1,
					'pid'=>'',
					'type'=>0,
					'create_tm' =>time(),
					'update_tm' =>time(),
					'start_tm' =>$data['begin'],
					'stop_tm' =>$data['end'],
					'pid' =>1,
					'co_type'=>$data['type'],
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result_package=$saw -> table('sj_search_key_package')->add($map_package);
				if($result_key)
				{
					$this->writelog("搜索关键字管理_单个从搜索阿拉丁同步到搜索结果运营关键字列表_同步关键字{$val}id:".$result_key,"sj_search_key",$result_key,__ACTION__ ,"","add");
				}
				if($result_package)
				{
					$this->writelog('搜索关键字管理_单个从搜索阿拉丁同步到搜索关键字软件列表（无关键字先增加关键字）_同步关键字 软件'.$result_package.'包名为'.$data['package'],"sj_search_key_package",$result_package,__ACTION__ ,"","add");
				}
			}
			if($result)
			{
			    $this->writelog("市场搜索管理-单个搜索阿拉丁管理:添加包名'".$data['package']."'的关联词为$associate","sj_soft_associate_hot_word",$result,__ACTION__ ,"","add");
				$this->success("添加成功");
			}
			else
			{
				$this->error("添加失败");
			}
					} 
		else 
		{
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			$this->display();
		}
	}
	function soft_hot_words_search(){
		$package = trim($_POST['package']);
		$soft_db = M('soft');
		$soft = $soft_db->where("package='$package' AND status=1 AND hide=1")->field('softid, softname, package, hide')->select();
		if (!count($soft)){
			$this->ajaxReturn(0, '包不存在于市场当中！或者已下架……', 0);
		} else {
			$this->ajaxReturn(1, $soft[0]['softname'], 1);
		}
	}
	function soft_hot_words_edit(){
		if ($_POST){
			$saw = M('soft_associate_hot_word');
			$idd = $_POST['id'];
			$pid = $_POST['pid'];
			$package = trim($_POST['package']);
			if ($package == '' || empty($package)){
				$this->error('包名不能为空，请填写有效的包名！');
				return ;
			}
			if(isset($_POST['type'])){
				$data['type'] = $_POST['type'];
			}else{
				$data['type'] = 0;
			}
			$data['package'] = $package;
			$data['recommend'] = trim($_POST['recommend']) ? trim($_POST['recommend']) : '';
			if (!$data['recommend'] && mb_strlen($data['recommend'],'utf-8') > 150){
				$this->error('推荐简介1~150个字！');
			}
			$associate = trim($_POST['associate']);
            if (!$associate) {
                $this->error('关联词不能为空！');
            }
			$associate_arr = explode(';', $associate);
            // 将每个关键字前后空格去掉
            foreach ($associate_arr as $key => $value) {
                $associate_arr[$key] = trim($value);
            }
			
            if ($_POST['begintime']) {
                $begintime = strtotime(trim($_POST['begintime']));
            } else {//为空则赋值为今天零点
                $begintime = strtotime(date('Y-m-d H:i:s',time()));
            }
            if ($_POST['endtime']) {
                $endtime = strtotime(trim($_POST['endtime']));
            } else {//为空则赋值为今天零点
                $endtime = strtotime(date('Y-m-d H:i:s',time()));
            }
            
            if (!$begintime) {
                $this->error("开始时间不能为空");
            }
            if (!$endtime) {
                $this->error("结束时间不能为空");
            }
            
			$data['begin'] = $begintime;
			$data['end'] = $endtime;
			if ($data['begin'] > $data['end']){
				$this->error('对不起，开始时间不能大于结束时间！');
				return ;
			}
			
			//不用检查包是否存在
			/*$res = $saw->where("package='$package' and id != {$idd} and stat=1 and begin <= {$data['end']} and end >= {$data['begin']}")->select();
			if ($res){
				$this->error('已存在包！');
			}*/
			// 关键字统一转小写
            foreach ($associate_arr as $key => $value) {
                $associate_arr[$key] = strtolower($value);
            }
            // 关键字去重
            $associate_arr = array_unique($associate_arr);
            // 记录存在冲突的关键字
            $wrong_associate_map = array();
            $wrong_i = 0;
			foreach($associate_arr as $key => $val){
                if (!$val)
                    continue;
                // 跟自表判断是否有重复
                $have_ass = ';'.$val.';';
                $where_have['_string'] = "associate like '%{$have_ass}%' and id != {$idd} and stat = 1 and begin < {$data['end']} and end >= {$data['begin']} and pid={$pid}";
                $have_result = $saw -> where($where_have) -> select();
                if($have_result){
                    $wrong_associate_map[$wrong_i]['associate_single'] = $val;
                    $wrong_associate_map[$wrong_i++]['reason'] = "已绑定阿拉丁";
                    continue;
                }
                // 在搜索关键字表查询在此时间段内有没有运营相同关键字
                $where = array(
                    'srh_key' => array('eq', $val),
                    'start_tm' => array('elt', $data['end']),
                    'stop_tm' => array('egt', $data['begin']),
                    'status' => 1,
                );
                $find = $saw->table('sj_search_key')->where($where)->find();
                if (!$find)
                    continue;
                // 找到，判断这个关键字在此时间段有没有添加内容
                $kid = $find['id'];
                $where = array(
                    'kid' => array('eq', $kid),
                    'start_tm' => array('elt', $data['end']),
                    'stop_tm' => array('egt', $data['begin']),
                    'status' => 1,
                );
                $find_list = $saw->table('sj_search_key_package')->where($where)->select();
                if (empty($find_list)) {
					$key_kids[$kid] = $val;
                    continue;
                }
                // 有添加内容，判断添加的内容是什么
                $type = $find_list[0]['type'];
                if ($type == 1) {
                    // 此关键字不允许添加阿拉丁，记录下此关键字
                    $wrong_associate_map[$wrong_i]['associate_single'] = $val;
                    $wrong_associate_map[$wrong_i++]['reason'] = "关键字已指向了页面，该关键字添加失败";
                    continue;
                }
                // 说明添加的都是包
                $wrong_situation = 0;
                foreach ($find_list as $find) {
                    $find_package = $find['package'];
                    $find_pos = $find['pos'];
                    if ($find_package == $package && $find_pos != 1) {
                        // 搜索关键字列表已添加了此包，但并不是排第一位
                        $wrong_situation = 1;
                        break;
                    } else if ($find_package != $package && $find_pos == 1) {
                        // 搜索关键字列表排第一位的包和阿拉丁的包设置不一致
                        $wrong_situation = 2;
                        break;
                    }
                }
                if ($wrong_situation == 1) {
                    $wrong_associate_map[$wrong_i]['associate_single'] = $val;
                    $wrong_associate_map[$wrong_i++]['reason'] = "关键字已推荐了相同软件，该关键字添加失败";
                    continue;
                } else if ($wrong_situation == 2) {
                    $wrong_associate_map[$wrong_i]['associate_single'] = $val;
                    $wrong_associate_map[$wrong_i++]['reason'] = "关键字在【搜索关键字列表】已存在第一位的运营数据，该关键字添加失败";
                    continue;
                }
			}
            
			$tmp_background = $_FILES['background']['tmp_name'];
			$tmp_publicimg = $_FILES['publicimg']['tmp_name'];

			$config = array('multi_config' => array());
            $path=date('Ym/d/', time());
            if ($tmp_background) {
                $config['multi_config']['background'] = array(
                    'savepath' => UPLOAD_PATH. '/img/'. $path,
                    'saveRule' => 'getmsec',
                    'enable_resize' => false,
                    /*'img_p_size' =>  1024*20,
                    'img_p_width' => 122,
                    'img_p_height' => 480,*/
                );
            }
            
            if ($tmp_publicimg) {
                $config['multi_config']['publicimg'] = array(
                    'savepath' => UPLOAD_PATH. '/img/'. $path,
                    'saveRule' => 'getmsec',
                    'enable_resize' => false,
                    /*'img_p_size' =>  1024*20,
                    'img_p_width' => 122,
                    'img_p_height' => 480,*/
                );
            }
            
            if (!empty($config['multi_config'])) {
                $lists=$this->_uploadapk(0, $config);
                foreach($lists['image'] as $val) {
                    if ($val['post_name'] == 'background') {
                        $data['background']= $val['url'];
                    } else if($val['post_name'] == 'publicimg'){
                        $data['publicimg']= $val['url'];
                    }
                }
            }

			$data['update_time'] = time();
            $data['admin_id'] = $_SESSION['admin']['admin_id'];
            // 添加可以被添加的关键字
            $wrong_associate_arr = array();
            $wrong_reason = '';
            foreach ($wrong_associate_map as $key => $value) {
                $associate_single = $value['associate_single'];
                $reason = $value['reason'];
                $wrong_associate_arr[] = $associate_single;
                $wrong_reason .= "关键字【{$associate_single}】添加失败！原因：{$reason};";
            }
            $right_associate_arr = array_diff($associate_arr, $wrong_associate_arr);
            
            if (empty($right_associate_arr)) {
                $this->error("{$wrong_reason}");
            }
            // 将正确的关键字重新组装在一起
            $data['associate'] = ';' . implode(";", $right_associate_arr) . ';';
            
			$log = $this -> logcheck(array('id' =>$idd),'sj_soft_associate_hot_word',$data,$saw);
			if($data['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($data['package'],$data['begin'],$data['end'],1);
				if($shield_error){
				    $this -> error($shield_error);
				}
			}
			
			$id=$saw->where("id='$idd'")->save($data);
			if($idd){
				$this->writelog('市场搜索管理-搜索阿拉丁管理:编辑了名称ID为['.$idd.']为['.$package.']的推荐软件'.$log, 'sj_soft_associate_hot_word', $idd,__ACTION__ ,"","edit");
				
				//$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/soft_hot_words');
                if (!empty($wrong_associate_arr)) {
                    $this->assign('waitSecond', 8);
                    $this->success("{$wrong_reason}其余编辑成功！");
                } else {
                    $this->success("编辑成功！");
                }
			}else{
				//$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Searchkeywords/soft_hot_words');
				$this->error("编辑失败,发生错误！");
			}			
		} else {
			if ($id = $_GET['id']){
				$saw = M('soft_associate_hot_word');
				$list = $saw->where("id='$id'")->select();
				$list[0]['associates'] = substr($list[0]['associate'],1);
				$list[0]['associate'] = substr($list[0]['associates'],0,-1);
				
				$util = D("Sj.Util");
				$typelist = $util->getHomeExtentSoftTypeList($list[0]['type']);
				$this->assign('typelist',$typelist);

				$this->assign('list', $list[0]);
				$this->assign('id', $id);
				$this->display();
			} else {
				$this->error('发生未知错误！');
				return ;
			}
		}
	}
    
    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
    
    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check_hotwords(&$content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'hot_words'     =>  '搜索热词',
            'location'   =>  '所在位置',
            'start_time'  =>   '开始时间(yyyy/MM/dd)',
            'end_time'  =>   '结束时间(yyyy/MM/dd)',
            'key_type'  =>   '趋势',
			'type' =>   '合作形式',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        // 业务逻辑：检查列填写是否为预期内容（固定的内容），如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 有些字段输入为空时是合法的，有些字段输入为空不允许，当为空不允许时，将其值设为false以作区别
        $expected_words['key_type'] = array("" => false, "上升" => 1, "下降" => 2, "持平" => 3);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
                if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 检查是否为false，如果不是，则表示可以为空，替换成相应的数字，否则不处理，即还是为空，在logic_check()里会进行非空值判断
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_time' || $r_key == 'end_time') {
                    if ($r_key == 'start_time') {
                        $type = 0;
                        $hint = '开始';
                    } else {
                        $type = 1;
                        $hint = '结束';
                    }
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $this->append_error_msg($error_msg, $key, 1, "{$hint}时间需以T开头;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }// else转换错误，保持原始值，后面的logic_check会校验原始格式
                }
            }
        }
        return $error_msg;
    }
    //搜索阿拉丁导入文件检查
	function handwriting_convert_and_check_soft_hot_words(&$content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'package'      =>  '广告位包名',
            'associate'    =>  '关联热词(多个关联词以‘;’分隔)',
			'recommend'    =>  '简介(非必填,限制150字以内)',
            'begin'        =>  '开始时间(yyyy/MM/dd)',
            'end'          =>  '结束时间(yyyy/MM/dd)',
			'type'         =>  '合作形式',
         );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
		// 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息   
		$expected_words = array();   
		// 当输入为空不允许时，将其值设为false以作区别  
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
		foreach($content_arr as $key=>$record) 
		{
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) 
			{
				if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 如果是false不处理（在后台的logic_check()里会统一进行非空检查），即还是为空，否则替换成相应的数字
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 判断开始时间和结束时间，去掉T如果没有精确到秒后面加上00:00:00,23:59:59
                if ($r_key == 'begin') 
				{
				  if($content_arr[$key][$r_key])
				    {
					  if(strpos($content_arr[$key][$r_key],"T")==0)
					 {
					  $content_arr[$key][$r_key]=substr($content_arr[$key][$r_key], 1);
					 }
					  if(strpos($content_arr[$key][$r_key],"/")==true)
					  {
						$content_arr[$key][$r_key]=str_replace("/","-",$content_arr[$key][$r_key]);
					  }
					  if(strpos($content_arr[$key][$r_key],":")==false)
					  {
						$content_arr[$key][$r_key] .= ' 00:00:00';
					  }
					}
				   else
					{//为空则默认当前时间
					  $content_arr[$key][$r_key]=date('Y-m-d H:i:s',time());
					}
				  
                } 
				else if ($r_key == 'end') 
				{
					if($content_arr[$key][$r_key])
					{
					   if(strpos($content_arr[$key][$r_key],"T")==0)
					   {
					    $content_arr[$key][$r_key]=substr($content_arr[$key][$r_key], 1);
					   }
					   if(strpos($content_arr[$key][$r_key],"/")==true)
					   {
						$content_arr[$key][$r_key]=str_replace("/","-",$content_arr[$key][$r_key]);
					   }
					   if(strpos($content_arr[$key][$r_key],":")==false)
					   {
						$content_arr[$key][$r_key] .= ' 23:59:59';
					   }
					}
					else
					{//为空则默认当前时间
					  $content_arr[$key][$r_key]=date('Y-m-d H:i:s',time());
					}
                }
            }
        }
        return $error_msg;
    }
	
    function handwriting_convert_and_check_defaultkeywords(&$content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'key_words'     =>  '默认关键字',
            'weight'   =>  '权重',
            'start_time'  =>   '开始时间(yyyy/MM/dd)',
            'end_time'  =>   '结束时间(yyyy/MM/dd)',
			'type' =>   '合作形式'
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
		$expected_words = array();
		// 开始检查每列内容是否为预期内容
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_value) {
				if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 如果是false不处理（在后台的logic_check()里会统一进行非空检查），即还是为空，否则替换成相应的数字
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_time' || $r_key == 'end_time') {
                    if ($r_key == 'start_time') {
                        $type = 0;
                        $hint = '开始';
                    } else {
                        $type = 1;
                        $hint = '结束';
                    }
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $this->append_error_msg($error_msg, $key, 1, "{$hint}时间需以T开头;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }// else转换错误，保持原始值，后面的logic_check会校验原始格式
                }
            }
        }
        return $error_msg;
    }
    
    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_hotwords($content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：获得热词表model
        $hotwords_model = M("soft_hotwords");
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查搜索热词是否为空
            if (isset($record['hot_words']) && !empty($record['hot_words'])) {
            } else {
                $this->append_error_msg($error_msg, $key, 1, "搜索热词不能为空;");
            }
            // 检查当前位置是否为数字
            if (isset($record['location']) && !empty($record['location'])) {
                if (!preg_match("/^\d+$/", $record['location'])) {
                    $this->append_error_msg($error_msg, $key, 1, "所在位置应为整数;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "所在位置不能为空;");
            }
            // 检查开始时间
            if (isset($record['start_time']) && $record['start_time'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_time'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_time']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_time']) && $record['end_time'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_time'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_time']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
        }
        
        // 检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) {
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                $k1 = $key1 + 1;
                $k2 = $key2 + 1;
                if ($record1['location'] == $record2['location']) {
                    // 时间是否交叉
                    if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                        $this->append_error_msg($error_msg, $key1, 1, "同一位置下，投放时间与第{$k2}行有冲突;");
                        $this->append_error_msg($error_msg, $key2, 1, "同一位置下，投放时间与第{$k1}行有冲突;");
                    }
                }
                if ($record1['hot_words'] == $record2['hot_words']) {
                    // 时间是否交叉
                    if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                        $this->append_error_msg($error_msg, $key1, 1, "同一热词下，投放时间与第{$k2}行有冲突;");
                        $this->append_error_msg($error_msg, $key2, 1, "同一热词下，投放时间与第{$k1}行有冲突;");
                    }
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            // 查找位置相同的记录
            $where = array(
                'location' => array('EQ', $record['location']),
                'status' => array('NEQ', 0),
                'start_time' => array('ELT', $record['bk_end_time']),
                'end_time' => array('EGT', $record['bk_start_time']),
            );
            // 如果是编辑，需在后台记录中排除自己
            if (isset($record['hot_id'])) {
                $where['hot_id'] = array('NEQ', $record['hot_id']);
            }
            $db_records = $hotwords_model->where($where)->select();
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) {
                $start_time_str = date('Y-m-d H:i:s',$db_record['start_time']);
                $end_time_str = date('Y-m-d H:i:s',$db_record['end_time']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "同一位置下，投放时间与后台记录ID为【{$db_record['hot_id']}】、热词为【{$db_record['hot_words']}】的记录有冲突（其时间从【{$start_time_str}】到【{$end_time_str}】{$status_paused_hint}）;");
            }
            // 查找热词相同的记录
            $where = array(
                'hot_words' => array('EQ', $record['hot_words']),
                'status' => array('NEQ', 0),
                'start_time' => array('ELT', $record['bk_end_time']),
                'end_time' => array('EGT', $record['bk_start_time']),
            );
            // 如果是编辑，需在后台记录中排除自己
            if (isset($record['hot_id'])) {
                $where['hot_id'] = array('NEQ', $record['hot_id']);
            }
            $db_records = $hotwords_model->where($where)->select();
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) {
                $start_time_str = date('Y-m-d H:i:s',$db_record['start_time']);
                $end_time_str = date('Y-m-d H:i:s',$db_record['end_time']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "同一热词下，与后台记录ID为【{$db_record['hot_id']}】、热词为【{$db_record['hot_words']}】的记录有冲突（其时间从【{$start_time_str}】到【{$end_time_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
    //搜索阿拉丁逻辑检查
	function logic_check_soft_hot_words(&$content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：获得阿拉丁表model
		$saw = M('soft_associate_hot_word');
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
            // 检查包名是否为空logic_check_soft_hot_words是否有效
            if (isset($record['package']) && !empty($record['package'])) 
			{
				$have_package = $saw -> table('sj_soft') -> where(array('package' => $record['package'],'status' => 1,'hide' => 1)) -> select();
				if(!$have_package)
				{
					$this -> append_error_msg($error_msg, $key, 1, "包名不存在;");
				}
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空，请填写有效的包名;");
            }
            // 检查推荐介绍是否符合要求
			if(isset($record['recommend'])&&!empty($record['recommend']))
			{
			  if(mb_strlen(trim($record['recommend']),'utf-8') > 150)
			  {
			    $this->append_error_msg($error_msg, $key, 1,"推荐简介1~150个字;");
			  }
			}
			//检查关联词
			if(isset($record['associate'])&&!empty($record['associate']))
			{
				 $associate_arr = explode(';',trim($record['associate']));
				//将每个关键字前后空格去掉关键字统一转小写
				foreach ($associate_arr as $key2 => $value) 
				{
					$associate_arr[$key2] = strtolower(trim($value));
				}
				// 判断本身关联字是否重复
				$count=count($associate_arr);
				$count_new=count(array_unique($associate_arr));
				if($count!=$count_new)
				{
				 $this->append_error_msg($error_msg, $key, 1, "您填写的关联词内容有重复;");
				}
				// 记录存在冲突的关键字
				$wrong_associate_map = array();
				$wrong_i = 0;
				foreach($associate_arr as $ke => $val)
				{
					if (!$val)
						continue;
					// 跟自表判断是否有重复
					$have_ass = ';'.$val.';';
					$end_tm=strtotime($record['end']);
					$begin_tm=strtotime($record['begin']);
					$where_have['_string'] = "associate like '%{$have_ass}%' and stat = 1 and begin < {$end_tm} and end >= {$begin_tm} and pid={$record['pid']}";
					$have_result = $saw -> where($where_have) -> select();
					if($have_result)
					{
						$wrong_associate_map[$wrong_i]['associate_single'] = $val;
						$wrong_associate_map[$wrong_i++]['reason'] = "已绑定阿拉丁";
						continue;
					}
					// 在搜索关键字表查询在此时间段内有没有运营相同关键字
					$where = array(
						'srh_key' => array('eq', $val),
						//'start_tm' => array('elt', $end_tm),
						//'stop_tm' => array('egt', time()),
						'status' => 1,
					);
					$find = $saw->table('sj_search_key')->where($where)->find();
					if (!$find)
						continue;
					// 找到，判断这个关键字在此时间段有没有添加内容
					$kid = $find['id'];
					$where = array(
						'kid' => array('eq', $kid),
						'start_tm' => array('elt', $end_tm),
						'stop_tm' => array('egt', $begin_tm),
						'status' => 1,
					);
					$find_list = $saw->table('sj_search_key_package')->where($where)->select();
					//如果是空，可以往次关键词添加软件
					if (empty($find_list)) 
					{
						$content_arr[$key]['key_ids_words'][$kid] =$val;
						continue;
					}
					else
					{
						// 有添加内容，判断添加的内容是什么
						$type = $find_list[0]['type'];
						if ($type == 1) 
						{
							// 此关键字不允许添加阿拉丁，记录下此关键字
							$wrong_associate_map[$wrong_i]['associate_single'] = $val;
							$wrong_associate_map[$wrong_i++]['reason'] = "关键字已指向了页面，该关键字添加失败";
							continue;
						}
						else
						{
							// 说明添加的都是包
							$wrong_situation = 0;
							foreach ($find_list as $find) 
							{
								$find_package = $find['package'];
								$find_pos = $find['pos'];
								$package = $record['package'];
								if ($find_package == $package && $find_pos != 1) 
								{
									// 搜索关键字列表已添加了此包，但并不是排第一位
									$wrong_situation = 1;
									break;
								} 
								elseif($find_package != $package && $find_pos == 1) 
								{
									// 搜索关键字列表排第一位的包和阿拉丁的包设置不一致
									$wrong_situation = 2;
									break;
								}
								elseif($find_package == $package && $find_pos == 1) 
								{
									//说明该包再时间段内有已有排期 不处理
									$content_arr[$key]['no_add'][$kid] =$val;
									continue;
								}
								else
								{
									$content_arr[$key]['key_ids_words'][$kid] =$val;
									continue;
								}
							}
						}
					}
					if ($wrong_situation == 1) 
					{
						$wrong_associate_map[$wrong_i]['associate_single'] = $val;
						$wrong_associate_map[$wrong_i++]['reason'] = "关键字已推荐了相同软件，该关键字添加失败";
						continue;
					} 
					elseif ($wrong_situation == 2) 
					{
						$wrong_associate_map[$wrong_i]['associate_single'] = $val;
						$wrong_associate_map[$wrong_i++]['reason'] = "关键字在【搜索关键字列表】已存在第一位的运营数据，该关键字添加失败";
						continue;
					}

				}
				 // 关键字失败的原因
				$wrong_associate_arr = array();
				$wrong_reason = '';
				if($wrong_associate_map)
				{
					foreach ($wrong_associate_map as $k => $value) 
					{
						$associate_single = $value['associate_single'];
						$reason = $value['reason'];
						$wrong_associate_arr[] = $associate_single;
						$wrong_reason .= "关键字【{$associate_single}】添加失败！原因：{$reason};";
					}
					$this->append_error_msg($error_msg, $key, 1, "{$wrong_reason}");
				}
				else
				{
					// 将正确的关键字重新组装在一起
					$content_arr[$key]['associate'] = ';' . implode(";", $associate_arr) . ';';
				}
			}
			else
			{
				$this->append_error_msg($error_msg, $key, 1, "关联词不能为空;");
			}
            // 检查开始时间
			if($record['begin']=="")
			{
				$record['begin']=date('Y-m-d H:i:s',time());
			}
			if($record['end']=="")
			{
				$record['end']=date('Y-m-d H:i:s',time());
			}
            if (isset($record['begin']) && $record['begin'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['begin'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } 
				else 
				{
				    $time = strtotime($record['begin']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end']) && $record['end'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } 
				else 
				{   
				   $time = strtotime($record['end']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            }
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) 
			{
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
        }
        // 检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) 
		{
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) 
			{
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
				//关联热词是否有重复
				$associate_arr1 = explode(';',trim($record1['associate']));
				$associate_arr2 = explode(';',trim($record2['associate']));
				// 关键字统一转小写 将每个关键字前后空格去掉
				foreach ($associate_arr1 as $ke => $value) 
				{
					$associate_arr1[$ke] = strtolower(trim($value));
				}
				foreach ($associate_arr2 as $ke2 => $value) 
				{
					$associate_arr2[$ke2] = strtolower(trim($value));
				}
				$associate1=array_filter($associate_arr1);
				$associate2=array_filter($associate_arr2);
				$intersect=array_intersect($associate1,$associate2);
				$k1 = $key1 + 1;
                $k2 = $key2 + 1;
				//包名相同 时间交叉 关键字不能重复	
                if ($record1['package'] == $record2['package']) 
				{
                    // 时间交叉下
					if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) 
					{
						if($intersect)
						{
							$this->append_error_msg($error_msg, $key1, 1,
							"同一包名，时间交叉下，关联热词与第{$k2}行有重复;");
							$this->append_error_msg($error_msg, $key2, 1, 
							"同一包名，时间交叉下，关联热词与第{$k1}行有重复;");
						}
					}
                }
				else
				{//包名不同 关键词相同 时间不能重复
					if($intersect)
					{
						$data = implode(',',$intersect);
						if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) 
						{
							$this->append_error_msg($error_msg, $key1, 1, "关联词中的“{$data}”，投放时间时间与第{$k2}行有交叉;");
							$this->append_error_msg($error_msg, $key2, 1, "关联词中的“{$data}”，投放时间与第{$k1}行有交叉;");
						}
					}
				}
            }
        }
        
        // 检查每一行数据是否与数据库的包名内容相冲突
        foreach($content_arr as $key=>$record) 
		{
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            // 查找热词相同的记录
            $where = array(
                'package' => array('EQ', $record['package']),
                'stat' => array('NEQ', 0),
                'begin' => array('ELT', $record['bk_end_time']),
                'end' => array('EGT', $record['bk_start_time']),
            );
            // 如果是编辑，需在后台记录中排除自己
            if (isset($record['key_id'])) {
                $where['key_id'] = array('NEQ', $record['key_id']);
            }
            $db_records = $saw->where($where)->select();
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) 
			{
                $start_time_str = date('Y-m-d H:i:s',$db_record['begin']);
                $end_time_str = date('Y-m-d H:i:s',$db_record['end']);
                $status_paused_hint = "";
                if ($db_record['stat'] == 2) 
				{
                    //$status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
					$this->append_error_msg($error_msg, $key, 1, "该记录处于已停用状态，请前往批量明细列表中操作;");
                }
                //$this->append_error_msg($error_msg, $key, 1, "与后台记录ID为【{$db_record['id']}】、包名为【{$db_record['package']}】的记录有时间冲突（其时间从【{$start_time_str}】到【{$end_time_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
	
    function logic_check_defaultkeywords($content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：获得热词表model
        $keywords_model = M("soft_defaultkeywords");
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查默认搜索关键字是否为空
            if (isset($record['key_words']) && !empty($record['key_words'])) {
            } else {
                $this->append_error_msg($error_msg, $key, 1, "默认搜索关键字不能为空;");
            }
            // 检查当前位置是否为数字
            if (isset($record['weight']) && !empty($record['weight'])) {
                if (!preg_match("/^\d+$/", $record['weight'])) {
                    $this->append_error_msg($error_msg, $key, 1, "权重应为整数;");
                } else if ($record['weight'] < 1 || $record['weight'] > 5) {
                    $this->append_error_msg($error_msg, $key, 1, "权重值应在1-5之间;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "权重不能为空;");
            }
            // 检查开始时间
            if (isset($record['start_time']) && $record['start_time'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_time'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_time']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_time']) && $record['end_time'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_time'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_time']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
        }
        
        // 检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) {
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                $k1 = $key1 + 1;
                $k2 = $key2 + 1;
                if ($record1['key_words'] == $record2['key_words']) {
                    // 时间是否交叉
                    if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                        $this->append_error_msg($error_msg, $key1, 1, "同一默认搜索关键字的显示时间与第{$k2}行有交叉;");
                        $this->append_error_msg($error_msg, $key2, 1, "同一默认搜索关键字的显示时间与第{$k1}行有交叉;");
                    }
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            // 查找热词相同的记录
            $where = array(
                'key_words' => array('EQ', $record['key_words']),
                'status' => array('NEQ', 0),
                'start_time' => array('ELT', $record['bk_end_time']),
                'end_time' => array('EGT', $record['bk_start_time']),
                'pid' => array('EQ', $record['product_id']),
            );
            // 如果是编辑，需在后台记录中排除自己
            if (isset($record['key_id'])) {
                $where['key_id'] = array('NEQ', $record['key_id']);
            }
            $db_records = $keywords_model->where($where)->select();
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) {
                $start_time_str = date('Y-m-d H:i:s',$db_record['start_time']);
                $end_time_str = date('Y-m-d H:i:s',$db_record['end_time']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "与后台记录ID为【{$db_record['key_id']}】、默认关键字为【{$db_record['key_words']}】的记录有时间冲突（其时间从【{$start_time_str}】到【{$end_time_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
    
    // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
            }
        }
        return $content_arr;
    }
    
    function import_array_convert_and_check_hotwords(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check_hotwords($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check_hotwords($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    function import_array_convert_and_check_soft_hot_words(&$content_arr) 
	{
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check_soft_hot_words($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check_soft_hot_words($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'begin','end','',1);
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        foreach($error_msg3 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    function import_array_convert_and_check_defaultkeywords(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check_defaultkeywords($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check_defaultkeywords($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    
    // 下载批量导入模版
    function down_moban($type) 
	{
        if ($type == 'hotwords') 
		{
            $file_dir = C("ADLIST_PATH") . "sousuoreci_import_moban.csv";
            $file_name = '搜索热词管理V4';
        }
		else if($type=='soft_hot_words')
		{
		    $file_dir = C("ADLIST_PATH") . "sousuoalading_import_moban.csv";
            $file_name = '搜索阿拉丁';
		}
		else 
		{
            $file_dir = C("ADLIST_PATH") . "morenguanjianzi_import_moban.csv";
            $file_name = '搜索默认关键字管理V4';
        }
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode($file_name . "批量导入模版") . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } 
		else 
		{
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }
    
    // 批量导入访问的页面节点（搜索热词V4）
    function import_softs_hotwords() {
        if ($_GET['down_moban']) {
            $this->down_moban('hotwords');
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check_hotwords($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_soft_hotwords_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array_hotwords($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("搜索热词管理v4：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs_hotwords");
        }
    }
	
	// 批量导入访问的页面节点（搜索阿拉丁）
    function import_softs_soft_hot_words() 
	{
        if ($_GET['down_moban']) {
            $this->down_moban('soft_hot_words');
        } 
		else if ($_FILES) 
		{
            $err = $_FILES["upload_file"]["error"];
            if ($err) 
			{
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") 
			{
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) 
			{
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) 
			{
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check_soft_hot_words($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) 
			{
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) 
			{
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_soft_hot_words_importing';
            $import_lock = S($lock_name);
            if ($import_lock) 
			{
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array_soft_hot_words($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) 
			{
                if ($value['flag'] == 0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("搜索阿拉丁：批量导入了{$save_file_name}。");
            if ($flag) 
			{
                $this->ajaxReturn("",'导入成功！', 0);
            } 
			else 
			{
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } 
		else 
		{
            $this->display("import_softs_soft_hot_words");
        }
    }
    
    // 批量导入访问的页面节点（默认关键字管理V4）
    function import_softs_defaultkeywords() {
        if ($_GET['down_moban']) {
            $this->down_moban('defaultkeywords');
        } 
		else if ($_FILES) 
		{
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check_defaultkeywords($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_soft_defaultkeywords_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array_defaultkeywords($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("搜索默认关键字管理v4：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs_defaultkeywords");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array_hotwords($content_arr) {
        $result_arr = array();
        $model = M('soft_hotwords');
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			$map['add_time'] = time();
            // 赋值，以下为必填的值
			$map['hot_words'] = $record['hot_words'];
			$map['location'] = $record['location'];
			$map['key_type'] = $record['key_type'];
			$map['start_time'] = strtotime($record['start_time']);
			$map['end_time'] = strtotime($record['end_time']);
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            //一下为非必填
			$map['type'] = isset($record['type']) ? $record['type'] : 0;
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('搜索关键字管理_搜索热词管理V4_添加搜索热词'.$record['hot_words'], 'sj_soft_hotwords', $id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} else {
                // 未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
			}
        }
        return $result_arr;
    }
     // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array_soft_hot_words($content_arr) {
        $result_arr = array();
        $model = M('soft_associate_hot_word');
		//var_dump($content_arr);exit;
		$AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) 
		{
            $map = array();
            // 设置默认值
			$map['stat'] = 1;
			$map['create_time'] = time();
			$map['update_time'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
			if($record['begin']=="")
			{
			  $record['begin']=date('Y-m-d H:i:s',time());
			}
			if($record['end']=="")
			{
			  $record['end']=date('Y-m-d H:i:s',time());
			}
			//$record['associate']=';' . trim($record['associate']) . ';';
			$map['package'] = trim($record['package']);
			$map['recommend'] = trim($record['recommend']);
			$map['associate'] = trim($record['associate']);
			$map['begin'] = strtotime($record['begin']);
			$map['end'] = strtotime($record['end']);
            $map['background'] ="";
			$map['publicimg'] ="";
			$map['type'] = isset($record['type']) ? $record['type'] : 0;

			$data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$record;
            	continue;
            }

			$id = $model->add($map);
			
			//同步到搜索结果中
			//搜索结果运营有关键字但是没包名，添加的软件是往该关键字中添加
			$key_arr=explode(";",trim($record['associate']));
			if($record['no_add'])
			{
				$key_arr = array_diff(array_filter($key_arr),$record['no_add']);
			}
			if($record['key_ids_words'])
			{
				foreach($record['key_ids_words'] as $k =>$v)
				{
					$map_package=array(
						'kid'=>$k,
						'status' =>1,
						'package'=>$map['package'],
						'weight'=>'',
						'pos'=>1,
						'pid'=>'',
						'type'=>0,
						'create_tm' =>time(),
						'update_tm' =>time(),
						'start_tm' =>$map['begin'],
						'stop_tm' =>$map['end'],
						'pid' =>1,
						'co_type'=>$map['type'],
						'admin_id'=>$_SESSION['admin']['admin_id'],
					);
					$result_package=$model -> table('sj_search_key_package')->add($map_package);
					if($result_package)
					{
						$this->writelog('搜索关键字管理_批量从搜索阿拉丁同步到搜索结果运营_同步关键字 软件'.$result_package.'包名为'.$map['package'],"sj_search_key_package",$result_package,__ACTION__ ,"","add");
					}
				}
				$key_diff = array_diff(array_filter($key_arr),$record['key_ids_words']);
			}
			else
			{
				$key_diff = array_filter($key_arr);
			}
			//其余关键字 添加关键字列表 同时添加关键字包名
			foreach($key_diff as $k => $val)
			{
				$map_key=array(
					'srh_key'=>$val,
					'status' =>1,
					'create_tm' =>time(),
					'update_tm' =>time(),
					'start_tm' =>$map['begin'],
					'stop_tm' =>$map['end'],
					'pid' =>1,
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result_key=$model -> table('sj_search_key')->add($map_key);
				$map_package=array(
					'kid'=>$result_key,
					'status' =>1,
					'package'=>$map['package'],
					'weight'=>'',
					'pos'=>1,
					'pid'=>'',
					'type'=>0,
					'create_tm' =>time(),
					'update_tm' =>time(),
					'start_tm' =>$map['begin'],
					'stop_tm' =>$map['end'],
					'pid' =>1,
					'co_type'=>$map['type'],
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result_package=$model -> table('sj_search_key_package')->add($map_package);
				if($result_key)
				{
					$this->writelog("搜索关键字管理_批量从搜索阿拉丁同步到搜索结果运营关键字表_同步关键字{$val}id:".$result_key,"sj_search_key",$result_key,__ACTION__ ,"","add");
				}
				if($result_package)
				{
					$this->writelog('搜索关键字管理_批量从搜索阿拉丁同步到搜索结果运营软件列表_同步关键字 软件'.$result_package.'包名为'.$map['package'],"sj_search_key_package",$result_package,__ACTION__ ,"","add");
				}
			}
			
            // 添加到表中
			if ($id) 
			{
				$this->writelog('广告批量排期管理_搜索阿拉丁_批量投放广告'.$record['recommend'],"sj_soft_associate_hot_word",$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} 
			// else
			// {
                // 未知原因添加失败
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_soft_associate_hot_word')){
        	$result_arr['table_name']='sj_soft_associate_hot_word';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
	
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array_defaultkeywords($content_arr) {
        $result_arr = array();
        $model = M('soft_defaultkeywords');
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			$map['add_time'] = time();
            // 赋值，以下为必填的值
			$map['key_words'] = $record['key_words'];
			$map['weight'] = $record['weight'];
			$map['start_time'] = strtotime($record['start_time']);
			$map['end_time'] = strtotime($record['end_time']);
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			//非必填的值
			$map['type'] = isset($record['type']) ? $record['type'] : 0;

            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('搜索关键词管理_默认关键字管理V4_添加默认搜索关键字'.$map['key_words'],"sj_soft_defaultkeywords",$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} else {
                // 未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
			}
        }
        return $result_arr;
    }
}
?>
