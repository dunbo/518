<?php
function insertOrderList(& $list, $order_list)
{
	asort($order_list);
	$result = array();
	$start = 0;
	$old_pos = 0;
	
	foreach($order_list as $k => $v) {
		if (isset($list[$k])) {
			unset($list[$k]);
		} else {
			unset($order_list[$k]);
		}
	}
	$total = count($list);
	foreach($order_list as $k => $v) {
		$len = $v - 1 - $old_pos;
		$old_pos = $v;
		$tmp = array_slice($list, $start, $len, true);
		$tmp[$k] = $v;
		$start = $start + $len;
		$result = $result + $tmp;
	}
	if ($start < $total) {
		$result = $result + array_slice($list, $start, null, true);
	}
	return $result;
}

function array_shuffle($list)
{
    if (!is_array($list)) return $list; 

    $keys = array_keys($list); 
    shuffle($keys); 
    $random = array(); 
    foreach ($keys as $key) {
        $random[$key] = $list[$key]; 
    }

    return $random; 
}

function ad_generate($n, $param) {
    # sort by p and reserve index
    $ip = array();
	$h = 0;
    foreach ($param as $idx => $val) {
		if ($val[1] == 0) {
			unset($param[$idx]);
			continue;
		}
        $ip[$idx] = $val[1];
		if ($val[1] == 100) {
			$h++;
		}
    }
	$l = count($param);
	if ($n > $l) $n = $l;
    uasort($ip, create_function('$a,$b', 'return ($b - $a);'));
    $p = array_values($ip);
    rsort($p);
    $i = 0;
    $pi = array();
    foreach ($ip as $idx => $val)
        $pi[$i++] = $idx;
    # build partition
    $max = 0;
    $partition = array(0);
    $i = 0;
    for ($i = 0; $i < count($param); $i++) {
        $partition[] = $max + $p[$i];
        $max += $p[$i];
    }
    # generate result
    $used = array();
    $result = array();
    $min = -1;
    for ($i = 0; $i < $n; $i++) {
        do {
            if ($p[$i] == 100)
                $index = rand(0, $h - 1);
            else {
                if ($min < 0)
                    $min = $partition[$i];
                $r = rand($min, $max - 1);
	            $index = ad_find_index($partition, $r);
            }
        } while ($index < 0 || isset($used[$index]));
        $result[] = $param[$pi[$index]][0];
        $used[$index] = 0;
    }
    return $result;
}

function ad_find_index($partition, $v) {
    $i = 0;
    $j = count($partition);
    if ($v < $partition[$i] || $v >= $partition[$j - 1])
        return -1;
    if ($j < 3)
        return $partition[1] > $v ? 0 : 1;
    for (; $j - $i > 1;) {
        $m = intval(($i + $j) / 2);
        if ($v < $partition[$m])
            $j = $m;
        if ($v >= $partition[$m])
            $i = $m;
    }
    return $i;
}