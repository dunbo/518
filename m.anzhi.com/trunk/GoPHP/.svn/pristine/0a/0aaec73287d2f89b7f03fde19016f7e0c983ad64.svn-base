<?php
/*
    数据层框架类
    负责数据的调度，提取，分配
*/
class GoPu_model
{
	protected static $_instance = array(); //model的单例存储
    public $db_arr; //所有db的连接
	public $table; //model用到的数据库表
    public $data_info; //负责数据的存储
    public $index_name; //model的索引名称 
    public $index; //model的数据索引数组, 必须为唯一值
    public $cache_key; //model存放在cache和单例数组中的key
    public $cache_timeout = 300; //cache的超时时间
    public $use_mysql_cache = False; //是否使用mysql的cache
    public $is_new = False; //是否是新数据

    //通过索引和model名生成cache_key
    public function make_cache_key($model_name, $index)
    {
        if ($index) { 
            return $model_name. '_' . (is_array($index)? serialize($index) : (string)$index);
        } else {
            return False;
        }
    }
   
    /*
       单例函数, 返回一个model类或一个数据存储
       @param string $model_name :  要取得的model
       @param array $index : model的数据索引 (可选，为空默认返回新model)
       @param array $data_info : model 的数据存储 (可选, 如为空，则从get_data_info中获取)
       @param string $return_mode : 返回的类型 'object' 为返回model的实例化类 'data_info' 为返回model的数据存储 (可选，默认为'object')
       @return GoPu_model OR array
    */
    public static function getInstance($model_name, $index = '', $data_info = array(), $return_mode = 'object')
    {   
        if (!$index) { //如果数据索引为空，返回新的实例化model
            return new $model_name();
        }
        $cache_key = call_user_func(array($model_name, 'make_cache_key'), $model_name, $index);
        if ( !isset(self::$_instance[$cache_key]) ) { //如果单例中没有该model的存储
            $model = new $model_name($index);
            if ( !($model->data_info = $data_info) ) { 
                $model->get_data_info(); //获取数据存储
            }
            $model->data2property(); //数据存储转为相应的model属性
            self::$_instance[$cache_key] = $model;
        } elseif ($data_info) {
            self::$_instance[$cache_key]->data_info = $data_info;//重置数据存储
        }
        if (!self::$_instance[$cache_key]->data_info) {
            self::$_instance[$cache_key]->is_new = True;
        }

        if ($return_mode == 'object') {
            return self::$_instance[$cache_key];
        } elseif ($return_mode == 'data_info') {
            return self::$_instance[$cache_key]->data_info;
        }
	}
	
    function __destruct()
    {
        unset($this->data_info);
    }
    public static function unsetModel($model, $index = '')
    {
        $model_name = "Go". ucfirst($model) ."Model";

        if (is_array($index) && key($index) === 0) {
            foreach ($index as $v) {
                $cache_key = call_user_func(array($model_name, 'make_cache_key'), $model_name, $v);
                unset(self::$_instance[$cache_key]);
            }
        } else {
            $cache_key = call_user_func(array($model_name, 'make_cache_key'), $model_name, $index);
            unset(self::$_instance[$cache_key]);
        }
    }
	
	
    /*
       单例函数, 返回多个model类或多个数据存储
       @param string $model_name : 对某个model进行批量提取
       @param array $index_arr : model的数据索引集合 ex: array($val1, $val2, ......);
       @param string $return_mode : 返回的类型 'object' 为返回model的实例化类 'data_info' 为返回model的数据存储 (可选，默认为'object')
       @return array(GoPu_model) OR array 
    */

    public static function getInstance_arr($model_name, $index_arr, $return_mode = 'object')
    {
        $model = new $model_name();
        $data_info_arr= $model->get_data_info_arr($index_arr, $model_name); //批量取得数据存储
        $instance_arr = array();
        foreach ($index_arr as $v) {
            if ($data_info_arr[$v]) {
                //把数据存储保存进单例数组对应的model
                $instance_arr[] =   self::getInstance($model_name, $v, $data_info_arr[$v]);
            }
        }
        if ($return_mode == 'object') {
            return $instance_arr;
        } elseif ($return_mode == 'data_info') {
            return $data_info_arr;
        }
    }

