<?php
class GoDB
{
	public static $dbConfig = array();
	public static $drivers = array();

	public static function init() {}

	/**
	 *
	 * Enter description here ...
	 * @param string $server
	 * @return GoDBDriver
	 */
	public static function getServerDriver($server)
	{
		$server_key = $server;
		if (isset(self::$drivers[$server_key])) {
			return self::$drivers[$server_key];
		} else {
			$driver = null;
			$namespace = 'config';
			if (strstr($server, '/')) {
				list($server, $namespace) = explode('/', $server);
			}
			if (empty(self::$dbConfig[$namespace])) {
				self::$dbConfig[$namespace] = load_config('db', $namespace);
			}
			
			$dbConfig = self::$dbConfig[$namespace][$server];
			$dbType = ucfirst($dbConfig['type']);
			if (empty($dbType)) {
				go_log(GO_ERROR, go_trace());
			} else {
				$driverClassName = "Go{$dbType}Driver";
				$driver = new $driverClassName();

				$driver->connect($dbConfig);
				self::$drivers[$server_key] = $driver;
			}

			return $driver;
		}
	}

	public static function query($query, $server = 'master')
	{
		$driver = self::getServerDriver($server);
		return $driver->query($query);
	}

	public static function closeAll()
	{
		foreach(self::$drivers as $driver) {
			$driver->close();
		}
	}
}

abstract class GoDBDriver
{
	protected $conn;

	/**
	 * 数据库连接接口
	 * @param unknown_type $dbConfig
	 */
	abstract public function connect($dbConfig);
	/**
	 *
	 * 查询方法，对于mysql类型的查询，考虑添加cache扩展支持
	 *
	 * @param unknown_type $query
	 */
	abstract public function query($query);

	/**
	 * 结果集的遍历方法
	 * @param unknown_type $result
	 * @param unknown_type $result_type
	 */
	abstract public function fetch($result);

	public function escape_string($string){}
	public function close(){}

}

class GoMysqlDriver extends GoDBDriver
{
	protected $conn;
	protected $latestSql;
	public $table;
	protected $dbConfig;
	protected $max_retry = 3;
	protected $connect_timeout = 1;
	protected $db_cache = null;
	public $cache_timeout = '';
	public $cache_key = '';

	public function connect($dbConfig)
	{
		$this->dbConfig = $dbConfig;
		ini_set('mysql.connect_timeout', $this->connect_timeout);
		$start = microtime_float();
		$retry = 0;
		while($retry < $this->max_retry){
			$this->conn = @mysql_connect($dbConfig['host']. ':'. $dbConfig['port'], $dbConfig['username'], $dbConfig['password'], true);
			if ($this->conn) {
				break;
			}
			$retry++;
		}

		$end = microtime_float();
		$spend = $end - $start;
		$time = date('Y-m-d H:i:s', time());
		if(!$this->conn){
			go_error(mysql_error($this->conn), "HTTP/1.1 417 Mysql Server {$dbConfig['host']} unreachable");
			//file_put_contents('/tmp/mysql_c_error.log', "{$time} : {$spend} retry {$retry} times\n", FILE_APPEND);
		} else {
			if ($spend > 0.5 || $retry > 1) {
                $day = date('Y-m-d', time());
                $sid = session_id();
                file_put_contents("/tmp/mysql_connect_{$day}.log",  "{$time} : {$dbConfig['host']} {$sid} {$spend} retry {$retry} times\n", FILE_APPEND);
			}
		}

		if (!empty($dbConfig['charset'])) {
			mysql_query('SET NAMES '. $dbConfig['charset'], $this->conn);
			
			mysql_set_charset($dbConfig['charset'], $this->conn);
		}

		mysql_select_db($dbConfig['database'], $this->conn);
	}
	public function query($sql)
	{
		if (!mysql_ping ($this->conn)) {
			$this->close();
			$this->connect($this->dbConfig);
		}
		$this->latestSql = $sql;
		$start = microtime_float();
		$res = mysql_query($sql, $this->conn);
		//debug
		$spend = microtime_float()-  $start;
		$sid = session_id();
		$message = "{$sql} time : {$sid} {$spend} \n";
		//if(GoService::getInstance()->getParameter('KEY') != 'LOGIN_NEW') echo $message;
		if ($spend > 0.5 && strtoupper(substr($sql, 0 , 6)) == 'SELECT') {
			//go_log(GO_INFO, $message);
			$day = date('Y-m-d', time());
			$message = date('H:i:s ', time()). $message;
			file_put_contents("/tmp/query_slow_{$day}.log", $message, FILE_APPEND);
		}
		if(mysql_errno($this->conn) >0){
			go_log(GO_ERROR, $sql .mysql_error($this->conn). "\n". go_trace());
			//file_put_contents('/tmp/xsql.log', $sql .mysql_error($this->conn). "\n". go_trace(). "\n", FILE_APPEND);
		}
		empty($GLOBALS['_db_debug']) && $GLOBALS['_db_debug'] = '';
		$GLOBALS['_db_debug'] .= $message;
		return $res;
	}

