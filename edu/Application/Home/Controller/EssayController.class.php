<?php
namespace Home\Controller;

class EssayController extends BaseController{
	/**
	 * 论文主界面
	 */
	public function pMain(){
		$dbc = M("class");
		$grades = $dbc->group("`grade`")->order("`grade` DESC")->field("grade")->select();
		$class =$dbc->where(array("enable"=>1))->select();
		$this->assign("grades", $grades);
		$this->assign("class", $class);
		$this->display("main");
	}
	
	/**
	 * 论文详情界面
	 * @param unknown $essay		论文id
	 */
	public function pEssayInfo($essay){
		$curEssay = M("essay")->where(array("id"=>$essay))->find();
		$this->assign("essay", $curEssay);
		$this->assign("viewer", "teacher");
		$this->assign("hideStr", "style='display:none'");
		$this->display("info");
	}
	
	/**
	 * 学生主页
	 */
	public function pStudentMains(){
		//查询论文
		$essayInfo = M("essay")->where(array("enable"=>1, "student"=>self::getUserID()))->find();
		$this->assign("essay", $essayInfo);
		$this->assign("viewer", "student");
		$this->assign("hideStr", "");
		$this->display("info");
	}
	
	/**
	 * 打开8张表
	 * @param unknown $type			打开类型
	 */
	public function pTable8($essay, $type){
		$this->assign("essay", $essay);
		$this->assign("type", $type);
		$this->display("table8");
	}
	
	/**
	 * 相关资料管理
	 */
	public function pResource(){
		$this->display("resource");
	}
	
	/**
	 * 下载资料
	 * @param unknown $ids		资料id数组，这里只去一个
	 * @param unknown $name		下载的文件名
	 */
	public function pResourceDownload($ids, $name){
		//自增
		$db = M("essay_resource");
		$db->where(array("id"=>$ids[0]))->setInc("downnum");
		//获取文件id
		$res = $db->where(array("id"=>$ids[0]))->find();
		self::pDownloadBase(array($res["file"]), $name);
	}
	
