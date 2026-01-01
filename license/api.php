<?php
namespace ElementorPro\License;

class API {
    // All constants from original API.php
    const STATUS_VALID = 'valid';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_HTTP_ERROR = 'http_error';
    const STATUS_SITE_INACTIVE = 'site_inactive';
    const STATUS_MISSING = 'missing';
    const STATUS_REQUEST_LOCKED = 'request_locked';
    const STATUS_DISCONNECTED = 'disconnected';
    
    const TIER_ESSENENTIAL = 1;
    const TIER_ADVANCED = 2;
    const TIER_EXPERT = 3;
    const TIER_AGENCY = 4;
    
    // Features
    const FEATURE_ADVANCED_CUSTOM_FIELDS = 'advanced-custom-fields';
    const FEATURE_PRO_TRIAL = 'pro-trial';
    const FEATURE_CUSTOM_ATTRIBUTES = 'custom-attributes';
    const FEATURE_DISPLAY_CONDITIONS = 'display-conditions';
    const FEATURE_EYE_DROPPER = 'eye-dropper';
    const FEATURE_FORMS = 'forms';
    const FEATURE_GLOBAL_WIDGET = 'global-widget';
    const FEATURE_LOOP_FILTER = 'loop-filter';
    const FEATURE_MOTION_EFFECTS = 'motion-effects';
    const FEATURE_POPUP = 'popup';
    const FEATURE_THEME_BUILDER = 'theme-builder';
    const FEATURE_WIDGET_PRO = 'widget-pro';
    const FEATURE_WOOCOMMERCE = 'woocommerce';
    const FEATURE_CUSTOM_CODE = 'custom-code';
    const FEATURE_ROLE_MANAGER = 'role-manager';
    const FEATURE_ASSETS_MANAGER = 'assets-manager';
    const FEATURE_SITE_NAVIGATION = 'site-navigation';
    const FEATURE_MEGA_MENU = 'mega-menu';
    const FEATURE_LOOP_CAROUSEL = 'loop-carousel';
    const FEATURE_NESTED_CAROUSEL = 'nested-carousel';
    const FEATURE_NESTED_TABS = 'nested-tabs';
    const FEATURE_NESTED_ACCORDION = 'nested-accordion';
    const FEATURE_PAYMENT_BUTTON = 'payment-button';
    const FEATURE_VIDEO_PLAYLIST = 'video-playlist';
    const FEATURE_SOCIAL = 'social';
    const FEATURE_FLIP_BOX = 'flip-box';
    const FEATURE_CALL_TO_ACTION = 'call-to-action';
    const FEATURE_ANIMATED_HEADLINE = 'animated-headline';
    const FEATURE_HOTSPOT = 'hotspot';
    const FEATURE_BLOCKQUOTE = 'blockquote';
    const FEATURE_GALLERY = 'gallery';
    const FEATURE_NAV_MENU = 'nav-menu';
    const FEATURE_SLIDES = 'slides';
    const FEATURE_SHARE_BUTTONS = 'share-buttons';
    const FEATURE_TESTIMONIAL_CAROUSEL = 'testimonial-carousel';
    const FEATURE_PORTFOLIO = 'portfolio';
    const FEATURE_COUNTDOWN = 'countdown';
    const FEATURE_PRICE_LIST = 'price-list';
    const FEATURE_PRICE_TABLE = 'price-table';
    const FEATURE_PROGRESS_TRACKER = 'progress-tracker';
    const FEATURE_TABLE_OF_CONTENTS = 'table-of-contents';
    const FEATURE_LOGIN = 'login';
    const FEATURE_MEDIA_CAROUSEL = 'media-carousel';
    const FEATURE_AUDIO = 'audio';
    const FEATURE_CUSTOM_CSS = 'custom-css';
    const FEATURE_LOTTIE = 'lottie';
    const FEATURE_FORMS_SUBMISSIONS = 'forms-submissions';
    
    const BC_VALIDATION_CALLBACK = '__return_true';
    
    const RENEW_URL = 'https://go.elementor.com/renew/';
    const UPGRADE_URL = 'https://go.elementor.com/go-pro-advanced-license-screen/';
    
    // ============================================
    // ALL LICENSE STATUS METHODS - ALWAYS RETURN TRUE
    // ============================================
    
    public static function is_license_active() { return true; }
    public static function is_license_expired() { return false; }
    public static function is_licence_pro_trial() { return false; }
    public static function is_license_about_to_expire() { return false; }
    public static function is_need_to_show_upgrade_promotion() { return false; }
    public static function is_license_grace_period() { return false; }
    public static function is_beta_tester() { return false; }
    public static function is_development_mode() { return false; }
    public static function is_staging() { return false; }
    public static function is_local() { return false; }
    public static function is_connected() { return true; }
    public static function is_valid() { return true; }
    public static function is_valid_for_domain() { return true; }
    public static function is_auto_renew() { return true; }
    public static function is_license_disconnected() { return false; }
    
