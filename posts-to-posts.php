<?php
/*
Plugin Name: BP Posts 2 Posts
Description: Create many-to-many relationships between all types of posts.
Version: 1.6.3
Author: scribu
Author URI: http://scribu.net/
Plugin URI: http://scribu.net/wordpress/posts-to-posts
Text Domain: posts-to-posts
Domain Path: /lang
*/

function _p2p_load() {
	require_once dirname( __FILE__ ) . '/core/init.php';

	register_uninstall_hook( __FILE__, array( 'P2P_Storage', 'uninstall' ) );
}
scb_init( '_p2p_load' );

function _p2p_init() {
	// Safe hook for calling p2p_register_connection_type()
	do_action( 'p2p_init' );
}
add_action( 'wp_loaded', '_p2p_init' );
