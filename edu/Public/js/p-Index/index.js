window.addEventListener("load", function () {
	window.mainIframe = document.querySelector(".main-iframe");
	
	function doLogin() {
		if (!logined) {
			var main = document.querySelector(".u-login");
			var form = main.querySelector("form");
			var button = main.querySelector("button[type='submit']").children[1];
			var win = main.querySelector(".login-container");
			var logo = main.querySelector(".login-logo");

			//登录，如果传有参数，则按指定的用户类型登录
			function login(type) {
				//锁操作
				if (button.innerHTML == "登录中") return;
				button.innerHTML = "登录中";
				//创建http
				var http = k360_http.create().addForm(form);
				if (type) {
					http.addOne("type", type);
				}
				button.parentElement.disabled = true;
				//数据提交
				dataLoad(http, "登录错误", function (data) {
					if (data === false) {
						//多账号
						k360_popover.create().confirm("请选择角色", "系统存在两个相同账号，<span style='color:#F00'>请注意更改密码</span>，请问您是老师还是学生呢？", "教师", "学生")
						.onok(function () {
							login("teacher");
						})
						.oncancel(function () {
							login("student");
						});
						return;
					}
					//登录成功
					afterLogin();
				}, function (err) {
					//出现错误
					k360_popover.create().toast(err);
				}, function () {
					//登录结束
					button.innerHTML = "登录";
				});
			}

			//登录成功后调用此方法
			function afterLogin() {
				anim(win, "hinge", function () {
					main.style.display = "none";
					button.parentElement.disabled = false;
					//自动登陆操作
					if (form.autoLogin.checked) {
						localStorage["k360-edu-zync-number"] = form.number.value;
						localStorage["k360-edu-zync-password"] = form.password.value;
					} else {
						localStorage.removeItem("k360-edu-zync-number");
						localStorage.removeItem("k360-edu-zync-password");
					}
					doGetInfo();
				});
			}

			//页面加载时让登陆框居中
			function toCenter() {
				main.style.display = "inline-block";
				win.style.marginTop = parseInt((window.innerHeight - 350) / 2) + "px";
			}

			toCenter();
			//登录事件
			form.onsubmit = function () {
				login();
				return false;
			}
			//初始化输入框
			var num = localStorage["k360-edu-zync-number"];
			var pwd = localStorage["k360-edu-zync-password"];
			setTimeout(function () {
				form.number.value = num ? num : "";
				form.password.value = pwd ? pwd : "";
			}, 100);


		} else {
			doGetInfo();
		}
	}

	function doGetInfo() {
		//设置iframe
		mainIframe.src = __ROOT__ + "/index.php/Home/Index/pMain";
		//获取用户信息
		indexLoading.style.display = "inline-block";
		var loadingDom = indexLoading.children[0];
		loadingDom.style.marginTop = (window.innerHeight - loadingDom.clientHeight) / 2 + "px";
		//加载用户数据
		var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/User/gMyInfo");
		dataLoad(http, "获取用户资料错误", function (data) {
			window.user = data;
			doGetFuncs();
			doBaseData();
		}, function (err) {
			k360_popover.create().confirm("警告", "获取用户信息失败", "重试")
			.onok(function () {
				window.location.reload(true);
			})
			.oncancel(function () {
				window.document.write(exitHTML);
			});
		});

	}

	function doGetFuncs() {
		var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Index/gFuncs");
		dataLoad(http, "获取用户资料错误", function (data) {
			indexLoading.style.display = "none";
			//显示数据
			var menuList = document.getElementById("main-container").children[0];
			var items = menuList.querySelectorAll(".menu-item");
			while (items.length > 1) {
				menuList.removeChild(items[1]);
				items = menuList.querySelectorAll(".menu-item");
			}
			var itemDom = menuList.children[0].cloneNode(true);
			for (var i in data) {
				var aData = data[i];
				var dom = itemDom.cloneNode(true);
				dom.className = "menu-item";
				dom.querySelector("i").className = "fa " + aData.icon;
				dom.querySelector(".name").innerHTML = aData.name;
				menuList.appendChild(dom);
				domEvent.call(dom, aData);
			}
		}, function (err) {
			k360_popover.create().confirm("警告", "获取功能列表失败", "重试")
			.onok(function () {
				window.location.reload(true);
			})
			.oncancel(function () {
				window.document.write(exitHTML);
			});
		});

		document.getElementById("main-container").children[0].children[0].addEventListener("click", function () {
			mainIframe.src = __ROOT__ + "/index.php/Home/Index/pMain";
			setActive(this);
		});

		function domEvent(data) {
			this.addEventListener("click", function () {
				mainIframe.childMenus = data.children;
				mainIframe.childActive = data.children[0].id;
				mainIframe.src = __ROOT__ + "/index.php/" + data.children[0].url;
				setActive(this);
			});

		}

		function setActive(dom) {
			var menuList = document.getElementById("main-container").children[0];
			for (var i = 0; i < menuList.children.length; i++) {
				var iDom = menuList.children[i];
				if (iDom == dom) {
					iDom.className = "menu-item active";
				} else {
					iDom.className = "menu-item";
				}
			}
		}
	}


	function doBaseData() {
		//头像
		topNavUserHeadImage.src = __ROOT__ + "/index.php/Home/User/gHead/type/" + user.type + "/id/" + user.id;
	}

	/***********************页面基本事件****************************/
	document.querySelector(".full-screen").onclick = function () {
		toggleFullScreen(document.body);
	}
	doLogin();


});
