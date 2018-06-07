<?php
/*
*软件管理__审核中列表
*
*
*
*
*
*
*
************************************************/

define('ABI_ARMEABI', 1);
define('ABI_ARMEABI_V7A', 2);
define('ABI_X86', 4);
define('ABI_MIPS', 8);
class SoftwareReviewAction extends CommonAction {
	//新软件审核
	public function newsoft_audit_list(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);
		$record_type =1;
		$status =2;

		if($_GET){
			if(isset($_GET['softid'])){
				$this->assign('softid',$_GET['softid']);
			}		
			if(isset($_GET['dev_id'])){
				$this->assign('dev_id',$_GET['dev_id']);
			}	
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['package'])){
				$this->assign('package',$_GET['package']);
			}
			if(isset($_GET['ip']) ){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}	
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this->assign('ad_str',$_GET['ad_type']);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);			
			}
			
			//起止日期
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$this->assign('begintime',$_GET['begintime']);
				$endtime = strtotime($_GET['endtime'])+86399;
				if($endtime<$begintime)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime',$_GET['endtime']);
			}
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip(array_filter($cateids));
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}	
			//联运状态
			if(isset($_GET['is_sdk'])){
				$this->assign("is_sdk", $_GET['is_sdk']);
			}	
		}
		$room_total = $this->getroomtotal('config_room_1');
		if($room_total)   $this->assign("room_total", $room_total);

		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page,$reason_list) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit,$room_total);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark ->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);		
		//专题
		$model = M('feature');
		$featurelist = $model->where('status=1')->field('feature_id, name')->order('orderid')->select();
		$result = $this->get_remind_words($result);
		$this->assign('featurelist', $featurelist);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		$this -> assign('reason_list',$reason_list);
		//url路径__下载量排序和更新时间排序
	 	$param = $_GET;
		if($param['orderby'] == 'upload_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display();
		$this ->display('Public:check_reject');
	}
	//修改描述审核
	public function edit_audit(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);
		$record_type =2;
		$status =2;
		if($_GET){
			if(isset($_GET['package_id'])){
				$this->assign('package_id_type',$_GET['package_id_type']);
				$this->assign('package_id',$_GET['package_id']);
			}
			if(isset($_GET['dev_id'])){
				$this->assign('dev_id',$_GET['dev_id']);
			}				
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['ip'])){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);
				$this->assign('ad_str',$_GET['ad_type']);				
			}
			//起止日期
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$this->assign('begintime',$_GET['begintime']);
				$endtime = strtotime($_GET['endtime'])+86399;
				if($endtime<$begintime)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime',$_GET['endtime']);
			}
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}		
			//联运状态
			if(isset($_GET['is_sdk'])){
				$this->assign("is_sdk", $_GET['is_sdk']);
			}
		}
		$room_total = $this->getroomtotal('config_room_2');
		if($room_total)   $this->assign("room_total", $room_total);
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page,$reason_list) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit,$room_total);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark ->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);	
		
		$result = $this->get_remind_words($result);
		
		//专题
		$model = M('feature');
		$featurelist = $model->where('status=1')->field('feature_id, name')->order('orderid')->select();
		$this->assign('featurelist', $featurelist);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		$this -> assign('reason_list',$reason_list);
		
		//url路径__下载量排序和更新时间排序
	 	$param = $_GET;
		if($param['orderby'] == 'download'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order',$param['order']);				
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'upload_tm'){
			
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order1',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display();
	}
	//版本升级审核
	public function softupgrade_audit(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();	
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);	
		if(isset($_GET['form_type'])){
			$this->assign('form_type',$_GET['form_type']);
		}else{
			$this->assign('form_type','softupgrade');
		}
		$record_type =3;
		$status =2;
		if($_GET){
			if(isset($_GET['package_id'])){
				$this->assign('package_id_type',$_GET['package_id_type']);
				$this->assign('package_id',$_GET['package_id']);
			}
                                                    //联运状态
			if(isset($_GET['is_sdk'])){
				$this->assign("is_sdk", $_GET['is_sdk']);
			}
			if(isset($_GET['update_from'])){
				$this->assign('update_from',$_GET['update_from']);
			}
			if(isset($_GET['dev_id'])){
				$this->assign('dev_id',$_GET['dev_id']);
			}				
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['package'])){
				$this->assign('package',$_GET['package']);
			}
			if(isset($_GET['ip'])){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//搜索官方认证
			if(!empty($_GET['Official'])){
				$this->assign("Official", $_GET['Official']);
			}			
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);	
				$this->assign('ad_str',$_GET['ad_type']);				
			}
			//起止日期
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$this->assign('begintime',$_GET['begintime']);
				$endtime = strtotime($_GET['endtime'])+86399;
			    if($endtime<$begintime)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime',$_GET['endtime']);
			}
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}
			//官方认证
			if(isset($_GET['is_note'])){
				$this->assign("is_note", $_GET['is_note']);
			}
		}
		$room_total = $this->getroomtotal('config_room_3');
		if($room_total)   $this->assign("room_total", $room_total);
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page,$reason_list) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit,$room_total);
		//闪屏
		foreach($result as $k=>$v){
			$soft_screen = M('soft_screen');
			$sj_soft_screen = $soft_screen ->where("status=2 and package='{$v['package']}'")->field('id')->select();
			if($sj_soft_screen){
				$result[$k]['shanpin']=1;
			}else{
				$result[$k]['shanpin']=0;
			}
		}
		//echo "<pre>";print_r($result);;exit;
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark ->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);	

		$result = $this->get_remind_words($result);
		//专题
		$model = M('feature');
		$featurelist = $model->where('status=1')->field('feature_id, name')->order('orderid')->select();
		$this->assign('featurelist', $featurelist);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		$this -> assign('reason_list',$reason_list);
		//url路径--下载量排序--提交时间排序
		$param = $_GET;
		if($param['orderby'] == 'download'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'upload_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order1',$param['order']);	
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display();
		$this ->display('Public:check_reject');
	}
	//首发软件审核
	public function softdebut_audit(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
        $this -> assign('alladlist',$adlist_str);
		$record_type =3;
		$status =2;
		if($_GET){
			if(isset($_GET['package_id'])){
				$this->assign('package_id_type',$_GET['package_id_type']);
				$this->assign('package_id',$_GET['package_id']);
			}
			if(isset($_GET['form_type'])){
				$this->assign('form_type',$_GET['form_type']);
			}
			if(isset($_GET['update_from'])){
				$this->assign('update_from',$_GET['update_from']);
			}
			if(isset($_GET['dev_id'])){
				$this->assign('dev_id',$_GET['dev_id']);
			}				
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['package'])){
				$this->assign('package',$_GET['package']);
			}
			if(isset($_GET['ip'])){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);
                $this->assign('ad_str',$_GET['ad_type']);
			}
			//起止日期
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$this->assign('begintime',$_GET['begintime']);
				$endtime = strtotime($_GET['endtime'])+86399;
			    if($endtime<$begintime)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime',$_GET['endtime']);
			}
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}				
		}
		$room_total = $this->getroomtotal('config_room_3');
		if($room_total)   $this->assign("room_total", $room_total);
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page,$reason_list) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit,$room_total);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark ->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);	

		$result = $this->get_remind_words($result);
		//专题
		$model = M('feature');
		$featurelist = $model->where('status=1')->field('feature_id, name')->order('orderid')->select();
		$this->assign('featurelist', $featurelist);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		$this -> assign('reason_list',$reason_list);
		//url路径--下载量排序--提交时间排序
		$param = $_GET;
		if($param['orderby'] == 'but_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order2',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'upload_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order1',$param['order']);	
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display('Dev:SoftwareReview:softupgrade_audit');
		$this ->display('Public:check_reject');
	}
	//定时上架软件
	public function time_shelves(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);	
		$this->assign('form_type','time_shelves');
		//$record_type =3;
		$status =2;
		if($_GET){
			if(isset($_GET['package_id'])){
				unset($_GET['is_bj_shield']);
				$this->assign('package_id_type',$_GET['package_id_type']);
				$this->assign('package_id',$_GET['package_id']);
			}else{
				//屏蔽北京市
				if(isset($_GET['is_bj_shield'])){
					$this->assign('is_bj_shield',$_GET['is_bj_shield']);
				}	
			}
			if(isset($_GET['update_from'])){
				$this->assign('update_from',$_GET['update_from']);
			}
			if(isset($_GET['dev_id'])){
				$this->assign('dev_id',$_GET['dev_id']);
			}				
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['package'])){
				
				$this->assign('package',$_GET['package']);
			}
			if(isset($_GET['ip'])){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);	
				$this->assign('ad_str',$_GET['ad_type']);	
			}
			//起止日期
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$this->assign('begintime',$_GET['begintime']);
				$endtime = strtotime($_GET['endtime'])+86399;
			    if($endtime<$begintime)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime',$_GET['endtime']);
			}
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}		
				
		}
		$room_total = $this->getroomtotal('config_room_3');
		if($room_total)   $this->assign("room_total", $room_total);
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page,$reason_list) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit,$room_total);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark ->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);	

		$result = $this->get_remind_words($result);
		//专题
		$model = M('feature');
		$featurelist = $model->where('status=1')->field('feature_id, name')->order('orderid')->select();
		$this->assign('featurelist', $featurelist);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		$this -> assign('reason_list',$reason_list);
		//url路径--下载量排序--提交时间排序
		$param = $_GET;
		if($param['orderby'] == 'but_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order2',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'upload_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order1',$param['order']);	
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display();
	}
	//申请下架审核
	public function  nextframe_audit(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);	
		$record_type =4;
		$status =2;
		if($_GET){
			if(isset($_GET['package_id'])){
				$this->assign('package_id_type',$_GET['package_id_type']);
				$this->assign('package_id',$_GET['package_id']);
			}		
			if(isset($_GET['softid'])){
				$this->assign('softid',$_GET['softid']);
			}	
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['package'])){
				$this->assign('package',$_GET['package']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['ip'])){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//角标类型
			if(isset($_GET['type'])){
				$this->assign('type',$_GET['type']);
			}
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);	
			    $this->assign('ad_str',$_GET['ad_type']);
			}
			//起止日期
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$this->assign('begintime',$_GET['begintime']);
				$endtime = strtotime($_GET['endtime'])+86399;
				if($endtime<$begintime)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime',$_GET['endtime']);
			}
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}	
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}				
		}
		$room_total = $this->getroomtotal('config_room_4');
		if($room_total)   $this->assign("room_total", $room_total);
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page,$reason_list) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit,$room_total);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		$result = $this->get_remind_words($result);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark->where("status=1")->field('id,name') ->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);				
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		$this -> assign('reason_list',$reason_list);
		//url路径
	 	$param = $_GET;
		if($param['orderby'] == 'download'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order',$param['order']);	
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'upload_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order1',$param['order']);					
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display();
	}
	//未通过列表
	public function  not_through(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);
		$status = 3;
		if($_GET){
			if(isset($_GET['softids'])){
				$this->assign('softids',$_GET['softids']);
			}	
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['package'])){
				$this->assign('package',$_GET['package']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['ip'])){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//角标类型
			if(isset($_GET['type'])){
				$this->assign('type',$_GET['type']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}			
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);		
				$this->assign('ad_str',$_GET['ad_type']);
			}
			//起止日期
			if(!empty($_GET['begintime_a']) && !empty($_GET['endtime_a'])){
				$begintime_a = strtotime($_GET['begintime_a']);
				$this->assign('begintime_a',$_GET['begintime_a']);
				$endtime_a = strtotime($_GET['endtime_a']);
				if($endtime_a<$begintime_a)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime_a',$_GET['endtime_a']);
			}
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}	
		}
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark->where("status=1")->field('id,name') ->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);				
		//url路径
	 	$param = $_GET;
		if($param['orderby'] == 'download'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'time'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order1',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display();
	}
	//新软件通过
 	function newsoft_pass(){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		//操作临时表，通过
		list($error,$msg_str,$add_result) = $soft_tmp -> update_tmp($id_arr,1);
		//记录操作日志
		foreach($add_result as $v){
			if($v['softid']){
				//操作日志
				$this->writelog("通过了softid为{$v['softid']}包名为{$v['package']}的软件",'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
				//软件后台操作数据日志
				$soft_obj = M('soft');
				update_data_log($v['softid'],'add',$soft_obj);
			}
			if($v['pass_time'] && $v['pass_status'] == 1){
				$this->writelog("通过了包名【{$v['package']}】新软件审核，定时上架时间设置为：".date("Y-m-d H:i:s",$v['pass_time']),'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
			}			
		}	
		//$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	
	//定时上架直接上架
	public function time_shelves_up(){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		
		$record_type = explode(',',$_GET['form_type']);
		//var_dump(count(array_unique($record_type)));		

		$soft_arr = array();
		foreach($id_arr as $k=>$v){
			$soft_arr[$record_type[$k]][] = $id_arr[$k];
		}

		if($soft_arr){
			$last_error = $last_msg_str = '';
			foreach($soft_arr as $k=>$v){	
				list($error,$msg_str,$add_result) = $soft_tmp -> update_tmp($v,$k,1);
				foreach($add_result as $v){
					if($v['update_from']){
						$this->writelog("直接上架包名【{$v['package']}】的定时上架软件，原id为{$v['update_from']}新ID为{$v['softid']}",'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
					}else{
						$this->writelog("直接上架包名【{$v['package']}】的定时上架软件，ID为{$v['softid']}",'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
					}
					$soft_obj = M('soft');
					update_data_log($v['softid'],'update',$soft_obj);
				}
				if(!empty($error)){
					$last_error .= $error.'\n';
				}
				if(!empty($msg_str)){
					$last_msg_str .= $msg_str.'\n';
				}
				
			}
			$last_id = implode(',',$id_arr);
			if(!empty($last_error)){
				exit(json_encode(array('code'=>'2','error'=>$last_error,'msg'=>$last_id)));
			}else if(!empty($last_msg_str)){
				exit(json_encode(array('code'=>0,'msg'=>$last_msg_str)));
			}else{
				exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
			}	
		}else{
			//操作临时表，通过
			list($error,$msg_str,$add_result) = $soft_tmp -> update_tmp($id_arr,$record_type[0],1);
			foreach($add_result as $v){
				//操作日志
				if($v['update_from']){
					$this->writelog("直接上架包名【{$v['package']}】的定时上架软件，原id为{$v['update_from']}新ID为{$v['softid']}",'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
					//软件后台操作数据日志
				}else{
					$this->writelog("直接上架包名【{$v['package']}】的定时上架软件，ID为{$v['softid']}",'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
				}
				$soft_obj = M('soft');
				update_data_log($v['softid'],'update',$soft_obj);
			}

			//$msg_str = nl2br($msg_str);
			if(!empty($error) && $error != ''){
				exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
			}else if(!empty($msg_str) && $msg_str != ''){
				exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
			}else{
				exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
			}		
		}
		
	}
	
	
	//版本升级通过
	public function updatesoft_pass(){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$id_str = implode(',',$id_arr);
		//操作临时表，通过
		list($error,$msg_str,$add_result) = $soft_tmp -> update_tmp($id_arr,3);
		foreach($add_result as $v){
			//操作日志
			if($v['softid']){
				$this->writelog("通过了包名【{$v['package']}】版本升级审核，原id为{$v['update_from']}新ID为{$v['softid']}",'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
				//软件后台操作数据日志
				$soft_obj = M('soft');
				update_data_log($v['softid'],'update',$soft_obj);
			}
			
			if($v['pass_time'] && $v['pass_status'] == 1){
				$this->writelog("通过了包名【{$v['package']}】版本升级审核，原id为{$v['update_from']}定时上架时间设置为：".date("Y-m-d H:i:s",$v['pass_time']),'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
			}
		}

		//$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//描述审核通过
	public function describe_pass(){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		list($error,$msg_str,$add_result) = $soft_tmp -> update_soft($id_arr);

		foreach($add_result as $v){
			//操作日志
			if($v['softid']){
				$this->writelog("通过了softid为{$v['softid']}包名为{$v['package']}的软件",'sj_soft_tmp',$v['softid'],__ACTION__ ,"","edit");
			}
			$soft_obj = M('soft');
			update_data_log($v['softid'],'update',$soft_obj);
			
		}
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//申请下架通过验证剩余下载量是否>100w
	public function pub_downloaded_surplus_check(){
		$model = new Model();
		$id_arr = explode(',',$_POST['id']);
		$list = $model ->table('sj_soft_tmp')-> where(array('id'=>array('in',$id_arr)))->field('softid') -> select();
		$softid = array();
		foreach($list as $v){
			$softid[] = $v['softid'];
		}
		$softlist = $model ->table('sj_soft')-> where(array('softid'=>array('in',$softid)))->field('softid,softname,total_downloaded,total_downloaded_detain,total_downloaded_add') -> select();
		$softname = '';
		foreach($softlist as $v){
			if($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add'] > 1000000){
				$softname .= $v['softname']."、";
			}
		}
		if($softname){
			$softname = mb_substr($softname,0,-1, 'UTF-8')."。";
			exit(json_encode(array('code'=>1,'msg'=>$softname)));
		}else{
			exit(json_encode(array('code'=>0)));
		}
	}
	//申请下架通过
	public function undercarriage_pass(){
		$soft_tmp = D("soft_tmp");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$tmp = $soft_tmp -> where(array('id'=>array('in',$id_arr)))->field('id,softid,status,package,dev_id,softname,shelf_reason') -> select();
		$time = time();
		$pkg_key = array();
		$data = array();
		$tmpid = array();
		$softname_arr = array();
		$soft_obj = M('soft');
		$model = new Model();
		$msg_str = '';
		$msg = "开发者主动申请下架";
		foreach($tmp as $v){
			if($v['status'] == 2){
				$down = $soft_obj -> where("softid={$v['softid']}") ->field('softid,hide')->find();
				if($down['hide'] == 1){
					$result = $soft_obj -> where("softid={$v['softid']}") -> save(array('hide'=>3,'last_refresh'=>$time,'review_time'=>$time,'deny_msg'=>$v['shelf_reason']));
					$data[$v['softid']] = $v;
					if($v['dev_id'] != 0 && $result){
						if($v['package']) $pkg_key[$v['package']] = 1; 
						$softname_arr[$v['package']] = $v['softname'];
					}	
					if($result){
						//修改sj_soft_status表
						//update_soft_status(array('soft_status'=>60,'update_tm'=>$time,'version'=>''),$v['package']);
													
						$this->writelog("下架了softid为{{$v['softid']}}包名为{$v['package']}，下架原因：{$msg}",'sj_soft',$v['softid'],__ACTION__ ,"","edit");
						//后台操作数据日志
						update_data_log($v['softid'],'delete',$soft_obj);
						$update_tmp = $soft_tmp -> where("id={$v['id']}") -> save(array('status'=>1,'last_refresh'=>$time,'review_time'=>$time));
						getSoftStatusByPackage($v['package']);
						if(!$update_tmp){
							$msg_str .= "softid为{$v['softid']}的soft_tmp表操作失败\n";
						}
					}else{
						$msg_str .= "softid为{$v['softid']}的soft表操作失败\n";
					}
				}else{
					$msg_str .= "软件softid为{$v['softid']}的不是上架状态\n";
				}
			}else{
				$msg_str .= "软件softid为{$v['softid']}的软件记录已失效";
			}
			//联运游戏下架同步到用户中心
			$isSdkGame = isSdkGame($v['package']);
			if($isSdkGame){
				$appkey = getAppKey($v['package']);
			}
			if($isSdkGame&&$appkey){
				$appkey = getAppKey($v['package']);
				$vals = array('appKey' => $appkey, 'isOnline' => 0);
				$res = json_decode(modifyAppNew($vals), true);
				if(!$res['code']=='success'){
					$msg_str .= "软件softid为{$v['softid']}下架同步到用户中心失败";
				}
			}
		}
		$debut_arr = array();
		$screen_arr = array();
		if($pkg_key){
			//下架后如果上架列表还有软件就还原soft_status表
			$where = array(
				'package'=>array('in',array_keys($pkg_key)),
				'status' => 1,
				'hide' => 1,
				'dev_id' => array('exp','!=0')
			);
			$soft_list = get_table_data($where,"sj_soft","package","package,softid,version,softname",'softid asc');
			if($soft_list){
				foreach($soft_list as $k => $v){
					unset($pkg_key[$v['package']]);
					//修改sj_soft_status表
					//update_soft_status(array('soft_status'=>50,'update_tm'=>$time,'version'=>$v['version'],'softid'=>$v['softid'],'softname'=>$v['softname']),$v['package']);	
					getSoftStatusByPackage($v['package']);
				}
			}
			//下架后，首发<待审核><通过列表><驳回列表>中的软件记录移动到取消列表
			$where = array(
				'package' => array('in',array_keys($pkg_key)),
				'status' => array('in',array(1,2,3)),
			);	
			$debut_list = $model->table('sj_soft_debut')->where($where)->field('dev_id,package')->select();
			$option = array(
				'status'=>4,
				'cancel_time'=>$time,
				'cancel_reason'=>'下架取消',
			);
			if($debut_list){
				$package_d = array();
				foreach($debut_list as $k => $v){
					$debut_arr[$v['dev_id']][] = $softname_arr[$v['package']];
					$package_d[] = $v['package'];
					//修改sj_soft_status表
					update_soft_status(array('debut_status'=>4),$v['package']);	 
				}
				unset($debut_list);
				$where_d = array(
					'package' => array('in',$package_d),
					'status' => array('in',array(1,2,3)),
				);
				$debut_save = $model->table('sj_soft_debut') -> where($where_d)->save($option);
				unset($package_d);
			}
			//下架后，闪屏<待审核><通过列表><驳回列表>中的软件记录移动到取消列表
			$screen_list = $model->table('sj_soft_screen')->where($where)->field('dev_id,package')->select();
			if($screen_list){
				$package_s = array();
				foreach($screen_list as $k => $v){
					$screen_arr[$v['dev_id']][] = $softname_arr[$v['package']];
					$package_s[] = $v['package'];
					//修改sj_soft_status表
					update_soft_status(array('screen_status'=>4),$v['package']);					
				}
				unset($screen_list);
				$where_s = array(
					'package' => array('in',$package_s),
					'status' => array('in',array(1,2,3)),
				);
				$screen_save = $model->table('sj_soft_screen') -> where($where_s)->save($option);
				unset($package_s);	
			}
		}
		//异步操作--发邮件--安智提醒
		$task_client = get_task_client();
		$data['msg'] = $msg;
		if($debut_save){
			$data['debut_arr'] = $debut_arr;
		}
		if($screen_save){
			$data['screen_arr'] = $screen_arr;
		}
		$task_client->doBackground("soft_undercarriage", json_encode($data));			
		if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>'0','msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//新软件驳回
	public function newsoft_down($from){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);
		
		$msg = $_POST['msg'];
		
		if(!$msg) exit(json_encode(array('code'=>'0','msg'=>'请填写驳回原因！')));
		list($return,$package)  = $soft_tmp -> reject_do($id_arr,$msg,$from);
		$msg_str = '';
		$error = '';
		foreach($return as $key => $val){
			if($val == 0){
				$error .= "tmpid为{$key}的软件记录已失效\n";
			}else if($val == 2){
				$msg_str .= "tmpid为{$key}驳回失败\n";
			}else if($val == 3){
				$msg_str .= "tmpid为{$key}驳回内容插入失败\n";
			}else{
				//日志
				$this->writelog("驳回了tmp_id为{$key}包名为{$package[$key]['package']} 原因:{$msg}",'sj_soft_tmp',$key,__ACTION__ ,"","edit");
			}
		}
		$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//修改描述列表  驳回
	public function edit_down(){
		$soft_tmp = D("Dev.Softaudit");
		$tmp = M('soft_tmp');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);
		
		$msg = $_POST['msg'];
		
		if(!$msg) exit(json_encode(array('code'=>'0','msg'=>'请填写驳回原因！')));
		list($return,$package)  = $soft_tmp -> reject_do($id_arr,$msg);
		$msg_str = '';
		$error = '';
		foreach($return as $key => $val){
			if($val == 0){
				$softid = $tmp-> where("id={$key}")-> field('softid')->find();
				$error .= "软件id为{$softid['softid']}的软件记录已失效\n";
			}else if($val == 2){
				$msg_str .= "tmpid为{$key}驳回失败\n";
			}else if($val == 3){
				$msg_str .= "tmpid为{$key}驳回内容插入失败\n";
			}else{
				$this->writelog("驳回了tmp_id为{$key}包名为{$package[$key]['package']} 原因:{$msg}",'sj_soft_tmp',$key,__ACTION__ ,"","edit");
			}
		}
		$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//版本升级驳回
	public function update_down(){
		$soft_tmp = D("Dev.Softaudit");
		$tmp = M('soft_tmp');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);
		
		$msg = $_POST['msg'];
		
		if(!$msg) exit(json_encode(array('code'=>'0','msg'=>'请填写驳回原因！')));
		list($return,$package)  = $soft_tmp -> reject_do($id_arr,$msg);
		$msg_str = '';
		$error = '';
		foreach($return as $key => $val){
			if($val == 0){
				$softid = $tmp-> where("id={$key}")-> field('softid')->find();
				$error .= "软件id为{$softid['softid']}的软件记录已失效\n";
			}else if($val == 2){
				$msg_str .= "tmpid为{$key}驳回失败\n";
			}else if($val == 3){
				$msg_str .= "tmpid为{$key}驳回内容插入失败\n";
			}else{
				$this->writelog("驳回了tmp_id为{$key}包名为{$package[$key]['package']} 原因:{$msg}",'sj_soft_tmp',$key,__ACTION__ ,"","edit");
			}
		}
		$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}	
	//申请下架列表 驳回__操作
	public function nextframe_down(){
		$soft_tmp = D("Dev.Softaudit");
		$tmp = M('soft_tmp');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);
		
		$msg = $_POST['msg'];
		
		if(!$msg) exit(json_encode(array('code'=>'0','msg'=>'请填写驳回原因！')));
		list($return,$package) = $soft_tmp -> reject_do($id_arr,$msg);
		$msg_str = '';
		$error = '';
		foreach($return as $key => $val){
			if($val == 0){
				$softid = $tmp-> where("id={$key}")-> field('softid')->find();
				$error .= "软件id为{$softid['softid']}的软件记录已失效\n";
			}else if($val == 2){
				$msg_str .= "软件id为{$key}驳回失败\n";
			}else if($val == 3){
				$msg_str .= "软件id为{$key}驳回内容插入失败\n";
			}else{
				$this->writelog("驳回了tmp_id为{$key}包名为{$package[$key]['package']} 原因:{$msg}",'sj_soft_tmp',$key,__ACTION__ ,"","edit");
			}
		}
		$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//新软件列表__批量通过并加入专题
	public function pass_subject(){
		$soft_tmp = D("Dev.Softaudit");
		$feature_id = $_GET['feature_id'];
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));
		//操作临时表，通过
		list($error,$msg_str,$add_result) = $soft_tmp -> update_tmp($id_arr);
		if(!empty($feature_id)){
			$model = M('feature_soft');
			$feature_soft = $model->where("feature_id={$feature_id} and status=1")->field('package')->select();
			$count = count($feature_soft);
			$package = '';
			$soft_obj = M('soft');
			foreach($add_result as  $v){
				$count ++;
				$map['package'] = $v['package'];
				$map['feature_id'] = $feature_id;
				$map['rank'] = $count;
				$map['status'] = 1;
				$add_feature = $model -> add($map);
				$package .= $v['package'].",";
				$this->writelog("通过了softid为{$v['softid']}包名为{$v['package']}的软件",'sj_feature_soft',$add_feature,__ACTION__ ,"","add");	
				//后台操作数据日志
				update_data_log($v['softid'],'add',$soft_obj);				
			}
			$package = substr($package,0,-1);
		}
		$this->writelog("通过了软件ID为{$_GET['id']}包名为{$package}的软件并批量加入到专题ID为{$feature_id}",'sj_soft_tmp',$_GET['id'],__ACTION__ ,"","edit");
		//$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}		
	}
	//修改描述批量加入专题
	public function pass_subject_edit(){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		$feature_id = $_GET['feature_id'];
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		list($error,$msg_str,$add_result) = $soft_tmp -> update_soft($id_arr);
		if(!empty($feature_id)){
			$model = M('feature_soft');
			$feature_soft = $model->where("feature_id={$feature_id} and status=1")->field('package')->select();
			$count = count($feature_soft);
			$package = '';
			foreach($add_result as  $v){
				$count ++;
				$map = array();
				$map['package'] = $v['package'];
				$map['feature_id'] = $feature_id;
				$map['rank'] = $count;
				$map['status'] = 1;
				$add_feature = $model -> add($map);
				$package .= $v['package'].",";
				$this->writelog("通过了softid为{$v['softid']}包名为{$v['package']}的软件",'sj_feature_soft',$add_feature,__ACTION__ ,"","add");
				//后台操作数据日志
				$soft_obj = M('soft');
				update_data_log($v['softid'],'update',$soft_obj);
			}
			$package .= substr($package,0,-1);
		}
		$this->writelog("通过了软件ID为{$_GET['id']}包名为{$package}的软件并批量加入到专题ID为{$feature_id}",'sj_soft_tmp',$_GET['id'],__ACTION__ ,"","edit");
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//版本升级__批量通过并加入专题
	public function pass_subject_update(){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		$feature_id = $_GET['feature_id'];
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		//操作临时表，通过
		list($error,$msg_str,$add_result) = $soft_tmp -> update_tmp($id_arr,3);
		if(!empty($feature_id)){
			$model = M('feature_soft');
			$feature_soft = $model->where("feature_id={$feature_id} and status=1")->field('package')->select();
			$count = count($feature_soft);
			$package = '';
			$soft_obj = M('soft');
			foreach($add_result as  $v){
				$count ++;
				$map = array();
				$map['package'] = $v['package'];
				$map['feature_id'] = $feature_id;
				$map['rank'] = $count;
				$map['status'] = 1;
				$add_feature = $model -> add($map);
				$package .= $v['package'].",";
				$this->writelog("通过了softid为{$v['softid']}包名为{$v['package']}的软件",'sj_feature_soft',$add_feature,__ACTION__ ,"","add");
				//后台操作数据日志
				update_data_log($v['softid'],'update',$soft_obj);
			}
			$package .= substr($package,0,-1);
		}
		$this->writelog("通过了软件ID为{$_GET['id']}包名为{$package}的软件并批量加入到专题ID为{$feature_id}",'sj_soft_tmp',$_GET['id'],__ACTION__ ,"","edit");
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//未通过列表__删除
	public function del_status_tmp(){
		$model = M('soft_tmp');
		$time = time();
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$id_str = implode(',',$id_arr);
		$list = $model -> where(array('id'=>array('in',$id_arr))) -> field('id,status,package,record_type') -> select();
		$idarr = array();
		$msg_str = '';
		foreach($list as $k => $v){
	 		if($v['status'] == 3){	
				$del = $model -> where("id={$v['id']}") -> save(array('status'=>0));
				if(!$del){
					$msg_str .= "tmpid为{$v['id']}操作不成功\n";
				} else {
					$map = array('update_tm'=>$time);
					if($v['record_type'] == 1){
						$map['del'] = 1;
						update_soft_status($map,$v['package']);
					} else{
						getSoftStatusByPackage($v['package']);
					}
					$this->writelog("删除了tmp_id为{$v['id']}包名为{$v['package']}的软件",'sj_soft_tmp',$v['id'],__ACTION__ ,"","del");
				}
			}else{
				$msg_str .= "tmpid为{$v['id']}不在未通过状态\n";
			}
			$idarr[] = $v['id'];
		}
		if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$idarr)));
		}
	}
	//审核中软件列表查看
	public function soft_information_view(){
		$model = new Model();
		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		$tmpid = $_GET['id'];
		$softid = $_GET['softid'];
		$record_type = $_GET['record_type'];
		$tmp_list = $model->table('sj_soft_tmp')->where("id={$tmpid}")->find();
		if($record_type == 2 || $record_type == 3 || $record_type == 4){		    
			$icon_file = $model->table('sj_soft_file')->where("apk_name='{$tmp_list['package']}' and package_status=1 and softid='{$tmp_list['softid']}'")->field('iconurl')->find();
			$this -> assign('icon_file',$icon_file['iconurl']);
			$soft_data = $model->table('sj_soft')->where("package='{$tmp_list['package']}' and status=1 and hide=1")->field('softname')->order('softid desc')->find();
			$this -> assign('online_softname',$soft_data['softname']);
			//修改描述图标 
			$t_icon = $model->table('sj_soft_file_tmp')->where("tmp_id={$tmpid} and package_status=1")->find();
			
			if($t_icon){
				$tmp_file = $t_icon;
			}else{
				$tmp_file = $model->table('sj_soft_file')->where("softid={$softid} and package_status=1")->find();
			}
		}else{
			$tmp_file = $model->table('sj_soft_file_tmp')->where("tmp_id={$tmpid} and package_status=1")->find();
		}
		if($record_type == 2 && $this->getchangeapk($tmp_list['dev_id']) ){		     
		    if($tmp_file_data = $model->table('sj_soft_file_tmp')->where("tmp_id={$tmpid} and package_status=1")->find()){
		        $tmp_file = $tmp_file_data;
		    }
		}
		if(!empty($tmp_file) && !empty($tmp_file['filesize']))
		{   
			$tmp_file['filesize'] = sprintf("%1\$.2f",($tmp_file['filesize']/(1024*1024)))."M";
		}
		$tmp_thumb = $model->table('sj_soft_thumb_tmp')->where("tmp_id={$tmpid} and status=1")->select();
		$tmp_thumb_gif = $model->table('sj_soft_thumbgif_tmp')->where("tmp_id={$tmpid} and status=1")->field('id,tmp_id,url,image_raw')->select();

		$tmp_book = $model->table('sj_soft_bookright_tmp')->where("tmp_id={$tmpid} and status=1")->find();
		$newicon = $model->table('sj_icon_tmp')->where("tmpid={$tmpid} and status=1")->find();
		//echo $tmp_icon->getlastsql();
		$cid = substr($tmp_list['category_id'],1,-1);
		$cname = $model->table('sj_category') -> where("category_id='{$cid}'")->field('name,parentid')->find();
		if($cname['parentid'] > 3){
			$fid = $model->table('sj_category') -> where("category_id={$cname['parentid']}")->field('parentid')->find();
		}else{
			$fid = $cname;
		}
		//abi显示
		foreach($known_abis as $abi_key => $abi_value){
			if($abi_value & $tmp_list['abi'] || $tmp_list['abi'] == 0){
				$abis[] = $abi_key."&nbsp;&nbsp;";
			}
		}
		//著作权证书--授权文件
		$note = $model->table('sj_soft_note') -> where("package ='{$tmp_list['package']}'")->find();
		$this -> assign('note',$note);
		if($note && $note['shield'] == 1 && $note['shield_start'] <= $tm && $note['shield_end'] >= $tm ){
			$is_shield = $model->table('sj_soft_copyright') -> where("package='{$tmp_list['package']}'")->limit(1)->select();
			//echo $soft_copyright->getLastSql();
			$authorize_file_url = IMGATT_HOST.$is_shield[0]['authorize_file'];
		  	$authorize_file  = array_pop(explode('/', $is_shield[0]['authorize_file']));
		  	$reg_cred_url = IMGATT_HOST.$is_shield[0]['reg_cred'];
		  	$reg_cred = array_pop(explode('/', $is_shield[0]['reg_cred']));
		  	$reg_num = $is_shield[0]['reg_num'];
		  	$reg_people = $is_shield[0]['reg_people'];
		  	$reg_softname = $is_shield[0]['softname'];
			$this -> assign('authorize_file_url',$authorize_file_url);
			$this -> assign('authorize_file',$authorize_file);
			$this -> assign('reg_cred_url',$reg_cred_url);
			$this -> assign('reg_cred',$reg_cred);
			$this -> assign('reg_people',$reg_people);
			$this -> assign('reg_num',$reg_num);
			$this -> assign('reg_softname',$reg_softname);
		}
		//获取电子书分类
		$softaudit_model = D("Dev.Softaudit");
		$book_categoryid = $softaudit_model -> get_book_categoryid();	
		$this->assign('book_categoryid',$book_categoryid);		
		if(in_array($cid,$book_categoryid)){
			$tmp_list = $this->get_remind_words($tmp_list);
		}
		//若为公司类型开发者，在审核预览页系统读出开发者营业执照信息
		$developer = $model -> table('pu_developer')->where("dev_id = {$tmp_list['dev_id']}")->field('dev_id,type,charter,charterpic,company')->find();
		if($developer['type'] == 0){
			$charterpic = $developer['charterpic'];
			$charter = $developer['charter'];
			$company = $developer['company'];
			$this -> assign('charter',$charter);
			$this -> assign('company',$company);
			$this -> assign('charterpic',$charterpic);
		}
		//首发--闪屏
		$soft_debut = $model->table('sj_soft_debut') -> where("package = '{$tmp_list['package']}' and status=2 and debut_time+(debut_length*3600) >= '{$tm}' and del_status =1 and is_apk=1")->field('package')->find();		
		$soft_screen = $model->table('sj_soft_screen') -> where("package = '{$tmp_list['package']}' and status=2 and del_status =1")->field('package')->find();		
		if($soft_debut) $this -> assign('soft_debut',$soft_debut['package']);
		if($soft_screen) $this -> assign('soft_screen',$soft_screen['package']);
		$soft_tags = explode(',',$tmp_list['tags']);
		$this -> assign('soft_tags',$soft_tags);
		$this -> assign('tmp_list',$tmp_list);
		$this -> assign('abis',$abis);
		$this -> assign('tmp_file',$tmp_file);
		$this -> assign('tmp_thumb',$tmp_thumb);
		$this -> assign('tmp_thumb_gif',$tmp_thumb_gif);

		$this -> assign('tmp_book',$tmp_book);
		$this -> assign('newicon',$newicon);
		$this -> assign('cname',$cname);
		$this -> assign('fid',$fid);
		//获取标签
		$Tagsmodel = D('Sj.Tags');	
		$tag_list = $Tagsmodel -> get_tag($tmp_list['package']);	
		$is_new = $Tagsmodel -> getTagidbyname($tag_list[1]);
		$this->assign('is_new',$is_new);		
		$this->assign('tag_list',$tag_list);
		//备案号
		$beian = $model->table('yx_product')->where(array('package' => $tmp_list['package'], 'del' => 0))->field('soft_id,record_num,record_url,publication_num,publication_url')->find();
		$this->assign('beian',$beian);
		$this->get_video($tmpid);
		$this->assign('IMGATT_HOST',IMGATT_HOST);		
		$this -> display();

	}

	//视频
	public function get_video($tmpid){
		//软件视频
		$model = M('');
		$video = $model->table('sj_soft_extra_tmp')->where(array('tmpid'=>$tmpid,'status'=>1))->field('id,package,tmpid,video_url,video_title,video_pic,video_num')->select();
		if($video){
			$thumb_video = $top_video = array();
			foreach($video as $k=>$v){
				if($v['video_num']==1){
					$thumb_video['video_url'] = $v['video_url'];
					$thumb_video['video_title'] = $v['video_title'];
					$thumb_video['video_pic'] = $v['video_pic'];
				}
				if($v['video_num']==2){
					$top_video['video_url'] = $v['video_url'];
					$top_video['video_title'] = $v['video_title'];
					$top_video['video_pic'] = $v['video_pic'];
				}
			}
			$this->assign('thumb_video',$thumb_video);
			$this->assign('top_video',$top_video);
		}
	}
	//新软件撤销
	public function newsoft_cancel($from){
		$model = M('soft_tmp');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$id_str = implode(',',$id_arr);
		$list = $model -> where(array('id'=>array('in',$id_arr))) -> field('id,status,package,record_type,sdk_status,single_sdk') -> select();
		$idarr = array();
		$msg_str = '';
		$error = '';
		foreach($list as $k => $v){
	 		if($v['status'] == 3){	
				$data = array();
				if($from == 'sdk') $data['sdk_send'] =0;
				$data['status'] = 2; 
				if($v['single_sdk']==3){
					$data['single_sdk'] = 2;
				}
				$del = $model -> where("id={$v['id']}") -> save($data);
				if(!$del){
					$msg_str .= "软件ID为{$v['id']}操作不成功\n";
				} else {
					if($v['record_type'] == 1){
						$s_status = 31;
					}else if($v['record_type'] == 2){
						$s_status = 32;
					}else if($v['record_type'] == 3){
						$s_status = 33;
					}else if($v['record_type'] == 4){
						$s_status = 34;
					}
					if($v['sdk_status']==1){
						$update_data = array('soft_status'=>$s_status,'sdk_status'=>4,'update_tm'=>time());
					}else{
						$update_data = array('soft_status'=>$s_status,'update_tm'=>time());
					}
					//修改sj_soft_status表
					update_soft_status($update_data,$v['package']);	
					$this->writelog("撤销了tmp_id为{$v['id']}包名为{$v['package']}的软件",'sj_soft_tmp',$v['id'],__ACTION__ ,"","edit");
				}
			}else{
				$error .= "软件id为{$v['id']}的软件记录已失效\n";
			}
			$idarr[] = $v['id'];
		}
		$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$idarr)));
		}
	}
	//设置房间数
	public  function  update_room(){	    
	    $model = M();
	    $room_total = intval($_GET['room_total']);
	    $room_type	= $_GET['room_type'];
	    if($room_total>0 && $room_total<=10){
	        $res = $model ->table('pu_config')-> where("config_type='{$room_type}' and configname='config_room_check'") -> save(array('configcontent'=>$room_total));
			$this -> writelog("设置房间数为{$room_total}成功", 'pu_config',"config_type:{$room_type}",__ACTION__ ,'','edit');
			exit(json_encode(array('code'=>'1','msg'=>"设置成功")));
	        echo  json_encode(array('code'=>'1','msg'=>""));
	    }else{
	        echo  json_encode(array('code'=>'2','msg'=>"设置失败，请重试"));
	    }	
	    exit;
	}
	//得到房间数
	public  function getroomtotal($type){
	    $model = M();
	    return $model ->table('pu_config') -> where("config_type = '{$type}' and configname='config_room_check'") -> getField('configcontent');
	}
	public function getchangeapk($dev_id){
	    $model = new Model();
	    $id = $model->table('sj_userapk')->where("dev_id={$dev_id} and status=1")->getField('id');
	    if ($id) {
	        return true;
	    } else {
	        return false;
	    }
	}
	public function get_remind_words($data){		    
	    $model = M();
	    if($data['softname']){
	        $data['softname']=checkword($data['softname'],$model);
	        $data['softname_prev']=checkword($data['softname_prev'],$model);
	        $data['update_content']=checkword($data['update_content'],$model);
	        $data['intro']=checkword($data['intro'],$model);
	        $tags = checkword_intro($data['tags'],$model);
	        $data['tags'] != $tags ?  $data['tags_r'] = $tags:''; 
	    }else{
	        $num = count($data);
	        for($i=0;$i<$num ;$i++){
	            $data[$i]['softname']=checkword($data[$i]['softname'],$model);
	            $data[$i]['softname_prev']=checkword($data[$i]['softname_prev'],$model);
	            $data[$i]['update_content']=checkword($data[$i]['update_content'],$model);
	            $data[$i]['intro']=checkword($data[$i]['intro'],$model);
	        } 
	    }
	    return $data;
	}
	//新软件列表_屏蔽
	function newsoft_shield(){
		$shield_start_time = date("Y-m-d H:i:s",time());
		$shield_end_time = "2023-01-01 00:00:00";
		$this->assign("shield_start_time",$shield_start_time);
		$this->assign("type",'tmp');	
		$this->assign("act",'newsoft_shield');	
		//排期中
		$softid = explode(',',$_GET['id']);
		
		$softmodel = D('Dev.Softlist');
		$msg = $softmodel ->get_Schedule($softid,"tmp");
		/*
		 * 12月临时需求（定时上架软件上架时间作为屏蔽结束时间）
		 * 选择一个软件时显示其定时上架时间，多个时显示2023-01-01 00:00:00，提交时再分别做处理
		 */
		if(count($softid)==1){
			$pass_time = $softmodel->get_pass_time($softid);
			if(!empty($pass_time[$softid[0]]['pass_time']))
			$shield_end_time = date("Y-m-d H:i:s",$pass_time[$softid[0]]['pass_time']);
		}
		$this->assign("shield_end_time",$shield_end_time);
		$this->assign("msg",$msg);			
		$this -> display('Dev:Soft:shield_soft');		
	}
	//修改审核列表_屏蔽
	function edit_audit_shield(){
		$shield_start_time = date("Y-m-d H:i:s",time());
		$shield_end_time = "2023-01-01 00:00:00";
		$this->assign("shield_start_time",$shield_start_time);
		$this->assign("shield_end_time",$shield_end_time);	
		$this->assign("type",'tmp');
		$this->assign("act",'edit_audit_shield');	
		//排期中
		$softid = explode(',',$_GET['id']);
		$softmodel = D('Dev.Softlist');
		$msg = $softmodel ->get_Schedule($softid,"tmp");
		$this->assign("msg",$msg);			
		$this -> display('Dev:Soft:shield_soft');		
	}
	//升级列表_屏蔽
	function softupgrade_shield(){
		$shield_start_time = date("Y-m-d H:i:s",time());
		$shield_end_time = "2023-01-01 00:00:00";
		$this->assign("shield_start_time",$shield_start_time);
		$this->assign("shield_end_time",$shield_end_time);	
		$this->assign("type",'tmp');	
		$this->assign("act",'softupgrade_shield');	
		//排期中
		$softid = explode(',',$_GET['id']);
		$softmodel = D('Dev.Softlist');
		$msg = $softmodel ->get_Schedule($softid,"tmp");
		$this->assign("msg",$msg);			
		$this -> display('Dev:Soft:shield_soft');		
	}
	//未通过列表_屏蔽
	function not_through_shield(){
		$shield_start_time = date("Y-m-d H:i:s",time());
		$shield_end_time = "2023-01-01 00:00:00";
		$this->assign("shield_start_time",$shield_start_time);
		$this->assign("shield_end_time",$shield_end_time);	
		$this->assign("type",'tmp');	
		$this->assign("act",'not_through_shield');	
		//排期中
		$softid = explode(',',$_GET['id']);
		$softmodel = D('Dev.Softlist');
		$msg = $softmodel ->get_Schedule($softid,"tmp");
		$this->assign("msg",$msg);			
		$this -> display('Dev:Soft:shield_soft');		
	}
	//定时上架列表__撤销
	function shelves_dropped(){
		$model = M('soft_tmp');
		$id_arr = explode(',',$_GET['id']);
		$where = array();
		$where['status'] = 2;
		$where['pass_status'] = 1;
		$where['id'] = array('in',$id_arr);
		$list = $model->where($where)->field('id,softid,package,record_type')->select();
		$data = array();
		foreach($list as $k => $v){
			$data[$v['id']] =  $v;
		}
		$ret = $model->where($where)->save(array('pass_status'=>0,'last_refresh'=>time()));
		if($ret){
			//操作日志
			foreach($id_arr as $v){
				//修改sj_soft_status表
				if($data[$v]['record_type'] == 1){
					$s_status = 31;
				}else if($data[$v]['record_type'] == 2){
					$s_status = 32;
				}else if($data[$v]['record_type'] == 3){
					$s_status = 33;
				}else if($data[$v]['record_type'] == 4){
					$s_status = 34;
				}
				update_soft_status(array('soft_status'=>$s_status,'update_tm'=>time()),$data[$v]['package']);	
				$this->writelog("撤销了定时上架软件softid为{$data[$v]['softid']}包名为{$data[$v]['package']}",'sj_soft_tmp',$data[$v]['softid'],__ACTION__ ,"","edit");
			}
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}else{
			exit(json_encode(array('code'=>'0','msg'=>'撤销失败')));
		}
	}
	//X86 权限
	function x86_check(){
		//验证X86权限
		exit(json_encode(array('status'=>1,'info'=>"有权限")));
	}
	//sdk测试列表
	function sdk_test_list(){
		$soft_tmp = D("Dev.Softaudit");
		$sdk_version = $soft_tmp->table('pu_config')->where(array('config_type'=>'sdk_version','status'=>1))->field('configcontent')->find();
		$this->assign('sdk_version',$sdk_version['configcontent']);
		list($adlist,$adlist2,$adlist3) = $soft_tmp->asort_ad_select();	
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);

		if(isset($_GET['form_type'])){
			$this->assign('form_type',$_GET['form_type']);
		}else{
			$_GET['form_type'] = 'sdk_test';
			$this->assign('form_type','sdk_test');
		}
		if(empty($_GET['status'])){
			$status = 2;
		}else{	
			$status = $_GET['status'];
		}
		$this->assign('status',$status);

		if(empty($_GET['sdk_status'])){
			$_GET['sdk_status'] = 2;
		}
		$this->assign('sdk_status',$_GET['sdk_status']);
		if(empty($_GET['safe'])){
			$_GET['safe'] = 0;
		}
		$safe = $_GET['safe'];
		$this->assign('safe',$safe);
		if($_GET){
			if(isset($_GET['update_from'])){
				$this->assign('update_from',$_GET['update_from']);
			}
			if(isset($_GET['sdk'])){
				$this->assign('sdk',$_GET['sdk']);
			}
			if(isset($_GET['dev_id'])){
				$this->assign('dev_id',$_GET['dev_id']);
			}				
			if(isset($_GET['softname'])){
				$this->assign('softname',$_GET['softname']);
			}	
			if(isset($_GET['package'])){
				$this->assign('package',$_GET['package']);
			}
			if(isset($_GET['ip'])){
				$this->assign('ip',$_GET['ip']);
			}
			if(isset($_GET['dev_name'])){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
			}
			//开发者类型	
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);			
			}
			//定时上架时间
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$this->assign('begintime',$_GET['begintime']);
				$endtime = strtotime($_GET['endtime']);
			    if($endtime<$begintime)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime',$_GET['endtime']);
			}
			//提交时间
			if(!empty($_GET['begintime_up']) && !empty($_GET['endtime_up'])){
				$begintime_up = strtotime($_GET['begintime_up']);
				$this->assign('begintime_up',$_GET['begintime_up']);
				$endtime_up = strtotime($_GET['endtime_up']);
			    if($endtime_up<$begintime_up)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime_up',$_GET['endtime_up']);
			}
			//驳回时间
			if(!empty($_GET['begintime_a']) && !empty($_GET['endtime_a'])){
				$begintime_a = strtotime($_GET['begintime_a']);
				$this->assign('begintime_a',$_GET['begintime_a']);
				$endtime_a = strtotime($_GET['endtime_a']);
				if($endtime_a<$begintime_a)
				{   
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("时间无效,请选择正常时间");
				}
				$this->assign('endtime_a',$_GET['endtime_a']);
			}			
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//不安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}		
			//账号接入
			if(isset($_GET['accept_account_status'])){
				$this->assign("accept_account_status", $_GET['accept_account_status']);
			}	
			//测试类型
			if(isset($_GET['test_type'])){
				$this->assign("test_type", $_GET['test_type']);
			}	
		}
		$room_total = $this->getroomtotal('config_room_3');
		if($room_total)   $this->assign("room_total", $room_total);
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total, $page,$reason_list) = $soft_tmp->getsoftaudit($status,$record_type,$_GET, $limit,$room_total);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark ->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);	

		$result = $this->get_remind_words($result);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);

		$this -> assign('reason_list',$reason_list);
		//url路径--下载量排序--提交时间排序
		$param = $_GET;
		if($param['orderby'] == 'upload_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order1',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'shelves_tm'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order',$param['order']);	
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		if($param['orderby'] == 'time'){
			if($param['order'] == 'd'){
				$param['order'] = 'a';
				$this -> assign('order1',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}		
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);

		$param = http_build_query($param);

		$this -> assign('param',$param);
		$this -> assign('sdk_on_off',C('SDK_on_off'));
		$this ->display();
	}
	//sdk通过操作
	public function sdk_pass(){
		$soft_tmp = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$id_str = implode(',',$id_arr);
		//操作临时表，通过
		list($error,$msg_str,$add_result) = $soft_tmp -> update_tmp($id_arr,'sdk');
		foreach($add_result as $k=>$v){
			//操作日志
			if($v['sdk_status'] == 1 && !empty($v['package'])){
				$this->writelog("通过了包名【{$v['package']}】SDK测试软件",'sj_soft_tmp',$k,__ACTION__ ,"","edit");
			}
		}
		//$msg_str = nl2br($msg_str);
		if(!empty($error) && $error != ''){
			exit(json_encode(array('code'=>'2','error'=>$error,'msg'=>$id_arr)));
		}else if(!empty($msg_str) && $msg_str != ''){
			exit(json_encode(array('code'=>0,'msg'=>$msg_str)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//sdk测试列表驳回操作
	public function sdk_reject(){
		$this -> newsoft_down('sdk');
	}
	//adk未通过列表撤销
	public function sdk_cancel(){
		$this -> newsoft_cancel('sdk');
	}
	//adk未通过列表删除
	public function del_sdk(){
		$this -> del_status_tmp();
	}
	//被驳回软件的联系方式_导出
	function soft_reject_contact(){
		if($_GET){
			$Export = D("Dev.Export");
			$begintime = strtotime($_GET['begintime']);
			$endtime = strtotime($_GET['endtime']);
			if($endtime<$begintime){   
				//$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error("时间无效,请选择正常时间");
			}
			$Export->get_reject_contact_export($begintime,$endtime);
		}else{
			$this -> assign('begintime',date("Y-m-d 00:00:00",time()-14*86400));
			$this -> assign('endtime',date("Y-m-d 23:59:59",time()));
			$this -> display();
		}
	}
	//sdk类型
	function pub_sdk_type(){
		$model = new Model();
		$info = array();
		if(!$_POST['data']) exit(json_encode($info));
		$where = array(
			'id' => array('in',$_POST['data'])
		);
		$list = $model -> table('sj_soft_tmp')->where($where)->field('id,package')->select();
		$pkg_arr = array();
		//echo $model->Getlastsql();
		foreach($list as $k => $v){
			$p_fenlei = $model->table('yx_product')->where(array('package'=>$v['package'],'del'=>0))->field('p_fenlei')->find();
			
			if(!$p_fenlei){
				$fenlei = $model->table('sj_soft_whitelist')->where(array('package'=>$v['package']))->field('fen_lei')->find();
				$p_fenlei['p_fenlei'] = $fenlei['fen_lei'];
			}
//			$str = '';
//			if($v['type']== '单机'){
//				$str = "支付功能";
//			}else{
//				$str = "用户中心<br/>支付功能";
//			}
			$info[$v['id']] =  $p_fenlei['p_fenlei'];
		}
		exit(json_encode($info));
	}
	//运营白名单总数
	function pub_white_count(){
		$model = new Model();
		$record_type = $_POST['record_type'];
		$where = array(
			'A.record_type'=>$record_type,
			'A.status'=>2,
			'A.sdk_status' => array('exp',"<=1"),
			'A.safe' => array('exp',"<=1"),
			'B.status'=>1
		);
		if($record_type == 3){
			$where['A.pass_status'] = array('exp',"!=1");
		}
		$list = $model->table('sj_soft_tmp A')->join("sj_soft_whitelist B ON B.package = A.package")->where($where)->field('B.package')->select();
		$package = array();
		$i = 0;
		foreach($list as $v){
			$package[] = $v['package']; 
			$i++;
		}
		unset($list);
		if($i > 0)
		exit(json_encode(array('code'=>1,'total'=>$i,'package'=>$package)));
	}
        
        //保存sdk版本
        public function save_sdk_version(){
            if($_POST['sdk_version']!=''){
                $model = M('');
                $res = $model->table('pu_config')->where(array('status'=>1,'config_type'=>'sdk_version'))->save(array('configcontent'=>$_POST['sdk_version']));
                if($res){
                    $result['error'] = 0;
                }else{
                    $result['error'] = 1;
                }
            }else{
                $result['error'] = 1;
            }
            echo json_encode($result);
        }
        //拼接软件包的下载地址
}
?>