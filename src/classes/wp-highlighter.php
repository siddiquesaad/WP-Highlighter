<?php

namespace ss_wp_highlighter;


/**
 * Class WPHighlighter
 * @package ss_wp_highlighter
 */
class WPHighlighter {
	/**
	 *
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'wp_highlighter_admin_page' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_custom_scripts' ) );
		add_action( 'wp_footer', array( __CLASS__, 'mailchimp_script' ), 99 );
		add_action( 'wp_head', array( __CLASS__, 'wp_highlighter_add_custom_css' ), 99 );
		add_action( 'admin_init', array( __CLASS__, 'wp_highlighter_save_settings' ) );
	}


	/**
	 *
	 */
	public static function wp_highlighter_save_settings() {
		if ( isset( $_GET['class-selector'] ) ) {
			update_option( 'wp-highlighter-class-selector', $_GET['class-selector'] );
		}
		if ( isset( $_GET['bottom-percentage'] ) ) {
			update_option( 'wp-highlighter-bottom-percentage', $_GET['bottom-percentage'] );
		}
		if ( isset( $_GET['disappear-in'] ) ) {
			update_option( 'wp-highlighter-disappear-in', $_GET['disappear-in'] );
		}
		if ( isset( $_GET['hide-for'] ) ) {
			update_option( 'wp-highlighter-hide-for', $_GET['hide-for'] );
		}
		if ( isset( $_GET['custom-css'] ) ) {
			update_option( 'wp-highlighter-custom-css', $_GET['custom-css'] );
		}
		if ( isset( $_GET['wp-highlighter-submit'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . $_GET['page'] . '&saved=true' ) );
			exit;
		}
	}


	/**
	 *
	 */
	public static function wp_highlighter_admin_page() {
		add_menu_page(
			__( 'WP Highlighter', 'wp-highlighter' ),
			__( 'WP Highlighter', 'wp-highlighter' ),
			'manage_options',
			'wp-highlighter',
			array( __CLASS__, 'show_admin_page_callback' )
		);
	}

