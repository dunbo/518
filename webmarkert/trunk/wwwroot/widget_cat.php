<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$parentid = (int)$_GET['parentid']?$_GET['parentid'] : 1;
$result = gomarket_action('soft.GoGetSoftCategoryList', array('TYPE' => $parentid == 1 ? 0 : 1, 'VR' => 1));
/*echo '<pre>';
print_r($result['DATA']);
exit('</pre>');*/
$tplObj -> out['applist'] = $result['DATA'];	
$tplObj -> out['parentid'] = $parentid;	
$tplObj->display('widget_cat.html');

