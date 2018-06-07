<?php 

Class ZhiyooConfigAction extends CommonAction{
	public $no_type = array('1.0','1.1','2.0','2.1');
	
	public function update_type(){
		$model = D('Zhiyoo.UpdateType');
		$result = $model->select();
		$this -> assign('result',$result);
		$this -> assign('no_type',$this->no_type);
		$this -> display();
	}
	
	public function do_update_type(){
		$model = D('Zhiyoo.UpdateType');
		
		foreach($_POST['switch'] as $key => $val){
			$model->where('id='.$key)->save(array('switch'=>$val));
			$this -> writelog("智友-智友功能配置-升级类型配置 已更改id{$key}升级状态{$val}","zy_update_type",$key,__ACTION__ ,"","edit");
		}
		
		foreach($_POST['type'] as $key => $val){
			$model->where('id='.$key)->save(array('type'=>$val));
			$this -> writelog("智友-智友功能配置-升级类型配置 已更改id{$key}升级方式{$val}","zy_update_type",$key,__ACTION__ ,"","edit");
		}
		
		$this->success("更改成功！");
	}
	
}