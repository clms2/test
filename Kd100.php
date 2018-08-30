<?php 
/**
* 快递100api
*/
class Kd100{

	// type:物流公司代码 sn:运单号 rand:15位随机数
	private $api_url = 'http://www.kuaidi100.com/query?type={com}&postid={sn}&id=1&valicode=&temp=0.{rand}';
	// company_code 获取地址 sn:运单号
	private $api_type_url = 'http://www.kuaidi100.com/autonumber/autoComNum?resultv2=1&text={sn}';
	// 当接口返回以下状态时则不再继续调用接口
	private $end_status = array(3, 4);
	// curl请求option
	private $curlopt = array(
		// 模拟的官网请求头
		CURLOPT_HTTPHEADER => array(
			'Accept: */*',
	        'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
	        'Connection: keep-alive',
	        'X-Requested-With: XMLHttpRequest',
	        'Host: www.kuaidi100.com',
	        'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0'
		),
		// 超过5秒就不请求了 浪费时间
		CURLOPT_TIMEOUT => 5,

	);
	
	function __construct($param = array()){
		if (!empty($param['end_status']) && is_array($param['end_status'])) {
			$this->end_status = $param['end_status'];
		}

	}

	function http_request($url, $data = null, $options = null){

		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			if(isset($data)){
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
			}
			if(isset($options)){
				curl_setopt_array($ch, $options);
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($ch);
			if(curl_errno($ch)){
				$result = curl_error($ch);
			}
			curl_close($ch);

			return $result;
		}

		if (function_exists('file_get_contents')) {
			if (isset($data)) {
				$url .= '?' . (is_array($data) ? http_build_query($data) : $data);
			}

			return file_get_contents($url);
		}

		throw new \Exception('curl & file_get_contents not exists');
	}

	/**
	 * 根据运单号获取company_code 一般是快递公司的拼音
	 * @param  string $sn 
	 * @return string     
	 */
	private function getCompanyCodeBySn($sn){
		$url = $this->api_type_url;
		$url = strtr($url, array(
			'{sn}' => $sn,
		));

		$ret = $res = $this->http_request($url, null, $this->curlopt);
		$ret = json_decode($ret, true);

		if (!is_array($ret) || !isset($ret['auto'][0]['comCode'])) {
			throw new \Exception('获取company code失败 res:' . $res);
		}
		$company_code = $ret['auto'][0]['comCode'];

		return $company_code;
	}

	/**
	 * 获取物流信息
	 * @param  string $sn           运单号
	 * @param  string $company_code 物流公司代码 建议传 会少个请求
	 * @return array               
	 */
	function trace($sn, $company_code = ''){
		if (empty($$company_code)) {
			$company_code = $this->getCompanyCodeBySn($sn);
		}

		$url = $this->api_url;
		$url = strtr($url, array(
			'{com}'  => $company_code,
			'{sn}'   => $sn,
			'{rand}' => mt_rand(10000, 99999) . mt_rand(10000, 99999) . mt_rand(10000, 99999),
		));

		$ret = $this->http_request($url, null, $this->curlopt);
		$ret = json_decode($ret, 1);

		if(!is_array($ret)){
			throw new \Exception('快递100api不能用了～');
		}

		return $ret;
	}
}

$kd = new Kd100();
$data = $kd->trace('3920841725569', 'yunda');
echo '<pre>';
print_r($data);