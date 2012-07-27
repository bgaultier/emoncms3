CKEDITOR.dialog.add( 'e3map', function( editor ) {

	return {
		title : 'Insert map dialog',
		minWidth : 560,
		minHeight : 580,
		onOk : function() {
			max = this.getContentElement('tab1','max').getValue();
			kwhdmax = this.getContentElement('tab1','kwhdmax').getValue();
			ckeditor_widget_id ++;
			editor.insertHtml("<div class='map' id='"+ckeditor_widget_id+"' max='"+max+"' kwhdmax='"+kwhdmax+"' style='width:560px; height:580px;'></div>");    
		},
		contents : [
			{
			id : 'tab1',
			label : '',
			title : '',
			elements :[
				{  id : 'max', type : 'text', label : 'Max', labelLayout: 'horizontal' },
				{  id : 'kwhdmax', type : 'text', label : 'kWh/d max', labelLayout: 'horizontal' }
			]
			}
		]
	};
} );
