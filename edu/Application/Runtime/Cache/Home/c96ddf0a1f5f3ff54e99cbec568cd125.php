<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
	<meta charset="utf-8" />
	<script src="/edu/edu/Public/js/jQuery/jquery-2.1.4.min.js"></script>
	<script src="/edu/edu/Public/js/base/func.js"></script>
	<link href="/edu/edu/Public/css/base/animate.css" rel="stylesheet" />
	<script src="/edu/edu/Public/js/k360/k360-scroll-bar.js"></script>
	<style>
		
.user {
	position: fixed;
	left:0px;
	top: 0px;
	right: 0px;
	bottom: 0px;
}

.user-base-info {
	margin: 12px;
}

	.user-base-info img {
		width: 90px;
		height: 90px;
		border-radius: 50px;
		display: inline-block;
		vertical-align: middle;
	}

	.user-base-info > div {
		display: inline-block;
		margin-top: 20px;
		font-size: 15px;
		vertical-align: middle;
		width: 115px;
	}

	.user-base-info .user-name {
		font-weight: bold;
	}

		.user-base-info .user-name span {
			display: inline-block;
			width: 25px;
			height: 25px;
			background: url(/edu/edu/Public/images/index/star.gif) no-repeat;
			background-position: center;
			text-align: center;
			line-height: 28px;
			color: #b65e00;
			font-family: arial;
			font-size: 11px;
			margin-left: 5px;
		}

	.user-base-info .user-prog {
		width: 100%;
		margin-top: 8px;
		height: 5px;
		background: #FFF;
	}

		.user-base-info .user-prog div {
			width: 90%;
			height: 100%;
			background: #67c68b;
		}

	.user-base-info .user-data-ok {
		font-size: 12px;
		margin-top: 6px;
		color: #4183C4;
	}

.user-data-num {
	background: #f0f0f0;
	padding-top: 10px;
	padding-bottom: 10px;
	margin: 5px 0px 10px 0px;
}

	.user-data-num > .item {
		width: 33.333%;
		float: left;
		text-align: center;
	}

		.user-data-num > .item .number {
			display: block;
			font-size: 18px;
			font-weight: bold;
			color: #444;
		}

		.user-data-num > .item .text {
			color: #555;
			font-size: 12px;
		}

.recent-data{
	margin: 20px 22px 4px 22px;
}

.recent-data .title{
	height: 18px;
    border-left: 4px solid rgb(39, 174, 91);
    padding-left: 8px;
    margin-bottom: 8px;
}

.recent-data .title .more{
	float: right;
    font-size: 12px;
	color: #4183C4;
    cursor: pointer;
}

.recent-data .datas .item{
	border-bottom: 1px solid #eee;
    height: 55px;
    background: #fdfdfd;
	line-height:27px;
	font-size:13px;
}

.recent-data .datas .item >div{
	margin-left:10px;
}

.recent-data .datas .item .name{
	cursor:pointer;
	color:#4183C4;
}
	</style>
</head>
<body>
	<div class="user" k360-scroll-y="4">

		<div class="user-base-info" style="opacity:0">
			<img src="/edu/edu/index.php/Home/User/gHead/type/<?php echo ($user["type"]); ?>/id/<?php echo ($user["id"]); ?>" />
			<div>
				<div class="user-name">陆建康<span class="levo">1</span></div>
				<div class="user-prog">
					<div></div>
				</div>
				<div class="user-data-ok">资料完整度<span>90%</span></div>
			</div>
		</div>

		<div class="user-data-num">
			<div class="item">
				<div class="number">0</div>
				<div class="text">文件</div>
			</div>
			<div class="item">
				<div class="number">0</div>
				<div class="text">课程</div>
			</div>
			<div class="item">
				<div class="number">0</div>
				<div class="text">题目</div>
			</div>
			<div style="clear:both"></div>
		</div>

		<div class="recent-files recent-data">
			<div class="title">最新文件<span class="more">更多</span></div>
			<div class="datas">
				<div class="item">
					<div class="name">测试文件.doc</div>
					<div class="date">2015-03-12</div>
				</div>
				<div class="item">
					<div class="name">C语言基础教程（1）.ppt</div>
					<div class="date">2015-03-15</div>
				</div>
				<div class="item">
					<div class="name">汇编语言（1）.ppt</div>
					<div class="date">2015-04-15</div>
				</div>
				<div class="item">
					<div class="name">汇编语言（2）.ppt</div>
					<div class="date">2015-04-15</div>
				</div>
			</div>
		</div>



		<div class="recent-homework recent-data">
			<div class="title">最新作业<span class="more">更多</span></div>
			<div class="datas">
				<div class="item">
					<div class="name">第一次作业</div>
					<div class="date">《C语言基础教程》</div>
				</div>
				<div class="item">
					<div class="name">第二次作业</div>
					<div class="date">《计算机组成原理》</div>
				</div>
				<div class="item">
					<div class="name">第一次作业</div>
					<div class="date">《计算机网络》</div>
				</div>
			</div>
		</div>

	</div>
</body>
</html>

<script>
	window.addEventListener("load", function () {
		var animdom = document.querySelector(".user-base-info");
		animdom.style.opacity = 1;
		anim(animdom, "flipInX");
	});
</script>