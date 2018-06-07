<?php
class CpdMonthExpendAction extends CommonAction {
    public $cpd_statistics_data='cpd_statistics_data';
	public $cpd_statistics_user='cpd_statistics_user';
  
    public function month_expend_list()
	{
        $model = M('');
        $where=array();
        $where['status'] = 1;

        if($_GET['s_package'] && $s_package = trim($_GET['s_package'])){
            $where['package'] = array('eq', $s_package);
            $this->assign("package", $s_package);
        }
        if($_GET['s_product'] && $s_product = trim($_GET['s_product'])){
            $where['product'] = array('eq', $s_product);
            $this->assign("product", $s_product);
        }
        if($_GET['s_company'] && $s_company = trim($_GET['s_company'])){
            $this->assign("company", $s_company);
            $user_data=$model->table($this->cpd_statistics_user)->where(array('company'=>$s_company))->select();
            $user_id=array();
            foreach($user_data as $v){
                $user_id[]=$v['id'];
            }
            if($user_id){
                $where['userid'] = array('in', $user_id);
            }
            
            
        }
        if($_GET['begintime'] && $begintime = trim($_GET['begintime'])){
            $where["month"] = array('egt', $begintime);
            $this->assign("begintime", $_GET['begintime']);
        }
        if($_GET['endtime'] && $endtime = trim($_GET['endtime'])){
            $where["month"] = array('elt', $endtime);
            $this->assign("endtime", $_GET['endtime']);
        }
        if($begintime && $endtime){
            $where["month"] = array('exp', ">=$begintime and month<=$endtime");
        }
        $count_list = $model->table($this->cpd_statistics_data)->where($where)->select();
		$count = count($count_list);
        import("@.ORG.Page");
		$p = new Page ($count, 20);
		$list = $model->table($this->cpd_statistics_data) -> where($where)->limit($p->firstRow.','.$p->listRows)->order('month desc') -> select();
        $page = $p->show();
		$this -> assign("page",$page);
        $user_id_arr=array();
        foreach ($list as $key => $v) 
		{
            $user_id_arr[]=$v['userid'];
			
        }
        if($user_id_arr){
            $user_data=$model->table($this->cpd_statistics_user)->where(array('id'=>array('in',$user_id_arr)))->select();
            $user_data_new=array();
            foreach($user_data as $v){
                $user_data_new[$v['id']]=$v;
            }
        }
        $this->assign('list', $list);
        $this->assign('user_data_new', $user_data_new);
        $this->display();
    }
    
