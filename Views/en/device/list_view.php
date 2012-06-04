<!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->

<?php global $path; ?>

<script type="text/javascript" src="<?php print $path; ?>Vis/d3/d3.js?2.7.1"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/d3/d3.layout.js?2.7.1"></script>
<script type="text/javascript" src="<?php print $path; ?>Vis/d3/d3.geom.js?2.7.1"></script>

<div class='lightbox' style="margin-bottom:20px; margin-left:3%; margin-right:3%;">
  <h2>Devices</h2>
  
  <h3>Add device</h3>
  <form id="adddevice" method="get" action="add">
  <input type="hidden" name="userid" value="<?php echo $_SESSION['userid']; ?>">
  <fieldset>
    <ol>
      <li>
        <label for=hostname>Hostname</label>
        <input id=hostname name=hostname type=text placeholder="host name of the device" required autofocus>
      </li>
      <li>
        <label for=margintop>Margin top (m)</label>
        <input id=margintop name=margintop type=number placeholder="e.g. 10" required>
      </li>
      <li>
        <label for=marginleft>Margin left (m)</label>
        <input id=marginleft name=marginleft type=number placeholder="e.g. 38.5" required>
      </li>
      <div class="map" style="margin: 24px; margin-left: 118px; "></div>
      <li>
        <label for=comments>Comments</label>
        <textarea id=comments name=comments rows=4 placeholder="Comments about the device"></textarea>
      </li>
      <li>
        <label for=ipv4addr>IPv4 address</label>
        <input id=ipv4addr name=ipv4addr type=text placeholder="e.g. 10.20.30.40">
      </li>
      <li>
        <label for=ipv6addr>IPv6 address</label>
        <input id=ipv6addr name=ipv6addr type=text placeholder="e.g. 2001:660:7301:d161::4">
      </li>
      <li>
		  <label for=type>Type</label>
		  <select id=type name=type><?php if ($types) { foreach ($types as $type){ ?>
		  
			<option><?php echo $type['type']; ?></option><?php }} else{ ?>
			<option>No type</option><?php } ?>

		  </select>
      </li>
    </ol>
  </fieldset>
  <fieldset>
    <button type=submit class="button05">Add</button>
  </fieldset>
  </form>
</div>


<div class='lightbox' style="margin-bottom:20px; margin-left:3%; margin-right:3%;">
  <h3>My devices</h3>
  <?php if ($devices) { ?>
  <table class='catlist'><tr><th>id</th><th>Hostname</th><th>IPv4 address</th><th>IPv6 address</th><th>Type</th></tr>
  <?php
    foreach ($devices as $device)
    { ?>
      <tr class="<?php echo 'd'.($device['id'] & 1); ?>" >
      <td><?php echo $device['id']; ?></td>
      <td><?php echo $device['hostname']; ?></td>
      <td><?php echo $device['ipv4']; ?></td>
      <td><?php echo $device['ipv6']; ?></td>
      <td><?php echo $device['type']; ?></td>
      </tr>
    <?php } ?>
    </table>
    <?php } else { ?>
      <p>You have no devices</p>
    <?php } ?>
</div>

<script type="application/javascript">

var w = 265,
	h = 256,
	r = 4,
	path = "<?php echo $path; ?>";
	fill = d3.scale.category20();
	
var drag = d3.behavior.drag()
.origin(Object)
.on("drag", dragmove);
	
var vis = d3.select(".map").append("svg")
.attr("width", w)
.attr("height", h)
.attr("class", "widget-container")
;

vis.append("image")
.attr("width", w)
.attr("height", h)
.attr("xlink:href", path+"Views/theme/dark/campus-small.svg");

var drag = d3.behavior.drag()
    .origin(Object)
    .on("drag", dragmove);

var circle = vis.append("circle")
    .data([{x: w / 2, y: h / 2}])
    .attr("class", "node")
    .attr("r", r)
    .attr("cx", function(d) { return d.x; })
    .attr("cy", function(d) { return d.y; })
    .style("fill", function(d) { return fill(1); })
    .call(drag);

function dragmove(d) {
  circle
      .attr("cx", d.x = Math.max(r, Math.min(w - r, d3.event.x)))
      .attr("cy", d.y = Math.max(r, Math.min(h - r, d3.event.y)));
      
  document.getElementById("margintop").value = (d.x/2.8).toFixed(1);
  document.getElementById("marginleft").value = (d.y/2.8).toFixed(1);
  
}
</script>

