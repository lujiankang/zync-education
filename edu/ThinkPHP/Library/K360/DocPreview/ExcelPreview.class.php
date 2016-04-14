<?php
namespace K360\DocPreview;

class ExcelPreview extends DPVSocket{
	
	private $url = "";
	private $index = "";
	
	private $html = "";
	private $count = 0;
	private $names = array();
	
	/**
	 * 在数据发送之前调用，请重写此方法，否则无法通过
	 * @return multitype		url以及其他数据组成的一个数组
	 */
	protected function onBeforeSend(){
		//提供url以及其他信息，url是必须的
		return
		array(					//设置要解析的数据
				"url"=>$this->url,			//文档的url
				"index"=>$this->index		//获取第几个表格
		);
	}
	
	/**
	 * 接受完毕后调用这个方法，请重写此方法以获取服务器返回的数据
	 * @param unknown $data
	 */
	protected function onAfterRecv($data){
		//接收完毕
		$this->html = $data->html;
		$this->count = intval($data->count);
		$this->names = $data->names;
	}
	
	/**
	 * 设置excel文档的url
	 * @param unknown $url			文档的url地址，如：http://www.example.com/exmp.xlsx
	 */
	public function setUrl($url){
		$this->url = $url;
	}
	
	/**
	 * 设置要获取excel的那张表格
	 * @param unknown $index		表格的位置
	 */
	public function setGetIndex($index){
		$this->index = $index;
	}
	
	/**
	 * 开始解析文档
	 * @return boolean
	 */
	public function parseExcel(){
		if($this->connect() == false)
			return false;
		if($this->parse() == false)
			return false;
		return true;
	}
	
	/**
	 * 获取表格的总数
	 * @return number			表格总数
	 */
	public function getSheetCount(){
		return $this->count;
	}
	
	/**
	 * 获取每张表格的名字
	 * @return multitype:String			每张表格的名字
	 */
	public function getSheetNames(){
		return $this->names;
	}
	
	/**
	 * 获取表格的html字符串
	 * @return string
	 */
	public function getHtml(){
		return $this->html;
	}
		
}