var setProcessing = function(container) {
	container.html('<img src="images/loader.gif" style="position:relative;margin-top:130px;" />');
};

var unsetProcessing = function(container) {
	container.html('');
};


var setGraph = function(container, graph) {
	if (container.length > 0) {
		$.ajax({
			type: 'GET',
			data: {
				'url': '/index.php',
				'r': 'graphsApi/getseries',
				'graph': graph
			},
			processData: true,
			dataType: 'json',
			success: function(series) {

				var width = document.body.clientWidth;

				if (series.emptyData) {
					placeMessageOnGraph(container, series.emptyDataMessage);
					return;
				}

				for (var i = 0; i < series.series.length; i++) {
					for (var j = 0; j < series.series[i].data.length; j++) {
						if (series.series[i].data[j].stringValue) {
							// New scope for strValue
							(function() {
								var strValue = series.series[i].data[j].stringValue;
								series.series[i].data[j].dataLabels = {
									enabled: true,
									formatter: function() {
										return strValue;
									},
									y: -10
								};
							})();
						}
						//console.log(series.series[i].data[j])
					}
				}

				//remove this if the graphs are corrupted
				//--------------------------------
				Highcharts.setOptions({
					global: {
						useUTC: false
					}
				});
				//-----------------------------

				new Highcharts.Chart({
					chart: {
						renderTo: container.attr('id'),
						backgroundColor: "#f7f7f7"
					},
					title: {
						text: series.title
					},
					subtitle: {
						text: series.subtitle
					},
					xAxis: {
						type: 'datetime'
					},
					series: series.series,
					yAxis: series.yaxis,
					plotOptions: {
						series: {
							lineWidth: 1,
							animation: false,
							shadow: false,
							marker: {
								enabled: false
							},
							turboTreshold: 2000,
						},
						area: {
							fillOpacity: 0.5
						}
					},
					tooltip: {
						formatter: function() {
							return formatTooltip(this);
						},
						style: {
							fontSize: '8pt',
							fontFamily: 'Arial',
							lineHeight: '11pt',
							padding: '5pt'
						}
					}
				});
			},
			error: function(jqXHR, textStatus, errorThrown) {
				placeMessageOnGraph(container, null);
			}
		});
	}
};

var placeMessageOnGraph = function(container, message) {
	if (message == null)
		message = "No data in selected interval!";
	container.html("<p style='font-size:1.7em;position:relative;top:130px;text-align:center;color:#DD8888;'>" + message + "</p>");
};


function formatTooltip(point) {
	var d = new Date(point.x);
	var tt = '<span>'; //tooltip
	tt += d.toLocaleString();
	console.log(point);
	tt += '<br /><span style="color: ' + point.series.color + '; font-weight: bold; font-size:10pt; ">' + point.series.name + ": " + Math.round(point.y * 100000) / 100000 + "</span>";
	//console.log(tt);
	if ("text" in point.point) {
		tt += '<br /><span style="font-style: italic;">' + point.point.text + "</span>";
	}
	return tt;
}