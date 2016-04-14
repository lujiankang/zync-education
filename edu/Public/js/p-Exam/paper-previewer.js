window.exam_design_paper = {
	create: function () {
		var obj = {};
		/*
		功能：调用此方法会自动对页面进行调整
		*/
		obj.format = function () {
			var firstPage = document.querySelector(".paper >.page");
			toFirst();
			startFrom(firstPage);
			showPagerItem();
		}

		/*
		功能：清空数据
		*/
		obj.clear = function () {
			//删除paper
			var papers = preview.querySelectorAll(".paper");
			while (papers.length > 1) {
				papers.item(1).parentElement.removeChild(papers.item(1));
				papers = preview.querySelectorAll(".paper");
			}
			//删除page
			var pages = papers.item(0).querySelectorAll(".page");
			if (pages.length > 1) pages.item(1).parentElement.removeChild(pages.item(1));
			//删除题目
			var items = pages.item(0).querySelectorAll(".topic-title,.topic-text");
			while (items.length > 0) {
				items.item(0).parentElement.removeChild(items.item(0));
				items = pages.item(0).querySelectorAll(".topic-title,.topic-text");
			}
		}
		//显示页码
		function showPagerItem() {
			var pages = document.querySelectorAll(".paper");
			for (var i = 0; i < pages.length; i++) {
				var spans = pages.item(i).querySelector(".page-fotter").querySelectorAll("span");
				spans.item(1).innerHTML = pages.length;
				spans.item(0).innerHTML = i + 1;
			}
		}
		//获取下一页，如果没有则自动创建
		function getNextPage(cur) {
			var pages = document.querySelectorAll(".paper >.page");
			for (var i = 0; i < pages.length; i++) {
				var p = pages.item(i);
				if (cur == p) {
					if (pages.length < i + 1) return pages.item(i + 1);
					else {
						var next = document.createElement("div");
						next.className = "page";
						if ((pages.length & 1) == 1) {
							//基数个直接加入
							p.parentElement.appendChild(next);
							//p.parentElement.appendChild(PACKLINE_CLONED);
						} else {
							//偶数个则先创建一个paper再加入它
							var prt = document.createElement("div");
							prt.className = "paper";
							prt.innerHTML = "<div class=\"page-fotter\">第<span>1</span>页&nbsp;&nbsp;共<span>3</span>页（<span>A</span>卷）</div>";
							//prt.appendChild(PACKLINE_CLONED);
							prt.appendChild(next);
							p.parentElement.parentElement.appendChild(prt);
						}
						return next;
					}
				}
			}
			return null;
		}
		//从某一页开始设置
		function startFrom(cur) {
			if (!cur) return;
			//获取下一个page，如果没有创建之
			var next = getNextPage(cur);
			while (cur.clientHeight < cur.scrollHeight && cur.lastElementChild) {
				if (next.children.length > 0) {
					next.insertBefore(cur.lastElementChild, next.firstElementChild);
				} else {
					next.appendChild(cur.lastElementChild);
				}
			}
			//删除空next及paper
			if (next && next.children.length <= 0) {
				var prt = next.parentElement;
				prt.removeChild(next);
				if (prt.children.length <= 1) {
					prt.parentElement.removeChild(prt);
				}
			}
			startFrom(next);
		}
		function toFirst() {
			var pages = document.querySelectorAll(".paper >.page");
			var first = pages.item(0);
			while (pages.length > 1) {
				var page = pages.item(1);
				while (page.children.length > 0) {
					first.appendChild(page.firstElementChild);
				}
				page.parentElement.removeChild(page);
				pages = document.querySelectorAll(".paper >.page");
			}
			papers = document.querySelectorAll(".paper");
			while (papers.length > 1) {
				var paper = papers.item(1);
				paper.parentElement.removeChild(paper);
				papers = document.querySelectorAll(".paper");
			}
		}
		//创建完成返回obj
		return obj;
	}
}