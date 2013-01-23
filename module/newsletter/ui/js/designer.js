
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
    $(".repeat, .unique.noremove").each( function(e) {
        var opt = new Option(getName($(this)), getId($(this)), false, false);
        $("#layoutAdd").append(opt);
    });

    // ------------------------------
    // Initialisation template
    // ------------------------------
    if(init == true) {
        // Ajout 1 item par bloc
        $(".repeat").each( function(e) {
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

    // Controller des repeat
    $(".edit-repeat").remove();
    $(".repeat").each( function() {
        $(this).before('<!--TEMPLATE--><div class="edit-repeat" data-id="' + getId($(this)) + '">Editer liste</div><!--/TEMPLATE-->');
        $(".edit-repeat").click( function(e){
            repeatEdit($(this));
            e.preventDefault();
            e.stopImmediatePropagation();
        });
    });


    // ---- Events
    // Ajout d'un item
    $("#layoutAdd").change( function(e) {
        layoutAdd($(this).val());
    });
    // Desactiver liens
    $('a').click( function(e){
        e.preventDefault();
    });
    bindEvents();


    // Sauvegarde
    $('#save').click( function(e) {
        save();
    });

    // Sortable
    $(".repeat").sortable({
        items: ".layout"
    });
    //$(".edit").resizable();
    $(".edit").draggable({ handle: "div.ui-widget-header" });

    loadData();

});

//------------------------
// LAYOUTS
//------------------------

function layoutAdd(id, content) {

    var items   = search(".repeat", id);

    if( (items.attr("data-unique") != "1") || ((items.attr("data-unique") == "1") && !(items.find('.layout.template'))) ) {
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

        bindEvents();

    }else {
        alert("Ajout impossible");
    }
    sizeIframe();
}

function layoutList(items) {
    var layouts = items.find('.layout.template');
    //console.log(layouts);
    return layouts;
}

function bindEvents() {
    // Repeat
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

    // Unique
    $('.unique').click( function(e){
        uniqueEdit($(this));
        e.preventDefault();
        e.stopImmediatePropagation();
    });
    sizeIframe();
}

function layoutEdit(layout) {
    var editable    = layout.find(".editable");
    var id_layout   = layout.attr("data-idx");
    var id_type     = layout.attr("data-id_type");
    var id_content  = layout.attr("data-id_content");
    var user        = layout.attr("data-user");
    var id_user     = layout.attr("data-id_user");

    $(".layoutActive").removeClass("layoutActive");
    layout.addClass("layoutActive");

    editorOpen();
    //editor.html('<div class="edit-header"><a class="btn save">Enregistrer</a><a class="btn close">Annuler</a></div>');

    $('.close').click( function(e){
        editorClose();
        e.preventDefault();
        e.stopImmediatePropagation();
    });
    $('.save').click( function(e){
        layoutSave(layout);
        e.preventDefault();
        e.stopImmediatePropagation();
    });

    // Menu deroulant changement layout
    var layouts = layoutList(layout.parent(".repeat"));
    if(layouts.length > 1) {
        editor.append('<div class="edit-form">Changer de mise en forme : <select id="layoutChange"></select><hr></div>');
        layouts.each( function() {
            var opt = new Option(getName($(this)), getId($(this)), false, false);
            $("#layoutChange").append(opt);
        });

        $("#layoutChange option[value='" + getId(layout) + "']").attr('selected','selected');

        $("#layoutChange").change( function(e) {
            layoutChange(layout, $(this).val());
        });
    }

    // Selecteur content
    if(id_type) {
        if(id_content) id_content = id_content;
        else id_content = "";
        var searchContent = '<div class="edit-form">';
        searchContent += 'Chercher un contenu<br /><input type="text" id="search_content"><div id="results"></div>';
        searchContent += '<br />ID contenu sélectionné : <input type="text" id="select_id_content" value="' + id_content + '" size="5">';
        searchContent += '&nbsp;<a class="btn apply">Recharger le contenu</a><hr></div>';

        editor.append(searchContent);

        $("#search_content").bind('keydown keyup',function() {
            var request = $.ajax({
                url: 'helper/content-picker',
                data: {
                    q: $(this).val(),
                    id_type: id_type
                }
            });
            request.done(function(d) {
                $('#results').fadeIn(200);
                $('#results').html(d);
                $('#results a').click( function(e) {
                    $("#select_id_content").val($(this).attr("data-id_content"));
                    $('#results').fadeOut(200);

                    if($("#select_id_content").val() > 0) {
                        layoutApplyContent(layout, $("#select_id_content").val());
                    }
                    e.preventDefault();
                    e.stopImmediatePropagation();
                });
            });
        });

        $(".btn.apply").click( function() {
            if($("#select_id_content").val() > 0) {
                layoutApplyContent(layout, $("#select_id_content").val());
            }
        });

    }


    editable.each( function() {
        var type  = getType($(this));
        var id    = getId($(this), true);
        var name  = getName($(this));
        var html  = '<div class="edit-form">';
        var val   = $(this).html();

        var href  = false;
        var atype = type.split(",");

        if(atype[0] == "href") {
            href = true;
            type = atype[1];
        }
        if(href) {
            html += 'URL (' + name + ')<br /><input type="text" id="editor-href-' + id + '" value="' + $(this).attr("href") + '"> ';
        }
        //alert(type);
        if(type == "line" || type == "href") {
            if(type == "href") val = $(this).attr("href");
            html += name + '<br /><input type="text" id="editor-' + id + '" value="' + val + '"> ';
        }
        if(type == "text") {
            html += name + '<br /><textarea class="mceNoEditor" rows="10" id="editor-' + id + '">' + val + '</textarea> ';

        }
        if(type == "image") {
            var imgSrc = $(this).attr("src");
            var imgW = ($(this).attr("width")) ? $(this).attr("width") : "";
            var imgH = ($(this).attr("height")) ? $(this).attr("height") : "";

            html += name + '<br /><input type="text" id="editor-' + id + '" value="' + imgSrc + '">';
            html += '<input type="hidden" id="editor-height-' + id + '" value="' + imgH + '">';
            html += '<input type="hidden" id="editor-width-' + id + '" value="' + imgW + '">';
            html += '<a onclick="mediaPicker(\'editor-' + id + '\',\'line\')" class="btn">Sélectionner une image</a>';
            //html += '<img src="' + imgSrc + '" id="editor-preview-' + id + '"><hr>';

        }
        if(type == "richtext") {
            html += name + '<br /><textarea class="mceEditor" rows="10" name="editor-preview' + id + '" id="editor-' + id + '">' + val + '</textarea> ';
        }
        editor.append(html + "</div>");

    });
    richtext();


}
function layoutApplyContent(layout, id_content) {
    $.post('helper/designer-content', { id_content: id_content }, function(d) {
        var content = $.parseJSON(d);
        //console.log(content);
        layoutInit(layout, content);
        editorClose();
        layoutEdit(layout);
    });
    sizeIframe();


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
        var href  = false;
        var atype = type.split(",");

        if(atype[0] == "href") {
            href = true;
            type = atype[1];
        }
        if(href) {
            $(this).attr("href", editor.find("#editor-href-" + id).val());
            data[id_layout]["href-" + id] = editor.find("#editor-href-" + id).val();
        }
        if (type == "href") {
            val = editor.find("#editor-" + id).val();
            $(this).attr("href", val);
        }
        if(type == "text" || type == "line") {
            val = editor.find("#editor-" + id).val();
            $(this).html(val);
        }
        if (type == "richtext") {

            //alert("editor-" + id);
            var ed  = tinymce.get("editor-" + id);
            val     = ed.getContent();
            $(this).html(val);
        }
        if (type == "image") {
            var imgW = editor.find("#editor-width-" + id).val();
            var imgH = editor.find("#editor-height-" + id).val();

            var img  = $(this);
            val = editor.find("#editor-" + id).val();
            $.post('helper/designer-media', { src: editor.find("#editor-" + id).val(), w: imgW, h: imgH}, function(d) {
                val = d;
                img.attr("src", val);
            });
        }
        data[id_layout][id] = val;

    });
    editorClose();

}

