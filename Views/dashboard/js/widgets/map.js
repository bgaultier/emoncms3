  /*
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  function draw_map(max, kwhdmax) {
    // TODO : size of the map has to be dynamic
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
		
	var consumption_max = max;
	var consumption_per_day_max = kwhdmax;
		
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
		  .on("click", function(d) { render_node_information(d); });

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
  }
