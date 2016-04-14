window.p_setting = {
	create: function () {
		var obj = {};
		var main = document.querySelector(".pop-setting");
		var headDiv = main.querySelector(".head-image-sel");
		var uploading = main.querySelector(".head-uploading");
		var msgfiltercheckboxs = main.querySelectorAll(".pop-msg-filter input[type='checkbox']");
		var midiplaycheckboxs = main.querySelectorAll(".pop-midi-filter input[type='checkbox']");
		var backFeedForm = main.querySelector(".back-feed-form");
		var backFeedBtn = backFeedForm.querySelector("button[type='submit']");
		var helpBtn = main.querySelector(".help-view-btn");
		var helpMain = document.querySelector(".help-viewer");
		var helpBody = helpMain.querySelector(".help-container");

		var frameShadow = document.querySelector(".page-container >.page-main >.frame-shadow");

		obj.init = function () {
			headUpload();
			messageFilter();
			midiPlayCtrl();
			backFeed();
			help();
			//事件处理====
			//点击其他地方隐藏
			document.addEventListener("click", function () {
				main.style.display = "none";
				frameShadow.style.display = "none";
			});
			//点击顶部图标显示
			topSetting.onclick = function (e) {
				main.style.display = "inline-block";
				frameShadow.style.display = "inline-block";
				e.stopPropagation();
			}
			//关闭主界面事件冒泡
			main.onclick = function (e) {
				e.stopPropagation();
			}
			//点击其他图标隐藏
			on([topMessage, topSearch], "click", function () {
				main.style.display = "none";
			});
			//关闭帮助界面事件冒泡
			helpMain.onclick = function (e) {
				e.stopPropagation();
			}
		}


		//帮助
		function help() {
			helpBtn.onclick = function () {
				helpBody.style.marginTop = parseInt((window.innerHeight - 480) / 2) + "px";
				helpMain.style.display = "inline-block";
			}
		}

		//反馈
		function backFeed() {
			backFeedForm.onsubmit = function () {
				//锁
				if (backFeedBtn.innerHTML == "正在提交...") return;
				//数据
				var http = k360_http.create().addForm(this);
				var oldText = backFeedBtn.innerHTML;
				backFeedBtn.innerHTML = "正在提交...";
				//提交
				dataLoad(http, "提交反馈错误", function (data) {
					k360_popover.create().alert("Thank You", "感谢您的反馈，您的支持就是我们最大的动力。");
				}, function (err) {
					k360_popover.create().toast(err);
				}, function () {
					backFeedBtn.innerHTML = oldText;
				});
				return false;
			}
		}

		//消息接收控制
		function messageFilter() {
			//初始化值
			var push = localStorage["k360_edu_zync_msg_push"];
			var rmnd = localStorage["k360_edu_zync_msg_rmnd"];
			var chat = localStorage["k360_edu_zync_msg_chat"];
			msgfilter.push = (push == "F") ? "F" : "T";
			msgfilter.rmnd = (rmnd == "F") ? "F" : "T";
			msgfilter.chat = (chat == "F") ? "F" : "T";
			//设置是否选中
			for (var i = 0; i < msgfiltercheckboxs.length; i++) {
				var cbx = msgfiltercheckboxs.item(i);
				var name = cbx.getAttribute("name");
				//设置选择框选中状态
				cbx.checked = (msgfilter[name] == "F") ? false : true;
			}
			//监听点击
			on(msgfiltercheckboxs, "click", function () {
				var name = this.getAttribute("name");
				var checked = this.checked ? "T" : "F";
				//保存设置
				msgfilter[name] = checked;
				localStorage["k360_edu_zync_msg_" + name] = checked;
			});
		}

		//铃声播放控制
		function midiPlayCtrl() {
			var systmidi = localStorage["k360_edu_zync_midi_syst"];
			var usermidi = localStorage["k360_edu_zync_midi_user"];
			midiplay.syst = (systmidi == "F") ? "F" : "T";
			midiplay.user = (usermidi == "F") ? "F" : "T";
			//设置是否选中
			for (var i = 0; i < midiplaycheckboxs.length; i++) {
				var cbx = midiplaycheckboxs.item(i);
				var name = cbx.getAttribute("name");
				//设置选择框选中状态
				cbx.checked = (midiplay[name] == "F") ? false : true;
			}
			//监听点击
			on(midiplaycheckboxs, "click", function () {
				var name = this.getAttribute("name");
				var checked = this.checked ? "T" : "F";
				//保存设置
				midiplay[name] = checked;
				localStorage["k360_edu_zync_midi_" + name] = checked;
			});
		}

		//头像上传控制
		function headUpload() {
			headDiv.onclick = function () {
				k360_popover.create().confirm("警告", "最好上传正方形头像，否则系统将对图片进行剪裁", "选择图片")
				.setOnOk(function () {
					var finput = document.createElement("input");
					finput.type = "file";
					finput.onchange = picSeled;
					finput.click();
				});
			}
			function picSeled() {
				var http = k360_http.create().setUrl(USER_HEAD_SETTER).addFile("head", this).addData({ type: user.type, id: user.id });
				uploading.style.display = "inline";
				dataLoad(http, "上传头像错误", function (data) {
					//重新设置头像
					userHeadImage.src = getUserHead(user.type, user.id);
					k360_popover.create().tip("头像更改成功");
				}, function (err) {
					k360_popover.create().toast(err);
				}, function () {
					uploading.style.display = "none";
				});
			}
		}


		return obj;
	}
};


window.pSettingMain = function () {
	p_setting.create().init();
}