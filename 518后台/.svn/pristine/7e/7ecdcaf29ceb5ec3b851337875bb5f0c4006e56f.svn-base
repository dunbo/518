<?php

class ForceUpdateVersionConfigAction extends CommonAction {
    
    public function index() {
        // 获得所有版本号
        $util = D('Sj.Util');
        $version_list = $util->getMarketVersion(explode(',', $sj_market_push_one['version_code']));
        ksort($version_list);
        
        // 所有渠道
        $channel_model = M('channel');
        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
        $channels_key = array();
        foreach($channels as $v) {
            $channels_key[$v['cid']] = $v['chname'];
        }
        // 将通用渠道也加进去
        $channels_key[0] = '通用';
        
        // 列表数据
        $model = M();
        $where = array(
            'status' => 1,
        );
        
        // 是否有搜索条件
        if (!empty($_GET['search_force_update_version'])) {
            $search_force_update_version = $_GET['search_force_update_version'];
            $where['force_update_version'] = array('like', "%,{$search_force_update_version},%");
            $this->assign('search_force_update_version', $search_force_update_version);
        }
        if (!empty($_GET['search_start_time'])) {
            $search_start_time = strtotime($_GET['search_start_time']);
            $this->assign('search_start_time', $_GET['search_start_time']);
        } else {
            $search_start_time = 0;
        }
        if (!empty($_GET['search_end_time'])) {
            $search_end_time = strtotime($_GET['search_end_time'].":59");
            $this->assign('search_end_time', $_GET['search_end_time']);
        } else {
            $search_end_time = 9999999999;
        }
        $where['update_time'] = array('between', "{$search_start_time},{$search_end_time}");
        
        $list = $model->table('sj_force_update_version_config')->where($where)->order('update_time desc')->select();
        // 处理一下list
        foreach ($list as $key => $record) {
            // 展示的时候将版本字符串的前后逗号去掉
            $force_update_version = $record['force_update_version'];
            $force_update_version = trim($force_update_version, ',');
            if (mb_strlen($force_update_version, 'utf-8') > 20) {
                $force_update_version = mb_substr($force_update_version, 0, 20, 'utf-8') . '...';
            }
            $list[$key]['force_update_version'] = $force_update_version;
            
            // 将渠道号换成渠道名称
            $cid_str = $record['cid'];
            $cid_arr = explode(',', $cid_str);
            $cid_name = '';
            foreach ($cid_arr as $cid) {
                if (trim($cid) == '')
                    continue;
                if ($cid_name != '') {
                    $cid_name .= ',';
                }
                $cid_name .= $channels_key[$cid];
            }
            if (mb_strlen($cid_name, 'utf-8') > 20) {
                $cid_name = mb_substr($cid_name, 0, 20, 'utf-8') . '...';
            }
            $list[$key]['cid'] = $cid_name;
            
            // 文案显示缩短
            $force_update_note = $record['force_update_note'];
            if (mb_strlen($force_update_note, 'utf-8') > 20) {
                $force_update_note = mb_substr($force_update_note, 0, 20, 'utf-8') . '...';
            }
            $list[$key]['force_update_note'] = $force_update_note;
        }
        
        $this->assign('version_list', $version_list);
        $this->assign('list', $list);
        $this->display();
    }
    
    public function show_full_content() {
        $id = $_GET['id'];
        $type = $_GET['type'];
        $model = M();
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $find = $model->table('sj_force_update_version_config')->where($where)->find();
        if ($type == 1) {
            $force_update_version = $find['force_update_version'];
            $force_update_version = trim($force_update_version, ',');
            $content = $force_update_version;
        } else if ($type == 2){
            // 所有渠道
            $channel_model = M('channel');
            $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
            $channels_key = array();
            foreach($channels as $v) {
                $channels_key[$v['cid']] = $v['chname'];
            }
            // 将通用渠道也加进去
            $channels_key[0] = '通用';
            // 将渠道号换成渠道名称
            $cid_str = $find['cid'];
            $cid_arr = explode(',', $cid_str);
            $cid_name = '';
            foreach ($cid_arr as $cid) {
                if (trim($cid) == '')
                    continue;
                if ($cid_name != '') {
                    $cid_name .= ',';
                }
                $cid_name .= $channels_key[$cid];
            }
            $content = $cid_name;
        } else if ($type == 3) {
            $force_update_note = $find['force_update_note'];
            $force_update_note = trim($force_update_note, ',');
            $content = $force_update_note;
        }
        
        $this->assign('content', $content);
        $this->display();
    }
    
