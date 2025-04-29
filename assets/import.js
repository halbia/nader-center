jQuery(document).ready(function($) {

    const importData = {
        currentStep: 1,
        selectedDemo: null,
        steps: [
            'انتخاب دمو',
            'بررسی پلاگین‌ها',
            'درون‌ریزی برگه‌ها',
            'درون‌ریزی محتوا',
            'تنظیم کیت المنتور',
            'پیکربندی قالب'
        ],
        requiredPlugins: {
            'elementor/elementor.php': 'المنتور',
            'advanced-custom-fields-pro/acf.php': 'ACF Pro',
            'woocommerce/woocommerce.php': 'ووکامرس'
        }
    };

    // ----------------------------
    // توابع اصلی
    // ----------------------------

    // مقداردهی اولیه
    function initImportWizard() {
        updateStepsUI();
        bindEvents();
    }

    // اتصال رویدادها
    function bindEvents() {
        // کلیک روی مراحل
        $(document).on('click', '.nader-step', function() {
            const step = $(this).data('step');
            if (!isNaN(step)) {
                loadImportStep(step);
            }
        });

        // دکمه ادامه
        $(document).on('click', '.nader-import-button.primary:not(:disabled)', processCurrentStep);

        // دکمه بازگشت
        $(document).on('click', '.nader-import-button.secondary', function() {
            loadImportStep(importData.currentStep - 1);
        });

        // انتخاب دمو
        $(document).on('click', '.nader-select-demo', function() {
            const demoBox = $(this).closest('.nader-demo-box');
            importData.selectedDemo = demoBox.data('demo-id');

            $('.nader-demo-box').removeClass('selected');
            demoBox.addClass('selected');

            $('#nader-continue-after-demo').prop('disabled', false);
        });
    }

    // بارگذاری مرحله
    function loadImportStep(step) {
        showLoading();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nader_load_import_step',
                step: step,
                nonce: naderImport.nonce
            },
            success: function(response) {
                if (response.success) {
                    importData.currentStep = step;
                    $('.nader-import-content').html(response.data.content);
                    updateStepsUI();
                } else {
                    showError('خطا در بارگذاری مرحله');
                }
            },
            error: handleAjaxError
        });
    }

    // پردازش مرحله فعلی
    function processCurrentStep() {
        if (!validateCurrentStep()) return;

        showProcessing();
        const nextStep = importData.currentStep + 1;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: getProcessStepData(),
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    markStepAsCompleted();
                    setTimeout(() => loadImportStep(nextStep), 1500);
                } else {
                    showError(response.message);
                }
            },
            error: handleAjaxError
        });
    }

    // ----------------------------
    // توابع کمکی
    // ----------------------------

    // اعتبارسنجی مرحله فعلی
    function validateCurrentStep() {
        if (importData.currentStep === 1 && !importData.selectedDemo) {
            showError('لطفاً یک دمو را انتخاب کنید');
            return false;
        }
        return true;
    }

    // آماده‌سازی داده‌های مرحله پردازش
    function getProcessStepData() {
        const data = {
            action: 'nader_process_import_step',
            step: importData.currentStep,
            nonce: naderImport.nonce
        };

        if (importData.currentStep === 1) {
            data.demo_id = importData.selectedDemo;
        }

        return data;
    }

    // به‌روزرسانی UI مراحل
    function updateStepsUI() {
        $('.nader-step').removeClass('active completed');

        $('.nader-step').each(function() {
            const step = $(this).data('step');

            if (step < importData.currentStep) {
                $(this).addClass('completed');
            } else if (step == importData.currentStep) {
                $(this).addClass('active');
            }
        });
    }

    // علامت‌گذاری مرحله به عنوان تکمیل شده
    function markStepAsCompleted() {
        $(`.nader-step[data-step="${importData.currentStep}"]`).addClass('completed');
    }

    // ----------------------------
    // توابع نمایش وضعیت
    // ----------------------------

    function showLoading() {
        $('.nader-import-content').html(`
            <div class="nader-import-status">
                <span class="nader-spinner"></span>
                در حال بارگذاری...
            </div>
        `);
    }

    function showProcessing() {
        $('.nader-import-actions').hide();
        $('.nader-import-content').append(`
            <div class="nader-import-status">
                <span class="nader-spinner"></span>
                در حال پردازش...
            </div>
        `);
    }

    function showSuccess(message) {
        $('.nader-import-status').html(`
            <div class="nader-success-message">
                <span class="dashicons dashicons-yes-alt"></span>
                ${message}
            </div>
        `);
    }

    function showError(message) {
        $('.nader-import-status').remove();
        alert(message);
        $('.nader-import-actions').show();
    }

    function handleAjaxError(xhr, status, error) {
        $('.nader-import-status').remove();
        console.error('AJAX Error:', status, error);
        showError('خطا در پردازش مرحله');
        $('.nader-import-actions').show();
    }

    // ----------------------------
    // راه‌اندازی اولیه
    // ----------------------------
    initImportWizard();
});