<?php
/**
 * 推荐管理-活动专区
 * Added by 黄文强
 * 2013/05/13
 */
class ActivityAction extends CommonAction {
	
	private $image_width=480;
	private $image_height=181;
	private $image_width_high=684;
	private $image_height_high=231;
	private $image_width_low=444;
	private $image_height_low=150;
	/**
	 * 展示活动列表
	 */
	public function showActivityList() {
		$data = array();
		$activity = D('Sj.Activity');
		$model = new Model();
		
		//百度下拉效果
		if($this->isAjax())
		{
			$keyword = $_GET['query'];
//activity_type_bank 2通用3集赞4常规返利5充值消费6新预约7运营预下载8签到模板9评论可回复模板
//activate_type活动类型,1:普通,2:多软件3广告4通用5集赞7常规充值8排行榜9新预约10对外预约11运营预下载12签到13红包活动14评论可回复活动15会员折扣活动 16流量活动
			$activity_type_bank = intval($_GET['activity_type_bank']);
			$activate_type = array(
				2 => 4,
				3 => 5,
				4 => 7,
				5 => 8,
				6 => 9,
				7 => 11,
				8 => 12,
				9 => 14,
				10 => 15,
			    11=>16
			);
			
			$where = array(
				'activate_type' => $activate_type[$activity_type_bank],
				'ap_name' =>  array('like', "%{$keyword}%"),
				'ap_type' => $_GET['ap_type'],
				'status' => 1
			);
			$activity_name = $model -> table('sj_activity_page') -> where($where)->field('ap_name')->select();
			$data = array(
					'query' => $keyword,
					'suggestions' => array(),
			);
			foreach($activity_name as $v) {
					$data['suggestions'][] = $v['ap_name'];
			}
			exit(json_encode($data));
		}
		$labels = $model -> table('sj_active_filter_label') ->field('id,label_name')-> where(array('status' => 1)) -> select();
		$this -> assign("labels",$labels);
		if(!$_GET){
			$this->display('showActivtyList');
			EXIT;
		}
		//var_dump($activity);
		//var_dump(get_class_methods($activity));
		//初始化查询条件
		// echo "<pre>";var_dump($_GET);die;
		$options = array();
		$options['status'] = 1;
		if(isset($_GET['activityName']) && $_GET['activityName'] != "")
		{
			$options['name'] = array('like',"%".$_GET['activityName']."%");
		}
		if(isset($_GET['id']) && $_GET['id'] != "")
		{
			$options['id'] = $_GET['id'];
			$this->id = $_GET['id'];
		}
		$options['activity_category'] = array('exp','!=3');
		if(isset($_GET['activity_category']) && $_GET['activity_category'] != "")
		{
			if($_GET['activity_category'] != 0){
				$options['activity_category'] = $_GET['activity_category'];
			}
			$this->activity_category = $_GET['activity_category'];
		}
		if(isset($_GET['startDate']) && $_GET['startDate'] != "")
		{
			$start = strtotime($_GET['startDate']);
			$options['start_tm'] = array('egt',$start);
		}
		if(isset($_GET['filter_label_id']) && $_GET['filter_label_id'] != "")
		{
			$options['filter_label_id'] = $_GET['filter_label_id'];
		}
		if(isset($_GET['endDate']) && $_GET['endDate'] != "")
		{
			$end = strtotime($_GET['endDate']." 23:59:59");
			$options['end_tm'] = array('elt',$end);
		}
		if(isset($_GET['is_start']) && $_GET['is_start'] != "")
		{
			if($_GET['is_start']!=3){
				$options['is_start'] = $_GET['is_start'];
			}else{
				$options['is_start'] = array('in',array(1,2));
			}
		}
		if(isset($_GET['activitySite']) && $_GET['activitySite'] != "")
		{
			$options['url'] = array('like',"%".str_ireplace("$","/",$_GET['activitySite'])."%");
		}
		$this->url_subff = $this->get_url_suffix(array('activityName','startDate','endDate','activitySite','p','lr'));
		$this->activityName = $_GET['activityName'];
		$this->startDate = $_GET['startDate'];
		$this->endDate = $_GET['endDate'];
		$this->is_start = $_GET['is_start'];
		$this->filter_label_id = $_GET['filter_label_id'];
		$this->activitySite = str_ireplace("$","/",$_GET['activitySite']);
// 		$data = $activity->getActivityList($options);
		import("@.ORG.Page");
		$model = M('sj_activity');
		$count = $model ->table('sj_activity') ->where($options)->count();

		$Page = new Page($count, 10);
		$show = $Page->show();
// 		foreach ($data as $k => $v)
// 		{
// 			$data[$k]['cururl'] = $data[$k]['url'];
// 			if (time() < $data[$k]['start_tm']) {
// 				$data[$k]['cururl'] = $data[$k]['pre_url'];
// 			} else if (time() > $data[$k]['end_tm']) {
// 				$data[$k]['cururl'] = $data[$k]['end_url'];
// 			}
// 			$data[$k]['start_tm'] = date('Y-m-d', $v['start_tm']);
// 			$data[$k]['end_tm'] = date('Y-m-d', $v['end_tm']);
// 			$data[$k]['show_start_tm'] = date('Y-m-d', $data[$k]['show_start_tm']);
// 			$data[$k]['show_end_tm'] = date('Y-m-d', $data[$k]['show_end_tm']);
// 		}
		//$this->assign('list', $data);
		$this->assign('page', $show);
// 		$arr =$activity->arr_arr($options);
		$this->assign('count',$count);
		//分段排序
		$order = "IF (
			start_tm <= UNIX_TIMESTAMP(NOW())  AND UNIX_TIMESTAMP(NOW()) < end_tm, pos, 
			IF( UNIX_TIMESTAMP(NOW()) <= start_tm ,
				pos + POW(2, 40), 
				end_tm * (-1) + POW(2, 41)
			  )
        ),IF (
			start_tm <= UNIX_TIMESTAMP(NOW())  AND UNIX_TIMESTAMP(NOW()) < end_tm, start_tm ,start_tm
        )";
		$arr = $model ->table('sj_activity') ->where($options)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
	//	echo $model->getlastsql();
		//获取合作样式列表
		$util = D("Sj.Util"); 
		foreach($arr as $key => $val){
			$cid_str = explode(',',$val['channel_id']);
			$channel_str = '';
			foreach($cid_str as $k => $v){
				if($val){
					$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> find();
					if($chname_result){
						$channel_str .= $chname_result['chname'].',';
					}
				}
			}
			$label = $model -> table('sj_active_filter_label') -> where(array('id' => $val['filter_label_id'],'status'=>1)) -> find();
			$arr[$key]['filter_label_id']=$label['label_name']?$label['label_name']:'无';
			if($val['version_type']==3)
			{
			 $arr[$key]['version_code']=trim($val['version_code'], ',');
			}
			if($val['version_type']==1)
			{
			 $arr[$key]['version_code']="大于等于".$val['version_code'];
			}
			if($val['version_type']==2)
			{
			 $arr[$key]['version_code']="小于等于".$val['version_code'];
			}
			$arr[$key]['chname'] = substr($channel_str,0,-1);
			$arr[$key]['end_tm_str'] = $val['end_tm'];
			$arr[$key]['start_tm_str'] = $val['start_tm'];
			$arr[$key]['end_tm'] = date('Y-m-d H:i:s',$val['end_tm']);
			$arr[$key]['start_tm'] = date('Y-m-d H:i:s',$val['start_tm']);
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($val['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$arr[$key]['co_types'] = $v[0];
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
			$lr = 15;
		}
		// echo "<pre>";var_dump($arr);die;
		$this->assign('p', $p);
		$this->assign('lr', $lr);
		$this->assign('list', $arr);
		$this->display('showActivtyList');
	}

	/*import("@.ORG.Page");
	$count = $model ->table('sj_new_server')->where(" begin > ".$time)->count();
	$Page = new Page($count, 10);
	$show = $Page->show();
	$this->assign('page', $show);

	$this->assign('count',$count);
	$this->assign('value',$arr);
	$this ->display();*/

	public function addActivity_show(){
		$model = new Model();
		$this->url_subff = $this->get_url_suffix(array('activityName','startDate','endDate','package','activitySite','p','lr'));
		$where['_string'] = "activate_type in(4,5,7,8,9,10,11)  and status = 1";
		$all_activity_page = $model -> table('sj_activity_page') -> where($where) -> field('ap_id,ap_name') -> order('ap_ctm DESC') -> select();
		//百度下拉效果
		if($this->isAjax())
		{
			$keyword = $_GET['query'];
			$activity_name =$model->query("select activity_name from sj_pre_download where status=1 and activity_name like '%{$keyword}%'");

			$data = array(
					'query' => $keyword,
					'suggestions' => array(),
			);
			foreach($activity_name as $v) {
					$data['suggestions'][] = $v['activity_name'];
			}
			exit(json_encode($data));
		}
		$pre_page = $model -> table('sj_pre_download') -> where(array('status' => 1)) -> select();
		$this -> assign("pre_page",$pre_page);
		$this -> assign("all_activity_page",$all_activity_page);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);

		$labels = $model -> table('sj_active_filter_label') ->field('id,label_name')-> where(array('status' => 1)) -> select();
		$this -> assign("labels",$labels);
		
		$this -> assign("image_width",$this->image_width);
		$this -> assign("image_height",$this->image_height);
		$this -> assign("image_width_high",$this->image_width_high);
		$this -> assign("image_height_high",$this->image_height_high);
		$this -> assign("image_width_low",$this->image_width_low);
		$this -> assign("image_height_low",$this->image_height_low);
		$this -> display();
	}
	
