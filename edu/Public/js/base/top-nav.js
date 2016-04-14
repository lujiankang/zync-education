window.addEventListener("load", function () {
	var navCSS = "\
.body-nav{\
	position:absolute;\
	left:0px;\
	top:0px;\
	right:250px;\
	height:51px;\
	background:#f2f3fb;\
	color:#333;\
}\
\
.body-nav i{\
	margin-right:5px;\
	color:#a0a0a0;\
	font-size:19px;\
}\
\
.body-nav .item{\
	padding:0px 12px;\
	min-width:9%;\
	border-bottom:solid 3px #f2f3fb;\
	cursor:pointer;\
	float:left;\
	text-align:center;\
	font-size:14px;\
	line-height:48px;\
}\
\
.body-nav .item:hover{\
	border-bottom-color:#7ccd9b;\
}\
\
.body-nav .item.active{\
	background:#FFF;\
	border-bottom-color:#7ccd9b;\
	cursor:default;\
}";
	var cssDom = document.createElement("style");
	cssDom.innerHTML = navCSS;
	document.head.appendChild(cssDom);

	var navDom = document.createElement("div");
	navDom.className = "body-nav";
	document.body.appendChild(navDom);

	if (!top.mainIframe) return;

	var children = top.mainIframe.childMenus;
	var activeId = top.mainIframe.childActive;

	if (children.length <= 0) return;

	var header = document.querySelector(".header");
	header && (header.style.display = "none");

	for (var i in children) {
		var child = children[i];
		var dom = document.createElement("div");
		if (child.id == activeId)
			dom.className = "item active";
		else dom.className = "item";
		var icon = document.createElement("i");
		icon.className = "fa " + child.icon;
		var text = document.createTextNode(child.name);
		dom.appendChild(icon);
		dom.appendChild(text);
		navDom.appendChild(dom);
		setDomEvent(dom, child);
	}

	function setDomEvent(dom, data) {
		dom.addEventListener("click", function () {
			if (data.id == activeId) return;
			activeId = data.id;
			top.mainIframe.childActive = activeId;
			window.location.href = __ROOT__ + "/index.php/" + data.url;
		});
	}
});