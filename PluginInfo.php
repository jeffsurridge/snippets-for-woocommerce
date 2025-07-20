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
    if (get_option('sfw_custom_coupon_message_toggle', 'off') === 'on') {
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
    // Minimum order amount enforcement
    if (get_option('sfw_minimum_order_toggle', 'off') === 'on') {
        $minimum = get_option('sfw_minimum_order_amount', '');
        if (is_numeric($minimum) && $minimum > 0) {
            add_action('woocommerce_checkout_process', function() use ($minimum) {
                if (WC()->cart && WC()->cart->subtotal < $minimum) {
                    wc_add_notice(
                        sprintf('You must have a minimum order amount of %s to place your order. Your current order total is %s.', wc_price($minimum), wc_price(WC()->cart->subtotal)),
                        'error'
                    );
                }
            });
            add_action('woocommerce_before_cart', function() use ($minimum) {
                if (WC()->cart && WC()->cart->subtotal < $minimum) {
                    wc_print_notice(
                        sprintf('You must have a minimum order amount of %s to place your order. Your current order total is %s.', wc_price($minimum), wc_price(WC()->cart->subtotal)),
                        'error'
                    );
                }
            });
        }
    }
});
