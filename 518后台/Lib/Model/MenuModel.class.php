<?php
class MenuModel extends Model {
	/*
	protected $connect_id = 15;
	public function __construct(){
		$co_Connect = array(
			'dbms'     => 'mysql',
			'username' => 'root',
			'password' => 'root',
			'hostname' => '127.0.0.1',
			'hostport' => '3306',
			'database' => 'test'
		);
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
		*/
	public function getMenu($admin_id='')
	{
		if ($admin_id) {
			# code...
			$group_list = $this
			->Table('sj_admin_role A,sj_admin_node B, sj_admin_note_group AS C, sj_admin_node_group AS D')
			->where('A.admin_id="'.$admin_id.'" AND A.node_id=B.node_id AND C.node_id=B.node_id AND C.group_id=D.group_id')
			->field('D.*,C.id as map_id')
			->order('D.rank ASC, D.group_id ASC')
			->group('D.group_id')
			->select();
		} else {
			$group_list = $this
			->Table('sj_admin_node B, sj_admin_note_group AS C, sj_admin_node_group AS D')
			->where('C.node_id=B.node_id AND C.group_id=D.group_id')
			->field('D.*,C.id as map_id')
			->order('D.rank ASC, D.group_id ASC')
			->group('D.group_id')
			->select();
		}


		$groups = array();

		foreach ($group_list as $item) {
			$groups[$item['group_id']] = $item;
			$groups[$item['group_id']]['menu_item'] = array();
		}

		if ($admin_id) {
			$node_list = $this
			->Table('sj_admin_role A,sj_admin_node B, sj_admin_note_group AS C')
			->where('A.admin_id="'.$admin_id.'" AND A.node_id=B.node_id AND C.node_id=B.node_id')
			->field('B.node_id, B.nodename, B.postil, B.type, B.note, C.group_id, C.id as map_id')
			->select();
		} else {
			$node_list = $this
			->Table('sj_admin_node B, sj_admin_note_group AS C')
			->where('C.node_id=B.node_id')
			->field('B.node_id, B.nodename, B.postil, B.type, B.note, C.group_id, C.id as map_id')
			->select();
		}

		$nodes = array();
		$group_ids = array();
		$admin = array();
		foreach ($node_list as $item) {
			if ($item['type'] == 1 || empty($admin_id)) {
				if (!isset($nodes[$item['group_id']])) $nodes[$item['group_id']] = array();

				$arr = explode('/', $item['nodename']);
				$plateform = $arr[2];
				if (!isset($group_ids[$plateform])) {
					$group_ids[$plateform] = array();
				}
				$group_ids[$plateform][] = $item['group_id'];


				if ($plateform = $groups[$item['group_id']]['platform']){
					if (!isset($group_ids[$plateform])) {
						$group_ids[$plateform] = array();
					}
					$group_ids[$plateform][] = $item['group_id'];
				}

				if (!empty($item['note'])){
					$note = $item['note'];
					$menu_list = explode(';', $note);

					foreach ($menu_list as $menu_item) {
						if (empty($menu_item)) continue;
						$subitem = $item;
						list($nodename, $postil) = explode(',', $menu_item);
						$subitem['nodename'] = $item['nodename']. $nodename;
						$subitem['postil'] = $postil;
						$nodes[$item['group_id']][] = $subitem;
					}
				} else {
					$desc = explode('_', $item['postil']);
					if (isset($groups[$item['group_id']])) {
						$item['postil'] = str_replace($groups[$item['group_id']]['group_name']. '__', '', $item['postil']);
						$item['postil'] = str_replace($groups[$item['group_id']]['group_name']. '_', '', $item['postil']);
					}
					//$item['postil'] = $desc[1];
					$nodes[$item['group_id']][] = $item;
				}
			}
		}
		$group_menus = array();
		foreach ($group_ids as $plateform => $ids) {
			$group_menus[$plateform] = array();
			foreach ($ids as $gid) {
				if (isset($nodes[$gid])) {
					if (!isset($group_menus[$plateform][$gid])) {
						$group_menus[$plateform][$gid] = $groups[$gid];
						$group_menus[$plateform][$gid]['menu_item'] = array();
					}
					$group_menus[$plateform][$gid]['menu_item'] = $nodes[$gid];
				}
			}

		}
		$result = array();

		foreach($group_menus as $plateform => $val){
			foreach($groups as $gid => $v){
				if (isset($group_menus[$plateform][$gid])) {
					$result[$plateform][$gid] = $group_menus[$plateform][$gid];
				}
			}
		}
		return $result;
	}
}
?>