<?php
class InformationManagementAction extends CommonAction {
		//信息管理 反馈建意 显示列表
	function feedback_suggestions(){
		$model = new Model();
		import('@.ORG.Page2');
		$where = array();
		isset($_GET['status']) ? $_GET['status'] : $_GET['status'] = 1;
		if($_GET['status'] == 0 && isset($_GET['status'])){
			$where['status'] = 0;
		}else{
			$where['status'] = 1;
		}
		$this->assign('status',$_GET['status']);
		if(!$_GET){ 
			$where['status'] = 1;
			$where['last_status'] =0;
			$this->assign('status',1);
			$this->assign('last_status',2);
		}
		$referer = explode('/', $_SERVER['HTTP_REFERER']);
		$from_referer = 0;
		if(in_array('feedback_reply',$referer)){
		    $from_referer = 1;
		}
		$_GET['last_status'] ?$_GET['last_status']:$_GET['last_status'] = 2;
		$sesson_type = 'soft_comment'.$_GET['last_status'];	
		//var_dump($_GET);	
		$get_num = count($_GET);
		if( $from_referer  ){
		    //||($get_num == 4 && $_GET['status'] && $_GET['last_status'] && $_GET['p'] && $_GET['lr']
		    $this->getGetValfromSession($sesson_type);
		}
		
		$this->setGetValfromSession($sesson_type);
		if($_GET){
		//查询数据			
			if(!empty($_GET['content'])){
			    $_SESSION['feedback']['content'] = $_GET['content'];
				$where['content'] = array("like","%{$_GET['content']}%");
				$this->assign('content',$_GET['content']);
			}else{
			    $_SESSION['feedback']['content'] = '';
			}
			//处理状态
			if(isset($_GET['last_status']) && $_GET['status'] != 0 ){
				if($_GET['last_status'] == 1){
					$where['last_status'] = array('gt',0);
					//读取状态
					if(isset($_GET['stat'])){
					    if($_GET['stat'] == 1){
					        $where['stat'] = array('eq',0);
					        $this->assign('stat',1);
					    }elseif($_GET['stat'] == 2){
					        $where['stat'] = array('gt',0);
					        $this->assign('stat',2);
					    }
					}					
					$this->assign('last_status',1);
				}elseif($_GET['last_status'] == 2){
					$where['last_status'] = array('eq',0);
					$this->assign('last_status',2);
				}				
			}			
			
			if(!empty($_GET['start_at']) && !empty($_GET['end_at'])){
				$start_at = strtotime($_GET['start_at']);
				$end_at = strtotime($_GET['end_at']);
				$where['create_tm'] = array(array('egt',$start_at),array('elt',$end_at));
				$this->assign('start_at', $_GET['start_at']);
				$this->assign('end_at', $_GET['end_at']);
			}else{
			    $_SESSION['feedback']['start_at']	='';
			    $_SESSION['feedback']['end_at']		='';
			}			
			if(isset($_GET['dev_name']) || isset($_GET['email'])){
				$this->assign('dev_name', $_GET['dev_name']);
				$this->assign('email', $_GET['email']);
				if(isset($_GET['email']) && $_GET['email']!=''){
					$params['email'] = trim($_GET['email']);
					$wheres['email'] = array("eq","{$params['email']}");
					$_SESSION['feedback']['email'] = $_GET['email'];
				}else{
				    $_SESSION['feedback']['email'] = '';
				}
				if(isset($_GET['dev_name']) && $_GET['dev_name']!=''){	
					$wheres['dev_name'] = array('like',"%{$_GET['dev_name']}%");
					$_SESSION['feedback']['dev_name'] = $_GET['dev_name'];
				}else{
				    $_SESSION['feedback']['dev_name'] = '';
				}
				$devname = $model->table('pu_developer')->where($wheres)->field('dev_id')->select();
				$dev_id = array();
				foreach ($devname as $n => $m ){
					$dev_id[] = $m['dev_id'];
				}
				$where['dev_id'] = array("in",$dev_id);
			}
                        if(isset($_GET['operator'])&&$_GET['operator']!=""){
                            $this->assign('operator', $_GET['operator']);
                            $where['operator'] = array('like',"%{$_GET['operator']}%");
                        }
		}
		//分页
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
		$total = $model->table('dev_feedback')->where($where)->count();
		$param = http_build_query($_GET);
		$page = new Page($total, $limit,$param);
		$feedbacklists = $model->table('dev_feedback')->where($where)->order('dev_tm desc')->limit($page->firstRow.','.$page->listRows)->select();
        //        echo $model->getLastSql();
		$feedbacklist = array();
		//整合数据
		$dev_id = array();
		foreach($feedbacklists as $k => $v){
			$feedbacklist[$k]['id'] = $v['id'];
			$feedbacklist[$k]['last_status'] = $v['last_status'];
			$feedbacklist[$k]['remark'] = $v['remark'];
			$feedbacklist[$k]['stat'] = $v['stat'];
			$feedbacklist[$k]['tag'] = $v['tag'];
			$feedbacklist[$k]['content'] = $v['content'];
			$feedbacklist[$k]['create_tm'] = date("Y-m-d H:i",$v['create_tm']);
			$feedbacklist[$k]['last_tm'] = date("Y-m-d H:i",$v['last_tm']);
                        $feedbacklist[$k]['operator'] = $v['operator'];
			$reply_desc = $model -> table('dev_feedback_reply') -> field('id,fid,create_tm,content')->where(array('fid'=>$v['id'],'back_dev'=>1))->order('id desc')->find();
			$reply_desc2 = $model -> table('dev_feedback_reply') -> field('content,create_tm')->where(array('fid'=>$v['id'],'back_dev'=>0))->order('id desc')->find();
			$feedbacklist[$k]['desc_content2'] = $reply_desc2['content'];
			$feedbacklist[$k]['desc_content'] = $reply_desc['content'];
			$feedbacklist[$k]['reply_tm'] = $reply_desc['create_tm'] ? date("Y-m-d H:i",$reply_desc['create_tm']) : '';
			$feedbacklist[$k]['reply_tm2'] = $reply_desc2['create_tm'] ? date("Y-m-d H:i",$reply_desc2['create_tm']) : '';
			$dev_id[] = $v['dev_id'];
		}
		//开发者信息
	 	$dev['status'] = 0;
		$dev['dev_id'] = array('in',$dev_id);
		$dev_name = $model->table('pu_developer')->where($dev)->field('dev_id,dev_name,type,email')->select();
		$dev_all = array();
		foreach($dev_name as $m){
			$dev_all[$m['dev_id']] = $m;
		}
		foreach($feedbacklists as $k => $v){
			//type 0公司 1个人 2团队
			$feedbacklist[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
			$feedbacklist[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
			$feedbacklist[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
			$feedbacklist[$k]['dev_id'] = $dev_all[$v['dev_id']]['dev_id'];
		}
		//echo '<pre>'; var_dump($feedbacklist); exit;
		$this->assign('feedbacklist',$feedbacklist);
		$page->rollPage = 10;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','首页');
        $page->setConfig('last','尾页');			
		$this -> assign('page', $page->show());
		$this -> assign('count', $total);
		$param = http_build_query($_GET);
		$this -> assign('param',$param);
		$this ->display();
	}
	//信息管理  反馈建意 删除
	function feedback_del(){
		$model = new Model();
		$data['status'] = 0;
		$data['last_tm'] = time();
        $data['operator'] = $_SESSION['admin']['admin_user_name'];
		if(!empty($_GET['id'])){
			$ret = $model->TABLE('dev_feedback')->WHERE(array('id'=>$_GET['id']))->FIELD('last_status')->find();
			$res = $model->table('dev_feedback')->data($data)->where(array('id'=>$_GET['id']))->save();
			if($res){
				if($ret['last_status']){
					$this->writelog('删除反馈ID为' . $_GET['id'] . '的数据','dev_feedback',$_GET['id'],__ACTION__,'processed','del');
				}else{
					$this->writelog('删除反馈ID为' . $_GET['id'] . '的数据','dev_feedback',$_GET['id'],__ACTION__,'','del');
				}
				
				//$this -> success('删除成功');
				exit(json_encode(array('code'=>1,'msg'=>array(0=>$_GET['id']))));
			}else{
				$this -> error('删除失败');
			}
		} else if(isset($_GET['feed_id'])){		
			$id_arr =  explode(',',$_GET['feed_id']);
			$where['id'] = array('in',$id_arr);
			$ret = $model->TABLE('dev_feedback')->WHERE(array('id'=>array('in',$id_arr)))->FIELD('last_status')->find();
			$res = $model->table('dev_feedback')->data($data)->where($where)->save();
			if($res){
				foreach($id_arr as $val){
					if($ret['last_status']){
						$this->writelog('删除反馈ID为' . $_GET['feed_id'] . '的数据','dev_feedback',$_GET['feed_id'],__ACTION__,'processed','del');
					}else{
						$this->writelog('删除反馈ID为' . $_GET['feed_id'] . '的数据','dev_feedback',$_GET['feed_id'],__ACTION__,'','del');
					}
				}
				$this -> success('批量删除成功');
			}else{
				$this -> error('删除失败');
			}
		}
	}
	//开发者信息管理 信息反馈 回复显示 
	function feedback_reply(){
		$model = new Model();
		$fid = $_GET['id'];
		//回复标题 图片
		$reply = $model->table('dev_feedback')->where(array('id'=>$fid))->find();
		if($reply['att1']){
			$new_array['att1']=$reply['att1'];
		}
		if($reply['att2']){
			$new_array['att2']=$reply['att2'];
		}
		if($reply['att3']){
			$new_array['att3']=$reply['att3'];
		}
		$img_host = IMGATT_HOST;
		//回复内容
		$reply_content = $model->table('dev_feedback_reply')->where(array('fid'=>$fid))->order('create_tm desc')->select();
		$replycontent = array();
		foreach($reply_content as $k => $v){
			$replycontent[$k]['id'] = $v['id'];
			$replycontent[$k]['back_dev'] = $v['back_dev'];
			$replycontent[$k]['content'] = $v['content'];
			$replycontent[$k]['att1'] = $v['att1'];//开发者回复截图
			$replycontent[$k]['att2'] = $v['att2'];
			$replycontent[$k]['att3'] = $v['att3'];
			$replycontent[$k]['create_tm'] = date("Y-m-d H:i:s",$v['create_tm']);
		}
		//var_dump($replycontent);
		$this->assign('table',$reply['content']);
		$this->assign('fid',$fid);
		$this->assign('img_host',$img_host);
		$this->assign('new_array',$new_array);
		$this->assign('replycontent',$replycontent);
		$this->assign('create_tm',date("Y-m-d H:i:s",$reply['create_tm']));
		$this->assign('last_status',$_GET['last_status']);
		//回复内容
		$reason_list = $model -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 9 ))->order('pos asc,id desc')->select();
		foreach($reason_list as &$val){
		    if($val['content2']){
		        $val['content2'] = explode('<br />', $val['content2']);
		    }
		}	
		$this -> assign('reason_list',$reason_list);			
		$this -> display();
	}
	//开发者审核平台 开发者信息管理 反馈建意列表 回复提交
	function reply_add(){
	    header("Content-type: text/html; charset=utf-8");
		$model = new Model();
		if(!empty($_POST['content'])){			
			$time= time();
			$map['fid'] = $_POST['fid'];
			$map['content'] = $_POST['content'];
			$map['create_tm'] = $time;
			$map['back_dev'] = 1;
			$reset = $model->table('dev_feedback_reply')->data($map)->add();
			if($reset){
				//stat字段自动加1
				$res = $model->table('dev_feedback')->where(array('id'=>$_POST['fid']))->field('last_status')->find();
				$sql = "UPDATE `dev_feedback` SET `last_tm`={$time},`stat`=stat+1 ,`last_status`= 1,`admin_tm`={$time} WHERE ( `id` = {$_POST['fid']} )";
				$model->table('dev_feedback')->query($sql);
				//echo $model->getlastsql();exit;
				if($res['last_status']){
					$this -> writelog("回复的ID为".$reset."内容为".$map['content'],'dev_feedback',$reset,__ACTION__,'processed','edit');
				}else{
					$this -> writelog("回复的ID为".$reset."内容为".$map['content'],'dev_feedback',$reset,__ACTION__,'','edit');
				}
				
				exit(json_encode(array('code'=>'1','msg'=>'回复成功')));				
				// echo "<script>alert('回复成功');</script>";
				// $this->redirect('feedback_suggestions','' , 0.5, '');
			}
		}else{
			$this -> error('回复的内容不能为空');
		}
	}
	//软件反馈
	public function soft_feedback(){
		
	}
	//添加标签
	public function edittag(){
	    $model = new Model();
	    $data['tag'] = trim($_GET['tag']);
	    if(!empty($_GET['id']) && $data['tag']){
	        $res = $model->table('dev_feedback')->where(array('id'=>$_GET['id']))->save($data);
	        //echo $model->getlastsql();
	        if($res){
	            exit(json_encode(array('code'=>1,'msg'=>array(0=>$_GET['id']))));
	        }else{
	            exit(json_encode(array('code'=>0,'msg'=>array(0=>$_GET['id']))));
	        }
	    }	    
	}
	//导出数据
	function feedback_suggestions_export(){
		$Export = D("Dev.Export");
		//分页		
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 100;
		$data = $Export->get_feedback_suggestions($_GET,$limit,$p);
		exit(json_encode($data));
	}
	//得到get值从session
	public function getGetValfromSession($session_type){
	    foreach($_SESSION[$session_type] as $k=>$v){
	        $_GET[$k]	=	$_GET[$k]?$_GET[$k]:$_SESSION[$session_type][$k];
	    }
	}
	//set session值从get
	public function setGetValfromSession($session_type){
	    unset($_SESSION[$session_type]);
	    foreach($_GET as $k=>$v){
	        if($v){
	            $_SESSION[$session_type][$k]	=	$_GET[$k];
	        }
	    }
	}
	//信息管理__开发者反馈_已处理操作
	function handle_status(){
		$model = new Model();
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要通过的对象！')));
		$where = array(
			'id' => array('in',$id_arr),
		);
		$remark = $_GET['remark'];
		if(mb_check_encoding($remark,"utf-8") != true){
			$remark = iconv("gbk","utf-8", $remark);
		}
		$map = array(
			'last_status' => 1,
			'last_tm' => time(),
			'remark'=>$remark,
		);
		$res = $model->table('dev_feedback')->where($where)->save($map);
		if($res){
			$this->writelog('把反馈ID为' . $_GET['id'] . '的数据转到已处理列表','dev_feedback',$_GET['id'],__ACTION__,'','edit');
			exit(json_encode(array('code'=>1,'msg'=>$id_arr)));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'转到已处理列表失败')));
		}
	}	
}
?>