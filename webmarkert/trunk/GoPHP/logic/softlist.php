<?php
/*
    软件列表logic
*/
class GoSoftlistLogic
{
    function __construct($parameter)
    {
        $this->soft_logic = pu_load_logic('soft');
        if ($parameter['filter_logic'] && is_object($parameter['filter_logic'])) {
            $this->filter_logic = $parameter['filter_logic'];
        }
    }

    function get_type_list($offset = 0, $limit = 9, $category_id, $order_by)
    {
        $option = array(
            'catalogid' => $category_id,
            'order_by' => $order_by,
        );
        $softid_arr = pu_load_model_data('pu_type', $option);
        if ($this->filter_logic) {
            $softid_arr = $this->filter_logic->filter_softid($softid_arr, false, 'top_'. $category_id. '_'. $order_by);
        }
        $count = count($softid_arr);
        $softid_arr  = array_slice($softid_arr, $offset, $limit);
        $list = $this->soft_logic->get_soft_all_data_by_softid($softid_arr);
        return array($list, $count);
    }

    function get_new($category_id, $offset = 0, $limit = 9)
    {
        $option = array(
            'type' => 'subnew',
            'catalogid' => $category_id,
        );
        $new_id = pu_load_model_data('pu_new', $option);
        global $filter_logic;
        if ($this->filter_logic) {
            $new_id = $this->filter_logic->filter_softid($new_id, false, 'top_'. $category_id. '_new');
        }
        $count = count($new_id);
        $new_id = array_slice($new_id, $offset, $limit);
        $new = $this->soft_logic->get_soft_all_data_by_softid($new_id);
        return array($new, $count);
    }
    function get_new_all($offset=0,$limit=9) {
        $option = array(
            'type' => 'new',
        );
        $new_id = pu_load_model_data('pu_new', $option);
        global $filter_logic;
        if ($this->filter_logic) {
            $new_id = $this->filter_logic->filter_softid($new_id, false, 'top_new');
        }
        $count = count($new_id);
        $new_id = array_slice($new_id, $offset, $limit);
        $new = $this->soft_logic->get_soft_all_data_by_softid($new_id);
        return array($new, $count);
    }
    function get_ranking($offset = 0, $limit =10)
    {
        $type_arr = array('hot_1d', 'hot_7d', 'hot_30d');
		$type_order_cache_key = array('top_1d', 'top_7d', 'top_30d');
        $data_arr = array();
        foreach ($type_arr as $k => $type) {
            $option = array( 'type' => $type );
            $ranking_id = pu_load_model_data('pu_hot', $option);
            if ($this->filter_logic) {
                $ranking_id = $this->filter_logic->filter_softid($ranking_id, false, $type_order_cache_key[$k] );
            }
            if ($ranking_id) {
                $ranking_id = array_slice($ranking_id, $offset, $limit);
                $data_arr[] = $this->soft_logic->get_soft_all_data_by_softid($ranking_id);
            } else {
                $data_arr[] = array();
            }
        }
        return $data_arr;
    }
    function get_ranking_xd($hot_xd,$offset = 0, $limit =10)
    {
        $data_arr = array();
        $option = array( 'type' => $hot_xd );
        $ranking_id = pu_load_model_data('pu_hot', $option);
        if ($this->filter_logic) {
            $ranking_id = $this->filter_logic->filter_softid($ranking_id, false, 'top_'.$hot_xd.'d');
        }
        $count = count($ranking_id);
        if ($ranking_id) {
            $ranking_id = array_slice($ranking_id, $offset, $limit);
            $data_arr= $this->soft_logic->get_soft_all_data_by_softid($ranking_id);
        } else {
            $data_arr = array();
        }
        return array($data_arr,$count);
    }
    function get_hot($category_id, $offset = 0, $limit = 9)
    {
        $option = array (
            'type' => 'subrank',
            'catalogid' => $category_id,
        );
        $hot_id = pu_load_model_data('pu_hot', $option);
        if ($this->filter_logic) {
            $hot_id = $this->filter_logic->filter_softid($hot_id, false, 'top_'. $category_id. '_hot');
        }
        $count = count($hot_id);
        $hot_id = array_slice($hot_id, $offset, $limit);
        $hot = $this->soft_logic->get_soft_all_data_by_softid($hot_id);
        return array($hot, $count);
    }

    function get_recommend($offset = 0, $limit = 20,$filter_soft='')
    {
        $option = array(
            'type' => 0,
        );
        $suggest_id = pu_load_model_data('pu_suggest', $option);
        if(!empty($filter_soft)){
            if(in_array($filter_soft,$suggest_id)){
                 $suggest_id = array_flip($suggest_id);
                 unset($suggest_id[$filter_soft]);
                 $suggest_id = array_flip($suggest_id);
            }
        }
        if ($this->filter_logic) {
            $suggest_id = $this->filter_logic->filter_softid($suggest_id);
        }
        $suggest_id = array_slice($suggest_id, $offset, $limit);
        $soft_recommend_arr = $this->soft_logic->get_soft_all_data_by_softid($suggest_id);
        return $soft_recommend_arr;
    }

    function get_recommend_sec($offset = 0, $limit = 20,$filter_soft='')
    {
        $option = array(
            'type' => 0,
        );
        $suggest_id = pu_load_model_data('pu_suggest', $option);
        if(!empty($filter_soft)){
            if(in_array($filter_soft,$suggest_id)){
                 $suggest_id = array_flip($suggest_id);
                 unset($suggest_id[$filter_soft]);
                 $suggest_id = array_flip($suggest_id);
            }
        }
        if ($this->filter_logic) {
            $suggest_id = $this->filter_logic->filter_softid($suggest_id);
        }
        $count = count($suggest_id);
        $suggest_id = array_slice($suggest_id, $offset, $limit);
        $soft_recommend_arr = $this->soft_logic->get_soft_all_data_by_softid($suggest_id);
        return array($soft_recommend_arr,$count);
    }
	function get_extent_softlist($fid,$offset = 0, $limit = 20){
/* 		ini_set("display_errors", true);
		error_reporting(E_ALL); */
        $subject_obj = pu_load_model_data('pu_subject');
		$softid_arr = $subject_obj -> get_extent_feature_by_id($fid);
		foreach($softid_arr as $info ){
			$id_arr[] = $info['softid'];
		}
        if ($this->filter_logic) {
            $softid_arr = $this->filter_logic->filter_softid($id_arr);
        }
        $count = count($softid_arr);
        $softid_arr = array_slice($softid_arr, $offset, $limit);
        $feature_soft = $this->soft_logic->get_soft_all_data_by_softid($softid_arr);
        return array($feature_soft, $count);
	}
}
