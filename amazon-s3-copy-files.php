<?php
/*
	Plugin Name: Amazon s3 copy files
	Plugin URI:
	Description: This plugin copies files to aws s3 bucket
	Author: UL
	Version: 1.0
 */

require_once(dirname(__FILE__).'/includes/image-management.php');

if ( is_admin() ){ // admin actions
    add_action( 'admin_menu', 'ascf_admin_page' );
    add_action('init', 'ascf_myStartSession', 1);
    add_action('wp_logout', 'ascf_myEndSession');
    add_action('wp_login', 'ascf_myEndSession');
} else {
  // non-admin enqueues, actions, and filters
}

function ascf_myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

function ascf_myEndSession() {
    session_destroy ();
}

/*
 * Add the menu to the admin page
 */
function ascf_admin_page() {
  add_management_page( 'AWS S3 Copy Files' , 'AWS S3 Copy Files', 'manage_options', 'awss3_copy_images', 'ascf_admin_page_callback');
}

/*
 * Admin page code
 */
function ascf_admin_page_callback() {

  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }

  if (isset($_POST['awss3_copy_images']) && $_POST['awss3_copy_images'] == 'copy') {

    $ascf_images = new ASCF_Images();
    $ascf_images->copy_all();

  }

  if (isset($_POST['awss3_offset'])) {

    $_SESSION['ascf_offset'] = $_POST['awss3_offset'];

  }

  require(dirname(__FILE__).'/views/admin-page.php');
}
