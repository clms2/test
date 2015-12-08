<?php
class CMB
{
    public static function getBindInfo($openid)
    {
        $ar = array('merchantId'=>'zsxn',
                    'token'=>'5d4f58c54g68a5ad',
                    'timestamp'=>time(),
                    'nonce'=>mt_rand(10000,99999)      );
        ksort($ar); //按字典排序
        $uri = '';
        $uri = http_build_query($ar);
        // foreach($ar as $k=>$v){	$uri .= '&' . $k . '=' . $v; }
        // if(!empty($uri)){ $uri = substr($uri,1); }
        $signature = md5($uri);
        $ar['signature'] = $signature;
        $ar['openId'] = $openid;
        
        $urlIsBind = CHECKBIND;
        $c = self::CURL($urlIsBind, $ar);
        $c = simplexml_load_string($c);  
        return json_decode(json_encode($c),1);
    }
    
    private static function CURL($url, $data = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);        
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        
        if (isset($data))
        {
            $data = is_array($data) ? http_build_query($data) : $data;
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT,25);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }
    
    /**
     *仅适用于cmb的从url中提取openid的方法
     */
    public static function getOpenId($tuid,$datetime)
    {
        $str_encrypt=base64_decode($tuid);
        $key = "cmbedcba"; //密钥
        $cipher = MCRYPT_DES; //密码类型
        $modes = MCRYPT_MODE_ECB; //密码模式
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher,$modes),MCRYPT_RAND);//初始化向量
        $str_decrypt = mcrypt_decrypt($cipher,$key,$str_encrypt,$modes,$iv); //解密函数	
        $str_decrypt=urldecode($str_decrypt);
        $openId=str_replace('_'.$datetime,'',$str_decrypt);
        return  substr($openId,0,-5);
    }
}
?>
