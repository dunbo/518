<?php

class CollectWebsiteConfigAction extends CommonAction {

    private $model;
	private $table = 'cj_zy_collect_website_config';

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Caiji.UpdatePackageWebsiteConfig');
    }

	public function index() {
		$where = array(
			'status' => array('neq', 0)
		);
        $list = $this->model->table($this->table)->where($where)->order('status, priority')->select();

        $this->assign('list', $list);
		$this->display();
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
        
        $ret = $this->model->table($this->table)->where($where)->save($data);
        if ($ret) {
            $this->writelog("智友内容管理-内容采集规则管理-外部采集规则管理：{$hint}了id为{$id}的记录",$this->table,$id,__ACTION__ ,"","edit");
            $this->success("{$hint}成功！");
        } else {
            $this->error("{$hint}失败！");
        }
    }

    public function batch_priority() {
        $arr = array();
        foreach ($_POST as $name => $priority) {
            $ret = preg_match("/^priority_(\d+)$/", $name, $matches);
            if (!$ret)
                continue;
            $id = $matches[1];
            // 判断输入的priority是不是数字
            $priority = trim($priority);
            if (!preg_match('/^\d+$/', $priority) || $priority < 1) {
                $this->ajaxReturn('', "排序调整无效，排序值需为正整数", -1);
            }
            
            $arr[$id] = $priority;
        }

        // 更新表
        $each_log = array();
        $ids = '';
        foreach ($arr as $id => $priority) {
            $ids .= $id.',';
            $where = array(
                'id' => $id
            );
            $data = array(
                'priority' => $priority,
                'update_time' => time()
            );
            $log_result = $this -> logcheck($where, $this->table, $data, $this->model);
            $each_log[] = "编辑了id为{$id}的记录，{$log_result}";
            $ret = $this->model->table($this->table)->where($where)->save($data);
        }
        $log = implode(';', $each_log);
        $this->writelog("智友内容管理-内容采集规则管理-外部采集规则管理：批量编辑排序，{$log}",$this->table,$ids,__ACTION__ ,"","edit");
        $this->ajaxReturn('', "更新排序成功！", 0);
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
        $ret = $this->model->table($this->table)->where($where)->save($data);
        if ($ret) {
            $this->writelog("智友内容管理-内容采集规则管理-外部采集规则管理：删除了id为{$id}的记录",$this->table,$id,__ACTION__ ,"","del");
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
            
            $website_page_url = trim($_POST['website_page_url']);
            if (!$website_page_url) {
                $this->error("页面地址不能为空！");
            }
            $data['website_page_url'] = $website_page_url;

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
                $this->error("入口url或api不能为空！");
            }
            $data['fake_list_url'] = $fake_list_url;
            
            if ($entrance_type == 1) {
                // cell
                $cell_dom_str = trim($_POST['cell_dom_str']);
                if (!$this->check_dom_str($cell_dom_str)) {
                    $this->error("请填写合法的cell的dom_str");
                }
                $data['cell_dom_str'] = $cell_dom_str;
                if (!$cell_dom_str) {
                    $this->error("请填写cell配置");
                }
                
                // 详情页
                $cell_detail_url_dom_str = trim($_POST['cell_detail_url_dom_str']);
                if (!$this->check_dom_str($cell_detail_url_dom_str)) {
                    $this->error("请填写合法的详情页url的dom_str");
                }
                $data['cell_detail_url_dom_str'] = $cell_detail_url_dom_str;
                
                $cell_detail_url_dom_value = trim($_POST['cell_detail_url_dom_value']);
                $data['cell_detail_url_dom_value'] = $cell_detail_url_dom_value;
                
                $cell_detail_url_dom_reg = trim($_POST['cell_detail_url_dom_reg']);
                if (!$this->check_dom_reg($cell_detail_url_dom_reg)) {
                    $this->error("请填写合法的详情页url的dom_reg");
                }
                $data['cell_detail_url_dom_reg'] = $cell_detail_url_dom_reg;
                
                if ($cell_detail_url_dom_str && !$cell_detail_url_dom_value) {
                    $this->error("请填写详情页值配置");
                }
                
                if (!$cell_detail_url_dom_str && !$cell_detail_url_dom_reg) {
                    $this->error("请填写详情页相关配置");
                }

                // 标题
                $cell_title_dom_str = trim($_POST['cell_title_dom_str']);
                if (!$this->check_dom_str($cell_title_dom_str)) {
                    $this->error("请填写合法的标题的dom_str");
                }
                $data['cell_title_dom_str'] = $cell_title_dom_str;
                
                $cell_title_dom_value = trim($_POST['cell_title_dom_value']);
                $data['cell_title_dom_value'] = $cell_title_dom_value;
                
                $cell_title_dom_reg = trim($_POST['cell_title_dom_reg']);
                if (!$this->check_dom_reg($cell_title_dom_reg)) {
                    $this->error("请填写合法的标题的dom_reg");
                }
                $data['cell_title_dom_reg'] = $cell_title_dom_reg;
                
                if ($cell_title_dom_str && !$cell_title_dom_value) {
                    $this->error("请填写标题值配置");
                }

                // 简介
                $cell_synopsis_dom_str = trim($_POST['cell_synopsis_dom_str']);
                if (!$this->check_dom_str($cell_synopsis_dom_str)) {
                    $this->error("请填写合法的简介的dom_str");
                }
                $data['cell_synopsis_dom_str'] = $cell_synopsis_dom_str;
                
                $cell_synopsis_dom_value = trim($_POST['cell_synopsis_dom_value']);
                $data['cell_synopsis_dom_value'] = $cell_synopsis_dom_value;
                
                $cell_synopsis_dom_reg = trim($_POST['cell_synopsis_dom_reg']);
                if (!$this->check_dom_reg($cell_synopsis_dom_reg)) {
                    $this->error("请填写合法的简介的dom_reg");
                }
                $data['cell_synopsis_dom_reg'] = $cell_synopsis_dom_reg;
                
                if ($cell_synopsis_dom_str && !$cell_synopsis_dom_value) {
                    $this->error("请填写简介值配置");
                }
                
                // 图片
                $cell_img_url_dom_str = trim($_POST['cell_img_url_dom_str']);
                if (!$this->check_dom_str($cell_img_url_dom_str)) {
                    $this->error("请填写合法的图片的dom_str");
                }
                $data['cell_img_url_dom_str'] = $cell_img_url_dom_str;
                
                $cell_img_url_dom_value = trim($_POST['cell_img_url_dom_value']);
                $data['cell_img_url_dom_value'] = $cell_img_url_dom_value;
                
                $cell_img_url_dom_reg = trim($_POST['cell_img_url_dom_reg']);
                if (!$this->check_dom_reg($cell_img_url_dom_reg)) {
                    $this->error("请填写合法的图片的dom_reg");
                }
                $data['cell_img_url_dom_reg'] = $cell_img_url_dom_reg;
                
                if ($cell_img_url_dom_str && !$cell_img_url_dom_value) {
                    $this->error("请填写图片配置");
                }
               
            } else {
                $offset_type = trim($_POST['offset_type']); 
                if (empty($offset_type)) {
                    $this->error("请填写偏移方式");
                }
                $data['offset_type'] = $offset_type;


                $dynamic_type = trim($_POST['dynamic_type']) ? 1 : 0;
                $data['dynamic_type'] = $dynamic_type;

                if ($dynamic_type) {
                    $dynamic_arg_url = trim($_POST['dynamic_arg_url']);
                    if (empty($dynamic_arg_url)) {
                        $this->error("请填写动态参数获取地址");
                    }
                    $data['dynamic_arg_url'] = $dynamic_arg_url;
                    
                    $dynamic_arg_dom_arr_reg = trim($_POST['dynamic_arg_dom_arr_reg']);
                    if (empty($dynamic_arg_dom_arr_reg)) {
                        $this->error("请填写动态参数获取正则");
                    }
                    $data['dynamic_arg_dom_arr_reg'] = $dynamic_arg_dom_arr_reg;

                }

                $cellarr_route = trim($_POST['cellarr_route']);
                if (!$cellarr_route) {
                    $this->error("请填写cell数组路由");
                }
                $data['cellarr_route'] = $cellarr_route;

                $cell_title_route = trim($_POST['cell_title_route']);
                $data['cell_title_route'] = $cell_title_route;

                $cell_synopsis_route = trim($_POST['cell_synopsis_route']);
                $data['cell_synopsis_route'] = $cell_synopsis_route;

                $cell_img_url_route = trim($_POST['cell_img_url_route']);
                $data['cell_img_url_route'] = $cell_img_url_route;
                
                $cell_author_route = trim($_POST['cell_author_route']);
                $data['cell_author_route'] = $cell_author_route;

                $cell_detail_url_route = trim($_POST['cell_detail_url_route']);
                $data['cell_detail_url_route'] = $cell_detail_url_route;

                $cell_dateline_route = trim($_POST['cell_dateline_route']);
                $data['cell_dateline_route'] = $cell_dateline_route;
            }
            // 作者
            $author_dom_str = trim($_POST['author_dom_str']);
            if (!$this->check_dom_str($author_dom_str)) {
                $this->error("请填写合法的作者dom_str");
            }
            $data['author_dom_str'] = $author_dom_str;
            
            $author_dom_value = trim($_POST['author_dom_value']);
            $data['author_dom_value'] = $author_dom_value;
            
            $author_dom_reg = trim($_POST['author_dom_reg']);
            if (!$this->check_dom_reg($author_dom_reg)) {
                $this->error("请填写合法的作者dom_reg");
            }
            $data['author_dom_reg'] = $author_dom_reg;
            
            // 时间
            $dateline_dom_str = trim($_POST['dateline_dom_str']);
            if (!$this->check_dom_str($dateline_dom_str)) {
                $this->error("请填写合法的时间的dom_str");
            }
            $data['dateline_dom_str'] = $dateline_dom_str;
            
            $dateline_dom_value = trim($_POST['dateline_dom_value']);
            $data['dateline_dom_value'] = $dateline_dom_value;
            
            $dateline_dom_reg = trim($_POST['dateline_dom_reg']);
            if (!$this->check_dom_reg($dateline_dom_reg)) {
                $this->error("请填写合法的时间的dom_reg");
            }
            $data['dateline_dom_reg'] = $dateline_dom_reg;
            
            // 正文翻页url
            $page_url_dom_str = trim($_POST['page_url_dom_str']);
            if (!$this->check_dom_str($page_url_dom_str)) {
                $this->error("请填写合法的版本名称的dom_str");
            }
            $data['page_url_dom_str'] = $page_url_dom_str;
            
            $page_url_dom_value = trim($_POST['page_url_dom_value']);
            $data['page_url_dom_value'] = $page_url_dom_value;
            
            if ($page_url_dom_str && !$page_url_dom_value) {
                $this->error("请填写正文翻页url值配置");
            }
            
            $page_url_dom_reg = trim($_POST['page_url_dom_reg']);
            if (!$this->check_dom_reg($page_url_dom_reg)) {
                $this->error("请填写合法的正文翻页url的dom_reg");
            }
            $data['page_url_dom_reg'] = $page_url_dom_reg;
            
            // 正文翻页页码
            $page_dom_str = trim($_POST['page_dom_str']);
            if (!$this->check_dom_str($page_dom_str)) {
                $this->error("请填写合法的正文翻页页码的dom_str");
            }
            $data['page_dom_str'] = $page_dom_str;
            
            $page_dom_value = trim($_POST['page_dom_value']);
            $data['page_dom_value'] = $page_dom_value;
            
            $page_dom_reg = trim($_POST['page_dom_reg']);
            if (!$this->check_dom_reg($page_dom_str)) {
                $this->error("请填写合法的正文翻页页码的dom_reg");
            }
            $data['page_dom_reg'] = $page_dom_reg;
            
            // 正文
            $content_dom_str = trim($_POST['content_dom_str']);
            if (!$this->check_dom_str($content_dom_str)) {
                $this->error("请填写合法的正文的dom_str");
            }
            $data['content_dom_str'] = $content_dom_str;
            
            $content_dom_value = trim($_POST['content_dom_value']);
            $data['content_dom_value'] = $content_dom_value;
            
            $content_dom_reg = trim($_POST['content_dom_reg']);
            if (!$this->check_dom_reg($content_dom_reg)) {
                $this->error("请填写合法的正文dom_reg");
            }
            $data['content_dom_reg'] = $content_dom_reg;
            
            // 正文去除正则
            $content_remove_reg = trim($_POST['content_remove_reg']);
			$content_remove_reg_arr = explode("\r\n", $content_remove_reg);
			foreach ($content_remove_reg_arr as $reg) {
				if (!$this->check_dom_reg($reg)) {
					$this->error("请填写合法的正文去除正则");
				}
			}
            $data['content_remove_reg'] = $content_remove_reg;
                       
            $now = time();
            $data['create_time'] = $now;
            $data['update_time'] = $now;
            
            $ret = $this->model->table($this->table)->add($data);
            if ($ret) {
                // 写日志
                $this->writelog("智友内容管理-内容采集规则管理-外部采集规则管理：添加了id为{$ret}的记录",$this->table,$ret,__ACTION__ ,"","add");
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
            $data = array();
            $website_name = trim($_POST['website_name']);
            if (!$website_name) {
                $this->error("网站名称不能为空！");
            }
            $data['website_name'] = $website_name;
            
            $website_page_url = trim($_POST['website_page_url']);
            if (!$website_page_url) {
                $this->error("页面地址不能为空！");
            }
            $data['website_page_url'] = $website_page_url;

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
                $this->error("入口url或api不能为空！");
            }
            $data['fake_list_url'] = $fake_list_url;
            
            if ($entrance_type == 1) {
                // cell
                $cell_dom_str = trim($_POST['cell_dom_str']);
                if (!$this->check_dom_str($cell_dom_str)) {
                    $this->error("请填写合法的cell的dom_str");
                }
                $data['cell_dom_str'] = $cell_dom_str;
                if (!$cell_dom_str) {
                    $this->error("请填写cell配置");
                }
                
                // 详情页
                $cell_detail_url_dom_str = trim($_POST['cell_detail_url_dom_str']);
                if (!$this->check_dom_str($cell_detail_url_dom_str)) {
                    $this->error("请填写合法的详情页url的dom_str");
                }
                $data['cell_detail_url_dom_str'] = $cell_detail_url_dom_str;
                
                $cell_detail_url_dom_value = trim($_POST['cell_detail_url_dom_value']);
                $data['cell_detail_url_dom_value'] = $cell_detail_url_dom_value;
                
                $cell_detail_url_dom_reg = trim($_POST['cell_detail_url_dom_reg']);
                if (!$this->check_dom_reg($cell_detail_url_dom_reg)) {
                    $this->error("请填写合法的详情页url的dom_reg");
                }
                $data['cell_detail_url_dom_reg'] = $cell_detail_url_dom_reg;
                
                if ($cell_detail_url_dom_str && !$cell_detail_url_dom_value) {
                    $this->error("请填写详情页值配置");
                }
                
                if (!$cell_detail_url_dom_str && !$cell_detail_url_dom_reg) {
                    $this->error("请填写详情页相关配置");
                }

                // 标题
                $cell_title_dom_str = trim($_POST['cell_title_dom_str']);
                if (!$this->check_dom_str($cell_title_dom_str)) {
                    $this->error("请填写合法的标题的dom_str");
                }
                $data['cell_title_dom_str'] = $cell_title_dom_str;
                
                $cell_title_dom_value = trim($_POST['cell_title_dom_value']);
                $data['cell_title_dom_value'] = $cell_title_dom_value;
                
                $cell_title_dom_reg = trim($_POST['cell_title_dom_reg']);
                if (!$this->check_dom_reg($cell_title_dom_reg)) {
                    $this->error("请填写合法的标题的dom_reg");
                }
                $data['cell_title_dom_reg'] = $cell_title_dom_reg;
                
                if ($cell_title_dom_str && !$cell_title_dom_value) {
                    $this->error("请填写标题值配置");
                }

                // 简介
                $cell_synopsis_dom_str = trim($_POST['cell_synopsis_dom_str']);
                if (!$this->check_dom_str($cell_synopsis_dom_str)) {
                    $this->error("请填写合法的简介的dom_str");
                }
                $data['cell_synopsis_dom_str'] = $cell_synopsis_dom_str;
                
                $cell_synopsis_dom_value = trim($_POST['cell_synopsis_dom_value']);
                $data['cell_synopsis_dom_value'] = $cell_synopsis_dom_value;
                
                $cell_synopsis_dom_reg = trim($_POST['cell_synopsis_dom_reg']);
                if (!$this->check_dom_reg($cell_synopsis_dom_reg)) {
                    $this->error("请填写合法的简介的dom_reg");
                }
                $data['cell_synopsis_dom_reg'] = $cell_synopsis_dom_reg;
                
                if ($cell_synopsis_dom_str && !$cell_synopsis_dom_value) {
                    $this->error("请填写简介值配置");
                }
                
                // 图片
                $cell_img_url_dom_str = trim($_POST['cell_img_url_dom_str']);
                if (!$this->check_dom_str($cell_img_url_dom_str)) {
                    $this->error("请填写合法的图片的dom_str");
                }
                $data['cell_img_url_dom_str'] = $cell_img_url_dom_str;
                
                $cell_img_url_dom_value = trim($_POST['cell_img_url_dom_value']);
                $data['cell_img_url_dom_value'] = $cell_img_url_dom_value;
                
                $cell_img_url_dom_reg = trim($_POST['cell_img_url_dom_reg']);
                if (!$this->check_dom_reg($cell_img_url_dom_reg)) {
                    $this->error("请填写合法的图片的dom_reg");
                }
                $data['cell_img_url_dom_reg'] = $cell_img_url_dom_reg;
                
                if ($cell_img_url_dom_str && !$cell_img_url_dom_value) {
                    $this->error("请填写图片配置");
                }
            } else {
                $offset_type = trim($_POST['offset_type']); 
                if (empty($offset_type)) {
                    $this->error("请填写偏移方式");
                }
                $data['offset_type'] = $offset_type;


                $dynamic_type = trim($_POST['dynamic_type']) ? 1 : 0;
                $data['dynamic_type'] = $dynamic_type;

                if ($dynamic_type) {
                    $dynamic_arg_url = trim($_POST['dynamic_arg_url']);
                    if (empty($dynamic_arg_url)) {
                        $this->error("请填写动态参数获取地址");
                    }
                    $data['dynamic_arg_url'] = $dynamic_arg_url;
                    
                    $dynamic_arg_dom_arr_reg = trim($_POST['dynamic_arg_dom_arr_reg']);
                    if (empty($dynamic_arg_dom_arr_reg)) {
                        $this->error("请填写动态参数获取正则");
                    }
                    $data['dynamic_arg_dom_arr_reg'] = $dynamic_arg_dom_arr_reg;

                } else {
					// 清空
					$data['dynamic_arg_url'] = '';
					$data['dynamic_arg_dom_arr_reg'] = '';
				}

                $cellarr_route = trim($_POST['cellarr_route']);
                if (!$cellarr_route) {
                    $this->error("请填写cell数组路由");
                }
                $data['cellarr_route'] = $cellarr_route;

                $cell_title_route = trim($_POST['cell_title_route']);
                $data['cell_title_route'] = $cell_title_route;

                $cell_synopsis_route = trim($_POST['cell_synopsis_route']);
                $data['cell_synopsis_route'] = $cell_synopsis_route;

                $cell_img_url_route = trim($_POST['cell_img_url_route']);
                $data['cell_img_url_route'] = $cell_img_url_route;
                
                $cell_author_route = trim($_POST['cell_author_route']);
                $data['cell_author_route'] = $cell_author_route;

                $cell_detail_url_route = trim($_POST['cell_detail_url_route']);
                $data['cell_detail_url_route'] = $cell_detail_url_route;

                $cell_dateline_route = trim($_POST['cell_dateline_route']);
                $data['cell_dateline_route'] = $cell_dateline_route;
            }
			// 清空部分字段的数据
			if ($entrance_type == 1) {
				$data['dynamic_type'] = 0;
				$data['dynamic_arg_dom_arr_reg'] = '';
				$data['offset_type'] = 0;
				$data['cellarr_route'] = '';
				$data['cell_title_route'] = '';
				$data['cell_synopsis_route'] = '';
				$data['cell_img_url_route'] = '';
				$data['cell_author_route'] = '';
				$data['cell_detail_url_route'] = '';
				$data['cell_dateline_route'] = '';
			} else {
				$data['cell_dom_str'] = '';
				$data['cell_detail_url_dom_str'] = '';
				$data['cell_detail_url_dom_value'] = '';
				$data['cell_detail_url_dom_reg'] = '';
				$data['cell_title_dom_str'] = '';
				$data['cell_title_dom_value'] = '';
				$data['cell_title_dom_reg'] = '';
				$data['cell_synopsis_dom_str'] = '';
				$data['cell_synopsis_dom_value'] = '';
				$data['cell_synopsis_dom_reg'] = '';
				$data['cell_img_url_dom_str'] = '';
				$data['cell_img_url_dom_value'] = '';
				$data['cell_img_url_dom_reg'] = '';
			}
            // 作者
            $author_dom_str = trim($_POST['author_dom_str']);
            if (!$this->check_dom_str($author_dom_str)) {
                $this->error("请填写合法的作者dom_str");
            }
            $data['author_dom_str'] = $author_dom_str;
            
            $author_dom_value = trim($_POST['author_dom_value']);
            $data['author_dom_value'] = $author_dom_value;
            
            $author_dom_reg = trim($_POST['author_dom_reg']);
            if (!$this->check_dom_reg($author_dom_reg)) {
                $this->error("请填写合法的作者dom_reg");
            }
            $data['author_dom_reg'] = $author_dom_reg;
            
            // 时间
            $dateline_dom_str = trim($_POST['dateline_dom_str']);
            if (!$this->check_dom_str($dateline_dom_str)) {
                $this->error("请填写合法的时间的dom_str");
            }
            $data['dateline_dom_str'] = $dateline_dom_str;
            
            $dateline_dom_value = trim($_POST['dateline_dom_value']);
            $data['dateline_dom_value'] = $dateline_dom_value;
            
            $dateline_dom_reg = trim($_POST['dateline_dom_reg']);
            if (!$this->check_dom_reg($dateline_dom_reg)) {
                $this->error("请填写合法的时间的dom_reg");
            }
            $data['dateline_dom_reg'] = $dateline_dom_reg;
            
            // 正文翻页url
            $page_url_dom_str = trim($_POST['page_url_dom_str']);
            if (!$this->check_dom_str($page_url_dom_str)) {
                $this->error("请填写合法的版本名称的dom_str");
            }
            $data['page_url_dom_str'] = $page_url_dom_str;
            
            $page_url_dom_value = trim($_POST['page_url_dom_value']);
            $data['page_url_dom_value'] = $page_url_dom_value;
            
            if ($page_url_dom_str && !$page_url_dom_value) {
                $this->error("请填写正文翻页url值配置");
            }
            
            $page_url_dom_reg = trim($_POST['page_url_dom_reg']);
            if (!$this->check_dom_reg($page_url_dom_reg)) {
                $this->error("请填写合法的正文翻页url的dom_reg");
            }
            $data['page_url_dom_reg'] = $page_url_dom_reg;
            
            // 正文翻页页码
            $page_dom_str = trim($_POST['page_dom_str']);
            if (!$this->check_dom_str($page_dom_str)) {
                $this->error("请填写合法的正文翻页页码的dom_str");
            }
            $data['page_dom_str'] = $page_dom_str;
            
            $page_dom_value = trim($_POST['page_dom_value']);
            $data['page_dom_value'] = $page_dom_value;
            
            $page_dom_reg = trim($_POST['page_dom_reg']);
            if (!$this->check_dom_reg($page_dom_str)) {
                $this->error("请填写合法的正文翻页页码的dom_reg");
            }
            $data['page_dom_reg'] = $page_dom_reg;
            
            // 正文
            $content_dom_str = trim($_POST['content_dom_str']);
            if (!$this->check_dom_str($content_dom_str)) {
                $this->error("请填写合法的正文的dom_str");
            }
            $data['content_dom_str'] = $content_dom_str;
            
            $content_dom_value = trim($_POST['content_dom_value']);
            $data['content_dom_value'] = $content_dom_value;
            
            $content_dom_reg = trim($_POST['content_dom_reg']);
            if (!$this->check_dom_reg($content_dom_reg)) {
                $this->error("请填写合法的正文dom_reg");
            }
            $data['content_dom_reg'] = $content_dom_reg;
            
            // 正文去除正则
            $content_remove_reg = trim($_POST['content_remove_reg']);
			$content_remove_reg_arr = explode("\r\n", $content_remove_reg);
			foreach ($content_remove_reg_arr as $reg) {
				if (!$this->check_dom_reg($reg)) {
					$this->error("请填写合法的正文去除正则");
				}
			}
            $data['content_remove_reg'] = $content_remove_reg;
                       
            $now = time();
            $data['update_time'] = $now;
            
			$where = array('id' => $_POST['id']);
			// 日志
			$log = $this -> logcheck($where, $this->table, $data, $this->model);
            $ret = $this->model->table($this->table)->where($where)->save($data);
            if ($ret) {
                // 写日志
                $this->writelog("智友内容管理-内容采集规则管理-外部采集规则管理：编辑了id为{$_POST['id']}的记录，{$log}",$this->table,$_POST['id'],__ACTION__ ,"","edit");
                $this->assign('jumpUrl', '__URL__/index');
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else {
			$id = $_GET['id'];
			$where = array(
				'id' => $id
			);
			$find = $this->model->table($this->table)->where($where)->find();
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
