<?php
ini_set("display_errors", 1);
error_reporting(1);
class commonDownload{
    public $param    = array();
    public $apkHost  = '';
    public $package = '';
    public function __construct($param){
        $this->params = $param;

    }
    public function getData(){
        //$this->checkIp();
        $softId = $this->params['softid'];
        $channel = $this->params['channel'];
        $channelId = $_SESSION['API_CID'];
        if($softId && $channelId && $channel){
            $softObj = load_model('softlist');
            $url = $this->getDownloadUrl($softObj);
            if($url){
                $this->addLog();
                $url = $this->apkHost.$url;
                $this->toLocation($url);
            }else{
                $this->error();
            }
        }else{
            $this->error();
        }
    }
    function getDownloadUrl(&$obj){
        $option = array(
            'table' => 'sdk_channel_game',
            'where' => array(
                'softid' => $this->params['softid'],
                'channel_id' => $_SESSION['API_CID']
            ),
            'field'=>'url,softid,package,name',
            'cache_time'=>3600,
        );
        $res = $obj->findOne($option);
        if($res){
            $this->package =$res['package'];
            $this->apkHost = 'http://g.apk.anzhi.com';
            return $res['url'];
        }else{
            return false;
        }
    }
    function toLocation($url) {
        header("content-type:text/html; charset=utf-8");
        header("Location: {$url}");
        exit;
    }
    function error(){
        header("HTTP/1.1 404 Not Found");
        exit;
    }
    function addLog(){
        $h = date("H");
        $ip = onlineip();
        permanentlog('parter_'.$h.'.json', json_encode(array(
            'softid' => $this->params['softid'],
            'package' => $this->package,
            'channel' => $this->params['channel'],
            'ip' => $ip,
            'submit_tm' => time() ,
            'type'=>'sdk'
        )));
        permanentlog('install_log_'.$h.'.json', json_encode(array(
            'softid' => $this->params['softid'],
            'userid' => GO_UID_DEFAULT,
            'action' => ACTION_PARTER_DOWNLOAD,
            'channel' => $this->params['channel'],
            'submit_tm' => time() ,
            'package' => $this->package,
            'ip' => $ip,
            'type'=>'sdk'
        )));
    }

}