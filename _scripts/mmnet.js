function turn(elem) {
    if( typeof elem == "string" ) { elem = document.getElementById(elem); }
    if( elem == null ) { return; }

    switch( elem.style.display ) {
    case "none":
        if( elem.Display == null ) { elem.Display = "block"; }
        elem.style.display = elem.Display;
        break;
    default:
        elem.Display = elem.style.display;
        elem.style.display = "none";
        break;
    }
}