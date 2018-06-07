<?php
header("Content-type: text/html; charset=utf-8");
ini_set("display_errors", 1);
error_reporting(1);

class AdSearchAction extends CommonAction {
	private $soft_model;
	private $co_type ;
	private $status;
	function __construct() {
		parent::__construct();
		$this->soft_model = M('soft');
	}
	function getSoftNameByPackage($package) {
		if (empty($package))
			return '';
		$map = array(
			'package' => $package,
			'status' => 1,
		);
		$res = $this->soft_model->where($map)->order('softid desc')->find();
		return $res['softname'];
	}

	function pub_get_package_adinfo(){
		$package = $_POST['package'];
		$res = $this->index(1,$package);

		$location = array();
		if(!$res) return '';
		foreach($res as $k=>$v){
			$location[] = $v['location'];
		}
		echo json_encode($location);
		exit();
	}

	function index($type='',$s_pack=''){
		ini_set('memory_limit','1024M');
		// 获得软件名称
		$name = isset($_GET['name']) ? $_GET['name'] : '';
		$this->assign("name",$name);
		// 获得软件包名称
		if($type == 1){
			$package = $s_pack;
		}else{
			$package = isset($_GET['package']) ? $_GET['package'] : '';
		}

        $package = trim($package);
		$this->assign("package",$package);
		// 获得开始时间
		$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : '';
		$fromdate1 = $fromdate?$fromdate:date('Y-m-d');
		$this->assign("so_start_tm",$fromdate1);
		// 获得结束时间
		$todate = isset($_GET['todate']) ? $_GET['todate'] : '';
		$todate1 = $todate?$todate:date('Y-m-d');
		$this->assign("so_end_tm",$todate1);
		// 获得负责人
		$fuzeren = isset($_GET['fuzeren']) ? $_GET['fuzeren'] : '';
		$this->assign("fuzeren",$fuzeren);
		// 状态
		$this->status = $status = isset($_GET['status']) ? $_GET['status'] : 1;
		$this->assign("status",$status);
		// 合作形式
		$this->co_type = isset($_GET['hezuo']) ? $_GET['hezuo'] : 0;
		//$this->assign("hezuo",$this->co_type);
		
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($this->co_type);
		$this->assign('hezuo',$typelist);
			
		if(!empty($fromdate) && !empty($todate) && (strtotime($fromdate) > strtotime($todate)))
			$this->error("开始时间不能大于结束时间");

		$adlist = array();
		//装机必备
		$necessary = $this->getnecessary($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($necessary))
			$adlist = array_merge($adlist, $necessary);

		//首页推荐
		$Recommend = $this->getRecommend($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($Recommend))
			$adlist = array_merge($adlist, $Recommend);
		
		//首页推荐（Feed版本）
		$RecommendV2 = $this->getRecommendV2($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($RecommendV2))
			$adlist = array_merge($adlist, $RecommendV2);
		//print_r($RecommendV2);die;
		//应用热门
//		$Application_hot = $this->getApplication_hot($name, $package, $fromdate,$todate,$fuzeren,$status);
//		if (!empty($Application_hot))
//			$adlist = array_merge($adlist, $Application_hot);

		//游戏热门
//		$Game_hot = $this->getGame_hot($name, $package, $fromdate,$todate,$fuzeren,$status);
//		if (!empty($Game_hot))
//			$adlist = array_merge($adlist, $Game_hot);

		//最新
//		$news = $this->getnews($name, $package, $fromdate,$todate,$fuzeren,$status);
//		if (!empty($news))
//			$adlist = array_merge($adlist, $news);

		//类别置顶
//		$Category = $this->getCategory($name, $package, $fromdate,$todate,$fuzeren,$status);
//		if (!empty($Category))
//			$adlist = array_merge($adlist, $Category);
		//必备
		$essential = $this->getessential($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($essential))
			$adlist = array_merge($adlist, $essential);
		// //猜你喜欢
		// $search_like = $this->getsearch_like($name, $package, $fromdate,$todate,$fuzeren,$status);
		// if (!empty($search_like))
		// 	$adlist = array_merge($adlist, $search_like);
		//搜索热词
		$search_hot = $this->getsearch_hot($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($search_hot))
			$adlist = array_merge($adlist, $search_hot);
		
		//搜索提示运营  getsearchtips
		$search_tips = $this->getsearchtips($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($search_tips))
			$adlist = array_merge($adlist, $search_tips);
		
		//搜索相关词  getsearchrelated
		$search_related = $this->getsearchrelated($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($search_related))
			$adlist = array_merge($adlist, $search_related);
		//=================================================================================
		//
		//下载设置
		$DownloadRec = $this->getDownloadRecSoft($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($DownloadRec)) $adlist = array_merge($adlist, $DownloadRec);
		//下载suggest推荐
		$SearchSuggest = $this->getSearchSuggest($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($SearchSuggest)) $adlist = array_merge($adlist, $SearchSuggest);
		//频道列表推荐
		$CEventSuggest = $this->getCEventSuggest($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($CEventSuggest)) $adlist = array_merge($adlist, $CEventSuggest);
		//一键装机运营
		$LoadSoft = $this->getLoadSoft($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($LoadSoft)) $adlist = array_merge($adlist, $LoadSoft);
		//新版轮播图
		$AdEventNew = $this->getAdEventNew($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($AdEventNew)) $adlist = array_merge($adlist, $AdEventNew);
		//闪屏管理
		$SplashSoft = $this->getSplashSoft($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($SplashSoft)) $adlist = array_merge($adlist, $SplashSoft);
		//动画广告
		$AnimationAd = $this->getAnimationAd($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($AnimationAd)) $adlist = array_merge($adlist, $AnimationAd);

		//灵活运营
		$FlexibleExtent = $this->getFlexibleExtent($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($FlexibleExtent)) $adlist = array_merge($adlist, $FlexibleExtent);
		
		//热搜词推荐
		$OlgameKeywords = $this->getOlgameKeywords($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($OlgameKeywords)) $adlist = array_merge($adlist, $OlgameKeywords);

		//文字链推广位
		$TextPageSoft = $this->getTextPageSoft($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($TextPageSoft)) $adlist = array_merge($adlist, $TextPageSoft);

		//返回运营
		$ReturnBackSoft = $this->getReturnBackSoft($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($ReturnBackSoft)) $adlist = array_merge($adlist, $ReturnBackSoft);

		//getSearchLikeNew
//		$SearchLikeNew = $this->getSearchLikeNew($name, $package, $fromdate,$todate,$fuzeren,$status);
//		if (!empty($SearchLikeNew)) $adlist = array_merge($adlist, $SearchLikeNew);

		//getPushSoft
		$getPushSoft = $this->getPushSoft($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($getPushSoft)) $adlist = array_merge($adlist, $getPushSoft);

		//getPushSoft 通知栏
		$getPushSoft1 = $this->getPushSoft1($name, $package, $fromdate,$todate,$fuzeren,$status,'4');
		if (!empty($getPushSoft1)) $adlist = array_merge($adlist, $getPushSoft1);

		//getActivitySoft 弹窗广告
		$getActivitySoft = $this->getActivitySoft($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($getActivitySoft)) $adlist = array_merge($adlist, $getActivitySoft);
//        $adlist = array();
        $getSearchLikeNew_T = $this->getSearchLikeNew_T($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($getSearchLikeNew_T)) $adlist = array_merge($adlist, $getSearchLikeNew_T);

		//自定义列表
		$getCustomList = $this->getCustomList($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($getCustomList)) $adlist = array_merge($adlist, $getCustomList);

		//市场首页软件列表v2
		$getExtentV2 = $this->getExtentV2($name, $package, $fromdate,$todate,$fuzeren,$status);
		if (!empty($getExtentV2)) $adlist = array_merge($adlist, $getExtentV2);

		$util = D("Sj.Util"); 
		foreach($adlist as $key=>$val) 
		{
			if($val['co_type']==0)
			{
				$adlist[$key]['hezuo'] = "--";
			}
			else
			{
				$typelist = $util->getHomeExtentSoftTypeList($val['co_type']);
				foreach($typelist as $k => $v){
					if($v[1] == true)
					{
						$adlist[$key]['hezuo'] = $v[0];
					}
				}
			}
		}
		// echo "<pre>";var_dump($adlist);die;
		if($type == 1){
			return $adlist;
		}else{
			$this->assign('list', $adlist);
			$this->display();
		}
	}
	//装机必备 --> 改成专题列表，将feature_id限制去掉
	function getnecessary($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		$feature_id = C('ZJBB_FEATURE_ID');
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("feature_soft");
		$now = time();
		$sql_where .= " b.status=1 ";
		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
//		$sql_where .= " and b.feature_id={$feature_id} ";
		if($status > 0){
			if($status == 1){
			  $sql_where .= " and b.end_tm > $now ";
			}elseif($status == 2){
				$sql_where .=" and b.start_tm > $now ";
			}else{
				$sql_where .= " and b.end_tm < $now";
			}
		}
		if(isset($package) && $package!=''){
			$sql_where .= " and b.package = '{$package}' ";
		}
		if(!empty($fromdate) && !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//			$sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		/*  $sql = "select a.softname,b.package,b.start_tm,b.end_tm,c.fuzeren from sj_feature_soft as b inner join sj_soft as a on a.package=b.package left join yx_product as c on a.package=c.package where {$sql_where}"; */
		$sql = "select b.id,b.package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.type,b.feature_id,d.name from sj_feature_soft as b left join sj_feature as d on b.feature_id=d.feature_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		// 遍历返回的结果，返回统一数据。
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
            if($v['start_tm'] > time()){
                $list_status ="3";
            }else{
                $list_status = ($v['end_tm'] < time())?'4':"2";
            }
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => '【专题】-'.$v['feature_id'].'-'.$v['name'],
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/Advertisement/feature_soft_list/id/{$v['feature_id']}/select_time/".$list_status,
                ///index.php/Sj/Advertisement/feature_soft_list/id/574/select_time/2/
				'pagelist' => '专题配置',
				'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
				'co_type'=>$v['type'],
				'id'=>$v['id'],
				'table'=>'sj_feature_soft'
			);
		}
		// 返回结果
		return $data;
	}
	//首页推荐
	function getRecommend($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$User = M("feature_soft");
		$now = time();
		$sql_where = '';
		$sql_where .= " b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " d.status=1 ";
		//$sql_where .= " and b.status=1 ";
		$sql_where .= " and d.type!=2 and d.parent_union_id=0 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}

		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
			//$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

		}
		if(isset($fuzeren)&& $fuzeren!=''){

			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_at,b.end_at,b.type,c.fuzeren,d.extent_name,d.extent_id,d.status,b.type from sj_extent_v1 as d inner join sj_extent_soft_v1 as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		// 遍历返回的结果，返回统一数据。
		foreach ($res as $z=>$v){
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【首页】-' . '['.$v['extent_name'].']',
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/ExtentV1/list_soft".$list_status."?extent_id=".$v['extent_id']."",
				'pagelist' => '市场首页软件列表',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['type'],
			    'id'=>$v['id'],
				'table'=>'sj_extent_soft_v1'
			);
		}
		return $data;
	}
	
	//首页推荐（Feed版本）
	function getRecommendV2($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$User = M("feature_soft");
		$now = time();
		$sql_where = '';
		$sql_where .= " b.status= 1 ";
	
		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " d.status=1 ";
		//$sql_where .= " and b.status=1 ";
		$sql_where .= " and d.type!=2 and d.parent_union_id=0 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_at,b.end_at,b.type,c.fuzeren,d.extent_name,d.extent_id,d.show_form,d.status,b.type from sj_extent_v2 as d inner join sj_extent_soft_v2 as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		// 遍历返回的结果，返回统一数据。
		foreach ($res as $z=>$v){
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
					'package' => $v['package'],
					'softname' => $softname,
					'location' => $v['location'] = '【首页】-' . '['.$v['extent_name'].']',
					'start_at' => $v['start_at'],
					'end_at' => $v['end_at'],
					'fuzeren' => $v['fuzeren'],
					'url' => "/index.php/Sj/ExtentV2/list_soft".$list_status."?extent_id=".$v['extent_id']."&show_form=".$v['show_form'],
					'pagelist' => '首页推荐（Feed版本）',
					'status' => $this->getStatus($v['start_at'],$v['end_at']),
					'co_type' => $v['type'],
					'id' => $v['id'],
					'table' =>'sj_extent_soft_v2'
			);
		}
		return $data;
	}
	//应用热门
	function getApplication_hot($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;

		$User = M("feature_soft");
		$now = time();
		$sql_where = '';
		$sql_where .= " b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " d.status=1 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}
		$sql_where .= " and b.status=1 ";
		$sql_where .= " and d.category_type='top_1_hot' and d.pid=1 ";

		if(isset($package)&& $package!=''){

			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
			//$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.package,b.start_at,b.type,b.end_at,c.fuzeren,d.extent_name,d.extent_id,d.status,b.type from sj_category_extent as d inner join sj_category_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【频道列表】-应用热门-'.$v['d.extent_id'].'-' . '['.$v['extent_name'].']',
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/CategoryExtent/list_soft".$list_status."?extent_id=".$v['extent_id']."",
				'pagelist' => '频道列表软件推荐',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['type']
			);
		}
		//return $data;
	}
	//游戏热门
	function getGame_hot($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;

		$User = M("feature_soft");
		$now = time();
		$sql_where = '';
		$sql_where .= " d.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " d.status=1 ";
		$sql_where .= " and b.status=1 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}
		$sql_where .= " and d.category_type='top_2_hot' and d.pid=1 ";

		if(isset($package)&& $package!=''){

			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
//            $sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.package,b.start_at,b.end_at,c.fuzeren,d.extent_name,d.extent_id,d.status,b.type from sj_category_extent as d inner join sj_category_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【频道列表】-游戏热门-' .$v['d.extent_id'].'-' . '['.$v['extent_name'].']',
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/CategoryExtent/list_soft$list_status?extent_id=".$v['extent_id']."",
				'pagelist' => '频道列表软件推荐',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['type']
			);
		}
		//return $data;
	}
	//最新
	function getnews($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;

		$User = M("product");
		$now = time();
		$sql_where = '';
		$sql_where .= " d.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " d.status=1 ";
		$sql_where .= " and b.status=1 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}
		$sql_where .= " and d.category_type='top_new' and d.pid=1 ";

		if(isset($package)&& $package!=''){

			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
//			$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

		}
		if(isset($fuzeren)&& $fuzeren!=''){

			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.package,b.start_at,b.end_at,c.fuzeren,d.extent_name,d.extent_id,d.status,b.type from sj_category_extent as d inner join sj_category_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【频道列表】-最新-' .$v['d.extent_id'].'-' . '['.$v['extent_name'].']',
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/CategoryExtent/list_soft$list_status?extent_id=".$v['extent_id']."",
				'pagelist' => '频道列表软件推荐',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['type']
			);
		}
		//return $data;
	}
	function getCategory($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("product");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "  b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_at > $now ";
			}else{
				$sql_where .= "  b.end_at < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}

		$sql_where .= " and b.status=1 ";
		$sql_where .= " and d.status=1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " and b.status=1 ";
		//$sql_where .= " and d.status=1 ";
		$sql_where .= " and d.category_type not in('top_new','top_2_hot','top_1_hot','fixed_user_also_download') and d.pid=1";
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){

			$this->timeLimit($sql_where,$fromdate,$todate,2);
//			$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

		}
		if (!empty($fuzeren)) {
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}

		$sql = "select b.package,b.start_at,b.end_at,c.fuzeren,d.extent_name,d.category_type,d.extent_id,b.status,b.type from sj_category_extent as d inner join sj_category_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		$category_types = $this->getCategoryTypes();
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【频道列表】-类别置顶-' .'['.$category_types[$v['category_type']].']'. '-['
					.$v['extent_name'].']',
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/CategoryExtent/list_soft$list_status?extent_id=".$v['extent_id']."",
				'pagelist' => '频道列表软件推荐',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['type']
			);
		}
		//return $data;
	}
	//软件必备
	function getessential($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;

		$User = M("necessary_extent");
		$now = time();
		$sql_where = '';
		$sql_where .= " d.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " d.status=1 " ;
		$sql_where .= " and d.type!=2 and b.status=1" ;
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
//			$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_at,b.end_at,c.fuzeren,d.extent_name,d.extent_id,d.status,b.type  from sj_necessary_extent as d inner join sj_necessary_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
            if($v['start_at'] > time()){
                $list_status ="/srch_type/f";
            }else{
                $list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
            }
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【必备】-' . '['.$v['extent_name'].']',
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/NecessaryExtent/list_soft$list_status?extent_id=".$v['extent_id']."",
				'pagelist' => '必备频道软件推荐',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['type'],
			    'id'=>$v['id'],
				'table'=>'sj_necessary_extent_soft'
			);
		}
		// echo "<pre>";var_dump($data);die;
		return $data;
	}
	//猜你喜欢
	function getsearch_like($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("soft_recommend");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "  b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}

		$sql_where .= " and b.status= 1 ";
		//$sql_where .= " and b.status=1 ";
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.soft_package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){

			$this->timeLimit($sql_where,$fromdate,$todate,1);
//			$sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

		}
		if(isset($fuzeren)&& $fuzeren!=''){

			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.soft_package,b.start_tm,b.end_tm,c.fuzeren,b.status from sj_soft_recommend as b left join yx_product as c on b.soft_package=c.package and c.del = 0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['soft_package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			$data[] = array(
				'package' => $v['soft_package'],
				'softname' => $softname,
				'location' => $v['location'] = '猜你喜欢',
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => '/index.php/Sj/SoftRecommed/soft_recommend_list',
				'pagelist' => '软件推荐设置',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
			    'id'=>$v['id'],
				'table'=>'sj_soft_recommend'
			);
		}
		return $data;
	}
	//搜索热词
	function getsearch_hot($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;

		$User = M("search_key");
		$now = time();
		$sql_where = '';
		$sql_where .= " b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		//$sql_where .= " d.status=1 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and  b.stop_tm > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_tm > $now ";
			}else{
				$sql_where .= " and b.stop_tm < $now";
			}
		}
		$sql_where .= " and b.status=1" ;

		if(isset($package)&& $package!=''){

			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,4);
