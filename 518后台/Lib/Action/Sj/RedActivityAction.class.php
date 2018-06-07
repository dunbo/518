<?php
/**
 * 红包活动
 * activity_type定义 5、九宫格，6、天降红包雨，7、红包翻翻乐，8、红包叠叠乐
 */
class RedActivityAction extends CommonAction{
	//九宫格活动列表
	function jgg_list()
	{
		//列表类型：false待审核列表 true通过列表
		$list_type	=	!empty($_REQUEST['list_type'])?$_REQUEST['list_type']:0;
		$id			=	$_REQUEST['id'];
		$name		=	!empty($_REQUEST['name'])?trim($_REQUEST['name']):null;
		$start_tm	=	!empty($_REQUEST['start_tm'])?strtotime($_REQUEST['start_tm']):null;
		$end_tm		=	!empty($_REQUEST['end_tm'])?strtotime($_REQUEST['end_tm']):null;
		if($start_tm && $end_tm && $start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$model	=	D('Sj.RedActivity');
		$where	=	'activity_type = 5';
		if( !$list_type ) {
			$where .= ' and status = 2';
		}else {
			$where .= ' and status in (1,3)';
		}
		$id		&&	$where .= " and `id` = {$id} ";
		$name	&&	$where .= " and `name` like '%{$name}%' ";
		if( $start_tm && !$end_tm ){
			$where .= " and start_tm >= {$start_tm} ";
		}elseif( !$start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} ";
		}elseif($start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} and start_tm >= {$start_tm} ";
		}
		$where_go['_string'] = $where;
		$count = $model->table('sj_activity')->where($where_go)->count();
		
		import("@.ORG.Page");
		$param	=	http_build_query($_REQUEST);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('sj_activity') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('id DESC') -> select();
		
		$this->assign('at', 5);
		$this->assign('timestamp', time());
		$this->assign('list_type', $list_type);
		$this->assign('id', $id);
		$this->assign('name', $name);
		$this->assign('start_tm', $_REQUEST['start_tm']);
		$this->assign('end_tm', $_REQUEST['end_tm']);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	//红包雨活动列表
	function hby_list()
	{
		//列表类型：false待审核列表 true通过列表
		$list_type	=	!empty($_REQUEST['list_type'])?$_REQUEST['list_type']:0;
		$id			=	$_REQUEST['id'];
		$name		=	!empty($_REQUEST['name'])?trim($_REQUEST['name']):null;
		$start_tm	=	!empty($_REQUEST['start_tm'])?strtotime($_REQUEST['start_tm']):null;
		$end_tm		=	!empty($_REQUEST['end_tm'])?strtotime($_REQUEST['end_tm']):null;
		if($start_tm && $end_tm && $start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$model	=	D('Sj.RedActivity');
		$where	=	'activity_type = 6';
		if( !$list_type ) {
			$where .= ' and status = 2';
		}else {
			$where .= ' and status in (1,3)';
		}
		$id		&&	$where .= " and `id` = {$id} ";
		$name	&&	$where .= " and `name` like '%{$name}%' ";
		if( $start_tm && !$end_tm ){
			$where .= " and start_tm >= {$start_tm} ";
		}elseif( !$start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} ";
		}elseif($start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} and start_tm >= {$start_tm} ";
		}
		$where_go['_string'] = $where;
		$count = $model->table('sj_activity')->where($where_go)->count();
		
		import("@.ORG.Page");
		$param	=	http_build_query($_REQUEST);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('sj_activity') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('id DESC') -> select();
		
		$this->assign('at', 6);
		$this->assign('timestamp', time());
		$this->assign('list_type', $list_type);
		$this->assign('id', $id);
		$this->assign('name', $name);
		$this->assign('start_tm', $_REQUEST['start_tm']);
		$this->assign('end_tm', $_REQUEST['end_tm']);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	//红包翻翻乐列表
	function ffl_list()
	{
		//列表类型：false待审核列表 true通过列表
		$list_type	=	!empty($_REQUEST['list_type'])?$_REQUEST['list_type']:0;
		$id			=	$_REQUEST['id'];
		$name		=	!empty($_REQUEST['name'])?trim($_REQUEST['name']):null;
		$start_tm	=	!empty($_REQUEST['start_tm'])?strtotime($_REQUEST['start_tm']):null;
		$end_tm		=	!empty($_REQUEST['end_tm'])?strtotime($_REQUEST['end_tm']):null;
		if($start_tm && $end_tm && $start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$model	=	D('Sj.RedActivity');
		$where	=	'activity_type = 7';
		if( !$list_type ) {
			$where .= ' and status = 2';
		}else {
			$where .= ' and status in (1,3)';
		}
		$id		&&	$where .= " and `id` = {$id} ";
		$name	&&	$where .= " and `name` like '%{$name}%' ";
		if( $start_tm && !$end_tm ){
			$where .= " and start_tm >= {$start_tm} ";
		}elseif( !$start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} ";
		}elseif($start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} and start_tm >= {$start_tm} ";
		}
		$where_go['_string'] = $where;
		$count = $model->table('sj_activity')->where($where_go)->count();
		
