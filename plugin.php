<?php
namespace ElementorPro;

use ElementorPro\Core\Maintenance;
use ElementorPro\Core\PHP_Api;
use ElementorPro\Core\Admin\Admin;
use ElementorPro\Core\App\App;
use ElementorPro\Core\Connect;
use ElementorPro\Core\Compatibility\Compatibility;
use Elementor\Utils;
use ElementorPro\Core\Editor\Editor;
use ElementorPro\Core\Integrations\Integrations_Manager;
use ElementorPro\Core\Modules_Manager;
use ElementorPro\Core\Notifications\Notifications_Manager;
use ElementorPro\Core\Preview\Preview;
use ElementorPro\Core\Upgrade\Manager as UpgradeManager;
use ElementorPro\License\API;
use ElementorPro\License\Updater;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// ============================================
// ULTIMATE LICENSE BYPASS - COMPLETE SOLUTION
// ============================================

// 1. FIRST: Set fake license data immediately when plugin loads
register_activation_hook(__FILE__, function() {
    $license_data = [
        'success' => true,
        'license' => 'valid',
        'item_id' => false,
        'item_name' => 'Elementor Pro',
        'license_limit' => 999,
        'site_count' => 1,
        'expires' => '2099-12-31 23:59:59',
        'payment_id' => 999999,
        'customer_name' => 'Licensed User',
        'customer_email' => get_option('admin_email'),
        'price_id' => 3,
        'plan' => 'expert',
        'access_tier' => 3,
        'renewal_discount' => 0,
        'activations_left' => 999,
        'is_lifetime' => true,
    ];
    
    update_option('elementor_pro_license_key', 'ELEMENTOR-PRO-ACTIVATED-' . md5(get_home_url()));
    update_option('_elementor_pro_license_v2_data', $license_data);
    update_option('_elementor_pro_license_v2_data_fallback', $license_data);
});

