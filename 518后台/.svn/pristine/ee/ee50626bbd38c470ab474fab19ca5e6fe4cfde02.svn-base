<?php

/**
 * Desc:   报表产品功能
 * @author Sun Tao<suntao@anzhi.com>
 * @final  2013-09-02
 */
if(!defined('IMG_HOST')) define('IMG_HOST',IMGATT_HOST);
class ProductAction extends CommonAction {
	private $one_category = array(
            '1' => '网游',
            '2' => '单机',
            '3' => '棋牌'
        );
    private $other_reason = '亲爱的开发者您好，根据审核规则，游戏需具备国家新闻出版广电总局游戏的版号批复文件（包含游戏名称和版号及公章），游戏才可上线，请上传！';
    /**
     *  新提交产品
     */
    function index() {
        $model = D('sendNum.Product');
        if(empty($_GET['orderby'])){
            $_GET['o'] = 1;
            $this->assign("o", $_GET['o']);
        }
        if(empty($_GET['orderby'])||$_GET['orderby']=='desc'){   
            $order = 'asc';
        }else if($_GET['orderby']=='asc'){
            $order = 'desc';
        }
       
        $this->assign("order", $order);
        
        $res = $model->getprolist($_GET, $_POST);
         // echo $model->getLastSql();die;
        $list = $res['list'];
		$package = array();
        foreach ($list as $k => $v) {
            if (strlen($v['jianjie']) > 18) {
                $list[$k]['jianjie'] = $this->chgtitle($v['jianjie']);
            }
            if (strlen($v['beizhu']) > 18) {
                $list[$k]['beizhu'] = $this->chgtitle($v['beizhu']);
            }
            if (strlen($v['com_tj_tel']) > 18) {
                $list[$k]['com_tj_tel'] = $this->chgtitle($v['com_tj_tel']);
            }
            $list[$k]['testname'] = array_pop(explode('_', $v['test_path']));
			$package[] = $v['package'];
        }
        $show = $res['show'];
		$where = array(
            'package' => array('in', $package),
        );
		$contract_status = get_table_data($where, "yx_contract", "package", "package,status");
		//var_dump($contract_status);
       
        $sqlparam = $res['sqlparam'];
		$this->assign("contract_status", $contract_status);
        $this->assign("softname", $_REQUEST['softname']);
        $this->assign("package", $_REQUEST['package']);
        $this->assign("companyname", $_REQUEST['companyname']);
        $this->assign("hztype", $_REQUEST['hztype']);
        $this->assign("is_debut", $_REQUEST['is_debut']);

        $this->assign("record_status", $_REQUEST['record_status']);
        $this->assign("publication_status", $_REQUEST['publication_status']);

        if (isset($_GET['zhuangtai'])) {
            $this->assign("zhuangtai", $_REQUEST['zhuangtai'] + 1);
        } else if (isset($_POST['zhuangtai'])) {
            $this->assign("zhuangtai", $_REQUEST['zhuangtai']);
        }else{
            $this->assign("zhuangtai",-1);
        }

        $this->assign("osuser", $_REQUEST['osuser']);
        $this->assign("rqtype", $_REQUEST['rqtype']);

        if ($_GET['begintime']) {
            $this->assign("begintime", date('Y-m-d H:i:s', $_REQUEST['begintime']));
        } else {
            $this->assign("begintime", $_REQUEST['begintime']);
        }

        if ($_GET['endtime']) {
            $this->assign("endtime", date('Y-m-d H:i:s', $_REQUEST['endtime']));
        } else {
            $this->assign("endtime", $_REQUEST['endtime']);
        }
        if (isset($_GET['mobandown'])) {
            $path = C('BAOBIAO_PATH');
            $file_dir = $path . 'moban.csv';
            if (!file_exists($file_dir)) {
                header("Content-type: text/html; charset=utf-8");
                echo "File not found!";
                exit;
            } else {
                $file = fopen($file_dir, "r");
                Header("Content-type: application/octet-stream");
                Header("Accept-Ranges: bytes");
                Header("Accept-Length: " . filesize($file_dir));
                Header("Content-Disposition: attachment; filename=moban.csv");
                echo fread($file, filesize($file_dir));
                fclose($file);
                exit(0);
            }
        }

        if (isset($_GET['down'])) {
            $allist = $res['allist'];
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=new.csv");

            $desc = "产品名称,包源大小,产品所属性质,公司名称,简介,合作方式,操作人,联系方式,创建时间,驳回时间,备注,状态\r\n";
            foreach ($allist as $v) {
                $status = $v['status'] == 1 ? '拒绝评测' : '待处理';
                $status = $v['status'] == 0 ? '待处理' : ($v['status'] == 1 ? '拒绝评测' : '评测驳回');
                $botime = strlen($v['botime'] > 0) ? date('Y-m-d H:i:s', $v['botime']) : '无';

                // 将可能引起格式错误的字符替换掉
                $beizhu = trim($v['beizhu']);
                $beizhu = str_replace("\n", " ", $beizhu);
                $beizhu = str_replace("\r", " ", $beizhu);
                $beizhu = str_replace(",", "，", $beizhu);

                $jianjie = trim($v['jianjie']);
                $jianjie = str_replace("\n", " ", $jianjie);
                $jianjie = str_replace("\r", " ", $jianjie);
                $jianjie = str_replace(",", "，", $jianjie);

                $com_tj_tel = trim($v['com_tj_tel']);
                $com_tj_tel = str_replace(",", "，", $com_tj_tel);

                $desc = $desc . $v['softname'] . ',' . $v['size'] . ',' . $v['nature'] . ',' . $v['companyname'] . ',' . $jianjie . ',' . $v['hztype'] . ',' . $v['osuser'] . ',' . $com_tj_tel . ',' . date('Y-m-d H:i:s', $v['createtime']) . ',' . $botime . ',' . $beizhu . ',' . $status . "\r";
            }
            $desc = mb_convert_encoding($desc, 'gbk', 'utf8');
            echo $desc;
            exit(0);
        }

        if ($_POST) {
            $this->redirect('/Product/index?' . $sqlparam);
        }
        // echo "<pre>";var_dump($list);die;
        $this->assign("page", $show);
        $this->assign("list", $list);
        $this->assign("sqlparam", $sqlparam);
        $this->display('index');
    }

    //截取字符串
    function chgtitle($title) {
        $length = 9;
        $encoding = 'utf8';
        if (mb_strlen($title, $encoding) > $length) {
            $title = mb_substr($title, 0, $length, $encoding) . '...';
        }
        return $title;
    }

