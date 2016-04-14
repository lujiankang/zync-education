<?php
namespace Home\Controller;

use K360\ExpDoc\ExamPaperDoc;
class ExamController extends BaseController{
	/**
	 * 提醒管理主页
	 */
	public function pTypes(){
		$this->display("types");
	}
	
	/**
	 * 考试科目管理主页
	 */
	public function pSubject (){
		$this->display("subject");
	}
	
	/**
	 * 题目管理主页
	 */
	public function pTopic(){
		//获取所有题目类型
		$dbs = M("exam_subject");
		$subjects = $dbs->where(array("enable"=>1))->field(array("id", "name"))->select();
		//获取所有题型
		$dbt = M("exam_type");
		$types = $dbt->where(array("enable"=>1))->field(array("id", "name"))->select();
		$this->assign("subjects", $subjects);
		$this->assign("types", $types);
		$this->display("topic");
	}
	
	
	/**
	 * 试卷中学主页
	 */
	public function pPaper(){
		$years = array();
		$now = date("Y", time());
		for($i=2014; $i<=$now; $i++){
			$years[] = array("name"=>$i, "select"=>($i==$now)?"selected":"");
		}
		$this->assign("years", $years);
		$this->display("paper");
	}
	
	/**
	 * 试卷设计主页
	 */
	public function pDesign(){
		//获取所有科目及题型
		$subjects = M("exam_subject")->where(array("enable"=>1))->select();
		$types = M("exam_type")->where(array("enable"=>1))->select();
		$this->assign("subjects", $subjects);
		$this->assign("types", $types);
		$this->assign("json_type", json_encode($types));
		$this->display("design");
	}
	
	/**
	 * 记分册主界面
	 */
	public function pScore(){
		echo "<h2>Nothing has done! This is only a null module.</h2>";
	}
	
	
	/**
	 * 获取所有题型
	 */
	public function gTypes() {
		$db = M("exam_type");
		$types = $db->where(array("enable"=>1))->select();
		if($types === false){
			self::ajaxError("获取题型列表失败，请稍后再试。");
			return;
		}
		self::ajaxSuccess($types);
	}
	
	/**
	 * 创建一类题型
	 * @param unknown $name		提醒名称
	 * @param unknown $desc		题型描述
	 */
	public function aCreateType($name, $desc){
		self::baseCreate("exam_type", array("name"=>$name, "desc"=>$desc), "创建题型失败，请稍后再试");
	}
	
	/**
	 * 修改一类题型
	 * @param unknown $tid			要修改的题型的id
	 * @param unknown $name			题型名称
	 * @param unknown $desc			题型描述
	 */
	public function aUpdateType($tid, $name, $desc) {
		self::baseUpdate("exam_type", $tid, array("name"=>$name, "desc"=>$desc), "修改题型信息失败，请稍后再试");
	}
	
	/**
	 * 删除一类题型
	 * @param unknown $id		要删除的题型的id
	 */
	public function aDeleteType($id){
		self::baseDelete("exam_type", $id, "删除题型失败，请稍后再试");
	}
	
	
	
	
	
