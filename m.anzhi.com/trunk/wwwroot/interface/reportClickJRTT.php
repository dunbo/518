<?php
ini_set("display_errors", 1);
//error_reporting(E_ALL);
class reportClickJRTT
{
    public $params;
    public function __construct($param)
    {
        $this->params = $param;
        $this->cid = 'JRTT';
        $this->expire_time = 86400 * 7;
    }
    public function getData()
    {
        $aid = $this->params['aid'];
        $cid = $this->params['cid'];
        $csite = (int)$this->params['csite'];
        $ctype = (int)$this->params['ctype'];
        $mac = $this->params['mac'];
        $ua = $this->params['ua'];
        $androidid = $this->params['androidid'];
        $imei = $this->params['imei'];
        $os = (int)$this->params['os'];
        $ip = $this->params['ip'];
        $ts = (int)$this->params['ts'];
        $convert_id = $this->params['convert_id'];
        $callback_url = $this->params['callback_url'];
        $submit_tm = time();
        if ($os != 0) {
            return '{"status":-1,"msg":"参数异常"}';
        }
        $model = new GoModel();
        $redis_config = load_config('ad_channel/redis');
        $redis = new GoRedisCacheAdapter($redis_config);
        $key = "AD_CLICK:" . $this->cid . ":" . $imei;
        $cache_data = array(
            "click_id" => $convert_id,
            "callback_url" => $callback_url,
        );
        $ret = $redis->set($key, $cache_data, $this->expire_time);
        if (!$ret) {
            return '{"status":-4,"msg":"数据记录异常"}';
        }
        $data = array(
            'aid' => $aid,
            'cid' => $cid,
            'csite' => $csite,
            'ctype' => $ctype,
            'mac' => $mac,
            'ua' => $ua,
            'androidid' => $androidid,
            'imei' => $imei,
            'os' => $os,
            'ip' => $ip,
            'ts' => $ts,
            'convert_id' => $convert_id,
            'callback_url' => $callback_url,
            'submit_tm' => $submit_tm,
        );
        $db_data = $data;
        $db_data['__user_table'] = 'jrtt_click_log';
        $ret = $model->insert($db_data, 'ad_channel');
        if (!$ret) {
            return '{"status":-4,"msg":"数据记录异常"}';
        }
        permanentlog('gdt_click.log', json_encode($data));
        $return  = array(
            'status' => 0
        );
        return json_encode($return);
    }
}

?>
