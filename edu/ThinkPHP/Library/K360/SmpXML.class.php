<?php
namespace K360;

class SmpXML{
	
	private $xml = "";
	private $index = 0;
	
	private $buffer = "";
	private $bufferProp = "";
	private $propStartCh = "";		//属性开始标记（"或'）
	private $bufferStart = "";		//开始标记，用于处理<xxxxx xxxx xxxx='xxxx' />标记的结束标记
	
	private $err = "";
	private $datas = array();
	
	
	/**
	 * 构造函数
	 * @param unknown $xml			要解析的xml字符串
	 */
	public function __construct($xml){
		$this->xml = preg_replace("/(\r|\n|\t| {2})/", "", $xml);
		$index = 0;
	}
	
	
	/**
	 * 开始解析
	 */
	public function parse(){
		self::_step1();
		if($this->err != "")
			E($this->err);
		//进一步分析
		$buffer = $this->datas;
		$datas = self::_parseNext($buffer, null);
		print_r($datas);
	}
	
	private function _parseNext(&$buffer){
		//读取开始标记及属性
		$items = array();
		do{
			array_push($items, array_shift($buffer));
		}while(($buffer[0]["type"] != "STT") && ($buffer[0]["type"]!="END"));
		//生成dom
		$dom = array("name"=>"", "prop"=>array(), "children"=>array(), "text"=>"");
		foreach ($items as $v){
			if($v["type"] == "STT") $dom["name"] = $v["name"];
			else if($v["type"] == "PRP") $dom["prop"][$v["name"]] = $v["value"];
			else if($v["type"] == "TXT") $dom["text"] = $v["text"];
		}
		//解析下一层，直到解析完毕
		while($buffer[0]["type"] == "STT"){
			$child = self::_parseNext($buffer, $dom);
			array_push($dom["children"], $child);
		}
		//删除自己的结束标记
		if($buffer[0]["type"] == "END"){
			array_shift($buffer);
		}
		return $dom;
	}
	
	
	/**
	 * 生成开始标签
	 * @param unknown $name		标签名称
	 */
	private function STT($name){
		return array("type"=>"STT", "name"=>$name);
	}
	
	/**
	 * 生成属性
	 * @param unknown $name			属性名
	 * @param unknown $value		属性值
	 */
	private function PRP($name, $value){
		return array("type"=>"PRP", "name"=>$name, "value"=>$value);
	}
	
	/**
	 * 生成文本
	 * @param unknown $str		文本字符串
	 */
	private function TXT($str){
		return array("type"=>"TXT", "text"=>$str);
	}
	
	/**
	 * 生成结束标签
	 * @param unknown $name		标签名
	 */
	private function END($name){
		return array("type"=>"END", "name"=>$name);
	}
	
	/**
	 * 获取下一个字符
	 * @return string			下一个字符
	 */
	private function ch(){
		if((strlen($this->xml)) <= $this->index)
			return false;
		$c = $this->xml[$this->index];
		$this->index ++;
		return $c;
	}
	
	private function _step1(){
		$c = self::ch();
		if($c === false) return;
		if($c == "<"){
			//有字符串
			if(strlen($this->buffer)>0){
				array_push($this->datas, self::TXT($this->buffer));
			}
			//清空
			$this->buffer = "";
			self::_step2();
		}
		else{
			$this->buffer .= $c;
			self::_step1();
		}
	}
	
	private function _step2(){
		$c = self::ch();
		if($c == " "){
			array_push($this->datas, self::STT($this->buffer));
			$this->bufferStart = $this->buffer;
			//接下来读取属性
			$this->buffer = "";
			self::_step3();
		}else if($c == "/"){
			//结束标记
			self::_step7();
		}else if($c == ">"){
			//开始标记完
			array_push($this->datas, self::STT($this->buffer));
			$this->bufferStart = $this->buffer;
			$this->buffer = "";
			self::_step1();
		}else if ($c == "/"){
			array_push($this->datas, self::END($this->bufferStart));
			$this->buffer = "";
			self::_step8();
		}else{
			//读取开始标记
			$this->buffer .= $c;
			self::_step2();
		}
	}
	
	private function _step3(){
		$c = self::ch();
		if($c == " "){
			//读取完了一个属性
			array_push($this->datas, self::PRP($this->buffer, ""));
			$this->buffer = "";
			self::_step3();
		}else if($c == "="){
			//读取到键值对属性
			$this->bufferProp = $this->buffer;		//保存键
			$this->buffer = "";
			self::_step4();			//去读取值
		}else if($c == ">"){
			//开始标签结束
			self::_step1();
		}else if ($c == "/"){
			array_push($this->datas, self::END($this->bufferStart));
			$this->buffer = "";
			self::_step8();
		}else{
			//继续读取
			$this->buffer .= $c;
			self::_step3();
		}
	}
	
	private function _step4(){
		$c = self::ch();
		if($c == "\"" || $c="'"){
			//读取到属性值内容
			$this->propStartCh = $c;
			self::_step5();
		}else{
			//出错了
			$this->err = "读取属性值错误，" . substr($this->xml, $this->index-5, 20) . " ...";
		}
	}
	
	private function _step5(){
		$c = self::ch();
		if($c == $this->propStartCh){
			//读取到属性值
			array_push($this->datas, self::PRP($this->bufferProp, $this->buffer));
			$this->buffer = "";
			self::_step6();
		}else{
			//继续读取
			$this->buffer .= $c;
			self::_step5();
		}
	}
	
	private function _step6(){
		$c = self::ch();
		if($c==" "){
			//还有属性没有读取，回去继续读取
			self::_step3();
		}else if($c == ">"){
			//开始标记读取完
			self::_step1();
		}else if($c == "/"){
			array_push($this->datas, self::END($this->bufferStart));
			$this->buffer = "";
			self::_step8();
		}else{
			//出错了
			$this->err = "读取属性值错误，" . substr($this->xml, $this->index-5, 20) . " ...";
		}
	}
	
	private function _step7(){
		$c = self::ch();
		if($c == ">"){
			//结束标记读取完毕
			array_push($this->datas, self::END($this->buffer));
			$this->buffer = "";
			self::_step1();
		}else {
			//继续读取
			$this->buffer .= $c;
			self::_step7();
		}
	}
	
	private function _step8(){
		$c = self::ch();
		if($c == ">") self::_step1();
		else self::_step8();
	}
	
	
	
	
}