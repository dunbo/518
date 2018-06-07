<?php
class PerfectSoftStatModel extends AdvModel{
	protected $connect_id = 77;
	protected $tableName = 'perfect_soft_stat';
	protected $tablePrefix = 'sj_';
	
	
	public function __construct(){
		parent::__construct();
	    $ps_Connect = C('DB_STAT_BASE');
		$this -> addConnect($ps_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	public function findStatByPerfectSoftId($perfect_soft_id, $from_date=null, $to_date=null)
	{
		$map['perfect_soft_id'] = $perfect_soft_id;
		
		if(is_null($from_date)==FALSE && is_null($to_date)==FALSE)
			$map['stat_date'] = array(array('egt', $from_date), array('elt', $to_date));
			
		return $this->field('stat_date, sum(browser_pv) AS browser_pv, sum(market_pv) AS market_pv')
					->where($map)
					->group('stat_date')
					->order('stat_date')
					->select();
	}
}