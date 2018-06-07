<?php
/*
    分类logic
*/
class GoCategoryLogic
{
    function get_sub_category($category_id)
    {
        $category_obj = pu_load_model_obj('pu_category', $category_id);
        return $category_obj->get_sub_category();
    }

    function get_all_category() {
      $category_obj = pu_load_model_obj('pu_category');
      $all = $category_obj -> get_all_category();
      return $category_obj -> get_all_category();
    }
}

