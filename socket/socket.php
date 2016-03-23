<?php 
$ws = new WS('127.0.0.1', '8000');
Class WS {
    var $master;  // 连接 server 的 client
    var $sockets = array(); // 不同状态的 socket 管理
    var $handshake = false; // 判断是否握手


    function woshou($buffer){
		$buf  = substr($buffer, strpos($buffer, 'Sec-WebSocket-Key:')+18);
		$key  = trim(substr($buf, 0, strpos($buf,"\r\n")));

		$new_key = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
		
		$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
		$new_message .= "Upgrade: websocket\r\n";
		$new_message .= "Connection: Upgrade\r\n";
		$new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
		$new_message .= "Sec-WebSocket-Protocol: chat";
		
		// socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
		// $this->users[$k]['shou']=true;
		return $new_message;
		
	}

	function dohandshake($socket, $req){
	    // 获取加密key
	    $acceptKey = $this->woshou($req);

	    // 写入socket
	    socket_write($socket, $acceptKey, strlen($acceptKey));
	    // 标记握手已经成功，下次接受数据采用数据帧格式
	    $this->handshake = true;
	}

	// 解析数据帧
	function decode($buffer)  {
	    $len = $masks = $data = $decoded = null;
	    $len = ord($buffer[1]) & 127;

	    if ($len === 126)  {
	        $masks = substr($buffer, 4, 4);
	        $data = substr($buffer, 8);
	    } else if ($len === 127)  {
	        $masks = substr($buffer, 10, 4);
	        $data = substr($buffer, 14);
	    } else  {
	        $masks = substr($buffer, 2, 4);
	        $data = substr($buffer, 6);
	    }
	    for ($index = 0; $index < strlen($data); $index++) {
	        $decoded .= $data[$index] ^ $masks[$index % 4];
	    }
	    return $decoded;
	}

	function _encode($payload, $type = 'text'){
        $frameHead = array();
        $payloadLength = strlen($payload);
        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;
            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;
            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;
            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }
        if ($payloadLength > 65535) {
            $ext = pack('NN', 0, $payloadLength);
            $secondByte = 127;
        } elseif ($payloadLength > 125) {
            $ext = pack('n', $payloadLength);
            $secondByte = 126;
        } else {
            $ext = '';
            $secondByte = $payloadLength;
        }
        return $data  = chr($frameHead[0]) . chr($secondByte) . $ext . $payload;
    }

	// 返回帧信息处理
	function frame($s) {
	    $a = str_split($s, 125);
	    if (count($a) == 1) {
	        return "\x81" . chr(strlen($a[0])) . $a[0];
	    }
	    $ns = "";
	    foreach ($a as $o) {
	        $ns .= "\x81" . chr(strlen($o)) . $o;
	    }
	    return $ns;
	}

	// 返回数据
	function send($client, $msg){
	    // $msg = $this->frame($msg);
	    $msg = $this->_encode($msg);
	    // var_dump($msg);
	    return socket_write($client, $msg, strlen($msg)) !== false;
	}

    function __construct($address, $port){
        // 建立一个 socket 套接字
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)  
            or die("socket_create() failed");
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) 
            or die("socket_option() failed");
        socket_bind($this->master, $address, $port)                   
            or die("socket_bind() failed");
        socket_listen($this->master, 2)                              
            or die("socket_listen() failed");

        $this->sockets[] = $this->master;

        // debug
        echo("Master socket  : ".$this->master."\n");

        while(true) {
            //自动选择来消息的 socket 如果是握手 自动选择主机
            $write = NULL;
            $except = NULL;
            socket_select($this->sockets, $write, $except, NULL);

            foreach ($this->sockets as $socket) {
                //连接主机的 client
                if ($socket == $this->master){
                    $client = socket_accept($this->master);
                    if ($client < 0) {
                        // debug
                        echo "socket_accept() failed";
                        continue;
                    } else {
                        //connect($client);
                        array_push($this->sockets, $client);
                        echo "connect client\n";
                    }
                } else {
                	$buffer = '';
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);
                    if($bytes == 0) return;
                    if (!$this->handshake) {
                        // 如果没有握手，先握手回应
                        $this->doHandShake($socket, $buffer);
                        echo "shakeHands\n";
                    } else {
                        // 如果已经握手，直接接受数据，并处理
                        // echo date('Y/m/d H:i:s') . ":send data\n";
                        $buffer = $this->decode($buffer);
                        $this->send($socket, 'ok');//???
                        //process($socket, $buffer);
                        // echo "send file\n";
                        // ($this->send($socket, 'ok')) or print('error');
                        // echo $buffer;
                    }
                }
            }
        }

    }

}


function woshou($buffer){
	$buf  = substr($buffer, strpos($buffer, 'Sec-WebSocket-Key:')+18);
	$key  = trim(substr($buf, 0, strpos($buf,"\r\n")));

	$new_key = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
	
	$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
	$new_message .= "Upgrade: websocket\r\n";
	$new_message .= "Connection: Upgrade\r\n";
	$new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
	$new_message .= "Sec-WebSocket-Protocol: chat";
	
	// socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
	// $this->users[$k]['shou']=true;
	return $new_message;
	
}

function strToBin($str){
	$arr = preg_split('/(?<!^)(?!$)/u', $str);
	// $arr = preg_split('//u', $str,-1, PREG_SPLIT_NO_EMPTY);

	//2.unpack字符
	foreach($arr as &$v){
		$temp = unpack('H*', $v); 
		$v = base_convert($temp[1], 16, 2);
		unset($temp);
	}

	return join(' ',$arr);
}

