<?php

class SoftOfficialAction extends CommonAction {
	//官方认证软件审核列表
	public function confirm()
	{
		import('@.ORG.Page');
		$cond = array();
		if (!empty($_GET['softname']))
		{
			$softname = $_GET['softname'];
			$cond[] = "`softname` like '%$softname%'";
			$this->assign('softname', $softname);
		}
		if (!empty($_GET['package']))
		{
			$package = $_GET['package'];
			$cond[] = "`package`='$package'";
			$this->assign('package', $package);
		}
		$from = isset($_GET['from']) ? $_GET['from'] : 'all';
		$this->assign('from', $from);
		$status = isset($_GET['status']) ? $_GET['status'] : 'all';
		$this->assign('status', $status);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 15;
		$param = http_build_query($_REQUEST);

		if ($from != 'all')
			$cond[] = "`from`=$from";
		$where = implode(" and ", $cond);
		$model = new Model();
		$total = $model -> table('sj_official_soft_fetched') -> where($where) -> count();
		$page = new Page($total, $limit, $param);
		$conlist = $model->table('sj_official_soft_fetched')->where($where)->field('*')->select();
		//var_dump($conlist);
		$return = array();
		$count = 0;
		$ids = array();
		foreach ($conlist as $v)
		{
			$conds = "package='{$v['package']}'";
			$num = $model->table('sj_soft_note')->where($conds)->field('status')->count();
			if ($num > 0)
			{
				$statusFind = $model->table('sj_soft_note')->where("package='{$v['package']}'")->field('status')->select();
				$v['status'] = $statusFind[0]['status'];
			}
			else
			{
				$v['status'] = 3;
			}
			if ($status == 'all' || $v['status'] == $status)
			{
				if ($count >= $page->firstRow && $count < ($page->firstRow + $page->listRows))
				{
					$return[] = $v;
					$ids[] = $v['id'];
				}
				$count++;
			}
		}
		$page = new Page($count, $limit, $param);
		//过滤
		$s = date("Y-m-d",time());
		$this->assign('begintime',$s); 	
		$end_at = "2023-1-1";
		$this->assign('endtime',$end_at);
		$this->assign('ssid',$ssid); 
		$this->gpcFilter($return);
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('page', $page->show());
		$this->assign('officialfetch', $return);
		$this->display('confirm');
	}
	//官方认证软件审核--通过显示
	public function confirm_timelist(){
		if (empty($_GET['id'])){
			$this -> error('ID不能为空');
		}
		$sid = $_GET['id'];
		$model = new Model();
		$ret = $model->table('sj_official_soft_fetched')->where("id = $sid")->field('package')->select();
		$package = $ret[0]['package'];
		$ret = $model->table('sj_soft_note')->where("package='$package'")->field('status,start_time,terminal_time')->select();
		if($ret[0]['start_time'] == 0){
			$s = date("Y-m-d",time());
			$this->assign('start_at',$s); 	
		}else{
			$start_at = date("Y-m-d",$ret[0]['start_time']);
			$this->assign('start_at',$start_at);
		}
		if($ret[0]['terminal_time'] == 0){
			$end_at = "2023-1-1";
			$this->assign('end_at',$end_at);
		}else{ 
			$end_at = date("Y-m-d",$ret[0]['terminal_time']);
			$this->assign('end_at',$end_at);
		}
		$this->assign('ssid',$sid); 
		$this->display('confirm_timelist');
	}
	//官方认证通过--提交
	public function official_soft_confirm_pass()
	{
		$time = time();
		$flag = true;
		if (empty($_GET['id']))
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> error('ID不能为空');
		}
		if($_GET['fals'] == 'list'){
			$start_at = strtotime($_GET['start_at']);
			$end_at = strtotime($_GET['end_at'])+86399;
			$id = explode(',',$_GET['id'],-1);
		}else{
			$start_at = strtotime($_POST['start_at']);
			$end_at = strtotime($_POST['end_at'])+86399;
			$id = json_decode($_GET['id']);
		}
		if($start_at > $end_at){
			$this -> error('开始时间不能大于结束时间');
		}
		if (!$id)
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> error('ID格式错误');
		}
		$model = new Model();
		foreach ($id as $v)
		{
			$ret = $model->table('sj_official_soft_fetched')->where("id = $v")->field('package')->select();				
			$package = $ret[0]['package'];
			$log = $this->logcheck(array('package'=>$package),'sj_soft_note',array('status' => 1, 'update_time' => $time,'start_time'=>$start_at,'terminal_time'=>$end_at),$model);
			$ret = $model->table('sj_soft_note')->where("package='$package'")->save(array('status' => 1, 'update_time' => $time,'start_time'=>$start_at,'terminal_time'=>$end_at));
			if (!$ret)
				$flag = false;
			else
				//$this->writelog('修改软件包名为' . $package . '的软件"官方认证状态"为1');
				$this->writelog('软件管理-官方认证软件审核-修改软件包名为' . $package . $log);
				//echo $model->getlastsql();
		}
		if ($flag == false)
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> error('审核通过出错');
		}
		else
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> success('审核通过成功');
		}
	}
	//官方认证软件审核--拒绝--提交
	public function official_soft_confirm_reject()
	{
		$time = time();
		$flag = true;
		if (!isset($_GET['id']))
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> error('ID不能为空');
		}
		$id = json_decode($_GET['id']);
		if (!$id)
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> error('ID格式错误');
		}
		$model = new Model();
		foreach ($id as $v)
		{
			$ret = $model->table('sj_official_soft_fetched')->where("id = $v")->field('package')->select();
			//var_dump($ret);
			$package = $ret[0]['package'];
			$ret = $model->table('sj_soft_note')->where("package='$package'")->field('status')->select();
			if ($ret[0]['status'] != 2)
			{
				$log = $this->logcheck(array('package'=>$package),'sj_soft_note',array('status' => 2, 'update_time' => $time),$model);
				$ret = $model->table('sj_soft_note')->where("package='$package'")->save(array('status' => 2, 'update_time' => $time));
				if (!$ret)
					$flag = false;
				else
					//$this->writelog('修改软件包名为' . $package . '的软件"官方认证状态"为2');
					$this->writelog('软件管理-官方认证软件审核-修改软件包名为' . $package . $log);
			}
		}
		if ($flag == false)
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> error('审核拒绝出错');
		}
		else
		{
			//$this -> assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this -> success('审核拒绝成功');
		}
	}
	//官方认证软件列表--显示
	public function accreditation_list(){
		$model = new Model();
   		import('@.ORG.Page');
		if(!empty($_GET['zh_type'])){
			if($_GET['zh_type'] == 1){
				$wheres['start_time'] = array('elt',time());
				$wheres['terminal_time'] = array( 'egt',time());
				$this->assign("zh_type",$_GET['zh_type']);
			}else if($_GET['zh_type'] == 2){
				$wheres['terminal_time'] = array('lt',time());
				$this->assign("zh_type",$_GET['zh_type']);
			}else if($_GET['zh_type'] == 3){
				$wheres['start_time'] = array ('gt',time());
				$this->assign("zh_type",$_GET['zh_type']);
			}
		}

		if(!empty($_GET['start_at']) && !empty($_GET['end_at'])){
			$start_at = strtotime($_GET['start_at']);
			$end_at = strtotime($_GET['end_at'])+86399;
			$wheres['start_time'] = array('egt',$start_at);
			$wheres['terminal_time'] = array('elt',$end_at);
			$this->assign('start_at', $_GET['start_at']);
			$this->assign('end_at', $_GET['end_at']);
		}
		if (!empty($_GET['package'])){
			$package = $_GET['package'];
			$wheres['package'] = array("like","%{$package}%");
			$this->assign('package', $package);
		}
		if (!empty($_GET['softid'])){
			$softid = $_GET['softid'];
			$where['softid'] = array("like","{$softid}");
			$this->assign('softid', $softid);
		}
		if (!empty($_GET['softname'])){
			$softname = $_GET['softname'];
			$where['softname'] = array("like","%{$softname}%");
			$this->assign('softname', $softname);
		}
		if(!empty($_GET['order'])){
			if($_GET['order']==1){
				$order= array('total_downloaded'=>'desc');
				$this->assign('order', 2);
			}else if($_GET['order']==2){
				$order= array('total_downloaded'=>'asc');
				$this->assign('order', 1);
			}
		}else{
			$order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
			$this->assign('order', $order);
		}

		$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
		$where['status'] = array('eq',1);
		$where['hide'] = array('eq',1);
		$where['channel_id'] = array('eq','');
		$wheres['status'] = array('neq',0);
		//查询soft_note表中的数据
		$pack = $model->table('sj_soft_note')->where($wheres)->field('package,status')->select();
		//echo $model ->getlastsql();
		$packages = array();
		foreach($pack as $n => $m){
				$packages[] = $m['package'];				 
		}
		$where['package']= array('in',array_unique($packages));//取到包名付给条件
		$param = http_build_query($_REQUEST);
		$totals = $model -> table('sj_soft') -> where($where) ->group('package')->field('max(softid),softid,softname,package,version,version_code,total_downloaded')-> select();
		$total = count($totals);
		$page = new Page($total, $limit, $param);
		$softlist = $model -> table('sj_soft')->where($where)->group('package')->order($order)->field('max(softid),softid,softname,package,version,version_code,total_downloaded')->limit($page->firstRow.','.$page->listRows)->select();
		//整合数据
		$officialfetch = array();
		foreach ($softlist as $k => $v){
			$list = $model->table('sj_soft_note')->where(array('package'=>$v['package']))->field('package,start_time,terminal_time,status')->select();
			$officialfetch[$k]['softid'] = $v['softid'];
			$officialfetch[$k]['softname'] = $v['softname'];
			$officialfetch[$k]['package'] = $list[0]['package'];
			$officialfetch[$k]['status'] = $list[0]['status'];
				if($list[0]['start_time'] == 0){
					$officialfetch[$k]['start_time'] = "------";
				}else{
					$officialfetch[$k]['start_time'] = date("Y/m/d H:i:s",$list[0]['start_time']);
				}
				if($list[0]['terminal_time'] == 0){
					$officialfetch[$k]['terminal_time'] ="-----";
				}else{
					$officialfetch[$k]['terminal_time'] = date("Y/m/d H:i:s",$list[0]['terminal_time']);
				}
			$officialfetch[$k]['version'] = $v['version'];
			$officialfetch[$k]['version_code'] = $v['version_code'];
			$officialfetch[$k]['total_downloaded'] = $v['total_downloaded'];
			if(($list[0]['start_time'] <= time())&&($list[0]['terminal_time'] >= time())){
				$officialfetch[$k]['type'] = 1; //当前显示
			}elseif($list[0]['terminal_time'] < time()){
				$officialfetch[$k]['type'] = 2;//过期
			}elseif($list[0]['start_time'] > time()){
				$officialfetch[$k]['type'] = 3;//未开始
			}else{
				$officialfetch[$k]['type'] = 0;
			}
			//var_dump($officialfetch);
		}
		$this -> assign('page', $page->show());
		$this->assign('officialfetch_list', $officialfetch);
		$this -> display('accreditation_list');
	}
	//取消官方认证
  	public function rm_accreditation(){
 		$model = new Model();
		$package = $_GET['packages'];
		$time = time();
	 	if(!empty($package)){
			$map['status'] = 2;
			$map['start_time'] = 0;
			$map['terminal_time'] = 0;
			$map['update_time'] = $time;
			$log = $this->logcheck(array('package'=>$package),'sj_soft_note',$map,$model);
			$rm_acc = $model ->table('sj_soft_note') -> where(array('package'=>"$package")) -> save($map);
			if($rm_acc){
				//$this->writelog('软件管理-官方认证软件审核-修改软件包名为' . $package . '的软件"官方认证状态"为2');
				$this->writelog('软件管理-官方认证软件审核-修改软件包名为' . $package .$log);
				$this -> success('取消认证成功');
			}
		} 
	}
	//官方认证软件列表--编辑认证--时间插件
	public function confirm_timelist_one(){
		$package = $_GET['package'];
		$model = new Model();
		$ret = $model->table('sj_soft_note')->where("package = '$package'")->select();
		if($ret[0]['start_time'] == 0){
			$s = date("Y-m-d",time());
			$this->assign('start',$s); 	
		}else{
			$start_at = date("Y-m-d",$ret[0]['start_time']);
			$this->assign('start',$start_at);
		}
		if($ret[0]['terminal_time'] == 0){
			$end_at = "2023-1-1";
			$this->assign('end',$end_at);
		}else{ 
			$end_at = date("Y-m-d",$ret[0]['terminal_time']);
			$this->assign('end',$end_at);
		}
		$this->assign('package',$package); 
		$this -> display();
	}
	//官方认证软件列表--编辑认证--提交
	public function official_soft_confirm_pass_one(){
		$package = $_POST['package'];
		$time = time();
		$model = new Model();
		if(!empty($package)){
			$map['status'] = 1;
			$map['update_time'] = $time;
			$map['start_time'] = strtotime($_POST['start']);
			$map['terminal_time'] = strtotime($_POST['end'])+86399;
			if($map['start_time'] > $map['terminal_time']){
				$this -> error('开始时间不能大于结束时间');
			}
			$log = $this->logcheck(array('package'=>$package),'sj_soft_note',$map,$model);
			$rm_acc = $model ->table('sj_soft_note') -> where(array('package'=>"$package")) -> save($map);
			if($rm_acc){
				//$this->writelog('软件管理-官方认证软件审核-修改软件包名为' . $package . '的软件"官方认证状态"为1');
				$this->writelog('软件管理-官方认证软件审核-修改软件包名为' . $package .$log);
				$this -> success('通过认证成功');
			}
		} 
	}
	//官方认证软件列表--删除认证
	public function del_accreditation(){
		$package = $_GET['package'];
		$time = time();
		$model = new Model();
		if(!empty($package)){
			$map['status'] = 0;
			$map['update_time'] = $time;
			$log = $this->logcheck(array('package'=>$package),'sj_soft_note',$map,$model);
			$rm_acc = $model ->table('sj_soft_note') -> where(array('package'=>"$package")) -> save($map);
			if($rm_acc){
				//$this->writelog('修改软件包名为' . $package . '的软件"官方认证状态"为0');
				$this->writelog('修改软件包名为' . $package . $log);
				$this -> success('删除认证成功');
			}
		} 
	}
}