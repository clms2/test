<?php
//授权登陆类 需要先定义常量
class Auth{
	/**
	 * 请求授权码地址
	 */
	public $authorUrl;
	/**
	 * 外部需定义的常量
	 * tokenurl:请求访问令牌地址
	 */
	public $tokenUrl, $appkey, $secret, $redirectUrl;

	private static $instance;
	private $param = array();

	private function __construct(){
		$vars = get_class_vars(__CLASS__);
		unset($vars['instance'], $vars['param']);
		$consts = get_defined_constants(true);
		$consts = $consts['user'];

		foreach ($vars as $k=>$v){
			if (isset($v)) continue;
			$tempk = strtoupper($k);
			$this->$k = isset($consts[$tempk]) ? $consts[$tempk] : 'test';
		}
		$this->param['client_id'] = $this->appkey;
		$this->param['client_secret'] = $this->secret;
		
	}

	static function getInstance(){
		if (!isset(self::$instance)){
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * 授权登陆跳转链接
	 * @return string
	 */
	function getLoginUrl($state = ''){
		return "{$this->authorUrl}response_type=code&client_id={$this->appkey}&redirect_uri={$this->redirectUrl}&state={$state}";
	}

	/**
	 * 获取访问令牌
	 * @param string $code 授权返回回来的get参数code
	 * @return array http://open.taobao.com/doc/detail.htm?spm=a219a.7386781.0.0.Ol2MzG&id=118
	 */
	function getToken($code){
		$this->param['grant_type'] = 'authorization_code';
		$this->param['code'] = $code;
		$this->param['redirect_uri'] = $this->redirectUrl;

		$result = $this->curl($this->tokenUrl, $this->param);
		return json_decode($result, 1);
	}

	/**
	 * 延长Access token的时长
	 * @param string $refresh_token 获取访问令牌时的refresh_token
	 * @return array
	 */
	function getRefresh($refresh_token){
		$this->param['grant_type'] = 'refresh_token';
		$this->param['refresh_token'] = $refresh_token;

		$result = $this->curl($this->tokenUrl, $this->param);
		return json_decode($result, 1);
	}

	function curl($url, $postFields){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		if (is_array($postFields) && 0 < count($postFields))
		{
			$postBodyString = "";
			$postMultipart = false;
			foreach ($postFields as $k => $v)
			{
				if("@" != substr($v, 0, 1))//判断是不是文件上传
				{
					$postBodyString .= "$k=" . urlencode($v) . "&"; 
				}
				else//文件上传用multipart/form-data，否则用www-form-urlencoded
				{
					$postMultipart = true;
				}
			}
			unset($k, $v);
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart)
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			}
			else
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
			}
		}
		$reponse = curl_exec($ch);
		
		if (curl_errno($ch))
		{
			throw new Exception(curl_error($ch),0);
		}
		else
		{
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode)
			{
				throw new Exception($reponse,$httpStatusCode);
			}
		}
		curl_close($ch);
		return $reponse;
	}
}