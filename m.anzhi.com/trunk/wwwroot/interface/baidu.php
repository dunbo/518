<?php
//百度回调接口
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('_ROOT', dirname(realpath(__FILE__)). DS.'..'.DS. '..');
define('GO_CONFIG_DIR', _ROOT. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_MODEL_DIR', _ROOT. DS. 'newgomarket.goapk.com'. DS. 'model');
include_once _ROOT. DS. 'GoPHP'. DS. 'Startup.php';

$dir = '/data/att/permanent_log/baidu/'. date('Y-m-d');
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$file = $dir. '/docids.log';
$raw = file_get_contents('php://input');
file_put_contents($file, date('Y-m-d H:i:s').'|'.$raw. "\n", FILE_APPEND);
$json = json_decode($raw, true);
$model = new GoModel();
$now = time();
foreach ($json['data'] as $val) {
    $option = array(
        'table' => 'baidu_docid_map',
        'where' => array(
            'softid' => $val['appid'],
        )
    );
    $res = $model->findOne($option);
    
    if (!$res) {
        //数据库不存在的记录，则创建
        $data = array(
            'softid' => $val['appid'],
            'docurl' => '',
            'status' => 1,
            'flag' => 1,
            'create_at' => $now,
            'update_at' => $now,
            'memo' => $val['memo'],
            '__user_table' => 'baidu_docid_map'
        );
        if ($val['docid']) {
            //带docid 则入库
            $data['docid'] = $val['docid'];
        } else {
            $data['failed_count'] = 1;
        }
        $model->insert($data);
    } else {
        $where = array(
            'id' => $res['id']
        );
        if (empty($res['docid'])) {
            //数据库存在记录，但是没有回调的docid
            if ($val['docid']) {
                $data = array(
                    'docid' => $val['docid'],
                    'update_at' => $now,
                    'memo' => $val['memo'],
                    'flag' => 1,
                    '__user_table' => 'baidu_docid_map'
                );
                $model->update($where, $data);
            } else {
                $data = array(
                    'update_at' => $now,
                    'memo' => $val['memo'],
                    'failed_count' => array('exp', 'failed_count+1'),
                    '__user_table' => 'baidu_docid_map'
                );
                $model->update($where, $data);
            }
        } else {
            if ($val['docid'] == $res['docid']) {
                //可能进行了同版本替包，但是docid没变，原数据设置为无效
                if (!checkBaiduDocid($val['docid'], $val['appid'])) {
                    $data = array(
                        'flag' => 0,
                        'update_at' => $now,
                        'memo' => 'file md5 match error',
                        '__user_table' => 'baidu_docid_map'
                    );
                    $model->update($where, $data);                
                }
            } else {
                //替换了docid ，清除docurl
                $data = array(
                    'docid' => $val['docid'],
                    'flag' => 1,
                    'update_at' => $now,
                    'memo' => '',
                    'docurl' => '',
                    '__user_table' => 'baidu_docid_map'
                );
                $model->update($where, $data);                
            }
        }
    }
}

echo json_encode(array('ret' => 0, 'memo' => ''));


function checkBaiduDocid($docid, $softid)
{
    if (empty($docid)) return false;

    $baidu_model = load_model('baiduApi');
    $baidu_model->from = '1014104p';
    $baidu_model->token = 'anzhi-yifu';
    $baidu_model->url_key = 'download_inner';

    $model = new GoModel();
    $option = array(
        'table' => 'sj_soft_file',
        'where' => array(
            'package_status' => 1,
            'softid' => $softid
        ),
    );
    $file_info = $model->findOne($option);
    $baidu_info = $baidu_model->getDetailByDocid($docid);
    $flag = ($file_info['md5_file'] == $baidu_info['md5']);

    return $flag;
}