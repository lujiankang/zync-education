/*
功能：输入条垂直居中
参数：
	elem		dom			要居中的输入条
*/
function inputerCenter(elem) {
	if (window.innerWidth <= 600) return;
	var cd = elem.children[0];
	var y = (elem.clientHeight - cd.clientHeight) >> 1;
	(y < 0) && (y = 0);
	cd.style.top = y + "px";
}

/*
功能：全屏显示某个元素
参数：
	element		dom		要全屏显示的dom
*/
function fullScreen(element) {
	if (element.requestFullscreen) {
		element.requestFullscreen();
	} else if (element.mozRequestFullScreen) {
		element.mozRequestFullScreen();
	} else if (element.webkitRequestFullscreen) {
		element.webkitRequestFullscreen();
	} else if (element.msRequestFullscreen) {
		element.msRequestFullscreen();
	}
}

/*
功能：退出全屏显示
*/
function exitFullScreen() {
	if (document.exitFullscreen) {
		document.exitFullscreen();
	} else if (document.msExitFullscreen) {
		document.msExitFullscreen();
	} else if (document.mozCancelFullScreen) {
		document.mozCancelFullScreen();
	} else if (document.webkitExitFullscreen) {
		document.webkitExitFullscreen();
	}
}

/*
功能：全屏与非全屏之间切换
*/
function toggleFullScreen(element) {
	if (!document.fullscreenElement &&    // alternative standard method
		!document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {  // current working methods
		fullScreen(element);
	} else {
		exitFullScreen();
	}
}

/*
功能：数字转换成汉语拼音
参数：
	num		int			要转换的数字
*/
function number2chinese(num) {
	function base(number) {
		var suff = ["", "十", "百", "千"];
		var dict = ["零", "一", "二", "三", "四", "五", "六", "七", "八", "九"];
		var zero = true;
		var buffer = "";
		var index = 0;
		do {
			var ge = number % 10;
			number = parseInt(number / 10);
			(ge == 0) || (zero = false);
			if (ge == 0){
				if (!zero && buffer.charAt(0) != dict[0]) {
					buffer = dict[ge] + buffer;
				}
			} else {
				buffer = dict[ge] + suff[index] + buffer;
			}
			index++;
		} while (number > 0);
		//特殊情况
		var b = "" + num;
		if (b.length == 2 && b.charAt(0) == 1) return buffer.substr(1);
		return buffer;
	}
	function big(bumber) {
		var suff = ["", "万", "亿"];
		var index = 0;
		var buffer = "";
		do {
			var baseNum = bumber % 10000;
			bumber = parseInt(bumber / 10000);
			buffer = base(baseNum) + suff[index] + buffer;
			index++;
		} while (bumber > 0);
		return buffer;
	}
	if (!/^\d{1,12}$/.test(num)) {
		throw new Error("数字转中文错误，非数字或大于999999999999");
	}
	return big(num);
}

/*
功能：下载文件
参数：
	ids		array		要下载的文件的id，
	name	string		文件的下载名，不含后缀（后缀会自动确定），如果不传入此参数则使用默认文件名
	action	string		自定义的文件下载方法路径，如果不传则默认使用BaseController的pDownloadBase方法
*/
function downloadFile(ids, name, action) {
	(typeof ids == "object") || (ids = [ids]);
	name || (name = "%s");
	var form = document.createElement("form");
	form.hidden = true;
	form.method = "post";
	form.action = action ? action : (__APP__ + "/Home/Index/pDownloadBase");
	for (var i in ids) {
		var input = document.createElement("input");
		input.name = "ids[]";
		input.value = ids[i];
		form.appendChild(input);
	}
	var input = document.createElement("input");
	input.type = "text";
	input.name = "name";
	input.value = name;
	form.appendChild(input);
	document.body.appendChild(form);
	form.submit();
	document.body.removeChild(form);
}

/*
功能：删除指定的dom
参数：
	dom		dom		要删除的dom
*/
function removeDom(dom) {
	dom.parentElement.removeChild(dom);
}

/*
功能：删除某个dom下的所有孩子，除了某个，如果but传入null或不传则删除所有
参数：
	dom		dom		需要进行删除孩子操作的dom
*/
function removeChildrenAll(dom, but) {
	var pos = 0;
	while (dom.children.length > pos) {
		if (dom.children[pos] == but) {
			pos++;
			continue;
		}
		dom.removeChild(dom.children[pos]);
	}
}

/*
功能：获取一些日期中的最大日期和最小日期
参数：
	dates		array		日期数组
*/
function getMaxMinDate(dates) {
	if (!dates || dates.length == 0) return null;
	var dateInt = [];
	for (var i in dates) {
		var date = dates[i];
		if (date == null) {
			dateInt.push(null);
			continue;
		}
		//去掉其他字符，保留数字
		
		var str = date.replace(/\s|-|:/gim, "");
		dateInt.push(parseInt(str));
	}
	var maxindex;
	var minindex = maxindex = 0;
	for (var i in dateInt) {
		if (dateInt[i] === null) continue;
		(dateInt[minindex] === null) && (minindex = i);
		(dateInt[maxindex] === null) && (maxindex = i);
		if (dateInt[i] < dateInt[minindex]) {
			minindex = i;
		}
		if (dateInt[i] > dateInt[maxindex]) {
			maxindex = i;
		}
	}
	return { max: dates[maxindex], min: dates[minindex], maxIndex: maxindex, minIndex: minindex };
}

/*
功能：获取窗口滚动到顶部的距离
*/
function getWindowScrollTop() {
	return document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop;
}

/*
功能：设置窗口滚动到顶部的距离
*/
function setWindowScrollTop(pos) {
	document.documentElement.scrollTop = pos;
	document.body.scrollTop = pos;
	window.pageYOffset = pos;
}


/*
功能：加载数据
参数：
	http			k360_http.create()创建的对象
	err				响应onerror事件的错误消息
	onsuccess		成功回调
	onerror			失败回调
	onend			结束回调
*/
function dataLoad(http, err, onsuccess, onerror, onend) {
	http
	.onsuccess(function (data, xhr) {
		onend && onend();
		if (data.reson) {
			onerror && onerror(data.reson);
			return;
		}
		onsuccess && onsuccess(data.data);
	})
	.onerror(function (xhr, status, statusText) {
		console.log(xhr);
		onend && onend();
		onerror && onerror(err + "，可能是网络问题，状态码：" + status);
	})
	.send();
}

/*
功能：为多个dom添加处理事件
参数：
	es		[Dom]		要添加事件的dom
	evt		String		事件名称
	cb		Function	回调函数
*/
function on(es, evt, cb) {
	for (var i = 0; i < es.length ; i++) {
		es[i].addEventListener(evt, cb, false);
	}
}

/*
功能：为多个dom设置样式
参数：
	es		[Dom]		要设置css样式的dom
	ss		Object		css样式
	but		Dom			除了某个dom
*/
function style(es, ss, but) {
	for (var i = 0; i < es.length ; i++) {
		var e = es[i];
		if (e == but) continue;
		for (var j in ss) {
			e.style[j] = ss[j];
		}
	}
}


function anim(dom, x, cb) {
	$(dom).addClass(x + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
		$(this).removeClass(x + ' animated');
		cb && cb();
	});
};

function $$(selector) {
	return document.querySelector(selector);
}

function $$$(selector) {
	document.querySelectorAll(selector);
}

function formSubmitor(form, err, onsuccess) {
	var http = k360_http.create().addForm(form);
	var loading = form.querySelector("button[type='submit']");
	loading.disabled = true;
	dataLoad(http, err, onsuccess, function (e) {
		k360_popover.create().toast(e);
	}, function () {
		loading.disabled = false;
	});
}


//window.location.hash = "no-back-button";
//window.location.hash = "Again-No-back-button";//again because google chrome don't insert first hash into history
//window.onhashchange = function () { window.location.hash = "no-back-button"; }


