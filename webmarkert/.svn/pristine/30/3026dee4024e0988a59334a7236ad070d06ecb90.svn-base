<?php
	include_once(dirname(realpath(__FILE__)).'/init.php');
	$parentid = isset($_GET['parentid']) ? ($_GET['parentid']-1) : 0;	
	switch($_GET['type']) {
			//应用
		case 'applist'; 
		case 'gamelist';
			//应用版块分类
			$parentid = ($_GET['type'] == 'applist') ? 0 : 1; 
			/*$category_id = array('TYPE' => $parentid, 'VR' => 1);
			$categorylist = gomarket_action('soft.GoGetSoftCategoryList',$category_id);
			$categorylists = $categorylist['DATA'];
			$tplObj -> out['show_cate_list'] = $categorylists;*/
			$tag_id = array('CATE_TYPE' => $parentid, 'VR' => 14);
			$results = gomarket_action('v53.GoGetCategoryTag', $tag_id);
			$new_res = array();
	        //过滤掉没有标签的分类和电子书分类
	        foreach($results['CATEGORY_GROUP'] as $val){
	            if(!empty($val[3]) && $val[0]!=3){
	                $new_res[] = $val;
	            }
	        }
			$tplObj -> out['show_cateTag_list'] = $new_res;

			//广告图
			//上
			$tplObj->out['GOAPK_IMG_HOST'] = GOAPK_IMG_HOST;
			$adp_top = get_ad_pic(1);
			$tplObj -> out['adp'] = $adp_top;
			//下
			$adp_under = get_ad_pic(2);
			$tplObj -> out['adp_under'] = $adp_under;

			if($_GET['type'] == 'applist'){
				$tpl = 'item_app.html';
			}else{
				$model = new GoModel();
				$option = array(
					'table' => 'sj_special_list',
					'where' => array(
						'status' => 1,
						'special_place' => 'ad3',
					),
				);
				$res = $model -> findOne($option);
				$tplObj -> out['subject3id'] = $res['special_show'];
				$tplObj -> out['subject3size'] = $res['soft_num'];
				$tplObj -> out['subject3name'] = $res['special_name'];
				$tpl = 'item_game.html';
			}
			$tplObj->out['type'] = $_GET['type'];
			display($tpl);
		break;
		
		case 'gamecat';//游戏各分类软件列表
		case 'appcat';//应用各分类软件列表
			$memcache = GoCache::getCacheAdapter('memcached');
			$type_id = $memcache->get('TYPE_ID');

			$sub_cate_id = $_GET['sub_cat_id'] ;
			$tplObj->out['sub_cat_ids'] = $sub_cate_id;			
			#$tplObj->out['channel'] = $_GET['channel'];			
			$order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
            $tplObj->out['order'] = $order;	
			$page = (int)$_GET['page']?$_GET['page'] : 1;
			$limit = 20;
			$offset = ($page - 1) *$limit; 
			$parameters = array(
				'LIST_INDEX_START' => $offset,
				'LIST_INDEX_SIZE' => $limit,
				'GET_COUNT' => True,
				'EXTRA_OPTION_FIELD' => array(
					'upload_tm',
					'min_firmware',
					'category_name',
					//'parent_name',
					'subname',
					'intro',
					'parentid',
				),
				'ORDER'=> $order,
				'ID' => $sub_cate_id,
				'VR' => 1
			);
			//各分类的软件
			$results = gomarket_action('soft.GoGetCategoryAllSoftList',$parameters);
			/*echo '<pre>';
			print_r($results['DATA']);
			exit('</pre>');*/
			foreach($results['DATA'] as $key => $val){
				$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
				$results['DATA'][$key] = $val;
			}

			if($type_id[$sub_cate_id]['parentid'] == 1) {
				//安卓应用二级分类
				$tplObj -> out['appcat_seo_type'] = 'appcat_1';
			}elseif($type_id[$sub_cate_id]['parentid'] == 2) {
				//安卓游戏二级目录
				$tplObj -> out['appcat_seo_type'] = 'appcat_2';
			}
			$count = $results['COUNT'];
			$parentid = $type_id[$sub_cate_id]['parentid'];
			$tplObj -> out['softapplist'] = $results['DATA'];
			$tplObj -> out['parentid'] =  $parentid;
			$tplObj -> out['parentname'] =  $type_id[$parentid]['name'];
			$tplObj -> out['subname'] =  $type_id[$sub_cate_id]['subname'];
			$tplObj -> out['categoryname'] =  $type_id[$sub_cate_id]['name'];
			$tplObj -> out['page'] =  pagination_arr($page,$count,$limit,10);
			$tplObj -> out['now_page'] = $page;
			//应用分类列表
			//$result = gomarket_action('soft.GoGetSoftCategoryList', array('TYPE' => $parentid == 1 ? 0 : 1, 'VR' => 1));
			//$tplObj -> out['applist'] = $result['DATA'];	
			
			//$hot = get_softlist('soft.GoGetHomeHot',0, 8, $parentid);
			$tplObj->out['type'] = $_GET['type'];
			//$tplObj -> out['hot'] = $hot['list'];
			
			display('item_list.html');
		break;
		case 'appctag';
		case 'gamectag';
			$memcache = GoCache::getCacheAdapter('memcached');
			$type_id = $memcache->get('TYPE_ID');

			//标签id
			$sub_tag_id = $_GET['sub_tag_id'];
			$model = new GoModel();
			$option = array(
				'table' => 'sj_tag',
				'where' => array(
					'status' => 1,
					'tag_id' => $sub_tag_id,
				),
			);
			$res = $model -> findOne($option);
			$tplObj->out['sub_tag_name'] = $res['tag_name'];
			$tplObj->out['sub_tag_ids'] = $sub_tag_id;
			//分类id
			$sub_cate_id = $_GET['sub_cat_id'] ;
			$tplObj->out['sub_cat_ids'] = $sub_cate_id;
			//order 1最热 0最新
			$order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
            $tplObj->out['order'] = $order;
            //分页	
			$page = (int)$_GET['page']?$_GET['page'] : 1;
			$limit = 20;
			$offset = ($page - 1) *$limit; 
			$parameters = array(
				'GET_COUNT' => True,
				'LIST_INDEX_START' => $offset,
				'LIST_INDEX_SIZE' => $limit,
				'ORDER'=> $order,
				'CATEGORY_ID' => $sub_cate_id,
				'TAG_ID' => $sub_tag_id,
				'VR' => 24,
			);
			//各标签的软件
			$results = gomarket_action('v53.GoGetTagSoftList',$parameters);
			foreach($results['DATA'] as $key => $val){
				$val['qrimg'] = get_qrimg($val[0],$val[7],$val[15],$val[1]);
				$val[11] = num_format($val[11], 2);
				$results['DATA'][$key] = $val;
			}
		
			$count = $results['COUNT'];
			$parentid = $type_id[$sub_cate_id]['parentid'];
			$tplObj -> out['softapplist'] = $results['DATA'];
			$tplObj -> out['parentid'] =  $parentid;
			$tplObj -> out['categoryname'] =  $type_id[$sub_cate_id]['name'];
			/*echo '<pre>';
			print_r($parameters);
			exit('</pre>');*/
			$tplObj -> out['page'] =  pagination_arr($page,$count,$limit,10);
			$tplObj -> out['now_page'] = $page;
			$tplObj->out['type'] = 'ctag';
			
			display('item_list2.html');
		break;
	}	
?>
