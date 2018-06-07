<?php
class SoftRecommedAction extends CommonAction {
	function soft_recommend_list(){
		$soft=M("soft");
		$sj_soft_recommend=M("soft_recommend");
		$extent_candidate=M("extent_candidate");
		$download_count=M("download_count");
		$operation_soft=M("soft_operation");
		$redis = new redis();
		$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
		$key=escape_string($_GET['key']);
		$where="status=1 and hide=1";

		$config_model = D('Sj.Config');
		$map = array(
			'config_type' => 'suggest_config',
			'status' => 1
		);
		$config = $config_model->where($map)->find();
		$config = json_decode($config['configcontent'], true);

		if($_GET['biao_key']==4){
			set_time_limit(0);
			$res = $soft->table('pu_config')->where("config_type='trend_config' and status=1")->find();
			$info = json_decode($res['configcontent'], true);
			$day = $info['day'];
			$start_date=date("Y-m-d",time() - $day * 86400);
			$todate = $info['todate'];
			$end_date=date("Y-m-d",time());
			$fromdate = strtotime($start_date);
			$todate = strtotime($end_date);

			$min_data = $info['min'];
			$max_data = $info['max'];
			$dir="/tmp/zh_recommend/";
			//$dir="D:/gongju/APMServ5.2.6/www/htdocs/wwwroot/newadmin.goapk.com/yan/";
			$file_txt=$dir.md5($start_date."_".$end_date."_".$min_data."_".$max_data).".txt";
			if(!file_exists($dir)){
				mkdir($dir,0755,TRUE);
			}
			if(file_exists($file_txt)){
				$str=file_get_contents($file_txt);
				$str_array=json_decode($str,true);
			}else{
				$conn = Db::getInstance()->connect(C('DB_STAT_BASE'));
				ini_set('memory_limit', '512M');
				$sql = "select package,submit_day, (mob_dl_cnt+mob_up_cnt) as total from sj_download_count where submit_day between  {$fromdate} and {$todate} ";
				$source =  mysql_query($sql, $conn);
				$soft_dl_cnt_arr = $aver_arr = $arfa_arr = $arfa_arr_b = $soft_arr = array();
				$package_info = array();
				while($result = mysql_fetch_assoc($source)){
					$pkg = $result['package'];
					$package_info[$pkg][] = array($result['submit_day'], $result['total']);
					unset($result);
				}
				foreach ($package_info as $pkg => $val) {
					$t = 0;
					foreach ($val as $v) {
						$t += $v[1];
					}
					$aver = $t / $day; //平均值
					if($aver > $max_data || $aver < $min_data) {
						unset($package_info[$pkg]);
						continue;
					}

					$aver_arr[$pkg] = $aver;
					list($arfa_arr[$pkg],$arfa_arr_b[$pkg])= $this->linear_regression($val);
				}
				//算出斜率并排序
				arsort($arfa_arr);
				$num=1;
				$conn = Db::getInstance()->connect(C('DB_SLAVE_BASE'));
				$zh_list=array();
				foreach($arfa_arr as $pkg=>$val){
					$soft_list[$num]['xie']=sprintf("%01.5f", $val*100).'%';
					$soft_list[$num]['num']=$num;
					$soft_list[$num]['ping']=ceil($aver_arr[$pkg]);
					$soft_list[$num]['package']=$pkg;
					$num++;
					if($num>100) break;
				}
				//$str_array=array();
				foreach($soft_list as $m=>$n){
					$str_array[]=$n;
				}
				$zh_str=json_encode($str_array);
				$file = fopen($file_txt,"w");
				fputs($file,$zh_str);
				fclose($file);
			}
			$conn = Db::getInstance()->connect(C('DB_SLAVE_BASE'));
			foreach($str_array as $m=>$n){
				$soft_sql="select softid,softname from sj_soft where status=1 and hide=1 and package='".$n['package']."'";
				$result_query = mysql_query($soft_sql,$conn);
				$soft_result = mysql_fetch_assoc($result_query);
				$str_array[$m]['softname']=$soft_result['softname'];
				$str_array[$m]['softid']=$soft_result['softid'];
				$str_array[$m]['rank']=$config['trend'];
				$str_array[$m]['date']=date('Y-m-d', $fromdate)." ~ ". date('Y-m-d', $todate);
				$str_array[$m]['number']=$min_data." ~ ".$max_data;
			}
			$str=json_encode($str_array);
			echo $str;
		}else{
			if($key==1){
				if(empty($_GET['soft_id']) && empty($_GET['soft_package'])){
					$this->error("请至少填写一个搜索条件");
				}
				if(!empty($_GET['soft_id'])){
					$softid=escape_string($_GET['soft_id']);
					$this->assign("softid", $softid);
					$where.=" and softid ='".$softid."'";
				}
				if (!empty($_GET['soft_package'])) {
				$soft_package=escape_string($_GET['soft_package']);
				$this->assign("soft_package",$soft_package);
				$where.=' and package like "%' . trim($soft_package) . '%"';
				}
				$result=$soft->field("package")->where($where)->select();
				if($result){
					$this->assign("biao",1);
					$this->assign("soft_package",$result);
				}else{
					$this->error("没有搜到为当前条件下的软件");
				}
			}
			$zh_type=$_GET['zh_type'];
			if(empty($zh_type)){
				$zh_type=1;
			}

			$time = $_GET['select_time'];
			if($time == 1 || $time == ""){
				$soft_map['_string'] = " status = 1 ";
			}elseif($time == 2){
				$soft_map['_string'] = " start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			}elseif($time == 3){
				$soft_map['_string'] = " start_tm > ".time()." and status = 1 ";
			}elseif($time == 4){
				$soft_map['_string'] = " end_tm < ".time()." and status = 1 ";
			}
			if($zh_type==1){
				$soft_recommend_list=$sj_soft_recommend->where($soft_map)->select();
				foreach($soft_recommend_list as $k=>$info){
					$soft_list[$k]['package']=$info['soft_package'];
					$zh_where['status']=1;
					$zh_where['hide']=1;
					$zh_where['package']=$info['soft_package'];
					$soft_result=$soft->field("softid,softname")->where($zh_where)->select();
					$soft_list[$k]['softid']=$soft_result[0]['softid'];
					$soft_list[$k]['softname']=$soft_result[0]['softname'];
					$soft_list[$k]['rank']=$info['rank'];
					$soft_list[$k]['id']=$info['id'];
					$soft_list[$k]['start_tm']=$info['start_tm'];
					$soft_list[$k]['end_tm']=$info['end_tm'];
				}
			}elseif($zh_type==2){
				$soft_where['status']=1;
				$soft_where['start_at']=array('elt',time());
				$soft_where['end_at']=array('egt',time());
				$result=$extent_candidate->field("package")->where($soft_where)->select();
				foreach($result as $k=>$val){
					$zh_where['status']=1;
					$zh_where['hide']=1;
					$zh_where['package']=$val['package'];
					$soft_result=$soft->field("softid,softname")->where($zh_where)->select();
					$soft_list[$k]['softid']=$soft_result[0]['softid'];
					$soft_list[$k]['softname']=$soft_result[0]['softname'];
					$soft_list[$k]['package']=$val['package'];
					$soft_list[$k]['rank']=$config['candidate'];
				}
				$pos = $config['candidate'];
			}elseif($zh_type==3){
				$soft_where['status']=1;
				$soft_where['hide']=1;
				$key_soft_a="SOFTLIST_CATEGORY_SOFTID_1_HOT";
				$key_soft_b="SOFTLIST_CATEGORY_SOFTID_2_HOT";
				$soft_repalce_a=$redis->get($key_soft_a);
				$soft_array_a=json_decode($soft_repalce_a,true);
				$a_num=0;
				$list_a=array();
				foreach($soft_array_a as $ka=>$a_val){
					if(isset($list_a[$a_val])){
						continue;
					}
					$list_a[$a_val]=1;
					$a_num++;
					if($a_num>=100) break;
				}
				$soft_array_a=array_keys($list_a);
				$soft_repalce_b=$redis->get($key_soft_b);
				$soft_array_b=json_decode($soft_repalce_b,true);
				$b_num=0;
				$list_b=array();
				foreach($soft_array_b as $kb=>$b_val){
					if(isset($list_b[$b_val])){
						continue;
					}
					$list_b[$b_val]=1;
					$b_num++;
					if($b_num>=100) break;
				}
				$soft_array_b=array_keys($list_b);
				foreach($soft_array_a AS $n=>$m)
					{
					 $soft_array[] = $m;
					 $soft_array[] = $soft_array_b[$n];
					}
				foreach($soft_array as $k=>$val){
					$soft_where['package']=$val;
					$soft_result=$soft->field("softid,softname")->where($soft_where)->select();
					$soft_list[$k]['softid']=$soft_result[0]['softid'];
					$soft_list[$k]['softname']=$soft_result[0]['softname'];
					$soft_list[$k]['package']=$val;
					$soft_list[$k]['rank']=$config['top'];
				}
				$pos = $config['top'];
			}elseif($zh_type==4){
				$pos = $config['trend'];
			}elseif($zh_type==5){
				$operation_where['status'] = 1;
				$sel = M('product');
        		$pid = $_GET['pid'];
        		$pid = $pid=='' ? 1: $pid;
        		$sel_list = $sel ->table('pu_product')->where('status = 1')->findAll();
				$soft_list = $operation_soft->table('pu_product')->join('sj_soft_operation on sj_soft_operation.pid = pu_product.pid')->where("sj_soft_operation.status = 1 and sj_soft_operation.pid =$pid")->order('sj_soft_operation.create_tm desc')->select();
				//$soft_list = $operation_soft -> where($operation_where) -> order('create_tm DESC') -> select();
			}
			$this->assign ('pid',$pid);
			$this->assign ("sel_list", $sel_list);
			$this->assign("zh_type",$zh_type);
			$this->assign("list",$soft_list);
			$this->assign("config",$config);
			$this->assign("pos",$pos);
			$this -> assign("time",$time);
			$this->display();
		}
	}

