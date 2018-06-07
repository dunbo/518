<?php

class HookPushAction extends CommonAction {
    public function index() 
	{
        

        $act = empty($_GET['act']) ? 'index' : $_GET['act'];
        
        //var_dump($act);
        //exit;
        if(in_array($act,array('edit','add','del'))) {
            return $this->{$act}();
        }
        
        
        $where = array();
        $where['status'] = 1;
        $where['platform'] = 1;
        $where['channel'] = 1;

        
        $model_push = D('Sj.Push');

        // 分页
        import("@.ORG.Page");
        $limit = 20;
        $count = $model_push->table('sj_push')->where($where)->count();
        $page  = new Page($count, $limit);
        

        // 当前页数据
        $list = $model_push->table('sj_push')->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();


        $this->assign('list', $list);
        $this->assign("page", $page->show());
        $this->display();
    }
    public function edit() 
	{

        $id = intval($_GET['id']);
        $where = array();
        $where['id'] = $id;
        $model_push = D('Sj.Push');
        
        $item = $model_push->table('sj_push')->where($where)->find();
 
        //var_dump($item);exit;

        
        if(!empty($_POST)) {
            
            $data = array();
            $data['start_time'] = strtotime($_POST['start_time']);
            $data['end_time'] = strtotime($_POST['end_time']);
            $data['update_time'] = time();
            
            
            $exp = array();
            $exp['push_type'] = 1;
            $exp['version_code'] = 2147483647;
            
            
            $exp['hook_interval'] = intval($_POST['hook_interval']);
            $exp['flag'] = intval($_POST['flag']);
            $exp['channel_code'] = $_POST['channel_code'];
            $exp['scheme'] = $_POST['scheme'];
            $exp['package_name'] = $_POST['package_name'];

            $exp['action'] = $_POST['action'];            
            $exp['class_name'] = $_POST['class_name'];
            $exp['permission'] = $_POST['permission'];
            $exp['process'] = $_POST['process'];
            $exp['type'] = $_POST['type'];
            
            
            $exp['params'] = $_POST['params'] ? explode(',',$_POST['params']) : array();
            $exp['category'] = $_POST['category'] ? explode(',',$_POST['category']) : array();

            
            
            
            
            
            
            $data['expand'] = json_encode($exp);
            $result = $model_push->editApiPush($id,$data);
            
            $this->assign("jumpUrl",SITE_URL.'/index.php/Webmarket/HookPush/index');
            if($result) {
                $this->writelog("更新PUSH设置：修改了id为{$id}的记录",'sj_push',$id,__ACTION__ ,"","edit");
                $this -> success("操作成功");
            } else {
                $this->error("操作失败");
            }




        } else {

            $exp = json_decode($item['expand'],true);
            $this->assign('item', $item);
            $this->assign('exp', $exp);
            $this->display('Webmarket:HookPush:edit');
        }

    }


    public function del() 
	{
        
        $id = intval($_REQUEST['id']);
        
        $where = array();
        $where['id'] = $id;
        $model_push = D('Sj.Push');

 

        $data = array();

        $data['update_time'] = time();
        
        $data['status'] = 0;

        $result = $model_push->editApiPush($id,$data);
        
        $this->assign("jumpUrl",SITE_URL.'/index.php/Webmarket/HookPush/index');
        if($result) {
            $this->writelog("更新PUSH设置：修改了id为{$id}的记录",'sj_push',$id,__ACTION__ ,"","edit");
            $this -> success("操作成功");
        } else {
            $this->error("操作失败");
        }






    }




    public function add() 
	{


        $model_push = D('Sj.Push');
        $model = new Model();


        //var_dump($item);exit;

        
        if(!empty($_POST)) {
            
            $data = array();
            $data['start_time'] = strtotime($_POST['start_time']);
            $data['end_time'] = strtotime($_POST['end_time']);
            $data['update_time'] = time();
            $data['platform'] = 1;
            $data['channel'] = 1;
            
            
            $exp = array();
            $exp['push_type'] = 1;
            $exp['version_code'] = 2147483647;
            
            
            
            $exp['hook_interval'] = intval($_POST['hook_interval']);
            $exp['flag'] = intval($_POST['flag']);
            $exp['channel_code'] = $_POST['channel_code'];
            $exp['scheme'] = $_POST['scheme'];
            $exp['package_name'] = $_POST['package_name'];

            $exp['action'] = $_POST['action']; 
            $exp['class_name'] = $_POST['class_name'];
            $exp['permission'] = $_POST['permission'];
            $exp['process'] = $_POST['process'];
            $exp['type'] = $_POST['type'];
            
            
            $exp['params'] = $_POST['params'] ? explode(',',$_POST['params']) : array();
            $exp['category'] = $_POST['category'] ? explode(',',$_POST['category']) : array();

            $data['expand'] = json_encode($exp);
           

            $result = $model_push->addApiPush($data);
            
            $this->assign("jumpUrl",SITE_URL.'/index.php/Webmarket/HookPush/index');
            if($result) {
                $this->writelog("更新PUSH设置：添加了id为{$result}的记录",'sj_push',$id,__ACTION__ ,"","add");
                $this -> success("操作成功");
            } else {
                $this->error("操作失败");
            }




        } else {
            $this->assign('item', array());
            $this->assign('exp', array());
            $this->display('Webmarket:HookPush:edit');
        }

    }


    
    public function delete_content() 
	{
        $model = M();
        $id = $_GET['id'];
        $data['status'] = 0;
        $del = $model->table('sj_update_push')->where("id = {$id}")->save($data);
        if($del) {
            $this->writelog("更新PUSH设置：删除了id为{$id}的记录",'sj_update_push',$id,__ACTION__ ,"","del");
            $this -> success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
	public function import_softs()
	{

	}

}

