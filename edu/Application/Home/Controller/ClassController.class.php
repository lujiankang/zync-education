<?php
namespace Home\Controller;

class ClassController extends BaseController{
	
	public function pMain(){
		$this->display("main");
	}
	
	/**
	 * 创建班级
	 * @param unknown $name				班级名称
	 * @param unknown $grade			班级年级
	 * @param unknown $teacher			班主任id
	 */
	public function aCreate($name, $grade, $teacher){
		self::baseCreate("class", array("name"=>$name, "grade"=>$grade, "teacher"=>$teacher), "创建班级错误了，请稍后再试");
	}
	
	/**
	 * 获取班级信息
	 * @param unknown $page			页码
	 * @param unknown $key		·	关键字，没有则查询所有
	 */
	public function gClasses($page, $key=""){
		try{
			//分页
			$page = intval($page);
			$perpage = self::getPerPage();
			//查询
			$db = M("class");
			$where = array(
				"`class`.`enable`"		=>	1,
				array(
					"`class`.`name`"		=>	array("LIKE", "%$key%"),
					"`class`.`grade`"		=>	$key,
					"`teacher`.`name`"		=>	array("LIKE", "%$key%"),
					"_logic"				=>	"OR"
				)
			);
			$classes = $db->join("`teacher` ON `teacher`.`id` = `class`.`teacher`")
			->where($where)->field(array(
				"`class`.*",
				"`teacher`.`name`"=>"teachername"
			))->limit($page*$perpage, $perpage)->select();
			if($classes === false) {
				self::ajaxError("查询班级错误，请稍后再试");
				return;
			}
			$count = $db->join("`teacher` ON `teacher`.`id` = `class`.`teacher`")->where($where)->count();
			self::ajaxSuccess(array(
				"count"		=>	$count,
				"pages"		=>	self::getPages($count, $perpage),
				"datas"		=>	$classes
			));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误" . $e->getMessage());
		}
	}
	
	/**
	 * 删除一个班级
	 * @param unknown $id		要删除的班级的id
	 */
	public function aRemove($id){
		self::baseDelete("class", $id, "删除班级错误，请稍后再试");
	}
	
	/**
	 * 修改班级信息
	 * @param unknown $cid			班级id
	 * @param unknown $name			班级名称
	 * @param unknown $grade		班级所属年级
	 * @param unknown $teacher		班主任id
	 */
	public function aUpdate($cid, $name, $grade, $teacher){
		self::baseUpdate("class", $cid, array("name"=>$name, "grade"=>$grade, "teacher"=>$teacher), "修改班级信息错误，请稍后再试");
	}
	
}
