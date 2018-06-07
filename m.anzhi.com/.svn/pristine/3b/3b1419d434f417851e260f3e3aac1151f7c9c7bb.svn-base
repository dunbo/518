<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
//session_start();
if (!isset($_GET['gcid']) && !isset($_GET['package'])) {
    exit;
}

$_SESSION['GAME_CID'] = intval($_GET['gcid']);
$info = getGameChannelInfo($_GET['gcid'], $_GET['package']);
if (!$info) {
    //没有渠道包，跳转到404
    header('HTTP/1.1 404 Not Found');
    exit;
}

$softid = $info['softid'];

$tolog = array(
    'softid' => $softid,
    'userid' => isset($_SESSION["USER_ID"]) ? $_SESSION["USER_ID"] : GO_UID_DEFAULT,
    'action' => 10,//自己定的下载日志记录号
    'submit_tm' => time(),
    'package' => $_GET['package'],
    'device' => $_SERVER['DEVICE'],
    'channel' => defined('CHL') ? CHL : $chl_code,
    'ip' => onlineip(),
    'refer' => $_SERVER['HTTP_REFERER'],
    'download_type' => 0,
    'gcid' => $_GET['gcid'],
);

$h = date('H');

if($_GET['from']=='cpd'){
    permanentlog(date("Y-m-d").'_install_log', json_encode($tolog),'','mm.anzhi.com');
}else{
    permanentlog('install_log_'.$h.'.json', json_encode($tolog));
}

$url = 'http://mm.apk.anzhi.com/'. base64_encode($info['url']). '?type=base64';
//$url = getApkHost().$info['url'];
toLocation($url);
exit;

function get_short_soft_info($softid)
{
    static $infos = array();
    $model = new GoModel();
    if (!isset($infos[$softid])) {
        $option = array(
            'table' => 'sj_soft',
            'where' => array(
                'softid' => $softid
            ),
            'field' => 'intro, update_content, softname, upload_tm, last_refresh'
        );
        $infos[$softid] = $model->findOne($option); 
    }
    return $infos[$softid];
}
