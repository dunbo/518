<?php
class CommonDataAction extends CommonAction {
    public $pagesize = 100;
    public $key = '';
    public $field_map = array();
    public $display_field = array();
    public $insert_field = array();
    public $edit_field = array();
    public $order_field = array();
    /*
    1, 普通文本
        equal完全匹配，like模糊搜索
    2, 单个时间字段可以选择时间区间
    3, 单个时间字段，开始时间
    4, 单个时间字段，结束时间
     */
    public $search_field = array();
    public $table = '';
    public $config = '';
    public $develop = true;
    //@TODO ，自定义排序、添加功能、编辑功能、删除功能、自定义数据连接的功能

    public function index() 
    {
        $this->checkConfig();
        $model = M();
        foreach ($_GET as $key => $value) {
            $_GET[$key] = trim($value);
        }
        $where = array();
        $search_data = array();
        foreach ($this->search_field as $k => $v) {
            //普通文本字段
            if(($v[0] == 1)&& !empty($_GET[$k])) {
                if ($v[1] == 'equal') {
                    $where[$k] = $_GET[$k];
                } elseif($v[1] == 'like') {
                    $where[$k] = array('like', '%'.escape_string(trim($_GET[$k])).'%');
                }
                $search_data[$k] = $_GET[$k];
            }

            //单个时间字段可以选择时间区间
            if($v[0] == 2) {
                $column = $k;
                $start = $k. '_start';
                $end = $k. '_end';

                if (!empty($_GET[$start]) && !empty($_GET[$end])) {
                    $where[$column] = array(
                        array('egt', strtotime($_GET[$start])),
                        array('elt', strtotime($_GET[$end]. '23:59:59')),
                    );
                    $search_data[$start] = $_GET[$start];
                    $search_data[$end] = $_GET[$end];
                } elseif (!empty($_GET[$start])) {
                    $where[$column] = array('egt', strtotime($_GET[$start]));
                    $search_data[$start] = $_GET[$start];
                } elseif (!empty($_GET[$end])) {
                    $where[$column] = array('elt', strtotime($_GET[$end]. '23:59:59'));
                    $search_data[$end] = $_GET[$end];
                }

            }

            //单个时间字段，开始时间
            if($v[0] == 3) {

            }

            //单个时间字段，结束时间
            if($v[0] == 4) {

            }

            //下拉框
            if($v[0] == 5 && isset($_GET[$k])) {
                $where[$k] = $_GET[$k];
                $search_data[$k] = $_GET[$k];
            }
        }
        import("@.ORG.Page");
        
        $count = $model->table($this->table)->where($where)->count();
        $page = new Page($count, $this->pagesize);
        if ($this->order_field) {
            $list = $model->table($this->table)->where($where)->limit($page->firstRow.','.$page->listRows)->order($this->order_field)->select();
        } else {
            $list = $model->table($this->table)->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        }
        $show = $page->show();//分页显示输出
        $data = array();
        foreach ($list as $k => $row) {
            $tmp = $this->format($row);
            $data[] = $tmp;
        }
        $this->assign('list', $data);
        $this->assign('page', $show);
        $this->assign('search_data', $search_data);
        $this->display('index');
    }

    public function format($row)
    {
        $tmp = array();
        foreach ($this->display_field as $k => $v) {
            $d = $row[$k];
            if ($k == 'anzhi_act') {
                $d = "<a href='__URL__/edit_{$this->config}/{$this->key}/{$row[$this->key]}' class='thickbox'>编辑</a>&nbsp;&nbsp;<a href='__URL__/view_{$this->config}/del/{$this->key}/{$row[$this->key]}'>查看</a>";
            } elseif(!empty($v)) {
                $cmd = '$d='. preg_replace('/%row\.([^%]+)%/', "\$row['$1']", $v);
                eval($cmd);
            } else {
                
                if (is_array($this->field_map[$k])) {
                    $d = $this->field_map[$k][1][$d];
                }
            }
            $tmp[] = $d;
        }
        return $tmp;
    }