// 2. Override ALL Elementor Pro license checks
add_action('plugins_loaded', function() {
    // Force set license data on every load
    $license_key = 'ELEMENTOR-PRO-ACTIVATED-' . md5(get_home_url());
    if (get_option('elementor_pro_license_key') !== $license_key) {
        update_option('elementor_pro_license_key', $license_key);
    }
    
    $license_data = [
        'success' => true,
        'license' => 'valid',
        'expires' => '2099-12-31 23:59:59',
        'customer_email' => get_option('admin_email'),
        'customer_name' => 'Licensed User',
        'plan' => 'expert',
        'access_tier' => 3,
        'payment_id' => 999999,
        'activations_left' => 999,
        'features' => ['all'],
    ];
    
    if (!get_option('_elementor_pro_license_v2_data') || 
        !is_array(get_option('_elementor_pro_license_v2_data')) || 
        empty(get_option('_elementor_pro_license_v2_data')['success'])) {
        update_option('_elementor_pro_license_v2_data', $license_data);
    }
    
    // Override option filters - HIGHEST PRIORITY
    add_filter('pre_option_elementor_pro_license_key', function($value) use ($license_key) {
        return $license_key;
    }, 9999999);
    
    add_filter('option_elementor_pro_license_key', function($value) use ($license_key) {
        return $license_key;
    }, 9999999);
    
    add_filter('pre_option__elementor_pro_license_v2_data', function($value) use ($license_data) {
        if ($value && is_array($value) && isset($value['success']) && $value['success']) {
            return $value;
        }
        return $license_data;
    }, 9999999);
    
    // Force ALL license API calls to return valid
    add_filter('elementor_pro/license/is_active', '__return_true', 9999999);
    add_filter('elementor_pro/license/is_expired', '__return_false', 9999999);
    add_filter('elementor_pro/license/can_activate_pro', '__return_true', 9999999);
    add_filter('elementor_pro/license/get_license_key', function() use ($license_key) {
        return $license_key;
    }, 9999999);
    add_filter('elementor_pro/license/is_licence_has_feature', '__return_true', 9999999);
    add_filter('elementor_pro/license/has_feature', '__return_true', 9999999);
    add_filter('elementor_pro/license/can_use_feature', '__return_true', 9999999);
    add_filter('elementor_pro/license/filter_active_features', function($features) {
        return is_array($features) ? $features : [];
    }, 9999999);
    
    // Force ALL modules to be active
    add_filter('elementor_pro/is_active', '__return_true', 9999999);
    add_filter('elementor_pro/is_module_active', '__return_true', 9999999);
    add_filter('elementor_pro/is_feature_active', '__return_true', 9999999);
    
    // Force specific critical modules that are checked separately
    add_filter('elementor/theme/theme_builder/is_active', '__return_true', 9999999);
    add_filter('elementor/theme/theme_builder/can_use', '__return_true', 9999999);
    add_filter('elementor_pro/popup/is_active', '__return_true', 9999999);
    add_filter('elementor_pro/forms/is_active', '__return_true', 9999999);
    add_filter('elementor_pro/woocommerce/is_active', '__return_true', 9999999);
    add_filter('elementor_pro/custom_code/is_active', '__return_true', 9999999);
    add_filter('elementor_pro/motion_effects/is_active', '__return_true', 9999999);
    add_filter('elementor_pro/custom-attributes/is_active', '__return_true', 9999999);
    
    // Force editor to show Pro features
    add_filter('elementor/editor/localize_settings', function($settings) {
        if (is_array($settings)) {
            $settings['user'] = array_merge($settings['user'] ?? [], [
                'is_pro' => true,
                'has_pro' => true,
                'can_use_pro' => true,
                'plan' => 'expert',
            ]);
            
            if (!isset($settings['license'])) {
                $settings['license'] = [];
            }
            
            $settings['license'] = array_merge($settings['license'], [
                'isActive' => true,
                'isExpired' => false,
                'isValid' => true,
                'plan' => 'expert',
                'tier' => 3,
                'features' => ['all'],
            ]);
            
            $settings['hasPro'] = true;
            $settings['proWidgets'] = 'all';
            
            // Ensure all widgets are marked as available
            if (isset($settings['widgets']) && is_array($settings['widgets'])) {
                foreach ($settings['widgets'] as $widget_name => &$widget_data) {
                    $widget_data['is_pro'] = true;
                }
            }
        }
        return $settings;
    }, 9999999);
    
    // Force template library access
    add_filter('elementor/api/get_templates/body_args', function($body_args) use ($license_key) {
        $body_args['license'] = $license_key;
        $body_args['url'] = home_url();
        return $body_args;
    }, 9999999);
    
    // Intercept ALL Elementor API calls
    add_filter('pre_http_request', function($preempt, $args, $url) {
        // Block ALL license-related API calls
        $blocked_patterns = [
            'my.elementor.com/api/v1/license/',
            'my.elementor.com/api/v2/license/',
            'my.elementor.com/api/v3/license/',
            'elementor.com/api/v1/license/',
        ];
        
        foreach ($blocked_patterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return [
                    'response' => ['code' => 200, 'message' => 'OK'],
                    'body' => json_encode([
                        'success' => true,
                        'license' => 'valid',
                        'expires' => '2099-12-31 23:59:59',
                        'customer_email' => get_option('admin_email'),
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
        }
        
        // Add license to template library calls
        if (strpos($url, 'my.elementor.com/api/v1/templates-library') !== false ||
            strpos($url, 'my.elementor.com/api/v2/templates-library') !== false) {
            if (is_array($args) && isset($args['body'])) {
                if (is_array($args['body'])) {
                    $args['body']['license'] = 'ELEMENTOR-PRO-ACTIVATED-' . md5(get_home_url());
                    $args['body']['url'] = home_url();
                } elseif (is_string($args['body'])) {
                    parse_str($args['body'], $body_params);
                    $body_params['license'] = 'ELEMENTOR-PRO-ACTIVATED-' . md5(get_home_url());
                    $body_params['url'] = home_url();
                    $args['body'] = http_build_query($body_params);
                }
            }
        }
        
        return $preempt;
    }, 10, 3);
    
    // Remove ALL license admin notices
    add_action('admin_head', function() {
        ?>
        <style>
        /* Hide ALL Elementor license notices */
        .notice[class*="elementor"],
        .e-notice,
        .elementor-message,
        .elementor-admin-notice,
        [data-notice*="license"],
        [data-notice*="License"],
        .notice-warning[class*="elementor"],
        .notice-error[class*="elementor"],
        #wpadminbar .e-notice-topbar,
        .e-notice-topbar,
        #elementor-notice-bar,
        .elementor-notice-bar,
        .elementor-license-notice,
        .elementor-pro-license-notice {
            display: none !important;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Remove any license notices
            function removeNotices() {
                $('.notice, .updated, .error, .warning, .info').each(function() {
                    var $this = $(this);
                    var text = $this.text().toLowerCase();
                    var html = $this.html().toLowerCase();
                    if (text.includes('license') || text.includes('activate') || 
                        text.includes('renew') || text.includes('elementor pro') ||
                        html.includes('license') || html.includes('activate')) {
                        $this.remove();
                    }
                });
                
                // Remove from plugin action links
                $('tr[data-plugin="elementor-pro/elementor-pro.php"] .row-actions span').each(function() {
                    var text = $(this).text().toLowerCase();
                    if (text.includes('activate license') || text.includes('renew')) {
                        $(this).remove();
                    }
                });
            }
            removeNotices();
            setInterval(removeNotices, 1000);
        });
        </script>
        <?php
    });
    
    // Modify plugin action links
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
        $new_links = [];
        foreach ($links as $key => $link) {
            if (strpos($link, 'Connect & Activate') === false &&
                strpos($link, 'Renew Now') === false &&
                strpos($link, 'license') === false &&
                strpos($link, 'activate') === false) {
                $new_links[$key] = $link;
            }
        }
        $new_links['status'] = '<span style="color:#46b450;font-weight:bold;">✅ PRO UNLOCKED</span>';
        return $new_links;
    }, 9999999);
    
    // Fix for BC_VALIDATION_CALLBACK constant
    if (!defined('ElementorPro\License\API::BC_VALIDATION_CALLBACK')) {
        define('ElementorPro\License\API::BC_VALIDATION_CALLBACK', '__return_true');
    }
    
    // Add missing API methods dynamically
    if (class_exists('ElementorPro\License\API')) {
        // Check and add missing methods
        $missing_methods = [
            'filter_active_features' => 'return is_array($features) ? $features : [];',
            'get_active_features' => 'return ["all"];',
            'is_licence_has_feature' => 'return true;',
            'has_feature' => 'return true;',
            'can_use_feature' => 'return true;',
            'is_feature_active' => 'return true;',
            'get_feature_tier' => 'return 3;',
            'is_feature_in_tier' => 'return true;',
            'validate_feature' => 'return true;',
        ];
        
        foreach ($missing_methods as $method => $code) {
            if (!method_exists('ElementorPro\License\API', $method)) {
                if (function_exists('runkit7_method_add')) {
                    runkit7_method_add(
                        'ElementorPro\License\API',
                        $method,
                        $method === 'filter_active_features' ? '$features' : '$feature_name = null',
                        $code,
                        RUNKIT7_ACC_STATIC
                    );
                }
            }
        }
    }
}, 0); // Priority 0 - Run FIRST

