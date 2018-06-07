<?php
/**
 * Desc:   论坛抽奖
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class BbsAction extends CommonAction {

    function index(){
        $model = D('sendNum.Bbs');
        $model->autoupdatestatus();
        //展示 搜索

        $sqlparam='';

        if(isset($_GET['bbs_hdname']))
        {
            $sqlparam = 'bbs_hdname='.$_GET['bbs_hdname'];
            $where['name'] = array('like', '%'.$_GET['bbs_hdname'].'%');
        }

        if($_POST)
        {
            if(strlen($_POST['bbs_hdname'])>0)
            {
                $sqlparam = 'bbs_hdname='.$_POST['bbs_hdname'];
                $where['name'] = array('like', '%'.$_POST['bbs_hdname'].'%');
            }
            if(strlen($_POST['bbs_hdurl'])>0)
            {
                $where['url'] = array('EQ',$_POST['bbs_hdurl']);
            }
        }

        $where['status'] = 0;
        import("@.ORG.Page");
        $count = $model->where($where)->count();
        $page = new Page($count, 15);
        $list = $model->field("`id`,`tid`,`name`,`url`,`fengtime`,`closetime`,`createtime`,`os_name`,`desc`,`is_fengpost`")->where($where)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        //echo $model->getlastsql();

        $new_data = array();
        $new_data = $list;
        foreach($list as $k=>$v)
        {
            if(strlen($v['desc'])>18)
            {
                $new_data[$k]['desc'] = $this->chgtitle($v['desc']);
            }
        }

        $page->parameter = $sqlparam;
        
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("bbs_hdname", $_REQUEST['bbs_hdname']);
        $this->assign("bbs_hdurl", $_POST['bbs_hdurl']);
        $this->assign("page", $show);
        $this->assign("mklist" , $new_data);

        $this->display('index');
    }

    function wxindex(){
        //展示 搜索

        $sqlparam='';

        if(isset($_GET['bbs_hdname']))
        {
            $sqlparam = 'bbs_hdname='.$_GET['bbs_hdname'];
            $where['name'] = array('like', '%'.$_GET['bbs_hdname'].'%');
        }


        if($_POST)
        {
            if(strlen($_POST['bbs_hdname'])>0)
            {
                $sqlparam = 'bbs_hdname='.$_POST['bbs_hdname'];
                $where['name'] = array('like', '%'.$_POST['bbs_hdname'].'%');
            }
            if(strlen($_POST['bbs_hdurl'])>0)
            {
                $where['url'] = array('EQ',$_POST['bbs_hdurl']);
            }
        }

        $where['status'] = 1;
        import("@.ORG.Page");
        $model = D('sendNum.Bbs');
        $count = $model->where($where)->count();
        $page = new Page($count, 15);
        $list = $model->field("`id`,`tid`,`name`,`url`,`fengtime`,`closetime`,`createtime`,`os_name`,`desc`,`is_fengpost`")->where($where)->limit($page->firstRow.','.$page->listRows)->order('id asc')->select();
        //echo $model->getlastsql();

        $new_data = array();
        $new_data = $list;
        foreach($list as $k=>$v)
        {
            if(strlen($v['desc'])>18)
            {
                $new_data[$k]['desc'] = $this->chgtitle($v['desc']);
            }
        }

        $page->parameter = $sqlparam;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("bbs_hdname", $_REQUEST['bbs_hdname']);
        $this->assign("bbs_hdurl", $_POST['bbs_hdurl']);
        $this->assign("page", $show);
        $this->assign("mklist" , $new_data);

        $this->display('wxindex');
    }

    //截取字符串
    function chgtitle($title){
        $length=9;
        $encoding='utf8';
        if(mb_strlen($title,$encoding)>$length){
        $title=mb_substr($title,0,$length,$encoding).'...';
        }
        return $title;
    }

    function add(){
        $this->display('add');
    }

    //编辑
    function edit(){
        $id = $_GET['id'];
        $model = D('sendNum.Bbs');
        $rs = $model->getbbshd($id);
        if($_POST){
            //是否已经禁用了
            $h_id = $_POST['hh_id'];
            $res1 = $model->getbbshd($h_id);
            if($res1['status']==1)
            {
                echo 5;exit(0);
            }
       
            
            $bbshdname = $_POST['bbshdname'];
            $hdurl = $_POST['hdurl'];
            $fengtime= $_POST['fengtime'];
            $closetime = $_POST['closetime'];
            if(time()>strtotime($closetime)){
                echo 7;exit(0);
            }

            //if(date('Y-m-d H:i:s')>$fengtime){
            //    echo 6;exit(0);
            //}            
            $bbsbeizhu = $_POST['bbsbeizhu'];

            if($fengtime>$closetime){
                echo 4;exit(0);
            }

            $res =strpos($hdurl,'thread');
            $resbbs =strpos($hdurl,'bbs.anzhi.com');
            if(empty($res)||empty($resbbs)){
                echo 2;exit(0);
            }

            $model = D('sendNum.Bbs');
            $tid = $model->gettidbyurl($hdurl);

            if(empty($tid)){
                echo 2;exit(0);
            }

            $pattern = "/^[0-9]*[1-9][0-9]*$/";
            preg_match($pattern, $tid, $matches);
            if(empty($matches)){
                echo 2;exit(0);
            }


            //如果是自己 就不唯一判断

            if($res1['tid']!=$tid)
            {
                $rrs = $model->getbbshdbytid($tid);
                if(!empty($rrs)){
                    echo 3;exit(0);
                }     
            }
			$log_result = $this->logcheck(array('id'=>$h_id),'lottery_bbs',array('tid' => $tid,'url' => $hdurl,'fengtime' => $fengtime,'os_name'=>$bbshdname,'closetime'=>$closetime,'desc'=>$bbsbeizhu),$model);
            $model->updatebbshd($h_id,$tid,$hdurl,$bbshdname,$fengtime,$closetime,$bbsbeizhu);
            $this->writelog('安智抽奖平台-论坛活动列表-进行了编辑操作,活动期:'.$h_id.$log_result, 'sj_lottery_bbs', $h_id,__ACTION__ ,'','edit');
            echo 1;exit(0);
        }

        $this->assign("id" , $id);
        $this->assign("list" , $rs);
        $this->display('edit');
    }

    //备注展示

    function showdesc()
    {
        $id = $_GET['id'];
        $model = D('sendNum.Bbs');
        $rs = $model->getbbshd($id);
        $this->assign("desc" ,$rs['desc']);
        $this->display('showdesc');
    }

    //禁用
    function updatestatus()
    {
        if($_POST){
            $id = $_POST['id'];
            $status = $_POST['is_status'];
            $model = D('sendNum.Bbs');
            $rs = $model->updatestatus($id,$status);
            if($rs>0){
                $this->writelog('安智抽奖平台-论坛活动列表-进行了禁用操作,活动期:'.$id, 'sj_lottery_bbs', $id,__ACTION__ ,'','edit');
                echo 1;exit(0);
            }
        }
    }

    //启用
    function qiyong()
    {
        $id = $_GET['id'];
        $model = D('sendNum.Bbs');
        $res = $model->getbbshd($id);
        if($_POST){
            $id = $_POST['h_id'];
            $res = $model->getbbshd($id);
            if($res['status']==0)
            {
                echo 3;exit(0);
            }

            $closetime = $_POST['closetime'];

            if(strtotime($closetime)<time()){
                echo 2;exit(0);
            }
            $rs = $model->qiyong($id,$closetime);
                $this->writelog('安智抽奖平台-论坛活动列表-进行了启用操作,活动期:'.$id, 'sj_lottery_bbs', $id,__ACTION__ ,'','edit');
            echo 1;exit(0);
        }
        $this->assign("id" ,$id);
        $this->assign("closetime" ,$res['closetime']);
        $this->display('qiyong');
    }

    //封贴后回复
    function bbspost()
    {
        $postmodel = D('sendNum.BbsPost');
        $tid = $_GET['tid'];
        $is_down= $_GET['is_down'];
        if($is_down){
            $list = $postmodel->getbbspost($tid);
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=bbspost.csv");

            $desc ="编号,帖子ID,回复者,回复者ID,回复内容,回复时间,回复IP\r";
            foreach($list as $v)
            {
                $desc = $desc.$v['id'].','.$v['tid'].','.$v['author'].','.$v['authorid'].','.$v['message'].','.date('Y-m-d H:i:s',$v['dateline']).','.$v['useip']."\r";
            }
            $desc = iconv('utf-8','gbk',$desc);
            echo $desc;
            exit(0);
        }

        $list = $postmodel->getbbspost($tid);
        $this->assign("postlist" , $list);
        $this->assign("num" , count($list));
        $this->assign("tid" , $tid);
        $this->display('bbspost');
    }

    //保存数据
    function save(){
        if($_POST){
            $bbshdname = $_POST['bbshdname'];
            $fengtime= $_POST['fengtime'];
            $closetime = $_POST['closetime'];
            $bbsbeizhu = $_POST['bbsbeizhu'];
            $hdurl = $_POST['hdurl'];

            if($fengtime>$closetime){
                echo 4;exit(0);
            }

            if(time()>strtotime($closetime)){
                echo 5;exit(0);
            }

            //if(date('Y-m-d H:i:s')>$fengtime){
            //    echo 6;exit(0);
            //}

            $res =strpos($hdurl,'thread');
            $resbbs =strpos($hdurl,'bbs.anzhi.com');
            if(empty($res)||empty($resbbs)){
                echo 2;exit(0);
            }

            $model = D('sendNum.Bbs');
            $tid = $model->gettidbyurl($hdurl);

            if(empty($tid)){
                echo 2;exit(0);
            }

            $pattern = "/^[0-9]*[1-9][0-9]*$/";
            preg_match($pattern, $tid, $matches);
            if(empty($matches)){
                echo 2;exit(0);
            }

            $rrs = $model->getbbshdbytid($tid);
            if(!empty($rrs)){
                echo 3;exit(0);
            }


            $res = $model->addbbshd($bbshdname,$tid,$hdurl,$fengtime,$closetime,$bbsbeizhu);
            $this->writelog('安智抽奖平台-论坛活动列表-新建了活动,活动名称:'.$bbshdname, 'sj_lottery_bbs', $res,__ACTION__ ,'','add');
            echo 1;exit(0);
        }
    }
}
?>
