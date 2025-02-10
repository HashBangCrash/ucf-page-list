<?php

/*
Plugin Name: UCF Page List
Description: Displays a list of immediate child pages for a specified parent page.
Version: 1.1
Author: Stephen Schrauger
Plugin URI: https://github.com/HashBangCrash/ucf-page-list
Github Plugin URI: HashBangCrash/ucf-page-list
*/

namespace ucf_page_list;

include_once plugin_dir_path( __FILE__ ) . 'includes/acf-pro-fields.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/block.php';

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Used by acf enqueue assets, to load the js and css conditionally (only when block is on page)
 */
function enqueue_js_css() {
    add_css();
}

function add_css() {
    if ( file_exists( plugin_dir_path( __FILE__ ) . '/includes/plugin.css' ) ) {
        wp_enqueue_style(
            'child-pages-menu-style',
            plugin_dir_url( __FILE__ ) . '/includes/plugin.css',
            false,
            filemtime( plugin_dir_path( __FILE__ ) . '/includes/plugin.css' ),
            false
        );
    }
}
