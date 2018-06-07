<?php

class NewHotFeatureAction extends CommonAction {
    protected $table = 'new_hot_feature';
    private $max_rank = 3;
    
    private $img_width;
    private $img_height;
    private $img_max_MB;
    private $img_max_size;
    
    public function __construct() {
        parent::__construct();
        $this->img_width = 200;
        $this->img_height = 105;
        $this->img_max_MB = 2;
        $this->img_max_size = $this->img_max_MB*1024*1024;
    }
    
    
    public function index() {
        $bbs_model = D('Bbs_manage.Bbs_manage');
        
        $where = array(
            'status' => 1
        );
        $list = $bbs_model->table($this->table)->where($where)->order('rank asc')->select();
        
        $this->assign('img_host', IMGATT_HOST);
        $this->assign('list', $list);
        $this->display();
    }

    public function batch_rank() {
        $bbs_model = D('Bbs_manage.Bbs_manage');
        $arr = array();
        foreach ($_POST as $name => $rank) {
            $ret = preg_match("/^rank_(\d+)$/", $name, $matches);
            if (!$ret)
                continue;
            $id = $matches[1];
            // 判断输入的rank是不是数字
            $rank = trim($rank);
            if (!preg_match('/^\d+$/', $rank)) {
                $this->ajaxReturn('', "排序调整无效，排序值需为小于等于{$this->max_rank}的正整数", -1);
            }
            if ($rank < 1 || $rank > $this->max_rank) {
                $this->ajaxReturn('', "排序调整无效，排序值不可超过该模块最大位置数{$this->max_rank}");
            }
            $arr[$id] = $rank;
        }

        foreach ($arr as $id1 => $rank1) {
            foreach ($arr as $id2 => $rank2) {
                if ($rank1 ==  $rank2 && $id1 != $id2) {
                    $this->ajaxReturn("", "排序调整无效，排序不可以相同", -1);
                }
            }
        }
        
        // 更新表
        $each_log = array();
        $ids = '';
        foreach ($arr as $id => $rank) {
            $ids .= $id.',';
            $where = array(
                'id' => $id
            );
            $data = array(
                'rank' => $rank,
                'update_tm' => time()
            );
            $log_result = $this -> logcheck($where, $this->table, $data, $bbs_model);
            $each_log[] = "编辑了id为{$id}的记录，{$log_result}";
            $ret = $bbs_model->table($this->table)->where($where)->save($data);
        }
        $log = implode(';', $each_log);
        $this->writelog("论坛PC端管理-新精品资源-专题管理：批量编辑排序，{$log}",$this->table,$ids,__ACTION__ ,"","edit");
        $this->ajaxReturn('', "更新排序成功！", 0);
    }
    
    public function add() {
        if ($_POST) {
            $bbs_model = D('Bbs_manage.Bbs_manage');
            $label_name = trim($_POST['label_name']); 
            $link_url = trim($_POST['link_url']);
            $rank= trim($_POST['rank']);
            $img_file = $_FILES['img_file'];

            if ($label_name == '') {
                $this->error("专题名称不能为空");
            }
            if ($link_url == '') {
                $this->error("链接地址不能为空");
            }
            if (!$this->check_url($link_url)) {
                $this->error("请填写有效的链接地址");
            }
            if ($rank == '') {
                $this->error("排序不能为空");
            }
            if (!preg_match('/^[1-9]\d*$/', $rank)) {
                $this->error("排序值需为小于等于{$this->max_rank}的正整数");
            }
            if ($rank > $this->max_rank) {
                $this->error("排序值不可超过该模块最大位置数{$this->max_rank}");
            }
            if ($img_file['name'] == '') {
                $this->error("图片不能为空");
            }
            // 检查此位置是否已有排序
            $conflict_id = $this->check_rank($rank, $id);
            if ($conflict_id) {
                $this->error("已存在排序为{$rank}的记录");
            }
            
            if ($img_file['error'] != 0) {
                $this->error("上传图片出错");
            }
            // 检查图片大小
            if ($img_file['size'] > $this->img_max_size) {
                $this->error("图片大小不超过{$this->img_max_MB}MB");
            }
            // 检查尺寸
            $img_info_arr = getimagesize($img_file['tmp_name']);
            if (!$img_info_arr) {
                $this->error("上传图片出错");
            }
            $width = $img_info_arr[0];
            $height = $img_info_arr[1];
            if ($width != $this->img_width || $height != $this->img_height) {
                $this->error("图片尺寸应为{$this->img_width}*{$this->img_height}");
            }
            // 检查后缀
            $img_file_name = trim($img_file['name']);
            $suffix = array_pop(explode('.', $img_file_name));
            if ($suffix != 'jpg' && $suffix != 'jpeg' && $suffix != 'png' && $suffix != 'bmp') {
                $this->error("图片格式错误，应为jpg，bmp，jpeg和png");
            }
            
            // 将图片存储起来
            $folder = UPLOAD_PATH . '/img/' . date('Ym/d');
            if (!is_dir($folder)) {
                $this->mkDirs($folder);
            }
            $img_new_name = str_replace('.', '', microtime(true)) . rand(1000, 9999) . ".{$suffix}";
            $img_path = $folder . '/' . $img_new_name;
            $ret = move_uploaded_file($img_file['tmp_name'], $img_path);
            if (!$ret) {
                $this->error("上传图片出错");
            }
            $img_relative_path = str_replace(UPLOAD_PATH, '', $img_path);

            $data = array();
            $data['create_tm'] = $data['update_tm'] = time();
            $data['status'] = 1;

            $data['label_name'] = $label_name;
            $data['link_url'] = $link_url;
            $data['rank'] = $rank;
            $data['img_path'] = $img_relative_path;
            
            $ret = $bbs_model->table($this->table)->add($data);
            if ($ret) {
                $this->writelog("论坛PC端管理-新精品资源-专题管理：新增了id为{$ret}的专题",$this->table,$ret,__ACTION__ ,"","add");
                $this->success('添加成功！');
            } else {
                $this->error('添加失败！');
            }
        } else {
            $this->display();
        }
    }

