<?php
class BbsModel extends Model {
     protected $trueTableName = 'lottery_bbs';

    /**
     *  新增论坛活动
     *  @final       2013-08-14
     */	
    public function addbbshd($hdname,$tid,$hdurl,$fengtime,$closetime,$beizhu)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data['tid'] = $tid;
        $data['name'] = $hdname;
        $data['url'] = $hdurl;
        $data['fengtime'] = $fengtime;
        $data['closetime'] = $closetime;
        $data['createtime'] = date('Y-m-d H:i:s',time());
        $data['os_name'] = $admin_user_name;
        $data['desc'] = $beizhu;
        return $this->add($data);
    }

    /**
     *  获取论坛活动
     *  @final       2013-08-14
     */	
    public function getbbshd($id)
    {
        return $this->field("`id`,`tid`,`name`,`url`,`fengtime`,`closetime`,`createtime`,`os_name`,`desc`,`status`")->where("id=$id")->find();
    }


    /**
     *  获取论坛活动
     *  @final       2013-08-14
     */	
    public function getbbshdbytid($tid)
    {
        return $this->field("`id`")->where("tid=$tid")->find();
    }    

    /**
     *  修改市场活动
     *  @final       2013-08-12
     */	
    public function updatebbshd($id,$tid,$hdurl,$bbshdname,$fengtime,$closetime,$bbsbeizhu)
    {
        $data['tid'] = $tid;
        $data['name'] = $bbshdname;
        $data['url'] = $hdurl;
        $data['fengtime'] = $fengtime;
        $data['closetime'] = $closetime;
        $data['desc'] = $bbsbeizhu;
        return $this->data($data)->where("id = $id")->save();
    }

    /**
     *  禁用
     *  @final       2013-08-14
     */	
    public function updatestatus($id,$status)
    {
        $data['status'] = $status;
        return $this->data($data)->where("id = $id")->save();
    }

    /**
     *  启用
     *  @final       2013-08-14
     */	
    public function qiyong($id,$closetime)
    {
        $data['closetime'] = $closetime;
        $data['status'] = 0;
        return $this->data($data)->where("id = $id")->save();
    }

    /**
     *  根据URL 获取tid
     *  @final       2013-08-19
     */	
    public function gettidbyurl($url)
    {
        if(substr($url,-4)=='html')
        {
            $rs = explode('-',$url);
            return $rs[1];
        }else
        {
            $rs = explode('=',$url);
            $rss = explode('&',$rs[2]);
            return $rss[0];
        }
    }

    /**
     *  过期的自动禁用
     *  @final       2013-08-14
     */	
    public function autoupdatestatus()
    {
        $now = date('Y-m-d H:i:s');
        $data['status'] = 1;
        return $this->data($data)->where("closetime <= '$now'")->save();
    }
}
?>