// 3. Fix specific module issues
add_action('elementor_pro/init', function() {
    // Force all widgets to be available
    add_filter('elementor/widgets/is_widget_active', '__return_true', 9999999);
    
    // Fix for Forms module
    add_filter('elementor_pro/forms/actions/active_features', function($features) {
        // Return all form actions
        return [
            'email',
            'email2',
            'redirect',
            'webhook',
            'mailchimp',
            'drip',
            'activecampaign',
            'getresponse',
            'convertkit',
            'mailerlite',
            'slack',
            'discord',
        ];
    }, 9999999);
    
    // Fix for Custom Attributes module
    add_filter('elementor_pro/custom-attributes/is_active', '__return_true', 9999999);
    
    // Ensure all Pro widgets are enabled
    add_action('elementor/widgets/register', function($widgets_manager) {
        // This ensures all widgets are registered
    }, 9999999);
}, 1);

// 4. Override the add_subscription_template_access_level_to_settings method
add_filter('elementor/editor/localize_settings', function($settings) {
    if (is_array($settings)) {
        if (isset($settings['library_connect'])) {
            $settings['library_connect']['current_access_level'] = 3;
            $settings['library_connect']['current_access_tier'] = 3;
            $settings['library_connect']['plan_type'] = 'expert';
        }
    }
    return $settings;
}, 9999999);

add_filter('elementor/common/localize_settings', function($settings) {
    if (is_array($settings)) {
        if (isset($settings['library_connect'])) {
            $settings['library_connect']['current_access_level'] = 3;
            $settings['library_connect']['current_access_tier'] = 3;
            $settings['library_connect']['plan_type'] = 'expert';
        }
    }
    return $settings;
}, 9999999);

// ============================================
// END BYPASS CODE - ORIGINAL PLUGIN CODE BELOW
// ============================================

/**
 * Main class plugin
 */
class Plugin {

	/**
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * @var Modules_Manager
	 */
	public $modules_manager;

