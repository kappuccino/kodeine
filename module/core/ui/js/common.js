$(document).ready(function() {

	if ($('.sortable').length > 0) {
	    datatbl = $('.sortable').dataTable({
			"bInfo": false,
			"bPaginate" : false,
			"bAutoWidth" : false,
			"oLanguage": {
				"sSearch": "Filtrer les résultats",
				"sInfo": ""
			},
			"aoColumns": aoCol($('.sortable'))
	    });
	    
	    $('.dataTables_filter').css('display', 'none');

		//$('th').last().append($('.upper'));		
    	//$('th').last().append('<input type="text" class="input-small nomargin" placeholder="filtrer..." id="filter" style="float:right"/>');
		
		$('#filter').bind('keypress keydown keyup', function() {
			// On a un filter que si une table, pos 0
			datatbl.fnFilter($('#filter').val());
    	});	
    	$('#filter').on('click', function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    	});
    	
	}
	
	subNavOnDemand();


	if($('#sub_nav').length > 0){
		if($('#sub_nav').hasClass('notOnTop')) return;

		var app  = $('#app');
		var menu = $('#sub_nav'),
			pos  = menu.offset();
		
		$(window).scroll(function(){
		//	console.log($(this).scrollTop(), pos.top+menu.height());

			if($(this).scrollTop() > pos.top/*+menu.height()*/ && !menu.hasClass('fixed')){
			//	menu.fadeOut('fast', function(){
					menu.removeClass('default').addClass('fixed'); //fadeIn('fast');
					app.css('padding-top', menu.height());
			//	});
			} else
			if($(this).scrollTop() <= pos.top && menu.hasClass('fixed')){
			//	menu.fadeOut('fast', function(){
					menu.removeClass('fixed').addClass('default'); //fadeIn('fast');
					app.css('padding-top', 0);
			//	});
			}
		});
	}

	$('a[data-ajaxhandler]').on('click', function(e) {
		var el = $(this).attr('data-ajaxhandler');
		kajaxHandler($(el), $(this));
	});



});

function subNavOnDemand(){
	$('#sub_nav ul.left').append($('.inject-subnav-left').html());
	$('.inject-subnav-left').remove();
	
	$('#sub_nav ul.right').append($('.inject-subnav-right').html());
	$('.inject-subnav-right').remove();

	if($('#sub_nav').hasClass('icon') && $('#sub_nav .dropdown-toggle').length > 0){
		$('#sub_nav .dropdown-toggle').removeClass('btn-mini');
	}
}

