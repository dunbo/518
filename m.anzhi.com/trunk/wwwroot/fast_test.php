<?php
/* 极速下载
** 浏览器下载安智市场，文件名：Anzhi_安智软件ID_其它值_flag.apk
** 文件名含义：flag : 0无操作，只进详情，1进详情打开分享对话框，2进详情下载, 3进活动页面, 4来自搜狗，其它值：当flag为3时为活动ID，当flag为4时为搜狗的下载地址映射ID
** 支持参数：puid（为推广链，选填），如果有传puid，则下载此puid的市场安装包，否则下载最新的安智市场包
** 支持参数：type（使用情景，必填，type=details表示极速下载或分享市场包，type=action表示活动，type=sogou表示搜狗合作）
** type=details支持参数：softid或package（二选一必填），share（传此参数为分享，选填）
** type=action支持参数：aid（为活动id，必填）
** type=launch支持参数：id（为通用跳转id，必填）
** type=sogou支持参数：package（第三方包名，必填），appname（第三方软件名称，必填），downurl（第三方下载地址，必填），icon（第三方icon地址，必填），vn（第三方版本名称，必填），size（第三方apk大小，必填），update_time（第三方apk包更新时间，必填）
*/

if (isset($_GET['fdtype']) && $_GET['fdtype'] == 'sogou' && isset($_GET['softid'])) {
    //搜狗特殊调整，所有资源为极速下载地址，针对客户端的请求自动跳转成实际软件下载地址
    header("location: http://www.anzhi.com/dl_app.php?s={$_GET['softid']}&channel=sogou");
    exit;
}

include_once (dirname(realpath(__FILE__)).'/init.php');
$log_action = 10;
if (isset($_GET['log_action'])) {
    $log_action = 100;
}
// 下载文件命名的第一个变量，当是极速下载或分享时，此值应被置为softid，其它情况为空
$first_value = '';
// 下载文件命名的第二个变量，根据flag变化
$second_value = '';

// 是否下载推广链的安智市场，默认下载最新版本的安智市场
$puid = isset($_GET['puid']) ? $_GET['puid'] : 0;

if (isset($_GET['type'])) {
    $type = $_GET['type'];
} else {
    $type = 'details';
}
$version_code = $_SESSION['VERSION_CODE'];
unset($_SESSION['VERSION_CODE']);
$flag_4_map = array(
    'sogou' => 'ce78d7e83356',
    'sogoufd2' => '14e70bd83746',
    'sogoufd3' => 'b18fbc443751',
    'baizhu' => '0a814db43941',
    'ydss' => '80dc6c8e4003',
);
$extent_str = '';
switch ($type) {
    case 'details':
        // 判断有没有其他的参数再决定情景
        if (isset($_GET['share']) && $_GET['share']) {
            // 情景：打开分享窗口
            $flag = 1;
        } else if (isset($_GET['partner']) && isset($flag_4_map[$_GET['partner']])){
            // 情景：搜狗合作
            $flag = 4;
        } else {
            // 情景：极速下载
            $flag = 2;
        }
        // 检查是否传够参数，否则退出
        if ($flag == 1 || $flag == 2) {
            // 如果是分享或极速下载，则需要传软件id或包名
            if (!isset($_GET['softid']) && !isset($_GET['package'])) {
                exit;
            }
        } else if ($flag == 4) {
            // 检查是否传够参数，否则退出
            if (!isset($_GET['package']) || !isset($_GET['appname']) || !isset($_GET['downurl'])
                || !isset($_GET['icon']) || !isset($_GET['vn'])
                || !isset($_GET['size']) || !isset($_GET['update_time'])) {
                exit;
            }
            if (isset($_GET['softid'])) {
                unset($_GET['softid']);//以package为准
            }
            // 搜狗合作下载的是推广链，内部指定puid
            $puid = SOGOU_COOPERATE_PUID;
    //        $_GET['chl_cid'] = 'a915c5973042';
            if (isset($flag_4_map[$_GET['partner']])) {
                $_GET['chl_cid'] = $flag_4_map[$_GET['partner']];
            }
            $popularapk['pkg_name'] = 'cn.goapk.market';
            $popularapk['puid'] = $puid;
        }
        break;
    case 'action':
        // 情景：活动
        $flag = 3;
        // 检查是否传够参数，否则退出
        if (!isset($_GET['aid'])) {
            exit;
        }
        $second_value = $_GET['aid'];
        break;
    case 'launchact':
        // 情景：活动，且带有参数actsid，会在安装后自动打开指定活动页面并传递$_GET['actsid']参数给活动页面
        $flag = 8;
        if (!isset($_GET['aid']) || !isset($_GET['actsid'])) {
            exit;
        }
        $second_value = $_GET['aid'];
        $actsid = $_GET['actsid'];
        break;
    case 'invite':
        // 情景：邀请安装
        $flag = 5;
        if (!isset($_GET['uid'])) {
            exit;
        }
        $second_value = $_GET['uid'];
        break;
    case 'webpage':
        // 情景：分享网页
        $flag = 6;
        if (!isset($_GET['webpage_id'])) {
            exit;
        }
        $second_value = $_GET['webpage_id'];
        break;
    case 'launch':
        // 情景：分享网页
        $flag = 7;
        if (!isset($_GET['id'])) {
            exit;
        }
        $second_value = $_GET['id'];
        break;
    case 'apptaskdetails':
        // 情景：分享网页，进入软件任务详情
        $flag = 9;
        if (!isset($_GET['id'])) {
            exit;
        }
        $second_value = $_GET['id'];        
    break;
    case 'outsidejump':
        // 外投首页
        $flag = 10;
        if (!isset($_GET['id'])) {
            exit;
        }
        $extent_str = $_GET['id'];
		$second_value = '';
    break;

    case 'ota':

    break;

    default:
        exit;
}

