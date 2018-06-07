<?php 

class DownloadrecommendAction extends CommonAction{

	function recommend_list(){
		$model = new Model();
		$config_result = $model -> table('pu_config') -> where(array('config_type' => 'DOWNLOAD_RECOMMEND','status' => 1)) -> select();
		
		$soft_name = $_GET['soft_name'];
		if($soft_name){
			$soft_name_where['_string'] = "softname like '%{$soft_name}%' and status = 1 and hide = 1";
			$soft_name_result = $model -> table('sj_soft') -> where($soft_name_where) -> select();
		
			foreach($soft_name_result as $key => $val){
				$package_str_go .= "'".$val['package']."',";
			}
			$package_str = substr($package_str_go,0,-1);
			$where_go .= " and package in ({$package_str})";
		}
		$package = $_GET['package'];
		if($package){
			$where_go .= " and package = '{$package}'";
		}
		$operating_id = $_GET['operating_id'];
		if($operating_id){
			$where_go .= " and operating_id = {$operating_id}";
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $model -> table('sj_download_recommend') -> where($where) -> count();
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_download_recommend') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		
		foreach($result as $key => $val){
			$soft_result = $model -> table('sj_soft') -> where(array('package' => $val['package'],'status' => 1,'hide' => 1)) -> select();
			$val['soft_name'] = $soft_result[0]['softname'];
			$operating_name_result = $model -> table('pu_operating') -> where(array('oid' => $val['operating_id'])) -> select();
			$val['operating_name'] = $operating_name_result[0]['mname'];
			//渠道没填写 默认是0
			if($val['cid']=="0")
			{
				$val['chname'] = '';
			}
			else
			{
				//渠道填写用英文,隔开
				$cid_arr = explode(',',$val['cid']);
				$chname_go = '';
				foreach($cid_arr as $k => $v){
					//填写的渠道有通用渠道的处理
					if($v==0&&$v!='')
					{
						$chname_go .="通用,";
					}
					else
					{
						$channel_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> select();
						$chname_go .= $channel_result[0]['chname'].',';
					}
				}
				$val['chname'] = substr($chname_go,1,-2);
			}
			$soft_count_where['_string'] = "recommend_id = {$val['id']} and status = 1 and end_tm > ".time()."";
			$soft_count_result = $model -> table('sj_download_recommend_soft') -> where($soft_count_where) -> count();
			$val['soft_count'] = $soft_count_result;
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
			$lr = 10;
		}
		$operating_result = $model -> table('pu_operating') -> select();
		
		$this -> assign('operating_result',$operating_result);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('result',$result);
		$this -> assign('config_result',$config_result);
		$this -> assign('soft_name',$soft_name);
		$this -> assign('package',$package);
		$this -> assign('operating_id',$operating_id);
		$this -> display();
	}

	function add_recommend_show(){
		$model = new Model();
		$operating_result = $model -> table('pu_operating') -> select();
		$soft_names = $_GET['soft_name'];
		$packages = $_GET['package'];
		$operating_ids = $_GET['operating_id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('soft_names',$soft_names);
		$this -> assign('packages',$packages);
		$this -> assign('operating_ids',$operating_ids);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('operating_result',$operating_result);
		$this -> display();
	}

	function add_recommend_do(){
		$model = new Model();
		$soft_names = $_POST['soft_names'];
		
		if($soft_names){
			$where_go .= "/soft_name/{$soft_names}";
		}
		$packages = $_POST['packages'];
		if($packages){
			$where_go .= "/package/{$packages}";
		}
		$operating_ids = $_POST['operating_ids'];
		if($operating_ids){
			$where_go .= "/operating_id/{$operating_ids}";
		}
		$p = $_POST['p'];
		$where_go .= "/p/{$p}";
		$lr = $_POST['lr'];
		$where_go .= "/lr/{$lr}";
		$package = $_POST['package'];
		if(!$package){
			$this -> error("请填写软件包名");
		}
		$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> select();
		if(!$have_package){
			$this -> error("该软件包名不存在");
		}
		$operating_id = $_POST['operating_id'];
		$cid = $_POST['cid'];
		if(!$cid){
			$cid = 0;
		}
		if($cid == 0){
			$have_where_all['_string'] = "package = '{$package}' and status = 1";
			$have_been_result_all = $model -> table('sj_download_recommend') -> where($have_where_all) -> select();
			if($have_been_result_all){
				$this -> error("相同渠道内不能添加同一包名");
			}
			$cid_str = 0;
		}else{
			foreach($cid as $key => $val){
				$have_where['_string'] = "package = '{$package}' and (cid like '%,{$val},%' or cid = '0') and status = 1";
				$have_been_result = $model -> table('sj_download_recommend') -> where($have_where) -> select();
				if($have_been_result){
					$this -> error("相同渠道内不能添加同一包名");
				}
				$cid_str_go .= $val.',';
			}
			if($cid_str_go){
				$cid_str = ','.$cid_str_go;
			}
		}
		$data = array(
			'package' => $package,
			'operating_id' => $operating_id,
			'cid' => $cid_str,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		
		$result = $model -> table('sj_download_recommend') -> add($data);
		
		if($result){
			$this -> writelog("已添加id为{$result}的下载推荐",'sj_download_recommend',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Downloadrecommend/recommend_list'.$where_go);
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_config(){
		$model = new Model();
		$config = $_GET['config'];
		$result = $model -> table('pu_config') -> where(array('config_type' => 'DOWNLOAD_RECOMMEND','status' => 1)) -> save(array('configcontent' => $config));
		
		if($config == 1){
			$configs = "搜索结果列表";
		}elseif($config == 2){
			$configs = "通用下载列表";
		}elseif($config == 0){
			$configs = "关闭";
		}
		if($result){
			$this -> writelog("已编辑下载推荐范围为{$configs}",'pu_config','DOWNLOAD_RECOMMEND',__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function edit_recommend_show(){
		$model = new Model();
		$id = $_GET['id'];
		$soft_names = $_GET['soft_name'];
		$packages = $_GET['package'];
		$operating_ids = $_GET['operating_id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$operating_result = $model -> table('pu_operating') -> select();
		$result = $model -> table('sj_download_recommend') -> where(array('id' => $id)) -> select();
		$the_cid = substr($result[0]['cid'],1,-1);
		$chl_where['_string'] = "cid in ({$the_cid})";
		$chl_result = $model -> table('sj_channel') -> where($chl_where) -> select();
	
		$this -> assign('result',$result);
		$this -> assign('soft_names',$soft_names);
		$this -> assign('packages',$packages);
		$this -> assign('operating_ids',$operating_ids);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('operating_result',$operating_result);
		$this -> assign('chl_list',$chl_result);
		$this -> display();
	}

	function edit_recommend_do(){
		$model = new Model();
		$id = $_POST['id'];
		$soft_names = $_POST['soft_names'];
		if($soft_names){
			$where_go .= "/soft_name/{$soft_names}";
		}
		$packages = $_POST['packages'];
		if($packages){
			$where_go .= "/package/{$packages}";
		}
		$operating_ids = $_POST['operating_ids'];
		if($operating_ids){
			$where_go .= "/operating_id/{$operating_ids}";
		}
		$p = $_POST['p'];
		$where_go .= "/p/{$p}";
		$lr = $_POST['lr'];
		$where_go .= "/lr/{$lr}";
		$package = $_POST['package'];
		if(!$package){
			$this -> error("请填写软件包名");
		}
		$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> select();
		if(!$have_package){
			$this -> error("该软件包名不存在");
		}
		$operating_id = $_POST['operating_id'];
		$cid = $_POST['cid'];
		if($cid == 0){
			$have_where_all['_string'] = "package = '{$package}' and status = 1 and id != {$id}";
			$have_been_result_all = $model -> table('sj_download_recommend') -> where($have_where_all) -> select();
			if($have_been_result_all){
				$this -> error("相同渠道内不能添加同一包名");
			}
			$cid_str = 0;
		}else{
			foreach($cid as $key => $val){
				$have_where['_string'] = "package = '{$package}' and (cid like '%,{$val},%' or cid = '0') and status = 1 and id != {$id}";
				$have_been_result = $model -> table('sj_download_recommend') -> where($have_where) -> select();
				if($have_been_result){
					$this -> error("相同渠道内不能添加同一包名");
				}
				$cid_str_go .= $val.',';
			}
			if($cid_str_go){
				$cid_str = ','.$cid_str_go;
			}
		}
		
		$data = array(
			'package' => $package,
			'operating_id' => $operating_id,
			'cid' => $cid_str,
			'update_tm' => time(),
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_download_recommend',$data,$model);
		$result = $model -> table('sj_download_recommend') -> where(array('id' => $id)) -> save($data);
		
		if($result){
			$this -> writelog("已编辑id为{$id}的下载推荐".$log_result,'sj_download_recommend',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Downloadrecommend/recommend_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_recommend(){
		$model = new Model();
		$id = $_GET['id'];
		$soft_names = $_GET['soft_name'];
		if($soft_names){
			$where_go .= "/soft_name/{$soft_names}";
		}
		$packages = $_GET['package'];
		if($packages){
			$where_go .= "/package/{$packages}";
		}
		$operating_ids = $_GET['operating_id'];
		if($operating_ids){
			$where_go .= "/operating_id/{$operating_ids}";
		}
		$p = $_GET['p'];
		$where_go .= "/p/{$p}";
		$lr = $_GET['lr'];
		$where_go .= "/lr/{$lr}";
		$result = $model -> table('sj_download_recommend') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm' => time()));
		if($result){
			$this -> writelog("已删除id为{$id}的下载推荐",'sj_download_recommend',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Downloadrecommend/recommend_list'.$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}

	function soft_list(){
		$model = new Model();
		$recommend_id = $_GET['recommend_id'];
		if($_GET['my_time'] == 2){
			$where['_string'] = "recommend_id = {$recommend_id} and end_tm < ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 1){
			$where['_string'] = "recommend_id = {$recommend_id} and start_tm > ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 3 || !$_GET['my_time']){
			$where['_string'] = "recommend_id = {$recommend_id} and start_tm < ".time()." and end_tm > ".time()." and status = 1";
			$order = "start_tm";
		}
		$count = $model -> table('sj_download_recommend_soft') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_download_recommend_soft') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
	
		$util = D("Sj.Util");
		foreach($result as $key => $val){
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v)
			{
				if($v[1] == true)
				{
					$result[$key]['types'] = $v[0];
				}
			}
			$soft_name_result = $model -> table('sj_soft') -> where(array('package' => $val['package'],'status' => 1,'hide' => 1)) -> select();
			$result[$key]['soft_name'] = $soft_name_result[0]['softname'];
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 10;
		}
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('recommend_id',$recommend_id);
		$this -> assign('result',$result);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> display();
	}
	
	function add_soft_show(){
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$recommend_id = $_GET['recommend_id'];
		$this -> assign('recommend_id',$recommend_id);
		$this -> assign('typelist',$typelist);
		$this -> display();
	}
    
    function add_soft_do() {
        // 有效数据trim一下
        $column_arr = array('recommend_id', 'package', 'show', 'type', 'start_tm', 'end_tm');
        $check_column_arr = array();
        foreach ($column_arr as $column) {
            $check_column_arr[$column] = trim($_GET[$column]);
        }
        
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
        $error_msg = $this->logic_check($content_arr);
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
        $package = $check_column_arr['package'];
        $recommend_id = $check_column_arr['recommend_id'];
        $show = $check_column_arr['show'];
        $type = $check_column_arr['type'];
        $start_tm = $check_column_arr['start_tm'];
        $end_tm = $check_column_arr['end_tm'];
        $data = array(
			'package' => $package,
			'recommend_id' => $recommend_id,
			'show' => $show,
			'type' => $type,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
        $model = new Model();
        if($data['package']){
        	//屏蔽软件上排期时报警需求 新增  yuesai
	        $AdSearch = D("Sj.AdSearch");
	        $shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
				$this -> error($shield_error);
			}
        }
        
		$result = $model -> table('sj_download_recommend_soft') -> add($data);
		
		if($result){
			$this -> writelog("已添加id为{$recommend_id}的下载推荐软件id为{$result},包名为{$package},权重为{$show},合作方式为{$type},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_download_recommend_soft',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Downloadrecommend/soft_list/recommend_id/'.$recommend_id);
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
    }
	
	function edit_soft_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_download_recommend_soft') -> where(array('id'=> $id)) -> select();
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($result[0]['type']);
		$this->assign('typelist',$typelist);
		$this -> assign('result',$result);
		$this -> display();
	} 
	
	function edit_soft_do(){
		// 有效数据trim一下
        $column_arr = array('id', 'recommend_id', 'package', 'show', 'type', 'start_tm', 'end_tm','life');
        $check_column_arr = array();
        foreach ($column_arr as $column) {
            $check_column_arr[$column] = trim($_GET[$column]);
        }
        
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
        $error_msg = $this->logic_check($content_arr);
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
        $id = $check_column_arr['id'];
        $package = $check_column_arr['package'];
        $recommend_id = $check_column_arr['recommend_id'];
        $show = $check_column_arr['show'];
        $type = $check_column_arr['type'];
        $start_tm = $check_column_arr['start_tm'];
        $end_tm = $check_column_arr['end_tm'];
        if($_GET['life']==1)
		{
		  if(strtotime($end_tm)<time())
		  {
		    $this -> error("您修改的复制上线的日期还是无效日期");
		  }
		}
        $data = array(
			'package' => $package,
			'show' => $show,
			'type' => $type,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'update_tm' => time(),
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		if($data['package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
	        $AdSearch = D("Sj.AdSearch");
	        $shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
				$this -> error($shield_error);
			}
		}
		
        $model = new Model();
		$log_result = $this -> logcheck(array('id' => $id),'sj_download_recommend_soft',$data,$model);
		 if($_GET['life']==1)
		{
		   $been_result = $model -> table('sj_download_recommend_soft') -> where(array('id' => $id)) -> select();
		   $data['create_tm']=time();
	       $data['recommend_id']=$been_result[0]['recommend_id'];
		   $result = $model -> table('sj_download_recommend_soft') -> add($data);
			if($result){
				$this -> writelog("已复制上线package为{$package}的下载推荐软件".$log_result,'sj_download_recommend_soft',$result,__ACTION__ ,"","add");
				$this -> assign('jumpUrl','/index.php/Sj/Downloadrecommend/soft_list/recommend_id/'.$been_result[0]['recommend_id']);
				$this -> success("复制上线成功");
			}else{
				$this -> error("复制上线失败");
			}
		}
		else
		{
			$result = $model -> table('sj_download_recommend_soft') -> where(array('id' => $id)) -> save($data);
			$been_result = $model -> table('sj_download_recommend_soft') -> where(array('id' => $id)) -> select();
			if($result){
				$this -> writelog("已编辑id为{$id}的下载推荐软件".$log_result,'sj_download_recommend_soft',$id,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl','/index.php/Sj/Downloadrecommend/soft_list/recommend_id/'.$been_result[0]['recommend_id']);
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
	}
	
	function del_soft(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_download_recommend_soft') -> where(array('id' => $id)) -> select();
		$result = $model -> table('sj_download_recommend_soft') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm' =>time()));
		if($result){
			$this -> writelog("已删除id为{$id}的下载推荐软件",'sj_download_recommend_soft',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Downloadrecommend/soft_list/recommend_id/'.$been_result[0]['recommend_id']);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
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
    
    function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_tm','end_tm');
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
    
    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0...
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'recommend_id'     =>  '广告位包名ID',
            'recommend_package'   =>  '广告位包名',//批量导入时才有此列，防止填错
            'package'  =>   '推荐软件包名',
            'show'  =>   '权重',
            'type'  =>   '合作方式',
            'start_tm'  =>   '开始时间',
            'end_tm'  =>   '结束时间',
			'operating' =>'运营商', //推荐广告位id不存在的时候 新增广告位和广告位的包名 
			'channel' =>'渠道',//用到运营商和渠道  add by shitingting 2016-1-19
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
		
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
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
                if ($r_key == 'start_tm' || $r_key == 'end_tm') {
                    if ($r_key == 'start_tm') {
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
    function logic_check($content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：
        $recommend_model = M('download_recommend');
        $recommend_soft_model = M('download_recommend_soft');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $recommend_soft_model->where($where)->find();
                $content_arr[$key]['recommend_id'] = $find['recommend_id'];
                $record['recommend_id'] = $find['recommend_id'];
            }
            // 检查推荐位包名ID是否有效
            if (isset($record['recommend_id']) && $record['recommend_id'] != "") {
                $where = array(
                    'id' => array('EQ', $record['recommend_id']),
                    'status' => array('EQ', 1),
                );
                $find = $recommend_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "推荐位包名id不存在;");
                } else {
                    $content_arr[$key]['bk_recommend_id'] = $record['recommend_id'];
                    // 如果传了推荐位包名，还需要检查与recommend_id是否匹配
                    if (isset($record['recommend_package'])) {
                        if ($find['package'] != $record['recommend_package']) {
                            $this->append_error_msg($error_msg, $key, 1, "推荐位包名id与推荐位包名不对应;");
                        }
                    }
                }
            } else {
				//没有广告位包名id  有广告位id的 ID必须存在
				$have_no_cid=false;
				$recommend_where['package'] =  $record['recommend_package'];
				$recommend_where['status'] = 1;
				//有渠道  判断渠道名称是否正确  然后判断渠道下面的包是否存在 存在广告位id必填
				//没有的话就可以添加 广告位和广告位的包
				if($record['channel'])
				{
					$chname_arr=explode(',',$record['channel']);
					foreach($chname_arr as $k =>$val)
					{
						$channel_where=array(
							'chname' =>trim($val),
							'status' =>1,
						);
						//根据渠道名称获取渠道id
						$cid_result = $recommend_model->table('sj_channel')->where($channel_where)->find();
						if($cid_result)
						{
							$have_where['_string'] = "package = '{$record['recommend_package']}' and (cid like '%,{$cid_result['cid']},%' or cid = '0') and status = 1";
							$have_result = $recommend_model->where($have_where)->select();
							if($have_result)
								break;
						}
						else
						{
							$have_no_cid=true;
							break;
						}
					}
				}
				else
				{
					$have_result = $recommend_model->where($recommend_where)->select();
				}
				if($have_no_cid)
				{
					$this->append_error_msg($error_msg, $key, 1, "填写的渠道名称【{$record['channel']}】错误;");
				}
				if($have_result)
				{
					$this->append_error_msg($error_msg, $key, 1, "广告位已存在，广告位ID必填;");//广告位ID没写，但是广告位包名已经存在
				}
				//有运营商  判断运营商填写的是否正确
				if($record['operating'])
				{
					$oid_result = $recommend_model->table('pu_operating')->where(array('mname'=>trim($record['operating'])))->find();
					if(!$oid_result)
					{
						$this->append_error_msg($error_msg, $key, 1, "填写的运营商【{$record['operating']}】错误;");
					}
				}
                //$this->append_error_msg($error_msg, $key, 1, "推荐位包名id不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if (isset($record['package']) && $record['package'] != "") {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    //'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
            // 检查权重填写的是不是数字
            if (isset($record['show'])) {
                if ($record['show'] == '') {
                    $this->append_error_msg($error_msg, $key, 1, "权重不能为空;");
                } else if (!preg_match("/^\d+$/", $record['show'])) {
                    $this->append_error_msg($error_msg, $key, 1, "权重格式不对，请填写小于100的整数;");
                } else {
                    // 检查大小
                    if ($record['show'] > 100 || $record['show'] <= 0) {
                        $this->append_error_msg($error_msg, $key, 1, "请填写小于100的整数;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "请填写权重;");
            }
            // 检查开始时间
            if (isset($record['start_tm']) && $record['start_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_tm']);
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
            if (isset($record['end_tm']) && $record['end_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_tm']);
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
        
        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) {
            // 如果填写时间的不完善，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果专题id不相等，则不比较
                if ($record1['recommend_id'] != $record2['recommend_id'])
                    continue;
				// 广告位包名不相等，则不比较
                if ($record1['recommend_package'] != $record2['recommend_package'])
                    continue;
                // 如果填写时间的不完善，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
				//新增加广告区间有影响
				$k1 = $key1 + 1; $k2 = $key2 + 1;
				if(!$record1['recommend_id']&&!$record2['recommend_id'])
				{
					if($record1['channel']!=$record2['channel'])
					{
						$this->append_error_msg($error_msg, $key1, 1, "新增同一推荐广告位的渠道与第{$k2}行不一样;");
						$this->append_error_msg($error_msg, $key2, 1, "新增同一推荐广告位的渠道与第{$k1}行不一样;");
					}
					if($record1['operating']!=$record2['operating'])
					{
						$this->append_error_msg($error_msg, $key1, 1, "新增同一推荐广告位的运营商与第{$k2}行不一样;");
						$this->append_error_msg($error_msg, $key2, 1, "新增同一推荐广告位的运营商与第{$k1}行不一样;");
					}
				}
				// 包名不相同
                if ($record1['package'] != $record2['package'])
                    continue;
                // 时间是否交叉
                if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key1, 1, "投放时间区间与第{$k2}行有重叠;");
                    $this->append_error_msg($error_msg, $key2, 1, "投放时间区间与第{$k1}行有重叠;");
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            if (!isset($record['bk_recommend_id']))
                continue;
            // 业务逻辑：如果填写时间的不完善，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            $start_time = $record['bk_start_time'];
            $end_time = $record['bk_end_time'];
            $where = array(
                'package' => array('EQ', $record['package']),
                'status' => array('NEQ', 0),
                'recommend_id' => array('EQ', $record['recommend_id']),
                'start_tm' => array('ELT', $end_time),
                'end_tm' => array('EGT', $start_time),
            );
            if (isset($record['id'])&&$record['life']!=1) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $db_records = $recommend_soft_model->where($where)->select();
            // 有冲突的后台记录
            foreach($db_records as $db_key=>$db_record) {
                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "投放区间与ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
    
    // 下载批量导入模版
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "xiazaituijian_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('下载推荐批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }
    
    // 批量导入访问的页面节点（只是批量导入装机必备的软件）
    function import_softs() {
        if ($_GET['down_moban']) {
            $this->down_moban();
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
            $error_msg = $this->import_array_convert_and_check($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_feature_soft_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $fail_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($fail_arr as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("下载推荐：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($fail_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $fail_arr = array();
        $model = M('download_recommend_soft');
        $AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
            $map['create_tm'] = time();
            $map['update_tm'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
			if($record['recommend_id'])
			{
				$map['recommend_id'] = $record['recommend_id'];
			}
			else
			{
				//判断广告位是否已经存在广告位包名
				//新增广告位以及包名 运营商和渠道
				if($record['channel'])
				{
					$chname_arr=explode(',',$record['channel']);
					$cid_str=",";
					foreach($chname_arr as $key =>$val)
					{
						$channel_where=array(
							'chname' =>trim($val),
							'status' =>1,
						);
						//根据渠道名称获取渠道id
						$cid_result = $model->table('sj_channel')->where($channel_where)->find();
						$cid_str.=$cid_result['cid'].',';
						$have_where['_string'] = "package = '{$record['recommend_package']}' and (cid like '%,{$cid_result['cid']},%' or cid = '0') and status = 1";
						$have_result = $model->table('sj_download_recommend')->where($have_where)->find();
						if($have_result)
						{
							break;
						}
					}
				}
				else
				{
					$cid_str=0;
					$recommend_where['package'] =  $record['recommend_package'];
					$recommend_where['status'] = 1;
					$have_result = $model->table('sj_download_recommend')->where($recommend_where)->find();
				}
				if($have_result)
				{
					$map['recommend_id'] = $have_result['id'];
				}
				else
				{
					//有运营商  判断运营商填写的是否正确
					if($record['operating'])
					{
						$oid_result = $model->table('pu_operating')->where(array('mname'=>trim($record['operating'])))->find();
						$oid_str=$oid_result['oid'];
					}
					else
					{
						$oid_str=0;
					}
					$data=array(
						'package'=>$record['recommend_package'],
						'operating_id'=>$oid_str,
						'cid'=>$cid_str,
						'create_tm'=>time(),
						'update_tm'=>time(),
						'status'=>1,
					);
				
					$recommend_id = $model->table('sj_download_recommend')->add($data);
					if($recommend_id)
					{
						$this->writelog("已添加id为{$recommend_id}的下载推荐",'sj_download_recommend',$recommend_id,__ACTION__ ,"","add");
						$map['recommend_id'] = $recommend_id;
					}
				}
			}
			
			//$map['recommend_id'] = $record['recommend_id'];
			$map['package'] = $record['package'];
            $map['show'] = $record['show'];
			$map['start_tm'] = strtotime($record['start_tm']);
			$map['end_tm'] = strtotime($record['end_tm']);

			
            // 以下为非必须的值
            if (isset($record['type'])){
                $map['type'] = $record['type'];
            }
            $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$fail_arr[$key]=array('flag'=>1,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog("推荐管理-专题软件列表-添加了ID为[{$id}]包名为{$map['package']}的专题软件",'sj_download_recommend_soft',$id,__ACTION__ ,"","add");
                $fail_arr[$key]['flag'] = 0;
                $fail_arr[$key]['msg'] = "添加成功";
			} 
			// else {
                // $fail_arr[$key]['flag'] = 1;
                // $fail_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_download_recommend_soft')){
        	$fail_arr['table_name']='sj_download_recommend_soft';
        	$fail_arr['filename']=$file_data['filename'];
        }
        return $fail_arr;
    }
    
}
