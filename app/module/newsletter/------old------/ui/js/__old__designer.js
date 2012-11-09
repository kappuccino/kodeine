function start(){

	iframe			= $(view.document.body)
	layout			= iframe.getElements('layout');

	formData		= $('form').getElement('.data');

	// Ajouter le CSS a la iFrame pour afficher le menu
	var cssLink		= document.createElement("link");
	cssLink.href	= "/app/admin/ressource/css/newsleter.editor.css"; 
	cssLink.rel		= "stylesheet"; 
	cssLink.type	= "text/css";
	iframe.appendChild(cssLink);

	panelInit();
}

function panelInit(){

	layout.each(function(l,i){

		// Ajouter le menu de la layout
		var bar = new Element('div', {
			'class': 'del layout-menu clearfix'
		}).inject(l, 'top');

			var menu = new Element('div', {
				'class' : 'menu',
				'html'  : 'menu'
			}).inject(bar);

			var edit = new Element('div', {
				'class' : 'edit',
				'html' : 'edit',
				'events': {
					'click': function(){
						layoutEdit(i);
					}
				}
			}).inject(bar);	

	});

}

function layoutEdit(n){
	formData.empty();

	myLayout = layout[n];

	var ta = [];
//	var temp = myLayout.getElements('singleline, multiline');
	var temp = myLayout.getElements('.item');

	temp.each(function(f){

		new Element('div', {
			'html' : '<b>'+f.get('label')+'</b>'
		}).inject(formData);

		if(f.tagName.toUpperCase() == 'SINGLELINE'){
			var input = new Element('input', {
				'type': 'text',
				'class': 'item',
				'value': f.innerHTML
			});
		}else
		if(f.tagName.toUpperCase() == 'MULTILINE'){

			var taid	= 'm' + Math.round(Math.random()*100000);
			var input	= new Element('textarea', {
				'id': taid,
				'class': 'item',
				'value': f.innerHTML
			});
			
			ta.push(taid);
		}

		input.inject(formData);
	});

	console.log(ta);
	setupEditor(ta.join(','));
}

function apply(){

	fItem = formData.getElements('.item');
	vItem = myLayout.getElements('.item');

	fItem.each(function(item,i){
		vItem[i].set('html', item.value)
	});

}

function setupEditor(ta){

	tinyMCE.init({
		mode								: 'exact',
		elements							: ta,
		theme								: 'advanced',
//		plugins								: 'safari,spellchecker,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
        remove_script_host					: true,
        convert_urls 						: false,
		theme_advanced_buttons1 			: 'code,|,bold,italic,underline,|,cut,copy,paste,|,bullist,|,link,unlink',
//		theme_advanced_buttons3 			: 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
//		theme_advanced_buttons4 			: 'styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak',
		theme_advanced_toolbar_location		: 'top',
		theme_advanced_toolbar_align		: 'left',
//		theme_advanced_statusbar_location	: 'bottom',
		theme_advanced_resizing				: false,

		apply_source_formatting				: false,
        convert_fonts_to_spans				: true,
        forced_root_block 					: 'div'
	});

}