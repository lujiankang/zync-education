<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>论文管理中心</title>
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

	<style>
		.timeline{
			position:relative;
			width:100%;
			height:400px;
		}
		.timeline >.timeline-item{
			position:relative;
			min-width:700px;
			min-height:150px;
			width:100%;
		}
		.timeline >.timeline-item >.timeline-line{
			position:absolute;
			left:120px;
			top:0px;
			width:3px;
			height:100%;
			background:#34495E;
		}
		.timeline >.timeline-item >.timeline-pic{
			position:absolute;
			left:101px;
			top:0px;
			width:40px;
			height:40px;
			background:#34495E;
			border-radius:40px;
			text-align:center;
			line-height:40px;
			color:#FFF;
		}
		.timeline >.timeline-item >.timeline-time{
			position:absolute;
			left:10px;
			top:0px;
			line-height:40px;
		}
		.timeline >.timeline-item >.timeline-shower{
			margin-left:120px;
			min-width:400px;
			max-width:60%;
			transform:translateX(40px);
			padding-bottom:50px;
		}
		.timeline >.timeline-item >.timeline-pao{
			position:absolute;
			left:152px;
			top:15px;
			width:0px;
			height:0px;
			border-top: 6px solid transparent;
			border-right: 8px solid #34495E;
			border-bottom: 6px solid transparent;
		}
		.timeline >.timeline-item >.timeline-shower >.timeline-title{
			color:#FFF;
			padding:0px 10px;
			line-height:40px;
			background:#34495E;
			border-top-left-radius:3px;
			border-top-right-radius:3px;
		}
		.timeline >.timeline-item >.timeline-shower >.timeline-content{
			background:#FBFCFC;
			min-height:60px;
			border:solid 1px #BDC3C7;
			border-top:none;
			border-bottom-left-radius:3px;
			border-bottom-right-radius:3px;
			padding:10px 20px;
		}
		.timeline >.timeline-item >.timeline-shower >.timeline-space{
			width:100%;
			height:50px;
		}

	</style>