	function soft_operation_add(){
		$sel = M('product');
		$sel_id = substr($_GET['pid'],0,1);
		$this->assign ('p_id',$sel_id);
		$sel_list = $sel ->table('pu_product')->where('status = 1')->findAll();
		$this->assign ("sel_list", $sel_list);
		$this -> display("soft_operation_add");
	}
	function soft_operation_add_do(){
		$model = new Model();
		$package = $_GET['operation_soft'];
		$pid = $_GET['pid'];
		$where_go['package'] = $package;
		$where_go['status'] = 1;
		$soft_go = $model -> table('sj_soft') -> where($where_go) -> field('softid,softname,package') -> select();
		$soft_to = $model -> table('sj_soft_operation') -> where($where_go) -> field('softid') -> select();
		if($soft_to){
			$this -> error("对不起，该软件已存在于运营推荐软件列表");
		}

		if($soft_go){
			$map['softid'] = $soft_go[0]['softid'];
			$map['softname'] = $soft_go[0]['softname'];
			$map['package'] = $soft_go[0]['package'];
			$map['status'] = 1;
			$map['pid'] = $pid;
			$map['update_tm'] = time();
			$map['create_tm'] = time();
			$affect = $model -> table('sj_soft_operation') -> add($map);
			if($affect){
				$this -> writelog("已添加运营推荐软件，软件id为".$map['softid'].",软件名为".$map['softname'].",软件包名为".$map['package']."",'sj_soft_operation',$affect,__ACTION__ ,"","add");
				$this -> success("添加运营推荐软件成功");
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list/zh_type/5');
			}
		}else{
			$this -> error('对不起，此软件不存在于软件列表中');
		}

	}

