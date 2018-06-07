<?php
class DeliveryAction extends CommonAction{
	/**
	 * 渠道列表
	 */
	function ch_list()
	{
		$keyword	=	!empty($_GET['keyword']) ? addslashes(trim($_GET['keyword'])) : null;
		$start_time	=	!empty($_GET['start_time']) ? strtotime($_GET['start_time']) : null;
		$end_time	=	!empty($_GET['end_time']) ? strtotime($_GET['end_time']) : null;
		$co_type	=	!empty($_GET['co_type']) ? $_GET['co_type'] : 0;
		
		if(strtotime($_GET['start_time'].'-01') > strtotime($_GET['end_time'].'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$where = ' status = 1';

		$co_type	&&	$where .= " and co_type = {$co_type}";
		$keyword	&&	$where .= " and name like '%{$keyword}%' ";
		$start_time	&&	$where .= " and create_tm >= {$start_time}";
		$end_time	&&	$where .= " and create_tm <= {$end_time}";
		$where_go['_string'] = $where;
		
		$model = D('Channel_cooperation.toufang');
                $model_newgo = new Model();//市场model

		$count = $model->table('tf_channel')->where($where_go)->count();
		import("@.ORG.Page");
		$param		=	http_build_query($_GET);
		$Page		=	new Page($count, 15, $param);
		$show		=	$Page->show();
		$list		=	$model->table('tf_channel') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('create_tm DESC') -> select();
		$time		=	time();
		$time_str	=	date('Y-m-d', $time);
		if( !empty($list) ) {
			foreach ( $list as $k => $v ) {
				$condtion = array(
						'channel_id'	=>	$v['id'],
						'ef_date'		=>	array('ELT', $time_str),
						'status'		=>	1,
				);
				$row	=	$model->table('tf_split_ratio')->where($condtion)->order('ef_date desc, update_tm desc')->find();
				if( empty($row) ) {
					//如何当日取不到 取即将开始的分成比
					$condtion_lt = array(
						'channel_id'	=>	$v['id'],
						'ef_date'		=>	array('gt', $time_str),
						'status'		=>	1,
					);
					$row = $model->table('tf_split_ratio')->where($condtion_lt)->order('ef_date asc')->find();
				}
				//增加税率
				$tax_where = array(
						'channel_id'	=>	$v['id'],
						'sl_date'		=>	array('ELT', $time_str),
						'status'		=>	1,
				);
				$tax_row	=	$model->table('tf_tax_rate')->where($tax_where)->order('sl_date desc, update_tm desc')->find();
				if( empty($tax_row) ) {
					//如何当月取不到 取即将开始的税率
					$tax_condtion_lt = array(
						'channel_id'	=>	$v['id'],
						'sl_date'		=>	array('gt', $time_str),
						'status'		=>	1,
					);
					$tax_row = $model->table('tf_tax_rate')->where($tax_condtion_lt)->order('sl_date asc')->find();
				}
				$condtion_soft = array(
						'channel_id'	=>	$v['id'],
						'status'		=>	1,
						'type'			=>	0,
						'tf_date'		=>	array('ELT', $time_str),
				);
				$count_soft	=	$model->table('tf_soft')->where($condtion_soft)->count();
				$list[$k]['anzhi']		=	isset($row['anzhi']) ? $row['anzhi'] : '';
				$list[$k]['other']		=	isset($row['other']) ? $row['other'] : '';
				$list[$k]['ef_date']	=	isset($row['ef_date']) ? $row['ef_date'] : '';
				//增加税率
				$list[$k]['tax_rate']		=	isset($tax_row['tax_rate']) ? $tax_row['tax_rate'] : '';
				$list[$k]['sl_date']	=	isset($tax_row['sl_date']) ? $tax_row['sl_date'] : '';
				
                                $list[$k]['count_soft']	=	$count_soft ? $count_soft : 0;

                                if($v['cid']!=0){
                                    $sdk_channel_info = $model_newgo->table('sdk_channel')->where(array('market_c_id'=>$v['cid']))->find();
                                    $list[$k]['sdk_channel']	=	$sdk_channel_info['channel_name'] ? $sdk_channel_info['channel_name'] : '无';
                                }else{
                                    $list[$k]['sdk_channel'] = '无';
                                }
			}
		}
		
		$this->assign('page', $show);
		$this->assign('keyword', $keyword);
		$this->assign('co_type', $co_type);
		$this->assign('start_time', $_GET['start_time']);
		$this->assign('end_time', $_GET['end_time']);
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 渠道添加
	 */
	function ch_add()
	{
		if (!empty($_POST)) {
			$name		=	!empty($_POST['name']) ? addslashes(trim($_POST['name'])) : null;
			//增加了渠道id和渠道编码
			$cid		=	!empty($_POST['cid']) ? trim($_POST['cid']) : 0;
			$chl_cid		=	!empty($_POST['chl_cid']) ? addslashes(trim($_POST['chl_cid'])) : null;
			
			$username	=	!empty($_POST['username']) ? addslashes(trim($_POST['username'])) : null;
			$password	=	!empty($_POST['password']) ? addslashes(trim($_POST['password'])) : null;
			$co_type	=	$_POST['co_type'];
			$kou_set	=	is_numeric($_POST['kou_set']) ? floatval($_POST['kou_set']) : null;
			$kou_set_in	=	is_numeric($_POST['kou_set_in']) ? floatval($_POST['kou_set_in']) : null;
			$ratio_a	=	is_numeric($_POST['ratio_a']) ? floatval($_POST['ratio_a']) : null;
			$ratio_b	=	is_numeric($_POST['ratio_b']) ? floatval($_POST['ratio_b']) : null;
			$ef_date	=	!empty($_POST['ef_date']) ? $_POST['ef_date'] : null;
			
			//增加了税率
			$tax_rate	=	is_numeric($_POST['tax_rate']) ? floatval($_POST['tax_rate']) : null;
			$sl_date	=	!empty($_POST['sl_date']) ? $_POST['sl_date'].'-00' : null;
			
			$time		=	time();
			
			$pattern		=	"/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/";
			$pattern_two	=	'/^[0-9a-zA-Z]*$/';
			if( !$name ) {
				$this->error('渠道名称不能为空');
			}
			if( preg_match($pattern, $name) )
			{
				$this->error('渠道名称只能输入中文数字下划线字母');
			}
			if( !$username ) {
				$this->error('账号不能为空');
			}
			if( !preg_match($pattern_two, $username) )
			{
				$this->error('账号只能输数字和英文大小写的字符');
			}
			if( !$password ) {
				$this->error('密码不能为空');
			}
			if( strlen($password) < 4 ) {
				$this->error('密码设置不能少于4个字符');
			}
			if( !preg_match($pattern_two, $password) )
			{
				$this->error('密码只能输数字和英文大小写的字符');
			}
			if( $kou_set < 0 ) {
				$this->error('扣量系数-渠道用不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $kou_set)) {
				$this->error('扣量系数-渠道用只能为整数或小数二位');
			}
			if( $kou_set > 100 ) {
				$this->error('扣量系数-渠道用不能超过100');
			}
			if( $kou_set_in < 0 ) {
				$this->error('扣量系数-内部用不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $kou_set_in)) {
				$this->error('扣量系数-内部用只能为整数或小数二位');
			}
			if( $kou_set_in > 100 ) {
				$this->error('扣量系数-内部用不能超过100');
			}
			if( $ratio_a < 0 ) {	
				$this->error('安智分成比不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $ratio_a)) {
				$this->error('安智分成比只能为整数或小数二位');
			}
			if( $ratio_a > 100 ) {
				$this->error('安智分成比不能超过100');
			}
			if( $ratio_b < 0 ) {	
				$this->error('渠道分成比不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $ratio_b)) {
				$this->error('渠道分成比只能为整数或小数二位');
			}
			if( $ratio_b > 100 ) {
				$this->error('渠道分成比不能超过100');
			}
			if( !$ef_date ) {
				$this->error('生效日期不能为空');
			}
			if( !strtotime($ef_date)) {
				$this->error('日期格式不对');
			}
			if( $ef_date < date('Y-m-d', $time) ) {
				$this->error('分成比例的生效日期只能填写今天和今天之后的日期');
			}
			
			//增加的税率和生效日期
			if( !$tax_rate ) {
				$this->error('税率不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $tax_rate)) {
				$this->error('税率只能为整数或小数二位');
			}
			if( $tax_rate > 100 ) {
				$this->error('税率不能超过100');
			}
			if( !$sl_date ) {
				$this->error('税率生效日期不能为空');
			}
			if( !strtotime($sl_date)) {
				$this->error('税率日期格式不对');
			}
			if( $sl_date < date('Y-m', $time) ) {
				$this->error('税率的生效日期只能填写当月和当月之后的日期');
			}
			
			
			$model	=	D('Channel_cooperation.toufang');
			
			$condition_ch['name']	=	$name;
			$condition_ch['status']	=	1;
			$row_ch	=	$model->table('tf_channel')->where($condition_ch)->find();
			if( $row_ch ) {
				$this->error('渠道名称不能重复');
			}
			$condition['username']	=	$username;
			$condition['status']	=	1;
			$row	=	$model->table('tf_channel')->where($condition)->find();
			if( $row ) {
				$this->error('用户名不能重复');
			}
			
			$map = array(
				'name'		=>	$name,
				'kou_set'	=>	$kou_set,
				'kou_set_in'=>  $kou_set_in,
				'username'	=>	$username,
				'password'	=>	$password,
				'create_tm'	=>	$time,
				'update_tm'	=>	$time,
				'cid'		=>	$cid,
				'chl_cid'	=>	$chl_cid,
				'status'	=>	1,
				'co_type'	=>	$co_type,
			);
			$channel_id = $model->table('tf_channel')->add($map);
			
			$map_split = array(
				'anzhi'			=>	$ratio_a,
				'other'			=>	$ratio_b,
				'create_tm'		=>	$time,
				'update_tm'		=>	$time,
				'status'		=>	1,
				'ef_date'		=>	$ef_date,
				'channel_id'	=>	$channel_id,
				'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);
			
			$map_tax = array(
				'sl_date'		=>	$sl_date,
				'tax_rate'		=>	$tax_rate,
				'create_tm'		=>	$time,
				'update_tm'		=>	$time,
				'status'		=>	1,
				'channel_id'	=>	$channel_id,
				'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);

			$map_kouset = array(
				'start_date'	=>	date('Y-m-d', $time),
				'kou_set'		=>	$kou_set,
				'create_tm'		=>	$time,
				'update_tm'		=>	$time,
				'status'		=>	1,
				'channel_id'	=>	$channel_id,
				'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);

			$map_kouset_in = array(
				'start_date'	=>	date('Y-m-d', $time),
				'kou_set_in'	=>	$kou_set_in,
				'create_tm'		=>	$time,
				'update_tm'		=>	$time,
				'status'		=>	1,
				'channel_id'	=>	$channel_id,
				'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);
			
			if( $channel_id ) {
				$this->writelog("在软件外投管理中添加了id为[{$channel_id}]的渠道", 'tf_channel', $channel_id,__ACTION__,"","add");
				
				$split_id = $model->table('tf_split_ratio')->add($map_split);
				if( $split_id ) {
					$this->writelog("在软件外投管理中的配置分成比例管理中添加了channel_id为[{$channel_id}]分成id为[{$split_id}]的配置分成比", 'tf_split_ratio', $split_id,__ACTION__,"","add");
				}
				
				$tax_id = $model->table('tf_tax_rate')->add($map_tax);
				if( $tax_id ) {
					$this->writelog("在软件外投管理中的配置税率管理中添加了channel_id为[{$channel_id}]税率id为[{$tax_id}]的配置税率", 'tf_tax_rate', $tax_id,__ACTION__,"","add");
				}
				
				$kouset_id = $model->table('tf_kouset')->add($map_kouset);
				if( $kouset_id ){
					$this->writelog("在软件外投管理中的历史扣量系数表(渠道用)中添加了channel_id为[{$channel_id}]主键id为[{$kouset_id}]的扣量系数", 'tf_kouset', $kouset_id,__ACTION__,"","add");
				}

				$kouset_in_id = $model->table('tf_kouset_in')->add($map_kouset_in);
				if( $kouset_in_id ){
					$this->writelog("在软件外投管理中的历史扣量系数表(内部用)中添加了channel_id为[{$channel_id}]主键id为[{$kouset_in_id}]的扣量系数", 'tf_kouset_in', $kouset_in_id,__ACTION__,"","add");
				}

				$this->success('添加成功');
			}else {
				$this->error('添加失败');
			}
		} else {
			$this->display();
		}
	}
	
	/**
	 * 渠道编辑
	 */
	function ch_edit()
	{	
		$id			=	is_numeric($_REQUEST['id']) ? (int) $_REQUEST['id'] : null;
		$time		=	time();
		$time_str	=	date('Y-m-d', $time);
		$where = array(
				'id' => $id
		);
		$where_split = array(
				'channel_id' =>	$id,
				'ef_date'	 =>	array('ELT',date('Y-m-d', time())),
				'status'	 =>	1,
		);
		$where_tax = array(
				'channel_id' =>	$id,
				'sl_date'	 =>	array('ELT',date('Y-m-00', time())),
				'status'	 =>	1,
		);
		$model	=	D('Channel_cooperation.toufang');
		if (!empty($_POST)) {
			$name		=	!empty($_POST['name']) ? addslashes(trim($_POST['name'])) : null;
			//增加了渠道id和渠道编码
			$cid		=	!empty($_POST['cid']) ? trim($_POST['cid']) : 0;
			$chl_cid		=	!empty($_POST['chl_cid']) ? addslashes(trim($_POST['chl_cid'])) : null;
			
			$username	=	!empty($_POST['username']) ? addslashes(trim($_POST['username'])) : null;
			$password	=	!empty($_POST['password']) ? addslashes(trim($_POST['password'])) : null;
			$kou_set	=	is_numeric($_POST['kou_set']) ? floatval($_POST['kou_set']) : null;//新扣量系数-渠道用
			$kouset		=	is_numeric($_POST['kouset']) ? floatval($_POST['kouset']) : null;//原扣量系数-渠道用
			$kou_set_in	=	is_numeric($_POST['kou_set_in']) ? floatval($_POST['kou_set_in']) : null;//新扣量系数-内部用
			$kouset_in		=	is_numeric($_POST['kouset_in']) ? floatval($_POST['kouset_in']) : null;//原扣量系数-内部用
			
			$pattern		=	"/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/";
			$pattern_two	=	'/^[0-9a-zA-Z]*$/';
			if( !$name ) {
				$this->error('渠道名称不能为空');
			}
			if( preg_match($pattern, $name) )
			{
				$this->error('渠道名称只能输入中文数字下划线字母');
			}
			if( !$username ) {
				$this->error('账号不能为空');
			}
			if( !preg_match($pattern_two, $username) ) {
				$this->error('账号只能输数字和英文大小写的字符');
			}
			if( !$password ) {
				$this->error('密码不能为空');
			}
			if( strlen($password) < 4 ) {
				$this->error('密码设置不能少于4个字符');
			}
			if( !preg_match($pattern_two, $password) ) {
				$this->error('密码只能输数字和英文大小写的字符');
			}
			if( $kou_set < 0 ) {
				$this->error('扣量系数-渠道用不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $kou_set)) {
				$this->error('扣量系数-渠道用只能为整数或小数二位');
			}
			if( $kou_set > 100 ) {
				$this->error('扣量系数-渠道用不能超过100');
			}
			if( $kou_set_in < 0 ) {
				$this->error('扣量系数-内部用不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $kou_set_in)) {
				$this->error('扣量系数-内部用只能为整数或小数二位');
			}
			if( $kou_set_in > 100 ) {
				$this->error('扣量系数-内部用不能超过100');
			}
			
			$condition_ch['name'] = $name;
			$condition_ch['status'] = 1;
			$row_ch	=	$model->table('tf_channel')->where($condition_ch)->find();
			if( $row_ch && $row_ch['id'] != $id ) {
				$this->error('渠道名称不能重复');
			}
			$condition['username'] = $username;
			$condition['status'] = 1;
			$row	=	$model->table('tf_channel')->where($condition)->find();
			if( $row && $row['id'] != $id ) {
				$this->error('用户名不能重复');
			}
			
			$map = array(
					'name'		=>	$name,
					'cid'		=>	$cid,
					'chl_cid'	=>	$chl_cid,
					'username'	=>	$username,
					'kou_set'	=>	$kou_set,
					'kou_set_in'=>  $kou_set_in,
					'password'	=>	$password,
			);
			//如果改了扣量系数则修改更新时间
			if( $kouset != $kou_set || $kouset_in != $kou_set_in) {
				$map['update_tm'] = $time;
			}
			
			$result = $model->table('tf_channel')->where($where)->save($map);
			if ($result) {
				$this->writelog("在软件外投管理中编辑了id为[{$id}]渠道名称为{$name}的渠道", 'tf_channel', $id,__ACTION__,"","edit");
				
				if( $kouset != $kou_set){
					$map_kouset = array(
						'start_date'	=>	date('Y-m-d', $time),
						'kou_set'		=>	$kou_set,
						'create_tm'		=>	$time,
						'update_tm'		=>	$time,
						'status'		=>	1,
						'channel_id'	=>	$id,
						'os_user'		=>	$_SESSION['admin']['admin_user_name'],
					);
					$kouset_id = $model->table('tf_kouset')->add($map_kouset);
					if( $kouset_id ){
						$this->writelog("在软件外投管理中的历史扣量系数表(渠道用)中添加了channel_id为[{$id}]主键id为[{$kouset_id}]的扣量系数", 'tf_kouset', $kouset_id,__ACTION__,"","add");
					}
				}

				if( $kouset_in != $kou_set_in ){
					$map_kouset_in = array(
						'start_date'	=>	date('Y-m-d', $time),
						'kou_set_in'	=>	$kou_set_in,
						'create_tm'		=>	$time,
						'update_tm'		=>	$time,
						'status'		=>	1,
						'channel_id'	=>	$id,
						'os_user'		=>	$_SESSION['admin']['admin_user_name'],
					);
					$kouset_in_id = $model->table('tf_kouset_in')->add($map_kouset_in);
					if( $kouset_in_id ){
						$this->writelog("在软件外投管理中的历史扣量系数表(内部用)中添加了channel_id为[{$id}]主键id为[{$kouset_in_id}]的扣量系数", 'tf_kouset_in', $kouset_in_id,__ACTION__,"","add");
					}
				}

				$this->success('编辑成功');
			}else {
				$this->error('编辑失败');
			}
		} else {
			$list			=	$model->table('tf_channel')->where($where)->find();
			$list_split		=	$model->table('tf_split_ratio')->where($where_split)->order('ef_date desc,update_tm desc')->find();
			if( empty($list_split) ) {
				//如何当日取不到 取即将开始的分成比
				$condtion_lt = array(
						'channel_id'	=>	$id,
						'ef_date'		=>	array('gt', $time_str),
						'status'		=>	1,
				);
				$list_split = $model->table('tf_split_ratio')->where($condtion_lt)->order('ef_date asc')->find();
			}
			$list_tax =	$model->table('tf_tax_rate')->where($where_tax)->order('sl_date desc,update_tm desc')->find();
			if( empty($list_tax) ) {
				//如何当月取不到 取即将开始的税率
				$tax_condtion_lt = array(
						'channel_id'	=>	$id,
						'sl_date'		=>	array('gt', $time_str),
						'status'		=>	1,
				);
				$list_tax = $model->table('tf_tax_rate')->where($tax_condtion_lt)->order('sl_date asc')->find();
			}
			
			$this->assign('list', $list);
			$this->assign('list_split', $list_split);
			$this->assign('list_tax', $list_tax);
			$this->display();
		}
	}
	
	/**
	 * 渠道删除
	 */
	function ch_delete()
	{
		$id = !empty($_REQUEST['id']) ? trim($_REQUEST['id'],',') : null;
		if( !$id ) {
			$this -> error('删除失败！');
		}
		$ids	=	is_array(id) ? implode(',', $id) : $id;
		$model	=	D('Channel_cooperation.toufang');
		//删除渠道
		$model->table('tf_channel')->where(array('id' => array('in',$ids)))->save(array('status' => 0));
		//删除渠道下分成比例
		$model->table('tf_split_ratio')->where(array('channel_id' => array('in',$ids)))->save(array('status' => 0));
		//删除渠道下税率
		$model->table('tf_tax_rate')->where(array('channel_id' => array('in',$ids)))->save(array('status' => 0));
		//删除渠道下的软件按
		$model->table('tf_soft')->where(array('channel_id' => array('in',$ids)))->save(array('status'=>0,'type'=>1));
		$this->writelog("在软件外投管理中删除了id为[{$ids}]的渠道", 'tf_channel', $ids,__ACTION__ ,"","del");
		$this->writelog("在软件外投管理中的配置分成比例管理中删除了channel_id为[{$ids}]的配置分成比", 'tf_split_ratio', $ids,__ACTION__,"","del");
		$this->writelog("在软件外投管理中的配置税率管理中删除了channel_id为[{$ids}]的配置税率", 'tf_tax_rate', $ids,__ACTION__,"","del");
		$this->writelog("在渠道软件管理中删除了channel_id为[{$ids}]的软件", 'tf_soft', $ids,__ACTION__ ,"","del");
		$this->success('删除成功');
	}
	
	/**
	 * 扣量系数列表(渠道用)
	 */
	function kouset_list()
	{
		$channel_id	= is_numeric($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		if( !$channel_id ) {
			$this->error('渠道id为空！');
		}

		$where = "channel_id = {$channel_id} and status = 1";
		$order = 'update_tm desc';
		$where_go['_string'] = $where;

		$model = D('Channel_cooperation.toufang');
		$count = $model->table('tf_kouset')->where($where_go)->count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 15, $param);
		$show = $Page->show();
		$list = $model->table('tf_kouset')->where($where_go)->limit($Page->firstRow.','.$Page->listRows)->order($order)->select();

		$this->assign('page', $show);
		$this->assign('channel_id', $channel_id);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}

	/**
	 * 扣量系数列表(内部用)
	 */
	function kouset_in_list()
	{
		$channel_id	= is_numeric($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		if( !$channel_id ) {
			$this->error('渠道id为空！');
		}

		$where = "channel_id = {$channel_id} and status = 1";
		$order = 'update_tm desc';
		$where_go['_string'] = $where;

		$model = D('Channel_cooperation.toufang');
		$count = $model->table('tf_kouset_in')->where($where_go)->count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 15, $param);
		$show = $Page->show();
		$list = $model->table('tf_kouset_in')->where($where_go)->limit($Page->firstRow.','.$Page->listRows)->order($order)->select();

		$this->assign('page', $show);
		$this->assign('channel_id', $channel_id);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}

	/**
	 * 配置分成比列表
	 */
	function split_list()
	{
		$channel_id	=	is_numeric($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		$time_type	=	!empty($_GET['time_type']) ? $_GET['time_type'] : null;
		if( !$channel_id ) {
			$this -> error('渠道id为空！');
		}
		$time	=	date('Y-m-d H:i:s', time());
		$where	=	" channel_id = {$channel_id} and status = 1 ";
		switch ($time_type) {
			case 'gt':
				//过期
				$where .= " and ef_date <= '{$time}' ";
				$order  = 'ef_date desc, update_tm desc';
				break;
			case 'eq':
				//当前
				$where .= " and ef_date <= '{$time}' ";
				$order  = 'ef_date desc, update_tm desc ';
				break;
			case 'lt':
				//未开始
				$where .= " and ef_date > '{$time}' ";
				$order  = 'ef_date asc';
				break;
			default:
				$where .= " and ef_date  <= '{$time}' ";
				$order  = 'ef_date desc, update_tm desc ';
				$time_type = 'eq';
				break;
		}
		$model	=	D('Channel_cooperation.toufang');
		$where_go['_string'] = $where;
		$count	=	$model->table('tf_split_ratio')->where($where_go)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		if( $time_type == 'eq' ) {
			$list	=	$model->table('tf_split_ratio') -> where($where_go)-> limit('0,1') ->order($order) -> select();
		}else {
			$list	=	$model->table('tf_split_ratio') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
			//过期处理
			if( $time_type == 'gt' ) {
				unset ($list[0]);
			}
		}
		$this->assign('page', $show);
		$this->assign('channel_id', $channel_id);
		$this->assign('page', $show);
		$this->assign('time_type', $time_type);
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 配置分成比添加
	 */
	function split_add()
	{
		$channel_id	=	is_numeric($_REQUEST['channel_id']) ? (int) $_REQUEST['channel_id'] : null;
		if (!empty($_POST)) {
			$ratio_a	=	is_numeric($_POST['ratio_a']) ? floatval($_POST['ratio_a']) : null;
			$ratio_b	=	is_numeric($_POST['ratio_b']) ? floatval($_POST['ratio_b']) : null;
			$ef_date	=	!empty($_POST['ef_date']) ? $_POST['ef_date'] : null;
			$time		=	time();
			$time_str	=	date('Y-m-d', $time);
				
			if( $ratio_a < 0 ) {
				$this->error('安智分成比不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $ratio_a)) {
				$this->error('安智分成比只能为整数或小数二位');
			}
			if( $ratio_a > 100 ) {
				$this->error('安智分成比不能超过100');
			}
			if( $ratio_b < 0 ) {
				$this->error('渠道分成比不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $ratio_b)) {
				$this->error('渠道分成比只能为整数或小数二位');
			}
			if( $ratio_b > 100 ) {
				$this->error('渠道分成比不能超过100');
			}
			if( !$ef_date ) {
				$this->error('生效日期不能为空');
			}
			if( !strtotime($ef_date)) {
				$this->error('日期格式不对');
			}
			if( $ef_date < $time_str ) {
				$this->error('分成比例的生效日期只能填写今天和今天之后的日期');
			}

			$model	=	D('Channel_cooperation.toufang');
			
			$where	=	array(
						'channel_id'	=>	$channel_id,
						'ef_date'		=>	$ef_date,
						'status'		=>	1,
				); 
			$row = $model->table('tf_split_ratio')->where($where)->find();
			if( $row && ($ef_date != $time_str) ) {
				$this->error('该时间段已经添加过了');
			}
			
			$map_split = array(
					'anzhi'			=>	$ratio_a,
					'other'			=>	$ratio_b,
					'create_tm'		=>	$time,
					'update_tm'		=>	$time,
					'status'		=>	1,
					'ef_date'		=>	$ef_date,
					'channel_id'	=>	$channel_id,
					'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);
			$split_id = $model->table('tf_split_ratio')->add($map_split);
			if( $split_id ) {
				$this->writelog("在软件外投管理中的配置分成比例管理中添加了id为[{$split_id}]的配置分成比", 'tf_split_ratio', $split_id,__ACTION__ ,"","add");
				$this->success('添加成功');
			}else {
				$this->error('添加失败');
			}
		} else {
			$this->assign('channel_id', $channel_id);
			$this->display();
		}
	}
	
	/**
	 * 配置分成比更新
	 */
	function split_edit()
	{
		$id = is_numeric($_REQUEST['id']) ? (int) $_REQUEST['id'] : null;
		$where_split = array(
				'id' => $id
		);
		$model	=	D('Channel_cooperation.toufang');
		if (!empty($_POST)) {
			$id			=	is_numeric($_POST['id']) ? (int) $_POST['id'] : null;
			$channel_id	=	is_numeric($_POST['channel_id']) ? (int) $_POST['channel_id'] : null;
			$ratio_a	=	is_numeric($_POST['ratio_a']) ? floatval($_POST['ratio_a']) : null;
			$ratio_b	=	is_numeric($_POST['ratio_b']) ? floatval($_POST['ratio_b']) : null;
			$ef_date	=	!empty($_POST['ef_date']) ? $_POST['ef_date'] : null;
			$time		=	time();
			$time_str	=	date('Y-m-d', $time);
		
			if( $ratio_a < 0 ) {
				$this->error('安智分成比不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $ratio_a)) {
				$this->error('安智分成比只能为整数或小数二位');
			}
			if( $ratio_a > 100 ) {
				$this->error('安智分成比不能超过100');
			}
			if( $ratio_b < 0 ) {
				$this->error('渠道分成比不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $ratio_b)) {
				$this->error('渠道分成比只能为整数或小数二位');
			}
			if( $ratio_b > 100 ) {
				$this->error('渠道分成比不能超过100');
			}
			if( !$ef_date ) {
				$this->error('生效日期不能为空');
			}
			if( !strtotime($ef_date)) {
				$this->error('日期格式不对');
			}
			if( $ef_date < $time_str ) {
				$this->error('分成比例的生效日期只能填写今天之后的日期');
			}

			$where	=	array(
				'id'			=>	$id,
				'channel_id'	=>	$channel_id,
				'status'		=>	1,
			);
			$row = $model->table('tf_split_ratio')->where($where)->find();
			if( $row  && $row['ef_date'] != $ef_date) {
				$this->error('该时间段已经添加过了');
			}

			$map_split = array(
				'anzhi'			=>	$ratio_a,
				'other'			=>	$ratio_b,
				'update_tm'		=>	$time,
				'status'		=>	1,
				'ef_date'		=>	$ef_date,
				'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);
				
			$result = $model->table('tf_split_ratio')->where($where_split)->save($map_split);
			if ($result) {
				$this->writelog("在软件外投管理中的配置分成比例管理中编辑了id为[{$id}]的配置分成比", 'tf_split_ratio', $id,__ACTION__,"","edit");
				$this->success('编辑成功');
			}else {
				$this->error('编辑失败');
			}
		} else {
			$list	=	$model->table('tf_split_ratio')->where($where_split)->find();
			$this->assign('list', $list);
			$this->display();
		}
	}
	
	/**
	 * 配置分成比删除
	 */
	function split_delete()
	{
		$id = !empty($_REQUEST['id']) ? trim($_REQUEST['id'],',') : null;
		if( !$id ) {
			$this -> error('删除失败！');
		}
		$model	=	D('Channel_cooperation.toufang');
		$ids = is_array(id) ? implode(',', $id) : $id;
		$map = array(
				'status' 	=>	0,
				'update_tm'	=>	time(),
				'os_user'	=>	$_SESSION['admin']['admin_user_name'],
		);
		$model->table('tf_split_ratio')->where(array('id' => array('in',$ids)))->save(array('status' => 0));
		$this->writelog("在软件外投管理中的配置分成比例管理中删除了id为[{$ids}]的配置分成比", 'tf_split_ratio', $ids,__ACTION__ ,"","del");
		$this->success('删除成功');
	}
	
	/**
	 * 配置税率列表 
	 */
	function tax_rate_list()
	{
		$channel_id	=	is_numeric($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		$time_type	=	!empty($_GET['time_type']) ? $_GET['time_type'] : null;
		if( !$channel_id ) {
			$this -> error('渠道id为空！');
		}
		$time	=	date('Y-m-00', time());
		$where	=	" channel_id = {$channel_id} and status = 1 ";
		switch ($time_type) {
			case 'gt':
				//过期
				$where .= " and sl_date < '{$time}' ";
				$order  = 'sl_date desc, update_tm desc';
				break;
			case 'eq':
				//当前
				$where .= " and sl_date = '{$time}' ";
				$order  = 'sl_date desc, update_tm desc ';
				break;
			case 'lt':
				//未开始
				$where .= " and sl_date > '{$time}' ";
				$order  = 'sl_date asc';
				break;
			default:
				$where .= " and sl_date  = '{$time}' ";
				$order  = 'sl_date desc, update_tm desc ';
				$time_type = 'eq';
				break;
		}
		$model	=	D('Channel_cooperation.toufang');
		$where_go['_string'] = $where;
		$count	=	$model->table('tf_tax_rate')->where($where_go)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('tf_tax_rate') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		$this->assign('page', $show);
		$this->assign('channel_id', $channel_id);
		$this->assign('page', $show);
		$this->assign('time_type', $time_type);
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 配置税率添加
	 */
	function tax_rate_add()
	{
		$channel_id	=	is_numeric($_REQUEST['channel_id']) ? (int) $_REQUEST['channel_id'] : null;
		if (!empty($_POST)) {
			$tax_rate	=	is_numeric($_POST['tax_rate']) ? floatval($_POST['tax_rate']) : null;
			$sl_date	=	!empty($_POST['sl_date']) ? $_POST['sl_date'].'-00' : null;
			$time		=	time();
			$time_str	=	date('Y-m-00', $time);
				
			if( $tax_rate < 0 ) {
				$this->error('税率不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $tax_rate)) {
				$this->error('税率只能为整数或小数二位');
			}
			if( $tax_rate > 100 ) {
				$this->error('税率不能超过100');
			}
			if( !$sl_date ) {
				$this->error('生效日期不能为空');
			}
			if( !strtotime($sl_date)) {
				$this->error('日期格式不对');
			}
			if( $sl_date < $time_str ) {
				$this->error('税率的生效日期只能填写当月和当月之后的日期');
			}

			$model	=	D('Channel_cooperation.toufang');
			
			$where	=	array(
						'channel_id'	=>	$channel_id,
						'sl_date'		=>	$sl_date,
						'status'		=>	1,
				); 
			$row = $model->table('tf_tax_rate')->where($where)->find();
			if( $row ) {
				$this->error('该月份已经添加过了');
			}
			
			$map_tax = array(
					'tax_rate'			=>	$tax_rate,
					'create_tm'		=>	$time,
					'update_tm'		=>	$time,
					'status'		=>	1,
					'sl_date'		=>	$sl_date,
					'channel_id'	=>	$channel_id,
					'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);
			$tax_id = $model->table('tf_tax_rate')->add($map_tax);
			if( $tax_id ) {
				$this->writelog("在软件外投管理中的配置税率管理中添加了id为[{$tax_id}]的配置税率", 'tf_tax_rate', $tax_id,__ACTION__ ,"","add");
				$this->success('添加成功');
			}else {
				$this->error('添加失败');
			}
		} else {
			$this->assign('channel_id', $channel_id);
			$this->display();
		}
	}
	
	/**
	 * 配置税率更新
	 */
	function tax_rate_edit()
	{
		$id = is_numeric($_REQUEST['id']) ? (int) $_REQUEST['id'] : null;
		$where_tax = array(
				'id' => $id
		);
		$model	=	D('Channel_cooperation.toufang');
		if (!empty($_POST)) {
			$id			=	is_numeric($_POST['id']) ? (int) $_POST['id'] : null;
			$channel_id	=	is_numeric($_POST['channel_id']) ? (int) $_POST['channel_id'] : null;
			$tax_rate	=	is_numeric($_POST['tax_rate']) ? floatval($_POST['tax_rate']) : null;
			$sl_date	=	!empty($_POST['sl_date']) ? $_POST['sl_date'].'-00' : null;
			$time		=	time();
			$time_str	=	date('Y-m-00', $time);
		
			if( $tax_rate < 0 ) {
				$this->error('税率不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $tax_rate)) {
				$this->error('税率只能为整数或小数二位');
			}
			if( $tax_rate > 100 ) {
				$this->error('税率不能超过100');
			}
			if( !strtotime($sl_date)) {
				$this->error('日期格式不对');
			}
			if( $sl_date < $time_str ) {
				$this->error('税率的生效日期只能填写当月和当月之后的日期');
			}

			$where	=	array(
					'id'			=>	array('neq',$id),
					'channel_id'	=>	$channel_id,
					'status'		=>	1,
					'sl_date'		=>  $sl_date,
				); 
			$row = $model->table('tf_tax_rate')->where($where)->select();
			
			if( $row ) {
				$this->error('该月份已经添加过了');
			}

			$map_tax = array(
					'tax_rate'			=>	$tax_rate,
					'update_tm'		=>	$time,
					'status'		=>	1,
					'sl_date'		=>	$sl_date,
					'os_user'		=>	$_SESSION['admin']['admin_user_name'],
			);
				
			$result = $model->table('tf_tax_rate')->where($where_tax)->save($map_tax);
			if ($result) {
				$this->writelog("在软件外投管理中的配置税率管理中编辑了id为[{$id}]的配置税率", 'tf_tax_rate', $id,__ACTION__,"","edit");
				$this->success('编辑成功');
			}else {
				$this->error('编辑失败');
			}
		} else {
			$list	=	$model->table('tf_tax_rate')->where($where_tax)->find();
			$this->assign('list', $list);
			$this->display();
		}
	}
	
	/**
	 * 配置税率删除
	 */
	function tax_rate_delete()
	{
		$id = !empty($_REQUEST['id']) ? trim($_REQUEST['id'],',') : null;
		if( !$id ) {
			$this -> error('删除失败！');
		}
		$model	=	D('Channel_cooperation.toufang');
		$ids = is_array(id) ? implode(',', $id) : $id;
		$map = array(
				'status' 	=>	0,
				'update_tm'	=>	time(),
				'os_user'	=>	$_SESSION['admin']['admin_user_name'],
		);
		$model->table('tf_tax_rate')->where(array('id' => array('in',$ids)))->save(array('status' => 0));
		$this->writelog("在软件外投管理中的配置税率管理中删除了id为[{$ids}]的配置税率", 'tf_tax_rate', $ids,__ACTION__ ,"","del");
		$this->success('删除成功');
	}
	
	
	/**
	 * 软件管理列表
	 */
	function soft_list()
	{
		$channel_id	=	!empty($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		$soft_name	=	!empty($_GET['soft_name']) ? addslashes(trim($_GET['soft_name'])) : null;
		$package	=	!empty($_GET['package']) ? addslashes(trim($_GET['package'])) : null;
		$start_time	=	!empty($_GET['start_time']) ? $_GET['start_time'] : null;
		$end_time	=	!empty($_GET['end_time']) ? $_GET['end_time'] : null;
		$time_type	=	!empty($_GET['time_type']) ? $_GET['time_type'] : 'eq';
		$time		=	date('Y-m-d', time());
		
		if( $end_time &&  $start_time > $end_time ) {
			$this->error('开始时间不能大于结束时间');
		}
		
		$where = ' status = 1 ';
		$channel_id	&&	$where .= " and channel_id = {$channel_id}";
		$soft_name	&&	$where .= " and softname like '%{$soft_name}%' ";
		$package	&&	$where .= " and package = '{$package}'";
		$start_time	&&	$where .= " and tf_date >= '{$start_time}'";
		$end_time	&&	$where .= " and tf_date <= '{$end_time}'";

		if( !$start_time || !$end_time ) {
			switch ($time_type) {
				case 'gt':
					//过期
					$where .= " and type = 1 ";
					$order  = " tf_date desc";
					break;
				case 'eq':
					//当前
					$where .= " and tf_date <= '{$time}' and type = 0 ";
					$order  = " tf_date asc";
					break;
				case 'lt':
					//未开始
					$where .= " and tf_date > '{$time}' ";
					$order  = " tf_date asc";
					break;
				default:
					//$where .= " and tf_date  <= '{$time}' and type = 0 ";
					//$time_type = 'eq';
					$order  = " tf_date asc";
					break;
			}
		}else {
			$time_type = '';
		}
		$where_go['_string'] = $where;
		$model = D('Channel_cooperation.toufang');
		$count = $model->table('tf_soft')->where($where_go)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('tf_soft') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		if( !empty($list) ) {
			foreach ( $list as $k => $v ) {
				$condtion = array(
						'id'		=>	$v['channel_id'],
						'status'	=>	1,
				);
				$row = $model->table('tf_channel')->where($condtion)->find();
				$list[$k]['channel_name']	=	isset($row['name']) ? $row['name'] : '';
			}
		}
		
		$channel_list	=	$model->table('tf_channel')->where(array('status'=>1))->select();
		$this->assign('page', $show);
		$this->assign('time_type', $time_type);
		$this->assign('IMGATT_HOST', IMGATT_HOST);
		$this->assign('channel_list', $channel_list);
		$this->assign('channel_id', $channel_id);
		$this->assign('soft_name', $soft_name);
		$this->assign('package', $package);
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('list', $list);
		$this->display();
	}
	
	function soft_add()
	{
		$model	=	D('Channel_cooperation.toufang');
		$channel_list	=	$model->table('tf_channel')->where(array('status'=>1))->select();
		if (!empty($_POST)){
			$channel_id	=	!empty($_POST['channel_id']) ? $_POST['channel_id'] : null;
			$package	=	!empty($_POST['package']) ? addslashes(trim($_POST['package'])) : null;
			$price		=	trim($_POST['price']);
			$tf_date	=	!empty($_POST['tf_date']) ? date('Y-m-d', strtotime($_POST['tf_date'])) : null;
			$time		=	time();
			$time_str	=	date('Y-m-d', $time);

			$introduce	=	!empty($_POST['introduce']) ? addslashes(trim($_POST['introduce'])) : null;
			$banner = $iconurl_72 =  null;
			if ($_FILES['iconurl_72']['tmp_name']) {
				if($_FILES['iconurl_72']['size']>1024*5){
					$this->error('icon图标大小不能超过5k');exit;
				}
				$icon = getimagesize($_FILES['iconurl_72']['tmp_name']);
				if($icon[0] != '72' || $icon[1] != '72'){
					$this->error('icon图片尺寸不符合要求');exit;
				}
				$path = date("Ym/d/");
				$config['multi_config']['iconurl_72'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			} else {
				$this->error('请上传icon图标');exit;
			}
			if ($_FILES['banner']['tmp_name']) {
				if($_FILES['banner']['size']>1024*20){
					$this->error('banner图大小不能超过20k');exit;
				}
				$ban = getimagesize($_FILES['banner']['tmp_name']);
				if($ban[0] != '660' || $ban[1] != '180'){
					$this->error('banner图片尺寸不符合要求');exit;
				}
				$path = date("Ym/d/");
				$config['multi_config']['banner'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if(!empty($config['multi_config'])){
				$list = $this->_uploadapk(0, $config);
				foreach ($list['image'] as $val) {
			        if ($val['post_name'] == 'iconurl_72') {
			            $iconurl_72 = $val['url'];
			        }
			        if ($val['post_name'] == 'banner') {
			            $banner = $val['url'];
			        }
			    }
			}
			
			if (mb_strlen($introduce, 'utf-8') > 13) {
				$this->error('简介最多13个字');
			}
			if( !$channel_id ) {
				$this->error('请选择渠道');
			}			
			if( !$package ) {
				$this->error('软件包名不能为空');
			}
			if( $price == '' ) {
				$this->error('单价不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price)) {
				$this->error('单价只能为整数或小数二位');
			}
			if( !$tf_date ) {
				$this->error('投放日期不能为空');
			}
			if( !strtotime($tf_date)) {
				$this->error('日期格式不对');
			}
			if( $tf_date < $time_str ) {
				$this->error('软件投放日期只能填写今天和今天之后的日期');
			}
				
			$model_soft = M('');
			$soft_info  = $model_soft->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
			if( empty($soft_info) ) {
				$this->error('该软件不存在');
			}
			
			$condtion = array(
					'channel_id'=>	$channel_id,
					'package'	=>	$package,
					'tf_date'	=>	$tf_date,
					'status'	=>	1,
					'type'		=>	0,
			);
			$row = $model->table('tf_soft')->where($condtion)->find();
			if( !empty($row) ) {
				$this->error('软件时间冲突');
			}
		
			//只有是当前投放才结束当前对应的软件
			if( $tf_date == $time_str ) {
					$where_sf	=	array(
							'channel_id'	=>	$channel_id,
							'package'		=>	$package,
							'tf_date'		=>	array('LT', $time_str),
							'status'		=>	1,
							'type'			=>	0,
					);
					$rows = $model->table('tf_soft')->where($where_sf)->select();
					if( !empty($rows) ) {
						$id_arr = array();
						foreach ( $rows as $val ) {
							$id_arr[] = $val['id'];
						}
						$ids		=	implode(',', $id_arr);
						$where_go	=	array('id'=> array('in',$ids));
						$model->table('tf_soft')->where($where_go)->save(array('type'=>1));
					}
			}
			
			$map = array(
					'package'	=>	$package,
					'beid'	=>	$_POST['beid']?$_POST['beid']:0,
					'softname'	=>	$soft_info['softname'],
					'price'		=>	$price,
					'tf_date'	=>	$tf_date,
					'update_tm'	=>	$time,
					'create_tm'	=>	$time,
					'os_user'	=>	$_SESSION['admin']['admin_user_name'],
					'status'	=>	1,
					'type'		=>	0,
					'channel_id'=>	$channel_id,
					'introduce' =>  $introduce,
					'banner'    =>  $banner,
					'iconurl_72' => $iconurl_72,
			);
			$soft_id = $model->table('tf_soft')->add($map);
				
			if( $soft_id ) {
				$this->writelog("在渠道软件投管理中添加了id为[{$soft_id}]的软件", 'tf_soft', $soft_id,__ACTION__ ,"","add");
				$this->success('添加成功');
			}else {
				$this->error('添加失败');
			}
		} else {
			$this->assign('channel_list', $channel_list);
			$this->display();
		}
	}
	
	/**
	 * 软件编辑
	 */
	function soft_edit()
	{
		$id		=	!empty($_REQUEST['id']) ? $_REQUEST['id'] : null;
		$model	=	D('Channel_cooperation.toufang');
		$where	=	array(
						'id'	=>	$id
					);
		if (!empty($_POST)){
			$channel_id		=	!empty($_POST['channel_id']) ? $_POST['channel_id'] : null;
			$package		=	!empty($_POST['package']) ? addslashes(trim($_POST['package'])) : null;
			$beid =	!empty($_POST['beid']) ? $_POST['beid'] : 0;
			$price			=	trim($_POST['price']);
			$tf_date		=	!empty($_POST['tf_date']) ? date('Y-m-d', strtotime($_POST['tf_date'])) : null;
			$time			=	time();
			$time_str		=	date('Y-m-d', $time);
			
			$introduce	=	!empty($_POST['introduce']) ? addslashes(trim($_POST['introduce'])) : null;
			$banner		=	!empty($_POST['banner_v']) ? $_POST['banner_v'] : null;
			$iconurl_72 =  !empty($_POST['icon_v']) ? $_POST['icon_v'] : null;
			if ($_FILES['iconurl_72']['tmp_name']) {
				if($_FILES['iconurl_72']['size']>1024*5){
					$this->error('icon图标大小不能超过5k');
					exit;
				}
				$icon = getimagesize($_FILES['iconurl_72']['tmp_name']);
				if($icon[0] != '72' || $icon[1] != '72'){
					$this->error('icon图片尺寸不符合要求');
					exit;
				}
				$path = date("Ym/d/");
				$config['multi_config']['iconurl_72'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if ($_FILES['banner']['tmp_name']) {
				if($_FILES['banner']['size']>1024*20){
					$this->error('banner图大小不能超过20k');
				}
				$ban = getimagesize($_FILES['banner']['tmp_name']);
				if($ban[0] != 660 || $ban[1] != 180){
					$this->error('banner图片尺寸不符合要求');
				}
				$path = date("Ym/d/");
				$config['multi_config']['banner'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if(!empty($config['multi_config'])){
				$list = $this->_uploadapk(0, $config);
				foreach ($list['image'] as $val) {
			        if ($val['post_name'] == 'iconurl_72') {
			            $iconurl_72 = $val['url'];
			        }
			        if ($val['post_name'] == 'banner') {
			            $banner = $val['url'];
			        }
			    }
			}
			if (mb_strlen($introduce, 'utf-8') > 13) {
				$this->error('简介最多13个字');
			}
			if( !$channel_id ) {
				$this->error('请选择渠道');
			}
			if( !$package ) {
				$this->error('软件包名不能为空');
			}
			if( $price == '' ) {
				$this->error('单价不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price)) {
				$this->error('单价只能为整数或小数二位');
			}
			if( !$tf_date ) {
				$this->error('投放日期不能为空');
			}
			if( !strtotime($tf_date) ) {
				$this->error('日期格式不对');
			}
			if( $tf_date < $time_str ) {
				$this->error('软件投放日期只能填写今天和今天之后的日期');
			}
		
			$model_soft = M('');
			$soft_info  = $model_soft->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
			if( empty($soft_info) ) {
				$this->error('该软件不存在');
			}
			
			$condtion = array(
					'channel_id'=>	$channel_id,
					'package'	=>	$package,
					'tf_date'	=>	$tf_date,
					'status'	=>	1,
					'type'		=>	0,
			);
			$row = $model->table('tf_soft')->where($condtion)->find();
			if( !empty($row)  && $row['id'] != $id ) {
				$this->error('软件时间冲突');
			}
			
			//只有是当前投放才结束当前对应的软件
			if( $tf_date == $time_str ) {
					$where_sf	=	array(
							'channel_id'	=>	$channel_id,
							'package'		=>	$package,
							'tf_date'		=>	array('LT', $time_str),
							'status'		=>	1,
							'type'			=>	0,
					);
					$rows = $model->table('tf_soft')->where($where_sf)->select();
					if( !empty($rows) ) {
						$id_arr = array();
						foreach ( $rows as $val ) {
							$id_arr[] = $val['id'];
						}
						$ids		=	implode(',', $id_arr);
						$where_go	=	array('id'=> array('in',$ids));
						$model->table('tf_soft')->where($where_go)->save(array('type'=>1));
					}
			}
			
			$map = array(
					'package'	=>	$package,
					'beid' =>  $beid,
					'softname'	=>	$soft_info['softname'],
					'price'		=>	$price,
					'tf_date'	=>	$tf_date,
					'update_tm'	=>	$time,
					'os_user'	=>	$_SESSION['admin']['admin_user_name'],
					'channel_id'=>	$channel_id,
					'introduce' =>  $introduce,
					'banner'    =>  $banner,
					'iconurl_72' => $iconurl_72,
			);
			
			$result = $model->table('tf_soft')->where($where)->save($map);
			
			if( $result ) {
				$this->writelog("在渠道软件管理中编辑了id为[{$id}]的软件", 'tf_soft', $id,__ACTION__ ,"","edit");
				$this->success('修改成功');
			}else {
				$this->error('修改失败');
			}
		} else {
			$soft_info		=	$model->table('tf_soft')->where($where)->find();
			$channel_list	=	$model->table('tf_channel')->where(array('status'=>1))->select();
			$this->assign('IMGATT_HOST', IMGATT_HOST);
			$this->assign('soft_info', $soft_info);
			$this->assign('channel_list', $channel_list);
			$this->display();
		}
	}

	/**
	 * 软件更新
	 */
	function soft_update()
	{
		$id		=	!empty($_REQUEST['id']) ? $_REQUEST['id'] : null;
		$model	=	D('Channel_cooperation.toufang');
		$where	=	array(
						'id'	=>	$id
					);
		if (!empty($_POST)){
			$package		=	!empty($_POST['package']) ? addslashes(trim($_POST['package'])) : null;
			$time			=	time();	
			$introduce	=	!empty($_POST['introduce']) ? addslashes(trim($_POST['introduce'])) : null;
			$beid =	!empty($_POST['beid']) ? $_POST['beid'] : 0;
			$banner		=	!empty($_POST['banner_v']) ? $_POST['banner_v'] : null;
			$iconurl_72 =  !empty($_POST['icon_v']) ? $_POST['icon_v'] : null;
			if ($_FILES['iconurl_72']['tmp_name']) {
				if($_FILES['iconurl_72']['size']>1024*5){
					$this->error('icon图标大小不能超过5k');exit;
				}
				$icon = getimagesize($_FILES['iconurl_72']['tmp_name']);
				if($icon[0] != '72' || $icon[1] != '72'){
					$this->error('icon图片尺寸不符合要求');exit;
				}
				$path = date("Ym/d/");
				$config['multi_config']['iconurl_72'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if ($_FILES['banner']['tmp_name']) {
				if($_FILES['banner']['size']>1024*20){
					$this->error('banner图大小不能超过20k');
				}
				$ban = getimagesize($_FILES['banner']['tmp_name']);
				if($ban[0] != 660 || $ban[1] != 180){
					$this->error('banner图片尺寸不符合要求');
				}
				$path = date("Ym/d/");
				$config['multi_config']['banner'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if(!empty($config['multi_config'])){
				$list = $this->_uploadapk(0, $config);
				foreach ($list['image'] as $val) {
			        if ($val['post_name'] == 'iconurl_72') {
			            $iconurl_72 = $val['url'];
			        }
			        if ($val['post_name'] == 'banner') {
			            $banner = $val['url'];
			        }
			    }
			}
			if (mb_strlen($introduce, 'utf-8') > 13) {
				$this->error('简介最多13个字');
			}
			if( !$package ) {
				$this->error('软件包名不能为空');
			}
			$model_soft = M('');
			$soft_info  = $model_soft->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
			if( empty($soft_info) ) {
				$this->error('该软件不存在');
			}
			
			$map = array(
					'package'	=>	$package,
					'softname'	=>	$soft_info['softname'],
					'update_tm'	=>	$time,
					'os_user'	=>	$_SESSION['admin']['admin_user_name'],
					'introduce' =>  $introduce,
					'beid' =>  $beid,
					'banner'    =>  $banner,
					'iconurl_72' => $iconurl_72,
			);
			
			$result = $model->table('tf_soft')->where($where)->save($map);
                        //echo $model->getlastsql();exit;
			
			if( $result ) {
				$this->writelog("在渠道软件管理中更新了id为[{$id}]的软件", 'tf_soft', $id,__ACTION__ ,"","update");
				$this->success('更新成功');
			}else {
				$this->error('更新失败');
			}
		} else {
			$soft_info		=	$model->table('tf_soft')->where($where)->find();
			$channel_list	=	$model->table('tf_channel')->where(array('status'=>1,'id'=>$soft_info['channel_id']))->find();
			$this->assign('IMGATT_HOST', IMGATT_HOST);
			$this->assign('soft_info', $soft_info);
			$this->assign('channel_list', $channel_list);
			$this->display();
		}
	}
	
	/**
	 * 软件删除
	 */
	function soft_delete()
	{
		$id		=	!empty($_REQUEST['id']) ? trim($_REQUEST['id'],',') : null;
		$type	=	!empty($_REQUEST['type']) ? $_REQUEST['type'] : null;
		$time	=	time();
		if( !$id ) {
			$this -> error('删除失败！');
		}
		$model	=	D('Channel_cooperation.toufang');
		$ids = is_array(id) ? implode(',', $id) : $id;
		if($type) {
			//结束
			$str = '结束';
			$map = array(
					'type'		=>	1,
					'os_user'	=>	$_SESSION['admin']['admin_user_name'],
					'update_tm'	=>	$time,
			);
		}else {
			//删除
			$str = '删除';
			$map = array(
					'status'	=>	0,
					'type'		=>	1,
					'os_user'	=>	$_SESSION['admin']['admin_user_name'],
					'update_tm'	=>	$time,
			);
		}
		$model->table('tf_soft')->where(array('id' => array('in',$ids)))->save($map);
		$this->writelog("在渠道软件管理中{$str}了id为[{$ids}]的软件", 'tf_soft', $ids,__ACTION__ ,"","del");
		$this->success("{$str}成功");
	}

	/**
	 * 批量修改软件单价
	 */
	function soft_price_batch()
	{
		$id		=	!empty($_REQUEST['id']) ? trim($_REQUEST['id'],',') : null;
		if (!empty($_POST)) {
			$price	=	trim($_REQUEST['price']);
			if( !$id ) {
				$this -> error('删除失败！');
			}
			if( $price == '' ) {
				$this->error('单价不能为空');
			}
			if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price)) {
				$this->error('单价只能为整数或小数二位');
			}
			$time	=	date('Y-m-d', time());
			$ids	=	is_array(id) ? implode(',', $id) : $id;
			
			$model	=	D('Channel_cooperation.toufang');
			//验证投放时间必须是未开始的
			$row	=	$model->table('tf_soft')->where(array('id' => array('in',$ids),'tf_date'=>array('ELT', $time)))->find();
			if( !empty($row) ) {
				$this->error('操作项中投放时间有误');
			}
			$map	=	array( 
							'price'		=>	$price,
							'update_tm'	=>	time(),
							'os_user'	=>	$_SESSION['admin']['admin_user_name'],
			);
			$result	=	$model->table('tf_soft')->where(array('id' => array('in',$ids)))->save($map);
			if( $result ) {
				$this->writelog("在渠道软件管理中编辑了id为[{$ids}]的软件的单价", 'tf_soft', $ids,__ACTION__,"","edit");
				$this->success('修改成功');
			}else {
				$this->error('修改失败');
			}
		}else {
			$idarr	=	!empty($id) ? explode(',', $id) : null;
			$total	=	count($idarr);
			$this->assign('total', $total);
			$this->assign('id', $id);
			$this->display();
		}
	}
	
	/**
	 * 软件批量导入
	 */
	function soft_import()
	{
		$channel_id	=	!empty($_REQUEST['channel_id']) ? (int)$_REQUEST['channel_id'] : null;
		if( $_FILES ) {
			$model		=	M('');
			$ad_file	=	$_FILES['ad_file'];
			$time		=	strtotime(date('Y-m-d', time()));
			if(!$channel_id) {
				$this->error('请选择渠道');
			}
			if(!$ad_file['size']) {
				$this->error('请上传文件');
			}
			$ext	=	pathinfo( $ad_file['name'] );
			if( strtolower($ext['extension']) != 'csv' ) {
				$this->error('请上传csv格式文件');
			}
			$shili	 = fopen($ad_file['tmp_name'], "r");
			$package = $info = $repeat_pack = $not_found_pack= $no_price= $time_error = $intro_error = array();
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
					list($a,$pack,$price,$start_at,$introduce,$beid) = explode(',',$val);
				}
				/*echo '<pre>';
			print_r($pack);
			exit('</pre>');*/
				$pkg_data=$model->table('sj_soft')->where(array('package'=>$pack,'status'=>1,'hide'=>1))->find();
				if($pkg_data) {
						$introduce = iconv('gb2312', 'utf-8', $introduce);
						$introduce = addslashes(trim($introduce));
						if (mb_strlen($introduce, 'utf-8') > 13) {
							$intro_error[] = $pack;
							continue;
						}
						if($price=='' || !is_numeric($price) || $price <0){
							$no_price[] = $pack;
							continue;
						}
						$price = floatval($price);
						if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price)) {
							$no_price[] = $pack;
							continue;
						}
						
						$tf_data = !empty($start_at) ? explode('/', $start_at) : null;
						if( !$tf_data || !strtotime($tf_data[1]) ) {
							$time_error[] = $pack;
							continue;
						}
						if( strtotime(date('Y-m-d', strtotime($tf_data[1]))) < $time ) {
							//分成比例的生效日期只能填写今天和今天之后的日期
							$time_error[] = $pack;
							continue;
						}
						
						$pack	=	trim($pack);
						$info[$i]['package']		=	$pack;
						$info[$i]['price']			=	$price;
						$info[$i]['beid']			=	$beid;
						$info[$i]['tf_date']		=	$tf_data[1];
						$info[$i]['softname']		=	$pkg_data['softname'];
						$info[$i]['introduce']		=	$introduce;
						$package[]	=	$pack;
						$i++;
				}else{
					$not_found_pack[]	=	$pack;
				}
			}
			
			$not_found_pack	=	array_unique(array_filter($not_found_pack));
			$num = count($package);
			$this->assign('num', $num);
			$this->assign('channel_id', $channel_id);
			$this->assign('not_found_pack', $not_found_pack);

			$j_info = base64_encode(json_encode($info));
			$this->assign('j_info', $j_info);
			$this->assign('info', $info);
			$this->assign('no_price', $no_price);
			$this->assign('time_error', $time_error);
			$this->assign('intro_error', $intro_error);
			$this->display('soft_import_view');
		}else {
			$model_toufang	=	D('Channel_cooperation.toufang');
			$channel_list	=	$model_toufang->table('tf_channel')->where(array('status'=>1))->select();
			$this->assign('channel_list',$channel_list);
			$this->assign('channel_id',$channel_id);
			$this->display();
		}	
	}
	
	/**
	 * 渠道软件批量导入POST
	 */
	function soft_import_up()
	{
		$model		=	D('Channel_cooperation.toufang');
		$info		=	json_decode(base64_decode($_POST['info']),true);
		$key_arr	=	explode(',',trim($_POST['id'],','));
		$channel_id	=	!empty($_POST['channel_id']) ? (int)$_POST['channel_id'] : null;
		$id_str		=	'';
		$repeat_pack	=	array();
		$import_count	=	0;
		$time			=	time();
		$time_str		=	date('Y-m-d', $time);
		if( !$channel_id ) {
			echo json_encode(array('code'=>2,'msg'=>'未选择渠道'));
		}
		foreach( $info as $k=>$v ) {
			if(!in_array($k, $key_arr)) {
				continue;
			}
			
			if( !$v['tf_date'] ) {
				$repeat_pack[] = $v['package'];
				continue;
			}
			
			$tf_date	=	date('Y-m-d', strtotime($v['tf_date']));
			
			if( $tf_date < $time_str ) {
				$repeat_pack[] = $v['package'];
				continue;
			}
			$condtion = array(
					'channel_id'=>	$channel_id,
					'package'	=>	$v['package'],
					'tf_date'	=>	$tf_date,
					'status'	=>	1,
					'type'		=>	0,
			);
			$row = $model->table('tf_soft')->where($condtion)->find();
			if( !empty($row) ) {
				$repeat_pack[] = $v['package'];
				continue;
			}
			
			//只有是当前投放才结束当前对应的软件
			if( $tf_date == $time_str ) {
					$where_sf	=	array(
							'channel_id'	=>	$channel_id,
							'package'		=>	$v['package'],
							'tf_date'		=>	array('LT', $time_str),
							'status'		=>	1,
							'type'			=>	0,
					);
					$rows = $model->table('tf_soft')->where($where_sf)->select();
					if( !empty($rows) ) {
						$id_arr = array();
						foreach ( $rows as $val ) {
							$id_arr[] = $val['id'];
						}
						$ids		=	implode(',', $id_arr);
						$where_go	=	array('id'=> array('in',$ids));
						$model->table('tf_soft')->where($where_go)->save(array('type'=>1));
					}
			}
			
			$data	=	array(
					'softname'	=>	$v['softname'],
					'package'	=>	$v['package'],
					'beid'	=>	$v['beid']?$v['beid']:0,
					'price'		=>	floatval($v['price']),
					'tf_date'	=>	$tf_date,
					'update_tm'	=>	$time,
					'create_tm'	=>	$time,
					'status'	=>	1,
					'type'		=>	0,
					'os_user'	=>	$_SESSION['admin']['admin_user_name'],
					'channel_id'=>	$channel_id,	
					'introduce' =>  $v['introduce'],
			);
			
			$res = $model->table('tf_soft')->add($data);
			if($res){
				$import_count++;//导入成功的软件
				$id_str .= $res.',';
			}
		}
		
		if($id_str){
			$this->writelog("在渠道软件管理中添加了id为{$id_str}的软件", 'tf_soft', $id_str,__ACTION__,'','add');
		}
		
		$count = count($key_arr);//选中的软件
		if(count($repeat_pack)) {
			$import_count_failure = $count - $import_count;//冲突的软件
			$str='';
			if($import_count) {
				$str="成功导入软件：{$import_count}个  ";
			}
			$str.="软件时间冲突：{$import_count_failure}个  ";
			foreach($repeat_pack as $k=>$v ) {
				$str .= $v.',';
			}
			echo json_encode(array('code'=>2,'msg'=>$str));
		}else {
			echo json_encode(array('code'=>1,'msg'=>"成功导入软件：{$count}个"));
		}
	}
	
	/**
	 * 渠道统计数据-渠道用
	 */
	function ch_count()
	{
		$channel_id	=	!empty($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		$soft_name	=	!empty($_GET['soft_name']) ? addslashes(trim($_GET['soft_name'])) : null;
		$package	=	!empty($_GET['package']) ? addslashes(trim($_GET['package'])) : null;
		$start_time	=	!empty($_GET['start_time']) ? $_GET['start_time'] : null;
		$end_time	=	!empty($_GET['end_time']) ? $_GET['end_time'] : null;
		$time_type	=	!empty($_GET['time_type']) ? $_GET['time_type'] : null;
		$export		=	!empty($_GET['export']) ? $_GET['export'] : null;
		$batch_id	=	!empty($_GET['batch_id']) ? trim($_GET['batch_id'],',') : null;
		$co_type	=	!empty($_GET['co_type']) ? $_GET['co_type'] : 0;
		$time		=	date('Y-m-d', time());
		
		if(strtotime($start_time.'-01') > strtotime($end_time.'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$where = '1=1';
		$co_type	&&	$where .= " and co_type = {$co_type}";
		$channel_id	&&	$where .= " and channel_id = {$channel_id}";
		$soft_name	&&	$where .= " and softname like '%{$soft_name}%' ";
		$package	&&	$where .= " and package = '{$package}'";
		$start_time	&&	$where .= " and date >= '{$start_time}'";
		$end_time	&&	$where .= " and date <= '{$end_time}'";
		
		$where_go['_string'] = $where;
		$model = D('Channel_cooperation.toufang');
		$count = $model->table('tf_statistics')->where($where_go)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 20, $param);
		$show	=	$Page->show();
		
		if( $batch_id ) {
			//批量导出
			$list	=	$model->table('tf_statistics') -> where( array('id' => array('in',$batch_id)) )->order('date DESC') -> select();
		}elseif( $export ) {
			//全部导出
			$list	=	$model->table('tf_statistics') -> where($where_go)->order('date DESC') -> select();
		}else {
			//正常
			$list	=	$model->table('tf_statistics') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('date DESC') -> select();
		}
		
		//是否导出报表
		if( $export || $batch_id ) {
			$filename = date('Ymd').'.csv'; //设置文件名
			$str = '日期,渠道名称,合作类型,已关联cps渠道,软件名称,软件包名,下载量,下载完成量,扣量系数,计费量（扣量前）,计费量（扣量后）,单价,分成比例(渠道),分成单价(渠道),渠道分成';
			$str = iconv('utf-8','gb2312', $str);
			$str = $str."\n";
			foreach ( $list as $val ) {
				$date			=	iconv('utf-8','gb2312', $val['date']);
				$channel_name	=	iconv('utf-8','gb2312', $val['channel_name']);
				$co_type		=	$val['co_type']==1 ? '按下载请求计费' : '按下载完成计费';
				$co_type		=	iconv('utf-8','gb2312', $co_type);
				$cps_name 		=	iconv('utf-8','gb2312', $val['cps_name']);
				$softname		=	iconv('utf-8','gb2312', $val['softname']);
				$package		=	iconv('utf-8','gb2312', $val['package']);
				$download_num	=	$val['download_num'];
				$downloaded_num	=	$val['downloaded_num'];
				$kou_set		=	$val['kou_set'] ? $val['kou_set'].'%' : '0%';
				$downum_front	=	$val['downum_front'];
				$downum_after	=	$val['downum_after'];
				$price			=	$val['price'];
				$other_fc		=	$val['other_fc'];
				$price_fc		=	$val['price_fc'];
				$anzhi_val		=	$val['anzhi_val'];
				$other_val		=	$val['other_val'];
				$str .=	$date.",".$channel_name.",".$co_type.",".$cps_name.",".$softname.",".$package.",".$download_num.",".$downloaded_num.",".$kou_set.",".$downum_front.",".$downum_after.",".$price.",".$other_fc.",".$price_fc.",".$other_val."\n"; //用引文逗号分开
			}
			$this->export_csv($filename,$str);die; //导出
		}
		
		$channel_list	=	$model->table('tf_channel')->select();
		
		$this->assign('page', $show);
		$this->assign('time_type', $time_type);
		$this->assign('channel_list', $channel_list);
		$this->assign('channel_id', $channel_id);
		$this->assign('soft_name', $soft_name);
		$this->assign('co_type', $co_type);
		$this->assign('package', $package);
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('list', $list);
		$this->display();
	}

	/**
	 * 渠道统计数据-内部用
	 */
	function ch_count2()
	{
		$channel_id	=	!empty($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		$soft_name	=	!empty($_GET['soft_name']) ? addslashes(trim($_GET['soft_name'])) : null;
		$package	=	!empty($_GET['package']) ? addslashes(trim($_GET['package'])) : null;
		$start_time	=	!empty($_GET['start_time']) ? $_GET['start_time'] : null;
		$end_time	=	!empty($_GET['end_time']) ? $_GET['end_time'] : null;
		$export		=	!empty($_GET['export']) ? $_GET['export'] : null;
		$batch_id	=	!empty($_GET['batch_id']) ? trim($_GET['batch_id'],',') : null;
		$co_type	=	!empty($_GET['co_type']) ? $_GET['co_type'] : 0;
		$time		=	date('Y-m-d', time());
		
		if(strtotime($start_time.'-01') > strtotime($end_time.'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$where = '1=1';
		$co_type	&&	$where .= " and co_type = {$co_type}";
		$channel_id	&&	$where .= " and channel_id = {$channel_id}";
		$soft_name	&&	$where .= " and softname like '%{$soft_name}%' ";
		$package	&&	$where .= " and package = '{$package}'";
		$start_time	&&	$where .= " and date >= '{$start_time}'";
		$end_time	&&	$where .= " and date <= '{$end_time}'";
		
		$where_go['_string'] = $where;
		$model = D('Channel_cooperation.toufang');
		$count = $model->table('tf_statistics')->where($where_go)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 20, $param);
		$show	=	$Page->show();
		
		if( $batch_id ) {
			//批量导出
			$list	=	$model->table('tf_statistics') -> where( array('id' => array('in',$batch_id)) )->order('date DESC') -> select();
		}elseif( $export ) {
			//全部导出
			$list	=	$model->table('tf_statistics') -> where($where_go)->order('date DESC') -> select();
		}else {
			//正常
			$list	=	$model->table('tf_statistics') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('date DESC') -> select();
		}
		
		//是否导出报表
		if( $export || $batch_id ) {
			$filename = date('Ymd').'.csv'; //设置文件名
			$str = '日期,渠道名称,合作类型,已关联cps渠道,软件名称,软件包名,下载量,下载完成量,扣量系数,计费量（扣量前）,计费量（扣量后）,单价,内部收入';
			$str = iconv('utf-8','gb2312', $str);
			$str = $str."\n";
			foreach ( $list as $val ) {
				$date				=	iconv('utf-8','gb2312', $val['date']);
				$channel_name		=	iconv('utf-8','gb2312', $val['channel_name']);
				$co_type			=	$val['co_type']==1 ? '按下载请求计费' : '按下载完成计费';
				$co_type			=	iconv('utf-8','gb2312', $co_type);
				$cps_name 			=	iconv('utf-8','gb2312', $val['cps_name']);
				$softname			=	iconv('utf-8','gb2312', $val['softname']);
				$package			=	iconv('utf-8','gb2312', $val['package']);
				$download_num		=	$val['download_num'];
				$downloaded_num		=	$val['downloaded_num'];
				$kou_set_in			=	$val['kou_set_in'] ? $val['kou_set_in'].'%' : '0%';
				$downum_front		=	$val['downum_front'];
				$downnum_after_in	=	$val['downnum_after_in'] ? $val['downnum_after_in'] : 0;
				$price				=	$val['price'];
				$total				=   $val['kou_set_in'] ? $val['price']*$downnum_after_in : 0;
				$str .=	$date.",".$channel_name.",".$co_type.",".$cps_name.",".$softname.",".$package.",".$download_num.",".$downloaded_num.",".$kou_set_in.",".$downum_front.",".$downnum_after_in.",".$price.','.$total."\n"; //用引文逗号分开
			}
			$this->export_csv($filename,$str);die; //导出
		}
		
		$channel_list	=	$model->table('tf_channel')->select();
		
		$this->assign('page', $show);
		$this->assign('channel_list', $channel_list);
		$this->assign('channel_id', $channel_id);
		$this->assign('soft_name', $soft_name);
		$this->assign('package', $package);
		$this->assign('start_time', $start_time);
		$this->assign('co_type', $co_type);
		$this->assign('end_time', $end_time);
		$this->assign('list', $list);
		$this->display();
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

	/**
	 * 财务审核列表
	 */
	function audit_list()
	{
		$channel_id	=	!empty($_GET['channel_id']) ? (int)$_GET['channel_id'] : null;
		$start_time	=	!empty($_GET['start_time']) ? $_GET['start_time'] : null;
		$end_time	=	!empty($_GET['end_time']) ? $_GET['end_time'] : null;
		$status		=	!empty($_GET['status']) ? (int)$_GET['status'] : null;
		$time		=	date('Y-m-d', time());
		
		if(strtotime($start_time.'-01') > strtotime($end_time.'-01')){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$where = '1=1';
		$channel_id	&&	$where .= " and channel_id = {$channel_id}";
		$start_time	&&	$where .= " and date >= '{$start_time}'";
		$end_time	&&	$where .= " and date <= '{$end_time}'";
		$status		&&	$where .= " and status = {$status}";
		
		$order = " `status` ASC, `date` DESC, payment_amount DESC";
		
		$where_go['_string'] = $where;
		$model = D('Channel_cooperation.toufang');
		$count = $model->table('tf_month_statistics')->where($where_go)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 20, $param);
		$show	=	$Page->show();
		
		$list			=	$model->table('tf_month_statistics') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		$channel_list	=	$model->table('tf_channel')->select();
		$st_data		=	array('1'=>'待审核','2'=>'待付款','3'=>'已付款','4'=>'已冻结','5'=>'解冻');
		
		$this->assign('page', $show);
		$this->assign('st_data', $st_data);
		$this->assign('channel_list', $channel_list);
		$this->assign('status', $status);
		$this->assign('channel_id', $channel_id);
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 财务审核列表导出
	 */
	function audit_export()
	{
		$batch_id	=	!empty($_GET['batch_id']) ? trim($_GET['batch_id'],',') : null;
		$model = D('Channel_cooperation.toufang');
		$order = " `status` ASC, `date` DESC, payment_amount DESC";
		if( $batch_id ) {
			//选择性导出
			$list	=	$model->table('tf_month_statistics') -> where( array('id' => array('in',$batch_id)) )->order('date DESC') -> select();
		}else {
			//全部导出
			$list	=	$model->table('tf_month_statistics') ->order('date DESC') -> select();
		}
		$st_data = array('1'=>'待审核','2'=>'待付款','3'=>'已付款','4'=>'已冻结','5'=>'解冻');
		$filename = '外投结算'.date('Y-m-d').'.csv'; //设置文件名
		$str = '月份,渠道名称,下载量（扣量前）,下载量（扣量后）,安智分成,渠道分成,补差,应付金额,付款金额,差额补齐,未付金额,发票金额,税率,备注,状态';
		$str = iconv('utf-8','gb2312', $str);
		$str = $str."\n";
		foreach ( $list as $val ) {
			$data			=	iconv('utf-8','gb2312', $val['date']);
			$channel_name	=	iconv('utf-8','gb2312', $val['channel_name']);
			$downum_front	=	$val['downum_front'];
			$downum_after	=	$val['downum_after'];
			$anzhi_val		=	$val['anzhi_val'];
			$other_val		=	$val['other_val'];
			$reserve_price	=	$val['reserve_price'];
			$yf_price		=	($val['other_val']+$val['reserve_price'])*0.01*(100-$val['tax_rate']);
			$payment_amount	=	$val['payment_amount'];
			$diff_complete	=	$val['payment_amount'] - (($val['other_val']+$val['reserve_price'])*0.01*(100-$val['tax_rate']));
			$wf_price		=	(($val['other_val']+$val['reserve_price'])*0.01*(100-$val['tax_rate']))-$val['payment_amount'];
			$invoice		=	$val['invoice'];
			$remarks		=	iconv('utf-8','gb2312', $val['remarks']);
			$status			=	iconv('utf-8','gb2312', $st_data[$val['status']]);
			$tax_rate		=	$val['tax_rate'];
			$str	.=	$data.",".$channel_name.",".$downum_front.",".$downum_after.",".$anzhi_val.",".$other_val.",".$reserve_price.",".$yf_price.",".$payment_amount.",".$diff_complete.",".$wf_price.",".$invoice.",".$tax_rate.",".$remarks.",".$status."\n"; //用引文逗号分开
		}
		$this->export_csv($filename,$str);
	}
	
	/**
	 * 修改差价
	 */
	function audit_price()
	{
		$id		=	!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$model	=	D('Channel_cooperation.toufang');
		$rows = $model->table('tf_month_statistics') -> where(array('id'=> $id)) -> find();
		if( !empty($_POST) ) {
			$price	=	!empty($_POST['price']) ? $_POST['price'] : 0;
			if( !preg_match('/^(-)?[0-9][0-9]*$/', $price) ) {
				$this->error('请输入正确的数字');
			}
			//文档公式 应付金额：=（渠道分成金额+补差）*（1-税率）
			$amount_payable = ($rows['other_val']+$price)*0.01*(100-$rows['tax_rate']);
			//未付金额=应付金额-付款金额
			$unpaid_amount = $amount_payable - $rows['payment_amount'];
			//差额补齐=付款金额-应付金额
			$diff_complete = $rows['payment_amount'] - $amount_payable;
			
			$map	=	array(
				'reserve_price' => $price,
				'amount_payable' => $amount_payable,
				'unpaid_amount' => $unpaid_amount,
				'diff_complete' => $diff_complete,
			);
			
			$where	=	array('id' => $id);
			$res = $model->table('tf_month_statistics')->where(array('id'=> $id))->save($map);
			if( $res ) {
				$this->writelog("在商务审核列表中的修改的补差金额id为[{$id}]", 'tf_month_statistics', $id,__ACTION__,"","edit");
				$this->success('修改成功');
			}else {
				$this->error('修改失败');
			}
		}else {
			//$rows = $model->table('tf_month_statistics') -> where(array('id'=> $id)) -> find();
			$this->assign('rows', $rows);
			$this->display();
		}
	}
	
	/**
	 * 修改备注
	 */
	function audit_remarks()
	{
		$id		=	!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$model	=	D('Channel_cooperation.toufang');
		if( !empty($_POST) ) {
			$remarks	=	!empty($_POST['remarks']) ? addslashes(trim(strip_tags($_POST['remarks']))) : '';
			$map	=	array('remarks' => $remarks);
			$where	=	array('id' => $id);
			$res = $model->table('tf_month_statistics')->where(array('id'=> $id))->save($map);
			if( $res ) {
				$this->writelog("在商务审核列表中的修改的备注id为[{$id}]", 'tf_month_statistics', $id,__ACTION__,"","edit");
				$this->success('修改成功');
			}else {
				$this->error('修改失败');
			}
		}else {
			$rows = $model->table('tf_month_statistics') -> where(array('id'=> $id)) -> find();
			$this->assign('rows', $rows);
			$this->display();
		}
	}
	
	/**
	 * 商务审核与结算操作
	 */
	function audit_operation()
	{
		$id		=	!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
		$option	=	!empty($_REQUEST['option']) ? $_REQUEST['option'] : null;
		$model	=	D('Channel_cooperation.toufang');
		switch ($option) {
			case 'tg':
				//通过
				$status = 2;
				break;
			case 'fk':
				//付款
				$status = 3;
				break;
			case 'dj':
				//冻结
				$status = 4;
				break;
			case 'jd':
				//解冻
				$status = 5;
				break;
			default:
				$this->error('操作有误');
				break;
		}
		if( !empty($_POST) || $option == 'tg' ) {
			$map = array('status'=>$status);
			if($option == 'tg') {
				$res = $model->table('tf_month_statistics')->where(array('id'=> $id))->save($map);
				if( $res ) {
					$this->writelog("在商务审核列表中的修改为通过id为[{$id}]", 'tf_month_statistics', $id,__ACTION__,"","edit");
					$this->success('修改成功');
				}else {
					$this->error('修改失败');
				}
			}
		}else {
			$rows = $model->table('tf_month_statistics') -> where(array('id'=> $id)) -> find();
			$this->assign('option', $option);
			$this->assign('rows', $rows);
			$this->display();
		}
	}
	function showchannel()
	{
        $tf_model	=	D('Channel_cooperation.toufang');
		$model = M();
        $keyword = $_GET['query'];
		$real_keyword = escape_string($keyword);
		$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;

        $where = array(
            'chname' => array('like', "%{$real_keyword}%"),
            'status' => 1
        );

        $list = $model->table('sj_channel')->where($where)->select();
        $data = array(
			'query' => $keyword,
			'suggestions' => array(),
			'data' => array(),
		);

        foreach($list as $v) {
			$data['suggestions'][] = $v['chname'];
			$data['data'][] = $v['cid'].'_'.$v['chl_cid'];
		}
        exit(json_encode($data));
	}

}
