<?php

class showsoftlist
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
        $page = $page * $pagesize;

		if ($this->params['cid'])
		{
			$category_id = $this->params['cid'];
		}
		$views = array('new', 'hot_7d', 'hot_30d', 'type','special');
		if ($view == 'suggest')
		{
			$result = getAPI('soft.getrecommend',array('start' => $page,'limit'=> $pagesize,'action' => $this -> params['action']));
			return $result;
		}
		else if (in_array($view, $views))
		{
            if($view == "new"){			
				$result = getAPI('soft.getnew',array('start' => $page,'limit'=> $pagesize,'action' => $this -> params['action']));
				return $result;				
            }else if($view == "hot_7d" || $view == "hot_30d"){
				$id = ($view == "hot_7d") ? 2 : 3;
				$result = getAPI('soft.gethotnd',array('id' => $id,'start' => $page,'limit' => $pagesize,'action' => $this -> params['action']));
				return $result;
            }else if($view == 'type'){
                $order_by = 'hot';
				$result = getAPI('soft.gettypesoftlist',array('id' => $category_id,'order' => 1,'start' => $page,'limit' => $pagesize,'action' => $this -> params['action']));
				return $result;
            }else if($view == "special"){
				$result = getAPI('soft.getspeciallist',array('subject_id' => $category_id,'start' => $page,'limit' => $pagesize,'action' => $this -> params['action']));
				return $result;			
            }
		}
	}
}

?>
