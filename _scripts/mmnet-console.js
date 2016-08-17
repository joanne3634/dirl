window.mmnet = {

"turn": function(eid) {
    var e = document.getElementById(eid);
    if( e == null ) { return; }

    switch(e.style.display) {
    case "none":
        e.style.display = "block";
        break;
    default:
        e.style.display = "none";
        break;
    }
},

"Loader": new kj.event.AjaxLoader(0),
"reload": function(id,href,msg,callback) {
    mmnet.Loader.start({
        "method": "get",
        "action": href,
        "async": true,
    },{
        "type": "text",
        "func": function(html) {
            this.innerHTML = html;
            pop_msg("../pic/check.gif",msg,500);
            if( callback ) { callback.call(); }
        },
        "caller": document.getElementById(id)
    });
},

"change": function(table,id,field,value,msg) {
    this.Loader.start({
        "method": "post",
        "action": "/kj-backend/direct-database.php",
        "async": true,
        "data": {
            "id": id,
            "sql": "UPDATE "+table+" SET "+field+"='"+value+"' WHERE id='"+id+"' LIMIT 1"
        }
    },{
        "type": "json",
        "func": function(json) { mmnet.reload("panel_5","/MGE/panel_5.html",msg); }
    });
},

"switch": function(table,field,id1,id2,msg) {
    this.Loader.start({
        "method": "post",
        "action": "/kj-backend/ordinal.php",
        "async": true,
        "data": {
            "table": table,
            "field": field,
            "from": id1,
            "to": id2
        }
    },{
        "type": "json",
        "func": function(json) { mmnet.reload("panel_5","/MGE/panel_5.html",msg); }
    });
},

"remove": function(table,id,name) {
    if( name == null ) { name = table + "#" + id; }
    if( !confirm("確定要刪除「"+name+"」？") ) { return; }
    this.Loader.start({
        "method": "post",
        "action": "/kj-backend/delete.php",
        "async": true,
        "data": {
            "table": table,
            "id": id,
        }
    },{
        "type": "json",
        "func": function(json) { mmnet.reload("panel_5","/MGE/panel_5.html","刪除"); }
    });
},

"DragDrop": {
    "DropZone": null,
    "DragItem": null,
    "dragStart": function(evt,drag) {
        if( this.DragItem != null ) { this.DragItem.style.opacity = 1; }
        var ev = evt || window.event;
        ev.dataTransfer.setData("text/plain","This step should be SKIP!!!")
        this.DragItem = drag;
        this.DropZone = drag.parentNode;
        this.DragItem.style.opacity = 0.5;
    },
    "dragOver": function(evt,over) {
        if( this.DragItem == null ) { return false; }
        if( over.parentNode != this.DropZone ) { return false; }
        var ev = evt || window.event;
        ev.preventDefault();

        if( this.DragItem == over ) { return true; }
        var box = over.getBoundingClientRect();
        var before = over;
        if( ev.clientY > (box.top+(box.height/2)) ) {
            while( n = before.nextSibling ) {
                //console.log(n.tagName,over.tagName);
                before = n;
                if( n.tagName == over.tagName ) { break; }
            }
        }
        if( before != null ) {
            var p = before.parentNode;
            p.insertBefore(this.DragItem,before);        
            this.formData = { "before": before.getAttribute("data-dbid") };
        } else {
            var p = over.parentNode;
            p.appendChild(this.DragItem);
            this.formData = { "after": over.getAttribute("data-dbid") };
        }
    },
    "dropOn": function(evt,zone) {
        var ev = evt || window.event;
        ev.preventDefault();
        if( this.DragItem == null ) { return false; }
        if( zone.parentNode != this.DropZone ) { return false; }
        this.formData["id"] = this.DragItem.getAttribute("data-dbid");
        this.formData["table"] = this.DragItem.getAttribute("data-table");
        mmnet.Loader.start({
            "method": "post",
            "action": "/kj-backend/line-up.php",
            "async": true,
            "data": this.formData
        },{
            "type": "text",
            "func": function(json) {
                this.style.opacity = 1;
            },
            "caller": this.DragItem
        });
        this.DragItem = null;
    }
},

"fake": {
    "submitFromForm": function(frm) {
        var form = $(frm);
        var ajax_data = {
            "type": form.attr("method"),
            "url": form.attr("action"),
            "async": true,
            "data": form.serialize(),
            "dataType": "json",
            "success": function(json) {
                //$("#main-content").html(html);
                console.log(json);
                mmnet.reload("panel_10","/MGE/panel_10.html?id="+json.id);
            },
            "error": function(xhr,status,error) {
                console.log(status,error);
                mmnet.reload("panel_10","/MGE/panel_10.html");
            }
        };
        //console.log(ajax_data);
        $.ajax(ajax_data);
    },
    "putDataInForm": function(fid,table,id,json) {
        var form = document.getElementById(fid);
        if( form == null ) { return; }

        for(var i=0;i<form.elements.length;i++) {
            var input = form.elements[i], n = input.name;
            var key = n.match(/(.*)\[data\]/i);
            input.value = "";
            if( n == null ) { continue; }
            else if( n == "id" ) { input.value = id; }
            else if( n == "table" ) { input.value = table; }
            else if( key != null ) {
                var k = key[1], v = json[k];
                if( !v ) { continue; }
                console.log(input);
                if( input.type == "checkbox" ) {
                    if( v == "On" ) { input.setAttribute("checked","checked"); }
                    else { input.removeAttribute("checked"); }
                    continue;
                }
                if( lang = n.match(/.*\[data\]\[(.*)\]/i) ) {
                    var l = lang[1];
                    if( v[l] ) { input.value = v[l]; }
                } else { input.value = v; }
            }
        }

        if( form.style.display == "none" ) { form.style.display = "block"; }
    },
    "set": function(table,id,state) {
        $.ajax({
            "type": "post",
            "url": "/kj-backend/act/alter.php",
            "async": true,
            "data": {
                "table": table,
                "id": id,
                "field": "v_state",
                "value": state
            },
            "dataType": "json",
            "success": function(json) {
                console.log(json);
                mmnet.reload("panel_10","/MGE/panel_10.html");
            },
            "error": function(xhr,status,error) {
                console.log(status,error);
                mmnet.reload("panel_10","/MGE/panel_10.html");
            }
        });
    },
    "copy": function(fid,src,des) {
        var form = document.getElementById(fid);
        if( form == null ) { return; }
        var srcValue = new Object();
        var desInput = new Object();

        for(var i=0;i<form.elements.length;i++) {
            var input = form.elements[i], n = input.name;
            var key = n.match(/(.*)\[data\]/i);
            if( n == null ) { continue; }
            else if( n == "id" ) { continue; }
            else if( n == "table" ) { continue; }
            else if( key != null ) {
                var k = key[1];
                if( lang = n.match(/.*\[data\]\[(.*)\]/i) ) {
                    var l = lang[1];
                    if( l == src ) { srcValue[k] = input.value; }
                    if( l == des ) { desInput[k] = input; }
                }
            }
        }

        for( var k in desInput ) { desInput[k].value = srcValue[k]; }
    }
}

};