<?php 

$socket = null;
$conn = null;

$socket = stream_socket_server('tcp://127.0.0.1:8000', $errno, $errstr);
if (!$socket){
	echo "$errstr ($errno)<br />\n";
}
while (true){

	// creating the socket...
	// if(!$socket)
	
	echo 'in';
	// while there is connection, i'll receive it... if I didn't receive a message within $nbSecondsIdle seconds, the following function will stop.
	if(!$conn){
		while ($conn  = stream_socket_accept($socket, -1)){
			$message= fread($conn, 4096);


			if(strpos($message, 'Sec-WebSocket-Ke') !== false){
				fputs($conn, woshou($message));
				echo 1;
			}else{
				echo 2;
				fputs($conn, $message);
			}
			
			fclose ($conn);
		}
	}else{
		$conn  = stream_socket_accept($socket, -1);
		$message= fread($conn, 4096);
		echo 'conn';
	}
	
	fclose($socket);

}


function woshou($buffer){
	$buf  = substr($buffer, strpos($buffer, 'Sec-WebSocket-Key:')+18);
	$key  = trim(substr($buf, 0, strpos($buf,"\r\n")));

	$new_key = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
	
	$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
	$new_message .= "Upgrade: websocket\r\n";
	$new_message .= "Connection: Upgrade\r\n";
	$new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
	
	// socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
	// $this->users[$k]['shou']=true;
	return $new_message;
	
}