$ip = onlineip();
$threshold = 2000;//下载限制

//刷量判断
if (isset($_GET['softid']) || isset($_GET['package'])) {
    $key = isset($_GET['softid']) ? $_GET['softid'] : $_GET['package'];
    $tplObj->out['issoftid'] = isset($_GET['softid']) ? 1 : 0;
    $tplObj->out['key'] = $key;
    $tplObj->out['msg'] = "您当前对这个软件的下载操作过于频繁，被视为恶意刷下载量，需要输入安全验证后才能下载。";

    list($msec, $sec) = explode(' ', microtime());
    $msec = substr($msec, 2);
    $tplObj->out['rand'] = $msec;

    if (isset($_POST['codedownid'])) {
        go_require_once(dirname(realpath(__FILE__)).'/checkcode/config.php');
        if(formcheck($_POST['codedownid'])==false) {
            $tplObj->out['msg'] = "验证码错误，请重新输入";
            $tplObj->display ( 'yanzhengma.html' );
            exit;
        } else {

        }
    } else {
        $is_ip_banned = isIpBanned($ip, md5($key), $threshold);
        if ($is_ip_banned) {
            $tplObj->display ('yanzhengma.html');
            exit;
        }
    }
}
if (!empty($_SESSION['actid'])) {
    $actid = $_SESSION['actid'];
}
if (!empty($_SESSION['phone'])) {
    $phone = $_SESSION['phone'];
}

// 如果传的是package，需最终转成softid
if (isset($_GET['softid'])) {
    $softid = $_GET['softid'];
} else if (isset($_GET['package'])) {
    $softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $_GET['package']));
    if (empty($softinfo['ID'])) {
        $softid = 0;//无效的softid，与客户端约定好，就定为0
    } else {
        $softid = $softinfo['ID'];
    }
} else {
    $softid = 0;
}

