<?php
class AdListAction extends CommonAction {
	function index() {
		ini_set('display_errors', true);
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		$status = isset($_GET['status']) ? $_GET['status'] : 3;
		$today = strtotime(date('Y-m-d'));
		
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
		$dateToUse = $today;

		$adlist = array();
		//首页推荐
		$extentlist = $this->getExtentData($dateToUse, $status, $search_key, $pid);
		if (!empty($extentlist))
			$adlist = array_merge($adlist, $extentlist);
		//首页推荐v2
		$extentlist_v2 = $this->getExtentData_v2($dateToUse, $status, $search_key, $pid);
		if (!empty($extentlist_v2))
			$adlist = array_merge($adlist, $extentlist_v2);
		
		if ($pid == 1) {
			//应用最热
			$apphotlist = $this->getCategoryData($dateToUse, 'top_1_hot', $status, $search_key);
			if (!empty($apphotlist))
				$adlist = array_merge($adlist, $apphotlist);
			//游戏最热
			$gamehotlist = $this->getCategoryData($dateToUse, 'top_2_hot', $status, $search_key);
			if (!empty($gamehotlist))
				$adlist = array_merge($adlist, $gamehotlist);
			//应用最热2
			$fixedAppHotList = $this->getCategoryData($dateToUse, 'fixed_app_hot', $status, $search_key);
			if (!empty($fixedAppHotList))
				$adlist = array_merge($adlist, $fixedAppHotList);
			//游戏最热2
			$fixedGameHotList = $this->getCategoryData($dateToUse, 'fixed_game_hot', $status, $search_key);
			if (!empty($fixedGameHotList))
				$adlist = array_merge($adlist, $fixedGameHotList);
			//下载推荐  恢复之前的数据
			$downreclist = $this->getDownloadRecommendData($dateToUse, $status, $search_key);
			//$downreclist = $this->getCategoryData($dateToUse, 'fixed_user_also_download_recommend', $status, $search_key);
			if (!empty($downreclist))
				$adlist = array_merge($adlist, $downreclist);
			//猜你喜欢
			//$preferlist = $this->getPreferData($dateToUse, $status, $search_key);
			$preferlist = $this->getCategoryData($dateToUse, 'fixed_user_also_download', $status, $search_key);
			if (!empty($preferlist))
				$adlist = array_merge($adlist, $preferlist);
		}
		//类别置顶
		$categorytoplist = $this->getCategoryData($dateToUse, '', $status, $search_key, $pid);
		if (!empty($categorytoplist))
			$adlist = array_merge($adlist, $categorytoplist);
		if ($pid == 1) {
			//最新
			$topnewlist = $this->getCategoryData($dateToUse, 'top_new', $status, $search_key);
			if (!empty($topnewlist))
				$adlist = array_merge($adlist, $topnewlist);
			//必备
			$necessarylist = $this->getNecessaryData($dateToUse, $status, $search_key);
			if (!empty($necessarylist))
				$adlist = array_merge($adlist, $necessarylist);
			//搜索提示运营
			$search_tips_list = $this->getSearchTipsData($dateToUse, $status, $search_key);
			if (!empty($search_tips_list))
				$adlist = array_merge($adlist, $search_tips_list);
			
			//V6.4新增加搜索相关词
			$search_related_list = $this->getSearchRelatedData($dateToUse, $status, $search_key);
			if (!empty($search_related_list))
				$adlist = array_merge($adlist, $search_related_list);
			//搜索
			$searchlist = $this->getSearchData($dateToUse, $status, $search_key);
			if (!empty($searchlist))
				$adlist = array_merge($adlist, $searchlist);
			//下载推荐
			$downreclist = $this->getDownloadRecommendData($dateToUse, $status, $search_key);
			//$downreclist = $this->getCategoryData($dateToUse, 'fixed_user_also_download_recommend', $status, $search_key);
			if (!empty($downreclist))
				$adlist = array_merge($adlist, $downreclist);
			//搜索阿拉丁
			$searchaladinlist = $this->getSearchAladinData($dateToUse, $status, $search_key);
			if (!empty($searchaladinlist))
				$adlist = array_merge($adlist, $searchaladinlist);
			//搜索suggest
			$searchsuggestlist = $this->getSearchSuggestData($dateToUse, $status, $search_key);
			if (!empty($searchsuggestlist))
				$adlist = array_merge($adlist, $searchsuggestlist);
			//专题
			$featurelist = $this->getFeatureData($dateToUse, $status, $search_key);
			if (!empty($featurelist))
				$adlist = array_merge($adlist, $featurelist);
			//一键装机
			$ladinglist = $this->getLadingData($dateToUse, $status, $search_key);
			if (!empty($ladinglist))
				$adlist = array_merge($adlist, $ladinglist);
			//猜你喜欢
			//$preferlist = $this->getPreferData($dateToUse, $status, $search_key);
			$preferlist = $this->getCategoryData($dateToUse, 'fixed_user_also_download', $status, $search_key);
			if (!empty($preferlist))
				$adlist = array_merge($adlist, $preferlist);
		}
		foreach ($adlist as $v)
		{
			$extent[$v['id']] = array('name' => $v['name'], 'parent' => $v['parent']);
		}
		foreach ($extent as $k => $v)
		{
			if ($v['parent'] == '' || $v['parent'] == 'SKKEY' || $v['parent'] == 'SR0' || $v['parent'] == 'ST0')
				continue;
			if (isset($extent[$v['parent']]))
				$extent[$v['parent']]['children'][] = $k;
			else
				$extent[$v['parent']]['children'] = array($k);
		}
		$dateList = array();
		$this->assign('datalist', $adlist);
		$this->assign('extentlist', $extent);
		$this->assign('search_key', $search_key);
		$this->assign('status', $status);
		$this->assign('pid', $pid);
		$this->display();
	}

