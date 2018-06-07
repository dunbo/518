<?php
class MonitoringSoftAction extends CommonAction {
    
	function monitor_white(){//可疑IP列表
		ini_set('memory_limit', '512M');
		$model = new Model();
		$start_time = strtotime(trim($_GET['stime']));
		$end_time = strtotime(trim($_GET['etime']));
		$ip = trim($_GET['ip']);
		import('@.ORG.Page2');// 导入分页类
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		
		if(!empty($order_type) && !empty($order_field)){
			$order = "$order_field   $order_type";
			$url_field = trim($_GET['order']);
			$url_type = trim($_GET['order_type']);
			$this->assign('url_field',$url_field);
			$this->assign('url_type',$url_type);
		}else{
			$order = "add_time DESC";
		}
		//echo $order.'<br>';
		if((!empty($start_time) && !empty($end_time)) || !empty($ip)){
			$seach = '';
			$seach.= (!empty($start_time) && !empty($end_time))?"sj_monitor.add_time>='{$start_time}' and sj_monitor.add_time<='{$end_time}'":'';
			if(!empty($start_time) && !empty($end_time) && !empty($ip)){
				$seach.=" and ";
			}
			$seach.= !empty($ip)?"sj_monitor.ip='{$ip}'":'';
			/*分页开始*/
			$total = $model->table('sj_monitor')->where($seach)->select();// 查询满足要求的总记录数
	   	    $count =   count($total);
	   	    $Page       = new Page($count,100);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			/*分页结束*/
			$res = $model->table('sj_monitor')->field('sj_monitor.id,sj_monitor.add_num,sj_monitor.ip,sj_monitor.reg_num,sj_monitor.comment_num,sj_monitor.soft_num,sj_monitor.dev_monitor,sj_monitor.soft_monitor,sj_monitor.comment_monitor,sj_monitor.add_time,sj_monitor_black.black_num,sj_monitor_black.black_time_start,sj_monitor_black.black_time_end,sj_monitor_black.status')->join('sj_monitor_black on sj_monitor.ip = sj_monitor_black.ip')->where($seach)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
			//$res = $model->table('sj_monitor')->limit($Page->firstRow.','.$Page->listRows)->order($order)->where($seach)->select();
			//echo $model->getLastSql().'<br>';
		}else{
			/*分页开始*/
			$total = $model->table('sj_monitor')->select();// 查询满足要求的总记录数
	   	    $count = count($total);
	   	    $Page  = new Page($count,100);// 实例化分页类 传入总记录数和每页显示的记录数
			$show  = $Page->show();// 分页显示输出
			/*分页结束*/
			$res = $model->table('sj_monitor')->field('sj_monitor.id,sj_monitor.add_num,sj_monitor.ip,sj_monitor.reg_num,sj_monitor.comment_num,sj_monitor.soft_num,sj_monitor.dev_monitor,sj_monitor.soft_monitor,sj_monitor.comment_monitor,sj_monitor.add_time,sj_monitor_black.black_num,sj_monitor_black.black_time_start,sj_monitor_black.black_time_end,sj_monitor_black.status')->join('sj_monitor_black on sj_monitor.ip = sj_monitor_black.ip')->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
			//echo $model->getLastSql().'<br>';
		}
		//$res_black = $model->field('monitor_id,black_num')->order($order_black)->table('sj_monitor_black')->select();
		//echo $model->getLastSql().'<br>';
		/*$black_arr = array();
		foreach ($res_black as $val){
			$black_arr[$val['monitor_id']][]= $val['black_num'];
		}
		foreach ($res as $key=>$val){
			$res[$key]['black_num'] = $black_arr[$val['id']][0]?$black_arr[$val['id']][0]:0;
		}*/
		//var_dump($res);
		$arr = array();
		$time = time();
		//添加次数
		$add_num_arr = array();
		$add_num = $model->field('id,ip')->table('sj_monitor')->select();
		foreach ( $add_num as $val ) { //按IP下所有软件统计
			$add_num_arr [$val ['ip']][] = $val ['id'];
		}
		$res_add_num = array ();
		foreach ( $add_num_arr as $key => $val ) { //按IP下所有软件统计
			$res_add_num [$key] = count($val);
		}
		foreach ($res as $val){
			if($time<$val['black_time_start'] || $time>$val['black_time_end'] || $val['status']<1){
				$is_black_button = 1;//不在加黑有效期
			}else{
				$is_black_button = 0;
			}
			$arr[]=array('id'=>$val['id'],'ip'=>$val['ip'],'reg_num'=>$val['reg_num'],'comment_num'=>$val['comment_num'],'soft_num'=>$val['soft_num'],'dev_monitor'=>$val['dev_monitor'],'soft_monitor'=>$val['soft_monitor'],'comment_monitor'=>$val['comment_monitor'],'add_time'=>$val['add_time'],'black_num'=>$val['black_num'],'is_black_button'=>$is_black_button,'add_num'=>$val['add_num']);
		}
		$default_start_time = date('Y-m-d H:i:s',time());
		$default_end_time = date('Y-m-d H:i:s',time()+3600*24*20);
		$this->assign('default_start_time',$default_start_time);
		$this->assign('default_end_time',$default_end_time);
		$this->assign('list',$arr);
		$this->assign('status',1);
		$this->assign('count',$count);
		$this->assign('page',$show);// 赋值分页输出
		$this -> display();
	}
	function monitor_black(){ //黑名单列表
		$model = new Model();
		$start_time = strtotime(trim($_GET['stime']));
		$end_time = strtotime(trim($_GET['etime']));
		$ip = trim($_GET['ip']);
		$status = trim($_GET['status']);
		$black_num = trim($_GET['black_num']);
		$time = time();
		$order_field = trim($_GET['order']);
		$order_type = trim($_GET['order_type']);
		if(!empty($order_type) && !empty($order_field)){
			$order = "$order_field   $order_type";
			$url_field = trim($_GET['order']);
			$url_type = trim($_GET['order_type']);
			$this->assign('url_field',$url_field);
			$this->assign('url_type',$url_type);
		}else{
			$order = "black_time DESC";
		}
		import('@.ORG.Page2');// 导入分页类
		if((!empty($start_time) && !empty($end_time)) || !empty($ip) || !empty($status) || !empty($black_num)){
			$seach = '';
			if($status==2){ //无效
				$seach.= "and (sj_monitor_black.black_time_start>'{$time}' or sj_monitor_black.black_time_end<'{$time}')";
				
			}
			if($status==1){ //有效
				$seach.= "and sj_monitor_black.black_time_start<='{$time}' and sj_monitor_black.black_time_end>='{$time}' ";
			}
			$seach.= (!empty($start_time) && !empty($end_time))?" and sj_monitor_black.black_time_start>='{$start_time}' and sj_monitor_black.black_time_end<='{$end_time}' ":'';
			$seach.= !empty($black_num)?"and  sj_monitor_black.black_num>='{$black_num}'":'';
			$seach.= !empty($ip)?" and sj_monitor_black.ip='{$ip}'":'';
			/*分页开始*/
			$total =    $model->table('sj_monitor_black')->where("status=1 and {$seach}")->join('sj_monitor on sj_monitor.id = sj_monitor_black.monitor_id')->select();// 查询满足要求的总记录数
	   	    $count =     count($total);
	   	    $Page       = new Page($count,100);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			/*分页结束*/
			$res = $model->table('sj_monitor_black')->limit($Page->firstRow.','.$Page->listRows)->field('sj_monitor_black.ip as ip,sj_monitor_black.monitor_id,	sj_monitor_black.black_num,sj_monitor_black.black_time,sj_monitor_black.black_time_start,sj_monitor_black.black_time_end,sj_monitor_black.black_reason,sj_monitor_black.status,sj_monitor.reg_num,sj_monitor.comment_num,sj_monitor.soft_num')->order($order)->where("sj_monitor_black.status=1 {$seach}")->join('sj_monitor on sj_monitor.id = sj_monitor_black.monitor_id')->select();
			//echo $model->getLastSql().'<br>';
		}else{
			/*分页开始*/
			$total =    $model->table('sj_monitor_black')->where("status=1")->join('sj_monitor on sj_monitor.id = sj_monitor_black.monitor_id')->select();// 查询满足要求的总记录数
	   	    $count =     count($total);
	   	    $Page       = new Page($count,100);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			/*分页结束*/
			$res = $model->table('sj_monitor_black')->limit($Page->firstRow.','.$Page->listRows)->field('sj_monitor_black.ip as ip,sj_monitor_black.monitor_id,	sj_monitor_black.black_num,sj_monitor_black.black_time,sj_monitor_black.black_time_start,sj_monitor_black.black_time_end,sj_monitor_black.black_reason,sj_monitor_black.status,sj_monitor.reg_num,sj_monitor.comment_num,sj_monitor.soft_num')->order($order)->where("sj_monitor_black.status=1 and sj_monitor_black.black_time_start<='{$time}' and sj_monitor_black.black_time_end>='{$time}' ")->join('sj_monitor on sj_monitor.id = sj_monitor_black.monitor_id')->select();
			//echo $model->getLastSql().'<br>';
		}
		
		$arr = array();
		foreach ($res as $val){
			$str = '';
			foreach (explode(',', $val['black_reason']) as $val1){
				$str.=$val1.'<br>';
			}
			 
			if($time< $val['black_time_start'] || $time>$val['black_time_end']){
				$is_status = '无效';
			}else{
				$is_status = '有效';
			}
			$arr[] = array('id'=>$val['id'],'ip'=>$val['ip'],'monitor_id'=>$val['monitor_id'],'black_num'=>$val['black_num'],'black_time'=>$val['black_time'],'black_time_start'=>$val['black_time_start'],'black_time_end'=>$val['black_time_end'],'black_reason'=>$str,'status'=>$val['status'],'reg_num'=>$val['reg_num']?$val['reg_num']:0,'comment_num'=>$val['comment_num']?$val['comment_num']:0,'soft_num'=>$val['soft_num']?$val['soft_num']:0,'is_status'=>$is_status);
		}
		$this->assign('seach_status',$status);
		$this->assign('add_start_time',date("Y-m-d H:i:s",time()));
		$add_end_time = date("Y-m-d H:i:s",time()+20*86400);
		$this->assign('add_end_time',$add_end_time);
		$this->assign('list',$arr);
		$this->assign('status',3);
		$this->assign('page',$show);// 赋值分页输出
		$this -> display();
	}
	function get_edit_data(){
		$ip= trim($_POST['ip']);
		$model = new Model();
		$res = $model->table('sj_monitor_black')->where("ip='{$ip}'")->select();
		if($res){
			$result = array ('success' => true, 'rows' =>array('ip'=>$res[0]['ip'],'black_reason'=>$res[0]['black_reason'],'black_time_start'=>date('Y-m-d H:i:s',$res[0]['black_time_start']),'black_time_end'=>date('Y-m-d H:i:s',$res[0]['black_time_end'])));
			echo json_encode ($result);
			exit ();
		}else{
			$result = array ('success' => false, 'rows' =>0);
			echo json_encode ($result);
			exit ();
		}
		
	}
	
