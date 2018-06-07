<?php
/*
*多软件综合活动
*/
class ActivateAction extends CommonAction{
	
	public function activate_list(){
		$model = D('sendNum.Activity');	
		$where = array(
			'activate_type' => 2,
			'status' => 1
		);
		$this->check_where($where, 'ap_name', 'isset', 'like');
		$this->check_where($where, 'ap_id', 'isset');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total,$Page) = $model -> get_activity_page($where,$limit);
		if(EVN == '9test' || EVN == 'prod'){
			$my_host = 'http://m.anzhi.com';
		}elseif(EVN == '518test'){
			$my_host = ACTIVITY_URL;
		}
		$this -> assign('my_host',$my_host);
		$this -> assign('page', $Page->show());
		$this -> assign('result',$result);
		$this -> display();
	}


	function add_activate_show(){
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> display();
	}
	
	function add_activate_do(){
		$model = new Model();
		$ap_name = trim($_POST['ap_name']);
		$ap_type = $_POST['ap_type'];
		$ap_pic = $_FILES['ap_pic'];
		$ap_rule = $_POST['ap_rule'];
		$back_top = $_POST['back_top'];
		$ap_notice = $_POST['ap_notice'];
		if($ap_type == 3 || $ap_type == 4){
			if($this -> strlen_az($ap_notice) > 30 || !$ap_notice){
				$this -> error("请填写15字以内的提示文字");
			}
		}
		$have_been = $model -> table('sj_activity_page') -> where(array('ap_name' => $ap_name,'status' => 1)) -> select();
		if($have_been){
			$this -> error("该活动名称已存在");
		}
		if($this -> strlen_az($ap_name) > 60 || !$ap_name){
			$this -> error("请填写30字以内的活动名称");
		}
		if($this -> strlen_az($ap_rule) > 2000 || !$ap_rule){
			$this -> error("请填写1000字以内的活动规则");
		}
		
		if($ap_pic){
			$pic_tmp = getimagesize($ap_pic['tmp_name']);
			$my_highs = $pic_tmp[3];
			$wh_hgs = explode(' ',$my_highs);
			$wh1_hgs = $wh_hgs[0];
			$widths_hgs = explode('=',$wh1_hgs);
			$width1_hgs = substr($widths_hgs[1], 0, -1);
			$width_go_hgs = substr($width1_hgs,1);
			if($width_go_hgs != 480){
				$this -> error("请上传宽度为480的图片");
			}
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024*50,
				),
			));
			$lists=$this->_uploadapk(0, $config);
			$ap_imgurl = $lists['image'][0]['url'];
		}else{
			$this -> error("请上传活动图片");
		}
		
		$data = array(
			'ap_name' => $ap_name,
			'ap_type' => $ap_type,
			'ap_rule' => $ap_rule,
			'back_top' => $back_top,
			'ap_imgurl' => $ap_imgurl,
			'activate_type' => 2,
			'ap_notice' => $ap_notice,
			'ap_ctm' => time(),
			'ap_utm' => time()
		);
		
		$result = $model -> table('sj_activity_page') -> add($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已添加id为{$result}的多软件综合活动",'sj_activity_page',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_list/p/{$p}/lr/{$lr}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	
	}
	
	function edit_activate_show(){
		error_reporting(E_ALL);
		ini_set('display_errors','on');
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> select();
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_activate_do(){
		$model = new Model();
		$ap_name = $_POST['ap_name'];
		$ap_pic = $_FILES['ap_pic'];
		$ap_rule = $_POST['ap_rule'];
		$back_top = $_POST['back_top'];
		$id = $_POST['id'];
		$where_have['_string'] = "ap_name = '{$ap_name}' and ap_id != {$id} and status = 1";
		$have_been = $model -> table('sj_activity_page') -> where($where_have) -> select();
		if($have_been){
			$this -> error("该活动名称已存在");
		}
		if($this -> strlen_az($ap_name) > 60 || !$ap_name){
			$this -> error("请填写30字以内的活动名称");
		}
		$ap_notice = $_POST['ap_notice'];
		if($ap_type == 3 || $ap_type == 4){
			if($this -> strlen_az($ap_notice) > 30 || !$ap_notice){
				$this -> error("请填写15字以内的提示文字");
			}
		}
		if($this -> strlen_az($ap_rule) > 2000 || !$ap_rule){
			$this -> error("请填写1000字以内的活动规则");
		}
		
		if($_FILES['ap_pic']['size']){
			$pic_tmp = getimagesize($ap_pic['tmp_name']);
			$my_highs = $pic_tmp[3];
			$wh_hgs = explode(' ',$my_highs);
			$wh1_hgs = $wh_hgs[0];
			$widths_hgs = explode('=',$wh1_hgs);
			$width1_hgs = substr($widths_hgs[1], 0, -1);
			$width_go_hgs = substr($width1_hgs,1);
			if($width_go_hgs != 480){
				$this -> error("请上传宽度为480的图片");
			}
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024*50,
				),
			));
			$lists=$this->_uploadapk(0, $config);
			$ap_imgurl = $lists['image'][0]['url'];
		}
		if($_FILES['ap_pic']['size']){
			$data = array(
				'ap_name' => $ap_name,
				'ap_rule' => $ap_rule,
				'back_top' => $back_top,
				'ap_imgurl' => $ap_imgurl,
				'ap_notice' => $ap_notice,
				'ap_utm' => time()
			);
		}else{
			$data = array(
				'ap_name' => $ap_name,
				'ap_rule' => $ap_rule,
				'back_top' => $back_top,
				'ap_notice' => $ap_notice,
				'ap_utm' => time()
			);
		}
		$log_result = $this -> logcheck(array('ap_id' => $id),'sj_activity_page',$data,$model);
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已编辑id为{$id}的多软件综合活动".$log_result,'sj_activity_page',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_list/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	}
	
	
	function del_activate(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save(array('status' => 0));
		$my_category_result = $model -> table('sj_activity_page') -> where(array('active_id' => $id,'status' => 1)) -> select();
		foreach($my_category_result as $key => $val){
			$my_soft_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $val['id'],'status' => 1)) -> save(array('status' => 0));
		}
		$category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的多软件综合活动",'sj_activity_page',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_list/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}

	function activate_category_list(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> order('rank') -> select();
		for($i=1;$i<=count($result);$i++){
			$rank[] = $i;
		}
		foreach($result as $key => $val){
			$my_soft_count = $model -> table('sj_actives_soft') -> where(array('category_id' => $val['id'],'status' => 1)) -> count();
			$val['soft_count'] = $my_soft_count;
			$result[$key] = $val;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('id',$id);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_category_show(){
		$model = new Model();
		$active_id = $_GET['active_id'];
		$rank_result = $model -> table('sj_actives_category') -> where(array('active_id' => $active_id,'status' => 1)) -> count();
		for($i=1;$i<=$rank_result;$i++){
			$rank[] = $i;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('active_id',$active_id);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('rank',$rank);
		$this -> display();
	}
	
	function add_category_do(){
		$model = new Model();
		$category_name = $_POST['category_name'];
		$pic_url = $_FILES['pic_url'];
		$rank = $_POST['rank'];
		$active_id = $_POST['active_id'];
		if($this -> strlen_az($category_name) > 20 || !$category_name){
			$this -> error("请填写10字以内的分类名称");
		}
		$have_been = $model -> table('sj_actives_category') -> where(array('category_name' => $category_name,'active_id' => $active_id,'status' => 1)) -> select();
		if($have_been){
			$this -> error("该活动已存在此分类名称");
		}
		if($pic_url){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'pic_url' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 102,
					'img_p_height' => 35,
					'img_p_size' => 1024*30,
				),
			));
			$lists=$this->_uploadapk(0, $config);
			$my_pic = $lists['image'][0]['url'];
		}else{
			$this -> error("请上传分类图片");
		}
		$rank_where['_string'] = "active_id = {$active_id} and rank >= {$rank} and status = 1";
		$rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
		if($rank_result){
			foreach($rank_result as $key => $val){
				$rank_data = array(
					'rank' => $val['rank'] + 1
				);
				$rank_where['_string'] = "id = {$val['id']}";
				$change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
			}
		}
		$data = array(
			'category_name' => $category_name,
			'rank' => $rank,
			'pic_url' => $my_pic,
			'create_tm' => time(),
			'update_tm' => time(),
			'active_id' => $active_id,
			'status' => 1
		);
		$result = $model -> table('sj_actives_category') -> add($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已添加活动id为{$active_id}的分类，id为{$result}",'sj_actives_category',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_category_list/id/{$active_id}/p/{$p}/lr/{$lr}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_category_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
		$count = $model -> table('sj_actives_category') -> where(array('active_id' => $result[0]['active_id'],'status' => 1)) -> count();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		
		$this -> assign('rank',$rank);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_category_do(){
		$model = new Model();
		$id = $_POST['id'];
		$category_name = $_POST['category_name'];
		$pic_url = $_FILES['pic_url'];
		$rank = $_POST['rank'];
		if($this -> strlen_az($category_name) > 20 || !$category_name){
			$this -> error("请填写10字以内的分类名称");
		}
		$where_have['_string'] = "category_name = {$category_name} and active_id = {$active_id} and status = 1 and id != {$id}";
		$have_been = $model -> table('sj_actives_category') -> where(array('category_name' => $category_name,'active_id' => $active_id,'status' => 1)) -> select();
		if($have_been){
			$this -> error("该活动已存在此分类名称");
		}
		if($_FILES['pic_url']['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'pic_url' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 102,
					'img_p_height' => 35,
					'img_p_size' => 1024*30,
				),
			));
			$lists=$this->_uploadapk(0, $config);
			$my_pic = $lists['image'][0]['url'];
		}
		$have_been = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
		if($rank != $have_been[0]['rank']){
			if($rank < $have_been[0]['rank']){
				$rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank >= {$rank}  and rank < {$have_been[0]['rank']} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] + 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
					}
				}
			}elseif($rank > $have_been[0]['rank']){
				$rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank > {$have_been[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] - 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
					}
				}
			}
		}
		if($_FILES['pic_url']['size']){
			$data = array(
				'category_name' => $category_name,
				'rank' => $rank,
				'pic_url' => $my_pic,
				'update_tm' => time()
			);
		}else{
			$data = array(
				'category_name' => $category_name,
				'rank' => $rank,
				'update_tm' => time()
			);
		}
		$log_result = $this -> logcheck(array('id' => $id),'sj_actives_category',$data,$model);
		$result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> save($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已编辑活动id为{$have_been[0]['active_id']}的分类，id为{$id}".$log_result,'sj_actives_category',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_category_list/id/{$have_been[0]['active_id']}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	}
	
	function change_category_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$have_been = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
		if($rank != $have_been[0]['rank']){
			if($rank < $have_been[0]['rank']){
				$rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank >= {$rank}  and rank < {$have_been[0]['rank']} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] + 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
					}
				}
			}elseif($rank > $have_been[0]['rank']){
				$rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank > {$have_been[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] - 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
					}
				}
			}
		}
		$log_result = $this -> logcheck(array('id' => $id),'sj_actives_category',array('rank' => $rank),$model);
		$my_result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> save(array('rank' => $rank));
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		if($my_result){
			$this -> writelog("已编辑id为{$id}的活动分类".$log_result,'sj_actives_category',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_category_list/id/{$have_been[0]['active_id']}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_category(){
		$model = new Model();
		$id = $_GET['id'];
		$my_result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
		$where_rank['_string'] = "rank > {$my_result[0]['rank']} and active_id = {$my_result[0]['active_id']} and status = 1";
		$rank_result = $model -> table('sj_actives_category') -> where($where_rank) -> select();
		
		foreach($rank_result as $key => $val){
			$rank_data = array(
				'rank' => $val['rank'] - 1
			);
			$rank_result = $model -> table('sj_actives_category') -> where(array('id' => $val['id'])) -> save($rank_data);
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$soft_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $id)) -> save(array('status' => 0));
			$this -> writelog("已删除id为{$id}的活动分类",'sj_actives_category',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_category_list/id/{$my_result[0]['active_id']}/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function config_comment_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> select();
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('id',$id);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function config_comment_do(){
		$model = new Model();
		$id = $_POST['id'];
		$winning_comment = $_POST['winning_comment'];
		if($this -> strlen_az($winning_comment) > 24 || !$winning_comment){
			$this -> error("请填写12个字以内的获奖机会文案");
		}
		$button_comment = $_POST['button_comment'];
		if($this -> strlen_az($button_comment) > 30 || !$button_comment){
			$this -> error("请填写15个字以内的主页面按钮名称");
		}
		$download_comment = $_POST['download_comment'];
		if($this -> strlen_az($download_comment) > 100 || !$download_comment){
			$this -> error("请填写50个字以内的下载后文案");
		}
		$data = array(
			'winning_comment' => $winning_comment,
			'button_comment' => $button_comment,
			'download_comment' => $download_comment,
			'ap_utm' => time()
		);
		$log_result = $this -> logcheck(array('ap_id' => $id),'sj_activity_page',$data,$model);
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已编辑id为{$id}的活动的文案配置".$log_result,'sj_actives_category',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_category_list/id/{$id}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function soft_list(){
		$model = new Model();
		$category_id = $_GET['category_id'];
		$my_category = $model -> table('sj_actives_category') -> where(array('id' => $category_id)) -> select();
		$result = $model -> table('sj_actives_soft') -> where(array('category_id' => $category_id,'status' => 1)) -> order('rank') -> select();
		for($i=1;$i<=count($result);$i++){
			$rank[] = $i;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('id',$my_category[0]['active_id']);
		$this -> assign('category_id',$category_id);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_soft_show(){
		$model = new Model();
		$category_id = $_GET['category_id'];
		$result = $model -> table('sj_actives_soft') -> where(array('category_id' => $category_id,'status' => 1)) -> count();
		for($i=1;$i<=$result;$i++){
			$rank[] = $i;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('category_id',$category_id);
		$this -> assign('rank',$rank);
		$this -> assign('category_id',$category_id);
		$this -> display();
	}
	
	function add_soft_do(){
		$model = new Model();
		$category_id = $_POST['category_id'];
		$soft_name = trim($_POST['soft_name']);
		$package = trim($_POST['package']);
		$rank = $_POST['rank'];
		$recomment = $_POST['recomment'];
		$award_recomment = $_POST['award_recomment'];
		if($this -> strlen_az($soft_name) > 20 || !$soft_name){
			$this -> error("请填写10字以内的软件名称");
		}
		//该活动所拥有的分类
		$my_activate = $model -> table('sj_actives_category') -> where(array('id' => $category_id,'status' => 1)) -> select();
		$the_category_id = $model -> table('sj_actives_category') -> where(array('active_id' => $my_activate[0]['active_id'],'status' => 1)) -> select();
		
		foreach($the_category_id as $key => $val){
			$category_id_str_go .= $val['id'].',';
		}
		$category_id_str = substr($category_id_str_go,0,-1);
		$active_have_been_where['_string'] = "soft_name = '{$soft_name}' and category_id in ({$category_id_str}) and status = 1";
		$active_have_been = $model -> table('sj_actives_soft') -> where($active_have_been_where) -> select();
		
		if($active_have_been){
			$this -> error("该活动已存在此软件名称");
		}
		
		$have_been = $model -> table('sj_actives_soft') -> where(array('soft_name' => $soft_name,'category_id' => $category_id,'status' => 1)) -> select();
		
		if($have_been){
			$this -> error("该软件名称在此分类中已存在");
		}
		$my_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$my_package){
			$this -> error("该软件包名不存在");
		}
		
		$active_have_been_where_package['_string'] = "package = '{$package}' and category_id in ({$category_id_str}) and status = 1";
		$active_have_been_package = $model -> table('sj_actives_soft') -> where($active_have_been_where_package) -> select();
		if($active_have_been_package){
			$this -> error("该活动已存在此软件包名");
		}
		
		$have_been_package = $model -> table('sj_actives_soft') -> where(array('package' => $package,'category_id' => $category_id,'status' => 1)) -> select();
		if($have_been_package){
			$this -> error("该软件包名在此分类中已存在");
		}
		if($this -> strlen_az($recomment) > 30){
			$this -> error("请填写15字以内的一句话推荐");
		}
		if($this -> strlen_az($award_recomment) > 200){
			$this -> error("请填写100字以内的奖品介绍");
		}
		$rank_where['_string'] = "category_id = {$category_id} and rank >= {$rank} and status = 1";
		$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
		if($rank_result){
			foreach($rank_result as $key => $val){
				$rank_data = array(
					'rank' => $val['rank'] + 1
				);
				$change_result = $model -> table('sj_actives_soft') -> where(array('id' => $val['id'])) -> save($rank_data);
			}
		}
		$data = array(
			'soft_name' => $soft_name,
			'package' => $package,
			'page_id' => $my_activate[0]['active_id'],
			'rank' => $rank,
			'recomment' => $recomment,
			'award_recomment' => $award_recomment,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'category_id' => $category_id
		);
		$result = $model -> table('sj_actives_soft') -> add($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		
		if($result){
			$this -> writelog("已添加分类id为{$category_id}的软件，id为{$result}",'sj_actives_soft',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/soft_list/category_id/{$category_id}/p/{$p}/lr/{$lr}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_soft_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		$rank_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $result[0]['category_id'],'status' => 1)) -> count();
		for($i=1;$i<=$rank_result;$i++){
			$rank[] = $i;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_soft_do(){
		$model = new Model();
		$id = $_POST['id'];
		$soft_name = trim($_POST['soft_name']);
		$package = trim($_POST['package']);
		$rank = $_POST['rank'];
		$recomment = $_POST['recomment'];
		$award_recomment = $_POST['award_recomment'];
		$my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		
		//该活动所拥有的分类
		$my_activate = $model -> table('sj_actives_category') -> where(array('id' => $my_result[0]['category_id'],'status' => 1)) -> select();
		$the_category_id = $model -> table('sj_actives_category') -> where(array('active_id' => $my_activate[0]['active_id'],'status' => 1)) -> select();
		
		foreach($the_category_id as $key => $val){
			$category_id_str_go .= $val['id'].',';
		}
		$category_id_str = substr($category_id_str_go,0,-1);
		$active_have_been_where['_string'] = "soft_name = '{$soft_name}' and category_id in ({$category_id_str}) and status = 1 and id != {$id}";
		$active_have_been = $model -> table('sj_actives_soft') -> where($active_have_been_where) -> select();
		
		if($active_have_been){
			$this -> error("该活动已存在此软件名称");
		}
		
		$where_have['_string'] = "soft_name = {$soft_name} and category_id = {$my_result[0]['category_id']} and status = 1 and id != {$id}";
		$have_been = $model -> table('sj_actives_soft') -> where($where_have) -> select();
		if($have_been){
			$this -> error("该软件名在此分类中已存在");
		}
		if($this -> strlen_az($soft_name) > 20 || !$soft_name){
			$this -> error("请填写10字以内的软件名称");
		}
		$have_been = $model -> table('sj_actives_soft') -> where(array('soft_name' => $soft_name,'category_id' => $category_id,'status' => 1)) -> select();
		if($have_been){
			$this -> error("该软件名称在此分类中已存在");
		}
		$my_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$my_package){
			$this -> error("该软件包名不存在");
		}
		
		$active_have_been_where_package['_string'] = "package = '{$package}' and category_id in ({$category_id_str}) and status = 1 and id != {$id}";
		$active_have_been_package = $model -> table('sj_actives_soft') -> where($active_have_been_where_package) -> select();
		if($active_have_been_package){
			$this -> error("该活动已存在此软件包名");
		}
		
		$where_have_package['_string'] = "package = {$package} and category_id = {$category_id} and status = 1 and id != {$id}";
		$have_been_package = $model -> table('sj_actives_soft') -> where($where_have_package) -> select();
		if($have_been_package){
			$this -> error("该软件包名在此分类中已存在");
		}
		if($this -> strlen_az($recomment) > 30){
			$this -> error("请填写15字以内的一句话推荐");
		}
		if($this -> strlen_az($award_recomment) > 200){
			$this -> error("请填写100字以内的奖品介绍");
		}
		if($rank != $my_result[0]['rank']){
			if($rank < $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank >= {$rank}  and rank < {$my_result[0]['rank']} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] + 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}elseif($rank > $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] - 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}
		}
		
		$data = array(
			'soft_name' => $soft_name,
			'package' => $package,
			'rank' => $rank,
			'recomment' => $recomment,
			'award_recomment' => $award_recomment,
			'update_tm' => time(),
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_actives_soft',$data,$model);
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已编辑id为{$id}的活动分类软件".$log_result,'sj_actives_soft',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/soft_list/category_id/{$my_result[0]['category_id']}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function change_soft_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		if($rank != $my_result[0]['rank']){
			if($rank < $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank >= {$rank}  and rank < {$my_result[0]['rank']} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] + 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}elseif($rank > $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] - 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$log_result = $this -> logcheck(array('id' => $id),'sj_actives_soft',array('rank' => $rank),$model);
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save(array('rank' => $rank));
		if($result){
			$this -> writelog("已编辑id为{$id}的活动分类软件".$log_result,'sj_actives_soft',$id,__ACTION__ ,'','edit');
			$this -> assign("jumpUrl","/index.php/Sendnum/Activate/soft_list/category_id/{$my_result[0]['category_id']}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_soft(){
		$model = new Model();
		$id = $_GET['id'];
		$my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and status = 1";
		$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
		foreach($rank_result as $key => $val){
			$rank_data = array(
				'rank' => $val['rank'] - 1
			);
			$rank_result = $model -> table('sj_actives_soft') -> where(array('id' => $val['id'])) -> save($rank_data);
		}
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save(array('status' => 0));
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		if($result){
			$this -> writelog("已删除id为{$id}活动分类软件",'sj_actives_soft',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Activate/soft_list/category_id/{$my_result[0]['category_id']}/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	
	}
	
	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}
	
	function produce_page(){
		$model = new Model();
	
		 $id = $_GET['id'];
		/* $exit_file = "/data/www/wwwroot/new-wwwroot/m.goapk.com/activity/activity_page/{$id}.html";
		if(file_exists($exit_file)){
			unlink($exit_file);
		} */
		
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> select();
		/*if(!$result[0]['winning_comment']){
			$result[0]['winning_comment'] ="您共有X次获奖机会";
		}
		$result[0]['winning_comment'] = str_replace('X','<span id="times"></span>',$result[0]['winning_comment']);
		if(!$result[0]['button_comment']){
			$result[0]['button_comment'] = "提交并一键下载";
		}
		if(!$result[0]['download_comment']){
			$result[0]['download_comment'] = '恭喜，您已成功参与该活动！ 请前往"下载"页面确保成功下载并安装活动应用，即可获得X次获奖机会。';
		}
		$result[0]['download_comment'] = str_replace('X','<span id="packages"></span>',$result[0]['download_comment']);
		$category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $result[0]['ap_id'],'status' => 1)) -> order('rank') ->select();
		foreach($category_result as $key => $val){
			$soft_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $val['id'],'status' => 1)) -> order('rank') -> select();
			if($soft_result){
				$category_soft_count[$key] = 1;
			}
			foreach($soft_result as $k => $v){
				$soft_have_result = $model -> table('sj_soft') -> where(array('package' => $v['package'],'status' => 1,'hide' => 1)) -> order('softid DESC') -> limit('0,1') -> select();
				$soft_size = $model -> table('sj_soft_file') -> where(array('softid' => $soft_have_result[0]['softid'],'package_status' => 1)) -> select();
				$v['soft_size'] = sprintf('%.1f',$soft_size[0]['filesize']/1024/1024);
				$v['iconurl_72'] = $soft_size[0]['iconurl_72'];
				$v['softid'] = $soft_have_result[0]['softid'];
				$soft_result[$k] = $v;
			}
		
			$val['soft_result'] = $soft_result;
			$category_result[$key] = $val;
		}
		$my_category_soft = array_sum($category_soft_count);
		$this -> assign('my_category_soft',$my_category_soft);
		$this -> assign('result',$result);
		$this -> assign('category_result',$category_result);
		//生成下载后页面
		if($result[0]['ap_type'] == 1){
			$download_title = dirname(__FILE__)."./../../../Tpl/default/Sendnum/Activate/downloaded_".$_GET['id'].time().'.html'; 
			$download_html = 'downloaded'; //down_$GET['id'].html*/
			$download_page = 'downloaded_'.$_GET['id'].'.html';
			/* if(file_put_contents($download_title,$this -> fetch($download_html))){
				if(copy($download_title,ACTIVITY_PAGE . $download_page)){ */
					$data = array(
						'ap_download_link' => $download_page,
					);
					$my_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);
					/* unlink($download_title);
				}else{
					$this -> error("移动页面失败");
				}
			}else{
				$this -> error("写入页面失败");
			}
		} */
		
		if($result[0]['ap_type'] == 1){
			//$page = 'index_'.$_GET['id'].time().'.html';
			$page = "/activaties_".$_GET['id'].".html";
			/* $my_title = dirname(__FILE__)."./../../../Tpl/default/Sendnum/Activate/index_".$_GET['id'].time().'.html';
			$my_html = 'index'; */
		}elseif($result[0]['ap_type'] == 6){
			$page = "/lottery.html";
		}else{
			//$page = 'preheat_'.$_GET['id'].time().'.html';
			$page = "/activaties_".$_GET['id'].".html";
			/* $my_title = dirname(__FILE__)."./../../../Tpl/default/Sendnum/Activate/preheat_".$_GET['id'].time().'.html';
			$my_html = 'preheat'; */
		}

		$this -> assign('download_html',$download_page);
		/* if(file_put_contents($my_title,$this -> fetch($my_html))){
			if(
				copy($my_title,ACTIVITY_PAGE . $page)
			){ */
		$data = array(
			'ap_link' => $page,
			'ap_ptm' => time()
		);
		$log_result = $this -> logcheck(array('ap_id'=>$id),'sj_activity_page',$data,$model);
		$my_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);
				/* unlink($my_title);
			}else{
				$this -> error("移动页面失败");
			}
		}else{
			$this -> error("写入页面失败");
		} */
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		if($my_result){
			$this -> writelog("已生成id为{$id}的多软件综合活动页面,{$log_result}",'sj_activity_page',$id,__ACTION__ ,'','add');
			echo "<script>alert('成功生成页面，请复制活动链接使用！');
			location.href='/index.php/Sendnum/Activate/activate_list/p/{$p}/lr/{$lr}';
			</script>";
		}else{
			$this -> error("生成页面失败");
		}
	}
	
	
	

}
