<?php

/**
 * 广告结算-广告位相关
 * 
 * @author jiwei
 *
 */
class AdvertisingAction extends CommonAction
{
	/**
	 * 显示广告位列表
	 */
	public function index()
	{
		$rate_card_id = isset($_GET['ratecard_id']) && empty($_GET['ratecard_id']) == FALSE ? $_GET['ratecard_id'] : NULL;
		$str = isset($_GET['q']) && empty($_GET['q'])==FALSE ? $_GET['q'] : NULL; //关键字
                $q =  trim($str);
		
		if(is_null($rate_card_id))
			$this->error('参数错误！');
		
		$m_card = D('Settlement.RateCard');
		$card = $m_card->find($rate_card_id);
		if(!$card['id'])
			$this->error('没有找到刊例价格！');
		
		$m_advert = D('Settlement.Advertising');
		
		$where_query['rate_card_id'] = array('eq', $rate_card_id);
	
		if(is_null($q) == FALSE)
                        //关键词区分大小写
			//$where_query['advertising_name'] = array('like', "%{$q}%");
                        $where_query['_string'] = 'BINARY  advertising_name like "%'.$q.'%"';
		
		$result = $m_advert	-> field('id,advertising_name,app_weekday_price,app_weekend_price,game_weekday_price,game_weekend_price,remark')
							-> where($where_query)
							-> order('id ASC')
							-> select();
		
		if( isset($_GET['export']) && $_GET['export']==1 )
			$this->download($result, "刊例_广告位_".date('Y-m-d-h-i').".csv");
		
		$this->assign('ratecard_id', $rate_card_id);
		$this->assign('ratecard_name', $card['rate_card_name']);
		$this->assign('ratecard', $card);
		$this->assign('q', $q);
		$this->assign('result', $result);
		$this->display();
	}
	
	/**
	 * 显示编辑广告位表单
	 */
	public function edit_advertising_show()
	{
		$advertising_id = isset($_GET['id']) && empty($_GET['id'])==FALSE ? $_GET['id'] : NULL;
		if(is_null($advertising_id))
			$this->error("参数错误！");

		$m_advert = D('Settlement.Advertising');
		$advert = $m_advert->find($advertising_id);
		
		if(!$advert)
			$this->error("记录不存在！");
		
		//2014.9.28 jiwei
		//增加刊例的调用
		$m_card = D('Settlement.RateCard');
		$card = $m_card->find($advert['rate_card_id']);
		
		$this->assign('card', $card);
		$this->assign('advert', $advert);
		$this->display();
	}
	
	/**
	 * 处理广告位修改
	 * 重要：可能涉及所有和这个广告位关联的软件价格处理和结算统计处理
	 */
	public function edit_advertising_do()
	{
		$advertising_id = isset($_GET['id']) && empty($_GET['id'])==FALSE ? $_GET['id'] : NULL;
		if(is_null($advertising_id))
		{
			echo json_encode(array(
				'result_no' => -1,
				'result_msg' => '参数错误',
			));
			
			exit;
		}
		
		$app_weekday_price = isset($_POST['app_weekday_price']) && empty($_POST['app_weekday_price'])==FALSE ? $_POST['app_weekday_price'] : NULL;
		$app_weekend_price = isset($_POST['app_weekend_price']) && empty($_POST['app_weekend_price'])==FALSE ? $_POST['app_weekend_price'] : NULL;
		$game_weekday_price = isset($_POST['game_weekday_price']) && empty($_POST['game_weekday_price'])==FALSE ? $_POST['game_weekday_price'] : NULL;
		$game_weekend_price = isset($_POST['game_weekend_price']) && empty($_POST['game_weekend_price'])==FALSE ? $_POST['game_weekend_price'] : NULL;
		$remark = isset($_POST['remark']) && empty($_POST['remark'])==FALSE ? htmlspecialchars($_POST['remark']) : NULL;
		
		//2014.9.28 jiwei
		//增加启用停用判断
		$m_advert = D('Settlement.Advertising');
		$advert = $m_advert->find($advertising_id);
		
		$m_card = D('Settlement.RateCard');
		$card = $m_card->find($advert['rate_card_id']);

		if($card['is_disabled']==1)
		{	
			if(is_null($app_weekday_price) || is_null($app_weekend_price) || is_null($game_weekday_price) || is_null($game_weekend_price) )
			{
				echo json_encode(array(
						'result_no' => -2,
						'result_msg' => '所有价格都必须填写',
				));
					
				exit;
			}
			
			if(!(is_numeric($app_weekday_price) && is_numeric($app_weekend_price) && is_numeric($game_weekday_price) && is_numeric($game_weekend_price)) )
			{
				echo json_encode(array(
						'result_no' => -3,
						'result_msg' => '所有价格都需要为数字，如123456.78',
				));
					
				exit;
			}
			
			if(mb_strlen($remark) > 255)
			{
				echo json_encode(array(
						'result_no' => -4,
						'result_msg' => '广告位备注不能超过255个字符',
				));
				
				exit;
			}
			
			
			$data = array(
					'app_weekday_price' => $app_weekday_price,
					'app_weekend_price' => $app_weekend_price,
					'game_weekday_price' => $game_weekday_price,
					'game_weekend_price' => $game_weekend_price,
					'remark' => $remark,
					'update_tm' => time()
			);
		}
		else
		{
			if(mb_strlen($remark) > 255)
			{
				echo json_encode(array(
						'result_no' => -4,
						'result_msg' => '广告位备注不能超过255个字符',
				));
			
				exit;
			}
						
			$data = array(
					'remark' => $remark,
					'update_tm' => time()
			);
		}
		
		//$m_advert = D('Settlement.Advertising');
		$advert = $m_advert->find($advertising_id);
		
		
		//2014.11.6 jiwei 记录日志
		$log_result = $this->logcheck(array('id'=>$advertising_id), 'settlement.ad_advertisings', $data, $m_advert);

		$is_succeed = $m_advert->where(array('id'=>$advertising_id))->save($data);
		
		if($is_succeed)
		{
			//$m_card = D('Settlement.RateCard');
			$m_card->where(array('id'=>$advert['rate_card_id']))->save(array('update_tm'=>time()));

			//2014.11.6 jiwei 记录日志
			$this->writelog("广告结算：编辑广告位(advertising_id={$advertising_id})".$log_result, 'ad_advertisings',$advertising_id,__ACTION__ ,'','edit');
		}
		
		echo json_encode(array(
				'result_no' => 1,
				'result_msg' => '调用成功',
		));
	}
	
	/**
	 * 处理下载
	 * @param unknown $reuslt
	 */
	private function download($result, $filename)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		
		$out = "广告位名称,平日应用刊例价（元/天）,周末月初应用刊例价（元/天）,平日游戏刊例价格,周末月初游戏刊例价格,备注\r\n";
		
		foreach($result as $item)
			$out.="{$item['advertising_name']},{$item['app_weekday_price']},{$item['app_weekend_price']},{$item['game_weekday_price']},{$item['game_weekend_price']},{$item['remark']}\r\n";
	
		echo iconv('utf-8', 'gb2312', $out);
		exit;
	}
}