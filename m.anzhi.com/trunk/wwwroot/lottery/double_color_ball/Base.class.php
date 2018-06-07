<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
class Base{
    public $redis;
    public $model;
    public $configs;

    protected function __construct(){
        global $configs,$tplObj;
        $this->model = new GoModel();
        $this->configs = $configs;
        $this->tplObj = $tplObj;
        $config = load_config('lottery_cache/redis',"lottery");
        if ($config) {
            $this->redis = new GoRedisCacheAdapter($config);
        } else {
            $this->redis = GoCache::getCacheAdapter('redis');
        }
    }
}