    function show_jianjie() {
        $id = $_GET['id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($id);
        $this->assign("jianjie", $rs['jianjie']);
        $this->display("show_jianjie");
    }

    function show_beizhu() {
        $id = $_GET['id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($id);
        $this->assign("beizhu", $rs['beizhu']);
        $this->display("show_beizhu");
    }

    function show_com_tj_tel() {
        $id = $_GET['id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($id);
        $this->assign("rs", $rs);
        $this->display("show_com_tj_tel");
    }

    function show_bili() {
        $id = $_GET['id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($id);
        $this->assign("bili", $rs['bili']);
        $this->display("show_bili");
    }

    /**
     *  导出失败名单
     */
    function downfail() {
        $thistime = $_GET['thistime'];
        if ($thistime) {
            $model = D('sendNum.Productimfail');
            $desc = $model->getimportfail($thistime);
            $list = unserialize(iconv('utf-8', 'gbk', $desc['desc']));
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=fail.csv");

            $desc = "产品名称,包名,包源大小,产品所属性质,公司名称,简介,合作方式,联系方式,备注\r";
            $desc = iconv('utf-8', 'gbk', $desc);
            foreach ($list as $v) {
                $desc = $desc . $v['0'] . ',' . $v['1'] . ',' . $v['2'] . ',' . $v['3'] . ',' . $v['4'] . ',' . $v['5'] . ',' . $v['6'] . ',' . $v['7'] . ',' . $v['8'] . "\r\n";
            }
            echo $desc;
            exit(0);
        }
    }

    /**
     *  新提交产品添加/修改页面
     */
    function add() {
        $soft_id = $_GET['soft_id'];
        $status = $_GET['status'];
        $model = D('sendNum.Product');
        $list = $model->getallproductbyid($soft_id);
        if ($this->isAjax()) {
            $softname_new = $_POST['softname_new'];
            $soft_id = $_POST['soft_id'];
            //修改
            if ($soft_id > 0) {
                $list = $model->getallproductbyid($soft_id);
                //如果没有改产品名称则不判断
                if ($list['softname'] == $softname_new) {
                    echo 1;
                    exit(0);
                } else { //如果改了产品名称，判断新产品名称是否唯一
                    $ishas = $model->getproductbysoftname($softname_new);
                    if ($ishas) {
                        echo 2;
                        exit(0);
                    } else {
                        echo 1;
                        exit(0);
                    }
                }
            } else {//新增
                $ishas = $model->getproductbysoftname($softname_new);
                //file_put_contents('dd.txt',$ishas);
                if ($ishas) {
                    echo 2;
                    exit(0);
                } else {
                    echo 1;
                    exit(0);
                }
            }
        }
        //读取数据
        if ($_POST) {
            $softid = $_POST['soft_id'];
            $sstatus = $_POST['status'];
            $this->publication_record('record_url','del_ba','备案','record_');
            $this->publication_record('publication_url','del_cb','出版','publication_');
            $this->up_auth_file();
            if ($softid > 0) {
                // 获得编辑信息，以方便写数据库
                $where_arr = array('soft_id' => $_POST['soft_id']);
                $table_name = 'yx_product';
                $admin_user_name = $_SESSION['admin']['admin_user_name'];
                $column_arr = array(
                    'softname' => $_POST['softname'],
                    'size' => $_POST['size'],
                    'nature' => $_POST['nature'],
                    'companyname' => $_POST['companyname'],
                    'jianjie' => $_POST['jianjie'],
                    'osuser' => $admin_user_name,
                    'hztype' => $_POST['hztype'],
                    'com_tj_tel' => $_POST['com_tj_tel'],
                    'customer_tel' => $_POST['customer_tel'],
                    'customer_qq' => $_POST['customer_qq'],
                    'record_num' => $_POST['record_num'],
                    'publication_num' => $_POST['publication_num'],
                    'record_url' => $_POST['record_url'],
					'publication_url' => $_POST['publication_url'],
                    'dev_auth_url' => $_POST['dev_auth_url'],
                    'coop_auth_url' => $_POST['coop_auth_url'],
                    'soft_auth_url' => $_POST['soft_auth_url'],
                    'ip_auth_url' => $_POST['ip_auth_url'],
                );
                
                if ($status == 1) {
                    $column_arr['reason'] = $_POST['reason'];
                }
                $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
                $msg = "报表-产品状态列表-新提交产品：编辑了soft_id为{$_POST['soft_id']}的记录：";
                $msg .= $log_all_need;
                // 准备写库
                $rs = $model->editproduct($softid, $sstatus, $_POST);
                $this->assign("jumpUrl", "/index.php/Sendnum/Product/index");
                if ($rs === false) {
                    $this->error('修改失败');
                }
                $this->writelog($msg, 'yx_product',$softid,__ACTION__ ,'','edit');
                $this->success('修改成功');
            } else {
                $rs = $model->addproduct($_POST);
                if ($rs > 0) {
                    $this->writelog('报表-产品状态列表-新提交产品，新增了产品,softid为:' . $rs, 'yx_product',$rs,__ACTION__ ,'','add');
                    $this->assign("jumpUrl", "/index.php/Sendnum/Product/index");
                    $this->success('添加成功');
                }
            }
        }
        $this->assign('list', $list);
        $this->assign('status', $status);
        $this->assign('soft_id', $soft_id);
        $this->display('add');
    }
    //备案资料和出版证明资料上传和证书授权文件资料上传
    function publication_record($re_pb,$del,$str,$file,$bs=''){
        $model = D('sendNum.Product');
        //附件存放目录
        $static_data = C('BEIAN_PATH'); //附件的绝对路径
        list($msec, $sec) = explode(' ', microtime());
        $msec = substr($msec, 2);
        if(!$_POST["$del"]){
            if ($_FILES["$re_pb"]['name']) {
                $dir_img = $static_data . date('Ym/d') . '/';
                if (!is_dir($dir_img)) {
                    if (!mkdir($dir_img, 0777, true)) {
                        $this->error('目录创建失败');
                    }
                }
                $array = array('jpg', 'png', 'pdf');
                $ytypes = $_FILES["$re_pb"]['name'];
                $info = pathinfo($ytypes);
                $type = strtolower($info['extension']); //获取文件件扩展名
                if (!in_array($type, $array)) {
                    $this->error($str.'材料文件格式不正确！');
                }
                if ($_FILES["$re_pb"]['size']>=1024*1024*20 && !$bs) {
                    $this->error($str.'材料大小小于20M！');
                }
                $ba_path = $dir_img . $file . $msec . '.' . $type;
                if (move_uploaded_file($_FILES["$re_pb"]['tmp_name'], $ba_path)) {
                    $file_ba = str_replace(UPLOAD_PATH, '', $ba_path);
                    $_POST["$re_pb"]=$file_ba;
                }
            }else{
                $soft_id=$_POST["soft_id"];
                $res=$model->where("soft_id='$soft_id'")->find();
                $_POST["$re_pb"]=$res["$re_pb"];
            }
        }else{
            $_POST["$re_pb"]='';
        }
    }
    /**
     *  批量导入
     */
    function import() {
        if ($_POST) {
            $tmp_name = $_FILES['upload']['tmp_name'];

            $tmp_houzhui = $_FILES['upload']['name'];
            $tmp_arr = explode('.', $tmp_houzhui);
            $houzhui = array_pop($tmp_arr);
            if (strtoupper($houzhui) != 'CSV') {
                echo 2;
                exit(0);
            }

            $arr = $this->readcsv($tmp_name);
            if ($arr === false) {
                echo 2;
                exit(0);
            }
            $this->writelog('报表-产品状态列表-新提交产品，批量导入了产品', 'yx_product','',__ACTION__ ,'','add');
            $this->ajaxReturn($arr, '导入成功！', 1);
        }
        $this->display('import');
    }

    function readcsv($file) {
        $arr = array();
        $title = array();
        //$file="3.csv";
        $handel = fopen($file, "r");
        $i = 0;

        while (($num_arr = $this->mygetcsv($handel, 1000, ",")) !== FALSE) {
            //标题行不写入
            if ($i != 0) {
                $arr[$i] = $num_arr;
            } else {
                $title[$i] = $num_arr;
            }
            $i++;
        }

        //if(count($title[0])!=9||iconv('gbk','utf-8',$title[0][4])!='公司名称') {
        if (count($title[0]) != 8) {
            return false;
        }

        $model = D('sendNum.Product');
        $rs = $model->importadd($arr);
        fclose($handel);
        return $rs;
        //unlink($file);
        //return $arr2;
    }

    /**
     *  新提交产品页通过操作
     */
    function tonguo() {
        //$path = C('BAOBIAO_PATH');
		$path = UPLOAD_PATH . '/data3' . '/agreement/' . date('Ym/d') . '/';
        //if($_POST){
        if ($this->isPost()) {
            set_time_limit(300);
            $soft_id = $_POST['soft_id'];
            $h_softname = $_POST['h_softname'];
            $hztype = $_POST['hztype'];
            $fenlei = $_POST['fenlei'];
            $leixing = $_POST['leixing'];
			if(empty($_POST['p_leixing'])||empty($_POST['leixing'])){
				echo '7';
				exit(0);
			}
            //ini_set('memory_limit', '512M');

            //$path = $path . $soft_id . '/';
            $this->createFolder($path);

            $tmp_houzhui = $_FILES['upload']['name'][1];
            if (strlen($tmp_houzhui) > 0) {
                $tmp_arr = explode('.', $tmp_houzhui);
                $houzhui = array_pop($tmp_arr);
                if (strtoupper($houzhui) != 'JPG' && strtoupper($houzhui) != 'PNG') {
                    echo 4;
                    exit(0);
                }
                $zs_path = $path . $h_softname . '.' . $houzhui;
                $is_succ_zs = move_uploaded_file($_FILES['upload']['tmp_name'][1], $zs_path);
				$zs_path = str_replace(UPLOAD_PATH, '', $zs_path);  
            }

            if ($_FILES['upload']['name'][0] != "") {
                $tmp_houzhui2 = $_FILES['upload']['name'][0];
                $tmp_arr2 = explode('.', $tmp_houzhui2);
                $houzhui2 = array_pop($tmp_arr2);
                if (strtoupper($houzhui2) != 'ZIP' && strtoupper($houzhui2) != 'RAR' && strtoupper($houzhui2) != 'APK') {
                    echo 2;
                    exit(0);
                }
                $apk_path = $path . $h_softname . '.' . $houzhui2;

                $is_succ_apk = move_uploaded_file($_FILES['upload']['tmp_name'][0], $apk_path);
                if (empty($is_succ_apk)) {
                    echo 3;
                    exit(0);
                }
            }




            $model = D('sendNum.Product');

            // 如果有评测报告，删除评测报告
            $find = $model->getproductbyid($soft_id);

            $test_path = "";
            if (!empty($find['test_path'])) {
                $test_path = $find['test_path'];
                unlink($test_path);
            }
            if ($find['p_fenlei'] == '单机(运营商)' || $find['p_fenlei'] == '单机(安智SDK)') {
                $find['p_fenlei'] = '单机';
            }
			$find['p_leixing'] = $_POST['leixing'];
			
            //获取app信息
            $has_app = $model->table('sj_sdk_info')->where(array('package' => $find['package']))->field('app_id,app_status,dev_id')->find();
            if ($has_app) {
				if($has_app['app_status'] == 0){
					 $vals = array('appKey' => $has_app['app_id'], 'status' => 1,'isJoinUcenter'=>$_POST['is_accept_account']);

					//$res = json_decode($this->modify_app($vals), true);
					$res = json_decode(modifyAppNew($vals), true);
					if ($res['code'] != 'success') {
						echo 6;
						exit(0);
					} else {
						$this->writelog('报表-产品状态列表-新提交产品,将包名为:' . $find['package'].'的appkey置为有效', 'yx_product','',__ACTION__ ,'','add');
						$save_app = $model->table('sj_sdk_info')->where(array('package' => $find['package']))->save(array('app_status' => 1));
					}	
				}else if(!empty($find['dev_id'])&&!empty($has_app['dev_id'])&&$find['dev_id']!=$has_app['dev_id']){
					$vals = array('appKey' => $has_app['app_id'], 'pid' => $find['dev_id']);
					$res = json_decode(modifyAppPid($vals), true);
					if($res['code']!='success'){
						echo 8;
						exit();
					}else{
						$save_app = $model->table('sj_sdk_info')->where(array('package' => $find['package']))->save(array('dev_id' => $find['dev_id']));
					}
				}else{
					$save_app = true;
				}
               
            } else if (!$has_app) {
				$find['is_accept_account'] = $_POST['is_accept_account'];
				$app_info = getAppInfoNew($find);
                //$app_info = $this->getAppInfo($find['dev_id'], $find['softname'], $find['p_fenlei'], substr($find['jianjie'],0,1400));
                $app_info = json_decode($app_info, true);
				if($app_info['ret']['appKey']){
                //if ($app_info['appManage']['appKey']) {
                    // $vals = array('appKey' => $app_info['appManage']['appKey'], 'status' => 1);
                    // $res = json_decode($this->modify_app($vals), true);
                    // if($res['code'] != 'success'){
                        // echo 6;
                        // exit(0);
                    // }else{
					$save_app = $this->saveAppInfo($find['package'], $app_info['ret']['appKey'], $app_info['ret']['appSecret'], '1');
                    // }
                } else {
                    echo 5;
                    exit(0);
                }
            } else {
                $save_app = true;
            }
			
            if ($save_app) {
                $pass = $model->newtonguo($soft_id, $find['package'], $_POST['fenlei'], $_POST['p_leixing'], $hztype, $zs_path, $apk_path,$_POST['is_accept_account']);
                $this->writelog('报表-产品状态列表-新提交产品,通过了产品，包名为:' . $find['package'].',id为'.$soft_id, 'yx_product',$soft_id,__ACTION__ ,'','edit');
                echo 1;
                exit(0);
            } else {
                echo 6;
                exit(0);
            }
        }

        $soft_id = $_GET['soft_id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($soft_id);
        $this->assign("rs", $rs);
        $this->display('tonguo');
    }

    //保存appkey和appscret
    public function saveAppInfo($package, $app_key = '', $app_secret = '', $status) {
        $app = M('');
        $table = 'newgomarket.sj_sdk_info';
        if ($app_key != '')
            $data['app_id'] = $app_key;
        if ($app_secret != '')
            $data['app_secret'] = $app_secret;
        $data['app_status'] = $status;
        $appinfo = $app->table($table)->where(array('package' => $package))->field('id')->find();
        if ($appinfo) {
			$data['up_tm'] = time();
            $res = $app->table($table)->where(array('package' => $package))->save($data);
        } else {
            $data['package'] = $package;
			$tmp_id = $app->table('sj_soft_tmp')->where(array('package' => $package,'status'=>'2','sdk_status'=>array('neq',0)))->field('id,dev_id')->order('id desc')->find();
			$data['tmp_id'] = $tmp_id['id'];
			$data['dev_id'] = $tmp_id['dev_id'];
			$data['add_tm'] = time();
            $res = $app->table($table)->add($data);
        }
        if ($res) {
            return true;
        }
    }

	
    //获取appkey和appscret
    public function getAppInfo($dev_id, $softname, $p_fenlei, $jianjie) {
        $vals['devNum'] = $dev_id;
        $vals['appName'] = $softname;
        $category = array(
            '网游' => '1',
            '单机' => '2',
            '棋牌' => '3',
        );
        $vals['appType'] = $category[$p_fenlei];

        $vals['description'] = empty($jianjie) ? '系统' : $jianjie;
        if ($p_fenlei == "单机") {
            //接入产品功能
            $vals['productFuns'] = C('other_productFuns');
        } else {
            $vals['productFuns'] = C('single_productFuns');
        }
        if ($p_fenlei == "网游") {
            //支付方式    
            $vals['payTypes'] = C('web_payTypes');
        } else if ($p_fenlei == "单机") {
            $vals['payTypes'] = C('single_payTypes');
        } else {
            $vals['payTypes'] = C('card_payTypes');
        }
        $res = curl_init();
        $pro_env = C('PRO_ENV');
        if($pro_env == 1){
            //线上
            $host = 'http://open.anzhi.com/service/appmanage/create';
        }else if($pro_env == 2){
            $host = 'http://open.anzhi.com/service/appmanage/create';
        }else if($pro_env == 3||$pro_env == 4){
            $host = 'http://dev.i.anzhi.com:9011/service/appmanage/create';
        }
        curl_setopt($res, CURLOPT_URL, $host);
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
        $result = curl_exec($res);
        $http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);
        $dir = P_LOG_DIR . "/518.anzhi.com/" . date("Y-m-d", time());
        if (!is_dir($dir))  mkdir($dir, 0755);
        file_put_contents($dir.'/create_apppkey.log',$http_code."\n".$errno."\n".$error."\n".$host.var_export($vals,true).var_export($result,true).date('Y-m-d H:i:s')."\n",FILE_APPEND);
        return $result;
    }

    //修改回调地址接口
    function modify_app($data) {
        $res = curl_init();
        $pro_env = C('PRO_ENV');
        if($pro_env == 1){
            //线上
            $host = 'http://open.anzhi.com/service/appmanage/modify';
        }else if($pro_env == 2){
            $host = 'http://open.anzhi.com/service/appmanage/modify';
        }else if($pro_env == 3||$pro_env == 4){
            $host = 'http://dev.i.anzhi.com:9011/service/appmanage/modify';
        }
        curl_setopt($res, CURLOPT_URL, $host);
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($res);
        $http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);
		$dir = P_LOG_DIR . "/518.anzhi.com/" . date("Y-m-d", time());
        if (!is_dir($dir))
            mkdir($dir, 0755);
		$date = date('Y-m-d H:i:s',time());
        file_put_contents($dir.'/modifyapp.log', "{$http_code}|{$errno}|{$error}|{$host}|{$host_dam}|{$date}\n" . print_r($data, true) . "\n" . print_r($result, true) . "\n\n", FILE_APPEND);
        return $result;
    }
    


    //ajax查看app信息
    public function show_appinfo() {
        $app = M('');
        $table = 'newgomarket.sj_sdk_info';
        $package = $_POST['package'];
        //$fenlei = $app->table('yx_product')->where(array('package' => $package,'del'=>0))->field('p_fenlei')->find();        
        $app_info = $app->table($table)->where(array('package' => $package))->field('app_id,app_secret,app_status')->order('app_status desc')->find();
            // echo $app->table($table)->getLastSql();
        echo json_encode($app_info);  
        
    }

    /**
     * 创建目录
     */
    private function createFolder($path) {
        if (!file_exists($path)) {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }

    /**
     *  新提交产品页驳回操作
     */
    function bohui() {
        if ($_POST) {
            $soft_id = $_POST['soft_id'];
            $hztype = $_POST['hztype'];
            $reason = $_POST['reason'];
            if (strlen($reason) > 300) {
                echo 3;
                exit(0);
            }
            if($_POST['o_reason']=='true'){
                $reason .= ';'.$this->other_reason;
            }

            $model = D('sendNum.Product');
            $res = $model->newbohui($soft_id, $hztype, $reason);
            if ($res > 0) {
                $this->writelog('报表-产品状态列表-新提交产品,驳回了产品，softid为:' . $soft_id, 'yx_product',$soft_id,__ACTION__ ,'','edit');
                echo 1;
                exit(0);
            } else {
                echo 2;
                exit(0);
            }
        }

        $soft_id = $_GET['soft_id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($soft_id);
        $this->assign("rs", $rs);
        $this->assign("other_reason",$this->other_reason);
        $this->display('bohui');
    }

    function deletepro() {
        if ($_POST) {
            $path = C('BAOBIAO_PATH');
            $soft_id = $_POST['soft_id'];
            $unpath = $path . $soft_id . '/';
            $model = D('sendNum.Product');
            $res = $model->getproductbyid($soft_id);
            unlink($res['zs_path']);
            unlink($res['apk_path']);
            unlink($res['test_path']);
            unlink($res['test2_path']);
            unlink($res['test3_path']);
            unlink($res['test4_path']);
            unlink($res['test5_path']);
            rmdir($unpath);
            $ress = $model->delpro($soft_id);
            if ($ress) {
                $this->del_sdk($res['package'], $res['step'], $res['is_new_soft'], $res['pre_package'],$res['p_fenlei']);
            }

            $this->writelog('报表-删除了产品,softid:' . $soft_id, 'yx_product',$soft_id,__ACTION__ ,'','del');
            echo 1;
            exit(0);
        }
    }

    //删除联运游戏
    function del_sdk($package, $step, $is_new_soft, $pre_package,$p_fenlei) {

        $model = D('sendNum.Product');
        if (!empty($package)) {
            $model->table('sj_soft_whitelist')->where(array('package'=>$package))->save(array('fen_lei'=>$p_fenlei));
            $soft = $model->table('sj_soft')->where(array('package' => $package, 'hide' => 1, 'status' => 1))->field('softname')->find();

            if ($is_new_soft == 0 && !$soft) {
                $model->table('sj_soft_tmp')->where(array('package' => $package))->save(array('status' => 0));
                if ($step != 5) {
                    update_soft_status(array('del' => 1, 'soft_status' => 0, 'debut_status' => 0, 'screen_status' => 0, 'sdk_status' => -1, 'is_new_sdk' => 0, 'gift_status' => 0, 'server_status' => '0', 'update_tm' => time()), $package);
                }
            } else if ($is_new_soft == 1 || $soft) {
                $tmp_id = $model->table('sj_sdk_info')->where(array('package' => $package))->field('tmp_id')->find();
                if ($tmp_id) {
                    $model->table('sj_soft_tmp')->where(array('id' => $tmp_id['tmp_id']))->save(array('status' => 0));
                }
                $is_sdk = $model->table('sj_soft_whitelist')->where(array('package' => $package, 'status' => 1))->field('is_sdk')->find();
                if (!empty($pre_package)) {
                    if ($pre_package != $package) {
                        $softname = $model->table('sj_soft')->where(array('package' => $pre_package, 'hide' => 1, 'status' => 1))->field('softname')->find();
                        if ($is_sdk['is_sdk'] == 0) {
                            update_soft_status(array('package' => $pre_package, 'softname' => $softname['softname'], 'is_new_sdk' => 0, 'soft_status' => 50, 'sdk_status' => -1, 'update_tm' => time()), $package);
                        } else {
                            update_soft_status(array('package' => $pre_package, 'softname' => $softname['softname'], 'sdk_status' => 7, 'soft_status' => 50,'update_tm' => time()), $package);
                        }
                    } else {
                        if ($is_sdk['is_sdk'] == 0) {
                            update_soft_status(array('soft_status' => 50, 'sdk_status' => -1, 'update_tm' => time()), $package);
                        } else {
                            update_soft_status(array('soft_status' => 50,'sdk_status' => 7, 'update_tm' => time()), $package);
                        }
                    }
                } else {
                    if ($is_sdk['is_sdk'] == 0) {
                        update_soft_status(array('soft_status' => 50, 'sdk_status' => -1, 'update_tm' => time()), $package);
                    }else{
                        update_soft_status(array('soft_status' => 50,'sdk_status' => 7, 'update_tm' => time()), $package);
                    }
                    
                }
            }
            $model->table('sj_sdk_info')->where(array('package' => $package))->save(array('tmp_id' => null,'agreement_path'=>'','agreement_name'=>'','agreement_uptm'=>''));
        }
    }

    function deletepro_testlist() {
        if ($_POST) {
            $path = C('BAOBIAO_PATH');
            $soft_id = $_POST['soft_id'];
            $unpath = $path . $soft_id . '/';
            $model = D('sendNum.Product');
            $res = $model->getproductbyid($soft_id);
            unlink($res['zs_path']);
            unlink($res['apk_path']);
            unlink($res['test_path']);
            unlink($res['test2_path']);
            unlink($res['test3_path']);
            unlink($res['test4_path']);
            unlink($res['test5_path']);
            rmdir($unpath);
            $ress = $model->delpro($soft_id);
            if ($ress) {               
                $this->del_sdk($res['package'], $res['step'], $res['is_new_soft'], $res['pre_package'],$res['p_fenlei']);
            }
            $this->writelog('报表-删除了产品,softid:' . $soft_id, 'yx_product',$soft_id,__ACTION__ ,'','del');
            echo 1;
            exit(0);
        }
    }

    function deletepro_readyonline() {
        if ($_POST) {
            $path = C('BAOBIAO_PATH');
            $soft_id = $_POST['soft_id'];
            $unpath = $path . $soft_id . '/';
            $model = D('sendNum.Product');
            $res = $model->getproductbyid($soft_id);
            unlink($res['zs_path']);
            unlink($res['apk_path']);
            unlink($res['test_path']);
            unlink($res['test2_path']);
            unlink($res['test3_path']);
            unlink($res['test4_path']);
            unlink($res['test5_path']);
            rmdir($unpath);
            $soft = $model->getproductbyid($soft_id);
            $res = $model->delpro($soft_id);
            if ($res) {
                $this->del_sdk($soft['package'], $soft['step'], $soft['is_new_soft'], $soft['pre_package'],$soft['p_fenlei']);
            }
            $this->writelog('报表-删除了产品,softid:' . $soft_id, 'yx_product',$soft_id,__ACTION__ ,'','del');
            echo 1;
            exit(0);
        }
    }

    /*
      function deletepro_online() {
      if($_POST){
      $path = C('BAOBIAO_PATH');
      $soft_id = $_POST['soft_id'];
      $unpath = $path.$soft_id.'/';
      $model = D('sendNum.Product');
      $res = $model->getproductbyid($soft_id);
      unlink($res['zs_path']);
      unlink($res['apk_path']);
      unlink($res['test_path']);
      rmdir($unpath);
      $model->delpro($soft_id);
      $this->writelog('报表-删除了产品,softid:'.$soft_id);
      echo 1;exit(0);
      }
      }
     */

    function deletepro_online() {
        if ($_GET) {
            $path = C('BAOBIAO_PATH');
            $model = D('sendNum.Product');
            $ids_arr = explode(',', $_GET['soft_ids']);
            foreach ($ids_arr as $soft_id) {
                $unpath = $path . $soft_id . '/';
                unlink($res['zs_path']);
                unlink($res['apk_path']);
                unlink($res['test_path']);
                unlink($res['test2_path']);
                unlink($res['test3_path']);
                unlink($res['test4_path']);
                unlink($res['test5_path']);
                rmdir($unpath);
                $soft = $model->getproductbyid($soft_id);
                $res = $model->delpro($soft_id);
                if ($res) {                    
                    $this->del_sdk($soft['package'],$soft['step'], $soft['is_new_soft'], $soft['pre_package'],$soft['p_fenlei']);
                }
                $this->writelog('报表-删除了产品,softid:' . $soft_id, 'yx_product',$soft_id,__ACTION__ ,'','del');
            }
            $this->success("删除成功");
        }
    }

    /**
     *  评测产品列表页
     */
    function testlist() {
        $admin_user_name = $_SESSION['admin']['admin_user_name'];
        $model = D('sendNum.Product');
        if(empty($_GET['orderby'])){
            $_GET['o'] = 1;
            $this->assign("o", $_GET['o']);
        }
        if(empty($_GET['orderby'])||$_GET['orderby']=='desc'){   
            $order = 'asc';
        }else if($_GET['orderby']=='asc'){
            $order = 'desc';
        }
        $this->assign("order", $order);

        $res = $model->getprolist($_GET, $_POST, 1);
        $list = $res['list'];
		$package = array();
        foreach ($list as $k => $v) {
            if (strlen($v['jianjie']) > 18) {
                $list[$k]['jianjie'] = $this->chgtitle($v['jianjie']);
            }
            if (strlen($v['beizhu']) > 18) {
                $list[$k]['beizhu'] = $this->chgtitle($v['beizhu']);
            }
            if (strlen($v['com_tj_tel']) > 18) {
                $list[$k]['com_tj_tel'] = $this->chgtitle($v['com_tj_tel']);
            }
            /*
              if($v['test_osname']==$admin_user_name){
              $list[$k]['is_tg'] = 1;
              }else{
              $list[$k]['is_tg'] = 0;
              }
             */
            // 判断当前用户是否已评测过
            $test_osname_arr = array($v['test_osname'], $v['test2_osname'], $v['test3_osname'], $v['test4_osname'], $v['test5_osname']);
            if (in_array($admin_user_name, $test_osname_arr)) {
                $list[$k]['is_tg'] = 1;
            } else {
                $list[$k]['is_tg'] = 0;
            }

            $list[$k]['testname'] = array_pop(explode('_', $v['test_path']));
            $list[$k]['testname2'] = array_pop(explode('_', $v['test2_path']));
            $list[$k]['testname3'] = array_pop(explode('_', $v['test3_path']));
            $list[$k]['testname4'] = array_pop(explode('_', $v['test4_path']));
            $list[$k]['testname5'] = array_pop(explode('_', $v['test5_path']));
			$package[] = $v['package'];
        }
		$where = array(
            'package' => array('in', $package),
        );
		$contract_status = get_table_data($where, "yx_contract", "package", "package,status");
		$this->assign("contract_status", $contract_status);
        $show = $res['show'];
        $sqlparam = $res['sqlparam'];

        $this->assign("softname", $_REQUEST['softname']);
        $this->assign("package", $_REQUEST['package']);
        $this->assign("companyname", $_REQUEST['companyname']);
        $this->assign("hztype", $_REQUEST['hztype']);
        $this->assign("is_debut", $_REQUEST['is_debut']);
        $this->assign("record_status", $_REQUEST['record_status']);
        $this->assign("publication_status", $_REQUEST['publication_status']);
        if (isset($_GET['zhuangtai'])) {
            $this->assign("zhuangtai", $_REQUEST['zhuangtai'] + 1);
        } else if (isset($_POST['zhuangtai'])) {
            $this->assign("zhuangtai", $_REQUEST['zhuangtai']);
        }
        $this->assign("osuser", $_REQUEST['osuser']);

        if (isset($_GET['down'])) {
            $allist = $res['allist'];
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=testlist.csv");

            $desc = "产品名称,包源大小,产品分类,产品类型,产品所属性质,公司名称,简介,联系方式,合作方式,操作人,评测级别,测试开始时间,新评测提交时间,备注,状态\r\n";
            foreach ($allist as $v) {
                switch ($v['status']) {
                    case 0:
                        $status = '待评测';
                        break;
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                        $num = $v['status'] - 2;
                        $status = "{$num}人已评测";
                        break;
                    default:
                        $status = "未知";
                        break;
                }

                $reviewlevel = strlen($v['reviewlevel']) > 0 ? $v['reviewlevel'] : '暂无';
                $reviewtime = strlen($v['reviewtime'] > 0) ? date('Y-m-d H:i:s', $v['reviewtime']) : '无';
                $beizhu = trim($v['beizhu']);
                $beizhu = str_replace("\n", " ", $beizhu);
                $beizhu = str_replace("\r", " ", $beizhu);
                $beizhu = str_replace(",", "，", $beizhu);

                $jianjie = trim($v['jianjie']);
                $jianjie = str_replace("\n", " ", $jianjie);
                $jianjie = str_replace("\r", " ", $jianjie);
                $jianjie = str_replace(",", "，", $jianjie);

                $com_tj_tel = trim($v['com_tj_tel']);
                $com_tj_tel = str_replace(",", "，", $com_tj_tel);

                $desc = $desc . $v['softname'] . ',' . $v['size'] . ',' . $v['p_fenlei'] . ',' . $v['p_leixing'] . ',' . $v['nature'] . ',' . $v['companyname'] . ',' . $jianjie . ',' . $com_tj_tel . ',' . $v['hztype'] . ',' . $v['osuser'] . ',' . $reviewlevel . ',' . date('Y-m-d H:i:s', $v['testime']) . ',' . $reviewtime . ',' . $beizhu . ',' . $status . "\r";
            }
            $desc = mb_convert_encoding($desc, 'gbk', 'utf8');
            echo $desc;
            exit(0);
        }

        if ($_POST) {
            $this->redirect('/Product/testlist?' . $sqlparam);
        }

        // 查找pu_config表看设置了几个人
        $find = $model->table('pu_config')->where("config_type='yx_baobiao' and configname='test_num' and status=1")->find();
        // status=3表示1人已评测，status=4表示2人已评测，所以test_num_status需加2，给页面进行判断
        $test_num_status = $find['configcontent'] + 2;
        $this->assign("page", $show);
        $this->assign("list", $list);
        $this->assign("test_num_status", $test_num_status);

        $this->assign("sqlparam", $sqlparam);
        $this->display('testlist');
    }
    function up_auth_file(){
        if($_POST['nature']=='代理'){
            $this->publication_record('dev_auth_url','dev_coop_auth_soft_ip','研发商授权','dev_auth_');
        }
        $this->publication_record('coop_auth_url','dev_coop_auth_soft_ip','安智合作授权','coop_auth');
        $this->publication_record('soft_auth_url','dev_coop_auth_soft_ip','软件著作权','soft_auth');
        $this->publication_record('ip_auth_url','del_ip','IP授权','ip_auth');
        
    }
    
    /**
     *  评测产品页面编辑
     */
    function edit() {
        $soft_id = $_GET['soft_id'];
        $model = D('sendNum.Product');
        $list = $model->getallproductbyid($soft_id);
	
        if ($this->isAjax()) {
            $model = D('sendNum.Product');
            $soft_id = $_POST['soft_id'];
            $softname_new = $_POST['softname_new'];
            $list = $model->getallproductbyid($soft_id);
            //如果没有改产品名称则不判断
            if ($list['softname'] == $softname_new) {
                echo 1;
                exit(0);
            } else { //如果改了产品名称，判断新产品名称是否唯一
                $ishas = $model->getproductbysoftname($softname_new);
                if ($ishas) {
                    echo 2;
                    exit(0);
                } else {
                    echo 1;
                    exit(0);
                }
            }
        }

        //读取数据
        if ($_POST) {
			
            // 获得编辑信息，以方便写数据库
			$list = $model->getallproductbyid($_POST['soft_id']);
            $where_arr = array('soft_id' => $_POST['soft_id']);
            $table_name = 'yx_product';
            $admin_user_name = $_SESSION['admin']['admin_user_name'];
			if($_POST['one_category']=='网游'){
				$_POST['is_accept_account'] = 1;
			}
            $this->publication_record('record_url','del_ba','备案','record_');
            $this->publication_record('publication_url','del_cb','出版','publication_');
            $this->up_auth_file();
            $column_arr = array(
                'softname' => $_POST['softname'],
                'size' => $_POST['size'],
                'nature' => $_POST['nature'],
                'companyname' => $_POST['companyname'],
                'jianjie' => $_POST['jianjie'],
                'osuser' => $admin_user_name,
                'hztype' => $_POST['hztype'],
                'com_tj_tel' => $_POST['com_tj_tel'],
                'beizhu' => $_POST['beizhu'],
                'p_fenlei' => $_POST['one_category'],
                'p_leixing' => $_POST['p_leixing'],
				'is_accept_account'=>$_POST['is_accept_account'],
                'record_num' => $_POST['record_num'],
                'publication_num' => $_POST['publication_num'],
                'record_url' => $_POST['record_url'],
                'dev_auth_url' => $_POST['dev_auth_url'],
                'coop_auth_url' => $_POST['coop_auth_url'],
                'soft_auth_url' => $_POST['soft_auth_url'],
                'ip_auth_url' => $_POST['ip_auth_url'],
            );
            if ($status == 1) {
                $column_arr['reason'] = $_POST['reason'];
            }
            $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
            $msg = "报表-产品状态列表-评测产品：编辑了id为{$_POST['soft_id']}的记录：";
            $msg .= $log_all_need;
			$edit = true;
			if($list['p_leixing']!=$_POST['p_leixing']||$list['is_accept_account']!=$_POST['is_accept_account']){
				$appkey = getAppKey($list['package']);
				$vals = array('appKey' => $appkey, 'p_fenlei' => $_POST['one_category'],'p_leixing'=>$_POST['two_category'],'isJoinUcenter'=>$_POST['is_accept_account']);
				$res = json_decode(modifyAppNew($vals), true);
				if(!$res['code']=='success'){
					$edit = false;
				}
			}
            // 准备写库
            $softid = $_POST['soft_id'];
			if($edit){
				$ret = $model->editproduct($softid, 0, $_POST);
				$this->assign("jumpUrl", "/index.php/Sendnum/Product/testlist");
				if ($ret === false) {
					$this->success('修改失败');
				}
				$this->writelog($msg, 'yx_product',$_POST['soft_id'],__ACTION__ ,'','edit');
				$this->success('修改成功');
			}else{
				$this->success('分类同步到用户中心失败，请重试');
			}
            
        }
        //获取分类信息
        $comment_one_category = array(
            '1' => '网游',
            '2' => '单机'
        );
        $comment_two_category = array(
            '1' => 'SLG',
            '2' => 'MMORPG',
            '3' => 'ARPG',
            '4' => 'FPS',
            '5' => 'TPS',
            '6' => '策略经营',
            '7' => '动作格斗',
            '8' => '动作射击',
            '9' => '格斗动作',
            '10' => '回合角色扮演',
            '11' => '卡牌',
            '12' => '模拟经营',
            '13' => '棋牌',
            '14' => '射击',
            '15' => '塔防',
            '16' => '休闲竞速',
            '17' => '休闲益智',
            '18' => '益智'
        );
        $one_category = array(
            '1' => '网游',
            '2' => '单机',
            '3' => '棋牌'
        );
        $two_category = array(
            '1' => '卡牌',
            '2' => '策略',
            '3' => '角色扮演',
            '4' => '动作格斗',
            '5' => '飞行射击',
            '6' => '竞速',
            '7' => '棋牌',
            '8' => '休闲益智',
            '9' => '模拟经营'
        );
        if ($list['from'] == 1) {
            $this->assign('one_category', $one_category);
            $this->assign('two_category', $two_category);
        } else {
            $this->assign('one_category', $comment_one_category);
            $this->assign('two_category', $comment_two_category);
        }

        $this->assign('list', $list);
        $this->assign('status', $status);
        $this->assign('soft_id', $soft_id);
        $this->display('edit');
    }

    /**
     *  通用下载方法 根据type下载对应文件
     */
    function down() {
        ini_set('memory_limit', '512M');
        $soft_id = $_GET['soft_id'];
        $type = $_GET['type'];
        $model = D('sendNum.Product');
        $list = $model->getallproductbyid($soft_id);
        $split_flag = '/';
        if ($_GET['from'] == 1) {
            $str = UPLOAD_PATH;
        }
        if ($type == 1) {
            $file_dir = $str . $list['apk_path'];
        } else if ($type == 2) {
            $file_dir = $str . $list['zs_path'];
        } else if ($type == 3) {
            $file_dir = $list['test_path'];
            $split_flag = '_';
        } else if ($type == 4) {
            $file_dir = $list['test2_path'];
            $split_flag = '_';
        } else if ($type == 5) {
            $file_dir = $list['test3_path'];
            $split_flag = '_';
        } else if ($type == 6) {
            $file_dir = $list['test4_path'];
            $split_flag = '_';
        } else if ($type == 7) {
            $file_dir = $list['test5_path'];
            $split_flag = '_';
        }
        $tmp = explode($split_flag, $file_dir);
        $name = array_pop($tmp);
        $name = iconv('utf-8', 'gbk', $name);
        if (!file_exists($file_dir)) {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        } else {
            $file = fopen($file_dir, "r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . $name);
            echo fread($file, filesize($file_dir));
            fclose($file);
        }
    }

    /**
     *  评测产品页通过操作
     */
    function tonguo2() {
        $path = C('BAOBIAO_PATH');
        if ($_POST) {
            $model = D('sendNum.Product');
            $admin_user_name = $_SESSION['admin']['admin_user_name'];
            $soft_id = $_POST['soft_id'];
            $softname = $_POST['hh_softname'];
            $reviewlevel = $_POST['reviewlevel'];
			//$is_accept_account = $_POST['is_accept_account'];
            set_time_limit(300);
            $testosname = $model->getestosname($soft_id);
			//$softinfo = $model->table('yx_product')->where("soft_id = {$soft_id}")->field('package,is_accept_account')->find();
            if ($testosname['test_osname'] == $admin_user_name) {
                echo 4;
                exit(0);
            }

            $tmp_houzhui2 = $_FILES['upload']['name'][0];
            $tmp_arr2 = explode('.', $tmp_houzhui2);
            $houzhui2 = array_pop($tmp_arr2);

            if (strtoupper($houzhui2) != 'DOC' && strtoupper($houzhui2) != 'DOCX' && strtoupper($houzhui2) != 'XLS' && strtoupper($houzhui2) != 'XLSX') {
                echo 2;
                exit(0);
            }
            $path = $path . $soft_id . '/';
            $this->createFolder($path);

            $test_path = $path . $softname . '_' . $admin_user_name . '.' . $houzhui2;
            //$test_path =$path.$tmp_houzhui2;  // 原文件名

            $is_succ_test = move_uploaded_file($_FILES['upload']['tmp_name'][0], $test_path);
            if (empty($is_succ_test)) {
                echo 3;
                exit(0);
            }
			
            $res = $model->newtonguo2($soft_id, $softname, $reviewlevel, $test_path);            
            if($res){               
                $this->writelog('报表-产品状态列表-评测产品,评测了产品，产品名称为:' . $softname, 'yx_product',$soft_id,__ACTION__ ,'','edit');
                echo 1;
                exit(0);
            }else{
                echo 5;
                exit(0);
            }
            
            
        }

        $soft_id = $_GET['soft_id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($soft_id);
        $this->assign("rs", $rs);
        $this->display('tonguo2');
    }

    /**
     *  评测产品页通过操作
     */
    function test_tg() {
        if ($_POST) {
            $soft_id = $_POST['soft_id'];
            $softname = $_POST['softname'];
            $model = D('sendNum.Product');
            $status = $model->getstatus($soft_id);
            // 查找pu_config表看设置了几个人
            $find = $model->table('pu_config')->where("config_type='yx_baobiao' and configname='test_num' and status=1")->find();
            $test_num = $find['configcontent'];
            if ($status['status'] < $test_num + 2) {
                echo 2;
                exit(0);
            }
            // if ($status['singletype'] == 1) {
                // $step = 3;
            // } else {
                $step = 2;
            // }
            $res = $model->updatetype($soft_id, 2, $step);
			$data = array(
				'package' => $status['package'],
				'dev_id' => $status['dev_id'],
				'softname' => $status['softname'],
				'fen_lei' => $status['p_fenlei'],
				'is_accept_account' =>$status['is_accept_account'],
				'is_accept_sdk' =>1
			);
			if($status['p_fenlei']=='单机(运营商)'){
				$data['is_accept_pay'] = 0;
			}else{
				$data['is_accept_pay'] = 1;
			}
            $model->add_soft_whitelist($data);
            if ($res) {
                $model->table('sj_soft_status')->where(array('package' => $status['package']))->save(array('sdk_status' => $step));
            }
            if ($res && $_POST['dev_id']) {
                //发送提醒信息			
                $tm = date("Y-m-d", time());
                $emailmodel = D("Dev.Sendemail");
                $config_txt = C('_config_txt_');
				$path = C('agreement_path');
                $search = array("softname", "tm");
                $replace = array($softname, $tm);
                $msg = str_replace($search, $replace, $config_txt['sdk_pass']);
                $emailmodel->dev_remind_add($_POST['dev_id'], $msg);
				
				//发送邮件提醒
				$model = new Model();
				$dever = $model->table('pu_developer')->where("dev_id={$_POST['dev_id']}")->field('dev_id,email,dev_name')->find();
				$subject = $config_txt['tongguo_sdk_subject'];
				$search2 = array("devname", "softname", "today","path");
				$replace2 = array($dever['dev_name'], $softname, $tm,$path);
				$email_cont = str_replace($search2, $replace2, $config_txt['tongguo_sdk_txt']);
				$emailmodel->realsend($dever['email'], $dever['dev_name'], $subject, $email_cont);
            }
            $this->writelog('报表-通过了产品,softid:' . $soft_id, 'yx_product',$soft_id,__ACTION__ ,'','edit');
            echo 1;
            exit(0);
        }
    }

    /**
     *  评测产品页驳回操作
     */
    function bohui2() {
        $path = C('BAOBIAO_PATH');
        $model = D('sendNum.Product');
        if ($_POST) {
            //删文件
            $soft_id = $_POST['soft_id'];
            $unpath = $path . $soft_id . '/';

            $res = $model->getproductbyid($soft_id);
            unlink($res['test_path']);
            unlink($res['test2_path']);
            unlink($res['test3_path']);
            unlink($res['test4_path']);
            unlink($res['test5_path']);
            unlink($res['zs_path']); // 删除证书
            unlink($res['apk_path']); // 删除apk

            $user_name = $_SESSION['admin']['admin_user_name'];
            $newsoftname = $_POST['newsoftname'];
            //supwater 驳回原因
            $reasontest = $_POST['reasontest'];
            set_time_limit(300);

            $tmp_houzhui2 = $_FILES['upload']['name'][0];
            if (strlen($tmp_houzhui2) > 0) {
                $tmp_arr2 = explode('.', $tmp_houzhui2);
                $houzhui2 = array_pop($tmp_arr2);

                if (strtoupper($houzhui2) != 'DOC' && strtoupper($houzhui2) != 'DOCX' && strtoupper($houzhui2) != 'XLS' && strtoupper($houzhui2) != 'XLSX') {
                    echo 2;
                    exit(0);
                }
                $path = $path . $soft_id . '/';
                $this->createFolder($path);

                //supwater 文件名处理
                $test_path = $path . $newsoftname . '_' . $user_name . '.' . $houzhui2;
                //$test_path =$path.$tmp_houzhui2;  // 原文件名

                $is_succ_test = move_uploaded_file($_FILES['upload']['tmp_name'][0], $test_path);
                if (empty($is_succ_test)) {
                    echo 3;
                    exit(0);
                }
            }
            $can_pass = true;
            if ($_POST['changeapp'] && $_POST['changeapp'] == 1) {
                $app = M('');
                if ($_POST['package'] && !empty($_POST['package'])) {
                    //将app置为无效
                    $has_app = $model->table('sj_sdk_info')->where(array('package' => $_POST['package']))->field('app_id,app_status')->find();
                    //$vals = array('appKey' => $has_app['app_id'], 'status' => 4);
					$vals = array('appKey' => $has_app['app_id'], 'status' => 0);
                    //$res = json_decode($this->modify_app($vals), true);
					$res = json_decode(modifyAppNew($vals), true);
                    if ($res['code'] != 'success') {
                        $can_pass = false;
                        echo 5;
                        exit(0);
                    } else {
                        $this->writelog('报表-产品状态列表-评测产品,将包名为:' . $_POST['package'].'的appkey置为无效', 'yx_product',$_POST['package'],__ACTION__ ,'','edit');
                        $change_app = $model->table('newgomarket.sj_sdk_info')->where(array('package' => $_POST['package']))->save(array('app_status' => 0));
                        if (!$change_app) {
                            $can_pass = false;
                            echo 5;
                            exit(0);
                        }
                    }
                } else {
                    //包名不存在
                    echo 4;
                    exit(0);
                }
            }

            if ($can_pass) {
                $ret = $model->newbohui2($soft_id, $package, $test_path, $reasontest);
            }

            if ($ret && $_POST['dev_id']) {
                //发送提醒信息			
                $tm = date("Y-m-d", time());
                $emailmodel = D("Dev.Sendemail");
                $config_txt = C('_config_txt_');
                $search = array("softname", "tm", "msg");
                $replace = array($newsoftname, $tm, $reasontest);
                $msg = str_replace($search, $replace, $config_txt['sdk_reject']);
                $emailmodel->dev_remind_add($_POST['dev_id'], $msg);
                //发送邮件提醒
                $model = new Model();
                $dever = $model->table('pu_developer')->where("dev_id={$_POST['dev_id']}")->field('dev_id,email,dev_name')->find();
                $subject = $config_txt['sdk_subject'];
                $search2 = array("devname", "softname", "tm", "msg");
                $replace2 = array($dever['dev_name'], $newsoftname, $tm, $reasontest);
                $email_cont = str_replace($search2, $replace2, $config_txt['sdk_reject_txt']);
                $emailmodel->realsend($dever['email'], $dever['dev_name'], $subject, $email_cont);
            }
            $this->writelog('报表-产品状态列表-评测产品,驳回了产品，包名为:' . $_POST['package'], 'yx_product',$_POST['package'],__ACTION__ ,'','edit');
            echo 1;
            exit(0);
        }

        $soft_id = $_GET['soft_id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($soft_id);
        $this->assign("rs", $rs);
        $this->display('bohui2');
    }

    /**
     *  待上线产品列表页
     */
    function readyonline() {
        $model = D('sendNum.Product');
        if(empty($_GET['orderby'])){
            $_GET['o'] = 1;
            $this->assign("o", $_GET['o']);
        }
        if(empty($_GET['orderby'])||$_GET['orderby']=='desc'){   
            $order = 'asc';
        }else if($_GET['orderby']=='asc'){
            $order = 'desc';
        }
        $this->assign("order", $order);

        $res = $model->getprolist($_GET, $_POST, 2);
        $list = $res['list'];
        $package = array();
        foreach ($list as $k => $v) {
            if (strlen($v['jianjie']) > 18) {
                $list[$k]['jianjie'] = $this->chgtitle($v['jianjie']);
            }
            if (strlen($v['beizhu']) > 18) {
                $list[$k]['beizhu'] = $this->chgtitle($v['beizhu']);
            }
            if (strlen($v['com_tj_tel']) > 18) {
                $list[$k]['com_tj_tel'] = $this->chgtitle($v['com_tj_tel']);
            }

            $list[$k]['testname'] = array_pop(explode('_', $v['test_path']));
            $list[$k]['testname2'] = array_pop(explode('_', $v['test2_path']));
            $list[$k]['testname3'] = array_pop(explode('_', $v['test3_path']));
            $list[$k]['testname4'] = array_pop(explode('_', $v['test4_path']));
            $list[$k]['testname5'] = array_pop(explode('_', $v['test5_path']));
            if ($v['package'])
                $package[] = $v['package'];
        }
		$sdk_info = $model->get_sdk_down_path( $package);
        $this->assign("sdk_info", $sdk_info);
        $where = array(
            'package' => array('in', $package),
        );
        $soft_status = get_table_data($where, "sj_soft_status", "package", "package,sdk_status,soft_status");
		$whitelist_status = get_table_data($where, "sj_soft_whitelist", "package", "package,is_accept_account");
		$contract_status = get_table_data($where, "yx_contract", "package", "package,status");
		$this->assign("contract_status", $contract_status);
		$this->assign("whitelist_status", $whitelist_status);
        $this->assign("soft_status", $soft_status);
        $show = $res['show'];
        $sqlparam = $res['sqlparam'];

        $this->assign("softname", $_REQUEST['softname']);
        $this->assign("package", $_REQUEST['package']);
        $this->assign("companyname", $_REQUEST['companyname']);
        $this->assign("hztype", $_REQUEST['hztype']);
        $this->assign("osuser", $_REQUEST['osuser']);
        $this->assign("reviewlevel", $_REQUEST['reviewlevel']);
		$this->assign("p_fenlei", $_REQUEST['p_fenlei']);
        $this->assign("is_debut", $_REQUEST['is_debut']);
        $this->assign("st_status", $_REQUEST['soft_status']);
        $this->assign("record_status", $_REQUEST['record_status']);
        $this->assign("publication_status", $_REQUEST['publication_status']);
        if ($_GET['reviewbegintime']) {
            $this->assign("reviewbegintime", date('Y-m-d H:i:s', $_REQUEST['reviewbegintime']));
        } else {
            $this->assign("reviewbegintime", $_REQUEST['reviewbegintime']);
        }

        if ($_GET['reviewendtime']) {
            $this->assign("reviewendtime", date('Y-m-d H:i:s', $_REQUEST['reviewendtime']));
        } else {
            $this->assign("reviewendtime", $_REQUEST['reviewendtime']);
        }

        if (isset($_GET['down'])) {
            $allist = $res['allist'];
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=readyonline.csv");

            $desc = "产品名称,包名,包源大小,产品分类,产品类型,产品所属性质,公司名称,简介,联系方式,合作方式,操作人,接入测试进度,测评级别,评测通过时间,接入时间,备注\r\n";
            foreach ($allist as $v) {
                $jierutime = strlen($v['jierutime'] > 0) ? date('Y-m-d H:i:s', $v['jierutime']) : '无';
                $reviewtime = date('Y-m-d H:i:s', $v['reviewtime']);

                $beizhu = trim($v['beizhu']);
                $beizhu = str_replace("\n", " ", $beizhu);
                $beizhu = str_replace("\r", " ", $beizhu);
                $beizhu = str_replace(",", "，", $beizhu);

                $jianjie = trim($v['jianjie']);
                $jianjie = str_replace("\n", " ", $jianjie);
                $jianjie = str_replace("\r", " ", $jianjie);
                $jianjie = str_replace(",", "，", $jianjie);

                $com_tj_tel = trim($v['com_tj_tel']);
                $com_tj_tel = str_replace(",", "，", $com_tj_tel);

                $desc = $desc . $v['softname'] . ',' . $v['package'] .',' . $v['size'] . ',' . $v['p_fenlei'] . ',' . $v['p_leixing'] . ',' . $v['nature'] . ',' . $v['companyname'] . ',' . $jianjie . ',' . $com_tj_tel . ',' . $v['hztype'] . ',' . $v['osuser'] . ',' . $v['jierujindu'] . ',' . $v['reviewlevel'] . ',' . $reviewtime . ',' . $jierutime . ',' . $beizhu . "\r";
            }
            // $desc = iconv('utf-8', 'gbk', $desc);
            $desc = mb_convert_encoding($desc, 'gbk', 'utf8');
            echo $desc;
            exit(0);
        }

        if ($_POST) {
            $this->redirect('/Product/readyonline?' . $sqlparam);
        }
//        var_dump($list);
        $this->assign("page", $show);
        $this->assign("list", $list);

        $this->assign("sqlparam", $sqlparam);
        $this->display('readyonline');
    }
	//获取联运游戏接入sdk，接入账号，接入支付信息
	function get_softwhitelist_info($package){
		$model = D('sendNum.Product');
		$info = $model->table('sj_soft_whitelist')->where(array('package'=>$package,'status'=>1))->field('is_accept_account,is_accept_sdk,is_accept_pay')->find();
		return $info;
	}
    /**
     *  待上线编辑
     */
    function editready() {
        $soft_id = $_REQUEST['soft_id'];
        $model = D('sendNum.Product');
        $list = $model->getallproductbyid($soft_id);
		$whitelist_info = $this->get_softwhitelist_info($list['package']);
        if ($this->isAjax()) {
            $model = D('sendNum.Product');
            $soft_id = $_POST['soft_id'];
            $softname_new = $_POST['softname_new'];
            $list = $model->getallproductbyid($soft_id);
            //如果没有改产品名称则不判断
            if ($list['softname'] == $softname_new) {
                echo 1;
                exit(0);
            } else { //如果改了产品名称，判断新产品名称是否唯一
                $ishas = $model->getproductbysoftname($softname_new);
                if ($ishas) {
                    echo 2;
                    exit(0);
                } else {
                    echo 1;
                    exit(0);
                }
            }
        }

        //读取数据
        if ($_POST) {
            // 获得编辑信息，以方便写数据库
            $where_arr = array('soft_id' => $_POST['soft_id']);
            $table_name = 'yx_product';
            $admin_user_name = $_SESSION['admin']['admin_user_name'];
			$this->publication_record('record_url','del_ba','备案','record_');
            $this->publication_record('publication_url','del_cb','出版','publication_');
            $this->up_auth_file();
            $column_arr = array(
                'softname' => $_POST['softname'],
                'size' => $_POST['size'],
                'nature' => $_POST['nature'],
                'companyname' => $_POST['companyname'],
                'jianjie' => $_POST['jianjie'],
                'hztype' => $_POST['hztype'],
                'com_tj_tel' => $_POST['com_tj_tel'],
                'reviewlevel' => $_POST['reviewlevel'],
                'jierujindu' => $_POST['jierujindu'],
                'jierutime' => strtotime($_POST['jierutime']),
                'p_leixing' => $_POST['p_leixing'],
                'beizhu' => $_POST['beizhu'],
                'osuser' => $admin_user_name,
                'record_num' => $_POST['record_num'],
                'publication_num' => $_POST['publication_num'],
                'record_url' => $_POST['record_url'],
                'publication_url' => $_POST['publication_url'],
                'dev_auth_url' => $_POST['dev_auth_url'],
                'coop_auth_url' => $_POST['coop_auth_url'],
                'soft_auth_url' => $_POST['soft_auth_url'],
                'ip_auth_url' => $_POST['ip_auth_url'],
            );
            if ($_POST['pack_limit'] && $_POST['pack_limit'] == 'on') {
                $column_arr['pack_limit'] = 1;
                $_POST['pack_limit'] = 1;
            }
			$this->relevanceUcenter($_POST,$whitelist_info,$list);
			
			
            $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
            $msg = "报表-产品状态列表-评测产品：编辑了soft_id为{$_POST['soft_id']}的记录：";
            $msg .= $log_all_need;
            // 准备写库
            $rs = $model->editready($_POST);
            $this->assign("jumpUrl", "/index.php/Sendnum/Product/readyonline");
            if ($rs === false) {
                $this->error('修改失败');
            }
            $this->writelog($msg, 'yx_product',$_POST['soft_id'],__ACTION__ ,'','edit');
            $this->success('修改成功');
        }
		$this->assign('one_category',$this->one_category);
		$this->assign('whitelist_info', $whitelist_info);
        $this->assign('list', $list);
        $this->assign('soft_id', $soft_id);
        $this->display('editready');
    }
	private function relevanceUcenter($post,$whitelist_info,$list){
		//是否保存到白名单及同步到用户中心
		$model = M('');
		$need_relevance = $need = false;
		$check_arr = array('is_accept_account','is_accept_sdk','is_accept_pay');
		foreach($check_arr as $k=>$v){
			if(isset($post[$v])&&$whitelist_info[$v]!=$post[$v]) $need_relevance = true;
		}
		if($list['p_fenlei']!=$_POST['one_category']||$list['p_leixing']!=$_POST['p_leixing']) $need = true;
		if($need_relevance||$need){
			if($need_relevance){
				$save_whitelist = $model->table('sj_soft_whitelist')->where(array('package'=>$list['package'],'status'=>1))->save(array('is_accept_account'=>$post['is_accept_account'],'is_accept_sdk'=>$post['is_accept_sdk'],'is_accept_pay'=>$post['is_accept_pay']));
			}
			
			if($need_relevance||$need){
				$appkey = getAppKey($list['package']);
				$data = array(
					'appKey'=>$appkey					
				);
				if($need) $data['p_leixing'] = $post['two_category'];
				if($list['p_fenlei']=='单机'){
					if($post['is_accept_sdk']==1){
						$data['appType'] = 1;
					}else{
						$data['appType'] = 3;
					}
					if($post['is_accept_account']==1){
						$data['isJoinUcenter'] = 1;
					}else{
						$data['isJoinUcenter'] = 0;
					}
				}else if($list['p_fenlei']=='棋牌'){
					if($post['is_accept_account']==1){
						$data['isJoinUcenter'] = 1;
					}else{
						$data['isJoinUcenter'] = 0;
					}
				}
				modifyAppNew($data);
			}
		}
	}
    /**
     *  新提交产品页通过操作
     */
    function setonline() {
        if ($_POST) {
            list($msec, $sec) = explode(' ', microtime());
            $msec = substr($msec, 2);
            $path = UPLOAD_PATH . '/data2/agreement/' . date('Ym/d') . '/';
            $this->createFolder($path);
            $model = D('sendNum.Product');
            //如果有新上传的 要删掉旧的，如果没有上传，则保持原状态
            if (strlen($_POST['fuzeren']) > 0) {
                $res = $model->is_exsit_admin($_POST['fuzeren']);
                if ($res == 0) {
                    echo 2;
                    exit(0);
                }
            }
            

            $soft_id = $_POST['soft_id'];
            $softname = $_POST['hhh_softname'];
            set_time_limit(300);

            $tmp_houzhui2 = $_FILES['upload']['name'][0];
            if (strlen($tmp_houzhui2) > 0) {
                $array = array('jpg', 'png', 'pdf');
                $ytype = $_FILES['upload']['name'][0];
                $info = pathinfo($ytype);
                $type = $info['extension']; //获取文件件扩展名
                if (!in_array($type, $array)) {
                    echo 3;
                    exit(0);
                }
                $zs_path = $path . 'zs_' . $msec . '.' . $type;
                if (move_uploaded_file($_FILES['upload']['tmp_name'][0], $zs_path)) {
                    $test_path = str_replace(UPLOAD_PATH, '', $zs_path);
                }
            }
            if ($_FILES['agreement']['tmp_name']) {
                $array = array('zip', 'pdf');
                $ytypes = $_FILES['agreement']['name'];
                $info1 = pathinfo($ytypes);
                $type = $info1['extension']; //获取文件件扩展名
                if (!in_array($type, $array)) {
                    exit(5);
                }
                $agreement_path = $path . 'agreement_' . $msec . '.' . $type;
                if (move_uploaded_file($_FILES['agreement']['tmp_name'], $agreement_path)) {
                    $ht_path = str_replace(UPLOAD_PATH, '', $agreement_path);
                }
            }
            if ($ht_path != '') {
                $map = array(
                    'agreement_path' => $ht_path,
					'agreement_uptm'=>time()
                );
                $model->table('sj_sdk_info')->where("package='{$_POST['ol_package']}'")->save($map);
            }
            $model->setonline($_POST, $test_path);
            if(isset($_POST['p_fenlei']) && $_POST['p_fenlei'] != ''){				

                $has_app = $model->table('sj_sdk_info')->where(array('package' => $_POST['ol_package']))->field('app_id')->find();

				$data = array();
				if($_POST['p_fenlei'] == '网游'){
					$data['game_type'] = 1;
					$data['type'] = 2;
				}else if($_POST['p_fenlei'] == '棋牌'){
					$data['game_type'] = 2;
					$data['type'] = 0;
				}else{
					$data['game_type'] = 0;
					$data['type'] = 0;
				}
				if($data['game_type']==1||$data['game_type']==2){
				    $data['op_type'] = 1;
				    $data['app_key'] = $has_app['app_id'];
				    $data['dev_status'] = 0;
				    $data['pkg'] = $_POST['ol_package'];				    
				    //updata_app_message($data);
				}

            }
     
            $this->writelog('报表-产品状态列表-待上线产品,上线了产品，soft_id为:' . $_POST['soft_id'], 'yx_product',$_POST['soft_id'],__ACTION__ ,'','edit');
            echo 1;
            exit(0);
        }
        $soft_id = $_GET['soft_id'];
        $model = D('sendNum.Product');
        $rs = $model->getproductbyid($soft_id);
        $zsname = array_pop(explode('/', $rs['zs_path']));
        $this->assign("zsname", $zsname);
        $this->assign("p_fenlei", $rs['p_fenlei']);
        $this->assign("p_leixing", $rs['p_leixing']);
        $rt = $model->get_agreement($rs['package']);
        $this->assign("sdk_list", $rt);
        $this->assign("rs", $rs);
		$reviewlevel =  array("A","B","C","D","S","B-","B+","A+","A-");
		$this->assign("reviewlevel", $reviewlevel);
        $this->display('setonline');
    }

    /**
     *  全局搜索
     */
    function search() {
        $model = D('sendNum.Product');
        $sqlparam = '';
        $where['del'] = 0;
        if (isset($_GET['softname'])) {
            $sqlparam = $sqlparam . 'softname=' . $_GET['softname'] . '&';
            $where['softname'] = array('like', '%' . $_GET['softname'] . '%');
        }
        if (isset($_GET['package'])) {
            $sqlparam = $sqlparam . 'package=' . $_GET['package'] . '&';
            $where['package'] = array('EQ', $_GET['package']);
        }
        if (isset($_GET['companyname'])) {
            $sqlparam = $sqlparam . 'companyname=' . $_GET['companyname'] . '&';
            $where['companyname'] = array('EQ', $_GET['companyname']);
        }

        if ($_POST) {
            if (strlen($_POST['softname']) > 0) {
                $sqlparam = $sqlparam . 'softname=' . $_POST['softname'] . '&';
                $where['softname'] = array('like', '%' . $_POST['softname'] . '%');
            }
            if (strlen($_POST['package']) > 0) {
                $sqlparam = $sqlparam . 'package=' . $_POST['package'] . '&';
                $where['package'] = array('EQ', $_POST['package']);
            }
            if (strlen($_POST['companyname']) > 0) {
                $sqlparam = $sqlparam . 'companyname=' . $_POST['companyname'] . '&';
                $where['companyname'] = array('EQ', $_POST['companyname']);
            }
        }

        import("@.ORG.Page");
        $count = $model->where($where)->count();
        $page = new Page($count, 50);
        $list = $model->field("`soft_id`,`softname`,`package`,`companyname`,`type`")->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('soft_id desc')->select();

        $page->parameter = $sqlparam;
        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $show = $page->show();
        if ($_POST) {
            $this->redirect('/Product/search?' . $sqlparam);
        }
        $this->assign("softname", $_REQUEST['softname']);
        $this->assign("package", $_REQUEST['package']);
        $this->assign("companyname", $_REQUEST['companyname']);
        $this->assign("page", $show);
        $this->assign("list", $list);
        $this->display('search');
    }

    // 上线列表
    function online() {
        $model = D('sendNum.Product');
        if(empty($_GET['orderby'])){
            $_GET['o'] = 1;
            $this->assign("o", $_GET['o']);
        }
        if(empty($_GET['orderby'])||$_GET['orderby']=='desc'){   
            $order = 'asc';
        }else if($_GET['orderby']=='asc'){
            $order = 'desc';
        }
        $this->assign("order", $order);
        $res = $model->getprolist($_GET, $_POST, 3);
        $list = $res['list'];
        $show = $res['show'];
        $sqlparam = $res['sqlparam'];
        $package = array();
        foreach ($list as $k => $v) {
            if (strlen($v['beizhu']) > 18) {
                $list[$k]['beizhu'] = $this->chgtitle($v['beizhu']);
            }
            if (strlen($v['com_tj_tel']) > 18) {
                $list[$k]['com_tj_tel'] = $this->chgtitle($v['com_tj_tel']);
            }
            if (strlen($v['bili']) > 18) {
                $list[$k]['bili'] = $this->chgtitle($v['bili']);
            }
            /*
              $test_path_arr = explode('_',$list[$k]['test_path']);
              $list[$k]['test_path_name'] = array_pop($test_path_arr);
              $test2_path_arr = explode('_',$list[$k]['test2_path']);
              $list[$k]['test2_path_name'] = array_pop($test2_path_arr);
             */
            $list[$k]['testname'] = array_pop(explode('_', $v['test_path']));
            $list[$k]['testname2'] = array_pop(explode('_', $v['test2_path']));
            $list[$k]['testname3'] = array_pop(explode('_', $v['test3_path']));
            $list[$k]['testname4'] = array_pop(explode('_', $v['test4_path']));
            $list[$k]['testname5'] = array_pop(explode('_', $v['test5_path']));
            if ($v['package'])
                $package[] = $v['package'];
        }
        $sdk_info = $model->get_sdk_down_path($package);
		$where = array(
            'package' => array('in', $package),
        );
		$soft_status = get_table_data($where, "sj_soft_status", "package", "package,sdk_status,soft_status");
		$contract_status = get_table_data($where, "yx_contract", "package", "package,status,start_tm,end_tm");	
		$whitelist_status = get_table_data($where, "sj_soft_whitelist", "package", "package,is_accept_account,is_accept_sdk,is_accept_pay");
		$this->assign("whitelist_status", $whitelist_status);
		$this->assign("contract_status", $contract_status);		
		$this->assign("soft_status", $soft_status);	
        $this->assign("sdk_info", $sdk_info);
        $this->assign("softname", $_REQUEST['softname']);
        $this->assign("package", $_REQUEST['package']);
        $this->assign("fuzeren", $_REQUEST['fuzeren']);
        $this->assign("p_fenlei", $_REQUEST['p_fenlei']);
        $this->assign("p_leixing", $_REQUEST['p_leixing']);
        $this->assign("fc_type", $_REQUEST['fc_type']);
        $this->assign("companyname", $_REQUEST['companyname']);
        $this->assign("reviewlevel", $_REQUEST['reviewlevel']);
        $this->assign("hztype", $_REQUEST['hztype']);
        $this->assign("is_debut", $_REQUEST['is_debut']);
        $this->assign("record_status", $_REQUEST['record_status']);
        $this->assign("publication_status", $_REQUEST['publication_status']);
        if ($_GET['onlinebegintime']) {
            $this->assign("onlinebegintime", date('Y-m-d H:i:s', $_REQUEST['onlinebegintime']));
        } else {
            $this->assign("onlinebegintime", $_REQUEST['onlinebegintime']);
        }

        if ($_GET['onlineendtime']) {
            $this->assign("onlineendtime", date('Y-m-d H:i:s', $_REQUEST['onlineendtime']));
        } else {
            $this->assign("onlineendtime", $_REQUEST['onlineendtime']);
        }

        if (isset($_GET['down'])) {
            $allist = $res['allist'];
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=online.csv");

            $desc = "产品名称,包名,包源大小,SDK,负责人,产品分类,产品类型,合同签订时间,合同结束时间,通道费比例,公司名称,联系方式,合作方式,分成方式,评测级别,创建时间,上线时间,备注\r";
            foreach ($allist as $v) {
                $softname = $v['softname_bai'] ? $v['softname_bai'] : $v['softname'];

                $beizhu = trim($v['beizhu']);
                $beizhu = str_replace("\n", " ", $beizhu);
                $beizhu = str_replace("\r", " ", $beizhu);
                $beizhu = str_replace(",", "，", $beizhu);

                $bili = trim($v['bili']);
                $bili = str_replace("\n", " ", $bili);
                $bili = str_replace("\r", " ", $bili);
                $bili = str_replace(",", "，", $bili);

                $com_tj_tel = trim($v['com_tj_tel']);
                $com_tj_tel = str_replace("\n", " ", $com_tj_tel);
                $com_tj_tel = str_replace("\r", " ", $com_tj_tel);
                $com_tj_tel = str_replace(",", "，", $com_tj_tel);

                $fc_type = trim($v['fc_type']);
                $fc_type = str_replace(",", "，", $fc_type);

                $desc = $desc . $softname . ',' . $v['package'] . ',' . $v['size'] . ',' . $v['sdk'] . ',' . $v['fuzeren'] . ',' . $v['p_fenlei']
                        . ',' . $v['p_leixing'] . ',' . date('Y-m-d H:i:s', $v['contractime']) . ',' . date('Y-m-d H:i:s', $v['contractendtime'])
                        . ',' . $bili . ',' . $v['companyname'] . ',' . $com_tj_tel . ',' . $v['hztype'] . ',' . $fc_type . ',' . $v['reviewlevel']
                        . ',' . date('Y-m-d H:i:s', $v['createtime']) . ',' . date('Y-m-d H:i:s', $v['onlinetime']) . ',' . $beizhu . "\r";
            }
            $desc = mb_convert_encoding($desc, 'gbk', 'utf8');
            echo $desc;
            exit(0);
        }

        if ($_POST) {
            $this->redirect('/Product/online?' . $sqlparam);
        }
        $this->assign("page", $show);
        $this->assign("list", $list);
        $this->assign("sqlparam", $sqlparam);

        $this->display('online');
    }
    //拼接软件包的下载地址
    public function pub_open_url(){
        echo 'http://m.anzhi.com/download.php?package='.$_GET['package'];
    }
    // 编辑上线产品
    function editonline() {
        $model = D('sendNum.Product');
		$soft_id = $_REQUEST['soft_id'];
        $list = $model->getallproductbyid($soft_id);
		$whitelist_info = $this->get_softwhitelist_info($list['package']);
		$sdk_info = getAppKey($list['package'],1);
        //读取数据
        if ($_POST) {
            // 去掉_POST每个参数两边的空格
            foreach ($_POST as $key => $value) {
                $_POST[$key] = trim($_POST[$key]);
            }
            $this->publication_record('record_url','del_ba','备案','record_');
            $this->publication_record('publication_url','del_cb','出版','publication_');	
            $this->up_auth_file();	
            // 获得编辑信息，以方便写数据库
            $where_arr = array('soft_id' => $_POST['soft_id']);
            $table_name = 'yx_product';
            $column_arr = array(
                'softname' => $_POST['softname'],
                'nature' => $_POST['nature'],
                'companyname' => $_POST['companyname'],
                'package' => $_POST['package'],
                'size' => $_POST['size'],
                'com_tj_tel' => $_POST['com_tj_tel'],
                'hztype' => $_POST['hztype'],
                'jianjie' => $_POST['jianjie'],
                'beizhu' => $_POST['beizhu'],
                'reviewlevel' => $_POST['reviewlevel'],
                'jierujindu' => $_POST['jierujindu'],
                'jierutime' => strtotime($_POST['jierutime']),
                'sdk' => $_POST['sdk'],
                'fuzeren' => $_POST['fuzeren'],
                'fc_type' => $_POST['fc_type'],
				'p_leixing' => $_POST['p_leixing'],
                //'contractime' => strtotime($_POST['contractime']),
                //'contractendtime' => strtotime($_POST['contractendtime']),
                'bili' => $_POST['bili'],
                'record_num' => $_POST['record_num'],
                'publication_num' => $_POST['publication_num'],
                'record_url' => $_POST['record_url'],
                'publication_url' => $_POST['publication_url'],
                'dev_auth_url' => $_POST['dev_auth_url'],
                'coop_auth_url' => $_POST['coop_auth_url'],
                'soft_auth_url' => $_POST['soft_auth_url'],
                'ip_auth_url' => $_POST['ip_auth_url'],
            );
			$this->relevanceUcenter($_POST,$whitelist_info,$list);
            $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
            $msg = "报表-产品状态列表-编辑上线产品，修改了产品,soft_id为{$_POST['soft_id']}的记录：";
            $msg .= $log_all_need;
            // 写库
            $rs = $model->editonline($_POST);
            //file_put_contents("222.txt", var_export($rs, true));
            if ($rs === -1) {
                //$this->assign("jumpUrl","/index.php/Sendnum/Product/online");
                $this->error('负责人用户名不存在');
            }
            if ($rs === false) {
                //$this->assign("jumpUrl","/index.php/Sendnum/Product/online");
                $this->error('修改失败');
            } else if ($rs >= 0) {
                if ($rs > 0) {
                    $this->writelog($msg, 'yx_product',$_POST['soft_id'],__ACTION__ ,'','edit');
                }
				if((!empty($_POST['pay_url'])&&$sdk_info['pay_url']!=$_POST['pay_url']) || (!empty($_POST['usernotice_url'])&&$sdk_info['usernotice_url']!=$_POST['usernotice_url'])){
					$update_sdk_info = array('appKey'=>$sdk_info['app_id'],'pay_url'=>$_POST['pay_url'],'usernotice_url'=>$_POST['usernotice_url']);
					modifyAppNew($update_sdk_info);	
					unset($update_sdk_info['appKey']);
					$model->table('sj_sdk_info')->where("package='".$list['package']."'")->save($update_sdk_info);		
					//echo $model->getLastSql();exit;		
				}
				
                $this->assign("jumpUrl", "/index.php/Sendnum/Product/online");
                $this->success('修改成功');
            }
        }
		$this->assign('one_category',$this->one_category);
        $this->assign('whitelist_info', $whitelist_info);		
        $this->assign('list', $list);
		$this->assign('app_info', $sdk_info);
        $this->assign('soft_id', $soft_id);
        $this->display('editonline');
    }

    // 批量修改负责人
    function modify_fuzeren() {
        if ($_POST) {
            $model = D('sendNum.Product');
            // 检查负责人是否存在
            $exist = $model->is_exsit_admin(trim($_POST['batch_fuzeren']));
            if (!$exist) {
                $this->error('负责人用户名不存在');
            }
            $ids_arr = explode(',', $_POST['ids']);
            foreach ($ids_arr as $id) {
                $model->modify_fuzeren(trim($id), trim($_POST['batch_fuzeren']));
                $this->writelog("报表_上线产品:修改了ID为{$id}的负责人", 'yx_product',$id,__ACTION__ ,'','edit');
            }

            $this->success("修改负责人成功！");
        }
        $this->assign('ready', $_GET['ready']);
        $this->display('modify_fuzeren');
    }

    function mygetcsv(& $handle, $length = null, $d = ',', $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
            if ($itemcnt % 2 == 0)
                $eof = true;
        }
        $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
        $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
            $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }

    function check_softname() {
        if ($_POST) {
            $model = D('sendNum.Product');
            if ($_POST['softname']) {
                if ($_POST['softname'] == $_POST['softname_before'])
                    $this->ajaxReturn(0, '', 0);
                $map = array();
                $map['softname'] = array("EQ", $_POST['softname']);
                $exsit = $model->where($map)->select();
                if ($exsit) {
                    $this->ajaxReturn(0, '产品名称已存在', -1);
                }
                $this->ajaxReturn(0, '产品名称可用', 0);
            }
        }
    }

    function check_fuzeren() {
        if ($_POST) {
            if ($_POST['fuzeren']) {
                $map = array();
                $map['admin_user_name'] = array("EQ", $_POST['fuzeren']);
                $model = M();
                $exsit = $model->table('sj_admin_users')->where($map)->select();
                if (!$exsit) {
                    $this->ajaxReturn(0, '用户名不存在', -1);
                }
                $this->ajaxReturn(0, '用户名存在', 0);
            }
        }
    }

    function test_num_config() {
        $config_model = M();
        if ($_POST) {
            $data = array(
                'configcontent' => trim($_POST['test_num']),
            );
            $log = $this -> logcheck(array("config_type"=>'yx_baobiao','configname'=>'test_num','status'=>1),'pu_config',$data,$config_model);
            $ret = $config_model->table('pu_config')->where("config_type='yx_baobiao' and configname='test_num' and status=1")->save($data);
            $this->writelog("报表_评测产品:评测人数配置，{$log}", 'pu_config',"config_type:yx_baobiao",__ACTION__ ,'','edit');
            $this->success('编辑成功！');
        } else {
            // 查找pu_config表看设置了几个人
            $find = $config_model->table('pu_config')->where("config_type='yx_baobiao' and configname='test_num' and status=1")->find();
            $test_num = $find['configcontent'];
            $this->assign('test_num', $test_num);
            $this->display('test_num_config');
        }
    }
	
	//接口获取联运软件分类
	function pub_get_category(){
		$dicType = $_POST['p_cate'];
		if(!empty($dicType)){
			$app = C('app_key');
			$url = $app['category_host'].$app['category_url'];
			$cate = json_encode(array("dicType"=>$dicType));
			$get_data = $url."?serviceId={$app['serviceId']}&data=".$cate;
			//var_dump($get_data);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $get_data);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($curl);
			curl_close($curl);
			if($result){
				$result = json_decode($result,true);
				//var_dump($result);
				foreach($result['ret'] as $key=>$val){
					$category[] = array(
						'id'=>$key,
						'name'=>$val
					);
				}
				if($result['code']!="success"){
					echo json_encode(array('code'=>'0','msg'=>'未能成功获取分类'));
				}else{
					echo json_encode(array('code'=>'1','category'=>$category));
				}
				
			}else{
				echo json_encode(array('code'=>'0','msg'=>'未能成功获取分类'));
			}
		}
	}
    //批量导出icon
    function downicon(){
        $soft_ids=explode(',',$_GET['soft_ids']);
        $model=D();
        $where['softid']=array('in',$soft_ids);
        $soft_informations=$model->table('sj_soft')->field('softid,softname,package,intro')->where($where)->select();
        $where['status']=1;       
        $icon_informations=$model->table('sj_icon')->field('softid,iconurl_512,apk_icon')->where($where)->select();
        $thumb_informations=$model->table('sj_soft_thumb')->field('softid,image_raw')->where($where)->select();
        foreach($soft_informations as $key=>$soft_information){
            foreach($icon_informations as $icon_information){
                if($soft_information['softid']==$icon_information['softid']){
                    if($icon_information['iconurl_512']){
                        $soft_informations[$key]['image']['iconurl_512']=$icon_information['iconurl_512'];
                    }else{
                        $soft_informations[$key]['image']['apk_icon']=$icon_information['apk_icon'];
                    }
                }
            }
            foreach($thumb_informations as $thumb_information){
                if($soft_information['softid']==$thumb_information['softid']){
                    $soft_informations[$key]['image'][]=$thumb_information['image_raw'];
                }
            }
        }
        $path="/tmp/icon_csv_".date('Y-m-d');
        if(!is_dir($path)){
            if(!mkdir(iconv("UTF-8", "GBK", $path),0777,true)){
                $this->error('创建目录失败');
            }
        }
        $this->downloads($path,$soft_informations);
    }
    //批量导出无角标icon
    function downloads($file_dir,$soft_informations){
        foreach($soft_informations as $v){
            $file_package_dir=$file_dir."/".$v['package']."/";
            if(!is_dir($file_package_dir)){
                if(!mkdir(iconv("UTF-8", "GBK", $file_package_dir),0777,true)){
                    $this->error('创建目录失败');
                }
            }
            $file=$file_package_dir.$v['package'].".csv";
            if(!file_exists($file)){
                $file = fopen($file, 'a');
                fwrite($file,chr(0xEF).chr(0xBB).chr(0xBF)); 
                $heade = array('软件名称','软件包名','软件简介','软件无角标icon地址');
                fputcsv($file, $heade);
                $put_arr = array();
                $put_arr['softname'] = $v['softname'] ? $v['softname'] : "\t";
                $put_arr['package'] = $v['package'] ? $v['package'] : "\t";
                $put_arr['intro'] = $v['intro'] ? $v['intro'] : "\t";
                $put_arr['iconurl_512'] = IMGATT_HOST.($v['image']['iconurl_512'] ? $v['image']['iconurl_512'] : $v['image']['apk_icon']);
                fputcsv($file, $put_arr);
            }
            foreach($v['image'] as $kk => $vv){
                if($kk==='iconurl_512'){
                    copy("/data/att/m.goapk.com".$vv,$file_package_dir.$v['package'].'_无角标.png');
                }else if($kk==='apk_icon'){
                    copy("/data/att/m.goapk.com".$vv,$file_package_dir.$v['package'].'_有角标.png');
                }else{
                    $arr=explode('/',$vv);
                    copy("/data/att/m.goapk.com".$vv,$file_package_dir.$arr[count($arr)-1]);
                }
            }
        }
        $zip_dir='/tmp/zip_icon_'.date('Y-m-d-H-i-s');
        if(!is_dir($zip_dir)){
            if(!mkdir(iconv("UTF-8", "GBK", $zip_dir),0777,true)){
                $this->error('创建目录失败');
            }
        }
        $zipname=$zip_dir."/产品基本信息".date('Y-m-d').'.zip';        
        $cmd = "cd ".$file_dir."; zip -r ".$zipname." ./*";
        shell_exec($cmd);
        $filename=$this->path_info($zipname); 
        $file = fopen($zipname,"r"); 
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($zipname));
        header("Content-Disposition: attachment; filename=".$filename['basename']);
        echo fread($file, filesize($zipname));
        fclose($file);
        $cmd="rm -rf ".$file_dir;
        shell_exec($cmd);
        $cmd="rm -rf ".$zip_dir;
        shell_exec($cmd);
    }

    function path_info($filepath){  
        $path_parts = array();  
        $path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')),"/")."/";  
        $path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')),"/");  
        $path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);  
        $path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')),"/");  
        return $path_parts;  
    } 
}

?>
