<?php
define('DS', DIRECTORY_SEPARATOR);
define('WWWROOT', dirname(realpath(__FILE__)));
define('GO_APP_ROOT', dirname(realpath(__FILE__)). DS. '..'. DS. 'newgomarket.goapk.com');
include_once GO_APP_ROOT. DS.'..'.DS. 'GoPHP'. DS. 'Startup.php';
$lastmod = date('Y-m-d');
function gomarket_action($module_action, $parameters)
{
    load_library('GoService');

    list($module, $action) = explode('.', $module_action);
    $action_file = GO_APP_ROOT. DS. 'modules' . DS. strtolower($module). DS. $action. '.php';
    if (!file_exists($action_file)) {
        return False;
    }
    include_once $action_file;
    if (!class_exists($action)) {
        return False;
    }
    $actionClass = new $action;
    $actionClass->parameters = $parameters;
    return $actionClass->execute();
}
abstract class GoAction
{
    abstract public function execute();
    public function getParameter($key = null, $default = '')
    {
        $parameter = isset($this->parameters[$key])? $this->parameters[$key] : $default;
        return $parameter;
    }
}
$sitemap_body = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset>
<{item}>
</urlset>
EOF;

$sitemap_tpl = <<<EOF
<url>
<loc><{loc}></loc>
<lastmod><{lastmod}></lastmod>
<changefreq><{changefreq}></changefreq>
<priority><{priority}></priority>
</url>
EOF;

$sitemap_index_body = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex>
<{item}>
</sitemapindex>
EOF;
$sitemap_index_tpl = <<<EOF
<sitemap>
<loc><{loc}></loc>
<lastmod><{lastmod}></lastmod>
</sitemap>
EOF;
$tpl_config = array(
    'sitemap' => array($sitemap_body, $sitemap_tpl),
    'sitemap_index' => array($sitemap_index_body, $sitemap_index_tpl)
);
$config = array(
    array(
        'req' => '{"KEY":"SOFT_SUB_CATEGORY","ID":1, "LIST_INDEX_START":0,"LIST_INDEX_SIZE":40}',
        'path' => 'soft.GoGetSoftCatelog',
        'index_key' => 4,
        'changefreq' => 'daily', //always、hourly、daily、weekly、monthly、yearly、never
        'priority' => '1.0',
        'urls' => array(
            array(
                'file' => 'sitemap.xml',
                'type' => 'sitemap',
                'url' => 'http://www.anzhi.com/sort_<{index}>_1_new.html',
            ),
            array(
                'file' => 'sitemap.xml',
                'type' => 'sitemap',
                'url' => 'http://www.anzhi.com/sort_<{index}>_1_hot.html',
            ),
            array(
                'file' => 'sitemap_index.xml',
                'type' => 'sitemap_index',
                'url' => 'http://www.anzhi.com/sitemap/app_<{index}>_new.xml',
            ),
            array(
                'file' => 'sitemap_index.xml',
                'type' => 'sitemap_index',
                'url' => 'http://www.anzhi.com/sitemap/app_<{index}>_hot.xml',
            ),
        ),
        'sub' => array(
            array(
                'req' => '{"KEY":"SOFT_CATEGORY_ALL_LIST","ID":<{pindex}>,"LIST_INDEX_START":0,"LIST_INDEX_SIZE":100,"ORDER":0}',
                'path' => 'soft.GoGetCategoryAllSoftList',
                'changefreq' => 'hourly',
                'priority' => '0.8',
                'index_key' => 0,
                'urls' => array(
                    array(
                        'file' => 'sitemap/app_<{pindex}>_new.xml',
                        'type' => 'sitemap',
                        'url' => 'http://www.anzhi.com/soft_<{index}>.html',
                    ),
                ),
            ),
            array(
                'req' => '{"KEY":"SOFT_CATEGORY_ALL_LIST","ID":<{pindex}>,"LIST_INDEX_START":0,"LIST_INDEX_SIZE":100,"ORDER":1}',
                'path' => 'soft.GoGetCategoryAllSoftList',
                'changefreq' => 'hourly',
                'priority' => '0.8',
                'index_key' => 0,
                'urls' => array(
                    array(
                        'file' => 'sitemap/app_<{pindex}>_hot.xml',
                        'type' => 'sitemap',
                        'url' => 'http://www.anzhi.com/soft_<{index}>.html',
                    ),
                ),
            ),
        )
    ),

    array(
        'req' => '{"KEY":"SOFT_SUB_CATEGORY","ID":2,"LIST_INDEX_START":0,"LIST_INDEX_SIZE":40}',
        'path' => 'soft.GoGetSoftCatelog',
        'index_key' => 4,
        'changefreq' => 'daily', //always、hourly、daily、weekly、monthly、yearly、never
        'priority' => '1.0',
        'urls' => array(
            array(
                'file' => 'sitemap.xml',
                'type' => 'sitemap',
                'url' => 'http://www.anzhi.com/sort_<{index}>_1_new.html',
            ),
            array(
                'file' => 'sitemap.xml',
                'type' => 'sitemap',
                'url' => 'http://www.anzhi.com/sort_<{index}>_1_hot.html',
            ),
            array(
                'file' => 'sitemap_index.xml',
                'type' => 'sitemap_index',
                'url' => 'http://www.anzhi.com/sitemap/game_<{index}>_new.xml',
            ),
            array(
                'file' => 'sitemap_index.xml',
                'type' => 'sitemap_index',
                'url' => 'http://www.anzhi.com/sitemap/game_<{index}>_hot.xml',
            ),
        ),
        'sub' => array(
            array(
                'req' => '{"KEY":"SOFT_CATEGORY_ALL_LIST","ID":<{pindex}>,"LIST_INDEX_START":0,"LIST_INDEX_SIZE":100,"ORDER":0}',
                'path' => 'soft.GoGetCategoryAllSoftList',
                'changefreq' => 'hourly',
                'priority' => '0.8',
                'index_key' => 0,
                'urls' => array(
                    array(
                        'file' => 'sitemap/game_<{pindex}>_new.xml',
                        'type' => 'sitemap',
                        'url' => 'http://www.anzhi.com/soft_<{index}>.html',
                    ),
                ),
            ),
            array(
                'req' => '{"KEY":"SOFT_CATEGORY_ALL_LIST","ID":<{pindex}>,"LIST_INDEX_START":0,"LIST_INDEX_SIZE":100,"ORDER":1}',
                'path' => 'soft.GoGetCategoryAllSoftList',
                'changefreq' => 'hourly',
                'priority' => '0.8',
                'index_key' => 0,
                'urls' => array(
                    array(
                        'file' => 'sitemap/game_<{pindex}>_hot.xml',
                        'type' => 'sitemap',
                        'url' => 'http://www.anzhi.com/soft_<{index}>.html',
                    ),
                ),
            ),
        )
    ),

    array(
        'req' => '{"KEY":"SOFT_SUBJECT","LIST_INDEX_START":0,"LIST_INDEX_SIZE":20}',
        'path' => 'soft.GoGetSoftSubject',
        'index_key' => 1,
        'index_pre' => 'get_feature_id',
        'urls' => array(
            array(
                'file' => 'sitemap.xml',
                'type' => 'sitemap',
                'url' => 'http://www.anzhi.com/subject_<{index}>.html',
            ),
            array(
                'file' => 'sitemap_index.xml',
                'type' => 'sitemap_index',
                'url' => 'http://www.anzhi.com/sitemap/feature_<{index}>.xml',
            ),
        ),
        'changefreq' => 'daily', //always、hourly、daily、weekly、monthly、yearly、never
        'priority' => '1.0',
        'sub' => array(
            array(
                'req' => '{"KEY":"SOFT_SUBJECT_ALL_LIST","ID":<{pindex}>,"LIST_INDEX_START":0,"LIST_INDEX_SIZE":20}',
                'index_key' => 0,
                'path' => 'soft.GoGetSoftSubjectAllList',
                'changefreq' => 'hourly',
                'priority' => '0.8',
                'urls' => array(
                    array(
                        'file' => 'sitemap/feature_<{pindex}>.xml',
                        'type' => 'sitemap',
                        'url' => 'http://www.anzhi.com/soft_<{index}>.html',
                    ),
                ),
            ),
        )
    ),
);

