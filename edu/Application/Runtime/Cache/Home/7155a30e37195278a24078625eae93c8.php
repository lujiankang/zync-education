<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>论文管理中心</title>
	<meta charset="utf-8" />
	<link rel='icon' href="/edu/Public/images/index.ico" />
	<!-- 移动端适应 -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- jQuery -->
	<script src="/edu/Public/js/jQuery/jquery-2.1.4.min.js"></script>
	<!-- Bootstrap -->
	<link href="/edu/Public/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
	<script src="/edu/Public/js/bootstrap/bootstrap.min.js"></script>
	<!-- 字体图标 -->
	<link href="/edu/Public/css/awesome/font-awesome.css" rel="stylesheet" />
	<!-- k360 -->
	<script src="/edu/Public/js/k360/k360-scroll-bar.js"></script>
	<script src="/edu/Public/js/k360/k360-http.js"></script>
	<script src="/edu/Public/js/k360/k360-popover.js"></script>

	<!-- 当前页面css -->
	<link href="/edu/Public/css/base/page.css" rel="stylesheet" />
	<!-- 当前页面js -->
	<script src="/edu/Public/js/base/k360-bt-page.js"></script>
	<script src="/edu/Public/js/base/func.js"></script>
	<script src="/edu/Public/js/base/resp-head.js"></script>
</head>
<body>
	<!-- 头部 -->
	<div class="row header">
		<div class="col-md-12">
			<div class="header-close" onclick="top.respShower.style.display = 'none'"><i class="fa fa-close"></i></div>
			<div class="fa fa-sitemap icon"></div>
			<span class="title">论文管理中心</span>
			<div class="header-menu"><i class="fa fa-reorder"></i></div>
		</div>
	</div>

	<!-- 顶部操作 -->
	<div class="row handles margin-top-20">
		<div class="col-md-6 top-btns">
			<button type="button" class="btn btn-danger" id="essayCreateBtn">新建题目</button>
			<button class="handle-item btn btn-danger btn-filter" for="essayfilter">论文筛选</button>
			<div class="item-group top-filter" filter="essayfilter">
				<select class="form-control" style="width:150px" id="essayGrade">
					<?php if(is_array($grades)): $i = 0; $__LIST__ = $grades;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["grade"]); ?>"><?php echo ($vo["grade"]); ?>级</option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
				<button class="btn btn-warning btn-group" id="viewEssayBtn">查看题目</button>
				<div class="float-clear"></div>
			</div>
			<button class="btn btn-success">使用帮助</button>
		</div>
		<div class="col-md-6 top-search">
			<div class="handle-item">
				<form class="item-group pull-right" id="searchForm">
					<input type="text" name="key" class="form-control" placeholder="关键字：学生姓名，学生学号，论文题目" style="width:300px">
					<button type="submit" class="btn btn-primary btn-group"><i class="fa fa-search">&nbsp;</i><span>检索</span></button>
					<div class="float-clear"></div>
				</form>
			</div>
			<div class="float-clear"></div>
		</div>
	</div>

	<!-- 主体 -->
	<div class="row main margin-top-20">
		<div class="col-md-12 table-responsive">
			<table class="table table-bordered table-hover">
				<thead>
					<tr>
						<th>学生</th>
						<th>题目</th>
						<th>终稿</th>
						<th>新稿</th>
						<th width="175">操作</th>
					</tr>
				</thead>
				<tbody id="dataShower">
					<tr>
						<td>xxxx</td>
						<td>xxxx</td>
						<td>是</td>
						<td>是</td>
						<td>
							<button class="btn btn-sm btn-danger fa fa-trash to-remove" title="删除题目"></button>
							<button class="btn btn-sm btn-danger fa fa-pencil to-change" title="修改题目"></button>
							<button class="btn btn-sm btn-warning fa fa-hourglass-3 to-info" title="查看论文完成进度"></button>
							<button class="btn btn-sm btn-success fa fa-send to-message" title="发送消息给学生"></button>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i>正在加载..</div>
		</div>
	</div>

	<!-- 分页控件 -->
	<div class="row page margin-top-20">
		<div class="col-md-12" style="text-align:center">
			<div style="display:inline-block">
				<button class="btn btn-success goto-last">上一页</button>
				<button class="btn btn-success goto-next">下一页</button>
				<div class="item-group">
					<input type="text" class="form-control goto-input" value="1" style="width:50px; display:inline-block" />
					<button class="btn btn-primary goto-button">&nbsp;跳转&nbsp;</button>
				</div>
			</div>
			<div style="display:inline-block; margin-left:30px" class="k-page-info">
				共<span class="data-num"> - </span>条数据，共<span class="page-num"> - </span>页，第<span class="page-now"> - </span>页
			</div>
		</div>
	</div>

	<!-- 添加题目 -->
	<div class="inputer" id="essayCreator">
		<div>
			<div class="container-400">
				<form id="essayCreateForm" action="/edu/index.php/Home/Essay/aCreate">
					<div class="form-group">
						<label>题目</label>
						<input type="text" class="form-control" placeholder="如：关于xxxxxx的研究" name="name" required>
					</div>
					<div class="form-group">
						<label>学生班级</label>
						<select class="form-control" name="class">
							<?php if(is_array($class)): $i = 0; $__LIST__ = $class;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["grade"]); ?>级<?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
						</select>
					</div>
					<div class="form-group">
						<label>选择学生</label>
						<select class="form-control" required name="student">
						</select>
					</div>
					<div class="form-group">
						<label>说明</label>
						<textarea class="form-control" name="desc"></textarea>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						&nbsp;&nbsp;<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;创&nbsp;建&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="essayCreator.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 修改题目 -->
	<div class="inputer" id="essayUpdater">
		<div>
			<div class="container-400">
				<form id="essayUpdateForm" action="/edu/index.php/Home/Essay/aUpdate">
					<input type="text" name="eid" style="display:none" />
					<div class="form-group">
						<label>题目</label>
						<input type="text" class="form-control" placeholder="如：关于xxxxxx的研究" name="name" required>
					</div>
					<div class="form-group">
						<label>学生班级</label>
						<select class="form-control" name="class">
							<?php if(is_array($class)): $i = 0; $__LIST__ = $class;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["grade"]); ?>级<?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
						</select>
					</div>
					<div class="form-group">
						<label>选择学生</label>
						<select class="form-control" required name="student">
						</select>
					</div>
					<div class="form-group">
						<label>说明</label>
						<textarea class="form-control" name="desc"></textarea>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						&nbsp;&nbsp;<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;创&nbsp;建&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="essayUpdater.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

