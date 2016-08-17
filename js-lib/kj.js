function sync_form_submit(form,prefix) {
    var data = new Object(), data_array = new Array;
    for(var i=0;i<form.elements.length;i++) {
        var fe = form.elements[i];
        if( fe.name.indexOf(prefix) != 0 ) { continue; }
        var n = fe.name.substring(prefix.length);
        if( fe.type.toUpperCase() == "RADIO" && !fe.checked ) { continue; }
        data[n] = fe.value;
        data_array.push(n+"="+encodeURIComponent(fe.value));
    }
    console.log(data,data_array.join("&"));
    call_ajax('lib/update_11.php',data_array.join("&"),'POST','panel_11');
}

function kj_edit(form,prefix,data) {
    if( typeof form == "string" ) { form = document.getElementById(form); }
    if( form == null ) { return; }
    for(var i=0;i<form.elements.length;i++) {
        var fe = form.elements[i];
        if( fe.name.indexOf(prefix) != 0 ) { continue; }
        var n = fe.name.substring(prefix.length);
        if( data[n] != null ) {
            switch(fe.type.toUpperCase()) {
            case "RADIO":
                if( fe.value == 1 && data[n] == "127" ) { fe.checked = true; }
                if( fe.value == 0 && data[n] == "0" ) { fe.checked = true; }
                break;
            default:
                fe.value = data[n];
                break;
            }
        }
    }
}