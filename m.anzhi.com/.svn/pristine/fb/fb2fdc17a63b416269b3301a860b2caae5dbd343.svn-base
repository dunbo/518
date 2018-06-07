<?php
class suggestList extends Action{
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
        $page = isset($this->params['start']) ? $this->params['start'] : 0;
		$pagesize = isset($this->params['limit']) ? $this->params['limit'] : 9;
        if(isset($this -> params['type'])) {
            $type = $this -> params['type'];
        }
        if(empty($type)){
        $option = array(
            'offset' => $page * $pagesize,
            'pagesize' => $pagesize,
        );
        $softids = $this -> getCacheData($this -> device,'suggest',$option);
        //var_dump($softids);
        $total = $option['total'];
        $softinfos = $softObj -> getDataList('sj_soft',array('where'=>array('softid' => $softids)));
        $softfiles = $softObj -> getDataList('sj_soft_file',array('where' => array('softid'=> $softids),'index'=> 'softid'));
        $category = $softObj -> getDataList('sj_category', array('where' => array('status' =>1),'index' => 'category_id'));
        }else{
            $softids = $this -> getCacheData($this -> device,'suggest');
            $categoryids = $softObj -> getDataList('sj_category', array('where' => array('status' => 1, 'parentid' => $type),'field' => 'category_id'));
            foreach($categoryids as $value){
               $cids[] = ','.$value['category_id'].',';
            }
         $softinfos = $softObj -> getDataList('sj_soft',array('where'=>array('softid' => $softids,'category_id' => $cids ),'index' => 'softid'));
         $soft_ids   = array_keys($softinfos);
         $softinfos = array_values($softinfos);
         $softfiles = $softObj -> getDataList('sj_soft_file',array('where' => array('softid'=> $soft_ids),'index'=> 'softid'));
         $category = $softObj -> getDataList('sj_category', array('where' => array('status' =>1),'index' => 'category_id'));
         $total = count($softinfos);
         $page = $page*$pagesize;
         $softinfos = array_slice($softinfos,$page,$pagesize);
        }
        foreach($softinfos as $idx => $infos){
            $softlist[] = array(
                'softid' => $infos['softid'],
                'package' => $infos['package'],
                'softname' => $infos['softname'],
                'icon' =>STATIC_IMG_HOST.$softfiles[$infos['softid']]['iconurl'],
                'typeid' => substr($infos['category_id'],1,-1),
                'type' => $category[substr($infos['category_id'],1,-1)]['name'],
                'score' => $infos['score']
            );
        }
        $data = array(
            'KEY' => 'SUGGESTLIST',
            'TOTAL' => $total,
            'DATA' => $softlist,
        );
        return json_encode($data);
	}
}
?>
