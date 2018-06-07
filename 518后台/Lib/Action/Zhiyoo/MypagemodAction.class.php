<?php 

Class MypagemodAction extends CommonAction{
	private $model_mypage;
	private $table_active = 'zy_active_conf';
	
	public function _initialize() {
        parent::_initialize();
        $this->model_mypage = D('Zhiyoo.MyPageMod');
    }
	
	public function index(){
		$order = $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';
		$result = $this->model_mypage ->order('rank '.$order)-> select();
		
		foreach($result as $key => $val){
			//我的页面活动运营位状态判断 modid=3 active type=1
			if($val['modid'] == '3'){
				$res = $this->model_mypage->table($this->table_active)->where('type=1 and status>0')->limit(1)->getField('id');
				if(empty($res)){//运营位无内容
					if($val['status'] <2){//状态由停用 变成 停用且不可更改 状态
						$this->model_mypage ->where('modid=3')->save(array('status'=>2));
						$result[$key]['status'] = 2;
					}
				}elseif($val['status'] >=2){//运营位有内容时,状态由不可更改 变成 停用 状态
					$this->model_mypage ->where('modid=3')->save(array('status'=>0));
					$result[$key]['status'] = 0;
				}
			}
			
			//我的页面推荐运营位状态判断 modid=4 active type=2
			if($val['modid'] == '4'){
				$res = $this->model_mypage->table($this->table_active)->where('type=2 and status>0')->limit(1)->getField('id');
				if(empty($res)){//运营位无内容
					if($val['status'] <2){//状态由停用 变成 停用且不可更改 状态
						$this->model_mypage ->where('modid=4')->save(array('status'=>2));
						$result[$key]['status'] = 2;
					}
				}elseif($val['status'] >=2){//运营位有内容时,状态由不可更改 变成 停用 状态
					$this->model_mypage ->where('modid=4')->save(array('status'=>0));
					$result[$key]['status'] = 0;
				}
			}
			
		}
		$order = $_GET['order'] == 'DESC' ? 'ASC' : 'DESC';
		$this -> assign('order',$order);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function edit_rank(){
		foreach($_POST['id'] as $key => $val){
			$val = intval(trim($val));
			if($val > 2){//非固定优先级必须大于2
				$this->model_mypage ->where('modid='.$key)->save(array('rank'=>$val));
				$this -> writelog("智友-我的页面-模块配置 已更改modid{$key}优先级{$val}",'zy_active_conf', $key,__ACTION__ ,'','edit');
			}else{
				$this->error("优先级必须大于2");
			}
		}
		$this->assign("jumpUrl", "/index.php/Zhiyoo/Mypagemod/index");
		$this->success("更改优先级成功！");
	}
	
	public function status(){
		$result = $this->model_mypage ->where('modid='.$_GET['modid'])->save(array('status'=>$_GET['status']));
		if($result !== false){
			$this -> writelog("智友-我的页面-模块配置 已更改modid{$_GET['modid']}状态为".$_GET['status'],'zy_active_conf', $_GET['modid'],__ACTION__ ,'','edit');
			$this->success("更改状态成功！");
		}else{
			$this->error("更改状态失败！");
		}
	}
}