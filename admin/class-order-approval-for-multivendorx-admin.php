<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sevengits.com
 * @since      1.0.0
 *
 * @package    Order_Approval_For_Multivendorx
 * @subpackage Order_Approval_For_Multivendorx/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Order_Approval_For_Multivendorx
 * @subpackage Order_Approval_For_Multivendorx/admin
 * @author     Sevengits <sevengits@gmail.com>
 */
class Order_Approval_For_Multivendorx_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function oamvx_enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Order_Approval_For_Multivendorx_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Order_Approval_For_Multivendorx_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if (!wp_style_is('sgits-admin-common-css', 'enqueued'))
		wp_enqueue_style('sgits-admin-common', plugin_dir_url(__FILE__) . 'css/common.css', array(), $this->version, 'all');
		
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/order-approval-for-multivendorx-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function oamvx_enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Order_Approval_For_Multivendorx_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Order_Approval_For_Multivendorx_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/order-approval-for-multivendorx-admin.js', array('jquery'), $this->version, false);
	}
	function oamvx_custom_order_actions($actions, $order_id)
	{
		// Get an instance of the WC_Order object (same as before)
		$order = wc_get_order($order_id);
		if ($order->has_status('waiting')) {
			$actions['order_approve'] = array(
				'url'   => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=pending&order_id=' . $order_id), 'woocommerce-mark-order-status'),
				"icon" => "ico-approve-icon action-icon",
				"title" => "Approve order",
			);
		}
		if (!$order->has_status('cancelled')) {
			$actions['order_reject'] = array(
				'url'   => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=cancelled&order_id=' . $order_id), 'woocommerce-mark-order-status'),
				"icon" => "ico-reject-icon action-icon",
				"title" => "Reject order",
			);
		}

		return $actions;
	}
	function oamvx_order_status_waiting($order_id)
	{
		global $WCMp;
		$suborder_details = get_mvx_suborders($order_id);
		foreach ($suborder_details as $key => $value) {
			$suborder_fetch = array(
				'ID'           => $value->get_id(),
				'post_status'   => 'wc-waiting',
			);
			wp_update_post($suborder_fetch);
		}
	}

	function oamvx_settings($settings)
	{

		$new_settings = array(
			array(
				'name' => __('Sg Order Approval for multivendorX', 'order-approval-for-multivendorx'),
				'type' => 'title',
				'id'   => 'sg_oawoo_addon_mvx_section',
				'desc' => __('Order approval for multivendorX settings', 'order-approval-for-multivendorx'),
			),
			array(
				'type' => 'sectionend',
				'name' => 'end_section',
				'id' => 'ppw_woo'
			)
		);
		$settings = array_merge($settings, $new_settings);
		return $settings;
	}

	/**
	 * 
	 * For array of data convert array of links and merge with exists array of links
	 * 
	 * $position = "start | end" 
	 */
	public function oamvx_merge_links($old_list, $new_list, $position = "end")
	{
		$settings = array();
		foreach ($new_list as $name => $item) {
			$target = (array_key_exists("target", $item)) ? $item['target'] : '';
			$classList = (array_key_exists("classList", $item)) ? $item['classList'] : '';
			$settings[$name] = sprintf('<a href="%s" target="' . $target . '" class="' . $classList . '">%s</a>', esc_url($item['link'], $this->plugin_name), esc_html__($item['name'], $this->plugin_name));
		}
		if ($position !== "start") {
			// push into $links array at the end
			return array_merge($old_list, $settings);
		} else {
			return array_merge($settings, $old_list);
		}
	}


	# below the plugin title in plugins page. add custom links at the begin of list
	public function oamvx_links_below_title_begin($links)
	{
		// if plugin is installed $links listed below the plugin title in plugins page. add custom links at the begin of list
		if (!defined('SGOAMVX_DISABLED')) {
			$link_list = array(
				'settings' => array(
					"name" => 'Settings',
					"classList" => "",
					"link" => admin_url('admin.php?page=wc-settings&tab=advanced&section=sg_order_tab#sg_oawoo_addon_mvx_section-description')
				)
			);
			return $this->oamvx_merge_links($links, $link_list, "start");
		}
		return $links;
	}

	public function oamvx_links_below_title_end($links)
	{
		// if plugin is installed $links listed below the plugin title in plugins page. add custom links at the end of list
		$link_list = array(
			'buy-pro' => array(
				"name" => 'Buy Premium',
				"classList" => "pro-purchase get-pro-link",
				"target" => '_blank',
				"link" => 'https://sevengits.com/plugin/sg-order-approval-multivendorx-pro/?utm_source=Wordpress&utm_medium=plugins-link&utm_campaign=Free-plugin'
			)
		);
		return $this->oamvx_merge_links($links, $link_list, "end");
	}

	function oamvx_plugin_description_below_end($links, $file)
	{
		if (strpos($file, 'order-approval-for-multivendorx.php') !== false) {
			$new_links = array(
				'pro' => array(
					"name" => 'Buy Premium',
					"classList" => "pro-purchase get-pro-link",
					"target" => '_blank',
					"link" => 'https://sevengits.com/plugin/sg-order-approval-multivendorx-pro/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin'
				),
				'docs' => array(
					"name" => 'Docs',
					"target" => '_blank',
					"link" => 'https://sevengits.com/docs/sg-order-approval-multivendorx-pro/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin'
				),
				'support' => array(
					"name" => 'Free Support',
					"target" => '_blank',
					"link" => 'https://wordpress.org/plugins/order-approval-for-multivendorx/'
				),

			);
			$links = $this->oamvx_merge_links($links, $new_links, "end");
		}

		return $links;
	}
}