</head>
<body>
	<!-- 头部 -->
	<div class="row header">
		<div class="col-md-12">
			<div class="header-close" onclick="history.go(-1)"><i class="fa fa-chevron-left"></i></div>
			<div class="fa fa-battery-2 icon"></div>
			<span class="title">论文详情<span>——<?php echo ($essay["name"]); ?></span></span>
			<div class="header-menu"><i class="fa fa-reorder"></i></div>
		</div>
	</div>

	<!-- 顶部操作 -->
	<div class="row handles margin-top-20">
		<div class="col-sm-12 top-btns">
			<button class="handle-item btn btn-warning btn-goback" onclick="history.go(-1)"><i class="fa fa-angle-double-left"></i>&nbsp;&nbsp;返回上级</button>
			<button class="handle-item btn btn-danger" id="studentSubmitBtn" <?php echo ($hideStr); ?>><i class="fa fa-upload"></i>&nbsp;&nbsp;提交论文</button>
			<button class="handle-item btn btn-success" id="table8Btn"><i class="fa fa-newspaper-o"></i>&nbsp;&nbsp;查看8个表</button>
		</div>
	</div>

	<!-- 时间轴 -->
	<div class="row handles margin-top-20">
		<div class="col-md-12">
			<div class="timeline" style="overflow-x:auto">
				<!-- 创建论文时间轴单元 -->
				<div class="timeline-item info-create">
					<div class="timeline-shower">
						<div class="timeline-title"><span datain="teachername">xxxx</span> 创建论文题目《<span datain="name">xxxxxxx</span>》</div>
						<div class="timeline-content">
							<div datain="desc">xxxx</div>
							<div style="margin-top:10px">
								<!--<button class="btn btn-link"><i class="fa fa-pencil"></i>修改题目</button>-->
								<!--<button class="btn btn-link"><i class="fa fa-download"></i>题目附件（如果有附件）</button>-->
							</div>
						</div>
					</div>
					<div class="timeline-time" datain="time">2015-03-12</div>
					<div class="timeline-line"></div>
					<div class="timeline-pic"><i class="fa fa-fire"></i></div>
					<div class="timeline-pao"></div>
				</div>
				<!-- 教师恢复时间轴单元 -->
				<div class="timeline-item info-reply">
					<div class="timeline-shower">
						<div class="timeline-title"><span datain="teachername"></span> 对 <span datain="studentname"></span> 论文的修改意见</div>
						<div class="timeline-content">
							<div datain="desc">xxxxxxxxxxxxxxxxxxxxxxxxx</div>
							<div style="margin-top:10px">
								<button class="btn btn-link update" showto="teacher" dataid="xxx" datatype="reply"><i class="fa fa-pencil"></i>修改回复</button>
								<button class="btn btn-link delete" showto="teacher" dataid="xxx" datatype="reply"><i class="fa fa-trash"></i>删除回复</button>
								<button class="btn btn-link deletefile" showto="teacher" dataid="xxx" datatype="reply"><i class="fa fa-times"></i>删除附件</button>
								<button class="btn btn-link download" btn="download" dataid="xxx" datatype="reply"><i class="fa fa-download"></i>下载附件</button>
								<button class="btn btn-link preview" btn="preview" dataid="xxx" datatype="reply"><i class="fa fa-tv"></i>简单预览</button>
							</div>
						</div>
					</div>
					<div class="timeline-time">2015-03-13</div>
					<div class="timeline-line"></div>
					<div class="timeline-pic"><i class="fa fa-magic"></i></div>
					<div class="timeline-pao"></div>
				</div>
				<!-- 学生提交论文时间轴单元 -->
				<div class="timeline-item info-submit">
					<div class="timeline-shower">
						<div class="timeline-title"><span datain="studentname">xxxx</span> 提交了论文</div>
						<div class="timeline-content">
							<div datain="desc">xxxxxxxxxxxxxxxxxxxx</div>
							<div style="margin-top:20px">
								<button class="btn btn-link update" showto="student" dataid="xxx" datatype="submit"><i class="fa fa-download"></i>修改信息</button>
								<button class="btn btn-link delete" showto="student" dataid="xxx" datatype="submit"><i class="fa fa-download"></i>删除论文</button>
								<button class="btn btn-link download" btn="download" dataid="xxx" datatype="submit"><i class="fa fa-download"></i>下载附件</button>
								<button class="btn btn-link reply" showto="teacher" dataid="xxx" datatype="submit"><i class="fa fa-reply"></i>回复并建议</button>
								<button class="btn btn-link preview" btn="preview" dataid="xxx" datatype="submit"><i class="fa fa-tv"></i>简单预览</button>
							</div>
						</div>
					</div>
					<div class="timeline-time" datain="time">2015-03-14</div>
					<div class="timeline-line"></div>
					<div class="timeline-pic"><i class="fa fa-upload"></i></div>
					<div class="timeline-pao"></div>
				</div>
				<!-- 其他内容时间轴单元 -->
				<div class="timeline-item info-others">
					<div class="timeline-shower">
						<div class="timeline-title">论文转移</div>
						<div class="timeline-content">论文由指导老师 王亚 转到 刘彦兵，以后此论文将由刘彦兵做为指导老师</div>
					</div>
					<div class="timeline-time">2015-03-15</div>
					<div class="timeline-line"></div>
					<div class="timeline-pic"><i class="fa fa-random"></i></div>
					<div class="timeline-pao"></div>
				</div>
			</div>
		</div>
	</div>


	<!-- 修改回复 -->
	<div class="inputer" id="techerReplyUpdater">
		<div>
			<div class="container-400">
				<form id="replyUpdateForm" action="/edu/edu/index.php/Home/Essay/aUpdateReply" method="post">
					<input type="text" name="rid" style="display:none" />
					<div class="form-group">
						<label>回复内容</label>
						<textarea name="content" class="form-control" placeholder="你想对该学生说些什么？" style="height:100px"></textarea>
					</div>
					<div class="form-group">
						<label>回复文件</label>
						<input name="file" type="file" style="width:100%; height:35px" />
					</div>
					<button type="submit" class="btn btn-success btn-loading" style="width:100px"><i class="fa fa-spinner fa-spin"></i>提交</button>
					<button type="button" class="btn btn-warning" style="width:60px" onclick="techerReplyUpdater.style.display='none'">取消</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 修改论文 -->
	<div id="studentSubmitUpdater" class="inputer">
		<div>
			<div class="container-400">
				<form id="submitUpdateForm" action="/edu/edu/index.php/Home/Essay/aUpdateSubmit" method="post">
					<input type="text" name="rid" style="display:none" />
					<div class="form-group">
						<label>论文描述</label>
						<textarea name="content" class="form-control" placeholder="你想对此论文做点什么描述呢？" style="height:100px"></textarea>
					</div>
					<div class="form-group">
						<label>论文文件(<span style="color:#ff6a00">如果没有文件则保留原来文件</span>)</label>
						<input name="file" type="file" style="width:100%; height:35px" />
					</div>
					<button type="submit" class="btn btn-success btn-loading" style="width:100px"><i class="fa fa-spinner fa-spin"></i>提交</button>
					<button type="button" class="btn btn-warning" style="width:80px" onclick="studentSubmitUpdater.style.display = 'none'">取消</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 提交论文 -->
	<div id="studentSubmitCreator" class="inputer">
		<div>
			<div class="container-400">
				<form id="submitCreateForm" action="/edu/edu/index.php/Home/Essay/aCreateSubmit/essay/<?php echo ($essay["id"]); ?>" method="post">
					<div class="form-group">
						<label>论文描述</label>
						<textarea name="content" class="form-control" placeholder="你想对此论文做点什么描述呢？" style="height:100px"></textarea>
					</div>
					<div class="form-group">
						<label>论文文件(<span style="color:#ff6a00">如果没有文件则保留原来文件</span>)</label>
						<input name="file" type="file" style="width:100%; height:35px" required />
					</div>
					<button type="submit" class="btn btn-success btn-loading" style="width:100px"><i class="fa fa-spinner fa-spin"></i>提交</button>
					<button type="button" class="btn btn-warning" style="width:80px" onclick="studentSubmitCreator.style.display = 'none'">取消</button>
				</form>
			</div>
		</div>
	</div>