function layoutChange(layout, id) {
    var items       = layout.parent(".repeat");
    var newLayout   = items.find(".layout.template[data-id=" + id + "]").clone();

    newLayout.attr("data-idx", layout.attr("data-idx"));
    layoutInit(newLayout);

    layout.before(newLayout);
    layout.remove();
    editorClose();
    layoutEdit(newLayout);
    bindEvents();
}
function layoutRemove(layout) {
    if(confirm("Confirmer la suppression ?")) {
        layout.fadeOut(200);
        layout.fadeOut('slow').queue(function() { layout.remove(); });
    }

    sizeIframe();
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
    bindEvents();
}

function layoutInit(layout, content) {

    var items           = layout.parent(".repeat");
    var layoutControl   = "";
    var id_layout       = layout.attr("data-idx");

    layout.removeClass("template");

    if(items.attr("data-repeat", "1")) {
        layoutControl += '<a class="btn duplicate">Dupliquer</a>';
    }
    layoutControl += '<a class="btn delete">Supprimer</a>';

    layout.prepend(layoutControl);

    var editable    = layout.find(".editable");

    if(content) {
        data[id_layout] = {};
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
                var opt = {value:valeur, content:content};
                $.post(url, opt, function(d) {
                    if(type == "href") item.attr("href", d);
                    else if(type == "image") item.attr("src", d);
                    else item.html(d);
                    data[id_layout][id] = d;
                });
            }else {
                if(val) {
                    if(type == "href") item.attr("href", val);
                    else if(type == "image") item.attr("src", val);
                    else item.html(val);
                    data[id_layout][id] = val;
                }
            }
        });
        if(content.id_content) {
            layout.attr("data-id_content", content.id_content);
        }
    }else {
        // DATA: chargement de data si existe
        if(layout.attr("data-idx")) {
            if(data[id_layout]) {
                editable.each( function() {
                    var type  = getType($(this));
                    var id    = getId($(this));
                    if(data[id_layout][id]) {
                        val = data[id_layout][id];
                        if(type == "href") $(this).attr("href", val);
                        else if(type == "image") $(this).attr("src", val);
                        else $(this).html(val);
                    }
                });
            }
        }
    }

}


