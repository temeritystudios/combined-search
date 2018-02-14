<?php
/**
 * Combined Search
 * Add the ability to search through any content type on your WordPress powered web site.
 *
 * @package   Combined_Search
 * @author    Temerity Studios <info@temeritystudios.com>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/plugins/combined-search
 *
 * @wordpress-plugin
 *            Plugin Name:       Combined Search
 *            Plugin URI:        http://wordpress.org/plugins/combined-search
 *            Description:       Forked from the popular Search Everywhere plugin, Combined Search allows you to search all available content types on your WordPress site
 *            Version:           1.0
 *            Author:            Temerity Studios
 *            Author URI:        https://temeritystudios.com/
 *            License:           GPL-2.0+
 *            License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *            Text Domain:       combined-search
 *            Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('COMBINEDSEARCH_VERSION', '1.0');
define('COMBINEDSEARCH_OPTIONS', 'combined-search-options');

define('COMBINEDSEARCH_DIR', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
define('COMBINEDSEARCH_URL', plugins_url() . '/' . basename(dirname(__FILE__)));

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require COMBINEDSEARCH_DIR . '/includes/class-combined-search.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0
 */
function run_combined_search()
{

    $plugin = new Combined_Search();
    $plugin->run();

}

run_combined_search();