	/**
	 *
	 */
	public static function show_admin_page_callback() {
		?>
		<div class="wrap">
			<?php
			if ( isset( $_GET['saved'] ) ) {
				?>
				<div class="notices notice notice-success"><h3>Settings Saved!</h3></div>
				<?php
			}
			?>
			<h2>WP Highlighter Settings</h2>
			<form action="" method="get">
				<input type="hidden" name="page" value="wp-highlighter"/>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="class-selector">Input class or id</label></th>
						<td>
							<input id="class-selector" class="input-lg" type="text" name="class-selector" value="<?php echo get_option( 'wp-highlighter-class-selector', '' ); ?>" style="width: 100%; max-width:400px;" placeholder="#mailchimp or .mailchimp-class"/>
							<br/>
							<span class="description"><?php _e( 'Please enter selector CSS Class and/or ID.', 'wp-highlighter' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="bottom-percentage">%age from bottom</label></th>
						<td>
							<input id="bottom-percentage" class="input-lg" type="number" name="bottom-percentage" value="<?php echo get_option( 'wp-highlighter-bottom-percentage', '' ); ?>" style="width: 100%; max-width:50px;" placeholder="10"/>%
							<br/>
							<span class="description"><?php _e( 'Select %age from bottom to show backdrop.', 'wp-highlighter' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="disappear-in">Hide Overlay in</label></th>
						<td>
							<input id="disappear-in" class="input-lg" type="number" name="disappear-in" value="<?php echo get_option( 'wp-highlighter-disappear-in', '' ); ?>" style="width: 100%; max-width:50px;" placeholder="4"/>
							secs
							<br/>
							<span class="description"><?php _e( 'Hide backdrop in (secs).', 'wp-highlighter' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="hide-for">Hide Overlay for</label></th>
						<td>
							<input id="hide-for" class="input-lg" type="number" name="hide-for" value="<?php echo get_option( 'wp-highlighter-hide-for', '' ); ?>" style="width: 100%; max-width:50px;" placeholder="30"/>
							days
							<br/>
							<span class="description"><?php _e( 'Do not show overlay for (days). Enter 0 to show everytime.', 'wp-highlighter' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="custom-css">Custom CSS</label></th>
						<td>
							<textarea id="custom-css" name="custom-css" style="width: 100%; max-width:50%; height: 300px" row="12" placeholder=".sample-class{background:white;}"><?php echo get_option( 'wp-highlighter-custom-css', '' ); ?></textarea>
							<br/>
							<span class="description"><?php _e( 'Please enter selector CSS Class and/or ID.', 'wp-highlighter' ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
						</th>
						<td>
							<input type="submit" name="wp-highlighter-submit" value="Save Settings"/>
						</td>
					</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php
	}


	/**
	 *
	 */
	public static function add_custom_scripts() {
		wp_enqueue_style( 'wp-highlighter-style', plugins_url( '/css/style.css', WP_HIGHLIGHTER_FILE ), array(), '1.0' );
		wp_enqueue_script( 'jquery.scrolling', plugins_url( '/js/jquery.scrolling.js', WP_HIGHLIGHTER_FILE ), array( 'jquery' ), '0.1.20150317', false );
		wp_enqueue_script( 'js.cookie', plugins_url( '/js/js.cookie.js', WP_HIGHLIGHTER_FILE ), array( 'jquery' ), '2.1.3', false );
	}


	/**
	 *
	 */
	public static function mailchimp_script() {
		?>
		<script type="text/javascript">
			<?php if ( null !== get_option( 'wp-highlighter-class-selector', null ) ){ ?>
			var $selectorClass = '<?php echo get_option( 'wp-highlighter-class-selector' ) ?>';
			var numberOfDays = <?php echo get_option( 'wp-highlighter-hide-for', 30 ); ?>;
			var $closeTag = '.wp-highlighter #close-tag';
			jQuery(document).ready(function () {
				jQuery('body').find($selectorClass).addClass('wp-highlighter').append('<div id="close-tag"><a href="javascript:;">X</div>');
				jQuery('body').append('<div id="wp-highlighter-overlay"></div>');
				if ('hide' != Cookies.get('hideWPHighlighter')) {

					jQuery('body').delegate('.wp-highlighter #close-tag a', 'click', function () {
						jQuery('#wp-highlighter-overlay').fadeOut(300);
						jQuery($closeTag).hide();
						if (numberOfDays > 0) {
							Cookies.set('hideWPHighlighter', 'hide', {expires: numberOfDays})
						}
					})
					jQuery('body').delegate('#wp-highlighter-overlay', 'click', function () {
						jQuery(this).hide();
						jQuery($closeTag).hide();
						if (numberOfDays > 0) {
							Cookies.set('hideWPHighlighter', 'hide', {expires: numberOfDays})
						}
					})
				}

			});
			jQuery(function ($) {
				var hideIn = <?php echo get_option( 'wp-highlighter-disappear-in', 5 ) * 1000 ?>;
				var offsetTop = 0;
				var percentage = <?php echo get_option( 'wp-highlighter-bottom-percentage', 5 ) ?>;
				var windowHeight = jQuery(window).innerHeight();
				var offset = windowHeight * (percentage / 100);
				offsetTop = '-' + offset;
				var t = 0;
				$($selectorClass).scrolling({offsetTop: offsetTop});

				$($selectorClass).on('scrollin', function (event, $all_elements) {
					if ('hide' != Cookies.get('hideWPHighlighter')) {
						$('#wp-highlighter-overlay').fadeIn(500);
						$($closeTag).show(300);
						t = setTimeout(function () {
							$('#wp-highlighter-overlay').fadeOut(300);
							$($closeTag).hide();
							stopOverlays();
							if (numberOfDays > 0) {
								Cookies.set('hideWPHighlighter', 'hide', {expires: numberOfDays})
							}
						}, hideIn);
					}
				});

				function stopOverlays() {
					clearTimeout(t);
				}

				$($selectorClass).on('scrollout', function (event, $all_elements) {
					if ('hide' != Cookies.get('hideWPHighlighter')) {
						$('#wp-highlighter-overlay').fadeOut(300);
						$($closeTag).hide();
					}
				});
			});
			<?php } ?>
		</script>
		<?php
	}

	/**
	 *
	 */
	public static function wp_highlighter_add_custom_css() {
		?>
		<style type="text/css">
			<?php echo get_option( 'wp-highlighter-custom-css', '' ); ?>
		</style>
		<?php
	}
}