</body>
</html>


<script>

	window._GETSTUDENTBYCLASS = "/edu/index.php/Home/User/gStudentsOfClass";
	window._GETESSAYS = "/edu/index.php/Home/Essay/gEssaies";
	window._DELETEESSAY = "/edu/index.php/Home/Essay/aRemove";
	window._DOWNLOADURL = "/edu/index.php/Home/Essay/pDownload";
	window._SETFINISH = "/edu/index.php/Home/Essay/aFinish";
	window._SHOWINFO = "/edu/index.php/Home/Essay/pEssayInfo"
	window._APP = "/edu/index.php";

	window.essay_main_controller = {
		create: function () {
			var obj = {};
			var bufferClass = 0;			//避免没用的加载
			var bufferForm = null;
			var page = k360_bt_page.create(".page");
			var grade = essayGrade.value;
			var key = "";
			var clonedTR = dataShower.children[0].cloneNode(true);
			/*
			功能：初始化方法
			*/
			obj.init = function () {
				//分页控制
				page.onpage(function (to) {
					loadEssays(to);
				});
				page.onerror(function (err) {
					k360_popover.create().toast(err);
				})
				//年级改变
				essayCreateForm.class.onchange = function () {
					//获取某个年级下的学生
					loadStudentToSelect(essayCreateForm);
				}
				essayUpdateForm.class.onchange = function () {
					//获取某个年级下的学生
					loadStudentToSelect(essayUpdateForm);
				}
				//点击新建按钮
				essayCreateBtn.onclick = function () {
					essayCreator.style.display = "inline-block";
					inputerCenter(essayCreator);
					loadStudentToSelect(essayCreateForm);
				}
				//创建表单提交
				essayCreateForm.onsubmit = function () {
					var loading = this.querySelector("button[type='submit']");
					loading.disabled = true;
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						loading.disabled = false;
						if (data.reson) {
							k360_popover.create().toast(data.reson);
							return;
						}
						k360_popover.create().confirm("成功", "题目已经创建了，是否继续创建？", "继续创建", "不用了")
						.setOnCancel(function () {
							essayCreator.style.display = "none";
							essayCreateForm.reset();
							loadEssays(page.getCurPage());
						});
					})
					.onerror(function (xhr, status, statusText) {
						loading.disabled = false;
						k360_popover.create().toast("创建题目错误，可能是网络原因，状态码：" + status);
					})
					.send();
					return false;
				}
				//修改表单提交
				essayUpdateForm.onsubmit = function () {
					var loading = this.querySelector("button[type='submit']");
					loading.disabled = true;
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						loading.disabled = false;
						if (data.reson) {
							k360_popover.create().toast(data.reson);
							return;
						}
						//刷新列表
						loadEssays(page.getCurPage());
						essayUpdater.style.display = "none";
					})
					.onerror(function (xhr, status, statusText) {
						loading.disabled = false;
						k360_popover.create().toast("修改题目错误，可能是网络原因，状态码：" + status);
					})
					.send();
					return false;
				}
				//查看制定年级下的题目
				viewEssayBtn.onclick = function () {
					grade = essayGrade.value;
					key = "";
					searchForm.key.value = "";
					loadEssays(0);
					hidefilter();
				}
				//关键字搜索
				searchForm.onsubmit = function () {
					key = this.key.value;
					key || (key = "");
					this.key.focus();
					loadEssays(0);
					return false;
				}
				//默认加载一次题目
				loadEssays(0);
			}
			//加载题目列表
			function loadEssays(toPage) {
				if (page.isLocked()) return;
				page.lock();
				var loading = document.querySelector(".dataLoading");
				loading.style.display = "inline-block";
				//删除旧数据
				while (dataShower.children.length > 0) {
					dataShower.removeChild(dataShower.children[0]);
				}
				k360_http.create().setUrl(_GETESSAYS).addData({ grade: grade, page: toPage, key: key })
				.onsuccess(function (data, xhr) {
					page.unlock();
					loading.style.display = "none";
					if (data.reson) {
						k360_popover.create().toast(data.reson);
						return;
					}
					//分页
					page.setInfo(data.data.pages, data.data.count, toPage);
					//显示数据
					for (var i in data.data.essays) {
						var essay = data.data.essays[i];
						var tr = clonedTR.cloneNode(true);
						tr.children[0].innerHTML = essay.studentname;
						tr.children[1].innerHTML = essay.name;
						tr.children[2].innerHTML = (essay.finish == 0) ? "否" : "是";
						tr.children[3].innerHTML = (essay.hasnew == 0) ? "否" : "是";
						tr.children[4].querySelector(".to-remove").disabled = (essay.finish == 1);
						tr.children[4].querySelector(".to-change").disabled = (essay.finish == 1);
						setDataTREvent(tr.children[4], essay);
						dataShower.appendChild(tr);
					}
				})
				.onerror(function (xhr, status, statusText) {
					page.unlock();
					loading.style.display = "none";
					k360_popover.create().toast("加载题目错误，可能是网络原因，状态码：" + status);
				})
				.send();
			}
			//设置每行数据的事件
			function setDataTREvent(td, essay) {
				td.querySelector(".to-remove").onclick = function () {
					k360_popover.create().confirm("严重警告", "您是否要删除“" + essay.name + "”吗？很有可能学生正在完成论文，如果删除学生将无法提交论文，请慎重删除。", "删除", "取消")
					.setOnOk(function () {
						var loading = k360_popover.create().loading();
						k360_http.create().setUrl(_DELETEESSAY).addOne("id", essay.id)
						.onsuccess(function (data, xhr) {
							loading.distroy();
							if (data.reson) {
								k360_popover.create().toast(data.reson);
								return;
							}
							//刷新列表
							loadEssays(page.getCurPage());
						})
						.onerror(function (xhr, status, statusText) {
							loading.distroy();
							k360_popover.create().toast("删除题目失败，可能是网络原因，状态码：" + status);
						})
						.send();
					});
				}
				td.querySelector(".to-change").onclick = function () {
					k360_popover.create().confirm("严重警告", "是否要修改“" + essay.name + "”的基本信息，很有可能学生正在完成论文，此时修改信息很有可能是不妥的，请慎重操作", "继续", "取消")
					.setOnOk(function () {
						essayUpdater.style.display = "inline-block";
						inputerCenter(essayUpdater);
						essayUpdateForm.eid.value = essay.id;
						essayUpdateForm.name.value = essay.name;
						essayUpdateForm.class.value = essay.class;
						loadStudentToSelect(essayUpdateForm, essay.student);
						essayUpdateForm.desc.value = essay.desc;
					});
				}
				td.querySelector(".to-message").onclick = function () {
					top.chat.sendMessage("student", essay.student, essay.studentname);
				}
				td.querySelector(".to-info").onclick = function () {
					window.location.href = _SHOWINFO + "/essay/" + essay.id;
				}
			}
			//添加/更改题目时，年级改变时获取学生数据
			function loadStudentToSelect(form, selected) {
				if (bufferClass == form.class.value && bufferForm == form) {
					selected && (bufferForm.student.value = selected);
					return;
				}
				form.student.innerHTML = "<option disabled>数据加载中。。。</option>";
				k360_http.create().setUrl(_GETSTUDENTBYCLASS).addOne("class", form.class.value)
				.onsuccess(function (data, xhr) {
					if (data.reson) {
						form.student.innerHTML = "<option disabled>" + data.reson + "</option>";
						return;
					}
					form.student.innerHTML = "";
					for (var i in data.data) {
						var student = data.data[i];
						form.student.innerHTML += "<option value='" + student .id + "'>" + student.name + "</option>";
					}
					bufferClass = form.class.value;
					bufferForm = form;
					selected && (bufferForm.student.value = selected);
				})
				.onerror(function (xhr, status, statusText) {
					form.student.innerHTML = "<option disabled>数据加载失败，状态码：" + status + "</option>";
				})
				.send();
			}
			return obj;
		}
	};

	window.addEventListener("load", function () {
		essay_main_controller.create().init();
	});
</script>