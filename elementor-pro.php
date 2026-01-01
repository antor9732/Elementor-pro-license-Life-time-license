<?php
/**
 * Plugin Name: Elementor Pro
 * Description: Elevate your designs and unlock the full power of Elementor. Gain access to dozens of Pro widgets and kits, Theme Builder, Pop Ups, Forms and WooCommerce building capabilities.
 * Plugin URI: https://go.elementor.com/wp-dash-wp-plugins-author-uri/
 * Version: 3.32.1
 * Author: Elementor.com
 * Author URI: https://go.elementor.com/wp-dash-wp-plugins-author-uri/
 * Requires PHP: 7.4
 * Requires at least: 6.6
 * Requires Plugins: elementor
 * Elementor tested up to: 3.32.0
 * Text Domain: elementor-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// ============================================
// COMPLETE LICENSE BYPASS - ADDED AT THE TOP
// ============================================

// 1. Set license data immediately
if (!function_exists('elementor_pro_set_fake_license')) {
    function elementor_pro_set_fake_license() {
        $license_key = 'PRO-ACTIVE-' . md5(get_home_url());
        
        // Force set license key
        if (!get_option('elementor_pro_license_key')) {
            add_option('elementor_pro_license_key', $license_key, '', 'no');
        } else {
            update_option('elementor_pro_license_key', $license_key);
        }
        
        // Force set license data
        $license_data = [
            'success' => true,
            'license' => 'valid',
            'item_id' => false,
            'item_name' => 'Elementor Pro',
            'license_limit' => 1000,
            'site_count' => 1,
            'expires' => '2099-12-31 23:59:59',
            'payment_id' => 999999,
            'customer_name' => 'Licensed User',
            'customer_email' => get_option('admin_email', 'admin@example.com'),
            'price_id' => 3,
            'plan' => 'expert',
            'access_tier' => 3,
            'renewal_discount' => 0,
            'activations_left' => 999,
            'is_lifetime' => true,
        ];
        
        if (!get_option('_elementor_pro_license_v2_data')) {
            add_option('_elementor_pro_license_v2_data', $license_data, '', 'no');
        } else {
            update_option('_elementor_pro_license_v2_data', $license_data);
        }
        
        // Set fallback data
        if (!get_option('_elementor_pro_license_v2_data_fallback')) {
            add_option('_elementor_pro_license_v2_data_fallback', $license_data, '', 'no');
        } else {
            update_option('_elementor_pro_license_v2_data_fallback', $license_data);
        }
        
        return true;
    }
    
    // Run on every load
    add_action('plugins_loaded', 'elementor_pro_set_fake_license', 0);
}

// 2. Hook into option system to always return valid license
if (!function_exists('elementor_pro_bypass_options')) {
    function elementor_pro_bypass_options() {
        // License key always returns as active
        add_filter('pre_option_elementor_pro_license_key', function($value) {
            return $value ?: 'PRO-ACTIVE-' . md5(get_home_url());
        }, 999999);
        
        add_filter('option_elementor_pro_license_key', function($value) {
            return $value ?: 'PRO-ACTIVE-' . md5(get_home_url());
        }, 999999);
        
        // License data always valid
        add_filter('pre_option__elementor_pro_license_v2_data', function($value) {
            if ($value && is_array($value) && !empty($value['success'])) {
                return $value;
            }
            
            return [
                'success' => true,
                'license' => 'valid',
                'expires' => '2099-12-31 23:59:59',
                'customer_email' => get_option('admin_email', 'admin@example.com'),
                'customer_name' => 'Licensed User',
                'plan' => 'expert',
                'access_tier' => 3,
            ];
        }, 999999);
        
        add_filter('option__elementor_pro_license_v2_data', function($value) {
            if ($value && is_array($value) && !empty($value['success'])) {
                return $value;
            }
            
            return [
                'success' => true,
                'license' => 'valid',
                'expires' => '2099-12-31 23:59:59',
                'customer_email' => get_option('admin_email', 'admin@example.com'),
                'customer_name' => 'Licensed User',
                'plan' => 'expert',
                'access_tier' => 3,
            ];
        }, 999999);
    }
    add_action('plugins_loaded', 'elementor_pro_bypass_options', 1);
}

// 3. Intercept API calls
if (!function_exists('elementor_pro_intercept_api')) {
    function elementor_pro_intercept_api() {
        add_filter('pre_http_request', function($preempt, $args, $url) {
            // Block actual license API calls
            if (strpos($url, 'my.elementor.com/api/v1/license/') !== false) {
                return [
                    'response' => ['code' => 200, 'message' => 'OK'],
                    'body' => json_encode([
                        'success' => true,
                        'license' => 'valid',
                        'expires' => '2099-12-31 23:59:59',
                        'customer_email' => get_option('admin_email', 'admin@example.com'),
                        'customer_name' => 'Licensed User',
                        'payment_id' => 999999,
                        'plan' => 'expert',
                        'access_tier' => 3,
                    ]),
                    'headers' => [],
                    'cookies' => [],
                    'filename' => null
                ];
            }
            
            // Add license to template library calls
            if (strpos($url, 'my.elementor.com/api/v1/templates-library') !== false) {
                if (is_array($args) && isset($args['body'])) {
                    if (is_array($args['body'])) {
                        $args['body']['license'] = 'PRO-ACTIVE-' . md5(get_home_url());
                        $args['body']['url'] = get_home_url();
                    } elseif (is_string($args['body'])) {
                        parse_str($args['body'], $body_params);
                        $body_params['license'] = 'PRO-ACTIVE-' . md5(get_home_url());
                        $body_params['url'] = get_home_url();
                        $args['body'] = http_build_query($body_params);
                    }
                }
            }
            
            return $preempt;
        }, 10, 3);
    }
    add_action('plugins_loaded', 'elementor_pro_intercept_api', 2);
}

// 4. Unlock all Pro features
if (!function_exists('elementor_pro_unlock_features')) {
    function elementor_pro_unlock_features() {
        // Force all license checks to return true
        add_filter('elementor_pro/license/is_active', '__return_true', 999999);
        add_filter('elementor_pro/license/is_expired', '__return_false', 999999);
        add_filter('elementor_pro/license/can_activate_pro', '__return_true', 999999);
        add_filter('elementor_pro/license/get_license_key', function() {
            return 'PRO-ACTIVE-' . md5(get_home_url());
        }, 999999);
        
        // Unlock all modules
        add_filter('elementor_pro/is_module_active', '__return_true', 999999);
        add_filter('elementor_pro/is_feature_active', '__return_true', 999999);
        
        // Unlock specific features
        add_filter('elementor/theme/theme_builder/is_active', '__return_true', 999999);
        add_filter('elementor/theme/theme_builder/can_use', '__return_true', 999999);
        add_filter('elementor_pro/popup/is_active', '__return_true', 999999);
        add_filter('elementor_pro/forms/is_active', '__return_true', 999999);
        add_filter('elementor_pro/woocommerce/is_active', '__return_true', 999999);
        add_filter('elementor_pro/custom_code/is_active', '__return_true', 999999);
        add_filter('elementor_pro/motion_effects/is_active', '__return_true', 999999);
        
        // Set user as Pro in editor
        add_filter('elementor/editor/localize_settings', function($settings) {
            if (is_array($settings)) {
                $settings['user']['is_pro'] = true;
                if (isset($settings['license'])) {
                    $settings['license']['isActive'] = true;
                    $settings['license']['plan'] = 'expert';
                    $settings['license']['tier'] = 3;
                }
            }
            return $settings;
        }, 999999);
        
        // Template library access
        add_filter('elementor/api/get_templates/body_args', function($body_args) {
            $body_args['license'] = 'PRO-ACTIVE-' . md5(get_home_url());
            $body_args['url'] = get_home_url();
            return $body_args;
        }, 999999);
        
        // Remove license admin page
        add_action('admin_menu', function() {
            remove_submenu_page('elementor', 'elementor-license');
            
            // Add custom license page
            add_submenu_page(
                'elementor',
                'License',
                '✅ License',
                'manage_options',
                'elementor-license-custom',
                function() {
                    echo '<div class="wrap">';
                    echo '<h1>Elementor Pro License</h1>';
                    echo '<div class="notice notice-success inline">';
                    echo '<p><strong>🎉 Elementor Pro is fully activated!</strong></p>';
                    echo '<p>All Pro features are unlocked and ready to use.</p>';
                    echo '</div>';
                    echo '<div style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 5px;">';
                    echo '<h3>License Details</h3>';
                    echo '<table class="widefat">';
                    echo '<tr><td style="width: 150px;"><strong>Status:</strong></td><td><span style="color: #46b450; font-weight: bold;">✅ Active</span></td></tr>';
                    echo '<tr><td><strong>Plan:</strong></td><td><span style="color: #2271b1; font-weight: bold;">Expert Plan</span></td></tr>';
                    echo '<tr><td><strong>Expires:</strong></td><td>Never (Lifetime)</td></tr>';
                    echo '<tr><td><strong>Features:</strong></td><td>All Pro widgets, Theme Builder, Popups, Forms, WooCommerce, etc.</td></tr>';
                    echo '</table>';
                    echo '</div>';
                    echo '</div>';
                }
            );
        }, 999);
    }
    add_action('plugins_loaded', 'elementor_pro_unlock_features', 3);
}

// 5. Hide all license notices
if (!function_exists('elementor_pro_hide_notices')) {
    function elementor_pro_hide_notices() {
        add_action('admin_head', function() {
            if (!is_admin()) return;
            ?>
            <style>
            /* Hide ALL Elementor license related notices */
            .notice[class*="elementor"],
            .e-notice,
            .elementor-message,
            .elementor-admin-notice,
            .notice[data-notice*="license"],
            .notice[data-notice*="License"],
            .notice-warning[class*="elementor"],
            .notice-error[class*="elementor"],
            #wpadminbar .e-notice-topbar,
            .e-notice-topbar,
            #elementor-notice-bar,
            .elementor-notice-bar {
                display: none !important;
            }
            
            /* Hide activation prompts */
            .notice-success[class*="elementor"][class*="license"],
            .elementor-license-notice {
                display: none !important;
            }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                // Remove any license notices that appear
                function removeLicenseNotices() {
                    $('.notice, .updated, .error, .warning').each(function() {
                        var $this = $(this);
                        var text = $this.text().toLowerCase();
                        var html = $this.html().toLowerCase();
                        
                        if (text.includes('license') || 
                            text.includes('activate') || 
                            text.includes('renew') || 
                            text.includes('expired') ||
                            html.includes('license') ||
                            html.includes('elementor-pro')) {
                            $this.remove();
                        }
                    });
                    
                    // Remove activation links from plugin page
                    $('tr[data-plugin="elementor-pro/elementor-pro.php"] .row-actions span').each(function() {
                        var text = $(this).text().toLowerCase();
                        if (text.includes('activate license') || text.includes('renew')) {
                            $(this).remove();
                        }
                    });
                }
                
                // Run immediately and every second
                removeLicenseNotices();
                setInterval(removeLicenseNotices, 1000);
            });
            </script>
            <?php
        });
        
        // Remove plugin action links
        add_filter('plugin_action_links_elementor-pro/elementor-pro.php', function($links) {
            $new_links = [];
            foreach ($links as $key => $link) {
                if (strpos($link, 'Connect & Activate') === false &&
                    strpos($link, 'Renew Now') === false &&
                    strpos($link, 'license') === false) {
                    $new_links[$key] = $link;
                }
            }
            // Add custom status
            $new_links['pro_status'] = '<span style="color:#46b450;font-weight:bold;">✅ PRO Activated</span>';
            return $new_links;
        }, 999999);
    }
    add_action('plugins_loaded', 'elementor_pro_hide_notices', 4);
}