    //初始化model对应的cache_key
    function __construct($model_name, $index = '')
    {
        $this->model_name = $model_name;
        if ($index) {
            $this->cache_key = $this->cache_timeout? $this->make_cache_key($model_name, $index) : '';
            $this->index = $index;
            $index_name = $this->index_name;
            $this->$index_name = $index;
        }
    }

    //存储转化为对应的model属性，可由子类自定义
    function data2property()
    {
        return True;
    }

    /*
       保存数据的存储
       @return boolean : 是否保存成功 
    */
    function save_data_info()
    {
        $r1 = $r2 = $r3 = True;
        if ($this->is_use_db()) {
            if ($this->is_new) {
                $r1 = $this->insert();
            } else {
                $r2 = $this->update();
            }
        }
        if ($r1 && $r2) {
            if ($this->is_new) {
                $this->insert_complate($insert_index);
            }
            $r3 = $this->set_cache_data_info(); //数据保存进cache中
        }
        return $r1 && $r2 && $r3;
    }

    //数据存储save前进行过滤, 默认不过滤， 可由子类自定义
    function save_field_filter()
    {
        return $this->data_info; 
    }

    //insert 进db
    function insert()
    {
		return $this->get_db('write')->insert( $this->save_field_filter($this->data_info) );
    }

    //update db
    function update()
    {
        if (!$this->index) { return False; }
        return $this->get_db('write')->update(  array($this->index_name => $this->index), $this->save_field_filter($this->data_info) );
    }

    //cache命中失效的情况，取得DB中的数据，可由子类自定义
    function get_real_data_info($index)
    {
        if (!$this->is_use_db() || !$index) { return False; }
        $option = array(
            'where' => array($this->index_name => $index),
            'index' => $this->index_name,
        );
        $data_info = $this->findAll($option);        

        if (is_array($index)) { //如果是批量获取
            return $data_info;
        } else {
            return ($data_info? current($data_info) : array());
        }
    }

    //获取数据存储
    function get_data_info()
    {
        if (!$this->index) { return False; }
        if ($this->data_info) { return $this->data_info; }
        $this->data_info = $this->get_cache_data_info();
        if (!$this->cache_not_null($this->data_info)) {
            $this->data_info = $this->get_real_data_info($this->index); 
            $this->set_cache_data_info();
        }
        return $this->data_info;
    }

    //保存数据存储进cache
    public function set_cache_data_info()
    {
        if (!$this->is_use_cache() || !$this->cache_not_null($this->data_info)) { return True; }
        if ($this->use_mysql_cache) {
        } elseif ($this->cache_timeout) {
			$this->get_model_cache()->set($this->cache_key. '_time', time(), $this->cache_timeout);
            return $this->get_model_cache()->set($this->cache_key, $this->data_info, $this->cache_timeout);
        }
    }

    //从cache中获取存储
    public function get_cache_data_info()
    {
        if ($this->is_use_mysql_cache()) {
            $this->data_info = $this->get_mysql_cache($this->cache_key);
        } elseif ($this->is_use_cache()) {
            $this->data_info = $this->get_model_cache()->get($this->cache_key);
        }
        return $this->data_info;
    }

