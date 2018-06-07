<?php
ini_set("display_errors", 1);
//error_reporting(E_ALL);
class reportInstall
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{	
		$input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if (!empty($data)) {
			permanentlog('bdhk_install.log', json_encode($data));
			$return  = array(
				'ret' => 0
			);
			return json_encode($return);
		}
	}
}

?>
