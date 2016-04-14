window.k360_popover = {
	create: function () {
		var obj = {
			dom: null,
			type: k360_popover.TYPE_NONE,
			id: 0,
			bgcolor: "rgba(0, 0, 0, 0.5)",
			document: document,
			tipPos: k360_popover.POS_RB,
			_cb: {
				onok: function () { },
				oncancel: function () { },
			}
		};


		/*
		功能：获取弹出框的Dom的id属性
		返回值：
			如果有则返回id，否则返回null
		*/
		obj.getAttrId = function () {
			if (!obj)
				return null;
			if (obj.type == k360_popover.TYPE_NONE || obj.id == 0) {
				return null;
			}
			return obj.type + obj.id;
		}

		/*
		功能：设置背景色
		参数：
			r	int		红色值（0~255）
			g	int		绿色值（0~255）
			b	int		蓝色值（0~255）
			a	float	透明度（0~1）
		说明：
			对于参数，可以有以下几种传值方式，除了这几种方式之外的其他传值方式均为无效
			1、分别传入颜色的r、g、b、a四个值，如：setBgcolor(255, 0, 0, 0.5)
			2、分别传入颜色的r、g、b三个值，如：setBgcolor(255, 0, 0)，此时透明度为1（不透明）
			3、传入颜色字符串，如："#FF0000"、"#F00"、"rgb(255, 0, 0)"、rgba(255, 0, 0, 0.5)等等
		返回值：
			当前对象
		*/
		obj.setBgcolor = function (r, g, b, a) {
			if (a==undefined) {
				if (r != undefined && g != undefined && b != undefined) {
					obj.bgcolor = "rgb(" + r + ", " + g + ", " + b + ")";
				} else {
					if (("" + r).substr(0, 3) == "rgb" || ("" + r).substr(0, 1) == "#") {
						obj.bgcolor = r;
					} else {
						return obj;
					}
				}
			} else {
				obj.bgcolor = "rgba(" + r + ", " + g + ", " + b + ", " + a + ")";
			}
			return obj;
		}

		/*
		功能：设置是否在最上层窗口弹出
		参数：
			isTopPop	bool		如果为true则在最上层窗口弹出，否则在当前窗口弹出
		返回值：
			当前对象
		*/
		obj.setTopPop = function (isTopPop) {
			if (isTopPop === true) {
				obj.document = top.document;
			} else {
				obj.document = document;
			}
			return obj;
		}

		/*
		功能：添加点击确定的处理时间
		参数：
			onok		Function()		点击确定后调用此函数
		返回值：
			当前对象
		*/
		obj.onok = obj.setOnOk = function (onok) {
			obj["_cb"].onok = onok;
			return obj;
		}

		/*
		功能：添加点击取消处理事件
		参数：
			oncancel	Function()		点击取消后调用此函数
		返回值：
			当前对象
		*/
		obj.oncancel = obj.setOnCancel = function (oncancel) {
			obj["_cb"].oncancel = oncancel;
			return obj;
		}


		/*
		功能：创建并显示加载中
		返回值：
			当前对象
		*/
		obj.loading = function () {
			var dom = obj.document.createElement("div");
			//阻止事件冒泡
			stopPropagation(dom);
			dom.setAttribute("class", "k-popover");
			dom.style.backgroundImage = "url(" + k360_popover.IMG_LOADING + ")";
			dom.style.backgroundColor = obj.bgcolor;
			dom.style.backgroundPosition = "center 40%";
			dom.style.backgroundRepeat = "no-repeat";
			obj.type = k360_popover.TYPE_LOADING;
			obj.id = createId();
			dom.id = obj.getAttrId();
			obj.document.body.appendChild(dom);
			dom.focus();
			obj.dom = dom;
			return obj;
		}

		/*
		功能：创建并显示alert弹窗
		参数：
			title		String		alert的标题
			content		String		alert的内容
			onBtnText	String		确定按钮上的文本，默认为“确定”
		返回值：
			当前对象
		*/
		obj.alert = function (title, content, okBtnText) {
			//参数处理
			okBtnText || (okBtnText = "确定");
			//创建dom
			var dom = obj.document.createElement("div");
			//阻止事件冒泡
			stopPropagation(dom);
			dom.setAttribute("class", "k-popover");
			var win = (obj.document == document) ? window : top;
			var mtop = parseInt(win.innerHeight / 2 - 100);
			dom.innerHTML = "<div class=\"k-popover-win in\" style=\"margin-top:" + mtop + "px\">\
				<div class=\"title\">" + title + "</div>\
				<div class=\"body\">" + content + "</div>\
				<div class=\"buttons\">\
					<div class=\"k-popover-button blue\">" + okBtnText + "</div>\
					<div style=\"clear:both\"></div>\
				</div>\
			</div>";
			//禁用选择
			setCantSelect(dom);
			//处理dom属性
			obj.type = k360_popover.TYPE_ALERT;
			obj.id = createId();
			dom.style.backgroundColor = obj.bgcolor;
			dom.id = obj.getAttrId();
			obj.document.body.appendChild(dom);
			dom.focus();
			obj.dom = dom;
			//事件处理
			dom.querySelector(".k-popover-button.blue").addEventListener("click", function () {
				obj["_cb"].onok && obj["_cb"].onok();
				elemOut(function () {
					dom.innerHTML = "";
					obj.distroy();
				});
			});
			return obj;
		}

		/*
		功能：创建并显示confirm
		参数：
			title			String		confirm的标题
			content			String		confirm的内容
			okBtnText		String		confirm确定按钮上的文字，默认为“确定”
			cancelBtnText	String		confirm取消按钮上的文字，默认为“取消”
		*/
		obj.confirm = function (title, content, okBtnText, cancleBtnText) {
			//初始化参数
			okBtnText || (okBtnText = "确定");
			cancleBtnText || (cancleBtnText = "取消");
			//创建dom
			var dom = obj.document.createElement("div");
			//阻止事件冒泡
			stopPropagation(dom);
			dom.setAttribute("class", "k-popover");
			var win = (obj.document == document) ? window : top;
			var mtop = parseInt(win.innerHeight / 2 - 100);
			dom.innerHTML = "<div class=\"k-popover-win in\" style=\"margin-top:" + mtop + "px\">\
				<div class=\"title\">" + title + "</div>\
				<div class=\"body\">" + content + "</div>\
				<div class=\"buttons\">\
					<div class=\"k-popover-button blue\">" + okBtnText + "</div>\
					<div class=\"k-popover-button default\">" + cancleBtnText + "</div>\
					<div style=\"clear:both\"></div>\
				</div>\
			</div>";
			//禁用选择
			setCantSelect(dom);
			//dom属性设置
			obj.type = k360_popover.TYPE_CONFIRM;
			obj.id = createId();
			dom.style.backgroundColor = obj.bgcolor;
			dom.id = obj.getAttrId();
			obj.document.body.appendChild(dom);
			dom.focus();
			obj.dom = dom;
			//事件处理
			var okbtn = dom.querySelector(".k-popover-button.blue").addEventListener("click", function () {
				obj["_cb"].onok && obj["_cb"].onok();
				elemOut(function () {
					dom.innerHTML = "";
					obj.distroy();
				});
			});
			var cancelbtn = dom.querySelector(".k-popover-button.default").addEventListener("click", function () {
				obj["_cb"].oncancel && obj["_cb"].oncancel();
				elemOut(function () {
					dom.innerHTML = "";
					obj.distroy();
				});
			});
			return obj;
		}

		/*
		功能：打开提示框
		参数：
			text		String			提示框文本
			position	int				提示框位置
			color		String			提示框颜色
			during		int				提示框持续时间，单位（ms），默认10s
		说明：
			position取值有如下几种：
				1、POS_LT		左上角
				2、POS_LB		左下角
				3、POS_RT		右上角
				4、POS_RB		右下角，默认位置
			color的取值可以有如下几种
				1、COLOR_DRAK_BLUE		深蓝色，默认颜色
				2、COLOR_BLUE			蓝色
				3、COLOR_RED			红色
				4、COLOR_ORANGE			橙色
				5、COLOR_GREEN			绿色
				6、其他自定义颜色
		返回值：
			当前对象
		*/
		obj.tip = function (text, position, color, during) {
			position || (position = k360_popover.POS_RB);
			color || (color = k360_popover.COLOR_DRAK_BLUE);
			during || (during = 10000);
			var TIPHEIGHT = 60;
			//获取样式
			var style = {};
			var yPos = 0;
			var tips = obj.document.querySelectorAll("[tiptype='tippos" + position + "']");
			yPos = tips.length;
			switch (position) {
				case k360_popover.POS_LT:
					style = { left: "10px", top: yPos * TIPHEIGHT + 10 + "px" };
					break;
				case k360_popover.POS_LB:
					style = { left: "10px", bottom: yPos * TIPHEIGHT + 10 + "px" };
					break;
				case k360_popover.POS_RT:
					style = { right: "10px", top: yPos * TIPHEIGHT + 10 + "px" };
					break;
				case k360_popover.POS_RB:
					style = { right: "10px", bottom: yPos * TIPHEIGHT + 10 + "px" };
					break;
				default:
					return obj;
			}
			style.backgroundColor = color;
			//创建dom
			var dom = obj.document.createElement("div");
			//阻止事件冒泡
			stopPropagation(dom);
			dom.innerHTML = "<div class=\"text\">" + text + "</div>\
			<div class=\"close\">╳</div>";
			dom.setAttribute("class", "k-popover-tip in");
			//设置dom属性，并添加到body
			setStyle(dom, style);
			setCantSelect(dom);
			obj.id = createId();
			obj.type = k360_popover.TYPE_TIP;
			dom.id = obj.getAttrId();
			dom.setAttribute("tiptype", "tippos" + position)
			obj.tipPos = position;
			obj.document.body.appendChild(dom);
			obj.dom = dom;
			//创建定时器监听
			var timer = setTimeout(closetip, during);
			//添加关闭事件
			var closeBtn = dom.querySelector(".close");
			closeBtn.addEventListener("click", closetip);
			//关闭提示框，同时删除
			function closetip() {
				//先清除提示框的各项属性
				clearTimeout(timer);
				closeBtn.removeEventListener("click", closetip);
				//删除
				tipOut(tipFinishAnim);
			}
			return obj;
		}

		/*
		*/
		obj.toast = function (text, color, during) {
			color || (color = k360_popover.COLOR_WHITE);
			during || (during = 6000);
			//将之前的toast透明
			var otherdoms = document.querySelectorAll(".k-popover-toast");
			for (var i = 0; i < otherdoms.length; i++) {
				otherdoms[i].style.opacity = "0.6";
			}
			//创建dom
			var dom = obj.document.createElement("div");
			//阻止事件冒泡
			stopPropagation(dom);
			//设置属性
			dom.innerHTML = text;
			dom.setAttribute("class", "k-popover-toast in");
			dom.style.backgroundColor = color;
			dom.id = createId();
			setCantSelect(dom);
			//添加到body
			obj.document.body.appendChild(dom);
			obj.dom = dom;
			//重定义位置
			var toastDom = obj.document.getElementById(dom.id);
			var win = (obj.document == document) ? window : top;
			var left = (win.innerWidth - toastDom.clientWidth) / 2 - 40;
			toastDom.style.left = left + "px";
			//定时关闭
			var timer = setTimeout(function () {
				clearTimeout(timer);
				obj.dom.setAttribute("class", "k-popover-toast out");
				//等待动画结束
				timer = setTimeout(function () {
					obj.dom.innerHTML = "";
					obj.distroy();
				}, 400);
			}, during);
		}

		/*
		功能：删除当前对象同时会删除弹窗
		说明：
			此方法仅限于loading，其他控件切记不能调用此方法
		*/
		obj.distroy = function () {
			var dom = obj.dom;
			if (!dom)
				return;
			dom.parentElement.removeChild(dom);
			obj = null;
		}

		//阻止事件冒泡
		function stopPropagation(dom) {
			dom.onclick = function (e) {
				e.stopPropagation();
			}
		}

		//生成随机id
		function createId(){
			return (new Date()).getTime() + "" + parseInt(Math.random() * 65535);		//已当前时间+随机数作为id
		}

		//为某个dom设置样式
		function setStyle(dom, style) {
			for (var name in style) {
				dom.style[name] = style[name];
			}
		}

		//让提示框消失
		function tipOut(onFinish) {
			var dom = obj.dom;
			dom.setAttribute("class", "k-popover-tip out");
			setTimeout(onFinish, 500);
		}

		//让警告框消失
		function elemOut(onFinish) {
			var dom = obj.dom.children[0];
			dom.setAttribute("class", "k-popover-win out");
			onFinish();
		}

		//设置都没不允许选择
		function setCantSelect(dom) {
			dom.onselectstart = function () {
				return false;
			}
		}

		//提示框动画结束
		function tipFinishAnim() {
			obj.dom.innerHTML = "";
			//位置调整
			var tips = obj.document.querySelectorAll("[tiptype='tippos" + obj.tipPos + "']");
			var beSub = false;
			for (var i = 0; i < tips.length; i++) {
				var aTip = tips[i];
				if (aTip == obj.dom) {
					beSub = true;
					continue;
				}
				if (beSub) {
					var top = parseInt(aTip.style.top);
					var bottom = parseInt(aTip.style.bottom);
					if (top) {
						aTip.style.top = top - 60 + "px";
					}
					else if (bottom) {
						aTip.style.bottom = bottom - 60 + "px";
					}
				}
			}
			obj.distroy();
		}

		return obj;
	},


	/*控件类型*/
	TYPE_NONE: "none",
	TYPE_LOADING: "loading",
	TYPE_ALERT: "alert",
	TYPE_CONFIRM: "confirm",
	TYPE_TIP: "tip",

	/*提示框颜色*/
	COLOR_DRAK_BLUE: "rgb(52, 73, 94)",
	COLOR_BLUE: "rgb(41, 127, 184)",
	COLOR_RED: "rgb(193, 57, 43)",
	COLOR_ORANGE: "rgb(210, 84, 0)",
	COLOR_GREEN: "rgb(39, 174, 97)",
	COLOR_WHITE: "rgb(255, 255, 255)",

	/*提示框位置*/
	POS_RT: 0x0001,
	POS_RB: 0x0002,
	POS_LT: 0x0003,
	POS_LB: 0x0004,



	IMG_LOADING: "data:image/gif;base64,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",
};

