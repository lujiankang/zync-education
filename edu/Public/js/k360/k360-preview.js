window.k360_preview = {

	TYPE_DOC: 0x0001,
	TYPE_XLS: 0x0002,
	TYPE_PPT: 0x0003,
	TYPE_TXT: 0x0004,
	TYPE_IMG: 0x0005,

	/*
	功能：ajax操作，这个函数不要调用
	参数：
		baseurl			转换器的url
		path			文档路径
		page			页数
		onsuccess		成功回调
		onerror			失败回调
	*/
	ajax: function (baseurl, path, page, onsuccess, onerror) {
		//创建ajax
		var xhr = null;
		if (window.XMLHttpRequest) {
			xhr = new XMLHttpRequest();
		} else {
			xhr = new ActiveXObject("Microsoft.XMLHTTP");
		}
		//监听
		xhr.onreadystatechange = function () {
			if (xhr.readyState == 4) {
				if (xhr.status == 200) {
					var data = JSON.parse(xhr.responseText);
					onsuccess(data);
				} else {
					onerror(xhr, xhr.status, xhr.statusText);
				}
			}
		}
		//发送
		var postURL = baseurl + "?path=" + encodeURI(path) + "&page=" + page;
		xhr.open("POST", postURL, true);
		xhr.send();
	},

	create: function () {
		//后缀->文档类型 字典
		var suff_dic = {
			"doc": k360_preview.TYPE_DOC,
			"docx": k360_preview.TYPE_DOC,
			"xls": k360_preview.TYPE_XLS,
			"xlsx": k360_preview.TYPE_XLS,
			"ppt": k360_preview.TYPE_PPT,
			"pptx": k360_preview.TYPE_PPT,
			"txt": k360_preview.TYPE_TXT,
			"jpg": k360_preview.TYPE_IMG,
			"bmp": k360_preview.TYPE_IMG,
			"png": k360_preview.TYPE_IMG,
			"jpeg": k360_preview.TYPE_IMG,
			"gif": k360_preview.TYPE_IMG
		};
		//预览对象
		var obj = {
			conf: {
				parser: "",			//文档转换器的服务器地址，如：http://localhost/parser.php
				url: "",			//要转换的文档的地址
				type: 0,			//文档类型
				dom: null,			//主dom
				contentdom: null,	//最内层文档显示区域dom
				loadingdom: null,	//加载中dom
				pptlastdom: null,	//ppt上一页dom
				pptnextdom: null,	//ppt下一页dom
				xlstopdom: null,	//xls顶部列表dom
				tipdom: null		//提示dom
			},
			status: {
				isloading: false,	//是否正在加载
				pptcount: 0,		//ppt数量
				curppt: 0,			//当前的ppt位置
				curxls: 0			//当前的xls位置
			},
			res: {
				tiptimer: null
			}
		};

		/*
		功能：设置文档的url地址，取决于服务器端的写法，详见说明
		参数：
			url		string		文档的url地址
		说明：
			文档地址的写法取决于服务器端的操作，例如：
			有一文档：http://localhost/test.docx
			如果服务器端写成：$docUrl = "http://localhost/" . $_POST["path"]，则这里的url参数需要传入"test.docx"
			如果服务器端写成：$docUrl = "http://localhost" . $_POST["path"]，则这里的url参数需要传入"/test.docx"
			如果服务器端写成：$docUrl = $_POST["path"]，则这里需要传入"http://localhost/test.docx"
			以此类推
		*/
		obj.setUrl = function (url) {
			obj.conf.url = url;
			//设置默认后缀
			if (obj.conf.type == 0) {
				var sf = suff(url);
				if (suff_dic[sf]) {
					obj.conf.type = suff_dic[sf];
				}
			}
			return obj;
		}

		/*
		功能：设置文档类型，如果文档的url带有后缀，则文档类型可以在setUrl函数中自动识别，此时这个函数可以不必调用
		参数：
			type		int		文档类型
		说明：
			type的取值有：
				TYPE_DOC			word文档
				TYPE_XLS			excel文档
				TYPE_PPT			powerpoint文档
				TYPE_TXT			文本文档
				TYPE_IMG			图片文档
		*/
		obj.setType = function (type) {
			if (type <= 0 || type > 0x0005)
				return obj;
			obj.conf.type = type;
			return obj;
		}

		/*
		功能：设置转换器的url地址，如果是图片预览，则不必设置
		参数：
			url		string		转换器的url地址
		*/
		obj.setParser = function (url) {
			obj.conf.parser = url;
			return obj;
		}

		/*
		功能：显示预览
		*/
		obj.show = function () {
			switch (obj.conf.type) {
				case k360_preview.TYPE_DOC:
				case k360_preview.TYPE_TXT:
					showDOC("");
					docLoad();
					break;
				case k360_preview.TYPE_IMG:
					showImg("");
					imgLoad();
					break;
				case k360_preview.TYPE_PPT:
					showPPT("");
					pptGoto();
					break;
				case k360_preview.TYPE_XLS:
					showXLS("");
					xlsGoto();
					break;
				default:
					break;
			}
			fullScreen(obj.conf.dom);
			return obj;
		}

		//图片加载
		function imgLoad() {
			obj.conf.contentdom.src = obj.conf.url;
			obj.conf.contentdom.onload = function () {
				setunloading();
			}
		}

		//文档加载
		function docLoad() {
			if (obj.status.isloading) return;
			setloading();
			k360_preview.ajax(obj.conf.parser, obj.conf.url, 0, function (data) {
				setunloading();
				if (data.reson) {
					showtip(data.reson);
					return;
				}
				showDOC(data.data.html);
			}, function (xhr, status, statusText) {
				setunloading();
				showtip(status + "，" + statusText);
			});
		}

		//ppt跳到上一页
		function pptGotoLast() {
			if (obj.status.pptcount == 0)
				return;
			if (obj.status.curppt <= 0) {
				showtip("已经是第一张了");
				return;
			}
			pptGoto(obj.status.curppt - 1);
		}

		//ppt跳到下一页
		function pptGotoNext() {
			if (obj.status.pptcount == 0)
				return;
			if (obj.status.curppt >= obj.status.pptcount - 1) {
				showtip("已经是最后一张了");
				return;
			}
			pptGoto(obj.status.curppt + 1);
		}

		//ppt跳到指定页
		function pptGoto(page) {
			if (obj.status.isloading) return;
			(page == undefined) && (page = 0);
			setloading();
			k360_preview.ajax(obj.conf.parser, obj.conf.url, page, function (data) {
				setunloading();
				if (data.reson) {
					showtip(data.reson);
					return;
				}
				showPPT(data.data.html);
				obj.status.pptcount = parseInt(data.data.count);
				obj.status.curppt = page;
			}, function (xhr, status, statusText) {
				setunloading();
				showtip(status + "，" + statusText);
			});
		}

		//xls跳到指定页
		function xlsGoto(page) {
			if (obj.status.isloading) return;
			(page == undefined) && (page = 0);
			setloading();
			k360_preview.ajax(obj.conf.parser, obj.conf.url, page, function (data) {
				setunloading();
				if (data.reson) {
					showtip(data.reson);
					return;
				}
				obj.status.curxls = page;
				showXLS(data.data.html);
				xlsSheetsDom(data.data.sheets)
			}, function (xhr, status, statusText) {
				setunloading();
				showtip(status + "，" + statusText);
			});
		}

		//设置xls顶部dom数据，也就是显示出xls文档中的所有表格名称
		function xlsSheetsDom(sheets) {
			//清空原有内容
			obj.conf.xlstopdom.innerHTML = "<div class='sheet-text'>所有表格：</div>";
			//逐个添加
			for (var i = 0; i < sheets.length; i++) {
				//创建
				var dom = top.document.createElement("div");
				dom.setAttribute("class", "sheet-item");
				dom.setAttribute("sheet-index", i);
				//判断是不是当前的dom，如果是当前的dom则高亮
				if (i == obj.status.curxls) {
					dom.setAttribute("checked", "");
				}
				dom.innerHTML = sheets[i];
				obj.conf.xlstopdom.appendChild(dom);
				//事件处理
				dom.onclick = function () {
					var index = parseInt(this.getAttribute("sheet-index"));
					if (obj.status.curxls == index)
						return;
					xlsGoto(index);
				}
			}
		}

		//显示doc文档
		function showDOC(content) {
			if (!obj.conf.dom) {
				//主dom
				var dom = top.document.createElement("div");
				obj.conf.dom = dom;
				dom.setAttribute("class", "k360-preview");
				//loading dom
				var loadingdom = top.document.createElement("div");
				obj.conf.loadingdom = loadingdom;
				loadingdom.setAttribute("class", "inner-loading");
				dom.appendChild(loadingdom);
				//内 dom
				var docdom = top.document.createElement("div");
				obj.conf.contentdom = docdom;
				docdom.setAttribute("class", "inner-doc");
				dom.appendChild(docdom);
				//关闭dom
				var closedom = top.document.createElement("div");
				closedom.setAttribute("class", "inner-close");
				dom.appendChild(closedom);
				//tip dom
				var tipdom = top.document.createElement("div");
				obj.conf.tipdom = tipdom;
				tipdom.setAttribute("class", "inner-tip");
				dom.appendChild(tipdom);
				//添加到body
				top.document.body.appendChild(dom);
				//事件
				closedom.onclick = function () {
					removeDom(obj.conf.dom);
					obj = undefined;
				}
			}
			obj.conf.contentdom.innerHTML = content;
		}

		//显示ppt文档
		function showPPT(content) {
			if (!obj.conf.dom) {
				//主dom
				var dom = top.document.createElement("div");
				obj.conf.dom = dom;
				dom.setAttribute("class", "k360-preview");
				//ppt dom
				var pptdom = top.document.createElement("div");
				pptdom.setAttribute("class", "inner-ppt");
				dom.appendChild(pptdom);
				//last next dom
				var pptlastdom = top.document.createElement("div");
				var pptnextdom = top.document.createElement("div");
				obj.conf.pptlastdom = pptlastdom;
				obj.conf.pptnextdom = pptnextdom;
				pptlastdom.setAttribute("class", "goto-last");
				pptnextdom.setAttribute("class", "goto-next");
				dom.appendChild(pptlastdom);
				dom.appendChild(pptnextdom);
				//loading dom
				var loadingdom = top.document.createElement("div");
				obj.conf.loadingdom = loadingdom;
				loadingdom.setAttribute("class", "inner-loading");
				dom.appendChild(loadingdom);
				//内dom
				var innerdom = top.document.createElement("div");
				obj.conf.contentdom = innerdom;
				innerdom.setAttribute("class", "ppt-content");
				pptdom.appendChild(innerdom);
				//关闭dom
				var closedom = top.document.createElement("div");
				closedom.setAttribute("class", "inner-close");
				dom.appendChild(closedom);
				//tip dom
				var tipdom = top.document.createElement("div");
				obj.conf.tipdom = tipdom;
				tipdom.setAttribute("class", "inner-tip");
				dom.appendChild(tipdom);
				//添加到body
				top.document.body.appendChild(dom);
				//事件处理
				pptnextdom.onclick = pptGotoNext;
				pptlastdom.onclick = pptGotoLast;
				closedom.onclick = function () {
					removeDom(obj.conf.dom);
					obj = undefined;
				}
			}
			obj.conf.contentdom.innerHTML = content;
			//解析ppt元素
			k360_preview.pptparser.parseAll()
		}

		//显示xls文档
		function showXLS(content) {
			if (!obj.conf.dom) {
				//主dom
				var dom = top.document.createElement("div");
				obj.conf.dom = dom;
				dom.setAttribute("class", "k360-preview");
				//top dom
				var topdom = top.document.createElement("div");
				obj.conf.xlstopdom = topdom;
				topdom.setAttribute("class", "inner-xls-top");
				dom.appendChild(topdom);
				//xls dom
				var xlsdom = top.document.createElement("div");
				obj.conf.contentdom = xlsdom;
				xlsdom.setAttribute("class", "inner-xls");
				dom.appendChild(xlsdom);
				//loading dom
				var loadingdom = top.document.createElement("div");
				obj.conf.loadingdom = loadingdom;
				loadingdom.setAttribute("class", "inner-loading");
				dom.appendChild(loadingdom);
				//关闭dom
				var closedom = top.document.createElement("div");
				closedom.setAttribute("class", "inner-close");
				dom.appendChild(closedom);
				//tip dom
				var tipdom = top.document.createElement("div");
				obj.conf.tipdom = tipdom;
				tipdom.setAttribute("class", "inner-tip");
				dom.appendChild(tipdom);
				//添加到body
				top.document.body.appendChild(dom);
				//事件
				closedom.onclick = function () {
					removeDom(obj.conf.dom);
					obj = undefined;
				}
			}
			obj.conf.contentdom.innerHTML = content;
		}

		//显示图片
		function showImg(src) {
			if (!obj.conf.dom) {
				//主dom
				var dom = top.document.createElement("div");
				obj.conf.dom = dom;
				dom.setAttribute("class", "k360-preview");
				//img dom
				var imgdom = top.document.createElement("div");
				imgdom.setAttribute("class", "inner-img");
				dom.appendChild(imgdom);
				//loading dom
				var loadingdom = top.document.createElement("div");
				obj.conf.loadingdom = loadingdom;
				loadingdom.setAttribute("class", "inner-loading");
				dom.appendChild(loadingdom);
				//关闭dom
				var closedom = top.document.createElement("div");
				closedom.setAttribute("class", "inner-close");
				dom.appendChild(closedom);
				//tip dom
				var tipdom = top.document.createElement("div");
				obj.conf.tipdom = tipdom;
				tipdom.setAttribute("class", "inner-tip");
				dom.appendChild(tipdom);
				//内dom
				var innerdom = top.document.createElement("div");
				innerdom.setAttribute("class", "img-content");
				imgdom.appendChild(innerdom);
				//图片dom
				var imagedom = top.document.createElement("img");
				obj.conf.contentdom = imagedom;
				innerdom.appendChild(imagedom);
				//添加到body
				top.document.body.appendChild(dom);
				//事件
				innerdom.ondrag = function () { return false; };
				closedom.onclick = function () {
					removeDom(obj.conf.dom);
					obj = undefined;
				}
			}
			obj.conf.contentdom.src = src;
		}

		//显示loading
		function setloading() {
			obj.conf.loadingdom.style.display = "inline-block";
			obj.status.isloading = true;
		}

		//删除loading
		function setunloading() {
			obj.conf.loadingdom.style.display = "none";
			obj.status.isloading = false;
		}

		//全屏
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

		//为dom设置css
		function setCSS(dom, css) {
			for (var name in css) {
				dom.style[name] = css[name];
			}
		}

		//获取文件名后缀
		function suff(fname) {
			var index = fname.lastIndexOf(".");
			if (index == -1)
				return "";
			return fname.substring(index + 1);
		}

		//删除一个dom
		function removeDom(dom) {
			dom.innerHTML = "";
			if (dom.remove) {
				dom.remove();
				dom = null;
			}
			if (dom.removeNode) {
				dom.removeNode();
				dom = null;
			}
		}

		//显示提示
		function showtip(text) {
			obj.conf.tipdom.innerHTML = text;
			setCSS(obj.conf.tipdom, {
				borderStyle: "solid",
				minWidth: "200px",
				maxWidth: "600px",
				padding: "0px 20px"
			});
			if (!obj.res.tiptimer) {
				clearTimeout(obj.res.tiptimer);
			}
			obj.res.tiptimer = setTimeout(function () {
				clearTimeout(obj.res.tiptimer);
				obj.res.tiptimer = null;
				setCSS(obj.conf.tipdom, {
					borderStyle: "none",
					minWidth: "0px",
					maxWidth: "0px",
					padding: "0px 0px"
				});
			}, 5000);
		}

		return obj;
	},

	//ppt元素解析器
	pptparser: {
		//解析所有元素
		parseAll: function () {
			for (var func in k360_preview.pptparser) {
				if (func == "parseAll") continue;
				k360_preview.pptparser[func].parse();
			}
		},
		//横向箭头
		arrow: {
			//解析横向箭头
			parse: function () {
				var arrows = top.document.getElementsByClassName("ppt-arrow");
				for (var i = 0; i < arrows.length; i++) {
					var arrow = arrows.item(i);
					var w = parseInt(arrow.style.width);
					var h = parseInt(arrow.style.height);
					parseArrow(arrow, w, h);
				}
				//解析一个箭头
				function parseArrow(arrow, width, height) {
					var ltWidth = width - height / 2;
					var ltHeight = height / 3.991;
					if (ltWidth < width / 2) {
						ltWidth = width / 2;
					}
					var color = arrow.children[0].style.backgroundColor;
					var content = arrow.children[0].innerHTML;
					var vAlign = arrow.children[0].style.verticalAlign;
					var rectHtml = getRectHtml(ltHeight, ltWidth, height - ltHeight * 2, color);
					var triangleHtml = getTriangleHtml(ltWidth, width - ltWidth, height, color);
					var baseHtml = getMainHtml(ltHeight, width, height, vAlign, content);
					arrow.innerHTML = rectHtml + triangleHtml + baseHtml;
				}
				//获取箭头矩形部分的html
				function getRectHtml(top, width, height, background) {
					return "<div style=\"position:absolute; width:" + (width + 1) + "pt; height:" + height + "pt; left:0pt; top:" + top + "pt; background-color:" + background + "\"></div>";
				}
				//获取箭头三角形部分的html
				function getTriangleHtml(left, width, height, background) {
					return "<div style=\"position:absolute; left:" + left + "pt; top:0pt; width:0pt; height:0pt; border-left:solid " + width + "pt " + background + "; border-top:solid " + (height / 2) + "pt transparent; border-bottom:solid " + (height / 2) + "pt transparent\"></div>"
				}
				//获取主体html
				function getMainHtml(top, width, height, valign, baseHtml) {
					return "<div style=\"position:absolute; left:0pt; top:" + top + "pt; right:0pt; bottom:" + top + "pt\">\
                    <div style=\"width:" + (width - top / 2) + "pt; height:" + (height - top * 2) + "pt; display:table-cell; vertical-align:" + valign + "\">\
                        " + baseHtml + "\
                    </div>\
                </div>";
				}
			}
		},
		//纵向箭头
		downarraw: {
			//开始解析
			parse: function () {
				var arrows = top.document.getElementsByClassName("ppt-downarrow");
				for (var i = 0; i < arrows.length; i++) {
					var arrow = arrows.item(i);
					var w = parseInt(arrow.style.width);
					var h = parseInt(arrow.style.height);
					parseArrow(arrow, w, h);
				}
				//解析一个纵向箭头
				function parseArrow(arrow, width, height) {
					var ltWidth = width / 3.991;
					var ltHeight = height - width / 2;
					if (ltHeight < height / 2) {
						ltHeight = height / 2;
					}
					var color = arrow.children[0].style.backgroundColor;
					var content = arrow.children[0].innerHTML;
					var vAlign = arrow.children[0].style.verticalAlign;
					var rectHtml = getRectHtml(ltWidth, width - ltWidth * 2, ltHeight, color);
					var triangleHtml = getTriangleHtml(ltHeight, width, height - ltHeight, color);
					var baseHtml = getMainHtml(ltWidth, width - ltWidth * 2, height, vAlign, content);
					arrow.innerHTML = rectHtml + triangleHtml + baseHtml;
				}
				//获取箭头矩形区域html
				function getRectHtml(left, width, height, background) {
					return "<div style=\"position:absolute; width:" + width + "pt; height:" + (height + 1) + "pt; left:" + left + "pt; top:0pt; background-color:" + background + "\"></div>";
				}
				//获取箭头三角形区域html
				function getTriangleHtml(top, width, height, background) {
					return "<div style=\"position:absolute; left:0pt; top:" + top + "pt; width:0pt; height:0pt; border-top:solid " + height + "pt " + background + "; border-left:solid " + (width / 2) + "pt transparent; border-right:solid " + (width / 2) + "pt transparent\"></div>"
				}
				//获取主体html
				function getMainHtml(left, width, height, valign, baseHtml) {
					return "<div style=\"position:absolute; left:" + left + "pt; top:0pt; right:" + left + "pt; bottom:0pt; padding:10px 10px 30px 10px\">\
                    <div style=\"width:" + width + "pt; height:" + (height - left / 2) + "pt; display:table-cell; vertical-align:" + valign + "\">\
                        " + baseHtml + "\
                    </div>\
                </div>";
				}
			}
		}
		//----
		//添加其他元素的解析代码
		//---
	}

}

