<?php
/*
Plugin Name: Minutes Linker
Plugin URI:  http://austin.minutes.city
Description: Allows post authors to easily add links to agenda items on Minutes.city
Version:     0.1
Author:      Alex Schmitz <alex@ssrpventures.com>
License:     Apache License 2.0

Copyright 2016 SSRP Ventures, LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use these files except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

// Prevent script being called directly
defined( 'ABSPATH' ) or die( 'No direct access please.' );

// Call add_meta_boxes function
add_action("add_meta_boxes", "add_minutes_box");

// Define minutes box function
function add_minutes_box(){
    add_meta_box("minutes-meta-box", "Minutes.city Linker", "minutes_callback", "post", "side", "high", null);
}

// Define contents of minutes box - Date, Agenda Items, Insert button, etc. 
function minutes_callback($object){
    wp_nonce_field(basename(__FILE__), "minutes-box-nonce");

    ?>
    <div>
        <label for="minutes-box-dropdown">Meeting Date</label>
        <select name="minutes-box-date-dropdown" id="meetingDateSelector" style="float: right;">
            <!--  Starts off empty, filled via ajax later -->
        </select>
        <br><br>
        <label for="minutes-box-text">Agenda Items</label><br>
        <select style="width:99%;" name="minutes-box-items" id="agendaItemSelector">
            <!--  Starts off empty, filled via ajax later -->
        </select>
        <br>
        <div>
            <br>
            <a class="button" href="#" target="" id="insertURLButton">Insert URL</a>
            <!-- Button gets event listener assigned in .js later on -->
        </div>
    </div>
    <?php

}

// Enqueue our javascript to make sure it gets in the page
add_action("admin_enqueue_scripts", "minutes_javascript_enqueue");

// Actual enqueue code, including localising / adding our vars to script
function minutes_javascript_enqueue(){
    // Enqueue our js file, without a version, and in the footer of the HTML page
    wp_enqueue_script('minutes_script', plugins_url('/js/minutes-functions.js', __FILE__), array('jquery'), false, true );
}

// Ping back to minutes.city, indicating that an article references one or more agenda items
// Send post URL, agenda items by internal_id
// <!-- Minutes.City Link: #56441 -->
function pingMinutesCity($ID, $post){
    $permalink = get_permalink($ID);

    preg_match_all('/###(\d{5})/', $post->post_content, $agendaItems);

    $jsonArray = array();
    $jsonArray['postURL'] = $permalink;
    $jsonArray['agendaItems'] = array();

    foreach ($agendaItems[1] as $item) {
        $jsonArray['agendaItems'][] = $item;
    }

    $url = 'http://austin.minutes.city/AusMon/AusMonPingBack.php';
    $payload = json_encode($jsonArray, JSON_UNESCAPED_SLASHES);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    curl_exec($ch);
    curl_close($ch);
}

add_action('publish_post', 'pingMinutesCity', 10, 2);
