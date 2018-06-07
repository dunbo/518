<?php
class CommentModel{
	
	private $db_read;
	private $db_write;
	public function __construct(){
		$this->db_read = D('Dev.Comment_read');
		$this->db_write = new Model();
	}

    public function __call($name, $arguments) 
    {
		$select_name = array(
			'count' => 1,
			'find' => 1,
			'select' => 1,
			'findAll' => 1,
			'findOne' => 1,
			'limit' => 1,
			'field' => 1,
			'group' => 1,
			'order' => 1,
			'join' => 1,
			'buildSql' => 1,
		);
		$update_name = array(
			'query' => 1,
			'save' => 1,
			'add' => 1,
			'update' => 1,
			'delete' => 1,
		);
		
		$common_name = array(
			'table' => 1,
			'where' => 1,
		);
		
		if (isset($select_name[$name])) {
			$r = call_user_func_array (array($this->db_read, $name),  $arguments);
		}
		
		if (isset($update_name[$name])) {
			$r = call_user_func_array (array($this->db_write, $name),  $arguments);
		}
		if (isset($common_name[$name])) {
			$r = call_user_func_array (array($this->db_write, $name),  $arguments);
			$r = call_user_func_array (array($this->db_read, $name),  $arguments);
		}		
		if (is_a($r, 'Model')) {
			return $this;
		} else {
			return $r;
		}
    }
	
      
}
