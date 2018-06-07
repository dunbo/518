<?php

class mybelike1
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
		$softid = $this->params['softid'];
		$page = $this->params['start'] ? $this->params['start'] : 0;
		$pagesize = $this->params['limit'] ? $this->params['limit'] : 5;
        $offset = $page * $pagesize;
        $softObj = load_model('sjsoft');
		$packinfo = $softObj->getDataList('sj_soft', array('where' => array('softid' => $softid)));
		$package = $packinfo[0]['package'];
		$soft_note = $softObj->getDataList('sj_soft_note', array('where' => array('package' => $package)));
		$soft_suggest = json_decode($soft_note[0]['soft_suggest'], true);



        for($i=0;$i<count($soft_suggest);$i++) {
		    $softlikelist= $softObj->getDataList('sj_soft', array('where' => array('package' => $soft_suggest[$i],"status"=>1,"hide"=>1)));

		    $icon= $softObj->getDataList('sj_soft_file', array('where' => array('softid' => $softlikelist[0]['softid'],'package_status'=>1)));

            $youlike[$i]=$softlikelist[0];
            $youlike[$i]['iconurl']=$icon[0]['iconurl'];

        }

		$catalog = $softObj->getDataList('sj_category', array('index' => 'category_id'));
        $data = array();
		foreach ($youlike as $idx => $info)
		{

            if($info['category_id'][0]==',')
            {
                $info['category_id']=substr($info['category_id'],1);
            }

            $tnum=strlen($info['category_id']);
            $tnum--;
            if($info['category_id'][$tnum]==',')
            {
                $info['category_id']=substr($info['category_id'],0,-1);
            }

			$data[] = array('package' => $info["package"],
				"icon" => STATIC_IMG_HOST . $info['iconurl'],
				'typeid' => $info['category_id'],
				'type' => $catalog[$info['category_id']]['name'],
				'name' => $info["softname"],
				'softid' => $info["softid"]
				);
		}
        $total = count($data);
		$data = array_slice($data, $offset, $pagesize);
		$return  = array(
			'TOTAL' => $total,
			'DATA' => $data,
			'KEY' => strtoupper($this -> params['action']),
		);
		return json_encode($return);
	}
}

?>