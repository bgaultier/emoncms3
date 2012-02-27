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
    require "Models/feed_model.php";
    require "Models/device_model.php";
    global $session, $action, $format;

    $output['content'] = "";
    $output['message'] = "";

    //---------------------------------------------------------------------------------------------------------
    // List
    // http://yoursite/emoncms/device/list.html
    // http://yoursite/emoncms/device/list.json
    //---------------------------------------------------------------------------------------------------------
    if ($action == 'list' && $session['read'])
    {
      $feeds = get_user_feeds($session['userid']);
      $devices = get_user_devices($session['userid']);
      $types = get_all_types();
    
      if ($format == 'json') $output['content'] = "{\"nodes\":" . json_encode($devices) . ",\"links\":[{\"source\":0,\"target\":1,\"value\":1}]}";
      if ($format == 'html') $output['content'] = view("device/list_view.php", array('devices' => $devices, 'types' => $types, 'feeds' => $feeds));
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
    if ($action == "add" && $session['write']) // write access required
    {
		$result = add_device($_GET["hostname"],$_GET["margintop"],$_GET["marginleft"],$session['userid'],$_GET["comments"],$_GET["ipv4addr"],$_GET["ipv6addr"],get_typeid($_GET["type"]));
		if ($format == 'html') header("Location: list");	// Return to device list page
    }
    
    return $output;
    
  }
?>


