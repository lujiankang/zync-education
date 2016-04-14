/*
消息控制说明：
	1、消息控制用于显示用户消息。
	2、由于有可能用户在关闭浏览器是并不一定看完了所有消息，所以需要将消息缓存下来，下次打开时直接显示消息
	3、消息缓存读取如下：
					  开始
					   ︳
					读取缓存
					   ︳
					JSON转数组－－－－┐
					   ︳T			  ︳
			   ┌— 有消息吗－－┐    ︳
			   ︳	   ︳T	    ︳F   ︳F
			   ︳	显示消息    ︳    ︳
			   ︳	   ︳	    ︳    ︳
			   └－－－┘	    ︳    ︳
					  ┌－－－－┴－－┘
					 结束

	4、消息缓存写入如下
						  开始
						   ︳
				   ┌－－－┴－－－┐
				收到消息      顶部图标点击
				   ︳			   ︳
				写入缓存		置空缓存
				   ︳			   ︳
				   └－－－┬－－－┘
				           ︳
					   数组长度>50－┐
				           ︳T		︳F
				       去掉第一个	︳
				           ├－－－	┘
						转换成JSON
						   ︳
						存入缓存
						   ︳
						  结束
	5、消息缓存采用localStorage存储
*/

window.p_message = {
	create: function () {
		var obj = {};
		var datas = [];
		//数据
		var main = document.querySelector(".pop-message");
		var container = main.querySelector(".msg-list");
		var body = main.querySelector(".pop-content");
		var msgNum = document.querySelector(".page-top .item-msg-num");
		var domPush = null;
		var domRmnd = null;
		var domUser = null;
		var domRept = null;
		var frameShadow = document.querySelector(".page-container >.page-main >.frame-shadow");
		//用户缓存，当点击回复时将该用户资料缓存
		var sendBuffer = { sender: null, senderType: null };

		var msgBuffer = [];

		/*
		功能：初始化
		*/
		obj.init = function () {
			//克隆数据
			var pd = container.querySelector(".msg-list .li-msg-push");
			var rd = container.querySelector(".msg-list .li-msg-rmnd");
			var ud = container.querySelector(".msg-list .li-msg-user");
			var rpd = container.querySelector(".msg-list .li-msg-rept");
			domPush = pd.cloneNode(true);
			domRmnd = rd.cloneNode(true);
			domUser = ud.cloneNode(true);
			domRept = rpd.cloneNode(true);
			//清空所有元素
			removeChildrenAll(container);
			//事件处理
			//顶部图标点击显示界面
			topMessage.onclick = function (e) {
				main.style.display = "inline-block";
				frameShadow.style.display = "inline-block";
				body.scrollTop = body.scrollHeight;
				msgNum.innerHTML = 0;
				msgNum.style.display = "none";
				msgCache(false);		//清空缓存
				e.stopPropagation();
			}
			//页面点击关闭界面
			document.addEventListener("click", function (e) {
				meHide();
				frameShadow.style.display = "none";
				//repeater.style.display = "none";
			});
			//主界面点击关闭输入，并阻止事件冒泡
			main.onclick = function (e) {
				//repeater.style.display = "none";
				e.stopPropagation();
			}
			//顶部其他图标点击时关闭当前界面
			on([topSetting, topSearch], "click", function () {
				meHide();
			});
			return obj;
		}

		/*
		功能：显示消息
		参数：
			msg		Object		要显示的消息
		*/
		obj.showMsg = function (msg) {
			msg.time = msg.time ? msg.time : now();
			var needScrool = (body.scrollTop >= body.scrollHeight - 398 - 20);
			switch (msg.type) {
				case "push":
					showBase(msg.type, msg.msg, msg.time);
					msgAdd();
					break;
				case "rmnd":
					showBase(msg.type, msg.info, msg.time);
					msgAdd();
					break;
				case "chat":
					var data = msg.msg;
					showChat(data.sender, data.sendertype, data.content, msg.time);
					msgAdd();
					break;
				case "repd":
					var data = msg.msg;
					showRepeat(data.uid, data.utype, data.content, msg.time);
					break;
				default:
					break;
			}
			//保存消息
			msgCache(msg);		//加入到缓存
			//将消息滚到底部
			setTimeout(function () {
				if (needScrool) {
					body.scrollTop = body.scrollHeight-397;
				}
			}, 100);
		}


		//消息缓存处理，传入非真值则清空缓存，否则加入缓存
		function msgCache(msg) {
			if (!msg)
				msgBuffer = [];
			else {
				msgBuffer.push(msg);
				//超过时，去掉前面的消息
				if (msgBuffer.length > 50) msgBuffer.shift();
			}
			localStorage["k360_edu_zync_msg_cache"] = JSON.stringify(msgBuffer);
		}

		function msgAdd() {
			//如果没有显示消息窗口则设置消息树
			if (main.style.display != "inline-block") {
				var num = parseInt(msgNum.innerHTML) + 1;
				//判断是否显示消
				if (num > 0) msgNum.style.display = "inline-block";
				//超过最大数
				if (num > 99)
					num = "99+";
				msgNum.innerHTML = num;
			}
		}

		//关闭当前窗口
		function meHide() {
			main.style.display = "none";
			var rpts = main.querySelectorAll(".msg-repeator");
			style(rpts, { display: "none" });
		}

		//显示提醒和推送
		function showBase(type, content, time) {
			var li = ((type == "push") ? domPush : domRmnd).cloneNode(true);
			var timedom = li.querySelector(".msg-time");
			var textdom = li.querySelector(".msg-text");
			timedom.innerHTML = time;
			textdom.innerHTML = content;
			container.appendChild(li);
		}

		//显示回复
		function showRepeat(uid, utype, content, time) {
			//获取dom
			var li = domRept.cloneNode(true);
			var namedom = li.querySelector(".msg-name");
			var timedom = li.querySelector(".msg-time");
			var textdom = li.querySelector(".msg-text");
			var headdom = li.querySelector(".mgs-user-head");
			//设置各项属性
			headdom.src = getUserHead(utype, uid, true);
			timedom.innerHTML = time;
			textdom.innerHTML = content;
			container.appendChild(li);
			//设置姓名
			var name = getUserName(uid, utype);
			if (name === false) {
				loadUserName(uid, utype, function (uname) {
					namedom.innerHTML = uname;
					window.users.push({ id: uid, type: utype, name: uname });
				});
			} else {
				namedom.innerHTML = name;
			}
		}

		//显示聊天消息
		function showChat(sender, sendtype, content, time) {
			//获取dom
			var li = domUser.cloneNode(true);
			var namedom = li.querySelector(".msg-name");
			var timedom = li.querySelector(".msg-time");
			var textdom = li.querySelector(".msg-text");
			var headdom = li.querySelector(".mgs-user-head");
			//设置各项属性
			headdom.src = getUserHead(sendtype, sender, true);
			timedom.innerHTML = time;
			textdom.innerHTML = content;
			container.appendChild(li);
			//设置姓名，如果缓存有姓名则使用缓存的姓名，如果没有则从服务器获取
			var name = getUserName(sender, sendtype);
			if (name === false) {
				loadUserName(sender, sendtype, function (uname) {
					namedom.innerHTML = uname;
					user2local(sender, sendtype, uname);
				});
			} else {
				namedom.innerHTML = name;
			}
			//各个按钮事件
			li.querySelector(".mailSendBtn").onclick = function () {
				chat.sendMessage(sendtype, sender);
			}
			li.querySelector(".infoViewBtn").onclick = function () {
				userinfoviewer.show(sender, sendtype);
			}
			//li.querySelector(".showReplyBtn").onclick = function (e) {
			//	sendBuffer.sender = sender;
			//	sendBuffer.senderType = sendtype;
			//	repeater.style.display = "block";
			//	repeatInput.innerHTML = "";
			//	repeatInput.focus();
			//	e.stopPropagation();
			//}
		}

		//获取当前时间xx/xx xx:xx
		function now() {
			var date = new Date();
			//var y = date.getFullYear();
			var m = date.getMonth()+1;
			(m < 10) && (m = "0" + m);
			var d = date.getDate();
			(d < 10) && (d = "0" + d);
			var h = date.getHours();
			(h < 10) && (h = "0" + h);
			var i = date.getMinutes();
			(i < 10) && (i = "0" + i);
			//var s = date.getSeconds();
			//(s < 10) && (s = "0" + s);
			return m + "/" + d + " " + h + ":" + i;
		}

		return obj;
	}
};