function repeatEdit(repeat) {
    var id      = getId(repeat);
    var id_type = repeat.attr("data-id_type");

    editorOpen();

    editor.find(".save").hide();

    // Combien
    editor.append('<div class="edit-form">Combien : <input type="text" id="editor-limit" style="width: 30px;"></div>');

    // Tri
    editor.append('<div class="edit-form">Trier par : <select id="editor-order"><option value="contentDateCreation">Date de création</option><option value="contentDateUpdate">Date de modification</option><option value="contentView">Nombre de vues</option><option value="contentName">Titre</option></select></div>');

    // Ordre
    editor.append('<div class="edit-form">Classement : <select id="editor-direction"><option value="ASC">Croissant</option><option value="DESC">Décroissant</option></select></div>');

    // Submit
    editor.append('<div class="edit-form"><a class="editor-load">Charger les éléments></a></div>');

    $('.close').click( function(e){
        editorClose();
    });
    $('.editor-load').click( function(e){
        var limit       = $("#editor-limit").val();
        var order       = $("#editor-order").val();
        var direction   = $("#editor-direction").val();
        var id_type     = id_type;
        var opt = {"limit":limit, "order":order, "direction":direction, "id_type":id_type};
        console.log(opt);
        $.post('helper/designer-contentList', opt, function(d) {
            var list = $.parseJSON(d);
            for(i in list) {
                layoutAdd(getId(repeat), list[i]);
            }
        });
    });



    $(".edit-form").show();


    /*
    var editable    = layout.find(".editable");
    var id_layout   = layout.attr("data-idx");
    var id_type     = layout.attr("data-id_type");
    var id_content  = layout.attr("data-id_content");
    var user        = layout.attr("data-user");
    var id_user     = layout.attr("data-id_user");

    $(".layoutActive").removeClass("layoutActive");
    layout.addClass("layoutActive");

    editorOpen();
    //editor.html('<div class="edit-header"><a class="btn save">Enregistrer</a><a class="btn close">Annuler</a></div>');

    $('.close').click( function(e){
        editorClose();
    });
    $('.save').click( function(e){
        layoutSave(layout);
    });

    // Menu deroulant changement layout
    var layouts = layoutList(layout.parent(".repeat"));
    if(layouts.length > 1) {
        editor.append('<div class="edit-form">Changer de mise en forme : <select id="layoutChange"></select><hr></div>');
        layouts.each( function() {
            var opt = new Option(getName($(this)), getId($(this)), false, false);
            $("#layoutChange").append(opt);
        });

        $("#layoutChange option[value='" + getId(layout) + "']").attr('selected','selected');

        $("#layoutChange").change( function(e) {
            layoutChange(layout, $(this).val());
        });
    }

    // Selecteur content
    if(id_type) {
        if(id_content) id_content = id_content;
        else id_content = "";
        var searchContent = '<div class="edit-form">';
        searchContent += 'Chercher un contenu<br /><input type="text" id="search_content"><div id="results"></div>';
        searchContent += '<br />ID contenu sélectionné : <input type="text" id="select_id_content" value="' + id_content + '" size="5">';
        searchContent += '&nbsp;<a class="btn apply">Recharger le contenu</a><hr></div>';

        editor.append(searchContent);

        $("#search_content").bind('keydown keyup',function() {
            var request = $.ajax({
                url: 'helper/content-picker',
                data: {
                    q: $(this).val(),
                    id_type: id_type
                }
            });
            request.done(function(d) {
                $('#results').fadeIn(200);
                $('#results').html(d);
                $('#results a').click( function(e) {
                    $("#select_id_content").val($(this).attr("data-id_content"));
                    $('#results').fadeOut(200);

                    if($("#select_id_content").val() > 0) {
                        layoutApplyContent(layout, $("#select_id_content").val());
                    }
                    e.preventDefault();
                    e.stopImmediatePropagation();
                });
            });
        });

        $(".btn.apply").click( function() {
            if($("#select_id_content").val() > 0) {
                layoutApplyContent(layout, $("#select_id_content").val());
            }
        });

    }


    editable.each( function() {
        var type  = getType($(this));
        var id    = getId($(this), true);
        var name  = getName($(this));
        var html  = '<div class="edit-form">';
        var val   = $(this).html();

        var href  = false;
        var atype = type.split(",");

        if(atype[0] == "href") {
            href = true;
            type = atype[1];
        }
        if(href) {
            html += 'URL (' + name + ')<br /><input type="text" id="editor-href-' + id + '" value="' + $(this).attr("href") + '"> ';
        }
        //alert(type);
        if(type == "line" || type == "href") {
            if(type == "href") val = $(this).attr("href");
            html += name + '<br /><input type="text" id="editor-' + id + '" value="' + val + '"> ';
        }
        if(type == "text") {
            html += name + '<br /><textarea class="mceNoEditor" rows="10" id="editor-' + id + '">' + val + '</textarea> ';

        }
        if(type == "image") {
            var imgSrc = $(this).attr("src");
            var imgW = ($(this).attr("width")) ? $(this).attr("width") : "";
            var imgH = ($(this).attr("height")) ? $(this).attr("height") : "";

            html += name + '<br /><input type="text" id="editor-' + id + '" value="' + imgSrc + '">';
            html += '<input type="hidden" id="editor-height-' + id + '" value="' + imgH + '">';
            html += '<input type="hidden" id="editor-width-' + id + '" value="' + imgW + '">';
            html += '<a onclick="mediaPicker(\'editor-' + id + '\',\'line\')" class="btn">Sélectionner une image</a>';
            //html += '<img src="' + imgSrc + '" id="editor-preview-' + id + '"><hr>';

        }
        if(type == "richtext") {
            html += name + '<br /><textarea class="mceEditor" rows="10" name="editor-preview' + id + '" id="editor-' + id + '">' + val + '</textarea> ';
        }
        editor.append(html + "</div>");

    });
    richtext();
*/

}


