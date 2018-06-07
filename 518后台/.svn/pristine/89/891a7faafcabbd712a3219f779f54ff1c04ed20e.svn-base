<?php

class AddPackageWebsiteConfigAction extends CommonAction {
    
    public function index() {
        $model = D('Caiji.UpdatePackageWebsiteConfig');
        
        $where = array(
            'status' => array('neq', 0)
        );
        
        $list = $model->table('cj_add_package_website_config')->where($where)->order('status, priority asc')->select();
        
        $this->assign('list', $list);
        $this->display('index');
    }
    
    public function toggle_enabled_status() {
        $id = $_GET['id'];
        $current_status = $_GET['current_status'];
        
        $new_status = $current_status == 1 ? 2 : 1;
        $hint = $current_status == 1 ? '禁用' : '启用';
        
        $where = array(
            'id' => $id
        );
        $data = array(
            'update_time' => time(),
            'status' => $new_status
        );
        
        $model = D('Caiji.UpdatePackageWebsiteConfig');
        $ret = $model->table('cj_add_package_website_config')->where($where)->save($data);
        if ($ret) {
            $this->writelog("采集/质管--配置管理--新增网站配置：{$hint}了id为{$id}的记录",'cj_add_package_website_config',$id,__ACTION__ ,"","edit");
            $this->success("{$hint}成功！");
        } else {
            $this->error("{$hint}失败！");
        }
    }
    
