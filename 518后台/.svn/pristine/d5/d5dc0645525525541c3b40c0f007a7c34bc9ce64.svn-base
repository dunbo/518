<?php 
Class RecContentAction extends CommonAction{
	private $bbsmodel;
	private $model;

	
	public function _initialize() {
        parent::_initialize();
		$this->bbsmodel = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.Zhiyoo');
	}
    
    public function showList(){
        $res = $this->model->table('zy_reccontent')->find();
        $showadd = 1;
        if($res){
            $showadd = 0;
        }
        $hotforum = $this->model->query("SELECT id,class,b_fid,b_name,b_plat,name,icon,status FROM `zy_bbs_plate_area` WHERE class=1 AND status!='-1' ORDER BY status DESC , modifytime ASC ");
        foreach($hotforum as $f){
            $forum[$f['b_fid']] = $f['name'];
        }

        $this->assign('fname',$forum[$res['fid']]);
        $this->assign('res',$res);
        $this->assign('showadd',$showadd);
        $this->display();
    }
    
    public function add(){
        if($_GET['action'] == 'do'){
            if($_POST['id'])  $data['id'] = intval($_POST['id']);
            $data['title'] = trim($_POST['title']);
            $data['fid'] = intval($_POST['fid']);
            if(!$data['title'] || mb_strlen($data['title'],'utf8')>8){
                $this->error('文案只能填写1-8个字');
            }
            if(!$data['fid']){
                 $this->error('请选择正确的版块');
            }
            if($data['id']){
                $tips = '编辑';
                $res = $this->model->table('zy_reccontent')->where('id='.$data['id'])->save($data);
                $this -> writelog("智友内容管理-论坛页面-推荐内容运营位 {$tips}了id为{$data['id']}推荐内容版块","zy_reccontent",$data['id'],__ACTION__ ,"",'edit');
            }else{
                $tips = '添加';
                $data['status'] = 1;
                $res = $this->model->table('zy_reccontent')->add($data);
                $this -> writelog("智友内容管理-论坛页面-推荐内容运营位 {$tips}了一条推荐内容板块","zy_reccontent",$res,__ACTION__ ,"",'add');
            }
            $this -> assign('jumpUrl',"/index.php/Zhiyoo/RecContent/showList/");
            $this->success($tips.'成功');
        }else{
            $hotforum = $this->model->query("SELECT id,class,b_fid,b_name,b_plat,name,icon,status FROM `zy_bbs_plate_area` WHERE class=1 AND status!='-1' ORDER BY status DESC , modifytime ASC ");
            foreach($hotforum as $f){
                $forum[] = array('fid'=>$f['b_fid'],'fname'=>$f['name']);
            }
            if($_GET['id']){
                $id = (int) $_GET['id'];
                $res = $this->model->table('zy_reccontent')->where('id='.$id)->find();
            }
            $this->assign('res',$res);
            $this->assign('forum',$forum);
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
        $this->model->table('zy_reccontent')->where('id='.$id)->save(array('status'=>$status));
        $this -> writelog("智友内容管理-论坛页面-推荐内容运营位 {$tips}了配置","zy_reccontent",$id,__ACTION__ ,"",'edit');
        $this->success($tips.'成功');
    }


}