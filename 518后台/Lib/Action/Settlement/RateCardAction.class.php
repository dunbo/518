<?php
/**
 * 广告结算-刊例相关
 * 
 * @author jiwei
 *
 */
class RateCardAction extends CommonAction 
{
	const EXCELHASH = 'e3848982771ec7feb2ec534e370d390c';
	
	public function index()
	{
		//
		// 处理输入参数
		//
		$str = isset($_GET['q']) && empty($_GET['q'])==FALSE ? $_GET['q'] : NULL; //关键字    
               $q =  trim($str);
		$start = isset($_GET['start']) && strtotime($_GET['start']) > 0 ? strtotime($_GET['start']) : NULL; //开始日期
		$end = isset($_GET['end']) && strtotime($_GET['end']) > 0 ? strtotime($_GET['end']) : NULL; //结束日期
		$p = isset($_GET['p']) && empty($_GET['p'])==FALSE ? $_GET['p'] : 1; //页数
		$lr = isset($_GET['lr']) && empty($_GET['lr'])==FALSE ? $_GET['lr'] : 20; //每页记录数

		if($start && is_null($end))
			$end = time();
		
		//
		// 处理查询条件
		//
		$where_query = array(); 
		
		// 没删除的
		$where_query['status'] = array('eq', 1);
		
		// 关键字
		if(is_null($q) == FALSE)
                    //关键词区分大小写
			$where_query['_string'] = 'BINARY  rate_card_name like "%'.$q.'%"';

		
		// 创建时间段
		if(is_null($start)==FALSE) 
		{
			$where_query['create_tm'] = array(
				array('egt', $start), // 大于等于开始时间戳
				array('lt', $end) //小于结束时间戳
			);
			
			$this->assign('start', $start);
			$this->assign('end', $end);
		}
		
		// 处理查询
		$m_card = D('Settlement.RateCard');

		$count = $m_card->where($where_query)->count();
		$result = $m_card	-> field('id,rate_card_name,is_defaulted,is_disabled,remark,update_tm,create_tm')
							-> where($where_query)
							-> page("{$p},{$lr}")
							-> order('id DESC')
							-> select();
                                                       
		
		// 处理分页
		import("@.ORG.Page");
		$page = new Page($count, $lr);
		$page->setConfig('header','条记录');
		$page->setConfig('first','<<');
		$page->setConfig('last','>>');
		$page_show = $page->show();
		

		if(is_null($start)==FALSE)
			$this->assign('start', date('Y-m-d H:i:s', $start));
		if(is_null($end)==FALSE)
			$this->assign('end', date('Y-m-d H:i:s', $end));
		
		// 查询默认刊例ID
		$default = $m_card->where(array('is_defaulted'=>1))->find();
		$default_id = $default['id'] ? $default['id'] : 0;
		
		$this->assign('default_id', $default_id);
		$this->assign('q', $q);
		$this->assign('url_suffix',base64_encode($this->get_url_suffix(array('q','start','end','p','lr'))));
		$this->assign('page', $page_show);
		$this->assign('result', $result);
		
		$this->display();
	}
	
	/**
	 * 显示上传刊例表单
	 */
	public function add_ratecard_show()
	{
		$this->display();
	}
	
	/**
	 * 处理上传刊例
	 */
	public function add_ratecard_do()
	{
		if(!$this->isPost())
			$this->error('请求错误！');
		
		//
		// 处理验证
		//
		$ratecard_name = isset($_POST['ratecard_name']) && empty($_POST['ratecard_name'])==FALSE ? htmlspecialchars($_POST['ratecard_name']) : NULL;
		$ratecard_remark = isset($_POST['ratecard_remark']) && empty($_POST['ratecard_remark'])==FALSE ? htmlspecialchars($_POST['ratecard_remark']) : '';
		
		if(is_null($ratecard_name))
		{
			echo "<script>alert('刊例名称为必填项');</script>";
			exit;
		}
		
		if(mb_strlen($ratecard_name) > 50)
		{
			echo "<script>alert('刊例名称不能超过50个字符');</script>";
			exit;
		}
		
		if(empty($ratecard_remark)==FALSE && mb_strlen($ratecard_remark) > 255)
		{
			echo "<script>alert('刊例备注不能超过255个字符');</script>";
			exit;
		}
		
		//
		// 处理文件上传
		//
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('xlsx');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');</script>";
			exit;
		}