    public function add_month_expend() 
	{
        if($_POST) 
		{
            $model = M('');
            $map = array();
			$map['create_tm']=time();
            $map['status']=1;
            $map['product']=trim($_POST['product']);
            $map['package']=trim($_POST['package']);
            $map['month']=trim($_POST['month']);
            $map['download']=trim($_POST['download']);
            $map['price']=trim($_POST['price']);
			$map['cost']=trim($_POST['cost']);
            if($company=trim($_POST['company'])){
                $user=$model->table($this->cpd_statistics_user)->where(array('company'=>$company))->find();
                if($user){
                    $map['userid']=$user['id'];
                }else{
                    $this->error("公司名称不存在");
                }
            }
            if(!is_numeric($map['cost'])){
                $this->error("消费金额必须为数字");
            }
            if(!is_numeric($map['cost'])){
                $this->error("单价必须为数字");
            }
            if(!preg_match("/^\d*$/",$map['download'])){
                $this->error("计费下载量必须为数字");
            }
            if(!$map['month']){
                $this->error("月份必填");
            }

            $statistics_data=$model->table($this->cpd_statistics_data)->where(array('userid'=>$map['userid'],'month'=>$map['month'],'product'=>$map['product']))->find();
            if($statistics_data){
                $this->error("一个公司下的一个产品的一个月份只能录入一条。");
            }
            // 添加
            $ret = $model->table($this->cpd_statistics_data)->add($map);
            if ($ret) {
                $this->writelog("CPD数据统计-CPD月消耗：添加了id为{$ret}的记录",$this->cpd_statistics_data,$ret,__ACTION__ ,"","add");
                $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/CpdMonthExpend/month_expend_list/');
                $this->success("添加成功！");
            } else {
                $this->error("添加失败");
            }
        }
		else 
		{
            $this->display();
        }
    }
    public function edit_month_expend() 
    {
        $model = M('');
        if($_POST) 
        {
            $id=trim($_POST['id']);
          
            if(!$id){
                $this->error('缺少ID');
            }
            $map = array();
            // $map['create_tm']=time();
            $map['status']=1;
            $map['id']=$id;
            $map['product']=trim($_POST['product']);
            $map['package']=trim($_POST['package']);
            $map['month']=trim($_POST['month']);
            $map['download']=trim($_POST['download']);
            $map['price']=trim($_POST['price']);
            $map['cost']=trim($_POST['cost']);
            if($company=trim($_POST['company'])){
                $user=$model->table($this->cpd_statistics_user)->where(array('company'=>$company))->find();
                if($user){
                    $map['userid']=$user['id'];
                }else{
                    $this->error("公司名称不存在");
                }
            }
            if(!is_numeric($map['cost'])){
                $this->error("消费金额必须为数字");
            }
            if(!is_numeric($map['cost'])){
                $this->error("单价必须为数字");
            }
            if(!preg_match("/^\d*$/",$map['download'])){
                $this->error("计费下载量必须为数字");
            }
            if(!$map['month']){
                $this->error("月份必填");
            }

            $statistics_data=$model->table($this->cpd_statistics_data)->where(array('userid'=>$map['userid'],'month'=>$map['month'],'product'=>$map['product'],'id'=>array('neq',$id)))->find();
            if($statistics_data){
                $this->error("一个公司下的一个产品的一个月份只能录入一条。");
            }
            $log = $this -> logcheck(array('id' =>$id),$this->cpd_statistics_data,$map,$model);
            $ret = $model->table($this->cpd_statistics_data)->save($map);
            if ($ret) {
                $this->writelog("CPD数据统计-CPD月消耗：编辑了id为{$id}的记录.{$log}",$this->cpd_statistics_data,$id,__ACTION__ ,"","edit");
                $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/CpdMonthExpend/month_expend_list/');
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败");
            }
        }
        else 
        {
            if(!$_GET['id']){
                $this->error('缺少ID');
            }
            $statistics_data=$model->table($this->cpd_statistics_data)->where(array('id'=>$_GET['id']))->find();
            $user_data=$model->table($this->cpd_statistics_user)->where(array('id'=>$statistics_data['userid']))->find();
            $statistics_data['company']=$user_data['company'];
            $this->assign('statistics_data', $statistics_data);
            $this->display();
        }
    }

    public function delete_month_expend(){
        if(!$_GET['id']){
            $this->error('缺少ID');
        }
        $model = M('');
        $res = $model->table($this->cpd_statistics_data)->where(array('id'=>$_GET['id']))->save(array('status'=>0));
        if($res){
            $this->writelog("CPD数据统计-CPD月消耗：删除了id为{$_GET['id']}的记录",$this->cpd_statistics_data,$_GET['id'],__ACTION__ ,"","del");
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }



    // 批量导入访问的页面节点
    function import_softs() {
        $CustomList = D("Sj.CustomList");
        
        if ($_GET['down_moban']) {
            $CustomList->down_moban(2);
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $CustomList->import_file_to_array($tmp_name,$this);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $CustomList->import_array_convert_and_check($content_arr,$this);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            // var_dump($error_msg);
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'cpd_statistics_data_importing';
            // S($lock_name, NULL);die;
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
            
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            $count=0;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
                if($value['flag'] == 1){
                    $count++;
                }
            }
            
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            $ret = move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("CPD数据统计-cpd月消耗：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("","成功添加{$count}个cpd月消耗", 0);
            } 
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $model = M('');
        foreach($content_arr as $key => $record) {

            $map = array();
            // 设置默认值
            $map['status'] = 1;
            $map['create_tm'] = time();
            $map['package'] = $record['package'];
            $map['month'] = $record['month'];
            $map['product'] = $record['product'];
            $map['download'] = $record['download'];
            $map['price'] = $record['price'];
            $map['cost'] = $record['cost'];
            $where = array(
                'company' => array('EQ', $record['company']),
            );
            $find = $model->table($this->cpd_statistics_user)->where($where)->find();
            $map['userid'] = $find['id'];

            // 添加到表中
            if ($id = $model->table($this->cpd_statistics_data)->add($map)) {
                $this->writelog("添加了月消耗id为{$id}.",'cpd_statistics_data',$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
            }else {
                //未知原因添加失败
                $result_arr[$key]['flag'] = 0;
                $result_arr[$key]['msg'] = "添加失败";
            }
        }
        return $result_arr;
    }
    

    function handwriting_convert_and_check(&$content_arr) {
        $CustomList = D("Sj.CustomList");
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $CustomList->init_error_msg($error_msg, $key);
        }

        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'company'  =>   '公司',
            'month'  =>   '月份',
            'product'  =>   '产品',
            'package'  =>   '包名',
            'download'  =>   '计费下载量',
            'price'  =>   '单价',
            'cost'  =>   '消费金额',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        
        return $error_msg;
    }

    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr) {
        $CustomList = D("Sj.CustomList");
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $CustomList->init_error_msg($error_msg, $key);
        }
        $model = M('');

        
        // 业务逻辑：以下为各项具体检查

