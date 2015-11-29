<?php 

/*

Copyright 2014 Dario Curvino (email : d.curvino@tiscali.it)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if ( ! defined( 'ABSPATH' ) ) exit('You\'re not allowed to see this page'); // Exit if accessed directly

/****** Add shortcode for overall rating ******/
add_shortcode ('yasr_overall_rating', 'shortcode_overall_rating_callback');

function shortcode_overall_rating_callback ($atts) {

    extract( shortcode_atts (
            array(
                'size' => 'large',
                'postid' => FALSE
            ), $atts )
        );

    if(!$postid) {

        $overall_rating=yasr_get_overall_rating();

    }

    else {

        $overall_rating=yasr_get_overall_rating($postid);

    }

    if (!$overall_rating) {
        $overall_rating = "-1";
    }

    $shortcode_html = '';

    if (YASR_TEXT_BEFORE_STARS == 1 && YASR_TEXT_BEFORE_OVERALL != '') {

        $text_before_star = str_replace('%overall_rating%', $overall_rating, YASR_TEXT_BEFORE_OVERALL);

        $shortcode_html = "<div class=\"yasr-container-custom-text-and-overall\">
                                <span id=\"yasr-custom-text-before-overall\">" . $text_before_star . "</span>";

    }

    $stars_attribute = yasr_stars_size($size);

    $shortcode_html .= "<div class=\"$stars_attribute[class]\" id=\"yasr_rateit_overall\" data-rateit-starwidth=\"$stars_attribute[px_size]\" data-rateit-starheight=\"$stars_attribute[px_size]\" data-rateit-value=\"$overall_rating\" data-rateit-step=\"0.1\" data-rateit-resetable=\"false\" data-rateit-readonly=\"true\"></div>"; 


    if (YASR_TEXT_BEFORE_STARS == 1 && YASR_TEXT_BEFORE_OVERALL != '') {

        $shortcode_html .= "</div>";
    
    }

    //IF show overall rating in loop is disabled use is_singular && is_main query
    if ( YASR_SHOW_OVERALL_IN_LOOP === 'disabled' ) {

        if( is_singular() && is_main_query() ) {

            return $shortcode_html;

        }

    } // End if YASR_SHOW_OVERALL_IN_LOOP === 'disabled') {

    //If overall rating in loop is enabled don't use is_singular && is main_query
    elseif ( YASR_SHOW_OVERALL_IN_LOOP === 'enabled' ) {

        return $shortcode_html;

    }

} //end function


/****** Add shortcode for user vote ******/

add_shortcode ('yasr_visitor_votes', 'shortcode_visitor_votes_callback');

