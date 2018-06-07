<?php
/*
 软件搜索model
 index格式 $search_key
 data_info 格式 array(softid1, softid2, softid3,....);
 */

class GoPu_sphinx_searchModel extends GoPu_model
{
	public $search_key = '';  //搜索的关键词
	public $index_name = 'search_key';
	public $sphinx_client; //sphinx连接
	public $search_sort_factor; //排序的权重因子
	public $pid;
	public $natrue_rate = '';
	public $down_rate = '';
	public $socre_rate = '';

	function __construct($index = '')
	{
		parent::__construct(__CLASS__, $index);
		if ($index)
		{
			//初始化资源
			($this->cache_timeout = load_config('search_cache_timeout'))? True : $this->cache_timeout = 900;
			if (is_array($index)) {
				$this->search_key = $index['search_key'];
				//$this->natrue_rate = $index['nature_rate'];
				//$this->down_rate = $index['score_rate'];
				$this->pid = $index['pid'];
			}
			$this->search_key =  preg_replace('/[^\x{4e00}-\x{9fff}a-zA-Z0-9\.]+/u', '', $this->search_key);
			($this->sphinx_client['server'] = load_config('sphinx_server'))? True : $this->sphinx_client['server'] = '127.0.0.1';
			($this->sphinx_client['port'] = load_config('sphinx_port'))? True : $this->sphinx_client['port'] = 9313;
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
		return $this->data_info;
	}

	//设置排序的权重, 并进行排序
	function set_search_result_weight($res)
	{
		$softid_arr = array();
		$weight_arr = array();
		$cache = GoCache::getCacheAdapter('memcached');
		$white_softid_pkg = $cache -> get('safe_white_pkgs');
		$nature_weight_arr = array();
		$download_arr = array();
		$score_arr = array();
		//sphinx无法做到精准排序，所以只能在外部进行排序
		$res_match_len = count($res['matches']);
		$w = $res_match_len+1;
		$cmpTime = 0;
		$biggest = $smallest = $res['matches'][$res_match_len-1]['attrs']['total_downloaded'];
		for($i = 0; $i < $res_match_len - 1; $i += 2) {
			$cmpTime++;
			if($res['matches'][$i]['attrs']['total_downloaded'] > $res['matches'][$i + 1]['attrs']['total_downloaded']) {
				$bigger = $res['matches'][$i]['attrs']['total_downloaded'];
				$smaller = $res['matches'][$i+1]['attrs']['total_downloaded'];
			} else {
				$bigger = $res['matches'][$i+1]['attrs']['total_downloaded'];
				$smaller = $res['matches'][$i]['attrs']['total_downloaded'];
			}
			$cmpTime++;
			if($bigger > $biggest) {
				$biggest = $bigger;
			}
			$cmpTime++;
			if($smaller < $smallest) {
				$smallest = $smaller;
			}
		}
		$d_download = ($biggest-$smallest)/$res_match_len;
		foreach ($res['matches'] as $match) {
			//排序的权重加成算法， 权重 * $this->search_sort_factor + 下载量
  
			$download = round($match['attrs']['total_downloaded']/$d_download);
			//$d = ($w*$this->natrue_rate+$download*$this->down_rate+$match['attrs']['score']*$this->socre_rate);
			//$d = ($w+$download+$match['attrs']['score']*100*0.2);
			$d = $w;
			$safe = $match['attrs']['safe'];
			$softid = $match['attrs']['id'];
			if( !isset($white_softid_pkg[$softid]) && $safe >= 2){
				continue;
			}
			//if ($this->search_sort_factor == 3000 && $match['weight'] <= 3) { //如果仅仅是在描述，开发者名词和包名中有, 权重要放低
			//  $d = ceil($d / $this->search_sort_factor);
			//} elseif ($this->search_sort_factor == 10 && $match['weight'] <= 3000) { //如果仅仅是在描述，开发者名词和包名中有, 权重要放低
			// $d = ceil($d / 3000);
			//}
			$softid_arr[$match['id']] = $match['id'];
			//$weight_arr[$match['id']] = $match['weight'] * $this->search_sort_factor + $d;
			$weight_arr[$match['id']] = $d;
			$nature_weight_arr[$match['id']] = $w;
			$download_arr[$match['id']] = $download;
			$score_arr[$match['id']] = $match['attrs']['score'];
			$w--;
		}
		
		$by_sort_weight = $weight_arr;
		array_multisort($by_sort_weight, SORT_DESC, $softid_arr);
		return array(
            'softid_arr' => $softid_arr,
            'weight_arr' => $weight_arr,
			'nature_weight_arr'	=>	$nature_weight_arr,
			'download_arr' =>	$download_arr,
			'score_arr'	=>	$score_arr,
            'count' => $res['total'],
		);
	}

	//取得搜索的softid
	function get_search_result()
	{
		$this->init_sphinx_client();
		$q = $this->search_key;
		$this->sphinx_client['conn']->SetMatchMode ( SPH_MATCH_EXTENDED2 ); //扩展模式匹配,可以试下词组匹配
   		//$this->sphinx_client['conn']->setSortMode(SPH_SORT_ATTR_DESC,'score');
   		//$this->sphinx_client['conn']->SetMatchMode ( SPH_MATCH_ALL);
        //$this->sphinx_client['conn']->SetMatchMode(SPH_MATCH_PHRASE);
		if (!empty($this->pid)){
			$redis=GoCache::getCacheAdapter('redis');
			$all_types = $redis->get('ALL_TYPES');
			$category_ids = isset($all_types[$this->pid]) ? $all_types[$this->pid] : array($this->pid);
			$query = array();
			foreach($category_ids as $item){
				$query[] = "@category_id ,{$item},";
			}
			$query = implode('|', $query);
			$query = "({$q} | *{$q}*| ^{$q} )({$query})";
		} else {
			$query = "$q | *$q*| ^$q ";
		}

		$res = $this->sphinx_client['conn']->Query($query);
		$build_keys = $this->sphinx_client['conn']->BuildKeywords($query,$q,true);

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
		$cl->SetFieldWeights( array( "package" => 1, "dev_name" => 1, "softname" => 400, "tags" => 1, "intro" => 1) );
		$cl->SetSelect("*");//返回所有的属性值
		$cl->setSortMode(SPH_SORT_EXTENDED,"@relevance DESC,total_downloaded DESC,score DESC,");
		//$cl->SetRankingMode ( SPH_RANK_PROXIMITY_BM25 ); //内部权重匹配算法
		$cl->SetRankingMode ( SPH_RANK_PROXIMITY);
		$cl->SetLimits(0, 1000); //最多提取1000条 (server端默认限制1000条)
		$cl->SetIndexWeights(array("prefix_sj_soft" => 1, "infix_sj_soft" => 5, "sj_soft" => 1));//softname的前缀索引, softname的中缀索引,全文索引的权重设置
		$cl->SetFilter ( "status", array (1)); //status为1
		$cl->SetFilter ( "hide", array (1)); //hide为1
		//$cl->SetFilter ( "safe", array (0, 1)); //safe小于2
		$this->sphinx_client['conn'] = $cl;
		return $this->sphinx_client;
	}

	function make_cache_key($model_name, $index)
	{
		return md5($model_name.json_encode($index));
	}
}