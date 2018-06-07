<?php
/*
    软件搜索model
    index格式 $search_key
    data_info 格式 array(softid1, softid2, softid3,....);
*/

class GoPu_searchModel extends GoPu_model
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
			($this->cache_timeout = load_config('search_cache_timeout'))? True : $this->cache_timeout = 6;
			if (is_array($index)) {
				$this->search_key = $index['search_key'];
				$this->pid = $index['pid'];
			}
			//$this->search_key =  preg_replace('/[^\x{4e00}-\x{9fff}a-zA-Z0-9\.\s\-]+/u', '', $this->search_key);
			if (!preg_match('/^[a-z0-9 \.\-]+$/i', $this->search_key)) {
				$this->search_key =  preg_replace('/[^\x{4e00}-\x{9fff}a-zA-Z0-9\-]+/u', '', $this->search_key);
			}
			$this->search_key =  str_replace(array("(",")"),array(),$this->search_key);
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
		
		$person_contrl = array();
		
		//sphinx无法做到精准排序，所以只能在外部进行排序 
		foreach ($res['matches'] as $match) {
			//排序的权重加成算法， 权重 * $this->search_sort_factor + 下载量
			$d = $match['attrs']['total_downloaded'];
			
			$safe = $match['attrs']['safe'];
			
			$softid = $match['attrs']['id'];
			
			//人工干预属性
			$runindex = $match['attrs']['runindex'];
			
			$is_intervres = $match['attrs']['is_intervres'];
			
			if( !isset($white_softid_pkg[$softid]) && $safe >= 2){
				continue;
			}
			
			$softid_arr[$match['id']] = $match['id'];
			$weight_arr[$match['id']] = $match['weight'] * $this->search_sort_factor + $d;
			
			if($is_intervres > 0){
				$person_contrl[$match['id']] = array($runindex, $d);
			}
		}
		
		$by_sort_weight = $weight_arr;
		
		//人工干预系数 大于 0 单独处理
		return array(
			'softid_arr' => $softid_arr,
			'weight_arr' => $weight_arr,
			'person_contrl' => $person_contrl,
		);
		
	}

	//取得搜索的softid
	function get_search_result() 
	{   
	  static $useindex=0;
		$start = microtime_float();
		$this->init_sphinx_client();
		$q = $this->search_key;
		//是否是全字母
		$py_flag = 0;
		if(preg_match('/^[a-z]+$/i',$q)) $py_flag = 1;
		if(preg_match('/^[a-z\d]*$/i',$q)&& strlen($q)>4)
		{	
			$i = 0;
			$str = '';
			$num = '';
			for($i;$i<strlen($q);$i++)
			{
				$ord_num = intval(ord($q[$i]));
				if($ord_num>=45 && $ord_num <=57)
				{
				$num .= $q[$i];
				}else{
				$str .= $q[$i];
				}
			}
			if(intval(ord($q[0]))>=45 && intval(ord($q[0]))<=57)
			{
				$q_bak = $num.' '.$str;
			}else{
				$q_bak = $str.' '.$num;
			}
			$q_bak = trim($q_bak);
			
		}
		
		
		
		$this->pid = '';

		if (!empty($this->pid)){
			$redis=GoCache::getCacheAdapter('redis');
			$all_types = $redis->get('ALL_TYPES');
			$category_ids = isset($all_types[$this->pid]) ? $all_types[$this->pid] : array($this->pid);
			$query = array();
			foreach($category_ids as $item){
			$query[] = "@category_id ,{$item},";
			}
			$query = implode('|', $query);
			if(isset($q_bak))
			{
			$query_bak = "(^{$q_bak} | *{$q_bak}*| l{$q_bak} )({$query})";
			$query = "(^{$q} | *{$q}*| l{$q} )({$query})";	
			}else{
			$query = "(^{$q} | *{$q}*| l{$q} )({$query})";
			}
		} else {
			if(isset($q_bak))
			{
					$query_bak = "^$q_bak | *$q_bak|*$q_bak*|$q_bak*|$q_bak$|($q_bak)";
					$query = "^$q |^$q_bak | *$q_bak|*$q_bak*|$q_bak*|$q_bak$|($q_bak)";
			}else{
					$query = "^$q |*$q|*$q*|$q*|$q$|($q)";
			}
		}
		$q = isset($q_bak)?$q_bak:$q;
		
		
		$k = $this->sphinx_client['conn']->BuildKeywords($q,"sj_soft",false);
		$query_bak_st = $query;
		
		
		
		
		//需要优化,只对非中文有效.无法做到全面优化.
		if(strlen($q)>2){
			
			/**
			 *@BuildKeywords 某些词并未做排从处理,如 新中国新中国;则会出现多次中国,需要后期排从处理.
			 * 
			 */
			$normalizedArray = array(); 
			
			foreach($k as $s=>$v)
			{  
			    
				$md5 = md5($v['normalized']);
				
				if(!$normalizedArray[$md5]){
					 
					 $normalizedArray[$md5] = $md5;
				     
					 $query_bak_st .= "|^".$v['normalized']."|*".$v['normalized']."*|".$v['normalized']."$"."|(".$v['normalized'].")";	  
					  
				}
				
			}
			
		} 
		
		//$this->sphinx_client['conn']->SetFieldWeights( array("softname" =>1000,"softname_pinyin"=>1,"softname_pinyin_short"=>10,"softname_pinyin_no_blank"=>1) );
		$this->sphinx_client['conn']->SetIndexWeights(array("prefix_sj_soft" =>10, "infix_sj_soft" =>10,"sj_soft" => 1));
		
		$this->sphinx_client['conn']->SetSortMode (SPH_SORT_EXPR,"@weight*20+total_downloaded+score*10");
		
		$this->sphinx_client['conn']->SetMatchMode(SPH_MATCH_EXTENDED2);
		
		if (!$py_flag) {
			$query = "@softname {$query}";
		}
		
		
		$this->sphinx_client['conn']->AddQuery($this->search_key,"infix_sj_soft,prefix_sj_soft");
		
		
		$this->sphinx_client['conn']->AddQuery($query,"infix_sj_soft,prefix_sj_soft");
		
		
		$this->sphinx_client['conn']->ResetFilters();
		
		$this->sphinx_client['conn']->SetMatchMode(SPH_MATCH_EXTENDED2);
		
		$this->sphinx_client['conn']->SetFieldWeights( array("dev_name" => 10, "softname" =>1000, "tags" => 10, "intro" => 1,"softname_pinyin"=>10,"softname_pinyin_no_blank"=>10) );
		
		
		$this->sphinx_client['conn']->AddQuery($query_bak_st);
		
		
		$res = $this->sphinx_client['conn']->RunQueries ();
		
		
		$result_1 = $this->set_search_result_weight($res[0]);
		$result_2 = $this->set_search_result_weight($res[1]);
		$result_3 = $this->set_search_result_weight($res[2]);
		
		
		$end = microtime_float();
		
		if ($error = $this->sphinx_client['conn']->GetLastError()) {
			$msg = date('Y-m-d H:i:s');
			$day = date('Y-m-d');
			$msg .= ": {$_SERVER['REMOTE_ADDR']} {$error} when search [{$this->search_key}]\n";
			file_put_contents("/tmp/search_error_{$day}.log", $msg, FILE_APPEND);
		}
		$spend = $end - $start;
		if ($spend > 0.5) {
			$msg = date('Y-m-d H:i:s');
			$day = date('Y-m-d');
			$msg .= ": {$_SERVER['REMOTE_ADDR']} spend {$spend} when search [{$this->search_key}]\n";
			file_put_contents("/tmp/search_slow_{$day}.log", $msg, FILE_APPEND);
		}
		
		
		//var_dump($result_1['softid_arr']);
		foreach($result_2['softid_arr'] as $k =>$softid)
		{
			$result_1['softid_arr'][$softid] = $softid;
			if(isset($result_2['person_contrl'][$softid])){
				$result_1['person_contrl'][$softid] = $result_2['person_contrl'][$softid];
			
			}
		}
		
		foreach($result_3['softid_arr'] as $k =>$softid)
		{
			$result_1['softid_arr'][$softid] = $softid;
			if(isset($result_3['person_contrl'][$softid])){
				$result_1['person_contrl'][$softid] = $result_3['person_contrl'][$softid];
			
			}
		}
		
		//人工运营 
		$merge_list = $result_1;
		$person_contrl = $this -> person_contrl($this->search_key,$merge_list['person_contrl']);
		$temp = array();		
		$temp = $result_1['softid_arr'];
		if (empty($temp)) {
			$msg = date('Y-m-d H:i:s');
			$day = date('Y-m-d');
			$msg .= ": {$_SERVER['REMOTE_ADDR']} [{$this->search_key}]\n";
			file_put_contents("/tmp/search_empty_{$day}.log", $msg, FILE_APPEND);
		}
		$result['softid_arr'] = $temp;
		$result['person_contrl'] = $person_contrl;
		$result['weight_arr'] = array_merge($result_1['weight_arr'],$result_2['weight_arr'],$result_3['weight_arr']);
		$result['count'] = count($result['softid_arr']);
		
		if($useindex < 1 && count($result['softid_arr'])<1 && !$py_flag){
			
			     $useindex++;
				 
			        $this->search_key = SpGetPinyin(utf82gb($this->search_key));
			          
		               
			    
				
				
			 return $this->get_search_result();      
		}
		
		
		return $result; 
	}
	
	
	
	//初始化客户端
	function init_sphinx_client()
	{  
		    go_require_once( GOPHP_ROOT.'/lib/GoSphinxApi.php' );
			
			$cl = new sphinx_client();
			$cl->SetServer ($this->sphinx_client['server'], $this->sphinx_client['port']);
			$cl->SetConnectTimeout (4); //设置查询的超时时间
			$cl->SetArrayResult ( True ); //以数组形式返回
			$cl->ResetFilters();
			$cl->SetRetries(4);
			//设置字段的相应权重
			#$cl->SetFieldWeights( array("dev_name" => 1, "softname" => 10, "tags" => 1, "intro" => 1,"softname_pinyin"=>1,"softname_pinyin_no_blank"=>1) );
			$cl->SetSelect("*");//返回所有的属性值
			$cl->SetRankingMode (SPH_RANK_BM25); //内部权重匹配算法
			//$cl->SetSortMode (SPH_SORT_EXPR,"@weight*10+total_downloaded+score*10");
			$cl->SetLimits(0, 1000); //最多提取1000条 (server端默认限制1000条)
			$cl->SetIndexWeights(array("prefix_sj_soft" => 10, "infix_sj_soft" => 10, "sj_soft" =>1));//softname的前缀索引, softname的中缀索引,全文索引的权重设置            
			$cl->SetFilter ( "status", array (1)); //status为1
			//$cl->SetFilter ( "hide", array (1)); //hide为1
			//$cl->SetFilter ( "safe", array (0, 1)); //safe小于2
			$this->sphinx_client['conn'] = $cl;
		
	   return $this->sphinx_client;
	}
	function make_cache_key($model_name, $index)
	{
		return md5(json_encode($index)); 
	}
	//人工干预
	function person_contrl($search_key,$person_ctrl_list){
		$strlen = mb_strlen($search_key,'utf-8');
		//query在纯中文字符2个及以上时做处理
		if($strlen < 2){
			return null;
		}
		$cache = GoCache::getCacheAdapter('memcached');
		$md5 = md5($search_key);
		$cache_key = "PERSON_CTRL{$md5}";
		$person_contrl = $cache -> get($cache_key);
		if($person_contrl) return $person_contrl;		
		$result_2 =  $list;
		$temp = array();
		$person_contrl = array();
		if($person_ctrl_list){
			$runindex_arr = array();
			$download_arr = array();
			foreach ($person_ctrl_list as $softid => $val) {
				list($r, $d) = $val;
				$runindex_arr[$softid] = $r;
				$download_arr[$softid] = $d;
				$person_contrl[$softid] = $softid;
			}
			array_multisort($runindex_arr, SORT_DESC,
			$download_arr, SORT_DESC,
			$person_contrl);
		}
		if($person_contrl) {
			$person_contrl = array_flip($person_contrl);
			$cache -> set($cache_key,$person_contrl,600);
		}
		return $person_contrl;
	}
}