    public function add_content() {
        if ($_POST) {
            $data = array();
            $force_update_version = trim($_POST['force_update_version']);
            if (!$force_update_version) {
                $this->error("强制升级版本不能为空！");
            }
            $data['force_update_version'] = $force_update_version;
            
            $cid_arr = $_POST['cid'];
            if ($cid_arr) {
                $cid = trim(implode(',', $cid_arr));
                $data['cid'] = ",{$cid},";
            } else {
                $data['cid'] = "";
            }
            
            // 检查冲突
            $conflict_content = $this->check_conflict($data['force_update_version'], $data['cid']);
            if ($conflict_content) {
                $this->error($conflict_content);
            }
            
            $force_update_note = trim($_POST['force_update_note']);
            if (!$force_update_note) {
                $this->error("强制升级文案不能为空");
            }
            // 字数限制在100字以内
            if (mb_strlen($force_update_note, 'utf-8') > 100) {
                $this->error("强制升级文案不能超过100个字！");
            }
            $data['force_update_note'] = $force_update_note;
            
            // 默认的
            $data['create_time'] = $data['update_time'] =  time();
            $data['status'] = 1;
            
            $model = M();
            $ret = $model->table('sj_force_update_version_config')->add($data);
            if ($ret) {
                $this->writelog("强制升级：添加了id为{$ret}的记录",'sj_force_update_version_config',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            
        } else {
            $this->display();
        }
    }
    
    public function edit_content() {
        if ($_POST) {
            $id = $_POST['id'];
            $data = array();
            $force_update_version = trim($_POST['force_update_version']);
            if (!$force_update_version) {
                $this->error("强制升级版本不能为空！");
            }
            $data['force_update_version'] = $force_update_version;
            
            $cid_arr = $_POST['cid'];
            if ($cid_arr) {
                $cid = trim(implode(',', $cid_arr));
                $data['cid'] = ",{$cid},";
            } else {
                $data['cid'] = "";
            }
            
            // 检查冲突
            $conflict_content = $this->check_conflict($data['force_update_version'], $data['cid'], $id);
            if ($conflict_content) {
                $this->error($conflict_content);
            }
            
            $force_update_note = trim($_POST['force_update_note']);
            if (!$force_update_note) {
                $this->error("强制升级文案不能为空");
            }
            // 字数限制在100字以内
            if (mb_strlen($force_update_note, 'utf-8') > 100) {
                $this->error("强制升级文案不能超过100个字！");
            }
            $data['force_update_note'] = $force_update_note;
            
            // 默认的
            $data['update_time'] =  time();
            
            $model = M();
            $where = array(
                'id' => $id,
            );
            $log = $this->logcheck($where, 'sj_force_update_version_config', $data, $model);
            $ret = $model->table('sj_force_update_version_config')->where($where)->save($data);
            if ($ret || $ret === 0) {
                if ($ret) {
                    $this->writelog("强制升级：编辑了id为{$id}的记录，{$log}",'sj_force_update_version_config',$id,__ACTION__ ,"","edit");
                }
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
            
        } else {
            $id = $_GET['id'];
            $model = M();
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $record = $model->table('sj_force_update_version_config')->where($where)->find();
            // 展示的时候将版本字符串的前后逗号去掉
            $force_update_version = $record['force_update_version'];
            $record['force_update_version_show'] = trim($force_update_version, ',');
            
            // 所有渠道
            $channel_model = M('channel');
            $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
            $channels_key = array();
            foreach($channels as $v) {
                $channels_key[$v['cid']] = $v['chname'];
            }
            // 将通用渠道也加进去
            $channels_key[0] = '通用';
            
            // 渠道字符串转成渠道数组
            $cid = $record['cid'];
            $cid_arr_tmp = explode(',', $cid);
            
            $cid_arr = array();
            foreach ($cid_arr_tmp as $cid) {
                if (trim($cid) == '')
                    continue;
                $cid_arr[$cid] = $channels_key[$cid];
            }
            
            $this->assign('record', $record);
            $this->assign('cid_arr', $cid_arr);
            $this->display();
        }
    }
    
    public function delete_content() {
        $id = $_GET['id'];
        $model = M();
        $where = array(
            'id' => $id,
        );
        $data = array(
            'update_time' => time(),
            'status' => 0,
        );
        $ret = $model->table('sj_force_update_version_config')->where($where)->save($data);
        if ($ret) {
            $this->writelog("强制升级：删除了id为{$id}的记录",'sj_force_update_version_config',$id,__ACTION__ ,"","del");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    private function check_conflict($version_str, $cid_str, $except_id = 0) {
        $version_arr = explode(',', $version_str);
        if (!empty($cid_str)) {
            $cid_arr = explode(',', $cid_str);
        } else {
            $cid_arr = array();
        }
        
        // 所有渠道
        $channel_model = M('channel');
        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
        $channels_key = array();
        foreach($channels as $v) {
            $channels_key[$v['cid']] = $v['chname'];
        }
        // 将通用渠道也加进去
        $channels_key[0] = '通用';
        
        $conflict_arr = array();
        $model = M();
        foreach ($version_arr as $version) {
            if (trim($version) == '')
                continue;
            if (empty($cid_arr)) {
                // 只要添加过此版本的强制升级，不管cid是否为什么，都是冲突
                $where = array(
                    'force_update_version' => array('like', "%,{$version},%"),
                    //'cid' => '',
                    'status' => 1,
                );
                if ($except_id) {
                    $where['id'] = array('neq', $except_id);
                }
                $find = $model->table('sj_force_update_version_config')->where($where)->find();
                if ($find) {
                    $conflict_arr[$version] = '';
                }
            } else {
                foreach ($cid_arr as $cid) {
                    if (trim($cid) == '')
                        continue;
                    // 该版本的空渠道是否已添加过（空渠道表示所有的渠道）
                    $where = array(
                        'force_update_version' => array('like', "%,{$version},%"),
                        'cid' => '',
                        'status' => 1,
                    );
                    if ($except_id) {
                        $where['id'] = array('neq', $except_id);
                    }
                    $find = $model->table('sj_force_update_version_config')->where($where)->find();
                    if ($find) {
                        $conflict_arr[$version] = '';
                        continue;
                    }
                    // 查找该版本的该渠道是否已被添加过
                    $where = array(
                        'force_update_version' => array('like', "%,{$version},%"),
                        'cid' => array('like', "%,{$cid},%"),
                        'status' => 1,
                    );
                    if ($except_id) {
                        $where['id'] = array('neq', $except_id);
                    }
                    $find = $model->table('sj_force_update_version_config')->where($where)->find();
                    if ($find) {
                        $conflict_arr[$version] = $cid;
                    }
                }
            }
        }
        
        $conflict_content = '';
        foreach ($conflict_arr as $version => $cid) {
            if ($conflict_content != '') {
                $conflict_content .= '，';
            }
            if ($cid === '') {
                $conflict_content .= "版本【{$version}】已强制升级";
            } else {
                $cid_name = $channels_key[$cid];
                $conflict_content .= "版本【{$version}】的渠道【{$cid_name}】已强制升级";
            }
        }
        
        return $conflict_content;
    }
}