window.kEditorC = {
	create: function (container, editor, textarea) {
		var obj = {};
		var doc = null;

		obj.setFontFamily = function (fontFamily) {
			editor.focus();
			doc.execCommand("FontName", false, fontFamily);
		}

		obj.setFontSize = function (fontSize) {
			editor.focus();
			doc.execCommand("FontSize", false, fontSize);
			var fontDom = doc.querySelector("font[size]");
			if (fontDom) {
				fontDom.removeAttribute("size");
				fontDom.style.fontSize = fontSize + "pt";
			}
		}

		obj.setBackgroundColor = function (color) {
			editor.focus();
			doc.execCommand("BackColor", false, color);
		}

		obj.setForeColor = function (color) {
			editor.focus();
			doc.execCommand("ForeColor", false, color);
		}

		obj.switchBold = function () {
			editor.focus();
			doc.execCommand("Bold", false, null);
		}

		obj.switchItalic = function () {
			editor.focus();
			doc.execCommand("Italic", false, null);
		}

		obj.switchUnderline = function () {
			editor.focus();
			doc.execCommand("Underline", false, null);
		}

		obj.setAlignment = function (alignment) {
			editor.focus();
			var dict = { left: "JustifyLeft", right: "JustifyRight", center: "JustifyCenter" };
			doc.execCommand(dict[alignment], false, null);
		}

		obj.insertTable = function (rows, cols) {
			var tableInnerHTML = "";
			for (var i = 0; i < rows; i++) {
				tableInnerHTML += "<tr height=\"25\">";
				for (var j = 0; j < cols; j++) {
					tableInnerHTML += "<td style=\"border:solid 1px #000; border-left:none; border-top:none\"></td>";
				}
				tableInnerHTML += "</tr>";
			}
			obj.insertHTML("<table cellspacing=\"0\" width=\"100%\" style=\"border:solid 1px #000; border-right:none; border-bottom:none\">" + tableInnerHTML + "</table><br />")
		}

		obj.insertList = function (isOrderList, type) {
			var listname = isOrderList ? "ol type=\"" + type + "\"" : "ul";
			var html = "<" + listname + "><li></li></" + listname + "><br />"
			obj.insertHTML(html);
		}

		obj.insertHTML = function (html) {
			//如果能直接插入则直接插入
			if (doc.execCommand('InsertHtml', false, html)) return;
			//否则利用其他方式插入
			if (doc.selection && doc.selection.createRange) {
				var myRange = doc.selection.createRange();
				var m = myRange.pasteHTML(html);
			}
			else if (editor.getSelection) {
				var selection = editor.getSelection();
				var range = editor.getSelection().getRangeAt(0);
				range.deleteContents();
				var newP = doc.createElement("span");
				newP.innerHTML = html;
				range.insertNode(newP);
			}
			//document.selection.createRange().pasteHTML(html);
		}

		//初始化
		obj.init = function () {
			doc = editor.document ? editor.document : editor.contentDocument;
			//开启设计模式
			doc.designMode = "ON";
			//启用某些设置
			doc.execCommand('2D-position', false, true);
			doc.execCommand('enableInlineTableEditing', false, true);
			doc.execCommand('enableObjectResizing', false, true);

			//doc.body.contentEditable = true;
			//doc.body.innerHTML = "<div style='width:100%; min-height:10px'></div>"
			//填写CSS
			var styleDom = document.createElement("style");
			styleDom.innerHTML = "p{line-height:20px}";
			doc.head.appendChild(styleDom);
			doc.body.style.margin = "0px";

			//事件
			document.onmousedown = function () {
				textarea.value = container.value = doc.body.innerHTML;
			}
			return obj;
		}
		
		return obj;
	}
}



