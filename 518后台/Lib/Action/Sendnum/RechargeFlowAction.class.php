<?php
/*
 * 流量签到活动管理
 */
class RechargeFlowAction extends CommonAction {

	private $activity;
	private $link_pre;
	private $link_path;
	private $link_log_url;

	public function __construct() {
		parent::__construct();
		$this->activity = D('sendNum.Activity');			
		$this->link_pre = 'http://fx.anzhi.com/activity/activity_page/';
		$this->link_path = ACTIVITY_PAGE;
	}

	public function produceList() {
	    $model = D('sendNum.RechargeFlow');
		$where = array();

		//$this->check_where($where, 'ap_name', 'isset', 'like');
		$this->check_where($where, 'orderid', 'isset');
		$this->check_where($where, 'mobile', 'isset');
		$this->check_where($where, 'aid', 'isset');
		$this->check_where($where, 'price', 'isset');
		if($_GET['status']!='-1'){
		    $this->check_where($where, 'status', 'isset');
		}
		
		$limit = isset($_GET['lr']) ? $_GET['lr'] : 10;
		list($produceList,$total,$Page) = $model ->get_activity_page($where,$limit);
		
		if(EVN == '9test' || EVN == 'prod'){
			$my_host = 'http://fx.anzhi.com';
		}elseif(EVN == '518test'){
			$my_host = ACTIVITY_URL;
		}
				
		$this -> assign('lr',$_GET['lr']?$_GET['lr']:20);
		$this -> assign('my_host',$my_host);
		$this -> assign('page', $Page->show());
		$this -> assign('get', $_GET);
		$this->assign('produceList', $produceList);
		$this->display('produceList');
	}
} 
