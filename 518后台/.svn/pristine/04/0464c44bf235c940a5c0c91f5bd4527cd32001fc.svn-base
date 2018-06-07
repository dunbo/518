<?php
class TimeConfigAction extends CommonAction {
	//显示最新页面配置时间列表
	function time_list(){
		$time_list=M("new_time");
		import("@.ORG.Page");
		$count = $time_list->where(array("status"=>1))->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
		$result=$time_list->where(array("status"=>1))->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($result as $k=>$v){
			$result[$k]['spare_start_tm']=date("Y.m.d",$v['spare_start_tm']);
			$result[$k]['spare_end_tm']=date("Y.m.d",$v['spare_end_tm']);
			$result[$k]['from_start_tm']=date("Y.m.d",$v['from_start_tm']);
			$result[$k]['from_end_tm']=date("Y.m.d",$v['from_end_tm']);
		}
		$this->assign("list",$result);
		$this->assign("page", $Page->show());
		$this->display();
	}
	//添加最新页面配置时间
	function add_time(){
		$time_list=M("new_time");
		if($_POST){
			if(empty($_POST['spare_start_date'])||empty($_POST['spare_end_date'])||empty($_POST['from_start_date'])||empty($_POST['from_end_date'])){
				$this->error("请不要提交空值，请重新填写！！！");
				exit;
			}
			$spare_start_time=strtotime($_POST['spare_start_date']." 00:00:00");
			$spare_end_time=strtotime($_POST['spare_end_date']." 23:59:59");
			$from_start_time=strtotime($_POST['from_start_date']." 00:00:00");
			$from_end_time=strtotime($_POST['from_end_date']." 23:59:59");
			if($spare_start_time > $spare_end_time){
				$this->error("备用规则时间段:起始时间大于结束时间！！！");
				exit;
			}
			if($from_start_time > $from_end_time){
				$this->error("数据来源时间段:起始时间大于结束时间！！！");
				exit;
			}
			if(($spare_start_time - $from_start_time) > (30*24*3600)){
				$this->error("数据来源时间段只可以选备用规则时间段起始时间30天以内的数据");
				exit;
			}
			if(($spare_start_time - $from_end_time) < 0){
				$this->error("数据来源时间段只可以选备用规则时间段内");
				exit;
			}
			if(($from_end_time - $from_start_time) > (14*24*3600)){
				$this->error("数据来源时间段最大的周期只可以选择2周期间的数据");
				exit;
			}
			$where=array();
			$where['status']=1;
			$where['spare_start_tm']=array("elt",$spare_end_time);
			$where['spare_end_tm']=array("egt",$spare_start_time);
			$count=$time_list->where($where)->count();
			if($count){
				$this->error("数据来源时间段内已经存在符合条件的数据，请重新选择时间");
				exit;
			}
			$data['spare_start_tm']=$spare_start_time;
			$data['spare_end_tm']=$spare_end_time;
			$data['from_start_tm']=$from_start_time;
			$data['from_end_tm']=$from_end_time;
			$data['status']=1;
			$data['add_tm']=time();
			$data['update_tm']=time();
			$id=$time_list->add($data);
			if($id){
				$this->writelog('图片广告位配置:增加了ID为['.$id.']的最新页面规则配置', 'sj_new_time',$id,__ACTION__ ,'','add');
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/TimeConfig/time_list');
				$this->success("最新页面规则配置添加成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/TimeConfig/time_list');
				$this->error("最新页面规则配置添加失败！");
			}
		}
		$this->display();
	}
	function edit_time(){
		$time_list=M("new_time");
		$id=$_REQUEST['id'];
		$where['id']=$id;
		$where['status']=1;
		$result_one=$time_list->where($where)->find();
		$spare_start_date=date("Y-m-d",$result_one['spare_start_tm']);
		$spare_end_date=date("Y-m-d",$result_one['spare_end_tm']);
		$from_start_date=date("Y-m-d",$result_one['from_start_tm']);
		$from_end_date=date("Y-m-d",$result_one['from_end_tm']);
		if($_POST){
			if(empty($_POST['spare_start_date'])||empty($_POST['spare_end_date'])||empty($_POST['from_start_date'])||empty($_POST['from_end_date'])){
				$this->error("请不要提交空值，请重新填写！！！");
				exit;
			}
			$spare_start_time=strtotime($_POST['spare_start_date']." 00:00:00");
			$spare_end_time=strtotime($_POST['spare_end_date']." 23:59:59");
			$from_start_time=strtotime($_POST['from_start_date']." 00:00:00");
			$from_end_time=strtotime($_POST['from_end_date']." 23:59:59");
			if($spare_start_time > $spare_end_time){
				$this->error("备用规则时间段:起始时间大于结束时间！！！");
				exit;
			}
			if($from_start_time > $from_end_time){
				$this->error("数据来源时间段:起始时间大于结束时间！！！");
				exit;
			}
			if(($spare_start_time - $from_start_time) > (30*24*3600)){
				$this->error("数据来源时间段只可以选备用规则时间段起始时间30天以内的数据");
				exit;
			}
			if(($spare_start_time - $from_end_time) < 0){
				$this->error("数据来源时间段只可以选备用规则时间段内");
				exit;
			}
			if(($from_end_time - $from_start_time) > (14*24*3600)){
				$this->error("数据来源时间段最大的周期只可以选择2周期间的数据");
				exit;
			}
			$where=array();
			$where['status']=1;
			$where['spare_start_tm']=array("elt",$spare_end_time);
			$where['spare_end_tm']=array("egt",$spare_start_time);
			$where['id']=array("neq",$id);
			$count=$time_list->where($where)->count();
			//echo $time_list->getlastsql();exit;
			if($count){
				$this->error("数据来源时间段内已经存在符合条件的数据，请重新选择时间");
				exit;
			}
			$data['spare_start_tm']=$spare_start_time;
			$data['spare_end_tm']=$spare_end_time;
			$data['from_start_tm']=$from_start_time;
			$data['from_end_tm']=$from_end_time;
			$data['status']=1;
			$data['update_tm']=time();
			$log = $this -> logcheck(array('id' =>$id),'sj_new_time',$data,$time_list);
			$res=$time_list->where(array("id"=>$id,"status"=>1))->save($data);
			if($res){
				$this->writelog("图片广告位配置:编辑了ID为['.$id.']的最新页面规则配置，{$log}", 'sj_new_time',$id,__ACTION__ ,'','edit');
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/TimeConfig/time_list');
				$this->success("最新页面规则配置编辑成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/TimeConfig/time_list');
				$this->error("最新页面规则配置编辑失败！");
			}
		}
		$this->assign("spare_start_date",$spare_start_date);
		$this->assign("spare_end_date",$spare_end_date);
		$this->assign("from_start_date",$from_start_date);
		$this->assign("from_end_date",$from_end_date);
		$this->assign("id",$id);
		$this->display();
	}
	//删除最新时间配置
	function del_time(){
		$time_list=M("new_time");
		$id=$_GET['id'];
		$affect = $time_list -> query("update __TABLE__ set status = 0 where id = ".$id);
		$this->writelog('图片广告位配置:删除了ID为['.$id.']的最新页面规则配置', 'sj_new_time',$id,__ACTION__ ,'','del');
		$this->assign("jumpUrl","/index.php/".GROUP_NAME."/TimeConfig/time_list");
		$this->success('删除成功');
	}
}
?>
