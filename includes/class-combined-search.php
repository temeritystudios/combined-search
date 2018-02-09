<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package    Combined_Search
 * @subpackage Combined_Search/includes
 */

/**
 * The core plugin class.
 *
 * @since 1.0
 */
class Combined_Search
{

    /**
     * @access   protected
     * @var      Combined_Search_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;


    /**
     * @access  protected
     * @var     Combined_Search_Options $options The controller class for our plugin options
     */
    protected $options;


    /**
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the search query.
     */
    public function __construct()
    {

        $this->version = COMBINEDSEARCH_VERSION;
        $this->plugin_name = 'combined-search';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_search_query_hooks();

    }

    /**
     * Load the required dependencies for this plugin and create an instance of the loader which will be used to
     * register the hooks  with WordPress.
     */
    private function load_dependencies()
    {

        require_once COMBINEDSEARCH_DIR . '/includes/class-combined-search-options.php';
        require_once COMBINEDSEARCH_DIR . '/includes/class-combined-search-loader.php';
        require_once COMBINEDSEARCH_DIR . '/includes/class-combined-search-i18n.php';
        require_once COMBINEDSEARCH_DIR . '/includes/class-combined-search-search-query.php';
        require_once COMBINEDSEARCH_DIR . '/admin/class-combined-search-admin.php';

        $this->options = Combined_Search_Options::get_instance();
        $this->loader = new Combined_Search_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Combined_Search_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Combined_Search_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Combined_Search_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_options_page');

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0
     * @access   private
     */
    private function define_search_query_hooks()
    {

        $search_query = new Combined_Search_Search_Query($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $search_query, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $search_query, 'enqueue_scripts');

        if ($this->options->get_option('highlight_terms')) {
            $this->loader->add_filter('the_content', $search_query, 'highlight_search_terms');
            $this->loader->add_filter('the_title', $search_query, 'highlight_search_terms');
            $this->loader->add_filter('the_excerpt', $search_query, 'highlight_search_terms');
        }

        if ($this->options->get_option('enable_search_tags') || $this->options->get_option('enable_search_category_meta') || $this->options->get_option('enable_search_custom_taxonomies')) {
            $this->loader->add_filter('posts_join', $search_query, 'terms_join');
        }


        if ($this->options->get_option('enable_search_comments')) {
            $this->loader->add_filter('posts_join', $search_query, 'comments_join');

            if ($this->options->get_option('highlight_terms')) {
                $this->loader->add_filter('comment_text', $search_query, 'highlight_search_terms');
            }
        }

        if ($this->options->get_option('enable_search_drafts')) {
            $this->loader->add_filter('posts_where', $search_query, 'search_draft_posts');
        }

        if ($this->options->get_option('enable_search_attachments')) {
            $this->loader->add_filter('posts_where', $search_query, 'search_attachments');
        }

        if ($this->options->get_option('enable_search_custom_fields')) {
            $this->loader->add_filter('posts_join', $search_query, 'search_metadata_join');
        }

        if ($this->options->get_option('exclude_taxonomies_list') != '') {
            $this->loader->add_filter('posts_join', $search_query, 'exclude_categories_join');
        }

        if ($this->options->get_option('enable_search_authors')) {
            $this->loader->add_filter('posts_join', $search_query, 'search_authors_join');
        }

        $this->loader->add_filter('posts_search', $search_query, 'search_where', 10, 2);
        $this->loader->add_filter('posts_where', $search_query, 'no_revisions');
        $this->loader->add_filter('posts_request', $search_query, 'distinct');
        $this->loader->add_filter('posts_where', $search_query, 'no_future');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0
     * @return    Combined_Search_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}
