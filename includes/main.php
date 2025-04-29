<?php
defined('ABSPATH') || die();
class Nader_Center{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('nader-center-style', NADER_CENTER_URL . 'assets/style.css', array(), NADER_CENTER_VERSION);
        wp_enqueue_style('dashicons');
    }

    public function add_admin_menus()
    {
        add_menu_page(
            'پیشخوان قالب نادر',
            'پیشخوان قالب نادر',
            'manage_options',
            'nader-center',
            array('Nader_Center_Help', 'display'),
            'dashicons-admin-generic',
            2
        );

        add_submenu_page(
            'nader-center',
            'درون ریزی',
            'درون ریزی',
            'manage_options',
            'nader-center-import',
            array('Nader_Center_Import', 'display')
        );

        add_submenu_page(
            'nader-center',
            'اطلاعات سیستم',
            'اطلاعات سیستم',
            'manage_options',
            'nader-center-system-info',
            array('Nader_Center_System_Info', 'display')
        );
    }
}
