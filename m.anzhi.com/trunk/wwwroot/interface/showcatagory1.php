<?php
class showcatagory1
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}
	public function getData()
	{
        $softObj = load_model('sjsoft');
        $typeid = $this -> params['typeid'];
		$view = $this->params['view'];
        if($view=='type' || empty($view)) {

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
        if($view=='special' || empty($view)) {

            $feature = $softObj->getDataList('sj_feature',array('where' => array('status' => 1)));
             $catalogs['3'] = array(
                                 'id' => '3',
                                 'name' =>'专题'
                            );

            foreach($feature as $info)
            {
                extract($info);
                    $catalogs['3']['child'][] = array(
                        'cid' => $feature_id,
                        'name' => $name
                     );
            }
            $catalog_arr='';
            foreach($catalogs as $info){
               $catalog_arr[] = $info;
            }

        }
		return json_encode($catalog_arr);
	}
}

?>