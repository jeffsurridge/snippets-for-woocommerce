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
    // Hide products from guests logic
    if (get_option('sfw_hide_products_from_guests_toggle', 'off') === 'on') {
        $ids = get_option('sfw_hidden_product_ids', '');
        $hidden_product_ids = array_filter(array_map('intval', explode(',', $ids)));
        if (!empty($hidden_product_ids)) {
            // Hide products from shop, category, tag, search for guests
            add_action('pre_get_posts', function($query) use ($hidden_product_ids) {
                if (
                    !is_user_logged_in() &&
                    !is_admin() &&
                    $query->is_main_query() &&
                    (is_shop() || is_product_category() || is_product_tag() || is_search())
                ) {
                    $query->set('post__not_in', $hidden_product_ids);
                }
            });
            // Disable purchase button for guests
            add_filter('woocommerce_is_purchasable', function($purchasable, $product) use ($hidden_product_ids) {
                if (!is_user_logged_in() && in_array($product->get_id(), $hidden_product_ids)) {
                    return false;
                }
                return $purchasable;
            }, 10, 2);
        }
    }
});

add_action('wp_ajax_sfw_product_search', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }
    check_ajax_referer('sfw_product_search_nonce', 'nonce');
    $results = [];
    if (!empty($_POST['ids'])) {
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'])));
        if (!empty($ids)) {
            $query = new WP_Query([
                'post_type' => 'product',
                'post__in' => $ids,
                'posts_per_page' => count($ids),
            ]);
            foreach ($query->posts as $post) {
                $results[] = [
                    'id' => $post->ID,
                    'text' => get_the_title($post->ID)
                ];
            }
        }
    } elseif (!empty($_POST['term'])) {
        $term = sanitize_text_field($_POST['term']);
        $query = new WP_Query([
            'post_type' => 'product',
            's' => $term,
            'posts_per_page' => 10,
        ]);
        foreach ($query->posts as $post) {
            $results[] = [
                'id' => $post->ID,
                'text' => get_the_title($post->ID)
            ];
        }
    }
    wp_send_json_success($results);
});
