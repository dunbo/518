<?php
/**
 * 广告结算-合同结算录入列表
 * 
 * @author jiwei
 *
 */
class ContractSettlementAction extends CommonAction 
{
	/**
	 * 合同结算录入列表页
	 */
	public function index()
	{
		$contract_code = isset($_GET['contract_code']) && empty($_GET['contract_code'])==FALSE ? trim($_GET['contract_code']) : NULL;
		$client_name = isset($_GET['client_name']) && empty($_GET['client_name'])==FALSE ? trim($_GET['client_name']) : NULL;
		$responsible = isset($_GET['responsible']) && empty($_GET['responsible'])==FALSE ? trim($_GET['responsible']) : NULL;
		$start = isset($_GET['start']) && strtotime($_GET['start']) > 0 ? $_GET['start'] : NULL; //开始日期
		$end = isset($_GET['end']) && strtotime($_GET['end']) > 0 ? $_GET['end'] : NULL; //结束日期
		$p = isset($_GET['p']) && empty($_GET['p'])==FALSE ? $_GET['p'] : 1; //页数
		$lr = isset($_GET['lr']) && empty($_GET['lr'])==FALSE ? $_GET['lr'] : 20; //每页记录数
		
		$where_query = array();
		
		if(is_null($contract_code)==FALSE)
		{
			$where_query['contract.contract_code'] = array('like', "%{$contract_code}%");
			$this->assign('contract_code', $contract_code);
		}
		
		if(is_null($client_name)==FALSE)
		{
			$where_query['client.client_name'] = array('like', "%{$client_name}%");
			$this->assign('client_name', $client_name);
		}
		
		if(is_null($responsible)==FALSE)
		{
			$where_query['responsible'] = array('like', "%{$responsible}%");
			$this->assign('responsible', $responsible);
		}
		
		if(is_null($start)==FALSE && is_null($end)==FALSE)
		{
			$where_query['_string'] = "(contract.start_date>='{$start}' AND contract.start_date <= '{$end}') OR (contract.end_date >= '{$start}' AND contract.end_date <= '{$end}')";
			$this->assign('start', $start);
			$this->assign('end', $end);
		}
		
		$where_query['contract.status'] = array('eq', 1);
		
		//
		// 处理查询
		//
		$model = new Model();
		
		$count = $model -> table('settlement.ad_contracts contract')
					 	-> join('settlement.ad_clients client ON client.id=contract.client_id')
					 	-> where($where_query)
					 	-> count();
		
		$contracts = $model -> table('settlement.ad_contracts contract')
							-> field('contract.id, client.client_name, contract.month, contract.contract_code, contract.app_num, contract.package_num, contract.invoiced_total, contract.received_total, contract.app_discount_total, contract.deposited_total, contract.responsible')
					 		-> join('settlement.ad_clients client ON client.id=contract.client_id')
					 		-> where($where_query)
					 		-> order('contract.month DESC,CONVERT(client.client_name USING gbk) ASC')
					 		-> page("{$p},{$lr}")
					 		-> select();

		//
		// 生成结果集数组，避免多次循环嵌套查询数据库
		//
		$result = array();
		$contract_ids = ''; //用于保存合同id列表，逗号分隔
		$app_ids = ''; //用于保存应用id列表，逗号分隔
		
		// 处理第一层，合同相关数据
		foreach($contracts as $con)
		{
			$contract_ids .= ','.$con['id'];
			
			$con['schedule_x'] = 0; //已排期
			$con['schedule_y'] = 0; //未到期未排期
			$con['schedule_z'] = 0; //已到期未排期
			$con['exec_total'] = 0; //已执行金额
			$con['no_exec_total'] = 0; //未执行金额
			
			$result[$con['id']] = $con;
		}

		if($contract_ids)
			$contract_ids = substr($contract_ids, 1);
		
		// 处理第二层，应用相关数据
		$m_app = D('Settlement.ContractApp');
		$apps = $m_app->where(array('contract_id'=>array('in',$contract_ids)))->select();
		
		foreach($apps as $app)
		{
			$app_ids .= ','.$app['id'];
			$result[$app['contract_id']]['apps'][$app['id']] = $app;
		}

		if($app_ids)
			$app_ids = substr($app_ids, 1);
		
		// 处理第三层，排期相关数据
		$m_sch = D('Settlement.Schedule');
		$schedules = $m_sch->where(array('app_id'=>array('in', $app_ids)))->order('schedule_date ASC')->select();
		foreach($schedules as $sch)
		{
			$result[$sch['contract_id']]['apps'][$sch['app_id']]['schedules'][$sch['id']] = $sch;
		}
		
		
		//
		// 进行排期统计
		//
		// 已排期 = 软件中排期与“排期系统”对应上的总和
		// 未到期未排 = 当前日之后～软件最后排期，软件排期与“排期系统”没对上的总和
		// 已到期未排 = 截至到当前日，软件排期与“排期系统”没对上的总和
		//
		$adv_tables = C('_proofread_.advert_tables');
		
		foreach($result as $key=>$con)
		{
			foreach($con['apps'] as $app)
			{
				foreach($app['schedules'] as $sch)
				{
					// 如果频道标记不在广告位配置数组
					if( !key_exists($sch['channel'], $adv_tables) )
						continue;
					
					// 根据频道实例化相应model
					$a = $adv_tables[$sch['channel']]['app'];
					$m = D($a['model']);
					
					$q = array(); //查询条件数组
					
					// 如果广告频道为搜索或者推荐  新增加的返回运营、广告闪屏和push中的弹窗
					if( $sch['channel']=='SK' || $sch['channel']=='SKH' || $sch['channel']=='SKD' || $sch['channel']=='PR' || $sch['channel']=='PU' || $sch['channel']=='PUP' || $sch['SAD']=='SAD' || $sch['channel']=='RE' )
					{
						if($sch['is_keyword'])
							$q[$a['find']] = array('eq', $sch['keyword']);
						else
							$q[$a['find']] = array('eq', $sch['app_package']);
						if($a['type']) //表中有分类
						{
							if($sch['channel']=='PU')  //表示PUSH推送
							$q[$a['type']] = array('eq',1);
							elseif($sch['channel']=='PUP') //表示push 弹窗
							$q[$a['type']] = array('eq',2);
							elseif($sch['channel']=='SAD') //表示闪屏中的广告闪屏
							$q[$a['type']] = array('eq',2);
						}
					}
					else
					{
						$q[$a['node_field']] = array('eq', $sch['channel_node_id']);
						$q[$a['find']] = array('eq', $sch['app_package']);
					}
					
					$t = strtotime($sch['schedule_date']);
					$q[$a['date_field'][0]] = array('elt', $t);
					$q[$a['date_field'][1]] = array('egt', $t);
					
					// 处理排期表的查询
					$exist = $m->where($q)->count();
					if($exist)
					{
						$result[$key]['schedule_x']++; //已排期+1
						$result[$key]['exec_total'] += $sch['price']; //已执行金额累加
					}
					else 
					{
						$result[$key]['no_exec_total'] += $sch['price']; //未执行金额累加
						$ts = strtotime($sch['schedule_date']);
						$current_ts = strtotime(date('Y-m-d'));
						
						if($ts > $current_ts)
						{
							$result[$key]['schedule_y']++; //未到期未排+1
						}
						else 
						{
							$result[$key]['schedule_z']++; //已到期未排+1
						}
					}
				}//foreach($app['schedules'] as $sch)
			}//foreach($con['apps'] as $app)
		}//foreach($result as $key=>$con)
		
		//
		// 计算统计信息
		//
		$total = array(
				'app_num'=>0,
				'package_num'=>0,
				'schedule_x'=>0,
				'schedule_y'=>0,
				'schedule_z'=>0,
				'invoiced_total'=>0,
				'no_invoiced_total'=>0,
				'received_total'=>0,
				'no_received_total'=>0,
				'app_discount_total'=>0,
				'exec_total' => 0,
				'no_exec_total' => 0,
				'deposited_total' => 0
		);
		
		foreach($result as $key=>$contracts)
		{
			$result[$key]['no_invoiced_total'] = $contracts['app_discount_total']-$contracts['deposited_total']-$contracts['invoiced_total'];
			$result[$key]['no_received_total'] = $contracts['app_discount_total']-$contracts['deposited_total']-$contracts['received_total'];
			
			$total['app_num'] += $contracts['app_num'];
			$total['package_num'] += $contracts['package_num'];
			$total['schedule_x'] += $contracts['schedule_x'];
			$total['schedule_y'] += $contracts['schedule_y'];
			$total['schedule_z'] += $contracts['schedule_z'];
			$total['invoiced_total'] += $contracts['invoiced_total'];
			$total['no_invoiced_total'] += $result[$key]['no_invoiced_total'];
			$total['received_total'] += $contracts['received_total'];
			$total['no_received_total'] += $result[$key]['no_received_total'];
			$total['app_discount_total'] += $contracts['app_discount_total'];
			$total['exec_total'] += $result[$key]['exec_total'];
			$total['no_exec_total'] += $result[$key]['no_exec_total'];
			$total['deposited_total'] += $contracts['deposited_total'];
		}

		// 处理下载
		$exp_list = isset($_GET['list']) && empty($_GET['list'])==FALSE ? $_GET['list'] : NULL;
		if( isset($_GET['export']) && $_GET['export']==1 )
			$this->download($result, "结算列表_按合同_".date('Y-m-d-h-i').".csv", $exp_list);
		
		// 处理分页
		import("@.ORG.Page");
		$page = new Page($count, $lr);
		$page->setConfig('header','条记录');
		$page->setConfig('first','<<');
		$page->setConfig('last','>>');
		$page_show = $page->show();
		
		$this->assign('page', $page_show);
		$this->assign('result', $result);
		$this->assign('total', $total);
		$this->display();
	}
	
