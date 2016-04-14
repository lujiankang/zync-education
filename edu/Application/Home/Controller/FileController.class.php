<?php
namespace Home\Controller;

use K360\DocPreview\WordPreview;
use K360\DocPreview\PowerpointPreview;
use K360\DocPreview\ExcelPreview;
class FileController extends BaseController{
	
	/**
	 * 文件在线预览
	 * @param unknown $id		文件的id
	 */
	public function pPreview($id){
		//获取文件路径
		$public = "http://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . __ROOT__ . "/Public/";
		$db = M("file");
		$file = $db->where(array("id"=>$id))->field(array("id", "name", "path"))->find();
		$fileURL = urlencode($public . $file["path"]);
		$url = "https://view.officeapps.live.com/op/view.aspx?src=" . $fileURL;
		echo file_get_contents($url);
	}
	
	
	/**
	 * 重命名一个文件
	 * @param unknown $fid			文件id
	 * @param unknown $name			文件新名字
	 */
	public function aRenameFile($fid, $name){
		try{
			$db = M("file");
			if($db->where(array("id"=>$fid))->data(array("name"=>$name))->save()===false){
				self::ajaxError("文件重命名是出现错误");
				return;
			}
			self::ajaxSuccess(true);
		}catch (\Exception $e){
			self::ajaxError("出现了未知错误");
		}
	}

	
	public function aPreview($path, $page){
		try{
			$suff = "";
			for($i=strlen($path)-1; $i>=0; $i--){
				if($path[$i] == "."){
					$suff = substr($path, $i+1);
					break;
				}
			}
			if($suff=="doc" || $suff=="docx"){
				$prv = new WordPreview();
				$url = $prv->getPublic() . $path;
				$prv->setWordUrl($url);
				if(!$prv->parseWord()){
					self::ajaxError($prv->getError());
					return;
				}
				$content = $prv->getHtml();
				self::ajaxSuccess(array("html"=>$content));
				return;
			}
			if($suff=="ppt" || $suff=="pptx"){
				$prv = new PowerpointPreview();
				$url = $prv->getPublic() . $path;
				$prv->setUrl($url);
				$prv->setGetIndex($page);
				if(!$prv->parsePowerpoint()){
					self::ajaxError($prv->getError());
					return;
				}
				$content = $prv->getHtml();
				self::ajaxSuccess(array("html"=>$content, "count"=>$prv->getSlideCount()));
				return;
			}
			if($suff=="xls" || $suff=="xlsx"){
				$prv = new ExcelPreview();
				$url = $prv->getPublic() . $path;
				$prv->setUrl($url);
				$prv->setGetIndex($page);
				if(!$prv->parseExcel()){
					self::ajaxError($prv->getError());
					return;
				}
				self::ajaxSuccess(array("html"=>$prv->getHtml(), "count"=>$prv->getSheetCount(), "sheets"=>$prv->getSheetNames()));
				return;
			}
			//其他文档直接打开
			$content = file_get_contents("Public/$path");
			$content = str_replace("\r", "", $content);
			$content = str_replace("\n", "<br />", $content);
			$content = self::str2utf8($content);
			self::ajaxSuccess(array("html"=>$content));
		}catch (\Exception $e){
			self::ajaxError("出现未知错误");
		}
	}
	
}
