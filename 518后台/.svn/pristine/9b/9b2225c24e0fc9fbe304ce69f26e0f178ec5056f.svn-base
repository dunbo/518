<?php

class FrendlyPictureAction extends CommonAction{
		
	//友情链接管理
	function frendly_link_list(){
		$model = M('frendly_link');
		if(!empty($_GET['state'])){
			$where['state']= $_GET['state'];
			$where['status'] = 1;
			if($_GET['state'] == 1){
				$order = "rank ASC";
			}else{
				$order = "update_tm";
			}
			$state = $_GET['state'];
		}else{
			$where['state'] = '1';
			$where['status'] = 1;
			$state = 1;
			$order = "rank ASC";
		}
		$link_list = $model -> where($where) -> select();
		$count = count($link_list);
		import("@.ORG.Page");
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $link_list = $model ->where($where)->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		for($i=1;$i<=$count;$i++){
			$rank_all[] = $i;
		}
		$this -> assign('link_list',$link_list);
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
		$this -> assign("state",$state);
		$this -> assign("rank_all",$rank_all);
        $this->assign("page", $show);
		$this -> display();
	}
	//友情链接编辑显示
	function frendly_link_edit(){
		$model = M('frendly_link');
		$id = $_GET['id'];
		$link_result = $model -> where(array('id' => $id)) -> select();
		$this -> assign('link_result',$link_result);
		$this -> display();
	}
	//友情链接编辑提交
	function frendly_link_edit_do(){
		$model = M('frendly_link');	
		if($_POST['id']){
			$data['link_name'] = trim($_POST['link_name']);
			$data['link_url'] = trim($_POST['link_url']);
			if(!empty($_POST['qq'])){
				$data['qq'] = trim($_POST['qq']);
			}else{
				$data['qq'] = "";
			}
			$id= $_POST['id'];
			if(!empty($_POST['type'])){
				$data['type'] = trim($_POST['type']);
			}else{
				$data['type'] = 0;
			}
			if(!empty($_POST['email'])){
				if($this -> valid_email($_POST['email'])){
					$data['email'] = $_POST['email'];
				}else{
					$this -> error("对不起，邮件格式错误!");
				}
			}else{
				$data['email'] ='';
			}
			$data['update_tm'] = time();
		if(empty($_POST['link_name']) || empty($_POST['link_url'])){
			$this -> error("名称和网址不能为空!");
		}
		if(!empty($_POST['link_name'])){
			$linkname = $model -> where(array('status' => 1,'link_name'=>$_POST['link_name'],'id'=>array('neq',$id)))->select();
			if($linkname){
				$this -> error("该名称已经存在!");
			}
		}
		if(!empty($_POST['link_url'])){
			$linkname = $model -> where(array('status' => 1,'link_url'=>$_POST['link_url'],'id'=>array('neq',$id)))->select();
			if($linkname){
				$this -> error("该网址已经存在!");
			}
		}
		$log = $this -> logcheck(array('id' =>$id),'sj_frendly_link',$data,$model);
			$affect = $model -> where(array('id' => $id)) -> save($data);
			if($affect){
				$this -> writelog("首页图片管理-情链接管理已修改id为{$_POST['id']}的友情链接,链接名称为".$_POST['link_name'].",内容为".$log,'sj_frendly_link',"{$id}",__ACTION__ ,'','edit');
				$this -> assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list');
				$this -> success("编辑成功");
			}else{
				$this -> assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list');
				$this -> error("对不起，编辑失败");
			}
		}
	
		if($_GET['mid']){
			$state = $_GET['state'];
			$mid = $_GET['mid'];
			$data['state'] = $state;
			$data['update_tm'] = time();
			if($_GET['state'] == 1){
				$rank_go = $model -> where(array('state' => 1,'status' => 1)) -> count();
				$data['rank'] = $rank_go + 1;
				$where['id'] = $mid;
				$where['status'] = 1;
				$link_affect = $model  -> where($where) -> save($data);
			}
			if($_GET['state'] == 3){
				$old_state = $model -> where(array('id' => $mid,'status' => 1)) -> select();
				if($old_state[0]['state'] == 1){
					$need_where['_string'] = " status = 1 and state = 1 and rank >= ".$old_state[0]['rank']."";
					$need_id = $model -> where($need_where) -> select();
					foreach($need_id as $val){
						$need_data['rank'] = $val['rank'] - 1;
						$need_affect = $model -> where(array('id' => $val['id'])) -> save($need_data);
					}
				}
				$link_affect = $model  -> where(array('id' => $mid)) -> save($data);
			}
			if($link_affect){
				$this -> writelog("首页图片管理-友情链接管理已修改id为{$_GET['mid']}的友情链接的显示状态为".$state."",'sj_frendly_link',"{$mid}",__ACTION__ ,'','edit');
				$this -> assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list');
				$this -> success("操作成功");
			}else{
				$this -> assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list');
				$this -> error("操作失败");
			}
		}
		
		if($_GET['sid']){
			$sid = $_GET['sid'];
			$data['status'] = 0;
			$data['update_tm'] = time();
			$old_state = $model -> where(array('id' => $sid,'status' => 1)) -> select();
			$need_where['_string'] = " status = 1 and state = 1 and rank >= ".$old_state[0]['rank']."";
			$need_id = $model -> where($need_where) -> select();
			foreach($need_id as $key => $val){
				$need_data['rank'] = $val['rank'] - 1;
				$need_affect = $model -> where(array('id' => $val['id'])) -> save($need_data);
			}
			
			$link_affect = $model  -> where(array('id' => $sid)) -> save($data);
			
			if($link_affect){
				$this -> writelog("首页图片管理-友情链接管理已删除id为".$sid."的友情链接",'sj_frendly_link',"{$sid}",__ACTION__ ,'','del');
				$this -> assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list');
				$this -> success("删除成功");
			}else{
				$this -> assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list');
				$this -> error("删除失败");
			}
		}
	}
	
