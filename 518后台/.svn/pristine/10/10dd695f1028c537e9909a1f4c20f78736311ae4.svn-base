<?php
class AdScheduleAction extends CommonAction {
	private $prefix = 'EX';
	function index() {
		$today = strtotime(date('Y-m-d'));
		$selectYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
		$selectMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
		$selectDate = strtotime("$selectYear-$selectMonth-01");
		$dateToUse = ($today > $selectDate) ? $today : $selectDate;
		
		$temparray = array(
			-1,
			0,
			1,
		);
		//$extent = array(
		//	'home_extent' => array('name' => '首页推荐', 'parent' => ''),
		//	'2' => array('name' => '首页推荐区间1', 'parent' => 'home_extent'),
		//	'3' => array('name' => '首页推荐区间1-1', 'parent' => '2'),
		//	'4' => array('name' => '首页推荐区间2', 'parent' => 'home_extent'),
		//	'5' => array('name' => '首页推荐区间1-2', 'parent' => '2'),
		//);
		for ($i = 0; $i < 31; $i++)
		{
			$timestamp = $dateToUse + $i * 86400;
			$extentlist = $this->getExtentData($timestamp);
			//var_dump($extentlist);
			foreach ($extentlist as $v)
			{
				$data[$timestamp][$v['id']] = intval($v['status']);
			}
		}
		foreach ($extentlist as $v)
		{
			$extent[$v['id']] = array('name' => $v['name'], 'parent' => $v['parent']);
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
		//var_dump($extent);
		ksort($data);
		$dateList = array();
		foreach ($data as $date => $value)
		{
			$month = date('Y-m', $date);
			$day = date('d', $date);
			$dateList[$month][$day] = 1;
		}
		$monthList = array();
		foreach ($dateList as $month => $day)
			$monthList[$month] = count($day);
		//var_dump($monthList);
		//var_dump($data);
		$this->assign('monthlist', $monthList);
		$this->assign('datalist', $data);
		$this->assign('extentlist', $extent);
		$this->assign('timelist', array_keys($data));
		$this->display();
	}
	
	function getExtentData($time = '') {
		if (empty($time))
			$time = time();
		$extent_model = M('extent_v1');
		$extentsoft_model = M('extent_soft_v1');
		$map = array(
			'status' => 1,
			'pid' => 1,
			'parent_union_id' => '',
			'type' => array('exp', '!=2'),
		);
		$extentlist = $extent_model->where($map)->order('rank asc')->select();
		$statusall = 1;
		$statuspart = 0;
		foreach ($extentlist as $v)
		{
			$map = array(
				'extent_id' => $v['extent_id'],
				'status' => 1,
				'start_at' => array('ELT', $time),
				'end_at' => array('EGT', $time),
			);
			$available = $extentsoft_model->where($map)->count();
			$map = array(
				'extent_id' => $v['extent_id'],
				'status' => 2,
			);
			$paused = $extentsoft_model->where($map)->count();
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
			if ($buckets <= $available)
			{
				$status = 2;
				$statuspart = 1;
			}
			else
			{
				$statusall = 0;
				if ($available == 0)
				{
					$status = 0;
				}
				else
				{
					$statuspart = 1;
					$status = 1;
				}
			}
			if (($buckets - $available) > 0) 
				$empty = ($buckets - $available);
			else
				$empty = 0;
			$data[$v['extent_id']] = array(
				'id' => $this->prefix . $v['extent_id'],
				'name' => $v['extent_name'],
				'status' => $status,
				'available' => $available,
				'empty' => $empty,
				'paused' => $paused,
				'parent' => $this->prefix . '0',
			);
			$availableall += $available;
			$emptyall += $empty;
			$pausedall += $paused;
		}
		$statusfinal = $statuspart + $statusall;
		$data[0] = array(
			'id' => $this->prefix . '0',
			'name' => '首页推荐',
			'status' => $statusfinal,
			'available' => $availableall,
			'empty' => $empty,
			'paused' => $pausedall,
			'parent' => '',
		);	
		return $data;
	}

}