//------------------------
// EDITOR
//------------------------

function editorClose() {
    $("#overlay").hide();
    $(".layoutActive").removeClass("layoutActive");
    //editor.fadeOut(100);

    //editor.not(".top").hide();
    editor.find("repeat, .close").hide();
    //console.log(editor.find(".top"));
    editor.find(".top").show();
    editor.find(".edit-form").remove();
    $(".delete, .duplicate").hide();
    sizeIframe();

    var h = ($(window).scrollTop() + 50);

    if(parent.document) h = $(parent.document).scrollTop() - 230;

    if(h < 50) h = 50;
    editor.css({'marginTop': h + 'px'});
}
function editorOpen() {
    var h = ($(window).scrollTop() + 50);

    if(parent.document) h = $(parent.document).scrollTop() - 230;

    if(h < 50) h = 50;

    editor.find(".save, .close").show(); //html('<div class="edit-header ui-widget-header"><a class="btn save">Enregistrer</a><a class="btn close">Annuler</a></div>');
    $("#overlay").show();
    editor.find(".top").hide();
    editor.css({'marginTop': h + 'px'});
    //editor.fadeIn(100);
    $(".delete, .duplicate").hide();
    sizeIframe();

    $(document).click(function (e)
    {
        var container = $(".edit");
        if (container.has(e.target).length === 0) {
            editorClose();
            $("#overlay").hide();
        }
    });
    editor.find(".top").hide();
}

//------------------------
// UNIQUE
//------------------------

