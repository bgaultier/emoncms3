<?php
  /*
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  function add_device($hostname,$x,$y,$userid,$comments,$ipv4_addr,$ipv6_addr,$type,$feedid,$group)
  {
  	$result = db_query("INSERT INTO devices (id, hostname, x, y, userid, comments, ipv4_addr, ipv6_addr,`type`, feedid, `group`) VALUES (NULL, '$hostname', '$x', '$y', '$userid', '$comments', '$ipv4_addr', '$ipv6_addr', '$type', NULL, '$group')");
    return $result;
  }

  //-----------------------------------------------------------------------------------------------
  // Gets the device information. Returns json string
  //-----------------------------------------------------------------------------------------------
  function get_device_info($deviceid)
  {
  	$result = db_query("SELECT * FROM devices WHERE id = '$deviceid';");
	$row = db_fetch_array($result);
	$arr = array('hostname' => $row['hostname'], 'x' => $row['x'], 'y' => $row['y'], 'type' => $row['type'], 'userID' => $row['userid'], 'ipv4' => $row['ipv4_addr'], 'ipv6' => $row['ipv6_addr'],'feedID' => $row['feedid'], 'comments' => $row['comments']);
	return $arr;
  }  
  
  function get_user_devices($userid)
  {
        $result = db_query("SELECT * FROM devices WHERE userid = '$userid'");
        $devices = array();
        if ($result)
        {
          while ($row = db_fetch_array($result))
          {
          	$device = array('id' => $row['id'], 'hostname' => $row['hostname'], 'x' => $row['x'], 'y' => $row['y'], 'type' => $row['type'], 'group' => $row['group'], 'userID' => $row['userid'], 'ipv4' => $row['ipv4_addr'], 'ipv6' => $row['ipv6_addr'],'feedID' => $row['feedid'], 'comments' => $row['comments']);
          	$devices[] = $device;
          }
        }
        return $devices;
  }
  
  function get_all_devices()
  {
        $result = db_query("SELECT * FROM devices");
        $devices = array();
        if ($result)
        {
          while ($row = db_fetch_array($result))
          {
          	$device = array('id' => $row['id'], 'hostname' => $row['hostname'], 'x' => $row['x'], 'y' => $row['y'], 'type' => $row['type'], 'group' => $row['group'], 'userID' => $row['userid'], 'ipv4' => $row['ipv4_addr'], 'ipv6' => $row['ipv6_addr'],'feedID' => $row['feedid'], 'comments' => $row['comments']);
          	$devices[] = $device;
          }
        }
        return $devices;
  }

?>
