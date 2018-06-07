<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR);
//include_once ("../../../stdafx.php");

include dirname(__FILE__).'/../../newgomarket.goapk.com/init.php';
include_once ("../../data/config_plus.php");
include_once ("./Action.class.php");
/**
 * Dispatch class
 */
class Api
{
	public $params = null;
    public $channel = '';
    public $pad_key = '';
    public $channel_key = '';
    public $softObj;
    public $cache;
	public $action_arr = array(
        'suggestList',
        'newSoftList',
        'topSoftList',
        'showCategory',
        'showSoftList',
        'downLoad',
        'mayBeLike',
        'softIntro',
        'commentList',
        'hotSearch',
        'hotSearchList',
        'searchSoftList',
        'searchHSoftList'
    );
	public $channel_arr = array(
        'GT-P1000' => 'cb8c12230a457f67f5a663809198a7c66e6308d1',//汉王平板_WAP
        //'X10i' => 'xxxx'
	);
    public $pad_device = array(
        '1597' => 'GT-P1000',//汉王平板——资源和gt-p1000的一样
        //'1593' => 'X10i'
    );
	public function getClassByAct()
	{
		if (in_array($this->params['action'], $this->action_arr))
		{
			if (is_file($this->params['action'] . ".php"))
			{
				include($this->params['action'] . ".php");
			}else{
				header("HTTP/1.1 404 Not Found");
				header("Status: 404 Not Found");
				exit;
			}
		}else{
			header("HTTP/1.1 417 Expectation Failed");
            exit;
		}
	}
	public function getData()
	{
		$data = "";
        $this -> pad_key  = $this -> params['device'];
        $this -> channel_key = $this -> pad_device[$this -> pad_key];
        $this -> channel = $this -> channel_arr[$this -> channel_key];

        $adapter = $this->chlAdapter();
        if($adapter && !empty($this -> pad_device[$this->pad_key]) && !empty($this -> channel) && ($this -> channel == $this -> params['channel'])){
            $class = $this->params['action'];
            $datacls = new $class($this->params);
            $datacls -> device  = $this -> channel_key;
            $data = $datacls->getData();
        }else{
        	header("HTTP/1.1 403 Forbidden");
            exit;
            //echo "forbidden";
        }

        if($data=="[]"){
        	header("HTTP/1.1 204 No Content");
            exit;
           //echo "No Content";
        }
		return $data;
	}

	public function chlAdapter(){
		$chl = $this -> params['channel'];
		if($chl=="cb8c12230a457f67f5a663809198a7c66e6308d1"){//汉王，限定时间凌晨2点到7点
			$now_hour = date("G");
			$now_day = date("j");
			if($now_hour>=2 && $now_hour<7){
				return true;
			}else{
				return true;
			}
		}

		return true;
	}
}

$api = new Api();
$api->params = $_REQUEST;
$api->getClassByAct();
//var_dump(json_decode($api->getData(),true));
echo $api->getData();
?>