window.kEditorCtrl = {
	create: function (editor) {
		try{
			var obj = {};

			obj.ShowAndSetPos = function (dom) {
				var widget = document.querySelector(".ke-widget");
				var rect = dom.getBoundingClientRect();
				var x = rect.right;
				//var y = rect.top;
				if (rect.right + 220 > window.innerWidth) {
					x = rect.left - 230;
				}
				setTimeout(function () {
					widget.style.display = "inline-block";
					widget.style.left = x + 2 + "px";
				}, 100);
				widget.style.display = "inline-block";
				widget.style.left = x + 2 + "px";
				//widget.style.top = y + "px";
				//widget.style.top = y + "px";
			}

			obj.init = function () {
				try {
					//避免ie失去焦点
					var handleDoms = document.querySelectorAll(".ke-widget >div >div:not(.ke-table)");
					for (var i = 0; i < handleDoms.length; i++) {
						handleDoms.item(i).onmousedown = function (e) {
							e.stopPropagation();
							return false;
						}
					}

					//字体
					document.querySelector("[kectrl='FontFamily']").onchange = function () {
						editor.setFontFamily(this.value);
					}
					//字体大小
					document.querySelector("[kectrl='FontSize']").onchange = function () {
						editor.setFontSize(this.value);
					}
					//对其方式
					var alignBtns = document.querySelectorAll(".ke-btn.ke-btn-align");
					for (var i = 0; i < alignBtns.length; i++) {
						alignBtns.item(i).onclick = function (e) {
							editor.setAlignment(this.getAttribute("value"));
							e.stopPropagation();
							return false;
						}
					}
					//字体样式
					var fontStyleBtns = document.querySelectorAll(".ke-btn.ke-btn-fontstyle");
					for (var i = 0; i < fontStyleBtns.length; i++) {
						fontStyleBtns.item(i).onclick = function (e) {
							var fontStyleDict = { bold: "switchBold", italic: "switchItalic", underline: "switchUnderline" };
							var styleName = this.getAttribute("value");
							editor[fontStyleDict[styleName]]();
							e.stopPropagation();
							return false;
						}
					}
					//背景色
					document.querySelector(".ke-select-backcolor").onchange = function (e) {
						editor.setBackgroundColor(this.value);
						e.stopPropagation();
						return false;
					}
					//前景色
					document.querySelector(".ke-select-forecolor").onchange = function (e) {
						editor.setForeColor(this.value);
						e.stopPropagation();
						return false;
					}
					//表格
					var tableOptionDom = document.querySelector(".ke-select-option-table");
					if (tableOptionDom) {
						tableOptionDom.querySelector(".ke-option-btn-add").onclick = function (e) {
							var rowDom = tableOptionDom.querySelector("[ke-name='rows']");
							var colDom = tableOptionDom.querySelector("[ke-name='cols']");
							var rownum = rowDom.value;
							var colnum = colDom.value;
							rowDom.value = "";
							colDom.value = "";
							rownum = parseInt(rownum);
							colnum = parseInt(colnum);
							if (rownum > 0 && colnum > 0) {
								editor.insertTable(rownum, colnum);
								tableOptionDom.style.display = "none";
							}
						}
					}
					//列表
					document.querySelector(".ke-select-list").onchange = function () {
						var liArr = this.value.split(" ");
						var isOl = (liArr[0] == "ol") ? true : false;
						var type = liArr[1];
						editor.insertList(isOl, type);
					}
				} catch (err) {
					console.error(err);
				}
				return obj;
			}

		} catch (er) {
			console.error(err);
		}
		return obj;
	}
}


/*
类说明：
	此类为下拉框类，用于处理class="ke-select"的div
*/
window.elemSelect = {
	create: function () {
		var obj = {};

		obj.init = function () {
			var sels = document.querySelectorAll(".ke-select");
			for (var i = 0; i < sels.length; i++) {
				handleOne(sels[i], sels);
			}
		}

		//处理每一个下拉框
		function handleOne(sel, sels) {
			//获取某些重要dom
			var optionDom = sel.querySelector(".ke-select-option");
			var textDom = sel.querySelector(".ke-select-text");
			//设置默认option隐藏
			optionDom.style.display = "none";
			//select点击的时候显示option
			sel.onclick = function (e) {
				//隐藏其他选项
				for (var i = 0; i < sels.length; i++) {
					if (sels.item(i) == sel) continue;
					sels.item(i).querySelector(".ke-select-option").style.display = "none";
				}
				var selRect = this.getBoundingClientRect();
				//显示
				optionDom.style.display = (optionDom.style.display == "none") ? "inline-block" : "none";
				//计算位置
				var oRect = { x: -200, y: 0, r: 0, b: 0 };
				oRect.y = selRect.top + this.clientHeight + 1;
				oRect.x = selRect.left;
				oRect.w = selRect.width - 8;
				//超过窗口
				if (oRect.y + optionDom.clientHeight > window.innerHeight)
					oRect.y = selRect.top - optionDom.clientHeight - 1;
				if (oRect.x + oRect.w > window.innerWidth)
					oRect.x = selRect.right - oRect.w;
				if (oRect.y < 0) oRect.y = 0;
				if (oRect.x < 0) oRect.x = 0;
				//是指位置
				optionDom.style.left = oRect.x + "px";
				optionDom.style.top = oRect.y + "px";
				optionDom.style.minWidth = oRect.w + "px";
				//禁用事件冒泡
				e.stopPropagation();
			}
			//处理option每个选项的点击事件
			var items = optionDom.children;
			for (var i = 0; i < items.length; i++) {
				items[i].onclick = function (e) {
					//赋值
					sel.value = this.getAttribute("value");
					//调用onshow方法显示数据
					var ret1 = callOnShow.call(sel);
					//调用onChange函数，并设置this参数为sel
					var ret2 = callOnChange.call(sel);
					//隐藏
					if (ret1 !== false && ret2 !== false && optionDom.getAttribute("ke-option-keep")!="true")
						optionDom.style.display = "none";
				}
			}
			optionDom.onclick = function (e) {
				e.stopPropagation();
			}
			//文档点击的时候隐藏option
			document.addEventListener("click", function () {
				optionDom.style.display = "none";
			});
			
		}
		//调用onshow函数
		function callOnShow() {
			var ret1 = true;
			var ret2 = true;
			if (this.onshow)
				ret1 = this.onshow.call(this);
			ret2 = eval(this.getAttribute("onshow"));
			return !(ret1===false || ret2===false);
			
		}
		//调用onChange函数
		function callOnChange() {
			var ret1 = true;
			var ret2 = true;
			if (this.onchange)
				ret1 = this.onchange.call(this);
			ret2 = eval(this.getAttribute("onchange"));
			return !(ret1 === false || ret2 === false);
		}

		return obj;
	}
};

