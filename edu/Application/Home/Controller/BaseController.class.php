<?php
/**
 * ==================================================
 * 					基本功能控制器
 * ==================================================
 */

namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Think\Upload;
use K360\MSG;
use K360\Timer;

class BaseController extends Controller{
	
	/**
	 * 判断用户是否登录
	 * @return boolean		如果用户已经登录返回true，否则返回false
	 */
	public function isLogin(){
		return session("edu-user") ? true : false;
	}
	
	/**
	 * 判断用户是不是教师
	 * @return boolean		如果是教师返回true，否则返回false
	 */
	public function isTeacher(){
		return (session("edu-user-type")=="teacher") ? true : false;
	}
	
	/**
	 * 判断用户是不是学生
	 * @return boolean		如果是学生返回true，否则返回false
	 */
	public function isStudent(){
		return (session("edu-user-type")=="student") ? true : false;
	}
	
	/**
	 * 获取当前用户的id
	 * @return boolean|String		如果用户已经登录则返回用户id，否则返回false
	 */
	public function getUserID(){
		$user = session("edu-user");
		if(!$user)
			return false;
		return $user["id"];
	}
	
	/**
	 * 获取用户类型
	 * @return string		student、teacher、other
	 */
	public function getUserType(){
		if(self::isStudent())
			return "student";
		if(self::isTeacher())
			return "teacher";
		else return "other";
	}
	
	/**
	 * 获取当前用户的姓名
	 * @return boolean|String		如果已经有用户登录则返回用户名字，否则返回false
	 */
	public function getUserName(){
		$user = session("edu-user");
		if(!$user)
			return false;
		return $user["name"];
	}
	
	/**
	 * 刷新用户信息
	 * @return boolean		刷新成功返回true，否则返回false
	 */
	public function refreshUser(){
		$id = self::getUserID();
		//用户没有登录返回false
		if($id === false){
			return false;
		}
		$table = self::isTeacher() ? "teacher" : (self::isStudent() ? "student" : null);
		//找不到表格
		if(!$table)
			return false;
		try{
			//查询用户
			$db = M($table);
			$user = $db->where(array("id"=>$id, "enable"=>1))->find();
			//查询失败返回false
			if($user === false)
				return false;
			//查询成功，添加session
			session("edu-user", $user);
			return true;
		}catch (\Exception $e){
			//查询出错返回false
			return false;
		}
	}
	
	/**
	 * 获取用户信息
	 * @return boolean|array		如果用户灭有登录返回false，否则返回用户信息
	 */
	public function getUserInfo(){
		$user = session("edu-user");
		if(!$user)
			return false;
		return $user;
	}
	
	/**
	 * 通过ajax返回成功数据
	 * @param string|array $data		成功数据
	 */
	public function ajaxSuccess($data){
		header("Content-type:application/json");
		echo json_encode(array("data"=>$data));
	}
	
	/**
	 * 通过ajax返回失败原因
	 * @param string $reson		失败原因
	 */
	public function ajaxError($reson){
		header("Content-type:application/json");
		echo json_encode(array("reson"=>$reson));
	}
	
	/**
	 * 分页时获取每页数量
	 * @param string $name		配置名称，如果不传则使用控制器，为名称，如果没有控制器名称则使用默认名称
	 * @return int 每页数量
	 */
	public function getPerPage($name=null){
		$conf = C("PAGE");
		if($name && isset($conf["_common"][$name]))
			return $conf["_common"][$name];
		if(isset($conf[CONTROLLER_NAME]))
			return $conf[CONTROLLER_NAME];
		return $conf["_default"];
	}
	
	/**
	 * 分页时获取总页数
	 * @param int $count			数据数量
	 * @param int $perpage			每页数量
	 * @return number		总页数
	 */
	public function getPages($count, $perpage){
		$pageNum = intval($count) / intval($perpage);
		$intPage = intval($pageNum);
		if($pageNum > $intPage)
			return  $intPage + 1;
		return $intPage;
	}
	
	/**
	 * 将字符串转换为utf8编码
	 * @param string $data		要转换的字符串
	 * @return string			编码后的字符串
	 */
	public function str2utf8($data){
		if( !empty($data) ){
			$fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5')) ;
			if( $fileType != 'UTF-8'){
				$data = mb_convert_encoding($data ,'utf-8' , $fileType);
			}
		}
		return $data;
	}
	
	/**
	 * 获取文件后缀
	 * @param string $file			文件名
	 * @return string			文件后缀，如果没有后缀返回""
	 */
	public function suff($file){
		for($i=strlen($file)-1; $i>=0; $i--){
			if($file[$i] == "."){
				return substr($file, $i+1);
			}
		}
		return "";
	}
	
