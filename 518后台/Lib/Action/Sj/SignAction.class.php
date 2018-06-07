<?php
class SignAction extends CommonAction{
	
	function month_list()
	{
		//列表类型：false待审核列表 true通过列表
		$list_type = !empty($_GET['list_type']) ? $_GET['list_type'] : 0;
		if( !$list_type ) {
			$where = array(
				'status' => 2,//待审核列表
			);
		}else {
			$where = array(
				'status' =>	array('in','1,3'),//通过列表
			);
		}
		//获取签到状态
		$remind = D('Sj.RedActivity')->table('pu_config')->where(array('config_type'=>'SIGN_REMIND'))->find();
		
		$model	=	D('Sj.Sign');
		$count	=	$model->getcount();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$order	=	' year desc, month desc';
		$list	=	$model->table('qd_sign_month') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		$timestamp	=	strtotime(date('Y-m', time()));
		
		$this->assign('timestamp', $timestamp);
		$this->assign('remind', $remind);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->assign('list_type', $list_type);
		$this->display();	
	}
	
	//添加月份
	function month_add()
	{
		if (!empty($_POST)) {
			//验证
			$model	=	D('Sj.Sign');
			$res	=	$model->sign_month_validate();
			if( $res['code'] == 0 ) {
				$this->error($res['msg']);
			}
			$result = $model->add_sign_month($res['data']);
			if( $result ) {
				$this -> writelog("已添加id为{$result}的每日签到活动",'qd_sign_month',$result,__ACTION__ ,'','add');
				$this->success('添加成功');
			}else {
				$this->error('添加失败');
			}
		}else {
			$time		=	time();
			$cur_year	=	date('Y', $time);
			$cur_month	=	(Int)date('m', $time);
			$this->assign('cur_year', $cur_year);
			$this->assign('cur_month', $cur_month);
			$this->display();
		}
	}
	
	//编辑月份
	function month_edit()
	{
		$model	=	D('Sj.Sign');
		if (!empty($_POST)) {
			//验证
			$res	=	$model->sign_month_validate();
			if( $res['code'] == 0 ) {
				$this->error($res['msg']);
			}
			$id		=	$_POST['id'];
			$year	=	$_POST['year'];
			$month	=	$_POST['month'];
			$where	=	array('id'=>$id);
			$table	=	'qd_sign_month';
			$time	=	time();
			$rows = $model->table($table)->where($where)->find();
			
			$new_date = $year.'-'.$month;
			$cur_date = $rows['year'].'-'.$rows['month'];
			
			$new_days = date("t",strtotime($new_date));
			$cur_days = date("t",strtotime($cur_date));
			
			if( $new_date != $cur_date ) {
				//解绑红包任务类型红包
				$where_days = array('mid'=>$id,'type'=>1);
				$days_list = $model->table('qd_sign_days')->where($where_days)->select();
				foreach ( $days_list as $k => $v ) {
					if($v['redid'] && $v['task_id']) {
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(3, $v['id'], $v['redid'], $v['redid']);
						if( $bind_info['STATUS'] != 1 ) {
							$this->error('编辑月份的同时解除绑定红包任务失败，包名为:'.$v['package']);
						}else {
							$map = array(
								'type'	=>	0,
								'condition'	=>	0,
								'softname'	=>	'',
								'package'	=>	'',
								'task_id'	=>	0,
								'redid'		=>	0,
								'pic_path'	=>	'',
							);
							$where_map = array('id'=>$v['id']);
							$model->table('qd_sign_days')->where($where_map)->save($map);
						}
					}
				}
				//自动完成月份天数补齐
				if( $new_days > $cur_days ) {
					$cha = $new_days - $cur_days;
					for ($i=1; $i<= $cha; $i++) {
						$data = array(
								'day'		=>	$cur_days + $i,
								'mid'		=>	$id,
								'type'		=>	0,
								'condition'	=>	0,
								'softname'	=>	'',
								'package'	=>	'',
								'task_id'	=>	0,
								'redid'		=>	0,
								'pic_path'	=>	'',
								'create_tm'	=>	$time,
						);
						$model->table('qd_sign_days')->add($data);
					}
				}
				if( $new_days < $cur_days ) {
					$cha = $cur_days - $new_days;
					$cha_days_arr = $model->table('qd_sign_days')->where(array('mid'=>$id))->order('day desc')->limit($cha)->select();
					foreach ($cha_days_arr as $val) {
						$model->table('qd_sign_days')->where(array('id'=>$val['id']))->delete();
					}
				}
				//修改月份表
				$data_arr = array(
						'year'		=>	$year,
						'month'		=>	$month,
						'status'	=>	2,
						'update_tm'	=>	$time,
				);
				$result = $model->table($table)->where(array('id'=>$id))->save($data_arr);
				if( $result ) {
					$this -> writelog("已编辑id为{$_POST['id']}的每日签到活动",'qd_sign_month',$_POST['id'],__ACTION__ ,'','edit');
					$this->success('编辑成功');
				}else {
					$this->error('编辑失败');
				}
			}else {
				$this->success('编辑成功');
			}
		}else {
			$id		=	!empty($_GET['id'])?$_GET['id']:0;
			$where	=	array('id' => $id);
			$model	=	D('Sj.Sign');
			$rows = $model->table('qd_sign_month')->where($where)->find();
			$this->assign('id', $rows['id']);
			$this->assign('edit_year', $rows['year']);
			$this->assign('edit_month', $rows['month']);
			$this->display();
		}
	}
	
