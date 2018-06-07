<?php
class newSoftList extends Action{
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
        $softObj = '';
        $type     = '';
        $softObj = load_model('sjsoft');
        $page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 9;
        if(isset($this -> params['type'])) {
            $type = $this -> params['type'];
        }
        if(empty($type)){
			$option = array(
				'offset' => $page * $pagesize,
				'pagesize' => $pagesize,
			);
			$softids = $this -> getCacheData($this -> device,'new',$option);

			$total = $option['total'];
			$softinfos = $softObj -> getDataList('sj_soft',array('where'=>array('softid' => $softids),'index'=> 'softid'));
			$softfiles = $softObj -> getDataList('sj_soft_file',array('where' => array('softid'=> $softids),'index'=> 'softid'));
        }else{
			$option = array(
				'offset' => $page * $pagesize,
				'pagesize' => $pagesize,
				'catalogid' => $type,
				'order_by' => 'new'
			);
			$softids = $this -> getCacheData($this -> device, "type", $option);
			$total = $option['total'];

			$softinfos = $softObj -> getDataList('sj_soft',array('where'=>array('softid' => $softids),'index' => 'softid'));
			$softfiles = $softObj -> getDataList('sj_soft_file',array('where' => array('softid'=> $softids),'index'=> 'softid'));
        }
		$category = $softObj -> getDataList('sj_category', array('where' => array('status' =>1),'index' => 'category_id'));
		$img_host = getImageHost();
        foreach($softids as $idx){
			if (!isset($softinfos[$idx])) continue;
			$infos = $softinfos[$idx];
            $softlist[] = array(
                'softid' => $infos['softid'],
                'package' => $infos['package'],
                'softname' => $infos['softname'],
                'icon' => $img_host . $softfiles[$idx]['iconurl'],
                'typeid' => substr($infos['category_id'],1,-1),
                'type' => $category[substr($infos['category_id'],1,-1)]['name'],
                'score' => $infos['score']
            );
        }
        $data = array(
            'KEY' => 'NEWSOFTLIST',
            'TOTAL' => $total,
            'DATA' => $softlist,
        );
        return json_encode($data);
	}
}
?>