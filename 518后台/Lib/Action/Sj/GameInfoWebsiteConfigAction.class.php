<?php
    class GameInfoWebsiteConfigAction extends CommonAction {
        
        // 站点列表
        public function website_list() {
            $model = D('Sj.GameInfoCollection');
            $where = array(
                'status' => 1,
            );
            // 记录分页参数
            $url_param = "";
            foreach ($_GET as $key => $value) {
                $_GET[$key] = trim($value);
                if ($url_param != "")
                    $url_param .= "&";
                $url_param .= "{$key}={$value}";
            }
            $count = $model->table('caiji_game_info_config')->where($where)->count();
            import("@.ORG.Page");
            $page = new Page($count, 20);
            $show = $page->show();//分页显示输出
            
            $list = $model->table('caiji_game_info_config')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('url_param', $url_param);
            $this->display('website_list');
        }
        
        // 启用/停用
        public function toggle_enabled_status() {
            if ($_GET['id']) {
                $id = $_GET['id'];
                $where = array(
                    'id' => $id,
                    'status' => 1,
                );
                $model = D('Sj.GameInfoCollection');
                $find = $model->table('caiji_game_info_config')->where($where)->find();
                if ($find) {
                    $enabled = ($find['enabled'] == 1 ? 0 : 1);
                    $map = array(
                        'enabled' => $enabled,
                    );
                    $ret = $model->table('caiji_game_info_config')->where($where)->save($map);
                    $hint = ($enabled == 1 ? '启用' : '停用');
                    if ($ret) {
                        $this->writelog("网游_资讯采集站点：{$hint}了id为{$id}的站点",'caiji_game_info_config',"{$id}",__ACTION__ ,"","edit");
                        $this->success("{$hint}成功！");
                    } else {
                        $this->error("{$hint}失败！");
                    }
                } else {
                    $this->error("没有找到id为【{$id}】为记录！");
                }
            }
        }
        
        public function del() {
            if ($_GET['id']) {
                $id = $_GET['id'];
                $where = array(
                    'id' => $id,
                    'status' => 1,
                );
                $model = D('Sj.GameInfoCollection');
                $find = $model->table('caiji_game_info_config')->where($where)->find();
                if ($find) {
                    $map = array(
                        'status' => 0,
                    );
                    $ret = $model->table('caiji_game_info_config')->where($where)->save($map);
                    if ($ret) {
                        $this->writelog("网游_资讯采集站点：删除了id为{$id}的站点",'caiji_game_info_config',"{$id}",__ACTION__ ,"","del");
                        $this->success("删除成功！");
                    } else {
                        $this->error("删除失败！");
                    }
                } else {
                    $this->error("没有找到id为【{$id}】为记录！");
                }
            }
        }
        
        // 添加网站，fix：已添加过的website_url不再添加
        public function add_website() {
            if ($_POST) {
                // trim一下所有数据
                foreach ($_POST as $key => $value) {
                    $_POST[$key] = trim($value);
                }
                $data = array();
                // 必填，且可直接赋值字段
                $field_nes = array('website_name', 'website_url', 'info_type', 'cell_str', 'cell_title_str', 'cell_title_value',
                    'cell_articleurl_str', 'cell_articleurl_value', 'article_detail_str', 'article_detail_value');
                foreach ($field_nes as $key => $value) {
                    if (!array_key_exists($value, $_POST))
                        $this->error("{$value}不能为空！");
                    $data[$value] = $_POST[$value];
                }
                // 非必填，且可直接赋值
                $field_unnes = array('remark', 'cell_brief_content_str', 'cell_brief_content_value', 'cell_img_str', 'cell_img_value', 'article_time_str',
                    'article_time_value', 'article_time_rm_reg', 'article_author_str', 'article_author_value', 'article_author_rm_reg', 'article_image_str', 'article_image_value', 'article_page_str', 'article_rm_reg');
                foreach ($field_unnes as $key => $value) {
                    if (array_key_exists($value, $_POST))
                        $data[$value] = $_POST[$value];
                }
                // 默认数据
                $now = time();
                $data['create_time'] = $data['update_time'] = $now;
                $data['status'] = 1;
                $data['enabled'] = 1;
                // 存储数据
                $model = D('Sj.GameInfoCollection');
                $ret = $model->table('caiji_game_info_config')->add($data);
                if ($ret) {
                    $this->writelog("网游_资讯采集站点：添加了id为{$ret}的站点",'caiji_game_info_config',"{$ret}",__ACTION__ ,"","add");
                    $this->assign('jumpUrl', '/index.php/Sj/GameInfoWebsiteConfig/website_list?' . $_POST['url_param']);
                    $this->success("添加成功！");
                } else {
                    $this->error("添加失败");
                }
            }
            if ($_GET) {
                // 记录分页参数
                $url_param = "";
                foreach ($_GET as $key => $value) {
                    $_GET[$key] = trim($value);
                    if ($url_param != "")
                        $url_param .= "&";
                    $url_param .= "{$key}={$value}";
                }
                $this->assign('url_param', $url_param);
            }
            $this->display('add_website');
        }
        
        public function edit_website() {
            if ($_POST) {
                // trim一下所有数据
                foreach ($_POST as $key => $value) {
                    $_POST[$key] = trim($value);
                }
                $data = array();
                // 必填，且可直接赋值字段
                $field_nes = array('website_name', 'website_url', 'info_type', 'cell_str', 'cell_title_str', 'cell_title_value',
                    'cell_articleurl_str', 'cell_articleurl_value', 'article_detail_str', 'article_detail_value');
                foreach ($field_nes as $key => $value) {
                    if (!array_key_exists($value, $_POST))
                        $this->error("{$value}不能为空！");
                    $data[$value] = $_POST[$value];
                }
                // 非必填，且可直接赋值
                $field_unnes = array('cell_brief_content_str', 'cell_brief_content_value', 'cell_img_str', 'cell_img_value', 'article_time_str',
                    'article_time_value', 'article_time_rm_reg', 'article_author_str', 'article_author_value', 'article_author_rm_reg', 'article_image_str', 'article_image_value', 'article_page_str', 'article_rm_reg', 'remark');
                foreach ($field_unnes as $key => $value) {
                    if (array_key_exists($value, $_POST))
                        $data[$value] = $_POST[$value];
                }
                // 默认数据
                $now = time();
                $data['update_time'] = $now;
                // 存储数据
                $model = D('Sj.GameInfoCollection');
                $log = $this->logcheck(array('id' => $_POST['id']), 'caiji_game_info_config', $data, $model);
                $ret = $model->table('caiji_game_info_config')->where(array('id' => $_POST['id']))->save($data);
                if ($ret) {
                    $this->writelog("网游_资讯采集站点：编辑了id为{$_POST['id']}的站点，{$log}",'caiji_game_info_config',"{$_POST['id']}",__ACTION__ ,"","edit");
                    $this->assign('jumpUrl', '/index.php/Sj/GameInfoWebsiteConfig/website_list?'. $_POST['url_param']);
                    $this->success("编辑成功！");
                } else {
                    $this->error("编辑失败");
                }
            } else if ($_GET['id']) {
                // 记录分页参数
                $url_param = "";
                foreach ($_GET as $key => $value) {
                    $_GET[$key] = trim($value);
                    if ($key == 'id') {
                        continue;
                    }
                    if ($url_param != "")
                        $url_param .= "&";
                    $url_param .= "{$key}={$value}";
                }
                $id = $_GET['id'];
                $where = array(
                    'id' => $id,
                    'status' => 1,
                );
                $model = D('Sj.GameInfoCollection');
                $find = $model->table('caiji_game_info_config')->where($where)->find();
                if ($find) {
                    // 字段值里可能包含引号等特殊html字符，需转义一下
                    foreach ($find as $key => $value) {
                        $new_find[$key] = htmlspecialchars($value);
                    }
                    $this->assign('website', $new_find);
                    $this->assign('url_param', $url_param);
                    $this->display('edit_website');
                } else {
                    $this->error("没有找到id为【{$id}】为记录");
                }
            }
        }
    }
?>