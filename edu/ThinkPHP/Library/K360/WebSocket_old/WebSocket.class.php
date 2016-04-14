<?php
namespace K360\WebSocket_old;
use Think\Controller;
/*
 * 用户发送消息体为：
 * 	加入消息：{type:'add', info:{id:xxx, name:xxx, ...}}，告诉服务器你的个人信息
 * 	普通消息：{type:'msg', msg:{xxx:xxx, xxx:xxx}}，消息内容自定义
 * 	退出消息：{type:'end'}
 * 	关闭服务器：{type:'off'}
 * */

class WebSocket_old extends Controller implements WebSocketInterface_old{
	private $conf = array(
		"app"	=>	"exmpAPP",
		"host"	=>	"localhost",
		"port"	=>	6310
	);
	
	private $master = null;			//本机socket
	
	private $sockets = array();		//存放socket
	
	private $users = array();		//[{sock:xxx, hand:true/false, info:{id:xxx, name:xxx}}, ... ...]
	
	public function __construct($app, $host, $port){
		parent::__construct();
		set_time_limit(0);
		error_reporting(E_ALL);
		ob_implicit_flush();
		//设置数据
		$this->conf["app"] = $app;
		$this->conf["host"] = $host;
		$this->conf["port"] = $port;
	}

	/**
	 * 启动服务器
	 */
	protected function run(){
		if(!$this->master = self::_create($this->conf["host"], $this->conf["port"])){
			return false;
		}
		self::_start();
		self::_close();
	}
	
	/**
	 * 向指定的用户发送消息
	 * @param unknown $uids			用户id
	 * @param unknown $str			要发送的消息
	 */
	public function send($uids, $str){
		$str = self::_code($str);
		foreach ($this->users as $user){
			$s = false;
			foreach ($uids as $v){
				if($user["info"] && $v == $user["info"]->id){
					$s = true;
					break;
				}
			}
			if(!$s) continue;
			$sock = $user["sock"];
			socket_write($sock, $str, strlen($str));
		}
	}
	
	/**
	 * 发送消息给某个人
	 * @param unknown $uid		用户id
	 * @param unknown $str		要发送的消息
	 */
	public function sendOne($uid, $str){
		self::send(array($uid), $str);
	}
	
	/**
	 * 向所有人发送消息
	 * @param unknown $str		消息内容
	 */
	public function sendAll($str){
		$ids = array();
		foreach ($this->users as $user){
			if(!isset($user["info"]->id)) continue;
			$ids[] = $user["info"]->id;
		}
		self::send($ids, $str);
	}
	
