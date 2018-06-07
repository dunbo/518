<?php
class MailConfigModel extends Model {
     protected $trueTableName = 'lottery_mail';

    /**
     *  新增后台市场活动
     *  @final       2013-08-13
     */	
    public function addMailConfig($pztype,$mails,$beizhu)
    {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $data['type'] = $pztype;
        $data['mails'] = $mails;
        $data['os_name'] = $admin_user_name;
        $data['createtime'] = date('Y-m-d H:i:s',time());
        $data['desc'] = $beizhu;
        return $this->add($data);
        //file_put_contents('sql.txt',$this->getlastsql());
    }

    /**
     *  获取活动
     *  @final       2013-08-12
     */	
    public function getmailConfig($id)
    {
        return $this->field("`type`,`mails`,`os_name`,`createtime`,`desc`")->where("id=$id")->find();
    }

    /**
     *  获取活动
     *  @final       2013-08-12
     */	
    public function getmailConfigbytid($typeid)
    {
        return $this->field("`id`")->where("type=$typeid")->find();
    }    

    /**
     *  修改市场活动
     *  @final       2013-08-12
     */	
    public function updatemailConfig($id,$pztype,$mails,$beizhu)
    {
        $data['type'] = $pztype;
        $data['mails'] = $mails;
        $data['desc'] = $beizhu;
        return $this->data($data)->where("id = $id")->save();
    }

    /**
     *  禁用
     *  @final       2013-08-12
     */	
    public function delConfig($id)
    {
        return $this->where("id = $id")->delete();
    }

}
?>