function shortcode_visitor_votes_callback ($atts) {

    $shortcode_html = NULL; //Avoid undefined variable outside is_singular && is_main_query

    extract( shortcode_atts (
            array(
                'size' => 'large',
                'postid' => FALSE
            ), $atts )
        );

    //If it's not specified use get_the_id
    if (!$postid) {

        $post_id = get_the_ID();

    }

    else {

        $post_id = $postid;

    }

    $ajax_nonce_visitor = wp_create_nonce( "yasr_nonce_insert_visitor_rating" );

    $votes=yasr_get_visitor_votes($post_id); //always reference it

    $medium_rating=0;   //Avoid undefined variable

    if (!$votes) {
        $votes=0;         //Avoid undefined variable if there is not overall rating
        $votes_number=0;  //Avoid undefined variable
    }

    else {
        foreach ($votes as $user_votes) {
            $votes_number = $user_votes->number_of_votes;
            if ($votes_number != 0 ) {
                $medium_rating = ($user_votes->sum_votes/$votes_number);
            }
            else {
                $medium_rating = 0;
            }
        }
    }

    $medium_rating=round($medium_rating, 1);

    $stars_attribute = yasr_stars_size($size);

    $yasr_cookiename = 'yasr_visitor_vote_' . $post_id;

    if (isset($_COOKIE[$yasr_cookiename])) { 

        $cookie_value = $_COOKIE[$yasr_cookiename];

        $cookie_value = (int)$cookie_value;

        if ($cookie_value > 5) {

            $cookie_value = 5;

        }

        elseif ($cookie_value < 1) {

            $cookie_value = 1;

        }

    }

    else {

        $cookie_value = FALSE;

    }

    $vote_if_user_already_rated = FALSE;

    $shortcode_html = "<div id=\"yasr_visitor_votes_$post_id\" class=\"yasr-visitor-votes\">";
    $span_after_rate_it = "";

    //if anonymous are allowed to vote
    if (YASR_ALLOWED_USER === 'allow_anonymous') {

        //I've to checl a logged in user that has already rated
        if ( is_user_logged_in() ) {

            $readonly = 'false'; 

            //Chek if a logged in user has already rated for this post
            $vote_if_user_already_rated = yasr_check_if_user_already_voted($post_id);

            //If user has already rated 
            if ($vote_if_user_already_rated) {

                $span_after_rate_it="<span class=\"yasr-small-block-bold\" id=\"yasr-already-voted-text\">" . __("You've already voted this article with", 'yet-another-stars-rating') . " $vote_if_user_already_rated </span>";

            }

        } //End if user is logged

        else {

            //if cookie exists
            if($cookie_value) {

                $readonly = 'true';

                if (YASR_TEXT_BEFORE_STARS == 1 && YASR_CUSTOM_TEXT_USER_VOTED!='') {

                    $span_after_rate_it = $span_after_rate_it="<span class=\"yasr-small-block-bold\" id=\"yasr-already-voted-text\">" . YASR_CUSTOM_TEXT_USER_VOTED . " </span>";; 

                }

                else {

                    $span_after_rate_it="<span class=\"yasr-small-block-bold\" id=\"yasr-already-voted-text\">" . __("You've already voted this article with", 'yet-another-stars-rating') . " $cookie_value </span>";

                }

            }

            else {

                $readonly = 'false';

            }

        }
  
    } //end if  ($allow_logged_option['allowed_user']==='allow_anonymous') {


    //If only logged in users can vote
    elseif (YASR_ALLOWED_USER === 'logged_only') {

        //If user is logged in and can vote
        if ( is_user_logged_in() ) {

            $readonly = 'false'; //REadonly is false if user is logged

            //Chek if a logged in user has already rated for this post
            $vote_if_user_already_rated = yasr_check_if_user_already_voted($post_id);

            if ($vote_if_user_already_rated) {

                $span_after_rate_it="<span class=\"yasr-small-block-bold\" id=\"yasr-already-voted-text\">" . __("You've already voted this article with", 'yet-another-stars-rating') . " $vote_if_user_already_rated </span>";

            }

        } //End if user is logged in

        //Else mean user is not logged in and can't vote
        else {

            $readonly = 'true'; //readonly is true if user isn't logged

            $span_after_rate_it = __("You must sign in to vote", 'yet-another-stars-rating');

        }

    }

    if (YASR_VISITORS_STATS === 'yes') {

        $span_dashicon = "<span class=\"dashicons dashicons-chart-bar yasr-dashicons-visitor-stats \" id=\"yasr-total-average-dashicon-$post_id\" title=\"yasr-stats-dashicon\"></span>";

    }

    else {

        $span_dashicon = "";

    }

    if(YASR_TEXT_BEFORE_STARS == 1 && YASR_TEXT_BEFORE_VISITOR_RATING != '') {

        $text_before_star = str_replace('%total_count%', $votes_number, YASR_TEXT_BEFORE_VISITOR_RATING);

        $text_before_star = str_replace('%average%', $medium_rating, $text_before_star);

        $shortcode_html .= "<div class=\"yasr-container-custom-text-and-visitor-rating\">
        <span id=\"yasr-custom-text-before-visitor-rating\">" . $text_before_star . "</span></div>"; 

    }

    if(YASR_TEXT_BEFORE_STARS == 1 && YASR_TEXT_AFTER_VISITOR_RATING != '') {

        $text_after_star = str_replace('%total_count%', $votes_number, YASR_TEXT_AFTER_VISITOR_RATING);

        $text_after_star = str_replace('%average%', $medium_rating, $text_after_star);

        $span_text_after_star = "<span class=\"yasr-total-average-container\" id=\"yasr-total-average-text_$post_id\">" . $text_after_star . "</span>";

    }

    else {

        $span_text_after_star = "<span class=\"yasr-total-average-container\" id=\"yasr-total-average-text_$post_id\">
                [" . __("Total: ", 'yet-another-stars-rating') . "$votes_number &nbsp; &nbsp;" .  __("Average: ",'yet-another-stars-rating') . "$medium_rating/5]
            </span>";

    }

    $shortcode_html .= "<div class=\"$stars_attribute[class]\" id=\"yasr_rateit_visitor_votes_$post_id\" data-postid=\"$post_id\" data-rateit-starwidth=\"$stars_attribute[px_size]\" data-rateit-starheight=\"$stars_attribute[px_size]\" data-rateit-value=\"$medium_rating\" data-rateit-step=\"1\" data-rateit-resetable=\"false\" data-rateit-readonly=\"$readonly\"></div>";

    $shortcode_html .= $span_dashicon;

    $shortcode_html .= $span_text_after_star;

    $shortcode_html .= $span_after_rate_it;

    $shortcode_html .= "</div>";

    wp_localize_script( 'yasrfront', "yasrVisitorsVotesData", array(
        'voteIfUserAlredyRated' => $vote_if_user_already_rated,
        'nonceVisitor' => $ajax_nonce_visitor
        ) 
    );


    //IF show visitor votes in loop is disabled use is_singular && is_main query
    if ( YASR_SHOW_VISITOR_VOTES_IN_LOOP === 'disabled' ) {

        if( is_singular() && is_main_query() ) {

            return $shortcode_html;

        }

    } // End if YASR_SHOW_VISITOR_VOTES_IN_LOOP === 'disabled') {

    //If overall rating in loop is enabled don't use is_singular && is main_query
    elseif ( YASR_SHOW_VISITOR_VOTES_IN_LOOP === 'enabled' ) {

        return $shortcode_html;

    }

} //End function shortcode_visitor_votes_callback