    public function delete_website() {
        $id = $_GET['id'];
        $where = array(
            'id' => $id
        );
        $data = array(
            'update_time' => time(),
            'status' => 0
        );
        $model = D('Caiji.UpdatePackageWebsiteConfig');
        $ret = $model->table('cj_add_package_website_config')->where($where)->save($data);
        if ($ret) {
            $this->writelog("采集/质管--配置管理--新增网站配置：删除了id为{$id}的记录",'cj_add_package_website_config',$id,__ACTION__ ,"","del");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    public function add_website() {
        if ($_POST) {
            $data = array();
            $website_name = trim($_POST['website_name']);
            if (!$website_name) {
                $this->error("网站名称不能为空！");
            }
            $data['website_name'] = $website_name;
            
            $priority = trim($_POST['priority']);
            if (!$priority) {
                $priority = 1;
            } else {
                // 必需填数字
                if (!preg_match('/\d+/', $priority)) {
                    $this->error("优先级必须是正整数！");
                }
            }
            $data['priority'] = $priority;
            
            $remark = trim($_POST['remark']);
            $data['remark'] = $remark ? $remark : '';
            
            $entrance_type = trim($_POST['entrance_type']);
            if (!$entrance_type) {
                $this->error("入口方式不能为空！");
            }
            $data['entrance_type'] = $entrance_type;
            
            // 入口url或api
            $fake_list_url = trim($_POST['fake_list_url']);
            if (!$fake_list_url) {
                $this->error("入口方式不能为空！");
            }
            $data['fake_list_url'] = $fake_list_url;
            
            if ($entrance_type == 1) {
            
                $max_page_dom_str = trim($_POST['max_page_dom_str']);
                if (!$this->check_dom_str($max_page_dom_str)) {
                    $this->error("请填写合法的页码dom_str");
                }
                $data['max_page_dom_str'] = $max_page_dom_str;
                
                $max_page_dom_value = trim($_POST['max_page_dom_value']);
                $data['max_page_dom_value'] = $max_page_dom_value;
                
                $max_page_dom_reg = trim($_POST['max_page_dom_reg']);
                if (!$this->check_dom_reg($max_page_dom_reg)) {
                    $this->error("请填写合法的页面dom_reg");
                }
                $data['max_page_dom_reg'] = $max_page_dom_reg;
                
                if ($max_page_dom_str && !$max_page_dom_value) {
                    $this->error("请填写详情页值配置");
                }
                
                if (!$max_page_dom_str && !$max_page_dom_reg) {
                    $this->error("请填写页码相关配置");
                }
                
                // cell
                $cell_dom_str = trim($_POST['cell_dom_str']);
                if (!$this->check_dom_str($cell_dom_str)) {
                    $this->error("请填写合法的cell的dom_str");
                }
                $data['cell_dom_str'] = $cell_dom_str;
                if (!$cell_dom_str) {
                    $this->error("请填写cell配置");
                }
                
                $detail_url_dom_str = trim($_POST['detail_url_dom_str']);
                if (!$this->check_dom_str($detail_url_dom_str)) {
                    $this->error("请填写合法的详情页url的dom_str");
                }
                $data['detail_url_dom_str'] = $detail_url_dom_str;
                
                $detail_url_dom_value = trim($_POST['detail_url_dom_value']);
                $data['detail_url_dom_value'] = $detail_url_dom_value;
                
                $detail_url_dom_reg = trim($_POST['detail_url_dom_reg']);
                if (!$this->check_dom_reg($detail_url_dom_reg)) {
                    $this->error("请填写合法的详情页url的dom_reg");
                }
                $data['detail_url_dom_reg'] = $detail_url_dom_reg;
                
                if ($detail_url_dom_str && !$detail_url_dom_value) {
                    $this->error("请填写详情页值配置");
                }
                
                if (!$detail_url_dom_str && !$detail_url_dom_reg) {
                    $this->error("请填写详情页相关配置");
                }
                
                $cell_package_dom_str = trim($_POST['cell_package_dom_str']);
                if (!$this->check_dom_str($cell_package_dom_str)) {
                    $this->error("请填写合法的cell中包名的dom_str");
                }
                $data['cell_package_dom_str'] = $cell_package_dom_str;
                
                $cell_package_dom_value = trim($_POST['cell_package_dom_value']);
                $data['cell_package_dom_value'] = $cell_package_dom_value;
                
                $cell_package_dom_reg = trim($_POST['cell_package_dom_reg']);
                if (!$this->check_dom_reg($cell_package_dom_reg)) {
                    $this->error("请填写合法的cell中包名的dom_reg");
                }
                $data['cell_package_dom_reg'] = $cell_package_dom_reg;
                
                $cell_download_count_dom_str = trim($_POST['cell_download_count_dom_str']);
                if (!$this->check_dom_str($cell_download_count_dom_str)) {
                    $this->error("请填写合法的cell中下载量的dom_str");
                }
                $data['cell_download_count_dom_str'] = $cell_download_count_dom_str;
                
                $cell_download_count_dom_value = trim($_POST['cell_download_count_dom_value']);
                $data['cell_download_count_dom_value'] = $cell_download_count_dom_value;
                
                $cell_download_count_dom_reg = trim($_POST['cell_download_count_dom_reg']);
                if (!$this->check_dom_reg($cell_download_count_dom_reg)) {
                    $this->error("请填写合法的cell中下载量的dom_reg");
                }
                $data['cell_download_count_dom_reg'] = $cell_download_count_dom_reg;
                
            } else {
                $apparr_route = trim($_POST['apparr_route']);
                if (!$apparr_route) {
                    $this->error("请填写app数组路由");
                }
                $data['apparr_route'] = $apparr_route;
                $apparr_package_route = trim($_POST['apparr_package_route']);
                if (!$apparr_package_route) {
                    $this->error("app数组内包名路由");
                }
                $data['apparr_package_route'] = $apparr_package_route;
                $fake_detail_url = trim($_POST['fake_detail_url']);
                if (!$fake_detail_url) {
                    $this->error("请填写替换url");
                }
                $data['fake_detail_url'] = $fake_detail_url;
            }
            $cookie_open = trim($_POST['cookie_open']);
            $data['cookie_open'] = $cookie_open ? 1 : 0;
            
            $package_dom_str = trim($_POST['package_dom_str']);
            if (!$this->check_dom_str($package_dom_str)) {
                $this->error("请填写合法的包名dom_str");
            }
            $data['package_dom_str'] = $package_dom_str;
            
            $package_dom_value = trim($_POST['package_dom_value']);
            $data['package_dom_value'] = $package_dom_value;
            
            $package_dom_reg = trim($_POST['package_dom_reg']);
            if (!$this->check_dom_reg($package_dom_reg)) {
                $this->error("请填写合法的包名dom_reg");
            }
            $data['package_dom_reg'] = $package_dom_reg;
            
            $version_code_dom_str = trim($_POST['version_code_dom_str']);
            if (!$this->check_dom_str($version_code_dom_str)) {
                $this->error("请填写合法的版本号的dom_str");
            }
            $data['version_code_dom_str'] = $version_code_dom_str;
            
            $version_code_dom_value = trim($_POST['version_code_dom_value']);
            $data['version_code_dom_value'] = $version_code_dom_value;
            
            if ($version_code_dom_str && !$version_code_dom_value) {
                $this->error("请填写版本号值配置");
            }
            
            $version_code_dom_reg = trim($_POST['version_code_dom_reg']);
            if (!$this->check_dom_reg($version_code_dom_reg)) {
                $this->error("请填写合法的版本号的dom_reg");
            }
            $data['version_code_dom_reg'] = $version_code_dom_reg;
            
            $version_name_dom_str = trim($_POST['version_name_dom_str']);
            if (!$this->check_dom_str($version_name_dom_str)) {
                $this->error("请填写合法的版本名称的dom_str");
            }
            $data['version_name_dom_str'] = $version_name_dom_str;
            
            $version_name_dom_value = trim($_POST['version_name_dom_value']);
            $data['version_name_dom_value'] = $version_name_dom_value;
            
            if ($version_name_dom_str && !$version_name_dom_value) {
                $this->error("请填写版本名称值配置");
            }
            
            $version_name_dom_reg = trim($_POST['version_name_dom_reg']);
            if (!$this->check_dom_reg($version_name_dom_reg)) {
                $this->error("请填写合法的版本名称的dom_reg");
            }
            $data['version_name_dom_reg'] = $version_name_dom_reg;
            
            if ((!$version_code_dom_str && !$version_code_dom_reg) && (!$version_name_dom_str && !$version_name_dom_reg)) {
                $this->error("请填写版本号名版本名称相关配置");
            }
            
            $softname_dom_str = trim($_POST['softname_dom_str']);
            if (!$this->check_dom_str($softname_dom_str)) {
                $this->error("请填写合法的软件名称的dom_str");
            }
            $data['softname_dom_str'] = $softname_dom_str;
            
            $softname_dom_value = trim($_POST['softname_dom_value']);
            $data['softname_dom_value'] = $softname_dom_value;
            
            $softname_dom_reg = trim($_POST['softname_dom_reg']);
            if (!$this->check_dom_reg($softname_dom_reg)) {
                $this->error("请填写合法的软件名称的dom_reg");
            }
            $data['softname_dom_reg'] = $softname_dom_reg;
            
            // 广告
            $is_ad_dom_str = trim($_POST['is_ad_dom_str']);
            if (!$this->check_dom_str($is_ad_dom_str)) {
                $this->error("请填写合法的有广告的dom_str");
            }
            $data['is_ad_dom_str'] = $is_ad_dom_str;
            
            $is_ad_dom_value = trim($_POST['is_ad_dom_value']);
            $data['is_ad_dom_value'] = $is_ad_dom_value;
            
            $is_ad_dom_reg = trim($_POST['is_ad_dom_reg']);
            if (!$this->check_dom_reg($is_ad_dom_reg)) {
                $this->error("请填写合法的有广告dom_reg");
            }
            $data['is_ad_dom_reg'] = $is_ad_dom_reg;
            
            // 安全
            $is_safe_dom_str = trim($_POST['is_safe_dom_str']);
            if (!$this->check_dom_str($is_safe_dom_str)) {
                $this->error("请填写合法的安全dom_str");
            }
            $data['is_safe_dom_str'] = $is_safe_dom_str;
            
            $is_safe_dom_value = trim($_POST['is_safe_dom_value']);
            $data['is_safe_dom_value'] = $is_safe_dom_value;
            
            $is_safe_dom_reg = trim($_POST['is_safe_dom_reg']);
            if (!$this->check_dom_reg($is_safe_dom_reg)) {
                $this->error("请填写合法的安全dom_reg");
            }
            $data['is_safe_dom_reg'] = $is_safe_dom_reg;
            
            
            // 官方
            $is_office_dom_str = trim($_POST['is_office_dom_str']);
            if (!$this->check_dom_str($is_office_dom_str)) {
                $this->error("请填写合法的官方dom_str");
            }
            $data['is_office_dom_str'] = $is_office_dom_str;
            
            $is_office_dom_value = trim($_POST['is_office_dom_value']);
            $data['is_office_dom_value'] = $is_office_dom_value;
            
            $is_office_dom_reg = trim($_POST['is_office_dom_reg']);
            if (!$this->check_dom_reg($is_office_dom_reg)) {
                $this->error("请填写合法的官方dom_reg");
            }
            $data['is_office_dom_reg'] = $is_office_dom_reg;
            
            
            $category_name_dom_str = trim($_POST['category_name_dom_str']);
            if (!$this->check_dom_str($category_name_dom_str)) {
                $this->error("请填写合法的分类dom_str");
            }
            $data['category_name_dom_str'] = $category_name_dom_str;
            
            $category_name_dom_value = trim($_POST['category_name_dom_value']);
            $data['category_name_dom_value'] = $category_name_dom_value;
            
            $category_name_dom_reg = trim($_POST['category_name_dom_reg']);
            if (!$this->check_dom_reg($category_name_dom_reg)) {
                $this->error("请填写合法的分类dom_reg");
            }
            $data['category_name_dom_reg'] = $category_name_dom_reg;
            
            $keyword_dom_str = trim($_POST['keyword_dom_str']);
            if (!$this->check_dom_str($keyword_dom_str)) {
                $this->error("请填写合法的关键字dom_str");
            }
            $data['keyword_dom_str'] = $keyword_dom_str;
            
            $keyword_dom_value = trim($_POST['keyword_dom_value']);
            $data['keyword_dom_value'] = $keyword_dom_value;
            
            $keyword_dom_reg = trim($_POST['keyword_dom_reg']);
            if (!$this->check_dom_reg($keyword_dom_reg)) {
                $this->error("请填写合法的关键字dom_reg");
            }
            $data['keyword_dom_reg'] = $keyword_dom_reg;
            
            $update_log_dom_str = trim($_POST['update_log_dom_str']);
            if (!$this->check_dom_str($update_log_dom_str)) {
                $this->error("请填写合法的更新日志dom_str");
            }
            $data['update_log_dom_str'] = $update_log_dom_str;
            
            $update_log_dom_value = trim($_POST['update_log_dom_value']);
            $data['update_log_dom_value'] = $update_log_dom_value;
            
            $update_log_dom_reg = trim($_POST['update_log_dom_reg']);
            if (!$this->check_dom_reg($update_log_dom_reg)) {
                $this->error("请填写合法的更新日志dom_reg");
            }
            $data['update_log_dom_reg'] = $update_log_dom_reg;
            
            $description_dom_str = trim($_POST['description_dom_str']);
            if (!$this->check_dom_str($description_dom_str)) {
                $this->error("请填写合法的描述dom_str");
            }
            $data['description_dom_str'] = $description_dom_str;
            
            $description_dom_value = trim($_POST['description_dom_value']);
            $data['description_dom_value'] = $description_dom_value;
            
            $description_dom_reg = trim($_POST['description_dom_reg']);
            if (!$this->check_dom_reg($description_dom_reg)) {
                $this->error("请填写合法的描述dom_reg");
            }
            $data['description_dom_reg'] = $description_dom_reg;
            
            $download_count_dom_str = trim($_POST['download_count_dom_str']);
            if (!$this->check_dom_str($download_count_dom_str)) {
                $this->error("请填写合法的下载量dom_str");
            }
            $data['download_count_dom_str'] = $download_count_dom_str;
            
            $download_count_dom_value = trim($_POST['download_count_dom_value']);
            $data['download_count_dom_value'] = $download_count_dom_value;
            
            $download_count_dom_reg = trim($_POST['download_count_dom_reg']);
            if (!$this->check_dom_reg($download_count_dom_reg)) {
                $this->error("请填写合法的下载量dom_reg");
            }
            $data['download_count_dom_reg'] = $download_count_dom_reg;
            
            $author_dom_str = trim($_POST['author_dom_str']);
            if (!$this->check_dom_str($author_dom_str)) {
                $this->error("请填写合法的开发者dom_str");
            }
            $data['author_dom_str'] = $author_dom_str;
            
            $author_dom_value = trim($_POST['author_dom_value']);
            $data['author_dom_value'] = $author_dom_value;
            
            $author_dom_reg = trim($_POST['author_dom_reg']);
            if (!$this->check_dom_reg($author_dom_reg)) {
                $this->error("请填写合法的作者dom_reg");
            }
            $data['author_dom_reg'] = $author_dom_reg;
            
            $score_dom_str = trim($_POST['score_dom_str']);
            if (!$this->check_dom_str($score_dom_str)) {
                $this->error("请填写合法的评分dom_str");
            }
            $data['score_dom_str'] = $score_dom_str;
            
            $score_dom_value = trim($_POST['score_dom_value']);
            $data['score_dom_value'] = $score_dom_value;
            
            $score_dom_reg = trim($_POST['score_dom_reg']);
            if (!$this->check_dom_reg($score_dom_reg)) {
                $this->error("请填写合法的评分dom_reg");
            }
            $data['score_dom_reg'] = $score_dom_reg;
            
            $logo_url_dom_str = trim($_POST['logo_url_dom_str']);
            if (!$this->check_dom_str($logo_url_dom_str)) {
                $this->error("请填写合法的icon的dom_str");
            }
            $data['logo_url_dom_str'] = $logo_url_dom_str;
            
            $logo_url_dom_value = trim($_POST['logo_url_dom_value']);
            $data['logo_url_dom_value'] = $logo_url_dom_value;
            
            $logo_url_dom_reg = trim($_POST['logo_url_dom_reg']);
            if (!$this->check_dom_reg($logo_url_dom_reg)) {
                $this->error("请填写合法的icon的dom_reg");
            }
            $data['logo_url_dom_reg'] = $logo_url_dom_reg;
            
            $download_url_dom_str = trim($_POST['download_url_dom_str']);
            if (!$this->check_dom_str($download_url_dom_str)) {
                $this->error("请填写合法的下载地址的dom_str");
            }
            $data['download_url_dom_str'] = $download_url_dom_str;
            
            $download_url_dom_value = trim($_POST['download_url_dom_value']);
            $data['download_url_dom_value'] = $download_url_dom_value;
            
            $download_url_dom_reg = trim($_POST['download_url_dom_reg']);
            if (!$this->check_dom_reg($download_url_dom_reg)) {
                $this->error("请填写合法的下载链接dom_reg");
            }
            $data['download_url_dom_reg'] = $download_url_dom_reg;
            
            if ($download_url_dom_str && !$download_url_dom_value) {
                $this->error("请填写下载链接值配置");
            }
            
            if (!$download_url_dom_str && !$download_url_dom_reg) {
                $this->error("请填写下载链接相关配置");
            }
            
            // 截图
            $screenshot_dom_str = trim($_POST['screenshot_dom_str']);
            if (!$this->check_dom_str($screenshot_dom_str)) {
                $this->error("请填写合法的截图dom_str");
            }
            $data['screenshot_dom_str'] = $screenshot_dom_str;
            
            $screenshot_dom_value = trim($_POST['screenshot_dom_value']);
            $data['screenshot_dom_value'] = $screenshot_dom_value;
            
            $screenshot_dom_reg = trim($_POST['screenshot_dom_reg']);
            if (!$this->check_dom_reg($screenshot_dom_reg)) {
                $this->error("请填写合法的截图dom_reg");
            }
            $data['screenshot_dom_reg'] = $screenshot_dom_reg;
            
            // 截图的替换正则
            $screenshot_dom_replacereg = trim($_POST['screenshot_dom_replacereg']);
            if (!$this->check_dom_reg($screenshot_dom_replacereg)) {
                $this->error("请填写合法的截图dom_reg");
            }
            $data['screenshot_dom_replacereg'] = $screenshot_dom_replacereg;
            
            // 截图的替换内容
            $screenshot_dom_replacement = trim($_POST['screenshot_dom_replacement']);
            $data['screenshot_dom_replacement'] = $screenshot_dom_replacement;
            
            $now = time();
            $data['create_time'] = $now;
            $data['update_time'] = $now;
            
            $model = D('Caiji.UpdatePackageWebsiteConfig');
            $ret = $model->table('cj_add_package_website_config')->add($data);
            if ($ret) {
                // 写日志
                $this->writelog("采集/质管--配置管理--新增网站配置：添加了id为{$ret}的记录",'cj_add_package_website_config',$ret,__ACTION__ ,"","add");
                $this->assign('jumpUrl', '__URL__/index');
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        } else {
            $this->display('add_website');
        }
    }
    
    public function edit_website() {
        if ($_POST) {
            $id = $_POST['id'];
            $data = array();
            
            $website_name = trim($_POST['website_name']);
            if (!$website_name) {
                $this->error("网站名称不能为空！");
            }
            $data['website_name'] = $website_name;
            
            $priority = trim($_POST['priority']);
            if (!$priority) {
                $priority = 1;
            } else {
                // 必需填数字
                if (!preg_match('/\d+/', $priority)) {
                    $this->error("优先级必须是正整数！");
                }
            }
            $data['priority'] = $priority;
            
            $remark = trim($_POST['remark']);
            $data['remark'] = $remark ? $remark : '';
            
            $entrance_type = trim($_POST['entrance_type']);
            if (!$entrance_type) {
                $this->error("入口方式不能为空！");
            }
            $data['entrance_type'] = $entrance_type;
            
            // 入口url或api
            $fake_list_url = trim($_POST['fake_list_url']);
            if (!$fake_list_url) {
                $this->error("入口方式不能为空！");
            }
            $data['fake_list_url'] = $fake_list_url;
            
            if ($entrance_type == 1) {
            
                $max_page_dom_str = trim($_POST['max_page_dom_str']);
                if (!$this->check_dom_str($max_page_dom_str)) {
                    $this->error("请填写合法的页码dom_str");
                }
                $data['max_page_dom_str'] = $max_page_dom_str;
                
                $max_page_dom_value = trim($_POST['max_page_dom_value']);
                $data['max_page_dom_value'] = $max_page_dom_value;
                
                $max_page_dom_reg = trim($_POST['max_page_dom_reg']);
                if (!$this->check_dom_reg($max_page_dom_reg)) {
                    $this->error("请填写合法的页面dom_reg");
                }
                $data['max_page_dom_reg'] = $max_page_dom_reg;
                
                if ($max_page_dom_str && !$max_page_dom_value) {
                    $this->error("请填写详情页值配置");
                }
                
                if (!$max_page_dom_str && !$max_page_dom_reg) {
                    $this->error("请填写页码相关配置");
                }
                
                // cell
                $cell_dom_str = trim($_POST['cell_dom_str']);
                if (!$this->check_dom_str($cell_dom_str)) {
                    $this->error("请填写合法的cell的dom_str");
                }
                $data['cell_dom_str'] = $cell_dom_str;
                if (!$cell_dom_str) {
                    $this->error("请填写cell配置");
                }
                
                $detail_url_dom_str = trim($_POST['detail_url_dom_str']);
                if (!$this->check_dom_str($detail_url_dom_str)) {
                    $this->error("请填写合法的详情页url的dom_str");
                }
                $data['detail_url_dom_str'] = $detail_url_dom_str;
                
                $detail_url_dom_value = trim($_POST['detail_url_dom_value']);
                $data['detail_url_dom_value'] = $detail_url_dom_value;
                
                $detail_url_dom_reg = trim($_POST['detail_url_dom_reg']);
                if (!$this->check_dom_reg($detail_url_dom_reg)) {
                    $this->error("请填写合法的详情页url的dom_reg");
                }
                $data['detail_url_dom_reg'] = $detail_url_dom_reg;
                
                if ($detail_url_dom_str && !$detail_url_dom_value) {
                    $this->error("请填写详情页值配置");
                }
                
                if (!$detail_url_dom_str && !$detail_url_dom_reg) {
                    $this->error("请填写详情页相关配置");
                }
                
                $cell_package_dom_str = trim($_POST['cell_package_dom_str']);
                if (!$this->check_dom_str($cell_package_dom_str)) {
                    $this->error("请填写合法的cell中包名的dom_str");
                }
                $data['cell_package_dom_str'] = $cell_package_dom_str;
                
                $cell_package_dom_value = trim($_POST['cell_package_dom_value']);
                $data['cell_package_dom_value'] = $cell_package_dom_value;
                
                $cell_package_dom_reg = trim($_POST['cell_package_dom_reg']);
                if (!$this->check_dom_reg($cell_package_dom_reg)) {
                    $this->error("请填写合法的cell中包名的dom_reg");
                }
                $data['cell_package_dom_reg'] = $cell_package_dom_reg;
                
                $cell_download_count_dom_str = trim($_POST['cell_download_count_dom_str']);
                if (!$this->check_dom_str($cell_download_count_dom_str)) {
                    $this->error("请填写合法的cell中下载量的dom_str");
                }
                $data['cell_download_count_dom_str'] = $cell_download_count_dom_str;
                
                $cell_download_count_dom_value = trim($_POST['cell_download_count_dom_value']);
                $data['cell_download_count_dom_value'] = $cell_download_count_dom_value;
                
                $cell_download_count_dom_reg = trim($_POST['cell_download_count_dom_reg']);
                if (!$this->check_dom_reg($cell_download_count_dom_reg)) {
                    $this->error("请填写合法的cell中下载量的dom_reg");
                }
                $data['cell_download_count_dom_reg'] = $cell_download_count_dom_reg;
                
            } else {
                $apparr_route = trim($_POST['apparr_route']);
                if (!$apparr_route) {
                    $this->error("请填写app数组路由");
                }
                $data['apparr_route'] = $apparr_route;
                $apparr_package_route = trim($_POST['apparr_package_route']);
                if (!$apparr_package_route) {
                    $this->error("app数组内包名路由");
                }
                $fake_detail_url = trim($_POST['fake_detail_url']);
                if (!$fake_detail_url) {
                    $this->error("请填写替换url");
                }
                $data['fake_detail_url'] = $fake_detail_url;
            }
            $cookie_open = trim($_POST['cookie_open']);
            $data['cookie_open'] = $cookie_open ? 1 : 0;
            
            $package_dom_str = trim($_POST['package_dom_str']);
            if (!$this->check_dom_str($package_dom_str)) {
                $this->error("请填写合法的包名dom_str");
            }
            $data['package_dom_str'] = $package_dom_str;
            
            $package_dom_value = trim($_POST['package_dom_value']);
            $data['package_dom_value'] = $package_dom_value;
            
            $package_dom_reg = trim($_POST['package_dom_reg']);
            if (!$this->check_dom_reg($package_dom_reg)) {
                $this->error("请填写合法的包名dom_reg");
            }
            $data['package_dom_reg'] = $package_dom_reg;
            
            $version_code_dom_str = trim($_POST['version_code_dom_str']);
            if (!$this->check_dom_str($version_code_dom_str)) {
                $this->error("请填写合法的版本号的dom_str");
            }
            $data['version_code_dom_str'] = $version_code_dom_str;
            
            $version_code_dom_value = trim($_POST['version_code_dom_value']);
            $data['version_code_dom_value'] = $version_code_dom_value;
            
            if ($version_code_dom_str && !$version_code_dom_value) {
                $this->error("请填写版本号值配置");
            }
            
            $version_code_dom_reg = trim($_POST['version_code_dom_reg']);
            if (!$this->check_dom_reg($version_code_dom_reg)) {
                $this->error("请填写合法的版本号的dom_reg");
            }
            $data['version_code_dom_reg'] = $version_code_dom_reg;
            
            $version_name_dom_str = trim($_POST['version_name_dom_str']);
            if (!$this->check_dom_str($version_name_dom_str)) {
                $this->error("请填写合法的版本名称的dom_str");
            }
            $data['version_name_dom_str'] = $version_name_dom_str;
            
            $version_name_dom_value = trim($_POST['version_name_dom_value']);
            $data['version_name_dom_value'] = $version_name_dom_value;
            
            if ($version_name_dom_str && !$version_name_dom_value) {
                $this->error("请填写版本名称值配置");
            }
            
            $version_name_dom_reg = trim($_POST['version_name_dom_reg']);
            if (!$this->check_dom_reg($version_name_dom_reg)) {
                $this->error("请填写合法的版本名称的dom_reg");
            }
            $data['version_name_dom_reg'] = $version_name_dom_reg;
            
            if ((!$version_code_dom_str && !$version_code_dom_reg) && (!$version_name_dom_str && !$version_name_dom_reg)) {
                $this->error("请填写版本号名版本名称相关配置");
            }
            
            $softname_dom_str = trim($_POST['softname_dom_str']);
            if (!$this->check_dom_str($softname_dom_str)) {
                $this->error("请填写合法的软件名称的dom_str");
            }
            $data['softname_dom_str'] = $softname_dom_str;
            
            $softname_dom_value = trim($_POST['softname_dom_value']);
            $data['softname_dom_value'] = $softname_dom_value;
            
            $softname_dom_reg = trim($_POST['softname_dom_reg']);
            if (!$this->check_dom_reg($softname_dom_reg)) {
                $this->error("请填写合法的软件名称的dom_reg");
            }
            $data['softname_dom_reg'] = $softname_dom_reg;
            
            // 广告
            $is_ad_dom_str = trim($_POST['is_ad_dom_str']);
            if (!$this->check_dom_str($is_ad_dom_str)) {
                $this->error("请填写合法的有广告的dom_str");
            }
            $data['is_ad_dom_str'] = $is_ad_dom_str;
            
            $is_ad_dom_value = trim($_POST['is_ad_dom_value']);
            $data['is_ad_dom_value'] = $is_ad_dom_value;
            
            $is_ad_dom_reg = trim($_POST['is_ad_dom_reg']);
            if (!$this->check_dom_reg($is_ad_dom_reg)) {
                $this->error("请填写合法的有广告dom_reg");
            }
            $data['is_ad_dom_reg'] = $is_ad_dom_reg;
            
            // 安全
            $is_safe_dom_str = trim($_POST['is_safe_dom_str']);
            if (!$this->check_dom_str($is_safe_dom_str)) {
                $this->error("请填写合法的安全dom_str");
            }
            $data['is_safe_dom_str'] = $is_safe_dom_str;
            
            $is_safe_dom_value = trim($_POST['is_safe_dom_value']);
            $data['is_safe_dom_value'] = $is_safe_dom_value;
            
            $is_safe_dom_reg = trim($_POST['is_safe_dom_reg']);
            if (!$this->check_dom_reg($is_safe_dom_reg)) {
                $this->error("请填写合法的安全dom_reg");
            }
            $data['is_safe_dom_reg'] = $is_safe_dom_reg;
            
            
            // 官方
            $is_office_dom_str = trim($_POST['is_office_dom_str']);
            if (!$this->check_dom_str($is_office_dom_str)) {
                $this->error("请填写合法的官方dom_str");
            }
            $data['is_office_dom_str'] = $is_office_dom_str;
            
            $is_office_dom_value = trim($_POST['is_office_dom_value']);
            $data['is_office_dom_value'] = $is_office_dom_value;
            
            $is_office_dom_reg = trim($_POST['is_office_dom_reg']);
            if (!$this->check_dom_reg($is_office_dom_reg)) {
                $this->error("请填写合法的官方dom_reg");
            }
            $data['is_office_dom_reg'] = $is_office_dom_reg;
            
            
            $category_name_dom_str = trim($_POST['category_name_dom_str']);
            if (!$this->check_dom_str($category_name_dom_str)) {
                $this->error("请填写合法的分类dom_str");
            }
            $data['category_name_dom_str'] = $category_name_dom_str;
            
            $category_name_dom_value = trim($_POST['category_name_dom_value']);
            $data['category_name_dom_value'] = $category_name_dom_value;
            
            $category_name_dom_reg = trim($_POST['category_name_dom_reg']);
            if (!$this->check_dom_reg($category_name_dom_reg)) {
                $this->error("请填写合法的分类dom_reg");
            }
            $data['category_name_dom_reg'] = $category_name_dom_reg;
            
            $keyword_dom_str = trim($_POST['keyword_dom_str']);
            if (!$this->check_dom_str($keyword_dom_str)) {
                $this->error("请填写合法的关键字dom_str");
            }
            $data['keyword_dom_str'] = $keyword_dom_str;
            
            $keyword_dom_value = trim($_POST['keyword_dom_value']);
            $data['keyword_dom_value'] = $keyword_dom_value;
            
            $keyword_dom_reg = trim($_POST['keyword_dom_reg']);
            if (!$this->check_dom_reg($keyword_dom_reg)) {
                $this->error("请填写合法的关键字dom_reg");
            }
            $data['keyword_dom_reg'] = $keyword_dom_reg;
            
            $update_log_dom_str = trim($_POST['update_log_dom_str']);
            if (!$this->check_dom_str($update_log_dom_str)) {
                $this->error("请填写合法的更新日志dom_str");
            }
            $data['update_log_dom_str'] = $update_log_dom_str;
            
            $update_log_dom_value = trim($_POST['update_log_dom_value']);
            $data['update_log_dom_value'] = $update_log_dom_value;
            
            $update_log_dom_reg = trim($_POST['update_log_dom_reg']);
            if (!$this->check_dom_reg($update_log_dom_reg)) {
                $this->error("请填写合法的更新日志dom_reg");
            }
            $data['update_log_dom_reg'] = $update_log_dom_reg;
            
            $description_dom_str = trim($_POST['description_dom_str']);
            if (!$this->check_dom_str($description_dom_str)) {
                $this->error("请填写合法的描述dom_str");
            }
            $data['description_dom_str'] = $description_dom_str;
            
            $description_dom_value = trim($_POST['description_dom_value']);
            $data['description_dom_value'] = $description_dom_value;
            
            $description_dom_reg = trim($_POST['description_dom_reg']);
            if (!$this->check_dom_reg($description_dom_reg)) {
                $this->error("请填写合法的描述dom_reg");
            }
            $data['description_dom_reg'] = $description_dom_reg;
            
            $download_count_dom_str = trim($_POST['download_count_dom_str']);
            if (!$this->check_dom_str($download_count_dom_str)) {
                $this->error("请填写合法的下载量dom_str");
            }
            $data['download_count_dom_str'] = $download_count_dom_str;
            
            $download_count_dom_value = trim($_POST['download_count_dom_value']);
            $data['download_count_dom_value'] = $download_count_dom_value;
            
            $download_count_dom_reg = trim($_POST['download_count_dom_reg']);
            if (!$this->check_dom_reg($download_count_dom_reg)) {
                $this->error("请填写合法的下载量dom_reg");
            }
            $data['download_count_dom_reg'] = $download_count_dom_reg;
            
            $author_dom_str = trim($_POST['author_dom_str']);
            if (!$this->check_dom_str($author_dom_str)) {
                $this->error("请填写合法的开发者dom_str");
            }
            $data['author_dom_str'] = $author_dom_str;
            
            $author_dom_value = trim($_POST['author_dom_value']);
            $data['author_dom_value'] = $author_dom_value;
            
            $author_dom_reg = trim($_POST['author_dom_reg']);
            if (!$this->check_dom_reg($author_dom_reg)) {
                $this->error("请填写合法的作者dom_reg");
            }
            $data['author_dom_reg'] = $author_dom_reg;
            
            $score_dom_str = trim($_POST['score_dom_str']);
            if (!$this->check_dom_str($score_dom_str)) {
                $this->error("请填写合法的评分dom_str");
            }
            $data['score_dom_str'] = $score_dom_str;
            
            $score_dom_value = trim($_POST['score_dom_value']);
            $data['score_dom_value'] = $score_dom_value;
            
            $score_dom_reg = trim($_POST['score_dom_reg']);
            if (!$this->check_dom_reg($score_dom_reg)) {
                $this->error("请填写合法的评分dom_reg");
            }
            $data['score_dom_reg'] = $score_dom_reg;
            
            $logo_url_dom_str = trim($_POST['logo_url_dom_str']);
            if (!$this->check_dom_str($logo_url_dom_str)) {
                $this->error("请填写合法的icon的dom_str");
            }
            $data['logo_url_dom_str'] = $logo_url_dom_str;
            
            $logo_url_dom_value = trim($_POST['logo_url_dom_value']);
            $data['logo_url_dom_value'] = $logo_url_dom_value;
            
            $logo_url_dom_reg = trim($_POST['logo_url_dom_reg']);
            if (!$this->check_dom_reg($logo_url_dom_reg)) {
                $this->error("请填写合法的icon的dom_reg");
            }
            $data['logo_url_dom_reg'] = $logo_url_dom_reg;
            
            $download_url_dom_str = trim($_POST['download_url_dom_str']);
            if (!$this->check_dom_str($download_url_dom_str)) {
                $this->error("请填写合法的下载地址的dom_str");
            }
            $data['download_url_dom_str'] = $download_url_dom_str;
            
            $download_url_dom_value = trim($_POST['download_url_dom_value']);
            $data['download_url_dom_value'] = $download_url_dom_value;
            
            $download_url_dom_reg = trim($_POST['download_url_dom_reg']);
            if (!$this->check_dom_reg($download_url_dom_reg)) {
                $this->error("请填写合法的下载链接dom_reg");
            }
            $data['download_url_dom_reg'] = $download_url_dom_reg;
            
            if ($download_url_dom_str && !$download_url_dom_value) {
                $this->error("请填写下载链接值配置");
            }
            
            if (!$download_url_dom_str && !$download_url_dom_reg) {
                $this->error("请填写下载链接相关配置");
            }
            
            // 截图
            $screenshot_dom_str = trim($_POST['screenshot_dom_str']);
            if (!$this->check_dom_str($screenshot_dom_str)) {
                $this->error("请填写合法的截图dom_str");
            }
            $data['screenshot_dom_str'] = $screenshot_dom_str;
            
            $screenshot_dom_value = trim($_POST['screenshot_dom_value']);
            $data['screenshot_dom_value'] = $screenshot_dom_value;
            
            $screenshot_dom_reg = trim($_POST['screenshot_dom_reg']);
            if (!$this->check_dom_reg($screenshot_dom_reg)) {
                $this->error("请填写合法的截图dom_reg");
            }
            $data['screenshot_dom_reg'] = $screenshot_dom_reg;
            
            // 截图的替换正则
            $screenshot_dom_replacereg = trim($_POST['screenshot_dom_replacereg']);
            if (!$this->check_dom_reg($screenshot_dom_replacereg)) {
                $this->error("请填写合法的截图dom_reg");
            }
            $data['screenshot_dom_replacereg'] = $screenshot_dom_replacereg;
            
            // 截图的替换内容
            $screenshot_dom_replacement = trim($_POST['screenshot_dom_replacement']);
            $data['screenshot_dom_replacement'] = $screenshot_dom_replacement;
            
            $now = time();
            $data['update_time'] = $now;
            
            $model = D('Caiji.UpdatePackageWebsiteConfig');
            $where = array('id'=>$id);
            $log = $this->logcheck($where, 'cj_add_package_website_config', $data, $model);
            $ret = $model->table('cj_add_package_website_config')->where($where)->save($data);
            if ($ret) {
                $this->writelog("采集/质管--配置管理--新增网站配置，编辑了id为{$id}的记录：{$log}",'cj_add_package_website_config',$id,__ACTION__ ,"","edit");
                $this->assign('jumpUrl', '__URL__/index');
                $this->success("编辑成功！");
            } else {
                var_dump($model->getlastsql());
                $this->error("编辑失败！");
            }
        } else if ($_GET) {
            $id = $_GET['id'];
            $model = D('Caiji.UpdatePackageWebsiteConfig');
            
            $where = array(
                'id' => array('eq', $id)
            );
            
            $find = $model->table('cj_add_package_website_config')->where($where)->find();
            
            $this->assign('website', $find);
            $this->display('edit_website');
        }
    }
    
    // 检查dom_str是否合法
    function check_dom_str($dom_str) {
        if (!$dom_str) return true;
        $ret = json_decode($dom_str);
        if (!$ret) {
            return false;
        }
        return true;
    }
    
    // 检查dom_reg是否合法
    function check_dom_reg($dom_reg) {
        if (!$dom_reg) return true;
        // 前后需有相同的字符作为定界符号
        $reg = '/^(.).+?\1$/';
        $ret = preg_match($reg, $dom_reg, $matches);
        if (!$ret) {
            return false;
        }
        /*
        $delimitar = $matches[1];
        // 除了字母、数字和反斜线\以外的任何字符都可以为定界符号
        $illegal_delimitar_reg = '/[\w\d\\\\]/';
        if (preg_match($illegal_delimitar_reg, $delimitar)) {
            return false;
        }
        */
        return true;
    }
}

?>