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
class BusinessSetAction extends CommonAction {


	public function index()
	{
		$act = (empty($_GET['act']) || !in_array($_GET['act'],array('edit_one','json_list'))) ? 'list' : $_GET['act'];
		//print_r($_SESSION);
		//exit($act);
		$busi = D("Settlement.Business");
		switch($act){
			case 'edit_one':
				$this->edit_one();
				break;
			case 'json_list':
				$result = $busi->getBusList(array('status' => 1), 1, 1000);
				exit(json_encode($result['item']));
				break;
			default:
				$p = !empty($_GET['p']) ? intval($_GET['p']) : 1; //页数
				$lr = !empty($_GET['lr']) ? intval($_GET['lr']) : 20; //每页记录数
				$search = array();
				$search['bname'] = empty($_GET['bname']) ? null : trim($_GET['bname']);
				$search['type'] = empty($_GET['type']) ? null : intval($_GET['type']);
				$search['status'] = empty($_GET['status']) ? null : intval($_GET['status']);
				
				$result = $busi->getBusList($search, $p, $lr);
				$item = $result['item'];
				$count = $result['count'];
				// 处理分页
				import("@.ORG.Page");
				$page = new Page($count, $lr);
				$page->setConfig('header','条记录');
				$page->setConfig('first','<<');
				$page->setConfig('last','>>');
				$this->assign('page', $page->show());
				$this->assign('item', $item);
				$this->display();
				break;

				
		}
	}



	private function edit_one() {
	
		$busi = D("Settlement.Business");

		$item = array();
		$bid = intval($_POST['bid']);
		$item['lasttime'] = time();
		$item['admin_id'] = $_SESSION['admin']['admin_id'];
		$item['admin_name'] = $_SESSION['admin']['admin_user_name'];
		
		foreach(array('bname','color','status','type') as $v){
			isset($_POST[$v]) && $item[$v] = strip_tags($_POST[$v]);
		}

		if($bid){
			if(!empty($item['bname']) && !empty($item['color'])) {
				$mat_items = $busi->getBusListByNC($item['bname'], $item['color']);
				foreach($mat_items as $k => $v) {
					if($v['bid'] != $bid) {
						$error = array();
						
						if($item['color'] == $v['color']) {
							$error['code'] = 2;
							$error['msg'] = '当前颜色已使用，请重新输入';
						} else {
							$error['code'] = 1;
							$error['msg'] = '当前名称已使用，请重新输入';
						}
						exit(json_encode(array('error' => $error)));
					}
				}
			}
			$log = $this->logcheck(array('bid'=>$bid), 'settlement.ad_business', $item, $busi);
			$busi->editById($bid,$item);
			if(isset($item['status'])){
				$type = ($item['status'] == 2)?'停用':'启用';
				$this->writelog("广告结算-商务配置：{$type}了mid为{$bid}的商务",'ad_business',$bid,__ACTION__ ,"","edit");
			}else{
				$this->writelog("广告结算-商务配置：编辑了mid为{$bid}的商务,{$log}",'ad_business',$bid,__ACTION__ ,"","edit");
			}
		} else {
			$mat_items = $busi->getBusListByNC($item['bname'], $item['color']);
			
			$mat_items = $mat_items ? $mat_items : array();
			
			foreach($mat_items as $k => $v) {
				$error = array();
				if($item['color'] == $v['color']) {
					$error['code'] = 2;
					$error['msg'] = '当前颜色已使用，请重新输入';
				} else {
					$error['code'] = 1;
					$error['msg'] = '当前名称已使用，请重新输入';
				}
				//var_dump($error);
				exit(json_encode(array('error' => $error)));
			}
			$bid = $busi->addItem($item);
			$this->writelog("广告结算-商务配置：添加了mid为{$bid}的商务",'ad_business',$bid,__ACTION__ ,"","add");
		}
		exit(json_encode(array('msg' => 'ok')));
	}
}
