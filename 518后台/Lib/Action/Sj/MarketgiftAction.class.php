<?php
class MarketgiftAction extends CommonAction {
	private $becomes = array('&', '"', "'", '<', '>');
	private $active_module_url = "/tmp/azyx/activities";         //活动礼包模板临时文件夹
	private $active_module_true_url = "/data/www/wwwroot/new-wwwroot/azyx-html/activities";   //活动礼包模板存储文件夹
	private $news_module_url = "/tmp/azyx/news";					//新闻资讯模板临时文件夹
	private $news_module_true_url = "/data/www/wwwroot/new-wwwroot/azyx-html/news";			//新闻资讯模板存储文件夹
	private $header = "<!DOCTYPE html>
						<html>
						<head>
						<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
						<meta name='viewport' content='width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no'>
						<META HTTP-EQUIV='Cache-Control' CONTENT='max-age=5'>
						<meta content='telephone=no' name='format-detection'>
						<meta name='keywords' content='Android,安卓,安卓网,安卓市场,安卓游戏,电子市场,安卓软件,Android游戏,安卓手机,游戏汉化,手机游戏,Android软件,软件下载,最新汉化软件,安卓游戏下载,游戏汉化' />
						<meta name='description' content='安智市场,Android,安卓,安卓网,安卓市场,电子市场,安卓游戏,应用商店-国内最专业的Android安卓电子市场，提供海量安卓软件、Android手机游戏、安卓最新汉化软件、汉化游戏资源及最新APK汉化、汉化破解APP、APK免费下载，致力于为用户打造最贴心的Android安卓手机应用商店' />
						<title>安智活动2</title>";






	//活动礼包预览

    function gift_profile(){
		$id = $_GET['id'];
		$active_model = D('sendNum.sendNum');
		$result = $active_model -> table('olgame_active') -> where(array('active_id'=>$id)) -> select();
		$content = $result[0]['module_content'];
		$content = htmlspecialchars_decode($content);
		$this -> assign("profile", $content);
		$this -> display("");
    }
	
	
	function text(){
		$model = new Model();
		sleep(700);
		$result = $model -> table('sj_soft') -> where(array('status' => 1)) -> limit(1) -> select();
		var_dump($result);
		$this -> display();
	}


	function market_gift_list(){
		$active_model = D('sendNum.sendNum');
		$qu_array=array(
			"2"=>"安卓游戏",
			"4"=>"安智市场",
			"6"=>"安智市场,安卓游戏",
			"8"=>"SDK",
			"10"=>"安卓游戏,SDK",
			"12"=>"安智市场,SDK",
			"14"=>"安智市场,安卓游戏,SDK",
		);
	
		//刷新已过期活动礼包
		$now_where['_string'] = "status = 1 and end_tm < ".time()." ";
		$now_result = $active_model -> table('sendNum.sendnum_active') -> where($now_where) -> select();

		foreach($now_result as $key => $val){

			$rank_where['_string'] = "status = 1 and active_id = {$val['id']}";
			$rank_result = $active_model -> table('sendNum.olgame_active') -> where($rank_where) -> select();
			
			if($rank_result[0]['rank'] != 0){
				$other_where['_string'] = "status = 1 and rank > {$rank_result[0]['rank']}";
				$other_result = $active_model -> table('sendNum.olgame_active') -> where($other_where) -> select();

				foreach($other_result as $key => $val){
					

					$change_where['rank'] = $val['rank'];
					$change_data = array(
						'rank' => $val['rank'] - 1,
					);

					$change_result = $active_model -> update_active_content($change_where,$change_data);
				}

				$myself_where['id'] = $rank_result[0]['id'];

				$myself_data = array(
					'rank' => 0,
					'selection' => 0,
				);
				$myself_result = $active_model -> update_active_content($myself_where,$myself_data);
			}
		}
		

		//刷新开启网游精选状态的已过期活动礼包的网游精选状态为关闭
		$last_result = $active_model -> table('sendNum.olgame_active') -> where(array('selection' => 1,'status' => 1)) -> select();
		$last_result_active = $active_model -> table('sendNum.sendnum_active') -> where(array('id' => $last_result[0]['active_id'])) -> select();
		if($last_result_active[0]['end_tm'] < time()){
			$selection_where['active_id'] = $last_result_active[0]['id'];
			$data = array(
				'selection' => 0,
			);
			$selection_result = $active_model -> update_active_content($selection_where,$data);
		}
		
	
		
		$no_where['_string'] = "status = 1 and  end_tm >".time()."";
		$no_result = $active_model -> table('sendNum.sendnum_active') -> where($no_where) -> select();
		$time = time();
		$sql ="select * from sendNum.sendnum_active as a left join sendNum.olgame_active as b on a.id = b.active_id where a.status = 1 and b.status = 1 and a.end_tm > $time and b.rank > 0 order by b.rank";

		$first_one = $active_model ->query($sql);
//		print_r($first_one);
//		print_r(mysql_error());
		$l_count = count($first_one);
		if($l_count!=0){
			$flash = 'flash';
			$this->edit_rank($flash);
		}

		foreach($no_result as $key => $val){
			$no_id_str .= $val['id'].',';
		}
		import("@.ORG.Page");
		$no_id = substr($no_id_str,0,-1);
		$where['_string'] = "status = 1 and active_id in ({$no_id})";
		$count = $active_model -> table('sendNum.olgame_active') -> where($where) -> order('rank') -> count();
		
		$Page = new Page($count,20,$limit);
		$result = $active_model -> table('sendNum.olgame_active') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank') -> select();
		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		if(!$_GET['lr']){
			$_GET['lr'] =20;
		}
		$this->assign('p',$_GET['p']);
		$this->assign('lr',$_GET['lr']);
		foreach($result as $key => $val){
			$active_where['sendnum_active.id'] = $val['active_id'];
			$active_where['sendnum_active.status'] = 1;

			//$active_result = $active_model -> table('sendnum_active') -> where($active_where) -> select();
			$active_result = $active_model -> table('sendnum_active') ->join("sendnum_gift_type on sendnum_active.gift_type = sendnum_gift_type.id")-> where($active_where) -> select();
			
			//$sql = "select * from sendNum.sendnum_active as a left join sendNum.sendnum_gift_type as b on a.gift_type = b.id where a.`status` = 1 and a.id = ".$val['active_id'];
			//$active_result = $active_model->query($sql);
			
			if($active_result[0]['active_from'] == 4 || $active_result[0]['active_from'] == 8 || $active_result[0]['active_from'] == 12){
				$val['start_button'] = 0;
			}else{
				$val['start_button'] = 1;
			}
			$val['num'] = $key+1+(($_GET['p']-1)*$_GET['lr']);
			$val['cut_tm'] = $active_result[0]['end_tm'];
			$val['start_tm'] = $active_result[0]['start_tm'];
			$val['all_num'] = $active_result[0]['num_cnt'];
			$else_result = $active_model -> table('sendNum.sendnum_number') -> where(array('active_id' => $val['active_id'],'status' => 0)) -> select();
			$val['surplus_num'] = $active_result[0]['num_cnt'] - $active_result[0]['used_cnt'];
			$val['limit_num'] = $active_result[0]['conf_cnt'];
			$val['release_tm'] = $val['start_tm'];
			$val['active_name'] = $active_result[0]['active_name'];
			$val['active_status'] = $active_result[0]['status'];
			$val['gift_type_name'] = $active_result[0]['gift_type'];
			$val['active_from'] = $qu_array[$active_result[0]['active_from']];
			$result[$key] = $val;
		}

		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');	
		$show = $Page->show();
		$this->assign('page',$show);
		
		$this -> assign("count",$count);
		$this -> assign("result",$result);
		
		$this -> display();

		
	}


