<?php 
$title = array('手机号'=>array('field'=>'tel','islong'=>1), '归属地'=>array('field'=>'area'));
arr2csv($title, $data);

/**
* 通用导出csv
* @param  array $title key:excel表头字段，field:对应的数据库字段,islong:防止对长整数进行科学计数，func:对该字段值进行回调,自定义
* @param  array $arr   数据库记录，格式为array(0=>array('field'=>'value'));
*/
function arr2csv($title, $arr, $filename = ''){
    if (empty($filename)) $filename = date('Ymd');
    header('Content-Type: application/vnd.ms-excel; charset=GB2312'); 
    header('Pragma: public'); 
    header('Expires: 0'); 
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
    header('Content-Type: application/force-download'); 
    header('Content-Type: application/octet-stream'); 
    header('Content-Type: application/download'); 
    header("Content-Disposition: attachment;filename={$filename}.csv"); 
    header('Content-Transfer-Encoding: binary');

    echo iconv('utf-8', 'gbk', implode(',', array_keys($title))),"\r\n";//表头
    $total = count($title);
    foreach ($arr as $a) {
        $i = 0;
        foreach ($title as $t) {
            // 查询的时候没取出该字段 或字段对应错误
            if(!isset($a[$t['field']])){
                echo 'error',',';
                continue;
            }
            $v = $a[$t['field']];
            // $v = str_replace('"', '""', $v);
            $v = addslashes($v);
            if(strpos($v, ',') !== false){
                $v = '"' . $v . '"';
            }
            if(isset($t['islong'])) echo "\t";// \t不进行科学计数
            echo iconv('utf-8', 'gbk', isset($t['func']) ? $t['func']($v) : $v);
            echo ++$i == $total ? "\r\n" : ',';
        }
    }
}
