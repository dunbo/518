<?php

/**
 * 安智网产品管理平台 开发者管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * By:金山 2010.5.19
 * ----------------------------------------------------------------------------
 */
date_default_timezone_set('Asia/Shanghai');

class UserAction extends CommonAction {

    private $uid;           //用户id;传值用
    private $sid;           //用户id;
    private $users_db;      //用户表
    private $userslist;     //用户列表
    private $conf_db;       //配置表
    private $configlist;    //配置列表
    private $hashs;         //默认hashs
    private $map;           //条件
    private $errarr;        //错误数组

    public function index() {
        //$this->display();
    }

    //开发者管理_待审核开发者列表
    public function auditforuser() {

        //获取驳回原因列表
        $model = new Model();
        if ($_REQUEST['p'])
            $this->assign('p', $_REQUEST['p']);
        else
            $this->assign('p', 1);

        if ($_REQUEST['lr'])
            $this->assign('lr', $_REQUEST['lr']);
        else
            $this->assign('lr', 10);

        require_once(realpath(dirname(__FILE__) . '/../../../../') . '/GoPHP/config/config.inc.php');

        $list_type = isset($_GET['list_type']) && in_array($_GET['list_type'], array('audit', 'reject')) ? $_GET['list_type'] : 'audit';
        if ($list_type == 'audit') { //待审核
            $status = 1;
        } else if ($list_type == 'reject') { //驳回
            $status = -1;
        } else {
            $status = 1;
        }

        if (empty($_GET['p'])) {
            $this->map = "p.status='{$status}' ";
            if ($status == 1) { //email验证后才进入待审核
                $this->map.=' and p.email_verified=1';
            }
            if (!empty($_GET['username']) && $_GET['user_type'] == 1) {
                //获取userid
                $userid = $model->table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
                $this->map.=" and p.dev_id='{$userid}'";
            } else if (!empty($_GET['username']) && $_GET['user_type'] == 2) {
                $userinfo = $this->get_UserInfoByAccount($_GET['username'],'account');
                $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                $this->assign('user_type', $_GET['user_type']);
            }  else {

                if (!empty($_GET['dev_id'])) {
                    $this->map.=" and p.dev_id='{$_GET['dev_id']}'";
                }
                if (!empty($_GET['truename'])) {
                    $this->map.=" and p.truename like '%{$_GET['truename']}%'";
                }
                if (isset($_GET['type']) && $_GET['type'] != -1) {
                    $this->map.=" and p.type='{$_GET['type']}'";
                }
                if (!empty($_GET['mobile'])&&empty($_GET['username'])&&empty($_GET['email'])) {
                    $mobile = $_GET['mobile'];
                    $this->assign("mobile", $mobile);
                    $_SESSION['dev_person']['mobile'] = $mobile;
                    //$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
                    $userinfo = $this->get_UserInfoByAccount($_GET['mobile'],'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (!empty($_GET['dev_name'])) {
                    $this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
                }
                if (!empty($_GET['cardnumber'])) {
                    $this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
                }
                if (!empty($_GET['company'])) {
                    $this->map.=" and p.company like '%{$_GET['company']}%'";
                }
                if (!empty($_GET['charter'])) {
                    $this->map.=" and p.charter like '%{$_GET['charter']}%'";
                }
                if (!empty($_GET['location'])) {
                    $this->map.=" and p.location='{$_GET['location']}'";
                }
                 if (!empty($_GET['email'])&&empty($_GET['username'])) {
                    $email = $_GET['email'];
                    $this->assign("email", $email);
                    $_SESSION['dev_person']['email'] = $email;
                    //$this->map.=" and p.email='{$_GET['email']}'";
                    $userinfo = $this->get_UserInfoByAccount($email,'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (!empty($_GET['begintime'])) {
                    $begintime = strtotime($_GET['begintime']);
                    $this->map.=" and p.register_time>='{$begintime}'";
                }
                if (!empty($_GET['endtime'])) {
                    $endtime = strtotime($_GET['endtime']);
                    $this->map.=" and p.register_time<='{$endtime}'";
                }
            }
            $_SESSION['admin']['auditforuser']['where'] = $this->map;
        } else {
            $this->map = $_SESSION['admin']['auditforuser']['where'];
        }
        if (!empty($_GET['ip'])) {
            $this->map.=" and p.reg_ip='{$_GET['ip']}'";
        }
        $this->map.=" and p.dev_type='0'";
        $this->users_db = D('Sj.Developer');
        import("@.ORG.Page2");
		$bo = true;
        if($userinfo['data']['userList']){
            if(!empty($_GET['mobile'])){
                if($_GET['mobile']!=$userinfo['data']['userList'][0]['telephone']){
                    $bo  = false;
                }
            }
            if(!empty($_GET['email'])){
                if(strtolower($_GET['email'])!=strtolower($userinfo['data']['userList'][0]['email'])){
                    $bo  = false;
                }
            }
        }
        if(!empty($_GET['licence'])){
            if($_GET['licence']==1){
                $this->map.=" and (p.licence_num !='' or p.licence_url != '')";
            }else{
                $this->map.=" and p.licence_num = '' and  p.licence_url = ''";
            }
        }
		if($bo){
			$count = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
			$Page = new Page($count, 10);
			$this->userslist = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.register_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
			//echo $model->getlastsql();
			$devid = array();
			$devid_arr = array();
			if ($this->userslist) {
				foreach ($this->userslist as $key => $val) {
					unset($this->userslist[$key]['email']);
                    unset($this->userslist[$key]['mobile']);
					$devid[] = $val['dev_id']; 
					$devid_arr[] = $val['dev_id'];  //身份证
					$devid_arr[] = 'a' . $val['dev_id']; //营业执照

					// if ($val['email']) {
					// 	$this->userslist[$key]['email_verified_str'] = $val['email_verified'] == 1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
					// }
					// if ($val['mobile']) {
					// 	$this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified'] == 1 ? '<span style="color:red;">[已验证]</span>' : '';
					// }
					if ($val['type'] == 0) {
						$this->userslist[$key]['type_str'] = '公司';
					} else if ($val['type'] == 1) {
						$this->userslist[$key]['type_str'] = '个人';
					} else if ($val['type'] == 2) {
						$this->userslist[$key]['type_str'] = '团队';
					}
					$this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST . $val['cardpic'] : '';
					$this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST . $val['charterpic'] : '';
					$this->userslist[$key]['handcardpic'] = $val['handcardpic'] ? IMGATT_HOST . $val['handcardpic'] : '';
                    $this->userslist[$key]['licence_url'] = $val['licence_url'] ? IMGATT_HOST . $val['licence_url'] : '';
					if ($val['im_type'] == 1) {
						$this->userslist[$key]['im_type_str'] = 'QQ';
					} else if ($val['im_type'] == 2) {
						$this->userslist[$key]['im_type_str'] = 'Gtalk';
					} else if ($val['im_type'] == 3) {
						$this->userslist[$key]['im_type_str'] = 'Msn';
					} else if ($val['im_type'] == 4) {
						$this->userslist[$key]['im_type_str'] = 'Skype';
					}
					//$this->userslist[$key]['ip_num'] = count($ip_num_arr[$val['reg_ip']][0]) ? $ip_num_arr[$val['reg_ip']][0] : 1;
					//$this->userslist[$key]['ip_num'] = count($ip_num_arr[$val['reg_ip']]);
					$this->userslist[$key]['ip_num'] = $model->table('pu_developer')->where("status != 2 and email_verified=1 and reg_ip='{$val['reg_ip']}'")->count();
					$this->userslist[$key]['register_time'] = $val['register_time'] ? date('Y-m-d H:i:s', $val['register_time']) : '';
					$this->userslist[$key]['dismissed_time'] = $val['dismissed_time'] ? date('Y-m-d H:i:s', $val['dismissed_time']) : '';
					if (!empty($val['site'])) {
						if (strstr($val['site'], 'http://')) {
							$this->userslist[$key]['site'] = "<a target='_blank' href='{$val['site']}' >" . $val['site'] . "</a>";
						} else {
							$this->userslist[$key]['site'] = "<a target='_blank' href='http://{$val['site']}' >" . $val['site'] . "</a>";
						}
					}
					if ($val['location'] == 1) {
						$this->userslist[$key]['location_str'] = '中国大陆';
					} else if ($val['location'] == 2) {
						$this->userslist[$key]['location_str'] = '港澳台和国外';
					}
				}
			}
			$this->userslist = $this->get_info($devid, $this->userslist,'1');
			$reason_list = $model->table("dev_reason")->where(array("status" => 1, "reason_type" => 1))->order('pos asc,id desc')->select();
			foreach ($reason_list as $key => $val) {
				if ($val['content2']) {
					$reason_list[$key]['content2'] = explode('<br />', $val['content2']);
				}
			}
			$devid_str = "'" . implode("','", $devid_arr) . "'";
			$Page->setConfig('header', '篇记录');
			$Page->rollPage = 10;
			$Page->setConfig('first', '首页');
			$Page->setConfig('last', '尾页');
			$show = $Page->show();
			$this->assign("page", $show);
			$this->assign("count", $count);
			$this->assign("reason_list", $reason_list);
			$this->assign("list_type", $list_type);
			$this->assign('userslist', $this->userslist);
			$this->assign('devid_str', $devid_str);
			$param = http_build_query($_GET);
			$this->assign('param', $param);
		}
        
        $this->display();
    }

    //开发者管理_待审核开发者_审核
    public function auditforuser_confirm() {
        $denyurl = $_POST['denyurl'];
        //referer定义
        $c = $_GET['key'];
        $referer = '/index.php/' . GROUP_NAME . '/User/auditforuser/list_type/' . $_GET['list_type'];
        if ($_POST['preurl']) {
            $referer = $_POST['preurl'];
        }
        $this->uid = $_GET['uid'];
        $uid_arr = explode(',', $this->uid);
        if ($uid_arr) {
            foreach ($uid_arr as $key => $val) {
                if (!is_numeric($val))
                    unset($uid_arr[$key]);
            }
        }

        if (!$uid_arr) {
            if ($type == -1)
                exit(json_encode(array('code' => 0, 'msg' => '非法操作，请重试')));

            $this->assign('jumpUrl', $referer);
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }

        $type = $_GET['state'];
        $reason = $_POST['reject']; //驳回原因

        if ($type == null) {
            if ($type == -1)
                exit(json_encode(array('code' => 0, 'msg' => '非法操作，请重试(1)')));

            $this->assign('jumpUrl', $referer);
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if ($type == -1 && $reason == '') {
            if ($type == -1)
                exit(json_encode(array('code' => 0, 'msg' => '请选择或填写驳回原因！')));
        }

        $this->users_db = D('Sj.Developer');
        $map['status'] = $type;
        $map['pass_time'] = time();
        $map['last_time'] = time();
        if ($type == -1) {
            $map['dismissed'] = $reason;
            $map['dismissed_time'] = time();
        }

        $where = '';
        if (strpos($this->uid, ',') !== FALSE) {
            $where = "dev_id IN (" . mysql_escape_string($this->uid) . ")";
        } else {
            $where = "dev_id='{$this->uid}'";
        }

        if (false !== $this->users_db->where($where)->save($map)) {
            //var_dump($c ,$type);
            //echo $this->users_db->getlastsql();exit;
            $do_str = '';
            if ($_GET['state'] == 1) {
                $do_str = '撤销';
            } else if ($_GET['state'] == 0) {
                $do_str = '通过';
            } else if ($_GET['state'] == -1) {
                $do_str = '驳回';
                $reason_log = "驳回原因：{$reason}";
            } else {
                $do_str = '审核';
            }
            $emailmodel = D("Dev.Sendemail");
            //发送邮件提醒
            $model = new Model();
            $developer_list = $this->users_db->where($where)->select();
            $tm = date("Y-m-d", time());
            $subject = "【安智提醒】_ 开发者审核通知";
            if ($_GET['state'] == 0) {
                $str = "恭喜您通过开发者资料审核，成功加入安智开发者联盟，赶快登录http://dev.anzhi.com 发布您的软件吧；";
            } else if ($_GET['state'] == -1) {
                $str = "很抱歉，您提交的开发者资料未通过审核，请参考下面的审核意见修改一下吧，安智的小伙伴们期待您的加入；<br/><br/><br/>审核意见：<br/>{$reason} ";
            }
            // $str .= "<br/><br/><br/> 如有问题，请与安智客服联系（http://dev.anzhi.com/contact_us.php）<br/> 安智开发者联盟敬上<br/> http://dev.anzhi.com <br/> 日期：{$tm} <br/>  (系统邮件,请勿回复)";
            $str .= "<br/><br/><br/> 如有问题，请与安智客服联系 QQ：800004609 <br/>（http://dev.anzhi.com/contact_us.php）<br/> 安智开发者联盟敬上<br/> http://dev.anzhi.com <br/> 日期：{$tm} <br/> (系统邮件,请勿回复)<br/><br/>应用CPD/CPT商务合作，请加：<br/>高辉—QQ：1365801369，电话：18610962660<br/>孙健—QQ：455561771， 电话：18243662333<br/><br/>安智商务推广—最丰富的推广形式，让您的应用获得更快更高效的下载效果<br/>合作详情：http://www.anzhi.com/contact.php<br/>联系邮箱：guanggao@anzhi.com<br/>联系电话：010-58858201<br/><br/>安智地推-国内最大的线下流量入口，解决你的推广难题。<br/>详情请见：http://ditui.anzhi.com<br/>联系QQ：2440331513 ";
            foreach ($developer_list as $val) {
                $email_cont = "亲爱的：{$val['dev_name']}<br><br>{$str}";
                if ($_GET['state'] == 0 || $_GET['state'] == -1) {
                    $emailmodel->realsend($val['email'], $val['dev_name'], $subject, $email_cont);
                }
            }

            foreach (explode(",", $this->uid) as $value) {
                if ($value)
                    $this->writelog("{$do_str}了ID为" . $value . "的开发者。" . $reason_log, 'pu_developer',$value, __ACTION__, $_GET['state'],'edit');
            }
            if ($type == -1)
                exit(json_encode(array('code' => 1, 'msg' => $uid_arr)));

            if ($c == 1 && (strpos($this->uid, ',') === FALSE)) {
                $this->ajaxReturn(1, "确认成功", 1);
            } else if ($c == 2 && $type == 0) {
                exit(json_encode(array('code' => 1, 'msg' => $uid_arr)));
            } else {
                //$this->assign('jumpUrl',$denyurl);
                //$this->success("确认成功");
            }
        } else {
            if ($type == -1)
                exit(json_encode(array('code' => 0, 'msg' => '驳回失败，请重试')));

            if ($c == 1 && strpos($this->uid, ',') === FALSE) {
                $this->ajaxReturn(0, "通过失败！", 0);
            } else if ($c == 2 && $type == 0) {
                exit(json_encode(array('code' => 0, 'msg' => "确认失败")));
            } else {
                //$this->assign('jumpUrl',$denyurl);
                //$this->error('确认失败');
            }
        }
    }

    //开发者管理_驳回开发者列表
    public function reject_users() {
        $model = new Model();
        require_once(realpath(dirname(__FILE__) . '/../../../../') . '/GoPHP/config/config.inc.php');
        $list_type = 'reject';
        if (empty($_GET['p'])) {
            $this->map = "(p.status='-1' or p.save_status=1)";
            if (!empty($_GET['username'])&& $_GET['user_type'] == 1) {
                //获取userid
                $userid = $model->table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
                $this->map.=" and p.dev_id='{$userid}'";
            } else if (!empty($_GET['username']) && $_GET['user_type'] == 2) {
                $userinfo = $this->get_UserInfoByAccount($_GET['username'],'account');
                $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                $this->assign('user_type', $_GET['user_type']);
            } else {
                if (!empty($_GET['dev_id'])) {
                    $this->map.=" and p.dev_id='{$_GET['dev_id']}'";
                }

                if (!empty($_GET['truename'])) {
                    $this->map.=" and p.truename like '%{$_GET['truename']}%'";
                }
                if (isset($_GET['type']) && $_GET['type'] != -1) {
                    $this->map.=" and p.type='{$_GET['type']}'";
                }
                
                if (!empty($_GET['dev_name'])) {
                    $this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
                }
                if (!empty($_GET['cardnumber'])) {
                    $this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
                }
                if (!empty($_GET['company'])) {
                    $this->map.=" and p.company like '%{$_GET['company']}%'";
                }
                if (!empty($_GET['charter'])) {
                    $this->map.=" and p.charter like '%{$_GET['charter']}%'";
                }
                if (!empty($_GET['location'])) {
                    $this->map.=" and p.location='{$_GET['location']}'";
                }
                if (!empty($_GET['mobile'])&&empty($_GET['username'])&&empty($_GET['email'])) {
                    //$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
                    $userinfo = $this->get_UserInfoByAccount($_GET['mobile'],'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (!empty($_GET['email'])) {
                    //$this->map.=" and p.email='{$_GET['email']}'";
                    $userinfo = $this->get_UserInfoByAccount($_GET['email'],'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (!empty($_GET['begintime'])) {
                    $begintime = strtotime($_GET['begintime']);
                    $this->map.=" and p.dismissed_time>='{$begintime}'";
                }
                if (!empty($_GET['endtime'])) {
                    $endtime = strtotime($_GET['endtime']);
                    $this->map.=" and p.dismissed_time<='{$endtime}'";
                }
            }
            $_SESSION['admin']['reject_users']['where'] = $this->map;
        } else {
            $this->map = $_SESSION['admin']['reject_users']['where'];
        }
        //dump($this->map);
        if (!empty($_GET['ip'])) {
            $this->map.=" and p.reg_ip='{$_GET['ip']}'";
        }
        $bo = true;
        if($userinfo['data']['userList']){
            if(!empty($_GET['mobile'])){
                if($_GET['mobile']!=$userinfo['data']['userList'][0]['telephone']){
                    $bo  = false;
                }
            }
            if(!empty($_GET['email'])){
                if(strtolower($_GET['email'])!=strtolower($userinfo['data']['userList'][0]['email'])){
                    $bo  = false;
                }
            }
        }
        if(!empty($_GET['licence'])){
            if($_GET['licence']==1){
                $this->map.=" and (p.licence_num !='' or p.licence_url != '')";
            }else{
                $this->map.=" and p.licence_num = '' and  p.licence_url = ''";
            }
        }
        if ($bo) {
            $this->users_db = D('Sj.Developer');
            import("@.ORG.Page2");
            $this->map.=" and p.dev_type='0'";
            $count = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
            $Page = new Page($count, 10);
            $this->userslist = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.dismissed_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            //echo $model->getlastsql();
            $devid_arr = array();
            $devid = array();
            if ($this->userslist) {
                foreach ($this->userslist as $key => $val) {
                    unset($this->userslist[$key]['email']);
                    unset($this->userslist[$key]['mobile']);
                    if ($val['user_name'] != '') {
                        $userinfo = $this->get_UserInfoByAccount($val['email'], 'account');
                        if (!empty($userinfo['data']['userList'][0]['loginName']) && $userinfo['data']['userList'][0]['loginName'] != $val['user_name']) {
                            $this->userslist[$key]['loginName'] = $userinfo['data']['userList'][0]['loginName'];
                        }
                    }
                    $devid[] = $val['dev_id'];
                    $devid_arr[] = $val['dev_id'];  //身份证
                    $devid_arr[] = 'a' . $val['dev_id']; //营业执照
                    $ip = $val['reg_ip'];
                    if ($ip) {
                        $this->userslist[$key]['ip_num'] = $model->table('pu_developer')->where("status = -1 and reg_ip='{$ip}'")->count();
                    }

                    if ($val['type'] == 0) {
                        $this->userslist[$key]['type_str'] = '公司';
                    } else if ($val['type'] == 1) {
                        $this->userslist[$key]['type_str'] = '个人';
                    } else if ($val['type'] == 2) {
                        $this->userslist[$key]['type_str'] = '团队';
                    }
                    $this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST . $val['cardpic'] : '';
                    $this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST . $val['charterpic'] : '';
					$this->userslist[$key]['handcardpic'] = $val['handcardpic'] ? IMGATT_HOST . $val['handcardpic'] : '';
                    $this->userslist[$key]['licence_url'] = $val['licence_url'] ? IMGATT_HOST . $val['licence_url'] : '';
                    if ($val['im_type'] == 1) {
                        $this->userslist[$key]['im_type_str'] = 'QQ';
                    } else if ($val['im_type'] == 2) {
                        $this->userslist[$key]['im_type_str'] = 'Gtalk';
                    } else if ($val['im_type'] == 3) {
                        $this->userslist[$key]['im_type_str'] = 'Msn';
                    } else if ($val['im_type'] == 4) {
                        $this->userslist[$key]['im_type_str'] = 'Skype';
                    }
                    $this->userslist[$key]['register_time'] = $val['register_time'] ? date('Y-m-d H:i:s', $val['register_time']) : '';
                    $this->userslist[$key]['dismissed_time'] = $val['dismissed_time'] ? date('Y-m-d H:i:s', $val['dismissed_time']) : '';
                    if ($val['dismissed']) {
                        $str = str_replace('<br />', '', $val['dismissed']);
                        $str = str_replace('\n', '', $str);
                        $this->userslist[$key]['dismissed2'] = trim($str);
                        $this->userslist[$key]['dismissed2_num'] = mb_strlen(trim($str), 'utf8');
                    }

                    if (!empty($val['site'])) {
                        if (strstr($val['site'], 'http://')) {
                            $this->userslist[$key]['site'] = "<a target='_blank' href='{$val['site']}' >" . $val['site'] . "</a>";
                        } else {
                            $this->userslist[$key]['site'] = "<a target='_blank' href='http://{$val['site']}' >" . $val['site'] . "</a>";
                        }
                    }
                    if ($val['location'] == 1) {
                        $this->userslist[$key]['location_str'] = '中国大陆';
                    } else if ($val['location'] == 2) {
                        $this->userslist[$key]['location_str'] = '港澳台和国外';
                    }
                }
            }
            $this->userslist = $this->get_info($devid, $this->userslist);
            $where = array(
                'dev_id' => array('in', $devid),
                'status' => 3,
            );
            $dev_save = get_table_data($where, "pu_developer_save", "dev_id", "dev_id,dev_name,edit_reject,update_tm");
            $this->assign('dev_save', $dev_save);
            $devid_str = "'" . implode("','", $devid_arr) . "'";
            $Page->setConfig('header', '篇记录');
            $Page->rollPage = 10;
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $show = $Page->show();
            $this->assign("page", $show);
            $this->assign("count", $count);
            $this->assign("list_type", $list_type);
            $this->assign('userslist', $this->userslist);
            $this->assign('devid_str', $devid_str);
            $param = http_build_query($_GET);
            $this->assign('param', $param);
        }
        
        $this->display();
    }

    //开发者管理_驳回开发者列表_删除
    public function reject_users_delete() {
        $preurl = $_POST['preurl'] ? $_POST['preurl'] : '/index.php/' . GROUP_NAME . '/User/reject_users';
        if (!is_numeric($_GET['uid']) && !preg_match('/[0-9,]+/i', $_GET['uid'])) {
            $this->assign('jumpUrl', $preurl);
            $this->error('参数错误！');
        }
        //删除 pu_developer 表中开发者记录
        $where = '';
        if (is_numeric($_GET['uid'])) {
            $where = "dev_id='{$_GET['uid']}'";
        } else {
            $where = "dev_id IN ({$_GET['uid']})";
        }
        $model = new Model();
		$p_where = "parentid in ({$_GET['uid']}) or (dev_id IN ({$_GET['uid']}) AND status in (0,-1,-2))";
		$subQuery = $model->table('pu_developer')->where($p_where)->buildSql();
		$sql_str = "INSERT INTO pu_developer_old {$subQuery}";
		$model -> query($sql_str);
        $rs = $model->query("DELETE FROM pu_developer WHERE {$where} AND status in (0,-1,-2)");
//        $data = array(
//            'dev_name'=>'','truename'=>'','site'=>'','introduction'=>'','zipcode'=>'','address'=>'','phone'=>'','im_id'=>'','im_type'=>'','status'=>'0','mobile'=>'',
//            'mobile_verified'=>'0','mobile_time'=>'','company'=>'','type'=>'','fax'=>'0','cardtype'=>'0','cardnumber'=>'','cardpic'=>'','charter'=>'','charterpic'=>'',
//            'message'=>'','approve'=>'N','old_start'=>'','verify_mobile'=>'','verify_time'=>'','dismissed'=>'','shield_reason'=>'','shield_time'=>'0','location'=>'1','quest_mobile'=>'','quest_descrip'=>'','quest_time'=>'0','complete_time'=>'0',
//            'dismissed_time'=>'0','last_time'=>time(),'is_first_login'=>'0','lastlogin'=>'0','lastlogin2'=>'0','statistics_on'=>'0','parentid'=>'0','dev_type'=>'0','is_comment'=>'0','save_status'=>'0'
//        );
//        $data['truename'] = '';
        $model->table('pu_developer')->where("parentid in ({$_GET['uid']})")->delete();

        $this->writelog("删除ID：{$_GET['uid']}开发者。备注：{$_GET['beizhu']}", 'pu_developer',$_GET['uid'], __ACTION__,'','del');
        $this->assign('jumpUrl', $preurl);
        $this->success("恭喜您，该开发者删除成功！");
    }

    //开发者管理_屏蔽开发者列表
    public function shield_users() {
        $model = new Model();
        if (empty($_GET['p'])) {
            $this->map = 'p.status=-2 ';
            if (!empty($_GET['username'])&& $_GET['user_type'] == 1) {
                //获取userid
                $userid = $model->table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
                $this->map.=" and p.dev_id='{$userid}'";
            } else if (!empty($_GET['username']) && $_GET['user_type'] == 2) {
                $userinfo = $this->get_UserInfoByAccount($_GET['username'],'account');
                $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                $this->assign('user_type', $_GET['user_type']);
            } else {
                if (!empty($_GET['dev_id'])) {
                    $this->map.=" and p.dev_id='{$_GET['dev_id']}'";
                }
                if (!empty($_GET['truename'])) {
                    $this->map.=" and p.truename like '%{$_GET['truename']}%'";
                }
                if (isset($_GET['type']) && $_GET['type'] != -1) {
                    $this->map.=" and p.type='{$_GET['type']}'";
                }
                
                if (!empty($_GET['dev_name'])) {
                    $this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
                }
                if (!empty($_GET['cardnumber'])) {
                    $this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
                }
                if (!empty($_GET['company'])) {
                    $this->map.=" and p.company like '%{$_GET['company']}%'";
                }
                if (!empty($_GET['charter'])) {
                    $this->map.=" and p.charter like '%{$_GET['charter']}%'";
                }
                if (!empty($_GET['location'])) {
                    $this->map.=" and p.location='{$_GET['location']}'";
                }
                if (!empty($_GET['mobile'])&&empty($_GET['username'])&&empty($_GET['email'])) {
                    //$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
                    $userinfo = $this->get_UserInfoByAccount($_GET['mobile'],'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (!empty($_GET['email'])&&empty($_GET['username'])) {
                    //$this->map.=" and p.email='{$_GET['email']}'";
                    $userinfo = $this->get_UserInfoByAccount($_GET['email'],'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (!empty($_GET['begintime'])) {
                    $begintime = strtotime($_GET['begintime']);
                    $this->map.=" and p.register_time>='{$begintime}'";
                }
                if (!empty($_GET['endtime'])) {
                    $endtime = strtotime($_GET['endtime']);
                    $this->map.=" and p.register_time<='{$endtime}'";
                }
                if (!empty($_GET['shield_reason'])) {
                    $this->map.=" and p.shield_reason like '%{$_GET['shield_reason']}%'";
                }
            }
            $_SESSION['admin']['shield_users']['where'] = $this->map;
        } else {
            $this->map = $_SESSION['admin']['shield_users']['where'];
        }
        if (!empty($_GET['ip'])) {
            $this->map.=" and p.reg_ip='{$_GET['ip']}'";
        }
        $bo = true;
        if($userinfo['data']['userList']){
            if(!empty($_GET['mobile'])){
                if($_GET['mobile']!=$userinfo['data']['userList'][0]['telephone']){
                    $bo  = false;
                }
            }
            if(!empty($_GET['email'])){
                if(strtolower($_GET['email'])!=strtolower($userinfo['data']['userList'][0]['email'])){
                    $bo  = false;
                }
            }
        }
        if(!empty($_GET['licence'])){
            if($_GET['licence']==1){
                $this->map.=" and (p.licence_num !='' or p.licence_url != '')";
            }else{
                $this->map.=" and p.licence_num = '' and  p.licence_url = ''";
            }
        }

        if ($bo) {
            $this->map.=" and p.dev_type='0'";
            $this->users_db = D('Sj.Developer');
            import("@.ORG.Page2");
            $count = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
            $Page = new Page($count, 10);
            $this->userslist = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.shield_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            //echo $model->getLastSql();
            $model = new Model();
            foreach ($this->userslist as $indx => $info) {
                $status = $model->table('pu_user')->where('userid = ' . $info['dev_id'])->getField('status');
                $this->userslist[$indx]['deny_status'] = $status;
            }
            $_soft_db = M('soft');
            $_soft_tmp = M('soft_tmp');
            $devid_arr = array();
            if ($this->userslist) {
                foreach ($this->userslist as $key => $val) {
                    //unset($this->userslist[$key]['email']);
                    //unset($this->userslist[$key]['mobile']);
                    $this->userslist[$key]['son_num'] = $this->get_sonuser_num($val['dev_id']);
                    $devid_arr[] = $val['dev_id'];  //身份证
                    $devid_arr[] = 'a' . $val['dev_id']; //营业执照
                    if ($val['type'] == 0) {
                        $this->userslist[$key]['type_str'] = '公司';
                    } else if ($val['type'] == 1) {
                        $this->userslist[$key]['type_str'] = '个人';
                    } else if ($val['type'] == 2) {
                        $this->userslist[$key]['type_str'] = '团队';
                    }
                    $this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST . $val['cardpic'] : '';
                    $this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST . $val['charterpic'] : '';
					$this->userslist[$key]['handcardpic'] = $val['handcardpic'] ? IMGATT_HOST . $val['handcardpic'] : '';
                    $this->userslist[$key]['licence_url'] = $val['licence_url'] ? IMGATT_HOST . $val['licence_url'] : '';
                    if ($val['im_type'] == 1) {
                        $this->userslist[$key]['im_type_str'] = 'QQ';
                    } else if ($val['im_type'] == 2) {
                        $this->userslist[$key]['im_type_str'] = 'Gtalk';
                    } else if ($val['im_type'] == 3) {
                        $this->userslist[$key]['im_type_str'] = 'Msn';
                    } else if ($val['im_type'] == 4) {
                        $this->userslist[$key]['im_type_str'] = 'Skype';
                    }
                    $this->userslist[$key]['register_time'] = $val['register_time'] ? date('Y-m-d H:i:s', $val['register_time']) : '';
                    $this->userslist[$key]['complete_time'] = $val['complete_time'] ? date('Y-m-d H:i:s', $val['complete_time']) : '';
                    $this->userslist[$key]['pass_time'] = $val['pass_time'] ? date('Y-m-d H:i:s', $val['pass_time']) : '';
                    $this->userslist[$key]['shield_time'] = $val['shield_time'] ? date('Y-m-d H:i:s', $val['shield_time']) : '';
                    if (!empty($val['site'])) {
                        if (strstr($val['site'], 'http://')) {
                            $this->userslist[$key]['site'] = "<a target='_blank' href='{$val['site']}' >" . $val['site'] . "</a>";
                        } else {
                            $this->userslist[$key]['site'] = "<a target='_blank' href='http://{$val['site']}' >" . $val['site'] . "</a>";
                        }
                    }
                    //软件统计,开始
                    $num = $_soft_db->where("status=1 and hide=1 and channel_id='' and dev_id='{$val['dev_id']}' and claim_status=2")->count();
                    $this->userslist[$key]['soft_num'] = $num ? $num : 0; //已发布
                    $undercarriage_num = $_soft_db->where("status=1 and hide=3 and dev_id='{$val['dev_id']}'")->count();
                    $this->userslist[$key]['undercarriage_num'] = $undercarriage_num ? $undercarriage_num : 0; //已下架

                    $soft_new = $_soft_tmp->where("status=2 and record_type=1 and dev_id='{$val['dev_id']}'")->count();
                    $this->userslist[$key]['soft_new'] = $soft_new ? $soft_new : 0;    //新软件审核

                    $soft_edit = $_soft_tmp->where("status=2 and record_type=2 and dev_id='{$val['dev_id']}'")->count();
                    $this->userslist[$key]['soft_edit'] = $soft_edit ? $soft_edit : 0;    //编辑审核

                    $soft_update = $_soft_tmp->where("status=2 and record_type=3 and dev_id='{$val['dev_id']}'")->count();
                    $this->userslist[$key]['soft_update'] = $soft_update ? $soft_update : 0;    //升级审核
                    //软件统计,结束

                    if ($val['location'] == 1) {
                        $this->userslist[$key]['location_str'] = '中国大陆';
                    } else if ($val['location'] == 2) {
                        $this->userslist[$key]['location_str'] = '港澳台和国外';
                    }
                }
            }
            $this->userslist = $this->get_info($devid_arr, $this->userslist);
            $devid_str = "'" . implode("','", $devid_arr) . "'";
            $Page->setConfig('header', '篇记录');
            $Page->rollPage = 10;
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $show = $Page->show();
            $this->assign("page", $show);
            $this->assign("count", $count);
            $this->assign('userslist', $this->userslist);
            $this->assign('devid_str', $devid_str);
            $param = http_build_query($_GET);
            $this->assign('param', $param);
        }
        
        $this->display();
    }

    //开发者管理_开发者列表
    public function userlists() {
        $model = new Model();
        if (empty($_GET['p'])) {
            $p = 1;
            unset($_SESSION['dev_person']);
            $this->map = 'p.status=0 ';
            if (!empty($_GET['username']) && $_GET['user_type'] == 1) {
                //获取userid
                $userid = $model->table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
                $this->map.=" and p.dev_id='{$userid}'";
            } else if (!empty($_GET['username']) && $_GET['user_type'] == 2) {
                $userinfo = $this->get_UserInfoByAccount($_GET['username'],'account');
                $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                $this->assign('user_type', $_GET['user_type']);
            } else {
                if (!empty($_GET['dev_id'])) {
                    $dev_id = $_GET['dev_id'];
                    $this->assign("dev_id", $dev_id);
                    $_SESSION['dev_person']['dev_id'] = $dev_id;
                    $this->map.=" and p.dev_id='{$_GET['dev_id']}'";
                }
                if (!empty($_GET['time'])) {
                    $stime = strtotime(date("Y-m-d 0:0:0", $_GET['time']));
                    $etime = $stime + (3600 * 24) - 1;
                    //echo $_GET['time']; 
                    $this->map.=" and p.quest_time between '{$stime}' and '{$etime}'";
                }
                if (!empty($_GET['truename'])) {
                    $truename = $_GET['truename'];
                    $this->assign("truename", $truename);
                    $_SESSION['dev_person']['truename'] = $dev_id;
                    $this->map.=" and p.truename like '%{$_GET['truename']}%'";
                }
                if ($_GET['type'] === "0" || $_GET['type'] === "1") {
                    $type = $_GET['type'];
                    $this->assign("type", $type);
                    $_SESSION['dev_person']['type'] = $type;
                    $this->map.=" and p.type='{$_GET['type']}'";
                } else {
                    $type = 3;
                    $this->assign("type", $type);
                    $_SESSION['dev_person']['type'] = $type;
                }
                if (!empty($_GET['mobile'])&&empty($_GET['username'])&&empty($_GET['email'])) {
                    $mobile = $_GET['mobile'];
                    $this->assign("mobile", $mobile);
                    $_SESSION['dev_person']['mobile'] = $mobile;
                    //$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
                    $userinfo = $this->get_UserInfoByAccount($_GET['mobile'],'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (isset($_GET['mobile_verified'])) {
                    $mobile_verified = $_GET['mobile_verified'];
                    $this->assign("mobile_verified", $mobile_verified);
                    $_SESSION['dev_person']['mobile_verified'] = $mobile_verified;
                    $this->map.=" and p.mobile_verified = '{$_GET['mobile_verified']}' and p.mobile != ''";
                }
                if (isset($_GET['statistics_on'])) {
                    $statistics_on = $_GET['statistics_on'];
                    $this->assign("statistics_on", $statistics_on);
                    $_SESSION['dev_person']['statistics_on'] = $statistics_on;
                    if ($statistics_on == 1) {
                        $this->map.=" and p.statistics_on = '0'";
                    } else {
                        $this->map.=" and p.statistics_on > '0'";
                    }
                }
                if (!empty($_GET['dev_name'])) {
                    $dev_name = $_GET['dev_name'];
                    $this->assign("dev_name", $dev_name);
                    $_SESSION['dev_person']['dev_name'] = $dev_name;
                    $this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
                }
                if (!empty($_GET['cardnumber'])) {
                    $cardnumber = $_GET['cardnumber'];
                    $this->assign("cardnumber", $cardnumber);
                    $_SESSION['dev_person']['cardnumber'] = $cardnumber;
                    $this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
                }
                if (!empty($_GET['company'])) {
                    $this->map.=" and p.company like '%{$_GET['company']}%'";
                }
                if (!empty($_GET['charter'])) {
                    $charter = $_GET['charter'];
                    $this->assign("charter", $charter);
                    $_SESSION['dev_person']['charter'] = $charter;
                    $this->map.=" and p.charter like '%{$_GET['charter']}%'";
                }
                if (!empty($_GET['location'])) {
                    $location = $_GET['location'];
                    $this->assign("location", $location);
                    $_SESSION['dev_person']['location'] = $location;
                    $this->map.=" and p.location='{$_GET['location']}'";
                }
                if (!empty($_GET['email'])&&empty($_GET['username'])) {
                    $email = $_GET['email'];
                    $this->assign("email", $email);
                    $_SESSION['dev_person']['email'] = $email;
                    //$this->map.=" and p.email='{$_GET['email']}'";
                    $userinfo = $this->get_UserInfoByAccount($email,'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                }
                if (isset($_GET['is_comment'])) {
                    $is_comment = $_GET['is_comment'];
                    $this->assign("is_comment", $is_comment);
                    $_SESSION['dev_person']['is_comment'] = $is_comment;
                    $this->map.=" and p.is_comment='{$_GET['is_comment']}'";
                }
                if (!empty($_GET['begintime'])) {
                    $begintime = strtotime($_GET['begintime']);
                    $this->assign("begintime", $begintime);
                    $_SESSION['dev_person']['begintime'] = $begintime;
                    $this->map.=" and p.register_time>='{$begintime}'";
                }
                if (!empty($_GET['endtime'])) {
                    $endtime = strtotime($_GET['endtime']);
                    $this->assign("endtime", $endtime);
                    $_SESSION['dev_person']['endtime'] = $endtime;
                    $this->map.=" and p.register_time<='{$endtime}'";
                }
            }
            $_SESSION['admin']['userlists']['where'] = $this->map;
            if (!empty($_GET['ip'])) {
                $this->map.=" and p.reg_ip='{$_GET['ip']}'";
            }
        } else {
            if ($_GET['dev_id']) {
                $dev_id = $_GET['dev_id'];
                $_SESSION['dev_person']['dev_id'] = $dev_id;
            } else {
                $dev_id = $_SESSION['dev_person']['dev_id'];
            }
            $this->assign("dev_id", $dev_id);
            if ($_GET['truename']) {
                $truename = $_GET['truename'];
                $_SESSION['dev_person']['truename'] = $truename;
            } else {
                $truename = $_SESSION['dev_person']['truename'];
            }
            $this->assign("truename", $truename);
            if ($_GET['type'] === "0" || $_GET['type'] === "1" || $_GET['type'] === "3") {
                $type = $_GET['type'];
                $_SESSION['dev_person']['type'] = $type;
            } else {
                $type = $_SESSION['dev_person']['type'];
            }
            $this->assign("type", $type);
            if ($_GET['mobile']) {
                $mobile = $_GET['mobile'];
                $_SESSION['dev_person']['mobile'] = $mobile;
            } else {
                $mobile = $_SESSION['dev_person']['mobile'];
            }
            $this->assign("mobile", $mobile);
            if ($_GET['mobile_verified']) {
                $mobile_verified = $_GET['mobile_verified'];
                $_SESSION['dev_person']['mobile_verified'] = $mobile_verified;
            } else {
                $mobile_verified = $_SESSION['dev_person']['mobile_verified'];
            }
            $this->assign("mobile_verified", $mobile_verified);
            if ($_GET['statistics_on']) {
                $statistics_on = $_GET['statistics_on'];
                $_SESSION['dev_person']['statistics_on'] = $statistics_on;
            } else {
                $statistics_on = $_SESSION['dev_person']['statistics_on'];
            }
            $this->assign("statistics_on", $statistics_on);
            if ($_GET['dev_name']) {
                $dev_name = $_GET['dev_name'];
                $_SESSION['dev_person']['dev_name'] = $dev_name;
            } else {
                $dev_name = $_SESSION['dev_person']['dev_name'];
            }
            $this->assign("dev_name", $dev_name);
            if ($_GET['cardnumber']) {
                $cardnumber = $_GET['cardnumber'];
                $_SESSION['dev_person']['cardnumber'] = $cardnumber;
            } else {
                $cardnumber = $_SESSION['dev_person']['cardnumber'];
            }
            $this->assign("cardnumber", $cardnumber);
            if (!empty($_GET['company'])) {
                $company = $_GET['company'];
                $_SESSION['dev_person']['company'] = $company;
            } else {
                $company = $_SESSION['dev_person']['company'];
            }
            $this->assign("company", $company);
            if ($_GET['charter']) {
                $charter = $_GET['charter'];
                $_SESSION['dev_person']['charter'] = $charter;
            } else {
                $charter = $_SESSION['dev_person']['charter'];
            }
            $this->assign("charter", $charter);
            if ($_GET['charter']) {
                $location = $_SESSION['dev_person']['location'];
            } else {
                $location = $_SESSION['dev_person']['location'];
            }
            $this->assign("location", $location);
            if ($_GET['email']) {
                $email = $_GET['email'];
                $_SESSION['dev_person']['email'] = $email;
            } else {
                $email = $_SESSION['dev_person']['email'];
            }
            $this->assign("email", $email);
            if ($_GET['begintime']) {
                $begintime = $_GET['begintime'];
                $_SESSION['dev_person']['begintime'] = $begintime;
            } else {
                $begintime = $_SESSION['dev_person']['begintime'];
            }
            if (isset($_GET['is_comment'])) {
                $is_comment = $_GET['is_comment'];
                $this->assign("is_comment", $is_comment);
                $_SESSION['dev_person']['is_comment'] = $is_comment;
                $this->map.=" and p.is_comment='{$_GET['is_comment']}'";
            }
            $this->assign("begintime", $begintime);
            if ($_GET['endtime']) {
                $endtime = $_GET['endtime'];
                $_SESSION['dev_person']['endtime'] = $endtime;
            } else {
                $endtime = $_SESSION['dev_person']['endtime'];
            }
            $this->assign("endtime", $endtime);
            $p = $_GET['p'];
            $this->map = $_SESSION['admin']['userlists']['where'];
            if (!empty($_GET['ip'])) {
                $this->map.=" and p.reg_ip='{$_GET['ip']}'";
            }
        }
        //dump($this->map);
        //排序
        if ($_GET['orderby'] == 'download') {
            if ($_GET['order'] == 'd') {
                $order = 'p.statistics_on desc';
            } elseif ($_GET['order'] == 'a') {
                $order = 'p.statistics_on asc';
            } elseif ($_GET['order'] == '') {
                $order = 'p.statistics_on desc';
            }
            $this->assign('order', $_GET['order']);
        }
        if ($_GET['orderby'] == 'time') {
            if ($_GET['order'] == 'd') {
                $order = 'p.register_time desc';
            } elseif ($_GET['order'] == 'a') {
                $order = 'p.register_time asc';
            } elseif ($_GET['order'] == '') {
                $order = 'p.register_time desc';
            }
            $this->assign('order1', $_GET['order']);
        }
        $this->assign('orderby', $_GET['orderby']);
        unset($_GET['order']);
        if (empty($_GET['orderby'])) {
            $order = 'p.register_time desc';
        }

        $this->users_db = D('Sj.Developer');
        import("@.ORG.Page2");
        $this->map .=" and p.dev_type='0'";
        $bo = true;
        if($userinfo['data']['userList']){
            if(!empty($_GET['mobile'])){
                if($_GET['mobile']!=$userinfo['data']['userList'][0]['telephone']){
                    $bo  = false;
                }
            }
            if(!empty($_GET['email'])){
                if(strtolower($_GET['email'])!=strtolower($userinfo['data']['userList'][0]['email'])){
                    $bo  = false;
                }
            }
        }
        if(!empty($_GET['licence'])){
            if($_GET['licence']==1){
                $this->map.=" and (p.licence_num !='' or p.licence_url != '')";
            }else{
                $this->map.=" and p.licence_num = '' and  p.licence_url = ''";
            }
        }
        if(isset($_GET['publication_status'])){
            $this->map .= " and p.publication_status = {$_GET['publication_status']}";
            $this->assign('publication_status',$_GET['publication_status']);
        }
        if ($bo) {
		    $this->map.=" and p.dev_type='0'";
            $count = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
            $Page = new Page($count, 10);
            $this->userslist = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
            //echo $model->getLastSql();
            foreach ($this->userslist as $indx => $info) {
                unset($this->userslist[$indx]['email']);
                unset($this->userslist[$indx]['mobile']);
                $status = $model->table('pu_user')->where('userid = ' . $info['dev_id'])->getField('status');
                $this->userslist[$indx]['deny_status'] = $status;
            }
            $_soft_db = M('soft');
            $_soft_tmp = M('soft_tmp');
            $devid_arr = array();
            if ($this->userslist) {
                foreach ($this->userslist as $key => $val) {
                    $this->userslist[$key]['son_num'] = $this->get_sonuser_num($val['dev_id']);
                    $devid_arr[] = $val['dev_id'];  //身份证
                    // $devid_arr[] = 'a' . $val['dev_id']; //营业执照
                    $ip = $val['reg_ip'];
                    if ($ip) {
                        $this->userslist[$key]['ip_num'] = $model->table('pu_developer')->where("status = 0 and reg_ip='{$ip}'")->count();
                    }
                    if ($val['type'] == 0) {
                        $this->userslist[$key]['type_str'] = '公司';
                    } else if ($val['type'] == 1) {
                        $this->userslist[$key]['type_str'] = '个人';
                    } else if ($val['type'] == 2) {
                        $this->userslist[$key]['type_str'] = '团队';
                    }
                    $this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST . $val['cardpic'] : '';
                    $this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST . $val['charterpic'] : '';
					$this->userslist[$key]['handcardpic'] = $val['handcardpic'] ? IMGATT_HOST . $val['handcardpic'] : '';
                    $this->userslist[$key]['licence_url'] = $val['licence_url'] ? IMGATT_HOST . $val['licence_url'] : '';
                    if ($val['im_type'] == 1) {
                        $this->userslist[$key]['im_type_str'] = 'QQ';
                    } else if ($val['im_type'] == 2) {
                        $this->userslist[$key]['im_type_str'] = 'Gtalk';
                    } else if ($val['im_type'] == 3) {
                        $this->userslist[$key]['im_type_str'] = 'Msn';
                    } else if ($val['im_type'] == 4) {
                        $this->userslist[$key]['im_type_str'] = 'Skype';
                    }
                    $this->userslist[$key]['register_time'] = $val['register_time'] ? date('Y-m-d H:i:s', $val['register_time']) : '';
                    $this->userslist[$key]['pass_time'] = $val['pass_time'] ? date('Y-m-d H:i:s', $val['pass_time']) : '';
                    $this->userslist[$key]['last_time'] = $val['last_time'] ? date('Y-m-d H:i:s', $val['last_time']) : '';
                    if (!empty($val['site'])) {
                        if (strstr($val['site'], 'http://')) {
                            $this->userslist[$key]['site'] = "<a target='_blank' href='{$val['site']}' >" . $val['site'] . "</a>";
                        } else {
                            $this->userslist[$key]['site'] = "<a target='_blank' href='http://{$val['site']}' >" . $val['site'] . "</a>";
                        }
                    }
                    if ($val['location'] == 1) {
                        $this->userslist[$key]['location_str'] = '中国大陆';
                    } else if ($val['location'] == 2) {
                        $this->userslist[$key]['location_str'] = '港澳台和国外';
                    }

                    //软件统计,开始
                    $num = $_soft_db->where("status=1 and hide=1 and channel_id='' and dev_id='{$val['dev_id']}' and claim_status=2")->count();
                    $this->userslist[$key]['soft_num'] = $num ? $num : 0; //已发布	

                    $undercarriage_num = $_soft_db->where("status=1 and hide=3 and dev_id='{$val['dev_id']}'and claim_status=2")->count();
                    $this->userslist[$key]['undercarriage_num'] = $undercarriage_num ? $undercarriage_num : 0; //已下架

                    $soft_new = $_soft_tmp->where("status=2 and record_type=1 and dev_id='{$val['dev_id']}'")->count();
                    $this->userslist[$key]['soft_new'] = $soft_new ? $soft_new : 0;    //新软件审核

                    $soft_edit = $_soft_tmp->where("status=2 and record_type=2 and dev_id='{$val['dev_id']}'")->count();
                    $this->userslist[$key]['soft_edit'] = $soft_edit ? $soft_edit : 0;    //编辑审核

                    $soft_update = $_soft_tmp->where("status=2 and record_type=3 and dev_id='{$val['dev_id']}'")->count();
                    $this->userslist[$key]['soft_update'] = $soft_update ? $soft_update : 0;    //升级审核

                    /* 	$soft_edit2 = $model->table('sj_soft_temporary')->where("status=1 and hide=9 and dev_id='{$val['dev_id']}'")->count();
                      $this->userslist[$key]['soft_edit2'] = $soft_edit2 ? $soft_edit2 : 0;	//上线软件重新编辑审核

                      $this->userslist[$key]['soft_edit'] += $this->userslist[$key]['soft_edit2']; */
                    //软件统计,结束
                }
            }
            $this->userslist = $this->get_info($devid_arr, $this->userslist,1);
            $where = array(
                'dev_id' => array('in', $devid_arr),
                'status' => 1,
            );
            $dev_save = get_table_data($where, "pu_developer_save", "dev_id", "dev_id,dev_name");
            $this->assign('dev_save', $dev_save);
            $devid_str = "'" . implode("','", $devid_arr) . "'";

            $Page->rollPage = 10;
            $Page->setConfig('header', '篇记录');
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $show = $Page->show();
            $this->assign("page", $show);
            $this->assign("count", $count);
            $this->assign("p", $p);
            $this->assign('userslist', $this->userslist);
            $this->assign('referer', $_SERVER['PHP_SELF']);
            $this->assign('devid_str', $devid_str);
            $param = http_build_query($_GET);
            $this->assign('param', $param);
        }
        
        $this->display();
    }

    //获取之账号个数
    public function get_sonuser_num($parent_id) {
        $model = D('Sj.Developer');
        $num = $model->table('pu_developer')->where(array('parentid' => $parent_id, 'status' => 0))->count();
        return $num;
    }

    //子账号列表
    public function show_sonuser() {
        $model = D('Sj.Developer');
        $dev_id = $_GET['dev_id'];
        $dev_name = $model->table('pu_developer')->where(array('dev_id' => $dev_id))->field('dev_name')->find();
        $list = $model->table('pu_developer a')->join('pu_sonuser_point b on a.dev_id = b.user_id')->where(array('a.parentid' => $dev_id, 'status' => 0))->field('a.dev_id,a.dev_name,a.email,a.mobile,a.register_time,b.*')->order('register_time desc')->select();
        $son_authority = C('son_authority');
        if ($list) {
            //
            foreach ($list as $key => $val) {
                $soft = $finance = $user_data = $info = '';
                foreach ($son_authority['soft'] as $soft_key => $soft_val) {
                    if ($list[$key][$soft_key] == 1) {
                        //软件管理
                        $list[$key]['soft'] = 1;
                        $soft .= $soft_val . ',';
                    }
                }
                $list[$key]['softval'] = substr($soft, 0, -1);

                foreach ($son_authority['finance'] as $finance_key => $finance_val) {
                    if ($val[$finance_key] == 1) {
                        //财务管理
                        $list[$key]['finance'] = 1;
                        $finance .= $finance_val . ',';
                    }
                }
                $list[$key]['financeval'] = substr($finance, 0, -1);

                foreach ($son_authority['user_data'] as $data_key => $data_val) {
                    if ($val[$data_key] == 1) {
                        //用户数据
                        $list[$key]['user_data'] = 1;
                        $user_data .= $data_val . ',';
                    }
                }
                $list[$key]['user_dataval'] = substr($user_data, 0, -1);


                foreach ($son_authority['info_manage'] as $info_key => $info_val) {
                    if ($val[$info_key] == 1) {
                        //信息管理
                        $list[$key]['info_manage'] = 1;
                        $info .= $info_val . ',';
                    }
                }
                $list[$key]['infoval'] = substr($info, 0, -1);
            }
        }
//        if ($soft) {
//            $soft = substr($soft, 0, -1);
//            $this->assign('soft', $soft);
//        }
//        if ($finance) {
//            $finance = substr($finance, 0, -1);
//            $this->assign('finance', $finance);
//        }
//        if ($user_data) {
//            $user_data = substr($user_data, 0, -1);
//            $this->assign('user_data', $user_data);
//        }
//        if ($info) {
//            $info = substr($info, 0, -1);
//            $this->assign('info', $info);
//        }
//        var_dump($list);
//                var_dump($info);
        $this->assign('list', $list);
        $this->assign('parent_id', $dev_id);
        $this->assign('dev_name', $dev_name['dev_name']);
        $this->display();
    }

    //编辑或添加子账号
    public function edit_son() {
        $dev_id = $_GET['dev_id'];
        $email = $_GET['email'];
        $model = D('Sj.Developer');
        $authority = $model->table('pu_sonuser_point')->where(array('user_id' => $dev_id))->find();
        if ($authority['app'] || $authority['game'] || $authority['sdk'] || $authority['gift'] || $authority['server'] || $authority['debut'] || $authority['screen']) {
            //软件管理
            $this->assign('soft', '1');
        }
        if ($authority['sale'] || $authority['order'] || $authority['income'] || $authority['selfservice']) {
            //财务管理
            $this->assign('finance', '1');
        }
        if ($authority['recomment'] || $authority['feedback'] || $authority['usercount']) {
            //用户数据
            $this->assign('user_data', '1');
        }
        if ($authority['dev_info']) {
            //用户数据
            $this->assign('info_manage', '1');
        }
        $this->assign('email', $email);
        $this->assign('dev_id', $dev_id);
        $this->assign('parent_id', $_GET['parent_id']);
        $this->assign('authority', $authority);
        if (!empty($dev_id)) {
            $this->assign('edit', 1);
        }
//        echo $dev_id;
        $this->display();
    }

    public function uc_getUserinfoByAccount($account) {
        $result = $this->request_ucenter('/api/account/queryUserInfo', array('account' => $account));
        return $result;
    }

    public function request_ucenter($api, $data, $need_crypt = true, $need_info = true, $method = 'POST') {
        if (empty($data) || empty($api) || !is_array($data)) {
            return false;
        }
        $start = microtime_float();
//        $app = defined('APP_NAME') ? APP_NAME : 'www';

        $ucenter = array(
            'uri' => C('reg_url'),
            'imguri' => 'http://image.anzhi.com/header',
            'serviceId' => '005',
            'serviceVersion' => '1.0',
            'privatekey' => 'hLpo7ksW45mH50qTx08Sr152',
            'serviceType' => 1,
        );

        if (empty($ucenter)) {
            return false;
        }
        if ($need_crypt === true) { //需要加密的数据要包在data层,否则无需
            import("@.ORG.GoDes");
            $des = new GoDes($ucenter['privatekey']);

            $temp_data = $des->encrypt(json_encode($data));
            $data = base64_encode($temp_data);
            $request_data = array('data' => $data);
        } else {
            $request_data = $data;
        }
        if ($need_info === true) {
            $request_data['serviceId'] = $ucenter['serviceId'];
            $request_data['serviceVersion'] = $ucenter['serviceVersion'];
            $request_data['serviceType'] = 0; //	0 移动端， 1 Web端
        }
        $ch = curl_init();
//	curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /*
          $header = array(
          'Content-Type: application/json;charset=utf-8',
          'Content-Length:'.strlen(json_encode($request_data)),
          );
          curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
         */

        $url = $ucenter['uri'] . $api;

        if ('POST' == $method) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
        } elseif ('GET' == $method) {
            $request_data = http_build_query($request_data);
            $url .= '?' . $request_data;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
//	list($rheader, $result) = explode("\r\n\r\n", curl_exec($ch));
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $code = $info['http_code'];
        $msg = date('Y-m-d H:i:s');
        $day = date('Y-m-d');
        if (is_array($request_data)) { //	为记日志处理
            $request_data = json_encode($request_data);
        }
        if ($code == 200) {
            if (empty($result)) {
                $new_msg = $msg . $request_data . " return empty \n";
                file_put_contents("/tmp/ucenter_{$api}_error_{$day}.log", $new_msg, FILE_APPEND);
            }
            $ret = json_decode($result, true);
            $end = microtime_float();
            $s = $end - $start;
            if ($s > 0.5) { //	此常量待定
                $rheader = explode("\r\n", $rheader);
                $ss = end($rheader);
                $new_msg = $msg . $request_data . " spend {$s} {$ss}\n";
                file_put_contents("/tmp/ucenter_{$api}_slow_{$day}.log", $new_msg, FILE_APPEND);
            }
            return $ret;
        } else {
            $msg .= $request_data . " service abnormal \n";
            file_put_contents("/tmp/ucenter_{$api}_error_{$day}.log", $msg, FILE_APPEND);
            return false;
        }
        return false;
    }

    //验证子账号邮箱
    public function verify_son_email() {
        $email = mysql_escape_string($_POST['email']);
        $is_userinfo = $this->uc_getUserinfoByAccount($email);
        if ($is_userinfo['code'] != 200) {
            $rt['url'] = C('reg_url');
            $rt['code'] = 1;
        } else {
            $model = D('Sj.Developer');
            $dev = $model->table('pu_developer')->where(array('dev_id' => $is_userinfo['pid']))->find();
            if ($dev) {
                if ($dev['dev_type'] == 1) {
                    $rt['code'] = 2;
                } else {
                    $rt['code'] = 3;
                }
            } else {
                $result = $model->table('pu_developer')->where(array('email' => $email, 'email_verified' => 1, 'status' => array('exp', '!=2')))->find();
                if ($result) {
                    if ($result['dev_type'] == 1) {
                        $rt['code'] = 2;
                    } else {
                        $rt['code'] = 3;
                    }
                } else {
                    $rt['code'] = 4;
                }
            }
        }
        exit(json_encode($rt));
    }

    //子账号权限
    public function edit_son_authority() {
        $model = D('Sj.Developer');
        //添加子账号权限
        $is_userinfo = $this->uc_getUserinfoByAccount($_POST['email']);
        if ($is_userinfo['code'] == 200 || $_POST['edit'] == 1) {
            if ($_POST['edit'] != 1) {
                $dev = $model->table('pu_developer')->where(array('dev_id' => $is_userinfo['pid']))->find();
                if ($dev) {
                    if ($dev['dev_type'] == 1) {
                        $re = array('error' => '该账号已被绑定，请勿重复绑定', 'code' => '213');
                    } else {
                        $re = array('error' => '已被注册为开发者，请更换其他账号', 'code' => '213');
                    }
                    echo json_encode($re);
                    exit();
                }
                $result = $model->table('pu_developer')->where(array('email' => $_POST['email'], 'email_verified' => 1, 'status' => array('exp', '!=2')))->find();
            }

            if ($result) {
                if ($result['dev_type'] == 1) {
                    $re = array('error' => '该账号已被绑定，请勿重复绑定', 'code' => '213');
                } else {
                    $re = array('error' => '已被注册为开发者，请更换其他账号', 'code' => '213');
                }
            } else {
                if (($_POST['point'] && !empty($_POST['point'])) || !empty($_POST['dataarr_off'])) {
                    if ($_POST['point']) {
                        if ($_POST['edit'] == 1) {
                            $parent_id = $model->table('pu_developer')->where(array('dev_id' => $_POST['dev_id']))->field('parentid')->find();

                            if (strpos($_POST['point'], 'recomment')) {
                                if ($is_update) {
                                    $model->table('pu_developer')->where(array('dev_id' => $_POST['dev_id']))->save(array('is_comment' => 1));
                                    // $model->update(array('dev_id' => $_POST['id']), array('__user_table' => 'pu_developer', 'is_comment' => 1), 'master');
                                }
                            }
                        }

                        $point_arr = explode(',', $_POST['point']);
                        foreach ($point_arr as $k => $v) {
                            $data[$v] = 1;
                        }
                    }
                    if ($_POST['dataarr_off']) {
                        $point_arr_off = explode(',', $_POST['dataarr_off']);
                        foreach ($point_arr_off as $k => $v) {
                            $data[$v] = 0;
                        }
                    }
                    //$data['__user_table'] = 'pu_sonuser_point';
                    $data['add_tm'] = time();
                    if ($_POST['edit'] && $_POST['edit'] == 1) {
                        //编辑  

                        $res = $model->table('pu_sonuser_point')->where(array('user_id' => $_POST['dev_id']))->save($data);
                        if ($res) {
                            $re = array('info' => '编辑成功', 'edit' => 1);
                        }
                    } else {
                        $tmp_data['parent_id'] = $_POST['parent_id'];
                        $tmp_data['user_name'] = $is_userinfo['data']['userInfo']['loginName'];
                        $tmp_data['email'] = $_POST['email'];
                        $tmp_data['add_tm'] = time();
                        $res = $model->table('pu_user_tmp')->add($tmp_data);
                        $this->send_sonactive_email($res, $_POST['email'], $is_userinfo['data']['userInfo']['loginName'], $is_userinfo['data']['userInfo']['pid']);
                        $data['user_id'] = $res;
                        $res = $model->table('pu_sonuser_point')->add($data);

//                        $dev_log = array('user_ip' => $user_ip, 'id' => $_SESSION['dev']['dev_id'], 'record_type' => 35, 'get_type' => 'add_sonuser', 'sonuser_id' => $data['user_id']);
//                        pu_dev_log($dev_log); //写日志 

                        if ($res) {
                            $this->writelog("添加了子账号" . $_POST['email'], 'pu_sonuser_point',$res, __ACTION__,'','add');
                            $re = array('info' => '添加成功', 'son_name' => $_POST['son_name']);
                        } else {
                            $re = array('error' => '添加失败');
                        }
                    }
                }
            }
        } else {
            $str = '该邮箱非安智认证邮箱，请先<a href=' . C('reg_url') . '>注册</a>';
            $re = array('error' => $str, 'code' => '213');
        }
        echo json_encode($re);
    }

    public function add_sonuser_success() {
        $this->assign('email', $_GET['email']);
        $this->assign('dev_id', $_GET['dev_id']);
        $this->display();
    }

    //子账号发送激活邮件
    public function send_sonactive_email($tmp_dev_id, $email, $username, $dev_id) {
//	global $conf;

        $model = D('Sj.Developer');
        $option = array(
            'table' => 'pu_user_tmp',
            'where' => array(
                'dev_id' => $tmp_dev_id,
                'email' => $email,
            ),
        );
//	$user = $model->findOne($option);
        $user = $model->table('pu_user_tmp')->where(array(
                    'dev_id' => $tmp_dev_id,
                    'email' => $email,
                ))->find();
        if ($user['email_verified'] == 1) {
//		return array(
//			'error' => '100',
//			'msg' => '您的邮箱地址已验证成功，请不要重复验证！',
//		);
            $re = array('error' => '您的邮箱地址已验证成功，请不要重复验证！');
            echo json_encode($re);
            exit();
        }
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
            $url_host = 'http://localhost:82';
        } else if ($_SERVER['SERVER_ADDR'] == '192.168.0.99' || $_SERVER['SERVER_ADDR'] == '192.168.0.252' || $_SERVER['SERVER_ADDR'] == '114.247.222.131') {
            $url_host = 'http://9.newdev.anzhi.com';
        } else {
            $url_host = 'http://dev.anzhi.com';
        }
        $code = rand_code_md5();

        $msec = microtime(true);

        $email_time = date('Y-m-d H:i:s');
        $email_day = date('Y年m月d日'); //{$_SESSION['USER_NAME']}
        $email_cont = <<<EOT
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>安智开发者</title>
</head>
<body>
亲爱的{$username}，<br /><br />
欢迎成为安智开发者联盟成员！请按照以下提示完成安智子账号激活。<br /><br />
请您点击下面的链接操作（此链接在24小时内激活有效）：<br /><br />
<a href={$url_host}/active_sonemail.php?code={$code}&id={$tmp_dev_id}&d={$dev_id}&r={$msec}  target=_blank>{$url_host}/active_sonemail.php?code={$code}&id={$tmp_dev_id}&d={$dev_id}&r={$msec}
        </a>
            <br /><br />
(如果上面不是链接形式，请复制上方网页地址到浏览器地址栏中打开)<br /><br /><br />
为什么您会收到这封邮件？<br />
您收到这封邮件，是由于在安智开发者联盟进行「绑定子帐号」使用了这个邮箱地址；<br />
如果您没有「绑定子帐号」，请忽略这封邮件，您不需要退订或进行其他进一步的操作。<br /><br /><br />
安智开发者联盟敬上<br />
<a href={$url_host} target=_blank>{$url_host}</a><br />
日期：$email_day
</body>
</html>
EOT;
        $rt = array();
        //开始发邮件
        $rs = _http_post_email(array('email' => $email, 'name' => $username, 'subject' => '请激活安智账户', 'content' => $email_cont));
//                $emailmodel = D("Dev.Sendemail");
//                $rs = $emailmodel->realsend($email, $username, '请激活安智账户', $email_cont);
        //邮件日志,开始
        if (substr(strtolower(PHP_OS), 0, 3) != 'win') {
            $email_log = '/data/att/permanent_log/dev.anzhi.com/' . date('Y-m-d') . '/email_send.log';
        } else {
            $email_log = 'e:/' . date('Ymd') . '/email_send.log';
        }
        if (!is_dir(dirname($email_log)))
            mkdir(dirname($email_log), 0777, true);
        $log_rs = json_encode($rs);
        file_put_contents($email_log, "{$email}|{$email_cont}|{$log_rs}|{$tmp_dev_id}|" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        //邮件日志,结束

        if ($rs['http_code'] != 200) {
//		return array(
//			'error' => 5,
//			'msg' => '和邮件服务器通讯失败！',
//		);
            $re = array('error' => '和邮件服务器通讯失败！');
            echo json_encode($re);
            exit();
        } else {
            $ret = json_decode($rs['ret'], true);
            if ($ret['code'] < 0) {
//			return array(
//				'error' => $ret['code'],
//				'msg' => $ret['msg'],
//			);
                $re = array('error' => $ret['msg']);
                echo json_encode($re);
                exit();
            } else { //成功进入发送队列
                $rt['error'] = $ret['code'];
                $rt['msg'] = $ret['msg'];
                $_SESSION['send_active_email_time'] = time();
                //计发送次数
                $_SESSION['active_email'][$email] += 1;
                //$model->update(array('id' => $tmp_dev_id), array('__user_table'=>'pu_user_tmp','active_email'=>$code,'active_email_sendtime'=>time()), 'master');
                $model->table('pu_user_tmp')->where(array('id' => $tmp_dev_id))->save(array('active_email' => $code, 'active_email_sendtime' => time()));
            }
        }
        //结束发邮件
        return $rt;
    }

    //删除子账号
    public function del_son() {
        $dev_id = $_GET['id'];
        $model = D('Sj.Developer');
        $del_son = $model->where(array('dev_id' => $dev_id))->save(array('status' => 2));
        if ($del_son) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    //开发者审核中__信息修改__编辑
    public function save_data_edit() {
        $sid = $_GET['uid'];
        $this->assign('edit_dev_name', $_GET['edit_dev_name']);
        $this->usersedit($sid);
    }

    //开发者审核中__编辑
    public function user_audit_edit() {
        $sid = $_GET['uid'];
        $this->usersedit($sid);
    }

    //开发者通过列表__编辑
    public function user_pass_edit() {
        $sid = $_GET['uid'];
        $this->usersedit($sid);
    }

    //开发者未通过列表__编辑
    public function user_not_through_edit() {
        $sid = $_GET['uid'];
        $this->usersedit($sid);
    }

    //开发者屏蔽列表__编辑
    public function user_screen_edit() {
        $sid = $_GET['uid'];
        $this->usersedit($sid);
    }

    //开发者管理_编辑开发者_显示
    public function usersedit($sid) {
        $this->sid = $sid;
        $p = $_GET['p'];
        if (empty($this->sid)) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/userlists');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        $model = new Model();
        $this->users_db = D('Sj.Developer');
        $this->userslist = $this->users_db->where(array('dev_id' => $this->sid))->select();
        $user_list = $model->table('pu_user')->where(array('userid' => $this->sid))->find();
		$user_settlement = $model->table('pu_developer_settlement')->where(array('dev_id' => $this->sid,'del'=>0))->find();
        $this->assign('preurl', $_POST['preurl']);
        $this->assign('p', $p);
        $this->assign('userslist', $this->userslist[0]);
        $this->assign('userlist', $user_list);
		$this->assign('user_settlement', $user_settlement);
        $reffer = $_SERVER["HTTP_REFERER"];
        $this->assign('reffer', $reffer);
        $this->display("usersedit");
    }

    //bbs ucapi 用户相关信息 (修改邮箱)
    /* $params = array(
      'new_mail' => mail,修改的值
      );
      return  -7修改失败  -8 用户受保护 >0 修改成功
     */
    public static function pub_change_email($uid, $username, $auth, $params = array()) {
        if (empty($params))
            return false;
        $discuz_api = "http://bbs.anzhi.com/member.php?mod=modify_userinfo_dev";
        $data_info = array();
        $str = md5($uid . $auth . '$%QIDNS*&');
        $username = base64_encode(mb_convert_encoding($username, 'GBK', 'utf-8'));
        $data_info = array('uid' => $uid, 'username' => $username, 'auth' => $auth, 'str' => $str);
        foreach ($params as $field => $info) {
            $data_info[$field] = $info;
        }
        $user_info = UserAction::pub_require_url($discuz_api, $data_info);
        return $user_info;
    }

    public static function pub_require_url($url, $data = array(), $timeout = 4) {
        if (!is_array($data) || !$data) {
            return False;
        }
        if ($data) {
            $str = http_build_query($data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($str) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //开发者管理_开发者信息编辑_执行
    public function usersedit_edit() {
        $preurl = $_POST['preurl'] ? $_POST['preurl'] : '';
        $reffer = $_POST['reffer'] ? $_POST['reffer'] : '';
        $this->sid = $_GET['uid'];
        $p = $_POST['p'];
        //$preurl = $preurl."/p/{$p}";
        if (empty($this->sid)) {
            $this->assign('jumpUrl', $preurl);
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }

        $this->users_db = D('Sj.Developer');
        if ($_POST['dev_type'] == 0) {
            $res = $this->users_db->where("parentid = '{$_POST['dev_id']}' and status = 0")->field('dev_id')->select();
        } else {
            $res = $this->users_db->where("parentid = '{$_POST['parentid']}' and status = 0")->field('dev_id')->select();
        }
        $dev_str = '';
        foreach ($res as $k => $v) {
            $dev_str .= $v['dev_id'] . ',';
        }
        $dev_str = substr($dev_str, 0, -1);
        if ($_POST['dev_type'] == 1) {
            if ($dev_str)
                $dev_str .= ',' . $_POST['parentid'];
            else
                $dev_str = $_POST['parentid'];
        }else {
            if ($dev_str)
                $dev_str .= ',' . $_POST['dev_id'];
            else
                $dev_str = $_POST['dev_id'];
        }
//        var_dump($dev_str);
//        die();
        //一些唯一性信息检查
        if ($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND dev_name='{$_POST['dev_name']}'")->count()) {
            $this->error("该作者已存在，请重填！");
        }
        /*       if ($_POST['type'] == 0) { //公司
          if ($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND dev_name='{$_POST['dev_name']}'")->count()) {
          $this->error("该作者已存在，请重填！");
          }
          } else {
          if ($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND dev_name='{$_POST['dev_name']}' AND type=0")->count()) {
          $this->error("该作者已存在，请重填！");
          }
          }
         */
		if ($_POST['type'] == 0) { //公司
			if(empty($_POST['company_legal'])){
				$this->error("请填写公司法人代表！");
			}
			if(empty($_POST['company_address'])){
				$this->error("请填写公司注册地址！");
			}
		}
        if ($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND email='{$_POST['email']}' AND email_verified=1")->count()) {
            $this->error("该电子邮件已存在，请重填！");
        }
        if ($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND cardnumber='{$_POST['cardnumber']}' AND cardtype=1 and dev_id!='{$_POST['parentid']}' and dev_type=0")->count()) {

            $this->error("该身份证号已存在，请重填！");
        }
        /* 	if($_POST['im_id']) {
          if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND im_type='{$_POST['im_type']}' AND im_id='{$_POST['im_id']}'")->count()) {
          $this->error("该联系IM号已存在，请重填！");
          }
          }
          if($_POST['phone']) {
          if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND phone='{$_POST['phone']}'")->count()) {
          $this->error("该固定电话已存在，请重填！");
          }
          } */
        /*
          if ($_POST['company']) {
          if ($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND company='{$_POST['company']}' and dev_id!='{$_POST['parentid']}' and dev_type=0")->count()) {
          echo $this->users_db->getLastSql();
          $this->error("该公司全称已存在，请重填！");
          }
          }
         */
        //上传身份证,营业执照
        if ($_POST['type'] == 1) {
            unset($_FILES['charterpic']);
        }

        $file = array();
        if ($_FILES) {
            foreach ($_FILES as $key => $val) {
                $ext = strtolower(pathinfo($val['name'],PATHINFO_EXTENSION));
                if(!$val['size']){
                    continue;
                }
                if ($key == 'cardpic' && ($val['size'] > 2097152 || !in_array($ext,array('png','jpg')))) {
                    $this->error('身份证扫描件不能大于2M，请重传！');
                }
                if ($key == 'charterpic' &&  ($val['size'] > 2097152 || !in_array($ext,array('png','jpg')))) {
                    $this->error('营业执照扫描件不能大于2M，请重传！');
                }
				if ($key == 'handcardpic' &&  ($val['size'] > 2097152 || !in_array($ext,array('png','jpg')))) {
                    $this->error('请提供手持身份证原件扫描件，大小不超过2M，图片格式.jpg/.png');
                }
                if ($key == 'licence_url' &&  ($val['size'] > 2097152 || !in_array($ext,array('png','jpg')))) {
                    $this->error('请提供网络文化经营许可证扫描件，大小不超过2M，图片格式.jpg/.png');
                }
                if ($key == 'news_license_url' &&  ($val['size'] > 2097152 || !in_array($ext,array('png','jpg')))) {
                    $this->error('请上传《互联网新闻信息服务许可证》 的正面（彩色）扫描件，图片.jpg或.png格式，大小2M以内；');
                }
                if ($key == 'program_license_url' &&  ($val['size'] > 2097152 || !in_array($ext,array('png','jpg')))) {
                    $this->error('请上传《信息网络传播视听节目许可证》的正面（彩色）扫描件，图片.jpg或.png格式，大小2M以内；');
                }
                if ($val['error'] == 0)
                    $file[$key] = '@' . $val['tmp_name'];
            }
        }
        if ($file) {
            //保留，本地测试用 $file['static_data'] = UPLOAD_PATH;
            $file['static_data'] = '/data/att/m.goapk.com';
            $file['do'] = 'save';
        }
        //在UserAction类中调_http_post方法
        $upload_model = D("Dev.Uploadfile");
        $upload = $upload_model->_http_post($file);
        if ($upload['info']['http_code'] != 200) {
            $this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
        }
        $this->userslist = array();
        if ($upload['ret']) {
            foreach ($upload['ret'] as $key => $val) {
                if ($val == 'failed') {
                    if ($key == 'cardpic') {
                        $this->error('身份证扫描件上传失败，请重试！');
                    } else if ($key == 'charterpic') {
                        $this->error('营业执照扫描件上传失败，请重试！');
                    }else if($key == 'handcardpic'){
						$this->error('手持身份证扫描件上传失败，请重试！');
					}else if($key == 'licence_url'){
                        $this->error('网络文化经营许可证扫描件上传失败，请重试！');
                    }else if($key == 'news_license_url'){
                        $this->error('互联网新闻信息服务许可证上传失败，请重试！');
                    }else if($key == 'program_license_url'){
                        $this->error('信息网络传播视听节目许可证上传失败，请重试！');
                    }
                } else {
                    $this->userslist[$key] = $val;
                }
            }
        }
		//勾选删除时删除手持身份证图片
		if(isset($_POST['del_handcard'])){
			$this->userslist['handcardpic']='';
		}
        unset($upload['errno'], $upload['error'], $upload['info']);
        $this->userslist['type'] = $_POST['type'];
        $this->userslist['dev_name'] = trim($_POST['dev_name']);

        $this->userslist['truename'] = trim($_POST['truename']);
//        $this->userslist['email'] = $_POST['email'];


        if ($_POST['mobile']) {
            $this->userslist['mobile'] = $_POST['mobile'];
            $this->userslist['mobile_verified'] = 1;
            $this->userslist['mobile_time'] = time();
        }
        $this->userslist['cardnumber'] = $_POST['cardnumber'];
        $this->userslist['charter'] = $_POST['charter'];
        $this->userslist['company'] = $_POST['company'];
		$this->userslist['address'] = $_POST['address'];
        $this->userslist['im_type'] = $_POST['im_type'];
        $this->userslist['im_id'] = $_POST['im_id'];
        $this->userslist['phone'] = $_POST['phone'];
        $this->userslist['site'] = $_POST['site'];
        $this->userslist['zipcode'] = $_POST['zipcode'];
        $this->userslist['location'] = $_POST['location'];
        $this->userslist['book_from'] = $_POST['book_from'];
        $this->userslist['bankcard'] = $_POST['bankcard'];
        $this->userslist['licence_num'] = $_POST['licence_num'];
        $this->userslist['op_company'] = $_POST['op_company'];
        $this->userslist['op_address'] = $_POST['op_address'];
        $this->userslist['op_tel'] = $_POST['op_tel'];
        $this->userslist['op_name'] = $_POST['op_name'];
		$res = $this->save_developer_settlement($_POST);
        $list = $this->users_db->where(array('dev_id' => $this->sid))->save(array('email' => $_POST['email']));
        $list1 = $this->users_db->where('dev_id in (' . $dev_str . ')')->save($this->userslist);
        if (false !== $list && false !== $list1&& false !== $res) {
            $soft = M('soft');
            $soft_tmp = M('soft_tmp');
            $data['dev_name'] = $this->userslist['dev_name'];
            $soft->where(array('dev_id' => $this->sid))->save($data);
            $soft_tmp->where(array('dev_id' => $this->sid))->save($data);
            //同步到论坛邮箱
            /* 			$params = array(
              //修改的值
              'new_mail' => $_POST['email'],
              );
              $auth = $_POST['email'];
              $rs = UserAction::pub_change_email($this->sid, $_POST['user_name'],$auth,$params);
              if($rs <=0 ){
              $this -> error("同步邮箱失败");
              } */
            $this->writelog('修改ID为[' . $dev_str . ']的开发者信息', 'pu_developer',$dev_str, __ACTION__,'','edit');
            //$this->assign('jumpUrl',$preurl);
            $this->assign('jumpUrl', $reffer);
            //echo "<script>alert('恭喜您，编辑开发者成功！');window.history.go(-2);</script>";
            $this->success("恭喜您，编辑开发者成功！");
        } else {
            $this->assign('jumpUrl', $preurl);
            $this->error('编辑失败，请重试！');
        }
    }
	//保存开发者联运信息
	function save_developer_settlement($data){
		$model = new Model();
		$dev_settlement_info = array();
		$operate = false;
		if(isset($data['type'])&&$data['type']==0){
			//公司类型
			$operate = true;
			$dev_settlement_info['company_legal'] = $data['company_legal'];
			$dev_settlement_info['company_address'] = $data['company_address'];
		}
		$dev_arr = array('base_company_address','base_zip','company_tel','company_fax','base_contacts','base_phone','company_email','company_bank','company_account','company_card','company_rate');
		
		foreach($dev_arr as $v){
			if(isset($data[$v])) $dev_settlement_info[$v] = $data[$v];
			if(!empty($data[$v])) $operate = true; //所有为空不入库
		}
		if($operate){
			$dev_settlement_info['up_tm'] = time();
			$settle_info = $model->table('pu_developer_settlement')->where(array('dev_id'=>$data['dev_id'],'del'=>0))->find();
			if(!$settle_info){
				$dev_settlement_info['dev_id'] = $data['dev_id'];
				$dev_settlement_info['add_tm'] = time();
				$res = $model->table('pu_developer_settlement')->add($dev_settlement_info);
			}else{
				$res = $model->table('pu_developer_settlement')->where(array('dev_id' => $data['dev_id']))->save($dev_settlement_info);
			}
			return $res;
		}else{
			return true;
		}		
	}
    function denyuser() {
        $model = new Model();
        $reason_list = $model->table("dev_reason")->where(array("status" => 1, "reason_type" => 2))->order('pos asc')->select();
        foreach ($reason_list as $k => $v) {
            if ($v['content2']) {
                $reason_list[$k]['content2'] = explode('<br />', $v['content2']);
            }
        }
        $dev_id = escape_string($_GET['uid']);
        $count = $model->table("pu_user")->where('userid =' . $dev_id . ' and status = 1')->count();
        if ($count == 0) {
            $this->error("开发者不存在！！");
        }
        $userinfo = $model->table("pu_user")->where('userid =' . $dev_id . ' and status = 1')->select();
        $comment_cnt = $model->table("sj_soft_comment")->where('userid=' . $dev_id . ' and status =1')->count();
        //上架__下架统计
        $softlist = $model->table('sj_soft')->where("dev_id='{$dev_id}' and claim_status=2 and status=1 and hide in (1,3)")->order('softname')->select();
        foreach ($softlist as $info) {
            $soft_file = $model->table('sj_soft_file')->where("softid='{$info['softid']}'")->find();
            $info['iconurl'] = $soft_file ? "<img src='" . IMGATT_HOST . "{$soft_file['iconurl']}' border='0' style='width:32px;height:32px;' />" : '';

            $soft_list[$info['hide']][] = $info;
        }

        //临时表sj_soft_tmp软件统计
        $soft_tmp_list = $model->table('sj_soft_tmp')->where("status=2 and record_type <= 3 and dev_id='{$dev_id}'")->order('softname')->select();
        foreach ($soft_tmp_list as $val) {
            if ($val['record_type'] == 2) {
                //修改描述图标
                $tmp_file = $model->table('sj_soft_file')->where("softid={$val['softid']} and package_status=1")->find();
            } else {
                $tmp_file = $model->table('sj_soft_file_tmp')->where("tmp_id={$val['id']} and package_status=1")->find();
            }
            $val['iconurl'] = $tmp_file ? "<img src='" . IMGATT_HOST . "{$tmp_file['iconurl']}' border='0' style='width:32px;height:32px;' />" : '';
            $soft_tmp[$val['record_type']][] = $val;
        }
        $this->assign('last_status', $_GET['last_status']);
        $this->assign('userinfo', $userinfo[0]);
        $this->assign('comment_cnt', $comment_cnt);
        $this->assign('userid', $dev_id);
        $this->assign('reason_list', $reason_list);
        $this->assign('p', $_GET['p']);
        $this->assign('softlist', $soft_list);
        $this->assign('soft_tmp', $soft_tmp);
        $this->display('denyuser');
    }

    function denyuser_user_softid() {
        $model = new Model();
        $search = isset($_POST['search']) ? "softname like '%" . $_POST['search'] . "%' and " : '';
        $dev_id = escape_string(isset($_POST['dev_id']) ? $_POST['dev_id'] : '');
        $softlist = $model->table('sj_soft')->where($search . 'dev_id = ' . $dev_id . ' and status = 1 and hide in (1,2,4,5) ')->select();
        foreach ($softlist as $info) {
            $soft_list[$info['hide']][] = $info;
        }
        $str = '';
        $i = 1;
        $a = 5;
        $soft_type = array('1' => '上架软件', '2' => '新软件', '4' => '编辑软件', '5' => '升级(更新)软件');
        $str = "<table>";
        $str .='<tr><td><label for="all"><input type="checkbox" id="all" name="all" onclick="checkall()">全选</label></td></tr>';
        foreach ($soft_type as $key => $title) {
            if (!$soft_list[$key])
                continue;
            $list = $soft_list[$key];
            $count = count($soft_list[$key]);
            $str .= '<tr bgColor="green"><td>' . $title . '</td><td></td><td></td><td></td><td></td></tr>';
            $i = 1;
            foreach ($list as $info) {
                if ($i % $a == 1)
                    $str .='<tr>';
                $str .= '<td><label for="softid_' . $info['softid'] . '"><input type="checkbox" name="softid[]" id="softid_' . $info['softid'] . '" value="' . $info['softid'] . '"/>' . $info['softname'] . '</label></td>';
                if ($i % $a == 0 || $i == $count)
                    $str .='</tr>';
                $i++;
            }
            $str .= "</table>";
        }
        echo $str;
    }

    function denyuser_do() {
        $userid = $_POST['userid'];
        $p = $_POST['p'];
        $cmmt_userid = $_POST['cmmt_userid'] ? $_POST['cmmt_userid'] : 0;
        $softids = $_POST['softid'] ? escape_array($_POST['softid']) : null;
        $shield_reason = $_POST['shield_reason'];
        $model = new Model();
        $data['status'] = 0;
        $devid = escape_string($_POST['dev_id'] ? $_POST['dev_id'] : 0);
        if (empty($devid) && empty($cmmt_userid) && empty($softids)) {
            $this->error('请选择你要进行的操作！');
        }
        $status = 0;
        if ($devid) {
            $affect = $model->table('pu_user')->where('userid = ' . $devid)->save($data);
            if ($affect) {
                $status = 1;
                $this->writelog('userid 为 ' . $userid . "的用户被屏蔽  屏蔽原因：{$shield_reason}", 'pu_user',$userid, __ACTION__,'','edit');
            }
            //屏蔽信息写入 pu_developer
			$data = array(
				'status' => '-2', 
				'shield_reason' => $shield_reason,
				'shield_time' => time(),
                'last_time' => time(),
				'last_status'=> isset($_POST['last_status']) ? $_POST['last_status'] : 0,
			);
            $developer = $model->table('pu_developer')->where("dev_id='{$devid}'")->save($data);
            if ($developer) {
                //发送邮件提醒
                $tm = date("Y-m-d", time());
                $dever = $model->table('pu_developer')->where("dev_id='{$devid}'")->field('dev_id,email,dev_name')->find();
                $config_txt = C('_config_txt_');
                $subject = $config_txt['dev_shield_subject'];
                $search = array("devname", "tm", "msg");
                $replace = array($dever['dev_name'], $tm, $shield_reason);
                $email_cont = str_replace($search, $replace, $config_txt['dev_shield']);
                $emailmodel = D("Dev.Sendemail");
                $emailmodel->realsend($dever['email'], $dever['dev_name'], $subject, $email_cont);
            }
        }
        if ($cmmt_userid) {
            $affect = $model->table('sj_soft_comment')->where(array('userid' => $userid))->save(array('status'=>0));
            if ($affect) {
                $status += 2;
                $this->writelog('userid 为 ' . $userid . '的评论已经全部永久性删除', 'sj_soft_comment',$userid, __ACTION__,'','del');
            }
        }
        $data = array();
        $time = time();
        $soft_obj = M('soft');
        if (!empty($softids)) {
			$pkg = array();
            foreach ($softids as $v) {
                if (strstr($v, 'tmp_')) {
                    $tmp_id = substr($v, 4);
                    $data['status'] = 0;
                    $data['last_refresh'] = $time;
                    $data['deny_msg'] = '从开发者屏蔽管理中下架软件';
                    $soft_tmp = $model->table('sj_soft_tmp')->where("id='{$tmp_id}'")->field('status,id,package')->find();
                    if ($soft_tmp['status'] == 2) {
                        $affect = $model->table('sj_soft_tmp')->where("id='{$tmp_id}'")->save($data);
                        if ($affect) {
							$pkg[] = $soft_tmp['package'];
							 //修改sj_soft_status表
							//update_soft_status(array('soft_status'=>60,'update_tm'=>$time,'version'=>''),$soft_tmp['package']);	  
							getSoftStatusByPackage($soft_tmp['package']);
                            $map['tmp_id'] = $tmp_id;
                            $map['reason'] = '从开发者屏蔽管理中下架软件';
                            $map['create_tm'] = $time;
                            $map['adminid'] = $_SESSION['admin']['admin_id'];
                            $add_log = $model->table('sj_reject_log')->add($map);
                            if (!$add_log) {
                                $this->error("插入驳回日志表失败");
                            }
                            $this->writelog('审核中id 为 ' . $tmp_id . "的软件被下架  下架原因：{$shield_reason}",'sj_soft_tmp',$tmp_id,__ACTION__,'','edit');
                        }
                    } else {
                        $this->error("软件ID为{$tmp_id}的软件不在审核中");
                    }
                } else {
                    $datas['hide'] = 3; //软件下架
                    $datas['deny_msg'] = '该软件已经在开发者屏蔽管理中下架';
                    $datas['last_refresh'] = time();
                    $soft_cnt = $model->table('sj_soft')->where("softid='{$v}' and hide=1 and status=1")->field('hide,package')->find();
                    if ($soft_cnt['hide'] == 1) {
                        $affects = $model->table('sj_soft')->where("softid='{$v}' and hide=1")->save($datas);
                        if ($affects) {
							$pkg[] = $soft_cnt['package'];
							//修改sj_soft_status表
							//update_soft_status(array('soft_status'=>60,'update_tm'=>$time,'version'=>''),$soft_cnt['package']);	  
							getSoftStatusByPackage($soft_cnt['package']);
                            $maps['softid'] = $v;
                            $maps['reason'] = '从开发者屏蔽管理中下架软件';
                            $maps['create_tm'] = $time;
                            $maps['adminid'] = $_SESSION['admin']['admin_id'];
                            $add_logs = $model->table('sj_reject_log')->add($maps);
                            if (!$add_logs) {
                                $this->error("插入驳回日志表失败");
                            }
                            $this->writelog('软件id 为 ' . $v . "的软件被下架  下架原因：{$shield_reason}",'sj_soft',$v,__ACTION__,'','edit');
                            //后台操作数据日志
                            update_data_log($v, 'delete', $soft_obj);
                        }
                    } else {
                        $this->error("软件ID为{$v}的软件不在上架状态");
                    }
                }
            }
			$where = array(
				'status' =>1,
				'hide' =>1,
				'dev_id' => array('exp','!=0'),
				'package'=>array('in',$pkg)
			);
			$res =  $model->table('sj_soft')->where($where)->field('package,version')->select();
			if($res){
				foreach($res as $v){
					//修改sj_soft_status表
					//update_soft_status(array('soft_status'=>50,'update_tm'=>$time,'version'=>$v['version']),$v['package']);	
					getSoftStatusByPackage($v['package']);
				}
			}
			
        }
        $message = '';
        switch ($status) {
            case 1:$message = '该用户账户已经被屏蔽';
                break;
            case 2:$message = '该用户的评论已经被删除';
                break;
            case 3:$message = '该用户的账户已经被屏蔽,评论已经被删除';
                break;
            case 4:$message = '该开发者的软件已经被下架';
                break;
            case 5:$message = '该用户账户已经被屏蔽,软件已经被下架';
                break;
            case 6:$message = '该用户的评论已经被删除,开发的软件已经被下架';
                break;
            case 7:$message = '该用户账户被屏蔽,评论被删除,软件被下架';
                break;
        }
        if ($status) {
			if($_POST['last_status'] == 1){
				$str = 'auditforuser';
			}else if($_POST['last_status'] != '' && $_POST['last_status'] == 0 ){
				$str = 'userlists';
			}else{
				//如果没传$_POST['last_status']最回到信息修改列表
				$str = 'save_dev_data';
			}
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/User/".$str."/p/{$p}");
            $this->success($message);
        }
    }

    //恢复开发者的账户
    function permit_user() {
        $preurl = $_POST['preurl'] ? $_POST['preurl'] : str_replace("@", "/", $_GET['preurl']);
        $user_id = $_GET['uid'];
        $where_user = '';
        $where_dev = '';
        if (is_numeric($user_id)) { //单个用户
            $where_user = "userid='{$user_id}'";
            $where_dev = "dev_id='{$user_id}'";
        } else if (preg_match('/[0-9,]+/', $user_id)) { //批量用户
            $where_user = "userid IN ({$user_id})";
            $where_dev = "dev_id IN ({$user_id})";
        } else {
            $this->assign('jumpUrl', $preurl);
            $this->error('参数错误！');
        }
        $model = new Model();
        $data['status'] = 1;
        $affect = $model->table('pu_user')->where($where_user)->save($data);
		$list = $model->table('pu_developer')->where($where_dev)->field('dev_id,last_status')->select();
		foreach($list as $v){
			$where = array(
				'status'=> -2,
				'dev_id'=>$v['dev_id'],
			);
			$map = array(
				'status' => $v['last_status'],
                'last_time' => time()
			);
			$model->table('pu_developer')->where($where)->save($map);
		}
		$this->writelog("恢复ID：{$_GET['uid']}开发者",'pu_developer',$_GET['uid'],__ACTION__,'','edit');
        $this->assign('jumpUrl', $preurl);
        $this->success("该用户已经被恢复！");
    }

    //认证审核
    function approve() {
        $user_id = $_GET['uid'];
        $p = $_GET['p'];
        $this->users_db = D('Sj.Developer');
        $data['approve'] = 1;
        $affect = $this->users_db->where(array('dev_id' => $user_id))->save($data);
        if ($affect) {
            $this->writelog('软件开发者的ID' . $user_id . '的认证状态为已认证','pu_developer',$user_id,__ACTION__,'','edit');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/User/userlists/p/{$p}");
            $this->success("已认证该开发者！");
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/User/userlists/p/{$p}");
            $this->error("认证失败！");
        }
    }

    //取消认证
    function reapprove() {
        $user_id = $_GET['uid'];
        $this->users_db = D('Sj.Developer');
        $data['approve'] = 0;
        $affect = $this->users_db->where(array('dev_id' => $user_id))->save($data);
        if ($affect) {
            $this->writelog('软件开发者的ID' . $user_id . '的认证状态已撤消','pu_developer',$user_id,__ACTION__,'','edit');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/userlists');
            $this->success("已取消对该开发者的认证！");
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/userlists');
            $this->error("认证失败！");
        }
    }

    //短信问题反馈
    public function problem_sms() {
        require_once(realpath(dirname(__FILE__) . '/../../../../') . '/GoPHP/config/config.inc.php');

        $model = new Model();
        if (empty($_GET['p'])) {
            $this->map.="s.status=1";
            if (!empty($_GET['dev_id'])) {
                $this->map.=" and s.dev_id='{$_GET['dev_id']}'";
            }
            if (!empty($_GET['username'])) {
                $this->map.=" and u.user_name like '%{$_GET['username']}%'";
            }
            if (!empty($_GET['truename'])) {
                $this->map.=" and d.truename like '%{$_GET['truename']}%'";
            }
            if (isset($_GET['type']) && $_GET['type'] != -1) {
                $this->map.=" and d.type='{$_GET['type']}'";
            }
            if (!empty($_GET['mobile'])) {
                $this->map.=" and d.mobile like '%{$_GET['mobile']}%'";
            }
            if (!empty($_GET['dev_name'])) {
                $this->map.=" and d.dev_name like '%{$_GET['dev_name']}%'";
            }
            if (!empty($_GET['cardnumber'])) {
                $this->map.=" and d.cardnumber like '%{$_GET['cardnumber']}%'";
            }
            if (!empty($_GET['charter'])) {
                $this->map.=" and d.charter like '%{$_GET['charter']}%'";
            }
            if (!empty($_GET['location'])) {
                $this->map.=" and d.location='{$_GET['location']}'";
            }
            if (!empty($_GET['email'])) {
                $this->map.=" and d.email='{$_GET['email']}'";
            }
            if (!empty($_GET['begintime'])) {
                $begintime = strtotime($_GET['begintime']);
                $this->map.=" and s.quest_time>='{$begintime}'";
            }
            if (!empty($_GET['endtime'])) {
                $endtime = strtotime($_GET['endtime']);
                $this->map.=" and s.quest_time<='{$endtime}'";
            }
            $_SESSION['admin']['userlist']['where'] = $this->map;
        } else {
            $this->map = $_SESSION['admin']['userlist']['where'];
        }
        //dump($this->map);
        $this->users_db = D('Sj.Developer');
        import("@.ORG.Page");
        $count = $model->Table('pu_problem_sms s LEFT JOIN pu_developer d ON s.dev_id=d.dev_id LEFT JOIN pu_user u ON s.dev_id=u.userid')->where($this->map)->count();
        $Page = new Page($count, 15);
        $this->userslist = $model->Table('pu_problem_sms s LEFT JOIN pu_developer d ON s.dev_id=d.dev_id LEFT JOIN pu_user u ON s.dev_id=u.userid')->where($this->map)->field('s.*,d.dev_name,d.truename,d.site,d.introduction,d.zipcode,d.address,d.phone,d.im_id,d.im_type,d.email,d.email_verified,d.email_time,d.mobile,d.mobile_verified,d.mobile_time,d.company,d.type,d.fax,d.cardtype,d.cardnumber,d.cardpic,d.charter,d.charterpic,d.message,d.approve,d.old_start,d.register_time,d.pass_time,d.verify_email,d.verify_mobile,d.verify_time,d.dismissed,d.shield_reason,d.shield_time,d.active_email,d.active_email_sendtime,d.location,d.complete_time,d.last_time,d.dismissed_time,u.userid,u.user_name')->order('s.quest_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $devid_arr = array();
        if ($this->userslist) {
            foreach ($this->userslist as $key => $val) {
                $devid_arr[] = $val['id'];  //身份证
                $devid_arr[] = 'a' . $val['id']; //营业执照

                // $this->userslist[$key]['email_verified_str'] = $val['email_verified'] ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
                // $this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified'] ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
                if ($val['type'] == 0) {
                    $this->userslist[$key]['type_str'] = '公司';
                } else if ($val['type'] == 1) {
                    $this->userslist[$key]['type_str'] = '个人';
                } else if ($val['type'] == 2) {
                    $this->userslist[$key]['type_str'] = '团队';
                }
                $this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST . $val['cardpic'] : '';
                $this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST . $val['charterpic'] : '';
                if ($val['im_type'] == 1) {
                    $this->userslist[$key]['im_type_str'] = 'QQ';
                } else if ($val['im_type'] == 2) {
                    $this->userslist[$key]['im_type_str'] = 'Gtalk';
                } else if ($val['im_type'] == 3) {
                    $this->userslist[$key]['im_type_str'] = 'Msn';
                } else if ($val['im_type'] == 4) {
                    $this->userslist[$key]['im_type_str'] = 'Skype';
                }

                if ($val['location'] == 1) {
                    $this->userslist[$key]['location_str'] = '中国大陆';
                } else if ($val['location'] == 2) {
                    $this->userslist[$key]['location_str'] = '港澳台和国外';
                }

                $this->userslist[$key]['register_time'] = $val['register_time'] ? date('Y-m-d H:i:s', $val['register_time']) : '';
                $this->userslist[$key]['quest_time'] = $val['quest_time'] ? date('Y-m-d H:i:s', $val['quest_time']) : '';
            }
        }
        $devid_str = "'" . implode("','", $devid_arr) . "'";
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("list_type", $list_type);
        $this->assign('userslist', $this->userslist);
        $this->assign('devid_str', $devid_str);
        $this->display();
    }

    //短信问题反馈_删除
    public function problem_sms_del() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/problem_sms');
            $this->error("参数错误！");
        }
        $id_arr = explode(',', $_GET['id']);
        if ($id_arr) {
            foreach ($id_arr as $key => $val) {
                if (!is_numeric($val))
                    unset($id_arr[$key]);
            }
        }
        if (!$id_arr) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/problem_sms');
            $this->error("参数错误！(1)");
        }
        $id_str = implode(',', $id_arr);

        $model = new Model();
        $data = array(
            'status' => 0,
        );
        $affect = $model->table('pu_problem_sms')->where("dev_id IN ({$id_str})")->save($data);
        if ($affect) {
            $this->writelog("删除了ID为" . $id_str . "的短信反馈问题",'pu_problem_sms',$id_str,__ACTION__,'','del');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/problem_sms');
            $this->success("删除成功！");
        } else {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/User/problem_sms');
            $this->error("数据更新失败，请重试！");
        }
    }

    //全局搜索页面显示所有开发者
    public function developer_list() {
        $model = new Model();
        require_once(realpath(dirname(__FILE__) . '/../../../../') . '/GoPHP/config/config.inc.php');
        if ($_GET) {
            if (empty($_GET['p'])) {
                //$this->map="p.email_verified = 1";
                $this->map = "p.status <= 1";
                if (!empty($_GET['username']) && $_GET['user_type'] == 1) {
                    //获取userid
                    $userid = $model->table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
                    $this->map.=" and p.dev_id='{$userid}'";
                } else if (!empty($_GET['username']) && $_GET['user_type'] == 2) {
                    $userinfo = $this->get_UserInfoByAccount($_GET['username'],'account');
                    $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                    $this->assign('user_type', $_GET['user_type']);
                } else {
                    if (!empty($_GET['dev_id'])) {
                        $this->map.=" and p.dev_id='{$_GET['dev_id']}'";
                    }

                    if (!empty($_GET['ip'])) {
                        $this->map.=" and p.reg_ip='{$_GET['ip']}'";
                    }
                    if (!empty($_GET['time'])) {
                        $stime = strtotime(date("Y-m-d 0:0:0", $_GET['time']));
                        $etime = $stime + (3600 * 24) - 1;
                        $this->map.=" and p.quest_time between '{$stime}' and '{$etime}'";
                    }
                    if (!empty($_GET['truename'])) {
                        $this->map.=" and p.truename like '%{$_GET['truename']}%'";
                    }
                    if (isset($_GET['type']) && $_GET['type'] != -1) {
                        $this->map.=" and p.type='{$_GET['type']}'";
                    }
                    
                    if (!empty($_GET['dev_name'])) {
                        $this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
                    }
                    if (!empty($_GET['cardnumber'])) {
                        $this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
                    }
                    if (!empty($_GET['company'])) {
                        $this->map.=" and p.company like '%{$_GET['company']}%'";
                    }
                    if (!empty($_GET['charter'])) {
                        $this->map.=" and p.charter like '%{$_GET['charter']}%'";
                    }
                    if (!empty($_GET['location'])) {
                        $this->map.=" and p.location='{$_GET['location']}'";
                    }
                    if (!empty($_GET['mobile'])&&empty($_GET['username'])&&empty($_GET['email'])) {
                        //$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
                        $userinfo = $this->get_UserInfoByAccount($_GET['mobile'],'account');
                        $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                    }
                    if (!empty($_GET['email'])&&empty($_GET['username'])) {
                        //$this->map.=" and p.email='{$_GET['email']}'";
                        $userinfo = $this->get_UserInfoByAccount($_GET['email'],'account');
                        $this->map.=" and p.dev_id='{$userinfo['data']['userList'][0]['pid']}'";
                    }
                    if (!empty($_GET['begintime'])) {
                        $begintime = strtotime($_GET['begintime']);
                        $this->map.=" and p.register_time>='{$begintime}'";
                    }
                    if (!empty($_GET['endtime'])) {
                        $endtime = strtotime($_GET['endtime']);
                        $this->map.=" and p.register_time<='{$endtime}'";
                    }
                }
                $_SESSION['admin']['developer_list']['where'] = $this->map;
            } else {
                $this->map = $_SESSION['admin']['developer_list']['where'];
            }
            $bo = true;
            if ($userinfo['data']['userList']) {
                if (!empty($_GET['mobile'])) {
                    if ($_GET['mobile'] != $userinfo['data']['userList'][0]['telephone']) {
                        $bo = false;
                    }
                }
                if (!empty($_GET['email'])) {
                    if (strtolower($_GET['email']) != strtolower($userinfo['data']['userList'][0]['email'])) {
                        $bo = false;
                    }
                }
            }
            if ($bo) {
				$this->map.=" and p.dev_type='0'";
                $this->users_db = D('Sj.Developer');
                import("@.ORG.Page2");
                $count = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
                //  var_dump($count);
                $Page = new Page($count, 10);
                $this->userslist = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.register_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                //echo $model->getlastsql();
                $devid = array();
                $devid_arr = array();
                if ($this->userslist) {
                    foreach ($this->userslist as $key => $val) {
                        unset($this->userslist[$key]['email']);
                        unset($this->userslist[$key]['mobile']);
                        $devid[] = $val['dev_id'];  //身份证
                        $devid_arr[] = $val['dev_id'];  //身份证
                        $devid_arr[] = 'a' . $val['dev_id']; //营业执照
                        if ($val['type'] == 0) {
                            $this->userslist[$key]['type_str'] = '公司';
                        } else if ($val['type'] == 1) {
                            $this->userslist[$key]['type_str'] = '个人';
                        } else if ($val['type'] == 2) {
                            $this->userslist[$key]['type_str'] = '团队';
                        }
                        $this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST . $val['cardpic'] : '';
                        $this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST . $val['charterpic'] : '';
						$this->userslist[$key]['handcardpic'] = $val['handcardpic'] ? IMGATT_HOST . $val['handcardpic'] : '';
                        if ($val['im_type'] == 1) {
                            $this->userslist[$key]['im_type_str'] = 'QQ';
                        } else if ($val['im_type'] == 2) {
                            $this->userslist[$key]['im_type_str'] = 'Gtalk';
                        } else if ($val['im_type'] == 3) {
                            $this->userslist[$key]['im_type_str'] = 'Msn';
                        } else if ($val['im_type'] == 4) {
                            $this->userslist[$key]['im_type_str'] = 'Skype';
                        }
                        $this->userslist[$key]['register_time'] = $val['register_time'] ? date('Y-m-d H:i:s', $val['register_time']) : '';
                        if (!empty($val['site'])) {
                            if (strstr($val['site'], 'http://')) {
                                $this->userslist[$key]['site'] = "<a target='_blank' href='{$val['site']}' >" . $val['site'] . "</a>";
                            } else {
                                $this->userslist[$key]['site'] = "<a target='_blank' href='http://{$val['site']}' >" . $val['site'] . "</a>";
                            }
                        }
                        if ($val['location'] == 1) {
                            $this->userslist[$key]['location_str'] = '中国大陆';
                        } else if ($val['location'] == 2) {
                            $this->userslist[$key]['location_str'] = '港澳台和国外';
                        }
                    }
                }
                $this->userslist = $this->get_info($devid, $this->userslist,1);
				//var_dump($this->userslist);
                $where = array(
                    'dev_id' => array('in', $devid),
                    'status' => 2,
                );
                $dev_save = get_table_data($where, "pu_developer_save", "dev_id", "dev_id,dev_name");
                $this->assign('dev_save', $dev_save);
                $devid_str = "'" . implode("','", $devid_arr) . "'";
                $Page->rollPage = 10;
                $Page->setConfig('header', '篇记录');
                $Page->setConfig('first', '首页');
                $Page->setConfig('last', '尾页');
                $show = $Page->show();
                $this->assign("page", $show);
                $this->assign("count", $count);
                $this->assign("list_type", $list_type);
                $this->assign('userslist', $this->userslist);
                $this->assign('devid_str', $devid_str);
            }
            
        }
        $this->display();
    }

    public function get_info($devid,$data,$appcert=''){
        if($appcert){
            $appcert_status = C('appcert_status');
            //app认证
            $app_info = get_table_data(array('dev_id'=>array('in',$devid)),"sj_appcert_user","dev_id","dev_id,status,msg");
        }
         $devid_str1 = implode(',', $devid);
            if ($devid_str1 != '') {
                $userinfo = $this->get_UserInfoByAccount($devid_str1);
            }
            
            foreach ($data as $l_key => $l_v) {
                foreach ($userinfo['data']['userList'] as $key => $val) {
                    if ($l_v['dev_id'] == $val['pid']) {
                        $data[$l_key]['email'] = $val['email'];
                        $email_verified_str = '';
                        // if ($val['emailValidStatus'] == "YES") {
                        //     $email_verified_str = '<span style="color:red;">[已验证]</span>';
                        // } else {
                        //     $email_verified_str = $val['email'] ? '[未验证]' : '';
                        // }
                        $data[$l_key]['email_verified_str'] = $email_verified_str;
                        $data[$l_key]['mobile'] = $val['telephone'];
                        $mobile_verified_str = '';
                        // if ($val['telephoneValidStatus'] == "YES") {
                        //     $mobile_verified_str = '<span style="color:red;">[已验证]</span>';
                        // } else {
                        //     $mobile_verified_str = $val['telephone'] ? '[未验证]' : '';
                        // }
                        $data[$l_key]['mobile_verified_str'] = $mobile_verified_str;
                        if ($l_v['user_name'] != '') {
                            if (!empty($val['loginName']) && $val['loginName'] != $l_v['user_name']) {
                                $data[$l_key]['loginName'] = $val['loginName'];
                            }
                        }
                }

                }
                if($app_info){
                    if($app_info[$data[$l_key]['dev_id']]['status']!='0'){
                        $data[$l_key]['appcert_status'] = $appcert_status[$app_info[$data[$l_key]['dev_id']]['status']];
                    }
                    if($app_info[$data[$l_key]['dev_id']]['status']==3){
                        $msg = json_decode($app_info[$data[$l_key]['dev_id']]['msg'],true);
                        $data[$l_key]['certinfo'] = $msg['certinfo'];
                    }else if($app_info[$data[$l_key]['dev_id']]['status']==4){
                        $data[$l_key]['certinfo'] = '提供的信息有误，无法进行认证工作';
                    }
                }
            }
            return $data;
    }
    
    //开发者管理_前台开发者替换包
    public function userapklists() {
        import('@.ORG.Page');
        $model = new Model();
        $where = "";
        $where.= "status=1 ";
        if (!empty($_POST['dev_id'])) {
            $dev_id = $_POST['dev_id'];
            $where.="  and dev_id = '{$dev_id}'";
            $this->assign('dev_id', $dev_id);
        }
        if (!empty($_POST['username'])) {
            $username = $_POST['username'];
            $where.="  and user_name = '{$username}'";
            $this->assign('username', $username);
        }
        if (!empty($_POST['email'])) {
            $email = $_POST['email'];
            $where.="  and email = '{$email}'";
            $this->assign('email', $email);
        }
        if (!empty($_POST['dev_name'])) {
            $dev_name = $_POST['dev_name'];
            $where.="  and dev_name like '%{$dev_name}%'";
            $this->assign('dev_name', $dev_name);
        }
        if (!empty($_POST['mobile'])) {
            $mobile = $_POST['mobile'];
            $where.="  and mobile = '{$mobile}'";
            $this->assign('mobile', $mobile);
        }
        if (!empty($_POST['location'])) {
            $location = $_POST['location'];
            $where.="  and location = '{$location}'";
            $this->assign('location', $location);
        }
        if (!empty($_POST['type'])) {
            $type = $_POST['type'];
            if ($type == 1) {
                $type = 0;
            }
            if ($type == 2) {
                $type = 1;
            }
            if ($type == 3) {
                $type = 2;
            }
            $where.="  and type = '{$type}'";
            $this->assign('type', $type);
        }
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 20;

        $total = $model->table('sj_userapk')->where($where)->count();
        $page = new Page($total, $limit);
        $list = $model->table('sj_userapk')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('add_time desc')->select();
        //echo $model->getLastSql();
        //var_dump($list);
        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('page', $page->show());
        $this->assign('count', $total);
        $this->assign('list', $list);
        $this->display();
    }

    public function getUserbydevid() {
        $dev_id = intval(trim($_POST['dev_id']));
        if ($dev_id) {
            $model = new Model();
            $dev_name = $model->table('pu_developer')->where("dev_id={$dev_id} and status=0")->getField('dev_name');
            if ($dev_name) {
                echo json_encode(array('code' => 1, 'msg' => '获取成功', 'rows' => $dev_name));
            } else {
                echo json_encode(array('code' => 0, 'msg' => '未找到开发者'));
            }
        } else {
            echo json_encode(array('code' => 0, 'msg' => '请输入开发者id'));
            exit();
        }
    }

    public function add_userapk() {
        //$dev_id = trim($_POST['get_dev_id']);
        //$dev_name =  trim($_POST['get_dev_name']);
        if (empty($_POST['get_dev_id'])) {
            $this->error('没有输入开发者ID');
        }
        $model = new Model();
        foreach ($_POST['get_dev_id'] as $key => $value) {
            $dev_id = $value;
            $is_dev_user = $model->table('sj_userapk')->where("dev_id ='{$dev_id}' and status=1")->select();
            if ($is_dev_user) {
                $this->error('此开发者已经存在列表中！');
                exit;
            }
            $developer = $model->table('pu_developer')->where("dev_id ='{$dev_id}' and status=0")->field('dev_id,dev_name,mobile,email,type,location')->select();
            $user = $model->table('pu_user')->where("userid ='{$dev_id}'  and status=1")->field('user_name')->select();
            foreach ($_POST['permission_type'] as $v) {
                $permission_type += $v;
            }
            if (empty($user[0]) || empty($developer[0])) {
                $this->error('开发者状态不正常');
            }
            $data = array_merge($developer[0], $user[0], array('permission_type' => $permission_type, 'add_time' => time(), 'status' => 1));
            $res = $model->table('sj_userapk')->data($data)->add();
        }
        if ($res) {
            $this->success('添加成功！');
        } else {
            $this->error('添加失败！');
        }
    }

    function userapklist_del() {
        $flag = true;
        $id = $_POST['id'];
        if (!isset($id)) {
            $this->error('ID不能为空');
        }
        $model = new Model();
        foreach (explode(',', $id) as $v) {
            $ret = $model->table('sj_userapk')->where("id = $v ")->field('status')->select();
            if ($ret[0]['status'] != 0) {
                $ret = $model->table('sj_userapk')->where("id = $v")->save(array('status' => 0, 'update_time' => time()));
                if (!$ret)
                    $flag = false;
                else
                    $this->writelog('删除了ID为' . $v . '开发者权限名单','sj_userapk',$v,__ACTION__,'','del');
            }
        }
        if ($flag == false) {
            $result = array('success' => false, 'msg' => '删除失败！');
            echo json_encode($result);
            exit();
        } else {
            $result = array('success' => false, 'msg' => '删除成功！');
            echo json_encode($result);
            exit();
        }
    }

    //获取文件
    function pub_getfile() {
        header('Content-Type: application/vnd.ms-excel');
        if (!empty($_GET['name'])) {
            header('Content-Disposition: attachment;filename="' . $_GET['name'] . '.csv"');
        } else {
            header('Content-Disposition: attachment;filename="out_export.csv"');
        }
        header('Cache-Control: max-age=0');
        $fid = $_GET['fid'];
        $file = '/tmp/export/' . session_id() . '_' . $fid . 'export' . ".csv";
        if (!file_exists($file)) {
            exit;
        }
        $fp = fopen($file, 'r');
        $out_fp = fopen('php://output', 'a');
        while (!feof($fp)) {
            fputs($out_fp, fgets($fp));
        }
        fclose($fp);
        fclose($out_fp);
        exit;
    }

    //开发者管理导出	
    function developer_export() {
        $Export = D("Dev.Export");
        //分页		
        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 1000;
        $data = $Export->get_developer($_GET, $limit, $p);
        exit(json_encode($data));
    }

    //开启--关闭评论模块
    function is_comment_model() {
        $model = new Model();
        $id_arr = explode(',', $_GET['id']);
        $type = $_GET['type'];
        if (!$id_arr) {
            $this->error('ID不可为空');
        }
        $where = array(
            'dev_id' => array('in', $id_arr),
            'status' => 0
        );
        $map = array(
            'is_comment' => $type,
            'last_time' => time()
        );
        $ret = $model->table('pu_developer')->where($where)->save($map);
        if ($ret) {
            if ($type == 1) {
                $log = "开启";
            } else {
                $log = "关闭";
            }
            $this->writelog("开发者id为{$_GET['id']}的评论回复模块{$log}",'pu_developer',$_GET['id'],__ACTION__,'','edit');
            $this->success('设置成功！');
        } else {
            $this->error('设置失败！');
        }
    }

    //开启-关闭游戏出版编号状态
    function pub_change_status(){
        $model = new Model();
        $id_arr = explode(',', $_GET['id']);
        $type = $_GET['type'];
        if (!$id_arr) {
            $this->error('ID不可为空');
        }
        $where = array(
            'dev_id' => array('in', $id_arr),
            'status' => 0
        );
        $map = array(
            'publication_status' => $type,
            'last_time' => time()
        );
        $ret = $model->table('pu_developer')->where($where)->save($map);
        if ($ret) {
            if ($type == 1) {
                $log = "开启";
            } else {
                $log = "关闭";
            }
            $this->writelog("开发者id为{$_GET['id']}的游戏出版编号状态{$log}",'pu_developer',$_GET['id'],__ACTION__,'','edit');
            $this->success('设置成功！');
        } else {
            $this->error('设置失败！');
        }
    }
    //开发者管理--信息修改审核列表
    function save_dev_data() {
        $model = new Model();
        $where = array(
            'A.status' => 2,
            'B.status' => 0,
            //'A.edit_num' => array('exp', '<=3')
        );
        if ($_GET) {
            if (!empty($_GET['dev_id'])) {
                $where['A.dev_id'] = $_GET['dev_id'];
                $this->assign('dev_id', $_GET['dev_id']);
            }
            if (!empty($_GET['dev_name'])) {
                $where['B.dev_name'] = array('like', "%{$_GET['dev_name']}%");
                $this->assign('dev_name', $_GET['dev_name']);
            }
            if (!empty($_GET['edit_dev_name'])) {
                $where['A.dev_name'] = array('like', "%{$_GET['edit_dev_name']}%");
                $this->assign('edit_dev_name', $_GET['edit_dev_name']);
            }
            
            if (!empty($_GET['location'])) {
                $where['B.location'] = $_GET['location'];
                $this->assign('location', $_GET['location']);
            }
            if (!empty($_GET['mobile'])&&empty($_GET['email'])&&empty($_GET['user_name'])) {
                //$where['B.mobile'] = $_GET['mobile'];
                $userinfo = $this->get_UserInfoByAccount($_GET['mobile'],'account');
                $where['A.dev_id'] = $userinfo['data']['userList'][0]['pid'];
                $this->assign('mobile', $_GET['mobile']);
            }
            if (!empty($_GET['email'])&&empty($_GET['user_name'])) {
                //$where['B.email'] = $_GET['email'];
                $userinfo = $this->get_UserInfoByAccount($_GET['email'],'account');
                $where['A.dev_id'] = $userinfo['data']['userList'][0]['pid'];
                $this->assign('email', $_GET['email']);
            }
            if (!empty($_GET['user_name']) && $_GET['user_type'] == 1) {
                $subQuery = $model->table('pu_user')->field('userid')->where("user_name='{$_GET['user_name']}'")->find();
                $where['A.dev_id'] = $subQuery['userid'];
                $this->assign('user_name', $_GET['user_name']);
            } else if (!empty($_GET['user_name']) && $_GET['user_type'] == 2) {
                $userinfo = $this->get_UserInfoByAccount($_GET['user_name'],'account');
                $where['A.dev_id'] = $userinfo['data']['userList'][0]['pid'];
                $this->assign('user_type', $_GET['user_type']);
            }
            if (isset($_GET['type']) && $_GET['type'] != -1) {
                $where['B.type'] = $_GET['type'];
                $this->assign('type', $_GET['type']);
            }
            if (!empty($_GET['begintime'])) {
                $begintime = strtotime($_GET['begintime']);
                $this->map.=" and p.register_time>='{$begintime}'";
            }
            if (!empty($_GET['endtime'])) {
                $endtime = strtotime($_GET['endtime']);
                $this->map.=" and p.register_time<='{$endtime}'";
            }
            if (!empty($params['begintime']) && !empty($params['endtime'])) {
                $begintime = strtotime($params['begintime']);
                $endtime = strtotime($params['endtime']);
                $where['A.add_tm'] = array(array("egt", $begintime), array("elt", $endtime));
            }
			if (isset($_GET['edit_type']) && $_GET['edit_type'] != -1) {
                $edit_type = $_GET['edit_type'];	
				$where['A.type'] = $edit_type;
            }
			if (!empty($_GET['company'])) {
                $company = $_GET['company'];
				$where['A.company'] =  array('like', "%{$_GET['company']}%");
            }  
			if (!empty($_GET['charter'])) {
                $charter = $_GET['charter'];
				$where['A.charter'] =  array('like', "%{$_GET['charter']}%");
            }
			if (!empty($_GET['truename'])) {
                $truename = $_GET['truename'];
				$where['A.truename'] =  array('like', "%{$_GET['truename']}%");
            }
			if (!empty($_GET['cardnumber'])) {
                $cardnumber = $_GET['cardnumber'];
				$where['A.cardnumber'] =  array('like', "%{$_GET['cardnumber']}%");
            }
        }
        $bo = true;
        if($userinfo['data']['userList']){
            if(!empty($_GET['mobile'])){
                if($_GET['mobile']!=$userinfo['data']['userList'][0]['telephone']){
                    $bo  = false;
                }
            }
            if(!empty($_GET['email'])){
                if(strtolower($_GET['email'])!=strtolower($userinfo['data']['userList'][0]['email'])){
                    $bo  = false;
                }
            }
        }
        if ($bo) {
            $total = $model->table('pu_developer_save A')->join("pu_developer B ON A.dev_id = B.dev_id")->where($where)->count();
            import('@.ORG.Page2');
            //分页		
            $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
            $param = http_build_query($_GET);
            $Page = new Page($total, $limit, $param);
            $Page->rollPage = 10;
            $Page->setConfig('header', '篇记录');
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $order_str = "A.update_tm asc";
            $list = $model->table('pu_developer_save A')->join("pu_developer B ON A.dev_id = B.dev_id")->where($where)->field('A.dev_id,A.dev_name as edit_dev_name,A.edit_reject,A.add_tm,A.update_tm,A.reject_tm,A.last_status,A.type as edit_type,A.company,A.charter,A.charterpic,A.truename,A.cardpic,A.handcardpic,A.cardnumber,A.licence_num,A.licence_url,A.news_license_url,A.program_license_url,B.dev_name,B.type,B.location,B.company as old_company,B.charter as old_charter,B.charterpic as old_charterpic,B.truename as old_truename,B.cardnumber as old_cardnumber,B.cardpic as old_cardpic,B.handcardpic as old_handcardpic,B.licence_num as old_licence_num,B.licence_url as old_licence_url,B.news_license_url as old_news_license_url,B.program_license_url as old_program_license_url,A.bankcard,A.op_company,A.op_address,A.op_tel,A.op_name,B.bankcard as old_bankcard,B.op_company as old_op_company,B.op_address as old_op_address,B.op_tel as old_op_tel,B.op_name as old_op_name')->limit($Page->firstRow . ',' . $Page->listRows)->order($order_str)->select();
//            echo $model->getlastsql();
            $devid = array();
            foreach ($list as $k => $v) {
                $type_str = '';
                if ($v['type'] == 1) {
                    $type_str = '个人';
                } else {
                    $type_str = '公司';
                }
                $list[$k]['type_str'] = $type_str;
                $location_str = '';
                if ($v['location'] == 1) {
                    $location_str = '大陆';
                } else {
                    $location_str = '港澳台和国外';
                }
                $list[$k]['location_str'] = $location_str;
                $list[$k]['add_tm'] = $v['add_tm'] ? date("Y-m-d H:i:s", $v['add_tm']) : '';
                $devid[] = $v['dev_id'];
            }
			$where = array('del'=>0,'dev_id'=>array('in',$devid));
			$dev_settlement = get_table_data($where, "pu_developer_settlement", "dev_id", "dev_id,company_legal,company_address,info_json");
			foreach($dev_settlement as $k=>$v){
				$old_info = json_decode($v['info_json'],true);
				$dev_settlement[$k]['old_company_legal'] = $old_info['company_legal'];
				$dev_settlement[$k]['old_company_address'] = $old_info['company_address'];
			}
			$this->assign('dev_settlement', $dev_settlement);
            $list = $this->get_info($devid, $list,1);

            if ($devid) {
                $where = array(
                    'userid' => array('in', $devid),
                    'status' => 1,
                );
                $user_name_arr = get_table_data($where, "pu_user", "userid", "userid,user_name");
            }
            $this->assign('page', $Page->show());
            $this->assign('total', $total);

            $this->assign('list', $list);
            $this->assign('user_name_arr', $user_name_arr);
            $reason_list = $model->table("dev_reason")->where(array("status" => 1, "reason_type" => 1))->order('pos asc,id desc')->select();
            foreach ($reason_list as $key => $val) {
                if ($val['content2']) {
                    $reason_list[$key]['content2'] = explode('<br />', $val['content2']);
                }
            }
            $this->assign('reason_list', $reason_list);
        }
		$this->assign('img_host',IMGATT_HOST);
        $this->display();
    }
	
    function get_UserInfoByAccount($v,$type='pid') {
        import("@.ORG.GoDes");
        $url = C('online_query_user');
        $vals = array();
        $vals['serviceId'] = '013';
        $vals['serviceVersion'] = '1.0';
        $vals['serviceType'] = '1';
        $privatekey = C('online_private_key');
        $des = new GoDes($privatekey);
        $vals['data'][$type] = $v;
        $temp_data = $des->encrypt(json_encode($vals['data']));
        $data = base64_encode($temp_data);
        $vals['data'] = $data;
        $res = httpGetInfo($url, $vals);       
        $userinfo = json_decode($res, true);
        return $userinfo;
    }

    //开发者管理--信息修改审核列表--通过操作
    function save_dev_data_pass() {
        $model = new Model();
        $id_arr = explode(',', $_POST['id']);
        if (!$id_arr)
            exit(json_encode(array('code' => '0', 'msg' => '请选择要操作的对象！')));
        $where = array(
            'dev_id' => array('in', $id_arr)
        );
        $list = $model->table('pu_developer_save')->where($where)->select();
        $dev_info = get_table_data($where, "pu_developer", "dev_id", "dev_id,email,dev_name,type,company,charter,charterpic,truename,cardnumber,cardpic,handcardpic,location,licence_num,licence_url,news_license_url,program_license_url,bankcard,op_company,op_address,op_tel,op_name");
		$dev_settlement = get_table_data($where, "pu_developer_settlement", "dev_id", "dev_id,company_legal,company_address,info_json");
        $dev_id_error = '';
        $dev_id = array();
        $time = time();
        $data = array();
        foreach ($list as $v) {
            $map = array(
                //'dev_name' => $v['dev_name'],
				'pass_time' => $time,
                'last_time' => $time,
                'licence_url' => $v['licence_url'],
                'news_license_url' => $v['news_license_url'],
                'program_license_url' => $v['program_license_url'],
                'bankcard' => $v['bankcard'],
                'op_company' => $v['op_company'],
                'op_address' => $v['op_address'],
                'op_tel' => $v['op_tel'],
                'op_name' => $v['op_name']
            );
			if(!empty($v['dev_name'])) $map['dev_name'] = $v['dev_name'];
			$info_arr = array('company','charter','charterpic','truename','cardnumber','cardpic','handcardpic','location','licence_num');
			if(!is_null($v['type'])){
				$map['type'] = $v['type'];
			}
			foreach($info_arr as $i_k=>$i_v){
				if(!empty($v[$i_v])){
					$map[$i_v] = $v[$i_v];
				}
			}
            $res = $model->table('pu_developer')->where("dev_id='{$v['dev_id']}'")->save($map);
            $map = array(
                'dev_name' => $v['dev_name'],
            );
			$model->table('sj_soft')->where("dev_id='{$v['dev_id']}'")->save($map);
			$model->table('sj_soft_whitelist')->where("dev_id='{$v['dev_id']}'")->save($map);
            if (!$res) {
                $dev_id_error .= $v['dev_id'] . "\n";
            } else {
                $dev_id[] = $v['dev_id'];
                $map = array(
                    'update_tm' => time(),
                    'status' => 1,
                    'last_status' => 1,
					'last_type'=>$dev_info[$v['dev_id']]['type'],
					'last_company'=>$dev_info[$v['dev_id']]['company'],
					'last_charter'=>$dev_info[$v['dev_id']]['charter'],
					'last_charterpic'=>$dev_info[$v['dev_id']]['charterpic'],
					'last_truename'=>$dev_info[$v['dev_id']]['truename'],
					'last_cardnumber'=>$dev_info[$v['dev_id']]['cardnumber'],
					'last_cardpic'=>$dev_info[$v['dev_id']]['cardpic'],
					'last_handcardpic'=>$dev_info[$v['dev_id']]['handcardpic'],
					'last_location'=>$dev_info[$v['dev_id']]['location'],
                    'last_licence_num' => $dev_info[$v['dev_id']]['licence_num'],
                    'last_licence_url' => $dev_info[$v['dev_id']]['licence_url']
                );
                $last_json = json_encode(array(
                    'bankcard' => $dev_info[$v['dev_id']]['bankcard'],
                    'op_company' => $dev_info[$v['dev_id']]['op_company'],
                    'op_address' => $dev_info[$v['dev_id']]['op_address'],
                    'op_tel' => $dev_info[$v['dev_id']]['op_tel'],
                    'op_name' => $dev_info[$v['dev_id']]['op_name'],
                ));
                $map['last_info'] = $last_json;
				if(!empty($v['dev_name'])&&$dev_info[$v['dev_id']]['dev_name']!=$v['dev_name']){
					$map['edit_num'] = $v['edit_num'] + 1;
					$map['last_dev_name'] = $dev_info[$v['dev_id']]['dev_name'];
					$this->writelog("把devid为{$v['dev_id']}开发者昵称改为{$v['dev_name']}",'pu_developer',$v['dev_id'],__ACTION__,'','edit');
				}
                $model->table('pu_developer_save')->where("dev_id='{$v['dev_id']}'")->save($map);
                $save_settlement_json = json_encode(array('company_legal'=>$dev_settlement[$v['dev_id']]['company_legal'],'company_address'=>$dev_settlement[$v['dev_id']]['company_address']));
				$save_settlement_info = array('info_json'=>$save_settlement_json,'last_info_json'=>$dev_settlement[$v['dev_id']]['info_json'],'up_tm'=>time());
				$save_settlement = $model->table('pu_developer_settlement')->where("dev_id='{$v['dev_id']}'")->save($save_settlement_info);
                $data[$v['dev_id']] = $v['dev_name'];
            }
        }
        if ($dev_id) {
            $str_dev_ids=implode(',', $dev_id);
            $this->writelog("把devid为{$str_dev_ids}信息修改审核通过",'pu_developer_save',$str_dev_ids,__ACTION__,'','edit');
            //发送邮件提醒
            if ($data) {
                $tm = date("Y-m-d", time());
                $emailmodel = D("Dev.Sendemail");
                $config_txt = C('_config_txt_');
                foreach ($data as $k => $v) {
                    $search = array("dev_name", "tm");
                    $replace = array($v, $tm);
                    $msg = str_replace($search, $replace, $config_txt['save_data_pass']);
                    //发送邮件提醒
                    $subject = $config_txt['save_data_subject'];
                    $pass_txt = $config_txt['save_data_pass_txt'];
                    $emailmodel->dev_remind_add($k, $msg);
                    $search2 = array("devname", "tm");
                    $replace2 = array($v, $tm);
                    $email_cont = str_replace($search2, $replace2, $pass_txt);
                    $emailmodel->realsend($dev_info[$k]['email'], $v, $subject, $email_cont);
                }
            }
        }
        if ($dev_id_error)
            exit(json_encode(array('code' => '0', 'msg' => 'id为' . $dev_id_error . '修改失败')));
        exit(json_encode(array('code' => '1', 'msg' => $id_arr)));
    }

    //开发者管理--信息修改审核列表--驳回操作
    function save_dev_data_reject() {
        $model = new Model();
        $id_arr = explode(',', $_POST['id']);
        if (!$id_arr)
            exit(json_encode(array('code' => '0', 'msg' => '请选择要操作的对象！')));
        $time = time();
        $where = array(
            'dev_id' => array('in', $id_arr),
            'status' => 2
        );
        $data = array(
            //'edit_num'=>array('exp',"`edit_num`+1"),
            'edit_reject' => $_POST['msg'],
            'reject_tm' => $time,
            'status' => 3,
            'last_status' => 3
        );
        $ret = $model->table('pu_developer_save')->where($where)->save($data);
        if ($ret) {
            $where_p = array(
                'dev_id' => array('in', $id_arr),
            );
            $map = array(
                'save_status' => 1,
            );
            $dev_save = get_table_data($where_p, "pu_developer_save", "dev_id", "dev_id,dev_name");
            $model->table('pu_developer')->where($where_p)->save($map);
            foreach($id_arr as $k=>$v){
                $this->writelog("驳回了dev_id为{$v}的信息修改，驳回原因为:{$_POST['msg']}",'pu_developer',$v,__ACTION__,'','edit');
            }
            
            //发送提醒--邮件
            if ($dev_save) {
                $dev_info = get_table_data($where_p, "pu_developer", "dev_id", "dev_id,email,dev_name");

                $tm = date("Y-m-d", $time);
                $emailmodel = D("Dev.Sendemail");
                $config_txt = C('_config_txt_');
                foreach ($dev_save as $k => $v) {
                    $search = array("dev_name", "tm");
                    $replace = array($v['dev_name'], $tm);
                    $msg = str_replace($search, $replace, $config_txt['save_data_reject']);

                    //发送邮件提醒
                    $subject = $config_txt['save_data_subject'];
                    $pass_txt = $config_txt['save_data_reject_txt'];
                    $emailmodel->dev_remind_add($k, $msg);
                    $search2 = array("devname", 'dev_name', "tm", "msg");
                    $replace2 = array($dev_info[$k]['dev_name'], $v['dev_name'], $tm, $_POST['msg']);
                    $email_cont = str_replace($search2, $replace2, $pass_txt);
                    $emailmodel->realsend($dev_info[$k]['email'], $dev_info[$k]['dev_name'], $subject, $email_cont);
                }
            }
            exit(json_encode(array('code' => '1', 'msg' => $id_arr)));
        } else {
            exit(json_encode(array('code' => '0', 'msg' => '驳回失败')));
        }
    }

    //开发者通过列表--撤销到信息修改操作
    function revoke_dev_data() {
        $model = new Model();
        $id = $_GET['uid'];
        if (!$id)
            exit(json_encode(array('code' => '0', 'msg' => '请选择操作对像')));
        $list = $model->table('pu_developer_save')->where("dev_id='{$id}' and status=1")->find();
		$dev_settlement = $model->table('pu_developer_settlement')->where("dev_id='{$id}' and del=0")->field('last_info_json')->find();
		$map = array();
		if(!empty($list['dev_name'])&&$list['last_dev_name']!=$list['dev_name']) $map['dev_name'] = $list['last_dev_name'];
		if(!empty($list['type'])&&$list['last_type']!=$list['type']) $map['type'] = $list['last_type'];
		if(!empty($list['company'])&&$list['last_company']!=$list['company']) $map['company'] = $list['last_company'];
		if(!empty($list['charter'])&&$list['last_charter']!=$list['charter']) $map['charter'] = $list['last_charter'];
		if(!empty($list['charterpic'])&&$list['last_charterpic']!=$list['charterpic']) $map['charterpic'] = $list['last_charterpic'];
		if(!empty($list['truename'])&&$list['last_truename']!=$list['truename']) $map['truename'] = $list['last_truename'];
		if(!empty($list['cardnumber'])&&$list['last_cardnumber']!=$list['cardnumber']) $map['cardnumber'] = $list['last_cardnumber'];
		if(!empty($list['cardpic'])&&$list['last_cardpic']!=$list['cardpic']) $map['cardpic'] = $list['last_cardpic'];
		if(!empty($list['handcardpic'])&&$list['last_handcardpic']!=$list['handcardpic']) $map['handcardpic'] = $list['last_handcardpic'];
		if(!empty($list['location'])&&$list['last_location']!=$list['location']) $map['location'] = $list['last_location'];
        if(!empty($list['licence_num'])&&$list['last_licence_num']!=$list['licence_num']) $map['licence_num'] = $list['last_licence_num'];
        if(!empty($list['licence_url'])&&$list['last_licence_url']!=$list['licence_url']) $map['licence_url'] = $list['last_licence_url'];
        if(!empty($list['news_license_url'])&&$list['last_news_license_url']!=$list['news_license_url']) $map['news_license_url'] = $list['last_news_license_url'];
        if(!empty($list['program_license_url'])&&$list['last_program_license_url']!=$list['program_license_url']) $map['program_license_url'] = $list['last_program_license_url'];
        $last_info = json_decode($list['last_info'],true);
        foreach($last_info as $k=>$v){
            if($list[$k]!=$v) $map[$k] = $v;
        }
		$map['last_time'] = time();
        $res = $model->table('pu_developer')->where("dev_id='{$id}' and status=0")->save($map);
		
		$settlement_res = $model->table('pu_developer_settlement')->where("dev_id='{$id}' and del = 0")->save(array('info_json'=>$dev_settlement['last_info_json'],'up_tm'=>time()));
		if (!$res)
            exit(json_encode(array('code' => '0', 'msg' => '撤销失败')));
		//判断信息修改有修改昵称
		$save_data = array();
		if(!empty($list['dev_name'])&&$list['last_dev_name']!=$list['dev_name']){
			$map = array(
				'dev_name' => $list['last_dev_name'],
			);		
			$model->table('sj_soft')->where("dev_id='{$id}'")->save($map);
			$model->table('sj_soft_whitelist')->where("dev_id='{$id}'")->save($map);
			$save_data['edit_num'] = $list['edit_num'] - 1;
		}
        $save_data['status'] = 2;
        

        $ret = $model->table('pu_developer_save')->where("dev_id='{$id}' and status=1")->save($save_data);
        if ($ret) {
            $this->writelog("撤销了dev_id为{$id}到信息修改列表",'pu_developer_save',$id,__ACTION__,'','edit');
            exit(json_encode(array('code' => '1')));
        } else {
            exit(json_encode(array('code' => '0', 'msg' => '撤销失败')));
        }
    }

    //开发者未通过列表--删除操作（删除信息修改）
    function save_data_del() {
        $model = new Model();
        $this->assign('jumpUrl', $_SERVER['HTTP_REFERER']);
        $id = $_GET['uid'];
        if (!$id)
            $this->error('请选择操作对像');
        $map = array(
            'update_tm' => time(),
            'status' => 0
        );
        $res = $model->table('pu_developer_save')->where("dev_id='{$id}'")->save($map);
        if ($res) {
            $this->writelog("删除了dev_id为{$id}的信息修改。备注：{$_GET['beizhu']}",'pu_developer_save',$id,__ACTION__,'','del');
            $this->success('操作成功！');
        } else {
            $this->error('操作失败');
        }
    }

}

?>