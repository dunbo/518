<?php
class DeliveryFinanceAction extends CommonAction{
	
	/**
	 * 财务结算列表
	 */
	function finance_list()
	{
		$channel_id	=	!empty($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		$start_time	=	!empty($_GET['start_time']) ? $_GET['start_time'] : null;
		$end_time	=	!empty($_GET['end_time']) ? $_GET['end_time'] : null;
		$status =	!empty($_GET['status']) ? $_GET['status'] : null;
		$export		=	!empty($_GET['export']) ? $_GET['export'] : null;
		$batch_id	=	!empty($_GET['batch_id']) ? trim($_GET['batch_id'],',') : null;
		$time		=	date('Y-m-d', time());
		
		if(strtotime($start_time.'-01') > strtotime($end_time.'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$where = '1=1 and status !=1';
		$channel_id	&&	$where .= " and channel_id = {$channel_id}";
		$start_time	&&	$where .= " and date >= '{$start_time}'";
		$end_time	&&	$where .= " and date <= '{$end_time}'";
		$status	&&	$where .= " and status= {$status}";
		
		$where_go['_string'] = $where;
		$model = D('Channel_cooperation.toufang');
		$count = $model->table('tf_month_statistics')->where($where_go)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 20, $param);
		$show	=	$Page->show();
		
		$status_arr = array(1=>'待审核',2=>'待付款',3=>'已付款',4=>'已冻结');
		
		$order = " `status` ASC, `date` DESC, payment_amount DESC";
		
		if( $batch_id ) {
			//批量导出
			$list	=	$model->table('tf_month_statistics') -> where( array('id' => array('in',$batch_id)) )->order($order) -> select();
		}elseif($export) {
			//全部导出
			$list	=	$model->table('tf_month_statistics') -> where($where_go)->order($order) -> select();
		}else {
			//正常
			$list	=	$model->table('tf_month_statistics') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		}
		
		//是否导出报表
		if( $export || $batch_id ) {
			$filename = '外投结算'.date('Y-m-d').'.csv'; //设置文件名
			$str = '日期,渠道名称,下载量（扣量前）,下载量（扣量后）,安智分成,渠道分成,补差,应付金额,已付金额,差额补齐,未付金额,发票金额,税率,备注,状态';
			$str = iconv('utf-8','gb2312', $str);
			$str = $str."\n";
			foreach ( $list as $val ) {
				$data			=	iconv('utf-8','gb2312', $val['date']);
				$channel_name	=	iconv('utf-8','gb2312', $val['channel_name']);
				$downum_front	=	$val['downum_front'];
				$downum_after	=	$val['downum_after'];
				$anzhi_val		=	$val['anzhi_val'];
				$other_val		=	$val['other_val'];
				$reserve_price		=	$val['reserve_price'];
				$payment_amount		=	$val['payment_amount'];
				
				$diff_complete		=	$val['diff_complete'];
				$amount_payable		=	$val['amount_payable'];
				$unpaid_amount		=	$val['unpaid_amount'];
				
				$invoice		=	$val['invoice'];
				$tax_rate		=	$val['tax_rate'];
				$remarks		=	iconv('utf-8','gb2312', $val['remarks']);
				//1:待审核,2:待付款,3:已付款,4:已冻结,5:解冻
				$status		=	iconv('utf-8','gb2312', $status_arr[$val['status']]);
				$str	.=	$data.",".$channel_name.",".$downum_front.",".$downum_after.",".$anzhi_val.",".$other_val.",".$reserve_price.",".$amount_payable.",".$payment_amount.",".$diff_complete.",".$unpaid_amount.",".$invoice.",".$tax_rate.",".$remarks.",".$status."\n"; //用引文逗号分开
			}
			$this->export_csv($filename,$str);die; //导出
		}
		
		$channel_list	=	$model->table('tf_channel')->select();
		
		$this->assign('page', $show);
		$this->assign('status', $status);
		$this->assign('status_arr', $status_arr);
		$this->assign('channel_list', $channel_list);
		$this->assign('channel_id', $channel_id);
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 财务结算
	 */
	function finance_settlement()
	{
		$id		=	!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$model	=	D('Channel_cooperation.toufang');
		$rows = $model->table('tf_month_statistics') -> where(array('id'=> $id)) -> find();
		if (!empty($_POST)) {
			$payment_amount	=	is_numeric($_POST['payment_amount']) ? floatval($_POST['payment_amount']) : null;
			$invoice	=	is_numeric($_POST['invoice']) ? floatval($_POST['invoice']) : null;
			$invoice_tm	=	strtotime($_POST['invoice_tm']) ? strtotime($_POST['invoice_tm']) : null;
			$payment_tm	=	strtotime($_POST['payment_tm']) ? strtotime($_POST['payment_tm']) : null;
			$time		=	time();
			
			
			if( !$payment_amount ) {
				$this->error('付款金额不能为空');
			}
			if( !$payment_tm ) {
				$this->error('付款日期不能为空');
			}
			
			//未付金额=应付金额-付款金额
			$unpaid_amount = $rows['amount_payable'] - $payment_amount;
			//差额补齐=付款金额-应付金额
			$diff_complete = $payment_amount - $rows['amount_payable'];
			
			$map = array(
					'payment_amount'		=>	$payment_amount,
					'invoice'	=>	$invoice,
					'invoice_tm'	=>	$invoice_tm,
					'payment_tm'	=>	$payment_tm,
					'update_tm'	=>	$time,
					'status'	=>	3, //结算过了就是已付款
					'diff_complete'		=>	$diff_complete,
					'unpaid_amount'		=>	$unpaid_amount,
			);
			$finance_id = $model->table('tf_month_statistics')->where(array('id'=>$id))->save($map);
			
			
			if($finance_id ) {
				$this->writelog("在软件外投管理中-财务结算-结算了id为[{$id}]的数据", 'tf_month_statistics', $finance_id,__ACTION__,"","save");
				$this->success('结算成功');
			}else {
				$this->error('结算失败');
			}
		} else {
			$this->assign('rows', $rows);
			$this->display();
		}
	}
	
	/**
	 * 编辑备注
	 */
	function remarks_edit()
	{	
		$id		=	!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$model	=	D('Channel_cooperation.toufang');
		if( !empty($_POST) ) {
			$remarks	=	!empty($_POST['remarks']) ? addslashes(trim(strip_tags($_POST['remarks']))) : '';
			$map	=	array(
				'remarks' => $remarks,
				'update_tm'	=>	$time,
			);
			$where	=	array(
				'id' => $id,
			);
			$res = $model->table('tf_month_statistics')->where(array('id'=> $id))->save($map);
			if( $res ) {
				$this->writelog("在软件外投管理中-财务管理-编辑了id为[{$id}]的备注,内容为【{$remarks}】", 'tf_month_statistics', $id,__ACTION__,"","edit");
				$this->success('修改成功');
			}else {
				$this->error('修改失败');
			}
		}else {
			$rows = $model->table('tf_month_statistics') -> where(array('id'=> $id)) -> find();
			$this->assign('rows', $rows);
			$this->display();
		}
	}
	
	/**
	 * 结算操作 驳回、结算、冻结、解冻
	 */
	function finance_operation()
	{
		$id		=	!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$option	=	!empty($_REQUEST['option']) ? $_REQUEST['option'] : null;
		$model	=	D('Channel_cooperation.toufang');
		switch ($option) {
			case 'dj':
				//冻结
				$status = 4;
				break;
			case 'jd':
				//解冻
				$status = 2;
				break;
			case 'bh':
				//驳回
				$status = 1;
				break;
			default:
				$this->error('操作有误');
				break;
		}
		if($option) {
			$map = array('status'=>$status,'update_tm'=>time());
			
			$res = $model->table('tf_month_statistics')->where(array('id'=> $id))->save($map);
			if( $res ) {
				if($option=='dj')
				{
					$this->writelog("在软件外投管理中-财务管理-冻结了id为[{$id}]的数据", 'tf_month_statistics', $id,__ACTION__ ,"","edit");
				}
				elseif($option=='jd')
				{
					$this->writelog("在软件外投管理中-财务管理-解冻了id为[{$id}]的数据", 'tf_month_statistics', $id,__ACTION__ ,"","edit");
				}
				elseif($option=='bh')
				{
					$this->writelog("在软件外投管理中-财务管理-驳回了id为[{$id}]的数据", 'tf_month_statistics', $id,__ACTION__ ,"","edit");
				}
				$this->success('修改成功');
			}else {
				$this->error('修改失败');
			}	
		}
	}
	function export_csv($filename, $data)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $data;
	}
	
}