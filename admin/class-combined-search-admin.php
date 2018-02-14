<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0
 *
 * @package    Combined_Search
 * @subpackage Combined_Search/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Combined_Search
 * @subpackage Combined_Search/admin
 * @author     Your Name <email@example.com>
 */
class Combined_Search_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = Combined_Search_Options::get_instance();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0
     */
    public function enqueue_styles()
    {

        $screen = get_current_screen();

        if ('settings_page_combined_search' == $screen->base) {
            wp_enqueue_style($this->plugin_name, COMBINEDSEARCH_URL . '/assets/dist/css/admin.min.css', array(), $this->version, 'all');
        }

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0
     */
    public function enqueue_scripts()
    {

        //wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/combined-search-admin.js', array( 'jquery' ), $this->version, false );

    }

    public function add_options_page()
    {
        add_options_page('Combined Search Settings', 'Combined Search', 'manage_options', 'combined_search', array($this, 'option_page'));
    }

    public function option_page()
    {
        global $wpdb, $table_prefix, $wp_version;

        if ($_POST) {
            check_admin_referer('combined-search-nonce');

            $errors = $this->validate_options(
                array(
                    'highlight_terms_background' => 'color',
                    'exclude_taxonomies_list' => 'numeric-comma',
                    'exclude_posts_list' => 'numeric-comma',
                )
            );

            if ($errors) {
                $fields = array(
                    "highlight_terms_background" => __('Highlight background color', 'combined-search'),
                    "exclude_taxonomies_list" => __('Exclude Taxonomies', 'combined-search'),
                    "exclude_posts_list" => __('Exclude by Post ID', 'combined-search'),
                );

                $data = '';

                foreach ($errors as $field => $message) {
                    $data .= '<li>' . sprintf($message, $fields[$field]) . '</li>';
                }

                $this->get_partial('options-errors', $data);

            } else {
                $new_options = array();
                $option_keys = $this->options->get_default_options(true);

                foreach ($option_keys as $key) {
                    if (false !== strpos($key, 'enable')) {
                        $new_options[$key] = isset($_POST[$key]) ? 1 : 0;
                    }
                }

                $new_options['highlight_terms'] = isset($_POST['highlight_terms']) ? 1 : 0;
                $new_options['highlight_terms_background'] = isset($_POST['highlight_terms_background']) ? $_POST['highlight_terms_background'] : '';
                $new_options['exclude_taxonomies_list'] = isset($_POST['exclude_taxonomies_list']) ? $_POST['exclude_taxonomies_list'] : '';
                $new_options['exclude_posts_list'] = isset($_POST['exclude_posts_list']) ? $_POST['exclude_posts_list'] : '';

                $this->options->set_options($new_options);
                echo "<div class=\"updated fade\" ><p>" . __('Your default search settings have been <strong>updated. ', 'combined-search') . "</p></div>";
            }
        }

        $this->get_partial('options-display');

    }

    protected function get_partial($partial, $data = null)
    {
        include COMBINEDSEARCH_DIR . '/admin/partials/' . $partial . '.php';
    }

    protected function validate_options($validation_rules)
    {
        $regex = array(
            'color' => '^(([a-z]+)|(#[0-9a-f]{2,6}))?$',
            'numeric-comma' => '^(\d+(, ?\d+)*)?$',
            'css' => '^(([a-zA-Z-])+\ *\:[^;]+; *)*$',
        );
        $messages = array(
            'numeric-comma' => __('Incorrect format for field <strong>%s</strong>', 'combined-search'),
            'color' => __("Field <strong>%s</strong> should be a css color ('red' or '#abc123')", 'combined-search'),
            'css' => __("Field <strong>%s</strong> doesn't contain valid css", 'combined-search'),
        );
        $errors = array();
        foreach ($validation_rules as $field => $rule_name) {
            $rule = $regex[$rule_name];
            if (!preg_match("/$rule/", $_POST[$field])) {
                $errors[$field] = $messages[$rule_name];
            }
        }
        return $errors;
    }

}
