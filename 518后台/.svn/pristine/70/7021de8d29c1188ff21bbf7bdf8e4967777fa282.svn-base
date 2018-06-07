<?php
class MarketModel extends Model {
     protected $trueTableName = 'lottery_market';

    /**
     *  新增后台市场活动
     *  @final       2013-08-09
     */	
    public function addmarkhd($hdname,$poolnum,$endtime,$beizhu,$bi_path,$cp_path,$khd_path)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data['name'] = $hdname;
        $data['poolnum'] = $poolnum;
        $data['addname'] = $admin_user_name;
        $data['createtime'] = date('Y-m-d',time());
        $data['endtime'] = $endtime;
        $data['desc'] = $beizhu;
        $data['bi_path'] = iconv('gbk','utf-8',$bi_path);
        $data['cp_path'] = iconv('gbk','utf-8',$cp_path);
        $data['khd_path'] = iconv('gbk','utf-8',$khd_path);
        return $this->add($data);
    }

    /**
     *  获取活动
     *  @final       2013-08-12
     */	
    public function getmarkhd($id)
    {
        return $this->field("`id`,`name`,`endtime`,`desc`,`status`")->where("id=$id")->find();
    }

    /**
     *  查询活动是否已经抽过奖
     *  @final       2013-08-12
     */	
    public function getmarkis_chou($id)
    {
        return $this->field("`is_chou`")->where("id=$id")->find();
    }

    /**
     *  修改市场活动
     *  @final       2013-08-12
     */	
    public function updatemarkhd($id,$hdname,$endtime,$beizhu)
    {
        $data['name'] = $hdname;
        $data['endtime'] = $endtime;
        $data['desc'] = $beizhu;
        return $this->data($data)->where("id = $id")->save();
    }

    /**
     *  禁用
     *  @final       2013-08-12
     */	
    public function updatestatus($id,$status)
    {
        $data['status'] = $status;
        return $this->data($data)->where("id = $id")->save();
    }

    /**
     *  启用
     *  @final       2013-08-12
     */	
    public function qiyong($id,$endtime)
    {
        $data['endtime'] = $endtime;
        $data['status'] = 0;
        return $this->data($data)->where("id = $id")->save();
    }

    /**
     *  更改为已抽奖
     *  @final       2013-08-12
     */	
    public function updatechou($id,$winnum)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data['is_chou'] = 1;
        $data['osusername'] = $admin_user_name;
        $data['winnum'] = $winnum;
        $data['ostime'] = date('Y-m-d',time());
        return $this->data($data)->where("id = $id")->save();
    }    
}
?>
