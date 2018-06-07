<?php
if(php_sapi_name()!='cli'){
    exit(0);
}
set_time_limit(300);
error_reporting(E_ALL & ~E_NOTICE);
define('MY_PATH', dirname(realpath(__FILE__)));
$config = include_once MY_PATH.'/../Conf/config.php';
$link = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME']);
//$link = mysqli_connect($config['DB_HOST'],$config['DB_SLAVE_BASE']['username'],$config['DB_SLAVE_BASE']['password'],$config['DB_SLAVE_BASE']['database']);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

mysqli_set_charset($link,'utf8');

//mysqli_set_charset($link,'gbk');
$sql ="select package,tags from sj_soft where hide=1 and status=1 and update_type = 4 and dev_id in('9161471','4494796', '863945','416546','1014461','9255463','5868404','2732570', '264773','510083',  '9680947','1352033','12226751','373232')";
//$sql ="select package,tags from sj_soft where hide=1 and status=1 and update_type = 4";
    $result = mysqli_query($link,$sql);
    while($row = mysqli_fetch_array($result))
    {
        $package = $row['package'];
        $tags = $row['tags'];
        add_package_tags($package,$tags);
    }


function add_package_tags($package,$tags)
{
    global $link;
        //$tags = "图骥文学,地名文化,文学,阅读,文化";
        $tagarr = explode(',',$tags);

        foreach($tagarr as $v)
        {
            $sql1 = "select tag_id from sj_tag where tag_name = '$v' and status=1";
            $row1 = mysqli_fetch_array(mysqli_query($link,$sql1));
            if($row1===NULL)
            {
                //没有该标签，新增
                echo $sqladd ="insert into sj_tag(tag_name,addtime,update_tm) values('$v',".time().",".time().")";
                echo "\n";
                mysqli_query($link,$sqladd);
                $insertid  =mysqli_insert_id($link);
                if($insertid>0)
                {
                    echo $sqladd2 ="insert into sj_tag_package(package,tag_id) values('$package','$insertid')";
                    echo "\n";
                    mysqli_query($link,$sqladd2);
                }
            }else
            {
                //查下对应的关系是否存在
                $sql2 = "select tag_id from sj_tag_package where package= '$package' and tag_id = ".$row1['tag_id'];
                $row2 = mysqli_fetch_array(mysqli_query($link,$sql2));

                if($row2===NULL){
                    echo $sqladd3 ="insert into sj_tag_package(package,tag_id) values('$package','".$row1['tag_id']."')";
                    echo "\n";
                    mysqli_query($link,$sqladd3);
                }
            }
        }

        //更新sj_soft_note
        $tagstr = ',';
        $sql3 = "select distinct(tag_id) from sj_tag_package where package='$package'";
        $result = mysqli_query($link,$sql3);
        while($row3 = mysqli_fetch_array($result))
        {
            $tagstr = $tagstr.$row3['tag_id'].',';
        }

        $data['tag_ids'] = $tagstr;
        echo $sql_update = "update sj_soft_note set tag_ids ='$tagstr' where package ='$package'";
        echo "\n";
        mysqli_query($link,$sql_update);
}
