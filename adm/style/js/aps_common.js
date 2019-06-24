jQuery(function($) {
	$('.aps-names-toggle').on('click', function() {
		$(this).parents('fieldset').toggleClass('aps-points-full');
		let altText = $(this).data('text');
		$(this).data('text', $(this).text()).text(altText);
	});

	$('#aps_points_icon').iconpicker({
		collision: true,
		placement: 'bottomRight',
		component: '#aps_points_icon + i',
	});
});
