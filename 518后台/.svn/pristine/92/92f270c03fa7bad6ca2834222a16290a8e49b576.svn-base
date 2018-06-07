<?php 
Class SitemapAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.Sitemap');
	}
	
	public function linklist(){
		$order = "status desc,rank asc";
		$_GET['ordertype'] == 'rank' && $order = 'rank '.$_GET['order'];
		
		$linkdata = $this->model->where(array('status'=>array('exp','>=0'),'parentid'=>0))->order($order)->select();
		
		$this -> assign('order',$_GET['order']=='ASC' ? 'DESC' : 'ASC');
		$this -> assign('linkdata',$linkdata);
		$this -> display();
	}
	
	public function linklistsec(){
		$parentid = intval($_GET['id']) ? intval($_GET['id']) : 1;
		$order = "status desc,rank asc";
		$_GET['ordertype'] == 'rank' && $order = 'rank '.$_GET['order'];
		
		$par = $this->model->where(array('id'=>$parentid))->find();
		$linkdata = $this->model->where(array('status'=>array('exp','>=0'),'parentid'=>$parentid))->order($order)->select();
		
		$this -> assign('order',$_GET['order']=='ASC' ? 'DESC' : 'ASC');
		$this -> assign('parent',$par);
		$this -> assign('linkdata',$linkdata);
		$this -> assign('parentid',$parentid);
		$this -> display();
	}
	
	public function add_link(){
		$parentid = intval($_GET['id']);
		if($_POST){
			$name = trim($_POST['name']);
			$link = trim($_POST['link']);
			$rank = intval($_POST['rank']);
			if (empty($name) ||empty($link)) {
				$this->error("参数不能为空！");
			}
			$data = array('name'=>$name,'link'=>$link,'rank'=>$rank,'parentid'=>$parentid,'modifytime'=>time());
			$result = $this->model->add($data);
			$this -> writelog('添加网站地图链接id为'.$result,"zy_sitemap",$result,__ACTION__ ,"","add");
			$this->success("添加成功！");
		}
		$this -> assign('parentid',$parentid);
		$this -> display();
	}
	
	public function edit_link(){
		$id = intval($_GET['id']);
		if ($id<1) {
			$this->error("参数有误！");
		}
		if ($_POST && isset($_POST['name'])) {
			$name = trim($_POST['name']);
			$link = trim($_POST['link']);
			$rank = intval($_POST['rank']);
			if (empty($name) ||empty($link)) {
				$this->error("参数不能为空！");
			}
			$times = time();
			$result = $this->model->where(array('id'=>$id)) -> save(array('name'=>$name,'link'=>$link,'rank'=>$rank,'modifytime'=>time()));
			$this -> writelog('编辑网站地图链接展示名称:'.$name.' | 跳转链接:'.$link.'  | rank:'.$rank.' | id为'.$id,"zy_sitemap",$id,__ACTION__ ,"","edit");
			$this->success("编辑成功！");
		}
	
		$linkdata = $this->model->where(array('status'=>array('exp','>=0'),'id'=>$id))->find();
		$this -> assign('link',$linkdata);
		$this -> assign('id',$id);
		$this -> display();
	}
	
	public function edit_rank(){
		if (isset($_POST['level'])){
			$idstr = '';
			foreach ($_POST['level'] as $k=>$v){
				$v = abs(intval($v));
				$k = abs(intval($k));
				$p_ret = $this->model->where(array('id'=>$k)) -> save(array('rank'=>$v));
				$idstr = $k.',';
			}
			$jsonarr = '网站地图链接 优先级 id:rank'.json_encode($_POST['level']);
			$this -> writelog($jsonarr,"zy_sitemap",$idstr,__ACTION__ ,"","edit");
			$this->success("编辑优先级成功！");
		}
	}
	public function updateStatus(){
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		if ($id<1) {
			$this -> writelog('更改网站地图链接 状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$ret = $this->model->where(array('id'=>$id)) -> save(array('status'=>$status,'modifytime'=>time()));
		if($ret!==false){
			if($status==-1){
				$type = 'del';
			}else{
				$type = 'edit';
			}
			$this -> writelog('更改网站地图链接配置id为'.$id.' status '.$status,"zy_sitemap",$id,__ACTION__ ,"",$type);
			$this->success("更改成功！");
		}else{
			$this -> writelog('更改网站地图链接状态失败id为'.$id,"zy_sitemap",$id,__ACTION__ ,"","edit");
			$this->error("更改状态失败！");
		}
	}
	
}