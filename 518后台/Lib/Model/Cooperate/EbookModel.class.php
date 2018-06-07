<?php
class EbookModel extends AdvModel {
	//调整表前缀
	protected $trueTableName = 'coop_ebook';
	protected $connect_id = 119;
    
	public function __construct()
	{
		//parent::__construct();
		$myConnect1 = C('DB_EBOOK_BASE');
		$this -> addConnect($myConnect1, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
}