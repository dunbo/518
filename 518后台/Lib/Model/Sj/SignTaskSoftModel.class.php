<?php

class SignTaskSoftModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'qd_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}

	/**
	 * 总数
	 */
	public function getcount($options)
	{
		$result = $this->table('qd_task_soft')->where($options)->count();
		return $result;
	}
	
	/**
	 * 任务软件验证
	 */
	public function task_soft_validate( $table )
	{
		$package	=	!empty($_POST['package']) ? trim($_POST['package']) : '';
		$aid		=	(Int)$_POST['aid'];
		$atp		=	(Int)$_POST['atp'];
		if( !$package ) {
			$return = array(
				'code'	=>	0,
				'msg'	=>	'包名不能为空'
			);
			return $return;		
		}
		$pkg_data = M('')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if( !$pkg_data ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	'包名不存在'
			);
			return $return;
		}
		$width = $height = 100;
// 		$date	=	date("Ym/d/");
// 		if($_FILES['pic_path']['tmp_name']) {
// 			$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
// 			if($pic_path[0] != $width || $pic_path[1] != $height){
// 				$return = array(
// 						'code'	=>	0,
// 						'msg'	=>	"分辨率图标大小不符合条件",
// 				);
// 				return $return;
// 			}
// 			$config['multi_config']['pic_path'] = array(
// 					'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
// 					'saveRule'	 =>	'getmsec',
// 					'img_p_size' =>	1024 * 200,
// 			);
// 		}
		$data = array(
			'package'	=>	$package,
			'softname'	=>	$pkg_data['softname'],
			'status'	=>	1,
		);
		$condtion = array(
				'package'	=>	$package,
				'status'	=>	array('in','1,2'),
		);
		if( $atp == 1 ) {
			$data['mid']		=	$aid;
			$condtion['mid']	=	$aid;
		}else {
			$data['did']		=	$aid;
			$condtion['did']	=	$aid;
		}
		if( $_POST['id'] ) {
			$condtion['id']		=	array('neq', $_POST['id']);
		}
		//检查是否重复添加
		$row = $this->table('qd_task_soft')->where($condtion)->find();
		if( !empty($row) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	'该软件已经添加过了'
			);
			return $return;
		}
		$time = time();
		if(!$_POST['id']){
			$data['create_tm']	=	$time;
		}		
		$return = array(
			'code'		=>	1,
			'data'		=>	$data,
		);
		return $return;
	}
}
