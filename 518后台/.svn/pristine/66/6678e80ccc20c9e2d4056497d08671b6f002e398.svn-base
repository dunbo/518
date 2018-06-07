<?php
/**
 * Desc:   世界杯活动
 * @author Sun Tao<suntao@anzhi.com>
 * @final  2013-06-13
 */
class WorldCupAction extends CommonAction {

    /**
     *  比赛管理列表页
     */	    
    function match_index(){
        $model = D('sendNum.WorldCup');
        $param = empty($_POST)?$_GET:$_POST;
        
        $rs = $model->getCupMatch($param);
        $this->assign("page", $rs['pageshow']);
        $this->assign("mklist" , $rs['list']);

        $this->assign("sqlparam", $rs['sqlparam']);
        $this->assign("home_name_search", $param['home_name_search']);
        $this->assign("client_name_search", $param['client_name_search']);
        $this->assign("begintime_e", $param['begintime_e']);
        $this->assign("begintime_s", $param['begintime_s']);
        $this->assign("create_tm_e", $param['create_tm_e']);
        $this->assign("create_tm_s", $param['create_tm_s']);
        $this->assign("client_name_search", $param['client_name_search']);
        $this->assign("result_search", $param['result_search']);
        $this->display();
    }

    function formdisplay() {
            $id = (int) $_GET['id'];
            $action = 'addto';
            if ($id) {
                    $model = D('sendNum.WorldCup');
                    $info = $model->getCupInfo($id);
                    $this->assign("begintime",date('Y-m-d H:i:s',$info['begintime']));
                    $this->assign("info",$info);
                    $action = 'editto';
            }
            $this->assign('action', $action);
            $this->display('match_form');
    }
    
    function check(&$param) {
            foreach($param as $k=>$v) {
                     $v = trim($v);
                     if (!$v && $k!='id') exit("$k 为空");
                     $param[$k] = $v;
            }
    }
    
    function uploadFile($field) {
            $img = $_FILES[$field];	
            if ($img['name']) {
                    $fileExt = substr($img['name'], strrpos($img['name'], '.'));
                    $cdndir = '/data/att/m.goapk.com/img/';
                    $datedir = date('Ym',time()).'/'.date('d', time()).'/';
                    $filename = md5(time().$img['name']).$fileExt;
                    $type = $img['type'];
                    if (!in_array($fileExt, array('.png','.jpg'))) {
                            return 123;
                    }
                    if (!is_dir($cdndir.$datedir)) {
                            mkdir($cdndir.$datedir, 0755, true);	
                    }
                    copy($img['tmp_name'], $cdndir.$datedir.$filename);
                    return '/img/'.$datedir.$filename;
            } else {
                    return '';	
            }
    }
    
    function addto() {
            $param = $_POST;
            $this->check($param);
            $param['home_pic'] = $this->uploadFile('home_pic');
            $param['client_pic'] = $this->uploadFile('client_pic');
            
            if ($param['home_pic']==123 || $param['client_pic']==123) {
            	$this->error('图片错误');
            }
            $model = D('sendNum.WorldCup');
            $r = $model->modifyCupMatch($param);
            if ($r) {
                $this -> writelog("添加世界杯比赛，内容为".json_encode($param),"cup_match",$r,__ACTION__ ,"","add");
                $this->success("添加成功");
            } else {
                    $this->error('添加失败');
            }
    }
    
    function editto() {
            $param = $_POST;
            $id = (int) $param['id'];
            if (!$id) {
                    $this->error('错误的编辑');
            }
            $this->check($param);
            if ($this->uploadFile('home_pic')) {
                    $param['home_pic'] = $this->uploadFile('home_pic');
            }
            if ($this->uploadFile('client_pic')) {
                    $param['client_pic'] = $this->uploadFile('client_pic');
            }
    		if ($param['home_pic']==123 || $param['client_pic']==123) {
            	$this->error('图片错误');
            }
            
            $model = D('sendNum.WorldCup');
            $log_result = $this->logcheck(array('id'=>$id),'cup_match',$param,$model);
            $this->writelog("修改世界杯比赛id:".$id.$log_result,"cup_match",$id,__ACTION__ ,"","edit");
            $r = $model->modifyCupMatch($param);
            
            if ($r) {
                    $this->success("编辑成功");
            } else {
                    $this->error('编辑失败');
            }
    }
    
