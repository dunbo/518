<?php
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
class showfulllist
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$view = $this->params['view'];
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;
        $pagesize = ($pagesize > 30) ? 30 : $pagesize; //对 limit 进行限制  加入 大于30  设置为30
		$channel = $this->params['channel'];
        $page = $page * $pagesize;
		$pname = "";
		$category_id = $this -> params['cid'];
        if($view == 'fulltype'){
		
			$result = getAPI('soft.gettypesoftlist',array('id' => $category_id,'order' => 1,'start' => $page,'limit' => $pagesize,'showtype' => 'full'));
            $data = json_decode($result,true);
			$infos = $data['DATA'];
			$count = $data['TOTAL'];
            
        }else if($view == "fullspecial"){
			$result = getAPI('soft.getspeciallist',array('subject_id' => $category_id,'order' => 1,'start' => $page,'limit' => $pagesize,'showtype' => 'full'));
            $data = json_decode($result,true);
			$infos = $data['DATA'];
			$count = $data['COUNT'];
        }else{
			header("HTTP/1.1 403 Forbidden");
            exit;
		}
		$softids = array();
        $data = array();
		$img_host = getImageHost();
		foreach ($infos as $idx => $app)
		{   
			$app['thumb'] = $this->getThumb($app['softid']);
			$permis_js = getAPI('soft.get_permission',array('softid' => $app['softid']));
			$permission = json_decode($permis_js,true);
			$app['userpermission'] = $permission['DATA'];
			$app['download'] = "http://m.anzhi.com/interface/index.php?action=download&softid={$app['softid']}&channel=$channel";
			$app['parent_type'] = $app['parent_name'];
			$data[$idx] = $app;
		}
		$return  = array(
			'TOTAL' => $count,
			'DATA' => $data,
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}

	public function getCategory(&$category_id,$catalog){
		if($category_id[0]==',')
		{
			$category_id=substr($category_id,1);
		}

		$tnum=strlen($category_id);
		$tnum--;
		if($category_id[$tnum]==',')
		{
			$category_id=substr($category_id,0,-1);
		}
		$category_id_array = explode(",", $category_id);

		$re = "";
		foreach($category_id_array as $v){
			if($v=='') continue;
			$re .= $catalog[$v]['name'].",";
		}
		$re = substr($re,0,-1);
		return $re;
	}

	public function getParentCategory($category_id,$catalog){
		$pid = $catalog[$category_id]["parentid"];
		$pname = $catalog[$pid]["name"];
		return $pname;
	}

	public function getThumb($softid){
		$softObj = load_model('sjsoft');
		$pics = $softObj->getDataList('sj_soft_thumb', array('where' => array('softid' => $softid
					,'status'=>1)
				));

        $thumb  = array();
		$img_host = getImageHost();
		if (count($pics) > 0)
		{
			foreach($pics as $idx => $pic)
			{
				$thumb[] = array('thumb_' . $idx . '_url'=>$img_host . $pic['url']);
			}
		}

		return $thumb;
	}
	
	public function getPermission($fileid){
	   if(is_array($fileid)){
	      $file_id = array_unique($fileid);
	   }else{
	      $file_id = $fileid;
	   }
	   $model = new GoModel();
	   $option = array(
				'table' => 'sj_soft_permission as sp',
				'join'  => array('sj_soft_permission_details as spd' => array('on' => array('sp.permissionid', 'spd.id'), 'join_type' => 'left')),
				'where' => array('sp.fileid' => $file_id),
				'field' => array('spd.name'),
				'cache_time' => 3600,
			    );
		$soft_permission_lists = $model->findAll($option);
		foreach($soft_permission_lists as $permission_list){
			$permission[] = $permission_list['name'];
		}
        return $permission;  
	}

}

?>
