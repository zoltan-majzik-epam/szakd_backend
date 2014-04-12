$(function() {
	var graphsURL = "index.php?r=graphs/index";
	$("#stationSelector").change(function() {
		//alert(graphsURL + "?station=" + this.value);
		window.top.location = graphsURL + "&station=" + this.value;
	});
	
	$("#intervalSelector").change(function() {
		//alert(graphsURL + "?interval=" + this.value);
		window.top.location = graphsURL + "&interval=" + this.value;
	});
	
});