if ($flag == 4) {
    // 尝试获得搜狗的下载地址映射ID
    $model = new GoModel();
    $option = array(
        'table' => 'sogou_cooperate_download',
        'where' => array('downurl' => $_GET['downurl']),
        'cache_time' => 1200
    );
    $cooperate_apk = $model->findOne($option);
    if ($cooperate_apk) {
        $cooperate_id = $cooperate_apk['id'];
        $update_time = trim($cooperate_apk['update_time']);
        $get_update_time = strtotime(trim($_GET['update_time']));
        // 比较一下更新时间，如果不一样，直接更新
        if ($update_time != $get_update_time) {
            $where = array(
                'downurl' => $_GET['downurl']
            );
            $data = array(
                '__user_table' => 'sogou_cooperate_download',
                'package' => $_GET['package'],
                'appname' => $_GET['appname'],
                'icon' => $_GET['icon'],
                'vn' => $_GET['vn'],
                'vc' => $_GET['vc'],
                'size' => $_GET['size'],
                'update_time' => $get_update_time
            );
            $model->update($where, $data);
        }
    } else {
        // 新建
        $data = array(
            '__user_table' => 'sogou_cooperate_download',
            'package' => $_GET['package'],
            'appname' => $_GET['appname'],
            'downurl' => $_GET['downurl'],
            'icon' => $_GET['icon'],
            'vn' => $_GET['vn'],
            'vc' => $_GET['vc'],
            'size' => $_GET['size'],
            'update_time' => $get_update_time
        );
        $cooperate_id = $model->insert($data);
        if (!$cooperate_id) {
            exit;
        }
    }
    $second_value = $cooperate_id;
}
$apk_md5 = "";
$use_zip_comment = false;
if(!empty($_GET['chl_cid'])) {
    $model = new GoModel();
    $chl_cid = $_GET['chl_cid'];
    $model = new GoModel();
    //获取渠道cid
    $option = array(
        'table' => 'sj_channel',
        'where' => array(
            'chl_cid' => $chl_cid
        ),
        'field' => 'cid,chl,inputtext',
        'cache_time' => 864000
    );
    $channel_cid = $model->findOne($option);
    if ($chl_config_str = $channel_cid['inputtext']) {
        $chl_config_str = str_replace("\r", '', $chl_config_str);
        $chl_config_str = str_replace(" ", '', $chl_config_str);
        $chl_config_str = str_replace("\n", '&', $chl_config_str);
        parse_str($chl_config_str, $chl_config);
        if ($chl_config['IS_ZIP_COMMENT_FAST_DOWNLOAD'] == 'true') {
            $use_zip_comment = true;
        }
    }

    $market_package = 'cn.goapk.market';
    if(!empty($channel_cid['cid'])){
        $url_option = array(
            'table' => 'sj_market',
            'where' => array(
                'cid' => $channel_cid['cid']
            ),
            'order' => 'id desc',
            'field' => 'id,apkurl,apksize,md5_file, real_version_code',
//            'cache_time' => 1800
        );
        
//        if ($chl_cid == 'ce78d7e83356' || $chl_cid == '14e70bd83746' || $chl_cid == '2c9225283150' || $chl_cid=='935f9f6a466') {
  //          $url_option['where']['version_code'] = array('exp', "=(SELECT max(real_version_code) real_version_code from sj_market where cid={$channel_cid['cid']} and status=1)");
    //        $apk_url_res = $model->findAll($url_option);
      //      $apk_url = $apk_url_res[array_rand($apk_url_res)];
       // } else {
            $apk_url = $model->findOne($url_option);
      //  }

        if ($apk_url['real_version_code'] >=6470) {
            $use_zip_comment = true;
        }
        $chl = $channel_cid['chl'];
        if($apk_url){
            $market_download_url = $apk_url['apkurl'];
            $apk_md5 = $apk_url['md5_file'];
            if (isset($_GET['key_word']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Baiduspider-ads') === false) {
                $key_word = $_GET['key_word'];
                $log_arr = array(
                    'key_word'=>$key_word,
                    'chl'=>$channel_cid['chl'],
                    'time'=>time(),
                    'ip'=>$_SERVER["REMOTE_ADDR"],
                    'browser'=>$_SERVER['HTTP_USER_AGENT'],
                    'type'=>$_GET['atype']
                );
                permanentlog('ad_download_log.json', json_encode($log_arr));
            }
        }
    }
} elseif (!$puid) {
    // 快速下载的包为安智市场的包，不是传进来的softid，所以记日志是安智市场的下载
    $intro = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => 'cn.goapk.market', 'EXTRA_OPTION_FIELD'=>array('url', 'md5_file','package', 'category_id')));
    if (!$intro) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }
    $market_softid = $intro['ID'];
    $market_package = $intro['package'];
    $category = $intro['category_id'];
    $market_download_url = $intro['url'];
    $apk_md5 = $intro['md5_file'];
} else {
    $market_softid = 0;
    // 下载推广链的安智市场
    $model = new GoModel();
    $option = array(
        'table' => 'pu_pupolarlink',
        'where' => array('puid' => $puid , 'status' => 1),
        'cache_time' => 1200,
    );
    $popularapk = $model->findOne($option);
    if (!$popularapk) {
        exit;
    }
    $market_download_url = $popularapk['url'].$popularapk['pkg_name'];
}

