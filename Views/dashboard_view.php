<!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->
<?php global $path; ?>
<!------------------------------------------------------------------------------------------
  Dashboard related javascripts
------------------------------------------------------------------------------------------->
<script type="text/javascript" src="<?php print $path; ?>Vis/flot/jquery.js"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/flot/jquery.flot.js"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/Dashboard/widgets/dial.js"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/Dashboard/widgets/led.js"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/d3/d3.js?2.7.1"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/d3/d3.layout.js?2.7.1"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/d3/d3.geom.js?2.7.1"></script>

<!------------------------------------------------------------------------------------------
  Dashboard HTML
------------------------------------------------------------------------------------------->

        <div style="text-align:center; width:100%;">
          <div style="width: 960px; margin: 0px auto; padding:0px; text-align:left; margin-bottom:20px; margin-top:20px;">


<div style="width:99.0%; padding:5px; border-radius: 3px; background-color: #E9E9E9; background-image: -moz-linear-gradient(center bottom , #DDDDDD, #E9E9E9); border: 1px solid #CCCCCC;">
<textarea rows="20"  id="editarea" style="width:99.4%; border-radius: 2px;"></textarea>
<button type="button" id="editsave" class="button05" >Save</button>
<button type="button" id="editclose" class="button05" >Close</button>
<button type="button" id="edit" class="button05" >Edit Dashboard</button>
</div>
<br/>

<div id="page">
<?php echo $page; ?>
</div>
          <div style="clear:both;"></div> 
          </div>
        </div>

<script type="application/javascript">

