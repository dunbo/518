<?php
    /*
     * 安智内容合作平台
     * @date 2018.4.11
	 * @by yuanming
     * */

class ContentPlatformStatisAction extends CommonAction {
	public $limit = 20;
	public $pid_data = array('-1'=>'全平台','1'=>'安智市场','20'=>'什么值得玩');
    public function market_list() 
	{
		$model = new Model();
		$time = time();
		
		$download = 0;
		if($_GET['sub_name'] == '导出'){
		    $download = 1;
		}
		
		$where = $this->deal_query();
		$talbe = 'content_platform_statis';		
		$filed = "pid,sum(visit_num) as visit_total,sum(visit_deduct_num) as visit_d_total,sum(down_num) as down_tatol,sum(down_deduct_num) as down_d_total";
		
		$pid_type = $_GET['pid_type']?$_GET['pid_type']:0;
		if($pid_type == 1){
			$where_con = $where. ' and type = 1 group by pid';
			$where_video = $where. ' and type = 2 group by pid';
		}else{
			$where_con = $where. ' and type = 1';
			$where_video = $where. ' and type = 2';			
		}
		$content_data = $model->table($talbe)->where($where_con)->field($filed)->select();	
		//echo $model->getLastSql();
		$video_data = $model->table($talbe)->where($where_video)->field($filed)->select();
		//echo $model->getLastSql();
		if($pid_type == 1){
			$data = array();
			if($content_data){
				foreach($content_data as $val){
					$data[$val['pid']]['content'] = $val;
				}
			}
			if($video_data){
				foreach($video_data as $val){
					$data[$val['pid']]['video'] = $val;
				}
			}
			$this->assign('data',$data);
		}else{
			$this->assign('video_data',$video_data[0]);
			$this->assign('content_data',$content_data[0]);
		}		
		if(empty($_GET['pid'])|| $_GET['pid'] == 1){
			$tpl = 'market_list';
		}else{
			
			$tpl = 'market_list_total';
		}
		if($download){
			$out_data = array();
			if($pid_type == 1){
				$out_data['tab_menu'] = array('渠道名称','图文-浏览量','图文-下载量','视频-浏览量','视频-下载量');
				foreach($data as $key => $val){
					$out_data['data'][] = array(
									$this->pid_data[$key],
									$val['content']['visit_total'],
									$val['content']['down_tatol'],
									$val['video']['visit_total'],
									$val['video']['down_tatol'],
									);
				}
			}else{
				$out_data['tab_menu'] = array('图文-浏览量','图文-下载量','视频-浏览量','视频-下载量');
				$out_data['data'][] = array(
								$content_data[0]['visit_total'],
								$content_data[0]['down_tatol'],
								$video_data[0]['visit_total'],
								$video_data[0]['down_tatol'],
				);
			}
			$this->out_export($out_data,'total');	
		}else{
			$this->assign('pid_data',$this->pid_data);
			$this->assign('pid_type',$pid_type);
			$this->assign('pid',$_GET['pid']);
			$this->display($tpl);
		}
		
    }
	//用户统计 //内容统计
	public function user_statis_list()
	{
		$time = time();
		$model = new Model();
		import("@.ORG.Page");
		
		$download = 0;
		if($_GET['sub_name'] == '导出'){
		    $download = 1;
		}
		if($_GET['user_id']){
			$_GET['statis_type'] = 2;
			if(!$_GET['cont_type']){
				$_GET['cont_type'] = 1;
			}
		}
		$where = $this->deal_query();
		$statis_type = $_GET['statis_type'];
		$cont_type = $_GET['cont_type'];
		
		$user_data = $this->grep_user();
		if($user_data){
			$user_id_srt = array_keys($user_data);
			$user_id_srt = implode(',',$user_id_srt);			
			$where .= " and  user_id  in ({$user_id_srt}) ";
		}
		if($_GET['user_id']){
			$where .= " and  user_id = {$_GET['user_id']} ";
		}
		
		if($statis_type == 2){
		    if($_GET['title']){
		        $parm['title'] = trim($_GET['title']);
				$content_res = $this->grep_content($parm,$cont_type);
		        if($content_res){
		            $content_id_str = '';
		            $content_id_str = array_keys($content_res);
					$content_id_str = implode(',',$content_id_str);
		            $where .= " and  content_id  in ({$content_id_str}) ";
		        }
		    }
		}
		
		$table = 'content_platform_statis';		
		$filed = "user_id,content_id,type,COUNT(*) as cont_total,sum(visit_num) as visit_total,sum(visit_deduct_num) as visit_d_total,sum(down_num) as down_tatol,sum(down_deduct_num) as down_d_total";
		
	
		if($statis_type == 1){ //用户分组
		    $tpl = 'user_statis_list';
			$where_con = $where. ' and type = 1 group by user_id';
			$where_video = $where. ' and type = 2 group by user_id';
			$con_count = $model->execute("select id from {$table} where {$where_con}");
			$video_count = $model->execute("select id from {$table} where {$where_video}");			
			if($con_count >= $video_count) {
			    $count = $con_count;
			}else{
			    $count = $video_count;
			}
		}elseif($statis_type == 2){ //内容分组
		    $tpl = 'content_statis_list';
		    $where = $where. " and type = {$cont_type} group by content_id";	
		    $count = $model->execute("select id from {$table} where {$where}");
		}
		//echo $model->getLastSql();
		//var_dump($count);
		if($count > 0){			
			$page = new Page($count, $this->limit);
			if($download == 1){
			    if($statis_type == 2){
			        $data_cont = $model->table($table)->where($where)->field($filed)->select();
			    }else{
			        $video_data = $model->table($table)->where($where_video)->field($filed)->select();
			        $content_data = $model->table($table)->where($where_con)->field($filed)->select();
			    }			    
			}else{
			    if($statis_type == 2){
			        $data_cont = $model->table($table)->where($where)->field($filed)->order('statis_tm desc')->limit($page->firstRow . ',' . $page->listRows)->select();
			    }else{
			        $video_data = $model->table($table)->where($where_video)->field($filed)->limit($page->firstRow . ',' . $page->listRows)->select();
			        $content_data = $model->table($table)->where($where_con)->field($filed)->limit($page->firstRow . ',' . $page->listRows)->select();
			    }			    
			}			
	
			$user_arr = array();			
			for($i = 0;$i<$count;$i++){
			    if($statis_type == 2){
			        $user_id = $data_cont[$i]['user_id'];
			        $content_id = $data_cont[$i]['content_id'];
			        if($content_id){
			            $data[$content_id] = $data_cont[$i];
			            $user_arr[] = $user_id;
			            $content_id_arr[] = $content_id;
			        }
			    }else{
			        $user_id = $content_data[$i]['user_id'];
			        if($user_id){
			            $data[$user_id][1] = $content_data[$i];
			            $user_arr[] = $user_id;
			        }
			        $user_id2 = $video_data[$i]['user_id'];
			        if($user_id2){
			            $data[$user_id2][2] = $video_data[$i] ;
			            $user_arr[] = $user_id2;
			        }
			    }
				
			}
			
			if($user_arr && !$_GET['username']){
			    $where = array();
				$where['userid'] = array('in',$user_arr);
				$filed = 'userid,username';
				$user_res = $model->table('content_platform_user')->where($where)->field($filed)->select();
				//echo $model->getLastSql(); exit;
				foreach($user_res as $row){
					$user_data[$row['userid']] = $row;
				}
			}
			if($statis_type == 2 && $content_id_arr){
				$pram = array();
				$pram['content_ids'] = $content_id_arr;
				$cont_data = $this->grep_content($pram, $cont_type);
				//echo $model->getLastSql();
				//var_dump($cont_data);
			}			
			$this->assign('page', $page->show());
			$this->assign('cont_data',$cont_data);
			$this->assign('user_data',$user_data);
			$this->assign('data',$data);
		}
		if($download){		    
		    $out_data = array();		
		    if($statis_type == 1){
		        $out_data['tab_menu'] = array('用户','发布图文数','浏览量','浏览量（扣量后）','下载量','下载量（扣量后）','发布视频数','播放量','播放量（扣量后）','下载量','下载量（扣量后）');		        
		        if($data){
		            foreach ($data as $key=>$val){
		                $out_data['data'][] = array(
		                        $user_data[$key]['username'],
		                        $val[1]['cont_total'],
		                        $val[1]['visit_total'],
		                        $val[1]['visit_d_total'],
		                        $val[1]['down_tatol'],
		                        $val[1]['down_d_total'],
		                        $val[2]['cont_total'],
		                        $val[2]['visit_total'],
		                        $val[2]['visit_d_total'],
		                        $val[2]['down_tatol'],
		                        $val[2]['down_d_total'],
		                    );
		            }	        	
		        }				
		    }elseif($statis_type == 2){
				if($_GET['user_id']){
					if($cont_type == 1){
						$out_data['tab_menu'] = array('标题','发布时间','浏览量','浏览量（扣量后）','下载量','下载量（扣量后）');
					}else{
						$out_data['tab_menu'] = array('标题','发布时间','播放量','播放量（扣量后）','下载量','下载量（扣量后）');
					}
				}else{
					if($cont_type == 1){
						$out_data['tab_menu'] = array('标题','发布时间','用户','浏览量','浏览量（扣量后）','下载量','下载量（扣量后）');
					}else{
						$out_data['tab_menu'] = array('标题','发布时间','用户','播放量','播放量（扣量后）','下载量','下载量（扣量后）');
					}
				}
				foreach($data as $key => $val){
					$tmp = array(
						0 => $cont_data[$key]['title'],
						1 => $cont_data[$key]['pass_tm'],				
						3 => $val['visit_total'],
						4 => $val['visit_d_total'],
						5 => $val['down_tatol'],
						6 => $val['down_d_total']
						);
					if(!$_GET['user_id']){
						$tmp[2] = $user_data[$val['user_id']]['username'];
					}		
					$out_data['data'][] = $tmp ;
				}
				
		        
		    }
			$this->out_export($out_data,$statis_type);		   
		}else{
			$this->assign('pid',$_GET['pid']);
			$this->assign('pid_name',$this->pid_data[$_GET['pid']]);
		    $this->display($tpl);		    
		}		
    }
	//数据统计-全平台详情
	public function data_statis_total()
	{
		import("@.ORG.Page");
		$model = new Model();
		$download = 0;
		if($_GET['sub_name'] == '导出'){
		    $download = 1;
		}
		$table = 'content_platform_statis';				
		$where = $this->deal_query();
		$where_con = $where. ' and type = 1 group by statis_tm';
		$where_video = $where. ' and type = 2 group by statis_tm';
		$con_count = $model->execute("select id from {$table} where {$where_con}");
		$video_count = $model->execute("select id from {$table} where {$where_video}");
		if($con_count >= $video_count) {
			$count = $con_count;
		}else{
			$count = $video_count;
		}
		$filed = "user_id,statis_tm,content_id,type,sum(visit_num) as visit_total,sum(visit_deduct_num) as visit_d_total,sum(down_num) as down_tatol,sum(down_deduct_num) as down_d_total";
		//$filed = "user_id,content_id,type,COUNT(*) as cont_total,sum(visit_num) as visit_total,sum(visit_deduct_num) as visit_d_total,sum(down_num) as down_tatol,sum(down_deduct_num) as down_d_total";
		$data = array();
		if($count > 0){			
			$page = new Page($count, $this->limit);
			if($download == 1){			    
				$video_data = $model->table($table)->where($where_video)->order('statis_tm desc')->field($filed)->select();
				$content_data = $model->table($table)->where($where_con)->order('statis_tm desc')->field($filed)->select();			    			    
			}else{			    
				$video_data = $model->table($table)->where($where_video)->order('statis_tm desc')->field($filed)->limit($page->firstRow . ',' . $page->listRows)->select();
				//echo $model->getLastSql();
				$content_data = $model->table($table)->where($where_con)->order('statis_tm desc')->field($filed)->limit($page->firstRow . ',' . $page->listRows)->select();				
			}

			$video_data_new = array();
			$date_arr = array();
			foreach($video_data as $row){
				$video_data_new[$row['statis_tm']] = $row;
				$date_arr[] = $row['statis_tm'];
			}
			$content_data_new = array();
			foreach($content_data as $row){
				$content_data_new[$row['statis_tm']] = $row;
				$date_arr[] = $row['statis_tm'];
			}
			$data_arr = arsort(array_unique($data_arr));
			foreach($date_arr as $v){
				$tmp = array();				
				if($video_data_new[$v]){
					$tmp['video'] = $video_data_new[$v];
					$key = $video_data_new[$v]['statis_tm'];
				}
				if($content_data_new[$v]){                
					$tmp['content'] = $content_data_new[$v];
					$key = $content_data_new[$v]['statis_tm'];
				}
				$data[$key] = $tmp;
			}
			$this->assign('page', $page->show());
		}
		if($download){
			$out_data['tab_menu'] = array('日期','图文浏览量','图文下载量','视频播放量','视频下载量');
			 foreach ($data as $key=>$val){
			 $out_data['data'][] = array(
					 $key,
					 $val['content']['visit_total'],
					 $val['content']['down_tatol'],
					 $val['video']['visit_total'],
					 $val['video']['down_tatol']
					 );
			 }
			$this->out_export($out_data,'detail');
		}else{
			$this->assign('pid',$_GET['pid']);
			$this->assign('pid_name',$this->pid_data[$_GET['pid']]);
			$this->assign('data',$data);
			$this->display();
		}
		
	}
	public function deal_query()
	{
		//1用户
		$time = time();
		$statis_type = $_GET['statis_type']?$_GET['statis_type']:1;
		$pid = !empty($_GET['pid'])?$_GET['pid']:1;	//1安智
		$_GET['pid'] = $pid;
		$type = $_GET['type'];
				
		$start_tm = $_GET['start_tm']?strtotime($_GET['start_tm']):'';
		$end_tm = $_GET['end_tm']?strtotime($_GET['end_tm']):'';
		
		if(!$start_tm && !$end_tm && !$type) $type = 1;
		elseif($start_tm && $end_tm) $type = 0 ;
		$cont_type = 0;
		if($statis_type == 2){
			$cont_type = $_GET['cont_type']?$_GET['cont_type']:1;
			$this->assign('cont_type',$cont_type);
		}	
		if(in_array($type,array(1,2,3))){
			if($type == 1) $day_num = 7;
			elseif($type == 2) $day_num = 14;
			elseif($type == 3) $day_num = 30;
		}else{			
			if(!$start_tm || !$end_tm){
				$this->error("请填写开始和结束日期！");
			}
			$this->assign('start_tm',$_GET['start_tm']);
			$this->assign('end_tm',$_GET['end_tm']);			
		}
		$this->assign('type',$type);
		$_GET['cont_type'] = $cont_type;
		$_GET['statis_type'] = $statis_type;
		
		$where = '1';
		if(in_array($type,array(1,2,3))){
			$day_time = date('Ymd',(strtotime(date('Ymd',$time)) - 86400*$day_num));
			$where .= " and `statis_tm` >= {$day_time}"; 
		}else{
		    $start_tm = date('Ymd',$start_tm);
		    $end_tm = date('Ymd',$end_tm);
			$where .= " and `statis_tm` >= {$start_tm}  and `statis_tm` <= {$end_tm}"; 
		}
		if($pid && $pid != -1){
			$where .= " and pid = {$pid} ";
		}		
		return $where;
	}
    
