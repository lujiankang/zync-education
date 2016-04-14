window.p_left = {

	create: function () {
		var obj = {};

		var main = document.querySelector(".page-left");
		var loading = main.querySelector(".loading");

		var iframe = document.querySelector(".page-main").children[0];

		var listShowBtn = document.querySelector(".top-menu-btn");

		var pageTop = document.querySelector(".page-top");

		var miniShowWin = document.querySelector(".response_shower");
		var miniframe = miniShowWin.children[0];

		var pageShadow = document.querySelector(".page-shadow");

		var childCloned = null;
		var parentCloned = null;

		obj.init = function () {
			//对象克隆
			cloneBase();
			//获取数据并显示
			loadMenu();
			//点击顶部显示列表按钮
			on([listShowBtn], "click", function () {
				pageShadow.style.display = "inline-block"
				main.style.left = "0px";
			});
			//点击其他地方关闭菜单
			on([pageShadow], "click", function () {
				pageShadow.style.display = "none"
				main.style.left = "-250px";
			});
		}

		//克隆父级菜单以及子级菜单并删除原有内容
		function cloneBase() {
			var p = main.querySelector(".left-parent");
			var c = p.querySelector(".left-child");
			childCloned = c.cloneNode(true);
			p.removeChild(c);
			parentCloned = p.cloneNode(true);
			parentCloned.style.display = "";
			main.removeChild(p);
		}

		function loadMenu() {
			var http = k360_http.create().setUrl(MENU_INFO_GETTER);
			dataLoad(http, "获取系统菜单错误", function (data) {
				//保存用户数据
				drawMenus(data);
				//调用其它加载函数
				loadOther();
			}, function (err) {
				//出现错误
				k360_popover.create().alert("出错了", err, "重新试试")
				.setOnOk(function () {
					window.location.reload(true);
				});
			}, function () {
				//登录结束
				loading.style.display = "none";
			});
		}

		//绘制菜单
		function drawMenus(menus) {
			for (var i = 0; i < menus.length; i++) {
				var amenu = menus[i];
				var children = amenu.children;
				var pDom = parentCloned.cloneNode(true);
				pDom.querySelector(".icon >i").className = "fa " + amenu.icon;
				pDom.querySelector(".name").innerHTML = amenu.name;
				for (var j = 0; j < children.length; j++) {
					var child = children[j];
					var cDom = childCloned.cloneNode(true);
					cDom.querySelector(".icon >i").className = "fa " + child.icon;
					cDom.querySelector(".name").innerHTML = child.name;
					cDom.setAttribute("url", APP_PATH + "/" + child.url);
					//添加url打开事件
					cDom.onclick = function (e) {
						menuOpenUrl(this.getAttribute("url"));
						e.stopPropagation();
					}
					pDom.appendChild(cDom);
				}
				pDom.onclick = function () {
					autoOpenClose(this);
				}
				main.appendChild(pDom);
			}
		}

		function autoOpenClose(but) {
			but.className = /opened/.test(but.className) ? "left-parent" : "left-parent opened";
			var pDoms = main.querySelectorAll(".left-parent.opened");
			for (var i = 0; i < pDoms.length; i++) {
				var dom = pDoms.item(i);
				if (but == dom) continue;
				dom.className = "left-parent";
			}
		}
		
		//打开链接
		function menuOpenUrl(url) {
			//手机
			if (window.innerWidth <= 600) {
				miniframe.src = url;
				miniShowWin.style.display = "inline-block";
				pageShadow.click();
				return;
			}
			iframe.src = url;
		}

		return obj;
	}

};


window.finishGetData = function () {
	//p_left.create().init();
}