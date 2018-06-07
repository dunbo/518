<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');
/* ini_set('display_errors',true);
error_reporting(E_ERROR); */
if (!strstr ( TEMPLATE_DIR, "uc" )) {
    $_SESSION['MODEL_OID'] = 1;
    $ads = gomarket_action('soft.GoGetRecommend', array('VR' => 1,));
    $adlist = array();
    foreach ($ads['DATA'] as $val) {
        if ($val[0] == 2) {
            $url = '/subject.php?type=subject_app&subject_id='. $val[1];
        } elseif ($val[0] == 1) {
            $url = '/app.php?type=info&package='. $val[1];
        }
        $adlist[] = array(
            'imageurl' => $val[2],
            'name' => $val[3],
            'url' => $url,
        );
    }
	$re_size = 30;
	$index_start = $_GET['index_start'] ? $_GET['index_start'] : 0;
	//$result = get_softlist ( 'soft.GoGetExtentHomeFeatureOld', 0, $re_size); //0,30只针对推荐软件列表
	// 推荐与最新
	$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
	if ($channel == 'bbg' && $_GET['type'] == '')
	{
		if ($morelist >= $bbg_page)
		{
			$model = load_model('softlist');
			$r = gomarket_action ( 'soft.GoGetSoftDetailPackage', array ('PACKAGE_NAME' => 'cn.goapk.market','EXTRA_OPTION_FIELD' => array('isoffice')));
			$softid = $r['ID'];
			if (!empty($softid))
			{
				$model = load_model('softlist');
				$resultanzhi = $model->getsoftinfos($softid, getFilterOption());
				if (!empty($resultanzhi))
				{
					$resultanzhi = $resultanzhi[$softid];
					$resultanzhi['iconurl'] = $img_host . $resultanzhi['iconurl'];
					$resultanzhi['down_url'] = "download.php?softid={$softid}";
					$resultanzhi['isoffice'] = 1;
				}
			}
		}
	}
	if ($_GET['type'] == 'new'){
		$result = get_softlist ( 'soft.GoGetHomeNew', $morelist * 15, 15);
		$tplObj->out ['type'] = $_GET['type'];
	} else {
		if ($channel == 'bbg' && $_GET['type'] == '' && $resultanzhi != '' )
		{
			if ($morelist == $bbg_page)
			{
				$result = get_softlist ( 'softv1.GoGetExtentHomeFeatureOld', $morelist * $re_size, $re_size - 1);
			}
			elseif ($morelist > $bbg_page)
			{
				$result = get_softlist ( 'softv1.GoGetExtentHomeFeatureOld', $morelist * $re_size - 1, $re_size);
			}
			else
			{
				$result = get_softlist ( 'softv1.GoGetExtentHomeFeatureOld', $morelist * $re_size, $re_size);
			}
		}
		else
		{
			$result = get_softlist ( 'softv1.GoGetExtentHomeFeatureOld', $morelist * $re_size, $re_size);
		}
	}

	if ($morelist == $bbg_page && !empty($resultanzhi))
	{
		$result['list'] = array_merge(array_slice($result['list'], 0, $bbg_index), array($resultanzhi), array_slice($result['list'], $bbg_index));
	}
	$sum_count = $re_size + $feature_count;
	$tplObj->out ['page_count'] = ceil($sum_count/$re_size);
	$tplObj->out ['adlist'] = $adlist;
	$tplObj->out ['index_start'] =  $re_size;
	foreach ( $result ['list'] as $key => $value ) {
		$i = $k = 0;
		$result ['list'] [$key] ['scorehtml'] = "";
		$i = floor ( $value ['score'] / 2 );
		$k = $value ['score'] % 2;
		for($i1 = $i; $i1 > 0; $i1 --) {
			$result ['list'] [$key] ['scorehtml'] .= '<img alt="" src="/images/star_01.png">';
		}
		if ($k != 0)
			$result ['list'] [$key] ['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
		if (($i + $k) < 5) {
			for($i2 = (5 - $i - $k); $i2 > 0; $i2 --) {
				$result ['list'] [$key] ['scorehtml'] .= '<img alt="" src="/images/star_03.png">';
			}
		}
	}
	if($_COOKIE['wap']=="concise"){
		$inofret = gomarket_action('soft.GoGetNecessaryExtent',array("LIST_INDEX_SIZE"=>50));
		$tplObj->out['home_install'] = $inofret['DATA'];
	}
	$tplObj->out ['home_features'] = $result ['list'];
	$tplObj->out ['list_page'] = $result ['list_page'];
} else {
	$re_size = 30;
	// 推荐与最新
	$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
	if ($_GET['type'] == 'new'){
		$result = get_softlist ( 'soft.GoGetHomeNew', $morelist * 15, 15);
		$tplObj->out ['type'] = $_GET['type'];
	} else {
		$result = get_softlist ( 'softv1.GoGetExtentHomeFeatureOld', $morelist * $re_size, $re_size);
	}
	$tplObj->out ['home_features'] = $result ['list'];
}

//对步步高wap渠道单独处理
if ($channel != 'bbg')
{
	if ($_GET['type'] != 'new'){
		//最新市场id(安智)
		$model = load_model('softlist');
		$resultanzhi = gomarket_action ( 'soft.GoGetSoftDetailPackage', array ('PACKAGE_NAME' => 'cn.goapk.market','EXTRA_OPTION_FIELD' => array('isoffice')));
		$resultanzhi['isoffice'] = 1;
		$tplObj->out ['anzhilist'] = $resultanzhi;
	}
}
$tplObj->out ['title'] = '安智市场';
// ajax加载分页
if ($_GET['morelist'] >= 1){
	$tplObj->out['morelist'] = $_GET['morelist'];
	$tplObj->display ( "index_ajax.html" );
} else {
	$tplObj->display ( "index.html" );
}
