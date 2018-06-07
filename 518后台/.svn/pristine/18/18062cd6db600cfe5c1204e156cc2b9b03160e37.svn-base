<?php

class Products2Model extends Model {
    
    protected $trueTableName = 'sj_market';
        
    public function modify_in_use($id, $status) {
        $data['status'] = $status;
        $this->where("id=$id")->save($data);
    }
    // 更新时间
    public function modify_last_refresh($id, $last_refresh) {
        $data['last_refresh'] = $last_refresh;
        $this->where("id=$id")->save($data);
    }
    
    public function add_data($data) {
        $ret = $this->add($data);
        //file_put_contents("add_sql.txt", $this->getlastsql());
        //echo $this->getlastsql();
        //exit(0);
        return $ret;
    }
    
    public function save_data($id, $data) {
        $where = array();
        $where['id'] = array('EQ', $id);
        $ret = $this->where($where)->save($data);
        return $ret;
    }
    
    public function get_record_by_id($id) {
        $where = array();
        $where['id'] = Array('EQ', $id);
        return $this->where($where)->select();
    }
    
    
    public function getlist($request, $tab=1, $getall=0) {
        // $where = "status=1";
        //file_put_contents("222.txt", $this->getlastsql());
        
        $sqlparam='';
        
        if ($tab == 1) {
            $sqlparam = $sqlparam.'status=1&';
            $where['status'] = array('EQ', 1);
        } else {
            $sqlparam = $sqlparam.'status=0&';
            $where['status'] = array('EQ', 0);
        }
        
        if (isset($request['version_code']) && strlen($request['version_code']) > 0) {
            $sqlparam = $sqlparam.'version_code='.$request['version_code'].'&';
            $where['version_code'] = array('EQ', $request['version_code']);
        }
        
        if(isset($request['version_name']) && strlen($request['version_name']) > 0) {
            $sqlparam = $sqlparam.'version_name='.$request['version_name'].'&';
            $where['version_name'] = array('like', '%'.$request['version_name'].'%'); 
        }
        
        if (isset($request['cid']) && strlen($request['cid']) > 0) {
            $sqlparam = $sqlparam.'cid='.$request['cid'].'&';
            $where['cid'] = array('EQ', $request['cid']);
        }
        
        if (isset($request['did'])) {
            $did_str = '';
            $like_arr = array();
            foreach($request['did'] as $key => $value) {
                if ($key != 0)
                    $did_str .=',';
                $did_str .= $value;
                $like_arr[] = array('like', '%'.$value.'%');
            }
            $sqlparam = $sqlparam.'did='.$did_str.'&';
            $where['exclude_did'] = $like_arr;
        }
        
        if (isset($request['remark']) && strlen($request['remark']) > 0) {
            $sqlparam = $sqlparam.'remark='.$request['remark'].'&';
            $where['remark'] = array('like', '%'.$request['remark'].'%');
        }
        
        if (isset($request['id']) && strlen($request['id']) > 0) {
            $sqlparam = $sqlparam.'id='.$request['id'].'&';
            $where['id'] = array('EQ', $request['id']);
        }
        
        if (isset($request['force_update']) && strlen($request['force_update']) > 0 && $request['force_update'] != -1) {
            $sqlparam = $sqlparam.'force_update='.$request['force_update'].'&';
            $where['force_update'] = array('EQ', $request['force_update']);
        }
        
        if (isset($request['platform']) && strlen($request['platform']) > 0) {
            $sqlparam = $sqlparam.'platform='.$request['platform'].'&';
            $where['platform'] = array('EQ', $request['platform']);
        }
        
        if (isset($request['target_version_code']) && strlen($request['target_version_code']) > 0) {
            $sqlparam = $sqlparam.'target_version_code='.$request['target_version_code'].'&';
            $where['target_version_code'] = array('like', '%'.$request['target_version_code'].'%');
        }
        
        if (isset($request['type']) && strlen($request['type']) > 0 && $request['type'] != 0) {
            $sqlparam = $sqlparam.'type='.$request['type'].'&';
            $where['type'] = array('EQ', $request['type']);
        }
        
        if (isset($request['wifi_load']) && strlen($request['wifi_load']) > 0 && $request['wifi_load'] != 0) {
            $sqlparam = $sqlparam.'wifi_load='.$request['wifi_load'].'&';
            $where['wifi_load'] = array('EQ', $request['wifi_load']);
        }
        
        
        import("@.ORG.Page");
        $count = $this->where($where)->count();
        $page = new Page($count, 10);
        $page->parameter = $sqlparam;
        $show = $page->show();        
        
        if (!$getall)
            $list = $this->where($where)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        else
            $list = $this->where($where)->order('id')->select();
        
        $res = array(
            'list' => $list,
            'page' => $show,
            //'sqlparam' => $sqlparam,
        );
        return $res;
    }
    
}
