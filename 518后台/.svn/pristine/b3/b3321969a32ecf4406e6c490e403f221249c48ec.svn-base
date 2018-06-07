<?php

/**
 * 安智网产品管理平台 
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 
 * ----------------------------------------------------------------------------
 */
class CpdUserAction extends CommonAction {
    private $table = 'cpd_statistics_user';
    public function index(){
        $model = M();
        $where = array();
        if(!empty($_GET['sname'])){
            $this->assign('name',$_GET['sname']);
            $where['name'] = array('exp', " like '%{$_GET['sname']}%'");
        }
        if(!empty($_GET['scompany'])){
            $this->assign('company',$_GET['scompany']);
            $where['company'] = array('exp', " like '%{$_GET['scompany']}%'");
        }
        $count = $model->table($this->table)->where($where)->count();
        import("@.ORG.Page2");
        $pg=$_GET['p']?$_GET['p']:1;
        $this->assign('pg', $pg);
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        
        $this->assign('total', $count);
        $user = $model->table($this->table)->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->select();
        $this->assign('user',$user);
        $this->assign('page', $Page->show());
        $this->display();
    }
    
    public function saveUser(){
        $model = M();
        if($_POST){
            $id = $_POST['id'];
            $company = $_POST['company'];
            $where1 = array('name'=>$_POST['name']);
            $where2 = array('company'=>$company);
            if($id) $where1['id'] = $where2['id'] = array('exp', " != '{$id}'");            
            $is_has_user = $model->table($this->table)->where($where1)->find();
            if($is_has_user){
               $this->error('已存在此用户名,请更换');
            }
            $is_has_company = $model->table($this->table)->where($where2)->find();
            if($is_has_company){
               $this->error('已存在此公司名,请更换');
            }
            $data = array(
                'name' => $_POST['name'],
                'password' => $_POST['password'],
                'company' => $company
            );
            if(!$id){
                //添加
                $data['create_tm'] = time();
                $res = $model -> table($this->table)->add($data);
                $msg = '添加';
                $id = $res;
                $type = 'add';
            }else{
                //编辑
                $where = array('id'=>$id);
                $data['update_tm'] = time();
                $res = $model->table($this->table)->where($where)->save($data);
                $msg = '修改';
                $type = 'edit';
            }

            if($res){
                $this->writelog("{$msg}了CPD用户", $this->table, $id, __ACTION__,"", $type);
                $this->success("{$msg}成功");
            }else{
                $this->error("{$msg}失败");
            } 
        }
        if(!empty($_GET['id'])){
            $user = $model->table($this->table)->where("id = '{$_GET['id']}'")->find();
            $this->assign('user',$user);
        }
        $this->display();
    }
    
}