//            $sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.stop_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){

			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
			$this->assign("fuzeren",$fuzeren);
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id as bid,b.package,b.start_tm,b.stop_tm,c.fuzeren,d.srh_key,d.id,b.status,b.co_type,b.type from sj_search_key as d inner join sj_search_key_package as b on d.id=b.kid left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		// echo $User->getLastSql();
		// echo "<pre>";var_dump($res);die;
		$category_types = $this->getCategoryTypes();
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
				if ($v['type'] == 1){
					$v['package'] = $category_types[$v['package']];
				}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【搜索关键字列表】-'. '['.$v['srh_key'].']',
				'start_at' => $v['start_tm'],
				'end_at' => $v['stop_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/Search_weight/search_key_package_add?id=".$v['id']."",
				'pagelist' => '搜索结果运营',
			    'status' =>$this->getStatus($v['start_tm'],$v['stop_tm']),
			    'co_type' =>$v['co_type'],
			    'id'=>$v['bid'],
				'table'=>'sj_search_key_package'
			);
		}
		// echo "<pre>";var_dump($data);die;
		return $data;
	}

	//猜你喜欢
	function getSearchLikeNew($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("category_extent_soft");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_at > $now ";
			}else{
				$sql_where .= "  b.end_at < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " and b.status=1 ";
		$sql_where .= " and d.category_type = 'fixed_user_also_download' and d.pid=1";
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//			$sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

		}
		if(isset($fuzeren)&& $fuzeren!=''){

			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.package,b.start_at,b.end_at,c.fuzeren,b.status,d.extent_name,d.extent_id,b.type from sj_category_extent as d inner join sj_category_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【频道列表】-猜你喜欢-'.$v['extent_name'],
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/CategoryExtent/list_soft$list_status/extent_id/".$v['extent_id'],
				'pagelist' => '频道列表软件推荐',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
				'co_type'=>$v['type']
			);
		}
//		return $data;
	}
    function getSearchLikeNew_T($name, $package, $fromdate,$todate,$fuzeren,$status){
        if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
            return false;
        $sql_where = '';
        $User = M("category_extent");
        $now = time();
        if($status > 0){
            if($status == 1){
                $sql_where .= "   b.end_at > $now ";
            }elseif($status == 3){
                $sql_where .= "  b.start_at > $now ";
            }else{
                $sql_where .= "  b.end_at < $now";
            }
        }else{
            $sql_where = ' 1 ';
        }
        $sql_where .= " and b.status= 1 ";

        if($this->co_type){
            $sql_where .= " and b.type = ".$this->co_type;
        }
        //$sql_where .= " and b.status=1 ";and d.category_type = 'fixed_user_also_download'
        $sql_where .= "  and d.pid=1";
        if(isset($package)&& $package!=''){
            $sql_where .= " and b.package = '{$package}'";
        }
        if(!empty($fromdate)&& !empty($todate)){
            $this->timeLimit($sql_where,$fromdate,$todate,2);
//			$sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

        }
        if(isset($fuzeren)&& $fuzeren!=''){

            $sql_where .= " and c.fuzeren = '{$fuzeren}'";
        }else{
            $sql_where .= "";
        }
        $sql = "select b.id,b.package,b.start_at,b.end_at,c.fuzeren,b.status,d.extent_name,d.extent_id,b.type,d.category_type from sj_category_extent as d inner join sj_category_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
        $res = $User->query($sql);
        foreach ($res as $v)
        {
            $softname = $this->getSoftNameByPackage($v['package']);
            if (!empty($name) && stripos($softname, $name) === false)
                continue;
            if($v['start_at'] > time()){
                $list_status ="/srch_type/f";
            }else{
                $list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
            }
            $data[] = array(
                'package' => $v['package'],
                'softname' => $softname,
                'location' => $v['location'] = '【频道列表】-'.$this->getFlexibleType($v['category_type']).'-'.$this->getExtentCateName($v['category_type']).'-'.$v['extent_name'],
                'start_at' => $v['start_at'],
                'end_at' => $v['end_at'],
                'fuzeren' => $v['fuzeren'],
                'url' => "/index.php/Sj/CategoryExtent/list_soft$list_status/extent_id/".$v['extent_id'],
                'pagelist' => '频道列表软件推荐',//频道列表软件推荐1com.fly.switcherwechat
                'status' =>$this->getStatus($v['start_at'],$v['end_at']),
                'co_type'=>$v['type'],
                'id'=>$v['id'],
				'table'=>'sj_category_extent_soft'
            );
        }
        return $data;
    }

	//sj_download_recommend_soft
	//下载推荐
	function getDownloadRecSoft($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("download_recommend_soft");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		$sql_where .= " and b.package !=''  ";

		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
//				$sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
			$this->timeLimit($sql_where,$fromdate,$todate,1);
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.recommend_id,b.type,d.package as dpackage  from sj_download_recommend as d left join sj_download_recommend_soft as b on d.id=b.recommend_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		foreach ($res as $v){
			$softName = $this->getSoftNameByPackage($v['package']);
            $softName2 = $this->getSoftNameByPackage($v['dpackage']);
				if (!empty($name) && stripos($softName, $name) === false)
					continue;
			if($v['start_tm'] > time()){
				$list_status = 1;
			}else{
				$list_status = ($v['end_tm'] < time())?2:3;
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softName,
				'location' => $v['location'] = '【下载推荐】-'.$softName2,
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/Downloadrecommend/soft_list/my_time/".$list_status."/recommend_id/".$v['recommend_id'],
				'pagelist' => '下载推荐',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
			    'co_type'=>$v['type'],
			    'id'=>$v['id'],
				'table'=>'sj_download_recommend_soft'
			);
		}
		return $data;
	}
	//搜索suggest设置
	function getSearchSuggest($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("think_words");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_time > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_time > $now ";
			}else{
				$sql_where .= "  b.end_time < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		$sql_where .= " and b.package !='' ";
		//$sql_where .= " and b.status=1 ";
		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){

			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,3);
