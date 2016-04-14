<?php
namespace Home\Controller;

use Think\Log;
class CourseController extends BaseController{
	
	/**
	 * 课程管理主界面
	 */
	public function pMyCourse(){
		//获取科目
		$db = M("subject");
		$subjects = $db->where(array("enable"=>1))->field(array("id", "name"))->select();
		$this->assign("subjects", $subjects);
		$this->assign("forStudent", "false");
		$this->display("mycourse");
	}
	
	/**
	 * 学生课程管理主界面
	 */
	public function pStudentCourse(){
		$this->assign("forStudent", "true");
		$this->display("mycourse");
	}
	
	public function gSubjectOfTimeStudent($year, $degree){
		try{
			$from = $year . (($degree==1) ? "-01-01" : "-07-01");
			$to = $year . (($degree==1) ? "-06-30" : "-12-31");
			$db = M("student_course");
			$courses = $db
			->join("`course` ON `course`.`id` = `student_course`.`course`")
			->join("`subject` ON `subject`.`id` = `course`.`subject`")
			->where(array(
					"`student_course`.`student`"	=>	self::getUserID(),
					"`course`.`time`>'$from' AND `course`.`time`<'$to'",
					"`student_course`.`enable`"	=>	1
			))
			->field(array(
					"`course`.`id`",
					"`course`.`name`",
					"`course`.`subject`",
					"`course`.`number`",
					"`course`.`grade`",
					"DATE(`course`.`time`)"		=>	"time",
					"`subject`.`name`"			=>	"subjectname",
					"`subject`.`desc`"			=>	"subjectdesc",
					"`subject`.`chapter`"		=>	"chapter"
			))
			->select();
			if(false === $courses){
				self::ajaxError("查询课程列表失败，请稍后再试");
				return;
			}
			foreach ($courses as $i=>$v){
				$courses[$i]["chapter"] = json_decode($courses[$i]["chapter"]);
				$courses[$i]["chapter"] || ($courses[$i]["chapter"] = array());
			}
			self::ajaxSuccess($courses);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取某个时间段下的课程
	 * @param unknown $year			年份
	 * @param unknown $degree		季度（1标识上半年，2标识下半年）
	 */
	public function gSubjectOfTime($year, $degree){
		try{
			$from = $year . (($degree==1) ? "-01-01" : "-07-01");
			$to = $year . (($degree==1) ? "-06-30" : "-12-31");
			$db = M("course");
			$courses = $db
			->join("`subject` ON `subject`.`id` = `course`.`subject`")
			->where(array(
				"`course`.`teacher`"	=>	self::getUserID(),
				"`course`.`time`>'$from' AND `course`.`time`<'$to'",
				"`course`.`enable`"	=>	1
			))
			->field(array(
				"`course`.`id`",
				"`course`.`name`",
				"`course`.`subject`",
				"`course`.`number`",
				"`course`.`grade`",
				"DATE(`course`.`time`)"		=>	"time",
				"`subject`.`name`"			=>	"subjectname",
				"`subject`.`desc`"			=>	"subjectdesc",
				"`subject`.`chapter`"		=>	"chapter"
			))
			->select();
			if(false === $courses){
				self::ajaxError("查询课程列表失败，请稍后再试");
				return;
			}
			foreach ($courses as $i=>$v){
				$courses[$i]["chapter"] = json_decode($courses[$i]["chapter"]);
				$courses[$i]["chapter"] || ($courses[$i]["chapter"] = array());
			}
			self::ajaxSuccess($courses);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}

	/**
	 * 获取某个课程的学生信息
	 * @param unknown $course			课程id
	 */
	public function gStudentInfo($course){
		try{
			$dbc = M("course");
			$dbs = M("student");
			$dbsc = M("student_course");
			//通过课程查询年级
			$courseGrade = $dbc->where(array("id"=>$course))->field(array("grade"))->find();
			$grade = $courseGrade["grade"];
			//查询某个年级下的所有学生
			$students = $dbs->join("`class` ON `student`.`class` = `class`.`id`")
			->where(array("`class`.`grade`"=>$grade, "`student`.`enable`"=>1))
			->field(array("`student`.`id`", "`student`.`number`", "`student`.`name`"))
			->select();
			//查询课程下的学生的id
			$studentIds = $dbsc->where(array("course"=>$course, "enable"=>1))->field(array("student"))->select();
			if($courseGrade===false || $studentIds===false){
				self::ajaxError("获取学生列表错误，请稍后再试");
				return;
			}
			$ids = array();
			foreach ($studentIds as $v){
				array_push($ids, $v["student"]);
			}
			//返回数据
			self::ajaxSuccess(array("all"=>$students, "cur"=>$ids));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取某个课程下的文件
	 * @param unknown $id			课程id
	 */
	public function gCourseFile($id){
		try{
			$db = M("course_file");
			$files = $db->join("`file` ON `file`.`id` = `course_file`.`file`")
			->where(array(
				"`course_file`.`enable`"=>1,
				"`course_file`.`course`"=>$id
			))->field(array(
				"`course_file`.`id`",
				"`course_file`.`chapter`",
				"`course_file`.`file`",
				"`file`.`name`"			=>	"filename",
				"`file`.`time`"
			))->order("`chapter` ASC")->select();
			if(false === $files){
				self::ajaxError("获取文件列表失败，请稍后再试");
				return;
			}
			//解析章节
			foreach ($files as $i=>$v){
				$files[$i]["chapter"] = json_decode($files[$i]["chapter"]);
			}
			self::ajaxSuccess($files);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 创建课程
	 * @param unknown $name			课程名
	 * @param unknown $subject		科目id
	 * @param unknown $number		课程号
	 * @param unknown $grade		针对学生年级
	 */
	public function aCreateCourse($name, $subject, $number, $grade){
		self::baseCreate("course", array("name"=>$name, "subject"=>$subject, "number"=>$number, "teacher"=>self::getUserID(), "grade"=>$grade), "创建课程失败，请稍后再试");
	}
	
	/**
	 * 修改课程信息
	 * @param unknown $cid			课程id
	 * @param unknown $name			课程名
	 * @param unknown $subject		科目id
	 * @param unknown $number		课程号
	 */
	public function aUpdateCourse($cid, $name, $subject, $number){
		self::baseUpdate("course", $cid, array("name"=>$name, "subject"=>$subject, "number"=>$number), "修改课程失败，请稍后再试");
	}
	
	/**
	 * 删除课程
	 * @param unknown $id		课程id
	 */
	public function aDeleteCourse($id){
		self::baseDelete("course", $id, "删除课程失败，请稍后再试");
	}
	
	/**
	 * 上传文件到课程
	 * @param unknown $courses			课程id
	 * @param unknown $chapter			章节，形如："[1, 2]"——————表示第一章第二节
	 */
	public function aUploadCourseFile($courses, $chapter){
		try{
			//写入数据库
			$dbf = M("file");
			$dbr = M("course_file");
			$dbf->startTrans();
			$dbr->startTrans();
			//上传并保存文件
			$info = self::uploadFiles();
			if(is_string($info)){
				self::ajaxError("上传文件错误，0x0001");
				return;
			}
			$fids = self::uploadSaveFiles($dbf, $info, "COURSE_TAG");
			if($fids === false){
				self::ajaxError("上传文件错误，0x0002");
				return;
			}
			//保存课程
			$datas = array();
			foreach($courses as $course){
				foreach ($fids as $fid){
					array_push($datas, array("course"=>$course, "file"=>$fid, "chapter"=>$chapter));
				}
			}
			if($dbr->addAll($datas)===false){
				self::ajaxError("保存数据错误，请稍后再试");
				return;
			}
			$dbf->commit();
			$dbr->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 删除课程文件
	 * @param unknown $rid			课程-文件表关系id
	 */
	public function aDeleteCourseFile($rid){
		self::baseDelete("course_file", $rid, "删除文件失败，请稍后再试");
	}
	
	/**
	 * 保存课程学生名单
	 * @param unknown $course			课程id
	 * @param unknown $add				需要添加的学生名单
	 * @param unknown $del				需要删除的学生名单
	 */
	public function aSaveStudents($course, $add=array(), $del=array()){
		try{
			$db = M("student_course");
			$db->startTrans();
			$added = array();
			foreach ($add as $v){
				array_push($added, array("course"=>$course, "student"=>$v));
			}
			$ret1 = (count($add)==0) ? true : $db->addAll($added);
			$ret2 = (count($del)==0) ? true : ($db->where(array("course"=>$course, "student"=>array("IN", $del)))->data(array("enable"=>0))->save());
			if(($ret1===false) || ($ret2===false)){
				self::ajaxError("保存学生信息失败，请稍后再试");
				Log::write($db->_sql());
				return;
			}
			$db->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
}
