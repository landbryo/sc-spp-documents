jQuery(function($){
	$(document).ready(function(){
        //KANIKSU LAND TRUST DOCUMENTS
		$('.documents-output .documents-list').hide();
		
		$('.documents-output .child.documents').hide().addClass('expander');
		
		$('.documents-output .parent.documents').each(function() {
			
			$document = $(this);
			if ( $document.children(".documents-list").length > 0 || $document.children(".child.documents").length > 0 ) {
				$document.addClass('expander').find('h3').click(function() {
					$(this).parent().find('.child.documents').addClass('expander').slideToggle('fast');
					$(this).parent().toggleClass('open');
					$(this).parent().children('.documents-list').slideToggle('fast');
				});
				
			}
        });
    });
});