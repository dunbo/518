<?php
class SdkUpdateAction extends CommonAction {
	
	public $sdk_types  = array(array('id' => 1,'name' => '网游'), array('id' => 2,'name' => '单机'));

	//SDK更新增加
	function sdk_add_show(){
		$model = new Model();
		$where['status'] = 1;
		$jar = $model->table('sj_sdk_jar')->where($where)->select();//JAR版本
		$sdk = $model->table('sj_sdk')->where($where)->field('DISTINCT version_code,version_name,sdk_type')->select();//JAR版本//SDK版本
		 //echo "<pre>";print_r($sdk);
		//echo "<pre>";print_r($sdk);echo 1;
		$this->assign('sdk_types',$this->sdk_types);
		$this->assign('jar',$jar);
		$this->assign('sdk',$sdk);
		$this->display();
	}
	//SDK更新增加处理
	function sdk_add_do(){
		$model =new Model();
		//echo "<pre>";print_r($_POST);exit;
		
		$jar_version = implode(',',$_POST['jar_version']);//jar版本
		$old_version = implode(',',$_POST['old_version']);//sdk版本
		//print_r($jar_version);exit;
		
		/* 2015.8.28 去除判断 
		if($old_version  == ''){
			$this->error("没有选择更新版本");
		}*/
		if($jar_version  == ''){
			$this->error("没有选择JAR版本");
		}
		if($_POST['game_ext'] == 0){
			$this->error("没有选择游戏范围");
		}
		//手动上传游戏
		if($_POST['game_ext'] == 1){
			$select_package_list = $_POST['all_select'];
			if($_POST['all_select'] == ''){
				$this->error("没有选择游戏");
			}
		}
		if(!isset($_POST['force'])){
			$this->error("没有选择是否强制更新");
		}
		if($_FILES['file_update']['name'] == ''){
			$this->error("没有上传更新包");
		}
		
		//屏蔽游戏
		if($_POST['game_ext'] != 1){
			$all_ignore = $_POST['all_ignore'];
		}
		
		//移动文件
		$path = date("Ym/d/");
		$folder = UPLOAD_PATH. '/apk/'. $path;
		//$path= "/".date('Ym/d').'/';
		//$folder = C("MARKET_PUSH_CSV").$path;
		$this->mkDirs($folder);

		$new_file_name = time() . '_' . rand(1000,9999) . '.apk';
		
		$new_file_path = $folder . $new_file_name;
		$apkurl = substr($new_file_path,21);
		move_uploaded_file($_FILES['file_update']['tmp_name'], $new_file_path);
		$info_tmp = get_apk_info($new_file_path);
		$jar_support_ver = get_sdk_plugin_jar_version_code($new_file_path);
		
		//写入数据库
		$data['jar_support_ver'] = $jar_support_ver;
		$data['limit_rules'] = $_POST['limit_rules'];//2015.8.28新增 
		$data['version_code'] = $info_tmp['versionCode'];
		$data['version_name'] = $info_tmp['versionName'];
		$data['target_jar_code'] = $jar_version; //接单表数据 jar 版本 
		$data['apkurl'] = $apkurl;
		$data['apksize'] = filesize($new_file_path);
		$data['submit_tm'] = time();
		$data['last_refresh'] = time();//更新时间
		$data['force_update'] = $_POST['force'];//接单表数据 是否强制更新
		$data['status'] = 1;//删除修改该值
		$data['target_version_code'] = empty($old_version)?"":$old_version;;//接表单数据 针对升级的版本
		$md5 = md5_file($new_file_path);
		$data['md5_file'] = $md5;
		$data['select_package_type'] = $_POST['game_ext'];//接表单数据 sdk关联游戏包类型,1:手动上传,2:全部游戏,3:网络游戏,4:棋牌游戏,5:单机游戏
		$data['select_package_list'] = $select_package_list;//手动上传的游戏包名，如果多个包名则用英文逗号,隔开
		$data['ignore_package_list'] = $all_ignore;//屏蔽游戏
		$data['remark'] = trim(strip_tags($_POST['remark']));
		$data['sdk_type'] = intval($_POST['sdk_type']);
		
		//echo "<pre>";print_r($_FILES);print_r($info_tmp);exit;
		//判断SDK版本
		if(isset($data['version_code']) && $data['version_code']<4000){
			//$this->error("SDK版本不能低于4.0");
		}
		//判断是否重复：
		$model =new Model();
		$where2['md5_file'] = $data['md5_file'];
		$where2['status'] = 1;
		$md5_files = $model->table('sj_sdk')->field('md5_file')->where($where2)->select();
		if($md5_files){
			$this->error("该版本已上传，可通过列表进行编辑!");
		}
		
		$result = $model->table('sj_sdk')->add($data);
		//echo $model->getlastsql();exit;
		if ($result) {
			//生成增量更新 $result 新ID $oid_arr 小于$result 
			$where['version_code'] =array('lt', $data['version_code']);
			$ids = $model->table('sj_sdk')->field('id')->where($where)->select();
			//echo "<pre>";print_r($oid_arr);exit;
			$oid_arr = array();
			foreach($ids as $v){
				$oid_arr[] =$v['id'];
			}
			$max_num = 50;
			$oid_count = count($oid_arr);
			for ($i = 0; $i < $oid_count; $i += $max_num) {
				$arr = array_slice($oid_arr, $i, $max_num);
				$task_client = get_task_client();
				$task_client->doBackground("sdk_incremental_update",json_encode(array('id'=>$result,'oid'=>$arr)));
			}
			//插入sj_sdk_md5
			$where =array('md5',$data['md5_file']);
			$ids = $model->table('sj_sdk_md5')->field('id')->where($where)->find();
			if(empty($ids)){
				$md_data['md5']=$data['md5_file'];
				$result = $model->table('sj_sdk_md5')->add($md_data);
			}
			
            $this->writelog("已添加id为{$result}sdk",'sj_dev_everybody_said', $result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl', '/index.php/Sj/SdkUpdate/sdk_update_list');
            $this->success("添加成功");
        }else{
			$this->error("添加失败");
		}
	}
	//添加屏蔽游戏 AJAX
	function get_ignore(){
		$model = new Model();
		$tmp_name = $_FILES['the_ignore_package']['tmp_name'];
		$tmp_houzhui = $_FILES['the_ignore_package']['name'];
		$file_type = explode('.', $tmp_houzhui);
		
		if ($file_type[1] != 'csv') {
			echo 2;
			return 2;
            exit;
		}
		$shili = fopen($tmp_name, "r");  
		while (!feof($shili)) {
			$shi = fgets($shili, 1024);
			$str .= $shi.',';
		}
		$str = substr($str,0,-1);
		$strss = explode("\r\n,",$str);
		$strss = array_unique($strss);
		foreach($strss as $key => $val){
			if($val && $val != ''){
				$vals[0] = $val;
				$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
				if(!$soft_result){
					$error = array(4,$val);
					echo json_encode($error);
					return json_encode($error);
					exit;
				}
				$vals[1] = $soft_result[0]['softname'];
				$package_arr[] = $vals;
			}
		}
		if(count($package_arr) > 200){
			echo 3;
			return 3;
			exit;
		}
		//提交的包名是否可以存在浏览器中方便添加或删除的时候提取/剔除包名？
		echo json_encode($package_arr);
		return json_encode($package_arr);
	}
	//手动上传游戏
	function get_package(){
		$model = new Model();
		$tmp_name = $_FILES['the_package']['tmp_name'];
		$tmp_houzhui = $_FILES['the_package']['name'];
		$file_type = explode('.', $tmp_houzhui);
		
		if ($file_type[1] != 'csv') {
			echo 2;
			return 2;
            exit;
		}
		$shili = fopen($tmp_name, "r");  
		while (!feof($shili)) {
			$shi = fgets($shili, 1024);
			$str .= $shi.',';
		}
		$str = substr($str,0,-1);
		$strss = explode("\r\n,",$str);
		$strss = array_unique($strss);
		foreach($strss as $key => $val){
			if($val && $val != ''){
				$vals[0] = $val;
				$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
				// file_put_contents('lizuofeng_8.5.log',$model->getlastsql(),FILE_APPEND);
				if(!$soft_result){
					$error = array(4,$val);
					echo json_encode($error);
					return json_encode($error);
					exit;
				}
				$vals[1] = $soft_result[0]['softname'];
				$package_arr[] = $vals;
			}
		}
		if(count($package_arr) > 200){
			echo 3;
			return 3;
			exit;
		}
		//提交的包名是否可以存在浏览器中方便添加或删除的时候提取/剔除包名？
		echo json_encode($package_arr);
		return json_encode($package_arr);
	}
	//单独增加屏蔽游戏
	function add_ignore(){
		//修改后的版本，解决添加为NULL问题
		$model = new Model();
		$old_package = $_GET['old_package'];
		$new_package = $_GET['new_package'];
		$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $new_package)) -> order('softid DESC') -> limit(1) -> select();
		if(!$soft_result){
			$error = array(4,$new_package);
			echo json_encode($error);
			return json_encode($error);
			exit;
		}
		$old_package_arr = explode(',',$old_package);
		if(in_array($new_package,$c)){
			echo 5;
			return 5;
			exit;
		}
		
