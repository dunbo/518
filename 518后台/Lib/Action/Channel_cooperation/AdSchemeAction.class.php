<?php
header("Content-type: text/html; charset=utf-8"); 
class AdSchemeAction extends CommonAction{
	public function index(){
		$model = D('Channel_cooperation.channel_cooperation');
		$arr = $model -> table('co_scheme') -> where(array('status' => 1)) ->ORDER('create_tm desc')->select();
		$models = new Model();
		foreach($arr as $key=>$val){
			$arr[$key]['asd']=$model -> table('co_detail')->where(array('pid' => $val['id'],'status' => 1))->select();
			
		}
		foreach($arr as $key => $val){
			$price_result = $models -> table('sj_channel') -> where(array('price_type' => 1,'price' => $val['id'])) -> count();
			$arr[$key]['num'] = $price_result;
		}
		$this->assign('num',$price_result);
		$this->assign('data',$arr);
		$this->display();
	}
	//添加展示页面
	function add(){
		$this->display();
	}
	//添加操作
	function adddo(){
		$model = D('Channel_cooperation.channel_cooperation');
		if(empty($_POST['name'])){
				echo "<script>alert('方案名称不能为空！');history.go(-1);</script>";die;
		}
		$name = $_POST['name'];
		$result=$model -> table('co_scheme')->where(array('name' => $name,'status' => 1)) -> select();
		if($result != ""){
			echo "<script>alert('方案名称已存在');history.go(-1);</script>";
			return false;
		}
		$jihuoliang=$_POST['arr'];
		$jiage=$_POST['arr1'];
		$number=count($jihuoliang);
		$arr[]=$jihuoliang[0];
		foreach($jihuoliang as $key=>$val){
			if ($val <= $jihuoliang[$key-1]){
				echo "<script>alert('区间起始数值需比上一区间末尾值大1');history.go(-1);</script>";die;
			}
			if($key%2==0 && $key!=0){
				
				$arr[]=$jihuoliang[$key-1].'-'.$jihuoliang[$key];
			}
			if($key%2!=0){
				//echo $val;echo $jihuoliang[$key-1]-1;echo echo "<br/>";die;
				if($val != $jihuoliang[$key-1]+1){
					echo "<script>alert('区间起始数值需比上一区间末尾值大1');history.go(-1);</script>";
					die;
				}
			}
			
		}
		$pid = $model -> table('co_scheme')->add(array('name' => $_POST['name'],'create_tm' => time(), 'scheme_type'=> $_POST['scheme_type']));
		$model -> table('co_detail')->where("pid = " . $pid )->delete();
		$len = count($_POST['arr']);
				$array['star_activations'] = 0;
				$array['price'] = $jiage['sta'];
				$array['end_activations'] = $jihuoliang[0];
				$array['pid'] = $pid;
				$model -> table('co_detail')->add($array);

		for ($i=0;$i<(count($jiage)-2);$i++) {
				if (($i*2) == 0) {
					$k_sta = 1;
				} else {
					$k_sta = $i * 2 + 1;
				}
				if (($i*2) == 0) {
					$k_end = 2;
				} else {
					$k_end = $i * 2 + 2;
				}
				$array['star_activations'] = $jihuoliang[$k_sta];
				$array['price'] = $jiage[$i];
				$array['end_activations'] = $jihuoliang[$k_end];
				$array['pid'] = $pid;
			$model -> table('co_detail')->add($array);
		}
				$k = $len -1;
				$array['star_activations'] = $jihuoliang[$k];
				$array['price'] = $jiage['end'];
				$array['end_activations'] = 0;
				$array['pid'] = $pid;
		$isadd = $model -> table('co_detail')->add($array);
		if($isadd){
			$this->writelog("阶梯单价设置添加了方案id为{$isadd}", 'co_detail', $isadd,__ACTION__ ,'','add');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdScheme/index');
			$this->success("添加成功");
		}
	}
	//编辑展示
	function edit(){
		$model = D('Channel_cooperation.channel_cooperation');
		$id=$_GET['id'];
		//显示名称
		$result = $model -> table('co_scheme')->where(array('id' => $id))->select();
		$arr=$model -> table('co_detail')->where('pid in ('.$id . ')')->order('star_activations asc')->select();
		$len = count($arr);
		foreach ($arr as $key => $val) {
			if ($key == 0) {
				$array['sta'] = $val['end_activations'];
				$array['sta_danjia'] = $val['price'];
			}
			if ($key == ($len-1)){
				$array['end'] = $val['star_activations'];
				$array['end_danjia'] = $val['price'];
			}
			if ($key != 0 && $key != ($len-1))
				$array['asd'][] = $val;
		}
		$array['pid'] = $id;
		$this->assign('name',$result);
		$this->assign('data',$array);
		$this->display();

	}
	//编辑操作
	function doedit(){
		$model = D('Channel_cooperation.channel_cooperation');
		if(empty($_POST['name'])){
				echo "<script>alert('方案名称不能为空！');history.go(-1);</script>";die;
		}
		$name = $_POST['name'];
		$where = array();
        $where['name'] = array('EQ', $name);
		$where['status'] = array('EQ', 1);
		$where['id'] = array('NEQ', $_POST['pid']);
		$result=$model -> table('co_scheme')->where($where) -> select();
		if($result != ""){
			echo "<script>alert('方案名称已存在');history.go(-1);</script>";
			return false;
		}
		$jihuoliang=$_POST['arr'];
		$jiage=$_POST['arr1'];
		$number=count($jihuoliang);
		$arr[]=$jihuoliang[0];
		foreach($jihuoliang as $key=>$val){
			if ($val <= $jihuoliang[$key-1]){
				echo "<script>alert('区间起始数值需比上一区间末尾值大1');history.go(-1);</script>";die;
			}
			if($key%2==0 && $key!=0){
				
				$arr[]=$jihuoliang[$key-1].'-'.$jihuoliang[$key];
			}
			if($key%2!=0){
				//echo $val;echo $jihuoliang[$key-1]-1;echo echo "<br/>";die;
				if($val != $jihuoliang[$key-1]+1){
					echo "<script>alert('区间起始数值需比上一区间末尾值大1');history.go(-1);</script>";
					die;
				}
			}
		}
		$pid=$_POST['pid'];
		$model -> table('co_detail')->where("pid = " . $pid )->delete();
		$len = count($_POST['arr']);

				$array['star_activations'] = 0;
				$array['price'] = $jiage['sta'];
				$array['end_activations'] = $jihuoliang[0];
				$array['pid'] = $pid;
				$model -> table('co_detail')->add($array);

		for ($i=0;$i<(count($jiage)-2);$i++) {
			if (($i*2) == 0) {
					$k_sta = 1;
				} else {
					$k_sta = $i * 2 + 1;
				}
				if (($i*2) == 0) {
					$k_end = 2;
				} else {
					$k_end = $i * 2 + 2;
				}
				$array['star_activations'] = $jihuoliang[$k_sta];
				$array['price'] = $jiage[$i];
				$array['end_activations'] = $jihuoliang[$k_end];
				$array['pid'] = $pid;
			$add = $model -> table('co_detail')->add($array);
		}
				$k = $len -1;
				$array['star_activations'] = $jihuoliang[$k];
				$array['price'] = $jiage['end'];
				$array['end_activations'] = 0;
				$array['pid'] = $pid;
			$adds = $model -> table('co_detail')->add($array);
			$log_result = $this -> logcheck(array('id' => $pid),'co_scheme',array('name' => $_POST['name']),$model);
		$isadd = $model -> table('co_scheme')->where('id='.$pid)->save(array('name' => $_POST['name'],'scheme_type'=>$_POST['scheme_type']));
		if($isadd || $add ||$adds){
			$this -> writelog("阶梯单价设置已编辑id为{$pid}的方案".$log_result, 'co_scheme', $pid,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdScheme/index');
			$this->success("编辑成功");
		}else{
			$this->error("您未做任何修改");
		}
	}
	//删除操作
	function del(){
		$model = D('Channel_cooperation.channel_cooperation');
		$id=$_GET['id'];
		$where = array();
        $where['id'] = array('EQ', $id);
		$data = array();
        $data['status'] = 0;
		$arr=$model -> table('co_scheme')->where($where)->save($data);
		if($arr){
			$this->writelog("删除了id为{$id}的方案", 'co_scheme', $id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl','/index.php/Channel_cooperation/AdScheme/index');
			$this->success("删除成功");
		}else{
			$this->error("删除失败");
		}
	}
}