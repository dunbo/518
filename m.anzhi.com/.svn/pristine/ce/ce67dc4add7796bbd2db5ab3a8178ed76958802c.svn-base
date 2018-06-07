<?php
require_once 'Base.class.php';

class User extends Base{
    protected $uid;

    protected $aid;

    protected $a_url;

    protected $reutrn_url;

    protected $pay_url;


    public function __construct(){
        parent::__construct();
        $this->aid = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
        global $prefix;

        $this->pay_url = 'http://h5pay.anzhi.com/h5pay/market/recharge/method';
        $build_query = http_build_query($_GET);
        if($this->configs['is_test'] == 1){
            $this->pay_url = 'http://dev.pay.anzhi.com:9051/h5pay/market/recharge/method';
            $h_str = 'dev.';
            $this->tplObj -> out['is_test'] = 1;
        }
        $center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
        $this->a_url = $this->configs['activity_url']."lottery/{$prefix}/index.php?".$build_query;
        $this->reutrn_url = $this->configs['activity_url']."lottery/{$prefix}/myprize.php?aid=".$this->aid."&sid=".$_SESSION['UCENTER_SID'];
        $login_url = $center_url.$this->configs['activity_url']."lottery/{$prefix}/index.php?".$build_query;
        $this->tplObj -> out['login_url'] = $login_url;	

        user_loging_new();
        if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
            if($_GET['is_register'] == 1){
                //注册成功日志
                $log_data = array(
                    'uid' => $_SESSION['USER_UID'],
                    'imsi' => $_SESSION['USER_IMSI'],
                    'device_id' => $_SESSION['DEVICEID'],
                    'activity_id' => $this->aid,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'sid' => $sid,
                    'time' => time(),
                    'key' => 'register'
                );
                permanentlog('activity_'.$this->aid.'.log', json_encode($log_data));
            }else{	
                //登录日志
                $log_data = array(
                    'uid' => $_SESSION['USER_UID'],
                    'imsi' => $_SESSION['USER_IMSI'],
                    'device_id' => $_SESSION['DEVICEID'],
                    'activity_id' => $this->aid,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'sid' => $sid,
                    'time' => time(),
                    'key' => 'login'
                );
                permanentlog('activity_'.$this->aid.'.log', json_encode($log_data));
            }
            $this->tplObj -> out['username'] = $_SESSION['USER_NAME'];
            $this->tplObj -> out['is_login'] = 1;
            $this->tplObj -> out['uid'] = $_SESSION['USER_UID'];

        }else{//未登录
            if(array_pop(explode('/',$_SERVER['DOCUMENT_URI']))!='index.php'){
                header('Location:'.$this->a_url.'');exit(0);
            }
            $this->tplObj -> out['is_login'] = 2;
        }
    }

    //获取充值跳转地址
    public function get_cz_url(){
        //$appkey = '1517468107oN85pyrajO1IS4dgg6hJ';
        //$app_secret = "JVcEyysUVSagC0413YkrA697";
        $appkey = "151799446743NwIAa0Vgs2gw0dhgod";
        $app_secret = "a00on69IMfEUl1DCxl4jV92H";

        $query = array(
            'appkey' => $appkey,
            'time' => time() . '000',
            'open_id' => $_SESSION['USER_UID'],
            'sid' => $_SESSION['UCENTER_SID'],
            'return_url' => $this->reutrn_url,
        );
        $check_data = $query;
        $check_data['app_secret'] = $app_secret;
        $sign = $this->build_sign($check_data);
        $query['sign'] = $sign;
        $query_string = http_build_query($query);
        return $jump_url = $this->pay_url . '?' . $query_string;
    }

    private function build_sign($data) {
        ksort($data);
        $query = array();
        foreach ($data as $k => $v) {
            $query[] = "$k=$v";
        }
        $query_string = implode('&', $query);
        $sign = strtolower(md5($query_string));
        return $sign;
    }
}
