<?php
/*
    日志model
*/

class GoPu_logModel extends GoPu_model
{
    public $index_name = 'option';
    public $option;
    public $cache_timeout = 0;

    function __construct($index = '')
    {
        parent::__construct(__CLASS__, $index);
        if (!$this->option['http_host']) { 
        	$host = $_SERVER['HTTP_HOST'];
        	if (strstr($host, ":")) {
				$host = substr($host, 0, strrpos($host, ":"));
			}
		
		    $this->option['http_host'] = strtolower($host); 
        }
        if (!$this->option['logpath']) { 
            $this->option['logpath'] = P_LOG_DIR ? P_LOG_DIR : '/tmp';
        }
        if (!$this->option['prefix']) { 
            $this->option['prefix'] = $_SERVER['HTTP_HOST']? $_SERVER['HTTP_HOST'] : 'GoPHP';
        }
        if (!$this->option['logdir']) {$this->option['logdir'] = dirname($this->option['logfile']); } 
        if (!$this->option['pid']) {$this->option['pid'] = getmypid(); } 
        
        if ( !is_string($this->option['message']) ) {
			$this->option['message'] =  var_export($this->option['message'], True);
		}
    }
    
    //重载了父类的方法，基于文件形式的日志写入  
    function save_data_info() 
    {
	    if (!$this->option['logpath'] || !$this->option['logfile'] || !$this->option['message'] ) {return False;}
		$path = $this->option['logpath'] .'/'. $this->option['http_host'].'/'.date('Y-m-d', time()).'/';
        if (!is_dir($path)) {
            __mkdir($path, 0755, True);
        }
        $logfile = $path.$this->option['logfile'];
        if ($logfile) { file_put_contents($logfile, $this->option['message']."\n", FILE_APPEND); }
    }

    //目前该model只有写入数据，没有读取数据的逻辑
    function get_data_info()
    {
        return False;
    }

    function get_data_info_arr() 
    {
        return False;
    }
}
