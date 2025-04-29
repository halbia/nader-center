<?php
defined('ABSPATH') || die();

class Nader_Center_System_Info {
    public static function display() {
        global $wpdb;
        
        // اطلاعات سرور
        $server_info = [
            'سیستم عامل سرور' => PHP_OS,
            'نسخه PHP' => phpversion(),
            'نسخه MySQL' => $wpdb->db_version(),
            'سرور وب' => $_SERVER['SERVER_SOFTWARE'],
            'حداکثر حجم آپلود' => ini_get('upload_max_filesize'),
            'حداکثر زمان اجرا' => ini_get('max_execution_time') . ' ثانیه',
            'حافظه مصرفی PHP' => ini_get('memory_limit'),
            'پروتکل سرور' => is_ssl() ? 'HTTPS' : 'HTTP',
            'آیپی سرور' => $_SERVER['SERVER_ADDR'],
            'آیپی کاربر' => $_SERVER['REMOTE_ADDR']
        ];
        
        // اطلاعات وردپرس
        $wp_info = [
            'نسخه وردپرس' => get_bloginfo('version'),
            'آدرس سایت' => get_site_url(),
            'آدرس خانه' => get_home_url(),
            'پیشوند جداول' => $wpdb->prefix,
            'زبان سایت' => get_locale(),
            'تعداد کاربران' => count_users()['total_users'],
            'پوسته فعال' => wp_get_theme()->get('Name'),
            'حالت دیباگ' => defined('WP_DEBUG') && WP_DEBUG ? 'فعال' : 'غیرفعال',
            'حالت نگهداری' => get_option('maintenance_mode') ? 'فعال' : 'غیرفعال',
            'تعداد پست‌ها' => wp_count_posts()->publish
        ];
        
        // اطلاعات پلاگین
        $plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        
        // نمایش اطلاعات
        echo '<div class="wrap">';
        echo '<h1>اطلاعات سیستم مرکز نادر</h1>';
        
        // تب‌ها
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="#server-info" class="nav-tab nav-tab-active">اطلاعات سرور</a>';
        echo '<a href="#wp-info" class="nav-tab">اطلاعات وردپرس</a>';
        echo '<a href="#plugins-info" class="nav-tab">پلاگین‌ها</a>';
        echo '</h2>';
        
        // اطلاعات سرور
        echo '<div id="server-info" class="tab-content active">';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>عنوان</th><th>مقدار</th></tr></thead>';
        echo '<tbody>';
        foreach ($server_info as $title => $value) {
            echo '<tr><td>' . $title . '</td><td>' . $value . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
        
        // اطلاعات وردپرس
        echo '<div id="wp-info" class="tab-content" style="display:none;">';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>عنوان</th><th>مقدار</th></tr></thead>';
        echo '<tbody>';
        foreach ($wp_info as $title => $value) {
            echo '<tr><td>' . $title . '</td><td>' . $value . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
        
        // اطلاعات پلاگین‌ها
        echo '<div id="plugins-info" class="tab-content" style="display:none;">';
        echo '<h3>پلاگین‌های نصب شده (' . count($plugins) . ')</h3>';
        echo '<h4>پلاگین‌های فعال (' . count($active_plugins) . ')</h4>';
        
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>نام پلاگین</th><th>نسخه</th><th>وضعیت</th></tr></thead>';
        echo '<tbody>';
        foreach ($plugins as $path => $plugin) {
            $status = in_array($path, $active_plugins) ? '<span style="color:green;">فعال</span>' : '<span style="color:red;">غیرفعال</span>';
            echo '<tr>';
            echo '<td>' . $plugin['Name'] . '<br><small>' . $plugin['Description'] . '</small></td>';
            echo '<td>' . $plugin['Version'] . '</td>';
            echo '<td>' . $status . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
        
        // اسکریپت تب‌ها
        echo '<script>
        jQuery(document).ready(function($) {
            $(".nav-tab-wrapper a").click(function(e) {
                e.preventDefault();
                $(".nav-tab-wrapper a").removeClass("nav-tab-active");
                $(this).addClass("nav-tab-active");
                $(".tab-content").hide();
                $($(this).attr("href")).show();
            });
        });
        </script>';

        echo '</div>';
    }
}