window.course_studentcourse_controller = {
	create: function () {
		var obj = {};

		obj.init = function () {


			dateShower.parentElement.onclick = function (e) {
				dateShower.style.display = "inline-block";
				e.stopPropagation();
			}

			document.addEventListener("click", function (e) {
				dateShower.style.display = "none";
			});

			
			loadDate();
		}

		//加载科目列表，加载完成后默认点击一次第一个元素
		function loadSubjects(year, degree) {
			if (!year || !degree) return;
			while (subjectShower.children.length > 0) {
				subjectShower.removeChild(subjectShower.children[0]);
			}
			subjectShower.year = null;
			subjectShower.degree = null;
			var loading = document.querySelector(".list-subjects .dataLoading");
			loading.style.display = "inline-block";
			var http = k360_http.create().setUrl(__ROOT__ + "/index.php/Home/Course/gSubjectOfTimeStudent").addData({ year: year, degree: degree });
			dataLoad(http, "加载课程列表醋欧文", function (data) {
				subjectShower.year = year;
				subjectShower.degree = degree;
				courseDatas = data;
				if (!data || data.length == 0) {
					k360_popover.create().setTopPop(true).toast("没有任何课程", null, 2000);
				}
				var li = null;
				for (var i in data) {
					var bli = showCourse(data[i]);
					if (!li) li = bli;
				}
				li.click();
			}, function (err) {
				k360_popover.create().setTopPop(true).toast(err);
			}, function () {
				loading.style.display = "none";
			});
		}

		//显示右边信息栏
		function showRightInfo(course) {
			faceImage.src = __APP__ + "/Home/Subject/gSubjectFace/id/" + course.subject;
			var detail = document.querySelector(".data-detail");
			for (var i in course) {
				var doms = detail.querySelectorAll("[name='" + i + "']");
				for (var j = 0; j < doms.length; j++) {
					doms.item(j).innerHTML = course[i] ? course[i] : "无";
				}
			}
		}

		//显示课程信息
		function showCourseInfo(course) {
			loadFiles(course);
			//loadStudentsList(course);
			showRightInfo(course);
			while (courseDescShower.children.length > 0) {
				courseDescShower.removeChild(courseDescShower.children[0]);
			}
			showingCourse = course;
			var chapters = course.chapter;
			for (var i in chapters) {
				var chapter = chapters[i];
				var li = document.createElement("li");
				li.innerHTML = chapter.name;
				var ol = document.createElement("ol");
				li.appendChild(ol);
				for (var j in chapter.children) {
					var cli = document.createElement("li");
					cli.className = "animated fadeInRight";
					cli.style.animationDuration = "0.3s";
					cli.appendChild(document.createTextNode(chapter.children[j]));
					ol.appendChild(cli);
				}
				courseDescShower.appendChild(li);
			}
			document.querySelector(".main-container .handles").children[0].click();
		}

		//显示课程
		function showCourse(course) {
			studentsAll = null;
			studentsCur = null;
			showingCourse = null;
			courseDatas = null;

			var li = document.createElement("li");
			li.innerHTML = course.name;
			li.className = "a animated fadeInLeft";
			li.style.animationDuration = "0.3s";
		
			subjectShower.appendChild(li);

			li.onclick = function () {
				showCourseInfo(course);
			}
			return li;
		}

		//加载年份，并显示到年份选择列表中，加载完成后会点击一次第一个li
		function loadDate(minYear) {
			var date = new Date();
			var year = date.getFullYear()
			var month = date.getMonth() + 1;
			if (month > 7) addOne(year, 2);
			addOne(year, 1);
			for (var i = year - 1; i >= minYear; i--) {
				addOne(i, 2);
				addOne(i, 1);
			}
			dateShower.children[0].click();

			function addOne(year, degree) {
				var str = year + "年 （" + ((degree == 1) ? "上" : "下") + "）";
				var li = document.createElement("li");
				li.innerHTML = str;
				dateShower.appendChild(li);
				li.onclick = function (e) {
					dateShower.style.display = "none";
					loadSubjects(year, degree);
					e.stopPropagation();
				}
			}
		}
		return obj;
	}
};