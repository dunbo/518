<?php

class AlarmNotiModel extends AdvModel
{
	protected $dbName = 'settlement';
	protected $tableName = 'alarmnoti';
	protected $tablePrefix = 'ad_';




	public function getList()
	{
		return array('item' => $this->field(" * ")->select(),'count' => $this->where($where)->count());
	}


	public function editById($aid,$data) {
		$result = $this->where("aid=$aid")->save($data);
		return $result;
	}


	/**
	* 
	*
	*
	*/
	public function on($action, $date, $new_data, $old_data = array()) {

		$timeout = false;
		$now_time = time();
		
		//var_dump($new_data);
		//var_dump($old_data);
		
		//var_dump($date);
		
		if(is_array($date)) {
			foreach($date as $v) {
				$tmp = strtotime($v);
				if($tmp < $now_time) {
					$timeout = true;
				}
			}
		} else {
			if(strtotime($v) > $now_time) {
				$timeout = true;
			}
		}
		if(!$timeout){
			return false;
		}
		$item = array();
		$item['user'] = $_SESSION['admin']['admin_user_name'];
		$item['uid'] = $_SESSION['admin']['admin_id'];
		$item['change'] = array();
		switch($action) {
			case 'add_contract':
				$item['title'] = "新增了合同";
				$item['contract_code'] = $new_data['contract_code'];
				break;
			case 'del_contract':
				$item['title'] = "删除了合同";
				$item['contract_code'] = $old_data['contract_code'];
				break;
			case 'edit_contract':
				$item['title'] = "修改了合同";
				$item['change'] = $this->getChange($new_data, $old_data);
				$item['contract_code'] = $old_data['contract_code'];
				break;
			case 'add_app':
				$item['title'] = "新增了合同软件";
				$item['app_name'] = $new_data['app_name'];
				$item['contract_code'] = $new_data['contract_code'];
				break;
			case 'del_app':
				$item['title'] = "删除了合同软件";
				$item['app_name'] = $old_data['app_name'];
				$item['contract_code'] = $old_data['contract_code'];
				break;
			case 'edit_app':
				$item['title'] = "修改了合同软件";
				$item['change'] = $this->getChange($new_data, $old_data);
				$item['app_name'] = $old_data['app_name'];
				$item['contract_code'] = $old_data['contract_code'];
				break;

			default:
				throw new Exception("传递的动作参数错误");
				break;
		}
		//var_dump($action);
		$this->sendMail($item, $action);
		//exit;
		return true;

	}


	private function getChange($ndata, $odata) {

		$item = array();
		foreach($ndata as $k => $v) {
			
			if(in_array($k,array('update_tm','admin_id','admin_name'))) {
				continue;
			}
			
			if(isset($odata[$k]) && $odata[$k] != $v) {
				$item[$k] = array('new' => $v,'old' => $odata[$k]);
			}
		}
		return $item;
	}

	private function sendMail($item, $action) {

		$clm_name = array(
			'contract_code' => '合同编号',
			'sign_date' => '合同签订日期',
			'start_date' => '合同开始日期',
			'end_date' => '合同结束日期',
			'responsible' => '负责人',
			'remark' => '合同备注',
			'month' => '合同月份',

			
			'wee_date' => '投放日期',
			'app_package' => '软件包名',
			'app_name' => '应用名称',
			'app_type' => '软件类型',
			'keyword' => '关键字',
			'advertising_id' => '广告位ID',
			'mid' => '客户编号ID'


		);
		$html = '<table>';
		$html .= '<tr><td>操作行为</td><td>'.$item['title'].'</td></tr>';
		$html .= '<tr><td>操作时间</td><td>'.date('Y-m-d h:i:s').'</td></tr>';
		$html .= '<tr><td>操作用户</td><td>'.$item['user'].'</td></tr>';
		if(!empty($item['contract_code'])) {
			$html .= '<tr><td>合同编号</td><td>'.$item['contract_code'].'</td></tr>';
		}
		
		if(!empty($item['app_name'])) {
			$html .= '<tr><td>合同软件</td><td>'.$item['app_name'].'</td></tr>';
		}
		
		if(isset($item['add'])) {
			$html .= '<tr><td>添加</td><td>'.$k.':'.$v['old'].'</td></tr>';
		}
		
		if(isset($item['del'])) {
			$html .= '<tr><td>删除</td><td>'.$k.':'.$v['old'].'</td></tr>';
		}
		
		foreach($item['change'] as $k => $v) {
			if(!isset($clm_name[$k])) {
				continue;
			}
			$html .= '<tr><td>修改['.$clm_name[$k].']</td><td>新值:'.$v['new'].'/旧值:'.$v['old'].'</td></tr>';
		}
		
		
		
		
		
		

		$html .= '</table>';
	
		//var_dump($html);


		//$path = dirname(__FILE__).'/mail.htm';
		
		//file_put_contents($path,$html);
		$where = array();
		if(strstr($action,'_app')){
			$where['aid'] = 1;
		} else {
			$where['aid'] = 2;
		}
		list($data) = $this->field(" * ")->where($where)->select();
		//$targets = explode(',',$data['target']);
		//$ccs = explode(',',$data['cc']);
		if($data['status'] != 1){
			return;
		}
		$emailmodel = D("Dev.Sendemail");
		$result = $emailmodel->realsend($data['target'],"rrrr",$item['title'],$html,$data['cc']);


	}

}
