<?php

class AdCheckIfScheduledModel extends Model {
    //////////////////////////////////////判断包在具体广告位是否已排期的函数
    // 首页推荐
    public function checkIfScheduled_Ex($soft) {
        $ret = false;
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $extent_id = $tmp_arr[1];
        $where = array(
            'extent_id' => $extent_id,
            'package' => $package,
            'start_at' => array('elt', $ad_time),
            'end_at' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_extent_soft_v1')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 轮播图
    public function checkIfScheduled_LB($soft) {
        $ret = false;
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $where = array(
            'package' => $package,
            'begintime' => array('elt', $ad_time),
            'endtime' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_ad')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 装机必备
    public function checkIfScheduled_FE($soft) {
        $ret = false;
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $where = array(
            'feature_id' => C("ZJBB_FEATURE_ID"), //装机必备配置的id
            'package' => $package,
            'start_tm' => array('elt', $ad_time),
            'end_tm' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_feature_soft')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 应用热门
    public function checkIfScheduled_AH($soft) {
        $ret = false;
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $extent_id = $tmp_arr[1];
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $where = array(
            'extent_id' => $extent_id,
            'package' => $package,
            'start_at' => array('elt', $ad_time),
            'end_at' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_category_extent_soft')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 游戏热门
    public function checkIfScheduled_GH($soft) {
        $ret = false;
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $extent_id = $tmp_arr[1];
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $where = array(
            'extent_id' => $extent_id,
            'package' => $package,
            'start_at' => array('elt', $ad_time),
            'end_at' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_category_extent_soft')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 分类置顶(TODO，好像有误)
    public function checkIfScheduled_CA($soft) {
        $ret = false;
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $page_type = $tmp_arr[1];
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $extent_id = $tmp_arr[2];
        $where = array(
            'extent_id' => $extent_id,
            'package' => $package,
            'start_at' => array('elt', $ad_time),
            'end_at' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_category_extent_soft')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 最新
    public function checkIfScheduled_TN($soft) {
        $ret = false;
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $extent_id = $tmp_arr[1];
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $where = array(
            'extent_id' => $extent_id,
            'package' => $package,
            'start_at' => array('elt', $ad_time),
            'end_at' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_category_extent_soft')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 搜索
    public function checkIfScheduled_SK($soft) {
        $ret = false;
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $search_words = $tmp_arr[1];
        $words_arr = explode(',', $search_words);
        // 挨个关键字分别在三个地方查找
        foreach ($words_arr as $word) {
            // 运营管理-热词显示页面查找有没有这个运营这个关键字
            $where = array(
                'key_word' => $word,
                'start_tm' => array('elt', $ad_time),
                'end_tm' => array('egt', $ad_time),
                'status' => 1,
            );
            $find = $this->table('sj_search_keywords')->where($where)->find();
            if (!$find) {
                $ret = false;
                break;
            }
            // 默认关键字管理查找是否运营了这个关键字
            $where = array(
                'key_words' => $word,
                'start_time' => array('elt', $ad_time),
                'end_time' => array('egt', $ad_time),
                'status' => 1,
            );
            $find = $this->table('sj_soft_defaultkeywords')->where($where)->find();
            if (!$find) {
                $ret = false;
                break;
            }
            // 搜索关键字列表中是否已运营了此关键字
            $where = array(
                'srh_key' => $word,
                'start_tm' => array('elt', $ad_time),
                'stop_tm' => array('egt', $ad_time),
                'status' => 1,
            );
            $find = $this->table('sj_search_key')->where($where)->find();
            if (!$find) {
                $ret = false;
                break;
            }
            // 得到此关键字的kid
            $kid = $find['id'];
            // 该搜索关键字是否已添加此包名（位置暂时不判断）
            $where = array(
                'kid' => $kid,
                'package' => $package,
                'start_tm' => array('elt', $ad_time),
                'stop_tm' => array('egt', $ad_time),
                'type' => 0,
                'status' => 1,
            );
            $find = $this->table('sj_search_key_package')->where($where)->find();
            if (!$find) {
                $ret = false;
                break;
            }
            $ret = true;
        }
        return $ret;
    }
    
    // 必备
    public function checkIfScheduled_NE($soft) {
        $ret = false;
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $extent_id = $tmp_arr[1];
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $where = array(
            'extent_id' => $extent_id,
            'package' => $package,
            'start_at' => array('elt', $ad_time),
            'end_at' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_necessary_extent_soft')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    // 猜你喜欢（用户还下载了）
    public function checkIfScheduled_PR($soft) {
        $ret = false;
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        $where = array(
            'soft_package' => $package,
            'start_tm' => array('elt', $ad_time),
            'end_tm' => array('egt', $ad_time),
            'status' => 1,
        );
        $find = $this->table('sj_soft_recommend')->where($where)->find();
        if ($find) {
            $ret = true;
        }
        return $ret;
    }
    
    ///////////////////////////////////////具体广告位名称生成函数
    // 首页推荐
    public function getExtentName_EX($extent_code, $first_name) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $extent_id = $tmp_arr[1];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $find = $this->table('sj_extent_v1')->where($where)->find();
        if ($find) {
            $last_extent_name = $find['extent_name'];
        }
        $extent_name = "{$first_name}-{$last_extent_name}";
        return $extent_name;
    }
    
    // 轮播图
    public function getExtentName_LB($extent_code, $first_name) {
        return $first_name;
    }
    
    // 装机必备
    public function getExtentName_FE($extent_code, $first_name) {
        return $first_name;
    }
    
    // 应用热门
    public function getExtentName_AH($extent_code, $first_name) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $extent_id = $tmp_arr[1];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $find = $this->table('sj_category_extent')->where($where)->find();
        if ($find) {
            $last_extent_name = $find['extent_name'];
        }
        $extent_name = "{$first_name}-{$last_extent_name}";
        return $extent_name;
    }
    
    // 游戏热门
    public function getExtentName_GH($extent_code, $first_name) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $extent_id = $tmp_arr[1];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $find = $this->table('sj_category_extent')->where($where)->find();
        if ($find) {
            $last_extent_name = $find['extent_name'];
        }
        $extent_name = "{$first_name}-{$last_extent_name}";
        return $extent_name;
    }
    
    // 类别置顶
    public function getExtentName_CA($extent_code, $first_name) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $page_type = $tmp_arr[1];
        $CotentTypeModel = D('ContentType');
        $ca_map = $CotentTypeModel::getCategoryTypes();
        $page_name = $ca_map[$page_type];
        $extent_id = $tmp_arr[2];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $find = $this->table('sj_category_extent')->where($where)->find();
        if ($find) {
            $last_extent_name = $find['extent_name'];
        }
        $extent_name = "{$first_name}-{$page_name}-{$last_extent_name}";
        return $extent_name;
    }
    
    // 最新
    public function getExtentName_TN($extent_code, $first_name) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $extent_id = $tmp_arr[1];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $find = $this->table('sj_category_extent')->where($where)->find();
        if ($find) {
            $last_extent_name = $find['extent_name'];
        }
        $extent_name = "{$first_name}-{$last_extent_name}";
        return $extent_name;
    }
    
    // 搜索
    public function getExtentName_SK($extent_code, $first_name) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $last_extent_name = $tmp_arr[1];
        $extent_name = "{$first_name}-{$last_extent_name}";
        return $extent_name;
    }
    
    // 必备
    public function getExtentName_NE($extent_code, $first_name) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $extent_id = $tmp_arr[1];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $find = $this->table('sj_necessary_extent')->where($where)->find();
        if ($find) {
            $last_extent_name = $find['extent_name'];
        }
        $extent_name = "{$first_name}-{$last_extent_name}";
        return $extent_name;
    }
    
    // 猜你喜欢（用户还下载了）
    public function getExtentName_PR($extent_code, $first_name) {
        return $first_name;
    }
}
?>