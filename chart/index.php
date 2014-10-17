<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>JWS Reports</title>

		<script type="text/javascript" src="chart/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
	var chart;
	$(document).ready(function() {
		chart = new Highcharts.Chart({
			chart: {
				renderTo: 'container',
				type: 'line',
				marginRight: 130,
				marginBottom: 25
			},
			title: {
				text: 'Comparison of Collection and Collectibles',
				x: -20 //center
			},
			subtitle: {
				text: 'Juban Water System Report',
				x: -20
			},
			xAxis: {
				categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
					'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
			},
			yAxis: {
				title: {
					text: 'Amount(P)'
				},
				plotLines: [{
					value: 0,
					width: 1,
					color: '#808080'
				}]
			},
			tooltip: {
				formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ this.y +'P';
				}
			},
			legend: {
				layout: 'vertical',
				align: 'right',                                                            
				verticalAlign: 'top',
				x: -10,
				y: 100,
				borderWidth: 0
			},
			series: [{
				name: 'BINANUAHAN_TABOC',
				data: [<?php if(end())?>]
			},{
				name: 'CATANAGAN',
				data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
			},{
                name: 'COGON',
                data: [-0.5, -0.8, -5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]               
            },{ 
                name: 'EMBARCADERO',
                data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]                 
            },{  
                name: 'NORTH_POBLACION',
                data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]              
            },{  
                name: 'SOUTH_POBLACION',
                data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]               
            },{  
                name: 'TUGHAN',
                data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]               
            }	
			]
		});
	});
	
});
		</script>
	</head>
	<body>
<script src="chart/js/highcharts.js"></script>
<script src="chart/js/modules/exporting.js"></script>

<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>
