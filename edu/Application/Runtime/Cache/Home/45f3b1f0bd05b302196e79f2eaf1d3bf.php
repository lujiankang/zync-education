<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>我的课程</title>
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
	<css root="/edu/edu/Public/css">["p-Course/mycourse"]</css>
	<!-- 当前页面js -->
	<script src="/edu/edu/Public/js/base/k360-bt-page.js"></script>
	<script src="/edu/edu/Public/js/base/func.js"></script>
	<script src="/edu/edu/Public/js/base/resp-head.js"></script>
	<script src="/edu/edu/Public/js/base/top-nav.js"></script>
	<script src="/edu/edu/Public/js/k360/k360-tag.js"></script>
	<link href="/edu/edu/Public/css/base/animate.css" rel="stylesheet" />
	<script src="/edu/edu/Public/js/p-Course/mycourse.js"></script>
	<script src="/edu/edu/Public/tools/keditor/js/ke.js"></script>
	<link href="/edu/edu/Public/tools/keditor/css/ke.css" rel="stylesheet" />
</head>
<body>

	<div class="data-detail" style="overflow:hidden" k360-scroll-y="4">
		<div class="detail-img">
			<img src="/edu/edu/index.php/Home/Subject/gSubjectFace/id/-1" height="100%" id="faceImage" />
		</div>
		<div class="detail-info">
			<div class="detail-title">科目信息</div>
			<div class="detail-item">课程：<span name="name">xxxxx</span></div>
			<div class="detail-item">科目：<span name="subjectname">xxxxx</span></div>
			<div class="detail-item">课程号：<span name="number">xxxxx</span></div>
			<div class="detail-item">年级：<span name="grade">xxxxx</span></div>
			<div class="detail-item">添加日期：<span name="time">xxxxx</span></div>
			<div class="detail-item">描述：<span name="subjectdesc">xxxxx</span></div>
		</div>
		<div class="detail-btn">
		</div>
	</div>


	<div class="data-core">
		<div class="list-subjects">
			<div class="list-title">
				<div class="to-show-all">
					<i class="fa fa-navicon" style="margin:0px; color:#2980B9"></i>&nbsp;课程
					<ul class="all-time-list" id="dateShower">
					</ul>
				</div>
				<?php if($forStudent != 'true'): ?><button class="btn btn-sm btn-link pull-right" title="添加新的课程" id="courseAddBtn"><i class="fa fa-plus"></i>新增</button><?php endif; ?>
			</div>
			<ul id="subjectShower">
				<!--<li>
					测试内容
					<div class="pull-right">
						<button class="btn btn-sm btn-link"><i class="fa fa-pencil"></i></button>
						<button class="btn btn-sm btn-link"><i class="fa fa-trash"></i></button>
						<button class="btn btn-sm btn-link"><i class="fa fa-calendar"></i></button>
					</div>
					<div class="clearfix"></div>
				</li>-->
			</ul>
			<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i>数据加载中。。。</div>
		</div>

		<div class="main-container">
			<div class="handles">
				<div for="tab1" class="handle-item active"><i class="fa fa-tag"></i> 概述</div>
				<div for="tab2" class="handle-item"><i class="fa fa-folder-open"></i> 文件</div>
				<div for="tab3" class="handle-item"><i class="fa fa-question"></i> 问题答疑</div>
				<?php if($forStudent != 'true'): ?><div for="tab4" class="handle-item"><i class="fa fa-tree"></i> 名单管理</div><?php endif; ?>
				<div for="tab5" class="handle-item"><i class="fa fa-bug"></i> 作业中心</div>
				<div for="tab6" class="handle-item"><i class="fa fa-calendar"></i> 考勤表</div>
			</div>
			<div class="shower">
				<div name="tab1" class="tab" style="line-height:30px; display:inline-block" k360-scroll-y="4">
					<ol type="I" id="courseDescShower">
						<!--<li>xxxx
							<ol>
								<li>
									<button class="fa fa-upload btn btn-link btn-sm" title="上传文件"></button>
									<button class="fa fa-bug btn btn-link btn-sm" title="安排作业"></button>
									xxxxxxxxxxxxx
								</li>
							</ol>
						</li>-->
					</ol>
				</div>
				<div name="tab2" class="tab" k360-scroll-y="3">
					<ul id="courseFileShower">
						<!--<li class="animated fadeInRight">
							<div class="file-type">
								<i class="fa fa-file-word-o"></i>
							</div>
							<div class="file-name">
								<div>测试文件测试文件测试文件.doc</div>
								<div>1-1：绪论</div>
							</div>
							<div class="file-handle">
								<button class="btn btn-sm btn-link fa fa-download">&nbsp;下载</button>
								<button class="btn btn-sm btn-link fa fa-folder-open-o">&nbsp;打开</button>
								<button class="btn btn-sm btn-link fa fa-pencil">&nbsp;重命名</button>
								<button class="btn btn-sm btn-link fa fa-trash">&nbsp;删除</button>
							</div>
						</li>-->
					</ul>
					<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i></div>
				</div>
				<div name="tab3" class="tab">
					<div class="faq" k360-scroll-y="3">
						<!-- 提问 -->
						<?php if($forStudent == 'true'): ?><div class="animated rotateInUpLeft faq-creator" style="animation-duration:0.4s">
								<div class="faq-create-input faq-editor" contenteditable="true" spellcheck="false">你有什么问题向老师求助呢？</div>
								<div class="handles">
									<button class="pull-left fa fa-image btn btn-link" style="margin-top:15px" title="添加图片" id="faqCreateAddImg"></button>
									<span class="pull-left" style="margin-top:27px; font-size:11px; line-height:0px">(添加图片)</span>
									<!--<span title="如果选中择，别的同学将可以对其进行回复。">
										权限：<select class="faq-create-select">
											<option value="1">公开</option>
											<option value="2">教师可见</option>
											<option value="3">当前教师可见</option>
										</select>
									</span>-->
									<button class="btn btn-success btn-sm btn-submit btn-loading" id="faqCreateBtn"><i class="fa fa-spinner fa-spin"></i>发布</button>
									<div class="clearfix"></div>
								</div>
								<!--<div class="imgs-area">
									<div onclick="this.parentElement.removeChild(this)"><i class="fa fa-close"></i></div>
								</div>-->
							</div><?php endif; ?>
						<!-- 问题列表 -->
						<ul id="faqListShower">
							<li class="animated slideInRight" style="animation-duration:0.3s">
								<div class="faq-question" clonename="faq-question">
									<i class="faq-icon-show-title"></i>
									<div class="pull-left faq-question-user-name" name="studentname">xxx</div>
									<div class="faq-question-text" name="text">奋斗精神疗法书法家烦死了烦死了开发商浪费时间俯拾地芥浪费时间康复老师</div>
									<div class="faq-question-user">
										<div class="pull-left faq-question-time" name="time">2015-03-12 03:22:11</div>

										<span class="faq-slide pull-right" style="margin-left:5px">展开/折叠</span>
										<?php if($forStudent != 'true'): ?><span class="faq-to-reply pull-right" style="display:none">回答Ta</span><?php endif; ?>
										<div class="clearfix"></div>
									</div>
								</div>
								<div class="faq-reply animated slideInRight" style="animation-duration:0.3s; margin-top:0px">
									<!-- 回复 -->
									<div class="faq-question" style="background:#f3ffec" clonename="faq-reply">
										<div class="faq-question-text">
											<span class="faq-question-user-name" name="teachername">xxxx</span>
											<span class="faq-text-before reply">回答</span>
											<span name="text">xxxxxxxxxx</span>
										</div>
										<div class="faq-question-user">
											<div class="pull-left faq-question-time" name="time">2015-03-12 03:22:11</div>
											<span class="faq-to-reply pull-right" style="display:none">继续追问</span>
											<div class="clearfix"></div>
										</div>
									</div>
									<!-- 追问 -->
									<div class="faq-question" clonename="faq-moreask">
										<div class="faq-question-text">
											<span class="faq-question-user-name" name="studentname">xxxx</span>
											<span class="faq-text-before moreask">追问</span>
											<span name="text">xxxxxxxxxxxxxx</span>
										</div>
										<div class="faq-question-user">
											<div class="pull-left faq-question-time" name="time">2015-03-12 03:22:11</div>
											<span class="faq-to-reply pull-right" style="display:none">回答Ta</span>
											<div class="clearfix"></div>
										</div>
									</div>
								</div>
								<!-- 回复 -->
								<div class="faq-replier" clonename="faq-replier">
									<div style="min-height:30px" contenteditable="true" class="faq-editor"></div>
									<div class="faq-replier-handles">
										<button class="pull-left btn btn-link fa fa-image"></button>
										<button class="pull-right btn btn-primary btn-sm btn-submit">发布</button>
										<span class="clearfix"></span>
									</div>
								</div>
							</li>
						</ul>
						<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i>数据加载中。。。</div>
					</div>
				</div>
				<div name="tab4" class="tab">
					<div class="student-list-left">
						<div class="list-title">全部学生</div>
						<div class="student-list-show" k360-scroll-y="4">
							<ul id="studentAllShower">
								<!--<li>
									<img src="/edu/edu/index.php/Home/User/gHead/type/student/id/-1" />
									陆建康
									<span>（114090102037）</span>
									<button class="pull-right fa fa-arrow-right btn btn-sm btn-link move-btn"></button>
									<div class="clearfix"></div>
								</li>-->
							</ul>
							<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i>数据加载中。。。</div>
						</div>
					</div>

					<div class="student-list-right">
						<div class="list-title">
							<span>当前课程学生</span>
							<button class="pull-right btn btn-link btn-sm fa fa-save" style="padding:0px; margin-top:12px" id="studentListSaveBtn"> 保存</button>
							<div class="clearfix"></div>
						</div>
						<div class="student-list-show" k360-scroll-y="4">
							<ul class="student-list-cur" id="studentCurShower">
								<!--<li>
									陆建康
									<span>（114090102037）</span>
									<img src="/edu/edu/index.php/Home/User/gHead/type/student/id/-1" />
									<button class="pull-left fa fa-arrow-left btn btn-sm btn-link move-btn"></button>
									<div class="clearfix"></div>
								</li>-->
							</ul>
							<div class="dataLoading"><i class="fa fa-spinner fa-spin"></i>数据加载中。。。</div>
						</div>
					</div>
				</div>
				<div name="tab5" class="tab">
					<ul id="homeworkShower">
						<li>
							<div>1-2、C语言基础教程</div>
							<div style="margin-left:30px">添加时间：2015-03-12</div>
							<div style="margin-left:30px"><soan>已提交：5人</soan><span style="margin-left:20px">已批改：6人</span></div>
						</li>
					</ul>
				</div>
				<div name="tab6" class="tab">
					考勤
				</div>
			</div>
		</div>
	</div>

	<!-- 创建课程 -->
	<div class="inputer" id="courseCreator">
		<div class="animated bounceIn">
			<div class="container-400">
				<form action="/edu/edu/index.php/Home/Course/aCreateCourse" method="post" id="courseCreateForm">
					<div class="form-group">
						<label>名称</label>
						<input type="text" class="form-control" name="name" placeholder="如：数据库系统概论" required>
						<p class="help-block">课程名称尽量便于识别，如：12级计科(2)班 数据库系统概论</p>
					</div>
					<div class="form-group">
						<label>编号</label>
						<input type="text" class="form-control" name="number" placeholder="如：20150394" required>
					</div>
					<div class="form-group">
						<label>科目</label>
						<select class="form-control" name="subject">
							<?php if(is_array($subjects)): $i = 0; $__LIST__ = $subjects;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
						</select>
					</div>
					<div class="form-group">
						<label>年级（针对某个年级的学生，方便生成学生名单）</label>
						<input type="text" class="form-control" name="grade" placeholder="如：2016" required>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;创&nbsp;建&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="courseCreator.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 修改课程信息 -->
	<div class="inputer" id="courseUpdater">
		<div class="animated bounceIn">
			<div class="container-400">
				<form action="/edu/edu/index.php/Home/Course/aUpdateCourse" method="post" id="courseUpdateForm">
					<input type="text" name="cid" style="display:none" />
					<div class="form-group">
						<label>名称</label>
						<input type="text" class="form-control" name="name" placeholder="如：数据库系统概论" required>
						<p class="help-block">课程名称尽量便于识别，如：12级计科(2)班 数据库系统概论</p>
					</div>
					<div class="form-group">
						<label>编号</label>
						<input type="text" class="form-control" name="number" placeholder="如：20150394" required>
					</div>
					<div class="form-group">
						<label>科目</label>
						<select class="form-control" name="subject">
							<?php if(is_array($subjects)): $i = 0; $__LIST__ = $subjects;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
						</select>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;保&nbsp;存&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="courseUpdater.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 上传文件到课程->章节 -->
	<div class="inputer" id="courseUploader">
		<div class="animated bounceIn">
			<div class="container-400">
				<form action="/edu/edu/index.php/Home/Course/aUploadCourseFile" method="post" id="courseUploadForm">
					<div class="form-group">
						<label>文件</label>
						<input type="file" class="form-control" name="files" required multiple>
					</div>
					<div class="form-group">
						<label>同时上传到</label>
						<select type="file" class="form-control" name="courses[]" multiple>
							<option value="222">xxxxxxxxx</option>
						</select>
						<p class="help-block">如果仅上传到当前课程则不用选择(按住Ctrl进行多选取消选择)</p>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;上&nbsp;传&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="courseUploader.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 重命名 -->
	<div class="inputer" id="fileRemamer">
		<div class="animated bounceIn">
			<div class="container-400">
				<form id="fileRenameForm" action="/edu/edu/index.php/Home/File/aRenameFile">
					<input type="text" name="fid" style="display:none" required />
					<div class="form-group">
						<label>文件名（不要随意改动后缀）</label>
						<input type="text" class="form-control" name="name" placeholder="如：数据库系统概论第一讲.ppt" required>
					</div>
					<button type="submit" class="btn btn-success btn-loading">
						&nbsp;&nbsp;<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;重命名&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="fileRemamer.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 作业 -->
	<div class="inputer" id="homewordSetter">
		<div>
			<div style="width:500px">
				<form action="/edu/edu/index.php/Home/Homework/aSaveHomeWork" method="post" id="homeworkSetForm">
					<div class="form-group">
						<label>作业内容</label>
						<div class="form-control" name="content" style="height:150px" kEditor>
							<script src="/edu/edu/Public/tools/keditor/js/modle.js"></script>
						</div>
						<p class="help-block">你可以从word文档中复制作业内容粘贴到此处</p>
					</div>
					<button type="button" class="btn btn-primary btn-loading image-add"><i class="fa fa-spin fa-spinner"></i>添加图片</button>
					<button type="submit" class="btn btn-success btn-loading">
						<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;添加&nbsp;&nbsp;
					</button>
					<button type="button" class="btn btn-warning" onclick="homewordSetter.style.display = 'none'">&nbsp;&nbsp;取&nbsp;消&nbsp;&nbsp;</button>
				</form>
			</div>
		</div>
	</div>

	<!-- 考勤 -->
	<div class="inputer-right" id="attend" style="z-index:1002">
		<div class="animated bounceInRight">
			<div class="list-title">
				<span>考勤记录</span>
				<span class="pull-right">
					
				</span>
				<div class="clearfix"></div>
			</div>
			<div class="attend-content" k360-scroll-y="4">
				<ul>
					<li>
						<img src="/edu/edu/Public/res/head/student_1.png" />
						<span class="name">陆建康</span>
						<span class="pull-right">
							<input type="checkbox" value="1" /> 迟&nbsp;&nbsp;
							<input type="checkbox" value="2" /> 旷&nbsp;&nbsp;
							<input type="checkbox" value="3" /> 假
						</span>
						<div class="clearfix"></div>
					</li>
				</ul>
			</div>
			<div class="attend-footer">
				周数：<select></select>
				<button class="fa fa-check btn btn-primary btn-sm attend-save"> 保存</button>
				<button class="fa fa-close btn btn-warning btn-sm" onclick="attend.style.display='none'"> 取消</button>
			</div>
		</div>
	</div>
</body>
</html>

<script>
	window.forStudent = <?php echo ($forStudent); ?>;

	window.addEventListener("load", function () {
		course_mycourse_controller.create().init();
	})

</script>