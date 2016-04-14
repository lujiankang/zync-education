<?php
namespace Home\Controller;

class EducationController extends BaseController{
	
	public function pEssay(){
		$dbc = M("class");
		$grades = $dbc->group("`grade`")->order("`grade` DESC")->field("grade")->select();
		$this->assign("grades", $grades);
		$this->display("essay");
	}
	
	public function pResearch(){
		echo "<h2>Nothing has done! This is only a null module.</h2>";
	}
	
	public function pEduInfo(){
		$dbc = M("class");
		$grades = $dbc->group("`grade`")->order("`grade` DESC")->field("grade")->select();
		$this->assign("grades", $grades);
		$this->display("eduinfo");
	}
	
	
	public function gEssayInfo($class, $page=0){
		try{
			$perpage = self::getPerPage("EducationGetEssayInfo");
			$db = M("student");
			$where = array("`student`.`enable`"=>1, "`student`.`class`"=>$class);
			//数据查询
			$infos = $db
			->join("LEFT JOIN `essay` ON `essay`.`student` = `student`.`id`")
			->join("LEFT JOIN `teacher` ON `essay`.`teacher` = `teacher`.`id`")
			->where($where)
			->field(array(
				"`student`.`id`",
				"`student`.`number`",
				"`student`.`name`",
				"`essay`.`name`"		=>"essayname",
				"`teacher`.`name`"		=>"teachername",
				"IF(`essay`.`id` IS NULL, 0, (SELECT COUNT(*) FROM `student_essay` WHERE `student_essay`.`enable` = 1 AND `student_essay`.`file` IS NOT NULL)) AS `essaynum`",
				"IF(`essay`.`id` IS NULL, 0, (SELECT COUNT(*) FROM `student_essay` WHERE `student_essay`.`replyer` <> 0)) AS `replynum`"
			))->order("`student`.`id` ASC")->limit($page*$perpage, $perpage)->select();
			if($infos === false){
				self::ajaxError("获取信息失败，请稍后再试");
				return;
			}
			//分页查询
			$count = $db->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("info"=>$infos, "count"=>$count, "pages"=>$pages));
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	public function gEduInfo($grade, $page=0){
		try{
			$perpage = self::getPerPage("EducationGetEduInfo");
			$db = M("course");
			//查询
			$where = array("`course`.`grade`"=>$grade, "`course`.`enable`"=>1);
			$courses = $db
			->join("LEFT JOIN `teacher` ON `teacher`.`id` = `course`.`teacher`")
			->where($where)
			->field(array(
				"`course`.`id`",
				"`course`.`name`",
				"`course`.`number`",
				"`course`.`teacher`",
				"`teacher`.`name`"		=>	"teachername",
				"(SELECT COUNT(*) FROM `student_course` WHERE `student_course`.`course` = `course`.`id`) AS `stunum`",
				"(SELECT COUNT(*) FROM `attend` WHERE `attend`.`course` = `course`.`id`) AS `attnum`",
				"(SELECT COUNT(*) FROM `homework` WHERE `homework`.`course` = `course`.`id`) `hwknum`"
			))->order("`course`.`name` ASC")->limit($perpage*$page, $perpage)->select();
			if($courses === false){
				self::ajaxError("获取教学情况列表错误，请稍后再试");
				return;
			}
			//分页数据
			$count = $db->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("count"=>$count, "pages"=>$pages, "courses"=>$courses));
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}