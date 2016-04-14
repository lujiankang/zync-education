window.k360_chat = {
	create: function () {
		var obj = {};

		var main = document.querySelector(".sender-win");
		var body = main.querySelector(".sender-container");
		var head = main.querySelector(".viewer-user-head").querySelector("img");
		var input = main.querySelector("textarea");
		var sendbtn = main.querySelector(".sender-msg");
		var mailbtn = main.querySelector(".sender-mail");
		var namedom = main.querySelector(".sender-user").querySelector("span");
		var mailbtnicon = mailbtn.querySelector("i");

		var plane = main.querySelector(".sender-msg-tip");

		var sendBuffer = { id: 0, type: 0 };

		var conf = {
			host: "127.0.0.1",
			port: 38438
		};

		var ws = null;

		/*
		功能：初始化
		*/
		obj.init = function () {
			//Dom初始化
			domInit();
			//websocket创建及事件监听
			ws = new WebSocket("ws://" + conf.host + ":" + conf.port);
			ws.onerror = function (evt) {
				k360_popover.create().toast("WebSocket服务器除了点儿问题");
			}
			ws.onclose = function (evt) {
				k360_popover.create().toast("WebSocket服务器已停止运行");
				console.log(evt);
			}
			ws.onmessage = function (evt) {
				//解码消息
				var data = JSON.parse(evt.data);
				//消息过滤
				if (msgfilter.push == "F" && data.type == "push") return;
				if (msgfilter.rmnd == "F" && data.type == "rmnd") return;
				if (msgfilter.chat == "F" && data.type == "chat") return;
				var typeName = "其他";
				(data.type == "push") && (typeName = "推送");
				(data.type == "rmnd") && (typeName = "提醒");
				(data.type == "chat") && (typeName = "用户");
				//播放铃声
				if (data.type == "chat" && midiplay.user == "T")
					msgMid.play();
				else if ((data.type == "rmnd" || data.type == "push") && midiplay.syst == "T")
					tipMid.play();
				//发出提示
				k360_msg_scr_shower.show("收到了新【" + typeName + "】消息");
				messageShower.showMsg(data);
			}
			ws.onopen = function (evt) {
				//连接打开后告知服务器自己的信息
				sendData("join", "info", { id: window.user.id, name: window.user.name, type: window.user.type });
				console.log("Open");
			}

			return obj;
		}

		/*
		功能：打开一个消息发送窗口
		参数：
			type		用户类型
			id			用户id
		*/
		obj.sendMessage = function (type, id) {
			//判断
			if (type == user.type && id == user.id) {
				k360_popover.create().toast("不能给自己发送内容");
				return;
			}
			//保存数据
			sendBuffer.id = id;
			sendBuffer.type = type;
			//显示界面
			input.value = "";
			namedom.innerHTML = "加载中...";
			main.style.display = "inline-block";
			//设置输入框宽度
			if (window.innerWidth <= 600) input.style.width = input.parentElement.clientWidth - 115 + "px";
			//设置头像
			head.src = getUserHead(type, id, true);
			//设置姓名
			var name = getUserName(id, type);
			if (name === false) {
				loadUserName(id, type, function (uname) {
					namedom.innerHTML = uname;
					user2local(id, type, uname);
				});
			} else {
				namedom.innerHTML = name;
			}
			//输入框焦点
			input.focus();
			return obj;
		}

		/*
		功能：发送推送消息
		参数：
			str			要发送的推送的内容
		*/
		obj.pushMessage = function (str) {
			sendData("msg", "msg", { type: "push", msg: str });
		}

		/*
		功能：给制定的人发送消息
		参数：
			uid		用户id
			utype	用户类型
			message	消息内容
		*/
		obj.sendTo = function (uid, utype, message) {
			sendMultDate("chat", { sender: user.id, sendertype: user.type, recver: uid, recvertype: utype, msg: message });
		}

		/*
		功能：关闭服务器（此功能不要调用）
		*/
		obj.shutdown = function () {
			sendData("off");
		}


		function domInit() {
			//居中
			//body.style.marginTop = (window.innerHeight - 120) / 2 + "px";
			//输入框控制
			function inputfunc () {
				var height = this.scrollHeight;
				if (!this.value) height = 25;
				if (height >= 55) {
					this.style.overflowY = "scroll";
					height = 55;
				} else {
					this.style.overflowY = "hidden";
				}
				var top = 65 - height;
				this.style.maxHeight = height + "px";
				this.style.height = height + "px";
				this.style.marginTop = top + "px";
			}
			input.onkeyup = inputfunc;
			input.onclick = inputfunc;
			//事件冒泡
			main.onclick = function (e) {
				e.stopPropagation();
			}
			//按钮事件——发送消息
			sendbtn.onclick = function () {
				if (!input.value) return;
				var val = input.value;
				obj.sendTo(sendBuffer.id, sendBuffer.type, val);
				input.click();
				//显示刚发送的消息
				messageShower.showMsg({ type: "repd", msg: { uid: sendBuffer.id, utype: sendBuffer.type, content: val } });
				//飞机
				plane.style.animation = "";
				setTimeout(function () {
					plane.style.animation = "msgSendAnim 2s forwards";
					setTimeout(function () {
						input.value = "";
						input.focus();
					}, 500);
				}, 40);
			}
			//按键事件——发送邮件
			var mailSending = false;
			mailbtn.onclick = function () {
				//数据监测&锁
				if (!input.value) return;
				if (mailSending) return;
				//变量
				mailSending = true;
				mailbtn.disabled = true;
				var oldClass = mailbtnicon.className;
				mailbtnicon.className = "fa fa-spinner fa-spin";
				var http = k360_http.create().setUrl(USER_MAIL_SENDER)
				.addData({ id: sendBuffer.id, type: sendBuffer.type, content: input.value });
				//数据提交
				dataLoad(http, "邮件发送错误", function (data) {
					k360_popover.create().tip("邮件已发送");
				}, function (err) {
					k360_popover.create().toast(err);
				}, function () {
					mailSending = false;
					mailbtn.disabled = false;
					mailbtnicon.className = oldClass;
				});
			}
		}


		function sendData(type, name, data) {
			var obj = { type: type };
			if (name) obj[name] = data;
			var str = JSON.stringify(obj);
			ws.send(str);
		}

		function sendMultDate(type, datas) {
			var obj = { type: type };
			for (var i in datas) {
				obj[i] = datas[i];
			}
			var str = JSON.stringify(obj);
			ws.send(str);
		}

		return obj;
	}
};

window.chatMain = function () {
	window.chat = k360_chat.create().init();
}
