<?php
namespace Home\Controller;

use Think\Image;
use K360\MailSender\SendMail;
class UserController extends BaseController{
	
	/**
	 * 教师管理界面
	 */
	public function pTeacher(){
		$this->display("teacher");
	}
	
	public function pUserCtrl(){
		$this->assign("user", self::getUserInfo());
		$this->display("ctrl");
	}
	
	/**
	 * 学生管理界面
	 */
	public function pStudent(){
		//查询班级
		$db = M("class");
		$classes = $db->where(array("enable"=>1))->field(array("id", "CONCAT(`grade`, '级', `name`)"=>"name"))->select();
		$this->assign("classes", $classes);
		$this->display("student");
	}
	
	/**
	 * 用户登录接口
	 * @param unknown $number			用户账号
	 * @param unknown $password			用户密码
	 * @param string $type				用户类型，teacher、student，默认自动检测，如果检测到两个相同的账号，使用ajaxSuccess返回false
	 */
	public function aLogin($number, $password, $type=null){
		//查询前
		$dbt = M("teacher");
		$dbs = M("student");
		$where = array("number"=>$number, "password"=>strtoupper(md5($password)), "enable"=>1);
		//查询
		$teacher = $dbt->where($where)->find();
		$student = $dbs->where($where)->find();
		if($teacher===false || $student === false){
			self::ajaxError("出现了未知错误");
			return;
		}
		//数据分析=======
		//是否双重身份
		if($teacher && $student && count($teacher)>0 && count($student)>0){
			//双重身份
			if($type && ($type=="teacher" || $type=="student")){
				//身份已指定
				$info = ($type=="teacher") ? $teacher : $student;
				self::_afterLogin($info, $type);
				self::ajaxSuccess(true);
				return;
			}
			//未知身份
			self::ajaxSuccess(false);
			return;
		}
		//如果是教师
		if($teacher && count($teacher)>0){
			self::_afterLogin($teacher, "teacher");
			self::ajaxSuccess(true);
			return ;
		}
		//如果是学生
		if($student && count($student)>0){
			self::_afterLogin($student, "student");
			self::ajaxSuccess(true);
			return ;
		}
		self::ajaxError("账号或密码错误");
	}
	
	//登录完成后保存用户session
	private function _afterLogin($user, $type){
		$user["type"] = $type;
		session("edu-user", $user);
		session("edu-user-type", $type);
	}
	
	/**
	 * 获取个人信息
	 */
	public function gMyInfo(){
		$uid = self::getUserID();
		$type = self::getUserType();
		$db = M($type);
		$info = $db->where(array("id"=>$uid))->field(array(
			"id",
			"number",
			"name",
			"sex",
			"tel",
			"qq",
			"email",
			"levo",
			"'$type' AS `type`"
		))->find();
		if($info === false){
			self::ajaxError("获取您的用户信息错误，请稍后再试");
			return;
		}
		self::ajaxSuccess($info);
	}
	
