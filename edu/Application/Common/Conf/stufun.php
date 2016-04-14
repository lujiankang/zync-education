<?php
return array(
	array(
		"id"		=>	1,
		"name"		=>	"课程中心",
		"icon"		=>	"fa-graduation-cap",
		"children"	=>	array(
			array(
				"id"		=>	11,
				"name"		=>	"我的课程",
				"url"		=>	"Home/Course/pStudentCourse",
				"icon"		=>	"fa-leaf"
			),
			array(
				"id"		=>	12,
				"name"		=>	"作业中心",
				"url"		=>	"Home/Course/pStudentHomework",
				"icon"		=>	"fa-bug"
			)
		)
	),
	
		
		
	array(
			"id"		=>	1,
			"name"		=>	"论文中心",
			"icon"		=>	"fa-pie-chart",
			"children"	=>	array(
					array(
							"id"		=>	11,
							"name"		=>	"我的论文信息",
							"url"		=>	"Home/Essay/pStudentMains",
							"icon"		=>	"fa-sitemap"
					)
			)
	)
		
		
);