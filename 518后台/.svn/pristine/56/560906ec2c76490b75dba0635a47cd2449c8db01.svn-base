<?php
class HomeBottomHideExtentAction extends CommonAction {
    private $image_width = 466;
    private $image_height = 460;
    
    public function index() {
        $model = M();
        $where = array();
        $now = time();
        // 如果有搜索条件
        $search_content_type = $_GET['search_content_type'];
        if ($search_content_type) {
            $where['content_type'] = $search_content_type;
            $this->assign('search_content_type', $search_content_type);
        }
        $start_at = $_GET['start_at'];
        if ($start_at) {
            $start_at = strtotime($start_at);
            $where['start_at'] = array('egt', $start_at);
            $this->assign('start_at', $start_at);
        } else {
            $start_at = 0;
        }
        $end_at = $_GET['end_at'];
        if ($end_at) {
            $end_at = strtotime($end_at . " 23:59:59");
            $where['end_at'] = array('elt', $end_at);
            $where['end_at'] = array('exp', "<={$end_at}");
            $this->assign('end_at', $end_at);
        } else {
            $end_at = 9999999999;
        }
        // 是已过期列表还是未过期列表
        $overdue = $_GET['overdue'];
        if (!$overdue || $overdue != 1) {
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
            $order = "start_at asc";
        } else if ($overdue == -1) {
            // 如果是未过期列表，判断搜索的结束时间是否比当前要大
            if ($where['end_at']) {
                $where['end_at'][1].= " and end_at>{$now}";
            } else {
                $where['end_at'] = array('exp', ">{$now}");
            }
            $order = "start_at asc";
        }
        
        $where['status'] = 1;
        // 翻页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_homebottomhideextent_soft')->where($where)->count();
        $page  = new Page($count, $limit);
        
        $list = $model->table('sj_homebottomhideextent_soft')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($order)->select();
        
        foreach ($list as $key => $value) {
            $content_type = $value['content_type'];
            if ($content_type == 2) {
                // 活动名称
                $list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($value['activity_id']);
            } else if ($content_type == 3) {
                // 专题名称
                $list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($value['feature_id']);
            } else if ($content_type == 4) {
                // 页面名称
                $list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($value['page_type']);
            }
        }
        
        $this->assign('list', $list);
        $this->assign('overdue', $overdue);
        $this->assign('now', $now);//主要给未过期列表里判断哪些是未开始行，然后置灰展示用
        $this->assign("apkurl",IMGATT_HOST);
        $this->assign("page", $page->show());
        $this->display();
    }
    
