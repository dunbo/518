<?php
/**
 * 广告结算-合同相关连的软件
 * 
 * @author jiwei
 *
 */
class ContractAppAction extends CommonAction 
{
	/**
	 * 合同软件列表页
	 */
	public function index()
	{
		$contract_code = isset($_GET['contract_code']) && empty($_GET['contract_code'])==FALSE ? trim($_GET['contract_code']) : NULL; //合同编号
		$client_name = isset($_GET['client_name']) && empty($_GET['client_name'])==FALSE ? trim($_GET['client_name']) : NULL; //客户名称
		$s = isset($_GET['ss']) && ($_GET['ss']==1 || $_GET['ss']==2) ? $_GET['ss'] : 0; 
		$q = isset($_GET['q']) && empty($_GET['q'])==FALSE ? trim($_GET['q']) : NULL; //关键字
		$ratecard_id = isset($_GET['ratecard_id']) && empty($_GET['ratecard_id'])==FALSE ? $_GET['ratecard_id'] : 0; //刊例ID
		$advertising_id = isset($_GET['advertising_id']) && empty($_GET['advertising_id']) == FALSE ? $_GET['advertising_id'] : 0; //广告位ID
		$start = isset($_GET['start']) && strtotime($_GET['start']) > 0 ? $_GET['start'] : NULL; //开始日期
		$end = isset($_GET['end']) && strtotime($_GET['end']) > 0 ? $_GET['end'] : NULL; //结束日期
		$p = isset($_GET['p']) && empty($_GET['p'])==FALSE ? $_GET['p'] : 1; //页数
		$lr = isset($_GET['lr']) && empty($_GET['lr'])==FALSE ? $_GET['lr'] : 20; //每页记录数
		
		$flag = false; //2014.12.12 jiwei 是否进行了投放日期的选择
		$schedule_stat = null; //2014.12.12 jiwei 排期统计数据 

		$where_query = array();
		
		//合同状态不能为删除
		$where_query['contract.status'] = array('eq', 1);
		
		//合同编号
		if(is_null($contract_code)==FALSE)
		{
			$where_query['contract.contract_code'] = array('like', "%{$contract_code}%");
			$this->assign('contract_code', $contract_code);
		}
		
		//客户名称
		if(is_null($client_name)==FALSE)
		{
			$where_query['client.client_name'] = array('like', "%{$client_name}%");
			$this->assign('client_name', $client_name);
		}
		
		//应用名称或包名
		if(is_null($q)==FALSE)
		{
			if($s==1)
				$where_query['app.app_name'] = array('like', "%{$q}%");
			elseif($s==2)
				$where_query['app.app_package'] = array('eq', $q);
			
			$this->assign('q', $q);
		}

		//刊例
		if($ratecard_id != 0)
		{
			$where_query['app.rate_card_id'] = array('eq', $ratecard_id);
			$this->assign('rate_card_id', $ratecard_id);
		}
		
		//广告位
		if($advertising_id != 0)
		{
			$where_query['app.advertising_id'] = array('eq', $advertising_id);
			$this->assign('advertising_id', $advertising_id);
		}
		
		//排期区间
		if(is_null($start)==FALSE && is_null($end)==FALSE)
		{
			$m_schedule = D('Settlement.Schedule');
			
			//2014.12.12 jiwei
			/*
			$schedule = $m_schedule	-> field('app_id')
									-> where(array('schedule_date'=>array(array('egt',$start),array('lt',$end))))
									-> group('app_id')
									-> select();
			
			$in_str = '';
			foreach($schedule as $row)
				$in_str .= ','.$row['app_id'];
				
			if($in_str)
				$in_str = substr($in_str,1);
			*/
			
			//2014.12.12 jiwei
			//根据合同id和软件软件id统计平日和周末天天数，还有价格
			$schedule = $m_schedule	-> field('app_id, contract_id, price, original_price, is_weekend')
									-> where(array('schedule_date'=>array(array('egt',$start),array('elt',$end))))
									-> select();
			
			$app_ids = array();
			
			foreach($schedule as $row)
			{
				if(!isset($schedule_stat[$row['contract_id']][$row['app_id']]['weekdays']))
					$schedule_stat[$row['contract_id']][$row['app_id']]['weekdays'] = 0;
				
				if(!isset($schedule_stat[$row['contract_id']][$row['app_id']]['weekends']))
					$schedule_stat[$row['contract_id']][$row['app_id']]['weekends'] = 0;
				
				if(!isset($schedule_stat[$row['contract_id']][$row['app_id']]['price']))
					$schedule_stat[$row['contract_id']][$row['app_id']]['price'] = 0;
				
				if(!isset($schedule_stat[$row['contract_id']][$row['app_id']]['original_price']))
					$schedule_stat[$row['contract_id']][$row['app_id']]['original_price'] = 0;
				
				if($row['is_weekend'])
					$schedule_stat[$row['contract_id']][$row['app_id']]['weekends']++;
				else 
					$schedule_stat[$row['contract_id']][$row['app_id']]['weekdays']++;

				$schedule_stat[$row['contract_id']][$row['app_id']]['price'] = $row['price'];
				$schedule_stat[$row['contract_id']][$row['app_id']]['original_price'] = $row['original_price'];
				
				$app_ids[$row['app_id']] = $row['app_id'];
			}

			$flag = true;
			$this->assign('_S', $flag); //标志用户选择了排期范围
			$this->assign('stat', $schedule_stat);
			
			$in_str = implode(',', $app_ids);
		
			///////
			//end//
			///////
			
			$where_query['app.id'] = array('in', $in_str);
			
			$this->assign('start', $start);
			$this->assign('end', $end);
		}
		
		$m = new Model();
		
		$count = $m	-> table('settlement.ad_contract_apps app')
					-> join('settlement.ad_contracts contract ON contract.id=app.contract_id')
					-> join('settlement.ad_clients client ON client.id=app.client_id')
					-> join('settlement.ad_advertisings advert ON advert.id=app.advertising_id')
					-> where($where_query)
					-> count();

		$result = $m -> table('settlement.ad_contract_apps app')
					 -> field('app.id, app.app_name, app.app_package, app.contract_id, contract.month, contract.contract_code, contract.agreement_code, client.client_name, advert.advertising_name, app.weekday_total, app.weekend_total, app.weekday_original_total, app.weekend_original_total, app.weekdays, app.weekends')
					 -> join('settlement.ad_contracts contract ON contract.id=app.contract_id')
					 -> join('settlement.ad_clients client ON client.id=app.client_id')
					 -> join('settlement.ad_advertisings advert ON advert.id=app.advertising_id')
					 -> where($where_query)
					 -> order('id DESC')
					 -> page("{$p},{$lr}")
					 -> select();
		
		// 处理下载
		if( isset($_GET['export']) && $_GET['export']==1 )
			$this->download($result, "合同软件列表_".date('Y-m-d-h-i').".csv", $flag, $schedule_stat);
		
		// 处理下载校对表
		if( isset($_GET['proofread']) && $_GET['proofread']==1 )
			$this->proofread($result, "合同软件列表_校对报表_".date('Y-m-d-h-i').".csv");
		
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
		
		
		$this->assign('result', $result);
		$this->assign('page', $page_show);
		$this->display();
	}
	
	/**
	 * 显示和某个合同关联的所有软件，可以在该页面进行添加和编辑操作
	 */
	public function detail()
	{
		$contract_id = isset($_GET['contract_id']) && empty($_GET['contract_id'])==FALSE ? $_GET['contract_id'] : NULL;
		if(is_null($contract_id))
			$this->error('参数错误！');

		$m_contract = D('Settlement.Contract');
		$contract = $m_contract->find($contract_id);
		
		if(!$contract)
			$this->error('没有找到合同！');
		
		$m_client = D('Settlement.Client');
		$client = $m_client->find($contract['client_id']);
		
		// 查询刊例列表
		$m_card = D('Settlement.RateCard');
		$cards = $m_card -> field('id,rate_card_name')
						 -> where(array('status'=>1,'is_disabled'=>0))
						 -> select();
		
		// 查询合同相关联软件
		$where_query['app.contract_id'] = array('eq',$contract_id);
		
		$m = new Model();
		$result = $m-> table('settlement.ad_contract_apps app')
					-> field('app.id, app.create_tm, app.update_tm, app.app_name, app.app_package, app.app_type, app.weekdays, app.weekends, app.weekday_total, app.weekend_total, app.weekday_original_total, app.weekend_original_total, advert.advertising_name,m.cmark')
					-> join('settlement.ad_advertisings advert ON advert.id=app.advertising_id')
					-> join('settlement.ad_client_mark m ON m.mid=app.mid')
					-> where($where_query)
					-> select();
		
		//echo $m->getLastSql(); 
		$tmp_pak = array();
		foreach($result as $v) {
			array_push($tmp_pak,$v['app_package']);
		}


		$categorys = $this->getCategoryBuPak($tmp_pak);


		foreach($result as $k => $v) {
			if(isset($categorys[$v['app_package']])) {
				$result[$k]['category_id'] = $categorys[$v['app_package']]['category_id'];
				$result[$k]['category_name'] = $categorys[$v['app_package']]['name'];
			} else {
				$result[$k]['category_id'] = '';
				$result[$k]['category_name'] = '';
			}
		
		}




		
		
		
		//2014.12.9 jiwei
		//如果选择了记住输入，则根据刊例查找默认广告位
		if(isset($_COOKIE['cook_rate_card_id']) && empty($_COOKIE['cook_rate_card_id'])==false)
		{
			$m_advert = D('Settlement.Advertising');
			$adverts = $m_advert -> field('id, advertising_name')
								 -> where(array('rate_card_id'=>$_COOKIE['cook_rate_card_id']))
								 -> select();
			
			$this->assign('adverts', $adverts);
		}
		//end

		
		$app_type_key[1] = '应用';
		$app_type_key[2] = '游戏';
		$busi = D("Settlement.ClientMark");
		$mark_list = $busi->field("mid,cmark,softid,package,softname,stype")->where(array('status' => 0))->select();
		
		$this->assign("mark_list",$mark_list);
		$this->assign('app_type_key', $app_type_key);
		$this->assign('client', $client);
		$this->assign('contract', $contract);
		$this->assign('cards', $cards);
		$this->assign('result', $result);
		$this->display();
		
	}

