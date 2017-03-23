<?php
date_default_timezone_set('Asia/Shanghai');
include_once 'function/common.php';
include_once 'class/file.php';
define('DATAFILE', 'cache/data.php');
define('SAVEPATH', 'cache/session'); // session存放目录
define('MAXNUM', 6); // 缓存文件记录数
define('NAMELEN', 3); // 游客名称长度
!is_dir(SAVEPATH) && mkdir(SAVEPATH);
session_save_path(SAVEPATH);
session_start();

$act = getval('act');

$file = new file(DATAFILE);

if ($act == 'newmsg') {
    $msg = getval('msg');
    $uname = getval('uname');
    $t = time();
    $dataarr = array ();
    $newmsg = array ('msg' => $msg, 'uname' => $uname);
    if (file_exists(DATAFILE)) {
        $dataarr = $file->read(1);
        if (count($dataarr) >= MAXNUM) {
            $key = array_keys($dataarr);
            $key = $key[0] == 'offlinelist' ? ($key[1] == 'newuser' ? $key[2] : $key[1]) : $key[0];
            unset($dataarr[$key]);
        }
    }
    // 同一秒有多条信息
    if (isset($dataarr[$t])) {
        if (is_assoc($dataarr[$t])) {
            $dataarr[$t] = array_chunk($dataarr[$t], 2, true);
        }
        $dataarr[$t][] = $newmsg;
    } else {
        $dataarr[$t] = $newmsg;
    }
    
    $file->mk_file($dataarr);
    echo date('H:i:s', $t);
    exit();
} elseif ($act == 'getmsg') {
    /**
	 * 获取实时信息，告知客户端脚本有离线用户则删除离线用户，有新用户则刷新列表
	 */
    $rqtime = $_SERVER['REQUEST_TIME'];
    $dataarr = $file->read(1);
    if (!isset($_SESSION['lasttime'])) {
        $_SESSION['lasttime'] = $rqtime - 1;
    } else {
        if ($rqtime - $_SESSION['lasttime'] != 1) {
            $rqtime = ++$_SESSION['lasttime'];
        } else {
            $_SESSION['lasttime'] = $rqtime;
        }
    }
    
    if (isset($dataarr[$rqtime])) {
        $rsarr = $dataarr[$rqtime];
        $rqtime = date('H:i:s', $rqtime);
        if (is_assoc($rsarr)) $rsarr = array_chunk($rsarr, 2, true);
        $rsarr = array ($rqtime => $rsarr);
    }
    if (isset($dataarr['offlinelist']) && count($dataarr['offlinelist']) > 0) {
        $rsarr['offlinelist'] = $dataarr['offlinelist'];
        $dataarr['offlinelist'] = array ();
        $file->mk_file($dataarr);
    }
    if (isset($dataarr['newuser']) && $dataarr['newuser'] == 1) {
        $rsarr['newuser'] = 1;
        unset($dataarr['newuser']);
        $file->mk_file($dataarr);
    }
    echo json_encode($rsarr);
    exit();

} elseif ($act == 'setuname') {
    $rndname = !empty($_GET['uname']) ? $_GET['uname'] : '游客' . substr(microtime(1), -3 - NAMELEN, NAMELEN);
    $_SESSION['uname'] = $rndname;
    
    $dataarr = $file->read(true);
    $dataarr['newuser'] = 1;
    $file->mk_file($dataarr);
    echo json_encode($_SESSION['uname']);
    exit();
} elseif ($act == 'getlist') {
    session_write_close(); //否则没法读取session文件
    $filearr = glob(SAVEPATH . DIRECTORY_SEPARATOR . '*');
    $list = array ();
    foreach ( $filearr as $file ) {
        if (file_exists($file)) {
            $c = file_get_contents($file);
            preg_match('/uname\S*"(.*)"/U', $c, $m);
            isset($m[1]) ? $list[] = $m[1] : '';
        }
    }
    echo json_encode($list);
    exit();

} elseif ($act == 'logout') {
    ignore_user_abort(true);
    set_time_limit(0);
    $dataarr = $file->read(true);
    $dataarr['offlinelist'][] = $_SESSION['uname'];
    $file->mk_file($dataarr);
    
    session_unset(); // 内存中的session变量
    session_destroy(); // session文件
} elseif ($act == 'login') {

}

?>