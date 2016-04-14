<?php
namespace K360\DocPreview;

class PowerpointPreview extends DPVSocket{
	
	private $url = null;
	private $index = 0;

	private $html = "";
	private $count = 0;
	
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
	}
	
	/**
	 * 设置PPT/PPTX文档的url地址
	 * @param unknown $url			文档的url
	 */
	public function setUrl($url){
		$this->url = $url;
	}
	
	/**
	 * 设置你想要获取第几张幻灯片
	 * @param unknown $index		幻灯片的位置
	 */
	public function setGetIndex($index){
		$this->index = $index;
	}
	
	/**
	 * 开始解析PPT
	 * @return boolean		解析成功返回true，否则返回false
	 */
	public function parsePowerpoint(){
		if($this->connect() == false)
			return false;
		if($this->parse() == false)
			return false;
		return true;
	}
	
	/**
	 * 解析完成后获取文档解析后的html
	 * @return string			html
	 */
	public function getHtml(){
		return $this->html;
	}
	
	/**
	 * 解析完成后得到幻灯片的数量
	 * @return number		幻灯片的数量
	 */
	public function getSlideCount(){
		return intval($this->count);
	}
	
}