	function soft_operation_del(){
		$model = new Model();
		$softid = $_GET['softid'];
		$where['softid'] = $softid;
		$map['status'] = 0;
		$map['update_tm'] = time();
		if($softid){
			$pakage = $model -> table('sj_soft_operation') -> where($where) -> find();
			$affect = $model -> table('sj_soft_operation') -> where($where) -> save($map);
		}
		if($affect){
			$this -> writelog("已删除运营推荐软件，软件id为".$softid."包名为".$pakage['package'],'sj_soft_operation',$softid,__ACTION__ ,"","del");
			$this -> success("删除成功");
			$this -> assign("jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list/zh_type/5");
		}

	}
	function soft_recommend_add(){
        // 业务逻辑：trim一下需要用到的数据
        $useful_key = array('package', 'rank', 'fromdate', 'todate');
        foreach($useful_key as $key=>$value) {
            if (isset($_GET[$value]))
                $_GET[$value] = trim($_GET[$value]);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $_GET;
        $error_msg = $this->logic_check($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->assign('jumpUrl', '/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list');
            $this->error($msg);
        }
    
		//print_r($_POST);
		$sj_soft_recommend=M("soft_recommend");
        /*
		$config_model = D('Sj.Config');
		$soft_search = M("soft");
		$map = array(
			'config_type' => 'suggest_config',
			'status' => 1
		);
		
		$where['soft_package']=$_GET['soft_package'];
		$where_go['package'] = $_GET['soft_package'];
		
		$where['status']=1;
		$soft_result = $soft_search -> where($where_go) -> select();
		if(!$soft_result){
			$this -> error("对不起，此软件不存在于软件列表中");
		}
		if($_GET['rank']>10){
			$this->error("位置选择有误，请选择在10以内的位置");
		}
		$config = $config_model->where($map)->find();
		$config = json_decode($config['configcontent'], true);
		foreach($config as $m=>$n){
			$str .=$n.",";
		}
		if(in_array($_GET['rank'],$config)){
			$this->error("位置选择有误，请不要选择".$str);
		}
		$count=$sj_soft_recommend->where($where)->count();
		if($count>0){
			$this->error("添加软件已经存在，请重新选择！！！");
		}
        */
        $info_list = array();
        $info_list['rank']=$_GET['rank'];
        $info_list['soft_package']=$_GET['soft_package'];
		$info_list['start_tm']=strtotime($_GET['fromdate']);
		$info_list['end_tm']=strtotime($_GET['todate']);
		$info_list['status']=1;
		$info_list['create_tm']=time();
		$info_list['update_tm']=time();
		if($info_list['soft_package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($info_list['soft_package'],$info_list['start_tm'],$info_list['end_tm']);
			if($shield_error){
			    $this -> error($shield_error);
			}
		}
		
		if($id=$sj_soft_recommend->add($info_list)) {
			$this->writelog('增加了ID为['.$id.']包名为['.$info_list['soft_package'].']的软件推荐,开始时间为'.$_GET['fromdate'].',结束时间为'.$_GET['todate'], 'sj_soft_recommend', $id,__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list');
			$this->success("添加软件推荐位置成功！");
		}else
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list');
			$this->error("添加软件推荐位置失败,发生错误！");
		}
	}
	//趋势软件条件添加
	function add_soft_list(){
		$model = D('Sj.Config');
		$soft=M("soft");
		$dir="/tmp/zh_recommend/";
		//$dir="D:/gongju/APMServ5.2.6/www/htdocs/wwwroot/newadmin.goapk.com/yan/";
		if($_POST){
			/*
			if(!empty($_POST['fromdate'])){
				$fromdate=strtotime($_POST['fromdate']." 00:00:00");
			}
			if(!empty($_POST['todate'])){
				$todate=strtotime($_POST['todate']." 23:59:59");
			}
			*/
			$min_data=$_POST['min'];
			$max_data=$_POST['max'];
			$day=$_POST['day'];
			$zh_type=$_POST['zh_type'];
			$info = array(
				//'fromdate' => $fromdate,
				//'todate' => $todate,
				'day' => $day,
				'min' => $min_data,
				'max' => $max_data,
			);
			$info = json_encode($info);
			$res = $soft->table('pu_config')->where("config_type='trend_config' and status=1")->find();
			$data = array(
				'uptime' => time(),
				'config_type' => 'trend_config',
				'configname' => 'trend_config',
				'configcontent' => $info,
				'status' => 1,
			);
			if ($res) {
				$result=$model->where("config_type='trend_config' and status=1")->save($data);
			} else {
				$result=$model->data($data)->add();
			}
			if($result){
					$this->del_dir($dir);
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list/zh_type/'.$zh_type);
					$this->success("设置成功");
				}else{
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list/zh_type/'.$zh_type);
					$this->error("失败");
				}
		}else{
			$res = $model->where("config_type='trend_config'")->find();
			if ($res) {
				$info = json_decode($res['configcontent'], true);
				//$fromdate = trim($info['fromdate']);
				//$todate = trim($info['todate']);
				$min_data = $info['min'];
				$max_data = $info['max'];
				$day = $info['day'];

				$this->assign('min', $min_data);
				$this->assign('max', $max_data);
				$this->assign('day', $day);
				//$this->assign('fromdate', date('Y-m-d', $fromdate));
				//$this->assign('todate', date('Y-m-d', $todate));
			} else {
				$this->assign('min', 1000);
				$this->assign('max', 2000);
				$this->assign('day', 3);
				//$this->assign('fromdate', date('Y-m-d', time() - 86400 * 7 ) );
				//$this->assign('todate', date('Y-m-d', time()) );
			}

			$zh_type = $_GET['zh_type'];
			$this->assign("zh_type", $zh_type);
			$this->display();
		}
	}
	/* function add_soft_list_to(){
	} */
	function soft_recommend_edit(){
		$sj_soft_recommend=M("soft_recommend");
		$id=$_GET['id'];
		$map['id']=$id;
		$map['status']=1;
		$soft_recommend_list=$sj_soft_recommend->where($map)->find();
		$this->assign("list",$soft_recommend_list);
		$this->display();
	}
	function soft_recommend_editto(){
        // 业务逻辑：trim一下需要用到的数据
        $useful_key = array('id', 'package', 'rank', 'fromdate', 'todate');
        foreach($useful_key as $key=>$value) {
            if (isset($_POST[$value]))
                $_POST[$value] = trim($_POST[$value]);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $_POST;
        $error_msg = $this->logic_check($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->assign('jumpUrl', '/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list');
            $this->error($msg);
        }
    
		$sj_soft_recommend=M("soft_recommend");
        /*
		$config_model = D('Sj.Config');
		$zh_map = array(
			'config_type' => 'suggest_config',
			'status' => 1
		);
		$sj_soft_recommend=M("soft_recommend");
        
		if($_POST['rank']>10){
			$this->error("位置选择有误，请选择在10以内的位置");
		}
		$config = $config_model->where($zh_map)->find();
		$config = json_decode($config['configcontent'], true);
		foreach($config as $m=>$n){
			$str .=$n.",";
		}
		if(in_array($_POST['rank'],$config)){
			$this->error("位置选择有误，请不要选择".$str);
		}
        */
        $data['soft_package']=$_POST['soft_package'];
		$data['rank']=$_POST['rank'];
		$data['start_tm']=strtotime($_POST['fromdate']);
		$data['end_tm']=strtotime($_POST['todate']);
		$data['update_tm']=time();
		$map['id']=$_POST['id'];
		$map['status']=1;
		if($data['soft_package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['soft_package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
			    $this -> error($shield_error);
			}
		}
		
		$log_result = $this->logcheck(array('id'=>$_POST['id']),'sj_soft_recommend',$data,$sj_soft_recommend);
		$affect = $sj_soft_recommend -> where($map) -> save($data);
		if($affect){
			$this->writelog("已修改软件推荐设置id为{$_POST['id']},".$log_result,'sj_soft_recommend',$_POST['id'],__ACTION__ ,"","edit");
			//$this->writelog('修改了ID为['.$_POST['id'].']包名为['.$data['soft_package'].']的运营推荐信息', 'sj_soft_recommend', $data['soft_package']);
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list');
			$this->success('修改成功');
		}
	}
	
	function soft_recommend_del(){
		$sj_soft_recommend=M("soft_recommend");
		$id=$_GET['id'];
		$result=$sj_soft_recommend->where(array("id"=>$id,"status"=>1))->find();
		if(empty($id)){
			$this->error("删除错误，请重新删除");
		}
		$update['status'] = 0;
		$update['update_tm'] = time();
		$map['id']=$id;
		$map['status']=1;
		$affect = $sj_soft_recommend -> where($map) -> save($update);
		if($affect){
			$this->writelog('市场软件运营推荐-用户还下载设置:删除了ID为['.$id.']包名为['.$result['soft_package'].']的运营推荐信息', 'sj_soft_recommend',$id,__ACTION__ ,"","del");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/SoftRecommed/soft_recommend_list');
			$this->success('删除成功');
		}
	}
	function linear_regression($arr) {
		$n = 0;
		$sum_x = 0;
		$sum_y = 0;
		$sum_x_mult_y = 0;
		$sum_x_power_2 = 0;
		foreach ($arr as $coord) {
			$sum_x += $coord[0];
			$sum_y += $coord[1];
			$sum_x_mult_y += $coord[0] * $coord[1];
			$sum_x_power_2 += $coord[0] * $coord[0];
			$n += 1;
		}
		$avg_x = $sum_x / $n;
		$avg_y = $sum_y / $n;
		$a = ($sum_x_mult_y - $n * $avg_x * $avg_y) / ($sum_x_power_2 - $n * $avg_x * $avg_x);
		$b = $avg_y - $a * $avg_x;
		# y = a * x + b
		#return ($a * $x + $b);
		return array($a,$b);
	}
	/* function soft_recommend_get_data(){

	} */
	function save_list_suggest_order()
	{
		$used_pos = array();
		$model = D('Sj.Config');
		$map = array(
			'config_type' => 'suggest_config',
			'status' => 1
		);
		$config = $model->where($map)->find();
		$config = json_decode($config['configcontent'], true);
		if (!is_array($config)) {
			$config = array();
		}
		foreach($config as $val) {
			$used_pos[$val] = 1;
		}
		$now = time();
		$sql = "select rank from sj_soft_recommend where status=1 and start_tm<={$now} and end_tm>={$now} group by rank";
		$res = $model->query($sql);
		foreach ($res as $val) {
			$used_pos[$val['rank']] = 1;
		}

		if (isset($used_pos[$_POST['pos']])) {
			exit('该位置已经被占用');
		}
		
		
		$type = intval($_POST['type']);
		switch($type) {
			case 2:
				$config['candidate'] = $_POST['pos'];
			break;
			
			case 3:
				$config['top'] = $_POST['pos'];
			break;
			
			case 4:
				$config['trend'] = $_POST['pos'];
			break;
		}

		$config = json_encode($config);
		$data = array(
			'configcontent' => $config
		);
		$model->where($map)->save($data);
	}
	//删除过期文件
	public function del_dir($dir){	//删除目录
		if(!($mydir=@dir($dir))){
			return;
		}
		while($file=$mydir->read()){
			if(is_file($dir.$file)){
			@chmod($dir.$file, 0777);
			@unlink($dir.$file);
			}
		}
		$mydir->close();
		@chmod($dir, 0777);
		//@rmdir($dir);
	}
    
    
    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
    
    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'soft_package'  =>   '包名',
            'rank' => '位置',
            'fromdate'  =>   '开始时间(yyyy/MM/dd)',
            'todate'  =>   '结束时间(yyyy/MM/dd)',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
                // 自动填充批量导入的时间
                if ($r_key == 'fromdate' || $r_key == 'todate') {
                    if ($r_key == 'fromdate') {
                        $type = 0;
                        $hint = '开始';
                    } else {
                        $type = 1;
                        $hint = '结束';
                    }
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $this->append_error_msg($error_msg, $key, 1, "{$hint}时间需以T开头;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }// else转换错误，保持原始值，后面的logic_check会校验原始格式
                }
            }
        }
        return $error_msg;
    }
    
    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：软件推荐表、配置表
        $M_soft_recommend = 'soft_recommend';
        $recommend_model = M($M_soft_recommend);
        $config_model = D('Sj.Config');
        
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'extent_v1';
        $M_extent_soft_table = 'extent_soft_v1';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查包名是否在sj_soft表里
            if (isset($record['soft_package']) && $record['soft_package'] != "") {
                $where = array(
                    'package' => $record['soft_package'],
                    'status' => 1,
                );
                $find = $soft_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['soft_package']}】不存在于市场软件库中;");
                } else {
                    // 做一下标记，表示这个包存在市场中，方便后面导入的冲突检查
                    $content_arr[$key]['exist'] = 1;
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
            // 检查位置
            if (isset($record['rank']) && $record['rank'] != "") {
                if (!preg_match("/^\d+$/", $record['rank'])) {
                    // 不是数字
                    $this->append_error_msg($error_msg, $key, 1, "位置值应为整数;");
                } else if ($record['rank'] > 10 || $record['rank'] < 1) {
                    // 不在1-10之间
                    $this->append_error_msg($error_msg, $key, 1, "位置值应在1-10之间;");
                } else {
                    // 判断是否处于特殊位置
                    $special_rank_where = array(
                        'config_type' => 'suggest_config',
                        'status' => 1
                    );
                    $config = $config_model->where($special_rank_where)->find();
                    $config = json_decode($config['configcontent'], true);
                    $str = "";
                    foreach($config as $m=>$n){
                        $str .=$n.",";
                    }
                    if(in_array($record['rank'],$config)){
                        $this->append_error_msg($error_msg, $key, 1, "位置选择有误，请不要选择".$str.";");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "位置值不能为空;");
            }
            // 检查开始时间
            if (isset($record['fromdate']) && $record['fromdate'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['fromdate'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['fromdate']);
                    if ($time) {
                        $start_time = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['todate']) && $record['todate'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['todate'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['todate']);
                    if ($time) {
                        $end_time = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($start_time) && isset($end_time)) {
                if ($start_time > $end_time) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
        }
        
        // 检查行与行之间的数据是否冲突（主要检查不能有包名相同的行）
        foreach($content_arr as $key1=>$record1) {
            // 如果包无效，则不比较
            if (!isset($record1['exist']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果包无效，则不比较
                if (!isset($record2['exist']))
                    continue;
                // 包相同，提示
                if ($record1['soft_package'] == $record2['soft_package']) {
                    $k1 = $key1 + 1;
                    $k2 = $key2 + 1;
                    $this->append_error_msg($error_msg, $key1, 1, "包名与第{$k2}行有重叠;");
                    $this->append_error_msg($error_msg, $key2, 1, "包名与第{$k1}行有重叠;");
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 如果包无效，则不比较
            if (!isset($record['exist']))
                continue;
            $where = array(
                'soft_package' => $record['soft_package'],
                'status' => array(array('EQ', 1), array('EQ', 2), 'OR'),
            );
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $find = $recommend_model->where($where)->find();
            if($find){
                $status_paused_hint = "";
                if ($find['status'] == 2) {
                    $status_paused_hint = "，其处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "该包名已经添加过{$status_paused_hint};");
            }
        }
        return $error_msg;
    }
    
    // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
            }
        }
        return $content_arr;
    }
    
    function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    
    // 下载批量导入模版
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "cainixihuan_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('猜你喜欢批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }
    
    // 批量导入访问的页面节点
    function import_softs() {
        if ($_GET['down_moban']) {
            $this->down_moban();
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_soft_recommend_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            $ret = move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("用户还下载设置：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $model = M('soft_recommend');
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			$map['create_tm'] = time();
			$map['update_tm'] = time();
            // 赋值，以下为必填的值
			$map['soft_package'] = $record['soft_package'];
			$map['rank'] = $record['rank'];
			$map['start_tm'] = strtotime($record['fromdate']);
			$map['end_tm'] = strtotime($record['todate']);
            
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('增加了ID为['.$id.']包名为['.$map['soft_package'].']的软件推荐', 'sj_soft_recommend', $id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} else {
                // 未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
			}
        }
        return $result_arr;
    }

    
}