function aoCol(item) {
	var aoCol = [];
	
	$.each(item.find('tr:eq(1) td'), function(k,v) {
		var attr = $(v).attr("data-raw");

		if (typeof attr !== 'undefined') {
			aoCol.push({ "sSortDataType": "data-raw", "sType": "numeric" });
		} else {
			aoCol.push(null);
		}
		
	});	
	return aoCol;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
$(function() {
	
	$.each( $('.toggle'), function() {
		if ($(this).children('input[type="checkbox"]').is(':checked'))
			toggleCheck($(this));
	});
	
	
	$('.toggle').bind('click', function() {
		
		if ($(this).children('input[type="checkbox"]').is(':checked')) {
			toggleUncheck($(this));
		}
		else {
			toggleCheck($(this));
		}
	});

});

toggleUncheck = function(item) {
	$checkbox = item.children('input[type="checkbox"]');
	item.find('img').animate({
		'left' : '0px'
	}, 218, function() {
		$checkbox.attr('checked', false);	
	});
}

toggleCheck = function(item) {
	$checkbox = item.children('input[type="checkbox"]');
	item.find('img').animate({
		'left' : '-27px'
	}, 218, function() {
		$checkbox.attr('checked', true);
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function cbchange(that) {
	var state = that.prop('checked');
	if (state) {
		$('.cb').prop('checked', true).siblings('label').addClass('ui-state-active');
	} else {
		$('.cb').prop('checked', false).siblings('label').removeClass('ui-state-active');
	}
}
function cschange(that) {
	var state = that.prop('checked');
	if (state) {
		$('.cs').prop('checked', true).siblings('label').addClass('ui-state-active');
	} else {
		$('.cs').prop('checked', false).siblings('label').removeClass('ui-state-active');
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mediaPicker(id, back){
	window.open('../media/index?popMode=1&field='+id+'&method='+back, 'pick', 'height=600,width=550;');
}
function mediaOpen(method,field){
	window.open('../media/index?field='+field+'&method='+method, 'filemanager', 'scrollbars=yes,status=yes,resizable=yes,width=950,height=800');
}
function insertRichEditor(editor_id, code){

	if (CKEDITOR) {
		CKEDITOR.instances[editor_id].insertHtml(code);
	} else {
		tinyMCE.execInstanceCommand(editor_id, "mceInsertContent", false, code, true);
	}
}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function filterToggle(mod){

	var open = ($('.quickForm').css('display') == 'none') ? true : false;
	$('.quickForm').css('display', ((open) ? '' : 'none'));

	$.ajax({
		url: '../core/helper/filter-open',
		data: {
			mod: mod,
			open: open
		}
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function recherche(f){
	$('.sniff').each(function(me){
		
		// http://stackoverflow.com/questions/1789945/javascript-string-contains
		if ($(this).text().toLowerCase().indexOf(f.val().toLowerCase()) != -1) {
			$(this).parent().css('display', '');
		} else {
			$(this).parent().css('display', 'none');
		}
		
		/*var patt = new RegExp($(this).text(), "g");
		
		if( !patt.test(f.val()) ){
			$(this).parent().css('display', 'none');
		}else{
			$(this).parent().css('display', '');
		}*/
		
		if ( f.val() == "" ) $('.sniff').parent().css('display', '');
	});
}
	
function liveUrlTitle(NewText){
	
	NewText = NewText.toLowerCase();
	var separator = "-";
	
	if (separator != "-"){
		NewText = NewText.replace(/\_/g, separator);
	}else{
		NewText = NewText.replace(/\-/g, separator);
	}

	// Foreign Character Attempt
	var NewTextTemp = '';
	for(var pos=0; pos<NewText.length; pos++){
		var c = NewText.charCodeAt(pos);
		
		if (c >= 32 && c < 128){
			NewTextTemp += NewText.charAt(pos);
		}else{
			if(c == '223'){ NewTextTemp += 'ss'; continue;}
			if(c == '224'){ NewTextTemp += 'a'; continue;}
			if(c == '225'){ NewTextTemp += 'a'; continue;}
			if(c == '226'){ NewTextTemp += 'a'; continue;}
			if(c == '229'){ NewTextTemp += 'a'; continue;}
			if(c == '227'){ NewTextTemp += 'ae'; continue;}
			if(c == '230'){ NewTextTemp += 'ae'; continue;}
			if(c == '228'){ NewTextTemp += 'ae'; continue;}
			if(c == '231'){ NewTextTemp += 'c'; continue;}
			if(c == '232'){ NewTextTemp += 'e'; continue;}
			if(c == '233'){ NewTextTemp += 'e'; continue;}
			if(c == '234'){ NewTextTemp += 'e'; continue;}
			if(c == '235'){ NewTextTemp += 'e'; continue;}
			if(c == '236'){ NewTextTemp += 'i'; continue;}
			if(c == '237'){ NewTextTemp += 'i'; continue;}
			if(c == '238'){ NewTextTemp += 'i'; continue;}
			if(c == '239'){ NewTextTemp += 'i'; continue;}
			if(c == '241'){ NewTextTemp += 'n'; continue;}
			if(c == '242'){ NewTextTemp += 'o'; continue;}
			if(c == '243'){ NewTextTemp += 'o'; continue;}
			if(c == '244'){ NewTextTemp += 'o'; continue;}
			if(c == '245'){ NewTextTemp += 'o'; continue;}
			if(c == '246'){ NewTextTemp += 'oe'; continue;}
			if(c == '249'){ NewTextTemp += 'u'; continue;}
			if(c == '250'){ NewTextTemp += 'u'; continue;}
			if(c == '251'){ NewTextTemp += 'u'; continue;}
			if(c == '252'){ NewTextTemp += 'ue'; continue;}
			if(c == '255'){ NewTextTemp += 'y'; continue;}
			if(c == '257'){ NewTextTemp += 'aa'; continue;}
			if(c == '269'){ NewTextTemp += 'ch'; continue;}
			if(c == '275'){ NewTextTemp += 'ee'; continue;}
			if(c == '291'){ NewTextTemp += 'gj'; continue;}
			if(c == '299'){ NewTextTemp += 'ii'; continue;}
			if(c == '311'){ NewTextTemp += 'kj'; continue;}
			if(c == '316'){ NewTextTemp += 'lj'; continue;}
			if(c == '326'){ NewTextTemp += 'nj'; continue;}
			if(c == '353'){ NewTextTemp += 'sh'; continue;}
			if(c == '363'){ NewTextTemp += 'uu'; continue;}
			if(c == '382'){ NewTextTemp += 'zh'; continue;}
			if(c == '256'){ NewTextTemp += 'aa'; continue;}
			if(c == '268'){ NewTextTemp += 'ch'; continue;}
			if(c == '274'){ NewTextTemp += 'ee'; continue;}
			if(c == '290'){ NewTextTemp += 'gj'; continue;}
			if(c == '298'){ NewTextTemp += 'ii'; continue;}
			if(c == '310'){ NewTextTemp += 'kj'; continue;}
			if(c == '315'){ NewTextTemp += 'lj'; continue;}
			if(c == '325'){ NewTextTemp += 'nj'; continue;}
			if(c == '352'){ NewTextTemp += 'sh'; continue;}
			if(c == '362'){ NewTextTemp += 'uu'; continue;}
			if(c == '381'){ NewTextTemp += 'zh'; continue;}
		}
	}

	NewText = NewTextTemp;
	
	NewText = NewText.replace('/<(.*?)>/g', '');
	NewText = NewText.replace('/\&#\d+\;/g', '');
	NewText = NewText.replace('/\&\#\d+?\;/g', '');
	NewText = NewText.replace('/\&\S+?\;/g','');
	NewText = NewText.replace(/[\"\?\.\!*$\#@%;:,=\(\)\[\]]/g,'');
	NewText = NewText.replace(/[']/g, separator);
	NewText = NewText.replace(/\s+/g, separator);
	NewText = NewText.replace(/\//g, separator);
	NewText = NewText.replace(/[^a-z0-9-_]/g,'');
	NewText = NewText.replace(/\+/g, separator);
	NewText = NewText.replace(/[-_]+/g, separator);
	NewText = NewText.replace(/\&/g,'');
	NewText = NewText.replace(/-$/g,'');
	NewText = NewText.replace(/_$/g,'');
	NewText = NewText.replace(/^_/g,'');
	NewText = NewText.replace(/^-/g,'');
	
	/*if (document.getElementById("url_title")){
		document.getElementById("url_title").value = "" + NewText;			
	}else{
		document.forms['entryform'].elements['url_title'].value = "" + NewText; 
	}*/

	return NewText;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mediaInsert(id, data, method){
console.log(id, data, method)
	if(method == 'sort'){
		var li 		= $('<li />').appendTo($('#'+id+'-list'));
		var action	= $('<div class="action clearfix" />').appendTo(li);
		var move	= $('<span class="move" />').appendTo(action);
		var info	= $('<span class="info" />').appendTo(action);				
		var remove 	= $('<a class="remove" />').appendTo(action);
		var view	= $('<div class="media-view" />').appendTo(li);
		
		mediaEnableSort('#'+id, false);
		mediaView(view, data);
		mediaSerialize('#'+id);
	}
}

function buildRichEditor(){
	if(!useEditor) return false
	tinyMCE.init({
		mode		: 'exact',
		elements	: textarea,
		theme		: 'advanced',
		plugins		: 'safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',

        remove_script_host		: true,
        convert_urls 			: false,
		theme_advanced_buttons1 : 'mybutton,code,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect',
		theme_advanced_buttons2 : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,|,insertdate,inserttime,preview,|,forecolor,backcolor',
		theme_advanced_buttons3 : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
		theme_advanced_buttons4 : 'styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak',
	
		theme_advanced_toolbar_location		: 'top',
		theme_advanced_toolbar_align		: 'left',
		theme_advanced_statusbar_location	: 'bottom',
		theme_advanced_resizing				: false,
	
		// Example content CSS (should be your site CSS)
		content_css		: '../core/helper/tinymce',

		// Custom FORMAT
		style_formats 	: MceStyleFormats,

		// Drop lists for link/image/media/template dialogs
	//	template_external_list_url	: "js/template_list.js",
	//	external_link_list_url 		: "js/link_list.js",
	//	external_image_list_url 	: "js/image_list.js",
	//	media_external_list_url 	: "js/media_list.js",
		
		setup : function(ed) {
		    ed.addButton('mybutton', {
		        title : 'Insérer des images',
		        image : '../core/ui/img/_img/myb.gif',
		        onclick : function() {
					mediaPicker(ed.id, 'mce');
		        }
		    });
		}
	
	});
}

function toggleSlider(that, callbackON, callbackOFF) {

	var displacement = 0;
	
	if (that.hasClass('toggleslider-small')) {
		displacement = 41;
	}

	if (that.hasClass('on')) {
		
		if (typeof callbackOFF === 'function' || typeof callbackOFF === 'object') {
			callbackOFF.call(that, false);
		} else {
			callbackON.call(that, false);
		}		
		
		that.find('.slide img').css('left', '');
		that.removeClass('on').addClass('off');
		
	} else {
		
		callbackON.call(that, true);
	
		that.find('.slide img').css('right', '');
		that.removeClass('off').addClass('on');
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function jumpMenu(targ,selObj,restore){
	eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
	if (restore) selObj.selectedIndex=0;
}


function kajaxHandler(el, btn) {

	// INIT
	el.css('display', 'block');
	var url          = el.attr('data-url');
	var innersaved   = el.html();

	if (url.length == 0) return console.log('No url specified');
	var _attrs = el[0].attributes;
	var patt   = /^data-/;
	var attrs  = {};
	btn.hide();

	for(var k in _attrs) {
		if (typeof _attrs[k].name !== 'string') continue;
		if (_attrs[k].name.match(patt)) {
			if (_attrs[k].name != 'data-url')
				attrs[_attrs[k].name.substring(5)] = _attrs[k].nodeValue;
		}
	}

	el.wrapInner('<form id="k-ajax-form"></form>');

	for (var attr in attrs) {
		var hid = $('<input type="hidden" name="'+attr+'" value="'+attrs[attr]+'" />').appendTo('#k-ajax-form');
	}

	// BINDS
	var cancel = el.find('a[data-action="cancel"]').on('click', function() {
		el.css('display', 'none');
		el.html(innersaved);
		btn.show();
	});

	// AJAX
	var go = el.find('a[data-action="go"]').on('click', function() {
		var ser = $('#k-ajax-form').serialize();
		$.ajax({
			url     : url,
			data    : ser,
			type    : 'POST',
			dataType: 'json'
		}).done(function(r) {
			if (!r.success) {
				alert('Une erreur s\'est produite sur le serveur.');
				console.log(r);
			} else {
				if (r.reload) window.location.reload();
				cancel.trigger('click');
			}
		}).error(function(xhr, status, error) {
			console.log("Error : "+error+" status : "+status);
			cancel.trigger('click');
		});
	});

}