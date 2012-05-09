/*
 All Emoncms code is released under the GNU Affero General Public License.
 See COPYRIGHT.txt and LICENSE.txt.

 ---------------------------------------------------------------------
 Emoncms - open source energy visualisation
 Part of the OpenEnergyMonitor project:
 http://openenergymonitor.org
 */

// Global page vars definition
var feedids = [];		// Array that holds ID's of feeds of associative key
var assoc = [];			// Array for exact values
var assoc_curve = [];	// Array for smooth change values - creation of smooth dial widget

var firstdraw = 1;

var dialrate = 0.02;
var browserVersion = 999;

var Browser = {
  Version: function() {
    var version = 999;
    if (navigator.appVersion.indexOf("MSIE") != -1)
      version = parseFloat(navigator.appVersion.split("MSIE")[1]);
    return version;
  }
}

function show_dashboard() {
	update();
	setInterval(update,30000);
	setInterval(fast_update,30);
	setInterval(slow_update,60000);
	slow_update();
}	
		
// update function	
function update() {


	browserVersion=Browser.Version();
	if (browserVersion < 9) dialrate=0.2;
    
	$.ajax({
		url: path+"feed/list.json?apikey="+apikey_read,
		dataType: 'json',
		success: function(data)	{
			for (z in data)	{
				var newstr = data[z][1].replace(/\s/g, '-');
				var value = parseFloat(data[z][4]);
						
				if (value<100) 
					value = value.toFixed(1); 
				else 
					value = value.toFixed(0);

				$("."+newstr).html(value);
				assoc[newstr] = value*1;
				feedids[newstr] = data[z][0];
			}

			draw_graphs();
			draw_maps();

			// Calls specific page javascript update function for any in page javascript
			if(typeof page_js_update == 'function') {
				page_js_update(assoc); }
			//--------------------------------------------------------------------------

		}  // End of data return function
	});  // End of AJAX function
}

function fast_update()
{
	draw_dials();
	draw_leds();
}
				
function slow_update() {
}

function curveValue(start, end, rate) {
	if(!start)
		start = 0;
	return start + ((end - start) * rate);
}

function draw_dials() {
	$('.dial').each(function(index) {
		var feed = $(this).attr("feed");
		var maxval = $(this).attr("max");
		var units = $(this).attr("units");
		var scale = $(this).attr("scale");

		assoc_curve[feed] = curveValue(assoc_curve[feed], parseFloat(assoc[feed]), dialrate);
		var val = assoc_curve[feed] * 1;

		var id = "can-" + feed + "-" + index;
		
		if(!$(this).html()) {// Only calling this when its empty saved a lot of memory! over 100Mb
			$(this).html('<canvas id="' + id + '" width="200px" height="160px"></canvas>');
			firstdraw = 1;
		}

		if((val * 1).toFixed(1) != (assoc[feed] * 1).toFixed(1) || firstdraw == 1) {//Only update graphs when there is a change to update
			var canvas = document.getElementById(id);
 			if (browserVersion != 999) {
	
		  		canvas.setAttribute('width', '200'); 
		  		canvas.setAttribute('height', '160');
                  		if(typeof G_vmlCanvasManager != "undefined")   G_vmlCanvasManager.initElement(canvas);
			}
			var ctx = canvas.getContext("2d");
			draw_gauge(ctx, 200 / 2, 100, 80, val * scale, maxval, units);
			firstdraw = 0;
		}
	});
        $('.centredial').each(function(index) {
              var feed = $(this).attr("feed");
              var maxval = $(this).attr("max");
              var units = $(this).attr("units");
              var scale = $(this).attr("scale");

              assoc_curve[feed] = curveValue(assoc_curve[feed],parseFloat(assoc[feed]), dialrate);
              var val = assoc_curve[feed]*1;

                var id = "can-"+feed+"-"+index;

                if (!$(this).html()) {	// Only calling this when its empty saved a lot of memory! over 100Mb
                  $(this).html('<canvas id="'+id+'" width="200px" height="160px"></canvas>');
                  firstdraw = 1;
                }

              if ((val*1).toFixed(1)!=(assoc[feed]*1).toFixed(1) || firstdraw == 1){ //Only update graphs when there is a change to update
                var canvas = document.getElementById(id);
   	        if (browserVersion != 999) { 
		   canvas.setAttribute('width', '200'); 
                   canvas.setAttribute('height', '160');
                   if(typeof G_vmlCanvasManager != "undefined")  G_vmlCanvasManager.initElement(canvas);
		}
                var ctx = canvas.getContext("2d");
                draw_centregauge(ctx,200/2,100,80,val*scale,maxval,units); firstdraw = 0;
              }
         });
}

