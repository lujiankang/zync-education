<?php
namespace Home\Controller;

class CourseController extends BaseController{
	
	/**
	 * 课程管理主界面
	 */
	public function pMyCourse(){
		//获取年级
		$dbc = M("class");
		$grades = $dbc->group("`grade`")->order("`grade` DESC")->field("grade")->select();
		$this->assign("grades", $grades);
		$this->display("mycourse");
	}
	
	/**
	 * 管理界面
	 * @param unknown $id		课程的id
	 */
	public function pManage($id){
		//获取课程信息
		$db = M("course");
		$course = $db->where(array("id"=>$id))->find();
		$this->assign("course", $course);
		$this->display("manage");
	}
	
	/**
	 * 考勤中心界面
	 */
	public function pAttend(){
		//获取年级
		$dbc = M("class");
		$grades = $dbc->group("`grade`")->order("`grade` DESC")->field("grade")->select();
		$this->assign("grades", $grades);
		$this->display("attend");
	}
	
	/**
	 * 作业中心界面
	 */
	public function pHomework(){
		//获取年级
		$dbc = M("class");
		$grades = $dbc->group("`grade`")->order("`grade` DESC")->field("grade")->select();
		$this->assign("grades", $grades);
		$this->display("homework");
	}
	
	/**
	 * 打开批改作业界面
	 * @param unknown $id			作业id
	 */
	public function pCorrect($id){
		//获取作业信息
		$db = M("homework");
		$homework = $db->join("`course` ON `course`.`id` = `homework`.`course`")
			->where(array("`homework`.`id`"=>$id))
			->field(array("`homework`.*", "`course`.`name`"=>"coursename", "`course`.`grade`"))->find();
		$this->assign("homework", $homework);
		$this->display("correct");
	}
	
	/**
	 * 打开我的课程界面（学生端）
	 */
	public function pStudentCourse(){
		$dbc = M("class");
		$grades = $dbc->group("`grade`")->order("`grade` DESC")->field("grade")->select();
		$this->assign("grades", $grades);
		$this->display("studentcourse");
	}
	
	/**
	 * 做作业界面（学生端）
	 * @param unknown $homework
	 */
	public function pDoHomework($homework){
		$data = M("homework")
		->join("LEFT JOIN `student_homework` ON `student_homework`.`homework` = `homework`.`id`")
		->where(array(
			"`homework`.`id`"=>$homework,
			"(`student_homework`.`student` IS NULL OR `student_homework`.`student` = 1)"
		))
		->field(array(
			"`homework`.*",
			"`student_homework`.`score`",
			"`student_homework`.`remark`",
			"`student_homework`.`content`"=>"docontent",
			"IF(`student_homework`.`homework` > 0, TRUE, FALSE) AS `issubmit`",
			"IF(`student_homework`.`score` >= 0, TRUE, FALSE) AS `iscorect`"
		))
		->find();
		$data["score"] = $data["issubmit"] ? ($data["iscorect"] ? $data["score"] : "还没有批改，无法查看") : "未上交，无法查看";
		$data["remark"] = $data["issubmit"] ? ($data["iscorect"] ? ($data["remark"] ? $data["remark"] : "没有评语") : "还没有批改，无法查看") : "未上交，无法查看";
		$data["showcontent"] = $data["docontent"] ? $data["docontent"] : $data["content"];
		$this->assign("submitBtnStyle", $data["iscorect"] ? " disabled" : "");
		$this->assign("homework", $data);
		$this->display("dohomework");
	}
	
	/**
	 * 作业中心界面（学生端）
	 */
	public function pStudentHomework(){
		$this->display("studenthomework");
	}
	
	
	
	
	
	/**
	 * 创建课程
	 * @param unknown $name			课程名称
	 * @param unknown $number		课程号
	 * @param unknown $grade		年级
	 */
	public function aCreateCourse($name, $number, $grade){
		self::baseCreate("course", array("name"=>$name, "teacher"=>self::getUserID(), "grade"=>$grade, "number"=>$number), "新建课程错误，请稍后再试");
	}
	
	/**
	 * 删除课程
	 * @param unknown $id		要删除的课程的id
	 */
	public function aRemove($id){
		self::baseDelete("course", $id, "删除课程失败，请稍后再试");
	}
	
	/**
	 * 修改课程信息
	 * @param unknown $cid			课程id
	 * @param unknown $name			课程名称
	 * @param unknown $number		课程编号
	 */
	public function aUpdateCourse($cid, $name, $number){
		self::baseUpdate("course", $cid, array("name"=>$name, "teacher"=>self::getUserID(), "number"=>$number), "修改课程信息错误，请稍后再试");
	}
	