	//月份操作
	function month_operation()
	{
		$id = !empty($_GET['id']) ? (Int)$_GET['id'] : 0;
		$operation = !empty($_GET['operation']) ? $_GET['operation'] : '';
		$oper_arr = array(
				'del'	=>	array(0, '删除'), //删除
				'tg'	=>	array(1, '通过'), //通过
				'bh'	=>	array(2, '驳回'), //驳回
				'stp'	=>	array(3, '停用'), //停用
		);
		if( !$id || !$operation || !array_key_exists($operation, $oper_arr) ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.Sign');
		$table	=	'qd_sign_month';
		$where	=	array('id' => $id);
		switch ( $oper_arr[$operation][0] ) {
			case 0:
				//删除
				$rows = $model->table($table)->where($where)->find();
				if( $rows['status'] == 2 ) {
					//删除签到先解除红包绑定
					$where_days = array('mid'=>$id,'type'=>1);
					$days_list = $model->table('qd_sign_days')->where($where_days)->select();
					foreach ( $days_list as $k => $v ) {
						if($v['redid']) {
							D('Sj.RedActivity')->bind_red_pagckage(3, $v['id'], $v['redid'], $v['redid']);
						}
					}
					$result = $model->table($table)->where($where)->save(array('status'=>0));
				}else {
					$this->error('只能删除未开始状态的签到活动');
				}
				break;
			case 1:
				//通过
				$rows		=	$model->table($table)->where($where)->find();
				$time_str	=	$rows['year'].'-'.$rows['month'];
				$time_stamp	=	strtotime($time_str);
				$cur_time	=	strtotime( date('Y-m', time()) );
// 				if( $time_stamp <= $cur_time ) {
// 					$this->error('活动已开始或活动已过期不能通过审核！');
// 				}else {
					$result = $model->table($table)->where($where)->save(array('status'=>1));
// 				}
				break;
			case 2:
				//驳回
					$result = $model->table($table)->where($where)->save(array('status'=>2));
				break;
			case 3:
				//停用
				$rows	=	$model->table($table)->where($where)->find();
				$time	=	time();
				$year	=	date('Y', $time);
				$month	=	(Int)date('m', $time);
				if( $rows['year'] == $year && $rows['month'] == $month ) {
					$result = $model->table($table)->where($where)->save(array('status'=>3));
					//删除缓存
					$redis	=	new redis();
					$redis -> connect(C('GIFT_REDIS_HOST'),C('GIFT_REDIS_PORT'));
					$key1 = "web_sign:now_month_id:".date('Y-m-d',time());
					$key2 = "web_sign:{$rows['id']}:tm_config:*";
					$redis->delete(key1);
					$redis->delete(key2);
				}else {
					$this->error('只能停用进行中的活动');
				}
				break;
		}
		if( $result ) {
			$this -> writelog("已{$oper_arr[$operation][1]}id为{$id}的红包活动",$table,$id,__ACTION__ ,'','edit');
			$this->success('操作成功');
		}else {
			$this->error('操作失败');
		}
	}
	
	/**
	 * 签到提醒开关
	 */
	function remind()
	{
		$model	=	D('Sj.RedActivity');
		if($_POST) {
			$is_close = (Int)$_POST['is_close'];
			$map = array('configcontent'=>$is_close,'uptime'=>time());
			$res = $model->table('pu_config')->where(array('config_type'=>'SIGN_REMIND'))->save($map);
			if( $res ) {
				$str = $is_close ? '打开': '关闭';
				$this -> writelog("已{$str}了签到提醒",'pu_config',$res['conf_id'],__ACTION__ ,'','edit');
				$this->success('操作成功');
			}else {
				$this->error('操作失败');
			}
		}else {
			$remind_info	=	$model->table('pu_config')->where(array('config_type'=>'SIGN_REMIND'))->find();
			if( empty($remind_info) ) {
				$data = array(
					'config_type'	=>	'SIGN_REMIND',
					'configname'	=>	'SIGN_ON_OFF',
					'configcontent'	=>	1,//默认开启
					'uptime'		=>	time(),
					'status'		=>	1,
				);
				$model->table('pu_config')->add($data);
				$is_close = 1;
			}else {
				$is_close = $remind_info['configcontent'];
			}
			$this->assign('is_close', $is_close);
			$this->display();
		}
	}
	
	
	/**
	 * 每日签到-配置
	 */
	function days_list()
	{
		$mid = !empty($_GET['mid']) ? (Int)$_GET['mid'] : 0;
		if( !$mid ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.Sign');
		$where	=	array('mid'=>$mid);
		$days	=	$model->table('qd_sign_days')->where($where)->order('day asc')->select();
		$data	=	$model->table('qd_sign_month')->where(array('id'=>$mid))->find();
		$year_month	=	$data['year'].'-'.$data['month'];
		//获取红包详情
		$red_id_arr = $pkg_arr = array();
		foreach( $days as $k => $v ) {
			if( $v['redid'] ) {
				$red_id_arr[] = $v['redid'];
			}
			if( $v['package'] ) {
				$task_arr	=	array();
				$task_arr	=	D('Sj.RedActivity')->get_red_soft_list(3, $v['package'], $v['task_id']);
				if( $task_arr ) {
					$days[$k]['task_info'] = array(
						'package'	=>	$task_arr['package'],
						'softname'	=>	$task_arr['softname'],
						'start_tm'	=>	$task_arr['start_tm'],
						'end_tm'	=>	$task_arr['end_tm'],
					);
				}
			}
		}
		$red_list = D('Sj.RedActivity')->get_red_package_info_ext($red_id_arr);
		$this->assign('red_list', $red_list);
		$this->assign('year_month', $year_month);
		$this->assign('days', $days);
		$this->display();
	}
	
	/**
	 * 某天奖励类型-配置
	 */
	function days_prize_config()
	{
		$model = D('Sj.Sign');
		if( $_POST ) {
			$id				=	$_POST['id'] ? (Int)$_POST['id'] : 0;
			$reward_type	=	$_POST['reward_type'] ? (Int)$_POST['reward_type'] : 0;
			$condition		=	$_POST['condition'] ? (Int)$_POST['condition'] : 0;
			$redid			=	$_POST['redid'] ? (Int)$_POST['redid'] : 0;
			$task_id		=	$_POST['task_id'] ? (Int)$_POST['task_id'] : 0;
			$pkg			=	$_POST['pkg'] ? trim($_POST['pkg']) : '';
			$time			=	time();
			
			$this -> assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			$where = array('id' => $id);
			if( $reward_type == 0 ) {
				$data = array(
					'type'		=>	0,
					'condition'	=>	0,
					'redid'		=>	0,
					'task_id'	=>	0,
					'task_type'	=>	'',
					'pic_path'	=>	'',
					'package'	=>	'',
					'softname'	=>	'',
					'update_tm'	=>	$time,
				);
			}elseif( $reward_type == 1 ) {
				$data = array(
					'type'		=>	$reward_type,
					'condition'	=>	$condition,
					//'redid'		=>	0,
					'task_id'	=>	0,
					'package'	=>	'',
					'softname'	=>	'',
					'update_tm'	=>	$time,
				);
			}elseif($reward_type == 2) {
				$data = array(
					'type'		=>	$reward_type,
					'condition'	=>	$condition,
					'redid'		=>	0,
					'task_id'	=>	0,
					'task_type'	=>	'',
					'update_tm'	=>	$time,
				);
				//验证九宫格是否配满8个
				$award_count	=	D('Sj.SignAward')->table('qd_draw_prize')->where(array('did' => $id,'status' => 1))->count();
				if( $award_count < 8 ) {
					$this->error('奖品必须配满8个');
				}
			}else{
				$this->error('参数有误');
			}
			
			$rows = $model->table('qd_sign_days')->where($where)->find();
			if( $reward_type == 1 || $reward_type == 2 ) {
				if( !$rows['pic_path'] && empty($_FILES['pic_path']['tmp_name']) ) {
					$this -> assign('jumpUrl',"/index.php/Sj/Sign/days_prize_config/id/{$id}/");
					$this->error('请上传图片');
				}
			}
			$width = 74; $height = 54;
			$date	=	date("Ym/d/");
			if($_FILES['pic_path']['tmp_name']) {
				$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
				if($pic_path[0] != $width || $pic_path[1] != $height){
					$this->error("分辨率图标大小不符合条件");
				}
				if( !in_array($_FILES['pic_path']['type'], array('image/png','image/jpg','image/jpeg')) ) {
					$this->error("请添加图片格式为：jpg，png的弹窗图片");
				}
				$config['multi_config']['pic_path'] = array(
						'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
						'saveRule'	 =>	'getmsec',
						'img_p_size' =>	1024 * 200,
				);
			}
			if (!empty($config['multi_config'])) {
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $val) {
					$data[$val['post_name']] = $val['url'];
				}
			}
			
			$bind_ext_data		=	array('name' => '签到');
			
			if( $reward_type == 1 ) {
				//处理红包
				if($condition == 1) {
					if( !$redid ) {
						$this->error("请选择红包");
					}
					if( $rows['redid'] != $redid ) {
						$red_package_info = D('Sj.RedActivity')->get_red_package_info($redid);
						if( empty($red_package_info) ) {
							$this->error("未获到红包信息");
						}
						if( $red_package_info[0]['givetype'] !=1 ) {
							$this->error("红包发放类型必须为一次性发放");
						}
						$data['redid']		=	$redid;
						$data['task_id']	=	'';
						$data['package']	=	'';
						$data['softname']	=	'';
						//解除触绑定
						if( $rows['redid'] ) {
							$bind_info = D('Sj.RedActivity')->bind_red_pagckage(3, $rows['id'], $rows['redid'], $rows['redid'],$rows['task_id']);
							if( $bind_info['STATUS'] != 1 ) {
								$this->error($bind_info['MSG']);
							}
						}
						//绑定新的红包
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(3, $rows['id'], $data['redid'],'',$data['task_id'], $bind_ext_data);
						if( $bind_info['STATUS'] != 1 ) {
							$this->error($bind_info['MSG']);
						}
					}
				}elseif( $condition == 2 ) {
					if( !$task_id ) {
						$this->error("请选择红包任务");
					}
					if( $rows['task_id'] != $task_id ) {
						$task_info = D('Sj.RedActivity')->get_red_soft_list(3, $pkg, $task_id);
						if( empty($task_info) ){
							$this->error('未获取到任务信息');
						}
						//获取月份
						$day = $model->table('qd_sign_days')->where(array('id'=>$id))->find();
						$m_info = $model->table('qd_sign_month')->where(array('id'=>$day['mid']))->find();
						$start_tm	=	strtotime($m_info['year'].'-'.$m_info['month'].'-'.$day['day'].' 00:00:00');
						$end_tm		=	strtotime($m_info['year'].'-'.$m_info['month'].'-'.$day['day'].' 23:59:59');
						$task_info['start_tm']	=	strtotime($task_info['start_tm']);
						$task_info['end_tm']	=	strtotime($task_info['end_tm']);
// 						if( $start_tm < $task_info['start_tm'] || $start_tm > $task_info['end_tm'] || $end_tm < $task_info['start_tm'] || $end_tm > $task_info['end_tm'] ) {
// 							$this->error('活动时间不在任务时间内');
// 						}
						$data['task_id']	=	$task_id;
						$data['task_type']	=	$task_info['task_type'];
						$data['redid']		=	$task_info['red_id'];
						$data['package']	=	$task_info['package'];
						$data['softname']	=	$task_info['softname'];
						//解除触绑定
						if( $rows['redid'] ) {
							//解除绑定
							$bind_info = D('Sj.RedActivity')->bind_red_pagckage(3, $rows['id'], $rows['redid'], $rows['redid'], $rows['task_id']);
							if( $bind_info['STATUS'] != 1 ) {
								$this->error($bind_info['MSG']);
							}
						}
						//绑定新的任务红包
						//$bind_ext_data['name']	=	$bind_ext_data['name'].$task_info['taskname'];
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(3, $rows['id'], $data['redid'],'',$data['task_id'],$bind_ext_data);
						if( $bind_info['STATUS'] != 1 ) {
							$this->error($bind_info['MSG']);
						}
					}
				}
			}else {
				//解除触绑定
				if( $rows['redid'] ) {
					//解除绑定
					$bind_info = D('Sj.RedActivity')->bind_red_pagckage(3, $rows['id'], $rows['redid'], $rows['redid'], $rows['task_id']);
					if( $bind_info['STATUS'] != 1 ) {
						$this->error($bind_info['MSG']);
					}
				}
			}
			$result = $model->table('qd_sign_days')->where($where)->save($data);
			if( $result ) {
				$this -> writelog("已编辑id为{$result}的签到日活动配置",'qd_sign_days',$result,__ACTION__ ,'','edit');
				$this->success('操作成功');
			}else {
				$this->error('操作失败');
			}
		}else {
			$id = !empty($_GET['id']) ? (Int)$_GET['id'] : 0;
			if( !$id ) {
				$this->error('参数有误');
			}
			$day = $model->table('qd_sign_days')->where(array('id'=>$id))->find();
			//获取月份
			$m_info = $model->table('qd_sign_month')->where(array('id'=>$day['mid']))->find();
			if( $m_info['status'] != 2 ) {
				$this->error('只能操作处于待审核状态的月份');
			}
			$red_package_list	=	D('Sj.RedActivity')->get_red_pagckage_list(3);
			if( $day['type'] == 1 && $day['condition'] == 1 ) {
				$red_package = D('Sj.RedActivity')->get_red_package_info($day['redid']);
				$this->assign('red_package', $red_package);
			}
			if( $day['type'] == 1 && $day['condition'] == 2 ) {
				$task_list = D('Sj.RedActivity')->get_red_soft_list(3, $day['package'], $day['task_id']);
				$this->assign('task_list', $task_list);
			}
			$this->assign('red_package_list', $red_package_list);
			$date = $m_info['year'].'-'.$m_info['month'].'-'.$day['day'];
			$this->assign('id', $id);
			$this->assign('mid', $day['mid']);
			$this->assign('date', $date);
			$this->assign('day', $day);
			$this->display();
		}
	}
	
	//获取红包任务
	function pub_red_soft_list( ) {
		$id		=	(Int)$_REQUEST['id'];//某天id
		$pkg	=	trim($_REQUEST['pkg']);
		$task_list = D('Sj.RedActivity')->get_red_soft_list(3, $pkg);
		if( !empty($task_list) ) {
			$res = array(
					'code'	=>	1,
					'data'	=> $task_list,
			);
			exit(json_encode($res));
		}else {
			$res = array(
					'code'	=>	0,
			);
			exit(json_encode($res));
		}
	}
	
	//查看某月下的配置详情
	function month_detail()
	{
		$mid = !empty($_GET['mid']) ? (Int)$_GET['mid'] : 0;
		if( !$mid ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.Sign');
		$where	=	array('mid'=>$mid);
		$days	=	$model->table('qd_sign_days')->where($where)->order('day asc')->select();
		$data	=	$model->table('qd_sign_month')->where(array('id'=>$mid))->find();
		$year_month	=	$data['year'].'-'.$data['month'];
		//连续签到配置
		$list = $model->table('qd_sign_continuity')->where($where)->order('id asc')->select();
		//获取红包详情
		$red_id_arr = array();
		foreach( $days as $k => $v ) {
			if( $v['redid'] ) {
				$red_id_arr[] = $v['redid'];
			}
		}
		$red_list = D('Sj.RedActivity')->get_red_package_info_ext($red_id_arr);
		//补签卡配置详情
		$list_price = $list_task = array();
		$list_mend = $model->table('qd_sign_mend')->where($where)->order('id asc')->select();
		foreach ($list_mend as $key => $val) {
			if( $val['type'] == 1 ) {
				$list_task[] = $val;//做任务领补签卡
			}else {
				$list_price[] = $val;//金币购买补签卡
			}
		}
		$this->assign('list', $list);
		$this->assign('red_list', $red_list);
		$this->assign('list_price', $list_price);
		$this->assign('list_task', $list_task);
		$this->assign('year_month', $year_month);
		$this->assign('days', $days);
		$this->display();
	}
	
	//中奖纪录
	function award_record()
	{
		$username	=	!empty($_REQUEST['username']) ? trim($_REQUEST['username']) : null;
		$phone		=	!empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : null;
		$prizename	=	!empty($_REQUEST['prizename']) ? trim($_REQUEST['prizename']) : null;
		$type		=	!empty($_REQUEST['type']) ? trim($_REQUEST['type']) : null;//中奖类型
		$is_pub		=	isset($_REQUEST['is_pub']) ? $_REQUEST['is_pub'] : -1;//是否发奖
		$start_time	=	!empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : null;//中奖时间
		$end_time	=	!empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : null;//中奖时间
		$export		=	!empty($_GET['export']) ? $_GET['export'] : null;//导出全部
		$batch_id	=	!empty($_GET['batch_id']) ? trim($_GET['batch_id'],',') : null;//导出选中
		$model		=	D('Sj.Sign');
		$day_30		=	86400 * 30;//超过30天已过期
		$time		=	time();
		if( $batch_id ) {
			//导出选择选中
			$list	=	$model->table('qd_draw_award') -> where( array('id' => array('in',$batch_id)) )->order('id DESC') -> select();
		}else {
			
				$where = ' where A.status = 1 ';
				$username	&&	$where .= " and A.username like '%{$username}%' ";
				$prizename	&&	$where .= " and A.prizename like '%{$prizename}%' ";
				$type		&&	$where .= " and A.type = {$type} ";
				$phone	&&	$where .= " and ((A.phone = '{$phone}' and type =2) or (B.phone = '{$phone}' and type =1)) ";
				($is_pub!=-1&&$is_pub!=2)	&&	$where .= " and A.is_pub = {$is_pub} ";
				$is_pub == 2 && $where .= " and $time - A.create_tm >= {$day_30} and A.type in (1,2) ";
				$start_time	&&	$where .= "and A.create_tm >= {$start_time}";
				$end_time	&&	$where .= " and A.create_tm <= {$end_time}";
				
				$sql_ret	=	"SELECT A.*,B.contact_name,B.phone as contact_phone,B.address FROM qd_draw_award as A LEFT JOIN qd_draw_userinfo as B on  A.mid = B.mid and A.uid = B.uid ";
				$sql_count	=	"SELECT count(*) as count FROM qd_draw_award as A LEFT JOIN qd_draw_userinfo as B on  A.mid = B.mid and A.uid = B.uid ";
				$order = ' order by A.id DESC ';
				$sql_ret   .= $where.$order;
				$sql_count .= $where;
				$count = $model->query($sql_count);
				$count = $count[0]['count'];
				
				import("@.ORG.Page");
				$param		=	http_build_query($_REQUEST);
				$Page		=	new Page($count, 20, $param);
				$show		=	$Page->show();
				
				$limit =  'LIMIT '.$Page->firstRow . ',' . $Page->listRows;
				
				if( $export ) {
					//导出全部
					$list	=	$model->query($sql_ret);
				}else {
					//搜索
					$sql_ret .= $limit;
					$list	=	$model->query($sql_ret);
				}
		}
		
		foreach ($list as $key => $val) {
			if( $val['type'] == 1 || $val['type'] == 2 ) {
				if( ($time - $val['create_tm'] ) >= $day_30) {
					$list[$key]['is_pub'] = 2;//已过期
				}
			}
		}
		//是否导出报表
		if( $export || $batch_id ) {
			if( empty($list) ) {
				$this->error('无数据');
			}
			$filename = date('Ymd').'.csv'; //设置文件名
			$str = 'IMEI及IP,用户名,奖品名称,奖品类型,个人信息,中奖时间,发奖状态,发奖时间,备注';
			$str = iconv('utf-8','gb2312', $str);
			$str = $str."\n";
			foreach ( $list as $val ) {
				$imei			=	iconv('utf-8','gb2312', $val['imei'].'  '.$val['ip']);
				$username		=	iconv('utf-8','gb2312', $val['username']);
				$prizename		=	iconv('utf-8','gb2312', $val['prizename']);
				$type = '';
				if( $val['type'] == 1 ) {
					$type = '实物奖';
				}elseif ( $val['type'] == 2 ) {
					$type = '充值卡';
				}elseif ( $val['type'] == 3 ) {
					$type = '礼券';
				}elseif ( $val['type'] == 4 ) {
					$type = '礼包';
				}elseif ( $val['type'] == 5 ) {
					$type = '礼包（直接发放）';
				}elseif ( $val['type'] == 6 ) {
					$type = '谢谢参与';
				}
				$type			=	iconv('utf-8','gb2312', $type);
				if($val['type'] == 1) {
					if($val['contact_name']||$val['contact_phone']||$val['address']){
						$userinfo	=	'姓名:'.$val['contact_name'].' '.'手机号：'.$val['contact_phone'].' '.'地址：'.$val['address'];
					}else {
						$userinfo	=	'未填写';
					}
				}elseif( $val['type'] == 2) {
					$userinfo	=	$val['phone']?'手机号：'.$val['phone']:'未填写';
				}else {
					$userinfo	=	'';
				}
				$userinfo		=	iconv('utf-8','gb2312', $userinfo);
				$create_tm		=	iconv('utf-8','gb2312', date('Y-m-d H:i:s', $val['create_tm']));
				if( $val['is_pub'] == 0 ){
					$is_pub			=	iconv('utf-8','gb2312','待发奖');
				}elseif( $val['is_pub'] == 1 ) {
					$is_pub			=	iconv('utf-8','gb2312','已发奖');
				}elseif( $val['is_pub'] == 2 ) {
					$is_pub			=	iconv('utf-8','gb2312','已过期');
				}
				$pub_tm	=	$val['pub_tm']?iconv('utf-8','gb2312', date('Y-m-d H:i:s', $val['pub_tm'])):'';
				$remark	=	iconv('utf-8','gb2312', $val['remark']);
				
				$str	.=	$imei.",".$username.",".$prizename.",".$type.",".$userinfo.",".$create_tm.",".$is_pub.",".$pub_tm.",".$remark.","."\n"; //用引文逗号分开
			}
			$this->export_csv($filename,$str);die; //导出
		}
		$this->assign('page', $show);
		$this->assign('username', $username);
		$this->assign('phone', $phone);
		$this->assign('prizename', $prizename);
		$this->assign('type', $type);
		$this->assign('is_pub', $is_pub);
		$this->assign('start_time', $_REQUEST['start_time']);
		$this->assign('end_time', $_REQUEST['end_time']);
		$this->assign('list', $list);
		$this->display();
	}
	
	//中奖纪录操作
	function award_record_operation()
	{
		$id_arr	=	!empty($_POST['ids']) ? $_POST['ids'] : null;
		if( !$id_arr ) {
			exit(json_encode(array('code'=>0,'msg'=>'参数有误')));
		}
		$model	=	D('Sj.Sign');
		foreach( $id_arr as $key => $val ) {
			$where	=	array('id' => $val);
			$rows = $model->table('qd_draw_award')->where($where)->find();
			if($rows['is_pub'] == 1) {
				$res = 0;
				continue;
			}else {
				$map = array(
						'is_pub'	=>	1,
						'pub_tm'	=>	time(),
				);
				$res = $model->table('qd_draw_award')->where($where)->save($map);
				$this -> writelog("已编辑id为{$val}的中奖纪录中的发奖状态",'qd_draw_award',$val,__ACTION__ ,'','edit');
			}
		}
		if( $res ) {
			exit(json_encode(array('code'=>1,'msg'=>'操作成功')));
		}else {
			exit(json_encode(array('code'=>0,'msg'=>'操作失败')));
		}
	}
	
	function award_record_remark()
	{
		$id		=	!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$model	=	D('Sj.Sign');
		if( !empty($_POST) ) {
			$remark	=	str_replace(array("\r\n","\n"),'', trim($_POST['remark']));
			$map	=	array('remark' => $remark);
			$where	=	array('id' => $id);
			$res = $model->table('qd_draw_award')->where(array('id'=> $id))->save($map);
			if( $res ) {
				$this -> writelog("已编辑id为{$id}的中奖纪录中的备注",'qd_draw_award',$id,__ACTION__ ,'','edit');
				$this->success('修改成功');
			}else {
				$this->error('修改失败');
			}
		}else {
			$rows = $model->table('qd_draw_award') -> where(array('id'=> $id)) -> find();
			$this->assign('rows', $rows);
			$this->display();
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