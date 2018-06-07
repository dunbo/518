<?php
class ProductimfailModel extends Model {
     protected $trueTableName = 'yx_fail';

    /**
     *  新增导入失败数据
     *  @final       2013-09-02
     */	
    public function importfailadd($data,$thistime)
    {
        $data['desc'] = iconv('gbk','utf-8',serialize($data));
        $data['uncreatetime'] = $thistime;
        $this->add($data);
    }

    /**
     *  根据时间获取内容
     *  @final       2013-09-02
     */	
    public function getimportfail($thistime)
    {
        return $this->field("`desc`")->where("uncreatetime='$thistime'")->find();
    }
}
?>
