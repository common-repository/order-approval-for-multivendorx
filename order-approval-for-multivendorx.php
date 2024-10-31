<?php

/**
 *
 * @link              https://sevengits.com/plugin/order-approval-for-multivendorx-pro/
 * @since             1.0.0
 * @package           Order_Approval_For_Multivendorx
 *
 * @wordpress-plugin
 * Plugin Name:       Order Approval for MultiVendorX
 * Plugin URI:        https://sevengits.com/plugin/order-approval-for-multivendorx/
 * Description:       The Order Approval for MultiVendorX plugin enables vendors to review and either accept or reject customer orders before any payment is made.
 * Version:           1.0.1
 * Author:            Sevengits
 * Author URI:        https://sevengits.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       order-approval-for-multivendorx
 * Domain Path:       /languages
 * WC requires at least: 3.7
 * WC tested up to:      8.1
 * MultiVendorX tested up to:	4.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('SGOAMVX_VERSION', '1.0.1');


if (!defined('SGOAMVX_PLUGIN_PATH')) {
	define('SGOAMVX_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('SGOAMVX_PLUGIN_BASENAME')) {
	define('SGOAMVX_PLUGIN_BASENAME', plugin_basename(__FILE__));
}


if (!class_exists('\OAMVX\Reviews\Notice')) {
	require SGOAMVX_PLUGIN_PATH . 'includes/packages/plugin-review/notice.php';
	# code...
}
function oamvx_is_depencies_deactivated()
{
	/**
	 * disable if depencies not activate
	 */
	$depended_plugins = array(
		array(
			'plugins' => array(
				'Sg Order Approval for Woocommerce' => 'order-approval-woocommerce/order-approval-woocommerce.php',
				'Sg Order Approval for Woocommerce Pro' => 'order-approval-woocommerce-pro/order-approval-woocommerce-pro.php'
			), 'links' => array(
				'free' => 'https://wordpress.org/plugins/order-approval-woocommerce/',
				'pro' => 'https://sevengits.com/plugin/order-approval-woocommerce-pro'
			)
		),
		array(
			'plugins' => array(
				'MultiVendorX - MultiVendor Marketplace Solution For WooCommerce' => 'dc-woocommerce-multi-vendor/dc_product_vendor.php'
			), 'links' => array('free' => 'https://wordpress.org/plugins/dc-woocommerce-multi-vendor/')
		),
		array(
			'plugins' => array(
				'WooCommerce' => 'woocommerce/woocommerce.php'
			), 'links' => array('free' => 'https://wordpress.org/plugins/woocommerce/')
		)

	);
	$message = __('The following plugins are required for <b>Order Approval for MultiVendorX</b> plugin to work. Please ensure that they are activated: ', 'order-approval-for-multivendorx');
	$is_disabled = false;
	foreach ($depended_plugins as $key => $dependency) {
		$dep_plugin_name = array_keys($dependency['plugins']);
		$dep_plugin = array_values($dependency['plugins']);
		if (count($dep_plugin) > 1) {
			if (!in_array($dep_plugin[0], apply_filters('active_plugins', get_option('active_plugins'))) && !in_array($dep_plugin[1], apply_filters('active_plugins', get_option('active_plugins')))) {
				$class = 'notice notice-error is-dismissible';
				$is_disabled = true;
				if (isset($dependency['links'])) {
					# code...
					$message .= '<br/> <a href="' . $dependency['links']['free'] . '" target="_blank" ><b>' . $dep_plugin_name[0] . '</b></a> Or <a href="' . $dependency['links']['pro'] . '" target="_blank" ><b>' . $dep_plugin_name[1] . '</b></a>';
				} else {
					$message .= "<br/> <b> $dep_plugin_name[0] </b> Or <b> $dep_plugin_name[1] . </b>";
				}
			}
		} else {
			if (!in_array($dep_plugin[0], apply_filters('active_plugins', get_option('active_plugins')))) {
				$class = 'notice notice-error is-dismissible';
				$is_disabled = true;
				if (isset($dependency['links'])) {
					$message .= '<br/> <a href="' . $dependency['links']['free'] . '" target="_blank" ><b>' . $dep_plugin_name[0] . '</b></a>';
				} else {
					$message .= "<br/><b>$dep_plugin_name[0]</b>";
				}
			}
		}
	}
	if ($is_disabled) {

		if (!defined('SGOAMVX_DISABLED')) {
			define('SGOAMVX_DISABLED', true);
		}
		printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
	}

	/**
	 * review notice for collect user experience
	 */
	if (class_exists('\OAMVX\Reviews\Notice')) {
		// delete_site_option('prefix_reviews_time'); // FOR testing purpose only. this helps to show message always
		$message = sprintf(__("Hello! Seems like you have been using %s for a while – that’s awesome! Could you please do us a BIG favor and give it a 5-star rating on WordPress? This would boost our motivation and help us spread the word.", 'order-approval-for-multivendorx'), "<b>" . get_plugin_data(__FILE__)['Name'] . "</b>");
		$actions = array(
			'review'  => __('Ok, you deserve it', 'order-approval-for-multivendorx'),
			'later'   => __('Nope, maybe later I', 'order-approval-for-multivendorx'),
			'dismiss' => __('already did', 'order-approval-for-multivendorx'),
		);
		$notice = \OAMVX\Reviews\Notice::get(
			'order-approval-for-multivendorx',
			get_plugin_data(__FILE__)['Name'],
			array(
				'days'          => 7,
				'message'       => $message,
				'action_labels' => $actions,
				'prefix' => "prefix"
			)
		);

		// Render notice.
		$notice->render();
	}
}
add_action('admin_notices', 'oamvx_is_depencies_deactivated');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-order-approval-for-multivendorx-activator.php
 */
function oamvx_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-order-approval-for-multivendorx-activator.php';
	Order_Approval_For_Multivendorx_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-order-approval-for-multivendorx-deactivator.php
 */
function oamvx_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-order-approval-for-multivendorx-deactivator.php';
	Order_Approval_For_Multivendorx_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'oamvx_activate');
register_deactivation_hook(__FILE__, 'oamvx_deactivate');

require SGOAMVX_PLUGIN_PATH . 'plugin-deactivation-survey/deactivate-feedback-form.php';
add_filter('sgits_deactivate_feedback_form_plugins', 'oamvx_deactivate_feedback');
function oamvx_deactivate_feedback($plugins)
{
	$plugins[] = (object)array(
		'slug'		=> 'order-approval-for-multivendorx',
		'version'	=> SGOAMVX_VERSION
	);
	return $plugins;
}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-order-approval-for-multivendorx.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function oamvx_run()
{
	$plugin = new Order_Approval_For_Multivendorx();
	$plugin->run();
}
oamvx_run();