function uniqueEdit(el) {
    var type        = getType(el);
    var id          = getId(el, true);
    var name        = getName(el);
    var href        = false;
    var atype       = type.split(",");

    if(atype[0] == "href") {
        href = true;
        type = atype[1];
    }


    $(".layoutActive").removeClass("layoutActive");
    el.addClass("layoutActive");

    editorOpen();

    $('.close').click( function(e){
        editorClose();
        e.preventDefault();
        e.stopImmediatePropagation();
    });
    $('.save').click( function(e){
        uniqueSave(el);
        e.preventDefault();
        e.stopImmediatePropagation();
    });


    var html  = '<div class="edit-form">';
    var val   = el.html();
    //alert(type);
    if(href) {
        html += 'URL <br /><input type="text" id="editor-href-' + id + '" value="' + el.attr("href") + '"> ';
    }

    if(type == "line") {
        html += name + '<br /><input type="text" id="editor-' + id + '" value="' + val + '"> ';
    }
    if(type == "text") {
        html += name + '<br /><textarea class="mceNoEditor" rows="10" id="editor-' + id + '">' + val + '</textarea> ';
    }
    if(type == "richtext") {
        html += name + '<br /><textarea class="mceEditor" rows="10" name="editor-' + id + '" id="editor-' + id + '">' + val + '</textarea> ';
    }
    if(type == "image") {
        var imgSrc = el.attr("src");
        var imgW = (el.attr("width")) ? el.attr("width") : "";
        var imgH = (el.attr("height")) ? el.attr("height") : "";

        html += name + '<br /><input type="text" id="editor-' + id + '" value="' + imgSrc + '">';
        html += '<input type="hidden" id="editor-height-' + id + '" value="' + imgH + '">';
        html += '<input type="hidden" id="editor-width-' + id + '" value="' + imgW + '">';
        html += '<a onclick="mediaPicker(\'editor-' + id + '\',\'line\')" class="btn">Sélectionner une image</a>';

    }
    editor.append(html + "</div></div>");

    richtext();

}


function uniqueSave(el) {

    var type  = getType(el);
    var id    = getId(el, true);
    var name  = getName(el);
    var atype       = type.split(",");
    var href        = false;
    if(atype[0] == "href") {
        href = true;
        type = atype[1];
    }

    var val   = "";

    if(href) {
        val = editor.find("#editor-href-" + id).val();
        el.attr("href", val);
    }

    if(type == "text" || type == "line") {
        val = editor.find("#editor-" + id).val();
        el.html(val);
    }

    if (type == "richtext") {
        var ed  = tinymce.get("editor-" + id);
        val     = ed.getContent();
        el.html(val);
    }
    if (type == "image") {
        var imgW = editor.find("#editor-width-" + id).val();
        var imgH = editor.find("#editor-height-" + id).val();

        val = editor.find("#editor-" + id).val();
        $.post('helper/designer-media', { src: editor.find("#editor-" + id).val(), w: imgW, h: imgH}, function(d) {
            val = d;
            el.attr("src", val);
        });
    }

    editorClose();

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
        //alert('url : ' + url + ' - result : ' + d);
        return d;
    });
}

//------------------------
// UTILITIES
//------------------------

// Fonctions pour recuperer facilement les attributs
function getId(el, as) {
    //console.log(el);
    if(el) {
        var id = el.attr('data-id');
        if(as) id = id.replace(".", "---");
        else id = id.replace("---", ".");
        return id;
    }
}
function getName(el) {
    if(el) return (el.attr('data-name')) ? el.attr('data-name') : el.attr('data-id');
}
function getType(el) {
    var r = "line";
    if(el) {
        if(el.attr('data-type')) r = el.attr('data-type')
        else {
            if(el.get(0).nodeName.toLowerCase() == "img") r = "image";
            //if(el.get(0).nodeName == "a") r = "href";
        }
    }
    return r;
}
// Fonctions pour chercher par ID
function search(el, id) {
    return $(el + "[data-id=" + id + "]");
}

function sizeIframe() {
    var h = $(document).outerHeight(true) + 50;

    var ifra = parent.document.getElementById("designer-iframe");
    if(ifra) ifra.style.height = h+"px";
}


function topBar() {
    /*var ifra = parent.document.getElementById("designer-iframe");

    if(ifra) {
        var h = $(parent.document).scrollTop() - $(ifra).offset().top;
        if(h < 0) h = 0;
        //h = $(ifra).offset().top;//$(parent.document).scrollTop();
        $(".top").css("marginTop", h + "px");
    }*/
}

//-----------------------
// SAUVEGARDE
//-----------------------

function save() {
    var html = document.documentElement.outerHTML;
    //var out = $("<out>" + html + "</out>");
    /*out.find("template").remove();
    var outhtml = out.html();
    //console.log(html);
    console.log(outhtml);*/

    var outhtml = html;

    $.post('helper/designer-save', { id_newsletter: id_newsletter, finalhtml: outhtml, designerhtml: html}, function(d) {
        if(d != 0) {
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