//检测文档是否加载完成
var k360_preview_timer = setInterval(function () {
	if (document.readyState == "complete") {
		clearInterval(k360_preview_timer);
		k360_preview_on_ready();
	}
}, 100);

//文档加载完成回调
function k360_preview_on_ready() {
	if (top.document.querySelectorAll("#k360-preview").length > 0)
		return;
	var style = top.document.createElement("style");
	style.id = "k360-preview";
	top.document.head.appendChild(style);
	style.innerHTML = "\
	.k360-preview{\
		position:fixed;\
		left:0px;\
		top:0px;\
		right:0px;\
		bottom:0px;\
		z-index:999;\
		background:#EFEFEF;\
		overflow:auto;\
	}\
	.k360-preview >.inner-loading{\
		z-index:1;\
		position:fixed;\
		left:0px;\
		top:0px;\
		right:0px;\
		bottom:0px;\
		background-image:url(data:image/gif;base64,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);\
		background-repeat:no-repeat;\
		background-position:center;\
	}\
	.k360-preview >.inner-doc{\
		width:900px;\
		min-height:100%;\
		background:#FFF;\
		margin:auto;\
		padding:150px;\
	}\
	.k360-preview >.inner-ppt{\
		display:table;\
		width:100%;\
		height:100%;\
	}\
	.k360-preview >.goto-next{\
		right:20px;\
		top:45%;\
		width:40px;\
		height:40px;\
		position:absolute;\
		border-radius:20px;\
		background-image:url(data:image/png;base64,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);\
		cursor:pointer;\
	}\
	.k360-preview >.goto-last{\
		left:20px;\
		top:45%;\
		width:40px;\
		height:40px;\
		position:absolute;\
		border-radius:20px;\
		background-image:url(data:image/png;base64,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);\
		cursor:pointer;\
	}\
	.k360-preview >.goto-next:hover{\
		background-color:rgba(149, 165, 165, .5)\
	}\
	.k360-preview >.goto-last:hover{\
		background-color:rgba(149, 165, 165, .5)\
	}\
	.k360-preview >.inner-ppt >.ppt-content{\
		display:table-cell;\
		vertical-align:middle;\
	}\
	.k360-preview >.inner-ppt >.ppt-content >div{\
		margin:auto;\
	}\
	.k360-preview >.inner-img{\
		display:table;\
		width:100%;\
		height:100%;\
		text-align:center;\
	}\
	.k360-preview >.inner-img >.img-content{\
		display:table-cell;\
		vertical-align:middle;\
	}\
	.k360-preview >.inner-xls-top{\
		position:absolute;\
		left:0px;\
		top:0px;\
		right:0px;\
		height:50px;\
		line-height:50px;\
		overflow:hidden;\
		border-bottom:solid 1px #808080;\
		background:#FFF;\
	}\
	.k360-preview >.inner-xls-top >.sheet-item{\
		margin:0px 10px;\
		color:#297FB8;\
		display:inline-block;\
		cursor:pointer;\
		font-family:微软雅黑;\
		font-size:11pt;\
	}\
	.k360-preview >.inner-xls-top >.sheet-item:hover{\
		color:#C1392B;\
	}\
	.k360-preview >.inner-xls-top >.sheet-item[checked]{\
		text-decoration:underline;\
		color:#C1392B;\
		cursor:default;\
	}\
	.k360-preview >.inner-xls-top >.sheet-text{\
		display:inline-block;\
		margin:0px 10px;\
		font-family:微软雅黑;\
		font-size:11pt;\
	}\
	.k360-preview >.inner-xls{\
		position:absolute;\
		left:0px;\
		top:51px;\
		right:0px;\
		bottom:0px;\
		overflow:auto;\
		background:#FFF;\
		padding-top:20px;\
		padding:20px;\
	}\
	.k360-preview table tr >td{\
		border:solid 1px #808080;\
		border-left:none;\
		border-top:none;\
		line-height:25px;\
	}\
	.k360-preview table tr >td:first-child{\
		border-left:solid 1px #808080;\
	}\
	.k360-preview table tr:first-child >td{\
		border-top:solid 1px #808080;\
	}\
	.inner-close{\
		position:fixed;\
		right:30px;\
		top:10px;\
		width:30px;\
		height:30px;\
		border-radius:15px;\
		cursor:pointer;\
		background-color:#E84C3D;\
		background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAB9SURBVHja7NVLDoBACANQIN7/ynXj0l8pOpuSuJhI5gVTYgKIFVWxqAwbNvw7jOMZ62UnhviehvPF5bjolye+wym086nPcBrtpjqVSdV1yofzZzC6aVZgkGkf3+NU8BqYtIXXANrCS0xzdNO9DYF0r//Hhg2P1Q4AAP//AwAtYRo5HR7WPwAAAABJRU5ErkJggg==);\
		z-index:999;\
	}\
	.inner-close:hover{\
		background-color:#D25400;\
	}\
	.inner-tip{\
		position:fixed;\
		bottom:10px;\
		left:20px;\
		height:40px;\
		min-width:0px;\
		background:#E84C3D;\
		border:solid 1px #C1392B;\
		border-style:none;\
		color:#FFF;\
		line-height:40px;\
		font-family:微软雅黑;\
		font-size:10pt;\
		padding:0px 0px;\
		transition:all 300ms;\
	}\
	";
}