//			$sql_where .=" and b.start_time <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_time>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_time,b.end_time,c.fuzeren,b.status,b.search_words,b.type from sj_think_words as b  left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_time asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【搜索suggest】-'.$v['search_words'],
				'start_at' => $v['start_time'],
				'end_at' => $v['end_time'],
				'fuzeren' => $v['fuzeren'],
				'url' => ($v['end_time'] > time())?'/index.php/Sj/Searchthinkword/search_keyword_manage':'/index.php/Sj/Searchthinkword/searchkeywords_out_show',
				'pagelist' => '搜索suggest设置',
			    'status' =>$this->getStatus($v['start_time'],$v['end_time']),
			    'co_type'=>$v['type'],
			    'id'=>$v['id'],
				'table'=>'sj_think_words'
			);
		}
		return $data;
	}
	//频道列表软件推荐
	function getCEventSuggest($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("category_extent");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_at > $now ";
			}else{
				$sql_where .= "  b.end_at < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		$sql_where .= " and b.package !='' and d.pid =1  ";
		//$sql_where .= " and b.status=1 ";
		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
//			$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_at,b.end_at,c.fuzeren,b.status,d.extent_id,d.extent_name,b.type,d.extent_type,d.display_title from sj_category_extent as d inner join sj_category_extent_soft as b on d.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v){
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			if($v['start_at'] > time()){
				$list_status ="/srch_type/f";
			}else{
				$list_status = ($v['end_at'] < time())?'/srch_type/e':"/srch_type/n";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【频道列表】-'.$v['extent_name'],
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/CategoryExtent/list_soft/time_status/".($list_status)."extent_id/{$v['extent_id']}/p/1",
				'pagelist' => '频道列表软件推荐',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['type'],
			    'id'=>$v['id'],
				'table'=>'sj_category_extent_soft'
			);
		}
//		return $data;
	}
	//sj_lading_soft
	//一键装机运营
	function getLoadSoft($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("lading_soft");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		$sql_where .= " and b.package !=''   ";
		//$sql_where .= " and b.status=1 ";
		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//			$sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.type,b.softname,b.recommend,b.category_id from sj_lading_soft as b  left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			if($v['start_tm'] > time()){
				$list_status = 3;
			}else{
				$list_status = ($v['end_tm'] < time())?2:1;
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【一键装机】-'.$this->getLoadSoftCateName($v['category_id']),
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
				'url' => "/index.php/Sj/Ladingmanage/show_soft_list/time_status/".$list_status."/id/".$v['category_id'],
				'pagelist' => '一键装机运营',
			    'co_type'=>$v['type'],
			    'id'=>$v['id'],
				'table'=>'sj_lading_soft'
			);
		}
		return $data;
	}
	//新版轮播图
	//sj_ad_extent
	function getAdEventNew($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("ad_extent");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";
		//$sql_where .= " and b.status=1 ";
		$sql_where .= " and b.package != '' ";
		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//            $sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.co_type,b.ad_name,b.extent_id from sj_ad_new as b left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			if($v['start_tm'] > time()){
				$list_status = 3;
			}else{
				$list_status = ($v['end_tm'] < time())?1:2;
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【轮播图】-'.$this->getAdExtent($v['extent_id']),
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/Adextent/ad_list/extent_id/{$v['extent_id']}/my_time/".$list_status,
				'pagelist' => '新版轮播图',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
			    'co_type'=>$v['co_type'],
			    'id'=>$v['id'],
				'table'=>'sj_ad_new'
			);
		}
		return $data;
	}
	//sj_splash_manage
	//闪屏管理
	function getSplashSoft($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("splash_manage");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		$sql_where .= " and b.package !=''   ";
		$sql_where .= " and b.content_type = 1 ";
		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//            $sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.co_type,b.splash_name from sj_splash_manage as b left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			if($v['start_tm'] > time()){
				$list_status = 1;
			}else{
				$list_status = ($v['end_tm'] < time())?2:3;
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【闪屏】-'.$v['id'].'-'.$v['splash_name'],
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/Splashmanage/splash_list/my_time/".$list_status,
				'pagelist' => '闪屏管理',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
			    'co_type'=>$v['co_type'],
			    'id'=>$v['id'],
				'table'=>'sj_splash_manage'
			);
		}
		return $data;
	}
	//sj_animation_ad
	//动画广告
	function getAnimationAd($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("animation_ad");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_at > $now ";
			}else{
				$sql_where .= "  b.end_at < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		$sql_where .= " and b.package !='' ";
		$sql_where .= " and b.content_type = 1 ";
		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
//			$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_at,b.end_at,c.fuzeren,b.status,b.co_type,b.ad_name,b.image_type from sj_animation_ad as b left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【'.(($v['image_type']==1)?'动画广告':'图片广告').'】'.'-'.$v['id'].'-'.$v['ad_name'],
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/AnimationAd/index/overdue/".(($v['end_at'] < time())?1:-1),
				'pagelist' => '悬浮窗管理',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['co_type'],
			    'id'=>$v['id'],
				'table'=>'sj_animation_ad'
			);
		}
		return $data;
	}
	// sj_flexible_extent
	//灵活运营
	function getFlexibleExtent($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("flexible_extent");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= " b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " b.start_at > $now ";
			}else{
				$sql_where .= " b.end_at < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and a.status = 1 and b.status= 1 ";

		//$sql_where .= " and b.package !=''  ";
		//$sql_where .= " and b.content_type = 1 ";
		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and (b.package = '{$package}' or b.package_643 = '{$package}') ";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
//			$sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			//$sql_where .= " and c.fuzeren = '{$fuzeren}'";
			$product_list = M('')->table('yx_product')->where(array('fuzeren'=>trim($fuzeren),'del'=>0))->field("package")->select();
			if(!empty($product_list)) {
				$product_list = array_unique($product_list);
				foreach ($product_list as $val) {
					$sql_where .= " and (b.package = '{$val['package']}' or b.package_643 = '{$val['package']}') ";
				}	
			}else {
				$sql_where = " and (b.package='' and b.package_643='') ";
			}
		}else{
			$sql_where .= "";
		}
		//$sql = "select b.id,b.package,b.start_at,b.end_at,c.fuzeren,b.status,b.co_type,d.extent_name,d.extent_id,d.belong_page_type,d.extent_type from sj_flexible_extent as d inner join sj_flexible_extent_soft as b on b.extent_id = d.extent_id left join yx_product as c on  b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc limit 500";
		$sql = "SELECT b.id,b.package,b.package_643,b.start_at,b.end_at,b.status,b.co_type,a.extent_name,a.extent_id,a.belong_page_type,a.extent_type FROM sj_flexible_extent as a INNER JOIN sj_flexible_extent_soft as b on  a.extent_id = b.extent_id and b.extent_id !=0 where a.extent_type in (5,16,17,24,25,26,27,28,29,30,31) and {$sql_where} ORDER BY b.start_at asc limit 500";
		//echo $sql;die;
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			if($v['package_643']) {
				$softname = $this->getSoftNameByPackage($v['package_643']);
				$product = M('')->table('yx_product')->where(array('package'=>$v['package_643'],'del'=>0))->find();
				$fuzeren = isset($product['fuzeren'])?$product['fuzeren']:'';
			}else {
				$softname = $this->getSoftNameByPackage($v['package']);
				$product = M('')->table('yx_product')->where(array('package'=>$v['package'],'del'=>0))->find();
				$fuzeren = isset($product['fuzeren'])?$product['fuzeren']:'';
			}
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			if($v['start_at'] > time()){
				$list_status = 3;
			}else{
				$list_status = ($v['end_at'] < time())?1:2;
			}
			$data[] = array(
				'package' => $v['package_643']?$v['package_643']:$v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【灵活运营样式】-'.$this->getFlexibleType($v['belong_page_type']).'-'.ContentTypeModel::convertPageType2PageNameOfFlexible($v['belong_page_type']).'-'.$v['extent_name'],
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $fuzeren,
				'url' => "/index.php/Sj/FlexibleExtent/list_soft/extent_id/{$v['extent_id']}/period/".$list_status."/",
				'pagelist' => '灵活运营样式',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['co_type'],
			    'id'=>$v['id'],
				'table'=>'sj_flexible_extent_soft'
			);
		}
		return $data;
	}
	//sj_search_keywords
	//搜索热词推荐
	function getOlgameKeywords($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("olgame_keywords");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		//$sql_where .= " and b.content_type = 1 ";
		$sql_where .= " and b.package  != '' ";

		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//            $sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.type,b.key_word from sj_search_keywords as b left join yx_product as c on  b.package=c.package and c.del = 0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【搜索热词】-'.$v['key_word'],
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => ($v['end_tm'] > time())?"/index.php/Search/Advertisement/searchkeywords_list_hot":"/index.php/Search/Advertisement/stale_searchkeywords_out_show",
				'pagelist' => '搜索热词推荐',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
			    'co_type'=>$v['type'],
			    'id'=>$v['id'],
				'table'=>'sj_search_keywords'
			);
		}
		return $data;
	}
	//文字链推广位
	//sj_text_page
	function getTextPageSoft($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("text_page");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_time > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_time > $now ";
			}else{
				$sql_where .= "  b.end_time < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		$sql_where .= " and b.package !='' ";
		$sql_where .= " and b.content_type = 1 ";
		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,3);
