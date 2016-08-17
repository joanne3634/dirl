$.extend(MMNet,{

"scrollTo": function(dat) {
    if( typeof dat == "string" ) { dat = $(dat).offset().top - 50; }
    $("#main-content").animate( {"scrollTop":dat} );
},

"configure": function(page,data) {
    if( page == null || page == "" ) { page = "general"; }
    if( MMNet.Controller.Actived ) {
        if( !confirm("還有正在輸入的資料，確定放棄編輯？") ) { return; }
        MMNet.Controller.close();
    }

    if( data == null ) { data = new Object(); }
    

    data["ajax"] = 'index.php';
    $.ajax({
        "type": "post",
        "url": "page_"+page+".php",
        "async": true,
        "data": data,
        "dataType": "html",
        "success": function(html) {
            try {
                var json = JSON.parse(html);
                console.log(json);
                if( json.error == 401 ) { location.reload(); }
            } catch(e) {
                $("#main-content").html(html);
                MMNet.scrollTo(0);
            }
            $("table.foldable > caption").click(function(e) {
                var table = $(e.target).parents("table");
                if( table.hasClass("-fold") ) {
                    if( table.hasClass("-unique") ) { $("table.foldable").addClass("-fold"); }
                    table.removeClass("-fold");
                } else { table.addClass("-fold"); }
            });
        }
    });
},

"disable": function(elem,swc) {
    if( swc ) {
        $(elem).prop("on",elem.hasAttribute("checked"));
        elem.removeAttribute("checked");
        elem.setAttribute("disabled","disabled");
    } else {
        elem.removeAttribute("disabled");
        if( $(elem).prop("on") ) { elem.setAttribute("checked","checked"); }
    }
},

/*"loadOptions": function(target,type,data) {
    $.ajax({
        "type": "post",
        "url": "patterns/"+type+".options.php",
        "async": true,
        "data": data,
        "dataType": "html",
        "success": function(html) { target.html(html); }
    });
},*/

"insertRow": function(trigger,type) {
    if( trigger.value == null || trigger.value == "" ) { return; }
    var option = $(trigger).children("option:selected");
    $(trigger).children("option:first-child").attr("selected",true);
    var table = $(trigger).parents("table.define-list");

    var key = option.attr("value"), name = type + "[" + key + "]";
    // Unique Row Check
    var types = $("*[name^=\""+type+"\"]"), names = new Array();
    types.each(function(num,ele) { names.push(ele.name); });
    if( names.indexOf(name) >= 0 ) { return; }
    // Append Input Row
    var tbody = table.children("tbody:last-child");
    var input = "input type=\"text\" name=\""+name+"\"";
    if( option.attr("data-require") ) { input += " required"; }
    var td_title = ( option.attr("title") != "" ) ? " title=\""+option.attr("title")+"\"" : "";
    tbody.append("<tr><th>"+option.text()+"</th><td"+td_title+"><"+input+" /></td></tr>");
},

"insertKeyValuePair": function(trigger,type) {
    var table = $(trigger).parents("table.define-list");
    var tbody = table.children("tbody:last-child");
    var prefix = type + "[" + tbody.children("tr").length + "]";
    tbody.append("<tr><th><input type=\"text\" name=\""+prefix+"[key]\" /></th><td><input type=\"text\" name=\""+prefix+"[val]\" /></td></tr>");
},

"submitFromTableRow": function(tr,where) {
    var method = tr.getAttribute("method") || "get";
    var action = tr.getAttribute("action") || where;
    if( action == null || action == "" ) {
        console.log("No where");
        return;
    }

    var data = new Object();
    for(var i=0;i<tr.childNodes.length;i++) {
        var td = tr.childNodes[i];
        for(var j=0;j<td.childNodes.length;j++) {
            var child = td.childNodes[j];
            switch(child.tagName) {
            case "INPUT":
                var type = child.getAttribute("type");
                switch(type) {
                case "radio":
                    break;
                case "checkbox":
                    break;
                default:
                    data[child.name] = child.value;
                    break;
                }
                break;
            case "SELECT":
                data[child.name] = child.value;
                break;
            case "TEXTAREA":
                data[child.name] = child.textContent || child.innerText;
                break;
            default:
                continue;
            }
        }
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
},

"alter": function(table,id,field,value,options) {
    if( !options ) { options = new Object(); }
    if( options.confirm ) {
        console.log("進行確認，是否將"+table+"#"+id+"的"+field+"改為「"+value+"」。");
        if( !confirm(options.confirm) ) { return; }
    }

    $.ajax({
        "type": "post",
        "url": "act/update.php",
        "async": true,
        "data": {
            "table": table,
            "id": id,
            "field": field,
            "value": value
        },
        "dataType": "json",
        "success": function(json) {
            console.log(json);
            if( options.redirect ) {
                //MMNet.configure(options.redirect);
                MMNet.view("main",options.redirect,json);
            }
            if( options.reload ) { location.reload(); }
            if( options.target ) {
                var target = $(options.target);
                if( options.turn ) {
                    target.removeClass("-"+options.turn);
                    target.addClass("-"+value);
                    var event = options.event || "onclick";
                    target.attr(event,"MMNet.alter('"+table+"','"+id+"','"+field+"','"+options.turn+"',{'target':this,'turn':'"+value+"'});");
                }
            }
        },
        "error": function(xhr,status,error) {
            console.log(status,error);
        }
    });
},

"upload": function(folder,selector,target,option) {
    var fd = new FormData();
    fd.append("folder_id",folder);
    $.each(
        selector.files,
        function(count,file) {
            fd.append("uploads["+count+"]",file);
        }
    );
    if( option != null ) {
        for( key in option ) {
            var val = option[key];
            fd.append(key,val);
        }
    }
    $.ajax({
        "type": "post",
        "url": "act/upload.php",
        "async": true,
        "data": fd,
        "contentType": false,
        "processData": false,
        "dataType": "json",
        "success": function(list) {
            console.log(list);
            var result = list[0];
            if( result.status != "success" ) {
                console.log(result.message);
                return;
            }
            if( target.length > 0 ) { target[0].setAttribute("value",result.refer); }
        },
        "error": function(xhr,status,error) {
            console.log(status,error);
        }
    });
},



"savePage": function(after) {
    var datalist = new Array();
    $("#main-content form").each(function(n,form) {
        datalist.push($(form).serialize());
    });
    $.ajax({
        "type": "post",
        "url": "act/update-page.php",
        "async": true,
        "data": datalist.join("&"),
        "dataType": "json",
        "success": function(json) {
            console.log(json);
            var dat = { "id" : json.page };
            switch(after) {
            case "reload":
                MMNet.configure("menupage",dat);
                break;
            case "update":
                $.ajax({
                    "type": "post",
                    "url": "act/save-page.php",
                    "async": true,
                    "data": dat,
                    "dataType": "json",
                    "success": function(json) {
                        console.log(json);
                    }
                });
                break;
            case "preview":
                $.ajax({
                    "type": "post",
                    "url": "act/save-page.php",
                    "async": true,
                    "data": dat,
                    "dataType": "json",
                    "success": function(json) {
                        // create preview iframe...
                        if( json.url ) {
                            window.open(json.url);
                        }
                    }
                });
                break;
            default:
                break;
            }
        },
        "error": function(xhr,status,error) {
            console.log(status,error);
        }
    });
},

"Section": {
    "load": function(type,data,area) {
        if( area == null ) { return; }
        if( area.length == 0 ) { return; }
        $.ajax({
            "type": "post",
            "url": "patterns/"+type+".php",
            "async": true,
            "data": data,
            "dataType": "html",
            "success": function(html) { area.html(html); },
            "error": function(xhr,status,error) { console.log(status,error); }
        });
    },
    "Language": {
        "show": function(form) {
            form.children(".language-selector").removeClass("-hide");
            form.children(".section-content").removeClass("-full");
        },
        "hide": function(form) {
            form.children(".language-selector").addClass("-hide");
            form.children(".section-content").addClass("-full");
        }
    },
    "LastButton": null,
    "select": function(lang,btn) {
        MMNet.Section.Language.show(btn.parents("form"));
        var area = btn.parents(".language-selector").siblings(".section-content");
        if( area.length == 0 ) { return; }

        if( this.LastButton != null ) {
            this.LastButton.removeClass("active");
        }
        this.LastButton = btn;
        this.LastButton.addClass("active");

        area.children("*[lang]").each(function(n,e) {
            $(e).css("display",( e.getAttribute("lang") == lang ) ? "block" : "none" );
        });
    },
    "Table": {
        "show": function(area,forms) {
            if( area.attr("data-status") == "opened" ) {
                this.hide(area);
                return;
            }
            if( forms.length <= 0 ) { return; }
            var inputs = forms[0].elements, w = area.width(), cw = 150;
            var data = {
                "bound": {
                    "width": w - cw,
                    "height": 300
                },
                "controller": {
                    "width": cw
                }
            };
            for(var i=0;i<inputs.length;i++) {
                var input = inputs[i];
                if( typeof input.name != "string" ) { continue; }
                var match = input.name.match(/.*\[source\]\[(.*?)\]/);
                if( match == null ) { continue; }
                data[match[1]] = input.value;
            }
            area.animate({"height":"300px"});
            $.ajax({
                "type": "post",
                "url": "patterns/table.view.php",
                "async": true,
                "data": data,
                "dataType": "html",
                "success": function(html) {
                    area.empty();
                    area.html(html);
                },
                "error": function(xhr,status,error) { console.log(status,error); }
            });
            area.attr("data-status","opened");
        },
        "hide": function(area) {
            area.empty();
            area.animate({"height":"0px"});
            area.attr("data-status","closed");
        }
    }
},



/*-------------------------------------------------------------\
 * FileManager
 - select from folder(DB:file_folder).
 - upload to folder(DB:file_folder).
\-------------------------------------------------------------*/
"FileManager": {
    "Dialog": null,
    "select": function(folder,target) {
        if( target.length <= 0 ) { return; }
        if( this.Dialog == null ) { this.Dialog = new MMNet.ui.Dialog({"pattern":"folder.select"}); }
        this.Dialog.load({"id":folder});
        this.Dialog.setListener({
            "submit": {
                "callback": function(imgpath) { this.value = imgpath; },
                "caller": target[0]
            }
        });
        this.Dialog.show();
        //var dialog = new MMNet.ui.Dialog("folder.select",{"id":folder},function(imgpath) { this.value = imgpath; },target[0]);
        //dialog.show();
    }
},

/*-------------------------------------------------------------\
 * UI:IFrameDialog
\-------------------------------------------------------------*/
"ui": {
    "Activated": {
        "Dialog": null,
    },
    "report": function(type,events,closed) {
        var ui = this.Activated[type];
        if( this.Activated[type] == null ) {
            console.log("No Activated "+type.toUpperCase()+".");
            return;
        }
        for( var k in events ) { ui.trigger(k,events[k]); }
        if( closed ) { ui.hide(); }
    },
    "close": function(type) {
        var ui = this.Activated[type];
        if( this.Activated[type] == null ) {
            console.log("No Activated "+type.toUpperCase()+".");
            return;
        }
        ui.hide();
    },

    "Dialog": function(config) {
        this.Cover = document.createElement("div");
        this.Cover.style.display = "none";
        this.Cover.style.backgroundColor = "rgba(0,0,0,0.5)";
        this.Cover.style.position = "fixed";
        this.Cover.style.top = "0px";
        this.Cover.style.left = "0px";
        this.Cover.style.right = "0px";
        this.Cover.style.bottom = "0px";
        this.Cover.style.padding = "20px";
        document.body.appendChild(this.Cover);

        this._method = "post";
        this._url = "";
        if( config ) {
            if( config.method ) { this._method = config.method; }
            if( config.pattern ) { this._url = "patterns/" + config.pattern + ".php"; }
        }
        this._status = "Empty";

        var that = this;
        this.load = function(data) {
            $.ajax({
                "type": this._method,
                "url": this._url,
                "async": true,
                "data": data,
                "dataType": "html",
                "success": function(html) {
                    $(that.Cover).html(html);
                    var prevStatus = that._status;
                    that._status = "Loaded";
                    if( prevStatus == "Delay" ) { that.show(); }
                },
                "error": function(xhr,status,error) { console.log(status,error); }
            });
        }

        this._listeners = {};
        this.setListener = function(listeners) { this._listeners = listeners; };
        this.trigger = function(event,args) {
            var listener = this._listeners[event];
            if( listener != null ) { listener.callback.call(listener.caller,args); }
        };

        
        this.BodyOverflow = null;
        this.show = function() {
            if( this._status != "Loaded" ) {
                this._status = "Delay";
                return;
            }
            this.BodyOverflow = document.body.style.overflow;
            document.body.style.overflow = "hidden";
            this.Cover.style.display = "block";
            MMNet.ui.Activated.Dialog = this;
        };
        this.hide = function() {
            this.Cover.style.display = "none";
            if( this.BodyOverflow == null ) { this.BodyOverflow = "auto"; }
            document.body.style.overflow = this.BodyOverflow;
            MMNet.ui.Activated.Dialog = null;
        };
    }
}

});