$(function() {
  var path = "<?php echo $path; ?>";
  var apikey_read = "<?php echo $apikey_read; ?>";
  var apikey_write = "<?php echo $apikey_write; ?>";

  $("#editarea").hide();
  $("#editclose").hide();
  $("#editsave").hide();

  $("#editarea").val($("#page").html());			// Place page html in edit area ready for editing

  //------------------------------------------------------
  // Save changes made to edit area
  //------------------------------------------------------
  $("#editsave").click(function(){
    $("#page").html($("#editarea").val());			// Update page html
    update();							// Run javascript

    // Upload changes to server
    $.ajax({                                      
      type: "POST",
      url: path+"dashboard/set",     
      data: "&content="+encodeURIComponent($("#editarea").val()),
      dataType: 'json',   
      success: function() { }
    });
  });

  //------------------------------------------------------
  // Handle Edit and close buttons
  //------------------------------------------------------
  $("#editclose").click(function(){
      $("#editarea").hide();
      $("#editclose").hide();
      $("#edit").show();
      $("#editsave").hide();
  });

  $("#edit").click(function(){
      $("#editarea").show();
      $("#editclose").show();
      $("#edit").hide();
      $("#editsave").show();
  });


  var feedids = [];		// Array that holds ID's of feeds of associative key
  var assoc = [];		// Array for exact values
  var assoc_curve = [];		// Array for smooth change values - creation of smooth dial widget

  var firstdraw = 1;

  update();
  setInterval(update,30000);
  setInterval(fast_update,30);
  setInterval(slow_update,60000);
  slow_update();

  function update()
  {
        $.ajax({                                      
          url: path+"feed/list.json",                
          dataType: 'json',
          success: function(data) 
          { 

            for (z in data)
            {
              var newstr = data[z][1].replace(/\s/g, '-');

              var value = parseFloat(data[z][4]);
              if (value<100) value = value.toFixed(1); else value = value.toFixed(0);
              console.log(newstr);
		 
              $("."+newstr).html(value);
              assoc[newstr] = value*1;
              feedids[newstr] = data[z][0];
            }

            draw_graphs();
            draw_maps();
  
            // Calls specific page javascript update function for any in page javascript
            if(typeof page_js_update == 'function') { page_js_update(assoc); }
            //--------------------------------------------------------------------------

          }  // End of data return function
        });  // End of AJAX function

  } // End of update function


  function fast_update()
  {
    draw_dials();
    draw_leds();
  }

  function slow_update()
  {
  }

  function curveValue(start,end,rate)
  {
    if (!start) start = 0;
    return start + ((end-start)*rate);
  }

  function draw_dials()
  {
           $('.dial').each(function(index) {
              var feed = $(this).attr("feed");
              var maxval = $(this).attr("max");
              var units = $(this).attr("units");
              var scale = $(this).attr("scale");

              assoc_curve[feed] = curveValue(assoc_curve[feed],parseFloat(assoc[feed]),0.02);
              var val = assoc_curve[feed]*1;

                var id = "can-"+feed+"-"+index;

                if (!$(this).html()) {	// Only calling this when its empty saved a lot of memory! over 100Mb
                  $(this).html('<canvas id="'+id+'" width="200px" height="160px"></canvas>');
                  firstdraw = 1;
                }

              if ((val*1).toFixed(1)!=(assoc[feed]*1).toFixed(1) || firstdraw == 1){ //Only update graphs when there is a change to update
                var canvas = document.getElementById(id);
                var ctx = canvas.getContext("2d");
                draw_gauge(ctx,200/2,100,80,val*scale,maxval,units); firstdraw = 0;
              }
            });
  }
  
  function draw_maps()
  {
  	$('.map').each(function(index)
  	{
  		if (!$(this).html()) // Only calling this the first time
  		{	
  			var w = 541,
			h = 524,
			fill = d3.scale.category20c();

			var vis = d3.select(".map").append("svg")
				.attr("width", w)
				.attr("height", h+100);
		
			vis.append("image")
					.attr("width", w)
					.attr("height", h)
					.attr("xlink:href", path+"Views/theme/dark/campus.svg");

			d3.json(path+"device/list.json", function(json) {
			  var force = d3.layout.force()
				  .nodes(json.nodes)
				  .links(json.links)
				  .size([w, h])
				  .start();

			  var link = vis.selectAll("line.link")
				  .data(json.links)
				.enter().append("line")
				  .attr("class", "link")
				  .style("stroke-width", function(d) { return Math.sqrt(d.value); })
				  .attr("x1", function(d) { return d.source.x * 5.6; })
				  .attr("y1", function(d) { return d.source.y * 5.6; })
				  .attr("x2", function(d) { return d.target.x * 5.6; })
				  .attr("y2", function(d) { return d.target.y * 5.6; });

			  var node = vis.selectAll("circle.node")
				  .data(json.nodes)
				.enter().append("circle")
				  .attr("class", "node")
				  .attr("cx", function(d) { return d.x*5.6; })
				  .attr("cy", function(d) { return d.y*5.6; })
				  .attr("r", 5)
				  .style("fill", function(d) { return fill(d.typeid); })
				  .on("click", function(d) { draw_node_informations(d); });

			  node.append("title")
				  .text(function(d) { return d.hostname; });
				  
			  /*vis.append("rect")
			   .attr("width", 250)
			   .attr("height", 120)
			   .attr("id","caption")
			   .style("fill", "#eee")
			   .attr("y", h+8);*/
			 
			  var type = vis.selectAll(".types")
						    .data(json.types)
						 .enter();
						 
			 type.append("circle")
				 .attr("class", "node")
				 .attr("cx", 10+40)
				 .attr("cy", function(d) { return d.typeid * 15 + h/2 + 8; })
				 .attr("r", 5)
				 .style("fill", function(d) { return fill(d.typeid); })
								 
								 
			type.append("text")
				.text(function(d) { return d.type; })
				.attr("x", 18+40)
				.attr("style", "font: 10px sans-serif;")
				.attr("y", function(d) { return d.typeid * 16 + h/2 + 10; });
								 
								 
			});
			
			   /*vis.append("rect")
			   .attr("width", 250)
			   .attr("height", 120)
			   .attr("id","caption")
			   .style("fill", "#eee")
			   .attr("y", h+8);*/
			var nodeinfos = d3.select(".nodeinfos");
			
			nodeinfos.append("div")
				.attr("id", "node-infos")
				.append("div")
					.attr("class", "title")
					.html("Node informations");
					
			nodeinfos.append("div")
					.attr("class", "infos")
					.html("Click on a node for more details");

  		}
  	});
  }
  
  function draw_node_informations(node)
  {
	  $('.infos').each(function(index)
	  {
		  $(this).html('<table><tr><th>Hostname:</td><td id="hostname">' + node.hostname + '</tr><tr><th>Consumption:</td><td id="consumption"><div class="value" style="color: #333333; font-size: 14px; font-weight: normal;"><span class="'+ node.hostname +'"></span>W</div></td></tr><tr><th>IPv4 Address:</td><td id="ipv4">' + node.ipv4 + '</td></tr><tr><th>IPv6 Address:</td><td id="ipv6">' + node.ipv6 + '</td></tr><tr><th style="vertical-align:top">Comments:</td><td id="comments"style="width: 124px; vertical-align:top;">'+ node.comments + '</td></tr></table><h3>Type</h3><img src="' + path + 'Views/theme/dark/' + node.typeid +'.png" alt="Device Type"/>');
	  });
	  update();
  }
  
  function draw_leds()
  {
           $('.led').each(function(index) {
              var feed = $(this).attr("feed");
              var val = assoc[feed];
	         var id = "canled-"+feed+"-"+index;
                if (!$(this).html()) {	// Only calling this when its empty saved a lot of memory! over 100Mb
                  $(this).html('<canvas id="'+id+'" width="50px" height="50px"></canvas>');
                  firstdraw = 1;
                }

       //   if ( firstdraw == 1){ //Only update graphs when there is a change to update

                var canvas = document.getElementById(id);
                var circle = canvas.getContext("2d");
                draw_led(circle,val); 
			firstdraw = 0;
       //       }
            });
  }



  function draw_graphs()
  {
    $('.graph').each(function(index) {
      var feed = $(this).attr("feed");
      var id = "#"+$(this).attr('id');
      var feedid = feedids[feed];
      $(id).width(200);
      $(id).height(200);

      var data = [];

      var timeWindow = (3600000*12);
      var start = ((new Date()).getTime())-timeWindow;		//Get start time

      var ndp_target = 200;
      var postrate = 5000; //ms
      var ndp_in_window = timeWindow / postrate;
      var res = ndp_in_window / ndp_target;
      if (res<1) res = 1;
      $.ajax({                                      
          url: path+"feed/data.json",                         
          data: "&apikey="+apikey_read+"&id="+feedid+"&start="+start+"&end="+0+"&res="+res,
          dataType: 'json',                           
          success: function(data) 
          { 
             $.plot($(id),
              [{data: data, lines: { fill: true }}],
              {xaxis: { mode: "time", localTimezone: true },
              grid: { show: true }
             });
          } 
      });
    });
  }



}); 

</script>