	function schedule() {
		ini_set('display_errors', true);
		error_reporting(E_ALL);
		ini_set('memory_limit', '1024M');
		$search_key = isset($_GET['search_key']) ? $_GET['search_key'] : '';
		$status = isset($_GET['status']) ? $_GET['status'] : 3;
		$today = strtotime(date('Y-m-d'));
		$selectYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
		$selectMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
		$selectDate = strtotime("$selectYear-$selectMonth-01");
		$dateToUse = ($today > $selectDate) ? $today : $selectDate;
		if (isset($_GET['year']) && isset($_GET['month']) && $dateToUse == $selectDate)
			$n = date('t', strtotime($dateToUse));
		else
			$n = 31;
		for ($i = 0; $i < $n; $i++)
		{
			$adlist = array();
			$timestamp = $dateToUse + $i * 86400;

			//首页推荐
			$extentlist = $this->getExtentData($timestamp, $status, $search_key);
			if (!empty($extentlist))
				$adlist = array_merge($adlist, $extentlist);
			//装机必备(新版删除)
			//$featurelist = $this->getFeatureData($timestamp, $status, $search_key);
			//if (!empty($featurelist))
			//	$adlist = array_merge($adlist, $featurelist);
			//应用最热
			$apphotlist = $this->getCategoryData($timestamp, 'top_1_hot', $status, $search_key);
			if (!empty($apphotlist))
				$adlist = array_merge($adlist, $apphotlist);
			//游戏最热
			$gamehotlist = $this->getCategoryData($timestamp, 'top_2_hot', $status, $search_key);
			if (!empty($gamehotlist))
				$adlist = array_merge($adlist, $gamehotlist);
			//类别置顶
			$categorytoplist = $this->getCategoryData($timestamp, '', $status, $search_key);
			if (!empty($categorytoplist))
				$adlist = array_merge($adlist, $categorytoplist);
			//最新
			$topnewlist = $this->getCategoryData($timestamp, 'top_new', $status, $search_key);
			if (!empty($topnewlist))
				$adlist = array_merge($adlist, $topnewlist);
			//搜索
			$searchlist = $this->getSearchData($timestamp, $status, $search_key);
			if (!empty($searchlist))
				$adlist = array_merge($adlist, $searchlist);
			//搜索提示运营
			$search_tips_list = $this->getSearchTipsData($dateToUse, $status, $search_key);
			if (!empty($search_tips_list))
				$adlist = array_merge($adlist, $search_tips_list);
			
			//V6.4新增加搜索相关词
			$search_related_list = $this->getSearchRelatedData($dateToUse, $status, $search_key);
			if (!empty($search_related_list))
				$adlist = array_merge($adlist, $search_related_list);
			
			//必备
			$necessarylist = $this->getNecessaryData($timestamp, $status, $search_key);
			if (!empty($necessarylist))
				$adlist = array_merge($adlist, $necessarylist);
			//猜你喜欢
			/*$preferlist = $this->getPreferData($timestamp , $status, $search_key);
			if (!empty($preferlist))
				$adlist = array_merge($adlist, $preferlist);*/
			//下载推荐
			$downreclist = $this->getDownloadRecommendData($timestamp, $status, $search_key);
			//$downreclist = $this->getCategoryData($timestamp, 'fixed_user_also_download_recommend', $status, $search_key);
			if (!empty($downreclist))
				$adlist = array_merge($adlist, $downreclist);
			//猜你喜欢
			//$preferlist = $this->getPreferData($timestamp, $status, $search_key);
			$preferlist = $this->getCategoryData($timestamp, 'fixed_user_also_download', $status, $search_key);
			if (!empty($preferlist))
				$adlist = array_merge($adlist, $preferlist);
			
			foreach ($adlist as $v)
			{
				$data[$timestamp][$v['id']] = intval($v['status']);
			}
		}
		foreach ($adlist as $v)
		{
			$extent[$v['id']] = array('name' => $v['name'], 'parent' => $v['parent'], 'listurl' => $v['listurl']);
		}
		foreach ($extent as $k => $v)
		{

			if ($v['parent'] == '')
				continue;
			if (isset($extent[$v['parent']]))
				$extent[$v['parent']]['children'][] = $k;
			else
				$extent[$v['parent']]['children'] = array($k);
		}
		ksort($data);
		$dateList = array();
		foreach ($data as $date => $value)
		{
			$month = date('Y-m', $date);
			$day = date('d', $date);
			$dateList[$month][] = $day;
		}
		$monthList = array();
		foreach ($dateList as $month => $day)
			$monthList[$month] = count($day);
		if (!empty($_GET['action']) && $_GET['action'] == 'export')
		{
			$this->exportExcel($monthList, $data, $extent);
		}
		else
		{
			$this->assign('monthlist', $monthList);
			$this->assign('datalist', $data);
			$this->assign('extentlist', $extent);
			$this->assign('timelist', array_keys($data));
			$this->assign('year', date('Y', $dateToUse));
			$this->assign('month', date('m', $dateToUse));
			$this->assign('search_key', $search_key);
			$this->display();
		}
	}

