<?php

class CustomListModel extends Model {
    
    public function getNameRecordByName($name) {
        $where = array(
            'name' => $name,
            'status' => 1,
        );
        $find = $this->field('id')->table('sj_custom_list_name')->where($where)->find();
        return $find;
    }
	//增加所有自定义的编码和名字
	public function getAllCustom() {
        $where = array(
            'status' => 1,
        );
        $result = $this->field('id,name')->table('sj_custom_list_name')->where($where)->select();
        return $result;
    }
    
    public function getNameRecordLikeName($name_like) {
        $where = array(
            'name' => array('like', "%{$name_like}%"),
            'status' => 1,
        );
        $list = $this->field('name')->table('sj_custom_list_name')->where($where)->select();
        return $list;
    }
    
    public function getNameRecordById($id) {
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $find = $this->table('sj_custom_list_name')->where($where)->find();
        return $find;
    }
    

    //下载批量导入模版
    function down_moban($type) {
        if ($type == 1) {
            $file_dir = C("ADLIST_PATH") . "custom_soft_import_moban.csv";
            $file_name = '自定义列表软件';
        }elseif($type == 2){
            $file_dir = C("ADLIST_PATH") . "cpd_month_expend_import_moban.csv";
            $file_name = 'CPD月消耗';
        }
        
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode($file_name . '批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }

    // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file,&$c_this) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $c_this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $c_this->convert_encoding($r_value);
            }
        }
        return $content_arr;
    }
    

    function import_array_convert_and_check(&$content_arr,&$cc_this) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $cc_this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $cc_this->logic_check($content_arr);

        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }

        return $error_msg1;
    }

    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }

    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
}

?>