<?php
/*
*软件管理__上架列表
*
*
*
*
*
*
*
****************
******************
**************/
define('ABI_ARMEABI', 1);
define('ABI_ARMEABI_V7A', 2);
define('ABI_X86', 4);
define('ABI_MIPS', 8);
class SoftAction extends CommonAction {
	
	private $admin_down = array(390);
	
	//上架列表
	public function softlist(){	
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);
		$hide = 1;
		if($_GET){
			if(!isset($_GET['softid']) && !isset($_GET['dev_id']) && !isset($_GET['is_official']) && !isset($_GET['is_bj_shield']) && !isset($_GET['softname']) && !isset($_GET['package']) && !isset($_GET['dev_name']) && !isset($_GET['ip']) && !isset($_GET['email']) && !isset($_GET['dev_type']) && !isset($_GET['type'])&& !isset($_GET['begintime']) && !isset($_GET['endtime']) && !isset($_GET['cateid']) && !isset($_GET['ad_id']) && !isset($_GET['uplode']) && !isset($_GET['uplode1']) && !isset($_GET['Official']) && !isset($_GET['shield_status']) && !isset($_GET['intro']) && !isset($_GET['TV']) && !isset($_GET['soft_source']) && !isset($_GET['Operators']) && !isset($_GET['claim']) && empty($_GET['safe'])&& !isset($_GET['x86'])&& !isset($_GET['game_charge'])&& !isset($_GET['comment']) && !isset($_GET['azjx_type']) && !isset($_GET['download_type'])){
				$this->error('请至少填写一个条件');
			}
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
			if(isset($_GET['dev_name']) ){
				$this->assign('dev_name',$_GET['dev_name']);
			}
			if(isset($_GET['ip']) ){
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
			//屏蔽北京市
			if(isset($_GET['is_bj_shield'])){
				$this->assign('is_bj_shield',$_GET['is_bj_shield']);
			}
			//接入SDK类型
			if(isset($_GET['is_official'])){
				$this->assign('is_official',$_GET['is_official']);
			}

			//起止日期
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
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//广告类型
			if (!empty($_GET['ad_id'])) {
				$ad_id_arr = explode(',',$_GET['ad_id']);
				$ad_id = array_flip($ad_id_arr);
				$this -> assign('ad_id',$ad_id);			
				$this -> assign('ad_id_str',$_GET['ad_id']);
				$this->assign('ad_str',$_GET['ad_type']);	
			}
			//下载量搜索
			if(isset($_GET['uplode']) && isset($_GET['uplode1'])){
				$this->assign('uplode', $_GET['uplode']);
				$this->assign('uplode1', $_GET['uplode1']);	
			}	

			//搜索官方认证
			if(!empty($_GET['Official'])){
				$this->assign("Official", $_GET['Official']);
			}
			//搜索游戏内付费
			if(!empty($_GET['game_charge'])){
				$this->assign("game_charge", $_GET['game_charge']);
			}			
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}		
			//软件简介
			if(!empty($_GET['intro'])){
				$this->assign("intro", $_GET['intro']);
			}
			//搜索TV认证
			if(isset($_GET['TV'])){
				$this->assign("TV", $_GET['TV']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			if(!empty($_GET['Operators'])){
				$this->assign("Operators", $_GET['Operators']);
			}
			if(isset($_GET['claim'])){
				$this->assign("claim", $_GET['claim']);
			}
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}
			//x86是否支持
			if(isset($_GET['x86'])){
				$this->assign("x86", $_GET['x86']);
            }

			//是否有小编点评
			if(isset($_GET['comment'])){
				$this->assign("comment", $_GET['comment']);
			}
			//是否有安智精选
			if(isset($_GET['azjx_type'])){
				$this->assign("azjx_type", $_GET['azjx_type']);
			}
			//下载方式
			if(isset($_GET['download_type'])){
				$this->assign("download_type", $_GET['download_type']);
			}
			//分页		
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
			$soft_list = D('Dev.Softlist');
			//var_dump($_GET);
			list($result,$total, $page,$reason_list,$sj_corner_mark) = $soft_list->getsoftlist($hide,$_GET, $limit);
			
			//官方为认证默认时间
			$start_at = date("Y-m-d H:i:s",time());
			$end_at = "2023-01-01 00:00:00";
			$this->assign("start_at",$start_at);
			$this->assign("end_at",$end_at);
			$this -> assign('page', $page->show());		
			$this -> assign('total',$total);
			$this -> assign('list',$result);
			$this -> assign('reason_list',$reason_list);
			//加入合作渠道
			$tvCategoryModel = M('prove_category');
			$res = $tvCategoryModel->where(array('status'=>1))->field('id,category_name,prove_type')->select();

			$provider = array(
				'1' => 'LG'
			);

			$category = array();
			foreach ($res as $key => $value) {
				# code...
				$prove_type = $value['prove_type'];
				!isset($category[$prove_type]) && $category[$prove_type] = array();
				$category[$prove_type][] = $value;
			}
			$this->assign('category', $category);
			$this->assign('provider', $provider);
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
			if($param['orderby'] == 'time'){
				
				if($param['order'] == 'd'){
					$param['order'] = 'a'; 
				}elseif($param['order'] == 'a'){
					$param['order'] = 'd'; 
					$this -> assign('order1',$param['order']);
				}elseif($param['order'] == ''){
					$param['order'] = 'd'; 
				}
			}
			$this -> assign('orderby',$param['orderby']);	
			unset($param['orderby']);
			$param = http_build_query($param);
			$this -> assign('param',$param);
		}	
		//角标搜索
		$sj_corner_mark = $soft_tmp -> table('sj_corner_mark')->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);
		
