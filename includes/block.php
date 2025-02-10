<?php

namespace ucf_page_list\block;

/**
 * Output a menu of all child pages based on the post id specified
 * @param $block
 * @param $content
 * @param $is_preview
 * @param $post_id
 * @return void
 */
function child_pages_menu_block_render_callback($block, $content = '', $is_preview = false, $post_id = 0) {
    // Get the toggle value
    $use_manual_id = get_field('use_manual_id');

    // Get custom “className” from the $block array.
    $class_name = isset($block['className']) ? $block['className'] : '';

    $default_class = 'child-pages-menu';
    if( $class_name ) {
        $class_name = $default_class . ' ' . $class_name;
    } else {
        $class_name = $default_class;
    }

    // Determine the parent page ID
    if ($use_manual_id) {
        // Use the manually entered Page ID
        $parent_page_id = get_field('manual_page_id');
    } else {
        // Get the selected parent page
        $parent_page = get_field('parent_page');
        // Get the ID from the URL or ID
        if (is_numeric($parent_page)) {
            $parent_page_id = $parent_page;
        } else {
            $parent_page_id = url_to_postid($parent_page);
        }
    }

    if (empty($parent_page_id)) {
        // Use the current page if none selected
        $parent_page_id = get_the_ID();
    }

    // Validate the parent page ID
    if (empty($parent_page_id) || get_post_status($parent_page_id) != 'publish') {
        echo '<p>Invalid Parent Page ID.</p>';
        return;
    }

    // Query for child pages
    $args = array(
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'post_parent'    => $parent_page_id,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );

    $child_query = new \WP_Query($args);

    // Output the list
    if ($child_query->have_posts()) {
        $menu_items_html = "";
        while ($child_query->have_posts()) {
            $child_query->the_post();
            $permalink = get_permalink();
            $title = get_the_title();
            $menu_items_html .= "
            <li>
                <a 
                href='${permalink}'
                class='child-page-item'
                >
                $title
                </a>
            </li>
            ";
        }

        // wrap list items in an unordered list
        $menu_html = "
        <ul class='${class_name}'>
            $menu_items_html
        </ul>
        ";

        echo $menu_html;
    } else {
        echo '<p>No child pages found.</p>';
    }

    wp_reset_postdata();
}