		import("@.ORG.Page");
		$param	=	http_build_query($_REQUEST);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('sj_activity') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('id DESC') -> select();
		
		$this->assign('at', 7);
		$this->assign('timestamp', time());
		$this->assign('list_type', $list_type);
		$this->assign('id', $id);
		$this->assign('name', $name);
		$this->assign('start_tm', $_REQUEST['start_tm']);
		$this->assign('end_tm', $_REQUEST['end_tm']);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	//红包叠叠乐列表
	function ddl_list()
	{
		//列表类型：false待审核列表 true通过列表
		$list_type	=	!empty($_REQUEST['list_type'])?$_REQUEST['list_type']:0;
		$id			=	$_REQUEST['id'];
		$name		=	!empty($_REQUEST['name'])?trim($_REQUEST['name']):null;
		$start_tm	=	!empty($_REQUEST['start_tm'])?strtotime($_REQUEST['start_tm']):null;
		$end_tm		=	!empty($_REQUEST['end_tm'])?strtotime($_REQUEST['end_tm']):null;
		if($start_tm && $end_tm && $start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$model	=	D('Sj.RedActivity');
		$where	=	'activity_type = 8';
		if( !$list_type ) {
			$where .= ' and status = 2';
		}else {
			$where .= ' and status in (1,3)';
		}
		$id		&&	$where .= " and `id` = {$id} ";
		$name	&&	$where .= " and `name` like '%{$name}%' ";
		if( $start_tm && !$end_tm ){
			$where .= " and start_tm >= {$start_tm} ";
		}elseif( !$start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} ";
		}elseif($start_tm && $end_tm) {
			$where .= " and end_tm <= {$end_tm} and start_tm >= {$start_tm} ";
		}
		$where_go['_string'] = $where;
		$count = $model->table('sj_activity')->where($where_go)->count();
		
		import("@.ORG.Page");
		$param	=	http_build_query($_REQUEST);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('sj_activity') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('id DESC') -> select();
		
		$this->assign('at', 8);
		$this->assign('timestamp', time());
		$this->assign('list_type', $list_type);
		$this->assign('id', $id);
		$this->assign('name', $name);
		$this->assign('start_tm', $_REQUEST['start_tm']);
		$this->assign('end_tm', $_REQUEST['end_tm']);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	//添加活动
	function activity_add()
	{
		$at = (int)$_REQUEST['at'];//活动类型
		if($_POST) {
			$table	=	'sj_activity';
			$model	=	D('Sj.RedActivity');
			if( $at == 5 ) {
				$ret	=	$model->jgg_add_do();
			}elseif( $at == 6 ) {
				$ret	=	$model->hby_add_do();
			}elseif( $at == 7 ) {
				$ret	=	$model->ffl_add_do();
			}elseif($at == 8) {
				$ret	=	$model->ddl_add_do();
			}else {
				$this -> error("参数有误");
			}
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$res = $model -> red_activity_add($data, $at);
				if($res) {
					$this -> writelog("已添加id为{$res}的红包活动",$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else {
					$this -> error("操作失败");
				}
			}else {
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$this->assign('at', $at);
			$this->display();
		}
	}
	//编辑活动
	function activity_edit()
	{
		$id		=	$_REQUEST['id'];
		$at		=	(int)$_REQUEST['at'];//活动类型
		$table	=	'sj_activity';
		$model	=	D('Sj.RedActivity');
		$where	=	array('id' => $id);
		if( $_POST ) {
			$ck		=	$this->check_activity_operation($id);
			if( !$ck ) {
				$this -> error("只能操作处于待审核状态的活动");
			}
			if( $at == 5 ) {
				$ret	=	$model->jgg_add_do();
			}elseif( $at == 6 ) {
				$ret	=	$model->hby_add_do();
			}elseif( $at == 7 ) {
				$ret	=	$model->ffl_add_do();
			}elseif($at == 8) {
				$ret	=	$model->ddl_add_do();
			}else {
				$this -> error("参数有误");
			}
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$res = $model->table($table)->where($where)->save($data);
				if($res) {
					$this -> writelog("已编辑id为{$id}的红包活动",$table,$id,__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else {
					$this -> error("操作失败");
				}
			}else {
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$list = $model->table($table)->where($where)->find();
			$this->assign('at', $at);
			$this->assign('list', $list);
			$this->display('activity_add');
		}
	}
	
	//活动状态操作
	public function operation()
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
		$model	=	D('Sj.RedActivity');
		$table	=	'sj_activity';
		$where	=	array('id' => $id);
		$rows = $model->table($table)->where($where)->find();
		$activity_name	=	array('5'=>'九宫格','6'=>'天降红包雨','7'=>'红包翻翻乐','8'=>'红包叠叠乐');
		$activity_name_param	=	$activity_name[$rows['activity_type']].'缓存';
		switch ( $oper_arr[$operation][0] ) {
			case 0:
				//删除
				if( $rows['status'] == 2 ) {
					//删除活动先而解除活动下的红包
					$where_award	=	array(
						'aid'	=>	$id,
						'type'	=>	6,
						'status'=>	1,
					);
					$award_list		=	D('Sj.Sign')->table('sign_prize')->where($where_award)->select();
					foreach ( $award_list as $k => $v ) {
						if($v['red_id']) {
							D('Sj.RedActivity')->bind_red_pagckage(4, $v['aid'], $v['red_id'], $v['red_id']);
						}
						if( $v['d_redid'] ) {
							D('Sj.RedActivity')->bind_red_pagckage(4, $v['aid'], $v['d_redid'], $v['d_redid']);
						}
					}
					$result = $model->table($table)->where($where)->save(array('status'=>0));
					if( $result ) {
						//刷红包雨缓存
						$tool_model = D('Admin.Tool');
						$tool_model->brush_cache( $activity_name_param, '/cache/cache_sign.php');
					}
				}else {
					$this->error('只能删除未开始状态的活动列表');
				}
				break;
			case 1:
				//通过
				$time = time();
				if( $time > $rows['end_tm'] ) {
					$this->error('活动已开始或活动已过期不能通过审核！');
				}
				//检查是否配满8个奖品
				if( $rows['activity_type'] == 5 || $rows['activity_type'] == 7 ) {
					$award_count = D('Sj.Sign')->table('sign_prize')->where(array('aid'=>$id,'status'=>1))->count();
					if( $award_count < 8 ) {
						$this->error('奖品必须配满8个');
					}
				}
				$result = $model->table($table)->where($where)->save(array('status'=>1));
				if( $result ) {
					//刷红包雨缓存
					$tool_model = D('Admin.Tool');
					$tool_model->brush_cache($activity_name_param,'/cache/cache_sign.php');
				}
				break;
			case 2:
				//驳回
				$ex = $this->check_heifer_activity($id);
				if( $ex ) {
					$this->error('该活动已在分享活动发布！');
				}
				//进行中的活动停止需要检查灵活运营样式中是处于当前或未开始状态
				$AdSearch	=	D("Sj.AdSearch");
				$stop_error	=	$AdSearch->check_ad($id, $rows['start_tm'], $rows['end_tm'], 2, 1);
				if( $stop_error ) {
					$this->error($stop_error);
				}else {
					$result = $model->table($table)->where($where)->save(array('status'=>2));
					if( $result ) {
						//刷红包雨缓存
						$tool_model = D('Admin.Tool');
						$tool_model->brush_cache($activity_name_param,'/cache/cache_sign.php');
					}
					break;
				}
			case 3:
				//停用
				$ex = $this->check_heifer_activity($id);
				if( $ex ) {
					$this->error('该活动已在分享活动发布！');
				}
				//进行中的活动停止需要检查灵活运营样式中是处于当前或未开始状态
				$AdSearch	=	D("Sj.AdSearch");
				$stop_error	=	$AdSearch->check_ad($id, $rows['start_tm'], $rows['end_tm'], 2, 1);
				if( $stop_error ) {
					$this->error($stop_error);
				}else {
					$result = $model->table($table)->where($where)->save(array('status'=>3));
					if( $result ) {
						//刷红包雨缓存
						$tool_model = D('Admin.Tool');
						$tool_model->brush_cache($activity_name_param,'/cache/cache_sign.php');
					}
					break;
				}
		}
		if( $result ) {
			$this -> writelog("已{$oper_arr[$operation][1]}id为{$id}的红包活动",$table,$id,__ACTION__ ,'','edit');
			$this->success('操作成功');
		}else {
			$this->error('操作失败');
		}
	}
	
	//活动详情
	public function activity_detail()
	{
		$at		=	(Int)$_GET['at'];//活动类型
		$aid	=	(Int)$_GET['id'];
		$pid	=	(Int)$_GET['pid'];//活动页面id
		
		$model_activity = D('Sj.Sign');//活动库
		$model_gomarket = D('Sj.RedActivity');//gomarket库
		
		if( $at == 5 || $at == 7 ) {
			$where_award	=	array(
					'aid'	=>	$aid,
					'status'=>	1,
			);
			$where_soft	=	array(
					'page_id'	=>	$pid,
					'status'	=>	array('in','1,2'),
			);
			$order = 'level asc';
			$list		=	$model_activity->table('sign_prize')->where($where_award)->order($order)->select();
			$soft_list	=	$model_gomarket->table('sj_actives_soft')->where($where_soft)->order('id DESC') -> select();
			if( $at == 5 ){
				$draw	=	$model_gomarket->table('sj_activity')->where(array('id'=>$aid))->find();
			}else {
				$draw	=	'';
			}
			$award_list = array();
			foreach ($list as $key =>$val) {
				$award_list[$val['level']] = $val;
			}
			$this->assign('draw', $draw);
			$this->assign('soft_list', $soft_list);
		}elseif( $at == 6 ) {
			$where_award	=	array(
					'aid'	=>	$aid,
					'status'=>	array('in','1,2'),
			);
			$order = 'id asc';
			$award_list	=	$model_activity->table('sign_prize')->where($where_award)->order($order)->select();
		}elseif( $at == 8 ) {
			$where_award	=	array(
					'aid'	=>	$aid,
					'status'=>	array('in','1,2'),
			);
			$order = 'id asc';
			$award_list	=	$model_activity->table('sign_prize')->where($where_award)->order($order)->select();
		}
		
		//获取红包详情
		$red_id_arr = $d_red_id_arr = $pkg_arr = array();
		foreach( $award_list as $k => $v ) {
			if( $v['red_id'] ) {
				$red_id_arr[] = $v['red_id'];
			}
			if( $at == 8 && $v['d_redid'] ) {
				$d_red_id_arr[] = $v['d_redid'];
			}
			if( $v['package'] ) {
				$task_arr	=	array();
				$task_arr	=	D('Sj.RedActivity')->get_red_soft_list(4, $v['package'], $v['task_id']);
				if( $task_arr ) {
					$award_list[$k]['task_info'] = array(
							'package'	=>	$task_arr['package'],
							'softname'	=>	$task_arr['softname'],
							'start_tm'	=>	$task_arr['start_tm'],
							'end_tm'	=>	$task_arr['end_tm'],
					);
				}
			}
		}
		$red_list	=	D('Sj.RedActivity')->get_red_package_info_ext($red_id_arr);
		$d_red_list	=	D('Sj.RedActivity')->get_red_package_info_ext($d_red_id_arr);
		$this->assign('red_list', $red_list);
		$this->assign('d_red_list', $d_red_list);
		$this->assign('award_list', $award_list);
		$this->assign('at', $at);
		$this->display();
	}
	
	//红包活动奖品配置列表
	public function award_list()
	{
		$at		=	(Int)$_GET['at'];//活动类型
		$aid	=	(Int)$_GET['id'];
		$pid	=	(Int)$_GET['pid'];//活动页面id
		$model	=	D('Sj.Sign');
		$where	=	array(
				'aid'		=>	$aid,
				'status'	=>	array('in',array(1,2)),
		);
		$list = array();
		if( $at == 5 || $at == 7 ) {
			$award_list	=	$model->table('sign_prize')->where($where)->order('level asc')->select();
			foreach ($award_list as $key =>$val) {
				$list[$val['level']] = $val;
			}
		}else {
			$list	=	$model->table('sign_prize')->field('*')->where($where)->order('id desc')->select();
		}
		//获取红包详情
		$red_id_arr = $d_red_id_arr = $pkg_arr = array();
		foreach( $list as $k => $v ) {
			if( $v['red_id'] ) {
				$red_id_arr[] = $v['red_id'];
			}
			if( $at == 8 && $v['d_redid'] ) {
				$d_red_id_arr[] = $v['d_redid'];
			}
			if( $v['package'] ) {
				$task_arr	=	array();
				$task_arr	=	D('Sj.RedActivity')->get_red_soft_list(4, $v['package'], $v['task_id']);
				if( $task_arr ) {
					$list[$k]['task_info'] = array(
							'package'	=>	$task_arr['package'],
							'softname'	=>	$task_arr['softname'],
							'start_tm'	=>	$task_arr['start_tm'],
							'end_tm'	=>	$task_arr['end_tm'],
					);
				}
			}
		}
		$red_list	=	D('Sj.RedActivity')->get_red_package_info_ext($red_id_arr);
		$d_red_list	=	D('Sj.RedActivity')->get_red_package_info_ext($d_red_id_arr);
		$this->assign('at', $at);
		$this->assign('aid', $aid);
		$this->assign('pid', $pid);
		$this->assign('list', $list);
		$this->assign('red_list', $red_list);
		$this->assign('d_red_list', $d_red_list);
		$this->display();
	}
	
	//奖品添加
	public function award_add()
	{
		$at		=	(Int)$_REQUEST['at'];
		$aid	=	(Int)$_REQUEST['aid'];
		$level	=	(Int)$_REQUEST['level'];
		if( $_POST ) {
			$ck		=	$this->check_activity_operation($aid);
			if( !$ck ) {
				$this -> error("只能操作处于待审核状态的活动");
			}
			$model	=	D('Sj.Sign');
			$table	=	"sign_prize";
			$ret	=	D('Sj.RedActivity') -> red_award_add_do($table);
			if($ret['code'] == 1) {
				$data	=	$ret['data'];
				$res = $model -> table($table) -> add($data);
				if($res){
					$this -> writelog("已添加id为{$res}的红包活动奖品",$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			//获取红包列表
			$model_red	=	D('Sj.RedActivity');
			$red_package_list = $model_red->get_red_pagckage_list(4);
			$this->assign('at', $at);
			$this->assign('aid', $aid);
			$this->assign('level', $level);
			$this->assign('red_package_list', $red_package_list);
			$this->display();
		}
	}
	
	//奖品编辑
	public function award_edit()
	{
		$id		=	(Int)$_REQUEST['id'];
		$aid	=	(Int)$_REQUEST['aid'];
		$at		=	(Int)$_REQUEST['at'];
		$model	=	D('Sj.Sign');
		$where	=	array('id'	=>	$id);
		if( $_POST ) {
			$ck		=	$this->check_activity_operation($aid);
			if( !$ck ) {
				$this -> error("只能操作处于待审核状态的活动");
			}
			$table	=	"sign_prize";
			$ret	=	D('Sj.RedActivity') -> red_award_add_do($table);
			if($ret['code'] == 1) {
				$data	=	$ret['data'];
				$res = $model -> table($table) -> where($where) -> save($data);
				if($res){
					$this -> writelog("已编辑id为{$id}的红包活动奖品",$table,$id,__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$list = $model->table('sign_prize')->where($where)->find();
			$model_red	=	D('Sj.RedActivity');
			$red_package_list	=	$model_red->get_red_pagckage_list(4);
			if($list['condition'] == 1 && $at != 8 ) {
				$red_package = $model_red->get_red_package_info($list['red_id']);
				$this->assign('red_package', $red_package);
			}else {
				$red_package = $model_red->get_red_package_info($list['d_redid']);
				$this->assign('red_package', $red_package);
			}
			if( $list['condition'] == 2 || $at == 8 ) {
				$task_list = D('Sj.RedActivity')->get_red_soft_list(4,$list['package'],$list['task_id']);
				$this->assign('task_list', $task_list);
			}
			$this->assign('at', $at);
			$this->assign('id', $id);
			$this->assign('aid', $aid);
			$this->assign('list', $list);
			$this->assign('red_package_list', $red_package_list);
			$this->display('award_add');
		}
	}
	
	function pub_red_soft_list( ) {
		$aid	=	(Int)$_REQUEST['aid'];
		$pkg	=	trim($_REQUEST['pkg']);
		$model = D('Sj.RedActivity');
		$task_list = $model->get_red_soft_list(4,$pkg);
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
	
	
	//设置中奖概率
	public function award_probability($table = '' ){
		$model	=	D('Sj.SignAward');
		$table	=	"sign_prize";
		if($_POST) {
			$num2	=	explode("/",$_POST['probability']);
			$pos	=	strpos($_POST['probability'],"/");
			$id		=	$_POST['id'];
			$aid	=	$_POST['aid'];
			$ck		=	$this->check_activity_operation($aid);
			if( !$ck ) {
				$this -> error("只能操作处于待审核状态的活动");
			}
			if(!empty($_POST['probability']) && $pos == false) {
				$this -> error("概率格式错误");
			}else if($_POST['probability'] != 0 && (!is_numeric($num2[0]) || !is_numeric($num2[1]))){
				$this -> error("中奖概率格式出错");
			}else if($_POST['probability'] == '') {
				$_POST['probability'] = 0;
			}
			$activity =D('Sj.RedActivity')->table('sj_activity')->field('activity_type')->where(array('id'=>$aid))->find();
			if($activity['activity_type'] == 8) {
				$probability_num = ($num2[0]/$num2[1]);
			}else {
				$where = array('id'=>array('neq',$id),'aid'=>$aid,'type'=>6, 'status'=>1);
				$list = $model -> table($table) -> where($where) ->field('probability')-> select();
				$probability_num = 0;
				foreach($list as $v) {
					if(empty($v['probability'])){
						continue;
					}
					$num = explode("/",$v['probability']);
					$calculate = ($num[0]/$num[1]);
					$probability_num = $probability_num + $calculate;
				}
				$calculate2 = ($num2[0]/$num2[1]);
				$probability_num = $probability_num + $calculate2;
			}
			if($probability_num > 1){
				$this -> error("概率不能大于1");
			}
			$where = array(
					'id'	=>	$id,
			);
			$data = array(
					'probability'	=>	$_POST['probability'],
					'update_tm'		=>	time(),
			);
			$log = $this->logcheck($where, $table, $data, $model);
			$res = $model -> table($table) -> where($where) -> save($data);
			if($res){
				$this -> writelog("活动编辑概率：id为[{$_POST['id']}]<br/>".$log,$table,$_POST['id'],__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else{
			$where = array('id'=>$_GET['id']);
			$list = $model -> table($table)  ->where($where)->field('id,aid,probability,name,num,prize_num')->find();
			$this -> assign('list',$list);
			$this -> display();
		}
	}
	
	//奖品操作
	public function award_operation()
	{
		$id = !empty($_GET['id']) ? (Int)$_GET['id'] : 0;
		$operation = !empty($_GET['operation']) ? trim($_GET['operation']) : '';
		$oper_arr = array(
				'del'	=>	array(0, '删除'), //删除
				'qd'	=>	array(1, '启动'), //启动
				'stp'	=>	array(2, '停用'), //停用
		);
		if( !$id || !$operation || !array_key_exists($operation, $oper_arr) ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.Sign');
		$table	=	'sign_prize';
		$where	=	array('id' => $id);
		$rows	=	$model->table($table)->where($where)->find();
		$ck		=	$this->check_activity_operation($rows['aid']);
		if( !$ck ) {
			$this -> error("只能操作处于待审核状态的活动");
		}
		switch ( $oper_arr[$operation][0] ) {
			case 0:
				//删除
				if( $rows['type'] == 6 && $rows['red_id']) {
					$bind_info = D('Sj.RedActivity')->bind_red_pagckage(4, $rows['aid'], $rows['red_id'], $rows['red_id']);
					if( $bind_info['STATUS'] != 1 ) {
						$this->error($bind_info['MSG']);
					}
				}
				if( $rows['type'] == 6 && $rows['d_redid'] ) {
					$bind_info = D('Sj.RedActivity')->bind_red_pagckage(4, $rows['aid'], $rows['d_redid'], $rows['d_redid']);
					if( $bind_info['STATUS'] != 1 ) {
						$this->error($bind_info['MSG']);
					}
				}
				$result = $model->table($table)->where($where)->save(array('status'=>0));
				break;
			case 1:
				//启动
				$result = $model->table($table)->where($where)->save(array('status'=>1));
				break;
			case 2:
				//停用
				$result = $model->table($table)->where($where)->save(array('status'=>2));
				break;
		}
		if( $result ) {
			$this -> writelog("已{$oper_arr[$operation][1]}id为{$id}的红包活动奖品",$table,$id,__ACTION__ ,'','edit');
			$this->success('操作成功');
		}else {
			$this->error('操作失败');
		}
	}
	
	
	//抽奖机会软件库配置
	public function soft_list()
	{
		$aid	=	(Int)$_GET['aid'];//活动id
		$pid	=	(Int)$_GET['pid'];//活动页面id
		$at		=	(Int)$_GET['at'];//活动类型
		if( !$pid ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.ReActivity');
		$where	=	array(
				'page_id'	=>	$pid,
				'status'	=>	array('in','1,2'),
		);
		$count = $model->table('sj_actives_soft')->where($where)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('sj_actives_soft') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('id DESC') -> select();
		//九宫格才有抽奖机会 
		if( $at == 5 ){
			$draw	=	$model->table('sj_activity')->where(array('id'=>$aid))->find();
		}else {
			$draw	=	'';
		}
		$this->assign('at', $at);
		$this->assign('aid', $aid);
		$this->assign('pid', $pid);
		$this->assign('draw', $draw);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	//配置软件抽奖机会
	public function soft_draw_chance()
	{
		$id		=	$_REQUEST['id'];
		$model	=	D('Sj.ReActivity');
		$table	=	'sj_activity';
		$where	=	array('id' => $id);
		if( $_POST ) {
			$ck		=	$this->check_activity_operation($id);
			if( !$ck ) {
				$this -> error("只能操作处于待审核状态的活动");
			}
			$red_chance_num = (Int)$_POST['red_chance_num'];
			if($red_chance_num <= 0) {
				$this->error('抽奖机会配置有误！');
			}	
			$map = array(
				'red_chance_num'	=>	$red_chance_num,
			);
			$res	=	$model -> table($table)->where($where) -> save($map);
			if($res){
				$this -> writelog("已编辑id为{$id}的软件抽奖机会",$table,$id,__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else {
			$draw = $model->table($table)->where($where)->find();
			$this->assign('id', $id);
			$this->assign('draw', $draw);
			$this->display();
		}
	}
	
	
	//输入包名智能验证包名是否存在
	public function pub_soft_val()
	{
		$package	=	trim($_GET['package']);
		$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if( $pkg_data ) {
			exit( json_encode(array('code'=>1,'msg'=>'','softname'=>$pkg_data['softname'])) );
		}else {
			exit( json_encode(array('code'=>0,'msg'=>'包名不存在')) );
		}
	}
	
	
	//添加任务软件库
	public function soft_add(){
		$pid = (Int)$_GET['pid'];
		if($_POST){
			$model	=	D('Sj.RedActivity');
			$table	=	"sj_actives_soft ";
			$ret	=	$model -> soft_validate($table);
			if($ret['code'] == 1) {
				$data	=	$ret['data'];
				$res = $model -> table($table) -> add($data);
				if($res){
					$this -> writelog("已添加id为{$res}的任务软件库",$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this->assign('pid', $pid);
			$this -> display();
		}
	}
	
	//编辑任务软件
	function soft_edit()
	{
		$id		=	$_REQUEST['id'];
		$pid	=	$_REQUEST['pid'];
		$model	=	D('Sj.RedActivity');
		$table	=	"sj_actives_soft";
		$where	=	array('id' => $id);
		if($_POST) {
			$ret = $model -> soft_validate($table);
			if($ret['code'] == 1){
				$data	=	$ret['data'];
				$res	=	$model -> table($table)->where($where) -> save($data);
				if($res){
					$this -> writelog("已编辑id为{$id}的软件任务",$table,$id,__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$list	=	$model->table($table)->where($where)->find();
			$this -> assign('id', $id);
			$this -> assign('pid', $pid);
			$this -> assign('list',$list);
			$this -> display('soft_add');
		}
	}
	
	//任务软件系列操作
	function soft_operation()
	{
		$id = !empty($_GET['id']) ? (Int)$_GET['id'] : 0;
		$operation = !empty($_GET['operation']) ? $_GET['operation'] : '';
		$oper_arr = array(
				'del'	=>	array(0, '删除'), //删除
				'qd'	=>	array(1, '启动'), //启动
				'stp'	=>	array(2, '停用'), //停用
		);
		if( !$id || !$operation || !array_key_exists($operation, $oper_arr) ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.RedActivity');
		$table	=	'sj_actives_soft';
		$where	=	array('id' => $id);
		switch ( $oper_arr[$operation][0] ) {
			case 0:
				//删除
				$result = $model->table($table)->where($where)->save(array('status'=>0));
				break;
			case 1:
				//启动
				$result = $model->table($table)->where($where)->save(array('status'=>1));
				break;
			case 2:
				//停用
				$result = $model->table($table)->where($where)->save(array('status'=>2));
				break;
		}
		if( $result ) {
			$this -> writelog("已{$oper_arr[$operation][1]}id为{$id}的软件任务",$table,$id,__ACTION__ ,'','edit');
			$this->success('操作成功');
		}else {
			$this->error('操作失败');
		}
	}
	
	function soft_import()
	{
		$pid = (Int)$_REQUEST['pid'];//页面pid
		if($_POST) {
			$model		=	M('');
			$ad_file	=	$_FILES['ad_file'];
			$time		=	strtotime(date('Y-m-d', time()));
			if(!$ad_file['size']) {
				$this->error('请上传文件');
			}
			$ext	=	pathinfo( $ad_file['name'] );
			if( strtolower($ext['extension']) != 'csv' ) {
				$this->error('请上传csv格式文件');
			}
			$shili	 = fopen($ad_file['tmp_name'], "r");
			$package = $info = $repeat_pack = $not_found_pack=array();
			$str = '';
			while ( !feof($shili) ) {
				$shi = fgets($shili, 1024);
				$a = explode(',', $shi);
				if(count($a)>5){
					$this->error("文件格式错误");
				}
				$str .= $shi . ",";
			}
			$str_arr = explode("\r\n", $str);
			$i = 0;
			foreach($str_arr as $key => $val) {
				if(empty($val)||$val === ',,') {
					continue;
				}
				if($key==0){
					continue;
				}else{
					$pack = trim($val, ',');
				}
				$pkg_data = $model->table('sj_soft')->where(array('package'=>$pack,'status'=>1,'hide'=>1))->find();
				if($pkg_data) {
					$pack	=	trim($pack);
					$info[$i]['package']	=	$pack;
					$info[$i]['softname']	=	$pkg_data['softname'];
					$package[]	=	$pack;
					$i++;
				}else{
					$not_found_pack[]	=	$pack;
				}
			}
	
			$not_found_pack	=	array_unique(array_filter($not_found_pack));
			$num = count($package);
			$this->assign('num', $num);
			$this->assign('not_found_pack', $not_found_pack);
			$j_info = base64_encode(json_encode($info));
			$this->assign('pid', $pid);
			$this->assign('j_info', $j_info);
			$this->assign('info', $info);
			$this->display('soft_import_view');
		}else {
			$this->assign('pid', $pid);
			$this->display();
		}
	}
	
	//导入数据库
	function soft_import_up()
	{
		$model		=	D('Sj.RedActivity');
		$pid		=	$_POST['pid'];
		$info		=	json_decode(base64_decode($_POST['info']),true);
		$key_arr	=	explode(',',trim($_POST['id'],','));
		$id_str		=	'';
		$repeat_pack	=	array();
		$import_count	=	0;
		$time			=	time();
		foreach( $info as $k=>$v ) {
			if(!in_array($k, $key_arr)) {
				continue;
			}
			$condtion = array(
					'page_id'	=>	$pid,
					'package'	=>	$v['package'],
					'status'	=>	array('in','1,2'),
			);
			//检查是否重复添加
			$row = $model->table('sj_actives_soft')->where($condtion)->find();
			if( !empty($row) ) {
				$repeat_pack[] = $v['package'];
				continue;
			}
			$data	=	array(
					'page_id'	=>	$pid,
					'soft_name'	=>	$v['softname'],
					'package'	=>	$v['package'],
					'status'	=>	1,
					'update_tm'	=>	$time,
					'create_tm'	=>	$time,
			);
			//根据活动页面id寻找活动页面分类
			$category = $model->table('sj_actives_category')->where(array('active_id'=>$pid))->find();
			if($category) {
				$data['category_id'] = $category['id'];
			}
			$res = $model->table('sj_actives_soft')->add($data);
			if($res){
				$import_count++;//导入成功的软件
				$id_str .= $res.',';
			}
		}
		if($id_str){
			$this->writelog("添加了id为{$id_str}的软件任务", 'sj_actives_soft', $id_str,__ACTION__,'','add');
		}
		$count = count($key_arr);//选中的软件
		if(count($repeat_pack)) {
			$import_count_failure = $count - $import_count;//冲突的软件
			$str='';
			if($import_count) {
				$str="成功导入软件：{$import_count}个  ";
			}
			$str.="软件冲突：{$import_count_failure}个  ";
			foreach($repeat_pack as $k=>$v ) {
				$str .= $v.',';
			}
			echo json_encode(array('code'=>2,'msg'=>$str));
		}else {
			echo json_encode(array('code'=>1,'msg'=>"成功导入软件：{$count}个"));
		}
	}
	
	//获取发布中的分享活动
	function check_heifer_activity( $aid )
	{
		$redis	=	new Redis();
		$task_config	=	C('task_redis');
		$redis -> connect($task_config['host'], $task_config['port']);
		$rs		=	$redis->get('v65UCENTER_TASK_T16_LIST');
		$rss	=	json_decode($rs, true);
		$aid_arr=	$rss[$aid];
		if($aid_arr) {
			return true;
		}else {
			return false;
		}
	}
	
	//检查 活动是否可以操作
	function check_activity_operation( $aid )
	{
		$table	=	'sj_activity';
		$model	=	D('Sj.RedActivity');
		$where	=	array('id'	=>	$aid, 'status'	=>	2);
		$rows = $model->table($table)->where($where)->find();
		if($rows) {
			return true;
		}else {
			return false;
		}
	}
	
}