    public function edit() {
        $bbs_model = D('Bbs_manage.Bbs_manage');
        if ($_GET['id']) {
            $id = $_GET['id'];
            $where = array(
                'id' => $id
            );
            $find = $bbs_model->table($this->table)->where($where)->find();
            $this->assign('list', $find);
            $this->display();
        } else if ($_POST){
            $id = $_POST['id'];
            $label_name = trim($_POST['label_name']);
            $link_url = trim($_POST['link_url']);
            $rank = trim($_POST['rank']);
 
            if ($label_name == '') {
                $this->error("专题名称不能为空");
            }
            if ($link_url == '') {
                $this->error("链接地址不能为空");
            }
            if (!$this->check_url($link_url)) {
                $this->error("请填写有效的链接地址");
            }
            if ($rank == '') {
                $this->error("排序不能为空");
            }
            if (!preg_match('/^[1-9]\d*$/', $rank)) {
                $this->error("排序值需为小于等于{$this->max_rank}的正整数");
            }
            if ($rank > $this->max_rank) {
                $this->error("排序值不可超过该模块最大位置数{$this->max_rank}");
            }
            // 检查此位置是否已有排序
            $conflict_id = $this->check_rank($rank, $id);
            if ($conflict_id) {
                $this->error("已存在排序为{$rank}的记录");
            }
            
            $img_file = $_FILES['img_file'];
            if ($img_file['name'] != '') {
                if ($img_file['error'] != 0) {
                    $this->error("上传图片出错");
                }
                // 检查图片大小
                if ($img_file['size'] > $this->img_max_size) {
                    $this->error("图片大小不超过{$this->img_max_MB}MB");
                }
                // 检查尺寸
                $img_info_arr = getimagesize($img_file['tmp_name']);
                if (!$img_info_arr) {
                    $this->error("上传图片出错");
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                if ($width != $this->img_width || $height != $this->img_height) {
                    $this->error("图片尺寸应为{$this->img_width}*{$this->img_height}");
                }
                // 检查后缀
                $img_file_name = trim($img_file['name']);
                $suffix = array_pop(explode('.', $img_file_name));
                if ($suffix != 'jpg' && $suffix != 'jpeg' && $suffix != 'png' && $suffix != 'bmp') {
                    $this->error("图片格式错误，应为jpg，bmp，jpeg和png");
                }
                
                // 将图片存储起来
                $folder = UPLOAD_PATH . '/img/' . date('Ym/d');
                if (!is_dir($folder)) {
                    $this->mkDirs($folder);
                }
                $img_new_name = str_replace('.', '', microtime(true)) . rand(1000, 9999) . ".{$suffix}";
                $img_path = $folder . '/' . $img_new_name;
                $ret = move_uploaded_file($img_file['tmp_name'], $img_path);
                if (!$ret) {
                    $this->error("上传图片出错");
                }
                $img_relative_path = str_replace(UPLOAD_PATH, '', $img_path);
            }
            
            $where = array(
                'id' => $id
            );
            $data = array();
            $data['update_tm'] = time();

            $data['label_name'] = $label_name;
            $data['link_url'] = $link_url;
            $data['rank'] = $rank;
            
            if ($img_file['name'] != '') {
                $data['img_path'] = $img_relative_path;
            }
            
            $log_result = $this -> logcheck($where, $this->table, $data, $bbs_model);
            $ret = $bbs_model->table($this->table)->where($where)->save($data);
            if ($ret) {
                $this->writelog("论坛PC端管理-新精品资源-专题管理：编辑了id为{$id}的专题，{$log_result}",$this->table,$id,__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }

        }
    }
    
    public function del() {
        if ($_GET['id']) {
            $bbs_model = D('Bbs_manage.Bbs_manage');
            $id = $_GET['id'];
            $where = array(
                'id' => $id,
                'status' => 1
            );
            $data = array(
                'update_tm' => time(),
                'status' => 0
            );
            $ret = $bbs_model->table($this->table)->where($where)->save($data);
            if ($ret) {
                $this->writelog("论坛PC端管理-新精品资源-专题管理：专删除了id为{$id}的专题",$this->table,$id,__ACTION__ ,"","del");
                $this->success("删除成功");
            } else {
                $this->error("删除失败");
            }
        }
    }

    private function check_rank($rank, $except_id) {
        // check if rank exists
        $where = array(
            'rank' => $rank,
            'status' => 1
        );
        if ($except_id) {
            $where['id'] = array('neq', $except_id);
        }

        $bbs_model = D('Bbs_manage.Bbs_manage');
        $find = $bbs_model->table($this->table)->where($where)->find();
        if ($find) {
            return $find['id'];
        }
        return 0;
    }
    
    public function check_url($url) {
        $reg = "/^((http:\/\/)|(https:\/\/))([\w\d-]+\.)+[\w-]+(\/[\x{4e00}-\x{9fa5}\d\w-.\/?%&=]*)?$/iu";
        if (!preg_match($reg, $url)) {
            return false;
        }
        return true;
    }
}
