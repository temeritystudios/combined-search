<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0
 *
 * @package    Combined_Search
 * @subpackage Combined_Search/includes
 */

/**
 * Manages all plugin options
 *
 * @since      1.0
 * @package    Combined_Search
 * @subpackage Combined_Search/includes
 * @author     Your Name <email@example.com>
 */
class Combined_Search_Options
{

    /**
     * @var $_instance An instance of ones own instance
     */
    private static $_instance;

    private $_options;

    public function get_instance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
            self::$_instance->populate_options();
        }

        return self::$_instance;
    }

    /**
     * Populate self::$options with the configuration settings.
     *
     * @since 2.0
     */
    public function populate_options()
    {

        $defaults = $this->get_default_options();
        $this->_options = wp_parse_args(get_option(COMBINEDSEARCH_OPTIONS), $defaults);

    }


    /**
     * Save all current options to the database
     *
     * @since 2.4.0
     */
    private function _save_options()
    {
        update_option(COMBINEDSEARCH_OPTIONS, $this->_options);
    }


    /**
     * Access to our options array.
     *
     * @since 2.2.5
     * @param  $option string The name of the option we need to retrieve
     * @return         mixed  Returns the option
     */
    public function get_option($option)
    {
        if (!isset($this->_options[$option])) {
            trigger_error(sprintf(WPBITLY_ERROR, ' <code>' . $option . '</code>'), E_USER_ERROR);
        }

        return $this->_options[$option];
    }


    /**
     * Sets a single WPBitly::$_options value on the fly
     *
     * @since 2.2.5
     * @param $option string The name of the option we're setting
     * @param $value  mixed  The value, could be bool, string, array
     */
    public function set_option($option, $value)
    {
        if (!isset($this->_options[$option])) {
            trigger_error(sprintf(WPBITLY_ERROR, ' <code>' . $option . '</code>'), E_USER_ERROR);
        }

        $this->_options[$option] = $value;
        $this->_save_options();
    }


    public function set_options($new_options) {

        $current_options = $this->_options;

        $this->_options = array_merge($current_options, $new_options);
        $this->_save_options();
    }

    public function get_default_options($keys_only = false) {
        $default_options = apply_filters(
            'combined_search_default_options', array(
                'version' => COMBINEDSEARCH_VERSION,
                'enable_search_tags' => false,
                'enable_search_custom_taxonomies' => false,
                'enable_search_category_meta' => true,
                'enable_search_comments' => true,
                'enable_search_comments_authors' => false,
                'enable_search_comments_approved_only' => true,
                'enable_search_excerpts' => false,
                'enable_search_drafts' => false,
                'enable_search_attachments' => false,
                'enable_search_custom_fields' => false,
                'enable_search_authors' => false,

                'highlight_terms' => true,
                'highlight_terms_background' => '',

                'exclude_posts_list' => '',
                'exclude_taxonomies_list' => '',
            )
        );

        return $keys_only ? array_keys($default_options) : $default_options;
    }
}


function combined_search_options()
{
    return Combined_Search_Options::get_instance();
}

