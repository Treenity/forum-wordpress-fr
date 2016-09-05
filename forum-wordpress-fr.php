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
 * @package forumWordpressFr
 */

/**
 * Class Forum_wordpress_fr
 */
class Forum_wordpress_fr {
	/**
	 * Forum_wordpress_fr constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			load_plugin_textdomain( __CLASS__, false, plugin_dir_path( __FILE__ ) . '/languages' );
			add_action( 'wp_dashboard_setup', array( &$this, 'wp_dashboard_setup' ), 8 );
		}
	}

	/**
	 * Add dashboard
	 */
	public function wp_dashboard_setup() {
		if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
			return;
		}
		// for widget.
		wp_add_dashboard_widget( __CLASS__, __( 'wordpress-fr.net/support', __CLASS__ ), array(
			&$this,
			'render',
		), array( &$this, 'control' ) );
	}

	/**
	 * Generate informations
	 *
	 * @return stdClass
	 */
	public function get_datas() {
		global $wp_version, $required_php_version, $wpdb, $required_mysql_version;
		$export = new stdClass();
		$export->wp_version = $wp_version;
		$export->php_version = phpversion();
		$export->required_php_version = $required_php_version;
		$export->mysql_version = $wpdb->db_version();
		$export->required_mysql_version = $required_mysql_version;
		$export->site_url = defined( 'WP_SITEURL' ) ? WP_SITEURL : site_url();
		// todo: Get host with dns_get_record( $this->site_url() ) ?
		$export->host = filter_input( INPUT_SERVER, 'SERVER_SOFTWARE' );
		foreach ( get_plugins() as $plugin ) {
			$export->plugins[] = $plugin;
		}

		return $export;
	}

	/**
	 * Render the informations
	 */
	public function render() {
		wp_enqueue_style( __CLASS__, plugin_dir_url( __FILE__ ) . '/assets/css/global.css' );
		$datas = $this->get_datas();
		?>
		<div class="forumWordpressFr">
			<div class="forumWordpressFr__panel">
				<ul class="forumWordpressFr__panel__list">
					<li class="forumWordpressFr__panel__list__item forumWordpressFr__panel__list__item--wp_version">
						<strong>
							<?php printf( __( '%s version', __CLASS__ ), 'Wordpress' ) ?> :
						</strong>
						<?php echo $datas->wp_version ?>
					</li>
					<li class="forumWordpressFr__panel__list__item forumWordpressFr__panel__list__item--php_version">
						<strong>
							<?php printf( __( '%s version', __CLASS__ ), 'PHP' ) ?> :
						</strong>
						<?php echo $datas->php_version ?>
					</li>
					<li class="forumWordpressFr__panel__list__item forumWordpressFr__panel__list__item--mysql_version">
						<strong>
							<?php printf( __( '%s version', __CLASS__ ), 'Mysql' ) ?> :
						</strong>
						<?php echo $datas->mysql_version ?>
					</li>
					<li class="forumWordpressFr__panel__list__item forumWordpressFr__panel__list__item--host">
						<strong>
							<?php _e( 'Host', __CLASS__ ) ?> :
						</strong>
						<?php echo $datas->host ?>
					</li>
					<li class="forumWordpressFr__panel__list__item forumWordpressFr__panel__list__item--plugins">
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
