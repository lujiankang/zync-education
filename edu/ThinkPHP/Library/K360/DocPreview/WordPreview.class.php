<?php
/**
 * @version 1.0
 * @author 陆建康
 * @example
 * 这个类是用于word在线预览的类，通过这个类，可以快速得到word文档对应的html文档而无须生成中间数据。
 * 使用这个类请遵从以下流程：
 * 		1、创建WordPreview对象。
 * 		2、设置word文件所在的URL（URL需填写完整路径，如：http://www.example.com/exmp.docx）。
 * 		3、调用 parseWord方法进行解析。
 * 		4、判断parseWord的返回值。
 * 			4.1、true	解析成功，你可以调用getHtml方法得到html字符串。
 * 			4.2、false	解析失败，你可以调用getError方法得到错误。
 * 		5、既然html数据都得到了，剩下的就不用我说了吧。
 * 
 */
namespace K360\DocPreview;

class WordPreview extends DPVSocket{
	
	private $url = null;
	
	private $docHtml = null;
	
	
	//解析开始前调用，设置解析参数
	protected function onBeforeSend(){
		return
		array(
			"url"	=>	$this->url
		);
	}
	
	//解析成功调用，得到html字符串
	protected function onAfterRecv($data){
		$this->docHtml = $data->html;
		//正则替换
		//PAGE&nbsp;&nbsp;&nbsp;\*&nbsp;MERGEFORMAT13
		$this->docHtml = preg_replace("/(HYPERLINK)(&nbsp;)(\\\l)(&nbsp;)(\")(_Toc\d+)(\")/", "", $this->docHtml);
		$this->docHtml = preg_replace("/(PAGEREF)(&nbsp;)(_Toc\d+)/", "", $this->docHtml);
		$this->docHtml = preg_replace("/(TOC)(&nbsp;)(\\\o)([\s\S]+)(\\\u)/", "", $this->docHtml);
		$this->docHtml = preg_replace("/(PAGE)([\s\S]+)(MERGEFORMAT)([\s\S]*)\r\n/", "", $this->docHtml);
	}
	
	/**
	 * 设置word文档的url
	 * @param string $url		word文档的url，例如：http://www.xxxx.com/uploads/example.docx
	 */
	public function setWordUrl($url){
		$this->url = $url;
	}
	
	/**
	 * 开始解析word文档
	 * @return boolean		解析成功返回true，否则返回false
	 */
	public function parseWord(){
		if($this->connect() == false)
			return false;
		if($this->parse() == false)
			return false;
		return true;
	}
	
	/**
	 * 得到解析后的html字符串
	 * @return string			html字符串
	 */
	public function getHtml(){
		return $this->docHtml;
	}
}