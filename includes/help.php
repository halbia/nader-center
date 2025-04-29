<?php
defined('ABSPATH') || die();

class Nader_Center_Help {
    public static function display() {
        // مسیر فایل JSON
        $json_file = NADER_CENTER_PATH . 'assets/help.json';

        // بررسی وجود فایل
        if (!file_exists($json_file)) {
            echo '<div class="notice notice-error"><p>فایل راهنما یافت نشد.</p></div>';
            return;
        }

        // خواندن فایل JSON
        $json_data = file_get_contents($json_file);
        $help_data = json_decode($json_data, true);

        // بررسی خطای JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '<div class="notice notice-error"><p>خطا در پردازش فایل راهنما.</p></div>';
            return;
        }

        ?>
        <div class="wrap">
            <h1>راهنمای مرکز نادر</h1>

            <!-- باکس اطلاع‌رسانی -->
            <div class="nader-notice-box-enhanced">
                <div class="nader-notice-icon">
                    <span class="dashicons dashicons-media-document"></span>
                </div>
                <div class="nader-notice-content">
                    <h3>📚 راهنمای جامع قالب</h3>
                    <p>اکثر سوالات شما دوستان عزیز در فایل راهنما PDF قالب با استفاده از اسکرین‌شات و به صورت قدم به قدم پاسخ داده شده است. در بخش‌های زیر سوالاتی است که توسط کاربران بیشتر پرسیده شده است.</p>
                    <div class="nader-notice-actions">
                        <a href="#" target="_blank" class="nader-notice-button primary">چت با پشتیبان آنلاین</a>
                        <a href="#" class="nader-notice-button secondary">دانلود راهنمای PDF</a>
                    </div>
                </div>
            </div>

            <div class="nader-help-boxes">
                <?php foreach ($help_data['help_sections'] as $section): ?>
                    <div class="nader-help-box">
                        <span class="dashicons dashicons-<?php echo esc_attr($section['icon']); ?>"></span>
                        <h3><?php echo esc_html($section['title']); ?></h3>
                        <ul class="help-links">
                            <?php foreach ($section['links'] as $link): ?>
                                <li>
                                    <a href="<?php echo $link['url'] ? esc_url($link['url']) : '#'; ?>"
                                        <?php echo $link['url'] ? 'target="_blank"' : ''; ?>
                                        <?php echo !$link['url'] ? 'class="no-link"' : ''; ?>>
                                        <?php echo esc_html($link['text']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
        <?php
    }
}
