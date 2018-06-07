<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
define('MY_PATH', dirname(realpath(__FILE__)));
$config = include_once MY_PATH.'/Conf/config.php';

$memcache_obj = new Memcache;
$memcache_obj->connect($config['MC_HOST'], $config['MC_PORT']);


$link = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME']);
//$link = mysqli_connect($config['DB_HOST'],$config['DB_SLAVE_BASE']['username'],$config['DB_SLAVE_BASE']['password'],$config['DB_SLAVE_BASE']['database']);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$prefix = "tagcid_";
$prefix2 = "tagcid_down_";
mysqli_set_charset($link,'utf8');
$sql = "SELECT category_id FROM sj_category WHERE parentid NOT IN(0,1,2,3) AND STATUS !=0";
$result = mysqli_query($link,$sql);
while($row = mysqli_fetch_array($result))
{
    echo $cid = $row['category_id'];
    echo "\n";

    $sql2 ="SELECT COUNT(DISTINCT(a.package)) AS num,a.tag_id,c.tag_name FROM sj_tag_package a INNER JOIN sj_soft b ON a.package = b.package INNER JOIN sj_tag c ON a.`tag_id` = c.`tag_id` WHERE category_id=',$cid,' AND b.`status`=1 AND b.hide=1 AND c.`status`=1  GROUP BY tag_id ORDER BY num DESC LIMIT 20";
    $result2 = mysqli_query($link,$sql2);
    $newarr = array();
    while($row2 = mysqli_fetch_array($result2))
    {
        $newarr[] = $row2['tag_name'];
    }

    $arrstr = serialize($newarr);
    $mc_key = $prefix.$cid;
    $memcache_obj->set($mc_key,$arrstr,0, 86400);


    $sql3 ="SELECT DISTINCT(tag_name) FROM sj_tag a INNER JOIN sj_tag_package b ON a.tag_id = b.tag_id INNER JOIN sj_soft c ON b.package = c.package WHERE c.status=1 AND c.hide=1 AND c.`category_id`=',$cid,' and a.status =1 order by down_num_yesterday desc limit 0,20";
    $result3 = mysqli_query($link,$sql3);
    $newarr2 = array();
    while($row3 = mysqli_fetch_array($result3))
    {
        $newarr2[] = $row3['tag_name'];
    }

    $arrstr2 = serialize($newarr2);
    $mc_key2 = $prefix2.$cid;
    $memcache_obj->set($mc_key2,$arrstr2,0, 172800);
}

echo $memcache_obj->get('tagcid_16');
echo '\n';
echo $memcache_obj->get('tagcid_down_16');