    function setResult() {
            $result = (int) $_GET['result'];
            $id = (int) $_GET['id'];
            if (!$result || !$id) {
                    $this->error('错误的设置');
            }
            
            $model = D('sendNum.WorldCup');
            $log_result = $this->logcheck(array('id'=>$id),'cup_match', $_GET, $model);
            $this->writelog("修改世界杯比赛id:".$id.$log_result,"cup_match",$id,__ACTION__ ,"","edit");
            $r = $model->setResult($result,$id);
            
            if ($r) {
                    $this->success("编辑成功");
            } else {
                    $this->error('编辑失败');
            }
    }
    
    function del() {
            $id = (int) $_GET['id'];
            if (!$id) {
                    $this->error('错误的删除');
            }
            $model = D('sendNum.WorldCup');
            $this->writelog("删除世界杯比赛id:".$id,"cup_match",$id,__ACTION__ ,"","del");
            $r = $model->del($id);
            
            if ($r) {
                    $this->success("删除成功");
            } else {
                    $this->error('删除失败');
            }
    }

    /**
     *  比赛管理列表页
     */	   
    function award_index(){
        //奖金等级配置
        $newmodel = D('sendNum.Contract');
        $res = $newmodel->table('pu_config')->where("config_type='WORLD_CUP_LEVEL'")->find();
        $cup_level = json_decode($res['configcontent'],true);

        $model = D('sendNum.WorldCup');
        $ret = $model->getobeAwardlist();

        $rs = $model->getAwardlist($_GET);
        $this->assign('list',$rs['list']);
        $this->assign('page',$rs['page']);
        $this->assign('cup_level',$cup_level);
        $this->assign('count',count($ret));

        if($this->isAjax())
        {
            $award_num1 = $_POST['award_num1'];
            $award_num2 = $_POST['award_num2'];
            $award_num3 = $_POST['award_num3'];
            $award_num4 = $_POST['award_num4'];
            $model->award($ret,$award_num1,$award_num2,$award_num3,$award_num4);
            $this->writelog('世界杯活动-抽奖管理,进行了抽奖操作,抽奖时间为'.time(),"cup_guess",'',__ACTION__ ,"","edit");
            echo 1;exit(0);
        }

        if(isset($_GET['down'])){
            $rs = $model->getAwardlist($_GET,1);
            $allist = $rs['list'];
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=list.csv");

            $desc ="手机号,竞猜比赛,刮刮卡状态,刮奖时间,话费金额,抽奖时间\r\n";
            foreach($allist as $v)
            {
                $is_gua = $v['is_gua']==1?'已刮奖':'未刮奖';
                $gua_time= $v['is_gua']==1?date('Y-m-d H:i:s',$v['gua_time']):'无';
                $award_time= date('Y-m-d H:i:s',$v['award_time']);
                $bisai = $v['home_name'].'VS'.$v['client_name'];
                $award_level = $v['award_level'];
                
                $desc = $desc.$v['mobile'].','.$bisai.','.$is_gua.','.$gua_time.','.$cup_level[$award_level].','.$award_time."\r";
            }
            $desc = iconv('utf-8','gbk',$desc);
            echo $desc;exit(0);
        }

        $is_gua = isset($_GET['is_gua'])?$_GET['is_gua']:2;
        $this->assign('is_gua',$is_gua);
        $this->assign('award_level',$_GET['award_level']);
        $this->display();
    }
}
?>