	function monitor_black_out(){//从黑名单撤销掉
		$id = trim($_POST['id']);
		$model = new Model();
	    foreach (explode(',', $id) as $val){
			$id = $val;
			$res = $model->table('sj_monitor_black')->where("ip='{$id}'")->save(array('status'=>0));
			$this->writelog("ip为{$id}的记录从黑名单中撤销掉",'sj_monitor_black',"ip:{$id}",__ACTION__ ,"","del");
			
		}
		
		if($res){
			$result = array ('success' => true, 'msg' =>'操作成功！');
			echo json_encode ( $result );
			exit ();
		}else{
			$result = array ('success' => false, 'msg' =>'操作失败！');
			echo json_encode ( $result );
			exit ();
		}
	}
	function monitor_black_oper(){ //编辑、添加黑名单 
		$black_reason = trim($_POST['reason']);//加黑理由
		$start_time = strtotime(trim($_POST['start_time']));//加黑开始时间
		$end_time = strtotime(trim($_POST['end_time']))+(3600*24)-1;//加黑结束时间 截止到23:59:59
		$id = trim($_POST['id']); //加黑的ID
		$ip = trim($_POST['ip']); //加黑的ID
		$model = new Model();
		if(empty($black_reason) || empty($start_time) || empty($end_time) || empty($ip)){
			$result = array ('success' => false, 'msg' =>'请输入正确的信息！');
			echo json_encode ( $result );
			exit ();
		}
		if(empty($id) || !isset($id)){ //手动添加IP到黑名单
			
		    $res_sel = $model->table('sj_monitor_black')->where("ip='{$ip}'")->select(); //判断是否已经存在此IP
			//echo $model->getLastSql().'<br>';
			$data = array('ip'=>$ip,'black_num'=>array('exp','black_num+1'),'black_time'=>time(),'black_time_start'=>$start_time,'black_time_end'=>$end_time,'black_reason'=>$black_reason,'status'=>1);
			if($res_sel){
				$res = $model->table('sj_monitor_black')->where("ip='{$ip}'")->save($data);
			}else{
				$res = $model->table('sj_monitor_black')->add($data);
			}
			
		}else{//编辑黑名单或从可疑IP列表加黑
			$arr = array();
			$arr_id = array();
			$arr_ip = array();
			foreach (explode(',', $ip) as $val){
				$arr_ip[] = $val;
			}
			foreach (explode(',', $id) as $val){
				$arr_id[] = $val;
			}
			$arr = array_combine($arr_id,$arr_ip);
			foreach ($arr as $key=>$val){
				$ip = $val;
				$id = $key;
				$res_sel = $model->table('sj_monitor_black')->where("ip='{$ip}'")->select(); //判断是否已经存在此IP
				//echo $model->getLastSql().'<br>';
				$data = array('ip'=>$ip,'black_num'=>array('exp','black_num+1'),'black_time'=>time(),'black_time_start'=>$start_time,'black_time_end'=>$end_time,'black_reason'=>$black_reason,'status'=>1);
				if($res_sel){
					$log_result = $this->logcheck("ip='{$ip}'",'sj_monitor_black',$data,$model->table('sj_monitor_black'));
					$res = $model->table('sj_monitor_black')->where("ip='{$ip}'")->save($data);
					$this->writelog("黑名单编辑了ip为{$ip}的记录。{$log_result}",'sj_monitor_black',$res,__ACTION__ ,"","edit");
				}else{
					$res = $model->table('sj_monitor_black')->add($data);
					$this->writelog("黑名单新增id为{$res}的记录",'sj_monitor_black',$res,__ACTION__ ,"","add");
				}
			}
		}
		if($res){
			$result = array ('success' => true, 'msg' =>'操作成功！');
			echo json_encode ( $result );
			exit ();
		}else{
			$result = array ('success' => false, 'msg' =>'操作失败！');
			echo json_encode ( $result );
			exit ();
		}
		//echo 'ip:'.$ip.'<br>加黑理由:'.$black_reason.'加黑开始时间：<br>'.$start_time.'加黑结束时间：<br>'.$end_time.'<br>ID'.$id;
	}
	