	function frendly_link_rank(){
		$model = M('frendly_link');
		$new_rank = $_GET['rank'];
		$rid = $_GET['rid'];
		$m_data['rank'] = $new_rank;
		$m_data['update_tm'] = time();
		$old_rank = $model -> where(array('id' => $rid)) -> field('rank') -> select();
		if($new_rank > $old_rank[0]['rank']){
			$where['_string'] = "rank > ".$old_rank[0]['rank']." and rank <= ".$new_rank." and status = 1";
			$rank_all = $model -> where($where) -> field('rank,id') -> select();
			foreach($rank_all as $key => $val){
				$data['rank'] = $val['rank'] - 1;
				$data['update_tm'] = time();
				$all_result = $model -> where(array('id' => $val['id'])) -> save($data);
			}
		}elseif($new_rank < $old_rank[0]['rank']){
			$where['_string'] = "rank < ".$old_rank[0]['rank']." and rank >= ".$new_rank." and status = 1";
			$rank_all = $model -> where($where) -> field('rank,id') -> select();
			foreach($rank_all as $key => $val){
				$data['rank'] = $val['rank'] + 1;
				$data['update_tm'] = time();
				$all_result = $model -> where(array('id' => $val['id'])) -> save($data);
			}
		}
		
		if($all_result){
			$result = $model -> where(array('id' => $rid)) -> save($m_data);
		}

		if($result){
			$this -> writelog("首页图片管理-友情链接管理已修改id为".$rid."的友情链接位置为".$new_rank,'sj_frendly_link',"{$rid}",__ACTION__ ,'','edit');
			$this->assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list/state/1');
			$this -> success("修改排序成功");
		}else{
			$this -> assign('jumpUrl', '/index.php/Webmarket/FrendlyPicture/frendly_link_list/state/1');
			$this -> error("修改排序出错");
		}
	}
	
