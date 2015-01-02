<?php
namespace util;

class webStomp {

  private $uri = [];
  private $fp = '';

  # -- public --

  public function __construct($uri,$login,$passcode){
    $this->uri = parse_url($uri);
    $this->fp = fsockopen($this->uri['host'],'80');
    $this->handshake();
    $this->connect($login,$passcode);
  }

  public function __desctruct(){
    $this->disconnect();
  }

  public function send($destination,$text){
    $len = strlen($text);
    $send = "\x0ASEND\x0Acontent-type:text/plain\x0Adestination:$destination\x0Acontent-length:".($len+2)."\x0A\x0A{$text}\x0A\x0A\x00\x0A";
    $this->write($send);
  }

  # -- private --

  private function handshake(){
    $head = "GET {$this->uri['path']} HTTP/1.1\r\n".
    "Host: im.build.omen.ru\r\n".
    "Upgrade: websocket\r\n".
    "Connection: Upgrade\r\n".
    "Sec-WebSocket-Key: ".base64_encode(sha1(microtime(1).rand(1,1000000)))."\r\n".
    "Origin: im.build.omen.ru\r\n".
    "Sec-WebSocket-Version: 13\r\n".
    "\r\n";
    fwrite($this->fp,$head);
    fread($this->fp,2000);
  }

  private function connect($login,$passcode){
    $stomp = "CONNECT\x0Aaccept-version:1.1,1.0\x0Aheart-beat:0,0\x0Ahost:/\x0Alogin:$login\x0Apasscode:$passcode\x0A\x0A\x00\x0A";
    $this->write($stomp);
    fread($this->fp,2000);
  }

  private function disconnect(){
    $send = "\x0ADISCONNECT\x0A\x0A\x00\x0A";
    $this->write($send);
    fread($this->fp,2000);
    fclose($this->fp);
  }

  private function write($cmd){
    fwrite($this->fp,$this->encode($cmd,'text',true));
  }

  private function encode($payload, $type = 'text', $masked = true){
		$frameHead = array();
		$frame = '';
		$payloadLength = strlen($payload);
		
		switch($type) {		
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
		
		// set mask and payload length (using 1, 3 or 9 bytes) 
		if($payloadLength > 65535) {
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 255 : 127;
			for($i = 0; $i < 8; $i++) {
				$frameHead[$i+2] = bindec($payloadLengthBin[$i]);
			}
			// most significant bit MUST be 0 (close connection if frame too big)
			if($frameHead[2] > 127) {
				$this->close(1004);
				return false;
			}
		}
		elseif($payloadLength > 125) {
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		}
		else {
			$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
		}
		// convert frame-head to string:
		foreach(array_keys($frameHead) as $i) {
			$frameHead[$i] = chr($frameHead[$i]);
		}
		if($masked === true) {
			// generate a random mask:
			$mask = array();
			for($i = 0; $i < 4; $i++) {
				$mask[$i] = chr(rand(0, 255));
			}
			
			$frameHead = array_merge($frameHead, $mask);			
		}						
		$frame = implode('', $frameHead);
		// append payload to frame:
		$framePayload = array();	
		for($i = 0; $i < $payloadLength; $i++) {		
			$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}
		return $frame;
	}

}
?>