	/**
	 * 按照广告位显示列表
	 */
	public function channel()
	{
		$ratecard_id = isset($_GET['ratecard_id']) && empty($_GET['ratecard_id'])==FALSE ? $_GET['ratecard_id'] : NULL;
		$advertising_id = isset($_GET['advertising_id']) && empty($_GET['advertising_id'])==FALSE ? $_GET['advertising_id'] : NULL;
		$start = isset($_GET['start']) && strtotime($_GET['start']) > 0 ? $_GET['start'] : NULL; //开始日期
		$end = isset($_GET['end']) && strtotime($_GET['end']) > 0 ? $_GET['end'] : NULL; //结束日期
		$p = isset($_GET['p']) && empty($_GET['p'])==FALSE ? $_GET['p'] : 1; //页数
		$lr = isset($_GET['lr']) && empty($_GET['lr'])==FALSE ? $_GET['lr'] : 20; //每页记录数
		
		$where_query = array();
		
		if(is_null($ratecard_id)==FALSE)
		{
			$where_query['app.rate_card_id'] = array('eq', $ratecard_id);
			$this->assign('rate_card_id', $ratecard_id);
		}
		
		if(is_null($advertising_id)==FALSE)
		{
			$where_query['app.advertising_id'] = array('eq', $advertising_id);
			$this->assign('advertising_id', $advertising_id);
		}
		
		if(is_null($start)==FALSE && is_null($end)==FALSE)
		{
			$where_query['contract.month'] = array(array('egt', date('Ym', strtotime($start))), array('elt', date('Ym', strtotime($end))) );
			$this->assign('start', $start);
			$this->assign('end', $end);
		}
		
		// 合同状态条件
		$where_query['contract.status'] = array('eq', 1);
		$where_query['card.status'] = array('eq', 1);
		$where_query['card.is_disabled'] = array('eq', 0);
		
		//
		// 处理查询
		// 
		$_m = new Model();
		$_m -> table('settlement.ad_contract_apps app')
			-> field('app.advertising_id, contract.month')
			-> join('settlement.ad_contracts contract ON contract.id=app.contract_id')
			-> join('settlement.ad_rate_cards card ON card.id=app.rate_card_id')
			-> group('app.advertising_id,contract.month')
			-> where($where_query)
			-> select();

		$count_result = $_m->query("SELECT COUNT(*) AS n FROM (".$_m->getLastSql().") t");
		$count = $count_result[0]['n'];

		$result = $_m -> table('settlement.ad_contract_apps app')
					 -> join('settlement.ad_contracts contract ON contract.id=app.contract_id')
					 -> join('settlement.ad_rate_cards card ON card.id=app.rate_card_id')
					 -> join('settlement.ad_advertisings advert ON advert.id=app.advertising_id')
					 -> field('contract.month,app.rate_card_id,card.rate_card_name,app.advertising_id,advert.advertising_name,count(app.id) AS app_num_sum, sum(app.weekday_total) AS weekday_total_sum, sum(app.weekend_total) AS weekend_total_sum')
					 -> group('app.advertising_id,contract.month')
					 -> where($where_query)
					 -> order('contract.month DESC')
					 -> page("{$p},{$lr}")
					 -> select();
		
		//
		// 通过广告位梳理排期数据,处理是否排期的验证、统计
		//
		$advertising_ids = '';
		foreach($result as $row)
			$advertising_ids .= ','.$row['advertising_id'];
		
		if($advertising_ids)
			$advertising_ids = substr($advertising_ids, 1);
		
		$schedules = $_m -> table('settlement.ad_schedules schedule')
						-> field('contract.month, schedule.*')
						-> join('settlement.ad_contracts contract ON contract.id=schedule.contract_id')
						-> where(array('schedule.advertising_id'=>array('in',$advertising_ids)))
						-> select();
		
		// 处理排期表的数据格式，用“广告位ID_合同月份”作为key检索
		$sch_data = array();
		foreach($schedules as $sch)
		{
			$sch_data["{$sch['advertising_id']}_{$sch['month']}"][$sch['id']] = $sch;
		}
		
		// 计算排期统计
		$adv_tables = C('_proofread_.advert_tables');
		
		// 用于计算统计总和
		$total = array(
			'app_num' => 0,
			'schedule_x' => 0,
			'schedule_y' => 0,
			'schedule_z' => 0,
			'discount_total' => 0,
			'exec_total' => 0,
			'no_exec_total' => 0	
		);
		
		foreach($result as $key=>$row)
		{
			$sch_key = $row['advertising_id'].'_'.$row['month'];
			
			$result[$key]['id'] = $sch_key; //id=广告位ID+_+合同编号前六位（月份）
			$result[$key]['schedule_x'] = 0; //已排期
			$result[$key]['schedule_y'] = 0; //未到期未排期
			$result[$key]['schedule_z'] = 0; //已到期为排期
			$result[$key]['exec_total'] = 0; //已执行金额
			$result[$key]['no_exec_total'] = 0; //未执行金额
			
			foreach($sch_data[$sch_key] as $sch)
			{
				// 如果频道标记不在广告位配置数组
				if( !key_exists($sch['channel'], $adv_tables) )
					continue;
					
				// 根据频道实例化相应model
				$a = $adv_tables[$sch['channel']]['app'];
				$m = D($a['model']);
					
				$q = array(); //查询条件数组
					
				// 如果广告频道为搜索或者推荐
				if( $sch['channel']=='SK' || $sch['channel']=='SKH' || $sch['channel']=='SKD' || $sch['channel']=='PR' || $sch['channel']=='PU' || $sch['channel']=='PUP' || $sch['SAD']=='SAD' || $sch['channel']=='RE' )
				{
					if($sch['is_keyword'])
						$q[$a['find']] = array('eq', $sch['keyword']);
					else
						$q[$a['find']] = array('eq', $sch['app_package']);
					if($a['type']) //表中有分类
					{
						if($sch['channel']=='PU')  //表示PUSH推送
						$q[$a['type']] = array('eq',1);
						elseif($sch['channel']=='PUP') //表示push 弹窗
						$q[$a['type']] = array('eq',2);
						elseif($sch['channel']=='SAD') //表示闪屏中的广告闪屏
						$q[$a['type']] = array('eq',2);
					}
				}
				else
				{
					$q[$a['node_field']] = array('eq', $sch['channel_node_id']);
					$q[$a['find']] = array('eq', $sch['app_package']);
				}
					
				$t = strtotime($sch['schedule_date']);
				$q[$a['date_field'][0]] = array('elt', $t);
				$q[$a['date_field'][1]] = array('egt', $t);
					
				// 处理排期表的查询
				$exist = $m->where($q)->count();
				if($exist)
				{
					$result[$key]['schedule_x']++; //已排期+1
					$result[$key]['exec_total'] += $sch['price']; //已执行金额累加
				}
				else
				{
					$result[$key]['no_exec_total'] += $sch['price']; //未执行金额累加
					$ts = strtotime($sch['schedule_date']);
					$current_ts = strtotime(date('Y-m-d'));
				
					if($ts > $current_ts)
					{
						$result[$key]['schedule_y']++; //未到期未排+1
					}
					else
					{
						$result[$key]['schedule_z']++; //已到期未排+1
					}
				}
			}
			
			$total['app_num'] += $result[$key]['app_num_sum'];
			$total['schedule_x'] += $result[$key]['schedule_x'];
			$total['schedule_y'] += $result[$key]['schedule_y'];
			$total['schedule_z'] += $result[$key]['schedule_z']; 
			$total['discount_total'] += $result[$key]['weekday_total_sum']+$result[$key]['weekend_total_sum'];
			$total['exec_total'] += $result[$key]['exec_total'];
			$total['no_exec_total'] += $result[$key]['no_exec_total'];
		}
		
		
		// 处理下载
		$exp_list = isset($_GET['list']) && empty($_GET['list'])==FALSE ? $_GET['list'] : NULL;
		if( isset($_GET['export']) && $_GET['export']==1 )
			$this->channel_download($result, "结算列表_按频道_".date('Y-m-d-h-i').".csv", $exp_list);
		
		
		// 查询刊例
		$m_card = D('Settlement.RateCard');
		$card = $m_card	-> field('id,rate_card_name')
						-> where(array('status'=>array('eq',1), 'is_disabled'=>array('eq',0)))
						-> select();
		$this->assign('card', $card);

		// 如果用户搜索了刊例和广告位，则处理广告位
		if($ratecard_id && $advertising_id)
		{
			$m_advert = D('Settlement.Advertising');
			$advert = $m_advert->where(array('rate_card_id'=>$ratecard_id))->order('id ASC')->select();
			$this->assign('advert', $advert);
		}
		
		// 处理分页
		import("@.ORG.Page");
		$page = new Page($count, $lr);
		$page->setConfig('header','条记录');
		$page->setConfig('first','<<');
		$page->setConfig('last','>>');
		$page_show = $page->show();
		
		$this->assign('page', $page_show);
		$this->assign('result', $result);
		$this->assign('total', $total);
		$this->display();
	}
	
