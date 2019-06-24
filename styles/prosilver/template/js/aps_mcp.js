jQuery(function($) {
	let $action = $('#action'),
		$points = $('#points'),
		$reason = $('#reason');

	let apsChangeAction = function() {
		let $selected = $(this).find(':selected'),
			points = $selected.data('points'),
			reason = $selected.data('reason');

		if (points || $selected.context === undefined) {
			$points.prop('disabled', 'disabled').val(points);
			$reason.prop('disabled', 'disabled').val(reason);
		} else {
			$points.prop('disabled', false).val('');
			$reason.prop('disabled', false).val('');
		}
	};

	apsChangeAction();

	$action.on('change', apsChangeAction);
});
