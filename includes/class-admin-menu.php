<?php

class AdminMainMenu{

    public function __construct(){
        add_action('admin_menu',[$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function register_admin_menu(){

        add_menu_page(
            'My Plugin',
            'Snippets For Woocommerce',
            'manage_options',
            'sfw-dashboard',
            [$this, 'dashboard_page']
        );

        add_submenu_page(
            'sfw-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'sfw-settings',
            [$this, 'settings_page']
        );
    }

    public function enqueue_admin_scripts($hook) {
        // Only enqueue on our settings page
        if ($hook !== 'snippets-for-woocommerce_page_sfw-settings') {
            return;
        }
        wp_enqueue_script(
            'sfw-admin-product-search',
            plugin_dir_url(__FILE__) . '../JS/sfw-admin-product-search.js',
            array('jquery'),
            '1.0',
            true
        );
        wp_localize_script('sfw-admin-product-search', 'sfwProductSearch', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('sfw_product_search_nonce')
        ));
    }

    public function dashboard_page(){
        echo "<h1> Dashboard </h1>";
    }

    public function settings_page(){
        // Handle form submission
        $coupon_toggle = get_option('sfw_custom_coupon_message_toggle', 'off');
        $current_message = get_option('sfw_coupon_message', '');
        $checkout_toggle = get_option('sfw_checkout_coupon_toggle', 'off');
        $checkout_message = get_option('sfw_checkout_coupon_message', '');
        $error = '';
        $minimum_toggle = get_option('sfw_minimum_order_toggle', 'off');
        $minimum_amount = get_option('sfw_minimum_order_amount', '');
        $hide_products_toggle = get_option('sfw_hide_products_from_guests_toggle', 'off');
        $hidden_product_ids = get_option('sfw_hidden_product_ids', '');
        if (isset($_POST['sfw_toggle_submit'])) {
            check_admin_referer('sfw_toggle_save', 'sfw_toggle_nonce');
            $toggle_value = isset($_POST['sfw_custom_coupon_message_toggle']) ? 'on' : 'off';
            $message_value = isset($_POST['sfw_coupon_message']) ? sanitize_text_field($_POST['sfw_coupon_message']) : '';
            $checkout_toggle_value = isset($_POST['sfw_checkout_coupon_toggle']) ? 'on' : 'off';
            $checkout_message_value = isset($_POST['sfw_checkout_coupon_message']) ? sanitize_text_field($_POST['sfw_checkout_coupon_message']) : '';
            $minimum_toggle_value = isset($_POST['sfw_minimum_order_toggle']) ? 'on' : 'off';
            $minimum_amount_value = isset($_POST['sfw_minimum_order_amount']) ? sanitize_text_field($_POST['sfw_minimum_order_amount']) : '';
            $hide_products_toggle_value = isset($_POST['sfw_hide_products_from_guests_toggle']) ? 'on' : 'off';
            $hidden_product_ids_value = isset($_POST['sfw_hidden_product_ids']) ? sanitize_text_field($_POST['sfw_hidden_product_ids']) : '';
            $has_error = false;
            if ($toggle_value === 'on' && empty($message_value)) {
                $error .= '<div class="error"><p>Please enter a coupon message for the first toggle when enabled.</p></div>';
                $has_error = true;
            }
            if ($checkout_toggle_value === 'on' && empty($checkout_message_value)) {
                $error .= '<div class="error"><p>Please enter a checkout coupon message for the second toggle when enabled.</p></div>';
                $has_error = true;
            }
            if ($minimum_toggle_value === 'on' && (empty($minimum_amount_value) || !is_numeric($minimum_amount_value) || $minimum_amount_value <= 0)) {
                $error .= '<div class="error"><p>Please enter a valid minimum order amount for the third toggle when enabled.</p></div>';
                $has_error = true;
            }
            if (!$has_error) {
                update_option('sfw_custom_coupon_message_toggle', $toggle_value);
                if ($toggle_value === 'on') {
                    update_option('sfw_coupon_message', $message_value);
                } else {
                    update_option('sfw_coupon_message', '');
                }
                update_option('sfw_checkout_coupon_toggle', $checkout_toggle_value);
                if ($checkout_toggle_value === 'on') {
                    update_option('sfw_checkout_coupon_message', $checkout_message_value);
                } else {
                    update_option('sfw_checkout_coupon_message', '');
                }
                update_option('sfw_minimum_order_toggle', $minimum_toggle_value);
                if ($minimum_toggle_value === 'on') {
                    update_option('sfw_minimum_order_amount', $minimum_amount_value);
                } else {
                    update_option('sfw_minimum_order_amount', '');
                }
                update_option('sfw_hide_products_from_guests_toggle', $hide_products_toggle_value);
                if ($hide_products_toggle_value === 'on') {
                    update_option('sfw_hidden_product_ids', $hidden_product_ids_value);
                } else {
                    update_option('sfw_hidden_product_ids', '');
                }
                echo '<div class="updated"><p>Settings saved.</p></div>';
                $coupon_toggle = $toggle_value;
                $current_message = $message_value;
                $checkout_toggle = $checkout_toggle_value;
                $checkout_message = $checkout_message_value;
                $minimum_toggle = $minimum_toggle_value;
                $minimum_amount = $minimum_amount_value;
                $hide_products_toggle = $hide_products_toggle_value;
                $hidden_product_ids = $hidden_product_ids_value;
            }
        }
        ?>
        <div class="wrap">
            <h1>Settings page</h1>
            <?php if ($error) echo $error; ?>
            <form method="post">
                <?php wp_nonce_field('sfw_toggle_save', 'sfw_toggle_nonce'); ?>
                <!-- Coupon Message Toggle -->
                <label class="sfw-switch">
                    <input type="checkbox" name="sfw_custom_coupon_message_toggle" id="sfw_custom_coupon_message_toggle" <?php checked($coupon_toggle, 'on'); ?> onchange="document.getElementById('sfw_coupon_message_wrap').style.display = this.checked ? 'block' : 'none';" />
                    <span class="sfw-slider"></span>
                </label>
                <span style="margin-left:10px;vertical-align:middle;">Enable Custom Coupon Message</span>
                <br><br>
                <div id="sfw_coupon_message_wrap" style="margin-bottom:15px;<?php echo ($coupon_toggle === 'on') ? '' : 'display:none;'; ?>">
                    <label for="sfw_coupon_message"><strong>Custom Coupon Message:</strong></label><br>
                    <input type="text" name="sfw_coupon_message" id="sfw_coupon_message" value="<?php echo esc_attr($current_message); ?>" style="width:350px;" />
                </div>
                <hr style="margin:30px 0;">
                <!-- Checkout Coupon Message Toggle -->
                <label class="sfw-switch">
                    <input type="checkbox" name="sfw_checkout_coupon_toggle" id="sfw_checkout_coupon_toggle" <?php checked($checkout_toggle, 'on'); ?> onchange="document.getElementById('sfw_checkout_coupon_message_wrap').style.display = this.checked ? 'block' : 'none';" />
                    <span class="sfw-slider"></span>
                </label>
                <span style="margin-left:10px;vertical-align:middle;">Enable Custom Checkout Coupon Message</span>
                <br><br>
                <div id="sfw_checkout_coupon_message_wrap" style="margin-bottom:15px;<?php echo ($checkout_toggle === 'on') ? '' : 'display:none;'; ?>">
                    <label for="sfw_checkout_coupon_message"><strong>Custom Checkout Coupon Message:</strong></label><br>
                    <input type="text" name="sfw_checkout_coupon_message" id="sfw_checkout_coupon_message" value="<?php echo esc_attr($checkout_message); ?>" style="width:350px;" />
                </div>
                <hr style="margin:30px 0;">
                <!-- Minimum Order Amount Toggle -->
                <label class="sfw-switch">
                    <input type="checkbox" name="sfw_minimum_order_toggle" id="sfw_minimum_order_toggle" <?php checked($minimum_toggle, 'on'); ?> onchange="document.getElementById('sfw_minimum_order_wrap').style.display = this.checked ? 'block' : 'none';" />
                    <span class="sfw-slider"></span>
                </label>
                <span style="margin-left:10px;vertical-align:middle;">Enable Minimum Order Amount</span>
                <br><br>
                <div id="sfw_minimum_order_wrap" style="margin-bottom:15px;<?php echo ($minimum_toggle === 'on') ? '' : 'display:none;'; ?>">
                    <label for="sfw_minimum_order_amount"><strong>Minimum Order Amount:</strong></label><br>
                    <input type="number" min="1" name="sfw_minimum_order_amount" id="sfw_minimum_order_amount" value="<?php echo esc_attr($minimum_amount); ?>" style="width:150px;" />
                </div>
                <hr style="margin:30px 0;">
                <!-- Hide Products From Guests Toggle -->
                <label class="sfw-switch">
                    <input type="checkbox" name="sfw_hide_products_from_guests_toggle" id="sfw_hide_products_from_guests_toggle" <?php checked($hide_products_toggle, 'on'); ?> onchange="document.getElementById('sfw_hidden_product_ids_wrap').style.display = this.checked ? 'block' : 'none';" />
                    <span class="sfw-slider"></span>
                </label>
                <span style="margin-left:10px;vertical-align:middle;">Hide Selected Products From Guests</span>
                <br><br>
                <div id="sfw_hidden_product_ids_wrap" style="margin-bottom:15px;<?php echo ($hide_products_toggle === 'on') ? '' : 'display:none;'; ?>">
                    <label for="sfw_product_search_input"><strong>Products to Hide:</strong></label><br>
                    <input type="text" id="sfw_product_search_input" placeholder="Search products..." autocomplete="off" style="width:350px;" />
                    <div id="sfw_product_search_results" style="border:1px solid #ccc;max-height:150px;overflow-y:auto;margin-bottom:10px;"></div>
                    <div id="sfw_selected_products" style="margin-bottom:10px;"></div>
                    <input type="hidden" name="sfw_hidden_product_ids" id="sfw_hidden_product_ids" value="<?php echo esc_attr($hidden_product_ids); ?>" />
                    <p class="description">Search and select products to hide from guests.</p>
                </div>
                <input type="submit" name="sfw_toggle_submit" class="button button-primary" value="Save Changes" />
            </form>
            <style>
                .sfw-switch {
                  position: relative;
                  display: inline-block;
                  width: 50px;
                  height: 24px;
                }
                .sfw-switch input {display:none;}
                .sfw-slider {
                  position: absolute;
                  cursor: pointer;
                  top: 0; left: 0; right: 0; bottom: 0;
                  background-color: #ccc;
                  transition: .4s;
                  border-radius: 24px;
                }
                .sfw-slider:before {
                  position: absolute;
                  content: "";
                  height: 18px;
                  width: 18px;
                  left: 3px;
                  bottom: 3px;
                  background-color: white;
                  transition: .4s;
                  border-radius: 50%;
                }
                .sfw-switch input:checked + .sfw-slider {
                  background-color: #2271b1;
                }
                .sfw-switch input:checked + .sfw-slider:before {
                  transform: translateX(26px);
                }
                .sfw-selected-product {
                    display: inline-block;
                    background: #f1f1f1;
                    border: 1px solid #ccc;
                    border-radius: 3px;
                    padding: 2px 8px;
                    margin: 2px 4px 2px 0;
                    font-size: 13px;
                    position: relative;
                }
                .sfw-selected-product .sfw-remove {
                    color: #a00;
                    margin-left: 8px;
                    cursor: pointer;
                    font-weight: bold;
                }
                .sfw-search-result {
                    padding: 4px 8px;
                    cursor: pointer;
                }
                .sfw-search-result:hover {
                    background: #e5f3ff;
                }
            </style>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toggle = document.getElementById('sfw_custom_coupon_message_toggle');
                var msgWrap = document.getElementById('sfw_coupon_message_wrap');
                msgWrap.style.display = toggle.checked ? 'block' : 'none';
                var checkoutToggle = document.getElementById('sfw_checkout_coupon_toggle');
                var checkoutMsgWrap = document.getElementById('sfw_checkout_coupon_message_wrap');
                checkoutMsgWrap.style.display = checkoutToggle.checked ? 'block' : 'none';
                var minimumToggle = document.getElementById('sfw_minimum_order_toggle');
                var minimumWrap = document.getElementById('sfw_minimum_order_wrap');
                minimumWrap.style.display = minimumToggle.checked ? 'block' : 'none';
                var hideProductsToggle = document.getElementById('sfw_hide_products_from_guests_toggle');
                var hiddenProductsWrap = document.getElementById('sfw_hidden_product_ids_wrap');
                if (hideProductsToggle && hiddenProductsWrap) {
                    hiddenProductsWrap.style.display = hideProductsToggle.checked ? 'block' : 'none';
                }
            });
            </script>
        </div>
        <?php
    }
}