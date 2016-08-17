window.MMNet = window.MMNet || new Object();

/*-------------------------------------------------------------\
 * Item Remote
\-------------------------------------------------------------*/
MMNet.Item = {

"check": function() {
    if( !confirm("確定對所有符合條件的物品進行盤點？") ) { return; }
    $.ajax({
        "type": "post",
        "url": "act/item.check.php",
        "data": MMNet.Page.get(),
        "success": function() {
            MMNet.view("main","itemlist");
        }
    });
},

"report": function(id,status) {
    var reason = prompt("請輸入回報內容：");
    $.ajax({
        "type": "post",
        "url": "act/update.php",
        "async": true,
        "data": {
            "table": "item_list",
            "id": id,
            "field": "report",
            "value": status,
            "reason": reason
        },
        "success": function() {
            MMNet.view("main","inventory");
        },
        "error": function(xhr,status,error) { console.log(status,error); }
    });
},

"confirm": function(id,is_confirmed) {
    $.ajax({
        "type": "post",
        "url": "act/update.php",
        "async": true,
        "data": {
            "table": "item_list",
            "id": id,
            "field": "confirm",
            "value": is_confirmed ? "checked" : null
        },
        "success": function() {
            MMNet.view("main","itemlist");
        },
        "error": function(xhr,status,error) { console.log(status,error); }
    });
},

"export": function() {
    $.ajax({
        "type": "post",
        "url": "extends/item.export.php",
        "data": MMNet.Page.get(),
        "dataType": "json",
        "success": function(json) {
            console.log(json);
            window.open(json.file);
            //MMNet.view("main","itemlist");
        }
    });
}

};