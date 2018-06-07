<?php

class MarketgameAction extends CommonAction{

	function channel_manage_list(){
		$model = new Model();
		$result = $model -> table('sj_market_game') -> where(array('status' => 1)) -> select();;
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_channel_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_market_game') -> where(array('id' => $id)) -> select();
		$count = $model -> table('sj_market_game') -> where(array('status' => 1)) -> count();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_channel_do(){
		$model = new Model();
		$id = $_GET['id'];
		$name = $_GET['name'];
		$rank = $_GET['rank'];
		if(!$name){
			$this -> error("请填写名称");
		}
		$where_have_name['_string'] = "name = '{$name}' and status = 1 and id != {$id}";
		$have_name_result = $model -> table('sj_market_game') -> where($where_have_name) -> select();
		if($have_name_result){
			$this -> error("该名称已存在");
		}
		$been_result = $model -> table('sj_market_game') -> where(array('id' => $id)) -> select();
		$log_result = $this -> logcheck(array('id' => $id),'sj_market_game',array('name' => $name,'rank' => $rank),$model);
		$rank_result = $this -> select_rank($been_result[0]['rank'],$rank,'sj_market_game',$id);
		$name_result = $model -> table('sj_market_game') -> where(array('id' => $id)) -> save(array('name' => $name,'update_tm' => time()));
		if($name_result || $rank_result){
			$this -> writelog("已编辑id为{$id}的市场游戏子频道".$log_result,'sj_market_game',"{$id}",__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Marketgame/channel_manage_list');
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	
	function service_statement_list(){
		$model = new Model();
		if($_GET['type']){
			$type = $_GET['type'];
		}else{
			$type = 1;
		}
	
		if($_GET['my_time'] == 2){
			$where['_string'] = "end_tm < ".time()." and status = 1 and type = {$type}";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 1){
			$where['_string'] = "start_tm > ".time()." and status = 1 and type = {$type}";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 3 || !$_GET['my_time']){
			$_GET['my_time'] = 3;
			$where['_string'] = "start_tm < ".time()." and end_tm > ".time()." and status = 1 and type = {$type}";
			$order = "start_tm";
		}
		$count = $model -> table('sj_service_statement') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_service_statement') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
	
		foreach($result as $key => $val){
			$admin_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['admin_id'])) -> select();
			$val['admin_name'] = $admin_result[0]['admin_user_name'];
			$result[$key] = $val;
		}
		$this -> assign('type',$type);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	
	function add_statement_show(){
		$type = $_GET['type'];
		$this -> assign('type',$type);
		$this -> display();
	}
	
	function add_statement_do(){
		$model = new Model();
		$type = $_POST['type'];
		$statement = $_POST['statement'];
		if(!$statement){
			$this -> error("请填写声明");
		}
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if(!$start_tm || !$end_tm){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		$start_tms = strtotime(date('Y-m-d H:i:s',strtotime($start_tm)));
		$end_tms = strtotime(date('Y-m-d H:i:s',strtotime($end_tm)));
		$admin_id = $_SESSION['admin']['admin_id'];
		$where_have_been['_string'] = "type= {$type} and status = 1 and start_tm <= ".$end_tms." and end_tm >= ".$start_tms;
		$have_been = $model -> table('sj_service_statement') -> where($where_have_been) -> select();
		if($have_been){
			$this -> error("该时间段内已存在客服声明");
		}
		$data = array(
			'statement' => $statement,
			'type' => $type,
			'start_tm' => $start_tms,
			'end_tm' => $end_tms,
			'admin_id' => $admin_id,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1
		);
		$result = $model -> table('sj_service_statement') -> add($data);
		
		if($result){
			$this -> writelog("已添加客服声明id为{$result}",'sj_service_statement',"{$result}",__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Marketgame/service_statement_list/type/'.$type);
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_statement_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_service_statement') -> where(array('id' => $id)) -> select();
		$type = $_GET['type'];
		$my_time = $_GET['my_time'];
		$this -> assign('result',$result);
		$this -> assign('type',$type);
		$this -> assign('my_time',$my_time);
		$this -> display();
	}
	
	function edit_statement_do(){
		$model = new Model();
		$id = $_POST['id'];
		$type = $_POST['type'];
		$my_time = $_POST['my_time'];
		$my_result = $model -> table('sj_service_statement') -> where(array('id' => $id)) -> select();
		$statement = trim($_POST['statement']);
		if(!$statement){
			$this -> error("请填写声明");
		}
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if(!$start_tm || !$end_tm){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		$start_tms = strtotime(date('Y-m-d H:i:s',strtotime($start_tm)));
		$end_tms = strtotime(date('Y-m-d H:i:s',strtotime($end_tm)));
		$admin_id = $_SESSION['admin']['admin_id'];
		$where_have_been['_string'] = "type = {$my_result[0]['type']} and status = 1 and start_tm <= ".$end_tms." and end_tm >= ".$start_tms." and id != {$id}";
		$have_been = $model -> table('sj_service_statement') -> where($where_have_been) -> select();
		
		if($have_been){
			$this -> error("该时间段内已存在客服声明");
		}
		$data = array(
			'statement' => $statement,
			'start_tm' => $start_tms,
			'end_tm' => $end_tms,
			'admin_id' => $admin_id,
			'update_tm' => time(),
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_service_statement',$data,$model);
		$result = $model -> table('sj_service_statement') -> where(array('id' => $id)) -> save($data);
		
		if($result){
			$this -> writelog("已编辑客服声明id为{$id}".$log_result,'sj_service_statement',"{$id}",__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Marketgame/service_statement_list/type/'.$my_result[0]['type'].'/my_time/'.$my_time.'/');
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	
	function del_statement(){
		$model = new Model();
		$type = $_GET['type'];
		$my_time = $_GET['my_time'];
		$id = $_GET['id'];
		$result = $model -> table('sj_service_statement') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的客服声明",'sj_service_statement',"{$id}",__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Marketgame/service_statement_list/type/'.$type.'/my_time/'.$my_time);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	

}