	/**
	 * 处理导出报表
	 * @param unknown $reuslt
	 */
	private function download($result, $filename, $list=NULL, $flag=FALSE)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
	
		if(is_null($list)==FALSE)
			$list = explode('|', $list);
		
		$total = array(
			'app_num' => 0,
			'package_num' => 0,
			'schedule_x' => 0,
			'schedule_y' => 0,
			'schedule_z' => 0,
			'invoiced_total' => 0,
			'no_invoiced_total' => 0,
			'received_total' => 0,
			'no_received_total' => 0,
			'app_discount_total' => 0,
			'app_schedule_price' => 0,
			'exec_total' => 0,
			'no_exec_total' => 0,
			'deposited_total' => 0
		);
		
		$out = "月份,合同编号,客户名称,负责人,合作软件,已排期,未到期未排,已到期未排,已开发票,未开发票,已收款,已抵扣,未收款,折扣后总价,执行价格,未执行价格\r\n";
	
		foreach($result as $item)
		{
			if(is_array($list) && in_array($item['id'], $list))
			{
				if($flag)
					$discount = $item['app_schedule_price'];
				else 
					$discount = $item['app_discount_total'];
				
				$out.="{$item['month']},{$item['contract_code']},{$item['client_name']},{$item['responsible']},{$item['package_num']},{$item['schedule_x']},{$item['schedule_y']},{$item['schedule_z']},{$item['invoiced_total']},{$item['no_invoiced_total']},{$item['received_total']},{$item['deposited_total']},{$item['no_received_total']},{$discount},{$item['exec_total']},{$item['no_exec_total']}\r\n";
				
				$total['app_num'] += $item['app_num'];
				$total['package_num'] += $item['package_num'];
				$total['schedule_x'] += $item['schedule_x'];
				$total['schedule_y'] += $item['schedule_y'];
				$total['schedule_z'] += $item['schedule_z'];
				$total['invoiced_total'] += $item['invoiced_total'];
				$total['no_invoiced_total'] += $item['no_invoiced_total'];
				$total['received_total'] += $item['received_total'];
				$total['no_received_total'] += $item['no_received_total'];
				$total['app_discount_total'] += $item['app_discount_total'];
				$total['app_schedule_price'] += $item['app_schedule_price'];
				$total['exec_total'] += $item['exec_total'];
				$total['no_exec_total'] += $item['no_exec_total'];
				$total['deposited_total'] += $item['deposited_total'];
			}
		}
		
