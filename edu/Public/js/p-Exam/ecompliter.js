/*
题目批量导入解析器简单编译分析：

doWorldAna：

								   ┌──┐大写数字
								   │    │
							       ↓    │
	───→  ①  ── 大写数字 ─→  ②  ──顿号─→  ③  ──非换行─→  ④ ETN
			  │                      │                └──────┐
			  │                     其他                             │换行
			  │					  ↓                              ↓
			  ├──── 其他 ──→  ⑧  ──换行─→  ⑨ T          ⑩ ERR
			  │                      ↑                              ↑
			  │                     其他                             │换行
			  │					  │                ┌──────┘
			  ├───阿拉伯数字─→  ⑤  ──点号─→  ⑥  ──非换行─→  ⑦ EN
			  │				   ↑    │
			  │			       │    │
			  │				   └──┘阿拉伯数字
			  │
			  └──── # ───→   ⑪ AW

 doSynAna：
                                     ┌────────────┐
	                                 ↓                        │
	          ┌──── ETN ──→  ②  ──── T（*） ───┼─→  ③   题型
              │                     │                        │
              │                    其他                       │
              │                     ↓                        ETN
	───→  ①──── 其他 ──→ ⑧  ERR                   │
              │                     ↑                        └──┐
              │                    其他                             │
              │                     │                              │
		      └──── E N ──→  ④  ──── T（*） ────→  ⑤   题目
			                                                         │
		    	                                                     │
		 答案 ⑦ ←── 其他 ───  ⑥  ←─────  AW  ─────┘
								   ↑    │
			                       │    │
								   └T(*)┘
*/

