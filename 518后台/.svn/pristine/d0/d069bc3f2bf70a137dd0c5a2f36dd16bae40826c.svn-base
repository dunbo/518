<?php
class CooperativeLogModel extends AdvModel{
    public function nodename2id($action_list)
	{
		//$model = new Model();
		$nodes = '';
		$i = 0;
		foreach ($action_list as $val){
			if ($i > 0) $nodes .= ',';
			$nodes .= $val;
			$i++;
		}
		$nodes = str_replace(',', "','", $nodes);

		$sql = "select node_id, nodename from sj_admin_node where nodename in ('{$nodes}')";

		$res = $this->query($sql);
		$node_map = array();
		foreach($res as $row) {
			$node_map[$row['nodename']] = $row['node_id'];
		}

		$action_list_new = $action_list;

		foreach ($action_list_new as $k => $v){
			$tmp = explode(',', $v);
			if (is_array($tmp)) {
				$i = 0;
				$str = '';
				foreach ($tmp as $val) {
					if ($i > 0) $str .= ',';				
					$str .= $node_map[$val];
					$i++;
				}
				$action_list_new[$k] = $str;
			} else {
				$action_list_new[$k] = $node_map[$v];
			}
		//var_dump($k);
		}
		return $action_list_new;
	}
}