<?php
require_once 'Base.class.php';

class Activity extends Base{
    public $time;
    public $uid;
    public $puid;
    public $imsi;
    public $prefix;
    public $today;
    public $aid;
    public $sid;
    public $stop;
    public $activity_host;

    public function __construct(){
        parent::__construct();
        global $prefix;

        if($this->configs['is_test'] == 1 ) {
            $this->time  = $this->get_now_time();
        }else {
            $this->time  = time();
        }
        $this->sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
        //var_dump($this->sid);
        session_begin($this->sid);
        //var_dump($_SERVER['REMOTE_ADDR'],$_SESSION['ip']);
/*        if($_SERVER['REMOTE_ADDR']!=$_SESSION['ip']){
            session_destroy ();
        }
*/


        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){
                $this->tplObj -> display('lottery/vip/weixin.html');
                exit(0);
                /*
            if($type==1){
                $this->tplObj -> display('lottery/vip/weixin.html');
                exit(0);
            }else{
                $tplObj -> out['is_weixin'] = 1;
            }*/
        }

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
                $this->tplObj -> display('lottery/red_ffl/share_ios.html');
                exit(0);
                /*
            if($type==2){
                $tplObj -> display('lottery/red_ffl/share_ios.html');
                exit(0);
            }else{
                $tplObj -> out['is_ios'] = 1;
            }
                 */
        }
        
        $this->uid = $_SESSION['USER_UID'];
        $this->puid = $_SESSION['USER_ID'];

        if($_SESSION['USER_IMSI']){
            $this->imsi = $_SESSION['USER_IMSI'];
        }

        if(!$this->imsi || $this->imsi == '000000000000000'){
            $this->imsi = '';
        }

        $this->prefix = $prefix;
        $this->today = date('Y-m-d',$time);
        $this->aid = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
        //ctype_digit  检查时候是只包含数字字符的字符串（0-9）//todo
        
        if(!ctype_digit($this->aid)){
            exit;
        }

        $this->activity_host = $this->configs['activity_url'];
        $this->stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];
        /*
        if($this->stop != 1 && !$_GET['ap_id']){//todo
            $activity_list = activity_is_stop($this->aid);
            if(!$activity_list){
                $url = $activity_host."/lottery/{$this->prefix}/index.php?stop=1&aid=".$this->aid."&sid=".$sid;
                header("Location: {$url}");
                exit;
            }
        }
         */
        $this->tplObj -> out['is_market'] = $_SESSION['product'];
        $this->tplObj -> out['is_share'] = $_GET['is_share'];
        $this->tplObj -> out['new_static_url'] = $this->configs['new_static_url'];
        $this->tplObj -> out['prefix'] = $this->prefix;
        $this->tplObj -> out['sid'] = $this->sid;
        $this->tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
        $this->tplObj -> out['aid'] = $this->aid;
    }

    public function get_activity_start_tm(){
        $activity_start_tm_key = $this->prefix.":".$this->aid.":activity_start_tm";
        $activity_start_tm = $this->redis->get($activity_start_tm_key);
        if($activity_start_tm == null){	
            $option = array(
                'where' => array(
                    'id' => $this->aid,
                ),
                'table' => 'sj_activity',
                'field' => 'start_tm,end_tm,activation_time,cover_user_type,activation_date_start,activation_date_end',
            );
            $activity = $this->model->findOne($option);	
            $activity_start_tm = $activity['start_tm'];
            $this->redis->set($activity_start_tm_key,$activity_start_tm,86400);		
        }
        return $activity_start_tm;
    }

    function get_now_time(){
        $option = array(
            'where' => array(
                'status'  => 1,
                'conf_id' => 294
            ),
            'table' => 'pu_config',
            'field' => 'configcontent',
        );
        $list = $this->model->findOne($option);
        return strtotime($list['configcontent']);
    }

    function index_log(){
        $log_data = array(
            "imsi" => $_SESSION['USER_IMSI'],
            "device_id" => $_SESSION['DEVICEID'],
            "activity_id" => $this->aid,
            "ip" => $_SERVER['REMOTE_ADDR'],
            "sid" => $this->sid,
            "time" => $this->time,
            "user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
            'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
            'key' => 'show_homepage'
        );
        permanentlog('activity_'.$this->aid.'.log', json_encode($log_data));	
    }

    function str_replace_cn($str, $start, $enlengthd ){
         if(preg_match("/[\x7f-\xff]/", $str)){
            if($this->is_utf8($str)){
                return substr_replace($str,'***',$start*3, -1*3);
            }else{
                return substr_replace($str,'***',$start*2, -1*2);
            }
         }else{
            return substr_replace($str,'***',$start*2, $enlengthd);
         }
    }

    function is_utf8($word){   
     if(preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$word) == true) {   
      return true;   
     }else {   
      return false;   
     }   
} 
}
