<?php
/**
 * gophp数据模型类
 * @TODO 需要对query方法的sql语句做插入/查询判断！！
 * @author Administrator
 *
 */
class GoModel
{
	protected static $_instance = array();
	protected $lastServer;
	public $lastDb;
	public $table;
	public $custom_detail_option;

	/**
	 *单点类
	 * @param GoModel $class
	 */
	public static function getInstance($class = '', $alias = '')
	{
		$alias = empty($alias) ? $class : $alias;
        if(empty($alias)&&empty($class)){
            $alias = $class = __CLASS__;
        }
		if (empty(self::$_instance[$alias])) {
			self::$_instance[$alias] = new $class;
		}
		return self::$_instance[$alias];
	}

	public function findOne($option = array(), $server = '')
	{
		$db = $this->getDb($server, 'SELECT');
		return $db->findOne($option);
	}

	public function findAll($option = array(), $server = '')
	{
		$db = $this->getDb($server, 'SELECT');
		return $db->findAll($option);
	}
	
	public function update($where, $data, $server = '')
	{
		$db = $this->getDb($server, 'UPDATE');
		return $db->update($where, $data);
	}

	public function insert($data, $server = '')
	{
		$db = $this->getDb($server, 'INSERT');
		return $db->insert($data);
	}

	public function query($sql, $server = '')
	{
		$db = $this->getDb($server, $sql);
		return $db->query($sql);
	}

	public function fetch($result)
	{
		return $this->lastDb->fetch($result);
	}
	
	public function affected_rows()
	{
		return $this->lastDb->affected_rows();
	}

	public function escape_string($string, $server = '')
	{
		$db = $this->getDb($server);
		return $db->escape_string($string);
	}

	public function delete($where, $server = '')
	{
		$db = $this->getDb($server, 'DELETE');
		return $db->delete($where);
	}

	public function getLatestSql()
	{
		return $this->lastDb->getLatestSql();
	}
	public function getSql()
	{
		return $this->getLatestSql();
	}
	public function free_result($result)
	{
		return $this->lastDb->free_result($result);
	}

	public function getDb($server, $sql = '')
	{
		if (empty($server)) {
			if (!empty($sql) && strtoupper(substr($sql, 0 , 6)) == 'SELECT') {
				$server = load_config('system/read_db');
			} else {
				$server = load_config('system/write_db');
			}
		}
		$db = GoDB::getServerDriver($server);
		$db->table = $this->table;
		$this->lastDb = $db;
		
		return $db;
	}
	
	public function getCustomCache() {
		if (empty($this->custom_detail_option['cache_obj'])) {
			if (empty($this->custom_detail_option['cache'])) {
				$this->custom_detail_option['cache_obj'] = GoCache::getCacheAdapterNew('cache/soft_memcached');
			} else {
				$this->custom_detail_option['cache_obj'] = GoCache::getCacheAdapterNew($this->custom_detail_option['cache']);
			}
		}
		return $this->custom_detail_option['cache_obj'];
	}
	
	public function getDetailById($ids) {
		if (empty($ids)) {
			return false;
		}
		if (!is_array($ids)) {
			$ids_arr = array($ids);
		} else {
			$ids_arr = $ids;
		}
		
		// 默认值
		if (empty($this->custom_detail_option['cache'])) {
			$this->custom_detail_option['cache'] = 'cache/soft_memcached';
		}
		if (empty($this->custom_detail_option['cache_time'])) {
			$this->custom_detail_option['cache_time'] = 3600;
		}
		if (empty($this->custom_detail_option['id_field'])) {
			$this->custom_detail_option['id_field'] = 'id';
		}
		if (empty($this->custom_detail_option['select_option']['table'])) {
			if (empty($this->table)) {
				return false;
			}
			$this->custom_detail_option['select_option']['table'] = $this->table;
		}
		if (empty($this->custom_detail_option['select_option']['field'])) {
			$this->custom_detail_option['select_option']['field'] = '*';
		}
		$cache = $this->getCustomCache();
		
		$cache_key_prefix = 'DETAILROW:' . strtoupper(md5(json_encode($this->custom_detail_option)));
		
		$key_arr = array();//批量去cache取key
		$id_key_arr = array();//id对应的key
		foreach ($ids_arr as $id) {
			$key = "{$cache_key_prefix}:{$id}";
			$id_key_arr[$id] = $key;
			if (!in_array($key, $key_arr)) {
				$key_arr[] = $key;
			}
		}
		
		// 批量从cache中取key
		$info_list = $cache->gets($key_arr);
		
		$fail_ids_arr = array();//缓存中没有取到详情的id
		$id_info_arr = array();//id对应detail
		foreach ($id_key_arr as $id => $key) {
			if (!array_key_exists($key, $info_list)) {
				$fail_ids_arr[] = $id;
			} else {
				$id_info_arr[$id] = $info_list[$key];
			}
		}
		
		// 如果缓存没取到，到mysql中取
		if (!empty($fail_ids_arr)) {
			$fail_key_info_arr = array();
			if (empty($this->custom_detail_option['callback'])) {
				// 默认的查mysql函数
				$fail_id_info_arr = $this->getDetailDirectbyId($fail_ids_arr);
			} else {
				// 自定义回调函数查mysql
				$callback = array($this, $this->custom_detail_option['callback']);
				$fail_id_info_arr = call_user_func($callback, $fail_ids_arr);
			}
			
			if (!empty($fail_id_info_arr)) {
				foreach ($fail_id_info_arr as $id => $row) {
					$key = "{$cache_key_prefix}:{$id}";
					$fail_key_info_arr[$key] = $row;
					$id_info_arr[$id] = $row;// 合并id_info_arr
				}
				if (!empty($fail_key_info_arr)) {
					// 补写缓存
					$cache->sets($fail_key_info_arr, $this->custom_detail_option['cache_time']);
				}
			}
		}
		
		return $id_info_arr;
		
	}
	
	public function getDetailDirectbyId($ids) {
		if (empty($ids)) {
			return false;
		}
		
		$id_str = implode(',', $ids);
		
		$option = $this->custom_detail_option['select_option'];
		$id_field = $this->custom_detail_option['id_field'];
		
		$id_info_arr = array();
		
		$new_option = $option;
		$where = array();
		$where[$id_field] = array('exp', "in ({$id_str})");
		if (empty($option['where'])) {
			$option['where'] = array();
		}
		$new_option['where'] = array_merge($where, $option['where']);
		$res = $this->findAll($new_option, $this->custom_detail_option['server']);
		if (empty($res)) {
			return $res;
		}
		foreach ($res as $res_row) {
			$id = $res_row[$id_field];
			$id_info_arr[$id] = $res_row;//补充
		}
		return $id_info_arr;
	}

}