	/**
	 * 获取科目列表
	 * @param number $page		分页页码
	 */
	public function gSubjects ( $page = 0 ) {
		try{
			$perpage = self::getPerPage();
			$db = M("exam_subject");
			$subjects = $db->where( array("enable"=>1) )->limit( $page * $perpage, $perpage ) ->select();
			if($subjects === false){
				self::ajaxError("获取科目信息失败，请稍后再试");
				return;
			}
			$count = $db->where( array("enable"=>1) )->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess( array("subjects"=>$subjects, "pages"=>$pages, "count"=>$count) );
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 删除科目
	 * @param unknown $id		要删除的科目的id
	 */
	public function aDeleteSubject($id){
		self::baseDelete("exam_subject", $id, "删除科目失败，请稍后再试");
	}
	
	/**
	 * 添加科目
	 * @param unknown $name		科目名称
	 * @param unknown $desc		科目描述
	 */
	public function aCreateSubject($name, $desc){
		self::baseCreate("exam_subject", array("name"=>$name, "desc"=>$desc), "添加科目信息失败，请稍后再试");
	}
	
	/**
	 * 修改科目
	 * @param unknown $sid			要修改的科目id
	 * @param unknown $name			科目名称
	 * @param unknown $desc			科目描述
	 */
	public function aUpdateSubject($sid, $name, $desc){
		self::baseUpdate("exam_subject", $sid, array("name"=>$name, "desc"=>$desc), "修改科目信息失败，请稍后再试");
	}
	
	
	
	
	
	/**
	 * 获取题目列表
	 * @param unknown $subject		科目id
	 * @param number $page			页码
	 * @param string $key			关键字
	 */
	public function gTopics($subject, $page=0, $key=""){
		try{
			$perpage = self::getPerPage("ExamTopicList");
			$where = array("`exam_topic`.`enable`"=>1);
			if($key != ""){
				$like = array("LIKE", "%$key%");
				$where[] = array(
					"_logic"				=>	"OR",
					"`exam_topic`.`text`"	=>	$like
				);
			}else{
				$where["`exam_topic`.`subject`"] = $subject;
			}
			
			$db = M("exam_topic");
			
			$db->join("LEFT JOIN `teacher` ON `teacher`.`id` = `exam_topic`.`user`");
			$db->join("LEFT JOIN `exam_subject` ON `exam_subject`.`id` = `exam_topic`.`subject`");
			$db->join("LEFT JOIN `exam_type` ON `exam_type`.`id` = `exam_topic`.`type`");
			$db->field(array(
				"`exam_topic`.`id`",
				"`exam_topic`.`text`",
				"`exam_topic`.`answer`",
				"`teacher`.`name`"			=>	"uname",
				"`exam_subject`.`name`"		=>	"sname",
				"`exam_type`.`name`"		=>	"tname",
				"`exam_topic`.`subject`",
				"`exam_topic`.`type`"
			));
			$topics = $db->where($where)->limit($page*$perpage, $perpage)->select();
			$count = $db->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("topics"=>$topics, "pages"=>$pages, "count"=>$count));
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	} 
	
	/**
	 * 创建题目
	 * @param unknown $subject		科目
	 * @param unknown $type			题目类型
	 * @param unknown $text			题目内容
	 * @param string $answer		题目答案
	 */
	public function aCreateTopic($subject, $type, $text, $answer=""){
		self::baseCreate("exam_topic", array("text"=>$text, "type"=>$type, "subject"=>$subject, "answer"=>$answer, "user"=>self::getUserID()), "添加题目失败，请稍后再试");
	}
	
	/**
	 * 导入题目
	 * @param unknown $subject		科目
	 * @param unknown $datas		题目数据，如（[{items:[{data:"题目内容", answer:"题目答案"}, ...], type:"填空题"}, ...]）
	 */
	public function aImportTopics($subject, $datas){
		$datas = json_decode($datas, true);
		try{
			//查询题型
			$dbt = M("exam_type");
			$db = M("exam_topic");
			$types = $dbt->where(array("enable"=>1))->field(array("id", "name"))->select();
			if($types === false){
				self::ajaxError("导入数据错误，请稍后再试，0x0001");
				return;
			}
			//查询相同题目
			$ttexts = array();
			foreach ($datas as $data){
				foreach ($data["items"] as $item){
					$ttexts[] = $item["data"];
				}
			}
			$exists = $db->where(array("enable"=>1, "subject"=>$subject, "text"=>array("IN", $ttexts)))->select();
			//过滤数据，同时生成插入数据
			$added = array();
			foreach ($datas as $data){
				$isInType = false;
				$tid = 0;
				foreach ($types as $type){
					if($data["type"] == $type["name"]){
						$tid =  $type["id"];
						$isInType = true;
						break;
					}
				}
				if(!$isInType) continue;
				//生成数据
				foreach ($data["items"] as $item){
					$isExisst = false;
					foreach ($exists as $ev){
						if($ev["text"] == $item["data"]){
							$isExisst = true;
							break;
						}
					}
					if($isExisst) continue;
					$added[] = array("text"=>$item["data"], "answer"=>$item["answer"], "user"=>self::getUserID(), "type"=>$tid, "subject"=>$subject);
				}
			}
			//判断数据条数
			$sucCount = count($added);
			if($sucCount==0){
				self::ajaxSuccess(0);
				return;
			}
			//写入数据库
			if($db->addAll($added) === false){
				self::ajaxError("导入数据错误，请稍后再试，0x0002");
				return;
			}
			self::ajaxSuccess($sucCount);
		}catch (\Exception $e){
			echo $e->getMessage();
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 删除题目
	 * @param unknown $id		要删除的题目的id
	 */
	public function aDeleteTopic($id){
		self::baseDelete("exam_topic", $id, "删除题目失败，请稍后再试");
	}
	
	/**
	 * 修改题目信息
	 * @param unknown $tid			要修改的题目的id
	 * @param unknown $subject		科目
	 * @param unknown $type			题目类型
	 * @param unknown $text			题目内容
	 * @param string $answer		题目答案
	 */
	public function aUpdateTopic($tid, $subject, $type, $text, $answer=""){
		self::baseUpdate("exam_topic", $tid, array("text"=>$text, "type"=>$type, "subject"=>$subject, "answer"=>$answer, "user"=>self::getUserID()), "修改题目失败，请稍后再试");
	}
	
	
	
	
	
	/**
	 * 获取题型数目
	 * @param unknown $subject			科目
	 * @param unknown $types			题型数组
	 */
	public function gTypeNumber($subject, $types){
		try{
			$db = M("exam_topic");
			$datas = $db->where(array("subject"=>$subject, "type"=>array("IN", $types)))->field(array("type", "COUNT(*)"=>"count"))->group("type")->select();
			if($datas === false){
				self::ajaxError("获取题目数量错误，请稍后再试");
				return;
			}
			self::ajaxSuccess($datas);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 生成试卷
	 * @param unknown $subject			科目
	 * @param unknown $types			题目类型
	 * @param unknown $numbers			各个类型的题目数量
	 */
	public function gBuildPaper($subject, $types, $numbers){
		try{
			//查询各类题目的id，并生成随机id
			$idbuffer = array();			//[[xx, xx, xx, ...], ...]
			$db = M("exam_topic");
			//$notshuch = false;				//标识是否有足够的题目
			foreach ($types as $i=>$type){
				//查询数据
				$ids = $db->where(array("enable"=>1, "type"=>$type, "subject"=>$subject))->field(array("id"))->select();
				$aids = array();
				//转换成id数组
				foreach ($ids as $id){
					array_push($aids, $id["id"]);
				}
				$randIds = self::_getRand($aids, $numbers[$i]);
				//数量不足
// 				if(count($randIds) < $numbers[$i]){
// 					$notshuch = true;
// 				}
				//放入id数组
				foreach($randIds as $rv){
					array_push($idbuffer, $rv);
				}
			}
			if(count($idbuffer)==0){
				self::ajaxError("没有数据");
				return;
			}
			//查询题目
			$topics = $db->where(array("id"=>array("IN", $idbuffer)))->select();
			if($topics === false){
				self::ajaxError("查询题目错误，请稍后再试");
				return;
			}
			self::ajaxSuccess($topics);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误".$e->getMessage());
		}
	}
	
	public function aSavePaper($name, $course, $opened, $caculatored, $examtime, $grade, $html, $paperid=""){
		$data = array(
			"name"			=>	$name,
			"user"			=>	self::getUserID(),
			"course"		=>	$course,
			"opened"		=>	$opened,
			"caculatored"	=>	$caculatored,
			"examtime"		=>	$examtime,
			"grade"			=>	$grade,
			"html"			=>	$html
		);
		
		if($paperid==""){
			self::baseCreate("exam_paper", $data, "保存试卷错误，请稍后再试");
		}else {
			self::baseUpdate("exam_paper", $paperid, $data, "保存试卷错误，请稍后再试");
		}
	}
	
	
// 	public function test(){
// 		$html = M("exam_paper")->field("html")->find()["html"];
// 		$obj = json_decode($html, true);
// 		$doc = new ExamPaperDoc();
// //		$doc->setBaseInfo("闭", , $caculator, $examtime, $papername, $grade)
// 		foreach ($obj as $v){
// 			if($v["type"] == "typename"){
// 				$doc->addType($v["text"]);
// 			}else{
// 				$doc->addText($v["text"]);
// 			}
// 		}
// 		$doc->save("1.doc");
// 	}
	
	
	/**
	 * 从指定的数据中随机获取num条
	 * @param unknown $datas			数据
	 * @param unknown $num				获取条数
	 * @return boolean|multitype:		随机的num条数据，如果不足全部返回
	 */
	private function _getRand($datas, $num){
		//数量太少直接返回
		$len = count($datas);
		if($len <= $num)
			return $datas;
		$numbers = [];
		//一直往numbers里填充数据，直到获取完所有目标数据
		while(true){
			//获取随机index
			$i = rand(0, $len-1);
			//随机数据
			$dn = $datas[$i];
			//判断该数据是否已经存在于numbers中
			$haded = false;
			foreach($numbers as $v){
				if($dn == $v){
					$haded = true;
					continue;
				} 
			}
			//如果没有存在则存入numbers中
			if(!$haded) array_push($numbers, $datas[$i]);
			//已经获取到了指定的数据条数，结束循环
			if(count($numbers) == $num) break;
		}
		//返回数据
		return $numbers;
	}
	
	
	
	public function gMyPapers($page, $year){
		try{
			$perpage = self::getPerPage();
			$db = M("exam_paper");
			$where = array("enable"=>1, "user"=>self::getUserID(), "YEAR(`time`)"=>$year);
			$papers = $db->where($where)
				->field(array("id", "name", "course", "grade", "time"))
				->limit($perpage*$page, $perpage)
				->select();
			if($papers === false){
				self::ajaxError("获取试卷失败，请稍后再试");
				return;
			}
			foreach ($papers as $i => $paper){
				$time = strtotime($paper["time"]);
				$month = intval(date("m",  $time));
				$year = intval(date("Y", $time));
				$year = ($month>=7) ? $year : ($year-1);
				$papers[$i]["terms"] = $year . "~" . ($year+1) . "学年度第" . (($month>=7) ? "一" : "二") . "学期";
			}
			$count = $db->where($where)->count();
			$pages = self::getPages($count, $perpage);
			self::ajaxSuccess(array("papers"=>$papers, "pages"=>$pages, "count"=>$count));
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	
	public function aDeletePaper($id){
		self::baseDelete("exam_paper", $id, "删除试卷失败，请稍后再试");
	}
	
	
	public function gPaperHTML($id){
		try{
			$paper = M("exam_paper")->where(array("id"=>$id))->find();
			if($paper === false){
				self::ajaxError("获取内容失败，请稍后再试");
				return;
			}
			$time = strtotime($paper["time"]);
			$month = intval(date("m",  $time));
			$year = intval(date("Y", $time));
			$year = ($month>=7) ? $year : ($year-1);
			$paper["termnum"] = $year . "~" . ($year+1);
			$paper["termtext"] = ($month>=7) ? "一" : "二";

			$paper["html"] = json_decode($paper["html"], true);
			self::ajaxSuccess($paper);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	public function pDownloadPaper($id){
		$paper = M("exam_paper")->where(array("id"=>$id))->find();
		$html = $paper["html"];
		$obj = json_decode($html, true);
		$doc = new ExamPaperDoc();
		$doc->setBaseInfo($paper["opened"], $paper["course"], $paper["caculatored"], $paper["examtime"], $paper["name"], $paper["grade"]);
		foreach ($obj as $v){
			if($v["type"] == "typename"){
				$doc->addType($v["text"]);
			}else{
				$doc->addText($v["text"]);
			}
		}
		$doc->save();
	}
	
}