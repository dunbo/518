<?php
/**
 * 安智网产品管理平台 渠道管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:高硕 2011.5.17
 * ----------------------------------------------------------------------------
*/
class ChannelsAction extends CommonAction {

    private $channel_db;     //渠道表
    private $channel_list;   //渠道列表
    private $cid;            //渠道CID
    private $soft_db;        //软件表
    private $soft_list;      //软件列表

	/**
	 *
	 * 渠道用户激活系数，用于在前台的合作显示
	 */
	public function channels_coefficient_list(){
		define("GO_HIDE_SKIN", 4);
		$Form = M ('channel');
		$model = new Model();
        import("@.ORG.Page");
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_SHOW_TYPE;
        $map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $res = $Form->table('sj_admin_filter')->where($map)->find();
        $show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
        $this->assign( "show_chl", $show_chl );

        $chl = '';
        if(isset($_REQUEST['chl'])&&$_REQUEST['chl']){
        	$chl = $_REQUEST['chl'];
        }
        $chname = '';
		if(isset($_REQUEST['chname'])&&$_REQUEST['chname']){
        	$chname = trim($_REQUEST['chname']);
        }

        $category_id = '';
		if(isset($_REQUEST['category_id'])&&$_REQUEST['category_id']!=''){
        	$category_id = $_REQUEST['category_id'];
        	$this->assign('category_id', $category_id);
        }
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $_SESSION['admin']['admin_id'],'target_type' => 9)) -> find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql_where=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			//$target_type= CHANNEL_SHOW_CONTROL;
			$target_type = CHANNEL_FILTER_TYPE;
			if($count_power_result['filter_type'] == 2){
				$sql_where = " cid in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}')  and  ";
			}
		}
		$sql_where .= 'status=1';
		$client_model = D('Channel_cooperation.channel_cooperation');
		$GET['client_name'] = trim($_GET['client_name']);
		if($_GET['client_name'] && !$_GET['charge_id']){
			$client_name_where['_string'] = "client_name like '%{$_GET['client_name']}%' and status != 0";
			$client_name_result = $client_model -> table('co_client_list') -> where($client_name_where) -> select();
			foreach($client_name_result as $key => $val){
				$client_id_str .= $val['id'].',';
			}
			$client_str = substr($client_id_str,0,-1);
			$cid_where['_string'] = "client_id in ({$client_str}) and status = 1";
			$cid_result = $client_model -> table('co_client_channel') -> where($cid_where) -> select();
			foreach($cid_result as $key => $val){
				$cid_arr_go .= $val['cid'].',';
			}
			$cid_str = substr($cid_arr_go,0,-1);
			$sql_where .= " and cid in ({$cid_str})";
		}elseif(!$_GET['client_name'] && $_GET['charge_id']){
			$client_name_where['_string'] = "charge_id = {$_GET['charge_id']} and status != 0";
			$client_name_result = $client_model -> table('co_client_list') -> where($client_name_where) -> select();
			foreach($client_name_result as $key => $val){
				$client_id_str .= $val['id'].',';
			}
			$client_str = substr($client_id_str,0,-1);
			$cid_where['_string'] = "client_id in ({$client_str}) and status = 1";
			$cid_result = $client_model -> table('co_client_channel') -> where($cid_where) -> select();
		
			foreach($cid_result as $key => $val){
				if($val['cid']){
					$cid_arr_go .= $val['cid'].',';
				}
			}
			
			$cid_str = substr($cid_arr_go,0,-1);
			
			$sql_where .= " and cid in ({$cid_str})";
			
		}elseif($_GET['client_name'] && $_GET['charge_id']){
			$client_name_where['_string'] = "client_name like '%{$_GET['client_name']}%' and charge_id = {$_GET['charge_id']} and status != 0";
			$client_name_result = $client_model -> table('co_client_list') -> where($client_name_where) -> select();
			foreach($client_name_result as $key => $val){
				$client_id_str .= $val['id'].',';
			}
			$client_str = substr($client_id_str,0,-1);
			$cid_where['_string'] = "client_id in ({$client_str}) and status = 1";
			$cid_result = $client_model -> table('co_client_channel') -> where($cid_where) -> select();
			foreach($cid_result as $key => $val){
				$cid_arr_go .= $val['cid'].',';
			}
			$cid_str = substr($cid_arr_go,0,-1);
			
			$sql_where .= " and cid in ({$cid_str})";
		}
		if($chname){
			$sql_where .= " and chname like '%{$chname}%'";
		}
		if($category_id!=''){
			$sql_where .= " and category_id='{$category_id}'";
		}
		
		if($_GET['co_status']){
			$coefficient_where['_string'] = "status = 1 and coefficient<100";
			$coefficient_result = $Form -> table('sj_channel_coefficient') -> where($coefficient_where) -> select();
			foreach($coefficient_result as $key => $val){
				$coefficient_arr[] = $val['cid'];
			}
		}
		
		if($_GET['co_status'] == 1 && $cid_result){
			foreach($cid_result as $key => $val){
				$my_cid_arr_go[] = $val['cid'];
			}
			$my_cid = array_intersect($coefficient_arr,$my_cid_arr_go);
			
			foreach($my_cid as $key => $val){
				$cid_str_go .= $val.',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			
			$sql_where .= " and cid in ({$cid_str})";
			
		}elseif($_GET['co_status'] == 2 && $cid_result){
			foreach($cid_result as $key => $val){
				if(!in_array($val['cid'],$coefficient_arr)){
					$cid_str_go .= $val['cid'].',';
				}
			}
			$cid_str = substr($cid_str_go,0,-1);
			$sql_where .= " and cid in ({$cid_str})";
		}elseif(!$_GET['co_status'] && $cid_result){
	
			foreach($cid_result as $key => $val){
				$cid_str_go .= $val['cid'].',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			$sql_where .= " and cid in ({$cid_str})";
		}elseif($_GET['co_status'] == 1 && !$cid_result){
			foreach($coefficient_arr as $key => $val){
				$cid_str_go .= $val.',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			$sql_where .= " and cid in ({$cid_str})";
		}elseif($_GET['co_status'] == 2 && !$cid_result){
			foreach($coefficient_arr as $key => $val){
				$cid_str_go .= $val.',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			$sql_where .= " and cid not in ({$cid_str})";
		}
		if($_GET['client_id']){
			$my_cid_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $_GET['client_id'],'status' => 1)) -> select();
			foreach($my_cid_result as $key => $val){
				$my_cid_str_go .= $val['cid'].',';
			}
			$my_cid_str = substr($my_cid_str_go,0,-1);
			$sql_wheres = "cid in ({$my_cid_str}) and status = 1";
		}else{
			$sql_wheres = $sql_where;
		}
		// $admin_id = $_SESSION['admin']['admin_id'];
		// $filter_result = $Form -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		// foreach($filter_result as $key => $val){
		// 	$admin_cid_str .= $val['target_value'].',';
		// }
		// $admin_cid = substr($admin_cid_str,0,-1);
		// $sql_wheres = $sql_wheres." and cid in ({$admin_cid})";
		
        $sql = "select count(cid) as total from sj_channel where {$sql_wheres}";
        $res = $Form->query($sql);
        $count = $res[0]['total'];

        $p=new Page($count,50);
		
		
        //$sql = "select * from sj_channel {$sql_where}";
        $list=$Form->where($sql_wheres)->limit($p->firstRow.','.$p->listRows)->order('chname')->select();
	
		foreach($list as $key => $val){
			$client_channel_result = $client_model -> table('co_client_channel') -> where(array('cid' => $val['cid'])) -> select();
			$client_result = $client_model -> table('co_client_list') -> where(array('id' => $client_channel_result[0]['client_id'])) -> select();
			$val['client_name'] = $client_result[0]['client_name'];
			$val['client_id'] = $client_result[0]['id'];
			$charge_result = $client_model -> table('co_charge') -> where(array('id' => $client_result[0]['charge_id'])) -> select();
			$val['charge_name'] = $charge_result[0]['charge_name'];
			$cofficient_result = $Form -> table('sj_channel_coefficient') -> where(array('cid' => $val['cid'])) -> select();
			$val['cofficient'] = $cofficient_result[0]['cofficient'];
			$val['last_refresh'] = $cofficient_result[0]['last_refresh'];
			$list[$key] = $val;
		}
		$my_cid_result = $Form -> table('sj_channel_coefficient') -> group('cid') -> select();
		foreach($my_cid_result as $key => $val){
			$my_cid[] = $val['cid'];
		}
		
		//查询已被停用渠道
		$need_result = $model -> table('sj_channel') -> where(array('co_status' => 2)) -> field('cid') -> select();
		foreach($need_result as $key => $val){
			$no_cid[] = $val['cid'];
		}
		$this -> assign('no_cid',$no_cid);
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        if($chl) $this -> assign('chl', $chl);
        if($chname) $this -> assign('chname', $chname);
        $this->assign( "page", $page );
        $i=0;
        foreach($list as &$v)
        {
            $cid = $v['cid'];
            $sql = "select coefficient,create_time from sj_channel_coefficient where cid={$cid} and status=1" ;
            $coefficient = $model->query($sql);
            $v['coefficient'] = $coefficient[0]['coefficient'];
            $v['create_time'] = $coefficient[0]['create_time']?date("Y-m-d",$coefficient[0]['create_time']):"";
            $v['category_name'] = $category_list[$v['category_id']]['name'];
        }
	
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 50;
		}
		$my_charge_result = $client_model -> table('co_charge') -> where(array('status' => 1)) -> select();
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('my_cid',$my_cid);
	
		$this -> assign('charge_result',$my_charge_result);
		$this -> assign('chname',$_GET['chname']);
		$this -> assign('client_name',$_GET['client_name']);
		$this -> assign('charge_id',$_GET['charge_id']);
		$this -> assign('co_status',$_GET['co_status']);
        //$this->writelog('查看了渠道系数列表');
        $this->assign ( "list", $list );
        $this->display('channels_coefficient_list');
	}

	public function channels_coefficient_edit(){
		$cid = isset($_REQUEST['cid'])?escape_string($_REQUEST['cid']):'';
		$model = new Model();
		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_SHOW_TYPE;
        $map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $res = $model->table('sj_admin_filter')->where($map)->find();
        $show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
        $this->assign( "show_chl", $show_chl );
		if(empty($cid)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('没有cid参数错误');
		}
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $model->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql="select * from sj_channel where  cid={$cid} and status=1";
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;

			$sql = "select * from sj_channel where  cid  in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}') and cid={$cid} and status=1";
		}
	
		$channel = $model->query($sql);

		if(empty($channel)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('没有相应的渠道');
		}
		$sql = "select * from sj_channel_coefficient where cid={$cid} and status=1";
		$list = $model->query($sql);
		
		
		
		$list = $list[0];
		$list['cid'] = $cid;
		$list['chl'] = $channel[0]['chl'];
		$list['chname'] = $channel[0]['chname'];
		$list['coefficient'] = isset($list['coefficient']) ? $list['coefficient'] : 100; 
		$this -> assign('p',$_GET['p']);
		$this -> assign('lr',$_GET['lr']);
		$this -> assign('chname',$_GET['chname']);
		$this -> assign('client_name',$_GET['client_name']);
		$this -> assign('charge_id',$_GET['charge_id']);
		$this -> assign('co_status',$_GET['co_status']);
		$this->assign('vo',$list);
		$this->display('channels_coefficient_edit');
	}
	public function channels_coefficient_update(){
		$cid = isset($_REQUEST['cid'])?escape_string($_REQUEST['cid']):'';
		if($_REQUEST['coefficient'] == ''){
			$this -> error("请填写扣量系数");
		}
		$coefficient = isset($_REQUEST['coefficient'])?(int)$_REQUEST['coefficient']:0;
		if(empty($cid)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('没有cid参数错误');
		}
		if($coefficient>100 || $coefficient<0){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('数据大小错误');
		}

		$model = new Model();
		$sql = "select * from sj_channel_coefficient where cid={$cid} and status=1";
		$old = $model->query($sql);
		$t = time();
		$client_model = D('Channel_cooperation.channel_cooperation');
		if(!empty($old)){
			if($old[0]['coefficient']!=$coefficient){
				$sql = "update sj_channel_coefficient set last_refresh =".$t." ,status=0 where cid={$cid}";
				$model->query($sql);
				$sql = "insert into sj_channel_coefficient (cid,coefficient,create_time,status,last_refresh) values ($cid,$coefficient,$t,1,$t)";
				$model->query($sql);
			}
		}else{
			$sql = "insert into sj_channel_coefficient (cid,coefficient,create_time,status,last_refresh) values ($cid,$coefficient,$t,1,$t)";
			$model->query($sql);
		}
		$admin_id = $_SESSION['admin']['admin_id'];
		$admin_ip = $this -> getIP();
		$coefficient_sql = "insert into co_channel_coefficient (cid,create_tm,admin_id,coefficient,admin_ip) values($cid,$t,$admin_id,$coefficient,'$admin_ip')";
		$client_model -> query($coefficient_sql);

		if($_POST['chname']){
			$where_go .= "/chname/{$_POST['chname']}";
		}
		if($_POST['client_name']){
			$where_go .= "/client_name/{$_POST['client_name']}";
		}
		if($_POST['charge_id']){
			$where_go .= "/charge_id/{$_POST['charge_id']}";
		}
		if($_POST['co_status']){
			$where_go .= "/co_status/{$_POST['co_status']}";
		}
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if(!$old[0]['coefficient'] && $old[0]['coefficient'] != 0 ){
			!$old[0]['coefficient'] = 100;
		}
		if($list!==false){
			$this->writelog('编辑CID为['.$cid.']的渠道系数,渠道系数由['.$old[0]['coefficient'].']改为['.$coefficient.']', 'co_channel_coefficient', $cid,__ACTION__ ,'','edit');
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Channels/channels_coefficient_list/p/{$p}/lr/{$lr}".$where_go);
			$this->success('数据更新成功');
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Channels/channels_coefficient_list/p/{$_GET['p']}/lr/{$_GET['lr']}".$where_go);
			$this->error('数据更新失败');
		}
	}
	
	
	function coefficient_log_show(){
		$client_model = D('Channel_cooperation.channel_cooperation');
		$model = new Model();
		$cid = $_GET['cid'];
		$coefficient_result = $client_model -> table('co_channel_coefficient') -> where(array('cid' => $cid)) -> order('create_tm DESC') -> select();
		foreach($coefficient_result as $key => $val){
			$admin_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['admin_id'])) -> select();
			$val['admin_name'] = $admin_result[0]['admin_user_name'];
			$coefficient_result[$key] = $val;
		}
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		$this -> assign('channel_result',$channel_result);
		$this -> assign('coefficient_result',$coefficient_result);
		$this -> display();
	}
	
	function getIP(){
		global $ip;
		if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
		else $ip = "Unknow";
		return $ip;
	}
	
	function coefficient_history_list(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation');
		$channel_type = $_GET['channel_type'];
		$chname = trim($_GET['chname']);
		$client_name = trim($_GET['client_name']);
		$charge_id = $_GET['charge_id'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if($chname){
			$channel_where['_string'] = "chname like '%{$chname}%' and status = 1";
			$channel_result = $model -> table('sj_channel') -> where($channel_where) -> select();
			foreach($channel_result as $key => $val){
				$cid_str_arr[] = $val['cid'];
			}
		}
		if($client_name && $charge_id){
			$client_where['_string'] = "client_name like '%{$client_name}%' and charge_id = {$charge_id} and status !=0";
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_str .= $val['id'].',';
			}
			$client_id = substr($client_id_str,0,-1);
			$client_channel_where['_string'] = "client_id in ({$client_id}) and status = 1";
			$client_channel_result = $client_model -> table('co_client_channel') -> where($client_channel_where) -> select();
			foreach($client_channel_result as $key => $val){
				$client_id_arr[] = $val['cid'];
			}
		}elseif(!$client_name && $charge_id){
			$client_where['_string'] = "charge_id = {$charge_id} and status != 0";
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_str .= $val['id'].',';
			}
			$client_id = substr($client_id_str,0,-1);
			$client_channel_where['_string'] = "client_id in ({$client_id}) and status = 1";
			$client_channel_result = $client_model -> table('co_client_channel') -> where($client_channel_where) -> select();
			foreach($client_channel_result as $key => $val){
				$client_id_arr[] = $val['cid'];
			}
	
		}elseif($client_name && !$charge_id){
			$client_where['_string'] = "client_name like '%{$client_name}%' and status !=0";
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			foreach($client_result as $key => $val){
				$client_id_str .= $val['id'].',';
			}
			$client_id = substr($client_id_str,0,-1);
			$client_channel_where['_string'] = "client_id in ({$client_id}) and status = 1";
			$client_channel_result = $client_model -> table('co_client_channel') -> where($client_channel_where) -> select();
			foreach($client_channel_result as $key => $val){
				$client_id_arr[] = $val['cid'];
			}
		}
		$admin_id = $_SESSION['admin']['admin_id'];
		$filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
		foreach($filter_result as $key => $val){
			$admin_cid_str .= $val['target_value'].',';
		}
		$admin_cid = substr($admin_cid_str,0,-1);
		$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
		if($count_power_result['filter_type'] == 2){
			$where_go .= " and cid in ({$admin_cid})";
		}
		if(($chname && $client_name && $charge_id) || ($chname && !$client_name && $charge_id) || ($chname && $client_name && !$charge_id)){
			if($cid_str_arr && $client_id_arr){
				$cid_arr = array_intersect($cid_str_arr,$client_id_arr);
			}else{
				$cid_arr = array();
			}
			foreach($cid_arr as $key => $val){
				$cid_str_go .= $val.',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			
			$where_go .= " and cid in ({$cid_str})";
			
		}elseif($chname && !$client_name && !$charge_id){
			$cid_arr = $cid_str_arr;
			foreach($cid_arr as $key => $val){
				$cid_str_go .= $val.',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			$where_go .= " and cid in ({$cid_str})";
		}elseif((!$chname && $client_name && !$charge_id) || (!$chname && !$client_name && $charge_id) || (!$chname && $client_name && $charge_id)){
			$cid_arr = $client_id_arr;
			foreach($cid_arr as $key => $val){
				$cid_str_go .= $val.',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			$where_go .= " and cid in ({$cid_str})";
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($start_tm){
			$where_go .= " and submit_tm >= ".strtotime(date('Y-m-d 00:00:00',strtotime($start_tm)));
		}
		if($end_tm){
			$where_go .= " and submit_tm <= ".strtotime(date('Y-m-d 23:59:59',strtotime($end_tm)));
		}
		$rank = $_GET['rank'];
		if($channel_type == 1){
			if(!$rank || $rank == 1 || $rank == 2){
				$table = 'activation_state';
			}elseif($rank == 3 || $rank == 4){
				$table = 'activation_coefficient_state';
			}
			if(!$rank){
				$order = 'submit_tm DESC';
			}elseif($rank == 1 || $rank == 3){
				$order = 'counts DESC';
				
			}elseif($rank == 2 || $rank == 4){
				$order = 'counts';
			}
			if($rank == 1 || $rank == 2 || !$rank){
				$where_go .= " and pid = 1 and location = 1";
			}
		}else{
			if(!$rank || $rank == 1 || $rank == 2){
				$table = 'activation_game_state';
			}elseif($rank == 3 || $rank == 4){
				$table = 'activation_game_coefficient_state';
			}
			if(!$rank){
				$order = 'submit_tm DESC';
			}elseif($rank == 1 || $rank == 3){
				$order = 'counts DESC';
				
			}elseif($rank == 2 || $rank == 4){
				$order = 'counts';
			}
			if($rank == 1 || $rank == 2 || !$rank){
				$where_go .= " and pid = 5 and location = 1";
			}
		}
		if($_GET['client_id']){
			$client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $_GET['client_id'],'status' => 1)) -> select();
			foreach($client_channel_result as $key => $val){
				$my_cid_str_go .= $val['cid'].',';
			}
			$my_cid_str = substr($my_cid_str_go,0,-1);
			$where_go .= " and cid in ({$my_cid_str})";
		}
		
		if($_GET['chname'] || $_GET['client_name'] || $_GET['charge_id'] || $_GET['start_tm'] || $_GET['end_tm']){
		$where['_string'] = "status = 1".$where_go;
		}else{
			$where['_string'] = "status = 4";
		}
	
		$count = $coefficient_model -> table($table) -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 50, $param);
		$result = $coefficient_model -> table($table) -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
		foreach($result as $key => $val){
			if(!$rank || $rank == 1 || $rank == 2){
				if($channel_type == 1){
					$table_for = 'activation_coefficient_state';
				}else{
					$table_for = 'activation_game_coefficient_state';
				}
				$activation_where['_string'] = "cid = {$val['cid']} and submit_tm = {$val['submit_tm']} and status = 1";
				$activation_coefficient_result = $coefficient_model -> table($table_for) -> where($activation_where) -> select();
				$val['activation_coefficient'] = $activation_coefficient_result[0]['counts'];
				$val['coefficient'] = $activation_coefficient_result[0]['coefficient'];	
				$val['activation'] = $val['counts'];
			}elseif($rank == 3 || $rank == 4){
				if($channel_type == 1){
					$table_for = 'activation_state';
				}else{
					$table_for = 'activation_game_state';
				}
				$activation_where['_string'] = "cid = {$val['cid']} and submit_tm = {$val['submit_tm']} and pid = 1 and status = 1";
				$activation_result = $coefficient_model -> table($table_for) -> where($activation_where) -> select();
				$val['activation_coefficient'] = $val['counts'];
				$val['activation'] = $activation_result[0]['counts'];
			}
			$channel_name_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
			$val['chname'] = $channel_name_result[0]['chname'];
			$my_channel_result = $client_model -> table('co_client_channel') -> where(array('cid' => $val['cid'],'status' => 1)) -> select();
			$my_client_result = $client_model -> table('co_client_list') -> where(array('id' => $my_channel_result[0]['client_id'])) -> select();
			$val['client_name'] = $my_client_result[0]['client_name'];
			$val['client_id'] = $my_client_result[0]['id'];
			$my_charge_result = $client_model -> table('co_charge') -> where(array('id' => $my_client_result[0]['charge_id'])) -> select();
			$val['charge_name'] = $my_charge_result[0]['charge_name'];
			$result[$key] = $val;
		}
		
		$charge_result = $client_model -> table('co_charge') -> where(array('status' => 1)) -> select();
		
		$this -> assign('channel_type',$channel_type);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('chname',$chname);
		$this -> assign('client_name',$client_name);
		$this -> assign('charge_id',$charge_id);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('rank',$rank);
		$this -> assign('charge_result',$charge_result);
		$this -> assign('result',$result);
		
		
		$from = $_GET['from'];
	
		if($from == 1){
			$upload_result = $coefficient_model -> table($table) -> where($where) -> order($order) -> select();

			foreach($upload_result as $key => $val){
				if(!$rank || $rank == 1 || $rank == 2){
					if($channel_type == 1){
						$table_for = 'activation_coefficient_state';
					}else{
						$table_for = 'activation_game_coefficient_state';
					}
					$activation_coefficient_result = $coefficient_model -> table($table_for) -> where(array('submit_tm' => $val['submit_tm'],'cid' => $val['cid'])) -> select();
					$val['activation'] = $val['counts'];
					$val['activation_coefficient'] = $activation_coefficient_result[0]['counts'];
					$val['coefficient'] = 	$activation_coefficient_result[0]['coefficient'];	
				}elseif($rank == 3 || $rank == 4){
					if($channel_type == 1){
						$table_for = 'activation_state';
					}else{
						$table_for = 'activation_game_state';
					}
					$activation_result = $coefficient_model -> table($table_for) -> where(array('submit_tm' => $val['submit_tm'],'cid' => $val['cid'])) -> select();
					$val['activation_coefficient'] = $val['counts'];
					$val['activation'] = $activation_result[0]['counts'];
					$val['coefficient'] = $val['coefficient'];	
				}
				$channel_name_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$val['chname'] = $channel_name_result[0]['chname'];
				$my_channel_result = $client_model -> table('co_client_channel') -> where(array('cid' => $val['cid'],'status' => 1)) -> select();
				$my_client_result = $client_model -> table('co_client_list') -> where(array('id' => 		$my_channel_result[0]['client_id'])) -> select();
				$val['client_name'] = $my_client_result[0]['client_name'];
				$my_charge_result = $client_model -> table('co_charge') -> where(array('id' => $my_client_result[0]['charge_id'])) -> select();
				$val['charge_name'] = $my_charge_result[0]['charge_name'];
				$file_str .= date('Y/m/d',$val['submit_tm']).','.$val['cid'].','.$val['chname'].','.$val['client_name'].','.$val['charge_name'].','.$val['activation'].','.$val['coefficient'].'%,'.$val['activation_coefficient']."\n";
			}
		
			$file_gos = '历史激活量_'.date('Ymd').".csv";//文件名
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){
				$file_go = urlencode($file_gos); 
			}else{
				$file_go = $file_gos;
			}
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go);
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_end_clean();
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"日期,渠道ID,渠道名称,客户名称,负责人,扣量前激活量,扣量系数,扣量后激活量\n");
			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str;
			echo $file_str_go;
			ob_end_flush();
			exit;
		}
		
		$this -> display();
	}
	
	
	function coefficient_stats(){
		ini_set('memory_limit','-1');
		$coefficient_model = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation');
		if($_GET['start_tm']){
			$start_tm = strtotime(date('Ymd 00:00:00',strtotime($_GET['start_tm'])));
		}else{
			$start_tm = strtotime(date('Y-m-d 00:00:00',time()-86400));
			$_GET['start_tm'] = date('Y-m-d 0:0:0',time()-86400);
		}
		if($_GET['end_tm']){
			$end_tm = strtotime(date('Ymd 23:59:59',strtotime($_GET['end_tm'])));
		}else{
			$end_tm = strtotime(date('Ymd 23:59:59',time()-86400));
			$_GET['end_tm'] = date('Y-m-d 23:59:59',time()-86400);
		}
		if($start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$channel_type = $_GET['channel_type'];
		$group_result = $client_model -> table('co_group') -> where(array('status' => 1)) -> select();
		$no_table = 'activation_state';
		$yes_table = 'activation_coefficient_state';

		foreach($group_result as $key => $val){
			$charge_result = $client_model -> table('co_charge') -> where(array('group_id' => $val['id'])) -> select();
			$charge_str_go = '';
			foreach($charge_result as $k => $v){
				
				$charge_str_go .= $v['id'].',';
			}
		
			$charge_str = substr($charge_str_go,0,-1);
			$client_where['_string'] = "charge_id in ({$charge_str})";
			$client_result = $client_model -> table('co_client_list') -> where($client_where) -> select();
			$client_str_go = '';
			foreach($client_result as $k => $v){
				$client_str_go .= $v['id'].',';
			}
			$client_str = substr($client_str_go,0,-1);
			$channel_where['_string'] = "client_id in ({$client_str}) and status != 0";
			$channel_result = $client_model -> table('co_client_channel') -> where($channel_where) -> select();
		
			$cid_str_go = '';
			foreach($channel_result as $k => $v){
				$cid_str_go .= $v['cid'].',';
			}
			$cid_str = substr($cid_str_go,0,-1);
			if($start_tm && $end_tm){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
			}elseif($start_tm && !$end_tm){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm >= {$start_tm} and status = 1";
			}elseif(!$start_tm && $end_tm){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm <= {$end_tm} and status = 1";
			}elseif(!$start_tm && !$end_tm){
				$activation_where['_string'] = "cid in ({$cid_str}) and status = 1";
			}
			
			$activation_result = $coefficient_model -> table($no_table) -> where($activation_where) -> select();
		
			$my_activation = array();
			foreach($activation_result as $k => $v){
				$my_activation[] = $v['counts'];
			}
			$activation_coefficient_result = $coefficient_model -> table($yes_table) -> where($activation_where) -> select();
			$my_activation_coefficient = array();
			foreach($activation_coefficient_result as $k => $v){
				$my_activation_coefficient[] = $v['counts'];
			}
			$val['activation'] = array_sum($my_activation);
			$val['activation_coefficient'] = array_sum($my_activation_coefficient);
			$val['activation_ratio'] = round((($val['activation_coefficient']/$val['activation'])*100));
			$group_result[$key] = $val;
		}

		if(!$_GET['group_order'] || $_GET['group_order'] == 1){
			//扣量前降序排序
			foreach($group_result as $key => $val){
				$activation_cofficient_arr[] = $val['activation'];
			}
			array_multisort($activation_cofficient_arr,SORT_DESC,$group_result);
			$group_order = 1;
		}elseif($_GET['group_order'] == 2){
			//扣量前升序排序
			foreach($group_result as $key => $val){
				$activation_cofficient_arr[] = $val['activation'];
			}
			array_multisort($activation_cofficient_arr,SORT_ASC,$group_result);
			$group_order = 2;
		}elseif($_GET['group_order'] == 3){
			//扣量后降序排序
			foreach($group_result as $key => $val){
				$activation_arr[] = $val['activation_coefficient'];
			}
			array_multisort($activation_arr,SORT_DESC,$group_result);
			$group_order = 3;
		}elseif($_GET['group_order'] == 4){
			//扣量后升序排序
			foreach($group_result as $key => $val){
				$activation_arr[] = $val['activation_coefficient'];
			}
			array_multisort($activation_arr,SORT_ASC,$group_result);
			$group_order = 4;
		}
		
		$the_charge_result = $client_model -> table('co_charge') -> where(array('status' => 1)) -> select();
		foreach($the_charge_result as $key => $val){
			$my_client_where['_string'] = "charge_id = {$val['id']}";
			$my_client_result = $client_model -> table('co_client_list') -> where($my_client_where) -> select();
			$my_client_str_go = '';
			foreach($my_client_result as $k => $v){
				$my_client_str_go .= $v['id'].',';
			}
		
			$my_client_str = substr($my_client_str_go,0,-1);
			$my_channel_where['_string'] = "client_id in ({$my_client_str}) and status != 0";
			$my_channel_result = $client_model -> table('co_client_channel') -> where($my_channel_where) -> select();
			
			$my_cid_str_go = '';
			foreach($my_channel_result as $k => $v){
				$my_cid_str_go .= $v['cid'].',';
			}
			
			$my_cid_str = substr($my_cid_str_go,0,-1);
			if($start_tm && $end_tm){
				$my_activation_where['_string'] = "cid in ({$my_cid_str}) and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
			}elseif($start_tm && !$end_tm){
				$my_activation_where['_string'] = "cid in ({$my_cid_str}) and submit_tm >= {$start_tm} and status = 1";
			}elseif(!$start_tm && $end_tm){
				$my_activation_where['_string'] = "cid in ({$my_cid_str}) and submit_tm <= {$end_tm} and status = 1";
			}elseif(!$start_tm && !$end_tm){
				$my_activation_where['_string'] = "cid in ({$my_cid_str}) and status = 1";
			}
			
			$my_activation_result = $coefficient_model -> table($no_table) -> where($my_activation_where) -> select();
		
			$my_charge_activation = array();
			foreach($my_activation_result as $k => $v){
				$my_charge_activation[] = $v['counts'];
			}
			$my_activation_coefficient_result = $coefficient_model -> table($yes_table) -> where($my_activation_where) -> select();
			$my_charge_activation_coefficient = array();
			foreach($my_activation_coefficient_result as $k => $v){
				$my_charge_activation_coefficient[] = $v['counts'];
			}
			$val['activation'] = array_sum($my_charge_activation);
			$val['activation_coefficient'] = array_sum($my_charge_activation_coefficient);
			$val['activation_ratio'] = round((($val['activation_coefficient']/$val['activation'])*100));
			$the_charge_result[$key] = $val;
		}
		
		if(!$_GET['charge_order'] || $_GET['charge_order'] == 1){
			//扣量前降序排序
			foreach($the_charge_result as $key => $val){
				$charge_activation_cofficient_arr[] = $val['activation'];
			}
			array_multisort($charge_activation_cofficient_arr,SORT_DESC,$the_charge_result);
			$charge_order = 1;
		}elseif($_GET['charge_order'] == 2){
			//扣量前升序排序
			foreach($the_charge_result as $key => $val){
				$charge_activation_cofficient_arr[] = $val['activation'];
			}
			array_multisort($charge_activation_cofficient_arr,SORT_ASC,$the_charge_result);
			$charge_order = 2;
		}elseif($_GET['charge_order'] == 3){
			//扣量后降序排序
			foreach($the_charge_result as $key => $val){
				$charge_activation_arr[] = $val['activation_coefficient'];
			}
			array_multisort($charge_activation_arr,SORT_DESC,$the_charge_result);
			$charge_order = 3;
		}elseif($_GET['charge_order'] == 4){
			//扣量后升序排序
			foreach($the_charge_result as $key => $val){
				$charge_activation_arr[] = $val['activation_coefficient'];
			}
			array_multisort($charge_activation_arr,SORT_ASC,$the_charge_result);
			$charge_order = 4;
		}
		
		
		foreach($group_result as $key => $val){
			$activation_arr[] = $val['activation'];
			$activation_coefficient_arr [] = $val['activation_coefficient'];
		}
		
		//总计
		$all_activation = array_sum($activation_arr);
		$all_activation_coefficient = array_sum($activation_coefficient_arr);
		
		if(!$_GET['my_show'] || $_GET['my_show'] == 1){
			$admin_id = $_SESSION['admin']['admin_id'];
			$admin_filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
			foreach($admin_filter_result as $key => $val){
				$admin_cid[] = $val['target_value'];
			}
		
			$all_client = $client_model -> table('co_client_list') -> order('id') -> select();
	
			if($admin_cid){
				foreach($all_client as $key => $val){
					$client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $val['id'])) -> select();
					$my_cid_power = array();
					foreach($client_channel_result as $k => $v){
						$my_cid_power[] = $v['cid'];
					}
					if(!array_diff($my_cid_power,$admin_cid)){
						$my_client_id_str .= $val['id'].',';
					}
				}
			}else{
				$my_client_id = '';
			}
			$my_client_id = substr($my_client_id_str,0,-1);
			$my_where .= " and id in({$my_client_id})";
			
			$charge_id = $_GET['charge_id'];
			$client_name = trim($_GET['client_name']);
			if($charge_id){
				$my_where .= " and charge_id = {$charge_id}";
			}
			if($client_name){
				$my_where .= " and client_name like '%{$client_name}%'";
			}
			$the_client_where['_string'] = "status != 0".$my_where;
			$the_client_count = $client_model -> table('co_client_list') -> where($the_client_where) -> count();
			$the_client_result = $client_model -> table('co_client_list') -> where($the_client_where) -> select();
			
			foreach($the_client_result as $key => $val){
				$the_client_charge_result = $client_model -> table('co_charge') -> where(array('id' => $val['charge_id'])) -> select();
				$val['charge_name'] = $the_client_charge_result[0]['charge_name'];
			
				$the_client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $val['id'],'status' => 1)) -> select();
			
				$the_client_channel_str_go = '';
				foreach($the_client_channel_result as $k => $v){
					$the_client_channel_str_go .= $v['cid'].',';
				}
				$client_cid_str = substr($the_client_channel_str_go,0,-1);
			
				if($start_tm && $end_tm){
					$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
				}elseif($start_tm && !$end_tm){
					$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and submit_tm >= {$start_tm} and status = 1";
				}elseif(!$start_tm && $end_tm){
					$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and submit_tm <= {$end_tm} and status = 1";
				}elseif(!$start_tm && !$end_tm){
					$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and status = 1";
				}
				$the_client_activation_result = $coefficient_model -> table($no_table) -> where($the_client_activation_where) -> select();
				$the_client_activation_arr = array();
				foreach($the_client_activation_result as $k => $v){
					$the_client_activation_arr[] = $v['counts'];
				}
				
				$val['activation'] = array_sum($the_client_activation_arr);
				$the_client_activation_coefficient_result = $coefficient_model -> table($yes_table) -> where($the_client_activation_where) -> select();
				$the_client_activation_coefficient_arr = array();
				foreach($the_client_activation_coefficient_result as $k => $v){
					$the_client_activation_coefficient_arr[] = $v['counts'];
				}
				$val['activation_coefficient'] = array_sum($the_client_activation_coefficient_arr);
				$val['activation_ratio'] = round(($val['activation_coefficient']/$val['activation'])*100);
				$the_client_result[$key] = $val;
			}
			
			if(!$_GET['client_order'] || $_GET['client_order'] == 1){
				//扣量前降序排序
				foreach($the_client_result as $key => $val){
					$client_activation_cofficient_arr[] = $val['activation'];
				}
				array_multisort($client_activation_cofficient_arr,SORT_DESC,$the_client_result);
				$client_order = 1;
			}elseif($_GET['client_order'] == 2){
				//扣量前升序排序
				foreach($the_client_result as $key => $val){
					$client_activation_cofficient_arr[] = $val['activation'];
				}
				array_multisort($client_activation_cofficient_arr,SORT_ASC,$the_client_result);
				$client_order = 2;
			}elseif($_GET['client_order'] == 3){
				//扣量后降序排序
				foreach($the_client_result as $key => $val){
					$client_activation_arr[] = $val['activation_coefficient'];
				}
				array_multisort($client_activation_arr,SORT_DESC,$the_client_result);
				$client_order = 3;
			}elseif($_GET['client_order'] == 4){
				//扣量后升序排序
				foreach($the_client_result as $key => $val){
					$client_activation_arr[] = $val['activation_coefficient'];
				}
				array_multisort($client_activation_arr,SORT_ASC,$the_client_result);
				$client_order = 4;
			}
			
			$this -> assign("client_order",$client_order);		
		}elseif($_GET['my_show'] == 2){
			$charge_id = $_GET['charge_id'];
			$chname = trim($_GET['chname']);
			$client_name = trim($_GET['client_name']);
			if($chname){
				$channel_channel_where['_string'] = "chname like '%{$chname}%' and status = 1";
				$channel_channel_result = $model -> table('sj_channel') -> where($channel_channel_where) -> select();
				foreach($channel_channel_result as $key => $val){
					$channel_cid_str_arr[] = $val['cid'];
				}
			}
			if($client_name && $charge_id){
				$channel_client_where['_string'] = "client_name like '%{$client_name}%' and charge_id = {$charge_id} and status !=0";
				$channel_client_result = $client_model -> table('co_client_list') -> where($channel_client_where) -> select();
				foreach($channel_client_result as $key => $val){
					$channel_client_id_str .= $val['id'].',';
				}
				$channel_client_id = substr($channel_client_id_str,0,-1);
				$channel_client_channel_where['_string'] = "client_id in ({$channel_client_id}) and status = 1";
				$channel_client_channel_result = $client_model -> table('co_client_channel') -> where($channel_client_channel_where) -> select();
				foreach($channel_client_channel_result as $key => $val){
					$channel_client_id_arr[] = $val['cid'];
				}
			}elseif(!$client_name && $charge_id){
				$channel_client_where['_string'] = "charge_id = {$charge_id} and status != 0";
				$channel_client_result = $client_model -> table('co_client_list') -> where($channel_client_where) -> select();
				foreach($channel_client_result as $key => $val){
					$channel_client_id_str .= $val['id'].',';
				}
			
				$channel_client_id = substr($channel_client_id_str,0,-1);
				$channel_client_channel_where['_string'] = "client_id in ({$channel_client_id}) and status = 1";
				$channel_client_channel_result = $client_model -> table('co_client_channel') -> where($channel_client_channel_where) -> select();
				foreach($channel_client_channel_result as $key => $val){
					$channel_client_id_arr[] = $val['cid'];
				}
	
			}elseif($client_name && !$charge_id){
				$channel_client_where['_string'] = "client_name like '%{$client_name}%' and status !=0";
				$channel_client_result = $client_model -> table('co_client_list') -> where($channel_client_where) -> select();
				foreach($channel_client_result as $key => $val){
					$channel_client_id_str .= $val['id'].',';
				}
				$channel_client_id = substr($channel_client_id_str,0,-1);
				$channel_client_channel_where['_string'] = "client_id in ({$channel_client_id}) and status = 1";
				$channel_client_channel_result = $client_model -> table('co_client_channel') -> where($channel_client_channel_where) -> select();
			
				foreach($channel_client_channel_result as $key => $val){
					$channel_client_id_arr[] = $val['cid'];
				}
			
			}
		
			if(($chname && $client_name && $charge_id) || ($chname && !$client_name && $charge_id) || ($chname && $client_name && !$charge_id)){
				
				if($channel_cid_str_arr && $channel_client_id_arr){
					$channel_cid_arr = array_intersect($channel_cid_str_arr,$channel_client_id_arr);
				}else{
					$channel_cid_arr = array();
				}
				foreach($channel_cid_arr as $key => $val){
					$channel_cid_str_go .= $val.',';
				}
				$channel_cid_str = substr($channel_cid_str_go,0,-1);
				$channel_where_go .= " and cid in ({$channel_cid_str})";
			}elseif($chname && !$client_name && !$charge_id){
				$channel_cid_arr = $channel_cid_str_arr;
				foreach($channel_cid_arr as $key => $val){
					$channel_cid_str_go .= $val.',';
				}
				$channel_cid_str = substr($channel_cid_str_go,0,-1);
				$channel_where_go .= " and cid in ({$channel_cid_str})";
			}elseif((!$chname && $client_name && !$charge_id) || (!	$chname && !$client_name && $charge_id) || (!$chname && $client_name && $charge_id)){
				$channel_cid_arr = $channel_client_id_arr;
				foreach($channel_cid_arr as $key => $val){
					$channel_cid_str_go .= $val.',';
				}
				$channel_cid_str = substr($channel_cid_str_go,0,-1);
				$channel_where_go .= " and cid in ({$channel_cid_str})";
			}
			$admin_id = $_SESSION['admin']['admin_id'];
			$filter_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
			foreach($filter_result as $key => $val){
				$admin_cid_str .= $val['target_value'].',';
			}
			$admin_cid = substr($admin_cid_str,0,-1);
			$count_power_result = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $admin_id,'target_type' => 9)) -> find();
			if($count_power_result['filter_type'] == 2){
				$channel_where_go .= " and cid in ({$admin_cid})";
			}
	
			$channel_where['_string'] = "status = 1".$channel_where_go;
			$the_channe_count = $the_channel_result = $client_model -> table('co_client_channel') -> where($channel_where) -> count();
			$the_channel_result = $client_model -> table('co_client_channel') -> where($channel_where) -> select();
		
			$all_coefficient_result = $model -> table('sj_channel_coefficient') -> where(array('status' => 1)) -> select();
			foreach($all_coefficient_result as $key => $val){
				$all_cid[] = $val['cid'];
			}

			foreach($the_channel_result as $key => $val){
				$the_channel_chname_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$val['chname'] = $the_channel_chname_result[0]['chname'];
				$the_channel_client_result = $client_model -> table('co_client_list') -> where(array('id' => $val['client_id'])) -> select();
				$the_channel_charge_result = $client_model -> table('co_charge') -> where(array('id' => $the_channel_client_result[0]['charge_id'])) -> select();
				$val['charge_name'] = $the_channel_charge_result[0]['charge_name'];
				$the_channel_coefficient_result = $model -> table('sj_channel_coefficient') -> where(array('cid' => $val['cid'],'status' => 1)) -> select();
				if(in_array($val['cid'],$all_cid)){
					$val['coefficient'] = $the_channel_coefficient_result[0]['coefficient'];
				}else{
					$val['coefficient'] = 100;
				}
				
				$val['qualit'] = $the_channel_chname_result[0]['qualit'];
				$val['platform'] = $the_channel_chname_result[0]['platform'];
				$val['co_status'] = $the_channel_chname_result[0]['co_status'];
				if($start_tm && $end_tm){
					$the_channel_activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
				}elseif($start_tm && !$end_tm){
					$the_channel_activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and status = 1";
				}elseif(!$start_tm && $end_tm){
					$the_channel_activation_where['_string'] = "cid = {$val['cid']} and submit_tm <= {$end_tm} and status = 1";
				}elseif(!$start_tm && !$end_tm){
					$the_channel_activation_where['_string'] = "cid = {$val['cid']} and status = 1";
				}
				$the_channel_activation_result = $coefficient_model -> table($no_table) -> where($the_channel_activation_where) -> select();
				$the_channel_activation_arr = array();
				foreach($the_channel_activation_result as $k => $v){
					$the_channel_activation_arr[] = $v['counts'];
				}
				$val['activation'] = array_sum($the_channel_activation_arr);
				$the_channel_activation_coefficient_result = $coefficient_model -> table($yes_table) -> where($the_channel_activation_where) -> select();
				$the_channel_activation_coefficient_arr = array();
				foreach($the_channel_activation_coefficient_result as $k => $v){
					$the_channel_activation_coefficient_arr[] = $v['counts'];
				}
				$val['activation_coefficient'] = array_sum($the_channel_activation_coefficient_arr);
				$val['activation_ratio'] = round(($val['activation_coefficient']/$val['activation'])*100);
				$the_channel_result[$key] = $val;
			}
			
			if(!$_GET['channel_order'] || $_GET['channel_order'] == 1){
				//扣量前降序排序
				foreach($the_channel_result as $key => $val){
					$channel_activation_cofficient_arr[] = $val['activation'];
				}
				array_multisort($channel_activation_cofficient_arr,SORT_DESC,$the_channel_result);
				$channel_order = 1;
			}elseif($_GET['channel_order'] == 2){
				//扣量前升序排序
				foreach($the_channel_result as $key => $val){
					$channel_activation_cofficient_arr[] = $val['activation'];
				}
				array_multisort($channel_activation_cofficient_arr,SORT_ASC,$the_channel_result);
				$channel_order = 2;
			}elseif($_GET['channel_order'] == 3){
				//扣量后降序排序
				foreach($the_channel_result as $key => $val){
					$channel_activation_arr[] = $val['activation_coefficient'];
				}
				array_multisort($channel_activation_arr,SORT_DESC,$the_channel_result);
				$channel_order = 3;
			}elseif($_GET['channel_order'] == 4){
				//扣量后升序排序
				foreach($the_channel_result as $key => $val){
					$channel_activation_arr[] = $val['activation_coefficient'];
				}
				array_multisort($channel_activation_arr,SORT_ASC,$the_channel_result);
				$channel_order = 4;
			}
			
			$this -> assign("channel_order",$channel_order);	
		}
		$charge_list_result = $client_model -> table('co_charge') -> where(array('status' => 1)) -> select();
		//昨日总激活量环比
		$yesterdays = strtotime('-1 day');
		$yesterday_time = date('Y-m-d 00:00:00',$yesterdays);
		$yesterday = strtotime($yesterday_time);
		$before_yesterdays = strtotime('-2 day');
		$before_yesterday_time = date('Y-m-d 00:00:00',$before_yesterdays);
		$before_yesterday = strtotime($before_yesterday_time);
		$yesterday_result = $coefficient_model -> table('activation_coefficient_state') -> where(array('submit_tm' => $yesterday,'status' => 1)) -> field('sum(counts) as counts') -> select();
		$before_yesterday_result = $coefficient_model -> table('activation_coefficient_state') -> where(array('submit_tm' => $before_yesterday,'status' => 1)) -> field('sum(counts) as counts') -> select();
		$ratios = floor($yesterday_result[0]['counts'] - $before_yesterday_result[0]['counts'])/$before_yesterday_result[0]['counts']*100;
		$ratio = sprintf('%.2f', $ratios);
		$this -> assign('charge_list_result',$charge_list_result);
		if($_GET['my_show']){
			$my_show = $_GET['my_show'];
		}else{
			$my_show = 1;
		}

		$this -> assign('yesterday_activation',$yesterday_result[0]['counts']);
		$this -> assign('ratio',$ratio);
		$this -> assign('my_show',$my_show);
		$this -> assign("all_activation",$all_activation);
		$this -> assign("all_activation_coefficient",$all_activation_coefficient);
		$this -> assign("group_result",$group_result);
		$this -> assign("the_charge_result",$the_charge_result);
		$this -> assign("start_tm",$_GET['start_tm']);
		$this -> assign("end_tm",$_GET['end_tm']);
		$this -> assign("the_client_result",$the_client_result);
		$this -> assign("the_channel_result",$the_channel_result);
		$this -> assign("chname",$chname);
		$this -> assign("charge_id",$charge_id);
		$this -> assign("client_name",$client_name);
		$this -> assign("group_order",$group_order);
		$this -> assign("charge_order",$charge_order);
		if($_GET['from'] == 1){
			if($_GET['my_show'] == 1){
				foreach($the_client_result as $key => $val){
					$the_client_charge_result = $client_model -> table('co_charge') -> where(array('id' => $val['charge_id'])) -> select();
					$val['charge_name'] = $the_client_charge_result[0]['charge_name'];
			
					$the_client_channel_result = $client_model -> table('co_client_channel') -> where(array('client_id' => $val['id'],'status' => 1)) -> select();
			
					$the_client_channel_str_go = '';
					foreach($the_client_channel_result as $k => $v){
						$the_client_channel_str_go .= $v['cid'].',';
					}
					$client_cid_str = substr($the_client_channel_str_go,0,-1);
			
					if($start_tm && $end_tm){
						$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
					}elseif($start_tm && !$end_tm){
						$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and submit_tm >= {$start_tm} and status = 1";
					}elseif(!$start_tm && $end_tm){
						$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and submit_tm <= {$end_tm} and status = 1";
					}elseif(!$start_tm && !$end_tm){
						$the_client_activation_where['_string'] = "cid in ({$client_cid_str}) and status = 1";
					}
					$the_client_activation_result = $coefficient_model -> table($no_table) -> where($the_client_activation_where) -> select();
					$the_client_activation_arr = array();
					foreach($the_client_activation_result as $k => $v){
						$the_client_activation_arr[] = $v['counts'];
					}
				
					$val['activation'] = array_sum($the_client_activation_arr);
					$the_client_activation_coefficient_result = $coefficient_model -> table($yes_table) -> where($the_client_activation_where) -> select();
					$the_client_activation_coefficient_arr = array();
					foreach($the_client_activation_coefficient_result as $k => $v){
						$the_client_activation_coefficient_arr[] = $v['counts'];
					}
					$val['activation_coefficient'] = array_sum($the_client_activation_coefficient_arr);
					$val['activation_ratio'] = round(($val['activation_coefficient']/$val['activation'])*100);
					
					$file_str .= $val['client_name'].','.$val['charge_name'].','.$val['activation'].','.$val['activation_coefficient'].','.$val['activation_ratio'].'%'."\n";
				}
			
				$file_gos = '客户激活量_'.date('Ymd').".csv";//文件名
				if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){
					$file_go = urlencode($file_gos); 
				}else{
					$file_go = $file_gos;
				}
				header( "Cache-Control: public" );
				header( "Pragma: public" );
				header("Content-type:application/vnd.ms-excel");
				header('Content-Disposition:attachment;filename='.$file_go);
				header('Content-Type:APPLICATION/OCTET-STREAM');
				ob_end_clean();
				ob_start();
				$header_str =  iconv("UTF-8",'GBK',"客户名称,负责人,扣量前,扣量后,结算系数");
				$file_str_go=  iconv("UTF-8",'GBK',$file_str);
				echo $header_str."\n";
				echo $file_str_go;
				ob_end_flush();
				exit;
			}else{
				
				foreach($the_channel_result as $key => $val){
					$the_channel_chname_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
					$val['chname'] = $the_channel_chname_result[0]['chname'];
					$the_channel_client_result = $client_model -> table('co_client_list') -> where(array('id' => $val['client_id'])) -> select();
					$the_channel_charge_result = $client_model -> table('co_charge') -> where(array('id' => $the_channel_client_result[0]['charge_id'])) -> select();
					$val['charge_name'] = $the_channel_charge_result[0]['charge_name'];
					$the_channel_coefficient_result = $model -> table('sj_channel_coefficient') -> where(array('cid' => $val['cid'],'status' => 1)) -> select();
					$val['coefficient'] = $the_channel_coefficient_result[0]['coefficient'];
					$val['qualit'] = $the_channel_chname_result[0]['qualit'];
					$val['platform'] = $the_channel_chname_result[0]['platform'];
					$val['co_status'] = $the_channel_chname_result[0]['co_status'];
					if($start_tm && $end_tm){
						$the_channel_activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and submit_tm <= {$end_tm} and status = 1";
					}elseif($start_tm && !$end_tm){
						$the_channel_activation_where['_string'] = "cid = {$val['cid']} and submit_tm >= {$start_tm} and status = 1";
					}elseif(!$start_tm && $end_tm){
						$the_channel_activation_where['_string'] = "cid = {$val['cid']} and submit_tm <= {$end_tm} and status = 1";
					}elseif(!$start_tm && !$end_tm){
						$the_channel_activation_where['_string'] = "cid = {$val['cid']} and status = 1";
					}
					$the_channel_activation_result = $coefficient_model -> table($no_table) -> where($the_channel_activation_where) -> select();
					$the_channel_activation_arr = array();
					foreach($the_channel_activation_result as $k => $v){
						$the_channel_activation_arr[] = $v['counts'];
					}
					$val['activation'] = array_sum($the_channel_activation_arr);
					$the_channel_activation_coefficient_result = $coefficient_model -> table($yes_table) -> where($the_channel_activation_where) -> select();
					$the_channel_activation_coefficient_arr = array();
					foreach($the_channel_activation_coefficient_result as $k => $v){
						$the_channel_activation_coefficient_arr[] = $v['counts'];
					}
					$val['activation_coefficient'] = array_sum($the_channel_activation_coefficient_arr);
					$val['activation_ratio'] = round(($val['activation_coefficient']/$val['activation'])*100);
					if($val['co_status'] == 1){
						$co_status = "正常";
					}else{
						$co_status = "暂停";
					}
					if($val['qualit'] == 1){
						$qualit = "优质";
					}elseif($val['qualit'] == 2){
						$qualit = "普通";
					}elseif($val['qualit'] == 3){
						$qualit = "较差";
					}else{
						$qualit = "未编辑";
					}
					if($val['platform'] == 1){
						$platform = "安智市场";
					}elseif($val['platform'] == 2){
						$platform = "安智助手";
					}elseif($val['platform'] == 3){
						$platform = "sd卡备份	";
					}elseif($val['platform'] == 4){
						$platform = "平板";
					}elseif($val['platform'] == 5){
						$platform = "游戏客户端";
					}
					if($val['coefficient']){
						$coefficient = $val['coefficient'];
					}else{
						$coefficient = 100;
					}
					$file_str .= $val['chname'].','.$val['charge_name'].','.$co_status.','.$val['activation'].','.$val['activation_coefficient'].','.$val['activation_ratio'].'%'.','.$coefficient.'%'.','.$qualit.','.$platform."\n";
				}
				
				$file_gos = '渠道激活量_'.date('Ymd').".csv";//文件名
				if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){
					$file_go = urlencode($file_gos); 
				}else{
					$file_go = $file_gos;
				}
				header( "Cache-Control: public" );
				header( "Pragma: public" );
				header("Content-type:application/vnd.ms-excel");
				header('Content-Disposition:attachment;filename='.$file_go);
				header('Content-Type:APPLICATION/OCTET-STREAM');
				ob_end_clean();
				ob_start();
				$header_str =  iconv("UTF-8",'GBK',"渠道名称,负责人,状态,扣量前,扣量后,结算系数,当前扣量系数,质量,平台类型");
				$file_str_go=  iconv("UTF-8",'GBK',$file_str);
				echo $header_str."\n";
				echo $file_str_go;
				ob_end_flush();
				exit;
			}
		}
		
		$this -> display();
	}
	
	function client_comment(){
		$client_model = D('Channel_cooperation.channel_cooperation');
		$id = $_GET['id'];
		$client_result = $client_model -> table('co_client_list') -> where(array('id' => $id)) -> select();
		$this -> assign('client_result',$client_result);
		$this -> display();
	}
	
	function machine_activation(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$cid = $_GET['cid'];
		if($_GET['start_tm']){
			$start_tm = strtotime(date('Ymd 00:00:00',strtotime($_GET['start_tm'])));
		}
		if($_GET['end_tm']){
			$end_tm = strtotime(date('Ymd 00:00:00',strtotime($_GET['end_tm'])));
		}
		if($_GET['start_tm']){
			$where_go .= " and date >= {$start_tm}";
		}
		if($_GET['end_tm']){
			$where_go .= " and date <= {$end_tm}";
		}
		$where['_string'] = "chl = {$cid}".$where_go;
		$mechine_result = $coefficient_model -> table('mobile_chl_soft_stat') -> where($where) -> select();
	
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		foreach($mechine_result as $key => $val){
			$mechine_name_result = $model -> table('pu_device') -> where(array('did' => $val['device'])) -> select();
			$val['device_name'] = $mechine_name_result[0]['dname'];
			$mechine_result[$key] = $val;
		}
		
		$this -> assign("start_tm",$_GET['start_tm']);
		$this -> assign("end_tm",$_GET['end_tm']);
		$this -> assign("chname",$channel_result[0]['chname']);
		$this -> assign("mechine_result",$mechine_result);
		$this -> display();
	}
	
	function channel_activation(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$cid = $_GET['cid'];
		if($_GET['start_tm']){
			$start_tm = strtotime(date('Ymd 00:00:00',strtotime($_GET['start_tm'])));
		}
		if($_GET['end_tm']){
			$end_tm = strtotime(date('Ymd 00:00:00',strtotime($_GET['end_tm'])));
		}
		if($_GET['start_tm']){
			$where_go .= " and date >= {$start_tm}";
		}
		if($_GET['end_tm']){
			$where_go .= " and date <= {$end_tm}";
		}
		$where['_string'] = "chl = {$cid}".$where_go;
		$channel_activation = $coefficient_model -> table('mobile_chl_soft_stat') -> where($where) -> field('date,chl,sum(activates) as activates,sum(apps) as apps,sum(games) as games') -> group('date') -> select();
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		foreach($channel_activation as $key => $val){
		
			$val['all_download'] = $val['games'] + $val['apps'];
			$coefficient_where['_string'] = "cid = {$val['chl']} and status = 1 and submit_tm = {$val['date']}";
			$coefficient_result = $coefficient_model -> table('activation_coefficient_state') -> where($coefficient_where) -> field('counts') -> select();
		
			$val['coefficient_activation'] = $coefficient_result[0]['counts'];
			$channel_activation[$key] = $val;
		}

		$this -> assign('start_tm',$_GET['start_tm']);
		$this -> assign('end_tm',$_GET['end_tm']);
		$this -> assign('chname',$channel_result[0]['chname']);
		$this -> assign('channel_activation',$channel_activation);
		$this -> display();
	}
	
	
	function activatequantity(){
		$channel_activation = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$cid = $_GET['cid'];
		if(!$_GET['my_time']){
			$my_time = 2;
		}else{
			$my_time = $_GET['my_time'];
		}
		
		if($my_time == 1){
			for($i=1;$i<=7;$i++){
				$week_time[] = date('Y/m/d',time()-$i*86400);
			}
			$week_time = array_reverse($week_time);
			foreach($week_time as $key => $val){
				$activation_result = $channel_activation -> table('activation_coefficient_state') -> where(array('cid' => $cid,'submit_tm' => strtotime($val),'status' => 1)) -> field('counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($week_time));
		}elseif($my_time == 2){
			for($i=1;$i<=30;$i++){
				$month_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=30;$i++){
				$month_times[] = date('Y/m/d',time()-$i*86400);
			}
			$month_time = array_reverse($month_time);
			$month_times = array_reverse($month_times);
			foreach($month_times as $key => $val){
				$activation_result = $channel_activation -> table('activation_coefficient_state') -> where(array('cid' => $cid,'submit_tm' => strtotime($val),'status' => 1)) -> field('counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			
			$this -> assign('the_time',json_encode($month_time));
			$this -> assign('the_times',json_encode(2));
		}elseif($my_time == 3){
			for($i=1;$i<=90;$i++){
				$most_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=90;$i++){
				$most_times[] = date('Y/m/d',time()-$i*86400);
			}
			$most_time = array_reverse($most_time);
			$most_times = array_reverse($most_times);
			foreach($most_times as $key => $val){
				$activation_result = $channel_activation -> table('activation_coefficient_state') -> where(array('cid' => $cid,'submit_tm' => strtotime($val),'status' => 1)) -> field('counts') -> select();
	
				$activation[] = intval($activation_result[0]['counts']);
			}

			$this -> assign('the_time',json_encode($most_time));
			$this -> assign('the_times',json_encode(3));
		}
	
		$channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
		$jsdata = array(
			'name' => '激活量',
			'data' => $activation
		);
	
		$this -> assign('chname',$channel_result[0]['chname']);
		$this -> assign('jsdata',json_encode($jsdata));
		$this -> assign('cid',$cid);
		$this -> display();
		
	}
	
	
	function groupquantity(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation');
		$group_id = $_GET['group_id'];
		$group_charge_result = $client_model -> table('co_charge') -> where(array('group_id' => $group_id)) -> field('id') -> select();
		
		foreach($group_charge_result as $key => $val){
			$client_result = $client_model -> table('co_client_list') -> where(array('charge_id' => $val['id'])) -> select();
			$client_str_go = '';
			foreach($client_result as $k => $v){
				$client_str_go .= $v['id'].',';
			}
			$client_str = substr($client_str_go,0,-1);
			$all_client_str_go .= $client_str.',';
		}
		$all_client_str = substr($all_client_str_go,0,-2);
		$cid_where['_string'] = "client_id in ({$all_client_str}) and status = 1";
		$cid_result = $client_model -> table('co_client_channel')  -> where($cid_where) -> select();
	
		foreach($cid_result as $key => $val){
			$cid_str_go .= $val['cid'].',';
		}
		$cid_str = substr($cid_str_go,0,-1);
		if(!$_GET['my_time']){
			$my_time = 2;
		}else{
			$my_time = $_GET['my_time'];
		}
	
		if($my_time == 1){
			for($i=1;$i<=7;$i++){
				$week_time[] = date('Y/m/d',time()-$i*86400);
			}
			$week_time = array_reverse($week_time);
			foreach($week_time as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($week_time));
		}elseif($my_time == 2){
			for($i=1;$i<=30;$i++){
				$month_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=30;$i++){
				$month_times[] = date('Y/m/d',time()-$i*86400);
			}
			$month_time = array_reverse($month_time);
			$month_times = array_reverse($month_times);
			foreach($month_times as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($month_time));
			$this -> assign('the_times',json_encode(2));
		}elseif($my_time == 3){
			for($i=1;$i<=90;$i++){
				$most_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=90;$i++){
				$most_times[] = date('Y/m/d',time()-$i*86400);
			}
			$most_time = array_reverse($most_time);
			$most_times = array_reverse($most_times);
			foreach($most_times as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($most_time));
			$this -> assign('the_times',json_encode(3));
		}
		
		$group_result = $client_model -> table('co_group') -> where(array('id' => $group_id)) -> find();
		
		$jsdata = array(
			'name' => '激活量',
			'data' => $activation
		);
	
		$this -> assign('group_name',$group_result['group_name']);
		$this -> assign('jsdata',json_encode($jsdata));
		$this -> assign('group_id',$group_id);
		$this -> display();
	}
	
	
	function chargequantity(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation');
		$charge_id = $_GET['charge_id'];
		$charge_result = $client_model -> table('co_client_list') -> where(array('charge_id' => $charge_id)) -> field('id') -> select();
		foreach($charge_result as $k => $v){
			$client_str_go .= $v['id'].',';
		}
		$client_str = substr($client_str_go,0,-1);
		$cid_where['_string'] = "client_id in ({$client_str}) and status = 1";
		$cid_result = $client_model -> table('co_client_channel')  -> where($cid_where) -> select();
		foreach($cid_result as $key => $val){
			$cid_str_go .= $val['cid'].',';
		}
		$cid_str = substr($cid_str_go,0,-1);
		if(!$_GET['my_time']){
			$my_time = 2;
		}else{
			$my_time = $_GET['my_time'];
		}
		if($my_time == 1){
			for($i=1;$i<=7;$i++){
				$week_time[] = date('Y/m/d',time()-$i*86400);
			}
			$week_time = array_reverse($week_time);
			foreach($week_time as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($week_time));
		}elseif($my_time == 2){
			for($i=1;$i<=30;$i++){
				$month_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=30;$i++){
				$month_times[] = date('Y/m/d',time()-$i*86400);
			}
			$month_time = array_reverse($month_time);
			$month_times = array_reverse($month_times);
			foreach($month_times as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($month_time));
			$this -> assign('the_times',json_encode(2));
		}elseif($my_time == 3){
			for($i=1;$i<=90;$i++){
				$most_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=90;$i++){
				$most_times[] = date('Y/m/d',time()-$i*86400);
			}
			$most_time = array_reverse($most_time);
			$most_times = array_reverse($most_times);
			foreach($most_times as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($most_time));
			$this -> assign('the_times',json_encode(3));
		}
		
		$group_result = $client_model -> table('co_group') -> where(array('id' => $group_id)) -> find();
		
		$jsdata = array(
			'name' => '激活量',
			'data' => $activation
		);
		$charge_name_result = $client_model -> table('co_charge') -> where(array('id' => $charge_id)) -> find();
		$this -> assign('charge_id',$charge_id);
		$this -> assign('jsdata',json_encode($jsdata));
		$this -> assign('charge_name',$charge_name_result['charge_name']);
		$this -> display();
	}

	function clientquantity(){
		$coefficient_model = D('Channel_cooperation.channel_coefficient');
		$model = new Model();
		$client_model = D('Channel_cooperation.channel_cooperation');
		$client_id = $_GET['client_id'];
		$cid_where['_string'] = "client_id = {$client_id} and status = 1";
		$cid_result = $client_model -> table('co_client_channel')  -> where($cid_where) -> select();
		foreach($cid_result as $key => $val){
			$cid_str_go .= $val['cid'].',';
		}
		$cid_str = substr($cid_str_go,0,-1);
		if(!$_GET['my_time']){
			$my_time = 2;
		}else{
			$my_time = $_GET['my_time'];
		}
		if($my_time == 1){
			for($i=1;$i<=7;$i++){
				$week_time[] = date('Y/m/d',time()-$i*86400);
			}
			$week_time = array_reverse($week_time);
			foreach($week_time as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($week_time));
		}elseif($my_time == 2){
			for($i=1;$i<=30;$i++){
				$month_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=30;$i++){
				$month_times[] = date('Y/m/d',time()-$i*86400);
			}
			$month_time = array_reverse($month_time);
			$month_times = array_reverse($month_times);
			foreach($month_times as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($month_time));
			$this -> assign('the_times',json_encode(2));
		}elseif($my_time == 3){
			for($i=1;$i<=90;$i++){
				$most_time[] = date('m/d',time()-$i*86400);
			}
			for($i=1;$i<=90;$i++){
				$most_times[] = date('Y/m/d',time()-$i*86400);
			}
			$most_time = array_reverse($most_time);
			$most_times = array_reverse($most_times);
			foreach($most_times as $key => $val){
				$activation_where['_string'] = "cid in ({$cid_str}) and submit_tm = ".strtotime($val)." and status = 1";
				$activation_result = $coefficient_model -> table('activation_coefficient_state') -> where($activation_where) -> field('sum(counts) as counts') -> select();
				$activation[] = intval($activation_result[0]['counts']);
			}
			$this -> assign('the_time',json_encode($most_time));
			$this -> assign('the_times',json_encode(3));
		}
		
		$group_result = $client_model -> table('co_group') -> where(array('id' => $group_id)) -> find();
		
		$jsdata = array(
			'name' => '激活量',
			'data' => $activation
		);
		$client_name_result = $client_model -> table('co_client_list') -> where(array('id' => $client_id)) -> find();
		$this -> assign('client_id',$client_id);
		$this -> assign('jsdata',json_encode($jsdata));
		$this -> assign('client_name',$client_name_result['client_name']);
		$this -> display();
	}
	
}
?>


