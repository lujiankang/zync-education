<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>角色管理</title>
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
	<script src="/edu/edu/Public/js/base/k360-bt-page.js"></script>
	<script src="/edu/edu/Public/js/base/func.js"></script>
	<script src="/edu/edu/Public/js/base/resp-head.js"></script>

	<script src="/edu/edu/Public/js/base/top-nav.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-tag.js"></script>
	<link href="/edu/edu/Public/css/base/animate.css" rel="stylesheet" />

	<style>
		#rolePowersSetter .item {
			line-height: 30px;
		}

			#rolePowersSetter .item input {
				margin-right: 20px;
			}

		#rolePowersSetter .children {
			margin-left: 30px;
		}
		/*控制权限加载过程中的样式*/
		#rolePowersSetter > div > div.powersloading > .loading {
			color: #2980B9;
			display: inline-block;
		}

		#rolePowersSetter > div > div.powersloading > form > div {
			display: none;
		}

		#rolePowersSetter > div > div:not(.powersloading) .loading {
			display: none;
		}

		#rolePowersSetter > div > div:not(.powersloading) > form > div {
			display: inline-block;
		}
	</style>
</head>
<body>
	<!-- 头部 -->
	<div class="row header">
		<div class="col-md-12">
			<div class="header-close" onclick="top.respShower.style.display = 'none'"><i class="fa fa-close"></i></div>
			<div class="fa fa-unlock icon"></div>
			<span class="title">角色管理</span>
			<div class="header-menu"><i class="fa fa-reorder"></i></div>
		</div>
	</div>

	<div class="data-detail" style="overflow:hidden">
		<iframe width="100%" height="100%" frameborder="0" src="/edu/edu/index.php/Home/User/pUserCtrl"></iframe>
	</div>

	<div class="data-core">
		<!-- 顶部操作 -->
		<div class="row handles">
			<div class="col-sm-12 top-btns">
				<button class="handle-item btn btn-danger" id="roleCreateBtn"><i class="fa fa-plus"></i>添加角色</button>
			</div>
		</div>

		<!-- 主体 -->
		<div class="row main margin-top-20" k360-scroll-y="4" k360-scroll-keep>
			<div class="col-md-12 table-responsive">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>角色</th>
							<th>描述</th>
							<th width="140">操作</th>
						</tr>
					</thead>
					<tbody id="dataShower">
						<tr>
							<td>xxxx</td>
							<td>xxx</td>
							<td>
								<button class="btn btn-sm btn-danger fa fa-trash to-remove" title="删除角色"></button>
								<button class="btn btn-sm btn-warning fa fa-pencil to-change" title="修改信息"></button>
								<button class="btn btn-sm btn-warning fa fa-key to-powers" title="权限设置"></button>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i>正在加载..</div>
			</div>
		</div>
	</div>



	<div class="inputer" id="roleCreator">
		<div class="animated zoomInUp">
			<div class="container-400">
				<form id="roleCreateForm" action="/edu/edu/index.php/Home/Role/aCreate">
					<div class="form-group">
						<label>角色名称</label>
						<input type="text" name="name" class="form-control" placeholder="如：院长" required />
					</div>
					<div class="form-group">
						<label>角色描述</label>
						<textarea type="text" name="desc" class="form-control"></textarea>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						&nbsp;&nbsp;<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;添&nbsp;加&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="roleCreator.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>


	<div class="inputer" id="roleUpdater">
		<div class="animated zoomInUp">
			<div class="container-400">
				<form id="roleUpdateForm" action="/edu/edu/index.php/Home/Role/aUpdate">
					<input type="text" name="rid" style="display:none" />
					<div class="form-group">
						<label>角色名称</label>
						<input type="text" name="name" class="form-control" placeholder="如：院长" required />
					</div>
					<div class="form-group">
						<label>角色描述</label>
						<textarea type="text" name="desc" class="form-control"></textarea>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						&nbsp;&nbsp;<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;保&nbsp;存&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="roleUpdater.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<div class="inputer" id="rolePowersSetter">
		<div>
			<div class="container-400 powersloading">
				<div class="loading"><i class="fa fa-spin fa-spinner"></i>&nbsp;数据加载中。。。</div>
				<form id="rolePowerSetForm" action="/edu/edu/index.php/Home/Role/aPowerSet" method="post">
					<input type="text" style="display:none" name="rid" />
					<div style="width:100%; max-height:300px; position:relative" k360-scroll-y="6">
						<div class="powers"></div>
					</div>
					<div class="margin-top-10">
						<button type="submit" class="btn btn-success btn-loading"><i class="fa fa-spin fa-spinner"></i>&nbsp;设置权限&nbsp;</button>
						<button type="button" class="btn btn-warning" onclick="rolePowersSetter.style.display='none'">&nbsp;&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
					</div>
				</form>
			</div>
		</div>
	</div>

