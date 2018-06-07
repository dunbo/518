<?php 

Class CareGaojiAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.CareGaoji');
    }
	
	public function index(){
        $result = $this->model->where('status=1')->select();
        $where = array('status'=>1);
        if($_GET['time'])$where['endtime'] = array('elt',time());
        import("@.ORG.Page");
		$param = http_build_query($_GET);
        $count = $this->model -> where($where)->count();
        // var_dump($result);
        $prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
        $Page = new Page($count,$prepage , $param);
        $result = $this->model -> where($where)->order('`order` asc')-> limit("{$Page->firstRow},{$Page->listRows}")->select();
        $show = $Page->show();
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
        $this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> assign('imghost',IMGATT_HOST);
		$this -> display();
	}
	
	public function edit(){
        $where = array('id'=>$_GET['id']);
        $result = $this->model->where($where)->find();
		$this -> assign('result',$result);
		$this -> assign('imghost',IMGATT_HOST);
		$this -> display();
	}
	
	public function doedit(){
        $data['starttime'] = strtotime($_POST['starttime']);
        $data['endtime'] = strtotime($_POST['endtime']);
        if($data['endtime'] <= $data['starttime'])$this->error("开始时间必须小于结束时间");
        if(100 < $_POST['order'] || $_POST['order'] < 1)$this->error("排序值需要在1-100之间");
        else $data['order'] = intval($_POST['order']);
        $where = array('order'=>$data['order'],'status'=>1,'id'=>array('neq',$_GET['id']));
        $res = $this->model->where($where)->find();
        if($res)$this->error("排序值已存在");
            
        $res = $this->model->where(array('id'=>$_GET['id']))->field('tid')->find();
        if($res['tid'] != $_POST['tid']){
            $model = D('Zhiyoo.bbs');
            $data['tid'] = $_POST['tid'] > 0 ? $_POST['tid'] : 0;
            
            $where = array('tid'=>$data['tid'],'status'=>1,'id'=>array('neq',$_GET['id']));
            $res = $this->model->where($where)->find();
            if($res)$this->error("TID已存在");
            
            $res = $model->table('x15_forum_thread')->where(array('tid'=>$data['tid']))->field('posttableid,subject,authorid,author')->find();
            if(!$res)$this->error("编辑失败！TID不存在");
            $data['title'] = $res['subject'];
            $data['authorid'] = $res['authorid'];
            $data['author'] = $res['author'];
            
            $ptable = $res['posttableid'] > 0 ? 'x15_forum_post_'.$res['posttableid'] : 'x15_forum_post';
            $res = $model->table($ptable)->where(array('tid'=>$data['tid'],'first'=>1))->field('pid')->find();
            $attable = 'x15_forum_attachment_'.substr($data['tid'],-1);
            $res = $model->table($attable)->where(array('tid'=>$data['tid'],'pid'=>$res['pid'],'isimage'=>array('neq',0)))->field('attachment')->find();
            if($res)$data['img'] = $res['attachment'];
            else $data['img'] = '';
        }
        
        $data['ext_title'] = $_POST['ext_title'];
        $data['admin_id'] = $_SESSION['admin']['admin_id'];
        if($_FILES['ext_img']['size']>0){
            $attachname = strtolower(pathinfo($_FILES['ext_img']['name'], PATHINFO_EXTENSION ));
            if(!in_array($attachname,array('png','jpg','jpeg')))$this->error("图片格式不正确！");
            $config = array(
                'tmp_file_dir' => '/tmp/',
                'width' => 700,
                'filesize' => 100000,
                'real_width' => 700,
            );
            $datedir = '/CareGaoji/';
            $savepath = UPLOAD_PATH.$datedir;
            $imgpath = $this -> _upload($_FILES['ext_img'],$savepath,$config);
            $data['ext_img']=$datedir.$imgpath;
        }else{
            if(!$_POST['oriimg'])$data['ext_img']='';
        }
            
        $res = $this->model->where(array('id'=>$_GET['id']))->save($data);
        if($res !== false){
            $this -> writelog("灵活运营样式-搞机页面管理 已编辑id为{$_GET['id']}的内容",'zy_care_gaoji', $_GET['id'],__ACTION__ ,"","edit");
            $this->success("编辑成功！");
        }else{
            $this->error("编辑失败！");
        }
	}
	
	public function add(){
		$this -> display();
	}
	
	public function doadd(){
        $data['starttime'] = strtotime($_POST['starttime']);
        $data['endtime'] = strtotime($_POST['endtime']);
        if($data['endtime'] <= $data['starttime'])$this->error("开始时间必须小于结束时间");
        if(100 < $_POST['order'] || $_POST['order'] < 1)$this->error("排序值需要在1-100之间");
        else $data['order'] = intval($_POST['order']);
        $where = array('order'=>$data['order'],'status'=>1);
        $res = $this->model->where($where)->find();
        if($res)$this->error("排序值已存在");
            
        $model = D('Zhiyoo.bbs');
        $data['tid'] = $_POST['tid'] > 0 ? $_POST['tid'] : 0;
        $res = $this->model->where(array('tid'=>$data['tid'],'status'=>1))->find();
        if($res)$this->error("TID已存在");
        $res = $model->table('x15_forum_thread')->where(array('tid'=>$data['tid']))->field('posttableid,subject,authorid,author')->find();
        if(!$res)$this->error("编辑失败！TID不存在");
        $data['title'] = $res['subject'];
        $data['authorid'] = $res['authorid'];
        $data['author'] = $res['author'];
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
        //取原帖第一张图片
        $ptable = $res['posttableid'] > 0 ? 'x15_forum_post_'.$res['posttableid'] : 'x15_forum_post';
        $res = $model->table($ptable)->where(array('tid'=>$data['tid'],'first'=>1))->field('pid')->find();
        $attable = 'x15_forum_attachment_'.substr($data['tid'],-1);
        $res = $model->table($attable)->where(array('tid'=>$data['tid'],'pid'=>$res['pid'],'isimage'=>array('neq',0)))->field('attachment')->find();
        if($res)$data['img'] = $res['attachment'];
        else $data['img'] = '';
        
        $data['ext_title'] = $_POST['ext_title'];
        
        if($_FILES['ext_img']['size']>0){
            $attachname = strtolower(pathinfo($_FILES['ext_img']['name'], PATHINFO_EXTENSION ));
            if(!in_array($attachname,array('png','jpg','jpeg')))$this->error("图片格式不正确！");
            $config = array(
                'tmp_file_dir' => '/tmp/',
                'width' => 700,
                'filesize' => 100000,
                'real_width' => 700,
            );
            $datedir = '/CareGaoji/';
            $savepath = UPLOAD_PATH.$datedir;
            $imgpath = $this -> _upload($_FILES['ext_img'],$savepath,$config);
            $data['ext_img']=$datedir.$imgpath;
        }else{
            $data['ext_img']='';
        }
            
        $res = $this->model->add($data);
        if($res !== false){
            $this -> writelog("灵活运营样式-搞机页面管理 已添加id为{$res}的内容",'zy_care_gaoji', $res,__ACTION__ ,"","add");
            $this->success("添加成功！");
        }else{
            $this->error("添加失败！");
        }
	}
	
	public function ajaxchecktid(){
        $tid = $_GET['tid'] > 0 ? $_GET['tid'] : 0;
        $model = D('Zhiyoo.bbs');
        $data = array('tid' => $tid);
        
        $res = $model->table('x15_forum_thread')->where($data)->field('subject,author')->find();
        if($res){
            $out = array('ok'=>1,'title'=>$res['subject'],'author'=>$res['author']);
            echo json_encode($out);
            exit;
        }else{
            echo json_encode(array('ok'=>0));
            exit;
        }
        
	}
	
	public function doeditorder(){
        $logsuccess = $logfaild = $logid = '';
        $res = $this->model->where(array('status'=>1))->select();
        $or = $new = array();
        foreach($res as $val){
            $or[$val['id']] = $val['order'];
        }
        foreach($_POST['order'] as $k => $v){
            $id = intval($k);
            $order = intval($v);
            $new[$id] = $order;
            $or[$id] = $order;
            if($order > 100 || $order < 1)$this->error("排序值需要在1-100之间");
        }
        //去重后数量减少说明有重复值
        $oldcount = count($or);
        $or = array_unique($or);
        $newcount = count($or);
        if($oldcount > $newcount)$this->error("存在相同排序值");
        else{
            foreach($new as $k => $v){
                $res = $this->model->where(array('id'=>$k))->save(array('order'=>$v));
                if($res){
                    $logid = $k.',';
                    $logsuccess .= $id.'=>'.$order.', ';
                }
            }
        }
        if($res !== false){
            if($logsuccess)$this -> writelog("灵活运营样式-搞机页面管理 成功修改排序 ".$logsuccess,'zy_care_gaoji', $logid,__ACTION__ ,"","edit");
            $this -> assign('jumpUrl',"/index.php/Zhiyoo/CareGaoji/index");
            $this->success("修改成功！");
        }else{
            $this->error("修改失败！");
        }
        
	}
    
	public function del(){
		$res = $this->model->where(array('id'=>$_GET['id']))->save(array('status'=>-1));
        if($res){
            $this -> writelog("灵活运营样式-搞机页面管理 已删除id为{$_GET['id']}的内容",'zy_care_gaoji', $_GET['id'],__ACTION__ ,"","del");
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
	}
    
	protected function _upload($file,$savepath,$config){
		include dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}
}