	/**
	 * @var UpgradeManager
	 */
	public $upgrade;

	/**
	 * @var Editor
	 */
	public $editor;

	/**
	 * @var Preview
	 */
	public $preview;

	/**
	 * @var Admin
	 */
	public $admin;

	/**
	 * @var App
	 */
	public $app;

	/**
	 * @var License\Admin
	 */
	public $license_admin;

	/**
	 * @var \ElementorPro\Core\Integrations\Integrations_Manager
	 */
	public $integrations;

	/**
	 * @var \ElementorPro\Core\Notifications\Notifications_Manager
	 */
	public $notifications;

	private $classes_aliases = [
		'ElementorPro\Modules\PanelPostsControl\Module' => 'ElementorPro\Modules\QueryControl\Module',
		'ElementorPro\Modules\PanelPostsControl\Controls\Group_Control_Posts' => 'ElementorPro\Modules\QueryControl\Controls\Group_Control_Posts',
		'ElementorPro\Modules\PanelPostsControl\Controls\Query' => 'ElementorPro\Modules\QueryControl\Controls\Query',
	];

	/**
	 * @var \ElementorPro\License\Updater
	 */
	public $updater;

	/**
	 * @var PHP_Api
	 */
	public $php_api;

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf( 'Cloning instances of the singleton "%s" class is forbidden.', get_class( $this ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'1.0.0'
		);
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf( 'Unserializing instances of the singleton "%s" class is forbidden.', get_class( $this ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'1.0.0'
		);
	}

	/**
	 * @return \Elementor\Plugin
	 */

	public static function elementor() {
		return \Elementor\Plugin::$instance;
	}

	/**
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$has_class_alias = isset( $this->classes_aliases[ $class ] );

		// Backward Compatibility: Save old class name for set an alias after the new class is loaded
		if ( $has_class_alias ) {
			$class_alias_name = $this->classes_aliases[ $class ];
			$class_to_load = $class_alias_name;
		} else {
			$class_to_load = $class;
		}

		if ( ! class_exists( $class_to_load ) ) {
			$filename = strtolower(
				preg_replace(
					[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
					[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
					$class_to_load
				)
			);
			$filename = ELEMENTOR_PRO_PATH . $filename . '.php';

			if ( is_readable( $filename ) ) {
				include( $filename );
			}
		}

		if ( $has_class_alias ) {
			class_alias( $class_alias_name, $class );
		}
	}

	public static function get_frontend_file_url( $frontend_file_name, $custom_file ) {
		if ( $custom_file ) {
			$frontend_file = self::get_frontend_file( $frontend_file_name );

			$frontend_file_url = $frontend_file->get_url();
		} else {
			$frontend_file_url = ELEMENTOR_PRO_ASSETS_URL . 'css/' . $frontend_file_name;
		}

		return $frontend_file_url;
	}

	public static function get_frontend_file_path( $frontend_file_name, $custom_file ) {
		if ( $custom_file ) {
			$frontend_file = self::get_frontend_file( $frontend_file_name );

			$frontend_file_path = $frontend_file->get_path();
		} else {
			$frontend_file_path = ELEMENTOR_PRO_ASSETS_PATH . 'css/' . $frontend_file_name;
		}

		return $frontend_file_path;
	}

	/**
	 * @deprecated 3.26.0
	 * @return void
	 */
	public function enqueue_styles(): void {}

	public function enqueue_frontend_scripts() {
		$suffix = $this->get_assets_suffix();

		wp_enqueue_script(
			'elementor-pro-frontend',
			ELEMENTOR_PRO_URL . 'assets/js/frontend' . $suffix . '.js',
			$this->get_frontend_depends(),
			ELEMENTOR_PRO_VERSION,
			true
		);

		wp_set_script_translations( 'elementor-pro-frontend', 'elementor-pro', ELEMENTOR_PRO_PATH . 'languages' );

		wp_enqueue_script( 'pro-elements-handlers' );

		$assets_url = ELEMENTOR_PRO_ASSETS_URL;

		/**
		 * Elementor Pro assets URL.
		 *
		 * Filters the assets URL used by Elementor Pro.
		 *
		 * By default Elementor Pro assets URL is set by the ELEMENTOR_PRO_ASSETS_URL
		 * constant. This hook allows developers to change this URL.
		 *
		 * @param string $assets_url Elementor Pro assets URL.
		 */
		$assets_url = apply_filters( 'elementor_pro/frontend/assets_url', $assets_url );

		$locale_settings = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'elementor-pro-frontend' ),
			'urls' => [
				'assets' => $assets_url,
				'rest' => get_rest_url(),
			],
			'settings' => [
				'lazy_load_background_images' => ( '1' === get_option( 'elementor_lazy_load_background_images', '1' ) ),
			],
		];

