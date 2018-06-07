<?php
header("Content-type: text/html; charset=utf-8"); 
class AdCustomer_pAction extends CommonAction{
	function index() {
		$model = D('Channel_cooperation.AdCustomer_p');
		$affairs = $model->affairs();
		$affairs_Terminal = $model->affairs_Terminal();
	
		foreach($affairs as $key => $val){
			foreach($val['charge_result'] as $k => $v){
				$client_where['_string'] = "charge_id = {$v['id']} and status != 0";
				$client_count = $model -> table('co_client_list') -> where($client_where) -> select();
				$v['client_count'] = count($client_count);
				$val['charge_result'][$k] = $v;
			}
			$affairs[$key] = $val;
		}

		$this->assign('group_result', $affairs);
		$this->assign('group_p2', $affairs_Terminal);
		$this->display();
	}
	function edit(){
		$model = D('Channel_cooperation.AdCustomer_p');
		$coid = $_GET['coid'];
		if (!empty($coid)) {
		    $co_info = $model->table('co_group')->where('id = ' . $coid)->select();
		    $this->assign('co_info', $co_info[0]);
		}
		$this->display();
	}
	function editdo() {
		$model = D('Channel_cooperation.AdCustomer_p');
		$id = $_POST['id'];
		$group_name = trim($_POST['xiaozu']);
		
		if (!empty($id)) {
			$bumen = $_POST['bumen'];
			$xiaozu = $_POST['xiaozu'];
			$where['_string'] = "group_name = {$group_name} and status = 1 and id != {$id}";
			$rets = $model ->table('co_group')->where($where)->select();
			if($rets){
				echo "<script>alert('该小组已存在');history.go(-1);</script>";die;
			}
			$date = array("group_name" => $xiaozu,"charge_section" => $bumen);
			$log_result = $this -> logcheck(array('id' => $id),'co_group',$date,$model);
			$re = $model->table('co_group')->where("id=$id")->save($date);
			
			if($re){
				$this -> writelog("客户负责人已编辑id为{$id}的小组".$log_result, 'co_group', $id,__ACTION__ ,'','edit');
			}
		} else {
			$bumen = $_POST['bumen'];
			$xiaozu = $_POST['xiaozu'];
			$have_been = $model ->table('co_group')->where(array('group_name' => $group_name,'status'=> 1))->select();
			$rets = $model ->table('co_group')->where(array('group_name' => $group_name,'status'=> 1))->select();
	
			if($rets){
				echo "<script>alert('该小组已存在');history.go(-1);</script>";die;
			}
			
			$date = array("group_name" => $xiaozu,"charge_section" => $bumen);
			$re = $model->table('co_group')->add($date);
			if($re){
				$this -> writelog("已添加客户负责人小组id为{$re}", 'co_group', $re,__ACTION__ ,'','add');
			}
		}
		if($re == false){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdCustomer_p/index');
			$this->error("您未做修改");
			//echo "<script>alert('您未做修改!');history.go(-1);</script>";die;
		}else{
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdCustomer_p/index');
			$this->success("操作成功");
		}
	}
	function groupdel(){
		$model = D('Channel_cooperation.AdCustomer_p');
		$id = $_GET['coid'];
		$res = $model ->table('co_group')->where(array('id' => $id)) ->delete();
		if($res){
			$this -> writelog("已删除id为{$id}的负责人小组", 'co_group', $id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdCustomer_p/index');
			$this->success("删除成功");
		}else{
			$this->error("删除失败");
		}
	}
	function add(){
		$model = D('Channel_cooperation.AdCustomer_p');
		$id = $_GET['chid'];
		if (!empty($id)) {
		    $ch_info = $model->table('co_charge')->where('id = ' . $id)->select();
		    $this->assign('ch_info', $ch_info[0]);
		}
		$sql = 'SELECT charge_section FROM co_group WHERE id = (SELECT group_id FROM co_charge WHERE	        id = ' . $id . ')';
		$g = $model->query($sql);
		$charge_section1 = $model->table('co_group')->where('charge_section = 1')->select();
		$charge_section2 = $model->table('co_group')->where('charge_section = 2')->select();
		//print_r($charge_section1);
		$this->assign('g', $g[0]);
		$this->assign('z1', $charge_section1);
		$this->assign('z2', $charge_section2);
		
		//2014.10.27 jiwei
		//汉化推端需求
		//新增论坛部门处理
		$charge_section3 = $model->table('co_group')->where('charge_section = 3')->select();
		$this->assign('z3', $charge_section3);
		//END
		
		$this->display();
	}
	function person() {
		$model = D('Channel_cooperation.AdCustomer_p');
		$id = $_GET['chid'];
		$group_id=$_GET['group_id'];
		$this->assign('group_id',$group_id);
		if (!empty($id)) {
		    $ch_info = $model->table('co_charge')->where('id = ' . $id)->select();
		    $this->assign('ch_info', $ch_info[0]);
		}
		$sql = 'SELECT charge_section FROM co_group WHERE id = (SELECT group_id FROM co_charge WHERE	        id = ' . $id . ')';
		$g = $model->query($sql);
		$charge_section1 = $model->table('co_group')->where('charge_section = 1 and status = 1')->select();
		$charge_section2 = $model->table('co_group')->where(array('charge_section' => 2,'status' => 1))->select();
	
		//print_r($charge_section1);
		$this->assign('g', $g[0]);
		$this->assign('z1', $charge_section1);
		$this->assign('zsecond', $charge_section2);
		
		//2014.10.27 jiwei
		//汉化推端需求
		//新增论坛部门处理
		$charge_section3 = $model->table('co_group')->where(array('charge_section' => 3,'status' => 1))->select();
		$this->assign('zthree', $charge_section3);
		//END
		
		
		$this->display();
	}
	function persondo() {
		$model = D('Channel_cooperation.AdCustomer_p');
		$User = M("admin_users");
		$charge_name = $_POST['charge_name'];
		$result=$model -> table('co_charge')->where(array('charge_name' => $charge_name,'status' => 1)) -> select();
		
		$admin_user = $User->where('admin_user_name='."'$charge_name'")->select();
		if($admin_user!=""){
		//echo $User->getLastsql();
		
		//print_r($admin_user);die;
		$id = $_POST['id'];
		if (!empty($id)) {
			$bumen = $_POST['name'];
			$xiaozu1 = $_POST['zdsw'];
			$xiaozu2 = $_POST['sw'];
			$charge_name = $_POST['charge_name'];
			if ($bumen == 1) {
				$date = array("group_id" => $xiaozu1);
			}
			if ($bumen == 2) {
				$date = array("group_id" => $xiaozu2);
			}
			
			//2014.10.27 jiwei
			//汉化推端需求
			//新增论坛部门处理
			$forum_group = $_POST['forum_group'];
			if($bumen == 3)
				$date = array("group_id" => $forum_group);
			//END
			
			$date['admin_id'] = $admin_user[0]['admin_user_id'];
			$date['charge_name'] = $charge_name;
			$date['update_tm'] = time();
			$log_result = $this -> logcheck(array('id' => $id),'co_charge',$date,$model);
			$re = $model->table('co_charge')->where("id=$id")->save($date);
			$this -> writelog("已编辑客户负责人id为{$id}".$log_result, 'co_charge', $id,__ACTION__ ,'','edit');
		} else {
			if($result != ""){
				echo "<script>alert('负责人已存在');history.go(-1);</script>";die;
			}
			$bumen = $_POST['name'];
			$xiaozu1 = $_POST['zdsw'];
			$xiaozu2 = $_POST['sw'];
			$charge_name = $_POST['charge_name'];
			if ($bumen == 1) {
				$date = array("group_id" => $xiaozu1);
			}
			if ($bumen == 2) {
				$date = array("group_id" => $xiaozu2);
			}
			
			//2014.10.27 jiwei
			//汉化推端需求
			//新增论坛部门处理
			$forum_group = $_POST['forum_group'];
			if($bumen == 3)
				$date = array("group_id" => $forum_group);
			//END			
			
			$date['admin_id'] = $admin_user[0]['admin_user_id'];
			$date['charge_name'] = $charge_name;
			$date['create_tm'] = time();
			$re = $model->table('co_charge')->add($date);
			if($re){
				$this -> writelog("已添加客户负责人id为{$re}", 'co_charge', $re,__ACTION__ ,'','add');
			}
			}
		}else{
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdCustomer_p/index');
			$this->error("负责人不存在");
		}
		if($re == false){
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdCustomer_p/index');
			$this->error("修改失败");
		}else{
			
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdCustomer_p/index');
			$this->success("操作成功");
		}
	}
	function dodo(){
				$optionmodel = M("admin_users");
                if($this->isAjax())
                {
                    $keyword = $_GET['query'];
           
		   			$chname =$optionmodel->query("select admin_user_name from sj_admin_users where admin_user_name like '%$keyword%'");
					
		   			//file_put_contents('11.txt', $optionmodel->getlastsql());
                    $data = array(
                            'query' => $keyword,
                            'suggestions' => array(),
                    );
                    foreach($chname as $v) {
                            $data['suggestions'][] = $v['admin_user_name'];
                    }
                    //file_put_contents('data.txt',serialize($data));
                    exit(json_encode($data));
                }
	}
	//删除负责人
	function persondel(){
		$model = D('Channel_cooperation.AdCustomer_p');
		$id = $_GET['chid'];
		$ret = $model->delete_xiaozudel($id);
		if ($ret) {
			$this->writelog("删除了id为{$id}的负责人", 'co_charge', $id,__ACTION__ ,'','del');
			$this->success("删除成功！");
		} else {
            $this->success("删除失败！");
		}
	}
	//转移客户查询
	function transfer() {
		$model = D('Channel_cooperation.AdCustomer_p');
		$id = $_GET['chid'];
		$name = $_GET['name'];
		$customer_name = $_GET['customer_name'];
		$where['charge_id'] = array('EQ',$id);
		$where['status'] = array('NEQ', 0);
		import("@.ORG.Page");
		$count = $model->table('co_client_list')->where($where)->count();
		$Page = new Page($count, 10);
		$show = $Page->show();
		$sql = "SELECT co.id,cl.id,cl.client_name,cl.charge_id,cl.create_tm,cl.status,cc.login_name FROM co_charge co INNER JOIN co_client_list cl on co.id = cl.charge_id inner join co_account cc on cc.client_id = cl.id where cl.`status` != 0 and co.`status` = 1 and cc.is_parent = 1 and co.id =$id limit $Page->firstRow,$Page->listRows";
		$res = $model->query($sql);
	
		$this->assign('page', $show);
		//print_r($id);
		//print_r($customer_name);
		//$res = $model->showtransfer($id);
		$rest = $model->showhead();//查询负责人
		//print_r($rest);
		$this->assign("chid",$id);
		$this->assign("name",$name);
		$this->assign("list",$res);
		$this->assign("headlist",$rest);//展示所有负责人名称
		$this->display();
	}
	//转移客户里搜索
	function sel_customer_name(){
		$model = D('Channel_cooperation.AdCustomer_p');
		$id = $_POST['chid'];
		$name = $_POST['name'];
		$customer_name = $_POST['customer_name'];
		$ret = $model->sel_showtransfer($id,$customer_name);
		$rest = $model->showhead();//查询负责人
		$this->assign("list",$ret);
		$this->assign("head",$res);
		$this->assign("chid",$id);
		$this->assign("name",$name);//转出负责人
		$this->assign("headlist",$rest);//展示所有负责人名称
		$this->assign("customer_name",$customer_name);//保持搜索条件不变
		$this->display("transfer");
	}
	//转移操作
	function transfers(){
		$models = D('Channel_cooperation.AdCustomer_p');
		$model = new Model();
		$check = $_POST['check'];
		$fuzeren = $_POST['fuzeren'];
		
		//print_r($result);
		$check = $_POST['check'];
		foreach($check as $key => $val){
			$my_client_id_go .= $val.',';
		}
		$my_client_id = substr($my_client_id_go,0,-1);
		$my_cid_where['_string'] = "client_id in ({$my_client_id})";
		$my_cid_result = $models -> table('co_client_channel') -> where($my_cid_where) -> select();
		foreach($my_cid_result as $key => $val){
			$my_cid_arr[] = $val['cid'];
		}
		$my_admin_result = $models -> table('co_charge') -> where(array('id' => $fuzeren)) -> select();
		
		//是否关闭了渠道过滤设置  target_type = 9 是渠道过滤设置 filter_type = 1是关闭渠道过滤设置
		$is_close = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $my_admin_result[0]['admin_id'],'target_type' => CHANNEL_SHOW_CONTROL,'filter_type' => 1))-> select();
		
