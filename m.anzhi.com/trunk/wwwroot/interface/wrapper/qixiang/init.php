<?
exit();
# 要求对本文件所在的文件夹有写权限

define('IN_QIXIANG', 1);

include(dirname(realpath(__FILE__)). "/func.php");

class wrapper_qixiang_init
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}

    public function getData() {
    	$start = !empty($this->params['start']) ? intval($this->params['start']) : 0;
    	$count = !empty($this->params['per_page']) ? intval($this->params['per_page']) : 10;
    	$data = getData(true);
        $result = array(
        	'key' => 'INIT_DATA',
            'DATA' => array_slice($data, $start, $count),
    		'TOTAL' => count($data),
        );
        return json_encode($result);
    }
}

