<?php

class AdRatecardAction extends CommonAction{

	function rate_list(){
		$model = new Model();
		if(isset($_GET['rate_name'])){
			$rate_name = trim($_GET['rate_name']);
			$where_go .= " and rate_name like '%{$rate_name}%'";
		}
		if(isset($_GET['start_tm']) && isset($_GET['end_tm'])){
			$start = $_GET['start_tm'];
			$end = $_GET['end_tm'];
			$start_tm = strtotime($_GET['start_tm']);
			$end_tm = strtotime($_GET['end_tm']);
			if($start > $end){
				$this -> error("开始时间不能大于结束时间");
			}
			$where_go .= " and create_tm >= {$start_tm} and create_tm <= {$end_tm}";
		}else{
			$start = date('Y-m-d 00:00:00',strtotime('-1 year'));
			$start_tm = strtotime(date('Ymd 00:00:00',strtotime('-1 year')));
			$end = date('Y-m-d 23:59:59',time());
			$end_tm = time();
			$where_go .= " and create_tm >= {$start_tm} and create_tm <= {$end_tm}";
		}
		
		$where['_string'] = "status != 0".$where_go;
		$count = $model -> table('ad_rate_card') -> where($where) -> count();
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 50, $param);
		$result = $model -> table('ad_rate_card') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('create_tm DESC') -> select();
	
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
		$this -> assign('rate_name',$rate_name);
		$this -> assign('start',$start);
		$this -> assign('end',$end);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$Page -> setConfig('header', '篇记录');
		$Page -> setConfig('first', '<<');
		$Page -> setConfig('last', '>>');
		$show = $Page->show();
		$this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> display();
	}

	function change_rate(){
		$model = new Model();
		if($_GET['rate_name']){
			$where_go .= "/rate_name/{$_GET['rate_name']}";
		}
		if($_GET['start_tm']){
			$where_go .= "/start_tm/{$_GET['start_tm']}";
		}
		if($_GET['end_tm']){
			$where_go .= "/end_tm/{$_GET['end_tm']}";
		}
		if($_GET['p']){
			$where_go .= "/p/{$_GET['p']}";
		}
		if($_GET['lr']){
			$where_go .= "/lr/{$_GET['lr']}";
		}
		$id = $_GET['id'];
		$have_result = $model -> table('ad_rate_card') -> where(array('is_rate' => 1)) -> save(array('is_rate' => 2));
		$my_result = $model -> table('ad_rate_card') -> where(array('id' => $id)) -> save(array('is_rate' => 1));
		if($my_result){
			$this -> writelog("已编辑id为{$id}的刊例价格为默认刊例价格");
			$this -> assign("jumpUrl","/index.php/Sendnum/AdRatecard/rate_list".$where_go);
			$this -> success("设置成功");
		}else{
			$this -> success("设置失败");
		}
	}


	function change_status(){
		$model = new Model();
		if($_GET['rate_name']){
			$where_go .= "/rate_name/{$_GET['rate_name']}";
		}
		if($_GET['start_tm']){
			$where_go .= "/start_tm/{$_GET['start_tm']}";
		}
		if($_GET['end_tm']){
			$where_go .= "/end_tm/{$_GET['end_tm']}";
		}
		if($_GET['p']){
			$where_go .= "/p/{$_GET['p']}";
		}
		if($_GET['lr']){
			$where_go .= "/lr/{$_GET['lr']}";
		}
		$id = $_GET['id'];
		$status = $_GET['status'];
		$log_result = $this -> logcheck(array('id' => $id),'ad_rate_card',array('status' => $status),$model);
		$result = $model -> table('ad_rate_card') -> where(array('id' => $id)) -> save(array('status' => $status));
		if($result){
			$this -> writelog("已编辑id为{$id}的刊例价格状态".$log_result);
			$this -> assign('jumpUrl',"/index.php/Sendnum/AdRatecard/rate_list".$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}	
	}
	
	function add_rate_show(){
		$this -> display();
	}
		
	function add_rate_do(){
		$model = new Model();
		$rate_name = trim($_POST['rate_name']);
			if(!$rate_name){
			$this -> error("请填写刊例价格名称");
		}
		$comment = $_POST['comment'];
		$rate_card = $_FILES['rate_card'];
		//刊例价格配置
		$config = array(array('首页推荐',2,'EX',1,'sj_extent_v1','get_extent_data',array('parent_union_id' => '','pid' => 1,'type' => '<>2','parent_id' => 0)),array('首页轮播图',1,'FP',2),array('首页最新',2,'TN',3,'sj_category_extent','get_extent_data',array('category_type' => 'top_new','pid' => 1)),array('首页必备',2,'FM',4,'sj_necessary_extent','get_extent_data'),array('首页应用',3,'FA',5,array('sj_category','sj_category_extent'),'get_app_data',array('category_type' => 'top_1_hot')),array('搜索频道',2,'SK',6,'','get_search_data'),array('用户还下载了',1,'PR',7),array('类别频道',1,'CC',8));
		//获取刊例价格文件
		$rate_data = array();
		if($rate_card['size'] != 0){
			$file = fopen($_FILES['rate_card']['tmp_name'],'r'); 
			while ($data = fgetcsv($file)) {
				$my_rate[] = $data;
			}
			fclose($file);
			foreach($config as $key => $val){
				$config_name[] = $val[0];
			}
			foreach($my_rate as $key => $val){
				$con_data = array();
				$result = array();
				$correct_result = array();
				$wrong_result = array();
				$wrong_results = array();
				foreach($config as $k => $v){
					if($v[1] != 1 && $val[0] == $v[0]){
						$con_data[] = $val;
						$con_table = $v[4];
						$con_prefix = $v[2];
						$con_id = $v[3];
						$con_map = $v[6];
						$result = $this -> $v[5]($con_data,$con_table,$con_prefix,$v[3],$con_map);
						if($result[0]){
							$correct_result = $result[0];
						}
						if($result[1]){
							$wrong_result = $result[1];
						}
					}elseif($v[1] == 1 && $val[0] == $v[0]){
						$val['grand_id'] = $v[3];
						$correct_result = $val;
					}
				}
				
				if(!in_array($val[0],$config_name)){
					if($val[0]){
						$wrong_result = $val;
					}
				}
			
				if($correct_result){
					$correct[] = $correct_result;
				}
				if($wrong_result && $wrong_result[0] != "﻿"){
					$wrong[] = $wrong_result;
				}
			}
			
		}else{
			$this -> error("请上传刊例价格表");
		}
		if($rate_card['size'] != 0){
			$file_dir = C('AD_MODEL_FILE');
			$file_type_arr = array_reverse(explode('.',$rate_card['name']));
			$file_type = $file_type_arr[0];
			$path = $_SESSION['admin']['admin_id'].time().'.'.$file_type;
			if(move_uploaded_file($rate_card["tmp_name"], $file_dir .'/'. $path)){
				$rate_data['file_url'] = $file_dir .'/'. $path;
					$rate_data = array(
					'rate_name' => $rate_name,
					'file_url' => $file_dir .'/'. $path,
					'comment' => $comment,
					'create_tm' => time(),
					'update_tm' => time(),
					'is_rate' => 2,
					'status' => 1
				);
			}else{
				$this -> error("保存文件失败");
			}
			
		}else{
			$rate_data = array(
				'rate_name' => $rate_name,
				'comment' => $comment,
				'create_tm' => time(),
				'update_tm' => time(),
				'is_rate' => 2,
				'status' => 1
			);
		
		}
	
		$rate_result = $model -> table('ad_rate_card') -> add($rate_data);
	
		foreach($correct as $key => $val){
			if($val['extent']){
				$card_data = array(
					'rate_id' => $rate_result,
					'grand_id' => $val['grand_id'],
					'parent_id' => $val['parent_id'],
					'child_id' => $val['extent']['child_id'],
					'app_normal' => $val['extent'][4],
					'app_weekend' => $val['extent'][5],
					'game_normal' => $val['extent'][6],
					'game_weekend' => $val['extent'][7],
					'comment' => $val['extent'][8],
					'create_tm' => time(),
					'update_tm' => time(),
					'status' => 1
				);	
			}else{
				if(!is_string($val[1])){
					$card_data = array(
						'rate_id' => $rate_result,
						'grand_id' => $val[0]['grand_id'],
						'parent_id' => $val[0]['parent_id'],
						'app_normal' => $val[0][3],
						'app_weekend' => $val[0][4],
						'game_normal' => $val[0][5],
						'game_weekend' => $val[0][6],
						'comment' => $val[0][7],
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1
					);
				}else{
					$card_data = array(
						'rate_id' => $rate_result,
						'grand_id' => $val['grand_id'],
						'app_normal' => $val[1],
						'app_weekend' => $val[2],
						'game_normal' => $val[3],
						'game_weekend' => $val[4],
						'comment' => $val[5],
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1
					);
				}
				
			}
			$result = $model -> table('ad_rate') -> add($card_data);
		}
		
		if($rate_result){
			$this -> writelog("已添加id为{$result}的刊例价格");
			$this -> assign('jumpUrl','/index.php/Sendnum/AdRatecard/rate_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
		
		
	}
	


	function get_extent_data($data,$table,$prefix,$id,$maps=array()){
		$model = new Model();
		$map = array(
			'status' => 1,
			'type' => array('exp', '!=2'),
		);
		if($maps){
			$map = array_merge($map,$maps);
		}
		$extent_list = $model -> table($table) ->where($map)->order('rank asc')->select();
		foreach($extent_list as $key => $val){
			$extent_name[] = $val['extent_name'];
		}
	
		foreach($data as $key => $val){
			if(in_array($val[1],$extent_name)){
				$map['extent_name'] = $val[1];
				$map['extent_id'] = $val[2];
				$extend_id = $model -> table($table) -> where($map) -> select();
				if($extend_id){
					$val['grand_id'] = $id;
					$val['parent_id'] = $prefix.'_'.$extend_id[0]['extent_id'];
					$correct_result[] = $val;
				}
			}else{
					$wrong_result[] = $val;
			}
		}
	
		$result = array($correct_result,$wrong_result);
		return $result;
	}
	
	function get_app_data($data,$table,$prefix,$id,$maps=array()){

		$model = new Model();
		$map = array(
			'status' => 1,
			'pid' => 1,
			'type' => array('exp', '!=2'),
		);
		if($maps){
			$map_hot = array_merge($map,$maps);
		}
		$result = $model -> table($table[1]) ->where($map_hot)->order('rank asc')->select();
		foreach($result as $key => $val){
			$hot_result[$key]['extent_id'] = $prefix.'_'.$val['extent_id'];
			$hot_result[$key]['extent_name'] = $val['extent_name'];
		}
		foreach($hot_result as $key => $val){
			$hot_name[] = $val['extent_name'];
		}
		foreach($data as $key => $val){
			if($val[1] == '热门'){
				if(in_array($val[2],$hot_name)){
					$correct['parent_id'] = '热门';
					$correct['grand_id'] = $id;
					$val['child_id'] = $prefix.'_'.$val[3];
					$correct['extent'] = $val;
				}else{
					$wrong['extent'] = $val;
				}
			}
		}
	
		$category_parent_list = $model -> table($table[0]) -> where(array('status' => 1,'parentid' => 1)) -> field('category_id') -> select();
		
		foreach($category_parent_list as $key => $val){
			$category_child_list = $model -> table($table[0]) -> where(array('status' => 1,'parentid' => $val['category_id'])) -> field('category_id,name') -> select();
			$the_category_list = array();
			foreach($category_child_list as $k => $v){
				$the_category_list[$k]['category_id'] = $v['category_id'];
				$the_category_list[$k]['name'] = $v['name'];
			}
			$category_list[] = $the_category_list;
		}
		foreach($category_list as $key => $val){
			foreach($val as $k => $v){
				$category_all_list[] = $v;
			}
		}
	
		foreach($category_all_list as $key => $val){
			$category_type = 'top_'.$val['category_id'].'_hot';
			$category_extent_result = $model -> table($table[1]) -> where(array('status' => 1,'pid' => 1,'type' => array('exp','!=2'),'category_type' => $category_type)) -> select();
			$category_extent = array();
			foreach($category_extent_result as $k => $v){
				$category_extent[] = $v['extent_name'];
			}
			if($category_extent){
				$category_type_result[$key]['category_id'] = $val['category_id'];
				$category_type_result[$key]['category_name'] = $val['name'].'-最热';
				$category_type_result[$key]['extent'][] = $category_extent;
			}
		}
		
		foreach($category_type_result as $key => $val){
			foreach($data as $k => $v){
				if($val['category_name'] == $v[1]){
					if(in_array($v[2],$val['extent'][0])){
						$correct['parent_id'] = $val['category_id'];
						$correct['grand_id'] = $id;
						$v['child_id'] = $prefix.'_'.$v['3'];
						$correct['extent'] = $v;
					}else{
						$wrong['extent'] = $v;
					}
				}
			}
		}
		
		$result = array($correct,$wrong);
		return $result;
	}
	
	function  get_search_data($data,$table='',$prefix,$id,$maps=array()){
		$my_search = array('关键字搜索栏','关键字列表');
		foreach($data as $key => $val){
			if(in_array($val[1],$my_search)){
				$val['grand_id'] = $id;
				$val['parent_id'] = $val[1];
				$correct[] = $val;
			}else{
				$wrong[] = $val;
			}
		}
		return $result = array($correct,$wrong);
	}
	
	
	function  edit_rate_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('ad_rate_card') -> where(array('id' => $id)) -> select();
		$this -> assign('rate_name',$_GET['rate_name']);
		$this -> assign('start',$_GET['start']);
		$this -> assign('end',$_GET['end']);
		$this -> assign('p',$_GET['p']);
		$this -> assign('lr',$_GET['lr']);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_rate_do(){
		$model = new Model();
		if($_POST['rate_names']){
			$where_go .= "/rate_name/{$_POST['rate_names']}";
		}
		if($_POST['starts']){
			$where_go .= "/start_tm/{$_POST['starts']}";
		}
		if($_POST['ends']){
			$where_go .= "/end_tm/{$_POST['ends']}";
		}
		if($_POST['p']){
			$where_go .= "/p/{$_POST['p']}";
		}
		if($_POST['lr']){
			$where_go .= "/lr/{$_POST['lr']}";
		}
		$id = $_POST['id'];
		$rate_name = trim($_POST['rate_name']);
			if(!$rate_name){
			$this -> error("请填写刊例价格名称");
		}
		$comment = $_POST['comment'];
		$rate_card = $_FILES['rate_card'];
		//刊例价格配置
		$config = array(array('首页推荐',2,'EX',1,'sj_extent_v1','get_extent_data',array('parent_union_id' => '','pid' => 1)),array('首页轮播图',1,'FP',2),array('首页最新',2,'TN',3,'sj_category_extent','get_extent_data',array('category_type' => 'top_new','pid' => 1)),array('首页必备',2,'FM',4,'sj_necessary_extent','get_extent_data'),array('首页应用',3,'FA',5,array('sj_category','sj_category_extent'),'get_app_data',array('category_type' => 'top_1_hot')),array('搜索频道',2,'SK',6,'','get_search_data'),array('用户还下载了',1,'PR',7),array('类别频道',1,'CC',8));
		//获取刊例价格文件
		if($rate_card['size'] != 0){
			$file = fopen($_FILES['rate_card']['tmp_name'],'r'); 
			while ($data = fgetcsv($file)) {
				$my_rate[] = $data;
			}
			fclose($file);
			foreach($config as $key => $val){
				$config_name[] = $val[0];
			}
			foreach($my_rate as $key => $val){
				$con_data = array();
				$result = array();
				$correct_result = array();
				$wrong_result = array();
				$wrong_results = array();
				foreach($config as $k => $v){
					if($v[1] != 1 && $val[0] == $v[0]){
						$con_data[] = $val;
						$con_table = $v[4];
						$con_prefix = $v[2];
						$con_id = $v[3];
						$con_map = $v[6];
						$result = $this -> $v[5]($con_data,$con_table,$con_prefix,$v[3],$con_map);
						if($result[0]){
							$correct_result = $result[0];
						}
						if($result[1]){
							$wrong_result = $result[1];
						}
					}elseif($v[1] == 1 && $val[0] == $v[0]){
						$val['grand_id'] = $v[3];
						$correct_result = $val;
					}
				}
				
				if(!in_array($val[0],$config_name)){
					if($val[0]){
						$wrong_result = $val;
					}
				}
			
				if($correct_result){
					$correct[] = $correct_result;
				}
				if($wrong_result && $wrong_result[0] != "﻿"){
					$wrong[] = $wrong_result;
				}
			}
			
			$have_result = $model -> table('ad_rate') -> where(array('rate_id' => $id)) -> save(array('status' => 0));
			
			foreach($correct as $key => $val){
				if($val['extent']){
					$card_data = array(
						'rate_id' => $rate_result,
						'grand_id' => $val['grand_id'],
						'parent_id' => $val['parent_id'],
						'child_id' => $val['extent']['child_id'],
						'app_normal' => $val['extent'][4],
						'app_weekend' => $val['extent'][5],
						'game_normal' => $val['extent'][6],
						'game_weekend' => $val['extent'][7],
						'comment' => $val['extent'][8],
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1
					);	
				}else{
					if(!is_string($val[1])){
						$card_data = array(
							'rate_id' => $rate_result,
							'grand_id' => $val[0]['grand_id'],
							'parent_id' => $val[0]['parent_id'],
							'app_normal' => $val[0][3],
							'app_weekend' => $val[0][4],
							'game_normal' => $val[0][5],
							'game_weekend' => $val[0][6],
							'comment' => $val[0][7],
							'create_tm' => time(),
							'update_tm' => time(),
							'status' => 1
						);
					}else{
						$card_data = array(
							'rate_id' => $rate_result,
							'grand_id' => $val['grand_id'],
							'app_normal' => $val[1],
							'app_weekend' => $val[2],
							'game_normal' => $val[3],
							'game_weekend' => $val[4],
							'comment' => $val[5],
							'create_tm' => time(),
							'update_tm' => time(),
							'status' => 1
						);
					}
				}
				$result = $model -> table('ad_rate') -> add($card_data);
			}
		}
		
		if($rate_card['size'] != 0){
			$file_dir = C('AD_MODEL_FILE');
			$file_type_arr = array_reverse(explode('.',$rate_card['name']));
			$file_type = $file_type_arr[0];
			$path = $_SESSION['admin']['admin_id'].time().'.'.$file_type;
			if(move_uploaded_file($rate_card["tmp_name"], $file_dir .'/'. $path)){
				$rate_data['file_url'] = $file_dir .'/'. $path;
					$rate_data = array(
					'rate_name' => $rate_name,
					'file_url' => $file_dir .'/'. $path,
					'comment' => $comment,
					'update_tm' => time(),
				);
			}else{
				$this -> error("保存文件失败");
			}
			
		}else{
			$rate_data = array(
				'rate_name' => $rate_name,
				'comment' => $comment,
				'update_tm' => time(),
			);
		
		}
		
		$log_result = $this -> logcheck(array('id' => $id),'ad_rate_card',$rate_data,$model);
		$rate_result = $model -> table('ad_rate_card') -> where(array('id' => $id))-> save($rate_data);
	
		if($rate_result){
			$this -> writelog("已编辑id为{$id}的刊例价格".$log_result);
			$this -> assign('jumpUrl','/index.php/Sendnum/AdRatecard/rate_list'.$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
		
	}
	
	function upload_model(){
		$model = new Model();
		$id = $_GET['id'];
		
		if($id == 'example'){
			$file_dir = C('AD_MODEL_FILE');
			$file = $file_dir.'/'.'example.csv';
			$file_names = 'example.csv';
		}else{
			$result = $model -> table('ad_rate_card') -> where(array('id' => $id)) -> select();
			$file = $result[0]['file_url'];
			$file_name_arr = explode('.',$file);
			$file_names = time().'.'.$file_name_arr[1];
		}

		if(!file_exists($file)){
			$this -> error("文件不存在");
		}else{
			$open_file = fopen($file,"r");
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: ".filesize($file));
			Header("Content-Disposition: attachment; filename=" . $file_names);
			// 输出文件内容
			echo fread($open_file,filesize($file));
			fclose($file);
			exit();
		}	
	
	
	}
	
	
	function rate_card_list(){
		$this -> display();
	}
	
	function edit_card_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('ad_rate') -> where(array('id' => $id)) -> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_card_do(){
		$model = new Model();
		$app_normal = $_GET['app_normal'];
		$app_weekend = $_GET['app_weekend'];
		$game_normal = $_GET['game_normal'];
		$game_weekend = $_GET['game_weekend'];
		$comment = $_GET['comment'];
		$id = $_GET['id'];
		$data = array(
			'app_normal' => $app_normal,
			'app_weekend' => $app_weekend,
			'game_normal' => $game_normal,
			'game_weekend' => $game_weekend,
			'comment' => $comment,
		);
		$log_result = $this -> logcheck(array('id' => $id),'ad_rate',$data,$model);
		$result = $model -> table('ad_rate') -> where(array('id' => $id)) -> save($data);
		$rate_result = $model -> table('ad_rate') -> where(array('id' => $id)) -> select();
		if($result){
			$this -> writelog("已编辑id为{$id}的刊例价格广告位".$log_result);
			$this -> assign('jumpUrl','/index.php/Senddnum/AdRatecard/rate_card_list/id/'.$rate_result[0]['rate_id']);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_rate_card(){
		$model = New Model();
		$id = $_GET['id'];
		$rate_result = $model -> table('ad_rate') -> where(array('id' => $id)) -> select();
		$result = $model -> table('ad_rate') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除刊例价格id为{$rate_result[0]['rate_id']},广告位id为{$id}的广告位");
				$this -> assign('jumpUrl','/index.php/Senddnum/AdRatecard/rate_card_list/id/'.$rate_result[0]['rate_id']);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
}