	/**
	 * 获取我的课程
	 * @param unknown $grade		年级
	 * @param number $page			页码
	 * @param string $key			关键字
	 */
	public function gMyCourses($grade, $page=0, $key=""){
		try{
			//分页
			$page = intval($page);
			$perpage = self::getPerPage();
			$db = M("course");
			//条件
			$where = array("enable"=>1, "teacher"=>self::getUserID());
			$like = array("LIKE", "%$key%");
			if($key != ""){
				$where[] = array("name"=>$like, "number"=>$like, "_logic"=>"OR");
			}else{
				$where["grade"] = $grade;
			}
			//查询
			$courses = $db->where($where)
			->field(array(
				"id",
				"name",
				"teacher",
				"grade",
				"number",
				"DATE(`time`)"										=>	"time",
				"(SELECT COUNT(*) FROM `course_file` WHERE `course_file`.`course`= `course`.`id` AND `course_file`.`enable` = 1)"			=>	"filenum",
				"(SELECT COUNT(*) FROM `student_course` WHERE `student_course`.`course` = `course`.`id` AND `student_course`.`enable` = 1)"	=>	"studentnum"
			))->limit($page*$perpage, $perpage)->select();
			if($courses === false){
				self::ajaxError("获取课程错误，请稍后再试");
				return;
			}
			//查询数量
			$count = $db->where($where)->count();
			//页数
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("pages"=>$pages, "count"=>$count, "courses"=>$courses));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	
	
	
	/**
	 * 获取课程文件
	 * @param unknown $course		课程id
	 * @param number $page			页码
	 */
	public function gCourseFiles($course, $page=0){
		//分页
		$page = intval($page);
		$perpage = self::getPerPage();
		try{
			$db = M("file");
			$where = array("`file`.`enable`"=>1, "`course_file`.`enable`"=>1, "`course_file`.`course`"=>$course);
			$files = $db->join("`course_file` ON `course_file`.`file` = `file`.`id`")->where($where)
				->field(array("`file`.`id`", "`file`.`name`", "DATE(`file`.`time`)"=>"time"))
				->limit($page*$perpage, $perpage)->select();
			if($files === false){
				self::ajaxError("查询文件列表错误，请稍后再试");
				return;
			}
			//分页数据
			$count = $db->join("`course_file` ON `course_file`.`file` = `file`.`id`")->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("pages"=>$pages, "count"=>$count, "files"=>$files));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取某个课程下的所有文件
	 * @param unknown $course		课程id
	 */
	public function gCourseFilesAll($course){
		try{
			$db = M("course_file");
			$files = $db->join("`file` ON `file`.`id` = `course_file`.`file`")
				->where(array("`course_file`.`course`"=>$course, "`course_file`.`enable`"=>1, "`course_file`.`enable`"=>1))
				->field(array("`file`.`id`", "`file`.`name`"))
				->order("`file`.`id` DESC")->select();
			if($files === false){
				self::ajaxError("获取文件列表失败，请稍后再试");
				return;
			}
			self::ajaxSuccess($files);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 删除指定课程下的某个文件
	 * @param unknown $course		课程id
	 * @param unknown $file			文件id
	 */
	public function aRemoveCourseFile($course, $file){
		try{
			$dbr = M("course_file");
			//删除
			if($dbr->data(array("enable"=>0))->where(array("file"=>$file, "course"=>$course))->save() === 0){
				self::ajaxError("删除文件错误，请稍后再试".$file.$course);
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}

	/**
	 * 上传文件到课程下
	 * @param unknown $course		课程id
	 */
	public function aUploadCourseFile($course){
		try{
			//上传文档
			$upload = new \Think\Upload(array(
				"maxSize"		=>	1024*1024*10,
				"exts"			=>	array("txt", "jpg", "gif", "png", "bmp", "jpeg", "ppt", "pptx", "doc", "docx", "xls", "xlsx", "pdf"),
				"rootPath"		=>	"./Public/",
				"savePath"		=>	"uploads/",
				"replace"		=>	true		//同名覆盖
			));
			$info = $upload->upload();
			//上传错误
			if(!$info) {
				self::ajaxError($upload->getError());
				return;
			}
			//写入数据库
			$dbf = M("file");
			$dbr = M("course_file");
			$dbf->startTrans();
			$dbr->startTrans();
			//保存到文件
			$file = $info["file"];
			$path = $file["savepath"].$file["savename"];
			if($dbf->data(array(
				"name"=>$file["name"], 
				"path"=>$path, 
				"owner"=>self::getUserID(), 
				"usertype"=>self::getUserType(), 
				"tag"=>C("FILE.COURSE_TAG")
			))->add()===false){
				$dbf->rollback();
				self::ajaxError("保存数据错误，请稍后再试，0x0001");
				return;
			}
			//保存课程
			if($dbr->data(array("course"=>$course, "file"=>$dbf->getLastInsID()))->add()===false){
				$dbr->rollback();
				$dbf->rollback();
				self::ajaxError("保存数据错误，请稍后再试，0x0002");
				return;
			}
			$dbf->commit();
			$dbr->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			$dbr->rollback();
			$dbf->rollback();
			self::ajaxError("出现了未知错误".$e->getMessage());
		}
	}
	
	
	
	/**
	 * 获取某个年级下的学生列表
	 * @param unknown $grade			年级
	 * @param unknown $course			课程id		判断学生是否在此课程下，如果在标注isin为1
	 */
	public function gStudentList($grade, $course){
		try{
			$db = M("student");
			$students = $db->where(array("enable"=>1, "`class` IN (SELECT `id` FROM `class` WHERE `grade`=$grade)"))
			->field(array("id", "number", "name", "sex", "`id` IN(SELECT `student` FROM `student_course` WHERE `course`=$course AND `enable`=1)"=>"isin"))
			->select();
			if($students === false){
				self::ajaxError("查询学生错误，请稍后再试");
				return;
			}
			self::ajaxSuccess($students);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 设置学生名单
	 * @param unknown $course		课程id
	 * @param unknown $students		学生名单
	 */
	public function aSetStudentList($course, $students=array()){
		try{
	 		//思路：查询现有学生名单，与students参数对比，如果在students而不在现有名单则添加，如果在现有名单而不在students则删除，如果在都在，则enable为1
	 		$db = M("student_course");
	 		$db->startTrans();
	 		//查询所有数据
	 		$datas = $db->where(array("course"=>$course))->field(array("id", "student", "enable"))->select();
	 		if($datas === false){
	 			self::ajaxError("保存名单错误，请稍后再试，0x0001");
	 			return;
	 		}
	 		$addData = array();
	 		$updData = array();
	 		$delData = array();
	 		//分析数据（删除、更改）
	 		foreach($datas as $v1){
	 			$isin = false;
	 			foreach ($students as $v2){
	 				if($v1["student"] == $v2){
	 					$isin = true;
	 					break;
	 				}
	 			}
	 			if($isin)
	 				$updData[] = $v1["id"];
	 			else $delData[] = $v1["id"];
	 		}
	 		//分析数据（新增）
	 		foreach ($students as $v1){
	 			$isin = false;
	 			foreach ($datas as $v2){
	 				if($v1 == $v2["student"]){
	 					$isin = true;
	 					break;
	 				}
	 			}
	 			if(!$isin) $addData[] = array("student"=>$v1, "course"=>$course);
	 		}
	 		//增加数据
	 		if(count($addData)>0){
	 			if($db->addAll($addData) === false){
	 				$db->rollback();
	 				self::ajaxError("保存名单错误，请稍后再试，0x0002");
		 			return;
	 			}
	 		}
	 		//更改数据
	 		if(count($updData)>0){
	 			if($db->where(array("id"=>array("IN", $updData)))->data(array("enable"=>1))->save()===false){
	 				$db->rollback();
	 				self::ajaxError("保存名单错误，请稍后再试，0x0003");
	 				return;
	 			}
	 		}
	 		//删除数据
	 		if(count($delData)>0){
	 			if($db->where(array("id"=>array("IN", $delData)))->data(array("enable"=>0))->save() === false){
	 				$db->rollback();
	 				self::ajaxError("保存名单错误，请稍后再试，0x0004");
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
	
	
	
	
	
	
	/**
	 * 获取考勤列表
	 */
	public function gAttends($grade, $page=0, $key=""){
		try{
			$page = intval($page);
			$perpage = self::getPerPage();
			
			$db = M("attend");
			//条件
			$like = array("LIKE", "%$key%");
			$where = array(
					"`attend`.`enable`"		=>	1,
					"`course`.`enable`"		=>	1,
					"`course`.`teacher`"	=>	self::getUserID()
			);
			if($key != ""){
				$where[] = array(
					"`attend`.`name`"	=>	$like,
					"`course`.`name`"	=>	$like,
					"`course`.`number`"	=>	$like,
					"_logic"			=>	"OR"
				);
			}else{
				$where["`course`.`grade`"] = $grade;
			}
			$attends = $db->join("`course` ON `course`.`id` = `attend`.`course`")
			->where($where)->field(array(
				"`attend`.`id`",
				"`attend`.`name`",
				"`attend`.`week`",
				"`course`.`name`"		=>	"coursename",
				"(SELECT COUNT(*) FROM `student_attend` WHERE `student_attend`.`attend`=`attend`.`id` AND `student_attend`.`status`=1 AND `student_attend`.`enable` = 1)"	=>	"latenum",
				"(SELECT COUNT(*) FROM `student_attend` WHERE `student_attend`.`attend`=`attend`.`id` AND `student_attend`.`status`=2 AND `student_attend`.`enable` = 1)"	=>	"lostnum",
				"(SELECT COUNT(*) FROM `student_attend` WHERE `student_attend`.`attend`=`attend`.`id` AND `student_attend`.`status`=3 AND `student_attend`.`enable` = 1)"	=>	"timenum"
			))->order("`attend`.`week` ASC, `attend`.`name` ASC")->limit($page*$perpage, $perpage)->select();
			if($attends === false){
				self::ajaxError("获取考勤列表错误，请稍后再试");
				return;
			}
			$count = $db->join("`course` ON `course`.`id` = `attend`.`course`")->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("count"=>$count, "pages"=>$pages, "attends"=>$attends));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 删除考勤信息
	 * @param unknown $id		考勤id
	 */
	public function aRemoveAttend($id){
		self::baseDelete("attend", $id, "删除考勤信息错误，请稍后再试");
	}
	
	/**
	 * 获取某个考勤的点名信息
	 * @param unknown $id
	 */
	public function gAttendInfo($id){
		try{
			$db = M("student");
			$students = $db->where(array(
				"`student`.`enable`"	=>	1,
				"`student`.`id` IN (SELECT `student_course`.`student` FROM `student_course` WHERE `student_course`.`enable` = 1 AND `student_course`.`course` = (SELECT `course` FROM `attend` WHERE `attend`.`id` = $id))"
			))->field(array(
				"`student`.`id`",
				"`student`.`name`",
				"(SELECT `student_attend`.`status` FROM `student_attend` WHERE `student_attend`.`enable` = 1 AND `student_attend`.`attend` = $id AND `student_attend`.`student` = `student`.`id`)"	=>	"status"
			))->select();
			if($students === false){
				self::ajaxError("加载学生列表错误，请稍后再试");
				return;
			}
			self::ajaxSuccess($students);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取某个课程下的学生
	 * @param unknown $course			课程id
	 */
	public function gCourseStudents($course){
		try{
			$db = M("student");
			$students = $db->join("`student_course` ON `student_course`.`student` = `student`.`id`")
				->where(array(
					"`student_course`.`enable`"	=>	1,
					"`student`.`enable`"			=>	1,
					"`student_course`.`course`"	=>	$course
				))
				->field(array("`student`.`id`", "`student`.`number`", "`student`.`name`"))->select();
			if($students === false){
				self::ajaxError("获取学生列表错误，请稍后再试");
				return;
			}
			self::ajaxSuccess($students);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 保存点名信息之前先获取的信息
	 * @param unknown $course		课程id
	 */
	public function gAttendBeforeSaveInfo($course){
		try{
			//所有点名信息
			$db = M("attend");
			$weekdata = $db->where(array("course"=>$course, "enable"=>1))->field(array("week"))->select();
			if($weekdata === false){
				self::ajaxError("数据加载错误");
				return;
			}
			self::ajaxSuccess($weekdata);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 保存点名信息
	 * @param unknown $course			课程id
	 * @param unknown $week				周数
	 * @param unknown $name				名称
	 * @param unknown $info				包含学生考勤信息的json字符串：[{stu:xxx, stat:xxx}, ....]
	 */
	public function aSaveAttend($course, $week, $name, $info){
		try{
			$info = json_decode($info, true);
			$dba = M("attend");
			$dbr = M("student_attend");
			$dba->startTrans();
			$dbr->startTrans();
			//创建一个点名
			if($dba->data(array("name"=>$name, "week"=>$week, "course"=>$course))->add()===false){
				self::ajaxError("添加点名信息错误，请稍后再试，0x0001");
				return;
			}
			if(count($info)>0){
				//加入点名信息
				$aid = $dba->getLastInsID();
				$addData = array();
				foreach ($info as $v){
					$stuid = $v["stu"];
					$stat = $v["stat"];
					$addData[] = array("student"=>$stuid, "attend"=>$aid, "status"=>$stat);
				}
				if($dbr->addAll($addData)===false){
					self::ajaxError("添加点名信息错误，请稍后再试，0x0002");
					return;
				}
			}
			$dba->commit();
			$dbr->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误" . $e->getMessage());
		}
	}
	
	/**
	 * 修改考勤明细
	 * @param unknown $attend		考勤id
	 * @param unknown $data			学生点名情况，同aSaveAttend
	 */
	public function aUpdateAttendDetail($attend, $data){
		try{
			$data = json_decode($data, true);
			$db = M("student_attend");
			$db->startTrans();
			//查询出所有的考勤记录
			$datas = $db->where(array("attend"=>$attend))->select();
			if($datas === false){
				self::ajaxError("保存考勤信息错误，请稍后再试，0x0001");
				return;
			}
			$addData = array();
			$updData = array();
			$delData = array();
			//数据分析
			foreach ($datas as $v1){
				$isin = false;
				$stat = null;
				foreach ($data as $v2){
					if($v1["student"] == $v2["stu"]){
						$isin = true;
						$stat = $v2["stat"];
						break;
					}
				}
				if($isin){
					$updData[] = array("id"=>$v1["id"], "status"=>$stat, "enable"=>1);
				}else{
					$delData[] = $v1["id"];
				}
			}
			//继续分析
			foreach ($data as $v1){
				$isin = false;
				foreach ($datas as $v2){
					if($v1["stu"] == $v2["student"]){
						$isin = true;
						break;
					}
				}
				if(!$isin) $addData[] = array("student"=>$v1["stu"], "attend"=>$attend, "status"=>$v1["stat"]);
			}
			//数据写入
			if(count($addData)>0){
				if($db->addAll($addData) === false){
					self::ajaxError("保存考勤信息错误，请稍后再试，0x0002");
					return;
				}
			}
			if(count($delData)>0){
				if($db->where(array("id"=>array("IN", $delData)))->data(array("enable"=>0))->save() === false){
					self::ajaxError("保存考勤信息错误，请稍后再试，0x0003");
					return;
				}
			}
			foreach ($updData as $v){
				if($db->where(array("id"=>$v["id"]))->data(array("status"=>$v["status"], "enable"=>1))->save() === false){
					self::ajaxError("保存考勤信息错误，请稍后再试，0x0003");
					return;
				}
			}
			$db->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 重命名考勤
	 * @param unknown $attend		考勤id
	 * @param unknown $name			新名词
	 */
	public function aRenameAttend($attend, $name){
		self::baseUpdate("attend", $attend, array("name"=>$name), "重命名失败，请稍后再试");
	}
	
	
	
	
	/**
	 * 安排作业
	 * @param unknown $course			课程id
	 * @param unknown $name				作业名称
	 * @param unknown $content			作业内容
	 */
	public function aSetHomeWork($course, $name, $content=""){
		try{
			$dbf = M("file");
			$dbh = M("homework");
			$dbf->startTrans();
			$dbh->startTrans();
			//写入作业的数据
			$data = array("course"=>$course, "name"=>$name, "content"=>$content);
			//判断有没有文件
			if(!empty($_FILES['file']['tmp_name'])){
				//上传文件
				$upload = new \Think\Upload(array(
					"maxSize"		=>	1024*1024*10,
					"exts"			=>	array("txt", "jpg", "gif", "png", "bmp", "jpeg", "ppt", "pptx", "doc", "docx", "xls", "xlsx", "pdf"),
					"rootPath"		=>	"./Public/",
					"savePath"		=>	"uploads/",
					"replace"		=>	true		//同名覆盖
				));
				$info = $upload->upload();
				//上传错误
				if(!$info) {
					self::ajaxError($upload->getError());
					return;
				}
				//写入文件数据
				$file = $info["file"];
				$path = $file["savepath"].$file["savename"];
				if($dbf->data(array(
					"name"=>$file["name"],
					"path"=>$path,
					"owner"=>self::getUserID(),
					"usertype"=>self::getUserType(),
					"tag"=>C("FILE.HOMEWORK_TAG")
				))->add()===false){
					self::ajaxError("写入文件数据错误，请稍后再试");
					return;
				}
				//更新作业数据
				$data["file"] = $dbf->getLastInsID();
			}
			//写入作业数据
			if($dbh->data($data)->add() === false){
				self::ajaxError("保存作业数据错误，请稍后再试");
				return;
			}
			$dbf->commit();
			$dbh->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取作业
	 * @param unknown $grade			年级
	 * @param unknown $page				页码
	 * @param unknown $key				关键字
	 */
	public function gHomeworks($grade, $page=0, $key=""){
		try{
			//分页
			$page = intval($page);
			$perpage = self::getPerPage();
			//生成条件
			$db = M("homework");
			$where = array("`course`.`enable`"=>1, "`course`.`teacher`"=>self::getUserID(), "`homework`.`enable`"=>1);
			$like = array("LIKE", "%$key%");
			if($key != ""){
				$where[] = array(
					"`homework`.`name`"		=>	$like,
					"`course`.`name`"		=>	$like,
					"`course`.`number`"		=>	$like,
					"_logic"				=>	"OR"
				);
			}else{
				$where["`course`.`grade`"] = $grade;
			}
			//查询数据
			$homeworks = $db->join("`course` ON `course`.`id` = `homework`.`course`")->where($where)->field(array(
				"`homework`.`id`",
				"`homework`.`name`",
				"`homework`.`content`",
				"`course`.`name`"		=>	"coursename",
				"(SELECT COUNT(*) FROM `student_course` WHERE `student_course`.`course` = `homework`.`course` AND `student_course`.`enable` = 1) AS stunum",
				"(SELECT COUNT(*) FROM `student_homework` WHERE `student_homework`.`enable` = 1 AND `student_homework`.`homework` = `homework`.`id` AND `student_homework`.`file` IS NOT NULL ) AS submnum",
				"(SELECT COUNT(*) FROM `student_homework` WHERE `student_homework`.`enable` = 1 AND `student_homework`.`homework` = `homework`.`id` AND `student_homework`.`score` != -1  ) AS corrnum"
			))->limit($page*$perpage, $perpage)->select();
			if($homeworks === false){
				self::ajaxError("获取作业列表错误，请稍后再试");
				return;
			}
			//查询数量
			$count = $db->join("`course` ON `course`.`id` = `homework`.`course`")->where($where)->count();
			$pages = self::getPages($count, $perpage);
			//返回数据
			self::ajaxSuccess(array("count"=>$count, "pages"=>$pages, "homeworks"=>$homeworks));
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 获取某个课程下的作业
	 * @param unknown $course			课程id
	 */
	public function gHomeworksOfCourse($course){
		try{
			$stuid = self::getUserID();
			$db = M("homework");
			$homeworks = $db->where(array("course"=>$course, "enable"=>1))
			->field(array(
				"id",
				"name",
				"(SELECT COUNT(`id`) FROM `student_homework` WHERE `homework`=`homework`.`id` AND `student`=$stuid) AS `status`",
				"(SELECT `score` FROM `student_homework` WHERE `homework`=`homework`.`id` AND `student`=$stuid LIMIT 1) AS `score`"
			))->select();
			if($homeworks === false){
				self::ajaxError("查询作业失败，请稍后再试");
				return;
			}
			self::ajaxSuccess($homeworks);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 删除作业
	 * @param unknown $id		作业的id
	 */
	public function aRemoveHomework($id){
		self::baseDelete("homework", $id, "删除作业失败，请稍后再试");
	}
	
	/**
	 * 修改作业信息
	 * @param unknown $id			作业id
	 * @param unknown $name			作业名称
	 * @param unknown $content		作业内容
	 */
	public function aUpdateHomework($hid, $name, $content){
		self::baseUpdate("homework", $hid, array("name"=>$name, "content"=>$content), "修改作业信息失败，请稍后再试");
	}
	
	/**
	 * 上传文件到指定的作业下
	 * @param unknown $id		作业id
	 */
	public function aUploadHomeworkFile($id){
		try{
			$dbf = M("file");
			$dbh = M("homework");
			$dbf->startTrans();
			$dbh->startTrans();
			//上传文件
			$upload = new \Think\Upload(array(
					"maxSize"		=>	1024*1024*10,
					"exts"			=>	array("txt", "jpg", "gif", "png", "bmp", "jpeg", "ppt", "pptx", "doc", "docx", "xls", "xlsx", "pdf"),
					"rootPath"		=>	"./Public/",
					"savePath"		=>	"uploads/",
					"replace"		=>	true		//同名覆盖
			));
			$info = $upload->upload();
			//上传错误
			if(!$info) {
				self::ajaxError($upload->getError());
				return;
			}
			//写入文件数据
			$file = $info["file"];
			$path = $file["savepath"].$file["savename"];
			if($dbf->data(array(
					"name"=>$file["name"],
					"path"=>$path,
					"owner"=>self::getUserID(),
					"usertype"=>self::getUserType(),
					"tag"=>C("FILE.HOMEWORK_TAG")
			))->add()===false){
				self::ajaxError("写入文件数据错误，请稍后再试，0x0001");
				return;
			}
			//更新作业数据
			$fid = $dbf->getLastInsID();
			if($dbh->where(array("id"=>$id))->data(array("file"=>$fid))->save() == false){
				self::ajaxError("写入文件数据错误，请稍后再试，0x0002");
				return;
			}
			$dbf->commit();
			$dbh->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取批改作业作业学生列表
	 * @param unknown $id		作业id
	 */
	public function gHomeworCorrectLists($id){
		//作业id  ->  课程id   ->  课程下的学生
		//学生id  ->  学生作业   ->  是否提交文件
		try{
			$db = M("student");
			$where = array(
				"`student`.`enable`"=>1,
				"`student`.`id` IN (
					SELECT `student_course`.`student` 
					FROM `student_course` 
					WHERE `student_course`.`course` = (
							SELECT `course` FROM `homework` 
							WHERE `homework`.`id` = $id
						)
						AND `student_course`.`enable` = 1
					)"
			);
			$students = $db
				->join("LEFT JOIN `class` ON `class`.`id` = `student`.`class`")
				->join("LEFT JOIN `student_homework` ON `student_homework`.`student` = `student`.`id` AND `student_homework`.`homework` = $id")
				->where($where)
				->field(array(
					"`student`.`id`",
					"`student`.`name`",
					"`student`.`number`",
					"`student`.`sex`",
					"CONCAT(`class`.`grade`, '级', `class`.`name`) AS `classname`",
					"`student_homework`.`score`",
					"`student_homework`.`file`",
					"`student_homework`.`remark`",
					"`student_homework`.`content`"=>"docontent"
				))
				->select();
			if($students === false){
				self::ajaxError("获取学生完成情况失败，请稍后再试");
				return;
			}
			self::ajaxSuccess($students);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 作业打分
	 * @param unknown $student			学生
	 * @param unknown $homework			作业
	 * @param unknown $score			分数
	 * @param string $remark			评语
	 */
	public function aHomeworkMaking($student, $homework, $score, $remark=""){
		try{
			//条件/数据
			$where = array("student"=>$student, "homework"=>$homework, "enable"=>1);
			$data = array("score"=>$score, "student"=>$student, "homework"=>$homework, "remark"=>$remark, "time"=>date("Y-m-d H:i:s", time()));
			//更改数据
			$db = M("student_homework");
			$count = $db->where($where)->count();
			if($count === false){
				self::ajaxError("打分错误，请稍后再试，0x0001");
				return ;
			}
			if($count>0){
				//如果已经有数据则修改数据
				if($db->data($data)->where($where)->save() === false){
					self::ajaxError("打分错误，请稍后再试，0x0002");
					return ;
				}
			}else{
				//如果没有数据则添加数据
				if($db->data($data)->add()===false){
					self::ajaxError("打分错误，请稍后再试，0x0003");
					return false;
				}
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误".$e->getMessage());
		}
		
	}
	
	
	
	
	/**
	 * 获取学生的课程
	 * @param number $page		页码
	 * @param string $key		关键字
	 */
	public function gStudentCourse($page=0, $key=""){
		try{
			$page = intval($page);
			$perpage = self::getPerPage();
			$where = array("`course`.`enable`"=>1, "`student_course`.`enable`"=>1, "`student_course`.`student`"=>self::getUserID());
			$like = array("LIKE", "%$key%");
			if($key != ""){
				$where[] = array("`course`.`name`"=>$like, "`teacher`.`name`"=>$like, "_logic"=>"OR");
			}
			$db = M("student_course");
			$courses = $db->join("`course` ON `course`.`id` = `student_course`.`course`")
				->join("`teacher` ON `teacher`.`id` = `course`.`teacher`")
				->where($where)
				->field(array(
					"`course`.*",
					"`teacher`.`name` AS `teachername`",
					"(SELECT COUNT(*) FROM `course_file` WHERE `course_file`.`enable` = 1 AND `course_file`.`course` = `course`.`id`) AS `filenum`"
				))->order("`course`.`time` DESC, `course`.`name` ASC")->limit($page*$perpage, $perpage)->select();
			if($courses === false){
				self::ajaxError("获取课程列表错误，请稍后再试");
				return;
			}
			//分页数据
			$count = $db->join("`course` ON `course`.`id` = `student_course`.`course`")
				->join("`teacher` ON `teacher`.`id` = `course`.`teacher`")
				->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("count"=>$count, "pages"=>$pages, "courses"=>$courses));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取学生作业
	 * @param unknown $type			类型，0未上交，1上交但未批改，2完成，3所有
	 * @param number $page			页码
	 * @param string $key			关键字
	 */
	public function gStudentHomework($type, $page=0, $key=""){
		try{
			$stuid = self::getUserID();
			$db = M("homework");
			$perpage = self::getPerPage();
			$where = array(
				"`homework`.`enable`"=>1
			);
			if($key != ""){
				$like = array("LIKE", "%$key%");
				$where[] = array(
					"`homework`.`name`"=>$like,
					"`course`.`name`"=>$like,
					"_logic"=>"OR"
				);
			}else{
				$where["`student_course`.`student`"] = $stuid;
				$where[] = "(`student_homework`.`student`=$stuid OR `student_homework`.`student` IS NULL)";
				$dict = array(
					"`student_homework`.`homework` is null",													//未上交
					"`student_homework`.`homework` is not null and `student_homework`.`score` != -1",			//上交但未批改
					"`student_homework`.`score`>=0",															//一批该完成
					"true"																						//所有
				);
				$where[] = $dict[$type];
			}
			$homeworks = $db->join("LEFT JOIN `student_homework` ON `student_homework`.`homework` = `homework`.`id`")
			->join("JOIN `course` ON `course`.`id` = `homework`.`course`")
			->join("JOIN `student_course` ON `student_course`.`course` = `course`.`id`")
			->join("JOIN `teacher` ON `teacher`.`id` = `course`.`teacher`")
			->where($where)->field(array(
				"`homework`.`id`", 
				"`homework`.`name`", 
				"`course`.`name`"				=>"coursename",
				"`student_homework`.`score`",
				"`course`.`teacher`"			=>"teacherid",
				"`teacher`.`name`"				=>"teachername",
				"`student_homework`.`file`",
				"IF(`student_homework`.`homework` IS NULL, 0, IF(`student_homework`.`score`>=0, 2, 1)) AS `status`"
			))->group("`homework`.`id`")
			->order("`homework`.`id` DESC")
			->limit($page*$perpage, $perpage)
			->select();
			if($homeworks === false){
				self::ajaxError("查询作业信息错误，请稍后再试");
				return;
			}
			//数据更新
			$statusDict = array("未上交", "等待批改", "已批该");
			foreach ($homeworks as $i=>$v){
				$homeworks[$i]["statusname"] = $statusDict[$v["status"]];
				$homeworks[$i]["score"] = ($v["score"]==-1 || $v["score"]==null) ? "---" : $v["score"];
			}
			//分页数据
			$count = $db->join("LEFT JOIN `student_homework` ON `student_homework`.`homework` = `homework`.`id`")
			->join("JOIN `course` ON `course`.`id` = `homework`.`course`")
			->join("JOIN `student_course` ON `student_course`.`course` = `course`.`id`")
			->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("homeworks"=>$homeworks, "pages"=>$pages, "count"=>$count));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 学生上传作业文档
	 * @param unknown $homework			作业id
	 */
	public function aStudentUploadHomework($homework){
		try{
			$db = M("student_homework");
			$dbf = M("file");
			$db->startTrans();
			$dbf->startTrans();
			//先查看是否有作业
			$where = array("student"=>self::getUserID(), "homework"=>$homework, "enable"=>1);
			$count = $db->where($where)->count();
			if($count === false){
				self::ajaxError("保存错误，请稍后再试，0x0001");
				return;
			}
			$data = array("homework"=>$homework, "student"=>self::getUserID());
			//上传文件
			if(!$_FILES["file"]){
				self::ajaxError("没有任何文件");
				return ;
			}
			//上传文件
			$upload = new \Think\Upload(array(
					"maxSize"		=>	1024*1024*10,
					"exts"			=>	array("txt", "jpg", "gif", "png", "bmp", "jpeg", "ppt", "pptx", "doc", "docx", "xls", "xlsx", "pdf"),
					"rootPath"		=>	"./Public/",
					"savePath"		=>	"uploads/",
					"replace"		=>	true		//同名覆盖
			));
			$info   =   $upload->uploadOne($_FILES['file']);
			//上传错误
			if(!$info) {
				self::ajaxError($upload->getError());
				return;
			}
			$path = $info['savepath'].$info['savename'];
			//保存文件
			if(!self::uploadSaveFile($dbf, $info["name"], $path, "HOMEWORK_TAG")){
				self::ajaxError("保存错误，请稍后再试，0x0003");
				return;
			}
			//保存作业
			$data["file"] = $dbf->getLastInsID();
			$db->data($data);
			//判断是更改还是新建
			if((($count>0) ? $db->where($where)->save() : $db->add()) === false){
				self::ajaxError("保存错误，请稍后再试，0x0002");
				return;
			}
			$dbf->commit();
			$db->commit();
			self::ajaxSuccess(TRUE);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 学生保存作业文档
	 * @param unknown $homework			作业id
	 * @param unknown $content			文档id
	 */
	public function aStudentSetHomeworkContent($homework, $content){
		try{
			$db = M("student_homework");
			//先查看是否有作业
			$where = array("student"=>self::getUserID(), "homework"=>$homework, "enable"=>1);
			$count = $db->where($where)->count();
			if($count === false){
				self::ajaxError("保存错误，请稍后再试，0x0001");
				return;
			}
			//保存内容
			$data = array("homework"=>$homework, "student"=>self::getUserID(), "content"=>$content);
			$db->data($data);
			if((($count>0) ? ($db->where($where)->save()) : ($db->where($where)->add())) === false){
				self::ajaxError("保存错误，请稍后再试，0x0002");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
