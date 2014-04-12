$(document).ready(function() {

	setGraph($('#weather-container'), 'weather');

	setInterval(function() {
		setGraph($('#weather-container'), 'weather');
	}, 1000 * 60);


	$('#dateSelector').datepicker({
		inline: true,
		dateFormat: 'yy-mm-dd',
		onSelect: function(dateText, inst) {
			window.location.href = 'index.php?r=graphs/index&time=' + dateText;
		}
	});
	$('#SelectDate').live("click", function() {
		$('#dateSelector').slideToggle(150);
	});
	$('.cont').click(function() {
		if ($('#dateSelector').css("display") == "block") {
			$('#dateSelector').slideToggle(150);
		}
	})
	
	/*$('#dateSelector').datepicker({
		inline: true,
		dateFormat: 'yy-mm-dd',
		onSelect: function(dateText, inst) {
			window.location.href = 'index.php?r=graphs/index&time=' + dateText;
		}
	});*/
	
	
});


