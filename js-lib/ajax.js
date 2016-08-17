var http_request = false;

// 建立 xmlhttprequest 物件 並確認可執行性
function makeRequest() {

    http_request = false;

    if (window.XMLHttpRequest) { // Mozilla, Safari,...
        http_request = new XMLHttpRequest();
        if (http_request.overrideMimeType) {
            http_request.overrideMimeType('text/xml');
        }
    } else if (window.ActiveXObject) { // IE
		var XmlHttpVersions = new Array('MSXML2.XMLHTTP.6.0','MSXML2.XMLHTTP.5.0','MSXML2.XMLHTTP.4.0','MSXML2.XMLHTTP.3.0','MSXML2.XMLHTTP','Microsoft.XMLHTTP');
		// try every prog id until one works
		for (var i=0; i<XmlHttpVersions.length && !http_request; i++){
			try{
			// try to create XMLHttpRequest object
				http_request = new ActiveXObject(XmlHttpVersions[i]);
			}
			catch (e) {} // ignore potential error
			}
    }

    if (!http_request) {
        return false;
    }else{
		return true;
	}

}

// 執行 httprequest 並把數據傳出
function process(url, param, method,after){
	if(http_request){
		http_request.onreadystatechange = function() {
            checkstatus();
            if( after ) { after.call(); }
        };
		if(method == "GET"){
			http_request.open(method, url + param, true);
			http_request.send(null);
		}else if(method == "POST"){
			http_request.open(method, url, true);
			http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			// http_request.setRequestHeader("Content-length", param.length);
			// http_request.setRequestHeader("Connection", "close");
			http_request.send(param);
		}
	}
}

// 處理整個 ajax 呼叫過程
function call_ajax(url, param, method, id,after){
	// console.log(url, param, method, id,after);
	if(id != ""){
		ajax_id = id;

		d = document.getElementById("loading");
		m = document.getElementById("load_msg");
		i = document.getElementById("load_img");

		if(d != null && m != null && i != null){			
			i.src = "../pic/load.gif";
			m.innerHTML = " 系統初始化...";
			d.style.display = "block";	


			if(makeRequest()){

				m.innerHTML = target_msg[target_type] + request_msg[request_type];
				process(url, param, method, after);

			//	alert(url +"," + param + "," + method);

			}else{
				i.src = "../pic/error.gif";
				e.innerHTML = " 無法發出請求...";
				setTimeout(d.style.display="none", 5);
			}
		}else{
			alert("不允許外部呼叫");

		}

	}
}

function checkstatus() {
	d = document.getElementById("loading");
	m = document.getElementById("load_msg");
	i = document.getElementById("load_img");

	 if (http_request.readyState == 4){

			a = document.getElementById(ajax_id);
			//a_time = document.getElementById(ajax_id + "_t");
			


		if (http_request.status == 200) {			
			

			if(d != null && m != null && i != null){
				//alert(http_request.responseText);
				if(target_type == 0 || request_type == 4){
					a.innerHTML = http_request.responseText;
					a.style.display = "block";
					i.src = "../pic/check.gif";
					m.innerHTML = target_msg[target_type] + result_msg[request_type];

				}else{
					if(request_type == 1){		// 儲存 (新增)

						res = http_request.responseText;
						var resx = res.split("&&&");
						db_err_type = parseInt(resx[0]);

						if(isNaN(db_err_type)){
							db_err_type = 4;
							// alert(res);
						}

						if(db_err_type != 0 ){
							i.src = "../pic/error.gif";
							m.innerHTML = target_msg[target_type] + db_err_msg[db_err_type];
						}else{
							i.src = "../pic/check.gif";
							m.innerHTML = target_msg[target_type] + result_msg[request_type];

							if(renew_div_id != ""){
								update_div(renew_div_id, renew_path ,target_type);
								renew_div_id = "";
								
								if(target_type >= 12 && target_type <= 15 || target_type == 42){
									clear_add(target_type);
								}else if (target_type == 18 || target_type == 19){
									clear_add2(target_type);								
								}
							}
						}



					}else if(request_type == 2){		// 更新
						res = http_request.responseText;
						var resx = res.split("&&&");
						 db_err_type = parseInt(resx[0]);
						//alert(db_err_type);
						if(isNaN(db_err_type)){
							db_err_type = 4;
							alert(res);
						}

						if(db_err_type != 0 ){
							i.src = "../pic/error.gif";
							m.innerHTML = target_msg[target_type] + db_err_msg[db_err_type];
						}else{
							i.src = "../pic/check.gif";
							m.innerHTML = target_msg[target_type] + result_msg[request_type];
							
							if(resx[1] != ""){
								//	alert(ajax_id_related);
								a.value = resx[1];			// 更新原本變數 (反映是否成功)

								//alert(ajax_id_related);
								if(ajax_id_related != ""){	// 如果有相關欄位要更新，則一起更新
									a2 = document.getElementById(ajax_id_related);
									if(a2 != null){
										a2.innerHTML = resx[1];
										
									}
								}
							}else{
								// toggle image
								if(target_type == 10 || target_type == 11 || target_type == 21 || target_type == 27 ||target_type == 28 ||target_type == 31 || target_type == 36 || target_type == 37 || target_type == 47 ){		
									if(renew_img_src != ""){
										a.src = renew_img_src;
									}
								}
							}

							if(renew_div_id != ""){
								update_div(renew_div_id, renew_path ,target_type);
								renew_div_id = "";
								
								if(target_type == 17 ){
									clear_add(target_type);
								}else if (target_type == 19){
									clear_add2(target_type);
								}else if(target_type == 42){
									fin_add();
								}									
							}else{
								if(target_type == 41){
									fin_edit();
								}								
							

							}
							
							// 更新時間
							if(time_tag_id != ""){
								a_time = document.getElementById(time_tag_id);
								if(a_time != null){
									a_time.innerHTML = resx[2];
								}
							}
						}

					}else if(request_type == 3){		// 刪除

						res = http_request.responseText;
						var resx = res.split("&&&");
						 db_err_type = parseInt(resx[0]);

						if(isNaN(db_err_type)){
							db_err_type = 4;
							alert(res);
						}

						if(db_err_type != 0 ){
							i.src = "../pic/error.gif";
							m.innerHTML = target_msg[target_type] + db_err_msg[db_err_type];
							
							if(resx[1] != ""){
								alert(resx[1]);
							}
						}else{
							i.src = "../pic/check.gif";
							m.innerHTML = target_msg[target_type] + result_msg[request_type];
							if(renew_div_id != ""){
								update_div(renew_div_id, renew_path ,target_type);
								renew_div_id = "";
							}
							if(target_type == 43){
								fin_edit();
							}								
						}


					}

				}


				clearTimeout(timer);

				if(request_type == 0){
					timer = setTimeout("hide_div(d)", 500);
				}else if(db_err_type != 0){
					d.style.display = "block";
				}else{
					if(request_type == 2 || request_type == 4 ){
						timer = setTimeout("hide_div(d)", 2000);
					}else{
						d.style.display = "block";
					}
				}


			}
	

		} else {
			if(d != null && m != null && i != null){

				m.innerHTML = " 伺服器錯誤, 請稍後再試.";
				i.src = "../pic/error.gif";
				d.style.display="block";
			}
		}

	}
}



