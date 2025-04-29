<?php
/*
Plugin Name: مرکز نادر
Description: پلاگین مدیریتی نادر
Version: 1.0.0
Author: Ali Emadzadeh
Author URI: https://www.rtl-theme.com/author/halbia/
License: GPLv2 or later
*/

defined('ABSPATH') or die('دسترسی غیرمجاز!');

// تعریف ثابت‌های پلاگین
define('NADER_CENTER_PATH', plugin_dir_path(__FILE__));
define('NADER_CENTER_URL', plugin_dir_url(__FILE__));
define('NADER_CENTER_VERSION', '1.0.0');

// بارگذاری فایل‌های اصلی
require_once NADER_CENTER_PATH . 'includes/main.php';
require_once NADER_CENTER_PATH . 'includes/system-info.php';
require_once NADER_CENTER_PATH . 'includes/help.php';
require_once NADER_CENTER_PATH . 'includes/import.php';

// راه‌اندازی پلاگین
if (class_exists('Nader_Center')) {
    new Nader_Center();
}
