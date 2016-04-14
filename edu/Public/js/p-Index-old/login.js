window.p_login = {
	create: function () {
		var obj = {};

		var main = document.querySelector(".u-login");
		var form = main.querySelector("form");
		var button = main.querySelector("button[type='submit']").children[1];
		var win = main.querySelector(".login-container");
		var logo = main.querySelector(".login-logo");
		/*
		功能：初始化方法
		*/
		obj.init = function () {
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
		}

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
					.setOnOk(function () {
						login("teacher");
					})
					.setOnCancel(function () {
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
			win.style.animation = "loginFade 500ms forwards";
			//计算+设置logo位置
			logo.style.left = parseInt((window.innerWidth - 200) / 2) + "px";
			logo.style.top = parseInt((window.innerHeight - 200) / 2) + "px";
			logo.style.transform = "scale(0, 0)";
			logo.style.display = "inline-block";
			setTimeout(function () {
				logo.style.animation = "loginLogoAnim 3s forwards";
				setTimeout(function () {
					main.style.display = "none";
					finish();
				}, 3000);
			}, 500);
		}

		//登录成功且动画执行完毕后调用
		function finish() {
			//自动登陆操作
			if (form.autoLogin.checked) {
				localStorage["k360-edu-zync-number"] = form.number.value;
				localStorage["k360-edu-zync-password"] = form.password.value;
			} else {
				localStorage.removeItem("k360-edu-zync-number");
				localStorage.removeItem("k360-edu-zync-password");
			}
			window.finishLogin();
		}

		//页面加载时让登陆框居中
		function toCenter() {
			main.style.display = "inline-block";
			win.style.marginTop = parseInt((window.innerHeight - 350) / 2) + "px";
		}

		return obj;
	}
};

window.addEventListener("load", function () {
	//如果已经登录则不用再登陆了
	if (window.logined == "1") {
		window.finishLogin();
		return;
	}
	p_login.create().init();
});
