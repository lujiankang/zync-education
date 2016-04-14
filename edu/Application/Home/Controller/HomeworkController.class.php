<?php
namespace Home\Controller;

class HomeworkController extends BaseController{
	
	
	/**
	 * 上传图片到作业中
	 */
	public function aUploadImage(){
		try{
			$infos = self::uploadFiles(array("jpg", "jpeg", "png"));
			if(is_string($infos)){
				self::ajaxError("上传文件失败");
				return;
			}
			$names = array();
			foreach ($infos as $info){
				array_push($names, $info["path"]);
			}
			self::ajaxSuccess($names);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	
	public function aSaveHomeWork($course, $content, $chapter){
		self::baseCreate("homework", array(
			"course"	=>	$course,
			"chapter"	=>	$chapter,
			"content"	=>	$content
		), "布置作业失败，请稍后再试");
	}
	
	
	public function gHomeworks($course){
		
	}
	
	
	
}