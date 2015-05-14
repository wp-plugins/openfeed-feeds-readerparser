<?php  
    /* 
    Plugin Name: openFeed 
    Plugin URI: http://openfeed.io 
    Description: Real Time, Feed Reader/Parser
    Author: Dropstr Inc
    Version: 0.3 
    Author URI: http://dropstr.com 
    */  

if (!function_exists('openfeed_dashboard_menu')) {
function openfeed_dashboard_menu() {
	add_menu_page( 'Feeds', 'Feeds', 'read', 'openfeed', 'openfeed_dashboard','dashicons-list-view', '4.778' );
}
}


// Get openFeed Feeds
function openfeed_dashboard() {
global $current_user;
get_currentuserinfo();
$myDomain = get_site_url();
$myID = $current_user->ID;



// Get/Check or Register API Key for Beta Testing
function checkAPI($user){
      global $wpdb;
        $getApi = $wpdb->get_results( "SELECT option_id, option_value FROM $wpdb->options WHERE option_name = 'oF_api_$user'");
            foreach ( $getApi as $myApi )
        {
            $myApiKey = $myApi->option_value;
        }
        return $myApiKey;
    }
$myApiKey = checkAPI($myID);


// Check API Key and or create new API Key per user per domain
if($myApiKey == NULL){
$respOf = wp_remote_get( 'http://api.openfeed.io/v1/?c=register&domain='.$myDomain.'&uid='.$myID.'');
if ( 200 == $respOf['response']['code'] ) {
        $body = $respOf['body'];
        $obj = json_decode($body);
        // get Status codes and display errors
        $myApiKey = $obj->{'key'};

    $wpdb->insert( 
      $wpdb->options, 
      array( 
        'option_name' => 'oF_api_'.$myID, 
        'option_value' => $myApiKey 
      ) 
    );        
        }
}

        // Get Real time Feed (left) and Trending Feed via API call (right)
	echo "<table style=\"color:#FFF\"><tr><td colspan=2><font color='#000'>";
  // Check Version
 $respOf = wp_remote_get( 'http://api.openfeed.io');
if ( 200 == $respOf['response']['code'] ) {
        $body = $respOf['body'];
        $obj = json_decode($body);
        $version = $obj->{'version'};
    if($version != "0.3"){
      echo "A new updated version, v$version, is available, update via the Plugins page.";
    } else { echo "v$version Trend Edition";}       
        }
echo "</font></td></tr><tr><td width=50% valign=\"top\"><div id=\"trending\">";


// Get Feed via API Call
$respOf = wp_remote_get( 'http://api.openfeed.io/v1/?c=getFeed&feed=games&type=trending&timeline=6&key='.$myApiKey.'');
// If API Call ok/ 200
if ( 200 == $respOf['response']['code'] ) {
    $body = $respOf['body'];
        $ofFeed = json_decode($body, true);
     
?>
 <html>
<head>

</head><body>

<table width=100%><tr><td><div style="background:#000;padding:10px;width:250px;color:#FFF;"><table><tr><td align="left" style="color:#FFF"><b>All Posts</b></td><td><?php 
echo "<b>".$ofFeed["totalPosts"]."</b> [24h]";
?></td></tr></table></div></td></tr></table>
          
          <div style='color:#000000'><b>Trending Top 50 [ 6 Hours ]</b></div>
          <table rules=columns cellspacing=6>
<?php
    $rc =1;
        foreach ($ofFeed as $innerArray) {
            //  Check type
            if (is_array($innerArray)){
                //  Scan through inner loop
                
                foreach ($innerArray as $value) {

                  echo'<tr><td align=right style="color:#000000"><b>'.$rc.'</b></td><td><img src='.$value["favicon"].'> <a href='.$value["url"].' style="color:#000000" target=_new>'.$value["title"].'</a></td><td></td></tr>';
          $rc++;        
    }
    
  }
}
echo "</table><br />";
echo "</div></td><td width=50% valign=\"top\"><font color='#000'><p>This Version is the <b>Trend Version</b>, which only displays the top 50 trending articles from over 20k gaming websites over the past 6 hours. Get the <b>Real Time Version</b>(Free), which shows you articles being published as they happen for WordPress via the <a href='http://www.openfeed.io' target='openfeed'>openFeed</a> website.</p></font></td></tr></table>";
} 
}
add_action('admin_menu', 'openfeed_dashboard_menu');
?>
