<?php
namespace Home\Controller;

class RoleController extends BaseController{
	/**
	 * 角色管理主界面
	 */
	public function pMain(){
		$this->display("main");
	}
	
	/**
	 * 获取角色信息
	 */
	public function gRoles(){
		try{
			$db = M("role");
			$roles = $db->where(array("enable"=>1))->select();
			if($roles === false){
				self::ajaxError("查询数据错误，请稍后再试");
				return;
			}
			self::ajaxSuccess($roles);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 创建一个角色
	 * @param unknown $name			角色名称
	 * @param unknown $desc			角色描述
	 */
	public function aCreate($name, $desc){
		self::baseCreate("role", array("name"=>$name, "desc"=>$desc), "添加角色出现错误，请稍后再试");
	}
	
	/**
	 * 删除一个角色
	 * @param unknown $id			要删除的角色的id
	 */
	public function aRemove($id){
		self::baseDelete("role", $id, "删除角色错误，请稍后再试");
	}
	
	/**
	 * 修改角色信息
	 * @param unknown $rid			角色id
	 * @param unknown $name			角色名称
	 * @param unknown $desc			角色描述
	 */
	public function aUpdate($rid, $name, $desc){
		self::baseUpdate("role", $rid, array("name"=>$name, "desc"=>$desc), "修改角色信息错误，请稍后再试");
	}
	
	/**
	 * 获取某个角色的功能信息（获取所有功能信息和一个powered字段，如果该角色可以访问该功能则powered为1，否则为0）
	 * @param unknown $rid		角色id
	 */
	public function gPowers($rid){
		try{
			//查询所有功能，并把角色拥有的功能用powered标识
			$sql = "
					SELECT `id`, `name`, `parent`,
						(`id` IN (
							SELECT `function` 
							FROM `role_function` 
							WHERE `role`=$rid 
							AND `enable`=1
						)) AS `powered`
					FROM `function` 
					WHERE `enable`=1
			";
			$db = M();
			$datas = $db->query($sql);
			if($datas === false){
				self::ajaxError("查询权限信息错误，请稍后再试");
				return;
			}
			//数据处理（分级）
			$funcs = array();
			foreach ($datas as $v){
				if($v["parent"]==0){
					$buffer = array();
					foreach ($datas as $cv){
						if($cv["parent"] == $v["id"]){
							$buffer[] = $cv;
						}
					}
					$parent = $v;
					$parent["children"] = $buffer;
					$funcs[] = $parent;
				}
			}
			//返回数据
			self::ajaxSuccess($funcs);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 为角色设置权限
	 * @param unknown $rid			角色id
	 * @param unknown $funcs		功能id数组
	 */
	public function aPowerSet($rid, $funcs=array()){
		try{
			$db = M("role_function");
			$db->startTrans();
			//删除原有的数据
			if($db->where(array("role"=>$rid))->delete()===false){
				$db->rollback();
				self::ajaxError("更改角色权限错误，请稍后再试，0x0001");
				return;
			}
			//如果没有数据就不添加了
			if(count($funcs)>0){
				//添加新的数据
				$datas = array();
				foreach ($funcs as $v){
					$datas[] = array("role"=>$rid, "function"=>$v);
				}
				if($db->addAll($datas)===false){
					$db->rollback();
					self::ajaxError("更改角色权限错误，请稍后再试，0x0002");
					return;
				}
			}
			$db->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			$db->rollback();
			self::ajaxError("出现了未知错误");
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}