	public function fetch($result)
	{
		return mysql_fetch_assoc($result);
		//return $result->fetch();
	}

	public function findOne($option)
	{
		$option['limit'] = 1;
		$res = $this->findAll($option);

		return $res ? $res[0] : false;
	}

	public function get_db_cache()
	{
		if (!$this->db_cache) {
			$this->db_cache = GoCache::getCacheAdapterNew('cache/soft_memcached');
		}
		return $this->db_cache;
	}
	
	public function findAll($option = array(), $returnRes = false)
	{
		$refresh = isset($option['refresh']) ? $option['refresh'] : false;
		unset($option['refresh']);
		$cache_key = 'DB_FIND:' . strtoupper(md5(json_encode($option)));
		$this->cache_key = $cache_key;
		$result = false;
		if (isset($option['cache_time'])) {
			$cache = $this->get_db_cache();
		}
		if (isset($option['cache_time']) && empty($option['returnRes']) && empty($returnRes) && !$refresh) {
			$this->cache_timeout = $option['cache_time'];
			if ($cache) $result = $cache->get($cache_key);
			$code = $cache->getCode();
			if ( $code != Memcached::RES_NOTFOUND && $result) return $result;
		}
		$wherestring = isset($option['where']) ? $this->getWhereString($option['where']) : '';
		$fieldString = isset($option['field']) ? $this->getFieldString($option['field']) : '*';
		$distinct = isset($option['distinct']) && $option['distinct'] ? "DISTINCT" : "";
		$order = isset($option['order']) ? "ORDER BY ".$option['order'] : "";
		$limit = '';
		if (isset($option['limit'])) {
			if (isset($option['offset'])) {
				$limit = 'limit '. intval($option['offset']). ', '. intval($option['limit']);
			} else {
				$limit = 'limit '. intval($option['limit']);
			}
		}

		$group = isset($option['group']) ? "GROUP BY {$option['group']}" : "";

		$table = isset($option['table']) ? $option['table'] : $this->table;

		$join = isset($option['join']) ? $this->getJoinString($option['join']) : '';

		$sql = "SELECT {$distinct} {$fieldString} FROM {$table} {$join} {$wherestring} {$group} {$order} {$limit};";
		$query_start = time();
		$res = $this->query($sql);
		
		if ($returnRes || !empty($option['returnRes'])) {
			return $res;
		} elseif($res) {
			$values = array();
			if (empty($option['index'])) {	
                if (isset($option['returnGoRes'])) {
 				    $gores = new GoMysqlResultSet($res);
				    return $gores;   
                }
				while ($row = $this->fetch($res)) {
					$values[] = $row;
				}
				
			} else {
				$field = $option['index'];
				while ($row = $this->fetch($res)) {
					$values[$row[$field]] = $row;
				}
				$this->free_result($res);
			}
            if (isset($option['cache_time']) && $values) {
				if ($cache) {
					$cache->set($cache_key, $values, $option['cache_time']);
					$cache->set($cache_key. '_time', time(), $option['cache_time']);
				}
			}
			return $values;
		}
		return false;

	}

