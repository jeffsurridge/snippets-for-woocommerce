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