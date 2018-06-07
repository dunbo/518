<?php 
class GamesubjectAction extends CommonAction{

	//安卓游戏专题快速入口配置
	function subject_config_list(){
		$model = new Model();
		$from = $_GET['from'];
		if(!$from){
			$from = 1;
		}
		$where['_string'] = "type = {$from} and status = 1";
		$result = $model -> table('sj_olgame_subject') -> where($where) -> order('rank') -> select();

		$count = count($result);
		$this -> assign("count",$count);
		$this -> assign("from",$from);
		$this -> assign("result",$result);
		$this -> display("subject_config_now");

	}
	
	function change_my_rank(){
		$id = $_GET['id'];
		$action = $_GET['action'];
		$type = $_GET['type'];
		$my_result = $this -> change_rank($action,'sj_olgame_subject',$id,$type);
		if($my_result){
			$this -> writelog("推荐管理-安卓游戏专题快速入口配置-已编辑id为{$id}的专题的排序", 'sj_olgame_subject', $id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Gamesubject/subject_config_list');
			$this -> success("编辑成功");
		}
	}
	
	function rank_top(){
		$id = $_GET['id'];
		$type= $_GET['type'];
		$my_result = $this -> top_repick('sj_olgame_subject',$id,$type);
		if($my_result){
			$this -> writelog("推荐管理-安卓游戏专题快速入口配置-已编辑id为{$id}的专题的排序为1", 'sj_olgame_subject', $id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Gamesubject/subject_config_list');
			$this -> success("编辑成功");
		}
	
	}
	
	//添加安卓游戏专题展示
	function add_subject_show(){
		$model = new Model();
		$subject_result = $model -> table('sj_feature') -> where(array('pid' => 5,'status' => 1)) -> select();

		$this -> assign("subject_result",$subject_result);
		$this -> display();
	
	}
	
	//添加安卓游戏专题提交
	function add_subject_submit(){
		$model = new Model();
		$type = $_POST['type'];
		$subject_name = $_POST['subject_name'];
		$halve_pic = $_FILES['halve_pic'];
		$high_pic = $_FILES['high_pic'];
		if($type == 5 || $type == 6){
			$have_been = $model -> table('sj_olgame_subject') -> where(array('status' => 1,'type' => $type)) -> select();
			if($have_been){
				$this -> error("模拟器或礼包专题只能存在一个");
			}
		}
		if($type == 1 || $type == 2){
			if(!$subject_name || $subject_name == '请选择'){
				$this -> error("请选择专题");
			}
			if(!$high_pic['size']){
				$this -> error("请上传高分辨率图片");
			}
			if(!$halve_pic['size']){
				$this -> error("请上传中分辨率图片");
			}
		}
		
		$high_pic_new = $_FILES['high_pic_new'];
		$halve_pic_new = $_FILES['halve_pic_new'];
		if(!$high_pic_new['size']){
			$this -> error("请上传新高分辨率图片");
		}
		
		if(!$halve_pic_new['size']){
			$this -> error("请上传新中分辨率图片");
		}
	
		if($high_pic['size']){
			$high_wd = getimagesize($high_pic['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ',$widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=',$wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg,1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=',$hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg,1);
			if($width_go_hg != 235 || $height_go_hg != 76){
				$this -> error("高分辨率图片不符合条件");
			}
		}
		
		if($halve_pic['size']){
			$high_wds = getimagesize($halve_pic['tmp_name']);
			$widhig_hgs = $high_wds[3];
			$wh_hgs = explode(' ',$widhig_hgs);
			$wh1_hgs = $wh_hgs[0];
			$widths_hgs = explode('=',$wh1_hgs);
			$width1_hgs = substr($widths_hgs[1], 0, -1);
			$width_go_hgs = substr($width1_hgs,1);
			$hi1_hgs = $wh_hgs[1];
			$heights_hgs = explode('=',$hi1_hgs);
			$height1_hgs = substr($heights_hgs[1], 0, -1);
			$height_go_hgs = substr($height1_hgs,1);
			if($width_go_hgs != 156 || $height_go_hgs != 50){
				$this -> error("中分辨率图片不符合条件");
			}
		}
		if($type == 1 || $type == 2){
			if($high_pic['type'] != 'image/png' && $high_pic['type'] != 'image/x-png' && $high_pic['type'] != 'image/jpg' && $high_pic['type'] != 'image/jpeg' && $high_pic['type'] != 'image/pjpeg'){
				$this -> error("高分辨率图片格式错误");
			}
			if($halve_pic['type'] != 'image/png' && $halve_pic['type'] != 'image/jpg' && $halve_pic['type'] != 'image/jpeg' && $halve_pic['type'] != 'image/pjpeg'){
				$this -> error("中分辨率图片格式错误");
			}
			if($high_pic['size'] > 35*1024){
				$this -> error("高分辨率图片不可大于35KB");
			}
			if($halve_pic['size'] > 35*1024){
				$this -> error("中分辨率图片不可大于35KB");
			}
		}
		//验证同一入口的有效时间段唯一，不允许有交集
		//if($type != 3 && $type != 4){
		//	$where_only_time['_string'] = "status = 1 and type = {$type} and start_tm <= {$end_tm} and end_tm >= {$start_tm} and end_tm > ".time()."";
		//	$only_time_result = $model -> table('sj_olgame_subject') -> where($where_only_time) -> select();
		//	if($only_time_result){
		//		$this -> error("同一入口的有效时间段唯一");
		//	}
		//}
		
		//验证同一专题是否在两个入口展示,左右备选库不可添加相同专题
		if($type == 1 || $type == 2){
			$where_only_type['_string'] = "status = 1 and type != 5 and type != 6 and subject_id = {$subject_name}";
			$only_type_result = $model -> table('sj_olgame_subject') -> where($where_only_type) -> select();
		
			if($only_type_result){
				$this -> error("同一个专题不允许被同时展示在2个入口或在同一入口展示两次");
			}
			if($subject_name == 'all'){
				
				$where_all_type['_string'] = "status = 1 and type != 5 and type != 6 and subject_all = 2";
				$only_all_result = $model -> table('sj_olgame_subject') -> where($where_all_type) -> select();
		
				if($only_all_result){
					$this -> error("同一个专题不允许被同时展示在2个入口或在同一入口展示两次");
				}
			}
			
		}
		//else{
		//	$where_only_type['_string'] = "status = 1 and (type = 4 or type = 3) and subject_id = {$subject_name}";
		//	$only_type_result = $model -> table('sj_olgame_subject') -> where($where_only_type) -> select();
		//	if($only_type_result){
		//		$this -> error("已选此专题作为备选库");
		//	}
		//	if($subject_name == 'all'){
		//		$where_all_type['_string'] = "status = 1 and (type = 3 or type = 4) and subject_all = 2 ";
		//		$only_all_result = $model -> table('sj_olgame_subject') -> where($where_all_type) -> select();
		//
		//		if($only_all_result){
		//			$this -> error("已选此专题作为备选库");
		//		}
		//	}
		//}

		
		$path=date("Ym/d/",time());
		$config = array(
			'multi_config' => array(
				'high_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'=> 235, //图片常规压缩宽度
					'img_p_height'=> 76, //图片常规压缩高度
				),
				'halve_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'=> 156, //图片常规压缩宽度
					'img_p_height'=> 50, //图片常规压缩高度
				),
				'high_pic_new' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'=> 162, //图片常规压缩宽度
					'img_p_height'=> 62, //图片常规压缩高度
				),
				'halve_pic_new' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'=> 80, //图片常规压缩宽度
					'img_p_height'=> 42, //图片常规压缩高度
				),
			),
		);
		$list = $this -> _uploadapk(0, $config);
	
