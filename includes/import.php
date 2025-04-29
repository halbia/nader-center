<?php
defined('ABSPATH') || die();

class Nader_Center_Import{

    private static $required_plugins = array(
        'elementor/elementor.php'            => 'المنتور',
        'advanced-custom-fields-pro/acf.php' => 'ACF Pro',
        'woocommerce/woocommerce.php'        => 'ووکامرس'
    );

    private static $import_steps = array(
        1 => 'انتخاب دمو',
        2 => 'بررسی پلاگین‌ها',
        3 => 'درون‌ریزی برگه‌ها',
        4 => 'درون‌ریزی محتوا',
        5 => 'تنظیم کیت المنتور',
        6 => 'پیکربندی قالب'
    );

    public static function init()
    {
        add_action('wp_ajax_nader_load_import_step', array(__CLASS__, 'ajax_load_import_step'));
        add_action('wp_ajax_nader_process_import_step', array(__CLASS__, 'ajax_process_import_step'));
    }

    public static function display()
    {
        wp_enqueue_script('nader-import', NADER_CENTER_URL . 'assets/import.js', array('jquery'), NADER_CENTER_VERSION, true);

        wp_localize_script('nader-import', 'naderImport', array(
            'nonce'   => wp_create_nonce('nader_import_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));

        $current_step = isset($_GET['import_step']) ? intval($_GET['import_step']) : 1;
        ?>
        <div class="wrap nader-import-container">
            <h1>درون‌ریزی محتوای نمونه</h1>

            <div class="nader-import-steps">
                <?php foreach (self::$import_steps as $step_num => $step_title): ?>
                    <div class="nader-step <?php echo $current_step >= $step_num ? 'active' : ''; ?>
                        <?php echo $current_step > $step_num ? 'completed' : ''; ?>"
                         data-step="<?php echo $step_num; ?>">
                        <span class="step-number"><?php echo $step_num; ?></span>
                        <span class="step-title"><?php echo esc_html($step_title); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="nader-import-content">
                <?php self::load_step_content($current_step); ?>
            </div>
        </div>
        <?php
    }

    private static function load_step_content($step)
    {
        $step = intval($step);
        if ($step < 1 || $step > count(self::$import_steps)) {
            $step = 1;
        }

        echo '<input type="hidden" id="nader-current-step" value="' . esc_attr($step) . '">';

        if ($errors = get_transient('nader_import_errors')) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html($errors) . '</p>';
            echo '</div>';
            delete_transient('nader_import_errors');
        }

        if ($success = get_transient('nader_import_success')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html($success) . '</p>';
            echo '</div>';
            delete_transient('nader_import_success');
        }

        switch ($step) {
            case 1:
                self::select_demo_step();
                break;
            case 2:
                self::check_plugins_step();
                break;
            case 3:
                self::import_pages_step();
                break;
            case 4:
                self::import_content_step();
                break;
            case 5:
                self::elementor_kit_step();
                break;
            case 6:
                self::theme_config_step();
                break;
            default:
                self::select_demo_step();
        }
    }

    private static function select_demo_step()
    {
        $demos = self::get_available_demos();
        ?>
        <div class="nader-import-box">
            <h3>انتخاب دمو مورد نظر</h3>
            <p>لطفاً یکی از دموهای زیر را انتخاب کنید:</p>

            <div class="nader-demo-grid">
                <?php foreach ($demos as $demo): ?>
                    <div class="nader-demo-box" data-demo-id="<?php echo esc_attr($demo['id']); ?>">
                        <div class="nader-demo-image">
                            <img src="<?php echo esc_url($demo['image']); ?>"
                                 alt="<?php echo esc_attr($demo['name']); ?>">
                        </div>
                        <div class="nader-demo-info">
                            <h4><?php echo esc_html($demo['name']); ?></h4>
                            <p><?php echo esc_html($demo['description']); ?></p>
                            <div class="nader-demo-actions">
                                <a href="<?php echo esc_url($demo['preview_url']); ?>"
                                   target="_blank"
                                   class="nader-import-button secondary">
                                    پیش‌نمایش
                                </a>
                                <button class="nader-import-button primary nader-select-demo">
                                    انتخاب دمو
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" id="nader-selected-demo" name="selected_demo" value="">
        </div>

        <div class="nader-import-actions">
            <span></span>
            <button class="nader-import-button primary" id="nader-continue-after-demo" disabled>
                ادامه به مرحله بعد
            </button>
        </div>
        <?php
    }

    private static function check_plugins_step()
    {
        $missing_plugins = array();
        $all_plugins = get_plugins();

        foreach (self::$required_plugins as $plugin_path => $plugin_name) {
            if (!isset($all_plugins[$plugin_path])) {
                $missing_plugins[] = $plugin_name;
            }
        }

        $all_active = empty($missing_plugins);
        ?>
        <div class="nader-import-box">
            <h3>بررسی پلاگین‌های ضروری</h3>
            <p>قبل از شروع درون‌ریزی، لطفاً مطمئن شوید پلاگین‌های زیر نصب و فعال هستند:</p>

            <?php foreach (self::$required_plugins as $plugin_path => $plugin_name): ?>
                <?php $is_active = is_plugin_active($plugin_path); ?>
                <div class="plugin-status <?php echo $is_active ? 'active' : 'inactive'; ?>">
                    <span class="dashicons dashicons-<?php echo $is_active ? 'yes' : 'no'; ?>"></span>
                    <span><?php echo esc_html($plugin_name); ?></span>
                    <span><?php echo $is_active ? '(فعال)' : '(غیرفعال یا نصب نشده)'; ?></span>
                </div>
            <?php endforeach; ?>

            <?php if (!$all_active): ?>
                <div class="notice notice-error">
                    <p>برای ادامه فرآیند درون‌ریزی، باید تمام پلاگین‌های ضروری نصب و فعال باشند.</p>
                    <p>
                        <a href="<?php echo admin_url('plugin-install.php'); ?>" class="button">
                            رفتن به صفحه مدیریت پلاگین‌ها
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div class="nader-import-actions">
            <button class="nader-import-button secondary">
                مرحله قبل
            </button>
            <button class="nader-import-button primary" <?php echo $all_active ? '' : 'disabled'; ?>>
                ادامه به مرحله بعد
            </button>
        </div>
        <?php
    }

    private static function import_pages_step()
    {
        ?>
        <div class="nader-import-box">
            <h3>درون‌ریزی برگه‌ها</h3>
            <p>با کلیک بر روی دکمه زیر، برگه‌های اصلی قالب وارد خواهند شد:</p>

            <ul class="nader-import-list">
                <li><span class="dashicons dashicons-admin-page"></span> صفحه اصلی</li>
                <li><span class="dashicons dashicons-admin-page"></span> وبلاگ</li>
                <li><span class="dashicons dashicons-admin-page"></span> تماس با ما</li>
                <li><span class="dashicons dashicons-admin-page"></span> درباره ما</li>
                <li><span class="dashicons dashicons-admin-page"></span> فروشگاه</li>
            </ul>

            <div class="notice notice-info">
                <p>توجه: این عمل برگه‌های موجود با همین نام را جایگزین خواهد کرد.</p>
            </div>
        </div>

        <div class="nader-import-actions">
            <button class="nader-import-button secondary">
                مرحله قبل
            </button>
            <button class="nader-import-button primary">
                شروع درون‌ریزی برگه‌ها
            </button>
        </div>
        <?php
    }

    private static function import_content_step()
    {
        ?>
        <div class="nader-import-box">
            <h3>درون‌ریزی محتوای نمونه</h3>
            <p>با کلیک بر روی دکمه زیر، محتوای نمونه وارد خواهد شد:</p>

            <ul class="nader-import-list">
                <li><span class="dashicons dashicons-edit"></span> نوشته‌های نمونه</li>
                <li><span class="dashicons dashicons-portfolio"></span> پروژه‌های نمونه</li>
                <li><span class="dashicons dashicons-cart"></span> محصولات نمونه</li>
                <li><span class="dashicons dashicons-format-gallery"></span> تصاویر و رسانه‌ها</li>
            </ul>
        </div>

        <div class="nader-import-actions">
            <button class="nader-import-button secondary">
                مرحله قبل
            </button>
            <button class="nader-import-button primary">
                شروع درون‌ریزی محتوا
            </button>
        </div>
        <?php
    }

    private static function elementor_kit_step()
    {
        ?>
        <div class="nader-import-box">
            <h3>تنظیم کیت المنتور</h3>
            <p>با کلیک بر روی دکمه زیر، تنظیمات سبک و رنگ‌های المنتور اعمال خواهد شد:</p>

            <ul class="nader-import-list">
                <li><span class="dashicons dashicons-admin-customizer"></span> تنظیمات رنگ‌های اصلی</li>
                <li><span class="dashicons dashicons-editor-textcolor"></span> تنظیمات تایپوگرافی</li>
                <li><span class="dashicons dashicons-button"></span> تنظیمات دکمه‌ها</li>
            </ul>

            <div class="notice notice-warning">
                <p>این عمل تنظیمات فعلی المنتور شما را بازنویسی خواهد کرد.</p>
            </div>
        </div>

        <div class="nader-import-actions">
            <button class="nader-import-button secondary">
                مرحله قبل
            </button>
            <button class="nader-import-button primary">
                شروع تنظیمات المنتور
            </button>
        </div>
        <?php
    }

    private static function theme_config_step()
    {
        ?>
        <div class="nader-import-box">
            <h3>پیکربندی قالب</h3>
            <p>با کلیک بر روی دکمه زیر، تنظیمات اصلی قالب اعمال خواهد شد:</p>

            <ul class="nader-import-list">
                <li><span class="dashicons dashicons-admin-appearance"></span> تنظیمات هدر و فوتر</li>
                <li><span class="dashicons dashicons-menu"></span> تنظیمات منوها</li>
                <li><span class="dashicons dashicons-admin-home"></span> تنظیمات صفحه اصلی</li>
                <li><span class="dashicons dashicons-store"></span> تنظیمات فروشگاه</li>
            </ul>
        </div>

        <div class="nader-import-actions">
            <button class="nader-import-button secondary">
                مرحله قبل
            </button>
            <button class="nader-import-button primary nader-import-success">
                تکمیل فرآیند درون‌ریزی
            </button>
        </div>
        <?php
    }

    private static function get_available_demos()
    {
        $demos_file = NADER_CENTER_PATH . 'assets/demos.json';

        if (!file_exists($demos_file)) {
            return array();
        }

        $demos_data = file_get_contents($demos_file);
        $demos = json_decode($demos_data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($demos['demos'])) {
            return array();
        }

        return $demos['demos'];
    }

    public static function ajax_load_import_step()
    {
        check_ajax_referer('nader_import_nonce', 'nonce');

        $step = isset($_POST['step']) ? intval($_POST['step']) : 1;
        ob_start();
        self::load_step_content($step);
        $content = ob_get_clean();

        wp_send_json_success(array('content' => $content));
    }

    public static function ajax_process_import_step()
    {
        check_ajax_referer('nader_import_nonce', 'nonce');

        $step = isset($_POST['step']) ? intval($_POST['step']) : 0;
        $demo_id = isset($_POST['demo_id']) ? sanitize_text_field($_POST['demo_id']) : '';

        $response = array(
            'success' => false,
            'message' => 'خطای ناشناخته'
        );

        if ($step < 1 || $step > count(self::$import_steps)) {
            $response['message'] = 'مرحله نامعتبر است';
            wp_send_json($response);
            return;
        }

        switch ($step) {
            case 1:
                if (empty($demo_id)) {
                    $response['message'] = 'لطفاً یک دمو را انتخاب کنید';
                } else {
                    update_option('nader_selected_demo', $demo_id);
                    $response['success'] = true;
                    $response['message'] = 'دمو با موفقیت انتخاب شد';
                }
                break;

            case 2:
                $missing_plugins = array();
                $all_plugins = get_plugins();

                foreach (self::$required_plugins as $plugin_path => $plugin_name) {
                    if (!isset($all_plugins[$plugin_path])) {
                        $missing_plugins[] = $plugin_name;
                    }
                }

                if (empty($missing_plugins)) {
                    $response['success'] = true;
                    $response['message'] = 'تمام پلاگین‌های ضروری نصب و فعال هستند';
                } else {
                    $response['message'] = 'لطفاً تمام پلاگین‌های ضروری را نصب و فعال کنید';
                }
                break;

            case 3:
                $imported = self::import_pages();
                $response['success'] = $imported;
                $response['message'] = $imported ? 'برگه‌ها با موفقیت وارد شدند' : 'خطا در درون‌ریزی برگه‌ها';
                break;

            case 4:
                $imported = self::import_content();
                $response['success'] = $imported;
                $response['message'] = $imported ? 'محتوای نمونه با موفقیت وارد شد' : 'خطا در درون‌ریزی محتوا';
                break;

            case 5:
                $configured = self::configure_elementor_kit();
                $response['success'] = $configured;
                $response['message'] = $configured ? 'کیت المنتور با موفقیت تنظیم شد' : 'خطا در تنظیم کیت المنتور';
                break;

            case 6:
                $configured = self::configure_theme();
                $response['success'] = $configured;
                $response['message'] = $configured ? 'پیکربندی قالب با موفقیت انجام شد' : 'خطا در پیکربندی قالب';
                break;
        }

        wp_send_json($response);
    }

    private static function import_pages()
    {
        // پیاده‌سازی واقعی این تابع
        return true;
    }

    private static function import_content()
    {
        // پیاده‌سازی واقعی این تابع
        return true;
    }

    private static function configure_elementor_kit()
    {
        // پیاده‌سازی واقعی این تابع
        return true;
    }

    private static function configure_theme()
    {
        // پیاده‌سازی واقعی این تابع
        return true;
    }
}

Nader_Center_Import::init();
