<?php 

Class MessageAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.Message');
    }
	
	public function index(){
        $message = empty($_GET['message']) ? 0 : trim($_GET['message']);
        $starttime = empty($_GET['starttime']) ? 0 : strtotime($_GET['starttime']);
        $endtime = empty($_GET['endtime']) ? 0 : strtotime($_GET['endtime']);
        
        $wheresql = array('status >= 0');
        if($message){
            $wheresql[] = "message like '%".addslashes($message)."%'";
        }
        if($starttime){
            $wheresql[] = "time >= {$starttime}";
        }
        if($endtime){
            $wheresql[] = "time <= {$endtime}";
        }
        $wheresql = implode(' and ',$wheresql);
        
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
        $data = array();
        if($_POST['receivertype'] == 2){
            $csv = file_get_contents($_FILES['receiver']['tmp_name']);
            $csv = str_replace(array("\r","\n"),'',$csv);
            $csv = trim($csv,',');
            if(!preg_match('/^(\d+,?)+$/',$csv))$this->error("文件格式错误");
            $data['receiver'] = $csv;
            $data['receivertype'] = 2;
        }else{
            $data['receivertype'] = 1;
        }
        if($_POST['timetype'] == 2){
            $data['time'] = empty($_POST['time']) ? time() : strtotime($_POST['time']);
            $data['timetype'] = 2;
        }else{
            $data['time'] = time();
            $data['timetype'] = 1;
        }
        $data['message'] = $_POST['messaget'];
        //查询用户名
        $bbs = D('Zhiyoo.bbs');
        $result = $bbs->table('x15_common_member')->where("uid in ({$csv})")->field('username')->select();
        $data['receivername'] = '';
        foreach($result as $val){
            $data['receivername'] .= $val['username'].',';
        }
        
		$result = $this->model-> add($data);
		if($result !== false){
			$this -> writelog("智友-发送消息 添加id{$result}",'zy_message',$result,__ACTION__,'','add');
			$this->success("添加成功！");
		}else{
			$this->error("添加失败！");
		}
	}
	
	public function edit(){
        $result = $this->model-> where('id = '.$_GET['id'])->select();
        $uid = explode(',',$result[0]['receiver']);
        $count = count($uid);
		$this -> assign('result',$result[0]);
		$this -> assign('count',$count);
		$this -> display();
	}
	
	public function doedit(){
        $data = array();
        if($_POST['receivertype'] == 2){
            if($_FILES['receiver']["size"]>0){
                $csv = file_get_contents($_FILES['receiver']['tmp_name']);
                $csv = str_replace(array("\r","\n"),'',$csv);
                $csv = trim($csv,',');
                if(!preg_match('/^(\d+,?)+$/',$csv))$this->error("文件格式错误");
                $data['receiver'] = $csv;
                //查询用户名
                $bbs = D('Zhiyoo.bbs');
                $result = $bbs->table('x15_common_member')->where("uid in ({$csv})")->field('username')->select();
                $data['receivername'] = '';
                foreach($result as $val){
                    $data['receivername'] .= $val['username'].',';
                }
            }
            $data['receivertype'] = 2;
        }else{
            $data['receiver'] = '';
            $data['receivername'] = '';
            $data['receivertype'] = 1;
        }
        if($_POST['timetype'] == 2){
            $data['time'] = empty($_POST['time']) ? time() : strtotime($_POST['time']);
            $data['timetype'] = 2;
        }else{
            $data['time'] = time();
            $data['timetype'] = 1;
        }
        $data['message'] = $_POST['messaget'];
        
		$result = $this->model->where('id='.$_GET['id'])-> save($data);
		if($result !== false){
			$this -> writelog("智友-发送消息 编辑id{$_GET['id']}",'zy_message',$_GET['id'],__ACTION__,'','edit');
			$this->success("编辑成功！");
		}else{
			$this->error("编辑失败！");
		}
	}
	
	public function del(){
		$result = $this->model ->where('id='.$_GET['id'])->save(array('status'=>-1));
		if($result !== false){
			$this -> writelog("智友-发送消息 已删除id为{$_GET['id']}",'zy_message',$_GET['id'],__ACTION__,'','del');
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
	}
}