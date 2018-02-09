<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0
 * @package    combined-search
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


/**
 * Some people just don't know how cool this plugin is. When they realize
 * it and come back later, let's make sure they have to start all over.
 *
 * @return void
 */
function combined_search_uninstall() {
    // Delete associated options
    delete_option(COMBINEDSEARCH_OPTIONS);
}

// G'bye!
combined_search_uninstall();