		// 处理解析刊例
		$adv_list = $this->parse_advertising($info[0]['savepath'].$info[0]['savename']);
		//$adv_list = $this->parse_advertising('/tmp/1409359020.xlsx');
		if($adv_list == FALSE)
		{
			echo "<script>alert('解析刊例模板错误！');</script>";
			exit;
		}
		
		//
		// 处理创建刊例模板信息和广告位信息
		//
		$m_card = D('Settlement.RateCard');
		$m_adv = D('Settlement.Advertising');

		$data = array(	'admin_id' => $_SESSION['admin']['admin_id'],
						'admin_name' => $_SESSION['admin']['admin_user_name'],
						'rate_card_name' => $ratecard_name,
						'is_defaulted' => 0,
						'is_disabled' => 1, //2014.9.28 jiwei #默认将是否停用状态设置为停用
						'status' => 1,
						'remark' => $ratecard_remark,
						'create_tm' => time(),
						'update_tm' => time());

		try
		{
			$m_card->startTrans();
			
			$rate_card_id = $m_card->add($data);
								
			foreach($adv_list as $adv)
			{
				$adv['rate_card_id'] = $rate_card_id;
				$adv['admin_id'] = $_SESSION['admin']['admin_id'];
				$adv['admin_name'] = $_SESSION['admin']['admin_user_name'];
				$adv['update_tm'] = time();
				$adv['create_tm'] = time();

				$m_adv->add($adv);
			}
		
			$m_card->commit();
			$this->writelog("广告结算：新增刊例(rate_card_id={$rate_card_id})", 'sj_rate_cards',$rate_card_id,__ACTION__ ,'','add');
		}
		catch(Exception $e)
		{
			$m_card->rolback();
			
			echo "<script>alert('创建数据错误！');</script>";
			exit;
		}
		
		
		//
		// 搜索、比对广告位，提示校对情况
		//
		$adv_tables = C('_proofread_.advert_tables');

		$proofread_msg = NULL;
		$n = 0;
		foreach($adv_list as $adv)
		{
			$exist = false;
			// 如果频道标记不在广告位配置数组
			if( !key_exists($adv['channel'], $adv_tables) )
				continue;
			
			// 如果为 搜索相关广告位 和 猜你喜欢广告位 则不进行校对   返回运营和广告闪屏没有区间所以不用校验  2016-11-10
			if( $adv['channel']=='SK' || $adv['channel']=='SKH' || $adv['channel']=='SKD' || $adv['channel']=='PR' || $adv['channel']=='PU' || $adv['channel']=='PUP' || $adv['channel']=='SAD' || $adv['channel']=='RE')
				continue;
			
			$m = D($adv_tables[$adv['channel']]['node']['model']);
			$exist = $m->field($adv_tables[$adv['channel']]['node']['find'])->where(array($adv_tables[$adv['channel']]['node']['find']=>$adv['channel_node_id']))->find();
			
			if(!$exist)
			{
				$n++;
				$proofread_msg .= "{$adv['advertising_name']}({$adv['advertising_code']})\\n";
			}
		}
		
		if($n > 0)
		{
			$proofread_msg = "未找到广告位数：{$n}\\n".$proofread_msg;
			echo "<script>alert(\"".$proofread_msg."\");parent.tb_remove();parent.location.href='/index.php/Settlement/RateCard/download/id/{$rate_card_id}';</script>";
			exit;
		}
		
