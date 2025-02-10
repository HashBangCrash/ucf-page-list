<?php

namespace ucf_page_list\acf_pro_fields;

const acf_unique_identifier = "ucf_page_list_"; // prepend acf 'key' fields with this to prevent collisions


// create the acf fields for the editor
add_action( 'acf/init', __NAMESPACE__ . '\\create_fields' );
add_action('acf/init', __NAMESPACE__ .  '\\register_child_pages_menu_block');

// add a filter to only return pages that actually have children, in the acf editor dropdown field
add_filter( 'acf/fields/page_link/query/name=parent_page',  __NAMESPACE__ . '\\filter_pages_with_children', 10, 3);

function register_child_pages_menu_block() {
    // Check if function exists.
    if (function_exists('acf_register_block_type')) {
        acf_register_block_type(array(
            'name'              => 'ucf-page-list',
            'title'             => __('Child Page List'),
            'description'       => __('Displays a list of immediate child pages.'),
            'render_callback'   => 'ucf_page_list\\block\\child_pages_menu_block_render_callback',
            'enqueue_assets'    => 'ucf_page_list\\enqueue_js_css',
            'category'          => 'widgets',
            'icon'              => 'list-view',
            'keywords'          => array('child', 'pages', 'menu', 'list'),
            'supports'        => array(
                'align'            => false,
                'multiple'         => true,
                'customClassName'  => true,
            ),
        ));
    }
}

function create_fields()
{
    if (function_exists('acf_add_local_field_group')) {

        // toggle to switch between searchable pages and inputting the page id manually
        acf_add_local_field_group(array(
            'key' => acf_unique_identifier . "group_menu",
            'title' => 'Child Pages Menu Block',
            'fields' => array(
                // Toggle Field
                array(
                    'key' => acf_unique_identifier . 'field_use_manual_id',
                    'label' => 'Enter Page ID Manually',
                    'name' => 'use_manual_id',
                    'type' => 'true_false',
                    'instructions' => 'Toggle to enter a Page ID manually instead of selecting from the list.',
                    'default_value' => 0,
                    'ui' => 1,
                ),
                // Manual Page ID Field
                array(
                    'key' => acf_unique_identifier . 'field_manual_page_id',
                    'label' => 'Page ID',
                    'name' => 'manual_page_id',
                    'type' => 'number',
                    'instructions' => 'Enter the Page ID.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => acf_unique_identifier . 'field_use_manual_id',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                // Parent Page Selector Field
                array(
                    'key' => acf_unique_identifier . 'field_parent_page',
                    'label' => 'Parent Page',
                    'name' => 'parent_page',
                    'type' => 'page_link',
                    'post_type' => array('page'),
                    'allow_null' => true,
                    'allow_archives' => 0,
                    'multiple' => false,
                    'instructions' => 'Select the parent page. Only pages with child pages are listed.',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => acf_unique_identifier . 'field_use_manual_id',
                                'operator' => '==',
                                'value' => '0',
                            ),
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'block',
                        'operator' => '==',
                        'value' => 'acf/ucf-page-list',
                    ),
                ),
            ),
        ));

    }
}


/**
 * add a filter to only return pages that actually have children, in the acf editor dropdown field
 * @param $args
 * @param $field
 * @param $post_id
 * @return mixed
 */
function filter_pages_with_children($args, $field, $post_id) {
    global $wpdb;

    // Get IDs of pages that have children
    $pages_with_children = $wpdb->get_col("
        SELECT post_parent FROM $wpdb->posts
        WHERE post_type = 'page'
        AND post_status = 'publish'
        AND post_parent != 0
        GROUP BY post_parent
    ");

    // Ensure we have an array
    if (empty($pages_with_children)) {
        $pages_with_children = array(0); // No pages have children
    }

    // Modify the query arguments
    $args['post__in'] = array_unique($pages_with_children);
    $args['orderby'] = 'post_title';
    $args['order'] = 'ASC';

    return $args;
}