(function () {
	//填写CSS样式
	var kpopoverstyle = "\
@keyframes popoverIn{\
	/*0%		{transform:translateX(150px) skewX(20deg); opacity:.5}\
	50%		{transform:translateX(40px) skewX(20deg)}\
	75%		{transform:translateX(-10px) skewX(-5deg); opacity:1}\
	100%	{transform:translateX(0px) skewX(0deg)}*/\
}\
@keyframes popoverOut{\
	/*30%		{transform:rotateZ(90deg)}\
	40%		{transform:rotateZ(90deg)}\
	100%	{transform:translateY(300px) rotateZ(90deg); opacity:.2}*/\
}\
@keyframes tipShow{\
	0%		{transform:rotateX(90deg) scaleX(1.1); opacity:0.1}\
	50%		{transform:rotateX(0deg) scaleX(1.1); opacity:1.0;}\
	100%	{transform:rotateX(0deg) scaleX(1.0); opcity:1.0;}\
}\
@keyframes tipHide{\
	100%	{transform:rotateX(90deg); opacity:0}\
}\
@keyframes toastShow{\
	0%		{opacity:0}\
	100%	{opacity:1}\
}\
@keyframes toastHide{\
	100%{opacity:0}\
}\
.k-popover{\
	position:fixed;\
	left:0px;\
	top:0px;\
	right:0px;\
	bottom:0px;\
	z-index:999;\
}\
.k-popover-win{\
	width:380px;\
	height:200px;\
	background:#FFF;\
	margin:auto;\
	z-index:999;\
}\
.k-popover-win.in{\
	animation:popoverIn 300ms forwards linear;\
}\
.k-popover-win.out{\
	animation:popoverOut 600ms forwards linear;\
}\
.k-popover-win .title{\
	padding:10px 15px 25px 15px;\
	font-family:'Microsoft Yahei', 'Helvetica Neue', Helvetica, Arial, sans-serif;\
	font-size:14px;\
	overflow:hidden;\
	text-overflow:ellipsis;\
	white-space:nowrap;\
}\
.k-popover-win .body{\
	width:340px;\
	height:75px;\
	margin:auto;\
	font-size:14px;\
	overflow:hidden;\
	font-family:'Microsoft Yahei', 'Helvetica Neue', Helvetica, Arial, sans-serif;\
}\
.k-popover-win .buttons{\
	width:100%;\
	padding-top:10px;\
}\
.k-popover-button{\
	display:inline-block;\
	padding:0px 10px;\
	height:40px;\
	border:solid 1px transparent;\
	float:right;\
	margin-right:20px;\
	text-align:center;\
	line-height:40px;\
	font-family:'Microsoft Yahei', 'Helvetica Neue', Helvetica, Arial, sans-serif;\
	font-size:11pt;\
	cursor:default;\
	transition:all 200ms;\
	overflow:hidden;\
	cursor:pointer;\
	color:#2980B9;\
}\
.k-popover-button:hover{\
	color:#3498DB;\
}\
.k-popover-tip{\
	position:fixed;\
	font-family:'Microsoft Yahei', 'Helvetica Neue', Helvetica, Arial, sans-serif;\
	border-radius:3px;\
	padding:0px 10px;\
	cursor:default;\
	z-index:999;\
}\
.k-popover-tip.in{\
	animation:tipShow 1000ms forwards linear;\
}\
.k-popover-tip.out{\
	animation:tipHide 500ms forwards linear;\
}\
.k-popover-tip .text{\
	display:inline-block;\
	width:270px;\
	color:#FFF;\
	line-height:50px;\
	font-size:11pt;\
	overflow:hidden;\
	text-overflow:ellipsis;\
	white-space:nowrap;\
}\
.k-popover-tip .close{\
	display:inline-block;\
	width:20px;\
	height:20px;\
	margin-top:15px;\
	vertical-align:top;\
	cursor:pointer;\
	color:#FFF;\
	text-align:center;\
	font-weight:900;\
	transition:all 200ms;\
}\
.k-popover-tip .close:hover{\
	color:#808080;\
}\
.k-popover-toast.in{\
	animation:toastShow 500ms linear;\
}\
.k-popover-toast.out{\
	animation:toastHide 400ms linear;\
}\
.k-popover-toast{\
	position:fixed;\
	bottom:13%;\
	max-width:500px;\
	text-align:center;\
	height:50px;\
	background:#FFF;\
	border:solid 1px #CCC;\
	display:inline-block;\
	padding:0px 40px;\
	line-height:50px;\
	font-family:'Microsoft Yahei', 'Helvetica Neue', Helvetica, Arial, sans-serif;\
	font-size:14px;\
	color:#333;\
	overflow:hidden;\
	text-overflow:ellipsis;\
	white-space:nowrap;\
	cursor:default;\
	box-shadow:3px 3px 3px #888;\
	z-index:999;\
}\
\
\
\
\
@media screen and (max-width: 600px){\
	.k-popover-win{\
		width:100%;\
	}\
	.k-popover-toast{\
		max-width:280px\
	}\
	\
	\
}\
";
	var dom = document.createElement("style");
	dom.id = "k360-popover-style";
	dom.innerHTML = kpopoverstyle;
	document.head.appendChild(dom);

	//document.writeln("<style id='k360-popover-style'>" + kpopoverstyle + "</style>");

	if (top.document.querySelectorAll("#k360-popover-style").length == 0) {
		var dom = top.document.createElement("style");
		dom.id = "k360-popover-style";
		dom.innerHTML = kpopoverstyle;
		top.document.head.appendChild(dom);
	}
})();
//})();