	public function insert_id()
	{
		return mysql_insert_id($this->conn);
	}

	public function affected_rows()
	{
		return mysql_affected_rows($this->conn);
	}

	public function insert($data)
	{
		$table = '';
		$field = '';
		$value = '';
		if(!empty($data['__user_table'])){
			$table = $data['__user_table'];
			unset($data['__user_table']);
		} else {
			$table = $this->table;
		}
		if(!empty($data['__user_action'])){
			$action = strtoupper($data['__user_action']);
			unset($data['__user_action']);
		} else {
			$action = 'INSERT';
		}
				
		foreach ($data as $k => $v) {
			$field .= '`'. $k. '`,';
			$value .= "'". $this->escape_string($v). "',";
		}
		if ($field != '' && $value != '') {
			$field = substr($field, 0, -1);
			$value = substr($value, 0, -1);
			$sql = "{$action} INTO  {$table} ({$field}) VALUES ({$value})";

			$r = $this->query($sql);
			if ($r === false) {
				return $r;
			} else {
				$insert_id = $this->insert_id();
				return $insert_id > 0 ? $insert_id : $this->affected_rows();
			}

		}
		return false;
	}

	public function update($where, $data)
	{
		$table = '';
		if(!empty($data['__user_table'])){
			$table = $data['__user_table'];
			unset($data['__user_table']);
		} else {
			$table = $this->table;
		}
		$increase = array();
		if(!empty($data['__increase'])){
			$increase = $data['__increase'];
			unset($data['__increase']);
		}
		$wherestring = $this->getWhereString($where);

		$set = array();
		foreach ($data as $k => $v) {
			if (is_array($v) && is_string($v[0]) && strtolower($v[0]) == 'exp'){
				$set[] = '`'. $k. '`='. $v[1];
			} else {
				$set[] = '`'. $k. '`='. "'". $this->escape_string($v)."'";
			}
		}
		if(!empty($increase)){
			foreach($increase as $k=>$v){
				$set[] = "`$k`=`$k`+$v";
			}
		}
		if (!empty($set)) {
			$set = implode(',', $set);
			$sql = "UPDATE {$table} SET {$set} {$wherestring};";
			$r = $this->query($sql);
			return ($r === false) ? $r : $this->affected_rows();
		}

		return false;
	}

	public function delete($where)
	{
		$table = '';
		if(!empty($where['__user_table'])){
			$table = $where['__user_table'];
			unset($where['__user_table']);
		} else {
			$table = $this->table;
		}
		
		$wherestring = $this->getWhereString($where);
		if ($wherestring != '') {
			$sql = "DELETE FROM {$table} {$wherestring};";
			$r = $this->query($sql);
			return ($r === false) ? $r : $this->affected_rows();
		}
		return 0;
	}
	public function escape_string($string)
	{
		return mysql_real_escape_string($string);
	}


	private function getWhereString($where) {
		if(empty($where)) return "";
		if (is_array($where)) {
			$cond = array();
			$no_where = false;
			foreach ($where as $field => $value) {
				if($field == "_no_where") {
					$no_where = true;
				} else if(is_array($value)) {
					//if (!empty($value)) {
						if ($value[0] === 'exp') {
							//@TODO缺陷！以下临时处理，会导致调用异常！！！
							$value[1] = str_replace('#', '', $value[1]);
							$value[1] = str_replace('--', '', $value[1]);
							$value[1] = str_replace('union', '', $value[1]);
							$value[1] = str_replace('select', '', $value[1]);
							$value[1] = str_replace('outfile', '', $value[1]);
							$cond[] = "($field {$value[1]})";
						} else {
							$value = array_unique($value);
							$valueset = implode('", "', array_map('mysql_real_escape_string', $value));
							$cond[] = "$field IN (\"$valueset\")";
						}
					//}
				} else if($field!="_user_defined_condition"){
					$value = $this->escape_string($value);
					$cond[] = stripos($field, '.') ? "$field=\"$value\"" : "`$field`=\"$value\"";
				} else {
					$cond[] = $value;
				}
			}
			return ($no_where ? " ":"where ") . implode(" AND ", $cond);
		} elseif(is_string($where)) {
			return "WHERE {$where}";
		}
	}