    public function add() {
        if ($_POST) {
            // 必填字段
            $ness = array(
                'package' => '包名',
                'start_time' => '开始时间',
                'end_time' => '结束时间',
            );
            $data = array();
            foreach ($ness as $key => $value) {
                if (!$_POST[$key])
                    $this->error("{$vlaue}不能为空");
                if ($key == 'start_time')
                    $data[$key] = strtotime($_POST[$key]);
                else if ($key  == 'end_time')
                    $data[$key] = strtotime($_POST[$key]) + 86399;
                else
                    $data[$key] = $_POST[$key];
            }
            $model = M();
            // 检查包名是否存在
            $find = $this->package_search($data['package']);
            if (!$find)
                $this->error("包名不存在！");
            // 检查开始时间是否小于结束时间
            if ($data['start_time'] >= $data['end_time'])
                $this->error("开始时间需小于结束时间");
            // 检查相同包名有效期冲突
            $where = "package='{$data['package']}' and ((start_time>={$data['start_time']} and start_time<={$data['end_time']}) or (end_time>={$data['start_time']} and end_time<={$data['end_time']})) and status=1";
            $conflict = $model->table('sj_search_adapter')->where($where)->find();
            if ($conflict) {
                $this->error('时间与已存在记录存在冲突！');
            }
            $ret = $model->table('sj_search_adapter')->add($data);
            if ($ret) {
                $this->assign('jumpUrl', 'index');
				$this->writelog("搜索适配：添加了id为{$ret}的记录");
                $this->success('添加成功！');
            } else {
                $this->error('添加失败！');
            }
        } else {
            $this->display('add_package');
        }
    }
    
    public function edit() {
        $this->checkConfig();

        $id = $_GET[$this->key];
        $where = array(
            $this->key => $id,
        );
        $model = M();

        if ($_POST) {
            $data = array();
            $error = array();
            foreach ($this->edit_field as $k => $v) {
                if ($v[1] == 1 && empty($_POST[$k])) {
                    $error[] = "{$this->field_map[$k]} {$k}不能为空";
                    continue;
                }
                //普通文本字段
                if($v[0] == 1) {
                    $data[$k] = $_POST[$k];
                }

                //时间字段
                if($v[0] == 2) {
                    if (!empty($_POST[$k])) {
                        $data[$k] = strtotime($_POST[$k]);
                    } else {
                        $data[$k] = 0;
                    }
                }
            }
            if ($error) {
                //$this->error(implode("\n", $error));
            }
			$log = $this->logcheck($where, $this->table, $data, $model);
            $ret = $model->table($this->table)->where($where)->save($data);
            if ($ret || $ret === 0) {
				$this->writelog("搜索适配：编辑了id为{$_POST['id']}的记录，{$log}");
                $this->assign('jumpUrl', '__URL__/index_'. $this->config);
                $this->success('编辑成功！');
            } else {
                $this->error('编辑失败！');
            }
        } else {
            $find = $model->table($this->table)->where($where)->find();
            $this->assign('row', $find);
            $this->assign('edit_url', "__URL__/edit_{$this->config}/{$this->key}/{$_GET[$this->key]}");
            $this->display('edit');
        }
    }

    public function view()
    {
        $this->checkConfig();
        if ($_GET[$this->key]) {
            $id = $_GET[$this->key];
            $model = M();
            $where = array(
                $this->key => $id,
            );
            $find = $model->table($this->table)->where($where)->find();
            $this->assign('row', $find);
            $this->display('view');
        }       
    }
    
    public function del() {
        $this->checkConfig();
        if ($_GET['id']) {
            $id = $_GET['id'];
            $model = M();
            $where = array('id' => $id);
            $data = array('status' => 0);
            $ret = $model->table('sj_search_adapter')->where($where)->save($data);
            if ($ret) {
				$this->writelog("搜索适配：删除了id为{$id}的记录");
                $this->success("删除成功！");
            } else {
                $this->success("删除失败！");
            }
        }
    }

    public function checkConfig()
    {
        if (empty($this->config)) {
            $this->error('参数错误');
        }
        $this->assign('field_map', $this->field_map);
        $this->assign('edit_field', $this->edit_field);
        $this->assign('display_field', $this->display_field);
        $this->assign('search_field', $this->search_field);
        $this->assign('config', $this->config);
    }

