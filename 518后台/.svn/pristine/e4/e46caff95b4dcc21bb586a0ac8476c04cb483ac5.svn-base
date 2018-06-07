<?php
class DownloadModel extends AdvModel {
	//调整表前缀
     protected $trueTableName = 'sj_download_count_day';
	 
	 public function __construct()
	 {
		parent::__construct();
		if (C('DB_HOST') != '192.168.0.99') {
			$myConnect1 = C('DB_CO_GOSTATS'); 
			$this->addConnect($myConnect1,2);  
			$this->switchConnect(2);
		}
	 }
}
?>