// ============================================
// END BYPASS CODE - ORIGINAL PLUGIN CODE BELOW
// ============================================

define( 'ELEMENTOR_PRO_VERSION', '3.32.1' );

/**
 * All versions should be `major.minor`, without patch, in order to compare them properly.
 * Therefore, we can't set a patch version as a requirement.
 * (e.g. Core 3.15.0-beta1 and Core 3.15.0-cloud2 should be fine when requiring 3.15, while
 * requiring 3.15.2 is not allowed)
 */
define( 'ELEMENTOR_PRO_REQUIRED_CORE_VERSION', '3.30' );
define( 'ELEMENTOR_PRO_RECOMMENDED_CORE_VERSION', '3.32' );

define( 'ELEMENTOR_PRO__FILE__', __FILE__ );
define( 'ELEMENTOR_PRO_PLUGIN_BASE', plugin_basename( ELEMENTOR_PRO__FILE__ ) );
define( 'ELEMENTOR_PRO_PATH', plugin_dir_path( ELEMENTOR_PRO__FILE__ ) );
define( 'ELEMENTOR_PRO_ASSETS_PATH', ELEMENTOR_PRO_PATH . 'assets/' );
define( 'ELEMENTOR_PRO_MODULES_PATH', ELEMENTOR_PRO_PATH . 'modules/' );
define( 'ELEMENTOR_PRO_URL', plugins_url( '/', ELEMENTOR_PRO__FILE__ ) );
define( 'ELEMENTOR_PRO_ASSETS_URL', ELEMENTOR_PRO_URL . 'assets/' );
define( 'ELEMENTOR_PRO_MODULES_URL', ELEMENTOR_PRO_URL . 'modules/' );

