<?php
namespace Home\Controller;

class AttendController extends BaseController{
    
    
	public function aCreateAttend($course, $chapter, $attend){
		try{
			$attend = json_decode($attend, true);
			$dba = M("attend");
			$dbsa = M("student_attend");
			$dba->startTrans();
			$dbsa->startTrans();
			//创建考勤
			if(false === $dba->data(array("course"=>$course, "chapter"=>$chapter))->add()){
				self::ajaxError("创建考勤记录失败，0x0001");
				return;
			}
			$attendId = $dba->getLastInsID();
			//填入考勤信息
			$data = array();
			foreach ($attend as $v){
				array_push($data, array("attend"=>$attendId, "student"=>$v["stu"], "status"=>$v["stat"]));
			}
			if(count($data)>0){
				if(false === $dbsa->addAll($data)){
					self::ajaxError("创建考勤记录失败，0x0002");
					return;
				}
			}
			$dba->commit();
			$dbsa->commit();
			self::ajaxSuccess(true);
        
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	
}