/****** Show visitor votes average, READ ONLY ******/
add_shortcode ('yasr_visitor_votes_readonly', 'yasr_visitor_votes_readonly_callback');

function yasr_visitor_votes_readonly_callback ($atts) {

    $shortcode_html = NULL; //Avoid undefined variable outside is_singular && is_main_query

    extract( shortcode_atts (
            array(
                'size' => 'small',
                'postid' => FALSE
            ), $atts )
        );

    //If it's not specified use get_the_id
    if (!$postid) {

        $post_id = get_the_ID();

    }

    else {

        $post_id = $postid;

    }

    $votes=yasr_get_visitor_votes($post_id);

    $medium_rating=0;   //Avoid undefined variable

    if (!$votes) {
        $votes=0;         //Avoid undefined variable if there is not overall rating
        $votes_number=0;  //Avoid undefined variable
    }

    else {
        foreach ($votes as $user_votes) {
            $votes_number = $user_votes->number_of_votes;
            if ($votes_number != 0 ) {
                $medium_rating = ($user_votes->sum_votes/$votes_number);
            }
            else {
                $medium_rating = 0;
            }
        }
    }

    $medium_rating=round($medium_rating, 1);

    $stars_attribute = yasr_stars_size($size);

    $shortcode_html = "<div id=\"yasr_visitor_votes_readonly_$post_id\" class=\"yasr-visitor-votes_readonly\">";
    $span_after_rate_it = "";

    $shortcode_html .= "<div class=\"$stars_attribute[class]\" id=\"yasr_rateit_visitor_votes_readonly_$post_id\" data-rateit-starwidth=\"$stars_attribute[px_size]\" data-rateit-starheight=\"$stars_attribute[px_size]\" data-rateit-value=\"$medium_rating\" data-rateit-step=\"1\" data-rateit-resetable=\"false\" data-rateit-readonly=\"true\"></div>";

    $shortcode_html .= "</div>";


        //IF show visitor votes in loop is disabled use is_singular && is_main query
        if ( YASR_SHOW_VISITOR_VOTES_IN_LOOP === 'disabled' ) {

            if( is_singular() && is_main_query() ) {

                return $shortcode_html;

            }

        } // End if YASR_SHOW_VISITOR_VOTES_IN_LOOP === 'disabled') {

        //If overall rating in loop is enabled don't use is_singular && is main_query
        elseif ( YASR_SHOW_VISITOR_VOTES_IN_LOOP === 'enabled' ) {

            return $shortcode_html;

        }

   // } //End (!is_feed)

} //End function shortcode_visitor_votes_only_stars_callback


