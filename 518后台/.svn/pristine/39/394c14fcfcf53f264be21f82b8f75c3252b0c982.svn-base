<?php
class ValidationAction extends CommonAction {
	
//节点权限与渠道权限控制
	function permission(){

		$this -> display();
	}
	public function permissionControll(){
		$user_model = M('admin_user');
		$node_model = M('admin_node');
		$channel_model = M('channel');
		
		$admin_id = $_REQUEST['admin_id'];
		$admin_where = "admin_user_id = ".$admin_id;
		$admin_affect = $user_model -> where($where) -> select();
		if(!$admin_affect){
			$this -> error("对不起，此用户不存在");
		}
		$node = $_REQUEST['node'];
		$node_go = explode(',',$node);
		$node_arr = $node_model -> field('node_name') -> select();
		foreach($node as $key => $val){
			if(!in_array($val,$node_arr)){
				$this -> error("对不起，节点".$val."不存在");
			}
		}
		foreach($node as $key => $val){
			$where_go = "nodename =".$val;
			$node_id = $node_model -> where($where_go) -> field('node_id') -> select();
		}
		
		$channel = $_REQUEST['channel'];
		$channel_go = explode(',',$node);
		$channel_where = "status = 1";
		$channel_arr = $channel_model -> where($where) -> field('cid') -> select();
		foreach($channel as $key => $val){
			if(!in_array($val,$channel_arr)){
				$this -> error("对不起，渠道id为".$val."的渠道不存在");
			}
		}
	
		$node_contrl = M('admin_role');
		$channel_contrl = M('admin_filter');
		
		$node_map = " type = 1 and admin_id = ".$admin_id;
		$admin_node = $node_contrl -> where($node_map) -> field('node_id') -> select();
		foreach($node_id as $key => $val){
			if(!in_array($val,$admin_node)){
				$this -> error("对不起，此用户没有访问节点id为".$val."的节点权限");
			}
		}
		
		$channel_map = "source_value = ".$admin_id;
		$admin_channel = $channel_contrl -> where($channel_map) -> field('target_value') -> select();
		foreach($channel_arr as $key => $val){
			if(!in_array($val,$admin_channel)){
				$this -> error("对不起，此用户没有访问渠道id为".$val."的渠道权限");
			}
		}
	}

}

?>