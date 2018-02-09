<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0
 *
 * @package    Combined_Search
 * @subpackage Combined_Search/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Combined_Search
 * @subpackage Combined_Search/public
 */
class Combined_Search_Search_Query
{

    protected $query_instance;

    protected $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0
     */
    public function __construct()
    {
        $this->options = Combined_Search_Options::get_instance();

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0
     */
    public function enqueue_styles()
    {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/combined-search-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0
     */
    public function enqueue_scripts()
    {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/combined-search-public.js', array('jquery'), $this->version, false);

    }

    public function highlight_search_terms($postcontent)
    {
        global $wpdb;
        $s = isset($this->query_instance->query_vars['s']) ? $this->query_instance->query_vars['s'] : '';
        // highlighting
        if (!is_admin() && is_search() && $s != '') {

            $highlight_terms_background = $this->options->get_option('highlight_terms_background');
            $search_terms = $this->get_search_terms();

            foreach ($search_terms as $term) {
                if (preg_match('/\>/', $term)) {
                    continue;
                } //don't try to highlight this one
                $term = preg_quote($term);

                $postcontent = preg_replace(
                    '"(?<!\<)(?<!\w)(\pL*' . $term . '\pL*)(?!\w|[^<>]*>)"iu'
                    , '<span class="combined-search-highlight" style="background-color:' . $highlight_terms_background . '">$1</span>'
                    , $postcontent
                );

            }
        }
        return $postcontent;
    }

    public function terms_join($join)
    {
        global $wpdb;

        if (!empty($this->query_instance->query_vars['s'])) {

            // if we're searching for categories
            if ($this->options->get_option('enable_search_category_meta')) {
                $on[] = "ttax.taxonomy = 'category'";
            }

            // if we're searching for tags
            if ($this->options->get_option('enable_search_tags')) {
                $on[] = "ttax.taxonomy = 'post_tag'";
            }
            // if we're searching custom taxonomies
            if ($this->options->get_option('enable_search_custom_taxonomies')) {
                $all_taxonomies = get_taxonomies();
                $filter_taxonomies = ['post_tag', 'category', 'nav_menu', 'link_category'];

                foreach ($all_taxonomies as $taxonomy) {
                    if (in_array($taxonomy, $filter_taxonomies)) {
                        continue;
                    }
                    $on[] = "ttax.taxonomy = '" . addslashes($taxonomy) . "'";
                }
            }
            // build our final string
            $on = ' ( ' . implode(' OR ', $on) . ' ) ';
            $join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( ";
            $join .= $on;
            $join .= " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
        }

        return $join;
    }


    public function comments_join($join)
    {
        global $wpdb;

        if (!empty($this->query_instance->query_vars['s'])) {
            $join .= " LEFT JOIN $wpdb->comments AS cmt ON ( cmt.comment_post_ID = $wpdb->posts.ID ) ";
        }
        return $join;
    }

    // @TODO Trying to think of a reason you'd want to do this
    public function search_draft_posts($where)
    {
        global $wpdb;
        if (!empty($this->query_instance->query_vars['s'])) {
            $where = str_replace('"', '\'', $where);
            $where = str_replace(" AND ($wpdb->posts.post_status = 'publish'", " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'draft'", $where);
            $where = str_replace(" AND (post_status = 'publish'", " AND (post_status = 'publish' OR post_status = 'draft'", $where);
        }

        return $where;
    }

    public function search_attachments($where)
    {
        global $wpdb;
        if (!empty($this->query_instance->query_vars['s'])) {
            $where = str_replace('"', '\'', $where);
            $where = str_replace(" AND ($wpdb->posts.post_status = 'publish'", " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_type = 'attachment'", $where);
            $where = str_replace("AND $wpdb->posts.post_type != 'attachment'", "", $where);
        }

        return $where;
    }

    public function search_metadata_join($join)
    {
        global $wpdb;

        if (!empty($this->query_instance->query_vars['s'])) {
            $join .= " LEFT JOIN $wpdb->postmeta AS m ON ($wpdb->posts.ID = m.post_id) ";
        }
        return $join;
    }

    public function exclude_categories_join($join)
    {
        global $wpdb;

        if (!empty($this->query_instance->query_vars['s'])) {
            $join .= " LEFT JOIN $wpdb->term_relationships AS crel ON ($wpdb->posts.ID = crel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ctax ON (ctax.taxonomy = 'category' AND crel.term_taxonomy_id = ctax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS cter ON (ctax.term_id = cter.term_id) ";
        }
        return $join;
    }

    public function search_authors_join($join)
    {
        global $wpdb;

        if (!empty($this->query_instance->query_vars['s'])) {
            $join .= " LEFT JOIN $wpdb->users AS u ON ($wpdb->posts.post_author = u.ID) ";
        }
        return $join;
    }


    // @TODO All of these can be protected functions
    public function search_where($where, $wp_query)
    {
        if (!$wp_query->is_search()) {
            return $where;
        }

        $this->query_instance = &$wp_query;
        global $wpdb;

        $terms = $this->get_search_terms();
        $searchQuery = $this->search_default($terms);

        //add filters based upon option settings
        if ($this->options->get_option('enable_search_tags')) {
            $searchQuery .= $this->build_search_tag($terms);
        }

        if ($this->options->get_option('enable_search_category_meta') || $this->options->get_option('enable_search_custom_taxonomies')) {
            $searchQuery .= $this->build_search_categories($terms);
        }

        if ($this->options->get_option('enable_search_custom_fields')) {
            $searchQuery .= $this->build_search_metadata($terms);
        }

        if ($this->options->get_option('enable_search_excerpts')) {
            $searchQuery .= $this->build_search_excerpt($terms);
        }

        if ($this->options->get_option('enable_search_comments')) {
            $searchQuery .= $this->build_search_comments($terms);
        }

        if ($this->options->get_option('enable_search_authors')) {
            $searchQuery .= $this->build_search_authors($terms);
        }

        if ($searchQuery != '') {
            // lets use _OUR_ query instead of WP's, as we have posts already included in our query as well(assuming it's not empty which we check for)
            $where = " AND ((" . $searchQuery . ")) ";
        }

        if ($this->options->get_option('exclude_posts_list') != '') {
            $where .= $this->build_exclude_posts();
        }
        if ($this->options->get_option('exclude_taxonomies_list') != '') {
            $where .= $this->build_exclude_categories();

        }

        return $where;
    }


    public function no_revisions($where)
    {
        global $wpdb;
        if (!empty($this->query_instance->query_vars['s'])) {
            $where = ' AND (' . substr($where, strpos($where, 'AND') + 3) . ') AND post_type != \'revision\'';
        }
        return $where;
    }


    public function distinct($query)
    {
        global $wpdb;
        if (!empty($this->query_instance->query_vars['s'])) {
            if (strstr($query, 'DISTINCT')) {
            } else {
                $query = str_replace('SELECT', 'SELECT DISTINCT', $query);
            }
        }
        return $query;
    }


    public function no_future($where)
    {
        global $wpdb;
        if (!empty($this->query_instance->query_vars['s'])) {
            $where = 'AND (' . substr($where, strpos($where, 'AND') + 3) . ') AND post_status != \'future\'';
        }
        return $where;
    }


    // Start adding protected functions here
    protected function get_search_terms()
    {
        global $wpdb;
        $s = isset($this->query_instance->query_vars['s']) ? $this->query_instance->query_vars['s'] : '';
        $sentence = isset($this->query_instance->query_vars['sentence']) ? $this->query_instance->query_vars['sentence'] : false;
        $search_terms = [];

        if (!empty($s)) {
            // added slashes screw with quote grouping when done early, so done later
            $s = stripslashes($s);
            if ($sentence) {
                $search_terms = [$s];
            } else {
                preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches);
                $search_terms = array_filter(array_map(create_function('$a', 'return trim($a, "\\"\'\\n\\r ");'), $matches[0]));
            }
        }

        return $search_terms;
    }


    protected function search_default($terms)
    {
        global $wpdb;
        $not_exact = empty($this->query_instance->query_vars['exact']);
        $search_sql_query = '';
        $seperator = '';

        // if it's not a sentance add other terms
        $search_sql_query .= '(';

        foreach ($terms as $term) {
            $search_sql_query .= $seperator;

            $esc_term = $wpdb->prepare("%s", $not_exact ? "%" . $term . "%" : $term);

            $like_title = "($wpdb->posts.post_title LIKE $esc_term)";
            $like_post = "($wpdb->posts.post_content LIKE $esc_term)";

            $search_sql_query .= "($like_title OR $like_post)";

            $seperator = ' AND ';
        }

        $search_sql_query .= ')';
        return $search_sql_query;
    }


    protected function build_search_tag($terms)
    {
        global $wpdb;
        $vars = $this->query_instance->query_vars;

        $s = $vars['s'];
        $exact = isset($vars['exact']) ? $vars['exact'] : '';
        $search = '';

        if (!empty($terms)) {
            // Building search query
            $searchand = '';
            foreach ($terms as $term) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $search .= "{$searchand}(tter.name LIKE $term)";
                $searchand = ' OR ';
            }
            $sentence_term = $wpdb->prepare("%s", $s);
            if (count($terms) > 1 && $terms[0] != $sentence_term) {
                $search = "($search) OR (tter.name LIKE $sentence_term)";
            }
            if (!empty($search)) {
                $search = " OR ({$search}) ";
            }
        }

        return $search;
    }


    protected function build_search_categories($terms)
    {
        global $wpdb;
        $vars = $this->query_instance->query_vars;

        $s = $vars['s'];
        $exact = isset($vars['exact']) ? $vars['exact'] : '';
        $search = '';

        if (!empty($terms)) {
            // Building search query for categories slug.
            $searchand = '';
            $searchSlug = '';
            foreach ($terms as $term) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%" . sanitize_title_with_dashes($term) . "%");
                $searchSlug .= "{$searchand}(tter.slug LIKE $term)";
                $searchand = ' AND ';
            }

            $term = $wpdb->prepare("%s", $exact ? $term : "%" . sanitize_title_with_dashes($s) . "%");
            if (count($terms) > 1 && $terms[0] != $s) {
                $searchSlug = "($searchSlug) OR (tter.slug LIKE $term)";
            }
            if (!empty($searchSlug)) {
                $search = " OR ({$searchSlug}) ";
            }

            // Building search query for categories description.
            $searchand = '';
            $searchDesc = '';
            foreach ($terms as $term) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $searchDesc .= "{$searchand}(ttax.description LIKE $term)";
                $searchand = ' AND ';
            }
            $sentence_term = $wpdb->prepare("%s", $s);
            if (count($terms) > 1 && $terms[0] != $sentence_term) {
                $searchDesc = "($searchDesc) OR (ttax.description LIKE $sentence_term)";
            }
            if (!empty($searchDesc)) {
                $search = $search . " OR ({$searchDesc}) ";
            }
        }

