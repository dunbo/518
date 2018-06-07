<?php
/*
    软件model
    index格式 : $softid
    data_info格式 :  sj_soft_file数据库表下的字段;
*/

class GoPu_softModel extends GoPu_model
{
    public $table = 'sj_soft';
    public $index_name = 'softid';
    public $softid = 0;
    public $softname = '';
    public $package = '';

    public $comment_id_arr = array();
    public $cache_key_comment_id = 'SOFT_COMMENT_ID_'; //存储评论ID
    public $cache_timeout_comment_id = 300;

    public static $package2id_arr = array();
    public $cache_key_package2id = 'SOFTLIST_PACKAGE'; //所有软件ID到包名的映射
    public $cache_timeout_package2id = 0;

    public $suggest_id_arr = array();
    public $cache_key_suggest_id = 'SOFT_SUGGEST_ID_';//maybe like 的软件ID
    public $cache_timeout_suggest_id = 300;

    function __construct($index = '')
    {
        parent::__construct(__CLASS__, $index);
        if ($index) {
            $this->cache_key_comment_id .= $index; //设置存储软件下评论ID的key
            $this->cache_key_suggest_id .= $index; //设置存储软件下 maybe like ID的key
        }
    }

    //重载了父类的get_real_data_info 添加了软件关联文件ID(file_id) 和软件截图ID(thumb_id)
    function get_real_data_info($index)
    {
        $option = array(
            'table' => $this->table.' AS A',
            'where' => array('A.'.$this->index_name => $index,'A.status' => 1,'A.hide' => 1 /* array('exp','<>3') */, 'B.package_status' => 1, 'C.status' => 1),
            'field' => array('A.* , if(A.total_downloaded - A.total_downloaded_detain + total_downloaded_add > 0 , A.total_downloaded - A.total_downloaded_detain + total_downloaded_add , 0 ) as total_downloaded', 'B.id AS file_id', 'C.id AS thumb_id','D.name AS category_name', 'D.parentid', 'B.filesize','B.min_firmware', 'B.sign', 'A.category_id', 'B.iconurl', 'B.url'),
            'join'  => array(
							'sj_soft_file AS B' => array('on' => array('A.softid', 'B.softid'), 'join_type' => 'left'),
                            'sj_soft_thumb AS C' => array('on' => array('A.softid', 'C.softid'), 'join_type' => 'left'),
							'sj_category AS D' => array('on' => array("A.category_id"," CONCAT(',',D.category_id,',')"))
                        ),
        );
        $key = $this->index_name;
        $data_info = array();
		$memcache = GoCache::getCacheAdapter('memcached');
		$type_id = $memcache->get('TYPE_ID');
		
        if ($data = $this->findAll($option)) {
            foreach ($data as $d) {
				
				$category = explode(',', $d['category_id']);
				$category = $category[1];
				$d['parentid'] = $type_id[$category]['parentid'];
				
                if (!isset($data_info[$d[$key]])) {
                    $data_info[$d[$key]] = $d;
					$data_info[$d[$key]]['category'] = $category;
                    $data_info[$d[$key]]['file_id'] = $d['file_id']? array($d['file_id']) : array();
                    $data_info[$d[$key]]['thumb_id'] = $d['thumb_id']? array($d['thumb_id']) : array();
                } else {
                    if ($d['file_id'])  { $data_info[$d[$key]]['file_id'][] = $d['file_id']; }
                    if ($d['thumb_id']) { $data_info[$d[$key]]['thumb_id'][] = $d['thumb_id']; }
                }
            }
        }
	
        if (is_array($index)) {
            return $data_info;
        } else {
            return ($data_info? current($data_info) : array());
        }
    }

    //软件IP，软件名，和包名直接作为属性
    function data2property()
    {
        if ($this->data_info) {
            $this->softid = $this->data_info['softid'];
            $this->softname = $this->data_info['softname'];
            $this->package = $this->data_info['package'];
        }
    }

    //取得软件的评论ID
    function get_comment_id_arr()
    {
        if (!$this->softid) { return False; }
        if ($this->comment_id_arr) { return $this->comment_id_arr; }
        $this->comment_id_arr = $this->get_cache()->get($this->cache_key_comment_id);
        if ($this->cache_not_null($this->comment_id_arr)) { return $this->comment_id_arr; }
        $option = array('where' => array('package' => $this->package,'status' => 1, 'is_new' => 1),
                        'field' => array('id'),
                        'order' => 'create_time DESC',
                );
        $soft_comment_obj = pu_load_model_obj('pu_comment');
        $id_arr = $soft_comment_obj->findAll($option);
        $this->comment_id_arr = array();
        foreach ($id_arr as $v) {
            $this->comment_id_arr[] = $v['id'];
        }
        $this->cache->set($this->cache_key_comment_id, $this->comment_id_arr, $this->cache_timeout_comment_id);
        return $this->comment_id_arr;
    }

    //清除软件的评论ID缓存
    function clear_cache_comment_id()
    {
        return $this->get_cache()->delete($this->cache_key_comment_id);
    }
	
	function getPackageSuggestCache($package) {
		//$key = $package . '_suggest';
		$key = $package . '_related';//用户还下载了

        $config = load_config('cache/suggest_redis');
        if ($config) {
            $adapter = new GoRedisCacheAdapter($config);
        } else {
            $adapter = GoCache::getCacheAdapter('redis');
        }
		$cache = $adapter->get($key);

		return $cache;
	}
	
