window.addEventListener("load", function () {
	window.hidefilter = function () { }

	//如果不是手机屏幕则取消所有操作
	if (window.innerWidth > 600) return;
	//否则进行手机这姓手机端操作
	var listBtn = document.querySelector(".header-menu");
	var topBtns = document.querySelector(".top-btns");


	listBtn.addEventListener("click", function (e) {
		topBtns.style.display = "inline-block";
		e.stopPropagation();
	});
	topBtns.onclick = function () {
		topBtns.style.display = "none";
	}
	document.addEventListener("click", function () {
		topBtns.style.display = "none";
	});

	var filters = document.querySelectorAll(".btn-filter");
	var filterDoms = document.querySelectorAll(".top-filter");

	for (var i = 0; i < filterDoms.length; i++) {
		var selects = filterDoms[i].querySelectorAll("select");
		for(var j=0; j<selects.length; j++){
			selects[j].style.width = "";
		}
		document.body.appendChild(filterDoms[i]);
	}
	for (var i = 0; i < filters.length; i++) {
		domEvent(filters[i]);
	}

	function domEvent(filter) {
		var fname = filter.getAttribute("for");
		var dom = document.body.querySelector("[filter='" + fname + "']");

		filter.onclick = function () {
			dom.style.display = "inline-block";
		}
		for (var j = 0; j < dom.children.length; j++) {
			dom.children[j].addEventListener("click", function (e) {
				e.stopPropagation();
			})
		}
	}

	window.hidefilter = function () {
		for (var i = 0; i < filterDoms.length; i++) {
			filterDoms[i].style.display = "none";
		}
	}

})