		echo "<script>alert('添加成功！');parent.location.reload();</script>";
	}
	
	/**
	 * 显示编辑刊例表单
	 */
	public function edit_ratecard_show()
	{
		$rate_card_id = isset($_GET['id']) && empty($_GET['id'])==FALSE ? $_GET['id'] : NULL;
		
		if(is_null($rate_card_id))
			$this->error('没有获得参数');
		
		$m_card = D('Settlement.RateCard');
		$card = $m_card->find($rate_card_id);
		
		if(!$card['id'])
			$this->error('没找到数据');
		
		$this->assign('ratecard_id', $card['id']);
		$this->assign('ratecard_name', $card['rate_card_name']);
		$this->assign('ratecard_remark', $card['remark']);
		$this->display();
	}
	
	/**
	 * 处理编辑刊例
	 */
	public function edit_ratecard_do()
	{
		$rate_card_id = isset($_GET['id']) && empty($_GET['id'])==FALSE ? $_GET['id'] : NULL;
		
		if(is_null($rate_card_id))
		{
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => '参数错误',	
			));
			
			exit;
		}
		
		$ratecard_name = isset($_POST['ratecard_name']) && empty($_POST['ratecard_name'])==FALSE ? htmlspecialchars($_POST['ratecard_name']) : NULL;
		$ratecard_remark = isset($_POST['ratecard_remark']) && empty($_POST['ratecard_remark'])==FALSE ? htmlspecialchars($_POST['ratecard_remark']) : '';
		
		if(is_null($ratecard_name))
		{
			echo json_encode(array(
					'result_no' => -2,
					'result_msg' => '刊例名称为必填项',
			));
				
			exit;
		}
		
		if(mb_strlen($ratecard_name) > 50)
		{
			echo json_encode(array(
					'result_no' => -3,
					'result_msg' => '刊例名称不能超过50个字符',
			));
			
			exit;
		}
		
		if(empty($ratecard_remark)==FALSE && mb_strlen($ratecard_remark) > 255)
		{
			echo json_encode(array(
					'result_no' => -4,
					'result_msg' => '刊例备注不能超过255个字符',
			));
			
			exit;
		}
		
		
		$data = array(
			'rate_card_name' => $ratecard_name,
			'remark' => $ratecard_remark,
			'update_tm' => time()
		);
		
		$m_card = D('Settlement.RateCard');
		
		$log_result = $this->logcheck(array('id'=>$rate_card_id), 'settlement.ad_rate_cards', $data, $m_card);
		$m_card->where(array('id'=>$rate_card_id))->save($data);
		$this->writelog("广告结算：编辑刊例(rate_card_id={$rate_card_id})".$log_result, 'sj_rate_cards',$rate_card_id,__ACTION__ ,'','edit');
		
		echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '调用成功',	
		));
	}
	
	/**
	 * 下载某刊例，导出为csv文件
	 */
	public function download()
	{
		$id = isset($_GET['id']) && empty($_GET['id'])==FALSE ? $_GET['id'] : NULL;
		if(is_null($id))
			exit;
		
	
		$adv_tables = C('_proofread_.advert_tables');
		$m_adv = D('Settlement.Advertising');
		
		$filename = time().'.csv';
		$out = "广告编号,广告位名称,应用刊例价（元/天）平日,应用刊例价（元/天）周末/月初,游戏刊例价格（元/天）平日,游戏刊例价格（元/天）周末/月初,排期系统频道类型,排期系统频道ID,备注\r\n";
		
		$result = $m_adv->where(array('rate_card_id'=>array('eq',$id)))->select();

		foreach($result as $adv)
		{
			$exist = false;
			
			// 如果频道标记不在广告位配置数组
			if( !key_exists($adv['channel'], $adv_tables) )
				continue;
				
			// 如果为 搜索相关广告位 和 猜你喜欢广告位 则不进行校对
			if( $adv['channel']=='SK' || $adv['channel']=='SKH' || $adv['channel']=='SKD' || $adv['channel']=='PR' || $adv['channel']=='PU')
				continue;
			
			$m = D($adv_tables[$adv['channel']]['node']['model']);
			$exist = $m->field($adv_tables[$adv['channel']]['node']['find'])->where(array($adv_tables[$adv['channel']]['node']['find']=>$adv['channel_node_id']))->find();
			if(!$exist)
			{
				$out .= "{$adv['advertising_code']},{$adv['advertising_name']},{$adv['app_weekday_price']},{$adv['app_weekend_price']},{$adv['game_weekday_price']},{$adv['game_weekend_price']},{$adv['channel']},{$adv['channel_node_id']},{$adv['remark']}\r\n";
			}
		}
				
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		
		echo iconv('utf-8', 'gb2312', $out);
		exit;
	}	
	
	/**
	 * 设置某刊例为默认刊例
	 */
	public function set_default()
	{
		$rate_card_id = isset($_GET['id']) && empty($_GET['id']) == FALSE ? $_GET['id'] : NULL;
		
		if(is_null($rate_card_id))
		{
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => '参数错误',	
			));
			
			exit;
		}
		
		$m_card = D('Settlement.RateCard');
		
		$card = $m_card->find($rate_card_id);
		if(!$card)
		{
			echo json_encode(array(
				'result_no' => -2,
				'result_msg' => '没有找到记录',	
			));
			
			exit;
		}
		
		// 2014.9.28 jiwei
		// 判断当前刊例是否是停用状态，如果停用提示用户不能更改为默认刊例
		if($card['is_disabled']==1)
		{
			echo json_encode(array(
					'result_no' => -3,
					'result_msg' => '请先启用刊例',
			));
				
			exit;
		}
		
		
		try
		{
			$m_card->startTrans();
			
			$m_card	-> where(array('id'=>array('eq',$rate_card_id)))
					-> save(array('is_defaulted'=>1, 'is_disabled'=>0));
			
			$m_card	-> where(array('id'=>array('neq',$rate_card_id)))
					-> save(array('is_defaulted'=>0));
			
			$m_card->commit();
			$this->writelog("广告结算：修改刊例(rate_card_id={$rate_card_id}),设置为默认刊例", 'sj_rate_cards',$rate_card_id,__ACTION__ ,'','edit');
		}
		catch(Exception $e)
		{
			$m_card->rolback();
		}
		
		echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '调用成功',
		));
	}
	
	/**
	 * 停用某个刊例，默认刊例无法停用
	 */
	public function set_disable()
	{
		$rate_card_id = isset($_GET['id']) && empty($_GET['id']) == FALSE ? $_GET['id'] : NULL;
		
		if(is_null($rate_card_id))
		{
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => '参数错误',	
			));
			
			exit;
		}
		
		$m_card = D('Settlement.RateCard');
		
		$card = $m_card->find($rate_card_id);
		if($card['is_defaulted'] == 1)
		{
			echo json_encode(array(
					'result_no' => -2,
					'result_msg' => '默认刊例无法设置停用',
			));
				
			exit;
		}
		
		$where_query['id'] = array('eq', $rate_card_id);
		$where_query['is_defaulted'] = array('eq', 0);
		
		$m_card->where($where_query)->save(array('is_disabled'=>1));
		$this->writelog("广告结算：修改刊例(rate_card_id={$rate_card_id}),设置为停用", 'sj_rate_cards',$rate_card_id,__ACTION__ ,'','edit');
		
		echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '调用成功',
		));
	}
	
	/**
	 * 启用某个刊例
	 */
	public function set_enable()
	{
		$rate_card_id = isset($_GET['id']) && empty($_GET['id']) == FALSE ? $_GET['id'] : NULL;
		
		if(is_null($rate_card_id))
		{
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => '参数错误',	
			));
			
			exit;
		}
		
		$m_card = D('Settlement.RateCard');
		
		$m_card	-> where(array('id'=>array('eq', $rate_card_id)))
				-> save(array('is_disabled'=>0));
		
		$this->writelog("广告结算：修改刊例(rate_card_id={$rate_card_id}),设置为启用", 'sj_rate_cards',$rate_card_id,__ACTION__ ,'','edit');
		
		echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '调用成功',
		));
	}
	
	/**
	 * 标记删除某个刊例，默认刊例无法删除
	 */
	public function set_delete()
	{
		$rate_card_id = isset($_GET['id']) && empty($_GET['id']) == FALSE ? $_GET['id'] : NULL;
		
		if(is_null($rate_card_id))
		{
			echo json_encode(array(
					'result_no' => -1,
					'result_msg' => '参数错误',
			));
				
			exit;
		}
		
		$m_card = D('Settlement.RateCard');
		$card = $m_card->find($rate_card_id);
		
		if( $card['is_defaulted'] == 1 )
		{
			echo json_encode(array(
				'result_no' => -2,
				'result_msg' => '默认刊例无法删除',
			));
				
			exit;
		}
		
		if( $card['is_disabled'] == 0 )
		{
			echo json_encode(array(
				'result_no' => -3,
				'result_msg' => '刊例已经启用无法删除',
			));
				
			exit;
		}
		
		
		$m_card	-> where(array('id'=>array('eq', $rate_card_id)))
				-> save(array('status'=>0 ));
		
		$this->writelog("广告结算：修改刊例(rate_card_id={$rate_card_id}),设置为删除", 'sj_rate_cards',$rate_card_id,__ACTION__ ,'','del');
		
		echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '调用成功',
		));
	}
	
	/**
	 * 解析刊例文档中的广告位
	 * @param unknown $ratecard_file
	 * @return array $result
	 */
	private function parse_advertising($ratecard_file)
	{
		if(!file_exists($ratecard_file))
			return FALSE;
		
		import('@.ORG.PHPExcel.PHPExcel');
		
		$excel_file = $ratecard_file;
		$excel_type = 'Excel2007'; //excel文件类型
		$sheet_name = 'Sheet1'; //工作区名称
		
		// 指定Excel类型，创建一个reader
		$excel_reader = PHPExcel_IOFactory::createReader($excel_type);
		// 设置只读取数据，不包括公式和格式
		$excel_reader->setReadDataOnly(true);
		// 只读取指定的sheet
		$excel_reader->setLoadSheetsOnly($sheet_name);
		
		$excel = $excel_reader->load($excel_file);
		$sheet = $excel->getSheet(0);

		$col_count = $sheet->getHighestColumn(); //列数
		$row_count = $sheet->getHighestRow(); //行数
		
		//
		// 检查excel模板的正确性
		// 根据模板中的第一行所有文字进行md5来检测是不是规定的excel模板
		//
		for($col = 'A'; $col <= $col_count; $col++)
			$check_str.=$sheet->getCell($col.'1')->getValue();
		
		if(self::EXCELHASH != md5($check_str))
			return FALSE;
		
		//
		// 从excel第二行开始处理，生成数组返回
		//
		$result = FALSE;
		for($row = 2; $row <= $row_count; $row++)
		{
			$items = array();
			for($col = 'A'; $col <= $col_count; $col++)
			{
				$items[] = $sheet->getCell($col.$row)->getValue();
			}
			
			list($advertising_code, $advertising_name, $app_weekday_price, $app_weekend_price, $game_weekday_price, $game_weekend_price, $channel, $channel_node_id, $remark) = $items;
			
			$result[] = array(
					'advertising_code' => $advertising_code,
					'advertising_name' => $advertising_name,
					'app_weekday_price' => $app_weekday_price,
					'app_weekend_price' => $app_weekend_price,
					'game_weekday_price' => $game_weekday_price,
					'game_weekend_price' => $game_weekend_price,
					'channel' => $channel,
					'channel_node_id' => (int)$channel_node_id,
					'remark' => empty($remark) ? '' : $remark
			);
		}
		
		return $result;
	}

}