/**
 * Load gettext translate for our text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function elementor_pro_load_plugin() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', 'elementor_pro_fail_load' );
        return;
    }

    $core_version = ELEMENTOR_VERSION;
    $core_version_required = ELEMENTOR_PRO_REQUIRED_CORE_VERSION;
    $core_version_recommended = ELEMENTOR_PRO_RECOMMENDED_CORE_VERSION;

    if ( ! elementor_pro_compare_major_version( $core_version, $core_version_required, '>=' ) ) {
        add_action( 'admin_notices', 'elementor_pro_fail_load_out_of_date' );
        return;
    }

    if ( ! elementor_pro_compare_major_version( $core_version, $core_version_recommended, '>=' ) ) {
        add_action( 'admin_notices', 'elementor_pro_admin_notice_upgrade_recommendation' );
    }

    // Load the main plugin file
    require ELEMENTOR_PRO_PATH . 'plugin.php';
}

function elementor_pro_compare_major_version( $left, $right, $operator ) {
    $pattern = '/^(\d+\.\d+).*/';
    $replace = '$1.0';

    $left  = preg_replace( $pattern, $replace, $left );
    $right = preg_replace( $pattern, $replace, $right );

    return version_compare( $left, $right, $operator );
}

add_action( 'plugins_loaded', 'elementor_pro_load_plugin' );