	/**
	 * 添加活动
	 */
	public function addActivity() {
	    
	    
		$data = array();
		$model = new Model();
		$activity = D('Sj.Activity');
		$channel_arr = $_POST['cid'];
		foreach($channel_arr as $key => $val){
			$cid_str_go .= $val.',';
		}
		$cid_str = ','.$cid_str_go;
		
		if($channel_arr){
			$data['channel_id'] = $cid_str;
		}

		if (empty($_POST['act_name'])){
			$this->error('名称不能为空');
		}else{
			$data['name'] = trim($_POST['act_name']);
			if (mb_strlen(iconv('utf-8', 'gbk', $data['name'])) > 30)
				$this->error('名称不能多于15个字');
		}

		$have_been = $activity -> where(array('name' => $data['name'],'status' => 1)) -> select();
		if($have_been){
			$this -> error("该活动名称已存在");
		}
		//合作形式
		if(isset($_POST['co_type'])){
			$data['co_type'] = $_POST['co_type'];
		}else{
			$data['co_type'] = 0;
		}	
		
		//活动分类 1是游戏活动 2是应用活动
		if(isset($_POST['activity_category']))
		{
			$data['activity_category'] = $_POST['activity_category'];
		}
		if($_POST['activity_type'] == 2 || $_POST['activity_type'] == 1 || $_POST['activity_type'] == 4){
		    
			if($_POST['checkpackage']) {
				$soft_result = $activity -> table('sj_soft') -> where(array('package' => $_POST['checkpackage'],'status' => 1,'hide' => 1)) -> select();
				if(!$soft_result){
					$this -> error("软件包名不存在");
				}
				$data['package'] = trim($_POST['checkpackage']);
			}else{
				if($_POST['package']){
					$soft_result = $activity -> table('sj_soft') -> where(array('package' => $_POST['package'],'status' => 1,'hide' => 1)) -> select();
					if(!$soft_result){
						$this -> error("软件包名不存在");
					}
					$data['package'] = trim($_POST['package']);
				}
			} 
		}
		if (!trim($_POST['act_url'])){
			if(($_POST['activity_type'] == 1 && $_POST['activity_type_bank'] == 1) || $_POST['activity_type'] == 2 || $_POST['activity_type'] == 3){
				$this->error('活动网址不能为空');
			}
		}else{
			$data['url'] = trim($_POST['act_url']);
			//$my_url = explode('/',$data['url']);
			$activity_page_id_result = $model -> table('sj_activity_page') -> where(array('ap_link' => $data['url'],'status' => 1)) -> select();
		}
		if($_POST['url_list'] != 'on'){
			if (!trim($_POST['act_pre_url'])){
				if(($_POST['activity_type'] == 1 && $_POST['activity_type_bank'] == 1) || $_POST['activity_type'] == 2 || $_POST['activity_type'] == 3){
					$this->error('预告网址不能为空');
				}
			}else{
				$data['pre_url'] = trim($_POST['act_pre_url']);
				//$my_url = explode('/',$data['pre_url']);
				$activity_page_prepare_result = $model -> table('sj_activity_page') -> where(array('ap_link' => $data['pre_url'],'status' => 1)) -> select();
			}
			$data['url_list'] = 0;
		}else{
			$data['url_list'] = 1;
		}
		if($_POST['pos']){
			$data['pos'] = $_POST['pos'];		
		}	
		if (!trim($_POST['act_end_url'])){
			if(($_POST['activity_type'] == 1 && $_POST['activity_type_bank'] == 1) || $_POST['activity_type'] == 2 || $_POST['activity_type'] == 3){
				$this->error('结束网址不能为空');
			}
		}else{
			$data['end_url'] = trim($_POST['act_end_url']);
			//$my_url = explode('/',$data['end_url']);
			$activity_page_end_result = $model -> table('sj_activity_page') -> where(array('ap_link' => $data['end_url'],'status' => 1)) -> select();
		}


		if (empty($_FILES['act_img_444_150']['size']))	$this->error('请上传有效低分图片');
		if (empty($_FILES['act_img_604_204']['size']))	$this->error('请上传有效高分图片');

		if (empty($_FILES['act_img']['size']))
			$this->error('请上传有效图片');
		else
			$img = $_FILES['act_img'];

		if (empty($_POST['act_start_tm']))
			$this->error('开始时间不能为空');
		else
			$data['start_tm'] = strtotime($_POST['act_start_tm']);

		if (empty($_POST['act_start_tm']))
			$this->error('结束时间不能为空');
		else
			$data['end_tm'] = strtotime($_POST['act_end_tm']);

		if ($data['end_tm'] < $data['start_tm'])
			$this->error('结束时间不能小于开始时间');

		//if (empty($_POST['act_start_tm']))
		//	$this->error('展示开始时间不能为空');
		//else
		//	$data['show_start_tm'] = strtotime($_POST['act_show_start_tm']);
        //
		//if (empty($_POST['act_start_tm']))
		//	$this->error('展示结束时间不能为空');
		//else
		//	$data['show_end_tm'] = strtotime($_POST['act_show_end_tm']);
		$intro = $_POST['intro'];
		if(!$intro){
			$this -> error("请填写奖品介绍");
		}
		if(mb_strlen(iconv('utf-8', 'gbk', $_POST['intro'])) > 40){
			$this -> error("奖品介绍需在20字以内");
		}else{
			$data['intro'] = $intro;
		}
		$path = date("Ym/d/");
		$config = array();

		$image = getimagesize($_FILES['act_img']['tmp_name']);
		if($image[0] != $this->image_width || $image[1] != $this->image_height){
			$this -> error("图片：:请上传宽度为{$this->image_width},高度为{$this->image_height}的图片");
		}
		$image_604_204 = getimagesize($_FILES['act_img_604_204']['tmp_name']);
		if($image_604_204[0] != $this->image_width_high || $image_604_204[1] != $this->image_height_high){
			$this -> error("6.0高分图片：:请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}图片");
		}
		$image_444_150 = getimagesize($_FILES['act_img_444_150']['tmp_name']);
		if($image_444_150[0] != $this->image_width_low || $image_444_150[1] != $this->image_height_low){
			$this -> error("6.0低分图片：:请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
		}	
		$config['multi_config']['act_img'] = array(
			'savepath' => UPLOAD_PATH. '/img/'. $path,
			'saveRule' => 'getmsec',
			'img_p_width' => $this->image_width,
			'img_p_height' => $this->image_height,
			'img_p_size' => 1024 * 35,
		);		
		$config['multi_config']['act_img_604_204'] = array(
			'savepath' => UPLOAD_PATH. '/img/'. $path,
			'saveRule' => 'getmsec',
			'img_p_size' => 1024 * 80,
		);
		$config['multi_config']['act_img_444_150'] = array(
			'savepath' => UPLOAD_PATH. '/img/'. $path,
			'saveRule' => 'getmsec',
			'img_p_size' => 1024 * 40,
		);			
		$config['multi_config']['the_package'] = array(
			'savepath' => UPLOAD_PATH. '/sdk/'. $path,
			'saveRule' => 'getmsec',
		);
		$config['multi_config']['the_ignore_package'] = array(
			'savepath' => UPLOAD_PATH. '/sdk/'. $path,
			'saveRule' => 'getmsec',
		);
		$config['multi_config']['user_path'] = array(
			'savepath' => UPLOAD_PATH. '/sdk/'. $path,
			'saveRule' => 'getmsec',
		);	
		if (!empty($config['multi_config'])) {
			$list = $this->_uploadapk(0, $config);

			foreach($list['image'] as $val) {
				if ($val['post_name'] == 'act_img') {
					$data['imgurl'] = $val['url'];
				}
				if ($val['post_name'] == 'act_img_444_150') {
					$data['low_image_url'] = $val['url_original'];
					$data['low_image_url_40'] = $val['url'];
				}
				if ($val['post_name'] == 'act_img_604_204') {
					$data['high_image_url'] = $val['url_original'];
					$data['high_image_url_80'] = $val['url'];
				}
				
			}
		}
		$data['activity_type'] = $_POST['activity_type'];
		$data['version_type'] = $_POST['type'];
		if($_POST['type']==1)
		{
		 $data['version_code'] = $_POST['version_code1'];
		}
		if($_POST['type']==2)
		{
		 $data['version_code'] = $_POST['version_code2'];
		}
		if($_POST['type']==3)
		{
		 $data['version_code'] = $_POST['force_update_version'];
		}
        
		if (empty($data['imgurl']))
			$this->error('图片保存失败');
		$url_subff = $_POST['url_subff'];
		
		if($_POST['activity_type'] == 1){
			$data['activity_type_bank'] = $_POST['activity_type_bank'];
			$arr =  array(2,3,4,5,6,7,8,9,10,11);
			if(in_array($_POST['activity_type_bank'],$arr)){
				$activate_type_arr = array(
					2 => 4,
					3 => 5,
					4 => 7,
					5 => 8,
					6 => 9,
					7 => 11,
					8 => 12,
					9 => 14,
					10 => 15,
				    11=>16
				);
				$activate_type = $activate_type_arr[$_POST['activity_type_bank']];
				if($_POST['url_list'] != 'on'){
					$pre_coactivity = $_POST['pre_coactivity'];
					$pre_result = $model -> table('sj_activity_page') -> where(array('ap_name' => $pre_coactivity,'status' => 1)) -> select();
					if(!$pre_result && $pre_coactivity){
						$this -> error("预告页名称不存在");
					}else{
						$data['activity_prepare_id'] = $pre_result[0]['ap_id'];
					}
				}
				$bgn_coactivity = $_POST['bgn_coactivity'];
				$bgn_result = $model -> table('sj_activity_page') -> where(array('ap_name' => $bgn_coactivity,'status' => 1,'activate_type'=>$activate_type)) -> select();
				if(!$bgn_result){
					$this -> error("活动页名称不存在");
				}else{
					$data['activity_page_id'] = $bgn_result[0]['ap_id'];
				}
				$end_coactivity = $_POST['end_coactivity'];
				$end_result = $model -> table('sj_activity_page') -> where(array('ap_name' => $end_coactivity,'status' => 1,'activate_type'=>$activate_type)) -> select();
				if(!$end_result){
					$this -> error("结束页名称不存在");
				}else{
					$data['activity_end_id'] = $end_result[0]['ap_id'];
				}
				if($page_result['activate_type'] == 5){
					$page_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $data['activity_page_id'])) -> find();
					$page_start = $page_result['start_tm'];
					$page_end = $page_result['end_tm'];
					if($page_start < $data['start_tm'] || $page_end > $data['end_tm']){
						$this -> error("点赞时间必须在活动时间之内");
					}
				}
			}
			if($_POST['activity_type_bank'] == 2){
				$data['url'] = ACTIVITY_URL . '/lottery/coactivity_lottery.php?cbm=1';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/coactivity_prepare.php?cbm=1';
				$data['end_url']  = ACTIVITY_URL . '/lottery/coactivity_end.php?cbm=1';
			}elseif($_POST['activity_type_bank'] == 3){
				$data['url'] = ACTIVITY_URL . '/lottery/setlike_lottery.php?cbm=1';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/coactivity_prepare.php?cbm=1';
				$data['end_url']  = ACTIVITY_URL . '/lottery/setlike_lottery.php?cbm=1';
			}elseif($_POST['activity_type_bank'] == 4){
				$data['url'] = ACTIVITY_URL . '/lottery/routine_rebate_index.php?cbm=1';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/coactivity_prepare.php?cbm=1';
				$data['end_url']  = ACTIVITY_URL . '/lottery/routine_rebate_index.php?cbm=1&stop=1';
			}elseif($_POST['activity_type_bank'] == 5){
				$data['url']  =  ACTIVITY_URL.'/lottery/ranking/index.php?cbm=1';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/coactivity_prepare.php?cbm=1';
				$data['end_url']  =  ACTIVITY_URL.'/lottery/ranking/index.php?cbm=1&stop=1';
			}elseif($_POST['activity_type_bank'] == 6){
				$data['url']  =  ACTIVITY_URL.'/lottery/appointment/index.php?cbm=1';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/coactivity_prepare.php?cbm=1';
				$data['end_url']  =  ACTIVITY_URL.'/lottery/appointment/end.php?cbm=1';
				//$data['end_url']  = ACTIVITY_URL . '/lottery/coactivity_end.php?cbm=1';
			}elseif($_POST['activity_type_bank'] == 7){
				$data['url']  =   ACTIVITY_URL . '/lottery/pre_down_operation/index.php?cbm=1';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/coactivity_prepare.php?cbm=1';
				$data['end_url']  = ACTIVITY_URL . '/lottery/pre_down_operation/index.php?cbm=1&stop=1';
			}elseif($_POST['activity_type_bank'] == 8){
				$data['url']  =   ACTIVITY_URL . '/lottery/sign/index.php?cbm=1';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/coactivity_prepare.php?cbm=1';
				$data['end_url']  = ACTIVITY_URL . '/lottery/sign/index.php?cbm=1&stop=1';
			}elseif($_POST['activity_type_bank'] == 9){
				$data['url']  =   ACTIVITY_URL . '/lottery/cmt_reply/index.php';
				$data['pre_url']  = ACTIVITY_URL . '/lottery/cmt_reply/pre.php';
				$data['end_url']  = ACTIVITY_URL . '/lottery/cmt_reply/end.php';
			}elseif($_POST['activity_type_bank'] == 10){
				$data['url']  =   ACTIVITY_URL . '/lottery/vip/vip_discount.php?cbm=1';
				$data['end_url']  = ACTIVITY_URL . '/lottery/vip/vip_discount.php?cbm=1&stop=1';
			}elseif($_POST['activity_type_bank'] == 11){
				$data['url']  =   ACTIVITY_URL . '/lottery/flow/index_temp.php';
				$data['end_url']  = ACTIVITY_URL . '/lottery/flow/index_temp.php';
			}
		}elseif($_POST['activity_type'] == 4){
			if($_POST['url_list'] != 'on'){
				$pre_name = $_POST['pre_desc'];
				$pre_result = $model -> table('sj_pre_download') -> where(array('activity_name' => $pre_name,'status' => 1)) -> select();
				if(!$pre_result && $pre_name){
					$this -> error("预告页名称不存在");
				}else{
					$data['activity_prepare_id'] = $pre_result[0]['id'];
				}
			}
			$bgn_name = $_POST['bgn_desc'];
			$bgn_result = $model -> table('sj_pre_download') -> where(array('activity_name' => $bgn_name,'status' => 1)) -> select();
			if(!$bgn_result){
				$this -> error("活动页名称不存在");
			}else{
				$data['activity_page_id'] = $bgn_result[0]['id'];
			}
			$end_name = $_POST['end_desc'];
			$end_result = $model -> table('sj_pre_download') -> where(array('activity_name' => $end_name,'status' => 1)) -> select();
			if(!$end_result){
				$this -> error("结束页名称不存在");
			}else{
				$data['activity_end_id'] = $end_result[0]['id'];
			}
			$data['url'] = ACTIVITY_URL . '/lottery/pre_download.php';
			$data['pre_url']  = ACTIVITY_URL . '/lottery/pre_download.php';
			$data['end_url']  = ACTIVITY_URL . '/lottery/pre_download.php';
		}
		
