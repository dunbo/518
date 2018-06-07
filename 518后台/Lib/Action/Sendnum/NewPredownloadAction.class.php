<?php
//新预下载通用模板
class NewPredownloadAction extends CommonAction {

	function activity_list(){
		$model = D('sendNum.Activity');	
		$where = array(
			'activate_type' => 9,
			'status' => 1
		);
		$this->check_where($where, 'ap_name', 'isset', 'like');
		$this->check_where($where, 'ap_id');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total,$Page) = $model -> get_activity_page($where,$limit);
		$this -> assign("page", $Page->show());
		$this -> assign('result',$result);
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/NewPredownload/activity_list'));	
		$this -> display("");
	}

	//V6.4 礼包管理
	function bespoke_gift(){
		$activity_model = D('Sj.CoActivity');
		$model = M('');
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$where = array('status'=>1);
		if(!empty($_GET['s_name'])){
			$where['name'] = array('exp'," like ('%{$_GET['s_name']}%')");
			$this->assign('s_name',$_GET['s_name']);
		}
		$where['ap_id'] = $_GET['id'];
		$where['gtype'] = 1;
		if($_GET['lijuan']){ $where['gtype'] = 2; } 
		
		$activity = $model->table('sj_game_subscriber')->where(array('ap_id'=>$_GET['id'],'status'=>1))->field('start_tm,end_tm')->find();
		if($activity['start_tm']<time()&&$activity['end_tm']>time()){
			$this->assign('is_running',1);
		}
		$total = $activity_model->table('gm_bespoke_gift') -> where($where) -> count();


		$res = $activity_model->table('gm_bespoke_gift')->where($where)->order('rank asc,update_tm desc')->select();
		$id_arr = array();
		foreach($res as $k=>$v){
			$v['last_num'] = $activity_model->table('gm_bespoke_gift_code')->where(array('status'=>0,'pid'=>$v['id']))->count();
			$res[$k] = $v;
		}

		//echo $activity_model->getLastSql();
		$this->assign('res',$res);
		$this->assign('id',$_GET['id']);
		$this->display();
	}

	function del_bespoke_gift(){
		$id = $_GET['id'];
		$activity_model = D('Sj.CoActivity');
		$res = $activity_model->table('gm_bespoke_gift')->where(array('id'=>$id))->save(array('status'=>0));
		if($res){
		    if($_GET['lijuan']){
		      $this->writelog("活动管理-活动页面生成：删除id为{$id}的通用新预约活动礼券", 'gm_bespoke_gift', $id,__ACTION__ ,'','del');
		    }else{
		        $this->writelog("活动管理-活动页面生成：删除id为{$id}的通用新预约活动礼包", 'gm_bespoke_gift', $id,__ACTION__ ,'','del');
		    }
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

	function save_bespoke_gift(){
		$activity_model = D('Sj.CoActivity');
		$ap_id = $_GET['ap_id'];
		if(isset($_GET['rank'])){
			if(!is_numeric($_GET['rank'])||!is_int($_GET['rank']+0)||$_GET['rank']<=0){
				$this->error('排序为大于等于0的整数'.$_GET['rank']);
			}
			$re = $activity_model->table('gm_bespoke_gift')->where("id = '{$_GET['id']}'")->save(array('rank'=>$_GET['rank'],'update_tm'=>time()));
			if($re){
				$this->success('修改排序成功');
			}else{
				$this->error('修改排序失败');
			}
		}
		if(!empty($_GET['id'])){
			$res = $activity_model->table('gm_bespoke_gift')->where(array('id'=>$_GET['id']))->find();
			$this->assign('res',$res);
		}
		if($_POST){
			$id = $_POST['id'];
			$ap_id = $_POST['ap_id'];
			$where = array(
				'status' => 1,
			    'gtype'=>1,
				'name' => $_POST['name']
			);
			if($id){
				$where['id'] = array('exp'," != {$id}");
			}
			if($_POST['lijuan']){ $where['gtype']=2;} //礼券处理
			$has_info = $activity_model->table('gm_bespoke_gift')->where($where)->find();

			if($has_info){
			    if($_POST['lijuan']){ //礼券处理
			        $this->error('已存在此礼券名称');
			    }else{
			        $this->error('已存在此礼包名称');
			    }
			}
			if($_POST['lijuan']){ //礼券处理
        		$where = array(
        		    'status' => 1,			    
        		    'gift_file'=>$_POST['is_give_giftid']
        		);
        		if($id){
        		    $where['id'] = array('exp'," != {$id}");
        		}
        		$has_info = $activity_model->table('gm_bespoke_gift')->where($where)->find();
        		if($has_info){  //检查重复的礼券id
        		    $this->error('已存在此礼券id,以使用');
        		}
        		
        		if($_POST['is_give_gift']==1){
        		    $where = array(
        		        'status' => 1,
        		        'ap_id'=>$ap_id,
        		        'is_give_gift'=>1,
        		        'gtype'=>2
        		    );
        		    if($id){
        		        $where['id'] = array('exp'," != {$id}");
        		    }
        		    $has_info = $activity_model->table('gm_bespoke_gift')->where($where)->find();
        		    if($has_info){ //检查礼券赠送
        		        $this->error('已有礼券被设为预约成功自动赠送');
        		    }
        		}
			}
			
			$save_data = array();

			$save_data['name'] = $_POST['name'];
			$save_data['intro'] = $_POST['intro'];
			$save_data['is_give_gift'] = $_POST['is_give_gift'];
			$save_data['ap_id'] = $ap_id;
			$save_data['update_tm'] = time();
			
			if(!$id){
				//添加
				if($_POST['lijuan']){ //礼券处理
				    $save_data['gtype'] = 2;
				    $save_data['gift_file'] = trim($_POST['is_give_giftid']);
				    $save_data['num'] = $_POST['num'];
				    $save_data['run_num'] = $_POST['num'];
				}
				
			    $save_data['add_tm'] = time();
				$save_data['rank'] = 100;
				$res = $activity_model->table('gm_bespoke_gift')->add($save_data);
				if($res){
				    if($_POST['lijuan']){
				        $this->writelog("活动管理-活动页面生成：已添加id为{$res}的通用新预约活动礼券", 'gm_bespoke_gift', $res,__ACTION__ ,'','add');
				        $this->assign('jumpUrl', "/index.php/Sendnum/NewPredownload/bespoke_gift/id/{$ap_id}/lijuan/{$_POST['lijuan']}");
				    }else{
				        $this->writelog("活动管理-活动页面生成：已添加id为{$res}的通用新预约活动礼包", 'gm_bespoke_gift', $res,__ACTION__ ,'','add');
				    }
					$this->success('添加成功');
				}else{
					$this->error('添加失败');
				}
				
				
			}else{
			    if($_POST['lijuan']){ //礼券处理
			        $save_data['gtype'] = 2;
			        $save_data['gift_file'] = trim($_POST['is_give_giftid']);
			        //$save_data['num'] = $_POST['num'];
			    }
				$res = $activity_model->table('gm_bespoke_gift')->where("id = '{$id}'")->save($save_data);
				if($res){
				    if($_POST['lijuan']){
				        $this->writelog("活动管理-活动页面生成：编辑id为{$id}的通用新预约活动礼券", 'gm_bespoke_gift', $res,__ACTION__ ,'','edit');
				        $this->assign('jumpUrl', "/index.php/Sendnum/NewPredownload/bespoke_gift/id/{$ap_id}/lijuan/{$_POST['lijuan']}");
				    }else{
				        $this->writelog("活动管理-活动页面生成：编辑id为{$id}的通用新预约活动礼包", 'gm_bespoke_gift', $res,__ACTION__ ,'','edit');
				    }
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			}
		}
		$this->assign('ap_id',$ap_id);
		$this->display();
	}
	function add_activity_show(){
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('ap_type',$_GET['ap_type']);
		if($_GET['ap_type']==2){
			$this->display('new_activity');
		}else{
			$this -> display();
		}
	}

	function add_gift_code(){
		$activity_model = D('Sj.CoActivity');
		$result = $activity_model -> table('gm_bespoke_gift') -> where(array('id' => $_GET['pid'])) ->find();
		$this -> assign('prize_count',$result['num']);
		if($_POST){
			$this->add_gift_code_do();
		}
		$this->assign('pid',$_GET['pid']);
		$this->assign('ap_id',$_GET['ap_id']);
		$this->assign('lijuan',$_GET['lijuan']); //礼券
		$this->display();
	}

	function add_gift_code_do(){
		ini_set('memory_limit','256M');
		$activity_model = D('Sj.CoActivity');
		$model = new Model();
		$now = time();
		$id = $_POST['id'];
		$my_result = $activity_model -> table('gm_bespoke_gift') -> where(array('id' => $id,'status' => 1)) -> find();
		//$activity_result = $model -> table('sj_activity') -> where(array('id' => $my_result['aid'])) -> find();
		//if($activity_result['start_tm'] < time()){
		//	$this -> error("活动已开始不能上传虚拟奖品");
		//}
		//if($my_result['type'] == 2){
		$dir = '/tmp/audit_check/';
		if(!file_exists($dir)) mkdir($dir);
		$lock_file = $dir."add_gift_code_{$my_result['id']}.lock";
		if(file_exists($lock_file)){
			$time = file_get_contents($lock_file);
			if ($now - $time < 150) {
				$this->error("已经在执行上传动作，请三分钟后操作");
			}
		}
		file_put_contents($lock_file,$now);
			$have_result = $activity_model -> table('gm_bespoke_gift_code') -> where(array('pid' => $my_result['id'])) -> delete();

			$code_file = $_FILES['my_code'];
			if($code_file['size']){
				$file_type = explode('.', $code_file['name']);
				if ($file_type[1] != 'csv') {
					$this->error("请上传csv格式文件");
				}
				$file_handle = fopen($code_file['tmp_name'], "r");
				$shili = fopen($code_file['tmp_name'], "r");
				$str = '';
				while (!feof($shili)) {
					$shi = fgets($shili, 1024);
					$a = explode(',', $shi);
					if ($a[3]) {
						$this->error("激活码文件格式错误");
					}
					$str .= $shi . ",";
				}
				$str_arr = explode("\r\n", $str);
				$my_all_code = array();
				$newarr = array();
				$error = '礼包重复数据：';
				$csv_error = '';
				foreach($str_arr as $key => $val){
					if(empty($val)){
						continue;
					}
					if($key==0){
						$num = $val;
					}else{
						list($a,$num) = explode(',',$val);
					}

					if(!$num){
						continue;
					}
					$my_code = array($num);
					if($newarr[$num]){
						$csv_error .= $num."\n";
					}else{
						$newarr[$num] = 1;
					}
					$my_all_code[$key] = $my_code;
				}

				if($csv_error != ''){
					$csv_error = mb_convert_encoding($csv_error,"UTF-8","GBK");
					$this->error($error.$csv_error);
				}
				//上传礼包
				$date = date("Ym/d/");
				$dir_img = UPLOAD_PATH . '/gift/'.$date;
				if(!is_dir($dir_img)) {
					if(!mkdir($dir_img,0777,true)) {
						//创建gift目录{$dir_img}失败
						$this -> error("创建gift目录{$dir_img}失败");
					}
				}
				list($msec,$sec) = explode(' ',microtime());
				$types = 'csv';
				$msec = substr($msec,2);
				$dst = $dir_img.'gift'.$my_result['aid'].'_'.$my_result['pid'].'_'.$msec.'.'.$types;
				if(move_uploaded_file($_FILES['my_code']['tmp_name'],$dst)) {
					$path = str_replace(UPLOAD_PATH,'',$dst);
					$map = array( 'gift_file' => $path );
					$num = count($my_all_code);
					$map['num'] = $num;
					$activity_model -> table('gm_bespoke_gift') -> where(array('id' => $id,'status' => 1)) -> save($map);
				}
			}else{
				$this -> error("请上传虚拟奖品卡密等信息");
			}
			$sql = "insert into gm_bespoke_gift_code (`first_text`,`pid`,`status`,`ap_id`,`update_tm`,`create_tm`) values ";
			$sql_srt = '';
			$i = 1;
			$virtual_result = array();

			foreach($my_all_code as $key => $val){
				if($val[0] || $val[1]){
					if($val[0] != '' && $val[0] != '卡号'){
						if(!strstr($val[0],"+")){
							$code_data['first_text'] = $val[0];
							$valss['first_text'] = $val[0];
						}else{
							$this -> error("上传csv包含+号");
						}
					}

					$virtual_result[] = $valss;
					$code_data['pid'] = $my_result['pid'];
					$code_data['aid'] = $my_result['aid'];
					$code_data['create_tm'] = time();
					$code_data['update_tm'] = time();
					$code_data['status'] = 0;
					$sql_srt .="('".$code_data['first_text']."','".$my_result['id']."',0,'".$my_result['ap_id']."','".$code_data['update_tm']."','".$code_data['create_tm']."'),";
					if($i%5000 == 0){
						$sql_srt = substr($sql_srt,0,-1);
						$code_result = $activity_model->query($sql.$sql_srt);
						$sql_srt = '';
					}
					$i++;
				}
			}
			$sql_srt = substr($sql_srt,0,-1);
			$code_result = $activity_model->query($sql.$sql_srt);

			$package_result = $activity_model -> table('gm_bespoke_gift_code') -> where(array('pid' => $id,'status' => 0)) -> field('first_text') -> select();
			$redis = new redis();
			$redis->connect(C('LOTTERY_REDIS_HOST'),C('LOTTERY_REDIS_PORT'));

			//$virtual_result = array_slice($virtual_result,0,5500);
			$i = 1;
			$virtual_prize = "bespoke_gift:virtual_{$id}";
			$redis -> delete($virtual_prize);

			$param = '';
			foreach($package_result as $k=>$v){
				$vals = json_encode($v);
				$param .= $vals."','";
				if($i%5000 == 0){
					$params = substr($param,0,-3);
					$cmd = "\$redis->rpush('{$virtual_prize}', '{$params}');";
					eval($cmd);
					$param = '';
				}
				$i++;
			}
			if($param){
				$params = substr($param,0,-3);
				$cmd = "\$redis->rpush('{$virtual_prize}', '{$params}');";
				eval($cmd);
			}
			unlink($lock_file);
			$this -> assign('jumpUrl',"/index.php/Sendnum/NewPredownload/bespoke_gift/id/{$my_result['ap_id']}");
			$this -> success("添加成功");
		//}
	}

    function pic_isok($pic){
			$high_wd = getimagesize($pic['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
                        return array($width_go_hg,$height_go_hg);
        }


	//V6.4新增
	function new_activity_do(){
		$data = array();
		$id = $_POST['id'];
		$this->file_check($id);

		list($soft,$del_pic) = $this->data_check($id);
		$data['get_lottery_type'] = $soft['get_lottery_type'];
		$data['last_lottery_desc'] = $soft['last_lottery_desc'];
		$file_data = $this->file_upload();
		$data['ap_type'] = 2;
		$data['ap_name'] = $_POST['ap_name'];
		$data['show_award'] = $_POST['show_award'];
		$data['second_text_color'] = $_POST['second_text_color'];
		$data['first_text_color'] = $_POST['second_text_color1'];
		$data['third_text_color'] = $_POST['second_text_color1'];
		$data['submit_button_color'] = $_POST['submit_button_color'];
		$data['bg_color'] = $_POST['bg_color'];
		$data['button_text_color'] = $_POST['button_text_color'];
		$data['button_color'] = $_POST['button_color'];
		$data['info_color'] = $_POST['info_color'];
		$data['end_tm'] = strtotime($_POST['end_tm']);
		$data['marquee_text_color'] = $_POST['marquee_text_color'];

		$data['no_prize_pic'] = $_POST['no_prize_pic'];
		$data['no_prize_text'] = $_POST['no_prize_text'];
		$data['prize_back'] = 1;
		$data['my_prize_button'] = 1;
		$arr = array('prize_bg_pic','no_winning_marquee','my_prize_button_color','my_prize_text_color','draw_button_text','is_telephone');
		foreach($arr as $k=>$v){
			if($_POST[$v]=='on'){
				$data[$v] = 1;
			}else{
				$data[$v] = 0;
			}
		}
		$data['ap_notice'] = $_POST['ap_notice'];
		$ap_desc = trim($_POST['ap_desc']);
		$ap_desc = htmlspecialchars($_POST['ap_desc']);
		$ap_desc = str_replace("\\","",$ap_desc);
		$data['ap_desc'] = $ap_desc;
		//$data['change_switch'] = $_POST['change_switch'];
		$data['lottery_style'] = $_POST['lottery_style'];
		$data['sudoku_color'] = $_POST['sudoku_color'];
		$data['version_code'] = $_POST['version_code'];
		$data['free_day_switch'] = $_POST['free_day_switch'];
		$data['is_repeat'] = $_POST['is_repeat'];
		$data['lost_no_desc'] = $_POST['lost_no_desc'];
		$data['lose_yes_desc'] = $_POST['lose_yes_desc'];
		$data['is_warning'] = $_POST['is_warning'];
		$data['alert_color'] = $_POST['alert_color'];
		$data['alert_button_color'] = $_POST['alert_button_color'];
		$data['yes_marquee'] = $_POST['yes_marquee'];
		$no_marquee = trim($_POST['no_marquee']);
		$no_marquee = htmlspecialchars($no_marquee);
		$no_marquee = str_replace("\\","",$no_marquee);
		$data['no_marquee'] = $no_marquee;
		$data['title'] = $_POST['title'];
		$data['download_comment'] = $_POST['game_name'];//游戏名称
		$data['update_button_color'] = $_POST['geren_color'];
		$data['draw_font_color'] = $_POST['back_color'];
		if(!empty($data['draw_font_color'])){
			$data['mean_text_color'] = '';
		}
		$data['share_text'] = $_POST['share_text'];
		$data['prize_bg_color'] = $_POST['prize_bg_color'];
		$data['prize_text_color'] = $_POST['prize_text_color'];
		$data['share_bgcolor'] = $_POST['yuyue_color'];
		$data['again'] = $_POST['again'];
		$soft_bg = trim($_POST['soft_bg']);
		$soft_bg = htmlspecialchars($soft_bg);
		$soft_bg = str_replace("\\","",$soft_bg);
		$data['soft_bg'] = $soft_bg;
		$data['back_button'] = $_POST['back_button'];
		$data['close_button_color'] = $_POST['close_button_color'];
		$data['back_button_color'] = $_POST['back_button_color'];
		$data['rule_color'] = $_POST['rule_color'];
		$data['prize_back_color'] = $_POST['prize_back_color'];
		$data['warning_bgcolor'] = $_POST['geren_color'];
		$data['bottom_color'] = $_POST['back_color'];
		$data['share_add'] = $_POST['share_add'];
		if(!$id){
			$data['lottery_num_limit'] = 0;
			$data['gift_type'] = 1;
			$data['status'] = 1;
			$data['activate_type'] = 9;
			$data['ap_ctm'] = time();
		}
		//礼券
		$data['rank_lottery_desc_color'] = $_POST['rank_lottery_desc_color'];
		$data['rank_lottery_desc_text'] = $_POST['rank_lottery_desc_text'];
		$data['rank_lottery_desc_pic'] = $_POST['rank_lottery_desc_pic'];
		$data['text_color'] = $_POST['text_color'];
		$data['again_text_color'] = $_POST['again_text_color'];
		
		$data['ap_utm'] = time();
		$data = array_merge($data,$file_data);
		$model = M('');
		if(!$id){
			$result = $model -> table('sj_activity_page') -> add($data);
		}else{
			$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);
//			if(isset($_POST['marquee_text_color'])){
//				$this->edit_show_num($_POST['marquee_text_color'],$id);
//			}
		}
		if(!empty($data['nextpage_color'])){
			$task_client = get_task_client();
			$task_data = array();
			$task_data['url'] = $data['nextpage_color'];
			$ap_id = isset($id)?$id:$result;
			$task_data['ap_id'] = $ap_id;
			$task_client->doBackground('activity_video_change_format', json_encode($task_data));
		}
//		echo $model->getLastSql();exit();
//		var_dump($result);exit();
		if($result) {
			$param = '';
			if(!empty($_POST['p'])) $param .= "p={$_POST['p']}&";
			if(!empty($_POST['lr'])) $param .= "lr={$_POST['lr']}";
			if(!$id){
				$update_data['ap_link'] = ACTIVITY_URL . "/lottery/coactivity_lottery.php";
				$update_result = $model->table('sj_activity_page')->where(array('ap_id' => $result))->save($update_data);
				$this->insert_game($result,$soft,1);
				if(count($del_pic)>0){
					foreach($del_pic as $v){
						unlink($v);
					}
				}
				$this->writelog("已添加id为{$result}的通用新预约活动页面", 'sj_activity_page', $result,__ACTION__ ,'','add');
				$this->assign('jumpUrl', "/index.php/Sendnum/NewPredownload/activity_list?{$param}");

				$this->success("添加成功");
			}else{
				$game = array('yx_id'=>$data['get_lottery_type']);
				$model->table('sj_game_subscriber')->where(array('ap_id'=>$id))->save($game);
				$this->insert_game($id,$soft);
				$this -> writelog("已编辑id为{$id}的通用新预约活动页面", 'sj_activity_page', $id,__ACTION__ ,'','edit');
				$this -> assign('jumpUrl',"/index.php/Sendnum/NewPredownload/activity_list?{$param}");
				$this -> success("编辑成功");
			}
		}else{
			if(!$id) {
				$this->error("添加失败");
			}else{
				$this->error("编辑失败");
			}
		}
	}

	function insert_game($result,$soft,$type=''){
		$model = M('');
		$online = $model->table('sj_soft')->where(array('status'=>1,'hide'=>1,'package'=>$soft['package']))->find();
		$model->table('sj_actives_soft')->where(array('status'=>1,'page_id'=>$result,'package'=>array('neq','cn.goapk.market')))->save(array('status'=>0));
		if($online){
			$online['category_id'] = substr($online['category_id'],1,-1);
			$time = time();
			$model->query("insert into `sj_actives_soft` (`category_id`, `soft_name`, `package`, `rank`, `recomment`, `award_recomment`, `create_tm`, `update_tm`, `status`, `page_id`) values('{$online['category_id']}','{$online['softname']}','{$online['package']}','2','','','{$time}','{$time}','1',{$result})");
		}
		if($type == 1){
			$model->query("insert into `sj_actives_soft` (`category_id`, `soft_name`, `package`, `rank`, `recomment`, `award_recomment`, `create_tm`, `update_tm`, `status`, `page_id`) values('226','安智市场','cn.goapk.market','1','测试','测试测试测试测试测试测试测试','1463472638','1463472638','1',{$result})");
		}
	}

//	function edit_show_num($show_num,$id){
//		$model = M('');
//		$res = $model->table('sj_game_subscriber')->where(array('ap_id'=>$id))->save(array('show_num'=>$show_num));
//		if($res){
//			$this -> writelog("活动管理-活动页面生成：已编辑活动page_id的初始值为{$show_num}");
//		}
//	}

	function file_upload(){
		$path=UPLOAD_PATH. '/img/'.date("Ym/d/",time());

		if (!file_exists($path)) {
			mkdir($path, 0777,true);
		}
		$data = array();
		if($_FILES['cha5']['size']){
			list($msec, $sec) = explode(' ', microtime());
			$ext = strtolower(pathinfo($_FILES['cha5']['name'],PATHINFO_EXTENSION));
			$msec = substr($msec, 2);
			$video_path =   $path.'video_'. $msec . '.' . $ext;

			if (move_uploaded_file($_FILES['cha5']['tmp_name'], $video_path)) {
				$data['nextpage_color'] = str_replace(UPLOAD_PATH, '', $video_path);
			}
			unset($_FILES['cha5']);
		}
		$file_arr = array('ap_pic','rule_pic','button_pic','game_icon','cha1','cha2','cha3','cha4','cha6','cha7','mean_text_color','yuyue_pic','lottery_pic','click_lottery_pic','unclick_lottery_pic','lose_no_img','lose_yes_img','share_weixin_pic','share_other_pic','popup_bg_pic');
		$list = array();
		$img_path_p = $path;
		foreach($file_arr as $k=>$v){

			if($_FILES[$v]['size']){
				$ext = strtolower(pathinfo($_FILES[$v]['name'],PATHINFO_EXTENSION));
				list($msec, $sec) = explode(' ', microtime());
				$msec = substr($msec, 2);
				$img_path = $img_path_p. $msec . '.' . $ext;
				if (move_uploaded_file($_FILES[$v]['tmp_name'], $img_path)) {
					$list[$v]['post_name'] = $v;
					$list[$v]['url'] = str_replace(UPLOAD_PATH, '', $img_path);
					unset($_FILES[$v]);
				}else{
					$this->error('图片上传失败');
				}
			}
		}

		foreach ($list as $key => $val) {
			if ($val['post_name'] == 'popup_bg_pic') {
				$data['popup_bg_pic'] = $val['url'];
			}
			if ($val['post_name'] == 'ap_pic') {
				$data['ap_imgurl'] = $val['url'];
			}
			if ($val['post_name'] == 'rule_pic') {
				$data['rule_pic'] = $val['url'];
			}
			if ($val['post_name'] == 'button_pic') {
				$data['button_pic'] = $val['url'];
			}
			if ($val['post_name'] == 'game_icon') {
				$data['prize_back_text_color'] = $val['url'];
			}
			if ($val['post_name'] == 'cha1') {//插图1
				$data['ranking_no_pic1'] = $val['url'];
			}
			if ($val['post_name'] == 'cha2') {//插图2
				$data['ranking_pic1'] = $val['url'];
			}
			if ($val['post_name'] == 'cha3') {//插图3
				$data['uppage_color'] = $val['url'];
			}
			if ($val['post_name'] == 'cha4') {//视频缩略图
				$data['uppage'] = $val['url'];
			}
			if ($val['post_name'] == 'cha6') {//插图4
				$data['ap_download_link'] = $val['url'];
			}
			if ($val['post_name'] == 'cha7') {//插图5
				$data['telephone_warning'] = $val['url'];
			}
			if ($val['post_name'] == 'mean_text_color') {
				$data['mean_text_color'] = $val['url'];
				$data['bottom_color'] = $data['draw_font_color'] = '';
			}
			if ($val['post_name'] == 'yuyue_pic') {//预约按键图片
				$data['back_text_color'] = $val['url'];
			}
			if ($val['post_name'] == 'lottery_pic') {
				$data['lottery_pic'] = $val['url'];
			}
			if ($val['post_name'] == 'click_lottery_pic') {
				$data['click_lottery_pic'] = $val['url'];
			}
			if ($val['post_name'] == 'unclick_lottery_pic') {
				$data['unclick_lottery_pic'] = $val['url'];
			}
			if ($val['post_name'] == 'lose_no_img') {
				$data['lose_no_img'] = $val['url'];
			}
			if ($val['post_name'] == 'lose_yes_img') {
				$data['lose_yes_img'] = $val['url'];
			}
			if ($val['post_name'] == 'share_weixin_pic') {
				$data['share_weixin_pic'] = $val['url'];
			}
			if ($val['post_name'] == 'share_other_pic') {
				$data['share_other_pic'] = $val['url'];
			}
		}
		return $data;
	}

	function editor_pro(){
		//文章中图片处理,开始
		preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$_POST['no_marquee'],$matches);
		if($matches[1]) {	//有需要上传的新图片
			$pre_path = $_SERVER['DOCUMENT_ROOT'];		//web根目录
			//上传图片
			$files = array();
			$files_name = array();

			foreach($matches[1] as $key => $val) {
				$upload_model = D("Dev.Uploadfile");
				$files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
				if(file_exists($pre_path.$val)){
					$files[$files_name[$key]] = $pre_path.$val;
				}
			}
			$path=UPLOAD_PATH. '/img/'.date("Ym/d/",time());
			$arr = $del_pic = array();
			foreach($files as $f_key => $f_val) {
				list($msec, $sec) = explode(' ', microtime());
				$ext = strtolower(pathinfo($f_val,PATHINFO_EXTENSION));
				$msec = substr($msec, 2);
				$u_path =   $path. $msec . '.' . $ext;
				$cmd = "cp {$f_val} {$u_path}";
				$ret = shell_exec("${cmd}; echo $?");
				if($ret != 0){
					$this->error('预约福利图片上传失败');
				}else{
					$arr[$f_key] =  str_replace(UPLOAD_PATH, '', $u_path);
					$del_pic[] = $f_val;
				}
			}
			$new_arr = array();
			if($arr) {
				foreach($arr as $key=>$val) {
					unset($k,$new_k);
					$k = array_search($key,$files_name);
					$new_k = $matches[1][$k];

					$new_arr[$new_k] = IMGATT_HOST.$val;

				}
				//文章内容中图片路径替换
				$_POST['no_marquee'] = strtr($_POST['no_marquee'],$new_arr);
			}
		}
		return array($_POST['no_marquee'],$del_pic);
	}
	function data_check($id){
		$data = $_POST;
		$files = $_FILES;
		$return = array();
		list($data['no_marquee'],$del_pic) = $this->editor_pro();
		//no_winning_marquee my_prize_text_color
		if($data['no_winning_marquee']=='on'){
			$data['ap_desc'] = str_replace(' ','',$data['ap_desc']);
			if(empty($data['ap_desc'])){
				$this->error('活动介绍不能为空');
			}
		}
//		var_dump($data['my_prize_text_color']);
//		var_dump(preg_replace($data['no_marquee'],' ',''));
		if($data['my_prize_text_color']=='on'){
			if(empty($data['no_marquee'])){
				$this->error('预约福利内容不能为空');
			}
			$data['no_marquee'] = trim(str_replace(PHP_EOL,'',strip_tags($data['no_marquee'])));
		}
		$data['soft_bg'] = trim(str_replace(PHP_EOL,'',strip_tags($data['soft_bg'])));
		if(empty($data['soft_bg'])){
			$this->error('游戏介绍不能为空');
		}
		if($data['marquee_text_color']<=0||!is_numeric($data['marquee_text_color'])||!is_int($data['marquee_text_color']+0)){
			$this->error("预约人数初始值为大于0的整数");
		}
		if($data['no_prize_pic']<=0||!is_numeric($data['no_prize_pic'])||!is_int($data['no_prize_pic']+0)){
			$this->error("预约人数随机数为大于0的整数");
		}
		if($data['no_prize_text']<=0||!is_numeric($data['no_prize_text'])||!is_int($data['no_prize_text']+0)){
			$this->error("预约人数随机数为大于0的整数");
		}
		if($data['no_prize_pic']>=$data['no_prize_text']){
			$this->error("预约人数随机数最大值必须大于随机数最小值");
		}
		if(empty($data['second_text_color'])){
			$this->error("请选择页面通用字体颜色");
		}
		if(empty($data['second_text_color1'])){
			$this->error("请选择页面特殊字体颜色");
		}
		if(empty($data['bg_color'])){
			$this->error("请选择页面背景");
		}
		if(empty($data['button_text_color'])){
			$this->error('请选择模块标题字体颜色');
		}
		if(empty($data['button_color'])&&!$files['button_pic']['size']){
			$this->error('请选择模块标题背景颜色');
		}
		if(empty($data['info_color'])){
			$this->error('请选择模块背景颜色');
		}
		if(empty($data['submit_button_color'])){
			$this->error('请选择通用按钮颜色');
		}
		if(empty($data['game_name'])){
			$this->error('请填写游戏名称');
		}else{
			if($game_info = $this->check_game(trim($data['game_name']))){
				//返回yx_product ID 提交时使用
				$return['get_lottery_type'] = $game_info['soft_id'];
				$return['last_lottery_desc'] = $game_info['p_leixing'];
				$return['package'] = $game_info['package'];
			}else{
				$this->error('不存在此游戏或此游戏未通过');
			}
		}
		if(empty($data['end_tm'])){
			$this->error('请填写游戏上线时间');
		}


		if($data['uppage']=='on'){
			if(empty($data['back_text_color'])){
				$this->error('请选择礼包领取按钮文字颜色');
			}
			if(empty($data['back_button_color'])){
				$this->error('请选择礼包领取按钮背景颜色');
			}
			if(empty($data['rule_color'])){
				$this->error('请选择礼包查看按钮文字颜色');
			}
			if(empty($data['prize_back_color'])){
				$this->error('请选择礼包查看按钮背景颜色');
			}
		}

		if($data['show_award']==0){
			if(empty($data['prize_bg_color'])){
				$this->error('请选择底栏背景颜色');
			}
			if(empty($data['prize_text_color'])){
				$this->error('请选择预约按钮字体颜色');
			}
			if(empty($data['yuyue_color'])&&!$files['yuyue_pic']['size']){
				$this->error('请选择预约按钮颜色');
			}
			if(empty($data['geren_color'])&&empty($data['back_color'])&&!$files['mean_text_color']['size']){
				$this->error('请选择分享按钮背景颜色');
			}
			if(!empty($data['geren_color'])&&!empty($data['back_color'])&&$files['mean_text_color']['size']){
				$this->error('分享按钮颜色和图片只能选其一');
			}


			if(empty($data['share_text'])){
				$this -> error("请填写分享文案");
			}
		}

		if(!$id){
			if(!$files['share_weixin_pic']['size']){
				$this -> error("请上传分享微信图");
			}
			if(!$files['share_other_pic']['size']){
				$this -> error("请上传其他分享图");
			}
		}

		if($data['my_prize_button_color']=='on'){
			if(!$id){
				if(!$files['lose_no_img']['size']){
					$this -> error("请上传未中奖无抽奖机会提示图");
				}

				if(!$data['lost_no_desc']){
					$this -> error("请填写未中奖无抽奖机会提示语");
				}
				if(!$files['lose_yes_img']['size']){
					$this -> error("请上传未中奖有抽奖机会提示图");
				}
				if(!$data['lose_yes_desc']){
					$this -> error("请填写未中奖有抽奖机会提示语");
				}
			}
		}
		return array($return,$del_pic);
	}

	function check_game($softname){
		$model = M('');
		$where = array(
			'del' => 0,
			'type' => array('exp',' > 0'),
			'softname' => $softname
		);
		$game = $model->table('yx_product')->where($where)->field('soft_id,p_leixing,package')->find();
		return $game;
	}
	function file_check($id){
		$files = $_FILES;
		if(!$id){
			if(!$files['ap_pic']['size']){
				$this -> error("请上传页面banner图");
			}
			if(!$files['rule_pic']['size']){
				$this -> error("请上传运营位图片");
			}
			if(!$files['game_icon']['size']){
				$this -> error("请上传游戏icon");
			}
		}
		if($files['rule_pic']['size']){
			$ext = strtolower(pathinfo($files['rule_pic']['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('jpg','gif','png'))){
				$this->error("运营位图片格式为'jpg','gif','png'");
			}
			$rs = $this->pic_isok($files['rule_pic']);
			if ($rs[0] != 308 || $rs[1] != 182) {
				$this->error("运营位图片尺寸为308*182");
			}
		}
		if($files['game_icon']['size']){
			$ext = strtolower(pathinfo($files['game_icon']['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('jpg','jpeg','png'))){
				$this->error("游戏icon格式为'jpg','jpeg','png'");
			}
		}
		if($files['popup_bg_pic']['size']){
			$ext = strtolower(pathinfo($files['popup_bg_pic']['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('jpg','jpeg','png'))){
				$this->error("模块分割线图片格式为'jpg','jpeg','png'");
			}
			$rs = $this->pic_isok($files['popup_bg_pic']);
			if ($rs[0] != 13 || $rs[1] != 6) {
				$this->error("模块分割线图片格式为13*6");
			}
		}
		if($files['share_weixin_pic']['size']){
			$ext = strtolower(pathinfo($files['share_weixin_pic']['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('jpg','jpeg','png'))){
				$this->error("分享微信图格式为'jpg','jpeg','png'");
			}
			$rs = $this->pic_isok($files['share_weixin_pic']);
			if ($rs[0] != 100 || $rs[1] != 100) {
				$this->error("分享微信图格式为100*100");
			}
		}
		if($files['share_other_pic']['size']){
			$ext = strtolower(pathinfo($files['share_other_pic']['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('jpg','jpeg','png'))){
				$this->error("其他分享图格式为'jpg','jpeg','png'");
			}
			$rs = $this->pic_isok($files['share_other_pic']);
			if ($rs[0] != 200 || $rs[1] != 200) {
				$this->error("其他分享图格式为200*200");
			}
		}

		if($files['ap_pic']['size']){
			$high_wd = getimagesize($files['ap_pic']['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			if ($width_go_hg != 640 || $height_go_hg != 290) {
				$this->error("高分辨率图标大小不符合条件");
			}
			$ext = strtolower(pathinfo($files['ap_pic']['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('jpg','jpeg','png'))){
				$this->error("高分辨率图标格式为'jpg','jpeg','png'");
			}
		}
		$game_pic = array('cha1','cha2','cha3','cha4','cha6','cha7');
		$num = 0;
		foreach($game_pic as $k=>$v){
			if($files[$v]['size']){
				$ext = strtolower(pathinfo($files[$v]['name'],PATHINFO_EXTENSION));
				if(!in_array($ext,array('jpg','jpeg','png'))){
					$this->error("游戏插图格式为'jpg','jpeg','png'");
				}
				if($v!='cha4'){
					$num ++;
				}
				$rs = $this->pic_isok($files[$v]);
				if ($rs[0] != 200 || $rs[1] != 300) {
					if($k==3){
						$error = '游戏视频缩略图尺寸不符合条件';
					}else{
						$num = $k+1;
						$error = "游戏插图{$num}图标尺寸不符合条件,请上传200*300的图片";
					}
					$this->error($error);
				}
			}
		}
		if($num < 3&&$_POST['num']<3){
			$this->error("游戏图片最少上传3张,目前已上传{$num}张");
		}
		if($files['cha5']['size']){
			if(strtolower(pathinfo($files['cha5']['name'],PATHINFO_EXTENSION)) != 'mp4'){
				$this->error('游戏视频只能上传mp4格式');
			}
			if($files['cha5']['size'] > 52428800){
				$this->error('游戏视频最大上传大小为50M');
			}
			if(!$files['cha4']['size']){
				$this->error('上传游戏视频必须上传游戏缩略图');
			}
		}
	}
	function add_activity_do(){
		$model = new Model();
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$ap_name = str_replace(' ','',$_POST['ap_name']);
		$data['ap_name'] = $ap_name;
		if(!$ap_name){
			$this -> error("请填写活动名称");
		}
		$name_where['_string'] = "ap_name = '{$ap_name}' and status = 1";
		$name_result = $model -> table('sj_activity_page') -> where($name_where) -> select();
		if($name_result){
			$this -> error("该活动名称已存在");
		}
		$ap_type = $_POST['ap_type'];
		if($ap_type==2){
			//V6.4 处理预约活动页面
			$this->new_activity_do();
			exit();
		}
		$data['ap_type'] = $ap_type;
		$ap_desc = trim($_POST['ap_desc']);
		$ap_desc = htmlspecialchars($_POST['ap_desc']);
		$ap_desc = str_replace("\\","",$ap_desc);
		$data['ap_desc'] = $ap_desc;
		$desc_color = $_POST['desc_color'];
		$data['desc_color'] = $_POST['desc_color'];
		$bg_color = $_POST['bg_color'];
		$third_text_color = $_POST['third_text_color'];
		$data['third_text_color'] = $third_text_color;
		$second_text_color = $_POST['second_text_color'];
		$data['second_text_color'] = $second_text_color;
		if(!$ap_desc){
			$this -> error("请填写活动介绍");
		}


		$ap_pic = $_FILES['ap_pic'];

		$data['download_comment'] = $_POST['game_name'];//游戏名称
		$data['first_text_color'] = $_POST['yuyue_color'];//预约颜色
		$data['my_prize_text_color'] = $_POST['geren_color'];//个人确认颜色
		$data['third_text_color'] = $_POST['wanshan_color'];//完善颜色
		$data['back_button_color'] = $_POST['back_color'];//返回按钮颜色
		$data['change_button_color'] = $_POST['jieshao_color'];//活动介绍背景颜色

		$cha1= $_FILES['cha1'];
		$cha2= $_FILES['cha2'];
		$cha3= $_FILES['cha3'];
		$cha4= $_FILES['cha4'];
		$cha5= $_FILES['cha5'];
		$cha6= $_FILES['cha6'];

                /*
		$yuyue_pic= $_FILES['yuyue_pic'];
		$yuyue_pic= $_FILES['yuyue_pic'];
		$yuyue_pic= $_FILES['yuyue_pic'];
		$yuyue_pic= $_FILES['yuyue_pic'];
                 */

		if(!$ap_pic['size']){
			$this -> error("请上传页面banner图");
		}
		if($ap_pic['size']){
			$high_wd = getimagesize($ap_pic['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			if ($width_go_hg != 640 || $height_go_hg != 290) {
				$this->error("高分辨率图标大小不符合条件");
			}
                }

		if($cha1['size']){
                        $rs = $this->pic_isok($cha1);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图1图标尺寸不符合条件");
			}
                }

		if($cha2['size']){
                        $rs = $this->pic_isok($cha2);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图2图标尺寸不符合条件");
			}
                }
		if($cha3['size']){
                        $rs = $this->pic_isok($cha3);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图3图标尺寸不符合条件");
			}
                }

		if($cha4['size']){
                        $rs = $this->pic_isok($cha4);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图4图标尺寸不符合条件");
			}
                }

		if($cha5['size']){
                        $rs = $this->pic_isok($cha5);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图5图标尺寸不符合条件");
			}
                }
		if($cha6['size']){
                        $rs = $this->pic_isok($cha6);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图6图标尺寸不符合条件");
			}
                }


		$ap_imgurl_bg = $_FILES['ap_imgurl_bg'];
                /*
		if(!$ap_imgurl_bg['size']){
			$this -> error("请上传页面底部图片");
                }*/
		if($ap_type == 2){
                        /*
			$ap_rule = trim($_POST['ap_rule']);
			$ap_rule = htmlspecialchars($_POST['ap_rule']);
			$ap_rule = str_replace("\\","",$ap_rule);
			/*$data['ap_rule'] = $ap_rule;
			if(!$ap_rule){
				$this -> error("请填写活动规则");
                        }*/
			$version_code = $_POST['version_code'];
			if(!$version_code){
				$this -> error("请填写限制参加活动版本号");
			}
			if($version_code && !eregi('^[0-9]*$',$version_code)){
				$this -> error("版本号格式错误");
			}
			
			$data['version_code'] = $version_code;
			$change_switch = $_POST['change_switch'];
			$data['change_switch'] = $change_switch;
			$lottery_style = $_POST['lottery_style'];
			$data['lottery_style'] = $lottery_style;
			$share_switch = $_POST['share_switch'];
			$data['share_switch'] = $share_switch;
			$share_add = $_POST['share_add'];
			$data['share_add'] = $share_add;
			$share_add_all = $_POST['share_add_all'];
			$data['share_add_all'] = $share_add_all;
			//$must_share = $_POST['must_share'];
			//$data['must_share'] = $must_share;
			//$share_bgcolor = $_POST['share_bgcolor'];
			//$data['share_bgcolor'] = $share_bgcolor;
			//$download_bgcolor = $_POST['download_bgcolor'];
			//$data['download_bgcolor'] = $download_bgcolor;
			//$warning_bgcolor = $_POST['warning_bgcolor'];
			//$data['warning_bgcolor'] = $warning_bgcolor;
			$share_weixin_pic = $_FILES['share_weixin_pic'];
			$share_other_pic = $_FILES['share_other_pic'];
			$share_text = $_POST['share_text'];
			$data['share_text'] = $share_text;
			$button_color = $_POST['button_color'];
			$button_pic = $_FILES['button_pic'];
			$button_text_color = $_POST['button_text_color'];
			$data['button_text_color'] = $button_text_color;
			//$award_color = $_POST['award_color'];
			//$data['award_color'] = $award_color;
			//$rule_color = $_POST['rule_color'];
			//$data['rule_color'] = $rule_color;
			if($share_switch == 1){
				if(!$share_weixin_pic['size']){
						$this -> error("请上传分享到微信图");
				}
				if(!$share_other_pic['size']){
					$this -> error("请上传分享图");
				}
				if(!$share_text){
					$this -> error("请填写分享文案");
				}
			}
			$free_day_switch = $_POST['free_day_switch'];
			$data['free_day_switch'] = $free_day_switch;
                        /*
			$soft_style = $_POST['soft_style'];
			$data['soft_style'] = $soft_style;
			$soft_order = $_POST['soft_order'];
			$data['soft_order'] = $soft_order;
			$down_addlotterynum_switch = $_POST['down_addlotterynum_switch'];
			$data['down_addlotterynum_switch'] = $down_addlotterynum_switch;
			$is_filter = $_POST['is_filter'];
			$data['is_filter'] = $is_filter;
			$is_score = $_POST['is_score'];
			$data['is_score'] = $is_score;
			$package_size_switch = $_POST['package_size_switch'];
			$data['package_size_switch'] = $package_size_switch;
			$download_config = $_POST['download_config'];
			$data['download_config'] = $download_config;
                         */
			$lose_no_img = $_FILES['lose_no_img'];
	
                        if($_POST['change_switch']==1){
                            if(!$lose_no_img['size']){
                                    $this -> error("请上传未中奖无抽奖机会提示图");
                            }
                            $lost_no_desc = $_POST['lost_no_desc'];
                            $data['lost_no_desc'] = $lost_no_desc;
                            if(!$lost_no_desc){
                                    $this -> error("请填写未中奖无抽奖机会提示语");
                            }
                            $lose_yes_img = $_FILES['lose_yes_img'];
                            if(!$lose_yes_img['size']){
                                    $this -> error("请上传未中奖有抽奖机会提示图");
                            }
                            $lose_yes_desc = $_POST['lose_yes_desc'];
                            $data['lose_yes_desc'] = $lose_yes_desc;
                            if(!$lose_yes_desc){
                                    $this -> error("请填写未中奖有抽奖机会提示语");
                            }
                        }

                        /*
                        if($_POST['dep_type']==2){
                            if(!$lose_no_img['size']){
                                    $this -> error("请上传未中奖提示图");
                            }
                            $lost_no_desc = $_POST['lost_no_desc'];
                            $data['lost_no_desc'] = $lost_no_desc;
                            if(!$lost_no_desc){
                                    $this -> error("请填写未中奖提示语");
                            }
                            $lose_yes_img = $_FILES['lose_yes_img'];
                            if(!$lose_yes_img['size']){
                                    $this -> error("请上传无抽奖机会提示图");
                            }
                            $lose_yes_desc = $_POST['lose_yes_desc'];
                            $data['lose_yes_desc'] = $lose_yes_desc;
                            if(!$lose_yes_desc){
                                    $this -> error("请填写无抽奖机会提示语");
                            }
                        }*/


			$last_lottery_img = $_FILES['last_lottery_img'];

			//$last_lottery_desc = $_POST['last_lottery_desc'];
			//$data['last_lottery_desc'] = $last_lottery_desc;
			$is_warning = $_POST['is_warning'];
			$data['is_warning'] = $is_warning;
			$is_repeat = $_POST['is_repeat'];
			$data['is_repeat'] = $is_repeat;
			$bg_img = $_FILES['bg_img'];
                        /*
			$lottery_num_limit = $_POST['lottery_num_limit'];
			if(!$lottery_num_limit){
				$this -> error("请填写用户每日抽奖次数限制");
			}
			if($lottery_num_limit && !eregi('^[0-9]*$',$lottery_num_limit)){
				$this -> error("用户每日抽奖次数限制格式错误");
			}
			$data['lottery_num_limit'] = $lottery_num_limit;
                         */
			
			$lottery_pic = $_FILES['lottery_pic'];
			$click_lottery_pic = $_FILES['click_lottery_pic'];
			$unclick_lottery_pic = $_FILES['unclick_lottery_pic'];
			$alert_color = $_POST['alert_color'];
			$data['alert_color'] = $alert_color;
			$alert_button_color = $_POST['alert_button_color'];
			$data['alert_button_color'] = $alert_button_color;
			//$first_text_color = $_POST['first_text_color'];
			//$data['first_text_color'] = $first_text_color;
                        /*
			$update_warning_pic = $_FILES['update_warning_pic'];

                        if($_POST['dep_type']==1){
                            if(!$update_warning_pic['size']){
                                    $this -> error("请上传更新提示图");
                            }
                        }*/
                        /*
			$update_button_color = $_POST['update_button_color'];
			$data['update_button_color'] = $update_button_color;
			$rule_pic = $_FILES['rule_pic'];
			$prize_text_color = $_POST['prize_text_color'];
			$data['prize_text_color'] = $prize_text_color;
			$my_prize_button_color = $_POST['my_prize_button_color'];
			$data['my_prize_button_color'] = $my_prize_button_color;
			$popup_bg_pic = $_POST['popup_bg_pic'];
			$data['popup_bg_pic'] = $popup_bg_pic;
			$prize_bg_color = $_POST['prize_bg_color'];
			$data['prize_bg_color'] = $prize_bg_color;
			$soft_bg = $_POST['soft_bg'];
			$data['soft_bg'] = $soft_bg;
			$no_prize_pic = $_FILES['no_prize_pic'];
			$no_prize_text = $_POST['no_prize_text'];
			$data['no_prize_text'] = $no_prize_text;
                         */
		}
		//V6.4新增（下载按钮背景图片）
		if($_FILES['submit_button']) $submit_button = $_FILES['submit_button'];

		if($ap_pic || $ap_imgurl_bg || $share_weixin_pic || $share_other_pic || $lose_no_img || $lose_yes_img || $last_lottery_img || $button_pic || $bg_img || $lottery_pic || $click_lottery_pic || $unclick_lottery_pic || $update_warning_pic || $rule_pic || $no_prize_pic||$submit_button){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'cha1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'cha2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'cha3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'cha4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'cha5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'cha6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),

                'yuyue_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'geren_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'wanshan_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'back_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'game_icon' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'jieshao_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),
                'jieshao_all_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                ),

				'ap_imgurl_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_weixin_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_other_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_no_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_yes_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'last_lottery_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'button_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'bg_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'click_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'unclick_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'rule_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'no_prize_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'submit_button' =>array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
				),
			));

			$lists=$this->_uploadapk(0, $config);

			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'ap_pic') {
                    $data['ap_imgurl'] = $val['url'];
                }
                
                if ($val['post_name'] == 'ap_imgurl_bg') {
                    $data['ap_imgurl_bg'] = $val['url'];
                }

                if ($val['post_name'] == 'cha1') {//插图1
                    $data['ranking_no_pic1'] = $val['url'];
                }
                if ($val['post_name'] == 'cha2') {//插图2
                    $data['ranking_pic1'] = $val['url'];
                }
                if ($val['post_name'] == 'cha3') {//插图3
                    $data['uppage_color'] = $val['url'];
                }
                if ($val['post_name'] == 'cha4') {//插图4
                    $data['uppage'] = $val['url'];
                }
                if ($val['post_name'] == 'cha5') {//插图5
                    $data['nextpage_color'] = $val['url'];
                }
                if ($val['post_name'] == 'cha6') {//插图6
                    $data['nextpage'] = $val['url'];
                }

                //todo
                if ($val['post_name'] == 'yuyue_pic') {//预约按键图片
                    $data['back_text_color'] = $val['url'];
                }
                if ($val['post_name'] == 'geren_pic') {//个人确认
                    $data['prize_pic'] = $val['url'];
                }
                if ($val['post_name'] == 'wanshan_pic') {//完善个人信息
                    $data['back_button'] = $val['url'];
                }
                if ($val['post_name'] == 'back_pic') {//返回按钮
                    $data['prize_back_color'] = $val['url'];
                }
                if ($val['post_name'] == 'game_icon') {//游戏图标
                    $data['prize_back_text_color'] = $val['url'];
                }

                if ($val['post_name'] == 'jieshao_pic') {//活动介绍背景线
                    $data['change_button'] = $val['url'];
                }
                if ($val['post_name'] == 'jieshao_all_pic') {//活动介绍整个背景
                    $data['draw_button_color'] = $val['url'];
                }


				if ($val['post_name'] == 'share_weixin_pic') {
                    $data['share_weixin_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'share_other_pic') {
                    $data['share_other_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_no_img') {
                    $data['lose_no_img'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_yes_img') {
                    $data['lose_yes_img'] = $val['url'];
                }
				if ($val['post_name'] == 'last_lottery_img') {
                    $data['last_lottery_img'] = $val['url'];
                }
				if ($val['post_name'] == 'button_pic') {
                    $button_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'bg_img') {
                    $bg_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'lottery_pic') {
                    $lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'click_lottery_pic') {
                    $click_lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'unclick_lottery_pic') {
                    $unclick_lottery_pic = $val['url'];
                }

				if ($val['post_name'] == 'rule_pic') {
                    $rule_pic = $val['url'];
                }
				if ($val['post_name'] == 'no_prize_pic') {
                    $no_prize_pic = $val['url'];
                }
				//V6.4新增
				if ($val['post_name'] == 'submit_button') {
					$data['submit_button'] = $val['url'];
				}

            }
		}
		

		$data['bg_color'] = $bg_color;
		$data['bg_img'] = $bg_pic_url;
		
		if($lottery_style != 2){
			$data['lottery_pic'] = $lottery_pic;
		}elseif($lottery_style == 2){
			$sudoku_color = $_POST['sudoku_color'];
			$data['sudoku_color'] = $sudoku_color;
		}
		
		
		$data['click_lottery_pic'] = $click_lottery_pic;
		$data['unclick_lottery_pic'] = $unclick_lottery_pic;
		$data['rule_pic'] = $rule_pic;
		$data['no_prize_pic'] = $no_prize_pic;

		$data['button_color'] = $button_color;
		$data['button_pic'] = $button_pic_url;

		if($ap_type == 2){
                    /*
			$get_lottery_type_arr = $_POST['get_lottery_type'];
			if(!$get_lottery_type_arr){
				$this -> error("请选择抽奖机会获取方式");
			}else{
				$data['get_lottery_type'] = array_sum($get_lottery_type_arr);
			}
			$gift_type = $_POST['gift_type'];
			if(!$gift_type){
				$this -> error("请选择礼包发放优先级");
			}else{
				$data['gift_type'] = $gift_type;
			}
                         */
			$data['gift_type'] = 1;
		}else if($ap_type == 3){
			//v6.4新增 活动结束页面调整
			$data = $this->end_page_do($data);
		}
		$data['status'] = 1;
		//$data['dep_type'] = $_POST['dep_type'];
		$data['activate_type'] = 9;
		$data['ap_ctm'] = time();
		$data['ap_utm'] = time();

                /*
                if($_POST['dep_type']==2)//如果是游戏运营
                {
                    $data['lose_yes_img']=$data['lose_no_img'];
                }*/
		//

		$result = $model -> table('sj_activity_page') -> add($data);
                //echo $model->getlastsql();
		if($result){
			if($ap_type == 1){
				$update_data['ap_link'] = ACTIVITY_URL . "/lottery/coactivity_prepare.php";
			}elseif($ap_type == 2){
				$update_data['ap_link'] = ACTIVITY_URL . "/lottery/coactivity_lottery.php";
			}elseif($ap_type == 3){
				$update_data['ap_link'] = ACTIVITY_URL . "/lottery/coactivity_end.php";
			}
			$update_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $result)) -> save($update_data);
			$this -> writelog("已添加id为{$result}的通用新预约活动页面", 'sj_activity_page', $result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/NewPredownload/activity_list/p/{$p}/lr/{$lr}");
                        $model->query("insert into `sj_actives_soft` (`category_id`, `soft_name`, `package`, `rank`, `recomment`, `award_recomment`, `create_tm`, `update_tm`, `status`, `page_id`) values('226','安智市场','cn.goapk.market','1','测试','测试测试测试测试测试测试测试','1463472638','1463472638','1',{$result})");

                        
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}

	/*
	 * V6.4新增
	 * 页面类型（结束页面调整）
	 */
	function end_page_do($data){
		$data['submit_text_color'] = $_POST['submit_text_color'];
		$data['download_bgcolor'] = $_POST['download_bgcolor'];
		return $data;
	}

	function edit_activity_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id,'status' => 1)) -> select();
		$result[0]['ap_desc'] = str_replace("<br />","",$result[0]['ap_desc']);
		$result[0]['ap_rule'] = str_replace("<br />","",$result[0]['ap_rule']);
		$this -> assign('result',$result);

		if($result[0]['ap_type']==2){
			$img_arr = array('ranking_no_pic1','ranking_pic1','uppage_color','ap_download_link','telephone_warning');
			$num = 0;
			foreach($img_arr as $v){
				if(!empty($result[0][$v])){
					$num ++;
				}
			}
			$this->assign('num',$num);
			$this->display('new_edit_activity_show');
		}else{
			$this -> display();
		}
	}
	
	function edit_activity_do(){
		$model = new Model();
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$id = $_POST['id'];
		$the_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> select();
		$ap_name = str_replace(' ','',$_POST['ap_name']);
		if(!$ap_name){
			$this -> error("请填写活动名称");
		}
		$data['ap_name'] = $ap_name;
		$data['download_comment'] = $_POST['game_name'];//游戏名称
		$name_where['_string'] = "ap_name = '{$ap_name}' and status = 1 and ap_id != {$id}";
		$name_result = $model -> table('sj_activity_page') -> where($name_where) -> select();
		if($name_result){
			$this -> error("该活动名称已存在");
		}
		if($the_result[0]['ap_type']==2){
			$this->new_activity_do();
			exit();
		}

		$ap_type = $the_result[0]['ap_type'];
		$ap_desc = trim($_POST['ap_desc']);
		$ap_desc = htmlspecialchars($_POST['ap_desc']);
		$ap_desc = str_replace("\\","",$ap_desc);
		$data['ap_desc'] = $ap_desc;
		$desc_color = $_POST['desc_color'];
		$data['desc_color'] = $_POST['desc_color'];
		$bg_color = $_POST['bg_color'];
		$third_text_color = $_POST['third_text_color'];
		$data['third_text_color'] = $third_text_color;
		$second_text_color = $_POST['second_text_color'];
			$data['second_text_color'] = $second_text_color;

                $cha1= $_FILES['cha1'];
		$cha2= $_FILES['cha2'];
		$cha3= $_FILES['cha3'];
		$cha4= $_FILES['cha4'];
		$cha5= $_FILES['cha5'];
		$cha6= $_FILES['cha6'];
		$game_icon= $_FILES['game_icon'];

		$yuyue_pic= $_FILES['yuyue_pic'];
		$geren_pic= $_FILES['geren_pic'];
		$wanshan_pic= $_FILES['wanshan_pic'];
		$back_pic= $_FILES['back_pic'];
		$jieshao_pic= $_FILES['jieshao_pic'];
		$jieshao_all_pic= $_FILES['jieshao_all_pic'];

                

		$data['download_comment'] = $_POST['game_name'];//游戏名称
		$data['first_text_color'] = $_POST['yuyue_color'];//预约颜色
		$data['my_prize_text_color'] = $_POST['geren_color'];//个人确认颜色
		$data['third_text_color'] = $_POST['wanshan_color'];//完善颜色
		$data['back_button_color'] = $_POST['back_color'];//返回按钮颜色
		$data['change_button_color'] = $_POST['jieshao_color'];//活动介绍背景颜色

		if(!$ap_desc){
			$this -> error("请填写活动介绍");
		}
		$ap_pic = $_FILES['ap_pic'];
		
		if($ap_pic['size']){
			$high_wd = getimagesize($ap_pic['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			if ($width_go_hg != 640 || $height_go_hg != 290) {
				$this->error("高分辨率图标大小不符合条件");
			}
		}

		if($cha1['size']){
                        $rs = $this->pic_isok($cha1);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图1图标尺寸不符合条件");
			}
                }

		if($cha2['size']){
                        $rs = $this->pic_isok($cha2);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图2图标尺寸不符合条件");
			}
                }
		if($cha3['size']){
                        $rs = $this->pic_isok($cha3);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图3图标尺寸不符合条件");
			}
                }

		if($cha4['size']){
                        $rs = $this->pic_isok($cha4);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图4图标尺寸不符合条件");
			}
                }

		if($cha5['size']){
                        $rs = $this->pic_isok($cha5);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图5图标尺寸不符合条件");
			}
                }
		if($cha6['size']){
                        $rs = $this->pic_isok($cha6);
			if ($rs[0] != 200 || $rs[1] != 300) {
				$this->error("游戏插图6图标尺寸不符合条件");
			}
                }


		$ap_imgurl_bg = $_FILES['ap_imgurl_bg'];
			$bg_img = $_FILES['bg_img'];
		if($ap_type == 2){
                        /*
			$ap_rule = trim($_POST['ap_rule']);
			$ap_rule = htmlspecialchars($_POST['ap_rule']);
			$ap_rule = str_replace("\\","",$ap_rule);
			$data['ap_rule'] = $ap_rule;
			if(!$ap_rule){
				$this -> error("请填写活动规则");
			}
                         */
			$version_code = $_POST['version_code'];
			if(!$version_code){
				$this -> error("请填写限制参加活动版本号");
			}
			if($version_code && !eregi('^[0-9]*$',$version_code)){
				$this -> error("版本号格式错误");
			}
			
			$data['version_code'] = $version_code;
			$change_switch = $_POST['change_switch'];
			$data['change_switch'] = $change_switch;
			$lottery_style = $_POST['lottery_style'];
			$data['lottery_style'] = $lottery_style;
			$share_switch = $_POST['share_switch'];
			$data['share_switch'] = $share_switch;
			$share_add = $_POST['share_add'];
			$data['share_add'] = $share_add;
			$share_add_all = $_POST['share_add_all'];
			$data['share_add_all'] = $share_add_all;
			//$must_share = $_POST['must_share'];
			//$data['must_share'] = $must_share;
			//$share_bgcolor = $_POST['share_bgcolor'];
			//$data['share_bgcolor'] = $share_bgcolor;
			//$download_bgcolor = $_POST['download_bgcolor'];
			//$data['download_bgcolor'] = $download_bgcolor;
			//$warning_bgcolor = $_POST['warning_bgcolor'];
			//$data['warning_bgcolor'] = $warning_bgcolor;
			$share_weixin_pic = $_FILES['share_weixin_pic'];
			$share_other_pic = $_FILES['share_other_pic'];
			$share_text = $_POST['share_text'];
			$data['share_text'] = $share_text;
			$button_color = $_POST['button_color'];
			$button_pic = $_FILES['button_pic'];
			$button_text_color = $_POST['button_text_color'];
			$data['button_text_color'] = $button_text_color;
			//$award_color = $_POST['award_color'];
			//$data['award_color'] = $award_color;
			//$rule_color = $_POST['rule_color'];
			//$data['rule_color'] = $rule_color;
			$free_day_switch = $_POST['free_day_switch'];
			$data['free_day_switch'] = $free_day_switch;

                        /*
			$soft_style = $_POST['soft_style'];
			$data['soft_style'] = $soft_style;
			$soft_order = $_POST['soft_order'];
			$data['soft_order'] = $soft_order;
			$down_addlotterynum_switch = $_POST['down_addlotterynum_switch'];
			$data['down_addlotterynum_switch'] = $down_addlotterynum_switch;
			$is_filter = $_POST['is_filter'];
			$data['is_filter'] = $is_filter;
			$is_score = $_POST['is_score'];
			$data['is_score'] = $is_score;
			$package_size_switch = $_POST['package_size_switch'];
			$data['package_size_switch'] = $package_size_switch;
			$download_config = $_POST['download_config'];
			$data['download_config'] = $download_config;
                         */
			$lose_no_img = $_FILES['lose_no_img'];
			$lost_no_desc = $_POST['lost_no_desc'];
			$data['lost_no_desc'] = $lost_no_desc;
			if(!$lost_no_desc){
				$this -> error("请填写未中奖无抽奖机会提示语");
			}
			$lose_yes_img = $_FILES['lose_yes_img'];
			$lose_yes_desc = $_POST['lose_yes_desc'];
			$data['lose_yes_desc'] = $lose_yes_desc;
			if(!$lose_yes_desc){
				$this -> error("请填写未中奖有抽奖机会提示语");
			}
			$last_lottery_img = $_FILES['last_lottery_img'];

			//$last_lottery_desc = $_POST['last_lottery_desc'];
			//$data['last_lottery_desc'] = $last_lottery_desc;
			$is_warning = $_POST['is_warning'];
			$data['is_warning'] = $is_warning;
			$is_repeat = $_POST['is_repeat'];
			$data['is_repeat'] = $is_repeat;
                        /*
			$lottery_num_limit = $_POST['lottery_num_limit'];
			if(!$lottery_num_limit){
				$this -> error("请填写用户每日抽奖次数限制");
			}
			if($lottery_num_limit && !eregi('^[0-9]*$',$lottery_num_limit)){
				$this -> error("用户每日抽奖次数限制格式错误");
			}
			$data['lottery_num_limit'] = $lottery_num_limit;
                         */
			
			$lottery_pic = $_FILES['lottery_pic'];
			$click_lottery_pic = $_FILES['click_lottery_pic'];
			$unclick_lottery_pic = $_FILES['unclick_lottery_pic'];
			$alert_color = $_POST['alert_color'];
			$data['alert_color'] = $alert_color;
			$alert_button_color = $_POST['alert_button_color'];
			$data['alert_button_color'] = $alert_button_color;
			//$first_text_color = $_POST['first_text_color'];
			//$data['first_text_color'] = $first_text_color;
			$update_warning_pic = $_FILES['update_warning_pic'];
                        /*
			$update_button_color = $_POST['update_button_color'];
			$data['update_button_color'] = $update_button_color;
			$rule_pic = $_FILES['rule_pic'];
			$prize_text_color = $_POST['prize_text_color'];
			$data['prize_text_color'] = $prize_text_color;
			$my_prize_button_color = $_POST['my_prize_button_color'];
			$data['my_prize_button_color'] = $my_prize_button_color;
			$popup_bg_pic = $_POST['popup_bg_pic'];
			$data['popup_bg_pic'] = $popup_bg_pic;
			$prize_bg_color = $_POST['prize_bg_color'];
			$data['prize_bg_color'] = $prize_bg_color;
			$soft_bg = $_POST['soft_bg'];
			$data['soft_bg'] = $soft_bg;
			$no_prize_pic = $_FILES['no_prize_pic'];
			$no_prize_text = $_POST['no_prize_text'];
			$data['no_prize_text'] = $no_prize_text;
                         */
		}else if($ap_type==3){
			$ap_rule = trim($_POST['ap_rule']);
			$ap_rule = htmlspecialchars($_POST['ap_rule']);
			$ap_rule = str_replace("\\","",$ap_rule);
			$data['ap_rule'] = $ap_rule;
			//V6.4新增（下载按钮背景图片）
			if($_FILES['submit_button']) $submit_button = $_FILES['submit_button'];
		}
		
                //todo
		if($yuyue_pic['size'] ||$geren_pic['size'] ||$wanshan_pic['size'] ||$back_pic['size'] ||$jieshao_pic['size'] ||$jieshao_all_pic['size'] ||$game_icon['size'] ||$cha1['size'] ||$cha2['size'] ||$cha3['size'] ||$cha4['size'] ||$cha5['size'] ||$cha6['size'] ||$ap_pic['size'] || $ap_imgurl_bg['size'] || $share_weixin_pic['size'] || $share_other_pic['size'] || $lose_no_img['size'] || $lose_yes_img['size'] || $last_lottery_img['size'] || $button_pic['size'] || $bg_img['size'] || $lottery_pic['size'] || $click_lottery_pic['size'] || $unclick_lottery_pic['size'] || $update_warning_pic['size'] || $rule_pic['size'] || $no_prize_pic['size']||$submit_button['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				
                                'cha1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'cha2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'cha3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'cha4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'cha5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'cha6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),

                                'yuyue_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'geren_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'wanshan_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'back_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'game_icon' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'jieshao_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),
                                'jieshao_all_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
                                    ),

				'ap_imgurl_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_weixin_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_other_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_no_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_yes_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'last_lottery_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'button_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'bg_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'click_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'unclick_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'update_warning_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'rule_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'no_prize_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'submit_button' =>array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
				)
			));
			$lists=$this->_uploadapk(0, $config);
			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'ap_pic') {
                    $data['ap_imgurl'] = $val['url'];
                }
                if ($val['post_name'] == 'ap_imgurl_bg') {
                    $data['ap_imgurl_bg'] = $val['url'];
                }

                if ($val['post_name'] == 'cha1') {//插图1
                    $data['ranking_no_pic1'] = $val['url'];
                }
                if ($val['post_name'] == 'cha2') {//插图2
                    $data['ranking_pic1'] = $val['url'];
                }
                if ($val['post_name'] == 'cha3') {//插图3
                    $data['uppage_color'] = $val['url'];
                }
                if ($val['post_name'] == 'cha4') {//插图4
                    $data['uppage'] = $val['url'];
                }
                if ($val['post_name'] == 'cha5') {//插图5
                    $data['nextpage_color'] = $val['url'];
                }
                if ($val['post_name'] == 'cha6') {//插图6
                    $data['nextpage'] = $val['url'];
                }
				//V6.4新增
				if ($val['post_name'] == 'submit_button') {
					$data['submit_button'] = $val['url'];
				}
                //todo
                if ($val['post_name'] == 'yuyue_pic') {//预约按键图片
                    $data['back_text_color'] = $val['url'];
                }
                if ($val['post_name'] == 'geren_pic') {//个人确认
                    $data['prize_pic'] = $val['url'];
                }
                if ($val['post_name'] == 'wanshan_pic') {//完善个人信息
                    $data['back_button'] = $val['url'];
                }
                if ($val['post_name'] == 'back_pic') {//返回按钮
                    $data['prize_back_color'] = $val['url'];
                }
                if ($val['post_name'] == 'game_icon') {//游戏图标
                    $data['prize_back_text_color'] = $val['url'];
                }

                if ($val['post_name'] == 'jieshao_pic') {//活动介绍背景线
                    $data['change_button'] = $val['url'];
                }
                if ($val['post_name'] == 'jieshao_all_pic') {//活动介绍整个背景
                    $data['draw_button_color'] = $val['url'];
                }


				if ($val['post_name'] == 'mean_text_color') {
                    $data['mean_text_color'] = $val['url'];
                }
				if ($val['post_name'] == 'share_other_pic') {
                    $data['share_other_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_no_img') {
                    $data['lose_no_img'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_yes_img') {
                    $data['lose_yes_img'] = $val['url'];
                }
				if ($val['post_name'] == 'last_lottery_img') {
                    $data['last_lottery_img'] = $val['url'];
                }
				if ($val['post_name'] == 'button_pic') {
                    $button_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'bg_img') {
                    $bg_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'lottery_pic') {
                    $lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'click_lottery_pic') {
                    $click_lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'unclick_lottery_pic') {
                    $unclick_lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'update_warning_pic') {
                    $update_warning_pic = $val['url'];
                }
				if ($val['post_name'] == 'rule_pic') {
                    $rule_pic = $val['url'];
                }
				if ($val['post_name'] == 'no_prize_pic') {
                    $no_prize_pic = $val['url'];
                }
				if ($val['post_name'] == 'no_prize_pic') {
					$no_prize_pic = $val['url'];
				}
            }
		}
		

		$data['bg_color'] = $bg_color;
		if($bg_img['size']){
			$data['bg_img'] = $bg_pic_url;
		}

		if($lottery_style != 2){
			$data['lottery_pic'] = $lottery_pic;
		}elseif($lottery_style == 2){
			$sudoku_color = $_POST['sudoku_color'];
			$data['sudoku_color'] = $sudoku_color;
		}
		if($click_lottery_pic){
			$data['click_lottery_pic'] = $click_lottery_pic;
		}
		if($unclick_lottery_pic){
			$data['unclick_lottery_pic'] = $unclick_lottery_pic;
		}
		if($update_warning_pic){
			$data['update_warning_pic'] = $update_warning_pic;
		}
		if($rule_pic['size']){
			$data['rule_pic'] = $rule_pic;
		}
		if($no_prize_pic['size']){
			$data['no_prize_pic'] = $no_prize_pic;
		}
		
		$data['button_color'] = $button_color;
		if($button_pic['size']){
			$data['button_pic'] = $button_pic_url;
		}
		
		if($ap_type == 2){
                    /*
			$get_lottery_type_arr = $_POST['get_lottery_type'];
			if(!$get_lottery_type_arr){
				$this -> error("请选择抽奖机会获取方式");
			}else{
				$data['get_lottery_type'] = array_sum($get_lottery_type_arr);
			}
			$gift_type = $_POST['gift_type'];
			if(!$gift_type){
				$this -> error("请选择礼包发放优先级");
			}else{
				$data['gift_type'] = $gift_type;
			}
                         */
			$data['gift_type'] = 1;
		}else if($ap_type==3){
			//v6.4新增
			//活动结束页面调整
			$data = $this->end_page_do($data);
		}
		$data['ap_utm'] = time();
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);

		if($result){
			$this -> writelog("已编辑id为{$id}的通用新预约活动页面", 'sj_activity_page', $id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/NewPredownload/activity_list/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	function del_activity(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的通用新预约活动页面", 'sj_activity_page', $id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/NewPredownload/activity_list/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}
	}

	function pub_get_softname(){
		$model = M();
		$keyword = $_GET['query'];
		$real_keyword = escape_string($keyword);
		$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;
		$where = array(
				'softname' => array('like', "%{$real_keyword}%"),
				'del' => 0
		);

		$list = $model->table('yx_product')->where($where)->select();
		$data = array(
				'query' => $keyword,
				'suggestions' => array(),
				'data' => array(),
		);

		foreach($list as $v) {
			$data['suggestions'][] = $v['softname'];
			$data['data'][] = $v['softname'];
		}

		exit(json_encode($data));
	}

}
