<?php
/*
    软件logic
*/

define('DOWNLOAD_NO_SOFT', -1);
define('DOWNLOAD_IPBANNED', -2);

class GoSoftLogic
{
    public $filter_logic;
	public $cache_key;
	public $cache_timeout;
	
    function __construct($parameter)
    {
        if (isset($parameter['filter_logic']) && is_object($parameter['filter_logic'])) {
            $this->filter_logic = $parameter['filter_logic'];
        }
    }

    function get_soft_maybe_like_sec($softid, $size = 9)
    {
        if ($this->filter_logic) {
            $filter_str = md5(json_encode($this->filter_logic->filter_option));
            $this->cache_key = "suggestsec_{$softid}_{$size}_{$filter_str}";
        } else {
            $this->cache_key = "suggestsec_{$softid}_{$size}";
        }
        $this->cache_timeout = 1800;
        $cache = GoCache::getCacheAdapter('memcached');
        $result = $cache->get($this->cache_key);
        if (!$result) {
            $soft_model = pu_load_model_obj('pu_soft', $softid);
            $maybe_like_softid_arr = $soft_model->get_suggest_id();
            
            $filter[] = $softid;
            $need_order_cache = array();
            
            $candidate_order_id = $order_total_result = array();
            
            for($i=0;$i<$size;$i++) {
                $order_total_result[$i] = 0;
            }
            if ($this->filter_logic) {
                $maybe_like_softid_arr = $this->filter_logic->filter_softid($maybe_like_softid_arr);
            }
            
            $redis = GoCache::getCacheAdapter('redis');
            $result = $redis->gets(array('SUGGEST:CP', 'EXTENT_CANDIDATE_F', 'HOT_LIST1:100', 'HOT_LIST2:100', 'SUGGEST:TREND'));
            
            $cp_suggest = $result['SUGGEST:CP'];
            if ($this->filter_logic) {
                $order_id = $this->filter_logic->filter_softid($cp_suggest, false);
            }
            $order_id = array_diff($order_id, $filter);
            
            $cp_suggest_flip = array();
            foreach($order_id as $val) {
                $rank = $cp_suggest[$val];
                $rank = $rank - 1;
                if (!isset($cp_suggest_flip[$rank])) $cp_suggest_flip[$rank] = array();
                $cp_suggest_flip[$rank][] = $val;
            }
            foreach ($cp_suggest_flip as $rank => $val) {
                $k = array_rand($val);
                //运营指定
                $order_total_result[$rank] = $val[$k];
                $filter[] = $val[$k];
            }
            $key = array('suggest_config');
            $model = load_model('pu_config');
            $configs = $model->getConfig($key);
            $suggest_config = json_decode($configs['suggest_config'][1], true);
            $candidate_pos = !empty($suggest_config['candidate']) ? $suggest_config['candidate'] - 1 : 4 ;
            $top_pos = !empty($suggest_config['top']) ? $suggest_config['top'] - 1 : 5 ;
            $trend_pos = !empty($suggest_config['trend']) ? $suggest_config['trend'] - 1 : 6 ;
            
            //备选库
            $candidate_suggest = $result['EXTENT_CANDIDATE_F'];
            if ($this->filter_logic) {
                $candidate_suggest = $this->filter_logic->filter_softid($candidate_suggest, false);
            }
            $candidate_suggest = array_diff($candidate_suggest, $filter);
            $k = array_rand($candidate_suggest);
            $filter[] = $candidate_suggest[$k];
            $order_total_result[$candidate_pos] = $candidate_suggest[$k];
            unset($candidate_suggest[$k]);
            
            //最热的位置
            $hot_suggest = $result['HOT_LIST1:100'] + $result['HOT_LIST2:100'];
            if ($this->filter_logic) {
                $hot_suggest = $this->filter_logic->filter_softid($hot_suggest);
            }
            $hot_suggest = array_diff($hot_suggest, $filter);
            $k = array_rand($hot_suggest);
            $filter[] = $hot_suggest[$k];
            $order_total_result[$top_pos] = $hot_suggest[$k];
            
            //趋势的位置
            $trend_suggest = $result['SUGGEST:TREND'];
            if ($this->filter_logic) {
                $trend_suggest = $this->filter_logic->filter_softid($trend_suggest);
            }
            $trend_suggest = array_diff($trend_suggest, $filter);

            $k = array_rand($trend_suggest);
            $filter[] = $trend_suggest[$k];
            $order_total_result[$trend_pos] = $trend_suggest[$k];

            $maybe_like_softid_arr = array_diff($maybe_like_softid_arr, $filter);
            foreach($order_total_result as $k=>$v) {
                if (empty($order_total_result[$k]) && !empty($maybe_like_softid_arr)) {
                    $mk = array_rand($maybe_like_softid_arr);
                    $order_total_result[$k] = $maybe_like_softid_arr[$mk];
                    $filter[] = $maybe_like_softid_arr[$mk];
                    unset($maybe_like_softid_arr[$mk]);
                }
            }
            
            $candidate_suggest = array_diff($candidate_suggest, $filter);
            foreach($order_total_result as $k=>$v) {
                if (empty($order_total_result[$k]) && !empty($candidate_suggest)) {
                    $mk = array_rand($candidate_suggest);
                    $order_total_result[$k] = $candidate_suggest[$mk];
                    unset($candidate_suggest[$mk]);
                }
            }
            $result = array_values($order_total_result) ;
            $cache->set($this->cache_key, $result, $this->cache_timeout);
            $cache->set($this->cache_key. '_time', time(), $this->cache_timeout);
        }
        return $result;
    }

	
    function get_soft_maybe_like($softid, $size = 9)
    {
		$softids = $this->get_soft_maybe_like_sec($softid, $size);
		$option = array(
			'ignore_file' => 1,
			'ignore_thumb' => 1,
		);
		$result = $this->get_soft_all_data_by_softid($softids, $option);
        return $result;
    }

