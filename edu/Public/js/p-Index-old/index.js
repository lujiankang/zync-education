window.p_index = {

	create: function () {
		var obj = {};

		var main = document.querySelector(".u-data");
		var win = main.querySelector(".u-data-text");

		obj.init = function () {
			//设置位置
			win.style.marginTop = parseInt((window.innerHeight - 50) / 2) + "px";
			//加载用户数据错误
			userDataLoad();
		}

		function userDataLoad() {
			main.style.display = "inline-block";
			var http = k360_http.create().setUrl(__ROOT__ + "index.php/Home/User/gMyInfo");
			dataLoad(http, "获取您的信息错误了", function (data) {
				//保存用户数据
				window.user = data;
				window.finishGetData();		//调用结束数据获取方法
				alert(data)
			}, function (err) {
				//出现错误
				k360_popover.create().alert("出错了", err, "重新试试")
				.setOnOk(function () {
					window.location.reload(true);
				});
			}, function () {
				//登录结束
				main.style.display = "none";
			});
		}


		return obj;

	}
};


window.finishLogin = function () {
	p_index.create().init();
}
