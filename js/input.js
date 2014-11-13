(function($){
	
	
	function initialize_field($el) {
		
		//console.log($el);
		
	}
	
	
	if(typeof acf.add_action !== 'undefined') {
	
		/*
		*  ready append (ACF5)
		*
		*  These are 2 events which are fired during the page load
		*  ready = on page load similar to $(document).ready()
		*  append = on new DOM elements appended via repeater field
		*
		*  @type	event
		*  @date	20/07/13
		*
		*  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
		*  @return	n/a
		*/
		
		acf.add_action('ready append', function($el) {
			
			// search $el for fields of type 'google_fonts'
			acf.get_fields({ type : 'google_fonts'}, $el).each(function() {
				
				initialize_field( $(this) );
				
			});
			
		});
		
		
	}
	else {
		
		
		/*
		*  acf/setup_fields (ACF4)
		*
		*  This event is triggered when ACF adds any new elements to the DOM. 
		*
		*  @type	function
		*  @since	1.0.0
		*  @date	01/01/12
		*
		*  @param	event		e: an event object. This can be ignored
		*  @param	Element		postbox: An element which contains the new HTML
		*
		*  @return	n/a
		*/
		
		$(document).live('acf/setup_fields', function(e, postbox) {
			
			$(postbox).find('.field[data-field_type="google_fonts"]').each(function() {
				
				initialize_field( $(this) );
				
			});
		
		});
	
	
	}


})(jQuery);

(function($) {

	$(document).ready(function() {
		$('select.google_fonts.fonts')
			.on('change.acf-google_fonts', function(ev) {
				var $selFonts = $(ev.target);
				var $box = $selFonts.closest('.google_fonts.box');
				var $selWeights = $box.find('select.google_fonts.font-weights');
				var $selStyles = $box.find('select.google_fonts.font-styles');
				var variants = $.parseJSON($selFonts.find('option:selected').attr('data-variants'));

				$selWeights[0].length = 1;
				for(var weight in variants) {
					var opt = new Option(weight == '400' ? '400 - regular' : weight, weight, false, weight == '400' ? true : false);

					if($selWeights.attr('data-stored-value') == weight) {
						opt.defaultSelected = true;
					}

					$selWeights[0].options[$selWeights[0].length] = opt;
				}

				$selWeights
					.off('change.acf-google_fonts')
					.on('change.acf-google_fonts', variants, function(ev) {
						if(ev.target.selectedIndex == 0)
							return;

						var styles = variants[ev.target.value];

						$selStyles[0].length = 1;
						for (var i = 0; i < styles.length; i++) {
							var style = styles[i];
							var opt = new Option(style, style, false, style == 'normal' ? true : false);
							
							if($selStyles.attr('data-stored-value') == style) {
								opt.defaultSelected = true;
							}

							$selStyles[0].options[$selStyles[0].length] = opt;
						};
					})
					.trigger('change.acf-google_fonts');

				//console.log();
			})
			.trigger('change.acf-google_fonts');
	});

})(jQuery);