    function get_soft_all_data($soft_model, $option = array())
    {
        $soft_info_arr = array();
        if ( is_array($soft_model) ) {
            $soft_model_arr = $soft_model;
            $file_id_arr =  array();
            $thumb_id_arr = array();
            foreach ($soft_model_arr as $obj) {
                $file_id_arr = array_merge($file_id_arr, $obj->data_info['file_id']);
                $thumb_id_arr  = array_merge($thumb_id_arr, $obj->data_info['thumb_id']);
                $soft_info_arr[$obj->softid] = $obj->data_info;
				$soft_info_arr[$obj->softid]['num_format_total_downloaded'] = num_format($obj->data_info['total_downloaded'],2);
				$soft_info_arr[$obj->softid]['num_format_downloaded'] = num_format($obj->data_info['downloaded'],2);
            }
			
        } else {
            $file_id_arr = $soft_model->data_info['file_id'];
            $thumb_id_arr = $soft_model->data_info['thumb_id'];
            $soft_info_arr[$soft_model->softid] = $soft_model->data_info;
			$soft_info_arr[$soft_model->softid]['num_format_total_downloaded'] = num_format($soft_model->data_info['total_downloaded'],2);
			$soft_info_arr[$soft_model->softid]['num_format_downloaded'] = num_format($soft_model->data_info['downloaded'],2);
        }
	
        if ($file_id_arr && empty($option['ignore_file'])) {
            $file_data_info = pu_load_model_data('pu_soft_file', $file_id_arr);
            foreach ($file_data_info as $k => $d) {
                $soft_info_arr[$d['softid']]['file'][$k]  = $d;
               // if ( !isset($soft_info_arr[$d['softid']]['iconurl']) ) {
                    $soft_info_arr[$d['softid']]['iconurl'] = $d['iconurl'];
                    $soft_info_arr[$d['softid']]['url'] = $d['url'];
                    $soft_info_arr[$d['softid']]['filesize'] = $d['filesize'];
                    $soft_info_arr[$d['softid']]['apk_name'] = $d['apk_name'];
                    $soft_info_arr[$d['softid']]['qrcode_url'] = $d['qrcode_url'];
                    $soft_info_arr[$d['softid']]['package_status'] = $d['package_status'];
                    $soft_info_arr[$d['softid']]['sign'] = $d['sign'];
                    $tags = explode(" ",str_replace(",", " ", $soft_info_arr[$d['softid']]['tags']));
                    $tags_arr = array_slice($tags,0,5);
                    $soft_info_arr[$d['softid']]['tags_arr'] = implode(" ",$tags_arr);
                //}
            }
        }
        if ($thumb_id_arr && empty($option['ignore_thumb'])) {
            $thumb_data_info = pu_load_model_data('pu_soft_thumb', $thumb_id_arr);
            foreach ($thumb_data_info as $k => $d) {
                $soft_info_arr[$d['softid']]['thumb_arr'][$k]  = $d;
            }
        }

        if ( is_array($soft_model) ) {
            return $soft_info_arr;
        } else {
            return current($soft_info_arr);
        }
    }

    function get_soft_all_data_by_softid($softid, $option = array())
    {
        $soft_model = pu_load_model_obj('pu_soft', $softid);
        $soft_data = $this->get_soft_all_data($soft_model, $option);
        return $soft_data;
    }

    function get_soft_all_data_by_package($package, $option = array())
    {
        $soft_model = pu_load_model_data('pu_soft')->get_model_by_package($package, $this->filter_logic);
        $soft_data = $this->get_soft_all_data($soft_model, $option);
        return $soft_data;
    }

    function get_download_url($softid)
    {
        if ( !($soft_data = pu_load_model_data('pu_soft', $softid)) ||
             !($soft_file = pu_load_model_data('pu_soft_file', $soft_data['file_id']))
        ) {
            return DOWNLOAD_NO_SOFT;
        }

        $ip = onlineip();
        $threshold = 50;
        $ipbanned_obj = pu_load_model_obj('pu_ipbanned', $ip);
        if ($ipbanned_obj->data_info[$softid] > $threshold) {
            return DOWNLOAD_IPBANNED;
        } else {
            $ipbanned_obj->data_info[$softid]++;
            $ipbanned_obj->save_data_info();
        }

        $soft_file_data = current($soft_file);
        $down_url = $soft_file_data['url'];
        $package = $soft_data['package'];
        $down_url= load_config('goapk_apk_host') .$down_url;
        return array($down_url, $package);
    }

}