    // ============================================
    // ALL FEATURE CHECK METHODS - ALWAYS RETURN TRUE
    // ============================================
    
    public static function is_licence_has_feature( $feature_name, $callback = null ) { return true; }
    public static function active_licence_has_feature( $feature_name ) { return true; }
    public static function has_feature( $feature_name ) { return true; }
    public static function can_use_feature( $feature_name ) { return true; }
    public static function is_at_least_feature( $feature_tier ) { return true; }
    public static function is_feature_active( $feature_name ) { return true; }
    public static function validate_feature( $feature_name ) { return true; }
    public static function is_feature_in_tier( $feature_name, $tier ) { return true; }
    public static function active_license_has_feature( $feature_name ) { return true; }
    
    // ============================================
    // FEATURE FILTERING METHODS
    // ============================================
    
    public static function filter_active_features( $features ) {
        return is_array($features) ? $features : [];
    }
    
    public static function get_active_features() {
        return self::get_license_features();
    }
    
    public static function get_promotion_widgets() {
        // Return empty array since all widgets are available
        return [];
    }
    
    public static function get_promotion_features() {
        // Return empty array since all features are available
        return [];
    }
    
    public static function get_available_promotions() {
        return [];
    }
    
    public static function get_promotion_data( $promotion_id ) {
        return null;
    }
    
    // ============================================
    // LICENSE DATA METHODS
    // ============================================
    
    public static function get_license_data( $force_request = false ) {
        return [
            'success' => true,
            'license' => 'valid',
            'item_id' => false,
            'item_name' => 'Elementor Pro',
            'license_limit' => 1000,
            'site_count' => 1,
            'expires' => '2099-12-31 23:59:59',
            'payment_id' => 999999,
            'customer_name' => 'Premium User',
            'customer_email' => get_option('admin_email', 'admin@example.com'),
            'price_id' => 3,
            'plan' => 'expert',
            'access_tier' => 3,
            'renewal_discount' => 0,
            'activations_left' => 999,
            'is_lifetime' => true,
            'features' => ['all'],
            'subscription_id' => 'SUB-' . md5(get_home_url()),
            'product_id' => 1,
            'license_key' => 'ELEMENTOR-PRO-ACTIVATED-' . md5(get_home_url()),
            'status' => 'valid',
            'remaining_activations' => 999,
            'activation_limit' => 1000,
            'date_created' => date('Y-m-d H:i:s'),
            'date_expiry' => '2099-12-31 23:59:59',
        ];
    }
    
    public static function get_cached_license_data() {
        return self::get_license_data();
    }
    
    public static function get_license_features() {
        // Return ALL features
        $reflection = new \ReflectionClass(__CLASS__);
        $constants = $reflection->getConstants();
        
        $features = [];
        foreach ($constants as $name => $value) {
            if (strpos($name, 'FEATURE_') === 0) {
                $features[] = $value;
            }
        }
        
        return $features;
    }
    
    // ============================================
    // LICENSE INFO METHODS
    // ============================================
    
    public static function get_access_tier() { return 3; }
    public static function get_library_access_level() { return 3; }
    public static function get_plan_type() { return 'expert'; }
    public static function get_subscription_plan() { return 'expert'; }
    public static function get_license_type() { return 'expert'; }
    public static function get_product_name() { return 'Elementor Pro'; }
    public static function get_license_key() { return 'ELEMENTOR-PRO-ACTIVATED-' . md5(get_home_url()); }
    public static function get_feature_tier( $feature_name ) { return 3; }
    public static function get_license_status() { return 'valid'; }
    public static function get_license_status_label() { return 'Active'; }
    public static function get_license_plan() { return 'expert'; }
    
    // ============================================
    // TIME METHODS
    // ============================================
    
    public static function get_expiration_time() { return strtotime('2099-12-31 23:59:59'); }
    public static function get_time_to_expire() { return 9999999999; }
    public static function get_time_to_expire_in_days() { return 36500; }
    public static function get_last_check() { return time(); }
    public static function get_next_payment_date() { return '2099-12-31'; }
    public static function get_license_age() { return 365; }
    
    // ============================================
    // ACTION METHODS
    // ============================================
    
    public static function activate_license( $license_key ) { return self::get_license_data(); }
    public static function deactivate_license() { return true; }
    public static function set_license_data( $data ) { return true; }
    public static function set_license_key( $license_key ) { return true; }
    public static function reset_license_data() { return true; }
    public static function check_for_updates() { return true; }
    public static function sync_license_data() { return true; }
    public static function refresh_license_data() { return self::get_license_data(); }
    
    // ============================================
    // USER/CONNECTION METHODS
    // ============================================
    
    public static function get_connected_user() {
        return (object) [
            'email' => get_option('admin_email'),
            'name' => 'Admin User',
            'username' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'avatar' => '',
            'is_verified' => true,
            'id' => 1,
        ];
    }
    
    public static function get_site_key() {
        return md5(get_home_url());
    }
    
    public static function get_account_url() {
        return 'https://my.elementor.com/';
    }
    
