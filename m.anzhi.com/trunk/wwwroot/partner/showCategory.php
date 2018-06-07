<?php
class showCategory {
    public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
        $softObj = load_model('sjsoft');
		$view = isset($this->params['view']) ? $this->params['view'] : 'type';
        if($view=='type') {

            $parent = $softObj->getDataList('sj_category', array('where' => array('parentid' => 0,'status' => 1), 'index' => 'category_id'));
            $catalog = $softObj->getDataList('sj_category',array('where' => array('status' => 1)));
            foreach($parent as $info){
                 $catalogs[$info['category_id']] = array(
                                     'id' => $info['category_id'],
                                     'name' =>$info['name']
                                );
            }
            foreach($catalog as $info)
            {
                extract($info);
                if ($parentid > 0)
                {
                    $catalogs[$parentid]['child'][] = array(
                        'cid' => $category_id,
                        'name' => $name
                     );
                }
            }
            foreach($catalogs as $info){
               $catalog_arr[] = $info;
            }

        }
		return json_encode($catalog_arr);
	}
}
?>