<div class="wrap">

    <h2><?php _e('Combined Search Settings', 'combined-search'); ?> </h2>

    <form id="combined-search-settings" method="post">
        <?php wp_nonce_field('combined-search-nonce'); ?>

        <table class="widefat cosr-widefat cosr-checkboxes">
            <thead>
            <tr class="title">
                <th scope="col" colspan="2" class="manage-column cosr-option-title"><?php _e('Enable/Disable Search Regions', 'combined-search'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_tags" value="yes" <?php checked($this->options->get_option('enable_search_tags')); ?>></td>
                <th scope="row"><label for="search_tags"><?php _e('Search tag names', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_custom_taxonomies" value="yes" <?php checked($this->options->get_option('enable_search_custom_taxonomies')); ?>></td>
                <th scope="row"><label for="search_tags"><?php _e('Search custom taxonomies', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_category_meta" value="yes" <?php checked($this->options->get_option('enable_search_category_meta')); ?>></td>
                <th scope="row"><label for="search_categories"><?php _e('Search category names and descriptions', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_comments" id="search_comments" value="yes" <?php checked($this->options->get_option('enable_search_comments')); ?> ></td>
                <th scope="row"><label for="search_comments"><?php _e('Search comments', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle" class="sub-option">
                <td><input type="checkbox" name="enable_search_comments_authors" value="yes" <?php checked($this->options->get_option('enable_search_comments_authors')); ?> ></td>
                <th scope="row"><label for="search_cmt_authors"><?php _e('Search comment authors', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle" class="sub-option">
                <td><input type="checkbox" name="enable_search_comments_approved_only" value="yes" <?php checked($this->options->get_option('enable_search_comments_approved_only')); ?> ></td>
                <th scope="row" class="se-suboption"><label for="appvd_comments"><?php _e('Search approved comments only', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_excerpts" value="yes" <?php checked($this->options->get_option('enable_search_excerpts')); ?> ></td>
                <th scope="row"><label for="search_excerpt"><?php _e('Search excerpts', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_drafts" value="yes" <?php checked($this->options->get_option('enable_search_drafts')); ?> ></td>
                <th scope="row"><label for="search_drafts"><?php _e('Search drafts', 'combined-search'); ?></label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_attachments" value="yes" <?php checked($this->options->get_option('enable_search_attachments')); ?> >   </td>
                <th><label for="search_attachments"><?php _e('Search attachments', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_custom_fields" value="yes" <?php checked($this->options->get_option('enable_search_custom_fields')); ?> ></td>
                <th scope="row"><label for="search_metadata"><?php _e('Search custom fields', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="middle">
                <td><input type="checkbox" name="enable_search_authors" value="yes" <?php checked($this->options->get_option('enable_search_authors')); ?> ></td>
                <th scope="row"><label for="search_authors"><?php _e('Search authors', 'combined-search'); ?>:</label></th>
            </tr>
            </tbody>
        </table>

        <table class="widefat cosr-widefat">
            <thead>
            <tr class="title">
                <th scope="col" colspan="2" class="manage-column cosr-option-title"><?php _e('Search Term Highlight', 'combined-search'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr valign="middle">
                <td><input type="checkbox" name="highlight_terms" value="yes" <?php checked($this->options->get_option('highlight_terms')); ?> ></td>
                <th scope="row"><label for="highlight_terms"><?php _e('Highlight search terms', 'combined-search'); ?>:</label></th>
            </tr>
            <tr valign="top">
                <td></td>
                <th scope="row">
                    <label for="highlight_terms_background"><?php _e('Highlight background color', 'combined-search'); ?>:</label><br>
                    <input type="text" name="highlight_terms_background" class="regular-text" value="<?php echo $this->options->get_option('highlight_terms_background'); ?>"><br>
                    <p class="description"><?php _e('Examples: \'#FFF984\' or \'red\'', 'combined-search'); ?></p>
                </th>
            </tr>
            </tbody>
        </table>

        <table class="widefat cosr-widefat">
            <thead>
            <tr class="title">
                <th scope="col" colspan="2" class="manage-column cosr-option-title"><?php _e('Exclude from Search', 'combined-search'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr valign="top">
                <td><input type="checkbox" name="" value="" style="visibility: hidden;"></td>
                <th scope="row">
                    <label for="exclude_posts_list"><?php _e('Exclude by Post ID', 'combined-search'); ?>:</label><br>
                    <input type="text" name="exclude_posts_list" class="regular-text" value="<?php echo $this->options->get_option('exclude_posts_list'); ?>">
                    <p class="description"><?php _e('Comma separated list of post IDs to be excluded from searches', 'combined-search'); ?></p>
                </th>
            </tr>
            <tr valign="top">
                <td></td>
                <th scope="row">
                    <label for="exclude_taxonomies_list"><?php _e('Exclude Taxonomies', 'combined-search'); ?>:</label><br>
                    <input type="text" name="exclude_taxonomies_list" class="regular-text" value="<?php echo $this->options->get_option('exclude_taxonomies_list'); ?>">
                    <p class="description"><?php _e('Comma separated list of category, tag or custom taxonomy IDs to be excluded', 'combined-search'); ?></p>
                </th>
            </tr>
            </tbody>
        </table>

        <div class="cosr-submit">
            <input type="hidden" name="action" value="save">
            <input type="submit" class="button button-primary" value="<?php _e('Save Changes', 'combined-search') ?>">
        </div>
    </form>


</div>
