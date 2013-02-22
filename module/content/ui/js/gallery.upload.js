$(function() {

	// Ban safari 5
	isSafari		= (/safari/.test(navigator.userAgent.toLowerCase())) ? true : false;
	isSafariFive	= (isSafari && /version\/5/.test(navigator.userAgent.toLowerCase())) ? true : false;

	/* Mettre a jour les path d'upload si déjà chargé */
	//uploadPath	= root + $('#path').attr('data-url');

	// SI ON A ACCES AU FILEREADER DU BROWSER
	if(typeof FileReader !== 'undefined' && !isSafariFive) {

		$('#file_upload').uploadifive({
			'buttonText'   : 'Parcourir',
			'auto'         : true,
			'formData'     : {
				id_album: id_album
			},
			'queueID'      : 'queue',
			'uploadScript' : 'helper/gallery-upload',

			'onUpload' : function(){
			},

			'onSelect'     : function(event, ID, fileObj) {
			},

			'onDrop' : function(file, count) {
			},

			'onUploadComplete' : function(file, data) {
				//alert($('#path').attr('data-url'));
			},

			'onQueueComplete' : function() {
			//	$('#queue').empty();
			}
		});

	}else{
		alert('En raison d\'un bug inhérent à la version de votre navigateur, l\'upload de fichiers est indisponible. Merci de mettre à jour votre navigateur.');
	}

});