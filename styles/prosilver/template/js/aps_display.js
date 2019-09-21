/** @function jQuery */
jQuery(function($) {
	let aps = {
		body: $('.aps-body'),
		empty: $('[data-aps-empty-panel]'),
		darken: $('#darkenwrapper'),
		classes: {
			column: '.aps-col',
			content: '.aps-panel-content',
			panel: '.aps-panel',
			pagination: '.aps-panel-footer .pagination'
		},
		add: {
			el: $('.aps-panel-add'),
			class: 'aps-panel-add-pulse',
			content: '.dropdown-contents',
			trigger: '.dropdown-trigger'
		},
		sortable: {
			el: $('[data-aps-sort]'),
			url: 'aps-sort',
			attr: 'data-aps-id',
			data: {
				delay: 150,
				cursor: 'grabbing',
				tolerance: 'pointer',
				handle: '.aps-panel-move',
				placeholder: 'aps-panel-placeholder',
				forcePlaceholderSize: true
			}
		},
		augmentation: {
			selector: '[data-aps-augmentation]'
		},
		charts: {
			selector: '[data-aps-chart]',
			data: {
				chart:	'aps-chart',
				colour:	'aps-colour',
				border:	'aps-colour-border',
				point:	'aps-colour-point',
				label:	'aps-label',
				labels:	'aps-labels',
				time:	'aps-time',
				value:	'aps-value'
			}
		}
	};

	aps.add.pulse = function() {
		this.el.children(this.trigger).toggleClass(this.class, !aps.empty.is(':hidden'));
	};

	aps.ajaxify = function(context) {
		$('[data-ajax]', context).each(function() {
			let $this	= $(this),
				ajax	= $this.data('ajax'),
				filter	= $this.data('filter');

			if (ajax !== 'false') {
				phpbb.ajaxify({
					selector: this,
					callback: ajax !== 'true' ? ajax : null,
					refresh: aps.defined($this.data('refresh')),
					filter: aps.defined(filter) ? phpbb.getFunctionByName(filter) : null
				})
			}
		});
	};

	aps.defined = function(operand) {
		return typeof operand !== 'undefined';
	};

	aps.message = function(title, text, type, time) {
		swal({
			title: title,
			text: text,
			type: type || 'success',
			timer: aps.defined(time) ? time : 1500,
			showConfirmButton: false
		});
	};

	aps.sortable.el.each(function() {
		$(this).sortable($.extend(aps.sortable.data, {
			containment: $(this),
			update: function() {
				$.ajax({
					url: $(this).data(aps.sortable.url),
					type: 'POST',
					data: {
						order: $(this).sortable('toArray', { attribute: aps.sortable.attr }),
					},
					error: function() {
						aps.message(aps.darken.data('ajax-error-title'), aps.darken.data('ajax-error-text'), 'error', null);
					},
					success: function(r) {
						aps.message(r.APS_TITLE, r.APS_TEXT);
					}
				});
			}
		}));
	});

	aps.augmentation.register = function(context) {
		$(aps.augmentation.selector, context).each(function() {
			$(this).on('click', function() {
				let $parent = $(this).parent(),
					$avatar = $parent.parent().siblings('img');

				swal({
					title: $parent.siblings().first().html(),
					html: $(this).next('.hidden').html(),
					imageUrl: $avatar.attr('src'),
					imageAlt: $avatar.attr('alt'),
					imageWidth: $avatar.attr('width'),
					imageHeight: $avatar.attr('height')
				});
			});
		});
	};

	aps.augmentation.register(null);

	aps.charts.draw = function() {
		$(aps.charts.selector).each(function() {
			let data = {
				datasets: [],
				labels: []
			};

			$(this).siblings('.hidden').each(function(i) {
				data.datasets[i] = {
					backgroundColor: [],
					data: []
				};

				$(this).children().each(function() {
					data.datasets[i].data.push($(this).data(aps.charts.data.value));

					let colour	= $(this).data(aps.charts.data.colour),
						border	= $(this).data(aps.charts.data.border),
						point	= $(this).data(aps.charts.data.point),
						label	= $(this).data(aps.charts.data.label),
						labels	= $(this).data(aps.charts.data.labels);

					if (aps.defined(colour)) {
						data.datasets[i].backgroundColor.push(colour);
						data.datasets[i].fill = colour;
					}

					if (aps.defined(border)) {
						data.datasets[i].borderColor = colour;
						data.datasets[i].pointBorderColor = colour;
					}

					if (aps.defined(point)) {
						data.datasets[i].pointBackgroundColor = point;
					}

					if (aps.defined(label)) {
						data.datasets[i].label = label;
					}

					if (aps.defined(labels)) {
						data.labels.push(labels);
					}
				});

				if (!data.datasets[i].backgroundColor.length) {
					data.datasets[i].backgroundColor = palette('tol', data.datasets[i].data.length).map(function(hex) {
						return '#' + hex;
					});
				}
			});

			new Chart($(this), {
				type: $(this).data(aps.charts.data.chart),
				data: data,
				responsive: true,
				maintainAspectRatio: false,
				options: {
					animation: { duration: $(this).data(aps.charts.data.time) },
					legend: { position: 'bottom' }
				}
			});
		});
	};

	/**
	 * Register a chart plugin.
	 * This plugin checks for empty charts.
	 * If the chart is empty, the canvas' data-aps-empty is displayed instead.
	 */
	Chart.plugins.register({
		afterDraw: function(chart) {
			if (!chart.config.data.datasets[0].data.length) {
				let ctx = chart.chart.ctx,
					width = chart.chart.width,
					height = chart.chart.height;

				chart.clear();

				ctx.save();
				ctx.textAlign = 'center';
				ctx.textBaseline = 'middle';
				ctx.fillText(chart.canvas.dataset.apsEmpty, width / 2, height / 2);
				ctx.restore();
			}
		}
	});

	/**
	 * Add a "fake" timeout to the Chart drawing animation,
	 * otherwise the animation does not show up.
	 */
	setTimeout(aps.charts.draw, 0);

	phpbb.addAjaxCallback('aps_add', function(r) {
		if (r.success) {
			$(this).parent('li').remove();

			let panel = $(r.success).insertBefore(aps.empty, null);

			aps.ajaxify(panel);
			aps.add.pulse();
			aps.charts.draw();
			aps.body.trigger('click');
			aps.message(r.APS_TITLE, r.APS_TEXT);
		}
	});

	phpbb.addAjaxCallback('aps_remove', function(r) {
		if (r.success) {
			$(this).parents(aps.classes.column).remove();

			let item = aps.add.el.find(aps.add.content).append(r.success);

			aps.ajaxify(item);
			aps.add.pulse();
			aps.message(r.APS_TITLE, r.APS_TEXT);
		}
	});

	phpbb.addAjaxCallback('aps_replace', function(r) {
		let $old = $(this).parents(aps.classes.panel),
			$new = $(r.body);

		$old.find(aps.classes.content).html($new.find(aps.classes.content));
		$old.find(aps.classes.pagination).html($new.find(aps.classes.pagination));

		aps.augmentation.register($old);
	});
});