	function monitor_data(){//监测管理数据处理入库  (已经改成单独文件运行脚本形式此方法废弃中2013.8.8)
		$model = new Model();
		$yesterday_start = 1345705906;//strtotime(date("Y-m-d 0:0:0",strtotime("-1 day")));
		$yesterday_end = 1385705906;//$yesterday_start+(3600*24)-1;
		//echo date("Y-m-d H:i:s",$yesterday_start).'<br>'; //3600*24
		//echo date("Y-m-d H:i:s",$yesterday_start+(3600*24)-1).'<br>';
		$rank = 1; //初始化累加命中规则次数
		$res_log_arr = array();
		$res_log_arr_ip = array();
		$res_log_arr_reg = array();
		$res_log = $model->table('pu_dev_log')->field('logtime,fromip,action_id,user_id')->where('action_id in (2,3,5,160) and logtime>'.$yesterday_start.' and logtime<'.$yesterday_end.'')->select();
		
		foreach ($res_log as $val){//统计log日志表数据
			
			if($val['action_id']=='160'){ //注册新用户
				$res_log_arr_reg[$val['fromip']][]= $val;
			}else{//提交审核的软件
				$res_log_arr[$val['fromip']][$val['user_id']][]= $val;
				$res_log_arr_ip[$val['fromip']][] = $val;
			}
		}
		$reg_dev_id =array();
	    foreach ($res_log_arr_reg as $key=>$val){ //统计每个IP下的注册用户总数
	    	$res_log_arr_reg[$key]= array('total'=>count($val));
	    	/*foreach ($val as $key1=>$val1){
	    		$reg_dev_id[]= $val1['user_id'];
	    		$res_log_arr_reg[$key]= array('total'=>count($val),'dev_id'=>$reg_dev_id);
	    	} 暂不取开发者ID*/
		}
		//var_dump($res_log_arr_reg);exit;
		/***********************************************************************
		 * 开发者注册监控入库 
		 * **********************************************************************/
		//查询IP监控规则  开发者注册 IP 限制规则
		$ip_config2 = $model->table('sj_monitor_config')->where('type=2 and status=1')->select();
		
		$ip_config_day2 = $ip_config2[0]['day_num']; //提交不同注册账号的个数（个）:开发者用该IP注册提交的注册帐号的个数
		$ip_config_time2 = $ip_config2[0]['time_num'];//提交注册帐号间隔时间（分）：该IP提交注册帐号的时间频次
		//$sql_reg_dev_id = '';  暂不取开发者ID
		
		foreach ($res_log_arr_reg as $key=>$val){ //单个开发者对应提交软件的数量
			if($val > $ip_config_day2){ //加可疑列表
					/*foreach ($val['dev_id'] as $val1){
						$sql_reg_dev_id.=$val1.',';
					} 暂不取开发者ID*/
				    $ip = $key;
				    $total = $val['total'];
				    $data = array('ip'=>$ip,'reg_num'=>array('exp','reg_num+'.$total.''),'add_time'=>time(),'dev_monitor'=>$rank++);
				    
				    $res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >'{$yesterday_start}' and add_time<'{$yesterday_end}'")->select(); //判断是否已经存在此IP
				    
					//$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >= date_format(NOW(),'%Y-%m-%d')")->select(); //判断是否已经存在此IP
					if($res_sel){ //sj_monitor入库可疑列表   命中开发者注册规则
						$log_result = $this->logcheck("ip='{$ip}'",'sj_monitor',$data,$model->table('sj_monitor'));
						$res = $model->table('sj_monitor')->where("ip='{$ip}'")->save($data);
						$this->writelog("sj_monitor编辑ip为{$ip}的记录{$log_result}",'sj_monitor',"ip:{$ip}",__ACTION__ ,"","edit");
					}else{
						$res = $model->table('sj_monitor')->add($data);
						$this->writelog("sj_monitor新增id为{$res}的记录",'sj_monitor',$res,__ACTION__ ,"","add");
					}
					//echo $model->table('sj_monitor')->getLastSql().'<br>';
					
			}
		}
		
		/***********************************************************************
		 * 软件提交监控入库 
		 * **********************************************************************/
		$res_soft_arr = array();
		$res_soft_arr_ip = array();
		foreach ($res_log_arr as $key1=>$val1){ //统计每个IP的开发者的软件总数
			foreach ($val1 as $key2=>$val2){
				//echo 'ip地址：&nbsp&nbsp'.$key1.'dev_id:'.$key2.'<br>';
				$res_soft_arr[$key1][]= array('dev_id'=>$key2,'total'=>count($val2),'ip'=>$key1);
			}
		}
		foreach ($res_log_arr_ip as $key=>$val){ //统计每个IP下的软件总数
			$res_soft_arr_ip[$key]=  count($val);
		}
		//查询IP监控规则  软件提交 IP 限制规则
		$ip_config1 = $model->table('sj_monitor_config')->where('type=1 and status=1')->select();
		
		$ip_config_day1 = $ip_config1[0]['day_num']; //提交软件个数（个/天）：该IP 每天提交的软件个数（以包名作为判断）；
		$ip_config_time1 = $ip_config1[0]['time_num'];//提交软件间隔时间（分）：该IP 提交软件的时间频次；
		$ip_config_soft1 = $ip_config1[0]['soft_num'];//提交软件时对应的不同开发者账号（个）：该IP 提交软件对应的开发者帐号个数（以开发者的ID作为判断）；
		foreach ($res_soft_arr as $key=>$val){ //单个开发者对应提交软件的数量
			foreach ($val as $key1=>$val1){
				if($val1['total']> $ip_config_soft1){ //加可疑列表
					//echo 'ip:'.$val1['ip'].'&nbsp&nbsp总条数'.$val1['total'].'&nbsp&nbsp开发者ID'.$val1['dev_id'].'<br>';
					 $ip = $val1['ip'];
					 $total =$val1['total'];
					 $data = array('ip'=>$val1['ip'],'soft_num'=> array('exp','soft_num+'.$total.''),'add_time'=>time(),'soft_monitor'=>$rank++);
					 
					// $res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >= date_format(NOW(),'%Y-%m-%d')")->select(); //判断是否已经存在此IP
					$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >'{$yesterday_start}' and add_time<'{$yesterday_end}'")->select(); //判断是否已经存在此IP
					if($res_sel){ //sj_monitor入库可疑列表   命中开发者注册规则
						$log_result = $this->logcheck("ip='{$ip}'",'sj_monitor',$data,$model->table('sj_monitor'));
						$res = $model->table('sj_monitor')->where("ip='{$ip}'")->save($data);
						$this->writelog("sj_monitor编辑ip为{$ip}的记录{$log_result}",'sj_monitor',"ip:{$ip}",__ACTION__ ,"","edit");
					}else{
						$res = $model->table('sj_monitor')->add($data);
						$this->writelog("sj_monitor新增id为{$res}的记录",'sj_monitor',$res,__ACTION__ ,"","add");
					}
					echo $model->table('sj_monitor')->getLastSql().'<br>';
				}
			}
		}
		
		foreach ($res_soft_arr_ip as $key=>$val){//单个IP下对应的提交软件数量
			if($val1['total']> $ip_config_day1){ //加可疑列表
				//echo 'ip:'.$key.'&nbpp&nbsp软件总数'.$val.'<br>';
				$total = $val;
				$ip = $key;
				$data = array('ip'=>$key,'soft_num'=> array('exp','soft_num+'.$total.''),'add_time'=>time(),'soft_monitor'=>$rank++);
				
				$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >'{$yesterday_start}' and add_time<'{$yesterday_end}'")->select(); //判断是否已经存在此IP
				//$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >= date_format(NOW(),'%Y-%m-%d')")->select(); //判断是否已经存在此IP
				if($res_sel){ //sj_monitor入库可疑列表   命中开发者注册规则
				    
				    $log_result = $this->logcheck("ip='{$ip}'",'sj_monitor',$data,$model->table('sj_monitor'));
					$res = $model->table('sj_monitor')->where("ip='{$ip}'")->save($data);
					$this->writelog("sj_monitor编辑ip为{$ip}的记录{$log_result}",'sj_monitor',"ip:{$ip}",__ACTION__ ,"","edit");
				}else{
				    $res = $model->table('sj_monitor')->add($data);
				    $this->writelog("sj_monitor新增id为{$res}的记录",'sj_monitor',$res,__ACTION__ ,"","add");
				}
				echo $model->table('sj_monitor')->getLastSql().'<br>';
			}
		}
		
		//print_r($res_soft_arr); exit;
		$res_comment_arr = array();
		$res_comment_arr_day = array();
		$res_comment = $model->table('sj_soft_comment')->where('create_time>'.$yesterday_start.' and create_time<'.$yesterday_end.'')->field('userid,softid,ipmsg')->select();
		foreach ($res_comment as $val) { //统计用户评论表数据
			if(!empty($val['ipmsg'])){
			  // $res_comment_arr[$val['ipmsg']]['comment'][]= array('softid'=>$val['softid'],'userid'=>$val['userid']);
			  $res_comment_arr[$val['ipmsg']][$val['softid']]['comment'][] = $val; //按单个软件统计
			  $res_comment_arr_day[$val['ipmsg']]['comment'][] = $val;//按IP下所有软件统计
			}
		}
	    //print_r($res_comment_arr_day); exit;
		$res_comment_num = array();
		$res_comment_num_day = array();
		foreach ($res_comment_arr_day as $key=>$val){//按IP下所有软件统计
			
			 $res_comment_num_day[$key][]=  array('total'=>count($val['comment']));
		}
		foreach ($res_comment_arr as $key=>$val){ //统计每个IP的同一软件的评论数
			 // $res_comment_num_day[$key][]=  array('total'=>count($val['comment']));
			foreach ($val as $key1=>$val1){
				//echo 'IP地址：'.$key.'&nbsp&nbsp&nbsp&nbspsoftid：'.$key1.'&nbsp&nbsp&nbsp&nbsp评论数：'.count($val1['comment']).'<br>';
				$res_comment_num[$key][]= array('total'=>count($val1['comment']),'softid'=>$key1,'ip'=>$key);
			}
		}
		/***********************************************************************
		 * 评论监控入库 
		 * **********************************************************************/
		//查询IP监控规则  评论提交 IP 限制规则
		$ip_config3 = $model->table('sj_monitor_config')->where('type=3 and status=1')->select();
		$ip_config_day3 = $ip_config3[0]['day_num']; //同一软件提交评论次数（次/天）: 该IP每天提交的评论个数
		$ip_config_time3 = $ip_config3[0]['time_num'];//提交评论间隔时间（分）：该IP提交软件评论的时间频次；
		$ip_config_soft3 = $ip_config3[0]['soft_num'];//评论的软件个数（个）：该IP提交的评论对应的不同软件个数；
		foreach ($res_comment_num as $key=>$val){//按单个软件统计
			foreach ($val as $val2){
				if($val2['total']> $ip_config_day3){ //加可疑列表
					echo 'ip:'.$val2['ip'].'&nbsp&nbsp总条数'.$val2['total'].'&nbsp&nbspsoftid&nbsp&nbsp'.$val2['softid'].'<br>';
					$total = $val2['total'];
					$ip = $val2['ip'];
					$data = array('ip'=>$ip,'comment_num'=> array('exp','comment_num+'.$total.''),'add_time'=>time(),'comment_monitor'=>$rank++);
					$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >'{$yesterday_start}' and add_time<'{$yesterday_end}'")->select(); //判断是否已经存在此IP
					//$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >= date_format(NOW(),'%Y-%m-%d')")->select(); //判断是否已经存在此IP
					if($res_sel){ //sj_monitor入库可疑列表   命中开发者注册规则
					    $log_result = $this->logcheck("ip='{$ip}'",'sj_monitor',$data,$model->table('sj_monitor'));
						$res = $model->table('sj_monitor')->where("ip='{$ip}'")->save($data);
						$this->writelog("sj_monitor编辑ip为{$ip}的记录{$log_result}",'sj_monitor',"ip:{$ip}",__ACTION__ ,"","edit");
					}else{
					    $res = $model->table('sj_monitor')->add($data);
					    $this->writelog("sj_monitor新增id为{$res}的记录",'sj_monitor',$res,__ACTION__ ,"","add");
					}
					echo $model->table('sj_monitor')->getLastSql().'<br>';
				}
			}
		}
		foreach ($res_comment_num_day as $key=>$val){//统计每个IP的同一软件的评论数
			        if($val[0]['total']> $ip_config_day3){ //加可疑列表
						echo 'ip:'.$key.'&nbsp&nbsp总条数'.$val[0]['total'].'<br>';
						$total = $val[0]['total'];
						$ip = $key;
						$data = array('ip'=>$ip,'comment_num'=> array('exp','comment_num+'.$total.''),'add_time'=>time(),'comment_monitor'=>$rank++);
						
						//$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >= date_format(NOW(),'%Y-%m-%d')")->select(); //判断是否已经存在此IP
						$res_sel = $model->table('sj_monitor')->where("ip='{$ip}' and add_time >'{$yesterday_start}' and add_time<'{$yesterday_end}'")->select();
						if($res_sel){ //sj_monitor入库可疑列表   命中开发者注册规则
						    $log_result = $this->logcheck("ip='{$ip}'",'sj_monitor',$data,$model->table('sj_monitor'));
							$res = $model->table('sj_monitor')->where("ip='{$ip}'")->save($data);
							$this->writelog("sj_monitor编辑ip为{$ip}的记录{$log_result}",'sj_monitor',"ip:{$ip}",__ACTION__ ,"","edit");
						}else{
						    $res = $model->table('sj_monitor')->add($data);
						    $this->writelog("sj_monitor新增id为{$res}的记录",'sj_monitor',$res,__ACTION__ ,"","add");
						}
						echo $model->table('sj_monitor')->getLastSql().'<br>';
				    }
		}
		
		
	}
	function monitor_config(){ //监测IP配置信息
		$model = M('monitor_config');
		/*$list1 = $model->where('type=1')->order('add_time desc')->select();
		$list2 = $model->where('type=2')->order('add_time desc')->select();
		$list3 = $model->where('type=3')->order('add_time desc')->select();
		$this->assign('status',4);
		$this->assign('list1',$list1);
		$this->assign('list2',$list2);
		$this->assign('list3',$list3);*/
		$list = $model->order('add_time desc')->select();
		$this->assign('status',4);
		$this->assign('list',$list);
		$this->assign('count',count($list));
		$this->display();
	}
	function monitor_config_del(){ //删除监测IP配置信息
		$id = trim($_GET['id']);
		$model = M('monitor_config');
		$res = $model->where("id={$id} and status=0")->delete();
		if($res){
			$this->writelog("监测IP配置信息删除id为{$id}的配置",'sj_monitor_config',$id,__ACTION__ ,"","del");
			$this->success('删除成功');
		}else{
			$this->error('删除失败,请检查该规则是否在启用中。');
		}
	}
    function monitor_config_add(){ //添加监测IP配置信息
    	$model    = M('monitor_config');
		$type     = trim($_POST['type']);
		$day_num  = trim($_POST['day_num']);
		$time_num = trim($_POST['num']);
		$second  =  trim($_POST['second']);
		global  $status_add;
		$status_add = false;
		foreach (explode(',', $type) as $key => $value) {
			if(is_numeric($value)){
						//$soft_num = trim($_POST['soft_num']); 产品阉割 
				if((empty($time_num) || empty($second) ) && empty($day_num)){
					$result = array ('success' => false, 'msg' =>'请输入整型数字！');
					echo json_encode ($result);  
					exit ();
				}
		    	$data = array(
				      'type'	=> $value,
					  'day_num' => $day_num,
					  'num'=> $time_num, //提交软件频次 个
					  'second'=> $second, //提交软件频次 秒
					  'status'	=> 1,
					  'add_time'=> time(),
					  //'day_date'=>date('Y-m-d',time()),
				);
				$res = $model->add($data);
				$last_id = mysql_insert_id();
				if($res){
				  $this->writelog("监测IP配置信息新增id为{$res}的配置",'sj_monitor_config',$res,__ACTION__ ,"","add");
				  $updates = $model->where('id!='.$last_id.' and type='.$value.'')->save(array('status'=>0));
				  
				}
				if($res && $updates){
				
					$status_add = true;
				}
			}
		}
		if($status_add){
		  	$result = array ('success' => true, 'msg' =>'添加成功！');
			echo json_encode ( $result );
			exit ();
		}else{
			$result = array ('success' => false, 'msg' =>'添加失败！');
			echo json_encode ( $result );
			exit ();
		}
	}
	function monitor_config_oper(){//IP配置规则的启用与停用
		$model = M('monitor_config');
		$id  = trim($_POST['id']);
		$status =  trim($_POST['status']);
		$types = trim($_POST['types']);
		if($status==1){
			$updates = $model->where('id='.$id.'')->save(array('status'=>0));
			//$max_date = $model->where('id!='.$id.' and type='.$types.'')->order('add_time desc')->select();
			//$max_time = $max_date[0]['add_time'];
		   // $updates = $model->where('add_time='.$max_time.'')->save(array('status'=>1));//把最近时间的一条启用
		}else{
			$updates = $model->where('id='.$id.'')->save(array('status'=>1));
			$updates = $model->where('id!='.$id.' and type='.$types.'')->save(array('status'=>0));//停用其它记录保持只有一条是启用的 
		}
		if($updates){
			$str=($status==1)?'停用':'启用';
			$this->writelog("{$str}了id为{$id}监测IP配置规则",'sj_monitor_config',$id,__ACTION__ ,"","edit");
			$result = array ('success' => true, 'msg' =>'操作成功！');
			echo json_encode ( $result );
			exit ();
		}else{
			$result = array ('success' => false, 'msg' =>'操作失败！');
			echo json_encode ( $result );
			exit ();
		}
	}
}
?>
