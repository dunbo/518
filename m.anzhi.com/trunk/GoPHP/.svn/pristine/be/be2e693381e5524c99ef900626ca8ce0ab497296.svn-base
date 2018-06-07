<?php
/*
    软件搜索model
    index格式 $search_key
    data_info 格式 array(softid1, softid2, softid3,....);
*/

class GoPu_category_searchModel extends GoPu_model
{
    public $search_key = '';  //搜索的关键词
    public $index_name = 'search_key';
    public $sphinx_client; //sphinx连接
    public $search_sort_factor; //排序的权重因子
    public $pid;

    function __construct($index = '')
    {
        parent::__construct(__CLASS__, $index);
		//var_dump($index);
        if ($index) { //初始化资源
            ($this->cache_timeout = load_config('search_cache_timeout'))? True : $this->cache_timeout = 5;
			if (is_array($index)) {
				$this->search_key = $index['search_key'];
				$this->pid = $index['pid'];
			}
            $this->search_key =  preg_replace('/[^\x{4e00}-\x{9fff}a-zA-Z0-9\.]+/u', '', $this->search_key);
            ($this->sphinx_client['server'] = load_config('sphinx_server'))? True : $this->sphinx_client['server'] = '127.0.0.1';
            ($this->sphinx_client['port'] = load_config('sphinx_port'))? True : $this->sphinx_client['port'] = 9312;
        }
    }

    //该model不支持批量获取
    function get_data_info_arr($index_arr, $class = '')
    {
        return False;
    }

    //重载父类的get_real_data_info
    function get_real_data_info()
    {
        $this->data_info = $this->get_search_result();
		//var_dump($this->data_info);
        return $this->data_info;
    }
   
    //设置排序的权重, 并进行排序 
    function set_search_result_weight($res)
    {
        $softid_arr = array();
        $weight_arr = array();
		$cache = GoCache::getCacheAdapter('memcached');
		$white_softid_pkg = $cache -> get('safe_white_pkgs');

        //sphinx无法做到精准排序，所以只能在外部进行排序 
        foreach ($res as $match) {
			$softid_arr[$match['id']] = $match['id'];
            $weight_arr[$match['id']] = $match['weight'];
        }
		//var_dump($res);
        return array(
            'softid_arr' => $softid_arr,
            'weight_arr' => $weight_arr,
            'count' => count($res),
        );
    }

    //取得搜索的softid
    function get_search_result() 
    {  
        $this->init_sphinx_client();        
        $q = $this->search_key;
		//$this->sphinx_client['conn']->SetMatchMode ( SPH_MATCH_ALL); //扩展模式匹配
		$redis=GoCache::getCacheAdapter('redis');
		if (!empty($this->pid)){
			//$redis=GoCache::getCacheAdapter('redis');
			if($this -> pid < 3){ // 1,2为应用和游戏大分类
				$all_types = $redis->get('ALL_TYPES');
				$category_ids = isset($all_types[$this->pid]) ? $all_types[$this->pid] : array($this->pid);
				$query = array();
				foreach($category_ids as $item){
					$query[] = "@category_id ,{$item},";
				}
				$query = implode('|', $query);
			}else{
				$query = "@category_id ,{$this -> pid}";
			}
			$query = "({$q} | *{$q}*| ^{$q} )({$query})";
		} else {
			$query = "$q | *$q*| ^$q ";
		}
		$this->sphinx_client['conn']->SetMatchMode (SPH_MATCH_EXTENDED2);
		$data = $redis->sIsMemberSet("Search_weight_words",strtolower($q));
        if($data){
		    $this->sphinx_client['conn']->setSortMode(SPH_SORT_EXTENDED,"@weight DESC");
		}else{
	 		$this->sphinx_client['conn']->setSortMode(SPH_SORT_EXTENDED,"total_downloaded DESC,score DESC");
		}
	 	$this->sphinx_client['conn']->AddQuery($query,"sj_soft,infix_sj_soft,prefix_sj_soft");
		$this->sphinx_client['conn']->SetMatchMode (SPH_MATCH_EXTENDED2);
        $this->sphinx_client['conn']->setSortMode(SPH_SORT_EXTENDED,"total_downloaded DESC,score DESC");
        $this->sphinx_client['conn']->AddQuery($query,"sj_soft_pinyin,infix_sj_soft_pinyin,prefix_sj_soft_pinyin");
		$this->sphinx_client['conn']->SetMatchMode ( SPH_MATCH_EXTENDED2);
		$this->sphinx_client['conn']->setSortMode(SPH_SORT_EXTENDED,"@weight DESC");
		$this->sphinx_client['conn']->AddQuery($query,"sj_soft_other,infix_sj_soft_other,prefix_sj_soft_other");
		$res = $this->sphinx_client['conn']->RunQueries();
		$softname_arr = array();
		$softname_pinyin_arr = array();
		$other_arr = array();
		if(!empty($res))
		{
			foreach($res[0]['matches'] as $k =>$val)
			{
				$softname_arr[$val['id']] = $val;
			}
			foreach($res[1]['matches'] as $k =>$val)
			{
				$softname_pinyin_arr[$val['id']] = $val;
			}
			foreach($res[2]['matches'] as $k =>$val)
			{
				$other_arr[$val['id']] = $val;
			}
		}
		//var_dump($other_arr);
		foreach($softname_arr as $key)
		{
			if(isset($softname_pinyin_arr[$key]))
			{
				unset($softname_pinyin_arr[$key]);
			}
		}
		$softname_arr = array_merge($softname_arr,$softname_pinyin_arr);
		foreach($softname_arr as $key)
		{
			if(isset($other_arr[$key]))
			{
				unset($other_arr[$key]);
			}
		}
		$merge_result = array_merge($softname_arr,$other_arr);
		$res = array_slice($merge_result,0,1000);
		//$this->search_sort_factor = 10;
        return $this->set_search_result_weight($res);
    }

    //初始化sphinx客户端
    function init_sphinx_client()
    {
        go_require_once( GOPHP_ROOT.'/lib/GoSphinxApi.php' );
        $cl = new sphinx_client();
        $cl->SetServer ($this->sphinx_client['server'], $this->sphinx_client['port']);
        $cl->SetConnectTimeout ( 1 ); //设置查询的超时时间
        $cl->SetArrayResult ( True ); //以数组形式返回
        //设置字段的相应权重
        //$cl->SetFieldWeights( array( "package" => 1, "dev_name" => 1, "softname" => 40, "tags" => 4, "intro" => 1) );
        $cl->SetSelect("*");//返回所有的属性值
        $cl->SetRankingMode ( SPH_RANK_PROXIMITY_BM25 ); //内部权重匹配算法
        $cl->SetLimits(0, 1000); //最多提取1000条 (server端默认限制1000条)
        $cl->SetIndexWeights(array("prefix_sj_soft" => 1, "infix_sj_soft" => 5, "sj_soft" => 5));//softname的前缀索引, softname的中缀索引,全文索引的权重设置            
        $cl->SetFilter ( "status", array (1)); //status为1
        $cl->SetFilter ( "hide", array (1)); //hide为1
        //$cl->SetFilter ( "safe", array (0, 1)); //safe小于2
        $this->sphinx_client['conn'] = $cl; 
        return $this->sphinx_client;
    }
	
    function make_cache_key($model_name, $index)
    {
        return md5(json_encode($index)); 
    }
}