function draw_leds() {
	$('.led').each(function(index) {
		var feed = $(this).attr("feed");
		var val = assoc[feed];
		var id = "canled-" + feed + "-" + index;
		if(!$(this).html()) {// Only calling this when its empty saved a lot of memory! over 100Mb
			$(this).html('<canvas id="' + id + '" width="50px" height="50px"></canvas>');
			firstdraw = 1;
		}

		//  if ( firstdraw == 1){ //Only update graphs when there is a change to update
		var canvas = document.getElementById(id);
   	        if (browserVersion != 999) { 
		   canvas.setAttribute('width', '200'); 
                   canvas.setAttribute('height', '160');
                   if(typeof G_vmlCanvasManager != "undefined")  G_vmlCanvasManager.initElement(canvas);
		}
		var circle = canvas.getContext("2d");
		if (browserVersion <9) draw_led_ie8(circle,val); else draw_led(circle,val); 		
		firstdraw = 0;
		//  }
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
		
function draw_maps()
{
	$('.map').each(function(index) {
		if (!$(this).html()) // Only calling this the first time
  		{	
  			var w = 541,
				h = 524,
				fill = d3.scale.category20c();
			
			var emoncms_dial_colors = ["#c0e392", "#9dc965", "#87c03f","#70ac21","#378d42","#046b34"];
			
			var layers = d3.select(".map")
						   .append("ul")
						   .attr("id", "layers")
						   .html('<li>Layers :</li><li><a id="devicelayer" href="">Device type</a></li><li><a id="powernowlayer" href="#">Power now</a></li><li><a id="energytodaylayer" href="#">Energy today</a></li>');

			var vis = d3.select(".map").append("svg")
				.attr("width", w)
				.attr("height", h+100);
				
		    var consumption_max = $(this).attr("max");
		    var consumption_per_day_max = $(this).attr("maxkwhd");
				
			d3.select(".map ul li #powernowlayer").on("click", function() {
				vis.selectAll(".caption").transition().remove();
								
				var caption = vis.append("g")
								  .attr("class", "caption")
								  .attr("width", 20 * emoncms_dial_colors.length +"px");
								  
				caption.selectAll("rect")
					   .data(emoncms_dial_colors)
					   .enter().append("rect")
							   .attr("x", function(d, i) { return i * 20+4; })
							   .attr("y", 524)
							   .attr("width", 20)
							   .attr("height", 20)
							   .style("fill", function(d, i) { return emoncms_dial_colors[i] });
									
				caption.append("text")
					   .attr("x", 0)
					   .attr("y", 554)
					   .text("0W")
					   .attr("style", "font: 10px sans-serif;");
					  
					  
				caption.append("text")
					   .attr("x", 20 * emoncms_dial_colors.length - 12 +"px")
					   .attr("y", 554)
					   .text(consumption_max + "W")
					   .attr("style", "font: 10px sans-serif;"); 
									
				vis.selectAll("circle.node")
				   .transition()
				   .style("fill", function(d) {
					   if(d.consumption)
					   {
						   if(d.consumption>=0 && d.consumption < (consumption_max/6))
								return  emoncms_dial_colors[0];
						   if(d.consumption>=(consumption_max/6) && d.consumption < (consumption_max/6)*2)
								return  emoncms_dial_colors[1];
						   if(d.consumption>=(consumption_max/6)*2 && d.consumption < (consumption_max/6)*3)
								return  emoncms_dial_colors[2];
						   if(d.consumption>=(consumption_max/6)*3 && d.consumption < (consumption_max/6)*4)
								return  emoncms_dial_colors[3];
						   if(d.consumption>=(consumption_max/6)*4 && d.consumption < (consumption_max/6)*5)
								return  emoncms_dial_colors[4];
						   if(d.consumption>=(consumption_max/6)*5)
								return  emoncms_dial_colors[4];
					   }
					   else return "#ccc";
				   });
					   
					   
			});
			
			d3.select(".map ul li #energytodaylayer").on("click", function() {
				vis.selectAll(".caption").transition().remove();
				
				color = d3.interpolateRgb("#aad", "#556");
				
				var caption = vis.append("g")
								 .attr("class", "caption")
								 .attr("width", "200px");
								  
				caption.selectAll("rect")
					   .data(color)
					   .enter().append("rect")
							   .attr("x", function(d, i) { return i*20})
							   .attr("y", 524)
							   .attr("width", 20)
							   .attr("height", 20)
							   .style("fill", function(d, i) { return color(i/10); });
									
				caption.append("text")
					   .attr("x", 0)
					   .attr("y", 554)
					   .text("0kWh")
					   .attr("style", "font: 10px sans-serif;");
					  
					  
				caption.append("text")
					   .attr("x", 20 * color.length*10 - 20 +"px")
					   .attr("y", 554)
					   .text(parseFloat(consumption_per_day_max).toFixed(2)+"kWhd")
					   .attr("style", "font: 10px sans-serif;"); 
									
				vis.selectAll("circle.node")
				   .transition()
				   .style("fill", function(d) {
					   if(d.kwhd)
					   {
						   return  color(d.kwhd);
					   }
					   else return "#ddd";
				   });
					   
					   
			});
		
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
				  
			  draw_caption();
			  
			  /** Returns an event handler for fading a given chord group. */
			  function fade(opacity) {
				  return function(g, i) {
					  vis.selectAll("circle.node")
					     .filter(function(d) {
							 return d.typeid != i+1;
							 })
							 .transition()
							 .style("opacity", opacity);
							 };
			  }
			  
			  /** Returns an event handler for fading a given chord group. */
			  function draw_caption()
			  {
				  var type = vis.selectAll(".types")
								.data(json.types)
								.enter();
						
				  var caption = type.append("g").attr("class", "caption");
				  
				  
				  caption.append("circle")
						 .attr("class", "node")
						 .attr("cx", 10+40)
						 .attr("cy", function(d) { return d.typeid * 15 + h/2 + 8; })
						 .attr("r", 5)
						 .style("fill", function(d) { return fill(d.typeid); });
						 
						 
				  caption.append("text")
						 .text(function(d) { return d.type; })
						 .attr("x", 18+40)
						 .attr("style", "font: 10px sans-serif;")
						 .attr("y", function(d) { return d.typeid * 16 + h/2 + 10; })
						 .on("mouseover", fade(.1))
						 .on("mouseout", fade(1));
			  }
			});
			
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
  var out = '<table><tr><th>Hostname:</th><td id="hostname">' + node.hostname + '</tr>';
  if(node.consumption)
	out += '<tr><th>Consumption:</th><td id="consumption">'+ node.consumption +'W</div></td></tr>';
  if(node.kwhd)
	out += '<tr><th>Energy today:</th><td id="kwhd">'+ parseFloat(node.kwhd).toFixed(3) +'kWhd</div></td></tr>';
  if(node.ipv4)
	out += '<tr><th>IPv4 Address:</th><td id="ipv4">' + node.ipv4 + '</td></tr>';
  if(node.ipv6)
	out += '<tr><th>IPv6 Address:</th><td id="ipv6">' + node.ipv6 + '</td></tr>';
  if(node.comments)
	out += '<tr><th style="vertical-align:top">Comments:</th><td id="comments"style="width: 124px; vertical-align:top;">'+ node.comments + '</td></tr>';
	
  out += '</table><h3>Type</h3><img src="' + path + 'Views/theme/dark/' + node.typeid +'.png" alt="Device Type"/>';
  
  $('.infos').each(function(index)
  {
	  $(this).hide().html(out).fadeIn();
  });
  update();
}
		