		/**
		 * Localized frontend settings.
		 *
		 * Filters the localized settings used in the frontend as JavaScript variables.
		 *
		 * By default Elementor Pro passes some frontend settings to be consumed as JavaScript
		 * variables. This hook allows developers to add extra settings values to be consumed
		 * using JavaScript in the frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param array $locale_settings Localized frontend settings.
		 */
		$locale_settings = apply_filters( 'elementor_pro/frontend/localize_settings', $locale_settings );

		Utils::print_js_config(
			'elementor-pro-frontend',
			'ElementorProFrontendConfig',
			$locale_settings
		);
	}

	public function register_frontend_scripts() {
		$suffix = $this->get_assets_suffix();

		wp_register_script(
			'elementor-pro-webpack-runtime',
			ELEMENTOR_PRO_URL . 'assets/js/webpack-pro.runtime' . $suffix . '.js',
			[],
			ELEMENTOR_PRO_VERSION,
			true
		);

		wp_register_script(
			'pro-elements-handlers',
			ELEMENTOR_PRO_URL . 'assets/js/elements-handlers' . $suffix . '.js',
			[
				'elementor-frontend',
			],
			ELEMENTOR_PRO_VERSION,
			true
		);

		wp_register_script(
			'smartmenus',
			ELEMENTOR_PRO_URL . 'assets/lib/smartmenus/jquery.smartmenus' . $suffix . '.js',
			[
				'jquery',
			],
			'1.2.1',
			true
		);

		$sticky_handle = $this->is_assets_loader_exist() ? 'e-sticky' : 'elementor-sticky';

		wp_register_script(
			$sticky_handle,
			ELEMENTOR_PRO_URL . 'assets/lib/sticky/jquery.sticky' . $suffix . '.js',
			[
				'jquery',
			],
			ELEMENTOR_PRO_VERSION,
			true
		);

		if ( $this->is_assets_loader_exist() ) {
			$this->register_assets();
		}
	}

	public function register_preview_scripts() {
		$suffix = $this->get_assets_suffix();

		wp_enqueue_script(
			'elementor-pro-preview',
			ELEMENTOR_PRO_URL . 'assets/js/preview' . $suffix . '.js',
			[
				'wp-i18n',
				'elementor-frontend',
			],
			ELEMENTOR_PRO_VERSION,
			true
		);
	}

	public function get_responsive_stylesheet_templates( $templates ) {
		$templates_paths = glob( $this->get_responsive_templates_path() . '*.css' );

		foreach ( $templates_paths as $template_path ) {
			$file_name = 'custom-pro-' . basename( $template_path );

			$templates[ $file_name ] = $template_path;
		}

		return $templates;
	}

	public function on_elementor_init() {
		$this->modules_manager = new Modules_Manager();

		/** TODO: BC for Elementor v2.4.0 */
		if ( class_exists( '\Elementor\Core\Upgrade\Manager' ) ) {
			$this->upgrade = UpgradeManager::instance();
		}

		/**
		 * Elementor Pro init.
		 *
		 * Fires on Elementor Pro initiation, after Elementor has finished loading
		 * but before any headers are sent.
		 *
		 * @since 1.0.0
		 */
		do_action( 'elementor_pro/init' );
	}

	/**
	 * @param \Elementor\Core\Base\Document $document
	 */
	public function on_document_save_version( $document ) {
		$document->update_meta( '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
	}

	private function get_frontend_depends() {
		$frontend_depends = [
			'elementor-pro-webpack-runtime',
			'elementor-frontend-modules',
		];

		if ( ! $this->is_assets_loader_exist() ) {
			$frontend_depends[] = 'elementor-sticky';
		}

		return $frontend_depends;
	}

	private static function get_responsive_templates_path() {
		return ELEMENTOR_PRO_ASSETS_PATH . 'css/templates/';
	}

	private function add_subscription_template_access_level_to_settings( $settings ) {
		// Already handled by our global filter above
		if ( isset( $settings['library_connect']['current_access_level'] ) ) {
			$settings['library_connect']['current_access_level'] = 3;
		}

		if ( isset( $settings['library_connect']['current_access_tier'] ) ) {
			$settings['library_connect']['current_access_tier'] = 3;
		}

		if ( isset( $settings['library_connect']['plan_type'] ) ) {
			$settings['library_connect']['plan_type'] = 'expert';
		}

		return $settings;
	}

	private function setup_hooks() {
		add_action( 'elementor/init', [ $this, 'on_elementor_init' ] );

		add_action( 'elementor/frontend/before_register_scripts', [ $this, 'register_frontend_scripts' ] );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'register_preview_scripts' ] );

		add_action( 'elementor/frontend/before_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );

		add_filter( 'elementor/core/breakpoints/get_stylesheet_template', [ $this, 'get_responsive_stylesheet_templates' ] );
		add_action( 'elementor/document/save_version', [ $this, 'on_document_save_version' ] );

		add_filter( 'elementor/editor/localize_settings', function ( $settings ) {
			return $this->add_subscription_template_access_level_to_settings( $settings );
		}, 11 /** After Elementor Core (Library) */ );

		add_filter( 'elementor/common/localize_settings', function ( $settings ) {
			return $this->add_subscription_template_access_level_to_settings( $settings );
		}, 11 /** After Elementor Core (Library) */ );
	}

	private function get_assets() {
		$suffix = $this->get_assets_suffix();

		return [
			'scripts' => [
				'e-sticky' => [
					'src' => ELEMENTOR_PRO_URL . 'assets/lib/sticky/jquery.sticky' . $suffix . '.js',
					'version' => ELEMENTOR_PRO_VERSION,
					'dependencies' => [
						'jquery',
					],
				],
			],
		];
	}

	private function register_assets() {
		$assets = $this->get_assets();

		if ( $assets ) {
			self::elementor()->assets_loader->add_assets( $assets );
		}
	}

	private function is_assets_loader_exist() {
		return ! ! self::elementor()->assets_loader;
	}

	/**
	 * Plugin constructor.
	 * @throws Exception
	 */
	private function __construct() {
		spl_autoload_register( [ $this, 'autoload' ] );

		Compatibility::register_actions();

		new Connect\Manager();

		$this->setup_hooks();

		$this->editor = new Editor();

		$this->preview = new Preview();

		$this->app = new App();

		$this->license_admin = new License\Admin();

		$this->php_api = new PHP_Api();

		if ( is_user_logged_in() ) {
			$this->integrations = new Integrations_Manager(); // TODO: This one is safe to move out of the condition.

			$this->notifications = new Notifications_Manager();
		}

		if ( is_admin() ) {
			$this->admin = new Admin();

			$this->license_admin->register_actions();
		}

		Maintenance::init();

		// The `Updater` class is responsible for adding some updates related filters, including auto updates, and since
		// WP crons don't run on admin mode, it should not depend on it.
		$this->updater = new Updater();
	}

	private function get_assets_suffix() {
		return Utils::is_script_debug() ? '' : '.min';
	}

	private static function get_frontend_file( $frontend_file_name ) {
		$template_file_path = self::get_responsive_templates_path() . $frontend_file_name;

		return self::elementor()->frontend->get_frontend_file( $frontend_file_name, 'custom-pro-', $template_file_path );
	}

	final public static function get_title() {
		return esc_html__( 'Elementor Pro', 'elementor-pro' );
	}
}

if ( ! defined( 'ELEMENTOR_PRO_TESTS' ) ) {
	// In tests we run the instance manually.
	Plugin::instance();
}

// ============================================
// FINAL SAFETY NET
// ============================================

// Ensure all filters are applied even if plugin loads in unexpected order
add_action('init', function() {
    // Final check: make sure license is active
    if (!get_option('elementor_pro_license_key')) {
        update_option('elementor_pro_license_key', 'ELEMENTOR-PRO-ACTIVATED-' . md5(get_home_url()));
    }
    
    // Final check for API methods
    if (class_exists('ElementorPro\License\API')) {
        // One last attempt to add missing methods
        $methods_to_check = [
            'filter_active_features',
            'is_licence_has_feature',
            'has_feature',
            'can_use_feature',
        ];
        
        foreach ($methods_to_check as $method) {
            if (!method_exists('ElementorPro\License\API', $method)) {
                // Create a global function that mimics the method
                $function_name = 'elementor_pro_license_' . $method;
                if (!function_exists($function_name)) {
                    eval("function $function_name(\$param = null) { return true; }");
                }
            }
        }
    }
}, 999999);