	function getImeiInstalledDirect($imei) {
		$installed = array();
		$option = array(
			'where' => array(
				'imei' => $imei,
			),
			'table' => 'sj_device_user',
			'field' => 'id'
		);
		$res = $this->findOne($option);
		$uid = $res['id'];
		if ($uid > 0) {
			$option = array(
			'table' => 'sj_device_user_package',
			'where' => array(
				'id' => $uid
			),
			'field' => 'packages'
			);
			$data = $this->findOne($option);
			if ($data) {
				$installed = json_decode($data['packages']);
			}
		}
		
		return $installed;
	}
	
	function getImeiInstalledCache($imei) {
		$key = 'USER_INSTALLED_' . $imei;
		$cache = $this->get_cache()->get($key);
		if (!$cache) {
			$cache = $this->getImeiInstalledDirect($imei);
			$this->get_cache()->set($key, $cache, 600);
		}
		return $cache;
	}
	
    function get_suggest_id($imei = '') {
	
        if (!$this->package) { return False; }
        if ($this->suggest_id_arr) { return $this->suggest_id_arr; }
        $this->suggest_id_arr = $this->get_cache()->get($this->cache_key_suggest_id);
        if ($this->cache_not_null($this->suggest_id_arr)) { return $this->suggest_id_arr; }
        $imei = false;//暂时注释用户已安装列表
		if ($imei) {
			$installed = $this->getImeiInstalledCache($imei);
			if($installed){
				foreach ($installed as $val) {
					$tmp_package = '';
					if (is_array($val)) {
						$tmp_package = $val[0];
					} else {
						$tmp_package = $val;
					}
					$exclude[$tmp_package] = 1;
				}
			}
		}
		
		$result = array();
		
		$data = $this->getPackageSuggestCache($this->package);
		
		$n = 0;
		foreach ((array)$data as $p => $val) {
			if (!isset($result[$p]) && !isset($exclude[$p])) {
				$result[$p] = 1;
				$n += 1;
			}
			if ($n >= 100)
			break;
		}
		
		$packages = array_keys($result);
		
		$this->suggest_id_arr = array();
		//self::$package2id_arr = $this->get_package2softid_arr();
		$package_all = $this->get_pkg2id($packages);
		
		foreach ($packages as $p) {
			//$this->suggest_id_arr[] = is_array(self::$package2id_arr[$p])? current(self::$package2id_arr[$p]) : self::$package2id_arr[$p];
			if (!isset($package_all[$p])) continue;
            if (is_array($package_all[$p])) {
                foreach ($package_all[$p] as $sid) {
                    $this->suggest_id_arr[] = $sid;
                }
            } else {
                $this->suggest_id_arr[] = $package_all[$p];
            }
		}
		$this->get_cache()->set($this->cache_key_suggest_id, $this->suggest_id_arr, $this->cache_timeout_suggest_id);
		return $this->suggest_id_arr;

    }

    //取得所有的ID 对应包名的映射
    function get_package2softid_arr()
    {
        if (self::$package2id_arr) { return self::$package2id_arr; }
        self::$package2id_arr = $this->get_cache()->get($this->cache_key_package2id);
        return self::$package2id_arr;
    }
	
	function get_pkg2id($package)
	{
        $package = (array) $package;
        $p_keys = array();
        foreach ($package as $p) {
            $p_keys[] = $p. ':ID';
        }
		$memcache = GoCache::getCacheAdapterNew('cache/soft_memcached');
        $package_all = $memcache->gets($p_keys);
        $result = array();
        $p_keys = array();
        foreach ($package as $p) {
            $k = $p. ':ID';
            if (isset($package_all[$k])) {
                $result[$p] = $package_all[$k];
            } else {
                $p_keys[] = $p;
            }
        }
        return $result;
		$package = (array) $package;
		$redis = GoCache::getCacheAdapter('redis');
		$package_all = $redis->gethash('ENABLED:PKG2ID', $package);
		return $package_all;
	}

    //file_id 与thumb_id 不属于sj_soft表， 所以在保存进DB的时候过滤掉 
    function save_field_filter()
    {
        $data_info = $this->data_info;
        unset($data_info['file_id']);
        unset($data_info['thumb_id']);
        return $data_info;
    }

    //根据包名取得model
    function get_model_by_package($package, $filter_logic)
    {
        //self::$package2id_arr = $this->get_package2softid_arr();
		$package_all = $this->get_pkg2id($package);
		if (is_array($package)) {
			$softid_arr = array();
			foreach ($package as $p) {
				//if (is_array(self::$package2id_arr[$p])) {
				if (is_array($package_all[$p])) {
					if ($filter_logic) {
						//$tmp_arr = $filter_logic->filter_softid(self::$package2id_arr[$p]);
						$tmp_arr = $filter_logic->filter_softid($package_all[$p]);
						if ($tmp_arr) {
							$softid_arr[] = $tmp_arr[0];
						}
					}
				} else {
					//$softid_arr[] = self::$package2id_arr[$p];
					$softid_arr[] = $package_all[$p];
				}
			}
			if (!$softid_arr) return false;

			return parent::getInstance_arr(__CLASS__, $softid_arr);
		} else {
			//if (is_array(self::$package2id_arr[$package])) {
			if (is_array($package_all[$package])) {
					if ($filter_logic) {
						//$tmp_arr = $filter_logic->filter_softid(self::$package2id_arr[$package]);
						$tmp_arr = $filter_logic->filter_softid($package_all[$package]);
						if ($tmp_arr) {
							$softid = $tmp_arr[0];
						}
					}
			} else {
				//$softid = self::$package2id_arr[$package];
				$softid = $package_all[$package];
			}
			if (!$softid) return false;
			return parent::getInstance(__CLASS__, $softid);
		}

    }
}
