<?php
header('content-type:text/html;charset="gbk"');
echo '<pre>';

print_r(getFileMissedOrAdded('1', '2'));

// 比对两个文件夹的MD5值，返回文件夹$dir2比$dir1少或多的文件
function getFileMissedOrAdded($dir1, $dir2){
    $src = globdir($dir1);
    $dealed = globdir($dir2, '*', GLOB_BRACE, true);

    $md5_src = $md5_deal= array();
    array_walk($src, function($file) use(&$md5_src){
        $md5_src[$file] = md5_file($file);
    });

    array_walk($dealed, function($file) use(&$md5_deal){
        $md5_deal[$file] = md5_file($file);
    });


    $key_src = array_values($md5_src);
    $key_deal = array_values($md5_deal);

    $diff = getArrDiff($key_src, $key_deal);

    $ret = array();
    if(!empty($diff['diff1'])){
        array_walk($diff['diff1'], function($v) use($md5_src, &$ret){
            $ret['missed'][] = array_search($v, $md5_src);
        });
    }
    if(!empty($diff['diff2'])){
        array_walk($diff['diff2'], function($v) use($md5_deal, &$ret){
            $ret['added'][] = array_search($v, $md5_deal);
        });
    }
    
    return $ret;
}

/**
 * 获取2个数组值不同的地方
 * @param  array $arr1 
 * @param  array $arr2 
 * @return array diff1 => array(arr2比arr1少的数据) diff2 => array(arr1比arr2少的部分)
 */
function getArrDiff($arr1, $arr2){
    $ret = array();

    $commonValues = array_intersect($arr1, $arr2);
    $commonValuesCount = count($commonValues);
    // 如果有交集的表的数量和arr1数量不同，那么获取arr2比arr1缺少的部分。
    if($commonValuesCount !== count($arr1)){
        $ret['diff1'] = array_diff($arr1, $commonValues);
    }
    // 如果有交集的表的数量和arr2数量不同，那么获取arr1比arr2缺少的部分。
    if($commonValuesCount !== count($arr2)){
        $ret['diff2'] = array_diff($arr2, $commonValues);
    }

    return $ret;
}


function globdir($dir, $filter = '*', $patten = GLOB_BRACE, $nocache = null) {
    static $file_arr = array ();
    isset($nocache) && $file_arr = array ();
    if (!is_dir($dir)) return;
    $a = glob("{$dir}/{$filter}", $patten);
    array_walk($a, function ($file) use(&$file_arr, $patten, $filter) {
        if ($patten == GLOB_ONLYDIR) {
            $file_arr[] = $file;
            globdir($file, '*', GLOB_ONLYDIR);
        } else {
            is_file($file) ? $file_arr[] = $file : globdir($file, $filter, $patten);
        }
    });
    if ($filter != '*') {
        $b = glob("{$dir}/*", GLOB_ONLYDIR);
        array_walk($b, function ($dir) use($filter, $patten) {
            globdir($dir, $filter, $patten);
        });
    }
    return $file_arr;
}
