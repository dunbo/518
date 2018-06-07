<?php
/**
 * 广告结算-排期表
 * 
 * @author jiwei
 *
 */
class SchedulesAction extends CommonAction 
{
	/**
	 * 排期报表
	 */
	public function index()
	{
		$ts = time();
		$cmark = isset($_GET['cmark']) && empty($_GET['cmark'])==FALSE ? trim($_GET['cmark']) : NULL; //客户编号
		$bids = isset($_GET['bids']) && empty($_GET['bids'])==FALSE ? trim($_GET['bids']) : NULL; //商务id，逗号分隔id字符串
		$start = isset($_GET['start']) && empty($_GET['start'])==FALSE ? strtotime($_GET['start']) : $ts; //开始日期
		$end = isset($_GET['end']) && empty($_GET['end'])==FALSE ? strtotime($_GET['end']) : $ts+29*86400; //结束日期
		$export = isset($_GET['export']) && $_GET['export']==1 ? true : false; //是否是导出操作
		
		//
		//验证查询时期
		//
		if($start>$end)
			$this->error("查询的开始日期大于结束日期！");
		
		if(($end-$start)/86400 > 365)
			$this->error("查询日期的时间不能大于1年！");
		
		//初始化一些用到的对象、变量
		$_m = new Model();
		$search_falg = false; //搜索标记，通过客户编号或者商务id搜索
		$sch_where = $app_where = $app_ids = array();
		
		//
		//查询编号
		//
		if(is_null($cmark)==FALSE)
		{
			$search_falg = true; 
			$app_where['mark.cmark'] = array('like', $cmark.'%');
		}
		
		if(is_null($bids)==FALSE)
		{
			$search_falg = true;
			$app_where['bd.bid'] = array('in',str_replace('_', ',', $bids));
		}

		if($search_falg)
		{
			$result = $_m -> table('settlement.ad_contract_apps app')
						-> field('app.id')
						-> join('settlement.ad_client_mark mark ON mark.mid=app.mid')
						-> join('settlement.ad_business bd ON bd.bid=mark.bid')
						-> where($app_where)
						-> select();
			
			foreach($result as $row)
				$app_ids[] = $row['id'];
			
			//排期查询条件
			$sch_where['app_id'] = array('in', $app_ids);
		}
		
		//
		//查询排期
		//
		$sch_where['schedule_date'] = array(array('egt', date('Y-m-d', $start)), array('elt', date('Y-m-d', $end)));
		
		$m_sch = D('Settlement.Schedule');
		$result = $m_sch->where($sch_where)->order('advertising_id ASC, app_id ASC')->select();

		$data = $area = $adv_ids = $app_ids = $stats = $prices = array();
		foreach($result as $row)
		{
			//索引下标 由广告id,频道标志,频道节点组成，逗号分隔
			$index = $row['advertising_id'].','.$row['channel'].','.$row['channel_node_id'];
			
			//统计同一广告位、同一区间的重复日期
			if(!isset($stats[$index][$row['schedule_date']]))
				$stats[$index][$row['schedule_date']] = 0;
			
			$stats[$index][$row['schedule_date']]++;
			
			//构建报表数据结构
			$data[$index][$row['schedule_date']][] = $row['app_id'];
			
			//用于区间查询
			if($row['channel_node_id'])
				$area[$row['channel']][$row['channel_node_id']] = 1;
			
			//用于广告位查询
			$adv_ids[$row['advertising_id']] = 1;
			
			//用于查询app对应的编号
			$app_ids[$row['app_id']] = 1;
		}
		$this->assign('stats', $stats);
		$this->assign('data', $data);
		
		
		//
		//查询软件对应编号
		//
		$app_mark = array();
		$result = $_m -> table('settlement.ad_contract_apps app')
						-> field('app.id, app.weekdays, app.weekday_discount, app.weekend_discount, mark.cmark, mark.status AS mark_status, bd.bname, bd.color, bd.type, bd.status AS bd_status')
						-> join('settlement.ad_client_mark mark ON mark.mid=app.mid')
						-> join('settlement.ad_business bd ON bd.bid=mark.bid')
						-> where(array('app.id'=>array('in', array_keys($app_ids))))
						-> select();

		foreach($result as $row)
		{
			if($row['cmark'])
			{
				$app_mark[$row['id']]['cmark'] = $row['mark_status']==0 ? $row['cmark'] : 'xxx';
				$app_mark[$row['id']]['bname'] = $row['bname'];
				$app_mark[$row['id']]['color'] = $row['color'];
				$app_mark[$row['id']]['discount'] = $row['weekdays']>0 ? $row['weekday_discount'] : $row['weekend_discount']; 
				$app_mark[$row['id']]['type'] = $row['type'];
				$app_mark[$row['id']]['mark_status'] = $row['mark_status'];
				$app_mark[$row['id']]['bd_status'] = $row['bd_status'];
			}
		}
		$this->assign('app_mark', $app_mark);

		
		//
		//查询广告位
		//
		$m_adv = D('Settlement.Advertising');
		$result = $m_adv->field('id,advertising_name')->where(array('id'=>array('in',array_keys($adv_ids))))->select();
		
		$advert = array(); //用于显示广告位名称用
		foreach($result as $row)
		{
			$advert[$row['id']] = $row['advertising_name'];
		}
		$this->assign('advert', $advert);
		
		//
		//查询广告区间名称
		//
		$adv_tables = C('_proofread_.advert_tables');
		
		foreach($area as $key=>$val)
		{
			$node = $adv_tables[$key]['node'];

			$node_where[$node['find']] = array('in', array_keys($val));
			
			$m = D($node['model']);
			$result = $m->field("{$node['find']},{$node['node_name']}")->where($node_where)->select();
						
			foreach($result as $row)
			{
				$advert_area[$key.','.$row[$node['find']]] = $row[$node['node_name']];
			}
		}
		$this->assign('advert_area', $advert_area);
		
		//
		//处理导出
		//
		if($export)
		{
			header("Content-type:text/html");
			header("Content-Disposition:attachment;filename=排期表_".date('Ymd',$start).'～'.date('Ymd',$end).".html");
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			header('Expires:0');
			header('Pragma:public');
		}
		
		$this->assign('cmark', $cmark);
		$this->assign('bids', $bids);
		$this->assign('export', $export);
		$this->assign('start', $start);
		$this->assign('end', $end);
		$this->assign('week', array(1=>'一', 2=>'二', 3=>'三', 4=>'四', 5=>'五', 6=>'六', 7=>'日'));
		$this->display();
	}
	
	
	public function bd()
	{
		$bids = isset($_GET['bids']) && empty($_GET['bids'])==FALSE ? trim($_GET['bids']) : NULL; //商务id，下划线分隔id字符串
		
		if(is_null($bids)==FALSE)
		{
			$bids = explode('_', $bids);
			$this->assign('bids', $bids);
		}
		
		//
		//查询商务列表
		//
		$m_bd = D('Settlement.Business');
		$bds = $m_bd->field('bid,bname,color')->where(array('status'=>'1'))->findAll();
		$this->assign('bds', $bds);
		
		$this->display();
	}
}