    //批量获取存储
    public function get_data_info_arr($index_arr, $model_name)
    {
        if ($this->is_use_mysql_cache() || !$this->is_use_cache() || !$this->is_use_db()) { return False; }
        $cache_key_arr = array();
        foreach ($index_arr as $k => $v) {
            if ($cache_key =  $this->make_cache_key($model_name, $v)) {
                $cache_key_arr[$v] = $cache_key;
            } else {
                unset($index_arr[$k]);
            }
        }
        $data_info_arr = array();
        $db_index_arr = array();
        //从cache中批量获取
        if ($cache_key_arr) {
            $cache_data_info_arr = $this->get_model_cache()->gets($cache_key_arr);
        }
        //判断哪些key命中失败 需要从db中获取
        foreach ($index_arr as $k => $v) {
            $cache_key =  $cache_key_arr[$v];
            if ( isset($cache_data_info_arr[$cache_key]) && $this->cache_not_null($cache_data_info_arr[$cache_key]) ) {
                $data_info_arr[$v]  = $cache_data_info_arr[$cache_key];
            } else {
                $db_index_arr[] = $v;
            }
        }
        if ($db_index_arr) {
            $db_data_info_arr = $this->get_real_data_info($db_index_arr);
            $set_cache_data_info_arr = array();
            foreach ($db_index_arr as $k => $v) {
                $cache_key =  $cache_key_arr[$v];
                if ($db_data_info_arr[$v]) {
                    $set_cache_data_info_arr[$cache_key] = $db_data_info_arr[$v];
                }
            }
            if ($set_cache_data_info_arr) {
                //之前没有命中到的key, 批量set回去
                $this->get_model_cache()->sets($set_cache_data_info_arr, $this->cache_timeout);
            }
            foreach ($db_data_info_arr as $key => $val) {
                $data_info_arr[$key] = $val;
            }
        }
        return $data_info_arr;
    }   

    //取得db连接
    protected function get_db($server_type)
	{
        if ($server_type == 'read') {
            $server = load_config('system/read_db');
            $server_num = $server_type.'_1';
        } elseif ($server_type == 'write') {
            $server = load_config('system/write_db');
            $server_num = $server_type.'_1';
        }
	    if (!$this->db_arr[$server_num]) {
    	    $this->db_arr[$server_num] = GoDB::getServerDriver($server);
    	    $this->db_arr[$server_num]->table = $this->table;
        }
		return $this->db_arr[$server_num];
	}

    //db中删除
    public function delete()
	{
		$r1 = $r2 = True;
        if ($this->is_use_cache()) {
            $r1 = $this->get_cache()->delete($this->cache_key);
        }
        $r2 = $this->get_db('write')->delete(array($this->index_name => $this->index) );			
        return $r1 && $r2;
	}

    //db中获取多行数据
    public function findAll($option = array())
    {
        return $this->get_db('read')->findAll($option);
    }

    //db中获取一行数据
    public function findOne($option)
    {
        return $this->get_db('read')->findOne($option);
    }

    //从mysql的cache中取得数据
    public function get_mysql_cache($cache_key, $extra = '') 
    {
        load_helper('mysqlcache');
        return getMysqlCache($cache_key, $extra);
    }

    //取得memcache连接
    public function get_cache($cache_type = '')
    {
        if (!isset($this->cache)) {
            $this->cache = GoCache::getCacheAdapter('Memcached');
        }
        return $this->cache;
    }
	
	public function get_model_cache($cache_type = '')
	{
        if (!isset($this->model_cache)) {
			$config = load_config('cache/model_memcached');
			if (!empty($config)) {
				$this->model_cache = new GoMemcachedCacheAdapter($config);
			} else {
				$this->model_cache = GoCache::getCacheAdapter('memcached');
			}
        }
        return $this->model_cache;
	}
    
    //判断取得的cache是否为空
    function cache_not_null($val)
    {
        return $val !== False && $val !== Null;
    }

    //添加数据完成以后的动作
    function insert_complate($insert_index)
    {
        if (!$this->index) {
            $index_name = $this->index_name;
            $this->$index_name = $this->index = $insert_index;
        }
        $this->__construct($this->model_name, $this->index);
        self::getInstance($this->model_name, $this->index, $this->data_info);
        $this->is_new = False;
        $this->data2property();
        $this->insert_complate_hook();
    }
    
    //添加数据扩展，可由子类实现 
    function insert_complate_hook()
    {
        return True;
    }

    //判断是否使用memcache的cache
    function is_use_cache()
    {
        return $this->cache_timeout;
    }

    //判断是否使用mysql
    function is_use_db() 
    {
        return $this->table && $this->index_name;
    }

    //判断是否使用mysql的cache
    function is_use_mysql_cache() 
    {
        return $this->use_mysql_cache;
    }
}
