<?php 

Class ProductTypeAction extends CommonAction{
	private $model;
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.ProductType');
    }
	
	function index(){
        //检查新专区
        $result = $this->model->order('status desc,rank '.$_GET['o'])->select();
        $sp = array();
        foreach($result as $val){
            if($val['gid']){
                $sp[] = $val['gid'];
            }else{
                $sp[] = $val['special'];
            }
        }
        if(!empty($sp)) {
			$sp_str = implode(',',$sp);
			$extsql = " and fid not in ({$sp_str})";
		}else{
            $extsql = '';
        }
        
		$model = D('Zhiyoo.bbs');
        $result1 = $model->table('x15_forum_forum')->where("fup=0 and status=1 {$extsql}")->select();
        if(!empty($result1)){
            foreach($result1 as $val){
                $add = array('gid'=>$val['fid'],'name'=>$val['name']);
                $id = $this->model->add($add);
            }
            $result = $this->model->order('status desc,rank '.$_GET['o'])->select();
        }
        
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function status(){
        if($_GET['status'] == '1'){//正常变停用
            $result = $this->model ->where('id='.$_GET['id'])->save(array('status'=>0));
        }else{
            $result = $this->model ->where('id='.$_GET['id'])->save(array('status'=>1));
        }
		if($result !== false){
			$this -> writelog("智友-产品分类管理 已更改状态id{$_GET['id']}为".($_GET['status'] == '1' ? 0 : 1),"zy_product_type",$_GET['id'],__ACTION__ ,"","edit");
			$this->success("状态更改成功！");
		}else{
			$this->error("状态更改失败！");
		}
	}
	
	public function edit(){
        if($_GET['id']){
            $res = $this->model->where ('id='.$_GET['id'])->select();
			
            $this->assign('result',$res[0]);
            $this -> display();
        }
	}
	
	public function doedit(){
        if($_GET['action'] == 'rank'){

            foreach($_POST['rank'] as $key => $val){
                $this->model->where ('id='.$key)->save(array('rank'=>intval($val)));
                $this -> writelog("智友-热门标签管理 已编辑id：{$key}优先级{$val}","zy_product_type",$key,__ACTION__ ,"","edit");
            }
			
            $this->assign('jumpUrl',"/index.php/Zhiyoo/ProductType/index");
			$this->success("编辑成功！");
        }
        if($_GET['action'] == 'rename'){
            $this->model->where ('id='.$_GET['id'])->save(array('rename'=>$_POST['rename']));
            $this -> writelog("智友-热门标签管理 已编辑id：{$_GET['id']}优先级{$val}","zy_product_type",$_GET['id'],__ACTION__ ,"","edit");
			
            $this->assign('jumpUrl',"/index.php/Zhiyoo/ProductType/index");
			$this->success("编辑成功！");
        }
	}
    
}