<?php
class WinModel extends Model {
     protected $trueTableName = 'lottery_win';

    /**
     *  新增中奖名单
     *  @final       2013-08-12
     */	
    public function addwin($winarr,$mid)
    {
        $model = D('sendNum.Market');
        $res = $model->getmarkhd($mid);
        foreach($winarr as $v)
        {
            $data['name'] = $v['name'];
            $data['imei'] = $v['imei'];
            $data['callnum'] = $v['callnum'];
            $data['uid'] = $v['uid'] ;
            $data['group'] = $v['group'];
            $data['desc'] = $v['desc'];
            $data['mid'] = $mid;
            $this->add($data);
        }
        $this->sendmail($res);
        return 1;
    }

    //对已抽奖操作发送邮件
    public function sendmail($res)
    {
        $list = $this->getwin($res['id']);
        $desc="活动期:".$res['id'].",活动名称:[".$res['name']."]的活动有抽奖操作。
抽奖结果：\r".$desc ="<table border=1 align='center' cellpadding='0' cellspacing='1' style='margin-top:4px;'><thead><tr><th bgcolor='#E3E2FE'>编号</th><th bgcolor='#E3E2FE'>中奖人名称</th><th bgcolor='#E3E2FE'>设备号</th><th bgcolor='#E3E2FE'>联系方式</th><th bgcolor='#E3E2FE'>论坛用户组</th><th bgcolor='#E3E2FE'>反馈内容</th></tr></thead>";
            foreach($list as $v)
            {
                $desc = $desc.'<tbody><td>'.$v['id'].'</td><td>'.$v['name'].'</td><td>'.$v['imei'].'</td><td>'.$v['callnum'].'</td><td>'.$v['group'].'</td><td>'.$v['desc']."</td></tbody>";
            }
            $desc = $desc.'</table>';
        $sqlmail="select mails from lottery_mail where type=1";
        $rs = $this->query($sqlmail);
        $mailarr = explode(';',$rs[0]['mails']);
        foreach($mailarr as $mv)
        {
            if(!empty($mv))
            {
                $tmp = $this->_http_post_email(array(
                    'email'=>trim($mv),
                    'name'=>'安智管理',
                    'subject'=>'市场抽奖活动有抽奖操作',
                    'content'=>$desc
                ));
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

    /**
     *  中奖池名单查询
     *  @final       2013-08-12
     */	
    public function getwin($mid)
    {
        return $this->field("`id`,`name`,`imei`,`callnum`,`uid`,`group`,`desc`,`mid`")->where("mid=$mid")->select();
    }

}
?>