//            $sql_where .=" and b.start_time <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_time>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_time,b.end_time,c.fuzeren,b.status,b.co_type,b.description from sj_text_page as b  left join yx_product as c on  b.package=c.package and c.del = 0 where {$sql_where} order by b.start_time asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			if($v['start_time'] > time()){
				$list_status = '#';
			}else{
				$list_status = ($v['end_time'] < time())?"/index.php/Sj/Textpage/textpagelist/overdue/1/":"/index.php/Sj/Textpage/textpagelist";
			}
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【文字链】-'.$v['id'].'-'.$v['description'],
				'start_at' => $v['start_time'],
				'end_at' => $v['end_time'],
				'fuzeren' => $v['fuzeren'],
				'url' => $list_status,
				'pagelist' => '文字链推广位',
			    'status' =>$this->getStatus($v['start_time'],$v['end_time']),
			    'co_type'=>$v['co_type'],
			    'id'=>$v['id'],
				'table'=>'sj_text_page'
			);
		}
		return $data;
	}
	//返回运营
	//sj_return_back_ad
	function getReturnBackSoft($name, $package, $fromdate,$todate,$fuzeren,$status){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$sql_where = '';
		$User = M("return_back_ad");
		$now = time();
		if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_at > $now ";
			}else{
				$sql_where .= "  b.end_at < $now";
			}
		}else{
		    $sql_where = ' 1 ';
		}
		$sql_where .= " and b.status= 1 ";

		$sql_where .= " and b.package !=''  ";
		$sql_where .= " and b.content_type = 1 ";
		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
