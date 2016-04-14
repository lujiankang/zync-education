window.k360_msg_scr_shower = {
	SHOW_TIME: 4000,

	/*
	功能：显示一则消息
	*/
	show: function (str) {
		k360_msg_scr_shower.data.push(str);
	},

	//运行
	_run: function () {
		//初始化
		if (!k360_msg_scr_shower.data) {
			k360_msg_scr_shower.data = [];
		}

		var showing = false;			//显示锁

		//初始化
		function init() {
			if (k360_msg_scr_shower.timer) return;
			k360_msg_scr_shower.timer = setInterval(function () {
				if (showing) return;
				show();
			}, 1000);
			//显示
			function show() {
				if (k360_msg_scr_shower.data.length > 0) {
					var text = k360_msg_scr_shower.data.shift();
					createDom(text);
				}
			}

		}
		//创建dom并显示
		function createDom(text) {
			showing = true;
			//创建dom
			var dom = document.createElement("div");
			dom.style.border = "solid 1px #2C3E50"
			dom.style.background = "#34495E";
			dom.style.position = "fixed";
			dom.style.bottom = "2px";
			dom.style.width = "150px";
			dom.style.left = "2px";
			dom.style.height = "20px";
			dom.style.color = "#FFF";
			dom.style.textAlign = "center";
			dom.style.lineHeight = "20px";
			dom.style.fontFamily = "微软雅黑";
			dom.style.fontSize = "8pt";
			dom.style.opacity = "0";
			dom.style.transition = "all 1000ms";
			dom.innerHTML = text;
			document.body.appendChild(dom);
			//缓慢显示
			setTimeout(function () {
				dom.style.opacity = "1";
			}, 100);
			//显示一段时间后删除
			setTimeout(function () {
				dom.style.opacity = "0";
				//缓慢隐藏
				setTimeout(function () {
					//删除
					document.body.removeChild(dom);
					showing = false;
				}, 1000);
			}, k360_msg_scr_shower.SHOW_TIME);
		}

		init();
	}

};


window.addEventListener("load", function () {
	k360_msg_scr_shower["_run"]();
});