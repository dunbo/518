<?php
class StoreModel extends AdvModel {

    protected $connect_id = 28;

    public function __construct(){
            parent::__construct();
            $cj_Connect = C('DB_ACTIVITY');

            $this -> addConnect($cj_Connect, $this->connect_id);
            $this -> switchConnect($this->connect_id);
    }

    //保存背景图片
    function save_img($explain,$buy_path,$sale_path){
        $data = array();
        $data['buy_img']= $buy_path;
        $data['explain']= $explain;
        $data['sale_img']= $sale_path;
        $data['update_tm']=time();

        $ret = $this->table('store_config')->where("id=1")->find();
        if($ret===NULL)
        {
            return $ret = $this->table('store_config')->add($data);
        }else
        {
            if(empty($buy_path)){unset($data['buy_img']);}
            if(empty($sale_path)){unset($data['sale_img']);}
            return $ret = $this->table('store_config')->where("id = 1")->save($data);
        }
    }


    //查询兑换记录
    function getorderlist($get,$type=1){

        if(isset($get['id'])&&$get['id']!=-1)
        {
            $where['store_id'] = array('eq',$get['id']);
        }

        if(isset($get['city'])&&$get['city']!=-1)
        {
            $where['city'] = array('eq',$get['city']);
        }

        if(isset($get['yichang'])&&$get['yichang']!=-1)
        {
            $where['effective'] = array('eq',$get['yichang']);
        }

        if($get['status']==1||$get['status']==2||$get['status']==3)
        {
            if($get['status']==1)
            {
                $where['status'] = array('eq',$get['status']);
            }else if($get['status']==2)
            {
                $where['status'] = array('eq',$get['status']);
            }else if($get['status']==3)
            {
                $where['status'] = array('eq',$get['status']);
            }
        }

        if(strlen($get['tel'])>0)
        {
            $where['tel'] = array('like', '%'.$get['tel'].'%');
        }

        if(strlen($get['imei'])>0)
        {
            //$where['imei'] = array('like', '%'.$get['imei'].'%');
            $where['imei'] = array('eq',$get['imei']);
        }

        if(strlen($get['school'])>0)
        {
            $where['school'] = array('like', '%'.$get['school'].'%');
        }
        if(strlen($get['store_name'])>0)
        {
            $where['store_name'] = array('like', '%'.$get['store_name'].'%');
        }

        if(strlen($get['add_begintime'])>0)
        {
            $where['exchange_tm']  = array('between',''.strtotime($get['add_begintime']).','.strtotime($get['add_endtime']).'');
        }

        $order = 'id desc';

        if($type==2)
        {
            $rs = $this->table('store_order')->field('*')->where($where)->order($order)->select();
            $res = array(
                'list'=>$rs,
            );
        }else
        {
            import("@.ORG.Page");
            $count = $this->table('store_order')->where($where)->count();
            $page = new Page($count, 10);
            $rs = $this->table('store_order')->field('*')->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
            //echo $this->getlastsql();
            $page->setConfig('header','条记录');
            $page->setConfig('first','<<');
            $page->setConfig('last','>>');
            $show =$page->show();    
            $res = array(
                'list'=>$rs,
                'page'=>$show,
                'count'=>$count,
            );
        }
        return $res;
    }

    function getcity(){
        $rs = $this->table('store_school')->field('*')->where("pid=0")->select();
        return $rs;
    }

    function getschool($city){
        $rs = $this->table('store_school')->field('*')->where("pid=".$city)->select();
        return $rs;
    }

    function getrecommend(){
        $rs = $this->table('store_recommend')->field('*')->select();
        return $rs;
    }

    function addsaler($post){
        $post['password'] = md5($post['password']);
        unset($post['school_val']);
        unset($post['saler_id']);
        unset($post['city_val']);
        $post['create_tm'] = time();
        $ret = $this->table('store_user')->add($post);
        return $ret;
    }

    function editsaler($post){
        if(empty($post['password'])){
            unset($post['password']);
        }else{
            $post['password'] = md5($post['password']);
        }
        $id = $post['saler_id'];
        unset($post['saler_id']);
        unset($post['school_val']);
        unset($post['city_val']);
        $post['update_tm'] = time();
        $ret = $this->table('store_user')->where('id='.$id)->save($post);
        //echo $this->getlastsql();
        return $ret;
    }

