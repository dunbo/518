<?php
/**
 * Desc:   报表项目-软件黑名单
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class BlacklistModel extends Model {
     protected $trueTableName = 'yx_blacklist';

    /**
     *  添加新黑名单
     *  @final       2013-09-10
     */	
    public function addblack($package)
    {
        $data = array();
        $data['package'] = $package;
        $data['createtime'] = time();
        return $this->add($data);
    }

    /**
     *  根据包名删除黑名单
     *  @final       2013-09-10
     */	
    public function delblack($package)
    {
        return $this->where("package = '$package'")->delete();
    }
}
?>
