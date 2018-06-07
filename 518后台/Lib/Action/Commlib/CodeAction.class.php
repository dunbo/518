<?php

class CodeAction extends CommonAction {

    function index(){
		$model = D('Commlib.Code');
		$this->check_where($where, 'model_name', 'isset', 'like'); 
		$this->check_range_where($where, 'startDate', 'endDate','add_time', true);
		//分页
		import("@.ORG.Page2");
		$count = $model->table('modelmanagement')->where($where)->count();	
        $Page=new Page($count,10);
		$list = $model->table('modelmanagement')-> where($where)->limit($Page->firstRow.','.$Page->listRows)-> select();
		$this -> assign("list",$list);
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
        $show =$Page->show();
        $this->assign ("page", $show );
		$this->assign('total', $count);	
		$dir_name_list = $model->get_module_dir();
		$this->assign("dir_name_list" , $dir_name_list);	
		$model->create_file();	
		$this->display('form_designer');
    }
	//添加模块
    function add_model(){
		$connect = D('Commlib.Code');
		if($_POST){
			$ret = $connect -> add_model();
			if($ret['code']){
				$this -> assign('jumpUrl','/index.php/Commlib/Code/index');
				$this -> success("添加成功");
			}else{
				$this->error($ret['msg']);
			}
		}else{
			$dir_name_list = $connect->get_module_dir();
			$this->assign("dir_name_list" , $dir_name_list);				
			$this->display();
		}
	}
	function save_model(){
		$connect = D('Commlib.Code');
		if($_POST){
			$ret = $connect -> save_model();
			if($ret['code']){
				$this -> assign('jumpUrl','/index.php/Commlib/Code/index');
				$this -> success("添加成功");
			}else{
				$this->error($ret['msg']);
			}			
		}else{
			$where = array(
				'id' => $_GET['id']
			);
			$info = $connect->table('modelmanagement')-> where($where)->find();
			$info['fieldcontent'] = json_decode($info['fieldcontent'],true);
			$this->assign("info" , $info);
			$dir_name_list = $connect->get_module_dir();
			$this->assign("dir_name_list" , $dir_name_list);			
			$this->display();
		}
	}
	/*
    function pub_get_tables(){
		$database = $_GET['database'] ;	
		$connect = D('Commlib.Code');
		$connect -> myConnect($database);
		$list = $connect -> query("SHOW TABLES like '%".$_GET['query']."%'");
		//var_dump($list);
	//	echo $connect ->getlastsql();exit;
		$data = array(
			'query' => $_GET['query'],
			'suggestions' => array(),
		);
		foreach($list as $v){
			$data['suggestions'][] = $v['Tables_in_'.$database.' '.'(%'.$_GET['query'].'%)'];
		}
		//var_dump($tables);exit;
		exit(json_encode($data));
    }
    //获取模块文件
	function pub_get_action_file(){
		$module = $_GET['module'];
		if(!S('module_file'.$module)){
			$show_module_file = array();
			$path = "./Lib/Action/".$module; 
			$dir = opendir($path);
			 while($file = readdir($dir)) {
				 if($file != '.' && $file != '..'){
					 $show_module_file[] = $file; 
				 }
			 }
			 S('module_file'.$module,$show_module_file,86400);
		 }else{
			 $show_module_file = S('module_file'.$module);
		 }
		exit(json_encode($show_module_file));
	}	
	*/
}
?>
