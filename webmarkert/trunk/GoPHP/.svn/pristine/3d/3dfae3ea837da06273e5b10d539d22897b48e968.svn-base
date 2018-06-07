<?php
class GoCommonFilterModel extends GoModel
{
    public $cache;

    function __construct() {
        # memcache
        $this->cache = GoCache::getCacheAdapter('Memcached');
    }
    public function filterSoftId($id, $filter = array(), $exclude_package_arr = array()) {
        if (!is_array($id))
            return array();
        $ng = $this->getExcludeSoftList($filter);
        $id_fllip = array_flip($id);
        foreach ($ng as $softid => $package) {
        	if (isset($id_fllip[$softid])) {
        		unset($id_fllip[$softid]);
        	}
        }
		$key = 'MULTI_PACKAGE_INFO';
        $multi_package_info = $this->cache->get($key);
        $same_package_ids = array_intersect_key($multi_package_info, $id_fllip);
        $result = array();
        foreach ($same_package_ids as $i => $p) {
            if (isset($result[$p])) {
        		unset($id_fllip[$i]);
        	} else {
        		$result[$p] = $i;
        	}
            if ($exclude_package_arr && in_array($p, $exclude_package_arr)) { //过滤指定包名
                unset($id_fllip[$i]);
            }
        }
        /*
        $result = array();
        $key = 'SOFTLIST_SOFTID_TO_DETAIL';
        $id_to_detail = $this->cache->get($key);
        $i = 0;
        $ordered = array();
        foreach ($id_fllip as $softid => $v) {
        	$i++;
        	$now = $id_to_detail[$softid];
        	if (empty($now)) {
        		continue;
        	}
        	$p = $now['package'];
        	$now['order'] = $i;
        	if (isset($result[$p])) {
        		if (($now['version_code'] > $result[$p]['version_code']) || ($result[$p]['version_code'] == $now['version_code'] && $now['softid'] > $result[$p]['softid'])) {
        			unset($id_fllip[$softid]);
        			$result[$p] = $now;
        		} else {
        			unset($id_fllip[$result[$p]['softid']]);
        			continue;
        		}
        	} else {
	        	$result[$p] = $now;
        	}
        }
        */
        return array_keys($id_fllip);
    }
    
    
    public function filterPackage($package, $filter = array()) {
        if (!is_array($package))
            return array();
        //$package_all = $this->getAllSoftPackageList();
        $package_flip = array_flip($package);
        $ng = $this->getExcludeSoftList($filter);
        $ng = array_flip($ng);
        foreach ($ng as $p => $val) {
            if (isset($package_flip[$p]))
                unset($package_flip[$p]);
        }
        return array_keys($package_flip);
    }
    
    public function getAllSoftListDirect($extra_option = array()) {
        $option = array(
            'where' => array(
                'hide' => 1,
                'status' => 1,
        		'safe' => array('exp', '<2'),
            ),
            'field' => array('softid', 'package'),
            'index' => 'softid',
            'table' => 'sj_soft'
        );
        if (!empty($extra_option)) {
            foreach ($extra_option as $key => $val) {
                if (isset($option[$key]) && is_array($option[$key])) {
                    $option[$key] = array_merge($option[$key], $val);
                }
                else
                    $option[$key] = $val;
            }
        }
        $data = $this->findAll($option);
        if (!$data)
            return array();
        $package_all = $softid_all = array();
        foreach ($data as $idx => $val) {
            $softid_all[$idx] = $val['package'];
            if (isset($package_all[$val['package']])) {
            	if (is_array($package_all[$val['package']])) {
            		$package_all[$val['package']][] = $idx;
            	} else {
            		$package_all[$val['package']] = array($package_all[$val['package']], $idx);
            	}
            } else {
            	$package_all[$val['package']] = $idx;
            }
        }
        return array(
        	'softid_all' => $softid_all,
        	'package_all' => $package_all,
        );
    }
    public function getAllSoftPackageListCached($extra_option = array(), $expire = 300) {
        $key = "SOFTLIST_PACKAGE";
        if (!empty($extra_option))
            $key .= ("_". md5(json_encode($extra_option)));
        $result = $this->cache->get($key);
        if (!$result) {
            $result = $this->getAllSoftListDirect($extra_option);
            $result = $result['package_all'];
            $this->cache->set($key, $result['package_all'], $expire);
        }
        return $result;
    }

    public function getAllSoftPackageList($extra_option = array()) {
        return $this->getAllSoftPackageListCached($extra_option);
    }
    
    public function getExcludeSoftListCached($filter = array(), $expire = 300) {
        $result = array();
        if (isset($filter['device'])) {
            $did = $filter['device'];
            $ng = $this->cache->get("SOFTLIST_NG_SOFTID_D${did}");
            if (!empty($ng))
                $result = $result + $ng;
        }
        if (isset($filter['firmware'])) {
            $fid = $filter['firmware'];
            $ng = $this->cache->get("SOFTLIST_NG_SOFTID_F${fid}");
            if (!empty($ng))
                $result = $result + $ng;
        }
        if (isset($filter['channel'])) {
            $cid = $filter['channel'];
            $ng = $this->cache->get("SOFTLIST_NG_SOFTID_C${cid}");
            if (!empty($ng))
                $result = $result + $ng;
        }
        if (isset($filter['authorized']) && $filter['authorized'] > 0) {
            $ng = $this->cache->get('SOFTLIST_NA_SOFTID');
            if (!empty($ng))
                $result = $result + $ng;
        }
        //过滤不安全软件
        if (1) {
        	$ng = $this->cache->get('SOFTLIST_UNSAFE_SOFTID');
        	if (!empty($ng)) {
	        	$result = $result + $ng;
        	}
        }
        
		if (isset($filter['abi'])) {
			$abi = $filter['abi'];
            $ng = $this->cache->get("SOFTLIST_NG_SOFTID_A${abi}");
            if (!empty($ng))
                $result = $result + $ng;
        }                
        return $result;
    }

    public function getExcludeSoftList($filter = array()) {
        return $this->getExcludeSoftListCached($filter);
    }
    
	public function getPackageToSoftId($in) {
        $package_all = $this->getAllSoftPackageList();
        $result = array();
        foreach ((array)$in as $val) {
        	if (isset($package_all[$val])) {
				if(is_array($package_all[$val])) {
        			$result = $result + $package_all[$val];
        		} else {
	        		$result[] = $package_all[$val];
        		}
        	}
        }
        return $result;
    }
}