        return $search;
    }


    protected function build_search_metadata($terms)
    {
        global $wpdb;
        $s = $this->query_instance->query_vars['s'];
        $exact = (isset($this->query_instance->query_vars['exact']) && $this->query_instance->query_vars['exact']) ? true : false;
        $search = '';

        if (!empty($terms)) {
            // Building search query
            $searchand = '';
            foreach ($terms as $term) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $search .= "{$searchand}(m.meta_value LIKE $term)";
                $searchand = ' AND ';
            }
            $sentence_term = $wpdb->prepare("%s", $s);
            if (count($terms) > 1 && $terms[0] != $sentence_term) {
                $search = "($search) OR (m.meta_value LIKE $sentence_term)";
            }

            if (!empty($search)) {
                $search = " OR ({$search}) ";
            }

        }

        return $search;
    }


    protected function build_search_excerpt($terms)
    {
        global $wpdb;
        $vars = $this->query_instance->query_vars;

        $s = $vars['s'];
        $exact = isset($vars['exact']) ? $vars['exact'] : '';
        $search = '';

        if (!empty($terms)) {
            // Building search query
            $n = ($exact) ? '' : '%';
            $searchand = '';
            foreach ($terms as $term) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $search .= "{$searchand}($wpdb->posts.post_excerpt LIKE $term)";
                $searchand = ' AND ';
            }
            $sentence_term = $wpdb->prepare("%s", $exact ? $s : "%$s%");
            if (count($terms) > 1 && $terms[0] != $sentence_term) {
                $search = "($search) OR ($wpdb->posts.post_excerpt LIKE $sentence_term)";
            }
            if (!empty($search)) {
                $search = " OR ({$search}) ";
            }
        }
        return $search;
    }


    protected function build_search_comments($terms)
    {
        global $wpdb;
        $vars = $this->query_instance->query_vars;

        $s = $vars['s'];
        $exact = isset($vars['exact']) ? $vars['exact'] : '';
        $search = '';
        if (!empty($terms)) {
            // Building search query on comments content
            $searchand = '';
            $searchContent = '';
            foreach ($terms as $term) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $searchContent .= "{$searchand}(cmt.comment_content LIKE $term)";
                $searchand = ' AND ';
            }
            $sentence_term = $wpdb->prepare("%s", $s);
            if (count($terms) > 1 && $terms[0] != $sentence_term) {
                $searchContent = "($searchContent) OR (cmt.comment_content LIKE $sentence_term)";
            }
            $search = $searchContent;
            // Building search query on comments author
            if ($this->options->get_option('enable_search_comments_authors')) {
                $searchand = '';
                $comment_author = '';
                foreach ($terms as $term) {
                    $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                    $comment_author .= "{$searchand}(cmt.comment_author LIKE $term)";
                    $searchand = ' AND ';
                }
                $sentence_term = $wpdb->prepare("%s", $s);
                if (count($terms) > 1 && $terms[0] != $sentence_term) {
                    $comment_author = "($comment_author) OR (cmt.comment_author LIKE $sentence_term)";
                }
                $search = "($search) OR ($comment_author)";
            }
            if ($this->options->get_option('enable_search_comments_approved_only')) {
                $comment_approved = "AND cmt.comment_approved =  '1'";
                $search = "($search) $comment_approved";
            }
            if (!empty($search)) {
                $search = " OR ({$search}) ";
            }
        }

        return $search;
    }


    protected function build_search_authors($terms)
    {
        global $wpdb;
        $s = $this->query_instance->query_vars['s'];
        $terms = $this->get_terms();
        $exact = (isset($this->query_instance->query_vars['exact']) && $this->query_instance->query_vars['exact']) ? true : false;
        $search = '';
        $searchand = '';

        if (!empty($terms)) {
            // Building search query
            foreach ($terms as $term) {
                $term = $wpdb->prepare("%s", $exact ? $term : "%$term%");
                $search .= "{$searchand}(u.display_name LIKE $term)";
                $searchand = ' OR ';
            }
            $sentence_term = $wpdb->prepare("%s", $s);
            if (count($terms) > 1 && $terms[0] != $sentence_term) {
                $search .= " OR (u.display_name LIKE $sentence_term)";
            }

            if (!empty($search)) {
                $search = " OR ({$search}) ";
            }

        }

        return $search;
    }


    protected function build_exclude_posts()
    {
        global $wpdb;
        $excludeQuery = '';
        if (!empty($this->query_instance->query_vars['s'])) {
            $excludedPostList = trim($this->options->get_option('exclude_posts_list'));
            if ($excludedPostList != '') {
                $excluded_post_list = [];
                foreach (explode(',', $excludedPostList) as $post_id) {
                    $excluded_post_list[] = (int)$post_id;
                }
                $excl_list = implode(',', $excluded_post_list);
                $excludeQuery = ' AND (' . $wpdb->posts . '.ID NOT IN ( ' . $excl_list . ' ))';
            }
        }
        return $excludeQuery;
    }


    protected function build_exclude_categories()
    {
        global $wpdb;
        $excludeQuery = '';
        if (!empty($this->query_instance->query_vars['s'])) {
            $excludedCatList = trim($this->options->get_option('exclude_taxonomies_list'));
            if ($excludedCatList != '') {
                $excluded_cat_list = [];
                foreach (explode(',', $excludedCatList) as $cat_id) {
                    $excluded_cat_list[] = (int)$cat_id;
                }
                $excl_list = implode(',', $excluded_cat_list);
                $excludeQuery = " AND ( ctax.term_id NOT IN ( " . $excl_list . " ) OR (wp_posts.post_type IN ( 'page' )))";
            }
        }
        return $excludeQuery;
    }
}
