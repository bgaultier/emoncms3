<!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->
<?php
	global $path;
	
	$id = $feed[0];
	$name = $feed[1];
	
?>

<script type="text/javascript" src="<?php print $path; ?>Vis/flot/jquery.js"></script>

<div class='lightbox' style="margin-bottom:20px; margin-left:3%; margin-right:3%;">
  <h2>Over time comparison reports : <?php echo $name; ?></h2>

<div id="reports">
</div>

</div>

<script type="application/javascript">

  var path = "<?php echo $path; ?>";
  var feedid = <?php echo $id; ?>;

  update_reports();
  setInterval(update_reports,2000);

  function update_reports()
  {
    $.ajax({
		url: path+"feed/comparison.json?id="+feedid,
		dataType: 'json',
		success: function(data) {
			
			reports = data;
			
			var out = "";
			
			var red = "rgb(255,125,20)"
				orange = "rgb(240,180,20)"
				green = "rgb(192, 227, 146)";
				
			var now = new Date();
			
			for (z in reports)
			{
				var color;
				var title;
				var value;
				
				var month=new Array();
				month[0]="January";
				month[1]="February";
				month[2]="March";
				month[3]="April";
				month[4]="May";
				month[5]="June";
				month[6]="July";
				month[7]="August";
				month[8]="September";
				month[9]="October";
				month[10]="November";
				month[11]="December";
				
				if(z == "day")
					title = "Yesterday &rarr; Today";
				if(z == "month")
					title = month[now.getMonth() - 1] + " &rarr; " + month[now.getMonth()];
				if(z == "year")
					title = (now.getFullYear()-1) + " &rarr; " + now.getFullYear();
					
				
				if (reports[z] > 0)
				{
					value = "+" + (100*reports[z]).toFixed(1);
					if(reports[z] > 0.20) { color = red; }
					else { color = orange; }
				}
				else { value = (100*reports[z]).toFixed(1); color = green; }
				
				out += "<div class=\"widget-container-h\" style=\"margin: 20px 0px 0px 8px; height: 80px; width: 236px;\"><h3 style=\"text-align: center; font-size: 22px; margin : 0px 0px 8px 0px;\">"+ title + "</h3><div style=\"color : " + color + "; text-align: center; font-size:24px; font-weight:bold; text-shadow: 0 1px 1px #CCC;\">" + value + "%</div></div>";
			  }
		  
		  
		  $("#reports").html(out);
	  }
	  });

  }

</script>
