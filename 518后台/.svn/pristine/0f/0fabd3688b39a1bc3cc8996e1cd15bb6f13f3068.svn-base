<?php
class CompetenceConfigModel extends Model {
    protected $trueTableName = 'yx_admin';
    
    // 返回管理员列表
    public function getlist() {
        return $this->field("`id`,`admin_user_name`")->findall();
    }
    
    // 根据用户名添加管理员
    public function addAdmin($name, $auth_online, $auth_schedule) {
        $exsit = $this->table('sj_admin_users')->where("admin_user_name='$name'")->select();
        //echo $this->getlastsql();
        //var_dump($exsit);
        if (!$exsit)
            return -1;
        $exsit = $this->where("admin_user_name='$name'")->select();
        if ($exsit)
            return -2;
        $data['admin_user_name'] = $name;
        $data['auth_online']= $auth_online;
        $data['auth_schedule']= $auth_schedule;
        return $this->add($data);
    }
    
    // 根据id删除管理员
    public function deleteAdmin($admin_id) {
        return $this->where("id=$admin_id")->delete();
    }
    
    // 根据id获取管理员用户名
    public function getAdminUserName($admin_id) {
        return $this->field("`admin_user_name`")->where("id=$admin_id")->find();
    }

}
?>
