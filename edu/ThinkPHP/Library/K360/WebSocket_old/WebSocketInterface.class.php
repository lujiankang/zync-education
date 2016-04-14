<?php
namespace K360\WebSocket_old;

interface WebSocketInterface_old{
	
	/**
	 * 有用户连接的时候调用
	 * @param unknown $user		连接的用户
	 */
	public function onConnect($user);
	
	/**
	 * 收到用户消息的时候调用
	 * @param unknown $user		消息的发送者
	 * @param unknown $msg		消息内容
	 */
	public function onMessage($user, $msg);
	
	/**
	 * 用户断开连接的时候调用
	 * @param unknown $user		断开连接的用户
	 */
	public function onDisconnect($user);
	
	/**
	 * socket关闭的时候调用
	 */
	public function onClose();
	
	
	/**
	 * 用户自定义操作
	 * @param $data 用户发送的数据
	 */
	public function onCustom($data);
}