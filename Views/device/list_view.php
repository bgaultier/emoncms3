<!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->


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
  <table class='catlist'><tr><th>id</th><th>Hostname</th><th>IPv4 address</th><th>IPv6 address</th><th>Type</th><th>Feed ID</th></tr>
  <?php
    foreach ($devices as $device)
    { ?>
      <tr class="<?php echo 'd'.($device['id'] & 1); ?>" >
      <td><?php echo $device['id']; ?></td>
      <td><?php echo $device['hostname']; ?></td>
      <td><?php echo $device['ipv4']; ?></td>
      <td><?php echo $device['ipv6']; ?></td>
      <td><?php echo $device['type']; ?></td>
      <td><?php echo $device['feedID']; ?></td>
      </tr>
    <?php } ?>
    </table>
    <?php } else { ?>
      <p>You have no devices</p>
    <?php } ?>
</div>



