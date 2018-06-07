<?php
/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2015 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author： 
 *
 * ----------------------------------------------------------------------------
*/
class AlarmNotiAction extends CommonAction {


	public function index()
	{
		$act = empty($_GET['act']) ? 'list' : $_GET['act'];

		$busi = D("Settlement.AlarmNoti");
		switch($act){
			case 'edit_one':
				$this->edit_one();
				break;
			default:
				$result = $busi->getList();
				$item = $result['item'];
				//var_dump($item);
				$this->assign('item', $item);
				$this->display();
				break;

				
		}
	}



	private function edit_one() {
	
		$busi = D("Settlement.AlarmNoti");

		$item = array();
		$aid = intval($_POST['aid']);
		$item['lasttime'] = time();
		$item['admin_id'] = $_SESSION['admin']['admin_id'];
		$item['admin_name'] = $_SESSION['admin']['admin_user_name'];
		foreach(array('status','target','cc') as $v){
			isset($_POST[$v]) && $item[$v] = strip_tags($_POST[$v]);
		}
		$log = $this->logcheck(array('aid'=>$aid), 'settlement.ad_alarmnoti', $item, $busi);
		$busi->editById($aid,$item);
		if(isset($item['status'])){
			$type = ($item['status'] == 2)?'停用':'启用';
			$this->writelog("广告结算-邮件报警配置：{$type}了mid为{$aid}的项目",'ad_business',$aid,__ACTION__ ,"","edit");
		}else{
			$this->writelog("广告结算-邮件报警配置：编辑了mid为{$aid}的商务,{$log}",'ad_business',$aid,__ACTION__ ,"","edit");
		}
		exit(json_encode(array('msg' => 'ok')));
	}
}