    //查询商家列表
    function getsalerlist($get,$type=1){

        if(isset($get['shopkeeper'])&&$get['shopkeeper']!=-1)
        {
            $where['shopkeeper'] = array('like', '%'.$get['shopkeeper'].'%');
        }

        if(isset($get['city'])&&$get['city']!=-1)
        {
            $where['city'] = array('eq',$get['city']);
        }

        if(strlen($get['tel'])>0)
        {
            $where['tel'] = array('like', '%'.$get['tel'].'%');
        }

        if(strlen($get['school'])>0)
        {
            $where['school'] = array('like', '%'.$get['school'].'%');
        }
        if(strlen($get['store_name'])>0)
        {
            $where['store_name'] = array('like', '%'.$get['store_name'].'%');
        }


        $where['status']=1;
        $order = 'id desc';

        if($type==2)
        {
            $rs = $this->table('store_user')->field('*')->where($where)->order($order)->select();
            $res = array(
                'list'=>$rs,
            );
        }else
        {
            import("@.ORG.Page");
            $count = $this->table('store_user')->where($where)->count();
            $page = new Page($count, 10);
            $rs = $this->table('store_user')->field('*')->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
            //echo $this->getlastsql();
            $page->setConfig('header','条记录');
            $page->setConfig('first','<<');
            $page->setConfig('last','>>');
            $show =$page->show();    
            $res = array(
                'list'=>$rs,
                'page'=>$show,
                'count'=>$count,
            );
        }
        return $res;
    }

    function getsalerinfo($id){
        $ret = $this->table('store_user')->where('id='.$id)->find();
        return $ret;
    }

    function updatestatus_order($id){
        $data['status']=3;
        $data['update_tm']=time();
        $ret = $this->table('store_order')->where("id =".$id)->save($data);
        return $ret;
    }

    function get_yjs_count($id){
        $where['store_id'] = array('eq',$id);
        $where['status'] = array('eq',3);
        return $ret = $this->table('store_order')->where($where)->count();
    }

    function get_wjs_count($id){
        $where['store_id'] = array('eq',$id);
        $where['status'] = array('eq',2);
        return $ret = $this->table('store_order')->where($where)->count();
    }

    function get_yeswjs_count($id,$yesterday_begin,$yesterday_end,$yichang){
        $yesterday_begin = empty($yesterday_begin)?date('Y-m-d',strtotime("-1 days")).' 00:00:00':$yesterday_begin;
        $yesterday_end = empty($yesterday_end)?date('Y-m-d',strtotime("-1 days")).' 23:59:59':$yesterday_end;
        $where['exchange_tm']  = array('between',''.strtotime($yesterday_begin).','.strtotime($yesterday_end).'');
        $where['store_id'] = array('eq',$id);
        if(!empty($yichang)&&$yichang!=-1){
            $where['effective'] = array('eq',$yichang);
        }
        $where['status'] = array('eq',2);
        $ret = $this->table('store_order')->where($where)->count();
        return $ret;
    }

    //异常
    function get_yeswjs_count_yichang($id,$yesterday_begin,$yesterday_end){
        $yesterday_begin = empty($yesterday_begin)?date('Y-m-d',strtotime("-1 days")).' 00:00:00':$yesterday_begin;
        $yesterday_end = empty($yesterday_end)?date('Y-m-d',strtotime("-1 days")).' 23:59:59':$yesterday_end;
        $where['exchange_tm']  = array('between',''.strtotime($yesterday_begin).','.strtotime($yesterday_end).'');
        $where['store_id'] = array('eq',$id);
        $where['status'] = array('eq',2);
        $where['effective'] = array('eq',2);
        return $ret = $this->table('store_order')->where($where)->count();
    }



    function getconfig(){
        $ret = $this->table('store_config')->where('id=1')->find();
        return $ret;
    }

    //金钱格式化
    function doFormatMoney($money){
        $tmp_money = strrev($money);
        $format_money = ""; 
        for($i = 3;$i<strlen($money);$i+=3){
            $format_money .= substr($tmp_money,0,3).",";
             $tmp_money = substr($tmp_money,3);
         }
        $format_money .=$tmp_money;
        $format_money = strrev($format_money); 
        return $format_money;
    }

    //批量结算多个商家
    function batch_js($id,$yesterday_begin,$yesterday_end){
        $data['status']=3;
        $data['update_tm']=time();
        $where['exchange_tm']  = array('between',''.strtotime($yesterday_begin).','.strtotime($yesterday_end).'');
        $where['store_id'] = array('eq',$id);
        $where['status'] = array('eq',2);
        $ret = $this->table('store_order')->where($where)->save($data);
        return $ret;
    }

    function del_user($id){
        $data['status']=0;
        $where['id'] = $id;
        $ret = $this->table('store_user')->where($where)->save($data);
    }

    function is_exist_user($username,$id){
        if(!empty($id)){
            $where['id'] = array('neq',$id);
        }
        $where['username'] = $username;
        $where['status'] = 1;
        $ret = $this->table('store_user')->where($where)->find();
        return $ret;
    }
    function is_exist_tel($tel,$id){
        if(!empty($id)){
            $where['id'] = array('neq',$id);
        }
        $where['tel'] = $tel;
        $where['status'] = 1;
        $ret = $this->table('store_user')->where($where)->find();
        return $ret;
    }
}
