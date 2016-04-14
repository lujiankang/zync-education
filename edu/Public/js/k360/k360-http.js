window.k360_http = {
	//基本操作
	base: {
		//获取文本输入框内容
		getTextInputs: function (form) {
			var inputs = form.getElementsByTagName("input");
			var ret = [];
			for (var i = 0; i < inputs.length; i++) {
				switch (inputs[i].type) {
					case "file":
					case "button":
					case "submit":
					case "reset":
					case "radio":
					case "checkbox":
					case "image":
						break;
					default:
						if (inputs[i].name == "" || !inputs[i].name)
							break;
						ret.push([inputs[i].name,  inputs[i].value]);
				}
			}
			return ret;
		},
		//获取文件输入框内容
		getFileInputs: function (form) {
			var elems = form.querySelectorAll("input[type='file']");
			var ret = [];
			for (var i = 0; i < elems.length; i++) {
				var elem = elems[i];
				var name = elem.name;
				if (name == "" || !name)
					continue;
				var files = elem.files;
				for (var j = 0; j < files.length; j++) {
					var file = files[j];
					ret.push([name, file]);
				}
			}
			return ret;
		},
		//获取单选框内容
		getRadios: function (form) {
			var radios = form.querySelectorAll("input[type='radio']:checked");
			var ret = [];
			for (var i = 0; i < radios.length; i++) {
				var radio = radios[i];
				var name = radio.name;
				if (name == "" || !name)
					continue;
				ret.push([name, radio.value]);
			}
			return ret;
		},
		//获取复选框内容
		getCheckboxes: function (form) {
			var checkboxes = form.querySelectorAll("input[type='checkbox']:checked");
			var ret = [];
			for (var i = 0; i < checkboxes.length; i++) {
				var checkbox = checkboxes[i];
				var name = checkbox.name;
				if (name == "" || !name)
					continue;
				ret.push([name, checkbox.value]);
			}
			return ret;
		},
		//获取文本域内容
		getTextareas: function (form) {
			var textareas = form.querySelectorAll("textarea");
			var ret = [];
			for (var i = 0; i < textareas.length; i++) {
				var textarea = textareas[i];
				var name = textarea.name;
				if (name == "" || !name)
					continue;
				ret.push([name, textarea.value]);
			}
			return ret;
		},
		//获取下拉列表内容
		getSelects: function (form) {
			var selects = form.querySelectorAll("select");
			var ret = [];
			for (var i = 0; i < selects.length; i++) {
				var select = selects[i];
				var name = select.name;
				if (name == "" || !name)
					continue;
				var options = select.options;
				for (var j = 0; j < options.length; j++) {
					var option = options[j];
					if (option.selected === false)
						continue;
					ret.push([name, option.value]);
				}
			}
			return ret;
		}
	},

	//表单数据操作
	formdata: {
		create: function () {
			var obj = {
				data: null,
				isFormData: true
			};
			try{
				obj.data = new FormData();
				obj.isFormData = true;
			} catch (err) {
				obj.data = [];
				obj.isFormData = false;
			}
			
			obj.append = function (name, value) {
				if (obj.isFormData) {
					obj.data.append(name, value);
					return;
				}
				//屏蔽文件输入框
				if (typeof (value) != "string")
					return;
				obj.data.push(escape(name + "=" + value));
			}

			obj.getdata = function () {
				if (obj.isFormData)
					return obj.data;
				return obj.data.join("&");
			}

			obj.getIsFormData = function(){
				return obj.isFormData;
			}

			return obj;
		}
	},

	/*
	功能：判断浏览器是否支持文件上传
	返回值：
		true		支持文件上传
		false		不支持文件上传
	*/
	canUpload:function(){
		try{
			new FormData();
			return true;
		}catch(error){
			return false;
		}
	},

	/*
	功能：创建k360_http对象
	返回值：
		创建好的HTTP对象
	*/
	create: function () {
		var obj = null;
		obj = {
			//配置
			conf: {
				method: "post",			//提交方式
				url: "",				//目的地址
				isAsync: true,			//是否异步提交
				timeout: 0,				//超时时间
				useold: false,			//是否使用旧的ajax
				isAbord:false,			//是否终端
			},
			//数据
			data: k360_http.formdata.create(),
			//事件
			event: {
				onSuccess: function (data, xhr) { },
				onError: function (xhr, status, statusText) { },
				onProgress: function (loaded, total) { },
				onTimeout: function (xhr) { },
				onAbort: function (xhr) { },
				onStart: function (xhr) { }
			},
			//请求对象
			xhr: null
		};
		

		/*
		功能：添加一个表单的数据
		参数：
			selector	String|Object	表单选择器
		说明:
			表单选择器可以有下列几种方式
			1、直接出入选择器，如："#myForm"
			2、传入HTMLFormElement，如：document.getElementById("myForm")，或document.myForm，等等
			3、出入jQuery选择器，如：$("#myForm")
		返回值：
			当前对象，可以连贯操作
		*/
		obj.addForm = function (selector) {
			//取得表单
			var form = null;
			if (typeof(selector) == "string")
				form = document.querySelectorAll(selector)[0];
			else if (selector instanceof HTMLFormElement)
				form = selector;
			else if ((typeof ($) == "function") && selector instanceof $)
				form = selector[0];
			//获取表单属性并添加属性
			var action = form.action;
			var method = form.method;
			action && (obj.conf.url = action);
			method && (obj.conf.method = method);
			//取得表单数据
			var datas = [];
			for (var i in k360_http.base) {
				//get方式提交忽略文件，或不能上传文件
				if (i == "getFileInputs" && (method.toUpperCase() == "GET" || obj.conf.useold == true))
					continue;
				var func = k360_http.base[i];
				datas = datas.concat(func(form));
			}
			//添加数据到表单数据
			for (var i in datas) {
				var name = datas[i][0];
				var value = datas[i][1];
				obj.data.append(name, value);
				addGetData(name, value);
			}
			return obj;
		}

		/*
		功能：添加数据
		参数：
			data		Object		要添加的数据
		说明：
			要添加的数据需以键值对的形式传如，且值必须为字符串，整数，浮点数或布尔值，例如：{"name":"Zhang San", "age":21}
			如果传递的键对应多个值，建议使用[]，如：{"user[]":"Zhang San", "user[]":"Li Si", "grade":"Grade 2015"}
		返回值：
			当前对象
		*/
		obj.addData = function (datas) {
			for (var name in datas) {
				var aData = datas[name];
				obj.addOne(name, aData);
			}
			return obj;
		}

		/*
		功能：添加一条数据
		参数：
			name		String						数据的名称
			data		String|Int|Bool|Array		数据内容
		返回值：
			当前对象
		*/
		obj.addOne = function (name, data) {
			var buffer = [data];
			if (data instanceof Array) {
				buffer = data;
				if (name.substr(name.length - 1) != "[]") {
					name += "[]";
				}
			}
			for (var i in buffer) {
				obj.data.append(name, buffer[i]);
				addGetData(name, buffer[i]);
			}
			return obj;
		}

		/*
		功能：添加文件数据
		参数：
			name		String							数据的名称
			selector	String|HTMLInputElement|jQuery	文件输入框的选择器，可以是选择器，HTMLInputElement对象，或者jQuery对象
		说明：
			如果文件输入框中已经有name属性，则可以不用传name参数，如：xxx.addFile(myForm.myFile)
		返回值：
			当前对象
		*/
		obj.addFile = function (name, selector) {
			//参数读取
			if (!name)
				return obj;
			var sel = selector ? selector : name;
			var name = selector ? name : null;
			//获取文件输入框
			var dom = null;
			if (typeof (sel) == "string") {
				dom = document.querySelectorAll(sel)[0];
			} else if (sel instanceof HTMLInputElement) {
				dom = sel;
			} else if (typeof ($) == "function" && sel instanceof $) {
				if (sel.length <= 0) return obj;
				dom = sel[0];
			} else {
				return obj;
			}
			if (dom.type != "file")
				return obj;
			//取输入框的值
			var files = dom.files;
			name = name ? name : dom.name;
			if (!name || name == "") {
				return obj;
			}
			for (var i = 0; i < files.length; i++) {
				obj.data.append(name, files[i]);
			}
			return obj;
		}

		/*
		功能：设置提交的目的地址
		参数：
			url		String		目的地址
		返回值：
			当前对象
		*/
		obj.setUrl = function (url) {
			obj.conf.url = url;
			return obj;
		}

		/*
		功能：设置提交方式
		参数：
			method	String		提交方式，get或post
		返回值：当前对象
		*/
		obj.setMethod = function (method) {
			obj.conf.method = method;
			return obj;
		}

		/*
		功能：设置是否异步提交
		参数：
			isAsync		Bool	是否异步提交，如果是true则异步提交，否则同步提交
		返回值：
			当前对象
		*/
		obj.setAsync = function (isAsync) {
			obj.conf.isAsync = isAsync;
			return obj;
		}

		/*
		功能：设置超时时间
		参数：
			timeout		Int		超时时间，单位（ms）
		返回值：
			当前对象
		*/
		obj.setTimeout = function (timeout) {
			obj.conf.timeout = timeout;
			return obj;
		}

		/*
		功能：添加提交成功处理事件
		参数：
			onSuccess		Function(data, xhr)		提交成功回调函数
		返回值：
			当前对象
		*/
		obj.onsuccess = function (onSuccess) {
			obj.event.onSuccess = onSuccess;
			return obj;
		}

		/*
		功能：添加提交失败处理事件
		参数：
			onError		Function(xhr, status, statusText)	提交失败回调函数
		返回值：
			当前对象
		*/
		obj.onerror = function (onError) {
			obj.event.onError = onError;
			return obj;
		}

		/*
		功能：添加提交进度处理事件
		参数：
			onProgress	Function(loaded, total)		提交进度回调函数
		返回值：
			当前对象
		*/
		obj.onprogress = function (onProgress) {
			obj.event.onProgress = onProgress;
			return obj;
		}

		/*
		功能：添加超时处理事件
		参数：
			onTimeout	Function(xhr)		提交超时回调函数
		返回值：
			当前对象
		*/
		obj.ontimeout = function (onTimeout) {
			obj.event.onTimeout = onTimeout;
			return obj;
		}

		/*
		功能：添加中断处理时间
		参数：
			onAbort		Function(xhr)		提交中断处理事件
		返回值：
			当前对象
		*/
		obj.onabort = function (onAbort) {
			obj.event.onAbort = onabort;
			return obj;
		}

		/*
		功能：添加提交开始处理事件
		参数：
			onStart		Function(xhr)		提交开始处理事件
		返回值：
			当前对象
		*/
		obj.onstart = function (onStart) {
			obj.event.onStart = onStart;
			return obj;
		}

		/*
		功能：提交数据
		返回值：
			当前对象
		*/
		obj.send = function () {
			try{
				var xhr = new XMLHttpRequest();
				obj.xhr = xhr;
				var xhr = new XMLHttpRequest();
				obj.xhr = xhr;
			}catch(err){
				throw err;
			};
			//状态改变事件
			(obj.data.getIsFormData()) || (xhr.onreadystatechange = function () {
				if (xhr.readyState == 4) {
					if (xhr.status == 200) {
						//成功
						var resp = xhr.responseText;
						var data = null;
						try {
							data = JSON.parse(resp);
						} catch (error) {
							//JSON转换失败
							(obj.event.onError instanceof Function) && obj.event.onError(xhr, xhr.status, error.name);
							return;
						}
						//成功
						(obj.event.onSuccess instanceof Function) && obj.event.onSuccess(data, xhr); throw error;
					} else {
						//出错
						if (!obj.conf.isAbord) {
							(obj.event.onError instanceof Function) && obj.event.onError(xhr, xhr.status, xhr.statusText);
						}
					}
				}
			});
			//进度事件
			(obj.data.getIsFormData()) && (xhr.upload.onprogress = function (evt) {
				(obj.event.onProgress instanceof Function) && obj.event.onProgress(evt.loaded, evt.total);
			});
			//超时事件
			(obj.data.getIsFormData()) && (xhr.ontimeout = function () {
				(obj.event.onTimeout instanceof Function) && obj.event.onTimeout(xhr);
			});
			//中断事件
			(obj.data.getIsFormData()) && (xhr.onabort = function (evt) {
				(obj.event.onAbort instanceof Function) && obj.event.onAbort(xhr);
			});
			//开始事件
			(obj.data.getIsFormData()) && (xhr.onloadstart = function (evt) {
				(obj.event.onStart instanceof Function) && obj.event.onStart(xhr);
			});
			//出错事件
			(obj.data.getIsFormData()) && (xhr.onerror = function (evt) {
				(obj.event.onError instanceof Function) && obj.event.onError(xhr, xhr.status, xhr.statusText);
			});
			//完成事件
			(obj.data.getIsFormData()) && (xhr.onloadend = function (evt) {
				if (xhr.readyState == 4 && xhr.status == 200) {
					var resp = xhr.responseText;
					var data = null;
					try {
						data = JSON.parse(resp);
					} catch (error) {
						(obj.event.onError instanceof Function) && obj.event.onError(xhr, xhr.status, error.name);
						return;
					}
					(obj.event.onSuccess instanceof Function) && obj.event.onSuccess(data, xhr);
				} else {
					if (!obj.conf.isAbord) {
						(obj.event.onError instanceof Function) && obj.event.onError(xhr, evt.target.status, evt.target.statusText);
					}
				}
			});
			try{
				//提交
				var url = obj.conf.url;
				var method = obj.conf.method.toUpperCase();
				if (method == "GET")
					url += "?" + getGetData();
				xhr.open(method, url, obj.conf.isAsync);
				xhr.timeout = obj.conf.timeout;
				(method=="GET") ? xhr.send() : xhr.send(obj.data.getdata());
			} catch (error) {
				throw error;
			}
			return obj;
		}

		/*
		功能：中断当前的提交操作
		返回值：
			当前对象
		*/
		obj.abort = function () {
			obj.conf.isAbord = true;
			obj.xhr.abort();
			return obj;
		}

		/*
		功能：删除对象
		*/
		obj.distroy = function () {
			obj = null;
		}
		

		function addGetData(name, value) {
			obj.getdata || (obj.getdata = []);
			obj.getdata.push(name + "=" + encodeURI(value));
		}

		function getGetData() {
			var buffer = "";
			for (var i in obj.getdata) {
				(i == 0) ? 
				(buffer += obj.getdata[i]) :
				(buffer += "&" + obj.getdata[i]);
			}
			return buffer;
		}

		return obj;
	}
}