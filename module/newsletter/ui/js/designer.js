
// Variables globales
var idx     = 0;
var editor  = $(".edit");
var data    = new Object();

// Apres le chargement de la page
$(document).ready(function() {

    // Init du compteur de layouts
    $(".layout").each( function(e) {
        if($(this).attr("data-idx")) {
            var id = parseInt($(this).attr("data-idx"));
            //alert(idx);
            if(id > idx) idx = id + 1;
        }
    });

    // Chargement des elements a ajouter
    $('#layoutAdd').find('option').remove();
    var optinit = new Option("", "", false, false);
    $("#layoutAdd").append(optinit);
    $("items").each( function(e) {
        var opt = new Option(getName($(this)), getId($(this)), false, false);
        $("#layoutAdd").append(opt);
    });

    // ------------------------------
    // Initialisation template
    // ------------------------------
    if(init == true) {
        // Ajout 1 item par bloc
        $("items").each( function(e) {
            var item = $(this);
            if(item.attr("data-url")) {
                var opt = {};
                $.post($(this).attr("data-url"), opt, function(d) {
                    var list = $.parseJSON(d);
                    for(i in list) {
                        layoutAdd(getId(item), list[i]);
                    }
                });
            }else {
                layoutAdd(getId(item));
            }
        });
    }

    // ---- Events
    // Ajout d'un item
    $("#layoutAdd").change( function(e) {
        layoutAdd($(this).val());
    });
    // Desactiver liens
    $('a').click( function(e){
        e.preventDefault();
    });
    layoutEvents();


    // Sauvegarde
    $('#save').click( function(e) {
        save();
    });

    // Sortable
    $("items").sortable({
        items: ".layout"
    });
    //$(".layout").disableSelection();

    loadData();

});

//------------------------
// LAYOUTS
//------------------------

function layoutAdd(id, content) {

    var items   = search("items", id);

    if(items.attr("data-repeat", "1")) {
        idx++;

        if(items) {
            var layouts     = layoutList(items);
            var newLayout   = layouts.first().clone().attr("data-idx", idx );

            layoutInit(newLayout, content);
            newLayout.hide();
            items.append(newLayout);
            newLayout.fadeIn(200);
        }
        $("#layoutAdd").val("");

        layoutEvents();
    }else {

    }
}

function layoutList(items) {
    return items.find("item").find(".layout");
}

function layoutEvents() {
    $('.layout').hover(
        function () {
            $(this).find(".delete, .duplicate").show();
        },
        function () { $(this).find(".delete, .duplicate").hide(); }
    );
    $('.layout').click( function(e){
        layoutEdit($(this));
        e.preventDefault();
        e.stopImmediatePropagation();
    });
    $('.delete').click( function(e){
        layoutRemove($(this).parent(".layout"));
        e.preventDefault();
        e.stopImmediatePropagation();
    });
    $('.duplicate').click( function(e){
        layoutDuplicate( $(this).parent(".layout"));
        e.preventDefault();
        e.stopImmediatePropagation();
    });
}

function layoutEdit(layout) {
    var editable    = layout.find(".editable");
    var id_layout   = layout.attr("data-idx");

    $(".layout").removeClass("layoutActive");
    layout.addClass("layoutActive");

    editorOpen();
    editor.html('<div class="edit-header"><a class="btn save">Enregistrer</a><a class="btn close">Annuler</a></div>');

    $('.close').click( function(e){
        editorClose();
    });
    $('.save').click( function(e){
        layoutSave(layout);
    });

    /*
     line : input text
     text : textarea
     richtext : textarea tinymce
     href : href du lien
     */

    // Menu deroulant changement layout
    editor.append('<div class="edit-form">Changer de mise en forme<br /><select id="layoutChange"></select></div>');
    var layouts = layoutList(layout.parent("items"));
    layouts.each( function() {
        var opt = new Option(getName($(this)), getId($(this)), false, false);
        $("#layoutChange").append(opt);
    });

    $("#layoutChange option[value='" + getId(layout) + "']").attr('selected','selected');

    editable.each( function() {
        var type  = getType($(this));
        var id    = getId($(this), true);
        var name  = getName($(this));
        var html  = '<div class="edit-form">';
        var val   = $(this).html();
        //alert(type);
        if(type == "line" || type == "href") {
            if(type == "href") val = $(this).attr("href");
            html += name + '<br /><input type="text" id="' + id + '" value="' + val + '"> ';
        }
        if(type == "text") {
            html += name + '<br /><textarea class="mceNoEditor" rows="10" id="' + id + '">' + val + '</textarea> ';

        }
        if(type == "richtext") {
            html += name + '<br /><textarea class="mceEditor" rows="10" name="' + id + '" id="' + id + '">' + val + '</textarea> ';
        }
        editor.append(html + "</div>");

    });
    richtext();

    $("#layoutChange").change( function(e) {
        layoutChange(layout, $(this).val());
    });
}

