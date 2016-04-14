window.p_userinfoviewer = {
	create:function(){
		var obj = {};
		//doms
		var main = document.querySelector(".viewer-userinfo");
		var cont = main.querySelector(".viewer-user-container");
		var head = main.querySelector(".viewer-user-head").querySelector("img");
		var name = main.querySelector(".viewer-user-name");
		var sexi = main.querySelector(".viewer-user-sex").children[0];
		var numb = main.querySelector(".viewer-info-numb");
		var body = main.querySelector('.viewer-user-main');
		var clonedItem = body.children[0].cloneNode(true);
		var loading = body.querySelector(".viewer-loading");
		var lineTa = main.querySelector(".viewer-user-msg");

		//数据转换字典
		var uvdict = {
			type: "类型",
			rolename: "角色",
			classname: "班级",
			tel: "电话",
			qq: "QQ",
			email: "邮箱"
		};

		//初始化
		obj.init = function () {
			main.onclick = function (e) {
				e.stopPropagation();
			}
			return obj;
		}

		//显示用户信息
		obj.show = function (id, type) {
			removeChildrenAll(body, loading);
			//窗口居中
			cont.style.marginTop = (window.innerHeight - cont.clientHeight) / 4 + "px";
			loading.style.display = "inline-block";
			name.innerHTML = "加载中";
			numb.innerHTML = "000000000000";
			sexi.className = getSexClass(null);
			main.style.display = "inline-block";
			head.src = getUserHead(type, id, true);
			loadUserInfo(id, type, function (data) {
				//根据字典显示
				for (var i in uvdict) {
					for (var j in data) {
						if (i == j) {
							var uname = uvdict[i];
							var val = data[j];
							var item = clonedItem.cloneNode(true);
							item.children[0].innerHTML = uname;
							item.children[1].innerHTML = val ? val : "<i class='fa fa-frown-o'>&nbsp;&nbsp;这人太懒</i>";
							body.appendChild(item);
							continue;
						}
					}
				}
				//设置顶部信息
				name.innerHTML = data.name;
				numb.innerHTML = data.number;
				sexi.className = getSexClass(data.sex);
			});
			//点击联系Ta
			lineTa.onclick = function () {
				chat.sendMessage(type, id);
			};
		}

		//加载用户信息
		function loadUserInfo(id, type, callback) {
			var http = k360_http.create().setUrl(USER_SHOW_GETTER).addData({ id: id, type: type });
			dataLoad(http, "加载用户信息错误", function (data) {
				callback(data);
			}, function (err) {
				k360_popover.create().toast(err);
			}, function () {
				loading.style.display = "none";
			});

		}

		//获取用户性别图标
		function getSexClass(sex) {
			var str = "fa viewer-user-sex ";
			if (sex == "男") str += "fa-mars";
			else if (sex == "女") str += "fa-venus";
			else str += "fa-venus-mars";
			return str;
		}

		return obj;
	}
};

window.addEventListener("load", function () {
	userinfoviewer = p_userinfoviewer.create().init();
});