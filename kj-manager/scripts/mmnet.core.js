/*-------------------------------------------------------------\
 * MMNet Web Console System
 - falsewinds(kj)@mmnet.iis 2014.10.24 via jQuery v.2.1.1
\-------------------------------------------------------------*/
window.MMNet = {

"viewStatus": new Object(),
"_fix": function(str,prefix,suffix) {
    if( str == null ) { return ""; }
    if( prefix == null ) { prefix = ""; }
    if( suffix == null ) { suffix = ""; }
    var result = str;
    if( result.substring(0,prefix.length) != prefix ) { result = prefix + result; }
    if( result.substring(result.length-suffix.length) != suffix ) { result += suffix; }
    return result;
},
"view": function(area,fragment,data,clear) {
    var pagetags = new Array();
    switch(area) {
    case "menu":
    case "menu-container":
    case "#menu-container":
    case "nav#menu-container":
        area = "nav#menu-container";
        fragment = MMNet._fix(fragment,"menu_",".php");
        pagetags.push("load-page");
        pagetags.push("save-menu");
        pagetags.push("stop-reload");
        break;
    case "main":
    case "content":
    case "main-content":
    case "#main-content":
    case "div#main-content":
    case "main-container":
    case "content-container":
    case null:
        area = "div#main-content";
        fragment = MMNet._fix(fragment,"page_",".php");
        pagetags.push("fold-table");
        pagetags.push("save-page");
        pagetags.push("page-class");
        break;
    default:
        break;
    }
    var container = $(area);
    if( container.length == 0 ) { return; }
    if( fragment.length == 0 ) { return; }

    if( MMNet.Controller.Actived ) {
        if( !confirm("還有正在輸入的資料，確定放棄編輯？") ) { return; }
        MMNet.Controller.close();
    }

    if( this.viewStatus[area] == fragment ) {
        if( pagetags.indexOf("stop-reload") >= 0 ) { return; }
    } else { pagetags.push("top"); }

    // Load Client Setting (SessionStorage)
    if( data == null ) { data = new Object(); }
    if( data.tagName == "FORM" ) {
        var form = $(data).serializeArray();
        data = new Object();
        $.each(form,function(n,input) {
            var name = input.name.replace("[]","");
            switch( typeof data[name] ) {
            case "null":
            case "undefined":
                data[name] = input.value;
                break;
            case "object":
                data[name].push(input.value);
                break;
            default:
                var temp = data[name];
                data[name] = new Array();
                data[name].push(temp);
                data[name].push(input.value);
                break;
            }
        });
    }
    var memory = sessionStorage.getItem(fragment+"_jsondata");
    var mem_json = (memory==null) ? new Object() : JSON.parse(memory);
    var viewdata = (clear===true) ? data : $.extend(mem_json,data);

    $.ajax({
        "type": "post",
        "url": fragment,
        "async": true,
        "data": $.extend(viewdata,{ "ajax": "index.php" }),
        "dataType": "html",
        "success": function(html) {
            try {
                var json = JSON.parse(html);
                console.log(json);
                if( json.error == 401 ) {
                    //location.reload();
                }
            } catch(e) {
                MMNet.viewStatus[area] = fragment;
                container.html(html);
                $.each(pagetags,function(n,tag) {
                    switch (tag) {
                    case "top":
                    case "scroll-top":
                        MMNet.Page.scrollTo(0);
                        break;
                    case "page-class":
                        container.removeClass();
                        var pagename = fragment.match(/^.*_(.*)\.php*$/);
                        container.addClass(pagename[1]);
                        break;
                    case "fold":
                    case "fold-table":
                        $("table.foldable > caption").click(function(e) {
                            var table = $(e.target).parents("table"), num = -1;
                            $("table.foldable").each(function(n,tab) {
                                if( tab != table[0] ) { return; }
                                num = n;
                            });
                            if( table.hasClass("-fold") ) {
                                if( table.hasClass("-unique") ) {
                                    $("table.foldable").addClass("-fold");
                                    MMNet.Page.save("unfolded",new Array());
                                }
                                table.removeClass("-fold");
                                if( num >= 0 ) { MMNet.Page.save("unfolded",num,"insert"); }
                            } else {
                                table.addClass("-fold");
                                if( num >= 0 ) { MMNet.Page.save("unfolded",num,"remove"); }
                            }
                        });
                        if( viewdata.unfolded ) {
                            $("table.foldable").each(function(n,tab) {
                                if( viewdata.unfolded.indexOf(n) < 0 ) { return; }
                                $(tab).removeClass("-fold");
                            });
                        }
                        break;
                    case "save-menu":
                        localStorage.setItem("menu",MMNet.viewStatus["nav#menu-container"]);
                        break;
                    case "save-page":
                        var menu = MMNet.viewStatus["nav#menu-container"];
                        localStorage.setItem(menu,fragment);
                        break;
                    case "load-page":
                        var menu = MMNet.viewStatus["nav#menu-container"];
                        var def_main = localStorage.getItem(menu);
                        MMNet.view("main",def_main);
                        break;
                    default:
                        break;
                    }
                });
            }
        },
        "error": function(xhr,status,error) {
            container.html("<h1>Page Not Found</h1>");
        }
    });
},

"link": function(href,file) {
    $.ajax({
        "type": "post",
        "url": href,
        "async": true,
        "dataType": "html",
        "success": function(html) { window.open(file,"download"); },
        "error": function(xhr,status,msg) { console.log(status,msg); }
    });
},

/*-------------------------------------------------------------\
 * MMNet Web Console System
 * MMNet Page Control
 - scrollTo(
\-------------------------------------------------------------*/
"Page": {
    "scrollTo": function(target) {
        if( typeof target == "string" ) { target = $(target).offset().top - 50; }
        $("#main-content").animate( {"scrollTop":target} );
    },
    "reload": function(def) {
        var menu = localStorage.getItem("menu");
        if( menu == null ) { menu = def; }
        MMNet.view("menu",menu);
    },
    "set": function(pagename,data) {
        var memory = sessionStorage.getItem(pagename+"_jsondata");
        var mem_json = (memory==null) ? new Object() : JSON.parse(memory);
        for( key in data ) { mem_json[key] = data[key]; }
        sessionStorage.setItem(pagename+"_jsondata",JSON.stringify(mem_json));
    },
    "get": function(pagename) {
        if( pagename == null ) { pagename = MMNet.viewStatus["div#main-content"]; }
        var memory = sessionStorage.getItem(pagename+"_jsondata");
        return (memory==null) ? new Object() : JSON.parse(memory);
    },
    "save": function(key,value,type) {
        var pagename = MMNet.viewStatus["div#main-content"];
        var memory = sessionStorage.getItem(pagename+"_jsondata");
        var mem_json = (memory==null) ? new Object() : JSON.parse(memory);
        switch(type) {
        case "insert":
            if( mem_json[key] == null ) { mem_json[key] = new Array(); }
            mem_json[key].push(value);
            break;
        case "remove":
            if( mem_json[key] == null ) { break; }
            var index = mem_json[key].indexOf(value);
            if( index >= 0 ) { mem_json[key].splice(index,1); }
            break;
        case "replace":
        default:
            mem_json[key] = value;
            break;
        }
        sessionStorage.setItem(pagename+"_jsondata",JSON.stringify(mem_json));
    }
},

/*-------------------------------------------------------------\
 * MMNet Web Console System
 * MMNet Controller: Bottom Form
\-------------------------------------------------------------*/
"Controller": {
    "Actived": false,
    "load": function(page,data) {
        if( this.Actived ) { return; }
        var url = ( page.indexOf(":") > 0 ) ? page.replace(":","/") : "patterns/"+page+".php";

        $.ajax({
            "type": "post",
            "url": url,
            "async": true,
            "data": data,
            "dataType": "html",
            "success": function(html) {
                $("#footer-controller").html(html);
                $("#footer-controller").addClass("show");
                $("#footer-controller > form input:first").focus();
                MMNet.Controller.Actived = true;
            },
            "error": function(xhr,status,error) {
                $("#footer-controller").removeClass("show");
                console.log(status,error);
                //this.Actived = true;
            }
        });
    },
    "submit": function(formdata) {
        if( !this.Actived ) { return; }

        var form = $("#footer-controller > form");
        var requireds = form.find("*[required]");
        var valid = true;
        requireds.each(function(num,required) {
            var this_valid = false, v = required.value;
            switch( required.tagName ) {
            case "INPUT":
                if( v != "" ) { this_valid = true; }
                break;
            case "SELECT":
                if( v != 0 && v != "" ) { this_valid = true; }
                break;
            case "TEXTAREA":
                if( v != "" ) { this_valid = true; }
                break;
            default:
                break;
            }
            var td = $(required).parents("td");
            if( !this_valid ) {
                td.addClass("invalid");
                required.focus();
            } else { td.removeClass("invalid"); }
            valid = valid && this_valid;
        });
        if( !valid ) { return; }
        $("#footer-controller").addClass("submiting");

        var ajax_data = {
            "type": form.attr("method"),
            "url": form.attr("action"),
            "async": true,
            "data": form.serialize(),
            "dataType": "json",
            "success": function(json) {
                console.log(json);
                MMNet.Controller.close();
                if( json.reload ) { MMNet.view("main",json.reload); }
                else if( json.page ) {
                    var dat = { "id": json.page };
                    if( json.section ) { dat.scroll = json.section; }
                    MMNet.view("main","menupage",dat);
                }
                $("#footer-controller").removeClass("show");
                $("#footer-controller").removeClass("submiting");
            },
            "error": function(xhr,status,error) {
                console.log(status,error);
            }
        };

        if( formdata ) {
            ajax_data.data = new FormData();
            $.each(form.serializeArray(),function(i,field) {
                ajax_data.data.append(field.name,field.value);
            });
            form.find("input[type=\"file\"]").each(function(count,input) {
                var n = input.name, t = input.type;
                if( n == null ) { return; }
                $.each(input.files,function(i,file) {
                    ajax_data.data.append(n+"["+i+"]",file);
                });
            });
            ajax_data.contentType = false;
            ajax_data.processData = false;
        }

        $.ajax(ajax_data);
    },
    "close": function() {
        $("#footer-controller").empty();
        $("#footer-controller").removeClass("show");
        $("#footer-controller").removeClass("submiting");
        this.Actived = false;
    }
},

/*-------------------------------------------------------------\
 * MMNet Web Console System
 * MMNet Form Control
 - extract
\-------------------------------------------------------------*/
"Form": {
    "extract": function(container) {
        var data = new Object();
        $(container).children("input").each(function(n,input) {
            if( input.name == null ) { return; }
            if( input.name == "" ) { return; }
            var type = input.getAttribute("type");
            switch(type.toLowerCase()) {
            case "radio":
                break;
            case "checkbox":
                break;
            default:
                data[input.name] = input.value;
                break;
            }
        });
        $(container).children("select").each(function(n,input) {
            if( input.name == null ) { return; }
            if( input.name == "" ) { return; }
            var type = input.getAttribute("type");
            switch(type.toLowerCase()) {
            case "radio":
                break;
            case "checkbox":
                break;
            default:
                data[input.name] = input.value;
                break;
            }
        });
        $(container).children("select").each(function(n,select) {
            data[select.name] = select.value;
        });
        $(container).children("textarea").each(function(n,textarea) {
            data[textarea.name] = textarea.textContent || textarea.innerText;
        });
    }
},

/*-------------------------------------------------------------\
 * MMNet Web Console System
 * MMNet Table Form Control
 - 
\-------------------------------------------------------------*/
"Table": {
    "insert": function(trigger,type,prefix) {
        if( typeof type != "string" ) { return; }
        if( type.length == 0 ) { type = "ROW"; }
        var table = $(trigger).parents("table.define-list");
        var tbody = table.children("tbody:last-child");
        var tr = null;
        switch(type.toLowerCase()) {
        case "row":
            var key = null, heade = "", title = "", required = false;
            switch( trigger.tagName ) {
            case "SELECT":
                var option = $(trigger).children("option:selected");
                $(trigger).children("option:first-child").attr("selected",true);
                key = option.attr("value");
                head = option.text();
                if( option.attr("title") ) { title = option.attr("title"); }
                if( option.attr("data-require") ) { required = true; }
                break;
            default:
                break;
            }
            if( key == null ) { break; }

            var name = prefix + "[" + key + "]";
            // Unique Row Check
            var types = $("*[name^=\""+prefix+"\"]"), names = new Array();
            types.each(function(num,ele) { names.push(ele.name); });
            if( names.indexOf(name) >= 0 ) { return; }

            tr = $("<tr>"
                + "<th>"+head+"</th>"
                + "<td"+((title.length>0)?" title=\""+title+"\"":"")+">"
                +   "<input type=\"text\" name=\""+name+"\""+(required?" required":"")+" />"
                + "</td>"
                + "</tr>");
            break;
        case "pair":
            var pair_order = type + "[" + tbody.children("tr").length + "]";
            tr = $("<tr>"
                + "<th><input type=\"text\" name=\""+pair_order+"[key]\" /></th>"
                + "<td><input type=\"text\" name=\""+pair_order+"[val]\" /></td>"
                + "</tr>");
            break;
        default:
            break;
        }
        if( tr == null ) { return; }
        tbody.append(tr);
    },
    "submit": function(tr,act) {
        var method = tr.getAttribute("method") || "get";
        var action = tr.getAttribute("action") || act;
        if( action == null || action == "" ) { return; }
        var data = MMNet.Form.extract(tr);
        if( method.toLowerCase() == "get" ) {
            action += "&" + MMNet.Form.serialize(data);
            data = null;
        }
        $.ajax({
            "type": method,
            "url": action,
            "async": true,
            "data": data,
            "dataType": "json",
            "success": function(json) {
                console.log(json);
                if( json.reload ) {
                    MMNet.configure(json.reload);
                }
            },
            "error": function(xhr,status,error) {
                console.log(status,error);
            }
        });
    }
},

/*-------------------------------------------------------------\
 * MMNet Web Console System
 * DragDrop List : controll DragDrop Element
\-------------------------------------------------------------*/
"DragDropList": {
    "initialize": function(e,table,options) {
        var option = {
            "axis": "y",
            "cursor": "move",
            "delay": 10,
            "placeholder": "dragdrop-placeholder",
            "update": function(event,ui) {
                if( ui.item.length <= 0 ) { return; }
                var target = ui.item;
                var data = {
                    "table": table,
                    "id": target.attr("data-dbid")
                };

                var tagn = target.tagName;
                var next = target.next(tagn);
                if( next.length > 0 ) {
                    data["before"] = next.attr("data-dbid");
                } else {
                    var prev = target.prev(tagn);
                    if( prev.length > 0 ) {
                        data["after"] = prev.attr("data-dbid");
                    } else {
                        console.log("Illegal move");
                        data = null;
                    }
                }

                if( data == null ) { return; }
                $.ajax({
                    "type": "post",
                    "url": "act/line-up.php",
                    "async": true,
                    "data": data,
                    "dataType": "json",
                    "success": function(json) { 
                        if( json.error ) { console.log(json.message); }
                    },
                    "error": function(xhr,status,error) {
                        console.log(status,error);
                    }
                });
            }
        };
        for( var k in options ) {
            if( typeof option[k] == "undefined" ) {
                option[k] = options[k];
            }
        }
        $(e).sortable(option);
    }
},

};