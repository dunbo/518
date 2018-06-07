<?php

class IndexSpecialAction extends CommonAction{
	//首页专题列表
	function special_list(){
		$model = M('special_list');
		$where['status'] = 1;
		$order = "show_place";
		$count = $model -> where($where) -> count();
		import("@.ORG.Page");
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $special_list = $model ->where($where)->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($special_list as $key => $val){
			//$special_show = $model -> table('sj_feature') -> where(array('status' => 1,'pid' => 1,'feature_id' => $val['special_show'])) -> select();
			$special_show = $model -> table('sj_feature') -> where(array('status' => 1,'feature_id' => $val['special_show'])) -> select();
			$val['special_show_name'] = $special_show[0]['name'];
			$special_list[$key] = $val;
		}
		
		$this->assign("special_list",$special_list);
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
		$this->display();
	}


	//添加专题显示
	function special_add(){
		$model = new Model();
		$special_show = $model -> table('pu_config') -> where(array('config_type' => 'SPECIAL_SHOW_PLACE','status' => 1)) -> select();
		//$special_list = $model -> table('sj_feature') -> where(array('status' => 1,'pid' => 1)) -> select();
                $where['status'] = 1;
                $where['pid'] = array('like',"%,1,%");
		$special_list = $model -> table('sj_feature') -> where($where) -> select();
		$special_go = explode(',',$special_show[0]['configcontent']);
		$this->assign('special_list',$special_list);
		$this->assign('special_show',$special_go);
		$this->display();
	}
	
	//添加专题提交
	function special_add_do(){
		$model = M('special_list');
		if(!empty($_GET['special_name'])){
			$data['special_name'] = trim($_GET['special_name']);
		}
		if(!empty($_GET['special_show'])){
			$data['special_show'] = trim($_GET['special_show']);
		}
		if(!empty($_GET['soft_num'])){
			$data['soft_num'] = trim($_GET['soft_num']);
		}
		if(!empty($_GET['show_place'])  && $_GET['show_place'] != '请选择'){
			$data['show_place'] = trim($_GET['show_place']);
		}else{
			$this -> error("对不起，专题位置不能为空");
		}
		if(!empty($_GET['special_place'])){
			$data['special_place'] = trim($_GET['special_place']);
		}else{
			$this -> error("对不起，专题位置变量不能为空");
		}
		$have_been = $model -> where(array('special_place' => $_GET['special_place'],'status' => 1)) -> select();
		if($have_been){
			$this -> error("对不起，此变量位置已存在专题");
		}
		$data['create_tm'] = time();
		$data['status'] = 1;
		if(!empty($_GET['special_name'])){
			$affect = $model -> add($data);
			if($affect){
				$this -> writelog("已添加id为{$affect}的首页专题",'sj_special_list',$affect,__ACTION__ ,'','add');
				$this -> success("添加成功");
			}else{
				$this -> error("添加失败");
			}
		}else{
			$this -> error("专题名称不能为空");
		}

	}

	//首页专题编辑显示
	function special_edit(){
		$model = new Model();
		$id = $_GET['id'];
		$special_show = $model -> table('pu_config') -> where(array('config_type' => 'SPECIAL_SHOW_PLACE','status' => 1)) -> select();
		//$special_list = $model -> table('sj_feature') -> where(array('status' => 1,'pid' => 1)) -> select();

                $where['status'] = 1;
                $where['pid'] = array('like',"%,1,%");
		$special_list = $model -> table('sj_feature') -> where($where) -> select();
		$special_go = explode(',',$special_show[0]['configcontent']);
		$special_edit = $model -> table('sj_special_list') -> where(array('id'=>$id,'status' => 1)) -> select();
		$this->assign('special_edit',$special_edit[0]);
		$this->assign('special_list',$special_list);
		$this->assign('special_show',$special_go);
		$this->display();
	}

	//首页专题编辑提交
	function special_edit_do(){
		$model = M('special_list');
		if(!empty($_GET['special_name'])){
			$data['special_name'] = trim($_GET['special_name']);
		}
		if(!empty($_GET['special_show'])){
			$data['special_show'] = trim($_GET['special_show']);
		}
		if(!empty($_GET['soft_num'])){
			$data['soft_num'] = trim($_GET['soft_num']);
		}
		if(!empty($_GET['show_place']) && $_GET['show_place'] != '请选择'){
			$data['show_place'] = trim($_GET['show_place']);
		}else{
			$this -> error("对不起，专题位置不能为空");
		}
		$have_been = $model -> where(array('special_place' => $_GET['special_place'],'status' => 1)) -> select();
		if($have_been){
			$this -> error("对不起，此位置已存在专题");
		}
		if(!empty($_GET['id'])){
			$log_result = $this->logcheck(array('id'=>$_GET['id']),'sj_special_list',$data,$model);
			$affect = $model ->where(array('id' => $_GET['id'])) -> save($data);
			if($affect){
				$this -> writelog("已编辑id为{$_GET['id']}的首页专题管理列表".$log_result,'sj_special_list',"{$_GET['id']}",__ACTION__ ,'','edit');
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}else{
			$this -> error("编辑失败");
		}
	}

	//删除首页专题
	function delete_special(){
		$model = M('special_list');
		$id = $_GET['id'];
		if(!empty($id)){
			$data['status'] = 0;
			$affect = $model -> where(array('id' => $id)) -> save($data);
		}
		if($affect){
			$this -> writelog("已删除id为{$_GET['id']}的首页专题管理列表",'sj_special_list',"{$id}",__ACTION__ ,'','del');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
}



?>
