<?php 
error_reporting(0);
require_once 'excel/excel_class.php';
Read_Excel_File('1.xls', $return);

echo '<pre>';
unset($return['VjiaCPS_1']);
print_r($return);
?>