function layoutSave(layout) {
    var editable    = layout.find(".editable");
    var id_layout   = layout.attr("data-idx");
    data[id_layout] = {};

    editable.each( function() {

        var type  = getType($(this));
        var id    = getId($(this), true);
        var name  = getName($(this));
        var val   = "";

        if(type == "text" || type == "line") {
            val = editor.find("#" + id).val();
            $(this).html(val);
        }
        if(type == "href") {
            val = editor.find("#" + id).val();
            $(this).attr("href", val);
        }
        if (type == "richtext") {
            var ed  = tinymce.get(id);
            val     = ed.getContent();
            $(this).html(val);
        }
        data[id_layout][id] = val;

    });
    editorClose();

}

function layoutChange(layout, id) {
    var items       = layout.parent("items");
    var newLayout   = items.find("item").find(".layout[data-id=" + id + "]").clone();

    newLayout.attr("data-idx", layout.attr("data-idx"));
    layoutInit(newLayout);

    layout.before(newLayout);
    layoutRemove(layout);
    editorClose();
    layoutEdit(newLayout);
    layoutEvents();
}
function layoutRemove(layout) {
    layout.fadeOut(200);
    layout.fadeOut('slow').queue(function() { layout.remove(); });

}

function layoutDuplicate(layout) {
    var newLayout   = layout.clone();
    idx ++;
    newLayout.attr("data-idx", idx);

    newLayout.hide();
    layout.after(newLayout);

    newLayout.find(".delete, .duplicate").hide();
    newLayout.fadeIn(200);
    //layoutInit(newLayout);
    layoutEvents();
}

function layoutInit(layout, content) {

    var items           = layout.parent("items");
    var layoutControl   = "";

    if(items.attr("data-repeat", "1")) {
        layoutControl += '<a class="btn duplicate">Dupliquer</a>';
    }
    layoutControl += '<a class="btn delete">Supprimer</a>';

    layout.prepend(layoutControl);

    var editable    = layout.find(".editable");

    if(content) {
        editable.each( function() {
            var item  = $(this);
            var type  = getType(item);
            var id    = getId(item);
            var url   = item.attr("data-url");
            val = eval("content." + id);
            if(url) {
                var valeur = "";
                if(type == "href") valeur = item.attr("href");
                else valeur = item.html();
                var opt = {value:valeur};
                $.post(url, opt, function(d) {
                    if(type == "href") item.attr("href", d);
                    else item.html(d);
                });
            }else {
                if(val) {
                    if(type == "href") item.attr("href", val);
                    else item.html(val);
                }
            }
        });
        if(content.id_content) {
            layout.attr("data-id_content", content.id_content);
        }
    }else {
        // DATA: chargement de data si existe
        if(layout.attr("data-idx")) {
            var id_layout   = layout.attr("data-idx");
            if(data[id_layout]) {
                editable.each( function() {
                    var type  = getType($(this));
                    var id    = getId($(this));
                    if(data[id_layout][id]) {
                        val = data[id_layout][id];
                        if(type == "href") $(this).attr("href", val);
                        else $(this).html(val);
                    }
                });
            }
        }
    }

}

//------------------------
// EDITOR
//------------------------


function editorClose() {
    $("#overlay").hide();
    $(".layout").removeClass("layoutActive");
    editor.fadeOut(100);
    editor.html("");
    $(".delete, .duplicate").hide();
}
function editorOpen() {
    $("#overlay").show();
    editor.html("");
    editor.fadeIn(100);
    $(".delete, .duplicate").hide();
}