    // ============================================
    // ERROR/STATUS METHODS
    // ============================================
    
    public static function get_error_message( $error ) { return 'License is active'; }
    public static function get_errors_details() { return []; }
    public static function get_license_errors() { return []; }
    public static function get_human_readable_license_status() { return 'Active'; }
    public static function get_version() { return ELEMENTOR_PRO_VERSION; }
    public static function get_activation_url() { return admin_url('admin.php?page=elementor-license'); }
    public static function get_renewal_url() { return self::RENEW_URL; }
    
    // ============================================
    // ADDITIONAL UTILITY METHODS
    // ============================================
    
    public static function get_feature_label( $feature_name ) {
        $labels = [
            self::FEATURE_CUSTOM_ATTRIBUTES => 'Custom Attributes',
            self::FEATURE_THEME_BUILDER => 'Theme Builder',
            self::FEATURE_POPUP => 'Popup Builder',
            self::FEATURE_FORMS => 'Form Builder',
            self::FEATURE_WOOCOMMERCE => 'WooCommerce Builder',
            self::FEATURE_CUSTOM_CODE => 'Custom Code',
            self::FEATURE_MOTION_EFFECTS => 'Motion Effects',
            self::FEATURE_FORMS_SUBMISSIONS => 'Forms Submissions',
            self::FEATURE_GLOBAL_WIDGET => 'Global Widget',
            self::FEATURE_ROLE_MANAGER => 'Role Manager',
        ];
        
        return $labels[$feature_name] ?? ucfirst(str_replace('-', ' ', $feature_name));
    }
    
    public static function get_all_features() {
        return self::get_license_features();
    }
    
    public static function is_feature_available( $feature_name ) {
        return true;
    }
    
    public static function get_license_info() {
        return self::get_license_data();
    }
    
    public static function get_subscription_info() {
        return [
            'status' => 'active',
            'plan' => 'expert',
            'is_active' => true,
            'is_cancelled' => false,
            'next_payment_date' => '2099-12-31',
        ];
    }
    
    public static function get_billing_info() {
        return [
            'status' => 'active',
            'next_payment' => '2099-12-31',
            'amount' => 0,
            'currency' => 'USD',
        ];
    }
    
    public static function get_upgrade_url( $tier = null ) {
        return self::UPGRADE_URL;
    }
    
    public static function get_switch_account_url() {
        return admin_url('admin.php?page=elementor-license');
    }
    
    public static function get_disconnect_url() {
        return '#';
    }
    
    // ============================================
    // WIDGET METHODS
    // ============================================
    
    public static function get_pro_widgets() {
        // Return all Pro widgets
        return [
            'slides',
            'form',
            'popup',
            'theme-elements',
            'woocommerce',
            'nested-carousel',
            'nested-tabs',
            'nested-accordion',
            'loop-grid',
            'loop-carousel',
            'mega-menu',
            'hotspot',
            'flip-box',
            'call-to-action',
            'animated-headline',
            'lottie',
            'paypal-button',
            'stripe-button',
            'video-playlist',
            'social',
            'testimonial-carousel',
            'portfolio',
            'gallery',
            'nav-menu',
            'share-buttons',
            'blockquote',
            'price-table',
            'price-list',
            'countdown',
            'progress-tracker',
            'table-of-contents',
            'login',
            'media-carousel',
            'audio',
        ];
    }
    
    public static function is_widget_available( $widget_name ) {
        return true;
    }
    
    public static function get_available_widgets() {
        return self::get_pro_widgets();
    }
    
    // ============================================
    // MODULE METHODS
    // ============================================
    
    public static function get_pro_modules() {
        return [
            'theme-builder',
            'popup',
            'forms',
            'woocommerce',
            'custom-code',
            'motion-effects',
            'custom-attributes',
            'display-conditions',
            'global-widget',
            'role-manager',
            'assets-manager',
            'site-navigation',
            'loop-builder',
            'nested-elements',
        ];
    }
    
    public static function is_module_available( $module_name ) {
        return true;
    }
}

// Disable WordPress update notifications and plugin API responses for Elementor Pro
if ( ! function_exists( 'elementor_pro_disable_updates' ) ) {
    add_filter( 'site_transient_update_plugins', function( $transient ) {
        if ( is_object( $transient ) && isset( $transient->response['elementor-pro/elementor-pro.php'] ) ) {
            unset( $transient->response['elementor-pro/elementor-pro.php'] );
        }
        return $transient;
    }, 9999 );

    add_filter( 'pre_set_site_transient_update_plugins', function( $transient ) {
        if ( is_object( $transient ) && isset( $transient->response['elementor-pro/elementor-pro.php'] ) ) {
            unset( $transient->response['elementor-pro/elementor-pro.php'] );
        }
        return $transient;
    }, 9999 );

    add_filter( 'plugins_api_result', function( $res, $action, $args ) {
        if ( isset( $args->slug ) && $args->slug === 'elementor-pro' ) {
            return null;
        }
        return $res;
    }, 10, 3 );
}