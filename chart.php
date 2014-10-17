<?php
	//ob_start();
    session_start();
	error_reporting(E_ALL ^ E_NOTICE);
	include_once 'forms.php';
	include_once 'cls_bill.php';
	include_once 'cls_codes.php';
    #this will check unauthorized users
    if ($_SESSION['profileid'] ==""){
        echo "<center style='color:red;font-size:20pt;'>Unauthorized access of page is detected</br></br><a style='color:red;font-size:12pt;' href='main_file.php'>Go homepage</a><center>";    
        die;
    }
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Visual Reporting for Monthly Basis</title>
		<form id="selector" method="POST" action="">
			<table>
				<tr>
					<td colspan="2">Select Month and Year for Visual Reporting</td>
				</tr>
				<tr>
					<td>Year/Month&nbsp;&nbsp;<?php cls_forms::vis_report_opt('barangay','reporting');?><!--option here--> </td>
					<td><input type="submit" name="Submit" value="View"></td>
				</tr>
			</table>
		</form>
		

<?php if($_SERVER['REQUEST_METHOD']=='POST'){ ?>
		
<?php
	$parse_data=explode('-',$_POST['year-month']);
	$year=$parse_data[0];
	$month=$parse_data[1];
    //$year = $_POST['year'];
    //$month=$_POST['bulan'];
	$month_str=cls_misc::toString($month,'month');
	$collectibles=cls_bill_get::collect_data($month,$year);
	//echo "collectibles=". $collectibles."<br>";
	$collection=cls_bill_get::collection_data($month,$year);
	//echo "collection=".$collection;
?>
		<script type="text/javascript" src="jquery/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
	var chart;
	$(document).ready(function() {
		chart = new Highcharts.Chart({
			chart: {
				renderTo: 'container',
				type: 'column'
			},
			title: {
				text: "Monthly Comparison of Collectibles/Collection Per Barangay(<?php echo strtoupper('<strong>'.$month_str).'-'.$year.'</strong>'?>)"
			},
			subtitle: {
				text: 'Source: Juban Water System Database'
			},
			xAxis: {
				categories: [
					'BINANUAHAN_TABOC',
					'CATANAGAN',
					'COGON',
					'EMBARCADERO',
					'NORTH_POBLACION',
					'SOUTH_POBLACION',
					'TUGHAN',
				]
			},
			yAxis: {
				min: 0,
				title: {
					text: 'Amount in Pesos(Php)'
				}
			},
			legend: {
				layout: 'horizontal',
				backgroundColor: '#FFFFFF',
				align: 'left',
				verticalAlign: 'top',
				x: 100,
				y: -5,
				floating: true,
				shadow: true
			},
			tooltip: {
				formatter: function() {
					return ''+
						//x_val +': '+ y_val +' Pesos';
						this.x +': '+ this.y +' Pesos';
				}
			},
			plotOptions: {
				column: {
					pointPadding: 0.2,
					borderWidth: 0
				}
			},
				
				series: [{
				name: 'Collectibles',
				data: [<?php echo $collectibles?>]              
	
			}, {
				name: 'Collections',
				data: [<?php echo $collection ?>]
	
			}]
	 });//end here
	});
	
});
		</script>
	</head>
	<body>
<script src="jquery/js/highcharts.js"></script>
<script src="jquery/js/modules/exporting.js"></script>
<br><br><br>
<div id="container" style="min-width: 400px; height: auto; margin: 0 auto"></div>

	</body>
</html>
<?php
	ob_start();
?>

<?php }  ?>