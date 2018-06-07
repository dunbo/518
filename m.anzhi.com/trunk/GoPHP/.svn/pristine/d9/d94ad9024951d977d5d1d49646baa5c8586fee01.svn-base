<?php
/*
    分类model
    index格式 : $category_id;
    data_info格式 :  sj_category数据库表中的字段  + all(分类下的软件数量) + feed  + free;
    不基于单个分类进行提取，而是提取出所有分类以后，再按category_id提取单条数据
*/
class GoPu_categoryModel extends GoPu_model
{
    public $table = 'sj_category';
    public $category_id = 0;
    public $index_name = 'category_id';
    public $name = '';
    public $sub_category = '';
    public $cache_timeout = 0;

    public $cache_timeout_all_category = 300;
    public $cache_key_all_category = 'PU_ALL_CATEGORY'; //存储所有分类的cache
    static public $all_category = ''; //所有的分类数组

    function __construct($index = '')
    {
        parent::__construct(__CLASS__, $index);
        $this->get_all_category();
    }

    function data2property()
    {
        if (isset($this->data_info['category_id'])) {
            $this->category_id = $this->data_info['category_id'];
        }
        if (isset($this->data_info['name'])) {
            $this->name = $this->data_info['name'];
        }
    }

    //重载了父类的get_data_info, 直接从所有分类的数组中提取单个分类数据
    function get_data_info()
    {
        if ($this->data_info) { return $this->data_info; }
        $this->data_info = self::$all_category[$this->category_id];
        return $this->data_info;
    }

    //通过分类名提取分类
    function get_model_info_by_name($name)
    {
        foreach (self::$all_category as $k => $v) {
           if ($v['name'] == $name) {
               $data_info = $v;
               break;
           }
        }
        return parent::getInstance(__CLASS__, $data_info['category_id'], $data_info);
    }

    //读取多个category 机制同get_data_info
    function get_data_info_arr($index_arr, $model_name)
    {
        $data_info_arr = array();
        $c = count($index_arr);
        foreach (self::$all_category as $k => $v) {
            if ($c <= count($data_info_arr)) {
                break;
            }
            if (in_array($k, $index_arr)) {
                $data_info_arr[$k] = $v;
            }
        }
        return $data_info_arr;
    }

    //取得所有的category数组, 并且统计所属分类的软件数量
    function get_all_category()
    {
        if (!self::$all_category) {
            self::$all_category = $this->get_cache()->get($this->cache_key_all_category);
        }
        if (!self::$all_category) {
            $option = array( 'where' => array( 'status' => 1,),
                             'index' => 'category_id');
            $data = $this->findAll($option);
            $result = array();
			$cache = GoCache::getCacheAdapter('redis');
			$tmp = array();
			foreach($data as $key => $val){
				$parentid = $data[$key]['parentid'];

				if ($parentid == 1 || $parentid==2 || $parentid==3 || $parentid==0) continue;
				
				if (isset($data[$parentid])) {
					$data[$key]['parentid'] = $data[$parentid]['parentid'];
					$tmp[$parentid] = $parentid;
				}
			}
			foreach($tmp as $k) {
				unset($data[$k]);
			}
            foreach ($data as $idx => $val) {
				//if (intval($temp[$idx]['free']) + intval($temp[$idx]['feed']) == 0) { continue; }
				/*if (!$get_ebook && ($val['parentid'] == 3 || $val['category_id'] == 3)) {
                    unset($data[$idx]);
                    continue;
                }*/
				$key = "SOFTLIST_CATEGORY_SOFTID_${idx}_NEW";
				$r = $cache->get($key);
                $data[$idx]['free'] = count($r);
                $data[$idx]['feed'] = 0;
                $data[$idx]['all'] =    $data[$idx]['feed'] + $data[$idx]['free'];
                if ($val['parentid']) {
                    $data[$val['parentid']]['free'] += $data[$idx]['free'];
                    $data[$val['parentid']]['feed'] += $data[$idx]['feed'];
                    $data[$val['parentid']]['all'] += $data[$idx]['all'];
                }
            }
            self::$all_category = $data;
            $this->get_cache()->set($this->cache_key_all_category, self::$all_category, $this->cache_timeout_all_category);
        }
        return self::$all_category;
    }

    //取得子分类
    function get_sub_category()
    {
        if ($this->sub_category) {
            return $this->sub_category;
        }
        foreach (self::$all_category as $k => $v) {
            if ($v['parentid'] == $this->category_id) {
                $this->sub_category[$k] = $v;
                //取得的分类直接缓存到单例数组中
                parent::getInstance(__CLASS__, $k, $v);
            }
        }
        return $this->sub_category;
    }

    //all, feed, free为计算出来的冗余字段，不需要存储进db
    function save_field_filter()
    {
        $data_info = $this->data_info;
        unset($data_info['all']);
        unset($data_info['feed']);
        unset($data_info['free']);
        return $data_info;
    }

    //重载父类方法  保存all_category数据，并set进缓存
    function set_cache_data_info()
    {
        self::$all_category[$this->category_id] = $this->data_info;
        return $this->get_cache()->set($this->cache_key_all_category, self::$all_category, $this->cache_timeout_all_category);
    }
}

