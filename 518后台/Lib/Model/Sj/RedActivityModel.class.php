<?php
/**
 * 红包活动
 * 定义 5、九宫格，6、天降红包雨，7、红包翻翻乐，8、红包叠叠乐
 */
class RedActivityModel extends Model {
	//九宫格验证
	public function jgg_add_do()
	{
		$name		=	trim($_POST['name']);
		$start_tm	=	strtotime($_POST['start_tm']);
		$end_tm		=	strtotime($_POST['end_tm']);
		$init_num	=	$_POST['init_num'];
		$intro		=	str_replace(array("\r\n","\n"),'', $_POST['intro']);
		$at_desc	=	trim($_POST['at_desc']);
		$time		=	time();
		if( !$name ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能为空",
			);
			return $return;
		}
		if( strlen($name) > 60 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称最多20个字",
			);
			return $return;
		}
		if( strlen($name) < 2 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能少于2个字",
			);
			return $return;
		}
		$where = array(
				'name'			=>	$name,
				'status'		=>	array('neq', 0),
		);
		if( $_POST['id'] ) {
			$where['id'] = array('neq', $_POST['id']);
		}
		$activity_info = $this->table('sj_activity')->where($where)->find();
		if( !empty($activity_info) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"无法添加成功，活动名称重复",
			);
			return $return;
		}
		if( !$start_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"开始时间不能为空",
			);
			return $return;
		}
		if( !$end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能为空",
			);
			return $return;
		}
		if( $start_tm >= $end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能小于等于开始时间",
			);
			return $return;
		}
		if( $_POST['id'] ) {
			$ex = $this->validate_time($_POST['id'], $start_tm, $end_tm);
			if( $ex ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"活动时间和红包任务时间冲突，请先修改红包任务时间",
				);
				return $return;
			}
		}
		if( !preg_match('/^\+?[1-9]\d*$/', $init_num) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"初始抽奖次数需要填写大于0的整数 ",
			);
			return $return;
		}
		if( !$at_desc ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"规则说明不能为空",
			);
			return $return;
		}
		$width = 480; $height = 154;
		$date	=	date("Ym/d/");
		if($_FILES['imgurl']['tmp_name']) {
			$pic_path = getimagesize($_FILES['imgurl']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
						'code'	=>	0,
						'msg'	=>	"分辨率图标大小不符合条件",
				);
				return $return;
			}
			if( !in_array($_FILES['imgurl']['type'], array('image/png','image/jpg','image/jpeg')) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请添加图片格式为：jpg，png的弹窗图片",
				);
				return $return;
			}
			$config['multi_config']['imgurl'] = array(
					'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
					'saveRule'	 =>	'getmsec',
					'img_p_size' =>	1024 * 200,
			);
		}
		$data = array(
				'name'				=>	$name,
				'start_tm'			=>	$start_tm,
				'end_tm'			=>	$end_tm,
				'url'				=>	$this->get_activity_url().'/lottery/red_package/index.php?cbm=1',
				'pre_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'end_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'red_init_num'		=>	$init_num,
				'intro'				=>	$intro,
				'status'			=>	2,//默认是待审核状态
				'activity_type'		=>	5,
				'activity_category'	=>	3,
				'red_chance_num'	=>	2,//抽奖机会默认为2次
				'red_at_desc'		=>	$at_desc,
		);
		if($_POST['id']){
			$data['last_refresh']	=	$time;
		}else{
			$data['last_refresh']	=	$time;
			$data['create_at']		=	$time;
		}
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
				'config'	=>	$config,
		);
		return $return;
	}
	
	//红包雨验证
	public function hby_add_do()
	{
		$name			=	trim($_POST['name']);
		$start_tm		=	strtotime($_POST['start_tm']);
		$end_tm			=	strtotime($_POST['end_tm']);
		$red_start_tm	=	$_POST['red_start_tm'];
		$red_end_tm		=	$_POST['red_end_tm'];
		$start_poke		=	(Int)$_POST['start_poke'];
		$end_poke		=	(Int)$_POST['end_poke'];
		$intro			=	str_replace(array("\r\n","\n"),'', $_POST['intro']);
		$at_desc		=	trim($_POST['at_desc']);
		$time			=	time();
		if( !$name ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能为空",
			);
			return $return;
		}
		if( strlen($name) > 60 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称最多20个字",
			);
			return $return;
		}
		if( strlen($name) < 2 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能少于2个字",
			);
			return $return;
		}
		$where = array(
				'name'			=>	$name,
				'status'		=>	array('neq', 0),
		);
		if( $_POST['id'] ) {
			$where['id'] = array('neq', $_POST['id']);
		}
		$activity_info = $this->table('sj_activity')->where($where)->find();
		if( !empty($activity_info) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"无法添加成功，活动名称重复",
			);
			return $return;
		}
		if( !$start_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"开始时间不能为空",
			);
			return $return;
		}
		if( !$end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能为空",
			);
			return $return;
		}
		if( $start_tm >= $end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能小于等于开始时间",
			);
			return $return;
		}
		if( !$red_start_tm || !$red_end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动时间段不能为空",
			);
			return $return;
		}
		if( strtotime($red_start_tm) >= strtotime($red_end_tm) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动时段后面的时间必须大于前面的时间",
			);
			return $return;
		}
		//如果在在一天之内
		if( date('Y-m-d', $start_tm) == date('Y-m-d', $end_tm) ){
			$sd_start_tm = date('Y-m-d', $start_tm)." {$red_start_tm}";
			$sd_start_tm = strtotime($sd_start_tm);
			$sd_end_tm = date('Y-m-d', $end_tm)." {$red_end_tm}";
			$sd_end_tm = strtotime($sd_end_tm);
			if( $sd_start_tm <= $start_tm || $sd_start_tm > $end_tm || $sd_end_tm <= $start_tm || $sd_end_tm > $end_tm ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"活动时段不在活动时间范围内",
				);
				return $return;
			}
		}
		
		if( $_POST['id'] ) {
			$ex = $this->validate_time($_POST['id'], $start_tm, $end_tm);
			if( $ex ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"活动时间和红包任务时间冲突，请先修改红包任务时间",
				);
				return $return;
			}
		}
		if($start_poke <= 0 || $end_poke <= 0) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"戳红包次数必须配置0以上的整数",
			);
			return $return;
		}
		
		if( $start_poke >= $end_poke ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"戳红包次数后面的数字必须大于前面的数字",
			);
			return $return;
		}
		if( !$at_desc ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"规则说明不能为空",
			);
			return $return;
		}
		if( $_POST['id'] ) {
			$have_where['_string'] = "start_tm <= {$end_tm} and end_tm >= {$start_tm} and status in(1,2) and activity_type = 6 and id != {$_POST['id']}";
		}else {
			$have_where['_string'] = "start_tm <= {$end_tm} and end_tm >= {$start_tm} and status in(1,2) and activity_type = 6";
		}
		$have_result = $this -> table('sj_activity') -> where($have_where) -> select();
		if( !empty($have_result) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"该时间段已配置红包雨,请修改活动时间",
			);
			return $return;
		}
		$width = 480; $height = 154;
		$date	=	date("Ym/d/");
		if($_FILES['imgurl']['tmp_name']) {
			$pic_path = getimagesize($_FILES['imgurl']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
						'code'	=>	0,
						'msg'	=>	"分辨率图标大小不符合条件",
				);
				return $return;
			}
			if( !in_array($_FILES['imgurl']['type'], array('image/png','image/jpg','image/jpeg')) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请添加图片格式为：jpg，png的弹窗图片",
				);
				return $return;
			}
			$config['multi_config']['imgurl'] = array(
					'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
					'saveRule'	 =>	'getmsec',
					'img_p_size' =>	1024 * 200,
			);
		}
		$data = array(
				'name'				=>	$name,
				'start_tm'			=>	$start_tm,
				'end_tm'			=>	$end_tm,
				'url'				=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'pre_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'end_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'red_start_tm'		=>	$red_start_tm,
				'red_end_tm'		=>	$red_end_tm,
				'red_start_poke'	=>	$start_poke,
				'red_end_poke'		=>	$end_poke,
				'intro'				=>	$intro,
				'status'			=>	2,//默认是待审核状态
				'activity_type'		=>	6,
				'activity_category'	=>	3,
				'red_at_desc'		=>	$at_desc,
		);
		if($_POST['id']) {
			$data['last_refresh']	=	$time;
		}else {
			$data['last_refresh']	=	$time;
			$data['create_at']		=	$time;
		}
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
				'config'	=>	$config,
		);
		return $return;
	}
	
	//翻翻乐验证
	public function ffl_add_do()
	{
		$name		=	trim($_POST['name']);
		$start_tm	=	strtotime($_POST['start_tm']);
		$end_tm		=	strtotime($_POST['end_tm']);
		$init_num	=	$_POST['init_num'];
		$intro		=	str_replace(array("\r\n","\n"),'', $_POST['intro']);
		$at_desc	=	trim($_POST['at_desc']);
		$time		=	time();
		if( !$name ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能为空",
			);
			return $return;
		}
		if( strlen($name) > 60 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称最多20个字",
			);
			return $return;
		}
		if( strlen($name) < 2 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能少于2个字",
			);
			return $return;
		}
		$where = array(
				'name'			=>	$name,
				'status'		=>	array('neq', 0),
		);
		if( $_POST['id'] ) {
			$where['id'] = array('neq', $_POST['id']);
		}
		$activity_info = $this->table('sj_activity')->where($where)->find();
		if( !empty($activity_info) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"无法添加成功，活动名称重复",
			);
			return $return;
		}
		if( !$start_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"开始时间不能为空",
			);
			return $return;
		}
		if( !$end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能为空",
			);
			return $return;
		}
		if( $start_tm >= $end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能小于等于开始时间",
			);
			return $return;
		}
		if( $_POST['id'] ) {
			$ex = $this->validate_time($_POST['id'], $start_tm, $end_tm);
			if( $ex ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"活动时间和红包任务时间冲突，请先修改红包任务时间",
				);
				return $return;
			}
		}
		if( !preg_match('/^\+?[1-9]\d*$/', $init_num) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"初始局数需要填写大于0的整数 ",
			);
			return $return;
		}
		if( !$at_desc ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"规则说明不能为空",
			);
			return $return;
		}
		$width = 480; $height = 154;
		$date	=	date("Ym/d/");
		if($_FILES['imgurl']['tmp_name']) {
			$pic_path = getimagesize($_FILES['imgurl']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
						'code'	=>	0,
						'msg'	=>	"分辨率图标大小不符合条件",
				);
				return $return;
			}
			if( !in_array($_FILES['imgurl']['type'], array('image/png','image/jpg','image/jpeg')) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请添加图片格式为：jpg，png的弹窗图片",
				);
				return $return;
			}
			$config['multi_config']['imgurl'] = array(
					'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
					'saveRule'	 =>	'getmsec',
					'img_p_size' =>	1024 * 200,
			);
		}
		$data = array(
				'name'				=>	$name,
				'start_tm'			=>	$start_tm,
				'url'				=>	$this->get_activity_url().'/lottery/red_ffl/index.php?cbm=1',
				'pre_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'end_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'end_tm'			=>	$end_tm,
				'red_init_num'		=>	$init_num,
				'intro'				=>	$intro,
				'status'			=>	2,//默认是待审核状态
				'activity_type'		=>	7,
				'activity_category'	=>	3,
				'red_at_desc'		=>	$at_desc,
		);
		if($_POST['id']){
			$data['last_refresh']	=	$time;
		}else{
			$data['last_refresh']	=	$time;
			$data['create_at']		=	$time;
		}
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
				'config'	=>	$config,
		);
		return $return;
	}
	
	//叠叠乐验证
	public function ddl_add_do()
	{
		$name		=	trim($_POST['name']);
		$start_tm	=	strtotime($_POST['start_tm']);
		$end_tm		=	strtotime($_POST['end_tm']);
		//$desc		=	str_replace(array("\r\n","\n"),'', $_POST['desc']);
		$intro		=	str_replace(array("\r\n","\n"),'', $_POST['intro']);
		$time		=	time();
		if( !$name ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能为空",
			);
			return $return;
		}
		if( strlen($name) > 60 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称最多20个字",
			);
			return $return;
		}
		$where = array(
				'name'			=>	$name,
				'status'		=>	array('neq', 0),
		);
		if( $_POST['id'] ) {
			$where['id'] = array('neq', $_POST['id']);
		}
		$activity_info = $this->table('sj_activity')->where($where)->find();
		if( !empty($activity_info) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"无法添加成功，活动名称重复",
			);
			return $return;
		}
		if( strlen($name) < 2 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"活动名称不能少于2个字",
			);
			return $return;
		}
		if( !$start_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"开始时间不能为空",
			);
			return $return;
		}
		if( !$end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能为空",
			);
			return $return;
		}
		if( $start_tm >= $end_tm ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能小于等于开始时间",
			);
			return $return;
		}
		if( $_POST['id'] ) {
			$ex = $this->validate_time($_POST['id'], $start_tm, $end_tm);
			if( $ex ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"活动时间和红包任务时间冲突，请先修改红包任务时间",
				);
				return $return;
			}
		}
		if(!$_POST['id'] && empty($_FILES['imgurl']['tmp_name'])){
			$return = array(
					'code'	=>	0,
					'msg'	=>	"请上传宣传图",
			);
			return $return;
		}
		$width = 480; $height = 154;
		$date	=	date("Ym/d/");
		if($_FILES['imgurl']['tmp_name']) {
			$pic_path = getimagesize($_FILES['imgurl']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
						'code'	=>	0,
						'msg'	=>	"分辨率图标大小不符合条件",
				);
				return $return;
			}
			if( !in_array($_FILES['imgurl']['type'], array('image/png','image/jpg','image/jpeg')) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请添加图片格式为：jpg，png的弹窗图片",
				);
				return $return;
			}
			$config['multi_config']['imgurl'] = array(
					'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
					'saveRule'	 =>	'getmsec',
					'img_p_size' =>	1024 * 200,
			);
		}
		$data = array(
				'name'				=>	$name,
				'start_tm'			=>	$start_tm,
				'end_tm'			=>	$end_tm,
				//'red_desc'			=>	$desc,
				'intro'				=>	$intro,
				'status'			=>	2,//默认是待审核状态
				'activity_type'		=>	8,
				'activity_category'	=>	3,
				'pre_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
				'end_url'			=>	$this->get_activity_url().'/lottery/red_ffl/currency.php?cbm=1',
		);
		if($_POST['id']){
			$data['last_refresh']	=	$time;
		}else{
			$data['last_refresh']	=	$time;
			$data['create_at']		=	$time;
		}
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
				'config'	=>	$config,
		);
		return $return;
	}
	
	//编辑活动时间验证活动时间是否在红包任务内
	public function validate_time($aid, $start_tm, $end_tm)
	{
		$where = array(
			'aid'		=>	$aid,
			'status'	=>	1,
			'task_id'	=>	array('gt', 0),
		);
		$prize_list = D('Sj.Sign')->table('sign_prize')->where($where)->select();
		if( !empty($prize_list) ) {
			//检查是红包任务时间
			foreach ($prize_list as $key => $val) {
				$task_info = $this->get_red_soft_list(4, $val['package'], $val['task_id']);
				if( empty($task_info) ){
					continue;
				}
				$task_info['start_tm']	=	strtotime($task_info['start_tm']);
				$task_info['end_tm']	=	strtotime($task_info['end_tm']);
				if( $start_tm < $task_info['start_tm'] || $start_tm > $task_info['end_tm'] || $end_tm < $task_info['start_tm'] ||$end_tm > $task_info['end_tm'] ) {
					return true;
				}
			}
			return false;
		}else {
			return false;
		}
	}
	
	/**
	 * 添加活动入库
	 * @param $data
	 * @param $at	定义 5、九宫格，6、天降红包雨，7、红包翻翻乐，8、红包叠叠乐
	 */
	public function red_activity_add($data, $at=0)
	{
		if( empty($data) ) {
			return false;
		}
		if( $at == 5 || $at == 7 ) {
			$timestamp = time();
			//九宫格和红包翻翻乐的情况 需要生成页面id 更新到活动表中的activity_page_id
			$page_data = array(
				'ap_name'		=>	'红包活动',
				'ap_type'		=>	1,
				'activate_type'	=>	13,
				'status'		=>	1,
				'ap_ctm'		=>	$timestamp,
			);
			$page_id = $this->table('sj_activity_page')->add($page_data);
			
			$category_data = array(
				'active_id'		=>	$page_id,
				'category_name'	=>	'红包活动',
				'create_tm'		=>	$timestamp,
				'update_tm'		=>	$timestamp,
				'status'		=>	1,
			);
			$category_id = $this->table('sj_actives_category')->add($category_data);
			
			if( $page_id ) {
				$data['activity_page_id'] = $page_id;
				$aid = $this->table('sj_activity')->add($data);
				//奖品表中默认一条为谢谢参数
				if( $aid ) {
					
					for($i = 1; $i< 8; $i++ ) {
						$prize_data = array(
								'level'		=>	$i,
								'name'		=>	'谢谢参与',
								'aid'		=>	$aid,
								'type'		=>	7,
								'condition'	=>	0,
								'add_tm'	=>	$timestamp,
								'update_tm'	=>	$timestamp,
						);
						D('Sj.Sign')->table('sign_prize')->add($prize_data);
					}
				}
				return $aid;
			}else {
				return false;
			}
		}else {
			$res = $this->table('sj_activity')->add($data);
			return $res;
		}
	}
	
	//红包活动奖品验证
	public function red_award_add_do()
	{
		$at				=	$_POST['at'];
		$aid			=	$_POST['aid'] ? (Int)$_POST['aid'] : 0;
		$type			=	$_POST['type'] ? (Int)$_POST['type'] : 0;//6红包 7未中奖
		$red_id			=	$_POST['red_id'] ? (Int)$_POST['red_id'] : 0;
		$d_redid		=	$_POST['d_redid'] ? (Int)$_POST['d_redid'] : 0;
		$task_id		=	$_POST['task_id'] ? (Int)$_POST['task_id'] : 0;
		$condition		=	$_POST['condition'] ? (Int)$_POST['condition'] : 1;
		$pkg			=	$_POST['pkg'] ? trim($_POST['pkg']) : '';
		$level			=	$_POST['level'] ? (Int)$_POST['level'] : 0;
		$time			=	time();
		if( in_array($at, array(5,6,7)) ) {
			if( $type == 6 ) {
				$data = array(
						'aid'		=>	$aid,
						'name'		=>	'红包',
						'type'		=>	$type,
						'condition'	=>	$condition,
						//'package'	=>	'',
						//'softname'	=>	'',
				);
			}else {
				$data = array(
						'aid'		=>	$aid,
						'name'		=>	'谢谢参与',
						'type'		=>	$type,
						'task_id'	=>	0,
						'task_type'	=>	'',
						'red_id'	=>	0,
						'condition'	=>	0,
						'd_redid'	=>	0,
						'num'		=>	0,
						'prize_num'	=>	0,
						'package'	=>	'',
						'softname'	=>	'',
				);
			}
			if($type == 6 && $condition == 1 && !$red_id ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"未配置红包",
				);
				return $return;
			}
			if($type == 6 && $condition == 2 && !$task_id ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"未配置红包任务",
				);
				return $return;
			}
		}elseif( $at == 8 ) {
			$data = array(
					'aid'		=>	$aid,
					'name'		=>	'红包',
					'type'		=>	$type,
			);
			if( !$d_redid ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"未配置红包",
				);
				return $return;
			}
			if( !$task_id ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"未配置红包任务",
				);
				return $return;
			}
		}else {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"参数有误",
			);
			return $return;
		}
		
		//添加红包活动为九宫格活翻翻的时候level不能重复添加且不能大于8不能小于1
		if( !$_POST['id'] && in_array($at, array(5,7)) ) {
			if( $level < 1 || $level > 8 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"参数有误",
				);
				return $return;
			}
			$where_go = array(
				'aid'		=>	$aid,
				'level'		=>	$level,
				'status'	=>	1,
			);
			$repeat =	D('Sj.Sign')->table('sign_prize')->where($where_go)->find();
			if( !empty($repeat) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"奖品位置不能重复添加",
				);
				return $return;
			}
		}
		$activity		=	$this->table('sj_activity')->field('name')->where(array('id'=>$aid))->find();
		$bind_ext_data	=	array();
		if( $at == 5 ) {
			$bind_ext_data['name'] = '九宫格';
		}elseif( $at == 6 ) {
			$bind_ext_data['name'] = '红包雨';
		}elseif( $at == 7 ) {
			$bind_ext_data['name'] = '翻翻乐';
		}elseif( $at == 8 ) {
			$bind_ext_data['name'] = '叠叠乐';
		}
		//奖品类型为红包时
		if( $type == 6 ) {
			$prize_info = array();
			if( $_POST['id'] ) {
				$prize_info = D('Sj.Sign')->table('sign_prize')->where(array('id'=>$_POST['id']))->find();
			}
			if( $at == 8 ) {
				//任务
				if( $prize_info['task_id'] != $task_id ) {
					$task_info = $this->get_red_soft_list(4,$pkg,$task_id);
					//获取活动的开始结束时间
					$activity = D('Sj.RedActivity')->table('sj_activity')->field('id,start_tm,end_tm')->where(array('id'=>$aid))->find();
					$task_info['start_tm']	=	strtotime($task_info['start_tm']);
					$task_info['end_tm']	=	strtotime($task_info['end_tm']);
					if( $activity['start_tm'] < $task_info['start_tm'] || $activity['start_tm'] > $task_info['end_tm'] || $activity['end_tm'] < $task_info['start_tm'] || $activity['end_tm'] > $task_info['end_tm'] ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"活动时间不在任务时间内",
						);
						return $return;
					}
					$data['task_id']	=	$task_id;
					$data['task_type']	=	$task_info['task_type'];
					$data['red_id']		=	$task_info['red_id'];
					$data['package']	=	$task_info['package'];
					$data['softname']	=	$task_info['softname'];
					$red_package_info = $this->get_red_package_info($task_info['red_id']);
					if( !empty($red_package_info) ) {
						$data['num']		=	$red_package_info[0]['totalnum'];
						$data['prize_num']	=	$red_package_info[0]['totalnum'];
					}else {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"未获取到软件关联的红包",
						);
						return $return;
					}
					if( $red_package_info[0]['givetype'] !=1 ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"任务关联的红包发放类型必须为一次性发放",
						);
						return $return;
					}
					//解除触绑定
					if( $prize_info['red_id'] ) {
						//解除绑定
						$bind_info = $this->bind_red_pagckage(4, $aid, $prize_info['red_id'], $prize_info['red_id'],$prize_info['task_id']);
						if( $bind_info['STATUS'] != 1 ) {
							$return = array(
									'code'	=>	0,
									'msg'	=>	$bind_info['MSG'],
							);
							return $return;
						}
					}
				}
				//红包
				if( $prize_info['d_redid'] != $d_redid ) {
					$red_package_info = $this->get_red_package_info($d_redid);
					if( empty($red_package_info) ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"未获到红包信息",
						);
						return $return;
					}
					if( $red_package_info[0]['givetype'] !=1 ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"红包发放类型必须为一次性发放",
						);
						return $return;
					}
					$data['d_redid']	=	$d_redid;
					//解除触绑定
					if( $prize_info['red_id'] ) {
						//解除绑定
						$bind_info = $this->bind_red_pagckage(4, $aid, $prize_info['d_redid'], $prize_info['d_redid'],"");
						if( $bind_info['STATUS'] != 1 ) {
							$return = array(
									'code'	=>	0,
									'msg'	=>	$bind_info['MSG'],
							);
							return $return;
						}
					}
				}
				//执行绑定
				if( $prize_info['d_redid'] != $d_redid ) {
					//绑定新的红包
					$bind_info = $this->bind_red_pagckage(4, $aid, $data['d_redid'],'','', $bind_ext_data);
					if( $bind_info['STATUS'] != 1 ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	$bind_info['MSG'],
						);
						return $return;
					}
				}
				if( $prize_info['task_id'] != $task_id ) {
					//绑定新的红包
					//$bind_ext_data['name']	=	$bind_ext_data['name'].$task_info['taskname'];
					$bind_info = $this->bind_red_pagckage(4, $aid, $data['red_id'],'',$data['task_id'], $bind_ext_data);
					if( $bind_info['STATUS'] != 1 ) {
						$this->bind_red_pagckage(4, $aid, $data['d_redid'], $data['d_redid'], '');
						$return = array(
								'code'	=>	0,
								'msg'	=>	$bind_info['MSG'],
						);
						return $return;
					}
				}
			}
			
			if($condition == 1 && $at != 8) {
				if( $prize_info['red_id'] != $red_id ) {
					$red_package_info = $this->get_red_package_info($red_id);
					if( empty($red_package_info) ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"未获取到红包信息",
						);
						return $return;
					}
					if( $red_package_info[0]['givetype'] !=1 ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"红包发放类型必须为一次性发放",
						);
						return $return;
					}
					$data['red_id']		=	$red_id;
					$data['task_id']	=	0;
					$data['task_type']	=	'';
					$data['num']		=	$red_package_info[0]['totalnum'];
					$data['prize_num']	=	$red_package_info[0]['totalnum'];
					//解除触绑定
					if( $prize_info['red_id'] ) {
						//解除绑定
						$bind_info = $this->bind_red_pagckage(4, $aid, $prize_info['red_id'], $prize_info['red_id'],$prize_info['task_id']);
						if( $bind_info['STATUS'] != 1 ) {
							$return = array(
									'code'	=>	0,
									'msg'	=>	$bind_info['MSG'],
							);
							return $return;
						}
					}
					//绑定新的红包
					$bind_info = $this->bind_red_pagckage(4, $aid, $data['red_id'],'',$data['task_id'], $bind_ext_data);
					if( $bind_info['STATUS'] != 1 ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	$bind_info['MSG'],
						);
						return $return;
					}
				}
			}elseif( $condition == 2 && $at != 8 ) {
				if( $prize_info['task_id'] != $task_id ) {
					$task_info = $this->get_red_soft_list(4,$pkg,$task_id);
					//获取活动的开始结束时间
					$activity = D('Sj.RedActivity')->table('sj_activity')->field('id,start_tm,end_tm')->where(array('id'=>$aid))->find();
					$task_info['start_tm']	=	strtotime($task_info['start_tm']);
					$task_info['end_tm']	=	strtotime($task_info['end_tm']);
					if( $activity['start_tm'] < $task_info['start_tm'] || $activity['start_tm'] > $task_info['end_tm'] || $activity['end_tm'] < $task_info['start_tm'] || $activity['end_tm'] > $task_info['end_tm'] ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"活动时间不在任务时间内",
						);
						return $return;
					}
					$data['task_id']	=	$task_id;
					$data['red_id']		=	$task_info['red_id'];
					$data['task_type']	=	$task_info['task_type'];
					$data['package']	=	$task_info['package'];
					$data['softname']	=	$task_info['softname'];
					$red_package_info = $this->get_red_package_info($task_info['red_id']);
					if( !empty($red_package_info) ) {
						$data['num']		=	$red_package_info[0]['totalnum'];
						$data['prize_num']	=	$red_package_info[0]['totalnum'];
					}else {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"未获取到软件关联的红包",
						);
						return $return;
					}
					if( $red_package_info[0]['givetype'] !=1 ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"任务关联的红包发放类型必须为一次性发放",
						);
						return $return;
					}
					//解除触绑定
					if( $prize_info['red_id'] ) {
						//解除绑定
						$bind_info = $this->bind_red_pagckage(4, $aid, $prize_info['red_id'], $prize_info['red_id'],$prize_info['task_id']);
						if( $bind_info['STATUS'] != 1 ) {
							$return = array(
									'code'	=>	0,
									'msg'	=>	$bind_info['MSG'],
							);
							return $return;
						}
					}
					//绑定新的任务红包
					//$bind_ext_data['name']	=	$bind_ext_data['name'].$task_info['taskname'];
					$bind_info = $this->bind_red_pagckage(4, $aid, $data['red_id'],'',$data['task_id'],$bind_ext_data);
					if( $bind_info['STATUS'] != 1 ) {
							$return = array(
									'code'	=>	0,
									'msg'	=>	$bind_info['MSG'],
							);
							return $return;
					}
				}
			}
		}else {
			//选择未中奖解除已绑定的红包
			if($_POST['id']) {
				$prize_info = D('Sj.Sign')->table('sign_prize')->where(array('id'=>$_POST['id']))->find();
				if($prize_info['type'] == 6 && $prize_info['red_id'] ){
					$bind_info = $this->bind_red_pagckage(4, $aid, $prize_info['red_id'], $prize_info['red_id']);
					if( $bind_info['STATUS'] != 1 ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	$bind_info['MSG'],
						);
						return $return;
					}
				}
			}
		}
		if($_POST['id']) {
			$data['update_tm']	=	$time;
		}else {
			$data['level']		=	$level;
			$data['update_tm']	=	$time;
			$data['add_tm']		=	$time;
		}
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
		);
		return $return;
	}
	
	
	/**
	 * 抽奖软件配置验证
	 */
	public function soft_validate( $table )
	{
		$package	=	!empty($_POST['package']) ? trim($_POST['package']) : '';
		$pid		=	(Int)$_POST['pid'];//活动页面id
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
		$data = array(
				'page_id'	=>	$pid,
				'package'	=>	$package,
				'soft_name'	=>	$pkg_data['softname'],
				'status'	=>	1,
		);
		//根据活动页面id寻找活动页面分类
		$category = $this->table('sj_actives_category')->where(array('active_id'=>$pid))->find();
		if($category) {
			$data['category_id'] = $category['id'];
		}
		$condtion = array(
				'page_id'	=>	$pid,
				'package'	=>	$package,
				'status'	=>	array('in','1,2'),
		);
		if( $_POST['id'] ) {
			$condtion['id']		=	array('neq', $_POST['id']);
		}
		//检查是否重复添加
		$row = $this->table('sj_actives_soft')->where($condtion)->find();
		if( !empty($row) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	'该软件已经添加过了'
			);
			return $return;
		}
		$time = time();
		if(!$_POST['id']){
			$data['update_tm']	=	$time;
			$data['create_tm']	=	$time;
		}else {
			$data['update_tm']	=	$time;
		}
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
		);
		return $return;
	}
	
	//获取红包活动列表 
	public function get_red_pagckage_list($type,$start_tm,$end_tm)
	{
		//TYPE 1 普通任务,2 成长任务,3 签到抽奖, 4 红包活动,5 桌面红包
		$val = array(
				'KEY'	=>	"REDPACK_LIST",
				'TYPE'	=>	$type,
				'VR'	=>	1,
				'START'	=>	$start_tm,
				'END'	=>	$end_tm,
		);
		return $this->http_get_red_package($val,'red_list.log');
	}
	
	//获取红包详情  key为红包id
	public function get_red_package_info_ext($red_id_arr)
	{
		$red_list_info = $this->get_red_package_info(array_unique($red_id_arr));
		$red_list = array();
		foreach ($red_list_info as $key => $val) {
			$red_list[$val['id']] = array(
					'pname'		=>	$val['pname'],
					'totalmon'	=>	$val['totalmon'],
					'totalnum'	=>	$val['totalnum'],
					'minrand'	=>	$val['minrand'],
					'maxrand'	=>	$val['maxrand'],
			);
		}
		return $red_list;
	}
	
	//获取红包详情
	public function get_red_package_info($red_id)
	{
		$val = array(
				'KEY'		=>	'REDPACK_INFO',
				'PACKID'	=>	is_array($red_id)?$red_id:array($red_id),
				'VR'		=>	1,
		);
		return $this->http_get_red_package($val,'red_info.log');
	}
	
	//绑定红包 
	public function bind_red_pagckage($type, $aid, $red_id, $old_redid='', $task_id='', $bind_ext_data=array() )
	{
		//TYPE 1 普通任务,2 成长任务,3 签到抽奖, 4 红包活动,5 桌面红包
		$val = array(
				'KEY'		=>	'BIND_REDPACK_518',
				'TYPE'		=>	$type,
				'VR'		=>	1,
				'PACKID'	=>	$red_id,
				'OLD_REDID'	=>	$old_redid,
				'ACTTYPE'	=>	1,
				'ACTIVEID'	=>	$aid,
				'TASKID'	=>	$task_id,
				'FROM'		=>	1,
				'DESKTOP'	=>	0,
				'NOTE'		=>	!empty($bind_ext_data['name'])?$bind_ext_data['name']:'',
				'OPENTYPE'	=>	3,
				//'PACKAGE'	=>	'',
				//'START'	=>	0,
				//'END'		=>	0,
			);
		if( $type == 1 ) {
			$val['DESKTOP']	= 1;
		}
		return $this->http_get_red_package($val,'red_bind.log', 1);
	}
	
	/**
	 * 获取红包接口
	 * @param $val
	 * @param string $log 每日签到红包：'sign.log'，红包活动：'red_package.log'
	 * @param int $oper 0 红包列表和红包详情  1绑定红
	 * @return array
	 */
	function http_get_red_package($val, $log='red_package.log', $oper = 0)
	{
		if( C('is_test') == 1 ) {
			$url = "http://api.test.anzhi.com/goserv.php?";
		}else {
			$url = "http://dev.gomarket.goapk.com/goserv.php?";
		}
		$result = httpGetInfo($url, json_encode($val), $log);
		$arr = json_decode($result, true);
		if( $oper ) {
			return $arr; //STATUS:1成功，2失败  MSG：原因
		}else {
			return $arr['DATA'];
		}
	}
	
	/**
	 * 获取红包任务
	 * @param $val
	 * @param string $log 每日签到红包：'sign.log'，红包活动：'red_package.log'
	 * @param int $oper 0 红包列表和红包详情  1绑定红
	 * @return array
	 */
	function http_get_task($val, $log='red_task.log')
	{
		if( C('is_test') == 1 ) {
			$url = "http://dev.heifer.anzhi.com/reSoftTask/getTasksByPackageAndcategory";
		}else {
			$url = "http://heifer.anzhi.com/reSoftTask/getTasksByPackageAndcategory";
		}
		$result = httpGetInfo($url, $val, $log);
		$arr = json_decode($result, true);
		return $arr['data'];
	}
	
	//活动地址
	function get_activity_url()
	{
		if( C('is_test') == 1 ) {
			$url = "http://m.test.anzhi.com";
		}else {
			$url = "http://promotion.anzhi.com";
		}
		return $url;
	}
	
	/**
	 * 获取红包任务
	 * $category 任务类型 1普通软件任务（桌面红包） 3 签到抽奖 4 红包活动
	 * $pkg 包名
	 * $task_id 0表获取任务列表  传任务task_id 获取任务详情
	 */
	function get_red_soft_list($category, $pkg, $task_id=0)
	{
		$val = array(
			'category'		=>	$category,
			'packageName'	=>	$pkg,
		);
		$data = $this->http_get_task($val);
		if( !empty($data) ) {
			if( $task_id ) {
				$task_info = array();
				foreach ( $data as $key => $val ) {
					if( $task_id == $val['id'] ) {
						$task_info['task_id']			=	$val['id'];
						$task_info['red_id']			=	$val['reId'];
						$task_info['task_type']			=	$val['subTypeStr'];
						$task_info['start_tm']			=	date('Y-m-d H:i:s',$val['startTime']);
						$task_info['end_tm']			=	date('Y-m-d H:i:s', $val['endTime']);
						$task_info['package']			=	$val['packageName'];
						$task_info['softname']			=	$val['softName'];
						$task_info['taskname']			=	$val['taskName'];
						$task_info['taskname']			=	$val['taskName'];
						$task_info['task_constraint']	=	$val['subTaskConstraint'];
					}
				}
				return $task_info;
			}else {
				$task_list = array();
				foreach ( $data as $key => $val ) {
					$task_list[$key]['task_id']			=	$val['id'];
					$task_list[$key]['task_type']		=	$val['subTypeStr'];
					$task_list[$key]['red_id']			=	$val['reId'];
					$task_list[$key]['start_tm']		=	date('Y-m-d H:i:s',$val['startTime']);
					$task_list[$key]['end_tm']			=	date('Y-m-d H:i:s', $val['endTime']);
					$task_list[$key]['package']			=	$val['packageName'];
					$task_list[$key]['softname']		=	$val['softName'];
					$task_list[$key]['task_constraint']	=	$val['subTaskConstraint'];
				}
				return $task_list;
			}
		}else {
			return false;
		}
	}
	
}