window.ecompliter = {

	error: [],

	/*
	功能：遍历每一个错误
	参数：
		cb		Function(string)		回调函数
	*/
	evetyError: function (cb) {
		for (var i in ecompliter.error) {
			cb(ecompliter.error[i]);
		}
	},

	/*
	功能：字符串元素分析
	参数：
		str		要进行元素分析的元素
	返回值：
		分析之后的数组
	分析说明：
		分析之后会得到如下的数据：
		1. 题目类型序号（中文大写数字 + 顿号）
		2. 题目序号（阿拉伯数字 + 点号）
		3. 文本
		4. 答案
	*/
	doWorldAna: function (str) {
		var index = 0;		//记录字符位置
		var row = 0;		//记录行数
		var col = 0;		//记录列数
		var ach = "";		//字符串缓存
		var words = [];		//存放各类单词

		str += "\n";		//解析需要，最后加上一个换行

		ecompliter.error = [];

		//逐个获取字符，并计算行数和列数，同时加入缓存
		function ch() {
			if (index >= str.length) return false;
			var c = str[index++];
			if (c == "\n") {
				row++;
				col = 0;
			}
			col++;
			ach += c;
			return c;
		}
		//生成ETN
		function ETN() {
			ach = "";
			index--;
			return { type: "ETN", row: row };
		}
		//生成T
		function T() {
			var ret = { type: "T", data: ach.substring(0, ach.length - 1), row: row - 1 };
			//index--;
			ach = "";
			return ret;
		}
		//生成EN
		function EN() {
			ach = "";
			index--;
			return { type: "EN", row: row };
		}
		//生成AW
		function AW() {
			ach = "";
			//index--;
			return { type: "AW", row: row };
		}
		//是否大写数字
		function isBN(c) {
			var dict = ["一", "二", "三", "四", "五", "六", "七", "八", "九", "十", "〇"];
			for (var i in dict) {
				if (dict[i] == c) return true;
			}
			return false;
		}
		//是否小写数字
		function isSN(c) {
			var dict = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
			for (var i in dict) {
				if (dict[i] == c) return true;
			}
			return false;
		}
		//开始解析
		function s1() {
			var c = ch();
			if (c === false) return;
			else if (c == "" || !c) return;
			if (isBN(c)) s2();
			else if (isSN(c)) s5();
			else if (c == "\n") s1();
			else if (c == "#") s11();
			else s8();
		}

		function s2() {
			var c = ch();
			if (isBN(c)) s2();
			else if (c == "、") s3();
			else s8();
		}

		function s3() {
			var c = ch();
			if (c == "\n") s10();
			else s4();
		}

		//终态、题目类型
		function s4() {
			words.push(ETN());
			s8();
		}

		function s5() {
			var c = ch();
			if (isSN(c)) s5();
			if (c == "." || c == "．") s6();
			else s8();
		}

		function s6() {
			var c = ch();
			if (c == "\n") s10();
			else s7();
		}

		//终态、题目标题
		function s7() {
			words.push(EN());
			s8();
		}

		function s8() {
			var c = ch();
			if (c != "\n") s8();
			else s9();
		}

		//终态、文本
		function s9() {
			var s = T();
			if (s.data !== "") words.push(s);
			s1();
		}

		//终态、错误处理
		function s10() {
			ecompliter.error.push("基本格式错误，位置，行" + (row + 1));
			//console.log("系统错误，行" + row);
		}

		//终态、答案
		function s11() {
			words.push(AW());
			s8();
		}

		s1();
		return words;
	},


	/*
	功能：将doWordAna解析出来的元素进行进一步分析，进而得到个元素之间的关联
	参数：
		words		array		要分析的单词数组
	返回值：
		分析后得到的元素关联数组
	*/
	doSynAna: function (words) {
		var index = 0;
		var buffer = [];
		var subjects = [];
		ecompliter.error = [];
		//获取去一个单词
		function getword() {
			if (index >= words.length) return false;
			var w = words[index];
			if (w.type == "T") buffer.push(w);
			index++;
			return w;
		}

		//开始分析语法
		function s1() {
			var w = getword();
			if (w === false) return;
			if (w.type == "ETN") s2();
			else if (w.type == "EN") s4();
			else s1();
		}

		function s2() {
			buffer = [];
			var w = getword();
			if (w.type == "T") s3();
			else s8();
		}

		function s3() {
			//存入数组
			var t = buffer[0];
			subjects.push({ type: t, items: [] });
			//继续执行
			s1();
		}

		function s4() {
			buffer = [];
			var w = getword();
			if (w.type == "T") s5();
			else s8();
		}

		function s5() {
			var w = getword();
			var len = subjects.length;
			if (w === false) subjects[len - 1].items.push({ name: buffer, answer: "" });
			else if (w.type == "T") s5();
			else {
				if (len <= 0) s8();
				else {
					//保存题目
					subjects[len - 1].items.push({ name: buffer, answer: "" });
					if (w.type == "ETN") s2();
					else if (w.type == "EN") s4();
					else if (w.type == "AW") {
						buffer = [];
						s6(w.data);
					}
					else s8();
				}
			}
		}

		function s6(str) {
			var w = getword();
			if (w.type == "T") s6();
			else s7();
		}

		function s7() {
			var len = subjects.length;
			if (len <= 0) s8();
			else {
				var lindex = subjects[len - 1].items.length;
				subjects[len - 1].items[lindex - 1].answer = buffer;
				index--;
				s1();
			}
		}

		//出错
		function s8() {
			var bf = buffer[buffer.length - 1];
			ecompliter.error.push("模板格式不符合，第" + (bf.row + 1) + "行。<span style='color:#F00'>" + bf.data + "</span>");
		}

		//分析
		s1();

		return subjects;
	},


	/*
	功能：做最后的解析，将doSynAna返回的数据做最后的解析，得到更清晰的关系数组
	参数：
		subjects		array		关系数组
	返回值：
		最后解析之后得到的关系数组
	*/
	doLastAna: function (subjects) {
		var ret = [];
		for (var i in subjects) {
			var subject = subjects[i];
			var sitems = subject.items;
			var items = [];
			for (var j in sitems) {
				var aItem = sitems[j];
				var answer = "";
				var names = aItem.name;
				var data = "";
				for (var k in names) {
					var name = names[k];
					data += name.data + "\n";
				}
				for (var k in aItem.answer) {
					answer += aItem.answer[k].data + "\n";
				}
				data = data.replace(/\\#/gim, "#");
				items.push({ data: data, answer: answer });
			}
			var subjectType = subject.type.data;
			//去掉括号及括号之间的内容，还有空格
			subjectType = subjectType.replace(/[\(|（][\s\S]+[\)|）]/gim, "");
			subjectType = subjectType.replace(/\s/gim, "");
			ret.push({ type: subjectType, items: items });
		}
		return ret;
	}
};