/*
类说明：
	此类为开关按钮类，按钮有开和关两种状态，处理class="ke-btn-switch"的div
*/
window.switchBtn = {
	create: function () {
		var obj = {};

		obj.init = function () {
			var btns = document.querySelectorAll(".ke-btn-switch");
			for (var i = 0; i < btns.length; i++) {
				handleOne(btns.item(i), btns);
			}
		}

		function handleOne(btn, btns) {
			btn.onclick = function () {
				var isLive = /ke-btn-live/.test(this.className);
				var className = "ke-btn ke-btn-switch" + (isLive ? "" : " ke-btn-live");
				this.className = className;
			}
		}

		return obj;
	}
};




/*
功能：程序入口函数
*/
function ke_main() {
	var editorDom = document.querySelector("[kEditor]");
	if (!editorDom) return;
	//初始化各类资源
	elemSelect.create().init();
	switchBtn.create().init();
	//事件处理======
	//1）基本select
	var baseSelects = document.querySelectorAll(".ke-select-base");
	for (var i = 0; i < baseSelects.length; i++) {
		var sel = baseSelects.item(i);
		sel.onshow = function () {
			this.querySelector('.ke-select-text').innerHTML = this.value;
		};
	}
	//2）颜色select
	var colorSelects = document.querySelectorAll(".ke-select-color");
	for (var i = 0; i < colorSelects.length; i++) {
		var sel = colorSelects.item(i);
		sel.onshow = function () {
			this.querySelector('.ke-select-color-show').style.background = this.value;
		}
	}
	//创建Editor
	var iframe = document.createElement("iframe");
	iframe.frameBorder = 0;
	iframe.style.width = "100%";
	iframe.style.height = "100%";
	editorDom.appendChild(iframe);
	//创建textarea
	var textarea = document.createElement("textarea");
	textarea.style.display = "none";
	textarea.name = editorDom.getAttribute("name");
	editorDom.appendChild(textarea);
	//初始化编辑器
	var editor = kEditorC.create(editorDom, iframe.contentWindow, textarea);
	var timer = setInterval(function () {
		editor.init();
		clearInterval(timer);
	}, 100);
	//控件操作
	var ctrl = kEditorCtrl.create(editor).init();
	//设置操作方法
	kEFunctions(editorDom, iframe, ctrl, editor);
	//默认刷新一次
	editorDom.refresh();
	//获取编辑控件浮窗
	var widget = document.querySelector(".ke-widget");
	//基本事件处理
	var cdoc = iframe.contentWindow.document;
	iframe.contentWindow.onfocus = cdoc.onclick = function () {
		//编辑模式
		widget.style.display = "inline-block";
		editorDom.refresh();
	}
	document.addEventListener("click", function () {
		//取消编辑
		widget.style.display = "none";
	});
	widget.onclick = function (e) {
		e.stopPropagation();
	}
	//初始化内容
	var contentDom = document.querySelector("[keContent]");
	if (contentDom) {
		var html = contentDom.innerHTML;
		editorDom.setContent(html);
		contentDom.parentElement.removeChild(contentDom);
	}
}


function kEFunctions(editorDom, iframe, ctrl, editor) {
	var cdoc = iframe.contentWindow.document;
	//刷新方法
	editorDom.refresh = function () {
		ctrl.ShowAndSetPos(editorDom);
	};

	//设置内容方法
	//如果设置了replacer，则会根据replacer进行替换，replacer是一个函数，参数为str，返回替换后的数据
	editorDom.setContent = function (str, replacer) {
		iframe.contentWindow.document.body.innerHTML = replacer ? replacer(str) : str;
	}

	//获取内容
	editorDom.getContentHTML = function () {
		return iframe.contentWindow.document.body.innerHTML;
	}
	//获取纯文本
	editorDom.getContentText = function () {
		return iframe.contentWindow.document.body.innerText;
	}


	editorDom.insert = function (html) {
		editor.insertHTML(html);
	}

	editorDom.focus = function () {
		iframe.contentWindow.document.body.focus();
	}

}


window.addEventListener("load", ke_main);


