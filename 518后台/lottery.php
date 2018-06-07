<?php
if(php_sapi_name()!='cli'){
    exit(0);
}
set_time_limit(300);
error_reporting(E_ALL & ~E_NOTICE);
define('MY_PATH', dirname(realpath(__FILE__)));
$config = include_once MY_PATH.'/Conf/config.php';
//$link = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME']);
$link = mysqli_connect($config['DB_HOST'],$config['DB_SLAVE_BASE']['username'],$config['DB_SLAVE_BASE']['password'],$config['DB_SLAVE_BASE']['database']);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

//过期活动变无效
$today = date('Y-m-d',time());

$sql1 ="update lottery_market set status = 1 where endtime <='$today'";
mysqli_query($link,$sql1);

$sql_bbs ="update lottery_bbs set status = 1 where closetime <'$today'";
mysqli_query($link,$sql_bbs);

//封贴回复操作
mysqli_set_charset($link,'utf8');
//mysqli_set_charset($link,'gbk');
$sql2 = "SELECT name,tid,url FROM lottery_bbs where status=0 and closetime>'".date('Y-m-d H:i:s',time())."'";
$result = mysqli_query($link,$sql2);
while($row = mysqli_fetch_array($result))
{

    $tid = $row['tid'];
    $url = "http://bbs.anzhi.com/forum.php?mod=getotherpost&tid=".$tid;
    $rs = file_get_contents($url);
    $arr = unserialize($rs);
    //有封贴回复现象
    if(!empty($arr))
    {
        //判断是否有新增的，如果没有return  有则替换
        count($arr);
        $sql_num = "SELECT count(id) as num FROM  lottery_bbs_post where tid =".$tid;
        $rs = $link->query($sql_num)->fetch_assoc();
        if(count($arr)>$rs['num'])
        {
            $delsql ="delete from lottery_bbs_post where tid=".$tid;
            mysqli_query($link,$delsql);

            $content = '活动：['.$row['name'].']有封贴后回复的现象。'.'  '.$row['url'];
            $sqlmail="select mails from lottery_mail where type=2";
            $result_mail = mysqli_query($link,$sqlmail);
            while($row_mail = mysqli_fetch_array($result_mail))
            {
                $mailarr = explode(';',$row_mail['mails']);
                foreach($mailarr as $mv)
                {
                    if(!empty($mv))
                    {
                        $tmp = _http_post_email(array(
                            'email'=>trim($mv),
                            'name'=>'安智管理',
                            'subject'=>'论坛活动有封贴后回复',
                            'content'=>$content
                        ));
                    }
                }
            }


            $sql_str = '';
            foreach($arr as $v)
            {
                $message =$v['message'];
                $sql_str = $sql_str.',('.$tid.',\''.$v['author'].'\','.$v['authorid'].',\''.$message.'\','.$v['dateline'].',\''.$v['useip'].'\')';
            }
            mysqli_set_charset($link,'gbk');
            $isql ="insert into lottery_bbs_post(tid,author,authorid,message,dateline,useip) values".substr($sql_str,1);
            mysqli_query($link,$isql);

            $sqlisfeng = "update lottery_bbs set is_fengpost=1 where tid =".$tid;
            mysqli_query($link,$sqlisfeng);            
        }

    }

}

function _http_post_email($vals) {
	$url = 'http://192.168.1.244/service.php';
	//$url = 'http://118.26.203.22/service.php';
	//$url = 'http://42.62.4.179/service.php';
	$host = 'Host: mail.goapk.com';
	$url .= '?key=019f160f2ae0c8990eb94653bd101857';

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 5);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	curl_close($res);
	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
}
?>
