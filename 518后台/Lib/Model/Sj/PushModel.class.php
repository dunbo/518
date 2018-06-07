<?php
class PushModel extends AdvModel {
	//调整表前缀
	protected $trueTableName = 'sj_sdk_push';
	protected $connect_id = 117;
    
	public function __construct()
	{
		//parent::__construct();
		$myConnect1 = C('DB_PUSH');
		$this -> addConnect($myConnect1, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	


	/**
	 * 添加主push信息
	 */
	private function addPush($data) {



		//必选字段列表
		$req_map = array(
			'platform',
			'start_time',
			'end_time',
		);

		foreach($req_map as $v) {
			if(!isset($data[$v])) {
				throw new Exception("缺少必要字段:".$v);
			}
		}

		if(!isset($data['create_time'])) {
			$data['create_time'] = time();
		}
		if(!isset($data['update_time'])) {
			$data['update_time'] = time();
		}

		if(empty($data['item_show_time_start'])) {
			$data['item_show_time_start'] = 0;
		}
		if(empty($data['item_show_time_end'])) {
			$data['item_show_time_end'] = 86399;
		}


		if(empty($data['channel'])) {
			$data['channel'] = 0;
		}



		return $this->table('sj_push')->add($data);

	}



	/**
	 * 修改主push信息
	 * 修改信息
	 */
	private function editPushById($id, $data) 
	{
		if(!isset($data['pro_status'])) {
			$data['pro_status'] = 0;
		}
		return $this->table('sj_push')->where(array('id' => $id))->save($data);
	}
	


	private function editItemById($id,$data) {
		
		if(empty($data['platform'])) {
			throw new Exception("缺少必要字段:platform");
		}
		
		$push_data = array();
		$push_field = $this->getPushTableField();
		$info_field = $this->getTableField('sj_push_info_'.$data['platform']);
		$self_data = array();



		foreach($data as $k => $v) {
			if(in_array($k,$push_field)) {
				$push_data[$k] = $v;
			} else if(in_array($k,$info_field)) {
				$self_data[$k] = $v;
			}
		}

		if(!empty($push_data)) {
			$this->editPushById($id,$push_data);
		}

		if(!empty($self_data)) {
			$this->table('sj_push_info_'.$data['platform'])->where(array('id' => $id))->save($self_data);
		}

	}

	
	/*编辑SDK数据 根据id获取编辑数据的信息展示
	*/
	public function getById($id) {
		$where=array(
			'push.id' => $id,
			'info.id' =>$id,
		);
		$where['_string'] = "push.id = info.id";
		return $this->table('sj_push push,sj_push_info_13 info') ->where($where)->find();
	}
	
	
	private function getTableField($table) {


		$result = $this->query('desc '.$table);
		$item = array();
		foreach($result as $v) {
			$item[] = $v['Field'];
		}

		return $item;

	}
	
	
	

	//主PUSH的数据库保存字段
	private function getPushTableField() 
	{
		return array('id','platform','is_repeat','create_time','update_time','start_time','end_time','status','pro_status','item_show_time_start','item_show_time_end','obj_uids');
	}
	
	/** 这是一个添加push的demo可根据实际情况修改或重构
		添加SDK数据
	 */
	public function addSdkPush($data) 
	{
		


		
		$push_data = array();
		$push_field = $this->getPushTableField();
		$self_data = array();
		foreach($data as $k => $v) {
			if(in_array($k,$push_field)) {
				$push_data[$k] = $v;
			} else {
				$self_data[$k] = $v;
			}
		}
		//设置推送类别(千万不能弄错)
		$push_data['platform'] = 13;
		$self_data['id'] = $this->addPush($push_data);
		$this->table("sj_push_info_13")->add($self_data);
		
		return $self_data['id'];
	}

	/** 这是一个修改push的demo可根据实际情况修改或重构
		修改SDK的push数据
	 */
	public function editSdkPush($id,$data) 
	{
		$push_data = array();
		$push_field = $this->getPushTableField();
		$push_field_new =array('item_icon_low','item_icon_high');;
		$self_data = array();
		foreach($data as $k => $v) {
			if(in_array($k,$push_field)) {
				$push_data[$k] = $v;
			} else {
				if(!in_array($k,$push_field_new)){
					$self_data[$k] = $v;	
				}
			}
		}
		$push_result = $this->editPushById($id,$push_data);
		if($push_result === false)
			return;
		else
		{	
			return $this->table("sj_push_info_13")->where(array('id' => $id))->save($self_data);	
		}
	}




	
	/** 这是一个添加push的demo可根据实际情况修改或重构
	 * 包更新推送
	 * 这以后都没用到
	 *
	 *
	 *
	 */
	public function addPckPush($data) {
	

		
		if(empty($data['package_name']) || empty($data['version_code'])) {
			throw new Exception('缺少必须字段');
		}
		
		if(!isset($data['start_time'])) {
			$data['start_time'] = time();
		}
		if(!isset($data['end_time'])) {
			$data['end_time'] = ($data['start_time'] + 86400);
		}

		//不同版本排重
		{
			$where = array('package_name' => $data['package_name'],'version_code' => $data['version_code']);

			$where['_string'] = "push.id = info.id";
			$obj_data = $this->table('sj_push push,sj_push_info_1 info') ->where($where)->find();


			//如果该推送已经存在
			if($obj_data) {
				$this->editItemById($obj_data['id'],$data);
				return $obj_data['id'];
			}
		}


		//有新版本到来
		{
			$where = array('package_name' => $data['package_name']);

			$where['_string'] = "push.id = info.id AND push.end_time >".time()." AND info.version_code < ".intval($data['version_code']);
			$obj_data = $this->table('sj_push push,sj_push_info_1 info') ->where($where)->find();

			//如果该推送已经存在
			if($obj_data) {

				$this->editItemById($obj_data['id'],array('status' => 0,'platform' => 1));

			}
		}

		
		$push_data = array();
		$push_field = $this->getPushTableField();
		$info_field = $this->getTableField("sj_push_info_1");

		$self_data = array();
		foreach($data as $k => $v) {
			if(in_array($k,$push_field)) {
				$push_data[$k] = $v;
			} else if(in_array($k,$info_field)) {
				$self_data[$k] = $v;
			}
		}
		//设置推送类别(千万不能弄错)
		$push_data['platform'] = 1;
		$self_data['id'] = $this->addPush($push_data);
		if (empty($self_data['id'])) {
			return;
		}







		$this->pckPushEmail($data);


		$result = $this->table("sj_push_info_1")->add($self_data);
		if(empty($result)) {
			permanentlog("sql_error.log",date('Y-m-d H:i:s')."---SQL建失败".$this->getLastSql());
		}
		return $self_data['id'];
		
	}

	/**
	 * 软件更新push邮件提醒
	 */
	private function pckPushEmail($data) {


		$html = '<table>';

		$html .= '<tr><td>软件名称</td><td>包名</td><td>版本</td><td>时间</td></tr>';
		$html .= '<tr><td>'.$data['app_name'].'</td><td>'.$data['package_name'].'</td><td>'.$data['version_name'].'</td><td>'.date('Y-m-d H:i:s').'</td></tr>';
		$html .= '</table>';

		$is_test = preg_match('#518test#',$_SERVER['HTTP_HOST']);

		$emailmodel = D("Dev.Sendemail");
		$title = "软件更新PUSH";
		if($is_test) {
			$title = "测试环境---------------".$title;
		}

		$result = $emailmodel->realsend('houwei@anzhi.com;linhongqing@anzhi.com;yangwei@anzhi.com;wangxiaoqi@anzhi.com;wangyongliang@anzhi.com',"rrrr",$title,$html,'');
	}




	/** 这是一个修改push的demo可根据实际情况修改或重构
	 * 修改包更新推送
	 *
	 *
	 *
	 *
	 */
	public function editPckPush($id,$data) {
	

		$push_data = array();
		$push_field = $this->getPushTableField();
		$self_data = array();
		foreach($data as $k => $v) {
			if(in_array($k,$push_field)) {
				$push_data[$k] = $v;
			} else {
				$self_data[$k] = $v;
			}
		}
		
		$self_data['id'] = $this->editPushById($id,$push_data);

		
		$this->table("sj_push_1")->where(array('id' => $id))->save($self_data);
		
	}
	


	/**
		添加老市场push
	 */
	public function addMarketOldPush($push_data) 
	{

		$push_data['platform'] = 0;
		
		if(empty($push_data['channel'])) {
			$push_data['channel'] = 1;
		}

		

		//print_r($push_data);
		//exit;

		return $this->addPush($push_data);
	}

	/** 
		修改老市场push
	 */
	public function editMarketOldPush($id,$push_data) 
	{
		//$push_data = array();

		return $this->editPushById($id,$push_data);

	}




	/**
		添加518 api push
	 */
	public function addApiPush($push_data) 
	{

		return $this->addPush($push_data);
	}

	/** 
		修改518 api push
	 */
	public function editApiPush($id,$push_data) 
	{
		//$push_data = array();

		return $this->editPushById($id,$push_data);

	}





}