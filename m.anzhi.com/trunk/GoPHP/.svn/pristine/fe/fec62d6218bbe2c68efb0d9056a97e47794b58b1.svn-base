<?php
/*
 * Oracle 处理类
 */
include_once("class.oracle.php");
class  GoOracle
{	
	protected  static  $oracle;
	
	//instance
	public function __construct()
	{
		$config = load_config('oracle/group1');
		$this->oracle = new ORACLE();
		$this->oracle->SetNlsLang($config['charset']);	//设置utf8编码
		$this->oracle->Connect($config['server'],$config['username'],$config['password']);
		$this->oracle->SetFetchMode(OCI_ASSOC);
		$this->oracle->SetAutoCommit(true);
	}
	
	public function fetchOne($table,$data,$field)
	{	
		if(empty($table)) return  false;
		
		$var_sql = '';
		$bind_arr = array();
		$select_field = '';
		if(empty($field)) $select_field = '*';
		if(is_array($field))
		{
			$select_field .= implode(",",array_values($field));
		}
		if(is_array($data))
		{   
			foreach($data as $key=>$val)
			{
				$var_sql .= $key.':'.$key.',';
				$bind_arr[':'.$key] = $val; 
			}
		}else{
			$var_sql = "1=1";
		}
		$select_sql =  "Select ".$select_field." from ".$table.' where '.$var_sql;
		if(empty($bind_arr)) $bind_arr = false;
		$h = $this->oracle->Select($select_sql,$bind_arr);
		$r = $this->oracle->FetchArray($h);
		if(!empty($r))
		{
			return $r;
		}
		return false;
		
	}
	public function fetchOneBySql($sql)
	{
		$h = $this->oracle->Select($sql);
		$r = $this->oracle->FetchArray($h);
		if(!empty($r))
		{
			return $r;
		}
		return false;
	}
	public function fetchALLBySql($sql)
	{
		$h = $this->oracle->Select($sql);
		$r = $this->oracle->FetchAll($h);
		if(!empty($r))
		{
			return $r;
		}
		return false;
	}
	//fetch All rows
	public function fetchAll($table,$data,$field)
	{	
		if(empty($table)) return  false;
		
		$var_sql = '';
		$bind_arr = array();
		$select_field = '';
		if(empty($field)) $select_field = '*';
		if(is_array($field))
		{
			$select_field .= implode(",",array_values($field));
		}
		if(is_array($data))
		{   
			foreach($data as $key=>$val)
			{
				$var_sql .= $key.':'.$key.',';
				$bind_arr[':'.$key] = $val; 
			}
		}else{
			$var_sql = "1=1";
		}
		$select_sql =  "Select ".$select_field." from ".$table.' where '.$var_sql;
		if(empty($bind_arr)) $bind_arr = false;
		$h = $this->oracle->Select($select_sql,$bind_arr);
		$r = $this->oracle->FetchAll($h);
		if(!empty($r))
		{
			return $r;
		}
		return false;
		
	}
	
	//Insert data into table 
	public function insert($table,$insert_data)
	{	
		if(empty($table)) return false;
		if(empty($insert_data)|| !is_array($insert_data)) return false;
		$sql_arr = array();
		$bind_arr = array();
		foreach($insert_data as $key =>$val)
		{
			$sql_arr[$key] = ':'.$key;
			$bind_arr[':'.$key] = $val;
		}
		$r = $this->oracle->Insert($table,$sql_arr,$bind_arr);
		if(empty($r))
		{
			return false;
		}else{
			return true;
		}
		//return $r;
		
	}
	public function  update($table,$update_data,$where)
	{
		if(empty($table))  return false;
		if(empty($update_data) || !is_array($update_data)) return false;
		$sql_arr = array();
		$bind_arr = array();
		$where_arr = '';
		foreach ($update_data as $key =>$val)
		{
			$sql_arr[$key] = ':'.$key;
			$bind_arr[':'.$key] = $val;
		}
		foreach($where as $k =>$v)
		{
			$where_arr[] = $k."='".$v."'";
		}
		//var_dump($where);
		$where_str = implode("and",$where_arr);
			
		$r = $this->oracle->update($table, $sql_arr,$where_str,$bind_arr);
		if(!empty($r))
		{
			return true;
		}else{
			return false;
		}
	}
}
