<?php
namespace ss_wp_highlighter;


/**
 * Class Boot
 * @package ss_wp_highlighter
 */
class Boot {
	/**
	 * Boot constructor.
	 */
	public function __construct() {
	}

	/**
	 *
	 */
	public static function init() {

		//Adding Classes!
		include_once 'classes/wp-highlighter.php';
		$class_wp_highlighter = new WPHighlighter();
		$class_wp_highlighter::init();


		/* Licensing */

		// URL of store powering the plugin
		define( 'WP_HIGHLIGHTER_STORE_URL', 'https://wphighlighter.com' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

		// Store download name/title
		define( 'WP_HIGHLIGHTER_ITEM_NAME', 'WP Highlighter' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

		// include updater
		include_once 'classes/EDD_SL_Plugin_Updater.php';

		add_action( 'admin_init', array( __CLASS__, 'wp_highlighter_plugin_updater' ), 0 );
		add_action( 'admin_menu', array( __CLASS__, 'wp_highlighter_license_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'wp_highlighter_register_option' ) );
		add_action( 'admin_init', array( __CLASS__, 'wp_highlighter_activate_license' ) );
		add_action( 'admin_init', array( __CLASS__, 'wp_highlighter_deactivate_license' ) );
	}

	/**
	 *
	 */
	public static function wp_highlighter_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'wp_highlighter_codes_license_key' ) );

		// setup the updater
		$wp_highlighter_updater = new EDD_SL_Plugin_Updater( WP_HIGHLIGHTER_STORE_URL, WP_HIGHLIGHTER_FILE, array(

			'version'   => WP_HIGHLIGHTER_VERSION,     // current version number
			'license'   => $license_key,                    // license key (used get_option above to retrieve from DB)
			'item_name' => WP_HIGHLIGHTER_ITEM_NAME,                    // name of this plugin
			'author'    => 'WP Highlighter',                    // author of this plugin

		) );

	}

	// Licence options page
	/**
	 *
	 */
	public static function wp_highlighter_license_menu() {

		add_submenu_page( 'wp-highlighter', 'WP Highlighter License Activation', 'License Activation', 'manage_options', 'wp-highlighter-license-activation', array(
			__CLASS__,
			'wp_highlighter_license_page',
		) );

	}

	/**
	 *
	 */
	public static function wp_highlighter_license_page() {
		$license = get_option( 'wp_highlighter_codes_license_key' );
		$status  = get_option( 'wp_highlighter_codes_license_status' );
		$error   = '';

		if ( 'invalid' === $status ) {
			$error = 'Sorry, that is an invalid license key.';
		}

		?>
		<div class="">
		<div class="uo-admin-header">
			<h2><?php esc_html_e( 'Thank you for using the WP Highlighter!', 'wp-highlighter' ); ?></h2>
			<br>

			<h3 style="font-weight: normal;"><?php _e( '1. Enter your license key and then click <strong>Save Changes</strong>.<br><br>2. Once the page is reloaded, click <strong>Activate License</strong>.', 'wp-highlighter' ); ?></h3>
		</div>

		<form method="post" action="options.php">
			<p style="color: #ff6b00;font-weight: bold; font-size: 14px;"><?php echo $error; ?></p>
			<?php settings_fields( 'wp_highlighter_codes_license' ); ?>

			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'License Key', 'wp-highlighter' ); ?>
					</th>
					<td>
						<input id="wp_highlighter_codes_license_key" name="wp_highlighter_codes_license_key" type="text" class="regular-text"
						       value="<?php esc_attr_e( $license ); ?>"/>
						<label class="description"
						       for="wp_highlighter_codes_license_key"><?php _e( 'Enter your license key', 'wp-highlighter' ); ?></label>
					</td>
				</tr>
				<?php if ( false !== $license ) { ?>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Activate License', 'wp-highlighter' ); ?>
						</th>
						<td>
							<?php if ( $status !== false && $status === 'valid' ) { ?>
								<span style="color: #29c129; font-weight:bold; line-height: 27px;padding-right: 20px;">Your License is active. </span>
								<?php wp_nonce_field( 'wp_highlighter_codes_nonce', 'wp_highlighter_codes_nonce' ); ?>
								<input type="submit" class="button-secondary" name="wp_highlighter_codes_license_deactivate"
								       value="<?php _e( 'Deactivate License', 'wp-highlighter' ); ?>"/>
							<?php } else {
								wp_nonce_field( 'wp_highlighter_codes_nonce', 'wp_highlighter_codes_nonce' ); ?>
								<input
										style="background: #29c129; border-color: #29c129!important; text-decoration: none; color: white; font-size: 17px; padding: 8px 0; width: 170px; line-height: 0;"
										type="submit" class="button-secondary" name="wp_highlighter_codes_license_activate"
										value="<?php _e( 'Activate License', 'wp-highlighter' ); ?>"/>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
		<?php
	}

	public static function wp_highlighter_register_option() {
		// creates our settings in the options table
		register_setting( 'wp_highlighter_codes_license', 'wp_highlighter_codes_license_key', array(
			__CLASS__,
			'wp_highlighter_sanitize_license',
		) );
	}


	public static function wp_highlighter_sanitize_license( $new ) {

		$old = get_option( 'wp_highlighter_codes_license_key' );
		if ( $old && $old !== $new ) {
			delete_option( 'wp_highlighter_codes_license_status' ); // new license has been entered, so must reactivate
		}

		return $new;
	}


	/************************************
	 * this illustrates how to activate
	 * a license key
	 *************************************/

	public static function wp_highlighter_activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['wp_highlighter_codes_license_activate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'wp_highlighter_codes_nonce', 'wp_highlighter_codes_nonce' ) ) {
				return null;
			}
			// get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'wp_highlighter_codes_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( WP_HIGHLIGHTER_ITEM_NAME ), // the name of our product in uo
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post( WP_HIGHLIGHTER_STORE_URL, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'wp_highlighter_codes_license_status', $license_data->license );

		}
	}


	/***********************************************
	 * Illustrates how to deactivate a license key.
	 * This will descrease the site count
	 ***********************************************/

	public static function wp_highlighter_deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['wp_highlighter_codes_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'wp_highlighter_codes_nonce', 'wp_highlighter_codes_nonce' ) ) {
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'wp_highlighter_codes_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( WP_HIGHLIGHTER_ITEM_NAME ), // the name of our product in uo
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post( WP_HIGHLIGHTER_STORE_URL, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( 'deactivated' === $license_data->license ) {
				delete_option( 'wp_highlighter_codes_license_status' );
			}

		}
	}


	/************************************
	 * this illustrates how to check if
	 * a license key is still valid
	 * the updater does this for you,
	 * so this is only needed if you
	 * want to do something custom
	 *************************************/

	public static function wp_highlighter_check_license() {

		global $wp_version;

		$license = trim( get_option( 'wp_highlighter_codes_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( WP_HIGHLIGHTER_ITEM_NAME ),
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( WP_HIGHLIGHTER_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'valid' === $license_data->license ) {
			echo 'valid';
			exit;
			// this license is still valid
		} else {
			echo 'invalid';
			exit;
			// this license is no longer valid
		}
	}

}