//$mark_id = $market_softid ? $market_softid : $puid;
if (!$second_value) {
    // 换成market_softid或者puid，好看一点
    $second_value = $market_softid ? $market_softid : $puid;
}

$h = date("H");
$dltime = time();
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$channel = $_GET['channel'];

if (!$is_ip_banned) { // 如果不是ipbanned才记录日志
    if (!$puid) {
        if($chl){
            $the_chl = $chl;
        }else{
            $the_chl = defined('CHL') ? CHL : $chl_code;
        }
        $tolog = array(
            'softid' => $market_softid,
            'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
            'action' => $log_action,//自己定的下载日志记录号
            'submit_tm' => $dltime,
            'package' => $market_package,
            'device' => $_SERVER['DEVICE'],
            'channel' => $the_chl,
            'ip' => $ip,
            'category' => $category,
            'refer' => $_SERVER['HTTP_REFERER'],
            'from_where' => 'fast_download'
        );
        if (!empty($actid)) {
            $tolog['activity'] = array(
                'actid' => $actid,
            );
            if (!empty($phone)) {
                $tolog['activity']['phone'] = $phone;
            }
        }
        permanentlog('install_log_'.$h.'.json', json_encode($tolog));
    } else {
        $tolog = array(
            'action' => 'link',
            'dl_time' => $dltime,
            'type' => 'popularize',
            'apk' => $popularapk['pkg_name'],
            'ip' => $ip,
            'puid' => $popularapk['puid'],
            'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ''
        );
        permanentlog('popularize_'.$h.'.json', json_encode($tolog));
    }
}

// 给下载文件命名
if ($flag == 1 || $flag == 2) {
    $first_value = $softid;
} else {
    $first_value = '';
}

$actsid = $_GET['actsid'];

$new_channel = array(
    '39a9371f3326' => 1,
    'ce78d7e83356' => 1,
    '14e70bd83746' => 1,
    'b18fbc443751' => 1,
    '755e5f893959' => 1,
);

$is_newapkpath = (isset($new_channel[$_GET['chl_cid']]) || $use_zip_comment)? true : false;

define("FD_DOMAIN","http://lw.apk.anzhi.com");

$_SESSION['VERSION_CODE'] = $version_code;
$values = array($first_value, $second_value, $flag);
if ($flag == 8) {
    $values[] = $actsid;
}elseif($flag == 5){
    $values[] = isset($_GET['rewardtype']) ? $_GET['rewardtype'] : 0;//4
    $values[] = isset($_GET['versionCode']) ? $_GET['versionCode'] : 0;//5
}elseif($flag == 3){
    $values[] = isset($_GET['from']) ? $_GET['from'] : 0;//4
    $values[] = isset($_GET['actiontype']) ? $_GET['actiontype'] : 0;//5
}else{
    if(isset($_GET['from'])) {
        $values[] = $_GET['from'];
    }
}

if ($type == 'ota') {
    $host = getapkhost();
    header("location: {$host}/{$market_download_url}");
    exit;
}

if(empty($is_newapkpath)) {
    $url = APK_DOWNLOAD_DOMAIN . "/Anzhi_".implode('_',$values).".apk?src={$market_download_url}";
} else {
    $url = FD_DOMAIN . getPath($values, $market_download_url,$extent_str);
}

function getPath($array,$market_download_url,$extent_str='') {
    global $apk_md5; 
    $str = "Anzhi_".implode('_',$array).".apk\n".$apk_md5;
	if($extent_str){
		$str .= "\n".$extent_str;
	}
    $tmp = pack("S",strlen($str));
    $str = $tmp.$str;
    //return '/create_apk.php?remove=2&append='.urlencode($str);
    return $market_download_url.'?remove=2&append='.urlencode($str);
}

header("location: {$url}");


