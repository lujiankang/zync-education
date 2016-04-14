<?php
/**
 * =========================================================
 * 					全局搜索配置
 * 
 * 配置格式为：
 * 		TABLE	要搜索的表格
 * 		AS		搜索出来作为什么
 * 		SEARCH	要搜索的字段
 * 		FIELD	返回的字段
 * 		WHERE	查询字段
 * 		JOIN	join数组
 * 
 * =========================================================
 */
return array(
	//教师和学生作为用户
	array(
		"TABLE"		=>	"teacher",
		"AS"		=>	"teacher",
		"SEARCH"	=>	array("number", "name"),
		"FIELD"		=>	array("id", "number", "name"),
		"ORDER"		=>	"`number` ASC"
	),
	array(
		"TABLE"		=>	"student",
		"AS"		=>	"student",
		"SEARCH"	=>	array("number", "name"),
		"FIELD"	=>	array("id", "number", "name"),
		"ORDER"		=>	"`number` ASC"
	),
	array(
		"TABLE"		=>	"file",
		"AS"		=>	"file",
		"SEARCH"	=>	array("name"),
		"FIELD"		=>	array("id", "name"),
		"WHERE"		=>	"`owner` = __use_uid__ AND `usertype` = '__use_utype__'",
		"ORDER"		=>	"`name` ASC"
	)
);