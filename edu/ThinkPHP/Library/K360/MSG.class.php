<?php
namespace K360;

use K360\WebSocket\WebSocketServer;
use K360\WebSocket\WebSocketUser;
use Think\Model;

class MSG extends WebSocketServer{
	const HOST = "127.0.0.1";
	const PORT = 38438;
	
	const T = 1;
	const F = 0;
	
	public function __construct(){
		set_time_limit(0);
		error_reporting(E_ALL);
		ob_implicit_flush();
		parent::__construct(self::HOST, self::PORT, 1024*1024*20);
	}
	
	/**
	 * 检测服务器是否启动
	 * @return boolean		如果服务器已经启动返回true，否则返回false
	 */
	public static function isRuning(){
		$connection  = @fsockopen(self::HOST, self::PORT);
		return is_resource($connection);
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
		if($lock==self::T){
			return 1;
		}
		//加锁
		self::_lock(self::T);
		//启动
		$ch = curl_init($runUrl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		curl_close($ch);
		//启动成功
		sleep(2);
		if(self::isRuning()){
			self::_lock(self::F);
			return 2;
		}
		//启动失败
		self::_lock(self::F);
		return -1;
	}
	
	
	
	protected function process (WebSocketUser $user, $message, $socket) {
		$data = json_decode($message);
		switch ($data->type){
			case "join":						//收到新用户加入消息
				$info = $data->info;
				$user->uid = $info->id;
				$user->utype = $info->type;
				$this->onUserAddIn($user);
				break;
			case "push":						//收到推送消息
				$this->onPushMessage($data->msg, $data->ignore);
				break;
			case "rmnd":						//收到提醒消息
				$this->onRemind($data->uid, $data->utype, $data->info, $data->ignore);
				break;
			case "chat":						//收到聊天消息
				$this->onChat($data->sender, $data->sendertype, $data->recver, $data->recvertype, $data->msg);
				break;
			case "off":							//收到关闭消息
				exit(0);
				break;
			case "user":						//收到获取用户信息的消息
				$this->onUser($user);
				break;
		}
	}
	
	protected function connected ($user) {
		
	}
	
	protected function closed ($user) {
	}
	
	protected function readmessage($message, $socket){
		$msg = json_decode($message);
		if(!is_object($msg) && !is_array($msg))
			return true;
		if($msg->type == "join") return true;
		$this->process(null, $message, $socket);
		return false;
	}
	

	/**
	 * 发送推送消息
	 * @param unknown $message		消息内容
	 */
	public static function sendPush($message){
		self::_writeData(self::_message("push", "msg", $message));
	}
	
	/**
	 * 发送提醒消息
	 * @param unknown $uid				提醒某个人
	 * @param unknown $message			消息内容
	 */
	public static function sendRemind($uid, $utype, $message){
		$data = array("type"=>"rmnd", "uid"=>$uid, "utype"=>$utype, "info"=>$message);
		self::_writeData(json_encode($data));
	}
	
	
	/**
	 * 关闭服务器
	 */
	public static function closeServer(){
		self::_writeData(json_encode(array("type"=>"off")));
	}
	
	/**
	 * 发送简单信息（简单信息不会保存到数据库）
	 * @param unknown $uid			用户id
	 * @param unknown $utype		用户类型
	 * @param unknown $message		消息内容
	 */
	public static function sendSampleRemind($uid, $utype, $message){
		$data = array("type"=>"rmnd", "uid"=>$uid, "utype"=>$utype, "info"=>$message, "ignore"=>"true");
		self::_writeData(json_encode($data));
	}
	
	/**
	 * 向在线用户发送消息（不会缓存）
	 * @param unknown $message		消息内容
	 */
	public static function sendSamplePush($message){
		$data = array("type"=>"push", "msg"=>$message, "ignore"=>"true");
		self::_writeData(json_encode($data));
	}

	
	/**
	 * 获取用户信息
	 * @param unknown $user			发送请求的用户，如果没有则使用socket返回
	 * @param unknown $socket
	 */
	private function onUser($user){
		$userData = array();
		foreach ($this->users as $u){
			if(!$u->uid || $u->uid=="" || !$u->utype || $u->utype==""){
				$this->disconnect($u->$socket);
				continue;
			}
			$userData[] = array("id"=>$u->uid, "type"=>$u->utype);
		}
		if($user){
			$this->send($user, json_encode(array("type"=>"user", "data"=>$userData)));
		}
	}
	
	
	/**
	 * 响应聊天事件
	 * @param unknown $sender		发送者
	 * @param unknown $sType		发送者类型
	 * @param unknown $receiver		接收者
	 * @param unknown $rType		接收者类型
	 * @param unknown $message		消息体
	 */
	private function onChat($sender, $sType, $receiver, $rType, $message){
		$users = self::_getUsersById($receiver, $rType);
		foreach ($users as $u){
			$this->send($u, self::_message("chat", "msg", array("sender"=>$sender, "sendertype"=>$sType, "content"=>$message)));
		}
		//保存到数据库
		$readed = (count($users)>0) ? true : false;
		$data = is_string($message) ? ("S" . $message) : ("O" . json_encode($message));
		self::_msg2db("message", $sender, $sType, $receiver, $rType, $data, $readed);
	}
	
	/**
	 * 响应提醒事件
	 * @param unknown $userId		提醒此用户
	 * @param unknown $userType		用户类型
	 * @param unknown $message		提醒内容
	 */
	private function onRemind($userId, $userType, $message, $ignore){
		$user = self::_getUsersById($userId, $userType);
		foreach ($user as $u){
			$this->send($u, self::_message("rmnd", "info", $message));
		}
		if($ignore==="true") return;
		$data = is_string($message) ? ("S" . $message) : ("O" . json_encode($message));
		self::_rmd2db("rmnd", $data, $userId, $userType);
	}
	
	/**
	 * 响应推送消息事件
	 * @param unknown $message		推送的消息
	 */
	private function onPushMessage($message, $ignore){
		$this->_sendToAll(self::_message("push", "msg", $message));
		if($ignore==="true") return;
		$data = is_string($message) ? ("S" . $message) : ("O" . json_encode($message));
		self::_rmd2db("rmnd", $data);
	}
	
	/**
	 * 响应用户加入事件
	 * @param unknown $user		用户Object
	 */
	private function onUserAddIn(WebSocketUser $user){
		$this->send($user, self::_message("push", "msg", "欢迎您登陆系统"));
	}
	
	/**
	 * 写入数据到提醒表
	 * @param unknown $type
	 * @param unknown $content
	 * @param string $recver
	 * @param string $rutype
	 */
	private function _rmd2db($type, $content, $recver=null, $rutype=NULL){
		//写入提醒表
		$db = new Model("remind");
		$data = array(
			"type"		=>$type,
			"content"	=>$content,
		);
		if($recver && $rutype){
			$data["recver"] = $recver;
			$data["rutype"] = $rutype;
		}
		$db->data($data)->add();
	}
	
	/**
	 * 写入数据到消息表
	 * @param unknown $type
	 * @param unknown $sender
	 * @param unknown $sutype
	 * @param unknown $recver
	 * @param unknown $rutype
	 * @param unknown $content
	 * @param unknown $readed
	 */
	private function _msg2db($type, $sender, $sutype, $recver, $rutype, $content, $readed){
		$db = new Model("message");
		$db->data(array(
			"type"		=>$type,
			"sender"	=>$sender,
			"sutype"	=>$sutype,
			"recver"	=>$recver,
			"rutype"	=>$rutype,
			"content"	=>$content,
			"readed"	=>$readed
		))->add();
	}
	
	
	
	/**
	 * 通过用户的id获取用户
	 * @param unknown $uid			用户id
	 * @param unknown $uType		用户类型
	 * @return multitype:unknown	用户数组（用户可能多处登陆）
	 */
	private function _getUsersById($uid, $uType){
		$users = array();
		foreach ($this->users as $u){
			if($u->uid == $uid && $u->utype == $uType){
				$users[] = $u;
			}
		}
		return $users;
	}
	
	
	/**
	 * 给除了某个用户之外所有的用户发送消息
	 * @param unknown $user		除开的用户
	 * @param unknown $message	消息体
	 */
	private function _sendToAllBut($user, $message){
		$users = $this->users;
		foreach($users as $u){
			if($u == $user) continue;
			self::send($u, $message);
		}
	}
	
	/**
	 * 给所有用户发送消息
	 * @param unknown $message		消息体
	 */
	private function _sendToAll($message){
		$this->_sendToAllBut(NULL, $message);
	}
	
	/**
	 * 通过socket发送数据给服务器
	 * @param unknown $str		发送的字符串
	 */
	private static function _writeData($str){
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($sock, self::HOST, self::PORT);
		$num = socket_write($sock, $str, strlen($str));
		socket_close($sock);
	}
	

	/**
	 * 生成消息体
	 * @param unknown $type			消息类型
	 * @param unknown $name			消息体名称
	 * @param unknown $data			消息体数据
	 * @return string			生成好的消息体
	 */
	private static function _message($type, $name, $data){
		$msg = array("type"=>$type, $name=>$data);
		return json_encode($msg);
	}

	/**
	 * 锁的读写
	 * @param string $value			写入的值，如果不传则表示度取
	 * @return Ambigous <NULL, string>		度取的值，如果是写入则返回null
	 */
	private function _lock($value = null){
		$shmid = shmop_open(0xF8EA, "c", 0777, 1);
		$buffer = null;
		if($value !== null){
			shmop_write($shmid, $value, 0);
		}else{
			$buffer = shmop_read($shmid, 0, 1);
		}
		shmop_close($shmid);
		return $buffer;
	}
}
