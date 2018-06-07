<?php

class ShareTextAction extends CommonAction {

    // 分享方式
    private $share_way_arr = array(
        'weixinhaoyou' => '微信好友',
        'pengyouquan' => '朋友圈',
        'qqhaoyou' => 'QQ好友',
        'qqkongjian' => 'QQ空间',
        'weibo' => '微博',
        'duanxin' => '短信',
    );

    // 配置中分享值对应的文案
    private $share_way_link_text = array(
        1 => '被分享的软件在M站的软件详情页地址',
        2 => '被分享的软件在M站的极速下载地址',
        3 => '被分享的软件在M站的下载地址',
        4 => '安智市场在M站的软件详情页地址',
        5 => '安智市场在M站的下载地址',
    );
    
    public function index() {
    
        $model = M();
        // 邀请安装安智市场
        $where = array(
            'config_type' => 'SHARE_TEXT_ANZHI',
            'status' => 1,
        );
        $ret = $model->table('pu_config')->where($where)->find();
        $configcontent = $ret['configcontent'];
        $share_anzhi_config_arr = json_decode($configcontent, true);
        
        $share_anzhi_config = array();
        $share_anzhi_config['text'] = $share_anzhi_config_arr['text'];
        foreach ($share_anzhi_config_arr['share_way_link'] as $key => $value) {
            $share_anzhi_config['share_way_link'][$key] = $this->share_way_link_text[$value];
        }
        
        // 分享软件
        $where = array(
            'config_type' => 'SHARE_TEXT_SOFT',
            'status' => 1,
        );
        $ret = $model->table('pu_config')->where($where)->find();
        $configcontent = $ret['configcontent'];
        $share_soft_config_arr = json_decode($configcontent, true);
        $share_and_link_config = $share_soft_config_arr['share_way_link'];
        
        foreach ($share_and_link_config as $key => $value) {
            $share_soft_config_arr['share_way_link'][$key] = $this->share_way_link_text[$value];
        }
        $this->assign('share_anzhi_config', $share_anzhi_config);
        $this->assign('share_soft_config', $share_soft_config_arr);
        $this->assign('share_way_arr', $this->share_way_arr);
        $this->display();
    }
    
    public function edit_share_anzhi() {
        if ($_POST) {
            $text = trim($_POST['text']);
            if (!$text) {
                $this->error("文案不能为空");
            }
            // 不同分享方式对应的分享链接配置
            $share_way_link = array();
            /* 暂时隐藏
            foreach ($this->share_way_arr as $key => $name) {
                $tmp = trim($_POST[$key]);
                if (!$tmp) {
                    $this->error("{$name}文案链接不能为空");
                }
                $share_way_link[$key] = $tmp;
            }
            */
            $data = array();
            $data['text'] = $text;
            $data['share_way_link'] = $share_way_link;
            
            $model = M();
            $where = array(
                'config_type' => 'SHARE_TEXT_ANZHI',
                'status' => 1
            );
            $data_json = json_encode($data);
            $map = array('configcontent' => $data_json);
            $log_result = $this -> logcheck($where,'pu_config',$map,$model);
            $ret = $model->table('pu_config')->where($where)->save($map);
            if ($ret || $ret === 0) {
                $this -> writelog("{$log_result}",'pu_config','',__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error('编辑失败！');
            }
            
        } else {
            $model = M();
            // 邀请安装安智市场
            $where = array(
                'config_type' => 'SHARE_TEXT_ANZHI',
                'status' => 1,
            );
            $ret = $model->table('pu_config')->where($where)->find();
            $configcontent = $ret['configcontent'];
            $share_anzhi_config_arr = json_decode($configcontent, true);
            
            $this->assign('share_anzhi_config', $share_anzhi_config_arr);
            $this->assign('share_way_link_text', $this->share_way_link_text);
            $this->assign('share_way_arr', $this->share_way_arr);
            $this->display();
        }
    }
    
    public function edit_share_soft() {
        if ($_POST) {
            $text = trim($_POST['text']);
            if (!$text) {
                $this->error("文案不能为空");
            }
            // 不同分享方式对应的分享链接配置
            $share_way_link = array();
            /* 暂时隐藏
            foreach ($this->share_way_arr as $key => $name) {
                $tmp = trim($_POST[$key]);
                if (!$tmp) {
                    $this->error("{$name}文案链接不能为空");
                }
                $share_way_link[$key] = $tmp;
            }
            */
            $data = array();
            $data['text'] = $text;
            $data['share_way_link'] = $share_way_link;
            
            $model = M();
            $where = array(
                'config_type' => 'SHARE_TEXT_SOFT',
                'status' => 1
            );
            $data_json = json_encode($data);
            $map = array('configcontent' => $data_json);
            $log_result = $this -> logcheck($where,'pu_config',$map,$model);
            $ret = $model->table('pu_config')->where($where)->save($map);
            if ($ret || $ret === 0) {
                $this -> writelog("{$log_result}",'pu_config',"config_type:SHARE_TEXT_SOFT",__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error('编辑失败！');
            }
            
        } else {
            $model = M();
            // 软件详情页-分享软件
            $where = array(
                'config_type' => 'SHARE_TEXT_SOFT',
                'status' => 1,
            );
            $ret = $model->table('pu_config')->where($where)->find();
            $configcontent = $ret['configcontent'];
            $share_soft_config_arr = json_decode($configcontent, true);
            
            $this->assign('share_soft_config', $share_soft_config_arr);
            $this->assign('share_way_link_text', $this->share_way_link_text);
            $this->assign('share_way_arr', $this->share_way_arr);
            $this->display();
        }
    }
}

?>