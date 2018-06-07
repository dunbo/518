<?php 

Class TestBlacklistAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.TestBlacklist');
    }
	
	public function index(){
        $wheresql = array();
        if(!empty($_GET['username'])){
            $wheresql['uitype'] = 'user';
            $wheresql['username'] = array('like','%'.trim($_GET['username']).'%');
        }elseif(!empty($_GET['uid'])){
            $wheresql['uitype'] = 'user';
            $wheresql['uid'] = trim($_GET['uid']);
        }elseif(!empty($_GET['ip'])){
            $wheresql['uitype'] = 'ip';
            $wheresql['ip'] = trim($_GET['ip']);
        }
        
        if(!empty($_GET['starttime'])){
            $wheresql['time'] = array('egt',strtotime($_GET['starttime']));
        }
        if(!empty($_GET['endtime'])){
            $wheresql['time'] = array('elt',strtotime($_GET['endtime']));
        }
        
        $wheresql['status'] = 1;
        
        
        import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->model -> where($wheresql)->count();
		
		$Page = new Page($count, 20, $param);
		$result = $this->model -> where($wheresql)-> limit($Page->firstRow . ',' . $Page->listRows)->order('time desc')-> select();
		
		//保留标签功能
		$show = $Page->show();
		$lr = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
		
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function add(){
		$this -> display();
	}
	
	public function doadd(){
        if(empty($_POST['uitype']) || empty($_POST['uip'])){
            $this->error("用户名/IP 不能为空");
        }
        $data = $where = array();
        //区分用户名 /IP
        if($_POST['uitype'] == 'ip'){
            $data['ip'] = $where['ip'] = trim($_POST['uip']);
            $data['uitype'] = $where['uitype'] = 'ip';
            $where['status'] = 1;
            $res1 = $this->model->where($where)->field('id')->limit(1)->select();
        }elseif($_POST['uitype'] == 'user'){
            $data['uid'] = $where['uid'] = trim($_POST['uip']);
            $data['username'] = trim($_POST['username']);
            $data['uitype'] = $where['uitype'] = 'user';
            $where['status'] = 1;
            $res2 = $this->model->where($where)->field('id')->limit(1)->select();
        }
        // 封禁类型
        $data['type'] = 0;
        if(isset($_POST['apply'])){
            $data['type'] += 1;
            if($_POST['apply_validity'] == 2){
                $data['apply_validity'] = 2;
                empty($_POST['apply_time']) && $this->error("时间不能为空");
                $data['apply_time'] = strtotime($_POST['apply_time']);
                $data['apply_time'] < time() && $this->error("时间不能小于当前时间");
            }else{
                $data['apply_validity'] = 1;
            }
        }
        if(isset($_POST['posttest'])){
            $data['type'] += 2;
            if($_POST['posttest_validity'] == 2){
                $data['posttest_validity'] = 2;
                empty($_POST['posttest_time']) && $this->error("时间不能为空");
                $data['posttest_time'] = strtotime($_POST['posttest_time']);
                $data['posttest_time'] < time() && $this->error("时间不能小于当前时间");
            }else{
                $data['posttest_validity'] = 1;//永久
            }
        }
        $data['reason'] = trim($_POST['reason']);
        $data['time'] = time();
        $type = 'edit';
        if(!empty($res1)){
            $where = array('id'=>$res1[0]['id']);
            $target_id = $res1[0]['id'];
            $res = $this->model->where($where)->save($data);
        }elseif(!empty($res2)){
            $where = array('id'=>$res2[0]['id']);
            $target_id = $res2[0]['id'];
            $res = $this->model->where($where)->save($data);
        }else{
            $type = 'add';
            $res = $this->model->add($data);
            $target_id = $res;
        }
        $this -> writelog("智友内容管理-众测黑名单 已添加众测黑名单id[{$target_id}]",'zy_test_blacklist',$target_id,__ACTION__,'',$type);
		$this->success("添加成功！");
	}
	
	public function ajax_search(){
        $model = D('Zhiyoo.bbs');
        $where = array('username'=>$_GET['username']);
        $result = $model->table('x15_common_member')->where($where)->limit(1)->field('uid,username')->select();
        if(empty($result)){
            echo json_encode(array('code'=>0));
        }else{
            $result[0]['code'] = 1 ;
            echo json_encode($result[0]);
        }
	}
	
	public function edit(){
        $where = array('id'=>$_GET['id']);
        $result = $this->model->where($where)->select();
        if($result[0]['uitype'] == 'user'){
            $uinfo = $result[0]['username'];
        }elseif($result[0]['uitype'] == 'ip'){
            $uinfo = $result[0]['ip'];
        }
        
        if($result[0]['type'] & 1){
            $apply = true;
            if($result[0]['apply_validity'] == 2){
                $result[0]['apply_time'] = date('Y-m-d H:i:s',$result[0]['apply_time']);
            }
        }
        if($result[0]['type'] & 2){
            $posttest = true;
            if($result[0]['posttest_validity'] == 2){
                $result[0]['posttest_time'] = date('Y-m-d H:i:s',$result[0]['posttest_time']);
            }
        }
        
		$this -> assign('uinfo',$uinfo);
		$this -> assign('apply',$apply);
		$this -> assign('posttest',$posttest);
		$this -> assign('result',$result[0]);
		$this -> display();
	}
	
	public function doedit(){
        $data = array();
        $id = trim($_GET['id']);
        // 封禁类型
        $data['type'] = 0;
        if(isset($_POST['apply'])){
            $data['type'] += 1;
            if($_POST['apply_validity'] == 2){
                $data['apply_validity'] = 2;
                empty($_POST['apply_time']) && $this->error("时间不能为空");
                $data['apply_time'] = strtotime($_POST['apply_time']);
                $data['apply_time'] < time() && $this->error("时间不能小于当前时间");
            }else{
                $data['apply_validity'] = 1;
            }
        }
        if(isset($_POST['posttest'])){
            $data['type'] += 2;
            if($_POST['posttest_validity'] == 2){
                $data['posttest_validity'] = 2;
                empty($_POST['posttest_time']) && $this->error("时间不能为空");
                $data['posttest_time'] = strtotime($_POST['posttest_time']);
                $data['posttest_time'] < time() && $this->error("时间不能小于当前时间");
            }else{
                $data['posttest_validity'] = 1;//永久
            }
        }
        $data['reason'] = trim($_POST['reason']);
        $data['time'] = time();
        $where = array('id'=>$id);
        $res = $this->model->where($where)->save($data);
        if($res === false)$this->error("编辑失败！");
        else{
            $this -> writelog("智友内容管理-众测黑名单 已编辑众测黑名单id为[{$id}]",'zy_test_blacklist',$id,__ACTION__,'','edit');
            $this->success("编辑成功！");
        }
	}
	
	public function del(){
        $where = array('id'=>$_GET['id']);
        $result = $this->model->where($where)->save(array('status'=>-1));
        $this -> writelog("智友内容管理-众测黑名单 已解除众测黑名单id[{$_GET['id']}]",'zy_test_blacklist',$_GET['id'],__ACTION__,'','del');
		$this->success("解除成功！");
	}
}