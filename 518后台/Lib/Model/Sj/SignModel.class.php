<?php
class SignModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'qd_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}

	/**
	 * 列表
	 */
	public function get_sign_month_list($options)
	{
		$result = $this->where($options)->order("id desc")->select();
		return $result;
	}
	/**
	 * 总数
	 */
	public function getcount($options)
	{
		$result = $this->where($options)->count();
		return $result;
	}
	
	/**
	 * 签到月份验证
	 */
	public function sign_month_validate()
	{
		$year	=	!empty($_POST['year']) ? (Int)trim($_POST['year']) : '';
		$month	=	!empty($_POST['month']) ? trim($_POST['month']) : '';
		if( !$year ) {
			$return = array(
				'code'	=>	0,
				'msg'	=>	'请选择年份'
			);
			return $return;		
		}elseif( !preg_match( '/^\d{4}$/s', $year) ) {
			$return = array(
				'code'	=>	0,
				'msg'	=>	'请输入正确的年份'
			);
			return $return;
		}elseif( !$month  ) {
			$return = array(
				'code'	=>	0,
				'msg'	=>	'请选择月份'
			);
			return $return;
		}elseif( !preg_match( '/^(0?[[1-9]|1[0-2])$/', $month ) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	'请输入正确的月份'
			);
			return $return;
		}
		//检查只能配置当前月之后的任意月份
// 		$cur_month_stamp =  strtotime(date('Y-m', time())); //当前月的时间戳
// 		$time_str = $year.'-'.$month;
// 		$edit_time = strtotime($time_str); ///用户提交过来的时间戳
// 		if( $edit_time <= $cur_month_stamp ) {
// 			$return = array(
// 					'code'	=>	0,
// 					'msg'	=>	'只能添加当前月之后的月份'
// 			);
// 			return $return;
// 		}
		//检查不能重复添加
		if($_POST['id']){
			$where['id']		=	array('exp',"!={$_POST['id']}");
		}
		$where['year']		=	$year;
		$where['month']		=	$month;
		$where['status']	=	array('neq', 0);
		$rows = $this->table('qd_sign_month')->where($where)->find();
		if( !empty($rows) ) {
			$return = array(
				'code'	=>	0,
				'msg'	=>	'月份已存在，请勿重复添加',
			);
			return $return;
		}
		
		$data = array(
			'year'	=>	$year,
			'month'	=>	$month,
		);
		$timestamp	=	time();
		if($_POST['id']) {
			$data['id']			=	$_POST['id'];
			$data['update_tm']	=	$timestamp;
		}else {
			$data['create_tm']	=	$timestamp;
			$data['update_tm']	=	$timestamp;
		}
		
		$return = array(
				'code'	=>	1,
				'data'	=>	$data
		);
		return $return;
	}
	
	//新增月份
	public function add_sign_month( $data )
	{
		$timestamp	=	time();
 		$data['status']		=	2; //新增为待审核状态
 		$insertId	=	$this->table('qd_sign_month')->add( $data );
		//添加月份的同时生成对应的日期天数
		if( $insertId ) {
			//插入这个月下所有的天
			$year	=	$data['year'];
			$month	=	$data['month'];	
			$days	=	date("t",strtotime($year.'-'.$month));
			$values	=	'';
			if( $days ) {
				for ($i=1; $i<=$days; $i++) {
					$values .= "({$i},{$insertId},0,0,'',0,0,'',{$timestamp},{$timestamp}),";
				}
				$sql = "insert into qd_sign_days(`day`,`mid`,`type`,`condition`,`package`,`task_id`, `redid`,`pic_path`,`update_tm`,`create_tm`) values".trim($values,',');
				$res = $this->execute($sql);
				//插入这个月下连续签到默认配置
				$values_continuity  = "({$insertId},7,10000,1,5,0,{$timestamp}),";
				$values_continuity .= "({$insertId},15,5000,1,5,0,{$timestamp}),";
				$values_continuity .= "({$insertId},25,2000,1,5,0,{$timestamp})";
				$sql_continuity = "insert into qd_sign_continuity(`mid`,`count`,`base_num`,`random_start`,`random_end`,`status`,`create_tm`) values".$values_continuity;
				$res = $this->execute($sql_continuity);
				//插入这个月下补签卡默认配置
				$values_mend = "({$insertId},1000,1,0,1,0,{$timestamp}),";
				$values_mend .= "({$insertId},0,0,3,0,1,{$timestamp})";
				$sql_mend = "insert into qd_sign_mend(`mid`,`price`,`buy_num`,`task_num`,`status`,`type`,`create_tm`) values".$values_mend;
				$res = $this->execute($sql_mend);
				return $res;
 			}
		}else {
			return false; 
		}
	}
	
	public function edit_sign_month($data)
	{
		$result = $this->table('qd_sign_month')->where(array('id' => $data['id']))->save($data);
		return $result;
	}
	
	public function delete_sign_month($id)
	{
		$result = $this->where(array('id' => $id))->save(array('status' => 0));
		
		return $result;
	}
	
}
