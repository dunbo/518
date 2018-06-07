<?php

class SplashmanageAction extends CommonAction{


	function splash_list()
	{
		$model = new Model();
		if($_GET['platform']){
			$platform = $_GET['platform'];
		}else{
			$platform = 1;
		}
		if($_GET['my_time'] == 1){
			$where['_string'] = "platform = {$platform} and start_tm > ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 2){
			$where['_string'] = "platform = {$platform} and end_tm < ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 3  || !$_GET['my_time']){
			$_GET['my_time'] = 3;
			$where['_string'] = "platform = {$platform} and start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			$order = "start_tm";
		}
		$count = $model -> table('sj_splash_manage') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_splash_manage') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
		//合作形式
		$util = D("Sj.Util"); 
		foreach($result as $key => $val)
		{
			if ($val['content_type'] == 2) {
                // 活动名称
                $val['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($val['activity_id']);
            } else if ($val['content_type'] == 3) {
                // 专题名称
                $val['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($val['feature_id']);
            } else if ($val['content_type'] == 4) {
                // 页面名称
                $val['page_name'] = ContentTypeModel::convertPageType2PageName($val['page_type']);
            }
			$result[$key] = $val;
			
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($val['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$result[$key]['co_types'] = $v[0];
				}
			}
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

		//百度下拉效果
		if($this->isAjax())
		{
			$keyword = $_GET['query'];
			$ad_type = $_GET['ad_type'];

			if($ad_type == 1){
				$key_result =$model->query("select name from sj_soft where status=1 and hide = 1 and package like '%{$keyword}%'");
			}elseif($ad_type == 2){
				$key_result =$model->query("select name from sj_activity where status=1 and name like '%{$keyword}%'");

			}elseif($ad_type == 3){
				$key_result =$model->query("select name from sj_feature where status=1 and name like '%{$keyword}%'");
			}elseif($ad_type == 4){
				$key_result =$model->query("select name from sj_market_skip where status=1 and page like '%{$keyword}%'");
			}
			$data = array(
					'query' => $keyword,
					'suggestions' => array(),
			);
			foreach($key_result as $v) {
					$data['suggestions'][] = $v['name'];
			}
			exit(json_encode($data));
		}

		if($platform == 1){
			$the_time = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_MARKET','status' => 1)) -> select();
		}elseif($platform == 4){
			$the_time = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_HD','status' => 1)) -> select();
		}elseif($platform == 5){
			$the_time = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_GAME','status' => 1)) -> select();
		}elseif($platform == 9){
			$the_time = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_SDK','status' => 1)) -> select();
		}elseif($platform == 10){
			$the_time = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_FORUM','status' => 1)) -> select();
		}
		
		$this -> assign('platform',$platform);
		$this -> assign('the_time',$the_time);
		$this -> assign('result',$result);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> display();

	}

	function show_channel(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_splash_manage') -> where(array('id' =>$id)) -> find();
		$cid_arr = explode(',',$result['cid']);
		foreach($cid_arr as $k => $v){
			$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> find();
			$chname_str_go .= $chname_result['chname'].',';
		}
		$chname_str = substr($chname_str_go,0,-1);
		$this -> assign('chname_str',$chname_str);

		$this -> display();

	}

	function edit_splash_time_show(){
		$model = new Model();

		$platform = $_GET['platform'];
		if($platform == 1){
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_MARKET','status' => 1)) -> select();
		}elseif($platform == 4){
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_HD','status' => 1)) -> select();
		}elseif($platform == 5){
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_GAME','status' => 1)) -> select();
		}elseif($platform == 9){
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_SDK','status' => 1)) -> select();
		}elseif($platform == 10){
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_FORUM','status' => 1)) -> select();
		}
		$this -> assign('platform',$platform);
		$this -> assign('result',$result);
		$this -> display();
	}

	function edit_splash_time(){
		$model = new Model();
		$the_time = $_GET['the_time'];
		if(!$the_time){
			$this -> error("闪屏时间不能为0");
		}
		$platform = $_GET['platform'];
		if(!is_numeric($the_time) || $the_time < 0){
			$this -> error("请填写正整数");
		}
		$config_type="";
		if($platform == 1){
			$config_type='SPLASH_TIME_MARKET';
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_MARKET','status' => 1)) -> save(array('configcontent'=>$the_time, 'uptime'=>time()));
		}elseif($platform == 4){
			$config_type='SPLASH_TIME_HD';
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_HD','status' => 1)) -> save(array('configcontent'=>$the_time, 'uptime'=>time()));
		}elseif($platform == 5){
			$config_type='SPLASH_TIME_GAME';
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_GAME','status' => 1)) -> save(array('configcontent'=>$the_time, 'uptime'=>time()));
		}elseif($platform == 9){
			$config_type='SPLASH_TIME_SDK';
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_SDK','status' => 1)) -> save(array('configcontent'=>$the_time, 'uptime'=>time()));
		}elseif($platform == 10){
			$config_type='SPLASH_TIME_FORUM';
			$result = $model -> table('pu_config') -> where(array('config_type' => 'SPLASH_TIME_FORUM','status' => 1)) -> save(array('configcontent'=>$the_time, 'uptime'=>time()));
		}

		if($result){
			$this -> writelog("已修改闪屏显示时间为{$the_time}",'pu_config',"config_type:{$config_type}",__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Splashmanage/splash_list/platform/'.$platform);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}

	}

	function add_splash_show(){
		$model = new Model();
		$platform = $_GET['platform'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$splash_type = $_GET['splash_type'];
		$platform = $_GET['platform'];
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);
		
		$this -> assign('splash_type',$splash_type);
		$this -> assign('platform',$platform);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> display();
	}


	function add_splash_do(){
		$model = new Model();
		$platform = $_POST['platform'];
		$data['platform'] = $platform;
		$splash_type = $_POST['splash_type'];
		if(!$splash_type){
			$this -> error("请选择广告闪屏类型");
		}
		$data['splash_type'] = $splash_type;
		$pic_type = $_POST['pic_type'];
		$data['pic_type'] = $pic_type;
		$splash_name = trim($_POST['splash_name']);
		if(!$splash_name){
			$this -> error("请填写闪屏名称");
		}
		$data['splash_name'] = $splash_name;
		//合作形式
		if(isset($_POST['co_type'])){		
			$data['co_type'] = $_POST['co_type'];	
		}else{		
			$data['co_type'] = 0;	
		}
		//V6.0精准投放
		//处理上传csv
		$filename=$_FILES['upload_file']['name'];
		if(!$filename&&!$_POST['csv_count'])
		{
			$data['csv_count'] = 0;
			$data['csv_url'] = "";
			$data['is_upload_csv'] = 0;
		}
		if($filename&&!$_POST['csv_count'])
		{
			$this -> error("选择好的文件请点击上传才有效");
		}
		if($_POST['csv_count']&&$_POST['csv_url'])
		{
			$data['csv_count'] = $_POST['csv_count'];
			$data['csv_url'] = $_POST['csv_url'];
			$data['is_upload_csv'] = 1;
		}
		unset($_FILES['upload_file']);
		//渠道id和机型id
		$channel_id_array=$_POST['cid'];
		$cids = array_unique($channel_id_array);
		if (count($cids) > 0) 
		{
			$s = implode(',', $cids);
			$s = ",{$s},";
			$data['cid'] = $s;
		}
		else
		{
			$data['cid'] = ",,";
		}

		$device_did_array=$_POST['did'];
		$dids = array_unique($device_did_array);
		if (count($dids) > 0) 
		{
			$d= implode(',', $dids);
			$d = ",{$d},";
			$data['device_did'] = $d;
		}
		else
		{
			$data['device_did'] = ",,";
		}
		//运营商和固件版本和市场版本
		$data['oid'] = ','. implode(',', $_POST['oid']). ',';
		$data['firmware'] = ','. implode(',', $_POST['firmware']). ',';
		$data['version_code'] = ','. implode(',',$_POST['version_code']). ',';
		
		$pic_url = $_FILES['pic_url'];
		$logo_url = $_FILES['logo_url'];
		$path = date('Ym/d');
		// 图片不能为空 649更改为选填
		if (!$_FILES['pic_url']['name'])
			$this->error("请上传图片");
		// 取得图片后缀
		if($_FILES['pic_url']['name']){
			$suffix = preg_match("/\.(jpg|png)$/", $_FILES['pic_url']['name'],$matches);
			if ($matches) {
				$suffix = $matches[0];
			} else {
				$this->error('上传图片格式错误！');
			}
			// 判断图片长和宽
			$img_info_arr = getimagesize($_FILES['pic_url']['tmp_name']);
			if (!$img_info_arr) {
				$this->error('上传图片出错！');
			}
			$width = $img_info_arr[0];
			$height = $img_info_arr[1];
			if ($width != 480 || $height != 960)
				$this->error("上传图片大小错误，宽需为480px，高需为960px");
			// 将图片存储起来
			$folder = "/img/" . date("Ym/d/");
			$this->mkDirs(UPLOAD_PATH . $folder);
			$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
			$img_path = UPLOAD_PATH . $relative_path;
			$ret = move_uploaded_file($_FILES['pic_url']['tmp_name'], $img_path);
			$data['pic_url'] = $relative_path;
		}

		$this->splash_pub('add' ,$data, $_POST);
		if($pic_type ==  2)
		{
			if($logo_url['size'])
			{
				$ret = move_uploaded_file($_FILES['logo_url']['tmp_name'], $img_path);
				$data['logo_url'] = $relative_path;
			}
		}

		$data['show_rate'] = $_POST['show_rate'];
		$data['count_down'] = $_POST['count_down'];

		if($splash_type == 2){
			$data['jump'] = 1;
		}else{
			$data['jump'] = $_POST['jump'];
		}
				
		$start_tm = date('Y-m-d H:i:s',strtotime($_POST['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_POST['end_tm']));
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请选择开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		/* if($cid){

			foreach($cid as $key => $val){
				$have_been_where['_string'] = "platform = {$platform} and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				$have_been_result = $model -> table('sj_splash_manage') -> where($have_been_where) -> select();

				if($have_been_result){
					$this -> error("该平台该渠道该时间段内已存在该闪屏");
				}
	
			}
			
		}else{
			$have_been_where['_string'] = "platform = {$platform} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			$have_been_result = $model -> table('sj_splash_manage') -> where($have_been_where) -> select();
			if($have_been_result){
				$this -> error("该平台该渠道该时间段内已存在该闪屏");
			}
		} */
		$content_type = $_POST['content_type'];
		$data['content_type'] = $content_type;
		
		if($splash_type == 2)
		{
			//推荐内容处理 合并
            $rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$data);
			if($rcontent !==true)
			{
				$this -> error($rcontent);
			}
		}

		$data['start_tm'] = strtotime($start_tm);
		$data['end_tm'] = strtotime($end_tm);
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$data['admin_id'] = $_SESSION['admin']['admin_id'];

		$parameter_field=json_decode($data['parameter_field'],true);
		
		$parameter_field['cover_user_type']=trim($_POST['cover_user_type'])?trim($_POST['cover_user_type']):0;
        if($parameter_field['cover_user_type']==1){
        	$parameter_field['activation_date_start']=0;
        	$parameter_field['activation_date_end']=0;
        }elseif($parameter_field['cover_user_type']==2){
        	if(!$_POST['activation_date_start'] || !$_POST['activation_date_end']){
        		$this->error("选择定向用户时，激活日期必填！");
        	}else{
        		if(strtotime(trim($_POST['activation_date_start']))>=strtotime(trim($_POST['activation_date_end']))){
        			$this->error("激活结束日期必须大于开始日期！");
        		}
        	}
        	$parameter_field['activation_date_start']=strtotime(trim($_POST['activation_date_start']));
        	$parameter_field['activation_date_end']=strtotime(trim($_POST['activation_date_end']));
        }else{
        	$parameter_field['activation_date_start']=0;
        	$parameter_field['activation_date_end']=0;
        }



		$data['parameter_field'] = json_encode($parameter_field);


		if($_POST['package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
	        $AdSearch = D("Sj.AdSearch");
	        $shield_error=$AdSearch->check_shield($_POST['package'],$data['start_tm'],$data['end_tm']);
	        if($shield_error){
	            $this -> error($shield_error);
	        }  
		}                    
		$result = $model -> table('sj_splash_manage') -> add($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已添加闪屏id为{$result},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_splash_manage',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/splash_list/platform/{$platform}/p/{$p}/lr/{$lr}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}

	/**
	 * @desc 闪屏添加编辑公共
	 * @param $type add 添加 edit编辑
	 * @param $data
	 */
	function splash_pub($type ,&$data, $post){
		$data['show_az_banner '] = $post['show_az_banner'];
		// 图片不能为空
		$pic = 'pic_url1';
//		if($type == 'add'){
//			if (!$_FILES[$pic]['name'])
//				$this->error("请上传图片2");
//		}
		if($_FILES[$pic]['name']){
			// 取得图片后缀
			$suffix = preg_match("/\.(jpg|png)$/", $_FILES['pic_url1']['name'],$matches);
			if ($matches) {
				$suffix = $matches[0];
			} else {
				$this->error('上传图片2格式错误！');
			}
			// 判断图片长和宽
			$img_info_arr = getimagesize($_FILES[$pic]['tmp_name']);
			if (!$img_info_arr) {
				$this->error('上传图片2出错！');
			}
			$width = $img_info_arr[0];
			$height = $img_info_arr[1];
			if ($width != 480 || $height != 960)
				$this->error("上传图片2大小错误，宽需为480px，高需为960px");
			// 将图片存储起来
			$folder = "/img/" . date("Ym/d/");
			$this->mkDirs(UPLOAD_PATH . $folder);
			$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
			$img_path = UPLOAD_PATH . $relative_path;
			move_uploaded_file($_FILES[$pic]['tmp_name'], $img_path);
			$data[$pic] = $relative_path;
		}
	}

	function edit_splash_show()
	{
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_splash_manage') -> where(array('id' => $id)) -> select();
		$parameter_field=json_decode($result[0]['parameter_field'],true);

        $result[0]['cover_user_type']=$parameter_field['cover_user_type'];
        $result[0]['activation_date_end']=$parameter_field['activation_date_end'];
        $result[0]['activation_date_start']=$parameter_field['activation_date_start'];
		//渠道
		$channel_model = M('channel');
		$cookstr = preg_replace('/^,/','',$result[0]['cid']);
		$cookstr = preg_replace('/,$/','',$cookstr);
		$array = explode(',', $cookstr);
		$chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
		if (in_array("0",$array)&&$chl!=NULL)
		{
		  $tong = array("cid"=> "0" ,"chname"=> "通用");
		  array_unshift($chl, $tong);
		}
		if (in_array("0",$array)&&$chl==NULL)
		{
		  $chl[0]['cid']="0";
		  $chl[0]['chname']="通用";
		}
		//机型
		if (strlen($result[0]['device_did']) > 0)
		{
			$device_selected = explode(',', $result[0]['device_did']);
			$device_selected_ret = array();
			foreach ($device_selected as $ds) 
			{
				if (empty($ds)) continue;
				$device_name = $model->table("pu_device")->where(array('did' => $ds))->field('did,dname')->select();
				$device_selected_ret[] = array('did' => $ds,'dname' => $device_name[0]['dname']);
			}
			$this->assign('device_selected', $device_selected_ret);
		}
		//固件、市场版本、运营商
		$util = D('Sj.Util');
		$this->assign('firmwarelist', $util->getFirmwareList(explode(',', $result[0]['firmware'])));
		$this->assign('version_list', $util->getMarketVersion(explode(',', $result[0]['version_code'])));
		$this->assign('operator_list', $util->getOperators(explode(',', $result[0]['oid'])));
				
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		foreach($result as $key => $val){
			$activity_result = $model -> table('sj_activity') -> where(array('id' => $val['activity_id'])) -> select();
			$val['activity_name'] = $activity_result[0]['name'];
			$feature_result = $model -> table('sj_feature') -> where(array('feature_id' => $val['feature_id'])) -> select();
			$val['feature_name'] = $feature_result[0]['name'];
			$result[$key] = $val;
		}
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($result[0]['co_type']);
		$this->assign('typelist',$typelist);
		$this->assign('chl_list', $chl);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('result',$result);
		// echo "<pre>";var_dump($result);die;
		$this -> display();
	}

	function edit_splash_do()
	{
		$model = new Model();
		$id = $_POST['id'];
		$splash_name = trim($_POST['splash_name']);
		if(!$splash_name){
			$this -> error("请填写闪屏名称");
		}
		$data['splash_name'] = $splash_name;
		//合作形式
		if(isset($_POST['co_type'])){
			$data['co_type'] = $_POST['co_type'];
		}else{
			$data['co_type'] = 0;
		}
		//V6.0精准投放
		//处理上传csv
		$filename=$_FILES['upload_file']['name'];
		if(!$filename&&!trim($_POST['csv_count'])&&trim($_POST['have_pre_dl']))
		{
			$data['csv_count'] = trim($_POST['pre_dl_count']);
			$data['csv_url'] = trim($_POST['have_pre_dl']);
			$data['is_upload_csv'] = 1;
		}
		if(!$filename&&!$_POST['csv_url']&&!trim($_POST['have_pre_dl']))
		{
			$data['csv_count'] = 0;
			$data['csv_url'] = "";
		}
		if($filename&&!$_POST['csv_count'])
		{
			$this -> error("选择好的文件请点击上传才有效");
		}
		if(trim($_POST['csv_url'])&&trim($_POST['csv_count']))
		{
			$data['csv_count'] = $_POST['csv_count'];
			$data['csv_url'] = $_POST['csv_url'];
			$data['is_upload_csv'] = 1;
		}
		unset($_FILES['upload_file']);
		
		//渠道id和机型id
		$channel_id_array=$_POST['cid'];
		$cids = array_unique($channel_id_array);
		if (count($cids) > 0) {
			$s = implode(',', $cids);
			$s = ",{$s},";
			$data['cid'] = $s;
		}
		else
		{
			$data['cid'] = ",,";
		}
		$device_did_array=$_POST['did'];
		$dids = array_unique($device_did_array);
		if (count($dids) > 0) 
		{
			$d= implode(',', $dids);
			$d = ",{$d},";
			$data['device_did'] = $d;
		}
		else
		{
			$data['device_did'] = ",,";
		}
		//运营商和固件版本和市场版本
		$data['oid'] = ','. implode(',', $_POST['oid']). ',';
		$data['firmware'] = ','. implode(',', $_POST['firmware']). ',';
		$data['version_code'] = ','. implode(',', $_POST['version_code']). ',';
		
		//用move_uploaded_file上传图片
		$pic_url = $_FILES['pic_url'];
		$logo_url = $_FILES['logo_url'];
		
		if($_FILES['pic_url']['name'])
		{
			// 取得图片后缀
			$suffix = preg_match("/\.(jpg|png)$/", $_FILES['pic_url']['name'],$matches);
			if ($matches) {
				$suffix = $matches[0];
			} else {
				$this->error('上传图片格式错误！');
			}
			// 判断图片长和宽
			$img_info_arr = getimagesize($_FILES['pic_url']['tmp_name']);
			if (!$img_info_arr) {
				$this->error('上传图片出错！');
			}
			$width = $img_info_arr[0];
			$height = $img_info_arr[1];
			if ($width != 480 || $height != 960)
				$this->error("上传图片大小错误，宽需为480px，高需为960px");
			// 将图片存储起来
			$folder = "/img/" . date("Ym/d/");
			$this->mkDirs(UPLOAD_PATH . $folder);
			$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
			$img_path = UPLOAD_PATH . $relative_path;
			$ret = move_uploaded_file($_FILES['pic_url']['tmp_name'], $img_path);
			$data['pic_url'] = $relative_path;			
		}
		if($pic_type ==  2)
		{
			if($logo_url['size'])
			{
				$ret = move_uploaded_file($_FILES['logo_url']['tmp_name'], $img_path);
				$data['logo_url'] = $relative_path;
			}
		}		
		$been_result = $model -> table('sj_splash_manage') -> where(array('id' => $id)) -> select();
		$ad_type = $been_result[0]['ad_type'];
		
		if($been_result[0]['splash_type'] == 1){
			$data['jump'] = $_POST['jump'];
		}else{
			$data['jump'] = 1;
		}
		$this->splash_pub('edit' ,$data, $_POST);
		$data['show_rate'] = $_POST['show_rate'];
		$data['count_down'] = $_POST['count_down'];

		$start_tm = date('Y-m-d H:i:s',strtotime($_POST['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_POST['end_tm']));
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请选择开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($_POST['life']==1)
		{
			if(strtotime($end_tm)<time())
			{
			   $this->error("您修改的复制上线的日期还是无效日期");
			}
		}
		$data['start_tm'] = strtotime($start_tm);
		$data['end_tm'] = strtotime($end_tm);
        $data['update_tm']=time();
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$have_been = $model -> table('sj_splash_manage') -> where(array('id' => $id)) -> select();
		/* if($cid){
			foreach($cid as $key => $val){
				$have_been_where['_string'] = "id != {$id} and platform = {$have_been[0]['platform']} and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				$have_been_result = $model -> table('sj_splash_manage') -> where($have_been_where) -> select();
				if($have_been_result){
					$this -> error("该平台该渠道该时间段内已存在该闪屏");
				}

			}
		}else{
			$have_been_where['_string'] = "id != {$id} and platform = {$have_been[0]['platform']} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			$have_been_result = $model -> table('sj_splash_manage') -> where($have_been_where) -> select();
			if($have_been_result){
				$this -> error("该平台该渠道该时间段内已存在该闪屏");
			}

		} */

		$content_type = $_POST['content_type'];
		$data['content_type'] = $content_type;
		$data['platform'] = $_POST['platform']?$_POST['platform']:1;
		if($been_result[0]['splash_type'] == 2)
		{
			//推荐内容处理 合并
            $rcontent=ContentTypeModel::saveRecommendContent($_POST,$content_type,$data);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
		}

		$parameter_field=json_decode($data['parameter_field'],true);
		
		$parameter_field['cover_user_type']=trim($_POST['cover_user_type'])?trim($_POST['cover_user_type']):0;
        if($parameter_field['cover_user_type']==1){
        	$parameter_field['activation_date_start']=0;
        	$parameter_field['activation_date_end']=0;
        }elseif($parameter_field['cover_user_type']==2){
        	if(!$_POST['activation_date_start'] || !$_POST['activation_date_end']){
        		$this->error("选择定向用户时，激活日期必填！");
        	}else{
        		if(strtotime(trim($_POST['activation_date_start']))>=strtotime(trim($_POST['activation_date_end']))){
        			$this->error("激活结束日期必须大于开始日期！");
        		}
        	}
        	$parameter_field['activation_date_start']=strtotime(trim($_POST['activation_date_start']));
        	$parameter_field['activation_date_end']=strtotime(trim($_POST['activation_date_end']));
        }else{
        	$parameter_field['activation_date_start']=0;
        	$parameter_field['activation_date_end']=0;
        }

		$data['parameter_field'] = json_encode($parameter_field);

		$log_result = $this -> logcheck(array('id' => $id),'sj_splash_manage',$data,$model);
        if($_POST['package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
	        $AdSearch = D("Sj.AdSearch");
	        $shield_error=$AdSearch->check_shield($_POST['package'],$data['start_tm'],$data['end_tm']);
	        if($shield_error){
	            $this -> error($shield_error);
	        }
        }
		

		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$my_time = $_POST['my_time'];
		if($_POST['life']==1)
		{
		    $select =$model->table('sj_splash_manage') -> where(array('id' => $id)) -> select();
			if($_FILES['pic_url']['size']=="")
			{
			$data['pic_url']=$select[0]['pic_url'];
			$data['logo_url']=$select[0]['logo_url'];
			}
			$data['splash_type']=$select[0]['splash_type'];
			$data['create_tm']=time();
			$result = $model -> table('sj_splash_manage') -> add($data);
			if($result){
				$this -> writelog("已复制上线id为复制上线{$id}的闪屏,".$log_result,'sj_splash_manage',$result,__ACTION__ ,"","add");
				$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/splash_list/platform/{$have_been[0]['platform']}/p/{$p}/lr/{$lr}/my_time/{$my_time}/");
				$this -> success("复制上线成功");
			}else{
				$this -> error("复制上线失败");
			}
		}
		else
		{
		    $result = $model -> table('sj_splash_manage') -> where(array('id' => $id)) -> save($data);
			if($result){
				$this -> writelog("已编辑id为{$id}的闪屏,".$log_result,'sj_splash_manage',$id,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/splash_list/platform/{$have_been[0]['platform']}/p/{$p}/lr/{$lr}/my_time/{$my_time}/");
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}

	}


	function del_splash(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$been_result = $model -> table('sj_splash_manage') -> where(array('id' => $id)) -> select();
		$result = $model -> table('sj_splash_manage') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
		$my_time = $_GET['my_time'];
		if($result){
			$this -> writelog("已删除id为{$id}的闪屏",'sj_splash_manage',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/splash_list/platform/{$been_result[0]['platform']}/p/{$p}/lr/{$lr}/my_time/{$my_time}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
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

	public function hanhua_ads_config()
	{
		$ad_conf_db = new Model();
		if ($this->isPost())
		{//post请求，修改配置
			$conf_data = $_POST['config_type'];
			if(count($_POST['config_type']) < 3)
			{
				$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/hanhua_ads_config");
				$this -> error("配置名被恶意篡改！！");
			}
			foreach ( $conf_data as $config_type => $configcontent)
			{
				if(!in_array($config_type,array('STRONG_SPLASH_STATUS','SOFT_SPLASH_STATUS','FLOAT_ICON_STATUS','STRONG_POP_STATUS')))
				{
					$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/hanhua_ads_config");
					$this -> error("配置名被恶意篡改！！");
				}
			}
			
			$log_result = '';
			foreach ( $conf_data as $config_type => $configcontent)
			{
				$data = array();
				$data['configcontent'] = "$configcontent";
				$data['uptime'] = time();
				$log_result .= $this->logcheck(array('config_type'=> $config_type), 'pu_config', $data, $ad_conf_db);
				$tmp = $ad_conf_db->table("pu_config")->where('config_type="'.$config_type.'"')->data($data)->save();
			}
			$this -> writelog("汉化软件广告插件管理".$log_result,'pu_config',"config_type:{$config_type}",__ACTION__ ,"","edit");
			$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/hanhua_ads_config");
			$this -> success("保存配置成功！");
		}
		$map['_string'] = 'config_type in ("STRONG_SPLASH_STATUS","SOFT_SPLASH_STATUS","FLOAT_ICON_STATUS","STRONG_POP_STATUS")';
		$conf_lists = $ad_conf_db->table("pu_config")->where($map)->order("conf_id asc")->select();
		$this->assign("confLists",$conf_lists);
		$this->display();

	}//end of function

	
	//2014.10.27 jiwei
	//汉化推端需求
	//处理破解软件相关设置
	//命名和处理方式均参考hanhua_ads_config方法
	public function pojie_ads_config()
	{
		$ad_conf_db = new Model();
		if ($this->isPost())
		{//post请求，修改配置
			$conf_data = $_POST['config_type'];
			if(count($_POST['config_type']) < 3)
			{
				$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/pojie_ads_config");
				$this -> error("配置名被恶意篡改！！");
			}
			foreach ( $conf_data as $config_type => $configcontent)
			{
				if(!in_array($config_type,array('CRACK_STRONG_SPLASH_STATUS','CRACK_SOFT_SPLASH_STATUS','CRACK_FLOAT_ICON_STATUS','CRACK_STRONG_POP_STATUS')))
				{
					$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/pojie_ads_config");
					$this -> error("配置名被恶意篡改！！");
				}
			}
			
			$log_result = '';
			foreach ( $conf_data as $config_type => $configcontent)
			{
				$data = array();
				$data['configcontent'] = "$configcontent";
				$data['uptime'] = time();
				$log_result .= $this->logcheck(array('config_type'=> $config_type), 'pu_config', $data, $ad_conf_db);
				$tmp = $ad_conf_db->table("pu_config")->where('config_type="'.$config_type.'"')->data($data)->save();
			}
			$this -> writelog("破解软件广告插件管理".$log_result,'pu_config',"config_type:{$config_type}",__ACTION__ ,"","edit");
			$this -> assign('jumpUrl',"/index.php/Sj/Splashmanage/pojie_ads_config");
			$this -> success("保存配置成功！");
		}
		$map['_string'] = 'config_type in ("CRACK_STRONG_SPLASH_STATUS","CRACK_SOFT_SPLASH_STATUS","CRACK_FLOAT_ICON_STATUS","CRACK_STRONG_POP_STATUS")';
		$conf_lists = $ad_conf_db->table("pu_config")->where($map)->order("conf_id asc")->select();
		$this->assign("confLists",$conf_lists);
		$this->display();
	
	}
	//END
	//精准投放内容查看
	function jztf_splash_show()
	{
		$model=M();
		$channel_model = M('channel');
		$list=array();
		$where['id']=$_GET['id'];
		$where['status']=1;
		$result=$model->table('sj_splash_manage')->where($where)->find();
		
		//渠道	
		$cid_str = preg_replace('/^,/','',$result['cid']);
		$cid_str = preg_replace('/,$/','',$cid_str);
		$array = explode(',', $cid_str);
		$cname = $channel_model->where("cid in ({$cid_str})")->findAll();
		if($cid_str=="")
		{
			$list['cname'] = "<p>不限</p>";
		}
		else
		{
			if (in_array("0",$array))
			{
				$list['cname'] .= "<p>通用</p>";
			}
			foreach ($cname as $k1 => $v1) 
			{
				$list['cname'] .= "<p>{$v1['chname']}</p>";
			}
		}
		//机型
		$did_str = preg_replace('/^,/','',$result['device_did']);
		$did_str = preg_replace('/,$/','',$did_str);
		$dname = $channel_model->table("pu_device")->where("did in ({$did_str})")->findAll();
		
		if($did_str=="")
		{
			$list['dname'] .= "<p>不限</p>";		
		}
		else
		{
			foreach ($dname as $k2 => $v2) 
			{
					
				$list['dname'] .= "<p>{$v2['dname']}</p>";				
			}
		}
		//覆盖人数
		if($result['csv_count']==0)
		{
			$list['cover_num']="不限";
		}
		else
		{
			$list['cover_num']=$result['csv_count'];
		}
		//运营商
		$operating_db = D('Sj.Operating');
		$oid_str = preg_replace('/^,/','',$result['oid']);
		$oid_str = preg_replace('/,$/','',$oid_str);
		$oid_array = explode(',', $oid_str);
		$oname = $operating_db->where("oid in ({$oid_str})")->findAll();
		if($oid_str=="")
		{
			$list['oname'] = "不限";
		}
		else
		{
			if(in_array("-1",$oid_array))
			{
				$list['oname'] .="未插卡";
			}
			foreach ($oname as $k3 => $v3) 
			{
				$list['oname'] .= "<p>{$v3['mname']}</p>";
			}
		}
		//固件版本
		$firmware_str = preg_replace('/^,/','',$result['firmware']);
		$firmware_str = preg_replace('/,$/','',$firmware_str);
		$firmware_array = explode(',', $firmware_str);
		
		if($firmware_str==""||$result['firmware']=="")
		{
			$list['firmwares']="不限";
		}
		else
		{
			$firmwares = $channel_model->table('pu_config')->field('configname,configcontent')->where("config_type='firmware' and status=1 and configname in ({$firmware_str})")->select();

			foreach ($firmwares as $k4 => $v4) 
			{	
				$list['firmwares'] .= "<p>{$v4['configcontent']}</p>";				
			}
		}
		//市场版本
		$util = D('Sj.Util');
		$version_str = preg_replace('/^,/','',$result['version_code']);
		$version_str = preg_replace('/,$/','',$version_str);
		$version_array = explode(',', $version_str);
		if($version_str==""||$result['version_code']=="")
		{
			$list['version_name']="不限";
		}
		else
		{
			$version_list=$util->getMarketVersion($version_array);
			foreach ($version_list as $k5 => $v5) 
			{	
				if($v5[1]==true)
				{
					$list['version_name'] .= "<p>{$v5[0]}</p>";
				}
			}
		}
		$this->assign("list",$list);
		$this->display();
	}
}
