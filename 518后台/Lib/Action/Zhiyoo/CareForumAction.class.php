<?php 
Class CareForumAction extends CommonAction{
	private $bbsmodel;
	private $model;

	
	public function _initialize() {
        parent::_initialize();
		$this->bbsmodel = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.Zhiyoo');
	}
    
    public function showList(){
        $res = $this->model->table('zy_careforum')->find();
        $showadd = 1;
        if($res){
            $showadd = 0;
            $fname = $this->bbsmodel->table('x15_forum_forum')->field(array('name'))->where("fid={$res['fid']}")->find();
        }
        $this->assign('fname',$fname['name']);
        $this->assign('res',$res);
        $this->assign('showadd',$showadd);
        $this->display();
    }
    
    public function add(){
        if($_GET['action'] == 'do'){
            if($_POST['id'])  $data['id'] = intval($_POST['id']);
            $data['title'] = trim($_POST['title']);
            $data['fid'] = intval($_POST['fid']);
            $data['description'] = trim($_POST['description']);
            $data['position'] = intval($_POST['position']);
            // echo $data['title'],mb_strlen($data['title'],'utf8');die;
            if(!$data['title'] || mb_strlen($data['title'],'utf8')>8){
                $this->error('标题只能填写1-8个字');
            }
            if(!$data['fid']){
                 $this->error('请选择正确的板块');
            }
            if(!$data['description']){
                 $this->error('请选填写正确的描述');
            }
            if($data['position']<=0 ||$data['position']>10){
                 $this->error('显示位置只能填写1-10');
            }
            if($data['id']){
                $tips = '编辑';
                $res = $this->model->table('zy_careforum')->where('id='.$data['id'])->save($data);
                $this -> writelog("智友内容管理-智友精选栏目-板块配置 {$tips}id为{$data['id']}板块配置","zy_careforum",$data['id'],__ACTION__ ,"",'edit');
            }else{
                $tips = '添加';
                $data['status'] = 1;
                $res = $this->model->table('zy_careforum')->add($data);
                $this -> writelog("智友内容管理-智友精选栏目-板块配置 {$tips}一条板块配置","zy_careforum",$res,__ACTION__ ,"",'add');
            }
            $this -> assign('jumpUrl',"/index.php/Zhiyoo/CareForum/showList/");
            $this->success($tips.'成功');
        }else{
            if($_GET['id']){
                $id = (int) $_GET['id'];
                $res = $this->model->table('zy_careforum')->where('id='.$id)->find();
                if($res['fid'])
                    $fname = $this->bbsmodel->table('x15_forum_forum')->field(array('name'))->where("fid={$res['fid']}")->find();
            }
            $this->assign('res',$res);
            $this->assign('fname',$fname['name']);
            $this->assign('id',$id);
            $this->display();
            
        }
       
    }
    
    public function changeSwitch(){
      
        $status = 0;
        $tips = '停用'; 
        
        $id = $_GET['id'];
        if($_GET['status'] == 1){
            $status = 1;
            $tips = '开启';
           
        }
        $this->model->table('zy_careforum')->where('id='.$id)->save(array('status'=>$status));
        $this -> writelog("智友内容管理-智友精选栏目-板块配置 {$tips}了板块配置","zy_careforum",$id,__ACTION__ ,"",'edit');
        $this->success($tips.'成功');
    }

    public function forum_list(){
		$chkfid = $_GET['fid'] ? $_GET['fid']: 0;
		$grouplist = $this-> bbsmodel -> getForumList('group'); //论坛分区数据
		$forumlist = array();
		$subforumlist = array();
		foreach($grouplist as $index =>  $ginfo){
			$forums = $this ->bbsmodel -> getForumList('forum',$ginfo['fid']); //论坛板块数据
			if(!$forums){
				unset($grouplist[$index]);
				continue;
			}
			$forumlist[$ginfo['fid']] = $forums;
		}
		$this -> assign('chkfid',$chkfid);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('forumlist',$forumlist);
		$this -> display();
	}
}