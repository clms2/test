<?php
$data = array (array (i('第1列'), i('第2列')), array ('a1', 'a2'), array ('b1', 'b2'));

Create_Excel_File('ex.xls', $data);

function i($v) {
	return iconv('utf-8', 'gbk', $v);
}

function Create_Excel_File($ExcelFile, $Data) {
	header('Content-type: application/x-msexcel');
	header("Content-Disposition: attachment; filename=$ExcelFile");
	
	function xlsBOF() {
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
	}
	function xlsEOF() {
		echo pack("ss", 0x0A, 0x00);
		return;
	}
	function xlsWriteNumber($Row, $Col, $Value) {
		echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		echo pack("d", $Value);
		return;
	}
	function xlsWriteLabel($Row, $Col, $Value) {
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		echo $Value;
		return;
	}
	
	xlsBOF();
	
	for($i = 0; $i < count($Data[0]); $i++) {
		for($j = 0; $j < count($Data); $j++) {
			$v = $Data[$j][$i];
			
			xlsWriteLabel($j, $i, $v);
		}
	}
	xlsEOF();
}