	/**
	 * 调用自定义方法
	 * @param unknown $data			向自定义方法发送的数据，如果发送的数据是：array("name"=>"xxxx", ...)，则在onCustom函数中应该用$data->name来获取
	 * @return boolean		发送成功返回true，否则返回false
	 */
	public function callCustom($data){
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!socket_connect($sock, $this->conf["host"], $this->conf["port"])){
			//连接失败
			socket_close($sock);
			return false;
		}
		//写入数据
		socket_write($sock, json_encode(array("type"=>"custom", "data"=>$data)));
		socket_close($sock);
		return true;
	}
	
	/**
	 * 关闭服务器，关闭完成后最好用isRuning方法检测以便
	 * @return boolean		关闭成功返回true，否则返回false
	 */
	public function shutdown(){
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!socket_connect($sock, $this->conf["host"], $this->conf["port"])){
			//连接失败
			socket_close($sock);
			return false;
		}
		//写入数据
		socket_write($sock, json_encode(array("type"=>"shutdown")));
		socket_close($sock);
		return true;
	}
	
	/**
	 * 判断服务器是否在运行
	 */
	public function isRuning(){
		$ret = true;
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!socket_connect($sock, $this->conf["host"], $this->conf["port"]))
			$ret = false;
		socket_close($sock);
		return $ret;
	}
	
	/**
	 * 自动检测并开启服务器
	 * @param unknown $runUrl		服务器开启方法所在的url
	 * @return	0）服务已经启动，1)服务正在开启中，2）服务开启成功，-1）服务开启失败
	 */
	public function autoCheck($runUrl){
		//先检测是否已经启动
		if(self::isRuning()){
			return 0;
		}
		//判断是否正在启动中
		$lock = self::_lock();
		if($lock=="T"){
			return 1;
		}
		//加锁
		self::_lock("T");
		//启动
		$ch = curl_init($runUrl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		curl_close($ch);
		//启动成
		sleep(2);
		if(self::isRuning()){
			self::_lock("F");
			return 2;
		}
		//启动失败
		self::_lock("F");
		return -1;
	}
	
	/**
	 * 锁的读写
	 * @param string $value			写入的值，如果不传则表示度取
	 * @return Ambigous <NULL, string>		度取的值，如果是写入则返回null
	 */
	private function _lock($value = null){
		$shmid = shmop_open(0xF8EA, "c", 0777, 1);
		$buffer = null;
		if($value){
			shmop_write($shmid, $value, 0);
		}else{
			$buffer = shmop_read($shmid, 0, 1);
		}
		shmop_close($shmid);
		return $buffer;
	}
	
	public function getUserNum(){
		return count($this->users);
	}
	
	/*
	 * 以下几个方法，请按需要覆盖
	 * */
	public function onConnect($user){
		//有用户连接进来
	}
	
	public function onMessage($user, $msg){
		//有用户发送了一条消息
	}
	
	public function onDisconnect($user){
		//有用户断开连接
	}
	
	public function onClose(){
		//服务器被关闭
	}
	
	public function onCustom($data){
		//用户自定义消息处理
	}
	
	/**
	 * 创建连接
	 * @param unknown $host			ip地址
	 * @param unknown $port			端口号
	 * @return boolean|resource		创建好的连接  或者 fasle
	 */
	private function _create($host, $port){
		//创建
		if(!$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)){
			self::_close();
			return false;
		}
		if(!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)){
			self::_close();
			return false;
		}
		//绑定
		if(!socket_bind($sock, $host, $port)){
			self::_close();
			return false;
		}
		//监听
		if(!socket_listen($sock)){
			self::_close();
			return false;
		}
		//将服务器socket存放起来
		$this->sockets[] = $sock;
		return $sock;
	}
	
	/**
	 * 开启消息轮询
	 */
	private function _start(){
		while (true){
			$changes = $this->sockets;
			socket_select($changes,$write=NULL,$except=NULL,NULL);
			//遍历用户列表，并进行一些列操作
			foreach ($changes as $socket){
				if($socket == $this->master){
					//服务器的socket
					//获取用户
					$client=socket_accept($this->master);
					$this->sockets[] = $client;
					$this->users[] = array("sock"=>$client, "hand"=>false, "info"=>null);			//由于没有握手完成，所以用户信息为空
				}else{
					$buffer = "";
					//得到客户端发送的消息，最长20M
					$len=socket_recv($socket, $buffer, 1024*1024*30, 0);
					//echo $buffer;
					//找到用户
					$userIndex = self::_findUserBySocket($socket);
					//先判断用户连接是否已经断开
					if($len==false || $len<=0){
						$info = $this->users[$userIndex]["info"];
						self::_deleteUserBySocket($socket);		//删除用户
						self::_deleteSocket($socket);			//删除用户socket
						$info && $this->onDisconnect($info);						//事件
						continue;
					}
					//没有找到用户就算了
					if($userIndex != -1){
						$user = $this->users[$userIndex];
						//判断客户端是否完成握手
						if($user["hand"] == null){
							$json = json_decode($buffer);
							if($json && $json->type=="custom"){
								//调用自定义函数
								$this->onCustom($json->data);
							}else if($json && $json->type=="shutdown"){
								//关闭服务
								return;
							}else{
								//没有完成握手，则完成握手
								self::_handShake($user["sock"], $buffer);
								$this->users[$userIndex]["hand"] = true;
							}
						}else{
							//已经完成握手，则进行消息操作
							$buffer = self::_uncode($buffer);
							//解析消息
							$msgAll = json_decode($buffer);
							switch ($msgAll->type){
								case "add":
									//用户完成握手后添加进来
									$this->users[$userIndex]["info"] = $msgAll->info;
									$this->onConnect($msgAll->info);		//连接成功后的自定义操作
									break;
								case "msg":
									//用户发送了一则消息
									$this->onMessage($user["info"], $msgAll->msg);
									break;
								case "end":
									self::_deleteUserBySocket($socket);			//删除用户
									self::_deleteSocket($socket);				//关闭客户端socket
									$this->onDisconnect($user["info"]);			//用户退出后调用退出操作
									//用户退出
									break;
								case "off":
									//关闭服务器
									return;
								default:
									break;
							}//switch
						}//hand判断
					}//index判断
				}//socket判断
			}//foreach
		}//while
	}
	
	/**
	 * 服务器返回数据时编码
	 * @param unknown $msg		要编码的字符串
	 * @return string		编码后的字符串
	 */
	private function _code($msg){
		$msg = preg_replace(array('/\r$/','/\n$/','/\r\n$/',), '', $msg);
		$frame = array();
		$frame[0] = '81';
		$len = strlen($msg);
		$frame[1] = $len<16?'0'.dechex($len):dechex($len);
		$frame[2] = $this->_ord_hex($msg);
		$data = implode('',$frame);
		return pack("H*", $data);
	}
	
	
	private function _ord_hex($data)  {
		$msg = '';
		$l = strlen($data);
		for ($i= 0; $i<$l; $i++) {
			$msg .= dechex(ord($data{$i}));
		}
		return $msg;
	}
	
	/**
	 * 对接受到的字符串进行解码
	 * @param unknown $str				接收到的字符串
	 * @return Ambigous <string, boolean>		解码后的字符串
	 */
	private function _uncode($str){
		$mask = array();
		$data = '';
		$msg = unpack('H*',$str);
		$head = substr($msg[1],0,2);
		if (hexdec($head{1}) === 8) {
			$data = false;
		}else if (hexdec($head{1}) === 1){
			$mask[] = hexdec(substr($msg[1],4,2));
			$mask[] = hexdec(substr($msg[1],6,2));
			$mask[] = hexdec(substr($msg[1],8,2));
			$mask[] = hexdec(substr($msg[1],10,2));
			 
			$s = 12;
			$e = strlen($msg[1])-2;
			$n = 0;
			for ($i=$s; $i<= $e; $i+= 2) {
				$data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));
				$n++;
			}
		}
		return $data;
	}
	
	/**
	 * 通过id查找用户的位置
	 * @param unknown $id		用户id
	 * @return unknown|number	查找成功返回用户在数组中的位置，否则返回-1
	 */
	private function _findUserById($id){
		foreach ($this->users as $i=>$v){
			if($v["info"]->id == $id) return $i;
		}
		return -1;
	}
	
	/**
	 * 通过socket查找用户位置
	 * @param unknown $sock			socke
	 * @return unknown|number		查找成功返回用户在数组中的位置，否则返回-1
	 */
	private function _findUserBySocket($sock){
		foreach ($this->users as $i=>$v){
			if($v["sock"] == $sock) return $i;
		}
		return -1;
	}
	
	/**
	 * 通过用户socket删除用户
	 * @param unknown $socket		用户的socket
	 * @return boolean		删除成功返回true，如果用户不存在返回false
	 */
	private function _deleteUserBySocket($socket){
		foreach ($this->users as $i=>$v){
			if($v["sock"] == $socket){
				array_splice($this->users, $i, 1);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 查找并删除socket
	 * @param unknown $socket		要删除的socket
	 * @return boolean		删除成功返回true，如果没有找到返回false
	 */
	private function _deleteSocket($socket){
		foreach ($this->sockets as $i=>$v){
			if($v == $socket){
				socket_close($socket);
				array_splice($this->sockets, $i, 1);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 握手操作
	 * @param unknown $sock			要进行握手的客户端
	 * @param unknown $buffer		客户端发送过来的数据
	 * @return boolean		true
	 */
	private function _handShake($sock, $buffer){
		$buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
		$key  = trim(substr($buf,0,strpos($buf,"\r\n")));
		$new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
		$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
		$new_message .= "Upgrade: websocket\r\n";
		$new_message .= "Sec-WebSocket-Version: 13\r\n";
		$new_message .= "Connection: Upgrade\r\n";
		$new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
		socket_write($sock,$new_message,strlen($new_message));
		return true;
	}
	
	private function _close(){
		$this->onClose();
		socket_close($this->master);
	}

}