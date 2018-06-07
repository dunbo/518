<?php
class CoopwebsetModel extends AdvModel {
    protected $connect_id = 15;
    private $table='caiji_video_config';
    public function __construct()
    {
        //parent::__construct();
        $myConnect1 = C('DB_CAIJI');
        $this -> addConnect($myConnect1, $this->connect_id);
        $this -> switchConnect($this->connect_id);
    }
    //获取视频内容排序值
    public function get_video_rank(){
        $model = M('');
        $this->table = 'pu_config';
        $where = array('config_type'=>'COOP_VIDEO_RANK','status'=>1);
        $result =$model->table($this->table)->where($where)->field('configcontent')->find();
        return $result['configcontent'];
    }

    //获取视频配置
    public function get_video_config($where){
        $result = $this->table($this->table)->where($where)->find();
        //echo $this->table($this->table)->getLastSql();
        return $result;
    }
    //获取视频展示字段
    public function get_show_column($where){
        $this->table = 'caiji_video_show_column';
        $result = $this->table($this->table)->where($where)->find();
        return $result;
    }

    //保存视频展示字段
    public function save_show_column($id,$save_data){
        $this->table = 'caiji_video_show_column';
        $where = array('video_id'=>$id,'status'=>1);
        $has_info = $this->table($this->table)->where($where)->find();
        $save_data['update_tm'] = time();
        if($has_info){
            $up_data = $save_data;
            $res = $this->table($this->table)->where($where)->save($up_data);
        }else{
            $data = $save_data;
            $data['video_id'] = $id;
            $res = $this->table($this->table)->where($where)->add($data);
        }
        //echo $this->table($this->table)->getLastSql();
        return $res;
    }

    //保存视频配置
    public function save_video_config($id,$save_data){
        $where = array('id'=>$id);
        $save_data['update_time'] = time();
        $result = $this->table($this->table)->where($where)->save($save_data);
        //echo $this->table($this->table)->getLastSql();
        return $result;
    }

    //保存视频信息
    public function save_video_info($id,$data){
        unset($data['id']);
        unset($data['is_more']);
        $this->table = 'caiji_video_mess';
        if(is_array($id)){
            $where = array('id'=>array('in',$id));
        }else{
            $where = array('id'=>$id);
        }
        if($data['status']==2){
            $data['pass_tm'] = time();
        }
        $data['update_tm'] = time();
        $result = $this->table($this->table)->where($where)->save($data);
       // echo $this->table($this->table)->getLastSql();
        return $result;
    }
}