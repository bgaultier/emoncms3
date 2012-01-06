<?php 
  /*
    All Emoncms code is released under the GNU Affero General Public License.
    See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org

    DEVICE	CONTROLLER	ACTIONS	ACCESS
	list				read
    
  */
  function device_controller()
  {
    require "Models/device_model.php";
    require "Models/feed_model.php";
    global $action, $format;
    
    //---------------------------------------------------------------------------------------------------------
    // List
    // http://yoursite/emoncms/device/list.html
    // http://yoursite/emoncms/device/list.json
    //---------------------------------------------------------------------------------------------------------
    if ($action == 'list' && $_SESSION['read'])
    {
      $devices = get_user_devices($_SESSION['userid']);
      $feeds = get_user_feeds($_SESSION['userid']);
      if ($format == 'json') $output = "{\"nodes\":" . json_encode($devices) . ",\"links\":[{\"source\":0,\"target\":1,\"value\":1}]}";
      if ($format == 'html') $output = view("device/list_view.php", array('devices' => $devices, 'feeds' => $feeds));
    }
    
    //---------------------------------------------------------------------------------------------------------
    // Add device
    // http://yoursite/emoncms/device/add?userid=id&
    // hostname=name&
    // margintop=30&
    // marginleft=20&
    // comments=&
    // ipv4addr=&
    // ipv6addr=&
    // type=alix&
    // feedid=2
    //---------------------------------------------------------------------------------------------------------
    if ($action == "add" && $_SESSION['write']) // write access required
    {
      // TODO Improve this crapy code with a new database table 'Type'
      switch ($_GET["type"])
      {
    		case "arduino":
				$group = 1;
	  			break;
	  		case "alix":
	  			$group = 2;
	  			break;
	  		case "tmote sky":
	  			$group = 3;
	  			break;
	  		default:
	  			$group = 1;
	  			break;
	  	}
	  	$result = add_device($_GET["hostname"],$_GET["margintop"],$_GET["marginleft"],$_SESSION['userid'],$_GET["comments"],$_GET["ipv4addr"],$_GET["ipv6addr"],$_GET["type"],$_GET["feedid"],$group);
	  	 if ($format == 'html') header("Location: list");	// Return to device list page
    }
    
    return $output;
  }
  
  
?>


