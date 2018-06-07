<?php
class GoPu_ConfigModel extends GoModel
{
    public $table = 'pu_config';
    public $config_file = '';
    public $config_from_file = array();
    protected $cache_key = 'GLOBAL:CONFIG';
    protected $cache_time_key = 'GLOBAL:CONFIG:TIME';
    
	public function __construct()
	{
		$path = GOPHP_ROOT. DS. 'cache';
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}
		$this->config_file = $path. DS. '~config.php';
	}
	
    public function getConfiguration($key, $default = false)
    {
        $value = GoCache::get($key);
        if (!$value) {
            $option = array(
                'where' => array(
                    'config_type' => $key,
                    'status' => 1
                ),
                'field' => 'configcontent',
            );
            
            $config = $this->findOne($option);
            
            if (!$config || empty($config['configcontent'])) {
                $value = $default;
            } else {
                $value = $config['configcontent'];
            }
            GoCache::set($key, $value, 300);
        }
        return $value;
    }
    
    public function refreshConfig($config_key = '')
    {
        $redis = Gocache::getCacheAdapter('redis');
        $memcache = Gocache::getCacheAdapter('memcached');
        $option = array(
			'where' => array(
				'status' => 1
			)
		);
        if (!empty($config_key)) {
            $option['where']['config_type'] = $config_key;
        }
        $tmp = $data = array();
        $result = $this->findAll($option);
		$max_time = 0;
        foreach ($result as $val) {
            $key = $val['config_type'];
            //详情页专区只展示论坛内容（6.4.9+）
            if($key == 'detail_forum_show'){
                load_helper('utiltool');
                
                if($val['configcontent'] == 1){
                    $k_value = 0;
                }else{
                    $k_value = 1;
                }
                $p_data = array(
                    'KEY' => 'SET_MARKET_FLAG',
                    'CK' => 'BBS_PB_FLAG',
                    'VALUE' => $k_value,
                );
                postBbsDataT($p_data);
            }
            if ($val['uptime']>$max_time) $max_time = $val['uptime'];
            if (isset($tmp[$key])) {
                if ($tmp[$key] == 1) {
                    $t = $data[$key];
                    $data[$key] = array();
                    $data[$key][] = $t;
                }
                $data[$key][] = array($val['configname'], $val['configcontent'], $val['uptime']);
                $tmp[$key]++;
            } else {
                $data[$key] = array($val['configname'], $val['configcontent'], $val['uptime']);
                $tmp[$key] = 1;
            }
        }
        foreach ($data as $key => $val) {
            $memcache->set('GOCONFIG:'. $key, $val, 86400);
        }
        $memcache->set($this->cache_time_key, $max_time, 86400);
		if (empty($config_key)) {
			unset($data['badword']);
			unset($data['soft_shieldpackagename']);
			unset($data['soft_badword']);
			unset($data['soft_remind_words']);
			unset($data['soft_cp_highlight_edit']);
			load_helper('config');
			$tmp_data = getExtraConfig();
			if (!empty($tmp_data)) {
                foreach ($tmp_data as $k => $v) {
                    $data[$k] = $v;
                    if (stripos($k, 'TIMESTAMP') !== false && $v>$max_time) {
                        $max_time = $v;
                    }
                }
                $memcache->set($this->cache_time_key, $max_time, 86400);
			}
			$data[$this->cache_time_key] = $max_time;
			$arr_str = var_export($data, true);
			$time = date('Y-m-d H:i:s');
			$cache_str = <<<EOF
<?php
//generate by GoPHP at {$time}
\$config = {$arr_str};
return \$config;
EOF;
			$fp = fopen($this->config_file, "w+");

			if (flock($fp, LOCK_EX)) { // 杩涜鎺掑畠鍨嬮攣瀹?
				fwrite($fp, $cache_str);
				flock($fp, LOCK_UN); // 閲婃斁閿佸畾
			}
			fclose($fp);
		}
        return $data;
    }
    
    public function getConfigByMemcached($key)
    {
        $mem_key = array();
        foreach ($key as $k) {
            $mem_key[] = 'GOCONFIG:'. $k;
        }
        $memcache = GoCache::getCacheAdapter('memcached');
        $mem_result = $memcache->gets($mem_key);
        
        $refresh = false;
        $result = array();
        foreach($mem_result as $k => $v) {
            if (empty($v)) {
                $refresh = true;
                break;
            }
            $k = str_replace('GOCONFIG:', '', $k);
            $result[$k] = $v;
        }
        
        if ($refresh) {
            $result = array();
            $data = $this->refreshConfig();
            if (is_array($key)) {
                foreach ($key as $v) {
                    $result[$v] = $data[$v];
                }
            } else {
                $result = $data[$key];
            }
        }
        return $result;
    }
	
	public function getFileConfig()
	{
		if (!$this->config_from_file) {
			$this->config_from_file = go_require_once($this->config_file);
		}
	}
	
    public function getConfigByFile($key)
    {
		$this->getFileConfig();
        $refresh = false;
        $result = array();
        foreach((array)$key as $k) {
			if (isset($this->config_from_file[$k])) {
				$result[$k] = $this->config_from_file[$k];
			}
        }
        return $result;
    }
	
    public function getConfig($key, $type = 'file')
    {
        if ($type == 'file') {
			$result = $this->getConfigByFile($key);
		} elseif($type == 'memcached') {
			$result = $this->getConfigByMemcached($key);
		}
        return $result;
    }
    
    public function getConfigStmp($type = 'file')
    {
        if ($type == 'file') {
			$res = $this->getConfig($this->cache_time_key, 'file');
			return $res[$this->cache_time_key];
		} elseif($type == 'memcached') {
			$memcache = GoCache::getCacheAdapter('memcached');
			return $memcache->get($this->cache_time_key);
		}
    }
}

