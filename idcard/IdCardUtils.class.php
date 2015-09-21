<?php
//php验证身份证合法性
// 计算身份证校验码，根据国家标准GB 11643-1999
class IdcardUtils{
	static function idcard_verify_number($idcard_base){
		if (strlen($idcard_base) != 17){ return false; }
		// 加权因子
		$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

		// 校验码对应值
		$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

		$checksum = 0;
		for ($i = 0; $i < strlen($idcard_base); $i++){
			$checksum += substr($idcard_base, $i, 1) * $factor[$i];
		}

		$mod = strtoupper($checksum % 11);
		$verify_number = $verify_number_list[$mod];

		return $verify_number;
	}

	// 将15位身份证升级到18位
	function idcard_15to18($idcard){
		if (strlen($idcard) != 15){
			return false;
		}else{
			// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
			if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
				$idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
			}else{
				$idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
			}
		}
		$idcard = $idcard . idcard_verify_number($idcard);
		return $idcard;
	}

	//18位身份证校验码有效性检查
	function idcard_checksum18($idcard){
		if (strlen($idcard) != 18){ return false; }
		$aCity = array(11 => "北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",
		21=>"辽宁",22=>"吉林",23=>"黑龙江",
		31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",
		41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",
		50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",
		61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",
		71=>"台湾",81=>"香港",82=>"澳门",
		91=>"国外");
		//非法地区
		if (!array_key_exists(substr($idcard,0,2),$aCity)) {
			return false;
		}
		//验证生日
		if (!checkdate(substr($idcard,10,2),substr($idcard,12,2),substr($idcard,6,4))) {
			return false;
		}
		$idcard_base = substr($idcard, 0, 17);
		if (self::idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
			return false;
		}else{
			return true;
		}
	}
	
	function idMapAreaCode($idCard = ''){
		$cityMap = array("61" => "RES_DEPT_SAXA0","11"=>"RES_DEPT_BJBJ0","31"=>"RES_DEPT_SHSH0","44"=>"RES_DEPT_GDGZ0","33"=>"RES_DEPT_ZJHZ0",
				"37"=>"RES_DEPT_SDJN0","21"=>"RES_DEPT_LNSY0","42"=>"RES_DEPT_HBWH0","32"=>"RES_DEPT_JSNJ0","51"=>"RES_DEPT_SCCD0",
				"4403"=>"RES_DEPT_SZSZ0","3702"=>"RES_DEPT_QDQD0","other"=>"RES_DEPT_SDYT0");
		$fourNumber=substr($idCard,0,4);
		$twoNumber=substr($idCard,0,2);
		if(array_key_exists(substr($idCard,0,4),$cityMap)){
			return $cityMap[substr($idCard,0,4)];
		}elseif (array_key_exists(substr($idCard,0,2),$cityMap)){
			return $cityMap[substr($idCard,0,2)];
		}else {
			return $cityMap["other"];
		}
	}
		
}
