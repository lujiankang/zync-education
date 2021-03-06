<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>系统设置</title>
	<meta charset="utf-8" />
	<link rel='icon' href="/edu/edu/Public/images/index.ico" />
	<!-- 移动端适应 -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- jQuery -->
	<script src="/edu/edu/Public/js/jQuery/jquery-2.1.4.min.js"></script>
	<!-- Bootstrap -->
	<link href="/edu/edu/Public/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
	<script src="/edu/edu/Public/js/bootstrap/bootstrap.min.js"></script>
	<!-- 字体图标 -->
	<link href="/edu/edu/Public/css/awesome/font-awesome.css" rel="stylesheet" />
	<!-- k360 -->
	<script src="/edu/edu/Public/js/k360/k360-scroll-bar.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-http.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-popover.js"></script>

	<!-- 当前页面css -->
	<link href="/edu/edu/Public/css/base/page.css" rel="stylesheet" />
	<!-- 当前页面js -->
	<script src="/edu/edu/Public/js/base/resp-head.js"></script>
	<script src="/edu/edu/Public/js/base/top-nav.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-tag.js"></script>
	<link href="/edu/edu/Public/css/base/animate.css" rel="stylesheet" />
</head>
<body>
	
	<!-- 头部 -->
	<div class="row header">
		<div class="col-md-12">
			<div class="header-close" onclick="top.respShower.style.display = 'none'"><i class="fa fa-close"></i></div>
			<div class="fa fa-cog icon"></div>
			<span class="title">系统配置</span>
		</div>
	</div>

	<div class="data-detail" style="overflow:hidden">
		<iframe width="100%" height="100%" frameborder="0" src="/edu/edu/index.php/Home/User/pUserCtrl"></iframe>
	</div>

	<div class="data-core">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-warning">
					<div class="panel-heading">邮件服务器配置</div>
					<div class="panel-body table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>服务器地址</th>
									<th>服务器端口</th>
									<th>发送者邮箱</th>
									<th>登录名</th>
									<th>登录密码</th>
									<th>显示名</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td id="emailShowServer"><?php echo ($mailConf["server"]); ?></td>
									<td id="emailShowPort"><?php echo ($mailConf["port"]); ?></td>
									<td id="emailShowAddr"><?php echo ($mailConf["addr"]); ?></td>
									<td id="emailShowUser"><?php echo ($mailConf["user"]); ?></td>
									<td>***********<span hidden id="emailShowPassword"><?php echo ($mailConf["password"]); ?></span></td>
									<td id="emailShowUsername"><?php echo ($mailConf["username"]); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<button class="btn btn-success" id="mailConfUpdateBtn">&nbsp;&nbsp;更改配置&nbsp;&nbsp;</button>
					<p class="help-block">邮件服务器是用于向用户发送邮件的，配置时请注意，如果配置不正确，邮件将无法发出。</p>
				</div>
			</div>
		</div>
	</div>


	<div class="inputer" id="mailConfigerDiv">
		<div class="animated zoomInUp">
			<div style="width:100%">
				<form action="/edu/edu/index.php/Home/System/aSaveMailConf" method="post" id="emailConfUpdateForm">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="name">服务器地址</label>
								<input type="text" class="form-control" name="server" placeholder="请输入网址或IP地址" required>
							</div>
							<div class="form-group">
								<label for="name">服务器端口</label>
								<input type="text" class="form-control" name="port" placeholder="邮件服务器端口一般为25" required>
							</div>
							<div class="form-group">
								<label for="name">发送邮箱</label>
								<input type="text" class="form-control" name="addr" placeholder="xxxx@qq.com" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="name">登录名</label>
								<input type="text" class="form-control" name="user" placeholder="邮件服务器登陆用户名" required>
							</div>
							<div class="form-group">
								<label for="name">密码</label>
								<input type="password" class="form-control" name="password" placeholder="邮件服务器登录密码" required>
							</div>
							<div class="form-group">
								<label for="name">显示名</label>
								<input type="text" class="form-control" name="username" placeholder="用户收到邮件时所显示的标题名称" required>
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-success"><i class="fa fa-spin fa-spinner" style="display:none"></i>&nbsp;&nbsp;保存修改&nbsp;&nbsp;</button>
					<button type="button" class="btn btn-warning" onclick="mailConfigerDiv.style.display='none'">&nbsp;&nbsp;取消修改&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>
</body>
</html>


<script>


	window.mail_config_controller = {
		create: function () {
			var obj = {};

			obj.init = function () {
				//点击修改弹出修改框
				mailConfUpdateBtn.onclick = function () {
					mailConfigerDiv.style.display = "inline-block";
					//居中
					var ctt = mailConfigerDiv.children[0];
					var ph = mailConfigerDiv.clientHeight;
					var ch = ctt.clientHeight;
					var y = (ph - ch) >> 1;
					ctt.style.top = y + "px";
					//填充数据
					emailConfUpdateForm.server.value = emailShowServer.innerHTML;
					emailConfUpdateForm.port.value = emailShowPort.innerHTML;
					emailConfUpdateForm.addr.value = emailShowAddr.innerHTML;
					emailConfUpdateForm.user.value = emailShowUser.innerHTML;
					emailConfUpdateForm.password.value = emailShowPassword.innerHTML;
					emailConfUpdateForm.username.value = emailShowUsername.innerHTML;
				}
				//表单提交
				emailConfUpdateForm.onsubmit = function () {
					//禁用按钮
					var submitBtn = $(this).find("[type='submit']")[0];
					var submitIcon = submitBtn.children[0];
					submitBtn.disabled = true;
					submitIcon.style.display = "inline-block";
					//提交
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						//完成
						submitBtn.disabled = false;
						submitIcon.style.display = "none";
						k360_popover.create().setTopPop(true).toast("配置已经完成");
						mailConfigerDiv.style.display = "none";
						//更新数据
						emailShowServer.innerHTML = emailConfUpdateForm.server.value;
						emailShowPort.innerHTML = emailConfUpdateForm.port.value;
						emailShowAddr.innerHTML = emailConfUpdateForm.addr.value;
						emailShowUser.innerHTML = emailConfUpdateForm.user.value;
						emailShowPassword.innerHTML = emailConfUpdateForm.password.value;
						emailShowUsername.innerHTML = emailConfUpdateForm.username.value;
					})
					.onerror(function (xhr, status, statusText) {
						k360_popover.create().setTopPop(true).toast("出现错误了，错误码：" + status);
						submitBtn.disabled = false;
						submitIcon.style.display = "none";
					})
					.send();
					return false;
				}
			}

			return obj;
		}
	}

	window.addEventListener("load", function () {
		mail_config_controller.create().init();
	})

</script>