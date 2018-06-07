<?php
class GoFileCacheAdapter
{
	protected $cache_root;
	protected $prefix = 'file_cache_';

	public function __construct($config = '')
	{
		$this->cache_root = GOPHP_ROOT . '/cache';
	}
	//string 类型操作
	public function set($key, $value)
	{
		$cache_file = $this->cache_root . '/~' . $this->prefix . $key . '.php';
		$to_write = '<?php' . "\n";
		$to_write .= '//' . date('Y-m-d H:i:s') . "\n";
		$to_write .= '$cache = ' . var_export($value, true) . ";\n";
		$to_write .= 'return $cache;' . "\n";
		$to_write .= '?>' . "\n";
		$fp = fopen($cache_file, "w");
		if (flock($fp, LOCK_EX)) { // 进行排它型锁定
			$res = fwrite($fp, $to_write);
			fflush($fp);
			flock($fp, LOCK_UN); // 释放锁定
			fclose($fp);
			return $res;
		} else {
			fclose($fp);
			return false;
		}
	}
	//string 类型操作
	public function get($key)
	{
		if (empty($key)) {
			return false;
		}
		$cache_file = $this->cache_root . '/~' . $this->prefix . $key . '.php';
		$result = include_once($cache_file);
		return $result;
	}
}
?>
