;(function($, window, document, undefined) {
	'use strict';

	$(document).ready(function() {
		var $progress = $('#autodbrestore-progress');
		var frequency = window.autodbrestore_frequency * 60;
		var elapsed = (Date.parse(new Date()) / 1000) - window.autodbrestore_lastrun;
		var seconds = frequency - elapsed;
		setInterval(function() {
			seconds--;

			var width = 100 - ((seconds / frequency) * 100).toFixed(2);
			$progress.width(width + '%');

			// try to reload every 30 seconds after time elapses
			if (seconds === 0 || (seconds < 0 && seconds % 30 === 0)) {
				window.location.reload(true);
			}
		}, 1000);
	});
})(jQuery, window, document);