    public function content_config(){
        $model = new Model();
		$uptime = time();
		$action = $_POST['action']?$_POST['action']:$_GET['action'];
		if(!$action) $action = 'default';
		$table = 'pu_config';
		$configname = $config_type = 'content_platform_config';
		$this->assign('action',$action);
		$where = array();
		$where['configname'] = $configname;
		$where['config_type'] = $config_type;
		switch ($action)
		{
			case 'default':				
				$where['status'] = 1;
				$res = $model->table($table)->where($where)->order('conf_id desc')->limit(1)->select();
				$data = json_decode($res[0]['configcontent'],true);
				$tpl = 'content_config_list';				
				$this->assign('data',$data);
				break;			
			case 'edit': //编辑提交
				$data = array();
				$data['c_visit_congfig'] = $_POST['c_visit_congfig'];
				$data['c_down_congfig'] = $_POST['c_down_congfig'];
				$data['v_visti_congfig'] = $_POST['v_visti_congfig'];
				$data['v_down_congfig'] = $_POST['v_down_congfig'];
				$data = json_encode($data);
				$model->table($table)->where($where)->save(array('status'=>0));
				$result =$model->table($table)->add(array('uptime' => $uptime,'configcontent'=>$data,'config_type'=>$config_type,'status'=>1,'configname'=>$configname));
				if($result){
					$this->writelog("内容扣量系数配置",'pu_config',$result,__ACTION__,'','add');
					$this->success('新增成功');
				}else{
					$this->error('保存失败');
				}
				break;		
			case 'edit_show':
				$where['status'] = 1;
				$res = $model->table($table)->where($where)->order('conf_id desc')->limit(1)->select();
				$data = json_decode($res[0]['configcontent'],true);
				$this->assign('data',$data);
				$tpl = 'content_config';
				break;
			case 'list':
				import("@.ORG.Page");
				//$where['status'] = 0;
				$count = $model->table($table)->where($where)->field('conf_id')->count();
				$page = new Page($count, $this->limit);
				$res = $model->table($table)->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('conf_id desc')->select();				
				$data = array();
				foreach($res as $row){
					$tmp = array();
					$tmp = json_decode($row['configcontent'],true);
					$tmp['uptime'] = $row['uptime'];
					$data[] = $tmp;
				}
				$this->assign('page',$page->show());
				$this->assign('data',$data);
				$tpl = 'content_config_list';
				break;
		}
		$this->display($tpl);		
    }
    //内容详情页面
    public function content_detail()
	{
		$model = new Model();
		$time = time();
		$table = 'content_platform_statis';
		$download = 0;
		$where = $this->deal_query();
		if($_GET['sub_name'] == '导出'){
		    $download = 1;
		}		
		if($_GET['content_id']){
			$content_id = $_GET['content_id'];
			$where .= " and content_id = {$content_id} ";
			$this->assign('content_id',$content_id);
		}
		$count = $model->execute("select id from {$table} where {$where}");
		if($count > 0){
			import("@.ORG.Page");
			$this->assign('cont_type',$data[0]['type']);
			$pram['content_ids'] = $content_id;
			$cont_data = $this->grep_content($pram,'');	
			$page = new Page($count, $this->limit);
			$this->assign('page', $page->show());			
			if($download){
				$data = $model->table($table)->where($where)->select();
			}else{
				$data = $model->table($table)->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
			}			
			//echo $model->getLastSql();
			foreach($data as $row){
				$conf_ids[] = $row['conf_id'];
			}
			$where_conf['conf_id'] =  array('in',$conf_ids);
			$config_arr = get_table_data($where_conf,"pu_config","conf_id","conf_id,configcontent");
			$conf_data = array();  
			foreach($config_arr as $key=>$row){
				$conf_data[$key]['configcontent'] = json_decode($row['configcontent'],true);
			}
		}
		if($download){
			$out_data = array();
			if($data[0]['type'] == 1){
				$out_data['tab_menu'] = array('日期','浏览量','浏览量（扣量后）','扣量系数','下载量','下载量（扣量后）','扣量系数');
			}else{
				$out_data['tab_menu'] = array('日期','播放量','播放量（扣量后）','扣量系数','下载量','下载量（扣量后）','扣量系数');
			}
			foreach($data as $val){
				if($val['type'] == 1){
					$visit_config = $conf_data[$val['conf_id']]['configcontent']['c_visit_congfig'];
					$down_config = $conf_data[$val['conf_id']]['configcontent']['c_down_congfig'];					
				}else{
					$visit_config = $conf_data[$val['conf_id']]['configcontent']['v_visit_congfig'];
					$down_config = $conf_data[$val['conf_id']]['configcontent']['v_down_congfig'];	
				}				
				$out_data['data'][] = array(
								$val['statis_tm'],
								$val['visit_num'],
								$val['visit_deduct_num'],
								$visit_config.'%',
								$val['down_num'],
								$val['down_deduct_num'],
								$down_config.'%',
				);
			}
			$this->out_export($out_data,'content');			
		}else{
			$title = $cont_data[$content_id]['title'];
			$this->assign('pid',$_GET['pid']);
			$this->assign('conf_data',$conf_data);
			$this->assign('title',$title);
			$this->assign('data',$data);
			$this->display();	
		}			
    }
	//用户详情页
	public function user_detail()
	{
		
	}
	
