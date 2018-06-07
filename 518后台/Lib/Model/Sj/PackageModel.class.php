<?php
class PackageModel extends AdvModel{
	protected $trueTableName = 'package_user_add_channel';
	public function __construct(){
		parent::__construct();
	
		$myConnect2 = C('DB_CO_BULKPACKAGE');
		$this->addConnect($myConnect2,2);  
		$this->switchConnect(2);
		
	}


}

?>