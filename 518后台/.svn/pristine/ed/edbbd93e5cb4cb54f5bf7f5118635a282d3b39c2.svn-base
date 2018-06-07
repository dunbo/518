<?php
    class UnusedsoftwarelistAction extends CommonAction {
        
        public function index() {
            $model = M();
            $where = array(
                'new_status' => array('EQ', 3),
            );
            // 计算总记录数
            $count = $model->table('cj_new_sowftware')->join('inner join cj_new_software_ignored on cj_new_sowftware.new_sid=cj_new_software_ignored.new_sid')->where($where)->count();
            
            // 生成页面类
            import("@.ORG.Page");
            $page = new Page($count, 10);
            
            // 分配当前页数据
            $Datalist = $model->table('cj_new_sowftware')->join('inner join cj_new_software_ignored on cj_new_sowftware.new_sid=cj_new_software_ignored.new_sid')->where($where)->order('ignore_time desc')->limit($page->firstRow.','.$page->listRows)->select();
            
            // 把`忽略原因`trim成20个字符
            foreach ($Datalist as $key=>$val) {
                $Datalist[$key]['ignore_reason'] = $this->mb_trimstr($val['ignore_reason'], 20);
            }
            
            $page->setConfig('header','篇记录');
            $page->setConfig('first','<<');
            $page->setConfig('last','>>');
            $show =$page->show();
            
            $this->assign("Datalist" , $Datalist);
            $this->assign("page", $show);
            $this->display("index");
        }
        
        public function show_full_content() {
            if ($_GET) {
                $id = $_GET['id'];
                $type = $_GET['type'];
                $model = M();
                $where = array(
                    'id' => array('EQ', $id),
                );
                $data = $model->field("`{$type}`")->table('cj_new_software_ignored')->where($where)->find();
                if ($data)
                    $this->assign('content', $data["{$type}"]);
            }
            $this->display('show_full_content');
        }
        
        // 将字符串缩减至$len的长度，多出来用的...替换
        function mb_trimstr($str, $len) {
            if (mb_strlen($str, 'utf-8') > 20)
                return mb_substr($str, 0, 20, 'utf-8') . "...";
            return $str;
        }
        
    }

?>