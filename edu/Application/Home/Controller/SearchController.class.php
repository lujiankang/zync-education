<?php
/**
 * ==================================================
 * 				数据库搜索控制器
 * ==================================================
 */

namespace Home\Controller;

use Think\Log;
class SearchController extends BaseController{	
	/**
	 * 搜索数据
	 * @param string $key		关键字，如果没有关键字，则搜索所有数据
	 */
	public function search($key=""){
		$conf = C("SEARCH");
		$datas = array();
		foreach ($conf as $c){
			$data = self::_searchTable($c["TABLE"], $c["SEARCH"], $c["FIELD"], $c["WHERE"], $c["ORDER"], $key);
			if($data){
				$datas[$c["AS"]] || ($datas[$c["AS"]] = array());
				$datas[$c["AS"]] = array_merge($datas[$c["AS"]], $data);
			}
		}
		self::ajaxSuccess($datas);
	}
	
	/**
	 * 搜索某张表
	 * @param unknown $table			表名
	 * @param unknown $failed			字段数组
	 * @param unknown $key				关键字
	 * @return Ambigous obj|boolean		搜索结果，如果搜索失败返回false
	 */
	private function _searchTable($table, $search, $field, $where, $order, $key){
		try{
			$where = str_replace("__use_uid__", self::getUserID(), $where);
			$where = str_replace("__use_utype__", self::getUserType(), $where);
			//生成like
			$bwhere = array("_logic"=>"OR");
			//如果以空格隔开表示多个条件同时满足
			$keyArr = explode(" ", $key);
			foreach($search as $f){
				$bwhere[] = array($f => array("LIKE", "%$key%"));
				if(count($keyArr)>1){
					foreach($keyArr as $k){
						$bwhere[] = array($f => array("LIKE", "%$k%"));
					}
				}
			}
			if($where=="") $swhere = $bwhere;
			else $swhere = array($where, $bwhere);
			$db = M($table);
			$db->where(array("enable"=>1, $swhere))->field($field);
			$order && ($db->order($order));
			$data = $db->select();
			Log::write($db->_sql());
			return $data;
		}catch (\Exception $e){
			echo $e->getMessage();
			return false;
		}
	}
	
}


