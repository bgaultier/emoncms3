<!DOCTYPE html>
<html>
 <!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  -->
  <?php
		$path = dirname("http://".$_SERVER['HTTP_HOST'].str_replace('Vis/kwhdcomparison', '', $_SERVER['SCRIPT_NAME']))."/";
		
		$kwhd = $_GET['kwhd'];	//Get the table ID so that we know what graph to draw
		$apikey = $_GET['apikey'];
		$month = $_GET['month'];
		$year = $_GET['year'];
  ?>
	
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<script type="text/javascript" src="<?php echo $path; ?>Vis/flot/jquery.js"></script>
		<script type="text/javascript" src="<?php echo $path; ?>Vis/kwhdcomparison/kwhdcomparison_functions.js"></script>
		<script type="text/javascript" src="<?php echo $path; ?>Vis/d3/d3.js?2.7.1"></script>
		<script type="text/javascript" src="<?php echo $path; ?>Vis/d3/d3.layout.js?2.7.1"></script>
		<script type="text/javascript" src="<?php echo $path; ?>Vis/d3/d3.time.js?2.7.1"></script>
		<script type="text/javascript" src="<?php echo $path; ?>Vis/d3/d3.geom.js?2.7.1"></script>
	</head>
	
	<style type='text/css'>

	.chart {
	  margin-left: 42px;
	  font: 10px sans-serif;
	  shape-rendering: crispEdges;
	}

	.chart div {
	  background-color: #0096ff;
	  text-align: right;
	  padding: 3px;
	  margin: 1px;
	  color: white;
	}

	.chart rect {
	  stroke: #0095ff;
	  fill: #0095ff;
	  stroke-width: 2;
	  fill-opacity: 0.4;
	  stroke-linejoin : round;
	}

	.chart text.bar {
	  fill: white;
	}
	
	body {
	  font: 10px sans-serif;
	}

	.rule line {
	  stroke: #eee;
	  shape-rendering: crispEdges;
	}

	.rule line.axis {
	  stroke: #000;
	}
	
	.slider line {
		stroke: white;
		stroke-width: 10;
		cursor: pointer;
	}
	
	.comparison {
		background: none repeat scroll 0 0 #FFFFFF;
		border: 1px solid #E5E5E5;
		box-shadow: 0 4px 10px -1px rgba(200, 200, 200, 0.7);
		padding : 20px;
		margin-left : auto;
		margin-right:auto;
		text-align: center;
  }

	</style>
	
	<body>
		<script type="text/javascript">
			var kwhd = "<?php echo $kwhd; ?>";				//Fetch table name
			var path = "<?php echo $path; ?>";
			var apikey = "<?php echo $apikey; ?>";
			var month = "<?php echo $month; ?>";
			var year = "<?php echo $year; ?>";
			
			var today = new Date();
			if(!month)
				month = today.getMonth();
			if(!year)
				year = today.getFullYear();
				
			d3.select("body")
				.append("div")
				.attr("id", "container");
				
			var container = d3.select("#container")
							.append("div")
							.attr("id", "charts")
							.attr("style", "float : left; width : 600px;");
							
			d3.select("#container")
				.append("div")
				.attr("id", "placeholder")
				.attr("style", "float : left; height : 526px; width : 300px;");
				
			var container1 = container
				.append("div")
				.attr("id", "#container1")
				.attr("style", "width : 600px; height : 264px;");
				
			var container2 = container
				.append("div")
				.attr("id", "#container2")
				.attr("style", "width : 600px; height : 264px;");
				
			d3.select("#placeholder")
				.append("div")
				.attr("id", "day1")
				.attr("style", "width : 100%; height : 176px;");
				
			d3.select("#placeholder")
				.append("div")
				.attr("id", "comparison")
				.attr("style", "width : 100%; height : 176px;");
			
			d3.select("#placeholder")
				.append("div")
				.attr("id", "day2")
				.attr("style", "width : 100%; height : 176px;");
			
			plotChart(container1, 1, path, kwhd, apikey, month-1, year);
			
			plotChart(container2, 2, path, kwhd, apikey, month, year);
			
    </script>
	</body>
	
</html> 