    public function add_content() {
        if ($_POST) {
            $model = M();
            $map = array();
            $content_type = $_POST['content_type'];
            if (!$content_type) {
                $this->error("请输入类型");
            }
            if ($content_type != 1 && $content_type != 2 && $content_type != 3 && $content_type != 4 && $content_type != 5) {
                $this->error("请输入正确的类型");
            }
            $map['content_type'] = $content_type;
            if ($content_type == 1) {
                // 类型为软件
                $package = $_POST['package'];
                if (!$package) {
                    $this->error("包名不能为空");
                }
                // 检查包是否为有效包
                $where = array(
                    'package' => $package,
                    'status' => 1,
                    'hide' => 1,
                );
                $find = $model->table('sj_soft')->where($where)->find();
                if (!$find) {
                    $this->error("包名不存在");
                }
                $map['package'] = $package;
                // 获得软件详情页的展示编码
                $map['page_flag'] = ContentTypeModel::getSoftDetailPageFlag();
                // 小编推荐
                $editor_recommendation = $_POST['editor_recommendation'];
                if (!$editor_recommendation) {
                    $this->error("小编推荐不能为空");
                }
                $map['editor_recommendation'] = $editor_recommendation;
            } else {
                if ($content_type == 2) {
                    $activity_name = $_POST['activity_name'];
                    if (!$activity_name) {
                        $this->error("活动名称不能为空");
                    }
                    $activity_id = ContentTypeModel::convertActivityName2ActivityId($activity_name);
                    if (!$activity_id) {
                        $this->error("活动名称不存在 ！");
                    }
                    $map['activity_id'] = $activity_id;
                    // 获得活动详情页的展示编码
                    $map['page_flag'] = ContentTypeModel::getActivityDetailPageFlag();
                } else if ($content_type == 3) {
                    $feature_name = $_POST['feature_name'];
                    if (!$feature_name) {
                        $this->error("专题名称不能为空");
                    }
                    $feature_id = ContentTypeModel::convertFeatureName2FeatureId($feature_name);
                    if (!$feature_id) {
                        $this->error("专题名称不存在 ！");
                    }
                    $map['feature_id'] = $feature_id;
                    // 获得专题详情页的展示编码
                    $map['page_flag'] = ContentTypeModel::getFeatureDetailPageFlag();
                } else if ($content_type == 4) {
                    $general_page_type = $_POST['general_page_type'];
                    if ($general_page_type == 1) {
                        $page_name = $_POST['page_name1'];
                    } else if ($general_page_type == 2) {
                        $page_name = $_POST['page_name2'];
                    } else if ($general_page_type == 3) {
                        $page_name = $_POST['page_name3'];
                    } else if ($general_page_type == 4) {
                        $page_name = $_POST['page_name4'];
                    } else {
                        $this->error("页面类型不对");
                    }
                    if (!$page_name) {
                        $this->error("页面名称不能为空！");
                    }
                    $page_type = ContentTypeModel::convertPageName2PageType($page_name, $general_page_type);
                    if (!$page_type) {
                        $this->error("页面名称不对");
                    }
                    $map['page_type'] = $page_type;
                    // 生成page_flag和page_id值
                    $ret = ContentTypeModel::getPageFlagAndIds($page_type);
                    if ($ret['page_flag']) {
                        $map['page_flag'] = $ret['page_flag'];
                    }
                    if (!empty($ret['page_ids'])) {
                        if (isset($ret['page_ids'][0])) {
                            $map['page_id1'] = $ret['page_ids'][0];
                            if (isset($ret['page_ids'][1])) {
                                $map['page_id2'] = $ret['page_ids'][1];
                            }
                        }
                    }
                } else if ($content_type == 5) {
                    $website = $_POST['website'];
                    if (!$website)
                        $this->error("请输入网址");
                    if (!ContentTypeModel::check_url($website))
                        $this->error("请输入正确的url地址");
                    $map['website'] = $website;
                    $map['page_flag'] = ContentTypeModel::getWebsitePageFlag();
                } else {
                    $this->error("推荐类型不对");
                }
                if (!$_FILES['image_url']['name'])
                    $this->error("请上传图片");
                // 将图片存储起来
                $folder = "/img/" . date("Ym/d/");
                $this->mkDirs(UPLOAD_PATH . $folder);
                // 取得图片后缀
                $suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url']['name'],$matches);
                if ($matches) {
                    $suffix = $matches[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr = getimagesize($_FILES['image_url']['tmp_name']);
                if (!$img_info_arr) {
                    $this->error('上传图片出错！');
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                if ($width != $this->image_width || $height != $this->image_height)
                    $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
                $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                $img_path = UPLOAD_PATH . $relative_path;
                $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
                $map['image_url'] = $relative_path;
                
            }
            // 开始时间
            $map['start_at'] = strtotime($_POST['start_at']);
            $map['end_at'] = strtotime($_POST['end_at']);
            if (!$map['start_at']) {
                $this->error("开始时间不能为空");
            }
            if (!$map['end_at']) {
                $this->error("结束时间不能为空");
            }
            if ($map['start_at'] > $map['end_at']) {
                $this->error("开始时间不能大于结束时间");
            }
            // 其他
            /* 不要高低配页面
            $map['phone_dis'] = isset($_POST['phone_dis']) ? $_POST['phone_dis'] : 0;
            $map['old_phone'] = isset($_POST['old_phone']) ? $_POST['old_phone'] : 0;
            */
            $map['oid'] = isset($_POST['oid']) ? $_POST['oid'] : 0;
            $map['cid'] = isset($_POST['cid']) ? $_POST['cid'] : 0;
            // 检查冲突
            $conflict_ids = $this->check_conflict($map);
            if ($conflict_ids) {
                $this->error("与后台id为{$conflict_ids}的记录投放时间有冲突");
            }
            // 添加
            $ret = $model->table('sj_homebottomhideextent_soft')->add($map);
            if ($ret) {
                $this->writelog("首页底部隐藏推荐位：添加了id为{$ret}的记录",'sj_homebottomhideextent_soft',"{$ret}",__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        } else {
            // 运营商
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();
            $this->assign('operatinglist',$operating_list);
            $this->assign('image_width', $this->image_width);
            $this->assign('image_height', $this->image_height);
            $this->display();
        }
    }
    
    public function edit_content() {
        if ($_POST) {
            $id = $_POST['id'];
            $model = M();
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $find = $model->table('sj_homebottomhideextent_soft')->where($where)->find();
            // 编辑不允许改变类型，获得此记录数据库中的类型
            $content_type = $find['content_type'];
            $map = array();
            $map['content_type'] = $content_type;//检查冲突时需传此值
            if ($content_type == 1) {
                // 类型为软件
                $package = $_POST['package'];
                if (!$package) {
                    $this->error("包名不能为空");
                }
                // 检查包是否为有效包
                $where = array(
                    'package' => $package,
                    'status' => 1,
                    'hide' => 1,
                );
                $find = $model->table('sj_soft')->where($where)->find();
                if (!$find) {
                    $this->error("包名不存在");
                }
                $map['package'] = $package;
                // 获得软件详情页的展示编码
                $map['page_flag'] = ContentTypeModel::getSoftDetailPageFlag();
                // 小编推荐
                $editor_recommendation = $_POST['editor_recommendation'];
                if (!$editor_recommendation) {
                    $this->error("小编推荐不能为空");
                }
                $map['editor_recommendation'] = $editor_recommendation;
            } else {
                if ($content_type == 2) {
                    $activity_name = $_POST['activity_name'];
                    if (!$activity_name) {
                        $this->error("活动名称不能为空");
                    }
                    $activity_id = ContentTypeModel::convertActivityName2ActivityId($activity_name);
                    if (!$activity_id) {
                        $this->error("活动名称不存在 ！");
                    }
                    $map['activity_id'] = $activity_id;
                    // 获得活动详情页的展示编码
                    $map['page_flag'] = ContentTypeModel::getActivityDetailPageFlag();
                } else if ($content_type == 3) {
                    $feature_name = $_POST['feature_name'];
                    if (!$feature_name) {
                        $this->error("专题名称不能为空");
                    }
                    $feature_id = ContentTypeModel::convertFeatureName2FeatureId($feature_name);
                    if (!$feature_id) {
                        $this->error("专题名称不存在 ！");
                    }
                    $map['feature_id'] = $feature_id;
                    // 获得专题详情页的展示编码
                    $map['page_flag'] = ContentTypeModel::getFeatureDetailPageFlag();
                } else if ($content_type == 4) {
                    $general_page_type = $_POST['general_page_type'];
                    if ($general_page_type == 1) {
                        $page_name = $_POST['page_name1'];
                    } else if ($general_page_type == 2) {
                        $page_name = $_POST['page_name2'];
                    } else if ($general_page_type == 3) {
                        $page_name = $_POST['page_name3'];
                    } else if ($general_page_type == 4) {
                        $page_name = $_POST['page_name4'];
                    } else {
                        $this->error("页面类型不对");
                    }
                    if (!$page_name) {
                        $this->error("页面名称不能为空！");
                    }
                    $page_type = ContentTypeModel::convertPageName2PageType($page_name, $general_page_type);
                    if (!$page_type) {
                        $this->error("页面名称不对");
                    }
                    $map['page_type'] = $page_type;
                    // 生成page_flag和page_id值
                    $ret = ContentTypeModel::getPageFlagAndIds($page_type);
                    if ($ret['page_flag']) {
                        $map['page_flag'] = $ret['page_flag'];
                    }
                    if (!empty($ret['page_ids'])) {
                        if (isset($ret['page_ids'][0])) {
                            $map['page_id1'] = $ret['page_ids'][0];
                            if (isset($ret['page_ids'][1])) {
                                $map['page_id2'] = $ret['page_ids'][1];
                            }
                        }
                    }
                } else if ($content_type == 5) {
                    $website = $_POST['website'];
                    if (!$website)
                        $this->error("请输入网址");
                    if (!ContentTypeModel::check_url($website))
                        $this->error("请输入正确的url地址");
                    $map['website'] = $website;
                    $map['page_flag'] = ContentTypeModel::getWebsitePageFlag();
                } else {
                    $this->error("推荐类型不对");
                }
                if ($_FILES['image_url']['name']) {
                    // 将图片存储起来
                    $folder = "/img/" . date("Ym/d/");
                    $this->mkDirs(UPLOAD_PATH . $folder);
                    // 取得图片后缀
                    $suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url']['name'],$matches);
                    if ($matches) {
                        $suffix = $matches[0];
                    } else {
                        $this->error('上传图片格式错误！');
                    }
                    // 判断图片长和宽
                    $img_info_arr = getimagesize($_FILES['image_url']['tmp_name']);
                    if (!$img_info_arr) {
                        $this->error('上传图片出错！');
                    }
                    $width = $img_info_arr[0];
                    $height = $img_info_arr[1];
                    if ($width != $this->image_width || $height != $this->image_height)
                        $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
                    $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                    $img_path = UPLOAD_PATH . $relative_path;
                    $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
                    $map['image_url'] = $relative_path;
                }
            }
            // 开始时间
            $map['start_at'] = strtotime($_POST['start_at']);
            $map['end_at'] = strtotime($_POST['end_at'] . " 23:59:59");
            if (!$map['start_at']) {
                $this->error("开始时间不能为空");
            }
            if (!$map['end_at']) {
                $this->error("结束时间不能为空");
            }
            if ($map['start_at'] > $map['end_at']) {
                $this->error("开始时间不能大于结束时间");
            }
            // 其他
            /* 不要高低配页面
            $map['phone_dis'] = isset($_POST['phone_dis']) ? $_POST['phone_dis'] : 0;
            $map['old_phone'] = isset($_POST['old_phone']) ? $_POST['old_phone'] : 0;
            */
            $map['oid'] = isset($_POST['oid']) ? $_POST['oid'] : 0;
            $map['cid'] = isset($_POST['cid']) ? $_POST['cid'] : 0;
            
            // 检查冲突
            $conflict_ids = $this->check_conflict($map, $id);
            if ($conflict_ids) {
                $this->error("与后台id为{$conflict_ids}的记录投放时间有冲突");
            }
            
            $where = array('id' => $id);
            $log = $this->logcheck($where, 'sj_homebottomhideextent_soft', $map, $model);
            $ret = $model->table('sj_homebottomhideextent_soft')->where($where)->save($map);
            if ($ret || $ret === 0) {
                if ($ret) {
                    $this->writelog("首页底部隐藏推荐位：编辑了id为{$id}的记录，{$log}",'sj_homebottomhideextent_soft',"{$id}",__ACTION__ ,"","edit");
                }
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败");
            }
        } else {
            $model = M();
            $where = array(
                'id' => $_GET['id'],
                'status' => 1,
            );
            $find = $model->table('sj_homebottomhideextent_soft')->where($where)->find();
            // 给个默认值，要不然javascript那边可能会出错
            $find['general_page_type'] = 0;
            $find['page_name'] = "";
            
            // 获得活动名称，专题名称，页面名称
            $content_type = $find['content_type'];
            if ($content_type == 2) {
                // 活动名称
                $find['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($find['activity_id']);
            } else if ($content_type == 3) {
                // 专题名称
                $find['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($find['feature_id']);
            } else if ($content_type == 4) {
                // 页面类型
                $find['general_page_type'] = ContentTypeModel::getGeneralPageType($find['page_type']);
                // 页面名称
                $find['page_name'] = ContentTypeModel::convertPageType2PageName($find['page_type']);
            }
            $this->assign('list', $find);
            // 运营商
            $operating_db = D('Sj.Operating');
            $operating_list = $operating_db->field('oid,mname')->select();
            $this->assign('operatinglist',$operating_list);
            // 渠道
            $where = array(
                'cid' => $find['cid'],
            );
            $ch_find = $model->table('sj_channel')->where($where)->find();
            $this->assign('chname', $ch_find['chname']);
            $this->assign('image_width', $this->image_width);
            $this->assign('image_height', $this->image_height);
            $this->display();
        }
    }
    
    public function delete_content() {
        $id = $_GET['id'];
        $where = array('id' => $id);
        $map = array('status' => 0);
        $model = M();
        $ret = $model->table('sj_homebottomhideextent_soft')->where($where)->save($map);
        if ($ret) {
            $this->writelog("首页底部隐藏推荐位：删除了id为{$id}的记录",'sj_homebottomhideextent_soft',"{$id}",__ACTION__ ,"","del");
            $this->success("删除成功");
        } else {
            $this->error("删除失败");
        }
        
    }
    
    // 返回冲突id，否则返回0
    function check_conflict($record, $id = 0) {
        $content_type = $record['content_type'];
        $oid = $record['oid'];
        $cid = $record['cid'];
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
        $model = M();
        if ($content_type == 1) {
            // 查找包名
            $content_key = 'package';
            $content_value = $record['package'];
        } else if ($content_type == 2) {
            // 查找活动
            $content_key = 'activity_id';
            $content_value = $record['activity_id'];
        } else if ($content_type == 3) {
            // 查找专题
            $content_key = 'feature_id';
            $content_value = $record['feature_id'];
        } else if ($content_type == 4) {
            // 查找专题
            $content_key = 'page_type';
            $content_value = $record['page_type'];
        } else if ($content_type == 5) {
            // 查找网页
            $content_key = 'website';
            $content_value = $record['website'];
        } else {
            return false;
        }
        $where = array(
            "{$content_key}" => $content_value,
            'oid' => $oid,
            'cid' => $cid,
            'status' => 1,
            'start_at' => array('elt', $end_at),
            'end_at' => array('egt', $start_at),
        );
        if ($id) {
            $where['id'] = array('neq', $id);
        }
        $conflict_list = $model->table('sj_homebottomhideextent_soft')->where($where)->select();
        if (!empty($conflict_list)) {
            $id_arr = array();
            foreach ($conflict_list as $value) {
                $id_arr[] = $value['id'];
            }
            return implode(',', $id_arr);
        }
        return 0;
    }
}
?>