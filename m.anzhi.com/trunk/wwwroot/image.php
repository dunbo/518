<?php
include 'init.php';
$package = $_GET['package'];
$src = '/images/ic_app_default.png';
if ($package) {
    $model = new GoModel();

    $option = array(
        'table' => 'sj_soft AS A',
        'where' => array(
            'A.package' => $package,
            'B.package_status' => array('exp', '>0')
        ),
        'join' => array(
            'sj_soft_file AS B' => array(
                'on' => array('A.softid', 'B.softid')
            )
        ),
        'order' => 'A.softid desc, B.id desc',
        'field' => 'B.iconurl, B.iconurl_72, B.iconurl_125',
        'cache_time' => '1800'
    );



    $data = $model->findOne($option);
    if ($data) {
        $src = $data['iconurl_125'];
        if (!$src) $src = $data['iconurl_72'];
        if (!$src) $src = $data['iconurl'];
    }    
}

$src = getImageHost(). $src;
header('location:'. $src);
