<?php
class PoolModel extends Model {
     protected $trueTableName = 'lottery_pool';

    /**
     *  新增抽奖池名单
     *  @final       2013-08-12
     */	
    public function addpool($poolarr,$mid)
    {
        foreach($poolarr as $v)
        {
            $data['name'] = iconv('gbk','utf-8',$v['username']);
            $data['imei'] = $v['imei'];
            $data['callnum'] = $v['callnum'];
            $data['uid'] = $v['uid'] ;
            if(!empty($v['uid'])){
                $data['group'] = iconv('gbk','utf-8',$this->getGroup($v['uid']));
            }
            $data['desc'] = iconv('gbk','utf-8',$v['desc']);
            $data['mid'] = $mid;
            $res = $this->add($data);
            
        }
		return $res;
    }

    /**
     *  抽奖池名单查询
     *  @final       2013-08-12
     */	
    public function getpool($mid)
    {
        return $this->field("`id`,`name`,`imei`,`callnum`,`uid`,`group`,`desc`,`mid`")->where("mid=$mid")->select();
    }

    public function getGroup($uid)
    {
        if($uid ==13176)
        {
            $group = '-----';
        }else
        {
            $key ='anzhi2013suntao@!$';
            $thistime = time();
            $passkey = md5($key.$thistime);

            $group = file_get_contents("http://bbs.anzhi.com/member.php?mod=getuser&uid=$uid&time=$thistime&password=$passkey");
            $group = empty($group) ? '-----':$group;
        }
        return $group;
    }
}
?>
