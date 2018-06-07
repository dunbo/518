<?php

class UserLimitAction extends CommonAction{
    function showconf(){
        $model = D('Red_manage.RedpacketUserLimit');
        if(!$_POST['submit']){            
            $res = $model -> find();
            $res['maxcash'] = intval($res['maxcash']);
            $this->assign('pack',$res);
            $this->display();
        }else{
            $_POST['maxnum'] = intval($_POST['maxnum']);
            $_POST['maxcash'] = intval($_POST['maxcash']);
            $res = $model -> find();
            if($_POST['maxnum']<=3){
                $this->error('每用户每日最多可领红包数必须大于3个');
            }
            if($_POST['maxcash']>20||$_POST['maxcash']<=0){
                $this->error('最大红包金额必须大于0小于等于20');
            }
            if($res){
                $data['maxcash'] = $_POST['maxcash'];
                $data['maxnum'] = $_POST['maxnum'];
                $model->where(1)->save($data);         
                $this -> writelog("运营合作-红包配置-每日红包限制 编辑配置为maxcash:{$data['maxcash']}  maxnum:{$data['maxnum']}","sj_redpacket_user_limit",'',__ACTION__ ,"","edit");
                $tips = '编辑成功';                
            }else{
                $data['maxcash'] = $_POST['maxcash'];
                $data['maxnum'] = $_POST['maxnum'];
                $this -> writelog("运营合作-红包配置-每日红包限制 添加配置为maxcash:{$data['maxcash']}  maxnum:{$data['maxnum']}","sj_redpacket_user_limit",'',__ACTION__ ,"","add");
                 $tips = '添加成功';
                $model->add($data); 
            }
          
            $this -> assign("jumpUrl",__URL__."/showconf/");
            $this -> success($tips);
            
        }
        
    }
}    