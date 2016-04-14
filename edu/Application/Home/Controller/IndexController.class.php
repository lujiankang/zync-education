<?php
namespace Home\Controller;

class IndexController extends BaseController{
	
	/**
	 * 系统主页
	 */
	public function index(){
		self::autoRunTimer();
		self::autoRunWS();
		$this->assign("logined", self::isLogin());
		$this->display("index");
	}


	/**
	 * 主页面
	 */
	public function pMain(){
		$this->display("main");
	}
	

	/**
	 * 获取系统菜单
	 */
	public function gFuncs(){
		if(self::isTeacher()){
			//查询功能
			$info = self::getUserInfo();
			$db = M("function");
			$ret = $db->join("`role_function` ON `role_function`.`function` = `function`.`id`")
			->where(array("`role_function`.`enable`"=>1, "`function`.`enable`"=>1, "`role_function`.`role`"=>$info["role"]))
			->field(array(
				"`function`.`id`",
				"`function`.`name`",
				"`function`.`url`",
				"`function`.`parent`",
				"`function`.`icon`"
			))->order("`function`.`order` ASC")->select();
			if($ret === false){
				self::ajaxError("获取系统菜单错误，请稍后再试");
				return;
			}
			//分析数据
			$funcs = array();
			foreach ($ret as $v){
				if($v["parent"]==0){
					$buffer = array();
					foreach ($ret as $cv){
						if($cv["parent"] == $v["id"])
							$buffer[] = $cv;
					}
					$v["children"] = $buffer;
					$funcs[] = $v;
				}
			}
		}
		else{
			$funcs = C("STUFUN");
		}
		self::ajaxSuccess($funcs);
	}
	
	/**
	 * 添加反馈信息
	 * @param unknown $content			反馈内容
	 */
	public function aAddBackSeed($content){
		self::baseCreate("backseed", array("user"=>self::getUserID(), "utype"=>self::getUserType(), "content"=>$content), "反馈失败了，请稍后再试");
	}
	
	/**
	 * 获取“我”的未读消息
	 */
	public function gUnReadMsg(){
		try{
			//获取消息
			$db = M("message");
			$msgs = $db->where(array("readed"=>0, "recver"=>self::getUserID(), "rutype"=>self::getUserType()))->select();
			if($msgs === false){
				self::ajaxError("获取消息失败，0x0001");
				return;
			}
			//生成id数组
			$arrId = array();
			foreach ($msgs as $v){
				$arrId[] = $v["id"];
			}
			if(count($arrId)>0){
				//修改为已阅读
				$db->flush();
				$db->where(array("id"=>array("IN", $arrId)))->data(array("readed"=>1))->save();
			}
			//返回数据
			self::ajaxSuccess($msgs);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	

}