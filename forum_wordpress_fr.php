<?php
/**
 * Plugin Name: Forum_wordpress_fr
 * Description: Questionnaire du forum http://www.wordpress-fr.net/support
 * Author: Andre Renaut
 * Author URI: http://www.mailpress.org
 * Requires at least: 3.3
 * Tested up to: 4.5
 * Version: 4.1
 *
 * @package forumWordpress
 */

/**
 *
 */
define( 'FWF_FOLDER', basename( __DIR__ ) );
/**
 *
 */
define( 'FWF_PLUGIN_DIR', PLUGINDIR . '/' . FWF_FOLDER . '/' );

/**
 * Class Forum_wordpress_fr
 */
class Forum_wordpress_fr {
	/**
	 * Forum_wordpress_fr constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_dashboard_setup', array( &$this, 'wp_dashboard_setup' ), 8 );
		}
	}

	/**
	 *
	 */
	public function wp_dashboard_setup() {
		if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
			return;
		}
		// for gettext.
		load_plugin_textdomain( __CLASS__, false, FWF_PLUGIN_DIR . 'languages' );
		// for javascript.
		wp_register_script( __CLASS__, '/' . FWF_PLUGIN_DIR . 'js/fwf.js', array( 'jquery' ), false, 1 );
		// for widget.
		wp_add_dashboard_widget( __CLASS__, __( 'wordpress-fr.net/support', __CLASS__ ), array(
			&$this,
			'output',
		), array( &$this, 'control' ) );
	}

	/**
	 * @return stdClass
	 */
	public function export() {
		global $wp_version, $required_php_version, $wpdb, $required_mysql_version;
		$export = new stdClass();
		$export->wp_version = $wp_version;
		$export->php_version = phpversion();
		$export->required_php_version = $required_php_version;
		$export->mysql_version = $wpdb->db_version();
		$export->required_mysql_version = $required_mysql_version;
		$export->site_url = $this->site_url();
		// todo: Get host with dns_get_record( $this->site_url() ) ?
		$export->host = $_SERVER['SERVER_SOFTWARE'];
		foreach ( get_plugins() as $plugin ) {
			$export->plugins[] = $plugin;
		}

		return $export;
	}

	/**
	 * @return string
	 */
	public function site_url() {
		return defined( 'WP_SITEURL' ) ? WP_SITEURL : site_url();
	}

	public function output() {
		wp_enqueue_style( __CLASS__, plugin_dir_url( __FILE__ ) . '/assets/css/global.css' );
		$datas = $this->export();
		?>
		<div class="forumWordpress">
			<div class="forumWordpress__panel">
				<ul class="forumWordpress__panel__list">
					<li class="forumWordpress__panel__list__item forumWordpress__panel__list__item--wp_version">
						<strong>
							<?php printf( __( '%s version', __CLASS__ ), 'Wordpress' ) ?> :
						</strong>
						<?php echo $datas->wp_version ?>
					</li>
					<li class="forumWordpress__panel__list__item forumWordpress__panel__list__item--php_version">
						<strong>
							<?php printf( __( '%s version', __CLASS__ ), 'PHP' ) ?> :
						</strong>
						<?php echo $datas->php_version ?>
					</li>
					<li class="forumWordpress__panel__list__item forumWordpress__panel__list__item--mysql_version">
						<strong>
							<?php printf( __( '%s version', __CLASS__ ), 'Mysql' ) ?> :
						</strong>
						<?php echo $datas->mysql_version ?>
					</li>
					<li class="forumWordpress__panel__list__item forumWordpress__panel__list__item--host">
						<strong>
							<?php _e( 'Host', __CLASS__ ) ?> :
						</strong>
						<?php echo $datas->host ?>
					</li>
					<li class="forumWordpress__panel__list__item forumWordpress__panel__list__item--plugins">
						<strong>
							<?php _e( 'Plugins', __CLASS__ ) ?> :
						</strong>
						<ul>
							<?php foreach ( (array) $datas->plugins as $plugin ) : ?>
								<li>
									<?php printf( '%1$s (%2$s)', $plugin['Name'], $plugin['Version'] ) ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}
}

new Forum_wordpress_fr();
