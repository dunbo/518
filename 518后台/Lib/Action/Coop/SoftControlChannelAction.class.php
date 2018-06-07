<?php
header('content-type:text/html;charset=utf-8');
class SoftControlChannelAction extends CommonAction {
	/*
	 *  原道widget控制定制页面
	 * 	by 张辉
	*/
	public function ShowControl()
	{	
		$channel=M("soft_channel_control");
		$protocol=M("soft_channel_protocol");
		$channel_list=$channel->select();
		$new_channel_list=array();
		foreach($channel_list as $key=>$val){
			$channel_list[$key]['protocol']=$protocol->where(array("channel_type"=>$val['channel_type']))->getfield("protocol");	
		}
		foreach($channel_list as $k=>$v){
			$new_channel_list[$v['id']]=$v;
		}
		if($_POST){
			$id=$_POST['id'];
			if(empty($id)){
				$this->error("提交出问题，请重新提交！！！！");
			}
			if(empty($_POST['channel_type'])||empty($_POST['channel_name'])||empty($_POST['num'])||empty($_POST['protocol'])){
				//$this->error("请把提交信息填写完整！！！！");
				echo "<script>alert('请把提交信息填写完整！！！！');location.href='/index.php/Coop/SoftControlChannel/ShowControl';</script>";
				exit;
			}
			$data=array();
			if($_POST['channel_type']){
				$data['channel_type']=$_POST['channel_type'];
			}
			if($_POST['channel_name']){
				$data['channel_name']=$_POST['channel_name'];
			}
			if($_POST['num']){
				$data['num']=$_POST['num'];
			}
			import("@.ORG.Input");
			//判定protocol为json字符串
			$_POST['protocol']=Input::getVar(trim($_POST['protocol']));
			$arra=json_decode($_POST['protocol'],true);
			if(!is_array($arra)){
				echo "<script>alert('所填返回协议不符合规则！！！！');location.href='/index.php/Coop/SoftControlChannel/ShowControl';</script>";
				exit;
				//$this->error("所填返回协议不符合规则！！！！");
			}
			$count=$channel->where(array("id"=>$id))->count();
			if(empty($count)){
				$data['id']=$id;
				$data['add_tm']=time();
				$data['last_refresh']=time();
				$result=$channel->add($data);
				if($result){
					$data_protocol['channel_type']=$_POST['channel_type'];
					$data_protocol['protocol']=$_POST['protocol'];
					$result_protocol=$this->update_protocol($data_protocol);
					if($result_protocol){
						$this->assign('jumpUrl', "/index.php/Coop/SoftControlChannel/ShowControl");
						$this -> writelog('插入了id为'.$id."的原道频道", 'sj_soft_channel_control',$result,__ACTION__ ,"","add");
						$this->success('插入成功');
					}else{
						$this->assign('jumpUrl', "/index.php/Coop/SoftControlChannel/ShowControl");
						$this->error('更新返回协议失败，请重新编辑');
					}
				}else{
					$this->error('插入失败');
				}
			}else{
				$data['last_refresh']=time();
				$log_result = $this->logcheck(array("id"=>$id),'sj_soft_channel_control',$data,$channel);
				$result=$channel->where(array("id"=>$id))->save($data);
				if($result){
					$data_protocol['channel_type']=$_POST['channel_type'];
					$data_protocol['protocol']=$_POST['protocol'];
					$log_re = $this->logcheck(array("channel_type"=>$_POST['channel_type']),'sj_soft_channel_protocol',$data_protocol,$protocol);
					// var_dump($log_re);die;
					$result_protocol=$this->update_protocol($data_protocol);
					if($result_protocol){
						$this->assign('jumpUrl', "/index.php/Coop/SoftControlChannel/ShowControl");
						$this -> writelog('编辑了id为'.$id."的原道频道.{$log_result}.{$log_re}", 'sj_soft_channel_control',$id,__ACTION__ ,"","edit");
						$this->success('编辑成功');
					}else{
						$this->assign('jumpUrl', "/index.php/Coop/SoftControlChannel/ShowControl");
						$this->error('更新返回协议失败，请重新编辑');
					}
				}else{
					$this->error('插入失败');
				}
			}
		}
		//获取频道类型
		$category_list = $this->getCategoryTypes();
		$this->assign('category_list', $category_list);
		$this->assign('list', $new_channel_list);
		$html = $this->fetch();
		header("Cache-control: no-store");
		header("pragma:no-cache");
		exit($html);
	}
	public function get_protocol(){
		$protocol=M("soft_channel_protocol");
		$pid=$_POST['pid'];
		$cid=$_POST['cid'];
		if(empty($pid)||empty($cid)){
			$this->error("请求失败，请重新选择频道类型");
		}
		$back_array=array();
		$back_array['pid']=$pid;
		$protocol_info=$protocol->where(array("channel_type"=>$cid))->getfield("protocol");
		if(empty($protocol_info)){
			$back_array['protocol']="";
		}else{
			import("@.ORG.Input");
			$back_array['protocol']=Input::getVar($protocol_info);
		}
		echo json_encode($back_array);
	}
	//返回协议发生变动时对数据库进行操作
	public function update_protocol($data){
		$protocol=M("soft_channel_protocol");
		$count=$protocol->where(array("channel_type"=>$data['channel_type']))->count();
		if(empty($count)){
			$data['add_tm']=time();
			$data['update_tm']=time();
			$result=$protocol->add($data);
			if($result){
				return true;
			}else{
				return false;
			}
		}else{
			$data['update_tm']=time();
			$result=$protocol->where(array("channel_type"=>$data['channel_type']))->save($data);
			if($result){
				return true;
			}else{
				return false;
			}
		}
	}
	function getCategoryTypes()
	{
		$map = array(
			'home_features' => '首页',
			'top_new' => '最新',
			'top_hot' => '最热',
			'top_1d' => '日排行',
			'top_7d' => '周排行',
			'top_30d' => '月排行',
		);
		
		$category = D('Sj.Category');
		$category_list = $category->getCategoryArray();
		foreach ($category_list as $v) {
			$map['top_'. $v['category_id']. '_new'] = $v['name']. '-最新';
			$map['top_'. $v['category_id']. '_hot'] = $v['name']. '-最热';
		}
		return $map;
	}
	
}