		//查找首发ID 红包ID和特权ID 根据角标的不同点击角标页面显示内容不同
		$where_sf=array(
			'status' =>1,
			'name' =>'首发',
		);
		$sj_sf_id=$soft_tmp -> table('sj_corner_mark')->where($where_sf)->field('id')->find();
		$this->assign('sj_sf_id',$sj_sf_id['id']);
		$where_hb=array(
			'status' =>1,
			'name' =>'红包',
		);
		$sj_hb_id=$soft_tmp -> table('sj_corner_mark')->where($where_hb)->field('id')->find();
		$this->assign('sj_hb_id',$sj_hb_id['id']);
		$where_tq=array(
			'status' =>1,
			'name' =>'特权',
		);
		$sj_tq_id=$soft_tmp -> table('sj_corner_mark')->where($where_tq)->field('id')->find();
		$this->assign('sj_tq_id',$sj_tq_id['id']);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		if(in_array($_SESSION['admin']['admin_id'],$this->admin_down)){
			$this -> assign('div_no_show',1);
		}
		$this ->display('softlist');		
	}
	//历史软件列表
	public function  history_soft_list(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$hide = 0;
		if($_GET){

			if(isset($_GET['package'])){
				$this->assign('package',$_GET['package']);
			}
		}
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$soft_list = D('Dev.Softlist');
		list($result,$total,$page,$reason_list,$sj_corner_mark) = $soft_list->getsoftlist($hide,$_GET, $limit);
		//角标展示
		$this -> assign('sj_corner_mark',$sj_corner_mark);
		//软件类别
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		$this -> assign('page', $page->show());
		$this -> assign('list',$result);
		$this -> assign('total',$total);
		//url路径__下载量排序和更新时间排序
	 	$param = $_GET;
		if($param['orderby'] == 'download'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
				$this -> assign('order',$param['order']);
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
			}
		}	
		if($param['orderby'] == 'time'){		
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
				$this -> assign('order1',$param['order']);
			}elseif($param['order'] == ''){
				$param['order'] = 'a'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);
		$this ->display();
	}
	//删除历史软件
	public function  soft_status_hide(){
		$model = M('soft');
		if($_POST['softids']){
			    $ids =$_POST['softids'];
				$ids_one=trim($ids,',');
				$ids=explode ( ",",$ids_one );
				$list = $model->where("status=1 and softid={$ids[0]}")->find();
				$data['status'] = 0;
				$where=array();
				$where['status']=1;
				$where['softid']=array('in',$ids);
				$del = $model->where($where)->save($data);
				if($del){
					 $this->writelog("删除了softid为{$ids_one}包名为{$list['package']}的历史软件",'sj_soft',$ids_one,__ACTION__ ,"","del");
					 $this->success("删除成功");
				}
		}else{
			$id =$_GET['softid'];
			if($id){
				$list = $model->where("status=1 and softid={$id}")->find();
				$data['status'] = 0;
				$del = $model->where("status=1 and softid={$id}")->save($data);
				if($del){
					 $this->writelog("删除了softid为{$id}包名为{$list['package']}的历史软件",'sj_soft',$id,__ACTION__ ,"","del");
					 $this->success("删除成功");
				}
			}
		}
	}
	//下架列表
	public function undercarriage_list(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);
		$hide = 3;
		if($_GET){
			if(!isset($_GET['softid']) && !isset($_GET['dev_id']) && !isset($_GET['softname']) && !isset($_GET['package']) && !isset($_GET['dev_name']) && !isset($_GET['ip']) && !isset($_GET['email']) && !isset($_GET['dev_type']) && !isset($_GET['type'])&& !isset($_GET['begintime']) && !isset($_GET['endtime']) && !isset($_GET['cateid']) && !isset($_GET['ad_id']) && !isset($_GET['uplode']) && !isset($_GET['uplode1']) && !isset($_GET['Official']) && !isset($_GET['shield_status']) && !isset($_GET['intro']) && !isset($_GET['TV']) && !isset($_GET['soft_source']) && !isset($_GET['Operators']) && !isset($_GET['claim']) && empty($_GET['safe'])){
				$this->error('请至少填写一个条件');
			}		
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
				$endtime = strtotime($_GET['endtime']);
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
			//下载量搜索
			if(isset($_GET['uplode']) && isset($_GET['uplode1'])){
				$this->assign('uplode', $_GET['uplode']);
				$this->assign('uplode1', $_GET['uplode1']);	
			}
			//搜索官方认证
			if(!empty($_GET['Official'])){
				$this->assign("Official", $_GET['Official']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}
			//安全状态
			if(isset($_GET['safe'])){
				$this->assign("safe", $_GET['safe']);
			}

			//分页		
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
			$soft_list = D('Dev.Softlist');
			list($result,$total,$page,$reason_list,$sj_corner_mark) = $soft_list->getsoftlist($hide,$_GET, $limit);
			$this -> assign('page', $page->show());
			$this -> assign('list',$result);
			$this -> assign('total',$total);
			//url路径__下载量排序和更新时间排序
			$param = $_GET;
			if($param['orderby'] == 'download'){
				if($param['order'] == 'd'){
					$param['order'] = 'a'; 
					$this -> assign('order',$param['order']);
				}elseif($param['order'] == 'a'){
					$param['order'] = 'd'; 
				}
			}	
			if($param['orderby'] == 'time'){		
				if($param['order'] == 'd'){
					$param['order'] = 'a'; 
				}elseif($param['order'] == 'a'){
					$param['order'] = 'd'; 
					$this -> assign('order1',$param['order']);
				}elseif($param['order'] == ''){
					$param['order'] = 'a'; 
				}
			}
			$this -> assign('orderby',$param['orderby']);
			unset($param['orderby']);
			$param = http_build_query($param);
			$this -> assign('param',$param);
		}
		//角标搜索
		$sj_corner_mark = $soft_tmp -> table('sj_corner_mark')->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);		
		//软件类别
		$cname = $soft_tmp ->return_category();
		if(in_array($_SESSION['admin']['admin_id'],$this->admin_down)){
			$this -> assign('div_no_show',1);
		}
		$this -> assign('cname',$cname);
		$this ->display();
	}
	//不安全列表
	public function insecurity_list(){
		$soft_tmp = D("Dev.Softaudit");
		list($adlist,$adlist2,$adlist3,$adlist_str) = $soft_tmp->asort_ad_select();
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		$this -> assign('alladlist',$adlist_str);
		$safe = 3;
		if($_GET){
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
				$endtime = strtotime($_GET['endtime']);
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
			//更新时间排序
			if(isset($_GET['last_refresh_order'])){
				if($_GET['last_refresh_order']==1){
					$this->assign('last_refresh_order', 2);
				}else if($_GET['last_refresh_order']==2){
					$this->assign('last_refresh_order', 1);
				}
			}			
			//下载量搜索
			if(isset($_GET['uplode']) && isset($_GET['uplode1'])){
				$this->assign('uplode', $_GET['uplode']);
				$this->assign('uplode1', $_GET['uplode1']);	
			}
			//下载量排序
			if(isset($_GET['download_order'])){
				if($_GET['download_order']==1){
					$this->assign('download_order', 2);
				}else if($_GET['download_order']==2){
					$this->assign('download_order', 1);
				}
			}
			//搜索官方认证
			if(!empty($_GET['Official'])){
				$this->assign("Official", $_GET['Official']);
			}
			//搜索屏蔽状态
			if(!empty($_GET['shield_status'])){
				$this->assign("shield_status", $_GET['shield_status']);
			}			
			//软件来源：
			if(isset($_GET['soft_source'])){
				$this->assign("soft_source", $_GET['soft_source']);
			}

			//分页		
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
			$soft_list = D('Dev.Softlist');
			list($result,$total, $page,$sj_corner_mark) = $soft_list->getsoft_unsafe($_GET, $limit,$safe);
			$this -> assign('page', $page->show());
			$this -> assign('list',$result);
			$this -> assign('total',$total);
			//url路径__下载量排序和更新时间排序
			$param = $_GET;
			if($param['orderby'] == 'download'){
				if($param['order'] == 'd'){
					$param['order'] = 'a'; 
				}elseif($param['order'] == 'a'){
					$param['order'] = 'd'; 
				}
				$this -> assign('order',$param['order']);
			}
			$param = http_build_query($param);
			$this -> assign('param',$param);	
		}	
		//角标搜索
		$sj_corner_mark = $soft_tmp -> table('sj_corner_mark')->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark', $sj_corner_mark);		
		//软件类别
		$cname = $soft_tmp ->return_category();
		if(in_array($_SESSION['admin']['admin_id'],$this->admin_down)){
			$this -> assign('div_no_show',1);
		}
		$this -> assign('cname',$cname);			
		$this -> assign('orderby',$param['orderby']);	
		$this ->display();
	}
	//全局搜索
	public function global_search(){

		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		if($_GET){
			$where = "";
			//想不起为什么加and claim_status = 2---先去掉
			$status = "hide != 0 and channel_id = '' ";
			$status2 = "status >= 2 and record_type in(1,2,3,5,4,8)";
			$soft = M('soft');
			if(empty($_GET['softid']) && empty($_GET['softname']) && empty($_GET['package']) && empty($_GET['dev_name']) && empty($_GET['email'])&& empty($_GET['dev_id']) && empty($_GET['softid_str'])&& empty($_GET['tmpid_str'])&& empty($_GET['ip'])){
				$this -> error('必须填写一个条件');
			}
			if($_GET['del_soft'] == '1' ){
				$status .= 'and status = 1';
				$this -> assign('del_soft',1);
			}
			if(isset($_GET['ip'])){
				$softid2 .= " and ip='{$_GET['ip']}' and record_type <= 3 and status =2" ;
				$this -> assign('ip',$_GET['ip']);
			}
			if(isset($_GET['dev_id'])){
				$dev_id = trim($_GET['dev_id']);
				$softid1 .= " and dev_id={$dev_id}" ;
				$softid2 .= " and dev_id={$dev_id}" ;
				$this -> assign('dev_id',$dev_id);
			}
			if(isset($_GET['softid_str']) || isset($_GET['tmpid_str'])){
				$softid_str = $_GET['softid_str'] ? $_GET['softid_str'] : 0;
				$tmpid_str = $_GET['tmpid_str'] ? $_GET['tmpid_str'] : 0;
				$softid1 .= " and softid in({$softid_str})";
				$softid2 .= " and id in ({$tmpid_str})";
			}
			if(isset($_GET['softid'])){
				$softid = trim($_GET['softid']);
				$softid1 .= " and softid={$softid}" ;
				//$softid2 .= " and (softid={$softid} or update_from={$softid})" ;
				$softid2 .= " and (softid={$softid})" ;
				$softid3 = " union select 'tmp' as go,softid,id as tmpid,softname,package,abi,language,dev_id,version,version_code,category_id,status,record_type as hide,safe,update_from,dev_name,pass_status,sdk_status,single_sdk,record_type from sj_soft_tmp where update_from={$softid}" ;
				$this -> assign('softid',$softid);
			}			
			if(isset($_GET['softname'])){
				if (strlen($_GET['softname']) < 2) { 
					$this -> error("软件名不能小于两个字符");
				}
				$softname = trim($_GET['softname']);
				$where .= " and softname like '%{$softname}%'";
				$this -> assign('softname',$softname);	
			}
			//包名精确或模糊搜索
			if(isset($_GET['search_type'])){
				$this -> assign('search_type',$_GET['search_type']);	
			}
			if(isset($_GET['package'])){
				$package = trim($_GET['package']);
				if($_GET['search_type']==1){
					$where .= " and package='{$package}'";
				}else{
					//包名模糊搜索
					$all_package = $soft->table('sj_soft_status')->where(array('package'=>array('like',"%{$package}%")))->field('package')->select();
					$package_str = '';
					foreach($all_package as $k=>$v){
						$package_str .= "'".$v['package']."',";
					}
					$package_str = substr($package_str,0,-1);
					$where .= " and package in({$package_str})";
				}	
				
				$this -> assign('package',$package);	
			}
			if(isset($_GET['dev_name']) && $_GET['dev_name'] != '' || isset($_GET['email'])){
				if($_GET['dev_name'] != '' ){
					if (strlen($_GET['dev_name']) < 2) { 
						$this -> error("开发者名称不能小于两个字符");
					}
				}
				if(isset($_GET['dev_name'])){
					$this -> assign('dev_name',$_GET['dev_name']);	
					$wheres['dev_name'] = array('like',"%{$_GET['dev_name']}%");
				}
				if(isset($_GET['email'])){
					$this -> assign('email',$_GET['email']);	
					$email = trim($_GET['email']);
					$wheres['email'] = array("like","{$email}");
				}
				$devname = $soft->table('pu_developer')->where($wheres)->field('dev_id')->select();
				$dev_id = '';
				foreach ($devname as $n => $m ){
					$dev_id .= $m['dev_id'].",";
				}
				$devids = substr($dev_id,0,-1);
				$where .= " and dev_id in ({$devids})";
			}
			
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);	
				$this -> assign("init_cateid",$_GET['cateid']);
				$cateidarr = '';
				foreach($cateids as $vv){
					if($vv != ''){
						$cateidarr .= "',".$vv.",'".",";
					}
				}
				$cateidarrs = substr($cateidarr,0,-1);
				$where .= " and category_id in ({$cateidarrs})";
			}
			import('@.ORG.Page2'); // 导入分页类
			if(!empty($_GET['ip'])){
				$count  = $soft -> query("select count(*) as counts from sj_soft_tmp where {$status2}{$where}{$softid2}");
				$total = $count[0]['counts'];
			}else{
				$count  = $soft -> query("select sum(counts) from(select count(*) as counts from sj_soft where {$status}{$where}{$softid1}  union select count(*) as counts from sj_soft_tmp where {$status2}{$where}{$softid2}) A");
				foreach($count as $c){
					$total = $c['sum(counts)'];
				}
			}
			$Page  = new Page($total,10); 
			$Page->rollPage = 10; 
			$Page->setConfig('header','条记录&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
			$Page->setConfig('prefv','上一页');
			$Page->setConfig('next','下一页');
			$Page->setconfig('first','首页');
			$Page->setconfig('last','尾页');	
			if(!empty($_GET['ip'])){
				$list = $soft -> query("select 'tmp' as go,softid,id as tmpid,softname,package,abi,language,dev_id,version,version_code,category_id,status,record_type as hide,safe,update_from,dev_name,pass_status,sdk_status,single_sdk from sj_soft_tmp where {$status2}{$where}{$softid2}{$softid3} order by version desc limit {$Page->firstRow},{$Page->listRows}");
			}else{
				$list = $soft -> query("select * from (select 'soft' as go,softid ,0 as tmpid,softname,package,abi,language,dev_id,version,version_code,category_id,status,hide,safe,update_from,dev_name,1 as pass_status,0 as sdk_status,0 as single_sdk,0 as record_type from sj_soft where {$status}{$where}{$softid1} union select 'tmp' as go,softid,id as tmpid,softname,package,abi,language,dev_id,version,version_code,category_id,status,record_type as hide,safe,update_from,dev_name,pass_status,sdk_status,single_sdk,record_type from sj_soft_tmp where {$status2}{$where}{$softid2}{$softid3}) A order by version desc limit {$Page->firstRow},{$Page->listRows}");
			}
			//echo $soft->getlastsql();
			$softlist = array();
			$categoryids = '';
			$devids = '';
			$ids = '';
			$id = '';
			$package = array();
			$tmpid = array();
			$softid = array();
			foreach($list as $key => $val){
				$softlist[$key]['go'] = $val['go'];							
				$softlist[$key]['softid'] = $val['softid'];
				$softlist[$key]['tmpid'] = $val['tmpid'];
				$softlist[$key]['softname'] = $val['softname'];
				$softlist[$key]['package'] = $val['package'];
				$softlist[$key]['language'] = $val['language'];
				$softlist[$key]['version'] = $val['version'];				
				$softlist[$key]['version_code'] = $val['version_code'];		
				$softlist[$key]['pass_status'] = $val['pass_status'];		
				$softlist[$key]['safe'] = $val['safe'];							
				$softlist[$key]['hide'] = $val['hide'];							
				$softlist[$key]['status'] = $val['status'];							
				$softlist[$key]['update_from'] = $val['update_from'];							
				$softlist[$key]['dev_id'] = $val['dev_id'];							
				$softlist[$key]['dev_name_soft'] = $val['dev_name'];
				$softlist[$key]['record_type'] = $val['record_type'];
				
				$softlist[$key]['sdk_status'] = $val['sdk_status'];
				$softlist[$key]['single_sdk'] = $val['single_sdk'];
				//abi显示
				foreach($known_abis as $abi_key => $abi_value){
					if($abi_value & $val['abi'] || $val['abi'] == 0){
						$softlist[$key]['abis'][] = $abi_key."&nbsp;&nbsp;";
					}
				}
				$categoryids .= substr("{$val['category_id']}",1);
				if(!empty($val['dev_id'])){
					$devids .= "{$val['dev_id']}".",";
				}
				if($val['go'] != 'tmp'){
					$ids .= "{$val['softids']}".",";
				}
				if($val['go'] != 'soft'){
					$id .= "{$val['softids']}".",";
				}	
	
				if($val['package']) $package[] = $val['package'];
				if($val['tmpid']) $tmpid[] = $val['tmpid'];
				if($val['softid']) $softid[] =  $val['softid'];
			}
			//类别名称
			if($categoryids){
				$categoryid = array(
					'status'=>1,
					'category_id'=>array('in',substr($categoryids,0,-1)),
				);	
				$category_all = get_table_data($categoryid,"sj_category","category_id","category_id,name,status");
			}
			//开发者名称
			$dev_all = array();
			if($devids){
				$dev = array();
				$dev['dev_id'] = array('in',substr($devids,0,-1));
				$dev_all = get_table_data($dev,"pu_developer","dev_id","dev_id,dev_name,type,email,status");
			}
			$dev_claim_arr = array();
			$note_arr = array();
			if($package){
				$note_w = array(); 
				$note_w['package'] = array('in',$package); 
				$note_arr = get_table_data($note_w,"sj_soft_note","package","*");
				$where = array(
					'package' =>  array('in',$package),
					'status' => 1
				);
				$dev_claim_arr = get_table_data($where,"dev_claim","package","package,dev_id,softid");
				//首发
				$where = array(
					'package' =>  array('in',$package),
					'status' => array('exp','!=0')
				);
				$dev_debut_arr = get_table_data($where,"sj_soft_debut","package","package,status");
				//闪屏
				$dev_screen_arr = get_table_data($where,"sj_soft_screen","package","package,status");
				//新服
				$where = array(
					'pack_name' =>  array('in',$package),
					'status' => array('exp','!=0')
				);				
				$new_server = get_table_data($where,"sj_new_server","pack_name","pack_name,status,begin");
				//游戏联运
				$where = array(
					'package' =>  array('in',$package),
					'del' => 0
				);				
				$product_arr = get_table_data($where,"yx_product","package","package,type,status");
				$tm =time();
				//搜索适配列表
				$where = array(
					'package' =>  array('in',$package),
					'status' => 1,
					'start_time'=>array('elt',$tm),
					'end_time'=>array('egt',$tm)
				);				
				$search_adapter_arr = get_table_data($where,"sj_search_adapter","package","package,status");
				//软件屏蔽列表
				$where = array(
					'package' =>  array('in',$package),
					'status' => 1,
					'start_tm'=>array('elt',$tm),
					'end_tm'=>array('egt',$tm)
				);				
				$filter_arr = get_table_data($where,"sj_soft_filter","package","package,status");
				//礼包
				$where = array(
					'apply_pkg' =>  array('in',$package),
					'status' => array('exp','!=0')
				);
				$active_model = D('sendNum.sendNum');
				$gift = $active_model -> table('sendnum_tmp') -> where($where) -> field("apply_pkg,status,end_tm")->select();
				$gift_arr = array();
				foreach($gift as $k => $v){
					$gift_arr[$v['apply_pkg']] = $v;
				}
			}
			//软件图标
			$file_arr = array();
			if($softid){
				$filewhere = array();
				$filewhere['package_status'] = array('gt',0);
				$filewhere['softid'] = array("in",$softid);
				$file = $soft ->table('sj_soft_file')->where($filewhere)->field('softid,iconurl')->select();
				foreach($file as $k => $v){
					$file_arr[$v['softid']] = $v;
				}	
			}
			$file_tmp_arr = array();
			if($tmpid){
				$filewheres['tmp_id'] = array("in",$tmpid);
				$file_tmp = $soft ->table('sj_soft_file_tmp')->where($filewheres)->field('tmp_id,iconurl')->select();
				foreach($file_tmp as $k => $v){
					$file_tmp_arr[$v['tmp_id']] = $v;
				}
                //app认证
                $app_info = get_table_data(array('tmp_id'=>array('in',$tmpid)),"sj_appcert_soft","tmp_id","tmp_id,status,msg");
			}	
			// $softmodel = D('Dev.Softlist');
			// $iconlist = $softmodel -> new_icon_list($tmpid,$package);
			$year = date("Y");
			$month = date("m");
			$day = date("d");
			$dayBegin = mktime(0,0,0,$month,$day,$year);//当天开始时间戳	
            $appcert_status = C('appcert_status');
			foreach($list as $k => $v){
				$categoryid = substr("{$v['category_id']}",1,-1);
				$softlist[$k]['category_name'] = $category_all[$categoryid]['name'];
				//type 0公司 1个人 2团队
				if(!empty($v['dev_id'])){
					$softlist[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
					$softlist[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
					$softlist[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
					$softlist[$k]['dev_status'] = $dev_all[$v['dev_id']]['status'];
				}
				//
				if($note_arr[$v['package']]['shield_end'] >= time() && $note_arr[$v['package']]['shield'] == 1){
					$softlist[$k]['shield_list'] = $note_arr[$v['package']]['shield'];
				}
				//软件图标
				$iconurl = '';
				if($v['go'] == 'soft'){
					if($iconlist[1][$v['package']]['iconurl']){
						$iconurl = $iconlist[1][$v['package']]['iconurl'];
					}else{
						$iconurl = $file_arr[$v['softid']]['iconurl'];
					}
				}elseif($v['go'] == 'tmp'){
					if($iconlist[0][$v['tmpid']]['iconurl']){
						$iconurl = $iconlist[0][$v['tmpid']]['iconurl'];	
					}else if($file_tmp_arr[$v['tmpid']]['iconurl']){
						$iconurl = $file_tmp_arr[$v['tmpid']]['iconurl'];
					}else{
						$iconurl = $file_arr[$v['softid']]['iconurl'];
					}
				}
				$softlist[$k]['iconurl'] = $iconurl;	
				//认领列表
				$softlist[$k]['claim'] = $dev_claim_arr[$v['package']]['package'] ? 1 : 0 ;	
				//首发
				$softlist[$k]['debut_status'] = $dev_debut_arr[$v['package']]['status'];
				//闪屏
				$softlist[$k]['screen_status'] = $dev_screen_arr[$v['package']]['status'];
				//搜索适配列表
				$softlist[$k]['adapter_status'] = $search_adapter_arr[$v['package']]['status'];
				//软件屏蔽列表
				$softlist[$k]['filter_status'] = $filter_arr[$v['package']]['status'];
				//新服
				if($new_server[$v['package']]['status'] ==1 && $new_server[$v['package']]['begin'] < $dayBegin){
					$softlist[$k]['new_server_status'] = 100;
				}else{
					$softlist[$k]['new_server_status'] = $new_server[$v['package']]['status'];
				}
				$softlist[$k]['product_status'] = $product_arr[$v['package']]['type'];	
				//礼包				
				$softlist[$k]['gift_status'] = $gift_arr[$v['package']]['status'];			
				$softlist[$k]['gift_end_tm'] = $gift_arr[$v['package']]['end_tm'];	
				//app认证状态
				if($app_info[$softlist[$k]['tmpid']]['status']!='0'){
				$softlist[$k]['appcert_status'] = $appcert_status[$app_info[$softlist[$k]['tmpid']]['status']];
				}
				if($app_info[$softlist[$k]['tmpid']]['status']==3){
				$msg = json_decode($app_info[$softlist[$k]['tmpid']]['msg'],true);
				$softlist[$k]['certinfo'] = $msg['certinfo'];
				}else if($app_info[$softlist[$k]['tmpid']]['status']==4){
				$softlist[$k]['certinfo'] = '提供的信息有误，无法进行认证工作';
				}
			}
			$this -> assign('page', $Page->show());
			$this -> assign('total',$total);			
		}else{
			$this -> assign('del_soft',1);
		}
		//软件类别
		$soft_tmp = D("Dev.Softaudit");
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		// echo "<pre>";var_dump($softlist);die;
		$this -> assign('list',$softlist);

		$this->display();
	}
	//已上架列表__撤销操作
	public function newsoft_dropped(){
		$soft = M('soft');
		$tmp = M('soft_tmp');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要撤销的对象！')));		


		$where = array(
			'softid'=>array('in',$id_arr),
			//只能撤销新软件和升级的软件
			'hide_prev'=>array('in',array(2,5)),
		);
		$list = $soft -> where($where)->field('softid,status,softid,package,version_code,update_from') -> select();
		$error = '';
		foreach($list as $v){
			$soft_tmp_check = $tmp->where("package='{$v['package']}' and status in (2,3) and record_type < 5")->find();
			if($soft_tmp_check){
				$error .= "该软件在前台审核中有数据不能执行撤销！\n";
				continue;
			}
	        // $num= $soft->where("softid='{$v['softid']}' and update_from=0")->count();
	        if($v['update_from']==0){
	        	$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_ad($v['package'],time(),'',0,1);
				if($shield_error){
					$error .=$shield_error;
					// $error.="{$v['package']}软件在广告排期中";
					continue;
				}
	        }
			if($v['status'] == 1){
				$soft_status = $soft -> where("softid={$v['softid']}")->save(array('status'=>0));
				//取历史软件版本最大的一条改成上架
				//$soft_hisy = $soft->where("package='{$v['package']}' and version_code<'{$v['version_code']}' and status={$v['status']} and hide=0")->order('version_code desc')->find();
				//$soft->where("softid='{$soft_hisy['softid']}'")->data(array('hide'=>1))->save();
				//把上一条有记录还原
				$soft->where("softid='{$v['update_from']}'")->data(array('hide'=>1))->save();
				if($soft_status){
					$soft_tmp = $tmp -> where("softid={$v['softid']} and record_type < 5")->field('status,update_from,record_type') -> find();
					if($soft_tmp['status'] == 1){
						$map = array();
						if(!empty($soft_tmp['update_from'])){
							//返回上一条的softid
							$map['softid'] = $soft_tmp['update_from'];
						}
						$map['status'] =2;
						$map['pass_status'] =0;
						$map['pass_time'] =0;
						$tmp_status = $tmp -> where("softid={$v['softid']} and record_type < 5")->save($map);
						if(!$tmp_status){
							$error .= "软件ID为{$v['softid']}soft_tmp表状态变更不成功\n";
							continue;
						} else {
							//撤销后把草稿箱中的数据制成无效
							$tmp->where("package='{$v['package']}' and record_type >= '5' ")->save(array('status'=>0));
							//修改sj_soft_status表
							$option = array(
								'update_tm' => time(),
							);
							if($soft_tmp['record_type'] == 1){
								$option['soft_status'] = 31;
							}else if($soft_tmp['record_type'] == 2){
								$option['soft_status'] = 32;
							}else if($soft_tmp['record_type'] == 3){
								$option['soft_status'] = 33;
							}else if($soft_tmp['record_type'] == 4){
								$option['soft_status'] = 34;
							}	
							if($soft_hisy){
								$option['version'] = $soft_hisy['version'];
							}else{
								$option['version'] =0;
							}
							//update_soft_status($option,$v['package']);	
							getSoftStatusByPackage($v['package']);
							$this->writelog("撤销了softid为{$v['softid']}包名为{$v['package']}-update_from为{$v['update_from']}",'sj_soft',$v['softid'],__ACTION__ ,"","edit");
						}
					}else{
						$error .= "软件ID为{$v['softid']}不在审核通过状态\n";
						continue;
					}
				}else{					
					$error .= "软件ID为{$v['softid']}soft表状态变更不成功\n";
					continue;
				}
			}else{
				$error .= "软件ID为{$v['softid']}不在有效状态\n";
				continue;
			}
		}
		if($error){
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}
	}
	//已上架列表__下架操作
	public function undercarriage(){
		$model = new Model();
		$soft = M('soft');
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
		
		$softlist = $soft -> where(array('softid'=>array('in',$id_arr),'status'=>1))->field('hide,status,dev_id,softid,package,softname,version,version_code,total_downloaded,total_downloaded_add,total_downloaded_detain')->select();
		$time = time();
		$pkg_key = array();
		$data = array();
		$tmpid = array();
		$softid = array();
		$softname_arr = array();
		$soft_model = D('Dev.Softlist');
		$error = '';
		$AdSearch = D("Sj.AdSearch");
		foreach($softlist as $v){
			if($v['status'] ==1 && $v['hide'] == 1){
				$num= $soft->where("package='{$v['package']}' and status=1 and hide=1")->count();
				if($num==1){
					$shield_error=$AdSearch->check_ad($v['package'],time(),'',0,1);
					if($shield_error){
						$error .=$shield_error;
						// $error.="{$v['package']}软件在广告排期中";
						continue;
					}
				}
				$status_soft = $soft -> where("softid={$v['softid']}")->save(array('hide'=>3,'last_refresh'=>$time,'deny_msg'=>$msg));
				$data[$v['softid']] = $v;
				if($v['dev_id'] != 0){
					if($v['package']) $pkg_key[$v['package']] = 1; 
					$softname_arr[$v['package']] = $v['softname'];
					
				}	
				if($status_soft){                                    
					//下架后运营白名单的数据同步
					$soft_model -> updateWhitelistOnline($v,0);		
					if($v['softid']) $softid[] = $v['softid'];
					//把信息写入驳回日志表
					$map = array(
						'softid' => $v['softid'],
						'reason' => $msg,
						'create_tm' => $time,
						'adminid' => $_SESSION['admin']['admin_id'],
					);
					$log = $model->table('sj_reject_log')->add($map);
					//下架操作数据日志
					update_data_log($v['softid'],'delete',$soft);
					//操作日志
					$this->writelog("下架了softid为{$v['softid']}包名为{$v['package']}，下架原因：{$msg}",'sj_soft',$v['softid'],__ACTION__ ,"","edit");
				}else{
					$error .= "softid为{$v['softid']}soft表更改不成功\n";
					continue;
				}		
			}else{
				$error .= "softid为{$v['softid']}不在上架状态\n";
				continue;
			}
			//下架同步到用户中心
			$isSdkGame = isSdkGame($v['package']);
			$appkey = getAppKey($v['package']);
			if($isSdkGame&&$appkey){
				$vals = array('appKey' => $appkey, 'isOnline' => 0);
				$res = json_decode(modifyAppNew($vals), true);
			}
			
		}	
		$softid = implode(',',$softid);
		//查出soft_tmp表中审核中的未通过的数据--置成无效
		$list_tmp = $model -> query("select id from sj_soft_tmp where status >= 2 and   softid in ({$softid}) union select id from sj_soft_tmp where status >= 2 and update_from in ({$softid})");
		foreach($list_tmp as $vv){
			if($vv) $tmpid[] = $vv['id'];
		}			
		if($tmpid){
			$where = array(
				'id' => array('in',$tmpid)
			);
			$map = array(
				'status'=>0,
				'last_refresh'=>$time				
			);
			$model->table('sj_soft_tmp') -> where($where)->save($map);
		}
		$debut_arr = array();
		$screen_arr = array();
		if($pkg_key){
			foreach($pkg_key as $k=>$v){
				getSoftStatusByPackage($k);
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
		//如果驳回原因是--anzhi.com，则此数据进入飞沃回测列表
		if(strpos($msg,"anzhi.com") !== false){
			foreach($data as $key => $val){
				$down_cnt = $val['total_downloaded']+$val['total_downloaded_add']-$val['total_downloaded_detain'];
				$map2 = array(
					'softname' => $val['softname'],
					 'package' => $val['package'],
					 'version'    =>  $val['version'],
					 'version_code'  => $val['version_code'],
					 'down_cnt'  => $down_cnt,
					 'reason'    => $msg,
					 'down_tm' => $time,
					 'up_tm'   => $time
				);
				$is_back = $model->table('sj_backtest_soft')->where(array('softid'=>$key))->field('id')->find();						
				if($is_back){
					$model->table('sj_backtest_soft')->where(array('softid'=>$key))->save($map2);						
				}else{
					$map2['softid'] = $key;
					$model->table('sj_backtest_soft')->add($map2);
				}
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
		if($error){
			exit(json_encode(array('code'=>'0','msg'=>$error)));
		}else{
			exit(json_encode(array('code'=>'1','msg'=>$id_arr)));
		}
	}
	//已下架列表__上架操作
	public function shelves(){
		$soft = M('soft');
		$soft_list = D('Dev.Softlist');		
		$model = new model();
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));

		$id_str = implode(',',$id_arr);

		$list_soft = $soft -> where(array('softid'=>array('in',$id_arr),'status'=>1))->field('softid,status,hide,package,dev_id,softname')->select();
		$package = '';
		$error = '';
		$dev_id = array();
		foreach($list_soft as $v){
			if($v['hide'] == 3){
				$black_soft = $model ->table('sj_brush_black')->where("status=1 and package='{$v['package']}'")->count();
				if($black_soft==0){
					$status_soft = $soft -> where("softid={$v['softid']}") -> save(array('hide'=>1,'last_refresh'=>time()));
					if(!$status_soft){
						$error .= "软件ID为{$v['softid']}上架不成功\n";
						continue;
					} else {
						//恢复上架后运营白名单的数据同步
						$soft_list -> updateWhitelistOnline($v,1);					
						if($v['dev_id'] != 0 ){
							$dev_id[] = $v['dev_id'];
						}
                                                                                                  
                                                                                                                                    
						//恢复上架后删除之前的举报信息						
						$model->table('sj_feedback') -> where("softid={$v['softid']}") -> save(array('status'=>2,'update_at'=>time()));
						$this->writelog("上架了softid为{$v['softid']}包名为{$v['package']}。备注：{$_GET['beizhu']}",'sj_soft',$v['softid'],__ACTION__ ,"","edit");
						//修改sj_soft_status表
						update_soft_status(array('soft_status'=>50,'update_tm'=>time()),$v['package']);	
						//软件后台操作数据日志
						update_data_log($v['softid'],'add',$soft);
					}
					$package .= $v['package'].",";
				}else{
					$error .= "软件ID为{$v['softid']}在刷量黑名单中有记录，请修改后操作\n";
					continue;
				}
				//上架同步到用户中心
				$isSdkGame = isSdkGame($v['package']);
				$appkey = getAppKey($v['package']);
				if($isSdkGame&&$appkey){
					$vals = array('appKey' => $appkey, 'isOnline' => 1,'softname'=>$v['softname']);
					$res = json_decode(modifyAppNew($vals), true);
				}
			}else{
				$error .= "软件ID为{$v['softid']}不在下架状态\n";
				continue;
			}
		}
		if($error){
			exit(json_encode(array('code'=>0,'msg'=>$error)));
		}else{
			//更新pu_developer字段statistics_on
			$soft_tmp = D("Dev.Softaudit");
			$soft_tmp -> update_developer($dev_id);
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}		
	}
	//软件管理（新）__撤销认领
	public function update_claim(){
		$soft = M('soft');
		$soft_tmp = M('soft_tmp');
		$Softaudit = D("Dev.Softaudit");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要操作的对象！')));
		
		$id_str = implode(',',$id_arr);
		
		$list_soft = $soft -> where(array('softid'=>array('in',$id_arr),'status'=>1))->field('softid,status,hide,package,claim_status')->select();
		
		$dev_id = array();
		foreach($list_soft as $v){
			$package[] = $v['package'];
			if($v['dev_id'] != 0){
				$dev_id[] =  $v['dev_id'];
			}
			$this->writelog("撤销认领了softid为{$v['softid']}包名为{$v['package']}",'sj_soft',$v['softid'],__ACTION__ ,"","edit");
		}	
		$status_soft = $soft ->where(array('package'=>array('in',$package),'status'=>1)) -> save(array('dev_id'=>0,'dev_name'=>'','dever_email'=>'','claim_status'=>0));
		$status_soft_tmp = $soft_tmp ->where(array('package'=>array('in',$package))) -> save(array('dev_id'=>0,'dev_name'=>'','dever_email'=>'','claim_status'=>0));
		//更新pu_developer字段statistics_on
		$Softaudit -> update_developer($dev_id);
		//echo $soft->getlastsql();exit;
		if($status_soft){
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}

	}
	//官方认证
	public function setOfficial(){
		$soft = M('soft');
		$note = M('soft_note');
		$note_single = M('soft_note_single');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));
		if($_GET['status'] == 1){
			$start_time = strtotime($_GET['start_time']);
			$terminal_time = strtotime($_GET['terminal_time']);
			
			if($start_time > $terminal_time){
				exit(json_encode(array('code'=>'0','msg'=>'开始时间不能大于结束时间')));
			}
		}
		$id_str = implode(',',$id_arr);
		
		$list_soft = $soft -> where(array('softid'=>array('in',$id_arr),'status'=>1))->field('softid,status,hide,package,claim_status')->select();
		
		foreach($list_soft as $v){
			//查询note_single表有没这条数据有update没有add
			$note_single_list = $note_single -> where("softid='{$v['softid']}'")->field('status') -> find();
			$maps = array();
			$maps['status'] = $_GET['status'];
			if($_GET['status'] == 1){
				$maps['start_time'] = $start_time;
				$maps['terminal_time'] = $terminal_time;
				if($_GET['official_note']) $maps['official_note'] = $_GET['official_note'];
			}
			$maps['update_time'] = time();
			if($note_single_list){
				$note_single_status = $note_single -> where("softid='{$v['softid']}'") -> save($maps);
			}else{
				$maps['softid'] = $v['softid'];
				$maps['package'] = $v['package'];
				$note_single_add = $note_single -> add($maps);
			}
			$package[] = $v['package'];
			$packages = array_unique($package);
		}
		foreach($packages as $v){			
			//查询note表有没这条数据有update没有add
			$note_list = $note -> where("package='{$v}'")->field('status,package')->find();
			$map = array();
			$map['status'] = $_GET['status'];
			if($_GET['status'] == 1){
				$map['start_time'] = $start_time;
				$map['terminal_time'] = $terminal_time;
				if($_GET['official_note']) $map['official_note'] = $_GET['official_note'];
			}else{
				$map['start_time'] = '';
				$map['terminal_time'] = '';
				$map['official_note'] = '';
			}	
			$map['update_time'] = time();			
			if(!empty($note_list)){
				$note_status = $note -> where("package='{$note_list['package']}'") -> save($map);
		 		if($note_status){
					$this->writelog("官方认证了包名为{$note_list['package']}",'sj_soft_note',$note_list['package'],__ACTION__ ,"","edit");
				}
			}else{
				$map['package'] = $v;
				$note_add = $note -> add($map);
				if($note_add){
					$this->writelog("官方认证id为{$note_add}包名为{$note_list['package']}",'sj_soft_note',$note_add,__ACTION__ ,"","add");
				}
			}
		}
		exit(json_encode(array('code'=>1,'msg'=>$id_arr)));	
	}
	//批量管理
	public function Batch_management(){
		$this->display();
	}
	//批量管理__展示数据
	function Batch_management_list(){
		$soft_tmp = D("Dev.Softaudit");
		$model = new Model();
		list($adlist,$adlist2,$adlist3) = $soft_tmp->asort_ad_select();		
		$this -> assign('adlist',$adlist);
		$this -> assign('adlist2',$adlist2);
		$this -> assign('adlist3',$adlist3);
		if (!empty($_FILES)) {
			$array = array('csv');
			$ytypes = $_FILES['csv']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			if(!in_array($type,$array)){
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error('上传格式错误');
			}
			if (!empty($_FILES['csv']['tmp_name'])){		
				$fp = fopen($_FILES['csv']['tmp_name'], 'r');
				$package = array();
				$all = array(); 
				while(!feof($fp)){
					$pkg = fgetcsv($fp);
					if (empty($pkg)) continue;
					$pkg = trim($pkg[0]);
					$package[] = $pkg;
					$all[$pkg] =1;
				}
				fclose($fp);
				
				$soft = $model->table('sj_soft') -> where(array('package'=>array('in',$package),'status'=>1,'hide'=>1))->field('package')->select();
				$packagearr = array(); 
				foreach($soft as $val){
					//有效
					$packagearr[] = $val['package'];
					//无效	
					unset($all[$val['package']]);
				}
				$uniqid = uniqid();
				//无效
				mkdir('/tmp/admin/', 0755, true);
				$file1 = '/tmp/admin/'. session_id(). '_'.$uniqid.'disabled'. ".csv";
				$_SESSION['tmp_ineffective_file'] = $file1;
				$fpp = fopen($file1, 'w');
				foreach($all as $k => $v){
					fwrite($fpp,$k."\n");
				}
				fclose($fpp);
				//有效
				$file = '/tmp/admin/'. session_id(). '_'.$uniqid. ".csv";
				$_SESSION['tmp_effective_file'] = $file; 
				$fp = fopen($file, 'w');
				foreach($packagearr as  $v){
					fwrite($fp,$v."\n");
				}
				fclose($fp);
			}
		} else {
			$uniqid = $_GET['uid'];
			$fp = fopen($_SESSION['tmp_effective_file'], 'r');
			$package = array();
			while(!feof($fp)){
				$pkg = fgetcsv($fp);
				if (empty($pkg)) continue;
				$package[] = trim($pkg[0]);
			}
			fclose($fp);
		}
		$this->assign('uniqid',$uniqid);
		if($_GET){
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
			}
			//起止日期
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
			//软件类别条件
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
			}
			//下载量条件
			if(!empty($_GET['uplode']) && !empty($_GET['uplode1'])){
				$this->assign('uplode', $_GET['uplode']);
				$this->assign('uplode1', $_GET['uplode1']);
			}
		}			
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$soft_list = D('Dev.Softlist');
		list($result,$total, $page,$reason_list) = $soft_list->getsoftinformation($_GET, $limit,$uniqid,$package);
		//软件类别
		$catname = $soft_tmp ->getCategoryArray();
		$cname = array();
		foreach($catname[0] as $n){
			$threecat = array();
			foreach($catname[$n['category_id']] as $v){
				foreach( $catname[$v['category_id']] as $m){
					$threecat[] = $m;
				}
			}
			$n['sub'] = $threecat;
			$cname[] = $n;			
		}
		//无效数据
		$fpp = fopen($_SESSION['tmp_ineffective_file'], 'r');
		while(!feof($fpp)){
			$pkgs = fgetcsv($fpp);
			if (empty($pkgs)) continue;
			$packages[] = trim($pkgs[0]);
		}
		fclose($fpp);
		$this -> assign('pkgs',$packages);
		$this -> assign('pkgs_count',count($packages));
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$result);
		$this -> assign('cname',$cname);
		$this -> assign('reason_list',$reason_list);
		//url路径__下载量排序和更新时间排序
	 	$param = $_GET;
		if($param['orderby'] == 'name'){
			if($param['order'] == 'd'){
				$param['order'] = 'a'; 
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
				$this -> assign('order2',$param['order']);
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}	
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
			}elseif($param['order'] == 'a'){
				$param['order'] = 'd'; 
				$this -> assign('order1',$param['order']);
			}elseif($param['order'] == ''){
				$param['order'] = 'd'; 
			}
		}
		$this -> assign('orderby',$param['orderby']);
		unset($param['orderby']);
		$param = http_build_query($param);
		$this -> assign('param',$param);	
		//角标
		$corner_mark = M('corner_mark');
		$sj_corner_mark = $corner_mark ->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);	
		//加入合作渠道
		$tvCategoryModel = M('prove_category');
		$res = $tvCategoryModel->where(array('status'=>1))->field('id,category_name,prove_type')->select();

		$provider = array(
			'1' => 'LG'
		);

		$category = array();
		foreach ($res as $key => $value) {
			# code...
			$prove_type = $value['prove_type'];
			!isset($category[$prove_type]) && $category[$prove_type] = array();
			$category[$prove_type][] = $value;
		}
		$this->assign('category', $category);
		$this->assign('provider', $provider);	
		//官方为认证默认时间
		$start_at = date("Y-m-d H:i:s",time());
		$end_at = "2023-01-01 00:00:00";
		$this->assign("start_at",$start_at);
		$this->assign("end_at",$end_at);		
		$this->display('Batch_management_list');
	}
	//批量管理__导出无效数据
	function Export_failure_package(){
		$uid = $_GET['uid'];
		$file = '/tmp/admin/'. session_id(). '_'.$uid.'disabled'. ".csv";
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="disabled.csv"');
		header('Cache-Control: max-age=0');	
		echo file_get_contents($file);
		exit;
	}
	//批量管理__导出有效数据
	function Export_effective_package(){
		$uid = $_GET['uid'];
		$file = '/tmp/admin/'. session_id(). '_'.$uid. ".csv";
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="effective.csv"');
		header('Cache-Control: max-age=0');	
		echo file_get_contents($file);
		exit;
	}
	//检测软件是否在广告排期
	function Advertisement_scheduling(){
		$soft_list = D('Dev.Softlist');
		$soft = M('soft');
		$soft_debut_db = M('soft_debut');
		$soft_screen_db = M('soft_screen');
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择对象！')));
		$list_soft = $soft -> where(array('softid'=>array('in',$id_arr),'status'=>1))->field('softid,status,hide,package')->select();
		$package = '';
		foreach($list_soft as $v){
			$pro_soft = $soft_list -> ajax_pro_soft ($v['package']);
			if($pro_soft == true){
				$package .= "包名为".$v['package']."的软件在广告排期中\n";
			}
			$tm = time();
			$soft_debut = $soft_debut_db -> where("package = '{$v['package']}' and status=2 and debut_time+(debut_length*3600) >= '{$tm}'")->find();
			if($soft_debut){
				$package .= "包名为".$v['package']."软件已通过首发申请\n";
			}			
			$soft_screen = $soft_screen_db -> where("package = '{$v['package']}' and status=2")->find();
			if($soft_screen){
				$package .= "包名为".$v['package']."软件已通过闪屏申请\n";
			}
		}
		if($package){
			exit(json_encode(array('code'=>'0','msg'=>$package)));
		}else{
			echo (json_encode(array('code'=>'1')));
		}
	}
	//审核中软件列表查看
	public function soft_view(){
			//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		$soft = M('soft');
		$icon = M('icon');
		$file = M('soft_file');
		$thumb = M('soft_thumb');
		$book = M('soft_bookright');
		$category = M('category');
		$softid = $_GET['softid'];
		$record_type = $_GET['record_type'];
		$soft_list = $soft->where("softid={$softid}")->find();
		$file_list = $file->where("softid={$softid} and package_status=1")->find();
		if(!empty($file_list) && !empty($file_list['filesize']))
		{   
			$file_list['filesize'] = sprintf("%1\$.2f",($file_list['filesize']/(1024*1024)))."M";
		}
		$newicon = $icon->where("softid='{$softid}' and status=1")->find();	
		$thumb_list = $thumb->where("softid={$softid} and status=1")->select();
		$book_list = $book->where("softid={$softid} and status=1")->find();
		$cid = substr($soft_list['category_id'],1,-1);
		$cname = $category -> where("category_id='{$cid}'")->field('name,parentid')->find();
		if($cname['parentid'] > 3){
			$fid = $category -> where("category_id={$cname['parentid']}")->field('parentid')->find();
		}else{
			$fid = $cname;
		}
		//abi显示
		foreach($known_abis as $abi_key => $abi_value){
			if($abi_value & $soft_list['abi'] || $soft_list['abi'] == 0){
				$abis[] = $abi_key."&nbsp;&nbsp;";
			}
		}
		$soft_tags = explode(',',$soft_list['tags']);
		$this -> assign('soft_tags',$soft_tags);
		$this -> assign('soft_list',$soft_list);
		$this -> assign('abis',$abis);
		$this -> assign('file_list',$file_list);
		$this -> assign('thumb_list',$thumb_list);
		$this -> assign('book_list',$book_list);
		$this -> assign('newicon',$newicon);
		$this -> assign('cname',$cname);
		$this -> assign('fid',$fid);
		$this -> display();

	}

	//下载方式配置
	function save_download_type(){
		$softids = $_POST['id_str'];
		$download_type = $_POST['d_type'];
		$id_arr = explode(',',$softids);
		if($softids&&$download_type){
			$model = M('');
			$soft_data = $model -> table('sj_soft')->where("softid in ({$softids})") -> field('softid,package')->select();
			if($soft_data){
				$package = array();
				foreach($soft_data as $k=>$v){
					$package[] = $v['package'];
				}
				$package_str = implode("','",$package);
				$soft_info = $model->table('sj_soft_expand')->where("package in('{$package_str}')")->field('package')->select();
				$add_pack = $update_pack = array();
				if($soft_info){
					foreach($soft_info as $k => $v){
						$update_pack[] = $v['package'];
					}
					$add_pack = array_diff($package,$update_pack);
				}else{
					$add_pack = $package;
				}
				$now = time();
				if(count($add_pack)>0){
					foreach($add_pack as $a_v){
						$t_arr = array(
							'package' => $a_v,
							'download_type' => $download_type,
							'add_tm' => $now,
							'update_tm' => $now
						);
						$model->table('sj_soft_expand')->add($t_arr);
					}
				}
				if(count($update_pack)>0){
					$update_pack_str = implode("','",$update_pack);
					$save_data = array(
						'download_type' => $download_type,
						'update_tm' => $now
					);
					$model->table('sj_soft_expand')->where("package in('{$update_pack_str}')")->save($save_data);
				}
				$download_type_info = array('1'=>'安智下载','2'=>'第三方下载');
				$this->writelog("更改软件ID为{$softids}的下载方式为{$download_type_info[$download_type]}",'sj_soft_expand',$softids,__ACTION__ ,"","edit");
				exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
			}else{
				exit(json_encode(array('code'=>0,'msg'=>'未找到此软件')));
			}
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'参数错误')));
		}
	}

	//仅搜索显示
	function update_only_search(){
		$model = M('soft');
		$m_note = M('soft_note');
		$softids = $_GET['tmp_softids'];
		$only_type = $_GET['only_type'];
		$id_arr = explode(',',$softids);
		if($softids && $only_type != ''){
			$data['only_search'] = $only_type;
			$data['update_time'] = time();
			$soft_data = $model -> where("softid in ({$softids})") -> field('softid,package')->select();
			if($soft_data){
				$shield_error_all='';
				$package_str = '(';
				$ids='';
				foreach($soft_data as $soft){
					if($only_type==1){
						//屏蔽软件上排期时报警需求 新增  yuesai
						$AdSearch = D("Sj.AdSearch");
						$shield_error=$AdSearch->check_ad($soft['package'],time(),'',1,1);
						if($shield_error && $_GET['from_type'] ==1){
							$this -> error($shield_error);
						}elseif($shield_error){
							$shield_error_all.=$shield_error;
						}else{
							$package_str .= "'".$soft['package']."',";
							$ids.=$soft['softid'].",";
						}
					}else{
						$package_str .= "'".$soft['package']."',";
						$ids.=$soft['softid'].",";
					}
				}
				$package_str = substr($package_str,0,-1);
				$package_str .= ')';
				$condition['package'] = array('exp',' in'.$package_str);
				$res = $m_note -> where($condition) -> save($data);
			}
			if($res) $this->writelog("更改软件包为{$ids}的仅搜索显示为{$only_type}",'sj_soft_note',$ids,__ACTION__ ,"","edit");
			if($res && $_GET['from_type'] ==1){
				$this->success('操作成功');	
			}else{	
				if($shield_error_all){
					exit(json_encode(array('code'=>0,'msg'=>$shield_error_all)));
				}else{
					exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
				}
				
			}			
		}else{
		    $this->error("参数错误");
		}
		
	}

	//隐藏下载量
	function update_hidden_downloads(){
		$model = M('soft');
		$m_soft_downloads_hidden_config = M('soft_downloads_hidden_config');
		$softids = $_GET['tmp_softids'];
		$only_type = $_GET['only_type'];
		$id_arr = explode(',',$softids);
		if($softids && $only_type != ''){
			
			$soft_data = $model -> where("softid in ({$softids})") -> field('softid,package')->select();
			if($soft_data){
				//更新操作

				if($only_type==0){
					$data=array();
					$data['is_hidden'] = $only_type;
					$data['update_tm'] = time();
					$data['admin_id'] = $_SESSION['admin']['admin_id'];
					$package_str = '(';
					$ids='';
					foreach($soft_data as $soft){
						$package_str .= "'".$soft['package']."',";
						$ids.=$soft['softid'].",";
					}
					$package_str = substr($package_str,0,-1);
					$package_str .= ')';
					$condition['is_hidden']=1;
					$condition['package'] = array('exp',' in'.$package_str);
					$res = $m_soft_downloads_hidden_config -> where($condition) -> save($data);
				}else if($only_type==1){
					foreach($soft_data as $soft){
						$ids.=$soft['softid'].",";
						$data=array();
						$data['is_hidden'] = $only_type;
						$data['create_tm'] = time();
						$data['softid'] = $soft['softid'];
						$data['package'] = $soft['package'];
						$data['admin_id'] = $_SESSION['admin']['admin_id'];
						$res=$m_soft_downloads_hidden_config ->add($data);
					}
				}
				
			}
			if($only_type==1){
				if($res) $this->writelog("设置软件id为{$ids}的下载量为隐藏",'sj_soft_downloads_hidden_config',$ids,__ACTION__ ,"","edit");
			}else if($only_type==0){
				if($res) $this->writelog("设置软件id为{$ids}的下载量为显示",'sj_soft_downloads_hidden_config',$ids,__ACTION__ ,"","edit");
			}
			
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));		
		}else{
		    $this->error("参数错误");
		}
		
	}
	//是否屏蔽北京
	function update_bj_shield(){
		
		$model = M();
		$m_soft_bj_shield = M('soft_bj_shield');
		$softids = $_GET['tmp_softids'];
		$only_type = $_GET['only_type'];
		$shelves_type = $_GET['shelves_type'];
		$id_arr = explode(',',$softids);
		if($softids && $only_type != ''){
			
			// $soft_data = $model -> where("softid in ({$softids})") -> field('softid,package')->select();
			$soft_table=($shelves_type==0)?'sj_soft_tmp':'sj_soft';
			$where=($shelves_type==0)?"s.id in ({$softids})":"s.softid in ({$softids})";
			$soft_data = $model->table("{$soft_table} s")->join("sj_soft_bj_shield n ON n.package = s.package")->where($where)->field('s.*,n.package as shield_package')->select();
			
			if($soft_data){
				//更新操作
				foreach($soft_data as $soft){
					if($soft['shield_package']){
						$condition=array();
						$condition['package'] = $soft['package'];
						$data['is_shield']=$only_type;
						$data['update_tm'] = time();
						$data['admin_id'] = $_SESSION['admin']['admin_id'];
						$res = $m_soft_bj_shield -> where($condition) -> save($data);
						
					}else{
						$data=array();
						$data['is_shield'] = $only_type;
						$data['create_tm'] = time();
						
						$data['package'] = $soft['package'];
						$data['admin_id'] = $_SESSION['admin']['admin_id'];
						$res=$m_soft_bj_shield ->add($data);
					}
					
				}

			}
			if($only_type==1){
				if($res) $this->writelog("设置软件softid为{$softids}隐藏北京",'sj_soft_bj_shield',$softids,__ACTION__ ,"","edit");
			}else if($only_type==0){
				if($res) $this->writelog("设置软件softid为{$softids}取消隐藏北京",'sj_soft_bj_shield',$softids,__ACTION__ ,"","edit");
			}
			
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));		
		}else{
		    $this->error("参数错误");
		}
		
	}
	//已上架列表__屏蔽
	function soft_shield(){
		$shield_start_time = date("Y-m-d H:i:s",time());
		$shield_end_time = "2023-01-01 00:00:00";
		$this->assign("shield_start_time",$shield_start_time);
		$this->assign("shield_end_time",$shield_end_time);	
		$this->assign("type",'soft');	
		$this->assign("act",'soft_shield');	
		//排期中
		$softid = explode(',',$_GET['id']);
		$softmodel = D('Dev.Softlist');
		$msg = $softmodel ->get_Schedule($softid);
		$this->assign("msg",$msg);	
		$this -> display('shield_soft');
	}
	//已下架列表__屏蔽
	function undercarriage_shield(){
		$shield_start_time = date("Y-m-d H:i:s",time());
		$shield_end_time = "2023-01-01 00:00:00";
		$this->assign("shield_start_time",$shield_start_time);
		$this->assign("shield_end_time",$shield_end_time);	
		$this->assign("type",'soft');	
		$this->assign("act",'undercarriage_shield');	
		//排期中
		$softid = explode(',',$_GET['id']);
		$softmodel = D('Dev.Softlist');
		$msg = $softmodel ->get_Schedule($softid);
		$this->assign("msg",$msg);			
		$this -> display('shield_soft');
	}
	//不安全列表__屏蔽
	function insecurity_shield(){
		$shield_start_time = date("Y-m-d H:i:s",time());
		$shield_end_time = "2023-01-01 00:00:00";
		$this->assign("shield_start_time",$shield_start_time);
		$this->assign("shield_end_time",$shield_end_time);	
		$this->assign("type",'insecurity');	
		$this->assign("act",'insecurity_shield');
		//排期中
		$softid = explode(',',$_GET['id']);
		$softmodel = D('Dev.Softlist');
		$msg = $softmodel ->get_Schedule($softid);
		$this->assign("msg",$msg);			
		$this -> display('shield_soft');
	}
	//屏蔽提交
	function shield_soft_do(){
		$note = M('soft_note');
		$soft = M('soft');
		$soft_tmp = M('soft_tmp');
		$softmodel = D('Dev.Softlist');
		$Softaudit = D("Dev.Softaudit");
		$model = new Model();
		$id_arr = explode(',',$_GET['id']);
		$package = array();
		if($_GET['shield'] == 1){
			$start= strtotime($_GET['start_tm']);
			$end = strtotime($_GET['end_tm']);
			if(count($id_arr)>0){
				//如修改屏蔽结束时间则用修改的时间，否则使用定时上架时间作为结束时间
				if(strtotime($_GET['end_tm'])==strtotime("2023-01-01 00:00:00")){
					$pass_time = $softmodel->get_pass_time($id_arr,'package');
				}
			}
			if($end < $start){
				exit(json_encode(array('code'=>'0','msg'=>'结束时间不能小于开始时间！')));
			}		
		}	
		$tm = time();	
		//判断是哪个列表过来的
		if($_GET['type'] == 'soft'){
			$where = array();
			$where['softid'] = array('in',$id_arr);
			$softlist = $soft -> where($where)->field('package')->select();
			$soft -> where($where)->save("last_refresh ='{$tm}'");
			foreach($softlist as $val){
				$package[] = $val['package'];
			}
		}else if($_GET['type'] == 'tmp' && !empty($_GET['act'])){
			$where_t = array();
			$where_t['id'] = array('in',$id_arr);
			$softtmp_list = $soft_tmp -> where($where_t)->field('package')->select();
			$soft_tmp -> where($where_t)->save("last_refresh ='{$tm}'");
			foreach($softtmp_list as $val){
				$package[] = $val['package'];
			}
		}else if($_GET['type'] == 'insecurity'){
			$t_id =array();
			$o_id =array();
			foreach($id_arr as $v){
				list($flag,$id) = explode('_', $v);
				if($flag == 't'){
					$t_id[] = $id;
				}else if($flag == 'o'){
					$o_id[] =  $id; 
				}
			}
			$where = array();
			$package_o = array();
			$where['softid'] = array('in',$o_id);
			$softlist = $soft -> where($where)->field('package')->select();
			$soft -> where($where)->save("last_refresh ='{$tm}'");
			foreach($softlist as $val){
				$package_o[] = $val['package'];
			}
			$where_t = array();
			$package_t = array();
			$where_t['id'] = array('in',$t_id);
			$softtmp_list = $soft_tmp -> where($where_t)->field('package')->select();
			$soft_tmp -> where($where_t)->save("last_refresh ='{$tm}'");
			foreach($softtmp_list as $val){
				$package_t[] = $val['package'];
			}
			$package = array_merge($package_o,$package_t);	
		}
		$package = array_unique($package);
		//屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
		$i=0;
		$ad_error=array();
		foreach($package as $val){
			$check_ad=$AdSearch->check_ad($val,$start,$end);
			if($check_ad){
				$i++;
				$ad_error[$i]['cause']=$check_ad;
				$ad_error[$i]['package']=$val;
			}
		}
		// $ad_error[0]['cause']=12345678;
		// $ad_error[0]['package']="com.tencent.qq123";
		if($ad_error){
			$file_name=$this->export_ad($ad_error);
	   		 exit(json_encode(array('code'=>'0','msg'=>$file_name,'shield_soft'=>1,'ad_msg'=>$ad_error)));
		}		
		if($_GET['shield'] == 1){
			$error  = '' ;
			foreach($package as $v){
				//首发
				$soft_debut = $model->table('sj_soft_debut') -> where("package = '{$v}' and status=2 and debut_time+(debut_length*3600) >= '{$time}' and del_status =1")->find();
				if($soft_debut){
					$error .= "包名【{$v}】为在首发中不能屏蔽\n";
				}
				//闪屏
				$soft_screen = $model->table('sj_soft_screen') -> where("package = '{$v}' and status=2")->find();	
				if($soft_screen){
					$error .= "包名【{$v}】为在闪屏中不能屏蔽\n";
				}
			}	
			if($error){
				exit(json_encode(array('code'=>'0','msg'=>$error)));
			}
		}
		//操作临时表，通过
		if($_GET['type'] == 'tmp' && !empty($_GET['act'])){
			if($_GET['act'] == 'newsoft_shield' || $_GET['act'] == 'softupgrade_shield'){
				if($_GET['act'] == 'softupgrade_shield'){
					list($resert,$add_result) = $Softaudit -> update_tmp($id_arr,3);				
				}else if($_GET['act'] == 'newsoft_shield') {
					list($resert,$add_result) = $Softaudit -> update_tmp($id_arr,1,2);
				}
				//记录操作日志
				foreach($add_result as $v){
					if($v['softid']){
						//操作日志
						$this->writelog("通过了softid为{$v['softid']}包名为{$v['package']}的软件",'sj_soft_tmp',$v['softid'],__ACTION__,$_GET['act'],'edit');
						//软件后台操作数据日志
						update_data_log($v['softid'],'add',$soft);
						if($_GET['act'] == 'softupgrade_shield'){
							//增量更新
							$Softaudit -> send_incremental_update($v['softid'],$v['package'],$v['total_downloaded']);
						}
					}
				}
			}else if($_GET['act'] == 'edit_audit_shield'){
				list($return,$add_result) = $Softaudit -> update_soft($id_arr);
				foreach($add_result as $v){
					//操作日志
					$this->writelog("通过了softid为{$v['softid']}包名为{$v['package']}的软件",'sj_soft_tmp',$v['softid'],__ACTION__,$_GET['act'],'edit');
					update_data_log($v['softid'],'update',$soft);
				}
			}
		}	
						
		foreach($package as $v){
			$map = array();
			if($_GET['shield'] == 1){
				$map['shield_start'] = strtotime($_GET['start_tm']);
				if(!empty($pass_time[$v]['pass_time'])){
					$map['shield_end'] = $pass_time[$v]['pass_time'];
				}else{
					$map['shield_end'] = strtotime($_GET['end_tm']);
				}

			}else{
				$map['shield_start'] = 0;
				$map['shield_end'] = 0;
			}
			$map['shield'] = $_GET['shield'];
			$note_shield = $note -> where("package ='{$v}'")->find();		
			if($note_shield){
				$note -> where("package = '{$v}'")->save($map);
			}else{
				$map['package'] = $v;
				$note -> add($map);
			}
			if($_GET['shield'] == 1){
				$softmodel -> reason_Operation("sj_soft_note",$v,$_GET['shield_content'],$_SESSION['admin']['admin_id']);
				$this->writelog("屏蔽了包名【{$v}】的软件原因：{$_GET['shield_content']}",'sj_soft_note',$v,__ACTION__,$_GET['act'],'edit');
			}else{
				$this->writelog("包名【{$v}】的软件屏蔽状态改为正常",'sj_soft_note',$v,__ACTION__,$_GET['act'],'edit');
			}			
		}
		exit(json_encode(array('code'=>'1','msg'=>$id_arr)));
	}
	 // 下载批量屏蔽失败的csv文件
    function pub_down_shield_csv() {
    	header( 'Content-Type:text/html;charset=utf-8');  
        $file_dir = "/tmp/shield_failure/".$_GET['file_name'];
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            // Header("Content-Disposition: attachment; filename=" . urlencode("屏蔽失败_".date('Y-m-d')).'.csv');
            Header("Content-Disposition: attachment; filename=" . "屏蔽失败_".date('Y-m-d').'.csv');
            echo fread($file, filesize($file_dir));
            fclose($file);
            // unlink($file_dir);
            exit(0);
        } else {
            // header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }

    }

     //生成屏蔽失败文件
    function export_ad($lists){
        header( 'Content-Type:text/html;charset=utf-8 ');  
        $file_dir="/tmp/shield_failure/";
        if(!is_dir($file_dir)){
            if(!mkdir(iconv("UTF-8", "GBK", $file_dir),0777,true)){
                jsmsg("创建目录失败", -1);
            }
        }
        $file_name="屏蔽失败_".date('Y-m-d-h-i-s').'.csv';
        $file_re=$file_dir.$file_name;
        if(!file_exists($file_re)){
            $fp = fopen($file_re, 'w');
            $heade = array(iconv("UTF-8", "GBK", '产品包名'),iconv("UTF-8", "GBK", '屏蔽失败原因'));
            fputcsv($fp, $heade);
            foreach($lists as $v){
                $put_arr = array();
                $put_arr['package'] = $v['package'] ? iconv("UTF-8", "GBK", $v['package']) : "\t";
                $put_arr['cause'] = $v['cause'] ?  iconv("UTF-8", "GBK", $v['cause']) : "\t";
                fputcsv($fp, $put_arr);
            }
        }
        return $file_name;
    }
	//软件屏蔽列表
	function shield_soft_list(){
		
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
		if(isset($_GET['email'])){
			$this->assign('email',$_GET['email']);
		}
		//开发者类型	
		if(isset($_GET['dev_type'])){
			$this->assign('dev_type',$_GET['dev_type']);
		}
		//起止日期
		if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
			$begintime = strtotime($_GET['begintime']);
			$endtime = strtotime($_GET['endtime']);
			if($endtime<$begintime){   
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error("时间无效,请选择正常时间");
			}
			$this->assign('begintime',$_GET['begintime']);
			$this->assign('endtime',$_GET['endtime']);
		}
		//软件类别条件
		if(!empty($_GET['cateid'])){
			$cateids = explode(',',$_GET['cateid']);
			$cateid = array_flip($cateids);
			$this -> assign('cateid',$cateid);
			$this -> assign("init_cateid",$_GET['cateid']);
		}		
		//下载量搜索
		if(isset($_GET['uplode']) && isset($_GET['uplode1'])){
			$this->assign('uplode', $_GET['uplode']);
			$this->assign('uplode1', $_GET['uplode1']);	
		}
		if(isset($_GET['dev_id'])){
			$this->assign('dev_id',$_GET['dev_id']);
		}
		//分页		
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$soft_list = D('Dev.Softlist');
		list($total,$page,$data) = $soft_list->getsoft_shield($_GET, $limit);
		//软件类别
		$soft_tmp = D("Dev.Softaudit");
		$cname = $soft_tmp ->return_category();
		$this -> assign('cname',$cname);
		$this -> assign('page', $page->show());
		$this -> assign('total',$total);		
	
		$this -> assign('list',$data['note_list']);
		$this -> assign('soft_info',$data['soft_info']);
		$this -> assign('soft_info_tmp',$data['soft_info_tmp']);
		$this -> assign('soft_file',$data['soft_file']);
		$this -> assign('soft_file_tmp',$data['soft_file_tmp']);
		$this -> assign('developer_info',$data['developer_info']);
		$this -> assign('category_info',$data['category_info']);
		$this -> assign('reason',$data['reason']);
		if(in_array($_SESSION['admin']['admin_id'],$this->admin_down)){
			$this -> assign('div_no_show',1);
		}
		$this->display();
	}
	//软件屏蔽列表__修改
	function edit_shield(){
		$model = M('soft_note');
		if($_POST){
			$package = $_POST['package'];
			$map = array();
			$map['shield'] = $_POST['shield'];
			if($_POST['shield'] == 1){
				$start = strtotime($_POST['shield_start_time']);
				$end = strtotime($_POST['shield_end_time']);
				if($end < $start){
					$this -> error("开始时间不能大于结束时间");
				}
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$ad_error=$AdSearch->check_ad($package,$start,$end);
				// var_dump($ad_error);die;
				if($ad_error){
					$this->error($ad_error);
			   		 // exit("<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /> <script language='javascript'>alert(".$ad_error."); history.go(-1);</script>");
				}			
				$map['shield_start'] = $start;
				$map['shield_end'] = $end; 
			}else{
				$map['shield_start'] = 0;
				$map['shield_end'] = 0; 
			}
			$ret = $model -> where("package = '{$package}'")->save($map);
			if($_POST['shield'] == 1){
				$shield_content = $_POST['shield_content'];
				$content = '';
				foreach($shield_content as $v){
					$content .= $v.",";
				}
				$content = substr($content,0,-1);
				if($content == "其他"){
					$content = '';
				}
				if(!$content){
					exit("<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /> <script language='javascript'>alert('请填写屏蔽原因'); history.go(-1);</script>");
					$content_str = "为屏蔽原因：".$content;
				}
				$softmodel = D('Dev.Softlist');
				$softmodel -> reason_Operation("sj_soft_note",$package,$content,$_SESSION['admin']['admin_id']);
			}else{
				$content_str = "为正常";
			}
			$this->writelog("修改了包名【{$package}】的屏蔽状态{$content_str}",'sj_soft_note',$package,__ACTION__,'','edit');
			$this->assign("jumpUrl","/index.php/Dev/Soft/shield_soft_list");
			$this->success("操作成功");
		}else{
			$package = $_GET['package'];
			$list = $model -> where("package = '{$package}'")->find(); 
			$list['shield_start']  = date("Y-m-d H:i:s",$list['shield_start']);
			$list['shield_end']  = date("Y-m-d H:i:s",$list['shield_end']);
			//获取原因内容
			$soft_list = D('Dev.Softlist');
			$reason = $soft_list->get_reason_content('sj_soft_note',array($package));
			$content = $reason[$package]['content'];
			$content = explode(',',$content);
			$other = '';
			foreach($content as $v){
				if($v == "山寨风险" || $v == "盗版风险" )
				continue;
				$other .=  $v.",";
			}
			$other = substr($other,0,-1);
			if(!$other){
				$other = "其他";
			}
			$content = array_flip($content);
			$this -> assign('list',$list);
			$this -> assign('content',$content);
			$this -> assign('other',$other);
			$this->display();
		}
	}
	//角标状态显示
	function note_type(){
		$model = new Model();
		$time = time();
		$data = date("Y-m-d",$time);
		$type_start_time = $data." 00:00:00";
		$type_end_time = "2023-01-01 23:59:59";
		$this->assign("type_start_time",$type_start_time);
		$this->assign("type_end_time",$type_end_time);		
		$this->assign("feature_end_time",date("Y-m-d",strtotime('+1 year'))." 23:59:59" );		
		$sj_corner_mark = $model -> table('sj_corner_mark')->where("status=1")->field('id,name')->select();
		$this -> assign('sj_corner_mark',$sj_corner_mark);	
		$feature_id = C('feature_id');
		$where = array(
			'feature_id'=>$feature_id,
			'status' => 1,
			'start_tm' => array('exp',"<={$time}"),
			'end_tm' => array('exp',">={$time}"),
		);
		$feature_soft_list = $model->table('sj_feature_soft')->where($where)->count();		
		$this->assign('count',$feature_soft_list+1);
		$this->display();
	}
	//角标添加
	function soft_angle(){
		$note = M('soft_note');
		$soft = M('soft');
		$feature_soft_db = M('feature_soft');
		$type = $_POST['type'];
		$id_arr = explode(',',$_POST['id']);
		if($type > 0){
			$start= strtotime($_POST['start_tm']);
			$end = strtotime($_POST['end_tm']);
			if($end < $start){
				exit(json_encode(array('code'=>'0','msg'=>'结束时间不能大于开始时间！')));
			}
            if ($type == 1) {
                $new_version_feature = trim($_POST['new_version_feature']);
                if (!$new_version_feature) {
                    exit(json_encode(array('code'=>'0','msg'=>'一句话简介不能为空！')));
                }
                if (mb_strlen($new_version_feature, 'utf-8') > 20||mb_strlen($new_version_feature, 'utf-8') < 10) {
                    exit(json_encode(array('code'=>'0','msg'=>'一句话简介限10-20字以内！')));
                }
				if($_POST['add_feature'] == 1){
					$feature_start= strtotime($_POST['feature_start_time']);
					$feature_end = strtotime($_POST['feature_end_time']);
					if($feature_start > $feature_end){
						exit(json_encode(array('code'=>'0','msg'=>'专题结束时间不能大于开始时间！')));
					}
				}
            }
		}else{
			$start= 0;
			$end = 0;
		}	
		$where = array();
		$where['softid'] = array('in',$id_arr);
		$softlist = $soft -> where($where)->field('package')->select();
		$soft -> where($where)->save("last_refresh ='{$tm}'");
		foreach($softlist as $val){
			$package[] = $val['package'];
		}
		$map = array();
		$map['type_start'] = $start;
		$map['type_end'] = $end;
		$map['type'] = $type;
        if ($type == 1) {
            $map['new_version_feature'] = $new_version_feature;
        } else {
            $map['new_version_feature'] = '';//需清空这个字段
        }
		//	角标
		$where = array('status' => 1);
		$corner_mark = get_table_data($where,"sj_corner_mark","id","id,name");
		foreach($package as $v){
			$note_shield = $note -> where("package ='{$v}'")->find();		
			if($note_shield){
				$ret = $note -> where("package = '{$v}'")->save($map);
			}else{
				$map['package'] = $v;
				$ret = $note -> add($map);
			}
			//自动加入首发专题
			if($type ==1 && strpos($_POST['id'],',') === false && $_POST['add_feature']==1){
				$feature_list =  $feature_soft_db->where(array('package'=>$v,'feature_id'=> C('feature_id'),'status' => 1))->field('id')->find();
				$map = array(
					'feature_id' => C('feature_id'),
					'package' => $v,
					'status' => 1,
					'special' => 0,
					'rank' => (int)$_POST['rank'],
					'remark' => $new_version_feature,
					'begintime' => $_POST['feature_start_time'],
					'endtime' => $_POST['feature_end_time'],
					'from' => 1,
					'recommend_reason' => $_POST['recommend_reason'],
					'recommend_person' => $_POST['recommend_person'],
				);
				unset($_POST);
				if($feature_list){
					$map['id'] =  $feature_list['id'];
					foreach($map as $key => $val){
						$_POST[$key] = $val;
					}
					// 远程调用AdvertisementAction控制器的feature_soft_editit操作方法
					R("Sj.Advertisement","feature_soft_editit"); 
				}else{
					foreach($map as $key => $val){
						$_POST[$key] = $val;
					}
					// 远程调用AdvertisementAction控制器的feature_soft_upload操作方法
					R("Sj.Advertisement","feature_soft_upload"); 				
				}
				$str = ",并自动进入【首发专题】";
			}
			$this->writelog("包名【{$v}】的软件角标状态修改为【{$corner_mark[$type]['name']}】".$str,'sj_soft_note',$v,__ACTION__,'','edit');
		}
		exit(json_encode(array('code'=>'1','msg'=>$id_arr)));		
	}
	//山寨提醒
	function pub_soft_notice(){
		$result = array();
		$file_arr = array();
		foreach ($_POST['data'] as $package => $val) {
			$file_arr[] = $val[1];
		}
		if(!$file_arr){
			return false;
		}
		$model = new Model();
		$is_tmp = $_POST['is_tmp'];
		if ($is_tmp==0) {
			$table = 'sj_soft_fileicon';
			$where = array(
				'file_id' => array('IN', $file_arr)
			);
		} elseif($is_tmp==1) {
			$table = 'sj_soft_fileicon_tmp';
			$where = array(
				'file_tmp_id' => array('IN', $file_arr)
			);			
		}
		$res = $model->table($table)->where($where)->select();
		//var_dump(md5_file(UPLOAD_PATH . $res[0]['apk_icon']));
		foreach ($res as $val) {
			if ($is_tmp==0) {
				$id = $val['softid'];
			}else{
				$id = $val['tmp_id'];
			}
			list($counts,$softids,$tmp_ids) = get_copycat_num($_POST['data'][$id][0], $val['md5_icon'],$_POST['data'][$id][2]);
			if ($counts>=3) {
				if($softids){
					$softid_str = implode(',',$softids);										
				}
				if($tmp_ids){
					$tmpid_str = implode(',',$tmp_ids);
				}	
				$result[$id] = array($counts,$softid_str ? $softid_str : 0,$tmpid_str ? $tmpid_str :0 );
			}
		}
		exit(json_encode($result));
	}
	//运营提醒异步请求
	function pub_soft_oper(){
		$result = array();
		foreach ($_POST['data'] as $k => $v) {
			$result[$k] = getOperationWarning($v[0],$v[1],$v[2]);
		}
		//var_dump($result);
		exit(json_encode($result));
	}
	//上架列表导出
	function softlist_export(){
		$Export = D("Dev.Export");
		//分页		
		$p = isset($_GET['page']) ? $_GET['page'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 100;
		$data = $Export->getsoft_export(1,$_GET,$limit,$p);
		exit(json_encode($data));
	}
	function update_text(){
		$model = new Model();
		$package = $model -> table('sj_soft_note') -> where("shield =1")->field('package')->select();
		$i = 0;
		foreach($package as $v){
			$softlist = $model->table('sj_soft')->where("status=1 and (hide=1 or hide=3) and package='{$v['package']}'")->find();
			$tmplist = $model->table('sj_soft_tmp')->where("record_type<=3 and (status !=1 and status != 0) and package='{$v['package']}'")->find();
			if(empty($softlist) and empty($tmplist)){
				$map = array('shield'=>2);
				$model -> table('sj_soft_note') -> where("package ='{$v['package']}'")->save($map);
				//echo $model->getlastsql();exit;
			echo $i++;
			}
		}
	}
	//上架列表x86适配
	function x86_adaptation(){
		$model = D("Dev.Softaudit");
		$soft_db = M("soft");
		$softid = $_GET['softid'];
		if(empty($_GET['x86'])){
			exit(json_encode(array('code'=>'0','msg'=>'请选择适配！')));
		}
		$id_arr = explode(',',$softid);
		$where = array();
		$where['softid'] = array('in',$id_arr);
		$softlist = $soft_db -> where($where) ->field('softid,abi,dev_id,package,softname,min_firmware,max_firmware') -> select();
		foreach($softlist as $v){
			$model -> x86_check($_GET['x86'],$v['abi'],0,$v['softid'],"sj_soft",$v['package'],$v['softname']);
			$this->writelog("配置了软件id为{$v['softid']}的X86配置为{$_GET['x86']}",'sj_soft',$v['softid'],__ACTION__ ,"","edit");
			//检测abi兼容
			$data = array(
				'dev_id' => $v['dev_id'],
				'package' => $v['package'],
				'softid' =>$v['softid'],
				'abi' =>$v['abi'],
				'min_firmware' => $v['min_firmware'],
				'max_firmware' => $v['max_firmware']
			);
			$model -> abi_check($data);

		}
		exit(json_encode(array('code'=>'1','msg'=>'操作成功')));
	}
	//X86适配导出
	function x86_exp(){
		$Export = D("Dev.Export");
		//分页		
		$p = isset($_GET['page']) ? $_GET['page'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 100;
		$data = $Export->get_x86_export($_GET,$limit,$p);
		exit(json_encode($data));
	}
	//游戏内付费标识
	function game_charge(){
		$model = new Model();
		$id_arr = explode(',',$_GET['id']);
		$type = $_GET['charge'];
		if($type > 0){
			$start= strtotime($_GET['start_tm']);
			$end = strtotime($_GET['end_tm']);
			if($end < $start){
				exit(json_encode(array('code'=>'0','msg'=>'结束时间不能大于开始时间！')));
			}
		}else{
			$start= 0;
			$end = 0;
		}	

		$where = array();
		$where['softid'] = array('in',$id_arr);
		$softlist = $model->table('sj_soft') -> where($where)->field('package')->select();
		$model->table('sj_soft') -> where($where)->save("last_refresh ='{$tm}'");
		foreach($softlist as $val){
			$package[] = $val['package'];
		}
		unset($softlist);
		$map = array();
		$map['charge_start'] = $start;
		$map['charge_end'] = $end;
		$map['game_charge'] = $type;
		foreach($package as $v){
			$note_charge = $model->table('sj_soft_note') -> where("package ='{$v}'")->find();		
			if($note_charge){
				$model->table('sj_soft_note') -> where("package = '{$v}'")->save($map);
			}else{
				$map['package'] = $v;
				$model->table('sj_soft_note') -> add($map);
			}
			$this->writelog("包名【{$v}】的软件游戏内付费标识修改为{$type}",'sj_soft_note',$v,__ACTION__,'','edit');
		}
		exit(json_encode(array('code'=>'1','msg'=>$id_arr)));		
	}

        //小编点评
        function xb_comments()
        {
			$model = new Model();
			$model_corner_mark = M("corner_mark");
			$softid = $_GET['softid'];
			$softinfo = $model->table('sj_soft') -> where("softid='".$softid."'")->field('softname,package')->find();
            $package = $softinfo['package'];
            $softname = $softinfo['softname'];
			
			$where_tq=array(
				'status'=>1,
				'name'=>'特权',
			);
			$where_hb=array(
				'status'=>1,
				'name'=>'红包',
			);
            if($this->isPost())
            {
			    if($_POST['choose']==1)
				{
					$xb_text = $_POST['xb_text'];
					$package = $_POST['apk_package'];
					$type = $_POST['type'];
					$begin_tm = $_POST['begin_tm'];
					$end_tm = $_POST['end_tm'];
                    $data = array();
                    $data['package'] = $package;
                    $data['comment'] = $xb_text;
                    if($type==1)
                    {
                        $data['begin_tm'] = strtotime($begin_tm);
                        $data['end_tm'] = strtotime($end_tm);
                        if(strlen($begin_tm)==0||strlen($end_tm)==0){
                            echo 3;exit(0);
                        }
                        if(strtotime($begin_tm)>=strtotime($end_tm)){
                            echo 2;exit(0);
                        }
                    }
                    $data['update_time'] = time();
                    $data['azjx_type'] = $type;
                    $log_all_need = $this->logcheck(array('package' => $package), 'sj_soft_note', $data, $model);
                    $model->table('sj_soft_note')->where("package = '$package'")->save($data);
                    $msg = "编辑了包名为{$package}的小编点评,";
                    $msg .= $log_all_need;
                    $this->writelog($msg,'sj_soft_note',$package,__ACTION__,'','edit');
                    echo 1;exit(0);
				}
				if($_POST['choose']==2||$_POST['choose']==3||$_POST['choose']==4)
				{
					$pri_content = $_POST['pri_content'];
					$package = $_POST['apk_package'];
					$pri_choose = $_POST['pri_choose'];
					$red_choose = $_POST['red_choose'];
					$pri_begin_tm = $_POST['pri_begin_tm'];
					$pri_end_tm = $_POST['pri_end_tm'];
					$priority = $_POST['priority'];
					$choose = $_POST['choose'];
					$free_title=trim($_POST['free_title']);
					
                    $data = array();
                    $data['package'] = $package;
                    $data['new_content'] = $pri_content;
					$data['priority'] = $priority;
					
					if($choose==2)
					{
						$data['new_title']="下载享特权";
						if($pri_choose==1)
						{
							/*$model_config=M("pu_config");
							$res=$model_config->table("pu_config")->where("config_type='PRIVILEGE_RED_CORNER'")->find();
							$type=json_decode($res['configcontent'],true);
							$data['type']=$type['privilege'];*/
						
							$res=$model_corner_mark->where($where_tq)->find();
							$data['type']=$res['id'];
							$data['type_start'] =strtotime($pri_begin_tm);
							$data['type_end'] = strtotime($pri_end_tm);
						}
						else
						{
							$data['type']="";
							$data['type_start'] ="";
							$data['type_end'] = "";
						}
					}
					if($choose==3)
					{
						$data['new_title']="下载领红包";
						if($red_choose==1)
						{
							/*$model_config=M("pu_config");
							$res=$model_config->table("pu_config")->where("config_type='PRIVILEGE_RED_CORNER'")->find();
							$type=json_decode($res['configcontent'],true);
							$data['type']=$type['red_package'];*/
							
							$res=$model_corner_mark->where($where_hb)->find();
							$data['type']=$res['id'];
							$data['type_start'] =strtotime($pri_begin_tm);
							$data['type_end'] = strtotime($pri_end_tm);
						}
						else
						{
							$data['type']="";
							$data['type_start'] ="";
							$data['type_end'] = "";
						}
					}
					if($choose==4)
					{
						$data['new_title']=$free_title;
						$data['type']="";
						$data['type_start'] ="";
						$data['type_end'] = "";
					}
					$data['pri_red_begin'] = strtotime($pri_begin_tm);
					$data['pri_red_end'] = strtotime($pri_end_tm);
					
					if(strlen($pri_begin_tm)==0||strlen($pri_end_tm)==0){
						echo 3;exit(0);
					}
					if(strtotime($pri_begin_tm)>=strtotime($pri_end_tm)){
						echo 2;exit(0);
					}
                    
                    $data['update_time'] = time();
					
                    $log_all_need = $this->logcheck(array('package' => $package), 'sj_soft_note', $data, $model);
                    $result_save=$model->table('sj_soft_note')->where("package = '$package'")->save($data);
					if($choose==2)
					{
						$msg = "编辑了包名为{$package}的特权,";
					}
					if($choose==3)
					{
						$msg = "编辑了包名为{$package}的红包,";
					}
					if($choose==4)
					{
						$msg = "编辑了包名为{$package}的自定义,";
					}
                    $msg .= $log_all_need;
                    $this->writelog($msg,'sj_soft_note',$package,__ACTION__,'','edit');
                    echo 1;exit(0);
				}
            }

			$info = $model->table('sj_soft_note') -> where("package='".$package."'")->field('*')->find();
			
            if(empty($info['end_tm']))
            {
                $begin_tm = date('Y-m-d').' 00:00:00';
                //1年后
                $end_tm = date('Y-m-d',strtotime('+1 year')).' 23:59:59';
            }
			else
            {
                $begin_tm = date("Y-m-d H:i:s",$info['begin_tm']);
                $end_tm = date("Y-m-d H:i:s",$info['end_tm']);
            }
			if(empty($info['pri_red_end']))
			{
			    $new_begin_tm = date('Y-m-d').' 00:00:00';
			    //1月后
				$new_end_tm=date('Y-m-d',strtotime('+1 month')).' 23:59:59';
			}
			else
			{
			    $new_begin_tm = date("Y-m-d H:i:s",$info['pri_red_begin']);
                $new_end_tm = date("Y-m-d H:i:s",$info['pri_red_end']);
			} 
			if($info['priority']==999)
			{
				$priority_show="";
			}
			else
			{
				$priority_show=$info['priority'];
			}
			/*$model_config=M("pu_config");
			$res=$model_config->table("pu_config")->where("config_type='PRIVILEGE_RED_CORNER'")->find();
			 $this->assign('rs',json_decode($res['configcontent'],true));*/	 
			
			$res_hb=$model->table("sj_corner_mark")->where($where_hb)->find();
			$res_tq=$model->table("sj_corner_mark")->where($where_tq)->find();
			
			$this->assign('hb_id',$res_hb['id']);
			$this->assign('tq_id',$res_tq['id']);
            $this->assign('package',$package);
            $this->assign('info',$info);
            $this->assign('begin_tm',$begin_tm);
            $this->assign('end_tm',$end_tm);
            $this->assign('softname',$softname);
			$this->assign('new_begin_tm',$new_begin_tm);
			$this->assign('new_end_tm',$new_end_tm);
			$this->assign('priority_show',$priority_show);
            $this->display();
        }
        
        function pub_test(){

	
		$soft_tmp_db = M('soft_tmp');	
		$file_db = M('soft_file');	
		$fileicon_db = M('soft_fileicon');		
		$fileicon_tmp_db = M('soft_fileicon_tmp');	
		$thumb_db = M('soft_thumb');	
		$bookright_tmp_db = M('soft_bookright_tmp');	
		$bookright_db = M('soft_bookright');	
		$note_db = M('soft_note');				
		$error = '';
		$msg_str = '';
		$now = time();	
		$id_arr[] = 1412275;
		foreach ($id_arr as $k => $v) {
		    //更新soft表数据
			$tmplist = $soft_tmp_db->where("record_type=2 and id={$v}")->find();	
			
//如果是电子书通过
			$Tags = D('Sj.Tags'); 
			$book_categoryid = $this -> get_book_categoryid2();			
			if(in_array(substr($tmplist['category_id'],1,-1),$book_categoryid)){
			    $tags = $tmplist['tags'];
				$Tags -> del_dev_tag($tmplist['package']);
				$Tags -> add_package_tags($tmplist['package'],$tags);
			}else{
				$tag_list = $Tags -> get_tag($tmplist['package']);
				if($tag_list){
					$Tags -> save_tag_history($tmplist['package'],$tag_list[1],1);
					$Tags -> del_dev_tag($tmplist['package']);
					if(!empty($tag_list[1])){
						$custom_tags = $tag_list[1].",";
					}
					$Tags -> add_package_tags($tmplist['package'],$custom_tags.$tag_list[2].','.$tag_list[3]);				
				}
			}			
				
		}	
	
        }
        //获取电子书分类id
        public function get_book_categoryid2(){
            if(!S('book_categoryid')){
                $book = $this -> return_category2();
                $categoryid = array();
                foreach($book[2]['sub'] as $v){
                    $categoryid[] = $v['category_id'];
                }
                S('book_categoryid',$categoryid,300);
                $book_categoryid = $categoryid;
            }else{
                $book_categoryid = S('book_categoryid');
            }
            return $book_categoryid;
        }
        public function getCategoryArray2(){
            $Tags = D('Sj.Tags');
            $conf_list = $Tags->table('sj_category')->where("status=1 or status = 3")->field('category_id,name,orderid,parentid')->order('orderid')->select();
            $types = array();
            foreach($conf_list as $val){
                $category_id = $val['category_id'];
                $types[$category_id] = $val;
            }
            foreach($types as $key => $val){
                $parentid = $types[$key]['parentid'];
                $category_id = $val['category_id'];
                !isset($all_types[$parentid]) && $all_types[$parentid] = array();
                $all_types[$parentid][$category_id] = $val;
            }
            return $all_types;
        }
        public function return_category2(){
            if(!S('cname')){
                $cname = array();
                $catname = $this ->getCategoryArray2();
                foreach($catname[0] as $n){
                    $threecat = array();
                    foreach($catname[$n['category_id']] as $v){
                        foreach( $catname[$v['category_id']] as $m){
                            $threecat[] = $m;
                        }
                    }
                    $n['sub'] = $threecat;
                    $cname[] = $n;
                }
                S('cname',$cname,300);
            }else{
                $cname = S('cname');
            }
            return $cname;
        }

	public function pub_dev_info(){
		$package = $_POST['package'];
		if($package){
			$model = M('');
			$dev_id = $model->table('sj_soft')->where("package = '{$package}' and hide = 1 and status = 1 and dev_id != ''")->order('softid desc')->field('softid,dev_id')->find();
			if($dev_id){
				$devinfo = $model->table('pu_developer')->where("dev_id = {$dev_id['dev_id']}")->field('dev_id,dev_name,type,email,status')->find();
				echo  json_encode($devinfo);
				exit();
			}else{
				echo '';
				exit();
			}
		}
		echo '';
		exit();
	}
	public function sdk_access_list(){
		$model=M('sdk_official');
		$where=array();
		if(isset($_GET['softid'])){
			$this->assign('softid',$_GET['softid']);
			$where['softid']=trim($_GET['softid']);
		}else{
			$where['softid']=array('neq','');
		}
		if(isset($_GET['package'])){
			$this->assign('package',$_GET['package']);
			$where['package']=trim($_GET['package']);
		}
			
			
		//接入SDK类型
		if(isset($_GET['is_official'])){
			$this->assign('is_official',$_GET['is_official']);
			$where['is_official']=trim($_GET['is_official']);
		}

		//起止日期
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
			$where['create_tm']=array('exp',">={$begintime} && create_tm <= {$endtime}");
		}

		$count = $model->where($where)->count();
		// echo $model->getlastsql();
		$sdk_access_list = $model->where($where)->select();
		$softid=array();
		foreach($sdk_access_list as $k=>$v){
			$softid[]=$v['softid'];
		}
		$soft_list = get_table_data(array('softid' => array('in',$softid)),"sj_soft","softid","*");
		$category_id=array();
		$dev_id=array();
		foreach($sdk_access_list as $k=>$v){
			$sdk_access_list[$k]['softname']=$soft_list[$v['softid']]['softname'];
			$sdk_access_list[$k]['language']=$soft_list[$v['softid']]['language'];
			$sdk_access_list[$k]['version']=$soft_list[$v['softid']]['version'];
			$sdk_access_list[$k]['version_code']=$soft_list[$v['softid']]['version_code'];
			$sdk_access_list[$k]['dev_name']=$soft_list[$v['softid']]['dev_name'];
			$sdk_access_list[$k]['dever_email']=$soft_list[$v['softid']]['dever_email'];
			$sdk_access_list[$k]['category_id']=trim($soft_list[$v['softid']]['category_id'],',');
			$category_id[]=$sdk_access_list[$k]['category_id'];
			$sdk_access_list[$k]['dev_id']=$soft_list[$v['softid']]['dev_id'];
			$dev_id[]=$sdk_access_list[$k]['dev_id'];
		}
        $category_list = get_table_data(array('category_id' => array('in',$category_id)),"sj_category","category_id","category_id,name");
        
        $this->assign('category_list', $category_list);

        $dev_list = get_table_data(array('dev_id' => array('in',$dev_id)),"pu_developer","dev_id","dev_id,type,email");
        
        $this->assign('dev_list', $dev_list);

        $icon_list = get_table_data(array('softid' => array('in',$softid)),"sj_soft_file","softid","softid,iconurl_72");
        
        $this->assign('icon_list', $icon_list);
        import("@.ORG.Page2");
        $pg=$_GET['p']?$_GET['p']:1;
        $this->assign('pg', $pg);
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $this->assign('total', $count);
        $this->assign('sdk_access_list', $sdk_access_list);
        $this->assign('page', $Page->show());
        $this->display();
	}
}
?>