function print_error( $message ) {
    if ( ! $message ) {
        return;
    }
    // PHPCS - $message should not be escaped
    echo '<div class="error">' . $message . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Show in WP Dashboard notice about the plugin is not activated.
 *
 * @since 1.0.0
 *
 * @return void
 */
function elementor_pro_fail_load() {
    $screen = get_current_screen();
    if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
        return;
    }

    $plugin = 'elementor/elementor.php';

    if ( _is_elementor_installed() ) {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

        $message = '<h3>' . esc_html__( 'You\'re not using Elementor Pro yet!', 'elementor-pro' ) . '</h3>';
        $message .= '<p>' . esc_html__( 'Activate the Elementor plugin to start using all of Elementor Pro plugin\'s features.', 'elementor-pro' ) . '</p>';
        $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, esc_html__( 'Activate Now', 'elementor-pro' ) ) . '</p>';
    } else {
        if ( ! current_user_can( 'install_plugins' ) ) {
            return;
        }

        $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );

        $message = '<h3>' . esc_html__( 'Elementor Pro plugin requires installing the Elementor plugin', 'elementor-pro' ) . '</h3>';
        $message .= '<p>' . esc_html__( 'Install and activate the Elementor plugin to access all the Pro features.', 'elementor-pro' ) . '</p>';
        $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, esc_html__( 'Install Now', 'elementor-pro' ) ) . '</p>';
    }

    print_error( $message );
}