function get_list($config, & $file_list, $pindex = '')
{
    $config['req'] = str_replace('<{pindex}>', $pindex, $config['req']);
    $config['file'] = str_replace('<{pindex}>', $pindex, $config['file']);


    $param = json_decode($config['req'], true);
    $path = $config['path'];
    $changefreq = $config['changefreq'];
    $priority = $config['priority'];
    $index = $config['index_key'];

    $urls = $config['urls'];

    $list = gomarket_action($path, $param);
    $result = array();

    foreach ($list['DATA'] as $key => $value) {
        $index_value = $value[$index];
        if (isset($config['index_pre'])) {
            $index_value = $config['index_pre']($index_value);
        }
        foreach ($urls as $val) {
            $file = $val['file'];
            $type = $val['type'];
            $file = str_replace('<{pindex}>', $pindex, $file);
            $file = str_replace('<{index}>', $index_value, $file);

            !isset($file_list[$type][$file]) && $file_list[$type][$file] = array();
            $url = $val['url'];
            $url = str_replace('<{pindex}>', $pindex, $url);
            $url = str_replace('<{index}>', $index_value, $url);


            $file_list[$type][$file][] = array($url, $changefreq, $priority);    
        }

        if (isset($config['sub'])) {
            foreach ($config['sub'] as $key => $value) {
                get_list($value, $file_list, $index_value);
            }
        }
    }


    return $result;
}

function get_feature_id($real_id)
{
    if ($real_id > 1000000) {
        $real_id = intval($real_id / 1000000);
    }
    return $real_id;
}

function process_sitemap()
{
    global $config, $tpl_config, $lastmod;
    $file_list = array(
        'sitemap' => array(),
        'sitemap_index' => array(),
    );
    foreach ($config as $key => $value) {
        get_list($value, $file_list);
    }

    foreach ($file_list as $tpl_key => $value) {
        list($body, $tpl) = $tpl_config[$tpl_key];
        foreach ($value as $file => $item) {
            # code...
            $_body = $body;
            $_item = '';

            foreach ($item as $k => $v) {
                # code...
                $xml = $tpl;
                list($loc, $changefreq, $priority) = $v;
                $xml = str_replace('<{loc}>', $loc, $xml);
                $xml = str_replace('<{changefreq}>', $changefreq, $xml);
                $xml = str_replace('<{priority}>', $priority, $xml);
                $xml = str_replace('<{lastmod}>', $lastmod, $xml);
                $xml .= "\n";
                $_item .= $xml;
            }
            $_body = str_replace('<{item}>', $_item, $_body);
			$file = WWWROOT. '/'. $file;
            file_put_contents($file, $_body);
        }

    }
}
process_sitemap();