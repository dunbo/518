<?php
    class ScheduleImportFailModel extends Model {
        protected $trueTableName = "yx_schedule_fail";
        
        public function import_fail_add($data, $this_time) {
            $data['desc'] = iconv('gbk','utf-8',serialize($data));
            $data['uncreatetime'] = $this_time;
            return $this->add($data);
        }
        
        public function get_import_fail($this_time) {
            return $this->field("`desc`")->where("uncreatetime='$this_time'")->find();
        }
    }
?>