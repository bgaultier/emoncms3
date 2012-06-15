<?php 
  /*
    All Emoncms code is released under the GNU Affero General Public License.
    See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org

    FEED CONTROLLER ACTIONS				ACCESS

    tag?id=1&tag=tag					write
    rename?id=1&name=newname			write
    delete?id=1							write
    list								read
    view?id=1							read
    value?id=1							read
    comparison?id=1						read
    data?id=1&start=000&end=000&res=1	read
    
  */

  // no direct access
  defined('EMONCMS_EXEC') or die('Restricted access');

  function feed_controller()
  {
    require "Models/feed_model.php";
    global $session, $action, $format;

    $output['content'] = "";
    $output['message'] = "";

    //---------------------------------------------------------------------------------------------------------
    // Set feed tag
    // http://yoursite/emoncms/feed/tag?id=1&tag=tag
    //---------------------------------------------------------------------------------------------------------
    if ($action == "type" && $session['write'])
    { 
      $feedid = intval($_GET["id"]);
      $type = intval($_GET["type"]);

      if (feed_belongs_user($feedid, $session['userid'])) {
        set_feed_datatype($feedid,$type);
        $output['message'] = "feed type changed";
      } else $output['message'] = "feed does not exist";

      if ($format == 'html') header("Location: view?id=$feedid");	// Return to feed list page
    }


    //---------------------------------------------------------------------------------------------------------
    // Set feed tag
    // http://yoursite/emoncms/feed/tag?id=1&tag=tag
    //---------------------------------------------------------------------------------------------------------
    if ($action == "tag" && $session['write'])
    { 
      $feedid = intval($_GET["id"]);
      if (feed_belongs_user($feedid, $session['userid'])) {

        $newfeedtag = preg_replace('/[^\w\s-.]/','',$_GET["tag"]);	// filter out all except for alphanumeric white space and dash and full stop
        $newfeedtag = db_real_escape_string($newfeedtag);

        set_feed_tag($feedid,$newfeedtag);
        $output['message'] = "feed tag changed";
      } else $output['message'] = "feed does not exist";

      if ($format == 'html') header("Location: list");	// Return to feed list page
    }

    //---------------------------------------------------------------------------------------------------------
    // Rename a feed
    // http://yoursite/emoncms/feed/rename?id=1&name=newname
    //---------------------------------------------------------------------------------------------------------
    if ($action == "rename" && $session['write'])
    { 
      $feedid = intval($_GET["id"]);
      if (feed_belongs_user($feedid, $session['userid'])) {

        $newfeedname = preg_replace('/[^\w\s-.]/','',$_GET["name"]);	// filter out all except for alphanumeric white space and dash
        $newfeedname = db_real_escape_string($newfeedname);

        set_feed_name($feedid,$newfeedname);
        $output['message'] = "Feed renamed";
      } else $output['message'] = "Feed does not exist";

      if ($format == 'html') header("Location: list");	// Return to feed list page
    }

    //---------------------------------------------------------------------------------------------------------
    // Delete a feed
    // http://yoursite/emoncms/feed/delete?id=1
    //--------------------------------------------------------------------------------------------------------- 
    if ($action == "delete" && $session['write'])
    { 
      $feedid = intval($_GET["id"]);
      if (feed_belongs_user($feedid, $session['userid'])) {
        delete_feed($userid,$feedid);
        $output['message'] = "feed ".get_feed_name($feedid)." deleted";
      } else $output['message'] = "Feed does not exist";

    }
    
    //---------------------------------------------------------------------------------------------------------
    // Over Time Comparison
    // http://yoursite/emoncms/feed/comparison?id=1
    //--------------------------------------------------------------------------------------------------------- 
    if ($action == "comparison" && $session['read'])
    { 
      $feedid = intval($_GET["id"]);
      if (feed_belongs_user($feedid, $session['userid'])) {
		  $reports = get_over_time_comparison($feedid);
		  $feed = get_feed($feedid);
		  
		  if ($format == 'json') $output['content'] = json_encode($reports);
		  if ($format == 'html') $output['content'] = view("feed/comparison_view.php", array('feed' => $feed, 'reports' => $reports));
      } else $output['message'] = "Feed does not exist";
      
    }


    //---------------------------------------------------------------------------------------------------------
    // List
    // http://yoursite/emoncms/feed/list.html
    // http://yoursite/emoncms/feed/list.json
    //---------------------------------------------------------------------------------------------------------
    if ($action == 'list' && $session['read'])
    {
      $feeds = get_user_feeds($session['userid']);
    
      if ($format == 'json') $output['content'] = json_encode($feeds);
      if ($format == 'html') $output['content'] = view("feed/list_view.php", array('feeds' => $feeds));
    }

    //---------------------------------------------------------------------------------------------------------
    // View
    // http://yoursite/emoncms/feed/view.html?id=1
    // http://yoursite/emoncms/feed/view.json?id=1
    //---------------------------------------------------------------------------------------------------------
    if ($action == 'view' && $session['read'])
    {
      $feedid = intval($_GET["id"]);
      if (feed_belongs_user($feedid,$session['userid']))
      {
        $feed = get_feed($feedid);
      }

      if ($format == 'json')
      {
        $output['content'] = json_encode($feed);
        // Allow for AJAX from remote source
        if ($_GET["callback"]) $output['content'] = $_GET["callback"]."(".json_encode($feed).");";
      }
      if ($format == 'html') $output['content'] = view("feed/feed_view.php", array('feed' => $feed));
    }

    //---------------------------------------------------------------------------------------------------------
    // current feed value
    // http://yoursite/emoncms/feed/value?id=1
    //---------------------------------------------------------------------------------------------------------
    if ($action == 'value' && $session['read'])
    {
      $feedid = intval($_GET["id"]);
      if (feed_belongs_user($feedid,$session['userid'])) $output['content'] = get_feed_value($feedid);
    }
    
    //---------------------------------------------------------------------------------------------------------
    // current feed value
    // http://yoursite/emoncms/feed/arduino.xml?id=1
    //---------------------------------------------------------------------------------------------------------
    if ($action == 'arduino' && $session['read'])
    {
      $feedid = intval($_GET["id"]);
      if (feed_belongs_user($feedid,$session['userid'])) {
		  
		  $xml = simplexml_load_file('http://www.google.com/ig/api?weather=Rennes');
		  $information = $xml->xpath("/xml_api_reply/weather/forecast_information");
		  $current = $xml->xpath("/xml_api_reply/weather/current_conditions");
		  $forecast_list = $xml->xpath("/xml_api_reply/weather/forecast_conditions");
		  
		  $end = time() * 1000;
		  $start = $end - (3600000 * 24 * 7);
		  
		  $data = get_feed_data($feedid,$start,$end, 1, 7);
		  
		  foreach ($data as $value) {
			  $values[] = $value[1];
		  }
		  
		  $max = max($values);
		  
		  foreach ($values as $key => $value) {
			   $values[$key] = ($value * 7) / $max;
		  }
		  
		  $xml = "<?xml version=\"1.0\"?>\n";
		  
		  $xml .= "<time>" . date('H:i') . "</time>\n";
		  $xml .= "<today>" . icon_to_number($current[0]->icon['data']) . "</today>\n";
		  $xml .= "<temperature>". sprintf("%02d", $current[0]->temp_c['data']) . "</temperature>\n";
		  $xml .= "<tomorrow>" . icon_to_number($forecast_list[0]->icon['data']) . "</tomorrow >\n";
		  $xml .= "<low>" . sprintf("%02d", (($forecast_list[0]->low['data']-32)*5/9)) . "</low>\n";
		  $xml .= "<high>" . sprintf("%02d", (($forecast_list[0]->high['data']-32)*5/9)) . "</high>\n";
		  
		  $xml .= "<instantaneous>" .round(get_feed_value($feedid - 1 )) . "</instantaneous>\n";
		  $xml .= "<history>\n";
		  foreach ($values as $key => $value) {
			  $xml .= "\t<day" . $key . ">" . round(15 - $value) ."</day" . $key . ">\n";
		  }
		  $xml .= "</history>";
		  
		  
		  
		  if ($format == 'xml') {
			  header('Content-type: text/xml');
			  $output['content'] = $xml;
		  }
	  }
    }

    //---------------------------------------------------------------------------------------------------------
    // get feed data
    // http://yoursite/emoncms/feed/data?id=1&start=000&end=000&res=1
    //---------------------------------------------------------------------------------------------------------
    if ($action == 'data' && $session['read'])
    {
      $feedid = intval($_GET['id']);
      
      // Check if feed belongs to user
      if (feed_belongs_user($feedid,$session['userid']))
      {
        $start = floatval($_GET['start']);
        $end = floatval($_GET['end']);
        $oldres = intval($_GET['res']); 				// For legacy support
        $dp = intval($_GET['dp']);					// This is the new resolution setting where you ask for a specific number of datapoints
        $data = get_feed_data($feedid,$start,$end,$oldres,$dp);
        $output['content'] = json_encode($data);
      }
    }

    return $output;
  }
  
  function icon_to_number($icon) {
	  switch($icon) {
		case "/ig/images/weather/sunny.gif":
		case "/ig/images/weather/mostly_sunny.gif":
			return 0;
			break;
		case "/ig/images/weather/cloudy.gif":
		case "/ig/images/weather/partly_cloudy.gif":
		case "/ig/images/weather/mostly_cloudy.gif":
			return 1;
			break;
			
		case "/ig/images/weather/rain.gif":
		case "/ig/images/weather/chance_of_rain.gif":
		case "/ig/images/weather/storm.gif":
		case "/ig/images/weather/thunderstorm.gif":
		case "/ig/images/weather/chance_of_storm.gif":
			return 2;
			break;

		case "/ig/images/weather/dust.gif":
		case "/ig/images/weather/fog.gif":
		case "/ig/images/weather/smoke.gif":
		case "/ig/images/weather/haze.gif":
		case "/ig/images/weather/mist.gif":
			return 3;
			break;

		case "/ig/images/weather/flurries.gif":
		case "/ig/images/weather/snow.gif":
		case "/ig/images/weather/chance_of_snow.gif":
		case "/ig/images/weather/icy.gif":
		case "/ig/images/weather/sleet.gif":
			return 4;
			break;
		default:
			return 1;
			break;
	}
  }
?>