		$platform_arr = $_POST['platform'];
		if(!$platform_arr){
			$this -> error("请选择投放平台");
		}
		$the_platform = implode(',',$platform_arr);
		$data['platform'] = $the_platform;
		if(in_array(13,$platform_arr)){
			$data['sdk_type'] = $_POST['sdk_type'];
			// if(!trim($_POST['sdk_ac_rank'])){
			// 	$this -> error("优先级必填");
			// }else if(!preg_match("/^[1-9]\d*$/",$_POST['sdk_ac_rank'])){
			// 	$this -> error("优先级必须为正整数");
			// }
			// $data['sdk_ac_rank'] = trim($_POST['sdk_ac_rank']);
			$data['sdk_ac_type'] = $_POST['sdk_ac_type'];

		}else{
			// $data['sdk_ac_rank'] = '';
			$data['sdk_ac_type'] = '';
		}
		if($_POST['sdk_type'] == 3||$_POST['sdk_type'] == 4){
			$data['vip_jump'] = $_POST['vip_jump'];
		}else{
			$data['vip_jump'] = 0;
		}
		if(in_array(13,$platform_arr) && $data['sdk_type'] == 1){
			if(empty($_POST['all_select']) && $_POST['select_package_type'] == 1){
				$this -> error("请上传游戏范围");
			}
			if(!$_POST['select_package_type'] && !$_POST['select_user_type']){
				$this -> error("请选择游戏范围或用户名范围");
			}
			if($_POST['select_user_type'] == 1 && !$_POST['user_save']){
				$this -> error("请上传用户名");
			}
			if($_POST['select_user_type'] == 2){
				$select_user_type_ext = $_POST['select_user_type_ext'];
				if(!$select_user_type_ext){
					$this -> error("请选择老用户范围");
				}
				foreach($select_user_type_ext as $key => $val){
					$select_user_type_ext_str_go .= $val . ',';
				}
				$select_user_type_ext_str = substr($select_user_type_ext_str_go,0,-1);
				$data['select_user_type_ext'] = $select_user_type_ext_str;
			}
		}
		$data['select_package_type'] = $_POST['select_package_type'];
		$data['select_package_list'] = $_POST['all_select'];
		if($data['select_package_type'] != 1){
			$data['ignore_package_list'] = $_POST['all_ignore'];
		}
		$data['select_user_type'] = $_POST['select_user_type'];
		$data['select_user_file_path'] = $_POST['user_save'];
		$data['filter_label_id'] = $_POST['filter_label_id'];
                if($_POST['activity_type_bank']==1){
                    if(empty($_POST['page_id'])){
		        $this -> error("请填写页面id");
                    }
		    $data['activity_page_id'] = $_POST['page_id'];
		    $data['activity_prepare_id'] = $_POST['page_id'];
		    $data['activity_end_id'] = $_POST['page_id'];
                }
        if($_POST['package']){
        	//屏蔽软件上排期时报警需求 新增  yuesai
	        $AdSearch = D("Sj.AdSearch");
	        $shield_error=$AdSearch->check_shield($_POST['package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
				$this -> error($shield_error);
			}
        }
        $data['cover_user_type'] = $_POST['cover_user_type'];
        $data['activation_date_start'] = $_POST['activation_date_start']?strtotime($_POST['activation_date_start']):0;
        $data['activation_date_end'] = $_POST['activation_date_end']?strtotime($_POST['activation_date_end']):0;
		if ($my_result = $activity->addActivity($data))
		{
			//默认添加谢谢参与	
			$activity_model = D('Sj.CoActivity');
			$activity_model->add_no_win($my_result,$_POST['activity_type_bank']);
			$this->writelog('添加了活动id为'.$my_result,'sj_activity',$my_result,__ACTION__ ,'','add');
			$this -> assign("jumpUrl",'/index.php/Sj/Activity/showActivityList'.$url_subff);
			$this->success('添加成功');
		}
		else
		{
			$this->error('添加失败');
		}
	}
	//展示编辑弹出层
	public function edit_test_show() {

		$model = M('sj_activity');
		$id =$_GET['id'];
		$result = $model -> table('sj_activity') -> where(array('id' => $id)) -> select();
		$my_channel = explode(',',$result[0]['channel_id']);
		foreach($my_channel as $key => $val){
			$chname_result = $model -> table('sj_channel') -> where(array('cid' => $val)) -> select();
			$vals['chname'] = $chname_result[0]['chname'];
			$vals['cid'] = $val;
			$my_channels[] = $vals;
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
		$where['_string'] = "(activate_type = 4 or activate_type = 5)  and status = 1";
		$all_activity_page = $model -> table('sj_activity_page') -> where($where) -> field('ap_id,ap_name') -> order('ap_ctm DESC') -> select();
		$pre_page = $model -> table('sj_pre_download') -> where(array('status' => 1)) -> select();
		$this -> assign("pre_page",$pre_page);
		$this -> assign("all_activity_page",$all_activity_page);
		$this->url_subff = $this->get_url_suffix(array('id','activityName','startDate','endDate','package','activitySite','p','lr'));
		$arr =  array(2,3,4,5,6,7,8,9,10,11);	
		foreach($result as $key => $val){
			if($val['activity_type'] == 1 && (in_array($val['activity_type_bank'],$arr))){
				$pre_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $val['activity_prepare_id'])) -> find();
				$val['pre_name'] = $pre_result['ap_name'];
				$bgn_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $val['activity_page_id'])) -> find(); 
				$val['bgn_name'] = $bgn_result['ap_name'];
				$end_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $val['activity_end_id'])) -> find(); 
				$val['end_name'] = $end_result['ap_name'];
			}elseif($val['activity_type'] == 4){
				$pre_result = $model -> table('sj_pre_download') -> where(array('id' => $val['activity_prepare_id'])) -> find();
				$val['pre_name'] = $pre_result['activity_name'];
				$bgn_result = $model -> table('sj_pre_download') -> where(array('id' => $val['activity_page_id'])) -> find(); 
				$val['bgn_name'] = $bgn_result['activity_name'];
				$end_result = $model -> table('sj_pre_download') -> where(array('id' => $val['activity_end_id'])) -> find(); 
				$val['end_name'] = $end_result['activity_name'];
			}
			$result[$key] = $val;
		}
		if($result[0]['ignore_package_list']){
			$ignore_arr = explode(',',$result[0]['ignore_package_list']);
			foreach($ignore_arr as $key => $val){
				$vals[0] = $val;
				$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
				$vals[1] = $soft_result[0]['softname'];
				$ignore_package[] = $vals;
			}
			$this -> assign('ignore_package',$ignore_package);
		}

		if($result[0]['select_package_list']){
			$select_arr = explode(',',$result[0]['select_package_list']);
			foreach($select_arr as $key => $val){
				$vals[0] = $val;
				$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
				$vals[1] = $soft_result[0]['softname'];
				$select_package[] = $vals;
			}
			$this -> assign('select_package',$select_package);
		}
		if($result[0]['select_user_type_ext']){
			$select_user_type_ext_arr = explode(',',$result[0]['select_user_type_ext']);
			$this -> assign('select_user_type_ext_arr',$select_user_type_ext_arr);
		}
		$user_path = C("MARKET_PUSH_CSV") . '/' .$result[0]['select_user_file_path'];
		$shili = fopen($user_path, "r"); 
		if($shili){
			while (!feof($shili)) {
				$shi = fgets($shili, 1024);
				$str .= $shi.',';
			}
			$str = substr($str,0,-1);
			$strss = explode("\r\n,",$str);
			$all_user = count($strss);
			$this->assign('user_count',$all_user);
		}
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($result[0]['co_type']);
		$this->assign('typelist',$typelist);

		$platform_arr = explode(',',$result[0]['platform']);
		$this->assign('platform_arr',$platform_arr);
		$this->assign('p', $p);
		$this->assign('lr', $lr);
        $this -> assign('package',$package);
		$this -> assign('my_channels',$my_channels);
		$this -> assign('result',$result);
		$labels = $model -> table('sj_active_filter_label') ->field('id,label_name')-> where(array('status' => 1)) -> select();
		$this -> assign("labels",$labels);
		$this -> assign("image_width",$this->image_width);
		$this -> assign("image_height",$this->image_height);
		$this -> assign("image_width_high",$this->image_width_high);
		$this -> assign("image_height_high",$this->image_height_high);
		$this -> assign("image_width_low",$this->image_width_low);
		$this -> assign("image_height_low",$this->image_height_low);
		$this -> display();
	}

	/**
	 * 编辑活动
	 */
	public function editActivity() {
		$data = array();
		$model = new Model();
		$time = time();
		//var_dump($_FILES);
		$channel_arr = $_POST['cid'];
		foreach($channel_arr as $key => $val){
			$cid_str_go .= $val.',';
		}
		$cid_str = ','.$cid_str_go;
		if(!$channel_arr){
			$cid_str = '';
		}
		//合作形式
		if(isset($_POST['co_type'])){
			$data['co_type'] = $_POST['co_type'];
		}else{
			$data['co_type'] = 0;
		}
		
		$url_subff = $_POST['url_subff'];
		$data['channel_id'] = $cid_str;
		$activity = D('Sj.Activity');
		$a = $_POST['editid'];
		if (empty($_POST['editid']))
			$this->error('参数错误，请重试');
		else
			$data['id'] = $_POST['editid'];

		if (empty($_POST['act_name']))
			$this->error('名称不能为空');
		else
		{
			$data['name'] = $_POST['act_name'];
			if (mb_strlen(iconv('utf-8', 'gbk', $data['name'])) > 30)
				$this->error('名称不能多于15个字');
		}
		$have_where['_string'] = "name = '{$data['name']}' and status = 1 and id != {$data['id']}";
		$have_been = $activity -> where($have_where) -> select();
		
		if($have_been){
			$this -> error("该活动名称已存在");
		}
		if($_POST['checkpackage']) {
			$data['package'] = trim($_POST['checkpackage']);
		}
		if($_POST['package']){
			$soft_result = $activity -> table('sj_soft') -> where(array('package' => $_POST['package'],'status' => 1,'hide' => 1)) -> select();
			if(!$soft_result){
				$this -> error("软件包名不存在");
			}  else {
				$data['package'] = trim($_POST['package']);
			}
		}
		
		if (!trim($_POST['act_url'])){
			if(($_POST['activity_type'] == 1 && $_POST['activity_type_bank'] == 1) || $_POST['activity_type'] == 2 || $_POST['activity_type'] == 3){
				$this->error('活动网址不能为空');
			}
		}else{
			$data['url'] = trim($_POST['act_url']);
			$my_url = explode('/',$data['url']);
			$activity_page_id_result = $model -> table('sj_activity_page') -> where(array('ap_link' => '/' . end($my_url),'status' => 1)) -> select();
		}

        $data['version_type'] = $_POST['type'];
		if($_POST['type']==1)
		{
		 $data['version_code'] = $_POST['version_code1'];
		}
		if($_POST['type']==2)
		{
		 $data['version_code'] = $_POST['version_code2'];
		}
		if($_POST['type']==3)
		{
		 $data['version_code'] = $_POST['force_update_version'];
		}
		if($_POST['url_list'] != 'on'){
			if (!trim($_POST['act_pre_url'])){
				if(($_POST['activity_type'] == 1 && $_POST['activity_type_bank'] == 1) || $_POST['activity_type'] == 2 || $_POST['activity_type'] == 3){
					$this->error('预告网址不能为空');
				}
			}else{
				$data['pre_url'] = trim($_POST['act_pre_url']);
				$my_url = explode('/',$data['pre_url']);
				$activity_page_prepare_result = $model -> table('sj_activity_page') -> where(array('ap_link' => '/' . end($my_url),'status' => 1)) -> select();
			}
			$data['url_list'] = 0;
		}else{
			$data['url_list'] = 1;
		}

		if($_POST['pos']) {
			$data['pos'] = $_POST['pos'];
		}	
		if (!trim($_POST['act_end_url'])){
			if(($_POST['activity_type'] == 1 && $_POST['activity_type_bank'] == 1) || $_POST['activity_type'] == 2 || $_POST['activity_type'] == 3){
				$this->error('结束网址不能为空');
			}
		}else{
			$data['end_url'] = trim($_POST['act_end_url']);
			$my_url = explode('/',$data['end_url']);
			$activity_page_end_result = $model -> table('sj_activity_page') -> where(array('ap_link' => '/' . end($my_url),'status' => 1)) -> select();
		}
		if (empty($_POST['act_start_tm'])){
			$this->error('开始时间不能为空');
		}else{
			$act_start_tm =  strtotime($_POST['act_start_tm']);
			$data['start_tm'] = $act_start_tm;
		}
		if (empty($_POST['act_end_tm'])){
			$this->error('结束时间不能为空');
		}else{
			$act_end_tm = strtotime($_POST['act_end_tm']);
			$data['end_tm'] = $act_end_tm;
		}
		if ($data['end_tm'] < $data['start_tm']){
			$this->error('结束时间不能小于开始时间');
		}
		// if($act_start_tm <= $time && $act_end_tm >= $time  ){
			// $where2 = array(
				// 'start_tm' => array('exp',"<={$time}"),
				// 'end_tm' => array('exp',">={$time}"),
				// 'pos' => $_POST['pos']			
			// );
		// }else{
			// $where2 = array(
				// 'start_tm' => array('exp',">{$time}"),
				// 'pos' => $_POST['pos']			
			// );			
		// }
		// $res = $model->table('sj_activity')->where($where2)->field('pos') ->find();
		// if($res){
			// $this->error('该时间段排序号冲突！');
		// }			

		$intro = $_POST['intro'];
		if(!$intro){
			$this -> error("请填写奖品介绍");
		}

		if(mb_strlen(iconv('utf-8', 'gbk', $_POST['intro'])) > 40){
			$this -> error("奖品介绍需在20字以内");
		}else{
			$data['intro'] = $intro;
		}
		if(empty($_POST['old_imgurl_604_204']) && empty($_FILES['act_img_604_204']['tmp_name'])){
			$this -> error("6.0高分图片：:请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}的图片");
		}
		if(empty($_POST['old_imgurl_444_150']) && empty($_FILES['act_img_444_150']['tmp_name'])){
			$this -> error("6.0低分图片：:请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
		}
		if($_FILES['act_img']['tmp_name']){
			$image = getimagesize($_FILES['act_img']['tmp_name']);
			if($image[0] != $this->image_width || $image[1] != $this->image_height){
				$this -> error("图片：:请上传宽度为{$this->image_width},高度为{$this->image_height}的图片");
			}
		}
		if($_FILES['act_img_604_204']['tmp_name']){
			$image_604_204 = getimagesize($_FILES['act_img_604_204']['tmp_name']);
			if($image_604_204[0] != $this->image_width_high || $image_604_204[1] != $this->image_height_high){
				$this -> error("6.0高分图片：:请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}的图片");
			}
		}
		if($_FILES['act_img_444_150']['tmp_name']){
			$image_444_150 = getimagesize($_FILES['act_img_444_150']['tmp_name']);
			if($image_444_150[0] != $this->image_width_low || $image_444_150[1] != $this->image_height_low){
				$this -> error("6.0低分图片：:请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
			}	
		}
		if (!empty($_FILES['act_img']['size']) || !empty($_FILES['act_img_604_204']['size']) || !empty($_FILES['act_img_444_150']['size']))
		{
			$path = date("Ym/d/");
			$config = array();
			$config['multi_config']['act_img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_width' => $this->image_width,
				'img_p_height' => $this->image_height,
				'img_p_size' => 1024 * 35,
			);
			$config['multi_config']['act_img_604_204'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 80,
			);
			$config['multi_config']['act_img_444_150'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 40,
			);	
			$config['multi_config']['the_package'] = array(
				'savepath' => UPLOAD_PATH. '/sdk/'. $path,
				'saveRule' => 'getmsec',
			);
			$config['multi_config']['the_ignore_package'] = array(
				'savepath' => UPLOAD_PATH. '/sdk/'. $path,
				'saveRule' => 'getmsec',
			);
			$config['multi_config']['user_path'] = array(
				'savepath' => UPLOAD_PATH. '/sdk/'. $path,
				'saveRule' => 'getmsec',
			);				
			//var_dump($config);
			if (!empty($config['multi_config'])) {
				$list = $this->_uploadapk(0, $config);

				foreach($list['image'] as $val) {
					if ($val['post_name'] == 'act_img') {
						$data['imgurl'] = $val['url'];
					}
					if ($val['post_name'] == 'act_img_444_150') {
						$data['low_image_url'] = $val['url_original'];
						$data['low_image_url_40'] = $val['url'];
					}
					if ($val['post_name'] == 'act_img_604_204') {
						$data['high_image_url'] = $val['url_original'];
						$data['high_image_url_80'] = $val['url'];
					}					
				}
			}
		}
		$data['filter_label_id'] = $_POST['filter_label_id'];
		$log = $this -> logcheck(array('id' => $data['id']),'sj_activity',$data,$activity);
		if($_POST['p']){
			$p = $_POST['p'];
		}else{
			$p = 1;
		}
		if($_POST['lr']){
			$lr = $_POST['lr'];
		}else{
			$lr = 10;
		}
		$my_self = $activity -> table('sj_activity') -> where(array('id' => $_POST['editid'])) -> find();

		if($my_self['activity_type'] == 1){
			$data['activity_type_bank'] = $my_self['activity_type_bank'];
			$arr = array(2,3,4,5,6,7,8,9,10,11);
			if(in_array($my_self['activity_type_bank'],$arr)){
				$activate_type_arr = array(
					2 => 4,
					3 => 5,
					4 => 7,
					5 => 8,
					6 => 9,
					7 => 11,
					8 => 12,
					9 => 14,
					10 => 15,
				    11 =>16
				);
				$activate_type = $activate_type_arr[$my_self['activity_type_bank']];				
				if($_POST['url_list'] != 'on'){
					$pre_coactivity = $_POST['pre_coactivity'];
					$pre_result = $model -> table('sj_activity_page') -> where(array('ap_name' => $pre_coactivity,'status' => 1)) -> select();
					if(!$pre_result){
						$this -> error("预告页名称不存在");
					}else{
						$data['activity_prepare_id'] = $pre_result[0]['ap_id'];
						$data['pre_url'] = $pre_result[0]['ap_link'];
					}
				}else{
					$data['activity_prepare_id'] = '';
				}
				$bgn_coactivity = $_POST['bgn_coactivity'];
				$bgn_result = $model -> table('sj_activity_page') -> where(array('ap_name' => $bgn_coactivity,'status' => 1,'activate_type'=>$activate_type)) -> select();
				if(!$bgn_result){
					$this -> error("活动页名称不存在");
				}else{
					$data['activity_page_id'] = $bgn_result[0]['ap_id'];
				}
				$end_coactivity = $_POST['end_coactivity'];
				$end_result = $model -> table('sj_activity_page') -> where(array('ap_name' => $end_coactivity,'status' => 1,'activate_type'=>$activate_type)) -> select();
				if(!$end_result){
					$this -> error("结束页名称不存在");
				}else{
					$data['activity_end_id'] = $end_result[0]['ap_id'];
				}
				if($page_result['activate_type'] == 5){
					$page_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $data['activity_page_id'])) -> find();
					$page_start = $page_result['start_tm'];
					$page_end = $page_result['end_tm'];
					if($page_start < $data['start_tm'] || $page_end > $data['end_tm']){
						$this -> error("点赞时间必须在活动时间之内");
					}
				}
			}
		}elseif($my_self['activity_type'] == 4){
			if($_POST['url_list'] != 'on'){
				$pre_name = $_POST['pre_desc'];
				$pre_result = $model -> table('sj_pre_download') -> where(array('activity_name' => $pre_name,'status' => 1)) -> select();
				if(!$pre_result){
					$this -> error("预告页名称不存在");
				}else{
					$data['activity_prepare_id'] = $pre_result[0]['id'];
				}
			}else{
				$data['activity_prepare_id'] = '';
			}
			$bgn_name = $_POST['bgn_desc'];
			$bgn_result = $model -> table('sj_pre_download') -> where(array('activity_name' => $bgn_name,'status' => 1)) -> select();
			if(!$bgn_result){
				$this -> error("活动页名称不存在");
			}else{
				$data['activity_page_id'] = $bgn_result[0]['id'];
			}
			$end_name = $_POST['end_desc'];
			$end_result = $model -> table('sj_pre_download') -> where(array('activity_name' => $end_name,'status' => 1)) -> select();
			if(!$end_result){
				$this -> error("结束页名称不存在");
			}else{
				$data['activity_end_id'] = $end_result[0]['id'];
			}
		}
		$platform_arr = $_POST['platform'];
		if(!$platform_arr){
			$this -> error("请选择投放平台");
		}
		$the_platform = implode(',',$platform_arr);
		$data['platform'] = $the_platform;
		if(in_array(13,$platform_arr)){
			$data['sdk_type'] = $_POST['sdk_type'];
			// if(!trim($_POST['sdk_ac_rank'])){
			// 	$this -> error("优先级必填");
			// }else if(!preg_match("/^[1-9]\d*$/",trim($_POST['sdk_ac_rank']))){
			// 	$this -> error("优先级必须为正整数");
			// }
			// $data['sdk_ac_rank'] = trim($_POST['sdk_ac_rank']);
			$data['sdk_ac_type'] = $_POST['sdk_ac_type'];
		}else{
			// $data['sdk_ac_rank'] = '';
			$data['sdk_ac_type'] = '';
		}
		if($_POST['sdk_type'] == 3||$_POST['sdk_type'] == 4){
			$data['vip_jump'] = $_POST['vip_jump'];
		}else{
			$data['vip_jump'] = 0;
		}		
		if(in_array(13,$platform_arr) && $data['sdk_type'] == 1){
			if(empty($_POST['all_select']) && $_POST['select_package_type'] == 1){
				$this -> error("请上传游戏范围");
			}
			if(!$_POST['select_package_type'] && !$_POST['select_user_type']){
				$this -> error("请选择游戏范围或用户名范围");
			}
			if($_POST['select_user_type'] == 1 && !$_POST['user_save']){
				$this -> error("请上传用户名");
			}
			if($_POST['select_user_type'] == 2){
				$select_user_type_ext = $_POST['select_user_type_ext'];
				if(!$select_user_type_ext){
					$this -> error("请选择老用户范围");
				}
				foreach($select_user_type_ext as $key => $val){
					$select_user_type_ext_str_go .= $val . ',';
				}
				$select_user_type_ext_str = substr($select_user_type_ext_str_go,0,-1);
				$data['select_user_type_ext'] = $select_user_type_ext_str;
			}
		}
		$data['select_package_type'] = $_POST['select_package_type'];
		$data['select_package_list'] = $_POST['all_select'];
		
		if($data['select_package_type'] != 1){
			$data['ignore_package_list'] = $_POST['all_ignore'];
		}
		$data['select_user_type'] = $_POST['select_user_type'];
		if($_POST['user_save']){
			$data['select_user_file_path'] = $_POST['user_save'];
		}
		if($my_self['activity_type_bank']==1){
			if(empty($_POST['page_id'])){
				$this -> error("请填写页面id");
			}
		    $data['activity_page_id'] = $_POST['page_id'];
		    $data['activity_prepare_id'] = $_POST['page_id'];
		    $data['activity_end_id'] = $_POST['page_id'];
        }
        $data['cover_user_type'] = $_POST['cover_user_type'];
        if($data['cover_user_type'] != 2){
            $data['activation_date_start'] = 0;
            $data['activation_date_end'] = 0;
        }else{
            $data['activation_date_start'] = $_POST['activation_date_start']?strtotime($_POST['activation_date_start']):0;
            $data['activation_date_end'] = $_POST['activation_date_end']?strtotime($_POST['activation_date_end']):0;
        }
        
        if($_POST['package']){
        	//屏蔽软件上排期时报警需求 新增  yuesai
	        $AdSearch = D("Sj.AdSearch");
	        $shield_error=$AdSearch->check_shield($_POST['package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
				$this -> error($shield_error);
			}
        }
		if($data['start_tm'] > $my_self['start_tm']){
			$activity_model = D('Sj.CoActivity');
			if($my_self['activity_type_bank']==5){
				//清除排行榜测试数据
				$activity_model -> ranking_test_data_del($_POST['editid']);
			}else if($my_self['activity_type_bank']==8){
				//清除运营签到模版测试数据
				$activity_model -> test_data_del($_POST['editid'],'sign');
			}else if($my_self['activity_type_bank']==7){
				//清除运营预下载测试数据
				$activity_model -> test_data_del($_POST['editid'],'pre_down_operation');
			}
		}

		if ($activity->editActivity($data) !== false)
                {
                        $aid = $data['id'];
                        if($_POST['old_start_tm']!=strtotime($_POST['act_start_tm'])||$_POST['old_end_tm']!=strtotime($_POST['act_end_tm'])){
                            $redis = new Redis();
                            $task_config=C('task_redis');
                            $redis->connect($task_config['host'],$task_config['port']);
                            $rs = $redis->get('v65UCENTER_TASK_T16_LIST');
                            $rss = json_decode($rs,true);
                            $aid_arr = $rss[$aid];
                            if($aid_arr){//如果该活动 有配置任务
                                $do_ids = array();

                                foreach($aid_arr as $v){ //根据时间遍历多个 判断时间范围 todo
                                    $startTime = strtotime($v['startTime']);
                                    $endTime = strtotime($v['endTime']);

                                    if($startTime<strtotime($_POST['act_start_tm'])||$endTime>strtotime($_POST['act_end_tm'])){
			                $this->error('该活动开始时间不可晚于任务开始时间或活动结束时间不可早于任务结束时间，请先调整任务时间');
                                    }
                                }


                                /*
                                var_dump($do_ids);exit;
                                //时间有变动调用接口
                                $url = 'http://dev.heifer.anzhi.com/softActivityAudit/activityDown';
                                //$vals['ACTIVITYID'] = $data['id'];
                                $vals['ACTIVITYID'] = 673;
                                $rs = httpGetInfo($url,$vals);
                                var_dump($rs);
                                */

                            }
                        }

			$this->writelog('推荐管理-活动分区-编辑了ID为' . $data['id'] .$log,'sj_activity',$data['id'],__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/showActivityList".$url_subff);
			$this->success('编辑成功');
		}
		else{
			$this->error('编辑失败');
		}
	}

	/**
	 * 删除活动
	 */
	public function deleteActivity() {
		if (!isset($_GET['id']))
		{
			$this->assign('jumpUrl', '/index.php/Sj/Activity/showActivityList');
			$this->error('参数错误');
		}
		$id = $_GET['id'];
		$activity = D('Sj.Activity');
		$flag = $activity->delActivity($id);

		$url_subff = $this->get_url_suffix(array('activityName','startDate','endDate','activitySite','p','lr'));
		if ($flag)
		{
			$this->writelog('推荐管理-活动分区-删除了ID为' . $id . '的活动','sj_activity',$id,__ACTION__ ,'','del');
			$this->assign('jumpUrl', '/index.php/Sj/Activity/showActivityList'.$url_subff);
			$this->success('删除成功');
		}
		else
		{
			$this->assign('jumpUrl', '/index.php/Sj/Activity/showActivityList'.$url_subff);
			$this->error('删除失败');
		}
	}
	
	//活动奖品列表
	public function award_list(){



		$model = new Model();
		$activity_model = D('Sj.CoActivity');
		$id = $_GET['id'];
		$result = $activity_model -> table('gm_lottery_prize') -> where(array('aid' => $id,'status' => 1)) -> order('level') -> select();
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $id)) -> select();
		foreach($result as $key => $val){
			$odds_result = $activity_model -> table('gm_probability') -> where(array('pid' => $val['pid'])) -> select();
			if($odds_result){
				$val['is_edit'] = 1;
			}else{
				$val['is_edit'] = 0;
			}
			$result[$key] = $val;
		}
		$this -> assign('type',$_GET['type']);
		$this -> assign('activity_result',$activity_result);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function add_award_show(){
		$id = $_GET['id'];
		$type = $_GET['type'];
		$this -> assign('id',$id);
		$this -> assign('type',$type);
                if($type==2){
		    $this -> display();
                }else if($type==6){
		    $this -> display('add_award_show_yuyue');
                }
	}
	
	public function add_award_do(){ 
		$activity_model = D('Sj.CoActivity');
		$model = new Model();
		$aid = $_POST['aid'];
		$data['aid'] = $aid;
		$name = trim($_POST['name']);
		$data['name'] = $name;
		if(!$name){
			$this -> error("请填写奖品名称");
		}
		$level = trim($_POST['level']);
		$data['level'] = $level;
		$have_level_where['_string'] = "aid = {$aid} and status = 1 and level = {$level}";
		$have_level_result = $activity_model -> table('gm_lottery_prize') -> where($have_level_where) -> select();
		if(!$level){
			$this -> error("请填写奖品等级");
		}
		if($level && !eregi('^[0-9]*$',$level)){
			$this -> error("奖品等级格式错误");
		}
		if($have_level_result){
			$this -> error("该活动已包含此等级奖品");
		}
		$type = $_POST['type'];


		if($_POST['type'] == 4 && empty($_POST['couponid'])){
			$this -> error("请填写礼券ID");
		}elseif( !$_POST['id'] && $_POST['type'] == 5 && empty($_POST['code']) ){
			$this -> error("有未上传包名或文选择礼包");
		}

		if( $_POST['type'] == 4 && $_POST['couponid']){
			$data['gift_file'] = $_POST['couponid'];
		}
		
		if( $_POST['type'] == 5 && !empty($_POST['code']) ) {
			$gift_file = explode(',', $_POST['code']);
			foreach ($gift_file as $k => $v) {
				$gift_val[] = explode(':', $v);
			}
			$gift_data = array();
			foreach ($gift_val as $kk => $vv) {
				if( $kk == 0 ){
					$tmp = $vv[0];
					$gift_data[$vv[0]][] = $vv[1];
				}else {
					if( $vv[0] == $tmp ) {
						$gift_data[$vv[0]][] = $vv[1];
					}else {
						$tmp = $vv[0];
						$gift_data[$vv[0]][] = $vv[1];
					}
				}
			}
			$data['pkg_path'] = $_POST['pkg_path'];
			$data['gift_file'] = json_encode($gift_data);
		}


		$data['type'] = $type;
		
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $aid)) -> find();
		$page_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $activity_result['activity_page_id'])) -> find();

		if($page_result['lottery_style'] == 1){
			$width = 80;
                        if($_POST['ttype']==2){
			    $height = 100;
                        }else{
			    $height = 128;
                        }
		}elseif($page_result['lottery_style'] == 2||$page_result['lottery_style'] == 4){
			$width = 100;
			$height = 100;
		}elseif($page_result['lottery_style'] == 3||$page_result['lottery_style'] ==5){
			$width = 60;
			$height = 78;
		}

		
		$pic_path = $_FILES['pic_path'];
		$desc = trim($_POST['desc']);
		$data['desc'] = str_replace(array("\r\n","\n"),'',$desc);
		if($pic_path['size']){
			$high_wd = getimagesize($pic_path['tmp_name']);
            $widhig_hg = $high_wd[3];
            $wh_hg = explode(' ', $widhig_hg);
            $wh1_hg = $wh_hg[0];
            $widths_hg = explode('=', $wh1_hg);
            $width1_hg = substr($widths_hg[1], 0, -1);
            $width_go_hg = substr($width1_hg, 1);
            $hi1_hg = $wh_hg[1];
            $heights_hg = explode('=', $hi1_hg);
            $height1_hg = substr($heights_hg[1], 0, -1);
            $height_go_hg = substr($height1_hg, 1);
			
            if ($width_go_hg != $width || $height_go_hg != $height) {
                $this->error("分辨率图标大小不符合条件");
            }
			$path=date("Ym/d/",time());
			$config = array(
				'multi_config' => array(
				'pic_path' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				)),
			);
			$list =$this->_uploadapk(0, $config);
			$data['pic_path'] = $list['image'][0]['url'];
		}else{
			$this -> error("请上传奖品图片");
		}

		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$result = $award_result = $activity_model -> table('gm_lottery_prize') -> add($data);
		if($award_result){
			$this -> writelog("活动ID为{$aid}的活动已添加id为{$result}的活动奖品",'gm_lottery_prize',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/award_list/type/{$_POST['ttype']}/id/{$aid}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function add_virtual_show(){
		$activity_model = D('Sj.CoActivity');
		$id = $_GET['id'];
		$result = $activity_model -> table('gm_virtual_prize') -> where(array('pid' => $id)) -> count();
		$this -> assign('prize_count',$result);
		$this -> assign('id',$id);
		$this -> display();
	}
	
	function add_virtual_do(){
		ini_set('memory_limit','256M');
		$activity_model = D('Sj.CoActivity');
		$model = new Model();
		$id = $_POST['id'];
		$my_result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id,'status' => 1)) -> find();			
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $my_result['aid'])) -> find();
		//if($activity_result['start_tm'] < time()){
		//	$this -> error("活动已开始不能上传虚拟奖品");
		//}
		if($my_result['type'] == 2){
			$have_result = $activity_model -> table('gm_virtual_prize') -> where(array('pid' => $my_result['pid'])) -> delete();
		
			$code_file = $_FILES['my_code'];
			if($code_file['size']){
				$file_type = explode('.', $code_file['name']);
				if ($file_type[1] != 'csv') {
					$this->error("请上传csv格式文件");
				}
				$file_handle = fopen($code_file['tmp_name'], "r");
				$shili = fopen($code_file['tmp_name'], "r");  

				while (!feof($shili)) {
					$shi = fgets($shili, 1024);
					$a = explode(',', $shi);
					if ($a[3]) {
						$this->error("激活码文件格式错误");
					}
					$str .= $shi . ",";
				}
				$str_arr = explode("\r\n", $str);
				$my_all_code = array();
				$newarr = array();
				$error = '礼包重复数据：';
				foreach($str_arr as $key => $val){
					if($key == 0 || empty($val)){
						continue;
					}
					list($a,$num,$softname,$pkg) = explode(',',$val);
					$num = trim($num);
					$pkg = trim($pkg);
					if(!$num){
						continue;
					}
					$my_code = array($num,$softname,$pkg);
					if($newarr[$num]){
						$error .= $num."\n";
					}else{
						$newarr[$num] = 1;
					}
					$my_all_code[$key] = $my_code;
				}
				if($error != '礼包重复数据：'){
					$this->error($error);				
				}					
				//上传礼包
				$date = date("Ym/d/");
				$dir_img = C('ACTIVITY_CSV') . '/gift/'.$date;
				if(!is_dir($dir_img)) {
					if(!mkdir($dir_img,0777,true)) {
						//创建gift目录{$dir_img}失败
						$this -> error("创建gift目录{$dir_img}失败");			
					}
				}
				list($msec,$sec) = explode(' ',microtime());
				$types = 'csv';
				$msec = substr($msec,2);
				$dst = $dir_img.'gift'.$my_result['aid'].'_'.$my_result['pid'].'_'.$msec.'.'.$types;
				if(move_uploaded_file($_FILES['my_code']['tmp_name'],$dst)) {
					$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
					$map = array( 'gift_file' => $path );
					$activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id,'status' => 1)) -> save($map);
				} 				
			}else{
				$this -> error("请上传虚拟奖品卡密等信息");
			}
			$sql = "insert into gm_virtual_prize (`first_text`,`second_text`,`third_text`,`pid`,`status`,`aid`,`update_tm`,`create_tm`) values ";
			$sql_srt = '';
			$i = 1;
			$virtual_result = array();
			foreach($my_all_code as $key => $val){
				if($val[0] || $val[1]){
					if($val[0] != '' && $val[0] != '卡号'){
						if(!strstr($val[0],"+")){
							$code_data['first_text'] = $val[0];
							$valss['first_text'] = $val[0];
						}else{
							$this -> error("上传csv包含+号");
						}
					}
					if($val[1] != '' && $val[0] != '密码'){
						if(!strstr($val[1],"+")){
							$code_data['second_text'] = $val[1];
							$valss['second_text'] = $val[1];
						}else{
							$this -> error("上传csv包含+号");
						}
					}
					if($val[2] != '' && $val[0] != '包名'){
						$code_data['third_text'] = $val[2];
						$valss['third_text'] = $val[2];
					}

					if($val[2] == '' && $val[0] != '包名'){
				            $this -> error("请上传包名");
                                        }
				
					$virtual_result[] = $valss;
					$code_data['pid'] = $my_result['pid'];
					$code_data['aid'] = $my_result['aid'];
					$code_data['create_tm'] = time();
					$code_data['update_tm'] = time();
					$code_data['status'] = 0;
					$sql_srt .="('".$code_data['first_text']."','".$code_data['second_text']."','".$code_data['third_text']."','".$my_result['pid']."',0,'".$my_result['aid']."','".$code_data['update_tm']."','".$code_data['create_tm']."'),";
					if($i%5000 == 0){
						$sql_srt = substr($sql_srt,0,-1);
						$code_result = $activity_model->query($sql.$sql_srt);
						$sql_srt = '';
					}
					$i++;
				}
			}
			$sql_srt = substr($sql_srt,0,-1);
			$code_result = $activity_model->query($sql.$sql_srt);
			//$activity_model->ping();
			//$virtual_result = $activity_model -> table('gm_virtual_prize') -> where(array('pid' => $id,'status' => 0)) -> field('first_text,second_text,third_text') -> select();
			$package_result = $activity_model -> table('gm_virtual_prize') -> where(array('pid' => $id,'status' => 0)) -> field('third_text') -> group('third_text') -> select();
			foreach($virtual_result as $key => $val){
				$package_result[] = trim($val['third_text']);
			}
			$package_result = array_unique($package_result);
			$package_results = json_encode($package_result);
			$gift_package = "general_lottery:package_{$id}";
			$redis = new redis();
			$redis->connect(C('LOTTERY_REDIS_HOST'),C('LOTTERY_REDIS_PORT'));
			$redis -> delete($gift_package);
			$redis -> set($gift_package, $package_results,60*86400);
			//$virtual_result = array_slice($virtual_result,0,5500);
			$i = 1;
			foreach($package_result as $k => $v){
				$virtual_prize = "general_lottery:virtual_{$v}_{$id}";
				$redis -> delete($virtual_prize);
				$param = '';
				foreach($virtual_result as $key => $val){
					if($val['third_text'] == $v){
						$vals = json_encode($val);
						if($val['first_text']){
							$vals = json_encode($val);
						}
						$param .= $vals."','";
						if($i%5000 == 0){
							$params = substr($param,0,-3);
							$cmd = "\$redis->rpush('{$virtual_prize}', '{$params}');";
							eval($cmd);
                                                        $cmd = "\$redis->expire('{$virtual_prize}',86400*60);";
                                                        eval($cmd);				
							$params = '';
							$param = '';
						}
						$i++;
					}else{
						continue;
					}
				}
				if($param){
					$params = substr($param,0,-3);
					$cmd = "\$redis->rpush('{$virtual_prize}', '{$params}');";
					eval($cmd);
					$cmd = "\$redis->expire('{$virtual_prize}',86400*60);";
					eval($cmd);				
				}
			}
			$this -> writelog("已添加id为{$id}的虚拟活动奖品",'gm_virtual_prize',$id,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/award_list/id/{$my_result['aid']}");
			$this -> success("添加成功");
		}
	}
	
	
	function edit_award_show(){
		$activity_model = D('Sj.CoActivity');
		$id = $_GET['id'];
		$type = $_GET['type'];
		$result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id)) -> find();
		$this -> assign('result',$result);

		        $this -> assign('list',$result);
			$gift_arr	=	json_decode($result['gift_file'],true);
			$gift_count	=	count($gift_arr);
			$this -> assign('gift_arr',$gift_arr);
			$this -> assign('gift_count',$gift_count);

		$this -> assign('id',$id);
		$this -> assign('type',$type);
                if($type==2){
		    $this -> display();
                }else if($type==6){
		    $this -> display('add_award_show_yuyue');
                }
	}
	
	function edit_award_do(){
		$activity_model = D('Sj.CoActivity'); 
		$model = new Model();
		$id = $_POST['id'];
		$aid_result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id)) -> find();
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $aid_result['aid'])) -> find();
		$page_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $activity_result['activity_page_id'])) -> find();

		if($page_result['lottery_style'] == 1){
			$width = 80;
                        if($_POST['ttype']==2){
			    $height = 100;
                        }else{
			    $height = 128;
                        }
		}elseif($page_result['lottery_style'] == 2||$page_result['lottery_style']){
			$width = 100;
			$height = 100;
		}elseif($page_result['lottery_style'] == 3){
			$width = 60;
			$height = 78;
		}

		$name = trim($_POST['name']);
		$data['name'] = $name;
		if(!$name){
			$this -> error("请填写奖品名称");
		}
		$level = trim($_POST['level']);
		$data['level'] = $level;
		if(!$level){
			$this -> error("请填写奖品等级");
		}
		if($level && !eregi('^[0-9]*$',$level)){
			$this -> error("奖品等级格式错误");
		}
		$have_level_where['_string'] = "aid = {$aid_result['aid']} and status = 1 and level = {$level} and pid != {$id}";
		$have_level_result = $activity_model -> table('gm_lottery_prize') -> where($have_level_where) -> select();
		if($have_level_result){
			$this -> error("该活动已包含此等级奖品");
		}
		$type = $_POST['type'];
		$pic_path = $_FILES['pic_path'];
		$desc = trim($_POST['desc']);
		$data['desc'] = str_replace(array("\r\n","\n"),'',$desc);
		if($pic_path['size']){
			$high_wd = getimagesize($pic_path['tmp_name']);
            $widhig_hg = $high_wd[3];
            $wh_hg = explode(' ', $widhig_hg);
            $wh1_hg = $wh_hg[0];
            $widths_hg = explode('=', $wh1_hg);
            $width1_hg = substr($widths_hg[1], 0, -1);
            $width_go_hg = substr($width1_hg, 1);
            $hi1_hg = $wh_hg[1];
            $heights_hg = explode('=', $hi1_hg);
            $height1_hg = substr($heights_hg[1], 0, -1);
            $height_go_hg = substr($height1_hg, 1);
            if ($width_go_hg != $width || $height_go_hg != $height) {
                $this->error("分辨率图标大小不符合条件");
            }
			$path=date("Ym/d/",time());
			$config = array(
				'multi_config' => array(
				'pic_path' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024*50,
				)),
			);
			$list =$this->_uploadapk(0, $config);
			$data['pic_path'] = $list['image'][0]['url'];
		}

		$data['update_tm'] = time();
		$award_result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id)) -> save($data);

		if($award_result){
			$this -> writelog("已编辑id为{$id}的活动奖品",'gm_lottery_prize',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/award_list/id/{$aid_result['aid']}/type/{$_POST['ttype']}/");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_award(){
		$activity_model = D('Sj.CoActivity');
		$id = $_GET['id'];
		$aid_result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id)) -> find();
		$result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的活动奖品",'gm_lottery_prize',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/award_list/id/{$aid_result['aid']}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function edit_odds_show(){
		$activity_model = D('Sj.CoActivity');
		$model = new Model();
		$id = $_GET['id'];
		$result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id,'status' => 1)) -> find();
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $result['aid'],'status' => 1)) -> find();
		$pro_count = $activity_model -> table('gm_probability') -> where(array('pid' => $id,'status' => 1)) -> count();
		$pro_result = $activity_model -> table('gm_probability') -> where(array('pid' => $id,'status' => 1)) -> order('begin_tm') -> select();
		$this -> assign('pro_result',$pro_result);
		$this -> assign('pro_count',$pro_count);
		$this -> assign('activity_result',$activity_result);
		$this -> assign('id',$id);
		$this -> assign('type',$_GET['type']);
		$this -> display();
	}
	
	function edit_odds_do(){
		$activity_model = D('Sj.CoActivity');
		$model = new Model();
		$id = $_POST['id'];
		$pptype= $_POST['pptype'];
		$result = $activity_model -> table('gm_lottery_prize') -> where(array('pid' => $id)) -> find();
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $result['aid'])) -> find();
		$pro_count = $activity_model -> table('gm_probability') -> where(array('pid' => $id,'status' => 1)) -> count();
		//$pro_result = $activity_model -> table('gm_probability') -> where(array('pid' => $id,'status' => 1)) -> select();
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		
		$probability = $_POST['probability'];
		$upperlimit = $_POST['upperlimit'];
		$is_addup = $_POST['is_addup'];
		foreach($start_tm as $k => $v){
			$vs = date('Y-m-d 00:00:00',strtotime($v));
			$start_tms[$k] = $vs;
		}
		foreach($end_tm as $k => $v){
			$vs = date('Y-m-d 23:59:59',strtotime($v));
			$end_tms[$k] = $vs;
		}
	
		$time = time();
		//$have_where['_string'] = "pid = {$id} and status = 1 and begin_tm >= {$time}";
		$have_where['_string'] = "pid = {$id} and status = 1";
		$have_result = $activity_model -> table('gm_probability') -> where($have_where) -> select();
		$j = 0;
		for($i=0;$i<count($start_tms);$i++){
			if(!$start_tms[$i] || !$end_tms[$i]){
				$this -> error("时间段开始时间或结束时间不能为空");
			}
			if(strtotime($start_tms[$i]) > strtotime($end_tms[$i])){
				$this -> error("选择时间段的开始时间不能大于结束时间");
			}
		
			if(date('Ymd',strtotime($end_tms[$i])+86400) != date('Ymd',strtotime($start_tms[$i+1])) && $i != (count($start_tms) - 1)){
				$this -> error("时间段必须连续{$end_tms[$i]}");
			}
			if(date('Ymd',strtotime($start_tms[0])) != date('Ymd',$activity_result['start_tm'])){
				$this -> error("最初开始时间必须跟活动开始时间一致");
			}
			if(date('Ymd',strtotime($end_tms[count($start_tms) - 1])) != date('Ymd',$activity_result['end_tm'])){
				$this -> error("最后结束时间必须跟活动结束时间一致");
                        }

                        if($activity_result['start_tm']>time()){
                            $j = $j+1;
                        }

                        /*
			if(strtotime($start_tms[$i]) > time()){
				$j = $j + 1;
                        }*/
		}
		if($j == 0){
			//$this -> writelog("已编辑id为{$id}的奖品中奖概率",'gm_lottery_prize',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/edit_odds_show/type/{$pptype}/id/{$id}");
			$this -> success("无任何变化");
		}else{
			//获取所有奖品中奖率，并判断每天所有奖品中奖率是否<=1
			$all_prize = $activity_model -> table('gm_lottery_prize') -> where(array('aid' => $result['aid'],'status' => 1)) -> select();
			$page_id_result = $model ->  table('sj_activity') -> where(array('id' => $result['aid'])) -> select();
			$page_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $page_id_result[0]['activity_page_id'])) -> select();
			foreach($start_tms as $k => $v){
				$judge_time = array();
				$the_time = strtotime($v);
				foreach($all_prize as $key => $val){
					if($val['pid'] != $id){
						$judge_other_where['_string'] = "{$the_time} >= begin_tm and {$the_time} <= end_tm and aid = {$result['aid']} and pid = {$val['pid']} and status = 1";
						$judge_other_result = $activity_model -> table('gm_probability') -> where($judge_other_where) -> find();
						$judge_time[$val['pid']] = $judge_other_result['probability'];
					}else{
						if($the_time < time()){
							$judge_own_where['_string'] = "{$the_time} >= begin_tm and {$the_time} <= end_tm and aid = {$result['aid']} and pid = {$val['pid']} and status = 1";
							$judge_own_result = $activity_model -> table('gm_probability') -> where($judge_own_where) -> find();
							$judge_time[$val['pid']] = $judge_own_result['probability'];
						}else{
							$judge_time[$val['pid']] = $probability[$k];
						}
					}
				}
				//计算最小公倍数,'/'分开字符窜，用分母计算最小公倍数，公倍数除以每个分母得a,a*每个分子，累加分子，当累加分子大于最小公倍数则报错；

				foreach($judge_time as $m => $n){
					$r = array();
					if($n != null && $n){
						$r = explode('/',$n);
						$all_judge_basenum[] = $r[1];
					}
				}
				$judge_basenum = array_unique($all_judge_basenum);
				
				$basenum = 0;
				foreach($judge_basenum as $m => $n){
					if(!$basenum){
						$basenum = $n;
						continue;
					}else{
						$basenum = $basenum * $n;
					}
				}
				$childnum = 0;
				foreach($judge_time as $m => $n){
					if($n != null && $n){
						$r = explode('/',$n);
						$childnum += $r[0]/$r[1]*$basenum;
					}
				}

				if($childnum > $basenum){
					$this -> error("每一天所有奖品中奖率不能大于1");
				}
				if($page_result[0]['is_repeat'] == 1 && $childnum == $basenum && $childnum != 0){
					$this -> error("中奖率等于1与限制用户不重复中同一游戏礼包冲突");
				}
			}
			
			$have_id_str = '';

			
			for($i=0;$i<count($start_tms);$i++){
				//if(strtotime($start_tms[$i]) > time()){
                                    if($activity_result['start_tm']>time()){
					$data['begin_tm'] = strtotime($start_tms[$i]);
					$data['end_tm'] = strtotime($end_tms[$i]);
					if($probability[$i] < 0){
						$this -> error("中奖概率不能小于0");
					}
					if($probability[$i]){
						$probability_arr = explode('/',$probability[$i]);
						if(!$probability_arr[0] || !$probability_arr[1] || $probability_arr[0] > $probability_arr[1]){
							$this -> error("中奖率格式错误");
						}
						foreach($probability_arr as $key => $val){
							if(!preg_match('/^\d+$/i', $val)){
								$this -> error("中奖率格式错误");
							}
						}
					}
					$data['probability'] = $probability[$i];
					$data['upperlimit'] = $upperlimit[$i];
					if($upperlimit[$i] < 0){
						$this -> error("限制中奖数不能小于0");
					}
					if(!preg_match('/^\d+$/i',$upperlimit[$i])){
						$this -> error("限制中奖数格式错误");
					}
					$data['is_addup'] = $is_addup[$i];
					$data['now_num'] = $upperlimit[$i];
					$data['pid'] = $id;
					$data['aid'] = $activity_result['id'];
					$data['create_tm'] = time();
					$data['update_tm'] = time();
					$data['status'] = 1;
					$update_result = $activity_model -> table('gm_probability') -> add($data);
				}
			}

                        if($have_result){
				foreach($have_result as $k => $v){
					$have_id .= $v['id'].',';
				}
				$have_id_str = substr($have_id,0,-1);
				$del_where['_string'] = "pid = {$id} and status = 1 and id in ({$have_id_str})";
				$del_result = $activity_model -> table('gm_probability') -> where($del_where) -> save(array('status' => 0));
			}
			
			$this -> writelog("已编辑id为{$id}的奖品中奖概率",'gm_probability',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/award_list/type/{$pptype}/id/{$activity_result['id']}");
			$this -> success("编辑成功");
		}
	}
	function save_pos(){
		$model = new Model();
		$where = array(
			'id' => $_GET['id'],
		);
		$map = array('pos' => $_GET['pos']);
		$time = time();
		// $act_start_tm = $_GET['start'];
		// $act_end_tm = $_GET['end'];
		// if($act_start_tm <= $time && $act_end_tm >= $time  ){
			// $where2 = array(
				// 'start_tm' => array('exp',"<={$time}"),
				// 'end_tm' => array('exp',">={$time}"),
				// 'pos' => $_GET['pos']			
			// );
		// }else{
			// $where2 = array(
				// 'start_tm' => array('exp',">{$time}"),
				// 'pos' => $_GET['pos']			
			// );			
		// }
		// $res = $model->table('sj_activity')->where($where2)->field('pos') ->find();
		// if($res){
			// exit(json_encode(array('code'=>'0','msg'=>'该时间段排序号冲突！')));
		// }else{
			$ret = $model->table('sj_activity')->where($where)->save($map);
			if($ret){
				exit(json_encode(array('code'=>'1','msg'=>'修改成功')));
			}else{
				exit(json_encode(array('code'=>'0','msg'=>'修改失败')));
			}
		//}
	}
	
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
	
	function add_ignore(){
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

		if($old_package){
			$now_package = $old_package . ',' . $new_package;
			$package_arr = explode(',',$now_package);
		}else{
			$package_arr = array($new_package);
		}

		foreach($package_arr as $key => $val){
			if($val){
				$vals[0] = $val;
				$soft_result = $model -> table('sj_soft') -> where(array('status' => 1,'hide' => 1,'package' => $val)) -> order('softid DESC') -> limit(1) -> select();
				$vals[1] = $soft_result[0]['softname'];
				$package_arrs[] = $vals;
			}
		}

		echo json_encode($package_arrs);
		return json_encode($package_arrs);
	}
	
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
		$map = '/' . $upload_path . '/' .$dir_paths . $rand . '.' . $file_type[1];
		$all_users = array($all_user,$map);
		echo json_encode($all_users);
		return json_encode($all_users);
	}
	//充值活动奖品列表
	public function ranking_award_list(){
		$model = new Model();
		$activity_model = D('Sj.CoActivity');
		$where = array('aid'=> $_GET['id'],'status' => 1);
		$result = $activity_model -> table('gm_lottery_prize') -> where($where) -> order('level') -> select();
		$pid_arr = array();
		foreach($result as $v){
			if($v['pid']){ $pid_arr[] = $v['pid']; }
		}
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $_GET['id'])) -> find();
		$page_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $activity_result['activity_page_id'])) ->field('lottery_style')-> find();
		if($pid_arr){
			//中奖概率
			$where = array(
				'status' => 1,
				'aid' => $_GET['id'],
				'pid' => array('in',$pid_arr)
			);
			$probability = $activity_model -> table('gm_probability') -> where($where) -> field('probability,pid,aid,now_num,upperlimit') -> select();
			$award_probability = array();
			foreach($probability as $val){
				$award_probability[$val['pid'].$val['aid']] = $val;
			}
			unset($probability,$pid_arr);
		}
		$this -> assign('award_probability',$award_probability);
		$this -> assign('activity_result',$activity_result);
		$this -> assign('lottery_style',$page_result['lottery_style']);
		$this -> assign('result',$result);
		$this -> display();
	}
	//充值活动奖品列表----添加奖品
	public function ranking_award_add(){
		if($_POST){
			$model = D('Sj.CoActivity');
			$ret = $model -> ranking_award_add_do();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
				$res = $model -> table('gm_lottery_prize') -> add($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/ranking_award_list/id/{$_POST['aid']}");
				if($res){
					$map = array(
						'pid' => $res,
						'aid' => $_POST['aid'],
						'now_num' => $_POST['prize_num'],
						'upperlimit' => $_POST['prize_num'],
					);
					$model -> save_probability($map);
					//刷礼包缓存、礼包码入库
					if($_POST['type'] == 2 && $data['gift_file']){
						unset($map['now_num'],$map['upperlimit']);
						$model -> brush_gift_cache($map);	
					}					
					$this -> writelog("已添加pid为{$res}的活动奖品",'gm_lottery_prize',$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('lottery_style',$_GET['lottery_style']);
			$this -> display();
		}
	}
	//充值活动奖品列表----编辑奖品
	function ranking_award_edit(){
		$model = D('Sj.CoActivity');
		if($_POST){
			$ret = $model -> ranking_award_add_do();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
				$where = array('pid'=>$_POST['pid']);
				$res = $model -> table('gm_lottery_prize')->where($where) -> save($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/ranking_award_list/id/{$_POST['aid']}");
				if($res){
					$map = array(
						'pid' => $_POST['pid'],
						'aid' => $_POST['aid'],
						'now_num' => $_POST['prize_num'],
						'upperlimit' => $_POST['prize_num'],
					);
					$model -> save_probability($map);	
					//刷礼包缓存、礼包码入库
					if($_POST['type'] == 2 && $data['gift_file']){
						$model -> table('gm_virtual_prize') -> where(array('pid' => $_POST['pid'])) -> delete();
						//echo $model->getlastsql();exit;						
						unset($map['now_num'],$map['upperlimit']);
						$model -> brush_gift_cache($map);	
					}					
					$this -> writelog("已编辑pid为{$_POST['pid']}的活动奖品",'gm_lottery_prize',$_POST['pid'],__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{
			$where = array('pid'=>$_GET['pid']);
			$list = $model -> table('gm_lottery_prize') ->where($where)->find();
			$prize_num = $model -> table('gm_probability')  ->where($where)->field('now_num')->find();
			$list['prize_num'] = $prize_num['now_num'];
			if($list['type'] == 2){
				$where = array('aid'=>$_GET['aid'],'pid'=>$_GET['pid']);
				$count = $model -> table('gm_virtual_prize') ->where($where)->count();
				$this -> assign('gift_count',$count);
			}else{			
				$gift_arr	=	json_decode($list['gift_file'],true);
				$gift_count	=	count($gift_arr);
				$this -> assign('gift_arr',$gift_arr);
				$this -> assign('gift_count',$gift_count);
			}
                        //todo
			$this -> assign('list',$list);
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> assign('lottery_style',$_GET['lottery_style']);
			$this -> display('ranking_award_add');
		}
	}
	//充值活动奖品列表----设置中奖概率
	public function ranking_award_probability(){
		$model = D('Sj.CoActivity');
		if($_POST){
			$map = array(
				'pid' => $_POST['pid'],
				'aid' => $_POST['aid'],
				'probability' => $_POST['probability'],
			);
			$res = $model -> save_probability($map);	
			$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if($res){
				$this -> writelog("已编辑pid为{$_POST['pid']}，aid为{$_POST['aid']}的奖品概率",'gm_probability',$_POST['pid'],__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else{
			$where = array('pid'=>$_GET['pid']);
			$list = $model -> table('gm_probability')  ->where($where)->field('pid,probability')->find();
			$this -> assign('list',$list);
			$this -> assign('aid',$_GET['aid']);
			$this -> display();
		}
	}
	//充值活动奖品列表----删除
	function ranking_award_del(){
		$model = D('Sj.CoActivity');
		$map = array(
			'update_tm' => time(),
			'status' => 0,
		);
		$where = array('aid' => $_GET['aid'],'pid' => $_GET['pid']);
		$result = $model -> table('gm_lottery_prize')->where($where)->save($map);	
		$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		if($result){
			if($_GET['type'] == 4){
				$model -> table('gm_virtual_prize')->where($where)->delete();	
				//删除礼包缓存
				$redis = new redis();
				$redis->connect(C('LOTTERY_REDIS_HOST'),C('LOTTERY_REDIS_PORT'));
				$redis -> delete( "ranking_gift_{$_GET['aid']}{$_GET['pid']}");
			}
			$this -> writelog("已删除id为{$_GET['pid']}的充值类活动奖品",'gm_lottery_prize',$_GET['pid'],__ACTION__ ,'','edit');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function edit_extend_page(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_activity_extend') -> where(array('aid' => $id)) -> find();
		$this -> assign('result',$result);
		$this -> assign('id',$id);
		$this -> display();
	}
	
	function produce_extend_page(){
		$model = new Model();
		$aid = $_POST['aid'];
		$data['aid'] = $aid;
		$bg_pic = $_FILES['bg_pic'];
		$banner_pic = $_FILES['banner_pic'];
		$share_pic = $_FILES['share_pic'];
		$share_color = $_POST['share_color'];
		$data['share_color'] = $share_color;
		$lottery_color = $_POST['lottery_color'];
		$data['lottery_color'] = $lottery_color;
		$flow_pic = $_FILES['flow_pic'];
		$step_pic = $_FILES['step_pic'];
		$bottom_pic = $_FILES['bottom_pics'];
		if($bg_pic['size'] || $banner_pic['size'] || $share_pic['size'] || $flow_pic['size'] || $step_pic['size'] || $bottom_pic['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'bg_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'banner_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'share_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'flow_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'step_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'bottom_pics' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
			));
			$lists=$this->_uploadapk(0, $config);

			foreach($lists['image'] as $key => $val){
				if ($val['post_name'] == 'bg_pic') {
                    $data['bg_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'banner_pic') {
                    $data['banner_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'share_pic') {
                    $data['share_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'flow_pic') {
                    $data['flow_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'step_pic') {
                    $data['step_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'bottom_pics') {
                    $data['bottom_pic'] = $val['url'];
                }
			}
		}
		
		$data['update_tm'] = time();
		$have_result = $model -> table('sj_activity_extend') -> where(array('aid' => $aid)) -> select();
		if($have_result){
			$result = $model -> table('sj_activity_extend') -> where(array('aid' => $aid)) -> save($data);
		}else{
			$result = $model -> table('sj_activity_extend') -> add($data);
		}
		
		if($result){
			$activity_data['extend_link'] = "http://fx.anzhi.com/extend_{$aid}.html";
			$activity_result = $model -> table('sj_activity') -> where(array('id' => $aid)) -> save($activity_data);
			$this -> writelog("已添加id为{$result}的活动推广页面",'sj_activity_extend',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sj/Activity/showActivityList");
			$this -> success("生成成功");
		}else{
			$this -> error("生成失败");
		}
	}
	function pub_gift_check(){
		if($_FILES['gift']['tmp_name']){
			$array = array('csv');
			$ytypes = $_FILES['gift']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			if(!in_array($type,$array)){
				$return = array(
					'code' => 0,
					'msg' => "上传格式错误",
				);
				exit(json_encode($return));						
			}			
			$data = file_get_contents($_FILES['gift']['tmp_name']);
			//判断是否是utf-8编辑
			if(mb_check_encoding($data,"utf-8") != true){
				$data = iconv("gbk","utf-8", $data);
			}
			$data = str_replace("\r\n","\n",$data);	
			$data_arr = explode("\n", $data);
			$newarr = array();
			$str = '';
			foreach($data_arr as $k=>$v){
				if($k == 0){
					continue;
				}
				list($softname,$pkg,$num) = explode(',',$v);
				if($newarr[$num]){
					$str .= "重复数据：".$num."\n";
				}else{
					$newarr[$num] = 1;
				}
			}
			if($str != ''){
				$return = array(
					'code' => 0,
					'msg' => $str,
				);
				exit(json_encode($return));		
			}else{
				$return = array(
					'code' => 1,
				);
				exit(json_encode($return));		
			}
		}		
	}
	//订制活动奖品列表
	public function custom_award_list(){
		$model = new Model();
		$activity_model = D('Sj.CoActivity');
		$where = array('aid'=> $_GET['id'],'status' => 1);
		$result = $activity_model -> table('valentine_draw_prize') -> where($where) -> order('level') -> select();
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $_GET['id'])) -> find();
		$this -> assign('activity_result',$activity_result);
		$this -> assign('result',$result);
		$this -> display();
	}	
	//订制活动奖品列表----添加奖品
	public function custom_award_add(){
		if($_POST){
			$model = D('Sj.CoActivity');
			$ret = $model -> custom_award_add_do();	
			if($ret['code'] == 1){
				$data = $ret['data'];
				$table = 'valentine_draw_prize';
				$res = $model -> table($table) -> add($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/custom_award_list/id/{$_POST['aid']}");
				if($res){
					//刷礼包缓存、礼包码入库
					if($_POST['type'] == 2 && $data['gift_file']){
						$gift_table = "valentine_draw_gift";
						$map = array(
							'pid' => $res,
							'aid' => $_POST['aid'],
							'table' => $table,
							'gift_table' => $gift_table
						);
						$model -> brush_gift_redis($map);						
					}	
					$log = "已添加订制类活动奖品pid为{$res},aid为{$_POST['aid']}，奖品名称为{$_POST['name']},奖品数量：{$_POST['prize_num']},奖品说明：{$_POST['desc']},奖品等级：{$_POST['level']}";
					
					$this -> writelog($log,'valentine_draw_prize',$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{
			$this -> assign('aid',$_GET['aid']);
			$this -> display();
		}
	}
	//订制活动奖品列表----编辑奖品
	function custom_award_edit(){
		$model = D('Sj.CoActivity');
		$table = "valentine_draw_prize";
		$gift_table = "valentine_draw_gift";
		if($_POST){
			$ret = $model -> custom_award_add_do();	
			if($ret['code'] == 1){
				$data = $ret['data'];
				$where = array('id'=>$_POST['pid']);
				$log = $this->logcheck($where, $table, $data, $model);	
				$res = $model -> table($table)->where($where) -> save($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/custom_award_list/id/{$_POST['aid']}");
				if($res){
					//刷礼包缓存、礼包码入库
					if($_POST['type'] == 2 && $data['gift_file']){
						$map = array(
							'pid' => $_POST['pid'],
							'aid' => $_POST['aid'],
							'table' => $table,
							'gift_table' => $gift_table
						);
						$model -> brush_gift_redis($map);	
					}
					$this -> writelog("订制活动编辑：id为[{$_POST['pid']}]<br/>".$log,$table,$_POST['pid'],__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{
			$where = array('aid'=>$_GET['aid'],'id'=>$_GET['pid']);
			$list = $model -> table($table) ->where($where)->find();
			if($list['type'] == 2){
				$where = array('aid'=>$_GET['aid'],'pid'=>$_GET['pid']);
				$count = $model -> table($gift_table) ->where($where)->count();
				$this -> assign('gift_count',$count);
			}
			$this -> assign('list',$list);
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> display('custom_award_add');
		}
	}	
	//订制活动奖品列表----删除
	function custom_award_del(){			
		$model = D('Sj.CoActivity');
		$map = array(
			'update_tm' => time(),
			'status' => 0,
		);
		$aid = $_GET['aid'];
		$pid = $_GET['pid'];	
		$where = array('aid' => $aid,'id' => $pid);
		$result = $model -> table('valentine_draw_prize')->where($where)->save($map);	
		$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		if($result){
			if($_GET['type'] == 2){	
				$where = array('aid' => $aid,'pid' => $pid);
				$model -> table('valentine_draw_gift')->where($where)->delete();	
				//删除礼包缓存
				$model -> del_acrivity_gift_redis($aid,$pid);	
			}
			$this -> writelog("已删除id为{$_GET['pid']}的订制类活动奖品",'valentine_draw_gift',$_GET['pid'],__ACTION__ ,'','del');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}	
	//订制活动奖品列表----设置中奖概率
	public function custom_award_probability(){
		$model = D('Sj.CoActivity');
		if($_POST){
			$num2 = explode("/",$_POST['probability']);			
			$pos = strpos($_POST['probability'],"/");
			$pid = $_POST['pid'];
			if(!empty($_POST['probability']) && $pos == false ){
				$this -> error("概率格式错误");
			}else if($_POST['probability'] != 0 && (!is_numeric($num2[1]) || !is_numeric($num2[1]))){
				$this -> error("中奖概率格式出错");
			}else if($_POST['probability'] == ''){
				$_POST['probability'] = 0;
			}
			$where = array('aid'=>$_POST['aid'],'status'=>1,'id'=>array('neq',$pid));
			$list = $model -> table('valentine_draw_prize') -> where($where) ->field('probability')-> select();
			$probability_num = 0;
			foreach($list as $v){
				if(empty($v['probability'])){
					continue;
				}
				$num = explode("/",$v['probability']);
				$calculate = ($num[0]/$num[1]);
				$probability_num = $probability_num + $calculate;
			}
			$calculate2 = ($num2[0]/$num2[1]);
			$probability_num = $probability_num + $calculate2;
			if($probability_num  > 1){
				$this -> error("概率不能大于1");
			}
			$where = array(
				'id' => $_POST['pid'],
				'aid' => $_POST['aid'],
			);			
			$data = array(
				'probability' => $_POST['probability'],
				'update_tm' => time(),
			);		
			$log = $this->logcheck($where, 'valentine_draw_prize', $data, $model);	
			$res = $model -> table('valentine_draw_prize') -> where($where) -> save($data);
			$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if($res){
				$this -> writelog("订制活动编辑概率：id为[{$_POST['pid']}]<br/>".$log,'valentine_draw_prize',$_POST['pid'],__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else{
			$where = array('id'=>$_GET['pid']);
			$list = $model -> table('valentine_draw_prize')  ->where($where)->field('id,probability,name')->find();
			$this -> assign('list',$list);
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> display();
		}
	}	
	//运营预下载---奖品列表
	public function pre_down_operation_award_list(){
		$model = new Model();
		$activity_model = D('Sj.CoActivity');
		$where = array('aid'=> $_GET['id'],'status' => 1);
		$result = $activity_model -> table('pre_down_operation_prize') -> where($where) -> order('level') -> select();
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $_GET['id'])) -> find();
		$page_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $activity_result['activity_page_id'])) ->field('lottery_style')-> find();
		$this -> assign('activity_result',$activity_result);
		$this -> assign('lottery_style',$page_result['lottery_style']);
		$this -> assign('result',$result);
		$this -> display();
	}
	//运营预下载----添加奖品
	public function pre_down_operation_award_add(){
		if($_POST){
			$model = D('Sj.CoActivity');
			$table = "pre_down_operation_prize";
			$ret = $model -> award_add_do($table);	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
				$res = $model -> table($table) -> add($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/pre_down_operation_award_list/id/{$_POST['aid']}");
				if($res){
					//刷礼包缓存、礼包码入库
					if($_POST['type'] == 2 && $data['gift_file']){
						$gift_table = "pre_down_operation_gift";
						$map = array(
							'pid' => $res,
							'aid' => $_POST['aid'],
							'table' => $table,
							'gift_table' => $gift_table
						);
						$model -> brush_gift_redis($map);	
					}					
					$this -> writelog("已添加pid为{$res}的活动奖品",$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('lottery_style',$_GET['lottery_style']);
			$this -> display();
		}
	}
	//运营预下载----编辑奖品
	function pre_down_operation_award_edit(){
		$model = D('Sj.CoActivity');
		$table = "pre_down_operation_prize";
		$gift_table = "pre_down_operation_gift";		
		if($_POST){
			$ret = $model -> award_add_do($table);	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
				$where = array('aid'=>$_POST['aid'],'id'=>$_POST['pid']);
				$res = $model -> table($table)->where($where) -> save($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/pre_down_operation_award_list/id/{$_POST['aid']}");
				if($res){
					//刷礼包缓存、礼包码入库
					if($_POST['type'] == 2 && $data['gift_file']){					
						$map = array(
							'pid' => $_POST['pid'],
							'aid' => $_POST['aid'],
							'table' => $table,
							'gift_table' => $gift_table,
						);
						$model -> brush_gift_redis($map);	
					}					
					$this -> writelog("已编辑pid为{$_POST['pid']}的活动奖品",$table,$_POST['pid'],__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{
			$where = array('aid'=>$_GET['aid'],'id'=>$_GET['pid']);
			$list = $model -> table($table) ->where($where)->find();
			if($list['type'] == 2){
				$where = array('aid'=>$_GET['aid'],'pid'=>$_GET['pid']);
				$count = $model -> table($gift_table) ->where($where)->count();
				$this -> assign('gift_count',$count);
			}			
			$this -> assign('list',$list);
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> assign('lottery_style',$_GET['lottery_style']);
			$this -> display('pre_down_operation_award_add');
		}
	}
	//运营预下载----设置中奖概率
	public function pre_down_operation_award_probability($table = '' ){
		$model = D('Sj.CoActivity');
		if(!$table){
			$table = "pre_down_operation_prize";
		}
		if($_POST){
			$num2 = explode("/",$_POST['probability']);			
			$pos = strpos($_POST['probability'],"/");
			$pid = $_POST['pid'];
			if(!empty($_POST['probability']) && $pos == false ){
				$this -> error("概率格式错误");
			}else if($_POST['probability'] != 0 && (!is_numeric($num2[1]) || !is_numeric($num2[1]))){
				$this -> error("中奖概率格式出错");
			}else if($_POST['probability'] == ''){
				$_POST['probability'] = 0;
			}
			$where = array('aid'=>$_POST['aid'],'status'=>1,'id'=>array('neq',$pid));
			$list = $model -> table($table) -> where($where) ->field('probability')-> select();
			$probability_num = 0;
			foreach($list as $v){
				if(empty($v['probability'])){
					continue;
				}
				$num = explode("/",$v['probability']);
				$calculate = ($num[0]/$num[1]);
				$probability_num = $probability_num + $calculate;
			}
			$calculate2 = ($num2[0]/$num2[1]);
			$probability_num = $probability_num + $calculate2;
			if($probability_num  > 1){
				$this -> error("概率不能大于1");
			}
			$where = array(
				'id' => $_POST['pid'],
				'aid' => $_POST['aid'],
			);			
			$data = array(
				'probability' => $_POST['probability'],
				'update_tm' => time(),
			);		
			$log = $this->logcheck($where, $table, $data, $model);	
			$res = $model -> table($table) -> where($where) -> save($data);
			$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if($res){
				$this -> writelog("运营预下载活动编辑概率：id为[{$_POST['pid']}]<br/>".$log,$table,$_POST['pid'],__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else{
			$where = array('id'=>$_GET['pid']);
			$list = $model -> table($table)  ->where($where)->field('id,probability,name')->find();
			$this -> assign('list',$list);
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> display();
		}
	}
	//运营预下载----删除
	function pre_down_operation_award_del(){
		$model = D('Sj.CoActivity');
		$map = array(
			'update_tm' => time(),
			'status' => 0,
		);
		$aid = $_GET['aid'];
		$pid = $_GET['pid'];		
		$where = array('aid' => $aid,'id' => $pid);
		$result = $model -> table('pre_down_operation_prize')->where($where)->save($map);	
		$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		if($result){
			if($_GET['type'] == 2){
				$where = array('aid' => $aid,'pid' => $pid);
				$model -> table('pre_down_operation_gift')->where($where)->delete();	
				//删除礼包缓存
				$model -> del_acrivity_gift_redis($aid,$pid);				
			}
			$this -> writelog("已删除id为{$pid}的运营预下载活动奖品",'pre_down_operation_prize',$pid,__ACTION__ ,'','edit');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	//签到模板---奖品列表
	public function sign_award_list(){
		$model = new Model();
		$activity_model = D('Sj.CoActivity');
		$id = $_GET['id'];
		$result = $activity_model -> table('sign_prize') -> where(array('aid' => $id,'status' => 1)) -> order('level') -> select();
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $id))-> find();
		foreach($result as $key => $val){
			$odds_result = $activity_model -> table('gm_probability') -> where(array('pid' => $val['pid'])) -> select();
			if($odds_result){
				$val['is_edit'] = 1;
			}else{
				$val['is_edit'] = 0;
			}
			$result[$key] = $val;
		}
		
		$page_result	=	$model -> table('sj_activity_page') -> where(array('ap_id' => $activity_result['activity_page_id'])) ->field('lottery_style')-> find();
		$this -> assign('lottery_style',$page_result['lottery_style']);
		$this -> assign('activity_result',$activity_result);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	//签到模板----添加奖品
	public function sign_award_add(){
		if($_POST){
			$model = D('Sj.CoActivity');
			$ret = $model -> sign_award_add_do('sign_prize');
			if($ret['code'] == 1){
				$config	= $ret['config'];
				$data	= $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$res = $model -> table('sign_prize') -> add($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/sign_award_list/id/{$_POST['aid']}");
				if($res){
					$log = "已添加签到模板活动奖品pid为{$res},aid为{$_POST['aid']}，奖品名称为{$_POST['name']},奖品数量：{$_POST['prize_num']},奖品说明：{$_POST['desc']},奖品等级：{$_POST['level']}";
					$this -> writelog($log,'sign_prize',$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$model = new Model();
			$activity_result	=	$model -> table('sj_activity') -> where(array('id' => $_GET['aid'])) -> find();
			$page_result		=	$model -> table('sj_activity_page') -> where(array('ap_id' => $activity_result['activity_page_id'])) ->field('lottery_style')-> find();
			$this -> assign('lottery_style',$page_result['lottery_style']);
			$this -> assign('aid',$_GET['aid']);
			$this -> display();
		}
	}
	//签到奖品列表----编辑奖品
	function sign_award_edit(){
		$model = D('Sj.CoActivity');
		if($_POST){
			$ret = $model -> sign_award_add_do('sign_prize');
			if($ret['code'] == 1){
				$config	= $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$where = array('id'=>$_POST['id']);
				$log = $this->logcheck($where, 'sign_prize', $data, $model);
				$res = $model -> table('sign_prize')->where($where) -> save($data);
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/sign_award_list/id/{$_POST['aid']}");
				if($res){
					$this -> writelog("签到模板活动编辑：id为[{$_POST['pid']}]<br/>".$log,'sign_prize',$_POST['pid'],__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$where = array('aid'=>$_GET['aid'],'id'=>$_GET['pid']);
			$list = $model -> table('sign_prize') ->where($where)->find();
			$gift_arr	=	json_decode($list['gift_file'],true);
			$gift_count	=	count($gift_arr);
			
			$model_nmk = new Model();
			$activity_result	=	$model_nmk -> table('sj_activity') -> where(array('id' => $_GET['aid'])) -> find();
			$page_result		=	$model_nmk -> table('sj_activity_page') -> where(array('ap_id' => $activity_result['activity_page_id'])) ->field('lottery_style')-> find();
			
			$this -> assign('gift_arr',$gift_arr);
			$this -> assign('list',$list);
			$this -> assign('gift_count',$gift_count);
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> assign('lottery_style',$page_result['lottery_style']);
			$this -> display('sign_award_add');
		}
	}
	//签到列表----删除
	function sign_award_del(){
		$model = D('Sj.CoActivity');
		$map = array(
				'update_tm' => time(),
				'status' => 0,
		);
		$aid = $_GET['aid'];
		$id = $_GET['id'];
		$prefix = "custom";
		$where = array('aid' => $aid,'id' => $id);
		$result = $model -> table('sign_prize')->where($where)->save($map);
		$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		if($result){
			$this -> writelog("已删除id为{$_GET['pid']}的签到模板活动奖品",'valentine_draw_gift',$_GET['pid'],__ACTION__ ,'','del');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	//签到模板----设置中奖概率
	public function sign_award_probability(){
		$model = D('Sj.CoActivity');
		if($_POST){
			$num2 = explode("/",$_POST['probability']);
			$pos = strpos($_POST['probability'],"/");
			$pid = $_POST['pid'];
			if(!empty($_POST['probability']) && $pos == false ){
				$this -> error("概率格式错误");
			}else if($_POST['probability'] != 0 && (!is_numeric($num2[1]) || !is_numeric($num2[1]))){
				$this -> error("中奖概率格式出错");
			}else if($_POST['probability'] == ''){
				$_POST['probability'] = 0;
			}
			$where = array('aid'=>$_POST['aid'],'status'=>1,'id'=>array('neq',$pid));
			$list = $model -> table('sign_prize') -> where($where) ->field('probability')-> select();
			$probability_num = 0;
			foreach($list as $v){
				if(empty($v['probability'])){
					continue;
				}
				$num = explode("/",$v['probability']);
				$calculate = ($num[0]/$num[1]);
				$probability_num = $probability_num + $calculate;
			}
			$calculate2 = ($num2[0]/$num2[1]);
			$probability_num = $probability_num + $calculate2;
			if($probability_num  > 1){
				$this -> error("概率不能大于1");
			}
			$where = array(
					'id' => $_POST['pid'],
					'aid' => $_POST['aid'],
			);
			$data = array(
					'probability' => $_POST['probability'],
					'update_tm' => time(),
			);
			$log = $this->logcheck($where, 'sign_prize', $data, $model);
			$res = $model -> table('sign_prize') -> where($where) -> save($data);
			$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if($res){
				$this -> writelog("签到模板编辑概率：id为[{$_POST['pid']}]<br/>".$log,'sign_prize',$_POST['pid'],__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else{
			$where = array('id'=>$_GET['pid']);
			$list = $model -> table('sign_prize')  ->where($where)->field('id,probability,name')->find();
			$this -> assign('list',$list);
			$this -> assign('aid',$_GET['aid']);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> display();
		}
	}
	
	//签到上传包名
	function sign_package_view()
	{
		$model	=	D('Sj.CoActivity');
		if($_FILES['package']['tmp_name']) {
			$array = array('csv');
			$ytypes = $_FILES['package']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			if(!in_array($type,$array)){
				echo '上传格式错误';die;
			}
			//验证礼包重复数据
			$data_file = file_get_contents($_FILES['package']['tmp_name']);
			//判断是否是utf-8编辑
			if(mb_check_encoding($data_file,"utf-8") != true){
				$data_file = iconv("gbk","utf-8", $data_file);
			}
			$data_file	=	str_replace("\r\n","\n",$data_file);
			$data_arr	=	explode("\n", $data_file);
			$data_arr	=	array_unique($data_arr);
			$package	=	array();
			foreach ($data_arr as $k => $v) {
				if( $k == 0 ) {
					continue;
				}
				if( !empty($v) ) {
					$package[] = $v;
				}
			}
			$gift_data = array();
			$count		=	count($package);
			$page		=	1;
			$pageSize	=	20;
			$total		=	$count/$pageSize;
			for($i = 0; $i<$total; $i++) {
				$startNum	=	($page-1)*$pageSize;
				$val = 	array_slice($package, $startNum, $pageSize);
				$result = $model->httpGet($val);
				if( $result['code'] == 200 && !empty($result['result']) ) {
					foreach ($val as $key => $val) {
						foreach ($result['result'] as $k => $v) {
							if( $val == $v['giftSoftPname'] ){
								$gift_data[$val][] = array(
										'id'			=>	$v['id'],
										'giftSoftName'	=>	$v['giftSoftName'],
										'giftName'		=>	$v['giftName'],
								);
							}
						}
					}
				}
				$page++;
			}
				
			if( !empty($gift_data) ) {
				//上传包名
				$date	 = date("Ym/d/");
				$dir_img = C('ACTIVITY_CSV') . '/package/'.$date;
				if(!is_dir($dir_img)) {
					if(!mkdir($dir_img,0777,true)) {
						//创建pagckage目录{$dir_img}失败
						$return = array(
								'code' => 0,
								'msg' => "创建pagckage目录{$dir_img}失败",
						);
						return $return;
					}
				}
				list($msec,$sec) = explode(' ',microtime());
				$types = 'csv';
				$msec = substr($msec,2);
				$dst = $dir_img.'package'.$_POST['aid'].'_'.$_POST['pid'].'_'.$msec.'.'.$types;
				if(move_uploaded_file($_FILES['package']['tmp_name'],$dst)) {
					$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
					echo "<input type='hidden' id='pkg_path' name='pkg_path' value='{$path}'>";
				}
				
				$str = "<ul style='list-style-type: none;text-align: left;'>";
				foreach ( $gift_data as $k => $v ) {
					$str .= "<li>{$k}</li>";
					$str .= "<li>";
					foreach ( $v as $kk => $vv) {
						$value = $k.":".$vv['id'];
						$str .= "<input type='checkbox' checked name='code' value='{$value}'/>{$vv['giftName']}";
					}
					$str .= '</li>';
				}
				$str .= "</ul>";
				echo $str;
			}else{
				echo '未找到包名对应的礼包';
			}
			
		}else{
			echo '请上传包名';
		}
	}
	
	
	//签到模板列表
	function sign_template_list()
	{
		$aid = is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
		if( !$aid ) {
			$this->error('参数有误!');
		}
		$model		=	D('Sj.CoActivity');
		$model_gmk	=	D('');
		$redis = new redis();
		$redis->connect(C('LOTTERY_REDIS_HOST'),C('LOTTERY_REDIS_PORT'));
		$activityInfo		=	$model_gmk->table('sj_activity')->where(array('id'=>$aid))->find();
		$activity_page_id	=	$activityInfo['activity_page_id'];
		if(!$activity_page_id) {
			$this->error('未选择签到模板!');
		}
		$activityPageInfo = $model_gmk->table('sj_activity_page')->where(array('ap_id'=>$activity_page_id))->find();
		//配置签到数
		$sign_num = $activityPageInfo['like_limit'] ? $activityPageInfo['like_limit'] : 0;
		if(!$sign_num) {
			$this->error('未设置签到天数!');
		}
		//当前签到数
		$sign_cur_num = $model->table('sign_prize_icon')->where(array('aid'=> $aid,'status'=> 1))->count();
		if( empty($sign_cur_num) ) {
			$up_data	=	$model->table('sign_prize_icon')->where(array('aid'=> $aid, 'status'=>0))->order('level asc')->select();
			$up_count	=	count($up_data);
			if( $up_data ) {
				if($sign_num > $up_count) {
					foreach ($up_data as $k => $v) {
						$model->table('sign_prize_icon')-> where(array('id'=>$v['id']))->save(array('status'=>1));
					}
					$j = $sign_num - $up_count;
					$cur	=	$model->table('sign_prize_icon')->where(array('aid'=> $aid, 'status'=>1))->count();
					$data = array(
							'aid'		=>	$aid,
							'status'	=>	1,
					);
					for ($i = 1; $i <= $j; $i++ ) {
						$data['level'] = $cur + $i;
						$model->table('sign_prize_icon')->add($data);
					}
				}elseif ($sign_num < $up_count) {
					$i = 1;
					foreach ($up_data as $k => $v) {
						if($i <= $sign_num) {
							$model->table('sign_prize_icon')-> where(array('id'=>$v['id']))->save(array('status'=>1));
						}
						$i++;
					}
				}else if ($sign_num == $up_count){
					foreach ($up_data as $k => $v) {
						$model->table('sign_prize_icon')-> where(array('id'=>$v['id']))->save(array('status'=>1));
					}
				}
			}else {
				for ($i = 1; $i <= $sign_num; $i++ ) {
					$data = array(
							'aid'		=>	$aid,
							'status'	=>	1,
							'level'		=>	$i,
					);
					$model->table('sign_prize_icon')->add($data);
				}
			}
			$redis -> delete("sign:{$aid}:sign_icon");
		}else {
			if( $sign_num > $sign_cur_num ) {
				//计算差值
				$n = $sign_num - $sign_cur_num;
				$data = array(
						'aid'		=>	$aid,
						'status'	=>	1,
				);
				$up_data	=	$model->table('sign_prize_icon')->where(array('aid'=> $aid, 'status'=>0))->order('level asc')->limit("0,{$n}")->select();
				$up_count	=	count($up_data);
				if(!empty($up_data)) {
					foreach ($up_data as $k => $v) {
						$model->table('sign_prize_icon')-> where(array('id'=>$v['id']))->save(array('status'=>1));
					}
					$j = $n - $up_count;
					if( $j > 0 ){
						$cur	=	$model->table('sign_prize_icon')->where(array('aid'=> $aid, 'status'=>1))->count();
						for ($i = 1; $i <= $j; $i++ ) {
							$data['level'] = $cur + $i;
							$model->table('sign_prize_icon')->add($data);
						}
					}
				}else {
					for ($i = 1; $i <= $n; $i++ ) {
						$data['level'] = $sign_cur_num + $i;
						$model->table('sign_prize_icon')->add($data);
					}
				}
				$redis -> delete("sign:{$aid}:sign_icon");
			}else if( $sign_num < $sign_cur_num) {
				$m = $sign_cur_num - $sign_num;
				$del_data = $model->table('sign_prize_icon')->where(array('aid'=> $aid, 'status'=>1))->order('level desc')->limit("0,{$m}")->select();
				foreach ($del_data as $k => $v) {
					$model->table('sign_prize_icon')-> where(array('id'=>$v['id']))->save(array('status'=>0));
				}
				$redis -> delete("sign:{$aid}:sign_icon");
			}
		}
		
		$result = $model->table('sign_prize_icon')->where(array('aid'=> $aid, 'status'=> 1))->order('level asc')->select();
		$this -> assign('is_telephone',$activityPageInfo['is_telephone']);
		$this -> assign('activityInfo',$activityInfo);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	//签到模板编辑
	function sign_template_edit()
	{
		$id				=	is_numeric($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$aid			=	is_numeric($_REQUEST['aid']) ? (int)$_REQUEST['aid'] : null;
		$is_telephone	=	is_numeric($_REQUEST['is_telephone']) ? (int)$_REQUEST['is_telephone'] : null;
		
		$model	=	D('Sj.CoActivity');
		if( !$id ) {
			$this->error('参数有误!');
		}
		if($_POST) {
			$table = "sign_prize_icon";
			$ret = $model -> sign_template_add_do($table);
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				if ( !empty($config['multi_config']) ) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$res = $model -> table($table) ->where(array('id'=>$id))->save($data);
				if($_POST['is_copy'] == 1 && $_POST['level'] == 1){
					$list = $model -> table($table) ->where(array('id'=>$id))->find();
					//复制ICON功能
					$map = array(
						'icon_in'			=>	$list['icon_in'],
						'icon_not_in'		=>	$list['icon_not_in'],
						'icon_be_over'		=>	$list['icon_be_over'],
						'icon_not_start'	=>	$list['icon_not_start'],
						'add_tm' => $list['add_tm'],
						'update_tm' => $list['update_tm'],
					);
					$where = array(
						'aid' => $_POST['aid'],
						'status'=>1
					);
					$model -> table($table) ->where($where)->save($map);
					$log = "【并且复制icon】";
				}
				$this -> assign('jumpUrl',"/index.php/Sj/Activity/sign_template_list/id/{$_POST['aid']}");
				if($res){
					$redis = new redis();
					$redis -> connect(C('LOTTERY_REDIS_HOST'),C('LOTTERY_REDIS_PORT'));
					$redis -> delete("sign:{$aid}:sign_icon");
					$this -> writelog("已添加pid为{$res}的签到活动奖品".$log,$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$list = $model->table('sign_prize_icon')->where(array('id'=> $id))->find();
			$this -> assign('is_telephone',$is_telephone);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> assign('id',$id);
			$this -> assign('list',$list);
			$this -> display();
		}
	}
	//导出未发放礼包
	public function acrivity_export_gift(){
		$model	=	D('Sj.CoActivity');
		$tab_con = array(
			1 => 'valentine_draw_gift', //定制
			2 => 'gm_virtual_prize',//通用
			5 => 'gm_virtual_prize',//充值消费类活动
			6 => 'gm_virtual_prize',//新通用预约活动
			7 => 'pre_down_operation_gift',//运营预下载活动
			8 => 'sign_gift',//签到模板
		);			
		$this -> writelog("已导出为aid:{$_GET['aid']},pid:{$_GET['pid']}的导出未发放礼包",$tab_con[$_GET['type']],$_GET['pid'],__ACTION__ ,'','edit');
		$result = $model->export_acrivity_gift($_GET['type'],$_GET['aid'],$_GET['pid']);
	}

	public function start_activty() {
        $model = D('Sj.Activity');
        
        $is_start = $_GET['is_start'];

        $id_arr = explode(',',$_GET['id']);
        $where = array(
            'id' => array('in', $id_arr),
        );
        $data = array(
            'last_refresh' => time(),
            'is_start' =>$is_start,
        );
        
        $res = $model->where($where)->save($data);
        if ($res) {
            $this->writelog("启动了id为{$_GET['id']}的活动", 'sj_activity', $_GET['id'], __ACTION__,"","edit");
            $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/Activity/showActivityList/1/1');
            $this->success("启动成功");
        }else{
            $this->success("启动失败");
        }
    }
}
?>
