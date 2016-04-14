window.course_mycourse_controller = {
	create: function () {
		var obj = {};

		var showingCourse = null;
		var courseDatas = null;

		var studentsAll = null;
		var studentsCur = null;


		var icon_color = {
			"file-o": "#2C3E50",
			"file-image-o": "#16A085",
			"file-excel-o": "#C0392B",
			"file-word-o": "#C0392B",
			"file-powerpoint-o": "#C0392B",
			"file-text-o": "#E74C3C",
			"file-pdf-o": "#E74C3C",
			"file-archive-o": "#D35400"
		};

		var homeworkImageAddBtn = homewordSetter.querySelector("button.image-add");
		var attendSaveBtn = attend.querySelector(".attend-save");
		var attendWeekSel = attend.querySelector("select");

		var faqDom = {
			_question: document.querySelector("[clonename='faq-question']").cloneNode(true),
			_reply: document.querySelector("[clonename='faq-reply']").cloneNode(true),
			_moreask: document.querySelector("[clonename='faq-moreask']").cloneNode(true),
			_replier: document.querySelector("[clonename='faq-replier']").cloneNode(true),
			question: function (data) {
				var dom = faqDom["_dataSetter"](faqDom["_question"].cloneNode(true), data);
				if (!data.replies || data.replies.length <= 0) {
					dom.querySelector(" .faq-slide").style.display = "none";
					if (top.user.type == "teacher") dom.querySelector(" .faq-to-reply").style.display = "inline";
				}
				return dom;
			},
			reply: function (data) {
				return faqDom["_dataSetter"](faqDom["_reply"].cloneNode(true), data);
			},
			moreask: function (data) {
				return faqDom["_dataSetter"](faqDom["_moreask"].cloneNode(true), data);
			},
			replier: function (data) {
				return faqDom["_dataSetter"](faqDom["_replier"].cloneNode(true), data);
			},
			_dataSetter: function (dom, data) {
				for (var i in data) {
					var doms = dom.querySelectorAll("[name='" + i + "']");
					for (var j = 0; j < doms.length; j++) {
						if (typeof data[i] == "string")
							doms.item(j).innerHTML = data[i];
						else doms.item(j).appendChild(data[i]);
					}
				}
				return dom;
			}
		};
		faqListShower.removeChild(faqListShower.children[0]);


		obj.init = function () {
			if (!window.forStudent) {
				//点击添加课程按钮
				courseAddBtn.onclick = function () {
					courseCreator.style.display = "inline-block";
					inputerCenter(courseCreator);
				}
				//创建课程表单提交
				courseCreateForm.onsubmit = function () {
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					var http = k360_http.create().addForm(this);
					dataLoad(http, "创建课程错误", function (data) {
						loadSubjects(subjectShower.year, subjectShower.degree);
						courseCreator.style.display = "none";
						k360_popover.create().setTopPop(true).toast("创建成功，已放在本季度课程中");
					}, function (err) {
						k360_popover.create().setTopPop(true).toast(err);
					}, function () {
						btn.disabled = false;
					});
					return false;
				}
				//修改表单提交
				courseUpdateForm.onsubmit = function () {
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					var http = k360_http.create().addForm(this);
					dataLoad(http, "修改课程错误", function (data) {
						loadSubjects(subjectShower.year, subjectShower.degree);
						courseUpdater.style.display = "none";
						k360_popover.create().setTopPop(true).toast("课程信息已修改");
					}, function (err) {
						k360_popover.create().setTopPop(true).toast(err);
					}, function () {
						btn.disabled = false;
					});
					return false;
				}
				//文件上传表单提交
				courseUploadForm.onsubmit = function () {
					var chapter = this.chapter;
					var course = this.course.id;
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					var http = k360_http.create().addForm(this).addData({ chapter: chapter, "courses[]": course });
					dataLoad(http, "上传文件错误", function (data) {
						//courseUploadForm.course.filesData = null;
						courseUploader.style.display = 'none';
						//数据处理
						clearFileData();
						loadFiles(courseUploadForm.course);
					}, function (err) {
						k360_popover.create().setTopPop(true).toast(err);
					}, function () {
						btn.disabled = false;
					});
					return false;
				}
				//重命名表单提交
				fileRenameForm.onsubmit = function () {
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					var http = k360_http.create().addForm(this);
					dataLoad(http, "上传文件错误", function (data) {
						//修改名字
						for (var i in showingCourse.filesData) {
							if (showingCourse.filesData[i].file == fileRenameForm.fid.value) {
								showingCourse.filesData[i].filename = fileRenameForm.name.value;
								break;
							}
						}
						loadFiles(showingCourse);
						fileRemamer.style.display = "none";
					}, function (err) {
						k360_popover.create().setTopPop(true).toast(err);
					}, function () {
						btn.disabled = false;
					});
					return false;
				}
				//点击添加图片按钮
				homeworkImageAddBtn.onclick = function () {
					var editor = document.querySelector("[kEditor]");
					var input = document.createElement("input");
					input.type = "file";
					input.accept = "image/jpeg, image/png, image/bmp";
					input.name = "files[]";
					input.multiple = true;
					input.onchange = function () {
						homeworkImageAddBtn.disabled = true;
						var http = k360_http.create().setUrl(__APP__ + "/Home/Homework/aUploadImage").addFile(input);
						dataLoad(http, "上传图片错误", function (data) {
							for (var i in data) {
								var imgPath = data[i];
								var str = "<img src='" + (__ROOT__ + "/Public/" + imgPath) + "' path='" + imgPath + "' />";
								editor.insert(str);
							}
						}, function (err) {
							k360_popover.create().toast(err);
						}, function () {
							homeworkImageAddBtn.disabled = false;
						});
					}
					input.click();
				}
				//保存作业表单提交
				homeworkSetForm.onsubmit = function () {
					var chapter = this.chapter;
					var course = this.course.id;
					var editor = document.querySelector("[kEditor]");
					var content = editor.value;
					var btn = this.querySelector("button[type='submit']");
					btn.disabled = true;
					var http = k360_http.create().addForm(this).addData({ course: course, content: content, chapter: chapter });
					dataLoad(http, "安排作业错误", function (data) {
						k360_popover.create().toast("作业已安排");
						homewordSetter.style.display = "none";
						console.log(data);
					}, function (err) {
						k360_popover.create().setTopPop(true).toast(err);
					}, function () {
						btn.disabled = false;
					});
					return false;
				}
				//保存学生列表
				studentListSaveBtn.onclick = function () {
					//找到右边的学生
					var rids = [];
					for (var i = 0; i < studentCurShower.children.length; i++) {
						rids.push(studentCurShower.children[i].student.id);
					}
					//找到需要添加/删除的学生
					var needadd = [];
					var needdel = [];
					for (var i in rids) {
						var need = true;
						for (var j in studentsCur) {
							if (rids[i] == studentsCur[j]) {
								need = false;
								break;
							}
						}
						need && needadd.push(rids[i]);
					}
					for (var i in studentsCur) {
						var deled = true;
						for (var j in rids) {
							if (studentsCur[i] == rids[j]) {
								deled = false;
							}
						}
						deled && needdel.push(studentsCur[i]);
					}
					//提交数据
					var loading = k360_popover.create().loading();
					var http = k360_http.create().setUrl(__APP__ + "/Home/Course/aSaveStudents").setMethod("post").addData({ course: showingCourse.id, add: needadd, del: needdel });
					dataLoad(http, "保存学生名单错误", function (data) {
						studentsCur = rids;
						k360_popover.create().toast("学生名单已保存");
						console.log(data);
					}, function (err) {
						k360_popover.create().toast(err);
					}, function () {
						loading.distroy();
					});
				}
				//保存考勤信息
				attendSaveBtn.onclick = function () {
					var course = this.course.id;
					var chapter = this.chapter;
					var checkboxes = attend.querySelectorAll("input[type='checkbox']:checked");
					var data = [];
					for (var i = 0; i < checkboxes.length; i++) {
						var checkbox = checkboxes.item(i);
						var aData = { stu: checkbox.student, stat: checkbox.value };
						data.push(aData);
					}
					//发送数据
					var loading = k360_popover.create().loading();
					var http = k360_http.create().setUrl(__APP__ + "/Home/Attend/aCreateAttend").setMethod("post").addData({ course: course, chapter: chapter, attend: JSON.stringify(data) });
					dataLoad(http, "添加考勤记录错误", function (data) {
						k360_popover.create().toast("考勤记录已保存");
						attend.style.display = "none";
						console.log(data);
					}, function (err) {
						k360_popover.create().toast(err);
					}, function () {
						loading.distroy();
					});
				}
			} else {
				//点击问题提交按钮
				faqCreateBtn.onclick = function () {
					if (!showingCourse) {
						k360_popover.create().toast("没有找到课程信息");
						return;
					}
					var course = showingCourse.id;
					var html = document.querySelector(".faq-create-input").innerHTML;
					//var permission = document.querySelector(".faq-create-select").value;
					//提交数据
					html = html.replace(/<[div|p]*?>([\s\S]*?)<\/[div|p]*?>/gim, "<br />$1<br />").replace(/<br[\s\S]*?><br[\s\S]*?>/gim, "<br />");
					if (/你有什么问题向老师求助呢？/gim.test(html)) {
						k360_popover.create().toast("请输入内容");
						return;
					}
					faqCreateBtn.disabled = true;
					var http = k360_http.create().setUrl(__APP__ + "/Home/FAQ/aCreate").addData({ course: course, text: html });
					dataLoad(http, "提交问题错误", function (data) {
						//将问题插入到第一行
						var question = { id: data, student: top.user.id, studentname: top.user.name, text: html, time: "刚刚", course: showingCourse.id, replies: [] };
						var questionDom = faqDom.question(question);
						addAFAQ(questionDom, false);
						k360_popover.create().toast("问题已提交，老师回复后你将收到提醒");
					}, function (err) {
						k360_popover.create().toast(err);
					}, function () {
						faqCreateBtn.disabled = false;
					});
				}
				//问题编辑时点击插入图片按钮
				faqCreateAddImg.onclick = function () {
					var file = document.createElement("input");
					file.type = "file";
					file.accept = "image/*";
					file.name = "file";
					file.onchange = function () {
						//拼接img
						var inputer = document.querySelector(".faq-create-input");
						var img = document.createElement("img");
						img.src = "";
						img.alt = "图片上传中。。。";
						inputer.appendChild(img);
						//上传文件到faq文件夹
						var http = k360_http.create().setUrl(__APP__ + "/Home/FAQ/aUploadToFAQ").addFile(file);
						dataLoad(http, "上传文件错误", function (data) {
							img.src = __PUBLIC__ + "/" + data;
							img.setAttribute("path", data);
							img.alt = "加载中。。。";
						}, function (err) {
							k360_popover.create().toast(err);
						}, function () {
						});
					}
					file.click();
				}
			}

			dateShower.parentElement.onclick = function (e) {
				dateShower.style.display = "inline-block";
				e.stopPropagation();
			}

			document.addEventListener("click", function (e) {
				dateShower.style.display = "none";
			});

			var handles = document.querySelector(".main-container .handles").children;
			for (var i = 0; i < handles.length ; i++) {
				var ahandle = handles[i];
				ahandle.onclick = function () {
					var to = this.getAttribute("for");
					var tab = document.querySelector(".main-container [name='" + to + "']");
					var tabs = document.querySelectorAll(".main-container .tab");
					for (var j = 0; j < tabs.length; j++) {
						var atab = tabs[j];
						if (atab == tab) {
							atab.style.display = "inline-block";
						} else {
							atab.style.display = "none";
						}
					}
					for (var j = 0; j < handles.length; j++) {
						if (this == handles[j]) {
							handles[j].className = "handle-item active";
						} else {
							handles[j].className = "handle-item";
						}
					}
				}
			}

			//考勤周数
			for (var i = 1; i <= 20; i++) {
				var opt = document.createElement("option");
				opt.value = i;
				opt.innerHTML = i;
				attendWeekSel.appendChild(opt);
			}
			loadDate(2015);
		}

		//添加FAQ
		function addAFAQ(questionDom, beAppend) {
			var li = document.createElement("li");
			li.className = "animated slideInRight";
			li.style.animationDuration = "0.3s";
			//var replyDom = document.createElement("div");
			//replyDom.className = "faq-reply animated slideInRight";
			//replyDom.style.animationDuration = "0.3s";
			li.appendChild(questionDom);
			//li.appendChild(replyDom);
			if (beAppend || faqListShower.children.length <= 0)
				faqListShower.appendChild(li);
			else faqListShower.insertBefore(li, faqListShower.children[0]);
			return li;
		}

		//加载FAQ
		function loadFAQ(course, page) {
			var loading = document.querySelector(".faq .dataLoading");
			loading.style.display = "block";
			var http = k360_http.create().setUrl(__APP__ + "/Home/FAQ/gFAQ").addData({ page: page, course: course.id });
			dataLoad(http, "加载FAQ错误", function (data) {
				if (course.faq)
					course.faq.push(data);
				course.faq = data;
				showFAQ(course);
			}, function (err) {
				k360_popover.create().toast(err);
			}, function () {
				loading.style.display = "none";
			});
		}

		//FAQ教师回答
		function teacherReplyEvent() {

		}

		//FAQ学生追问
		function studentReplyEvent(faq, replyDom, reply, replierDom, typereplies) {
			replyDom.onclick = function () {
				replierDom.style.display = (replierDom.style.display == "none") ? "block" : "none";
			}
			replierDom.querySelector(".btn-submit").onclick = function () {
				var html = replierDom.querySelector(".faq-editor").innerHTML;
				//提交数据
				html = html.replace(/<[div|p]*?>([\s\S]*?)<\/[div|p]*?>/gim, "<br />$1<br />").replace(/<br[\s\S]*?><br[\s\S]*?>/gim, "<br />");
				var http = k360_http.create().setUrl(__APP__ + "/Home/FAQ/aReply").addData({ faq: faq.id, teacher: reply.teacher, text: html, moreask: replierDom.moreask });
				dataLoad(http, "提交内容错误", function (data) {
					//数据
					var replyData = { teacher: reply.teacher, teachername: reply.teachername, text: html, time: "刚刚", ismoreask: "1", studentname: faq.studentname };
					replierDom.style.display = "none";
					replyDom.querySelector(".faq-to-reply").style.display = "none";
					typereplies.push(replyData);
					//显示
					var replyDom1 = (replyData.ismoreask == 1) ? faqDom.moreask(replyData) : faqDom.reply(replyData);
					replierDom.parentElement.appendChild(replyDom1);
					//提示
					k360_popover.create().toast("回复成功");
				}, function (err) {
					k360_popover.create().toast(err);
				}, function () {
					faqCreateBtn.disabled = false;
				});
			}
		}

		//显示FAQ列表
		function showFAQ(course) {
			if (course.id != showingCourse.id) return;
			while (faqListShower.children.length > 0) {
				faqListShower.removeChild(faqListShower.children[0]);
			}
			var faqs = course.faq;
			//创建问题
			for (var i in faqs) {
				var faq = faqs[i];
				var questionDom = faqDom.question(faq);
				var li = addAFAQ(questionDom, true);
				li.style.paddingBottom = "0px";
				//创建回复
				var replies = faq.replies;
				for (var j in replies) {
					var typereplies = replies[j];

					var replyPrtDom = document.createElement("div");
					replyPrtDom.className = "faq-reply animated slideInRight";
					replyPrtDom.style.animationDuration = "0.3s";
					replyPrtDom.style.display = "none";

					var replyDom = null;
					var reply = null;
					for (var k in typereplies) {
						reply = typereplies[k];
						reply.studentname = faq.studentname;
						replyDom = (reply.ismoreask==1) ? faqDom.moreask(reply) : faqDom.reply(reply);
						replyPrtDom.appendChild(replyDom);
					}
					//学生-追问
					if (reply && reply.ismoreask != 1 && top.user.type == "student") {
						//显示追问
						replyDom.querySelector(".faq-to-reply").style.display = "inline";
						//输入框
						var replierDom = faqDom.replier();
						replierDom.style.display = "none";
						replierDom.moreask = "1";
						replyPrtDom.appendChild(replierDom);
						studentReplyEvent(faq, replyDom, reply, replierDom, typereplies);
					}
					//教师回答
					if (reply && reply.ismoreask == 1 && top.user.type == "teacher" && reply.teacher == top.user.id) {
						//显示回复
						replyDom.querySelector(".faq-to-reply").style.display = "inline";
						//输入框
						var replierDom = faqDom.replier();
						replierDom.style.display = "none";
						replierDom.moreask = "1";
						replyPrtDom.appendChild(replierDom);
						teacherReplyEvent(faq, replyDom, reply, replierDom, typereplies);
					}

					li.appendChild(replyPrtDom);
				}

				//创建输入
				var replier = faqDom.replier([]);
				replier.style.display = "none";
				li.appendChild(replier);
				liEvent(li);
			}
			
			function liEvent(li) {
				var replies = li.querySelectorAll(".faq-reply");
				var slide = li.querySelector(".faq-slide");
				var replier = li.querySelector('.faq-replier');
				var toReply = li.querySelectorAll(".faq-to-reply");
				slide.onclick = function () {
					var display = (replies[0].style.display == "none") ? "block" : "none";
					style(replies, { display: display });
				}
				
			}
		}

		//显示学生列表
		function showCourseStudent(all, cur, course) {
			for (var i in all) {
				//先生成一个li dom
				var student = all[i];
				//创建dom
				var li = document.createElement("li");
				var img = document.createElement("img");
				var name = document.createTextNode(student.name);
				var number = document.createElement("span");
				var btn = document.createElement("button");
				var clear = document.createElement("div");
				//设置属性
				li.className = "animated slideInRight";
				li.style.animationDuration = "0.3s";
				li.student = student;
				img.src = __APP__ + "/Home/User/gHead/type/student/id/" + student.id;
				btn.className = "pull-right fa fa-arrow-right btn btn-sm btn-link move-btn";
				clear.className = "clearfix";
				number.innerHTML = "（" + student.number + "）";
				//设置关系
				li.appendChild(img);
				li.appendChild(name);
				li.appendChild(number);
				li.appendChild(btn);
				li.appendChild(clear);
				//判断是否在右边
				var right = false;
				for (var j in cur) {
					if (cur[j] == student.id) {
						right = true;
						break;
					}
				}
				//添加
				if (right) {
					studentCurShower.appendChild(toRight(li));
				} else {
					studentAllShower.appendChild(li);
				}
				//事件
				toToggle(li, btn);
			}
			//学生转移到右边
			function toRight(liDom) {
				//设置到右边
				liDom.className = "animated slideInLeft";
				var doms = liDom.childNodes;
				var img = doms[0];
				var btn = doms[3];
				btn.className = "pull-left fa fa-arrow-left btn btn-sm btn-link move-btn";
				liDom.insertBefore(img, btn);
				liDom.pos = "right";
				return liDom;
			}
			//学生转移到左边
			function toLeft(liDom) {
				liDom.className = "animated slideInRight";
				//设置到右边
				var doms = liDom.childNodes;
				var img = liDom.querySelector("img");
				var btn = liDom.querySelector("button");
				btn.className = "pull-right fa fa-arrow-right btn btn-sm btn-link move-btn";
				liDom.insertBefore(img, doms[0]);
				liDom.pos = "left";
				return liDom;
			}
			//学生左右转换
			function toToggle(li, btn) {
				btn.onclick = function () {
					if (!li.pos || li.pos == "left") {
						studentCurShower.appendChild(toRight(li));
						setTimeout(function () {
							studentCurShower.scrollTop = studentCurShower.scrollHeight+10000;
						}, 100);
					}
					else {
						studentAllShower.appendChild(toLeft(li));
						setTimeout(function () {
							studentAllShower.scrollTop = studentAllShower.scrollHeight + 10000;
						}, 100);
					}
				}
			}
		}

		//加载学生列表
		function loadStudentsList(course) {
			//loading
			var loadings = document.querySelectorAll(".student-list-show .dataLoading");
			style(loadings, { display: "inline-block" });
			//删除元素
			removeChildrenAll(studentAllShower);
			removeChildrenAll(studentCurShower);
			//加载
			var http = k360_http.create().setUrl(__APP__ + "/Home/Course/gStudentInfo").addData({ course: course.id });
			dataLoad(http, "获取学生列表错误", function (data) {
				studentsAll = data.all;
				studentsCur = data.cur;
				showCourseStudent(data.all, data.cur, course);
			}, function (err) {
				k360_popover.create().toast(err);
			}, function () {
				style(loadings, { display: "none" });
			});
		}

		//获取文件图标
		function getFileIcon(name) {
			var index = name.lastIndexOf(".");
			if (index == -1) return "file-o";
			var suff = name.substr(index + 1);
			var dict = {
				jpg: "file-image-o",
				jpeg: "file-image-o",
				gif: "file-image-o",
				bmp: "file-image-o",
				png: "file-image-o",

				xls: "file-excel-o",
				xlsx: "file-excel-o",
				doc: "file-word-o",
				docx: "file-word-o",
				ppt: "file-powerpoint-o",
				pptx: "file-powerpoint-o",
				txt: "file-text-o",
				pdf: "file-pdf-o",

				zip: "file-archive-o",
				rar: "file-archive-o",
				"7z": "file-archive-o",
				tar: "file-archive-o",
				jar: "file-archive-o",
				apk: "file-archive-o"
			};
			if (dict[suff]) return dict[suff];
			return "file-o";
		}

		//根据上传表单的信息清楚course的文件数据
		function clearFileData() {
			courseUploadForm.course.filesData = null;
			for (var i in courseDatas) {
				var course = courseDatas[i];
				if (course.id == courseUploadForm.course.id) continue;
				var opt = courseUploadForm.querySelector("option[value='" + course.id + "']");
				console.log(opt);
				if (opt.selected) {
					course.filesData = null;
				}
			}
		}

		//加载文件数据
		function loadFiles(course) {
			//删除数据
			while (courseFileShower.children.length > 0) {
				courseFileShower.removeChild(courseFileShower.children[0]);
			}
			//如果已经有数据则直接显示
			if (course.filesData) {
				showFileData(course.filesData, course);
				return;
			}
			//否则进行数据加载
			var loading = document.querySelector(".main-container [name='tab2'] .dataLoading");
			loading.style.display = "inline-block";
			var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Course/gCourseFile").addData({ id: course.id });
			dataLoad(http, "获取文件列表错误", function (data) {
				course.filesData = data;
				//如果当前是课程则显示
				if (course.id == showingCourse.id) {
					showFileData(data, course);
				}
			}, function (err) {
				k360_popover.create().setTopPop(true).toast(err);
			}, function () {
				(course.id == showingCourse.id) && (loading.style.display = "none");
			});
			//显示数据函数
		}

		//文件浏览li按钮点击事件处理
		function fileEvent(btndownload, btnopen, btnrename, btnremove, course, data) {
			//事件处理
			btndownload.onclick = function () {
				downloadFile(data.file);
			}
			btnopen.onclick = function () {
				top.preview.show(data.file);
			}
			btnrename.onclick = function () {
				fileRemamer.style.display = "inline-block";
				inputerCenter(fileRemamer);
				fileRenameForm.fid.value = data.file;
				fileRenameForm.name.value = data.filename;
			}
			btnremove.onclick = function () {
				k360_popover.create().setTopPop(true).confirm("警告", "是否删除“" + data.filename + "”？", "删除")
				.onok(function () {
					var loading = k360_popover.create().setTopPop(true).loading();
					var http = k360_http.create().setUrl(__APP__ + "/Home/Course/aDeleteCourseFile").addData({ rid: data.id });
					dataLoad(http, "删除文件错误", function (d) {
						//删除course中指定的文件的数据
						for (var i in course.filesData) {
							if (course.filesData[i].id == data.id) {
								course.filesData.splice(i, 1);
								break;
							}
						}
						loadFiles(course);
					}, function (err) {
						k360_popover.create().setTopPop(true).toast(err);
					}, function () {
						loading.distroy();
					});
				});
			}
		}

		//显示文件数据
		function showFileData(data, course) {
			for (var i in data) {
				var info = data[i];
				//创建dom
				var li = document.createElement("li");
				var type = document.createElement("div");
				var icon = document.createElement("i");
				var name = document.createElement("div");
				var namedom = document.createElement("div");
				var chapter = document.createElement("div");
				//var br = document.createElement("br");
				var handle = document.createElement("div");
				var btndownload = document.createElement("button");
				var btnopen = document.createElement("button");
				var btnrename = document.createElement("button");
				var btnremove = document.createElement("button");
				//设置属性
				li.className = "animated fadeInRight";
				li.style.animationDuration = "0.3s";
				type.className = "file-type";
				var faIcon = getFileIcon(info.filename)
				icon.className = "fa fa-" + faIcon;
				icon.style.color = icon_color[faIcon];
				name.className = "file-name";
				chapter.style.color = "#555";
				handle.className = "file-handle";
				btndownload.className = "btn btn-sm btn-link fa fa-download";
				btnopen.className = "btn btn-sm btn-link fa fa-folder-open-o";
				btnrename.className = "btn btn-sm btn-link fa fa-pencil";
				btnremove.className = "btn btn-sm btn-link fa fa-trash";
				//设置数据
				namedom.innerHTML = info.filename;
				var ci = info.chapter[0];
				var cj = info.chapter[1];
				chapter.innerHTML = ci + "-" + cj + "、" + course.chapter[ci-1].children[cj-1];
				//设置关系
				type.appendChild(icon);
				name.appendChild(chapter);
				name.appendChild(namedom);
				btndownload.appendChild(document.createTextNode(" 下载"));
				btnopen.appendChild(document.createTextNode(" 打开"));
				btnrename.appendChild(document.createTextNode(" 重命名"));
				btnremove.appendChild(document.createTextNode(" 删除"));
				handle.appendChild(btndownload);
				handle.appendChild(btnopen);
				if (!window.forStudent) {
					handle.appendChild(btnrename);
					handle.appendChild(btnremove);
				}
				li.appendChild(type);
				li.appendChild(name);
				//li.appendChild(chapter);
				//li.appendChild(br);
				li.appendChild(handle);
				courseFileShower.appendChild(li);
				fileEvent(btndownload, btnopen, btnrename, btnremove, course, info);
			}
		}

		//加载科目列表，加载完成后默认点击一次第一个元素
		function loadSubjects(year, degree) {
			if (!year || !degree) return;
			while (subjectShower.children.length > 0) {
				subjectShower.removeChild(subjectShower.children[0]);
			}
			subjectShower.year = null;
			subjectShower.degree = null;
			var loading = document.querySelector(".list-subjects .dataLoading");
			loading.style.display = "inline-block";
			var func = window.forStudent ? "gSubjectOfTimeStudent" : "gSubjectOfTime";
			var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Course/" + func).addData({ year: year, degree: degree });
			dataLoad(http, "加载课程列表错误", function (data) {
				subjectShower.year = year;
				subjectShower.degree = degree;
				courseDatas = data;
				if (!data || data.length == 0) {
					k360_popover.create().setTopPop(true).toast("没有任何课程", null, 2000);
				}
				var li = null;
				for (var i in data) {
					var bli = showCourse(data[i]);
					if (!li) li = bli;
				}
				li.click();
			}, function (err) {
				k360_popover.create().setTopPop(true).toast(err);
			}, function () {
				loading.style.display = "none";
			});
		}

		//课程概述界面按钮事件
		function courseDescEvent(course, upl, hwk, att, i, j) {
			i = parseInt(i);
			j = parseInt(j);
			var chapter = JSON.stringify([i + 1, j + 1]);
			upl.onclick = function () {
				courseUploader.style.display = "inline-block";
				inputerCenter(courseUploader);
				courseUploadForm.chapter = chapter;
				courseUploadForm.course = course;
				//表单数据设置
				var sel = courseUploadForm["courses[]"];
				while (sel.children.length > 0) {
					sel.removeChild(sel.children[0]);
				}
				for (var m in courseDatas) {
					var c = courseDatas[m];
					if (course.id == c.id) continue;
					var opt = document.createElement("option");
					opt.value = c.id;
					opt.innerHTML = c.name;
					sel.appendChild(opt);
				}
			}
			hwk.onclick = function () {
				homewordSetter.style.display = "inline-block";
				inputerCenter(homewordSetter);
				homeworkSetForm.chapter = chapter;
				homeworkSetForm.course = course;
			}
			att.onclick = function () {
				if (!studentsCur) {
					k360_popover.create().toast("学生列表还没有加载完，请稍后");
					return;
				}
				attendSaveBtn.course = course;
				attendSaveBtn.chapter = chapter;
				var ul = attend.querySelector("ul");
				while (ul.children.length > 0) {
					ul.removeChild(ul.children[0]);
				}
				//设置学生列表
				for (var m in studentsCur) {
					var studentId = studentsCur[m];
					var student = getStudentInfoFromAll(studentId);
					//创建
					var li = document.createElement("li");
					var img = document.createElement("img");
					var name = document.createElement("span");
					var handles = document.createElement("span");
					var check1 = document.createElement("input");
					var check2 = document.createElement("input");
					var check3 = document.createElement("input");
					var clear = document.createElement("div");
					//属性
					img.src = __APP__ + "/Home/User/gHead/type/student/id/" + student.id;
					name.className = "name";
					name.innerHTML = student.name;
					handles.className = "pull-right";
					check1.type = "checkbox";
					check1.value = 1;
					check1.student = student.id;
					check2.type = "checkbox";
					check2.value = 2;
					check2.student = student.id;
					check3.type = "checkbox";
					check3.value = 3;
					check3.student = student.id;
					clear.className = "clearfix";
					//关系
					handles.appendChild(check1);
					handles.appendChild(document.createTextNode(" 迟  "));
					handles.appendChild(check2);
					handles.appendChild(document.createTextNode(" 旷  "));
					handles.appendChild(check3);
					handles.appendChild(document.createTextNode(" 假"));
					li.appendChild(img);
					li.appendChild(name);
					li.appendChild(handles);
					li.appendChild(clear);
					ul.appendChild(li);
					//事件处理
					handleLiEvent(check1, check2, check3);
				}

				attend.style.display = "inline-block";
			}
			//处理输入框点击事件
			function handleLiEvent(check1, check2, check3) {
				var checks = [check1, check2, check3];
				on(checks, "click", function () {
					for (var m in checks) {
						if (checks[m] == this) continue;
						checks[m].checked = false;
					}
				});
			}
		}

		//从全部学生中获取当前学生信息
		function getStudentInfoFromAll(studentId) {
			for (var i in studentsAll) {
				if (studentsAll[i].id == studentId)
					return studentsAll[i];
			}
			return false;
		}

		//显示右边信息栏
		function showRightInfo(course) {
			faceImage.src = __APP__ + "/Home/Subject/gSubjectFace/id/" + course.subject;
			var detail = document.querySelector(".data-detail");
			for (var i in course) {
				var doms = detail.querySelectorAll("[name='" + i + "']");
				for (var j = 0; j < doms.length; j++) {
					doms.item(j).innerHTML = course[i] ? course[i] : "无";
				}
			}
		}

		//显示课程信息
		function showCourseInfo(course) {
			loadFiles(course);
			loadStudentsList(course);
			showRightInfo(course);
			showingCourse = course;
			
			if (!course.faq)
				loadFAQ(course);
			else showFAQ(course);
			
			while (courseDescShower.children.length > 0) {
				courseDescShower.removeChild(courseDescShower.children[0]);
			}
			var chapters = course.chapter;
			for (var i in chapters) {
				var chapter = chapters[i];
				var li = document.createElement("li");
				li.innerHTML = chapter.name;
				var ol = document.createElement("ol");
				li.appendChild(ol);
				for (var j in chapter.children) {
					var cli = document.createElement("li");
					if (!window.forStudent) {
						var upl = document.createElement("button");
						upl.title = "上传文件到此章节";
						upl.className = "fa fa-upload btn btn-link btn-sm";
						var hwk = document.createElement("button");
						hwk.title = "安排作业";
						hwk.className = "fa fa-bug btn btn-link btn-sm";
						var att = document.createElement("button");
						att.title = "打考勤";
						att.className = "fa fa-calendar btn btn-link btn-sm";
						cli.appendChild(upl);
						cli.appendChild(hwk);
						cli.appendChild(att);
						courseDescEvent(course, upl, hwk, att, i, j);
					}
					cli.className = "animated fadeInRight";
					cli.style.animationDuration = "0.3s";
					cli.appendChild(document.createTextNode(chapter.children[j]));
					ol.appendChild(cli);
				}
				courseDescShower.appendChild(li);
			}
			document.querySelector(".main-container .handles").children[0].click();
		}

		//显示课程
		function showCourse(course) {
			studentsAll = null;
			studentsCur = null;
			showingCourse = null;
			courseDatas = null;

			var li = document.createElement("li");
			li.innerHTML = course.name;
			li.className = "a animated fadeInLeft";
			li.style.animationDuration = "0.3s";

			if (!window.forStudent) {
				var handles = document.createElement("div");
				handles.className = "pull-right";
				li.appendChild(handles);

				var edit = document.createElement("button");
				edit.className = "btn btn-sm btn-link";
				handles.appendChild(edit);
				var efa = document.createElement("i");
				efa.className = "fa fa-pencil";
				edit.appendChild(efa);

				var del = document.createElement("button");
				del.className = "btn btn-sm btn-link";
				handles.appendChild(del);
				var dfa = document.createElement("i");
				dfa.className = "fa fa-trash";
				del.appendChild(dfa);

				//编辑
				edit.onclick = function (e) {
					e.stopPropagation();
					courseUpdater.style.display = "inline-block";
					inputerCenter(courseUpdater);
					courseUpdateForm.cid.value = course.id;
					courseUpdateForm.name.value = course.name;
					courseUpdateForm.number.value = course.number;
					courseUpdateForm.subject.value = course.subject;
				}
				//删除
				del.onclick = function (e) {
					e.stopPropagation();
					k360_popover.create().setTopPop(true).confirm("警告", "是否删除“" + course.name + "”？", "删除")
					.onok(function () {
						var loading = k360_popover.create().setTopPop(true).loading();
						var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Course/aDeleteCourse").addData({ id: course.id });
						dataLoad(http, "删除课程错误", function (data) {
							loadSubjects(subjectShower.year, subjectShower.degree);
						}, function (err) {
							k360_popover.create().setTopPop(true).toast(err);
						}, function () {
							loading.distroy();
						});
					});
				}
			}
			li.onclick = function () {
				showCourseInfo(course);
			}
			subjectShower.appendChild(li);
			return li;
		}

		//加载年份，并显示到年份选择列表中，加载完成后会点击一次第一个li
		function loadDate(minYear) {
			var date = new Date();
			var year = date.getFullYear()
			var month = date.getMonth() + 1;
			if (month > 7) addOne(year, 2);
			addOne(year, 1);
			for (var i = year - 1; i >= minYear; i--) {
				addOne(i, 2);
				addOne(i, 1);
			}
			dateShower.children[0].click();

			function addOne(year, degree) {
				var str = year + "年 （" + ((degree==1)?"上":"下") + "）";
				var li = document.createElement("li");
				li.innerHTML = str;
				dateShower.appendChild(li);
				li.onclick = function (e) {
					dateShower.style.display = "none";
					loadSubjects(year, degree);
					e.stopPropagation();
				}
			}
		}


		return obj;
	}
};