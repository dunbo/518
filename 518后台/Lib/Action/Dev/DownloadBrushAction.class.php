<?php

/**
 * 安智网产品管理平台 下载刷量管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：wwt
 * date:2012-07-27
 * ----------------------------------------------------------------------------
 */
class DownloadBrushAction extends CommonAction {

    public function brush_types($type) {
        switch ($type) {
            case '1':
                return 'www下载刷量:';
                break;
            case '2':
                return 'M.anzhi.com下载刷量:';
                break;
            case '3':
                return 'IP下载刷量:';
                break;
            case '4':
                return '设备下载刷量:';
                break;
            case '0':
                return '下载量管理:';
                break;
        }
    }

    //设备下载刷量
    function device_download_brush() {
        $model = new Model();
        $where = ' `status` = 1 ';
        $where1 = 'a.status = 1 ';
        import('@.ORG.Page');
        $param = http_build_query($_REQUEST);
        $limit = 50;
        if (isset($_REQUEST['lr'])) {
            $this->assign('lr', (int) $_REQUEST['lr']);
        } else {
            $this->assign('lr', $limit);
        }
        if (isset($_REQUEST['p'])) {
            $this->assign('p', (int) $_REQUEST['p']);
        } else {
            $this->assign('p', 1);
        }
        if (isset($_REQUEST['softname']) && isset($_REQUEST['package'])) {
            $where .= ' AND `package` = "' . (string) $_REQUEST['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_REQUEST['package'] . '"';
            $this->assign('package', (string) $_REQUEST['package']);
        } else if (isset($_REQUEST['package'])) {
            $where .= ' AND `package` = "' . (string) $_REQUEST['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_REQUEST['package'] . '"';
            $this->assign('package', (string) $_REQUEST['package']);
            $this->assign('softname', '');
        } else if (isset($_REQUEST['softname'])) {
            $condition['`softname`'] = array('like', '%' . $_REQUEST['softname'] . '%');
            $soft_lists = $model->table('sj_soft')->where($condition)->field('package')->select();
            $packages = '';
            foreach ($soft_lists as $soft_list) {
                $packages .= ",'" . $soft_list['package'] . "'";
            }
            if ($packages[0] == ',') {
                $packages = substr($packages, 1);
            }
            $where .= " AND `package` in ({$packages}) ";
            $where1 .= " AND a.package in ({$packages}) ";
            $this->assign('softname', (string) $_REQUEST['softname']);
            $this->assign('package', '');
        } else {
            $this->assign('package', '');
            $this->assign('softname', '');
        }
        if (isset($_REQUEST['start_time'])) {
            $start_time = (string) $_REQUEST['start_time'];
            $where .= " AND `brush_time` >= '" . strtotime($start_time) . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime($start_time) . "'";
            $this->assign('start_time', $start_time);
        } else {
            $where .= " AND `brush_time` >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time()))) . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time()))) . "'";
            $this->assign('start_time', date('Y-m-d 00:00:00', strtotime('-7 days', time())));
        }
        if (isset($_REQUEST['end_time'])) {
            $end_time = (string) $_REQUEST['end_time'];
            $where .= " AND `brush_time` <= '" . strtotime($end_time) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime($end_time) . "'";
            $this->assign('end_time', $end_time);
        } else {
            $where .= " AND `brush_time` <= '" . strtotime(date('Y-m-d H:i:s')) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime(date('Y-m-d H:i:s')) . "'";
            $this->assign('end_time', date('Y-m-d 23:59:59'));
        }
        $zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM', 'status' => 1);
        $sum_info = $model->table('pu_config')->where($zonghe)->field('configcontent')->select();
        $baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT', 'status' => 1);
        $percent_info = $model->table('pu_config')->where($baifen)->field('configcontent')->select();
        //echo $model->getlastsql();
        if (isset($_REQUEST['exceed_sum'])) {
            $where .= " AND `exceed_sum` >= '" . intval($_REQUEST['exceed_sum']) . "'";
            $where1 .= " AND a.`exceed_sum` >= '" . intval($_REQUEST['exceed_sum']) . "'";
            $this->assign('exceed_sum', intval($_REQUEST['exceed_sum']));
        } else {
            //$this -> assign('exceed_sum',$sum_info[0]['configcontent']);
        }

        if (isset($_REQUEST['percent'])) {
            $percent = floatval(intval($_REQUEST['percent']) / 100);
            $where .= " AND `percent` >= '" . $percent . "'";
            $where1 .= " AND a.percent >= '" . $percent . "'";
            $this->assign('percent', intval($_REQUEST['percent']));
        } else {
            //$this -> assign('percent',$percent_info[0]['configcontent']);
        }
        if ($_GET['white_list'] == '0') {
            $where .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
            $where1 .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
        }
        $developwhere = '';
        if (isset($_GET['dev_name']) && $_GET['dev_name'] != '') {
            $developwhere .= 'c.dev_name like "%' . $_GET['dev_name'] . '%"';
            $this->assign('dev_name', $_GET['dev_name']);
        }
        if (isset($_GET['email']) && $_GET['email'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'c.email = "' . $_GET['email'] . '"';
            } else {
                $developwhere .= ' and c.email = "' . $_GET['email'] . '"';
            }
            $this->assign('email', $_GET['email']);
        }
        $type = array('0' => '公司', '1' => '个人');
        if (isset($_GET['dev_type']) && $_GET['dev_type'] != 3) {
            if ($developwhere == '') {
                $developwhere .= 'c.type = "' . $_GET['dev_type'] . '"';
            } else {
                $developwhere .= ' and c.type = "' . $_GET['dev_type'] . '"';
            }
            $this->assign('dev_type', $_GET['dev_type']);
        } else {
            $this->assign('dev_type', 3);
        }
        if (isset($_GET['dev_id']) && $_GET['dev_id'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'b.dev_id = "' . $_GET['dev_id'] . '"';
            } else {
                $developwhere .= ' and b.dev_id = "' . $_GET['dev_id'] . '"';
            }
            $this->assign('dev_id', $_GET['dev_id']);
        }
        $table = 'sj_device_download_brush a';
        if ($developwhere == "") {
            $count_total = $model->table('sj_device_download_brush')->where($where)->count();
        } else {
            $developwhere .= " and " . $where1;
            $res = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->field('a.brush_id')->select();
            $count_total = count($res);
        }

        $page = new Page($count_total, $limit, $param);

        $order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
        $order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;
        if ($developwhere == '') {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table('sj_device_download_brush')->where($where)->order('package ASC,brush_time ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table('sj_device_download_brush')->where($where)->order('package DESC,brush_time DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table('sj_device_download_brush')->where($where)->order('brush_time ASC,package ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table('sj_device_download_brush')->where($where)->order('brush_time DESC,package AESC')->limit($page->firstRow . ',' . $page->listRows)->select();
            }
        } else {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package ASC,a.brush_time ASC')->field('a.*')->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package DESC,a.brush_time DESC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time ASC,a.package ASC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time DESC,a.package DESC')->field('a.*')->select();
            }
        }

        //查询操作日志表
        $brush_log = $model->table('sj_brush_log')->order('oper_time DESC')->select();
        $brush_log_arr = array();
        foreach ($brush_log as $key => $value) {
            $oper_time = $value['oper_time'] ? date('Y-m-d', $value['oper_time']) . '<br />' . date('H:i:s', $value['oper_time']) : '';
            $brush_log_arr[$value['package']][] = array('oper_time' => $oper_time, 'reason' => $value['reason'], 'oper_type' => $value['type'],'add_data'=>$value['add_data'],'cut_data'=>$value['cut_data'],'is_system'=>$value['is_system']);
        }
        for ($i = 0; $i < count($brush_list); $i++) {
            $map['`package`'] = $brush_list[$i]['package'];
            $soft_info = $model->table('sj_soft')->where($map)->field('dev_id,dev_name,dever_email,softname,hide')->order('softid DESC')->limit(1)->select();
            $dev_id = $soft_info[0]['dev_id'];
            $develop_info = $model->table('pu_developer')->where(array('dev_id' => $dev_id))->field('email,dev_name,type,status')->select();
            $brush_list[$i]['softname'] = $soft_info[0]['softname'];
            $brush_list[$i]['hide'] = $soft_info[0]['hide'];
            $brush_list[$i]['dev_name'] = $develop_info[0]['dev_name'];
            $brush_list[$i]['email'] = $develop_info[0]['email'];
            $brush_list[$i]['dev_id'] = $soft_info[0]['dev_id'];
            $brush_list[$i]['status'] = $soft_info[0]['status'];
			if($brush_log_arr[$brush_list[$i]['package']][0]['add_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['add_data'];
				$brush_list[$i]['brush_type'] = '增量';
			}else if($brush_log_arr[$brush_list[$i]['package']][0]['cut_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['cut_data'];
				$brush_list[$i]['brush_type'] = '扣量';
			}
            $develop_typearr = array('0' => '公司', '1' => '个人');
            $brush_list[$i]['dev_type'] = $develop_typearr[$develop_info[0]['type']];
            if (empty($brush_list[$i]['softname'])) {
                $brush_list[$i]['softname'] = '0';
            }
            //操作记录
            $log_time = $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] ? $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] : '';
            $brush_list[$i]['log_time'] = $log_time;
            $brush_list[$i]['reason'] = $this->brush_types($brush_log_arr[$brush_list[$i]['package']][0]['oper_type']) . '<br />' . str_replace(',', '<br />', $brush_log_arr[$brush_list[$i]['package']][0]['reason']);
			if($brush_log_arr[$brush_list[$i]['package']][0]['is_system']==1){
				$brush_list[$i]['reason'] = '系统自动刷量:';
			}
            $brush_list[$i]['percent'] = substr($brush_list[$i]['percent'] * 100, 0, 5) . "%";
            $mingdan = array('`package`' => $brush_list[$i]['package'], '`status`' => 1);
            $white_data = $model->table('sj_brush_adapter')->where($mingdan)->limit(1)->select();
            $white_count = count($white_data);
            if ($white_count > 0) {
                $brush_list[$i]['white'] = 1;
                $brush_list[$i]['note'] = $white_data[0]['note'];
            } else {
                $brush_list[$i]['white'] = 0;
                $brush_list[$i]['note'] = '';
            }
            //获取历史增量值与扣量值
            $soft = $model->table('sj_soft')->field('total_downloaded_add ,total_downloaded_detain,total_downloaded as downloaded ')->where("package = '{$brush_list[$i]['package']}' and status=1")->order('softid DESC')->limit(1)->select();
            $brush_list[$i]['his_add_data'] = $soft[0]['total_downloaded_add'] ? $soft[0]['total_downloaded_add'] : 0;
            $brush_list[$i]['his_cut_data'] = $soft[0]['total_downloaded_detain'] ? $soft[0]['total_downloaded_detain'] : 0;
            $brush_list[$i]['soft_total_data'] = $soft[0]['downloaded'] ? $soft[0]['downloaded'] : 0;
        }

        //是否显示白名单
        $white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;

        foreach ($brush_list as $key => $val) {
            if ($white_list == 1) {
                $brush_list_all = $brush_list;
            } elseif ($white_list == 0) {
                if ($val['white'] == 0) {
                    $brush_list_all[] = $val;
                }
            }
        }
        $action_id = $model->table('sj_admin_node')->where("nodename='/index.php/Dev/DownloadBrush/brush_oper'")->getField('node_id');
        //echo $model->getlastsql();
        $this->assign('action_id', $action_id);
        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('white_list', $white_list);
        $this->assign('page', $page->show());
        $this->assign('order_go', $order_go);
        $this->assign('order_rule', $order_rule);
        $this->assign('status', 4);
        $this->assign('count', $count_total);
        $this->assign('brush_list', $brush_list_all);
        $this->display();
    }

    //IP下载刷量
    function ip_download_brush() {
        ini_set('memory_limit', '256M');
        import('@.ORG.Page');
        $param = http_build_query($_GET);
        $limit = 50;
        if (isset($_GET['lr'])) {
            $this->assign('lr', (int) $_GET['lr']);
        } else {
            $this->assign('lr', $limit);
        }
        if (isset($_GET['p'])) {
            $this->assign('p', (int) $_GET['p']);
        } else {
            $this->assign('p', 1);
        }
        $model = new Model();
        $where = ' `status` = 1 ';
        $where1 = 'a.status = 1 ';
        if (isset($_GET['softname']) && isset($_GET['package'])) {
            $where .= ' AND `package` = "' . (string) $_GET['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_GET['package'] . '"';
            $this->assign('package', (string) $_GET['package']);
        } else if (isset($_GET['package'])) {
            $where .= ' AND `package` = "' . (string) $_GET['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_GET['package'] . '"';
            $this->assign('package', (string) $_GET['package']);
            $this->assign('softname', '');
        } else if (isset($_GET['softname'])) {
            $condition['`softname`'] = array('like', '%' . $_GET['softname'] . '%');
            $soft_lists = $model->table('sj_soft')->where($condition)->field('package')->select();
            $packages = '';
            foreach ($soft_lists as $soft_list) {
                $packages .= ",'" . $soft_list['package'] . "'";
            }
            if ($packages[0] == ',') {
                $packages = substr($packages, 1);
            }
            $where .= " AND `package` in ({$packages}) ";
            $where1 .= " AND a.package in ({$packages}) ";
            $this->assign('softname', (string) $_GET['softname']);
            $this->assign('package', '');
        } else {
            $this->assign('package', '');
            $this->assign('softname', '');
        }
        if (isset($_GET['start_time'])) {
            $start_time = (string) $_GET['start_time'];
            $where .= " AND `brush_time` >= '" . strtotime($start_time) . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime($start_time) . "'";
            $this->assign('start_time', $start_time);
        } else {
            $where .= " AND `brush_time` >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time()))) . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time()))) . "'";
            $this->assign('start_time', date('Y-m-d 00:00:00', strtotime('-7 days', time())));
        }
        if (isset($_GET['end_time'])) {
            $end_time = (string) $_GET['end_time'];
            $where .= " AND `brush_time` <= '" . strtotime($end_time) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime($end_time) . "'";
            $this->assign('end_time', $end_time);
        } else {
            $where .= " AND `brush_time` <= '" . strtotime(date('Y-m-d H:i:s')) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime(date('Y-m-d')) . "'";
            $this->assign('end_time', date('Y-m-d 23:59:59'));
        }
        $zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM', 'status' => 1);
        $sum_info = $model->table('pu_config')->where($zonghe)->field('configcontent')->select();
        $baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT', 'status' => 1);
        $percent_info = $model->table('pu_config')->where($baifen)->field('configcontent')->select();
        if (isset($_GET['exceed_sum'])) {
            $where .= " AND `exceed_sum` >= '" . intval($_GET['exceed_sum']) . "'";
            $where1 .= " AND a.`exceed_sum` >= '" . intval($_GET['exceed_sum']) . "'";
            $this->assign('exceed_sum', intval($_GET['exceed_sum']));
        } else {
            //$this -> assign('exceed_sum',$sum_info[0]['configcontent']);
        }
        if (isset($_GET['percent'])) {
            $percent = floatval(intval($_GET['percent']) / 100);
            $where .= " AND `percent` >= '" . $percent . "'";
            $where1 .= " AND a.percent >= '" . $percent . "'";
            $this->assign('percent', intval($_GET['percent']));
        } else {
            //$this -> assign('percent',$percent_info[0]['configcontent']);
        }
        if ($_GET['white_list'] == '0') {
            $where .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
            $where1 .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
        }
        $developwhere = '';
        if (isset($_GET['dev_name']) && $_GET['dev_name'] != '') {
            $developwhere .= 'c.dev_name like "%' . $_GET['dev_name'] . '%"';
            $this->assign('dev_name', $_GET['dev_name']);
        }
        if (isset($_GET['email']) && $_GET['email'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'c.email = "' . $_GET['email'] . '"';
            } else {
                $developwhere .= ' and c.email = "' . $_GET['email'] . '"';
            }
            $this->assign('email', $_GET['email']);
        }
        $type = array('0' => '公司', '1' => '个人');
        if (isset($_GET['dev_type']) && $_GET['dev_type'] != 3) {
            if ($developwhere == '') {
                $developwhere .= 'c.type = "' . $_GET['dev_type'] . '"';
            } else {
                $developwhere .= ' and c.type = "' . $_GET['dev_type'] . '"';
            }
            $this->assign('dev_type', $_GET['dev_type']);
        } else {
            $this->assign('dev_type', 3);
        }
        if (isset($_GET['dev_id']) && $_GET['dev_id'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'b.dev_id = "' . $_GET['dev_id'] . '"';
            } else {
                $developwhere .= ' and b.dev_id = "' . $_GET['dev_id'] . '"';
            }
            $this->assign('dev_id', $_GET['dev_id']);
        }
        $table = 'sj_ip_download_brush a';
        if ($developwhere == "") {
            $count_total = $model->table('sj_ip_download_brush')->where($where)->count();
        } else {
            $developwhere .= " and " . $where1;
            $res = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->field('a.brush_id')->select();
            $count_total = count($res);
        }

        $page = new Page($count_total, $limit, $param);

        $order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
        $order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;
        if ($developwhere == "") {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table('sj_ip_download_brush')->where($where)->order('package ASC,brush_time ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table('sj_ip_download_brush')->where($where)->order('package DESC,brush_time ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table('sj_ip_download_brush')->where($where)->order('brush_time ASC,package ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table('sj_ip_download_brush')->where($where)->order('brush_time DESC,package DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
            }
        } else {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package ASC,a.brush_time ASC')->field('a.*')->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package DESC,a.brush_time DESC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time ASC,a.package ASC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time DESC,a.package DESC')->field('a.*')->select();
            }
        }
        //查询操作日志表
        $brush_log = $model->table('sj_brush_log')->order('oper_time DESC')->select();
        $brush_log_arr = array();
        foreach ($brush_log as $key => $value) {
            $oper_time = $value['oper_time'] ? date('Y-m-d', $value['oper_time']) . '<br />' . date('H:i:s', $value['oper_time']) : '';
            $brush_log_arr[$value['package']][] = array('oper_time' => $oper_time, 'reason' => $value['reason'], 'oper_type' => $value['type'],'add_data'=>$value['add_data'],'cut_data'=>$value['cut_data'],'is_system'=>$value['is_system']);
        }
        for ($i = 0; $i < count($brush_list); $i++) {
            $map['`package`'] = $brush_list[$i]['package'];
            $soft_info = $model->table('sj_soft')->where($map)->field('dev_id,dev_name,dever_email,softname,hide')->order('softid DESC')->limit(1)->select();
            $dev_id = $soft_info[0]['dev_id'];
            $develop_info = $model->table('pu_developer')->where(array('dev_id' => $dev_id))->field('email,dev_name,type,status')->select();
            $brush_list[$i]['softname'] = $soft_info[0]['softname'];
            $brush_list[$i]['hide'] = $soft_info[0]['hide'];
            $brush_list[$i]['dev_name'] = $develop_info[0]['dev_name'];
            $brush_list[$i]['email'] = $develop_info[0]['email'];
            $brush_list[$i]['dev_id'] = $soft_info[0]['dev_id'];
            $brush_list[$i]['status'] = $soft_info[0]['status'];
			if($brush_log_arr[$brush_list[$i]['package']][0]['add_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['add_data'];
				$brush_list[$i]['brush_type'] = '增量';
			}else if($brush_log_arr[$brush_list[$i]['package']][0]['cut_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['cut_data'];
				$brush_list[$i]['brush_type'] = '扣量';
			}

            $develop_typearr = array('0' => '公司', '1' => '个人');
            $brush_list[$i]['dev_type'] = $develop_typearr[$develop_info[0]['type']];
            if (empty($brush_list[$i]['softname'])) {
                $brush_list[$i]['softname'] = '0';
            }
            //操作记录
            $log_time = $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] ? $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] : '';
            $brush_list[$i]['log_time'] = $log_time;
            $brush_list[$i]['reason'] = $this->brush_types($brush_log_arr[$brush_list[$i]['package']][0]['oper_type']) . '<br />' . str_replace(',', '<br />', $brush_log_arr[$brush_list[$i]['package']][0]['reason']);
			if($brush_log_arr[$brush_list[$i]['package']][0]['is_system']==1){
				$brush_list[$i]['reason'] = '系统自动刷量:';
			}
            $brush_list[$i]['percent'] = substr($brush_list[$i]['percent'] * 100, 0, 5) . "%";
            $mingdan = array('`package`' => $brush_list[$i]['package'], '`status`' => 1);
            $white_data = $model->table('sj_brush_adapter')->where($mingdan)->limit(1)->select();
            $white_count = count($white_data);
            if ($white_count > 0) {
                $brush_list[$i]['white'] = 1;
                $brush_list[$i]['note'] = $white_data[0]['note'];
            } else {
                $brush_list[$i]['white'] = 0;
                $brush_list[$i]['note'] = '';
            }
            //获取历史增量值与扣量值
            $soft = $model->table('sj_soft')->field('total_downloaded_add ,total_downloaded_detain,total_downloaded as downloaded ')->where("package = '{$brush_list[$i]['package']}' and status=1")->order('softid DESC')->limit(1)->select();
            $brush_list[$i]['his_add_data'] = $soft[0]['total_downloaded_add'] ? $soft[0]['total_downloaded_add'] : 0;
            $brush_list[$i]['his_cut_data'] = $soft[0]['total_downloaded_detain'] ? $soft[0]['total_downloaded_detain'] : 0;
            $brush_list[$i]['soft_total_data'] = $soft[0]['downloaded'] ? $soft[0]['downloaded'] : 0;
        }
        //var_dump($brush_list);
        //是否显示白名单
        $white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;

        foreach ($brush_list as $key => $val) {
            if ($white_list == 1) {
                $brush_list_all = $brush_list;
            } elseif ($white_list == 0) {
                if ($val['white'] == 0) {
                    $brush_list_all[] = $val;
                }
            }
        }
        $action_id = $model->table('sj_admin_node')->where("nodename='/index.php/Dev/DownloadBrush/brush_oper'")->getField('node_id');
        //echo $model->getlastsql();
        $this->assign('action_id', $action_id);

        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('white_list', $white_list);
        $this->assign('page', $page->show());
        $this->assign('order_go', $order_go);
        $this->assign('status', 3);
        $this->assign('count', $count_total);
        $this->assign('order_rule', $order_rule);
        $this->assign('brush_list', $brush_list_all);
        $this->display();
    }

    //m.anzhi.com下载刷量
    function manzhi_download_brush() {
        import('@.ORG.Page');
        $param = http_build_query($_GET);
        $limit = 50;
        if (isset($_GET['lr'])) {
            $this->assign('lr', (int) $_GET['lr']);
        } else {
            $this->assign('lr', $limit);
        }
        if (isset($_GET['p'])) {
            $this->assign('p', (int) $_GET['p']);
        } else {
            $this->assign('p', 1);
        }
        $model = new Model();
        $where = ' `status` = 1 ';
        $where1 = 'a.status = 1 ';
        if (isset($_GET['softname']) && isset($_GET['package'])) {
            $where .= ' AND `package` = "' . (string) $_GET['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_GET['package'] . '"';
            $this->assign('package', (string) $_GET['package']);
        } else if (isset($_GET['package'])) {
            $where .= ' AND `package` = "' . (string) $_GET['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_GET['package'] . '"';
            $this->assign('package', (string) $_GET['package']);
            $this->assign('softname', '');
        } else if (isset($_GET['softname'])) {
            $condition['`softname`'] = array('like', '%' . $_GET['softname'] . '%');
            $soft_lists = $model->table('sj_soft')->where($condition)->field('package')->select();
            $packages = '';
            foreach ($soft_lists as $soft_list) {
                $packages .= ",'" . $soft_list['package'] . "'";
            }
            if ($packages[0] == ',') {
                $packages = substr($packages, 1);
            }
            $where .= " AND `package` in ({$packages}) ";
            $where1 .= " AND a.package in ({$packages}) ";
            $this->assign('softname', (string) $_GET['softname']);
            $this->assign('package', '');
        } else {
            $this->assign('package', '');
            $this->assign('softname', '');
        }
        if (isset($_GET['start_time'])) {
            $start_time = (string) $_GET['start_time'];
            $where .= " AND `brush_time` >= '" . strtotime($start_time) . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime($start_time) . "'";
            $this->assign('start_time', $start_time);
        } else {
            $where .= " AND `brush_time` >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time())) . ' 00:00:00') . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time()))) . "'";
            $this->assign('start_time', date('Y-m-d 00:00:00', strtotime('-7 days', time())));
        }
        if (isset($_GET['end_time'])) {
            $end_time = (string) $_GET['end_time'];
            $where .= " AND `brush_time` <= '" . strtotime($end_time) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime($end_time) . "'";
            $this->assign('end_time', $end_time);
        } else {
            $where .= " AND `brush_time` <= '" . strtotime(date('Y-m-d H:i:s')) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime(date('Y-m-d')) . "'";
            $this->assign('end_time', date('Y-m-d 23:59:59'));
        }
        $zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM', 'status' => 1);
        $sum_info = $model->table('pu_config')->where($zonghe)->field('configcontent')->select();
        $baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT', 'status' => 1);
        $percent_info = $model->table('pu_config')->where($baifen)->field('configcontent')->select();
        if (isset($_GET['m_sum'])) {
            $where .= " AND `m_sum`-`360_sum`-`qq_sum`-`sina_sum`-`uc_sum` >= '" . intval($_GET['m_sum']) . "'";
            $where1 .= " AND a.`m_sum`-`360_sum`-`qq_sum`-`sina_sum`-`uc_sum` >= '" . intval($_GET['m_sum']) . "'";
            $this->assign('m_sum', intval($_GET['m_sum']));
        } else {
            //$this -> assign('m_sum',$sum_info[0]['configcontent']);
        }
        if (isset($_GET['percent'])) {
            $percent = floatval(intval($_GET['percent']) / 100);
            $where .= " AND `percent` >= '" . $percent . "'";
            $this->assign('percent', intval($_GET['percent']));
        } else {
            //$this -> assign('percent',$percent_info[0]['configcontent']);
        }
        if ($_GET['white_list'] == '0') {
            $where .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
            $where1 .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
        }

        $developwhere = '';
        if (isset($_GET['dev_name']) && $_GET['dev_name'] != '') {
            $developwhere .= 'c.dev_name like "%' . $_GET['dev_name'] . '%"';
            $this->assign('dev_name', $_GET['dev_name']);
        }
        if (isset($_GET['email']) && $_GET['email'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'c.email = "' . $_GET['email'] . '"';
            } else {
                $developwhere .= ' and c.email = "' . $_GET['email'] . '"';
            }
            $this->assign('email', $_GET['email']);
        }
        $type = array('0' => '公司', '1' => '个人');
        if (isset($_GET['dev_type']) && $_GET['dev_type'] != 3) {
            if ($developwhere == '') {
                $developwhere .= 'c.type = "' . $_GET['dev_type'] . '"';
            } else {
                $developwhere .= ' and c.type = "' . $_GET['dev_type'] . '"';
            }
            $this->assign('dev_type', $_GET['dev_type']);
        } else {
            $this->assign('dev_type', 3);
        }
        if (isset($_GET['dev_id']) && $_GET['dev_id'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'b.dev_id = "' . $_GET['dev_id'] . '"';
            } else {
                $developwhere .= ' and b.dev_id = "' . $_GET['dev_id'] . '"';
            }
            $this->assign('dev_id', $_GET['dev_id']);
        }
        if ($developwhere == '') {
            $count_total = $model->table('sj_manzhi_download_brush a')->where($where)->count();
        } else {
            $developwhere .= " and " . $where1;
            $res = $model->table('sj_manzhi_download_brush a')->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->field('a.brush_id')->select();
            $count_total = count($res);
        }

        $page = new Page($count_total, $limit, $param);
        //排序规则
        $order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
        $order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;
        $table = 'sj_manzhi_download_brush a';
        if ($developwhere == '') {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table('sj_manzhi_download_brush')->where($where)->order('package ASC,brush_time ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table('sj_manzhi_download_brush')->where($where)->order('package DESC,brush_time DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table('sj_manzhi_download_brush')->where($where)->order('brush_time ASC,package ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table('sj_manzhi_download_brush')->where($where)->order('brush_time DESC,package DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
            }
        } else {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package ASC,a.brush_time ASC')->field('a.*')->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package DESC,a.brush_time DESC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time ASC,a.package ASC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time DESC,a.package DESC')->field('a.*')->select();
            }
        }
        //查询操作日志表
        $brush_log = $model->table('sj_brush_log')->order('oper_time DESC')->select();
        $brush_log_arr = array();
        foreach ($brush_log as $key => $value) {
            $oper_time = $value['oper_time'] ? date('Y-m-d', $value['oper_time']) . '<br />' . date('H:i:s', $value['oper_time']) : '';
            $brush_log_arr[$value['package']][] = array('oper_time' => $oper_time, 'reason' => $value['reason'], 'oper_type' => $value['type'],'add_data'=>$value['add_data'],'cut_data'=>$value['cut_data'],'is_system'=>$value['is_system']);
        }
        for ($i = 0; $i < count($brush_list); $i++) {
            $map['`package`'] = $brush_list[$i]['package'];
            $soft_info = $model->table('sj_soft')->where($map)->field('dev_id,dev_name,dever_email,softname,hide')->order('softid DESC')->limit(1)->select();
            $dev_id = $soft_info[0]['dev_id'];
            $develop_info = $model->table('pu_developer')->where(array('dev_id' => $dev_id))->field('email,dev_name,type,status')->select();
            $brush_list[$i]['softname'] = $soft_info[0]['softname'];
            $brush_list[$i]['hide'] = $soft_info[0]['hide'];
            $brush_list[$i]['dev_name'] = $develop_info[0]['dev_name'];
            $brush_list[$i]['email'] = $develop_info[0]['email'];
            $brush_list[$i]['dev_id'] = $soft_info[0]['dev_id'];
            $brush_list[$i]['status'] = $soft_info[0]['status'];
			
			if($brush_log_arr[$brush_list[$i]['package']][0]['add_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['add_data'];
				$brush_list[$i]['brush_type'] = '增量';
			}else if($brush_log_arr[$brush_list[$i]['package']][0]['cut_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['cut_data'];
				$brush_list[$i]['brush_type'] = '扣量';
			}
			//var_dump($brush_list[$i]['brush_type']);
            $develop_typearr = array('0' => '公司', '1' => '个人');
            $brush_list[$i]['dev_type'] = $develop_typearr[$develop_info[0]['type']];
            if (empty($brush_list[$i]['softname'])) {
                $brush_list[$i]['softname'] = '0';
            }
            $log_time = $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] ? $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] : '';
            //操作记录
            $brush_list[$i]['log_time'] = $log_time;
            $brush_list[$i]['reason'] = $this->brush_types($brush_log_arr[$brush_list[$i]['package']][0]['oper_type']) . '<br />' . str_replace(',', '<br />', $brush_log_arr[$brush_list[$i]['package']][0]['reason']);
			if($brush_log_arr[$brush_list[$i]['package']][0]['is_system']==1){
				$brush_list[$i]['reason'] = '系统自动刷量:';
			}
            $brush_list[$i]['percent'] = round($brush_list[$i]['percent'] * 10000) / 100 . "%";
            $brush_list[$i]['percentm'] = round($brush_list[$i]['percentm'] * 10000) / 100 . "%";
            $mingdan = array('`package`' => $brush_list[$i]['package'], '`status`' => 1);
            $white_data = $model->table('sj_brush_adapter')->where($mingdan)->limit(1)->select();
            $white_count = count($white_data);
            if ($white_count > 0) {
                $brush_list[$i]['white'] = 1;
                $brush_list[$i]['note'] = $white_data[0]['note'];
            } else {
                $brush_list[$i]['white'] = 0;
                $brush_list[$i]['note'] = '';
            }
            $brush_list[$i]['m_sum'] = $brush_list[$i]['m_sum'] - $brush_list[$i]['360_sum'] - $brush_list[$i]['qq_sum'] - $brush_list[$i]['sina_sum'] - $brush_list[$i]['uc_sum'];
            //获取历史增量值与扣量值
            $soft = $model->table('sj_soft')->field('total_downloaded_add ,total_downloaded_detain,total_downloaded as downloaded ')->where("package = '{$brush_list[$i]['package']}' and status=1")->order('softid DESC')->limit(1)->select();
            $brush_list[$i]['his_add_data'] = $soft[0]['total_downloaded_add'] ? $soft[0]['total_downloaded_add'] : 0;
            $brush_list[$i]['his_cut_data'] = $soft[0]['total_downloaded_detain'] ? $soft[0]['total_downloaded_detain'] : 0;
            $brush_list[$i]['soft_total_data'] = $soft[0]['downloaded'] ? $soft[0]['downloaded'] : 0;
        }


        //是否显示白名单
        $white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;

        foreach ($brush_list as $key => $val) {
            if ($white_list == 1) {
                $brush_list_all = $brush_list;
            } elseif ($white_list == 0) {
                if ($val['white'] == 0) {
                    $brush_list_all[] = $val;
                }
            }
        }

        $action_id = $model->table('sj_admin_node')->where("nodename='/index.php/Dev/DownloadBrush/brush_oper'")->getField('node_id');
        //echo $model->getlastsql();
        $this->assign('action_id', $action_id);

        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('white_list', $white_list);
        $this->assign('status', 2);
        $this->assign('page', $page->show());
        $this->assign('order_go', $order_go);
        $this->assign('order_rule', $order_rule);
        $this->assign('brush_list', $brush_list_all);
        $this->assign('count', $count_total);
        $this->display();
    }

    //www.anzhi.com下载刷量
    function webanzhi_download_brush() {
        import('@.ORG.Page');
        $param = http_build_query($_GET);
        $limit = 50;
        if (isset($_GET['lr'])) {
            $this->assign('lr', (int) $_GET['lr']);
        } else {
            $this->assign('lr', $limit);
        }
        if (isset($_GET['p'])) {
            $this->assign('p', (int) $_GET['p']);
        } else {
            $this->assign('p', 1);
        }
        $model = new Model();
        $where = ' status = 1 ';
        $where1 = 'a.status = 1 ';
        if (isset($_GET['softname']) && isset($_GET['package'])) {
            $where .= ' AND package = "' . (string) $_GET['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_GET['package'] . '"';
            $this->assign('package', (string) $_GET['package']);
        } else if (isset($_GET['package'])) {
            $where .= ' AND package = "' . (string) $_GET['package'] . '"';
            $where1 .= ' AND a.package = "' . (string) $_GET['package'] . '"';
            $this->assign('package', (string) $_GET['package']);
            $this->assign('softname', '');
        } else if (isset($_GET['softname'])) {
            $condition['`softname`'] = array('like', '%' . $_GET['softname'] . '%');
            $soft_lists = $model->table('sj_soft')->where($condition)->field('package')->select();
            $packages = '';
            foreach ($soft_lists as $soft_list) {
                $packages .= ",'" . $soft_list['package'] . "'";
            }
            if ($packages[0] == ',') {
                $packages = substr($packages, 1);
            }
            $where .= " AND package in ({$packages}) ";
            $where1 .= " AND a.package in ({$packages}) ";
            $this->assign('softname', (string) $_GET['softname']);
            $this->assign('package', '');
        } else {
            $this->assign('package', '');
            $this->assign('softname', '');
        }
        if (isset($_GET['start_time'])) {
            $start_time = (string) $_GET['start_time'];
            $where .= " AND brush_time >= '" . strtotime($start_time) . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime($start_time) . "'";
            $this->assign('start_time', $start_time);
        } else {
            $where .= " AND brush_time >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time()))) . "'";
            $where1 .= " AND a.brush_time >= '" . strtotime(date('Y-m-d', strtotime('-7 days', time()))) . "'";
            $this->assign('start_time', date('Y-m-d 00:00:00', strtotime('-7 days', time())));
        }
        if (isset($_GET['end_time'])) {
            $end_time = (string) $_GET['end_time'];
            $where .= " AND brush_time <= '" . strtotime($end_time) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime($end_time) . "'";
            $this->assign('end_time', $end_time);
        } else {
            $where .= " AND brush_time <= '" . strtotime(date('Y-m-d')) . "'";
            $where1 .= " AND a.brush_time <= '" . strtotime(date('Y-m-d')) . "'";
            $this->assign('end_time', date('Y-m-d 23:59:59'));
        }
        $zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM', 'status' => 1);
        $sum_info = $model->table('pu_config')->where($zonghe)->field('configcontent')->select();
        $baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT', 'status' => 1);
        $percent_info = $model->table('pu_config')->where($baifen)->field('configcontent')->select();
        if (isset($_GET['web_sum'])) {
            $where .= " AND web_sum-wdj_sum-tx_sum-baidu_sum >= '" . intval($_GET['web_sum']) . "'";
            $where1 .= " AND a.web_sum-wdj_sum-tx_sum-baidu_sum >= '" . intval($_GET['web_sum']) . "'";
            $this->assign('web_sum', intval($_GET['web_sum']));
        } else {
            //$this -> assign('web_sum',$sum_info[0]['configcontent']);
        }
        if (isset($_GET['percent'])) {
            $percent = floatval(intval($_GET['percent']) / 100);
            $where .= " AND percentweb >= '" . $percent . "'";
            $where1 .= " AND a.percentweb >= '" . $percent . "'";
            $this->assign('percent', intval($_GET['percent']));
        } else {
            //$this -> assign('percent',$percent_info[0]['configcontent']);
        }


        $order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
        $order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;


        if ($_GET['white_list'] == '0') {
            $where .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
            $where1 .= " AND package not in (select package from sj_brush_adapter  where  status = 1)";
        }
        $developwhere = '';
        if (isset($_GET['dev_name']) && $_GET['dev_name'] != '') {
            $developwhere .= 'c.dev_name like "%' . $_GET['dev_name'] . '%"';
            $this->assign('dev_name', $_GET['dev_name']);
        }
        if (isset($_GET['email']) && $_GET['email'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'c.email = "' . $_GET['email'] . '"';
            } else {
                $developwhere .= ' and c.email = "' . $_GET['email'] . '"';
            }
            $this->assign('email', $_GET['email']);
        }
        $type = array('0' => '公司', '1' => '个人');
        if (isset($_GET['dev_type']) && $_GET['dev_type'] != 3) {
            if ($developwhere == '') {
                $developwhere .= 'c.type = "' . $_GET['dev_type'] . '"';
            } else {
                $developwhere .= ' and c.type = "' . $_GET['dev_type'] . '"';
            }
            $this->assign('dev_type', $_GET['dev_type']);
        } else {
            $this->assign('dev_type', 3);
        }
        if (isset($_GET['dev_id']) && $_GET['dev_id'] != '') {
            if ($developwhere == '') {
                $developwhere .= 'b.dev_id = "' . $_GET['dev_id'] . '"';
            } else {
                $developwhere .= ' and b.dev_id = "' . $_GET['dev_id'] . '"';
            }
            $this->assign('dev_id', $_GET['dev_id']);
        }
//                //查询该开发者的软件
//                $devtable = "sj_soft";
//                $dev = $model -> table($devtable)->where($developwhere)->select();
//                echo $model->getLastSql();
//                var_dump($dev);
//        var_dump($developwhere);
//        var_dump($where1)."<br>";
        $table = 'sj_webanzhi_download_brush a';
        if ($developwhere == "") {
            $count_total = $model->table($table)->where($where)->count();
        } else {
            $developwhere .= " and " . $where1;
            $res = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->field('a.brush_id')->select();
            $count_total = count($res);
        }
        $page = new Page($count_total, $limit, $param);
        //修改排序规则
        if ($developwhere == "") {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table($table)->where($where)->order('package ASC,brush_time ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table($table)->where($where)->order('package DESC,brush_time DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table($table)->where($where)->order('brush_time ASC,package ASC')->limit($page->firstRow . ',' . $page->listRows)->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table($table)->where($where)->order('brush_time DESC,package DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
            }
        } else {
            if ($order_go == 1 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package ASC,a.brush_time ASC')->field('a.*')->select();
            } elseif ($order_go == 1 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.package DESC,a.brush_time DESC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 1) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time ASC,a.package ASC')->field('a.*')->select();
            } elseif ($order_go == 2 && $order_rule == 2) {
                $brush_list = $model->table($table)->join('sj_soft b on a.package = b.package')->join('pu_developer c on b.dev_id = c.dev_id')->where($developwhere)->group('b.package')->order('a.brush_time DESC,a.package DESC')->field('a.*')->select();
            }
        }
//        echo $model->getLastSql();
        //查询操作日志表
        $brush_log = $model->table('sj_brush_log')->order('oper_time DESC')->select();
        $brush_log_arr = array();
        foreach ($brush_log as $key => $value) {
            $oper_time = $value['oper_time'] ? date('Y-m-d', $value['oper_time']) . '<br />' . date('H:i:s', $value['oper_time']) : '';
            $brush_log_arr[$value['package']][] = array('oper_time' => $oper_time, 'reason' => $value['reason'], 'oper_type' => $value['type'],'add_data'=>$value['add_data'],'cut_data'=>$value['cut_data'],'is_system'=>$value['is_system']);
        }
        for ($i = 0; $i < count($brush_list); $i++) {
            $map['`package`'] = $brush_list[$i]['package'];
            $soft_info = $model->table('sj_soft')->where($map)->field('dev_id,dev_name,dever_email,softname,hide')->order('softid DESC')->limit(1)->select();
            $dev_id = $soft_info[0]['dev_id'];
            $develop_info = $model->table('pu_developer')->where(array('dev_id' => $dev_id))->field('email,dev_name,type,status')->select();
            $brush_list[$i]['softname'] = $soft_info[0]['softname'];
            $brush_list[$i]['hide'] = $soft_info[0]['hide'];
            $brush_list[$i]['dev_name'] = $develop_info[0]['dev_name'];
            $brush_list[$i]['email'] = $develop_info[0]['email'];
            $brush_list[$i]['dev_id'] = $soft_info[0]['dev_id'];
            $brush_list[$i]['status'] = $soft_info[0]['status'];
            $develop_typearr = array('0' => '公司', '1' => '个人');
            $brush_list[$i]['dev_type'] = $develop_typearr[$develop_info[0]['type']];
            if (empty($brush_list[$i]['softname'])) {
                $brush_list[$i]['softname'] = '0';
            }
			
            $log_time = $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] ? $brush_log_arr[$brush_list[$i]['package']][0]['oper_time'] : '';
            $brush_list[$i]['log_time'] = $log_time;
			if($brush_log_arr[$brush_list[$i]['package']][0]['add_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['add_data'];
				$brush_list[$i]['brush_type'] = '增量';
			}else if($brush_log_arr[$brush_list[$i]['package']][0]['cut_data']>0){
				$brush_list[$i]['brush_num'] = $brush_log_arr[$brush_list[$i]['package']][0]['cut_data'];
				$brush_list[$i]['brush_type'] = '扣量';
			}
			 //var_dump($brush_type);
			//var_dump($brush_log_arr[$brush_list[$i]['package']][0]['reason']);
			
            $brush_list[$i]['reason'] = $this->brush_types($brush_log_arr[$brush_list[$i]['package']][0]['oper_type']) . '<br />' . str_replace(',', '<br />', $brush_log_arr[$brush_list[$i]['package']][0]['reason']);
			if($brush_log_arr[$brush_list[$i]['package']][0]['is_system']==1){
				$brush_list[$i]['reason'] = '系统自动刷量:';
			}
            $brush_list[$i]['percent'] = round($brush_list[$i]['percent'] * 10000) / 100 . "%";
            $brush_list[$i]['percentweb'] = round($brush_list[$i]['percentweb'] * 10000) / 100 . "%";
            $mingdan = array('`package`' => $brush_list[$i]['package'], '`status`' => 1);
            $white_data = $model->table('sj_brush_adapter')->where($mingdan)->limit(1)->select();
            $white_count = count($white_data);
            if ($white_count > 0) {
                $brush_list[$i]['white'] = 1;
                $brush_list[$i]['note'] = $white_data[0]['note'];
            } else {
                $brush_list[$i]['white'] = 0;
                $brush_list[$i]['note'] = '';
            }
            $brush_list[$i]['web_sum'] = $brush_list[$i]['web_sum'] - $brush_list[$i]['wdj_sum'] - $brush_list[$i]['tx_sum'] - $brush_list[$i]['baidu_sum'];
            //$brush_list[$i]['web_sum'] = $brush_list[$i]['web_sum'];
            //获取历史增量值与扣量值
            $soft = $model->table('sj_soft')->field('softid,total_downloaded_add ,total_downloaded_detain,total_downloaded as downloaded ')->where("package = '{$brush_list[$i]['package']}' and status=1")->limit(1)->order('softid DESC')->select();
            //echo $model->getlastsql().'<br>';
            $brush_list[$i]['his_add_data'] = $soft[0]['total_downloaded_add'] ? $soft[0]['total_downloaded_add'] : 0;
            $brush_list[$i]['his_cut_data'] = $soft[0]['total_downloaded_detain'] ? $soft[0]['total_downloaded_detain'] : 0;
            $brush_list[$i]['soft_total_data'] = $soft[0]['downloaded'] ? $soft[0]['downloaded'] : 0;
        }
        ;
        //var_dump($brush_list);
        //是否显示白名单
        $white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;
        $lastbrush_list = array();
        foreach ($brush_list as $key => $val) {
            if ($white_list == 1) {
                $brush_list_all = $brush_list;
            } elseif ($white_list == 0) {
                if ($val['white'] == 0) {
                    $brush_list_all[] = $val;
                }
            }
        }
        $action_id = $model->table('sj_admin_node')->where("nodename='/index.php/Dev/DownloadBrush/brush_oper'")->getField('node_id');
        //echo $model->getlastsql();
        $this->assign('action_id', $action_id);
        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('status', 1);
        $this->assign('white_list', $white_list);
        $this->assign('page', $page->show());
        $this->assign('order_go', $order_go);
        $this->assign('order_rule', $order_rule);
        $this->assign('brush_list', $brush_list_all);
        $this->assign('count', $count_total);
        $this->display();
    }

    //刷量白名单列表
    function brush_white_list() {
        import('@.ORG.Page');
        $param = http_build_query($_GET);
        $limit = 15;
        //$where = array();
        $where = '';
        if (isset($_GET['lr'])) {
            $this->assign('lr', (int) $_GET['lr']);
        } else {
            $this->assign('lr', $limit);
        }
        if (isset($_GET['p'])) {
            $this->assign('p', (int) $_GET['p']);
        } else {
            $this->assign('p', 1);
        }
        if (isset($_GET['status'])) {
            //$where = array('a.`status`' => intval($_GET['status']));
            $where .= 'a.status = "' . intval($_GET['status']) . '"';
            $this->assign('status', intval($_GET['status']));
        } else {
            //$where = array('a.`status`' => 1);
            $where .= 'a.status = 1';
            $this->assign('status', 1);
        }
        if (isset($_GET['package'])) {
            //$where = array('a.`package`' => trim($_GET['package']));
            if ($where == '') {
                $where .= 'a.package = "' . trim($_GET['package']) . '"';
            } else {
                $where .= ' and a.package = "' . trim($_GET['package']) . '"';
            }
            $this->assign('package', $_GET['package']);
        } else {
            $this->assign('package', '');
        }
        if (isset($_GET['softname'])) {
//            $where['a.`softname`'] = array('like', "%" . trim($_GET['softname']) . "%");
            if ($where == "") {
                $where .= 'a.softname like "%' . trim($_GET['softname']) . '%"';
            } else {
                $where .= ' and a.softname like "%' . trim($_GET['softname']) . '%"';
            }
            $this->assign('softname', $_GET['softname']);
        } else {
            $this->assign('softname', '');
        }
        if (isset($_GET['start_time'])) {
//            $where['a.`publish_tm` >= '] = strtotime($_GET['starttime']);
            if ($where == "") {
                $where .= 'a.publish_tm >= "' . strtotime($_GET['start_time']) . '"';
            } else {
                $where .= ' and a.publish_tm >= "' . strtotime($_GET['start_time']) . '"';
            }
            $this->assign('start_time', $_GET['start_time']);
        }
        if (isset($_GET['end_time'])) {
//            $where['a.`publish_tm` >= '] = strtotime($_GET['starttime']);
            if ($where == "") {
                $where .= 'a.publish_tm <= "' . strtotime($_GET['end_time']) . '"';
            } else {
                $where .= ' and a.publish_tm <= "' . strtotime($_GET['end_time']) . '"';
            }
            $this->assign('end_time', $_GET['end_time']);
        }
        $allcategory = $this->get_catgory_str();
        foreach($allcategory as $key=>$val){
            $tmpcate = explode(',',$val);
            foreach($tmpcate as $tmpkey=>$tmpval){
                $tmpcate[$tmpkey] = ",".$tmpval.",";
            }
            $allcategory[$key] = implode('","', $tmpcate);
        }
        if(isset($_GET['softtype'])&&$_GET['softtype']!==''){
            if ($where == "") {
                $where .= 'b.category_id in ("' .$allcategory[$_GET['softtype']].'")';
            } else {
                $where .= ' and b.category_id in ("' .$allcategory[$_GET['softtype']].'")';
            }
            $this->assign('softtype', $_GET['softtype']);
        }
        $model = new Model();
        if(isset($_GET['softtype'])&&$_GET['softtype']!==''){
            $count = $model->table('sj_brush_adapter a')->join('sj_soft b on a.package = b.package')->where($where)
                        ->order('a.update_tm desc')->field('a.blank_id')->group('a.package')->select();
            $count_total = count($count);
        }else{
            $count_total = $model->table('sj_brush_adapter a')->where($where)->count();
        }
        $page = new Page($count_total, $limit, $param);
        $white_list = $model->table('sj_brush_adapter a')->join('sj_soft b on a.package = b.package')->where($where)
                        ->limit($page->firstRow . ',' . $page->listRows)->order('a.update_tm desc')->field('a.*,b.category_id')->group('a.package')->select();
        $type = array('1'=>'应用','2'=>'游戏','3'=>'电子书');
        foreach ($white_list as $key => $value) {
            $white_list[$key]['note'] = str_replace(',', '&nbsp&nbsp', $value['note']);
            $cate = explode(',',$value['category_id']);
            $white_list[$key]['category_id'] = $cate[1];
            foreach($allcategory as $allkey=>$allval){
                $catekey = strpos($allval,$white_list[$key]['category_id']);
//                var_dump($catekey);
                if($catekey){
                    $white_list[$key]['type'] = $type[$allkey];
                }
            }
        }

        //echo $model->getlastsql(); 
        $this->assign('white_list', $white_list);
        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('page', $page->show());
        $this->assign('count', $count_total);
        //导出csv文档
        if (isset($_GET['exportexcel']) && $_GET['exportexcel'] == 1) {
            header("Content-Type: application/vnd.ms-excel; charset=GB2312");
            header("Content-Disposition: attachment;filename=whitelist.csv ");
            $header_str = iconv("utf-8", 'gbk', "包名,软件名,软件类型,备注,添加时间,更新时间\n");
            echo $header_str;
            foreach ($white_list as $key => $val) {
                $note = str_replace('&nbsp','',$val['note']);
                $str = $val['package'] . "," . $val['softname'] . "," . $val['type'] . "," . $note . "," . date("Y-m-d H:i:s", $val['publish_tm']) . "," . date("Y-m-d H:i:s", $val['update_tm']);
                $str = iconv("utf-8", 'gbk', $str . "\n");
                echo $str;
            }
        } else {
            $this->display();
        }
    }



    //刷量白名单添加do
    function brush_white_add_do() {
        if (isset($_POST)) {
            $data['package'] = trim($_POST['package']);
            $data['publish_tm'] = time();
            $data['update_tm'] = time();
            $data['status'] = 1;
            $note = '';
            foreach ($_POST['note'] as $value) {
                $note.=$value . ',';
            }
            $data['note'] = substr($note, 0, -1);
            if (empty($data['package'])) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('请填写包名');
            }
            if (!preg_match("/^[a-z0-9_\.]+$/i", $data['package'])) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('包名格式有误');
            }
            $model = new Model();
            $adapter_where = array('`package`' => $data['package'], '`status`' => 1);
            $adapter_count = $model->table('sj_brush_adapter')->where($adapter_where)->count();
            if ($adapter_count > 0) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('包名已存在刷量白名单列表');
            }
            $black_count = $model->table('sj_brush_black')->where($adapter_where)->count();
            if ($black_count > 0) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('包名已存在刷量黑名单列表');
            }
            $soft_where = array('`package`' => $data['package'], '`status`' => 1,'`hide`' => 1);
            $soft_count = $model->table('sj_soft')->where($soft_where)->count();
            if ($soft_count == 0) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('该软件不在已上架列表');
            }
            $map = array('`package`' => $data['package'], '`status`' => 1);
            $soft_info = $model->table('sj_soft')->where($map)->limit(1)->field('softname')->select();
            $data['softname'] = $soft_info[0]['softname'];
            if ($blank_id = $model->table('sj_brush_adapter')->add($data)) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->writelog('添加了blank_id为' . $blank_id . '包名为' . $data['package'] . '的白名单', 'sj_brush_adapter', $blank_id,__ACTION__ ,"","add");
                $this->success('添加成功');
            } else {
                //$this -> assign('jumpUrl','/index.php/Sj/DownloadBrush/brush_white_add');
                $this->error('添加失败');
            }
        }
    }

    //刷量白名单编辑
    function brush_white_edit() {
        if (isset($_POST['blank_id'])) {
            $blank_id = intval($_POST['blank_id']);
            $model = new Model();
            $where = array('`blank_id`' => $blank_id);
            $white_info = $model->table('sj_brush_adapter')->where($where)->field('blank_id,package,note')
                            ->limit(1)->select();
            if ($white_info) {
                $result = array('success' => true, 'rows' => array('package' => $white_info[0]['package'], 'note' => explode(',', $white_info[0]['note']), 'blank_id' => $white_info[0]['blank_id']));
                $this->writelog('编辑了blank_id为' . $white_info[0]['blank_id'] . '包名为' . $white_info[0]['package'] . '的白名单', 'sj_brush_adapter', $white_info[0]['blank_id'],__ACTION__ ,"","edit");
                echo json_encode($result);
                exit();
            } else {
                $result = array('success' => false, 'rows' => $white_info[0]);
                echo json_encode($result);
                exit();
            }
        } else {
            $result = array('success' => false, 'rows' => $white_info[0]);
            echo json_encode($result);
            exit();
        }
    }

    //查看刷量记录
    function brush_show_log() {
        $type = trim($_GET['type']);
        $type_sql = $type ? " and type='{$type}'" : '';
        $package = trim($_GET['package']);
        //$start_time = strtotime(trim($_POST['log_start_time']));
        //$end_time =  strtotime(trim($_POST['log_end_time']));
        $model = new model();
        $list = $model->table('sj_brush_log')->where("package = '{$package}' {$type_sql}")->order('oper_time DESC')->limit(1)->select();
        //echo $model->getlastsql();
        foreach ($list as $key => $value) {
            $list[$key]['reason'] = str_replace(',', '<br />', $value['reason']);
        }
        //var_dump($list);
        $this->assign('list', $list);
        $this->assign('status', $type);
        $this->display();
    }

    //获取历史增量值与扣量值
    //function get_brush_log(){
    //$type = trim($_POST['type']);
    //$type_sql = $type?"  and type='{$type}'":'';
    //$package =  trim($_POST['package']);
    //$model =  new model();
    /* $add_data = $model->table('sj_brush_log')->field('add_data')->where("package = '{$package}' and add_data>0 $type_sql")->order('oper_time DESC')->select();
      $cut_data = $model->table('sj_brush_log')->field('cut_data')->where("type = '{$type}' and package = '{$package}' and cut_data>0 $type_sql")->order('oper_time DESC')->select(); */
    //$soft = $model->table('sj_soft')->field('total_downloaded_add ,total_downloaded_detain ')->where("package = '{$package}'")->limit(1)->select();
    //if($soft){
    //$result = array ('success' => true, 'add_data' =>$soft[0]['total_downloaded_add'],'cut_data'=>$soft[0]['total_downloaded_detain']);
    //echo json_encode ($result);
    //exit ();
    //}else{
    //$result = array ('success' => false, 'rows' =>$res[0]);
    //echo json_encode ($result);
    //exit ();
    //}
    //}
    //刷量操作
    function brush_oper() {
        $is_count = trim($_POST['is_count']); //1 增量 0减量
        $soft_last_data = trim($_POST['soft_last_data']);
        $package = trim($_POST['package']);
        $type = trim($_POST['type']);
        //新增暂时下载量
        if(trim($_POST['tmp_type'])){
            $data_n=array();
            $data_n['type'] = trim($_POST['tmp_type']);//1增量2扣量
            $data_n['start_tm'] = strtotime(trim($_POST['start_tm_new']));
            $data_n['end_tm'] = strtotime(trim($_POST['end_tm_new']));
            $data_n['tmp_downloaded_cnt'] = trim($_POST['tmp_downloaded_cnt']);
            $data_n['package'] = $package;
        }
        
        $oper_reason = '';
        foreach ($_POST['oper_reason'] as $value) {
            $oper_reason.=$value . ',';
        }
        $model = new model();
        $is_package = $model->table('sj_soft')->where("package = '{$package}' and status='1'")->select();
        if (!$is_package) {
            $this->error('对不起，查询不到此软件，无法操作！');
        }
        if ($is_count == '1') {
            $is_count = 'add_data';
            $is_white_log = '增量值:';
            $soft_res = $model->query("UPDATE `sj_soft` SET `total_downloaded_add`=total_downloaded_add+$soft_last_data WHERE package = '$package'");
        } else {
            $is_count = 'cut_data';
            $is_white_log = '扣量值:';
            $soft_res = $model->query("UPDATE `sj_soft` SET `total_downloaded_detain`=total_downloaded_detain+$soft_last_data WHERE package = '$package'");
        }
        if($data_n['start_tm']>$data_n['end_tm']){
             $this->error('“暂时下量”的“开始时间”不能大于“结束时间“');
        }
        if($data_n['start_tm'] && $data_n['end_tm']){
           $now_time=strtotime(date('Y-m-d',time()));
            $tmp_downloaded_data = $model->table('sj_soft_tmp_downloaded')->where("package = '{$package}' and status='1'")->find();
            if($tmp_downloaded_data){
                if(!(($tmp_downloaded_data['tmp_downloaded_cnt']==$data_n['tmp_downloaded_cnt']) && ($tmp_downloaded_data['start_tm']==$data_n['start_tm']) &&($tmp_downloaded_data['end_tm']==$data_n['end_tm']) && ($tmp_downloaded_data['type']==$data_n['type']))){
                    $time=time();
                    $data_n['create_tm']=$time;
                    if($data_n['start_tm']<$now_time){
                         $this->error('“暂时下载量”的“开始时间”不能小于“当前时间“');
                    }
                    $re=$model->query("UPDATE `sj_soft_tmp_downloaded` SET `status`=0,`update_tm`='$time' WHERE package = '$package' and status=1");
                    $res=$model->table('sj_soft_tmp_downloaded')->add($data_n);
                }
            }else{
                $data_n['create_tm']=time();
                if($data_n['start_tm']<$now_time){
                     $this->error('“暂时下载量”的“开始时间”不能小于“当前时间“');
                }
                $res=$model->table('sj_soft_tmp_downloaded')->add($data_n);
            }
        }
        if($res){
            if($data_n['type']==1){
                $this->writelog('下载量管理：暂时下载量-(新增增量为' . $data_n['tmp_downloaded_cnt'] . ",开始时间：".date('Y-m-d',$data_n['start_tm']).",结束时间：".date('Y-m-d',$data_n['end_tm']). ')包名为' . $package . '的软件','sj_soft_tmp_downloaded',$res,__ACTION__ ,"","add");
            }else{
                $this->writelog('下载量管理：暂时下载量-(新增扣量为' . $data_n['tmp_downloaded_cnt'] . ",开始时间：".date('Y-m-d',$data_n['start_tm']).",结束时间：".date('Y-m-d',$data_n['end_tm']) . ')包名为' . $package . '的软件','sj_soft_tmp_downloaded',$res,__ACTION__ ,"","add");
            }
        }
        

        $data = array('package' => $package, 'type' => $type, $is_count => $soft_last_data, 'oper_time' => time(), 'reason' => substr($oper_reason, 0, - 1));
        $res = $model->table('sj_brush_log')->add($data);
        $oper_types = $this->brush_types($type);
         // echo $model->getLastSql();die;
        if ($res) {
            $this->writelog($oper_types . '刷量操作-(' . $is_white_log . $soft_last_data . ')包名为' . $package . '的软件','sj_brush_log',$res,__ACTION__ ,"","add");
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    //刷量白名单编辑
    function brush_white_edit_do() {
        if (isset($_POST)) {
            $blank_id = intval($_POST['blank_id']);
            $data['package'] = trim($_POST['package']);
            //$data['note'] = trim($_POST['note']);
            $data['update_tm'] = time();
            $note = '';
            foreach ($_POST['note'] as $value) {
                $note.=$value . ',';
            }
            $data['note'] = substr($note, 0, -1);
            if (empty($data['package'])) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('请填写包名');
            }
            if (!preg_match("/^[a-z0-9_\.]+$/i", $data['package'])) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('包名格式有误');
            }
            $model = new Model();
            $white_where = array('`blank_id`' => $blank_id);
            $white_info = $model->table('sj_brush_adapter')->where($white_where)->field('package')
                            ->limit(1)->select();
            if ($white_info[0]['package'] != $data['package']) {
                $adapter_where = array('`package`' => $data['package'], '`status`' => 1);
                $adapter_count = $model->table('sj_brush_adapter')->where($adapter_where)->count();
                if ($adapter_count > 0) {
                    //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                    $this->error('包名不允许重复');
                }
            }
            $soft_where = array('`package`' => $data['package'], '`status`' => 1);
            $soft_count = $model->table('sj_soft')->where($soft_where)->count();
            if ($soft_count == 0) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('该包名不存在于软件表中');
            }
            $map = array('`package`' => $data['package'], '`status`' => 1);
            $soft_info = $model->table('sj_soft')->where($map)->limit(1)->field('softname')->select();
            $data['softname'] = $soft_info[0]['softname'];
            $condition = array('`blank_id`' => $blank_id);
            $log_result = $this->logcheck($condition,'sj_brush_adapter',$data,$model);
            if ($model->table('sj_brush_adapter')->where($condition)->save($data)) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->writelog('编辑了blank_id为' . $blank_id . '包名为' . $data['package'] . '的白名单'.$log_result, 'sj_brush_adapter', $blank_id,__ACTION__ ,"","edit");
                $this->success('编辑成功');
            } else {
                //$this -> assign('jumpUrl','/index.php/Sj/DownloadBrush/brush_white_edit');
                $this->error('编辑失败');
            }
        }
    }

    //刷量白名单删除
    function brush_white_del() {

        if (isset($_GET['blank_id'])) {
            $blankarr = explode(',', $_GET['blank_id']);
            $model = new Model();
            $data['update_tm'] = time();
            $data['status'] = 0;
            if (count($blankarr) == 1) {
                $blank_id = intval($_GET['blank_id']);
                $condition = array('`blank_id`' => $blank_id);      
                $package = $model->table('sj_brush_adapter')->where($condition)->find();
                $model->table('sj_brush_adapter')->where(array('package'=>$package['package']))->save($data);
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->writelog('删除了blank_id为' . $blank_id . '包名为' . $package['package'] . '的白名单', 'sj_brush_adapter', $blank_id,__ACTION__ ,"","del");
                $this->success('删除成功');
            } else {
                $condition = 'blank_id in (' . $_GET['blank_id'] . ')';
                $package = $model->table('sj_brush_adapter')->where($condition)->select();
                $model->table('sj_brush_adapter')->where($condition)->save($data);
                $packagename = '';
                foreach ($package as $key => $val) {
                    $packagename .= $val['package'] . ',';
                }
                $this->writelog('删除了blank_id为' . $blank_id . '包名为' . $packagename . '的白名单', 'sj_brush_adapter', $_GET['blank_id'],__ACTION__ ,"","del");
                $this->success('删除成功');
            }
        }
    }

    //导入下载量管理数据
    function import_download() {
        $model = new model();
        $time = time();
        $files_type = strtolower(substr(strrchr($_FILES ['add_csv']['name'], '.'), 1));
        if ($files_type == 'csv' && is_uploaded_file($_FILES["add_csv"]["tmp_name"])) {
            $file = $_FILES ['add_csv']['tmp_name'];
            $list_die = array();
            $list_ok = array();
            $contents = file_get_contents($file);
            $encoding = mb_detect_encoding($contents, array('GB2312', 'GBK', 'UTF-16', 'UCS-2', 'UTF-8', 'BIG5', 'ASCII'));
            $fp = fopen($file, "r");
            while (!feof($fp)) {
                $line = iconv($encoding, 'utf-8', trim(fgets($fp)));
                $arr = explode(',', $line);
                if ($arr) {
                    $softname = trim($arr[0]);
                    $package = trim($arr[1]);
                    $num = $arr[2];
                    $is_debut = $is_write = $is_screen = '';
                    if ($package && $num && $package != '包名' && $softname != '
							      	软件名') {
                        //echo $package.'<br>';
                        $is_package = $model->table('sj_soft')->field('hide,softid,package')->where("status=1 and package='{$package}'")->order('softid desc')->find();
                        //判断首发
                        $debut = $model->table('sj_soft_debut')->field('debut_length')->where("package = '{$package}' and del_status=1 and status=2")->find();
                        $debut_time = $debut['debut_length'] * 60 * 60 + $time;
                        if ($debut && $debut_time) {
                            $is_debut = $model->table('sj_soft_debut')->where("package = '{$package}' and del_status=1 and status=2 and debut_time>=$debut_time")->field('id,package')->find();
                        }
                        //var_dump($is_debut);
                        //echo $model->getlastsql().'<br>';
                        //判断闪屏
                        $is_screen = $model->table('sj_soft_screen')->where("package = '{$package}' and del_status=1 and status=2")->field('id')->find();
                        //判断刷量白名单
                        $is_write = $model->table('sj_brush_adapter')->where("package = '{$package}' and status=1")->find();
                        if ($is_package['hide'] == '1' && !$is_write && !$is_screen && !$is_debut && is_numeric($num)) {
                            $list_ok[] = array('package' => $package, 'num' => $num); //有效数据
                        } else {
//                            echo 1; 
                            $soft_type = '包名不存在';
                            if ($is_write) {
                                $soft_type = '白名单';
                            }
                            if ($is_screen) {
                                $soft_type = '闪屏';
                            }
                            if ($is_debut) {
                                $soft_type = '首发';
                            }
                            if ($is_package['hide'] == '3') {
                                $soft_type = '已下架';
                            }
                            if (!is_numeric($num)) {
                                $soft_type = '刷量值错误';
                            }
                            $list_die[] = array('package' => $package, 'num' => $num, 'soft_type' => $soft_type); //无效数据
                            //var_dump($list_die);
                        }
                    }
                }
            }
            fclose($fp);
            $count_ok = count($list_ok);
            $count_die = count($list_die);
            $this->assign('count_ok', $count_ok);
            $this->assign('count_die', $count_die);
            $this->assign('list_die', $list_die);
            $this->assign('list_ok', $list_ok);
            $this->display();
        } else {
            $this->error('文件格式错误!');
        }
    }

    //导入白名单
    function import_whitelist() {
        $model = new model();
        $time = time();
        $files_type = strtolower(substr(strrchr($_FILES ['add_csv']['name'], '.'), 1));
        if ($files_type == 'csv' && is_uploaded_file($_FILES["add_csv"]["tmp_name"])) {
            $file = $_FILES ['add_csv']['tmp_name'];
            $list_die = array();
            $list_ok = array();
            $contents = file_get_contents($file);
            $encoding = mb_detect_encoding($contents, array('GB2312', 'GBK', 'UTF-16', 'UCS-2', 'UTF-8', 'BIG5', 'ASCII'));
            $fp = fopen($file, "r");
            while (!feof($fp)) {
                $line = iconv($encoding, 'utf-8', trim(fgets($fp)));
                $arr = explode(',', $line);
                if ($arr) {
                    $package = trim($arr[0]);
                    $note = trim($arr[1]);
                    if ($package && $note && $package != '包名' && $note != '备注') {
                        $data['package'] = $package;
                        $data['note'] = $note;
                        $data['publish_tm'] = time();
                        $data['update_tm'] = time();
                        $data['status'] = 1;
//                        if (empty($data['package'])) {
//                            $this->error('请填写包名');
//                        }
                        if (!preg_match("/^[a-z0-9_\.]+$/i", $data['package'])) {
                            //$this->error($data['package'] . '包名格式有误');
                            $list_die[] = array('package'=>$data['package'],'soft_type'=>'包名格式错误');
                        }
                        $model = new Model();
                        $adapter_where = array('`package`' => $data['package'], '`status`' => 1);
                        $adapter_count = $model->table('sj_brush_adapter')->where($adapter_where)->count();
                        $check = true;
                        if ($adapter_count > 0&&$check) {
                            //$this->error($data['package'] . '包名已存在刷量白名单列表');
                            $list_die[] = array('package'=>$data['package'],'soft_type'=>'已存在刷量白名单');
                            $check = false;
                        }
                        $black_count = $model->table('sj_brush_black')->where($adapter_where)->count();
                        if ($black_count > 0&&$check) {
                            //$this->error($data['package'] . '包名已存在刷量黑名单列表');
                            $list_die[] = array('package'=>$data['package'],'soft_type'=>'已存在刷量黑名单');
                            $check = false;
                        }
                        if($check){
                            $soft_where = array('`package`' => $data['package'], '`status`' => 1,'`hide`' => 1);
                            $soft_count = $model->table('sj_soft')->where($soft_where)->count();
                            if ($soft_count == 0) {
                                //$this->error($data['package'] . '该软件不在已上架列表');
                                $list_die[] = array('package'=>$data['package'],'soft_type'=>'不在已上架列表');
                                $check = false;
                            }
                        }
                        if($check){
                            $map = array('`package`' => $data['package'], '`status`' => 1);
                            $soft_info = $model->table('sj_soft')->where($map)->limit(1)->field('softname')->select();
                            $data['softname'] = $soft_info[0]['softname'];
                            if ($blank_id = $model->table('sj_brush_adapter')->add($data)) {
                                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                                $list_ok[] = array('package' =>$data['package']);
                                $this->writelog('添加了blank_id为' . $blank_id . '包名为' . $data['package'] . '的白名单', 'sj_brush_adapter', $blank_id,__ACTION__ ,"","add");
                            } else {
                                //$this -> assign('jumpUrl','/index.php/Sj/DownloadBrush/brush_white_add');
                                //$this->error('添加失败');
                                $list_ok[] = array('package'=>$data['package'],'soft_type'=>'添加失败');
                            }                            
                        }

                    }
                }
            }
//            $this->success('添加成功');
            fclose($fp);
            $count_ok = count($list_ok);
            $count_die = count($list_die);
            $this->assign('count_ok', $count_ok);
            $this->assign('count_die', $count_die);
            $this->assign('list_die', $list_die);
            $this->assign('list_ok', $list_ok);
            $this->display();
        } else {
            $this->error('文件格式错误!');
        }
    }

    //导出失效包名数据
    function import_data_out() {
        for ($i = 0; $i < count($_POST['package']); $i++) {
            $package = $_POST['package'][$i];
            $num = $_POST['num'][$i];
            echo iconv('utf-8', 'utf-8', $package) . ",";
            echo iconv('utf-8', 'utf-8', $num) . "\t\n";
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="out_export.csv"');
        header('Cache-Control: max-age=0');
        $file = '/tmp/export/' . session_id() . '_' . time() . 'export' . ".csv";
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

    //导入文件数据处理（增量||扣量）
    function import_data_oper() {
        $package_die = $package_ok = $is_white_log = 0;
        $model = new model();
        $time = time();
        $soft_hide = 1;
        $softid_more = '';
        for ($i = 0; $i < count($_POST['package']); $i++) {
            $softid_more = '';
            $package = $_POST['package'][$i];
            $soft_last_data = $_POST['num'][$i];
            if ((int) $_POST['is_type'] == 1) {
                $is_count = 'add_data';
                $is_white_log = '增量:';
                $soft_res = $model->query("UPDATE `sj_soft` SET `total_downloaded_add`=total_downloaded_add+$soft_last_data WHERE package = '$package'");
            } else {
                $is_count = 'cut_data';
                $is_white_log = '扣量:';
                $softid = $model->table('sj_soft')->field('softid,dev_id')->where("package='{$package}' and status=1 and hide=1")->select();
                foreach ($softid as $key => $value) {
                    $twda['softid'] = $value['softid'];
                    $twda['update_from'] = $value['softid'];
                    $twda['_logic'] = 'or';
                    $where['_complex'] = $twda;
                    $where['status'] = array('egt', 2);
                    $tmp = $model->table('sj_soft_tmp')->where($where)->find();
                    if ($tmp) {
                        $tmp_save = $model->table('sj_soft_tmp')->where($where)->save(array('status' => 0));
                        //if(!$tmp){
                        //exit(json_encode(array('code'=>'0','msg'=>"tmp制成无效失败")));
                        //}
                    }
                    $soft_tmp = D("Dev.Softaudit");
                    //更新pu_developer字段statistics_on
                    $soft_tmp->update_developer($value['dev_id']);
                    //echo $soft_last_data.'<br>';
                    $soft_res = $model->query("UPDATE `sj_soft` SET `total_downloaded_detain`=total_downloaded_detain+$soft_last_data,`hide`=3,`deny_msg`='监控平台监控有恶意刷量行为',`last_refresh`={$time} WHERE package='{$package}' and softid={$value['softid']} ");
                    //echo $model->getlastsql();
                    $is_white_log = '扣量并下架:';
                    $model->query("UPDATE sj_reject_log SET reason='',create_tm=$time,admindid={$_SESSION['admin']['admin_id']} where softid={$softid['softid']}");
                    $download = $model->table('sj_soft')->field('total_downloaded,total_downloaded_add,total_downloaded_detain')->where("package = '{$package}' and status=1 and softid={$value['softid']} ")->find();
                    $total_downloaded_detain = $download['total_downloaded'] + $download['total_downloaded_add'] - $download['total_downloaded_detain'];
                    if ($total_downloaded_detain < 0) {

                        $model->query("UPDATE `sj_soft` SET `total_downloaded_detain`=total_downloaded_detain+$total_downloaded_detain,`hide`=3,`deny_msg`='监控平台监控有恶意刷量行为',`last_refresh`=$time WHERE package = '$package' and softid={$value['softid']} ");
                        
                        //echo $model->getlastsql();	         				
                    }
                    $softid_more.= $value['softid'] . ',';
                   // update_soft_status(array('soft_status'=>60,'update_tm'=>$time),$package);
					getSoftStatusByPackage($package);
                }

                $soft_hide = 3;
            }
            if(!empty($softid_more))
            $softid_more = substr($softid_more, 0, -1);
            $data = array('package' => $package, 'type' => 0, 'soft_hide' => $soft_hide, $is_count => $soft_last_data, 'oper_time' => $time, 'softid' => $softid_more, 'reason' => '批量操作');
            $res = $model->table('sj_brush_log')->add($data);
            if ($res) {
                $this->writelog("刷量操作记录表新增id为{$res}的记录",'sj_brush_log',$res,__ACTION__ ,"","add");
                $this->writelog('刷量操作：批量' . $is_white_log . $soft_last_data . '包名为' . $package . '的软件','sj_soft',"package:{$package}",__ACTION__ ,"","edit");
                $package_ok++;
            } else {
                $package_die++;
            }
        }
        $this->assign('jumpUrl', '/index.php/Dev/DownloadBrush/download_admin_list');
        $this->success($is_white_log . '成功：' . $package_ok . '个，失败：' . $package_die . '个');
    }

    //下载量管理
    function download_admin_list() {
        $model = new model();
        $where = '';
        if (isset($_GET['softname'])) {
            $where.=" and softname like '%{$_GET['softname']}%'";
            $this->assign('softname', $_GET['softname']);
        }
        if (isset($_GET['softid'])) {
            $where.=" and softid= '{$_GET['softid']}'";
            $this->assign('softid', $_GET['softid']);
        }
        if (isset($_GET['package'])) {
            $where.=" and package = '{$_GET['package']}'";
            $this->assign('package', $_GET['package']);
        }
        if (isset($_GET['order']) && $_GET['type'] == '1') {
            $order = " {$_GET['order']} DESC";
        } elseif (isset($_GET['order']) && $_GET['type'] == '2') {
            $order = " {$_GET['order']} ASC";
        } else {
            $order = " last_refresh desc";
        }
        if (!empty($_GET['cateid'])) {
            /* if(empty($_GET['softname']) && empty($_GET['softname']) && empty($_GET['softname'])){
              $this -> error("至少选择一个条件");
              } */
            $cateids = explode(',', $_GET['cateid']);
            $cateid = array_flip($cateids);
            $this->assign('cateid', $cateid);
            $this->assign("init_cateid", $_GET['cateid']);
            $cateidarr = '';
            foreach ($cateids as $vv) {
                if ($vv != '') {
                    $cateidarr .= "'," . $vv . ",'" . ",";
                }
            }
            $cateidarrs = substr($cateidarr, 0, -1);
            $where .= " and category_id in ({$cateidarrs})";
        }
        import('@.ORG.Page');
        $limit = 50;
        if (!empty($where)) {
            $count = $model->table('sj_soft')->where("status='1'  {$where}")->count();
            $page = new Page($count, $limit);
            $list = $model->table('sj_soft')->field('softid,softname, version_code ,package ,total_downloaded,total_downloaded_add,total_downloaded_detain,(total_downloaded +total_downloaded_add - total_downloaded_detain) as downloaded , hide,category_id')->where("status='1' {$where}")->limit($page->firstRow . ',' . $page->listRows)->order($order)->select();


            //echo $model->getlastsql();
            /* //查询软件分类表
              $category = $model->table('sj_category')->where("status = '1'")->select();
              $category_arr = array();
              foreach ($category as $key => $value) {
              $category_arr[$value['category_id']][] = $value['name'];
              } */
            /* //查询软件图标表
              $soft_file = $model->table('sj_soft_file')->field('softid,iconurl')->select();
              $soft_file_arr = array();
              foreach ($soft_file as $key => $value) {
              $soft_file_arr[$value['softid']][]= $value['iconurl'];
              } */
            //查询操作日志表
            $brush_log = $model->table('sj_brush_log')->order('oper_time DESC')->select();
            $brush_log_arr = array();
            foreach ($brush_log as $key => $value) {
                $oper_time = $value['oper_time'] ? date('Y-m-d', $value['oper_time']) . '<br />' . date('H:i:s', $value['oper_time']) : '';
                $brush_log_arr[$value['package']][] = array('oper_time' => $oper_time, 'oper_time_unix' => $value['oper_time'], 'reason' => $value['reason'], 'oper_type' => $value['type']);
            }

            foreach ($list as $key => $value) {
                $category_id = str_replace(',', '', $value['category_id']);
                $category = $model->table('sj_category')->where("status = '1' and category_id='{$category_id}'")->select();
                $list[$key]['category_name'] = $category[0]['name'];
                $iconurl = $model->table('sj_soft_file')->field('softid,iconurl')->where("softid = '{$value['softid']}'")->select();
                $list[$key]['iconurl'] = $iconurl[0]['iconurl'];
                $log_time = $brush_log_arr[$value['package']][0]['oper_time'] ? $brush_log_arr[$value['package']][0]['oper_time'] : '';
                $list[$key]['log_time'] = $log_time;
                $list[$key]['oper_time_unix'] = $brush_log_arr[$value['package']][0]['oper_time_unix'];
                $list[$key]['reason'] = $this->brush_types($brush_log_arr[$brush_list[$i]['package']][0]['oper_type']) . '<br />' . str_replace(',', '<br />', $brush_log_arr[$list[$key]['package']][0]['reason']);
                $mingdan = array('`package`' => $value['package'], '`status`' => 1);
                $white_data = $model->table('sj_brush_adapter')->where($mingdan)->limit(1)->select();
                $white_count = count($white_data);
                if ($white_count > 0) {
                    $list[$key]['white'] = 1;
                    $list[$key]['note'] = $white_data[0]['note'];
                } else {
                    $list[$key]['white'] = 0;
                    $list[$key]['note'] = '';
                }
                //暂时下载量新增  yuesai
                $tmp_downloaded = $model->table('sj_soft_tmp_downloaded')->where("status = '1' and package='{$value['package']}'")->find();
                if($tmp_downloaded){
                   
                    $list[$key]['start_tm_new'] = date("Y-m-d",$tmp_downloaded['start_tm']);
                    $list[$key]['end_tm_new'] = date("Y-m-d",$tmp_downloaded['end_tm']);
                    $list[$key]['tmp_type'] = $tmp_downloaded['type'];
                    $list[$key]['tmp_downloaded_cnt'] = $tmp_downloaded['tmp_downloaded_cnt'];
                    if($tmp_downloaded['type']==1){
                        $list[$key]['tmp_downloaded_cnt_new'] = '+'.$tmp_downloaded['tmp_downloaded_cnt'];
                    }else{
                        $list[$key]['tmp_downloaded_cnt_new'] = '-'.$tmp_downloaded['tmp_downloaded_cnt'];
                    }
                    if(strtotime(date("Y-m-d",time()))>=$tmp_downloaded['start_tm'] && strtotime(date("Y-m-d",time()))<=$tmp_downloaded['end_tm']){
                        if($tmp_downloaded['type']==1){
                            $list[$key]['downloaded'] = $list[$key]['downloaded']+$tmp_downloaded['tmp_downloaded_cnt'];
                        }else{
                            $list[$key]['downloaded'] = $list[$key]['downloaded']-$tmp_downloaded['tmp_downloaded_cnt'];
                        }
                    }
                }

            }
        }
        // echo "<pre>";var_dump($list);die;
        //软件类别
        $soft_tmp = D("Dev.Softaudit");
        $catname = $soft_tmp->getCategoryArray();
        $cname = array();
        foreach ($catname[0] as $n) {
            $threecat = array();
            foreach ($catname[$n['category_id']] as $v) {
                foreach ($catname[$v['category_id']] as $m) {
                    $threecat[] = $m;
                }
            }
            $n['sub'] = $threecat;
            $cname[] = $n;
        }
        $action_id = $model->table('sj_admin_node')->where("nodename='/index.php/Dev/DownloadBrush/brush_oper'")->getField('node_id');
        //echo $model->getlastsql();
        $action_id == 1579 && $action_id = '1586,1579';
        $this->assign('action_id', $action_id);
        $this->assign('cname', $cname);
        $this->assign('img_host', IMGATT_HOST);
        if (!empty($where)) {
            $this->assign('page', $page->show());
            $this->assign('list', $list);
            $page->setConfig('header', '篇记录');
            $page->setConfig('first', '<<');
            $page->setConfig('last', '>>');
            $this->assign('count', $count);
        }
        $this->display();
    }

    //刷量规则配置列表
    function brush_config_list() {
        $model = new model();
        $list = $model->table('sj_brush_config')->order('add_time desc')->select();
        foreach ($list as $key => $value) {
            $list[$key]['add_time'] = date('Y-m-d', $value['add_time']) . '<br />' . date('H:i:s', $value['add_time']);
        }
        $this->assign('list', $list);
        $this->display();
    }

    //添加刷量规则配置
    function brush_config_add() {
        $model = new model();
        $amount_web = trim($_POST['amount_web']);
        $amount_wap = trim($_POST['amount_wap']);
        $amount_ip = trim($_POST['amount_ip']);
        $amount_device = trim($_POST['amount_device']);
        $amount_ip_down = trim($_POST['amount_ip_down']);
        $amount_device_down = trim($_POST['amount_device_down']);
        $ratio_web = trim($_POST['ratio_web']);
        $ratio_wap = trim($_POST['ratio_wap']);
        $ratio_ip = trim($_POST['ratio_ip']);
        $ratio_device = trim($_POST['ratio_device']);
        if (!is_numeric($amount_web) || !is_numeric($amount_wap) || !is_numeric($amount_ip) || !is_numeric($amount_device) || !is_numeric($ratio_web) || !is_numeric($ratio_wap) || !is_numeric($ratio_ip) || !is_numeric($ratio_device)) {
            $this->error('请输入整型数字！');
        }
        $data = array(
            'amount_web' => $amount_web,
            'amount_wap' => $amount_wap,
            'amount_ip' => $amount_ip,
            'amount_device' => $amount_device,
            'amount_ip_down' => $amount_ip_down,
            'amount_device_down' => $amount_device_down,
            'ratio_web' => $ratio_web,
            'ratio_wap' => $ratio_wap,
            'ratio_ip' => $ratio_ip,
            'ratio_device' => $ratio_device,
            'add_time' => time(),
            'status' => 1
        );
        $res = $model->table('sj_brush_config')->add($data);
        $last_id = mysql_insert_id();
        if (mysql_affected_rows() >= 0) {
            $updates = $model->table('sj_brush_config')->where('id!=' . $last_id . '')->save(array('status' => 0));
            if (mysql_affected_rows() >= 0) {
                $this->writelog('刷量配置规则添加了ID为' . $last_id . '的刷量配置规则','sj_brush_config', $last_id,__ACTION__ ,"","add");
                $this->success('添加成功!');
            }
        } else {
            $this->success('添加失败!');
        }
    }

    //刷量配置规则的启用与停用
    function brush_config_oper() {
        $model = M('brush_config');
        $id = trim($_POST['id']);
        $status = trim($_POST['status']);
        if ($status == 1) {
            $updates = $model->where('id=' . $id . '')->save(array('status' => 0));
            $is_write = '停止';
        } else {
            $updates = $model->where('id=' . $id . '')->save(array('status' => 1));
            $updates = $model->where('id!=' . $id . '')->save(array('status' => 0)); //停用其它记录保持只有一条是启用的 
            $is_write = '启用';
        }
        if (mysql_affected_rows() >= 0) {
            $result = array('success' => true, 'msg' => '操作成功！');
            echo json_encode($result);
            $this->writelog('刷量配置规则:' . $is_write . 'ID为' . $id . '的刷量配置规则','sj_brush_config', $id,__ACTION__ ,"","edit");
            exit();
        } else {
            $result = array('success' => false, 'msg' => '操作失败！');
            echo json_encode($result);
            exit();
        }
    }

    //批量扣量规则配置 
    function brush_config_batch_list() {
        $model = new model();
        $list = $model->table('sj_brush_batch_config')->select();
        $this->assign('list', $list);
        $this->display();
    }

    //批量扣量配置规则的启用与停用
    function brush_config_batch_oper() {
        $model = M('brush_batch_config');
        $id = trim($_POST['id']);
        $status = trim($_POST['status']);
        if ($status == 1) {
            $updates = $model->where('id=' . $id . '')->save(array('status' => 0));
            $is_write = '停止';
        } else {
            $updates = $model->where('id=' . $id . '')->save(array('status' => 1));
            $updates = $model->where('id!=' . $id . '')->save(array('status' => 0)); //停用其它记录保持只有一条是启用的 
            $is_write = '启用';
        }
        if (mysql_affected_rows() >= 0) {
            $result = array('success' => true, 'msg' => '操作成功！');
            echo json_encode($result);
            $this->writelog('批量扣量配置规则:' . $is_write . 'ID为' . $id . '的扣量配置规则','sj_brush_config', $id,__ACTION__ ,"","edit");
            exit();
        } else {
            $result = array('success' => false, 'msg' => '操作失败！');
            echo json_encode($result);
            exit();
        }
    }

    function brush_config_batch_add() {
        $model = new model();
        $soft_download_first = trim($_POST['soft_download_first']);
        $down_day_first = trim($_POST['down_day_first']);
        $soft_download_second = trim($_POST['soft_download_second']);
        $down_day_second = trim($_POST['down_day_second']);
        if (!is_numeric($soft_download_first) || !is_numeric($down_day_first) || !is_numeric($soft_download_second) || !is_numeric($down_day_second)) {
            $this->error('请输入整型数字！');
        }
        $data = array(
            'soft_download_first' => $soft_download_first,
            'down_day_first' => $down_day_first,
            'soft_download_second' => $soft_download_second,
            'down_day_second' => $down_day_second,
        );
        $res = $model->table('sj_brush_batch_config')->add($data);
        $last_id = mysql_insert_id();
        if (mysql_affected_rows() >= 0) {
            $updates = $model->table('sj_brush_batch_config')->where('id!=' . $last_id . '')->save(array('status' => 0));
            if (mysql_affected_rows() >= 0) {
                $this->writelog('批量扣量配置规则添加了ID为' . $last_id . '的刷量配置规则','sj_brush_batch_config', $last_id,__ACTION__ ,"","add");
                $this->success('添加成功!');

            }
        } else {
            $this->success('添加失败!');
        }
    }

    //刷量黑名单列表
    function brush_black_list() {
        $model = new Model();
        import('@.ORG.Page');
        $param = http_build_query($_GET);
        $limit = 15;
        $where = array();
        if (isset($_GET['soft_type']) && isset($_GET['package'])) {
            $package = array();
            if ($_GET['soft_type'] == '0') {//按包名搜索
                //$where = array('`package`'=>trim($_GET['package']));
                $package = array(trim($_GET['package']));
            }
            if ($_GET['soft_type'] == '1') {//按软件名称搜索
                $soft_package = $model->table('sj_soft')->field('package')->where("softname like '{$_GET['package']}' and status=1")->select();
                $package = array();
                foreach ($soft_package as $key => $value) {
                    $package[] = $value['package'];
                }
                //$where = array('`package`'=>array('in',$package));
            }
            if ($_GET['soft_type'] == '2') {//按开发者名称搜索
                $black_data = $model->table('sj_brush_black')->field('package')->where('status=1')->select();
                $black_package = array();
                foreach ($black_data as $key => $value) {
                    $black_package[] = $value['package'];
                }
                $soft_info = $model->table('sj_soft')->field('dev_id')->where(array('package' => array('in', $black_package), 'status' => 1))->order('softid desc')->select();
                $soft_dev_id = array();
                foreach ($soft_info as $key => $value) {
                    $soft_dev_id[] = $value['dev_id'];
                }
                $dev_name = $_GET['package'];
                $dev_info = $model->table('pu_developer')->field('dev_id')->where(array('dev_id' => array('in', $soft_dev_id), 'status' => 0, 'dev_name' => array('exp', "like '%{$dev_name}%'")))->select();
                //echo $model->getlastsql();
                //var_dump($dev_info);
                $dev_id = array();
                foreach ($dev_info as $key => $value) {
                    $dev_id[] = $value['dev_id'];
                }
                $soft_package = $model->table('sj_soft')->field('package')->where(array('dev_id' => array('in', $dev_id), 'status' => 1))->order('softid desc')->select();
                $package = array();
                foreach ($soft_package as $key => $value) {
                    $package[] = $value['package'];
                }
                //$where = array('`package`'=>array('in',$package));
            }
            if ($_GET['soft_type'] == '3') {//按开发者邮箱搜索
                $black_data = $model->table('sj_brush_black')->field('package')->where('status=1')->select();
                $black_package = array();
                foreach ($black_data as $key => $value) {
                    $black_package[] = $value['package'];
                }
                $soft_info = $model->table('sj_soft')->field('dev_id')->where(array('package' => array('in', $black_package), 'status' => 1))->order('softid desc')->select();
                $soft_dev_id = array();
                foreach ($soft_info as $key => $value) {
                    $soft_dev_id[] = $value['dev_id'];
                }
                $dev_info = $model->table('pu_developer')->field('dev_id')->where(array('dev_id' => array('in', $soft_dev_id), 'status' => 0, 'email' => $_GET['package']))->select();
                //var_dump($dev_info);
                $dev_id = array();
                foreach ($dev_info as $key => $value) {
                    $dev_id[] = $value['dev_id'];
                }
                $soft_package = $model->table('sj_soft')->field('package')->where(array('dev_id' => array('in', $dev_id), 'status' => 1))->order('softid desc')->select();
                $package = array();
                foreach ($soft_package as $key => $value) {
                    $package[] = $value['package'];
                }
                //$where = array('`package`'=>array('in',$package));
            }
            //var_dump($package);
            $this->assign('package', $_GET['package']);
            $this->assign('soft_type', $_GET['soft_type']);
        }
        if (isset($_GET['dev_type'])) {
            $black_data = $model->table('sj_brush_black')->field('package')->where('status=1')->select();
            $black_package = array();
            foreach ($black_data as $key => $value) {
                $black_package[] = $value['package'];
            }
            $soft_info = $model->table('sj_soft')->field('dev_id')->where(array('package' => array('in', $black_package), 'status' => 1))->order('softid desc')->select();
            $soft_dev_id = array();
            foreach ($soft_info as $key => $value) {
                $soft_dev_id[] = $value['dev_id'];
            }
            $dev_info = $model->table('pu_developer')->field('dev_id')->where(array('dev_id' => array('in', $soft_dev_id), 'type' => $_GET['dev_type'], 'status' => 0))->select();
            $dev_id = array();
            foreach ($dev_info as $key => $value) {
                $dev_id[] = $value['dev_id'];
            }
            $soft_package = $model->table('sj_soft')->field('package')->where(array('dev_id' => array('in', $dev_id), 'status' => 1))->order('softid desc')->select();
            $package_type = array();
            foreach ($soft_package as $key => $value) {
                $package_type[] = $value['package'];
            }
            //$where = array('`package`'=>array('in',$package));
            $this->assign('dev_type', $_GET['dev_type']);
        }
        if (isset($_GET['dev_type']) && (isset($_GET['soft_type']) || isset($_GET['package']))) {

            $where = array('`package`' => array('in', array_intersect($package, $package_type)));
        }
        if ((isset($_GET['soft_type']) && isset($_GET['package']) && !isset($_GET['dev_type']))) {

            $where = array('`package`' => array('in', $package));
        }
        if (isset($_GET['dev_type']) && (!isset($_GET['soft_type']) || !isset($_GET['package']))) {

            $where = array('`package`' => array('in', $package_type));
        }
        //var_dump(array_intersect($package,$package_type));
        $where['`status`'] = 1;
        $count_total = $model->table('sj_brush_black')->where($where)->count();
        $page = new Page($count_total, $limit, $param);
        $white_list = $model->table('sj_brush_black')->where($where)
                        ->limit($page->firstRow . ',' . $page->listRows)->order('add_time desc')->select();
        //echo $model->getlastsql();
        foreach ($white_list as $key => $value) {
            $soft_info = $model->table('sj_soft')->where("package = '{$value['package']}' and status=1")->field('softname,dev_name,dev_id')->order('softid desc')->find();
            $dev_info = $model->table('pu_developer')->where("dev_id = '{$soft_info['dev_id']}' and status=0")->field('dev_name,email,type')->find();
            $white_list[$key]['softname'] = $soft_info['softname'];
            $white_list[$key]['dev_name'] = $dev_info['dev_name'] ? $dev_info['dev_name'] : $soft_info['dev_name'];
            $white_list[$key]['email'] = $dev_info['email'];
            $dev_type = '&nbsp';
            if ($dev_info['type'] == '1') {
                $dev_type = '个人';
            }
            if ($dev_info['type'] == '0') {
                $dev_type = '公司';
            }
            $white_list[$key]['dev_type'] = $dev_type;
        }
        $this->assign('white_list', $white_list);
        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('page', $page->show());
        $this->assign('count', $count_total);
        $this->display();
    }

    //刷量黑名单添加do
    function brush_black_add_do() {
        if (isset($_POST)) {
            $data['package'] = trim($_POST['package']);
            $data['add_time'] = time();
            $data['remark'] = trim($_POST['remark']);
            if (empty($data['package'])) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('请填写包名');
            }
            if (!preg_match("/^[a-z0-9_\.]+$/i", $data['package'])) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('包名格式有误');
            }
            $model = new Model();
            $adapter_where = array('`package`' => $data['package'], '`status`' => 1);
            $adapter_count = $model->table('sj_brush_adapter')->where($adapter_where)->count();
            if ($adapter_count > 0) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('包名已存在刷量白名单列表');
            }
            $black_count = $model->table('sj_brush_black')->where($adapter_where)->count();
            if ($black_count > 0) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('包名已存在刷量黑名单列表');
            }
            $soft_where = array('`package`' => $data['package'], '`status`' => 1, '`hide`' => 1);
            $soft_count = $model->table('sj_soft')->where($soft_where)->count();
            if ($soft_count == 0) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->error('该软件不在已上架列表');
            }

            if ($blank_id = $model->table('sj_brush_black')->add($data)) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->writelog('添加了blank_id为' . $blank_id . '包名为' . $data['package'] . '的黑名单', 'sj_brush_black', $blank_id,__ACTION__ ,"","add");
                $this->success('添加成功');
            } else {
                //echo $model->getlastsql();
                //$this -> assign('jumpUrl','/index.php/Sj/DownloadBrush/brush_white_add');
                $this->error('添加失败');
            }
        }
    }

    //刷量黑名单编辑
    function brush_black_edit() {
        if (isset($_POST['blank_id'])) {
            $blank_id = intval($_POST['blank_id']);
            $model = new Model();
            $where = array('`id`' => $blank_id, '`status`' => 1);
            $white_info = $model->table('sj_brush_black')->where($where)->field('id,package,remark')->find();
            if ($white_info) {
                $result = array('success' => true, 'rows' => array('package' => $white_info['package'], 'remark' => $white_info['remark'], 'blank_id' => $white_info['id']));
                $this->writelog('编辑了id为' . $white_info['id'] . '包名为' . $white_info['package'] . '的黑名单', 'sj_brush_black', $white_info['id'],__ACTION__ ,"","edit");
                echo json_encode($result);
                exit();
            } else {
                $result = array('success' => false, 'rows' => $white_info[0]);
                echo json_encode($result);
                exit();
            }
        } else {
            $result = array('success' => false, 'rows' => $white_info[0]);
            echo json_encode($result);
            exit();
        }
    }

    //提交刷量黑名单编辑
    function brush_black_edit_do() {
        if (isset($_POST)) {
            $blank_id = intval($_POST['blank_id']);
            //$data['package']    = trim($_POST['package']);
            $data['remark'] = trim($_POST['remark']);
            /* if(empty($data['package'])){
              //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
              $this -> error('请填写包名');
              }
              if(!preg_match("/^[a-z0-9_\.]+$/i",$data['package'])) {
              //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
              $this -> error('包名格式有误');
              } */
            $model = new Model();
            /* $white_where = array('`id`' => $blank_id);
              $white_info = $model -> table('sj_brush_black') -> where($white_where) -> field('package')
              -> limit(1) -> select();
              if($white_info[0]['package'] != $data['package']){
              $adapter_where = array('`package`' => $data['package'],'`status`' => 1);
              $adapter_count = $model ->table('sj_brush_black') -> where($adapter_where) -> count();
              if($adapter_count > 0){
              //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
              $this -> error('包名不允许重复');
              }
              }
              $soft_where = array('`package`' => $data['package'],'`status`' => 1);
              $soft_count = $model -> table('sj_soft') -> where($soft_where) -> count();
              if($soft_count == 0){
              //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
              $this -> error('该包名不存在于软件表中');
              } */
            $condition = array('`id`' => $blank_id);
            $log_result = $this->logcheck($condition,'sj_brush_black',$data,$model);
            if ($model->table('sj_brush_black')->where($condition)->save($data)) {
                //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
                $this->writelog('编辑了id为' . $blank_id . '包名为' . $data['package'] . '的黑名单.'.$log_result, 'sj_brush_black', $blank_id,__ACTION__ ,"","edit");
                $this->success('编辑成功');
            } else {
                //$this -> assign('jumpUrl','/index.php/Sj/DownloadBrush/brush_white_edit');
                $this->error('编辑失败');
            }
        }
    }

    //刷量黑名单删除
    function brush_black_del() {
        if (isset($_GET['blank_id'])) {
            $blank_id = intval($_GET['blank_id']);
            $data['status'] = 0;
            $condition = array('`id`' => $blank_id);
            $model = new Model();
            $package = $model->table('sj_brush_black')->where($condition)->find();
            $model->table('sj_brush_black')->where($condition)->save($data);
            //$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
            $this->writelog('删除了id为' . $blank_id . '包名为' . $package['package'] . '的黑名单', 'sj_brush_black', $blank_id,__ACTION__ ,"","del");
            $this->success('删除成功');
        }
    }
    
    //分类
    function get_catgory_str(){
        $model = new model();
        $appcategory = $model->table('sj_category')->where('parentid = 1 and status =1')->field('category_id')->select();
        $appstr = '';
        foreach($appcategory as $appkey=>$appval){
            $appstr .= $appval['category_id'].",";
            $sonapp = $model->table('sj_category')->where('parentid = "'.$appval['category_id'].'" and status =1')->field('category_id')->select();
            foreach($sonapp as $sonkey=>$sonval){
                $appstr .= $sonval['category_id'].',';
            }
        }
        $appstr = substr($appstr,0,-1);
        $gamecategory = $model->table('sj_category')->where('parentid = 2 and status =1')->field('category_id')->select();
        $gamestr = '';
        foreach($gamecategory as $gamekey=>$gameval){
            $gamestr .= $gameval['category_id'].",";
            $songame = $model->table('sj_category')->where('parentid = "'.$gameval['category_id'].'" and status =1')->field('category_id')->select();
            foreach($songame as $sonkey=>$sonval){
                $gamestr .= $sonval['category_id'].',';
            }
        }
        $gamestr = substr($gamestr,0,-1);
        //电子书
        $bookcategory = $model->table('sj_category')->where('parentid = 3 and status =1')->field('category_id')->select();
        $bookstr = '';
        foreach($bookcategory as $bookkey=>$bookval){
            $bookstr .= $bookval['category_id'].",";
            $sonbook = $model->table('sj_category')->where('parentid = "'.$bookval['category_id'].'" and status =1')->field('category_id')->select();
            foreach($sonbook as $sonkey=>$sonval){
                $bookstr .= $sonval['category_id'].',';
            }
        }
        $bookstr = substr($bookstr,0,-1);
        $cateidarr = array('1'=>$appstr,'2'=>$gamestr,'3'=>$bookstr);
        return $cateidarr;
    }
}

?>
