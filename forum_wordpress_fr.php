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
 * @package forum-wordpress-fr
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

		$l10n['ko'] = esc_js( __( 'Sélectionner le texte ci-dessous puis CTRL+C ou Pomme+C', __CLASS__ ) ) . "\n\n\n";
		$l10n['ok'] = esc_js( __( 'Copié dans le presse-papier', __CLASS__ ) );
		if ( $this->with_flash ) {
			$l10n['embed'] = $this->is_ie() ? "<object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' align='middle' codebase='" . ( is_ssl() ? 'https://' : 'http://' ) . "download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0' width='WW' height='HH' id='fwf_zc_movie'><param name='allowScriptAccess' value='always' /><param name='allowFullScreen' value='false' /><param name='wmode' value='transparent'/><param name='loop' value='false' /><param name='menu' value='false' /><param name='quality' value='best' /><param name='bgcolor' value='#ffffff' /><param name='movie' value='" . $this->site_url . '/' . FWF_PLUGIN_DIR . 'js/fwf_zc.swf' . "' /><param name='flashvars' value='id=1&width=WW&height=HH' /></object>" : "<embed pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' allowScriptAccess='always' allowFullScreen='false' loop='false' menu='false' quality='best' bgcolor='#ffffff' wmode='transparent' align='middle' width='WW' height='HH' flashvars='id=1&width=WW&height=HH' id='fwf_zc_movie' name='fwf_zc_movie' src='" . $this->site_url . '/' . FWF_PLUGIN_DIR . 'js/fwf_zc.swf' . "' />";
		}

		wp_localize_script( __CLASS__, 'fwf_L10n', $l10n );
		wp_enqueue_script( __CLASS__ );

		// for widget.
		wp_add_dashboard_widget( __CLASS__, __( 'wordpress-fr.net/support', __CLASS__ ), array(
			&$this,
			'widget',
		), array( &$this, 'control' ) );
	}

	/**
	 * @return bool
	 */
	public function is_ie() {
		return ( false !== strpos( $_SERVER['HTTP_USER_AGENT'], '/MSIE/' ) );
	}

	/**
	 *
	 */
	public function widget() {
		// version wordpress.
		global $wp_version, $wpdb;
		$txt[] = sprintf( __( '<strong>- Version de WordPress :</strong> %1$s%2$s', __CLASS__ ), $wp_version,
			is_multisite() ? ' ' . __( 'multi-site', __CLASS__ ) : '' );

		// version php/mysql.
		$php_ver = phpversion();
		$mysql_ver = $wpdb->db_version();
		$txt[] = sprintf( __( '<strong>- Version de PHP/MySQL :</strong> %1$s / %2$s', __CLASS__ ), $php_ver,
			$mysql_ver );

		// theme.
		if ( function_exists( 'wp_get_theme' ) ) {
			$wp_theme = wp_get_theme( get_stylesheet() );
			$wp_theme_name = $wp_theme->display( 'Name', true, false );
			$wp_theme_url = $wp_theme->display( 'ThemeURI', true, false );
			if ( ! empty( $wp_theme_url ) ) {
				$wp_theme_url = sprintf( __( '<strong>- Thème URI :</strong> %s', __CLASS__ ), $wp_theme_url );
			}
		} else {
			global $wp_themes;
			/** @noinspection PhpDeprecationInspection */
			$wp_theme_name = get_current_theme();
			$wp_theme = $wp_themes[ $wp_theme_name ];
			$wp_theme_url = $wp_theme['Author URI'];
			if ( ! empty( $wp_theme_url ) ) {
				$wp_theme_url = sprintf( __( '<strong>- Thème Auteur URI :</strong> %s', __CLASS__ ), $wp_theme_url );
			}
		}
		$txt[] = sprintf( __( '<strong>- Thème utilisé :</strong> %s', __CLASS__ ), $wp_theme_name );
		if ( ! empty( $wp_theme_url ) ) {
			$txt[] = $wp_theme_url;
		}
		// plugins.
		foreach ( (array) get_plugins() as $plugin_file => $plugin_data ) {
			if ( is_plugin_active_for_network( $plugin_file ) ) {
				$ms_plugins[] = $plugin_data['Name'] . ' (' . $plugin_data['Version'] . ')';
			} elseif ( is_plugin_active( $plugin_file ) ) {
				$wp_plugins[] = $plugin_data['Name'] . ' (' . $plugin_data['Version'] . ')';
			}
		}
		if ( isset( $wp_plugins ) ) {
			$txt[] = sprintf( __( '<strong>- Extensions en place :</strong> %s', __CLASS__ ),
				implode( ', ', $wp_plugins ) );
		}

		if ( isset( $ms_plugins ) ) {
			$txt[] = sprintf( __( '<strong>- Extensions réseau en place :</strong> %s', __CLASS__ ),
				implode( ', ', $ms_plugins ) );
		}

		// site url.
		$siteurl = $this->site_url;
		$txt[] = sprintf( __( "<strong>- Adresse du site :</strong> %s", __CLASS__ ), $siteurl );

		// host.
		$host = $_SERVER['SERVER_SOFTWARE'];
		$txt[] = sprintf( __( "<strong>- Nom de l'hébergeur :</strong> %s", __CLASS__ ), $host );
		?>
		<div id='fwf_content'><strong><?php _e( "Ma configuration WP actuelle :", __CLASS__ ); ?></strong>

			<ul>
				<li><?php echo implode( "</li>\n<li>", $txt ); ?></li>
			</ul>
		</div>
		<div>
			<?php if ( $this->with_flash() ) {
				echo "\t<div id='fwf_zc' style='position:absolute;z-index:99;'></div>\n";
			} ?>
			<div style='position:relative;'>
				<input id='fwf_copy' class='button-primary' type='button'
				       value="<?php echo esc_attr( __( 'Copier', __CLASS__ ) ); ?>"/>
			</div>
		</div>
		<?php
	}

	/**
	 * @return bool
	 */
	public function with_flash() {
		if ( ! $options = get_option( __CLASS__ ) ) {
			return false;
		}

		return ! ( array_key_exists( 'hidden', $options ) && ! array_key_exists( 'Flash', $options ) );
	}

	/**
	 *
	 */
	public function control() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['fwf'] ) ) {
			update_option( __CLASS__, $_POST['fwf'] );

			return;
		}
		?>
		<br/><strong><?php _e( "Copie dans le presse-papier : ", __CLASS__ ); ?></strong><br/>
		<label for='fwf_Flash'>
			<input type='checkbox' id='fwf_Flash' name='fwf[Flash]'
			       value='1'<?php checked( $this->with_flash, 1 ); ?> /> <?php _e( "Utiliser Flash", __CLASS__ ); ?>
		</label>
		<br/><br/>
		<input type='hidden' name='fwf[hidden]' value='0'/>
		<?php
	}

	/**
	 * @return stdClass
	 */
	public function export() {
		global $wp_version, $php_version, $required_php_version, $mysql_version, $required_mysql_version;
		$export = new stdClass();
		$export->wp_version = $wp_version;
		$export->php_version = $php_version;
		$export->required_php_version = $required_php_version;
		$export->mysql_version = $mysql_version;
		$export->required_mysql_version = $required_mysql_version;
		$export->site_url = $this->site_url();
		$export->host = gethostname();
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

	public function output( $datas ) {
		?>
		<div class="panel">
			<ul class="panel__list">
				<li>
					<strong>
						<?php printf( __( '% version', __CLASS__ ), 'Wordpress' ) ?>
					</strong>
					<?php echo $datas->wp_version ?>
				</li>
				<li>
					<strong>
						<?php printf( __( '% version', __CLASS__ ), 'PHP' ) ?>
					</strong>
					<?php echo $datas->php_version ?>
				</li>
				<li>
					<strong>
						<?php printf( __( '% version', __CLASS__ ), 'Mysql' ) ?>
					</strong>
					<?php echo $datas->mysql_version ?>
				</li>
				<li>
					<strong>
						<?php _e( 'Plugins', __CLASS__ ) ?>
					</strong>
					<?php foreach ( (array) $datas->plugins as $plugin ) : ?>
				<li>
					<?php echo '<pre>';
					print_r( $plugin );
					echo '</pre>'; ?>
				</li>
				<?php endforeach; ?>
				</li>
			</ul>
		</div>
		<?php
	}
}

new Forum_wordpress_fr();
