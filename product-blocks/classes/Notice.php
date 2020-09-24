<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Notice {
    public function __construct(){
		add_action('admin_init', array($this, 'admin_init_callback'));
		add_action('wp_ajax_wc_install', array($this, 'wc_install_callback'));
		add_action('admin_action_wc_activate', array($this, 'wc_activate_callback'));
		add_action('wp_ajax_wopb_dismiss_notice', array($this, 'set_dismiss_notice_callback'));
	}

	
	// Dismiss Notice Callback
	public function set_dismiss_notice_callback() {
		if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')) {
			return ;
		}
		update_option( 'dismiss_notice', 'yes' );
	}


	public function admin_init_callback(){
		if (!file_exists(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php')) {
			add_action('admin_notices', array($this, 'wc_installation_notice_callback'));
		} else if (file_exists(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php') && ! is_plugin_active('woocommerce/woocommerce.php')) {
			add_action('admin_notices', array($this, 'wc_activation_notice_callback'));
		}
	}


	public function wc_installation_notice_callback() {
		if (!get_option('dismiss_notice')) {
			$this->wc_notice_css();
			$this->wc_notice_js();
			?>
			<div class="wc-install">
				<img width="200" src="<?php echo WOPB_URL.'assets/img/WordPress.png'; ?>" alt="logo" />
				<div class="wc-install-body">
					<a class="wc-dismiss-notice" data-security=<?php echo wp_create_nonce('wopb-nonce'); ?>  data-ajax=<?php echo admin_url('admin-ajax.php'); ?> href="#"><span class="dashicons dashicons-no-alt"></span> <?php _e('Dismiss', 'product-blocks'); ?></a>
					<h3><?php _e('Welcome to Product Blocks.', 'product-blocks'); ?></h3>
					<div><?php _e('WooCommerce Product Blocks is a WooCommerce plugin. To use this plugins you have to install and activate WooCommerce.', 'product-blocks'); ?></div>
					<a class="wc-install-btn button button-primary button-hero" href="<?php echo add_query_arg(array('action' => 'wc_install'), admin_url()); ?>"><span class="dashicons dashicons-image-rotate"></span><?php _e('Install WooCommerce', 'product-blocks'); ?></a>
					<div id="installation-msg"></div>
				</div>
			</div>
			<?php
		}
	}

	public function wc_activation_notice_callback() {
		if (!get_option('dismiss_notice')) {
			$this->wc_notice_css();
			$this->wc_notice_js();
			?>
			<div class="wc-install">
				<img width="200" src="<?php echo WOPB_URL.'assets/img/WordPress.png'; ?>" alt="logo" />
				<div class="wc-install-body">
					<a class="wc-dismiss-notice" data-security=<?php echo wp_create_nonce('wopb-nonce'); ?>  data-ajax=<?php echo admin_url('admin-ajax.php'); ?> href="#"><span class="dashicons dashicons-no-alt"></span> <?php _e('Dismiss', 'product-blocks'); ?></a>
					<h3><?php _e('Welcome to Product Blocks.', 'product-blocks'); ?></h3>
					<div><?php _e('WooCommerce Product Blocks is a WooCommerce plugin. To use this plugins you have to install and activate WooCommerce.', 'product-blocks'); ?></div>
					<a class="button button-primary button-hero" href="<?php echo add_query_arg(array('action' => 'wc_activate'), admin_url()); ?>"><?php _e('Activate WooCommerce', 'product-blocks'); ?></a>
				</div>
			</div>
			<?php
		}
	}


	public function wc_notice_css() {
		?>
		<style type="text/css">
            .wc-install {
                display: -ms-flexbox;
                display: flex;
                align-items: center;
                background: #fff;
                margin-top: 40px;
                width: calc(100% - 50px);
                border: 1px solid #ccd0d4;
                padding: 15px;
                border-radius: 4px;
            }   
            .wc-install img {
                margin-right: 10px; 
            }
            .wc-install-body {
                -ms-flex: 1;
                flex: 1;
            }
            .wc-install-body > div {
                max-width: 450px;
                margin-bottom: 20px;
            }
            .wc-install-body h3 {
                margin-top: 0;
                font-size: 24px;
                margin-bottom: 15px;
            }
            .wc-install-btn {
                margin-top: 15px;
                display: inline-block;
            }
			.wc-install .dashicons{
				display: none;
				animation: dashicons-spin 1s infinite;
				animation-timing-function: linear;
			}
			.wc-install.loading .dashicons {
				display: inline-block;
				margin-top: 12px;
				margin-right: 5px;
			}
			@keyframes dashicons-spin {
				0% {
					transform: rotate( 0deg );
				}
				100% {
					transform: rotate( 360deg );
				}
			}
			.wc-dismiss-notice {
				position: relative;
				text-decoration: none;
				float: right;
				right: 26px;
			}
			.wc-dismiss-notice .dashicons{
				display: inline-block;
    			text-decoration: none;
				animation: none;
			}
		</style>
		<?php
	}


	public function wc_notice_js() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				'use strict';
				$(document).on('click', '.wc-install-btn', function(e){
					e.preventDefault();
					const $that = $(this);
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {install_plugin: 'woocommerce', action: 'wc_install'},
						beforeSend: function(){
                                $that.parents('.wc-install').addClass('loading');
                        },
						success: function (data) {
							$('#installation-msg').html(data);
							$that.parents('.wc-install').remove();
						},
						complete: function () {
							$that.parents('.wc-install').removeClass('loading');
						}
					});
				});

				// Dismiss notice
				$(document).on('click', '.wc-dismiss-notice', function(e){
					e.preventDefault();
					const that = $(this);
					$.ajax({
						url: that.data('ajax'),
						type: 'POST',
						data: { 
							action: 'wopb_dismiss_notice', 
							wpnonce: that.data('security')
						},
						success: function (data) {
							that.parents('.wc-install').hide("slow", function() { that.parents('.wc-install').remove(); });
						},
						error: function(xhr) {
							console.log('Error occured. Please try again' + xhr.statusText + xhr.responseText );
						},
					});
				});
				
			});
		</script>
		<?php
	}


	public function wc_install_callback(){
		include(ABSPATH . 'wp-admin/includes/plugin-install.php');
		include(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

		if (! class_exists('Plugin_Upgrader')){
			include(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
		}
		if (! class_exists('Plugin_Installer_Skin')) {
			include( ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php' );
		}

		$plugin = 'woocommerce';

		$api = plugins_api( 'plugin_information', array(
			'slug' => $plugin,
			'fields' => array(
				'short_description' => false,
				'sections' => false,
				'requires' => false,
				'rating' => false,
				'ratings' => false,
				'downloaded' => false,
				'last_updated' => false,
				'added' => false,
				'tags' => false,
				'compatibility' => false,
				'homepage' => false,
				'donate_link' => false,
			),
		) );

		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$title = sprintf( __('Installing Plugin: %s'), $api->name . ' ' . $api->version );
		$nonce = 'install-plugin_' . $plugin;
		$url = 'update.php?action=install-plugin&plugin=' . urlencode( $plugin );

		$upgrader = new \Plugin_Upgrader( new \Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
		$upgrader->install($api->download_link);
		die();
	}


	public function wc_activate_callback(){
		activate_plugin('woocommerce/woocommerce.php');
		wp_redirect(admin_url('admin.php?page=wopb-settings'));
		exit();
	}

}