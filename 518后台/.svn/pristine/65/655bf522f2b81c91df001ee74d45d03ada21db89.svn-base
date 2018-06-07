<?php

/**
 * 合作软件检测
 */

class CooperMonitorAction extends CommonAction {
    //飞沃回测列表
    public function back_test_list() {
        $feiwo = M('backtest_soft');
        $status = isset($_GET['status'])?$_GET['status']:2;
        $where = ''; 
        if($status != 0){
            $where = 'status = '.$status;
        }        
        $count = $feiwo->where($where)->field('id')->order('up_tm desc')->select();
        $total   = count($count);
        //分页		
        //$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        import("@.ORG.Page");
        $Page = new Page($total,20, $limit);       
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $list = $feiwo->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $key=>$val){
            //获取下载量
            $down = $this->get_down_num($val['package']);
            $list[$key]['down_cnt'] = $down;
        }
        $this->assign('page', $show);
        $this->assign('total',$total);
        $this->assign('list',$list);
        $this ->display('back_test_list');		
    }

    
    //采集,驳回列表
    public function reject_list(){
        $model	= new Model();
        $page	= isset($_GET['page'])?intval($_GET['page']):1;
        $status_30	= isset($_GET['status_30'])?intval($_GET['status_30']):2;
        $limit	= isset($_GET['limit']) ? $_GET['limit'] : 20;
        $step	= $page*$limit;
        $where	= '';
        $where	.= 's.status = 1 and s.status_30 = ' .$status_30;
        $total = $model->table('sj_download_30_operation s')->where($where)->count();
        if($step>$total){
        	$page = intval($total/$limit);
        }
        $step	= $page*$limit;
        import('@.ORG.Page2');
        $Page=new Page($total,$limit);                
        $list = $model->table('sj_download_30_operation s')->join('pu_developer u on u.dev_id = s.dev_id')->field('s.*,u.dev_id,u.dev_name,u.email,u.type,u.mobile')->where($where)->order('s.id desc ')->limit($Page->firstRow.','.$Page->listRows)->select();
        //echo $model->getLastSql();
        foreach($list as $key=>$val){
            //获取下载量
            $down = $this->get_down_num($val['package']);
            $list[$key]['downloaded'] = $down;
        }
        $Page->rollPage = $limit;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');        
        $show =$Page->show();
        $this->assign('status_30',$status_30);
        $this->assign('list',$list);
        $this->assign('total',$total);
        $this->assign ("page", $show );  
        $this->display();      
    }
    
    Public function del_reject(){
        $model	= new Model();
        $id = isset($_GET['id'])?intval($_GET['id']):0;
        if($id<=0){
            exit(json_encode(array('code'=>0,'msg'=>'参数错误')));
        }
        $res = $model->query("update sj_download_30_operation set status = 0 where id = {$id} ");
        $this->writelog("采集升级审核驳回记录删除了id为{$id}的数据",'sj_download_30_operation',$id,__ACTION__ ,"","del");
        if($res){
        	exit(json_encode(array('code'=>0,'msg'=>'删除失败')));
        }else{
            exit(json_encode(array('code'=>1,'msg'=>'成功')));
        }
    }
 
	//邮件通知--配置
	public function email_config_list(){
		$model = new Model();
		$total = $model->table('sj_email_config')->where('status=1')->count();	
		import('@.ORG.Page2');
		$limit = 10;
		$Page = new Page($total,$limit);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');			
		$list = $model -> table('sj_email_config')->where('status=1')->limit($Page->firstRow.','.$Page->listRows)->order("update_tm desc")->select();
		$adminid = array();
		foreach($list as $k=>$v){
			$adminid[] = $v['admin_id'];	
		}
		//操作人
		$adminid = array_unique($adminid);
		$where = array(
			'admin_user_id' => array('in',$adminid),
			'admin_state'=>1
		);
		$user_name = get_table_data($where,"sj_admin_users","admin_user_id","admin_user_id,admin_user_name");
		foreach($list as $k=>$v){
			$list[$k]['update_tm'] = $v['update_tm'] ? date("Y-m-d H:i:s",$v['update_tm']) : '';		
			$list[$k]['admin_user'] = $user_name[$v['admin_id']]['admin_user_name'] ?  $user_name[$v['admin_id']]['admin_user_name'] : '';		
			if($v['cc']){
				$cc = explode(';',$v['cc']);
				$list[$k]['cc'] = implode(';<br/>',$cc);
			}		
			if($v['addressee']){
				$cc = explode(';',$v['addressee']);
				$list[$k]['addressee'] = implode(';<br/>',$cc);
			}
		}
		$this -> assign('list',$list);
		$this -> assign('page', $Page->show());	
		$this ->display();		
	}
	//邮件配置--添加
	public function email_config_add(){
		$model = new Model();
		if($_POST){
			$time = time();
			if(empty($_POST['addressee']) || $_POST['addressee'] == "多个邮箱请以英文半角';'分隔" ){
				$this->assign("jumpUrl","/index.php/Dev/CooperMonitor/email_config_list");
				$this->error('请填写收件人');
			}
			if($_POST['cc'] == "多个邮箱请以英文半角';'分隔"){
				$_POST['cc'] == "";
			}
			$data = array(
				'addressee'=> $_POST['addressee'],
				'cc'=> $_POST['cc'],
				'send_frequency'=> $_POST['send_frequency'],
				'add_tm'=> $time,
				'update_tm'=> $time,
				'admin_id'=> $_SESSION['admin']['admin_id'],
			);
			$res = $model->table('sj_email_config')->add($data);
			$this->assign("jumpUrl","/index.php/Dev/CooperMonitor/email_config_list");
			if($res){
				$this->writelog("邮件配置--添加id为{$res}的配置",'sj_email_config',$res,__ACTION__ ,"","add");
				$this->success('操作成功');	
			}else{
				$this->error('操作失败');
			}
		}else{
			$this ->display('email_config');	
		}		
	}
	//邮件配置--编辑
	public function email_config_edit(){
		$model = new Model();
		if($_POST){
			if(empty($_POST['addressee']) || $_POST['addressee'] == "多个邮箱请以英文半角';'分隔" ){
				$this->assign("jumpUrl","/index.php/Dev/CooperMonitor/email_config_list");
				$this->error('请填写收件人');
			}		
			if($_POST['cc'] == "多个邮箱请以英文半角';'分隔"){
				$_POST['cc'] = "";
			}
			if($_POST['cc']){
				$cc = explode(';',$_POST['cc']);
				foreach($cc as $v){
					$pos = strpos($_POST['addressee'],$v);
					if ($pos !== false) {
						$this->assign("jumpUrl","/index.php/Dev/CooperMonitor/email_config_list");
						$this->error('抄送人和收件人冲突');
					}
				}
			}
			$map = array(
				'addressee'=> $_POST['addressee'],
				'cc'=> $_POST['cc'],
				'send_frequency'=> $_POST['send_frequency'],
				'update_tm'=> time(),
				'admin_id'=> $_SESSION['admin']['admin_id'],
			);
			$log_result = $this->logcheck(array('id'=>$_POST['id']),'sj_email_config',$map,$model->table('sj_email_config'));
			$res = $model->table('sj_email_config')->where("id='{$_POST['id']}'")->save($map);
			$this->assign("jumpUrl","/index.php/Dev/CooperMonitor/email_config_list");
			if($res){
				$this->writelog("邮件配置--编辑id为{$_POST['id']},{$log_result}",'sj_email_config',$_POST['id'],__ACTION__ ,"","edit");
				$this->success('操作成功');	
			}else{
				$this->error('操作失败');
			}			
		}else{
			$id = $_GET['id'];
			$list = $model->table('sj_email_config')->where("id='{$id}'")->find();
			$this -> assign('list',$list);
			$this ->display('email_config');
		}
	}   
        
    //获取下载量
    public function get_down_num($package) {
        $model = M('soft');
        $soft = $model->where(array('package'=>$package))->field('softid,total_downloaded,total_downloaded_add,total_downloaded_detain')->order('softid desc')->find();
        $down = $soft['total_downloaded'] + $soft['total_downloaded_add'] - $soft['total_downloaded_detain'];
        return $down;
    }

}
