window.k360_preview = {
	create: function () {
		var obj = {};

		//初始化操作
		obj.init = function () {
			return obj;
		}

		/*
		功能：预览指定问你件
		参数：
			id		int|string		文件id
		*/
		obj.show = function (id) {
			//创建容器包含预览iframe及关闭按钮
			var container = document.createElement("div");
			container.className = "k360-preview-iframe";
			//创建预览iframe
			var iframe = document.createElement("iframe");
			iframe.width = "100%";
			iframe.height = "100%";
			iframe.frameBorder = 0;
			iframe.src = getPreviewURL(id);
			//创建关闭按钮
			var closeBtn = document.createElement("div");
			closeBtn.className = "k360-preview-close fa fa-close";
			//设置三者之间的关系
			container.appendChild(closeBtn);
			container.appendChild(iframe);
			document.body.appendChild(container);
			//全屏显示
			fullScreen(container);
			//关闭按钮事件
			closeBtn.onclick = function () {
				container.style.display = "none";
				setTimeout(function () {
					iframe.parentElement.removeChild(iframe);
					closeBtn.parentElement.removeChild(closeBtn);
					container.parentElement.removeChild(container);
				}, 500);
			}
			//阻止事件冒泡
			container.onclick = function (e) {
				e.stopPropagation();
			}
			return obj;
		}

		//全屏显示
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

		return obj;
	}
};


window.preveiwMain = function () {
	//生成CSS样式
	var previewCSS = "\
.k360-preview-iframe{\
	position:fixed;\
	left:0px;\
	top:0px;\
	width:100%;\
	height:100%;\
	background:#FFF;\
	z-index:1000;\
}\
\
.k360-preview-close{\
	position:fixed;\
	right:20px;\
	top:20px;\
	width:40px;\
	height:40px;\
	border-radius:40px;\
	background:#FFF;\
	border:solid 1px #95A5A6;\
	line-height:38px;\
	text-align:center;\
	cursor:pointer;\
}\
.k360-preview-close:hover{\
	background:#2C3E50;\
	color:#FFF;\
	border:solid 1px #2C3E50;\
}";
	var styleDom = document.createElement("style");
	styleDom.id = "k360-preview-style";
	styleDom.innerHTML = previewCSS;
	document.head.appendChild(styleDom);
	window.preview = k360_preview.create().init();
};