		foreach($list['image'] as  $key => $val){
			if($val['post_name'] == 'high_pic'){
				$data['high_url'] = $val['url'];
			}
			
			if($val['post_name'] == 'halve_pic'){
				$data['halve_url'] = $val['url'];
			}
			
			if($val['post_name'] == 'high_pic_new'){
				$data['high_url_new'] = $val['url'];
			}
			
			if($val['post_name'] == 'halve_pic_new'){
				$data['halve_url_new'] = $val['url'];
			}
		}
		if($subject_name != "all"){
			$subject_result = $model -> table('sj_feature') -> where(array('feature_id' => $subject_name,'pid' => 5)) -> select();
			$data['subject_name'] = $subject_result[0]['name'];
			$data['subject_all'] = 1;
			$data['subject_id'] = $subject_name;
		}else{
			$data['subject_name'] = "所有专题";
			$data['subject_all'] = 2;
		}
		
		$data['type'] = $type;
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$have_beens = $model -> table('sj_olgame_subject') -> where(array('status' => 1,'type' => $type)) -> count();

		if($type == 1 || $type == 2){
			$data['rank'] = $have_beens + 1;
		}
	
		$result = $model -> table('sj_olgame_subject') -> add($data);

		if($result){
			if($type == 1){$a = '左侧专题1';}elseif($type == 2){$a = '左侧专题2';}elseif($type == 5){$a = '模拟器';}elseif($type == 6){$a = '礼包';}
			$this -> writelog("已添加安卓游戏专题id为{$result}为{$a}", 'sj_olgame_subject', $result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Gamesubject/subject_config_list');
			$this -> success("添加成功");
		}
	}
	
	
	//编辑安卓游戏专题展示
	function edit_subject_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_olgame_subject') -> where(array('id' => $id)) -> select();
		$subject_result = $model -> table('sj_feature') -> where(array('pid' => 5,'status' => 1)) -> select();
		$this -> assign("subject_result",$subject_result);
		$this -> assign("result",$result);
		$this -> display();
	
	}
	
	//编辑安卓游戏专题提交
	function edit_subject_submit(){
		$model = new Model();
		$id = $_POST['id'];
		$type = $_POST['type'];
	
		$my_result = $model -> table('sj_olgame_subject') -> where(array('id' => $id)) -> select();
		//if($my_result[0]['type'] != $type){
		//	if($my_result[0]['type'] == 3 || $my_result[0]['type'] == 4){
		//		$all_result = $model -> table('sj_olgame_subject') -> where(array('type' => $my_result[0]['type'],'status' => 1)) -> select();
		//		if(count($all_result) <= 2){
		//			$this -> error("左/右备选库至少保留两个备选排期");
		//		}
		//	}
		//}
		
		
		$subject_name = $_POST['subject_name'];
		if($type == 1 || $type == 2){
			if(!$subject_name){
				$this -> error("请选择专题");
			}
			if(($my_result[0]['type'] == 1 && $type == 2) || $my_result[0]['type'] == 2 && $type == 1){
				$have_been = $model -> table('sj_olgame_subject') -> where(array('type' => $my_result[0]['type'],'status' => 1)) -> select();
				if(count($have_been) <= 1){
					$this -> error("该专题入口至少保留一个专题");
				}
			}
		}
		$high_pic = $_FILES['high_pic'];
		
		$path=date("Ym/d/",time());
		if($high_pic['size']){
			if($high_pic['type'] != 'image/png' && $high_pic['type'] != 'image/jpg' && $high_pic['type'] != 'image/jpeg' && $high_pic['type'] != 'image/pjpeg'){
				$this -> error("高分辨率图片格式错误");
			}
			if($high_pic['size'] > 35*1024){
				$this -> error("高分辨率图片不可大于35KB");
			}
		}
		
		
		
		$halve_pic = $_FILES['halve_pic'];
		if($halve_pic['size']){
			if($halve_pic['type'] != 'image/png' && $halve_pic['type'] != 'image/jpg' && $halve_pic['type'] != 'image/jpeg' && $halve_pic['type'] != 'image/pjpeg'){
				$this -> error("中分辨率图片格式错误");
			}
			if($halve_pic['size'] > 35*1024){
				$this -> error("中分辨率图片不可大于35KB");
			}
		}
		
		if($high_pic['size']){
			$high_wd = getimagesize($high_pic['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ',$widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=',$wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg,1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=',$hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg,1);
			if($width_go_hg != 235 || $height_go_hg != 76){
				$this -> error("高分辨率图片不符合条件");
			}
		}
		
		if($halve_pic['size']){
			$high_wds = getimagesize($halve_pic['tmp_name']);
			$widhig_hgs = $high_wds[3];
			$wh_hgs = explode(' ',$widhig_hgs);
			$wh1_hgs = $wh_hgs[0];
			$widths_hgs = explode('=',$wh1_hgs);
			$width1_hgs = substr($widths_hgs[1], 0, -1);
			$width_go_hgs = substr($width1_hgs,1);
			$hi1_hgs = $wh_hgs[1];
			$heights_hgs = explode('=',$hi1_hgs);
			$height1_hgs = substr($heights_hgs[1], 0, -1);
			$height_go_hgs = substr($height1_hgs,1);
			if($width_go_hgs != 156 || $height_go_hgs != 50){
				$this -> error("中分辨率图片不符合条件");
			}
		}
		
		
		if($high_pic['size'] || $halve_pic['size'] || $_FILES['high_pic_new']['size'] || $_FILES['halve_pic_new']['size'])	{
			$config = array(
				'multi_config' => array(
					'high_pic' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
					),
					'halve_pic' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
					),
					'high_pic_new' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'=> 120, //图片常规压缩宽度
						'img_p_height'=> 62, //图片常规压缩高度
					),
					'halve_pic_new' => array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'=> 80, //图片常规压缩宽度
						'img_p_height'=> 42, //图片常规压缩高度
					),
				),
			);
			$list = $this -> _uploadapk(0, $config);
		
			foreach($list['image'] as  $key => $val){
				if($val['post_name'] == 'high_pic'){
					$data['high_url'] = $val['url'];
				}
				if($val['post_name'] == 'halve_pic'){
					$data['halve_url'] = $val['url'];
				}
				if($val['post_name'] == 'high_pic_new'){
					$data['high_url_new'] = $val['url'];
				}
				if($val['post_name'] == 'halve_pic_new'){
					$data['halve_url_new'] = $val['url'];
				}
			}
		}
	
		//验证同一入口的有效时间段唯一，不允许有交集
		//if($type != 3 && $type != 4){
		//	$where_only_time['_string'] = "status = 1 and id != {$id} and type = {$type} and start_tm <= {$end_tm} and end_tm >= {$start_tm} and end_tm > ".time()."";
		//	$only_time_result = $model -> table('sj_olgame_subject') -> where($where_only_time) -> select();
		//	if($only_time_result){
		//		$this -> error("同一入口的有效时间段唯一");
		//	}
		//}
		

		//验证同一专题是否在两个入口展示,左右备选库不可添加相同专题
		if($type == 1 || $type == 2){
			$where_only_type['_string'] = "status = 1 and id != {$id} and type != 5 and type != 6 and subject_id = {$subject_name}";
			$only_type_result = $model -> table('sj_olgame_subject') -> where($where_only_type) -> select();

			if($only_type_result){
				$this -> error("同一个专题不允许被同时展示在2个入口或在同一入口展示两次");
			}
			if($subject_name == 'all'){
				$where_all_type['_string'] = "status = 1 and id != {$id} and type != 5 and type != 6 and subject_all = 2 ";
				$only_all_result = $model -> table('sj_olgame_subject') -> where($where_all_type) -> select();
				if($only_all_result){
					$this -> error("同一个专题不允许被同时展示在2个入口或在同一入口展示两次");
				}
			}
			
		}
		//else{
			
		//	$where_only_type['_string'] = "status = 1 and (type = 4 or type = 3) and subject_id = {$subject_name} and id != {$id}";
		//	$only_type_result = $model -> table('sj_olgame_subject') -> where($where_only_type) -> select();
		//	if($only_type_result){
		//		$this -> error("已选此专题作为备选库");
		//	}
		//	if($subject_name == 'all'){
		//		$where_all_type['_string'] = "status = 1 and (type = 3 or type = 4) and subject_all = 2 and id != {$id}";
		//		$only_all_result = $model -> table('sj_olgame_subject') -> where($where_all_type) -> select();

		//		if($only_all_result){
		//			$this -> error("已选此专题作为备选库");
		//		}
		//	}
			
		//}

		if($subject_name != "all"){
			$subject_result = $model -> table('sj_feature') -> where(array('feature_id' => $subject_name,'pid' => 5)) -> select();
			$data['subject_name'] = $subject_result[0]['name'];
			$data['subject_all'] = 1;
			$data['subject_id'] = $subject_name;
		}else{
			$data['subject_id'] = 0;
			$data['subject_name'] = "所有专题";
			$data['subject_all'] = 2;
		}
		$data['type'] = $type;
		$data['update_tm'] = time();
		$data['status'] = 1;
		$log_result = $this -> logcheck(array('id' =>$id),'sj_olgame_subject',$data,$model);
		
		if($type != $my_result[0]['type']){
			$need_where["_string"] = "status = 1 and type = {$my_result[0]['type']} and rank > {$my_result[0]['rank']}";
			$need_result = $model -> table('sj_olgame_subject') -> where($need_where) -> select();

			foreach($need_result as $key => $val){
				$update_where['id'] = $val['id'];
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table('sj_olgame_subject') -> where($update_where) -> save($update_data);
			}
			$all_subject = $model -> table('sj_olgame_subject') -> where(array('status' => 1,'type' => $type)) -> count();
			$data['rank'] = $all_subject + 1;
		}
		$result = $model -> table('sj_olgame_subject') -> where(array("id" => $id)) -> save($data);
	
		if($result){
			$this -> writelog("推荐管理-安卓游戏专题快速入口配置-已编辑id为{$id}的安卓游戏专题".$log_result, 'sj_olgame_subject', $id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Gamesubject/subject_config_list/from/{$type}");
			$this -> success("编辑成功");
		}
	}
	
	
	//安卓游戏专题删除
	function del_subject(){
		$model = new Model();
		$id = $_GET['id'];
		$else_result = $model -> table('sj_olgame_subject') -> where(array('id' => $id)) -> select();
		if($else_result[0]['type'] == 5 || $else_result[0]['type'] == 6){
			$this -> error("不可删除模拟器和礼包");
		}else{
			$have_been = $model -> table('sj_olgame_subject') -> where(array('type' => $else_result[0]['type'],'status' => 1)) -> count();
			if($have_been <= 1){
				$this -> error("左侧入口1和左侧入口2至少保留1条");
			}
		}
		
		//if($else_result[0]['type'] == 3 || $else_result[0]['type'] == 4){
		//	$count_result = $model -> table('sj_olgame_subject') -> where(array('type' => $else_result[0]['type'],'status' => 1)) -> select();
		//	if(count($count_result) <= 2){
		//		$this -> error("该备选库至少保留两个专题");
		//	}
		//}
		//if($else_result[0]['end_tm'] > time() || $else_result[0]['type'] == 3 || $else_result[0]['type'] == 4){
		//	$from = 1;
		//}else{
		//	$from = 2;
		//}
		if($else_result[0]['type'] == 1 || $else_result[0]['type'] == 2){
			$need_where["_string"] = "status = 1 and type = {$else_result[0]['type']} and rank > {$else_result[0]['rank']}";
			$need_result = $model -> table('sj_olgame_subject') -> where($need_where) -> select();
			foreach($need_result as $key => $val){
				$update_where['_string'] = "id = {$val['id']}";
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table('sj_olgame_subject') -> where($update_where) -> save($update_data);
			}
		}
		$data['rank'] = 0;
		$data['status'] = 0;
		$data['update_tm'] = time();
		$result = $model -> table('sj_olgame_subject') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("推荐管理-安卓游戏专题快速入口配置-已删除id为{$id}的安卓游戏专题".$log_result, 'sj_olgame_subject', $id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Gamesubject/subject_config_list/from/{$from}");
			$this -> success("操作成功");
		}
	}
	
	
	

}