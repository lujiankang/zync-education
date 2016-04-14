<?php
namespace Home\Controller;

use Think\Image;
class SubjectController extends BaseController{
	
	/**
	 * 显示主界面
	 */
	public function pMain(){
		$this->display("main");
	}
	
	
	/**
	 * 获取所有科目
	 */
	public function gSubjects(){
		try{
			$db = M("subject");
			$data = $db
			->join("`teacher` ON `teacher`.`id` = `subject`.`user`")
			->where(array("`subject`.`enable`"=>1))
			->field(array(
				"`subject`.*",
				"`teacher`.`name`"		=>	"username",
				"`teacher`.`number`"	=>	"usernumber",
				"'teacher'"				=>	"usertype"
			))
			->select();
			if($data === false){
				self::ajaxError("获取科目列表失败，请稍后再试");
				return;
			}
			//json解码
			foreach ($data as $i=>$v){
				$data[$i]["chapter"] = json_decode($v["chapter"], true);
				$data[$i]["chapter"] || ($data[$i]["chapter"] = array());
			}
			self::ajaxSuccess($data);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 获取科目封面
	 * @param unknown $id		科目id
	 */
	public function gSubjectFace($id){
		$path = "Public/res/subject-face/" . $id . ".png";
		if(!file_exists($path)){
			$path = "Public/res/subject-face/default.png";
		}
		header("Content-type: image/png");
		readfile($path);
	}
	
	/**
	 * 创建科目
	 * @param unknown $name
	 * @param string $desc
	 */
	public function aCreateSubject($name, $desc=""){
		self::baseCreate("subject", array("name"=>$name, "desc"=>$desc, "user"=>self::getUserID()), "添加科目失败，请稍后再试");
	}
	
	/**
	 * 修改科目
	 * @param unknown $sid
	 * @param unknown $name
	 * @param string $desc
	 */
	public function aUpdateSubject($sid, $name, $desc=""){
		self::baseUpdate("subject", $sid, array("name"=>$name, "desc"=>$desc), "修改科目失败，请稍后再试");
	}
	
	/**
	 * 删除科目
	 * @param unknown $id
	 */
	public function aDeleteSubject($id){
		self::baseDelete("subject", $id, "删除科目失败，请稍后再试");
	}
	
	
	/**
	 * 保存章节
	 * @param unknown $id
	 * @param string $chapter
	 */
	public function aSaveChapter($id, $chapter=""){
		self::baseUpdate("subject", $id, array("chapter"=>$chapter), "保存章节失败，请稍后再试");
	}
	
	/**
	 * 设置科目封面
	 * @param unknown $id
	 */
	public function aSetFace($id){
		$f = $_FILES["face"];
		//判断是否有文件
		if(!$f){
			self::ajaxError("上传封面错误，无文件");
			return;
		}
		$mime = strtolower($f["type"]);
		$temp = $f["tmp_name"];
		//判断mime
		if($mime!="image/bmp" && $mime!="image/pjpeg" && $mime!="image/png" && $mime!="image/jpeg"){
			self::ajaxError("请上传png、jpg或bmp图片作为封面");
			return;
		}
		//图片剪裁
		$img = new Image(Image::IMAGE_GD, $temp);
		$iw = $img->width();
		$ih = $img->height();
		$is = ($iw > $ih) ? $ih : $iw;		//取最小值以便剪裁
		//剪裁
		$img = $img->thumb(520, 520);//->crop($is, $is);
		//缩放
		if($is>256){
			$img = $img->thumb(256, 256);
		}
		$path = "Public/res/subject-face/" . $id . ".png";
		$img->save($path);
		self::ajaxSuccess(true);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}