window.pMessageMain = function () {
	window.messageShower = p_message.create().init();
	//Demo:
	//messageShower.showMsg({ type: "repd", msg: { uid: 1, utype: "teacher", content: "附近的司法司法考试老福克斯的" } });
	//messageShower.showMsg({ type: "chat", msg: { sender: 1, sendertype: "student", content: "老师你好，你是煞笔" } });
	//messageShower.showMsg({ type: "push", msg: "我发来了一条推送消息，告诉你今晚等着。" });
	//messageShower.showMsg({ type: "rmnd", info: "明天早上9点钟上交所有作业，否则统统0分。" });
	//messageShower.showMsg({ type: "chat", msg: { sender: 1, sendertype: "student", content: "老师你好，你是煞笔" }, time:"自定义时间" });

	//读取消息缓存
	try{
		var caches = JSON.parse(localStorage["k360_edu_zync_msg_cache"]);
		for (var i in caches) {
			messageShower.showMsg(caches[i]);
		}
	} catch (err) {
		console.error("读取缓存消息时出现错误，主要是因为该缓存快为空，不能转换JSON，不过没事儿，下次不会的。", err);
	}
	//获取未读消息
	var http = k360_http.create().setUrl(MSG_UNREAD_GETTER);
	dataLoad(http, "获取消息失败", function (data) {
		for (var i in data) {
			var d = data[i];
			var msg = {};
			msg.type = "chat";
			msg.msg = {};
			msg.msg.sender = d.sender;
			msg.msg.sendertype = d.sutype;
			msg.msg.content = d.content;
			msg.time = d.time;
			messageShower.showMsg(msg);
		}
	}, function (err) {
		k360_msg_scr_shower.show(err);
	}, null);
}