		//$now_package = $old_package . ',' . $new_package; 
		if($old_package != 'undefined' && $old_package != ''){
			//file_put_contents('lizuofeng.php',$old_package."kong");
			$now_package = $old_package . ',' . $new_package;
			$package_arr = explode(',',$now_package);
		}else{
			$now_package = $new_package;
			$package_arr = explode(',',$now_package);
		}
		array_filter($package_arr);
		foreach($package_arr as $key => $val){
			$vals[0] = $val;
			$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
			$vals[1] = $soft_result[0]['softname'];
			$package_arrs[] = $vals;
		}
		array_filter($package_arrs);
		echo json_encode($package_arrs);
		return json_encode($package_arrs);
	}
	//删除屏蔽游戏
	function del_select(){
		$model = new Model();
		$no_package = $_GET['no_package'];
		$old_package = $_GET['old_package'];
		$old_package_arr = explode(',',$old_package);
		foreach($old_package_arr as $key => $val){
			if($val != $no_package){
				$new_package[] = $val;
			}
		}

		foreach($new_package as $key => $val){
			$vals[0] = $val;
			$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
			$vals[1] = $soft_result[0]['softname'];
			$package_arrs[] = $vals;
		}
		if($package_arrs){
			echo json_encode($package_arrs);
			return json_encode($package_arrs);
		}else{
			echo 204;
			return 204;
		}
	}
	
	//SDK或者JAR版本增加页面的展现
	function jar_add_show(){
		$model = new Model();
		
		
		//$sdk_types  = array( 3 => '网游', 4 => '棋牌', 5 => '单机' );
		$this->assign('sdk_types',$this->sdk_types);
		$this->display();
	}
	//增加SDK或者JAR 处理程序
	function jar_add_do(){
		$model = new Model();
		
		//判断参数是否为空
		if($_POST['jar_version'] =='' || $_POST['jar_name'] == ''){
			$this->error("非法操作，参数不能为空");
		}
		$where = array(
			'jar_version' => $_POST['jar_version'],
            'status' => 1
        );
		if($_POST['qufen'] == 'JAR'){
			
			$where['sdk_type'] = intval($_POST['sdk_type']);
			$sj_sdk_jar = $model->table('sj_sdk_jar')->where($where)->select();
			//echo "<pre>";print_r($sj_sdk_jar);exit;
			foreach($sj_sdk_jar as $v){
				if($v['jar_version'] == $_POST['jar_version']){
					$this->error("添加失败,已经存在版本号：".$_POST['jar_version']);
				}
				/*else if($v['jar_name'] == $_POST['jar_name']){
					$this->error("添加失败,已经存在版本名称：".$_POST['jar_name']);
				};*/
			}
			$table_name = 'sj_sdk_jar';//表名
			$data['jar_version'] = $_POST['jar_version'];//jar 版本
			$data['jar_name'] = $_POST['jar_name'];//jar 名称
			$data['sdk_type'] = intval($_POST['sdk_type']);//jar sdk类别
			$data['addtime'] = time();//添加时间
			$data['edit_time'] = time();//修改时间
			$data['status'] = 1;//状态
			
		}else{
			$table_name = 'sj_sdk_version';//表名
			$data['sdk_version'] = $_POST['jar_version'];//sdk 版本
			$data['sdk_name'] = $_POST['jar_name'];//sdk 名称
			$data['addtime'] = time();//添加时间
		}
		$result = $model->table($table_name)->add($data);
		//echo $model->getlastsql();exit;
		if ($result) {
            $this->writelog("已添加id为{$result}的{$_POST['qufen']}版本",$table_name, $result,__ACTION__ ,"","add");
			if($_POST['qufen'] == 'JAR'){
				$this -> assign('jumpUrl', '/index.php/Sj/Jar/jar_index_list');
			}else{
				$this -> assign('jumpUrl', '/index.php/Sj/SdkUpdate/sdk_update_list');
			}
            $this->success("添加成功");
        }else{
            //$this->writelog("已添加id为{$result}{$_POST['qufen']}版本");
			$this->error("添加失败");
		}
	}
	
	//SDK更新版本删除
    function sdk_update_del() {
        $model = new Model();
		//删除id 处理
		foreach(explode(',',$_GET['id']) as $v){
			if($v != ''){
				$ids[] = $v;
			}
		}
		$id = implode(',',$ids);
		
		$data['status'] = 0;
		$result = $model->table("sj_sdk")->where(array('id' => array('in',$id)))->save($data);
		if($result)
		{
			$this->writelog("删除id为{$id}的SDK更新版本",'sj_sdk', $id,__ACTION__ ,"","del");
			$this->success("删除成功");
		}
		else
		{
			$this->success("删除失败");
		}
    }
	//SDK编辑显示
    function news_edit_show() {
        $model = new Model();
		$where['status'] = 1;
		$jar = $model->table('sj_sdk_jar')->where($where)->select();//JAR版本
		$sdk = $model->table('sj_sdk')->where($where)->field('DISTINCT version_code,version_name,sdk_type')->select();//JAR版本//SDK版本
		
		$edit_sdk = $model->table('sj_sdk')->where("id = {$_GET['id']}")->select();//SDK版本
		foreach($edit_sdk[0]['target_jar_code'] as $k=>$v){
			//print_r($v);echo "<br/>";
		}
		$edit_sdk[0]['target_jar_code']= explode(',',$edit_sdk[0]['target_jar_code']);//变成数组 方便页面判断
		$edit_sdk[0]['target_version_code'] = explode(',',$edit_sdk[0]['target_version_code']);//变成数组 方便页面判断
		
		//手动上传游戏包名
		$select_package_list_arr = explode(',',$edit_sdk[0]['select_package_list']);
		foreach($select_package_list_arr as $key => $val){
			$vals[0] = $val;
			$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
			$vals[1] = $soft_result[0]['softname'];
			$package_arrs[] = $vals;
		}
		//屏蔽游戏包名
		$ignore_package_list_arr = explode(',',$edit_sdk[0]['ignore_package_list']);
		foreach($ignore_package_list_arr as $key => $val){
			$vals[0] = $val;
			$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
			$vals[1] = $soft_result[0]['softname'];
			$ignore_arrs[] = $vals;
		}
		
		//echo $model->getlastsql();
		//echo "<pre>";print_r($edit_sdk[0]);
		
		//print_r($sdk);exit;
		$this->assign('jar',$jar);
		$this->assign('sdk',$sdk);
		$this->assign('sdk_types',$this->sdk_types);
		$this->assign('package_arrs',$package_arrs);//手动上传游戏包名
		$this->assign('ignore_arrs',$ignore_arrs);//屏蔽游戏包名
		$this->assign('edit_sdk',$edit_sdk[0]);
        $this->display();
    }
	//编辑SDK 处理程序
	function sdk_edit_do(){
		$model =new Model();
		//echo "<pre>";print_r($_POST);exit;
		$where['id'] = $_POST['id'];
		$jar_version = implode(',',$_POST['jar_version']);//jar版本
		$old_version = implode(',',$_POST['old_version']);//sdk版本
		
		/* 2015.8.28 去除判断 
		if($old_version  == ''){
			$this->error("没有选择更新版本");
		}*/
		if($jar_version  == ''){
			$this->error("没有选择JAR版本");
		}
		if($_POST['game_ext'] == 0){
			$this->error("没有选择游戏范围");
		}
		//手动上传游戏
		if($_POST['game_ext'] == 1){
			$select_package_list = $_POST['all_select'];
			if($_POST['all_select'] == ''){
				$this->error("没有选择游戏");
			}
		}
		
		if(!isset($_POST['force'])){
			$this->error("没有选择是否强制更新");
		}
		
		//屏蔽游戏
		if($_POST['game_ext'] != 1){
			$all_ignore = $_POST['all_ignore'];
		}
		//只有上传APK文件时候才走
		if($_FILES['file_update']['name'] != ''){
			//移动文件
			
			//$path= "/".date('Ym/d').'/';
			//$folder = C("MARKET_PUSH_CSV").$path;
			$path = date("Ym/d/");
			$folder = UPLOAD_PATH. '/apk/'. $path;//2015.8.3 修改
			$this->mkDirs($folder);

			$new_file_name = time() . '_' . rand(1000,9999) . '.apk';
			
			$new_file_path = $folder . $new_file_name;
			
			$apkurl = substr($new_file_path,21);
			
			
			move_uploaded_file($_FILES['file_update']['tmp_name'], $new_file_path);
			$info_tmp = get_apk_info($new_file_path);
			$jar_support_ver = get_sdk_plugin_jar_version_code($new_file_path);
			$data['jar_support_ver'] = $jar_support_ver;
			$data['version_code'] = $info_tmp['versionCode'];
			$data['version_name'] = $info_tmp['versionName'];
			$data['md5_file'] = md5_file($new_file_path);
			$data['apkurl'] = $apkurl;
			$data['apksize'] = filesize($new_file_path);
			//判断SDK版本
			if(isset($data['version_code']) && $data['version_code']<4000){
				// $this->error("SDK版本不能低于4.0");
			}
			//判断是否重复：
			$where2['md5_file'] = $data['md5_file'];
			$md5_files = $model->table('sj_sdk')->field('md5_file')->where($where2)->select();
			if($md5_files){
				$this->error("该版本已上传，可通过列表进行编辑!");
			}
		}
		
		//写入数据库
		
		$data['limit_rules'] = $_POST['limit_rules'];//2015.8.28新增 
		$data['target_jar_code'] = $jar_version; //接单表数据 jar 版本 
		$data['submit_tm'] = time();
		$data['last_refresh'] = time();//更新时间
		$data['force_update'] = $_POST['force'];//接单表数据 是否强制更新
		$data['status'] = 1;//删除修改该值
		$data['target_version_code'] = empty($old_version)?"":$old_version;;//接表单数据 针对升级的版本
		$data['select_package_type'] = $_POST['game_ext'];//接表单数据 sdk关联游戏包类型,1:手动上传,2:全部游戏,3:网络游戏,4:棋牌游戏,5:单机游戏
		$data['select_package_list'] = $select_package_list;//手动上传的游戏包名，如果多个包名则用英文逗号,隔开
		$data['ignore_package_list'] = $all_ignore;//屏蔽游戏
		$data['remark'] = trim(strip_tags($_POST['remark']));
		$data['sdk_type'] = intval($_POST['sdk_type']);
		
		//echo "<pre>";print_r($_FILES);print_r($info_tmp);exit;
		
		//用于记录详细日志
        $log_result = $this->logcheck(array('id' => $_POST['id']), 'sj_sdk', $data, $model);
		
		$result = $model->table('sj_sdk')->where($where)->save($data);
		//echo $model->getlastsql();exit;
		$where3['status'] = 1;
		if ($result) {
			
			//生成增量更新 $result 新ID $oid_arr 小于$result 
			if($_FILES['file_update']['name'] != ''){//上传新的APK了
				$where3['version_code'] =array('lt', $data['version_code']);
			}else{
				$where2['id'] = $_POST['id'];
				$versioncode = $model->table('sj_sdk')->field('version_code')->where($where2)->select();
				$where3['version_code'] =array('lt', $versioncode[0]['version_code']);
			}
			$ids = $model->table('sj_sdk')->field('id')->where($where3)->select();
			//echo $model->getlastsql();
			//echo "<pre>";print_r($ids);exit;
			$oid_arr = array();
			foreach($ids as $v){
				$oid_arr[] =$v['id'];
			}
			$max_num = 50;
			$oid_count = count($oid_arr);
			for ($i = 0; $i < $oid_count; $i += $max_num) {
				$arr = array_slice($oid_arr, $i, $max_num);
				$task_client = get_task_client();
				$task_client->doBackground("sdk_incremental_update",json_encode(array('id'=>$_POST['id'],'oid'=>$arr)));
			}
			//插入sj_sdk_md5
			$where =array('md5',$data['md5_file']);
			$ids = $model->table('sj_sdk_md5')->field('id')->where($where)->find();
			if(empty($ids)){
				$md_data['md5']=$data['md5_file'];
				$result = $model->table('sj_sdk_md5')->add($md_data);
			}
			$this->writelog("成功修改ID为{$_POST['id']}的SDK热更新" . $log_result,'sj_sdk', $_POST['id'],__ACTION__ ,"","edit");
			$this -> assign('jumpUrl', '/index.php/Sj/SdkUpdate/sdk_update_list');
            $this->success("修改成功");
        }else{
            $this->writelog("修改ID为{$_POST['id']}的SDK热更新失败",'sj_sdk_md5', $_POST['id'],__ACTION__ ,"","edit");
			$this->error("修改失败");
		}
	}
	
	
	
	
	
    // SDK热更新 显示页面
    function sdk_update_list() {
		$model = D('Sj.SdkUpdate');
		$where['status'] = 1;
		//搜索 SDK更新版本
		$sdk = $model->table('sj_sdk')->field('DISTINCT version_code,version_name')->where($where)->select();//JAR版本//SDK版本
		
        $where = array(
            'status' => 1,
        );
        // trim一下
        foreach ($_GET as $key => $value) {
            $_GET[$key] = trim($value);
        }
		//print_r($_GET);
        // 记录页数参数
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
		// 搜索更新SDK类型
        if ($_GET['search_info_type'] != '' && $_GET['search_info_type'] != -1) {
            $where['version_code'] = $_GET['search_info_type'];
        }
		
		
        // 搜索开始时间
        if ($_GET['startDate'] != '') {
			$startDate = strtotime($_GET['startDate']);
            $where['last_refresh'] =array('egt', $startDate);
        }
        // 搜索结束时间
        if ($_GET['endDate'] != '') {
			$endDate =  strtotime($_GET['endDate'].'+1day');
            $where['last_refresh'] =array('elt', $endDate);
        }
		
		 if ($_GET['startDate'] != '') {
			$startDate = strtotime($_GET['startDate']);
            $where['last_refresh'] =array('egt', $startDate);
        }
        // 搜索开始 结束时间
        if ($_GET['endDate'] != '' && $_GET['startDate'] != '') {
			$endDate =  strtotime($_GET['endDate'].'+1day');
			$startDate = strtotime($_GET['startDate']);
            $where['last_refresh'] =array('between',array($startDate,$endDate));
        }
		
        
		// 搜索软件包名
        if ($_GET['search_pckname'] != '') {
            $where['apply_pkg'] = array('eq', $_GET['search_pckname']);
        }
		// 搜索采集站点
        if ($_GET['search_website'] != '') {
            $where['website_name'] = array('like', '%' . $_GET['search_website'] . '%');
        }


        if ($_GET['search_sdk_type']) {
            $where['sdk_type'] = $_GET['search_sdk_type'];
        }
		

		
        $count = $model->table('sj_sdk')->where($where)->order('last_refresh DESC')->count();
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show(); //分页显示输出
        $list = $model->table('sj_sdk')->field('*')->where($where)->order('last_refresh DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
        //echo $model -> getlastsql();
		// 搜索内容
		
		if($startDate != ''){
			$this->assign('startDate', date('Y-m-d',$startDate));//开始时间
		}
		if($endDate != ''){
			$endDate =  strtotime($_GET['endDate']);
			$this->assign('endDate', date('Y-m-d',$endDate));//结束时间
		}
		
		foreach ($list as $k => $v) {
			$list[$k]['sdk_type_name'] = $this->sdk_types[$v['sdk_type'] - 1]['name'];
		}



		$this->assign('sdk_types',$this->sdk_types);
		$this->assign('search_sdk_type', $_GET['search_sdk_type']);
        $this->assign('sdk', $sdk);//SDK更新版本
		$this->assign('search_pckname', $_GET['search_pckname']);
        $this->assign('search_website', $_GET['search_website']);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('apkurl', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('function_name', __FUNCTION__);
        $this->assign('url_param', $url_param);
        $this->display();
    }
	
}