	/**
	 * 获取论文列表
	 * @param unknown $grade			年级
	 * @param unknown $page				页码
	 * @param unknown $key				关键字
	 */
	public function gEssaies($grade, $page=0, $key=""){
		try{
			//生成查询条件
			$where = array("`essay`.`enable`"=>1, "`student`.`enable`"=>1, "`essay`.`teacher`"=>self::getUserID());
			$like = array("LIKE", "%$key%");
			if($key != ""){
				$where[] = array(
					"`student`.`name`"		=>	$like,
					"`student`.`number`"	=>	$like,
					"`essay`.`name`"		=>	$like,
					"_logic"				=>	"OR"
				);
			}else{
				$where["`class`.`grade`"] = $grade;
			}
			//查询数据
			$page = intval($page);
			$perpage = self::getPerPage();
			$db = M("essay");
			$essays = $db
				->join("LEFT JOIN `student` ON `student`.`id` = `essay`.`student`")
				->join("JOIN `class` ON `class`.`id` = `student`.`class`")
				->where($where)->field(array(
					"`essay`.`id`",
					"`essay`.`name`",
					"`essay`.`finish`",
					"`essay`.`hasnew`",
					"`essay`.`student`",
					"`essay`.`desc`",
					"`student`.`name`"		=>	"studentname",
					"`student`.`number`"	=>	"stunumber",
					"`class`.`id`"			=>	"class",
					"(SELECT `file` FROM `student_essay` WHERE `student_essay`.`essay` = `essay`.`id` ORDER BY `student_essay`.`id` DESC LIMIT 1) AS `file`"
			))->order("`student`.`number` ASC")->limit($page*$perpage, $perpage)->select();
			if($essays === false){
				self::ajaxError("获取论文列表错误，请稍后再试");
				return;
			}
			//分页数据
			$count = $db->join("LEFT JOIN `student` ON `student`.`id` = `essay`.`student`")->join("JOIN `class` ON `class`.`id` = `student`.`class`")->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("count"=>$count, "pages"=>$pages, "essays"=>$essays));
		}catch (\Exception $e){
			self::ajaxError("出现未知错误".$e->getMessage());
		}
	}
	
	/**
	 * 创建论文
	 * @param unknown $name				论文名称
	 * @param unknown $student			学生
	 * @param unknown $desc				描述
	 */
	public function aCreate($name, $student, $desc){
		self::baseCreate("essay", array("name"=>$name, "desc"=>$desc, "student"=>$student, "teacher"=>self::getUserID(), "creator"=>self::getUserID()), "创建题目错误，请稍后再试");
	}
	
	/**
	 * 删除论文
	 * @param unknown $id		论文id
	 */
	public function aRemove($id){
		self::baseDelete("essay", $id, "删除题目错误，请稍后再试");
	}
	
	/**
	 * 修改论文信息
	 * @param unknown $eid			论文id
	 * @param unknown $name			论文名称
	 * @param unknown $student		学生
	 * @param unknown $desc			描述
	 */
	public function aUpdate($eid, $name, $student, $desc){
		self::baseUpdate("essay", $eid, array("name"=>$name, "student"=>$student, "desc"=>$desc), "修改题目错误，请稍后再试");
	}
	
	/**
	 * 发表回复
	 * @param unknown $eid			论文id
	 * @param unknown $desc			恢复内容
	 */
	public function aReply($eid, $desc){
		try{
			$dbf = M("file");
			$dbr = M("student_essay");
			$dbe = M("essay");
			$dbf->startTrans();
			$dbr->startTrans();
			$dbe->startTrans();
			//查询到最后一则学生提交信息
			$r = $dbr->where(array("essay"=>$eid, "enable"=>1))->field("id")->order("`id` DESC")->find();
			if($r === false || count($r)==0){
				self::ajaxError("回复错误，请稍后再试，0x0001");
				return;
			}
			$data = array("reply"=>$desc);			//更新的数据
			//上传文件
			if(!empty($_FILES['file']['tmp_name'])){
				//上传文件
				$info = self::uploadFiles(array("txt", "jpg", "gif", "png", "bmp", "jpeg", "ppt", "pptx", "doc", "docx", "xls", "xlsx", "pdf"));
				if(is_string($info)){
					self::ajaxError($info);
					return;
				}
				//写入文件数据
				$ids = self::uploadSaveFiles($dbf, $info, "ESSAY_TAG");
				if($ids===false){
					self::ajaxError("回复错误，请稍后再试，0x0002");
					return;
				}
				//更新作业数据
				$data["replyfile"] = $ids[0];
			}
			if($dbr->where(array("id"=>$r["id"]))->data($data)->save() === false){
				self::ajaxError("回复错误，请稍后再试，0x0003");
				return;
			}
			if($dbe->where(array("id"=>$eid))->data(array("hasnew"=>0))->save() === false){
				self::ajaxError("回复错误，请稍后再试，0x0004");
				return;
			}
			//OK
			$dbf->commit();
			$dbr->commit();
			$dbe->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 终稿——————已弃用
	 * @param unknown $id		论文id
	 */
	public function aFinish($id){
		self::baseUpdate("essay", $id, array("finish"=>1), "出现了错误，请稍后再试");
	}
	
	/**
	 * 获取8个表的详细信息
	 * @param unknown $essay			论文id
	 */
	public function gTable8($essay){
		try{
			//获取论文信息
			$essayInfo = M("essay")->join("`teacher` ON `teacher`.`id` = `essay`.`teacher`")
			->where(array("`essay`.`id`"=>$essay))
			->field(array("`essay`.`name`", "`essay`.`student`", "`teacher`.`name`"=>"teacher"))
			->find();
			if($essayInfo === false){
				self::ajaxError("获取论文信息错误，请稍后再试，0x0001");
				return;
			}
			//查询学生信息
			$studentInfo = M("student")->join("`class` ON `class`.`id` = `student`.`class`")
			->field(array("`student`.`name`", "`student`.`number`", "`class`.`grade`", "`class`.`name`"=>"class"))
			->where(array("`student`.`id`"=>$essayInfo["student"]))->find();
			if($studentInfo === false){
				self::ajaxError("获取学生信息错误，请稍后再试，0x0002");
				return ;
			}
			//查询8个表数据
			$table8Info = M("table8")->where(array("essay"=>$essay))->find();
			if($table8Info === false){
				self::ajaxError("查询8个表的数据错误，请稍后再试，0x0003");
				return;
			}
			//如果没有数据则加入数据
			(count($table8Info)==0) && (M("table8")->data(array("essay"=>$essay))->add());
			//合并数据并返回
			$ret = $table8Info;
			$ret["essay_name"] = $essayInfo["name"];
			$ret["grade"] = $studentInfo["grade"] . "级";
			$ret["student_name"] = $studentInfo["name"];
			$ret["student_number"] = $studentInfo["number"];
			$ret["guid_teacher"] = $essayInfo["teacher"];
			$ret["student_class"] = $ret["grade"] . $studentInfo["class"];
			$ret["faculty"] = "计算机与信息科学学院";
			$ret["profession_grade"] = $table8Info["profession"] ? ($table8Info["profession"]."\n".$ret["grade"]) : "";
			self::ajaxSuccess($ret);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	
	/**
	 * 获取时间轴数据信息
	 * @param unknown $essay		论文id
	 */
	public function gEssayTimeLine($essay){
		try{
			//查询论文信息
			$essayInfo = M("essay")->join("`teacher` ON `teacher`.`id` = `essay`.`creator`")
			->join("`student` ON `student`.`id` = `essay`.`student`")
			->where(array("`essay`.`id`"=>$essay))
			->field(array("`essay`.`name`", "`essay`.`desc`", "`teacher`.`name`"=>"teachername", "`student`.`name`"=>"studentname", "`essay`.`creator`", "DATE(`time`)"=>"time"))
			->find();
			if($essayInfo === false){
				self::ajaxError("获取基本信息错误，请稍后再试,0x0001");
			}
			//获取学生提交信息
			$submitList = M("student_essay")
			->where(array("essay"=>$essay, "enable"=>1))
			->field(array("id", "essay", "file", "DATE(`submtime`)"=>"time", "desc", "'".$essayInfo["studentname"]."'"=>"studentname"))
			->order("`time` ASC")->select();
			//获取教师意见
			$replyList = M("student_essay")
			->join("`teacher` ON `teacher`.`id` = `student_essay`.`replyer`")->where(array("essay"=>$essay, "`student_essay`.`enable`"=>1))
			->field(array("`student_essay`.`id`", "essay", "replyfile"=>"file", "DATE(`replytime`)"=>"time", "reply"=>"desc", "replyer", "`teacher`.`name`"=>"teachername", "'".$essayInfo["studentname"]."'"=>"studentname"))
			->order("`time` ASC")->select();
			if($replyList === false){
				self::ajaxError("获取数据错误，请稍后再试，0x0002");
				return;
			}
			//获取其他列表
			$otherList = M("essay_log")
			->where(array("essay"=>$essay, "enable"=>1))
			->field(array("id", "log"=>"desc", "time"))
			->select();
			if($otherList === false){
				self::ajaxError("获取数据错误，请稍后再试，0x0003");
				return;
			}
			self::ajaxSuccess(array("essay"=>array($essayInfo), "submit"=>$submitList, "reply"=>$replyList, "other"=>$otherList));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	
	/**
	 * 教师修改回复信息
	 * @param unknown $rid				student_essay表的id
	 * @param unknown $content			恢复内容
	 * @param unknown $file				上传的文件
	 */
	public function aUpdateReply($rid, $content=""){
		try{
			$dbf = M("file");
			$dbr = M("student_essay");
			$dbf->startTrans();
			$dbr->startTrans();
			$replyData = array("replyer"=>self::getUserID(), "reply"=>$content, "replytime"=>date("Y-m-d H:i:s", time()));
			//先判断有没有文件
			if($_FILES["file"]){
				$info = self::uploadFiles();
				if(is_string($info)){
					self::ajaxError("文件上传失败，可能是文件后缀不对，请稍后再试");
					return;
				}
				if(self::uploadSaveFile($dbf, $info[0]["name"], $info[0]["path"], "ESSAY_TAG")===false){
					self::ajaxError("保存文件失败，请稍后再试，0x0001");
					return;
				}
				$replyData["replyfile"] = $dbf->getLastInsID();
			}
			//写入关系
			if($dbr->data($replyData)->where(array("id"=>$rid))->save() === false){
				self::ajaxError("保存内容失败，请稍后再试，0x0002");
				return;
			}
			$dbf->commit();
			$dbr->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误".$e->getMessage());
		}
	}
	
	/**
	 * 删除回复（教师）
	 * @param unknown $rid		学生——论文关系id
	 */
	public function aDeleteReply($rid){
		self::baseUpdate("student_essay", $rid, array("replyer"=>0, "reply"=>"", "replyfile"=>0), "删除回复失败，请稍后再试");
	}
	
	/**
	 * 删除文件
	 * @param unknown $rid		学生——论文关系id
	 */
	public function aDeleteReplyFile($rid){
		self::baseUpdate("student_essay", $rid, array("replyfile"=>0), "删除文件失败，请稍后再试");
	}
	
	/**
	 * 修改提交信息
	 * @param unknown $rid			学生——论文关系id
	 * @param string $content		内容
	 */
	public function aUpdateSubmit($rid, $content=""){
		try{
			$dbf = M("file");
			$dbr = M("student_essay");
			$dbf->startTrans();
			$dbr->startTrans();
			$replyData = array("desc"=>$content);
			//先判断有没有文件
			if($_FILES["file"]){
				$info = self::uploadFiles();
				if(is_string($info)){
					self::ajaxError("文件上传失败，可能是文件后缀不对，请稍后再试");
					return;
				}
				if(self::uploadSaveFile($dbf, $info[0]["name"], $info[0]["path"], "ESSAY_TAG")===false){
					self::ajaxError("保存文件失败，请稍后再试，0x0001");
					return;
				}
				$replyData["file"] = $dbf->getLastInsID();
			}
			//写入关系
			if($dbr->data($replyData)->where(array("id"=>$rid))->save() === false){
				self::ajaxError("保存内容失败，请稍后再试，0x0002");
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
	 * 删除提交的论文
	 * @param unknown $rid			学生——论文关系id
	 */
	public function aDeleteSubmit($rid){
		self::baseDelete("student_essay", $rid, "删除论文失败，请稍后再试");
	}
	
	/**
	 * 学生上传论文
	 * @param unknown $essay			论文id
	 * @param unknown $content			内容
	 */
	public function aCreateSubmit($essay, $content){
		try{
			$dbf = M("file");
			$dbr = M("student_essay");
			$dbf->startTrans();
			$dbr->startTrans();
			$replyData = array("essay"=>$essay, "desc"=>$content);
			//先判断有没有文件
			if($_FILES["file"]){
				$info = self::uploadFiles();
				if(is_string($info)){
					self::ajaxError("文件上传失败，可能是文件后缀不对，请稍后再试");
					return;
				}
				if(self::uploadSaveFile($dbf, $info[0]["name"], $info[0]["path"], "ESSAY_TAG")===false){
					self::ajaxError("保存文件失败，请稍后再试，0x0001");
					return;
				}
				$replyData["file"] = $dbf->getLastInsID();
			}
			//写入关系
			if($dbr->data($replyData)->add() === false){
				self::ajaxError("保存内容失败，请稍后再试，0x0002");
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
	 * 保存8个表
	 * @param unknown $essay		论文id
	 */
	public function aSaveTable8($essay){
		try{
			$db = M("table8");
			if($db->where(array("essay"=>$essay))->data($_POST)->save()===false){
				self::ajaxError("保存数据失败，请稍后再试");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 获取论文资料
	 * @param number $page			页码
	 * @param string $key			关键字
	 */
	public function gResources($page=0, $key=""){
		try{
			$perpage = self::getPerPage();
			//条件
			$where = array("enable"=>1);
			if($key != ""){
				$like = array("LIKE", "%$key%");
				$where[] = array("name"=>$like, "desc"=>$like, "_logic"=>"OR");
			}
			//查询
			$db = M("essay_resource");
			$datas = $db->where($where)->limit($page*$perpage, $perpage)->order("`name` ASC")->select();
			if($datas === false){
				self::ajaxError("获取资源数据失败，请稍后再试");
				return;
			}
			//分页数据
			$count = $db->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("pages"=>$pages, "count"=>$count, "resources"=>$datas));
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 删除资料
	 * @param unknown $id
	 */
	public function aDeleteResource($id){
		self::baseDelete("essay_resource", $id, "删除资料失败，请稍后再试。");
	}
	
	
	/**
	 * 修改资料
	 * @param unknown $rid		资料id
	 * @param unknown $name		资料名称
	 * @param unknown $desc		资料描述
	 */
	public function aUpdateResource($rid, $name, $desc){
		self::baseUpdate("essay_resource", $rid, array("name"=>$name, "desc"=>$desc), "修改资料信息失败，请稍后再试");
	}
	
	/**
	 * 上传资料
	 * @param unknown $name				资料名称
	 * @param unknown $desc				资料描述
	 */
	public function aCreateResource($name, $desc){
		try{
			$dbf = M("file");
			$dbr = M("essay_resource");
			$dbf->startTrans();
			$dbr->startTrans();
			//上传文件
			$info = self::uploadFiles();
			if(is_string($info)){
				self::ajaxError("文件上传错误，" . $info);
				return;
			}
			//保存文件数据
			$ids = self::uploadSaveFiles($dbf, $info, "ESSAY_RES_TAG");
			if($ids === false){
				self::ajaxError("保存数据错误，请稍后再试，0x0001");
				return;
			}
			$incId = $ids[0];
			//保存资料数据
			if($dbr->data(array("name"=>$name, "desc"=>$desc, "file"=>$incId))->add() === false){
				self::ajaxError("保存数据错误，请稍后再试，0x0002");
				return;
			}
			$dbf->commit();
			$dbr->commit();
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
