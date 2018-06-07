<?php
class PushManagementAction extends CommonAction {
	
  
    public function push_list()
	{
        $model = M('push_api');
        $where=array();
        $where['status'] = 1;
        if($_GET['s_type'] && $s_type = trim($_GET['s_type'])){
            $where['type'] = array('eq', $s_type);
            $this->assign("type", $s_type);
        }
        if($_GET['s_title'] && $s_title = trim($_GET['s_title'])){
            $where['title'] = array('eq', $s_title);
            $this->assign("title", $s_title);
        }
        if($_GET['begintime'] && $begintime = strtotime(trim($_GET['begintime']))){
            $where["create_tm"] = array('egt', $begintime);
            $this->assign("begintime", $_GET['begintime']);
        }
        if($_GET['endtime'] && $endtime = strtotime(trim($_GET['endtime']))){
            $where["create_tm"] = array('elt', $endtime);
            $this->assign("endtime", $_GET['endtime']);
        }
        if($begintime && $endtime){
            $where["create_tm"] = array('exp', ">=$begintime and create_tm<=$endtime");
        }
        $count_list = $model->where($where)->select();
		$count = count($count_list);
        import("@.ORG.Page");
		$p = new Page ($count, 20);
		$list = $model -> where($where)->limit($p->firstRow.','.$p->listRows)->order('create_tm desc') -> select();
        $page = $p->show();
		$this -> assign("page",$page);

        foreach ($list as $key => $value) 
		{
			if($value['type']){
				$list[$key]['type']=($value['type']==1)?'intent':(($value['type']==2)?'url':'payload');
			}else{
				$list[$key]['type']='';
			}
            $list[$key]['create_tm']=date('Y-m-d H:i:s',$value['create_tm']);
            $list[$key]['start_tm']=$value['start_tm']?date('Y-m-d H:i:s',$value['start_tm']):'';
            $list[$key]['end_tm']=$value['end_tm']?date('Y-m-d H:i:s',$value['end_tm']):'';
            if($value['send_status']==1){
                $list[$key]['send_status'] = '同步成功';
            }else if($value['send_status']==2){
                $list[$key]['send_status'] = '同步失败';
            }else if($value['send_status']==3){
                $list[$key]['send_status'] = '停止';
            }else{
                $list[$key]['send_status'] = '未同步';
            }
        }
        $this->assign('list', $list);
        $this->display();
    }
    
    public function add_push() 
	{
        if($_POST) 
		{
            $model = M('push_api');
			
            $map = array();
			
			$map['create_tm']=time();
			if(!$_POST['start_tm']){
				$this->error("开始时间不能为空");
			}
			if(!$_POST['end_tm']){
				$this->error("结束时间不能为空");
			}

			$map['start_tm']=strtotime($_POST['start_tm']);
			$map['end_tm']=strtotime($_POST['end_tm']);
			if($map['end_tm']<=$map['start_tm']){
				$this->error("结束时间必须大于开始时间");
			}

			$map['status']=1;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];

			$map['title']=$_POST['title'];	
			$map['content']=$_POST['content'];	
			$map['intent']=$_POST['intent'];	
			$map['cid']=$_POST['cid'];	
			if(!$map['title']){
				$this->error('标题必填');
			}
			if(!$map['content']){
				$this->error('内容必填');
			}
			$map['url']=$_POST['url'];	
			$pattern ='/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/';
			if(preg_match($pattern, $map['url'])){
				$this->error("push通知链接格式不正确");
			}
			$map['payload']=$_POST['payload'];	
			$map['type']=$_POST['type'];	
			if($map['payload'] || $map['url'] || $map['intent']){
				if(!$map['type']){
					$this->error("类型必选");
				}
			}
			$pattern = '/^intent:#Intent;.*;end$/';
			if(!preg_match($pattern,$map['intent'])){
	            $this->error('intent格式错误，需以"intent:#Intent;"开始并已";end"结束');
	        }
            // 添加
            $ret = $model->add($map);
            if ($ret) {
                $this->writelog("安智市场手机-厂商推送：添加了id为{$ret}的记录",'sj_push_api',$ret,__ACTION__ ,"","add");
                $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/PushManagement/push_list/');

//                $model = D('Getui');
//                $map['id']=$ret;
//		        $re_json=$model -> pushMessageToApp($map);
//		        if($re_json){
//		        	$re_arr=json_decode($re_json,true);
//		        	if($re_arr['code']==0){
//		        		$this->error($re_arr['msg']);
//		        	}
//		        }
                $this->success("添加成功！");
            } else {
                $this->error("添加失败");
            }
        }
		else 
		{
            $this->display();
        }
    }

    public function stop_push(){
        if(!$_GET['id']){
            $this->error('缺少ID');
        }
        $model = D('Getui');
        $res = $model->stoptask($_GET['id']);
        if($res){
            $this->success('停止成功');
        }else{
            $this->error('停止失败');
        }
    }
}
?>
