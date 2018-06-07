<?php
/*
    过滤规则model
    index格式 : array('firmware' => 'xxx', 'device' => 'xxx', 'channel' => 'xxx', 'authorized' => 'xxx', 'abi' => 'xxx')
    data_info格式 : array('firmware_cache_key' => array(softid1, softid2,....)
                          'device_cache_key' => array(softid1, softid2,....)
                          ....
                          'all' => array(softid1, softid2, ....)
                         )
*/
class GoPu_filterModel extends GoPu_model
{
    public $option;
    public $index_name = 'option';
	public $filter_hash_key = '';
	
    function __construct($index = array())
    {
        parent::__construct(__CLASS__, $index);
        if ($this->option = $index) {
			unset($index['order_key']);
			$this->filter_hash_key = md5(json_encode($index));
        }
    }
	public function initFilterCache($filter = array())
	{
		$f = load_model('pu_filtercache');
		$filterCache = $f->getFilterCache($this->option, $this->makeKey($filter));
		$this->data_info['multipackage'] = $filterCache['multi_package'];
		$this->data_info['exclude'][$this->filter_hash_key] = $filterCache['exclude'];
		$this->data_info['include'][$this->filter_hash_key] = $filterCache['include'];
		return $filterCache;
	}
    //重写了父类的get_real_data_info, 从预处理脚本中取得现成的cache
    function get_real_data_info($index)
    {
		$this->initFilterCache($this->option);
        return $this->data_info;
    }

    public function make_cache_key($model_name, $index)
    {
		unset($index['order_key']);
        return $model_name. '_'. self::makeKey($index);
    }
	
    public function makeKey($filter = array()) {
        $key = '';
        if (isset($filter['pid'])) {
            $pid = $filter['pid'];
            $key .= "PID${pid}";
        }
        if (isset($filter['device'])) {
            $device = $filter['device'];
            $key .= "D${device}";
        }
        if (isset($filter['firmware'])) {
            $firmware = $filter['firmware'];
            $key .= "F${firmware}";
        }
        if (isset($filter['channel'])) {
            $channel = $filter['channel'];
            $key .= "C${channel}";
        }
        if (isset($filter['authorized'])) {
            $authorized = $filter['authorized'];
            $key .= "A${authorized}";
        }
		if (isset($filter['safe'])) {
            $safe = $filter['safe'];
            $key .= "S${safe}";
        }
		if (isset($filter['abi'])) {
            $abi = $filter['abi'];
            $key .= "ABI${abi}";
        }
        if (isset($filter['operator'])) {
            $operator = $filter['operator'];
            $key .= "OP${operator}";
        }
		if (isset($filter['app2sd'])){
			$key .= "APP2SD";
		}
		if(isset($filter['model_oid'])){
			$oid = $filter['model_oid'];
			$key .= "OID${oid}";
		}
		
		if (isset($filter['channel_soft_cid'])) {
            $vr = $filter['channel_soft_cid'];
            $key .= "CSC${vr}";
        }
        return $key;
    }
}
