/*
k360 滚动条使用说明：
	在制定的标签上加入相应属性即可产生滚动条，具体属性如下:
		k360-scroll-x			横向滚动条
		k360-scroll-y			纵向滚动条
		k360-scroll-disabled	让滚动条不响应鼠标轮滚事件
		k360-scroll-keep		让滚动条一直显示着
	滚动条的默认大小是8个像素，如果你想设置大小，可以给k360-scroll-x或k360-scroll-y添加值。

注意：
	添加滚动条的div的css样式中，position，必须为fixed,absolute,relative之一

实例：
	创建6个像素大小的纵向滚动条
		<div k360-scroll-y="6">
			... ...
		</div>
	创建默认大小的纵向滚动条
		<div k360-scroll-y>
			... ...
		</div>
	创建横向滚动条
		<div k360-scroll-x>
			... ...
		</div>
	创建双向滚动条
		<div k360-scroll-x k360-scroll-y>
			... ...
		</div>
*/


window.k360_scroll = {
	/*
	功能：创建滚动条管理器
	*/
	create: function () {
		if (k360_scroll.obj)
			return k360_scroll.obj;
		k360_scroll.obj = {
			conf: {
				canDrag: false,
				mousePos: 0,
				curPos: 0,
				mouseovered: false,
				msUserSelect: "text",
				webkitUserSelect: "text",
				mozUserSelect: "text"
			},
			elem: {
				curBar: null,
				curDom: null,
				curXory: "y"
			}
		};
		var obj = k360_scroll.obj;

		/*
		功能：刷新滚动插件，将没有添加滚动的Dom添加上滚动
		*/
		obj.refresh = function () {
			//获取所有的滚动条控件
			var xScrolls = document.querySelectorAll("[k360-scroll-x]");
			var yScrolls = document.querySelectorAll("[k360-scroll-y]");
			//手机端
			if (top.innerWidth <= 600) {
				for (var i = 0; i < xScrolls.length; i++) {
					scrolled(xScrolls.item(i));
				}
				for (var i = 0; i < yScrolls.length; i++) {
					scrolled(yScrolls.item(i));
				}
				function scrolled(dom) {
					dom.style.overflow = "auto";
				}
				return;
			}
			//设置滚动条
			for (var i = 0; i < xScrolls.length; i++) {
				var dom = xScrolls.item(i);
				var size = parseInt(dom.attributes.getNamedItem("k360-scroll-x").value);
				size || (size = 8);
				var scroll = createScrollBar("x", "#CCC", "#888", "transparent", size);
				dom.appendChild(scroll);
				if (dom.hasAttribute("k360-scroll-keep")) scroll.style.opacity = 1;
				var bar = scroll.children.item(0);
				setScroll("x", dom, bar);
				setEvent("x", dom, bar);
			}
			for (var i = 0; i < yScrolls.length; i++) {
				var dom = yScrolls.item(i);
				var size = parseInt(dom.attributes.getNamedItem("k360-scroll-y").value);
				size || (size = 8);
				var scroll = createScrollBar("y", "#CCC", "#888", "transparent", size);
				dom.appendChild(scroll);
				if (dom.hasAttribute("k360-scroll-keep")) scroll.style.opacity = 1;
				var bar = scroll.children.item(0);
				setScroll("y", dom, bar);
				setEvent("y", dom, bar);
			}
			return obj;
		}

		/*
		功能：初始化滚动插件，需在创建完成后调用
		*/
		obj.init = function () {
			obj.refresh();
			document.addEventListener("mouseup", function () {
				if (obj.conf.canDrag) {
					obj.conf.canDrag = false;
					//恢复选择状态
					document.body.style.msUserSelect = obj.conf.msUserSelect;
					document.body.style.webkitUserSelect = obj.conf.webkitUserSelect;
					document.body.style.mozUserSelect = obj.conf.mozUserSelect;
				}
			});
			document.addEventListener("mousemove", function (e) {
				if (!obj.conf.canDrag)
					return;
				var dom = obj.elem.curDom;
				var bar = obj.elem.curBar;
				var xory = obj.elem.curXory;
				var curPos = (xory == "x") ? e.clientX : e.clientY;
				//获取所需属性
				var barPos = parseInt(bar.style[(xory == "x") ? "left" : "top"]);
				var pos = parseInt(curPos - obj.conf.mousePos + obj.conf.curPos);
				var wh = (xory == "x") ? dom.clientWidth : dom.clientHeight;
				var ch = (xory == "x") ? dom.scrollWidth : dom.scrollHeight;
				var sh = (xory == "x") ? bar.clientWidth : bar.clientHeight;
				var oh = parseInt(pos * (ch - wh) / (wh - sh));
				(xory == "x") ? (dom.scrollLeft = oh) : (dom.scrollTop = oh);
				overflowSet(dom);
				setScroll(xory, dom, bar);
				//设置滚动条新位置
				setBarBack(xory, dom, bar);
			});
			return obj;
		}

		/*创建滚动条，传入"x"穿件x方向的滚动条，传入"y"创建y方向的滚动条*/
		function createScrollBar(xory, bgColor, topColor, borderColor, size) {
			//创建div并设置关系
			var back = document.createElement("div");
			var bar = document.createElement("div");
			back.appendChild(bar);
			//设置标记
			back.setAttribute("kscroll" + xory, "true");
			//设置动画属性
			//back.style.transition = "all 500ms";
			//bar.style.transition = "all 40ms";
			//设置布局属性
			back.style.position = "absolute";
			bar.style.position = "absolute";
			//外观属性
			bar.style.borderRadius = parseInt(size / 2) + "px";
			back.style.background = bgColor;
			bar.style.background = topColor;
			//设置位置
			if (xory == "x") {
				back.style.left = "0px";
				back.style.bottom = "0px";
				back.style.right = "0px";
				back.style.height = size + "px";
				bar.style.height = size + "px";
			} else {
				back.style.top = "0px";
				back.style.right = "0px";
				back.style.bottom = "0px";
				back.style.width = size + "px";
				bar.style.width = size + "px";
			}
			//其他属性
			back.style.opacity = 0;
			back.style.zIndex = 999;
			return back;
		}

		/*自动设置滚动条属性*/
		function setScroll(xory, dom, bar) {
			if (!dom) return;
			//设置上级属性
			dom.style.overflow = "hidden";
			//长度公式：sh = wh * wh / ch
			//开始公式：pos = (wh - sh) * oh / (ch - wh)
			var wh = (xory == "x") ? dom.clientWidth : dom.clientHeight;
			var ch = (xory == "x") ? dom.scrollWidth : dom.scrollHeight;
			var oh = (xory == "x") ? dom.scrollLeft : dom.scrollTop;
			var sh = parseInt(wh * wh / ch);
			var pos = parseInt((wh - sh) * oh / (ch - wh));
			if (xory == "x") {
				bar.style.left = pos + "px";
				bar.style.width = sh + "px";
			} else {
				bar.style.top = pos + "px";
				bar.style.height = sh + "px";
			}
		}

		function overflowSet(dom) {
			if (!dom) return null;
			//超过处理
			if (dom.scrollTop > dom.scrollHeight - dom.clientHeight) {
				dom.scrollTop = dom.scrollHeight - dom.clientHeight;
				return false;
			}

			if (dom.scrollLeft > dom.scrollWidth - dom.clientWidth) {
				dom.scrollLeft = dom.scrollWidth - dom.clientWidth;
				return false;
			}
			return true;
		}

		function setEvent(xory, dom, bar) {
			if (!dom) return;
			var scrollTimers = [];
			var showTimer = null;
			var oldHeight = dom.scrollHeight;
			var oldDelta = -1;
			//鼠标点击，用于拖动
			bar.onmousedown = function (e) {
				//保存选择状态
				obj.conf.msUserSelect = document.body.style.msUserSelect;
				obj.conf.webkitUserSelect = document.body.style.webkitUserSelect;
				obj.conf.mozUserSelect = document.body.style.mozUserSelect;
				//禁用选择
				document.body.style.msUserSelect = "none";
				document.body.style.webkitUserSelect = "none";
				document.body.style.mozUserSelect = "none";
				//保存状态
				obj.conf.mousePos = (xory == "x") ? e.clientX : e.clientY;
				obj.conf.canDrag = true;
				obj.conf.curPos = parseInt((xory == "x") ? bar.style.left : bar.style.top);
				obj.conf.curPos || (obj.conf.curPos = 0);
				obj.elem.curBar = bar;
				obj.elem.curDom = dom;
				obj.elem.curXory = xory;
				bar.parentElement.style.opacity = 0.8;
			}
			//轮滚
			function mouseWheel(delta, e) {
				if (bar.parentElement.attributes.getNamedItem("k360-scroll-disabled"))
					return;
				if (oldDelta != delta) {
					for (var i in scrollTimers) {
						clearInterval(scrollTimers[i]);
					}
					scrollTimers = [];
				}
				oldDelta = delta;
				var times = 0;
				var timer = setInterval(function () {
					(xory == "x") ? (dom.scrollLeft -= delta * 4) : (dom.scrollTop -= delta * 4);
					//处理超出
					overflowSet(dom);
					setScroll(xory, dom, bar);
					setBarBack(xory, dom, bar);
					times++;
					if (times >= 20) {
						for (var i in scrollTimers) {
							if (scrollTimers[i] == timer) {
								clearInterval(scrollTimers[i]);
								scrollTimers.splice(i, 1);
								break;
							}
						}
					}
				}, 15);
				scrollTimers.push(timer);
				if (xory == "x") {
					if ((dom.scrollLeft <= 0 && delta > 0) || (dom.scrollLeft >= dom.scrollWidth - dom.clientWidth-1 && delta < 0)) {
						return true;
					}
				} else {
					if ((dom.scrollTop <= 0 && delta > 0) || (dom.scrollTop >= dom.scrollHeight - dom.clientHeight-1 && delta < 0)) {
						return true;
					}
				}
				e.stopPropagation();
				return false;
			}
			//鼠标轮滚事件处理
			dom.onmousewheel = function (e) {
				//IE Chrome
				var delta = 0;
				if (e.wheelDelta)
					delta = e.wheelDelta;
				return mouseWheel((delta > 0) ? 1 : -1, e);
			}
			dom.addEventListener('DOMMouseScroll', function (e) {
				//FireFox
				return mouseWheel((e.detail > 0) ? -1 : 1, e);
			}, false);
			//鼠标移入显示
			dom.addEventListener("mouseover", function () {
				if (dom.scrollHeight <= dom.clientHeight && !this.hasAttribute("k360-scroll-keep")) {
					bar.parentElement.style.opacity = 0;
					return;
				}
				if (obj.conf.canDrag)
					return;
				obj.conf.mouseovered = true;
				if (showTimer)
					clearInterval(showTimer);
				showTimer = setInterval(function () {
					var opcity = parseFloat(bar.parentElement.style.opacity);
					opcity || (opcity = 0);
					if (opcity >= 0.8) {
						clearInterval(showTimer);
						showTimer = null;
					}
					bar.parentElement.style.opacity = opcity + 0.05;
				}, 10);
			});
			//鼠标移出隐藏
			dom.addEventListener("mouseleave", function () {
				if (this.hasAttribute("k360-scroll-keep")) {
					bar.parentElement.style.opacity = 1;
					return;
				}
				if (dom.scrollHeight <= dom.clientHeight && !this.hasAttribute("k360-scroll-keep")) {
					bar.parentElement.style.opacity = 0;
					return;
				}
				obj.conf.mouseovered = false;
				if (obj.conf.canDrag)
					return;
				if (showTimer)
					clearInterval(showTimer);
				showTimer = setInterval(function () {
					var opcity = parseFloat(bar.parentElement.style.opacity);
					opcity || (opcity = 0.05);
					if (opcity <= 0.05) {
						clearInterval(showTimer);
						showTimer = null;
					}
					bar.parentElement.style.opacity = opcity - 0.05;
				}, 10);
			});
			//监听高度改变事件
			setInterval(function () {
				//if (oldHeight != dom.scrollHeight) {
				//	oldHeight = dom.scrollHeight;
					setScroll(xory, dom, bar);
					setBarBack(xory, dom, bar);
					overflowSet(dom);
				//}
			}, 125);
		}

		function setBarBack(xory, dom, bar) {
			if (!dom) return;
			var newOh = (xory == "x") ? dom.scrollLeft : dom.scrollTop;
			var scrollx = dom.querySelector("[kscrollx]");
			var scrolly = dom.querySelector("[kscrolly]");
			if (xory == "x") {
				scrollx && (scrollx.style.left = newOh + "px");
				scrollx && (scrollx.style.right = -newOh + "px");
				scrolly && (scrolly.style.right = -newOh + "px");
			} else {
				scrolly && (scrolly.style.bottom = -newOh + "px");
				scrolly && (scrolly.style.top = newOh + "px");
				scrollx && (scrollx.style.bottom = -newOh + "px");
			}
		}

		return obj;
	}
};

window.addEventListener("load", function () {
	k360_scroll.create().init();
});