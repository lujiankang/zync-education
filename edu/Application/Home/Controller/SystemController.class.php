<?php
namespace Home\Controller;

class SystemController extends BaseController{
	
	/**
	 * 主界面
	 */
	public function pMain(){
		//读取邮件配置
		$dbEmail = M("email_conf");
		$emeilConf = $dbEmail->find();
		//分配数据
		$this->assign("mailConf", $emeilConf);
		$this->display("main");
	}
	
	
	public function aSaveMailConf($server, $port, $addr, $user, $password, $username){
		try{
			$db = M("email_conf");
			if($db->data(array("server"=>$server, "port"=>$port, "addr"=>$addr, "user"=>$user, "password"=>$password, "username"=>$username))->where(1)->save()===false){
				self::ajaxError("保存邮件配置失败");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	
}