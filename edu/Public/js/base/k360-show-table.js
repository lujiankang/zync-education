window.k360_show_table = {
	/*
	功能：创建对象
	参数：
		selector		HTMLTableElement | String		表格/表格选择器
	*/
	create: function (selector) {
		var obj = {};
		var showDatas = null;
		var baseKey = "id";
		var clonedTR = null;
		var tableBody = null;
		var tableDom = null;
		//控制更新数据后滚动到旧位置
		var scroller = document.body;
		var bufferScroll = 0;

		/*
		功能：设置显示数据
		参数：
			datas		要显示的数据
			key			数据的主键，默认是“id”
		*/
		obj.setDatas = function (datas, key) {
			showDatas = datas;
			key && (baseKey = key);
		}

		/*
		功能：通过主键获取数据
		参数：
			keyVal		主键的值
		*/
		obj.getDataByKey = function (keyVal) {
			for (var i in showDatas) {
				if (showDatas[i][baseKey] == keyVal) {
					return showDatas[i];
				}
			}
			return null;
		}

		/*
		功能：清空表格数据
		*/
		obj.clear = function () {
			while (tableBody.children.length > 0) {
				tableBody.removeChild(tableBody.children[0]);
			}
		}

		/*
		功能：显示数据
		*/
		obj.show = function () {
			for (var i in showDatas) {
				//克隆
				var tr = clonedTR.cloneNode(true);
				//设置id
				tr.setAttribute("did", showDatas[i][baseKey]);
				//填充数据
				for (var j in showDatas[i]) {
					var td = tr.querySelector("[name='" + j + "']");
					td && (td.innerHTML = showDatas[i][j]);
				}
				var more = tr.querySelector(".table-handle-more");
				more && (more.style.display = "none");
				more && (more.onclick = function (e) {
					this.style.display = "none";
					e.stopPropagation();
				});
				//添加tr
				tableBody.appendChild(tr);
				//事件处理
				trEvent(tr, showDatas[i]);
			}
		}

		/*
		功能：设置滚动div，默认是body
		*/
		obj.setScroller = function (scr) {
			scroller = scr;
		}

		/*
		保存滚动条位置
		*/
		obj.saveScroll = function () {
			bufferScroll = scroller.scrollTop;
		}

		/*
		恢复滚动条位置
		*/
		obj.resumeScroll = function () {
			scroller.scrollTop = bufferScroll;
		}

		/*
		功能：便利每个表格行
		参数：
			cb		Function(HTMLTableRowElement tr)		回调函数
		*/
		obj.eachTR = function (cb) {
			var trs = tableBody.querySelectorAll("tr");
			for (var i = 0; i < trs.length; i++) {
				cb(trs.item(i));
			}
		}

		//行事件处理
		function trEvent(tr, data) {
			//绑定事件的handle处理
			var handles = tr.querySelectorAll("[callback]");
			for (var j = 0; j < handles.length; j++) {
				handles[j].onclick = function () {
					var func = this.getAttribute("callback");
					obj[func] && (obj[func](data[baseKey]));
				}
			}
			tr.onclick = function () {
				var cb = tr.getAttribute("callback");
				if (cb) {
					obj[cb] && obj[cb](data[baseKey]);
				}
			}
			//更多handle处理
			var morebtn = tr.querySelector("[name='more']");
			morebtn && (morebtn.onclick = function (e) {
				var moreDom = tr.querySelector(".table-handle-more");
				if (moreDom.style.display != "none") {
					moreDom.style.display = "none";
					return;
				}
				//隐藏其他
				hideMoreBut(this);
				//按钮位置
				var ofs = this.getBoundingClientRect();
				var x = ofs.left;
				var y = ofs.top
				var r = x + this.offsetWidth;
				var b = y + this.offsetHeight;
				//菜单宽高
				moreDom.style.display = "inline-block";
				var mx = x;
				var my = b;
				var mw = moreDom.offsetWidth;
				var mh = moreDom.offsetHeight;
				//窗口宽高
				var ww = window.innerWidth;
				var wh = window.innerHeight;
				//计算菜单位置
				if (x + mw > ww) {
					mx = r - mw;
				}
				if (b + mh > wh) {
					my = y - mh;
				}
				moreDom.style.left = mx + "px";
				moreDom.style.top = my + "px";
				e.stopPropagation();
			});
		}

		//隐藏所有的更多菜单，除了某个之外
		function hideMoreBut(but) {
			var moreDoms = document.querySelectorAll(".table-handle-more");
			for (var i = 0; i < moreDoms.length; i++) {
				if (moreDoms[i] == but) continue;
				moreDoms[i].style.display = "none";
			}
		}

		//初始化
		function init() {
			//获取shower
			var table = null;
			if (selector instanceof HTMLTableElement)
				table = selector;
			else if (typeof (selector) == "string")
				table = document.querySelector(selector);
			if (!table) {
				throw new Error("显示表格无法获取");
			}
			var tr = table.querySelector("tr[name='cloned']");
			if (!tr) {
				throw new Error("显示表格没有显示对象");
				return;
			}
			tableDom = table;
			tableBody = tr.parentElement;
			//克隆shower-tr
			clonedTR = tr.cloneNode(true);
			//鼠标点击隐藏更多
			document.addEventListener("click", function () {
				hideMoreBut(null);
			});
		}

		init();
		return obj;
	}
};