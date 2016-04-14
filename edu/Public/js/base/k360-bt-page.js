window.k360_bt_page = {
	/*
	功能：创建分页对象
	参数：
		selector		string			分页控件选择器
	*/
	create: function (selector) {
		var obj = {};
		var page = (typeof selector == "string") ? document.querySelector(selector) : selector;
		var gotoLastBtn = page.querySelector(".goto-last");
		var gotoNexttBtn = page.querySelector(".goto-next");
		var gotoInput = page.querySelector(".goto-input");
		var gotoBtn = page.querySelector(".goto-button");
		var dataNum = page.querySelector(".data-num");
		var pageNum = page.querySelector(".page-num");
		var pageNow = page.querySelector(".page-now");
		var locked = false;
		var onPageTo = function (page) { };
		var onError = function (err) { };

		var errors = {
			befirst: "已经是第一页了",
			belast: "已经是最后一页了",
			belocked: "数据进行中，无法跳转",
			beinvalid: "输入的页码无效"
		};

		var conf = {
			pageNum: 0,		//总页码
			dataNum: 0,		//总数据数
			pageNow: 0		//当前页码
		};

		/*
			功能：获取错误信息列表，同时可以直接设置错误提示信息
		*/
		obj.errors = errors;

		/*
			功能：设置分页控件信息
			参数：
				page_num	int		总页数
				data_num	int		数据数量
				page_now	int		当前页码（下标0开始）
		*/
		obj.setInfo = function (page_num, data_num, page_now) {
			conf.pageNum = page_num;
			conf.dataNum = data_num;
			conf.pageNow = page_now;
			setButtonStyle();
			fillData();
		}

		/*
			功能：设置总页数
		*/
		obj.setPageNum = function (page_num) {
			conf.pageNum = page_num;
			setButtonStyle();
			fillData();
		}

		/*
			功能：设置数据总数
		*/
		obj.setDataNum = function (data_num) {
			conf.dataNum = data_num;
			setButtonStyle();
			fillData();
		}

		/*
			功能：设置当前页码（下标0开始）
		*/
		obj.setPageNow = function (page_now) {
			conf.pageNow = page_now;
			setButtonStyle();
			fillData();
		}

		/*
			功能：获取当前页码
			返回值：
				int		当前页码
		*/
		obj.getCurPage = function () {
			return conf.pageNow;
		}

		/*
			功能：为分页加锁
			说明：加锁后点击任何按钮将无反应
		*/
		obj.lock = function () {
			locked = true;
		}

		/*
			功能：分页解锁
		*/
		obj.unlock = function () {
			locked = false;
		}

		/*
			功能：分页是否加有锁
		*/
		obj.isLocked = function () {
			return locked;
		}

		/*
			功能：监听分页跳转
			参数：
				onpage		func(page)		回调函数
		*/
		obj.onpage = function (onpage) {
			onPageTo = onpage;
		}

		/*
			功能：添加错误错误，（已经是上一页、已经是最后一页、加锁状态）
			参数：
				onerror		func(error)		回调函数
		*/
		obj.onerror = function (onerror) {
			onError = onerror;
		}

		//初始化一次
		init();
		setButtonStyle();

		function fillData() {
			pageNow.innerHTML = conf.pageNow + 1;
			pageNum.innerHTML = conf.pageNum;
			dataNum.innerHTML = conf.dataNum;
		}

		//设置按钮样式
		function setButtonStyle() {
			gotoLastBtn.disabled = (conf.pageNow <= 0);
			gotoNexttBtn.disabled = (conf.pageNow >= conf.pageNum - 1 || conf.pageNum == 0);
			//跳转按钮
			var val = parseInt(gotoInput.value);
			var enable = (/^\d+$/.test(val) && val > 0 && val <= conf.pageNum);
			gotoBtn.disabled = !enable;
		}

		//初始化，做各种事件处理
		function init() {
			//上一页
			gotoLastBtn.addEventListener("click", function () {
				if (locked) {
					onError && onError(errors.belocked);
					return;
				}
				if (conf.pageNow <= 0) {
					onError && onError(errors.befirst);
					return;
				}
				onPageTo && onPageTo(conf.pageNow - 1);
			});
			//下一页
			gotoNexttBtn.addEventListener("click", function () {
				if (locked) {
					onError && onError(errors.belocked);
					return;
				}
				if (conf.pageNow >= conf.pageNum - 1) {
					onError && onError(errors.belast);
					return;
				}
				onPageTo && onPageTo(conf.pageNow + 1);
			});
			//跳转到指定页
			gotoBtn.addEventListener("click", function () {
				if (locked) {
					onError && onError(errors.belocked);
					return;
				}
				var to = gotoInput.value;
				if (!to || to <= 0 || to > conf.pageNum) {
					onError && onError(errors.beinvalid);
					return;
				}
				onPageTo && onPageTo(to - 1);
			});
			//页码输入框事件
			gotoInput.addEventListener("keyup", inputEvent);
			//默认检测一次
			inputEvent();
			//输入框事件回调，默认执行一次
			function inputEvent() {
				var val = parseInt(gotoInput.value);
				var enable = (/^\d+$/.test(val) && val > 0 && val <= conf.pageNum);
				gotoBtn.disabled = !enable;
			}
		}

		return obj;
	}
}