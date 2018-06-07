<?php
ini_set("display_errors", 1);
//error_reporting(E_ALL);
class reportClickGDT
{
    public $params;
    public function __construct($param)
    {
        $this->params = $param;
        $this->appid = 1101166194;
        $this->cid = 'GDT';
        $this->expire_time = 86400 * 7;
        $this->uid = 5387535;
    }
    public function getData()
    {
        $muid = $this->params['muid'];
        $click_time = (int)$this->params['click_time'];
        $click_id = $this->params['click_id'];
        $appid = $this->params['appid'];
        $advertiser_id = $this->params['advertiser_id'];
        $app_type = $this->params['app_type'];
        $submit_tm = time();
        if ($this->appid != $appid) {
            return '{"ret":-1,"msg":"appid不一致"}';
        }
        if ($app_type != 'android') {
            return '{"ret":-2,"msg":"app类型不一致"}';
        }
        if ($this->uid != $advertiser_id) {
            return '{"ret":-3,"msg":"uid不一致"}';
        }
        $model = new GoModel();
        $redis_config = load_config('ad_channel/redis');
        $redis = new GoRedisCacheAdapter($redis_config);
        $key = "AD_CLICK:" . $this->cid . ":" . $muid;
        $cache_data = array(
            'click_id' => $click_id,
        );
        $ret = $redis->set($key, $cache_data, $this->expire_time);
        if (!$ret) {
            return '{"ret":-4,"msg":"数据记录异常"}';
        }
        $data = array(
            'muid' => $muid,
            'click_time' => $click_time,
            'click_id' => $click_id,
            'appid' => $appid,
            'advertiser_id' => $advertiser_id,
            'app_type' => $app_type,
            'submit_tm' => $submit_tm,
        );
        $db_data = $data;
        $db_data['__user_table'] = 'gdt_click_log';
        $ret = $model->insert($db_data, 'ad_channel');
        if (!$ret) {
            return '{"ret":-4,"msg":"数据记录异常"}';
        }
        permanentlog('gdt_click.log', json_encode($data));
        $return  = array(
            'ret' => 0
        );
        return json_encode($return);
    }
}

?>
