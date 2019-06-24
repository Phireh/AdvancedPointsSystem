jQuery(function($) {
	let $button = $('#aps_points_copy_ajax'),
		$select = $('#aps_points_copy'),
		$form	= $button.parents('form'),
		action	= $form.attr('action').replace('&amp;', '&');

	let callback = 'aps_points_copy',
		$dark = $('#darkenwrapper');

	$button.on('click', function(e) {
		/**
		 * Handler for AJAX errors
		 */
		function errorHandler(jqXHR, textStatus, errorThrown) {
			if (typeof console !== 'undefined' && console.log) {
				console.log('AJAX error. status: ' + textStatus + ', message: ' + errorThrown);
			}
			phpbb.clearLoadingTimeout();
			let responseText, errorText = false;
			try {
				responseText = JSON.parse(jqXHR.responseText);
				responseText = responseText.message;
			} catch (e) {}
			if (typeof responseText === 'string' && responseText.length > 0) {
				errorText = responseText;
			} else if (typeof errorThrown === 'string' && errorThrown.length > 0) {
				errorText = errorThrown;
			} else {
				errorText = $dark.attr('data-ajax-error-text-' + textStatus);
				if (typeof errorText !== 'string' || !errorText.length) {
					errorText = $dark.attr('data-ajax-error-text');
				}
			}
			phpbb.alert($dark.attr('data-ajax-error-title'), errorText);
		}

		let request = $.ajax({
			url: action,
			type: 'post',
			data: {'aps_action': 'copy', 'aps_points_copy': $select.val()},
			success: function(response) {
				/**
				 * @param {string} response.MESSAGE_TITLE
				 * @param {string} response.MESSAGE_TEXT
				 */
				phpbb.alert(response.MESSAGE_TITLE, response.MESSAGE_TEXT);

				if (typeof phpbb.ajaxCallbacks[callback] === 'function') {
					phpbb.ajaxCallbacks[callback].call(this, response);
				}
			},
			error: errorHandler,
			cache: false
		});

		request.always(function() {
			let $loadingIndicator = phpbb.loadingIndicator();

			if ($loadingIndicator && $loadingIndicator.is(':visible')) {
				$loadingIndicator.fadeOut(phpbb.alertTime);
			}
		});

		e.preventDefault();
	});

	phpbb.addAjaxCallback('aps_points_copy', function(response) {
		$select.val(0);

		/**
		 * @param {array} response.APS_VALUES
		 */
		$.each(response.APS_VALUES, function(name, value) {
			$('#' + name).val(value);
		});
	});

	phpbb.addAjaxCallback('aps_points_reset', function() {
		$('[name*="aps_values"]').each(function() {
			$(this).val(0);
		});
	});
});
