<?php
if (!defined('IMG_HOST'))
    define('IMG_HOST', '/data/att/m.goapk.com');
if (!defined('IMG_URL'))
    define('IMG_URL', IMGATT_HOST);

class PushAction extends CommonAction {
	/*start*/
	const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";
	//添加资讯显示
    function push_add_show() {
        $this->assign('function_name', $_GET['from']);
        // 记录页数参数，方便跳回第几页
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($key == 'id' || $key == 'from')
                continue;
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
		//版本号控制
		$sdk_version_db = json_decode(file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/index.php/Interface/sdk_list'),true);
		$this -> assign('sdk_version_db',$sdk_version_db['list']);
			
        $this->assign('url_param', $url_param);
        $this->display();
    }
	//上传图片处理程序
	function picture_do123(){
		//移动文件
		$path = date("Ym/d/");
		$folder = UPLOAD_PATH. '/img/'. $path;// /data/att/m.goapk.com/img/201508/19/1439953416_7699.jpg
		$this->mkDirs($folder);
		$new_file_name = time() . '_' . rand(1000,9999) . '.jpg';
		$new_file_path = $folder . $new_file_name; 
		
		//上传图片判断
		$tmp_name1 = $_FILES[$_POST['action']]['tmp_name'];
		//$tmp_name2 = $_FILES[$_POST['action']]['tmp_name'];
		$size = getimagesize($tmp_name1);
		//$size2 = getimagesize($tmp_name2);
		if($size[0] != $_POST['width'] || $size[1] != $_POST['height'] ){
			exit(json_encode(array('code'=>0,'msg'=>'图片尺寸错误，参照尺寸上传')));	
		}else{
			if(!move_uploaded_file($tmp_name1,$new_file_path)){
			//if(!move_uploaded_file($tmp_name1,$new_file_name)){ //本地测试
				$error .= "上传出错\n";
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}else{
				exit(json_encode(array('code'=>1,'action'=>$_POST['action'],'file_path'=>substr($new_file_path,21),'msg'=>$new_file_path)));
			}
		}
	}

	//上传图片处理程序
	function picture_do(){
		//移动文件
		$path = date("Ym/d/");
		$folder = UPLOAD_PATH. '/img/'. $path;// /data/att/m.goapk.com/img/201508/19/1439953416_7699.jpg
		$this->mkDirs($folder);
		$new_file_name = time() . '_' . rand(1000,9999) . '.jpg';
		$new_file_path = $folder . $new_file_name; 
		//上传图片判断
		$tmp_name1 = $_FILES[$_GET['action']]['tmp_name'];
		// $tmp_name1 = $_FILES[$_POST['action']]['tmp_name'];
		//$tmp_name2 = $_FILES[$_POST['action']]['tmp_name'];
		$size = getimagesize($tmp_name1);
		//$size2 = getimagesize($tmp_name2);
		if($size[0] != $_GET['width'] || $size[1] != $_GET['height'] ){
			exit(json_encode(array('code'=>0,'msg'=>'图片尺寸错误，参照尺寸上传')));	
		}else{
			if(!move_uploaded_file($tmp_name1,$new_file_path)){
			//if(!move_uploaded_file($tmp_name1,$new_file_name)){ //本地测试
				$error .= "上传出错\n";
				exit(json_encode(array('code'=>0,'msg'=>$error)));
			}else{
				exit(json_encode(array('code'=>1,'action'=>$_GET['action'],'file_path'=>substr($new_file_path,21),'msg'=>$new_file_path)));
			}
		}
	}
	//添加资讯提交
    function push_add_submit() {
		$model_push = D('Sj.Push');
        $model = new Model();
		//图片保存 四个判断
		//1.1低分图片 2.2高分图片
		$data['single_item_icon_low'] = $_POST['push_img_low_1_hide'];
		$data['single_item_icon_high'] = $_POST['push_img_low_2_hide'];
		
		$data['multi_item_icon_low'] = $_POST['push_img_high_1_hide'];
		$data['multi_item_icon_high'] = $_POST['push_img_high_2_hide'];
		//echo "<pre>";print_r($_FILES);print_r($_POST);exit;
		//编辑器传过来得内容处理
		if($_POST['push_type'] == 4){
			$module_content = $_POST['editor_content'];
			if (empty($module_content) || $module_content == "<p>
		&nbsp;
	</p>") {
				$this->error("编辑内容不能为空");
			}
			$_POST['editor_content'] = stripcslashes($_POST['editor_content']);
			// 1，将与自己域名相关的图片域名换回约定的标签字符串
			$_POST['editor_content'] = str_replace(IMGATT_HOST, self::HOST_TAG, $_POST['editor_content']);
			// 2，将富文本里的图片发送到服务器并路径内容写成约定标签
			preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $_POST['editor_content'], $matches);

			$pre_path = $_SERVER['DOCUMENT_ROOT'];

			foreach ($matches[1] as $key => $val) {
				$files_name[$key] = str_replace('.', '', microtime(true)) . '_' . PushAction::rand_code(8);
			}
			foreach ($matches[1] as $key => $val) {
				$files[$files_name[$key]] = '@' . $pre_path . $val;
			}
			$arr = PushAction::dev_upload($files);
			if ($arr['ret']) {
				foreach ($arr['ret'] as $key => $val) {
					unset($k, $new_k);
					$k = array_search($key, $files_name);
					$new_k = $matches[1][$k];
					$new_arr[$new_k] = self::HOST_TAG . $val;
				}
				//文章内容中图片路径替换
				$_POST['editor_content'] = strtr($_POST['editor_content'], $new_arr);
			}

			//手机显示时图片超出宽度
			$_POST['editor_content'] = '<style>img{max-width:90%}</style>'.$_POST['editor_content'];

		}
		//判断 老用户名 VIP用户
		if($_POST['select_user_type'] == 1){
			$data['select_user_type_ext'] ='';
		}else if($_POST['select_user_type'] == 2){
			$data['select_user_type_ext'] =  implode(',',$_POST['old_user']);
		}else if($_POST['select_user_type'] == 3){
			$data['select_user_type_ext'] =  implode(',',$_POST['user_vip']);
		}
		$val = $_POST;
		$data['select_phone_dpi'] = $val['select_phone_dpi']; //手机分辨率
        $data['title'] = $val['push_title']; //推送标题
        $data['intro'] = $val['push_intro']; //推送简介
        $data['is_repeat'] = $val['push_mode']; //不点击是否重复推送
        $data['select_package_type'] = $val['game_ext']; //sdk关联游戏包类型,1:手动上传,2:全部游戏,3:网络游戏,4:棋牌游戏,5:单机游戏
        $data['select_package_list'] = $val['all_select']; //手动上传的游戏包名，如果多个包名则用英文逗号,隔开
        $data['ignore_package_list'] = $val['all_ignore']; //屏蔽的游戏包名，如果多个包名则用英文逗号,隔开
        $data['select_user_type'] = $val['select_user_type']; //关联用户类型,1:手动上传,2:老用户,3:VIP用户
        $data['select_user_file_path'] = $val['user_save']; //上传用户名文件路径
        $data['create_time'] = time(); //创建时间
		$data['update_time'] = time(); //修改时间
        $data['start_time'] = strtotime($val['startdate']); //推送开始时间
        $data['end_time'] = strtotime($val['enddate']); //推送结束时间
        $data['status'] = 1; //操作状态,1:正常,0:删除
        $data['pro_status'] = 0; //处理状态，这个后台程序使用，添加和修改都需要把该值置为0,0:未处理,1:已处理
		$data['item_content'] = htmlspecialchars($val['editor_content']); //"内容"//公告内容是html文本(push_type 4有)//暂时没有增加
        $data['push_type'] = $val['push_type']; //活动类型数字,1:网页,2:礼包,3:活动,4:公告,5:开发者礼包,6:H5游戏

        //为了区别前端是否跳转后加的策略
        if($data['push_type'] == 3) {
        	$model_activity = D('Sj.Activity');
        	$tmp = $model_activity->getActivityList(array('id' => $val['buy_id']));
        	//var_dump($model_activity);
        	//var_dump(array('id' => $val['buy_id']));
        	
        	if (empty($tmp)) {
        		$this->error("活动不存在");
        		exit;
        	}
        	list($tmp) = $tmp;
        	//var_dump($tmp);exit;
        	$data['item_content'] = json_encode(array('vip_jump' => $tmp['vip_jump'],'sdk_type'=>$tmp['sdk_type']));

        }else if($data['push_type'] == 7){
        	$data['package'] = $val['package_new']; 
        	$data['install_setting'] = $val['install_setting']; 
        	$data['uninstall_setting'] = $val['uninstall_setting']; 
        	$data['start_to_page'] = $val['start_to_page'];
        	$data['download_way'] = $val['download_way'];
        }


        

        $data['push_style'] = $val['push_show_type']; //推送样式,1:传统push,2:图片push
        $data['item_title'] = $val['html_title']; //内容标题 俩个标题用这一个
        $data['item_url'] = $val['html_link']; //内容链接地址
        $data['item_open'] = $val['push_browser']; //打开链接的方式，和url属性配合使用(push_type 1webView,2浏览器)
		//防止为NULL的时候报错
		if($data['item_open'] == ''){
			$data['item_open'] = 1;
		}
		//H5游戏手机横屏竖屏
		$data['display_method'] = $val['display_method']; 
		
        $data['item_objid'] = $val['buy_id']; //礼包或活动ID(push_type 2、3有)//暂时没有增加
        
		
		//所选日期得第二天凌晨时间戳：
		$today = strtotime(date("Y-m-d"));
		//echo date('Y-m-d H:i:s',$today );exit;
        $data['item_show_time_end'] = strtotime($val['push_time_end']) - $today; //每日推送时间
        $data['item_show_time_start'] = strtotime($val['push_time_start']) - $today; //每日推送时间
		
		//$result = $model->table('sj_sdk_push')->data($data)->add();
		//echo $model->getLastSql();exit;
		
		//SDK版本 非必填 2017-1-12 
		$data['game_sdk_version_rule'] = $_POST['game_sdk_version_rule'];
		if($_POST['game_sdk_version_rule']=='>=')
		{
			$data['game_sdk_version_code'] = $_POST['game_sdk_version1'];
		}
		else if($_POST['game_sdk_version_rule']=='<=')
		{
			$data['game_sdk_version_code'] = $_POST['game_sdk_version2'];
		}
		else if($_POST['game_sdk_version_rule']=='==')
		{
			//多个版本号去掉前后","
			$data['game_sdk_version_code'] = trim($_POST['force_update_version'],',');
		}
			
		
		$data['cps_only'] = intval($_POST['cps_only']);
		//print_r($data);exit;
		$result = $model_push->addSdkPush($data);
		
        if ($result) {
            $this->writelog("push管理:已添加id为{$result}的push",'sj_push',$result,__ACTION__ ,"","add");
            $this -> assign('jumpUrl', '/index.php/Sj/Push/push_news_released_list');
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
		if(in_array($new_package,$old_package_arr)){
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
	//手动上传用户名
	function upload_user(){
		$tmp_name = $_FILES['up_user']['tmp_name'];
		$tmp_houzhui = $_FILES['up_user']['name'];
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
				$package_arr[] = $val;
				$package_str .= $val.',';
			}
		}
		if(count($package_arr) > 200){
			echo 3;
			return 3;
			exit;
		}
		//提交的包名是否可以存在浏览器中方便添加或删除的时候提取/剔除包名？
		$need_str = substr($package_str,0,-1);
		echo json_encode($need_str);
		return json_encode($need_str);
	}
	//删除手动上传用户名
	function del_user(){
		$no_package = $_GET['no_package'];
		$old_package = $_GET['old_package'];
		$old_package_arr = explode(',',$old_package);
		foreach($old_package_arr as $key => $val){
			if($val != $no_package){
				$new_package .= $val.',';
			}
		}
		$new_package_str = substr($new_package,0,-1);
		echo json_encode($new_package_str);
		return json_encode($new_package_str);
	}
	// push管理展示页面
    function push_news_released_list() {
        //$model = new Model();
		$model = D('Sj.Push');
        $where = array(
            'push.status' => 1,
			'info.push_type' => array('neq',5)
        );
		$where['_string'] ="push.id = info.id";
        // trim一下
        foreach ($_GET as $key => $value) {
            $_GET[$key] = trim($value);
        }
        // 记录页数参数
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
		
        // push类型
        if ($_GET['search_info_type'] != '' && $_GET['search_info_type'] != -1) {
            $where['info.push_type'] = $_GET['search_info_type'];
        }
        // push标题
        if ($_GET['search_softname'] != '') {
            $where['info.title'] = array('like', '%' . $_GET['search_softname'] . '%');
        }
		$count = $model->table('sj_push push,sj_push_info_13 info') ->where($where)->order('push.id DESC')->count();
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show(); //分页显示输出
        $list = $model->table('sj_push push,sj_push_info_13 info')->field('push.id,info.title,info.push_type,push.start_time,push.end_time')->where($where)->order('push.start_time DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
		//echo date('Y-m-d H:i:s','1438272000');exit;
		//echo "<pre>";print_r($list);exit;
		//echo $model->getLastsql();exit;
        // 搜索内容
        $this->assign('search_softname', $_GET['search_softname']);
        $this->assign('search_news_name', $_GET['search_news_name']);
        $this->assign('search_info_type', $_GET['search_info_type']);

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('apkurl', IMGATT_HOST);
        $this->assign('function_name', __FUNCTION__);
        $this->assign('url_param', $url_param);
        $this->display();
    }
	//Push删除
    function push_news_del() {
        //$model = new Model();
		$model = D('Sj.Push');
		$first=substr($_GET['id'],0,1);
		$data['status'] = 0;
		$data['pro_status'] = 0;
		if($first==",")
		{
			$nid = substr($_GET['id'],1);
		}
		else
		{
			$nid = $_GET['id'];
		}
		$id_arr = explode(',',$nid);
		foreach($id_arr as $id)
		{
			//$result = $model->table('sj_sdk_push')->where(array('id' => $id))->save($data);
			//删除信息 主PUSH和信息表都删除
			$log = $this->logcheck(array('id' => $id),'sj_push',$data,$model);
			$result_push = $model->table('sj_push')->where(array('id' => $id))->save($data);
			if($result_push)
			{
				$this->writelog("push管理:删除id为{$id}的push,{$log}",'sj_push',$nid,__ACTION__ ,"","del");
				$this->success("删除成功");
			}
			else
			{
				$this->success("删除失败");
			}
		}

    }
	
	//编辑Push显示页面
    function push_edit_show() {
        $model = new Model();
		$model_push = D('Sj.Push');
        $id = $_GET['id'];
		//调用Pushmodel中获取两张表的数据
		$result = $model_push -> getById($id);
        //$result = $model->table('sj_sdk_push')->where(array('id' => $id))->select();
		//echo "<pre>";print_r($result[0]['item_content']);
		$item_content = $result['item_content'];
		 // 展示需要将图片host换上去
        $item_content = htmlspecialchars_decode($item_content);
        $item_content = str_replace(self::HOST_TAG, IMGATT_HOST, $item_content);
        $result['item_content'] = $item_content;
		
		//储存【每日推送时间段】为差值 要恢复回来
		$today = strtotime(date("Y-m-d",time()));
		$result['item_show_time_start']=$today + $result['item_show_time_start'];
		$result['item_show_time_end']=$today + $result['item_show_time_end'];
		
        $this->assign("result", $result);
        $this->assign('function_name', $_GET['from']);
        // 记录页数参数，方便跳回第几页
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($key == 'id' || $key == 'from')
                continue;
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
		$edit_sdk = $model_push->table('sj_push_info_13')->where("id = {$_GET['id']}")->select();//SDK版本
		//echo $model->getlastsql();print_r($edit_sdk);exit;
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
		
		//版本号控制
		$sdk_version_db = json_decode(file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/index.php/Interface/sdk_list'),true);
		$this -> assign('sdk_version_db',$sdk_version_db['list']);
		
		$this->assign('package_arrs',$package_arrs);//手动上传游戏包名
		$this->assign('ignore_arrs',$ignore_arrs);//屏蔽游戏包名
        $this->assign('url_param', $url_param);
        $this->display();
    }

    //编辑push提交
    function push_edit_submit() {
        $model = new Model();
		$model_push = D('Sj.Push');
		//$where['id'] = $_POST['id'];
		$id = $_POST['id'];
		//图片保存 四个判断
		if($_POST['select_phone_dpi'] == 0){
			//1.1 单行 低分图片
			$data['single_item_icon_low'] = $_POST['push_img_low_1_hide'];
			//2.1 多行 低分图片
			$data['multi_item_icon_low'] = $_POST['push_img_high_1_hide'];
			
			//关联数据清空
			//1.2 单行 高分图片
			$data['single_item_icon_high'] = $_POST['push_img_low_2_hide'];
			//2.2 多行 高分图片
			$data['multi_item_icon_high'] = $_POST['push_img_high_2_hide'];
		}else if($_POST['select_phone_dpi'] == 1){
			//1.1 单行 低分图片
			$data['single_item_icon_low'] = $_POST['push_img_low_1_hide'];
			//2.1 多行 低分图片
			$data['multi_item_icon_low'] = $_POST['push_img_high_1_hide'];
			
			//关联数据清空
			//1.2 单行 高分图片
			$data['single_item_icon_high'] = '';
			//2.2 多行 高分图片
			$data['multi_item_icon_high'] = '';
		}else if($_POST['select_phone_dpi'] == 2){
			//1.2 单行 高分图片
			$data['single_item_icon_high'] = $_POST['push_img_low_2_hide'];
			//2.2 多行 高分图片
			$data['multi_item_icon_high'] = $_POST['push_img_high_2_hide'];
			
			//关联数据清空
			//1.1 单行 低分图片
			$data['single_item_icon_low'] = '';
			//2.1 多行 低分图片
			$data['multi_item_icon_low'] = '';
		}
		//print_r($_FILES);exit;
		//判断单行 多行 
		//echo "<pre>";print_r($_FILES);exit;
		if($_FILES['push_img_low_1']['name'] != ''){
			move_uploaded_file($_FILES['push_img_low_1']['tmp_name'], $new_file_path);
			$data['item_icon_low'] = substr($new_file_path,21);
		}else if($_FILES['push_img_low_2']['name'] != ''){
			move_uploaded_file($_FILES['push_img_low_2']['tmp_name'], $new_file_path2);
			$data['item_icon_high'] = substr($new_file_path2,21);
		}else if($_FILES['push_img_high_1']['name'] != ''){
			move_uploaded_file($_FILES['push_img_high_1']['tmp_name'], $new_file_path);
			$data['item_icon_low'] = substr($new_file_path,21);
		}else if($_FILES['push_img_high_2']['name'] != ''){
			move_uploaded_file($_FILES['push_img_high_2']['tmp_name'], $new_file_path2);
			$data['item_icon_high'] = substr($new_file_path2,21);
		}
		//echo $_FILES['push_img']['tmp_name'];
		//echo $new_file_path;exit;
		//echo "<pre>";print_r($_FILES);print_r($_POST);exit;
		//编辑器传过来得内容处理
		if($_POST['push_type'] == 4){
			$module_content = $_POST['editor_content'];
			if (empty($module_content) || $module_content == "<p>
		&nbsp;
	</p>") {
				$this->error("编辑内容不能为空");
			}
			$_POST['editor_content'] = stripcslashes($_POST['editor_content']);
			// 1，将与自己域名相关的图片域名换回约定的标签字符串
			$_POST['editor_content'] = str_replace(IMGATT_HOST, self::HOST_TAG, $_POST['editor_content']);
			// 2，将富文本里的图片发送到服务器并路径内容写成约定标签
			preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $_POST['editor_content'], $matches);

			$pre_path = $_SERVER['DOCUMENT_ROOT'];

			foreach ($matches[1] as $key => $val) {
				$files_name[$key] = str_replace('.', '', microtime(true)) . '_' . PushAction::rand_code(8);
			}
			foreach ($matches[1] as $key => $val) {
				$files[$files_name[$key]] = '@' . $pre_path . $val;
			}
			$arr = PushAction::dev_upload($files);
			if ($arr['ret']) {
				foreach ($arr['ret'] as $key => $val) {
					unset($k, $new_k);
					$k = array_search($key, $files_name);
					$new_k = $matches[1][$k];
					$new_arr[$new_k] = self::HOST_TAG . $val;
				}
				//文章内容中图片路径替换
				$_POST['editor_content'] = strtr($_POST['editor_content'], $new_arr);
			}
		}
		//判断 老用户名 VIP用户
		if($_POST['select_user_type'] == 1){
			$data['select_user_type_ext'] ='';
		}else if($_POST['select_user_type'] == 2){
			$data['select_user_type_ext'] =  implode(',',$_POST['old_user']);
		}else if($_POST['select_user_type'] == 3){
			$data['select_user_type_ext'] =  implode(',',$_POST['user_vip']);
		}
		$val = $_POST;
		$data['select_phone_dpi'] = $val['select_phone_dpi']; //手机分辨率
        $data['title'] = $val['push_title']; //推送标题
        $data['intro'] = $val['push_intro']; //推送简介
        $data['is_repeat'] = $val['push_mode']; //不点击是否重复推送
        $data['select_package_type'] = $val['game_ext']; //sdk关联游戏包类型,1:手动上传,2:全部游戏,3:网络游戏,4:棋牌游戏,5:单机游戏
        $data['select_package_list'] = $val['all_select']; //手动上传的游戏包名，如果多个包名则用英文逗号,隔开
        $data['ignore_package_list'] = $val['all_ignore']; //屏蔽的游戏包名，如果多个包名则用英文逗号,隔开
        $data['select_user_type'] = $val['select_user_type']; //关联用户类型,1:手动上传,2:老用户,3:VIP用户
        $data['select_user_file_path'] = $val['user_save']; //上传用户名文件路径
        //$data['create_time'] = time(); //创建时间
		$data['update_time'] = time(); //修改时间
        $data['start_time'] = strtotime($val['startdate']); //推送开始时间
        $data['end_time'] = strtotime($val['enddate']); //推送结束时间
        $data['status'] = 1; //操作状态,1:正常,0:删除
        $data['pro_status'] = 0; //处理状态，这个后台程序使用，添加和修改都需要把该值置为0,0:未处理,1:已处理
		
        $data['push_type'] = $val['push_type']; //活动类型数字,1:网页,2:礼包,3:活动,4:公告,5:开发者礼包,6:H5游戏
        $data['push_style'] = $val['push_show_type']; //推送样式,1:传统push,2:图片push
        $data['item_title'] = $val['html_title']; //内容标题 俩个标题用这一个
        $data['item_url'] = $val['html_link']; //内容链接地址
        $data['item_open'] = $val['push_browser']; //打开链接的方式，和url属性配合使用(push_type 1webView,2浏览器)
		//防止为NULL的时候报错
		if($data['item_open'] == ''){
			$data['item_open'] = '0';
		}
		if($data['push_type'] == 7){
        	$data['package'] = $val['package_new']; 
        	$data['install_setting'] = $val['install_setting']; 
        	$data['uninstall_setting'] = $val['uninstall_setting']; 
        	$data['start_to_page'] = $val['start_to_page'];
        	$data['download_way'] = $val['download_way'];
        }
        $data['item_objid'] = $val['buy_id']; //礼包或活动ID(push_type 2、3有)//暂时没有增加
        $data['item_content'] = htmlspecialchars($val['editor_content']); //"内容"//公告内容是html文本(push_type 4有)//暂时没有增加        //所选日期得第二天凌晨时间戳：
		
		//H5游戏手机横屏竖屏
		$data['display_method'] = $val['display_method']; 
		
		$today = strtotime(date("Y-m-d"));
		//echo date('Y-m-d H:i:s',$today );exit;
        $data['item_show_time_end'] = strtotime($val['push_time_end']) - $today; //每日推送时间
        $data['item_show_time_start'] = strtotime($val['push_time_start']) - $today; //每日推送时间
		
		//SDK版本 非必填 2017-1-12 
		$data['game_sdk_version_rule'] = $_POST['game_sdk_version_rule'];
		if($_POST['game_sdk_version_rule']=='>=')
		{
			$data['game_sdk_version_code'] = $_POST['game_sdk_version1'];
		}
		else if($_POST['game_sdk_version_rule']=='<=')
		{
			$data['game_sdk_version_code'] = $_POST['game_sdk_version2'];
		}
		else if($_POST['game_sdk_version_rule']=='==')
		{
			//多个版本号去掉前后","
			$data['game_sdk_version_code'] = trim($_POST['force_update_version'],',');
		}

		$data['cps_only'] = intval($_POST['cps_only']);
		//print_r($data);exit;
		
		$log = $this -> logcheck(array('id' =>$id),'sj_push_info_13',$data,$model_push);
		$result = $model_push -> editSdkPush($id,$data);
		//$result = $model->table('sj_sdk_push')->where($where)->save($data);
		// echo $model->getLastSql();exit;
        if ($result !== false) {
            //$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_news_list/from/1');
            //$from = $_POST['from'];
            $this->writelog("push管理:已修改id为{$_POST['id']}的push,{$log}",'sj_push',$_POST['id'],__ACTION__ ,"","edit");
            $this->assign('jumpUrl', "/index.php/Sj/Push/push_news_released_list");
            $this->success("修改成功");
        }else{
			$this->error("修改失败");
		}
    }
	//上传用户名
	function get_user(){
		$tmp_name = $_FILES['user_path']['tmp_name'];
		$tmp_houzhui = $_FILES['user_path']['name'];
		$file_type = explode('.', $tmp_houzhui);
		if ($file_type[1] != 'csv') {
			echo 2;
			return 2;
            exit;
		}
		$shili = fopen($tmp_name, "r");  
		while (!feof($shili)) {
			$shi = fgets($shili, 1024);
			if($shi){
				$str .= $shi.',';
			}
		}
		$str = substr($str,0,-1);
		$strss = explode("\r\n,",$str);
		$strss = array_unique($strss);
		$all_user = count($strss);
		$user_file = $_FILES['user_path'];
		$upload_path = date('Ym/d');
		$dir_paths = time();
		$rand = mt_rand(111111,999999);
		$dir_path = C("MARKET_PUSH_CSV") . '/' . $upload_path;
		$dir_pathss = C("MARKET_PUSH_CSV") . '/' . $upload_path . '/' . $dir_paths . $rand . '.' . $file_type[1];
		if(!file_exists($dir_path)){
			mkdir($dir_path,0777,true);
		}
		if($_FILES['user_path']['size']){
			move_uploaded_file($_FILES['user_path']['tmp_name'],$dir_pathss);
		}
		$dir_pathss2 = substr($dir_pathss,23);//数据库储存 原路径/data/att/518/push_file/201508/27/1440643045462059.csv
		$all_users = array($all_user,$dir_pathss2);
		echo json_encode($all_users);
		return json_encode($all_users);
	}
	 //图片处理,代码来源:/dev.goapk.com/common.php
    //上传图片
    public static function dev_upload($files) {
        $vals = array(
            'do' => 'save',
            'static_data' => '/data/att/m.goapk.com',
        );
        return PushAction::_http_post(array_merge($vals, $files));
    }
	//摘自tools/ClsFactory.php中http_post函数
    public static function _http_post($vals) {
		$pro_env = C('PRO_ENV');
		if($pro_env == 1){
			//线上
			$host = '192.168.1.18';
			$host_dam = 'Host: dummy.goapk.com';
		}else if($pro_env == 2){
			$host = 'dummy.goapk.com';
			$host_dam = 'Host: dummy.goapk.com';
		}else if($pro_env == 3||$pro_env == 4){
			$host = '192.168.0.99';
			$host_dam = 'Host: 9.dummy.goapk.com';
		}

        $res = curl_init();
        curl_setopt($res, CURLOPT_URL, "http://${host}/service_dev.php");
        curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
        $result = curl_exec($res);

        $info = curl_getinfo($res);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);

        return array('ret' => json_decode($result, true), 'info' => $info, 'errno' => $errno, 'error' => $error);
    }
	//编辑器内上传图片问题
	public static function rand_code($num) {
        $str = '';
        for ($i = 0; $i < $num; $i++) {
            $str .= mt_rand(0, 9);
        }
        return $str;
    }
	/* lzf_end */
}