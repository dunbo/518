<?php
header("Content-type: text/html; charset=utf-8");
class AdDetailAction extends CommonAction {
	private $soft_model;

	function __construct() {
		parent::__construct();
		$this->soft_model = M('soft');
	}

	function index() {
		ini_set('display_errors', true);
		ini_set('memory_limit','1024M');
		$pid = !empty($_GET['pid']) ? $_GET['pid'] : 1;
		$page_type = !empty($_GET['page_type']) ? $_GET['page_type'] : 0;
		if (empty($_GET['location1']))
		{
			if (empty($_GET['location2']) && empty($_GET['location3']))
			{
				$location1 = 'EX';
				//$location3 = '';
				$location3 = '-1';
			}
			else
				$this->error('错误的请求');
		}
		else
		{
			$location1 = $_GET['location1'];
			$location2 = isset($_GET['location2']) ? $_GET['location2'] : '';
			$location3 = isset($_GET['location3']) ? $_GET['location3'] : '';
		}
		import("@.ORG.Page");
		$action = isset($_GET['action']) ? $_GET['action'] : '';

		if ($action != 'export')
		{
			$limit = isset($_GET['lr']) ? $_GET['lr'] : 20;
		}
		else
		{
			$limit = 9999999;
		}
		$param = http_build_query($_GET);
		$status = isset($_GET['status']) ? $_GET['status'] : 0;
		$start_tm = !empty($_GET['start_tm']) ? strtotime($_GET['start_tm']) : 0;
		$end_tm = !empty($_GET['end_tm']) ? (strtotime($_GET['end_tm']) + 86399) : 0;
		if ($start_tm == 0 && $end_tm == 0)
			$start_tm = time();
		if (!empty($end_tm) && !empty($start_tm) && $end_tm < $start_tm)
		{
			$this->error("开始时间不能大于结束时间");
		}
		$package = '';
		$softname = '';
		$keyword = '';
		$search_key = isset($_GET['keyword']) ? $_GET['keyword'] : '';
		$key_type = isset($_GET['key_type']) ? $_GET['key_type'] : '';
		$is_accurate = isset($_GET['is_accurate']) ? $_GET['is_accurate'] : '';
		if (!empty($key_type))
		{
			switch ($key_type)
			{
				case 'package':
					$package = $search_key;
					break;

				case 'softname':
					$softname = $search_key;
					break;

				case 'keyword':
					$keyword = $search_key;
					break;

				default;
					$this->error('错误的请求');
			}
		}
		$tpl = '';
		switch ($location1)
		{
			case 'EX':
				$count = $this->getExtentDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getExtentDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_extent';
				break;

			case 'FE':
				$count = $this->getFeatureDetail('count', $status, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getFeatureDetail('detail', $status, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_feature';
				break;

			case 'AH':
				$count = $this->getCategoryDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'GH':
				$count = $this->getCategoryDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'CA':
				$count = $this->getCategoryDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'TN':
				$count = $this->getCategoryDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'SK':
				switch ($location2) {
					case 'keyword':
						$count = $this->getKeywordDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate);
						$page = new Page($count, $limit, $param);
						$res = $this->getKeywordDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate, $page->firstRow, $limit);
						$list = $res;
						$tpl = 'index_keyword';
						break;

					case 'default':
						$count = $this->getDefaultKeywordDetail('count', $status, $start_tm, $end_tm, $keyword);
						$page = new Page($count, $limit, $param);
						$res = $this->getDefaultKeywordDetail('detail', $status, $start_tm, $end_tm, $keyword, $page->firstRow, $limit);
						$list = $res;
						$tpl = 'index_default';
						break;

					case 'hotword':
						$count = $this->getHotwordDetail('count', $status, $start_tm, $end_tm, $keyword);
						$page = new Page($count, $limit, $param);
						$res = $this->getHotwordDetail('detail', $status, $start_tm, $end_tm, $keyword, $page->firstRow, $limit);
						$list = $res;
						$tpl = 'index_hotword';
						break;

					case 'skeyword':
						$count = $this->getSKeywordDetail('count', $status, $start_tm, $end_tm, $keyword);
						$page = new Page($count, $limit, $param);
						$res = $this->getSKeywordDetail('detail', $status, $start_tm, $end_tm, $keyword, $page->firstRow, $limit);
						$list = $res;
						$tpl = 'index_skeyword';
						break;
				}
				break;

			case 'NE':
				$count = $this->getNecessaryDetail('count', $status, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getNecessaryDetail('detail', $status, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_necessary';
				break;

			case 'PR':
				$count = $this->getCategoryDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);

				//$count = $this->getPreferDetail('count', $status, $start_tm, $end_tm, $package, $softname);
				//$page = new Page($count, $limit, $param);
				//$res = $this->getPreferDetail('detail', $status, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_prefer';
				break;

			case 'DR':
				/*$count = $this->getCategoryDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);*/

				$count = $this->getDownloadRecommendDetail('count', $status, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$page = new Page($count, $limit, $param);
				$res = $this->getDownloadRecommendDetail('detail', $status, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_download_recommend';
				break;

			case 'SA':
				$count = $this->getSearchAladinDetail('count', $status, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate);
				$page = new Page($count, $limit, $param);
				$res = $this->getSearchAladinDetail('detail', $status, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_search_aladin';
				break;

			case 'SS':
				$count = $this->getSearchSuggestDetail('count', $status, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate);
				$page = new Page($count, $limit, $param);
				$res = $this->getSearchSuggestDetail('detail', $status, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_search_suggest';
				break;

			case 'LA':
				$count = $this->getLadingDetail('count', $status, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getLadingDetail('detail', $status, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_lading';
				break;

			case 'ST':  //搜索运营提示
				$count = $this->getSearchTipsDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getSearchTipsDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_search_tips';
				break;

			case 'SR':  //V6.4搜索相关词
				$count = $this->getSearchRelatedDetail('count', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname);
				$page = new Page($count, $limit, $param);
				$res = $this->getSearchRelatedDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $page->firstRow, $limit);
				$list = $res;
				$tpl = 'index_search_related';
				break;
			default:
				$this->error('错误的请求');
		}

		$adpos = $this->getAdPosition($pid);
		$schedule_url = "/index.php/Sj/AdList/schedule/search_key/";
		switch ($location1)
		{
			case 'PR':
				$name = '猜你喜欢';
				break;

			case 'SK':
				switch ($location2)
				{
					case 'hotword':
						$name = '搜索热词管理V4';
						break;

					case 'default':
						$name = '默认关键字管理V4';
						break;

					case 'keyword';
						foreach ($adpos[$location1][$location2] as $v)
							if ($v['id'] == $location3)
								$name = $v['name'];

					case 'skeyword':
						$name = '搜索热词推荐';
						break;
				}
				break;

			case 'CA':
				foreach ($adpos[$location1][$location2] as $v)
					if ($v['id'] == $location3)
						$name = $v['name'];
				break;

			default:
				/*if ($location1 == 'EX' && $location3==-1)
				{
					$tmp = reset($adpos['EX']);
					$name = $tmp['name'];
				}*/
				foreach ($adpos[$location1] as $v)
					if ($v['id'] == $location3)
						$name = $v['name'];
				break;
		}
		$schedule_url .= urlencode($name);
		if (!empty($start_tm))
		{
			$schedule_url .= "/year/" . date('Y', $start_tm) . "/month/" . date('m', $start_tm);
		}
		//合作样式
		$util = D("Sj.Util");
		foreach($list as $key=>$val)
		{
			if($location2=='keyword' || $location1 == 'ST'|| $location1 == 'SR')
			{
				$typelist = $util->getHomeExtentSoftTypeList($val['co_type']);
			}
			else
			{
				$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			}
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$list[$key]['types'] = $v[0];
				}
			}
		}
		$this->assign('schedule_url', $schedule_url);
		$this->assign('adpos', $adpos);
		$this->assign('list', $list);
		$this->assign('status', $status);
		$this->assign('keyword', $search_key);
		$this->assign('key_type', $key_type);
		$this->assign('location1', $location1);
		$this->assign('location2', $location2);
		$this->assign('location3', $location3);
		$this->assign('start_tm', $_GET['start_tm']);
		$this->assign('end_tm', $_GET['end_tm']);
		$this->assign('pid', $pid);
		$this->assign('page_type', $page_type);
		$this->assign('is_accurate', $is_accurate);
		if ($action != 'export')
		{
			$this->assign('page', $page->show());
			$this->display($tpl);
		}
		else
		{
			$category_types = $this->getCategoryTypes();
			switch ($location1)
			{
				case 'EX':
					$position = "首页推荐-";
					$filename = "首页推荐";
					break;

				case 'FE':
					$position = "专题-[$name]";
					$filename = "专题";
					break;

				case 'AH':
					$position = "应用热门-";
					$filename = "应用热门";
					break;

				case 'GH':
					$position = "游戏热门-";
					$filename = "游戏热门";
					break;

				case 'CA':
					$position = "类别置顶-[{$category_types[$location2]}]-";
					$filename = "类别置顶";
					break;

				case 'TN':
					$position = "最新-";
					$filename = "最新";
					break;

				case 'SK':
					$filename = "搜索热词";
					switch ($location2) {
						case 'keyword':
							$position = "搜索热词-[搜索关键字列表]-[$name]";
							break;

						case 'default':
							$position = "搜索热词-[$name]";
							break;

						case 'hotword':
							$position = "搜索热词-[$name]";
							break;
					}
					break;

				case 'NE':
					$position = "必备-[$name]";
					$filename = "必备";
					break;

				case 'PR':
					$position = $name;
					$filename = $name;
					break;

				case 'DR':
					$position = "下载推荐";
					$filename = "下载推荐";
					break;
				case 'SA':
					$position = "搜索阿拉丁";
					$filename = "搜索阿拉丁";
					break;
				case 'SS':
					$position = "搜索suggest";
					$filename = "搜索suggest";
					break;
				case 'ST':
					$position = "搜索提示运营";
					$filename = "搜索提示运营";
					break;
				case 'SR':
					$position = "搜索相关词";
					$filename = "搜索相关词";
					break;
			}
			$this->assign('position', $position);
			$html = $this->fetch($tpl . "_csv");
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=" . urlencode($filename) . '_' . time() . sprintf("%05d", rand(0, 99999)) . ".csv");
			exit(iconv('utf-8', 'gbk//IGNORE', $html));
		}
	}

	function getHtmlDetail() {
		ini_set('display_errors', true);
		error_reporting(E_ALL);
		if (empty($_GET['location']))
		{
			exit;
		}
		else
		{
			$location1 = substr($_GET['location'], 0, 2);
			$location3 = substr($_GET['location'], 2);
		}
		if (empty($_GET['date']))
		{
			exit;
		}
		else
		{
			$start_tm = isset($_GET['date']) ? $_GET['date'] : 0;
			$end_tm = $start_tm + 86399;
		}
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
		$limit = 999999;
		$status = 4;
		$package = '';
		$softname = '';
		$keyword = '';
		$tpl = '';
		var_dump($location1);
		switch ($location1)
		{
			case 'EX':
				$res = $this->getExtentDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_extent';
				break;

			case 'FE':
				$res = $this->getFeatureDetail('detail', $status, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_feature';
				break;

			case 'AH':
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'GH':
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'CA':
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'TN':
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_category';
				break;

			case 'SK':
				$res = $this->getKeywordDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, $keyword, '', 0, $limit);
				$list = $res;
				$tpl = 'index_keyword';
				break;

			case 'NE':
				$res = $this->getNecessaryDetail('detail', $status, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_extent';
				break;

			case 'PR':
				$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				//$res = $this->getPreferDetail('detail', $status, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_prefer';
				break;

			case 'DR':
				//$res = $this->getCategoryDetail('detail', $status, $pid, $location3, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$res = $this->getDownloadRecommendDetail('detail', $status, $start_tm, $end_tm, $package, $softname, 0, $limit);
				$list = $res;
				$tpl = 'index_download_recommend';
				break;

			default:
				$this->error('错误的请求');
		}

		$this->assign('list', $list);
		foreach ($list as $v)
		{
			//echo "<p>软件名称  ：{$v['softname']}</p>";
			//if ($location1 == 'SK')
			//	echo "<p>包名/页面：";
			//else
			//	echo "<p>包名　　  ：";
			//echo "{$v['package']}</p>";
			//echo "<p>开始时间  ：" . date('Y-m-d H:i:s', $v['start_tm']) . "</p>";
			//echo "<p>结束时间  ：" . date('Y-m-d H:i:s', $v['end_tm']) . "</p>";
			//echo "<p>----------------------------------------</p>";
			echo "软件名称  ：{$v['softname']}\r\n";
			if ($location1 == 'SK')
				echo "包名/页面：";
			else
				echo "包名　　  ：";
			echo "{$v['package']}\r\n";
			echo "开始时间  ：" . date('Y-m-d H:i:s', $v['start_tm']) . "\r\n";
			echo "结束时间  ：" . date('Y-m-d H:i:s', $v['end_tm']) . "\r\n";
			echo "----------------------------------------\r\n";
		}
		//$this->display($tpl);
	}

	function getExtentDetail($type, $status, $pid, $extent_id, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 10) {
		$timestamp = time();
		$extent_model = M('extent_v1');
		$extent_soft_model = M('extent_soft_v1');
		if (empty($extent_id))
		{
			$map = array(
				'pid' => $pid,
				#'status' => 1,
				'type' => array('exp', '!=2'),
				'parent_union_id' => '0',
				'parent_id' => array('exp', '=0 OR (parent_id in (select extent_id from sj_extent_v1 where type=2))'),
			);
			$extent_first = $extent_model->where($map)->order('rank')->find();
			$extent_id = $extent_first['extent_id'];
		}
		else
		{
			if($extent_id!=-1)
			{
				$map = array(
					'extent_id' => $extent_id,
					'type' => array('exp', '!=2'),
					#'status' => 1,
				);
				$extent = $extent_model->where($map)->find();
				if (empty($extent))
					return false;
			}
		}
		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;

		if ($extent_id==-1) {
			$where_str = "1=1";
		} else {
			$where_str = "a.extent_id = {$extent_id}";
		}
		if (!empty($package))
			$where_str .= " and a.package='$package'";
		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_at');
		// if($end_tm>time() || empty($end_tm)){
		// 	$where_str_two.=" and a.end_at <= ".time();
		// }else{
		// 	$where_str_two.=" and a.end_at <= ".$end_tm;
		// }
		// if(!empty($start_tm)&&$start_tm<time()){
		// 	$where_str_two.=" and a.start_at >= ".$start_tm;
		// }


		if (!empty($start_tm))
			$where_str .= " and a.end_at >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_at <= {$end_tm}";
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_at>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_at<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.extent_name from sj_extent_soft_v1 as a join sj_extent_v1 b on a.extent_id=b.extent_id where {$where_str}";
		}else{
			$sql = "select a.*,b.extent_name from sj_extent_soft_v1 as a join sj_extent_v1 b on a.extent_id=b.extent_id where {$where_str_two}";
		}
		$data = array();
		$res = $extent_soft_model->query($sql);
		// echo "<pre>";var_dump($res);
		// var_dump($res);
		// echo $extent_soft_model->getLastSql();die;
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_at'] <= $timestamp)
				{
					if ($v['end_at'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'softname' => $tmpname,
				'package' => $v['package'],
				'prob' => $v['prob'],
				'start_tm' => $v['start_at'],
				'end_tm' => $v['end_at'],
				'type' => $v['type'],
				'extent_name' => $v['extent_name'],
				'status' => $soft_status,
				'editurl' => '/index.php/Sj/ExtentV1/edit_soft/id/' . $v['id'],
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getFeatureDetail($type, $status, $location3, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 0) {
		$timestamp = time();
		$feature_soft_model = M('feature_soft');

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		if (empty($location3)) {
			return false;
		}
		$where_str = "a.feature_id={$location3}";
		$where_str .= " and a.status!=0";
		if (!empty($package))
			$where_str .= " and a.package='$package'";
		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_tm');
		if (!empty($start_tm))
			$where_str .= " and a.end_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_tm <= {$end_tm}";
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_tm>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_tm<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.* from sj_feature_soft as a inner join sj_feature as b on a.feature_id=b.feature_id and b.status=1 where {$where_str} order by rank";
		}else{
			$sql = "select a.* from sj_feature_soft as a inner join sj_feature as b on a.feature_id=b.feature_id and b.status=1 where {$where_str_two} order by rank";
		}
		$data = array();
		$res = $feature_soft_model->query($sql);
		//图文段落转化
		$section = array('','一','二','三','四','五','六','七','八','九','十');
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_tm'] <= $timestamp)
				{
					if ($v['end_tm'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			//图文段落转化
			if($v['feature_graphic_id'])
			{
				$graphic_where=array(
					'status' =>1,
					'id' =>$v['feature_graphic_id'],
				);
				$graphic_find = $feature_soft_model->table('sj_feature_graphic')->where($graphic_where)->find();
				$section_name = "图文第".$section[$graphic_find['rank']]."段";
			}
			else
			{
				$section_name = "";
			}
			$data[] = array(
				'id' => $v['id'],
				'graphic_section' => $section_name,
				'softname' => $tmpname,
				'package' => $v['package'],
				'intro' => $v['remark'],
				'start_tm' => $v['start_tm'],
				'end_tm' => $v['end_tm'],
				'rank' => $v['rank'],
				'status' => $soft_status,
				'type' => $v['type'],
				'recommend_soft_name' => $v['recommend_soft_name'],
				'recommend_reason' => $v['recommend_reason'],
				'recommend_person' => $v['recommend_person'],
				'editurl' => "/index.php/Sj/Advertisement/feature_soft_edit/feature_id/{$v{'feature_id'}}/id/{$v['id']}",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getLadingDetail($type, $status, $location3, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 0)
	{
		$timestamp = time();
		$lading_soft_model = M('lading_soft');

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		if (empty($location3)) {
			return false;
		}
		$where_str = "a.status!=0";

		if ($location3!=-1)
		{
			$where_str .= " and a.category_id={$location3}";
		}
		if (!empty($package))
			$where_str .= " and a.package='$package'";
		// echo $where_str;echo "<br>";
		$where_str_two=$where_str;
		if (!empty($start_tm))
			$where_str .= " and a.end_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_tm <= {$end_tm}";
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_tm');
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_tm>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_tm<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.category_name from sj_lading_soft as a inner join sj_lading_category as b on a.category_id=b.id and b.status=1 where {$where_str} order by rank";
		}else{
			$sql = "select a.*,b.category_name from sj_lading_soft as a inner join sj_lading_category as b on a.category_id=b.id and b.status=1 where {$where_str_two} order by rank";
			// echo $where_str_two;echo "<br>";
		}
		$data = array();
		$res = $lading_soft_model->query($sql);
		// echo $lading_soft_model->getLastSql();die;
		foreach ($res as $v)
		{
			$channelname = $this ->getChannelByCategoryID($v['category_id']);
			//运营临时需求 渠道是安智市场内部测试时，列表中不显示批量管理
			if($channelname=="安智市场内部测试")
			{
				break;
			}
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_tm'] <= $timestamp)
				{
					if ($v['end_tm'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'chname' => $channelname,
				'softname' => $tmpname,
				'package' => $v['package'],
				'recommend' => $v['recommend'],
				'start_tm' => $v['start_tm'],
				'end_tm' => $v['end_tm'],
				'rank' => $v['rank'],
				'status' => $soft_status,
				'category_name' => $v['category_name'],
				'type' => $v['type'],
				'editurl' => "/index.php/Sj/Ladingmanage/edit_soft_show/category_id/{$v{'category_id'}}/id/{$v['id']}",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getCategoryDetail($type, $status, $pid, $extent_id, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 10) {
		$timestamp = time();
		$extent_model = M('category_extent');
		$extent_soft_model = M('category_extent_soft');
		if (empty($extent_id))
		{
			return false;
		}

		if(is_numeric($extent_id))
		{
			$map = array(
				'pid' => $pid,
				'extent_id' => $extent_id,
				'type' => array('exp', '!=2'),
				'status' => 1,
			);
			$extent = $extent_model->where($map)->find();
			if (empty($extent))
				return false;
		}


		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;


		if(is_numeric($extent_id))
		{
			$where_str = "a.extent_id = {$extent_id} and a.status!=0 and b.pid=".$pid;
		}else
		{
			$where_str = "b.category_type = '{$extent_id}' and a.status!=0 and b.pid=".$pid;
		}
		if (!empty($package))
			$where_str .= " and a.package='$package'";
		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_at');
		if (!empty($start_tm))
			$where_str .= " and a.end_at >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_at <= {$end_tm}";
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_at>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_at<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				// $where_str .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.extent_name from sj_category_extent_soft as a JOIN sj_category_extent b ON a.`extent_id`=b.`extent_id` and b.status=1 where {$where_str}";
		}else{
			$sql = "select a.*,b.extent_name from sj_category_extent_soft as a JOIN sj_category_extent b ON a.`extent_id`=b.`extent_id` and b.status=1 where {$where_str_two}";
		}
		$data = array();
		$res = $extent_soft_model->query($sql);
		// echo $extent_soft_model->getLastSql();
		// echo "<pre>";var_dump($res);
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_at'] <= $timestamp)
				{
					if ($v['end_at'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'softname' => $tmpname,
				'package' => $v['package'],
				'prob' => $v['prob'],
				'type' => $v['type'],
				'start_tm' => $v['start_at'],
				'end_tm' => $v['end_at'],
				'extent_name' => $v['extent_name'],
				'status' => $soft_status,
				'editurl' => "/index.php/Sj/CategoryExtent/edit_soft/id/{$v['id']}?modal=true",
			);
		}

		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getDefaultKeywordDetail($type, $status, $start_tm, $end_tm, $keyword, $start = 0, $limit = 0) {;
		$timestamp = time();
		$soft_defaultkeyword_model = M('soft_defaultkeyword');

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		switch ($status)
		{
			case 0:
				$where_str = "status!=0";
				break;

			case 1:
				$where_str = "status=1 and start_time>{$timestamp}";
				break;

			case 2:
				$where_str = "status=1 and start_time<={$timestamp}";
				break;

			case 3:
				$where_str = "status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if (!empty($start_tm))
			$where_str .= " and end_time >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and start_time <= {$end_tm}";
		if (!empty($keyword)){
			$where_str .= " and key_words like '%$keyword%'";
			$where_str_two .= " and key_words like '%$keyword%'";
		}


		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_time');
		switch ($type)
		{
			case 'detail':
				if($status!=4){
					$sql = "select * from sj_soft_defaultkeywords where {$where_str} limit {$start},{$limit}";
				}else{
					$sql = "select * from sj_soft_defaultkeywords where {$where_str_two} limit {$start},{$limit}";
				}
				$res = $soft_defaultkeyword_model->query($sql);
				if (empty($res))
				{
					return null;
				}
				else
				{
					$data = array();
					foreach ($res as $v)
					{
						if ($v['status'] == 2)
						{
							$soft_status = 3;
						}
						else
						{
							if ($v['start_time'] <= $timestamp)
							{
								if ($v['end_time'] < $timestamp)
									$soft_status = 4;
								else
									$soft_status = 2;
							}
							else
								$soft_status = 1;
						}
						$data[] = array(
							'id' => $v['key_id'],
							'keyword' => $v['key_words'],
							'weight' => $v['weight'],
							'start_tm' => $v['start_time'],
							'end_tm' => $v['end_time'],
							'add_tm' => $v['add_time'],
							'type' => $v['type'],
							'status' => $soft_status,
						);
					}
					return $data;
				}
				break;

			case 'count':
				if($status!=4){
					$sql = "select count(*) as c from sj_soft_defaultkeywords where {$where_str}";
				}else{
					$sql = "select count(*) as c from sj_soft_defaultkeywords where {$where_str_two}";
				}
				$result = $soft_defaultkeyword_model->query($sql);
				$count = $result[0]['c'];
				return $count;
				break;

			default:
				return false;
		}
	}

	function getHotwordDetail($type, $status, $start_tm, $end_tm, $keyword, $start = 0, $limit = 0) {;
		$timestamp = time();
		$soft_hotwords_model = M('soft_hotwords');

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		switch ($status)
		{
			case 0:
				$where_str = "status!=0";
				break;

			case 1:
				$where_str = "status=1 and start_time>{$timestamp}";
				break;

			case 2:
				$where_str = "status=1 and start_time<={$timestamp}";
				break;

			case 3:
				$where_str = "status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if (!empty($start_tm))
			$where_str .= " and end_time >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and start_time <= {$end_tm}";
		if (!empty($keyword)){
			$where_str .= " and hot_words like '%$keyword%'";
			$where_str_two .= " and hot_words like '%$keyword%'";
		}
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_time');
		switch ($type)
		{
			case 'detail':
				if($status!=4){
					$sql = "select * from sj_soft_hotwords where {$where_str} limit {$start},{$limit}";
				}else{
					$sql = "select * from sj_soft_hotwords where {$where_str_two} limit {$start},{$limit}";
				}
				$res = $soft_hotwords_model->query($sql);
				if (empty($res))
				{
					return null;
				}
				else
				{
					$data = array();
					foreach ($res as $v)
					{
						if ($v['status'] == 2)
						{
							$soft_status = 3;
						}
						else
						{
							if ($v['start_time'] <= $timestamp)
							{
								if ($v['end_time'] < $timestamp)
									$soft_status = 4;
								else
									$soft_status = 2;
							}
							else
								$soft_status = 1;
						}
						$data[] = array(
							'id' => $v['hot_id'],
							'keyword' => $v['hot_words'],
							'rank' => $v['location'],
							'start_tm' => $v['start_time'],
							'end_tm' => $v['end_time'],
							'add_tm' => $v['add_time'],
							'status' => $soft_status,
							'key_type' => $v['key_type'],
							'type' => $v['type'],
						);
					}
					return $data;
				}
				break;

			case 'count':
				if($status!=4){
					$sql = "select count(*) as c from sj_soft_hotwords where {$where_str}";
				}else{
					$sql = "select count(*) as c from sj_soft_hotwords where {$where_str_two}";
				}
				$result = $soft_hotwords_model->query($sql);
				$count = $result[0]['c'];
				return $count;
				break;

			default:
				return false;
		}
	}

	function getSKeywordDetail($type, $status, $start_tm, $end_tm, $keyword, $start = 0, $limit = 0) {;
		$timestamp = time();
		$skeywords_model = M('search_keywords');

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		switch ($status)
		{
			case 0:
				$where_str = "status!=0";
				break;

			case 1:
				$where_str = "status=1 and start_tm>{$timestamp}";
				break;

			case 2:
				$where_str = "status=1 and start_tm<={$timestamp}";
				break;

			case 3:
				$where_str = "status=2";
				break;

			case 4:
				$where_str_two = "a.status=1";
				break;
		}
		if (!empty($start_tm))
			$where_str .= " and end_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and start_tm <= {$end_tm}";
		if (!empty($keyword)){
			$where_str .= " and key_word like '%$keyword%'";
			$where_str_two .= " and key_word like '%$keyword%'";
		}
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_tm');
		switch ($type)
		{
			case 'detail':
				if($status!=4){
					$sql = "select * from sj_search_keywords where {$where_str} limit {$start},{$limit}";
				}else{
					$sql = "select * from sj_search_keywords as a where {$where_str_two} limit {$start},{$limit}";
				}
				$res = $skeywords_model->query($sql);
				// echo $skeywords_model->getLastSql();die;
				if (empty($res))
				{
					return null;
				}
				else
				{
					$data = array();
					foreach ($res as $v)
					{
						if ($v['status'] == 2)
						{
							$soft_status = 3;
						}
						else
						{
							if ($v['start_tm'] <= $timestamp)
							{
								if ($v['end_tm'] < $timestamp)
									$soft_status = 4;
								else
									$soft_status = 2;
							}
							else
								$soft_status = 1;
						}
						$data[] = array(
							'id' => $v['id'],
							'keyword' => $v['key_word'],
							'package' => $v['package'],
							'rank' => $v['rank'],
							'start_tm' => $v['start_tm'],
							'end_tm' => $v['end_tm'],
							'add_tm' => $v['update_tm'],
							'status' => $soft_status,
							'key_type' => $v['key_type'],
							'type' => $v['type'],
							'editurl' => '/index.php/Search/Advertisement/edit_searchkeywords_to_show',
						);
					}
					return $data;
				}
				break;

			case 'count':
				if($status!=4){
					$sql = "select count(*) as c from sj_search_keywords where {$where_str}";
				}else{
					$sql = "select count(*) as c from sj_search_keywords where {$where_str_two}";
				}
				$result = $skeywords_model->query($sql);
				$count = $result[0]['c'];
				return $count;
				break;

			default:
				return false;
		}
	}

	function getKeywordDetail($type, $status, $pid, $key_id, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate='', $start = 0, $limit = 10) {
		$timestamp = time();
		$search_key_model = M('search_key');
		$search_key_package_model = M('search_key_package');
		if (empty($key_id))
		{
			return false;
		}

		if(is_numeric($key_id))
		{
			$map = array(
				'pid' => $pid,
				'id' => $key_id,
				'status' => 1,
			);
			$extent = $search_key_model->where($map)->find();
			if (empty($extent))
				return false;
		}

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		if(is_numeric($key_id))
		{
			$where_str = "a.kid = {$key_id}";
		}else
		{
			$where_str = "1=1";
		}
		if (!empty($package))
			$where_str .= " and a.package='$package'";
		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'stop_tm');
		if (!empty($start_tm))
			$where_str .= " and a.stop_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_tm <= {$end_tm}";

		if (!empty($keyword)){
			$where_str .= ' and (';
			$keyword = explode('|', $keyword);
			foreach($keyword as $word){
				if(!empty($is_accurate)){
					//精确匹配
					$where_str .= " b.srh_key = '{$word}' or";
				}else{
					//模糊匹配
					$where_str .= " b.srh_key like '%{$word}%' or";
				}
			}
			$where_str = rtrim($where_str, ' or');
			$where_str .= ")";
		}
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_tm>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_tm<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.srh_key from sj_search_key_package as a join sj_search_key as b on a.kid =b.id and b.status=1 where {$where_str}";
		}else{
			$sql = "select a.*,b.srh_key from sj_search_key_package as a join sj_search_key as b on a.kid =b.id and b.status=1 where {$where_str_two}";
		}
		$data = array();
		$res = $search_key_package_model->query($sql);
		$category_types = $this->getCategoryTypes();
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_tm'] <= $timestamp)
				{
					if ($v['stop_tm'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			if ($v['type'] == 1)
			{
				$v['package'] = $category_types[$v['package']];
			}
			$data[] = array(
				'id' => $v['id'],
				'softname' => $tmpname,
				'package' => $v['package'],
				'rank' => $v['pos'],
				'start_tm' => $v['start_tm'],
				'end_tm' => $v['stop_tm'],
				'srh_key' => $v['srh_key'],
				'status' => $soft_status,
				'co_type' => $v['co_type'],
				'editurl' => "/index.php/Sj/Search_weight/search_key_package_update/kid/{$key_id}/id/{$v['id']}",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getNecessaryDetail($type, $status, $extent_id, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 10) {
		$timestamp = time();
		$extent_model = M('necessary_extent');
		$extent_soft_model = M('necessary_extent_soft');
		if (empty($extent_id))
		{
			return false;
		}

		if($extent_id!=-1)
		{
			$map = array(
				'extent_id' => $extent_id,
				'type' => array('exp', '!=2'),
				#'status' => 1,
			);
			$extent = $extent_model->where($map)->find();
			if (empty($extent))
				return false;
		}

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
				if($extent_id!=-1)
				{
			$where_str = "a.extent_id = {$extent_id}";
				}else
				{
			$where_str = "1=1";
				}
		if (!empty($package)){
			$where_str .= " and a.package='$package'";
		}

		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_at');
		if (!empty($start_tm))
			$where_str .= " and a.end_at >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_at <= {$end_tm}";
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_at>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_at<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.extent_name from sj_necessary_extent_soft as a join sj_necessary_extent as b on a.extent_id=b.extent_id where {$where_str}";
		}else{
			$sql = "select a.*,b.extent_name from sj_necessary_extent_soft as a join sj_necessary_extent as b on a.extent_id=b.extent_id where {$where_str_two}";
		}
		$data = array();
		$res = $extent_soft_model->query($sql);
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_at'] <= $timestamp)
				{
					if ($v['end_at'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'softname' => $tmpname,
				'package' => $v['package'],
				'prob' => $v['prob'],
				'type' => $v['type'],
				'start_tm' => $v['start_at'],
				'end_tm' => $v['end_at'],
				'extent_name' => $v['extent_name'],
				'status' => $soft_status,
				'editurl' => "/index.php/Sj/NecessaryExtent/edit_soft/id/{$v['id']}/p/1?modal=true",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getPreferDetail($type, $status, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 0) {;
		$timestamp = time();
		$soft_recommend_model = M('soft_recommend');

		//if ($start_tm <= $timestamp)
			//$start_tm = $timestamp;
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		switch ($status)
		{
			case 0:
				$where_str = "a.status!=0";
				break;

			case 1:
				$where_str = "a.status=1 and a.start_tm>{$timestamp}";
				break;

			case 2:
				$where_str = "a.status=1 and a.start_tm<={$timestamp}";
				break;

			case 3:
				$where_str = "a.status=2";
				break;

			case 4:
				$where_str = "a.status=1";
				break;
		}
		if (!empty($start_tm))
			$where_str .= " and a.end_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_tm <= {$end_tm}";
		if (!empty($package))
			$where_str .= " and a.soft_package='$package'";

		$sql = "select a.* from sj_soft_recommend as a where {$where_str}";
		$data = array();
		$res = $soft_recommend_model->query($sql);
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['soft_package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_tm'] <= $timestamp)
				{
					if ($v['end_tm'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'softname' => $tmpname,
				'package' => $v['soft_package'],
				'rank' => $v['rank'],
				'start_tm' => $v['start_tm'],
				'end_tm' => $v['end_tm'],
				'status' => $soft_status,
				'editurl' => "/index.php/Sj/SoftRecommed/soft_recommend_edit/id/{$v['id']}",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getDownloadRecommendDetail($type, $status, $id, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 10) {
		$timestamp = time();
		$download_recommend_model= M('download_recommend');
		$download_recommend_soft_model = M('download_recommend_soft');

		if($id != -1)
		{
			$map = array(
				'id' => $id,
				'status' => 1,
			);
			$extent = $download_recommend_model->where($map)->find();
			if (empty($extent))
				return false;
		}

		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;

		if ($id==-1) {
			$where_str = "1=1";
		} else {
			$where_str = "b.id = {$id}";
		}
		if (!empty($package))
			$where_str .= " and a.package='$package'";
		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_tm');
		if (!empty($start_tm))
			$where_str .= " and a.end_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_tm <= {$end_tm}";
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_tm>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_tm<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.package as recommend_package from sj_download_recommend_soft as a join sj_download_recommend as b on a.recommend_id=b.id and b.status=1 where {$where_str}";
		}else{
			$sql = "select a.*,b.package as recommend_package from sj_download_recommend_soft as a join sj_download_recommend as b on a.recommend_id=b.id and b.status=1 where {$where_str_two}";
		}
		$data = array();
		$res = $download_recommend_soft_model->query($sql);
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			$recommend_name = $this->getSoftNameByPackage($v['recommend_package']);
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_tm'] <= $timestamp)
				{
					if ($v['end_tm'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'softname' => $tmpname,
				'package' => $v['package'],
				'show' => $v['show'],
				'type' => $v['type'],
				'start_tm' => $v['start_tm'],
				'end_tm' => $v['end_tm'],
				'extent_name' => $recommend_name,
				'status' => $soft_status,
				'editurl' => '/index.php/Sj/Downloadrecommend/edit_soft_show/id/' . $v['id'],
			);
		}

		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getSearchAladinDetail($type, $status, $start_tm, $end_tm, $package, $tmpname, $keyword, $is_accurate='', $start = 0, $limit = 0) {
		$timestamp = time();
		$search_aladin_model = M('soft_associate_hot_word');
		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		switch ($status)
		{
			case 0:
				$where_str = "a.stat!=0";
				break;

			case 1:
				$where_str = "a.stat=1 and a.begin>{$timestamp}";
				break;

			case 2:
				$where_str = "a.stat=1 and a.begin<={$timestamp}";
				break;

			case 3:
				$where_str = "a.stat=2";
				break;

			case 4:
				$where_str_two = "a.stat=1";
				break;
		}
		if (!empty($start_tm))
			$where_str .= " and a.end >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.begin <= {$end_tm}";
		if (!empty($package)){
			$where_str .= " and a.package='$package'";
			$where_str_two .= " and a.package='$package'";
		}
// 		if (!empty($keyword)) {
// 			$where_str .= " and a.associate like '%{$keyword}%'";
// 			$where_str_two .= " and a.associate like '%{$keyword}%'";
// 		}
		if (!empty($keyword)){
			$where_str .= ' and (';
			$keyword = explode('|', $keyword);
			foreach($keyword as $word){
				if(!empty($is_accurate)){
					//精确匹配
					$word = ';'.$word.';';
					$where_str .= " a.associate = '{$word}' or";
				}else{
					//模糊匹配
					$where_str .= " a.associate like '%{$word}%' or";
				}
			}
			$where_str = rtrim($where_str, ' or');
			$where_str .= ")";
		}

		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end');
		if($status!=4){
			$sql = "select a.* from sj_soft_associate_hot_word as a where {$where_str}";
		}else{
			$sql = "select a.* from sj_soft_associate_hot_word as a where {$where_str_two}";
		}
		$data = array();
		$res = $search_aladin_model->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
			if (!empty($tmpname) && stripos($softname, $tmpname) === false)
				continue;
			if ($v['stat'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['begin'] <= $timestamp)
				{
					if ($v['end'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'keyword' => trim($v['associate'], ';'),
				'recommend' => $v['recommend'],
				'package' => $v['package'],
				'softname' => $softname,
				'start_tm' => $v['begin'],
				'end_tm' => $v['end'],
				'status' => $soft_status,
				'type' => $v['type'],
				'editurl' => "/index.php/Sj/Searchkeywords/soft_hot_words_edit/id/{$v['id']}/",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getSearchSuggestDetail($type, $status, $start_tm, $end_tm, $package, $softname, $keyword, $is_accurate='', $start = 0, $limit = 0) {;
		$timestamp = time();
		$search_suggest_model = M('think_words');

		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		switch ($status)
		{
			case 0:
				$where_str = "a.status!=0";
				break;

			case 1:
				$where_str = "a.status=1 and a.start_time>{$timestamp}";
				break;

			case 2:
				$where_str = "a.status=1 and a.start_time<={$timestamp}";
				break;

			case 3:
				$where_str = "a.status=2";
				break;

			case 4:
				$where_str_two = "a.status=1";
				break;
		}
		if (!empty($start_tm))
			$where_str .= " and a.end_time >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_time <= {$end_tm}";
		if (!empty($package)){
			$where_str .= " and a.package='$package'";
			$where_str_two .= " and a.package='$package'";
		}
		if (!empty($keyword)){
			$where_str .= ' and (';
			$where_str_two .= ' and (';
			$keyword = explode('|', $keyword);
			foreach($keyword as $word){
				if(!empty($is_accurate)){
					//精确匹配
					$where_str .= " a.search_words = '{$word}' or";
					$where_str_two .= " a.search_words = '{$word}' or";
				}else{
					//模糊匹配
					$where_str .= " a.search_words like '%{$word}%' or";
					$where_str_two .= " a.search_words like '%{$word}%' or";
				}
			}
			$where_str = rtrim($where_str, ' or');
			$where_str .= ")";
			$where_str_two = rtrim($where_str, ' or');
			$where_str_two .= ")";
		}

		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_time');
		if($status!=4){
			$sql = "select a.* from sj_think_words as a where {$where_str}";
		}else{
			$sql = "select a.* from sj_think_words as a where {$where_str_two}";
		}
		$data = array();
		$res = $search_suggest_model->query($sql);
		foreach ($res as $v)
		{
			$softname = $this->getSoftNameByPackage($v['package']);
			// if (!empty($softname) && stripos($tmpname, $softname) === false)
			// 	continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_time'] <= $timestamp)
				{
					if ($v['end_time'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'keyword' => $v['search_words'],
				'recommend' => $v['recommend'],
				'package' => $v['package'],
				'soft_rank' => $v['soft_rank'],
				'softname' => $softname,
				'start_tm' => $v['start_time'],
				'end_tm' => $v['end_time'],
				'type' => $v['type'],
				'status' => $soft_status,
				'editurl' => "/index.php/Sj/Searchthinkword/update_searchkeywords_show/id/{$v['id']}/",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}

	function getAdPosition($pid = 1) {
		$ret = array();

		$extent_model = M('extent_v1');
		$map = array(
			'pid' => $pid,
			#'status' => 1,
			'type' => array('exp', '!=2'),
			'parent_union_id' => '0',
			'parent_id' => array('exp', '=0 OR (parent_id in (select extent_id from sj_extent_v1 where type=2))'),
		);
		$extent_list = $extent_model->where($map)->order('rank')->select();
		$ret['EX'] = array();
		foreach ($extent_list as $v)
		{
			$ret['EX'][] = array(
				'id' => $v['extent_id'],
				'name' => $v['extent_name'],
			);
		}

		$category_extent_model = M('category_extent');
		$map = array(
			'pid' => $pid,
			'status' => 1,
			'category_type' => 'top_1_hot',
		);
		$apphot_list = $category_extent_model->where($map)->order('rank')->select();
		$ret['AH'] = array();
		foreach ($apphot_list as $v)
		{
			$ret['AH'][] = array(
				'id' => $v['extent_id'],
				'name' => $v['extent_name'],
			);
		}

		$map['category_type'] = 'top_2_hot';
		$gamehot_list = $category_extent_model->where($map)->order('rank')->select();
		$ret['GH'] = array();
		foreach ($gamehot_list as $v)
		{
			$ret['GH'][] = array(
				'id' => $v['extent_id'],
				'name' => $v['extent_name'],
			);
		}

		$map['category_type'] = 'top_new';
		$topnew_list = $category_extent_model->where($map)->order('rank')->select();
		$ret['TN'] = array();
		foreach ($topnew_list as $v)
		{
			$ret['TN'][] = array(
				'id' => $v['extent_id'],
				'name' => $v['extent_name'],
			);
		}

		/*$map['category_type'] = 'fixed_user_also_download_recommend';
		$drecommend_list = $category_extent_model->where($map)->order('rank')->select();
		$ret['DR'] = array();
		foreach ($drecommend_list as $v)
		{
			$ret['DR'][] = array(
				'id' => $v['extent_id'],
				'name' => $v['extent_name'],
			);
		}*/

		$map['category_type'] = 'fixed_user_also_download';
		$down_list = $category_extent_model->where($map)->order('rank')->select();
		$ret['PR'] = array();
		foreach ($down_list as $v)
		{
			$ret['PR'][] = array(
				'id' => $v['extent_id'],
				'name' => $v['extent_name'],
			);
		}

		$ret['CA'] = array();
		$category_type = $this->getCategoryTypes($pid);
		unset($category_type['top_1_hot']);
		if ($pid != 5)
			unset($category_type['top_2_hot']);
		unset($category_type['top_new']);
		$ret['CA']['category_type'] = $category_type;
		$map = array(
			'pid' => $pid,
			'status' => 1,
			//'category_type' => 'top_hot',
		);
		$category_list = $category_extent_model->where($map)->order('rank')->select();
		foreach ($category_list as $value)
		{
			$k = $value['category_type'];
			if (isset($category_type[$k])) {
				$ret['CA'][$k][] = array(
					'id' => $value['extent_id'],
					'name' => $value['extent_name'],
				);
			}
		}
		// foreach ($category_type as $k => $v)
		// {
		// 	$map['category_type'] = $k;
		// 	$category_list = $category_extent_model->where($map)->order('rank')->select();
		// 	$ret['CA'][$k] = array();
		// 	foreach ($category_list as $value)
		// 	{
		// 		$ret['CA'][$k][] = array(
		// 			'id' => $value['extent_id'],
		// 			'name' => $value['extent_name'],
		// 		);
		// 	}
		// }
		$search_key_model = M('search_key');
		$map = array(
			'status' => 1,
			'pid' => $pid,
		);
		$key_list = $search_key_model->where($map)->order('id')->select();
		$ret['SK']['keyword'] = array();
		foreach ($key_list as $v)
		{
			$ret['SK']['keyword'][] = array(
				'id' => $v['id'],
				'name' => $v['id'] . " - " . $v['srh_key'],
			);
		}

		$necessary_model = M('necessary_extent');
		$map = array(
			#'status' => 1,
			'type' => array('exp', '!=2'),
			'parent_id' => array('exp', '=0 OR (parent_id in (select extent_id from sj_necessary_extent where type=2))'),
		);
		$necessary_list = $necessary_model->where($map)->order('rank')->select();
		$ret['NE'] = array();
		foreach ($necessary_list as $v)
		{
			$ret['NE'][] = array(
				'id' => $v['extent_id'],
				'name' => $v['extent_name'],
			);
		}

		$feature_model = M('feature');
		$map = array(
			'status' => 1,
		);
		$feature_list = $feature_model->where($map)->select();
		$ret['FE'] = array();
		foreach ($feature_list as $v)
		{
			$ret['FE'][] = array(
				'id' => $v['feature_id'],
				'name' => $v['name'],
			);
		}

		$category_model = M('lading_category');
		$map = array(
			'status' => 1,
		);
		$category_list = $category_model->where($map)->select();
		$ret['LA'] = array();
		foreach ($category_list as $v)
		{
			$ret['LA'][] = array(
				'id' => $v['id'],
				'name' => $v['category_name'],
			);
		}

		$download_recommend_model = M('download_recommend');
		$map = array(
			'status' => 1,
		);
		$drlist = $download_recommend_model->where($map)->select();


		$ret['DR'] = array();
		foreach ($drlist as $v)
		{
			$map = array(
				'package' => $v['package'],
			);
			$soft = $this->soft_model->where($map)->order('softid desc')->find();
			$name = $soft['softname'];
			$ret['DR'][] = array(
				'id' => $v['id'],
				'name' => $name,
			);
		}
		//搜索提示运营
		$search_tips_model = M('search_tips');
		$map = array(
			'status' => 1,
		);
		$stlist = $search_tips_model->where($map)->select();

		$ret['ST'] = array();

		foreach ($stlist as $v)
		{
			$ret['ST'][] = array(
				'id' => $v['id'],
				'name' => $v['srh_key'],
			);
		}
		//搜索相关词
		$search_related_model = M('search_related');
		$map = array(
			'status' => 1,
		);
		$srlist = $search_related_model->where($map)->select();

		$ret['SR'] = array();

		foreach ($srlist as $v)
		{
			$ret['SR'][] = array(
				'id' => $v['id'],
				'name' => $v['srh_key'],
			);
		}
		return $ret;
	}

	function batch()
	{
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
			case "start":
				$status = 1;
				$actionname = '启用';
				break;

			case "pause":
				$status = 2;
				$actionname = '暂停';
				break;

			case "delete":
				$status = 0;
				$actionname = '删除';
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
		$location1 = !empty($_POST['location1']) ? $_POST['location1'] : 'EX';
		$location2 = !empty($_POST['location2']) ? $_POST['location2'] : '';
		switch ($location1)
		{
			case 'EX':
				$table = 'sj_extent_soft_v1';
				$id = 'id';
				break;

			case 'FE':
				$table = 'sj_feature_soft';
				$id = 'id';
				break;

			case 'AH':
			case 'GH':
			case 'CA':
			case 'TN':
				$table = 'sj_category_extent_soft';
				$id = 'id';
				break;

			case 'SK':
				switch ($location2) {
					case 'keyword':
						$table = 'sj_search_key_package';
						$id = 'id';
						break;

					case 'default':
						$table = 'sj_soft_defaultkeywords';
						$id = 'key_id';
						break;

					case 'hotword':
						$table = 'sj_soft_hotwords';
						$id = 'hot_id';
						break;

					case 'skeyword':
						$table = 'sj_search_keywords';
						$id = 'id';
						break;

					default:
						$this->ajaxReturn('', '错误的请求', -1);
				}
				break;

			case 'NE':
				$table = 'sj_necessary_extent_soft';
				$id = 'id';
				break;

			case 'PR':
				$table = 'sj_soft_recommend';
				$id = 'id';
				break;

			case 'DR':
				$table = 'sj_download_recommend_soft';
				$id = 'id';
				break;

			case 'SA':
				$table = 'sj_soft_associate_hot_word';
				$id = 'id';
				break;

			case 'SS':
				$table = 'sj_think_words';
				$id = 'id';
				break;

			case 'LA':
				$table = 'sj_lading_soft';
				$id = 'id';
				break;

			case 'ST':
				$table = 'sj_search_tips_package';
				$id = 'id';
				break;

			default:
				$this->ajaxReturn('', '错误的请求', -1);
		}
		$soft = M('soft');
		$map = array(
			$id => $idlist
		);
		$data = array(
			'status' => 0,
		);
		$idstr = implode(',', $idlist);
		$set_str = "status={$status}";
		if ($location1 == 'SA') {
			$set_str = "stat={$status}";
		}
		$sql = "UPDATE {$table} SET {$set_str} WHERE {$id} in ({$idstr})";
		//echo $sql;
		$res = $soft->execute($sql);
		if ($res)
		{
			if ($location1 == 'FE')
			{
				$feature_soft_db = M('feature_soft');
				if ($action != 'start') {
					$feature_soft = $feature_soft_db->where('status = 1 AND feature_id ='.$feature_id)->order('rank ASC')-> select();
					$count = count($feature_soft);
					for($i = 1;$i <= $count; $i++){
						$sql = 'UPDATE __TABLE__ SET rank ='.$i.' WHERE feature_id='.$feature_id.' AND id='.$feature_soft[$i-1]['id'];
						$feature_soft_db ->query($sql);
					}
				}
				else
				{
					$rank = $feature_soft_db->where("status=1 and feature_id={$feature_id}")->max('rank') + 1;
					$where_rank = array(
						'status' => 1,
						'feature_id' => $feature_id,
					);
					foreach ($idlist as $v)
						$this->_updateRankInfo('sj_feature_soft','rank',$v,$where_rank,$rank);
				}
			}
			$this->writelog("批量{$actionname}了表{$table}中id为{$idstr}的广告", $table, $idstr,__ACTION__ ,'','edit');
			$this->ajaxReturn('', "{$actionname}成功", 0);
		}
		else
		{
			$this->ajaxReturn('', "{$actionname}失败", -1);
		}
	}

	function edit_all()
	{
		if(isset($_GET['biaoshi'])){
			$this->assign('location1', $_GET['location1']);
			$location2=explode("&",$_GET['location2']);
			$this->assign('location2', $location2[0]);
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

		$location1 = !empty($_POST['location1']) ? $_POST['location1'] : 'EX';
		$location2 = !empty($_POST['location2']) ? $_POST['location2'] : '';
		switch ($location1)
		{
			case 'EX':
				$table = 'sj_extent_soft_v1';
				$id = 'id';
				break;

			case 'FE':
				$table = 'sj_feature_soft';
				$id = 'id';
				break;

			case 'AH':
			case 'GH':
			case 'CA':
			case 'TN':
				$table = 'sj_category_extent_soft';
				$id = 'id';
				break;

			case 'SK':
				switch ($location2) {
					case 'keyword':
						$table = 'sj_search_key_package';
						$id = 'id';
						break;

					case 'default':
						$table = 'sj_soft_defaultkeywords';
						$id = 'key_id';
						break;

					case 'hotword':
						$table = 'sj_soft_hotwords';
						$id = 'hot_id';
						break;

					case 'skeyword':
						$table = 'sj_search_keywords';
						$id = 'id';
						break;

					default:
						$this->ajaxReturn('', '错误的请求', -1);
				}
				break;

			case 'NE':
				$table = 'sj_necessary_extent_soft';
				$id = 'id';
				break;

			case 'PR':
				$table = 'sj_category_extent_soft';
				$id = 'id';
				break;

			case 'DR':
				$table = 'sj_download_recommend_soft';
				$id = 'id';
				break;

			case 'SA':
				$table = 'sj_soft_associate_hot_word';
				$id = 'id';
				break;

			case 'SS':
				$table = 'sj_think_words';
				$id = 'id';
				break;

			case 'LA':
				$table = 'sj_lading_soft';
				$id = 'id';
				break;

			case 'ST':
				$table = 'sj_search_tips_package';
				$id = 'id';
				break;
			case 'SR':
				$table = 'sj_search_related_content';
				$id = 'id';
				break;
			default:
				$this->ajaxReturn('', '错误的请求', -1);
		}
		$model = M();
		$idstr = substr ( $idlist ,  0 , - 1 );
		$ids=explode(',',$idstr);
		$start_at = $_POST['start_at']?strtotime($_POST['start_at']):'';
		$end_at = $_POST['end_tm']?time():strtotime($_POST['end_at']);
		$table_config=array(
            "sj_download_recommend_soft"=>array("sj_download_recommend_soft",'start_tm','end_tm','下载推荐','package'),
            "sj_think_words"=> array("sj_think_words",'start_time','end_time',"搜索suggest设置",'package'),
            "sj_animation_ad"=>array("sj_animation_ad",'start_at','end_at',"悬浮窗管理",'package'),
            "sj_feature_soft"=>array("sj_feature_soft",'start_tm','end_tm',"专题配置",'package'),
            "sj_activity"=>array("sj_activity",'start_tm','end_tm',"活动分区",'package'),
            "sj_extent_soft_v1"=>array("sj_extent_soft_v1",'start_at','end_at',"市场首页软件列表",'package'),
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
		$error_arr=array();
		$AdSearch = D("Sj.AdSearch");
		if($action==1){
			foreach($ids as $id){
				$update_at = time();
				if($table=='sj_necessary_extent_soft' || $table=='sj_category_extent_soft' || $table=='sj_extent_soft_v1'){
					$set_str="update_at=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_at={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_at={$end_at}";
					}
					$id_key='id';
					$result=$model->table($table)->where(array($id_key=>$id))->find();
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
					}else if($table=='sj_category_extent_soft'){
						$shield_error.=$AdSearch->check_shield_old($result['package'],$result['extent_id'],0,'sj_category_extent');
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
						case "sj_category_extent_soft":
				            $error_mess=$AdSearch->logic_check_CategoryExtent($result);
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
							// echo "<pre>";var_dump($error_mess);
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
				}else if($table=='sj_search_keywords123' ){
					$set_str="update_tm=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_tm={$end_at}";
					}
					$id_key='id';
					$result=$model->table($table)->where(array($id_key=>$id))->find();

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
		            $error_mess=$AdSearch->logic_check_Search_words($result);
					// var_dump($error_mess);die;
					if($error_mess!=2){
							$result['message']=$error_mess['message'];
							$error_arr[]=$result;
							continue;
					}

					$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
					$re = $model->execute($sql); //需要复制上线
				}else if($table=='sj_feature_soft'){
					$set_str="last_refresh=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_tm={$end_at}";
					}
					//无需复制上线
					$id_key='id';
					$result=$model->table($table)->where(array($id_key=>$id))->find();
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
				}else if($table=='sj_search_key_package'){
					$set_str="update_tm=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",stop_tm={$end_at}";
					}
					$id_key='id';
						$result=$model->table($table)->where(array($id_key=>$id))->find();
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
						if($error_mess!=2){
							$result['message']=$error_mess['message'];
							$error_arr[]=$result;
							continue;
						}
						$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
						$re = $model->execute($sql); //不需要复制上线
				}else if($table=='sj_search_keywords' || $table=='sj_download_recommend_soft' || $table=='sj_soft_recommend' || $table=='sj_lading_soft' || $table=='sj_search_tips_package'|| $table=='sj_search_related_content'){
					// if(!empty($start_at)){
					// 	$set_str="start_tm={$start_at},end_tm={$end_at},update_tm={$update_at}";
					// }else{
					// 	$set_str="end_tm={$end_at},update_tm={$update_at}";
					// }
					$set_str="update_tm=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_tm={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_tm={$end_at}";
					}
					$id_key='id';
					// if(!empty($start_at)){
					// 	$set_str="start_tm={$start_at},end_tm={$end_at},update_tm={$update_at}";
					// }else{
					// 	$set_str="end_tm={$end_at},end_tm={$update_at}";
					// }
					$result=$model->table($table)->where(array($id_key=>$id))->find();

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
					if($table=='sj_soft_recommend'){
						$shield_error=$AdSearch->check_shield($result['soft_package'],$result['start_tm'],$result['end_tm']);
						if($shield_error){
							$result['package']=$result['soft_package'];
							$result['message']=$shield_error;
							$error_arr[]=$result;
							continue;
						}
					}else{
						if($table=='sj_search_keywords' || $table=='sj_search_tips_package' || $table=='sj_search_related_content'){
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
							// var_dump($error_mess);die;
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
									$error_arr[]=$result;
									continue;
							}

							$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
							$re = $model->execute($sql); //需要复制上线
							break;
						default:
							$this->ajaxReturn('', '错误的请求', -1);
					}
				}else if($table=='sj_think_words'){
					$set_str="up_time=$update_at";
					if(!empty($start_at)){
						$set_str.=",start_time={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end_time={$end_at}";
					}
					$id_key='id';
					$result=$model->table($table)->where(array($id_key=>$id))->find();
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
						$shield_error=$AdSearch->check_shield($result['package'],$result['start_time'],$result['end_time'],1);
						if($shield_error){
							$result['message']=$shield_error;
							$error_arr[]=$result;
							continue;
						}
						switch ($table)
						{
							case "sj_think_words":
								$end_time=$result['end_time'];
								$start_time=$result['start_time'];
								$soft_rank=$result['soft_rank'];
								$id_tw=$result['id'];
								$search_words=$result['search_words'];
								 $sel = $model->table("sj_think_words")->where("((start_time <=$end_time and end_time>=$start_time) or (start_time <=$start_time and end_time>=$end_time)) and soft_rank=$soft_rank and status =1 and id !=$id_tw")->select();
								// }
								 // echo $model->getLastSql();
								 // echo "<pre>";var_dump($sel);
								 $new_str=array();
								foreach($sel as $k=>$v){
									$new_str[] =  $v['search_words'];

								}
								$a_arr = implode(',',$new_str);
								$newarr = explode(',',$a_arr);
								$arr=explode(',',$search_words);
								$res = array_intersect($newarr,$arr);
								// $error_mess=$AdSearch->check_conflict_AnimationAd($result,$result['id']);
								// echo "<pre>";var_dump($res);
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
				}else if($table=='sj_soft_associate_hot_word'){
					$id_key='id';
					$result=$model->table($table)->where(array($id_key=>$id))->find();
					if(!empty($start_at)){
							$result['begin']=$start_at;
					}
					if(!empty($end_at)){
						$result['end']=$end_at;
					}
					if($result['begin']>$result['end']){
						$result['message']="开始时间不能大于结束时间";
						$error_arr[]=$result;
						continue;
					}
					$shield_error=$AdSearch->check_shield($result['package'],$result['begin'],$result['end'],1);
					if($shield_error){
						$result['message']=$shield_error;
						$error_arr[]=$result;
						continue;
					}
					$set_str="update_time=$update_at";
					if(!empty($start_at)){
						$set_str.=",`begin`={$start_at}";
					}
					if(!empty($end_at)){
						$set_str.=",end={$end_at}";
					}
					$sql = "UPDATE {$table} SET {$set_str} WHERE {$id_key}"."="."{$id}";
					$re = $model->execute($sql);
					// echo $model->getLastSql();die;
				}
				if($re){
					$t1=$table_config["$table"]['1'];
					$t2=$table_config["$table"]['2'];
					$this->writelog("批量编辑了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$result["$t1"]},结束时间：{$result["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
				}
			}
			// if($table=='sj_soft_hotwords' || $table=='sj_soft_defaultkeywords'){
			// 		$result=$model->table($table)->where(array($id_key=>$id))->find();
			// 		if(!empty($start_at)){
			// 				$result['start_time']=$start_at;
			// 		}
			// 		if(!empty($end_at)){
			// 			$result['end_time']=$end_at;
			// 		}
			// 		if($result['start_time']>$result['end_time']){
			// 			$result['message']="开始时间不能大于结束时间";
			// 			$error_arr[]=$result;
			// 			continue;
			// 		}
			// 		$set_str="";
			// 		if(!empty($start_at)){
			// 			$set_str.=",start_time={$start_at}";
			// 		}
			// 		if(!empty($end_at)){
			// 			$set_str.=",end_time={$end_at}";
			// 		}
			// 		// if(!empty($start_at)){
			// 		// 	$set_str="start_time={$start_at},end_time={$end_at}";
			// 		// }else{
			// 		// 	$set_str="end_time={$end_at}";
			// 		// }
			// 		if($set_str!=""){
			// 			$sql = "UPDATE {$table} SET {$set_str} WHERE {$id} in ({$idstr})";
			// 			$re = $model->execute($sql);
			// 		}
			// }
			// $sql = "UPDATE {$table} SET {$set_str} WHERE {$id} in ({$idstr})";
			// $re = $model->execute($sql);
			// $bs=1;
		}else{
			if(!$end_at){
				$end_at = time();
			}
			$sql="select * from {$table} WHERE {$id} in ({$idstr})";
			$res = $model->query($sql);
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
								// if($start_at>time()){
									$res[$k]['status']=1;
								// }else if($start_at<time()){
								// 	$res[$k]['status']=2;
								// }
								if($res[$k]['start_at']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
								}
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
								// echo "<pre>";var_dump($res[$k]);
								// var_dump($error_mess);echo "<br>";
								if($error_mess!=2){
										$res[$k]['message']=$error_mess['message'];
										$error_arr[]=$res[$k];
										continue;
								}
								if($re){
									$t1=$table_config["$table"]['1'];
									$t2=$table_config["$table"]['2'];
									$this->writelog("批量复制上线了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$res[$k]["$t1"]},结束时间：{$res[$k]["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
								}
								unset($res[$k]['id']);
								$re=M('extent_soft_v1')->add($res[$k]);
								// echo M('extent_soft_v1')->getLastSql();die;
							}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
							}

						}
				}
			}
			if($table=='sj_feature_soft' ||  $table=='sj_search_keywords' ){
				if(!empty($res)){
						foreach($res as $k=>$v){
							if($table!='sj_search_keywords'){
								unset($res[$k]['id']);
							}

							if(empty($start_at)){
								$start_at=$v['start_time'];
							}
							if(($start_at<$end_at)){
								$res[$k]['start_tm']=$start_at;
								$res[$k]['end_tm']=$end_at;
								$res[$k]['status']=1;
								if($table!='sj_search_keywords'){
									$res[$k]['create_tm']=time();
								}
								if($table=='sj_search_keywords'){
									if($res[$k]['start_tm']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
									}
									$shield_error=$AdSearch->check_shield($res[$k]['package'],$res[$k]['start_tm'],$res[$k]['end_tm'],1);
									if($shield_error){
										$res[$k]['message']=$shield_error;
										$error_arr[]=$res[$k];
										continue;
									}
									$error_mess=$AdSearch->logic_check_Search_words($res[$k]);
									// var_dump($error_mess);die;
									if($error_mess!=2){
											$res[$k]['message']=$error_mess['message'];
											$error_arr[]=$res[$k];
											continue;
									}
									if($re){
										$t1=$table_config["$table"]['1'];
										$t2=$table_config["$table"]['2'];
										$this->writelog("批量复制上线了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$res[$k]["$t1"]},结束时间：{$res[$k]["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
									}
									unset($res[$k]['id']);
									$re=M('search_keywords')->add($res[$k]);
								}
							}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
							}
						}
					}
			}else if($table=='sj_search_key_package'){
				if(!empty($res)){
					foreach($res as $k=>$v){
						unset($res[$k]['id']);
						if(empty($start_at)){
							$start_at=$v['start_tm'];
						}
						if(($start_at<$end_at)){
							$res[$k]['start_tm']=$start_at;
							$res[$k]['stop_tm']=$end_at;
							$res[$k]['status']=1;
							$res[$k]['create_tm']=time();
							if($res[$k]['start_tm']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
								}
							// $re=M('search_key_package')->add($res[$k]);
						}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
						}

					}
				}
			}else if($table=='sj_soft_hotwords' || $table=='sj_soft_defaultkeywords'){
				// if(!empty($res)){
				// 	foreach($res as $k=>$v){
				// 		unset($res[$k]['hot_id']);
				// 		if(empty($start_at)){
				// 			$start_at=$v['start_time'];
				// 		}
				// 		if(($start_at<$end_at)){
				// 			$res[$k]['start_time']=$start_at;
				// 			$res[$k]['end_time']=$end_at;
				// 			$res[$k]['status']=1;
				// 			$res[$k]['add_time']=1;
				// 			if($res[$k]['start_time']<(time()-86400)){
				// 						$res[$k]['message']='复制上线日期还是无效日期';
				// 							$error_arr[]=$res[$k];
				// 							continue;
				// 			}
				// 			if($table=='sj_soft_hotwords'){
				// 				// $re=M('soft_hotwords')->add($res[$k]);
				// 			}else if($table=='sj_soft_defaultkeywords'){
				// 				// $re=M('soft_defaultkeywords')->add($res[$k]);
				// 			}
				// 		}else{
				// 				$res[$k]['message']="开始时间不能大于结束时间";
				// 				$error_arr[]=$res[$k];
				// 				continue;
				// 		}

				// 	}
				// }
			}else if($table=='sj_search_tips_package' || $table=='sj_lading_soft' || $table=='sj_soft_recommend' || $table=='sj_download_recommend_soft' ){
				if(!empty($res)){
					foreach($res as $k=>$v){
						unset($res[$k]['id']);
						if(empty($start_at)){
							$start_at=$v['start_tm'];
						}
						if(($start_at<$end_at)){
							$res[$k]['start_tm']=$start_at;
							$res[$k]['end_tm']=$end_at;
							$res[$k]['status']=1;
							$res[$k]['create_tm']=1;
							if($res[$k]['start_tm']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
							}
							if($table=='sj_search_tips_package'){
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

							if($table=='sj_soft_recommend'){
								// $re=M('soft_recommend')->add($res[$k]);
							}else if($table=='sj_download_recommend_soft'){
								$error_mess=$AdSearch->logic_check_download($res[$k]);
								if($error_mess!=2){
									$res[$k]['message']=$error_mess['message'];
									$error_arr[]=$res[$k];
									continue;
								}
								if($re){
									$t1=$table_config["$table"]['1'];
									$t2=$table_config["$table"]['2'];
									$this->writelog("批量复制上线了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$res[$k]["$t1"]},结束时间：{$res[$k]["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
								}
								unset($res[$k]['id']);
								$re=M('download_recommend_soft')->add($res[$k]);
							}else if($table=='sj_lading_soft'){
								// $re=M('lading_soft')->add($res[$k]);
							}else if($table=='sj_search_tips_package'){
								// $re=M('search_tips_package')->add($res[$k]);
							}
						}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
						}

					}
				}
			}else if($table=='sj_soft_associate_hot_word'){
				if(!empty($res)){
					foreach($res as $k=>$v){
						unset($res[$k]['id']);
						if(empty($start_at)){
							$start_at=$v['begin'];
						}
						if(($start_at<$end_at)){
							$res[$k]['begin']=$start_at;
							$res[$k]['end']=$end_at;
							$res[$k]['stat']=1;
							$res[$k]['create_time']=1;
							if($res[$k]['begin']<(time()-86400)){
										$res[$k]['message']='复制上线日期还是无效日期';
											$error_arr[]=$res[$k];
											continue;
							}
							// $re=M('soft_associate_hot_word')->add($res[$k]);
						}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
						}

					}
				}
			}else if($table=='sj_category_extent_soft' || $table=='sj_necessary_extent_soft'){
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
									$error_mess=$AdSearch->check_conflict_AnimationAd($res[$k]);
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
									if($re){
										$t1=$table_config["$table"]['1'];
										$t2=$table_config["$table"]['2'];
										$this->writelog("批量复制上线了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$res[$k]["$t1"]},结束时间：{$res[$k]["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
									}
									unset($res[$k]['id']);
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
									if($re){
										$t1=$table_config["$table"]['1'];
										$t2=$table_config["$table"]['2'];
										$this->writelog("批量复制上线了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$res[$k]["$t1"]},结束时间：{$res[$k]["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
									}
									unset($res[$k]['id']);
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
										if($re){
											$t1=$table_config["$table"]['1'];
											$t2=$table_config["$table"]['2'];
											$this->writelog("批量复制上线了{$table_config["$table"]['3']}中id为{$id}的广告，开始时间：{$res[$k]["$t1"]},结束时间：{$res[$k]["$t2"]}。", $table, $id,__ACTION__ ,'','edit');
										}
										unset($res[$k]['id']);
										// var_dump($res[$k]);die;
									$re=M('think_words')->add($res[$k]);
								}
							}else{
								$res[$k]['message']="开始时间不能大于结束时间";
								$error_arr[]=$res[$k];
								continue;
							}
						}
					}
			}
		}
		if (!$error_arr)
		{
			// $this->writelog("批量{$actionname}了表{$table}中id为{$idstr}的广告");
			$this->success("{$actionname}成功");
		}
		else
		{
			$this->assign('error_arr',$error_arr);
			$this->display("error");
		}
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
			'olgame_hot_5' => '网游精选',
			'fixed_olgame' => '网游',
			'fixed_offlinegame' => '单机',
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

	function getSoftnameByPackage($package) {
		if (empty($package))
			return "";
		$map = array(
			'package' => $package,
			'status' => 1,
		);
		$res = $this->soft_model->where($map)->order("softid desc")->find();
		if (empty($res))
			return "";
		else
			return $res['softname'];
	}
	function getChannelByCategoryID($category_id)
	{
		if(empty($category_id))
		{
			return "";
		}
		$map = array(
			'id' => $category_id,
			'status' => 1,
		);
		$model = M('lading_category');
		$res = $model -> table('sj_lading_category')-> where($map)->find();
		if(empty($res))
			return "";
		else
		{
			if($res['cid']==0)
			{
				$chname="--";
			}
			else
			{
				$my_chname_result = $model -> table('sj_channel') -> where(array('cid' => $res['cid'])) -> select();
				$chname = $my_chname_result[0]['chname'];
			}
			return $chname;
		}

	}
	//搜索提示运营
	function getSearchTipsDetail($type, $status, $pid, $key_id, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 10)
	{
		$timestamp = time();
		$search_tips_model = M('search_tips');
		$search_tips_package_model = M('search_tips_package');
		if (empty($key_id))
		{
			return false;
		}
		if(is_numeric($key_id))
		{
			$map = array(
				'id' => $key_id,
				'status' => 1,
			);
			$extent = $search_tips_model->where($map)->find();
			if (empty($extent))
				return false;
		}

		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		if(is_numeric($key_id))
		{
			$where_str = "a.kid = {$key_id}";
		}
		else
		{
			$where_str = "1=1";
		}
		if (!empty($package)){
			$where_str .= " and a.package='$package'";
		}
		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_tm');
		if (!empty($start_tm))
			$where_str .= " and a.end_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_tm <= {$end_tm}";
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_tm>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_tm<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.srh_key from sj_search_tips_package as a join sj_search_tips as b on a.kid =b.id and b.status=1 where {$where_str}";
		}else{
			$sql = "select a.*,b.srh_key from sj_search_tips_package as a join sj_search_tips as b on a.kid =b.id and b.status=1 where {$where_str_two}";
		}
		$data = array();
		$res = $search_tips_package_model->query($sql);
		foreach ($res as $v)
		{
			$tmpname = $this->getSoftNameByPackage($v['package']);
			if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_tm'] <= $timestamp)
				{
					if ($v['end_tm'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			$data[] = array(
				'id' => $v['id'],
				'softname' => $tmpname,
				'package' => $v['package'],
				'custom_name' => $v['custom_name'],
				'rank' => $v['rank'],
				'start_tm' => $v['start_tm'],
				'end_tm' => $v['end_tm'],
				'srh_key' => $v['srh_key'],
				'status' => $soft_status,
				'co_type' => $v['co_type'],
				'editurl' => "/index.php/Sj/Search_tips/search_tips_package_update/kid/{$key_id}/id/{$v['id']}",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}
	//V6.4搜索相关词
	function getSearchRelatedDetail($type, $status, $pid, $key_id, $start_tm, $end_tm, $package, $softname, $start = 0, $limit = 10)
	{
		$timestamp = time();
		$search_related_model = M('search_related');
		$search_related_content_model = M('search_related_content');
		if (empty($key_id))
		{
			return false;
		}
		if(is_numeric($key_id))
		{
			$map = array(
				'id' => $key_id,
				'status' => 1,
			);
			$extent = $search_related_model->where($map)->find();
			if (empty($extent))
				return false;
		}

		if (!empty($end_tm) && $end_tm < $start_tm)
			return false;
		if(is_numeric($key_id))
		{
			$where_str = "a.kid = {$key_id}";
		}
		else
		{
			$where_str = "1=1";
		}
		if (!empty($package)){
			$where_str .= " and a.package='$package'";
		}
		$where_str_two=$where_str;
		$where_str_two.=$this->joint($where_str_two,$end_tm,$start_tm,'end_tm');
		if (!empty($start_tm))
			$where_str .= " and a.end_tm >= {$start_tm}";
		if (!empty($end_tm))
			$where_str .= " and a.start_tm <= {$end_tm}";
		switch ($status)
		{
			case 0:
				$where_str .= " and a.status!=0";
				break;

			case 1:
				$where_str .= " and a.status=1 and a.start_tm>{$timestamp}";
				break;

			case 2:
				$where_str .= " and a.status=1 and a.start_tm<={$timestamp}";
				break;

			case 3:
				$where_str .= " and a.status=2";
				break;

			case 4:
				$where_str_two .= " and a.status=1";
				break;
		}
		if($status!=4){
			$sql = "select a.*,b.srh_key from sj_search_related_content as a join sj_search_related as b on a.kid =b.id and b.status=1 where {$where_str}";
		}else{
			$sql = "select a.*,b.srh_key from sj_search_related_content as a join sj_search_related as b on a.kid =b.id and b.status=1 where {$where_str_two}";
		}
		$data = array();
		$res = $search_related_content_model->query($sql);
		foreach ($res as $v)
		{

			if ($v['status'] == 2)
			{
				$soft_status = 3;
			}
			else
			{
				if ($v['start_tm'] <= $timestamp)
				{
					if ($v['end_tm'] < $timestamp)
						$soft_status = 4;
					else
						$soft_status = 2;
				}
				else
					$soft_status = 1;
			}
			if($v['guide_page']==1)
			{
				$guide_page="搜索结果页";
			}
			else if($v['guide_page']==2)
			{
				$tmpname = $this->getSoftNameByPackage($v['package']);
				if (!empty($softname) && stripos($tmpname, $softname) === false)
				continue;
				$guide_page="软件详情页:".$v['package']."/".$tmpname;
			}
			$data[] = array(
				'id' => $v['id'],
				'related_keys' => $v['related_keys'],
				'guide_page' => $guide_page,
				'rank' => $v['rank'],
				'start_tm' => $v['start_tm'],
				'end_tm' => $v['end_tm'],
				'srh_key' => $v['srh_key'],
				'status' => $soft_status,
				'co_type' => $v['co_type'],
				'editurl' => "/index.php/Sj/Search_related/search_related_content_update/kid/{$key_id}/id/{$v['id']}",
			);
		}
		switch ($type)
		{
			case 'detail':
				return array_slice($data, $start, $limit);
				break;

			case 'count':
				$count = count($data);
				return $count;
				break;

			default:
				return false;
		}
	}
	function joint($where_str_two,$end_tm,$start_tm,$end_b){
		$where_str_two=$where_str;
		if($end_tm>time() || empty($end_tm)){
			$where_str_two.=" and a.".$end_b."<= ".time();
		}else{
			$where_str_two.=" and a.".$end_b."<= ".$end_tm;
		}
		if(!empty($start_tm)&&$start_tm<(time()-1000)){
			$where_str_two.=" and a.".$end_b.">= ".$start_tm;
		}else{
			$where_str_two.=" and a.".$end_b.">= ".(time()-86400*30);
		}
		// echo $where_str_two;
		// echo "<br>";
		return $where_str_two;
	}

}
