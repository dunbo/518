<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$parentid = (int)$_GET['parentid']?$_GET['parentid'] : 1;
$results = gomarket_action('v53.GoGetCategoryTag', array('CATE_TYPE' => $parentid == 1 ? 0 : 1,'VR'=>14));
$new_res = array();
//过滤掉没有标签的分类和电子书分类
foreach($results['CATEGORY_GROUP'] as $val){
    if(!empty($val[3]) && $val[0]!=3){
        $new_res[] = $val;
    }
}
/*echo '<pre>';
print_r($result);
exit('</pre>');*/

$tplObj -> out['applist'] = $new_res;
$tplObj->display('widget_catetag.html');

