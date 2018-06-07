<?php
/*
    软件专题model
    index格式 : $feature_id
    data_info格式 :  sj_soft_file数据库表下的字段 + subject下对应的package ;
    不基于单个专题进行提取，而是提取出所有专题以后，再按feature_id提取单条数据
*/
class GoPu_subjectModel extends GoPu_model
{
    public $table = 'sj_feature';
    public $cache_timeout = 0;

    public $cache_timeout_all_subject = 300;
    public $index_name = 'feature_id';
    public $cache_key_all_subject = 'PU_ALL_SUBJECT'; //存储所有的专题
    static public $all_subject = '';
	public $cache_key_extent_feature = 'PU_EXTENT_ID'; //存储专题专区
	public $cache_key_feature_tab = 'FEATURE_TAB';
    public $package;
    public $feature_id;
    public $name;

    function __construct($index = '')
    {
        parent::__construct(__CLASS__, $index);
        $this->get_all_subject();
    }

    function save_field_filter()
    {
        $data_info = $this->data_info;
        unset($data_info['package']);
        return $data_info;
    }

    //重载父类方法  保存all_subject数据，并set进缓存
    function set_cache_data_info()
    {
        self::$all_subject[$this->feature_id] = $this->data_info;
        return $this->get_cache()->set($this->cache_key_all_subject, self::$all_subject, $this->cache_timeout_all_subject);
    }

    function get_data_info()
    {
        if ($this->data_info) { return $this->data_info; }
        $this->data_info = self::$all_subject[$this->index];
        return $this->data_info;
    }

    function get_model_by_name($name)
    {
        foreach (self::$all_subject as $k => $v) {
           if ($v['name'] == $name) {
               $data_info = $v;
               break;
           }
        }
        return parent::getInstance(__CLASS__, $data_info['feature_id'], $data_info);
    }

    function get_data_info_arr($index_arr, $model_name)
    {
        $data_info_arr = array();
        $c = count($index_arr);
        foreach (self::$all_subject as $k => $v) {
            if ($c <= count($data_info_arr)) {
                break;
            }
            if (in_array($k, $index_arr)) {
                $data_info_arr[$k] = $v;
            }
        }
        return $data_info_arr;
    }

    function get_all_subject()
    {
        if (!self::$all_subject) { self::$all_subject = $this->get_cache()->get($this->cache_key_all_subject); }
        if (self::$all_subject) { return self::$all_subject; }
		$now = time();
        $option = array(
            'table' => 'sj_feature AS A',
            'where' => array(
                'A.status' => 1,
                'B.status' => 1,
                'B.start_tm' => array('exp', '<='. $now),
                'B.end_tm' => array('exp', '>='. $now),
				'A.pid' => 1, //网页默认显示安智市场
				//'A.channel_id' => array('exp', 'is null')
            ),
            'join' => array(
                'sj_feature_soft AS B' => array(
                    'on' => array('A.feature_id', 'B.feature_id')
                )
            ),
            'order' => 'A.orderid ASC,  B.rank ASC',
        );
        $all_subject = $this->findAll($option);
        $new_all_subject = array();
        foreach ($all_subject as $k => $v) {
			// 特殊需求 在webmarket添加 天翼专区 feature_id : 87
			if ((!empty($v['channel_id']) && $v['feature_id'] !=87) || !empty($v['match_abi'])) {
				continue;
			}
			
            if (!isset($new_all_subject[$v['feature_id']])) {
                $new_all_subject[$v['feature_id']] = $v;
                $new_all_subject[$v['feature_id']]['package'] = array();
            }
			$new_all_subject[$v['feature_id']]['package'][] = $v['package'];
        }
        self::$all_subject = $new_all_subject;
        $this->get_cache()->set($this->cache_key_all_subject, self::$all_subject, $this->cache_timeout_all_subject);
        return self::$all_subject;
    }

    function get_feature_pic($limit) {
         $option = array(
            'table' => 'sj_webmarket_feature_picture AS A',
            'where' => array(
                'B.status' => 1,
            ),
            'join' => array(
                'sj_feature AS B' => array(
                    'on' => array('A.feature_id', 'B.feature_id')
                )
            ),
            'limit' => $limit,
             'field' => array('B.feature_id AS feature_id,B.webicon as webicon,B.name AS name')
        );
         $feature_id_pics = $this -> findAll($option);
         return $feature_id_pics;
    }
    function get_feature_info($feature_id) {
        $option = array(
            'table' => 'sj_webmarket_feature_text',
            'where' => array(
                'status' => 1,
                'feature_id' =>intval($feature_id),
            ),
            'limit' => 1,
        );
        $feature_info = $this -> findAll($option);
        return $feature_info;
    }
	function get_extent_feature_by_id($extent_id){
		$now = time();
	    $option = array(
            'table' => 'sj_extent_soft AS A',
            'where' => array(
                'A.status' => 1,
                'A.start_at' => array('exp','<='.$now),
				'A.end_at' => array('exp','>'.$now),
				'A.extent_id' => $extent_id,
				'B.status' => 1,
				'B.hide' => 1,
            ),
            'join' => array(
                'sj_soft AS B' => array(
                    'on' => array('A.package','B.package'),
                )
            ),
			'field' => 'B.softid as softid',
        );
		//$cache_time = 300;
        $feature_info = $this -> findAll($option);
        return $feature_info;
	}
	//获取专题选项卡的描述信息
	function get_feature_tab_info($limit=3){
		$option = array(
			'table' => 'sj_market_feature_tab',
			'where' => array(
				'status' => 1,
			),
			'order' => 'rank asc',
			'limit' => $limit,
		);
		$feature_tabs = $this -> findAll($option);	
		$list = array();
		if(count($feature_tabs) == $limit){
			foreach($feature_tabs as $info){
				$list[$info['tab_name']] = array(
					'title' => $info['tab_desc'],
				);
			}
		}
		return $list;
 	}
}
