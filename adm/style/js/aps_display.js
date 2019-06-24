jQuery(function($) {
	$('[data-aps-sortable]').sortable({
		axis: 'y',
		containment: $(this).selector,
		cursor: 'move',
		delay: 150,
		handle: '.aps-button-blue',
		forcePlaceholderSize: true,
		placeholder: 'panel',
		tolerance: 'pointer',
	});
});
