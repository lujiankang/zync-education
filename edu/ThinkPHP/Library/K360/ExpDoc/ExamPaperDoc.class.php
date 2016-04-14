<?php
namespace K360\ExpDoc;

class ExamPaperDoc{
	private $datas = array();

	private $conf = array(
		"opened"	=>	"闭",
		"course"	=>	"数据库原理",
		"caculator"	=>	"否",
		"examtime"	=>	"120",
		"papername"	=>	"期末考试试卷(Ａ卷)",
		"termnum"	=>	"2014~2015",
		"termtext"	=>	"二",
		"grade"		=>	"计算机科学与技术专业13级本科班（简称：13计科班）"
	);
	
	
	/**
	 * 创建试题word生成器
	 */
	public function __construct(){
		$month = intval(date("m", time()));
		$year = intval(date("Y", time()));
		$year = ($month>=7) ? $year : ($year-1);
		$this->conf["termnum"] = $year . "~" . ($year+1);
		$this->conf["termtext"] = ($month>=7) ? "一" : "二";
	}
	
	/**
	 * 设置基本信息
	 * @param unknown $opend			是否开卷
	 * @param unknown $course			课程名
	 * @param unknown $caculator		是否使用计算器
	 * @param unknown $examtime			考试时间（分钟）
	 * @param unknown $papername		试卷名称
	 * @param unknown $grade			试卷适用专业、年级
	 */
	public function setBaseInfo($opend, $course, $caculator, $examtime, $papername, $grade){
		$this->conf["opened"] = $opend;
		$this->conf["course"] = $course;
		$this->conf["caculator"] = $caculator;
		$this->conf["examtime"] = $examtime;
		$this->conf["papername"] = $papername;
		$this->conf["grade"] = $grade;
	}
	
	/**
	 * 添加题型
	 * @param unknown $typename		题型
	 */
	public function addType($typename){
		$str = sprintf(ExamPaperDocData::$typeName, self::_symble($typename));
		array_push($this->datas, $str);
	}
	
	/**
	 * 添加文本
	 * @param unknown $text		要添加的文本
	 */
	public function addText($text){
		if($text=="") return;
		$str = sprintf(ExamPaperDocData::$topicText, self::_symble($text));
		array_push($this->datas, $str);
	}
	
	
	/**
	 * 保存word文档
	 * @param string $path		文档路径，如果没有传入，则下载文件
	 */
	public function save($path = null){
		//拼接文档数据=============
		//顶部固定数据
		$str = sprintf(ExamPaperDocData::$docTable, $this->conf["opened"], $this->conf["course"], $this->conf["caculator"], $this->conf["examtime"]);
		$str .= ExamPaperDocData::$packLine;
		$str .= sprintf(ExamPaperDocData::$topDesc, $this->conf["papername"], $this->conf["termnum"], $this->conf["termtext"], $this->conf["grade"]);
		$str .= ExamPaperDocData::$forStudent;
		//文档内容数据
		foreach ($this->datas as $v){
			$str .= $v;
		}
		//文档结尾描述
		$str .= ExamPaperDocData::$pageDefine;
		//生成document.xml文档
		$xml = sprintf(ExamPaperDocData::$document, $str);
		//生成zip
		$zip = new \ZipArchive();
		$fpath = $path ? $path : "Public/cache/" . md5($xml) . ".docx";
		$zip->open($fpath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
		self::_zip($zip, ExamPaperDocData::$docRes, "");
 		$zip->addFromString("word/document.xml", $xml);
		$zip->close();
		//判断是否下载
		if(!$path){
			//下载
			$name = iconv("UTF-8", "GB2312", $this->conf["papername"]);
			header("Content-type:application/vnd.openxmlformats-officedocument.wordprocessingml.document");
			header("Content-Disposition:attachment;filename='$name.docx'");
			readfile($fpath);
			unlink($fpath);
		}
	}
	
	//生成压缩文件俺
	private function _zip(&$zip, $arr, $path){
		foreach ($arr as $k=>$v){
			if(is_string($v)){
				//文件
				if($v[0] != "<") $zip->addFile($v, $path . $k);
				else $zip->addFromString($path . $k, $v);
			}else{
				//目录
				$zip->addEmptyDir($path . $k);
				self::_zip($zip, $v, $path . $k . "/");
			}
		}
	}
	
	//将特殊符号分离出来XXX未用
	private function _subSymble($str){
		$ret = array();
		$last = 0;
		for($i=0; $i<strlen($str); $i++){
			if($str[$i] == "<" || $str[$i] == ">" || $str[$i] == "&"){
				array_push($ret, substr($str, $last, $i-$last));
				array_push($ret, preg_replace(array("/&/", "/</", "/>/"), array("&amp;", "&lt;", "&gt;"), $str[$i]));
				$last = $i + 1;
			}
		}
		return $ret;
	}
	
	//处理特殊符号
	private function _symble($str){
		$str = preg_replace(array("/&/", "/</", "/>/"), array("&amp;", "&lt;", "&gt;"), $str);
		return $str;
	}
	
	
}