	function refuse(){
		$model = M('frendly_link');
		if($_GET['mid']){
			$mid = substr($_GET['mid'],1);
			$id_arr = explode(',',$mid);
			foreach($id_arr as $val){
				$old_state = $model -> where(array('id' => $val,'status' => 1)) -> select();
				if($old_state[0]['state'] == 1){
					$need_where['_string'] = " status = 1 and state = 1 and rank > ".$old_state[0]['rank']."";
					$need_id = $model -> where($need_where) -> select();
					$data['state'] = 3;
					$data['update_tm'] = time();
					if(!empty($need_id)){
						foreach($need_id as $v){
							$need_data['rank'] = $v['rank'] - 1;
							$need_affect = $model -> where(array('id' => $v['id'])) -> save($need_data);
							$this -> writelog("首页图片管理-友情链接管理修改id为".$v['id']."的友情链接位置为".$need_data['rank'],'sj_frendly_link',"{$v['id']}",__ACTION__ ,'','edit');
							if($need_affect){
								$link_affects = $model  -> where(array('id' => $val)) -> save($data);
								$this -> writelog("修改id为".$val."的state字段为".'3','sj_frendly_link',"{$val}",__ACTION__ ,'','edit');
							}
							$link_affect[] = $link_affects;
						}
					} else{
						$link_affect[] = $model  -> where(array('id' => $val)) -> save($data);
						$this -> writelog("首页图片管理-友情链接管理修改id为".$val."的state字段为".'3','sj_frendly_link',"{$val}",__ACTION__ ,'','edit');
					}
					
				}elseif($old_state[0]['state'] == 2){
					$need_where['_string'] = "status = 1 and state = 2 and id=".$val."";
					$need_id = $model -> where($need_where) -> select();
					$data['state'] = 3;
					$data['update_tm'] = time();
					foreach($need_id as $val){
						$link_affect[] = $model -> where(array('id' => $val['id'])) -> save($data);
						$this -> writelog("首页图片管理-友情链接管理修改id为".$val['id']."的state字段为".'3','sj_frendly_link',"{$val['id']}",__ACTION__ ,'','edit');
					}
				}
			}

			if($link_affect){
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}
	}
	
	function delete_go(){
		$model = M('frendly_link');
		if($_GET['mid']){
			$mid = substr($_GET['mid'],1);
			$id_arr = explode(',',$mid);
			foreach($id_arr as $val){
				$old_state = $model -> where(array('id' => $val,'status' => 1)) -> select();
				if($old_state[0]['state'] == 1){
					$need_where['_string'] = " status = 1 and state = 1 and rank > ".$old_state[0]['rank']."";
					$need_id = $model -> where($need_where) -> select();
					$data['status'] = 0;
					$data['update_tm'] = time();
					if(!empty($need_id)){
						foreach($need_id as $k => $v){
							$need_data['rank'] = $v['rank'] - 1;
							$need_affect = $model -> where(array('id' => $v['id'])) -> save($need_data);
							$this -> writelog("首页图片管理-友情链接管理修改id为".$v['id']."的友情链接位置为".$need_data['rank'],'sj_frendly_link',"{$v['id']}",__ACTION__ ,'','edit');

							if($need_affect){
								$link_affect[] = $model  -> where(array('id' => $val)) -> save($data);
								$this -> writelog("修改id为".$val."的status字段为".'0','sj_frendly_link',"{$val}",__ACTION__ ,'','edit');
							}
						}
					}else{
						$link_affect[] = $model  -> where(array('id' => $val)) -> save($data);
						$this -> writelog("首页图片管理-友情链接管理修改id为".$val."的status字段为".'0','sj_frendly_link',"{$val}",__ACTION__ ,'','edit');
					}
				}elseif($old_state[0]['state'] == 3){
					$need_where['_string'] = "status = 1 and state = 3 and id=".$val."";
					$need_id = $model -> where($need_where) -> select();
					$data['status'] = 0;
					$data['update_tm'] = time();
					foreach($need_id as $val){
						$link_affect[] = $model -> where(array('id' => $val['id'])) -> save($data);
						$this -> writelog("首页图片管理-友情链接管理修改id为".$val['id']."的status字段为".'0','sj_frendly_link',"{$val['id']}",__ACTION__ ,'','edit');
					}
				}
			}
			if($link_affect){
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}
	}
	
	function transit_go(){
		$model = M('frendly_link');
		if($_GET['mid']){
			$mid = substr($_GET['mid'],1);
			$id_arr = explode(',',$mid);
			foreach($id_arr as $val){
				$count = $model -> where(array('state' => 1,'status' => 1)) -> count();
				$date['rank'] = $count + 1;
				$date['state'] = 1;
				$date['update_tm'] = time();
				$link_affect = $model -> where(array('id' => $val)) -> save($date);
				$this -> writelog("首页图片管理-友情链接管理修改id为".$val."的status字段为".'1'."rank字段为".$date['rank'],'sj_frendly_link',"{$val}",__ACTION__ ,'','edit');
			}
			if($link_affect){
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}
	}
	
	//判断电子邮箱格式是否正确
	function valid_email($email) {
		if(!preg_match('/^[a-zA-Z0-9_-]+([a-zA-Z0-9_-]|\.)+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/si', $email)){
			return false;
		}
		return true;
	}
	//安智市场__友情链接__添加显示
	function frendly_link_add(){
		$this->display();
	}
	
	function frendly_link_add_do(){
		$model= M('frendly_link');
		$data['link_name'] = $_POST['link_name'];
		$data['link_url'] = $_POST['link_url'];
		if(!empty($_POST['qq'])){
			$data['qq'] = $_POST['qq'];
		}
		if(!empty($_POST['type'])){
			$data['type'] = $_POST['type'];
		}else{
			$data['type'] = 0;
		}
		$data['state'] = 1;
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$rank_go = $model -> where(array('state' => 1,'status' => 1)) -> count();
		$data['rank'] = $rank_go + 1;
		if(empty($_POST['link_name']) || empty($_POST['link_url'])){
			$this -> error("名称和网址不能为空!");
		}
		if(!empty($_POST['link_name'])){
			$linkname = $model -> where(array('status' => 1,'link_name'=>$_POST['link_name']))->select();
			if($linkname){
				$this -> error("该名称已经存在!");
			}
		}
		if(!empty($_POST['link_url'])){
			$linkname = $model -> where(array('status' => 1,'link_url'=>$_POST['link_url']))->select();
			if($linkname){
				$this -> error("该网址已经存在!");
			}
		}
		if(!empty($_POST['email'])){
			if($this -> valid_email($_POST['email'])){
				$data['email'] = $_POST['email'];
			}else{
				$this -> error("对不起，邮件格式错误!");
			}
		}
		$reset = $model->add($data);
		if($reset){
			$this -> writelog("首页图片管理-友情链接管理添加链接名称为".$_POST['link_name'].",链接地址为".$_POST['link_url'].",qq为".$_POST['qq'].",email为".$_POST['email']."",'sj_frendly_link',"{$reset}",__ACTION__ ,'','add');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
}




?>