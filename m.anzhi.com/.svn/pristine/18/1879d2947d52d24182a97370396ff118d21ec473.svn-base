<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
if ($_SERVER['HTTP_HOST'] == 'promotion.anzhi.com') {
    header('location:http://m.anzhi.com');
    exit;
}
isset($_GET['parent_cat_id']) && !empty($_GET['parent_cat_id']) ? ($parent_cat_id = $_GET['parent_cat_id']) : ($parent_cat_id = 1);
$tplObj->out['parent_cat_id'] = $parent_cat_id;
$tplObj->out['type'] = $_GET['type'];
switch($_GET['type']) {
    case 'top':
        $listsize = 20;
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        if ($channel == 'bbg')
        {
            if ($morelist >= $bbg_page)
            {
                //$model = new GoModel();
                //$option = array(
                //  'table' => 'sj_soft',
                //  'where' => array(
                //      'package' => 'cn.goapk.market',
                //      'status' => 1,
                //      'hide' => 1024,
                //      'channel_id' => ",{$channel_info['cid']},"
                //  ),
                //);
                //$result = $model->findOne($option);

                $model = load_model('softlist');
                $softid = $model->getPackageToSoftId("cn.goapk.market");
                $softid = $softid[0];
                if (!empty($softid))
                {
                    $resultanzhi = $model->getsoftinfos($softid, getFilterOption());
                    if (!empty($resultanzhi))
                    {
                        $resultanzhi = $resultanzhi[$softid];
                        $resultanzhi['iconurl'] = getImageHost() . $resultanzhi['iconurl'];
                        $resultanzhi['down_url'] = "download.php?softid={$softid}";
                    }
                }
            }
        }
        if ($channel == 'bbg' && $resultanzhi != '' && $morelist >= $bbg_page)
        {
            if ($morelist == $bbg_page)
            {
                $result = get_softlist('soft.GoGetSoftList', $morelist*$listsize, $listsize - 1, $parent_cat_id, array('ID'=>1,     'GO_METHOD' => 'hotnd'));
            }
            elseif ($morelist > $bbg_page)
            {
                $result = get_softlist('soft.GoGetSoftList', $morelist*$listsize - 1, $listsize, $parent_cat_id, array('ID'=>1,     'GO_METHOD' => 'hotnd'));
            }
        }
        else
        {
            $result = get_softlist('soft.GoGetSoftList', $morelist*$listsize, $listsize, $parent_cat_id, array('ID'=>1,     'GO_METHOD' => 'hotnd'));
        }
        if ($morelist == $bbg_page && !empty($resultanzhi))
        {
            $result['list'] = array_merge(array_slice($result['list'], 0, $bbg_index), array($resultanzhi), array_slice($result['list'], $bbg_index));
        }
        if($parent_cat_id == 1) {
            //软件
            $tplObj->out['seo_type'] = 'top_1';
        }elseif($parent_cat_id == 2) {
            //游戏
            $tplObj->out['seo_type'] = 'top_2';
        }
        $tplObj->out['year'] = date('Y', time());
        $result['list'] = scorehtml($result['list']);
        $tplObj->out['app_top'] = $result['list'];
        $tplObj->out['title'] = '排行';
        $tplObj->out['list_page'] = $result['list_page'];
        $tplObj->out['title'] = '排行';
        if ($_GET['morelist'] >= 1){
            $tplObj->out['morelist'] = $_GET['morelist'];
             $tplObj->display("app_top_ajax.html");
        } else {
            $tplObj->display("app_top.html");
        }
        break;
    
    
    case 'recommend':
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        if ($channel == 'bbg')
        {
            if ($morelist >= $bbg_page)
            {
                //$model = new GoModel();
                //$option = array(
                //  'table' => 'sj_soft',
                //  'where' => array(
                //      'package' => 'cn.goapk.market',
                //      'status' => 1,
                //      'hide' => 1024,
                //      'channel_id' => ",{$channel_info['cid']},"
                //  ),
                //);
                //$result = $model->findOne($option);
                $model = load_model('softlist');
                $softid = $model->getPackageToSoftId("cn.goapk.market");
                $softid = $softid[0];
                if (!empty($softid))
                {
                    $resultanzhi = $model->getsoftinfos($softid, getFilterOption());
                    if (!empty($resultanzhi))
                    {
                        $resultanzhi = $resultanzhi[$softid];
                        $resultanzhi['iconurl'] = getImageHost() . $resultanzhi['iconurl'];
                        $resultanzhi['down_url'] = "download.php?softid={$softid}";
                    }
                }
            }
        }
        if ($channel == 'bbg' && $resultanzhi != '' && $morelist >= $bbg_page)
        {
            if ($morelist == $bbg_page)
            {
                $result = get_softlist('soft.GoGetHomeHot', $morelist*PAGE_LIMITE, PAGE_LIMITE - 1, $parent_cat_id);
            }
            elseif ($morelist > $bbg_page)
            {
                $result = get_softlist('soft.GoGetHomeHot', $morelist*PAGE_LIMITE - 1, PAGE_LIMITE, $parent_cat_id);
            }
        }
        else
        {
            $result = get_softlist('soft.GoGetHomeHot', $morelist*PAGE_LIMITE, PAGE_LIMITE, $parent_cat_id);
        }
        if ($morelist == $bbg_page && !empty($resultanzhi))
        {
            $result['list'] = array_merge(array_slice($result['list'], 0, $bbg_index), array($resultanzhi), array_slice($result['list'], $bbg_index));
        }
        //$result = get_softlist('soft.GoGetHomeHot', $morelist*PAGE_LIMITE, PAGE_LIMITE, $parent_cat_id);
        $result['list'] = scorehtml($result['list']);
        if($parent_cat_id == 1) {
            //软件
            $tplObj->out['seo_type'] = 'recommend_1';
        }elseif($parent_cat_id == 2) {
            //游戏
            $tplObj->out['seo_type'] = 'recommend_2';
        }
        $tplObj->out['morelist'] = $morelist;
        $tplObj->out['app_recommend'] = $result['list'];
        $tplObj->out['title'] = '热门';
        if ($_GET['morelist'] >= 1){
            //if ($_COOKIE['wap']=="concise"){}
            $tplObj->out['morelist'] = $_GET['morelist'];
            $tplObj->display("app_recommend_ajax.html");
        } else {
            $tplObj->display("app_recommend.html");
        }
        break;

    case 'recommend_':
        $result = get_softlist('soft.GoGetHomeHot', 0, 8, 1);
        $result['list'] = scorehtml($result['list']);
        $res = get_softlist('soft.GoGetHomeHot', 0, 6, 2);
        $res['list'] = scorehtml($res['list']);
        
        $tplObj->out['app_recommend1'] = $result['list'];
        $tplObj->out['app_recommend2'] = $res['list'];
        $tplObj->display("app_recommend_.html");
        break;
    case 'recommend1_':
        $result = get_softlist('soft.GoGetHomeHot', 0, 5, 1);
        $result['list'] = scorehtml($result['list']);
        $res = get_softlist('soft.GoGetHomeHot', 0, 5, 2);
        $res['list'] = scorehtml($res['list']);
        
                //获取puid
                $get_puid = $_GET['puid'];
                
                
                
        $tplObj->out['app_recommend1'] = $result['list'];
                $tplObj->out['app_recommend_getpuid'] = $get_puid;
        $tplObj->out['app_recommend2'] = $res['list'];
        $tplObj->display("app_recommend1_.html");
        break;

    case 'classifyapp':
        $sub_cat_id = (int)$_GET['sub_cat_id'];
        $tplObj->out['sub_cat_id'] = $sub_cat_id;
        $order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
        $tplObj->out['order'] = $order;
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        $result = get_softlist('soft.GoGetCategoryAllSoftList', $morelist * PAGE_LIMITE, PAGE_LIMITE, $sub_cat_id, array('ORDER'=> $order, 'EXTRA_OPTION_FIELD' => array('upload_tm','min_firmware', 'parentid','isoffice')));
        $result['list'] = scorehtml($result['list']);
        $tplObj->out['app_classifyapp'] = $result['list'];
        $memcache = GoCache::getCacheAdapter('memcached');
        $type_id = $memcache->get('TYPE_ID');
        $parentid = $type_id[$sub_cat_id]['parentid'];
        $tplObj -> out['parent_cat_id'] =  $parentid;
        $tplObj -> out['parentname'] =  $type_id[$parentid]['name'];
        $tplObj -> out['title'] =  $type_id[$sub_cat_id]['name'];       
        
        if ($_GET['morelist'] >= 1){
            $tplObj->out['morelist'] = $_GET['morelist'];
            $tplObj->display("app_classify_app_ajax.html");
        } else {
            $tplObj->display("app_classify_app.html");
        }
        break;

    case 'classifytag':
        $sub_cat_id = (int)$_GET['sub_cat_id'];
        $sub_tag_id = (int)$_GET['sub_tag_id'];
        $model = new GoModel();
        $option = array(
            'table' => 'sj_tag',
            'where' => array(
                'status' => 1,
                'tag_id' => $sub_tag_id,
            ),
        );
        $res = $model -> findOne($option);
        $tplObj->out['title'] = $res['tag_name'];
        $tplObj->out['sub_tag_id'] = $sub_tag_id;
        
        $order = isset($_GET['order']) ? (int)$_GET['order'] : 1;
        $tplObj->out['order'] = $order;
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        $parameters = array(
            'GET_COUNT' => True,
            'LIST_INDEX_START' => $morelist * PAGE_LIMITE,
            'LIST_INDEX_SIZE' => PAGE_LIMITE,
            'ORDER'=> $order,
            'CATEGORY_ID' => $sub_cat_id,
            'TAG_ID' => $sub_tag_id,
            'VR' => 24,
        );
        //各标签的软件
        $results = gomarket_action('v53.GoGetTagSoftList',$parameters);
        /*echo '<pre>';
        print_r($parameters);
        exit('</pre>');*/
        $result = array();
        foreach ($results['DATA'] as $k => $v) {
            $result[$k] = array( 'softid' => $v[0], 'iconurl' => $v[1], 'softname' => $v[2], 'score' => $v[3], 'msgnum' => $v[4], 'dev_name' => $v[5], 'costs' => $v[6], 'package' => $v[7], 'safe' => $v[8], 'filesize' => $v[9], 'category_id' => $v[10], 'total_downloaded' => $v[11], 'url' => $v[12], 'version_code' => $v[13], 'isoffice' => $v[21]);
        }
        $result = scorehtml($result);
        $tplObj->out['app_classifyapp'] = $result;
        $memcache = GoCache::getCacheAdapter('memcached');
        $type_id = $memcache->get('TYPE_ID');
        $parentid = $type_id[$sub_cat_id]['parentid'];
        if($type_id[$sub_cat_id]['parentid'] == 1) {
            //安卓应用二级分类
            $tplObj -> out['classifytag_seo_type'] = 'classifytag_1';
        }elseif($type_id[$sub_cat_id]['parentid'] == 2) {
            //安卓游戏二级目录
            $tplObj -> out['classifytag_seo_type'] = 'classifytag_2';
        }
        $tplObj -> out['parent_cat_id'] =  $parentid;
        $tplObj -> out['parentname'] =  $type_id[$parentid]['name'];
        $tplObj -> out['sub_cat_id'] = $sub_cat_id;
        $tplObj -> out['categoryname'] =  $type_id[$sub_cat_id]['name'];
        
        if ($_GET['morelist'] >= 1){
            $tplObj->out['morelist'] = $_GET['morelist'];
            $tplObj->display("app_classify_tag_ajax.html");
        } else {
            $tplObj->display("app_classify_tag.html");
        }
        break;

    case 'hanhua':
        /**
         *hanhua_id = 0 表示汉化游戏，对应数据库中的分类id为：暂设24
         *hanhua_id = 1 表示汉化软件，对应数据库中的分类id为：暂设22
        */
        $hanhua_id = (int)$_GET['hanhua_id'];
        if(!isset($hanhua_id) || !in_array($hanhua_id,array(0,1)))
        {
            $hanhua_id = 0 ;
        }
        $tmp_id_info = array('0'=>69,'1'=>67);
        $tmp_title_info = array('0'=>"汉化游戏-安智汉化-安卓市场-Android,安卓,安卓网,安卓游戏,电子市场,国内最专业的Android安卓市场,提供海量安卓软件、安卓游戏、最新汉化软件、APK免费下载",'1'=>"汉化软件-安智汉化-安卓市场-Android,安卓,安卓网,安卓游戏,电子市场,国内最专业的Android安卓市场,提供海量安卓软件、安卓游戏、最新汉化软件、APK免费下载");
        $tplObj->out['hanhua_id'] = $hanhua_id;
        $order = 1;
        // $tplObj->out['order'] = $order;
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        $result = get_softlist('soft.GoGetCategoryAllSoftList', $morelist * PAGE_LIMITE, PAGE_LIMITE, $tmp_id_info[$hanhua_id], array('ORDER'=> $order, 'EXTRA_OPTION_FIELD' => array('upload_tm','min_firmware', 'parentid','isoffice')));
        $result['list'] = scorehtml($result['list']);
        $tplObj->out['app_classifyapp'] = $result['list'];
        $memcache = GoCache::getCacheAdapter('memcached');
        $type_id = $memcache->get('TYPE_ID');
        
        $tplObj -> out['title'] =  $tmp_title_info[$hanhua_id];
        if ($_GET['morelist'] >= 1){
            $tplObj->out['morelist'] = $_GET['morelist'];
            $tplObj->display("app_classify_hanhua_ajax.html");
        } else {
            $tplObj->display("app_classify_hanhua.html");
        }
        break;

    case 'classify':
        if ($parent_cat_id == 1){
            $type = 0;
        } elseif ($parent_cat_id == 2){
            $type = 1;
        } else {
            
        }
        if($_GET['concise'] == 1){
            //简版继续使用老样式
            $res = gomarket_action('soft.GoGetSoftCategoryList', array('TYPE' => $type, 'VR' => 1));
            $tplObj->out['classify'] = $res['DATA'];
        }else{
            //炫版使用分类标签结构
            $res = gomarket_action('v53.GoGetCategoryTag', array('CATE_TYPE' => $type,'VR'=>14));
            $new_res = array();
            //过滤掉没有标签的分类和电子书分类
            foreach($res['CATEGORY_GROUP'] as $val){
                if(!empty($val[3]) && $val[0]!=3){
                    $new_res[] = $val;
                }
            }
            $tplObj->out['classify'] = $new_res;
        }
        if($parent_cat_id == 1) {
            //软件
            $tplObj->out['seo_type'] = 'classify_1';
        }elseif($parent_cat_id == 2) {
            //游戏
            $tplObj->out['seo_type'] = 'classify_2';
        }
        $tplObj->out['title'] = '分类';
        $tplObj->display("app_classify.html");
        break;
    case 'history':
        $softid = (int)$_GET['softid'];
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        $res = gomarket_action('soft.GoGetHistorySoft', array(
                'ID' => $softid,
                'VR' => 2,
                'LIST_INDEX_START' => $morelist * PAGE_LIMITE,
                'LIST_INDEX_SIZE' => PAGE_LIMITE,
                'EXTRA_OPTION_FIELD' => array(
                    'A.update_content'
                ),
            )
        );
        // 将更新说明开头的“系统：”三个字去掉
        foreach ($res['DATA'] as $key => $record) {
            $update_content = $record[11];
            $res['DATA'][$key][11] = preg_replace('/^系统：/', "", $update_content);
        }
        $tplObj->out['applist'] = $res['DATA'];
        if ($_GET['morelist'] >= 1){
            $tplObj->out['morelist'] = $_GET['morelist'];
            $tplObj->display("app_history_ajax.html");
        } else {
            $tplObj->display("app_history.html");
        }
        break;
    case 'share':
    case 'baidu':
    case 'channel':
	case 'chlpkg':
    case 'info':
    case 'sem':
    case 'mxzm':
    case 'semgame':
    case 'acts':
	case 'app':
    case 'chlapp':
        $path = checkurl($_SERVER);
        $share = false;
        $channel_apk = false;

        $from_where = 0; // 1二维码，2极速，3分享链接（除微信），4微信
        $weixin_hint = '<font color="red">微信用户请点击微信右上角<br />选择「在浏览器中打开」</font>';

        if ($_GET['type'] == 'share') {
            $share = true;
        }
        if ($_GET['type'] == 'acts') {
            $tplObj->out['acts'] = true;
        }
        if ($_GET['type'] == 'channel' ) {
            $channel_apk = true;
        }
        //验证短信点击日志
        if ($_GET['from']){
            $log_data = array(
                'ip' => $_SERVER['REMOTE_ADDR'],
                'aid' => $_GET['aid'],
                'time' => time(),
                'from' => $_GET['from'],
                'users' => '',
                'uid' => '',
                'key' => 'message_check'
            );
            permanentlog('message.log', json_encode($log_data));
        }
        
        $tplObj->out['share'] = $share;
        $tplObj->out['weixin_hint'] = $weixin_hint;
        $tplObj->out['path'] = $path;
        
        //session_start();
        $_SESSION['ABI']=3;
        if ($_GET['softid']) {
            $softid = (int)$_GET['softid'];
            $intro = gomarket_action('soft.GoGetSoftDetailCategory', array(
                'ID' => $softid,
                'VR' => 3, 
                'EXTRA_OPTION_FIELD' => array(
                'A.category_id','A.category_name','A.hide','A.status','A.update_content', 'tags'
                ),
            ));
        } elseif ($_GET['package']) {
			if($_GET['package'] == 'com.smzdw'){
				$tplObj->display('smzdw_info.html');
				exit;
			}
            $intro = gomarket_action('soft.GoGetSoftDetailPackage', array(
                'PACKAGE_NAME' => $_GET['package'],
                'VR' => 3,
                'EXTRA_OPTION_FIELD' => array(
                'A.category_id','A.category_name','A.hide','A.status','A.update_content','parentid'
                ),
            ));
            $softid = $intro['ID'];
            $intro['PACKAGENAME'] = $_GET['package'];
        }
        if($softid == 211831 && $_GET['k'] == 'r'){
            $softname = load_config('marketinfo/softname');
            $mark = $_GET['k'];
            if(!empty($softname)){
                $intro['SOFT_NAME'] = load_config('marketinfo/softname');
                $intro['SOFT_VERSION'] = load_config('marketinfo/version');
                $intro['SOFT_VERSION_CODE'] = load_config('marketinfo/version_code');
                $intro['ENAME'] = load_config('marketinfo/ename');
                $intro['SOFT_PROMULGATE_TIME'] = load_config('marketinfo/upload_tm');
                $intro['SOFT_SIZE'] = load_config('marketinfo/filesize');
                $intro['SOFT_DESCRIBE'] = load_config('marketinfo/intro');
                $intro['recommend'] = 1;
                
                $idx = 0;
                $thumb_arr = load_config('marketinfo/thumb');
                foreach($intro['SOFT_SCREENSHOT_URL'] as $id => $val){
                $url_arr = parse_url($val);
                $intro['SOFT_SCREENSHOT_URL'][$id] = 'http://'.$url_arr['host'].$thumb_arr[$idx];
                $idx++;
                }
            }
        }
        
        if(!in_array($intro['PACKAGENAME'],$configs['chl_package'])){
            $channel_apk = false;
        }

        if($softid != 499772 && (empty($intro['SOFT_NAME']) || $intro['status'] ==0 || !in_array($intro['hide'], array(1, 1024)))){
            $soft_model = load_model('soft');
            if ($softid = $soft_model->getRealSoftid($softid)) {
                $intro = gomarket_action('soft.GoGetSoftDetailCategory', 
                    array('ID' => $softid,'VR' => 3,'EXTRA_OPTION_FIELD' => 
                        array('A.upload_tm','B.min_firmware','A.intro','A.category_name','subname','A.category_id','tags',
                            'min_firmware','max_firmware', 'status', 'hide', 'iconurl_72','isoffice','A.update_content'
                )));
            }
            if (empty($intro['SOFT_NAME']) || $intro['status'] ==0 || !in_array($intro['hide'], array(1, 1024))) {
				Header("HTTP/1.1 404 Not Found"); 
                $tplObj->display("search_none.html");
                exit;
            } 
        }
        $soft_size = formatFileSize('',$intro['SOFT_SIZE']);
        $intro['SOFT_SIZE'] = $soft_size;
        $intro['update_content'] = trim($intro['update_content']);
        $intro['SOFT_PROMULGATE_TIMES'] = date('Y-m-d',strtotime($intro['SOFT_PROMULGATE_TIME']));
    
        $intro['update_content_len'] = strlen(trim($intro['update_content']));

		$replace = array("\n","<br/>","<br>","\r\n","\n\r");
		$intro['SOFT_DESCRIBE'] = str_replace($replace, '', $intro['SOFT_DESCRIBE']);    
        $tplObj->out['info'] = $intro;
        
        $i = $k =0;
        $tplObj->out['info']['scorehtml']="";
        $i = floor($tplObj->out['info'][SOFT_STAR] / 2);
        $k = $tplObj->out['info'][SOFT_STAR] % 2;
        for($i1=$i;$i1>0;$i1--){
            $tplObj->out['info']['scorehtml'] .='<img alt="" src="/images/star_01.png">';
        }
        if($k!=0)
            $tplObj->out['info']['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
        if(($i+$k)<5) {
            for($i2=(5-$i-$k);$i2>0;$i2--){
                $tplObj->out['info']['scorehtml'] .='<img alt="" src="/images/star_03.png">';
            }   
        }
        $tplObj->out['info']['SOFT_DESCRIBE'] = nl2br($tplObj->out['info']['SOFT_DESCRIBE']);
        if($tplObj->out['info']['PACKAGENAME']=="cn.goapk.market")
            $tplObj->out['info']['scorehtml'] = '<img alt="" src="/images/star_01.png"><img alt="" src="/images/star_01.png"><img alt="" src="/images/star_01.png"><img alt="" src="/images/star_01.png"><img alt="" src="/images/star_01.png">';
        $package = $tplObj->out['info']['PACKAGENAME'] ? $tplObj->out['info']['PACKAGENAME'] : $_GET['package'];
        $inofret = gomarket_action('soft.GoGetSuggest',array(
                "PACKAGE_NAME" => $package,
                "LIST_INDEX_START" => 0,
                "LIST_INDEX_SIZE" => 8,
                'EXTRA_OPTION_FIELD' => array(
                    'isoffice',
                )
            )
        );
    
        if(empty($_GET['more'])){
            if(strstr($_SERVER['REQUEST_URI'],'more')){
                $tplObj->out['imgurl'] = substr($_SERVER['REQUEST_URI'],0,-1)."1";  
            }else{
                $tplObj->out['imgurl'] = $_SERVER['REQUEST_URI']."&more=1";
            }
            $tplObj->out['imgtype'] = "1";
        } else {
            $tplObj->out['imgurl'] = substr($_SERVER['REQUEST_URI'],0,-1)."0";  
            $tplObj->out['imgtype'] = "0";
        }
        $CommentList = gomarket_action('comment.GoGetCommentList',array("ID" => $softid, 'GET_COUNT' => True, "LIST_INDEX_START" => 0,"LIST_INDEX_SIZE" => 5, 'VR' => 1));
        $historysoft = gomarket_action('soft.GoGetHistorySoft',array("ID"=>$softid,'GET_COUNT' => True,'EXTRA_OPTION_FIELD' => array(
            'isoffice',
        )));
        $info['update_content_len'] = strlen($info['update_content']);
        $tplObj->out['commentlist'] = scorehtml2($CommentList['DATA']);
        $tplObj->out['historysoft_total'] = count($historysoft['DATA']);
        $tplObj->out['historysoft'] = array_slice($historysoft['DATA'],0,3);
        $tplObj->out['DATA_LIKE'] = $inofret['DATA_LIKE']; 
        $tplObj->out['title'] = $tplObj->out['info']['SOFT_NAME'];
        
        //ANZHI_OWN
        $key = array('ANZHI_OWN');
        $model = load_model('pu_config');
        $configs = $model->getConfig($key);
        $config = json_decode($configs[$key[0]][1], true);
        $tplObj->out['share_soft'] = $config['share_soft'];
        if (isset($_GET['aid'])) {
            $tplObj->out['aid'] = $_GET['aid'];
        }
        $tplObj->out['from'] = '';
        if (isset($_GET['from'])) {
            $tplObj->out['from'] = $_GET['from'];
        }
        if($intro['PACKAGENAME'] == 'cn.goapk.market'){
            $tplObj->out['ID'] = $intro['ID'];
            if ($_GET['type'] == 'baidu') {
                if (isset($_GET['aid'])) {
                    $tplObj->display('anzhi_app_baidu.html');
                } else {
                    $tplObj->display('anzhi_app_new.html');
                }
            }elseif ($_GET['fid']) {
                $common_jump_opts = array(
                    'table' => 'sj_common_jump',
                    'where' => array(
                        'content_type' => 3,
                        'feature_id' => $_GET['fid'],
                        'page_flag' => '0x00150000',
                        'status'=>1,
                    ),
                );
                $common_jump_feature = $model->findOne($common_jump_opts);
                if($common_jump_feature)
                {
                    $share_common_jump_id = $common_jump_feature['id'];
                }
                else//添加
                {
                    $new_data = array(
                        'content_type' => 3,
                        'feature_id' => $_GET['fid'],
                        'page_flag' => '0x00150000',
                        'create_at' => time(),
                        'update_at' => time(),
                        'status' => 1,
                        '__user_table' => 'sj_common_jump'
                    );
                    $ret =  $model->insert($new_data);
                    if($ret)
                    {
                        $share_common_jump_id = $ret['id'];
                    }
                }
                $tplObj->out['share_common_jump_id'] = $share_common_jump_id;
                $tplObj->display('anzhi_app_feature.html');
            } else{
                $tplObj->display('anzhi_app.html');
            }
            
        }elseif($intro['PACKAGENAME'] == 'com.yuezhan'){
            $tplObj->display('battle_info.html');
        }else{
			$tplObj -> out['check_html']  =  "/post_".substr(md5($_GET['package']),0,4)."_".$_GET['package'].".html";
            $tplObj -> out['package']     =   $_GET['package'];
            if ($_GET['type'] == 'sem') {
                $tplObj->out['sem'] = true;
                $tplObj->display('sem.html');
            }elseif ($_GET['type'] == 'mxzm') {
                $tplObj->display('mx_app_info.html');
            }elseif ($_GET['type'] == 'semgame') {
                $tplObj->display('semgame.html');
			}elseif ($_GET['type'] == 'chlpkg') {
				$tplObj->out['chl_pkg_url_id'] = $_GET['chl_pkg_url_id'];
				$tplObj->display('channel_pkg_info.html');
            }elseif ($_GET['type'] == 'chlapp') {
                $tplObj->out['chl_cid'] = $_GET['chl_cid'];
                $tplObj->display('app_chl_info.html');
            }else{
                if($channel_apk){
                    $tplObj->display('app_channel_info.html');
                }else{
                    $content_option = array(
                        'where' => array(
                            'status' => 1,
                            'passed' => 2,
                            'package' => $_GET['package'],
                            'title' => array('exp', "!=''"),
                        ),
                        'order' => 'id desc',
                        'cache_time' => '600',
                        'limit' => 3,
                        'field' => 'id,package,title,show_style,explicit_pic',
                        'table' => 'sj_soft_content_explicit'
                    );
                    $content_info = $model->findAll($content_option);
                    foreach($content_info as $key => $val){
                        $explicit_pic = json_decode($val['explicit_pic'], true);
                        if($val['show_style'] == 2){
                            $explicit_pic = array_slice($explicit_pic, 1);
                        }
                        $content_info[$key]['explicit_pic'] = $explicit_pic;
                    }
                    /*echo '<pre>';
                    print_r($content_info);
                    exit('</pre>');*/
                    $tplObj -> out['ImageHost'] = getImageHost();
                    $tplObj -> out['content_info'] = $content_info;
                    $tplObj->display('app_info.html');
                }   
            }
        }
        break;
        
    case 'comment':
        $tplObj->out['referer'] = $_SERVER['HTTP_REFERER'];
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        $softid = isset($_GET['softid']) && !empty($_GET['softid']) ? $_GET['softid'] : '';
        $page = 15;
        $comment = gomarket_action('comment.GoGetCommentList', array("ID" => $softid, 'GET_COUNT' => True, "LIST_INDEX_START" => $morelist * $page,"LIST_INDEX_SIZE" => $page, 'VR' => 1));
            $i = $k =0;
            $comment['scorehtml']="";
            $i = floor($comment['AVERAGE_SCORE'] / 2);
            $k = $comment['AVERAGE_SCORE'] % 2;
            for($i1=$i;$i1>0;$i1--){
                $comment['scorehtml'] .='<img alt="" src="/images/star_01.png">';
            }
            if($k!=0)
                $comment['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
            if(($i+$k)<5) {
                for($i2=(5-$i-$k);$i2>0;$i2--){
                    $comment['scorehtml'] .='<img alt="" src="/images/star_03.png">';
                }   
            }
            foreach($comment['DATA'] as $key => $value) {
                $i = $k =0;
                $comment['DATA'][$key]['scorehtml']="";
                $i = floor($value[1] / 2);
                $k = $value[1] % 2;
                for($i1=$i;$i1>0;$i1--){
                    $comment['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
                }
                if($k!=0)
                    $comment['DATA'][$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
                if(($i+$k)<5) {
                    for($i2=(5-$i-$k);$i2>0;$i2--){
                        $comment['DATA'][$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
                    }   
                }
            }
        $max = max($comment['STARS']);
        $arr[0] = round(($comment['STARS'][0] / $max) * 100) . '%';
        $arr[1] = round(($comment['STARS'][1] / $max) * 100) . '%';
        $arr[2] = round(($comment['STARS'][2] / $max) * 100) . '%';
        $arr[3] = round(($comment['STARS'][3] / $max) * 100) . '%';
        $arr[4] = round(($comment['STARS'][4] / $max) * 100) . '%';
        $comment['percentage'] = $arr;
        $tplObj->out['comlist'] = $comment;
//          $tplObj->out['morelist'] = $morelist * $page;
        if ($_GET['morelist'] >= 1){
            $tplObj->out['morelist'] = $_GET['morelist'];
            $tplObj->display('comment_ajax.html');
        } else {
            $tplObj->display('comment.html');
        }
        break;

    default :
        /*
        $morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
        //$result = get_softlist('soft.GoGetHomeNew', $morelist*PAGE_LIMITE, PAGE_LIMITE, $parent_cat_id);
        $result = get_softlist('soft.GoGetHomeNew', $morelist*20, 20, $parent_cat_id);
        $result['list'] = scorehtml($result['list']);
        $tplObj->out['app_new'] = $result['list'];
        $tplObj->out['title'] = '最新';
        $tplObj->out['list_page'] = $result['list_page'];
        if ($_GET['morelist'] >= 1){
            //if ($_COOKIE['wap']=="concise"){}
            $tplObj->out['morelist'] = $_GET['morelist'];
             $tplObj->display("app_new_ajax.html");
        } else {
            $tplObj->display("app_new.html");
        }*/
		Header("HTTP/1.1 404 Not Found"); 
        $tplObj->display("search_none.html");
        break;

}


function scorehtml($result){
    foreach($result as $key => $value) {
        $i = $k =0;
        $result[$key]['scorehtml']="";
        $i = floor($value[score] / 2);
        $k = $value[score] % 2;
        for($i1=$i;$i1>0;$i1--){
            $result[$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
        }
        if($k!=0)
            $result[$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
        if(($i+$k)<5) {
            for($i2=(5-$i-$k);$i2>0;$i2--){
                $result[$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
            }   
        }
    }
    return  $result;
}
function scorehtml2($result, $index=1){
    foreach($result as $key => $value) {
        $i = $k =0;
        $result[$key]['scorehtml']="";
        $i = floor($value[$index] / 2);
        $k = $value[$index] % 2;
        for($i1=$i;$i1>0;$i1--){
            $result[$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
        }
        if($k!=0)
            $result[$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
        if(($i+$k)<5) {
            for($i2=(5-$i-$k);$i2>0;$i2--){
                $result[$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
            }   
        }
    }
    return  $result;
}

function checkurl($server){
    $referer = substr($server['HTTP_REFERER'], strpos($server['HTTP_REFERER'], $server['HTTP_HOST']) + strlen($server['HTTP_HOST']));
    $str = '';
    switch ($referer){
        case '/':
            break;
        case '/index.php?type=new':
            $str = '<a href="'.$server['HTTP_REFERER'].'">最新  &gt;    </a>';
            break;
        case '/inapp.php':
            $str = '<a href="'.$server['HTTP_REFERER'].'">必备  &gt;    </a>';
            break;
        case '/app.php?type=recommend&parent_cat_id=1':
            $str = '<a href="'.$server['HTTP_REFERER'].'">应用  &gt;    </a><a href="'.$server['HTTP_REFERER'].'">热门  &gt;    </a>';
            break;
        case '/app.php?parent_cat_id=1':
            $str = '<a href="app.php?type=recommend&parent_cat_id=1">应用       &gt;    </a><a href="'.$server['HTTP_REFERER'].'">排行      &gt;    </a>';
            break;
        case '/app.php?type=recommend&parent_cat_id=2':
            $str = '<a href="'.$server['HTTP_REFERER'].'">游戏  &gt;    </a><a href="'.$server['HTTP_REFERER'].'">热门  &gt;    </a>';
            break;
        case '/app.php?parent_cat_id=2':
            $str = '<a href="app.php?type=recommend&parent_cat_id=2">游戏       &gt;    </a><a href="'.$server['HTTP_REFERER'].'">排行      &gt;    </a>';
            break;                          
        default:
            break;
    }
    if (preg_match("/\/subject.php\?type=subject_app&subject_id=[\d]/", $server['HTTP_REFERER']) && $str == ''){
        $str = '<a href="subject.php">专题  &gt;    </a>';
    }
    if (preg_match("/\/app.php\?type=classifyapp&parent_cat_id=1&sub_cat_id=[\d]/", $server['HTTP_REFERER']) && $str == ''){
        $str = '<a href="app.php?type=recommend&parent_cat_id=1">应用       &gt;    </a><a href="'.$server['HTTP_REFERER'].'">类别      &gt;    </a>';
    }
    if (preg_match("/\/app.php\?type=classifyapp&parent_cat_id=2&sub_cat_id=[\d]/", $server['HTTP_REFERER']) && $str == ''){
        $str = '<a href="app.php?type=recommend&parent_cat_id=2">游戏       &gt;    </a><a href="'.$server['HTTP_REFERER'].'">类别      &gt;    </a>';
    }
    $str = '应用详情';  // 已经不用如果要用把此行删除即可
    return $str;
}
