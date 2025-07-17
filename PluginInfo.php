<?php
/*
Plugin Name: Snippets For Woocommerce
Plugin URI: https://jeffalvarez.dev
Description: Short description of what your plugin does.
Version: 1.0
Author: Jeff Surridge
Author URI: https://github.com/jeffsurridge/
License: GPL2
*/

require_once plugin_dir_path(__FILE__). 'includes/class-admin-menu.php';

new AdminMainMenu();

add_action('plugins_loaded', function() {
    if (get_option('sfw_hello_world_toggle', 'off') === 'on') {
        $msg = get_option('sfw_coupon_message', '');
        if (!empty($msg)) {
            add_filter('woocommerce_coupon_message', function($message, $msg_code, $that) use ($msg) {
                return $msg;
            }, 10, 3);
        }
    }
    if (get_option('sfw_checkout_coupon_toggle', 'off') === 'on') {
        $checkout_msg = get_option('sfw_checkout_coupon_message', '');
        if (!empty($checkout_msg)) {
            add_filter('woocommerce_checkout_coupon_message', function($message) use ($checkout_msg) {
                return $checkout_msg;
            }, 10, 1);
        }
    }
});
