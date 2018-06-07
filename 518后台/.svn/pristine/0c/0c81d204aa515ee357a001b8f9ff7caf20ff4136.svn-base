<?php 

Class PictureAction extends CommonAction{
	private $model_p;
	private $model_pt;
	private $table_p = 'zy_picture';
	private $table_pt = 'zy_picture_type';
	private $img_host_out = array(
            'http://img1.anzhi.com',
            'http://img2.anzhi.com',
            'http://img3.anzhi.com',
            'http://img4.anzhi.com',
            'http://img5.anzhi.com'
        );
	
	public function _initialize() {
        parent::_initialize();
        $this->model_p = D('Zhiyoo.Picture');
        $this->model_pt = D('Zhiyoo.PictureType');
    }
	
	function index(){
		$where_sql = array('status'=>1);
        $order_sql = 'addtime desc';
        
        $start = strtotime($_GET['start_tm']);
        $end = strtotime($_GET['end_tm']);
        $typeid = $_GET['typeid'];
        $watermark = $_GET['watermark'];
        if($start) {
            $where_sql['addtime'][] = array('egt',$start);
        }
        if($end) {
            $where_sql['addtime'][] = array('elt',$end);
        }
        if(isset($_GET['typeid'])) {
            $where_sql['typeid'] = $typeid;
        }
        if(isset($_GET['watermark'])) {
            $where_sql['watermark'] = $watermark;
        }
        if(isset($_GET['istypeid'])) {//已分类
            if($_GET['istypeid'] == 0){
                $where_sql['typeid'] = 0;
            }elseif($_GET['istypeid'] == 1){
                $where_sql['typeid'] = array('neq',0);
            }
        }
            
		import("@.ORG.Page");
		$param = http_build_query($_GET);
        //没有标题的查询不涉及采集池
        
        $count = $this->model_p-> where($where_sql)->count();
        
        $lr = $_GET['lr'] ? $_GET['lr'] : 20;
        $Page = new Page($count,$lr , $param);
        
        $result = $this->model_p->where($where_sql)->order($order_sql)->limit("{$Page->firstRow},{$Page->listRows} ")->select();
			
		$show = $Page->show();
            
		$typeman = $this->model_pt->where("status=1")->select();
        $typelist = array();
        foreach($typeman as $val){
            $typelist[$val['typeid']] = $val['typename'];
        }
        $img_host_out_count = count($this->img_host_out);
        $rand = mt_rand(0,$img_host_out_count-1);
        $img_host_out = $this->img_host_out[$rand];
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('img_host_out',$img_host_out);
		$this -> assign('typelist',$typelist);
		$this -> assign('result',$result);
        $this -> assign("page", $show);
        $this -> assign("imghost", IMGATT_HOST);
		$this -> display();
	}
	
	function typeman(){
		$result = $this->model_pt->where('status=1')->select();
		$this -> assign('result',$result);
		$this->display();
	}
    
	function addtype(){
		$this->display();
	}
	
	function doaddtype(){
        $result = $this->model_pt->where(array('typename'=>$_POST['typename'],'status'=>1))->find();
        if($result)$this -> error("添加失败，名称已存在");
		$result = $this->model_pt->add(array('typename'=>$_POST['typename']));
        if($result !== false){
            $this -> writelog("智友内容管理-图床管理 已添加分类id为{$result}的分类".$_POST['typename'],"zy_picture_type",$result,__ACTION__ ,"","add");
            $this -> success("添加成功");
        }else{
            $this -> error("添加失败");
        }
	}
    
	function edittype(){
        $result = $this->model_pt->where(array('typeid'=>$_GET['typeid']))->find();
		$this -> assign('typeid',$_GET['typeid']);
		$this -> assign('result',$result);
		$this->display();
	}
	
	function doedittype(){
        $result = $this->model_pt->where(array('typeid'=>array('neq',$_GET['typeid']),'typename'=>$_POST['typename'],'status'=>1))->find();
        if($result)$this -> error("修改失败，名称已存在");
		$result = $this->model_pt->where(array('typeid'=>$_GET['typeid']))->save(array('typename'=>$_POST['typename']));
        if($result !== false){
            $this -> writelog("智友内容管理-图床管理 已修改分类id为{$_GET['typeid']}的分类为".$_POST['typename'],"zy_picture_type",$_GET['typeid'],__ACTION__ ,"","edit");
            $this -> success("修改成功");
        }else{
            $this -> error("修改失败");
        }
	}
    
	function editext(){
        $result = $this->model_p->where(array('picid'=>$_GET['picid']))->find();
		$this -> assign('picid',$_GET['picid']);
		$this -> assign('result',$result);
		$this->display();
	}
	
	function doeditext(){
		$result = $this->model_p->where(array('picid'=>$_GET['picid']))->save(array('ext'=>$_POST['ext']));
        if($result !== false){
            $this -> writelog("智友内容管理-图床管理 已修改picid为{$_GET['picid']}的图片备注为".$_POST['ext'],"zy_picture",$_GET['picid'],__ACTION__ ,"","edit");
            $this -> success("修改成功");
        }else{
            $this -> error("修改失败");
        }
	}
    
	function edittypebyid(){
        $pic = $this->model_p->where(array('picid'=>$_GET['picid']))->find();
        $result = $this->model_pt->where("status=1")->select();
		$this -> assign('picid',$_GET['picid']);
		$this -> assign('pic',$pic);
		$this -> assign('typeman',$result);
		$this->display();
	}
	
	function doedittypebyid(){
		$result = $this->model_p->where(array('picid'=>$_GET['picid']))->save(array('typeid'=>$_POST['typeid']));
        if($result !== false){
            $this -> writelog("智友内容管理-图床管理 已修改picid为{$_GET['picid']}的图片分类为".$_POST['typeid'],"zy_picture_type",$_GET['picid'],__ACTION__ ,"","edit");
            $this -> success("修改成功");
        }else{
            $this -> error("修改失败");
        }
	}
	
	function deltype(){
		$result = $this->model_pt->where(array('typeid'=>$_GET['typeid']))->save(array('status'=>-1));
		$result = $this->model_p->where(array('typeid'=>$_GET['typeid']))->save(array('typeid'=>0));
        if($result !== false){
            $this -> writelog("智友内容管理-图床管理 已删除typeid为{$_GET['typeid']}的分类","zy_picture_type",$_GET['typeid'],__ACTION__ ,"","del");
            $this -> success("删除成功");
        }else{
            $this -> error("删除失败");
        }
	}
	
	function delpic(){
		$result = $this->model_p->where(array('picid'=>$_GET['picid']))->save(array('status'=>-1));
        if($result !== false){
            $this -> writelog("智友内容管理-图床管理 已删除picid为{$_GET['picid']}的图片","zy_picture",$_GET['picid'],__ACTION__ ,"","del");
            $this -> success("删除成功");
        }else{
            $this -> error("删除失败");
        }
	}
    
	function upload(){
        $typelist = $this->model_pt->where("status=1")->select();
		$this -> assign('typelist',$typelist);
		$this->display();
	}
    
	function doaddpic(){
		$data = array(
            'typeid'=>$_POST['typeid'],
            'watermark'=>$_POST['watermark'],
            'ext'=>$_POST['ext'],
            'addtime'=>time(),
            'status'=>1
        );
        $picid = '';
        foreach($_POST['path'] as $val){
            $data['path'] = $val;
            $result = $this->model_p->add($data);
            $picid .= $result.',';
        }
        if($result !== false){
            $this -> writelog("智友内容管理-图床管理 已添加picid为{$picid}的分类","zy_picture",$picid,__ACTION__ ,"","add");
            $this->assign("jumpUrl", "/index.php/Zhiyoo/Picture/index");
            $this -> success("添加成功");
        }else{
            $this -> error("添加失败");
        }
	}
    
	function uploadpic(){
        if($_FILES['file']['error']){
            $json = json_encode(array('ok'=>0,'msg'=>'fileerror'.$_FILES['file']['error']));
            exit($json);
        }
        $file_ext = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
        if(!in_array($file_ext,array('jpg','jpeg','png'))){
            $json = json_encode(array('ok'=>0,'msg'=>'不支持的格式'));
            exit($json);
        }
        $filename = time().rand_string(16).'.'.$file_ext;
        $path = UPLOAD_PATH.'/picture/';
        if (!is_dir($path)) mkdir($path, 0777, true);
        $path .= $filename;
        $result = move_uploaded_file($_FILES['file']['tmp_name'],$path);
        if(!$result){
            $json = json_encode(array('ok'=>0,'msg'=>'非法操作'));
            exit($json);
        }
        if($_GET['watermark']){
            $src_path = APP_PATH.'/Public/images/water.png';
            watermark($path,$src_path,'br');
        }
		$json = json_encode(array('ok'=>1,'path'=>'/picture/'.$filename));
        exit($json);
	}
}