//------------------------
// DATA
//------------------------
function loadData() {
    $(".layout").each( function(e) {
        var layout = $(this);
        if(layout.attr("data-idx")) {
            var editable    = layout.find(".editable");
            var id_layout   = layout.attr("data-idx");
            data[id_layout] = {};
            editable.each( function() {
                var type  = getType($(this));
                var id    = getId($(this));
                var val   = $(this).html();

                if(type == "href") {
                    val = $(this).attr("href");
                }
                data[id_layout][id] = val;

            });
        }
    });
}

function urlList(url, opt) {
    if(!opt) var opt = {};
    $.post(url, opt, function(d) {
        var list = $.parseJSON(d);
        return list;
    });
}
function urlLoad(url, opt) {
    if(!opt) var opt = {};
    $.post(url, opt, function(d) {
        alert('url : ' + url + ' - result : ' + d);
        return d;
    });
}

//------------------------
// UTILITIES
//------------------------

// Fonctions pour recuperer facilement les attributs
function getId(el, as) {
    if(el) {
        var id = el.attr('data-id');
        if(as) id = id.replace(".", "---");
        else id = id.replace("---", ".");
        return id;
    }
}
function getName(el) {
    if(el) return (el.attr('data-name') != '') ? el.attr('data-name') : el.attr('data-id');
}
function getType(el) {
    if(el) return (el.attr('data-type') != '') ? el.attr('data-type') : "line";
}
// Fonctions pour chercher par ID
function search(el, id) {
    return $(el + "[data-id=" + id + "]");
}


//-----------------------
// SAUVEGARDE
//-----------------------

function save() {
    var html = document.documentElement.outerHTML;

    $.post('helper/designer-save', { id_newsletter: id_newsletter, html: html, templatehtml: html}, function(data) {
        if(data != 0) {
            alert('Enregistré');
            $('#data').submit()
        }
    });
}

//-----------------------
// TINYMCE
//-----------------------

function richtext() {
    tinymce.init({
        mode : "textareas",

        editor_selector : "mceEditor",
        editor_deselector : "mceNoEditor",
        // General options
        theme : "advanced",
        plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "mediapicker,bold,italic,underline,|,link,unlink,|,pasteword",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        force_br_newlines : true,
        force_p_newlines : false,
        forced_root_block : "",
        invalid_elements : "p",
        paste_preprocess : function(pl, o) {
            //example: keep bold,italic,underline and paragraphs
            //o.content = strip_tags( o.content,'<b><u><i><p>' );

            // remove all tags => plain text
            o.content = strip_tags( o.content,'' );
        },

        setup : function(ed) {
            ed.addButton('mediapicker', {
                title : 'Insérer des images',
                image : '/admin/core/ui/img/_img/myb.gif',
                onclick : function() {
                    mediaPicker(ed.id, 'mce');
                }
            });
        }
    });
}

function strip_tags (str, allowed_tags)
{

    var key = '', allowed = false;
    var matches = [];    var allowed_array = [];
    var allowed_tag = '';
    var i = 0;
    var k = '';
    var html = '';
    var replacer = function (search, replace, str) {
        return str.split(search).join(replace);
    };
    // Build allowes tags associative array
    if (allowed_tags) {
        allowed_array = allowed_tags.match(/([a-zA-Z0-9]+)/gi);
    }
    str += '';

    // Match tags
    matches = str.match(/(<\/?[\S][^>]*>)/gi);
    // Go through all HTML tags
    for (key in matches) {
        if (isNaN(key)) {
            // IE7 Hack
            continue;
        }

        // Save HTML tag
        html = matches[key].toString();
        // Is tag not in allowed list? Remove from str!
        allowed = false;

        // Go through all allowed tags
        for (k in allowed_array) {            // Init
            allowed_tag = allowed_array[k];
            i = -1;

            if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');}
            if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');}
            if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;}

            // Determine
            if (i == 0) {                allowed = true;
                break;
            }
        }
        if (!allowed) {
            str = replacer(html, "", str); // Custom replace. No regexing
        }
    }
    return str;
}
