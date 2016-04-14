window.user_teacher_controller = {
	create: function () {
		var obj = {};

		var page = k360_bt_page.create(".page");
		var shower = k360_show_table.create(dataShower);
		var dataLoading = document.querySelector(".dataLoading");

		var key = "";

		var roles = null;

		obj.init = function () {
			//分页跳转事件
			page.onpage(function (go) {
				loadTeacher(go);
			});
			//分页提示事件
			page.onerror(function (error) {
				k360_popover.create().setTopPop(true).toast(error);
			});
			//点击创建教师
			teacherCreateBtn.onclick = function () {
				teacherCreator.style.display = "inline-block";
				inputerCenter(teacherCreator);
				teacherCreateForm.reset();		//重置
				teacherCreateForm.role.innerHTML = "<option disabled>数据加载中。。。</option>";
				//获取角色
				loadRoles(teacherCreateForm.role);
			}
			//添加教师的表单提交
			teacherCreateForm.onsubmit = function () {
				var btn = this.querySelector("button[type='submit']");
				//提交数据
				btn.disabled = true;
				k360_http.create().addForm(this)
				.onsuccess(function (data, xhr) {
					btn.disabled = false;
					if (data.reson) {
						k360_popover.create().setTopPop(true).toast(data.reson);
						return;
					}
					//刷新列表
					loadTeacher(page.getCurPage());
					//提示
					k360_popover.create().setTopPop(true).confirm("成功", "教师创建成功，是否继续添加教师？", "继续添加", "不添加了")
					.setOnCancel(function () {
						teacherCreator.style.display = "none";
					})
				})
				.onerror(function (xhr, status, statusText) {
					btn.disabled = false;
					k360_popover.create().setTopPop(true).toast("创建教师错误，可能是网络原因，状态码：" + status);
				})
				.send();

				return false;
			}
			//修改教师表单提交
			teacherUpdateForm.onsubmit = function () {
				var btn = this.querySelector("button[type='submit']");
				//提交数据
				btn.disabled = true;
				k360_http.create().addForm(this)
				.onsuccess(function (data, xhr) {
					btn.disabled = false;
					if (data.reson) {
						k360_popover.create().setTopPop(true).toast(data.reson);
						return;
					}
					//刷新列表
					loadTeacher(page.getCurPage());
					teacherUpdater.style.display = "none";
					k360_popover.create().setTopPop(true).toast("教师信息已修改");
				})
				.onerror(function (xhr, status, statusText) {
					btn.disabled = false;
					k360_popover.create().setTopPop(true).toast("修改教师信息错误，可能是网络原因，状态码：" + status);
				})
				.send();
				return false;
			}
			//搜索表单提交
			teacherSearchForm.onsubmit = function () {
				key = teacherSearchForm.key.value;
				loadTeacher(0);
				teacherSearchForm.key.focus();
				return false;
			}

			//删除教师
			shower.onDeleteTeacher = function (id) {
				var teacher = shower.getDataByKey(id);
				k360_popover.create().setTopPop(true).confirm("提示", "是否要删除“" + teacher.name + "”？", "删除", "取消")
				.setOnOk(function () {
					var loading = k360_popover.create().setTopPop(true).loading();
					k360_http.create().setUrl(__ROOT__ + "/index.php/Home/User/aRemove/type/teacher").addData({ id: teacher.id })
					.onsuccess(function (data, xhr) {
						loading.distroy();
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						loadTeacher(page.getCurPage());
						k360_popover.create().setTopPop(true).toast("“" + teacher.name + "”已删除");
					})
					.onerror(function (xhr, status, statusText) {
						loading.distroy();
						k360_popover.create().setTopPop(true).toast("删除失败，可能是网络出问题了，状态码：" + status);
					})
					.send();
				});
			}
			//修改信息
			shower.onClearPassowrd = function (id) {
				var teacher = shower.getDataByKey(id);
				k360_popover.create().setTopPop(true).confirm("提示", "是否要重置“" + teacher.name + "”的密码？", "重置", "取消")
				.setOnOk(function () {
					var loading = k360_popover.create().setTopPop(true).loading();
					k360_http.create().setUrl(_CLEARPASSWORD).addData({ id: teacher.id })
					.onsuccess(function (data, xhr) {
						loading.distroy();
						if (data.reson) {
							k360_popover.create().setTopPop(true).toast(data.reson);
							return;
						}
						k360_popover.create().setTopPop(true).toast("“" + teacher.name + "”的密码已重置");
					})
					.onerror(function (xhr, status, statusText) {
						loading.distroy();
						k360_popover.create().setTopPop(true).toast("重置密码失败，可能是网络出问题了，状态码：" + status);
					})
					.send();
				});
			}
			//修改信息
			shower.onUpdateTeacher = function (id) {
				var teacher = shower.getDataByKey(id);
				teacherUpdater.style.display = "inline-block";
				inputerCenter(teacherUpdater);
				//加载角色
				loadRoles(teacherUpdateForm.role, teacher.role);
				//填充其他数据
				teacherUpdateForm.uid.value = teacher.id;
				teacherUpdateForm.name.value = teacher.name;
				teacherUpdateForm.number.value = teacher.number;
			}
			//消息
			shower.onSendMessage = function (id) {
				var teacher = shower.getDataByKey(id);
				top.chat.sendMessage(teacher.type, teacher.id);
			}
			//数据点击
			shower.onTrClick = function (id) {
				var teacher = shower.getDataByKey(id);
				showUserInfo(teacher);
			}
			//显示用户数据
			function showUserInfo(user) {
				userHeadImage.src = __ROOT__ + "/index.php/Home/User/gHead/type/" + user.type + "/id/" + user.id;
				var dataDom = document.querySelector(".data-detail");
				var btnsDomTa = dataDom.querySelector(".buttons-ta");
				var btnsDomMe = dataDom.querySelector(".buttons-me");
				btnsDomTa.style.display = (user.id == top.user.id) ? "none" : "inline-block";
				btnsDomMe.style.display = (user.id == top.user.id) ? "inline-block" : "none";
				for (var i in user) {
					var doms = dataDom.querySelectorAll("[name='" + i + "']");
					for (var j = 0; j < doms.length; j++) {
						doms.item(j).innerHTML = user[i] ? user[i] : "无";
					}
				}
			}

			showUserInfo(top.user);

			loadTeacher(0);
		}

		function loadTeacher(toPage) {
			//数据加载中
			if (page.isLocked())
				return;
			page.lock();
			//删除表格中的数据
			shower.clear();
			//显示加载中
			dataLoading.style.display = "inline-block";
			k360_http.create().setUrl(__ROOT__ + "/index.php/Home/User/gTeacher").addData({ page: toPage, key: key })
			.onsuccess(function (data, xhr) {
				page.unlock();
				//关闭加载中
				dataLoading.style.display = "none";
				if (data.reson) {
					alert(data.reson);
					return;
				}
				page.setInfo(data.data.pages, data.data.count, toPage);
				//显示教师列表
				shower.setDatas(data.data.datas, "id");
				shower.show();
			})
			.onerror(function (xhr, status, statusText) {
				//关闭加载中
				page.unlock();
				dataLoading.style.display = "none";
				k360_popover.create().setTopPop(true).toast("加载出错，错误码：" + status, k360_popover.COLOR_DRAK_BLUE);
			})
			.send();
		}

		function loadRoles(toSelect, selected) {
			function load() {
				k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Role/gRoles")
					.onsuccess(function (data, xhr) {
						if (data.reson) {
							toSelect.innerHTML = "<option disabled>" + data.reson + "</option>";
							return;
						}
						//填充数据
						roles = data.data;
						show();
					})
					.onerror(function (xhr, status, statusText) {
						toSelect.innerHTML = "<option disabled>加载失败，可能是网络问题，" + status + "</option>";
					})
					.send();
			}
			function show() {
				toSelect.innerHTML = "";
				for (var i in roles) {
					var r = roles[i];
					var dom = document.createElement("option");
					dom.value = r.id;
					dom.innerHTML = r.name;
					toSelect.appendChild(dom);
				}
				toSelect.value = selected ? selected : roles[0].id;
			}

			roles ? show() : load();
		}
		return obj;
	}
}