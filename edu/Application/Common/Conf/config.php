<?php
return array(
	//'配置项'=>'配置值'
	
	"SEARCH_NUM"	=>	30,				//搜索的时候的最大搜索数量
	"DEFAULT_PWD"	=>	"123456",		//用户默认密码
	
	//文件上传配置
	
	"FILE_UPLOAD_ROOT_PATH"		=>	"./Public/",
	"FILE_UPLOAD_SAVE_PATH"		=>	"uploads/",
	"FILE_UPLOAD_MAX_SIZE"		=>	1024*1024*10,
	
	//邮件发送配置
	"MAIL_SUBJECT" =>	"【遵义大学计算机信息与技术学院】发来消息，请查阅",
	
	
	"LOAD_EXT_CONFIG"=>array(
		"WS"		=>	"ws",			//WebSocket配置
		"db",							//数据库配置
		"SEARCH"	=>	"search",		//全局搜索配置
		"PAGE"		=>	"page",			//分页配置
		"FILE"		=>	"file",			//文件配置
		"STUFUN"	=>	"stufun",		//学生功能列表
	),
		
);