    public function __call($method,$parms)
    {
        list($action, $config) = explode('_', $method);
        $this->config = $config;
        var_dump($config);
        switch($config) {
            case 'adlog';
                $this->key = 'id';
                $this->field_map = array(
                    'softname' => '软件名称',
                    'package' => '包名',
                    'subname' => '软件类型',
                    'parent_name' => '软件大类',
                    'extent_name' => '位置',
                    'type' => '合作形式',
                    'ad_type' => '运营方式',
                    'create_at' => '时间',
                );
                $this->search_field = array(
                    'package' => array(1, 'equal'),
                    'create_at' => array(2),
                );
                $this->display_field = array(
                    'create_at' => 'substr(date("Ymd", %row.create_at%), 0, 8);',
                    'softname' => '',
                    'package' => '',
                    'subname' => '',
                    'parent_name' => '',
                    'type' => '',
                    'extent_name' => 'mb_substr(%row.extent_name%, 0, 4);',
                    'ad_type' => '',
                );
                $this->insert_field = array();
                $this->edit_field = array();
                $this->order_field = 'create_at desc';
                $this->table = 'sj_extent_ad_log';
            break;

            case 'jump':
                $this->key = 'id';
                $this->field_map = array(
                    'id' => '',           
                    'content_type' => array(
                        '通用跳转类型',
                        array(
                            '1' => '类型1',
                            '2' => '类型2',
                            '3' => '类型3',
                            '4' => '类型4',
                        )
                    ), 
                    'mark' => 'flag值',         
                    'page' => '页面名称',         
                    'reg_exp' => '页面标识',      
                    'priority' => '',     
                    'list_key' => '列表缓存key',     
                    'param' => '通用跳转参数',
                    'order_key1' => '标识别名',   
                    'limit' => '列表数量限制',        
                    'api_key' => '接口key',      
                    'api_param' => '接口参数',
                );
                $this->search_field = array();
                $this->display_field = array(  
                    'mark' => '',    
                    'reg_exp' => '',    
                    'page' => '',
                    'anzhi_act' => array('操作','edit,delete')
                );
                $this->edit_field = array(   
                    'content_type' => array(1, 1), 
                    'mark' => array(1, 1),         
                    'page' => array(1, 1),         
                    'reg_exp' => array(1, 1),      
                    'list_key' => array(1, 1),     
                    'param' => array(1, 1),        
                    'order_key1' => array(1, 1),   
                    'limit' => array(1, 1),        
                    'api_key' => array(1, 1),      
                    'api_param' => array(1, 1),
                );
                $this->table = 'sj_market_jump_info';
            break;

            case 'bulletin':
                $this->key = 'id';
                $this->field_map = array(
                    'id' => '',
                    'content' => '内容',
                    'start_tm' => '开始时间',
                    'end_tm' => '结束时间',
                    'create_tm' => '添加时间',
                    'update_tm' => '更新时间',
                    'status' => array('状态', array(0=>'删除',1=>'正常')),
                    'pos' => '位置',
                    'dev_prompt' => '',
                );
                $this->table = 'sj_anzhi_bulletin';

                $this->search_field = array(
                    'content' => array(1, 'like'),//equal
                    'create_tm' => array(2),
                    'status' => array(5)
                );
                $this->display_field = array(  
                    'content' => 'mb_substr(strip_tags(%row.content%), 0, 20). "...";',    
                    'start_tm' => 'date("Y-m-d", %row.start_tm%);',    
                    'end_tm' => 'date("Y-m-d", %row.end_tm%);',
                    'create_tm' => 'date("Y-m-d", %row.create_tm%);',
                    'status' => '',
                    'anzhi_act' => array('操作','edit,delete')
                );
                $this->edit_field = array(   
                    'content' => array(1, 1), 
                    'start_tm' => array(2, 1), 
                    'end_tm' => array(2, 1), 
                    'create_tm' => array(2, 1), 
                    'status' => array(5, 1), 
                );

                $this->insert_field = array(   
                    'content' => array(1, 1), 
                    'start_tm' => array(2, 1), 
                    'end_tm' => array(2, 1), 
                    'create_tm' => array(2, 1), 
                    'status' => array(5, 1), 
                );
            break;
            default:
                if($this->develop){
                    $table = $this->config;
                    var_dump($table);
                    $model = D('Anzhi.CommonData');
                    $model->check_common_config();

                }else{
                    $this->error('未配置此功能');
                }

        }
        $this->$action();
    }
}
?>