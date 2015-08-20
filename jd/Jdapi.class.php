<?php
/**
 * 京东api
 *
 */
class Jdapi {
    private $appkey;
    private $appsecret;
    private $sys_para = array (); // 系统级输入参数,每次调用方法后其值不变
    private $sys_para2 = array (); // 系统级输入参数2，每次调用方法后其值清空
    private $app_param = array (); // 应用级输入参数,每次调用方法后其值清空
    const API_URL = 'http://gw.api.360buy.com/routerjson?'; //正式环境地址
    

    function __construct($appkey, $secret, $token = null) {
        $this->appkey = $appkey;
        $this->appsecret = $secret;
        $this->sys_para[] = 'timestamp=' . date('Y-m-d H:i:s', time());
        isset($token) && $this->sys_para[] = "access_token={$token}";
        $this->sys_para[] = "app_key={$appkey}";
        $this->sys_para[] = 'v=2.0';
    }
    
    function &get_param() {
        return $this->app_param;
    }
    
    private function sign_arr() {
        return array_merge($this->sys_para, $this->sys_para2);
    }
    
    /**
	 * 获取接口数据
	 *
	 * @return array $c
	 */
    function getData() {
        $this->sys_para2[] = "method={$this->app_param['method']}";
        unset($this->app_param['method']);
        if (!empty($this->app_param)) {
            ksort($this->app_param);
            $this->sys_para2[] = '360buy_param_json=' . json_encode($this->app_param);
        }
        $this->sys_para2[] = 'sign=' . $this->sign($this->sign_arr());
        
        /* $para = $this->sign_arr();
        sort($para);
        $para = implode('&', $para); */
        $c = $this->curl_send(self::API_URL, implode('&', $this->sign_arr()));
        $c = json_decode($c, true);
        $this->sys_para2 = array ();
        $this->app_param = array ();
        
        return $c;
    }
    
    /**
	 * 生成签名
	 *
	 * @param array $para
	 */
    private function sign($para) {
        $str = '';
        sort($para);
        $para = str_replace('=', '', $para);
        $str = implode('', $para);
        $str = strtoupper(md5($this->appsecret . $str . $this->appsecret));
        return $str;
    }
    
    /**
     * curl提交查询,如果有$data数组表示是post请求
     * @param string $url
     * @param array/string $data 键值关联数组/已拼接字符串
     * @return
     */
    private function curl_send($url, $data = null) {
        $ch = curl_init($url);
        //echo $url,'<br>';
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (isset($data)) {
            $data = is_array($data) ? http_build_query($data) : $data;
            echo $url, $data, '<br>';
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

}