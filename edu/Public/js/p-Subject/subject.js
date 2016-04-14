window.course_subject_controller = {
	create: function () {
		var obj = {};

		var clonedSubjectDom = subjectShower.children[0].cloneNode(true);
		subjectShower.removeChild(subjectShower.children[0]);
		var subjectLoading = subjectShower.parentElement.querySelector(".dataLoading");

		var shower = {
			addOne: function (ul, data) {
				var li = clonedSubjectDom.cloneNode(true);
				li.bindData = data;
				li.setAttribute("did", data.id);
				for (var i in data) {
					var idom = li.querySelector("[name='" + i + "']");
					idom && (idom.innerHTML = data[i]);
				}
				ul.appendChild(li);
				//设置callback
				var cbdoms = li.querySelectorAll("[callback]");
				for (var i = 0; i < cbdoms.length; i++) {
					var dom = cbdoms.item(i);
					setEvent(dom);
				}
				//ul的callback
				if (li.hasAttribute("callback")) {
					setEvent(li);
				}
				function setEvent(dom) {
					var cb = dom.getAttribute("callback");
					dom.onclick = function (e) {
						if (ul[cb]) {
							if (ul[cb](data.id) === false) e.stopPropagation();
						}
					}
				}
			},
			removeOne: function (ul, id) {
				var li = ul.querySelector("[did='" + id + "']");
				ul.removeChild(li);
			},
			updateOne: function (ul, id, data) {
				var li = ul.querySelector("li[did='" + id + "']");
				for (var i in data) {
					li.bindData[i] = data[i];
					var idom = li.querySelector("[name='" + i + "']");
					idom && (idom.innerHTML = data[i]);
				}
			},
			getById: function (ul, id) {
				var li = ul.querySelector("[did='" + id + "']");
				return li.bindData;
			}
		};

		obj.init = function () {
			//点击添加科目
			subjectAddBtn.onclick = function () {
				subjectCreator.style.display = "inline-block";
				inputerCenter(subjectCreator);
			}
			//添加科目表单提交
			subjectCreateForm.onsubmit = function () {
				var http = k360_http.create().addForm(this);
				var btn = this.querySelector("button[type='submit']");
				btn.disabled = true;
				dataLoad(http, "创建科目错误", function (data) {
					//添加一条数据
					var addData = { id: data, name: subjectCreateForm.name.value, desc: subjectCreateForm.desc.value, time: "刚刚", username: top.user.name, usernumber: top.user.number, usertype: "teacher", chapter: [] };
					shower.addOne(subjectShower, addData);
					subjectCreator.style.display = 'none';
				}, function (err) {
					k360_popover.create().toast(err);
				}, function () {
					btn.disabled = false;
				});
				return false;
			}
			//修改科目表单提交
			subjectUpdateForm.onsubmit = function () {
				var http = k360_http.create().addForm(this);
				var btn = this.querySelector("button[type='submit']");
				btn.disabled = true;
				dataLoad(http, "修改科目错误", function (data) {
					//修改一条数据
					var id = subjectUpdateForm.sid.value;
					var name = subjectUpdateForm.name.value;
					var desc = subjectUpdateForm.desc.value;
					shower.updateOne(subjectShower, id, { name: name, desc: desc });
					subjectUpdater.style.display = 'none';
				}, function (err) {
					k360_popover.create().toast(err);
				}, function () {
					btn.disabled = false;
				});
				return false;
			}
			//点击添加章
			chapterAddBtn.onclick = function () {
				var li = createChapterLi();
				chapterShower.appendChild(li);
			}
			//保存章节
			chapterSaveBtn.onclick = function () {
				/*
				[
					{
						name:xxxxxxxxx,		//章名称
						children:[xxxxxxxxxxx, xxxxxxxxxxxx, ... ...]		//章下面的各节
					},
					... ...
				]
				*/
				k360_popover.create().confirm("警告", "最好不要随意对章节进行修改，可能会导致课程中章节不正确？", "继续修改", "放弃")
				.onok(function () {
					//生成数据
					var data = getEditChapters();
					var chapterStr = JSON.stringify(data);
					//保存数据到服务器
					var id = chapterShower.subjectId;
					var loading = k360_popover.create().loading();
					var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Subject/aSaveChapter").addData({ id: id, chapter: chapterStr });
					dataLoad(http, "保存章节错误", function () {
						shower.updateOne(subjectShower, id, { chapter: data });
						k360_popover.create().toast("已保存");
					}, function (err) {
						k360_popover.create().toast(err);
					}, function () {
						loading.distroy();
					});
				});
			}
			//封面设置
			setSubjectFaceBtn.onclick = function () {
				var file = document.createElement("input");
				file.type = "file";
				file.name = "face";
				file.accept = "image/gif,image/jpeg,image/jpg,image/png,";
				file.onchange = function () {
					var id = chapterShower.subjectId;
					var loading = k360_popover.create().loading();
					var http = k360_http.create().setMethod("post").setUrl(__ROOT__ + "/index.php/Home/Subject/aSetFace").addOne("id", id).addFile(file);
					dataLoad(http, "上传封面错误", function () {
						faceImage.src = __ROOT__ + "/index.php/Home/Subject/gSubjectFace/id/" + id;
					}, function (err) {
						k360_popover.create().toast(err);
					}, function () {
						loading.distroy();
					});
				}
				file.click();
			}

			//科目事件=================
			//显示章节
			subjectShower.onSubjectClick = function (id) {
				showChapters(shower.getById(subjectShower, id));
			}
			//修改科目
			subjectShower.onUpdateSubject = function (id) {
				subjectUpdater.style.display = "inline-block";
				inputerCenter(subjectUpdater);
				//填充数据
				var subject = shower.getById(subjectShower, id);
				subjectUpdateForm.sid.value = subject.id;
				subjectUpdateForm.name.value = subject.name;
				subjectUpdateForm.desc.value = subject.desc;
				return false;
			}
			//删除科目
			subjectShower.onRemoveSubject = function (id) {
				var subject = shower.getById(subjectShower, id);
				k360_popover.create().confirm("警告", "是否删除“" + subject.name + "”？", "删除")
				.onok(function () {
					var loading = k360_popover.create().loading();
					var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Subject/aDeleteSubject").addData({ id: id });
					dataLoad(http, "删除科目错误", function (data) {
						//删除一条数据
						shower.removeOne(subjectShower, id);
						k360_popover.create().toast("“" + subject.name + "”已删除");
					}, function (err) {
						k360_popover.create().toast(err);
					}, function () {
						loading.distroy();
					});
				});
				return false;
			}

			loadSubjects();
		}

		//获取编辑的内容[{name:xxxx, children:[xxxx, xxx, ... ...]}, ... ...]
		function getEditChapters() {
			var data = [];
			var plis = chapterShower.children;
			for (var i = 0; i < plis.length; i++) {
				//所有的章li
				var pli = plis.item(i);
				var clis = pli.querySelectorAll("li");
				var pname = pli.querySelector("input").value;
				var chapterObj = { name: pname, children: [] };
				for (var j = 0; j < clis.length; j++) {
					var cli = clis.item(j);
					var cname = cli.innerText.replace(/(\r|\n)/gim, "");
					chapterObj.children.push(cname);
				}
				data.push(chapterObj);
			}
			return data;
		}

		//显示章节
		function showChapters(subject) {
			//第一次直接显示
			if (!chapterShower.subjectId) {
				show();
				return;
			}
			//判断是否有变动
			var editSubject = shower.getById(subjectShower, chapterShower.subjectId);
			if (JSON.stringify(getEditChapters()) != JSON.stringify(editSubject.chapter)) {
				//提示是否保存
				k360_popover.create().confirm("提示", "章节列表有变更，是否立即保存", "保存", "放弃")
				.oncancel(show)
				.onok(function () {
					chapterSaveBtn.click();
				});
			} else {
				show();
			}
			//显示数据
			function show() {
				showInfo(subject);
				chapterShower.innerHTML = "";
				chapterShower.subjectId = subject.id;
				var chapters = subject.chapter;
				for (var i in chapters) {
					var chapter = chapters[i];
					var pname = chapter.name;
					var liDom = createChapterLi(pname);
					var olDom = liDom.querySelector("ol");
					var children = chapter.children;
					for (var j in children) {
						var cname = children[j];
						var cli = document.createElement("li");
						cli.className = "animated flipInX";
						cli.style.animationDuration = "0.5s";
						cli.innerHTML = cname;
						olDom.appendChild(cli);
					}
					chapterShower.appendChild(liDom);
				}
			}
		}

		//创建一章
		function createChapterLi(name) {
			//创建
			var li = document.createElement("li");
			var delbtn = document.createElement("button");
			var icon = document.createElement("i");
			var input = document.createElement("input");
			var col = document.createElement("ol");
			if(!name) var cli = document.createElement("li");
			//属性
			li.style.animationDuration = "0.5s";
			if (!name) li.className = "animated slideInRight";
			icon.className = "fa fa-trash";
			delbtn.className = "btn btn-link btn-sm";
			delbtn.title = "删除该章节";
			input.type = "text";
			input.placeholder = "请输入章名称";
			if (name) input.value = name;
			col.contentEditable = true;
			if (!name) cli.placeholder = "在这里编辑小节，按回车自动添加一节";
			//关系
			delbtn.appendChild(icon);
			if (!name) col.appendChild(cli);
			li.appendChild(delbtn);
			li.appendChild(input);
			li.appendChild(col);
			//事件
			delbtn.onclick = function () {
				k360_popover.create().confirm("警告", "是否移除章节“" + input.value + "”及其子内容？", "移除")
				.onok(function () {
					li.className = "animated slideOutRight"
					anim(li, "slideOutRight", function () {
						li.parentElement.removeChild(li);
					})
				});
			}
			return li;
		}

		//加载科目
		function loadSubjects() {
			var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Subject/gSubjects");
			subjectLoading.style.display = "inline-block";
			dataLoad(http, "创建科目错误", function (data) {
				if (!data || data.length == 0) {
					k360_popover.create().toast("没有数据，请先添加数据");
					return;
				}
				for (var i in data) {
					shower.addOne(subjectShower,data[i]);
				}
				//默认打开第一个科目
				subjectShower.children[0].click();
			}, function (err) {
				k360_popover.create().toast(err);
			}, function () {
				subjectLoading.style.display = "none";
			});
		}

		//显示章节信息
		function showInfo(subject) {
			document.querySelector(".data-detail").scrollTop = 0;
			chapterShower.parentElement.scrollTop = 0;
			faceImage.src = __ROOT__ + "/index.php/Home/Subject/gSubjectFace/id/" + subject.id;
			var detail = document.querySelector(".data-detail");
			for (var i in subject) {
				var doms = detail.querySelectorAll("[name='" + i + "']");
				for (var j = 0; j < doms.length; j++) {
					doms.item(j).innerHTML = subject[i] ? subject[i] : "无";
				}
			}
		}

		return obj;
	}
}