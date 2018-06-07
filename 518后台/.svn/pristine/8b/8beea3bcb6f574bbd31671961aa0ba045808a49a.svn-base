<?php
/**
 * Desc:   邮件配置列表
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class MailConfigAction extends CommonAction {

    function index(){
        import("@.ORG.Page");
        $model = D('sendNum.MailConfig');
        $count = $model->count();
        $page = new Page($count, 15);
        $list = $model->field("`id`,`type`,`mails`,`os_name`,`createtime`,`desc`")->limit($page->firstRow.','.$page->listRows)->select();
        //echo $model->getlastsql();

        $new_data = array();
        $new_data = $list;
        foreach($list as $k=>$v)
        {
            $newmails = $v['mails'];
            $mail_tmp = explode(';',$newmails);
            $m_str = '';
            foreach($mail_tmp as $mv)
            {
                $m_str = $m_str.$mv."\r";
            }
            $new_data[$k]['mails']= $m_str;

            if(strlen($v['desc'])>18)
            {
                $new_data[$k]['desc'] = $this->chgtitle($v['desc']);
            }
        }

        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("page", $show);
        $this->assign("mklist" , $new_data);

        $this->display('index');
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

    //备注展示

    function showdesc()
    {
        $id = $_GET['id'];
        $model = D('sendNum.MailConfig');
        $rs = $model->getmailConfig($id);
        $this->assign("desc" ,$rs['desc']);
        $this->display('showdesc');
    }
    
    function add(){
        $this->display('add');
    }


    //编辑
    function edit(){
        $id = $_GET['id'];
        $model = D('sendNum.MailConfig');
        $rs = $model->getmailConfig($id);
        if($_POST){
            $hdname = $_POST['hdname'];
            $huodongqi = $_POST['huodongqi'];
            $endtime= $_POST['endtime'];
            $beizhu = $_POST['beizhu'];
            $model->updatemarkhd($huodongqi,$hdname,$endtime,$beizhu);
            echo 1;exit(0);
        }
        $this->assign("id" , $id);
        $this->assign("list" , $rs);
        $this->display('edit');
    }

    function match_email($email) {
         $pattern = "/\w+@(\w|\d)+\.\w{2,3}/i";
         preg_match($pattern, $email, $matches);
         return $matches;
    }
    

    function save(){
        if($_POST){
            $model = D('sendNum.MailConfig');
            $pztype = $_POST['pztype'];
            $mails= $_POST['mails'];



            $mailarr = explode(';',$mails);
            foreach($mailarr as $v)
            {
                if($v!='')
                {
                    $rs = $this->match_email($v);
                    if(empty($rs))
                    {
                        echo 2;exit(0);
                    }
                }
            }

            $beizhu = $_POST['beizhu'];
            $h_id = $_POST['h_id'];
            if($h_id)//编辑
            {
                $resbyid = $model->getmailConfig($h_id);
                if(empty($resbyid))
                {
                    echo 4;exit(0);
                }


                $count = $model->count();
                if($count>1)
                {
                    if($resbyid['type']!=$pztype)
                    {
                        echo 3;exit(0);
                    }              
                }
				$log_result = $this->logcheck(array('id'=>$h_id),'lottery_mail',array('type' => $pztype,'mails' => $mails,'desc' => $beizhu),$model);
                $rs = $model->updatemailConfig($h_id,$pztype,$mails,$beizhu);
                $this->writelog('安智抽奖平台-邮件配置列表-编辑了配置,编号:'.$h_id.$log_result, 'sj_lottery_mail', $h_id,__ACTION__ ,'','edit');
            }else
            {
                $res = $model->getmailConfigbytid($pztype);
                if(!empty($res))
                {
                    echo 3;exit(0);
                }
                $rs = $model->addMailConfig($pztype,$mails,$beizhu);
                $this->writelog('安智抽奖平台-邮件配置列表-新建了配置', 'sj_lottery_mail', $rs,__ACTION__ ,'','add');
            }
            echo 1;exit(0);
        }
    }

    function deleteconfig()
    {
        if($_POST){
            $h_id = $_POST['h_id'];
            $model = D('sendNum.MailConfig');
            $rs = $model->delConfig($h_id);
            $this->writelog('安智抽奖平台-邮件配置列表-删除了配置,编号:'.$h_id, 'sj_lottery_mail', $h_id,__ACTION__ ,'','del');
            echo 1;exit(0);
        }
    }
}
?>
