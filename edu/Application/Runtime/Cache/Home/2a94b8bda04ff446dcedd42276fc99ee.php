<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
	<meta charset="utf-8" />
	<link href="/edu/edu/Public/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
	<link href="/edu/edu/Public/css/awesome/font-awesome.css" rel="stylesheet" />
	<script src="/edu/edu/Public/js/jQuery/jquery-2.1.4.min.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-http.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-popover.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-scroll-bar.js"></script>
	<script src="/edu/edu/Public/js/base/func.js"></script>
	<script src="/edu/edu/Public/js/p-Index/index.js"></script>
	<link href="/edu/edu/Public/css/base/animate.css" rel="stylesheet" />
	<css root="/edu/edu/Public/css">["p-Index/index"]</css>
	<script src="/edu/edu/Public/js/k360/k360-tag.js"></script>
</head>
<body bgcolor="#292929">

	<!-- 背景 -->
	<div id="background-img"></div>


	<!-- 顶部导航 -->
	<div id="top-navigate">
		<div class="nav-left">
			<div class="logo">Magic School</div>
			<div class="item">我的文件</div>
			<div class="item">推荐</div>
			<div class="item">发现</div>
			<div class="item">小组</div>
			<div class="search">
				<i class="fa fa-search"></i>
				<input type="text" placeholder="搜索" />
			</div>
		</div>
		<div class="nav-right">
			<div class="user-head">
				<img id="topNavUserHeadImage" src="/edu/edu/index.php/Home/User/gHead/type/teacher/id/-1" />
				<div class="name">陆建康</div>
				<i class="fa fa-sort-down" style="margin-top:-5px"></i>
				<ul>
					<li>个人中心</li>
					<li>通知中心<span class="number-dot">2</span></li>
					<li>私信<span class="number-dot">20</span></li>
					<li>账户设置</li>
					<li>帮助（反馈）</li>
					<li>退出</li>
				</ul>
				<div class="nav-has-msg"></div>
			</div>
			<div class="full-screen"><i class="fa fa-arrows-alt"></i></div>
		</div>
	</div>


	<!-- 显示区域 -->
	<div id="main-container">

		<div class="left-menu">
			<div class="menu-item active">
				<i class="fa fa-home"></i>
				<div class="name">首页</div>
			</div>
			<!--<div class="menu-item">
				<i class="fa fa-cogs"></i>
				<div class="name">系统设置</div>
			</div>
			<div class="menu-item">
				<i class="fa fa-graduation-cap"></i>
				<div class="name">课程管理</div>
			</div>
			<div class="menu-item">
				<i class="fa fa-pie-chart"></i>
				<div class="name">论文管理</div>
			</div>-->
		</div>

		<div class="body-win">
			<iframe class="main-iframe" frameborder="0" width="100%" height="100%"></iframe>
		</div>

	</div>


	<!-- 登录界面 -->
	<div class="u-login">
		<div class="login-container">
			<div class="login-title"><i class="fa fa-key login-key-anim"></i>&nbsp;&nbsp;请先登录</div>
			<form style="margin-top:10px" action="/edu/edu/index.php/Home/User/aLogin" method="post">
				<div class="form-group">
					<label>账号</label>
					<input type="text" class="form-control" name="number" placeholder="请输入你的登录账号" required>
				</div>
				<div class="form-group">
					<label>密码</label>
					<input type="password" class="form-control" name="password" placeholder="请输入登录密码" required>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="autoLogin" checked> 记住我
					</label>
				</div>
				<button type="submit" class="btn btn-success btn-block" style="margin-top:30px">
					<i class="fa fa-home" style="margin-right:10px"></i><span>登录</span>
				</button>
			</form>
		</div>
		<img class="login-logo" src="/edu/edu/Public/images/index-logo.png" />
	</div>


	<!-- loading -->
	<div id="indexLoading">
		<div>
			<div><i class="fa fa-spinner fa-spin"></i></div>
			<div>数据加载中，请稍后。。。</div>
		</div>
	</div>
</body>
</html>

<script>
	window.logined = "<?php echo ($logined); ?>";			//是否已经登录

	window.exitHTML = "您已退出系统";

	
</script>