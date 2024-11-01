<?php

/*
Plugin Name: Stopwords for Comments
Description: Pre-moderatation for user comments
Version: 1.1
Author: jekko
Author URI: https://one-byten.space/
Plugin URI: https://one-byten.space/stopwords_for_comments
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tested up to: 5.7.2
Stable tag: 1.1
*/

header("Content-Type: text/html; charset=utf-8");

require_once ABSPATH . 'wp-admin/includes/post.php';

################################################################

add_action( 'admin_enqueue_scripts', 'stopwords_for_comments_styles', 100);        

function stopwords_for_comments_styles() {

    wp_enqueue_style( 'stopwords-for-comments', plugin_dir_url(__FILE__) . 'css/stopwords-for-comments.css?t='.time() );
}

######################################################################

add_action( 'admin_enqueue_scripts', 'stopwords_for_comments_scripts' );

function stopwords_for_comments_scripts() {
    
    wp_enqueue_script( 'stopwords-for-comments', plugin_dir_url(__FILE__) . 'js/stopwords-for-comments.js?t='.time(), array( 'jquery' ), '', True );
}

######################################################################################

add_action('admin_menu', 'stopwords_for_comments_menu' ); 

function stopwords_for_comments_menu() {

    add_submenu_page( 'edit-comments.php', 'Stopwords for comments', 'Stopwords for comments', 'manage_options', 'stopwords-for-comments/index.php', '', 'dashicons-update' );
}

#######################################################################################

register_activation_hook( __FILE__, 'stopwords_for_comments_on_activate' );

function stopwords_for_comments_on_activate() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'stopwords_for_comments';
 
    $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
 
    if ( $wpdb->get_var( $query ) === $table_name ) {

        return true;
    }

    else {

        create_stopwords_for_comments_table();
        #setDefaultMarkup();
    }       
}

######################################################################

function create_stopwords_for_comments_table() {

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'stopwords_for_comments';

    $sql = "CREATE TABLE $table_name (

        id INTEGER NOT NULL AUTO_INCREMENT,
        stopword TEXT NOT NULL,
        PRIMARY KEY (id)
        ) $charset_collate;";

    dbDelta( $sql );
}

###########################################################################

add_action( 'wp_ajax_get_stopwords_for_comments', 'get_stopwords_for_comments' );

add_action( 'wp_ajax_nopriv_get_stopwords_for_comments', 'get_stopwords_for_comments' );

function get_stopwords_for_comments() {
    
    global $wpdb;

    $stopwords_for_comments = array();

    $result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix .'stopwords_for_comments');

    foreach ($result as $value) {

        $stopword_id = esc_html($value -> id);
        
        $stopword = esc_html($value -> stopword);

        $stopword = $value -> stopword;

        echo '<div class="one_byten_stopwords_for_comments_current_list_item" data-stopword_id="'.$stopword_id.'"><div class="one_byten_stopwords_for_comments_current_list_item_name">'.$stopword.'</div><div class="one_byten_stopwords_for_comments_current_list_item_delete_symbol">x</div></div>';

        #exit();
    }

	exit();	
}

###########################################################################

function get_stopwords_for_comments_list() {
    
    global $wpdb;

    $stopwords_for_comments = array();

    $result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix .'stopwords_for_comments');

    $stopword_list = array();

    foreach ($result as $value) {
        
        $stopword = $value -> stopword;

        array_push($stopword_list, $stopword);
    }

    return $stopword_list;

    die(); 
}

###########################################################################

add_action( 'wp_ajax_delete_stopwords_for_comments', 'delete_stopwords_for_comments' );

function delete_stopwords_for_comments() {
    
    global $wpdb;

    $stopword_id = trim(sanitize_text_field($_GET['stopword_id']));

    $wpdb->delete( $wpdb->prefix . 'stopwords_for_comments', array( 'id' => $stopword_id ) );

    echo '<div class="result_message_row notice notice-success">Updated !</div>';

    die(); 
}



##############################################################################

add_action( 'wp_ajax_set_stopwords_for_comments', 'set_stopwords_for_comments' );

function set_stopwords_for_comments() {

	if(isset($_GET['stopword'])) {

        $stopword = trim(sanitize_text_field($_GET['stopword']));

        if(strlen($stopword) > 0) {

            #echo "$stopword";

    		insert_into_stopwords_for_comments($stopword);

    		echo '<div class="result_message_row notice notice-success">Updated !</div>';
        }

        else {

            echo '<div class="result_message_row notice notice-error">Please enter stopword !</div>';   
        }
	}

    die();
}

###############################################################################

function insert_into_stopwords_for_comments($stopword) {

    global $wpdb;

    $table = $wpdb->prefix . 'stopwords_for_comments';

    $data = array('stopword' => $stopword);

    $format = array('%s','%d');

    $wpdb->insert($table, $data, $format);

    $my_id = $wpdb->insert_id;

    return $my_id;
}

##########################################################################

add_filter('preprocess_comment', 'stopword_in_comment');

function stopword_in_comment($commentdata) {

    $stopwords_for_comments_list = get_stopwords_for_comments_list();

    foreach ($stopwords_for_comments_list as $stopword) {

        $stopword = mb_strtolower($stopword);

        if (!is_admin() and strstr(mb_strtolower($commentdata['comment_content']), $stopword )) {
            
            wp_die(__("Your comment contains a blacklisted word!").'<br /><br /><a href="javascript:history.go(-1);">'.__("← Go back to edit").'</a>');
        }
    }

    return $commentdata;
}

##########################################################################