/****** Add shortcode for multiple set ******/

add_shortcode ('yasr_multiset', 'shortcode_multi_set_callback');

function shortcode_multi_set_callback( $atts ) {

	global $wpdb;
	
	// Attributes
	extract( shortcode_atts(
		array(
			'setid' => '0',
            'postid' => FALSE
		), $atts )
	);

    //If it's not specified use get_the_id
    if (!$postid) {

        $post_id = get_the_ID();

    }

    else {

        $post_id = $postid;

    }

    $shortcode_html = ""; //Avoid undefined variable if used a missing setid

	$set_name_content=yasr_get_multi_set_values_and_field ($post_id, $setid);

	if ($set_name_content) {
		$shortcode_html="<table class=\"yasr_table_multi_set_shortcode\">";
     	foreach ($set_name_content as $set_content) {
        	$shortcode_html .=  "<tr> <td><span class=\"yasr-multi-set-name-field\">$set_content->name </span></td>
      		   					 <td><div class=\"rateit\" id=\"$set_content->id\" data-rateit-value=\"$set_content->vote\" data-rateit-step=\"0.5\" data-rateit-resetable=\"false\" data-rateit-readonly=\"true\"></div></td>
        						 </tr>";
        }
    	$shortcode_html.="</table>";
    }

    else {
        $set_name=$wpdb->get_results($wpdb->prepare("SELECT field_name AS name, field_id AS id
                    FROM " . YASR_MULTI_SET_FIELDS_TABLE . "  
                    WHERE parent_set_id=%f 
                    ORDER BY field_id ASC", $setid));

        $shortcode_html="<table class=\"yasr_table_multi_set_shortcode\">";

        foreach ($set_name as $set_content) {
            $shortcode_html .=  "<tr> <td><span class=\"yasr-multi-set-name-field\">$set_content->name </span></td>
                                 <td><div class=\"rateit\" id=\"$set_content->id\" data-rateit-value=\"0\" data-rateit-step=\"0.5\" data-rateit-resetable=\"false\" data-rateit-readonly=\"true\"></div></td>
                                 </tr>";
        }
        $shortcode_html.="</table>";
        
    }

	return $shortcode_html;
	} //End function


/****** Add shortcode for multiset writable by users  ******/

add_shortcode ('yasr_visitor_multiset', 'yasr_visitor_multiset_callback');

function yasr_visitor_multiset_callback ( $atts ) {

    $ajax_nonce_visitor_multiset = wp_create_nonce( "yasr_nonce_insert_visitor_rating_multiset" );

    global $wpdb;
    
    // Attributes
    extract( shortcode_atts(
        array(
            'setid' => '0',
            'postid' => FALSE
        ), $atts )
    );

    //If it's not specified use get_the_id
    if (!$postid) {

        $post_id = get_the_ID();

    }

    else {

        $post_id = $postid;

    }

    $cookiename = 'yasr_multi_visitor_cookie_' . $post_id . '_' . $setid;

    $image = YASR_IMG_DIR . "/loader.gif";

    $loader_html = "<span class=\"yasr-loader-multiset-visitor\" id=\"yasr-loader-multiset-visitor-$post_id-$setid\" >&nbsp; " . __("Loading, please wait",'yet-another-stars-rating') . ' <img src=' .  "$image" .' title="yasr-loader" alt="yasr-loader"></span>';


    if (isset($_COOKIE[$cookiename])) {

            $button = "";
            $star_readonly = 'true';
            $span_message_content = __('Thank you for voting! ', 'yet-another-stars-rating');

        }

    else {

        //If user is not logged in
        if (!is_user_logged_in()) {

            if (YASR_ALLOWED_USER === 'allow_anonymous') {

                $button = "<input type=\"submit\" name=\"submit\" id=\"yasr-send-visitor-multiset-$post_id-$setid\" class=\"button button-primary\" value=\"". __('Submit!', 'yet-another-stars-rating') . " \"  />";
                $star_readonly = 'false';
                $span_message_content = "";

            }

            elseif (YASR_ALLOWED_USER === 'logged_only') {

                $button = "<input type=\"submit\" name=\"submit\" id=\"yasr-send-visitor-multiset-$post_id-$setid\" class=\"button button-primary\" value=\"". __('Submit!', 'yet-another-stars-rating') . " \"  />";
                $star_readonly = 'true';
                $span_message_content = __("You must sign in to vote", 'yet-another-stars-rating');;

            }


        } //End if user logged in

        //Is user is logged in
        else {

                $button = "<input type=\"submit\" name=\"submit\" id=\"yasr-send-visitor-multiset-$post_id-$setid\" class=\"button button-primary\" value=\"" . __('Submit!', 'yet-another-stars-rating') . " \"  />";
                $star_readonly = 'false';
                $span_message_content = "";
            
            }

    }

    $set_name_content = yasr_get_multi_set_visitor ($post_id, $setid);

    if ($set_name_content) {

        $shortcode_html="<table class=\"yasr_table_multi_set_shortcode\">";

        foreach ($set_name_content as $set_content) {

            if($set_content->number_of_votes > 0) {

                $average_rating = $set_content->sum_votes / $set_content->number_of_votes;

                $average_rating = round($average_rating, 1);

            }

            else {

                $average_rating = 0;

            }

            $shortcode_html .=  "<tr> 
                                    <td>
                                        <span class=\"yasr-multi-set-name-field\">$set_content->name </span>
                                    </td>
                                    <td>
                                        <div class=\"rateit yasr-visitor-multi-$post_id-$setid\" id=\"$set_content->id \" data-rateit-value=\"$average_rating\" data-rateit-step=\"1\" data-rateit-resetable=\"false\" data-rateit-readonly=\"$star_readonly\"></div>
                                        <span class=\"yasr-visitor-multiset-vote-count\">$set_content->number_of_votes</span>
                                    </td>
                                 </tr>";
        }

        $shortcode_html.="<tr>
                            <td colspan=\"2\">
                                $button
                                $loader_html
                                <span class=\"yasr-visitor-multiset-message\">$span_message_content</span> 
                            </td>
                        </tr>
                        </table>";
    }

    else {

        $set_name=$wpdb->get_results($wpdb->prepare("SELECT field_name AS name, field_id AS id
                    FROM " . YASR_MULTI_SET_FIELDS_TABLE . "  
                    WHERE parent_set_id=%d 
                    ORDER BY field_id ASC", $setid));


        $shortcode_html="<table class=\"yasr_table_multi_set_shortcode\">";

        foreach ($set_name as $set_content) {

            $shortcode_html .=  "<tr> 
                                    <td>
                                        <span class=\"yasr-multi-set-name-field\">$set_content->name </span>
                                    </td>
                                    <td>
                                        <div class=\"rateit yasr-visitor-multi-$post_id-$setid\" id=\"$set_content->id\" data-rateit-value=\"0\" data-rateit-step=\"0.5\" data-rateit-resetable=\"false\" data-rateit-readonly=\"false\"></div> 
                                        <span class=\"yasr-visitor-multiset-vote-count\"> 0 </span> 
                                    </td>
                                 </tr>";



            //First time, initialize all fields to 0

            //Find the highest_id (it's not auto increment on  db due to gd star compatibility)
            $highest_id=$wpdb->get_results("SELECT id FROM " . YASR_MULTI_SET_VALUES_TABLE . " ORDER BY id DESC LIMIT 1 ");
        
            //highest id is 0 in data is empty
            if (!$highest_id) {
                $new_id=0;
            }

            //or is n+1
            foreach ($highest_id as $id) {
               $new_id=$id->id + 1;
            }

            $wpdb->replace(
                    YASR_MULTI_SET_VALUES_TABLE,
                    array (
                            'id'=>$new_id,
                            'post_id'=>$post_id,
                            'field_id'=>$set_content->id,
                            'set_type'=>$setid,
                            'number_of_votes' => 0,
                            'sum_votes' => 0
                            ),
                    array ("%d", "%d", "%d",  "%d", "%d", "%d")
                    );


        } //end foreach ($set_name as $set_content)

        $shortcode_html.="<tr>
                            <td colspan=\"2\">
                                $button
                                $loader_html
                                <span class=\"yasr-visitor-multiset-message\">$span_message_content</span> 
                            </td>
                        </tr>
                        </table>";

        $shortcode_html.="</table>";

    }

    wp_localize_script( 'yasrfront', 'yasrMultiSetData', array(
        'setType' => $setid,
        'nonce' => $ajax_nonce_visitor_multiset,
        'postid' => $post_id
        )
    );


    return $shortcode_html;

}



/****** Add top 10 highest rated post *****/

add_shortcode ('yasr_top_ten_highest_rated', 'yasr_top_ten_highest_rated_callback');

function yasr_top_ten_highest_rated_callback () {

    global $wpdb;

    $query_result = $wpdb->get_results("SELECT v.overall_rating, v.post_id
                                        FROM " . YASR_VOTES_TABLE . " AS v, $wpdb->posts AS p
                                        WHERE  v.post_id = p.ID
                                        AND p.post_status = 'publish'
                                        AND v.overall_rating > 0
                                        ORDER BY v.overall_rating DESC, v.id ASC LIMIT 10");

    if ($query_result) {

        $shortcode_html = "<table class=\"yasr-table-chart\">";

        foreach ($query_result as $result) {

            $post_title = get_the_title($result->post_id);

            $link = get_permalink($result->post_id); //Get permalink from post it

            $shortcode_html .= "<tr>
                                    <td width=\"60%\"><a href=\"$link\">$post_title</a></td>
                                    <td width=\"40%\">
                                        <div class=\"rateit medium\" data-rateit-starwidth=\"24\" data-rateit-starheight=\"24\" data-rateit-value=\"$result->overall_rating\" data-rateit-step=\"0.1\" data-rateit-resetable=\"false\" data-rateit-readonly=\"true\"></div>
                                        <span class=\"yasr-highest-rated-text\">" . __("Rating", 'yet-another-stars-rating') . " $result->overall_rating </span>
                                        </td>
                                </tr>";


        } //End foreach

        $shortcode_html .= "</table>";

        return $shortcode_html;

    } //end if $query_result

    else {
        _e("You don't have any votes stored", 'yet-another-stars-rating');
    }

} //End function


/****** Add top 10 most rated / highest rated post *****/

add_shortcode ('yasr_most_or_highest_rated_posts', 'yasr_most_or_highest_rated_posts_callback');

function yasr_most_or_highest_rated_posts_callback () {


    $shortcode_html = "";

    global $wpdb;

    $query_result_most_rated = $wpdb->get_results("SELECT post_id, number_of_votes, sum_votes
                                        FROM " . YASR_VOTES_TABLE . ", $wpdb->posts AS p 
                                        WHERE post_id = p.ID
                                        AND number_of_votes >= 1
                                        AND p.post_status = 'publish'
                                        ORDER BY number_of_votes DESC, sum_votes DESC LIMIT 10");

    $query_result_highest = $wpdb->get_results("SELECT (sum_votes / number_of_votes) as result, post_id, number_of_votes
                                        FROM " . YASR_VOTES_TABLE . ", $wpdb->posts AS p 
                                        WHERE post_id = p.ID
                                        AND number_of_votes >= 2
                                        AND p.post_status = 'publish'
                                        ORDER BY result DESC, number_of_votes DESC LIMIT 10
                                        ");

    if ($query_result_most_rated) {

        $shortcode_html .= "<table class=\"yasr-table-chart\" id=\"yasr-most-rated-posts\">
                        <tr>
                            <th>" . __("Post / Page" , 'yet-another-stars-rating') ." </th>
                            <th>". __("Order By" , 'yet-another-stars-rating') .":&nbsp;&nbsp;<span id=\"yasr_multi_chart_link_to_nothing\">" . __("Most Rated" , 'yet-another-stars-rating') ."</span> | <a href=\"#\" id=\"yasr_multi_chart_highest\">" . __("Highest Rated" , 'yet-another-stars-rating') ."</a></th>
                        </tr>"
                        ;

        foreach ($query_result_most_rated as $result) {

            $rating = $result->sum_votes / $result->number_of_votes;

            $rating = round($rating, 1);

            $post_title = get_the_title($result->post_id);

            $link = get_permalink($result->post_id); //Get permalink from post it

            $shortcode_html .= "<tr>
                        <td width=\"60%\"><a href=\"$link\">$post_title</a></td>
                            <td width=\"40%\"><div id=\"yasr_visitor_votes\">
                                <div class=\"rateit medium\" data-rateit-starwidth=\"24\" data-rateit-starheight=\"24\" data-rateit-value=\"$rating\" data-rateit-step=\"0.1\" data-rateit-resetable=\"false\" data-rateit-readonly=\"true\"></div>
                                <br /> [" .  __("Total:" , 'yet-another-stars-rating') . "$result->number_of_votes &nbsp;&nbsp;&nbsp;" . __("Average" , 'yet-another-stars-rating') . " $rating]
                            </td>
                    </tr>";


        } //End foreach

        $shortcode_html .= "</table>" ;

    } //End if $query_result_most_rated)

    else {
        $shortcode_html = __("You've not enough data",'yet-another-stars-rating') . "<br />";
    }

    
    if ($query_result_highest) {

        $shortcode_html .= "<table class=\"yasr-table-chart\" id=\"yasr-highest-rated-posts\">
                        <tr>
                            <th>" . __("Post / Page" , 'yet-another-stars-rating') ." </th>
                            <th>". __("Order By" , 'yet-another-stars-rating') .":&nbsp;&nbsp; <a href=\"#\" id=\"yasr_multi_chart_most\">". __("Most Rated" , 'yet-another-stars-rating') ."</a> | <span id=\"yasr_multi_chart_link_to_nothing\">". __("Highest Rated" , 'yet-another-stars-rating') ."</span></th>
                        </tr>";

        foreach ($query_result_highest as $result) {

            $rating = round($result->result, 1);

            $post_title = get_the_title($result->post_id);

            $link = get_permalink($result->post_id); //Get permalink from post it

            $shortcode_html .= "<tr>
                        <td width=\"60%\"><a href=\"$link\">$post_title</a></td>
                        <td width=\"40%\"><div id=\"yasr_visitor_votes\"><div class=\"rateit medium\" data-rateit-starwidth=\"24\" data-rateit-starheight=\"24\" data-rateit-value=\"$rating\" data-rateit-step=\"0.1\" data-rateit-resetable=\"false\" data-rateit-readonly=\"true\"></div>
                            <br /> [" .  __("Total:" , 'yet-another-stars-rating') . "$result->number_of_votes &nbsp;&nbsp;&nbsp;" . __("Average" , 'yet-another-stars-rating') . " $rating]
                        </td>
                   </tr>";


        } //End foreach

        $shortcode_html .= "</table>";

    } //end if $query_result

    else {
        $shortcode_html = __("You've not enought data",'yet-another-stars-rating') . "<br />";
    }

    ?>

    <script type="text/javascript">

        document.addEventListener('DOMContentLoaded', function(event) {

            yasrMostOrHighestRatedChart ();

        });


    </script>

    <?php

    return $shortcode_html;


} //End function


/****** Add top 5 most active reviewer ******/

add_shortcode ('yasr_top_5_reviewers', 'yasr_top_5_reviewers_callback');

function yasr_top_5_reviewers_callback () {

    global $wpdb;

    $query_result = $wpdb->get_results("SELECT COUNT( post_author ) as total_count, post_author as reviewer
                                        FROM $wpdb->posts AS p, " . YASR_VOTES_TABLE . " 
                                        WHERE  post_id = p.ID
                                        AND p.post_status = 'publish'
                                        GROUP BY post_author
                                        ORDER BY (total_count) DESC
                                        LIMIT 5");


    if ($query_result) {

        $shortcode_html = "
        <table class=\"yasr-table-chart\">
        <tr>
         <th>Author</th>
         <th>Reviews</th>
        </tr>
        ";

        foreach ($query_result as $result) {

            $user_data = get_userdata($result->reviewer);

            if ($user_data) {

                $user_profile = get_author_posts_url($result->reviewer);

            }

            else {

                $user_profile = '#';
                $user_data = new stdClass;
                $user_data->user_login = 'Anonymous';
            
            }


            $shortcode_html .= "<tr>
                                    <td><a href=\"$user_profile\">$user_data->user_login</a></td>
                                    <td>$result->total_count</td>
                                </tr>";
                                
        }

        $shortcode_html .= "</table>";

        return $shortcode_html;

    }

    else {

        _e("Problem while retrieving the top 5 most active reviewers. Did you publish any review?");

    }


} //End top 5 reviewers function





/****** Add top 10 most active user *****/

add_shortcode ('yasr_top_ten_active_users', 'yasr_top_ten_active_users_callback');

function yasr_top_ten_active_users_callback () {

    global $wpdb;

    $query_result = $wpdb->get_results("SELECT COUNT( user_id ) as total_count, user_id as user
                                        FROM " . YASR_LOG_TABLE . ", $wpdb->posts AS p
                                        WHERE  post_id = p.ID
                                        AND p.post_status = 'publish'
                                        GROUP BY user_id 
                                        ORDER BY ( total_count ) DESC
                                        LIMIT 10");

    if ($query_result) {

        $shortcode_html = "
        <table class=\"yasr-table-chart\">
        <tr>
         <th>UserName</th>
         <th>Number of votes</th>
        </tr>
        ";

        foreach ($query_result as $result) {

            $user_data = get_userdata($result->user);

            if ($user_data) {

                $user_profile = get_author_posts_url($result->user);

            }

            else {
                $user_profile = '#';
                $user_data = new stdClass;
                $user_data->user_login = 'Anonymous';
            }

            $shortcode_html .= "<tr>
                                    <td><a href=\"$user_profile\">$user_data->user_login</a></td>
                                    <td>$result->total_count</td>
                                </tr>";

        }


        $shortcode_html .= "</table>";

        return $shortcode_html;

    }

    else {
        _e("Problem while retrieving the top 10 active users chart. Are you sure you have votes to show?");
    }


} //End function

?>
