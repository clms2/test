<?php 
/**
 * lishi1.html和lishi2.html
 */
require 'inc/check.php';
require 'inc/conn.php';
require 'inc/func.php';

$my = $db->getOneAssoc('info', "uid={$_SESSION['uid']} and baoe != 0",'total,searchnum,foundnum,follow_id,moqi');
if(empty($my)){
    header('location:auth.php');
    exit;
}

// 分享给我的人的好友
if(!empty($my['follow_id'])){
  $shares = $db->getOneAssoc('info', "uid={$my['follow_id']} and baoe!=0",'uid,uname,total,searchnum,foundnum,moqi');
  if(!empty($shares)){
      // 只显示前3，不加limit 3因为需要获取我在他的好友里的排名~不知道有没其他方法
      $shares_follow = $db->getAssoc('info', "follow_id={$shares['uid']} order by moqi desc,total desc,id desc",'uid,uname,total,searchnum,foundnum,moqi');
    }
}
$class = empty($shares) ? 'lishi1' : 'lishi2';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" /> 
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no"/>
<title>70万神秘海底宝藏</title>
<link rel="stylesheet" href="css/css.css"/>
<link rel="stylesheet" href="css/animation.css"/>
<script type="text/javascript">
    var tag = '<?php echo $class ?>';
</script>
</head>
<body>
<div class="lishi">
	<img src="images/jijiehao.png" class="jijiehao">
	<div class="shuoming"></div>
    <div class="<?php echo $class ?>">
        <img src="images/<?php echo $class ?>.png">
        <p>
        <span class="faqi-title">您已成功领取<?php echo $my['total'] ?>万</span><br>
        <span class="font-27">海陆空意外保障</span><br>
        <span class="qixian">保障期限：2015年12月1日至2015年12月31日</span><br>
        <?php if(empty($shares)): ?>
        <span class="faqi-text"><img src="images/lishi1-text.png" style="width:100%"></span>
        <?php endif; ?>
        </p>
        <?php if(!empty($shares)): ?>
        <div class="list-box">
        	<h1><strong><?php echo $_SESSION['uname'] ?></strong> 的藏宝计划之搜查达人榜</h1>
            <div class="list">
                <table width="100%" border="0">
                  <tr>
                    <th width="20%">排名</th>
                    <th width="30%">昵称</th>
                    <th width="30%">次数/保额</th>
                    <th width="20%">默契度</th>
                  </tr>
                </table>
                <div>
                <table width="100%" border="0" style="margin:0; padding:0">
                <?php if(!empty($shares_follow)):$i = 0;foreach ($shares_follow as $follow): 
                    $p20 = $p30 = '';
                    if(++$i == 1){
                      $p20 = ' width="20%"';
                      $p30 = ' width="30%"';
                    }
                    // 我的排名
                    if($follow['uid'] == $_SESSION['uid']){
                      $myrank = $i;
                    }
                    if($i <= 3):
                ?>
                  <tr>
                    <td<?php echo $p20 ?>><img src="images/<?php echo $i ?>.png"></td>
                    <td<?php echo $p30 ?>><?php echo $follow['uname'] ?></td>
                    <td<?php echo $p30 ?>><?php echo $follow['searchnum'] ?>次/<?php echo $follow['total'] ?>万</td>
                    <td<?php echo $p20 ?>><?php echo $follow['moqi'] ?>%</td>
                  </tr>
                <?php endif;endforeach; else:?>
                <tr><td colspan="4">他还没有好友参与哦~</td></tr>
                <?php endif; ?>
                </table>
                </div>
            </div>
            <h2>我的排名<strong><?php echo $myrank ?></strong>  <strong><?php echo $my['searchnum'] ?></strong>次<strong><?php echo $my['total'] ?></strong>万  默契度<strong><?php echo $my['moqi'] ?>%</strong></h2>
        </div>
        <?php endif; ?>
        <div class="faqi"></div>
    </div>
</div>
<div class="fix"></div>

<!--规则介绍-->
<?php include 'rule.html'; ?>
<!--规则介绍-->

<!--设置藏保-->
<?php include 'setplace.html' ?>
<!--设置藏保-->

<!--分享-->
<?php include 'share.html' ?>
<!--分享-->

<script src="js/jquery-1.11.1.min.js"></script> 
<script src="/js/jweixin-1.0.0.js"></script>
<script src="js/women.js?v=110"></script> 

</body>
</html>