        foreach($content_arr as $key=>$record) {

            // 检测区间ID
            if (isset($record['company']) && $record['company'] != "") {
                $where = array(
                    'company' => array('EQ', $record['company']),
                    // 'status' => array('EQ', 1),
                );
                $find = $model->table($this->cpd_statistics_user)->where($where)->find();
                if (!$find) {
                    $CustomList->append_error_msg($error_msg, $key, 1, "公司名称【{$record['company']}】不存在;");
                }else{
                    $content_arr[$key]['userid']=$find['id'];
                }
            }else{
                $CustomList->append_error_msg($error_msg, $key, 1, "公司名称不能为空;");
            }
            
            if(!is_numeric($record['cost'])){
                $CustomList->append_error_msg($error_msg, $key, 1, "消费金额必须为数字;");
            }
            if(!is_numeric($record['price'])){
                $CustomList->append_error_msg($error_msg, $key, 1, "单价必须为数字;");
            }
            if(!preg_match("/^\d*$/",$record['download'])){
                $CustomList->append_error_msg($error_msg, $key, 1, "计费下载量必须为数字;");
            }
            if(!preg_match("/^\d*$/",$record['month'])){
                $CustomList->append_error_msg($error_msg, $key, 1, "月份必须为数字;");
            }
            
            


            // if (!ContentTypeModel::convertPackage2Softname($content_arr[$key]['package'])) {
            //     $CustomList->append_error_msg($error_msg, $key, 1, "包名【{$content_arr[$key]['package']}】不存在于市场软件库中;");
            // }
        }

        // var_dump($content_arr);die;
        foreach($content_arr as $key1=>$record1) {
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                if ($record1['product'] == $record2['product'] && $record1['month'] == $record2['month'] &&$record1['userid'] == $record2['userid']) {
                    $k1 = $key1 + 1; $k2 = $key2 + 1;
                    $CustomList->append_error_msg($error_msg, $key1, 1, "一个公司下的一个产品的一个月份只能录入一条。月消耗与第{$k2}行有重叠;");
                    $CustomList->append_error_msg($error_msg, $key2, 1, "一个公司下的一个产品的一个月份只能录入一条。月消耗与与第{$k1}行有重叠;");
                }
            }
        }

        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
                $where = array(
                    'userid' => $record['userid'],
                    'product' => $record['product'],
                    'month' => $record['month'],
                    'status' => 1,
                );
                
                $db_record = $model->table($this->cpd_statistics_data)->where($where)->find();
                if($db_record){
                    $CustomList->append_error_msg($error_msg, $key, 1, "一个公司下的一个产品的一个月份只能录入一条。此条数据已存在;");
                }

        }

        return $error_msg;
    }
}
?>