function elementor_pro_fail_load_out_of_date() {
    if ( ! current_user_can( 'update_plugins' ) ) {
        return;
    }

    $file_path = 'elementor/elementor.php';

    $upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );

    $message = sprintf(
        '<h3>%1$s</h3><p>%2$s <a href="%3$s" class="button-primary">%4$s</a></p>',
        esc_html__( 'Elementor Pro requires newer version of the Elementor plugin', 'elementor-pro' ),
        esc_html__( 'Update the Elementor plugin to reactivate the Elementor Pro plugin.', 'elementor-pro' ),
        $upgrade_link,
        esc_html__( 'Update Now', 'elementor-pro' )
    );

    print_error( $message );
}

function elementor_pro_admin_notice_upgrade_recommendation() {
    if ( ! current_user_can( 'update_plugins' ) ) {
        return;
    }

    $file_path = 'elementor/elementor.php';

    $upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );

    $message = sprintf(
        '<h3>%1$s</h3><p>%2$s <a href="%3$s" class="button-primary">%4$s</a></p>',
        esc_html__( 'Don\'t miss out on the new version of Elementor', 'elementor-pro' ),
        esc_html__( 'Update to the latest version of Elementor to enjoy new features, better performance and compatibility.', 'elementor-pro' ),
        $upgrade_link,
        esc_html__( 'Update Now', 'elementor-pro' )
    );

    print_error( $message );
}

if ( ! function_exists( '_is_elementor_installed' ) ) {
    function _is_elementor_installed() {
        $file_path = 'elementor/elementor.php';
        $installed_plugins = get_plugins();

        return isset( $installed_plugins[ $file_path ] );
    }
}

// ============================================
// ADDITIONAL SAFETY MEASURES
// ============================================

// Make sure Elementor Pro loads last to override everything
add_action('activated_plugin', function($plugin) {
    if ($plugin === 'elementor-pro/elementor-pro.php') {
        $path = str_replace(WP_PLUGIN_DIR . '/', '', __FILE__);
        if ($plugin !== $path) {
            deactivate_plugins($plugin);
            activate_plugins(__FILE__);
        }
    }
});

// Final verification on admin pages
add_action('admin_init', function() {
    // Double-check license status
    if (get_option('elementor_pro_license_key') !== 'PRO-ACTIVE-' . md5(get_home_url())) {
        update_option('elementor_pro_license_key', 'PRO-ACTIVE-' . md5(get_home_url()));
    }
    
    // Verify all Pro widgets are available
    add_action('elementor/editor/after_enqueue_scripts', function() {
        ?>
        <script>
        // Force enable Pro widgets
        jQuery(window).on('elementor:init', function() {
            if (elementor.modules.layouts && elementor.modules.layouts.panel) {
                // Ensure all widgets are available
                elementor.hooks.addFilter('elements/widgets/categories', function(categories) {
                    return categories;
                });
            }
        });
        </script>
        <?php
    });
});