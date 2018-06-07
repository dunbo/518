<?php
set_time_limit(300);
error_reporting(E_ALL & ~E_NOTICE);
define('MY_PATH', dirname(realpath(__FILE__)));
$config = include_once MY_PATH.'/Conf/config.php';
$link = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME']);
//$link = mysqli_connect($config['DB_HOST'],$config['DB_SLAVE_BASE']['username'],$config['DB_SLAVE_BASE']['password'],$config['DB_SLAVE_BASE']['database']);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}



mysqli_set_charset($link,'utf8');
//mysqli_set_charset($link,'gbk');

//清空之前的标签
$sql ="update sj_soft_note set tag_ids = ''";
mysqli_query($link,$sql);
exit;

$sql2 = "SELECT package,GROUP_CONCAT(tag_id) as tags FROM sj_tag_package GROUP BY package";
$result = mysqli_query($link,$sql2);
while($row = mysqli_fetch_array($result))
{
    echo $package = $row['package'];
    echo "\n";
    $tags = $row['tags'];
    $sql1 ="update sj_soft_note set tag_ids = '$tags' where package='$package'";
    mysqli_query($link,$sql1);
}
