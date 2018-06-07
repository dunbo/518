<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
define('MY_PATH', dirname(realpath(__FILE__)));
$config = include_once MY_PATH.'/Conf/config.php';
$link = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME']);
//$link = mysqli_connect($config['DB_HOST'],$config['DB_SLAVE_BASE']['username'],$config['DB_SLAVE_BASE']['password'],$config['DB_SLAVE_BASE']['database']);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

//test

mysqli_set_charset($link,'utf8');
//mysqli_set_charset($link,'gbk');

$sql = "SELECT 
  a.tag_id,
  COUNT(distinct(c.package)) AS softnum 
FROM
  sj_tag a 
  LEFT JOIN sj_tag_package b
    ON a.tag_id = b.tag_id 
  LEFT JOIN sj_soft c 
    ON b.`package` = c.`package` 
    AND c.status = 1 
    AND c.hide = 1 
WHERE (a.status = 1) 
GROUP BY a.tag_id";

$result = mysqli_query($link,$sql);

while($row = mysqli_fetch_array($result))
{
    echo $tag_id = $row['tag_id'];
    echo "\n";
    $softnum = $row['softnum'];

    $sql1 ="update sj_tag set soft_num = $softnum where tag_id='$tag_id'";
    mysqli_query($link,$sql1);
}
