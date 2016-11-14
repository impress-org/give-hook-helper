<?php
/**
 * Plugin Name: Give - Hook Helper
 * Plugin URI:
 * Description: View all hooks on screen which fired for current screen.
 * Author: Ravinder Kumar
 * Author URI: https://ravinder.me
 * Version: 1.0
 * Text Domain:
 * Domain Path: /languages
 * GitHub Plugin URI:
 *
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Hook_Helper' ) ) :

	class Give_Hook_Helper {

		/**
		 * Check hook output status.
		 *
		 * @since  1.0
		 * @access private
		 * @var array array of hooks
		 */
		private $status;

		/**
		 * List of hooks.
		 *
		 * @since  1.0
		 * @access private
		 * @var array array of hooks
		 */
		private $all_hooks = array();

		/**
		 * List of recently render hooks.
		 *
		 * @since  1.0
		 * @access private
		 * @var array array of hooks
		 */
		private $recent_hooks = array();

		/**
		 * List of ignore hooks.
		 *
		 * @since  1.0
		 * @access private
		 * @var array array of hooks
		 */
		private $ignore_hooks = array();

		/**
		 * Instance.
		 *
		 * @since  1.0
		 * @access static
		 * @var Give_Hook_Helper $instance
		 */
		static private $instance;

		/**
		 * Check hook status.
		 *
		 * @since  1.0
		 * @access static
		 * @var string $doing
		 */
		private $doing = 'collect';

		/**
		 * List of deprecated action hooks.
		 *
		 * @since  1.0
		 * @access private
		 * @var array array of hooks
		 */
		private $deprecated_action_hooks = array();

		/**
		 * List of deprecated filter hooks.
		 *
		 * @since  1.0
		 * @access private
		 * @var array array of hooks
		 */
		private $deprecated_filter_hooks = array();

		/**
		 * Construct and initialize the main plugin class
		 */
		private function __construct() {
		}


		/**
		 * Instance initiator
		 *
		 * @since 1.0
		 */
		public static function get_instance() {

			if ( null === static::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * Set params.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return mixed
		 */
		public function setup_params() {

			// Use this to set any tags known to cause display problems.
			// Will be display in sidebar.
			$this->ignore_hooks = apply_filters( 'ghh_ignore_hooks', array() );

			// List of deprecated action hooks.
			if( function_exists( 'give_deprecated_actions' ) ) {
				$this->deprecated_action_hooks = give_deprecated_actions();
			}

			// List of deprecated filter hooks.
			if( function_exists( 'give_deprecated_filters' ) ) {
				$this->deprecated_filter_hooks = give_deprecated_filters();
			}

			return self::$instance;
		}


		/**
		 * Setup hooks
		 *
		 * @since  1.0
		 * @access public
		 */
		public function setup_hooks() {

			// Translations
			//add_action( 'plugins_loaded', array( $this, 'load_translation' ) );

			// Init
			add_action( 'init', array( $this, 'wp_init' ) );

			if ( ! isset( $this->status ) ) {

				if ( ! isset( $_COOKIE['ghh_status'] ) ) {
					setcookie( 'ghh_status', 'off', time() + 3600 * 24 * 100, '/' );
				}

				if ( isset( $_REQUEST['ghh-hooks'] ) ) {
					setcookie( 'ghh_status', $_REQUEST['ghh-hooks'], time() + 3600 * 24 * 100, '/' );
					$this->status = $_REQUEST['ghh-hooks'];
				} elseif ( isset( $_COOKIE['ghh_status'] ) ) {
					$this->status = $_COOKIE['ghh_status'];
				} else {
					$this->status = 'off';
				}
			}

			if ( $this->status == 'show-action-hooks' || $this->status == 'show-filter-hooks' ) {

				add_filter( 'all', array( $this, 'hook_all_hooks' ), 100 );
				add_action( 'shutdown', array( $this, 'notification_switch' ) );

				add_action( 'shutdown', array( $this, 'filter_hooks_panel' ) );
			}
		}

		/**
		 * Admin Menu top bar
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @param WP_Admin_Bar $wp_admin_bar
		 */
		public function admin_bar_menu( $wp_admin_bar ) {

			if ( 'show-action-hooks' == $this->status ) {

				$title = __( 'Hide Action Hooks', 'give-hook-helper' );
				$href  = '?ghh-hooks=off';
				$css   = 'ghh-hooks-on ghh-hooks-normal';
			} else {

				$title = __( 'Show Action Hooks', 'give-hook-helper' );
				$href  = '?ghh-hooks=show-action-hooks';
				$css   = '';
			}

			$wp_admin_bar->add_menu( array(
				'title'  => '<span class="ab-icon"></span><span class="ab-label">' . __( 'Give Hook Helper', 'give-hook-helper' ) . '</span>',
				'id'     => 'ghh-main-menu',
				'parent' => false,
				'href'   => $href,
			) );

			$wp_admin_bar->add_menu( array(
				'title'  => $title,
				'id'     => 'ghh-give-hook-helper',
				'parent' => 'ghh-main-menu',
				'href'   => $href,
				'meta'   => array( 'class' => $css ),
			) );


			if ( $this->status == "show-filter-hooks" ) {

				$title = __( 'Hide Action & Filter Hooks', 'give-hook-helper' );
				$href  = '?ghh-hooks=off';
				$css   = 'ghh-hooks-on ghh-hooks-sidebar';
			} else {

				$title = __( 'Show Action & Filter Hooks', 'give-hook-helper' );
				$href  = '?ghh-hooks=show-filter-hooks';
				$css   = '';
			}

			$wp_admin_bar->add_menu( array(
				'title'  => $title,
				'id'     => 'ghh-show-all-hooks',
				'parent' => 'ghh-main-menu',
				'href'   => $href,
				'meta'   => array( 'class' => $css ),
			) );
		}

		/**
		 * Custom css to add icon to admin bar edit button.
		 *
		 * @since 1.0
		 */
		function add_builder_edit_button_css() {
			?>
			<style>
				#wp-admin-bar-ghh-main-menu .ab-icon:before {
					font-family: "dashicons" !important;
					content: "\f323" !important;
					font-size: 16px !important;
				}
			</style>
			<?php
		}

		/**
		 * Notification Switch
		 * Displays notification interface that will alway display
		 * even if the interface is corrupted in other places.
		 */
		function notification_switch() {
			?>
			<a class="ghh-notification-switch" href="?ghh-hooks=off">
				<span class="ghh-notification-indicator"></span>
				<?php _e( 'Hide Hooks', 'give-hook-helper' ) ?>
			</a>
			<?php
		}

		/**
		 * @return bool
		 */
		function wp_init() {

			// Restrict use to Admins only
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			// Enqueue Scripts/Styles - in head of admin
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
			add_action( 'login_enqueue_scripts', array( $this, 'enqueue_script' ) );

			// Top Admin Bar
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 90 );

			// Top Admin Bar Styles
			add_action( 'wp_print_styles', array( $this, 'add_builder_edit_button_css' ) );
			add_action( 'admin_print_styles', array( $this, 'add_builder_edit_button_css' ) );

			if ( $this->status == 'show-action-hooks' || $this->status == 'show-filter-hooks' ) {

				//Final hook - render the nested action array
				add_action( 'admin_head', array( $this, 'render_head_hooks' ), 100 ); // Back-end - Admin
				add_action( 'wp_head', array( $this, 'render_head_hooks' ), 100 ); // Front-end
				add_action( 'login_head', array( $this, 'render_head_hooks' ), 100 ); // Login
				add_action( 'customize_controls_print_scripts', array(
					$this,
					'render_head_hooks',
				), 100 ); // Customizer
			}
		}

		/**
		 * Enqueue Scripts
		 *
		 * @since 1.0
		 */
		public function enqueue_script() {

			global $wp_scripts, $current_screen;

			// Main Styles
			wp_register_style( 'ghh-main-css', plugins_url( basename( plugin_dir_path( __FILE__ ) ) . '/assets/css/ghh-main.css', basename( __FILE__ ) ), '', '1.0', 'screen' );
			wp_enqueue_style( 'ghh-main-css' );

		}

		/**
		 * Localization
		 *
		 * @since 1.0
		 */
		public function load_translation() {
			load_plugin_textdomain( 'give-hook-helper', false, dirname( plugin_basename( __FILE__ ) ) . '/localization/' );
		}

		/**
		 * Render Head Hooks
		 *
		 * @since 1.0
		 */
		function render_head_hooks() {

			// Render all the hooks so far
			$this->render_hooks();

			// Add header marker to hooks collection
			// $this->all_hooks[] = array( 'End Header. Start Body', false, 'marker' );

			// Change to doing 'write' which will write the hook as it happens
			$this->doing = 'write';
		}

		/**
		 * Render all hooks already in the collection
		 *
		 * @since 1.0
		 */
		function render_hooks() {

			foreach ( $this->all_hooks as $nested_value ) {

				if ( 'action' == $nested_value['type'] ) {
					$this->render_action( $nested_value );
				}
			}
		}

		/**
		 * Hook all hooks
		 *
		 * @since   1.0
		 * @access  public
		 *
		 * @param $hook
		 */
		public function hook_all_hooks( $hook ) {

			global $wp_actions, $wp_filter;

			if ( false === $this->is_give_plugin_hooks( $hook ) ) {
				return;
			}

			if ( ! in_array( $hook, $this->recent_hooks ) ) {

				if ( isset( $wp_actions[ $hook ] ) ) {

					// Action
					$this->all_hooks[] = array(
						'ID'       => $hook,
						'callback' => false,
						'type'     => 'action',
					);
				} else {

					// Filter
					$this->all_hooks[] = array(
						'ID'       => $hook,
						'callback' => false,
						'type'     => 'filter',
					);
				}
			}

			//if ( isset( $wp_actions[$hook] ) && $wp_actions[$hook] == 1 && !in_array( $hook, $this->ignore_hooks ) ) {
			//if (  ( isset( $wp_actions[$hook] ) || isset( $wp_filter[$hook] ) ) && !in_array( $hook, $this->ignore_hooks ) ) {
			if ( isset( $wp_actions[ $hook ] ) && ! in_array( $hook, $this->recent_hooks ) && ! in_array( $hook, $this->ignore_hooks ) ) {

				// @TODO - caller function testing.
				$callers = false; // @param $callers Array | false for debug_backtrace()

				if ( 'write' == $this->doing ) {
					$this->render_action( end( $this->all_hooks ) );
				}
			} else {
				//s('(skiped-hook!)');
				//$this->render_action( $hook );
			}

			// Discarded functionality: if the hook was
			// run recently then don't show it again.
			// Better to use the once run or always run theory.

			$this->recent_hooks[] = $hook;

			if ( count( $this->recent_hooks ) > 100 ) {
				array_shift( $this->recent_hooks );
			}
		}

		/**
		 * Render action
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @param array $args
		 */
		public function render_action( $args = array() ) {

			global $wp_filter;

			// Get all the nested hooks
			$nested_hooks = ( isset( $wp_filter[ $args['ID'] ] ) ) ? $wp_filter[ $args['ID'] ] : false;

			// Count the number of functions on this hook
			$nested_hooks_count = 0;
			if ( $nested_hooks ) {
				foreach ( $nested_hooks as $key => $value ) {
					$nested_hooks_count += count( $value );
				}
			}


			include 'view/action-html.php';
		}

		/**
		 * Filter Hooks Panel
		 *
		 * @since 1.0
		 * @acees public
		 */
		public function filter_hooks_panel() {
			include 'view/filters-panel-html.php';
		}

		/**
		 * Check if hook is from Give plugin or not.
		 *
		 * @since  1.0
		 * @access private
		 *
		 * @param $hook
		 *
		 * @return bool|int
		 */
		private function is_give_plugin_hooks( $hook ) {
			$hook_name = is_array( $hook ) ? $hook['ID'] : $hook;

			return strpos( $hook_name, 'give_' );
		}

	}
endif;

/**
 * Initialize plugin.
 */
function give_hook_helper_init() {
	// Bailout: Check if Give plugin is activae or not.
	if ( ! class_exists( 'Give' ) ) {
		return;
	}

	Give_Hook_Helper::get_instance()
	                ->setup_params()
	                ->setup_hooks();
}

add_action( 'plugins_loaded', 'give_hook_helper_init', 0 );
