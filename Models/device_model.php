<?php
  /*
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  // no direct access
  defined('EMONCMS_EXEC') or die('Restricted access');
  
  //id 	hostname 	x 	y 	userid 	comments 	ipv4_addr 	ipv6_addr 	typeid
  function add_device($hostname, $x, $y, $userid, $comments, $ipv4_addr, $ipv6_addr, $typeid)
  {
  	$result = db_query("INSERT INTO devices (id, hostname, x, y, userid, comments, ipv4_addr, ipv6_addr, typeid) VALUES (NULL, '$hostname', '$x', '$y', '$userid', '$comments', '$ipv4_addr', '$ipv6_addr', '$typeid')");
    return $result;
  }
  
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Gets a type string from it's typeid
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function get_type($typeid)
  {
    $result = db_query("SELECT * FROM device_type WHERE typeid='$typeid' LIMIT 1");
    while ($row = db_fetch_array($result))
    {
      $type = $row['type'];
      return $type;
    }
    return 0;
  }
  
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Gets a typeid from it's type string
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function get_typeid($type)
  {
    $result = db_query("SELECT * FROM device_type WHERE type='$type' LIMIT 1");
    while ($row = db_fetch_array($result))
    {
      $typeid = $row['typeid'];
      return $typeid;
    }
    return 0;
  }
  
  function get_all_types()
  {
        $result = db_query("SELECT * FROM device_type");
        $types = array();
        if ($result)
        {
          while ($row = db_fetch_array($result))
          {
          	$type = array('typeid' => $row['typeid'], 'type' => $row['type']);
          	$types[] = $type;
          }
        }
        return $types;
  }

  //-----------------------------------------------------------------------------------------------
  // Gets the device information.
  //-----------------------------------------------------------------------------------------------
  function get_device_info($deviceid)
  {
  	$result = db_query("SELECT * FROM devices WHERE id = '$deviceid';");
	$row = db_fetch_array($result);
	$arr = array('hostname' => $row['hostname'], 'x' => $row['x'], 'y' => $row['y'], 'type' => get_type($row['typeid']), 'userID' => $row['userid'], 'ipv4' => $row['ipv4_addr'], 'ipv6' => $row['ipv6_addr'],'comments' => $row['comments']);
	return $arr;
  }  
  
  function get_user_devices($userid)
  {
        $result = db_query("SELECT * FROM devices WHERE userid = '$userid' ORDER BY id ASC");
        $devices = array();
        if ($result)
        {
          while ($row = db_fetch_array($result))
          {
          	$device = array('id' => $row['id'], 'hostname' => $row['hostname'], 'x' => $row['x'], 'y' => $row['y'], 'type' => get_type($row['typeid']), 'typeid' => $row['typeid'], 'ipv4' => $row['ipv4_addr'], 'ipv6' => $row['ipv6_addr'],'comments' => $row['comments'], 'consumption' => device_consumption($row['hostname']), 'kwhd' => device_consumption($row['hostname'] . "-kwhd"));
          	$devices[] = $device;
          }
        }
        return $devices;
  }
  
  function device_consumption($devicename)
  {
    $feed_result = db_query("SELECT * FROM feeds WHERE name = '$devicename'");
    $feed_row = db_fetch_array($feed_result);
    return $feed_row['value'];
  }
  
  function get_all_devices()
  {
        $result = db_query("SELECT * FROM devices");
        $devices = array();
        if ($result)
        {
          while ($row = db_fetch_array($result))
          {
          	$device = array('id' => $row['id'], 'hostname' => $row['hostname'], 'x' => $row['x'], 'y' => $row['y'], 'type' => get_type($row['typeid']), 'typeid' => $row['typeid'], 'ipv4' => $row['ipv4_addr'], 'ipv6' => $row['ipv6_addr'],'comments' => $row['comments']);
          	$devices[] = $device;
          }
        }
        return $devices;
  }

?>
