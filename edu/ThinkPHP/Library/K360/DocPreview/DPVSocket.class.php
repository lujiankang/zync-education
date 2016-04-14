<?php
namespace K360\DocPreview;

class DPVSocket{
	/**
	 * 服务器的ip地址
	 */
	const IP = "127.0.0.1";

	/**
	 * 服务器的端口号
	 */
	const PORT = 5341;
	
	/**
	 * 服务器关闭操作，此方法禁用
	 */
	const JSON_OFF = 0x0001;
	
	/**
	 * 服务器文档转换操作
	 */
	const JSON_PARSE = 0x0002;
	
	/**
	 *	socket连接
	 */
	private $socket = null;

	/**
	 * 错误信息
	 */
	private $error = null;
	
	
	/**
	 * 在数据发送之前调用，请重写此方法，否则无法通过XXX
	 * @return multitype		url以及其他数据组成的一个数组
	 */
	protected function onBeforeSend(){
		//提供url以及其他信息，url是必须的
		return 
		array(					//设置要解析的数据
			"url"=>""			//文档的url
			// ... ...
		);
	}
	
	/**
	 * 接受完毕后调用这个方法，请重写此方法以获取服务器返回的数据XXX
	 * @param unknown $data
	 */
	protected function onAfterRecv($data){
		//接收完毕
	}
	
	/**
	 * 连接到服务器
	 * @return boolean		连接成功返回true，否则返回false
	 */
	protected function connect(){
		//创建socket
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($this->socket == false){
			$this->error = "创建连接失败";
			return false;
		}
		//连接
		if(false == socket_connect($this->socket, DPVSocket::IP, DPVSocket::PORT)){
			socket_close($this->socket);
			$this->socket = null;
			$this->error = "连接服务器失败";
			return false;
		}
		return true;
	}
	
	/**
	 * 进行解析
	 * @return boolean		解析成功返回true，否则返回false
	 */
	protected function parse(){
		//组合数据
		$post = array(
			"type"=>DPVSocket::JSON_PARSE,				//要求服务器做解析操作
			"datas"=>$this->onBeforeSend()				//得到要发送到服务器的其他数据
		);
		//发送数据到服务器
		if(false === socket_write($this->socket, json_encode($post))){
			socket_close($this->socket);
			$this->socket = null;
			$this->error = "发送请求失败";
			return false;
		}
		//读取数据
		$str = "";
		$buffer = "";
		while(true){
			$len = socket_recv($this->socket, $buffer, 1024, 0);
			if($len <= 0 || $len == false)
				break;
			$str .= $buffer;
		}
		//先关闭socket
		socket_close($this->socket);
		$this->socket = null;
		//解析服务器返回数据
		$json = json_decode($str);
		if(!$json){
			$this->error = "数据解析错误";
			return false;
		}
		//如果出现错误
		if($json->error){
			$this->error = $json->error;
			return false;
		}
		$this->onAfterRecv($json);
		return true;
	}
	
	/**
	 * 获取错误信息
	 * @return string		错误信息
	 */
	public function getError(){
		return $this->error;
	}
	
	/**
	 * 析构函数，用于关闭socket
	 */
	public function __destruct(){
		if($this->socket != null){
			socket_close($this->socket);
		}
	}
	
	/**
	 * 获取项目的根目录，根据这个地址可以很方便的找到项目下的文件
	 * @param boolean $separator			是否在根目录后面先加上斜杠（/），默认会加上
	 * @return string		根目录地址。（exmp：http://xx.xx.xx.xx:80/Demo/）
	 */
	public function getBasePath($separator = true){
		return "http://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . __ROOT__ . ($separator?"/":"");
	}
	
	/**
	 * 获取项目的Public文件夹，根据这个地址能方便得到Public文件夹下的文件
	 * @param boolean $separator			是否在文件夹末尾加上斜杠（/），默认会加上
	 * @return string		Public目录的地址
	 */
	public function getPublic($separator = true){
		return $this->getBasePath() . "Public" . ($separator?"/":"");
	}
	
}