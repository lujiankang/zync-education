window.p_search = {
	create: function () {
		var obj = {};
		var main = document.querySelector(".pop-search");

		var form = main.querySelector("form");
		var searchbtn = form.querySelector("i");
		var ul = main.querySelector(".list-group");
		var frameShadow = document.querySelector(".page-container >.page-main >.frame-shadow");
		
		var searching = false;			//是否正在搜索中（锁）
		var liCloned = ul.children[0].cloneNode(true);	//克隆

		//字典
		var colorDict = {
			teacher: "#2980B9",
			student: "#27AE60",
			file: "#D35400"
		};
		var nameDict = {
			teacher: "教师",
			student: "学生",
			file: "文件"
		};

		//初始化方法
		obj.init = function () {
			//删除所有的孩子
			removeChildrenAll(ul);
			//提交搜索表单时调用
			form.onsubmit = function () {
				//空格操作（多个空格换成一个，去掉开始和结尾空格）
				var v = form.key.value;
				v = v.replace(/\s+/gim, " ");
				v = v.replace(/^\s/gim, "");
				v = v.replace(/\s$/gim, "");
				form.key.value = v;
				//空值判断
				if (form.key.value == "" || !form.key.value) return false;
				//锁判断
				if (searching) return false;
				//变量
				removeChildrenAll(ul);
				ul.parentElement.scrollTop = 0;
				searching = true;
				searchbtn.style.animation = "searchAnim 1s infinite ease";
				var http = k360_http.create().addForm(this);
				//加载数据
				dataLoad(http, "搜索失败", function (data) {
					for (var i in data) {
						var tag = nameDict[i];
						var color = colorDict[i];
						for(var j in data[i]){
							putData(tag, color, data[i][j].name, data[i][j], i);
						}
					}
				}, function (err) {
					k360_popover.create().toast(err);
				}, function () {
					searching = false;
					searchbtn.style.animation = "none";
				});
				return false;
			}
			//顶部图标点击的时候调用
			topSearch.onclick = function (e) {
				main.style.display = "inline-block";
				frameShadow.style.display = "inline-block";
				form.key.focus();
				e.stopPropagation();
			}
			//其他地方点击的时候关闭弹窗
			document.onclick = function () {
				main.style.display = "none";
				frameShadow.style.display = "none";
			}
			//点击顶部其他图标关闭当前弹窗
			on([topSetting, topMessage], "click", function () {
				main.style.display = "none";
			});
			//阻止当前弹窗的事件冒泡
			main.onclick = function (e) {
				e.stopPropagation();
			}
			return obj;
		}

		function putData(tag, color, name, data, type) {
			var li = liCloned.cloneNode(true);
			var t = li.querySelector(".text-img");
			var n = li.querySelector(".name");
			t.style.background = color;
			t.innerHTML = tag;
			n.innerHTML = name;
			ul.appendChild(li);
			n.onclick = function () {
				onDataClick.call(li, type, data.id, data.name)
			}
		}

		function onDataClick(type, id, name) {
			if (type == "file") {
				preview.show(id);
			} else if (type == "student" || type == "teacher") {
				userinfoviewer.show(id, type);
			}
		}

		return obj;
	}
}

window.pSearchMain = function () {
	p_search.create().init();
}