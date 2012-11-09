function load_sortable() {    
    $("repeaters").sortable({
        items: "repeater"
    });
    
    $("repeaters").disableSelection();
}
$(document).ready(function() {
    load_sortable();
});