	/**
	 * 通过用户id和类型获取姓名
	 * @param unknown $id
	 * @param unknown $type
	 */
	public function gUserName($id, $type){
		try{
			$user = M($type)->where(array("id"=>$id))->find();
			if($user === false){
				self::ajaxError("获取用户姓名失败，请稍后再试");
				return;
			}
			self::ajaxSuccess($user["name"]);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取用户信息
	 * @param unknown $id			用户id
	 * @param unknown $type			用户类型
	 */
	public function gUserInfo($id, $type){
		try{
			$db = M($type);
			$where = array("`$type`.`id`"=>$id);
			$rtype = ($type=="student") ? "学生" : "教师";
			$fields = array("`$type`.`id`", "`$type`.`number`", "`$type`.`sex`", "`$type`.`name`", "`$type`.`tel`", "`$type`.`qq`", "`$type`.`email`", "'$rtype' AS `type`");
			if($type=="student"){
				$fields[] = "CONCAT(`class`.`grade`, '级', `class`.`name`) AS `classname`";
				$db->join("LEFT JOIN `class` ON `class`.`id` = `student`.`class`");
			}
			else if($type == "teacher"){
				$fields[] = "`role`.`name` AS `rolename`";
				$db->join("LEFT JOIN `role` ON `role`.`id` = `teacher`.`role`");
			}
			$user = $db->where($where)->field($fields)->find();
			if($user === false){
				self::ajaxError("获取用户信息错误，请稍后再试");
				return;
			}
			self::ajaxSuccess($user);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 获取用户头像
	 * @param unknown $type		用户类型
	 * @param unknown $id		用户id
	 */
	public function gHead($type, $id){
		$path = "Public/res/head/" . $type . "_" . $id . ".png";
		if(!file_exists($path)){
			$path = "Public/res/head/head.png";
		}
		header("Content-type: image/png");
		readfile($path);
	}
	
	/**
	 * 设置用户头像
	 * @param unknown $type			用户类型
	 * @param unknown $id			用户id
	 */
	public function aSetHead($type, $id){
		$f = $_FILES["head"];
		//判断是否有文件
		if(!$f){
			self::ajaxError("上传头像错误，无文件");
			return;
		}
		$mime = strtolower($f["type"]);
		$temp = $f["tmp_name"];
		//判断mime
		if($mime!="image/bmp" && $mime!="image/pjpeg" && $mime!="image/png" && $mime!="image/jpeg"){
			self::ajaxError("请上传png、jpg或bmp图片作为头像");
			return;
		}
		//图片剪裁
		$img = new Image(Image::IMAGE_GD, $temp);
		$iw = $img->width();
		$ih = $img->height();
		$is = ($iw > $ih) ? $ih : $iw;		//取最小值以便剪裁
		//剪裁
		$img = $img->crop($is, $is);
		//缩放
		if($is>256){
			$img = $img->thumb(256, 256);
		}
		$path = "Public/res/head/" . $type . "_" . $id . ".png";
		$img->save($path);
		self::ajaxSuccess(true);
	}
	
	
	/**
	 * 添加一个教师
	 * @param unknown $number		教师编号
	 * @param unknown $name			教师姓名
	 * @param unknown $role			教师的角色
	 */
	public function aCreateTeacher($number, $name, $role=null){
		try{
			$db = M("teacher");
			//判断教师是否已经存在
			if($db->where(array("number"=>$number, "enable"=>1))->count()>0){
				self::ajaxError("该教师编号已经存在");
				return;
			}
			$data = array("number"=>$number, "name"=>$name, "role"=>$role, "password"=>strtoupper(md5(C("DEFAULT_PWD"))));
			if($db->data($data)->add() === false){
				self::ajaxError("添加教师失败了");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	
	/**
	 * 添加一个学生
	 * @param unknown $number		学生学号
	 * @param unknown $name			学生姓名
	 * @param unknown $grade		学生班级
	 */
	public function aCreateStudent($number, $name, $class){
		try{
			$db = M("student");
			//查看学生是否已经存在
			if($db->where(array("number"=>$number, "enable"=>1))->count()>0){
				self::ajaxError("该学生编号已经存在");
				return;
			}
			if($db->data(array("number"=>$number, "name"=>$name, "class"=>$class, "password"=>strtoupper(md5(C("DEFAULT_PWD")))))->add() === false){
				self::ajaxError("添加学生失败了");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 批量导入学生
	 * @param unknown $class			班级
	 * @param unknown $names			姓名数组
	 * @param unknown $numbers			学号数组
	 * @param unknown $sexs				性别数组
	 */
	public function aCreateStudents($class, $names, $numbers, $sexs){
		try{
			//检查个数是否相等
			if(count($names) != count($numbers)){
				self::ajaxError("学生信息不正确，请检查学生信息并重新导入");
				return;
			}
			//生成数据
			$addData = array();
			foreach ($numbers as $i=>$number){
				$name = $names[$i];
				$sex = $sexs[$i];
				$addData[] = array("class"=>$class, "name"=>$name, "number"=>$number, "sex"=>$sex);
			}
			//保存数据
			if(M("student")->addAll($addData) === false){
				self::ajaxError("添加学生失败，请稍后再试");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 发送文本邮件
	 * @param unknown $type		用户类型
	 * @param unknown $id		用户id
	 * @param unknown $str		邮件内容
	 */
	public function aSendTextMail($type, $id, $content){
		try{
			//查询邮箱
			$db = M($type);
			$data = $db->where(array("id"=>$id))->field(array("email"))->find();
			if($data === false){
				self::ajaxError("邮件发送失败，请稍后再试，0x0001");
				return;
			}
			if(!$data["email"] || $data["email"]==""){
				self::ajaxError("该用户没有设置邮箱");
				return;
			}
			$mail = new SendMail("email_conf");
			$mail->addMail($data["email"], C("MAIL_SUBJECT"), $content);
			$ret = $mail->send();
			if(count($ret)>0){
				self::ajaxError("邮件发送失败，请稍后再试，0x0002");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	/**
	 * 获取学生
	 * @param unknown $page		页码
	 * @param unknown $class	学生班级
	 */
	public function gStudent($page=0, $class, $key=""){
		try{
			//分页数量
			$page = intval($page);
			$perPage = self::getPerPage();
			$where = array("`student`.`class`"=>$class, "`student`.`enable`"=>1);
			if($key!=""){
				$where = array(
					"`student`.`enable`"=>1,
					array(
						"`student`.`name`"								=>	array("LIKE", "%$key%"),
						"`student`.`number`"							=>	array("LIKE", "%$key%"),
						"CONCAT(`class`.`grade`, '级', `class`.`name`)"	=>	array("LIKE", "%$key%"),
						"_logic"	=>	"OR"
				));
			}
			//查询
			$db = M("student");
			$datas = $db->join("`class` ON `class`.`id` = `student`.`class`")
			->where($where)->limit($page*$perPage, $perPage)
			->field(array(
				"`student`.*",
				"CONCAT(`class`.`grade`, '级', `class`.`name`)"	=>	"classname"
			))->select();		//查询数据
			if($datas === false){
				self::ajaxError("查询数据失败");
				return;
			}
			foreach ($datas as $i=>$v){
				$datas[$i]["type"] = "student";
			}
			$count = $db->join("`class` ON `class`.`id` = `student`.`class`")->where($where)->count();			//数量
			$pages = self::getPages($count, $perPage);			//总页数
			$ret = array("count"=>$count, "pages"=>$pages, "datas"=>$datas);
			self::ajaxSuccess($ret);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误". $e->getMessage());
		}
	}	
	
	/**
	 * 获取教师
	 * @param unknown $page		页码
	 * @param unknown $key		关键字，如果传入，则通过关键字获取
	 */
	public function gTeacher($page, $key=null){
		try{
			//分页信息
			$page = intval($page);
			$perPage = self::getPerPage();
			//查询
			$db = M("teacher");
			$where = array("enable"=>1, array(
					"name"		=>	array("LIKE", "%$key%"),
					"number"	=>	array("LIKE", "%$key%"),
					"_logic"	=>	"OR"
			));
			$datas = $db
			->where($where)
			->limit($page*$perPage, $perPage)
			->select();		//数据
			if($datas === false){
				self::ajaxError("查询数据失败");
				return ;
			}
			foreach ($datas as $i=>$v){
				$datas[$i]["type"] = "teacher";
			}
			$count = $db->where($where)->count();		//数量
			$pages = self::getPages($count, $perPage);		//总页数
			$ret = array("count"=>$count, "pages"=>$pages, "datas"=>$datas);
			self::ajaxSuccess($ret);
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
	
	/**
	 * 删除一个用户
	 * @param unknown $type		用户的类型，teacher、student
	 * @param unknown $id		要删除的用户的id
	 */
	public function aRemove($type, $id){
		self::baseDelete($type, $id, "删除用户失败，请稍后再试");
	}
	
	/**
	 * 重置用户密码
	 * @param unknown $type			用户类型，teacher、student
	 * @param unknown $id			用户id
	 */
	public function aPswClear($type, $id){
		self::baseUpdate($type, $id, array("password"=>strtoupper(md5(C("DEFAULT_PWD")))), "重置用户密码错误，请稍后再试");
	}
	
	/**
	 * 修改用户信息
	 * @param unknown $uid			要修改的用户的id
	 * @param unknown $number		修改新账号
	 * @param unknown $name			修改新姓名
	 * @param unknown $role			修改新角色
	 */
	public function aUpdateTeacher($uid, $number, $name, $role){
		self::baseUpdate("teacher", $uid, array("name"=>$name, "number"=>$number, "role"=>$role), "修改教师信息错误，请稍后再试");
	}
	
	/**
	 * 修改用户信息
	 * @param unknown $uid			要修改的用户的id
	 * @param unknown $number		修改新账号
	 * @param unknown $name			修改新姓名
	 * @param unknown $class		修改新的班级
	 */
	public function aUpdateStudent($uid, $number, $name, $class){
		self::baseUpdate("student", $uid, array("name"=>$name, "number"=>$number, "class"=>$class), "修改学生信息错误，请稍后再试");
	}
	
	/**
	 * 获取所有的教师的id和name
	 */
	public function gTeachersAll(){
		try{
			$db = M("teacher");
			$teachers = $db->where(array("enable"=>1))->field(array("id", "name"))->select();
			if($teachers === false){
				self::ajaxError("获取教师列表失败");
				return;
			}
			self::ajaxSuccess($teachers);
		}catch(\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}
	
	/**
	 * 获取某个年级下的所有学生
	 * @param unknown $grade			年级
	 * @param string $jsoned			是否返回json
	 * @return Ambigous <\Think\mixed, boolean, string, NULL, mixed, unknown, multitype:, object>	获取成功返回数据否则返回false
	 */
	public function gStudentsOfGrade($grade, $jsoned=true){
		try{
			$db = M("student");
			$students = $db->join("`class` ON `class`.`id` = `student`.`class`")
				->where(array("`class`.`grade`"=>$grade, "`student`.`enable`"=>1))
				->field(array("`student`.`id`", "`student`.`number`", "`student`.`name`", "`student`.`sex`", "`class`.`name`"=>"classname", "`class`.`grade`"))
				->select();
			if($students === false){
				$jsoned && self::ajaxError("查询学生错误，请稍后再试");
				return false;
			}
			$jsoned && self::ajaxSuccess($students);
			return $students;
		}catch (\Exception $e){
			$jsoned && self::ajaxError("出现了未知错误");
			return false;
		}
		return false;
	}
	
	/**
	 * 获取某个班级下的学生
	 * @param unknown $class		班级id
	 * @param string $jsoned		是否返回json
	 * @return boolean|Ambigous <\Think\mixed, boolean, string, NULL, mixed, unknown, multitype:, object>	获取成功返回数据，否则返回false
	 */
	public function gStudentsOfClass($class, $jsoned=true){
		try{
			$db = M("student");
			$students = $db
			->where(array("class"=>$class, "enable"=>1))
			->field(array("id", "number", "name", "sex"))
			->select();
			if($students === false){
				$jsoned && self::ajaxError("查询学生错误，请稍后再试");
				return false;
			}
			$jsoned && self::ajaxSuccess($students);
			return $students;
		}catch (\Exception $e){
			$jsoned && self::ajaxError("出现了未知错误");
			return false;
		}
		return false;
	}
	
	
	
	
	
	
	
	
}