	private function getFieldString($field)
	{
		$fieldString = array();
		if (is_array($field)) {
			foreach ($field as $item) {
				$fieldString[] = stripos($item, '.') ? $item : "`{$item}`";
			}
			$fieldString = implode(',', $fieldString);
		} else {
			$fieldString = $field;
		}

		return $fieldString;
	}

	private function getJoinString($join)
	{
		$string = '';
		$type_list = array('LEFT', 'RIGHT', 'INNER');
		foreach ($join as $table => $item) {
			if (empty($item['on'])) continue;

			$join_type = (!empty($item['join_type']) && in_array(strtoupper($item['join_type']), $type_list)) ? strtoupper($item['join_type']) : 'LEFT';
			$string .= $join_type. ' JOIN '. $table;

			$string .= " on {$item['on'][0]} = {$item['on'][1]} ";
		}

		return $string;
	}


	public function getLatestSql()
	{
		return $this->latestSql;
	}
	
	public function getSql()
	{
		return $this->latestSql;
	}
		
	public function free_result($result)
	{
		mysql_free_result($result);
	}
	public function close()
	{
		mysql_close($this->conn);
	}
	
}
//效率不高，不推荐使用
class GoMysqlResultSet implements Iterator, ArrayAccess
{
	protected $key = 0;
	protected $valid = false;
	protected $res;
	protected $count;
	protected $current;
	
	function __construct($res)
	{
		$this->res = $res;
		$this->count = mysql_num_rows($this->res);
		if ($this->count > 0) $this->valid = true;
	}
	
	function rewind()
	{
		if ($this->count > 0) {
			$this->valid = true;
			$this->key = 0;
			mysql_data_seek($this->res, $this->key);
		}
	}
	
	function current()
	{
		return $this->current;
	}
	
	function key()
	{
		return $this->key;
	}
	
	function next()
	{
		$this->key++;
		$this->current = mysql_fetch_assoc($this->res);
		$this->valid = ($this->key < $this->count);
	}
	function valid()
	{
		return $this->valid;
	}
	
	function offsetExists($offset)
	{
		return $offset < $this->count;
	}
	 
	function offsetGet($offset)
	{
		if ($offset < $this->count) {
			$this->key = $offset;
			return $this->current();
		} else {
			return false;
		}
	}
	 
	function offsetSet($offset, $value)
	{
	}
	function offsetUnset($offset)
	{
	}
	
	function fetch()
	{
		$this->next();
		if ($this->valid()) {
			return $this->current();
		} else {
			return false;
		}
	}
}

class GoMongodbDriver extends GoDBDriver
{
	public $mongo;
	public $mongodb;
	
	public function connect($dbConfig)
	{
		$conn_str = 'mongodb://';
		if (isset($dbConfig['username']) && isset($dbConfig['password'])) {
			$conn_str .= "{$dbConfig['username']}:{$dbConfig['password']}@";
		}
		$conn_str .= "{$dbConfig['host']}:{$dbConfig['port']}";
		$this->mongo = new Mongo($conn_str);
		if (isset($dbConfig['database'])) {
			$this->mongodb = $mongo->selectDB($dbConfig['database']);
		}
	}
	
	public function query($query)
	{
		
	}
	
	public function fetch($result)
	{
		
	}
	
	public function close()
	{
		$this->mongo->close();
	}
}
