<?php
class GoMysqlCache
{
	/**
	 * 
	 * Enter description here ...
	 * @var GoDBDriver
	 */
	protected $model = null;
	protected $table = null;
	protected $key_name = null;
	protected $value_name = null;
	public function __construct()
	{
		$config = load_config('cache/mysql');
		
		$this->model = new GoModel();

		$this->table = $config['table'];
		$this->key_name = $config['key_name'];
		$this->value_name = $config['value_name'];
	}
	public function set($key, $value, $expired = 0)
	{ 
		$data = array(
			$this->key_name => $key,
			$this->value_name => json_encode($value),
			'__user_action' => 'REPLACE',
			'__user_table' => $this->table,
		);
		return $this->model->insert($data);
	}
	public function get($key, $callback = array(), $param_arr = array(), $expired = 0)
	{
		$option = array(
			'where' => array(
				$this->key_name => $key
			),
			'field' => $this->value_name,
			'table' => $this->table,
		);
		$result = $this->model->findOne($option);
		if(!$result && !empty($callback)){
			$res = call_user_func_array($callback, $param_arr);
			$this->set($key, $res, 0, false);
			return $res;
		} else {
			return json_decode($result[$this->value_name], true);
		}
	}
	public function delete($key, $time = 0)
	{
		$where = array(
			$this->key_name => $key,
			'__user_table' => $this->table,
		);
		return $this->model->delete($where);
	}
} 