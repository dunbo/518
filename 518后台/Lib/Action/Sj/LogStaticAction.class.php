<?php
class LogStaticAction extends CommonAction {
	function log_static_list(){
		$category=M("statistic_log_category");
		$internal=M("statistic_log_internal");
		$log_user=M("statistic_log_user");
		$model=M();
		$user_log=array();
		//获取大类数据
		$catrgory_result=$category->where(array("status"=>1))->findAll();
		$this->assign("category",$catrgory_result);
		if($_GET){
			if(!empty($_GET['key'])){
				$category_selected=$_GET['zh_type'];
				$fromdate=$_GET['fromdate'];
				$todate=$_GET['todate'];
				$internal_result=$internal->where(array("status"=>1,"cid"=>$category_selected))->findAll();
			}else{
				$fromdate=$_GET['fromdate'];
				$todate=$_GET['todate'];
				/* $daydate=strtotime(date("Y-m-d",time()));
				if(strtotime($todate)>=$daydate){
					$todate=date("Y-m-d",$daydate-3600*24);
				} */
				$category_selected=$_GET['zh_type'];
				if(empty($fromdate)||empty($todate)){
					$this->error("请正确选择时间区域！！！");
				}
			}
		}else{
			$category_selected=$catrgory_result[0]['id'];
			$fromdate=date("Y-m-d",strtotime(date("Y-m-d",time()))-7*3600*24);
			$todate=date("Y-m-d",time()-3600*24);
		}
		//程序执行
		$start_tm=strtotime($fromdate." 00:00:00");
		$end_tm=strtotime($todate." 23:59:59");
		//获取子分类列表
		$internal_result=$internal->where(array("status"=>1,"cid"=>$category_selected))->findAll();
		//获取用户名
		$log_result=$log_user->where(array("status"=>1,"cid"=>$category_selected))->findAll();
		$where =" where timestamp >={$start_tm} and timestamp<={$end_tm} ";
		$where .=" and Cid={$category_selected}";
		$sql="select sum(count)as num,userID,Bid from sj_user_statistic_log".$where." group by userID,Bid";
		$result=$model->query($sql);
		foreach($result as $k =>$val){
			$user_log[$val['userID']][$val['Bid']]=$val['num'];	
		}
		foreach($log_result as $key=>$v){
			$list[$v['userid']]['name']=$v['name'];
			foreach($internal_result as $m=>$n){
				$list[$v['userid']][$n['id']]=$user_log[$v['userid']][$n['id']]?$user_log[$v['userid']][$n['id']]:0;
			}
		}
		$this->assign("fromdate",$fromdate);
		$this->assign("todate",$todate);
		$this->assign("internal_result",$internal_result);
		$this->assign("category_selected",$category_selected);
		$this->assign("list",$list);
		$this->display();
	}
	//添加用户名
	function add_user(){
		$user=M("admin_users");
		$log_user=M("statistic_log_user");
		if($_POST){
			$data['name']=$_POST['username'];
			$user_result=$user->where(array("admin_state"=>1,"admin_user_name"=>$data['name']))->find();
			if(empty($user_result)){
				$this->error("请正确填写用户，此用户不存在用户表");
			}else{
				$data['userid']=$user_result['admin_user_id'];
			}
			$data['cid']=$_POST['type'];
			$fromdate=$_POST['fromdate'];
			$todate=$_POST['todate'];
			$data['status']=1;
			$data['create_tm']=time();
			$data['update_tm']=time();
			if($id=$log_user->add($data)){
				$this->writelog('增加了用户名称为['.$data['name'].']分类为['.$data['cid'].']的用户信息', 'sj_statistic_log_user', $id,__ACTION__ ,"","add");
				$this->assign("jumpUrl","/index.php/".GROUP_NAME."/LogStatic/log_static_list/zh_type/{$data['cid']}/fromdate/{$fromdate}/todate/{$todate}");
				$this->success("添加用户成功！");
			}else{
				$this->error("添加用户失败");
			}
		}
		$zh_type=$_GET['zh_type'];
		$fromdate=$_GET['fromdate'];
		$todate=$_GET['todate'];
		$this->assign("zh_type",$zh_type);
		$this->assign("fromdate",$fromdate);
		$this->assign("todate",$todate);
		$this->display();
	}
	//显示详细
	function log_inner_list(){
		import("@.ORG.Page");
		$internal=M("statistic_log_internal");
		$log=M("admin_log");
		$log_user=M("admin_users");
		$model=M();
		if(!empty($_GET['key'])){
			$bid=$_GET['zh_type'];
		}else{
			$bid=$_GET['bid'];
		}
		$cid=$_GET['cid'];
		$userid=$_GET['userid'];
		$fromdate=$_GET['fromdate'];
		$todate=$_GET['todate'];
		$start_tm=strtotime($fromdate." 00:00:00");
		$end_tm=strtotime($todate." 23:59:59");
		//获取子分类列表
		$internal_result=$internal->where(array("status"=>1,"cid"=>$cid))->findAll();
		$log_key=$internal->where(array("status"=>1,"id"=>$bid))->getfield("log_key");
		$where['log_key']=$log_key;
		$where['admin_id']=$userid;
		$where['_string']="logtime >={$start_tm} and logtime <={$end_tm}";
		$count=$log->where($where)->count();
		
		$param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
		$list=$log->where($where)->order("logtime desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$count=count($list);
		$num=0;
		foreach($list as $k=>$val){
			$zh_where['status']=1;
			$zh_where['hide']=1;
			$zh_where['channel_id'] = array('eq',"");
			if(!empty($val['value'])){
				if(!is_numeric($val['value'])){
					$zh_where['package']=$val['value'];
				$soft_result=$model->table("sj_soft")->where($zh_where)->order("softid asc")->findAll();
				$list[$k]['softid']=$soft_result[0]['softid'];
				}else{
					$list[$k]['softid']=$val['value'];
				}
				if(!empty($list[$k]['softid'])){
					$num++;
				}
			}
			$list[$k]['username']=$log_user->where(array("admin_state"=>1,"admin_user_id"=>$val['admin_id']))->getfield("admin_user_name");
			$list[$k]['logtime']=date("Y-m-d",$val['logtime']);
		}
		if ($_GET['p'])
            $this->assign('p', $_GET['p']);
		else
        $this->assign('p', '1');
		$show = $Page->show();
        $this->assign("page", $show);
		$this->assign("category_selected",$bid);
		$this->assign("cid",$cid);
		$this->assign("userid",$userid);
		$this->assign("fromdate",$fromdate);
		$this->assign("todate",$todate);
		$this->assign("num",$num);
		$this->assign("zong",$count);
		$this->assign("internal_result",$internal_result);
		$this->assign("list",$list);
		$this->display();
	}
	function del_user(){
		$user=M("admin_users");
		$log_user=M("statistic_log_user");
		$cid=$_GET['cid'];
		$userid=$_GET['userid'];
		$fromdate=$_GET['fromdate'];
		$todate=$_GET['todate'];
		$affect = $log_user -> query("update __TABLE__ set status = 0 where userid = ".$userid." and cid=".$cid);
		$this->writelog("删除了的userid为{$userid},cid为{$cid}用户信息", 'sj_statistic_log_user', "userid:{$userid}",__ACTION__ ,"","del");
		$this->assign("jumpUrl","/index.php/".GROUP_NAME."/LogStatic/log_static_list/zh_type/{$cid}/fromdate/{$fromdate}/todate/{$todate}");
		$this->success('删除成功');
	}
}
?>
