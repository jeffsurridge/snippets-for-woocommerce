<?php

class AdminMainMenu{

    public function __construct(){
        add_action('admin_menu',[$this, 'register_admin_menu']);
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

    public function dashboard_page(){
        echo "<h1> Dashboard </h1>";
    }

    public function settings_page(){
        echo "<h1> Settings page <h1>";
    }
}