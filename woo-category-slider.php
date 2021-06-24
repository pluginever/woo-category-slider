<?php
/**
 * Plugin Name: WooCommerce Category Slider
 * Plugin URI:  https://pluginever.com/woocommerce-category-slider
 * Description: Showcase product categories in the most appealing way. Create an instant impression & trigger purchase intention.
 * Version:     4.1.0
 * Author:      pluginever
 * Author URI:  http://pluginever.com
 * Donate link: https://pluginever.com/contact
 * License:     GPLv2+
 * Text Domain: woo-category-slider-by-pluginever
 * Domain Path: /i18n/languages/
 * Requires at least: 4.4
 * Tested up to: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 4.9.1
 */

/**
 * Copyright (c) 2018 manikmist09 (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main initiation class
 *
 * @since 1.0.0
 */
class Woocommerce_Category_Slider {
	/**
	 * WCSerialNumbers version.
	 *
	 * @var string
	 */
	public $version = '4.1.0';

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $min_php = '5.6';

	/**
	 * admin notices
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $notices = array();


	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var Woocommerce_Category_Slider
	 */
	protected static $instance = null;

	/**
	 * @var \WC_Category_Slider_Elements
	 */
	public $elements;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $api_url;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'WP WooCommerce Category Slider';

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access protected
	 * @return void
	 */

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woo-category-slider-by-pluginever' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 */

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woo-category-slider-by-pluginever' ), '1.0.0' );
	}

	/**
	 * WCSerialNumbers constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		add_action( 'init', array( $this, 'localization_setup' ) );
		add_action( 'admin_init', array( $this, 'init_update' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		if ( $this->is_plugin_compatible() ) {
			$this->define_constants();
			$this->includes();
			$this->elements = new WC_Category_Slider_Elements();
			$this->tracker  = new WC_Category_Slider_Tracker();
		}
	}

	/**
	 * Activate plugin
	 *
	 * @return void
	 * @since 4.0.0
	 *
	 */
	public function activate() {
		if ( false == get_option( 'woocatslider_install_date' ) ) {
			update_option( 'woocommerce_category_slider_install_date', current_time( 'timestamp' ) );
		} else {
			update_option( 'woocommerce_category_slider_install_date', get_option( 'woocatslider_install_date' ) );
			delete_option( 'woocatslider_install_date' );
		}

		update_option( 'wc_category_slider_version', $this->version );
	}

	/**
	 * Checks the server environment and other factors and deactivates plugins as necessary.
	 *
	 * @since 1.0.0
	 * @internal
	 *
	 */
	public function activation_check() {

		if ( ! version_compare( PHP_VERSION, $this->min_php, '>=' ) ) {

			deactivate_plugins( plugin_basename( __FILE__ ) );

			$message = sprintf( __( '%s could not be activated The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woo-category-slider-by-pluginever' ), $this->plugin_name, $this->min_php, PHP_VERSION );
			wp_die( $message );
		}

	}

	/**
	 * Determines if the plugin compatible.
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	protected function is_plugin_compatible() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$message = sprintf( __( '<strong>WooCommerce Category Slider</strong> requires <strong>WooCommerce</strong> installed and activated. Please install %s WooCommerce. %s', 'woo-category-slider-by-pluginever' ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' );
			$this->add_notice( 'error', $message );

			return false;
		}

		return true;
	}

	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @param string $class the notice class
	 * @param string $message the notice message body
	 *
	 * @since 1.0.0
	 *
	 */
	public function add_notice( $class, $message ) {

		$notices = get_option( sanitize_key( $this->plugin_name ), [] );
		if ( is_string( $message ) && is_string( $class ) && ! wp_list_filter( $notices, array( 'message' => $message ) ) ) {

			$notices[] = array(
				'message' => $message,
				'class'   => $class
			);

			update_option( sanitize_key( $this->plugin_name ), $notices );
		}

	}


	/**
	 * Displays any admin notices added
	 *
	 * @since 1.0.0
	 * @internal
	 *
	 */
	public function admin_notices() {
		$notices = (array) array_merge( $this->notices, get_option( sanitize_key( $this->plugin_name ), [] ) );
		foreach ( $notices as $notice_key => $notice ) :
			?>
            <div class="notice notice-<?php echo sanitize_html_class( $notice['class'] ); ?>">
                <p><?php echo wp_kses( $notice['message'], array(
						'a'      => array( 'href' => array() ),
						'strong' => array()
					) ); ?></p>
            </div>
			<?php
			update_option( sanitize_key( $this->plugin_name ), [] );
		endforeach;
	}

	/**
	 * Return plugin version.
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 **/
	public function get_version() {
		return $this->version;
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 * @since 4.0.9
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 * @since 4.0.9
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 * @since 4.0.9
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
	}


	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'woo-category-slider-by-pluginever', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * Determines if the pro version installed.
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function is_pro_installed() {
		$status = false;
		if ( is_plugin_active( 'wc-category-slider-pro/wc-category-slider-pro.php' ) || is_plugin_active( 'woocommerce-category-slider-pro/wc-category-slider-pro.php' ) ) {
			$status = true;
		}

		return $status;
	}

	/**
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="https://www.pluginever.com/plugins/woocommerce-category-slider-pro/">' . __( 'Documentation', 'woo-category-slider-by-pluginever' ) . '</a>';
		if ( ! $this->is_pro_installed() ) {
			$links[] = '<a href="https://www.pluginever.com/plugins/woocommerce-category-slider-pro/" style="color: red;font-weight: bold;" target="_blank">' . __( 'Upgrade to PRO', 'woo-category-slider-by-pluginever' ) . '</a>';
		}

		return $links;
	}

	public function init_update() {

		require_once( dirname( __FILE__ ) . '/includes/class-upgrades.php' );

		$updater = new WC_Category_Slider_Updates();
		if ( $updater->needs_update() ) {
			$updater->perform_updates();
		}

	}

	/**
	 * define plugin constants
	 *
	 * since 1.0.0
	 */
	private function define_constants() {
		define( 'WC_CAT_SLIDER_VERSION', $this->version );
		define( 'WC_CAT_SLIDER_FILE', __FILE__ );
		define( 'WC_CAT_SLIDER_PATH', dirname( WC_CAT_SLIDER_FILE ) );
		define( 'WC_CAT_SLIDER_INCLUDES', WC_CAT_SLIDER_PATH . '/includes' );
		define( 'WC_CAT_SLIDER_URL', plugins_url( '', WC_CAT_SLIDER_FILE ) );
		define( 'WC_CAT_SLIDER_ASSETS_URL', WC_CAT_SLIDER_URL . '/assets' );
		define( 'WC_CAT_SLIDER_TEMPLATES', WC_CAT_SLIDER_PATH . '/templates' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		require_once( WC_CAT_SLIDER_INCLUDES . '/class-elements.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/core-functions.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/hook-functions.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/class-shortcode.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/class-cpt.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/scripts-functions.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/admin/metabox-functions.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/class-insights.php' );
		require_once( WC_CAT_SLIDER_INCLUDES . '/class-tracker.php' );
		//admin
		if ( ! $this->is_pro_installed() && is_admin() ) {
			require_once( WC_CAT_SLIDER_INCLUDES . '/admin/class-promotion.php' );
		}

	}


	/**
	 * Returns the plugin loader main instance.
	 *
	 * @return \Woocommerce_Category_Slider
	 * @since 1.0.0
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add plugin docs links in plugin row links
	 *
	 * @param mixed $links Links
	 * @param mixed $file File
	 *
	 * @return array
	 * @since 4.1.1
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {

			$row_meta = array(
				'docs' => '<a href="' . esc_url( apply_filters( 'wc_category_slider_docs_url', 'https://pluginever.com/docs/woocommerce-category-slider/' ) ) . '" aria-label="' . esc_attr__( 'View documentation', 'woo-category-slider-by-pluginever' ) . '">' . esc_html__( 'Docs', 'woo-category-slider-by-pluginever' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return $links;
	}
}

/**
 * Initialize the plugin
 *
 * @return Woocommerce_Category_Slider
 */
function wc_category_slider() {
	return Woocommerce_Category_Slider::instance();
}

// kick-off
wc_category_slider();
