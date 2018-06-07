<?php
class showSoftList extends Action {
    public $params;
    public $device;
    public $did;
    public $cache;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
        $softObj = load_model('sjsoft');
		$view = $this->params['view'];
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;
		$option = array(
            'offset' => $page * $pagesize,
			'pagesize' => $pagesize,
			);
        $ad = array(1=>'desc' , 2=>'asc');
        if(isset($this -> params['order_key'])) $option['order_key']  = $this -> params['order_key'];
        if(isset($this -> params['order'])) $option['order' ]  = $ad[$this -> params['order']];

		if ($this->params['cid'])
		{
			$cid = $this->params['cid'];
			$option['catalogid'] = $cid;
		}
		$views = array('new','type','special');
		$catalog = $softObj->getDataList('sj_category', array('where' => array('status'=>1),'index' => 'category_id'));
		if ($view == 'suggest')
		{
            $view = 'subrank';
			$softids = $this ->getCacheData($this -> device,$view, $option);
		}
		else if (in_array($view, $views))
		{
            if($view == 'new') $view = 'subnew';
			$softids = $this->getCacheData($this -> device,$view, $option);
		}
        $total = $option['total'];
        $where = array('where'=>array('softid' => $softids));
        if(isset($this -> params['order_key'])&&isset($this -> params['order'])){
            $where['order'] = $option['order_key'].'  '.$option['order'];
        }
        $softinfos = $softObj -> getDataList('sj_soft',$where);
        $softfiles = $softObj -> getDataList('sj_soft_file',array('where' => array('softid'=> $softids),'index'=> 'softid'));
        $category = $softObj -> getDataList('sj_category', array('where' => array('status' =>1),'index' => 'category_id'));
        foreach($softinfos as $idx => $infos){
            $softlist[] = array(
                'softid' => $infos['softid'],
                'package' => $infos['package'],
                'softname' => $infos['softname'],
                'icon' =>STATIC_IMG_HOST.$softfiles[$infos['softid']]['iconurl'],
                'typeid' => substr($infos['category_id'],1,-1),
                'type' => $category[substr($infos['category_id'],1,-1)]['name'],
                'total_download' => $infos['total_downloaded'],
                'score' => $infos['score']
            );
        }
		$data  = array(
			'KEY' => strtoupper($this -> params['action']),
			'TOTAL' => $option['total'],
			'DATA' => $softlist,
		);
		return json_encode($data);
	}
}
?>