	//显示市场礼包添加页面
	function market_add_show(){
		$active_model = D('sendNum.sendNum');
		
		//刷新已过期活动礼包
		$now_where['_string'] = "status = 1 and end_tm < ".time()."";
		$now_result = $active_model -> table('sendNum.sendnum_active') -> where($now_where) -> select();
		foreach($now_result as $key => $val){
			$rank_where['_string'] = "status = 1 and active_id = {$val['id']}";
			$rank_result = $active_model -> table('sendNum.olgame_active') -> where($rank_where) -> select();
			if($rank_result[0]['rank'] != 0){
				$other_where['_string'] = "status = 1 and rank > {$rank_result[0]['rank']}";
				$other_result = $active_model -> table('sendNum.olgame_active') -> where($other_where) -> select();

				foreach($other_result as $key => $val){
					$change_where['rank'] = $val['rank'];
					$change_data = array(
						'rank' => $val['rank'] - 1,
					);
					$change_result = $active_model -> update_active_content($change_where,$change_data);
				}
				$myself_where['id'] = $rank_result[0]['id'];
				$myself_data = array(
					'rank' => 0,
					'selection' => 0,
				);
				$myself_result = $active_model -> update_active_content($myself_where,$myself_data);
			}
		}
		$time = time();
		$sql ="select * from sendNum.sendnum_active as a inner join sendNum.olgame_active as b on a.id = b.active_id where a.status = 1 and b.status = 1 and a.end_tm >{$time} and b.rank not in (0) order by b.rank limit 1";

		$first_one = $active_model ->query($sql);
		$l_count = count($first_one);
		if($l_count!=0){
			$flash = 'flash';
			$this->edit_rank($flash);
		}
		$result = $active_model->table("sendNum.sendnum_gift_type")->where("status = 1")->select();
		$this->assign('result',$result);
		$rand = rand(1,9999);
		$this->assign('rand',$rand);
		$this->display();
	}


