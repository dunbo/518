<?php

class GameDeskWidgetAction extends CommonAction {
    public function index() {
        // 所有运营商
        $operating_db = D('Sj.Operating');
        $operating_list = $operating_db->field('oid,mname')->select();
        $operators_key = array();
        foreach($operating_list as $v) {
            $operators_key[$v['oid']] = $v['mname'];
        }
        // 所有渠道
        $channel_model = M('channel');
        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
        $channels_key = array();
        foreach($channels as $v) {
            $channels_key[$v['cid']] = $v['chname'];
        }
        $model = M();
        $where = array();
        // 是已过期列表还是未过期列表
        $now = time();
        $overdue = $_GET['overdue'];
        if (!$overdue) {
            // 默认是未过期
            $overdue = -1;
        }
        if ($overdue == 1) {
            // 如果是已过期列表，判断搜索的结束时间是否比当前要大
            if ($where['end_at']) {
                $where['end_at'][1] .= " and end_at<{$now}";
            } else {
                $where['end_at'] = array('exp', "<{$now}");
            }
        } else if ($overdue == -1) {
            // 如果是未过期列表，判断搜索的结束时间是否比当前要大
            if ($where['end_at']) {
                $where['end_at'][1].= " and end_at>{$now}";
            } else {
                $where['end_at'] = array('exp', ">{$now}");
            }
        }
        $where['status'] = 1;
        // 分页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_game_desk_widget')->where($where)->count();
        $page  = new Page($count, $limit);
        // 当前页数据
        $list = $model->table('sj_game_desk_widget')->where($where)->order("start_at")->limit($page->firstRow . ',' . $page->listRows)->select();
        // 处理list
        foreach ($list as $key => $value) {
            $list[$key]['softname'] = $this->getSoftName($value['package']);
            $list[$key]['chname'] = $value['cid'] ? $channels_key[$value['cid']] : '不限';
            $list[$key]['oname'] = $value['oid'] ? $operators_key[$value['oid']] : '不限';
        }

        $this->assign('list', $list);
        $this->assign('apkurl', ATTACHMENT_HOST);
        $this->assign('overdue', $overdue);
        $this->assign("page", $page->show());
        $this->assign("now", $now);
        $this->display();
    }
    public function add_content() {
        if ($_POST) {
            $model = M();
            $map = array();
            // 标题
            $package = trim($_POST['package']);
            if (!$package) {
                $this->error("包名不能为空");
            }
            if (!$this->getSoftName($package)) {
                $this->error("包名不存在");
            }
            $map['package'] = $package;
			//自动下载选择
			$map['auto_download'] = $_POST['auto_download'];
			
            // 开始时间和结束时间
            $start_at = strtotime($_POST['start_at']);
            $end_at = strtotime($_POST['end_at']);
            if (!$start_at) {
                $this->error("开始时间不能为空");
            }
            if (!$end_at) {
                $this->error("结束时间不能为空");
            }
            if ($start_at > $end_at) {
                $this->error("开始时间不能大于结束时间");
            }
            $map['start_at'] = $start_at;
            $map['end_at'] = $end_at;
            // 渠道和运营商
            $map['cid'] = $_POST['cid'] ? $_POST['cid'] : 0;
            $map['oid'] = $_POST['oid'] ? $_POST['oid'] : 0;
            $conflict_id = $this->check_conflict($map);
            if ($conflict_id) {
                $this->error("包名、渠道、运营商与id为{$conflict_id}的记录相冲突");
            }
            // 添加
            $ret = $model->table('sj_game_desk_widget')->add($map);
            if ($ret) {
                $this->writelog("游戏桌面Widget：添加了id为{$ret}的记录",'sj_game_desk_widget',"{$ret}",__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败");
            }
        } else {
            // 所有运营商
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();
            $operators_key = array();
            foreach($operating_list as $v) {
                $operators_key[$v['oid']] = $v['mname'];
            }
            $this->assign('operating_list', $operating_list);
            $this->display();
        }
    }
    
    public function edit_content() {
        if ($_POST) {
            $id = $_POST['id'];
            $model = M();
            $map = array();
            // 标题
            $package = trim($_POST['package']);
            if (!$package) {
                $this->error("包名不能为空");
            }
            if (!$this->getSoftName($package)) {
                $this->error("包名不存在");
            }
            $map['package'] = $package;
			//自动下载选择
			$map['auto_download'] = $_POST['auto_download'];
			
            $start_at = strtotime($_POST['start_at']);
            $end_at = strtotime($_POST['end_at']);
            if (!$start_at) {
                $this->error("开始时间不能为空");
            }
            if (!$end_at) {
                $this->error("结束时间不能为空");
            }
            if ($start_at > $end_at) {
                $this->error("开始时间不能大于结束时间");
            }
			if($_POST['life']==1)
			{
			  if($end_at<time())
			  {
			    $this->error("您修改的复制上线的日期还是无效日期");
			  }
			}
            $map['start_at'] = $start_at;
            $map['end_at'] = $end_at;
			$map['life']=$_POST['life'];
            // 渠道和运营商
            $map['cid'] = $_POST['cid'] ? $_POST['cid'] : 0;
            $map['oid'] = $_POST['oid'] ? $_POST['oid'] : 0;
            $conflict_id = $this->check_conflict($map, $id);
            if ($conflict_id) {
                $this->error("包名、渠道、运营商与id为{$conflict_id}的记录相冲突");
            }
            // 编辑
            $where = array(
                'id' => $id
            );
            $log = $this->logcheck($where, 'sj_game_desk_widget', $map, $model);
			if($_POST['life']==1)
			{   
			    unset($map['life']);
			    $ret = $model->table('sj_game_desk_widget')->add($map);
				if ($ret || $ret === 0) {
					if ($ret) {
						$this->writelog("游戏桌面Widget：复制上线了package为{$package}的记录，{$log}",'sj_game_desk_widget',"{$ret}",__ACTION__ ,"","add");
					}
					$this->success("复制上线成功！");
				} else {
					$this->error("复制上线失败");
				}
			}
			else
			{   
			    unset($map['life']);
				$ret = $model->table('sj_game_desk_widget')->where($where)->save($map);
				if ($ret || $ret === 0) {
					if ($ret) {
						$this->writelog("游戏桌面Widget：编辑了id为{$id}的记录，{$log}",'sj_game_desk_widget',"{$id}",__ACTION__ ,"","edit");
					}
					$this->success("编辑成功！");
				} else {
					$this->error("编辑失败");
				}
			}
        } else {
            // 所有运营商
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();
            $operators_key = array();
            foreach($operating_list as $v) {
                $operators_key[$v['oid']] = $v['mname'];
            }
            // 所有渠道
            $channel_model = M('channel');
            $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
            $channels_key = array();
            foreach($channels as $v) {
                $channels_key[$v['cid']] = $v['chname'];
            }
            $model = M();
            $id = $_GET['id'];
            $find = $model->table('sj_game_desk_widget')->where("id = {$id}")->find();
            $find['chname'] = $channels_key[$find['cid']];
            $this->assign("list", $find);
            $this->assign("operating_list", $operating_list);
            $this->display();
        }
    }
    
    public function delete_content() {
        $model = M();
        $id = $_GET['id'];
        $data['status'] = 0;
        $del = $model->table('sj_game_desk_widget')->where("id = {$id}")->save($data);
        if($del) {
            $this->writelog("游戏桌面Widget：删除了id为{$id}的记录",'sj_game_desk_widget',"{$id}",__ACTION__ ,"","del");
            $this -> success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
    
    // 返回冲突id，否则返回0
    private function check_conflict($record, $id = 0) {
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
		$life=$record['life'];
        $model = M();
        $content_key = 'package';
        $content_value = $record['package'];
        $cid = $record['cid'];
        $oid = $record['oid'];
        $where = array(
            "{$content_key}" => $content_value,
            'status' => 1,
            'cid' => $cid,
            'oid' => $oid,
            'start_at' => array('elt', $end_at),
            'end_at' => array('egt', $start_at),
        );
        if ($id&&$life!=1) {
            $where['id'] = array('neq', $id);
        }
        $find = $model->table('sj_game_desk_widget')->where($where)->find();
        if ($find['id'])
            return $find['id'];
        return 0;
    }
    
    public function getSoftName($package) {
        $model = M();
        $where = array(
            'package' => $package,
            'status' => 1,
            'hide' => 1,
        );
        $find = $model->table('sj_soft')->where($where)->order('version_code desc')->find();
        if ($find['softname']) {
            return $find['softname'];
        }
        return false;
    }
}

?>