		if(!$is_close)
		{
			$my_admin_cid = $model -> table('sj_admin_filter') -> where(array('source_type' => 1,'source_value' => $my_admin_result[0]['admin_id'],'target_type' => 2,'filter_type' => 2)) -> field('target_value') -> select();
			foreach($my_admin_cid as $key => $val){
				$the_cid[] = $val['target_value'];
			}
			if($the_cid){
				foreach($my_cid_arr as $key => $val){
					if(!in_array($val,$the_cid) && $val){
						$this -> error("您要转入的负责人没有您所转移的渠道权限，请先联系技术人员为其开通相应的渠道权限");
					}
				}
			}else{
				$this -> error("您要转入的负责人没有您所转移的渠道权限，请先联系技术人员为其开通相应的渠道权限");
			}
		}
		$my_charge_result = $models -> table('co_client_list') -> where(array('id' => $check[0])) -> select();
		$arr = "(". implode(",",$check).")";
		$result = $models->transfersdo($check,$fuzeren);
		if($result){
			$this -> writelog("已转移id为{$my_charge_result[0]['charge_id']}的负责人客户，客户id为{$arr},到id为{$fuzeren}的负责人名下", 'co_client_list', $my_charge_result[0]['charge_id'],__ACTION__ ,'','edit');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdCustomer_p/index');
			$this->success("转移成功！");
		} else {
            $this->success("转移失败！");
		}
	}
}