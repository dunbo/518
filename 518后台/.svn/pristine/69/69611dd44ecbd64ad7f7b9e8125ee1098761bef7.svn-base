<?php
/**
 * Desc:   报表产品权限配置
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class CompetenceConfigAction extends CommonAction {
    private $yx_admin_table;
    
    public function __construct() {
        parent::__construct();
        $this->yx_admin_table = D('sendNum.CompetenceConfig');
    }

    // 获得已有管理员列表
    function adminList() {        
        import("@.ORG.Page");
        $count = $this->yx_admin_table->where("1=1")->count();//查询满足要求的总记录数
        $Page = new Page($count,15);//实例化分页类传入总记录数和每页显示的记录数
        $show = $Page->show();//分页显示输出
        //进行分页数据查询注意limit方法的参数要使用Page类的属性
        $list = $this->yx_admin_table->where('1=1')->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);//赋值数据集
        $this->assign('page',$show);//赋值分页输出
        $this->display('adminList');//输出模板
    }
    
    // 添加管理员，当用户不存在或已添加过的用户不允许添加
    function add() {
        if ($_POST) {
            $name = $_POST['admin_name'];
            $auth_online = $_POST['auth_online'];
            $auth_schedule = $_POST['auth_schedule'];
            $rs = $this->yx_admin_table->addAdmin($name, $auth_online, $auth_schedule);
            if ($rs === -1)
                $this->ajaxReturn(0,'用户名不存在', -1);
            else if($rs === -2)
                $this->ajaxReturn(0,'该用户已添加', -1);
			else if ($rs) {
                $this->writelog('报表-权限配置-添加人员，添加人员为：'.$name, 'yx_admin',$rs,__ACTION__ ,'','add');
                $this->ajaxReturn($rs,'添加成功！', 0);
            }
            else
                $this->ajaxReturn(0,'添加失败', -1);
            
        }
        $this->display('add');
    }
    // 删除管理员
	function delete() {
        if ($_POST) {
            $name = '';
            foreach ($_POST['admin_id'] as $id) {
                $arr = $this->yx_admin_table->getAdminUserName($id);
                $name .= $arr['admin_user_name'] . ', ';
                $this->yx_admin_table->deleteAdmin($id);
            }
            $this->writelog('报表-权限配置-删除人员，删除人员为：'.$name, 'yx_admin',$id,__ACTION__ ,'','del');
            $this->success('删除成功');
        }
    }
    
}
?>
