<?php
/**
 * Desc:   市场抽奖
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class MarketAction extends CommonAction {

    

    function index(){
        //展示 搜索
        

        $sqlparam='';

        if(isset($_GET['hdname']))
        {
            $sqlparam = $sqlparam.'hdname='.$_GET['hdname'].'&';
            $where['name'] = array('like', '%'.$_GET['hdname'].'%');
        }
        if(isset($_GET['osname']))
        {
            $sqlparam = $sqlparam.'osname='.$_GET['osname'].'&';
            $where['osusername'] = array('EQ',$_GET['osname']);
        }        
        if(isset($_GET['begintime']))
        {
            $sqlparam = $sqlparam.'begintime='.$_GET['begintime'].'&';
            $where['createtime'] = array('EGT',$_GET['begintime']);
        }

        if($_POST)
        {
            if(strlen($_POST['huodongqi'])>0)
            {
                $huodongqi = $_POST['huodongqi'];
                $pattern = "/^[0-9]*[1-9][0-9]*$/";
                preg_match($pattern, $huodongqi, $matches);
                if(empty($matches))
                {
                    echo "<script>alert('活动期请输入正整数')</script>";
                    echo '<script>location.href ="/index.php/Sendnum/Market/index";</script>';
                }else
                {
                    $where['id'] = array('eq',$_POST['huodongqi']);
                }
                //正则判断是否整数
            }
            if(strlen($_POST['hdname'])>0)
            {
                $sqlparam = $sqlparam.'hdname='.$_POST['hdname'].'&';
                $where['name'] = array('like', '%'.$_POST['hdname'].'%');
            }
            if(strlen($_POST['osname'])>0)
            {
                $sqlparam = $sqlparam.'osname='.$_POST['osname'].'&';
                $where['osusername'] = array('EQ',$_POST['osname']);
            }
            if(strlen($_POST['begintime'])>0)
            {
                $sqlparam = $sqlparam.'begintime='.$_POST['begintime'].'&';
                $where['createtime'] = array('EGT',$_POST['begintime']);
            }
        }

        $where['status'] = 0;
        import("@.ORG.Page");
        $model = D('sendNum.Market');
        $count = $model->where($where)->count();
        $page = new Page($count, 15);
        $list = $model->field("`id`,`name`,`poolnum`,`winnum`,`osusername`,`createtime`,`endtime`,`ostime`,`desc`,`is_chou`")->where($where)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
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
        $this->assign("huodongqi", $_POST['huodongqi']);
        $this->assign("hdname", $_REQUEST['hdname']);
        $this->assign("osname", $_REQUEST['osname']);
        $this->assign("begintime", $_REQUEST['begintime']);
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

    function wxindex(){
        //展示 搜索

        $sqlparam='';

        if(isset($_GET['hdname']))
        {
            $sqlparam = $sqlparam.'hdname='.$_GET['hdname'].'&';
            $where['name'] = array('like', '%'.$_GET['hdname'].'%');
        }
        if(isset($_GET['osname']))
        {
            $sqlparam = $sqlparam.'osname='.$_GET['osname'].'&';
            $where['osusername'] = array('EQ',$_GET['osname']);
        }        
        if(isset($_GET['begintime']))
        {
            $sqlparam = $sqlparam.'begintime='.$_GET['begintime'].'&';
            $where['createtime'] = array('EGT',$_GET['begintime']);
        }

        if($_POST)
        {
            if(strlen($_POST['huodongqi'])>0)
            {
                $huodongqi = $_POST['huodongqi'];
                $pattern = "/^[0-9]*[1-9][0-9]*$/";
                preg_match($pattern, $huodongqi, $matches);
                if(empty($matches))
                {
                    echo "<script>alert('活动期请输入正整数')</script>";
                    echo '<script>location.href ="/index.php/Sendnum/Market/wxindex";</script>';
                }else
                {
                    $where['id'] = array('eq',$_POST['huodongqi']);
                }
            }
            if(strlen($_POST['hdname'])>0)
            {
                $sqlparam = $sqlparam.'hdname='.$_POST['hdname'].'&';
                $where['name'] = array('like', '%'.$_POST['hdname'].'%');
            }
            if(strlen($_POST['osname'])>0)
            {
                $sqlparam = $sqlparam.'osname='.$_POST['osname'].'&';
                $where['osusername'] = array('EQ',$_POST['osname']);
            }
            if(strlen($_POST['begintime'])>0)
            {
                $sqlparam = $sqlparam.'begintime='.$_POST['begintime'].'&';
                $where['createtime'] = array('EGT',$_POST['begintime']);
            }
        }

        $where['status'] = 1;
        import("@.ORG.Page");
        $model = D('sendNum.Market');
        $count = $model->where($where)->count();
        $page = new Page($count, 15);
        $list = $model->field("`id`,`name`,`poolnum`,`winnum`,`osusername`,`createtime`,`endtime`,`ostime`,`desc`,`is_chou`")->where($where)->limit($page->firstRow.','.$page->listRows)->order('id asc')->select();
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
        $this->assign("huodongqi", $_POST['huodongqi']);
        $this->assign("hdname", $_REQUEST['hdname']);
        $this->assign("osname", $_REQUEST['osname']);
        $this->assign("begintime", $_REQUEST['begintime']);
        $this->assign("page", $show);
        $this->assign("mklist" , $new_data);

        $this->display('wxindex');
    }
    
    function add(){
        $this->display('add');
    }


    //获取指定个数的随机数
    function getrandnum($chounum,$zhongnum)
    {
        $arr=array();//创建数组
        while(count($arr)<$zhongnum){
          $a=rand(0,$chounum-1);//产生随机数
          if(!in_array($a,$arr)){ //判断$arr中是否有$a,有则返回true,否则false
          $arr[]=$a; //把$a 赋值给数组元素
          }
        }
        return $arr;
    }

    //抽奖
    function lottery(){
        $poolnum= $_GET['poolnum'];
        $id = $_GET['id'];
        if($_POST){
            //再次查询 是否抽过奖
            $huodongqi = $_POST['huodongqi'];
            $model = D('sendNum.Market');
            $is_chou = $model->getmarkis_chou($huodongqi);
            if($is_chou['is_chou']==1)
            {
                echo 3;exit(0);
            }
            $res = $model->getmarkhd($huodongqi);
            if(strtotime(date('Y-m-d'))>strtotime($res['endtime']))
            {
                echo 4;exit(0);
            }

            $zhongnum = $_POST['zhongnum'];
            
            $pattern = "/^[0-9]*[1-9][0-9]*$/";
            preg_match($pattern, $zhongnum, $matches);
            if(empty($matches))
            {
                echo 2;exit(0);
            }

            $chounum = $_POST['chounum'];
            $randarr = $this->getrandnum($chounum,$zhongnum);
            $poolmodel = D('sendNum.Pool');
            $list = $poolmodel->getpool($huodongqi);
            $zhong_arr = array();
            $i = 0;
            foreach($randarr as $v)
            {
                $zhong_arr[$i] = $list[$v];
                $i++;
            }
            $winmodel = D('sendNum.Win');
            $winmodel->addwin($zhong_arr,$huodongqi);

            //更改状态 为已抽奖
            $model->updatechou($huodongqi,count($zhong_arr));
            $this->writelog('安智抽奖平台-市场活动列表-进行了抽奖操作,活动期:'.$huodongqi, 'sj_lottery_market', $huodongqi,__ACTION__ ,'','edit');
            echo '1';exit(0);
        }
        $this->assign("poolnum" , $poolnum);
        $this->assign("id" , $id);
        $this->display('lottery');
    }

    //编辑
    function edit(){
        $id = $_GET['id'];
        $model = D('sendNum.Market');
        $rs = $model->getmarkhd($id);
        if($_POST){
            //是否已经禁用了
            $huodongqi = $_POST['huodongqi'];
            $res = $model->getmarkhd($huodongqi);
            if($res['status']==1)
            {
                echo 2;exit(0);
            }

            $hdname = $_POST['hdname'];
            $endtime= $_POST['endtime'];
            $beizhu = $_POST['beizhu'];
			$log_result = $this->logcheck(array('id'=>$huodongqi),'lottery_market',array('hdname' => $hdname,'endtime' => $endtime,'desc' => $beizhu),$model);
            $model->updatemarkhd($huodongqi,$hdname,$endtime,$beizhu);
            $this->writelog('安智抽奖平台-市场活动列表-进行了编辑操作,活动期:'.$huodongqi.$log_result, 'sj_lottery_market', $huodongqi,__ACTION__ ,'','edit');
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
        $model = D('sendNum.Market');
        $rs = $model->getmarkhd($id);
        $this->assign("desc" ,$rs['desc']);
        $this->display('showdesc');
    }

    //禁用
    function updatestatus()
    {
        if($_POST){
            $id = $_POST['id'];
            $status = $_POST['is_status'];
            $model = D('sendNum.Market');
            $rs = $model->updatestatus($id,$status);
            if($rs>0){
                $this->writelog('安智抽奖平台-市场活动列表-进行了禁用操作,活动期:'.$id, 'sj_lottery_market', $id,__ACTION__ ,'','edit');
                echo 1;exit(0);
            }
        }
    }

    //启用
    function qiyong()
    {
        $id = $_GET['id'];
        if($_POST){
            $id = $_POST['h_id'];
            $model = D('sendNum.Market');
            $res = $model->getmarkhd($id);
            if($res['status']==0)
            {
                echo 2;exit(0);
            }

            $endtime = $_POST['endtime'];
            $rs = $model->qiyong($id,$endtime);
            $this->writelog('安智抽奖平台-市场活动列表-进行了启用操作,活动期:'.$id, 'sj_lottery_market', $id,__ACTION__ ,'','edit');
            echo 1;exit(0);
        }
        $this->assign("id" ,$id);
        $this->assign("endtime" ,$_GET['endtime']);
        $this->display('qiyong');
    }

    //抽奖池展示
    function pool()
    {
        $poolmodel = D('sendNum.Pool');
        $mid = $_GET['id'];
        $is_down= $_GET['is_down'];
        if($is_down){
            $list = $poolmodel->getpool($mid);
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=pool.csv");

            $desc ="编号,中奖人名称,设备号,联系方式,论坛用户组,反馈内容\r";
            foreach($list as $v)
            {
                $desc = $desc.$v['id'].','.$v['name'].','.$v['imei'].','.$v['callnum'].','.$v['group'].','.$v['desc']."\r";
            }
            $desc = iconv('utf-8','gbk',$desc);
            echo $desc;
            exit(0);
        }

        $list = $poolmodel->getpool($mid);
        $this->assign("poolist" , $list);
        $this->assign("num" , count($list));
        $this->assign("mid" , $mid);
        $this->display('pool');
    }


    //中奖池展示
    function win()
    {
        $mid = $_GET['id'];
        $winmodel = D('sendNum.Win');
        $is_down= $_GET['is_down'];
        if($is_down){
            $list = $winmodel->getwin($mid);
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=win.csv");

            $desc ="编号,中奖人名称,设备号,联系方式,论坛用户组,反馈内容\r";
            foreach($list as $v)
            {
                $desc = $desc.$v['id'].','.$v['name'].','.$v['imei'].','.$v['callnum'].','.$v['group'].','.$v['desc']."\r";
            }
            $desc = iconv('utf-8','gbk',$desc);
            echo $desc;
            exit(0);
        }

        $list = $winmodel->getwin($mid);
        $this->assign("num" , count($list));
        $this->assign("winlist" , $list);
        $this->assign("mid" , $mid);
        $this->display('win');
    }

    function save(){
        $admin_id= $_SESSION['admin']['admin_id'];
        $path = P_LOG_DIR.'/'.$_SERVER['HTTP_HOST'].'/';
        if($_POST){
            $hdname = $_POST['hdname'];
            $endtime= $_POST['endtime'];
            $beizhu = $_POST['beizhu'];
            $rastatus = $_POST['rastatus'];
            $model = D('sendNum.Market');

            ini_set('memory_limit', '512M');
            set_time_limit(0);
            $tmp_name1 = $_FILES['upload']['tmp_name'][0];
            $tmp_name2 = $_FILES['upload']['tmp_name'][1];
            $tmp_name3 = $_FILES['upload']['tmp_name'][2];
            $path=$path.date('Y-m-d').'/';
            $this->createFolder($path);
            if($rastatus==1)
            {
                $bi_path =$path.'bi_'.rand(1,100000).'_'.time().'_uid_'.$admin_id.'.csv';
                $cp_path =$path.'cp_'.rand(1,100000).'_'.time().'_uid_'.$admin_id.'.csv';
                $is_succ_bi= move_uploaded_file($_FILES['upload']['tmp_name'][0],$bi_path);
                $is_succ_cp= move_uploaded_file($_FILES['upload']['tmp_name'][1],$cp_path);
                if(empty($is_succ_bi)||empty($is_succ_cp)){
                    echo 2;exit(0);
                }
                //$poolarr = $this->merger_csv($tmp_name2,$tmp_name1);
                $poolarr = $this->merger_csv($cp_path,$bi_path);
            }else
            {
                $khd_path =$path.'khd_'.rand(1,100000).'_'.time().'_uid_'.$admin_id.'.csv';
                $is_succ_khd= move_uploaded_file($_FILES['upload']['tmp_name'][2],$khd_path);

                if(empty($is_succ_khd)){
                    echo 2;exit(0);
                }
                //$poolarr = $this->read_csv($tmp_name3);
                $poolarr = $this->read_csv($khd_path);
            }
            $poolmodel = D('sendNum.Pool');
            $mid = $model->addmarkhd($hdname,count($poolarr),$endtime,$beizhu,$bi_path,$cp_path,$khd_path);
            if($mid>0)
            {
                $res = $poolmodel->addpool($poolarr,$mid);
                $this->writelog('安智抽奖平台-市场活动列表-新建了活动,活动名称:'.$hdname, 'sj_lottery_market', $mid,__ACTION__ ,'','add');
                echo 1;exit(0);
            }else{
                echo 3;exit(0);
            }
        }
    }

    /**
     * 创建目录
     */
    private function createFolder($path)
    {
       if(!file_exists($path))
       {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
       }
    }    

    function read_csv($file)
    {
        //$file = "new.csv";
	$handel = fopen($file,"r");
        $i=-1;
        $arr = array();
        $numarr = array();//联系方式数组
        $imeiarr = array();//IMEI数组
	while(!feof($handel)){
            $num_row = fgets($handel);
            $num_arr = explode(',',$num_row);
            if($i>-1)
            {
                if(empty($num_arr[0])&&empty($num_arr[1])&&empty($num_arr[2]))
                {
                    continue;
                }
                if(!in_array(trim($num_arr[2]),$numarr))
                {
                    if(!in_array(strtoupper(trim(str_replace('"','',$num_arr[0]))),$imeiarr))
                    {
                        $arr[$i]['imei']=strtoupper(trim(str_replace('"','',$num_arr[0])));
                        $arr[$i]['username']=trim($num_arr[1]);
                        $arr[$i]['callnum']=trim($num_arr[2]);
                        $imeiarr[] = strtoupper(trim(str_replace('"','',$num_arr[0])));
                        $numarr[] = trim($num_arr[2]);
                    }
                }
            }
            $i++;
        }
        fclose($handel);
        return $arr;
    }

    function merger_csv($file1,$file2)
    {
        //$file1 = "cp.csv";
        //$file2 = "bi.csv";
        //$file1 cp
	$handel = fopen($file1,"r");
        $arr2 = array();
        $numarr = array();//联系方式数组
        $imeiarr = array();//IMEI数组
        $i = -1;
	while(!feof($handel)){
	    $num_row = fgets($handel);
	    $num_arr = explode(',',$num_row);
            if($i>-1)
            {
                if(!in_array(trim(str_replace('"','',$num_arr[9])),$numarr))
                {
                    if(!in_array(strtoupper(trim(str_replace('"','',$num_arr[6]))),$imeiarr))
                    {
                        $imei= strtoupper(trim(str_replace('"','',$num_arr[6])));
                        $imeiarr[] = $imei;
                        $arr2[$imei]['desc']=trim(str_replace('"','',$num_arr[3]));
                        $arr2[$imei]['imei']=strtoupper(trim(str_replace('"','',$num_arr[6])));
                        $arr2[$imei]['callnum']=trim(str_replace('"','',$num_arr[9]));
                        $arr2[$imei]['uid']=trim(str_replace('"','',$num_arr[1]));
                        $numarr[] = trim(str_replace('"','',$num_arr[9]));
                    }
                }
            }
            $i++;
        }

        fclose($handel);


	$handel = fopen($file2,"r");
        $arr1 = array();
        $j = -1;
	while(!feof($handel)){
	    $num_row = fgets($handel);
	    $num_arr = explode(',',$num_row);
            if($j>-1)
            {
                $imei= strtoupper(trim(str_replace('"','',$num_arr[0])));
                if(in_array($imei,$imeiarr))
                {
                    $username = trim($num_arr[1]);
                    if($username=="")
                    {
                        $username = '安智用户'.substr($imei,0,4).substr($imei,-4);
                        $username = iconv('utf-8','gbk',$username);
                    }
                    if($arr2[$imei]['username']=='')
                    {
                        $arr2[$imei]['username'] = $username;
                    }
                }
            }
            $j++;
        }

        fclose($handel);

        foreach($arr2 as $k=>$v)
        {
            if(!isset($v['username']))
            {
                unset($arr2[$k]);
            }
            if($v['imei']=='')
            {
                unset($arr2[$k]);
            }
        }
        //file_put_contents('ttts.txt',serialize($arr2));

        //unlink($file1);
        //unlink($file2);
        return $arr2;
    }
     
    function merger_csv_temp(){
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		$tmp_name = $_FILES['num_file']['tmp_name'];
		$file_name = $_FILES['num_file']['name'];
		$file_size = $_FILES['num_file']['size'];
		$f_arr = explode('.',$file_name);
		$file_ext = $f_arr[1];
		if($file_ext != 'csv'){
			$model -> table('sendnum_active') -> where('id ='.$add_active_id) -> delete();
			$this -> error('请上传csv格式文件');
			
		}
		if($file_size == 0){
			$model -> table('sendnum_active') -> where('id ='.$add_active_id) -> delete();
			$this -> error('文件没有内容');
		}
		$handel = fopen($tmp_name,"r");
		$nums_info = array();
		$num_sum = 0;
		$num_ab_sum = 0;
		while(!feof($handel)){
			$nums = array();
			$num_row = fgets($handel);
			$num_sum ++;
			$num_arr = explode(',',$num_row);
			$number = iconv('GB2312','UTF-8', trim($num_arr[0]));
			if(count($num_arr) == 1 && (strlen($number) >0 && strlen($number) <= 25)){
				//if(preg_match("/[".chr(0xa1)."-".chr(0xff)."]+/",$number) || preg_match("/[\x{4e00}-\x{9fa5}]+/u",$number)) continue;
				//file_put_contents('/tmp/lipeng.log',$number."\n",FILE_APPEND);
				if(!preg_match("/^[0-9a-zA-Z]+$/",$number)) continue;
				$nums['active_num'] = $number;
				$nums['active_id'] = $add_active_id;
				$nums['status'] = 0;
			}else continue;
			// $tabid = strlen($num_ab_sum,-1,1); //数据分表
			// $affectid = $model -> add_active_num($nums,$tabid);
			// $tabid = strlen($num_ab_sum,-1,1); //数据分表
			$cnt = $model -> table('sendnum_number') -> where(" active_num = '{$nums['active_num']}' and active_id = {$add_active_id}") -> count();
			if(!$cnt){
				$affectid = $model -> add_active_num($nums);
				if($affectid > 0) $num_ab_sum++;	
			}
		//	$affectid = $model -> add_active_num($nums);
		//	if($affectid > 0) $num_ab_sum++;			
		}
		unlink($tmp_name);
		return array($num_sum,$num_ab_sum);
    }


}
?>