		//市场礼包添加提交
	function market_add_submit(){
		$model = new Model();
		$limit_num = $_POST['limit_num'];
		$active_from = 0;
		if(!empty($_POST['pt_market']) && $_POST['pt_market'] =='on'){
			$market_num = intval($_POST['sc_num']);
			$active_from += 4;
			
			if($market_num==0 || $market_num==''){
				$this -> error("发放平台激活码数量不能为0");
			}
			if($market_num<$limit_num){
				$this -> error("每日发放限制不能大于平台发布数量");
			}
		}else{
			$market_num = 0;
		}

		if(!empty($_POST['pt_game']) && $_POST['pt_game'] =='on'){
			$game_num = intval($_POST['game_num']);
			$active_from += 2;
			
			if($game_num==0 || $game_num==''){
				$this -> error("发放平台激活码数量不能为0");
			}
			if($game_num<$limit_num){
				$this -> error("每日发放限制不能大于平台发布数量");
			}
		}else{
			$game_num = 0;
		}
		if(!empty($_POST['pt_sdk']) && $_POST['pt_sdk'] =='on'){
			$sdk_num = intval($_POST['sdk_num']);
			$active_from += 8;
			
			if($sdk_num==0 || $sdk_num==''){
				$this -> error("发放平台激活码数量不能为0");
			}
			if($sdk_num<$limit_num){
				$this -> error("每日发放限制不能大于平台发布数量");
			}
		}else{
			$sdk_num = 0;
		}

		$count_num = $market_num+$game_num+$sdk_num;
	
		$active_name = trim($_POST['active_name']);
		$time = time();
		if(!$active_name){
			$this -> error("标题名称不能为空");
		}
		if(mb_strlen($active_name,'utf-8') > 10){
			$this -> error("请输入10个字以内的名称");
		}
		$gift_type = $_POST['gift_type'];
		if($gift_type==0){
			$this -> error("请选择礼包类型");
		}
		$usable = $_POST['usable'];
		if(!$usable){
			$this -> error("使用范围不能为空");
		}
		if(strlen($usable) > 30){
			$this -> error("请输入10个字以内的使用范围");
		}
		$apply_pkg = trim($_POST['apply_pkg']);
		$pkg_result = $model -> table('sj_soft') -> where(array('package' => $apply_pkg)) -> select();
		if(!$pkg_result){
			$this -> error("应用包名不存在");
		}
		$cut_tm = strtotime($_POST['cut_tm']);
		$start_tm = strtotime($_POST['start_tm']);
		if($start_tm==''){
			$this -> error("开始时间不能为空");
		}
		if($cut_tm==''){
			$this -> error("结束时间不能为空");
		}
		if($cut_tm < $start_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$intro = trim($_POST['intro']);
		if(strlen($intro) > 150){
			$this -> error("简介不能超过50个汉字");
		}
		
		if($limit_num == ''){
			$this -> error("每日发放限制不能为空");
		}


		if($_POST['usage']==''){
			$this -> error("使用方法不能为空");
		}

		if($_POST['detail']==''){
			$this -> error("礼包详情不能为空");
		}
		if($_POST['exchange_start']==''){
			$this -> error("兑换开始时间不能为空");
		}
		if($_POST['exchange_end']==''){
			$this -> error("兑换结束时间不能为空");
		}
		if($_POST['exchange_end']<$_POST['exchange_start']){
			$this -> error("兑换结束时间不能在兑换开始时间之前");
		}

		if(strtotime($_POST['exchange_start'])<$start_tm){
			$this -> error("兑换时间不能在开始时间之前");
		}

		//上传文件获取隐藏域中的内容
		$new_file = $_POST['new_file'];
		if($new_file==''){
			$this -> error("请上传激活码文件");
		}
	
		if(!file_exists($new_file)){
			$this -> error("csv文件不存在");
		}


		$new_file_name = $new_file;
		$shili = fopen ($new_file_name,"r") ;  //打开文本

		while ( !feof ( $shili )){      //判断是否到了文件最后的函数
			$shi = fgets ( $shili,1024 ) ;				//读取其中的数据
			
			$a = explode(',',$shi);

			if($a[1]){
				$this -> error("激活码文件格式错误");
			}
			$str .= $shi.","; 

		}

		$str_arr = str_replace("\r\n", '', $str);

		$str_arrs = substr($str_arr,0,strlen($str_arr) - 1);

		$code_arrs = explode(',',$str_arrs);
	
		foreach($code_arrs as $key => $val){
			if(trim($val)!=""){
				$code_arr[$key] = $val;
			}
		}

		$all_num = count($code_arr);

		if($all_num>9999){
			$this -> error("激活码总数不能超过9999个");
		}

		if($all_num<1){
			$this -> error("激活码不能为空");
		}

		if($count_num>$all_num){
			$this -> error("当前发布的礼包数量大于总数，请重新填写");
		}else if($count_num<$all_num){
			$this -> error("当前发布的礼包数量小于总数，请重新填写");
		}

		if($limit_num > $all_num){
			$this -> error("每日限制数不能大于激活码总数");
		}

		if($limit_num){
			$limit_num = intval(strval($limit_num));
			if($limit_num <= 0 || !is_int($limit_num)){
				$this -> error("请输入正确格式的限制数");
			}
		}


		foreach($code_arr as $key => $val){
			if(strlen($val) > 25){
				$this -> error("激活码长度不能大于25位");
			}
		}

		$code_arr_unique = array_unique($code_arr);
		if(preg_match("/[\x80-\xff]./", $str_arrs)){
			$this -> error("激活码文件中不可有中文");
		}
		if(count($code_arr) > count($code_arr_unique)){
			$repeat_arr = array_diff_assoc($code_arr,$code_arr_unique);
			foreach($repeat_arr as $key => $val){
				$repeat_str .= $val.',';
				$this -> error("激活码文件中含有重复数据:{$val}");
			}
			
		}

		//数据写入
		$data_active['active_name'] = trim($active_name);

		$data_active['active_type'] = 1;
		$data_active['conf_cnt'] = $limit_num;
		$data_active['status'] = 1;
		$data_active['end_tm'] = strtotime($_POST['cut_tm']);
		$data_active['start_tm'] = strtotime($_POST['start_tm']);
		$data_active['num_cnt'] = $all_num;
		$data_active['used_cnt'] = 0;
		$data_active['creater'] = $_SESSION['admin']['admin_user_name'];
		$data_active['creater_id'] = $_SESSION['admin']['admin_id'];
		$data_active['active_from'] = $active_from;
		$data_active['market_conf_cnt'] = $market_num;
		$data_active['game_conf_cnt'] = $game_num;
		$data_active['sdk_conf_cnt'] = $sdk_num;
		$data_active['gift_type'] = $gift_type;

		$data['apply_pkg'] = trim($apply_pkg);
		$data['usable'] = $usable;
		$data['module_content'] = htmlspecialchars($_POST['editor_content']);
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$data['active_type'] = $active_from;
		$active_model = D('sendNum.sendNum');

		if(strtotime($_POST['start_tm'])<$time && strtotime($_POST['cut_tm'])<$time){
			$data['rank'] = 0;
		}else{
			$data['rank'] = 1;
		}
		$data['intro'] = htmlspecialchars($_POST['intro']);
		$data['be_limit_num'] = $limit_num;
		$data['be_active_name'] = $active_name;
		$data['be_apply_pkg'] = $apply_pkg;
		$data['be_usable'] = $usable;

		//$data['sim_content'] = $_POST['sim_content'];
		$data['be_cut_tm'] = strtotime(date('Y-m-d 23:59:59',strtotime($_POST['cut_tm'])));
		$data['exchange_start'] = strtotime($_POST['exchange_start']);
		$data['exchange_end'] = strtotime($_POST['exchange_end']);
		$data['usage'] = trim($_POST['usage']);
		$data['detail'] = trim($_POST['detail']);
		$have_result = $active_model -> table('sendNum.sendnum_active') -> where("active_name = '{$active_name}' and status != 0") -> select();
		if($have_result){
			$this -> error("对不起，标题不能相同");
		}
		$result = $active_model ->  add_active($data_active);

		$data['gift_url'] = "/market_gift_preview_{$result}.html";
		if($result){
			$data['active_id'] = $result;
			$active_result = $active_model -> add_active_content($data);
			/*$been_where['_string'] = "status = 1 and rank != 0 and active_id != {$result}";
			$have_been = $active_model -> table('olgame_active') -> where($been_where) -> select();*/
			$query ="select a.id,a.rank from olgame_active as a left join sendnum_active as b on a.active_id = b.id where b.status = 1 and a.rank != 0 and a.active_id != {$result}";
			$have_been = $active_model ->query($query);
			if($data['rank']!=0){

				foreach($have_been as $key => $val){
					$rank_result = $active_model -> table('olgame_active') -> where(array('id' => $val['id'])) -> save(array('rank' => $val['rank'] + 1));
					
				}

			}
			//激活码入口
			foreach($code_arr as $key => $val){
				if($val){
					$data_code['active_id'] = $result;
					$data_code['active_num'] = $val;
					$data_code['status'] = 0;
					$code_result = $active_model -> add_market_num($data_code,$result);
				}
			}
		}

		$static_file = $this -> active_module_url;
		$static_file_true = $this -> active_module_true_url;
		//$a = "<a href='javascript:window.AnzhiActivitys.downloadForActivity('{$pkg_result}')' class='down_btn' title='安智市场下载'>开始下载</a>";
		$js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
		preg_match ("/href=\"(.*)\"/Ui",$module_content,$my_a);
		$package = $my_a[1];
		$module_contents = str_replace($package , "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)" , $module_content);
		//生成静态页面
		ob_start();
		echo $this -> header;
		echo $js_a;
		echo $module_contents;
		$temp = ob_get_contents();
		ob_end_clean();
		if (!file_exists($static_file)){
			@mkdir(rtrim($static_file, '/'), 0777,true) or die("创建目录失败");
		}
		$fp = fopen($static_file."/market_gift_preview_{$result}.html",'w');
		$create_result = fwrite($fp,$temp);
		fclose($fp);
		if (!file_exists($static_file_true)){
			@mkdir(rtrim($static_file_true, '/'), 0777,true) or die("创建目录失败");
		}
		if($create_result){
			if(file_exists($static_file_true."/market_gift_preview_{$result}.html")){
				unlink($static_file_true."/market_gift_preview_{$result}.html");
			}
			if(!@copy($static_file."/market_gift_preview_{$result}.html",$static_file_true."/market_gift_preview_{$result}.html")){
				$this -> error("移动文件错误");
			}  //删除以前的文件并把新的html移动到指定位置
		}else{
			$this -> error("写入文件错误");
		}

		if($result){
			
			$this -> writelog("礼包管理:已添加id为{$result}的标题为{$active_name}的礼包","sendnum_active",$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Marketgift/market_gift_list');
			$this -> success("添加成功");
			
		}

	}



	//市场礼包编辑显示
	function market_edit_show(){
		$id = $_GET['id'];
		$active_model = D('sendNum.sendNum');
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this->assign('p',$_GET['p']);
		$this->assign('lr',$_GET['lr']);
		$result = $active_model -> table('olgame_active') -> where("active_id = {$id} and status != 0") -> select();
		//$active_result = $active_model -> table('sendnum_active') -> where("id => {$id} and status != 0") -> select();
		$sql = "select * from sendNum.sendnum_active as a left join sendNum.sendnum_gift_type as b on a.gift_type = b.id where a.`status` != 0 and a.id = {$id}";
		
		$active_result = $active_model->query($sql);
		foreach($result as $key => $val){
			$active_where['id'] = $val['active_id'];
		//	$active_result = $active_model -> table('sendnum_active') -> where($active_where) -> select();
			$val['cut_tm'] = $active_result[0]['end_tm'];
			$val['start_tm'] = $active_result[0]['start_tm'];
			$val['all_num'] = $active_result[0]['num_cnt'];
			$val['used_cnt'] = $active_result[0]['used_cnt'];
			$else_result = $active_model -> table('sendnum_number') -> where(array('active_id' => $val['active_id'],'status' => 1)) -> select();
			$val['surplus_num'] = $active_result[0]['num_cnt'] - $active_result[0]['used_cnt'];
			$val['limit_num'] = $active_result[0]['conf_cnt'];
			$val['active_name'] = $active_result[0]['active_name'];
			$val['sim_content'] = $result[0]['sim_content'];
			$val['sel_type'] = $active_result[0]['id'];
			if($active_result[0]['market_conf_cnt']==0){
				$val['market_conf_cnt'] == '';
			}else{
				$val['market_conf_cnt'] = $active_result[0]['market_conf_cnt'];
			}

			if($active_result[0]['game_conf_cnt']==0){
				$val['game_conf_cnt'] =='';
			}else{
				$val['game_conf_cnt'] = $active_result[0]['game_conf_cnt'];
			}

			if($active_result[0]['sdk_conf_cnt']==0){
				$val['sdk_conf_cnt'] =='';
			}else{
				$val['sdk_conf_cnt'] = $active_result[0]['sdk_conf_cnt'];
			}
			
			$result[$key] = $val;
		}
		$result_one = $active_model->table("sendNum.sendnum_gift_type")->where("status = 1")->select();
		$this->assign('result_one',$result_one);
		$this -> assign("result",$result);
		$this -> display();
	}


	//市场礼包编辑
	function market_edit_submit(){
		$active_model = D('sendNum.sendNum');
		$model = new Model();
		$active_name = trim($_POST['active_name']);
		$id = $_POST['id'];
		$usable = $_POST['usable'];
		$detail = trim($_POST['detail']);
		$usage = trim($_POST['usage']);
		$exchange_start = strtotime($_POST['exchange_start']);
		$exchange_end = strtotime($_POST['exchange_end']);
		$cut_tm = strtotime($_POST['cut_tm']);
		$start_tm = strtotime($_POST['start_tm']);
		$limit_num = $_POST['limit_num'];
		$sur_num = $_POST['sur_num'];
		$p = $_POST['p'];
		$lr = $_POST['lr'];

		if(!$active_name){
			$this -> error("标题不能为空");
		}
		
		$gift_type = $_POST['gift_type'];
		if($gift_type==0){
			$this -> error("请选择礼包类型");
		}

		if(mb_strlen($active_name,'utf-8') > 10){
			$this -> error("请输入10个字以内的名称");
		}
		if(!$usable){
			$this -> error("使用范围不能为空");
		}
		if(strlen($usable) > 30){
			$this -> error("请输入10个字以内的使用范围");
		}
		if ($detail=='') {
			$this -> error("礼包详情不能为空");
		}
		if($usage==''){
			$this -> error("使用方法不能为空");
		}
		if($start_tm==''){
			$this -> error("开始时间不能为空");
		}
		if($cut_tm==''){
			$this -> error("结束时间不能为空");
		}

		if($exchange_start==''){
			$this -> error("兑换开始时间不能为空");
		}
		if($exchange_end==''){
			$this -> error("兑换结束时间不能为空");
		}
		if ($exchange_start>$exchange_end) {
			$this -> error("兑换开始时间不能大于结束时间");
		}
		if(strtotime($_POST['exchange_start'])<$start_tm){
			$this -> error("兑换结束时间不能在兑换开始时间之前");
		}
	
		$apply_pkg = trim($_POST['apply_pkg']);
		$pkg_result = $model -> table('sj_soft') -> where(array('package' => $apply_pkg)) -> select();
		if(!$pkg_result){
			$this -> error("应用包名不存在");
		}

		if($cut_tm < $start_tm){
			$this -> error("截止时间不可小于开始时间");
		}
		$intro = trim($_POST['intro']);
		
		if(strlen($intro) > 150){
			$this -> error("简介不能大于50个汉字");
		}

		$been_num = $active_model -> table("sendnum_number_$id") -> where(array('active_id' => $id,'status' => 0)) -> select();


		$pre_path = $_SERVER['DOCUMENT_ROOT'];	
	
		foreach($matches[1] as $key => $val) {
			$upload_model = D("Dev.Uploadfile");
			$files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
		}
		foreach($matches[1] as $key => $val) {
			$files[$files_name[$key]] = '@'.$pre_path.$val;
		}

		$been_result = $active_model -> table('olgame_active') -> where(array('active_id' => $id)) -> select();
		$been_code = $active_model -> table('sendnum_active') -> where(array('id' => $id)) -> select();
		//数据写入
		$data_active['active_name'] = trim($active_name);
		$data_active['end_tm'] = strtotime($_POST['cut_tm']);
		$data_active['start_tm'] = strtotime($_POST['start_tm']);
		$data_active['gift_type'] = $_POST['gift_type'];
		if($data_active['end_tm'] > time() && $been_code[0]['end_tm'] < time()){
			$where_my['_string'] = "rank > 0 and status = 1";
			$my_result = $active_model -> table('olgame_active') -> where($where_my) -> select();
			foreach($my_result as $key => $val){
				$where_self['id'] = $val['id'];
				$data_self['rank'] = $val['rank'] + 1;
				$self_result = $active_model -> update_active_content($where_self,$data_self);
			}
			$data['rank'] = 1;
		}
		//$data_active['day_surplus_num'] = $limit_num - ($been_code[0]['limit_num'] - $been_code[0]['day_surplus_num']);
		$where_active['_string'] = "id = {$id} and status != 0";
		$have_result = $active_model -> table('sendNum.sendnum_active') -> where("active_name = '{$active_name}' and status != 0 and id != {$id}") -> select();

		if($have_result){
			$this -> error("对不起，标题不能相同");
		}
		$log_result = $this -> logcheck(array('id' => $id),'sendnum_active',$data_active,$active_model);
		$active_result = $active_model -> active_save($where_active,$data_active);
		
		$data['be_limit_num'] = $been_code[0]['conf_cnt'];
		$data['be_active_name'] = $been_code[0]['active_name'];
		$data['be_apply_pkg'] = $been_result[0]['apply_pkg'];
		$data['be_usable'] = $been_result[0]['usable'];
		$data['be_cut_tm'] = $been_code[0]['end_tm'];
		$data['apply_pkg'] = $apply_pkg;
		$data['usable'] = $usable;
		$data['intro'] = htmlspecialchars($intro);
		$data['detail'] = $detail;
		$data['usage'] = $usage;
		$data['exchange_start'] = $exchange_start;
		$data['exchange_end'] = $exchange_end;
		$data['update_tm'] = time();

		$where_content['_string'] = "active_id = {$id} and status != 0";
		$log_result_content = $this -> logcheck(array('active_id' => $id),'olgame_active',$data,$active_model);
		$content_result = $active_model -> update_active_content($where_content,$data);
	
		$static_file = $this -> active_module_url;
		$static_file_true =$this -> active_module_true_url;
		$js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
		preg_match ("/href=\"(.*)\"/Ui",$module_content,$my_a);
		$package = $my_a[1];
		$module_contents = str_replace($package , "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)" , $module_content);
		//生成静态页面
		ob_start();
		echo $this -> header;
		echo $js_a;
		echo $module_contents;
		$temp = ob_get_contents();
		ob_end_clean();
		if (!file_exists($static_file)){
			@mkdir(rtrim($static_file, '/'), 0777,true) or die("创建目录失败");
		}
		$fp = fopen($static_file."/market_gift_preview_{$id}.html",'w');
		$create_result = fwrite($fp,$temp);
		fclose($fp);
		if (!file_exists($static_file_true)){
			@mkdir(rtrim($static_file_true, '/'), 0777,true) or die("创建目录失败");
		}
		if(!$create_result){
			$this -> error("写入文件错误");
		}
		$been_result = $active_model -> table('sendNum.sendnum_active') -> where("id = {$id}") -> select();
		$from = $been_result[0]['status'];
	
		if($active_result || $content_result){
			$this -> writelog("礼包管理:已编辑id为{$id}的市场礼包".$log_result.$log_result_content,"sendnum_active",$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Marketgift/market_gift_list/from/{$from}/lr/{$lr}/p/{$p}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}



	//市场礼包_已过期
	function market_gift_list_last(){
		$active_model = D('sendNum.sendNum');
		$qu_array=array(
			"2"=>"安卓游戏",
			"4"=>"安智市场",
			"6"=>"安智市场,安卓游戏",
			"8"=>"SDK",
			"10"=>"安卓游戏,SDK",
			"12"=>"安智市场,SDK",
			"14"=>"安智市场,安卓游戏,SDK",
		);
		$no_where['_string'] = "status = 1 and end_tm <".time()."";
		$no_result = $active_model -> table('sendnum_active') -> where($no_where) -> select();
		foreach($no_result as $key => $val){
			$no_id_str .= $val['id'].',';
		}
		import("@.ORG.Page");
		$no_id = substr($no_id_str,0,-1);
		$where['_string'] = "status = 1 and active_id in ({$no_id})";
		$count = $active_model -> table('olgame_active') -> where($where) -> order('create_tm DESC') -> count();
		$Page = new Page($count,20,$limit);
		$result = $active_model -> table('olgame_active') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank') -> select();
		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		if(!$_GET['lr']){
			$_GET['lr'] =20;
		}
		$this -> assign("p",$_GET['p']);
		$this -> assign("lr",$_GET['lr']);
		foreach($result as $key => $val){
			$active_where['sendnum_active.id'] = $val['active_id'];
			//$active_result = $active_model -> table('sendnum_active') -> where($active_where) -> select();
			$active_result = $active_model -> table('sendnum_active') ->join("sendnum_gift_type on sendnum_active.gift_type = sendnum_gift_type.id")-> where($active_where) -> select();
			$val['num'] = $key+1+(($_GET['p']-1)*$_GET['lr']);
			$val['cut_tm'] = $active_result[0]['end_tm'];
			$val['start_tm'] = $active_result[0]['start_tm'];
			$val['all_num'] = $active_result[0]['num_cnt'];
			$else_result = $active_model -> table('sendnum_number') -> where(array('active_id' => $val['active_id'],'status' => 0)) -> select();
			$val['surplus_num'] = $active_result[0]['num_cnt'] - $active_result[0]['used_cnt'];
			$val['limit_num'] = $active_result[0]['conf_cnt'];
			$val['release_tm'] = $val['start_tm'];
			$val['active_name'] = $active_result[0]['active_name'];
			$val['active_status'] = $active_result[0]['status'];
			$val['active_from'] = $qu_array[$active_result[0]['active_from']];
			$val['gift_type'] = $active_result[0]['gift_type'];
			$result[$key] = $val;
		}
	
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');	
		$show = $Page->show();
		$this->assign('page',$show);
		$count = count($result);
		$this -> assign("count",$count);
		$this -> assign("result",$result);
		$this -> display();
	}


	//活动礼包删除
	function market_del(){
		$active_model = D('sendNum.sendNum');
		$id = $_GET['id'];
		$data_active['status'] = 0;
		$data['status'] = 0;
		$data['rank'] = 0;
		$data['update_tm'] = time();
		$where['id'] = $id;
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$out = $_GET['out'];
		$where_content['active_id'] = $id;
		$been_result = $active_model -> table('sendnum_active') -> where(array('id' => $id)) -> select();
		$change_result = $active_model -> table('olgame_active') -> where(array('active_id' => $id)) -> select();

		if($been_result[0]['status'] == 1){

			if($change_result[0]['rank']!= 0){
				$all_result = $active_model -> table('olgame_active') -> where("status = 1 and rank > {$change_result[0]['rank']}")	-> select();

				foreach($all_result as $key => $val){
					$where_change['id'] = $val['id'];
					$data_change['rank'] = $val['rank'] - 1;
					$result = $active_model -> update_active_content($where_change,$data_change);
				}
			}
		}
		$from = $been_result[0]['status'];
		$result_active = $active_model -> active_save($where,$data_active);
		$result_content = $active_model -> update_active_content($where_content,$data);
	
		if($result_active && $result_content){
			$this -> writelog("礼包管理:已删除id为{$id}的礼包","sendnum_active",$id,__ACTION__ ,"","del");
			if($_GET['out']==1){
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Marketgift/market_gift_list_last/from/{$from}/lr/{$lr}/p/{$p}");
			}else{
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Marketgift/market_gift_list/from/{$from}/lr/{$lr}/p/{$p}");
			}
			$this -> success("删除成功");
		}
	}



	//开启/关闭网游精选
	function market_change_selection(){
		$model = new Model();
		$active_model = D('sendNum.sendNum');
		$id = $_GET['id'];
		$have_been = $active_model -> table('olgame_active') -> where(array('id' => $id)) -> select();
		$one_been = $active_model -> table('olgame_active') -> where(array('selection' => 1,'status' => 1)) -> select();
		$where['id'] = $id;
		if($have_been[0]['selection'] == 1){
			$data = array(
				'selection' => 0
			);
			$log = "关闭";
		}else{
			$time_result = $active_model -> table('sendnum_active') -> where(array('id' => $have_been[0]['active_id'])) -> select();
			if($time_result[0]['end_tm'] < time()){
				$this -> error("已过期市场礼包不能成为精选网游");
			}
			if($one_been){
				$this -> error("只能开启一个市场礼包成为精选网游");
			}
			if(!$have_been[0]['intro']){
				$this -> error("您没有填写简介内容，请先填写内容后在开启");
			}
			$data = array(
				'selection' => 1
			);
			$log = "开启";
		}
		$result = $active_model -> update_active_content($where,$data);
		if($result){
			$this -> writelog("礼包管理:网游名称/icon配置_已{$log}id为{$id}的精选网游配置","sendnum_active",$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Marketgift/market_gift_list/from/2');
			$this -> success("编辑成功");

		}
	}




	function  edit_rank($flash){
		$model = D('sendNum.sendNum');
		if($flash=='flash'){
			$time = time();
			$sql ="select * from sendNum.sendnum_active as a left join sendNum.olgame_active as b on a.id = b.active_id where a.status = 1 and b.status = 1 and a.end_tm > $time order by b.rank limit 1";
			$first_one = $model ->query($sql);
			$target_id   = $first_one[0]['id'];
			$target_rank = $first_one[0]['rank'];
		}else{
			$target_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['rank'];
		}
		
		//$result = $active_model -> edit_rank_to($target_id,$target_rank);
		
		$weizhi = $model->table("sendNum.olgame_active")->order("rank desc")->find();
		$max = $weizhi['rank'];
		if($target_rank>$weizhi['rank']){
			$target_rank = $weizhi['rank'];
		}

		$table       = 'sendNum.olgame_active';
		$field       = 'rank';
		$lr = 0;
		$p = 0;
		if ($target_id <= 0 || $target_rank <= 0) {
			    $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Marketgift/market_gift_list/');
			    $this->error("ID为".$target_id."位置修改失败！");
			}
			
			$where = array(
				'status' => 1,
				'rank'=> array('exp','<='.$max.' and rank > 0'),
			);

			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$target_id,$where,$target_rank,$lr,$p,$model);
		    if(!$flash){
		    	$this -> writelog('礼包管理:更新了安卓游戏_礼包设置id为'.$target_id.'的排序为'."{$target_rank}","sendnum_active",$target_id,__ACTION__ ,"","edit");
		    	echo json_encode($param);
		    }
		   
		

	}



	function add_type_show(){
		$model = D('sendNum.sendNum');
		$gift_category = $_GET['gift_category'];
		$result = $model->table("sendNum.sendnum_gift_type")->where(array('gift_category' => $gift_category,'status' => 1))->select();
		$this ->assign('gift_category',$gift_category);
		$this->assign('result',$result);
		$this -> display();
	}	


	function type_add_submit(){
		$model = D('sendNum.sendNum');
		$gift_type = $_POST['type_name'];
		$gift_category = $_POST['gift_category'];
		$where['_string'] = "gift_type = '{$gift_type}' and status = 1 and gift_category = {$gift_category}";
		$where_count['_string'] = "status = 1 and gift_category = {$gift_category}";
		$count = $model->table("sendnum_gift_type")->where($where_count)->count();

		$find = $model->table("sendNum.sendnum_gift_type")->where($where)->find();
		if($find){
			$this->error('您填写的礼包类型已存在');
		}
		if($count>=4){
			$this -> error("只能配置4个礼包类型");
		}
		$data['gift_type'] = $gift_type;
		$data['gift_category'] = $gift_category;
		$data['status'] = 1;
		$add = $model->table("sendNum.sendnum_gift_type")->add_active($data);
		if($add){
			$this->writelog("礼包管理:-添加礼包类型{$gift_type}","sendnum_gift_type",$add,__ACTION__ ,"","add");
			$this->success("添加成功");
		}
		
	}


	function gift_type_del(){
		$model = D('sendNum.sendNum');
		$id = $_GET['id'];
		$data['status'] = 0;
		$ac_data['gift_type'] = 0;
		$result = $model->table("sendNum.sendnum_gift_type")->where("id = $id")->save($data);	
		if($result){
			$model->table("sendNum.sendnum_active")->where("gift_type = $id")->save($ac_data);
			$this->writelog("礼包管理:删除礼包类型ID为{$id}","sendnum_gift_type",$id,__ACTION__ ,"","del");
			$this->success("删除成功");
		}
	}


	function file_num(){
		
		$code_file = $_FILES;
		
		if($code_file['activation_code']['name']==''){
			echo '{"err":"1","error_con":"请上传激活码文件"}';
			return false;
		}
		if($code_file['activation_code']['size'] == 0){
			echo '{"err":"2","error_con":"激活码文件不能为空"}';
			return false;
		}
		$file_type = explode('.',$code_file['activation_code']['name']);
	
		if($file_type[1] != 'csv'){
			echo '{"err":"3","error_con":"请上传csv格式文件"}';
			return false;
		}

		$text = $_FILES['activation_code']['tmp_name'];

		$time = md5(time()+rand(1,999));
		$file_str = "/tmp/$time.csv";
		$move = move_uploaded_file($text,$file_str);
		$read_name = $file_str;
		
		$shili = fopen ($read_name,"r");  

			while ( !feof ( $shili )){
				$shi = fgets ( $shili,1024 );
			
				$a = explode(',',$shi);

				if($a[1]){
					$this -> error("激活码文件格式错误");
				}
				$str .= $shi.","; 

			}

			$str_arr = str_replace("\r\n", '', $str);

			$str_arrs = substr($str_arr,0,strlen($str_arr) - 1);

			$code_arrs = explode(',',$str_arrs);
		
			foreach($code_arrs as $key => $val){
				if(trim($val)!=""){
					$code_arr[$key] = $val;
				}
			}
		
		$count = count($code_arr);
		
		echo '{"out_count":"'.$count.'","new_file":"'.$file_str.'","new_file_name":"'.$time.'.csv"}';
	
	}

}