</body>
</html>


<script>

	window._GETROLES = "/edu/edu/index.php/Home/Role/gRoles";
	window._REMOVEROLE = "/edu/edu/index.php/Home/Role/aRemove";
	window._GETPOWERS = "/edu/edu/index.php/Home/Role/gPowers"


	window.role_main_controller = {
		create: function () {
			var obj = {};

			//克隆一个tr
			var clonedTR = dataShower.children[0].cloneNode(true);
			//权限管理内容区域
			var powercontainer = rolePowersSetter.children[0].children[0];

			obj.init = function () {
				//点击创建角色
				roleCreateBtn.addEventListener("click", function () {
					roleCreator.style.display = "inline-block";
					inputerCenter(roleCreator);
				});
				//角色创建表单提交
				roleCreateForm.onsubmit = function () {
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						btn.disabled = false;
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						//刷新列表
						loadRoles();
						roleCreateForm.reset();
						roleCreator.style.display = "none";
					})
					.onerror(function (xhr, status, statusText) {
						btn.disabled = false;
						k360_popover.create().setTopPop(true).toast("创建角色错误，可能是网络原因，状态码：" + status);
					})
					.send();
					return false;
				}
				roleUpdateForm.onsubmit = function () {
					//loading
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					//提交
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						btn.disabled = false;
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						//刷新列表
						roleUpdateForm.reset();
						roleUpdater.style.display = "none";
						loadRoles();
						k360_popover.create().setTopPop(true).toast("角色信息已保存");
					})
					.onerror(function (xhr, status, statusText) {
						btn.disabled = false;
						k360_popover.create().setTopPop(true).toast("修改角色信息错误，可能是网络原因，状态码：" + status);
					})
					.send();
					return false;
				}
				rolePowerSetForm.onsubmit = function () {
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						btn.disabled = false;
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						k360_popover.create().setTopPop(true).toast("角色权限已更改");
						rolePowersSetter.style.display = 'none';
					})
					.onerror(function (xhr, status, statusText) {
						btn.disabled = false;
						k360_popover.create().setTopPop(true).toast("设置权限错误，可能是网络原因，状态码：" + status);
					})
					.send();
					return false;
				}

				loadRoles();
			}

			function loadRoles() {
				//删除旧数据
				while (dataShower.children.length > 0) {
					dataShower.removeChild(dataShower.children[0]);
				}
				//loading显示
				var loading = document.querySelector(".dataLoading");
				loading.style.display = "inline-block";
				//获取数据
				k360_http.create().setUrl(_GETROLES)
				.onsuccess(function (data, xhr) {
					loading.style.display = "none";
					if (data.reson) {
						k360_popover.create().setTopPop(true).toast(data.reson);
						return;
					}
					//显示列表
					for (var i in data.data) {
						var r = data.data[i];
						var tr = clonedTR.cloneNode(true);
						tr.children[0].innerHTML = r.name;
						tr.children[1].innerHTML = r.desc;
						setDataTREvent(tr.children[2], r);
						dataShower.appendChild(tr);
					}
				})
				.onerror(function (xhr, status, statusText) {
					loading.style.display = "none";
					k360_popover.create().setTopPop(true).toast("获取角色列表错误，可能是网络原因，状态码：" + status);
				})
				.send();
			}

			function setDataTREvent(td, role) {
				td.querySelector(".to-remove").addEventListener("click", function () {
					removeRole(role);
				});
				td.querySelector(".to-change").addEventListener("click", function () {
					roleUpdater.style.display = "inline-block";
					inputerCenter(roleUpdater);
					//设置数据
					roleUpdateForm.rid.value = role.id;
					roleUpdateForm.name.value = role.name;
					roleUpdateForm.desc.value = role.desc;
				});
				td.querySelector(".to-powers").addEventListener("click", function () {
					rolePowersSetter.style.display = "inline-block";
					inputerCenter(rolePowersSetter);
					//设置id
					rolePowerSetForm.rid.value = role.id;
					//加载权限信息
					loadPowers(role);
				});
			}

			function removeRole(role) {
				k360_popover.create().setTopPop(true).confirm("警告", "是否要删除角色“" + role.name + "”？", "删除", "取消")
				.setOnOk(function () {
					var loading = k360_popover.create().setTopPop(true).loading();
					k360_http.create().setUrl(_REMOVEROLE).addData({ id: role.id })
					.onsuccess(function (data, xhr) {
						loading.distroy();
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						//刷新
						loadRoles();
					})
					.onerror(function (xhr, status, statusText) {
						loading.distroy();
						k360_popover.create().setTopPop(true).toast("删除角色错误，可能是网络问题，状态码：" + status);
					})
					.send();
				});
			}

			function loadPowers(role) {
				//获取权限显示主区域
				var powersMain = rolePowersSetter.querySelector(".powers");
				//清空内容
				while (powersMain.children.length > 0) {
					powersMain.removeChild(powersMain.children[0]);
				}
				//显示加载中
				powercontainer.setAttribute("class", "powersloading");
				//获取数据
				k360_http.create().setUrl(_GETPOWERS).addData({ rid: role.id })
				.onsuccess(function (data, xhr) {
					if (data.reson) {
						k360_popover.create().setTopPop(true).toast(data.reson);
						rolePowersSetter.style.display = "none";
						powercontainer.setAttribute("class", "");
						return;
					}
					//绘制信息
					/*
					<div class="parent">
						<div class="item"><input type="checkbox" /><span>上级</span></div>
						<div class="children">
							<div class="item"><input type="checkbox" /><span>子级</span></div>
							<div class="item"><input type="checkbox" /><span>子级</span></div>
						</div>
					</div>
					*/
					for (var i in data.data) {
						//创建parent
						var p = data.data[i];
						var pDom = document.createElement("div");
						pDom.setAttribute("class", "parent");
						pDom.appendChild(createPowerItem(p));
						powersMain.appendChild(pDom);
						//children
						var children = p.children;
						if (children.length <= 0) {
							setPowerSetterEvent(pDom);
							continue;
						}
						var childrenDom = document.createElement("div");
						childrenDom.setAttribute("class", "children");
						pDom.appendChild(childrenDom);
						for (var j in children) {
							childrenDom.appendChild(createPowerItem(children[j]));
						}
						powercontainer.setAttribute("class", "");
						//再居中
						inputerCenter(rolePowersSetter);
						//事件处理
						setPowerSetterEvent(pDom);
					}
				})
				.onerror(function (xhr, status, statusText) {
					k360_popover.create().setTopPop(true).toast("加载权限信息错误，可能是网络原因，状态码：" + status);
					rolePowersSetter.style.display = "none";
					powercontainer.setAttribute("class", "");
				})
				.send();

			}

			function createPowerItem(func) {
				//<div class="item"><input type="checkbox" /><span>系统管理<span></div>
				var item = document.createElement("div");
				item.setAttribute("class", "item");
				var checkbox = document.createElement("input");
				checkbox.type = "checkbox";
				checkbox.name = "funcs[]";
				checkbox.value = func.id;
				checkbox.checked = (func.powered == 1);
				var span = document.createElement("span");
				span.innerHTML = func.name;
				item.appendChild(checkbox);
				item.appendChild(span);
				return item;
			}

			function setPowerSetterEvent(dom) {
				//获取dom的顶级checkbox并添加处理事件
				var parentDom = dom.children[0].children[0];
				parentDom.onchange = function () {
					if (this.checked) {
						checkAllChildren(true);
					} else {
						checkAllChildren(false);
					}
				}
				//获取所有子checkbox，并添加处理事件
				var childrenDom = dom.children[1].querySelectorAll("input");
				for (var i = 0; i < childrenDom.length; i++) {
					childrenDom[i].onchange = function () {
						if (this.checked) {
							parentDom.checked = true;
						} else {
							if (isAllUnChecked()) {
								parentDom.checked = false;
							}
						}
					}
				}
				//设置所有子checkbox是否选中
				function checkAllChildren(isCheck) {
					for (var i = 0; i < childrenDom.length; i++) {
						childrenDom[i].checked = isCheck;
					}
				}
				//是否所有的子checkbox都选中了
				function isAllUnChecked() {
					for (var i = 0; i < childrenDom.length; i++) {
						if (childrenDom[i].checked)
							return false;
					}
					return true;
				}
			}

			return obj;
		}

	};


	window.addEventListener("load", function () {
		role_main_controller.create().init();
	})


</script>