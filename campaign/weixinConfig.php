<?php
  error_reporting(0);
  include_once '../lib/mysql.class.php';
  $db_host = $databases['default']['default']['host'];
  $db_data = $databases['default']['default']['database'];
  $db_user = $databases['default']['default']['username'];
  $db_pass = $databases['default']['default']['password'];
  $db_prefix = $databases['default']['default']['prefix'];
  $db = new MySQL($db_host,$db_user,$db_pass,$db_data);
  
  //处理appid 公众号
  $appid = 'wx9de7849179fa4533';
  //生产环境
  if(strpos($_SERVER['HTTP_HOST'],'cigna') > 0 && $_SERVER['HTTP_HOST'] != 'test.cignacmb.com'){ $appid = 'wx8d188615510c9093'; }

  $jssdk = new JSSDK($appid);
  $signPackage = $jssdk->GetSignPackage();
  
  $jsApiList = " 'checkJsApi','onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','hideMenuItems','showMenuItems','hideAllNonBaseMenuItem','showAllNonBaseMenuItem','translateVoice','startRecord','stopRecord'";
  $share = "wx.onMenuShareAppMessage(wxData);  wx.onMenuShareTimeline(wxData);  wx.onMenuShareQQ(wxData);  wx.onMenuShareWeibo(wxData);";
  
  // $func = '';
  // if($_POST['func']){ $func = $_POST['func']; }

  // $debug = $_POST['debug' ]? $_POST['debug'] : 'false';
  // echo "wx.config({debug: ".$debug.", appId: '".$signPackage[appId]."',timestamp:'".$signPackage[timestamp]."',nonceStr: '".$signPackage[nonceStr]."', signature: '".$signPackage[signature]."',jsApiList: [".$jsApiList."]});	wx.ready(function () {".$share.$func." });";
    echo "wx.config({debug: false, appId: '".$signPackage['appId']."',timestamp:'".$signPackage['timestamp']."',nonceStr: '".$signPackage['nonceStr']."', signature: '".$signPackage['signature']."',jsApiList: [".$jsApiList."]});  wx.ready(function () {".$share." });";
  exit();

 	
class JSSDK
{
  private $accountId;
  private $appId;
  private $Ticketurl;

  public function __construct($appId)
  {	
	$this->appId = $appId;
	
	if(strpos($_SERVER['HTTP_HOST'],'cigna') > 0 && $_SERVER['HTTP_HOST'] != 'test.cignacmb.com')
	{		
	  $this->accountId = 'gh_6c17146316ec';
	  $this->Ticketurl = 'http://10.140.5.133/wxweb/getJsApiTicket.xhtml' . '?accountId='.$this->accountId;
	}else
	{
	  $this->accountId='gh_5c67caf6c386';
	  $this->Ticketurl = 'http://10.140.5.166:8080/wxweb/getJsApiTicket.xhtml' . '?accountId=' . $this->accountId;
	}
  }

  public function getSignPackage()
  {	
	$Ticket = $this->getJsApiTicket();
	if(!$Ticket){ return false;  	}
	$jsapiTicket = $Ticket['ticket'];
	$timestamp = $Ticket['expire_time'];
	$url = $_SERVER['HTTP_REFERER'];
	
    $nonceStr = mt_rand (10000, 99999);  
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature = sha1($string);
    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string,
	  "accountId" =>$this->accountId
    );
    return $signPackage; 
  }

  private function getJsApiTicket()
  {
	global  $db;	 
	$sql =  "SELECT * FROM tb_weixin_ticket where accountID='".$this->accountId."'";	
	$expiration = $db->getRow($sql);	
	if($expiration['expire_time']<time())
	{
	  $jsapi = $this->httpGet($this->Ticketurl);
	  $jsapiTicketArr = explode('#',$jsapi);			
	  $ticket['expire_time'] = strtotime($jsapiTicketArr[1])+3600;			
	  $ticket['ticket'] = $jsapiTicketArr[0];
	  if($expiration['expire_time'] < 10 )
	  {
		$uisql = "insert into tb_weixin_ticket(accountID,ticket,expire_time) values('".$this->accountId."','".$ticket['ticket']."','".$ticket['expire_time']."')";
	  }else
	  {
		$uisql = "update tb_weixin_ticket set ticket='".$ticket['ticket']."',expire_time='".$ticket['expire_time']."' where accountID='".$this->accountId."'";	
	  }
	  $r=$db->queryOpr($uisql);
	  
	}else{  $ticket=$expiration;}
	
	return $ticket;
  }


  private function httpGet($url)
  {	
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_URL, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
  }
  
}
