<?php 

Class BrandAction extends CommonAction{
	private $table_b = 'zy_brand_library';
	private $table_bt = 'zy_brand_type_library';
	private $table_btm = 'zy_brand_type_match_library';
	private $model;
	private $model_b;
	private $model_bt;
	private $model_btm;
	private $first_alp = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.Zhiyoo');
        $this->model_b = D('Zhiyoo.Brand_library');
        $this->model_bt = D('Zhiyoo.Brand_type_library');
        $this->model_btm = D('Zhiyoo.Brand_type_match_library');
    }
	
	public function brand_list(){
		$ot = isset($_GET['ot']) ? $_GET['ot'] : '';
		$order = $_GET['order'] == 'asc' ? 'asc' : 'desc';
		$sql = $ot ? $ot.' '.$order : 'status desc,brand_name asc';
		if($_GET['st'] == 'fn' && isset($_GET['val']))$wheresql[] = $_GET['val'] == 'other' ? "first_alphabet not regexp '[A-Z]'" : "first_alphabet='".$_GET['val']."'" ;
		isset($_GET['brand_name']) && $wheresql[] = "brand_name like '%".$_GET['brand_name']."%'";
		$wheresql[] = "status != -1";
		$wheresql = implode(' and ',$wheresql);
		$model = $this->model;
		
		import("@.ORG.Page");
		$count = $model -> table($this->table_b)->where($wheresql) ->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$param = http_build_query($_GET);
		$Page = new Page($count,$prepage , $param);
		$show = $Page->show();
		$result = $model->table($this->table_b)->where($wheresql)->order($sql)->limit("{$Page->firstRow},{$Page->listRows}")->select();
		$corder[$ot] = $order == 'asc' ? 'desc' : 'asc';
		$this -> assign('order',$corder);
		$this -> assign('result',$result);
		$this -> assign('show',$show);
		$this -> display();
	}
	
	public function brand_type_list(){
		$brand_id = $_GET['brand_id'];
		$ot = isset($_GET['ot']) ? $_GET['ot'] : '';
		$order = $_GET['order'] == 'asc' ? 'asc' : 'desc';
		$sql = $ot ? $ot.' '.$order : 'status desc,brand_type asc';
		isset($_GET['brand_type']) && $wheresql[] = "brand_type like '%".$_GET['brand_type']."%'";
		$wheresql[] = "brand_id='$brand_id' and status != -1";
		$wheresql = implode(' and ',$wheresql);
		$model = $this->model;
		
		import("@.ORG.Page");
		$count = $model -> table($this->table_bt)->where($wheresql) ->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$param = http_build_query($_GET);
		$Page = new Page($count,$prepage , $param);
		$show = $Page->show();
		$result = $model->table($this->table_bt)->where($wheresql)->order($sql)->limit("{$Page->firstRow},{$Page->listRows}")->select();
		$corder[$ot] = $order == 'asc' ? 'desc' : 'asc';
		$name = $model->table($this->table_b)->where("id=".$brand_id)->limit(1)->select();
		$this -> assign('order',$corder);
		$this -> assign('result',$result);
		$this -> assign('name',$name[0]['brand_name']);
		$this -> assign('show',$show);
		$this -> display();
	}
	
	public function brand_type_match_list(){
		$ot = isset($_GET['ot']) ? $_GET['ot'] : '';
		$order = $_GET['order'] == 'asc' ? 'asc' : 'desc';
		$sql = $ot ? $ot.' '.$order : 'btm.status desc,btm.upload_brand asc';
		isset($_GET['brand']) && $wheresql[] = "btm.upload_brand like '%".$_GET['brand']."%'";
		isset($_GET['brand_type']) && $wheresql[] = "btm.upload_type like '%".$_GET['brand_type']."%'";
		isset($_GET['match']) && $wheresql[] = "btm.match_type_id ".($_GET['match']?'!=0':'=0');
		$wheresql[] = "btm.status != -1";
		$wheresql = implode(' and ',$wheresql);
		$model = $this->model;
		
		import("@.ORG.Page");
		$count = $model -> table($this->table_btm." btm")->where($wheresql) ->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$param = http_build_query($_GET);
		$Page = new Page($count,$prepage , $param);
		$show = $Page->show();
		$result = $model->query("select btm.id,btm.upload_brand,btm.upload_type,btm.status,b.brand_name,bt.brand_type from ".$this->table_btm." btm left join ".$this->table_bt." bt on btm.match_type_id=bt.id left join ".$this->table_b." b on bt.brand_id=b.id where $wheresql order by $sql limit {$Page->firstRow},{$Page->listRows}");
		$corder[$ot] = $order == 'asc' ? 'desc' : 'asc';
		$this -> assign('order',$corder);
		$this -> assign('result',$result);
		$this -> assign('show',$show);
		$this -> display();
	}
	
	public function hot_brand(){
		$ot = isset($_GET['ot']) ? $_GET['ot'] : '';
		$order = $_GET['order'] == 'asc' ? 'asc' : 'desc';
		$sql = $ot ? $ot.' '.$order : 'rank asc';
		$result1 = $this->model_b->where("is_hot=1 and rank!=0 and status=1")->order($sql)->select();$result1 = $result1 ? $result1 : array();
		$result2 = $this->model_b->where("is_hot=1 and rank=0 and status=1")->order($sql)->select();$result2 = $result2 ? $result2 : array();
		$result = array_merge($result1,$result2);//未编辑优先级置底
		$corder[$ot] = $order == 'asc' ? 'desc' : 'asc';
		$this -> assign('order',$corder);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function add(){
		if($_GET['action']=='brand')
			$this -> display('add_brand');
		if($_GET['action']=='brand_type')
			$this -> display('add_brand_type');
	}
	
	public function doadd(){
		$model = $this->model;
		if($_GET['action']=='brand'){
			$data['brand_name'] = addslashes(trim($_POST['brand_name']));
			$result = $this->model_b->table($this->table_b)->where("brand_name='{$data['brand_name']}' and status != -1")->select();
			if($result)$this->error("添加品牌已存在！");
			$first_alphabet = trim($_POST['first_alphabet']);
			$first_alphabet = in_array(strtoupper($first_alphabet),$this->first_alp) ? strtoupper($first_alphabet) : $first_alphabet;//处理A-Z首字母大写
			$data['first_alphabet'] = addslashes($first_alphabet);
			$data['cbrand_name'] = addslashes(trim($_POST['cbrand_name']));
			$data['ebrand_name'] = addslashes(trim($_POST['ebrand_name']));
			$result = $this->model_b->table($this->table_b)->add($data);
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已添加品牌ID为{$result}", $this->table_b, $result,__ACTION__ ,'','add');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_list");
				$this->success("添加成功！");
			}
		}
		if($_GET['action']=='brand_type'){
			$data['brand_id'] = $_GET['brand_id'];
			$data['brand_type'] = addslashes(trim($_POST['brand_type']));
			$result = $this->model_bt->table($this->table_bt)->where("brand_type='{$data['brand_type']}' and status != -1")->select();
			if($result)$this->error("添加机型已存在！");
			$data['official_type'] = addslashes(trim($_POST['official_type']));
			$data['represent_type'] = addslashes(trim($_POST['represent_type']));
			$result = $this->model_bt->table($this->table_bt)->add($data);
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已添加品牌ID{$data['brand_id']}机型ID".$result, $this->table_bt, $data['brand_id'],__ACTION__ ,'','add');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_list/brand_id/".$data['brand_id']);
				$this->success("添加成功！");
			}
		}
		$this->error("添加失败！");
	}
	
	public function edit(){
		$model = $this->model;
		if($_GET['action']=='brand'){
			$id = $_GET['brand_id'];
			$result = $this->model_b->where(array('id'=>$id))->find();
			$this->assign("result", $result);
			$this -> display('edit_brand');
		}
		if($_GET['action']=='brand_type'){
			$id = $_GET['brand_type_id'];
			$result = $this->model_bt->where(array('id'=>$id))->find();
			$this->assign("result", $result);
			$this -> display('edit_brand_type');
		}
		if($_GET['action']=='hot_brand'){
			foreach($this->first_alp as $val){
				$result[$val] = $this->model_b->where(array('status'=>1,'first_alphabet'=>$val))->order('brand_name asc')->select();
			}
			$result['other'] = $this->model_b->where(array('status'=>1,'first_alphabet'=>array('exp',"not regexp '[A-Z]'")))->order('brand_name asc')->select();
			$hot = $this->model_b->where(array('status'=>1,'is_hot'=>1))->order('brand_name asc')->field('brand_name')->select();
			$this->assign("result", $result);
			$this->assign("hot", $hot);
			$this->assign("fa", $this->first_alp);
			$this -> display('edit_hot_brand');
		}
		if($_GET['action']=='brand_type_match'){
			$id = $_GET['id'];
			foreach($this->first_alp as $val){
                $result[$val] = $this->model_b->where(array('status'=>1,'first_alphabet'=>$val))->order('brand_name asc')->select();
			}
			$result['other'] = $this->model_b->where(array('status'=>1,'first_alphabet'=>array('exp',"not regexp '[A-Z]'")))->order('brand_name asc')->select();
			$data = $this->model_btm->where(array('id'=>array('in',$id)))->select();
			if(is_numeric($id)){//批量匹配默认不选中
				$fff = $data[0]['match_type_id'];
				$match = $this->model_bt->where(array('id'=>$fff))->field('brand_id')->find();
			}
			$this->assign("result", $result);
			$this->assign("match", $match['brand_id']);
			$this->assign("data", $data);
			$this->assign("fa", $this->first_alp);
			$this -> display('edit_brand_type_match');
		}
		if($_GET['action']=='edit_match_brand_type'){
			$id = $_GET['id'];
			if(is_numeric($_GET['btmid']))//批量匹配默认不选中
				$fff = $this->model_btm->where(array('id'=>$_GET['btmid']))->field('match_type_id')->select();
			if($fff){
				$match_type_id = $fff[0]['match_type_id'];
			}
			$result = $this->model_bt->where(array('status'=>1,'brand_id'=>$id))->order('brand_type asc')->select();
			$brand_name = $this->model_b->where(array('id'=>$id))->field('brand_name')->find();
			$this->assign("result", $result);
			$this->assign("match_type_id", $match_type_id);
			$this->assign("brand_name", $brand_name['brand_name']);
			$this -> display('edit_match_brand_type');
		}
	}
	
	public function doedit(){
		$model = $this->model;
		$model_btm = $this->model_btm;
		$action = $_GET['action'];
		if($action == 'brand'){
			$id = $_GET['id'];
			$first_alphabet = trim($_POST['first_alphabet']);
			$first_alphabet = in_array(strtoupper($first_alphabet),$this->first_alp) ? strtoupper($first_alphabet) : $first_alphabet;//处理A-Z首字母大写
			$data['first_alphabet'] = addslashes($first_alphabet);
			$data['cbrand_name'] = addslashes(trim($_POST['cbrand_name']));
			$data['ebrand_name'] = addslashes(trim($_POST['ebrand_name']));
			$result = $this->model_b->table($this->table_b)->where("id='$id'")->save($data);
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已修改品牌ID{$id}，首字母【{$first_alphabet}】，中文名【{$data['cbrand_name']}】，英文名【{$data['ebrand_name']}】", $this->table_b, $id,__ACTION__ ,'','edit');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_list");
				$this->success("修改成功！");
			}
		}elseif($action == 'brand_type'){
			$id = $_GET['id'];
			$brand_id=$_GET['brand_id'];
			$data['official_type'] = addslashes(trim($_POST['official_type']));
			$data['represent_type'] = addslashes(trim($_POST['represent_type']));
			$result = $this->model_bt->table($this->table_bt)->where("id='$id'")->save($data);
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已修改机型ID{$id}，官方名称【{$data['official_type']}】，展示名称【{$data['represent_type']}】", $this->table_bt, $id,__ACTION__ ,'','edit');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_list/brand_id/".$brand_id);
				$this->success("修改成功！");
			}
		}elseif($action == 'brand_type_rank'){
			$brand_id=$_GET['brand_id'];
			foreach($_POST['rank'] as $key => $val){
				$result = $this->model_bt->table($this->table_bt)->where("id='$key'")->save(array('rank'=>$val));
				if($result !== false)$this -> writelog("智友内容管理-机型库-标准机型库 已编辑品牌优先级ID{$key}为{$val}", $this->table_bt, $key,__ACTION__ ,'','edit');
			}
			
			if($result !== false){
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_list/brand_id/".$brand_id);
				$this->success("修改成功！");
			}
		}elseif($action=='hot_brand'){
			$id = implode(',',$_POST['id']);
			$result = $this->model_b->table($this->table_b)->where("id in ($id)")->save(array('is_hot'=>1));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已添加热门品牌ID{$result}");
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/hot_brand");
				$this->success("添加成功！");
			}
		}elseif($action=='hot_brand_rank'){
			foreach($_POST['rank'] as $key => $val){
				$result = $this->model_b->table($this->table_b)->where("id='$key'")->save(array('rank'=>$val));
				if($result !== false)$this -> writelog("智友内容管理-机型库-标准机型库 已编辑热门品牌优先级ID{$key}为{$val}", $this->table_b, $key,__ACTION__ ,'','edit');
			}
			if($result !== false){
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/hot_brand");
				$this->success("编辑成功！");
			}
		}elseif($action=='brand_type_match'){
			$id = $_GET['id'];
			$brand_type = $_POST['brand_type'];
			$result = $model_btm->table($this->table_btm)->where("id in ($id)")->save(array('match_type_id'=>$brand_type));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已编辑品牌机型ID{$id}匹配{$brand_type}", $this->table_btm, $id,__ACTION__ ,'','edit');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_match_list");
				$this->success("编辑成功！");
			}
		}
		$this->error("修改失败！");
	}
	
	public function del(){
		$model = $this->model;
		if($_GET['action']=='brand'){
			$id = $_GET['brand_id'];
            $result = $model->table($this->table_b)->where(array('id'=>$id))->save(array('status'=>-1));
            $result = $this->model_bt->where(array('brand_id'=>$id))->save(array('brand_id'=>1));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已删除品牌ID{$id}", $this->table_b, $id,__ACTION__ ,'','del');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_list");
				$this->success("删除成功！");
			}
		}
		if($_GET['action']=='brand_type'){
			$id = $_GET['brand_type_id'];
			$brand_id=$_GET['brand_id'];
			$result = $model->table($this->table_bt)->where(array('id'=>$id))->save(array('status'=>-1));
            $result = $model->table($this->table_btm)->where(array('match_type_id'=>$id))->save(array('match_type_id'=>0));//重置匹配机型
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已删除机型ID{$id}", $this->table_bt, $id,__ACTION__ ,'','del');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_list/brand_id/".$brand_id);
				$this->success("删除成功！");
			}
		}
		if($_GET['action']=='hot_brand'){
			$id = $_GET['brand_id'];
            $result = $model->table($this->table_b)->where(array('id'=>$id))->save(array('is_hot'=>0));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已删除热门品牌ID{$id}", $this->table_b, $id,__ACTION__ ,'','del');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/hot_brand");
				$this->success("删除成功！");
			}
		}
		if($_GET['action']=='brand_type_match'){
			$id = $_GET['id'];
            $result = $model->table($this->table_btm)->where(array('id'=>array('in'=>$id)))->save(array('status'=>-1));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已删除抓取品牌机型ID{$id}", $this->table_btm, $id,__ACTION__ ,'','del');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_match_list");
				$this->success("删除成功！");
			}
		}
		$this->error("删除失败！");
	}
	
	public function status(){
		$model = $this->model;
		if($_GET['action']=='brand'){
			$id = $_GET['brand_id'];
			$status = $_GET['status'];
            $result = $model->table($this->table_b)->where(array('id'=>$id))->save(array('status'=>$status));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已修改品牌ID{$id}的状态为{$status}", $this->table_b, $id,__ACTION__ ,'','edit');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_list");
				$this->success("修改成功！");
			}
		}
		if($_GET['action']=='brand_type'){
			$id = $_GET['brand_type_id'];
			$brand_id=$_GET['brand_id'];
			$status = $_GET['status'];
            $result = $model->table($this->table_bt)->where(array('id'=>$id))->save(array('status'=>$status));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已修改机型ID{$id}的状态为{$status}", $this->table_bt, $id,__ACTION__ ,'','edit');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_list/brand_id/".$brand_id);
				$this->success("修改成功！");
			}
		}
		if($_GET['action']=='brand_type_match'){
			$id = $_GET['id'];
			$status = $_GET['status'];
			$result = $model->table($this->table_btm)->where(array('id'=>$id))->save(array('status'=>$status));
			if($result !== false){
				$this -> writelog("智友内容管理-机型库-标准机型库 已修改抓取品牌机型ID{$id}的状态为{$status}", $this->table_btm, $id,__ACTION__ ,'','edit');
				$this->assign("jumpUrl", "/index.php/Zhiyoo/Brand/brand_type_match_list");
				$this->success("修改成功！");
			}
		}
		$this->error("修改失败！");
	}
}