//            $sql_where .=" and b.start_at <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_at>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_at,b.end_at,c.fuzeren,b.status,b.co_type,b.ad_name from sj_return_back_ad as b  left join yx_product as c on  b.package=c.package and c.del = 0 where {$sql_where} order by b.start_at asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【返回运营】-'.$v['id'].'-'.$v['ad_name'],
				'start_at' => $v['start_at'],
				'end_at' => $v['end_at'],
				'fuzeren' => $v['fuzeren'],
				'url' => ($v['end_at'] < time())?"/index.php/Sj/ReturnBackAd/index/overdue/1/":"/index.php/Sj/ReturnBackAd/index",
				'pagelist' => '返回运营',
			    'status' =>$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'=>$v['co_type'],
			    'id'=>$v['id'],
				'table'=>'sj_return_back_ad'
			);
		}
		return $data;
	}
	//push
	//sj_market_push
	function getPushSoft($name, $package, $fromdate,$todate,$fuzeren,$status,$type='3'){
	    if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
	        return false;
        $sql_where = '';
	    $User = M("market_push");
	    $now = time();
	    if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
	    }else{
		    $sql_where = ' 1 ';
		}
	    $sql_where .= " and b.status= 1 and b.push_type = 1 ";

	    $sql_where .= " and b.soft_package !=''  ";
	    $sql_where .= " and b.info_type = ". $type;
	    if($this->co_type){
	        $sql_where .= " and b.co_type = ".$this->co_type;
	    }
	    if(isset($package)&& $package!=''){
	        $sql_where .= " and b.soft_package = '{$package}'";
	    }
	    if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//			$sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

	    }
	    if(isset($fuzeren)&& $fuzeren!=''){
	        $sql_where .= " and c.fuzeren = '{$fuzeren}'";
	    }else{
	        $sql_where .= "";
	    }
	    $sql = "select b.id,b.soft_package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.co_type,b.info_title from sj_market_push as b  left join yx_product as c on  b.soft_package=c.package and c.del = 0 where {$sql_where} order by b.start_tm asc";
	    $res = $User->query($sql);
        $data =  $softname = array();
	    foreach ($res as $v)
	    {
	        $softname = $this->getSoftNameByPackage($v['soft_package']);
	        if (!empty($name) && stripos($softname, $name) === false)
	            continue;
			if($v['start_tm'] > time()){
				$list_status ="3";
			}else{
				$list_status = ($v['end_tm'] < time())?'2':"1";
			}
	        $data[] = array(
	            'package' => $v['soft_package'],
	            'softname' => $softname,
	            'location' => $v['location'] = "【PUSH】-".$v['id']."-".$v['info_title'],
	            'start_at' => $v['start_tm'],
	            'end_at' => $v['end_tm'],
	            'fuzeren' => $v['fuzeren'],
	            'url' => " /index.php/Webmarket/marketPush/market_push_list/zh_type/".$list_status,
	            'pagelist' => '市场手机---PUSH',
	            'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
	            'co_type'=>$v['co_type'],
	            'id'=>$v['id'],
				'table'=>'sj_market_push'
	        );
	    }
	    return $data;
	}
	//sj_market_push
	function getPushSoft1($name, $package, $fromdate,$todate,$fuzeren,$status,$type='3'){
	    if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
	        return false;
        $sql_where = '';
	    $User = M("market_push");
	    $now = time();
	    if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
	    }else{
	        $sql_where = ' 1 ';
	    }
	    $sql_where .= " and b.status= 1 ";

	    $sql_where .= " and b.soft_package !=''  ";
	    $sql_where .= " and b.notice_type = 1 and b.push_type=2";
	    if($this->co_type){
	        $sql_where .= " and b.co_type = ".$this->co_type;
	    }
	    if(isset($package)&& $package!=''){
	        $sql_where .= " and b.soft_package = '{$package}'";
	    }
	    if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//	        $sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));

	    }
	    if(isset($fuzeren)&& $fuzeren!=''){
	        $sql_where .= " and c.fuzeren = '{$fuzeren}'";
	    }else{
	        $sql_where .= "";
	    }
	    $sql = "select b.id,b.soft_package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.co_type,b.cpm_name from sj_market_push as b  left join yx_product as c on  b.soft_package=c.package and c.del = 0 where {$sql_where} order by b.start_tm asc";
	    $res = $User->query($sql);
	    foreach ($res as $v)
	    {
	        $softname = $this->getSoftNameByPackage($v['soft_package']);
	        if (!empty($name) && stripos($softname, $name) === false)
	            continue;
			if($v['start_at'] > time()){
				$list_status ="3";
			}else{
				$list_status = ($v['end_at'] < time())?'1':"2";
			}
	        $data[] = array(
	            'package' => $v['soft_package'],
	            'softname' => $softname,
	            'location' => $v['location'] = '弹窗名称-'.$v['cpm_name'],
	            'start_at' => $v['start_tm'],
	            'end_at' => $v['end_tm'],
	            'fuzeren' => $v['fuzeren'],
	            'url' => "/index.php/Webmarket/marketPush/market_cpm_list/push_type/".$list_status,
	            'pagelist' => '市场手机-弹窗---PUSH',
	            'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
	            'co_type'=>$v['co_type'],
	            'id'=>$v['id'],
				'table'=>'sj_market_push'
	        );
	    }
	    return $data;
	}
	//活动分区
	function getActivitySoft($name, $package, $fromdate,$todate,$fuzeren,$status){
	    if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
	        return false;
        $sql_where = '';
	    $User = M("activity");
	    $now = time();
	    if($status > 0){
			if($status == 1){
				$sql_where .= "   b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= "  b.start_tm > $now ";
			}else{
				$sql_where .= "  b.end_tm < $now";
			}
	    }else{
	        $sql_where = ' 1 ';
	    }
	    $sql_where .= " and b.status= 1 ";

	    $sql_where .= " and b.package !=''  ";
	    //$sql_where .= " and b.content_type = 1 ";
	    if($this->co_type){
	        $sql_where .= " and b.co_type = ".$this->co_type;
	    }
	    if(isset($package)&& $package!=''){
	        $sql_where .= " and b.package = '{$package}'";
	    }
	    if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
//	        $sql_where .=" and b.start_tm <=".strtotime(date('Ymd 23:59:59',strtotime($todate)))." and b.end_tm>=".strtotime(date('Ymd 00:00:00',strtotime($fromdate)));
	    }
	    if(isset($fuzeren)&& $fuzeren!=''){
	        $sql_where .= " and c.fuzeren = '{$fuzeren}'";
	    }else{
	        $sql_where .= "";
	    }
	    $sql_where .= " limit 500 ";
	    $sql = "select b.id,b.package,b.start_tm,b.end_tm,c.fuzeren,b.status,b.co_type,b.name from sj_activity as b left join yx_product as c on  b.package=c.package and c.del = 0 where {$sql_where} order by b.start_tm asc";
	    $res = $User->query($sql);
	    foreach ($res as $v)
	    {
	        $softname = $this->getSoftNameByPackage($v['package']);
	        if (!empty($name) && stripos($softname, $name) === false)
	            continue;
	        $data[] = array(
	            'package' => $v['package'],
	            'softname' => $softname,
	            'location' => $v['location'] = '【活动】-'.$v['id'].'-'.$v['name'],
	            'start_at' => $v['start_tm'],
	            'end_at' => $v['end_tm'],
	            'fuzeren' => $v['fuzeren'],
	            'url' => "/index.php/Sj/Activity/showActivityList",
	            'pagelist' => '活动分区',
	            'status' => $this->getStatus($v['start_tm'],$v['end_tm']),
	            'co_type'=>$v['co_type'],
	            'id'=>$v['id'],
				'table'=>'sj_activity'
	        );
	    }
	    return $data;
	}

    function getCategoryTypes() {
		$map = array(
			//'' => '全部',
			'top_new' => '最新',
			'top_hot' => '最热',
			'top_1d' => '日排行',
			'top_1d_1' => '应用日排行',
			'top_1d_2' => '游戏日排行',
			'top_7d' => '周排行',
			'top_30d' => '月排行',
			'olgame_down_5' => '下载最多',
			'olgame_hot_5' => '网游精选'
		);

		$category = D('Sj.Category');
		$category_list = $category->getCategoryArray();
		foreach ($category_list as $v) {
			$map['top_' . $v['category_id'] . '_new'] = $v['name'] . '-最新';
			$map['top_' . $v['category_id'] . '_hot'] = $v['name'] . '-最热';
		}
		return $map;
	}
	public function getStatus($start,$end){
		$now = time();
		if($start > $now){
			return  '即将投放';
		}else{
			if($end < $now){
				return  '已过期';
			}else{
				return '正在投放';
			}
		}
	}
	public function  timeLimit(&$sql_where,$from,$end,$type=1){
		$status = $this->status;
		$start_field = $end_field = '';
		if($type == 1){
			$start_field = 'b.start_tm';
			$end_field = 'b.end_tm';
		}elseif($type ==2 ){
			$start_field = 'b.start_at';
			$end_field = 'b.end_at';
		}elseif($type ==3){
			$start_field = 'b.start_time';
			$end_field = 'b.end_time';
		}elseif($type ==4){
            $start_field = 'b.start_tm';
            $end_field = 'b.stop_tm';
        }
		if($status == 3){
			$sql_where .=" and $start_field <=".strtotime(date('Ymd 23:59:59',strtotime($end)))." and $start_field >=".strtotime(date('Ymd 00:00:00',strtotime($from)));
		}else{
			$sql_where .=" and $start_field <=".strtotime(date('Ymd 23:59:59',strtotime($end)))." and $end_field >=".strtotime(date('Ymd 00:00:00',strtotime($from)));
		}
	}
    public function getLoadSoftCateName($cateId){
        $mod =  M('category_name');
        $cateInfo  = $mod->query("select category_name from sj_lading_category where id='".$cateId."' and status=1 limit 1");
        if($cateInfo[0]){
            return $cateInfo[0]['category_name'];
        }else{
            return '未获取到分类';
        }
    }
    public function getAdExtent($id){
        $mod = M('sj_ad_extent');
        $extentInfo = $mod->query("select extent_name from sj_ad_extent where extent_id ='".$id."' and status =1 limit 1");
        if($extentInfo[0]){
            return $extentInfo[0]['extent_name'];
        }else{
            return '未获取到分类';
        }
    }
    public function getFlexibleType($belongPage){
//        $normal = array('top');
        $page_arr = explode('_',$belongPage);
        $normal_arr = array(
            'top',
            'olgame',
            'fixed',
            'otherfixed');
        $tag_arr = array( 'tag');
        $commontag_arr = array('commontag');
        $normalList_arr = array('customlist');
        $bdlist_arr = array('bdlist');
        $gift_arr = array('gift');
        $strategy_arr = array('strategy');
        if(in_array($page_arr[0],$normal_arr)){
            return '普通';
        }elseif(in_array($page_arr[0],$tag_arr)){
            return '标签';
        }elseif(in_array($page_arr[0],$commontag_arr)){
            return '常用标签';
        }elseif(in_array($page_arr[0],$normalList_arr)){
            return '自定义列表';
        }elseif(in_array($page_arr[0],$bdlist_arr)){
            return '榜单';
        }elseif(in_array($page_arr[0],$gift_arr)){
            return '礼包';
        }elseif(in_array($page_arr[0],$strategy_arr)){
            return '攻略';
        }
    }
    public function getExtentCateName($page){
        $category_list = ContentTypeModel::getCategoryTypesOfCategory();
        $bd_list = ContentTypeModel::getbdList();
        //$arr = ContentTypeModel::convertCommonTagPageName2CommonTagPageType('常用工具-最热');

        if(isset($category_list[$page]) && $page){
            return  $category_list[$page];
        }elseif(isset($bd_list[$page]) && $page){
            return $bd_list[$page];
        }elseif(strpos($page,'tag_') !== false){
            $tag_arr = explode('_',$page);
            if(count($tag_arr) ==3){
                return $this->getTagName($tag_arr[1],$tag_arr[2]);
            }else{
                return $this->getTagName($tag_arr[2],$tag_arr[3]);
            }
        }else{
            return '全部';
        }
    }
    public function getTagName($tag_id,$types){
        $mod = M('sj_tag');
        $tag_tmp = $mod->query("select tag_name from sj_tag where tag_id='".$tag_id."' limit 1");
        $tag_info = $tag_tmp[0];
        if($tag_info){
            $type = ($types =='new')?'最新':'最热';
            return $tag_info['tag_name'].'-'.$type;
        }
    }
	//搜索提示运营
	function getsearchtips($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;

		$User = M("search_tips");
		$now = time();
		$sql_where = '';
		$sql_where .= " b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if($status > 0){
			if($status == 1){
				$sql_where .= " and  b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_tm > $now ";
			}else{
				$sql_where .= " and b.end_tm < $now";
			}
		}

		if(isset($package)&& $package!=''){

			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
		}
		if(isset($fuzeren)&& $fuzeren!=''){

			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
			$this->assign("fuzeren",$fuzeren);
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id as bid,b.package,b.start_tm,b.end_tm,c.fuzeren,d.srh_key,d.id,b.status,b.co_type from sj_search_tips as d inner join sj_search_tips_package as b on d.id=b.kid left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
				
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【搜索提示】-'. '['.$v['srh_key'].']',
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/Search_tips/search_tips_package_add?id=".$v['id']."",
				'pagelist' => '搜索提示运营',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
			    'co_type' =>$v['co_type'],
			    'id'=>$v['bid'],
				'table'=>'sj_search_tips_package'
			);
		}
		return $data;
	}
	//搜索相关词
	function getsearchrelated($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;

		$User = M("search_related");
		$now = time();
		$sql_where = '';
		$sql_where .= " b.status= 1 ";

		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if($status > 0){
			if($status == 1){
				$sql_where .= " and  b.end_tm > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_tm > $now ";
			}else{
				$sql_where .= " and b.end_tm < $now";
			}
		}

		if(isset($package)&& $package!=''){

			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,1);
		}
		if(isset($fuzeren)&& $fuzeren!=''){

			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
			$this->assign("fuzeren",$fuzeren);
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id as bid,b.package,b.start_tm,b.end_tm,c.fuzeren,d.srh_key,d.id,b.status,b.co_type from sj_search_related as d inner join sj_search_related_content as b on d.id=b.kid left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_tm asc";
		$res = $User->query($sql);
		//搜索相关词  选择软件详情页 填写包名 选择搜索结果页，包名字段为空
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
				if (!empty($name) && stripos($softname, $name) === false)
					continue;
				
			$data[] = array(
				'package' => $v['package'],
				'softname' => $softname,
				'location' => $v['location'] = '【搜索相关词】-'. '['.$v['srh_key'].']',
				'start_at' => $v['start_tm'],
				'end_at' => $v['end_tm'],
				'fuzeren' => $v['fuzeren'],
				'url' => "/index.php/Sj/Search_related/search_related_content_add?id=".$v['id']."",
				'pagelist' => '搜索相关词管理',
			    'status' =>$this->getStatus($v['start_tm'],$v['end_tm']),
			    'co_type' =>$v['co_type'],
			    'id'=>$v['bid'],
				'table'=>'sj_search_related_content'
			);
		}
		return $data;
	}

	//自定义列表
	function getCustomList($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$now = time();
		$sql_where = '';
		$sql_where .= " a.status= 1 and a.channel_id != 27 and b.status=1";

		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_time > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_time > $now ";
			}else{
				$sql_where .= " and b.end_time < $now";
			}
		}

		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,3);

		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_time,b.end_time,c.fuzeren,a.name,a.status from sj_custom_list_name as a inner join sj_custom_list_name_soft as b on a.id=b.name_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_time asc";
		$res = M('')->query($sql);
		// 遍历返回的结果，返回统一数据。
		foreach ($res as $z=>$v){
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			$data[] = array(
				'package' 	=> 	$v['package'],
				'softname' 	=> 	$softname,
				'location' 	=> 	$v['location'] = '【自定义列表】-' . '['.$v['name'].']',
				'start_at' 	=> 	$v['start_time'],
				'end_at' 	=> 	$v['end_time'],
				'fuzeren' 	=> 	$v['fuzeren'],
				'url' 		=> 	"/index.php/Sj/CustomList/index/search_name/".$v['name'],
				'pagelist' 	=> 	'自定义列表',
			    'status' 	=>	$this->getStatus($v['start_time'],$v['end_time']),
			    'co_type'	=>	0,
			    'id'		=>	$v['id'],
				'table'		=>	'sj_custom_list_name_soft'
			);
		}
		return $data;
	}

	//市场首页软件列表v2
	function getExtentV2($name, $package, $fromdate,$todate,$fuzeren,$status=0){
		if (empty($name) && empty($package) && empty($fromdate) && empty($todate) && empty($fuzeren))
			return false;
		$now = time();
		// 第一部分
		$sql_where = '';
		$sql_where .= " b.status= 1 ";
	
		if($this->co_type){
			$sql_where .= " and b.type = ".$this->co_type;
		}
		$sql_where .= " and a.status=1 and a.type !=3 and a.extent_type !=4 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and b.package = '{$package}'";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$sql_where .= " and c.fuzeren = '{$fuzeren}'";
		}else{
			$sql_where .= "";
		}
		$sql = "select b.id,b.package,b.start_at,b.end_at,b.type,c.fuzeren,a.extent_name from sj_extent_v2 as a inner join sj_extent_soft_v2 as b on a.extent_id=b.extent_id left join yx_product as c on b.package=c.package and c.del=0 where {$sql_where} order by b.start_at asc";
		$res = M('')->query($sql);
		$data = array();
		// 遍历返回的结果，返回统一数据。
		foreach ($res as $z=>$v){
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			$data[] = array(
					'package' 	=> 	$v['package'],
					'softname' 	=> 	$softname,
					'location' 	=> 	$v['location'] = '市场首页软件列表v2-' . '['.$v['extent_name'].']',
					'start_at' 	=> 	$v['start_at'],
					'end_at' 	=> 	$v['end_at'],
					'fuzeren' 	=> 	$v['fuzeren'],
					'url' 		=> 	"/index.php/Sj/ExtentV2/index",
					'pagelist' 	=> 	'市场首页软件列表v2',
					'status' 	=> 	$this->getStatus($v['start_at'],$v['end_at']),
					'co_type' 	=> 	$v['type'],
					'id' 		=> 	$v['id'],
					'table' 	=>	'sj_extent_soft_v2'
			);
		}

		// 第二部分
		$sql_where = '';
		$sql_where .= " a.status = 1  and a.belong_page_type='fixed_homepage_recommend' and b.status= 1 ";
		if($status > 0){
			if($status == 1){
				$sql_where .= " and b.end_at > $now ";
			}elseif($status == 3){
				$sql_where .= " and b.start_at > $now ";
			}else{
				$sql_where .= " and b.end_at < $now";
			}
		}

		if($this->co_type){
			$sql_where .= " and b.co_type = ".$this->co_type;
		}
		if(isset($package)&& $package!=''){
			$sql_where .= " and (b.package = '{$package}' or b.package_643 = '{$package}') ";
		}
		if(!empty($fromdate)&& !empty($todate)){
			$this->timeLimit($sql_where,$fromdate,$todate,2);
		}
		if(isset($fuzeren)&& $fuzeren!=''){
			$product_list = M('')->table('yx_product')->where(array('fuzeren'=>trim($fuzeren),'del'=>0))->field("package")->select();
			if(!empty($product_list)) {
				$product_list = array_unique($product_list);
				foreach ($product_list as $val) {
					$sql_where .= " and (b.package = '{$val['package']}' or b.package_643 = '{$val['package']}') ";
				}	
			}else {
				$sql_where = " and (b.package='' and b.package_643='') ";
			}
		}else{
			$sql_where .= "";
		}
		$sql = "SELECT b.id,b.package,b.package_643,b.start_at,b.end_at,b.co_type,a.extent_id,a.extent_name FROM sj_flexible_extent as a INNER JOIN sj_flexible_extent_soft as b on  a.extent_id = b.extent_id and b.extent_id !=0 where {$sql_where} ORDER BY b.start_at asc limit 500";
		$res = M('')->query($sql);
		foreach ($res as $v){
			if($v['package_643']) {
				$softname = $this->getSoftNameByPackage($v['package_643']);
				$product = M('')->table('yx_product')->where(array('package'=>$v['package_643'],'del'=>0))->find();
				$fuzeren = isset($product['fuzeren'])?$product['fuzeren']:'';
			}else {
				$softname = $this->getSoftNameByPackage($v['package']);
				$product = M('')->table('yx_product')->where(array('package'=>$v['package'],'del'=>0))->find();
				$fuzeren = isset($product['fuzeren'])?$product['fuzeren']:'';
			}
			if (!empty($name) && stripos($softname, $name) === false)
				continue;
			if($v['start_at'] > time()){
				$list_status = 3;
			}else{
				$list_status = ($v['end_at'] < time())?1:2;
			}
			$data[] = array(
				'package' 	=>	$v['package_643']?$v['package_643']:$v['package'],
				'softname' 	=> 	$softname,
				'location' 	=> 	$v['location'] = '市场首页软件列表v2-' . '['.$v['extent_name'].']',
				'start_at' 	=> 	$v['start_at'],
				'end_at' 	=> 	$v['end_at'],
				'fuzeren' 	=> 	$fuzeren,
				'url' 		=> 	"/index.php/Sj/FlexibleExtent/list_soft/extent_id/{$v['extent_id']}/period/".$list_status."/",
				'pagelist' 	=> 	'市场首页软件列表v2',
			    'status' 	=>	$this->getStatus($v['start_at'],$v['end_at']),
			    'co_type'	=>	$v['co_type'],
			    'id'		=>	$v['id'],
				'table'		=>	'sj_flexible_extent_soft'
			);
		}

		return $data;
	}

	function edit_all()
	{
		if(isset($_GET['biaoshi'])){
			$this->assign('location1', $_GET['location1']);
			$tblist=explode("&",$_GET['tblist']);
			$this->assign('tblist', $tblist[0]);
			$this->assign('idlist', $_GET['idlist']);
			$num= count(explode(",",trim($_GET['idlist'],',')));
			$this->assign('num', $num);
			$this->assign('action', $_GET['biaoshi']);
			$this->display();
			return;
		}
		if(empty($_POST['action']))
		{
			$this->ajaxReturn('', '错误的请求', -1);
		}
		else
		{
			$action = $_POST['action'];
		}

		switch ($action)
		{
			case "1":
				// $status = 2;
				$actionname = '编辑';
				break;

			case "2":
				// $status = 0;
				$actionname = '复制上线';
				break;

			default:
				$this->ajaxReturn('', '错误的请求', -1);
		}
		if (empty($_POST['idlist']))
		{
			$this->ajaxReturn('', '请选择操作对象', -1);
		}
		else
		{
			$idlist = $_POST['idlist'];
		}
		$tblist = !empty($_POST['tblist']) ? $_POST['tblist'] : '';
		$tblist = explode(',',trim($tblist,','));
		
		$model = M();
		$idstr = explode(',',substr ( $idlist ,  0 , - 1 )); 

		// echo "<pre>";var_dump($idstr);die;
		$start_at = $_POST['start_at']?strtotime($_POST['start_at']):'';
		// if(isset($_POST['end_at']) || isset($_POST['end_tm'])){
			$end_at = $_POST['end_tm']?time():strtotime($_POST['end_at']);
		// }
		$error_arr=array();
		$AdSearch = D("Sj.AdSearch");
		$table_config=array(
                "sj_download_recommend_soft"=>array("sj_download_recommend_soft",'start_tm','end_tm','下载推荐','package'),
                "sj_think_words"=> array("sj_think_words",'start_time','end_time',"搜索suggest设置",'package'),
                "sj_animation_ad"=>array("sj_animation_ad",'start_at','end_at',"悬浮窗管理",'package'),
                "sj_feature_soft"=>array("sj_feature_soft",'start_tm','end_tm',"专题配置",'package'),
                "sj_activity"=>array("sj_activity",'start_tm','end_tm',"活动分区",'package'),
                "sj_extent_soft_v1"=>array("sj_extent_soft_v1",'start_at','end_at',"市场首页软件列表",'package'),
				"sj_extent_soft_v2"=>array("sj_extent_soft_v2",'start_at','end_at',"首页推荐（Feed版本）",'package'),
                "sj_category_extent_soft"=> array("sj_category_extent_soft",'start_at','end_at',"频道列表软件推荐",'package'),
                // "7"=>array("sj_soft_recommend",'start_tm','end_tm',"软件推荐设置",'soft_package'),
                "sj_lading_soft"=>array("sj_lading_soft",'start_tm','end_tm',"一键装机运营",'package'),
                "sj_ad_new"=>array("sj_ad_new",'start_tm','end_tm',"新版轮播图",'package'),
                "sj_splash_manage"=> array("sj_splash_manage",'start_tm','end_tm',"闪屏管理",'package'),
                "sj_flexible_extent_soft"=>array("sj_flexible_extent_soft",'start_at','end_at',"灵活运营样式",'package'),
                "sj_search_keywords"=>array("sj_search_keywords",'start_tm','end_tm',"搜索热词推荐",'package'),
                "sj_text_page"=> array("sj_text_page",'start_time','end_time',"文字链推广位",'package'),
                "sj_return_back_ad"=> array("sj_return_back_ad",'start_at','end_at',"返回运营",'package'),
                "sj_necessary_extent_soft"=>array("sj_necessary_extent_soft",'start_at','end_at',"必备频道软件推荐",'package'),
                "sj_search_key_package"=>array("sj_search_key_package",'start_tm','stop_tm',"搜索结果运营",'package'),
                "sj_search_tips_package"=>array("sj_search_tips_package",'start_tm','end_tm',"搜索提示运营",'package'),
                "sj_market_push"=>array("sj_market_push",'start_tm','end_tm',"市场手机---PUSH",'soft_package'),
                "sj_soft_associate_hot_word"=>array("sj_soft_associate_hot_word",'begin','end',"搜索阿拉丁",'package'),
                "sj_search_related_content"=>array("sj_search_related_content",'start_tm','end_tm',"搜索相关词管理",'package'),
            );
		if($action==1){
			foreach($tblist as $k=>$v){				
				$table=$v;
				$id=$idstr[$k];
				$update_at = time();
				if($table=='sj_return_back_ad' || $table=='sj_flexible_extent_soft' || $table=='sj_animation_ad' || $table=='sj_necessary_extent_soft' || $table=='sj_category_extent_soft' || $table=='sj_extent_soft_v1'){
					$id_key='id';
					$set_str="update_at=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_at={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_at={$end_at}";
					}
					$result=$model->table($table)->where(array($id_key=>$id))->find();
					$result['start_at_sr']=$result['start_at'];
					$result['end_at_sr']=$result['end_at'];
					if(!empty($start_at)){
						$result['start_at']=$start_at;
					}
					if(!empty($end_at)){
						$result['end_at']=$end_at;
					}
					if($result['start_at']>$result['end_at']){
						$result['message']="开始时间不能大于结束时间";
						$error_arr[]=$result;
						continue;
					}
					$shield_error=$AdSearch->check_shield($result['package'],$result['start_at'],$result['end_at']);
					if($table=='sj_extent_soft_v1'){
						$shield_error.=$AdSearch->check_shield_old($result['package'],0,1);
					}else if($table=='sj_extent_soft_v2'){
						$shield_error.=$AdSearch->check_shield_old($result['package'],0,1);
					}else if($table=='sj_category_extent_soft'){
						$shield_error.=$AdSearch->check_shield_old($result['package'],$result['extent_id'],0,'sj_category_extent');
					}else if($table=='sj_flexible_extent_soft'){
						$shield_error.=$AdSearch->check_shield_old($result['package'],$result['extent_id'],0,'sj_flexible_extent');
					}
					if($shield_error){
						$result['message']=$shield_error;
						$error_arr[]=$result;
						continue;
					}
					switch ($table)
					{
						case "sj_necessary_extent_soft":
							$error_mess=$AdSearch->logic_check_ness($result);
							if($error_mess!=2){
									$result['message']=$error_mess['message'];
									$error_arr[]=$result;
									continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //不需要复制上线
							break;

						case "sj_return_back_ad":
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); 
							if(!$re){
								$result['message']='编辑失败';
								$error_arr[]=$result;
							}
							//复制上线和编辑，不需要校验
							break;
						case "sj_flexible_extent_soft":
							// $error_mess=$AdSearch->check_conflict_FlexibleExtent($result,$result['id']);
							$error_mess=$AdSearch->check_conflict_FlexibleExtent($result,$result['id']);
							// var_dump($error_mess);die;
							if($error_mess!=0){
								$result['message']="本条数据与id为".explode(',',$error_mess).'排期冲突';
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //传$result['id']为编辑，不传为复制上线
							break;
						case "sj_animation_ad":
							$error_mess=$AdSearch->check_conflict_AnimationAd($result,$result['id']);
							// var_dump($error_mess);die;
							if($error_mess!=0){
								$result['message']="本条数据与id为".$error_mess.'排期冲突';
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //传$result['id']为编辑，不传为复制上线
							break;
						case "sj_category_extent_soft":
				            $error_mess=$AdSearch->logic_check_CategoryExtent($result);
							// var_dump($error_mess);die;
							if($error_mess!=2){
									$result['message']=$error_mess['message'];
									$error_arr[]=$result;
									continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							break;
						case "sj_extent_soft_v1":
							$error_mess=$AdSearch->logic_check_ExtentV1($result);
							
							if($error_mess!=2){
									$result['message']=$error_mess['message'];
									$error_arr[]=$result;
									continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							break;
						case "sj_extent_soft_v2":
							$error_mess=$AdSearch->logic_check_ExtentV1($result);
								
							if($error_mess!=2){
								$result['message']=$error_mess['message'];
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							break;
						default:
							$this->ajaxReturn('', '错误的请求', -1);
					}
				}else if($table=='sj_search_key_package'){
					$id_key='id';
					$set_str="update_tm=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",stop_tm={$end_at}";
					}
					// if(!empty($start_at)){
					// 	$set_str="start_tm={$start_at},stop_tm={$end_at},update_tm={$update_at}";
					// }else{
					// 	$set_str="stop_tm={$end_at},update_tm={$update_at}";
					// }
					$result=$model->table($table)->where(array($id_key=>$id))->find();
					$result['start_tm_sr']=$result['start_tm'];
					$result['stop_tm_sr']=$result['stop_tm'];
					if(!empty($start_at)){
						$result['start_tm']=$start_at;
					}
					if(!empty($end_at)){
						$result['stop_tm']=$end_at;
					}
					if($result['start_tm']>$result['stop_tm']){
						$result['message']="开始时间不能大于结束时间";
						$error_arr[]=$result;
						continue;
					}
					$shield_error=$AdSearch->check_shield($result['package'],$result['start_tm'],$result['stop_tm'],1);
					if($shield_error){
						$result['message']=$shield_error;
						$error_arr[]=$result;
						continue;
					}
					$error_mess=$AdSearch->logic_check_Search_weight($result);
					// var_dump($error_mess);die;
					if($error_mess!=2){
						$result['message']=$error_mess['message'];
						$error_arr[]=$result;
						continue;
					}
					$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
					$re = $model->execute($sql); //不需要复制上线
				}else if($table=='sj_market_push' || $table=='sj_download_recommend_soft' || $table=='sj_ad_new' || $table=='sj_splash_manage' || $table=='sj_soft_recommend' || $table=='sj_lading_soft' || $table=='sj_search_tips_package'  || $table=='sj_search_keywords' || $table=='sj_search_related_content'){
					
					$id_key='id';
					$set_str="update_tm=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_tm={$end_at}";
					}
					// if(!empty($start_at)){
					// 	$set_str="start_tm={$start_at},end_tm={$end_at},update_tm={$update_at}";
					// }else{
					// 	$set_str="end_tm={$end_at},update_tm={$update_at}";
					// }
					$result=$model->table($table)->where(array($id_key=>$id))->find();
					$result['start_tm_sr']=$result['start_tm'];
					$result['end_tm_sr']=$result['end_tm'];
					if(!empty($start_at)){
						$result['start_tm']=$start_at;
					}
					if(!empty($end_at)){
						$result['end_tm']=$end_at;
					}
					if($result['start_tm']>$result['end_tm']){
						$result['message']="开始时间不能大于结束时间";
						if($table=='sj_soft_recommend' || $table=='sj_market_push'){
							$result['package']=$result['soft_package'];
						}
						$error_arr[]=$result;
						// var_dump($error_arr);die;
						continue;
					}
					if($table=='sj_market_push' || $table=='sj_soft_recommend'){
						$shield_error=$AdSearch->check_shield($result['soft_package'],$result['start_tm'],$result['end_tm']);
						if($shield_error){
							$result['package']=$result['soft_package'];
							$result['message']=$shield_error;
							$error_arr[]=$result;
							continue;
						}
					}else{
						if($table=='sj_search_tips_package'  || $table=='sj_search_keywords' || $table=='sj_search_related_content'){
							$shield_error=$AdSearch->check_shield($result['package'],$result['start_tm'],$result['end_tm'],1);
							if($shield_error){
								$result['message']=$shield_error;
								$error_arr[]=$result;
								continue;
							}
						}else{
							$shield_error=$AdSearch->check_shield($result['package'],$result['start_tm'],$result['end_tm']);
							if($shield_error){
								$result['message']=$shield_error;
								$error_arr[]=$result;
								continue;
							}
						}
					}
					
					switch ($table)
					{
						case "sj_lading_soft":
							// }else if($table=='sj_market_push' || $table=='sj_download_recommend_soft' || $table=='sj_ad_new' || $table=='sj_splash_manage' || $table=='sj_soft_recommend' $table=='sj_search_tips_package'  || $table=='sj_search_keywords'){
							$error_mess=$AdSearch->logic_check_lading($result);
							// var_dump($error_mess);die;
							if($error_mess!=2){
									$result['message']=$error_mess['message'];
									$error_arr[]=$result;
									continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //不需要复制上线
							break;

						case "sj_market_push":
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //不需要复制上线,确定，不需要限制条件
							if(!$re){
								$result['message']='编辑失败';
								$error_arr[]=$result;
							}
							//复制上线和编辑，不需要校验
							break;
						case "sj_search_tips_package":
						//确定无复制上线
							$error_mess=$AdSearch->logic_check_Search_tips($result);
							// var_dump($error_mess);die;
							if($error_mess!=2){
								$result['message']=$error_mess['message'];
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //传$result['id']为编辑，不传为复制上线
							break;
						case "sj_search_related_content":
						//确定无复制上线
							$error_mess=$AdSearch->logic_check_related_batch($result);
							if($error_mess!=2){
								$result['message']=$error_mess['message'];
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //传$result['id']为编辑，不传为复制上线
							break;
						case "sj_download_recommend_soft":
							$error_mess=$AdSearch->logic_check_download($result);
							if($error_mess!=2){
								$result['message']=$error_mess['message'];
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //传$result['id']为编辑，不传为复制上线
							break;
						case "sj_ad_new":
				            $error_mess=$AdSearch->logic_check_adextent($result);
							// var_dump($error_mess);die;
							if($error_mess!=2){
									$result['message']=$error_mess['message'];
									$error_arr[]=$result;
									continue;
							}

							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							break;
						case "sj_splash_manage":
							//无需校验
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							if(!$re){
								$result['message']='编辑失败';
								$error_arr[]=$result;
							}
							break;
						case "sj_search_keywords":
				            $error_mess=$AdSearch->logic_check_Search_words($result);
							// var_dump($error_mess);die;
							if($error_mess!=2){
									$result['message']=$error_mess['message'];
									$error_arr[]=$result;
									continue;
							}

							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							break;
						case "sj_soft_recommend":
							//无复制上线
				            $error_mess=$AdSearch->logic_check_softrecomend($result);
							if($error_mess!=2){
									$result['message']=$error_mess['message'];
									$result['package']=$result['soft_package'];
									$error_arr[]=$result;
									continue;
							}

							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							break;
						default:
							$this->ajaxReturn('', '错误的请求', -1);
					}					
				}else if($table=='sj_think_words' || $table=='sj_text_page'){
					$id_key='id';
					$set_str="up_time=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_time={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_time={$end_at}";
					}
					// if(!empty($start_at)){
					// 	$set_str="start_time={$start_at},end_time={$end_at},up_time={$update_at}";
					// }else{
					// 	$set_str="end_time={$end_at},up_time={$update_at}";
					// }
					$result=$model->table($table)->where(array($id_key=>$id))->find();
					$result['start_time_sr']=$result['start_time'];
					$result['end_time_sr']=$result['end_time'];
					if(!empty($start_at)){
						$result['start_time']=$start_at;
					}
					if(!empty($end_at)){
						$result['end_time']=$end_at;
					}
					if($result['start_time']>$result['end_time']){
						$result['message']="开始时间不能大于结束时间";
						$error_arr[]=$result;
						continue;
					}
					if($table=='sj_think_words'){
						$shield_error=$AdSearch->check_shield($result['package'],$result['start_time'],$result['end_time'],1);
						if($shield_error){
							$result['message']=$shield_error;
							$error_arr[]=$result;
							continue;
						}
					}else{
						$shield_error=$AdSearch->check_shield($result['package'],$result['start_time'],$result['end_time']);
						if($shield_error){
							$result['message']=$shield_error;
							$error_arr[]=$result;
							continue;
						}
					}
					
					switch ($table)
					{
						case "sj_text_page":
							$error_mess=$AdSearch->check_conflict_textpage($result,$result['id']);
							// var_dump($error_mess);die;
							if($error_mess!=0){
								$result['message']="本条数据与id为".$error_mess.'排期冲突';
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //传$result['id']为编辑，不传为复制上线
							break;
						case "sj_think_words":
							// if($_POST['life']==1)
						 //    {
							//   $sel = $model->table("sj_think_words")->where("((start_time <=$end_time and end_time>=$start_time) or (start_time <=$start_time and end_time>=$end_time)) and soft_rank=$soft_rank and status =1")->select();
							// }
							// else
							// {
							$end_time=$result['end_time'];
							$start_time=$result['start_time'];
							$soft_rank=$result['soft_rank'];
							$id_tw=$result['id'];
							$search_words=$result['search_words'];
							 $sel = $model->table("sj_think_words")->where("((start_time <=$end_time and end_time>=$start_time) or (start_time <=$start_time and end_time>=$end_time)) and soft_rank=$soft_rank and status =1 and id !=$id_tw")->select();
							// }
							$new_str = array();
							foreach($sel as $k=>$v){
								$new_str[] =  $v['search_words'];
								
							}
							$a_arr = implode(',',$new_str);
							$newarr = explode(',',$a_arr);
							$arr=explode(',',$search_words);
							$res = array_intersect($newarr,$arr);
							unset($new_str);
							unset($sel);
							// $error_mess=$AdSearch->check_conflict_AnimationAd($result,$result['id']);
							if($res){
								$result['message']="当前排期中您填写的搜索词有冲突,冲突词为".implode(',',$res);
								$error_arr[]=$result;
								continue;
							}
							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //传$result['id']为编辑，不传为复制上线
							break;
						default:
							$this->ajaxReturn('', '错误的请求', -1);
					}
				}else if($table=='sj_activity'){
					$id_key='id';
					$set_str="last_refresh=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_tm={$end_at}";
					}
					// if(!empty($start_at)){
					// 	$set_str="start_tm={$start_at},end_tm={$end_at},last_refresh={$update_at}";
					// }else{
					// 	$set_str="end_tm={$end_at},last_refresh={$update_at}";
					// }
					$result=$model->table($table)->where(array($id_key=>$id))->find();
					$result['start_tm_sr']=$result['start_tm'];
					$result['end_tm_sr']=$result['end_tm'];
					if(!empty($start_at)){
						$result['start_tm']=$start_at;
					}
					if(!empty($end_at)){
						$result['end_tm']=$end_at;
					}
					if($result['start_tm']>$result['end_tm']){
						$result['message']="开始时间不能大于结束时间";
						$error_arr[]=$result;
						continue;
					}
					$shield_error=$AdSearch->check_shield($result['package'],$result['start_tm'],$result['end_tm']);
					if($shield_error){
						$result['message']=$shield_error;
						$error_arr[]=$result;
						continue;
					}
					$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
					$re = $model->execute($sql); //需要复制上线但无校验
					if(!$re){
						$result['message']='编辑失败';
						$error_arr[]=$result;
					}
				}else if($table=='sj_feature_soft'){
					//无需复制上线
					$id_key='id';
					$set_str="last_refresh=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_tm={$end_at}";
					}
					// if(!empty($start_at)){
					// 	$set_str="start_tm={$start_at},end_tm={$end_at},last_refresh={$update_at}";
					// }else{
					// 	$set_str="end_tm={$end_at},last_refresh={$update_at}";
					// }
					$result=$model->table($table)->where(array($id_key=>$id))->find();
					$result['start_tm_sr']=$result['start_tm'];
					$result['end_tm_sr']=$result['end_tm'];
					if(!empty($start_at)){
						$result['start_tm']=$start_at;
					}
					if(!empty($end_at)){
						$result['end_tm']=$end_at;
					}
					if($result['start_tm']>$result['end_tm']){
						$result['message']="开始时间不能大于结束时间";
						$error_arr[]=$result;
						continue;
					}
					$shield_error=$AdSearch->check_shield($result['package'],$result['start_tm'],$result['end_tm']);
					if($shield_error){
						$result['message']=$shield_error;
						$error_arr[]=$result;
						continue;
					}
					$error_mess=$AdSearch->logic_check_Advertisement($result);
					// echo $AdSearch->getLastSql();
					// var_dump($error_mess);
					if($error_mess!=2){
						$result['message']=$error_mess['message'];
						$error_arr[]=$result;
						continue;
					}
					$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
					$re = $model->execute($sql); //不需要复制上线
					if(!$re){
						$result['message']='编辑失败';
						$error_arr[]=$result;
					}
				}
				if($re){
					$tt1 = $table_config["$table"]['1'].'_sr';
					$tt2 = $table_config["$table"]['2'].'_sr';
					$t1=$table_config["$table"]['1'];
					$t2=$table_config["$table"]['2'];
					$this->writelog("批量编辑了{$table_config["$table"]['3']}中id为{$id}的广告，原开始时间：{$result["$tt1"]},原结束时间：{$result["$tt2"]}更新为，开始时间：{$result["$t1"]},结束时间：{$result["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
				}
				// $bs=1;
			}
		}else{
			if(!$end_at){
				$end_at = time();
			}
			foreach($tblist as $k=>$v){
				$table=$v;
				$id=$idstr[$k];
				$sql="select * from {$table} WHERE id={$id}";
				$res = $model->query($sql);
				// echo $model->getLastSql();
				// echo "<pre>";var_dump($res);die;
				if($table=='sj_extent_soft_v1'){
					if(!empty($res)){
						foreach($res as $k=>$v){
							unset($res[$k]['id']);
							if(empty($start_at)){
								$start_at=$v['start_at'];
							}
							if(($start_at<$end_at)){
								$res[$k]['start_at']=$start_at;
								$res[$k]['end_at']=$end_at;
								if($res[$k]['start_at']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
								}
								// if($start_at>time()){
									$res[$k]['status']=1;
								// }else if($start_at<time()){
								// 	$res[$k]['status']=2;
								// }
								$res[$k]['create_at']=1;
								$res[$k]['admin_id']=$_SESSION['admin']['admin_id'];
								$shield_error=$AdSearch->check_shield($res[$k]['package'],$res[$k]['start_at'],$res[$k]['end_at']);
								$shield_error.=$AdSearch->check_shield_old($res[$k]['package'],0,1);
								if($shield_error){
									$res[$k]['message']=$shield_error;
									$error_arr[]=$res[$k];
									continue;
								}
								$error_mess=$AdSearch->logic_check_ExtentV1($res[$k]);
								if($error_mess!=2){
										$res[$k]['message']=$error_mess['message'];
										$error_arr[]=$res[$k];
										continue;
								}
								$re=M('extent_soft_v1')->add($res[$k]);
							}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
							}
							
						}
					}
				}else if($table=='sj_necessary_extent_soft'|| $table=='sj_category_extent_soft' || $table=='sj_return_back_ad' || $table=='sj_flexible_extent_soft' || $table=='sj_animation_ad'){
					if(!empty($res)){
						foreach($res as $k=>$v){
							unset($res[$k]['id']);
							if(empty($start_at)){
								$start_at=$v['start_at'];
							}
							if(($start_at<$end_at)){
								$res[$k]['start_at']=$start_at;
								$res[$k]['end_at']=$end_at;
								$res[$k]['status']=1;
								$res[$k]['create_at']=1;
								if($res[$k]['start_at']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
								}
								$shield_error=$AdSearch->check_shield($res[$k]['package'],$res[$k]['start_at'],$res[$k]['end_at']);
								if($table=='sj_category_extent_soft'){
									$shield_error.=$AdSearch->check_shield_old($res[$k]['package'],$res[$k]['extent_id'],0,'sj_category_extent');
								}else if($table=='sj_flexible_extent_soft'){
									$shield_error.=$AdSearch->check_shield_old($res[$k]['package'],$res[$k]['extent_id'],0,'sj_flexible_extent');
								}
								if($shield_error){
									$res[$k]['message']=$shield_error;
									$error_arr[]=$res[$k];
									continue;
								}
								if($table=='sj_return_back_ad'){
									$re=M('return_back_ad')->add($res[$k]);
								}else if($table=='sj_flexible_extent_soft'){
									$error_mess=$AdSearch->check_conflict_FlexibleExtent($res[$k]);
									if($error_mess!=0){
										$res[$k]['message']="本条数据与id为".explode(',',$error_mess).'排期冲突';
										$error_arr[]=$res[$k];
										continue;
									}
									$re=M('flexible_extent_soft')->add($res[$k]);
								}else if($table=='sj_animation_ad'){
									$error_mess=$AdSearch->check_conflict_AnimationAd($result);
									if($error_mess!=0){
										$result['message']="本条数据与id为".$error_mess.'排期冲突';
										$error_arr[]=$result;
										continue;
									}
									$re=M('animation_ad')->add($res[$k]);
								}else if($table=='sj_category_extent_soft'){
									$error_mess=$AdSearch->logic_check_CategoryExtent($res[$k]);
									if($error_mess!=2){
											$res[$k]['message']=$error_mess['message'];
											$error_arr[]=$res[$k];
											continue;
									}
									$re=M('category_extent_soft')->add($res[$k]);
								}else if($table=='sj_necessary_extent_soft'){
									//ok
									// $re=M('necessary_extent_soft')->add($res[$k]);
								}
							}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
							}
							
						}
					}
				}else if($table=='sj_search_key_package'){
					// if(!empty($res)){
					// 	foreach($res as $k=>$v){
					// 		unset($res[$k]['id']);
					// 		if(empty($start_at)){
					// 			$start_at=$v['start_tm'];
					// 		}
					// 		if(!($start_at<$v['stop_tm'] && $end_at>$v['start_tm'])){
					// 			$res[$k]['start_tm']=$start_at;
					// 			$res[$k]['stop_tm']=$end_at;
					// 			$res[$k]['status']=1;
					// 			$res[$k]['create_tm']=1;
					// 			$re=M('search_key_package')->add($res[$k]);
					// 		}
					// 		// echo M('')->getLastSql();
					// 		// var_dump($re);die;
					// 	}
					// }
				}else if($table=='sj_text_page' || $table=='sj_think_words'){
					if(!empty($res)){
						foreach($res as $k=>$v){
							unset($res[$k]['id']);
							if(empty($start_at)){
								$start_at=$v['start_time'];
							}
							if(($start_at<$end_at)){
								$res[$k]['start_time']=$start_at;
								$res[$k]['end_time']=$end_at;
								$res[$k]['status']=1;
								if($res[$k]['start_time']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
								}
								if($table=='sj_think_words'){
									$shield_error=$AdSearch->check_shield($res[$k]['package'],$res[$k]['start_time'],$res[$k]['end_time'],1);
									if($shield_error){
										$res[$k]['message']=$shield_error;
										$error_arr[]=$res[$k];
										continue;
									}
								}else{
									$shield_error=$AdSearch->check_shield($res[$k]['package'],$res[$k]['start_time'],$res[$k]['end_time']);
									if($shield_error){
										$res[$k]['message']=$shield_error;
										$error_arr[]=$res[$k];
										continue;
									}
								}
								
								if($table=='sj_text_page'){
									$error_mess=$AdSearch->check_conflict_textpage($res[$k]);
									if($error_mess!=0){
										$res[$k]['message']="本条数据与id为".$error_mess.'排期冲突';
										$error_arr[]=$res[$k];
										continue;
									}
									$re=M('text_page')->add($res[$k]);
								}else if($table=='sj_think_words'){

									$end_time=$res[$k]['end_time'];
										$start_time=$res[$k]['start_time'];
										$soft_rank=$res[$k]['soft_rank'];
										$id_tw=$res['id'];
										$search_words=$res[$k]['search_words'];
										 $sel = $model->table("sj_think_words")->where("((start_time <=$end_time and end_time>=$start_time) or (start_time <=$start_time and end_time>=$end_time)) and soft_rank=$soft_rank and status =1")->select();
										// }
										
										foreach($sel as $kk=>$vv){
											$new_str[] =  $vv['search_words'];
											
										}

										$a_arr = implode(',',$new_str);
										$newarr = explode(',',$a_arr);
										$arr=explode(',',$search_words);
										$res_re = array_intersect($newarr,$arr);

										if($res_re){
											$res[$k]['message']="当前排期中您填写的搜索词有冲突,冲突词为".implode(',',$res_re);
											$error_arr[]=$res[$k];
											continue;
										}
										// var_dump($res[$k]);die;
									$re=M('think_words')->add($res[$k]);
								}
							}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
							}
							// var_dump($re);
							// echo M('text_page')->getLastSql();
							// die;
						}
					}		
				}else if($table=='sj_search_keywords' || $table=='sj_download_recommend_soft' || $table=='sj_search_tips_package' || $table=='sj_market_push' || $table=='sj_splash_manage' || $table=='sj_soft_recommend' || $table=='sj_lading_soft' || $table=='sj_ad_new'){
					if(!empty($res)){
						foreach($res as $k=>$v){
							if($table!='sj_search_keywords'){
								unset($res[$k]['id']);
							}
							if(empty($start_at)){
								$start_at=$v['start_tm'];
							}
							if(($start_at<$end_at)){
								$res[$k]['start_tm']=$start_at;
								$res[$k]['end_tm']=$end_at;
								$res[$k]['status']=1;
								if($res[$k]['start_tm']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
										if($table=='sj_market_push'){
											$res[$k]['package']=$res[$k]['soft_package'];
										}
											$error_arr[]=$res[$k];
											continue;
								}
								if($table=='sj_search_keywords' || $table=='sj_search_tips_package'){
									$shield_error=$AdSearch->check_shield($res[$k]['package'],$res[$k]['start_tm'],$res[$k]['end_tm'],1);
									if($shield_error){
										$res[$k]['message']=$shield_error;
										$error_arr[]=$res[$k];
										continue;
									}
								}else{
									$shield_error=$AdSearch->check_shield($res[$k]['package'],$res[$k]['start_tm'],$res[$k]['end_tm']);
									if($shield_error){
										$res[$k]['message']=$shield_error;
										$error_arr[]=$res[$k];
										continue;
									}
								}
								
								if($table!='sj_search_keywords'){
									$res[$k]['create_tm']=time();
								}
								if($table=='sj_lading_soft'){
									//ok
									// $re=M('lading_soft')->add($res[$k]);
								}else if($table=='sj_ad_new'){
									$error_mess=$AdSearch->logic_check_adextent($res[$k]);
									if($error_mess!=2){
											$res[$k]['message']=$error_mess['message'];
											$error_arr[]=$res[$k];
											continue;
									}
									$re=M('ad_new')->add($res[$k]);
								}else if($table=='sj_splash_manage'){
									//ok
									$re=M('splash_manage')->add($res[$k]);
								}else if($table=='sj_market_push'){
									//ok
									// $re=M('market_push')->add($res[$k]);
								}else if($table=='sj_soft_recommend'){
									//ok
									// $re=M('soft_recommend')->add($res[$k]);
								}else if($table=='sj_search_tips_package'){
									//ok
									// $re=M('search_tips_package')->add($res[$k]);
								}else if($table=='sj_download_recommend_soft'){
									$error_mess=$AdSearch->logic_check_download($res[$k]);
									if($error_mess!=2){
										$res[$k]['message']=$error_mess['message'];
										$error_arr[]=$res[$k];
										continue;
									}
									$re=M('download_recommend_soft')->add($res[$k]);
								}else if($table=='sj_search_keywords'){
									// var_dump($res[$k]['start_tm']);die;
									$error_mess=$AdSearch->logic_check_Search_words($res[$k]);
									// var_dump($error_mess);die;
									if($error_mess!=2){
											$res[$k]['message']=$error_mess['message'];
											$error_arr[]=$res[$k];
											continue;
									}
									unset($res[$k]['id']);
									$re=M('search_keywords')->add($res[$k]);
									// echo M('search_keywords')->getLastSql();die;
								}
							}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								if($table=='sj_market_push'){
									$res[$k]['package']=$res[$k]['soft_package'];
								}
								$error_arr[]=$res[$k];
								continue;
							}
							// $re=M('sj_lading_soft')->add($res[$k]);
						}
					}
				}else if($table=='sj_activity'){
					//OK
					// if(!empty($res)){
					// 	foreach($res as $k=>$v){
					// 		unset($res[$k]['id']);
					// 		if(empty($start_at)){
					// 			$start_at=$v['start_tm'];
					// 		}
					// 		if(!($start_at<$v['end_tm'] && $end_at>$v['start_tm'])){
					// 			$res[$k]['start_tm']=$start_at;
					// 			$res[$k]['end_tm']=$end_at;
					// 			$res[$k]['status']=1;
					// 			$res[$k]['create_at']=time();
					// 			$re=M('activity')->add($res[$k]);
					// 		}
							
					// 	}
					// }
				}else if($table=='sj_feature_soft'){
					//ok
					// if(!empty($res)){
					// 	foreach($res as $k=>$v){
					// 		unset($res[$k]['id']);
					// 		if(empty($start_at)){
					// 			$start_at=$v['start_tm'];
					// 		}
					// 		if(!($start_at<$v['end_tm'] && $end_at>$v['start_tm'])){
					// 			$res[$k]['start_tm']=$start_at;
					// 			$res[$k]['end_tm']=$end_at;
					// 			$res[$k]['status']=1;
					// 			// $res[$k]['create_at']=time();
					// 			$re=M('feature_soft')->add($res[$k]);
					// 		}
							
					// 	}
					// }
				}
				if($re){
					$t1=$table_config["$table"]['1'];
					$t2=$table_config["$table"]['2'];
					$this->writelog("批量复制上线了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$res[$k]["$t1"]},结束时间：{$res[$k]["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
				}
			}
		}
		if (!$error_arr)
		{
			$this->success("{$actionname}成功");
		}
		else
		{
			$this->assign('error_arr',$error_arr);
			$this->display("error");
		}
	}
}