window.user_student_controller = {
	create: function () {
		var obj = {};
		//数据加载参数
		var page = k360_bt_page.create(".page");
		var key = "";
		var shower = k360_show_table.create(dataShower);

		var classes = null;
		//行数据tr克隆
		var clonedImportTR = importStudentShower.children[0].cloneNode(true);

		//初始化
		obj.init = function () {
			//分页页面跳转事件
			page.onpage(function (to) {
				loadStudents(to);
			});
			//分页提示错误事件
			page.onerror(function (error) {
				k360_popover.create().setTopPop(true).toast(error);
			});
			//点击顶部的添加学生按钮
			studentCreateBtn.addEventListener("click", function () {
				studentCreator.style.display = "inline-block";
				inputerCenter(studentCreator);
				showClassAtSelect(studentCreateForm.class);
			});
			//添加学生表单提交事件
			studentCreateForm.onsubmit = function () {
				var btn = this.querySelector("button[type='submit']");
				btn.disabled = true;
				k360_http.create().addForm(this)
				.onsuccess(function (data, xhr) {
					btn.disabled = false;
					if (data.reson) {
						k360_popover.create().setTopPop(true).toast(data.reson);
						return;
					}
					loadStudents(page.getCurPage());
					k360_popover.create().setTopPop(true).confirm("成功", "添加学生成功，是否继续添加", "继续添加", "不添加了")
					.setOnCancel(function () {
						studentCreator.style.display = "none";
					});
				})
				.onerror(function (xhr, status, statusText) {
					btn.disabled = false;
					k360_popover.create().setTopPop(true).toast("创建学生错误，可能是网络原因，状态码：" + status);
				})
				.send();
				return false;
			}
			//修改学生表单提交事件
			studentUpdateForm.onsubmit = function () {
				var btn = this.querySelector("button[type='submit']");
				btn.disabled = true;
				k360_http.create().addForm(this)
				.onsuccess(function (data, xhr) {
					btn.disabled = false;
					if (data.reson) {
						k360_popover.create().setTopPop(true).toast(data.reson);
						return;
					}
					loadStudents(page.getCurPage());
					studentUpdater.style.display = "none";
					k360_popover.create().setTopPop(true).toast("学生信息已修改");
				})
				.onerror(function (xhr, status, statusText) {
					btn.disabled = false;
					k360_popover.create().setTopPop(true).toast("修改学生信息错误，可能是网络原因，状态码：" + status);
				})
				.send();
				return false;
			}
			//查看指定年级学生按钮点击事件
			studentViewBtn.addEventListener("click", function () {
				studentSearchForm.key.value = "";
				key = "";
				loadStudents(0);
				hidefilter();
			});
			//学生搜索表单提交事件
			studentSearchForm.onsubmit = function () {
				key = this.key.value;
				key || (key = "");
				this.key.focus();
				loadStudents(0);
				return false;
			}
			//点击快速导入按钮
			studentImportBtn.onclick = function () {
				//显示div
				studentImporter.style.display = "inline-block";
				inputerCenter(studentImporter);
				//加载班级
				showClassAtSelect(studentImportForm.class)
				//删除旧数据
				while (importStudentShower.children.length > 0) {
					importStudentShower.removeChild(importStudentShower.children[0]);
				}
			}
			//点击读取剪贴板按钮
			studentImportForm.readClipBordBtn.onclick = function () {
				var readtextarea = studentImportForm.querySelector(".clipboard-read");
				//var str = window.clipboardData.getData("text");
				var str = readtextarea.value.replace(/(\r|\n)+$/gim, "");
				var aData = { name: null, number: null, sex: null };
				var dict = ["number", "name", "sex"];
				var buffer = "";
				var pos = 0;
				for (var i = 0; i < str.length; i++) {
					var ch = str.charAt(i);
					if (ch == "\n") {
						aData[dict[pos]] = buffer;
						//数据重复性判断
						var repeated = false;
						for (var j = 0; j < importStudentShower.children.length; j++) {
							var rtr = importStudentShower.children[j];
							if (rtr.children[1].innerHTML == aData.number) {
								repeated = true;
								break;
							}
						}
						//添加一条数据
						if (!repeated) {
							var tr = clonedImportTR.cloneNode(true);
							tr.children[0].innerHTML = aData.name;
							tr.children[1].innerHTML = aData.number;
							tr.children[2].innerHTML = aData.sex;
							importStudentShower.appendChild(tr);
						}
						//重置
						aData = { name: null, number: null, sex: null };
						buffer = "";
						pos = 0;
						continue;
					}
					if (ch == "\t") {
						aData[dict[pos]] = buffer;
						buffer = "";
						pos++;
						continue;
					}
					if (ch == "\r") continue;
					buffer += ch;
				}
				inputerCenter(studentImporter);
			}
			//保存导入学生
			studentImportForm.onsubmit = function () {
				if (importStudentShower.children.length == 0) {
					k360_popover.create().setTopPop(true).toast("学生列表为空，请先将学生列表复制过来");
					return false;
				}
				//数据处理
				var http = k360_http.create().addForm(this);
				for (var i = 0; i < importStudentShower.children.length; i++) {
					var tr = importStudentShower.children[i];
					var name = tr.children[0].innerHTML;
					var number = tr.children[1].innerHTML;
					var sex = tr.children[2].innerHTML;
					http.addOne("names[]", name);
					http.addOne("numbers[]", number);
					http.addOne("sexs[]", sex);
				}
				//loading
				var btn = this.querySelector("button[type='submit']");
				btn.disabled = true;
				//提交
				http.onsuccess(function (data, xhr) {
					btn.disabled = false;
					if (data.reson) {
						k360_popover.create().setTopPop(true).toast(data.reson);
						return;
					}
					loadStudents(page.getCurPage());
					studentImporter.style.display = "none";
				})
				.onerror(function (xhr, status, statusText) {
					btn.disabled = false;
					k360_popover.create().setTopPop(true).toast("创建学生错误，可能是网络原因，状态码：" + status);
				})
				.send();
				return false;
			}

			//删除
			shower.onDeleteTeacher = function (id) {
				var student = shower.getDataByKey(id);
				k360_popover.create().setTopPop(true).confirm("提示", "是否要删除“" + student.name + "”？", "删除", "取消")
				.setOnOk(function () {
					var loading = k360_popover.create().setTopPop(true).loading();
					k360_http.create().setUrl(__ROOT__ + "/index.php/Home/User/aRemove/type/student").addData({ id: student.id })
					.onsuccess(function (data, xhr) {
						loading.distroy();
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						loadStudents(page.getCurPage());
						k360_popover.create().setTopPop(true).toast("“" + student.name + "”已删除");
					})
					.onerror(function (xhr, status, statusText) {
						loading.distroy();
						k360_popover.create().setTopPop(true).toast("删除失败，可能是网络出问题了，状态码：" + status);
					})
					.send();
				});
			}
			//重置密码
			shower.onClearPassowrd = function (id) {
				var student = shower.getDataByKey(id);
				k360_popover.create().setTopPop(true).confirm("提示", "是否要重置“" + student.name + "”的密码？", "立即重置密码", "取消")
				.setOnOk(function () {
					var loading = k360_popover.create().setTopPop(true).loading();
					k360_http.create().setUrl(_CLEARPASSWORD).addData({ id: student.id })
					.onsuccess(function (data, xhr) {
						loading.distroy();
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						k360_popover.create().setTopPop(true).toast("“" + student.name + "”的密码已重置");
					})
					.onerror(function (xhr, status, statusText) {
						loading.distroy();
						k360_popover.create().setTopPop(true).toast("重置密码失败，可能是网络出问题了，状态码：" + status);
					})
					.send();
				});
			}
			//修改
			shower.onUpdateStudent = function (id) {
				var student = shower.getDataByKey(id);
				studentUpdater.style.display = "inline-block";
				inputerCenter(studentUpdater);
				//填充数据
				showClassAtSelect(studentUpdateForm.class, student.class);
				studentUpdateForm.uid.value = student.id;
				studentUpdateForm.name.value = student.name;
				studentUpdateForm.number.value = student.number;
			}
			//消息
			shower.onSendMessage = function (id) {
				var student = shower.getDataByKey(id);
				top.chat.sendMessage(student.type, student.id);
			}
			//行
			shower.onTRClick = function (id) {
				showStudent(shower.getDataByKey(id));
			}

			function showStudent(user) {
				userHeadImage.src = __ROOT__ + "/index.php/Home/User/gHead/type/" + user.type + "/id/" + user.id;
				var dataDom = document.querySelector(".data-detail");
				for (var i in user) {
					var doms = dataDom.querySelectorAll("[name='" + i + "']");
					for (var j = 0; j < doms.length; j++) {
						doms.item(j).innerHTML = user[i] ? user[i] : "无";
					}
				}
			}
			//默认加载一次学生
			loadStudents(0);
		}
		//加载班级信息到指定的select中，selected为默认选中项，不传则显示第一条
		function showClassAtSelect(select, selected) {
			function load() {
				select.innerHTML = "<option disabled>数据加载中。。。</option>";
				k360_http.create().setUrl(_GETCLASSES)
				.onsuccess(function (data, xhr) {
					if (data.reson) {
						select.innerHTML = "<option disabled>" + data.reson + "</option>";
						return;
					}
					classes = data.data;
					show();
				})
				.onerror(function (xhr, status, statusText) {
					select.innerHTML = "<option disabled>加载错误，可能是网络问题，" + status + "</option>";
				})
				.send();
			}
			function show() {
				select.innerHTML = "";
				for (var i in classes) {
					var aClass = classes[i];
					select.innerHTML += "<option value='" + aClass.id + "'>" + aClass.name + "</option>";
				}
				select.value = selected ? selected : classes[0].id;
			}
			//加载/显示
			classes ? show() : load();
		}
		//加载学生列表
		function loadStudents(toPage) {
			var loading = document.querySelector(".dataLoading");
			loading.style.display = "inline-block";
			//删除旧数据
			shower.clear();
			//获取数据
			k360_http.create().setUrl(_GETSTUDENT).addData({ page: toPage, class: stuClass.value, key: key })
			.onsuccess(function (data, xhr) {
				loading.style.display = "none";
				if (data.reson) {
					k360_popover.create().setTopPop(true).toast(data.reson);
					return;
				}
				//分页处理
				page.setInfo(data.data.pages, data.data.count, toPage)
				//显示学生
				shower.setDatas(data.data.datas, "id");
				shower.show();
			})
			.onerror(function (xhr, status, statusText) {
				loading.style.display = "none";
				k360_popover.create().setTopPop(true).toast("获取学生数据错误，可能是网络原因，状态码：" + status);
			})
			.send();
		}
		return obj;
	}
};