<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>科目管理</title>
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

	<css root="/edu/edu/Public/css/p-User">["detail"]</css>
	<!-- 当前页面css -->
	<link href="/edu/edu/Public/css/base/page.css" rel="stylesheet" />
	<!-- 当前页面js -->
	<script src="/edu/edu/Public/js/base/k360-show-table.js"></script>
	<script src="/edu/edu/Public/js/base/k360-bt-page.js"></script>
	<script src="/edu/edu/Public/js/base/func.js"></script>
	<script src="/edu/edu/Public/js/base/resp-head.js"></script>
	<script src="/edu/edu/Public/js/base/top-nav.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-tag.js"></script>
	<link href="/edu/edu/Public/css/base/animate.css" rel="stylesheet" />
	<link href="/edu/edu/Public/css/p-Subject/main.css" rel="stylesheet" />
	<script src="/edu/edu/Public/js/p-Subject/subject.js"></script>
</head>
<body>

	<div class="data-detail" style="overflow:hidden" k360-scroll-y="4">
		<div class="detail-img">
			<img src="/edu/edu/index.php/Home/Subject/gSubjectFace/id/-1" height="100%" id="faceImage" />
		</div>
		<div class="detail-info">
			<div class="detail-title">科目信息</div>
			<div class="detail-item">科目：<span name="name">xxxxx</span></div>
			<div class="detail-item">描述：<span name="desc">xxxxx</span></div>
			<div class="detail-item">添加人：<span name="username">xxxxx</span></div>
			<div class="detail-item">日期：<span name="time">xxxxx</span></div>
		</div>
		<div class="detail-btn">
			<button class="btn btn-primary btn-sm" id="setSubjectFaceBtn">添加封面</button>
		</div>
	</div>

	<div class="data-core">
		<!-- 科目列表 -->
		<div class="list-subject data-list">
			<div class="title">
				<div class="pull-left">科目列表</div>
				<button class="btn btn-link btn-sm pull-right" id="subjectAddBtn"><i class="fa fa-plus"></i>添加科目</button>
				<div class="clearfix"></div>
			</div>
			<div class="d-container" k360-scroll-y="3">
				<ul id="subjectShower">
					<li name="cloned" callback="onSubjectClick">
						<div name="name" class="name">xxxxx</div>
						<div class="handles">
							<button class="btn btn-sm btn-link" title="编辑" callback="onUpdateSubject"><i class="fa fa-pencil"></i></button>
							<button class="btn btn-sm btn-link" title="删除" callback="onRemoveSubject"><i class="fa fa-trash"></i></button>
						</div>
						<div style="clear:both"></div>
					</li>
				</ul>
				<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i>数据加载中。。。</div>
			</div>
		</div>
		<!-- 章节列表 -->
		<div class="list-chapter data-list">
			<div class="title">
				<div class="pull-left">章节列表<span class="chapter-subject-name">(C语言基础教程)</span></div>
				<button class="btn btn-link btn-sm pull-right" id="chapterSaveBtn"><i class="fa fa-save"></i>保存章节</button>
				<button class="btn btn-link btn-sm pull-right" id="chapterAddBtn"><i class="fa fa-plus"></i>添加章</button>
				<div class="clearfix"></div>
			</div>
			<div class="d-container" k360-scroll-y="3">
				<ol type="I" id="chapterShower">
					<!--<li>
						<button class="btn btn-link btn-sm"><i class="fa fa-trash"></i></button>
						<input type="text" value="概述" />
						<ol type="1" contenteditable="true">
							<li>hello world</li>
							<li>hello world</li>
							<li>hello world</li>
							<li>hello world</li>
						</ol>
					</li>-->
				</ol>
			</div>
		</div>
	</div>


	<!-- 创建科目 -->
	<div class="inputer" id="subjectCreator">
		<div class="animated zoomIn">
			<div class="container-400">
				<form action="/edu/edu/index.php/Home/Subject/aCreateSubject" method="post" id="subjectCreateForm">
					<div class="form-group">
						<label>名称</label>
						<input type="text" class="form-control" name="name" placeholder="如：数据库系统概论" required>
					</div>
					<div class="form-group">
						<label>描述</label>
						<textarea class="form-control" name="desc"></textarea>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;创&nbsp;建&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="subjectCreator.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 修改科目 -->
	<div class="inputer" id="subjectUpdater">
		<div class="animated zoomIn">
			<div class="container-400">
				<form action="/edu/edu/index.php/Home/Subject/aUpdateSubject" method="post" id="subjectUpdateForm">
					<input type="text" name="sid" style="display:none" />
					<div class="form-group">
						<label>名称</label>
						<input type="text" class="form-control" name="name" placeholder="如：数据库系统概论" required>
					</div>
					<div class="form-group">
						<label>描述</label>
						<textarea class="form-control" name="desc"></textarea>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;保&nbsp;存&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="subjectUpdater.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

</body>
</html>

<script>
	window.addEventListener("load", function () {
		course_subject_controller.create().init();
	});
</script>