	/**
	 * 获取文件除去后缀之后的部分
	 * @param string $file			文件名
	 * @return string		出去后缀后的部分
	 */
	public function nosuff($file){
		for($i=strlen($file)-1; $i>=0; $i--){
			if($file[$i] == "."){
				return substr($file, 0, $i);
			}
		}
		return $file;
	}
	
	
	//=====================================================基本数据库操作
	/**
	 * 获取所有年级，返回名称为name
	 */
	public function getGrades(){
		try{
			$db = M("class");
			$grades = $db->group("grade")->field(array("grade"=>"name"))->select();
			if($grades === false){
				self::ajaxError("获取年级失败");
				return ;
			}
			self::ajaxSuccess($grades);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取班级，返回名称为id，name
	 * @param string $grade		年级，如果不传此参数则获取所有班级
	 */
	public function getClasses($grade = null){
		$grade = intval($grade);
		try{
			$db = M("class");
			$where = array("enable"=>1);
			($grade>2000 && $grade<3000) && ($where["grade"] = $grade);
			$classes = $db->where($where)->field(array("id", "name"=>"basename", "CONCAT(`grade`, '级', `name`)"=>"name"))->order("`grade` DESC, `name` DESC")->select();
			if($classes === false){
				self::ajaxError("获取班级失败");
				return;
			}
			self::ajaxSuccess($classes);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 从某个表删除数据
	 * @param string $table				表名
	 * @param string|int $id			数据id
	 * @param string $errmsg			删除失败json返回的错误
	 * @param string $jsoned			是否返回json
	 * @return boolean					删除成功返回true，否则返回false
	 */
	public function baseDelete($table, $id, $errmsg="删除数据错误，请稍后再试", $jsoned=true){
		try{
			$db = M($table);
			if($db->where(array("id"=>$id))->data(array("enable"=>0))->save()==false){
				$jsoned && self::ajaxError($errmsg);
				return false;
			}
			$jsoned && self::ajaxSuccess(true);
			return true;
		}catch (\Exception $e){
			$jsoned && self::ajaxError("出现了未知错误");
			return false;
		}
	}
	
	/**
	 * 添加一条数据到某张表
	 * @param string $table			表名
	 * @param string $data				数据
	 * @param string $errmsg			添加失败错误提示
	 * @param string $jsoned			是否使用json返回数据
	 * @return boolean|string			添加失败返回false，添加成功返回添加的数据的id
	 */
	public function baseCreate($table, $data, $errmsg="添加数据错误，请稍后再试", $jsoned=true){
		try{
			$db = M($table);
			if($db->data($data)->add()===false){
				$jsoned && self::ajaxError($errmsg);
				return false;
			}
			$insid = $db->getLastInsID();
			$jsoned && self::ajaxSuccess($insid);
			return $insid;
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
			return false;
		}
	}
	
	/**
	 * 修改一条数据
	 * @param string $table			表名
	 * @param string|int $id		数据id
	 * @param array $data			数据内容
	 * @param string $errmsg		修改失败错误信息
	 * @param string $jsoned		是否使用json返回结果
	 * @return boolean				修改成功返回true，否则返回false
	 */
	public function baseUpdate($table, $id, $data, $errmsg="修改数据错误，请稍后再试", $jsoned = true){
		try{
			$db = M($table);
			if($db->where(array("id"=>$id))->data($data)->save()===false){
				$jsoned && self::ajaxError($errmsg);
				return false;
			}
			$jsoned && self::ajaxSuccess(true);
			return true;
		}catch (\Exception $e){
			$jsoned && self::ajaxError("出现了未知错误");
			return false;
		}
	}
	
	
	//========================================================下载文件
	/**
	 * 下载文件（最好以post方式提交文件id）
	 * @param array $fids			文件id数组
	 * @param string $name			下载名，可以使用%s格式化输出，如果使用%s则%s的位置将被原文件名代替
	 */
	public function pDownloadBase($ids, $name){
		try{
			set_time_limit(0);
			$db = M("file");
			$files = $db->where(array("id"=>array("IN", $ids)))->field(array("name", "path"))->select();
			if($files === false){
				echo "文件下载失败，请稍后再试";
				return;
			}
			//无文件
			if(count($files)<=0){
				echo "没有找到文件，要下载的文件不存在";
				return;
			}
			//单文件
			if(count($files)==1){
				$downloadName = sprintf($name, self::nosuff($files[0]["name"])) . "." . self::suff($files[0]["name"]);
				$downloadName = iconv("UTF-8", "GB2312", $downloadName);
				$path = "Public/". $files[0]["path"];
				$mime = mime_content_type($path);
				header("Content-type:" + $mime);
				header("Content-Disposition:attachment;filename=\"$downloadName\"");
				echo file_get_contents($path);
				return;
			}
			//多文件
			//1）压缩
			$downloadName = sprintf($name, "download " . date("Y-m-d H:i:s", time())) . ".zip";
			$downloadName = iconv("UTF-8", "GB2312", $downloadName);
			if(!file_exists("Public/cache")){
				mkdir("Public/cache");
			}
			$cachename = "Public/cache/" . md5(self::getUserName() . time()) . ".zip";
			$zip = new \ZipArchive();
			$zip->open($cachename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
			foreach ( $files as $file ){
				$zip->addFile("Public/". $file["path"], $file["name"]);
			}
			$zip->close();
			//2）下载
			$mime = mime_content_type($cachename);
			header("Content-type:" + $mime);
			header("Content-Disposition:attachment;filename=\"$downloadName\"");
			echo file_get_contents($cachename);
		}catch (\Exception $e){
			echo "出现未知错误";
		}
	}
	
	/**
	 * 文件上传后调用此方法保存文件
	 * @param Model $db			数据库模型对象
	 * @param string $name			文件名
	 * @param string $path			文件路径
	 * @param string $tag			文件标记
	 * @return boolean|Model		保存成功返回true，否则返回false
	 */
	public function uploadSaveFile(Model $db, $name, $path, $tag){
		try{
			$usertype = self::getUserType();
			$userid = self::getUserID();
			$tag = C("FILE.$tag");
			if($db->data(array("name"=>$name, "path"=>$path, "owner"=>$userid, "usertype"=>$usertype, "tag"=>$tag))->add()===false){
				return false;
			}
			return true;
		}catch (\Exception $e){
			return false;
		}
	}
	
	/**
	 * 上传文件
	 * @param array() $exts			允许的后缀
	 * @return string|array 		上传成功返回文件信息[{name:xxxx, path:xxxx}, ...]，上传失败返回失败原因。因此可以用is_string()来判断是否上传成功
	 */
	public function uploadFiles($exts = array(), $to=null){
		$upload = new Upload();
		$upload->maxSize = C("FILE_UPLOAD_MAX_SIZE");
		$upload->savePath = $to ? $to : C("FILE_UPLOAD_SAVE_PATH");
		$upload->exts = $exts;
		$upload->rootPath = C("FILE_UPLOAD_ROOT_PATH");
		$upload->replace = true;
		$info = $upload->upload();
		if(!$info) return $upload->getError();
		$finfo = array();
		foreach ($info as $file){
			$path = $file['savepath'].$file['savename'];
			$name = $file["name"];
			$finfo[] = array("name"=>$name, "path"=>$path);
		}
		return $finfo;
	}
	
	/**
	 * 
	 * 保存所有上传的文件
	 * @param Model $db					数据库模型
	 * @param array $info				文件信息，[{name:xxxx, path:xxxx}, ...]
	 * @param string $tag				标记
	 * @return boolean|multitype:		保存成功返回id数据数组，保存失败返回false
	 */
	public function uploadSaveFiles(Model $db, $info, $tag){
		$ids = array();
		foreach ($info as $f){
			if(!self::uploadSaveFile($db, $f["name"], $f["path"], $tag)){
				return false;
			}
			$ids[] = $db->getLastInsID();
		}
		return $ids;
	}
	
	//==============================================================服务操作
	
	/**
	 * 启动WS服务器，此方法不要调用，请调用autoRunWS
	 */
	public function runWSCB(){
		$sv = new MSG();
		$sv->run();
	}
	
	/**
	 * 启动WS服务器，如果已经启动则不启动
	 * @return number 0）服务已经启动，1)服务正在开启中，2）服务开启成功，-1）服务开启失败
	 */
	public function autoRunWS(){
		$sv = new MSG();
		$index = "http://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . __ROOT__ . "/index.php";
		$url = $index . "/Home/Base/runWSCB";
		$ret = $sv->autoCheck($url);
		return $ret;
	}
	
	/**
	 * 启动定时器服务，此方法不要调用，请调用autoRunTimer
	 */
	public function runTimerCB(){
		//10秒读取一次
		$timer = new Timer(10000);
		$index = "http://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . __ROOT__ . "/index.php";
		$url = $index . "/Home/Timer/timerCB";
		$timer->run($url);
	}
	
	/**
	 * 启动定时器服务
	 */
	public function autoRunTimer(){
		Timer::close();
		$index = "http://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . __ROOT__ . "/index.php";
		$url = $index . "/Home/Base/runTimerCB";
		Timer::autoStatr($url);
	}

	/**
	 * 关闭定时器
	 */
	public function closeTimer(){
		Timer::close();
	}
	
}