	function getExtentData($time, $status = 3, $key = '', $pid = 1) {
		$prefix = 'EX';
		if (empty($time))
			return false;
		$extent_model = M('extent_v1');
		$extent_soft_model = M('extent_soft_v1');
		$map = array(
			'status' => 1,
			'pid' => $pid,
			'parent_union_id' => '',
			'type' => array('exp', '!=2'),
			'parent_id' => array('exp', '=0 OR (parent_id in (select extent_id from sj_extent_v1 where type=2 and status=1))'),
		);
		if (!empty($key))
			$map['extent_name'] = array('exp', "LIKE '%$key%'");
		$extentlist = $extent_model->where($map)->order('rank asc')->select();
		if (empty($extentlist))
			return null;
		$statusall = 1;
		$statuspart = 0;
		$availableall = 0;
		$emptyall = 0;
		$pausedall = 0;
		$idlist = array();
		foreach ($extentlist as $v)
		{
			$idlist[] = $v['extent_id'];
		}
		$map = array(
			'extent_id' => array('in', $idlist),
			'start_at' => array('ELT', $time),
			'end_at' => array('EGT', $time),
		);
		$map['status'] = 2;
		$res = $extent_soft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$pauselist = array();
		foreach ($res as $v)
		{
			$pauselist[$v['extent_id']] = $v['c'];
		}
		$map['status'] = 1;
		$res = $extent_soft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$availlist = array();
		foreach ($res as $v)
		{
			$availlist[$v['extent_id']] = $v['c'];
		}
		foreach ($extentlist as $v)
		{
			$available = isset($availlist[$v['extent_id']]) ? $availlist[$v['extent_id']] : 0;
			$paused = isset($pauselist[$v['extent_id']]) ? $pauselist[$v['extent_id']] : 0;
			if ($v['type'] == 3)
			{
				$map = array(
					'parent_union_id' => $v['extent_id'],
					'status' => 1,
				);
				$ret = $extent_model->where($map)->field('sum(extent_size) as c')->find();
				$buckets = $ret['c'];
			}
			else
			{
				$buckets = $v['extent_size'];
			}
			if ($available == 0)
			{
				$statusnow = 0;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
			}
			elseif ($buckets <= $available)
			{
				$statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statuspart = 1;
			}
			else
			{
				$statusnow = 1;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
				$statuspart = 1;
			}
			if (($buckets - $available) > 0)
				$empty = ($buckets - $available);
			else
				$empty = 0;
			$data[$prefix . $v['extent_id']] = array(
				'id' => $prefix . $v['extent_id'],
				'name' => $v['extent_name'],
				'status' => $statusnow,
				'available' => $available,
				'empty' => $empty,
				'paused' => $paused,
				'parent' => $prefix . '0',
				'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/EX/location3/{$v['extent_id']}/pid/{$pid}",
				'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['extent_name']}",
			);
			$availableall += $available;
			$emptyall += $empty;
			$pausedall += $paused;
		}
		if (empty($data))
			return null;
		$statusfinal = $statuspart + $statusall;
		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '首页推荐',
			'status' => $statusfinal,
			'available' => $availableall,
			'empty' => $emptyall,
			'paused' => $pausedall,
			'parent' => '',
			'url' => '/index.php/Sj/ExtentV1/import_softs/pid/' . $pid,
		);
		return $data;
	}
	
	function getExtentData_v2($time, $status = 3, $key = '', $pid = 1) {
		$prefix = 'EX_v2';
		if (empty($time))
			return false;
		$extent_model = M('extent_v2');
		$extent_soft_model = M('extent_soft_v2');
		$map = array(
				'status' => 1,
				'pid' => $pid,
				'parent_union_id' => '',
				'type' => array('exp', '!=2'),
				'parent_id' => array('exp', '=0 OR (parent_id in (select extent_id from sj_extent_v2 where type=2 and status=1))'),
		);
		if (!empty($key))
			$map['extent_name'] = array('exp', "LIKE '%$key%'");
		$extentlist = $extent_model->where($map)->order('rank asc')->select();
		if (empty($extentlist))
			return null;
		$statusall = 1;
		$statuspart = 0;
		$availableall = 0;
		$emptyall = 0;
		$pausedall = 0;
		$idlist = array();
		foreach ($extentlist as $v)
		{
			$idlist[] = $v['extent_id'];
		}
		$map = array(
				'extent_id' => array('in', $idlist),
				'start_at' => array('ELT', $time),
				'end_at' => array('EGT', $time),
		);
		$map['status'] = 2;
		$res = $extent_soft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$pauselist = array();
		foreach ($res as $v)
		{
			$pauselist[$v['extent_id']] = $v['c'];
		}
		$map['status'] = 1;
		$res = $extent_soft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$availlist = array();
		foreach ($res as $v)
		{
			$availlist[$v['extent_id']] = $v['c'];
		}
		foreach ($extentlist as $v)
		{
			$available = isset($availlist[$v['extent_id']]) ? $availlist[$v['extent_id']] : 0;
			$paused = isset($pauselist[$v['extent_id']]) ? $pauselist[$v['extent_id']] : 0;
			if ($v['type'] == 3)
			{
				$map = array(
						'parent_union_id' => $v['extent_id'],
						'status' => 1,
				);
				$ret = $extent_model->where($map)->field('sum(extent_size) as c')->find();
				$buckets = $ret['c'];
			}
			else
			{
				$buckets = $v['extent_size'];
			}
			if ($available == 0)
			{
				$statusnow = 0;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
			}
			elseif ($buckets <= $available)
			{
				$statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statuspart = 1;
			}
			else
			{
				$statusnow = 1;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
				$statuspart = 1;
			}
			if (($buckets - $available) > 0)
				$empty = ($buckets - $available);
			else
				$empty = 0;
			$data[$prefix . $v['extent_id']] = array(
					'id' => $prefix . $v['extent_id'],
					'name' => $v['extent_name'],
					'status' => $statusnow,
					'available' => $available,
					'empty' => $empty,
					'paused' => $paused,
					'parent' => $prefix . '0',
					'jumpurl' => '',
					//'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/EX/location3/{$v['extent_id']}/pid/{$pid}",
					'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['extent_name']}",
			);
			$availableall += $available;
			$emptyall += $empty;
			$pausedall += $paused;
		}
		if (empty($data))
			return null;
		$statusfinal = $statuspart + $statusall;
		$data[$prefix . '0'] = array(
				'id' => $prefix . '0',
				'name' => '首页推荐_v2',
				'status' => $statusfinal,
				'available' => $availableall,
				'empty' => $emptyall,
				'paused' => $pausedall,
				'parent' => '',
				'url' => '/index.php/Sj/ExtentV2/import_softs/pid/' . $pid,
		);
		return $data;
	}

	function getFeatureData($time, $status = 3, $key = '') {
		$prefix = 'FE';
		if (empty($time))
			return false;
		$feature_model = M('feature');
		$feature_soft_model = M('feature_soft');
		$map = array(
			'status' => 1,
		);
		if (!empty($key)) {
			$map['name'] = array('exp', "like '%{$key}%'");
		}
		$feature = $feature_model->where($map)->select();
		if (empty($feature))
			return null;
		$statusall = 1;
		$statuspart = 0;
		$availableall = 0;
		$emptyall = 0;
		$pausedall = 0;
		$idlist = array();
		foreach ($feature as $v)
		{
			$idlist[] = $v['feature_id'];
		}
		$map = array(
			'feature_id' => array('in', $idlist),
			'start_tm' => array('ELT', $time),
			'end_tm' => array('EGT', $time),
		);
		$map['status'] = 2;
		$res = $feature_soft_model->where($map)->group('feature_id')->field('feature_id, count(*) as c')->select();
		$pauselist = array();
		foreach ($res as $v)
		{
			$pauselist[$v['feature_id']] = $v['c'];
		}
		$map['status'] = 1;
		$res = $feature_soft_model->where($map)->group('feature_id')->field('feature_id, count(*) as c')->select();
		$availlist = array();
		foreach ($res as $v)
		{
			$availlist[$v['feature_id']] = $v['c'];
		}
		foreach ($feature as $v)
		{
			$available = isset($availlist[$v['feature_id']]) ? $availlist[$v['feature_id']] : 0;
			$paused = isset($pauselist[$v['feature_id']]) ? $pauselist[$v['feature_id']] : 0;

			if ($available == 0)
			{
				$statusnow = 0;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
			}
			else
			{
				$statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statuspart = 1;
			}
			$data[$prefix . $v['feature_id']] = array(
				'id' => $prefix . $v['feature_id'],
				'name' => $v['name'],
				'status' => $statusnow,
				'available' => $available,
				'empty' => '-',
				'paused' => $paused,
				'parent' => $prefix . '0',
				'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/FE/location3/{$v['feature_id']}",
				'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['extent_name']}",
			);
			$availableall += $available;
			$pausedall += $paused;
		}
		if (empty($data))
			return null;
		$statusfinal = $statuspart + $statusall;
		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '专题',
			'status' => $statusfinal,
			'available' => $availableall,
			'empty' => '-',
			'paused' => $pausedall,
			'parent' => '',
			'url' => '/index.php/Sj/Advertisement/import_softs',
		);
		return $data;
	}
	function getLadingData($time, $status = 3, $key = '') 
	{
		$prefix = 'LA';
		if (empty($time))
			return false;
		$category_model = M('lading_category');
		$lading_soft_model = M('lading_soft');
		$map = array(
			'status' => 1,
		);
		if (!empty($key)) {
			$map['category_name'] = array('exp', "like '%{$key}%'");
		}
		$category = $category_model->where($map)->select();
		if (empty($category))
			return null;
		$statusall = 1;
		$statuspart = 0;
		$availableall = 0;
		$emptyall = 0;
		$pausedall = 0;
		$idlist = array();
		foreach ($category as $v)
		{
			$idlist[] = $v['id'];
		}
		$map = array(
			'category_id' => array('in', $idlist),
			'start_tm' => array('ELT', $time),
			'end_tm' => array('EGT', $time),
		);
		$map['status'] = 2;
		$res = $lading_soft_model->where($map)->group('category_id')->field('category_id, count(*) as c')->select();
		$pauselist = array();
		foreach ($res as $v)
		{
			$pauselist[$v['category_id']] = $v['c'];
		}
		$map['status'] = 1;
		$res = $lading_soft_model->where($map)->group('category_id')->field('category_id, count(*) as c')->select();
		$availlist = array();
		foreach ($res as $v)
		{
			$availlist[$v['category_id']] = $v['c'];
		}
		foreach ($category as $v)
		{
			$available = isset($availlist[$v['id']]) ? $availlist[$v['id']] : 0;
			$paused = isset($pauselist[$v['id']]) ? $pauselist[$v['id']] : 0;
			if ($available == 0)
			{
				$statusnow = 0;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
			}
			else
			{
				$statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statuspart = 1;
			}
			$data[$prefix . $v['id']] = array(
				'id' => $prefix . $v['id'],
				'name' => $v['category_name'],
				'status' => $statusnow,
				'available' => $available,
				'empty' => '-',
				'paused' => $paused,
				'parent' => $prefix . '0',
				'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/LA/location3/{$v['id']}",
				'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['category_name']}",
			);
			$availableall += $available;
			$pausedall += $paused;
		}
		if (empty($data))
			return null;
		$statusfinal = $statuspart + $statusall;
		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '一键装机',
			'status' => $statusfinal,
			'available' => $availableall,
			'empty' => '-',
			'paused' => $pausedall,
			'parent' => '',
			'url' => '/index.php/Sj/Ladingmanage/import_lading',
		);
		return $data;
	}
	function getNecessaryData($time, $status = 3, $key = '') {
		$prefix = 'NE';
		if (empty($time))
			return false;
		$nec_model = M('necessary_extent');
		$necsoft_model = M('necessary_extent_soft');
		$map = array(
			'status' => 1,
			'type' => 1,
			'parent_id' => array('exp', '=0 OR (parent_id in (select extent_id from sj_necessary_extent where type=2 and status=1))'),
		);
		if (!empty($key))
			$map['extent_name'] = array('exp', "LIKE '%$key%'");
		$extentlist = $nec_model->where($map)->order('rank asc')->select();
		if (empty($extentlist))
			return null;
		$statusall = 1;
		$statuspart = 0;
		$availableall = 0;
		$emptyall = 0;
		$pausedall = 0;
		$idlist = array();
		foreach ($extentlist as $v)
		{
			$idlist[] = $v['extent_id'];
		}
		$map = array(
			'extent_id' => array('in', $idlist),
			'start_at' => array('ELT', $time),
			'end_at' => array('EGT', $time),
		);
		$map['status'] = 2;
		$res = $necsoft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$pauselist = array();
		foreach ($res as $v)
		{
			$pauselist[$v['extent_id']] = $v['c'];
		}
		$map['status'] = 1;
		$res = $necsoft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$availlist = array();
		foreach ($res as $v)
		{
			$availlist[$v['extent_id']] = $v['c'];
		}
		foreach ($extentlist as $v)
		{
			$available = isset($availlist[$v['extent_id']]) ? $availlist[$v['extent_id']] : 0;
			$paused = isset($pauselist[$v['extent_id']]) ? $pauselist[$v['extent_id']] : 0;
			$buckets = $v['extent_size'];

			if ($available == 0)
			{
				$statusnow = 0;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
			}
			elseif ($buckets <= $available)
			{
				$statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statuspart = 1;
			}
			else
			{
				$statusnow = 1;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
				$statuspart = 1;
			}
			if (($buckets - $available) > 0)
				$empty = ($buckets - $available);
			else
				$empty = 0;
			$data[$prefix . $v['extent_id']] = array(
				'id' => $prefix . $v['extent_id'],
				'name' => $v['extent_name'],
				'status' => $statusnow,
				'available' => $available,
				'empty' => $empty,
				'paused' => $paused,
				'parent' => $prefix . '0',
				'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/NE/location3/{$v['extent_id']}",
				'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['extent_name']}",
			);
			$availableall += $available;
			$emptyall += $empty;
			$pausedall += $paused;
		}
		if (empty($data))
			return null;
		$statusfinal = $statuspart + $statusall;
		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '必备',
			'status' => $statusfinal,
			'available' => $availableall,
			'empty' => $emptyall,
			'paused' => $pausedall,
			'parent' => '',
			'url' => '/index.php/Sj/NecessaryExtent/import_softs',
		);
		return $data;
	}

	function getCategoryData($time, $cache_key = '', $status = 3, $key = '', $pid = 1) {
		if (empty($time))
			return false;
		$categorylist = array();
		$data = array();
		switch ($cache_key)
		{
			case 'top_1_hot':
				$prefix = 'AH';
				$categorylist[$cache_key] = '应用热门';
				break;

			case 'top_2_hot':
				$prefix = 'GH';
				$categorylist[$cache_key] = '游戏热门';
				break;

			case 'top_new':
				$prefix = 'TN';
				$categorylist[$cache_key] = '最新';
				break;
				
			/*case 'fixed_user_also_download_recommend':
				$prefix = 'DR';
				$categorylist[$cache_key] = '下载推荐';
				break;*/
				
			case 'fixed_user_also_download':
				$prefix = 'PR';
				$categorylist[$cache_key] = '猜你喜欢';
				break;
			case 'fixed_app_hot':
				$prefix = "FAH";
				$categorylist[$cache_key] = '应用最热2';
				break;
			case 'fixed_game_hot':
				$prefix = "FGH";
				$categorylist[$cache_key] = '游戏最热2';
				break;
			default:
				$prefix = 'CA';
				$categorylist = $this->getCategoryTypes();
				unset($categorylist['top_1_hot']);
				if ($pid != 5)
					unset($categorylist['top_2_hot']);
				unset($categorylist['top_new']);
				break;
		}

		$extent_model = M('category_extent');
		$extent_soft_model = M('category_extent_soft');
		$statusallfinal = 1;
		$statuspartfinal = 0;
		
		$map = array(
			'status' => 1,
			'pid' => $pid,
			'type' => array('exp', '!=2'),
			'category_type' => array('in', array_keys($categorylist)),
		);
		if (!empty($key))
			$map['extent_name'] = array('exp', "LIKE '%$key%'");
		$extentall = $extent_model->where($map)->order('rank asc')->select();
		$extentlist = array();
		foreach ($extentall as $v)
		{
			if (isset($extentlist[$v['category_type']]))
				$extentlist[$v['category_type']][] = $v;
			else
				$extentlist[$v['category_type']] = array($v);
		}
		foreach ($extentall as $v)
		{
			$idlist[] = $v['extent_id'];
		}
		$map = array(
			'extent_id' => array('in', $idlist),
			'start_at' => array('ELT', $time),
			'end_at' => array('EGT', $time),
		);
		$map['status'] = 2;
		$res = $extent_soft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$pauselist = array();
		foreach ($res as $v)
		{
			$pauselist[$v['extent_id']] = $v['c'];
		}
		$map['status'] = 1;
		$res = $extent_soft_model->where($map)->group('extent_id')->field('extent_id, count(*) as c')->select();
		$availlist = array();
		foreach ($res as $v)
		{
			$availlist[$v['extent_id']] = $v['c'];
		}

		foreach ($categorylist as $k => $name)
		{
			if (empty($extentlist[$k]))
				continue;
			$statussuball = 1;
			$statusall = 1;
			$statussubpart = 0;
			$statuspart = 0;
			$availablesub = 0;
			$emptysub = 0;
			$pausedsub = 0;
			$countbefore = count($data);
			$idlist = array();

			foreach ($extentlist[$k] as $v)
			{
				$available = isset($availlist[$v['extent_id']]) ? $availlist[$v['extent_id']] : 0;
				$paused = isset($pauselist[$v['extent_id']]) ? $pauselist[$v['extent_id']] : 0;
				$buckets = $v['extent_size'];

				if ($available == 0)
				{
					$statusnow = 0;
					if ($status != 3 && $status != $statusnow)
						continue;
					$statussuball = 0;
					$statusall = 0;
					$statusallfinal = 0;
				}
				elseif ($buckets <= $available)
				{
					$statusnow = 2;
					if ($status != 3 && $status != $statusnow)
						continue;
					$statussubpart = 1;
					$statuspart = 1;
					$statuspartfinal = 1;
				}
				else
				{
					$statusnow = 1;
					if ($status != 3 && $status != $statusnow)
						continue;
					$statussuball = 0;
					$statusall = 0;
					$statusallfinal = 0;
					$statussubpart = 1;
					$statuspart = 1;
					$statuspartfinal = 1;
				}
				if (($buckets - $available) > 0)
					$empty = ($buckets - $available);
				else
					$empty = 0;
				if($prefix=="CA")//类别置顶增加频道分类
				{
					//判断是普通、标签、常用标签、榜单
					if(strpos($v['category_type'],"bdlist_")!==false)
					{
						$page_type=5;
					}
					elseif(strpos($v['category_type'],"commontag_")!==false)
					{
						$page_type=3;
					}
					elseif(strpos($v['category_type'],"tag_")!==false)
					{
						$page_type=2;
					}
					else
					{
						$page_type=1;
					}
				}
				$data[$prefix . $v['extent_id']] = array(
					'id' => $prefix . $v['extent_id'],
					'name' => $v['extent_name'],
					'status' => $statusnow,
					'available' => $available,
					'empty' => $empty,
					'paused' => $paused,
					'parent' => $prefix . $k,
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/{$prefix}/" . (($prefix == 'CA') ? "location2/{$k}/" : "") . "location3/{$v['extent_id']}/pid/$pid/page_type/$page_type",
					'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['extent_name']}",
				);
				$availablesub += $available;
				$availableall += $available;
				$emptysub += $empty;
				$emptyall += $empty;
				$pausedsub += $paused;
				$pausedall += $paused;
			}
			$countafter = count($data);
			if ($countafter == $countbefore)
				continue;
			$statussub = $statussubpart + $statussuball;
			$data[$prefix . $k] = array(
				'id' => $prefix . $k,
				'name' => $name,
				'status' => $statussub,
				'available' => $availablesub,
				'empty' => $emptysub,
				'paused' => $pausedsub,
				'parent' => $prefix . '0',
			);
			if ($prefix != 'CA')
			{
				$data[$prefix . $k]['url'] = '/index.php/Sj/CategoryExtent/import_softs_' . $prefix;
			}
		}
		if (empty($data))
			return null;
		if ($prefix == 'CA')
		{
			$statusfinal = $statuspartfinal + $statusallfinal;
			$data[$prefix . '0'] = array(
				'id' => $prefix . '0',
				'name' => '类别置顶',
				'status' => $statusfinal,
				'available' => $availableall,
				'empty' => $emptyall,
				'paused' => $pausedall,
				'parent' => '',
				'url' => '/index.php/Sj/CategoryExtent/import_softs_' . $prefix . '/pid/' . $pid,
			);
		}
		else
		{
			$data[$prefix . $k]['parent'] = '';
		}
		return $data;
	}

	function getSearchData($time, $status = 3, $key = '') {
		if (empty($time))
			return false;
		$prefix = 'SK';
		$data = array();
		$statuspart = 0;
		$statusall = 1;
		$availableall = 0;

		$searchkey_model = M('search_key');
		$searchkeypkg_model = M('search_key_package');
		$map = array(
			'status' => 1,
			'start_tm' => array('ELT', $time),
		);
		if (!empty($key))
			$map['srh_key'] = array('exp', " like '%$key%'");
		$keylist = $searchkey_model->where($map)->order('update_tm desc')->select();
		if (!empty($keylist))
		{
			$statusallsub = 1;
			$statuspartsub = 0;
			$availablesub = 0;
			$pausedsub = 0;
			$idlist = array();
			foreach ($keylist as $v)
			{
				$idlist[] = $v['id'];
			}
			$map = array(
				'kid' => array('in', $idlist),
				'start_tm' => array('ELT', $time),
				'stop_tm' => array('EGT', $time),
			);
			$map['status'] = 2;
			$res = $searchkeypkg_model->where($map)->group('kid')->field('kid, count(*) as c')->select();
			$pauselist = array();
			foreach ($res as $v)
			{
				$pauselist[$v['kid']] = $v['c'];
			}
			$map['status'] = 1;
			$res = $searchkeypkg_model->where($map)->group('kid')->field('kid, count(*) as c')->select();
			$availlist = array();
			foreach ($res as $v)
			{
				$availlist[$v['kid']] = $v['c'];
			}
			foreach ($keylist as $v)
			{
				$available = isset($availlist[$v['id']]) ? $availlist[$v['id']] : 0;
				$paused = isset($pauselist[$v['id']]) ? $pauselist[$v['id']] : 0;
				($available == 0) ? $statusnow = 0 : $statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				if ($statusnow == 2)
				{
					$statuspartsub = 1;
					$statuspart = 1;
				}
				else
				{
					$statusallsub = 0;
					$statusall = 0;
				}
				$availablesub += $available;
				$availableall += $available;
				$pausedsub += $paused;
				$pausedall += $paused;
				$data[$prefix . $v['id']] = array(
					'id' => $prefix . $v['id'],
					'name' => $v['srh_key'],
					'status' => $statusnow,
					'available' => $available,
					'empty' => '-',
					'paused' => $paused,
					'parent' => $prefix . 'KEY',
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/SK/location2/keyword/location3/{$v['id']}",
					'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['srh_key']}",
				);
			}
			if (!empty($data))
			{
				$statussub = $statusallsub + $statuspartsub;
				$data[$prefix . 'KEY'] = array(
					'id' => $prefix . 'KEY',
					'name' => '搜索关键词列表',
					'status' => $statussub,
					'available' => $availablesub,
					'empty' => '-',
					'paused' => $pausedsub,
					'parent' => $prefix . '0',
					'url' => '/index.php/Sj/Search_weight/import_softs',
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/0/key_type/package/pid/1/location1/SK/page_type/1/location2/keyword/location3/keyword",
				);
			}
		}

		if (empty($key) || (stripos('搜索默认关键字管理v4', $key) !== false))
		{
			$keyword_model = M('soft_defaultkeywords');
			$map = array(
				'status' => 1,
				'start_time' => array('ELT', $time),
				'end_time' => array('EGT', $time),
			);
			$available = $keyword_model->where($map)->count();
			$map = array(
				'status' => 2,
				'start_time' => array('ELT', $time),
				'end_time' => array('EGT', $time),
			);
			$paused = $keyword_model->where($map)->count();
			($available == 0) ? $statusnow = 0 : $statusnow = 2;
			if ($status == 3 || $status == $statusnow)
			{
				if ($statusnow == 2)
				{
					$statuspart = 1;
				}
				else
				{
					$statusall = 0;
				}
				$availableall += $available;
				$pausedall += $paused;
				$data[$prefix . 'DEFAULT'] = array(
					'id' => $prefix . 'DEFAULT',
					'name' => '搜索默认关键字管理v4',
					'status' => $statusnow,
					'available' => $available,
					'empty' => '-',
					'paused' => $paused,
					'parent' => $prefix . '0',
					'url' => '/index.php/Sj/Searchkeywords/import_softs_defaultkeywords',
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/SK/location2/default",
					'listurl' => "/index.php/Sj/AdList/index/search_key/" . urlencode("搜索默认关键字管理v4"),
				);
			}
		}
		if (empty($key) || (stripos('搜索热词管理v4', $key) !== false))
		{
			$hotword_model = M('soft_hotwords');

			$map = array(
				'status' => 1,
				'start_time' => array('ELT', $time),
				'end_time' => array('EGT', $time),
			);
			$available = $hotword_model->where($map)->count();
			$map = array(
				'status' => 2,
				'start_time' => array('ELT', $time),
				'end_time' => array('EGT', $time),
			);
			$paused = $hotword_model->where($map)->count();
			($available == 0) ? $statusnow = 0 : $statusnow = 2;
			if ($status == 3 || $status == $statusnow)
			{
				if ($statusnow == 2)
				{
					$statuspart = 1;
				}
				else
				{
					$statusall = 0;
				}
				$availableall += $available;
				$pausedall += $paused;

				$data[$prefix . 'HOT'] = array(
					'id' => $prefix . 'HOT',
					'name' => '搜索热词管理v4',
					'status' => $statusnow,
					'available' => $available,
					'empty' => '-',
					'paused' => $paused,
					'parent' => $prefix . '0',
					'url' => '/index.php/Sj/Searchkeywords/import_softs_hotwords',
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/SK/location2/hotword",
					'listurl' => "/index.php/Sj/AdList/index/search_key/" . urlencode("搜索热词管理v4"),
				);
			}
		}
		if (empty($key) || (stripos('搜索热词推荐', $key) !== false))
		{
			$keywords_model = M('search_keywords');

			$map = array(
				'status' => 1,
				'start_tm' => array('ELT', $time),
				'end_tm' => array('EGT', $time),
			);
			$available = $keywords_model->where($map)->count();
			$map = array(
				'status' => 2,
				'start_tm' => array('ELT', $time),
				'end_tm' => array('EGT', $time),
			);
			$paused = $keywords_model->where($map)->count();
			($available == 0) ? $statusnow = 0 : $statusnow = 2;
			if ($status == 3 || $status == $statusnow)
			{
				if ($statusnow == 2)
				{
					$statuspart = 1;
				}
				else
				{
					$statusall = 0;
				}
				$availableall += $available;
				$pausedall += $paused;

				$data[$prefix . 'SKEY'] = array(
					'id' => $prefix . 'SKEY',
					'name' => '搜索热词推荐',
					'status' => $statusnow,
					'available' => $available,
					'empty' => '-',
					'paused' => $paused,
					'parent' => $prefix . '0',
					'url' => '/index.php/Search/Advertisement/import_softs_hotwords_recommend',
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/SK/location2/skeyword/pid/1",
					'listurl' => "/index.php/Sj/AdList/index/search_key/" . urlencode("搜索热词推荐"),
				);
			}
		}
		if (empty($data))
			return null;
		$statusfinal = $statuspart + $statusall;
		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '搜索热词',
			'status' => $statusfinal,
			'available' => $availableall,
			'empty' => '-',
			'paused' => $pausedall,
			'parent' => '',
		);
		return $data;
	}

	function getPreferData($time, $status = 3, $key = '') {
		$prefix = 'PR';
		if (!empty($key) && (stripos('猜你喜欢', $key) === false))
			return false;
		if (empty($time))
			return false;
		$softrecommend_model = M('soft_recommend');

		$map = array(
			'status' => 1,
			'start_tm' => array('ELT', $time),
			'end_tm' => array('EGT', $time),
		);
		$available = $softrecommend_model->where($map)->count();
		$map = array(
			'status' => 2,
			'start_tm' => array('ELT', $time),
			'end_tm' => array('EGT', $time),
		);
		$paused = $softrecommend_model->where($map)->count();
		($available == 0) ? $statusnow = 0 : $statusnow = 2;
		if ($status != 3 && $status != $statusnow)
			return null;

		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '猜你喜欢',
			'status' => $statusnow,
			'available' => $available,
			'empty' => '-',
			'paused' => $paused,
			'parent' => '',
			'url' => '/index.php/Sj/SoftRecommed/import_softs',
			'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/PR",
			'listurl' => "/index.php/Sj/AdList/index/search_key/" . urlencode("猜你喜欢"),
		);
		return $data;
	}

	function exportExcel($month, $data, $extent)
	{
		ini_set('memory_limit', -1);
		set_time_limit(600);
		//ini_set('display_errors', true);
		//error_reporting(E_ALL);
		require_once(dirname(__FILE__) . '/../../ORG/PHPExcel/PHPExcel.php');
		$excel = new PHPExcel();
		$excel->setActiveSheetIndex(0);
		$sheet = $excel->getActiveSheet();
		$style = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
		);
		$range = "A1:A2";
		$sheet->mergeCells($range);
		$sheet->setCellValue('A1',"                                                日期\r\n\r\n广告位");
		$sheet->getStyle($range)->applyFromArray($style);
		$sheet->getStyle($range) -> getBorders() -> setDiagonalDirection(PHPExcel_Style_Borders::DIAGONAL_DOWN);
		$sheet->getStyle($range)->getBorders()->getDiagonal()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$sheet->getStyle($range)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setWrapText(true);;
		$sheet->getRowDimension(1)->setRowHeight(22);
		$sheet->getRowDimension(2)->setRowHeight(22);
		$sheet->getColumnDimension('A')->setWidth(27);
		$i = 1;
		$month_range = array();
		$row = 3;
		$extentlist = array();
		foreach ($extent as $k => $v)
		{
			if (empty($v['parent']))
			{
				$extentlist[$k] = $v['name'];
				foreach ($v['children'] as $son)
				{
					$extentlist[$son] = '        ' . $extent[$son]['name'];
					foreach ($extent[$son]['children'] as $child)
					{
						$extentlist[$child] = '                ' . $extent[$child]['name'];
					}
				}
			}
		}
		foreach ($extentlist as $id => $v)
		{
			$name = $v;
			$range = "A" . $row . ":A" . ($row + 1);
			$sheet->mergeCells($range)->getStyle($range)
				->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$sheet->getStyle($range)->applyFromArray($style);
			$pos = $this->getExcelStringFromIndex(0, $row);
			$sheet->setCellValue($pos, $name);
			$row += 2;
		}
		foreach ($data as $k => $v)
		{
			$month = date('Y-m', $k);
			if (isset($month_range[$month]))
				$month_range[$month][] = $i;
			else
				$month_range[$month] = array($i);
			$pos = $this->getExcelStringFromIndex($i, 2);
			$sheet->setCellValue($pos, date('d', $k))->getStyle($pos)->applyFromArray($style);
			$sheet->getColumnDimension($this->getExcelStringFromIndex($i, ''))->setWidth(4.5);
			$sheet->getStyle($pos)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$row = 3;
			foreach ($extentlist as $id => $name)
			{
				$full = $v[$id];
				$cell_bottom = $this->getExcelStringFromIndex($i, $row + 1);
				$cell_top = $this->getExcelStringFromIndex($i, $row);
				$styleArray = array(
					'borders' => array(
						'outline' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
							'color' => array('argb' => 'FF000000'),
						),
						'inline' => array(
							'style' => PHPExcel_Style_Border::BORDER_NONE,
							'color' => array('argb' => 'FFFFFFFF'),
						),
					),
				);
				$sheet->getStyle("$cell_top:$cell_bottom")->applyFromArray($styleArray);


				if ($full != 0)
				{
					$sheet->getStyle($cell_bottom)->getFill()
						->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
						->getStartColor()->setARGB('FF00FF00');
					if ($full == 2)
					{
						$sheet->getStyle($cell_top)->getFill()
							->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
							->getStartColor()->setARGB('FF00FF00');
					}
				}
				$row += 2;
			}
			$i++;
		}
		foreach ($month_range as $month => $range)
		{
			$start = min($range);
			$end = max($range);
			$combine = $this->getExcelStringFromIndex($start, 1) . ":" . $this->getExcelStringFromIndex($end, 1);
			$sheet->mergeCells($combine)->setCellValue($this->getExcelStringFromIndex($start, 1), $month);
			$sheet->getStyle($combine)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$sheet->getStyle($combine)->applyFromArray($style);
		}

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . urlencode('广告排期表') . '.xlsx"');
		header('Cache-Control: max-age=0');
		$excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		//$excelWriter->save('/media/sf_E_DRIVE/temp/test.xlsx');
		$excelWriter->save('php://output');
		exit;
	}

	function getDownloadRecommendData($time, $status = 3, $key = '') {
		$prefix = 'DR';
		if (empty($time))
			return false;
		$dr_model = M('download_recommend');
		$drs_model = M('download_recommend_soft');
		$soft_model = M('soft');
		$map = array(
			'status' => 1,
		);
		$drlist = $dr_model->where($map)->select();
		if (empty($drlist))
			return null;
		foreach ($drlist as $k => $v) {
			$map = array(
				'package' => $v['package'],
			);
			$soft = $soft_model->where($map)->order('softid desc')->find();
			$name = $soft['softname'];
			if (!empty($key) && (stripos($name, $key) === false)) {
				unset($drlist[$k]);
			} else {
				$drlist[$k]['softname'] = $name;
			}
		}
		$statusall = 1;
		$statuspart = 0;
		$availableall = 0;
		$emptyall = 0;
		$pausedall = 0;
		$idlist = array();
		foreach ($drlist as $v)
		{
			$idlist[] = $v['id'];
		}
		$map = array(
			'recommend_id' => array('in', $idlist),
			'start_tm' => array('ELT', $time),
			'end_tm' => array('EGT', $time),
		);
		$map['status'] = 2;
		$res = $drs_model->where($map)->group('recommend_id')->field('recommend_id, count(*) as c')->select();
		$pauselist = array();
		foreach ($res as $v)
		{
			$pauselist[$v['recommend_id']] = $v['c'];
		}
		$map['status'] = 1;
		$res = $drs_model->where($map)->group('recommend_id')->field('recommend_id, count(*) as c')->select();
		$availlist = array();
		foreach ($res as $v)
		{
			$availlist[$v['recommend_id']] = $v['c'];
		}
		foreach ($drlist as $v)
		{

			$available = isset($availlist[$v['id']]) ? $availlist[$v['id']] : 0;
			$paused = isset($pauselist[$v['id']]) ? $pauselist[$v['id']] : 0;

			if ($available == 0)
			{
				$statusnow = 0;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statusall = 0;
			}
			else
			{
				$statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				$statuspart = 1;
			}
			$data[$prefix . $v['id']] = array(
				'id' => $prefix . $v['id'],
				'name' => $v['softname'],
				'status' => $statusnow,
				'available' => $available,
				'empty' => '-',
				'paused' => $paused,
				'parent' => $prefix . '0',
				'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/DR/location3/{$v['id']}",
				'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['extent_name']}",
			);
			$availableall += $available;
			$pausedall += $paused;
		}
		if (empty($data))
			return null;
		$statusfinal = $statuspart + $statusall;
		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '下载推荐',
			'status' => $statusfinal,
			'available' => $availableall,
			'empty' => '-',
			'paused' => $pausedall,
			'parent' => '',
			'url' => '/index.php/Sj/Downloadrecommend/import_softs',
		);
		return $data;
	}

	function getSearchAladinData($time, $status = 3, $key = '') {
		$prefix = 'SA';
		if (!empty($key) && (stripos('搜索阿拉丁', $key) === false))
			return false;
		if (empty($time))
			return false;
		$aladin_model = M('soft_associate_hot_word');
		$map = array(
			'stat' => 1,
			'begin' => array('ELT', $time),
			'end' => array('EGT', $time),
		);
		$available = $aladin_model->where($map)->count();
		$map = array(
			'stat' => 2,
			'begin' => array('ELT', $time),
			'end' => array('EGT', $time),
		);
		$paused = $aladin_model->where($map)->count();
		($available == 0) ? $statusnow = 0 : $statusnow = 2;
		if ($status != 3 && $status != $statusnow)
			return null;

		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '搜索阿拉丁',
			'status' => $statusnow,
			'available' => $available,
			'empty' => '-',
			'paused' => $paused,
			'parent' => '',
			'url' => '/index.php/Sj/Searchkeywords/import_softs_soft_hot_words',
			'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/SA",
			'listurl' => "/index.php/Sj/AdList/index/search_key/" . urlencode("搜索阿拉丁"),
		);
		return $data;
	}

	function getSearchSuggestData($time, $status = 3, $key = '') {
		$prefix = 'SS';
		if (!empty($key) && (stripos('搜索suggest', $key) === false))
			return false;
		if (empty($time))
			return false;
		$think_words_model = M('think_words');
		$map = array(
			'status' => 1,
			'start_time' => array('ELT', $time),
			'end_time' => array('EGT', $time),
		);
		$available = $think_words_model->where($map)->count();
		$map = array(
			'status' => 2,
			'start_time' => array('ELT', $time),
			'end_time' => array('EGT', $time),
		);
		$paused = $think_words_model->where($map)->count();
		($available == 0) ? $statusnow = 0 : $statusnow = 2;
		if ($status != 3 && $status != $statusnow)
			return null;

		$data[$prefix . '0'] = array(
			'id' => $prefix . '0',
			'name' => '搜索suggest',
			'status' => $statusnow,
			'available' => $available,
			'empty' => '-',
			'paused' => $paused,
			'parent' => '',
			'url' => '/index.php/Sj/Searchthinkword/import_softs_suggest',
			'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/SS",
			'listurl' => "/index.php/Sj/AdList/index/search_key/" . urlencode("搜索阿拉丁"),
		);
		return $data;
	}

	function getExcelStringFromIndex($a, $b)
	{
		return PHPExcel_Cell::stringFromColumnIndex($a) . $b;
	}

	function getCategoryTypes()
	{
		$map = array(
			'' => '全部',
			'top_new' => '最新',
			'top_hot' => '最热',
			'top_1d' => '日排行',
			'top_1d_1' => '应用日排行',
			'top_1d_2' => '游戏日排行',
			'top_7d' => '周排行',
			'top_30d' => '月排行',
			'olgame_down_5' => '下载最多',
			'olgame_hot_5' => '网游精选',
			'fixed_olgame' => '网游',//新增数据，以'fixed_'开头
			'fixed_offlinegame' => '单机',//新增数据，以'fixed_'开头
			'fixed_discovery_recommend' =>'发现-推荐',
		);

		$category = D('Sj.Category');
		$category_list = $category->getCategoryArray();
		foreach ($category_list as $v) {
			$map['top_' . $v['category_id'] . '_new'] = $v['name'] . '-最新';
			$map['top_' . $v['category_id'] . '_hot'] = $v['name'] . '-最热';
		}
		$tags_model = D("Sj.Tags");
		$tags = $tags_model->getTaglistbylike('', 0, 99999999);
		foreach ($tags as $v) {
			$map['tag_' . $v['tag_id'] . '_hot'] = "标签-{$v['tag_name']}-最热";
		}
		$bd_list = $category->getBdArray();
		foreach ($bd_list as $v) 
		{
			if($v['from_chl']==1)
			{
				$map['bdlist_' . $v['id']] = '榜单-应用-'.$v['name'];
			}
			else
			{
				$map['bdlist_' . $v['id']] = '榜单-'.$v['name'];
			}
		}

		// 获得所有有效的三级分类
		$third_level_category = $category->getThirdLevelCatgoryList();
		// 第一步：与关键字匹配上的三级分类记录
		$match_categorys = array();
		foreach ($third_level_category as $third) {
			$match_categorys[] = $third;
		}
		foreach ($match_categorys as $v) {
			if (!$v['tag_ids'])
				continue;
			$tag_ids = $v['tag_ids'];
			$tag_arr = explode(',', $tag_ids);
			foreach ($tag_arr as $tag_id) {
				if (!$tag_id)
					continue;
				$tag_id = ltrim($tag_id, "j");
				$tag_name = $tags_model->getTagnamebyid($tag_id);
				if ($tag_name) {
					$str = "{$v['name']}-{$tag_name}-{$suf}";
					$map['commontag_' . $v['category_id'] . '_' . $tag_id . '_new'] = "常用标签-{$v['name']}-{$tag_name}-最新";
					$map['commontag_' . $v['category_id'] . '_' . $tag_id . '_hot'] = "常用标签-{$v['name']}-{$tag_name}-最热";
				}
			}
		}
		return $map;
	}
	//搜索提示运营
	function getSearchTipsData($time, $status = 3, $key = '') 
	{
		$prefix = 'ST';
		if (empty($time))
			return false;
		$data = array();
		$statuspart = 0;
		$statusall = 1;
		$availableall = 0;

		$searchtips_model = M('search_tips');
		$searchtipspkg_model = M('search_tips_package');
		$map = array(
			'status' => 1,
			'start_tm' => array('ELT', $time),
		);
		if (!empty($key))
			$map['srh_key'] = array('exp', " like '%$key%'");
		$keylist = $searchtips_model->where($map)->order('update_tm desc')->select();
		
		if (!empty($keylist))
		{
			$statusallsub = 1;
			$statuspartsub = 0;
			$availablesub = 0;
			$pausedsub = 0;
			$idlist = array();
			foreach ($keylist as $v)
			{
				$idlist[] = $v['id'];
			}
			//暂停的软件
			$map = array(
				'kid' => array('in', $idlist),
				'start_tm' => array('ELT', $time),
				'end_tm' => array('EGT', $time),
			);
			$map['status'] = 2;
			$res = $searchtipspkg_model->where($map)->group('kid')->field('kid, count(*) as c')->select();
			$pauselist = array();
			foreach ($res as $v)
			{
				$pauselist[$v['kid']] = $v['c'];
			}
			//有效的软件
			$map['status'] = 1;
			$res = $searchtipspkg_model->where($map)->group('kid')->field('kid, count(*) as c')->select();
			$availlist = array();
			foreach ($res as $v)
			{
				$availlist[$v['kid']] = $v['c'];
			}
			
			foreach ($keylist as $v)
			{
				$available = isset($availlist[$v['id']]) ? $availlist[$v['id']] : 0;
				$paused = isset($pauselist[$v['id']]) ? $pauselist[$v['id']] : 0;
				($available == 0) ? $statusnow = 0 : $statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				if ($statusnow == 2)
				{
					$statuspartsub = 1;
					$statuspart = 1;
				}
				else
				{
					$statusallsub = 0;
					$statusall = 0;
				}
				$availablesub += $available;
				$availableall += $available;
				$pausedsub += $paused;
				$pausedall += $paused;
				$data[$prefix . $v['id']] = array(
					'id' => $prefix . $v['id'],
					'name' => $v['srh_key'],
					'status' => $statusnow,
					'available' => $available,
					'empty' => '-',
					'paused' => $paused,
					'parent' => $prefix . '0',
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/ST/location3/{$v['id']}",
					'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['srh_key']}",
				);
			}
			if (!empty($data))
			{
				$statussub = $statusallsub + $statuspartsub;
				$data[$prefix . '0'] = array(
					'id' => $prefix . '0',
					'name' => '搜索提示运营',
					'status' => $statussub,
					'available' => $availablesub,
					'empty' => '-',
					'paused' => $pausedsub,
					'parent' => '',
					'url' => '/index.php/Sj/Search_tips/import_softs',
					'jumpurl' => '/index.php/Sj/AdDetail/index/status/0/key_type/package/pid/1/location1/ST/page_type/1/location3/tips/',	
				);
			}
		}
		return $data;
	}
	//搜索相关词管理
	function getSearchRelatedData($time, $status = 3, $key = '') 
	{
		$prefix = 'SR';
		if (empty($time))
			return false;
		$data = array();
		$statuspart = 0;
		$statusall = 1;
		$availableall = 0;

		$searchrelated_model = M('search_related');
		$searchrelatedcontent_model = M('search_related_content');
		$map = array(
			'status' => 1,
			'start_tm' => array('ELT', $time),
		);
		if (!empty($key))
			$map['srh_key'] = array('exp', " like '%$key%'");
		$keylist = $searchrelated_model->where($map)->order('update_tm desc')->select();
		
		if (!empty($keylist))
		{
			$statusallsub = 1;
			$statuspartsub = 0;
			$availablesub = 0;
			$pausedsub = 0;
			$idlist = array();
			foreach ($keylist as $v)
			{
				$idlist[] = $v['id'];
			}
			//暂停的软件
			$map = array(
				'kid' => array('in', $idlist),
				'start_tm' => array('ELT', $time),
				'end_tm' => array('EGT', $time),
			);
			$map['status'] = 2;
			$res = $searchrelatedcontent_model->where($map)->group('kid')->field('kid, count(*) as c')->select();
			$pauselist = array();
			foreach ($res as $v)
			{
				$pauselist[$v['kid']] = $v['c'];
			}
			//有效的软件
			$map['status'] = 1;
			$res = $searchrelatedcontent_model->where($map)->group('kid')->field('kid, count(*) as c')->select();
			$availlist = array();
			foreach ($res as $v)
			{
				$availlist[$v['kid']] = $v['c'];
			}
			
			foreach ($keylist as $v)
			{
				$available = isset($availlist[$v['id']]) ? $availlist[$v['id']] : 0;
				$paused = isset($pauselist[$v['id']]) ? $pauselist[$v['id']] : 0;
				($available == 0) ? $statusnow = 0 : $statusnow = 2;
				if ($status != 3 && $status != $statusnow)
					continue;
				if ($statusnow == 2)
				{
					$statuspartsub = 1;
					$statuspart = 1;
				}
				else
				{
					$statusallsub = 0;
					$statusall = 0;
				}
				$availablesub += $available;
				$availableall += $available;
				$pausedsub += $paused;
				$pausedall += $paused;
				$data[$prefix . $v['id']] = array(
					'id' => $prefix . $v['id'],
					'name' => $v['srh_key'],
					'status' => $statusnow,
					'available' => $available,
					'empty' => '-',
					'paused' => $paused,
					'parent' => $prefix . '0',
					'jumpurl' => "/index.php/Sj/AdDetail/index/status/2/location1/SR/location3/{$v['id']}",
					'listurl' => "/index.php/Sj/AdList/index/search_key/{$v['srh_key']}",
				);
			}
			if (!empty($data))
			{
				$statussub = $statusallsub + $statuspartsub;
				$data[$prefix . '0'] = array(
					'id' => $prefix . '0',
					'name' => '搜索相关词',
					'status' => $statussub,
					'available' => $availablesub,
					'empty' => '-',
					'paused' => $pausedsub,
					'parent' => '',
					'url' => '/index.php/Sj/Search_related/import_softs',
					'jumpurl' => '/index.php/Sj/AdDetail/index/status/0/key_type/package/pid/1/location1/SR/page_type/1/location3/tips/',
				);
			}
		}
		return $data;
	}
}
