(function(){
	//Section 1 : Code to execute when the toolbar button is pressed
	var a = {
		exec:function(editor){

			console.log('EXEC', editor)
			mediaPicker(editor.name, 'mce');

		}
	}
	//Section 2 : Create the button and add the functionality to it
	CKEDITOR.plugins.add('kodeineimg',{
		init:function(editor){
			editor.addCommand('kodeineimg',a);
			editor.ui.addButton('kodeineimg',{
				label:'Kodeine Media',
				//icon: this.path + 'kodeineimg.png',
				command: 'kodeineimg'
			});
		//	console.log('INIT', this.path);
		}
	});
})();