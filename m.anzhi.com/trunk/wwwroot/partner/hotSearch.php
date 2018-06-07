<?php
class hotSearch {
    public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{

        $softObj = load_model('sjsoft');
        $option = array('where' => array('status' => 1));
        $keylists  = $softObj -> getDataList('sj_soft_keywords',$option);
        $total = count($keylists);
        $page = isset($this -> params['start']) ? $this -> params['start'] : 0;
        $pagesize = isset($this -> params['limit']) ? $this -> params['limit'] : 10;
        $option['limit'] = $pagesize;
        $option['offset'] = $page*$pagesize;
        $keylists  = $softObj -> getDataList('sj_soft_keywords',$option);

        foreach($keylists as $keys){
              $data[] =array(
                   'keyword' => $keys['keywords'],
                   'package' => $keys['package']
              );
        }
        $data = array(
            'KEY' => strtoupper($this -> params['action']),
            'TOTAL' => $total,
            'DATA'  =>$data
        );
        return json_encode($data);
    }
}
?>