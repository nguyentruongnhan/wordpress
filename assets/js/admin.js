(function($) {

	$(function() {
		$('.cw-slider').each(function() {
			initCWSlider(this);
		});

		$(document).on('mouseenter', '.cw-slider-ui', function() {
			if ( !$(this).data('cw_initialized') ) {
				initCWSlider($(this).siblings('.cw-slider').get(0));
			}
		});

		function initCWSlider(el) {
			var jelm = $(el), value = parseInt(jelm.val()),
				min = parseInt(jelm.attr('min')), max = parseInt(jelm.attr('max')), step = parseInt(jelm.attr('step')),
				jslider = jelm.siblings('.cw-slider-ui').length ? jelm.siblings('.cw-slider-ui') : $('<span class="cw-slider-ui" />');

			jslider.insertAfter(jelm);
			jslider.slider({
				min: min,
				max: max,
				step: step,
				value: value,
				slide: function() {
					jelm.val(jslider.slider('value'));
				},
				change: function() {
					jelm.val(jslider.slider('value'));
				}
			});
			jslider.data('cw_initialized', true);
		}
	});

})(jQuery);