</body>
</html>

<script>
	window._APP = "/edu/edu/index.php"

	window._SHOWTABLE8 = "/edu/edu/index.php/Home/Essay/pTable8/essay/<?php echo ($essay["id"]); ?>";
	window._GETTIMELINE = "/edu/edu/index.php/Home/Essay/gEssayTimeLine/essay/<?php echo ($essay["id"]); ?>";
	window._DELETEREPLY = "/edu/edu/index.php/Home/Essay/aDeleteReply";
	window._DELETEREPLYFILE = "/edu/edu/index.php/Home/Essay/aDeleteReplyFile";
	window._DELETESUBMIT = "/edu/edu/index.php/Home/Essay/aDeleteSubmit";

	window.pageVisitor = "<?php echo ($viewer); ?>";

	window.essay_info_controller = {
		create: function () {
			var obj = {};

			var createDom = document.querySelector(".info-create").cloneNode(true);
			var replyDom = document.querySelector(".info-reply").cloneNode(true);
			var submitDom = document.querySelector(".info-submit").cloneNode(true);
			var othersDom = document.querySelector(".info-others").cloneNode(true);
			var timelineDom = document.querySelector(".timeline");
			var bufferScrolltop = 0;
			

			var timelinegetter = {
				clear: function () {
					timelinegetter.indexs = [0, 0, 0, 0];
				},
				next: function () {
					var data = [timelinegetter.data.essay, timelinegetter.data.submit, timelinegetter.data.reply, timelinegetter.data.other];
					var dateArr = [];
					dateArr.push((data[0].length > timelinegetter.indexs[0]) ? data[0][timelinegetter.indexs[0]].time : null);
					dateArr.push((data[1].length > timelinegetter.indexs[1]) ? data[1][timelinegetter.indexs[1]].time : null);
					dateArr.push((data[2].length > timelinegetter.indexs[2]) ? data[2][timelinegetter.indexs[2]].time : null);
					dateArr.push((data[3].length > timelinegetter.indexs[3]) ? data[3][timelinegetter.indexs[3]].time : null);
					var index = getMaxMinDate(dateArr).minIndex;
					var needData = data[index][timelinegetter.indexs[index]];
					if (!needData) return null;
					timelinegetter.indexs[index]++;
					return { index: index, data: needData };
				},
				getReply: function (id) {
					var replys = timelinegetter.data.reply;
					for (var i in replys) {
						if (replys[i].id == id)
							return replys[i];
					}
					return null;
				},
				getSubmit: function (id) {
					var submit = timelinegetter.data.submit;
					for (var i in submit) {
						if (submit[i].id == id)
							return submit[i];
					}
					return null;
				},
				data: null,
				indexs: [0, 0, 0, 0]
			};

			var timelineData = null;

			obj.init = function () {
				//8个表按钮点击
				table8Btn.onclick = function () {
					window.open(_SHOWTABLE8 + "/type/" + pageVisitor, "_blank");
				}
				//获取一次信息
				loadEssayInfo();
				//更改回复表单提交
				replyUpdateForm.onsubmit = function () {
					var loading = this.querySelector("button[type='submit']");
					loading.disabled = true;
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						loading.disabled = false;
						if (data.reson) {
							k360_popover.create().toast(data.reson);
							return;
						}
						techerReplyUpdater.style.display = "none";
						loadEssayInfo();
					})
					.onerror(function (xhr, status, statusText) {
						loading.disabled = false;
						k360_popover.create().toast("保存数据错误，可能是网络问题，状态码：" + status);
					})
					.send();
					return false;
				}
				//更改论文表单提交
				submitUpdateForm.onsubmit = function () {
					var loading = this.querySelector("button[type='submit']");
					loading.disabled = true;
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						loading.disabled = false;
						if (data.reson) {
							k360_popover.create().toast(data.reson);
							return;
						}
						studentSubmitUpdater.style.display = "none";
						loadEssayInfo();
					})
					.onerror(function (xhr, status, statusText) {
						loading.disabled = false;
						k360_popover.create().toast("保存数据错误，可能是网络问题，状态码：" + status);
					})
					.send();
					return false;
				};
				//点击提交论文按钮
				studentSubmitBtn.onclick = function () {
					studentSubmitCreator.style.display = "inline-block";
					inputerCenter(studentSubmitCreator);
				}
				submitCreateForm.onsubmit = function () {
					var loading = this.querySelector("button[type='submit']");
					loading.disabled = true;
					k360_http.create().addForm(this)
					.onsuccess(function (data, xhr) {
						loading.disabled = false;
						if (data.reson) {
							k360_popover.create().toast(data.reson);
							return;
						}
						loadEssayInfo();
						studentSubmitCreator.style.display = "none";
					})
					.onerror(function (xhr, status, statusText) {
						loading.disabled = false;
						k360_popover.create().toast("提交论文错误，可能是网络问题，状态码：" + status);
					})
					.send();
					return false;
				}
				return obj;
			}
			//加载论文时间轴信息
			function loadEssayInfo() {
				bufferScrolltop = getWindowScrollTop();
				removeChildrenAll(timelineDom);
				k360_http.create().setUrl(_GETTIMELINE)
				.onsuccess(function (data, xhr) {
					if (data.reson) {
						k360_popover.create().toast(data.reson);
						return;
					}
					//逐个创建时间轴条目
					timelinegetter.data = data.data;
					var doms = [createDom, submitDom, replyDom, othersDom];
					timelinegetter.clear();
					while (true) {
						var item = timelinegetter.next();
						if (!item) break;
						var index = item.index;
						var aDate = item.data;
						var dom = doms[index].cloneNode(true);
						for (var i in aDate) {
							if (i == "id") {
								var dataid = dom.querySelectorAll("[dataid]");
								for (var j = 0; j < dataid.length; j++) {
									dataid.item(j).setAttribute("dataid", aDate.id);
								}
							}
							var str = (i == "desc" && (!aDate[i] || aDate[i] == "")) ? "没有任何的附加信息。" : aDate[i];
							var datain = dom.querySelector("[datain='" + i + "']");
							datain && (datain.innerHTML = str.replace(/\n/gim, "<br />"));
						}
						var downloadBtn = dom.querySelector("[btn='download']");
						downloadBtn && (downloadBtn.disabled = (!aDate.file || aDate.file == 0));
						var previewBtn = dom.querySelector("[btn='preview']");
						previewBtn && (previewBtn.disabled = (!aDate.file || aDate.file==0));
						timelineDom.appendChild(dom);
					}
					setBtnEvents();
					setWindowScrollTop(bufferScrolltop);
					//根据老师和学生访问隐藏相应的按钮
					show2teacher2student();
				})
				.onerror(function (xhr, status, statusText) {
					k360_popover.create().toast("获取数据错误，可能是网络原因，状态码：" + status)
				})
				.send();
			}
			//设置按钮事件
			function setBtnEvents() {
				//教师修改回复按钮
				var teacher_replyUpdateBtns = document.querySelectorAll("[datatype='reply'].update");
				//教师删除回复按钮
				var teacher_replyDeleteBtns = document.querySelectorAll("[datatype='reply'].delete");
				//教师下载自己的附件按钮
				var teacher_replyDownloadBtns = document.querySelectorAll("[datatype='reply'].download");
				//教师删除自己的附件
				var teacher_replyDelFileBtn = document.querySelectorAll("[datatype='reply'].deletefile");
				//教师下载学生提交的附件按钮
				var teacher_submitDownloadBtns = document.querySelectorAll("[datatype='submit'].download");
				//教师回复学生按钮
				var teacher_submitReplyBtns = document.querySelectorAll("[datatype='submit'].reply");
				//预览按钮
				var replyPreviewBtns = document.querySelectorAll("[datatype='reply'].preview");
				var submitPreviewBtns = document.querySelectorAll("[datatype='submit'].preview");
				//学生修改论文按钮
				var student_repeatUpdateBtns = document.querySelectorAll("[datatype='submit'].update");
				//学生删除论文按钮
				var student_repeatDeleteBtns = document.querySelectorAll("[datatype='submit'].delete");
				//各种事件处理
				btnEventCallback(teacher_replyUpdateBtns, function (id) {
					//更改回复
					replyUpdateForm.reset();
					techerReplyUpdater.style.display = "inline-block";
					replyUpdateForm.rid.value = id;
					replyUpdateForm.content.value = timelinegetter.getReply(id).desc;
					inputerCenter(techerReplyUpdater);
				});
				btnEventCallback(teacher_replyDeleteBtns, function (id) {
					//删除回复
					k360_popover.create().confirm("警告", "是否要删除该条回复", "删除", "取消")
					.setOnOk(function () {
						var loading = k360_popover.create().loading();
						k360_http.create().setUrl(_DELETEREPLY).addOne("rid", id)
						.onsuccess(function (data, xhr) {
							loading.distroy();
							if (data.reson) {
								k360_popover.create().toast(data.reson);
								return;
							}
							loadEssayInfo();
							k360_popover.create().tip("已删除");
						})
						.onerror(function (xhr, status, statusText) {
							loading.distroy();
							k360_popover.create().toast("删除回复失败，可能是网络原因，状态码：" + status)
						})
						.send();
					});
				});
				btnEventCallback(teacher_replyDownloadBtns, function (id) {
					//下载自己的附件
					var fileid = timelinegetter.getReply(id).file;
					downloadFile([fileid]);
				});
				btnEventCallback(teacher_replyDelFileBtn, function (id) {
					//删除自己的附件
					k360_popover.create().confirm("警告", "是否要删除该回复下的附件？", "删除", "不删除")
					.setOnOk(function () {
						var loading = k360_popover.create().loading();
						k360_http.create().setUrl(_DELETEREPLYFILE).addOne("rid", id)
						.onsuccess(function (data, xhr) {
							loading.distroy();
							if (data.reson) {
								k360_popover.create().toast(data.reson);
								return;
							}
							loadEssayInfo();
							k360_popover.create().tip("已删除");
						})
						.onerror(function (xhr, status, statusText) {
							loading.distroy();
							k360_popover.create().toast("删除回复失败，可能是网络原因，状态码：" + status)
						})
						.send();
					});
				});
				btnEventCallback(teacher_submitDownloadBtns, function (id) {
					//下载学生的附件
					var fileid = timelinegetter.getSubmit(id).file;
					downloadFile([fileid]);
				});
				btnEventCallback(teacher_submitReplyBtns, function (id) {
					//回复学生
					replyUpdateForm.reset();
					techerReplyUpdater.style.display = "inline-block";
					replyUpdateForm.rid.value = id;
					inputerCenter(techerReplyUpdater);
				});
				btnEventCallback(replyPreviewBtns, function (id) {
					//预览
					var data = timelinegetter.getReply(id);
					top.preview.show(data.file);
				});
				btnEventCallback(submitPreviewBtns, function (id) {
					//预览
					var data = timelinegetter.getSubmit(id);
					top.preview.show(data.file);
				});
				btnEventCallback(student_repeatUpdateBtns, function (id) {
					//学生修改论文
					submitUpdateForm.reset();
					studentSubmitUpdater.style.display = "inline-block";
					submitUpdateForm.rid.value = id;
					submitUpdateForm.content.value = timelinegetter.getSubmit(id).desc;
					inputerCenter(studentSubmitUpdater);
				});
				btnEventCallback(student_repeatDeleteBtns, function (id) {
					//学生删除论文
					var loading = k360_popover.create().loading();
					k360_popover.create().confirm("警告", "是否删除该论文？", "删除")
					.setOnOk(function () {
						k360_http.create().setUrl(_DELETESUBMIT).addOne("rid", id)
						.onsuccess(function (data, xhr) {
							loading.distroy();
							if (data.reson) {
								k360_popover.create().toast(data.reson);
								return;
							}
							loadEssayInfo();
						})
						.onerror(function (xhr, status, statusText) {
							loading.distroy();
							k360_popover.create().toast("删除论文错误，可能是网络问题，状态码：" + status);
						})
						.send();
					});
				});
			}
			//按键事件回调通用调用方法
			function btnEventCallback(selectors, callback) {
				for (var i = 0; i < selectors.length; i++) {
					selectors.item(i).onclick || (selectors.item(i).onclick = function () {
						var id = this.attributes.getNamedItem("dataid").value;
						callback(id);
					});
				}
			}
			//根据学生/老师访问隐藏特定按钮
			function show2teacher2student() {
				var dict = { teacher: "student", student: "teacher" };
				var needHide = dict[pageVisitor];
				var btns = document.querySelectorAll("[showto='" + needHide + "']");
				for (var i = 0; i < btns.length; i++) {
					btns.item(i).style.display = "none";
				}
			}
			return obj;
		}

	};

	window.addEventListener("load", function () {
		essay_info_controller.create().init();
	})


</script>