	private function getCategoryBuPak($paks) {
	

		$item = array();
		
		$softs = D('soft')->field("category_id,package")->where(array('package'=>array('in',$paks)))->select();
		
		if(empty($softs)) {
			return $item;
		}
		
		$catgoryIds = array();
		
		foreach($softs as &$v) {
			$v['category_id'] = preg_replace('/[^\d]/','',$v['category_id']);
			array_push($catgoryIds,$v['category_id']);
		}
		



		$category = D('Sj.Category')->field("category_id,name")->where(array('category_id' => array('in',$catgoryIds)))->select();
		
	
		
		$tmp = array();
		foreach($category as $k => $v) {
			$tmp[$v['category_id']] = $v;
		}



		foreach($softs as $k => $v) {
	
			if(isset($tmp[$v['category_id']])) {
				$item[$v['package']] = $tmp[$v['category_id']];
			}
		}

		return $item;
	}



	
	/**
	 * 处理删除合同软件
	 */
	public function delete()
	{
		$app_id = isset($_GET['app_id']) && empty($_GET['app_id'])==FALSE ? $_GET['app_id'] : NULL;
		if(is_null($app_id))
		{
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => "参数错误！"
			));
			exit;
		}
		
		$m_app = D('Settlement.ContractApp');
		$app = $m_app->find($app_id);
		if(!$app)
		{
			echo json_encode(array(
					'result_no' => -2,
					'result_msg' => "没有找到软件！"
			));
			exit;
		}
		
		//
		// 处理删除操作
		//
		$m_schedule = D('Settlement.Schedule');
		$m_contract = D('Settlement.Contract');
		$contract = $m_contract->find($app['contract_id']);
		
		
		
		/*************邮件报警*****************/
		$AlarmNoti_db = D("Settlement.AlarmNoti");
		
		
		$app_so_dates = array();
		foreach( $m_schedule->field(" * ")->where(array('app_id'=>$app_id))->select() as $v) {
			$app_so_dates[] = implode('/', array($v['y'] , $v['m'], $v['d']));
		}
		$result = $AlarmNoti_db->on("del_app", $app_so_dates,array(),
			array_merge($app, array('contract_code' => $contract['contract_code'])));
		
		/*************邮件报警END*****************/
		





		//1、删除软件所有排期
		$m_schedule->where(array('app_id'=>$app['id']))->delete();
		$this->writelog("广告结算：删除合同软件-1、删除软件排期(app_id={$app['id']})", 'ad_schedules',$app['id'],__ACTION__ ,'','del'); //2014.11.6 jiwei
		
		//3、删除合同软件表中的数据
		//2014.12.10 jiwei 调整执行顺序
		$m_app->where(array('id'=>$app['id']))->delete();
		$this->writelog("广告结算：删除合同软件-3、删除软件(app_id={$app['id']})", 'ad_contract_apps',$app['id'],__ACTION__ ,'','del'); //2014.11.6 jiwei
		
		//2、更新合同表中的软件数统计
		$con_data['app_num'] = $contract['app_num'] - 1;
		$con_data['app_discount_total'] = $contract['app_discount_total'] - $app['weekday_total'] - $app['weekend_total'];
		$con_data['app_original_total'] = $contract['app_original_total'] - $app['weekday_original_total'] - $app['weekend_original_total'];
		$con_data['update_tm'] = time();
		
		//2014.12.10 jiwei
		//增加根据包名统计的软件数
		$con_data['package_num'] = $m_app->where(array('contract_id'=>$contract['id']))->count('DISTINCT app_package');
		//end
		
		$log_result = $this->logcheck(array('id'=>$app['contract_id']), 'settlement.ad_contracts', $con_data, $m_contract); //2014.11.6 jiwei
		$m_contract->where(array('id'=>$app['contract_id']))->save($con_data);
		$this->writelog("广告结算：删除合同软件-2、更新合同统计(contract_id={$app['contract_id']})".$log_result, 'ad_contract_apps',$app['contract_id'],__ACTION__ ,'','del'); //2014.11.6 jiwei
		
		
		echo json_encode(array(
			'result_no' => 1,
			'result_msg' => '处理成功！'
		));
	}
	
	/**
	 * 删除软件排期表中的某个排期
	 */
	public function delete_schedule()
	{
		$schedule_id = isset($_GET['schedule_id']) && empty($_GET['schedule_id'])==FALSE ? $_GET['schedule_id'] : NULL;
		if(is_null($schedule_id))
		{
			echo json_encode(array(
					'result_no' => -1,
					'result_msg' => "参数错误！"
			));
			exit;
		}
		
		$m_schedule = D('Settlement.Schedule');
		$schedule = $m_schedule->find($schedule_id);
		if(!$schedule)
		{
			echo json_encode(array(
					'result_no' => -2,
					'result_msg' => "没有找到排期！"
			));
			exit;
		}
		
		//
		// 处理删除排期
		//
		$m_app = D('Settlement.ContractApp');
		$app = $m_app->find($schedule['app_id']);
		
		$m_contract = D('Settlement.Contract');
		$contract = $m_contract->find($app['contract_id']);
		
		//1、处理软件表中的统计
		if($schedule['is_weekend'])
		{
			$data['weekends'] = $app['weekends'] - 1;
			$data['weekend_total'] = $app['weekend_total'] - $schedule['price'];
			$data['weekend_original_total'] = $app['weekend_original_total'] - $schedule['original_price'];
		}
		else
		{
			$data['weekdays'] = $app['weekdays'] - 1;
			$data['weekday_total'] = $app['weekday_total'] - $schedule['price'];
			$data['weekday_original_total'] = $app['weekday_original_total'] - $schedule['original_price'];
		}
		
		$log_result = $this->logcheck(array('id'=>$schedule['app_id']), 'settlement.ad_contract_apps', $data, $m_app); //2014.11.6 jiwei
		$m_app->where(array('id'=>$schedule['app_id']))->data($data)->save();
		$this->writelog("广告结算：删除软件排期-1、更新软件统计(app_id={$app['id']})".$log_result, 'ad_contract_apps',$app['id'],__ACTION__ ,'','del'); //2014.11.6 jiwei
		
		
		//2、删除排期表中的数据
		$m_schedule->where(array('id'=>$schedule['id']))->delete();
		$this->writelog("广告结算：删除软件排期-2、删除排期(schedule_id={$schedule['id']})", 'ad_schedules',$schedule['id'],__ACTION__ ,'','del'); //2014.11.6 jiwei
		
		//3、 更新合同表统计
		$con_data['app_discount_total'] = $contract['app_discount_total'] - $schedule['price'];
		$con_data['app_original_total'] = $contract['app_original_total'] - $schedule['original_price'];
		$con_data['update_tm'] = time();
		$log_result = $this->logcheck(array('id'=>$contract['id']), 'settlement.ad_contracts', $con_data, $m_contract); //2014.11.6 jiwei
		$m_contract->where(array('id'=>$contract['id']))->save($con_data);
		$this->writelog("广告结算：删除软件排期-3、更新合同统计(contract_id={$contract['id']})".$log_result, 'ad_contract_apps',$contract['id'],__ACTION__ ,'','del'); //2014.11.6 jiwei
		
		
		echo json_encode(array(
			'result_no' => 1,
			'result_msg' => '处理成功'
		));
	}
	
	/**
	 * 处理合同软件的编辑操作和显示
	 */
	public function edit()
	{
		// 验证应用ID
		$app_id = isset($_GET['app_id']) && empty($_GET['app_id'])==FALSE ? $_GET['app_id'] : NULL;
		if(is_null($app_id))
		{
			if($this->isAjax())
			{
				echo json_encode(array(
					'result_no' => -1,
					'result_msg' => '参数错误！'
				));
				exit;
			}
			else 
				$this->error('参数错误！');
		}
		
		// 获取应用信息
		$m_app = D('Settlement.ContractApp');
		$app = $m_app->find($app_id);
		if(!$app)
		{
			if($this->isAjax())
			{
				echo json_encode(array(
						'result_no' => -2,
						'result_msg' => '没有找到软件！'
				));
				exit;
			}
			else
				$this->error('没有找到软件！');
		}
		

		if($this->isPost())
		{
			//
			// 验证提交参数
			//
			
			// 合同
			$contract_id = $app['contract_id'];
			$m_contract = D('Settlement.Contract');
			$contract = $m_contract->find($contract_id);
			if(!$contract)
			{
				echo json_encode(array(
						'result_no' => -2,
						'result_msg' => '没有找到合同！'
				));
					
				exit;
			}
			
			// 应用名
			$app_name = $app['app_name'];
			
			// 包名
			$app_package = $app['app_package'];
			
			// 应用类别
			$app_type = $app['app_type'];
			
			// 刊例
			$rate_card_id = isset($_POST['rate_card_id']) && empty($_POST['rate_card_id'])==FALSE ? $_POST['rate_card_id'] : $app['rate_card_id'];
			$m_card = D('Settlement.RateCard');
			if(!$m_card->find($rate_card_id))
			{
				echo json_encode(array(
						'result_no' => -7,
						'result_msg' => '没有找到刊例！'
				));
				
				exit;
			}
			
			// 广告位
			$advertising_id = isset($_POST['advertising_id']) && empty($_POST['advertising_id'])==FALSE ? htmlspecialchars($_POST['advertising_id']) : $app['advertising_id'];
			$m_advert = D('Settlement.Advertising');
			$advert = $m_advert->find($advertising_id);
			if(!$advert)
			{
				echo json_encode(array(
						'result_no' => -9,
						'result_msg' => '没有找到广告位！'
				));
			
				exit;
			}
			
			// 日期选择
			$weekdays = isset($_POST['weekdays']) && is_numeric($_POST['weekdays']) ? $_POST['weekdays'] : 0;
			$weekends = isset($_POST['weekends']) && is_numeric($_POST['weekends']) ? $_POST['weekends'] : 0;
			if(0==$weekdays+$weekends)
			{
				echo json_encode(array(
						'result_no' => -10,
						'result_msg' => '没有选择日期！'
				));
				
				exit;
			}
			
			$weekday_date = $weekend_date = array();
			// 平日天数
			if($weekdays>0)
			{
				$weekday_date = explode('|', $_POST['weekday_date']);
				if(count($weekday_date) != $weekdays)
				{
					echo json_encode(array(
							'result_no' => -11,
							'result_msg' => '平日投放天数错误！'
					));
					
					exit;
				}
				
				// 平日折扣
				$weekday_discount = isset($_POST['weekday_discount']) && ($_POST['weekday_discount']>=0 && $_POST['weekday_discount']<=100) ? $_POST['weekday_discount'] : NULL;
				if(is_null($weekday_discount))
				{
					echo json_encode(array(
							'result_no' => -12,
							'result_msg' => '折扣设置错误！'
					));
				
					exit;
				}
			}
			
			// 周末天数
			if($weekends > 0)
			{
				$weekend_date = explode('|', $_POST['weekend_date']);
				if(count($weekend_date) != $weekends)
				{
					echo json_encode(array(
							'result_no' => -13,
							'result_msg' => '周末投放天数错误！'
					));
			
					exit;
				}
				
				// 平日折扣
				$weekend_discount = isset($_POST['weekend_discount']) && ($_POST['weekend_discount']>=0 && $_POST['weekend_discount']<=100) ? $_POST['weekend_discount'] : NULL;
				if(is_null($weekend_discount))
				{
					echo json_encode(array(
							'result_no' => -14,
							'result_msg' => '周末折扣设置错误！'
					));
						
					exit;
				}
			}
			
			// 关键字
			$app_keyword = isset($_POST['app_keyword']) && empty($_POST['app_keyword'])==FALSE ? htmlspecialchars($_POST['app_keyword']) : NULL;
			if(in_array($advert['channel'],array('SK','SKH','SKD')))
			{
				if(is_null($app_keyword))
				{
					echo json_encode(array(
							'result_no' => -15,
							'result_msg' => '没有输入关键字！'
					));
						
					exit;
				}
			}
			
			//
			// 清除原有排期数据
			//
			$m_schedule = D('Settlement.Schedule');
			
			$app_so_dates = array();
			
			//是否允许修改客户编号等信息
			$no_change_appinfo = false;
			$tmp_time = time();

			foreach( $m_schedule->field(" * ")->where(array('app_id'=>$app_id))->select() as $v) {
				if(strtotime($v['schedule_date']) <= $tmp_time) {
					$no_change_appinfo = true;	
				}
				
				$app_so_dates[] = implode('-',array($v['y'] ,$v['m'], $v['d']));
			}
			
			$mid = intval($_POST['mid']);
			
			if(!$mid) {
				echo json_encode(array(
					'result_no' => -17,
					'result_msg' => '没有输入客户编号'
				));
						
				exit;
			}
			
			
			if($no_change_appinfo && $mid != $app['mid']) {
				echo json_encode(array(
					'result_no' => -18,
					'result_msg' => '不能修改之前的'
				));
						
				exit;
			}
			

			



			
			
			
			
			
			
			$m_app = D('Settlement.ContractApp');
			$old_data = $m_app->find($app_id); //获取原数据
			$m_cmark = D("Settlement.ClientMark");
			
			//$_m = new Model();
			
			$cmark = D("Settlement.ClientMark")->where(array('mid' => $mid))->find();
			
			if(empty($cmark)) {
				echo json_encode(array(
					'result_no' => -19,
					'result_msg' => '客户编号错误'
				));	
				exit;
			}
			


			
			
			//排重检查
			$tmp_dates = array();
			foreach(array_merge($weekend_date , $weekday_date) as $v) {
				array_push($tmp_dates,str_replace('/','-',$v));
			}
			
			$tmp_map = array('app_id'=>array('neq' , $app_id),'app_package' => $cmark['package'], 'advertising_id' => $advertising_id,'schedule_date' => array('in',$tmp_dates));
			
			$tmp_count = $m_schedule->where($tmp_map)->count();

			/*
			var_dump($tmp_dates);
			var_dump($tmp_count);
			echo $m_schedule->getLastSql(); 
			exit;
			*/
			
			if($tmp_count) {
				echo json_encode(array(
					'result_no' => -20,
					'result_msg' => '同一软件不能在同一位置在同一天发布两次'
				));	
				exit;
			}

			
			
			//
			// 更新合同软件表中的数据
			//
			$m_schedule->where(array('app_id'=>$app_id))->delete();
			$this->writelog("广告结算：编辑合同软件-1、清除排期(app_id={$app_id})", 'ad_schedules',$app_id,__ACTION__ ,'','del');



			$data['admin_id'] = $_SESSION['admin']['admin_id'];
			$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			$data['client_id'] = $contract['client_id'];
			$data['agreement_id'] = $contract['agreement_id'];
			$data['contract_id'] = $contract['id'];
			
			$data['app_name'] = $cmark['softname'];
			$data['app_package'] = $cmark['package'];
			$data['app_type'] = intval($_POST['app_type']);
			$data['mid'] = $mid;
			
			/*
			$data['app_name'] = $app_name;
			$data['app_package'] = $app_package;
			$data['app_type'] = $app_type;
			*/
			
			$data['keyword'] = $app_keyword ? $app_keyword : '';
			$data['is_keyword'] = $app_keyword ? 1 : 0;
			$data['rate_card_id'] = $rate_card_id;
			$data['advertising_id'] = $advertising_id;
			
			// 根据软件类型设置平日和周末的刊例价格
			if($data['app_type'] == 2)
			{
				$weekday_price = $advert['game_weekday_price'];
				$weekend_price = $advert['game_weekend_price'];
			}
			else 
			{
				$weekday_price = $advert['app_weekday_price'];
				$weekend_price = $advert['app_weekend_price'];
			}
			
			// 处理计算平日价格
			$data['weekdays'] = $weekdays;
			if($data['weekdays'] > 0)
			{
				$data['weekday_discount'] = $weekday_discount;
		  		$data['weekday_price'] = $weekday_price * $data['weekday_discount'] * 0.01;
		  		$data['weekday_total'] = $data['weekday_price'] * $data['weekdays'];
		  		$data['weekday_original_price'] = $weekday_price;
		 		$data['weekday_original_total'] = $data['weekday_original_price'] * $data['weekdays'];
			}
			else 
			{
				$data['weekday_discount'] = 0;
				$data['weekday_price'] = 0;
				$data['weekday_total'] = 0;
				$data['weekday_original_price'] = 0;
				$data['weekday_original_total'] = 0;
			}
			
			// 处理计算周末价格
	  		$data['weekends'] = $weekends;
	  		if( $data['weekends'] > 0 )
	  		{
	  			$data['weekend_discount'] = $weekend_discount;
	  			$data['weekend_price'] = $weekend_price * $data['weekend_discount'] * 0.01;
	  			$data['weekend_total'] = $data['weekend_price'] * $data['weekends'];
	  			$data['weekend_original_price'] = $weekend_price;
	  			$data['weekend_original_total'] = $data['weekend_original_price'] * $data['weekends'];
	  		}
	  		else
	  		{
	  			$data['weekend_discount'] = 0;
	  			$data['weekend_price'] = 0;
	  			$data['weekend_total'] = 0;
	  			$data['weekend_original_price'] = 0;
	  			$data['weekend_original_total'] = 0;
	  		}
	  		
			$data['update_tm'] = time();
			
	
			//var_dump($data);
			//exit;
			
			// 更新记录
			$log_result = $this->logcheck(array('id'=>$app_id), 'settlement.ad_contract_apps', $data, $m_app);
			$m_app->where(array('id'=>$app_id))->data($data)->save();
			
			
			/*************邮件报警*****************/
			$AlarmNoti_db = D("Settlement.AlarmNoti");
			
			$tmp_wee_date = array();
            foreach(array_merge($weekend_date , $weekday_date) as $v) {
                array_push($tmp_wee_date,str_replace('/','-',$v));
            }	
			//$tmp_wee_date = array_merge($weekend_date , $weekday_date);
			sort($tmp_wee_date);
			sort($app_so_dates);
			$result = $AlarmNoti_db->on("edit_app", 
				array_merge($tmp_wee_date, $app_so_dates), 
				array_merge($data, array('wee_date' => implode(',',$tmp_wee_date))), 
				array_merge($old_data,array('contract_code' => $contract['contract_code'] ,'app_name' => $old_data['app_name'], 'wee_date' => implode(',',$app_so_dates))));
			
			/*************邮件报警END*****************/
			
			$this->writelog("广告结算：编辑合同软件-2、更新软件统计(app_id={$app_id})".$log_result, 'ad_schedules',$app_id,__ACTION__ ,'','edit');
			

			
			// 更新合同表统计
			$con_data['app_discount_total'] = ($contract['app_discount_total'] - $old_data['weekday_total'] - $old_data['weekend_total']) + $data['weekday_total'] + $data['weekend_total'];
			$con_data['app_original_total'] = ($contract['app_original_total'] - $old_data['weekday_original_total'] - $old_data['weekend_original_total']) + $data['weekday_original_total'] + $data['weekend_original_total'];
			$con_data['update_tm'] = time();
			
			$log_result = $this->logcheck(array('id'=>$contract['id']), 'settlement.ad_contracts', $con_data, $m_contract);
			$m_contract->where(array('id'=>$contract['id']))->save($con_data);
			$this->writelog("广告结算：编辑合同软件-3、更新合同统计(contract_id={$contract['id']})".$log_result, 'ad_schedules',$app_id,__ACTION__ ,'','edit');
			
			
			// 录入广告排期数据
			foreach($weekday_date as $date)
			{
				$sch_data = array();
				$t = strtotime($date);
				
				$sch_data['admin_id'] = $_SESSION['admin']['admin_id'];
	  			$sch_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
	  			$sch_data['client_id'] = $contract['client_id'];
	  			$sch_data['agreement_id'] = $contract['agreement_id'];
	  			$sch_data['contract_id'] = $contract['id'];
	  			$sch_data['rate_card_id'] = $rate_card_id;
				$sch_data['advertising_id'] = $advert['id'];
				$sch_data['channel'] = $advert['channel'];
				$sch_data['channel_node_id'] = $advert['channel_node_id'];
				$sch_data['app_id'] = $app_id;
				$sch_data['app_name'] = $app_name;
				$sch_data['app_package'] = $app_package;
				$sch_data['app_type'] = $app_type;
				$sch_data['keyword'] = $app_keyword ? $app_keyword : '';
				$sch_data['is_keyword'] = $app_keyword ? 1 : 0;
				$sch_data['schedule_date'] = $date;
				$sch_data['price'] = $weekday_price * $weekday_discount * 0.01;
				$sch_data['original_price'] = $weekday_price;
				$sch_data['y'] = date('Y', $t);
				$sch_data['m'] = date('m', $t);
				$sch_data['d'] = date('d', $t);
				$sch_data['is_weekend'] = 0;
				$sch_data['is_scheduled'] = 0;
				$sch_data['update_tm'] = time();
				$sch_data['create_tm'] = time();
				
				$m_schedule->add($sch_data);
			}
			
			foreach($weekend_date as $date)
			{
				$sch_data = array();
				$t = strtotime($date);
					
				$sch_data['admin_id'] = $_SESSION['admin']['admin_id'];
				$sch_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
				$sch_data['client_id'] = $contract['client_id'];
				$sch_data['agreement_id'] = $contract['agreement_id'];
				$sch_data['contract_id'] = $contract['id'];
				$sch_data['rate_card_id'] = $rate_card_id;
				$sch_data['advertising_id'] = $advert['id'];
				$sch_data['channel'] = $advert['channel'];
				$sch_data['channel_node_id'] = $advert['channel_node_id'];
				$sch_data['app_id'] = $app_id;
				$sch_data['app_name'] = $app_name;
				$sch_data['app_package'] = $app_package;
				$sch_data['app_type'] = $app_type;
				$sch_data['keyword'] = $app_keyword ? $app_keyword : '';
				$sch_data['is_keyword'] = $app_keyword ? 1 : 0;
				$sch_data['schedule_date'] = $date;
				$sch_data['price'] = $weekend_price * $weekend_discount * 0.01;
				$sch_data['original_price'] = $weekend_price;
				$sch_data['y'] = date('Y', $t);
				$sch_data['m'] = date('m', $t);
				$sch_data['d'] = date('d', $t);
				$sch_data['is_weekend'] = 1;
				$sch_data['is_scheduled'] = 0;
				$sch_data['update_tm'] = time();
				$sch_data['create_tm'] = time();
					
				$m_schedule->add($sch_data);
			}
			
			$this->writelog("广告结算：编辑合同软件-4、录入新排期(app_id={$app_id})", 'ad_schedules',$app_id,__ACTION__ ,'','add');


			echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '调用成功'
			));
		}
		else 
		{
			$m_schedule = D('Settlement.Schedule');
			$schedules = $m_schedule->where(array('app_id'=>$app_id))->select();
			
			// 处理排期字串，用于在前端给js变量赋值
			$weekday_str = $weekend_str = $date_str = '';
			foreach($schedules as $schedule)
			{
				$date_str .= ",{$schedule['schedule_date']}";
				if($schedule['is_weekend'])
					$weekend_str .= ",'{$schedule['schedule_date']}'";
				else 
					$weekday_str .= ",'{$schedule['schedule_date']}'";
			}
			
			if($date_str)
			{
				$date_str = substr($date_str, 1);
				$date_str = str_replace('-', '/', $date_str);
			}
			if($weekday_str)
				$weekday_str = substr($weekday_str,1);
			if($weekend_str)
				$weekend_str = substr($weekend_str,1);
			
			
			// 查询刊例列表
			$m_card = D('Settlement.RateCard');
			$cards = $m_card -> field('id,rate_card_name')
							 -> where(array('status'=>1,'is_disabled'=>0))
							 -> select();
			
			// 查询广告位
			if($m_card->where(array('id'=>$app['rate_card_id'],'status'=>1,'is_disabled'=>0))->count())
			{
				$m_advert = D('Settlement.Advertising');
				$adverts = $m_advert->field('id,advertising_name')->where(array('rate_card_id'=>$app['rate_card_id']))->select();
				$this->assign('adverts', $adverts);
			}
			
			//2014.12.10 jiwei
			//处理平日、周末的广告位价格赋值
			if($adverts)
			{
				$advert = $m_advert->where(array('id'=>$app['advertising_id']))->find();
				if($app['app_type']==1)
				{
					$this->assign('advert_weekday_price', $advert['app_weekday_price']);
					$this->assign('advert_weekend_price', $advert['app_weekend_price']);
				}
				elseif($app['app_type']==2)
				{
					$this->assign('advert_weekday_price', $advert['game_weekday_price']);
					$this->assign('advert_weekend_price', $advert['game_weekend_price']);
				}
			}
			//end
			

			//print_r($app);exit;

			$busi = D("Settlement.ClientMark");
			$mark_list = $busi->field("mid,cmark,softid,package,softname")->where(array('status' => 0))->select();
			$this->assign('mark_list', $mark_list);
			$this->assign('date_str', $date_str);
			$this->assign('weekday_str', $weekday_str);
			$this->assign('weekend_str', $weekend_str);
			$this->assign('app', $app);
			$this->assign('schedules', $schedules);
			$this->assign('cards', $cards);
			$this->display();
		}
	}

	
	/**
	 * 处理合同软件的添加和显示
	 */
	public function add()
	{
		//
		// 验证提交参数
		//
		
		// 合同
		$contract_id = isset($_GET['contract_id']) && empty($_GET['contract_id'])==FALSE ? $_GET['contract_id'] : NULL;
		if(is_null($contract_id))
		{
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => '参数错误！'
			));
			
			exit;
		}
		
		$m_contract = D('Settlement.Contract');
		$contract = $m_contract->find($contract_id);
		if(!$contract)
		{
			echo json_encode(array(
					'result_no' => -2,
					'result_msg' => '没有找到合同！'
			));
				
			exit;
		}
		
		// 应用名
		$app_name = isset($_POST['app_name']) && empty($_POST['app_name'])==FALSE ? htmlspecialchars($_POST['app_name']) : NULL;
		if(is_null($app_name))
		{
			echo json_encode(array(
					'result_no' => -3,
					'result_msg' => '应用名称不能为空！'
			));
				
			exit;
		}
		
		// 包名
		$app_package = isset($_POST['app_package']) && empty($_POST['app_package'])==FALSE ? htmlspecialchars($_POST['app_package']) : NULL;
		if(is_null($app_package))
		{
			echo json_encode(array(
					'result_no' => -4,
					'result_msg' => '应用包名不能为空！'
			));
		
			exit;
		}
		
		// 应用类别
		$app_type = isset($_POST['app_type']) && in_array($_POST['app_type'], array(1,2)) ? $_POST['app_type'] : 0;
		if(0==$app_type)
		{
			echo json_encode(array(
					'result_no' => -5,
					'result_msg' => '软件类别错误！'
			));
			
			exit;
		}
		
		// 刊例
		$rate_card_id = isset($_POST['rate_card_id']) && empty($_POST['rate_card_id'])==FALSE ? htmlspecialchars($_POST['rate_card_id']) : NULL;
		if(is_null($rate_card_id))
		{
			echo json_encode(array(
					'result_no' => -6,
					'result_msg' => '刊例ID错误！'
			));
				
			exit;
		}
		$m_card = D('Settlement.RateCard');
		if(!$m_card->find($rate_card_id))
		{
			echo json_encode(array(
					'result_no' => -7,
					'result_msg' => '没有找到刊例！'
			));
			
			exit;
		}
		
		// 广告位
		$advertising_id = isset($_POST['advertising_id']) && empty($_POST['advertising_id'])==FALSE ? htmlspecialchars($_POST['advertising_id']) : NULL;
		if(is_null($advertising_id))
		{
			echo json_encode(array(
					'result_no' => -8,
					'result_msg' => '广告位ID错误！'
			));
		
			exit;
		}
		$m_advert = D('Settlement.Advertising');
		$advert = $m_advert->find($advertising_id);
		if(!$advert)
		{
			echo json_encode(array(
					'result_no' => -9,
					'result_msg' => '没有找到广告位！'
			));
		
			exit;
		}
		
		// 日期选择
		$weekdays = isset($_POST['weekdays']) && is_numeric($_POST['weekdays']) ? $_POST['weekdays'] : 0;
		$weekends = isset($_POST['weekends']) && is_numeric($_POST['weekends']) ? $_POST['weekends'] : 0;
		if(0==$weekdays+$weekends)
		{
			echo json_encode(array(
					'result_no' => -10,
					'result_msg' => '没有选择日期！'
			));
			
			exit;
		}
	

		$weekend_date = $weekday_date = array();	
		// 平日天数
		if($weekdays>0)
		{
			$weekday_date = explode('|', $_POST['weekday_date']);
			if(count($weekday_date) != $weekdays)
			{
				echo json_encode(array(
						'result_no' => -11,
						'result_msg' => '平日投放天数错误！'
				));
				
				exit;
			}
			
			// 平日折扣
			$weekday_discount = isset($_POST['weekday_discount']) && ($_POST['weekday_discount']>=0 && $_POST['weekday_discount']<=100) ? $_POST['weekday_discount'] : NULL;
			if(is_null($weekday_discount))
			{
				echo json_encode(array(
						'result_no' => -12,
						'result_msg' => '折扣设置错误！'
				));
			
				exit;
			}
		}
		
		// 周末天数
		if($weekends > 0)
		{
			$weekend_date = explode('|', $_POST['weekend_date']);
			if(count($weekend_date) != $weekends)
			{
				echo json_encode(array(
						'result_no' => -13,
						'result_msg' => '周末投放天数错误！'
				));
		
				exit;
			}
			
			// 平日折扣
			$weekend_discount = isset($_POST['weekend_discount']) && ($_POST['weekend_discount']>=0 && $_POST['weekend_discount']<=100) ? $_POST['weekend_discount'] : NULL;
			if(is_null($weekend_discount))
			{
				echo json_encode(array(
						'result_no' => -14,
						'result_msg' => '周末折扣设置错误！'
				));
					
				exit;
			}
		}
		
		// 关键字
		$app_keyword = isset($_POST['app_keyword']) && empty($_POST['app_keyword'])==FALSE ? htmlspecialchars($_POST['app_keyword']) : NULL;
		if(in_array($advert['channel'],array('SK','SKH','SKD')))
		{
			if(is_null($app_keyword))
			{
				echo json_encode(array(
						'result_no' => -15,
						'result_msg' => '没有输入关键字！'
				));
					
				exit;
			}
		}
		
		// 包名是否已经存在
		$m_app = D('Settlement.ContractApp');
		
		$app_exist = $m_app->where(array('contract_id'=>$contract_id, 'app_package'=>$app_package))->count();
		/*
		if($app_exist)
		{
			echo json_encode(array(
					'result_no' => -16,
					'result_msg' => '软件已经存在，不能重复添加！'
			));
				
			exit;
		}
		*/
		
		




		//
		// 处理数据入库
		//
		$m_schedule = D('Settlement.Schedule');
		
		
		//排重检查
		$tmp_dates = array();
		foreach(array_merge( $weekend_date, $weekday_date)  as $v) {
			array_push($tmp_dates,str_replace('/','-',$v));
		}
		
		$tmp_map = array('app_package' => $app_package,'advertising_id' => $advertising_id,'schedule_date' => array('in',$tmp_dates));
		
		$tmp_count = $m_schedule->where($tmp_map)->select();
		/*
		var_dump($weekend_date);
		var_dump($weekday_date);	
		var_dump($tmp_dates);
		var_dump($tmp_count);
		echo $m_schedule->getLastSql(); 
		exit;
		*/
		
		if($tmp_count) {
			echo json_encode(array(
				'result_no' => -20,
				'result_msg' => '同一软件不能在同一位置在同一天发布两次'
			));	
			exit;
		}
		
		
		
		
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$data['mid'] = intval($_POST['mid']);
		$data['admin_name'] = $_SESSION['admin']['admin_user_name'];
		$data['client_id'] = $contract['client_id'];
		$data['agreement_id'] = $contract['agreement_id'];
		$data['contract_id'] = $contract['id'];
		$data['app_name'] = $app_name;
		$data['app_package'] = $app_package;
		$data['app_type'] = $app_type;
		$data['keyword'] = $app_keyword ? $app_keyword : '';
		$data['is_keyword'] = $app_keyword ? 1 : 0;
		$data['rate_card_id'] = $rate_card_id;
		$data['advertising_id'] = $advertising_id;
		
		// 根据软件类型设置平日和周末的刊例价格
		if($data['app_type'] == 2)
		{
			$weekday_price = $advert['game_weekday_price'];
			$weekend_price = $advert['game_weekend_price'];
		}
		else 
		{
			$weekday_price = $advert['app_weekday_price'];
			$weekend_price = $advert['app_weekend_price'];
		}
		
		// 处理计算平日价格
		$data['weekdays'] = $weekdays;
		if($data['weekdays'] > 0)
		{
			$data['weekday_discount'] = $weekday_discount;
	  		$data['weekday_price'] = $weekday_price * $data['weekday_discount'] * 0.01;
	  		$data['weekday_total'] = $data['weekday_price'] * $data['weekdays'];
	  		$data['weekday_original_price'] = $weekday_price;
	 		$data['weekday_original_total'] = $data['weekday_original_price'] * $data['weekdays'];
		}
		else 
		{
			$data['weekday_discount'] = 0;
			$data['weekday_price'] = 0;
			$data['weekday_total'] = 0;
			$data['weekday_original_price'] = 0;
			$data['weekday_original_total'] = 0;
		}
		
		// 处理计算周末价格
  		$data['weekends'] = $weekends;
  		if( $data['weekends'] > 0 )
  		{
  			$data['weekend_discount'] = $weekend_discount;
  			$data['weekend_price'] = $weekend_price * $data['weekend_discount'] * 0.01;
  			$data['weekend_total'] = $data['weekend_price'] * $data['weekends'];
  			$data['weekend_original_price'] = $weekend_price;
  			$data['weekend_original_total'] = $data['weekend_original_price'] * $data['weekends'];
  		}
  		else
  		{
  			$data['weekend_discount'] = 0;
  			$data['weekend_price'] = 0;
  			$data['weekend_total'] = 0;
  			$data['weekend_original_price'] = 0;
  			$data['weekend_original_total'] = 0;
  		}
  		
		$data['update_tm'] = time();
		$data['create_tm'] = time();
		

		// 添加记录		
		$app_id = $m_app->add($data);
		
		/*************邮件报警*****************/
		$AlarmNoti_db = D("Settlement.AlarmNoti");
		

		$result = $AlarmNoti_db->on("add_app", array_merge($weekend_date, $weekday_date), 
			array_merge($data, array('contract_code' => $contract['contract_code'] )));
		
		/*************邮件报警END*****************/
		
		
		
		$this->writelog("广告结算：新增合同软件-1、新增软件(app_id={$app_id})", 'ad_schedules',$app_id,__ACTION__ ,'','add');
		
		// 更新合同表统计
		$con_data['app_num'] = $contract['app_num']+1;
		$con_data['app_discount_total'] = $contract['app_discount_total']+$data['weekday_total']+$data['weekend_total'];
		$con_data['app_original_total'] = $contract['app_original_total']+$data['weekday_original_total']+$data['weekend_original_total'];
		$con_data['update_tm'] = time();
		
		//2014.12.10 jiwei
		//增加根据包名统计的软件数
		$con_data['package_num'] = $m_app->where(array('contract_id'=>$contract['id']))->count('DISTINCT app_package');
		//end
		
		$log_result = $this->logcheck(array('id'=>$contract['id']), 'settlement.ad_contracts', $con_data, $m_contract);
		$m_contract->where(array('id'=>$contract['id']))->save($con_data);
		$this->writelog("广告结算：新增合同软件-2、更新合同统计(contract_id={$contract['id']})".$log_result, 'ad_contract_apps',$contract['id'],__ACTION__ ,'','add');
		
		// 录入广告排期数据
		foreach($weekday_date as $date)
		{
			$sch_data = array();
			$t = strtotime($date);
			
			$sch_data['admin_id'] = $_SESSION['admin']['admin_id'];
  			$sch_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
  			$sch_data['client_id'] = $contract['client_id'];
  			$sch_data['agreement_id'] = $contract['agreement_id'];
  			$sch_data['contract_id'] = $contract['id'];
  			$sch_data['rate_card_id'] = $rate_card_id;
			$sch_data['advertising_id'] = $advert['id'];
			$sch_data['channel'] = $advert['channel'];
			$sch_data['channel_node_id'] = $advert['channel_node_id'];
			$sch_data['app_id'] = $app_id;
			$sch_data['app_name'] = $app_name;
			$sch_data['app_package'] = $app_package;
			$sch_data['app_type'] = $app_type;
			$sch_data['keyword'] = $app_keyword ? $app_keyword : '';
			$sch_data['is_keyword'] = $app_keyword ? 1 : 0;
			$sch_data['schedule_date'] = $date;
			$sch_data['price'] = $weekday_price * $weekday_discount * 0.01;
			$sch_data['original_price'] = $weekday_price;
			$sch_data['y'] = date('Y', $t);
			$sch_data['m'] = date('m', $t);
			$sch_data['d'] = date('d', $t);
			$sch_data['is_weekend'] = 0;
			$sch_data['is_scheduled'] = 0;
			$sch_data['update_tm'] = time();
			$sch_data['create_tm'] = time();
			
			$m_schedule->add($sch_data);
		}
		
		foreach($weekend_date as $date)
		{
			$sch_data = array();
			$t = strtotime($date);
				
			$sch_data['admin_id'] = $_SESSION['admin']['admin_id'];
			$sch_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
			$sch_data['client_id'] = $contract['client_id'];
			$sch_data['agreement_id'] = $contract['agreement_id'];
			$sch_data['contract_id'] = $contract['id'];
			$sch_data['rate_card_id'] = $rate_card_id;
			$sch_data['advertising_id'] = $advert['id'];
			$sch_data['channel'] = $advert['channel'];
			$sch_data['channel_node_id'] = $advert['channel_node_id'];
			$sch_data['app_id'] = $app_id;
			$sch_data['app_name'] = $app_name;
			$sch_data['app_package'] = $app_package;
			$sch_data['app_type'] = $app_type;
			$sch_data['keyword'] = $app_keyword ? $app_keyword : '';
			$sch_data['is_keyword'] = $app_keyword ? 1 : 0;
			$sch_data['schedule_date'] = $date;
			$sch_data['price'] = $weekend_price * $weekend_discount * 0.01;
			$sch_data['original_price'] = $weekend_price;
			$sch_data['y'] = date('Y', $t);
			$sch_data['m'] = date('m', $t);
			$sch_data['d'] = date('d', $t);
			$sch_data['is_weekend'] = 1;
			$sch_data['is_scheduled'] = 0;
			$sch_data['update_tm'] = time();
			$sch_data['create_tm'] = time();
				
			$m_schedule->add($sch_data);
		}
		$this->writelog("广告结算：新增合同软件-3、增加排期(app_id={$app_id})", 'ad_schedules',$app_id,__ACTION__ ,'','add');
		
		//2014.12.9 jiwei
		//根据记住本次输入复选框来记录相关数据到cookie
		$remember = isset($_POST['remember']) && $_POST['remember']==1 ? 1 : 0;
		if($remember)
		{
			$expire = time()+3600;
			setcookie('cook_app_name_'.$contract_id, $app_name, $expire, '/');
			setcookie('cook_app_package_'.$contract_id, $app_package, $expire, '/');
			setcookie('cook_app_type_'.$contract_id, $app_type, $expire, '/');
			setcookie('cook_rate_card_id_'.$contract_id, $rate_card_id, $expire, '/');
			$discount = $weekday_discount ? $weekday_discount : $weekend_discount;
			setcookie('cook_discount_'.$contract_id, $discount, $expire, '/');
			setcookie('cook_remember_'.$contract_id, 1, $expire, '/');
		}
		else 
		{
			$expire = time()-3600;
			setcookie('cook_app_name_'.$contract_id, '', $expire, '/');
			setcookie('cook_app_package_'.$contract_id, '', $expire, '/');
			setcookie('cook_app_type_'.$contract_id, '', $expire, '/');
			setcookie('cook_rate_card_id_'.$contract_id, '', $expire, '/');
			setcookie('cook_discount_'.$contract_id, '', $expire, '/');
			setcookie('cook_remember_'.$contract_id, '', $expire, '/');
		}
		//end
		
		echo json_encode(array(
			'result_no' => 1,
			'result_msg' => '调用成功'
		));
	}
	
	/**
	 * 根据刊例id来获取相应的广告位
	 */
	public function ajax_get_advert()
	{
		$ratecard_id = isset($_GET['ratecard_id']) && empty($_GET['ratecard_id'])==FALSE ? $_GET['ratecard_id'] : NULL;
		if(is_null($ratecard_id))
		{
			echo json_encode(array());
			exit;
		}

		$m_advert = D('Settlement.Advertising');
		$result = $m_advert	-> field("id,advertising_code,advertising_name,app_weekday_price,app_weekend_price,game_weekday_price,game_weekend_price,channel")
							-> where(array('rate_card_id'=>$ratecard_id))
							-> select();
		
		if($result)
			echo json_encode($result);
		else 
			echo json_encode(array());
	}
	
	/**
	 * 显示排期表
	 */
	public function schedule()
	{
		//2014.12.10 jiwei
		//增加一段临时程序
		//用于更新生产环境中合同表中的的新增字段数据导入
		//执行完成后应删除此部分代码
		if($_GET['l9d2avzl']==='kjf832nv')
		{
			$_m_con = D('Settlement.Contract');
			$_m_app = D('Settlement.ContractApp');
			$_con = $_m_con->field('id')->select();
			foreach($_con as $row)
			{
				$_num = $_m_app->where(array('contract_id'=>$row['id']))->count('DISTINCT app_package');
				$_m_con->where(array('id'=>$row['id']))->data(array('package_num'=>$_num))->save();
			}
			exit;
		}
		//end
		
		$week = isset($_GET['week']) && in_array($_GET['week'], array('day','end')) ? $_GET['week'] : NULL;
		$id = isset($_GET['id']) && empty($_GET['id'])==FALSE ? $_GET['id'] : NULL;
		$start = isset($_GET['start']) && strtotime($_GET['start']) > 0 ? $_GET['start'] : NULL; //开始日期
		$end = isset($_GET['end']) && strtotime($_GET['end']) > 0 ? $_GET['end'] : NULL; //结束日期
		
		if(is_null($week) || is_null($id))
			$this->error("参数错误！");
		
		if($week=='end')
			$is_weekend = 1;
		else 
			$is_weekend = 0;
		
		$m_app = D('Settlement.ContractApp');
		$m_schedule = D('Settlement.Schedule');
		
		$where_query['app_id'] = array('eq', $id);
		$where_query['is_weekend'] = array('eq', $is_weekend);
		
		if(is_null($start)==FALSE && is_null($end)==FALSE)
			$where_query['schedule_date'] = array(array('egt',$start),array('elt',$end));
		
		$app = $m_app->find($id);
		$result = $m_schedule->where($where_query)->select();
		
		$this->assign('app', $app);
		$this->assign('result', $result);
		$this->display();
	}
	
	/**
	 * 处理下载
	 * @param unknown $reuslt
	 */
	private function download($result, $filename, $flag=false, $stat=null)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
	
		$out = "合同软件ID,月份,合同编号,客户名称,软件名,包名,广告位,平日,周末/月初,折扣后总价,刊例总价\r\n";
	
		foreach($result as $item)
		{
			$total = $item['weekday_total']+$item['weekend_total'];
			$original_total = $item['weekday_original_total']+$item['weekend_original_total'];
			
			if($flag)
				$out.="{$item['id']},{$item['month']},{$item['contract_code']},{$item['client_name']},{$item['app_name']},{$item['app_package']},{$item['advertising_name']},{$stat[$item['contract_id']][$item['id']]['weekdays']},{$stat[$item['contract_id']][$item['id']]['weekends']},{$stat[$item['contract_id']][$item['id']]['price']},{$stat[$item['contract_id']][$item['id']]['original_price']}\r\n";
			else
				$out.="{$item['month']},{$item['contract_code']},{$item['client_name']},{$item['app_name']},{$item['app_package']},{$item['advertising_name']},{$item['weekdays']},{$item['weekends']},{$total},{$original_total}\r\n";
		}
	
		echo iconv('utf-8', 'gb2312', $out);
		exit;
	}
	
	/**
	 * 处理导出校对表
	 * @param unknown $result
	 * @param unknown $filename
	 */
	private function proofread($result, $filename)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		
		$m_schedule = D('Settlement.Schedule');
		$adv_tables = C('_proofread_.advert_tables'); //引入校对表
		$app_type = array(1=>'应用', 2=>'游戏');

		$out = "广告位,投放日期,未排期合同软件包名,未排期合同软件名,合同软件类型,刊例价,折扣价,协议编号,合同编号,客户名称\r\n";
		
		foreach($result as $item)
		{
			//处理排期校对
			$schedule = $m_schedule->where(array('app_id'=>$item['id']))->order('schedule_date ASC')->select();
			
			foreach($schedule as $row)
			{
				$exist = false;
				$where_query = array();
					
				// 如果频道标记不在广告位配置数组
				if( !key_exists($row['channel'], $adv_tables) )
					continue;
				
				$a = $adv_tables[$row['channel']]['app'];	
				$m = D($a['model']);
				
				// 如果广告频道为搜索或者推荐
				if( $row['channel']=='SK' || $row['channel']=='SKH' || $row['channel']=='SKD' || $row['channel']=='PR' )
				{
					if($row['is_keyword'])
						$where_query[$a['find']] = array('eq', $row['keyword']);
					else 
						$where_query[$a['find']] = array('eq', $row['app_package']);
				}
				else 
				{
					$where_query[$a['node_field']] = array('eq', $row['channel_node_id']);
					$where_query[$a['find']] = array('eq', $row['app_package']);
				}
				
				$t = strtotime($row['schedule_date']);
				$where_query[$a['date_field'][0]] = array('elt', $t);
				$where_query[$a['date_field'][1]] = array('egt', $t);

				$exist = $m->where($where_query)->count();

				if(!$exist)
					$out.="{$item['advertising_name']},{$row['schedule_date']},{$row['app_package']},{$row['app_name']},{$app_type[$row['app_type']]},{$row['original_price']},{$row['price']},{$item['agreement_code']},{$item['contract_code']},{$item['client_name']}\r\n";
			}
		}

		echo iconv('utf-8', 'gb2312', $out);
		exit;
	}
	
	/**
	 * 显示软件统计、排期信息等
	 */
	public function show()
	{
		// 按合同或者按频道显示
		$type = isset($_GET['type']) && in_array($_GET['type'],array('contract','channel')) ? $_GET['type'] : NULL;
		// 合同ID或者广告位+合同月份的
		$id = isset($_GET['id']) && empty($_GET['id'])==FALSE ? $_GET['id'] : NULL;
		// 排期类型 1-已排期 2-未过期未排 3-已过期未排
		$filter = isset($_GET['filter']) && in_array($_GET['filter'],array(1,2,3)) ? $_GET['filter'] : NULL;
		
		if( is_null($type) || is_null($id) )
			$this->error("参数错误！");

		//2014.12.15 jiwei
		$start = isset($_GET['start']) && strtotime($_GET['start']) > 0 ? $_GET['start'] : NULL; //开始日期
		$end = isset($_GET['end']) && strtotime($_GET['end']) > 0 ? $_GET['end'] : NULL; //结束日期
		$flag = false; //用于标记用户是否选择了投放日期范围
		$map = array();
		
		if(is_null($start)==FALSE && is_null($end)==FALSE)
		{
			$m_schedule = D('Settlement.Schedule');
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
			$map['app.id'] = array('in', $schedule_app_ids);
		}
		//end

		$_m = new Model();		
		if($type == 'contract')
		{
			$map['app.contract_id'] = $id;
			
			$apps = $_m	-> table('settlement.ad_contract_apps app')
						-> field('app.id, app.app_name, app.app_package, card.rate_card_name, advert.advertising_name, app.contract_id, contract.contract_code')
						-> join('settlement.ad_rate_cards card ON card.id=app.rate_card_id')
						-> join('settlement.ad_advertisings advert ON advert.id=app.advertising_id')
						-> join('settlement.ad_contracts contract ON contract.id=app.contract_id')
						-> where($map)
						-> select();
		}
		elseif($type == 'channel')
		{
			list($advert_id, $con_month) = explode('_', $id);
			
			if(! ($advert_id && $con_month))
				$this->error('解析参数错误！');
			
			$map['app.advertising_id'] = $advert_id;
			$map['contract.month'] = $con_month;
			
			$apps = $_m	-> table('settlement.ad_contract_apps app')
						-> field('app.id, app.app_name, app.app_package, card.rate_card_name, advert.advertising_name, app.contract_id, contract.contract_code')
						-> join('settlement.ad_contracts contract ON contract.id=app.contract_id')
						-> join('settlement.ad_rate_cards card ON card.id=app.rate_card_id')
						-> join('settlement.ad_advertisings advert ON advert.id=app.advertising_id')
						-> where($map)
						-> select();
		}
		else 
			$this->error("参数错误！");
		
		
		$m_sch = D('Settlement.Schedule');
		$adv_tables = C('_proofread_.advert_tables');
		$_result = $app_ids = array();
		
		foreach($apps as $app)
		{
			$_result[$app['id']] = $app;
			$sch_map['app_id'] = $app['id'];
			if($flag)
				$sch_map['schedule_date'] = array(array('egt',$start),array('elt',$end));
			
			$schedules = $m_sch->where($sch_map)->order('schedule_date ASC')->select();
			
			foreach($schedules as $sch)
			{
				$_result[$app['id']]['schedule_date'][] = $sch['schedule_date'];
				
				// 如果频道标记不在广告位配置数组
				if( !key_exists($sch['channel'], $adv_tables) )
					continue;
					
				// 根据频道实例化相应model
				$a = $adv_tables[$sch['channel']]['app'];
				$m = D($a['model']);
					
				$q = array(); //查询条件数组
					
				// 如果广告频道为搜索或者推荐
				//if( $sch['channel']=='SK' || $sch['channel']=='SKH' || $sch['channel']=='SKD' || $sch['channel']=='PR' )
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

                                        /*
					if($sch['is_keyword'])
						$q[$a['find']] = array('eq', $sch['keyword']);
					else
						$q[$a['find']] = array('eq', $sch['app_package']);
                                        */
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
					if(!isset($app_ids[1][$app['id']]))
						$app_ids[1][$app['id']] = $app['id'];
					
					$_result[$app['id']]['schedule_filter'][1][] = $sch['schedule_date'];
				}
				else
				{
					$ts = strtotime($sch['schedule_date']);
					$current_ts = strtotime(date('Y-m-d'));
				
					if($ts > $current_ts)
					{
						if(!isset($app_ids[2][$app['id']]))
							$app_ids[2][$app['id']] = $app['id'];
						
						$_result[$app['id']]['schedule_filter'][2][] = $sch['schedule_date'];
					}
					else
					{
						if(!isset($app_ids[3][$app['id']]))
							$app_ids[3][$app['id']] = $app['id'];
						
						$_result[$app['id']]['schedule_filter'][3][] = $sch['schedule_date'];
					}
				}
			}
		}

		if(is_null($filter))
		{
			$result = $_result;
		}
		else 
		{
			foreach($app_ids[$filter] as $app_id)
				$result[] = $_result[$app_id];
		}

		$this->assign('filter', $filter);
		$this->assign('result', $result);
		$this->display();
	}
	
	
	public function show_pack()
	{

		
		$_m = new Model();

		$id = intval($_GET['id']);
		$weekend = intval($_GET['weekend']);
		$map['app.id'] = $id;
		$apps = $_m	-> table('settlement.ad_contract_apps app')
			-> field('app.id, app.app_name, app.app_package, card.rate_card_name, advert.advertising_name, app.contract_id, contract.contract_code')
			-> join('settlement.ad_rate_cards card ON card.id=app.rate_card_id')
			-> join('settlement.ad_advertisings advert ON advert.id=app.advertising_id')
			-> join('settlement.ad_contracts contract ON contract.id=app.contract_id')
			-> where($map)
			-> find();
		$m_sch = D('Settlement.Schedule');
		
		$apps['dlist'] = array();
			
		foreach($m_sch->where(array('app_id' => $id,'is_weekend' => $weekend))->order('schedule_date ASC')->select() as $v) {
			$apps['dlist'][] = $v['schedule_date'];
		}
		$this->assign('result', $apps);
		$this->display();
	}
	
	
	/**
	 * ajax方法，查询软件包名是否存在
	 */
	public function ajax_exist_app()
	{
		$package = isset($_GET['package']) && empty($_GET['package'])==FALSE ? $_GET['package'] : NULL;

		$soft = D('soft')->where(array('package'=>$package, 'status'=>1))->order('softid DESC')->find();

		
		if($soft)
		{
			$category = D('Sj.Category')->where(array('category_id' => preg_replace('/[^\d]/','',$soft['category_id'])))->find();
			$soft['category_name'] = $category['name'];
			
			echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '成功',
				'result_data' => $soft
			));
		}
		else
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => '没找到软件！'
			));
	}

            /*导入软件数据*/
            public function import_data() 
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

                $m_schedule = D('Settlement.Schedule');
                $m_contract = D('Settlement.Contract');

                $client_m= D('Settlement.ClientMark');
                $m_card = D('Settlement.RateCard');
                $m_advert = D('Settlement.Advertising');
                $m_app = D('Settlement.ContractApp');
                
                $error_ids = '';
                $succ_ids= '';

                $is_right = 0;
                foreach($content_arr as $v){
                    $ht_id = $v[1];//软件ID
                    $contract_id = $v[0];//合同ID csv
                    $cmark= $v[2];//客户编号 csv
                    $datestr = $v[8];//投放日期
                    $date_arr = explode(";",$datestr);
                    $weekdays=0;
                    $weekends=0;
                    $weekday_date = array();
                    $weekend_date = array();
                    $weekend_discount = $weekday_discount=$v[6];//折扣 csv
                    $app_keyword=trim(iconv('gb2312','utf-8',$v[9]));//关键字
                    $rate_card_id=$v[5]; //刊例ID csv
                    //$advertising_id=1847;//广告id 关键字的
                    $advertising_id=$v[7];//广告id  最好CSV  不行就根据名字查

                    $c_m_arr = $client_m->where("status=0 and cmark='".$cmark."'")->find();;//客户编号表信息
                    if(empty($c_m_arr)||empty($ht_id)||empty($cmark)||empty($datestr)||empty($rate_card_id)||empty($advertising_id)){
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                    }


                    $app_type=$c_m_arr['stype'];
                    $app_name = $c_m_arr['softname'];
                    $app_package = $c_m_arr['package'];

                    $contract = $m_contract->find($contract_id);
                    if(empty($contract)){
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                    }

                    foreach($date_arr as $v){
                        if(!empty($v))
                        {
                            $is_right = 1;
                            $day = date('d',strtotime($v));

                            $xq = date('w',strtotime($v));

                            if($day<=5){ //月初 1到5号
                                $weekends++;
                                array_push($weekend_date,$v);
                            }else if($xq==0||$xq==6){
                                $weekends++;
                                array_push($weekend_date,$v);
                            }else{
                                $weekdays++;
                                array_push($weekday_date,$v);
                            }
                        }
                    }

                    if($is_right == 0){
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                    }

                    if(is_null($contract_id))
                    {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                    }
                    


                    $rs = $m_card->find($rate_card_id);
                    if(!$m_card->find($rate_card_id))
                    {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                    }

                    $advert = $m_advert->where("rate_card_id=".$rate_card_id." and id=".$advertising_id)->find(); //广告位ID和刊例ID要匹配
                    if(!$advert)
                    {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                            /*
                            echo json_encode(array(
                                            'result_no' => -9,
                                            'result_msg' => '没有找到广告位！'
                            ));
                            exit;
                             */
                    }

                    //排重检查
                    $tmp_dates = array();
                    foreach(array_merge( $weekend_date, $weekday_date)  as $v) {
                            array_push($tmp_dates,str_replace('/','-',$v));
                    }
                    
                    $tmp_map = array('app_package' => $app_package,'advertising_id' => $advertising_id,'schedule_date' => array('in',$tmp_dates));
                    
                    $tmp_count = $m_schedule->where($tmp_map)->select();
                    //echo $m_schedule->getlastsql();exit;

                    
                    if($tmp_count) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                            /*
                            echo json_encode(array(
                                    'result_no' => -20,
                                    'result_msg' => '同一软件不能在同一位置在同一天发布两次'
                            ));	
                            exit;
                            */
                    }


                    $data['id'] = $ht_id;
                    $data['admin_id'] = $_SESSION['admin']['admin_id'];
                    $data['mid'] = $c_m_arr['mid'];;//客户编号
                    $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                    $data['client_id'] = $contract['client_id'];
                    $data['agreement_id'] = $contract['agreement_id'];
                    $data['contract_id'] = $contract_id;//合同ID
                    $data['app_name'] = $app_name;//从客户编号读
                    $data['app_package'] = $app_package;
                    $data['app_type'] = $app_type;//CSV
                    $data['keyword'] = $app_keyword ? $app_keyword : '';
                    $data['is_keyword'] = $app_keyword ? 1 : 0;
                    $data['rate_card_id'] = $rate_card_id;
                    $data['advertising_id'] = $advertising_id;
                    
                    // 根据软件类型设置平日和周末的刊例价格
                    if($data['app_type'] == 2)
                    {
                            $weekday_price = $advert['game_weekday_price'];
                            $weekend_price = $advert['game_weekend_price'];
                    }
                    else 
                    {
                            $weekday_price = $advert['app_weekday_price'];
                            $weekend_price = $advert['app_weekend_price'];
                    }
                    
                    // 处理计算平日价格 todo
                    $data['weekdays'] = $weekdays;
                    if($data['weekdays'] > 0)
                    {
                            $data['weekday_discount'] = $weekday_discount;
                            $data['weekday_price'] = $weekday_price * $data['weekday_discount'] * 0.01;
                            $data['weekday_total'] = $data['weekday_price'] * $data['weekdays'];
                            $data['weekday_original_price'] = $weekday_price;
                            $data['weekday_original_total'] = $data['weekday_original_price'] * $data['weekdays'];
                    }
                    else 
                    {
                            $data['weekday_discount'] = 0;
                            $data['weekday_price'] = 0;
                            $data['weekday_total'] = 0;
                            $data['weekday_original_price'] = 0;
                            $data['weekday_original_total'] = 0;
                    }
                    
                    // 处理计算周末价格
                    $data['weekends'] = $weekends;
                    if( $data['weekends'] > 0 )
                    {
                            $data['weekend_discount'] = $weekend_discount;
                            $data['weekend_price'] = $weekend_price * $data['weekend_discount'] * 0.01;
                            $data['weekend_total'] = $data['weekend_price'] * $data['weekends'];
                            $data['weekend_original_price'] = $weekend_price;
                            $data['weekend_original_total'] = $data['weekend_original_price'] * $data['weekends'];
                    }
                    else
                    {
                            $data['weekend_discount'] = 0;
                            $data['weekend_price'] = 0;
                            $data['weekend_total'] = 0;
                            $data['weekend_original_price'] = 0;
                            $data['weekend_original_total'] = 0;
                    }
                    
                    $data['update_tm'] = time();
                    $data['create_tm'] = time();
                    

                    // 添加记录		
                    $app_id = $m_app->add($data);
                    //echo $m_app->getlastsql();


                    //supwater todo
                    // 更新合同表统计
                    $con_data['app_num'] = $contract['app_num']+1;
                    $con_data['app_discount_total'] = $contract['app_discount_total']+$data['weekday_total']+$data['weekend_total'];
                    $con_data['app_original_total'] = $contract['app_original_total']+$data['weekday_original_total']+$data['weekend_original_total'];
                    $con_data['update_tm'] = time();
                    
                    //2014.12.10 jiwei
                    //增加根据包名统计的软件数
                    $con_data['package_num'] = $m_app->where(array('contract_id'=>$contract['id']))->count('DISTINCT app_package');
                    //end
                    
                    //$log_result = $this->logcheck(array('id'=>$contract['id']), 'settlement.ad_contracts', $con_data, $m_contract);
                    $m_contract->where(array('id'=>$contract['id']))->save($con_data);
                    
                    // 录入广告排期数据

                    //$weekday_date = array('2016-11-22');
                    foreach($weekday_date as $date)
                    {
                            $sch_data = array();
                            $t = strtotime($date);
                            
                            $sch_data['admin_id'] = $_SESSION['admin']['admin_id'];
                            $sch_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                            $sch_data['client_id'] = $contract['client_id'];
                            $sch_data['agreement_id'] = $contract['agreement_id'];
                            $sch_data['contract_id'] = $contract['id'];
                            $sch_data['rate_card_id'] = $rate_card_id;
                            $sch_data['advertising_id'] = $advert['id'];
                            $sch_data['channel'] = $advert['channel'];
                            $sch_data['channel_node_id'] = $advert['channel_node_id'];
                            $sch_data['app_id'] = $app_id; //todo
                            $sch_data['app_name'] = $app_name;
                            $sch_data['app_package'] = $app_package;
                            $sch_data['app_type'] = $app_type;
                            $sch_data['keyword'] = $app_keyword ? $app_keyword : '';
                            $sch_data['is_keyword'] = $app_keyword ? 1 : 0;
                            $sch_data['schedule_date'] = $date;
                            $sch_data['price'] = $weekday_price * $weekday_discount * 0.01;
                            $sch_data['original_price'] = $weekday_price;
                            $sch_data['y'] = date('Y', $t);
                            $sch_data['m'] = date('m', $t);
                            $sch_data['d'] = date('d', $t);
                            $sch_data['is_weekend'] = 0;
                            $sch_data['is_scheduled'] = 0;
                            $sch_data['update_tm'] = time();
                            $sch_data['create_tm'] = time();
                            
                            $m_schedule->add($sch_data);
                    }
                    
                    //$weekend_date = array('2016-11-20');
                    foreach($weekend_date as $date)
                    {
                            $sch_data = array();
                            $t = strtotime($date);
                                    
                            $sch_data['admin_id'] = $_SESSION['admin']['admin_id'];
                            $sch_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                            $sch_data['client_id'] = $contract['client_id'];
                            $sch_data['agreement_id'] = $contract['agreement_id'];
                            $sch_data['contract_id'] = $contract['id'];
                            $sch_data['rate_card_id'] = $rate_card_id;
                            $sch_data['advertising_id'] = $advert['id'];
                            $sch_data['channel'] = $advert['channel'];
                            $sch_data['channel_node_id'] = $advert['channel_node_id'];
                            $sch_data['app_id'] = $app_id;
                            $sch_data['app_name'] = $app_name;
                            $sch_data['app_package'] = $app_package;
                            $sch_data['app_type'] = $app_type;
                            $sch_data['keyword'] = $app_keyword ? $app_keyword : '';
                            $sch_data['is_keyword'] = $app_keyword ? 1 : 0;
                            $sch_data['schedule_date'] = $date;
                            $sch_data['price'] = $weekend_price * $weekend_discount * 0.01;
                            $sch_data['original_price'] = $weekend_price;
                            $sch_data['y'] = date('Y', $t);
                            $sch_data['m'] = date('m', $t);
                            $sch_data['d'] = date('d', $t);
                            $sch_data['is_weekend'] = 1;
                            $sch_data['is_scheduled'] = 0;
                            $sch_data['update_tm'] = time();
                            $sch_data['create_tm'] = time();
                                    
                            $m_schedule->add($sch_data);
                    }

                    $succ_ids = $succ_ids.$ht_id.',';
                }


                $error_ids =substr($error_ids,0,-1);
                $succ_ids =substr($succ_ids,0,-1);
                if($error_ids!=''){
		    echo "<script>alert('ID为".$error_ids."的数据导入失败,请检查必要字段是否为空，或者该合同ID不存在数据，或者频道ID不属于该刊例ID下,或者同一软件不能在同一位置在同一天发布两次');location.href='/index.php/Settlement/ContractApp/index'</script>";//todo
                }else{
		    echo "<script>alert('导入成功');location.href='/index.php/Settlement/ContractApp/index'</script>";
                }

		//写日志
                if($succ_ids!=''){
		    $this -> writelog("广告结算:批量导入了合同软件,ID为".$succ_ids);
                }
            }
}
