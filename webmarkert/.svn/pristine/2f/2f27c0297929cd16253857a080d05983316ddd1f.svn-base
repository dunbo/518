<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$tplObj->out['type'] = 'search';
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : $_GET['search_key'];
$show_keyword = htmlspecialchars($keyword);
$channel = $_GET['channel'];
// 客户端　手机版、HD版soft.GoGetSoftDetailCategory
$anzhi = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'VR' => 1));
$tplObj->out['anzhi'] = $anzhi;		// 手机版
if(!empty($keyword)){
	$shit = 1;
	$page = (int)$_GET['page'];
	$start = ($page <= 1)? 0 : ($page - 1) * 15;
	$size = 15;
	$result = gomarket_action("softv2.GoSearchSoft",array(
				'SEARCH_QUERY' => $keyword,
				'LIST_INDEX_START' => $start,
				'LIST_INDEX_SIZE' => $size,
				'VR' => 2,
				'GET_COUNT' => true,
				'QUERY_CONDITION' => 1234, 
				'EXTRA_OPTION_FIELD' => array(
				'A.upload_tm',
				'B.min_firmware',
				'A.intro'),
				'PARENT_CAT_ID' => $id,
				'ID' => $id,
			)
	);
    foreach ($result['DATA'] as $k => $v) {
        $list[$k] = array( 'softid' => $v[0], 'iconurl' => $v[1], 'softname' => $v[2], 'score' => $v[3], 'msgnum' => $v[4], 'dev_name' => $v[5], 'costs' => $v[6], 'package' => $v[7], 'safe' => $v[8], 'filesize' => $v[9], 'category_id' => $v[10], 'total_downloaded' => $v[11], 'url' => $v[12], 'version_code' => $v[13], 'upload_tm' => $v[14], 'min_firmware' => $v[15], 'official_icon' => $v[21],'intro' => $v[25]);
        $list[$k]['requirements'] = firmware2os($list[$k]['min_firmware']); //系统要?
        $list[$k]['down_url'] = 'download.php?softid='.$list[$k]['softid'];
        $cat_name = array();
        if ($cat_id_arr = explode(',', trim($list[$k]['category_id'], ','))) {
            foreach ($cat_id_arr as $cat_id) {
                $cat = get_category($cat_id);
                $cat_name[] = $cat['name'];
            }
        }
        $list[$k]['cat_name'] = implode(', ', $cat_name);
    }
	$result = array(
		'list' => $list,
		'count' => $result['COUNT'],
		'list_page' => make_list_page($start, 15, $result['COUNT'])
	);
	if($result['list']){
		$count = $result['count'];
		$num = 15;
		$area = 10;
		$tplObj->out['page'] =  pagination_arr($page, $count, $num, $area);
		$tplObj -> out['shit'] = $shit;
		$tplObj -> out['keyword'] = $show_keyword;
		$tplObj -> out['result'] = $result['list'];
		display('search_list_new.html');
	}else{
		//热门搜索排行
		$search_hot_result = gomarket_action('soft.GoGetHotWords');
		$hot = array_slice($search_hot_result['DATA'],0,8);
		foreach($hot as $key => $val){
			$val['rank'] = $key + 1;
			$hot[$key] = $val;
		}
		$tplObj -> out['keyword'] = $show_keyword;
		$tplObj -> out['hot'] = $hot;
		display('search_no.html');

	}
}else{
	$shit = 2;
	//无搜索关键词显示
	$page = (int)$_GET['page'];
	$start = ($page <= 1)? 0 : ($page - 1) * 10;
	$HomeHotApp = gomarket_action('soft.GoGetHomeHot', array('LIST_INDEX_START' => $start, 'LIST_INDEX_SIZE' => 15,'VR' => 1,'GET_COUNT' => true,'EXTRA_OPTION_FIELD' => array('upload_tm',
					'min_firmware',
					'category_name',
					'subname',
					'intro',
					'version',
					)));
	$count = $HomeHotApp['COUNT'];
	$num = 15;
	$area = 10;
	$tplObj->out['page'] =  pagination_arr($page, $count, $num, $area);
	$tplObj -> out['shit'] = $shit;
	$tplObj -> out['keyword'] = $show_keyword;
	$outkey = 'result';
	if(CHANNEL == '360_app' || CHANNEL == '360_game') $outkey = 'hotAppResult';
	$tplObj -> out[$outkey] = $HomeHotApp['DATA'];
	display('search_list_new.html');
}
?>