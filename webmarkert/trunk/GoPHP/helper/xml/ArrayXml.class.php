<?php
/**
 * XML解析类，把XML文件内容当作一个数组来处理(将XML文本分解成数组或将数组序列化成XML文本)
 *
 */
require_once('XmlParser.class.php');
class ArrayXml {
    /**
     * 从XML文件读取信息
     * @param string $filename XML文件名
     * @param string $startNode 返回结果的起始节点
     * @return array 返回XML内容数组
     */
    static function loadFromFile($filename, $startNode='') {
        if (is_file($filename)) {
            $xml = self::unserializeXml(file_get_contents($filename));
        } else {
            $xml = self::unserializeXml($filename);
        }
        if ($startNode) {
        	$startNode = explode('/', $startNode);
        	foreach ($startNode as $node); {
        		$xml = (isset($xml[$node])) ? $xml = $xml[$node] : array();
        	}
        }
        return $xml;
    }

    /**
     * 将信息写入XML文件中
     * @param string $filename XML文件名
     * @param array $xml XML数据数组
     * @param string $root 是否给信息添加根节点
     * @return boolean 如果写入文件成功,则返回写入的字节数,否则返回false
     */
    static function saveToFile($filename, $xml, $root='') {
        if ($root) {
            $xml = array($root => $xml);
        }
        $xmlString = self::serializeXml($xml);
        return file_put_contents($filename, $xmlString);
    }
    
    /**
     * XML数组初始化，当打开一个空文件时执行此函数格式化XML数组
     * @param array &$xml XML内容数组
     * @param array $nodes 需要初始化的节点数组
     */
    static function nodeInit(& $xml, $nodes) {
        if (!is_array($xml)) {
            $xml = array();
        }
        if (!is_array($nodes)) {
            $nodes = func_get_args();
            array_shift($nodes);
        }
        foreach ($nodes as $node) {
            if (!isset($xml[$node])) {
                $xml[$node] = array();
            }
            $xml = & $xml[$node];
        }
    }

    /**
     * 序列化XML
     * @param array &$data XML数据数组
     * @param int $level 当前处理的标签层次(外部调用时应该总是等于0)
     * @param string $priorKey 上一级的标签名词(外部调用时应该总是等于null)
     * @return string & 返回序列化后的XML文本
     */
    static function & serializeXml(&$data, $level = 0, $priorKey = NULL) {
        $serializedXmlString = '';
        foreach ($data as $key => $value) {
            $inline = false;
            $numericArray = false;
            $attributes = '';
            if (!strstr($key, ' attr') && $value!==NULL) { // 如果不是一个属性
                if (array_key_exists('$key' . ' attr', $data)) {
                    foreach ($data['$key' . ' attr'] as $attrName => $attrValue) {
                        $attrValue = &htmlspecialchars($attrValue, ENT_QUOTES);
                        $attributes .= ' ' . $attrName . '="' . $attrValue . '"';
                    }
                }
    
                if (is_numeric($key)) {
                    $key = $priorKey;
                } else {
                    if (is_array($value) and array_key_exists(0, $value)) {
                        $numericArray = true;
                        $serializedXmlString .= self::serializeXml($value, $level, $key);
                    }
                }
    
                if (!$numericArray) {
                    $serializedXmlString .= str_repeat("\t", $level) . "<$key$attributes>";
                    if (is_array($value)) {
                        $inner = self::serializeXml($value, $level+1);
                        if ($inner) {
                            $serializedXmlString .= "\r\n" . $inner;
                        }
                    } else {
                        $inline = true;
                        $serializedXmlString .= htmlspecialchars($value, ENT_QUOTES);
                    }
                    $serializedXmlString .= ((!$inline) ? str_repeat("\t", $level) : '') . "</$key>\r\n";
                }
            }
        }
        if ($level == 0) {
            $serializedXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\r\n" . $serializedXmlString;
        }
        return $serializedXmlString;
    }

    /**
     * 反序列化XML
     * @param string $xml 需要解析成数据数组的XML文本
     * @return array & XML数据数组
     */
    static function & unserializeXml($xml) {
        $xmlParser = new PEAR_XMLParser();
        $data = array();
        if ($xmlParser->parse($xml)) {
            $data = $xmlParser->getData();
        }
        
        return $data;
    }
}
?>