/*
===============================================================================
标签及说明：
	一、css标签
		属性：
			root		必须		css标签中所有css的公用目录
			suff		可选		是否加上.css后缀，默认会加后缀，如果设置为false则不加后缀
			base		可选		是否仅仅创建一个link标签，默认否
		示例：
			<css root="./style">["mycss"]</css>
			<css root="./style" suff="true">["mycss"]</css>
			<css root="./style" suff="false">["mycss.css"]</css>
	
	二、js标签
		属性：同css标签





===============================================================================
*/

/*
功能：自动获取当前路径
*/
(function () {
	var path = window.location.href;
	var index = path.indexOf("index.php");
	index = (index == -1) ? path.length : index;
	window.__ROOT__ = path.substring(0, index);
	if (window.__ROOT__.charAt(window.__ROOT__.length - 1) == "/") {
		window.__ROOT__ = window.__ROOT__.substring(0, window.__ROOT__.length - 1);
	}
	window.__APP__ = __ROOT__ + "/index.php";
	window.__PUBLIC__ = __ROOT__ + "/Public";
})();


(function () {
	/*功能：添加事件处理*/
	window.cb_on = function (obj, type, fn) {
		if (obj.attachEvent) {
			obj['e' + type + fn] = fn;
			obj[type + fn] = function () { obj['e' + type + fn](window.event); }
			obj.attachEvent('on' + type, obj[type + fn]);
		} else
			obj.addEventListener(type, fn, false);
	}

	/*功能：移除事件处理*/
	window.cb_off = function (obj, type, fn) {
		if (obj.detachEvent) {
			obj.detachEvent('on' + type, obj[type + fn]);
			obj[type + fn] = null;
		} else
			obj.removeEventListener(type, fn, false);
	}

})();



(function () {

	//处理CSS标签
	function cssTag() {

		//初始化基本样式
		//cssDom("css{display:none}");

		//找到所有css标签
		var cssTags = document.getElementsByTagName("css");
		for (var i = 0; i < cssTags.length; i++) {
			var tag = cssTags.item(i);
			handleCssTag(tag);
		}

		//往head中添加css
		function cssDom(html) {
			var dom = document.createElement("style");
			dom.innerHTML = html;
			document.head.appendChild(dom);
		}

		//处理一个CSS标签
		function handleCssTag(tag) {
			var csses = JSON.parse(tag.innerHTML);
			var root = tag.getAttribute("root");
			var suff = tag.getAttribute("suff");
			var base = tag.getAttribute("base");
			if (root.charAt(root.length - 1) != "/") {
				root += "/";
			}
			for (var i in csses) {
				var url = root + csses[i];// + ".css";
				if (suff != "false") url += ".css";
				if (base == "true") {
					var dom = document.createElement("link");
					dom.href = url;
					dom.rel = "stylesheet";
					document.head.appendChild(dom);
				} else {
					ajax(url, function (data) {
						var cssHtml = data.replace(/__PUBLIC__/gim, __ROOT__ + "/Public").replace(/__ROOT__/gim, __ROOT__);
						cssDom(cssHtml);
					});
				}
			}
			tag.parentElement.removeChild(tag);
		}
	}


	//处理JS标签
	function jsTag() {
		//获取所有的js标签
		var jsDoms = document.getElementsByTagName("js");
		for (var i = 0; i < jsDoms.length; i++) {
			var tag = jsDoms.item(i);
			handleJsTag(tag);
		}
		//处理一个js标签
		function handleJsTag(tag) {
			var jses = JSON.parse(tag.innerHTML);
			var root = tag.getAttribute("root");
			var suff = tag.getAttribute("suff");
			if (root.charAt(root.length - 1) != "/") {
				root += "/";
			}
			for (var i in jses) {
				//生成script标签
				var url = root + jses[i];
				if (suff != "false") url += ".js";
				var dom = document.createElement("script");
				dom.src = url;
				dom.type = "text/javascript";
				document.head.appendChild(dom);
			}
			tag.parentElement.removeChild(tag);
		}
	}


	//ajax处理
	function ajax(url, onsuccess) {
		//创建 - 非IE6 - 第一步
		if (window.XMLHttpRequest) {
			var xhr = new XMLHttpRequest();
		} else { //IE6及其以下版本浏览器
			var xhr = new ActiveXObject('Microsoft.XMLHTTP');
		}
		//接收 - 第三步
		xhr.onreadystatechange = function () {
			if (xhr.readyState == 4) {
				var status = xhr.status;
				if (status >= 200 && status < 300) {
					onsuccess(xhr.responseText);
				} else {
					console.error("css加载错误，状态码：" + status + "，XHR：\r\n", xhr);
				}
			}
		}
		//连接 和 发送 - 第二步
		//			if (options.type == "GET") {
		xhr.open("GET", url, true);
		xhr.send(null);
		//			}
	}

	//调用各个函数
	function main() {
		cssTag();
		jsTag();
	}
	main();
	setInterval(main, 1000);

})();
