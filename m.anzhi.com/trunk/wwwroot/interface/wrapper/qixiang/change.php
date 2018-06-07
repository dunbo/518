<?

define('IN_QIXIANG', 1);

include(dirname(realpath(__FILE__)). "/func.php");

class wrapper_qixiang_change
{
	public $params;
	public function __construct($param)
	{
		$this->params = $param;
	}

    public function getData() {
	$old_data = getData();
        # 访问过才有数据可以比较
        if (!$old_data) {
            $result = array(
                "add" => array(),
                "modify" => array(),
                "del" => array(),
            );
            return json_encode($result);
        }
        $old_data_p = array();
        $old_data_h = array();
        foreach ($old_data as $val) {
            $p = $val['package_name'];
            $old_data_p[$p] = $val;
            # 不计算评论数变化
            unset($val['rating_count']);
            $old_data_h[$p] = md5(json_encode($val));
        }
        $new_data = getData(true);
        $new_data_p = array();
        $new_data_h = array();
        foreach ($new_data as $val) {
            $p = $val['package_name'];
            $new_data_p[$p] = $val;
            # 不计算评论数变化
            unset($val['rating_count']);
            $new_data_h[$p] = md5(json_encode($val));
        }
        $add = array();
        $modify = array();
        $del = array();
        foreach ($new_data_h as $p => $h) {
            if (isset($old_data_h[$p])) {
                if ($old_data_h[$p] != $new_data_h[$p]) {
                    $modify[] = $new_data_p[$p];
                }
            }
            else {
                $add[] = $new_data_p[$p];
            }
        }
        foreach ($old_data_h as $p => $h) {
            if (!isset($new_data_h[$p])) {
                $del[] = $old_data_p[$p];
            }
        }
        $result = array(
            'add' => $add,
            'modify' => $modify,
            'del' => $del,
        );
        return json_encode($result);
    }
}
