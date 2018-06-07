<?php
include '../init.php';

$packages = include('config.php');

$dev_model = load_model('dev');
$softlist = load_model('softlist');

$packageIds = $softlist->getPkg2Id(array_keys($packages));
$softid_map = array();
foreach ($packageIds as $package => $softids) {
    foreach ((array) $softids as $softid) {
        $softid_map[$softid] = 1;
    }
}
$filter_option = array();
$softids = $dev_model->filterSoftId($softid_map, $tmp_filter_option);
$softinfos = $softlist->getSoftInfos($softids);

$list = array();
foreach ($softids as $softid) {
    $val = $softinfos[$softid];

    $i = floor ( $val ['score'] / 2 );
    $k = $val ['score'] % 2;
    $scorehtml = '';
    for($i1 = $i; $i1 > 0; $i1 --) {
        $scorehtml .= '<img alt="" src="/images/star_01.png">';
    }
    if ($k != 0)
        $scorehtml .= '<img alt="" src="/images/star_02.png">';
    if (($i + $k) < 5) {
        for($i2 = (5 - $i - $k); $i2 > 0; $i2 --) {
            $scorehtml .= '<img alt="" src="/images/star_03.png">';
        }
    }

    $tmp = array(
        'package' => $val['package'],
        'softname' => $val['softname'],
        'iconurl' => $val['iconurl'],
        'filesize' => formatFileSize('',$val['filesize']),
        'desc' => $val['desc'],
        'scorehtml' => $scorehtml,
    );
    $list[] = $tmp;
}
$tplObj->out ['list'] = $list;
$tplObj->display ( "../hw/index.tpl" );