    //查找内容相关信息
    public function grep_content($pram,$type){
		$model = new Model();
        $table = 'content_platform_content';
        $where = array();
        $res = array();
        if($type) $where['type'] = $type;
    	if($pram['content_ids']){
			if(is_array($pram['content_ids'])){
				$where['content_id'] = array('in',$pram['content_ids']);
			}else{
				$where['content_id'] = $pram['content_ids'];
			}   	    
    	}
    	if($pram['title']){
			$title = $pram['title'];
			$where['title'] = array('exp',"like '%".$title."%'");
			$this->assign('title',$title);
    	}
		$filed = 'content_id,title,pass_tm';
		$content = $model->table($table)->where($where)->field($filed)->select();
		//echo $model->getLastSql();
		foreach($content as $row){
			$row['pass_tm'] = date('Y-m-d H:i:s',$row['pass_tm']);
			$res[$row['content_id']] = $row;
		}		
    	return  $res;
    }
	public function grep_user(){
		$model = new Model();
		$username = trim($_GET['username']);
		$user_data = array();
		$filed = 'userid,username';		
		if($username){
			$user_res = $model->table('content_platform_user')->field($filed)->where("status = 1 and username like '%{$username}%' ")->select();
			if($user_res){
				$user_id_srt = '';
				foreach($user_res as $row){
					//$user_id_srt .= $row['userid'].',';
					$user_data[$row['userid']] = $row;
 				}
				//$user_id_srt = substr($user_id_srt,0,-1);
				//$where .= " and  user_id  in ({$user_id_srt}) ";
			}
			$this->assign('username',$username);
		}
		if($_GET['user_id']){
			$user_res = $model->table('content_platform_user')->field($filed)->where("userid = {$_GET['user_id']}")->find();
			$this->assign('username',$user_res['username']);
			$this->assign('user_id',$user_res['userid']);
		}
		return $user_data;
	}
	
	public function out_export($data,$filename)
	{
		header('Content-type: application/csv');		    
		$filename = date("Y-m-d-H")."_{$filename}".".csv";		    
		header('Content-Disposition: attachment; filename=data_"'.$filename);
		$out = fopen('php://output', 'w');
		fwrite($out,chr(0xEF).chr(0xBB).chr(0xBF));
		if($data['tab_menu']){
			fputcsv($out,$data['tab_menu']);
			foreach($data['data'] as $row){
				fputcsv($out,$row);				
			}
		}		
	}    
}
