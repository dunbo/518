<?php
class showcatagory
{
    public $params;
    public function __construct($param)
    {
        $this->params = $param;
    }
    public function getData()
    {
        $view = $this->params['view'];
        $category_logic = pu_load_logic('category');
        $get_ebook = ($this->params['channel'] == 'a4cbf188b6e7f9659415d52414a4262df2e7148e') ? true : false;
        $get_all_category = $category_logic -> get_all_category();
        $catalogs = array();
        if($view=='type' || empty($view)) {
            foreach($get_all_category as $cinfo){
	            if (!$get_ebook && ($cinfo['category_id'] == 3 || $cinfo['parentid'] == 3)) continue; 
                if($cinfo['parentid'] == 0){
                    $catalogs[$cinfo['category_id']] = array(
                           'id' => $cinfo['category_id'],
                           'name' => $cinfo['name']
                    );
                }else if($cinfo['parentid'] > 0){
                    $catalogs[$cinfo['parentid']]['child'][] = array(
                        'cid' => $cinfo['category_id'],
                        'name' => $cinfo['name']
                    );
                }
            }
        }
        if($view=='special'  || empty($view)) {
            $o_subject_obj = pu_load_model_obj('pu_subject');
            $all_subject = $o_subject_obj->get_all_subject();
            $catalogs['3'] = array(
                'id' => '3',
                'name' => '专题',
            );
            foreach($all_subject as $info)
            {
                extract($info);
                $catalogs['3']['child'][] = array(
                        'cid' => $feature_id,
                        'name' => $name
                );
            }
        }
        foreach($catalogs as $info){
            $catalog_arr[] = $info;
        }
        return json_encode($catalog_arr);
    }
}

?>