		if($flag)
			$discount_total = $total['app_schedule_price'];
		else 
			$discount_total = $total['app_discount_total'];
		 
		$out.="合计,,,,{$total['package_num']},{$total['schedule_x']},{$total['schedule_y']},{$total['schedule_z']},{$total['invoiced_total']},{$total['no_invoiced_total']},{$total['received_total']},{$total['deposited_total']},{$total['no_received_total']},{$discount_total},{$total['exec_total']},{$total['no_exec_total']}\r\n";

		echo iconv('utf-8', 'gb2312', $out);
		exit;
	}
	
	/**
	 * 按频道处理导出报表
	 * @param unknown $reuslt
	 */
	private function channel_download($result, $filename, $list=NULL)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
	
		if(is_null($list)==FALSE)
			$list = explode('|', $list);
	
		$total = array(
			'app_num' => 0,
			'schedule_x' => 0,
			'schedule_y' => 0,
			'schedule_z' => 0,
			'discount_total' => 0,
			'exec_total' => 0,
			'no_exec_total' => 0	
		);
	
		$out = "月份,刊例,广告位,合作软件,已排期,未到期未排,已到期未排,折扣后总价,执行金额,未执行金额\r\n";
	
		foreach($result as $item)
		{
			if(is_array($list) && in_array($item['id'], $list))
			{
				$discount_total = $item['weekday_total_sum']+$item['weekend_total_sum'];
				
				$out.="{$item['month']},{$item['rate_card_name']},{$item['advertising_name']},{$item['app_num_sum']},{$item['schedule_x']},{$item['schedule_y']},{$item['schedule_z']},{$discount_total},{$item['exec_total']},{$item['no_exec_total']}\r\n";
	
				$total['app_num'] += $item['app_num'];
				$total['schedule_x'] += $item['schedule_x'];
				$total['schedule_y'] += $item['schedule_y'];
				$total['schedule_z'] += $item['schedule_z'];
				$total['discount_total'] += $discount_total;
				$total['exec_total'] += $item['exec_total'];
				$total['no_exec_total'] += $item['no_exec_total'];
			}
		}
	
		$out.=",,,{$total['app_num']},{$total['schedule_x']},{$total['schedule_y']},{$total['schedule_z']},{$total['discount_total']},{$total['exec_total']},{$total['no_exec_total']}\r\n";
	
		echo iconv('utf-8', 'gb2312', $out);
		exit;
	}
	
	//2014.12.15 jiwei
	/**
	 * 将排期作为主要条件查询，影响广告排期、折扣后总价、广告金额
	 */
	public function schedule()
	{
		$contract_code = isset($_GET['contract_code']) && empty($_GET['contract_code'])==FALSE ? trim($_GET['contract_code']) : NULL;
		$client_name = isset($_GET['client_name']) && empty($_GET['client_name'])==FALSE ? trim($_GET['client_name']) : NULL;
		$responsible = isset($_GET['responsible']) && empty($_GET['responsible'])==FALSE ? trim($_GET['responsible']) : NULL;
		$start = isset($_GET['start']) && strtotime($_GET['start']) > 0 ? $_GET['start'] : NULL; //开始日期
		$end = isset($_GET['end']) && strtotime($_GET['end']) > 0 ? $_GET['end'] : NULL; //结束日期
		$p = isset($_GET['p']) && empty($_GET['p'])==FALSE ? $_GET['p'] : 1; //页数
		$lr = isset($_GET['lr']) && empty($_GET['lr'])==FALSE ? $_GET['lr'] : 20; //每页记录数
		$flag = false; //用于标记用户是否选择了投放日期范围
		
		$where_query = array();
		
		if(is_null($contract_code)==FALSE)
		{
			$where_query['contract.contract_code'] = array('like', "%{$contract_code}%");
			$this->assign('contract_code', $contract_code);
		}
		
		if(is_null($client_name)==FALSE)
		{
			$where_query['client.client_name'] = array('like', "%{$client_name}%");
			$this->assign('client_name', $client_name);
		}
		
		if(is_null($responsible)==FALSE)
		{
			$where_query['responsible'] = array('like', "%{$responsible}%");
			$this->assign('responsible', $responsible);
		}
		
		if(is_null($start)==FALSE && is_null($end)==FALSE)
		{
			//
			// 如果输入了投放日期区间，则先查询排期，通过排期来组织合同和软件
			//
			$m_schedule = D('Settlement.Schedule');
			
			//投放日期内的所有合同ID
			$schedules = $m_schedule -> field('contract_id, SUM(price) AS sum_price')
									 -> where(array('schedule_date'=>array(array('egt',$start),array('elt',$end))))
									 -> group('contract_id')
									 -> select();
			
			$contract_ids = '';
			foreach($schedules as $sch)
			{
				$contract_ids .= ','.$sch['contract_id'];
				$app_schedule_price[$sch['contract_id']] = $sch['sum_price'];
			}
			
			if($contract_ids)
				$contract_ids = substr($contract_ids, 1);
			
			$where_query['contract.id'] = array('in', $contract_ids);
			
			//投放日期内所有的软件id
			$schedules = $m_schedule -> field('app_id')
									 -> where(array('schedule_date'=>array(array('egt',$start),array('elt',$end))))
									 -> group('app_id')
									 -> select();
			
			$schedule_app_ids = '';
			foreach($schedules as $sch)
				$schedule_app_ids .= ','.$sch['app_id'];
			
			if($schedule_app_ids)
				$schedule_app_ids = substr($schedule_app_ids, 1);
			
			
			$flag = true;
			$this->assign('_S', $flag);
			$this->assign('start', $start);
			$this->assign('end', $end);
		}
		
		$where_query['contract.status'] = array('eq', 1);
		
		//
		// 处理查询
		//
		$model = new Model();
		
		$count = $model -> table('settlement.ad_contracts contract')
						-> join('settlement.ad_clients client ON client.id=contract.client_id')
						-> where($where_query)
						-> count();
		
		$contracts = $model -> table('settlement.ad_contracts contract')
							-> field('contract.id, client.client_name, contract.month, contract.contract_code, contract.app_num, contract.package_num, contract.invoiced_total, contract.received_total, contract.app_discount_total, contract.deposited_total, contract.responsible')
							-> join('settlement.ad_clients client ON client.id=contract.client_id')
							-> where($where_query)
							-> order('contract.month DESC,CONVERT(client.client_name USING gbk) ASC')
							-> page("{$p},{$lr}")
							-> select();
		
		//
		// 生成结果集数组，避免多次循环嵌套查询数据库
		//
		$result = array();
		$contract_ids = ''; //用于保存合同id列表，逗号分隔
		$app_ids = ''; //用于保存应用id列表，逗号分隔
	
		// 处理第一层，合同相关数据
		foreach($contracts as $con)
		{
			$contract_ids .= ','.$con['id'];
				
			$con['schedule_x'] = 0; //已排期
			$con['schedule_y'] = 0; //未到期未排期
			$con['schedule_z'] = 0; //已到期未排期
			$con['exec_total'] = 0; //已执行金额
			$con['no_exec_total'] = 0; //未执行金额
			$con['app_schedule_price'] = 0; //排期范围内的软件总价格
			
			if($flag)
				$con['app_schedule_price'] = $app_schedule_price[$con['id']];
				
			$result[$con['id']] = $con;
		}
		
		if($contract_ids)
			$contract_ids = substr($contract_ids, 1);
		
		// 处理第二层，应用相关数据
		$m_app = D('Settlement.ContractApp');
		
		if($flag)
			$app_where['id'] = array('in',$schedule_app_ids);
		
		$app_where['contract_id'] = array('in',$contract_ids);
		
		$apps = $m_app->where($app_where)->select();
		
		$tmp_pack = array();
		foreach($apps as $app)
		{
			$app_ids .= ','.$app['id'];
			$result[$app['contract_id']]['apps'][$app['id']] = $app;

			if($flag)
			{
				$tmp_pack[$app['contract_id']][$app['app_package']] = 1; //统计用软件包名个数
				$result[$app['contract_id']]['package_num'] = count($tmp_pack[$app['contract_id']]);
			}
		}
		
		if($app_ids)
			$app_ids = substr($app_ids, 1);
		
		// 处理第三层，排期相关数据
		$m_sch = D('Settlement.Schedule');
		
		$sch_where['app_id'] = array('in', $app_ids);
		if($flag)
			$sch_where['schedule_date']=array(array('egt',$start),array('elt',$end));
		
		$schedules = $m_sch->where($sch_where)->order('schedule_date ASC')->select();
		foreach($schedules as $sch)
		{
			$result[$sch['contract_id']]['apps'][$sch['app_id']]['schedules'][$sch['id']] = $sch;
		}
		
		
		//
		// 进行排期统计
		//
		// 已排期 = 软件中排期与“排期系统”对应上的总和
		// 未到期未排 = 当前日之后～软件最后排期，软件排期与“排期系统”没对上的总和
		// 已到期未排 = 截至到当前日，软件排期与“排期系统”没对上的总和
		//
		$adv_tables = C('_proofread_.advert_tables');
		
		foreach($result as $key=>$con)
		{
			foreach($con['apps'] as $app)
			{
				foreach($app['schedules'] as $sch)
				{
					// 如果频道标记不在广告位配置数组
					if( !key_exists($sch['channel'], $adv_tables) )
						continue;
						
					// 根据频道实例化相应model
					$a = $adv_tables[$sch['channel']]['app'];
					$m = D($a['model']);
						
					$q = array(); //查询条件数组
						
					// 如果广告频道为搜索或者推荐
					if( $sch['channel']=='SK' || $sch['channel']=='SKH' || $sch['channel']=='SKD' || $sch['channel']=='PR' || $sch['channel']=='PU' || $sch['channel']=='PUP' || $sch['SAD']=='SAD' || $sch['channel']=='RE' )
					{
						if($sch['is_keyword'])
							$q[$a['find']] = array('eq', $sch['keyword']);
						else
							$q[$a['find']] = array('eq', $sch['app_package']);
						if($a['type']) //表中有分类
						{
							if($sch['channel']=='PU')  //表示PUSH推送
							$q[$a['type']] = array('eq',1);
							elseif($sch['channel']=='PUP') //表示push 弹窗
							$q[$a['type']] = array('eq',2);
							elseif($sch['channel']=='SAD') //表示闪屏中的广告闪屏
							$q[$a['type']] = array('eq',2);
						}
					}
					else
					{
						$q[$a['node_field']] = array('eq', $sch['channel_node_id']);
						$q[$a['find']] = array('eq', $sch['app_package']);
					}
						
					$t = strtotime($sch['schedule_date']);
					$q[$a['date_field'][0]] = array('elt', $t);
					$q[$a['date_field'][1]] = array('egt', $t);
						
					// 处理排期表的查询
					$exist = $m->where($q)->count();
					if($exist)
					{
						$result[$key]['schedule_x']++; //已排期+1
						$result[$key]['exec_total'] += $sch['price']; //已执行金额累加
					}
					else
					{
						$result[$key]['no_exec_total'] += $sch['price']; //未执行金额累加
						$ts = strtotime($sch['schedule_date']);
						$current_ts = strtotime(date('Y-m-d'));
		
						if($ts > $current_ts)
						{
							$result[$key]['schedule_y']++; //未到期未排+1
						}
						else
						{
							$result[$key]['schedule_z']++; //已到期未排+1
						}
					}
				}//foreach($app['schedules'] as $sch)
			}//foreach($con['apps'] as $app)
		}//foreach($result as $key=>$con)
		
		//
		// 计算统计信息
		//
		$total = array(
				'app_num'=>0,
				'package_num'=>0,
				'schedule_x'=>0,
				'schedule_y'=>0,
				'schedule_z'=>0,
				'invoiced_total'=>0,
				'no_invoiced_total'=>0,
				'received_total'=>0,
				'no_received_total'=>0,
				'app_discount_total'=>0,
				'exec_total' => 0,
				'no_exec_total' => 0
		);
		
		foreach($result as $key=>$contracts)
		{
			$result[$key]['no_invoiced_total'] = $contracts['app_discount_total']-$contracts['deposited_total']-$contracts['invoiced_total'];
			$result[$key]['no_received_total'] = $contracts['app_discount_total']-$contracts['deposited_total']-$contracts['received_total'];
				
			$total['app_num'] += $contracts['app_num'];
			$total['package_num'] += $contracts['package_num'];
			$total['schedule_x'] += $contracts['schedule_x'];
			$total['schedule_y'] += $contracts['schedule_y'];
			$total['schedule_z'] += $contracts['schedule_z'];
			$total['invoiced_total'] += $contracts['invoiced_total'];
			$total['no_invoiced_total'] += $result[$key]['no_invoiced_total'];
			$total['received_total'] += $contracts['received_total'];
			$total['no_received_total'] += $result[$key]['no_received_total'];
			$total['app_discount_total'] += $contracts['app_discount_total'];
			$total['app_schedule_price'] += $contracts['app_schedule_price'];
			$total['exec_total'] += $result[$key]['exec_total'];
			$total['no_exec_total'] += $result[$key]['no_exec_total'];
		}
		
		// 处理下载
		$exp_list = isset($_GET['list']) && empty($_GET['list'])==FALSE ? $_GET['list'] : NULL;
		if( isset($_GET['export']) && $_GET['export']==1 )
			$this->download($result, "结算列表_按排期_".date('Y-m-d-h-i').".csv", $exp_list, $flag);
		
		// 处理分页
		import("@.ORG.Page");
		$page = new Page($count, $lr);
		$page->setConfig('header','条记录');
		$page->setConfig('first','<<');
		$page->setConfig('last','>>');
		$page_show = $page->show();
		
		$this->assign('page', $page_show);
		$this->assign('result', $result);
		$this->assign('total', $total);
		$this->display();
	}


            /*导入收款数据*/
            public function import_data1() 
            {
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('csv');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');location.href='/index.php/Settlement/ContractSettlement/index';</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');location.href='/index.php/Settlement/ContractSettlement/index'</script>";
			exit;
		}

                // 处理数据 读取csv数据
                $handle = fopen($info[0]['savepath'].$info[0]['savename'], "r");
                if ($handle === false) {
                    return -1;
                }
                $i = $j = 0;
                $content_arr = array();
                while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
                    if ($i == 0) {
                        // 读入标题列
                        $title_arr = $line_arr;
                    } else {
                        // 读入每行内容
                        $content_arr[$j] = $line_arr;
                        $j++;
                    }
                    $i++;
                }
                fclose($handle);

                $Receive_db = D("Settlement.ContractReceive");
                $m_contract = D('Settlement.Contract');
                
                $error_ids = '';
                $succ_ids= '';
                foreach($content_arr as $v){
                        $ht_id = $v[1];

                        if(empty($v[0])||empty($v[1])||empty($v[2])||empty($v[3])){
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }

                        $data['id'] = trim($v[1]); //todo 自增ID 不用写
                        $data['contract_id'] = trim($v[0]);
                        $data['collection_date'] = trim($v[3]);
                        $data['collection_cash'] = trim($v[2]);
                        foreach ($data as $d) {
                            if (!isset($d) || $d == "") {
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                            }
                        }

                        if (!is_numeric($data['collection_cash'])) {
                                //$this->error("收款必须是数字！");
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }
                        $data['admin_id'] = $_SESSION['admin']['admin_id'];
                        $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                        $data['create_tm'] = time();
                        $data['update_tm'] = time();
                        $data['remark'] = trim(iconv('gb2312','utf-8',$v[4]));

                        //准备写入数据
                        //开启事物
                        $Receive_db->startTrans();
                        $result = $Receive_db->add($data);
                        if ($result) {
                            // 2014.9.16 jiwei
                            // 加入相关表的统计字段更新
                            $_contract = $m_contract->find($data['contract_id']);
                            $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                                    ->save(array('update_tm' => time(), 'received_total' => $_contract['received_total'] + $data['collection_cash']));
                            if (!$m_contract_) {
                                $Receive_db->rollback();
                                //$this->error("录入收款失败！");
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                            }
                            // END
                            //$this->writelog("广告结算：合同-新录入了contract_id为[" . $data['contract_id'] . "]的收款。【广告结算】", 'sj_contract_receives',$data['contract_id'],__ACTION__ ,'','add');
                            $Receive_db->commit();
                            //$this->success("录入收款成功！");
                        } else {
                            $Receive_db->rollback();
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                            //$this->error("添加失败");
                        }
                        $succ_ids = $succ_ids.$ht_id.',';
                }

                $error_ids =substr($error_ids,0,-1);
                $succ_ids =substr($succ_ids,0,-1);
                if($error_ids!=''){
		    echo "<script>alert('ID为".$error_ids."的数据导入失败,请检查必要字段是否为空，或者金额不是数字,或者收款ID已存在!');location.href='/index.php/Settlement/ContractSettlement/index'</script>";//todo
                }else{
		    echo "<script>alert('导入成功');location.href='/index.php/Settlement/ContractSettlement/index'</script>";
                }
			
		//写日志
                if($succ_ids!=''){

		    $this -> writelog("广告结算:批量导入了收款,ID为".$succ_ids);
                }
            }


            /*导入发票数据*/
            public function import_data2() 
            {
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('csv');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');location.href='/index.php/Settlement/ContractSettlement/index';</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');location.href='/index.php/Settlement/ContractSettlement/index'</script>";
			exit;
		}

                // 处理数据 读取csv数据
                $handle = fopen($info[0]['savepath'].$info[0]['savename'], "r");
                if ($handle === false) {
                    return -1;
                }
                $i = $j = 0;
                $content_arr = array();
                while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
                    if ($i == 0) {
                        // 读入标题列
                        $title_arr = $line_arr;
                    } else {
                        // 读入每行内容
                        $content_arr[$j] = $line_arr;
                        $j++;
                    }
                    $i++;
                }
                fclose($handle);

                $Invoice_db = D("Settlement.ContractInvoice");
                $m_contract = D('Settlement.Contract');
                
                $error_ids = '';
                $succ_ids= '';
                foreach($content_arr as $v){
                        $ht_id = $v[1];
                        if(empty($v[0])||empty($v[1])||empty($v[2])||empty($v[3])){
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }

                        $data['id'] = trim($v[1]); //todo 自增ID 不用写
                        $data['contract_id'] = trim($v[0]);
                        $data['invoice_date'] = trim($v[3]);
                        $data['invoice_cash'] = trim($v[2]);
                        $data['invoice_code'] = $v[4];
                        foreach ($data as $d) {
                            if (!isset($d) || $d == "") {
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                            }
                        }

                        if (!is_numeric($data['invoice_cash'])) {
                                //$this->error("收款必须是数字！");
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }
                        $data['admin_id'] = $_SESSION['admin']['admin_id'];
                        $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                        $data['create_tm'] = time();
                        $data['update_tm'] = time();
                        $data['remark'] = trim(iconv('gb2312','utf-8',$v[5]));

                        //准备写入数据
                        //开启事物
                        $Invoice_db->startTrans();
                        $result = $Invoice_db->add($data);
                        if ($result) {
                            // 2014.9.16 jiwei
                            // 加入相关表的统计字段更新
                            $_contract = $m_contract->find($data['contract_id']);
                            $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                                    ->save(array('update_tm' => time(), 'invoiced_total' => $_contract['invoiced_total'] + $data['invoice_cash']));
                            if (!$m_contract_) {
                                $Invoice_db->rollback();
                                //$this->error("录入发票失败！");
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                            }
                            // END
                            $Invoice_db->commit();
                            //$this->success("录入发票成功！");
                        } else {
                            $Invoice_db->rollback();
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                            //$this->error("添加失败");
                        }
                        $succ_ids = $succ_ids.$ht_id.',';
                }

                $error_ids =substr($error_ids,0,-1);
                $succ_ids =substr($succ_ids,0,-1);
                if($error_ids!=''){
		    echo "<script>alert('ID为".$error_ids."的数据导入失败,请检查必要字段是否为空，或者金额不是数字,或者发票ID已存在!');location.href='/index.php/Settlement/ContractSettlement/index'</script>";//todo
                }else{
		    echo "<script>alert('导入成功');location.href='/index.php/Settlement/ContractSettlement/index'</script>";
                }

                //写日志
                if($succ_ids!=''){

		    $this -> writelog("广告结算:批量导入了发票,ID为".$succ_ids);
                }

            }



            /*导入保证金数据*/
            public function import_data3() 
            {
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('csv');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');location.href='/index.php/Settlement/ContractSettlement/index';</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');location.href='/index.php/Settlement/ContractSettlement/index'</script>";
			exit;
		}

                // 处理数据 读取csv数据
                $handle = fopen($info[0]['savepath'].$info[0]['savename'], "r");
                if ($handle === false) {
                    return -1;
                }
                $i = $j = 0;
                $content_arr = array();
                while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
                    if ($i == 0) {
                        // 读入标题列
                        $title_arr = $line_arr;
                    } else {
                        // 读入每行内容
                        $content_arr[$j] = $line_arr;
                        $j++;
                    }
                    $i++;
                }
                fclose($handle);

                $Invoice_db = D("Settlement.ContractInvoice");
                $m_contract = D('Settlement.Contract');
                $agreement_db = D("Settlement.Agreement");
                $Deposit_db = D("Settlement.ContractDeposit");
                
                $error_ids = '';
                $succ_ids = '';
                foreach($content_arr as $v){
                        $ht_id = $v[1];
                        if(empty($v[0])||empty($v[1])||empty($v[2])){
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }

                        $data['id'] = trim($v[1]); //todo 自增ID 不用写
                        $contract_id = $data['contract_id'] = trim($v[0]);
                        $data['deduct_date'] = date("Y-m-d", time());
                        $data['deduct_cash'] = trim($v[2]);
                        foreach ($data as $d) {
                            if (!isset($d) || $d == "") {
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                            }
                        }

                        if (!is_numeric($data['deduct_cash'])) {
                                //$this->error("收款必须是数字！");
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                        }
                        $data['admin_id'] = $_SESSION['admin']['admin_id'];
                        $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                        $data['create_tm'] = time();
                        $data['update_tm'] = time();
                        $data['remark'] = trim(iconv('gb2312','utf-8',$v[3]));

                        $get_data1 = $m_contract->where(array("id" => $contract_id))->select();
                        $agreement_id = $get_data1[0]["agreement_id"];
                        $get_data2 = $agreement_db->where(array('id' => $agreement_id, 'status' => 1))->select();
                        $daidikoushoukuan = $get_data2[0]['deductible_cash'];

                        if ($data['deduct_cash'] > $daidikoushoukuan) {
                            //$this->error("保证金额要小于等于待抵扣保证金收款！");
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                        }


                        //准备写入数据
                        //启动事务
                        $Deposit_db->startTrans();
                        $result = $Deposit_db->add($data);
                        if ($result) {
                            // 2014.9.16 jiwei
                            // 加入相关表的统计字段更新
                            $_contract = $m_contract->find($data['contract_id']);
                            $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                                    ->save(array('update_tm' => time(), 'deposited_total' => $_contract['deposited_total'] + $data['deduct_cash']));
                            if (!$m_contract_) {
                                //失败，回滚
                                $Deposit_db->rollback();
                                            $error_ids=$error_ids.$ht_id.',';
                                            continue;
                                //$this->error("添加出错！");
                            }
                            // END
                            // 2014.9.17 jiwei
                            // 处理框架协议保证金余额变化
                            $m_agreement = D('Settlement.Agreement');
                            $_agreement = $m_agreement->find($_contract['agreement_id']);
                            $m_agreement_ = $m_agreement->where(array('id' => $_agreement['id']))
                                    ->save(array('update_tm' => time(), 'deductible_cash' => $_agreement['deductible_cash'] - $data['deduct_cash']));
                            if (!$m_agreement_) {
                                //失败，回滚
                                $Deposit_db->rollback();
                                //$this->error("添加出错！");
                                            $error_ids=$error_ids.$ht_id.',';
                                            continue;
                            }
                            // END

                            //$this->writelog("广告结算：合同-新录入了contract_id为[" . $data['contract_id'] . "]的保证金详情。【广告结算】", 'sj_contract_deposits',$data['contract_id'],__ACTION__ ,'','add');
                            $Deposit_db->commit();
                            //$this->success("录入保证金详情成功！");
                        } else {
                            $Deposit_db->rollback();

                                            $error_ids=$error_ids.$ht_id.',';
                                            continue;
                            //$this->error("添加失败");
                        }
                        $succ_ids = $succ_ids.$ht_id.',';
                }

                $error_ids =substr($error_ids,0,-1);
                $succ_ids =substr($succ_ids,0,-1);
                if($error_ids!=''){
		    echo "<script>alert('ID为".$error_ids."的数据导入失败,请检查必要字段是否为空，或者金额不是数字,或者保证金ID已存在!');location.href='/index.php/Settlement/ContractSettlement/index'</script>";//todo
                }else{
		    echo "<script>alert('导入成功');location.href='/index.php/Settlement/ContractSettlement/index'</script>";
                }

		//写日志
                if($succ_ids!=''){
		    $this -> writelog("广告结算:批量导入了保证金,ID为".$succ_ids);
                }
            }
}
