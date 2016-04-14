<?php
namespace Home\Controller;

class FAQController extends BaseController{
	
	
	public function aUploadToFAQ(){
		try{
			$ret = self::uploadFiles(array(), "res/faq/");
			if(is_string($ret)){
				self::ajaxError($ret);
				return ;
			}
			self::ajaxSuccess($ret[0]["path"]);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	public function gFAQ($course, $page=0){
		//查询所有的FAQ
		$db = M("faq");
		$dbr = M("faq_reply");
		$faqs = $db
		->join("student ON faq.student = student.id")
		->where(array("course"=>$course, "`faq`.`enable`"=>1))
		->field(array(
			"`faq`.`id`", 
			"student",
			"`student`.`name`"=>"studentname",
			"text",
			"time"
		))->order("`faq`.`time` DESC")->select();
		if($faqs === false){
			self::ajaxError("获取FAQ失败，请稍后再试");
			return;
		}
		//查询回复
		foreach($faqs as $i=>$v){
			$replies = $dbr
			->join("`teacher` ON `teacher`.`id` = `faq_reply`.`teacher`")
			->where(array("faq"=>$v["id"], "`faq_reply`.`enable`"=>1))
			->field(array(
				"teacher"			=>	"teacher", 
				"`teacher`.`name`"	=>	"teachername",
				"text",
				"time",
				"moreask"			=>	"ismoreask"
			))->order("`time` ASC")->select();
			if($replies === false){
				self::ajaxError("获取FAQ失败，请稍后再试");
				return;
			}
			//分析回复数据（一个问题下可能有多个老师回复，所以要对回复进行区分）
			$buffer = array();
			foreach($replies as $reply){
				$teacher = $reply["teacher"];
				if(!$buffer[$teacher]) $buffer[$teacher] = array();
				array_push($buffer[$teacher], $reply);
			}
			$buffer2 = array();
			foreach($buffer as $bf){
				array_push($buffer2, $bf);
			}
			$replies || ($replies = array());
			$faqs[$i]["replies"] = $buffer2;
		}
		self::ajaxSuccess($faqs);
	}
	
	public function aCreate($text, $course){
		self::baseCreate("faq", array(
			"student"	=>	self::getUserID(),
			"text"		=>	$text,
			"course"	=>	$course
		), "问题提交失败，请稍后再试");
	}
	
	public function aReply($faq, $teacher, $text, $moreask){
		self::baseCreate("faq_reply", array(
			"faq"		=>	$faq,
			"teacher"	=>	$teacher,
			"text"		=>	$text,
			"moreask"	=>	$moreask
		), "操作失败，请稍后再试");
	}
	
	
	
	
	
	
	
	
	
}